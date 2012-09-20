<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/*
$catalogModulePermissions = $APPLICATION->GetGroupRight("catalog");
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

$sTableID = "tbl_catalog_discount_coupon";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_discount_id",
	"filter_active",
	"filter_coupon"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (strlen($filter_coupon) > 0) $arFilter["COUPON"] = $filter_coupon;
if (strlen($filter_discount_id) > 0) $arFilter["DISCOUNT_ID"] = $filter_discount_id;
if (strlen($filter_active) > 0) $arFilter["ACTIVE"] = $filter_active;

if ($lAdmin->EditAction() && !$bReadOnly /*$catalogModulePermissions >= "W"*/)
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		if (!CCatalogDiscountCoupon::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_DISCOUNT_CPN")), $ID);

			$DB->Rollback();
		}

		$DB->Commit();
	}
}


if (($arID = $lAdmin->GroupAction()) && !$bReadOnly /*$catalogModulePermissions >= "W"*/)
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CCatalogDiscountCoupon::GetList(
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

				if (!CCatalogDiscountCoupon::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_DELETE_DISCOUNT_CPN")), $ID);
				}

				$DB->Commit();

				break;

			case "activate":
			case "deactivate":

				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);

				if (!CCatalogDiscountCoupon::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_DISCOUNT_CPN")), $ID);
				}

				break;
		}
	}
}

$dbResultList = CCatalogDiscountCoupon::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("ID", "DISCOUNT_ID", "ACTIVE", "COUPON", "DATE_APPLY", "DISCOUNT_NAME", "ONE_TIME")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("DSC_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME","content"=>GetMessage("DSC_CPN_NAME"), "sort"=>"", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("DSC_CPN_ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"COUPON", "content"=>GetMessage("DSC_CPN_CPN"), "sort"=>"COUPON", "default"=>true),
	array("id"=>"DATE_APPLY", "content"=>GetMessage("DSC_CPN_DATE"), "sort"=>"DATE_APPLY", "default"=>true),
	array("id"=>"ONE_TIME", "content"=>GetMessage("DSC_CPN_TIME"), "sort"=>"ONE_TIME", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arDiscount = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arDiscount);

	$row->AddField("ID", $f_ID);
	$row->AddField("NAME", $f_DISCOUNT_NAME);
	
	if ($bReadOnly)
	{
		$row->AddViewField("ACTIVE", $f_ACTIVE);
		$row->AddViewField("COUPON", $f_COUPON);
		$row->AddViewField("DATE_APPLY", $f_DATE_APPLY);
		$row->AddViewField("ONE_TIME", $f_ONE_TIME);
	}
	else
	{
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("COUPON", array("size" => "30"));
		$row->AddCalendarField("DATE_APPLY", array("size" => "10"));
		$row->AddCheckField("ONE_TIME");
	}

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("DSC_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("cat_discount_coupon_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_", false).""), "DEFAULT"=>true);
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
			"TEXT" => GetMessage("DSC_CPN_ADD"),
			"ICON" => "btn_new",
			"LINK" => "cat_discount_coupon_edit.php?lang=".LANG,
			"TITLE" => GetMessage("DSC_CPN_ADD_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("DSC_CPN_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("DSC_CPN_ACT"),
		GetMessage("DSC_CPN_CPN"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?= GetMessage("DSC_CPN_DISC") ?>:</td>
		<td>
			<select name="filter_discount_id">
				<option value=""><?= GetMessage("DSC_CPN_ALL") ?></option>
				<?
				$dbDiscountList = CCatalogDiscount::GetList(
					array("NAME" => "ASC"),
					array(),
					false,
					false,
					array("ID", "SITE_ID", "NAME")
				);
				while ($arDiscountList = $dbDiscountList->Fetch())
				{
					?><option value="<?= $arDiscountList["ID"] ?>"<?if ($filter_discount_id == $arDiscountList["ID"]) echo " selected";?>><?= htmlspecialchars("[".$arDiscountList["ID"]."] ".$arDiscountList["NAME"]." (".$arDiscountList["SITE_ID"].")") ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_CPN_ACT") ?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?= htmlspecialcharsex("(".GetMessage("DSC_CPN_ALL").")") ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?= htmlspecialcharsex(GetMessage("DSC_CPN_YES")) ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?= htmlspecialcharsex(GetMessage("DSC_CPN_NO")) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_CPN_CPN") ?>:</td>
		<td>
			<input type="text" name="filter_coupon" value="<?echo htmlspecialchars($filter_coupon)?>" />
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