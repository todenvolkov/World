<?
IncludeModuleLangFile(__FILE__);

$GLOBALS['CATALOG_CATALOG_CACHE'] = null;
$GLOBALS['CATALOG_PRODUCT_CACHE'] = null;
$GLOBALS['MAIN_EXTRA_LIST_CACHE'] = null;
$GLOBALS["CATALOG_DISCOUNT_SECTION_CACHE"] = null;
$GLOBALS["CATALOG_DISCOUNT_TYPES_CACHE"] = null;
$GLOBALS["CATALOG_DISCOUNT_GROUPS_CACHE"] = null;
$GLOBALS["CATALOG_DISCOUNT_PRODUCTS_CACHE"] = null;

$GLOBALS['CATALOG_ONETIME_COUPONS_ORDER'] = null;
$GLOBALS['CATALOG_ONETIME_COUPONS_BASKET'] = null;

if (!CModule::IncludeModule("iblock"))
{
//	trigger_error("IBlock is not installed");
	$GLOBALS["APPLICATION"]->ThrowException(GetMessage('CAT_ERROR_IBLOCK_NOT_INSTALLED'));
	return false;
}

if (!CModule::IncludeModule("currency"))
{
//	trigger_error("Currency is not installed");
	$GLOBALS["APPLICATION"]->ThrowException(GetMessage('CAT_ERROR_CURRENCY_NOT_INSTALLED'));
	return false;
}

global $DBType, $CATALOG_TIME_PERIOD_TYPES;

$CATALOG_TIME_PERIOD_TYPES = array(
		"H" => GetMessage("I_PERIOD_HOUR"),
		"D" => GetMessage("I_PERIOD_DAY"),
		"W" => GetMessage("I_PERIOD_WEEK"),
		"M" => GetMessage("I_PERIOD_MONTH"),
		"Q" => GetMessage("I_PERIOD_QUART"),
		"S" => GetMessage("I_PERIOD_SEMIYEAR"),
		"Y" => GetMessage("I_PERIOD_YEAR")
	);

Define("CATALOG_VALUE_PRECISION", 2);
Define("CATALOG_CACHE_DEFAULT_TIME", 10800);

CModule::AddAutoloadClasses(
	"catalog",
	array(
		"CCatalog" => $DBType."/catalog.php",
		"CCatalogGroup" => $DBType."/cataloggroup.php",
		"CExtra" => $DBType."/extra.php",
		"CPrice" => $DBType."/price.php",
		"CCatalogProduct" => $DBType."/product.php",
		"CCatalogProductGroups" => $DBType."/product_group.php",
		"CCatalogLoad" => $DBType."/catalog_load.php",
		"CCatalogExport" => $DBType."/catalog_export.php",
		"CCatalogImport" => $DBType."/catalog_import.php",
		"CCatalogDiscount" => $DBType."/discount.php",
		"CCatalogDiscountCoupon" => $DBType."/discount_coupon.php",
		"CCatalogVat" => $DBType."/vat.php",
	)
);

/*
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/catalog.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/cataloggroup.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/extra.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/price.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/product.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/catalog_load.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/catalog_export.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/catalog_import.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/discount.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/".$DBType."/discount_coupon.php");
*/
//include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/cache.php");

define("CATALOG_PATH2EXPORTS", "/bitrix/php_interface/include/catalog_export/");
define("CATALOG_PATH2EXPORTS_DEF", "/bitrix/modules/catalog/load/");

define("CATALOG_PATH2IMPORTS", "/bitrix/php_interface/include/catalog_import/");
define("CATALOG_PATH2IMPORTS_DEF", "/bitrix/modules/catalog/load_import/");

/*************************************************************/
/*************************************************************/
global $MESS;
global $arCatalogAvailProdFields, $defCatalogAvailProdFields, $arCatalogAvailGroupFields, $defCatalogAvailGroupFields, $defCatalogAvailCurrencies, $arCatalogAvailPriceFields, $defCatalogAvailPriceFields, $arCatalogAvailValueFields, $defCatalogAvailValueFields;

$arCatalogAvailProdFields = array(
		array("value"=>"IE_XML_ID", "field"=>"XML_ID", "important"=>"Y", "name"=>GetMessage("CATI_FI_UNIXML")." (B_IBLOCK_ELEMENT.XML_ID)"),
		array("value"=>"IE_NAME", "field"=>"NAME", "important"=>"Y", "name"=>GetMessage("CATI_FI_NAME")." (B_IBLOCK_ELEMENT.NAME)"),
		array("value"=>"IE_ACTIVE", "field"=>"ACTIVE", "important"=>"N", "name"=>GetMessage("CATI_FI_ACTIV")." (B_IBLOCK_ELEMENT.ACTIVE)"),
		array("value"=>"IE_ACTIVE_FROM", "field"=>"ACTIVE_FROM", "important"=>"N", "name"=>GetMessage("CATI_FI_ACTIVFROM")." (B_IBLOCK_ELEMENT.ACTIVE_FROM)"),
		array("value"=>"IE_ACTIVE_TO", "field"=>"ACTIVE_TO", "important"=>"N", "name"=>GetMessage("CATI_FI_ACTIVTO")." (B_IBLOCK_ELEMENT.ACTIVE_TO)"),
		array("value"=>"IE_SORT", "field"=>"SORT", "important"=>"N", "name"=>GetMessage("CATI_FI_SORT")." (B_IBLOCK_ELEMENT.SORT)"),
		array("value"=>"IE_PREVIEW_PICTURE", "field"=>"PREVIEW_PICTURE", "important"=>"N", "name"=>GetMessage("CATI_FI_CATIMG")." (B_IBLOCK_ELEMENT.PREVIEW_PICTURE)"),
		array("value"=>"IE_PREVIEW_TEXT", "field"=>"PREVIEW_TEXT", "important"=>"N", "name"=>GetMessage("CATI_FI_CATDESCR")." (B_IBLOCK_ELEMENT.PREVIEW_TEXT)"),
		array("value"=>"IE_PREVIEW_TEXT_TYPE", "field"=>"PREVIEW_TEXT_TYPE", "important"=>"N", "name"=>GetMessage("CATI_FI_CATDESCRTYPE")." (B_IBLOCK_ELEMENT.PREVIEW_TEXT_TYPE)"),
		array("value"=>"IE_DETAIL_PICTURE", "field"=>"DETAIL_PICTURE", "important"=>"N", "name"=>GetMessage("CATI_FI_DETIMG")." (B_IBLOCK_ELEMENT.DETAIL_PICTURE)"),
		array("value"=>"IE_DETAIL_TEXT", "field"=>"DETAIL_TEXT", "important"=>"N", "name"=>GetMessage("CATI_FI_DETDESCR")." (B_IBLOCK_ELEMENT.DETAIL_TEXT)"),
		array("value"=>"IE_DETAIL_TEXT_TYPE", "field"=>"DETAIL_TEXT_TYPE", "important"=>"N", "name"=>GetMessage("CATI_FI_DETDESCRTYPE")." (B_IBLOCK_ELEMENT.DETAIL_TEXT_TYPE)"),
		array("value"=>"IE_CODE", "field"=>"CODE", "important"=>"N", "name"=>GetMessage("CATI_FI_CODE")." (B_IBLOCK_ELEMENT.CODE)"),
		array("value"=>"IE_ID", "field"=>"ID", "important"=>"N", "name"=>GetMessage("CATI_FI_ID")." (B_IBLOCK_ELEMENT.ID)")
	);
$defCatalogAvailProdFields = "IE_XML_ID,IE_NAME,IE_PREVIEW_TEXT,IE_DETAIL_TEXT";

$arCatalogAvailPriceFields = array(
		array("value"=>"CP_QUANTITY", "field"=>"QUANTITY", "important"=>"N", "name"=>GetMessage("CATI_FI_QUANT")." (B_CATALOG_PRODUCT.QUANTITY)"),
		array("value"=>"CP_QUANTITY_TRACE", "field"=>"QUANTITY_TRACE", "important"=>"N", "name"=>GetMessage("CATI_FI_QUANTITY_TRACE")." (B_CATALOG_PRODUCT.QUANTITY_TRACE)"),
		array("value"=>"CP_WEIGHT", "field"=>"WEIGHT", "important"=>"N", "name"=>GetMessage("CATI_FI_WEIGHT")." (B_CATALOG_PRODUCT.WEIGHT)"),
		array("value"=>"CP_PRICE_TYPE", "field"=>"PRICE_TYPE", "important"=>"N", "name"=>GetMessage("I_PAY_TYPE")." (B_CATALOG_PRODUCT.PRICE_TYPE)"),
		array("value"=>"CP_RECUR_SCHEME_LENGTH", "field"=>"RECUR_SCHEME_LENGTH", "important"=>"N", "name"=>GetMessage("I_PAY_PERIOD_LENGTH")." (B_CATALOG_PRODUCT.RECUR_SCHEME_LENGTH)"),
		array("value"=>"CP_RECUR_SCHEME_TYPE", "field"=>"RECUR_SCHEME_TYPE", "important"=>"N", "name"=>GetMessage("I_PAY_PERIOD_TYPE")." (B_CATALOG_PRODUCT.RECUR_SCHEME_TYPE)"),
		array("value"=>"CP_TRIAL_PRICE_ID", "field"=>"TRIAL_PRICE_ID", "important"=>"N", "name"=>GetMessage("I_TRIAL_FOR")." (B_CATALOG_PRODUCT.TRIAL_PRICE_ID)"),
		array("value"=>"CP_WITHOUT_ORDER", "field"=>"WITHOUT_ORDER", "important"=>"N", "name"=>GetMessage("I_WITHOUT_ORDER")." (B_CATALOG_PRODUCT.WITHOUT_ORDER)")
	);
