<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/assist.php"));

$psTitle = "Assist";
$psDescription = GetMessage("SALE_ASSIST_DESCRIPTION");

$arPSCorrespondence = array(
		"SHOP_IDP" => array(
				"NAME" => GetMessage("SALE_ASSIST_SHOP_IDP_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_SHOP_IDP_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_LOGIN" => array(
				"NAME" => GetMessage("SALE_ASSIST_SHOP_LOGIN_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_SHOP_LOGIN_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_PASSWORD" => array(
				"NAME" => GetMessage("SALE_ASSIST_SHOP_PASSWORD_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_SHOP_PASSWORD_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
			
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("SALE_ASSIST_SHOULD_PAY"),
				"DESCR" => GetMessage("SALE_ASSIST_DESC_SHOULD_PAY"),
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER"
			),
		"CURRENCY" => array(
				"NAME" => GetMessage("SALE_ASSIST_CURRENCY"),
				"DESCR" => GetMessage("SALE_ASSIST_DESC_CURRENCY"),
				"VALUE" => "CURRENCY",
				"TYPE" => "ORDER"
			),

		"ORDER_ID" => array(
				"NAME" => GetMessage("SALE_ASSIST_ORDER_ID"),
				"DESCR" => GetMessage("SALE_ASSIST_DESC_ORDER_ID"),
				"VALUE" => "ID",
				"TYPE" => "ORDER"
			),
		"DATE_INSERT" => array(
				"NAME" => GetMessage("SALE_ASSIST_DATE_INSERT"),
				"DESCR" => GetMessage("SALE_ASSIST_DESC_DATE_INSERT"),
				"VALUE" => "DATE_INSERT",
				"TYPE" => "ORDER"
			),
		"SUCCESS_URL" => array(
				"NAME" => GetMessage("SALE_ASSIST_SUCCESS_URL"),
				"DESCR" => GetMessage("SALE_ASSIST_DESC_SUCCESS_URL"),
				"VALUE" => "http://www.yoursite.com/sale/payment_result.php",
				"TYPE" => ""
			),
		"FAIL_URL" => array(
				"NAME" => GetMessage("SALE_ASSIST_FAIL_URL"),
				"DESCR" => GetMessage("SALE_ASSIST_DESC_FAIL_URL"),
				"VALUE" => "http://www.yoursite.com/sale/payment_failed.php",
				"TYPE" => ""
			),

		"FIRST_NAME" => array(
				"NAME" => GetMessage("SALE_ASSIST_FIRST_NAME_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_FIRST_NAME_DESC"),
				"VALUE" => "FIRST_NAME",
				"TYPE" => "PROPERTY"
			),
		"MIDDLE_NAME" => array(
				"NAME" => GetMessage("SALE_ASSIST_MIDDLE_NAME_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_MIDDLE_NAME_DESC"),
				"VALUE" => "MIDDLE_NAME",
				"TYPE" => "PROPERTY"
			),
		"LAST_NAME" => array(
				"NAME" => GetMessage("SALE_ASSIST_LAST_NAME_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_LAST_NAME_DESC"),
				"VALUE" => "LAST_NAME",
				"TYPE" => "PROPERTY"
			),
		"EMAIL" => array(
				"NAME" => GetMessage("SALE_ASSIST_EMAIL_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_EMAIL_DESC"),
				"VALUE" => "EMAIL",
				"TYPE" => "PROPERTY"
			),
		"ADDRESS" => array(
				"NAME" => GetMessage("SALE_ASSIST_ADDRESS_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_ADDRESS_DESC"),
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"PHONE" => array(
				"NAME" => GetMessage("SALE_ASSIST_PHONE_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_PHONE_DESC"),
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			),
		"PAYMENT_CardPayment" => array(
				"NAME" => GetMessage("SALE_ASSIST_PAYMENT_CardPayment_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_PAYMENT_CardPayment_DESC"),
				"VALUE" => "1",
				"TYPE" => ""
			),
		"PAYMENT_WebMoneyPayment" => array(
				"NAME" => GetMessage("SALE_ASSIST_PAYMENT_WebMoneyPayment_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_PAYMENT_WebMoneyPayment_DESC"),
				"VALUE" => "1",
				"TYPE" => ""
			),
		"PAYMENT_RapidaPayment" => array(
				"NAME" => GetMessage("SALE_ASSIST_PAYMENT_RapidaPayment_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_PAYMENT_RapidaPayment_DESC"),
				"VALUE" => "1",
				"TYPE" => ""
			),
		"PAYMENT_EPortPayment" => array(
				"NAME" => GetMessage("SALE_ASSIST_PAYMENT_EPortPayment_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_PAYMENT_EPortPayment_DESC"),
				"VALUE" => "1",
				"TYPE" => ""
			),
		"PAYMENT_KreditPilotPayment" => array(
				"NAME" => GetMessage("SALE_ASSIST_PAYMENT_KreditPilotPayment_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_PAYMENT_KreditPilotPayment_DESC"),
				"VALUE" => "1",
				"TYPE" => ""
			),
		"PAYMENT_PayCashPayment" => array(
				"NAME" => GetMessage("SALE_ASSIST_PAYMENT_PayCashPayment_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_PAYMENT_PayCashPayment_DESC"),
				"VALUE" => "1",
				"TYPE" => ""
			),
		"PAYMENT_AssistIDCCPayment" => array(
				"NAME" => GetMessage("SALE_ASSIST_PAYMENT_AssistIDCCPayment_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_PAYMENT_AssistIDCCPayment_DESC"),
				"VALUE" => "1",
				"TYPE" => ""
			),
		"PAYMENT_EPBeelinePayment" => array(
				"NAME" => GetMessage("SALE_ASSIST_PAYMENT_EPBeelinePayment_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_PAYMENT_EPBeelinePayment_DESC"),
				"VALUE" => "1",
				"TYPE" => ""
			),

		"DEMO" => array(
				"NAME" => GetMessage("SALE_ASSIST_DEMO_NAME"),
				"DESCR" => GetMessage("SALE_ASSIST_DEMO_DESC"),
				"VALUE" => "AS000",
				"TYPE" => ""
			)
	);
?>
