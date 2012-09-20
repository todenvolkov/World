<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");?><?$APPLICATION->IncludeComponent(
	"bitrix:sale.export.1c",
	"",
	Array(
		"SITE_LIST" => "s1",
		"EXPORT_PAYED_ORDERS" => "N",
		"EXPORT_ALLOW_DELIVERY_ORDERS" => "N",
		"EXPORT_FINAL_ORDERS" => "N",
		"REPLACE_CURRENCY" => "СЂСѓР±.",
		"GROUP_PERMISSIONS" => array("1", "2", "3", "4", "5", "6"),
		"USE_ZIP" => "N"
	),
false
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>