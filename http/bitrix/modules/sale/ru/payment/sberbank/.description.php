<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/sberbank.php"));

$psTitle = GetMessage("SSBP_DTITLE");
$psDescription = GetMessage("SSBP_DDESCR");

$arPSCorrespondence = array(
		"SELLER_PARAMS" => array(
				"NAME" => "Параметры получателя платежа",
				"DESCR" => "Параметры получателя платежа",
				"VALUE" => "ИНН XXXXXXXXXXXXX, КПП XXXXXXXXXX, \"Компания\", р/сч XXXXXXXXXX в \"Банк\", г. Город, к/сч XXXXXXXXXX, БИК XXXXXXXXX",
				"TYPE" => ""
			),
		"PAYER_NAME" => array(
				"NAME" => "Плательщик",
				"DESCR" => "ФИО плательщика",
				"VALUE" => "PAYER_NAME",
				"TYPE" => "PROPERTY"
			)
	);
?>