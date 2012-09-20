<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (!function_exists("GetTagsIdTmp"))
{
	function GetTagsIdTmp($sName)
	{
		static $arPostfix = array();
		$sPostfix = rand();
		while (in_array($sPostfix, $arPostfix))
		{
			$sPostfix = rand();
		}
		array_push($arPostfix, $sPostfix);
		$sId = preg_replace("/\W/", "_", $sName);
		$sId = $sId.$sPostfix;
		return $sId;
	}
}

if (empty($arParams["NAME"]))
{
	// counter of this component inclusions on a page
	$GLOBALS["usi_counter"]++;
	$arParams["NAME"] = "TAGS";
	if ($GLOBALS["usi_counter"] > 1)
	{
		$arResult["NAME"] .= "_".$GLOBALS["usi_counter"];
		$arResult["~NAME"] .= "_".$GLOBALS["usi_counter"];
	}	
}

$arResult["ID"] = GetTagsIdTmp($arParams["NAME"]);
$arResult["NAME"] = HtmlSpecialChars($arParams["NAME"]);
$arResult["~NAME"] = $arParams["NAME"];
$arResult["FUNCTION"] = HtmlSpecialChars($arParams["FUNCTION"]);
$arResult["VALUE"] = $arParams["VALUE"];
$arResult["~VALUE"] = $arParams["VALUE"];
$arResult["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);
$arResult["~GROUP_ID"] = $arParams["GROUP_ID"];

if (strlen($arParams["NAME_TEMPLATE"]) <= 0)		
	$arParams["NAME_TEMPLATE"] = '#NOBR##NAME# #LAST_NAME##/NOBR#';
						
if (trim($arParams["SHOW_LOGIN"]) != "N")
	$arParams['SHOW_LOGIN'] = "Y";

$this->IncludeComponentTemplate();
?>