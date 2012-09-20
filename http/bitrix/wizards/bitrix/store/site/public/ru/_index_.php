<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Интернет-магазин \"Мебель\"");
?>
<p>Работаем все праздники без выходных с 10 до 18, доставка день в день 9 - 22.</p>
 							
<p>Добро пожаловать в мебельный интернет-магазин! Мы предлагаем широкий ассортимент качественной мебели по адекватным ценам. Покупая мебель в нашем Интернет-магазине, вы можете быть уверены в качестве мебели - ведь мы работаем только с крупными и проверенными производителями.</p>

<?
$APPLICATION->IncludeComponent("bitrix:store.catalog.random", "main", array(
	"IBLOCK_TYPE_ID" => "catalog",
	"IBLOCK_ID" => array(
		0 => "-",
	),
	"RAND_COUNT" => "3",
	"DETAIL_URL" => "",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "180",
	"CACHE_GROUPS" => "Y",
	"PARENT_SECTION" => ""
	),
	false
);
?>
 <?$APPLICATION->IncludeComponent("bitrix:store.catalog.top", "new", array(
	"IBLOCK_TYPE_ID" => "catalog",
	"IBLOCK_ID" => array(
		0 => "",
		1 => "",
	),
	"ELEMENT_SORT_FIELD" => "RAND",
	"ELEMENT_SORT_ORDER" => "asc",
	"ELEMENT_COUNT" => "3",
	"LINE_ELEMENT_COUNT" => "3",
	"PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"FLAG_PROPERTY_CODE" => "NEWPRODUCT",
	"SECTION_URL" => "",
	"DETAIL_URL" => "",
	"BASKET_URL" => "/personal/basket.php",
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
);?> 
 <?$APPLICATION->IncludeComponent("bitrix:store.catalog.top", "leader", array(
	"IBLOCK_TYPE_ID" => "catalog",
	"IBLOCK_ID" => array(
		0 => "",
		1 => "",
	),
	"ELEMENT_SORT_FIELD" => "RAND",
	"ELEMENT_SORT_ORDER" => "asc",
	"ELEMENT_COUNT" => "3",
	"LINE_ELEMENT_COUNT" => "3",
	"PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"FLAG_PROPERTY_CODE" => "SALELEADER",
	"SECTION_URL" => "",
	"DETAIL_URL" => "",
	"BASKET_URL" => "/personal/basket.php",
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
);?>

<?
$APPLICATION->IncludeComponent("bitrix:news.list", "main", array(
	"IBLOCK_TYPE" => "news",
	"IBLOCK_ID" => "#NEWS_IBLOCK_ID#",
	"NEWS_COUNT" => "3",
	"SORT_BY1" => "ACTIVE_FROM",
	"SORT_ORDER1" => "DESC",
	"SORT_BY2" => "SORT",
	"SORT_ORDER2" => "ASC",
	"FILTER_NAME" => "",
	"FIELD_CODE" => array(
		0 => "",
		1 => "",
	),
	"PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"CHECK_DATES" => "Y",
	"DETAIL_URL" => "",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_FILTER" => "N",
	"CACHE_GROUPS" => "Y",
	"PREVIEW_TRUNCATE_LEN" => "",
	"ACTIVE_DATE_FORMAT" => "d M",
	"DISPLAY_PANEL" => "N",
	"SET_TITLE" => "N",
	"SET_STATUS_404" => "N",
	"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
	"ADD_SECTIONS_CHAIN" => "N",
	"HIDE_LINK_WHEN_NO_DETAIL" => "N",
	"PARENT_SECTION" => "",
	"PARENT_SECTION_CODE" => "",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "N",
	"PAGER_TITLE" => "Новости",
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_TEMPLATE" => "",
	"PAGER_DESC_NUMBERING" => "N",
	"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000000",
	"PAGER_SHOW_ALL" => "N",
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);
?>

<form action="#SITE_DIR#personal/subscribe/" method="post">
<div id="subscribe" class="form-box">
	<div class="form-textbox">
		<div class="form-textbox-border"><input type="text" value="Подписка на новости" onblur="if (this.value=='')this.value='Подписка на новости'; " onclick="if (this.value=='Подписка на новости')this.value='';this.style.color='#000000'" /></div>
	</div>
	<div class="form-button">
		<input type="submit" value="Подписаться" />
	</div>
</div>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>