<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arServices = Array(
	"main" => Array(
		"NAME" => GetMessage("SERVICE_MAIN_SETTINGS"),
		"STAGES" => Array(
			"files.php", // Copy bitrix files
			"template.php", // Install template
			"theme.php", // Install theme
			"group.php", // Install users and groups
			"settings.php", 
			"post_event.php",
		),
	),
	"forum" => Array(
		"NAME" => GetMessage("SERVICE_FORUM"),
	),
	"iblock" => Array(
		"NAME" => GetMessage("SERVICE_IBLOCK_DEMO_DATA"),
		"STAGES" => Array(
			"types.php", //IBlock types
			"themenews.php",//theme news
			"news.php",//news
			"nationalnews.php",//nationalnews
 			"board.php",
			"vacancy.php",
			"resume.php",
			"links.php",
			"user_photogallery.php",
		),
	),
	"blog" => Array(
		"NAME" => GetMessage("SERVICE_BLOG"),
	),
	"vote" => Array(
		"NAME" => GetMessage("SERVICE_VOTE"),
	),
	"advertising" => Array(
		"NAME" => GetMessage("SERVICE_ADVERTISING"),
	),
);
?>