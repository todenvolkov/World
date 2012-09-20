<?
IncludeModuleLangFile(__FILE__);

/***********************************************************************/
/***********  CCatalogProduct  *****************************************/
/***********************************************************************/
class CAllCatalogProduct
{
	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ($ACTION == "ADD" && (!is_set($arFields, "ID") || IntVal($arFields["ID"])<=0))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGP_EMPTY_ID"), "EMPTY_ID");
			return false;
		}
		if ($ACTION != "ADD" && IntVal($ID) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGP_EMPTY_ID"), "EMPTY_ID");
			return false;
		}

		if ($ACTION != "ADD")
			unset($arFields["ID"]);

		if (is_set($arFields, "ID") || $ACTION=="ADD")
			$arFields["ID"] = IntVal($arFields["ID"]);
		if (is_set($arFields, "QUANTITY") || $ACTION=="ADD")
			$arFields["QUANTITY"] = DoubleVal($arFields["QUANTITY"]);
		if (is_set($arFields, "WEIGHT") || $ACTION=="ADD")
			$arFields["WEIGHT"] = DoubleVal($arFields["WEIGHT"]);

		if (is_set($arFields, "VAT_ID") || $ACTION=="ADD")
			$arFields["VAT_ID"] = IntVal($arFields["VAT_ID"]);
		if ((is_set($arFields, "VAT_INCLUDED") || $ACTION=="ADD") && ($arFields["VAT_INCLUDED"] != "Y"))
			$arFields["VAT_INCLUDED"] = "N";
			
		if ((is_set($arFields, "QUANTITY_TRACE") || $ACTION=="ADD") && ($arFields["QUANTITY_TRACE"] != "Y"))
			$arFields["QUANTITY_TRACE"] = "N";

		if ((is_set($arFields, "PRICE_TYPE") || $ACTION=="ADD") && ($arFields["PRICE_TYPE"] != "R") && ($arFields["PRICE_TYPE"] != "T"))
			$arFields["PRICE_TYPE"] = "S";

		if (isset($GLOBALS["CATALOG_TIME_PERIOD_TYPES"]) && is_array($GLOBALS["CATALOG_TIME_PERIOD_TYPES"]))
		{
			if ((is_set($arFields, "RECUR_SCHEME_TYPE") || $ACTION=="ADD") && (StrLen($arFields["RECUR_SCHEME_TYPE"]) <= 0 || !array_key_exists($arFields["RECUR_SCHEME_TYPE"], $GLOBALS["CATALOG_TIME_PERIOD_TYPES"])))
			{
				$arRecurSchemeKeys = array_keys($GLOBALS["CATALOG_TIME_PERIOD_TYPES"]);
				$arFields["RECUR_SCHEME_TYPE"] = $arRecurSchemeKeys[1];
			}
		}

		if ((is_set($arFields, "RECUR_SCHEME_LENGTH") || $ACTION=="ADD") && (IntVal($arFields["RECUR_SCHEME_LENGTH"])<=0))
			$arFields["RECUR_SCHEME_LENGTH"] = 0;

		if ((is_set($arFields, "TRIAL_PRICE_ID") || $ACTION=="ADD") && (IntVal($arFields["TRIAL_PRICE_ID"])<=0))
			$arFields["TRIAL_PRICE_ID"] = False;

		if ((is_set($arFields, "WITHOUT_ORDER") || $ACTION=="ADD") && ($arFields["WITHOUT_ORDER"] != "Y"))
			$arFields["WITHOUT_ORDER"] = "N";

		if ((is_set($arFields, "SELECT_BEST_PRICE") || $ACTION=="ADD") && ($arFields["SELECT_BEST_PRICE"] != "N"))
			$arFields["SELECT_BEST_PRICE"] = "Y";

		return True;
	}

	function GetByIDEx($ID)
	{
		global $DB, $USER;

		$ID = IntVal($ID);
		$arFilter = Array("ID" => $ID, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");

		$dbIBlockElement = CIBlockElement::GetList(Array(), $arFilter);
		$dbIBlockElement = new CIBlockResult($dbIBlockElement->result);
		if ($arIBlockElement = $dbIBlockElement->GetNext())
		{
			if ($arIBlock = CIBlock::GetArrayByID($arIBlockElement["IBLOCK_ID"]))
			{
				$arIBlockElement["IBLOCK_ID"] = $arIBlock["ID"];
				$arIBlockElement["IBLOCK_NAME"] = htmlspecialchars($arIBlock["NAME"]);
				$arIBlockElement["~IBLOCK_NAME"] = $arIBlock["NAME"];
				$arIBlockElement["PROPERTIES"] = false;
				$dbProps = CIBlockElement::GetProperty($arIBlock["ID"], $ID, "sort", "asc", Array("ACTIVE"=>"Y", "NON_EMPTY"=>"Y"));
				if ($arProp = $dbProps->Fetch())
				{
					$arAllProps = Array();
					do
					{
						$arAllProps[
							strlen($arProp["CODE"])>0 
							? $arProp["CODE"] 
							: $arProp["ID"]
						] = array(
							"NAME"=>htmlspecialchars($arProp["NAME"]), 
							"VALUE"=>htmlspecialchars($arProp["VALUE"]), 
							"VALUE_ENUM"=>htmlspecialchars($arProp["VALUE_ENUM"]), 
							"VALUE_XML_ID"=>htmlspecialchars($arProp["VALUE_XML_ID"]), 
							"DEFAULT_VALUE"=>htmlspecialchars($arProp["DEFAULT_VALUE"]), 
							"SORT"=>htmlspecialchars($arProp["SORT"])
						);
					}
					while($arProp = $dbProps->Fetch());

					$arIBlockElement["PROPERTIES"] = $arAllProps;
				}

				// bugfix: 2007-07-31 by Sigurd
				$arIBlockElement["PRODUCT"] = CCatalogProduct::GetByID(IntVal($ID));
				/*
				$dbProduct = CCatalogProduct::GetByID(IntVal($ID));
				if ($arProduct = $dbProduct->Fetch())
				{
					$arIBlockElement["PRODUCT"] = $arProduct;
				}
				*/

				$dbPrices = CPrice::GetList(($by="SORT"), ($order="ASC"), Array("PRODUCT_ID" => $ID));
				if ($arPrices = $dbPrices->Fetch())
				{
					$arAllPrices = Array();
					do
					{
						$arAllPrices[$arPrices["CATALOG_GROUP_ID"]] = Array("EXTRA_ID"=>IntVal($arPrices["EXTRA_ID"]), "PRICE"=>DoubleVal($arPrices["PRICE"]), "CURRENCY"=>htmlspecialchars($arPrices["CURRENCY"]));
					}
					while($arPrices = $dbPrices->Fetch());

					$arIBlockElement["PRICES"] = $arAllPrices;
				}

				return $arIBlockElement;
			}
		}

		return false;
	}

	function GetByID($ID)
	{
		global $DB;
		$ID = IntVal($ID);

		if (isset($GLOBALS["CATALOG_PRODUCT_CACHE"][$ID]) && is_array($GLOBALS["CATALOG_PRODUCT_CACHE"][$ID]) && isset($GLOBALS["CATALOG_PRODUCT_CACHE"][$ID]["ID"]))
		{
			return $GLOBALS["CATALOG_PRODUCT_CACHE"][$ID];
		}
		else
		{
			$strSql = 
				"SELECT ID, QUANTITY, QUANTITY_TRACE, WEIGHT, PRICE_TYPE, RECUR_SCHEME_TYPE, RECUR_SCHEME_LENGTH, ".
				"	VAT_ID, VAT_INCLUDED, ".
				"	TRIAL_PRICE_ID, WITHOUT_ORDER, SELECT_BEST_PRICE, ".
				"	".$DB->DateToCharFunction("TIMESTAMP_X", "FULL")." as TIMESTAMP_X ".
				"FROM b_catalog_product ".
				"WHERE ID = ".$ID." ";
			
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($res = $db_res->Fetch())
			{
				$GLOBALS["CATALOG_PRODUCT_CACHE"][$ID] = $res;
				return $res;
			}
		}

		return false;
	}

	// change quantity of product $PRODUCT_ID for $DELTA_QUANTITY, or return false.
	function QuantityTracer($ProductID, $DeltaQuantity)
	{
		global $DB;

		$ProductID = IntVal($ProductID);
		$DeltaQuantity = DoubleVal($DeltaQuantity);
		if ($DeltaQuantity==0)
			return false;

		if (($arProduct = CCatalogProduct::GetByID($ProductID)) 
			&& ($arProduct["QUANTITY_TRACE"]=="Y"))
		{
			$arFields = array();
			$arFields["QUANTITY"] = DoubleVal($arProduct["QUANTITY"]) - $DeltaQuantity;
			if ($arFields["QUANTITY"] < 0)
				$arFields["QUANTITY"] = 0;

			CCatalogProduct::Update($arProduct["ID"], $arFields);

			return true;
		}

		return false;
	}

	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		// for old execution style
		if (!is_array($arOrder) && !is_array($arFilter))
		{
			$arOrder = strval($arOrder);
			$arFilter = strval($arFilter);
			if (strlen($arOrder) > 0 && strlen($arFilter) > 0)
				$arOrder = array($arOrder => $arFilter);
			else
				$arOrder = array();
			if (is_array($arGroupBy))
				$arFilter = $arGroupBy;
			else
				$arFilter = array();
			$arGroupBy = false;
		}

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "CP.ID", "TYPE" => "int"),
				"QUANTITY" => array("FIELD" => "CP.QUANTITY", "TYPE" => "double"),
				"QUANTITY_TRACE" => array("FIELD" => "CP.QUANTITY_TRACE", "TYPE" => "char"),
				"WEIGHT" => array("FIELD" => "CP.WEIGHT", "TYPE" => "double"),
				"TIMESTAMP_X" => array("FIELD" => "CP.TIMESTAMP_X", "TYPE" => "datetime"),
				"PRICE_TYPE" => array("FIELD" => "CP.PRICE_TYPE", "TYPE" => "char"),
				"RECUR_SCHEME_TYPE" => array("FIELD" => "CP.RECUR_SCHEME_TYPE", "TYPE" => "char"),
				"RECUR_SCHEME_LENGTH" => array("FIELD" => "CP.RECUR_SCHEME_LENGTH", "TYPE" => "int"),
				"TRIAL_PRICE_ID" => array("FIELD" => "CP.TRIAL_PRICE_ID", "TYPE" => "int"),
				"WITHOUT_ORDER" => array("FIELD" => "CP.WITHOUT_ORDER", "TYPE" => "char"),
				"SELECT_BEST_PRICE" => array("FIELD" => "CP.SELECT_BEST_PRICE", "TYPE" => "char"),
				"VAT_ID" => array("FIELD" => "CP.VAT_ID", "TYPE" => "int"),
				"VAT_INCLUDED" => array("FIELD" => "CP.VAT_INCLUDED", "TYPE" => "char"),
				"ELEMENT_IBLOCK_ID" => array("FIELD" => "I.IBLOCK_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_iblock_element I ON (CP.ID = I.ID)"),
				"ELEMENT_XML_ID" => array("FIELD" => "I.XML_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_iblock_element I ON (CP.ID = I.ID)"),
				"ELEMENT_NAME" => array("FIELD" => "I.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_iblock_element I ON (CP.ID = I.ID)")
			);
		// <-- FIELDS


		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_catalog_product CP ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialchars($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql = 
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_catalog_product CP ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_catalog_product CP ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialchars($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if (strlen($arSqls["GROUPBY"]) <= 0)
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				// MYSQL ONLY!!! ORACLE CODE IS TOTALLY DIFFERRENT
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialchars($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"])>0)
				$strSql .= "LIMIT ".$arNavStartParams["nTopCount"];

			//echo "!3!=".htmlspecialchars($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$ID = IntVal($ID);

		UnSet($arFields["ID"]);
		if ($ID <= 0)
			return false;

		$db_events = GetModuleEvents("catalog", "OnBeforeProductUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
				return false;

		if (!CCatalogProduct::CheckFields("UPDATE", $arFields, $ID))
			return False;

		$strUpdate = $DB->PrepareUpdate("b_catalog_product", $arFields);

		$strUpdate = Trim($strUpdate);
		if (StrLen($strUpdate) > 0)
		{
			$strSql = "UPDATE b_catalog_product SET ".$strUpdate." WHERE ID = ".$ID." ";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			unset($GLOBALS["CATALOG_PRODUCT_CACHE"][$ID]);
		}

		$events = GetModuleEvents("catalog", "OnProductUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return true;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = IntVal($ID);

		$DB->Query("DELETE FROM b_catalog_price WHERE PRODUCT_ID = ".$ID." ", True);
		$DB->Query("DELETE FROM b_catalog_product2group WHERE PRODUCT_ID = ".$ID." ", True);

		$dbDiscounts = CCatalogDiscount::GetList(array(), array("PRODUCT_ID" => $ID));
		if ($arDiscounts = $dbDiscounts->Fetch())
		{
			$cnt = CCatalogDiscount::GetList(array(), array("ID" => $arDiscounts["ID"], "!PRODUCT_ID" => $ID));
			if (IntVal($cnt) <= 0)
			{
				CCatalogDiscount::Delete($arDiscounts["ID"]);
			}
			else
			{
				$DB->Query("DELETE FROM b_catalog_discount2product WHERE PRODUCT_ID = ".$ID." ", True);
			}
		}

		unset($GLOBALS["CATALOG_PRODUCT_CACHE"][$ID]);

		return $DB->Query("DELETE FROM b_catalog_product WHERE ID = ".$ID." ", True);
	}


	function GetNearestQuantityPrice($productID, $quantity = 1, $arUserGroups = array())
	{
		global $USER;
		$renewal = "N";

		// Check input params
		$productID = IntVal($productID);
		if ($productID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Product ID is not set", "NO_PRODUCT_ID");
			return False;
		}

		$quantity = DoubleVal($quantity);
		if ($quantity <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Quantity is not set", "NO_QUANTITY");
			return False;
		}

		if (!is_array($arUserGroups) && IntVal($arUserGroups)."|" == $arUserGroups."|")
			$arUserGroups = array(IntVal($arUserGroups));

		if (!is_array($arUserGroups))
			$arUserGroups = array();

		if (!in_array(2, $arUserGroups))
			$arUserGroups[] = 2;

		$quantityDifference = -1;
		$nearestQuantity = -1;

		// Find nearest quantity
		$dbPriceList = CPrice::GetListEx(
			array(),
			array(
				"PRODUCT_ID" => $productID,
				"GROUP_GROUP_ID" => $arUserGroups,
				"GROUP_BUY" => "Y"
			),
			array("ID", "QUANTITY_FROM", "QUANTITY_TO")
		);
		while ($arPriceList = $dbPriceList->Fetch())
		{
			if ($quantity >= DoubleVal($arPriceList["QUANTITY_FROM"])
				&& ($quantity <= DoubleVal($arPriceList["QUANTITY_TO"]) || DoubleVal($arPriceList["QUANTITY_TO"]) == 0))
			{
				$nearestQuantity = $quantity;
				break;
			}

			if ($quantity < DoubleVal($arPriceList["QUANTITY_FROM"]))
			{
				$nearestQuantity_tmp = DoubleVal($arPriceList["QUANTITY_FROM"]);
				$quantityDifference_tmp = DoubleVal($arPriceList["QUANTITY_FROM"]) - $quantity;
			}
			else
			{
				$nearestQuantity_tmp = DoubleVal($arPriceList["QUANTITY_TO"]);
				$quantityDifference_tmp = $quantity - DoubleVal($arPriceList["QUANTITY_TO"]);
			}

			if ($quantityDifference < 0 || $quantityDifference_tmp < $quantityDifference)
			{
				$quantityDifference = $quantityDifference_tmp;
				$nearestQuantity = $nearestQuantity_tmp;
			}
		}

		return ($nearestQuantity > 0 ? $nearestQuantity : false);
	}

	function GetOptimalPrice($productID, $quantity = 1, $arUserGroups = array(), $renewal = "N", $arPrices = array(), $siteID = false, $arDiscountCoupons = false)
	{
		global $USER;

		// Check input params
		$productID = IntVal($productID);
		if ($productID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Product ID is not set", "NO_PRODUCT_ID");
			return False;
		}

		$quantity = DoubleVal($quantity);
		if ($quantity <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Quantity is not set", "NO_QUANTITY");
			return False;
		}

		if (!is_array($arUserGroups) && IntVal($arUserGroups)."|" == $arUserGroups."|")
			$arUserGroups = array(IntVal($arUserGroups));

		if (!is_array($arUserGroups))
			$arUserGroups = array();

		if (!in_array(2, $arUserGroups))
			$arUserGroups[] = 2;

		$dbVAT = CCatalogProduct::GetVATInfo($productID);
		if ($arVAT = $dbVAT->Fetch())
		{
			$arVAT['RATE'] = floatval($arVAT['RATE'] * 0.01);
		}
		else
		{
			$arVAT = array('RATE' => 0, 'VAT_INCLUDED' => 'N');
		}
		
		$renewal = (($renewal == "N") ? "N" : "Y");

		if (!isset($arPrices) || !is_array($arPrices))
			$arPrices = array();

		if ($siteID === false)
			$siteID = SITE_ID;

		if ($arDiscountCoupons === false)
			$arDiscountCoupons = CCatalogDiscount::GetCoupons();

		// Init base currency
		$baseCurrency = CCurrency::GetBaseCurrency();
		if (strlen($baseCurrency) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Can not determine base currency", "NO_BASE_CURRENCY");
			return False;
		}

		$arPriceMin = array();
		$totalPrice_min = -1;

		// Get price, get price discounts, count real price
		if (count($arPrices) <= 0)
		{
			$dbPriceList = CPrice::GetListEx(
				array(),
				array(
						"PRODUCT_ID" => $productID,
						"GROUP_GROUP_ID" => $arUserGroups,
						"GROUP_BUY" => "Y",
						"+<=QUANTITY_FROM" => $quantity,
						"+>=QUANTITY_TO" => $quantity
					),
				array("ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "ELEMENT_IBLOCK_ID")
			);
		}

		$iblockID = 0;
		$ind = -1;
		while (True)
		{
			if (count($arPrices) <= 0)
			{
				if (!($arPriceList = $dbPriceList->Fetch()))
					break;
			}
			else
			{
				$ind++;
				if ($ind >= count($arPrices))
					break;

				$arPriceList = $arPrices[$ind];
			}
			
			$arPriceList['VAT_RATE'] = $arVAT['RATE'];
			$arPriceList['VAT_INCLUDED'] = $arVAT['VAT_INCLUDED'];
			
			//SIGURD: logic change. see mantiss 5036.
			
			// if ($arPriceList['VAT_INCLUDED'] == 'Y')
			// {
				// $arPriceList['PRICE'] /= (1 + $arPriceList['VAT_RATE']);
				// $arPriceList['VAT_INCLUDED'] = 'N';
			// }
			
			// calc price WITH VAT included and use it for discount calculation and comparison
			if ($arPriceList['VAT_INCLUDED'] == 'N')
			{
				$arPriceList['PRICE'] *= (1 + $arPriceList['VAT_RATE']);
				$arPriceList['VAT_INCLUDED'] = 'Y';
			}
			
			//echo '<pre>arPriceList: '; print_r($arPriceList); echo '</pre>';

			if ($arPriceList["CURRENCY"] == $baseCurrency)
				$currentPrice = $arPriceList["PRICE"];
			else
				$currentPrice = CCurrencyRates::ConvertCurrency($arPriceList["PRICE"], $arPriceList["CURRENCY"], $baseCurrency);

			$currentPrice = roundEx($currentPrice, CATALOG_VALUE_PRECISION);

			$currentPrice_min = $currentPrice;
			$i_min = -1;

			if ($iblockID <= 0)
			{
				if (array_key_exists("ELEMENT_IBLOCK_ID", $arPriceList) && IntVal($arPriceList["ELEMENT_IBLOCK_ID"]) > 0)
					$iblockID = IntVal($arPriceList["ELEMENT_IBLOCK_ID"]);
			}

			if ($iblockID <= 0)
			{
				$dbElement = CIBlockElement::GetByID($productID);
				if (!($arElement = $dbElement->Fetch()))
				{
					$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $productID, "Element ##ID# is not found"), "NO_ELEMENT");
					return False;
				}
				$iblockID = IntVal($arElement["IBLOCK_ID"]);
			}

			$arDiscounts = CCatalogDiscount::GetDiscount($productID, $iblockID, $arPriceList["CATALOG_GROUP_ID"], $arUserGroups, $renewal, $siteID, $arDiscountCoupons);

			for ($i = 0; $i < count($arDiscounts); $i++)
			{
				$currentPrice_tmp = $currentPrice;

				if ($arDiscounts[$i]["VALUE_TYPE"] == "P")
				{
					$discount_tmp = $currentPrice_tmp * $arDiscounts[$i]["VALUE"] / 100.0;
					
					if (DoubleVal($arDiscounts[$i]["MAX_DISCOUNT"]) > 0)
					{
						if ($arDiscounts[$i]["CURRENCY"] == $baseCurrency)
							$maxDiscount = $arDiscounts[$i]["MAX_DISCOUNT"];
						else
							$maxDiscount = CCurrencyRates::ConvertCurrency($arDiscounts[$i]["MAX_DISCOUNT"], $arDiscounts[$i]["CURRENCY"], $baseCurrency);
						$maxDiscount = roundEx($maxDiscount, CATALOG_VALUE_PRECISION);
						
						if ($discount_tmp > $maxDiscount)
							$discount_tmp = $maxDiscount;
					}
				}
				else
				{
					if ($arDiscounts[$i]["CURRENCY"] == $baseCurrency)
						$discount_tmp = $arDiscounts[$i]["VALUE"];
					else
						$discount_tmp = CCurrencyRates::ConvertCurrency($arDiscounts[$i]["VALUE"], $arDiscounts[$i]["CURRENCY"], $baseCurrency);
					
					if ($arDiscounts[$i]['COUPON'] && $arDiscounts[$i]['COUPON_ONE_TIME'] == 'Y')
						$arDiscounts[$i]['VALUE'] /= $quantity;
				}
				$discount_tmp = roundEx($discount_tmp, CATALOG_VALUE_PRECISION);

				if ($currentPrice_tmp >= $discount_tmp) // equality is added for 100% discount possibility
					$currentPrice_tmp -= $discount_tmp;

				if ($currentPrice_tmp < $currentPrice_min)
				{
					$currentPrice_min = $currentPrice_tmp;
					$i_min = $i;
				}
			}

			if ($totalPrice_min < 0 || $currentPrice_min < $totalPrice_min)
			{
				$totalPrice_min = $currentPrice_min;
				$arPriceMin["PRICE"] = $arPriceList;
				$arPriceMin["DISCOUNT_PRICE"] = $currentPrice_min;
				$arPriceMin["DISCOUNT"] = array();
				if ($i_min >= 0)
					$arPriceMin["DISCOUNT"] = $arDiscounts[$i_min];
			}
		}
		
		//SIGURD: logic change. see mantiss 5036.
		// we must return price without VAT included. To be continued in catalog callbacks.
		if (is_array($arPriceMin) && $arPriceMin['VAT_INCLUDED'] == 'Y')
		{
			$arPriceMin['PRICE'] /= (1 + $arPriceMin['VAT_RATE']);
			$arPriceMin['VAT_INCLUDED'] = 'N';
		}

		return $arPriceMin;
	}


	function CountPriceWithDiscount($price, $currency, $arDiscounts)
	{
		if (strlen($currency) <= 0)
			return False;

		$price = DoubleVal($price);
		if ($price <= 0)
			return 0.0;

		if (!is_array($arDiscounts) || count($arDiscounts) <= 0)
			return $price;

		$currentPrice_min = $price;

		for ($i = 0; $i < count($arDiscounts); $i++)
		{
			$currentPrice_tmp = $price;

			if ($arDiscounts[$i]["VALUE_TYPE"] == "P")
			{
				$discount_tmp = $currentPrice_tmp * $arDiscounts[$i]["VALUE"] / 100.0;
			}
			else
			{
				if ($arDiscounts[$i]["CURRENCY"] == $currency)
					$discount_tmp = $arDiscounts[$i]["VALUE"];
				else
					$discount_tmp = CCurrencyRates::ConvertCurrency($arDiscounts[$i]["VALUE"], $arDiscounts[$i]["CURRENCY"], $currency);
			}
			$discount_tmp = roundEx($discount_tmp, CATALOG_VALUE_PRECISION);

			if (DoubleVal($arDiscounts[$i]["MAX_DISCOUNT"]) > 0)
			{
				if ($arDiscounts[$i]["CURRENCY"] == $currency)
					$maxDiscount = $arDiscounts[$i]["MAX_DISCOUNT"];
				else
					$maxDiscount = CCurrencyRates::ConvertCurrency($arDiscounts[$i]["MAX_DISCOUNT"], $arDiscounts[$i]["CURRENCY"], $currency);

				$maxDiscount = roundEx($maxDiscount, CATALOG_VALUE_PRECISION);

				if ($discount_tmp > $maxDiscount)
					$discount_tmp = $maxDiscount;
			}

			if ($currentPrice_tmp >= $discount_tmp)
				$currentPrice_tmp -= $discount_tmp;

			if ($currentPrice_tmp < $currentPrice_min)
			{
				$currentPrice_min = $currentPrice_tmp;
			}
		}

		return $currentPrice_min;
	}

	function GetProductSections($ID)
	{
		$ID = IntVal($ID);
		if ($ID <= 0)
			return false;

		$cacheTime = CATALOG_CACHE_DEFAULT_TIME;
		if (defined("CATALOG_CACHE_TIME"))
			$cacheTime = IntVal(CATALOG_CACHE_TIME);

		$arProductSections = array();

		$dbElementSections = CIBlockElement::GetElementGroups($ID);
		while ($arElementSections = $dbElementSections->Fetch())
		{
			$arSectionsTmp = array();

			$strCacheKey = "p".$arElementSections["ID"];

			$GLOBALS["stackCacheManager"]->SetLength("catalog_group_parents", 50);
			$GLOBALS["stackCacheManager"]->SetTTL("catalog_group_parents", $cacheTime);
			if ($GLOBALS["stackCacheManager"]->Exist("catalog_group_parents", $strCacheKey))
			{
				$arSectionsTmp = $GLOBALS["stackCacheManager"]->Get("catalog_group_parents", $strCacheKey);
			}
			else
			{
				$dbSection = CIBlockSection::GetByID($arElementSections["ID"]);
				if ($arSection = $dbSection->Fetch())
				{
					$dbSectionTree = CIBlockSection::GetList(
						array("LEFT_MARGIN" => "DESC"),
						array(
							"IBLOCK_ID" => $arSection["IBLOCK_ID"],
							"ACTIVE" => "Y",
							"GLOBAL_ACTIVE" => "Y",
							"IBLOCK_ACTIVE" => "Y",
							"<=LEFT_BORDER" => $arSection["LEFT_MARGIN"],
							">=RIGHT_BORDER" => $arSection["RIGHT_MARGIN"]
						)
					);
					while ($arSectionTree = $dbSectionTree->Fetch())
					{
						if (!in_array($arSectionTree["ID"], $arProductSections))
							$arSectionsTmp[] = $arSectionTree["ID"];
					}
				}

				$GLOBALS["stackCacheManager"]->Set("catalog_group_parents", $strCacheKey, $arSectionsTmp);
			}

			$arProductSections = array_merge($arProductSections, $arSectionsTmp);
		}

		$arProductSections = array_unique($arProductSections);

		return $arProductSections;
	}

	function OnIBlockElementDelete($ProductID)
	{
		global $DB;
		$ProductID = IntVal($ProductID);

		return CCatalogProduct::Delete($ProductID);
	}

	function OnAfterIBlockElementUpdate($arFields)
	{
		if (is_set($arFields, "IBLOCK_SECTION"))
			$GLOBALS["stackCacheManager"]->Clear("catalog_element_groups");
	}
}
?>