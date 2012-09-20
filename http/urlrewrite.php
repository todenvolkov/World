<?
$arUrlRewrite = array(
	array(
		"CONDITION"	=>	"#^/catalog/outdoor/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:catalog",
		"PATH"	=>	"/catalog/outdoor/index.php",
	),
	array(
		"CONDITION"	=>	"#^/catalog/pprint/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:catalog",
		"PATH"	=>	"/catalog/pprint/index.php",
	),
	array(
		"CONDITION"	=>	"#^/print/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:iblock.element.add",
		"PATH"	=>	"/print/edit.php",
	),
	array(
		"CONDITION"	=>	"#^/news/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:news",
		"PATH"	=>	"/news/index.php",
	),
);

?>