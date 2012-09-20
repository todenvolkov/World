<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/paycash.php"));

$psTitle = GetMessage("SPCP_DTITLE");
$psDescription = GetMessage("SPCP_DDESCR");

$arPSCorrespondence = array(
		"SHOP_ACCOUNT" => array(
				"NAME" => "Код магазина",
				"DESCR" => "Код магазина, который получен от Яндекс",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_KEY_ID" => array(
				"NAME" => "Код ключа",
				"DESCR" => "Код ключа, который получен от Яндекс",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_KEY" => array(
				"NAME" => "Ключ",
				"DESCR" => "Ключ, который получен от Яндекс",
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>