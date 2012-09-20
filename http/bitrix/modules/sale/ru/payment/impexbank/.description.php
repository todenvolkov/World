<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/impexbank.php"));

$psTitle = GetMessage("SIBP_DTITLE");
$psDescription = GetMessage("SIBP_DDESCR");

$arPSCorrespondence = array(
		"SHOP_ACCOUNT" => array(
				"NAME" => "Код магазина",
				"DESCR" => "Код магазина, который получен от ИмпексБанка",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_NAME" => array(
				"NAME" => "Наименование магазина",
				"DESCR" => "Наименование магазина",
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>