$defCatalogAvailPriceFields = "CP_QUANTITY,CP_WEIGHT";

$arCatalogAvailValueFields = array(
		array("value"=>"CV_PRICE", "field"=>"PRICE", "important"=>"N", "name"=>GetMessage("I_NAME_PRICE")." (B_CATALOG_PRICE.PRICE)"),
		array("value"=>"CV_CURRENCY", "field"=>"CURRENCY", "important"=>"N", "name"=>GetMessage("I_NAME_CURRENCY")." (B_CATALOG_PRICE.CURRENCY)")
	);
$defCatalogAvailValueFields = "CV_PRICE,CV_CURRENCY"; // CV_QUANTITY_FROM,CV_QUANTITY_TO,

$arCatalogAvailGroupFields = array(
		array("value"=>"IC_XML_ID", "field"=>"XML_ID", "important"=>"Y", "name"=>GetMessage("CATI_FG_UNIXML")." (B_IBLOCK_SECTION.XML_ID)"),
		array("value"=>"IC_GROUP", "field"=>"NAME", "important"=>"Y", "name"=>GetMessage("CATI_FG_NAME")." (B_IBLOCK_SECTION.NAME)"),
		array("value"=>"IC_ACTIVE", "field"=>"ACTIVE", "important"=>"N", "name"=>GetMessage("CATI_FG_ACTIV")." (B_IBLOCK_SECTION.ACTIVE)"),
		array("value"=>"IC_SORT", "field"=>"SORT", "important"=>"N", "name"=>GetMessage("CATI_FG_SORT")." (B_IBLOCK_SECTION.SORT)"),
		array("value"=>"IC_DESCRIPTION", "field"=>"DESCRIPTION", "important"=>"N", "name"=>GetMessage("CATI_FG_DESCR")." (B_IBLOCK_SECTION.DESCRIPTION)"),
		array("value"=>"IC_DESCRIPTION_TYPE", "field"=>"DESCRIPTION_TYPE", "important"=>"N", "name"=>GetMessage("CATI_FG_DESCRTYPE")." (B_IBLOCK_SECTION.DESCRIPTION_TYPE)"),
		array("value"=>"IC_CODE", "field"=>"CODE", "important"=>"N", "name"=>GetMessage("CATI_FG_CODE")." (B_IBLOCK_SECTION.CODE)")
	);
$defCatalogAvailGroupFields = "IC_GROUP";

$defCatalogAvailCurrencies = "USD";

/*************************************************************/
/*************************************************************/
function GetCatalogGroups($by = "SORT", $order = "ASC")
{
	$res = CCatalogGroup::GetList(array($by => $order));
	return $res;
}

function GetCatalogGroup($CATALOG_GROUP_ID)
{
	$CATALOG_GROUP_ID = IntVal($CATALOG_GROUP_ID);
	return CCatalogGroup::GetByID($CATALOG_GROUP_ID);
}

function GetCatalogGroupName($CATALOG_GROUP_ID)
{
	$rn = GetCatalogGroup($CATALOG_GROUP_ID);
	return $rn["NAME_LANG"];
}

function GetCatalogProduct($PRODUCT_ID)
{
	$PRODUCT_ID = IntVal($PRODUCT_ID);
	return CCatalogProduct::GetByID($PRODUCT_ID);
}

function GetCatalogProductEx($PRODUCT_ID)
{
	$PRODUCT_ID = IntVal($PRODUCT_ID);
	return CCatalogProduct::GetByIDEx($PRODUCT_ID);
}

function GetCatalogProductPrice($PRODUCT_ID, $CATALOG_GROUP_ID)
{
	$PRODUCT_ID = IntVal($PRODUCT_ID);
	$CATALOG_GROUP_ID = IntVal($CATALOG_GROUP_ID);

	$db_res = CPrice::GetList(($by="CATALOG_GROUP_ID"), ($order="ASC"), Array("PRODUCT_ID"=>$PRODUCT_ID, "CATALOG_GROUP_ID"=>$CATALOG_GROUP_ID));

	if ($res = $db_res->Fetch())
		return $res;

	return false;
}

function GetCatalogProductPriceList($PRODUCT_ID, $by = "SORT", $order = "ASC")
{
	$PRODUCT_ID = IntVal($PRODUCT_ID);

	$db_res = CPrice::GetList(
			array($by => $order),
			array("PRODUCT_ID" => $PRODUCT_ID)
		);

	$arPrice = array();
	while ($res = $db_res->Fetch())
	{
		$arPrice[] = $res;
	}

	return $arPrice;
}

function GetCatalogProductTable($IBLOCK, $SECT_ID=false, $arOrder=Array("sort"=>"asc"), $cnt=0)
{
	return false;
	$arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
	if ($SECT_ID!==false)
		$arFilter["SECTION_ID"]=IntVal($SECT_ID);

	$res = CCatalogProduct::GetListEx($arOrder, $arFilter);
	$dbr = new CIBlockResult($res->result);
	if ($cnt>0)
		$dbr->NavStart($cnt);

	return $dbr;
}


function FormatCurrency($fSum, $strCurrency)
{
	return CurrencyFormat($fSum, $strCurrency);
	/*
	if (!isset($fSum) || strlen($fSum)<=0) return "";

	$arCurFormat = CCurrencyLang::GetCurrencyFormat($strCurrency);

	if (!isset($arCurFormat["DECIMALS"])) $arCurFormat["DECIMALS"] = 2;
	$arCurFormat["DECIMALS"] = IntVal($arCurFormat["DECIMALS"]);
	if (!isset($arCurFormat["DEC_POINT"])) $arCurFormat["DEC_POINT"] = ".";
	if (!isset($arCurFormat["THOUSANDS_SEP"])) $arCurFormat["THOUSANDS_SEP"] = "\\"."xA0";
	$tmpTHOUSANDS_SEP = $arCurFormat["THOUSANDS_SEP"];
	eval("\$tmpTHOUSANDS_SEP = \"$tmpTHOUSANDS_SEP\";");
	$arCurFormat["THOUSANDS_SEP"] = $tmpTHOUSANDS_SEP;
	if (!isset($arCurFormat["FORMAT_STRING"])) $arCurFormat["FORMAT_STRING"] = "#";

	$num = number_format($fSum, $arCurFormat["DECIMALS"], $arCurFormat["DEC_POINT"], $arCurFormat["THOUSANDS_SEP"]);

	return str_replace("#", $num, $arCurFormat["FORMAT_STRING"]);
	*/
}


