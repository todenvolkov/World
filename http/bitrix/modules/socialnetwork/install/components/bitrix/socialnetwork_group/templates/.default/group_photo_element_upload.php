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
	"bitrix:photogallery.user",
	".default",
	Array(
		"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
		"PAGE_NAME" => "INDEX",
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
		
		"SORT_BY" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_ORD"],
		
		"INDEX_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
		"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
		"GALLERIES_URL" => $arResult["~PATH_TO_GROUP_PHOTO_GALLERIES"],
		"GALLERY_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_GALLERY_EDIT"],
		"SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT"],
		"SECTION_EDIT_ICON_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT_ICON"],
		"UPLOAD_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_UPLOAD"],
		
		"ONLY_ONE_GALLERY" => $arParams["PHOTO"]["ALL"]["ONLY_ONE_GALLERY"],
		"GALLERY_GROUPS" => $arParams["PHOTO"]["ALL"]["GALLERY_GROUPS"],
		"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"],
		
		"SET_NAV_CHAIN" => "N", 
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		
		"GALLERY_AVATAR_SIZE"	=>	$arParams["GALLERY_AVATAR_SIZE"]
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?>
<br />
<?$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.upload",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"GALLERY_SIZE"	=>	$arParams["PHOTO"]["ALL"]["GALLERY_SIZE"],
		
		"SECTIONS_TOP_URL" => "",
		"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
		"SECTION_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION"],
		
		"UPLOAD_MAX_FILE"	=>	$arParams["PHOTO"]["ALL"]["UPLOAD_MAX_FILE"],
		"UPLOAD_MAX_FILE_SIZE"	=>	$arParams["PHOTO"]["ALL"]["UPLOAD_MAX_FILE_SIZE"],
		"ADDITIONAL_SIGHTS" => $arParams["PHOTO"]["ALL"]["~ADDITIONAL_SIGHTS"],
		"MODERATION" => $arParams["PHOTO"]["ALL"]["MODERATION"],
		"PUBLIC_BY_DEFAULT" => "Y", 
		"APPROVE_BY_DEFAULT" => "Y", 
		
		"WATERMARK_RULES" => $arParams["PHOTO"]["ALL"]["WATERMARK_RULES"],
		"WATERMARK_TYPE" => $arParams["PHOTO"]["ALL"]["WATERMARK_TYPE"], 
		"WATERMARK_TEXT" => $arParams["PHOTO"]["ALL"]["WATERMARK_TEXT"], 
		"WATERMARK_COLOR" => $arParams["PHOTO"]["ALL"]["WATERMARK_COLOR"], 
		"WATERMARK_SIZE" => $arParams["PHOTO"]["ALL"]["WATERMARK_SIZE"], 
		"WATERMARK_FILE" => $arParams["PHOTO"]["ALL"]["WATERMARK_FILE"], 
		"WATERMARK_FILE_ORDER" => $arParams["PHOTO"]["ALL"]["WATERMARK_FILE_ORDER"], 
		"WATERMARK_POSITION" => $arParams["PHOTO"]["ALL"]["WATERMARK_POSITION"], 
		"WATERMARK_TRANSPARENCY" => $arParams["PHOTO"]["ALL"]["WATERMARK_TRANSPARENCY"], 
		"PATH_TO_FONT"	=>	$arParams["PHOTO"]["ALL"]["PATH_TO_FONT"],
		"WATERMARK_MIN_PICTURE_SIZE"	=>	$arParams["PHOTO"]["ALL"]["WATERMARK_MIN_PICTURE_SIZE"],
		
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_SIZE"],
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_THUMBS_SIZE"],
		
		"THUMBS_SIZE"	=>	$arParams["PHOTO"]["ALL"]["THUMBS_SIZE"],
		"JPEG_QUALITY1"	=>	$arParams["PHOTO"]["ALL"]["JPEG_QUALITY1"],
		"PREVIEW_SIZE"	=>	$arParams["PHOTO"]["ALL"]["PREVIEW_SIZE"],
		"JPEG_QUALITY2"	=>	$arParams["PHOTO"]["ALL"]["JPEG_QUALITY2"],
		"ORIGINAL_SIZE"	=>	$arParams["PHOTO"]["ALL"]["ORIGINAL_SIZE"],
		"JPEG_QUALITY"	=>	$arParams["PHOTO"]["ALL"]["JPEG_QUALITY"],
		
 		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
 		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => "N",
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
 		"WATERMARK" => $arParams["PHOTO"]["TEMPLATE"]["WATERMARK"],
 		"SHOW_WATERMARK" => $arParams["PHOTO"]["TEMPLATE"]["WATERMARK"],
 		"WATERMARK_COLORS" => $arParams["PHOTO"]["TEMPLATE"]["WATERMARK_COLORS"],
 		"SHOW_TAGS" => $arParams["PHOTO"]["ALL"]["SHOW_TAGS"]
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?><?

$this->__component->arParams["ANSWER_UPLOAD_PAGE"] = $result;
?>