<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/webmoney_pci.php"));

$psTitle = GetMessage("SWMPP_DTITLE");
$psDescription = GetMessage("SWMPP_DDESCR");

$arPSCorrespondence = array(
		"ORDER_ID" => array(
				"NAME" => "Номер заказа",
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOULD_PAY" => array(
				"NAME" => "Сумма к оплате",
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"ACC_NUMBER" => array(
				"NAME" => "Номер кошелька",
				"DESCR" => "Введите сюда номер вашего кошелька",
				"VALUE" => "",
				"TYPE" => ""
			),
		"TEST_MODE" => array(
				"NAME" => "Тестовый режим",
				"DESCR" => "test - для тестового режима, иначе пустое значение",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PATH_TO_RESULT" => array(
				"NAME" => "Путь к скрипту обработки ответа платежной системы",
				"DESCR" => "Путь задается относительно корня сайта",
				"VALUE" => "",
				"TYPE" => ""
			),
		"CNST_SECRET_KEY" => array(
				"NAME" => "Пароль в системе WebMoney Transfer",
				"DESCR" => "Пароль продавца в системе WebMoney Transfer",
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>