function CatalogBasketCallback($productID, $quantity = 0, $renewal = "N")
{
	global $USER;


	$productID = IntVal($productID);
	$quantity = DoubleVal($quantity);
	$renewal = (($renewal == "Y") ? "Y" : "N");

	$arResult = array();

	$dbIBlockElement = CIBlockElement::GetList(
			array(),
			array(
					"ID" => $productID,
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y"
				)
		);
	if(!($arProduct = $dbIBlockElement->GetNext()))
		return $arResult;

		
	$arCatalog = CCatalog::GetByID($arProduct["IBLOCK_ID"]);
	if ($arCatalog["SUBSCRIPTION"] == "Y")
	{
		$quantity = 1;
	}

	if ($arCatalogProduct = CCatalogProduct::GetByID($productID))
	{
		if ($arCatalogProduct["QUANTITY_TRACE"]=="Y" && DoubleVal($arCatalogProduct["QUANTITY"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("CATALOG_NO_QUANTITY_PRODUCT", Array("#NAME#" => $arProduct["NAME"])), "CATALOG_NO_QUANTITY_PRODUCT");
			return $arResult;
		}
	}

	
	$arCoupons = CCatalogDiscount::GetCoupons();
	if (is_array($arCoupons) && is_array($GLOBALS['CATALOG_ONETIME_COUPONS_BASKET']))
	{
		foreach ($arCoupons as $key => $coupon)
		{
			if (array_key_exists($coupon, $GLOBALS['CATALOG_ONETIME_COUPONS_BASKET']))
			{
				if ($GLOBALS['CATALOG_ONETIME_COUPONS_BASKET'][$coupon] == $productID)
				{
					$arCoupons = array($productID);
					break;
				}
				else
					unset($arCoupons[$key]);
			}
		}
	}
	
	//echo '<pre>Product: '; print_r($arCatalogProduct); echo '</pre>';
	//echo '<pre>Coupons: '; print_r($arCoupons); echo '</pre>';
	$arPrice = CCatalogProduct::GetOptimalPrice($productID, $quantity, $USER->GetUserGroupArray(), $renewal, array(), false, $arCoupons);
	//echo '<pre>Price: '; print_r($arPrice); echo '</pre>';
	
	if (!$arPrice || count($arPrice) <= 0)
	{
		if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($productID, $quantity, $USER->GetUserGroupArray()))
		{
			$quantity = $nearestQuantity;
			$arPrice = CCatalogProduct::GetOptimalPrice($productID, $quantity, $USER->GetUserGroupArray(), $renewal, array(), false, $arCoupons);
		}
	}
	
	if (!$arPrice || count($arPrice) <= 0)
	{
		return $arResult;
	}


	$currentPrice = $arPrice["PRICE"]["PRICE"];
	$currentDiscount = 0.0;

	if ($arPrice['PRICE']['VAT_INCLUDED'] == 'N')
	{
		if(DoubleVal($arPrice['PRICE']['VAT_RATE']) > 0)
		{
			$currentPrice *= (1 + $arPrice['PRICE']['VAT_RATE']);
			$arPrice['PRICE']['VAT_INCLUDED'] = 'Y';
		}
	}

	if (isset($arPrice["DISCOUNT"]) && count($arPrice["DISCOUNT"]) > 0)
	{
		if ($arPrice["DISCOUNT"]["VALUE_TYPE"]=="F")
		{
			if ($arPrice["DISCOUNT"]["CURRENCY"] == $arPrice["PRICE"]["CURRENCY"])
				$currentDiscount = $arPrice["DISCOUNT"]["VALUE"];
			else
				$currentDiscount = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT"]["VALUE"], $arPrice["DISCOUNT"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
		}
		else
			$currentDiscount = $currentPrice * $arPrice["DISCOUNT"]["VALUE"] / 100.0;

		$currentDiscount = roundEx($currentDiscount, CATALOG_VALUE_PRECISION);

		if (DoubleVal($arPrice["DISCOUNT"]["MAX_DISCOUNT"]) > 0)
		{
			if ($arPrice["DISCOUNT"]["CURRENCY"] == $baseCurrency)
				$maxDiscount = $arPrice["DISCOUNT"]["MAX_DISCOUNT"];
			else
				$maxDiscount = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT"]["MAX_DISCOUNT"], $arPrice["DISCOUNT"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
			$maxDiscount = roundEx($maxDiscount, CATALOG_VALUE_PRECISION);

			if ($currentDiscount > $maxDiscount)
				$currentDiscount = $maxDiscount;
		}
		
		$currentPrice = $currentPrice - $currentDiscount;
		
		if ($arPrice['DISCOUNT']['COUPON'])
		{
			//echo $arPrice['DISCOUNT']['COUPON'].'<br />';
			$dbRes = CCatalogDiscountCoupon::GetList(array(), array('COUPON' => $arPrice['DISCOUNT']['COUPON'], 'ONE_TIME' => 'Y'), false, false, array('ID'));
		
			if ($arRes = $dbRes->Fetch())
			{
				$GLOBALS['CATALOG_ONETIME_COUPONS_BASKET'][$arPrice['DISCOUNT']['COUPON']] = $productID;
			}
		}
	}
	
	$arResult = array(
			"PRODUCT_PRICE_ID" => $arPrice["PRICE"]["ID"],
			"PRICE" => $currentPrice,
			"VAT_RATE" => $arPrice['PRICE']['VAT_RATE'],
			"CURRENCY" => $arPrice["PRICE"]["CURRENCY"],
			"QUANTITY" => $quantity,
			"DISCOUNT_PRICE" => $currentDiscount,
			"WEIGHT" => 0,
			"NAME" => $arProduct["~NAME"],
			"CAN_BUY" => "Y",
			"NOTES" => $arPrice["PRICE"]["CATALOG_GROUP_NAME"]
		);

	if ($arCatalogProduct)
	{
		$arResult["WEIGHT"] = IntVal($arCatalogProduct["WEIGHT"]);
		if ($arCatalogProduct["QUANTITY_TRACE"]=="Y")
		{
			if ((DoubleVal($arCatalogProduct["QUANTITY"]) - $quantity) < 0)
			{
				$arResult["QUANTITY"] = DoubleVal($arCatalogProduct["QUANTITY"]);
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("CATALOG_QUANTITY_NOT_ENOGH", Array("#NAME#" => $arProduct["NAME"], "#CATALOG_QUANTITY#" => $arCatalogProduct["QUANTITY"], "#QUANTITY#" => $quantity)), "CATALOG_QUANTITY_NOT_ENOGH");
			}
		}
	}

	return $arResult;
}


function CatalogBasketOrderCallback($productID, $quantity, $renewal = "N")
{
	$productID = IntVal($productID);
	$quantity = DoubleVal($quantity);
	$renewal = (($renewal == "Y") ? "Y" : "N");
	$arResult = array();
	
	$dbIBlockElement = CIBlockElement::GetList(
			array(),
			array(
					"ID" => $productID,
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y"
				)
		);
	if(!($arProduct = $dbIBlockElement->GetNext()))
		return $arResult;


	if ($arCatalogProduct = CCatalogProduct::GetByID($productID))
	{
		if ($arCatalogProduct["QUANTITY_TRACE"]=="Y" && DoubleVal($arCatalogProduct["QUANTITY"])<doubleVal($quantity))
			return $arResult;
	}

	$arPrice = CCatalogProduct::GetOptimalPrice($productID, $quantity, $GLOBALS["USER"]->GetUserGroupArray(), $renewal);
	if (!$arPrice || count($arPrice) <= 0)
	{
		if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($productID, $quantity, $GLOBALS["USER"]->GetUserGroupArray()))
		{
			$quantity = $nearestQuantity;
			$arPrice = CCatalogProduct::GetOptimalPrice($productID, $quantity, $GLOBALS["USER"]->GetUserGroupArray(), $renewal);
		}
	}
	if (!$arPrice || count($arPrice) <= 0)
	{
		return $arResult;
	}

	$currentPrice = $arPrice["PRICE"]["PRICE"];
	$currentDiscount = 0.0;
	
	if ($arPrice['PRICE']['VAT_INCLUDED'] == 'N')
	{
		if(DoubleVal($arPrice['PRICE']['VAT_RATE']) > 0)
		{
			$currentPrice *= (1 + $arPrice['PRICE']['VAT_RATE']);
			$arPrice['PRICE']['VAT_INCLUDED'] = 'Y';
		}
	}

	if (isset($arPrice["DISCOUNT"]) && count($arPrice["DISCOUNT"]) > 0)
	{
		if ($arPrice["DISCOUNT"]["VALUE_TYPE"]=="F")
		{
			if ($arPrice["DISCOUNT"]["CURRENCY"] == $arPrice["PRICE"]["CURRENCY"])
				$currentDiscount = $arPrice["DISCOUNT"]["VALUE"];
			else
				$currentDiscount = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT"]["VALUE"], $arPrice["DISCOUNT"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
		}
		else
			$currentDiscount = $currentPrice * $arPrice["DISCOUNT"]["VALUE"] / 100.0;

		$currentDiscount = roundEx($currentDiscount, SALE_VALUE_PRECISION);

		if (DoubleVal($arPrice["DISCOUNT"]["MAX_DISCOUNT"]) > 0)
		{
			if ($arPrice["DISCOUNT"]["CURRENCY"] == $baseCurrency)
				$maxDiscount = $arPrice["DISCOUNT"]["MAX_DISCOUNT"];
			else
				$maxDiscount = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT"]["MAX_DISCOUNT"], $arPrice["DISCOUNT"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
			$maxDiscount = roundEx($maxDiscount, CATALOG_VALUE_PRECISION);

			if ($currentDiscount > $maxDiscount)
				$currentDiscount = $maxDiscount;
		}
		
		$currentPrice = $currentPrice - $currentDiscount;
	}
	
	$arResult = array(
			"PRODUCT_PRICE_ID" => $arPrice["PRICE"]["ID"],
			"PRICE" => $currentPrice,
			"VAT_RATE" => $arPrice['PRICE']['VAT_RATE'],
			"CURRENCY" => $arPrice["PRICE"]["CURRENCY"],
			"QUANTITY" => $quantity,
			"WEIGHT" => 0,
			"NAME" => $arProduct["~NAME"],
			"CAN_BUY" => "Y",
			"NOTES" => $arPrice["PRICE"]["CATALOG_GROUP_NAME"],
			"DISCOUNT_PRICE" => $currentDiscount,
		);
	if(!empty($arPrice["DISCOUNT"]))
	{
		if(strlen($arPrice["DISCOUNT"]["COUPON"])>0)
			$arResult["DISCOUNT_COUPON"] = $arPrice["DISCOUNT"]["COUPON"];
			if($arPrice["DISCOUNT"]["VALUE_TYPE"]=="P")
				$arResult["DISCOUNT_VALUE"] = $arPrice["DISCOUNT"]["VALUE"]."%";
			else
				$arResult["DISCOUNT_VALUE"] = SaleFormatCurrency($arPrice["DISCOUNT"]["VALUE"], $arPrice["DISCOUNT"]["CURRENCY"]);
			$arResult["DISCOUNT_NAME"] = "[".$arPrice["DISCOUNT"]["ID"]."] ".$arPrice["DISCOUNT"]["NAME"];
			
		$dbCoupon = CCatalogDiscountCoupon::GetList(
			array(),
			array("COUPON" => $arPrice["DISCOUNT"]["COUPON"], 'ACTIVE' => 'Y'),
			false,
			false,
			array("ID", "ONE_TIME")
		);
		
		if ($arCoupon = $dbCoupon->Fetch())
		{
			$arFieldsCoupon = Array("DATE_APPLY" => Date($GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID))));

			if ($arCoupon["ONE_TIME"] == "Y")
			{
				$arFieldsCoupon["ACTIVE"] = "N";

				foreach($_SESSION["CATALOG_USER_COUPONS"] as $k => $v)
				{
					if(trim($v) == trim($arPrice["DISCOUNT"]["COUPON"]))
					{
						unset($_SESSION["CATALOG_USER_COUPONS"][$k]);
						$_SESSION["CATALOG_USER_COUPONS"][$k] == "";
					}
				}
			}

			CCatalogDiscountCoupon::Update($arCoupon["ID"], $arFieldsCoupon);
		}
	}

	if ($arCatalogProduct)
	{
		$arResult["WEIGHT"] = IntVal($arCatalogProduct["WEIGHT"]);
	}
	CCatalogProduct::QuantityTracer($productID, $quantity);
	return $arResult;
}

