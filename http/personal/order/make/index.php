<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказы");
?><?$APPLICATION->IncludeComponent(
	"mir:sale.order.ajax",
	"order-mir",
	Array(
		"PATH_TO_BASKET" => "/personal/cart/",
		"PATH_TO_PERSONAL" => "/personal/order/",
		"PATH_TO_PAYMENT" => "/personal/order/payment/",
		"PAY_FROM_ACCOUNT" => "Y",
		"COUNT_DELIVERY_TAX" => "N",
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
		"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
		"ALLOW_AUTO_REGISTER" => "N",
		"SEND_NEW_USER_NOTIFY" => "Y",
		"SET_TITLE" => "Y",
		"PROP_1" => ""
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>