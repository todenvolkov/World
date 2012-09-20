<?
IncludeModuleLangFile(__FILE__);

class CAllCurrencyRates
{
	function Add($arFields)
	{
		global $DB;
		$arMsg = Array();

		$db_result = $DB->Query("SELECT 'x' ".
			"FROM b_catalog_currency_rate ".
			"WHERE CURRENCY = '".$DB->ForSql($arFields["CURRENCY"], 3)."' ".
			"	AND DATE_RATE = ".$DB->CharToDateFunction($DB->ForSql($arFields["DATE_RATE"]), "SHORT")." ");
		if ($db_result->Fetch())
		{
			$arMsg[] = array("id"=>"DATE_RATE", "text"=> GetMessage("ERROR_ADD_REC2"));
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		else
		{
			$GLOBALS["stackCacheManager"]->Clear("currency_rate");

			unset($arFields["ID"]);
			$ID = $DB->Add("b_catalog_currency_rate", $arFields);
			return $ID;


			/*
			$arInsert = $DB->PrepareInsert("b_catalog_currency_rate", $arFields);

			$strSql =
				"INSERT INTO b_catalog_currency_rate(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = IntVal($DB->LastID());
			*/
		}
		//return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$ID = IntVal($ID);
		$arMsg = Array();

		$db_result = $DB->Query("SELECT 'x' ".
			"FROM b_catalog_currency_rate ".
			"WHERE CURRENCY = '".$DB->ForSql($arFields["CURRENCY"], 3)."' ".
			"	AND DATE_RATE = ".$DB->CharToDateFunction($DB->ForSql($arFields["DATE_RATE"]), "SHORT")." ".
			"	AND ID<>".$ID." ");
		if ($db_result->Fetch())
		{
			$arMsg[] = array("id"=>"DATE_RATE", "text"=> GetMessage("ERROR_ADD_REC2"));
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		else
		{
			$GLOBALS["stackCacheManager"]->Clear("currency_rate");

			$strUpdate = $DB->PrepareUpdate("b_catalog_currency_rate", $arFields);
			$strSql = "UPDATE b_catalog_currency_rate SET ".$strUpdate." WHERE ID = ".$ID." ";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return true;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = IntVal($ID);

		$GLOBALS["stackCacheManager"]->Clear("currency_rate");

		$strSql = "DELETE FROM b_catalog_currency_rate WHERE ID = ".$ID." ";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return true;
	}

	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql = 
			"SELECT C.*, ".$DB->DateToCharFunction("C.DATE_RATE", "SHORT")." as DATE_RATE ".
			"FROM b_catalog_currency_rate C ".
			"WHERE ID = ".$ID." ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return false;
	}


	function GetList(&$by, &$order, $arFilter=Array())
	{
		global $DB, $DBType;
		$arSqlSearch = Array();

		if(!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for($i=0; $i<count($filter_keys); $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if (strlen($val)<=0) continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch(strtoupper($key))
			{
			case "CURRENCY":
				$arSqlSearch[] = "C.CURRENCY = '".$val."'";
				break;
			case "DATE_RATE":
				$arSqlSearch[] = "(C.DATE_RATE ".($bInvert?"<":">=")." ".($DBType == "mysql"?"CAST(":"").$DB->CharToDateFunction($DB->ForSql($val), "SHORT").($DBType == "mysql"?" AS DATE)":"")."".($bInvert?"":" OR C.DATE_RATE IS NULL").")";
				break;
			}
		}

		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
		{
			if($i>0)
				$strSqlSearch .= " AND ";
			else
				$strSqlSearch = " WHERE ";

			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql =
			"SELECT C.ID, C.CURRENCY, C.RATE_CNT, C.RATE, ".$DB->DateToCharFunction("C.DATE_RATE", "SHORT")." as DATE_RATE ".
			"FROM b_catalog_currency_rate C ".
			$strSqlSearch;

		if (strtolower($by) == "curr") $strSqlOrder = " ORDER BY C.CURRENCY ";
		elseif (strtolower($by) == "rate") $strSqlOrder = " ORDER BY C.RATE ";
		else
		{
			$strSqlOrder = " ORDER BY C.DATE_RATE "; 
			$by = "date";
		}

		if (strtolower($order)=="desc") 
			$strSqlOrder .= " desc "; 
		else
			$order = "asc"; 

		$strSql .= $strSqlOrder;
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $res;
	}
}
?>