function CatalogDeactivateOneTimeCoupons()
{
	global $CATALOG_ONETIME_COUPONS_ORDER;
	
	if (is_array($CATALOG_ONETIME_COUPONS_ORDER))
	{
		$arFieldsCoupon = array("ACTIVE" => "N");
		foreach ($CATALOG_ONETIME_COUPONS_ORDER as $ID)
		{
			foreach($_SESSION["CATALOG_USER_COUPONS"] as $k => $v)
			{
				if(trim($v) == $ID)
				{
					unset($_SESSION["CATALOG_USER_COUPONS"][$k]);
					$_SESSION["CATALOG_USER_COUPONS"][$k] == "";
				}
			}
		
			CCatalogDiscountCoupon::Update($ID, $arFieldsCoupon);
		}
		
		$GLOBALS["stackCacheManager"]->Clear("catalog_discount");
	}
}

function CatalogPayOrderCallback($productID, $userID, $bPaid, $orderID)
{
	global $DB;

	$productID = IntVal($productID);
	$userID = IntVal($userID);
	$bPaid = ($bPaid ? True : False);
	$orderID = IntVal($orderID);

	if ($userID <= 0)
		return False;

	$dbIBlockElement = CIBlockElement::GetList(
			array(),
			array(
					"ID" => $productID,
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y"
				)
		);
	if ($arIBlockElement = $dbIBlockElement->GetNext())
	{
		$arCatalog = CCatalog::GetByID($arIBlockElement["IBLOCK_ID"]);
		if ($arCatalog["SUBSCRIPTION"] == "Y")
		{
			$arProduct = CCatalogProduct::GetByID($productID);

			if ($bPaid)
			{
				$arUserGroups = array();
				$arTmp = array();
				$ind = -1;
				$dbProductGroups = CCatalogProductGroups::GetList(
						array(),
						array("PRODUCT_ID" => $productID),
						false,
						false,
						array("GROUP_ID", "ACCESS_LENGTH", "ACCESS_LENGTH_TYPE")
					);
				while ($arProductGroups = $dbProductGroups->Fetch())
				{
					$ind++;
					$curTime = time();

					$accessType = $arProductGroups["ACCESS_LENGTH_TYPE"];
					$accessLength = IntVal($arProductGroups["ACCESS_LENGTH"]);

					$accessVal = 0;
					if ($accessType == "H")
						$accessVal = mktime(date("H") + $accessLength, date("i"), date("s"), date("m"), date("d"), date("Y"));
					elseif ($accessType == "D")
						$accessVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $accessLength, date("Y"));
					elseif ($accessType == "W")
						$accessVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7 * $accessLength, date("Y"));
					elseif ($accessType == "M")
						$accessVal = mktime(date("H"), date("i"), date("s"), date("m") + $accessLength, date("d"), date("Y"));
					elseif ($accessType == "Q")
						$accessVal = mktime(date("H"), date("i"), date("s"), date("m") + 3 * $accessLength, date("d"), date("Y"));
					elseif ($accessType == "S")
						$accessVal = mktime(date("H"), date("i"), date("s"), date("m") + 6 * $accessLength, date("d"), date("Y"));
					elseif ($accessType == "Y")
						$accessVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + $accessLength);
					elseif ($accessType == "T")
						$accessVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + 2 * $accessLength);

					$arUserGroups[$ind] = array(
							"GROUP_ID" => $arProductGroups["GROUP_ID"],
							"DATE_ACTIVE_FROM" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)), $curTime),
							"DATE_ACTIVE_TO" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)), $accessVal)
						);

					$arTmp[IntVal($arProductGroups["GROUP_ID"])] = $ind;
				}

				if (count($arUserGroups) > 0)
				{
					$dbOldGroups = CUser::GetUserGroupEx($userID);
					while ($arOldGroups = $dbOldGroups->Fetch())
					{
						if (array_key_exists(IntVal($arOldGroups["GROUP_ID"]), $arTmp))
						{
							if (strlen($arOldGroups["DATE_ACTIVE_FROM"]) <= 0)
							{
								$arUserGroups[$arTmp[IntVal($arOldGroups["GROUP_ID"])]]["DATE_ACTIVE_FROM"] = false;
							}
							else
							{
								$oldDate = CDatabase::FormatDate($arOldGroups["DATE_ACTIVE_FROM"], CSite::GetDateFormat("SHORT", SITE_ID), "YYYYMMDDHHMISS");
								$newDate = CDatabase::FormatDate($arUserGroups[$arTmp[IntVal($arOldGroups["GROUP_ID"])]]["DATE_ACTIVE_FROM"], CSite::GetDateFormat("SHORT", SITE_ID), "YYYYMMDDHHMISS");
								if ($oldDate > $newDate)
									$arUserGroups[$arTmp[IntVal($arOldGroups["GROUP_ID"])]]["DATE_ACTIVE_FROM"] = $arOldGroups["DATE_ACTIVE_FROM"];
							}

							if (strlen($arOldGroups["DATE_ACTIVE_TO"]) <= 0)
							{
								$arUserGroups[$arTmp[IntVal($arOldGroups["GROUP_ID"])]]["DATE_ACTIVE_TO"] = false;
							}
							else
							{
								$oldDate = CDatabase::FormatDate($arOldGroups["DATE_ACTIVE_TO"], CSite::GetDateFormat("SHORT", SITE_ID), "YYYYMMDDHHMISS");
								$newDate = CDatabase::FormatDate($arUserGroups[$arTmp[IntVal($arOldGroups["GROUP_ID"])]]["DATE_ACTIVE_TO"], CSite::GetDateFormat("SHORT", SITE_ID), "YYYYMMDDHHMISS");
								if ($oldDate > $newDate)
									$arUserGroups[$arTmp[IntVal($arOldGroups["GROUP_ID"])]]["DATE_ACTIVE_TO"] = $arOldGroups["DATE_ACTIVE_TO"];
							}
						}
						else
						{
							$ind++;

							$arUserGroups[$ind] = array(
									"GROUP_ID" => $arOldGroups["GROUP_ID"],
									"DATE_ACTIVE_FROM" => $arOldGroups["DATE_ACTIVE_FROM"],
									"DATE_ACTIVE_TO" => $arOldGroups["DATE_ACTIVE_TO"]
								);
						}
					}

					CUser::SetUserGroup($userID, $arUserGroups);
					if (isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]) && IntVal($GLOBALS["USER"]->GetID()) == $userID)
					{
						$arUserGroupsTmp = array();
						for ($i = 0; $i < count($arUserGroups); $i++)
							$arUserGroupsTmp[] = IntVal($arUserGroups[$i]["GROUP_ID"]);

						$GLOBALS["USER"]->SetUserGroupArray($arUserGroupsTmp);
					}
				}
			}
			else
			{
				$arUserGroups = array();
				$ind = -1;
				$arTmp = array();

				$dbOldGroups = CUser::GetUserGroupEx($userID);
				while ($arOldGroups = $dbOldGroups->Fetch())
				{
					$ind++;
					$arUserGroups[$ind] = array(
							"GROUP_ID" => $arOldGroups["GROUP_ID"],
							"DATE_ACTIVE_FROM" => $arOldGroups["DATE_ACTIVE_FROM"],
							"DATE_ACTIVE_TO" => $arOldGroups["DATE_ACTIVE_FROM"]
						);

					$arTmp[IntVal($arOldGroups["GROUP_ID"])] = $ind;
				}

				$bNeedUpdate = False;
				$dbProductGroups = CCatalogProductGroups::GetList(
						array(),
						array("PRODUCT_ID" => $productID),
						false,
						false,
						array("GROUP_ID")
					);
				while ($arProductGroups = $dbProductGroups->Fetch())
				{
					if (array_key_exists(IntVal($arProductGroups["GROUP_ID"]), $arTmp))
					{
						unset($arUserGroups[IntVal($arProductGroups["GROUP_ID"])]);
						$bNeedUpdate = True;
					}
				}

				if ($bNeedUpdate)
				{
					CUser::SetUserGroup($userID, $arUserGroups);

					if (isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]) && IntVal($GLOBALS["USER"]->GetID()) == $userID)
					{
						$arUserGroupsTmp = array();
						for ($i = 0; $i < count($arUserGroups); $i++)
							$arUserGroupsTmp[] = IntVal($arUserGroups[$i]["GROUP_ID"]);

						$GLOBALS["USER"]->SetUserGroupArray($arUserGroupsTmp);
					}
				}
			}

			if ($arProduct["PRICE_TYPE"] != "S")
			{
				if ($bPaid)
				{
					$recurType = $arProduct["RECUR_SCHEME_TYPE"];
					$recurLength = IntVal($arProduct["RECUR_SCHEME_LENGTH"]);

					$recurSchemeVal = 0;
					if ($recurType == "H")
						$recurSchemeVal = mktime(date("H") + $recurLength, date("i"), date("s"), date("m"), date("d"), date("Y"));
					elseif ($recurType == "D")
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $recurLength, date("Y"));
					elseif ($recurType == "W")
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7 * $recurLength, date("Y"));
					elseif ($recurType == "M")
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + $recurLength, date("d"), date("Y"));
					elseif ($recurType == "Q")
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + 3 * $recurLength, date("d"), date("Y"));
					elseif ($recurType == "S")
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + 6 * $recurLength, date("d"), date("Y"));
					elseif ($recurType == "Y")
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + $recurLength);
					elseif ($recurType == "T")
						$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + 2 * $recurLength);

					$arFields = array(
							"USER_ID" => $userID,
							"MODULE" => "catalog",
							"PRODUCT_ID" => $productID,
							"PRODUCT_NAME" => $arIBlockElement["~NAME"],
							"PRODUCT_URL" => $arIBlockElement["DETAIL_PAGE_URL"],
							"PRODUCT_PRICE_ID" => false,
							"PRICE_TYPE" => $arProduct["PRICE_TYPE"],
							"RECUR_SCHEME_TYPE" => $recurType,
							"RECUR_SCHEME_LENGTH" => $recurLength,
							"WITHOUT_ORDER" => $arProduct["WITHOUT_ORDER"],
							"PRICE" => false,
							"CURRENCY" => false,
							"CANCELED" => "N",
							"CANCELED_REASON" => false,
							"CALLBACK_FUNC" => "CatalogRecurringCallback",
							"DESCRIPTION" => false,
							"PRIOR_DATE" => false,
							"NEXT_DATE" => Date(
									$GLOBALS["DB"]->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)),
									$recurSchemeVal
								)
						);

					return $arFields;
				}
			}
		}

		return True;
	}

	return false;
}

