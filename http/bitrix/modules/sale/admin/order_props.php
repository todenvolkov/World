<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

ClearVars("l_");

$sTableID = "tbl_sale_order_props";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_person_type_id",
	"filter_type",
	"filter_user",
	"filter_group",
	"filter_code",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (IntVal($filter_person_type_id)>0) $arFilter["PERSON_TYPE_ID"] = IntVal($filter_person_type_id);
if (strlen($filter_type)>0) $arFilter["TYPE"] = Trim($filter_type);
if (strlen($filter_user)>0) $arFilter["USER_PROPS"] = Trim($filter_user);
if (IntVal($filter_group)>0) $arFilter["PROPS_GROUP_ID"] = IntVal($filter_group);
if (strlen($filter_code)>0) $arFilter["CODE"] = Trim($filter_code);

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSaleOrderProps::GetList(
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

				if (!CSaleOrderProps::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SOPAN_ERROR_DELETE"), $ID);
				}

				$DB->Commit();

				break;
		}
	}
}

$dbResultList = CSaleOrderProps::GetList(
	array($by => $order),
	$arFilter
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SALE_PRLIST")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"ID", "default"=>true),
	array("id"=>"PERSON_TYPE_ID","content"=>GetMessage("SALE_PERSON_TYPE"), "sort"=>"PERSON_TYPE_ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('SALE_FIELD_NAME'),	"sort"=>"NAME", "default"=>true),
	array("id"=>"CODE", "content"=>GetMessage('SALE_FIELD_CODE'),	"sort"=>"CODE", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('SALE_FIELD_SORT'),	"sort"=>"SORT", "default"=>true),
	array("id"=>"TYPE", "content"=>GetMessage("SALE_FIELD_TYPE"),  "sort"=>"TYPE", "default"=>true),
	array("id"=>"REQUIED", "content"=>GetMessage("SALE_REQUIED"),  "sort"=>"REQUIED", "default"=>true),
	array("id"=>"PROPS_GROUP_ID", "content"=>GetMessage("SALE_GROUP"),  "sort"=>"PROPS_GROUP_ID", "default"=>true),
	array("id"=>"USER_PROPS", "content"=>GetMessage("SALE_USER"),  "sort"=>"USER_PROPS", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arOrderProp = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arOrderProp);

	//$row->AddField("ID", $f_ID);
	$row->AddField("ID", "<b><a href='sale_order_props_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_")."' title='".GetMessage("SALE_EDIT_DESCR")."'>".$f_ID."</a>");


	$fieldValue = "";
	if (in_array("PERSON_TYPE_ID", $arVisibleColumns))
	{
		$arPersType = CSalePersonType::GetByID($f_PERSON_TYPE_ID);
		$fieldValue  = "[".$arPersType["ID"]."] ";
		$fieldValue .= htmlspecialcharsEx($arPersType["NAME"])." ";
		$fieldValue .= "(".htmlspecialcharsEx($arPersType["LID"]).")";
	}
	$row->AddField("PERSON_TYPE_ID", $fieldValue);

	$row->AddField("NAME", $f_NAME);
	$row->AddField("SORT", $f_SORT);
	$row->AddField("CODE", $f_CODE);
	$row->AddField("TYPE", "[".$f_TYPE."] ".$SALE_FIELD_TYPES[$f_TYPE]."");
	$row->AddField("REQUIED", (($f_REQUIED=="Y") ? GetMessage("SOP_YES") : GetMessage("SOP_NO")));

	$fieldValue = "";
	if (in_array("PROPS_GROUP_ID", $arVisibleColumns))
	{
		$arPropsGroup = CSaleOrderPropsGroup::GetByID($f_PROPS_GROUP_ID);
		$fieldValue = htmlspecialcharsEx($arPropsGroup["NAME"]);
	}
	$row->AddField("PROPS_GROUP_ID", $fieldValue);

	$row->AddField("USER_PROPS", (($f_USER_PROPS=="Y") ? GetMessage("SOP_YES") : GetMessage("SOP_NO")));

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SALE_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_order_props_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_")), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SALE_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessage('SALE_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")
	)
);

if ($saleModulePermissions == "W")
{
	$arDDMenu = array();

	$arDDMenu[] = array(
		"TEXT" => "<b>".GetMessage("SOPAN_4NEW_PROMT")."</b>",
		"ACTION" => false
	);

	$dbRes = CSalePersonType::GetList(
		array("NAME" => "ASC"),
		array(),
		false,
		false,
		array("ID", "NAME", "LID")
	);
	while ($arRes = $dbRes->Fetch())
	{
		$arDDMenu[] = array(
			"TEXT" => "[".$arRes["ID"]."] ".$arRes["NAME"]." (".$arRes["LID"].")",
			"ACTION" => "window.location = 'sale_order_props_edit.php?lang=".LANG."&PERSON_TYPE_ID=".$arRes["ID"]."';"
		);
	}

	$aContext = array(
		array(
			"TEXT" => GetMessage("SOPAN_ADD_NEW"),
			"ICON" => "btn_new",
			"TITLE" => GetMessage("SOPAN_ADD_NEW_ALT"),
			"MENU" => $arDDMenu
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/

$APPLICATION->SetTitle(GetMessage("SALE_SECTION_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SALE_F_TYPE"),
		GetMessage("SALE_F_USER"),
		GetMessage("SALE_F_GROUP"),
		GetMessage("SALE_F_CODE"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SALE_F_PERS_TYPE");?>:</td>
		<td>
			<select name="filter_person_type_id">
				<option value="">(<?echo GetMessage("SALE_ALL")?>)</option>
				<?
				$l = CSalePersonType::GetList(($b="NAME"), ($o="ASC"), Array());
				while ($l->ExtractFields("l_")):
					?><option value="<?echo $l_ID?>"<?if (IntVal($filter_person_type_id)==IntVal($l_ID)) echo " selected"?>>[<?echo $l_ID ?>] <?echo $l_NAME?> (<?echo $l_LID ?>)</option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_TYPE")?>:</td>
		<td>
			<select name="filter_type">
				<option value="">(<?echo GetMessage("SALE_ALL")?>)</option>
				<?
				foreach ($SALE_FIELD_TYPES as $key => $value):
					?><option value="<?echo $key?>"<?if ($filter_type==$key) echo " selected"?>>[<?echo htmlspecialchars($key) ?>] <?echo htmlspecialchars($value) ?></option><?
				endforeach;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_USER")?>:</td>
		<td>
			<select name="filter_user">
				<option value="">(<?echo GetMessage("SALE_ALL")?>)</option>
				<option value="Y"<?if ($filter_user=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
				<option value="N"<?if ($filter_user=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_GROUP");?>:</td>
		<td>
			<select name="filter_group">
				<option value="">(<?echo GetMessage("SALE_ALL")?>)</option>
				<?
				$l = CSaleOrderPropsGroup::GetList(($b="NAME"), ($o="ASC"), Array());
				while ($l->ExtractFields("l_")):
					$arPT = CSalePersonType::GetByID($l_PERSON_TYPE_ID);
					?><option value="<?echo $l_ID?>"<?if (IntVal($filter_group)==IntVal($l_ID)) echo " selected"?>>[<?echo $l_ID ?>] <?echo htmlspecialcharsEx($l_NAME)?> <?if ($arPT) echo "(".htmlspecialcharsEx($arPT["NAME"]).")";?></option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_CODE")?>:</td>
		<td>
			<input type="text" name="filter_code" value="<?=htmlspecialcharsEx($filter_code)?>">
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

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>