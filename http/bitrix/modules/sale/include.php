<?
global $DBType;
//if($GLOBALS['DBType']!='mysql')return false;

include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lang/", "/include.php"));

$GLOBALS["SALE_FIELD_TYPES"] = array(
		"CHECKBOX" => GetMessage("SALE_TYPE_CHECKBOX"),
		"TEXT" => GetMessage("SALE_TYPE_TEXT"),
		"SELECT" => GetMessage("SALE_TYPE_SELECT"),
		"MULTISELECT" => GetMessage("SALE_TYPE_MULTISELECT"),
		"TEXTAREA" => GetMessage("SALE_TYPE_TEXTAREA"),
		"LOCATION" => GetMessage("SALE_TYPE_LOCATION"),
		"RADIO" => GetMessage("SALE_TYPE_RADIO")
	);

if (!CModule::IncludeModule("currency"))
{
//	trigger_error("Currency is not installed");
	return false;
}

/*patchlimitationmutatormark1*/

// Number of processed recurring records at one time
Define("SALE_PROC_REC_NUM", 3);
// Number of recurring payment attempts
Define("SALE_PROC_REC_ATTEMPTS", 3);
// Time between recurring payment attempts (in seconds)
Define("SALE_PROC_REC_TIME", 43200);

Define("SALE_PROC_REC_FREQUENCY", 7200);


global $SALE_TIME_PERIOD_TYPES;
$SALE_TIME_PERIOD_TYPES = array(
		"H" => GetMessage("I_PERIOD_HOUR"),
		"D" => GetMessage("I_PERIOD_DAY"),
		"W" => GetMessage("I_PERIOD_WEEK"),
		"M" => GetMessage("I_PERIOD_MONTH"),
		"Q" => GetMessage("I_PERIOD_QUART"),
		"S" => GetMessage("I_PERIOD_SEMIYEAR"),
		"Y" => GetMessage("I_PERIOD_YEAR")
	);

Define("SALE_VALUE_PRECISION", 2);