function CatalogRecurringCallback($productID, $userID)
{
	$productID = IntVal($productID);
	if ($productID <= 0)
		return False;

	$userID = IntVal($userID);
	if ($userID <= 0)
		return False;

	$arProduct = CCatalogProduct::GetByID($productID);
	if (!$arProduct)
	{
		$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $productID, GetMessage("I_NO_PRODUCT")), "NO_PRODUCT");
		return False;
	}

	if ($arProduct["PRICE_TYPE"] == "T")
	{
		$arProduct = CCatalogProduct::GetByID($arProduct["TRIAL_PRICE_ID"]);
		if (!$arProduct)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#TRIAL_ID#", $productID, str_replace("#ID#", $arProduct["TRIAL_PRICE_ID"], GetMessage("I_NO_TRIAL_PRODUCT"))), "NO_PRODUCT_TRIAL");
			return False;
		}
	}
	$productID = IntVal($arProduct["ID"]);

	if ($arProduct["PRICE_TYPE"] != "R")
	{
		$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $productID, GetMessage("I_PRODUCT_NOT_SUBSCR")), "NO_IBLOCK_SUBSCR");
		return False;
	}

	$dbIBlockElement = CIBlockElement::GetList(
			array(),
			array(
					"ID" => $productID,
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y"
				)
		);
	$arIBlockElement = $dbIBlockElement->GetNext();

	if (!$arIBlockElement)
	{
		$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $productID, GetMessage("I_NO_IBLOCK_ELEM")), "NO_IBLOCK_ELEMENT");
		return False;
	}

	$arCatalog = CCatalog::GetByID($arIBlockElement["IBLOCK_ID"]);
	if ($arCatalog["SUBSCRIPTION"] != "Y")
	{
		$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arIBlockElement["IBLOCK_ID"], GetMessage("I_CATALOG_NOT_SUBSCR")), "NOT_SUBSCRIPTION");
		return False;
	}

	if ($arProduct["QUANTITY_TRACE"] == "Y" && DoubleVal($arProduct["QUANTITY"]) <= 0)
	{
		$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $productID, GetMessage("I_PRODUCT_SOLD")), "PRODUCT_END");
		return False;
	}

	$arUserGroups = CUser::GetUserGroup($userID);
	$arUserGroups[] = 2;
	$arUserGroups = array_values(array_unique($arUserGroups));

	$arPrice = CCatalogProduct::GetOptimalPrice($productID, 1, $arUserGroups, "Y");
	if (!$arPrice || count($arPrice) <= 0)
	{
		if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($productID, 1, $GLOBALS["USER"]->GetUserGroupArray()))
		{
			$quantity = $nearestQuantity;
			$arPrice = CCatalogProduct::GetOptimalPrice($productID, $quantity, $GLOBALS["USER"]->GetUserGroupArray(), "Y");
		}
	}

	if ($arPrice && is_array($arPrice) && count($arPrice) > 0)
	{
		$currentPrice = $arPrice["PRICE"]["PRICE"];
		$currentDiscount = 0.0;
		
		//SIGURD: logic change. see mantiss 5036.
		// discount applied to a final price with VAT already included.
		if(DoubleVal($arPrice['PRICE']['VAT_RATE']) > 0)
			$currentPrice *= (1 + $arPrice['PRICE']['VAT_RATE']);
		
		if (isset($arPrice["DISCOUNT"]) && count($arPrice["DISCOUNT"]) > 0)
		{
			if ($arPrice["DISCOUNT"]["VALUE_TYPE"]=="F")
			{
				if ($arPrice["DISCOUNT"]["CURRENCY"] == $arPrice["PRICE"]["CURRENCY"])
					$currentDiscount = $arPrice["DISCOUNT"]["VALUE"];
				else
					$currentDiscount = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT"]["VALUE"], $arPrice["DISCOUNT"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
			}
			else
				$currentDiscount = $currentPrice * $arPrice["DISCOUNT"]["VALUE"] / 100.0;

			$currentDiscount = roundEx($currentDiscount, SALE_VALUE_PRECISION);

			if (DoubleVal($arPrice["DISCOUNT"]["MAX_DISCOUNT"]) > 0)
			{
				if ($arPrice["DISCOUNT"]["CURRENCY"] == $baseCurrency)
					$maxDiscount = $arPrice["DISCOUNT"]["MAX_DISCOUNT"];
				else
					$maxDiscount = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT"]["MAX_DISCOUNT"], $arPrice["DISCOUNT"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
				$maxDiscount = roundEx($maxDiscount, CATALOG_VALUE_PRECISION);

				if ($currentDiscount > $maxDiscount)
					$currentDiscount = $maxDiscount;
			}

			$currentPrice = $currentPrice - $currentDiscount;
		}
		
		$recurType = $arProduct["RECUR_SCHEME_TYPE"];
		$recurLength = IntVal($arProduct["RECUR_SCHEME_LENGTH"]);

		$recurSchemeVal = 0;
		if ($recurType == "H")
			$recurSchemeVal = mktime(date("H") + $recurLength, date("i"), date("s"), date("m"), date("d"), date("Y"));
		elseif ($recurType == "D")
			$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $recurLength, date("Y"));
		elseif ($recurType == "W")
			$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7 * $recurLength, date("Y"));
		elseif ($recurType == "M")
			$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + $recurLength, date("d"), date("Y"));
		elseif ($recurType == "Q")
			$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + 3 * $recurLength, date("d"), date("Y"));
		elseif ($recurType == "S")
			$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m") + 6 * $recurLength, date("d"), date("Y"));
		elseif ($recurType == "Y")
			$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + $recurLength);
		elseif ($recurType == "T")
			$recurSchemeVal = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") + 2 * $recurLength);

		$arResult = array(
				"WEIGHT" => $arProduct["WEIGHT"],
				"VAT_RATE" => $arPrice["PRICE"]["VAT_RATE"],
				"QUANTITY" => 1,
				"PRICE" => $currentPrice,
				"WITHOUT_ORDER" => $arProduct["WITHOUT_ORDER"],
				"PRODUCT_ID" => $productID,
				"PRODUCT_NAME" => $arIBlockElement["~NAME"],
				"PRODUCT_URL" => $arIBlockElement["DETAIL_PAGE_URL"],
				"PRODUCT_PRICE_ID" => $arPrice["PRICE"]["ID"],
				"CURRENCY" => $arPrice["PRICE"]["CURRENCY"],
				"NAME" => $arIBlockElement["NAME"],
				"CALLBACK_FUNC" => "CatalogBasketCallback",
				"ORDER_CALLBACK_FUNC" => "CatalogBasketOrderCallback",
				"CANCEL_CALLBACK_FUNC" => "CatalogBasketCancelCallback",
				"PAY_CALLBACK_FUNC" => "CatalogPayOrderCallback",
				"CATALOG_GROUP_NAME" => $arPrice["PRICE"]["CATALOG_GROUP_NAME"],
				"DETAIL_PAGE_URL" => $arIBlockElement["DETAIL_PAGE_URL"],
				"PRICE_TYPE" => $arProduct["PRICE_TYPE"],
				"RECUR_SCHEME_TYPE" => $arProduct["RECUR_SCHEME_TYPE"],
				"RECUR_SCHEME_LENGTH" => $arProduct["RECUR_SCHEME_LENGTH"],
				"PRODUCT_XML_ID" => $arIBlockElement["XML_ID"],
				"NEXT_DATE" => Date(
						$GLOBALS["DB"]->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)),
						$recurSchemeVal
					)
			);

		return $arResult;
	}

	return False;
}


