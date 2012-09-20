<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Рефлексы");
?><?$APPLICATION->IncludeComponent("bitrix:catalog", ".default", array(
	"IBLOCK_TYPE" => "catalog",
	"IBLOCK_ID" => "6",
	"BASKET_URL" => "/personal/cart/",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"SECTION_ID_VARIABLE" => "SECTION_ID",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/catalog/outdoor/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_FILTER" => "N",
	"CACHE_GROUPS" => "Y",
	"DISPLAY_PANEL" => "N",
	"SET_TITLE" => "Y",
	"SET_STATUS_404" => "Y",
	"USE_FILTER" => "Y",
	"FILTER_NAME" => "",
	"FILTER_FIELD_CODE" => array(
		0 => "NAME",
		1 => "",
	),
	"FILTER_PROPERTY_CODE" => array(
		0 => "ART1", 
1 => "COMM1", 
2 => "FULL_NAME1", 
3 => "SAPL1", 
4 => "OSV1", 
5 => "PIVO1", 
6 => "DIST1", 
7 => "STREET1", 
8 => "SIZE1", 
9 => "SIDE1", 
10 => "FAME1", 
11 => "KOORD1", 
12 => "DATE1", 
13 => "DATE2", 
14 => "PRICE1", 

	),
	"FILTER_PRICE_CODE" => array(
		0 => "BASE",
	),
	"USE_REVIEW" => "N",
	"USE_COMPARE" => "Y",
	"COMPARE_NAME" => "CATALOG_COMPARE_LIST",
	"COMPARE_FIELD_CODE" => array(
		0 => "PREVIEW_TEXT",
		1 => "DETAIL_PICTURE",
		2 => "",
	),
	"COMPARE_PROPERTY_CODE" => array(
		0 => "ART1", 
1 => "COMM1", 
2 => "FULL_NAME1", 
3 => "SAPL1", 
4 => "OSV1", 
5 => "PIVO1", 
6 => "DIST1", 
7 => "STREET1", 
8 => "SIZE1", 
9 => "SIDE1", 
10 => "FAME1", 
11 => "KOORD1", 
12 => "DATE1", 
13 => "DATE2", 
14 => "PRICE1", 

	),
	"DISPLAY_ELEMENT_SELECT_BOX" => "N",
	"ELEMENT_SORT_FIELD_BOX" => "name",
	"ELEMENT_SORT_ORDER_BOX" => "asc",
	"COMPARE_ELEMENT_SORT_FIELD" => "sort",
	"COMPARE_ELEMENT_SORT_ORDER" => "asc",
	"PRICE_CODE" => array(
		0 => "BASE",
	),
	"USE_PRICE_COUNT" => "N",
	"SHOW_PRICE_COUNT" => "1",
	"PRICE_VAT_INCLUDE" => "Y",
	"PRICE_VAT_SHOW_VALUE" => "N",
	"SHOW_TOP_ELEMENTS" => "N",
	"PAGE_ELEMENT_COUNT" => "10",
	"LINE_ELEMENT_COUNT" => "1",
	"ELEMENT_SORT_FIELD" => "sort",
	"ELEMENT_SORT_ORDER" => "asc",
	"LIST_PROPERTY_CODE" => array(
		0 => "NEWPRODUCT",
		1 => "SPECIALOFFER",
		2 => "SALELEADER",
	),
	"INCLUDE_SUBSECTIONS" => "Y",
	"LIST_META_KEYWORDS" => "-",
	"LIST_META_DESCRIPTION" => "-",
	"LIST_BROWSER_TITLE" => "-",
	"DETAIL_PROPERTY_CODE" => array(
		0 => "ART1", 
1 => "COMM1", 
2 => "FULL_NAME1", 
3 => "SAPL1", 
4 => "OSV1", 
5 => "PIVO1", 
6 => "DIST1", 
7 => "STREET1", 
8 => "SIZE1", 
9 => "SIDE1", 
10 => "FAME1", 
11 => "KOORD1", 
12 => "DATE1", 
13 => "DATE2", 
14 => "PRICE1", 
15 => "MORE_PHOTO"
, 16 => "RECOMMEND"

	),
	"DETAIL_META_KEYWORDS" => "-",
	"DETAIL_META_DESCRIPTION" => "-",
	"DETAIL_BROWSER_TITLE" => "-",
	"LINK_IBLOCK_TYPE" => "",
	"LINK_IBLOCK_ID" => "",
	"LINK_PROPERTY_SID" => "",
	"LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
	"USE_ALSO_BUY" => "Y",
	"ALSO_BUY_ELEMENT_COUNT" => "3",
	"ALSO_BUY_MIN_BUYES" => "2",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "Y",
	"PAGER_TITLE" => "Товары",
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_TEMPLATE" => "arrows",
	"PAGER_DESC_NUMBERING" => "N",
	"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000000",
	"PAGER_SHOW_ALL" => "N",
	"AJAX_OPTION_ADDITIONAL" => "",
	"SEF_URL_TEMPLATES" => array(
		"sections" => "",
		"section" => "#SECTION_CODE#/",
		"element" => "#SECTION_CODE#/#ELEMENT_CODE#/",
		"compare" => "compare/",
	)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>