<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:support.faq.element.detail",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"AJAX_MODE" => "N",
		"SECTION" => $arParams["SECTION"],
		"EXPAND_LIST" => $arParams["EXPAND_LIST"],
		"LINK_ELEMENTS" => "",
		"LINK_ELEMENTS_LINK" => "/support/faq/?ELEMENT_ID=#ELEMENT_ID#",
		"AJAX_OPTION_SHADOW" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",

		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
	),
	$component
);?>