function CatalogBasketCancelCallback($PRODUCT_ID, $QUANTITY, $bCancel)
{
	$PRODUCT_ID = IntVal($PRODUCT_ID);
	$QUANTITY = DoubleVal($QUANTITY);
	$bCancel = ($bCancel ? True : False);

	if ($bCancel)
		CCatalogProduct::QuantityTracer($PRODUCT_ID, -$QUANTITY);
	else
		CCatalogProduct::QuantityTracer($PRODUCT_ID, $QUANTITY);
}


function Add2Basket($PRICE_ID, $QUANTITY = 1, $arRewriteFields=array(), $arProductParams = array())
{
	$PRICE_ID = IntVal($PRICE_ID);
	if ($PRICE_ID<=0) return false;
	$QUANTITY = DoubleVal($QUANTITY);
	if ($QUANTITY<=0) $QUANTITY = 1;
	if (!CModule::IncludeModule("sale"))
		return false;
	if (CModule::IncludeModule("statistic") && IntVal($_SESSION["SESS_SEARCHER_ID"])>0)
		return false;

	$arPrice = CPrice::GetByID($PRICE_ID);
	if ($arPrice===false) return false;

	$arCatalogProduct = CCatalogProduct::GetByID($arPrice["PRODUCT_ID"]);
	if ($arCatalogProduct["QUANTITY_TRACE"]=="Y" && DoubleVal($arCatalogProduct["QUANTITY"])<=0)
		return false;

//	$arProduct = GetIBlockElement($arPrice["PRODUCT_ID"]);
	$dbIBlockElement = CIBlockElement::GetList(
			array(),
			array(
					"ID" => $arPrice["PRODUCT_ID"],
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y"
				)
		);
	$arProduct = $dbIBlockElement->GetNext();

	$arProps = array();

	$dbIBlock = CIBlock::GetList(
			array(),
			array("ID" => $arProduct["IBLOCK_ID"])
		);
	if ($arIBlock = $dbIBlock->Fetch())
	{
		$arProps[] = array(
				"NAME" => "Catalog XML_ID",
				"CODE" => "CATALOG.XML_ID",
				"VALUE" => $arIBlock["XML_ID"]
			);
	}

	$arProps[] = array(
			"NAME" => "Product XML_ID",
			"CODE" => "PRODUCT.XML_ID",
			"VALUE" => $arProduct["XML_ID"]
		);

	$arFields = array(
			"PRODUCT_ID" => $arPrice["PRODUCT_ID"],
			"PRODUCT_PRICE_ID" => $PRICE_ID,
			"PRICE" => $arPrice["PRICE"],
			"CURRENCY" => $arPrice["CURRENCY"],
			"WEIGHT" => $arCatalogProduct["WEIGHT"],
			"QUANTITY" => $QUANTITY,
			"LID" => LANG,
			"DELAY" => "N",
			"CAN_BUY" => "Y",
			"NAME" => $arProduct["~NAME"],
			"CALLBACK_FUNC" => "CatalogBasketCallback",
			"MODULE" => "catalog",
			"NOTES" => $arPrice["CATALOG_GROUP_NAME"],
			"ORDER_CALLBACK_FUNC" => "CatalogBasketOrderCallback",
			"CANCEL_CALLBACK_FUNC" => "CatalogBasketCancelCallback",
			"PAY_CALLBACK_FUNC" => "CatalogPayOrderCallback",
			"DETAIL_PAGE_URL" => $arProduct["DETAIL_PAGE_URL"],
			"CATALOG_XML_ID" => $arIBlock["XML_ID"],
			"PRODUCT_XML_ID" => $arProduct["XML_ID"]
		);

	if ($arCatalogProduct["QUANTITY_TRACE"]=="Y")
	{
		if (DoubleVal($arCatalogProduct["QUANTITY"])-$QUANTITY<0)
			$arFields["QUANTITY"] = DoubleVal($arCatalogProduct["QUANTITY"]);
	}

	if (is_array($arProductParams) && count($arProductParams) > 0)
	{
		for ($i = 0; $i < count($arProductParams); $i++)
		{
			$arProps[] = array(
					"NAME" => $arProductParams[$i]["NAME"],
					"CODE" => $arProductParams[$i]["CODE"],
					"VALUE" => $arProductParams[$i]["VALUE"],
					"SORT" => $arProductParams[$i]["SORT"],
				);
		}
	}
	$arFields["PROPS"] = $arProps;

	if (is_array($arRewriteFields) && count($arRewriteFields)>0)
	{
		while(list($key, $value)=each($arRewriteFields)) $arFields[$key] = $value;
	}
	$addres = CSaleBasket::Add($arFields);

	if (CModule::IncludeModule("statistic"))
		CStatistic::Set_Event("eStore", "add2basket", $arFields["PRODUCT_ID"]);

	return $addres;
}


