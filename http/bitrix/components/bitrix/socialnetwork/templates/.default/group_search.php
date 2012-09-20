<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (IsModuleInstalled("search")):?>
	<?
	$APPLICATION->IncludeComponent(
		"bitrix:search.tags.cloud",
		"",
		Array(
			"FONT_MAX" => (IntVal($arParams["FONT_MAX"]) >0 ? $arParams["FONT_MAX"] : 20), 
			"FONT_MIN" => (IntVal($arParams["FONT_MIN"]) >0 ? $arParams["FONT_MIN"] : 10),
			"COLOR_NEW" => (strlen($arParams["COLOR_NEW"]) >0 ? $arParams["COLOR_NEW"] : "3f75a2"),
			"COLOR_OLD" => (strlen($arParams["COLOR_OLD"]) >0 ? $arParams["COLOR_OLD"] : "8D8D8D"),
			"ANGULARITY" => $arParams["ANGULARITY"], 
			"PERIOD_NEW_TAGS" => $arResult["PERIOD_NEW_TAGS"], 
			"SHOW_CHAIN" => "N", 
			"COLOR_TYPE" => $arParams["COLOR_TYPE"], 
			"WIDTH" => $arParams["WIDTH"], 
			"SEARCH" => "", 
			"TAGS" => "", 
			"SORT" => "NAME", 
			"PAGE_ELEMENTS" => "150", 
			"PERIOD" => $arParams["PERIOD"], 
			"URL_SEARCH" => $arResult["PATH_TO_GROUP_SEARCH"], 
			"TAGS_INHERIT" => "N", 
			"CHECK_DATES" => "Y", 
			"arrFILTER" => Array("socialnetwork"), 
			"CACHE_TYPE" => "A", 
			"CACHE_TIME" => "3600" 
		),
		$component
	);
	?>
	<br/>
<?endif;?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group_search",
	"",
	Array(
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_SEARCH" => $arResult["PATH_TO_GROUP_SEARCH"],
		"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
		"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
	),
	$component
);
?>
