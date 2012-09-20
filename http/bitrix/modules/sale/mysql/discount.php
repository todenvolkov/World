<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/discount.php");

class CSaleDiscount extends CAllSaleDiscount
{
	function Add($arFields)
	{
		global $DB;

		if (!CSaleDiscount::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_discount", $arFields);
		$strSql =
			"INSERT INTO b_sale_discount(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = IntVal($DB->LastID());

		return $ID;
	}

	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		// Для старой формы вызова
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
			if (array_key_exists("PRICE", $arFilter))
			{
				$valTmp = $arFilter["PRICE"];
				unset($arFilter["PRICE"]);
				$arFilter["<=PRICE_FROM"] = $valTmp;
				$arFilter[">=PRICE_TO"] = $valTmp;
			}
			$arGroupBy = false;
		}

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "D.ID", "TYPE" => "int"),
				"LID" => array("FIELD" => "D.LID", "TYPE" => "string"),
				"SITE_ID" => array("FIELD" => "D.LID", "TYPE" => "string"),
				"PRICE_FROM" => array("FIELD" => "D.PRICE_FROM", "TYPE" => "double", "WHERE" => array("CSaleDiscount", "PrepareCurrency4Where")),
				"PRICE_TO" => array("FIELD" => "D.PRICE_TO", "TYPE" => "double", "WHERE" => array("CSaleDiscount", "PrepareCurrency4Where")),
				"CURRENCY" => array("FIELD" => "D.CURRENCY", "TYPE" => "string"),
				"DISCOUNT_VALUE" => array("FIELD" => "D.DISCOUNT_VALUE", "TYPE" => "double"),
				"DISCOUNT_TYPE" => array("FIELD" => "D.DISCOUNT_TYPE", "TYPE" => "char"),
				"ACTIVE" => array("FIELD" => "D.ACTIVE", "TYPE" => "char"),
				"SORT" => array("FIELD" => "D.SORT", "TYPE" => "int"),
				"ACTIVE_FROM" => array("FIELD" => "D.ACTIVE_FROM", "TYPE" => "datetime"),
				"ACTIVE_TO" => array("FIELD" => "D.ACTIVE_TO", "TYPE" => "datetime"),
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_discount D ".
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
			"FROM b_sale_discount D ".
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
				"FROM b_sale_discount D ".
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
				// ТОЛЬКО ДЛЯ MYSQL!!! ДЛЯ ORACLE ДРУГОЙ КОД
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
}
?>