function Add2BasketByProductID($PRODUCT_ID, $QUANTITY = 1, $arProductParams = array())
{

	$PRODUCT_ID = IntVal($PRODUCT_ID);
	if ($PRODUCT_ID <= 0)
	{
		$GLOBALS["APPLICATION"]->ThrowException("Empty product field", "EMPTY_PRODUCT_ID");
		return false;
	}

	$QUANTITY = DoubleVal($QUANTITY);
	if ($QUANTITY <= 0)
		$QUANTITY = 1;
	

	if (!CModule::IncludeModule("sale"))
	{
		$GLOBALS["APPLICATION"]->ThrowException("Sale module is not installed", "NO_SALE_MODULE");
		return false;
	}

	if (CModule::IncludeModule("statistic") && IntVal($_SESSION["SESS_SEARCHER_ID"])>0)
	{
		$GLOBALS["APPLICATION"]->ThrowException("Searcher can not buy anything", "SESS_SEARCHER");
		return false;
	}
	
	$arProduct = CCatalogProduct::GetByID($PRODUCT_ID);
	if ($arProduct === false)
	{
		$GLOBALS["APPLICATION"]->ThrowException("Product is not found", "NO_PRODUCT");
		return false;
	}
	
	if ($arProduct["QUANTITY_TRACE"]=="Y" && DoubleVal($arProduct["QUANTITY"])<=0)
	{
		$GLOBALS["APPLICATION"]->ThrowException("Product is run out", "PRODUCT_RUN_OUT");
		return false;
	}

	$CALLBACK_FUNC = "CatalogBasketCallback";
	$arCallbackPrice = CSaleBasket::ReReadPrice($CALLBACK_FUNC, "catalog", $PRODUCT_ID, $QUANTITY);
	if (!is_array($arCallbackPrice) || count($arCallbackPrice) <= 0)
	{
		$GLOBALS["APPLICATION"]->ThrowException("Product price is not found", "NO_PRODUCT_PRICE");
		return false;
	}
	
//	$arIBlockElement = GetIBlockElement($PRODUCT_ID);
	$dbIBlockElement = CIBlockElement::GetList(array(), array(
					"ID" => $PRODUCT_ID,
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y",
				), false, false, array(
					"ID",
					"IBLOCK_ID",
					"XML_ID",
					"NAME",
					"DETAIL_PAGE_URL",
	));
	$arIBlockElement = $dbIBlockElement->GetNext();

	if ($arIBlockElement == false)
	{
		$GLOBALS["APPLICATION"]->ThrowException("Infoblock element is not found", "NO_IBLOCK_ELEMENT");
		return false;
	}

	$arProps = array();

	$dbIBlock = CIBlock::GetList(
			array(),
			array("ID" => $arIBlockElement["IBLOCK_ID"])
		);
	if ($arIBlock = $dbIBlock->Fetch())
	{
		$arProps[] = array(
				"NAME" => "Catalog XML_ID",
				"CODE" => "CATALOG.XML_ID",
				"VALUE" => $arIBlock["XML_ID"]
			);
	}

	$arProps[] = array(
			"NAME" => "Product XML_ID",
			"CODE" => "PRODUCT.XML_ID",
			"VALUE" => $arIBlockElement["XML_ID"]
		);

	$arPrice = CPrice::GetByID($arCallbackPrice["PRODUCT_PRICE_ID"]);
	
	
	$arFields = array(
			"PRODUCT_ID" => $PRODUCT_ID,
			"PRODUCT_PRICE_ID" => $arCallbackPrice["PRODUCT_PRICE_ID"],
			"PRICE" => $arCallbackPrice["PRICE"],
			"CURRENCY" => $arCallbackPrice["CURRENCY"],
			"WEIGHT" => $arProduct["WEIGHT"],
			"QUANTITY" => $QUANTITY,
			"LID" => SITE_ID,
			"DELAY" => "N",
			"CAN_BUY" => "Y",
			"NAME" => $arIBlockElement["~NAME"],
			"CALLBACK_FUNC" => $CALLBACK_FUNC,
			"MODULE" => "catalog",
			//"NOTES" => $arProduct["CATALOG_GROUP_NAME"],
			"NOTES" => $arPrice["CATALOG_GROUP_NAME"],
			"ORDER_CALLBACK_FUNC" => "CatalogBasketOrderCallback",
			"CANCEL_CALLBACK_FUNC" => "CatalogBasketCancelCallback",
			"PAY_CALLBACK_FUNC" => "CatalogPayOrderCallback",
			"DETAIL_PAGE_URL" => $arIBlockElement["DETAIL_PAGE_URL"],
			"CATALOG_XML_ID" => $arIBlock["XML_ID"],
			"PRODUCT_XML_ID" => $arIBlockElement["XML_ID"],			
			"VAT_RATE" => $arCallbackPrice['VAT_RATE'],
		);

	if ($arProduct["QUANTITY_TRACE"]=="Y")
	{
		if (IntVal($arProduct["QUANTITY"])-$QUANTITY < 0)
			$arFields["QUANTITY"] = DoubleVal($arProduct["QUANTITY"]);
	}
	
	if (is_array($arProductParams) && count($arProductParams) > 0)
	{
		for ($i = 0; $i < count($arProductParams); $i++)
		{
			$arProps[] = array(
					"NAME" => $arProductParams[$i]["NAME"],
					"CODE" => $arProductParams[$i]["CODE"],
					"VALUE" => $arProductParams[$i]["VALUE"],
					"SORT" => $arProductParams[$i]["SORT"]
				);
		}
	}
	$arFields["PROPS"] = $arProps;
	
	$addres = CSaleBasket::Add($arFields);
	if ($addres)
	{
		if (CModule::IncludeModule("statistic"))
			CStatistic::Set_Event("sale2basket", "catalog", $arFields["DETAIL_PAGE_URL"]);
	}

	return $addres;
}

function CatalogGetPriceTableEx($ID, $filterQauntity = 0, $arFilterType = array(), $VAT_INCLUDE = 'Y')
{
	$ID = IntVal($ID);
	if ($ID <= 0)
		return False;

	$filterQauntity = IntVal($filterQauntity);

	if (!is_array($arFilterType))
		$arFilterType = array($arFilterType);

	$arResult = array();
	$arResult["ROWS"] = array();
	$arResult["COLS"] = array();
	$arResult["MATRIX"] = array();
	$arResult["CAN_BUY"] = array();
	$arResult["AVAILABLE"] = "N";

	$cacheTime = CATALOG_CACHE_DEFAULT_TIME;
	if (defined("CATALOG_CACHE_TIME"))
		$cacheTime = IntVal(CATALOG_CACHE_TIME);

	$arUserGroups = $GLOBALS["USER"]->GetUserGroupArray();

	$arPriceGroups = CCatalogGroup::GetGroupsPerms($arUserGroups, array());

	if (count($arPriceGroups["view"]) <= 0)
		return $arResult;

	$currentQuantity = -1;
	$rowsCnt = -1;

	$arFilter = array("PRODUCT_ID" => $ID);
	if ($filterQauntity > 0)
	{
		$arFilter["+<=QUANTITY_FROM"] = $filterQauntity;
		$arFilter["+>=QUANTITY_TO"] = $filterQauntity;
	}
	if (count($arFilterType) > 0)
	{
		$arTmp = array();
		for ($i = 0; $i < count($arPriceGroups["view"]); $i++)
		{
			if (in_array($arPriceGroups["view"][$i], $arFilterType))
				$arTmp[] = $arPriceGroups["view"][$i];
		}

		if (count($arTmp) <= 0)
			return $arResult;

		$arFilter["CATALOG_GROUP_ID"] = $arTmp;
	}
	else
	{
		$arFilter["CATALOG_GROUP_ID"] = $arPriceGroups["view"];
	}

	$productQuantity = 0;
	$productQuantityTrace = "N";

	$dbRes = CCatalogProduct::GetVATInfo($ID);
	if ($arVatInfo = $dbRes->Fetch())
	{
		$fVatRate = floatval($arVatInfo['RATE'] * 0.01);
		$bVatIncluded = $arVatInfo['VAT_INCLUDED'] == 'Y';
	}
	else
	{
		$fVatRate = 0.00;
		$bVatIncluded = false;
	}
	
	$dbPrice = CPrice::GetListEx(
		array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
		$arFilter,
		false,
		false,
		array("ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO", "PRODUCT_QUANTITY", "PRODUCT_QUANTITY_TRACE", "ELEMENT_IBLOCK_ID")
	);

	while ($arPrice = $dbPrice->Fetch())
	{
		if ($VAT_INCLUDE == 'N')
		{
			if ($bVatIncluded)
				$arPrice['PRICE'] /= (1 + $fVatRate);
		}
		else
		{
			if (!$bVatIncluded)
				$arPrice['PRICE'] *= (1 + $fVatRate);
		}

		$arPrice['VAT_RATE'] = $fVatRate;
		
		$arDiscounts = CCatalogDiscount::GetDiscount($ID, $arPrice["ELEMENT_IBLOCK_ID"], $arPrice["CATALOG_GROUP_ID"], $arUserGroups, "N", SITE_ID);

		$discountPrice = CCatalogProduct::CountPriceWithDiscount($arPrice["PRICE"], $arPrice["CURRENCY"], $arDiscounts);
		$arPrice["DISCOUNT_PRICE"] = $discountPrice;

		$productQuantity = $arPrice["PRODUCT_QUANTITY"];
		$productQuantityTrace = $arPrice["PRODUCT_QUANTITY_TRACE"];

		$arPrice["QUANTITY_FROM"] = DoubleVal($arPrice["QUANTITY_FROM"]);
		if ($currentQuantity != $arPrice["QUANTITY_FROM"])
		{
			$rowsCnt++;
			$arResult["ROWS"][$rowsCnt]["QUANTITY_FROM"] = $arPrice["QUANTITY_FROM"];
			$arResult["ROWS"][$rowsCnt]["QUANTITY_TO"] = DoubleVal($arPrice["QUANTITY_TO"]);
			$currentQuantity = $arPrice["QUANTITY_FROM"];
		}

		$arResult["MATRIX"][IntVal($arPrice["CATALOG_GROUP_ID"])][$rowsCnt] = array(
			"ID" => $arPrice["ID"],
			"PRICE" => $arPrice["PRICE"],
			"DISCOUNT_PRICE" => $arPrice["DISCOUNT_PRICE"],
			"CURRENCY" => $arPrice["CURRENCY"],
			"VAT_RATE" => $arPrice["VAT_RATE"]
		);
	}

	$colsCnt = -1;
	$arCatalogGroups = CCatalogGroup::GetListArray();
	foreach ($arCatalogGroups as $key => $value)
	{
		if (array_key_exists($key, $arResult["MATRIX"]))
			$arResult["COLS"][$value["ID"]] = $value;
	}

	$arResult["CAN_BUY"] = $arPriceGroups["buy"];
	$arResult["AVAILABLE"] = (($productQuantityTrace == "N" || $productQuantityTrace == "Y" && $productQuantity > 0) ? "Y" : "N");

	return $arResult;
}

