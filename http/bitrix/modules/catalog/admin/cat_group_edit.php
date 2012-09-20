<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/*
$catalogPermissions = $APPLICATION->GetGroupRight("catalog");
if ($catalogPermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
*/

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_group')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bReadOnly = !$USER->CanDoOperation('catalog_group');

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");

if ($ex = $APPLICATION->GetException())
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

ClearVars();

$ID = IntVal($ID);

$db_result_lang = CLangAdmin::GetList(($by1="sort"), ($order1="asc"));
$iCount = 0;
while ($db_result_lang_array = $db_result_lang->Fetch())
{
	$arLangsLid[$iCount] = $db_result_lang_array["LID"];
	$arLangsNames[$iCount] = htmlspecialchars($db_result_lang_array["NAME"]);
	$iCount++;
}

$strError = "";
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && !$bReadOnly /*$catalogPermissions=="W"*/ && check_bitrix_sessid())
{
	$str_NAME = Trim($NAME);
	$str_SORT = IntVal($VSORT);
	if (strlen($str_NAME)<=0)
		$strError = GetMessage("ERROR_EMPTY_NAME")."<br>";
	if (!is_array($GROUP_ID) || count($GROUP_ID)<=0)
		$strError = GetMessage("ERROR_EMPTY_GROUP")."<br>";
	if (!is_array($GROUP_ID_BUY) || count($GROUP_ID_BUY)<=0)
		$strError = GetMessage("ERROR_EMPTY_GROUP_BUY")."<br>";

	if (strlen($strError)<=0)
	{
		unset($arFields);
		$arFields["NAME"] = $str_NAME;
		$arFields["SORT"] = $str_SORT;

		if (is_set($BASE)) $arFields["BASE"] = 'Y';

		for ($i = 0; $i<count($GROUP_ID); $i++)
		{
			if (IntVal($GROUP_ID[$i])>0)
			{
				$arFields["USER_GROUP"][] = IntVal($GROUP_ID[$i]);
			}
		}

		for ($i = 0; $i<count($GROUP_ID_BUY); $i++)
		{
			if (IntVal($GROUP_ID_BUY[$i])>0)
			{
				$arFields["USER_GROUP_BUY"][] = IntVal($GROUP_ID_BUY[$i]);
			}
		}

		$arLangNames = array();
		for ($i=0; $i<$iCount; $i++)
		{
			$arLangNames[$arLangsLid[$i]] = Trim(${"NAME_".$arLangsLid[$i]});
		}
		$arFields["USER_LANG"] = $arLangNames;

		if ($ID>0)
		{
			if (!CCatalogGroup::Update($ID, $arFields))
				$strError = GetMessage("ERROR_UPDATING_TYPE")."<br>";
		}
		else
		{
			$ID = CCatalogGroup::Add($arFields);
			if ($ID<=0)
				$strError = GetMessage("ERROR_ADDING_TYPE")."<br>";
		}
	}

	if (strlen($strError) <= 0)
	{
		if (strlen($save)>0)
			LocalRedirect("cat_group_admin.php?lang=".LANG);
		elseif (strlen($apply)>0)
			LocalRedirect("cat_group_edit.php?lang=".LANG.'&ID='.$ID);
	}
}

if (!($arGroup = CCatalogGroup::GetByID($ID)))
{
	$ID = 0;
	$arGROUP_ID = array();
	$arGROUP_ID_BUY = array();
}
else
{
	$str_ID = $arGroup["ID"];
	$str_NAME = $arGroup["NAME"];
	$str_BASE = $arGroup["BASE"];
	$str_SORT = $arGroup["SORT"];
	$arGROUP_ID = array();
	$arGROUP_ID_BUY = array();

	$db_grl = CCatalogGroup::GetGroupsList(array("CATALOG_GROUP_ID"=>$ID));
	while ($grl = $db_grl->Fetch())
	{
		if ($grl["BUY"]=="Y")
			$arGROUP_ID_BUY[] = IntVal($grl["GROUP_ID"]);
		else
			$arGROUP_ID[] = IntVal($grl["GROUP_ID"]);
	}
}

$bInitVars = false;
if (strlen($strError)>0)
{
	$DB->InitTableVarsForEdit("b_catalog_group", "", "str_");

	for ($i = 0; $i<count($GROUP_ID); $i++)
	{
		if (IntVal($GROUP_ID[$i])>0)
		{
			$arGROUP_ID[] = IntVal($GROUP_ID[$i]);
		}
	}

	for ($i = 0; $i<count($GROUP_ID_BUY); $i++)
	{
		if (IntVal($GROUP_ID_BUY[$i])>0)
		{
			$arGROUP_ID_BUY[] = IntVal($GROUP_ID_BUY[$i]);
		}
	}

	$str_SORT = $VSORT;
	$bInitVars = true;
}

