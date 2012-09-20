<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
/********************************************************************
				Input params
********************************************************************/
$arThemesMessages = array(
	"beige" => GetMessage("F_THEME_BEIGE"), 
	"blue" => GetMessage("F_THEME_BLUE"), 
	"fluxbb" => GetMessage("F_THEME_FLUXBB"), 
	"gray" => GetMessage("F_THEME_GRAY"), 
	"green" => GetMessage("F_THEME_GREEN"), 
	"orange" => GetMessage("F_THEME_ORANGE"), 
	"red" => GetMessage("F_THEME_RED"), 
	"white" => GetMessage("F_THEME_WHITE"));
$arThemes = array();
$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[$file] = (!empty($arThemesMessages[$file]) ? $arThemesMessages[$file] : strtoupper(substr($file, 0, 1)).strtolower(substr($file, 1)));
	}
	closedir($directory);
endif;
$hidden = (!is_set($arCurrentValues, "USE_LIGHT_VIEW") || $arCurrentValues["USE_LIGHT_VIEW"] == "Y" ? "Y" : "N");
/********************************************************************
				/Input params
********************************************************************/

$arTemplateParameters = array(
	"THEME" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_THEMES"),
		"TYPE" => "LIST",
		"VALUES" => $arThemes,
		"MULTIPLE" => "N",
		"DEFAULT" => "blue", 
		"ADDITIONAL_VALUES" => "Y"),
    "SHOW_TAGS" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_TAGS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SHOW_AUTH_FORM" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_AUTH"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SHOW_NAVIGATION" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_NAVIGATION"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SHOW_SUBSCRIBE_LINK" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_SUBSCRIBE_LINK"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N", 
		"HIDDEN" => $hidden),
	"SHOW_LEGEND" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_LEGEND"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y", 
		"HIDDEN" => $hidden),
	"SHOW_STATISTIC" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_STATISTIC"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y", 
		"HIDDEN" => $hidden),
    "SHOW_NAME_LINK" => array(
    	"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",	
        "NAME" => GetMessage("F_SHOW_NAME_LINK"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y", 
		"HIDDEN" => $hidden),
    "SHOW_FORUMS" => array(
    	"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_FORUMS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y", 
		"HIDDEN" => $hidden),
	"SHOW_FIRST_POST" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_FIRST_POST"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N", 
		"HIDDEN" => $hidden),
	"SHOW_AUTHOR_COLUMN" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_AUTHOR_COLUMN"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N", 
		"HIDDEN" => $hidden),
	"TMPLT_SHOW_ADDITIONAL_MARKER" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_ADDITIONAL_MARKER"),
		"TYPE" => "STRING",
		"DEFAULT" => ""), 
	"SMILES_COUNT" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SMILES_COUNT"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "100"), 
	"PATH_TO_SMILE" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_DEFAULT_PATH_TO_SMILE"),
		"TYPE" => "STRING",
		"DEFAULT" => "/bitrix/images/forum/smile/", 
		"HIDDEN" => $hidden),
	"PATH_TO_ICON" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_DEFAULT_PATH_TO_ICON"),
		"TYPE" => "STRING",
		"DEFAULT" => "/bitrix/images/forum/icon/", 
		"HIDDEN" => $hidden),
	"PAGE_NAVIGATION_TEMPLATE" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
		"TYPE" => "STRING",
		"DEFAULT" => "forum", 
		"HIDDEN" => $hidden),
	"PAGE_NAVIGATION_WINDOW" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_PAGE_NAVIGATION_WINDOW"),
		"TYPE" => "STRING",
		"DEFAULT" => "5", 
		"HIDDEN" => $hidden), 
	"WORD_WRAP_CUT" => CForumParameters::GetWordWrapCut(false, "TEMPLATE_TEMPLATES_SETTINGS"),
	"WORD_LENGTH" => CForumParameters::GetWordLength(false, "TEMPLATE_TEMPLATES_SETTINGS"),
	"SEO_USER" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SEO_USER"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N", 
		"HIDDEN" => $hidden),
);
$arTemplateParameters["WORD_WRAP_CUT"]["HIDDEN"] = $hidden;
$arTemplateParameters["WORD_LENGTH"]["HIDDEN"] = $hidden;

?>