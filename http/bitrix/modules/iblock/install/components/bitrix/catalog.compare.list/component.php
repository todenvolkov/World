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
unset($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);
$arParams["COMPARE_URL"]=trim($arParams["COMPARE_URL"]);
if(strlen($arParams["COMPARE_URL"])<=0)
	$arParams["COMPARE_URL"] = "compare.php";

$arParams["NAME"]=trim($arParams["NAME"]);
if(strlen($arParams["NAME"])<=0)
	$arParams["NAME"] = "CATALOG_COMPARE_LIST";

/*************************************************************************
			Handling the Compare button
*************************************************************************/
if (!is_array($_SESSION[$arParams["NAME"]]) ||
	!is_array($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]) ||
	!is_array($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]))
{
	$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"] = Array();
}

if($_REQUEST["action"]=="ADD_TO_COMPARE_LIST" && intval($_REQUEST["id"])>0)
{
	if(is_array($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]) &&
		!array_key_exists($_REQUEST["id"], $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]))
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
			$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$_REQUEST["id"]] = $arElement;
		}
	}
}

/*************************************************************************
			Handling the Remove link
*************************************************************************/

if($_REQUEST["action"]=="DELETE_FROM_COMPARE_LIST" && intval($_REQUEST["id"])>0)
{
	unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$_REQUEST["id"]]);
}

$arResult = $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"];
if(is_array($arResult))
{
	foreach($arResult as $id=>$arItem)
	{
		$arResult[$id]["DELETE_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam("action=DELETE_FROM_COMPARE_LIST&id=".$arItem["ID"], array("action", "id")));
	}
	$this->IncludeComponentTemplate();
}

?>
