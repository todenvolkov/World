<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arResult['bDiscount'] = (is_array($arResult['PRICE']['DISCOUNT']) && count($arResult['PRICE']['DISCOUNT']) > 0);

if ($arResult['bDiscount'])
	$arResult['PRICE']['DISCOUNT_PRICE_F'] = CurrencyFormat(
		$arResult['PRICE']['DISCOUNT_PRICE'], 
		$arResult['PRICE']['DISCOUNT']['CURRENCY']
	);

if ($arResult['PRICE']['PRICE']['PRICE'])
	$arResult['PRICE']['PRICE_F'] = CurrencyFormat(
		$arResult['PRICE']['PRICE']['PRICE'], 
		$arResult['PRICE']['PRICE']['CURRENCY']
	);

$arResult['DESCRIPTION'] = '';

if ($arResult['PREVIEW_TEXT'])
{
	$arResult['DESCRIPTION'] = 
		$arResult['PREVIEW_TEXT_TYPE'] == 'html' 
		? $arResult['PREVIEW_TEXT'] 
		: htmlspecialchars($arResult['PREVIEW_TEXT']);
}
elseif ($arResult['DETAIL_TEXT'])
{
	$arResult['DESCRIPTION'] = 
		$arResult['DETAIL_TEXT_TYPE'] == 'html' 
		? $arResult['DETAIL_TEXT'] 
		: htmlspecialchars($arResult['DETAIL_TEXT']);
}

if(is_array($arResult["PICTURE"]))
{
	$arFileTmp = CFile::ResizeImageGet(
		$arResult['PICTURE'],
		array("width" => 75, 'height' => 225),
		BX_RESIZE_IMAGE_PROPORTIONAL,
		false
	);
	$arSize = getimagesize($_SERVER["DOCUMENT_ROOT"].$arFileTmp["src"]);

	$arResult['PICTURE_PREVIEW'] = array(
		'SRC' => $arFileTmp["src"],
		'WIDTH' => IntVal($arSize[0]),
		'HEIGHT' => IntVal($arSize[1]),
	);
}
?>