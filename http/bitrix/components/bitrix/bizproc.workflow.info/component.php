<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('bizproc'))
	return;

if (!function_exists("BPWSInitParam"))
{
	function BPWSInitParam(&$arParams, $name)
	{
		$arParams[$name] = trim($arParams[$name]);
		if ($arParams[$name] <= 0)
			$arParams[$name] = trim($_REQUEST[strtoupper($name)]);
		$nameLower = strtolower($name);
		if ($arParams[$name] <= 0)
			$arParams[$name] = trim($_REQUEST[$nameLower]);
		if ($arParams[$name] <= 0)
		{
			foreach ($_REQUEST as $key => $value)
			{
				if (strtolower($key) == $nameLower)
				{
					$arParams[$name] = trim($_REQUEST[$key]);
					break;
				}
			}
		}
	}
}

BPWSInitParam($arParams, "WORKFLOW_ID");

$arResult["NeedAuth"] = "N";
$arResult["FatalErrorMessage"] = "";
$arResult["ErrorMessage"] = "";

global $USER;
if (!$USER->IsAuthorized())
{
	$arResult["NeedAuth"] = "Y";
}
elseif (strlen($arParams["WORKFLOW_ID"]) <= 0)
{
	$arResult["FatalErrorMessage"] .= "РќРµ СѓРєР°Р·Р°РЅ РєРѕРґ СЂР°Р±РѕС‡РµРіРѕ РїРѕС‚РѕРєР°".". ";
}
else
{
	$arResult["WorkflowState"] = CBPStateService::GetWorkflowState($arParams["WORKFLOW_ID"]);

	if ($arResult["WorkflowState"])
		$arResult["WorkflowTrack"] = CBPTrackingService::DumpWorkflow($arParams["WORKFLOW_ID"]);
	else
		$arResult["FatalErrorMessage"] .= "Р Р°Р±РѕС‡РёР№ РїРѕС‚РѕРє РЅРµ РЅР°Р№РґРµРЅ".". ";
}

$this->IncludeComponentTemplate();
?>