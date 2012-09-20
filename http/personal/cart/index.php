<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
?><?$APPLICATION->IncludeComponent(
	"bitrix:store.sale.basket.basket",
	"mir_sale",
	Array(
		"PATH_TO_ORDER" => "/personal/order/make/",
		"HIDE_COUPON" => "Y",
		"COLUMNS_LIST" => array("NAME", "PROPS", "PRICE", "DELETE", "DELAY"),
		"QUANTITY_FLOAT" => "N",
		"PRICE_VAT_SHOW_VALUE" => "N",
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
		"SET_TITLE" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_SHADOW" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>