<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("РџСЂРѕРёР·РІРѕРґСЃС‚РІРѕ");
?> <?$APPLICATION->IncludeComponent(
	"bitrix:catalog.main",
	"",
	Array(
		"IBLOCK_TYPE" => "articles",
		"IBLOCK_URL" => "",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y"
	),
false
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>