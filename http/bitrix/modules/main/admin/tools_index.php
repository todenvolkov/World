<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("HELP_FILE", "utilities/index.php");

if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("tools_index_title"));
if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminPage->ShowSectionIndex("menu_util", "main");

if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
