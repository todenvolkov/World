<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPRIA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPRIA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "RequestInformationActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
	),
);
?>