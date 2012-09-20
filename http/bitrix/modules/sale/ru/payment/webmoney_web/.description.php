<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/webmoney_web.php"));

$psTitle = GetMessage("SWMWP_DTITLE");
$psDescription  = GetMessage("SWMWP_DDESCR");

$arPSCorrespondence = array(
		"SHOP_ACCT" => array(
				"NAME" => "Номер R кошелька",
				"DESCR" => "В виде буквы R и 12 цифр. Его необходимо взять из подписанного соглашения.",
				"VALUE" => "",
				"TYPE" => ""
			),
		"TEST_MODE" => array(
				"NAME" => "Тестовый режим",
				"DESCR" => "В режиме тестирования: 0 - успешный платеж; 1 - не успешный; 2 - около 80% успешных, остальные - не успешные",
				"VALUE" => "",
				"TYPE" => ""
			),
		"CNST_SECRET_KEY" => array(
				"NAME" => "Secret Key",
				"DESCR" => "Устанавливается в настройках сервиса Web Merchant Interface",
				"VALUE" => "",
				"TYPE" => ""
			),
		"ORDER_ID" => Array(
				"NAME" => "ID заказа",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"DATE_INSERT" => Array(
				"NAME" => "Дата заказа",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"SHOULD_PAY" => Array(
				"NAME" => "Сумма к оплате",
				"DESCR" => "",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"RESULT_URL" => Array(
				"NAME" => "Адрес для оповещения",
				"DESCR" => "URL (на веб-сайте продавца), на который будет сервис Web Merchant Interface посылает HTTP POST оповещение о совершении платежа с его детальными реквизитами. URL должен иметь префикс http:// или https://",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"SUCCESS_URL" => Array(
				"NAME" => "Адрес при успешной оплате",
				"DESCR" => "URL (на веб-сайте продавца), на который будет переведен интернет-браузер покупателя в случае успешного выполнения платежа в сервисе Web Merchant Interface. URL должен иметь префикс http:// или https://.",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"FAIL_URL" => Array(
				"NAME" => "Адрес при ошибке оплаты",
				"DESCR" => "URL (на веб-сайте продавца), на который будет переведен интернет-браузер покупателя в том случае, если платеж в сервисе Web Merchant Interface не был выполнен по каким-то причинам. URL должен иметь префикс http:// или https://.",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),

	);
?>