$GLOBALS["AVAILABLE_ORDER_FIELDS"] = array(
		"ID" => array("COLUMN_NAME" => "ID", "NAME" => GetMessage("SI_ORDER_ID"), "SELECT" => "ID,DATE_INSERT", "CUSTOM" => "Y", "SORT" => "ID"),
		"LID" => array("COLUMN_NAME" => GetMessage("SI_SITE"), "NAME" => GetMessage("SI_SITE"), "SELECT" => "LID", "CUSTOM" => "N", "SORT" => "LID"),
		"PERSON_TYPE" => array("COLUMN_NAME" => GetMessage("SI_PAYER_TYPE"), "NAME" => GetMessage("SI_PAYER_TYPE"), "SELECT" => "PERSON_TYPE_ID", "CUSTOM" => "Y", "SORT" => "PERSON_TYPE_ID"),
		"PAYED" => array("COLUMN_NAME" => GetMessage("SI_PAID"), "NAME" => GetMessage("SI_PAID_ORDER"), "SELECT" => "PAYED,DATE_PAYED,EMP_PAYED_ID", "CUSTOM" => "Y", "SORT" => "PAYED"),
		"PAY_VOUCHER_NUM" => array("COLUMN_NAME" => GetMessage("SI_NO_PP"), "NAME" => GetMessage("SI_NO_PP_DOC"), "SELECT" => "PAY_VOUCHER_NUM", "CUSTOM" => "N", "SORT" => "PAY_VOUCHER_NUM"),
		"PAY_VOUCHER_DATE" => array("COLUMN_NAME" => GetMessage("SI_DATE_PP"), "NAME" => GetMessage("SI_DATE_PP_DOC"), "SELECT" => "PAY_VOUCHER_DATE", "CUSTOM" => "N", "SORT" => "PAY_VOUCHER_DATE"),
		"DELIVERY_DOC_NUM" => array("COLUMN_NAME" => GetMessage("SI_DATE_PP_DELIVERY_DOC_NUM"), "NAME" => GetMessage("SI_DATE_PP_DOC_DELIVERY_DOC_NUM"), "SELECT" => "DELIVERY_DOC_NUM", "CUSTOM" => "N", "SORT" => "DELIVERY_DOC_NUM"),
		"DELIVERY_DOC_DATE" => array("COLUMN_NAME" => GetMessage("SI_DATE_PP_DELIVERY_DOC_DATE"), "NAME" => GetMessage("SI_DATE_PP_DOC_DELIVERY_DOC_DATE"), "SELECT" => "DELIVERY_DOC_DATE", "CUSTOM" => "N", "SORT" => "DELIVERY_DOC_DATE"),
		"PAYED" => array("COLUMN_NAME" => GetMessage("SI_PAID"), "NAME" => GetMessage("SI_PAID_ORDER"), "SELECT" => "PAYED,DATE_PAYED,EMP_PAYED_ID", "CUSTOM" => "Y", "SORT" => "PAYED"),
		"CANCELED" => array("COLUMN_NAME" => GetMessage("SI_CANCELED"), "NAME" => GetMessage("SI_CANCELED_ORD"), "SELECT" => "CANCELED,DATE_CANCELED,EMP_CANCELED_ID", "CUSTOM" => "Y", "SORT" => "CANCELED"),
		"STATUS" => array("COLUMN_NAME" => GetMessage("SI_STATUS"), "NAME" => GetMessage("SI_STATUS_ORD"), "SELECT" => "STATUS_ID,DATE_STATUS,EMP_STATUS_ID", "CUSTOM" => "Y", "SORT" => "STATUS_ID"),
		"PRICE_DELIVERY" => array("COLUMN_NAME" => GetMessage("SI_DELIVERY"), "NAME" => GetMessage("SI_DELIVERY"), "SELECT" => "PRICE_DELIVERY,CURRENCY", "CUSTOM" => "Y", "SORT" => "PRICE_DELIVERY"),
		"ALLOW_DELIVERY" => array("COLUMN_NAME" => GetMessage("SI_ALLOW_DELIVERY"), "NAME" => GetMessage("SI_ALLOW_DELIVERY1"), "SELECT" => "ALLOW_DELIVERY,DATE_ALLOW_DELIVERY,EMP_ALLOW_DELIVERY_ID", "CUSTOM" => "Y", "SORT" => "ALLOW_DELIVERY"),
		"PRICE" => array("COLUMN_NAME" => GetMessage("SI_SUM"), "NAME" => GetMessage("SI_SUM_ORD"), "SELECT" => "PRICE,CURRENCY", "CUSTOM" => "Y", "SORT" => "PRICE"),
		"SUM_PAID" => array("COLUMN_NAME" => GetMessage("SI_SUM_PAID"), "NAME" => GetMessage("SI_SUM_PAID1"), "SELECT" => "SUM_PAID,CURRENCY", "CUSTOM" => "Y", "SORT" => "SUM_PAID"),
		"USER" => array("COLUMN_NAME" => GetMessage("SI_BUYER"), "NAME" => GetMessage("SI_BUYER"), "SELECT" => "USER_ID", "CUSTOM" => "Y", "SORT" => "USER_ID"),
		"PAY_SYSTEM" => array("COLUMN_NAME" => GetMessage("SI_PAY_SYS"), "NAME" => GetMessage("SI_PAY_SYS"), "SELECT" => "PAY_SYSTEM_ID", "CUSTOM" => "Y", "SORT" => "PAY_SYSTEM_ID"),
		"DELIVERY" => array("COLUMN_NAME" => GetMessage("SI_DELIVERY_SYS"), "NAME" => GetMessage("SI_DELIVERY_SYS"), "SELECT" => "DELIVERY_ID", "CUSTOM" => "Y", "SORT" => "DELIVERY_ID"),
		"DATE_UPDATE" => array("COLUMN_NAME" => GetMessage("SI_DATE_UPDATE"), "NAME" => GetMessage("SI_DATE_UPDATE"), "SELECT" => "DATE_UPDATE", "CUSTOM" => "N", "SORT" => "DATE_UPDATE"),
		"PS_STATUS" => array("COLUMN_NAME" => GetMessage("SI_PAYMENT_PS"), "NAME" => GetMessage("SI_PS_STATUS"), "SELECT" => "PS_STATUS,PS_RESPONSE_DATE", "CUSTOM" => "N", "SORT" => "PS_STATUS"),
		"PS_SUM" => array("COLUMN_NAME" => GetMessage("SI_PS_SUM"), "NAME" => GetMessage("SI_PS_SUM1"), "SELECT" => "PS_SUM,PS_CURRENCY", "CUSTOM" => "Y", "SORT" => "PS_SUM"),
		"TAX_VALUE" => array("COLUMN_NAME" => GetMessage("SI_TAX"), "NAME" => GetMessage("SI_TAX_SUM"), "SELECT" => "TAX_VALUE,CURRENCY", "CUSTOM" => "Y", "SORT" => "TAX_VALUE"),
		"BASKET" => array("COLUMN_NAME" => GetMessage("SI_ITEMS"), "NAME" => GetMessage("SI_ITEMS_ORD"), "SELECT" => "", "CUSTOM" => "Y", "SORT" => "")
	);


