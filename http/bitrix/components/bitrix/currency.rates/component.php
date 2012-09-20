<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (CModule::IncludeModule("currency")):

	$arParams["CACHE_TIME"] = is_set($arParams, "CACHE_TIME") ? intval($arParams["CACHE_TIME"]) : 86400;
	$arResult["CURRENCY"] = array();
	
	$bCache = $arParams["CACHE_TIME"] > 0 && ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"));
	if ($bCache)
	{
		$arCacheParams = array();
		foreach ($arParams as $key => $value) if (substr($key, 0, 1) != "~") $arCacheParams[$key] = $value;
		$cache = new CPHPCache;
		
		$CACHE_ID = SITE_ID."|".$componentName."|".md5(serialize($arCacheParams))."|".$USER->GetGroups();
		$CACHE_PATH = "/".SITE_ID.CComponentEngine::MakeComponentPath($componentName);
	}
	
	if ($bCache && $cache->InitCache($arParams["CACHE_TIME"], $CACHE_ID, $CACHE_PATH))
	{
		$vars = $cache->GetVars();
		$arParams = $vars["arParams"];
		$arResult = $vars["arResult"];
		
		//$cache->Output();
	}
	else
	{
		if ($bCache)
		{
			$cache->StartDataCache();
		}
		if (strlen($arParams["CURRENCY_BASE"]) <= 0)
			$arParams["CURRENCY_BASE"] = COption::GetOptionString("sale", "default_currency");

		if (strlen($arParams["CURRENCY_BASE"]) <= 0)
			$arParams["CURRENCY_BASE"] = CCurrency::GetBaseCurrency();

		if (strlen($arParams["CURRENCY_BASE"]) <= 0)
		{
			$dbCurrency = CCurrency::GetList(($by="SORT"), ($order="ASC"));
			$arCurrency = $dbCurrency->Fetch();
			$arParams["CURRENCY_BASE"] = $arCurrency["CURRENCY"];
		}

		if (StrLen($arParams["CURRENCY_BASE"]) > 0)
		{
			if (strlen($arParams["RATE_DAY"]) <= 0)
			{
				$arResult["RATE_DAY_SHOW"] = GetTime(time(), "SHORT", LANGUAGE_ID);
			}
			else
			{
				$arRATE_DAY_PARSED = ParseDateTime($arParams["RATE_DAY"], "YYYY-MM-DD");
				if(IntVal($arRATE_DAY_PARSED["YYYY"]) < 1901 || IntVal($arRATE_DAY_PARSED["YYYY"]) > 2038)
					$arResult["RATE_DAY_SHOW"] = GetTime(time(), "SHORT", LANGUAGE_ID);
				else
					$arResult["RATE_DAY_SHOW"] = GetTime(mktime(0, 0, 0, $arRATE_DAY_PARSED["MM"], $arRATE_DAY_PARSED["DD"], $arRATE_DAY_PARSED["YYYY"]), "D.M.Y", LANGUAGE_ID);
			}

			if (count($arParams["arrCURRENCY_FROM"]) > 0)
			{
				if (($arParams["CURRENCY_BASE"] == "RUR" || $arParams["CURRENCY_BASE"] == "RUB") && $arParams["SHOW_CB"] == "Y")
				{
					$bWarning = False;
					$QUERY_STR = "date_req=".$DB->FormatDate($arResult["RATE_DAY_SHOW"], CLang::GetDateFormat("SHORT", SITE_ID), "D.M.Y");
					$strQueryText = QueryGetData("www.cbr.ru", 80, "/scripts/XML_daily.asp", $QUERY_STR, $errno, $errstr);
					if (strlen($strQueryText) <= 0)
						$bWarning = True;

					if(SITE_CHARSET != "windows-1251")
						$strQueryText = $APPLICATION->ConvertCharset($strQueryText, "windows-1251", SITE_CHARSET);

					if (!$bWarning)
					{
						require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");

						$strQueryText = eregi_replace("<!DOCTYPE[^>]{1,}>", "", $strQueryText);
						$strQueryText = eregi_replace("<"."\?XML[^>]{1,}\?".">", "", $strQueryText);

						$objXML = new CDataXML();
						$objXML->LoadString($strQueryText);
						$arData = $objXML->GetArray();

						$arFields = array();
						$arResult["CURRENCY_CBRF"] = array();
		
						if (is_array($arData) && count($arData["ValCurs"]["#"]["Valute"])>0 && !empty($arData["ValCurs"]["#"]))
						{
							for ($j1 = 0; $j1<count($arData["ValCurs"]["#"]["Valute"]); $j1++)
							{
								if (in_array($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["CharCode"][0]["#"], $arParams["arrCURRENCY_FROM"]))
								{
									$arCurrency = Array(
											"CURRENCY"	=> $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["CharCode"][0]["#"],
											"RATE_CNT"	=> IntVal($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Nominal"][0]["#"]),
											"RATE"		=> DoubleVal(str_replace(",", ".", $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Value"][0]["#"]))
										);
									
									$arResult["CURRENCY_CBRF"][] = array(
										"FROM" => CurrencyFormat($arCurrency["RATE_CNT"], $arCurrency["CURRENCY"]),
										"BASE" => CurrencyFormat($arCurrency["RATE"], $arParams["CURRENCY_BASE"]),
									);
								}
							}
						}
					}
				}
				
				$arDBCurrencies = array();
				$dbCurrencyList = CCurrency::GetList(($b = ""), ($o = ""));
				while ($arCurrency = $dbCurrencyList->Fetch())
					$arDBCurrencies[$arCurrency["CURRENCY"]] = $arCurrency["AMOUNT_CNT"];

				for ($i = 0; $i < count($arParams["arrCURRENCY_FROM"]); $i++)
				{
					if (array_key_exists($arParams["arrCURRENCY_FROM"][$i], $arDBCurrencies))
					{
						$arResult["CURRENCY"][$i] = array();
						$rate = CCurrencyRates::ConvertCurrency($arDBCurrencies[$arParams["arrCURRENCY_FROM"][$i]], $arParams["arrCURRENCY_FROM"][$i], $arParams["CURRENCY_BASE"], $arParams["RATE_DAY"]);
						$arResult["CURRENCY"][$i]["FROM"] = CurrencyFormat($arDBCurrencies[$arParams["arrCURRENCY_FROM"][$i]], $arParams["arrCURRENCY_FROM"][$i]);
						$arResult["CURRENCY"][$i]["BASE"] = CurrencyFormat($rate, $arParams["CURRENCY_BASE"]);
					}
				}
			}
		}

		if ($bCache)
		{
			$cache->EndDataCache(
				array(
					"arParams" => $arParams,
					"arResult" => $arResult,
				)
			);
		}
	}

	$this->IncludeComponentTemplate();
	
	//*******************************************************
endif;
?>