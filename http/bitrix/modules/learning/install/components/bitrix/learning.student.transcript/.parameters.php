<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"TRANSCRIPT_ID" => array(
			"NAME" => GetMessage("LEARNING_TRANSCRIPT_ID_NAME"),
			"TYPE"=>"STRING",
			"DEFAULT"=>'={$_REQUEST["TRANSCRIPT_ID"]}',
			"PARENT" => "BASE",
			"COLS" => 45
		),
		"SET_TITLE" => Array(),
	)
);
?>