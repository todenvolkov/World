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
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
		"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
		"PATH_TO_GROUP_PHOTO" => $arResult["PATH_TO_GROUP_PHOTO"],
		"PATH_TO_GROUP_FORUM" => $arResult["PATH_TO_GROUP_FORUM"],
		"PATH_TO_GROUP_CALENDAR" => $arResult["PATH_TO_GROUP_CALENDAR"],
		"PATH_TO_GROUP_FILES" => $arResult["PATH_TO_GROUP_FILES"],
		"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
		"PATH_TO_GROUP_CONTENT_SEARCH" => $arResult["PATH_TO_GROUP_CONTENT_SEARCH"],
		"PAGE_ID" => "group_requests",
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
		"PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
		"PATH_TO_USER_LEAVE_GROUP" => $arResult["PATH_TO_USER_LEAVE_GROUP"],
		"PATH_TO_GROUP_DELETE" => $arResult["PATH_TO_GROUP_DELETE"],
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
		"PATH_TO_GROUP_FEATURES" => $arResult["PATH_TO_GROUP_FEATURES"],
		"PATH_TO_SEARCH" => $arResult["PATH_TO_SEARCH"],
		"PATH_TO_MESSAGE_TO_GROUP" => $arResult["PATH_TO_MESSAGE_TO_GROUP"], 
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
		"SET_NAV_CHAIN" => "N",
		"SET_TITLE" => "N", 
		"SHORT_FORM" => "Y",
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"ITEMS_COUNT" => $arParams["ITEM_MAIN_COUNT"],
	),
	$component 
);
?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group_requests", 
	"", 
	Array(
		"PATH_TO_USER" 					=> $arParams["PATH_TO_USER"],
		"PATH_TO_GROUP" 				=> $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_REQUESTS" 		=> $arResult["PATH_TO_GROUP_REQUESTS"],
		"PATH_TO_SMILE" 				=> $arResult["PATH_TO_SMILE"],
		"PAGE_VAR" 						=> $arResult["ALIASES"]["page"],
		"GROUP_VAR" 					=> $arResult["ALIASES"]["group_id"],
		"USER_VAR" 						=> $arResult["ALIASES"]["user_id"],
		"SET_NAV_CHAIN" 				=> $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" 					=> $arResult["SET_TITLE"],
		"GROUP_ID" 						=> $arResult["VARIABLES"]["group_id"],
		"PATH_TO_GROUP_REQUEST_SEARCH" 	=> $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
		"ITEMS_COUNT" 					=> $arParams["ITEM_DETAIL_COUNT"],
		"PATH_TO_MESSAGES_CHAT" 		=> $arParams["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_VIDEO_CALL" 			=> $arParams["PATH_TO_VIDEO_CALL"],
		"DATE_TIME_FORMAT" 				=> $arResult["DATE_TIME_FORMAT"],
		"SHOW_YEAR" 					=> $arParams["SHOW_YEAR"],
		"NAME_TEMPLATE" 				=> $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" 					=> $arParams["SHOW_LOGIN"],
		"CACHE_TYPE" 					=> $arParams["CACHE_TYPE"],
		"CACHE_TIME" 					=> $arParams["CACHE_TIME"],
		"PATH_TO_CONPANY_DEPARTMENT" 	=> $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"USE_AUTOSUBSCRIBE" 			=> "N",
	),
	$component 
);
?>