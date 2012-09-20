<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent("bitrix:socialnetwork.group_menu", "", Array(
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_MODS" => $arResult["PATH_TO_GROUP_MODS"],
		"PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
		"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
		"PATH_TO_GROUP_REQUEST_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
		"PATH_TO_GROUP_REQUESTS" => $arResult["PATH_TO_GROUP_REQUESTS"],
		"PATH_TO_GROUP_REQUESTS_OUT" => $arResult["PATH_TO_GROUP_REQUESTS_OUT"],
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
		"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
		"PATH_TO_GROUP_PHOTO" => $arResult["PATH_TO_GROUP_PHOTO"],
		"PATH_TO_GROUP_FORUM" => $arResult["PATH_TO_GROUP_FORUM"],
		"PATH_TO_GROUP_CALENDAR" => $arResult["PATH_TO_GROUP_CALENDAR"],
		"PATH_TO_GROUP_FILES" => $arResult["PATH_TO_GROUP_FILES"],
		"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
		"PATH_TO_GROUP_CONTENT_SEARCH" => $arResult["PATH_TO_GROUP_CONTENT_SEARCH"],
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"PAGE_ID" => "group_files"),
	$component
);
?>

<?
$arGroup = $APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group", 
	"short", 
	Array(
		"PATH_TO_USER" => $arParams["PATH_TO_USER"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
		"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
		"PATH_TO_GROUP_REQUEST_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
		"PATH_TO_USER_REQUEST_GROUP" => $arResult["PATH_TO_USER_REQUEST_GROUP"],
		"PATH_TO_GROUP_REQUESTS" => $arResult["PATH_TO_GROUP_REQUESTS"],
		"PATH_TO_GROUP_REQUESTS_OUT" => $arResult["PATH_TO_GROUP_REQUESTS_OUT"],
		"PATH_TO_GROUP_MODS" => $arResult["PATH_TO_GROUP_MODS"],
		"PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
		"PATH_TO_USER_LEAVE_GROUP" => $arResult["PATH_TO_USER_LEAVE_GROUP"],
		"PATH_TO_GROUP_DELETE" => $arResult["PATH_TO_GROUP_DELETE"],
		"PATH_TO_GROUP_FEATURES" => $arResult["PATH_TO_GROUP_FEATURES"],
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
		"PATH_TO_SEARCH" => $arResult["PATH_TO_SEARCH"],
		"PATH_TO_MESSAGE_TO_GROUP" => $arResult["PATH_TO_MESSAGE_TO_GROUP"], 
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
		"SET_TITLE" => "N", 
		"SET_NAV_CHAIN" => "N",
		"SHORT_FORM" => "Y",
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"ITEMS_COUNT" => $arParams["ITEM_MAIN_COUNT"],
	),
	$component 
);
?><?
if ($arParams["FATAL_ERROR"] == "Y"):
	if (!empty($arParams["ERROR_MESSAGE"])):
		ShowError($arParams["ERROR_MESSAGE"]);
	else:
		ShowNote($arParams["NOTE_MESSAGE"], "notetext-simple");
	endif;
	$this->__component->__menu_values = false;
	return false;
endif;
if ($arResult["VARIABLES"]["PAGE_NAME"] == "WEBDAV_TASK"):
	$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("WD_TASK"), 
		CComponentEngine::MakePathFromTemplate($arResult['~PATH_TO_GROUP_FILES_WEBDAV_TASK_LIST'], array()));
elseif ($arResult["VARIABLES"]["PAGE_NAME"]== "PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_WORKFLOW_EDIT"):
	$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("WD_BP"), 
		CComponentEngine::MakePathFromTemplate($arResult['~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_WORKFLOW_ADMIN'], array()));
endif;
?>
<br class="sn-br" />
<?

