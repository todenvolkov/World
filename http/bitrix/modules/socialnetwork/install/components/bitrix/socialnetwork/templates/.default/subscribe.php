<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.messages_menu",
	"",
	Array(
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"PATH_TO_MESSAGES_INPUT" => $arResult["PATH_TO_MESSAGES_INPUT"],
		"PATH_TO_MESSAGES_OUTPUT" => $arResult["PATH_TO_MESSAGES_OUTPUT"],
		"PATH_TO_MESSAGES_USERS" => $arResult["PATH_TO_MESSAGES_USERS"],
		"PATH_TO_USER_BAN" => $arResult["PATH_TO_USER_BAN"],
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_LOG" => $arResult["PATH_TO_LOG"],
		"PATH_TO_SUBSCRIBE" => $arResult["PATH_TO_SUBSCRIBE"],
		"PATH_TO_BIZPROC" => $arResult["PATH_TO_BIZPROC"],
		"PATH_TO_TASKS" => $arResult["PATH_TO_TASKS"],
		"PAGE_ID" => "subscribe",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
	),
	$component
);
?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.subscribe_list", 
	"", 
	Array(
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_SUBSCRIBE" => $arResult["PATH_TO_GROUP_SUBSCRIBE"],
		"PATH_TO_USER_SUBSCRIBE" => $arResult["PATH_TO_USER_SUBSCRIBE"],
		"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
		"SHOW_YEAR" => $arParams["SHOW_YEAR"],		
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],		
		"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
		"INLINE" => "Y",		
	),
	$component 
);
?>