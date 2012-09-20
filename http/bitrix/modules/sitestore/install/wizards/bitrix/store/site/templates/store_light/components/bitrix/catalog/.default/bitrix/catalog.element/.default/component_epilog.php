<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

__IncludeLang($_SERVER["DOCUMENT_ROOT"].$templateFolder."/lang/".LANGUAGE_ID."/template.php");

$APPLICATION->AddHeadScript('/bitrix/templates/'.SITE_TEMPLATE_ID.'/jquery/fancybox/jquery.fancybox-1.3.1.pack.js');
$APPLICATION->SetAdditionalCSS('/bitrix/templates/'.SITE_TEMPLATE_ID.'/jquery/fancybox/jquery.fancybox-1.3.1.css');

if (CModule::IncludeModule('sale'))
{
	$dbBasketItems = CSaleBasket::GetList(
		array(
			"ID" => "ASC"
		),
		array(
			"PRODUCT_ID" => $arResult['ID'],
			"FUSER_ID" => CSaleBasket::GetBasketUserID(),
			"LID" => SITE_ID,
			"ORDER_ID" => "NULL",
		),
		false,
		false,
		array()
	);

	if ($arBasket = $dbBasketItems->Fetch())
	{
		if($arBasket["DELAY"] == "Y")
			echo "<script type=\"text/javascript\">$(function() {disableAddToCart('catalog_add2cart_link', 'detail', '".GetMessage("CATALOG_IN_CART_DELAY")."')});</script>\r\n";
		else
			echo "<script type=\"text/javascript\">$(function() {disableAddToCart('catalog_add2cart_link', 'detail', '".GetMessage("CATALOG_IN_BASKET")."')});</script>\r\n";
	}
}

if ($arParams['USE_COMPARE'])
{
	if (isset(
		$_SESSION[$arParams["COMPARE_NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$arResult['ID']]
	))
	{
		echo '<script type="text/javascript">$(function(){disableAddToCompare(\'catalog_add2compare_link\', \''.GetMessage("CATALOG_IN_COMPARE").'\');})</script>';
	}
}

if (array_key_exists("PROPERTIES", $arResult) && is_array($arResult["PROPERTIES"]))
{
	$sticker = "";

	foreach (Array("SPECIALOFFER", "NEWPRODUCT", "SALELEADER") as $propertyCode)
	{
		if (array_key_exists($propertyCode, $arResult["PROPERTIES"]) && intval($arResult["PROPERTIES"][$propertyCode]["PROPERTY_VALUE_ID"]) > 0)
			$sticker .= "&nbsp;<span class=\"sticker\">".$arResult["PROPERTIES"][$propertyCode]["NAME"]."</span>";
	}

	if ($sticker != "")
		$APPLICATION->SetPageProperty("ADDITIONAL_TITLE", $sticker);
}
?>