<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
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
		"PAGE_ID" => "group_photo",
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
		"PATH_TO_GROUP_MODS" => $arResult["PATH_TO_GROUP_MODS"],
		"PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
		"PATH_TO_USER_LEAVE_GROUP" => $arResult["PATH_TO_USER_LEAVE_GROUP"],
		"PATH_TO_GROUP_DELETE" => $arResult["PATH_TO_GROUP_DELETE"],
		"PATH_TO_GROUP_FEATURES" => $arResult["PATH_TO_GROUP_FEATURES"],
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

?><?
if ($arParams["FATAL_ERROR"] == "Y"):
	if (!empty($arParams["ERROR_MESSAGE"])):
		ShowError($arParams["ERROR_MESSAGE"]);
	else:
		ShowNote($arParams["NOTE_MESSAGE"], "notetext-simple");
	endif;
	return false;
endif;

?>
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	"slide_show", 
	Array(
		"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
		"BEHAVIOUR" => "USER",
 		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
 		"ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
 		
		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TIME" => "",
		"ELEMENT_SORT_FIELD" => $arParams["PHOTO"]["ALL"]["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["PHOTO"]["ALL"]["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD1" => "",
		"ELEMENT_SORT_ORDER1" => "",
		"ELEMENT_FILTER" => array(),
 		
		"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
		"DETAIL_SLIDE_SHOW_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_SLIDE_SHOW"],
		"DETAIL_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT"],
		"SEARCH_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SEARCH"],
		
		"USE_PERMISSIONS" => $arParams["PHOTO"]["ALL"]["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["PHOTO"]["ALL"]["GROUP_PERMISSIONS"],
		
		"USE_DESC_PAGE" => $arParams["PHOTO"]["ALL"]["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_ELEMENTS" => 10,
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PHOTO"]["ALL"]["PAGE_NAVIGATION_TEMPLATE"],
 		
 		"DATE_TIME_FORMAT" => $arParams["PHOTO"]["ALL"]["DATE_TIME_FORMAT_DETAIL"],
		"COMMENTS_TYPE" => $arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"],
		
		"PICTURES_SIGHT" => "REAL",
		"ADDITIONAL_SIGHTS" => $arParams["PHOTO"]["ALL"]["~ADDITIONAL_SIGHTS"],
		"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"],
		"SHOW_PHOTO_USER" => $arParams["PHOTO"]["TEMPLATE"]["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["PHOTO"]["TEMPLATE"]["GALLERY_AVATAR_SIZE"],
	
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",
		
 		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"]
	),
	$component
);
?>