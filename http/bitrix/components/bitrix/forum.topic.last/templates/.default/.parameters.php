<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arTemplateParameters = array(
    "SHOW_NAV" => array(
        "NAME" => GetMessage("F_SHOW_NAV"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => array(
			"TOP" => GetMessage("F_SHOW_NAV_TOP"),
			"BOTTOM" => GetMessage("F_SHOW_NAV_BOTTOM")),
		"DEFAULT" => array("BOTTOM")),
    "SHOW_COLUMNS" => array(
        "NAME" => GetMessage("F_SHOW_COLUMNS"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => array(
			"USER_START_NAME" => GetMessage("F_SHOW_COLUMNS_USER_START_NAME"),
			"POSTS" => GetMessage("F_SHOW_COLUMNS_POSTS"),
			"VIEWS" => GetMessage("F_SHOW_COLUMNS_VIEWS"),
			"LAST_POST_DATE" => GetMessage("F_SHOW_COLUMNS_LAST_POST_DATE")),
		"DEFAULT" => array("USER_START_NAME", "POSTS", "VIEWS", "LAST_POST_DATE")),
    "SHOW_SORTING" => array(
        "NAME" => GetMessage("F_SHOW_SORTING"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
    "SEPARATE" => array(
        "NAME" => GetMessage("F_SEPARATE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("F_IN_FORUM"))

);
?>