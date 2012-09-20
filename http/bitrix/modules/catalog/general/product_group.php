<?
IncludeModuleLangFile(__FILE__);

/***********************************************************************/
/***********  CCatalogProductGroups  ***********************************/
/***********************************************************************/
class CAllCatalogProductGroups
{
	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "PRODUCT_ID") || $ACTION=="ADD") && IntVal($arFields["PRODUCT_ID"]) <= 0)
			return false;

		if ((is_set($arFields, "GROUP_ID") || $ACTION=="ADD") && IntVal($arFields["GROUP_ID"]) <= 0)
			return false;

		if ((is_set($arFields, "ACCESS_LENGTH") || $ACTION=="ADD"))
		{
			$arFields["ACCESS_LENGTH"] = IntVal($arFields["ACCESS_LENGTH"]);
			if ($arFields["ACCESS_LENGTH"] < 0)
				$arFields["ACCESS_LENGTH"] = 0;
		}

		if ((is_set($arFields, "ACCESS_LENGTH_TYPE") || $ACTION=="ADD") && !array_key_exists($arFields["ACCESS_LENGTH_TYPE"], $GLOBALS["CATALOG_TIME_PERIOD_TYPES"]))
		{
			$arTypeKeys = array_keys($GLOBALS["CATALOG_TIME_PERIOD_TYPES"]);
			$arFields["ACCESS_LENGTH_TYPE"] = $arRecurSchemeKeys[1];
		}

		return True;
	}

	function GetByID($ID)
	{
		global $DB;
		$ID = IntVal($ID);

		$strSql = 
			"SELECT CPG.ID, CPG.PRODUCT_ID, CPG.GROUP_ID, CPG.ACCESS_LENGTH, CPG.ACCESS_LENGTH_TYPE ".
			"FROM b_catalog_product2group CPG ".
			"WHERE CPG.ID = ".$ID." ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($res = $db_res->Fetch())
			return $res;

		return false;
	}

	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "CPG.ID", "TYPE" => "int"),
				"PRODUCT_ID" => array("FIELD" => "CPG.PRODUCT_ID", "TYPE" => "int"),
				"GROUP_ID" => array("FIELD" => "CPG.GROUP_ID", "TYPE" => "int"),
				"ACCESS_LENGTH" => array("FIELD" => "CPG.ACCESS_LENGTH", "TYPE" => "int"),
				"ACCESS_LENGTH_TYPE" => array("FIELD" => "CPG.ACCESS_LENGTH_TYPE", "TYPE" => "char"),
				"GROUP_ACTIVE" => array("FIELD" => "G.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN b_group G ON (CPG.GROUP_ID = G.ID)"),
				"GROUP_NAME" => array("FIELD" => "G.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_group G ON (CPG.GROUP_ID = G.ID)")
			);
		// <-- FIELDS

		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_catalog_product2group CPG ".
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
			"FROM b_catalog_product2group CPG ".
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
				"FROM b_catalog_product2group CPG ".
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

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return false;

		if (!CCatalogProductGroups::CheckFields("UPDATE", $arFields, $ID))
			return False;

		$strUpdate = $DB->PrepareUpdate("b_catalog_product2group", $arFields);
		$strUpdate = Trim($strUpdate);
		if (StrLen($strUpdate) > 0)
		{
			$strSql = "UPDATE b_catalog_product2group SET ".$strUpdate." WHERE ID = ".$ID." ";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $ID;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return false;

		return $DB->Query("DELETE FROM b_catalog_product2group WHERE ID = ".$ID." ", True);
	}

	function DeleteByGroup($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return false;

		return $DB->Query("DELETE FROM b_catalog_product2group WHERE GROUP_ID = ".$ID." ", True);
	}

	function OnGroupDelete($ID)
	{
		CCatalogProductGroups::DeleteByGroup($ID);
	}
}
?>