if ($arParams["PERMISSION"] >= "W" && $arParams["CHECK_CREATOR"] != "Y" && $arResult["VARIABLES"]["PAGE_NAME"] == "SECTIONS")
{
//	$result = CSocNetUserToGroup::InitUserPerms($GLOBALS["USER"]->GetId(), $arGroup, CSocNetUser::IsCurrentUserModuleAdmin()); 
//	if ($result["UserCanModerateGroup"] === true)
//	{
		$bNeedButton = ($arParams["OBJECT"]->workflow == "bizproc"); 
		if ($arParams["OBJECT"]->workflow == "bizproc_limited")
		{
			$bNeedButton = (CIBlock::GetArrayByID($arParams["OBJECT"]->IBLOCK_ID, "BIZPROC") != "N"); 
		}
		if ($bNeedButton)
		{
			$component->arResult["arButtons"] = (is_array($component->arResult["arButtons"]) ? $component->arResult["arButtons"] : array()); 
			$component->arResult["arButtons"][] = array(
				"TEXT" => GetMessage("SOCNET_SETTINGS"),
				"TITLE" => GetMessage("SOCNET_SETTINGS_ALT"),
				"LINK" => "javascript:".$APPLICATION->GetPopupLink(Array(
					"URL" => $component->__path."/include/webdav_settings.php?DOCUMENT_ID=".$arParams["OBJECT"]->wfParams['DOCUMENT_TYPE'][2].
						"&back_url=".urlencode($APPLICATION->GetCurPage()),
					"PARAMS" => Array("min_width" => 300, "min_height" => 150)
				)),
				"ICON" => "btn-list settings"); 
		}
//	}
}

?><?$result = $APPLICATION->IncludeComponent("bitrix:webdav.menu", ".default", Array(
	"OBJECT"	=>	$arParams["OBJECT"], 
	"SECTION_ID"	=>	$arResult["VARIABLES"]["SECTION_ID"],
	"ELEMENT_ID"	=>	$arResult["VARIABLES"]["ELEMENT_ID"],
	"PAGE_NAME" => $arResult["VARIABLES"]["PAGE_NAME"],
	"ACTION"	=>	$arResult["VARIABLES"]["ACTION"],
	"BASE_URL"	=>	$arResult["VARIABLES"]["BASE_URL"],
	
	"SECTIONS_URL" => $arResult["~PATH_TO_GROUP_FILES"],
	"SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_SECTION_EDIT"],
	"ELEMENT_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT"],
	"ELEMENT_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_EDIT"],
	"ELEMENT_FILE_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_FILE"],
	"ELEMENT_HISTORY_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_HISTORY"],
	"ELEMENT_HISTORY_GET_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_HISTORY_GET"],
	"ELEMENT_VERSION_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_VERSION"],
	"ELEMENT_VERSIONS_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_VERSIONS"],
	"ELEMENT_UPLOAD_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_UPLOAD"],
	"HELP_URL" => $arResult["~PATH_TO_GROUP_FILES_HELP"],
	"CONNECTOR_URL" => $arResult["~PATH_TO_GROUP_FILES_CONNECTOR"],
	"USER_VIEW_URL" => $arResult["~PATH_TO_USER"],
	"WEBDAV_BIZPROC_HISTORY_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_HISTORY"], 
	"WEBDAV_BIZPROC_HISTORY_GET_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_HISTORY_GET"], 
	"WEBDAV_BIZPROC_LOG_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_LOG"], 
	"WEBDAV_BIZPROC_VIEW_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_VIEW"], 
	"WEBDAV_BIZPROC_WORKFLOW_ADMIN_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_WORKFLOW_ADMIN"], 
	"WEBDAV_BIZPROC_WORKFLOW_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_WORKFLOW_EDIT"], 
	"WEBDAV_START_BIZPROC_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_START_BIZPROC"], 
	"WEBDAV_TASK_LIST_URL" => $arResult["~PATH_TO_BIZPROC_TASK_LIST"], 
	"WEBDAV_TASK_URL" => $arResult["~PATH_TO_BIZPROC_TASK"], 
	
	"BIZPROC" => $arResult["VARIABLES"]["BIZPROC"], 
	"USE_COMMENTS"	=>	"N", 
	"FORUM_ID" => false, 

	"STR_TITLE" => $arResult["VARIABLES"]["STR_TITLE"], 
	"SHOW_WEBDAV" => $arResult["VARIABLES"]["SHOW_WEBDAV"]),
	$component,
	array("HIDE_ICONS" => "Y")
);?>
<?
$this->__component->__menu_values = $result;
?>
