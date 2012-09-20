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
		"PATH_TO_MESSAGES_INPUT" => $arResult["PATH_TO_MESSAGES_INPUT"],
		"PATH_TO_MESSAGE_FORM" => $arResult["PATH_TO_MESSAGE_FORM"],
		"PATH_TO_USER_BLOG" => $arResult["PATH_TO_USER_BLOG"],
		"PATH_TO_USER_PHOTO" => $arResult["PATH_TO_USER_PHOTO"],
		"PATH_TO_USER_FORUM" => $arResult["PATH_TO_USER_FORUM"],
		"PATH_TO_USER_CALENDAR" => $arResult["PATH_TO_USER_CALENDAR"],
		"PATH_TO_USER_FILES" => $arResult["PATH_TO_USER_FILES"],
		"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
		"PATH_TO_USER_CONTENT_SEARCH" => $arResult["PATH_TO_USER_CONTENT_SEARCH"],
		"ID" => $arResult["VARIABLES"]["user_id"],
		"PAGE_ID" => "user_photo",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>

<?$APPLICATION->IncludeComponent(
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
		"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
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
		"PATH_TO_GROUP_REQUEST_GROUP_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_GROUP_SEARCH"], 
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"], 
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
	),
	$component,
	array("HIDE_ICONS" => "Y") 
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
		"IBLOCK_TYPE" => $arParams["PHOTO_USER_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],
		"PAGE_NAME" => "INDEX",
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
		
		"SORT_BY" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_ORD"],
		
		"INDEX_URL" => $arResult["~PATH_TO_USER_PHOTO"],
		"GALLERY_URL" => $arResult["~PATH_TO_USER_PHOTO"],
		"GALLERIES_URL" => $arResult["~PATH_TO_USER_PHOTO_GALLERIES"],
		"GALLERY_EDIT_URL" => $arResult["~PATH_TO_USER_PHOTO_GALLERY_EDIT"],
		"SECTION_EDIT_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION_EDIT"],
		"SECTION_EDIT_ICON_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION_EDIT_ICON"],
		"UPLOAD_URL" => $arResult["~PATH_TO_USER_PHOTO_ELEMENT_UPLOAD"],
		
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
		"IBLOCK_TYPE" => $arParams["PHOTO_USER_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"GALLERY_SIZE"	=>	$arParams["PHOTO"]["ALL"]["GALLERY_SIZE"],
		
		"SECTIONS_TOP_URL" => "",
		"GALLERY_URL" => $arResult["~PATH_TO_USER_PHOTO"],
		"SECTION_URL" => $arResult["~PATH_TO_USER_PHOTO_SECTION"],
		
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