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
<?$ElementID = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
 		"ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
		
		"ELEMENT_SORT_FIELD" => $arParams["PHOTO"]["ALL"]["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["PHOTO"]["ALL"]["ELEMENT_SORT_ORDER"],
		
		"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
		"DETAIL_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT"],
		"DETAIL_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_EDIT"],
		"DETAIL_SLIDE_SHOW_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_SLIDE_SHOW"],
		"SEARCH_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SEARCH"],
		"SECTION_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION"],
		"UPLOAD_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_UPLOAD"],
		
 		"DATE_TIME_FORMAT" => $arParams["PHOTO"]["ALL"]["DATE_TIME_FORMAT_DETAIL"],
 		"COMMENTS_TYPE" => $arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"],
 		"THUMBS_SIZE" => $arParams["PHOTO"]["ALL"]["PREVIEW_SIZE"],
		"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => "N",
		
		"SHOW_TAGS" => $arParams["PHOTO"]["ALL"]["SHOW_TAGS"]
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?><?

if ($ElementID <= 0)
	return false;

if($arParams["PHOTO"]["ALL"]["USE_RATING"]=="Y"):
?><div id="photo_vote_source" style="display:none;"><?
$APPLICATION->IncludeComponent(
	"bitrix:iblock.vote",
	"ajax",
	Array(
		"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
		"ELEMENT_ID" => $ElementID,
		"MAX_VOTE" => $arParams["PHOTO"]["ALL"]["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["PHOTO"]["ALL"]["VOTE_NAMES"],
		"DISPLAY_AS_RATING" => $arParams["PHOTO"]["ALL"]["DISPLAY_AS_RATING"],
		"READ_ONLY" => (array_key_exists("CLOSED", $arGroup) && $arGroup["CLOSED"] == "Y" ? "Y" : ""),
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?></div>
<script>
function to_show_vote()
{
	if (document.getElementById('photo_vote') && document.getElementById('vote_<?=$ElementID?>'))
	{
		var _div = document.getElementById('vote_<?=$ElementID?>');
		var div = document.getElementById('vote_<?=$ElementID?>').cloneNode(true);
		_div.id = 'temp';
		document.getElementById('photo_vote').appendChild(div);
	}
	else
	{
		document.getElementById('photo_vote_source').style.display = '';
	}
	
}
setTimeout(to_show_vote, 100);
</script><?
endif;

// SLIDER
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	"slider", 
	Array(
		"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		
		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TIME" => "",
		"ELEMENTS_LAST_TIME_FROM" => "", 
		"ELEMENTS_LAST_TIME_TO" => "", 
		"ELEMENT_SORT_FIELD" => $arParams["PHOTO"]["ALL"]["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["PHOTO"]["ALL"]["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD1" => "",
		"ELEMENT_SORT_ORDER1" => "",
		"ELEMENT_FILTER" => array(),
		"ELEMENT_SELECT_FIELDS" => array(), 
		"PROPERTY_CODE" => $arParams["PHOTO"]["ALL"]["PROPERTY_CODE"], 
		
		"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
		"DETAIL_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_SLIDE_SHOW"],
		"SEARCH_URL"	=>	$arResult["~PATH_TO_GROUP_PHOTO_SEARCH"],
		
		"USE_PERMISSIONS" => $arParams["PHOTO"]["ALL"]["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["PHOTO"]["ALL"]["GROUP_PERMISSIONS"],
		
		"USE_DESC_PAGE" => $arParams["PHOTO"]["ALL"]["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_ELEMENTS" => 10,
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PHOTO"]["ALL"]["PAGE_NAVIGATION_TEMPLATE"],
		
 		"DATE_TIME_FORMAT" => $arParams["PHOTO"]["ALL"]["DATE_TIME_FORMAT_DETAIL"],
		
		"ADDITIONAL_SIGHTS" => $arParams["PHOTO"]["ALL"]["~ADDITIONAL_SIGHTS"],
		"PICTURES_SIGHT" => "",
		"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"],
		
		"SHOW_PHOTO_USER" => "Y",
		"GALLERY_AVATAR_SIZE" => $arParams["PHOTO"]["TEMPLATE"]["GALLERY_AVATAR_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",

		"SLIDER_COUNT_CELL" => $arParams["PHOTO"]["TEMPLATE"]["SLIDER_COUNT_CELL"],
		"THUMBS_SIZE"	=>	$arParams["PHOTO"]["ALL"]["THUMBS_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"none",
		
		"ELEMENT_ID" => ($_REQUEST["current"] ? $_REQUEST["current"]["id"] : $ElementID),
		"USE_RATING" => $arParams["PHOTO"]["ALL"]["USE_RATING"], 
		"MAX_VOTE" => $arParams["PHOTO"]["ALL"]["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["PHOTO"]["ALL"]["VOTE_NAMES"],
		"DISPLAY_AS_RATING" => $arParams["PHOTO"]["ALL"]["DISPLAY_AS_RATING"],
		"READ_ONLY" => (array_key_exists("CLOSED", $arGroup) && $arGroup["CLOSED"] == "Y" ? "Y" : ""), 
		"USE_COMMENTS" => $arParams["PHOTO"]["ALL"]["USE_COMMENTS"], 
		"INCLUDE_SLIDER" => "Y"
	),
	$component,
	array("HIDE_ICONS" => "Y")
);

// COMMENTS
if ($arParams["PHOTO"]["ALL"]["USE_COMMENTS"] == "Y" && $arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"] != "none"):
	?><div class="empty-clear before-comment"></div><?
	$arCommentsParams = Array(
		"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
 		"ELEMENT_ID" => $ElementID,
 		"COMMENTS_TYPE" => $arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"],
		"BEHAVIOUR" => "USER",
		
		"DETAIL_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT"],
		
 		"COMMENTS_COUNT" => $arParams["PHOTO"]["ALL"]["COMMENTS_COUNT"],
		"PATH_TO_SMILE" => $arParams["PHOTO"]["ALL"]["PATH_TO_SMILE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"]);

	$arCommentsParams["COMMENTS_TYPE"] = (strToLower($arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"]) == "forum" ? "forum" : "blog");
	
	if ($arCommentsParams["COMMENTS_TYPE"] != "forum")
	{
		$arCommentsParams["COMMENTS_TYPE"] = "blog";
		$arCommentsParams["BLOG_URL"] = $arParams["PHOTO"]["ALL"]["BLOG_URL"];
		$arCommentsParams["PATH_TO_USER"] = $arParams["PHOTO"]["ALL"]["PATH_TO_USER"];
		$arCommentsParams["PATH_TO_BLOG"] = $arParams["PHOTO"]["ALL"]["PATH_TO_BLOG"];
	}
	else
	{
		$arCommentsParams["FORUM_ID"] = $arParams["PHOTO"]["ALL"]["FORUM_ID"];
		$arCommentsParams["USE_CAPTCHA"] = $arParams["PHOTO"]["ALL"]["USE_CAPTCHA"];
		$arCommentsParams["URL_TEMPLATES_READ"] = $arParams["PHOTO"]["ALL"]["URL_TEMPLATES_READ"];
		$arCommentsParams["PREORDER"] = ($arParams["PHOTO"]["ALL"]["PREORDER"] != "N" ? "Y" : "N");
		$arCommentsParams["SHOW_LINK_TO_FORUM"] = ($arParams["PHOTO"]["ALL"]["SHOW_LINK_TO_FORUM"] == "Y" ? "Y" : "N");
	}
	$APPLICATION->IncludeComponent(
		"bitrix:photogallery.detail.comment", 
		"", 
		$arCommentsParams,
		$component,
		array("HIDE_ICONS" => "Y"));
endif;
?>