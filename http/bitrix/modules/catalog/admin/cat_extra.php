<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");

if ($ex = $APPLICATION->GetException())
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");
	
	$strError = $ex->GetString();
	ShowError($strError);
	
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

/*
$catalogModulePermissions = $APPLICATION->GetGroupRight("catalog");
if ($catalogModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
*/

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_price')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bReadOnly = !$USER->CanDoOperation('catalog_price');

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

// идентификатор таблицы
$sTableID = "tbl_catalog_extra";

// инициализация сортировки
$oSort = new CAdminSorting($sTableID, "ID", "asc");
// инициализация списка
$lAdmin = new CAdminList($sTableID, $oSort);

// инициализация параметров списка - фильтры
$arFilterFields = array();

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

// обработка редактирования (права доступа!)
if ($lAdmin->EditAction() && !$bReadOnly /*$catalogModulePermissions >= "W"*/)
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		if (!CExtra::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("CEN_ERROR_UPDATE"), $ID);

			$DB->Rollback();
		}

		$DB->Commit();
	}
}

// обработка действий групповых и одиночных
if (($arID = $lAdmin->GroupAction()) && !$bReadOnly /*$catalogModulePermissions >= "W"*/)
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CExtra::GetList($by, $order);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CExtra::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("EXTRA_DELETE_ERROR"), $ID);
				}

				$DB->Commit();

				break;
		}
	}
}

$dbResultList = CExtra::GetList($by, $order);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

// установке параметров списка
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("cat_extra_nav")));

// заголовок списка
$arHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"", "default"=>true),
	array("id"=>"NAME","content"=>GetMessage("EXTRA_NAME"), "sort"=>"", "default"=>true),
	array("id"=>"PERCENTAGE", "content"=>GetMessage('EXTRA_PERCENTAGE'),	"sort"=>"", "default"=>true),
);

if (!$bReadOnly)
	$arHeaders[] = array("id"=>"RECALCULATE", "content"=>GetMessage("EXTRA_RECALCULATE"),  "sort"=>"", "default"=>true);

$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();


// построение списка
while ($arExtra = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arExtra);

	$row->AddField("ID", $f_ID);

	if ($bReadOnly)
	{
		$row->AddViewField("NAME", $f_NAME);
		$row->AddViewField("PERCENTAGE", $f_PERCENATGE);
	}
	else
	{
		$row->AddInputField("NAME", array("size" => "35"));
		$row->AddInputField("PERCENTAGE", array("size" => "10"));
		$row->AddCheckField("RECALCULATE");
	}

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("CEN_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("cat_extra_edit.php?ID=".$f_ID."&lang=".LANG /*."&".GetFilterParams("filter_").""*/), "DEFAULT"=>true);
	
	//if ($catalogModulePermissions >= "U")
	if (!$bReadOnly)
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("CEN_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('CEN_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}

// "подвал" списка
$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

// показ формы с кнопками добавления, ...
if (!$bReadOnly)
{
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}

//if ($catalogModulePermissions >= "W")
if (!$bReadOnly)
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("CEN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "cat_extra_edit.php?lang=".LANG,
			"TITLE" => GetMessage("CEN_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

// проверка на вывод только списка (в случае списка, скрипт дальше выполняться не будет)
$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("EXTRA_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?
$lAdmin->DisplayList();
?>

<?
echo BeginNote();
echo GetMessage("EXTRA_NOTES");
echo EndNote();
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>