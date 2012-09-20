<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// DETAIL LIST
$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	($_REQUEST["SLIDER"] == "Y" ? "slider_big" : "slide_show"), 
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		"ELEMENT_ID" => (isset($_REQUEST["current"]) ? $_REQUEST["current"]["id"] : $arResult["VARIABLES"]["ELEMENT_ID"]),
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"PAGE_ELEMENTS" => 10,
		"PAGE_NAVIGATE" => $_REQUEST["direction"],
 		
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		
		"PICTURES_SIGHT" => "REAL",
		"SHOW_PAGE_NAVIGATION"	=>	"none",
		"SHOW_CONTROLS"	=>	"Y",
		"SET_TITLE" => "N",
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
	),
	$component
);
?>