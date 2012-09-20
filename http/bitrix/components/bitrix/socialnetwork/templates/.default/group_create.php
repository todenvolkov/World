<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.user_menu",
	"",
	Array(
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_USER_EDIT" => $arResult["PATH_TO_USER_PROFILE_EDIT"],
		"PATH_TO_USER_FRIENDS" => $arResult["PATH_TO_USER_FRIENDS"],
		"PATH_TO_USER_GROUPS" => $arResult["PATH_TO_USER_GROUPS"],
		"PATH_TO_USER_FRIENDS_ADD" => $arResult["PATH_TO_USER_FRIENDS_ADD"],
		"PATH_TO_USER_FRIENDS_DELETE" => $arResult["PATH_TO_USER_FRIENDS_DELETE"],
		"PATH_TO_MESSAGE_FORM" => $arResult["PATH_TO_MESSAGE_FORM"],
		"PATH_TO_MESSAGES_INPUT" => $arResult["PATH_TO_MESSAGES_INPUT"],
		"PATH_TO_USER_BLOG" => $arResult["PATH_TO_USER_BLOG"],
		"PATH_TO_USER_PHOTO" => $arResult["PATH_TO_USER_PHOTO"],
		"PATH_TO_USER_FORUM" => $arResult["PATH_TO_USER_FORUM"],
		"PATH_TO_USER_CALENDAR" => $arResult["PATH_TO_USER_CALENDAR"],
		"PATH_TO_USER_FILES" => $arResult["PATH_TO_USER_FILES"],
		"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
		"PATH_TO_USER_CONTENT_SEARCH" => $arResult["PATH_TO_USER_CONTENT_SEARCH"],
		"ID" => $arResult["VARIABLES"]["user_id"],
		"PAGE_ID" => "",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
	),
	$component
);
?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group_create", 
	"", 
	Array(
		"PATH_TO_USER" 				=> $arResult["PATH_TO_USER"],
		"PATH_TO_GROUP" 			=> $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_EDIT" 		=> $arResult["PATH_TO_GROUP_EDIT"],
		"PATH_TO_GROUP_CREATE" 		=> $arResult["PATH_TO_GROUP_CREATE"],
		"ALLOW_REDIRECT_REQUEST" 	=> $arParams["ALLOW_GROUP_CREATE_REDIRECT_REQUEST"],
		"REDIRECT_REQUEST" 			=> $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
		"PAGE_VAR" 					=> $arResult["ALIASES"]["page"],
		"USER_VAR" 					=> $arResult["ALIASES"]["user_id"],
		"GROUP_VAR" 				=> $arResult["ALIASES"]["group_id"],
		"SET_NAV_CHAIN" 			=> $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" 				=> $arResult["SET_TITLE"],
		"USER_ID" 					=> $arResult["VARIABLES"]["user_id"],
		"GROUP_ID" 					=> $arResult["VARIABLES"]["group_id"],
		"NAME_TEMPLATE" 			=> $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" 				=> $arParams["SHOW_LOGIN"],
		"USE_AUTOSUBSCRIBE" 		=> "N",
	),
	$component 
);
?>