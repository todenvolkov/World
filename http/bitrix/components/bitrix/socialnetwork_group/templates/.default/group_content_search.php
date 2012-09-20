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
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
		"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
		"PATH_TO_GROUP_PHOTO" => $arResult["PATH_TO_GROUP_PHOTO"],
		"PATH_TO_GROUP_FORUM" => $arResult["PATH_TO_GROUP_FORUM"],
		"PATH_TO_GROUP_CALENDAR" => $arResult["PATH_TO_GROUP_CALENDAR"],
		"PATH_TO_GROUP_FILES" => $arResult["PATH_TO_GROUP_FILES"],
		"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
		"PATH_TO_GROUP_CONTENT_SEARCH" => $arResult["PATH_TO_GROUP_CONTENT_SEARCH"],		
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"PAGE_ID" => "group_content_search",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
	),
	$component
);
?>

<?
$arGroupFields = $APPLICATION->IncludeComponent(
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
		"PATH_TO_SEARCH" => $arResult["PATH_TO_SEARCH"],
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
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
if (!CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "search", "view", CSocNetUser::IsCurrentUserModuleAdmin()))
{
	ShowError(GetMessage("GROUP_CONTENT_SEARCH_DISABLED"));
	return false;
}
?>
<?
if(is_array($arGroupFields) && array_key_exists("NAME", $arGroupFields) && strlen(trim($arGroupFields["NAME"])) > 0)
{
	$feature = "search";
	$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"]);		
	$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && StrLen($arEntityActiveFeatures[$feature]) > 0) ? $arEntityActiveFeatures[$feature] : GetMessage("GROUP_CONTENT_SEARCH_TITLE"));

	$GLOBALS["APPLICATION"]->SetTitle($arGroupFields["NAME"].": ".$strFeatureTitle);
}
?>
<?$APPLICATION->IncludeComponent("bitrix:search.page", "tags_icons", array(
	"RESTART" => "N",
	"CHECK_DATES" => "N",
	"USE_TITLE_RANK" => "N",
	"FILTER_NAME" => $arParams["SEARCH_FILTER_NAME"],
	"FILTER_DATE_NAME" => $arParams["SEARCH_FILTER_DATE_NAME"],
	"arrFILTER" => array(
		0 => "socialnetwork",
	),
	"arrFILTER_socialnetwork" => array(
		0 => $arResult["VARIABLES"]["group_id"],	
	),
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
	"PAGER_TITLE" => GetMessage("GROUP_CONTENT_SEARCH_RESULTS"),
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_TEMPLATE" => "",
	"TAGS_SORT" => "NAME",
	"TAGS_PAGE_ELEMENTS" => $arParams["SEARCH_TAGS_PAGE_ELEMENTS"],
	"TAGS_PERIOD" => $arParams["SEARCH_TAGS_PERIOD"],
	"TAGS_URL_SEARCH" => CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_GROUP_CONTENT_SEARCH"], array("group_id" => $arResult["VARIABLES"]["group_id"])),
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
	"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
	"PATH_TO_GROUP_FORUM" => $arResult["PATH_TO_GROUP_FORUM"],
	"PATH_TO_GROUP_FILES" => $arResult["PATH_TO_GROUP_FILES"],
	"PATH_TO_GROUP_FILES_SECTION" => $arResult["PATH_TO_GROUP_FILES"],
	"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
	"PATH_TO_GROUP_TASKS_SECTION" => $arResult["PATH_TO_GROUP_TASKS"],
	"PATH_TO_GROUP_PHOTO" => $arResult["PATH_TO_GROUP_PHOTO"],
	"PATH_TO_GROUP_PHOTO_SECTION" => $arResult["PATH_TO_GROUP_PHOTO_SECTION"],
	"PATH_TO_GROUP_CALENDAR" => $arResult["PATH_TO_GROUP_CALENDAR"],
	"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"FILES_GROUP_IBLOCK_ID" => $arParams["FILES_GROUP_IBLOCK_ID"],
	"CALENDAR_GROUP_IBLOCK_ID" => $arParams["CALENDAR_GROUP_IBLOCK_ID"],
	"TASKS_GROUP_IBLOCK_ID" => $arParams["TASK_IBLOCK_ID"],
	"PHOTO_GROUP_IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],	),
	$component
);?>
