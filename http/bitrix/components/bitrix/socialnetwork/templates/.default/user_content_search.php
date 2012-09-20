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
		"PAGE_ID" => "user_content_search",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
	),
	$component
);
?>

<?
$arUserFields = $APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.user_profile", 
	"short", 
	Array(
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_USER_EDIT" => $arResult["PATH_TO_USER_PROFILE_EDIT"],
		"PATH_TO_USER_FRIENDS" => $arResult["PATH_TO_USER_FRIENDS"],
		"PATH_TO_USER_GROUPS" => $arResult["PATH_TO_USER_GROUPS"],
		"PATH_TO_USER_FRIENDS_ADD" => $arResult["PATH_TO_USER_FRIENDS_ADD"],
		"PATH_TO_USER_FRIENDS_DELETE" => $arResult["PATH_TO_USER_FRIENDS_DELETE"],
		"PATH_TO_MESSAGE_FORM" => $arResult["PATH_TO_MESSAGE_FORM"],
		"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_MESSAGES_USERS_MESSAGES" => $arResult["PATH_TO_MESSAGES_USERS_MESSAGES"],
		"PATH_TO_USER_SETTINGS_EDIT" => $arResult["PATH_TO_USER_SETTINGS_EDIT"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
		"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
		"PATH_TO_USER_FEATURES" => $arResult["PATH_TO_USER_FEATURES"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"SET_TITLE" => "N", 
		"SET_NAV_CHAIN" => "N",
		"USER_PROPERTY_MAIN" => $arResult["USER_PROPERTY_MAIN"],
		"USER_PROPERTY_CONTACT" => $arResult["USER_PROPERTY_CONTACT"],
		"USER_PROPERTY_PERSONAL" => $arResult["USER_PROPERTY_PERSONAL"],
		"USER_FIELDS_MAIN" => $arResult["USER_FIELDS_MAIN"],
		"USER_FIELDS_CONTACT" => $arResult["USER_FIELDS_CONTACT"],
		"USER_FIELDS_PERSONAL" => $arResult["USER_FIELDS_PERSONAL"],
		"PATH_TO_USER_FEATURES" => $arResult["PATH_TO_USER_FEATURES"],
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
		"SHORT_FORM" => "Y",
		"ITEMS_COUNT" => $arParams["ITEM_MAIN_COUNT"],
		"ID" => $arResult["VARIABLES"]["user_id"],
		"SHOW_YEAR" => $arParams["SHOW_YEAR"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
	),
	$component 
);
?>

<?
if (!CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "search", "view", CSocNetUser::IsCurrentUserModuleAdmin()))
{
	ShowError(GetMessage("USER_CONTENT_SEARCH_DISABLED"));
	return false;
}
?>
<?
if(is_array($arUserFields) && array_key_exists("NAME", $arUserFields) && strlen(trim($arUserFields["NAME"])) > 0)
{
	$feature = "search";
	$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"]);		
	$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && StrLen($arEntityActiveFeatures[$feature]) > 0) ? $arEntityActiveFeatures[$feature] : GetMessage("USER_CONTENT_SEARCH_TITLE"));
	
	$GLOBALS["APPLICATION"]->SetTitle($arUserFields["NAME"].": ".$strFeatureTitle);
}
?>
<?$APPLICATION->IncludeComponent("bitrix:search.page", "tags_icons_user", array(
	"RESTART" => "N",
	"CHECK_DATES" => "N",
	"USE_TITLE_RANK" => "N",
	"FILTER_NAME" => $arParams["SEARCH_FILTER_NAME"],
	"FILTER_DATE_NAME" => $arParams["SEARCH_FILTER_DATE_NAME"],
	"arrFILTER" => array(
		0 => "socialnetwork_user",
	),
	"arrFILTER_socialnetwork_user" => $arResult["VARIABLES"]["user_id"],
	"SHOW_WHERE" => "N",
	"arrWHERE_SONET" => array(
		0 => "forum",
		1 => "blog",
		2 => "tasks",
		3 => "photo",
		4 => "files"
	),
	"DEFAULT_SORT" => (strlen($_REQUEST["tags"]) > 0 ? "date" : $arParams["SEARCH_DEFAULT_SORT"]),
	"PAGE_RESULT_COUNT" => $arParams["SEARCH_PAGE_RESULT_COUNT"],
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"PAGER_TITLE" => GetMessage("USER_CONTENT_SEARCH_RESULTS"),
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_TEMPLATE" => "",
	"TAGS_SORT" => "NAME",
	"TAGS_PAGE_ELEMENTS" => $arParams["SEARCH_TAGS_PAGE_ELEMENTS"],
	"TAGS_PERIOD" => $arParams["SEARCH_TAGS_PERIOD"],
	"TAGS_URL_SEARCH" => CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_USER_CONTENT_SEARCH"], array("user_id" => $arResult["VARIABLES"]["user_id"])),
	"TAGS_INHERIT" => "Y",
	"FONT_MAX" => $arParams["SEARCH_TAGS_FONT_MAX"],
	"FONT_MIN" => $arParams["SEARCH_TAGS_FONT_MIN"],
	"COLOR_NEW" => $arParams["SEARCH_TAGS_COLOR_NEW"],
	"COLOR_OLD" => $arParams["SEARCH_TAGS_COLOR_OLD"],
	"PERIOD_NEW_TAGS" => "",
	"SHOW_CHAIN" => "Y",
	"COLOR_TYPE" => "Y",
	"WIDTH" => "100%",
	"AJAX_OPTION_ADDITIONAL" => "",
	"PATH_TO_USER_BLOG" => $arResult["PATH_TO_USER_BLOG"],
	"PATH_TO_USER_FORUM" => $arResult["PATH_TO_USER_FORUM"],
	"PATH_TO_USER_FILES" => $arResult["PATH_TO_USER_FILES"],
	"PATH_TO_USER_FILES_SECTION" => $arResult["PATH_TO_USER_FILES"],
	"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
	"PATH_TO_USER_TASKS_SECTION" => $arResult["PATH_TO_USER_TASKS"],
	"PATH_TO_USER_PHOTO" => $arResult["PATH_TO_USER_PHOTO"],
	"PATH_TO_USER_PHOTO_SECTION" => $arResult["PATH_TO_USER_PHOTO_SECTION"],
	"PATH_TO_USER_CALENDAR" => $arResult["PATH_TO_USER_CALENDAR"],
	"SOCNET_USER_ID" => $arResult["VARIABLES"]["user_id"],
	"FILES_USER_IBLOCK_ID" => $arParams["FILES_USER_IBLOCK_ID"],
	"CALENDAR_USER_IBLOCK_ID" => $arParams["CALENDAR_USER_IBLOCK_ID"],
	"TASKS_USER_IBLOCK_ID" => $arParams["TASK_IBLOCK_ID"],
	"PHOTO_USER_IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],	),
	$component
);?>
