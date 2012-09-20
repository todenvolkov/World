<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Интернет-магазин \"Мебель\"");
?>
<p>Работаем все праздники без выходных с 10 до 18, доставка день в день 9 - 22.</p>
 							
<p>Добро пожаловать в мебельный интернет-магазин! Мы предлагаем широкий ассортимент качественной мебели по адекватным ценам. Покупая мебель в нашем Интернет-магазине, вы можете быть уверены в качестве мебели - ведь мы работаем только с крупными и проверенными производителями.</p>

<?
$APPLICATION->IncludeComponent("bitrix:store.catalog.top", "new", array(
	"IBLOCK_TYPE_ID" => "catalog",
	"IBLOCK_ID" => array(
		0 => "",
		1 => "",
	),
	"ELEMENT_SORT_FIELD" => "RAND",
	"ELEMENT_SORT_ORDER" => "asc",
	"ELEMENT_COUNT" => "4",
	"LINE_ELEMENT_COUNT" => "2",
	"PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"FLAG_PROPERTY_CODE" => "NEWPRODUCT",
	"SECTION_URL" => "",
	"DETAIL_URL" => "",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"PRODUCT_QUANTITY_VARIABLE" => "quantity",
	"PRODUCT_PROPS_VARIABLE" => "prop",
	"SECTION_ID_VARIABLE" => "SECTION_ID",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "180",
	"CACHE_GROUPS" => "Y",
	"DISPLAY_COMPARE" => "N",
	"PRICE_CODE" => array(
		0 => "BASE",
	),
	"USE_PRICE_COUNT" => "N",
	"SHOW_PRICE_COUNT" => "1",
	"PRICE_VAT_INCLUDE" => "Y",
	"PRODUCT_PROPERTIES" => array(
	),
	"USE_PRODUCT_QUANTITY" => "N"
	),
	false
);
?>
<?
$APPLICATION->IncludeComponent("bitrix:store.catalog.top", "leader", array(
	"IBLOCK_TYPE_ID" => "catalog",
	"IBLOCK_ID" => array(
		0 => "",
		1 => "",
	),
	"ELEMENT_SORT_FIELD" => "RAND",
	"ELEMENT_SORT_ORDER" => "asc",
	"ELEMENT_COUNT" => "6",
	"LINE_ELEMENT_COUNT" => "2",
	"PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"FLAG_PROPERTY_CODE" => "SALELEADER",
	"SECTION_URL" => "",
	"DETAIL_URL" => "",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"PRODUCT_QUANTITY_VARIABLE" => "quantity",
	"PRODUCT_PROPS_VARIABLE" => "prop",
	"SECTION_ID_VARIABLE" => "SECTION_ID",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "180",
	"CACHE_GROUPS" => "Y",
	"DISPLAY_COMPARE" => "N",
	"PRICE_CODE" => array(
		0 => "BASE",
	),
	"USE_PRICE_COUNT" => "N",
	"SHOW_PRICE_COUNT" => "1",
	"PRICE_VAT_INCLUDE" => "Y",
	"PRODUCT_PROPERTIES" => array(
	),
	"USE_PRODUCT_QUANTITY" => "N"
	),
	false
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>