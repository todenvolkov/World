<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}
$arParams["PATH_TO_BASKET"] = Trim($arParams["PATH_TO_BASKET"]);
$arParams["PATH_TO_ORDER"] = Trim($arParams["PATH_TO_ORDER"]);

$arItems = GetBasketList();
$bReady = False;
$bDelay = False;
$bNotAvail = False;
for ($i = 0; $i<count($arItems); $i++)
{
	if ($arItems[$i]["DELAY"]=="N" && $arItems[$i]["CAN_BUY"]=="Y")
		$bReady = True;
	elseif ($arItems[$i]["DELAY"]=="Y" && $arItems[$i]["CAN_BUY"]=="Y")
		$bDelay = True;
	elseif ($arItems[$i]["CAN_BUY"]=="N")
		$bNotAvail = True;
	$arItems[$i]["PRICE_FORMATED"] = SaleFormatCurrency($arItems[$i]["PRICE"], $arItems[$i]["CURRENCY"]);
}
$arResult["READY"] = (($bReady)?"Y":"N");
$arResult["DELAY"] = (($bDelay)?"Y":"N");
$arResult["NOTAVAIL"] = (($bNotAvail)?"Y":"N");
$arResult["ITEMS"] = $arItems;
	
$this->IncludeComponentTemplate();
?>