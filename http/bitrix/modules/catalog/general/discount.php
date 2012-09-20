<?
IncludeModuleLangFile(__FILE__);

/***********************************************************************/
/***********  CCatalogDiscount  ****************************************/
/***********************************************************************/
define("CATALOG_DISCOUNT_FILE", "/bitrix/modules/catalog/discount_data.php");
define("CATALOG_DISCOUNT_CPN_FILE", "/bitrix/modules/catalog/discount_cpn_data.php");

class CAllCatalogDiscount
{
	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "SITE_ID") || $ACTION=="ADD") && strlen($arFields["SITE_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGD_EMPTY_SITE"), "EMPTY_SITE");
			return false;
		}
		
		if ((is_set($arFields, "CURRENCY") || $ACTION=="ADD") && strlen($arFields["CURRENCY"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGD_EMPTY_CURRENCY"), "EMPTY_CURRENCY");
			return false;
		}

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && strlen($arFields["NAME"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("KGD_EMPTY_NAME"), "EMPTY_NAME");
			return false;
		}

		if ((is_set($arFields, "ACTIVE") || $ACTION=="ADD") && $arFields["ACTIVE"] != "N")
			$arFields["ACTIVE"] = "Y";
		if ((is_set($arFields, "ACTIVE_FROM") || $ACTION=="ADD") && (!$GLOBALS["DB"]->IsDate($arFields["ACTIVE_FROM"], false, LANG, "FULL")))
			$arFields["ACTIVE_FROM"] = false;
		if ((is_set($arFields, "ACTIVE_TO") || $ACTION=="ADD") && (!$GLOBALS["DB"]->IsDate($arFields["ACTIVE_TO"], false, LANG, "FULL")))
			$arFields["ACTIVE_TO"] = false;

		if ((is_set($arFields, "RENEWAL") || $ACTION=="ADD") && $arFields["RENEWAL"] != "Y")
			$arFields["RENEWAL"] = "N";

		if ((is_set($arFields, "MAX_USES") || $ACTION=="ADD") && IntVal($arFields["MAX_USES"]) <= 0)
			$arFields["MAX_USES"] = 0;
		if ((is_set($arFields, "COUNT_USES") || $ACTION=="ADD") && IntVal($arFields["COUNT_USES"]) <= 0)
			$arFields["COUNT_USES"] = 0;

		// if ((is_set($arFields, 'COUPON')))
			// $arFields['CATALOG_COUPONS'] = $arFields['COUPON'];
		
		if ((is_set($arFields, "CATALOG_COUPONS") || $ACTION=="ADD") && !is_array($arFields['CATALOG_COUPONS']) && strlen($arFields["CATALOG_COUPONS"]) <= 0)
			$arFields["CATALOG_COUPONS"] = false;

		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && IntVal($arFields["SORT"]) <= 0)
			$arFields["SORT"] = 100;

		if (is_set($arFields, "MAX_DISCOUNT") || $ACTION=="ADD")
		{
			$arFields["MAX_DISCOUNT"] = str_replace(",", ".", $arFields["MAX_DISCOUNT"]);
			$arFields["MAX_DISCOUNT"] = DoubleVal($arFields["MAX_DISCOUNT"]);
		}

		if ((is_set($arFields, "VALUE_TYPE") || $ACTION=="ADD") && $arFields["VALUE_TYPE"] != "F")
			$arFields["VALUE_TYPE"] = "P";

		if (is_set($arFields, "VALUE") || $ACTION=="ADD")
		{
			$arFields["VALUE"] = str_replace(",", ".", $arFields["VALUE"]);
			$arFields["VALUE"] = DoubleVal($arFields["VALUE"]);
		}

		if (is_set($arFields, "MIN_ORDER_SUM") || $ACTION=="ADD")
		{
			$arFields["MIN_ORDER_SUM"] = str_replace(",", ".", $arFields["MIN_ORDER_SUM"]);
			$arFields["MIN_ORDER_SUM"] = DoubleVal($arFields["MIN_ORDER_SUM"]);
		}
		
		return True;
	}

	function _Update($ID, $arFields)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return False;

		if (!CCatalogDiscount::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$GLOBALS["stackCacheManager"]->Clear("catalog_discount");
		
		$strUpdate = $DB->PrepareUpdate("b_catalog_discount", $arFields);
		if (strlen($strUpdate) > 0)
		{
			$strSql = "UPDATE b_catalog_discount SET ".$strUpdate." WHERE ID = ".$ID." ";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return False;

		if (!CCatalogDiscount::_Update($ID, $arFields))
			return False;

		CCatalogDiscount::ClearFile($ID);

		if (is_set($arFields, "PRODUCT_IDS"))
		{
			$DB->Query("DELETE FROM b_catalog_discount2product WHERE DISCOUNT_ID = ".$ID." ", false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if (!is_array($arFields["PRODUCT_IDS"]))
				$arFields["PRODUCT_IDS"] = array($arFields["PRODUCT_IDS"]);
			else
				$arFields['PRODUCT_IDS'] = array_unique($arFields['PRODUCT_IDS']);

			for ($i = 0; $i < count($arFields["PRODUCT_IDS"]); $i++)
			{
				if (IntVal($arFields["PRODUCT_IDS"][$i]) > 0)
				{
					$strSql =
						"INSERT INTO b_catalog_discount2product(DISCOUNT_ID, PRODUCT_ID) ".
						"VALUES(".$ID.", ".IntVal($arFields["PRODUCT_IDS"][$i]).")";
					$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
			}
		}

		if (is_set($arFields, "SECTION_IDS"))
		{
			$DB->Query("DELETE FROM b_catalog_discount2section WHERE DISCOUNT_ID = ".$ID." ", false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if (!is_array($arFields["SECTION_IDS"]))
				$arFields["SECTION_IDS"] = array($arFields["SECTION_IDS"]);

			for ($i = 0; $i < count($arFields["SECTION_IDS"]); $i++)
			{
				if (IntVal($arFields["SECTION_IDS"][$i]) > 0)
				{
					$strSql =
						"INSERT INTO b_catalog_discount2section(DISCOUNT_ID, SECTION_ID) ".
						"VALUES(".$ID.", ".IntVal($arFields["SECTION_IDS"][$i]).")";
					$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
			}
		}

		if (is_set($arFields, "GROUP_IDS"))
		{
			$DB->Query("DELETE FROM b_catalog_discount2group WHERE DISCOUNT_ID = ".$ID." ", false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if (!is_array($arFields["GROUP_IDS"]))
				$arFields["GROUP_IDS"] = array($arFields["GROUP_IDS"]);

			for ($i = 0; $i < count($arFields["GROUP_IDS"]); $i++)
			{
				if (IntVal($arFields["GROUP_IDS"][$i]) > 0)
				{
					$strSql =
						"INSERT INTO b_catalog_discount2group(DISCOUNT_ID, GROUP_ID) ".
						"VALUES(".$ID.", ".IntVal($arFields["GROUP_IDS"][$i]).")";
					$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
			}
		}

		if (is_set($arFields, "CATALOG_GROUP_IDS"))
		{
			$DB->Query("DELETE FROM b_catalog_discount2cat WHERE DISCOUNT_ID = ".$ID." ");

			if (!is_array($arFields["CATALOG_GROUP_IDS"]))
				$arFields["CATALOG_GROUP_IDS"] = array($arFields["CATALOG_GROUP_IDS"]);

			for ($i = 0; $i < count($arFields["CATALOG_GROUP_IDS"]); $i++)
			{
				if (IntVal($arFields["CATALOG_GROUP_IDS"][$i]) > 0)
				{
					$strSql =
						"INSERT INTO b_catalog_discount2cat(DISCOUNT_ID, CATALOG_GROUP_ID) ".
						"VALUES(".$ID.", ".IntVal($arFields["CATALOG_GROUP_IDS"][$i]).")";
					$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
			}
		}

		if (is_set($arFields, "CATALOG_COUPONS"))
		{
			CCatalogDiscountCoupon::DeleteByDiscountID($ID, False);

			if (!is_array($arFields["CATALOG_COUPONS"]))
				$arFields["CATALOG_COUPONS"] = array("ACTIVE" => "Y", "COUPON" => $arFields["CATALOG_COUPONS"], "DATE_APPLY" => false);

			$arKeys = array_keys($arFields["CATALOG_COUPONS"]);
			if (!is_array($arFields["CATALOG_COUPONS"][$arKeys[0]]))
				$arFields["CATALOG_COUPONS"] = array($arFields["CATALOG_COUPONS"]);

			for ($i = 0; $i < count($arFields["CATALOG_COUPONS"]); $i++)
			{
				if (strlen($arFields["CATALOG_COUPONS"][$i]["COUPON"]) > 0)
					CCatalogDiscountCoupon::Add($arFields["CATALOG_COUPONS"][$i], False);
			}
		}

		CCatalogDiscount::GenerateDataFile($ID);
		CCatalogDiscount::SaveFilterOptions();

		return $ID;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);

		$GLOBALS["stackCacheManager"]->Clear("catalog_discount");

		CCatalogDiscount::ClearFile($ID);

		$DB->Query("DELETE FROM b_catalog_discount2product WHERE DISCOUNT_ID = ".$ID." ");
		$DB->Query("DELETE FROM b_catalog_discount2section WHERE DISCOUNT_ID = ".$ID." ");
		$DB->Query("DELETE FROM b_catalog_discount2group WHERE DISCOUNT_ID = ".$ID." ");
		$DB->Query("DELETE FROM b_catalog_discount2cat WHERE DISCOUNT_ID = ".$ID." ");
		$DB->Query("DELETE FROM b_catalog_discount_coupon WHERE DISCOUNT_ID = ".$ID." ");

		$DB->Query("DELETE FROM b_catalog_discount WHERE ID = ".$ID." ");

		CCatalogDiscount::SaveFilterOptions();

		return True;
	}

	function SetCoupon($coupon)
	{
		$coupon = Trim($coupon);
		if (strlen($coupon) <= 0)
			return False;

		if (!isset($_SESSION["CATALOG_USER_COUPONS"]) || !is_array($_SESSION["CATALOG_USER_COUPONS"]))
			$_SESSION["CATALOG_USER_COUPONS"] = array();

		$dbCoupon = CCatalogDiscountCoupon::GetList(
			array(),
			array("COUPON" => $coupon, "ACTIVE" => "Y"),
			false,
			false,
			array("ID", "ONE_TIME")
		);
		if ($arCoupon = $dbCoupon->Fetch())
		{
			if (!in_array($coupon, $_SESSION["CATALOG_USER_COUPONS"]))
				$_SESSION["CATALOG_USER_COUPONS"][] = $coupon;
			/*
			if ($arCoupon["ONE_TIME"] == "Y")
			{
				CCatalogDiscountCoupon::Update(
					$arCoupon["ID"],
					array(
						"ACTIVE" => "N",
						"DATE_APPLY" => Date($GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)))
					)
				);
			}
			*/
			return True;
		}

		return False;
	}

	function GetCoupons()
	{
		if (!isset($_SESSION["CATALOG_USER_COUPONS"]) || !is_array($_SESSION["CATALOG_USER_COUPONS"]))
			$_SESSION["CATALOG_USER_COUPONS"] = array();

		return $_SESSION["CATALOG_USER_COUPONS"];
	}

	function ClearCoupon()
	{
		$_SESSION["CATALOG_USER_COUPONS"] = array();
	}

	function OnCurrencyDelete($Currency)
	{
		global $DB;
		if (strlen($Currency)<=0) return false;

		$dbDiscounts = CCatalogDiscount::GetList(array(), array("CURRENCY" => $Currency), false, false, array("ID"));
		while ($arDiscounts = $dbDiscounts->Fetch())
		{
			CCatalogDiscount::Delete($arDiscounts["ID"]);
		}

		return True;
	}

	function OnGroupDelete($GroupID)
	{
		global $DB;
		$GroupID = IntVal($GroupID);

		return $DB->Query("DELETE FROM b_catalog_discount2group WHERE GROUP_ID = ".$GroupID." ", true);
	}

	function GenerateDataFile($ID)
	{
		$ID = IntVal($ID);
		if ($ID <= 0)
			return false;

		$strDataFileName = CATALOG_DISCOUNT_FILE;
		$dbCoupons = CCatalogDiscountCoupon::GetList(array(), array("DISCOUNT_ID" => $ID), false, array("nTopCount" => 1), array("ID"));
		if ($dbCoupons->Fetch())
			$strDataFileName = CATALOG_DISCOUNT_CPN_FILE;

		$arDiscountSections = array();
		$arDiscountPriceTypes = array();
		$arDiscountUserGroups = array();
		$arDiscountProducts = array();

		if (file_exists($_SERVER["DOCUMENT_ROOT"].$strDataFileName) && is_file($_SERVER["DOCUMENT_ROOT"].$strDataFileName))
			include($_SERVER["DOCUMENT_ROOT"].$strDataFileName);

		if (count($arDiscountSections) > 0)
		{
			foreach ($arDiscountSections as $key => $value)
			{
				$key1 = array_search($ID, $value); 
				if ($key1 !== false)
				{
					unset($arDiscountSections[$key][$key1]);
					if (count($arDiscountSections[$key]) <= 0)
						unset($arDiscountSections[$key]);
				}
			}
		}

		if (count($arDiscountPriceTypes) > 0)
		{
			foreach ($arDiscountPriceTypes as $key => $value)
			{
				$key1 = array_search($ID, $value); 
				if ($key1 !== false)
				{
					unset($arDiscountPriceTypes[$key][$key1]);
					if (count($arDiscountPriceTypes[$key]) <= 0)
						unset($arDiscountPriceTypes[$key]);
				}
			}
		}

		if (count($arDiscountUserGroups) > 0)
		{
			foreach ($arDiscountUserGroups as $key => $value)
			{
				$key1 = array_search($ID, $value); 
				if ($key1 !== false)
				{
					unset($arDiscountUserGroups[$key][$key1]);
					if (count($arDiscountUserGroups[$key]) <= 0)
						unset($arDiscountUserGroups[$key]);
				}
			}
		}

		if (count($arDiscountProducts) > 0)
		{
			foreach ($arDiscountProducts as $key => $value)
			{
				$key1 = array_search($ID, $value); 
				if ($key1 !== false)
				{
					unset($arDiscountProducts[$key][$key1]);
					if (count($arDiscountProducts[$key]) <= 0)
						unset($arDiscountProducts[$key]);
				}
			}
		}

		$dbSectionsList = CCatalogDiscount::GetDiscountSectionsList(
			array(),
			array("DISCOUNT_ID" => $ID),
			false,
			false,
			array("ID", "SECTION_ID")
		);
		if ($arSectionsList = $dbSectionsList->Fetch())
		{
			do
			{
				$dbSection = CIBlockSection::GetByID($arSectionsList["SECTION_ID"]);
				if ($arSection = $dbSection->Fetch())
				{
					$dbSectionTree = CIBlockSection::GetList(
						array("LEFT_MARGIN" => "DESC"),
						array(
							"IBLOCK_ID" => $arSection["IBLOCK_ID"],
							"ACTIVE" => "Y",
							"GLOBAL_ACTIVE" => "Y",
							"IBLOCK_ACTIVE" => "Y",
							">=LEFT_BORDER" => $arSection["LEFT_MARGIN"],
							"<=RIGHT_BORDER" => $arSection["RIGHT_MARGIN"]
						)
					);
					while ($arSectionTree = $dbSectionTree->Fetch())
					{
						if (!array_key_exists($arSectionTree["ID"], $arDiscountSections))
							$arDiscountSections[$arSectionTree["ID"]] = array();

						$arDiscountSections[$arSectionTree["ID"]][] = $ID;
					}
				}
			}
			while ($arSectionsList = $dbSectionsList->Fetch());
		}
		else
		{
			if (!array_key_exists(0, $arDiscountSections))
				$arDiscountSections[0] = array();

			$arDiscountSections[0][] = $ID;
		}

		$dbCatsList = CCatalogDiscount::GetDiscountCatsList(
			array(),
			array("DISCOUNT_ID" => $ID),
			false,
			false,
			array("ID", "CATALOG_GROUP_ID")
		);
		if ($arCatsList = $dbCatsList->Fetch())
		{
			do
			{
				if (!array_key_exists($arCatsList["CATALOG_GROUP_ID"], $arDiscountPriceTypes))
					$arDiscountPriceTypes[$arCatsList["CATALOG_GROUP_ID"]] = array();

				$arDiscountPriceTypes[$arCatsList["CATALOG_GROUP_ID"]][] = $ID;
			}
			while ($arCatsList = $dbCatsList->Fetch());
		}
		else
		{
			if (!array_key_exists(0, $arDiscountPriceTypes))
				$arDiscountPriceTypes[0] = array();

			$arDiscountPriceTypes[0][] = $ID;
		}

		$dbGroupsList = CCatalogDiscount::GetDiscountGroupsList(
			array(),
			array("DISCOUNT_ID" => $ID),
			false,
			false,
			array("ID", "GROUP_ID")
		);
		if ($arGroupsList = $dbGroupsList->Fetch())
		{
			do
			{
				if (!array_key_exists($arGroupsList["GROUP_ID"], $arDiscountUserGroups))
					$arDiscountUserGroups[$arGroupsList["GROUP_ID"]] = array();

				$arDiscountUserGroups[$arGroupsList["GROUP_ID"]][] = $ID;
			}
			while ($arGroupsList = $dbGroupsList->Fetch());
		}
		else
		{
			if (!array_key_exists(0, $arDiscountUserGroups))
				$arDiscountUserGroups[0] = array();

			$arDiscountUserGroups[0][] = $ID;
		}

		$dbProductsList = CCatalogDiscount::GetDiscountProductsList(
			array(),
			array("DISCOUNT_ID" => $ID),
			false,
			false,
			array("ID", "PRODUCT_ID")
		);
		if ($arProductsList = $dbProductsList->Fetch())
		{
			do
			{
				if (!array_key_exists($arProductsList["PRODUCT_ID"], $arDiscountProducts))
					$arDiscountProducts[$arProductsList["PRODUCT_ID"]] = array();

				$arDiscountProducts[$arProductsList["PRODUCT_ID"]][] = $ID;
			}
			while ($arProductsList = $dbProductsList->Fetch());
		}
		else
		{
			if (!array_key_exists(0, $arDiscountProducts))
				$arDiscountProducts[0] = array();

			$arDiscountProducts[0][] = $ID;
		}

		ignore_user_abort(true);
		if ($fp = @fopen($_SERVER["DOCUMENT_ROOT"].$strDataFileName, "wb"))
		{
			if (flock($fp, LOCK_EX))
			{
				fwrite($fp, "<"."?\n");
				fwrite($fp, "\$arDiscountSections=unserialize('".serialize($arDiscountSections)."');");
				fwrite($fp, "if(!is_array(\$arDiscountSections))\$arDiscountSections=array();\n");
				fwrite($fp, "\$arDiscountPriceTypes=unserialize('".serialize($arDiscountPriceTypes)."');");
				fwrite($fp, "if(!is_array(\$arDiscountPriceTypes))\$arDiscountPriceTypes=array();\n");
				fwrite($fp, "\$arDiscountUserGroups=unserialize('".serialize($arDiscountUserGroups)."');");
				fwrite($fp, "if(!is_array(\$arDiscountUserGroups))\$arDiscountUserGroups=array();\n");
				fwrite($fp, "\$arDiscountProducts=unserialize('".serialize($arDiscountProducts)."');");
				fwrite($fp, "if(!is_array(\$arDiscountProducts))\$arDiscountProducts=array();\n");
				fwrite($fp, "?".">");

				fflush($fp);
				flock($fp, LOCK_UN);
				fclose($fp);
			}
		}
		ignore_user_abort(false);
	}

	function ClearFile($ID, $strDataFileName = False)
	{
		$ID = IntVal($ID);
		if ($ID <= 0)
			return false;

		if (!$strDataFileName || ($strDataFileName != CATALOG_DISCOUNT_FILE && $strDataFileName != CATALOG_DISCOUNT_CPN_FILE))
		{
			$strDataFileName = CATALOG_DISCOUNT_FILE;
			$dbCoupons = CCatalogDiscountCoupon::GetList(array(), array("DISCOUNT_ID" => $ID), false, array("nTopCount" => 1), array("ID"));
			if ($dbCoupons->Fetch())
				$strDataFileName = CATALOG_DISCOUNT_CPN_FILE;
		}

		$arDiscountSections = array();
		$arDiscountPriceTypes = array();
		$arDiscountUserGroups = array();
		$arDiscountProducts = array();

		if (file_exists($_SERVER["DOCUMENT_ROOT"].$strDataFileName) && is_file($_SERVER["DOCUMENT_ROOT"].$strDataFileName))
			include($_SERVER["DOCUMENT_ROOT"].$strDataFileName);

		if (count($arDiscountSections) > 0)
		{
			foreach ($arDiscountSections as $key => $value)
			{
				$key1 = array_search($ID, $value); 
				if ($key1 !== false)
				{
					unset($arDiscountSections[$key][$key1]);
					if (count($arDiscountSections[$key]) <= 0)
						unset($arDiscountSections[$key]);
				}
			}
		}

		if (count($arDiscountPriceTypes) > 0)
		{
			foreach ($arDiscountPriceTypes as $key => $value)
			{
				$key1 = array_search($ID, $value); 
				if ($key1 !== false)
				{
					unset($arDiscountPriceTypes[$key][$key1]);
					if (count($arDiscountPriceTypes[$key]) <= 0)
						unset($arDiscountPriceTypes[$key]);
				}
			}
		}

		if (count($arDiscountUserGroups) > 0)
		{
			foreach ($arDiscountUserGroups as $key => $value)
			{
				$key1 = array_search($ID, $value); 
				if ($key1 !== false)
				{
					unset($arDiscountUserGroups[$key][$key1]);
					if (count($arDiscountUserGroups[$key]) <= 0)
						unset($arDiscountUserGroups[$key]);
				}
			}
		}

		if (count($arDiscountProducts) > 0)
		{
			foreach ($arDiscountProducts as $key => $value)
			{
				$key1 = array_search($ID, $value); 
				if ($key1 !== false)
				{
					unset($arDiscountProducts[$key][$key1]);
					if (count($arDiscountProducts[$key]) <= 0)
						unset($arDiscountProducts[$key]);
				}
			}
		}

		ignore_user_abort(true);
		if ($fp = @fopen($_SERVER["DOCUMENT_ROOT"].$strDataFileName, "wb"))
		{
			if (flock($fp, LOCK_EX))
			{
				fwrite($fp, "<"."?\n");
				fwrite($fp, "\$arDiscountSections=unserialize('".serialize($arDiscountSections)."');");
				fwrite($fp, "if(!is_array(\$arDiscountSections))\$arDiscountSections=array();\n");
				fwrite($fp, "\$arDiscountPriceTypes=unserialize('".serialize($arDiscountPriceTypes)."');");
				fwrite($fp, "if(!is_array(\$arDiscountPriceTypes))\$arDiscountPriceTypes=array();\n");
				fwrite($fp, "\$arDiscountUserGroups=unserialize('".serialize($arDiscountUserGroups)."');");
				fwrite($fp, "if(!is_array(\$arDiscountUserGroups))\$arDiscountUserGroups=array();\n");
				fwrite($fp, "\$arDiscountProducts=unserialize('".serialize($arDiscountProducts)."');");
				fwrite($fp, "if(!is_array(\$arDiscountProducts))\$arDiscountProducts=array();\n");
				fwrite($fp, "?".">");

				fflush($fp);
				flock($fp, LOCK_UN);
				fclose($fp);
			}
		}
		ignore_user_abort(false);
	}

	function GetDiscountByPrice($productPriceID, $arUserGroups = array(), $renewal = "N", $siteID = false, $arDiscountCoupons = false)
	{
		global $DB;

		$events = GetModuleEvents("catalog", "OnGetDiscountByPrice");
		if ($arEvent = $events->Fetch())
			return ExecuteModuleEventEx($arEvent, array($productPriceID, $arUserGroups, $renewal, $siteID, $arDiscountCoupons));

		$productPriceID = IntVal($productPriceID);
		if ($productPriceID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Price ID is not set", "NO_PRICE_ID");
			return False;
		}

		if (!is_array($arUserGroups) && IntVal($arUserGroups)."|" == $arUserGroups."|")
			$arUserGroups = array(IntVal($arUserGroups));

		if (!is_array($arUserGroups))
			$arUserGroups = array();

		if (!in_array(2, $arUserGroups))
			$arUserGroups[] = 2;

		$renewal = (($renewal == "N") ? "N" : "Y");

		if ($siteID === false)
			$siteID = SITE_ID;

		if ($arDiscountCoupons === false)
			$arDiscountCoupons = CCatalogDiscount::GetCoupons();

		$dbPrice = CPrice::GetListEx(
			array(),
			array("ID" => $productPriceID),
			false,
			false,
			array("ID", "PRODUCT_ID", "CATALOG_GROUP_ID", "ELEMENT_IBLOCK_ID")
		);
		if ($arPrice = $dbPrice->Fetch())
		{
			return CCatalogDiscount::GetDiscount($arPrice["PRODUCT_ID"], $arPrice["ELEMENT_IBLOCK_ID"], $arPrice["CATALOG_GROUP_ID"], $arUserGroups, $renewal, $siteID, $arDiscountCoupons);
		}
		else
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $productPriceID, "Price ##ID# is not found"), "NO_PRICE");
			return False;
		}
	}

	function GetDiscountByProduct($productID = 0, $arUserGroups = array(), $renewal = "N", $arCatalogGroups = array(), $siteID = false, $arDiscountCoupons = false)
	{
		global $DB;

		$events = GetModuleEvents("catalog", "OnGetDiscountByProduct");
		if ($arEvent = $events->Fetch())
			return ExecuteModuleEventEx($arEvent, array($productID, $arUserGroups, $renewal, $arCatalogGroups, $siteID, $arDiscountCoupons));

		$productID = IntVal($productID);

		if (isset($arCatalogGroups))
		{
			if (is_array($arCatalogGroups))
			{
				array_walk($arCatalogGroups, create_function("&\$item", "\$item=IntVal(\$item);"));
				$arCatalogGroups = array_unique($arCatalogGroups);
			}
			else
			{
				if (IntVal($arCatalogGroups)."|" == $arCatalogGroups."|")
					$arCatalogGroups = array(IntVal($arCatalogGroups));
				else
					$arCatalogGroups = array();
			}
		}
		else
		{
			$arCatalogGroups = array();
		}

		if (!is_array($arUserGroups) && IntVal($arUserGroups)."|" == $arUserGroups."|")
			$arUserGroups = array(IntVal($arUserGroups));

		if (!is_array($arUserGroups))
			$arUserGroups = array();

		if (!in_array(2, $arUserGroups))
			$arUserGroups[] = 2;

		$renewal = (($renewal == "N") ? "N" : "Y");

		if ($siteID === false)
			$siteID = SITE_ID;

		if ($arDiscountCoupons === false)
			$arDiscountCoupons = CCatalogDiscount::GetCoupons();

		$dbElement = CIBlockElement::GetList(array(), array("ID"=>$productID), false, false, array("ID","IBLOCK_ID"));
		if (!($arElement = $dbElement->Fetch()))
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $productID, "Element ##ID# is not found"), "NO_ELEMENT");
			return False;
		}

		return CCatalogDiscount::GetDiscount($productID, $arElement["IBLOCK_ID"], $arCatalogGroups, $arUserGroups, $renewal, $siteID, $arDiscountCoupons);
	}

	function GetDiscount($productID, $iblockID, $arCatalogGroups = array(), $arUserGroups = array(), $renewal = "N", $siteID = false, $arDiscountCoupons = false)
	{
		global $DB;

		$events = GetModuleEvents("catalog", "OnGetDiscount");
		if ($arEvent = $events->Fetch())
			return ExecuteModuleEventEx($arEvent, array($productID, $iblockID, $arCatalogGroups, $arUserGroups, $renewal, $siteID, $arDiscountCoupons));

		$productID = IntVal($productID);
		if ($productID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Product ID is not set", "NO_PRODUCT_ID");
			return False;
		}

		$iblockID = IntVal($iblockID);
		if ($iblockID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("IBlock ID is not set", "NO_IBLOCK_ID");
			return False;
		}
		
		if (isset($arCatalogGroups))
		{
			if (is_array($arCatalogGroups))
			{
				array_walk($arCatalogGroups, create_function("&\$item", "\$item=IntVal(\$item);"));
				$arCatalogGroups = array_unique($arCatalogGroups);
			}
			else
			{
				if (IntVal($arCatalogGroups)."|" == $arCatalogGroups."|")
					$arCatalogGroups = array(IntVal($arCatalogGroups));
				else
					$arCatalogGroups = array();
			}
		}
		else
		{
			$arCatalogGroups = array();
		}

		if (!is_array($arUserGroups) && IntVal($arUserGroups)."|" == $arUserGroups."|")
			$arUserGroups = array(IntVal($arUserGroups));

		if (!is_array($arUserGroups))
			$arUserGroups = array();

		if (!in_array(2, $arUserGroups))
			$arUserGroups[] = 2;

		$renewal = (($renewal == "N") ? "N" : "Y");

		if ($siteID === false)
			$siteID = SITE_ID;

		if ($arDiscountCoupons === false)
			$arDiscountCoupons = CCatalogDiscount::GetCoupons();

		$arResult = array();

		$valueProductFilter = COption::GetOptionString("catalog", "do_use_discount_product", "Y");
		$valueCatalogGroupFilter = COption::GetOptionString("catalog", "do_use_discount_cat_group", "Y");
		$valueSectionFilter = COption::GetOptionString("catalog", "do_use_discount_section", "Y");
		$valueGroupFilter = COption::GetOptionString("catalog", "do_use_discount_group", "Y");

		$cacheTime = CATALOG_CACHE_DEFAULT_TIME;
		if (defined("CATALOG_CACHE_TIME"))
			$cacheTime = IntVal(CATALOG_CACHE_TIME);

		$strCacheKey = "I";

		if ($valueProductFilter == "Y")
			$strCacheKey .= "_".$productID;
		else
			$strCacheKey .= "_x";

		$arProductSections = array();
		if ($valueSectionFilter == "Y")
		{
			$strCacheKeyGroups = $productID."_".$iblockID;

			$stackLengthGroups = 200;
			if (defined("CATALOG_STACK_ELEMENT_LENGTH"))
				$stackLengthGroups = IntVal(CATALOG_STACK_ELEMENT_LENGTH);

			$GLOBALS["stackCacheManager"]->SetLength("catalog_element_groups", $stackLengthGroups);
			$GLOBALS["stackCacheManager"]->SetTTL("catalog_element_groups", $cacheTime);
			if ($GLOBALS["stackCacheManager"]->Exist("catalog_element_groups", $strCacheKeyGroups))
			{
				$arProductSections = $GLOBALS["stackCacheManager"]->Get("catalog_element_groups", $strCacheKeyGroups);
			}
			else
			{
				$arProductSections = array();
				$dbElementSections = CIBlockElement::GetElementGroups($productID);
				while ($arElementSections = $dbElementSections->Fetch())
				{
					if (IntVal($arElementSections["IBLOCK_ID"]) == $iblockID)
					{
						$arProductSections[] = IntVal($arElementSections["ID"]);
					}
				}

				$GLOBALS["stackCacheManager"]->Set("catalog_element_groups", $strCacheKeyGroups, $arProductSections);
			}

			for ($i = 0, $cnt = count($arProductSections); $i < $cnt; $i++)
				$strCacheKey .= "_".$arProductSections[$i];
		}
		else
		{
			$strCacheKey .= "_x";
		}

		if ($valueCatalogGroupFilter == "Y")
		{
			for ($i = 0; $i < count($arCatalogGroups); $i++)
				$strCacheKey .= "_".$arCatalogGroups[$i];
		}
		else
		{
			$strCacheKey .= "_x";
		}

		if ($valueGroupFilter == "Y")
		{
			for ($i = 0; $i < count($arUserGroups); $i++)
				$strCacheKey .= "_".$arUserGroups[$i];
		}
		else
		{
			$strCacheKey .= "_x";
		}

		$strCacheKey .= "_".$renewal;
		$strCacheKey .= "_".$siteID;
		if (count($arDiscountCoupons) > 0)
		{
			for ($i = 0; $i < count($arDiscountCoupons); $i++)
				$strCacheKey .= "_".$arDiscountCoupons[$i];
		}
		else
		{
			$strCacheKey .= "_x";
		}

		$stackLength = 100;
		if (defined("CATALOG_STACK_DISCOUNT_LENGTH"))
			$stackLength = IntVal(CATALOG_STACK_DISCOUNT_LENGTH);

		$GLOBALS["stackCacheManager"]->SetLength("catalog_discount", $stackLength);
		$GLOBALS["stackCacheManager"]->SetTTL("catalog_discount", $cacheTime);
		if ($GLOBALS["stackCacheManager"]->Exist("catalog_discount", $strCacheKey))
		{
			$arResult = $GLOBALS["stackCacheManager"]->Get("catalog_discount", $strCacheKey);
		}
		else
		{
			if (!isset($GLOBALS["CATALOG_DISCOUNT_SECTION_CACHE"]) || !is_array($GLOBALS["CATALOG_DISCOUNT_SECTION_CACHE"])
				|| !isset($GLOBALS["CATALOG_DISCOUNT_TYPES_CACHE"]) || !is_array($GLOBALS["CATALOG_DISCOUNT_TYPES_CACHE"])
				|| !isset($GLOBALS["CATALOG_DISCOUNT_GROUPS_CACHE"]) || !is_array($GLOBALS["CATALOG_DISCOUNT_GROUPS_CACHE"])
				|| !isset($GLOBALS["CATALOG_DISCOUNT_PRODUCTS_CACHE"]) || !is_array($GLOBALS["CATALOG_DISCOUNT_PRODUCTS_CACHE"]))
			{
				if (file_exists($_SERVER["DOCUMENT_ROOT"].CATALOG_DISCOUNT_FILE) && is_file($_SERVER["DOCUMENT_ROOT"].CATALOG_DISCOUNT_FILE))
				{
					$arDiscountSections = array();
					$arDiscountPriceTypes = array();
					$arDiscountUserGroups = array();
					$arDiscountProducts = array();

					include($_SERVER["DOCUMENT_ROOT"].CATALOG_DISCOUNT_FILE);

					$GLOBALS["CATALOG_DISCOUNT_SECTION_CACHE"] = $arDiscountSections;
					$GLOBALS["CATALOG_DISCOUNT_TYPES_CACHE"] = $arDiscountPriceTypes;
					$GLOBALS["CATALOG_DISCOUNT_GROUPS_CACHE"] = $arDiscountUserGroups;
					$GLOBALS["CATALOG_DISCOUNT_PRODUCTS_CACHE"] = $arDiscountProducts;
				}
				else
				{
					$GLOBALS["CATALOG_DISCOUNT_SECTION_CACHE"] = array();
					$GLOBALS["CATALOG_DISCOUNT_TYPES_CACHE"] = array();
					$GLOBALS["CATALOG_DISCOUNT_GROUPS_CACHE"] = array();
					$GLOBALS["CATALOG_DISCOUNT_PRODUCTS_CACHE"] = array();
				}
				if (count($arDiscountCoupons) > 0)
				{
					if (file_exists($_SERVER["DOCUMENT_ROOT"].CATALOG_DISCOUNT_CPN_FILE) && is_file($_SERVER["DOCUMENT_ROOT"].CATALOG_DISCOUNT_CPN_FILE))
					{
						$arDiscountSections = array();
						$arDiscountPriceTypes = array();
						$arDiscountUserGroups = array();
						$arDiscountProducts = array();

						include($_SERVER["DOCUMENT_ROOT"].CATALOG_DISCOUNT_CPN_FILE);

						$arDiscountSectionsKeys = array_keys($arDiscountSections);
						for ($i = 0; $i < count($arDiscountSectionsKeys); $i++)
						{
							if (array_key_exists($arDiscountSectionsKeys[$i], $GLOBALS["CATALOG_DISCOUNT_SECTION_CACHE"]))
								$GLOBALS["CATALOG_DISCOUNT_SECTION_CACHE"][$arDiscountSectionsKeys[$i]] = array_merge($GLOBALS["CATALOG_DISCOUNT_SECTION_CACHE"][$arDiscountSectionsKeys[$i]], $arDiscountSections[$arDiscountSectionsKeys[$i]]);
							else
								$GLOBALS["CATALOG_DISCOUNT_SECTION_CACHE"][$arDiscountSectionsKeys[$i]] = $arDiscountSections[$arDiscountSectionsKeys[$i]];
						}

						$arDiscountPriceTypesKeys = array_keys($arDiscountPriceTypes);
						for ($i = 0; $i < count($arDiscountPriceTypesKeys); $i++)
						{
							if (array_key_exists($arDiscountPriceTypesKeys[$i], $GLOBALS["CATALOG_DISCOUNT_TYPES_CACHE"]))
								$GLOBALS["CATALOG_DISCOUNT_TYPES_CACHE"][$arDiscountPriceTypesKeys[$i]] = array_merge($GLOBALS["CATALOG_DISCOUNT_TYPES_CACHE"][$arDiscountPriceTypesKeys[$i]], $arDiscountPriceTypes[$arDiscountPriceTypesKeys[$i]]);
							else
								$GLOBALS["CATALOG_DISCOUNT_TYPES_CACHE"][$arDiscountPriceTypesKeys[$i]] = $arDiscountPriceTypes[$arDiscountPriceTypesKeys[$i]];
						}

						$arDiscountUserGroupsKeys = array_keys($arDiscountUserGroups);
						for ($i = 0; $i < count($arDiscountUserGroupsKeys); $i++)
						{
							if (array_key_exists($arDiscountUserGroupsKeys[$i], $GLOBALS["CATALOG_DISCOUNT_GROUPS_CACHE"]))
								$GLOBALS["CATALOG_DISCOUNT_GROUPS_CACHE"][$arDiscountUserGroupsKeys[$i]] = array_merge($GLOBALS["CATALOG_DISCOUNT_GROUPS_CACHE"][$arDiscountUserGroupsKeys[$i]], $arDiscountUserGroups[$arDiscountUserGroupsKeys[$i]]);
							else
								$GLOBALS["CATALOG_DISCOUNT_GROUPS_CACHE"][$arDiscountUserGroupsKeys[$i]] = $arDiscountUserGroups[$arDiscountUserGroupsKeys[$i]];
						}

						$arDiscountProductsKeys = array_keys($arDiscountProducts);
						for ($i = 0; $i < count($arDiscountProductsKeys); $i++)
						{
							if (array_key_exists($arDiscountProductsKeys[$i], $GLOBALS["CATALOG_DISCOUNT_PRODUCTS_CACHE"]))
								$GLOBALS["CATALOG_DISCOUNT_PRODUCTS_CACHE"][$arDiscountProductsKeys[$i]] = array_merge($GLOBALS["CATALOG_DISCOUNT_PRODUCTS_CACHE"][$arDiscountProductsKeys[$i]], $arDiscountProducts[$arDiscountProductsKeys[$i]]);
							else
								$GLOBALS["CATALOG_DISCOUNT_PRODUCTS_CACHE"][$arDiscountProductsKeys[$i]] = $arDiscountProducts[$arDiscountProductsKeys[$i]];
						}
					}
				}
			}

			$arDiscountSections = $GLOBALS["CATALOG_DISCOUNT_SECTION_CACHE"];
			$arDiscountPriceTypes = $GLOBALS["CATALOG_DISCOUNT_TYPES_CACHE"];
			$arDiscountUserGroups = $GLOBALS["CATALOG_DISCOUNT_GROUPS_CACHE"];
			$arDiscountProducts = $GLOBALS["CATALOG_DISCOUNT_PRODUCTS_CACHE"];

			$arDiscountIDsTmp = array();
			if (array_key_exists(0, $arDiscountSections))
				$arDiscountIDsTmp = array_merge($arDiscountIDsTmp, $arDiscountSections[0]);
			for ($i = 0; $i < count($arProductSections); $i++)
				if (array_key_exists($arProductSections[$i], $arDiscountSections))
					$arDiscountIDsTmp = array_merge($arDiscountIDsTmp, $arDiscountSections[$arProductSections[$i]]);

			$arDiscountIDsTmp1 = array();
			if (array_key_exists(0, $arDiscountPriceTypes))
				$arDiscountIDsTmp1 = array_merge($arDiscountIDsTmp1, $arDiscountPriceTypes[0]);
			for ($i = 0; $i < count($arCatalogGroups); $i++)
				if (array_key_exists($arCatalogGroups[$i], $arDiscountPriceTypes))
					$arDiscountIDsTmp1 = array_merge($arDiscountIDsTmp1, $arDiscountPriceTypes[$arCatalogGroups[$i]]);

			$arDiscountIDsTmp2 = array();
			if (array_key_exists(0, $arDiscountUserGroups))
				$arDiscountIDsTmp2 = array_merge($arDiscountIDsTmp2, $arDiscountUserGroups[0]);
			for ($i = 0; $i < count($arUserGroups); $i++)
				if (array_key_exists($arUserGroups[$i], $arDiscountUserGroups))
					$arDiscountIDsTmp2 = array_merge($arDiscountIDsTmp2, $arDiscountUserGroups[$arUserGroups[$i]]);

			$arDiscountIDsTmp3 = array();
			if (array_key_exists(0, $arDiscountProducts))
				$arDiscountIDsTmp3 = array_merge($arDiscountIDsTmp3, $arDiscountProducts[0]);
			if (array_key_exists($productID, $arDiscountProducts))
				$arDiscountIDsTmp3 = array_merge($arDiscountIDsTmp3, $arDiscountProducts[$productID]);

			$arDiscountIDsTmp = array_intersect($arDiscountIDsTmp, $arDiscountIDsTmp1, $arDiscountIDsTmp2, $arDiscountIDsTmp3);

			$arDiscountIDs = array();
			foreach ($arDiscountIDsTmp as $value)
				$arDiscountIDs[] = $value;

			if (count($arDiscountIDs) > 0)
			{
				$arFilter = array(
					"ID" => $arDiscountIDs,
					"SITE_ID" => $siteID,
					"ACTIVE" => "Y",
					"+<=ACTIVE_FROM" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
					"+>=ACTIVE_TO" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
					"RENEWAL" => $renewal
				);
				
				if (is_array($arDiscountCoupons))
				{
					$arFilter["+COUPON"] = $arDiscountCoupons;
				}
				
				$dbPriceDiscount = CCatalogDiscount::GetList(
					array(
						"VALUE" => "DESC",
						"SORT" => "ASC",
						"ID" => "DESC"
					),
					array_merge($arFilter, array("VALUE_TYPE" => "P")),
					false,
					false,
					array("ID", "SITE_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO", "RENEWAL", "NAME", "MAX_USES", "COUNT_USES", "SORT", "MAX_DISCOUNT", "VALUE_TYPE", "VALUE", "CURRENCY", "MIN_ORDER_SUM", "TIMESTAMP_X", "NOTES", "COUPON", "COUPON_ONE_TIME", "COUPON_ACTIVE")
				);

				if ($arPriceDiscount = $dbPriceDiscount->Fetch())
					$arResult[] = $arPriceDiscount;

				$dbPriceDiscount = CCatalogDiscount::GetList(
					array(
						"VALUE" => "DESC",
						"SORT" => "ASC",
						"ID" => "DESC"
					),
					array_merge($arFilter, array("VALUE_TYPE" => "F")),
					false,
					false,
					array("ID", "SITE_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO", "RENEWAL", "NAME", "MAX_USES", "COUNT_USES", "SORT", "MAX_DISCOUNT", "VALUE_TYPE", "VALUE", "CURRENCY", "MIN_ORDER_SUM", "TIMESTAMP_X", "NOTES", "COUPON", "COUPON_ONE_TIME")
				);

				if ($arPriceDiscount = $dbPriceDiscount->Fetch())
				{
					if ($arPriceDiscount['COUPON_ACTIVE'] != 'N')
						$arResult[] = $arPriceDiscount;
				}
			}

			$GLOBALS["stackCacheManager"]->Set("catalog_discount", $strCacheKey, $arResult);
		}

		return $arResult;
	}

	function HaveCoupons($ID, $excludeID = 0)
	{
		$ID = IntVal($ID);
		if ($ID <= 0)
			return False;

		$arFilter = array("DISCOUNT_ID" => $ID);

		$excludeID = IntVal($excludeID);
		if ($excludeID > 0)
			$arFilter["!ID"] = $excludeID;

		$dbRes = CCatalogDiscountCoupon::GetList(array(), $arFilter, false, array("nTopCount" => 1), array("ID"));
		if ($dbRes->Fetch())
			return True;
		else
			return False;
	}
}
?>