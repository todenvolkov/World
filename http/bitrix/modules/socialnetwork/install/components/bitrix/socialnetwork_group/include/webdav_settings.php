<?
if (!function_exists("__wd_check_uf_use_bp_property"))
{
	function __wd_check_uf_use_bp_property($iblock_id)
	{
		$iblock_id = intval($iblock_id); 
		$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOCK_".$iblock_id."_SECTION", "FIELD_NAME" => "UF_USE_BP"));
		if (!$db_res || !($res = $db_res->GetNext()))
		{
			$arFields = Array(
				"ENTITY_ID" => "IBLOCK_".$iblock_id."_SECTION",
				"FIELD_NAME" => "UF_USE_BP",
				"USER_TYPE_ID" => "string",
				"MULTIPLE" => "N",
				"MANDATORY" => "N", 
				"SETTINGS" => array("DEFAULT_VALUE" => "Y"));
			$arFieldName = array();
			$rsLanguage = CLanguage::GetList($by, $order, array());
			while($arLanguage = $rsLanguage->Fetch()):
//				GetMessage("SONET_UF_USE_BP");
				$dir = str_replace(array("\\", "//"), "/", dirname(__FILE__)); 
				$dirs = explode("/", $dir); 
				array_pop($dirs); 
				$file = trim(implode("/", $dirs)."/lang/".$arLanguage["LID"]."/include/webdav_settings.php");
				$tmp_mess = __IncludeLang($file, true);
				$arFieldName[$arLanguage["LID"]] = (empty($tmp_mess["SONET_UF_USE_BP"]) ? "Use Business Process" : $tmp_mess["SONET_UF_USE_BP"]);
			endwhile;
			$arFields["EDIT_FORM_LABEL"] = $arFieldName;
			$obUserField  = new CUserTypeEntity;
			$obUserField->Add($arFields);
			$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
		}
	}
}
if (defined("WEBDAV_SETTINGS_LIMIT_INCLUDE"))
	return true; 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$dir = str_replace(array("\\", "//"), "/", dirname(__FILE__)); 
$dirs = explode("/", $dir); 
array_pop($dirs); 
$file = trim(implode("/", $dirs)."/lang/".LANGUAGE_ID."/include/webdav_settings.php");
__IncludeLang($file);
$res = explode("_", $_REQUEST["DOCUMENT_ID"]); 
$arParams = array();
$arParams["IBLOCK_ID"] = $IBLOCK_ID = intval($res[1]); 
$object = trim($res[2]); 
$object_id = intval($res[3]); 

$popupWindow = new CJSPopup('', '');

if (!CModule::IncludeModule("iblock"))
	$popupWindow->ShowError(GetMessage("SONET_IB_MODULE_IS_NOT_INSTALLED"));
elseif (!CModule::IncludeModule("webdav"))
	$popupWindow->ShowError(GetMessage("SONET_WD_MODULE_IS_NOT_INSTALLED"));
elseif ($IBLOCK_ID <= 0)
	$popupWindow->ShowError(GetMessage("SONET_IBLOCK_ID_EMPTY"));
elseif ($object_id <= 0 && ($object != "user" && $object != "group"))
	$popupWindow->ShowError(GetMessage("SONET_GROUP_NOT_EXISTS"));

$res = CIBlockWebdavSocnet::GetUserMaxPermission(
	$object, 
	$object_id, 
	$USER->GetID(), 
	$IBLOCK_ID);
$arParams["PERMISSION"] = $res["PERMISSION"];
$arParams["CHECK_CREATOR"] = $res["CHECK_CREATOR"];
if ($arParams["PERMISSION"] < "W" || $arParams["CHECK_CREATOR"] == "Y")
	$popupWindow->ShowError(GetMessage("SONET_ACCESS_DENIED")); 

$arFilter = array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SOCNET_GROUP_ID" => false, 
	"SECTION_ID" => 0);
if ($object == "user")
	$arFilter["CREATED_BY"] = $object_id;
else
	$arFilter["SOCNET_GROUP_ID"] = $object_id;
$arLibrary = array();
$db_res = CIBlockSection::GetList(array(), $arFilter, false, array("ID", "UF_USE_BP"));
if (!($db_res && $arLibrary = $db_res->GetNext()))
{
	$popupWindow->ShowError(GetMessage("SONET_WEBDAV_NOT_EXISTS")); 
}
else
{
	$arLibrary["UF_USE_BP"] = ($arLibrary["UF_USE_BP"] == "N" ? "N" : "Y"); 
}

//Save permissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
{
	CUtil::JSPostUnescape();
	$strWarning = GetMessage("MAIN_SESSION_EXPIRED");
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["save"]))
{
	$_REQUEST["UF_USE_BP"] = ($_REQUEST["UF_USE_BP"] == "Y" ? "Y" : "N"); 
	if ($_REQUEST["UF_USE_BP"] != $arLibrary["UF_USE_BP"])
	{
		if (!isset($arLibrary["~UF_USE_BP"]))
		{
			__wd_check_uf_use_bp_property($arParams["IBLOCK_ID"]); 
		}
		$arFields = Array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"UF_USE_BP" => $_REQUEST["UF_USE_BP"]);
		$GLOBALS["UF_USE_BP"] = $arFields["UF_USE_BP"];
		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
		$bs = new CIBlockSection(); 
		$res = $bs->Update($arLibrary["ID"], $arFields);
	}
	$popupWindow->Close($bReload = true, $_REQUEST["back_url"]);
	die();
}

//HTML output
$popupWindow->ShowTitlebar(GetMessage("SN_TITLE"));
$popupWindow->StartDescription("bx-access-folder");
if (isset($strWarning) && $strWarning != "")
	$popupWindow->ShowValidationError($strWarning);
?>

<p><b><?=GetMessage("SN_TITLE_TITLE")?></b></p>

<?
$popupWindow->EndDescription();
$popupWindow->StartContent();
?>
<p></p>
<table class="bx-width100" id="bx_permission_table">
	<tr>
		<td width="35%" align="right"><?=GetMessage("SN_BP")?></td>
		<td><input type="checkbox" name="UF_USE_BP" id="UF_USE_BP" value="Y" <?
			?><?=($arLibrary["UF_USE_BP"] == "N" ? '' : ' checked="checked" ')
			?> />&nbsp;<label for="UF_USE_BP"><?=GetMessage("SN_BP_LABEL")?></label> </td>
	</tr>
</table>
<div style="background-color:#FEFDEA; margin-bottom:16px; margin-top:16px; border:1px solid #D7D6BA; width:679px; position: relative; display:block; padding: 0 4px;">
<table class='notes' style='display:block;'>
<tr><td class='content'>
<?=GetMessage("SN_BP_NOTE")?>
</td></tr></table>
</div>
<input type="hidden" name="save" value="Y" />
<?
$popupWindow->EndContent();
$popupWindow->ShowStandardButtons();
?>
<script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>
