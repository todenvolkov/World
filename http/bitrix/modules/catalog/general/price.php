<?
IncludeModuleLangFile(__FILE__);

/***********************************************************************/
/***********  CPrice  **************************************************/
/***********************************************************************/
class CAllPrice
{
	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "PRODUCT_ID") || $ACTION=="ADD") && IntVal($arFields["PRODUCT_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGP_EMPTY_PRODUCT"), "EMPTY_PRODUCT_ID");
			return false;
		}
		if ((is_set($arFields, "CATALOG_GROUP_ID") || $ACTION=="ADD") && IntVal($arFields["CATALOG_GROUP_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGP_EMPTY_CATALOG_GROUP"), "EMPTY_CATALOG_GROUP_ID");
			return false;
		}
		if ((is_set($arFields, "CURRENCY") || $ACTION=="ADD") && strlen($arFields["CURRENCY"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGP_EMPTY_CURRENCY"), "EMPTY_CURRENCY");
			return false;
		}

		if (is_set($arFields, "PRICE") || $ACTION=="ADD")
		{
			$arFields["PRICE"] = str_replace(",", ".", $arFields["PRICE"]);
			$arFields["PRICE"] = DoubleVal($arFields["PRICE"]);
		}

		if ((is_set($arFields, "QUANTITY_FROM") || $ACTION=="ADD") && IntVal($arFields["QUANTITY_FROM"]) <= 0)
			$arFields["QUANTITY_FROM"] = False;
		if ((is_set($arFields, "QUANTITY_TO") || $ACTION=="ADD") && IntVal($arFields["QUANTITY_TO"]) <= 0)
			$arFields["QUANTITY_TO"] = False;

		return True;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return False;

		if (!CPrice::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("catalog", "OnBeforePriceUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
				return false;

		$strUpdate = $DB->PrepareUpdate("b_catalog_price", $arFields);
		$strSql = "UPDATE b_catalog_price SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$events = GetModuleEvents("catalog", "OnPriceUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}


	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);
		return $DB->Query("DELETE FROM b_catalog_price WHERE ID = ".$ID." ", true);
	}


	function GetBasePrice($productID, $quantityFrom = false, $quantityTo = false)
	{
		global $DB;

		$productID = IntVal($productID);
		if ($quantityFrom !== false)
			$quantityFrom = IntVal($quantityFrom);
		if ($quantityTo !== false)
			$quantityTo = IntVal($quantityTo);

		$arFilter = array(
				"BASE" => "Y",
				"PRODUCT_ID" => $productID
			);

		if ($quantityFrom !== false)
			$arFilter["QUANTITY_FROM"] = $quantityFrom;
		if ($quantityTo !== false)
			$arFilter["QUANTITY_TO"] = $quantityTo;

		$db_res = CPrice::GetList(
				array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
				$arFilter
			);
		if ($res = $db_res->Fetch())
			return $res;

		return false;
	}


	function SetBasePrice($ProductID, $Price, $Currency, $quantityFrom = 0, $quantityTo = 0)
	{
		global $DB;

		$arFields = array();
		$arFields["PRICE"] = DoubleVal($Price);
		$arFields["CURRENCY"] = $Currency;
		$arFields["QUANTITY_FROM"] = IntVal($quantityFrom);
		$arFields["QUANTITY_TO"] = IntVal($quantityTo);
		$arFields["EXTRA_ID"] = False;

		if ($arBasePrice = CPrice::GetBasePrice($ProductID, $quantityFrom, $quantityTo))
		{
			CPrice::Update($arBasePrice["ID"], $arFields);
		}
		else
		{
			$arBaseGroup = CCatalogGroup::GetBaseGroup();
			$arFields["CATALOG_GROUP_ID"] = $arBaseGroup["ID"];
			$arFields["PRODUCT_ID"] = $ProductID;
			CPrice::Add($arFields);
		}

		return true;
	}


	function ReCalculate($TYPE, $ID, $VAL)
	{
		$ID = IntVal($ID);
		if ($TYPE=="EXTRA")
		{
			$db_res = CPrice::GetList(
					array("EXTRA_ID" => "ASC"),
					array("EXTRA_ID" => $ID)
				);
			while ($res = $db_res->Fetch())
			{
				unset($arFields);
				$arFields = array();
				if ($arBasePrice = CPrice::GetBasePrice($res["PRODUCT_ID"], $res["QUANTITY_FROM"], $res["QUANTITY_TO"]))
				{
					$arFields["PRICE"] = RoundEx($arBasePrice["PRICE"] * (1 + 1 * $VAL / 100), 2);
					$arFields["CURRENCY"] = $arBasePrice["CURRENCY"];
					CPrice::Update($res["ID"], $arFields);
				}
			}
		}
		else
		{
			$db_res = CPrice::GetList(array("PRODUCT_ID" => "ASC"), array("PRODUCT_ID" => $ID));
			while ($res = $db_res->Fetch())
			{
				if (IntVal($res["EXTRA_ID"])>0)
				{
					$res1 = CExtra::GetByID($res["EXTRA_ID"]);
					unset($arFields);
					$arFields["PRICE"] = $VAL * (1 + 1 * $res1["PERCENTAGE"] / 100);
					CPrice::Update($res["ID"], $arFields);
				}
			}
		}
	}


	function OnCurrencyDelete($Currency)
	{
		global $DB;
		if (strlen($Currency)<=0) return false;

		$strSql = 
			"DELETE FROM b_catalog_price ".
			"WHERE CURRENCY = '".$DB->ForSql($Currency)."' ";

		return $DB->Query($strSql, true);
	}

	function OnIBlockElementDelete($ProductID)
	{
		global $DB;
		$ProductID = IntVal($ProductID);
		$strSql = 
			"DELETE ".
			"FROM b_catalog_price ".
			"WHERE PRODUCT_ID = ".$ProductID." ";
		return $DB->Query($strSql, true);
	}

	function DeleteByProduct($ProductID, $arExceptionIDs = array())
	{
		global $DB;

		$ProductID = IntVal($ProductID);

		$strExceptionIDs = "0";
		for ($i = 0; $i < count($arExceptionIDs); $i++)
		{
			$strExceptionIDs .= ",".IntVal($arExceptionIDs[$i]);
		}

		$strSql = 
			"DELETE ".
			"FROM b_catalog_price ".
			"WHERE PRODUCT_ID = ".$ProductID." ".
			"	AND ID NOT IN (".$strExceptionIDs.") ";

		return $DB->Query($strSql, true);
	}
}
?>