  <?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}
/*************************************************************************
	Processing of received parameters
*************************************************************************/
unset($arParams["IBLOCK_TYPE"]); //was used only for IBLOCK_ID setup with Editor
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["NAME"]=trim($arParams["NAME"]);
if(strlen($arParams["NAME"])<=0)
	$arParams["NAME"] = "CATALOG_COMPARE_LIST";
if(strlen($arParams["ELEMENT_SORT_FIELD"])<=0)
	$arParams["ELEMENT_SORT_FIELD"]="sort";
$arParams["ELEMENT_SORT_ORDER"] = strtolower($arParams["ELEMENT_SORT_ORDER"]);
if($arParams["ELEMENT_SORT_ORDER"]!="desc")
	 $arParams["ELEMENT_SORT_ORDER"]="asc";

$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);
$arParams["BASKET_URL"]=trim($arParams["BASKET_URL"]);
if(strlen($arParams["BASKET_URL"])<=0)
	$arParams["BASKET_URL"] = "/personal/basket.php";

$arParams["ACTION_VARIABLE"]=trim($arParams["ACTION_VARIABLE"]);
if(strlen($arParams["ACTION_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"]))
	$arParams["ACTION_VARIABLE"] = "action";
$arParams["PRODUCT_ID_VARIABLE"]=trim($arParams["PRODUCT_ID_VARIABLE"]);
if(strlen($arParams["PRODUCT_ID_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PRODUCT_ID_VARIABLE"]))
	$arParams["PRODUCT_ID_VARIABLE"] = "id";
$arParams["SECTION_ID_VARIABLE"]=trim($arParams["SECTION_ID_VARIABLE"]);
if(strlen($arParams["SECTION_ID_VARIABLE"])<=0|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["SECTION_ID_VARIABLE"]))
	$arParams["SECTION_ID_VARIABLE"] = "SECTION_ID";
