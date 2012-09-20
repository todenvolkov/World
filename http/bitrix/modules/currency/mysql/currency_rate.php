<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/general/currency_rate.php");

class CCurrencyRates extends CAllCurrencyRates
{
	function ConvertCurrency($valSum, $curFrom, $curTo, $valDate = "")
	{
		return DoubleVal(DoubleVal($valSum) * CCurrencyRates::GetConvertFactor($curFrom, $curTo, $valDate));
	}

	function GetConvertFactor($curFrom, $curTo, $valDate = "")
	{
		global $DB;

		if(strlen($curFrom) <= 0 || strlen($curTo) <= 0)
			return 0;
		
		if (strlen($valDate) <= 0)
			$valDate = date("Y-m-d");
		list($dpYear, $dpMonth, $dpDay) = explode("-", $valDate, 3);
		$dpDay += 1;
		if($dpYear < 2038 && $dpYear > 1970)
			$valDate = date("Y-m-d", mktime(0, 0, 0, $dpMonth, $dpDay, $dpYear));
		else
			$valDate = date("Y-m-d");

		$curFromRate = 0;
		$curFromRateCnt = 0;
		$curToRate = 1;
		$curToRateCnt = 1;

		if (defined("CURRENCY_SKIP_CACHE") && CURRENCY_SKIP_CACHE)
		{
			$strSql = 
				"SELECT C.AMOUNT, C.AMOUNT_CNT, CR.RATE, CR.RATE_CNT ".
				"FROM b_catalog_currency C ".
				"	LEFT JOIN b_catalog_currency_rate CR ".
				"		ON (C.CURRENCY = CR.CURRENCY AND CR.DATE_RATE < '".$valDate."') ".
				"WHERE C.CURRENCY = '".$DB->ForSql($curFrom)."' ".
				"ORDER BY DATE_RATE DESC";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($res = $db_res->Fetch())
			{
				$curFromRate = DoubleVal($res["RATE"]);
				$curFromRateCnt = IntVal($res["RATE_CNT"]);
				if ($curFromRate<=0)
				{
					$curFromRate = DoubleVal($res["AMOUNT"]);
					$curFromRateCnt = IntVal($res["AMOUNT_CNT"]);
				}
			}

			$strSql = 
				"SELECT C.AMOUNT, C.AMOUNT_CNT, CR.RATE, CR.RATE_CNT ".
				"FROM b_catalog_currency C ".
				"	LEFT JOIN b_catalog_currency_rate CR ".
				"		ON (C.CURRENCY = CR.CURRENCY AND CR.DATE_RATE < '".$valDate."') ".
				"WHERE C.CURRENCY = '".$DB->ForSql($curTo)."' ".
				"ORDER BY DATE_RATE DESC";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($res = $db_res->Fetch())
			{
				$curToRate = DoubleVal($res["RATE"]);
				$curToRateCnt = DoubleVal($res["RATE_CNT"]);
				if ($curToRate<=0)
				{
					$curToRate = DoubleVal($res["AMOUNT"]);
					$curToRateCnt = IntVal($res["AMOUNT_CNT"]);
				}
			}
		}
		else
		{
			$cacheTime = CURRENCY_CACHE_DEFAULT_TIME;
			if (defined("CURRENCY_CACHE_TIME"))
				$cacheTime = IntVal(CURRENCY_CACHE_TIME);

			$strCacheKey = "CR_".$valDate."_".$curFrom."_".$curTo;

			$GLOBALS["stackCacheManager"]->SetLength("currency_rate", 10);
			$GLOBALS["stackCacheManager"]->SetTTL("currency_rate", $cacheTime);
			if ($GLOBALS["stackCacheManager"]->Exist("currency_rate", $strCacheKey))
			{
				$arResult = $GLOBALS["stackCacheManager"]->Get("currency_rate", $strCacheKey);

				$curFromRate = $arResult["curFromRate"];
				$curFromRateCnt = $arResult["curFromRateCnt"];
				$curToRate = $arResult["curToRate"];
				$curToRateCnt = $arResult["curToRateCnt"];
			}
			else
			{
				$strSql = 
					"SELECT C.AMOUNT, C.AMOUNT_CNT, CR.RATE, CR.RATE_CNT ".
					"FROM b_catalog_currency C ".
					"	LEFT JOIN b_catalog_currency_rate CR ".
					"		ON (C.CURRENCY = CR.CURRENCY AND CR.DATE_RATE < '".$valDate."') ".
					"WHERE C.CURRENCY = '".$DB->ForSql($curFrom)."' ".
					"ORDER BY DATE_RATE DESC";
				$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if ($res = $db_res->Fetch())
				{
					$curFromRate = DoubleVal($res["RATE"]);
					$curFromRateCnt = IntVal($res["RATE_CNT"]);
					if ($curFromRate<=0)
					{
						$curFromRate = DoubleVal($res["AMOUNT"]);
						$curFromRateCnt = IntVal($res["AMOUNT_CNT"]);
					}
				}

				$strSql = 
					"SELECT C.AMOUNT, C.AMOUNT_CNT, CR.RATE, CR.RATE_CNT ".
					"FROM b_catalog_currency C ".
					"	LEFT JOIN b_catalog_currency_rate CR ".
					"		ON (C.CURRENCY = CR.CURRENCY AND CR.DATE_RATE < '".$valDate."') ".
					"WHERE C.CURRENCY = '".$DB->ForSql($curTo)."' ".
					"ORDER BY DATE_RATE DESC";
				$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if ($res = $db_res->Fetch())
				{
					$curToRate = DoubleVal($res["RATE"]);
					$curToRateCnt = DoubleVal($res["RATE_CNT"]);
					if ($curToRate<=0)
					{
						$curToRate = DoubleVal($res["AMOUNT"]);
						$curToRateCnt = IntVal($res["AMOUNT_CNT"]);
					}
				}

				$arResult = array(
					"curFromRate" => $curFromRate,
					"curFromRateCnt" => $curFromRateCnt,
					"curToRate" => $curToRate,
					"curToRateCnt" => $curToRateCnt
				);

				$GLOBALS["stackCacheManager"]->Set("currency_rate", $strCacheKey, $arResult);
			}
		}

		if($curFromRate == 0 || $curToRateCnt == 0 || $curToRate == 0 || $curFromRateCnt == 0)
			return 0;
		return DoubleVal($curFromRate*$curToRateCnt/$curToRate/$curFromRateCnt);
	}
}
?>