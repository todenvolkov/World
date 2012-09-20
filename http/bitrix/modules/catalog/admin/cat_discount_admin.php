<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/*$catalogModulePermissions = $APPLICATION->GetGroupRight("catalog");
if ($catalogModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
*/
if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_discount')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bReadOnly = !$USER->CanDoOperation('catalog_discount');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");

if ($ex = $APPLICATION->GetException())
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");
	
	$strError = $ex->GetString();
	ShowError($strError);
	
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

$sTableID = "tbl_catalog_discount";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_site_id",
	"filter_active",
	"filter_date_active_from",
	"filter_date_active_to",
	"filter_name",
	"filter_coupon",
	"filter_renewal"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (strlen($filter_site_id) > 0 && $filter_site_id != "NOT_REF") $arFilter["SITE_ID"] = $filter_site_id;
if (strlen($filter_active) > 0) $arFilter["ACTIVE"] = $filter_active;
if (strlen($filter_date_active_from) > 0) $arFilter["!>ACTIVE_FROM"] = $filter_date_active_from;
if (strlen($filter_date_active_to) > 0) $arFilter["!<ACTIVE_TO"] = $filter_date_active_to;
if (strlen($filter_name) > 0) $arFilter["~NAME"] = $filter_name;
if (strlen($filter_coupon) > 0) $arFilter["COUPON"] = $filter_coupon;
if (strlen($filter_renewal) > 0) $arFilter["RENEWAL"] = $filter_renewal;

if (!$bReadOnly && $lAdmin->EditAction()/* && $catalogModulePermissions >= "W"*/)
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		if (!CCatalogDiscount::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_DISCOUNT")), $ID);

			$DB->Rollback();
		}

		$DB->Commit();
	}
}


if (!$bReadOnly && ($arID = $lAdmin->GroupAction())/* && $catalogModulePermissions >= "W"*/)
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CCatalogDiscount::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);
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

				if (!CCatalogDiscount::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_DELETE_DISCOUNT")), $ID);
				}

				$DB->Commit();

				break;

			case "activate":
			case "deactivate":

				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);

				if (!CCatalogDiscount::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_DISCOUNT")), $ID);
				}

				break;
		}
	}
}

$dbResultList = CCatalogDiscount::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("ID", "SITE_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO", "RENEWAL", "NAME", "MAX_USES", "COUNT_USES", "SORT", "MAX_DISCOUNT", "VALUE_TYPE", "VALUE", "CURRENCY", "MIN_ORDER_SUM", "TIMESTAMP_X", "NOTES")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("DSC_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"SITE_ID","content"=>GetMessage("DSC_SITE"), "sort"=>"site_id", "default"=>true),
	array("id"=>"ACTIVE_FROM", "content"=>GetMessage('DSC_PERIOD_FROM'),	"sort"=>"active_from", "default"=>true),
	array("id"=>"ACTIVE_TO", "content"=>GetMessage("DSC_PERIOD_TO"),  "sort"=>"active_to", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("DSC_ACT"), "sort"=>"active", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("DSC_NAME"), "sort"=>"name", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("DSC_SORT"), "sort"=>"sort", "default"=>true),
	array("id"=>"VALUE", "content"=>GetMessage("DSC_VALUE"), "sort"=>"", "default"=>true),
	array("id"=>"RENEWAL", "content"=>GetMessage("DSC_REN"), "sort"=>"renewal", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arDiscount = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arDiscount);

	$row->AddField("ID", $f_ID);
	$row->AddField("SITE_ID", $f_SITE_ID);

	if ($bReadOnly)
	{
		$row->AddViewField("ACTIVE_FROM", $f_ACTIVE_FROM);
		$row->AddViewField("ACTIVE_TO", $f_ACTIVE_TO);
		$row->AddViewField("ACTIVE", $f_ACTIVE);
		$row->AddViewField("NAME", $f_NAME);
		$row->AddViewField("SORT", $f_SORT);
	}
	else
	{
		$row->AddCalendarField("ACTIVE_FROM", array("size" => "10"));
		$row->AddCalendarField("ACTIVE_TO", array("size" => "10"));
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("NAME", array("size" => "30"));
		$row->AddInputField("SORT", array("size" => "3"));
	}

	$row->AddField("VALUE", (($arDiscount["VALUE_TYPE"]=="P") ? $arDiscount["VALUE"]."%" : FormatCurrency($arDiscount["VALUE"], $arDiscount["CURRENCY"])));
	$row->AddField("RENEWAL", ($arDiscount["RENEWAL"]=="Y" ? GetMessage("DSC_YES_P") : GetMessage("DSC_NO_P")));

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("DSC_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("cat_discount_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_", false).""), "DEFAULT"=>true);
	//if ($catalogModulePermissions >= "U")
	if (!$bReadOnly)
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("DSC_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('DSC_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}

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

if (!$bReadOnly)
{
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
			"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
			"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		)
	);
}

//if ($catalogModulePermissions >= "W")
if (!$bReadOnly)
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("CDAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "cat_discount_edit.php?lang=".LANG,
			"TITLE" => GetMessage("CDAN_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);

}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("DISCOUNT_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("DSC_ACTIVE"),
		GetMessage("DSC_PERIOD"),
		GetMessage("DSC_NAME"),
		GetMessage("DSC_COUPON"),
		GetMessage("DSC_RENEW")
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?= GetMessage("DSC_SITE") ?>:</td>
		<td>
			<?echo CSite::SelectBox("filter_site_id", $filter_site_id, "(".GetMessage("DSC_ALL").")"); ?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_ACTIVE") ?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?= htmlspecialcharsex("(".GetMessage("DSC_ALL").")") ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?= htmlspecialcharsex(GetMessage("DSC_YES")) ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?= htmlspecialcharsex(GetMessage("DSC_NO")) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_PERIOD") ?> (<?= CSite::GetDateFormat("SHORT") ?>):</td>
		<td>
			<?echo CalendarPeriod("filter_date_active_from", $filter_date_active_from, "filter_date_active_to", $filter_date_active_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_NAME") ?>:</td>
		<td>
		   <input type="text" name="filter_name" size="50" value="<?echo htmlspecialcharsex($filter_name)?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_COUPON") ?>:</td>
		<td>
		   <input type="text" name="filter_coupon" size="50" value="<?echo htmlspecialcharsex($filter_coupon)?>" size="30">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_RENEW") ?>:</td>
		<td>
			<select name="filter_renewal">
				<option value=""><?= htmlspecialcharsex("(".GetMessage("DSC_ALL").")") ?></option>
				<option value="Y"<?if ($filter_renewal=="Y") echo " selected"?>><?= htmlspecialcharsex(GetMessage("DSC_YES")) ?></option>
				<option value="N"<?if ($filter_renewal=="N") echo " selected"?>><?= htmlspecialcharsex(GetMessage("DSC_NO")) ?></option>
			</select>
		</td>
	</tr>
<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>