<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arServices = Array(
	"main" => Array(
		"NAME" => GetMessage("SERVICE_MAIN_SETTINGS"),
		"STAGES" => Array(
			"files.php", // Copy bitrix files
			"template.php", // Install template
			"theme.php", // Install theme
			"menu.php", // Install menu
			"settings.php",
		),
	),

	"iblock" => Array(
		"NAME" => GetMessage("SERVICE_IBLOCK_DEMO_DATA"),
		"STAGES" => Array(
			"types.php", //IBlock types
			"news.php",//news
			"faq.php",
			"catalog.php",
		),
	),
	"sale" => Array(
		"NAME" => GetMessage("SERVICE_SALE_DEMO_DATA"),
		"STAGES" => Array(
			"step1.php", "step2.php",
		),
	),

);
?>