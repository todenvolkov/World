<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if(!is_array($arParams["TABS"]))
	$arParams["TABS"] = array();

if($arParams["CAN_EXPAND_TABS"] <> 'N' && $arParams["CAN_EXPAND_TABS"] !== false)
	$arParams["CAN_EXPAND_TABS"] = true;
else
	$arParams["CAN_EXPAND_TABS"] = false;

if($arParams["SHOW_FORM_TAG"] <> 'N' && $arParams["SHOW_FORM_TAG"] !== false)
	$arParams["SHOW_FORM_TAG"] = true;
else
	$arParams["SHOW_FORM_TAG"] = false;

if($arParams["SHOW_SETTINGS"] <> 'N' && $arParams["SHOW_SETTINGS"] !== false)
	$arParams["SHOW_SETTINGS"] = true;
else
	$arParams["SHOW_SETTINGS"] = false;

if($arParams["MAX_FILE_SIZE"] == '')
	$arParams["MAX_FILE_SIZE"] = 102400;

//*********************
//get saved options
//*********************
$aOptions = CUserOptions::GetOption("main.interface.form", $arParams["FORM_ID"], array());

if(!is_array($aOptions["tabs"]))
	$aOptions["tabs"] = array();

if($arParams["THEME_GRID_ID"] <> '')
{
	$aGridOptions = CUserOptions::GetOption("main.interface.grid", $arParams["THEME_GRID_ID"], array());
	if($aGridOptions["theme"] <> '')
		$aOptions["theme"] = $aGridOptions["theme"];
}

$arResult["OPTIONS"] = $aOptions;

$arResult["GLOBAL_OPTIONS"] = CUserOptions::GetOption("main.interface", "global", array(), 0);
if($arResult["GLOBAL_OPTIONS"]["theme_template"][SITE_TEMPLATE_ID] <> '')
	$arResult["GLOBAL_OPTIONS"]["theme"] = $arResult["GLOBAL_OPTIONS"]["theme_template"][SITE_TEMPLATE_ID];

if($arResult["OPTIONS"]["theme"] == '')
	$arResult["OPTIONS"]["theme"] = $arResult["GLOBAL_OPTIONS"]["theme"];

//*********************
// Tabs manipulating
//*********************

$arAllFields = array();
foreach($arParams["TABS"] as $tab)
{
	$arResult["TABS"][$tab["id"]] = $tab;
	$aFields = array();
	if(is_array($tab["fields"]))
		foreach($tab["fields"] as $field)
			$arAllFields[$field["id"]] = $aFields[$field["id"]] = $field;
	$arResult["TABS"][$tab["id"]]["fields"] = $aFields;
}

$arResult["AVAILABLE_FIELDS"] = array();

if(!empty($aOptions["tabs"]) && $arParams["SHOW_SETTINGS"])
{
	$aTabs = array();
	$aUsedFields = array();
	foreach($aOptions["tabs"] as $tab)
	{
		$aTabs[$tab["id"]] = $tab;
		$aTabs[$tab["id"]]["icon"] = $arResult["TABS"][$tab["id"]]["icon"];
		$aFields = array();
		foreach($tab["fields"] as $field)
		{
			if(array_key_exists($field["id"], $arAllFields))
			{
				$aFields[$field["id"]] = $arAllFields[$field["id"]];
				$aFields[$field["id"]]["name"] = $field["name"];
				$aUsedFields[$field["id"]] = true;
			}
			else
			{
				$aFields[$field["id"]] = $field;
				$aFields[$field["id"]]["type"] = "section";
			}
		}
		$aTabs[$tab["id"]]["fields"] = $aFields;
	}
	$arResult["TABS_META"] = $aTabs;

	foreach($arAllFields as $id => $field)
		if(!array_key_exists($id, $aUsedFields))
			$arResult["AVAILABLE_FIELDS"][$id] = array("id"=>$id, "name"=>$field["name"], "type"=>$field["type"]);

	if($arResult["OPTIONS"]["settings_disabled"] <> "Y")
		$arResult["TABS"] = $aTabs;
}
else
{
	$arResult["TABS_META"] = $arResult["TABS"];
}

$hidden = $arParams["FORM_ID"]."_active_tab";
if(isset($_REQUEST[$hidden]) && array_key_exists($_REQUEST[$hidden], $arResult["TABS"]))
{
	$arResult["SELECTED_TAB"] = $_REQUEST[$hidden];
}
else
{
	foreach($arResult["TABS"] as $tab)
	{
		$arResult["SELECTED_TAB"] = $tab["id"];
		break;
	}
}

//*********************
// Self-explaining
//*********************

$this->IncludeComponentTemplate();
?>