<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

$arParams["COUNTRY"] = intval($arParams["COUNTRY"]);
$arParams["LOCATION_VALUE"] = intval($arParams["LOCATION_VALUE"]);
$arParams["ALLOW_EMPTY_CITY"] = $arParams["ALLOW_EMPTY_CITY"] == "N" ? "N" : "Y"; 

$arParams["AJAX_CALL"] = $arParams["AJAX_CALL"] == "Y" ? "Y" : "N"; 


if ($arParams["LOCATION_VALUE"] > 0)
{
	if ($arLocation = CSaleLocation::GetByID($arParams["LOCATION_VALUE"]))
	{
		$arParams["COUNTRY"] = $arLocation["COUNTRY_ID"];
		$arParams["CITY"] = $arParams["CITY_OUT_LOCATION"] == "Y" ? $arParams["LOCATION_VALUE"] : $arLocation["CITY_ID"];
	}
}

$arResult["COUNTRY_LIST"] = array();
$rsCountryList = CSaleLocation::GetCountryList(array("SORT" => "ASC", "NAME_LANG" => "ASC"));
while ($arCountry = $rsCountryList->GetNext())
{
	$arResult["COUNTRY_LIST"][] = $arCountry;
}

$arResult["CITY_LIST"] = array();
if ($arParams["COUNTRY"] > 0)
{
	$rsLocationsList = CSaleLocation::GetList(
		array(
			"SORT" => "ASC",
            "COUNTRY_NAME_LANG" => "ASC",
            "CITY_NAME_LANG" => "ASC"
		),
		array(
			"COUNTRY_ID" => $arParams["COUNTRY"],
			"LID" => LANGUAGE_ID,
		),
		false,
		false,
		array(
			"ID", "CITY_ID", "CITY_NAME"
		)
	);
	
	while ($arCity = $rsLocationsList->GetNext())
	{
		if ($arParams['ALLOW_EMPTY_CITY'] == 'Y' || $arCity['CITY_ID'] > 0)
		{
			$arResult["CITY_LIST"][] = array(
				"ID" => $arCity[$arParams["CITY_OUT_LOCATION"] == "Y" ? "ID" : "CITY_ID"],
				"CITY_ID" => $arCity['CITY_ID'],
				"CITY_NAME" => $arCity["CITY_NAME"],
			);
		}
	}
}

$arParams["JS_CITY_INPUT_NAME"] = CUtil::JSEscape($arParams["CITY_INPUT_NAME"]);

$arTmpParams = array(
	"COUNTRY_INPUT_NAME" => $arParams["COUNTRY_INPUT_NAME"],
	"CITY_INPUT_NAME" => $arParams["CITY_INPUT_NAME"],
	"CITY_OUT_LOCATION" => $arParams["CITY_OUT_LOCATION"],
	"ALLOW_EMPTY_CITY" => $arParams["ALLOW_EMPTY_CITY"],
	"ONCITYCHANGE" => $arParams["ONCITYCHANGE"],
);

$arResult["JS_PARAMS"] = CUtil::PhpToJsObject($arTmpParams);

$this->IncludeComponentTemplate();

if ($arParams["AJAX_CALL"] != "Y")
{
	IncludeAJAX();
	$template =& $this->GetTemplate();
	$APPLICATION->AddHeadScript($template->GetFolder().'/proceed.js');
}
?>