CModule::AddAutoloadClasses(
	"sale",
	array(
		"CSaleDelivery" => $DBType."/delivery.php",
		"CSaleDeliveryHandler" => $DBType."/delivery_handler.php",
		"CSaleLocation" => $DBType."/location.php",
		"CSaleLocationGroup" => $DBType."/location_group.php",

		"CSaleBasket" => $DBType."/basket.php",
		"CSaleUser" => $DBType."/basket.php",

		"CSaleOrder" => $DBType."/order.php",
		"CSaleOrderProps" => $DBType."/order_props.php",
		"CSaleOrderPropsGroup" => $DBType."/order_props_group.php",
		"CSaleOrderPropsValue" => $DBType."/order_props_values.php",
		"CSaleOrderPropsVariant" => $DBType."/order_props_variant.php",
		"CSaleOrderUserProps" => $DBType."/order_user_props.php",
		"CSaleOrderUserPropsValue" => $DBType."/order_user_props_value.php",
		"CSaleOrderTax" => $DBType."/order_tax.php",

		"CSalePaySystem" => $DBType."/pay_system.php",
		"CSalePaySystemAction" => $DBType."/pay_system_action.php",

		"CSaleTax" => $DBType."/tax.php",
		"CSaleTaxRate" => $DBType."/tax_rate.php",

		"CSalePersonType" => $DBType."/person_type.php",
		"CSaleDiscount" => $DBType."/discount.php",
		"CSaleUserAccount" => $DBType."/user.php",
		"CSaleUserTransact" => $DBType."/user_transact.php",
		"CSaleUserCards" => $DBType."/user_cards.php",
		"CSaleRecurring" => $DBType."/recurring.php",
		"CSaleStatus" => $DBType."/status.php",

		"CSaleLang" => $DBType."/settings.php",
		"CSaleGroupAccessToSite" => $DBType."/settings.php",
		"CSaleGroupAccessToFlag" => $DBType."/settings.php",

		"CSaleAuxiliary" => $DBType."/auxiliary.php",

		"CSaleAffiliate" => $DBType."/affiliate.php",
		"CSaleAffiliatePlan" => $DBType."/affiliate_plan.php",
		"CSaleAffiliatePlanSection" => $DBType."/affiliate_plan_section.php",
		"CSaleAffiliateTier" => $DBType."/affiliate_tier.php",
		"CSaleAffiliateTransact" => $DBType."/affiliate_transact.php",
		"CSaleExport" => $DBType."/export.php",

		"CSaleMeasure" => "general/measurement.php",
		"CSaleProduct" => $DBType."/product.php",
	)
);

/*
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/delivery.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/location.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/location_group.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/basket.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/order.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/order_props.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/order_props_group.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/order_props_values.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/order_props_variant.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/order_user_props.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/order_user_props_value.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/order_tax.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/pay_system.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/pay_system_action.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/tax.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/tax_rate.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/person_type.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/discount.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/settings.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/user.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/user_transact.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/user_cards.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/recurring.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/status.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/auxiliary.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/affiliate.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/affiliate_plan.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/affiliate_plan_section.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/affiliate_tier.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/".$DBType."/affiliate_transact.php");
*/

//CSaleDeliveryHandler::Initialize();

function GetBasketListSimple($bSkipFUserInit = False)
{
	$fUserID = CSaleBasket::GetBasketUserID($bSkipFUserInit);
	if ($fUserID > 0)
		return CSaleBasket::GetList(
			array("NAME" => "ASC"),
			array("FUSER_ID" => $fUserID, "LID" => SITE_ID, "ORDER_ID" => "NULL")
		);
	else
		return False;
}

