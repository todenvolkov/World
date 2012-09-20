<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/discount_coupon.php");

/***********************************************************************/
/***********  CCatalogDiscount  ****************************************/
/***********************************************************************/
class CCatalogDiscountCoupon extends CAllCatalogDiscountCoupon
{
	function Add($arFields, $bAffectDataFile = True)
	{
		global $DB;

		if (!CCatalogDiscountCoupon::CheckFields("ADD", $arFields, 0))
			return false;

		if ($bAffectDataFile)
		{
			$bNeedRegenerate = False;
			if (array_key_exists("DISCOUNT_ID", $arFields))
			{
				if (!CCatalogDiscount::HaveCoupons($arFields["DISCOUNT_ID"]))
				{
					CCatalogDiscount::ClearFile($arFields["DISCOUNT_ID"]);
					$bNeedRegenerate = True;
				}
			}
		}

		$arInsert = $DB->PrepareInsert("b_catalog_discount_coupon", $arFields);

		$strSql =
			"INSERT INTO b_catalog_discount_coupon(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = IntVal($DB->LastID());

		if ($bAffectDataFile && $bNeedRegenerate)
			CCatalogDiscount::GenerateDataFile($arFields["DISCOUNT_ID"]);

		return $ID;
	}

	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		// FIELDS -->
		$arFields = array(
			"ID" => array("FIELD" => "CD.ID", "TYPE" => "int"),
			"DISCOUNT_ID" => array("FIELD" => "CD.DISCOUNT_ID", "TYPE" => "string"),
			"ACTIVE" => array("FIELD" => "CD.ACTIVE", "TYPE" => "char"),
			"ONE_TIME" => array("FIELD" => "CD.ONE_TIME", "TYPE" => "char"),
			"COUPON" => array("FIELD" => "CD.COUPON", "TYPE" => "string"),
			"DATE_APPLY" => array("FIELD" => "CD.DATE_APPLY", "TYPE" => "datetime"),
			"DISCOUNT_NAME" => array("FIELD" => "CDD.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_catalog_discount CDD ON (CD.DISCOUNT_ID = CDD.ID)"),
		);
		// <-- FIELDS

		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);//DISTINCT

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_catalog_discount_coupon CD ".
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
			"FROM b_catalog_discount_coupon CD ".
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
				"FROM b_catalog_discount_coupon CD ".
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