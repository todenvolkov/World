<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/general/currency.php");

class CCurrency extends CAllCurrency
{
	function __GetList(&$by, &$order, $lang = LANGUAGE_ID)
	{
		global $DB;

		$strSql =
			"SELECT CUR.CURRENCY, CUR.AMOUNT_CNT, CUR.AMOUNT, CUR.SORT, CUR.DATE_UPDATE, ".
			"	CURL.LID, CURL.FORMAT_STRING, CURL.FULL_NAME, CURL.DEC_POINT, ".
			"	CURL.THOUSANDS_SEP, CURL.DECIMALS ".
			"FROM b_catalog_currency CUR ".
			"	LEFT JOIN b_catalog_currency_lang CURL ON (CUR.CURRENCY = CURL.CURRENCY AND CURL.LID = '".$DB->ForSql($lang, 2)."') ";

		if (strtolower($by) == "currency") $strSqlOrder = " ORDER BY CUR.CURRENCY ";
		elseif (strtolower($by) == "name") $strSqlOrder = " ORDER BY CURL.FULL_NAME ";
		else
		{
			$strSqlOrder = " ORDER BY CUR.SORT "; 
			$by = "sort";
		}

		if ($order=="desc") 
			$strSqlOrder .= " desc "; 
		else
			$order = "asc"; 

		$strSql .= $strSqlOrder;
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $res;
	}

	function GetList(&$by, &$order, $lang = LANGUAGE_ID)
	{
		global $DB;

		if (defined("CURRENCY_SKIP_CACHE") && CURRENCY_SKIP_CACHE
			|| StrToLower($by) == "name"
			|| StrToLower($by) == "currency"
			|| StrToLower($order) == "desc")
		{
			$dbCurrencyList = CCurrency::__GetList($by, $order, $lang);
		}
		else
		{
			$by = "sort";
			$order = "asc";

			$cacheTime = CURRENCY_CACHE_DEFAULT_TIME;
			if (defined("CURRENCY_CACHE_TIME"))
				$cacheTime = IntVal(CURRENCY_CACHE_TIME);

			if ($GLOBALS["CACHE_MANAGER"]->Read($cacheTime, "currency_currency_list"))
			{
				$arCurrencyList = $GLOBALS["CACHE_MANAGER"]->Get("currency_currency_list");
				$dbCurrencyList = new CDBResult;
				$dbCurrencyList->InitFromArray($arCurrencyList);
			}
			else
			{
				$arCurrencyList = array();
				$dbCurrencyList = CCurrency::__GetList($by, $order, $lang);
				while ($arCurrency = $dbCurrencyList->Fetch())
					$arCurrencyList[] = $arCurrency;

				$GLOBALS["CACHE_MANAGER"]->Set("currency_currency_list", $arCurrencyList);

				$dbCurrencyList = new CDBResult;
				$dbCurrencyList->InitFromArray($arCurrencyList);
			}
		}

		return $dbCurrencyList;
	}
}
?>