$sDocTitle = ($ID>0) ? GetMessage("CAT_EDIT_RECORD", array("#ID#" => $ID)) : GetMessage("CAT_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("CGEN_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/cat_group_admin.php?lang=".LANG."&".GetFilterParams("filter_", false)
	)
);

if ($ID > 0 && !$bReadOnly /*$catalogPermissions == "W"*/)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("CGEN_NEW_GROUP"),
		"ICON" => "btn_new",
		"LINK" => "/bitrix/admin/cat_group_edit.php?lang=".LANG."&".GetFilterParams("filter_", false)
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("CGEN_DELETE_GROUP"),
		"ICON" => "btn_delete",
		"LINK" => "javascript:if(confirm('".GetMessage("CGEN_DELETE_GROUP_CONFIRM")."')) window.location='/bitrix/admin/cat_group_admin.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"WARNING" => "Y"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($strError);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="catalog_edit">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("CGEN_TAB_GROUP"), "ICON" => "catalog", "TITLE" => GetMessage("CGEN_TAB_GROUP_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID>0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?echo $ID ?></td>
		</tr>
	<?endif;?>
	<tr>
		<td valign="top"><?echo GetMessage("BASE") ?></td>
		<td valign="top"><?if ($str_BASE != 'Y'):?>
			<input type="checkbox" id="ch_BASE" name="BASE"<?=($str_BASE == "Y") ? " checked=\"checked\"" : "" ?> value="Y" />
<?
	echo BeginNote();
	echo GetMessage("BASE_COMMENT");
	echo EndNote();
?>
<?else:?>
	<?=GetMessage('BASE_YES')?><br />
<?
	echo BeginNote();
	echo GetMessage("BASE_COMMENT_Y");
	echo EndNote();
?>

<?endif?>
		</td>
	</tr>
	<tr>
		<td width="40%"><span class="required">*</span><?echo GetMessage("CODE") ?></td>
		<td width="60%">
			<input type="text" name="NAME" value="<?echo htmlspecialcharsEx($str_NAME) ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SORT") ?></td>
		<td>
			<input type="text" name="VSORT" value="<?echo $str_SORT ?>">
		</td>
	</tr>
	<?
	for ($i = 0; $i < $iCount; $i++)
	{
		$arcglang = CCatalogGroup::GetByID($ID, $arLangsLid[$i]);
		$str_NAME_LANG = htmlspecialcharsEx($arcglang["NAME_LANG"]);
		if ($bInitVars)
			$str_NAME_LANG = htmlspecialcharsEx(${"NAME_".$arLangsLid[$i]});
		?>
		<tr>
			<td><?echo GetMessage("NAME") ?> (<?echo $arLangsNames[$i];?>):</td>
			<td>
				<input type="text" name="NAME_<?echo $arLangsLid[$i] ?>" value="<?echo $str_NAME_LANG ?>">
			</td>
		</tr>
		<?
	}
	?>
	<tr>
		<td>
			<span class="required">*</span><?echo GetMessage('CAT_GROUPS');?><br><img src="/bitrix/images/catalog/mouse.gif" width="44" height="21" border=0 alt="">
		</td>
		<td>
			<select name="GROUP_ID[]" multiple size="7">
			<?
			$db_grl = CGroup::GetList(($by="sort"), ($order="asc"));
			while ($grl = $db_grl->Fetch())
			{
				?><option value="<?echo $grl["ID"] ?>"<?if (in_array(IntVal($grl["ID"]), $arGROUP_ID)) echo " selected"?>><?echo "[".$grl["ID"]."] ".htmlspecialchars($grl["NAME"]) ?></option><?
			}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<span class="required">*</span><?echo GetMessage('CAT_GROUPS_BUY');?><br><img src="/bitrix/images/catalog/mouse.gif" width="44" height="21" border=0 alt="">
		</td>
		<td>
			<select name="GROUP_ID_BUY[]" multiple size="7">
			<?
			$db_grl = CGroup::GetList(($by="sort"), ($order="asc"));
			while ($grl = $db_grl->Fetch())
			{
				?><option value="<?echo $grl["ID"] ?>"<?if (in_array(IntVal($grl["ID"]), $arGROUP_ID_BUY)) echo " selected"?>><?echo "[".$grl["ID"]."] ".htmlspecialchars($grl["NAME"]) ?></option><?
			}
			?>
			</select>
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				//"disabled" => ($catalogPermissions < "W"),
				"disabled" => $bReadOnly,
				"back_url" => "/bitrix/admin/cat_group_admin.php?lang=".LANG."&".GetFilterParams("filter_", false)
			)
	);
?>

<?
$tabControl->End();
?>

</form>

<?echo BeginNote();?>
<span class="required">*</span> <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote(); ?>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>