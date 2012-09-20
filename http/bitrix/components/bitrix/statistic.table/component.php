<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 20;

$arParams["CACHE_FOR_ADMIN"] = $arParams["CACHE_FOR_ADMIN"]!="N";

//Check if we can not cache
if(!$arParams["CACHE_FOR_ADMIN"] && $USER->IsAdmin())
	$arParams["CACHE_TIME"] = 0;
elseif($arParams["CACHE_TYPE"] == "N" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "N"))
	$arParams["CACHE_TIME"] = 0;

$obCache = new CPHPCache;
if($obCache->StartDataCache($arParams["CACHE_TIME"], LANG, $componentPath))
{
	if(!CModule::IncludeModule("statistic"))
	{
		$obCache->AbortDataCache();
		return;
	}

	$arResult["STATISTIC"] = CTraffic::GetCommonValues(array(),true);
	if(!is_array($arResult["STATISTIC"]))
	{
		$obCache->AbortDataCache();
		return;
	}

	$arResult["TODAY"] = GetTime(time(),"SHORT");
	$arResult["NOW"] = GetTime(time(),"FULL");

	$obCache->EndDataCache($arResult);
}
else
{
	$arResult = $obCache->GetVars();
}
$arResult["IS_ADMIN"] = $USER->IsAdmin();
$this->IncludeComponentTemplate();
?>
