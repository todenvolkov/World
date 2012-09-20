<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

$forumPermissions = $APPLICATION->GetGroupRight("forum");
if ($forumPermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$ID = intVal($ID);
$arError = $arSmile = $arFields = $arLang = array();
$arLangTitle = array("reference_id" => array(), "reference" => array());

$db_res = CLanguage::GetList(($b="sort"), ($o="asc"));
while ($res = $db_res->Fetch())
{
	$arLang[$res["LID"]] = $res;
	$arLangTitle["reference_id"][] = $res["LID"];
	$arLangTitle["reference"][] = htmlspecialchars($res["NAME"]);
}
$bInitVars = false;
/********************************************************************
				/Input params
********************************************************************/

$APPLICATION->SetTitle($ID > 0 ? GetMessage("FORUM_EDIT_RECORD", array("#ID#" => $ID)) : GetMessage("FORUM_NEW_RECORD"));

/********************************************************************
				Action
********************************************************************/
if ($REQUEST_METHOD == "POST" && $forumPermissions >= "W" && (strlen($save) > 0 || strlen($apply) > 0))
{
	if (!check_bitrix_sessid())
	{
		$arError[] = array(
			"id" => "bad_sessid",
			"text" => GetMessage("ERROR_BAD_SESSID"));
	}
	elseif ($ID > 0 && !CForumNew::CanUserUpdateForum($ID, $USER->GetUserGroupArray(), $USER->GetID()))
	{
		$arError[] = array(
			"id" => "not_right_for_edit",
			"text" => GetMessage("FE_NO_PERMS2UPDATE"));
	}
	elseif ($ID <= 0 && !CForumNew::CanUserAddForum($USER->GetUserGroupArray(), $USER->GetID()))
	{
		$arError[] = array(
			"id" => "not_right_for_add",
			"text" => GetMessage("FE_NO_PERMS2ADD"));
	}
	elseif (!empty($_FILES["IMAGE"]["tmp_name"]))
	{
		$sUploadDir = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/images/forum/".($_REQUEST["TYPE"] == "I" ? "icon" : "smile")."/";
		CheckDirPath($sUploadDir);
		
		$arSmile = ($ID > 0 ? CForumSmile::GetByID($ID) : $arSmile);
		$res = CFile::CheckImageFile($_FILES["IMAGE"], COption::GetOptionString("forum", "file_max_size", 50000), 0, 0);
		
		if (strLen($res) > 0)
		{
			$arError[] = array(
				"id" => "IMAGE", 
				"text" => $res);
		}
		elseif (file_exists($sUploadDir . $_FILES["IMAGE"]["name"]) && !(isset($arSmile["IMAGE"]) && $arSmile["IMAGE"] != $_FILES["IMAGE"]["name"]))
		{
			$arError[] = array(
				"id" => "IMAGE", 
				"text" => GetMessage("ERROR_EXISTS_IMAGE", 
					array("#FILE#" => str_replace("//", "/", "/".BX_ROOT."/images/forum/".($_REQUEST["TYPE"] == "I" ? "icon" : "smile")."/".$_FILES["IMAGE"]["name"]))));
		}
		elseif (!@copy($_FILES["IMAGE"]["tmp_name"], $sUploadDir.$_FILES["IMAGE"]["name"]))
		{
			$arError[] = array(
				"id" => "IMAGE", 
				"text" => GetMessage("ERROR_COPY_IMAGE"));
		}
		else
		{
			@chmod($sUploadDir.$_FILES["IMAGE"]["name"], BX_FILE_PERMISSIONS);
		}
	}
	
	if (empty($arError))
	{
		$GLOBALS["APPLICATION"]->ResetException();
		
		$arFields = array(
			"SORT" => $_REQUEST["SORT"],
			"TYPE" => $_REQUEST["TYPE"],
			"TYPING" => $_REQUEST["TYPING"],
			"DESCRIPTION" => $_REQUEST["DESCRIPTION"], 
			"LANG" => array());
		if (!empty($_FILES["IMAGE"]["tmp_name"]))
			$arFields["IMAGE"] = $_FILES["IMAGE"]["name"];
		foreach ($arLang as $key => $val)
			$arFields["LANG"][$key] = array("LID" => $key, "NAME" => $_REQUEST["NAME_".$key]);

		if ($ID > 0)
			CForumSmile::Update($ID, $arFields);
		else
			$ID = CForumSmile::Add($arFields);
		
		if ($e = $GLOBALS["APPLICATION"]->GetException())
		{
			$arError[] = array(
				"id" => "TYPING", 
				"text" => $e->getString()); 
			if (!empty($_FILES["IMAGE"]["tmp_name"]) && isset($sUploadDir))
			{
				@unlink($sUploadDir.$_FILES["IMAGE"]["name"]);
				unset($arFields["IMAGE"]);
			}
		}
		else
		{
			BXClearCache(true, "/".LANG."/forum/smilesList/");
			BXClearCache(true, "/".LANG."/forum/iconsList/");
			BXClearCache(true, "/".LANG."/forum/smiles/");
			if (!empty($arSmile))
			{
				$res = CForumSmile::GetByID($ID);
				if ($arSmile["TYPE"] != $res["TYPE"] || $arSmile["IMAGE"] != $res["IMAGE"]) 
					@unlink($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/images/forum/".($arSmile["TYPE"] == "I" ? "icon" : "smile")."/".$arSmile["IMAGE"]);
			}
			LocalRedirect(strlen($save) > 0 ? 
				"forum_smile.php?lang=".LANG."&".GetFilterParams("filter_", false) : 
				"forum_smile_edit.php?lang=".LANG."&ID=".$ID."&".GetFilterParams("filter_", false));
		}
	}
	
	$e = new CAdminException($arError);
	$message = new CAdminMessage(($ID > 0 ? GetMessage("ERROR_EDIT_SMILE") : GetMessage("ERROR_ADD_SMILE")), $e);
	$bInitVars = true;
}
/********************************************************************
				/Action
********************************************************************/
if ($bInitVars && !empty($arFields))
{
	$arSmile = $arFields; 
}
elseif ($ID > 0)
{
	$db_res = CForumSmile::GetList(array(), array("ID" => $ID));
	if ($db_res && $arSmile = $db_res->Fetch())
	{
		$arSmile["LANG"] = array();
		foreach ($arLang as $key => $val):
			$name = CForumSmile::GetLangByID($ID, $key);
			$arSmile["LANG"][$key] = array("LID" => $key, "NAME" => $name["NAME"]);
		endforeach;
	}
}
else 
{
	$arSmile = array(
		"SORT" => 150,
		"TYPE" => "S",
		"TYPING" => "",
		"IMAGE" => "", 
		"DESCRIPTION" => "",
		"LANG" => array());
}
foreach ($arSmile as $key => $val):
	if ($key == "LANG")
		continue;
	$arSmile[$key] = htmlspecialchars($val);
endforeach;
foreach ($arSmile["LANG"] as $key => $val):
	$arSmile["LANG"][$key] = array("LID" => htmlspecialchars($val["LID"]), "NAME" => htmlspecialchars($val["NAME"]));
endforeach;
/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$aMenu = array(
	array(
		"TEXT" => GetMessage("FSN_2FLIST"),
		"LINK" => "/bitrix/admin/forum_smile.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_list",
	)
);

if ($ID > 0 && $forumPermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("FSN_NEW_SMILE"),
		"LINK" => "/bitrix/admin/forum_smile_edit.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_new",
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("FSN_DELETE_SMILE"), 
		"LINK" => "javascript:if(confirm('".GetMessage("FSN_DELETE_SMILE_CONFIRM")."')) window.location='/bitrix/admin/forum_smile.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete",
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
if (isset($message) && $message)
	echo $message->Show();

?>
<form method="POST" action="<?=$APPLICATION->GetCurPageParam()?>" name="fform" enctype="multipart/form-data">
	<input type="hidden" name="Update" value="Y" />
	<input type="hidden" name="lang" value="<?=LANG?>" />
	<input type="hidden" name="ID" value="<?=$ID?>" />
	<?=bitrix_sessid_post()?>
<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("FSN_TAB_SMILE"), "ICON" => "forum", "TITLE" => GetMessage("FSN_TAB_SMILE_DESCR"))
	);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();

if ($ID > 0):
?>
	<tr>
		<td width="40%"><?=GetMessage("FORUM_CODE")?>:</td>
		<td width="60%"><?=intVal($ID)?></td>
	</tr>
<?
endif;
?>
	<tr>
		<td width="40%"><?=GetMessage("FORUM_SORT")?>:</td>
		<td width="60%">
			<input type="text" name="SORT" value="<?=$arSmile["SORT"]?>" size="10" />
		</td>
	</tr>

	<tr>
		<td><?=GetMessage("FORUM_TYPE")?>:</td>
		<td>
			<select name="TYPE">
				<option value="S" <?=($arSmile["TYPE"] == "S" ? "selected" : "")?>><?=GetMessage("FSE_SMILE");?></option>
				<option value="I" <?=($arSmile["TYPE"] == "I" ? "selected" : "")?>><?=GetMessage("FSE_ICON");?></option>
			</select>
		</td>
	</tr>

	<tr>
		<td valign="top"><?=GetMessage("FORUM_TYPING")?>:<br><small><?=GetMessage("FORUM_TYPING_NOTE")?></small></td>
		<td valign="top">
			<input type="text" name="TYPING" value="<?=$arSmile["TYPING"]?>" size="40" />
		</td>
	</tr>

	<tr>
		<td>
			<?if ($ID <= 0):?>
				<span class="required">*</span>
			<?endif;?>
			<?=GetMessage("FORUM_IMAGE")?>:<br><small><?=GetMessage("FORUM_IMAGE_NOTE")?></small></td>
		<td>
			<input type="file" name="IMAGE" size="30" />
<?
			if (!empty($arSmile["IMAGE"])):
?>
			<br /><img src="<?=BX_ROOT?>/images/forum/<?=($arSmile["TYPE"] == "I" ? "icon" : "smile")?>/<?=$arSmile["IMAGE"]?>" <?
				?>width="<?=$arSmile["IMAGE_WIDTH"]?>" height="<?=$arSmile["IMAGE_HEIGHT"]?>" />
			&nbsp;<?=BX_ROOT?>/images/forum/<?=($arSmile["TYPE"] == "I" ? "icon" : "smile")?>/<?=$arSmile["IMAGE"]?><?
			endif;
?>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><span class="required">*</span><?=GetMessage("FORUM_IMAGE_NAME")?></td>
	</tr>
	<?foreach ($arLang as $key => $val):?>
		<tr>
			<td><?=$val["NAME"]?> [<?=$key?>]:</td>
			<td><input type="text" name="NAME_<?=$key?>" value="<?=$arSmile["LANG"][$key]["NAME"]?>" size="40" /></td>
		</tr>
	<?endforeach;?>

<?
$tabControl->EndTab();

$tabControl->Buttons(array(
	"disabled" => ($forumPermissions < "W"),
	"back_url" => "/bitrix/admin/forum_smile.php?lang=".LANG."&".GetFilterParams("filter_", false)));
?>
</form>
<?
$tabControl->End();
$tabControl->ShowWarnings("fform", $message);
?>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>