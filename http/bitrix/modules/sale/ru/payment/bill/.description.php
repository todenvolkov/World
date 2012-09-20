<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/bill.php"));

$psTitle = GetMessage("SBLP_DTITLE");
$psDescription = GetMessage("SBLP_DDESCR");

$arPSCorrespondence = array(
		"DATE_INSERT" => array(
				"NAME" => "Дата заказа",
				"DESCR" => "Дата оформления заказа",
				"VALUE" => "DATE_INSERT",
				"TYPE" => "ORDER"
			),

		"SELLER_NAME" => array(
				"NAME" => "Название компании-поставщика",
				"DESCR" => "Название компании-поставщика (продавца)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_ADDRESS" => array(
				"NAME" => "Адрес компании-поставщика",
				"DESCR" => "Адрес компании-поставщика (продавца)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_PHONE" => array(
				"NAME" => "Телефон компании-поставщика",
				"DESCR" => "Телефон компании-поставщика (продавца)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_INN" => array(
				"NAME" => "ИНН компании-поставщика",
				"DESCR" => "ИНН компании-поставщика (продавца)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_KPP" => array(
				"NAME" => "КПП компании-поставщика",
				"DESCR" => "КПП компании-поставщика (продавца)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_RS" => array(
				"NAME" => "Расчетный счет компании-поставщика",
				"DESCR" => "Расчетный счет компании-поставщика (продавца) с указанием банка и города",
				"VALUE" => "р/с ... в \"Банк\", г. Город",
				"TYPE" => ""
			),
		"SELLER_KS" => array(
				"NAME" => "Корреспондентский счет компании-поставщика",
				"DESCR" => "Корреспондентский счет компании-поставщика (продавца)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SELLER_BIK" => array(
				"NAME" => "БИК компании-поставщика",
				"DESCR" => "БИК компании-поставщика (продавца)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"BUYER_NAME" => array(
				"NAME" => "Название компании-заказчика",
				"DESCR" => "Название компании-заказчика (покупателя)",
				"VALUE" => "COMPANY_NAME",
				"TYPE" => "PROPERTY"
			),
		"BUYER_INN" => array(
				"NAME" => "ИНН компании-заказчика",
				"DESCR" => "ИНН компании-заказчика (покупателя)",
				"VALUE" => "INN",
				"TYPE" => "PROPERTY"
			),
		"BUYER_ADDRESS" => array(
				"NAME" => "Адрес компании-заказчика",
				"DESCR" => "Адрес компании-заказчика (покупателя)",
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"BUYER_PHONE" => array(
				"NAME" => "Телефон компании-заказчика",
				"DESCR" => "Телефон компании-заказчика (покупателя)",
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			),
		"BUYER_FAX" => array(
				"NAME" => "Факс компании-заказчика",
				"DESCR" => "Факс компании-заказчика (покупателя)",
				"VALUE" => "FAX",
				"TYPE" => "PROPERTY"
			),
		"BUYER_PAYER_NAME" => array(
				"NAME" => "Контактное лицо компании-заказчика",
				"DESCR" => "Контактное лицо компании-заказчика (покупателя)",
				"VALUE" => "PAYER_NAME",
				"TYPE" => "PROPERTY"
			),

		"PATH_TO_STAMP" => array(
				"NAME" => "Печать",
				"DESCR" => "Путь к изображению печати поставщика на сайте",
				"VALUE" => "",
				"TYPE" => ""
			)
	);
?>