if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["PROPERTY_CODE"][$k]);
if(!is_array($arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"] = array();
foreach($arParams["FIELD_CODE"] as $k=>$v)
	if($v==="")
		unset($arParams["FIELD_CODE"][$k]);
if(!in_array("NAME", $arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"][]="NAME";
if(!is_array($arParams["PRICE_CODE"]))
	$arParams["PRICE_CODE"] = array();
$arParams["USE_PRICE_COUNT"] = $arParams["USE_PRICE_COUNT"]=="Y";
$arParams["SHOW_PRICE_COUNT"] = intval($arParams["SHOW_PRICE_COUNT"]);
if($arParams["SHOW_PRICE_COUNT"]<=0)
	$arParams["SHOW_PRICE_COUNT"]=1;
$arParams["DISPLAY_ELEMENT_SELECT_BOX"] = $arParams["DISPLAY_ELEMENT_SELECT_BOX"]=="Y";
if(strlen($arParams["ELEMENT_SORT_FIELD_BOX"])<=0)
	$arParams["ELEMENT_SORT_FIELD_BOX"]="sort";
$arParams["ELEMENT_SORT_ORDER_BOX"] = strtolower($arParams["ELEMENT_SORT_ORDER_BOX"]);
if($arParams["ELEMENT_SORT_ORDER_BOX"]!="desc")
	 $arParams["ELEMENT_SORT_ORDER_BOX"]="asc";

$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";

$arID = array();
if(isset($_REQUEST["ID"]))
{
	$arID = $_REQUEST["ID"];
	if(!is_array($arID))
		$arID = array($arID);
}
$arPR = array();
if(isset($_REQUEST["pr_code"]))
{
	$arPR = $_REQUEST["pr_code"];
	if(!is_array($arPR))
		$arPR = array($arPR);
}

/*************************************************************************
			Handling the Compare button
*************************************************************************/
if(isset($_REQUEST["action"]))
{
	switch($_REQUEST["action"])
	{
		case "ADD_TO_COMPARE_RESULT":
			if(intval($_REQUEST["id"])>0)
			{
				if(!array_key_exists($_REQUEST["id"], $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]))
				{
					//SELECT
					$arSelect = array(
						"ID",
						"IBLOCK_ID",
						"IBLOCK_SECTION_ID",
						"NAME",
						"DETAIL_PAGE_URL",
					);
					//WHERE
					$arFilter = array(
						"ID" => intval($_REQUEST["id"]),
						"IBLOCK_ID" => $arParams["IBLOCK_ID"],
						"IBLOCK_LID" => SITE_ID,
						"IBLOCK_ACTIVE" => "Y",
						"ACTIVE_DATE" => "Y",
						"ACTIVE" => "Y",
						"CHECK_PERMISSIONS" => "Y",
					);
					//ORDER BY
					$arSort = array(
					);
					$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
					$rsElement->SetUrlTemplates($arParams["DETAIL_URL"]);
					if($arElement = $rsElement->GetNext())
					{
						$arElement["DELETE_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam("action=DELETE_FROM_COMPARE_RESULT&id=".$arElement["ID"], array("action", "id")));

						$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$_REQUEST["id"]] = $arElement;
					}
				}
			}
			break;
		case "DELETE_FROM_COMPARE_RESULT":
			if(count($arID)>0)
				foreach($arID as $ID)
					unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$ID]);
			break;
		case "ADD_FEATURE":
			if(count($arPR)>0)
				foreach($arPR as $ID)
					unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"][$ID]);
			break;
		case "DELETE_FEATURE":
			if(count($arPR)>0)
				foreach($arPR as $ID)
					$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"][$ID]=true;
			break;
	}
}

if(!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DIFFERENT"]))
	$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DIFFERENT"] = false;
if(isset($_REQUEST["DIFFERENT"]))
	$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DIFFERENT"] = $_REQUEST["DIFFERENT"]=="Y";
$arResult["DIFFERENT"] = $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DIFFERENT"];

/*************************************************************************
			Processing of the Buy link
*************************************************************************/
$strError = "";
if (array_key_exists($arParams["ACTION_VARIABLE"], $_REQUEST) && array_key_exists($arParams["PRODUCT_ID_VARIABLE"], $_REQUEST))
{
	$action = strtoupper($_REQUEST[$arParams["ACTION_VARIABLE"]]);
	$productID = intval($_REQUEST[$arParams["PRODUCT_ID_VARIABLE"]]);
	if (($action == "COMPARE_ADD2BASKET" || $action == "COMPARE_BUY") && $productID > 0)
	{
		if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
		{
			if (Add2BasketByProductID($productID))
			{
				if ($action == "COMPARE_BUY")
					LocalRedirect($arParams["BASKET_URL"]);
				else
					LocalRedirect($APPLICATION->GetCurPageParam("", array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
			}
			else
			{
				if ($ex = $GLOBALS["APPLICATION"]->GetException())
					$strError = $ex->GetString();
				else
					$strError = GetMessage("CATALOG_ERROR2BASKET").".";
			}
		}
	}
}
if(strlen($strError)>0)
{
	ShowError($strError);
	return;
}

$arCompare = $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"];
if(is_array($arCompare) && count($arCompare)>0)
{
	//$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"] expected to be an array
	if(
		!array_key_exists("DELETE_PROP", $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]])
		|| !is_array($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"])
	)
	{
		$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"] = array();
	}
	//This function returns array with prices description and access rights
	//in case catalog module n/a prices get values from element properties
	$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);

	// list of the element fields that will be used in selection
	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"DETAIL_PAGE_URL",
		"PREVIEW_TEXT_TYPE",
		"DETAIL_TEXT_TYPE",
		"PROPERTY_*",
	);
	$arFilter = array(
		"ID" => array_keys($arCompare),
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_LID" => SITE_ID,
		"IBLOCK_ACTIVE" => "Y",
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
	);
	if(!$arParams["USE_PRICE_COUNT"])
	{
		foreach($arResult["PRICES"] as $key => $value)
		{
			$arSelect[] = $value["SELECT"];
			$arFilter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $arParams["SHOW_PRICE_COUNT"];
		}
	}
	$arSort = array(
		$arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"],
		"ID" => "DESC",
	);
	//EXECUTE
	$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, false, array_merge($arSelect, $arParams["FIELD_CODE"]));
	$rsElements->SetUrlTemplates($arParams["DETAIL_URL"]);
	$arResult["DELETED_PROPERTIES"] = array();
	$arResult["SHOW_PROPERTIES"] = array();
	$arResult["ITEMS"] = array();
	while($obElement = $rsElements->GetNextElement())
	{
		$arItem = $obElement->GetFields();

		$arItem["DETAIL_PICTURE"] = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);
		$arItem["PREVIEW_PICTURE"] = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);

		$arItem["FIELDS"] = array();
		foreach($arParams["FIELD_CODE"] as $code)
			if(array_key_exists($code, $arItem))
				$arItem["FIELDS"][$code] = $arItem[$code];

		if(count($arParams["PROPERTY_CODE"]) > 0)
			$arItem["PROPERTIES"] = $obElement->GetProperties();

		$arItem["DISPLAY_PROPERTIES"] = array();
		foreach($arParams["PROPERTY_CODE"] as $pid)
		{
			if(!array_key_exists($pid, $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"]))
				$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $arItem["PROPERTIES"][$pid], "catalog_out");

			if(array_key_exists($pid, $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["DELETE_PROP"]))
			{
				if(!array_key_exists($pid, $arResult["DELETED_PROPERTIES"]))
				{
					$arResult["DELETED_PROPERTIES"][$pid] = $arItem["PROPERTIES"][$pid];
				}
			}
			else
			{
				if(!array_key_exists($pid, $arResult["SHOW_PROPERTIES"]))
				{
					$arResult["SHOW_PROPERTIES"][$pid] = $arItem["DISPLAY_PROPERTIES"][$pid];
				}
			}
		}

		if($arParams["USE_PRICE_COUNT"])
		{
			if(CModule::IncludeModule("catalog"))
			{
				$arItem["PRICE_MATRIX"] = CatalogGetPriceTableEx($arItem["ID"]);
				foreach($arItem["PRICE_MATRIX"]["COLS"] as $keyColumn=>$arColumn)
					$arItem["PRICE_MATRIX"]["COLS"][$keyColumn]["NAME_LANG"] = htmlspecialchars($arColumn["NAME_LANG"]);
			}
			else
			{
				$arItem["PRICE_MATRIX"] = false;
			}
			$arItem["PRICES"] = array();
		}
		else
		{
			$arItem["PRICE_MATRIX"] = false;
			$arItem["PRICES"] = CIBlockPriceTools::GetItemPrices($arItem["IBLOCK_ID"], $arResult["PRICES"], $arItem, $arParams["PRICE_VAT_INCLUDE"]);
		}

		$arItem["CAN_BUY"] = CIBlockPriceTools::CanBuy($arParams["IBLOCK_ID"], $arResult["PRICES"], $arItem);

		$arItem["BUY_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=COMPARE_BUY&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));
		$arItem["ADD_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam($arParams["ACTION_VARIABLE"]."=COMPARE_ADD2BASKET&".$arParams["PRODUCT_ID_VARIABLE"]."=".$arItem["ID"], array($arParams["PRODUCT_ID_VARIABLE"], $arParams["ACTION_VARIABLE"])));

		$arResult["ITEMS"][]=$arItem;
	}
	$arResult["ITEMS_TO_ADD"] = array();
	if($arParams["DISPLAY_ELEMENT_SELECT_BOX"])
	{
		$arSelect = array(
			"ID",
			"NAME",
		);
		$arFilter = array(
			"!"."ID" => array_keys($arCompare),
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_LID" => SITE_ID,
			"IBLOCK_ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
		);
		$arSort = array(
			$arParams["ELEMENT_SORT_FIELD_BOX"] => $arParams["ELEMENT_SORT_ORDER_BOX"],
			"ID" => "DESC",
		);
		$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
		while($arElement = $rsElements->GetNext())
		{
			$arResult["ITEMS_TO_ADD"][$arElement["ID"]]=$arElement["NAME"];
		}
	}
	//echo "<pre>",htmlspecialchars(print_r($arResult,true)),"</pre>";
	$this->IncludeComponentTemplate();
}
else
{
	ShowNote(GetMessage("CATALOG_COMPARE_LIST_EMPTY"));
}
?>
