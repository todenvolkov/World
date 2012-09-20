<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="photo-page-section">
<?$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.section",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"USER_ALIAS" => $arResult["MENU_VARIABLES"]["USER_ALIAS"],
		"BEHAVIOUR" => "USER",
		"PERMISSION" => $arResult["MENU_VARIABLES"]["PERMISSION"],
		
		"INDEX_URL" => $arResult["URL_TEMPLATES"]["index"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
 		
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"GALLERY_SIZE"	=>	$arParams["GALLERY_SIZE"],
		"RETURN_SECTION_INFO" => "Y", 
		"SET_STATUS_404" => $arParams["SET_STATUS_404"], 
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => $arParams["SET_TITLE"], 
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"]
	),
	$component
);?>
<?
if ($result && intVal($result["ELEMENTS_CNT"]) > 0)
{
if ($arParams["USE_RATING"] == "Y"):
	$arParams["PROPERTY_CODE"][] = "PROPERTY_vote_count"; 
	$arParams["PROPERTY_CODE"][] = "PROPERTY_vote_sum";
	$arParams["PROPERTY_CODE"][] = "PROPERTY_RATING";
endif;
if ($arParams["USE_COMMENTS"] == "Y"):
	if ($arParams["COMMENTS_TYPE"] == "forum")
		$arParams["PROPERTY_CODE"][] = "PROPERTY_FORUM_MESSAGE_CNT";
	elseif ($arParams["COMMENTS_TYPE"] == "blog")
		$arParams["PROPERTY_CODE"][] = "PROPERTY_BLOG_COMMENTS_CNT";
endif;

// DETAIL LIST
?>
<div class="photo-info-box photo-info-box-photo-list">
	<div class="photo-info-box-inner">
<?$result2 = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	"", 
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["MENU_VARIABLES"]["USER_ALIAS"],
		"PERMISSION" => $arResult["MENU_VARIABLES"]["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],

		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TIME" => "",
		"ELEMENTS_LAST_TIME_FROM" => "", 
		"ELEMENTS_LAST_TIME_TO" => "", 
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD1" => "",
		"ELEMENT_SORT_ORDER1" => "",
		"ELEMENT_FILTER" => array(),
		"ELEMENT_SELECT_FIELDS" => array(), 
		"PROPERTY_CODE" => $arParams["PROPERTY_CODE"], 
		
		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"USE_DESC_PAGE" => $arParams["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_ELEMENTS" => $arParams["ELEMENTS_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"PICTURES_SIGHT" => "",
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		
		"SHOW_PHOTO_USER" => "N",
		"GALLERY_AVATAR_SIZE" => "0",
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",
		
		"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"bottom",
		
		"SHOW_CONTROLS"	=>	"Y",
		"SHOW_RATING" => "N",
		"SHOW_SHOWS" => "N", 
		"USE_RATING" => $arParams["USE_RATING"],
		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],
		"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
		"SHOW_COMMENTS" => $arParams["USE_COMMENTS"],
		"USE_COMMENTS" => $arParams["USE_COMMENTS"],
		"INCLUDE_SLIDER" => "Y",
		
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"]
	),
	$component, 
	array("HIDE_ICONS" => "Y")
);?>
	</div>
</div>
<?
if (empty($result2)):
?>
<style>
div.photo-page-section div.photo-info-box-photo-list {
	display: none;}
</style>
<?
endif;
}
// SECTIONS LIST
if (intVal($result["SECTIONS_CNT"]) > 0)
{
?>
<div class="photo-info-box photo-info-box-section-list">
	<div class="photo-info-box-inner">
		<div class="photo-header-big">
			<div class="photo-header-inner">
				<?=GetMessage("P_ALBUMS")?> 
			</div>
		</div>
	<?$result2 = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.list",
	".big",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["MENU_VARIABLES"]["USER_ALIAS"],
		"PERMISSION" => $arResult["MENU_VARIABLES"]["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		
		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],
		
		"INDEX_URL" => $arResult["URL_TEMPLATES"]["index"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		
		"PAGE_ELEMENTS" => $arParams["SECTION_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"], 
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"]
	),
	$component, 
	array("HIDE_ICONS" => "Y")
);
?>
	</div>
</div>
<?
if (empty($result2["SECTIONS"]))
{
?>
<style>
div.photo-page-section div.photo-info-box-section-list {
	display: none;}
</style>
<?
}
}
?>
</div>