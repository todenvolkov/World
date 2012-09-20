<?
IncludeModuleLangFile(__FILE__);

/***********************************************************************/
/***********  CCatalogDiscountCoupon  **********************************/
/***********************************************************************/
class CAllCatalogDiscountCoupon
{
	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return false;

		$strSql = 
			"SELECT CD.ID, CD.DISCOUNT_ID, CD.ACTIVE, CD.COUPON, CD.ONE_TIME, ".
			"	".$DB->DateToCharFunction("CD.DATE_APPLY", "FULL")." as DATE_APPLY ".
			"FROM b_catalog_discount_coupon CD ".
			"WHERE CD.ID = ".$ID." ";

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($res = $db_res->Fetch())
			return $res;

		return false;
	}

	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "DISCOUNT_ID") || $ACTION=="ADD") && IntVal($arFields["DISCOUNT_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGDC_EMPTY_DISCOUNT"), "EMPTY_DISCOUNT_ID");
			return false;
		}
	
/*
		if ((is_set($arFields, "COUPON") || $ACTION=="ADD") && StrLen($arFields["COUPON"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGDC_EMPTY_COUPON"), "EMPTY_COUPON");
			return false;
		}
*/


		if ((is_set($arFields, "COUPON") || $ACTION=="ADD") && StrLen($arFields["COUPON"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGDC_EMPTY_COUPON"), "EMPTY_COUPON");
			return false;
		}
		elseif(is_set($arFields, "COUPON"))
		{
			$arFilter = Array("COUPON" => substr($arFields["COUPON"], 0, 32));
			if ($ID > 0)
				$arFilter["!ID"] = $ID;

			$rsCoupon = CCatalogDiscountCoupon::GetList(Array(),$arFilter);

			if ($arCoupon = $rsCoupon->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGDC_DUPLICATE_COUPON"), "DUPLICATE_COUPON");
				return false;
			}
		}



		if ((is_set($arFields, "ACTIVE") || $ACTION=="ADD") && $arFields["ACTIVE"] != "N")
			$arFields["ACTIVE"] = "Y";
		if ((is_set($arFields, "ONE_TIME") || $ACTION=="ADD") && $arFields["ONE_TIME"] != "N")
			$arFields["ONE_TIME"] = "Y";
		
		if ((is_set($arFields, "DATE_APPLY") || $ACTION=="ADD") && (!$GLOBALS["DB"]->IsDate($arFields["DATE_APPLY"], false, LANG, "FULL")))
			$arFields["DATE_APPLY"] = false;

		return True;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return False;

		if (!CCatalogDiscountCoupon::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_catalog_discount_coupon", $arFields);
		$strSql = "UPDATE b_catalog_discount_coupon SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $ID;
	}


	function Delete($ID, $bAffectDataFile = True)
	{
		global $DB;
		$ID = IntVal($ID);
		if ($ID <= 0)
			return False;

		if ($bAffectDataFile)
		{
			$arCoupon = CCatalogDiscountCoupon::GetByID($ID);
			$bHaveCoupons = True;
			if (!CCatalogDiscount::HaveCoupons($arCoupon["DISCOUNT_ID"], $ID))
			{
				$bHaveCoupons = False;
				CCatalogDiscount::ClearFile($arCoupon["DISCOUNT_ID"]);
			}
		}

		$DB->Query("DELETE FROM b_catalog_discount_coupon WHERE ID = ".$ID." ");

		if ($bAffectDataFile && !$bHaveCoupons)
			CCatalogDiscount::GenerateDataFile($arCoupon["DISCOUNT_ID"]);

		return True;
	}

	function DeleteByDiscountID($ID, $bAffectDataFile = True)
	{
		global $DB;
		$ID = IntVal($ID);
		if ($ID <= 0)
			return False;

		if ($bAffectDataFile)
			CCatalogDiscount::ClearFile($ID);

		$DB->Query("DELETE FROM b_catalog_discount_coupon WHERE DISCOUNT_ID = ".$ID." ", true);

		if ($bAffectDataFile)
			CCatalogDiscount::GenerateDataFile($ID);

		return True;
	}
}
?>