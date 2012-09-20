<?
IncludeModuleLangFile(__FILE__);

class CAllSaleDelivery
{
	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql =
			"SELECT * ".
			"FROM b_sale_delivery ".
			"WHERE ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function GetLocationList($arFilter = Array())
	{
		global $DB;
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
			case "DELIVERY_ID":
				$arSqlSearch[] = "DL.DELIVERY_ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
				break;
			case "LOCATION_ID":
				$arSqlSearch[] = "DL.LOCATION_ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
				break;
			case "LOCATION_TYPE":
				$arSqlSearch[] = "DL.LOCATION_TYPE ".($bInvert?"<>":"=")." '".$val."' ";
				break;
			}
		}

		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql = 
			"SELECT DL.* ".
			"FROM b_sale_delivery2location DL ".
			"WHERE 1 = 1 ".
			"	".$strSqlSearch." ";

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function CheckFields($ACTION, &$arFields)
	{
		global $DB;

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && strlen($arFields["NAME"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGD_EMPTY_DELIVERY"), "ERROR_NO_NAME");
			return false;
		}

		if ((is_set($arFields, "LID") || $ACTION=="ADD") && strlen($arFields["LID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGD_EMPTY_SITE"), "ERROR_NO_SITE");
			return false;
		}

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";
		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && IntVal($arFields["SORT"]) <= 0)
			$arFields["SORT"] = 100;

		if (is_set($arFields, "PRICE"))
		{
			$arFields["PRICE"] = str_replace(",", ".", $arFields["PRICE"]);
			$arFields["PRICE"] = DoubleVal($arFields["PRICE"]);
		}
		if ((is_set($arFields, "PRICE") || $ACTION=="ADD") && DoubleVal($arFields["PRICE"]) < 0)
			return false;

		if ((is_set($arFields, "CURRENCY") || $ACTION=="ADD") && strlen($arFields["CURRENCY"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGD_EMPTY_CURRENCY"), "ERROR_NO_CURRENCY");
			return false;
		}

		if (is_set($arFields, "ORDER_PRICE_FROM"))
		{
			$arFields["ORDER_PRICE_FROM"] = str_replace(",", ".", $arFields["ORDER_PRICE_FROM"]);
			$arFields["ORDER_PRICE_FROM"] = DoubleVal($arFields["ORDER_PRICE_FROM"]);
		}

		if (is_set($arFields, "ORDER_PRICE_TO"))
		{
			$arFields["ORDER_PRICE_TO"] = str_replace(",", ".", $arFields["ORDER_PRICE_TO"]);
			$arFields["ORDER_PRICE_TO"] = DoubleVal($arFields["ORDER_PRICE_TO"]);
		}

		if ((is_set($arFields, "LOCATIONS") || $ACTION=="ADD") && (!is_array($arFields["LOCATIONS"]) || count($arFields["LOCATIONS"]) <= 0))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGD_EMPTY_LOCATION"), "ERROR_NO_LOCATIONS");
			return false;
		}

		if (is_set($arFields, "LID"))
		{
			$dbSite = CSite::GetByID($arFields["LID"]);
			if (!$dbSite->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["LID"], GetMessage("SKGD_NO_SITE")), "ERROR_NO_SITE");
				return false;
			}
		}

		if (is_set($arFields, "CURRENCY"))
		{
			if (!($arCurrency = CCurrency::GetByID($arFields["CURRENCY"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["CURRENCY"], GetMessage("SKGD_NO_CURRENCY")), "ERROR_NO_CURRENCY");
				return false;
			}
		}

		if (is_set($arFields, "LOCATIONS"))
		{
			for ($i = 0; $i < count($arFields["LOCATIONS"]); $i++)
			{
				if ($arFields["LOCATIONS"][$i]["LOCATION_TYPE"] != "G")
					$arFields["LOCATIONS"][$i]["LOCATION_TYPE"] = "L";
			}
		}

		return True;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		
		if ($ID <= 0 || !CSaleDelivery::CheckFields("UPDATE", $arFields)) 
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_delivery", $arFields);

		$strSql = "UPDATE b_sale_delivery SET ".$strUpdate." WHERE ID = ".$ID."";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if (is_set($arFields, "LOCATIONS"))
		{
			$DB->Query("DELETE FROM b_sale_delivery2location WHERE DELIVERY_ID = ".$ID."");

			for ($i = 0; $i<count($arFields["LOCATIONS"]); $i++)
			{
				$arInsert = $DB->PrepareInsert("b_sale_delivery2location", $arFields["LOCATIONS"][$i]);

				$strSql =
					"INSERT INTO b_sale_delivery2location(DELIVERY_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}

		return $ID;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);
		$DB->Query("DELETE FROM b_sale_delivery2location WHERE DELIVERY_ID = ".$ID."", true);
		return $DB->Query("DELETE FROM b_sale_delivery WHERE ID = ".$ID."", true);
	}
}
?>