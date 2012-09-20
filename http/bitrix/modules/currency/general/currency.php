<?
//IncludeModuleLangFile(__FILE__);

class CAllCurrency
{
	function GetCurrency($currency)
	{
		$arRes = CCurrency::GetByID($currency);
		//$arRes = $dbRes->Fetch();
		return $arRes;
	}

	function Add($arFields)
	{
		global $DB;

		$db_result = $DB->Query("SELECT 'x' FROM b_catalog_currency WHERE CURRENCY = '".$DB->ForSql($arFields["CURRENCY"], 3)."'");
		if ($db_result->Fetch())
			return false;
		else
		{
			$arInsert = $DB->PrepareInsert("b_catalog_currency", $arFields);

			$strSql =
				"INSERT INTO b_catalog_currency(".$arInsert[0].", DATE_UPDATE) ".
				"VALUES(".$arInsert[1].", ".$DB->GetNowFunction().")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$GLOBALS["CACHE_MANAGER"]->Clean("currency_currency_list");
			$GLOBALS["CACHE_MANAGER"]->Clean("currency_base_currency");
		}

		return $arFields["CURRENCY"];
	}

	function Update($currency, $arFields)
	{
		global $DB;

		$GLOBALS["CACHE_MANAGER"]->Clean("currency_base_currency");

		$bCanUpdate = False;
		if ($currency==$arFields["CURRENCY"])
		{
			$strUpdate = $DB->PrepareUpdate("b_catalog_currency", $arFields);
			$strSql = "UPDATE b_catalog_currency SET ".$strUpdate.", DATE_UPDATE = ".$DB->GetNowFunction()." WHERE CURRENCY = '".$DB->ForSql($currency, 3)."' ";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$GLOBALS["CACHE_MANAGER"]->Clean("currency_currency_list");

			return $arFields["CURRENCY"];
		}
		else
		{
			die("RYH76T85RF45");
			if (CCurrency::Delete($currency))
			{
				return CCurrency::Add($arFields);
			}
			else
			{
				return False;
			}
		}
	}

	function Delete($currency)
	{
		global $DB;

		$bCanDelete = true;
		$db_events = GetModuleEvents("currency", "OnBeforeCurrencyDelete");
		while($arEvent = $db_events->Fetch())
			if(ExecuteModuleEventEx($arEvent, Array($currency))===false)
				return false;

		$events = GetModuleEvents("currency", "OnCurrencyDelete");
		while($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($currency));

		$GLOBALS["CACHE_MANAGER"]->Clean("currency_currency_list");
		$GLOBALS["CACHE_MANAGER"]->Clean("currency_base_currency");

		$DB->Query("DELETE FROM b_catalog_currency_lang WHERE CURRENCY = '".$DB->ForSQL($currency, 3)."'", true);
		$DB->Query("DELETE FROM b_catalog_currency_rate WHERE CURRENCY = '".$DB->ForSQL($currency, 3)."'", true);

		return $DB->Query("DELETE FROM b_catalog_currency WHERE CURRENCY = '".$DB->ForSQL($currency, 3)."'", true);
	}


	function GetByID($currency)
	{
		global $DB;

		$strSql =
			"SELECT CUR.* ".
			"FROM b_catalog_currency CUR ".
			"WHERE CUR.CURRENCY = '".$DB->ForSQL($currency, 3)."'";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
			return $res;

		return False;
	}


	function GetBaseCurrency()
	{
		global $DB;

		$baseCurrency = "";

		if (defined("CURRENCY_SKIP_CACHE") && CURRENCY_SKIP_CACHE)
		{
			$strSql = "SELECT CURRENCY FROM b_catalog_currency WHERE AMOUNT = 1 ";
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				$baseCurrency = $arRes["CURRENCY"];
		}
		else
		{
			$cacheTime = CURRENCY_CACHE_DEFAULT_TIME;
			if (defined("CURRENCY_CACHE_TIME"))
				$cacheTime = IntVal(CURRENCY_CACHE_TIME);

			if ($GLOBALS["CACHE_MANAGER"]->Read(CURRENCY_CACHE_TIME, "currency_base_currency"))
			{
				$baseCurrency = $GLOBALS["CACHE_MANAGER"]->Get("currency_base_currency");
			}
			else
			{
				$strSql = "SELECT CURRENCY FROM b_catalog_currency WHERE AMOUNT = 1 ";
				$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if ($arRes = $dbRes->Fetch())
					$baseCurrency = $arRes["CURRENCY"];

				$GLOBALS["CACHE_MANAGER"]->Set("currency_base_currency", $baseCurrency);
			}
		}

		return $baseCurrency;
	}


	function SelectBox($sFieldName, $sValue, $sDefaultValue = "", $bFullName = True, $JavaFunc = "", $sAdditionalParams = "")
	{
		$s = '<select name="'.$sFieldName.'"';
		if (strlen($JavaFunc)>0) $s .= ' OnChange="'.$JavaFunc.'"';
		if (strlen($sAdditionalParams)>0) $s .= ' '.$sAdditionalParams.' ';
		$s .= '>'."\n";
		$found = false;

		$dbCurrencyList = CCurrency::GetList(($by="sort"), ($order="asc"));
		while ($arCurrency = $dbCurrencyList->Fetch())
		{
			$found = ($arCurrency["CURRENCY"] == $sValue);
			$s1 .= '<option value="'.$arCurrency["CURRENCY"].'"'.($found ? ' selected':'').'>'.htmlspecialchars($arCurrency["CURRENCY"]).(($bFullName)?(' ('.htmlspecialchars($arCurrency["FULL_NAME"]).')'):"").'</option>'."\n";
		}
		if (strlen($sDefaultValue)>0) 
			$s .= "<option value='' ".($found ? "" : "selected").">".htmlspecialchars($sDefaultValue)."</option>";
		return $s.$s1.'</select>';
	}
}
?>