<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?><?$APPLICATION->IncludeComponent("bitrix:sale.personal.order", "list", array(
	"PROP_1" => array(
	),
	"SEF_MODE" => "N",
	"SEF_FOLDER" => "/personal/order/",
	"ORDERS_PER_PAGE" => "10",
	"PATH_TO_PAYMENT" => "/personal/order/payment/",
	"PATH_TO_BASKET" => "/personal/cart/",
	"SET_TITLE" => "Y",
	"SAVE_IN_SESSION" => "N",
	"NAV_TEMPLATE" => "arrows"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>