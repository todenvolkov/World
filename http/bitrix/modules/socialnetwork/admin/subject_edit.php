<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$socialnetworkModulePermissions = $APPLICATION->GetGroupRight("socialnetwork");
if ($socialnetworkModulePermissions < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$errorMessage = "";
$bVarsFromForm = false;

$ID = IntVal($ID);

if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && $socialnetworkModulePermissions>="W" && check_bitrix_sessid())
{
	$arFields = array(
		"NAME" => $NAME,
		"SITE_ID" => $SITE_ID,
		"SORT" => $SORT,
	);

	if ($ID > 0)
	{
		$arBlogSubject = CSocNetGroupSubject::GetByID($ID);

		if (!CSocNetGroupSubject::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().". ";
			else
				$errorMessage .= GetMessage("SONETE_ERROR_SAVING").". ";
		}
	}
	else
	{
		$ID = CSocNetGroupSubject::Add($arFields);
		$ID = IntVal($ID);
		if ($ID <= 0)
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().". ";
			else
				$errorMessage .= GetMessage("SONETE_ERROR_SAVING").". ";
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		$arBlogSubjectTmp = CSocNetGroupSubject::GetByID($ID);
//		BXClearCache(True, "/".$arBlogSubjectTmp["SITE_ID"]."/socialnetwork/");
//		if (!empty($arBlogSubject))
//		{
//			BXClearCache(True, "/".$arBlogSubject["SITE_ID"]."/socialnetwork/");
//		}


		if (strlen($apply) <= 0)
			LocalRedirect("/bitrix/admin/socnet_subject.php?lang=".LANG."&".GetFilterParams("filter_", false));
	}
	else
	{
		$bVarsFromForm = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/prolog.php");

if ($ID > 0)
	$APPLICATION->SetTitle(GetMessage("SONETE_UPDATING"));
else
	$APPLICATION->SetTitle(GetMessage("SONETE_ADDING"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

ClearVars("str_");

$str_SORT = "100";

if ($ID > 0)
{
	$arSubject = CSocNetGroupSubject::GetByID($ID);
	if (!$arSubject)
	{
		if ($socialnetworkModulePermissions < "W")
			$errorMessage .= GetMessage("SONETE_NO_PERMS2ADD").". ";
		$ID = 0;
	}
	else
	{
		$str_NAME = $arSubject["NAME"];
		$str_SITE_ID = $arSubject["SITE_ID"];
		$str_SORT = $arSubject["SORT"];		
	}
}

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_socialnetwork_subject", "", "str_");
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("SONETE_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/socnet_subject.php?lang=".LANG."&".GetFilterParams("filter_", false)
	)
);

if ($ID > 0 && $socialnetworkModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("SONETE_NEW_SUBJECT"),
			"ICON" => "btn_new",
			"LINK" => "/bitrix/admin/socnet_subject_edit.php?lang=".LANG."&".GetFilterParams("filter_", false)
		);

	$aMenu[] = array(
			"TEXT" => GetMessage("SONETE_DELETE_SUBJECT"), 
			"ICON" => "btn_delete",
			"LINK" => "javascript:if(confirm('".GetMessage("SONETE_DELETE_SUBJECT_CONFIRM")."')) window.location='/bitrix/admin/socnet_subject.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
			"WARNING" => "Y"
		);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($errorMessage);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SONETE_TAB_SUBJECT"), "ICON" => "socialnetwork", "TITLE" => GetMessage("SONETE_TAB_SUBJECT_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
	<?endif;?>
	<tr>
		<td width="40%"><span class="required">*</span><?echo GetMessage("SONETE_NAME")?>:</td>
		<td width="60%">
			<input type="text" name="NAME" size="50" value="<?= $str_NAME ?>">
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?echo GetMessage("SONETE_SITE")?>:</td>
		<td>
			<?echo CSite::SelectBox("SITE_ID", $str_SITE_ID, "", "", "");?>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("SONETE_SORT")?>:</td>
		<td width="60%">
			<input type="text" name="SORT" size="10" value="<?= $str_SORT ?>">
		</td>
	</tr>	

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($socialnetworkModulePermissions < "W"),
				"back_url" => "/bitrix/admin/socnet_subject.php?lang=".LANG."&".GetFilterParams("filter_", false)
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

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>