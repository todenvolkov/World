<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("search"))
{
	//ShowError(GetMessage("SEARCH_MODULE_NOT_INSTALL"));
	return;
}

$arParams["PAGE_RESULT_COUNT"] = IntVal($arParams["PAGE_RESULT_COUNT"])>0 ? IntVal($arParams["PAGE_RESULT_COUNT"]): 20;
if(strlen($arParams["SEARCH_PAGE"])<=0)
	$arParams["SEARCH_PAGE"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search";
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["NAV_TEMPLATE"] = (strlen($arParams["NAV_TEMPLATE"])>0 ? $arParams["NAV_TEMPLATE"] : "");

$arResult["~q"] = trim($_REQUEST["q"]);
$arResult["~tags"] = trim($_REQUEST["tags"]);
$arResult["~where"] = trim($_REQUEST["where"]);
$arResult["~how"] = trim($_REQUEST["how"]);
$arResult["q"] = htmlspecialchars($arResult["~q"]);
$arResult["tags"] = htmlspecialchars($arResult["~tags"]);
$arResult["where"] = htmlspecialchars($arResult["~where"]);
$arResult["how"] = htmlspecialchars($arResult["~how"]);

$APPLICATION->SetTitle(GetMessage("LEARNING_MAIN_SEARCH_TITLE"));

$arResult["WHERE"] = Array(
	"C" => GetMessage("LEARNING_MAIN_SEARCH_SEARCH_COURSE"),
	"H" => GetMessage("LEARNING_MAIN_SEARCH_SEARCH_CHAPTER"),
	"L" => GetMessage("LEARNING_MAIN_SEARCH_SEARCH_LESSON"),
);

$arFilter = array(
	"SITE_ID"	=> SITE_ID,
	"QUERY"		=> $arResult["~q"],
	"MODULE_ID"	=> "learning",
	"CHECK_DATES"	=> "Y",
	"TAGS" => $arResult["~tags"],
);
if(strlen($arResult["~where"])>0 && in_array($arResult["~where"], array_keys($arResult["WHERE"]))) {
	$arFilter["%ITEM_ID"]	= $arResult["~where"];
}
if($arResult["~how"]=="d")
	$aSort=array("DATE_CHANGE"=>"DESC", "CUSTOM_RANK"=>"DESC", "RANK"=>"DESC");
else
	$aSort=array("CUSTOM_RANK"=>"DESC", "RANK"=>"DESC", "DATE_CHANGE"=>"DESC");

$arResult["SEARCH_RESULT"] = Array();
if(strlen($arResult["~q"])>0 || strlen($arResult["~tags"])>0)
{
$obSearch = new CSearch();
$obSearch->Search($arFilter, $aSort);
$arResult["SEARCH_RESULT"] = Array();
if($obSearch->errorno==0)
{
	$obSearch->NavStart($arParams["PAGE_RESULT_COUNT"]);
	$arResult["NAV_STRING"] = $obSearch->GetPageNavString(GetMessage("LEARNING_RESULT_PAGES"), $arParams["NAV_TEMPLATE"]);

	while($arSearch = $obSearch->GetNext())
	{
		$arResult["SEARCH_RESULT"][] = $arSearch;
	}

	if(count($arResult["SEARCH_RESULT"])>0)
	{
		if(strlen($arResult["~tags"])>0)
			$arResult["ORDER_LINK"] = $APPLICATION->GetCurPageParam("tags=".urlencode($arResult["tags"])."&where=".urlencode($arResult["where"]), Array("tags", "where", "how"));
		else
			$arResult["ORDER_LINK"] = $APPLICATION->GetCurPageParam("q=".urlencode($arResult["q"])."&where=".urlencode($arResult["where"]), Array("q", "where", "how"));
		if($arResult["~how"]!="d")
			$arResult["ORDER_LINK"] .= "&how=d";
	}
	else
	{
		$arResult["ERROR_MESSAGE"] = GetMessage("LEARNING_MAIN_SEARCH_NOTHING_FOUND");
	}
}
else
	$arResult["ERROR_MESSAGE"] = GetMessage("LEARNING_MAIN_SEARCH_ERROR").$obSearch->error;
}
$this->IncludeComponentTemplate();
?>
