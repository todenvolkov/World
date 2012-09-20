<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arResult["FILE"] <> ''):

	$APPLICATION->SetPageProperty("TEMPLATE_SIDEBAR_MODE", " class=\"sidebar-mode\"");?>

	<div id="sidebar"><?include($arResult["FILE"]);?></div>
<?endif?>
