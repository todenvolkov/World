<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
    "SHOW_TAGS" => array(
        "NAME" => GetMessage("F_SHOW_TAGS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
    "FILES_COUNT" => array(
        "NAME" => GetMessage("F_FILES_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => "2"),
	"SMILES_COUNT" => array(
        "NAME" => GetMessage("F_SMILES_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => "0")
);
if (IsModuleInstalled("vote") && $arCurrentValues["SHOW_VOTE"] == "Y"):
	$arTemplateParameters["VOTE_COUNT_QUESTIONS"] = array(
        "NAME" => GetMessage("F_VOTE_COUNT_QUESTIONS"),
		"TYPE" => "STRING",
		"DEFAULT" => "10");
	$arTemplateParameters["VOTE_COUNT_ANSWERS"] = array(
        "NAME" => GetMessage("F_VOTE_COUNT_ANSWERS"),
		"TYPE" => "STRING",
		"DEFAULT" => "20");
endif;
?>