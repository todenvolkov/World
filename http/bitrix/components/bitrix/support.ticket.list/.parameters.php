<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
	"Y" => GetMessage("SUP_DESC_YES"),
	"N" => GetMessage("SUP_DESC_NO"),
);

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"TICKET_EDIT_TEMPLATE" => Array(
			"NAME" => GetMessage("SUP_LIST_DEFAULT_TEMPLATE_PARAM_1_NAME"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT" => "ticket_edit.php?ID=#ID#",
			"COLS" => 45
		),

		"TICKETS_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_LIST_TICKETS_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "50"
		),

		"SET_PAGE_TITLE" => Array(
			"NAME"=>GetMessage("SUP_SET_PAGE_TITLE"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"N", 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=>"Y", 
			"VALUES"=>$arYesNo, 
			"ADDITIONAL_VALUES"=>"N"
		),

	)
);
?>