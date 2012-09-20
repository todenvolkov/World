<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
    "TMPLT_SHOW_AUTH_FORM" => array(
        "NAME" => GetMessage("F_TMPLT_SHOW_AUTH_FORM"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"LINK" => GetMessage("F_LINK"),
			"INPUT" => GetMessage("F_INPUTS"),
			),
		"DEFAULT" => "FORM"),
    "SHOW_ADD_MENU" => array(
		"NAME" => GetMessage("F_SHOW_ADD_MENU"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"),
);
?>