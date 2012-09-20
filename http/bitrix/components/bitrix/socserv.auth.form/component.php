<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if(!is_array($arParams["~SERVICES"]))
	$arParams["~SERVICES"] = array();

if(!is_array($arParams["~POST"]))
	$arParams["~POST"] = array();
	
if($arParams["POPUP"] === "Y" || $arParams["POPUP"] === true)
	$arParams["POPUP"] = true;
else
	$arParams["POPUP"] = false;
	
if(!$USER->IsAuthorized())
{
	$this->IncludeComponentTemplate();
}
?>