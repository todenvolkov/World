<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/include.php");
$CURRENCY_RIGHT = $APPLICATION->GetGroupRight("currency");
if ($CURRENCY_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/lang/", "/currencies_rates.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/prolog.php");



$sTableID = "t_currency_rates";
$oSort = new CAdminSorting($sTableID, "date", "desc");// инициализация сортировки
$lAdmin = new CAdminList($sTableID, $oSort);// инициализация списка

//названия всех input'ов фильтра
$arFilterFields = Array(
	"filter_period_from",
	"filter_period_to",
	"filter_currency",
);

$lAdmin->InitFilter($arFilterFields);//инициализация фильтра

$filter = new CAdminFilter(
	$sTableID."_filter", 
	array(
		GetMessage("curr_rates_curr1"),
	)
);

$arFilter = Array(
	"CURRENCY" => $filter_currency,
	"DATE_RATE" => $filter_period_from,
	"!DATE_RATE" => $filter_period_to,
);

if ($by!="curr" && $by!="rate")
	$by = "date"; 

$order = strtolower($order);
if ($order != "asc")
	$order = "desc";




if ($CURRENCY_RIGHT=="W" && $lAdmin->EditAction()) //если идет сохранение со списка
{

	function CheckFields($arFields, $ID = false)
	{
		global $DB;
		$arMsg = Array();


		if ($arFields["DATE_RATE"] == "" || !$DB->IsDate($arFields["DATE_RATE"]))
			$arMsg[] = array("id"=>"DATE_RATE", "text"=> GetMessage("ERROR_DATE_RATE"));


		if ($arFields["RATE"] <= 0.00)
			$arMsg[] = array("id"=>"RATE", "text"=> GetMessage("ERROR_SAVING_RATE1"));


		if ($arFields["RATE_CNT"] <= 0)
			$arMsg[] = array("id"=>"RATE_CNT", "text"=>  GetMessage("ERROR_SAVING_RATE2"));

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return true;
	}

	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		if (CheckFields($arFields, $ID))
		{
			$res = CCurrencyRates::Update($ID, $arFields);
			if (!$res)
			{
				if($e = $APPLICATION->GetException())
					$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".str_replace("<br>"," ",$e->GetString()), $ID);
			}
		}
		else
		{
			if($e = $APPLICATION->GetException())
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".str_replace("<br>"," ",$e->GetString()), $ID);
		}

	}
}


// обработка действий групповых и одиночных
if($CURRENCY_RIGHT=="W" && $arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CCourse::GetList(Array($by => $order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;

		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
			case "delete":
				CCurrencyRates::Delete($ID);
			break;
		}
	}
}


// инициализация списка - выборка данных
$rsData = CCurrencyRates::GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// установка строки навигации
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("curr_rates_nav")));

$arHeaders = Array();

$arHeaders[] = Array("id"=>"ID", "content"=>"ID", "default"=>false);
$arHeaders[] = Array("id"=>"CURRENCY", "content"=>GetMessage('curr_rates_curr1'), "sort"=>"curr", "default"=>true);
$arHeaders[] = Array("id"=>"DATE_RATE", "content"=>GetMessage('curr_rates_date1'), "sort"=>"date", "default"=>true);
$arHeaders[] = Array("id"=>"RATE_CNT", "content"=>GetMessage('curr_rates_rate_cnt'), "default"=>true);
$arHeaders[] = Array("id"=>"RATE", "content"=>GetMessage('curr_rates_rate'), "sort"=>"rate", "default"=>true);

$lAdmin->AddHeaders($arHeaders);

// построение списка
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if ($row->bEditMode)
	{
		if ($row->VarsFromForm() && $_REQUEST["FIELDS"])
			$val = $_REQUEST["FIELDS"][$f_ID]["DATE_RATE"];
		else
			$val = $f_DATE_RATE;

		$str = '
		<nobr>'.CalendarDate('FIELDS['.htmlspecialchars($f_ID).'][DATE_RATE]', htmlspecialchars($val), 'form_'.$sTableID, '10').'</nobr>
		<input type="hidden" name="FIELDS['.htmlspecialchars($f_ID).'][CURRENCY]" value="'.htmlspecialchars($f_CURRENCY).'">
		<input type="hidden" name="FIELDS_OLD['.htmlspecialchars($f_ID).'][CURRENCY]" value="'.htmlspecialchars($f_CURRENCY).'">';
		$row->AddViewField("DATE_RATE",$str);
	}

	//<input type="hidden" id="FIELDS['.$f_ID.'][CURRENCY]" name="FIELDS['.$f_ID.'][CURRENCY]" value="'.$f_CURRENCY.'">
	//$row->AddCalendarField("DATE_RATE");
	$row->AddInputField("RATE_CNT",Array("size"=>"3"));
	$row->AddInputField("RATE",Array("size"=>"8"));

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"DEFAULT" => "Y",
		"ACTION"=>$lAdmin->ActionRedirect("currency_rate_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_"))
	);

	if ($CURRENCY_RIGHT=="W")
	{
		$arActions[] = array("SEPARATOR"=>true);

		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"ACTION"=>"if(confirm('".GetMessage('CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);
	}
	
	$row->AddActions($arActions);

}

// "подвал" списка

$lAdmin->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);


if ($CURRENCY_RIGHT=="W")
{
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	));
}


$aContext = array(

	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("CURRENCY_NEW_TITLE"),
		"LINK"=>"currency_rate_edit.php?lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("CURRENCY_NEW_TITLE")
	),
);

$lAdmin->AddAdminContextMenu($aContext);

// проверка на вывод только списка (в случае списка в AJAX, скрипт дальше выполняться не будет)
$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("CURRENCY_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>




<form method="get" action="<?=$APPLICATION->GetCurPage()?>" name="find_form">
<?$filter->Begin();?>
	<tr>
		<td><?echo GetMessage("curr_rates_date1")?>:</td>
		<td>
			<?echo CalendarPeriod("filter_period_from", $filter_period_from, "filter_period_to", $filter_period_to, "find_form", "Y")?>
		</td>
	</tr>

	<tr> 
		<td><?echo GetMessage("curr_rates_curr1")?>:</td>
		<td>
			<?echo CCurrency::SelectBox("filter_currency", $filter_currency, GetMessage("curr_rates_all"), True, "", "") ?>
		</td>
	</tr>


<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));$filter->End();?>
</form>

<?$lAdmin->DisplayList();?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>