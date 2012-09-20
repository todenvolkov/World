<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/post.php"));

$psTitle = GetMessage("SPPP_DTITLE");
$psDescription = GetMessage("SPPP_DDESCR");

$arPSCorrespondence = array(
		"POST_ADDRESS" => array(
				"NAME" => "Адрес перевода",
				"DESCR" => "Адрес для перевода",
				"VALUE" => "страна, почтовый индекс, город, улица, дом, кабинет",
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