function CatalogGetPriceTable($ID)
{
	$ID = IntVal($ID);
	if ($ID <= 0)
		return False;

	$arResult = array();

	$arPriceGroups = array();
	$cacheKey = LANGUAGE_ID."_".($GLOBALS["USER"]->GetGroups());
	if (isset($GLOBALS["CATALOG_PRICE_GROUPS_CACHE"])
		&& is_array($GLOBALS["CATALOG_PRICE_GROUPS_CACHE"])
		&& isset($GLOBALS["CATALOG_PRICE_GROUPS_CACHE"][$cacheKey])
		&& is_array($GLOBALS["CATALOG_PRICE_GROUPS_CACHE"][$cacheKey]))
	{
		$arPriceGroups = $GLOBALS["CATALOG_PRICE_GROUPS_CACHE"][$cacheKey];
	}
	else
	{
		$dbPriceGroupsList = CCatalogGroup::GetList(
			array("SORT" => "ASC"),
			array(
				"CAN_ACCESS" => "Y",
				"LID" => LANGUAGE_ID
			),
			array("ID", "NAME_LANG", "SORT"),
			false,
			array("ID", "NAME_LANG", "CAN_BUY", "SORT")
		);
		while ($arPriceGroupsList = $dbPriceGroupsList->Fetch())
		{
			$arPriceGroups[] = $arPriceGroupsList;
			$GLOBALS["CATALOG_PRICE_GROUPS_CACHE"][$cacheKey][] = $arPriceGroupsList;
		}
	}

	if (count($arPriceGroups) <= 0)
		return False;

	$arBorderMap = array();
	$arPresentGroups = array();
	$bMultiQuantity = False;

	$dbPrice = CPrice::GetList(
		array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC", "SORT" => "ASC"),
		array("PRODUCT_ID" => $ID),
		false,
		false,
		array("ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO", "ELEMENT_IBLOCK_ID", "SORT")
	);
	while ($arPrice = $dbPrice->Fetch())
	{
		$arDiscounts = CCatalogDiscount::GetDiscount($ID, $arPrice["ELEMENT_IBLOCK_ID"], $arPrice["CATALOG_GROUP_ID"], $GLOBALS["USER"]->GetUserGroupArray(), "N", SITE_ID);

		$discountPrice = CCatalogProduct::CountPriceWithDiscount($arPrice["PRICE"], $arPrice["CURRENCY"], $arDiscounts);
		$arPrice["DISCOUNT_PRICE"] = $discountPrice;

		if (array_key_exists($arPrice["QUANTITY_FROM"]."-".$arPrice["QUANTITY_TO"], $arBorderMap))
			$jnd = $arBorderMap[$arPrice["QUANTITY_FROM"]."-".$arPrice["QUANTITY_TO"]];
		else
		{
			$jnd = count($arBorderMap);
			$arBorderMap[$arPrice["QUANTITY_FROM"]."-".$arPrice["QUANTITY_TO"]] = $jnd;
		}

		$arResult[$jnd]["QUANTITY_FROM"] = DoubleVal($arPrice["QUANTITY_FROM"]);
		$arResult[$jnd]["QUANTITY_TO"] = DoubleVal($arPrice["QUANTITY_TO"]);
		if (DoubleVal($arPrice["QUANTITY_FROM"]) > 0 || DoubleVal($arPrice["QUANTITY_TO"]) > 0)
			$bMultiQuantity = True;

		$arResult[$jnd]["PRICE"][$arPrice["CATALOG_GROUP_ID"]] = $arPrice;
	}

	$numGroups = count($arPriceGroups);
	for ($i = 0; $i < $numGroups; $i++)
	{
		$bNeedKill = True;
		for ($j = 0; $j < count($arResult); $j++)
		{
			if (!array_key_exists($arPriceGroups[$i]["ID"], $arResult[$j]["PRICE"]))
				$arResult[$j]["PRICE"][$arPriceGroups[$i]["ID"]] = False;

			if ($arResult[$j]["PRICE"][$arPriceGroups[$i]["ID"]] != false)
				$bNeedKill = False;
		}

		if ($bNeedKill)
		{
			for ($j = 0; $j < count($arResult); $j++)
				unset($arResult[$j]["PRICE"][$arPriceGroups[$i]["ID"]]);

			unset($arPriceGroups[$i]);
		}
	}

	return array(
		"COLS" => $arPriceGroups,
		"MATRIX" => $arResult,
		"MULTI_QUANTITY" => ($bMultiQuantity ? "Y" : "N")
	);
}


function __CatalogGetMicroTime()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function __CatalogSetTimeMark($text, $startStop = "")
{
	global $__catalogTimeMarkTo, $__catalogTimeMarkFrom, $__catalogTimeMarkGlobalFrom;

	if (StrToUpper($startStop) == "START")
	{
		$hFile = fopen($_SERVER["DOCUMENT_ROOT"]."/__catalog_debug.txt", "a");
		fwrite($hFile, date("H:i:s")." - ".$text."\n");
		fclose($hFile);

		$__catalogTimeMarkGlobalFrom = __CatalogGetMicroTime();
		$__catalogTimeMarkFrom = __CatalogGetMicroTime();
	}
	elseif (StrToUpper($startStop) == "STOP")
	{
		$__catalogTimeMarkTo = __CatalogGetMicroTime();

		$hFile = fopen($_SERVER["DOCUMENT_ROOT"]."/__catalog_debug.txt", "a");
		fwrite($hFile, date("H:i:s")." - ".Round($__catalogTimeMarkTo - $__catalogTimeMarkFrom, 3)." s - ".$text."\n");
		fwrite($hFile, date("H:i:s")." - ".Round($__catalogTimeMarkTo - $__catalogTimeMarkGlobalFrom, 3)." s\n\n");
		fclose($hFile);
	}
	else
	{
		$__catalogTimeMarkTo = __CatalogGetMicroTime();

		$hFile = fopen($_SERVER["DOCUMENT_ROOT"]."/__catalog_debug.txt", "a");
		fwrite($hFile, date("H:i:s")." - ".Round($__catalogTimeMarkTo - $__catalogTimeMarkFrom, 3)." s - ".$text."\n");
		fclose($hFile);

		$__catalogTimeMarkFrom = __CatalogGetMicroTime();
	}
}

function CatalogGetVATArray($arFilter = array(), $bInsertEmptyLine = false)
{
	$arFilter['ACTIVE'] = 'Y';
	$dbResult = CCatalogVat::GetList(array(), $arFilter, array('ID', 'NAME'));
	
	$arReference = array();
	
	if ($bInsertEmptyLine)
		$arList = array('REFERENCE' => array(0 => GetMessage('CAT_VAT_REF_NOT_SELECTED')), 'REFERENCE_ID' => array(0 => ''));
	else
		$arList = array('REFERENCE' => array(), 'REFERENCE_ID' => array());
	
	$bEmpty = true;
	while ($arRes = $dbResult->Fetch())
	{
		$bEmpty = false;
		$arList['REFERENCE'][] = $arRes['NAME'];
		$arList['REFERENCE_ID'][] = $arRes['ID'];
	}
	
	if ($bEmpty) 
		return false;
	else 
		return $arList;
}

function CurrencyModuleUnInstallCatalog()
{
	$GLOBALS["APPLICATION"]->ThrowException(GetMessage("CAT_INCLUDE_CURRENCY"), "CAT_DEPENDS_CURRENCY");
	return false;
}
?>