function GetBasketList($bSkipFUserInit = False)
{
	$fUserID = CSaleBasket::GetBasketUserID($bSkipFUserInit);
	$arRes = array();
	if ($fUserID > 0)
	{
		$db_res = CSaleBasket::GetList(
			array("NAME" => "ASC"),
			array("FUSER_ID" => $fUserID, "LID" => SITE_ID, "ORDER_ID" => "NULL")
		);
		while ($res = $db_res->GetNext())
		{
			if (strlen($res["CALLBACK_FUNC"]) > 0)
			{
				CSaleBasket::UpdatePrice($res["ID"], $res["CALLBACK_FUNC"], $res["MODULE"], $res["PRODUCT_ID"], $res["QUANTITY"]);
				$res = CSaleBasket::GetByID($res["ID"]);
			}
			$arRes[] = $res;
		}
	}
	return $arRes;
}

function SaleFormatCurrency($fSum, $strCurrency, $OnlyValue = False)
{
	return CurrencyFormat($fSum, $strCurrency);
}

function AutoPayOrder($ORDER_ID)
{
	$ORDER_ID = IntVal($ORDER_ID);

	$arOrder = CSaleOrder::GetByID($ORDER_ID);
	if (!$arOrder)
		return false;
	if ($arOrder["PS_STATUS"] != "Y")
		return false;
	if ($arOrder["PAYED"] != "N")
		return false;

	if ($arOrder["CURRENCY"] == $arOrder["PS_CURRENCY"]
		&& DoubleVal($arOrder["PRICE"]) == DoubleVal($arOrder["PS_SUM"]))
	{
		if (CSaleOrder::PayOrder($order["ID"], "Y", True, False))
			return true;
	}

	return false;
}

function sdeb($varName)
{
	$numArgs = func_num_args();
	if ($numArgs > 1)
		$varVal = func_get_arg(1);
	else
		$varVal = $GLOBALS[$varName];

	if (is_array($varVal))
	{
		echo "<br>".$varName.":<br><pre>";
		print_r($varVal);
		echo "</pre><br>";
	}
	else
	{
		echo $varName."=".$varVal.";<br>";
	}
}

function CurrencyModuleUnInstallSale()
{
	$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SALE_INCLUDE_CURRENCY"), "SALE_DEPENDES_CURRENCY");
	return false;
	
}

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/include.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/include.php");
	
function PayUserAccountDeliveryOrderCallback($productID, $userID, $bPaid, $orderID, $quantity = 1)
{
	global $DB;

	$productID = IntVal($productID);
	$userID = IntVal($userID);
	$bPaid = ($bPaid ? True : False);
	$orderID = IntVal($orderID);

	if ($userID <= 0)
		return False;

	if ($orderID <= 0)
		return False;

	if (!($arOrder = CSaleOrder::GetByID($orderID)))
		return False;

	$baseLangCurrency = CSaleLang::GetLangCurrency($arOrder["LID"]);
	$arAmount = unserialize(COption::GetOptionString("sale", "pay_amount", 'a:4:{i:1;a:2:{s:6:"AMOUNT";s:2:"10";s:8:"CURRENCY";s:3:"EUR";}i:2;a:2:{s:6:"AMOUNT";s:2:"20";s:8:"CURRENCY";s:3:"EUR";}i:3;a:2:{s:6:"AMOUNT";s:2:"30";s:8:"CURRENCY";s:3:"EUR";}i:4;a:2:{s:6:"AMOUNT";s:2:"40";s:8:"CURRENCY";s:3:"EUR";}}'));
	if (!array_key_exists($productID, $arAmount))
		return False;

	$currentPrice = $arAmount[$productID]["AMOUNT"] * $quantity;
	$currentCurrency = $arAmount[$productID]["CURRENCY"];
	if ($arAmount[$productID]["CURRENCY"] != $baseLangCurrency)
	{
		$currentPrice = CCurrencyRates::ConvertCurrency($arAmount[$productID]["AMOUNT"], $arAmount[$productID]["CURRENCY"], $baseLangCurrency) * $quantity;
		$currentCurrency = $baseLangCurrency;
	}

	if (!CSaleUserAccount::UpdateAccount($userID, ($bPaid ? $currentPrice : -$currentPrice), $currentCurrency, "MANUAL", $orderID, "Payment to user account"))
		return False;

	return True;
}

?>