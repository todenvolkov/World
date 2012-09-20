<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group_menu",
	"",
	Array(
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
		"PAGE_ID" => "group_forum",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
	),
	$component
);
?>

<?
$APPLICATION->IncludeComponent(
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
		"PATH_TO_SEARCH" => $arResult["PATH_TO_SEARCH"],
		"PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
		"PATH_TO_USER_LEAVE_GROUP" => $arResult["PATH_TO_USER_LEAVE_GROUP"],
		"PATH_TO_GROUP_DELETE" => $arResult["PATH_TO_GROUP_DELETE"],
		"PATH_TO_GROUP_FEATURES" => $arResult["PATH_TO_GROUP_FEATURES"],
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
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
?>

<?$arInfo = $APPLICATION->IncludeComponent("bitrix:socialnetwork.forum.topic.read", "", 
	Array(
		"FID"	=>	$arParams["FORUM_ID"],
		"TID"	=>	$arResult["VARIABLES"]["topic_id"],
		"MID"	=>	$arResult["VARIABLES"]["message_id"],
		"ACTION" => $arResult["VARIABLES"]["action"], 
		
		"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		
		"URL_TEMPLATES_TOPIC_LIST"	=>	$arResult["~PATH_TO_GROUP_FORUM"],
		"URL_TEMPLATES_TOPIC"	=>	$arResult["~PATH_TO_GROUP_FORUM_TOPIC"],
		"URL_TEMPLATES_TOPIC_EDIT"	=>	$arResult["~PATH_TO_GROUP_FORUM_TOPIC_EDIT"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["~PATH_TO_GROUP_FORUM_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW"	=>	$arResult["~PATH_TO_USER"],
		
		"PAGEN" => $arParams["PAGEN"],
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"],
		"PAGE_NAVIGATION_SHOW_ALL" =>  $arParams["PAGE_NAVIGATION_SHOW_ALL"],
		
		"MESSAGES_PER_PAGE"	=>	$arParams["MESSAGES_PER_PAGE"],
		
		"PATH_TO_ICON"	=> $arParams["PATH_TO_FORUM_ICON"],
		"PATH_TO_SMILE"	=> $arParams["PATH_TO_FORUM_SMILE"],
		"WORD_LENGTH"	=>	$arParams["WORD_LENGTH"],
		"IMAGE_SIZE"	=>	$arParams["IMAGE_SIZE"],
		"DATE_FORMAT"	=>	$arParams["DATE_FORMAT"],
		"DATE_TIME_FORMAT"	=>	$arParams["DATE_TIME_FORMAT"],
		
		"SHOW_RATING"	=>	$arParams["SHOW_RATING"],
		"RATING_ID"	=>	$arParams["RATING_ID"],
		"SET_TITLE"	=>	$arParams["SET_TITLE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
	), 
	$component,
	array("HIDE_ICONS" => "Y"));
?><?
if (!empty($arInfo) && $arInfo["PERMISSION"] > "E" && !$arInfo["HideArchiveLinks"]):
?><?$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.forum.post_form", 
	"", 
	Array(
		"FID"	=>	$arParams["FORUM_ID"],
		"TID"	=>	$arResult["VARIABLES"]["topic_id"],
		"MID"	=>	$arResult["VARIABLES"]["message_id"],
		"PAGE_NAME"	=>	"group_forum_message",
		"MESSAGE_TYPE"	=>	"REPLY",
		"bVarsFromForm" => $arInfo["bVarsFromForm"],
		
		"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"], 
		
		"URL_TEMPLATES_TOPIC_LIST" =>  $arResult["~PATH_TO_GROUP_FORUM_TOPIC"],
		"URL_TEMPLATES_MESSAGE" => $arResult["~PATH_TO_GROUP_FORUM_MESSAGE"],
		
		"MESSAGE" => $arInfo["MESSAGE"],
		"ERROR_MESSAGE" => $arInfo["ERROR_MESSAGE"],
		
		"PATH_TO_SMILE"	=>	$arParams["PATH_TO_FORUM_SMILE"],
		"PATH_TO_ICON"	=>	$arParams["PATH_TO_FORUM_ICON"],
		"SMILE_TABLE_COLS" => $arParams["SMILE_TABLE_COLS"],
		"AJAX_TYPE" => $arParams["AJAX_TYPE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"]),
	$component,
	array("HIDE_ICONS" => "Y"));
?><?
endif;
?>