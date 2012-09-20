<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH?>/favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/styles.css" />
<title>Печать заказа</title>
</head>
<body onload='window.print();'>
<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php'); 
?>
<?$APPLICATION->IncludeComponent("bitrix:sale.personal.order", "list", array(
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
);?>
</body>
</html>
