<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// Template params
$arParams["TMPLT_SHOW_MENU"] = (in_array($arParams["TMPLT_SHOW_MENU"], array("TOP", "BOTH", "BOTTOM", "NONE")) ? $arParams["TMPLT_SHOW_MENU"] : "BOTH"); 
$arParams["TMPLT_SHOW_AUTH_FORM"] = ($arParams["TMPLT_SHOW_AUTH_FORM"] == "LINK" ? "LINK" : "INPUT");
$arParams["TMPLT_SHOW_ADDITIONAL_MARKER"] = trim($arParams["TMPLT_SHOW_ADDITIONAL_MARKER"]);
$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
$arParams["WORD_WRAP_CUT"] = intVal($arParams["WORD_WRAP_CUT"]);
$arParams["PATH_TO_SMILE"] = (empty($arParams["PATH_TO_SMILE"]) ? "/bitrix/images/forum/smile/" : $arParams["PATH_TO_SMILE"]);
$arParams["PATH_TO_ICON"] = (empty($arParams["PATH_TO_ICON"]) ? "/bitrix/images/forum/icon/" : $arParams["PATH_TO_ICON"]);
$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);



$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/.default/script.js"></script>', true);
$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/lang/".LANGUAGE_ID."/result_modifier.php")));
if (!file_exists($file))
	$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/lang/en/result_modifier.php")));
if(file_exists($file)):
	global $MESS;
	include_once($file);
endif;
?><script type="text/javascript">
//<![CDATA[
	if (phpVars == null || typeof(phpVars) != "object")
	{
		var phpVars = {
			'ADMIN_THEME_ID': '.default',
			'titlePrefix': '<?=CUtil::JSEscape(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))?> - '};
	}
	if (typeof oText != "object")
	{
		var oText = {};
	}
	oText['wait_window'] = '<?=GetMessage("F_LOAD")?>';
//]]>
window.oForumForm = {};
</script>