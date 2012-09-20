<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", 0, LANGUAGE_ID);
$postProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$postProp[$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("B_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"MESSAGE_COUNT" => Array(
				"NAME" => GetMessage("BB_MESSAGE_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 25,
				"PARENT" => "VISUAL",
			),
		"DATE_TIME_FORMAT" => Array(
				"PARENT" => "VISUAL",
				"NAME" => GetMessage("BC_DATE_TIME_FORMAT"),
				"TYPE" => "LIST",
				"VALUES" => CBlogTools::GetDateTimeFormat(),
				"MULTIPLE" => "N",
				"DEFAULT" => $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL")),	
				"ADDITIONAL_VALUES" => "Y",
			),		
		"PATH_TO_BLOG" => Array(
			"NAME" => GetMessage("BB_PATH_TO_BLOG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_BLOG_CATEGORY" => Array(
			"NAME" => GetMessage("BB_PATH_TO_BLOG_CATEGORY"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_POST" => Array(
			"NAME" => GetMessage("BB_PATH_TO_POST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_POST_EDIT" => Array(
			"NAME" => GetMessage("BB_PATH_TO_POST_EDIT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("BB_PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_SMILE" => Array(
			"NAME" => GetMessage("BB_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"BLOG_VAR" => Array(
			"NAME" => GetMessage("BB_BLOG_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"POST_VAR" => Array(
			"NAME" => GetMessage("BB_POST_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"USER_VAR" => Array(
			"NAME" => GetMessage("BB_USER_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("BB_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"BLOG_URL" => Array(
			"NAME" => GetMessage("BB_BLOG_URL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$blog}",
			"COLS" => 25,
			"PARENT" => "DATA_SOURCE",
		),
		"YEAR" => Array(
			"NAME" => GetMessage("BB_YEAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$year}",
			"COLS" => 25,
			"PARENT" => "BASE",
		),
		"MONTH" => Array(
			"NAME" => GetMessage("BB_MONTH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$month}",
			"COLS" => 25,
			"PARENT" => "BASE",
		),		
		"DAY" => Array(
			"NAME" => GetMessage("BB_DAY"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$day}",
			"COLS" => 25,
			"PARENT" => "BASE",
		),
		"CATEGORY_ID" => Array(
			"NAME" => GetMessage("BB_CATEGORY_ID"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$category}",
			"COLS" => 25,
			"PARENT" => "BASE",
		),
		"CACHE_TIME"	=>	array("DEFAULT"=>"7200"),
		"CACHE_TIME_LONG"	=>	array(
			"NAME" => GetMessage("BB_CACHE_TIME_LONG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "604800",
			"COLS" => 25,
			"PARENT" => "CACHE_SETTINGS",
		),
		"SET_NAV_CHAIN" => Array(
		  	"NAME" => GetMessage("BB_SET_NAV_CHAIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SET_TITLE" =>Array(),
		"FILTER_NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("BB_FILTER_NAME_IN"),
			"TYPE" => "STRING",
			"DEFAULT" => "arFilter",
		),
		"NAV_TEMPLATE" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("BB_NAV_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"POST_PROPERTY_LIST"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("POST_PROPERTY_LIST"),
			"TYPE" => "LIST",
			"VALUES" => $postProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"SHOW_RATING" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("B_SHOW_RATING"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N", 
		),
	)
);
?>