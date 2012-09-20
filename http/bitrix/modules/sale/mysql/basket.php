<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/basket.php");

class CSaleBasket extends CAllSaleBasket
{
	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB, $USER;

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

			if (strtoupper($arFilter["ORDER_ID"]) == "NULL")
			{
				$arFilter["ORDER_ID"] = 0;
			}
		}

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "B.ID", "TYPE" => "int"),
				"FUSER_ID" => array("FIELD" => "B.FUSER_ID", "TYPE" => "int"),
				"ORDER_ID" => array("FIELD" => "B.ORDER_ID", "TYPE" => "int"),
				"PRODUCT_ID" => array("FIELD" => "B.PRODUCT_ID", "TYPE" => "int"),
				"PRODUCT_PRICE_ID" => array("FIELD" => "B.PRODUCT_PRICE_ID", "TYPE" => "int"),
				"PRICE" => array("FIELD" => "B.PRICE", "TYPE" => "double"),
				"CURRENCY" => array("FIELD" => "B.CURRENCY", "TYPE" => "string"),
				"DATE_INSERT" => array("FIELD" => "B.DATE_INSERT", "TYPE" => "datetime"),
				"DATE_UPDATE" => array("FIELD" => "B.DATE_UPDATE", "TYPE" => "datetime"),
				"WEIGHT" => array("FIELD" => "B.WEIGHT", "TYPE" => "double"),
				"QUANTITY" => array("FIELD" => "B.QUANTITY", "TYPE" => "double"),
				"LID" => array("FIELD" => "B.LID", "TYPE" => "string"),
				"DELAY" => array("FIELD" => "B.DELAY", "TYPE" => "char"),
				"NAME" => array("FIELD" => "B.NAME", "TYPE" => "string"),
				"CAN_BUY" => array("FIELD" => "B.CAN_BUY", "TYPE" => "char"),
				"MODULE" => array("FIELD" => "B.MODULE", "TYPE" => "string"),
				"CALLBACK_FUNC" => array("FIELD" => "B.CALLBACK_FUNC", "TYPE" => "string"),
				"NOTES" => array("FIELD" => "B.NOTES", "TYPE" => "string"),
				"ORDER_CALLBACK_FUNC" => array("FIELD" => "B.ORDER_CALLBACK_FUNC", "TYPE" => "string"),
				"PAY_CALLBACK_FUNC" => array("FIELD" => "B.PAY_CALLBACK_FUNC", "TYPE" => "string"),
				"CANCEL_CALLBACK_FUNC" => array("FIELD" => "B.CANCEL_CALLBACK_FUNC", "TYPE" => "string"),
				"DETAIL_PAGE_URL" => array("FIELD" => "B.DETAIL_PAGE_URL", "TYPE" => "string"),
				"DISCOUNT_PRICE" => array("FIELD" => "B.DISCOUNT_PRICE", "TYPE" => "double"),
				"CATALOG_XML_ID" => array("FIELD" => "B.CATALOG_XML_ID", "TYPE" => "string"),
				"PRODUCT_XML_ID" => array("FIELD" => "B.PRODUCT_XML_ID", "TYPE" => "string"),
				"DISCOUNT_NAME" => array("FIELD" => "B.DISCOUNT_NAME", "TYPE" => "string"),
				"DISCOUNT_VALUE" => array("FIELD" => "B.DISCOUNT_VALUE", "TYPE" => "string"),
				"DISCOUNT_COUPON" => array("FIELD" => "B.DISCOUNT_COUPON", "TYPE" => "string"),
				"VAT_RATE" => array("FIELD" => "B.VAT_RATE", "TYPE" => "double"),
				
				"ORDER_ALLOW_DELIVERY" => array("FIELD" => "O.ALLOW_DELIVERY", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order O ON (O.ID = B.ORDER_ID)"),
				"ORDER_PAYED" => array("FIELD" => "OP.PAYED", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order OP ON (OP.ID = B.ORDER_ID)"),
				"ORDER_PRICE" => array("FIELD" => "OPR.PRICE", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_order OPR ON (OPR.ID = B.ORDER_ID)"),
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_basket B ".
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
			"FROM b_sale_basket B ".
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
				"FROM b_sale_basket B ".
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

	function GetPropsList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

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
				"ID" => array("FIELD" => "BP.ID", "TYPE" => "int"),
				"BASKET_ID" => array("FIELD" => "BP.BASKET_ID", "TYPE" => "int"),
				"NAME" => array("FIELD" => "BP.NAME", "TYPE" => "string"),
				"VALUE" => array("FIELD" => "BP.VALUE", "TYPE" => "string"),
				"CODE" => array("FIELD" => "BP.CODE", "TYPE" => "string"),
				"SORT" => array("FIELD" => "BP.SORT", "TYPE" => "int")
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_basket_props BP ".
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
			"FROM b_sale_basket_props BP ".
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
				"FROM b_sale_basket_props BP ".
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

	//************** ADD, UPDATE, DELETE ********************//
	function Add($arFields)
	{
		global $DB;
		
		CSaleBasket::Init();
		if (!CSaleBasket::CheckFields("ADD", $arFields))
			return false;

		$db_events = GetModuleEvents("sale", "OnBeforeBasketAdd");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEvent($arEvent, $arFields)===false)
				return false;
		
		$bFound = false;
		$bEqAr = false;
		$db_res = CSaleBasket::GetList(
				array("LID" => "ASC"),
				array(
						"FUSER_ID" => $arFields["FUSER_ID"],
						"PRODUCT_ID" => $arFields["PRODUCT_ID"],
						"LID" => $arFields["LID"],
						"ORDER_ID" => "NULL"
					),
				false,
				false,
				array("ID", "QUANTITY")
			);
		while($res = $db_res->Fetch())
		{
			if(!$bEqAr)
			{
				$arPropsCur = Array();
				$arPropsOld = Array();
				
				$dbProp = CSaleBasket::GetPropsList(Array("ID" => "DESC"), Array("BASKET_ID" => $ID));
				foreach($arFields["PROPS"] as $arProp)
				{
					if(strlen($arProp["VALUE"]) > 0)
					{
						if(strlen($arProp["CODE"]) > 0)
							$propID = $arProp["CODE"];
						else
							$propID = $arProp["NAME"];
						$arPropsCur[$propID] = $arProp["VALUE"];
					}
				}
				
				$dbProp = CSaleBasket::GetPropsList(Array("ID" => "DESC"), Array("BASKET_ID" => $res["ID"]));
				while($arProp = $dbProp->Fetch())
				{
					if(strlen($arProp["VALUE"]) > 0)
					{
						if(strlen($arProp["CODE"]) > 0)
							$propID = $arProp["CODE"];
						else
							$propID = $arProp["NAME"];
						$arPropsOld[$propID] = $arProp["VALUE"];
					}
				}			
				
				$bEqAr = false;
				if(count($arPropsCur) == count($arPropsOld))
				{
					$bEqAr = true;
					foreach($arPropsCur as $key => $val)
					{
						if($bEqAr && (strlen($arPropsOld[$key]) <= 0 || $arPropsOld[$key] != $val))
							$bEqAr = false;
					}
				}
				
				
				if($bEqAr)
				{
					$ID = $res["ID"];
					$arFields["QUANTITY"] += $res["QUANTITY"];
					CSaleBasket::Update($ID, $arFields);
					$bFound = true;
					continue;
				}
			}
		}
		
		if(!$bFound)
		{
			$arInsert = $DB->PrepareInsert("b_sale_basket", $arFields);

			$strSql =
				"INSERT INTO b_sale_basket(".$arInsert[0].", DATE_INSERT, DATE_UPDATE) ".
				"VALUES(".$arInsert[1].", ".$DB->GetNowFunction().", ".$DB->GetNowFunction().")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = IntVal($DB->LastID());
			$_SESSION["SALE_BASKET_NUM_PRODUCTS"][SITE_ID]++;

			if (is_array($arFields["PROPS"]) && count($arFields["PROPS"])>0)
			{
				for ($i = 0; $i<count($arFields["PROPS"]); $i++)
				{
					if(strlen($arFields["PROPS"][$i]["NAME"]) > 0)
					{
						$arInsert = $DB->PrepareInsert("b_sale_basket_props", $arFields["PROPS"][$i]);

						$strSql =
							"INSERT INTO b_sale_basket_props(BASKET_ID, ".$arInsert[0].") ".
							"VALUES(".$ID.", ".$arInsert[1].")";
						$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
					}
				}
			}
		}
		
		$events = GetModuleEvents("sale", "OnBasketAdd");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, $ID, $arFields);

		return $ID;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return False;

		$db_events = GetModuleEvents("sale", "OnBeforeBasketDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEvent($arEvent, $ID)===false)
				return false;
				
		$events = GetModuleEvents("sale", "OnBasketDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, $ID);


		$DB->Query("DELETE FROM b_sale_basket_props WHERE BASKET_ID = ".$ID." ", true);
		if(IntVal($_SESSION["SALE_BASKET_NUM_PRODUCTS"][SITE_ID]) > 0 )
			$_SESSION["SALE_BASKET_NUM_PRODUCTS"][SITE_ID]--;
		return $DB->Query("DELETE FROM b_sale_basket WHERE ID = ".$ID." ", true);
	}

	function DeleteAll($FUSER_ID = 0, $bIncOrdered = false)
	{
		global $DB, $APPLICATION;

		$bIncOrdered = ($bIncOrdered ? True : False);
		$FUSER_ID = IntVal($FUSER_ID);
		if ($FUSER_ID <= 0)
			return false;

		$arFilter = Array("FUSER_ID" => $FUSER_ID);
		if (!$bIncOrdered)
			$arFilter["ORDER_ID"] = "NULL";

		$dbBasket = CSaleBasket::GetList(array("NAME" => "ASC"), $arFilter);
		while ($arBasket = $dbBasket->Fetch())
		{
			$DB->Query("DELETE FROM b_sale_basket_props WHERE BASKET_ID = ".$arBasket["ID"]." ", true);
			$DB->Query("DELETE FROM b_sale_basket WHERE ID = ".$arBasket["ID"]." ", true);
		}
		
		$_SESSION["SALE_BASKET_NUM_PRODUCTS"][SITE_ID] = 0;

		return true;
	}
/*
	function TransferBasket($FROM_FUSER_ID, $TO_FUSER_ID)
	{
		global $DB;

		$FROM_FUSER_ID = IntVal($FROM_FUSER_ID);
		$TO_FUSER_ID = IntVal($TO_FUSER_ID);

		if (($TO_FUSER_ID>0) && (CSaleUser::GetList(array("ID"=>$TO_FUSER_ID))))
		{
			$strSql =
				"UPDATE b_sale_basket SET ".
				"	FUSER_ID = ".$TO_FUSER_ID." ".
				"WHERE FUSER_ID = ".$FROM_FUSER_ID." ";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			return true;
		}
		return false;
	}
*/
}

class CSaleUser extends CAllSaleUser
{
	function Add()
	{
		global $DB, $USER;

		$arFields = array(
				"=DATE_INSERT" => $DB->GetNowFunction(),
				"=DATE_UPDATE" => $DB->GetNowFunction(),
				"USER_ID" => ($USER->IsAuthorized() ? IntVal($USER->GetID()) : False)
			);

		$ID = CSaleUser::_Add($arFields);
		$ID = IntVal($ID);

		$secure = false;
		if(COption::GetOptionString("sale", "use_secure_cookies", "N") == "Y" && CMain::IsHTTPS())
			$secure=1;
		$GLOBALS["APPLICATION"]->set_cookie("SALE_UID", $ID, false, "/", false, $secure, "Y", false);

		return $ID;
	}

	function _Add($arFields)
	{
		global $DB;

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1)=="=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleUser::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_fuser", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($arInsert[0])>0) $arInsert[0] .= ", ";
			$arInsert[0] .= $key;
			if (strlen($arInsert[1])>0) $arInsert[1] .= ", ";
			$arInsert[1] .= $value;
		}

		$strSql =
			"INSERT INTO b_sale_fuser(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = IntVal($DB->LastID());

		return $ID;
	}


	function DeleteOld($nDays)
	{
		global $DB;

		$nDays = IntVal($nDays);
		$strSql =
			"SELECT ID ".
			"FROM b_sale_fuser ".
			"WHERE TO_DAYS(DATE_UPDATE)<(TO_DAYS(NOW())-".$nDays.") LIMIT 300";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($ar_res = $db_res->Fetch())
		{
			CSaleBasket::DeleteAll($ar_res["ID"], false);
			CSaleUser::Delete($ar_res["ID"]);
		}
		return true;
	}

}
?>