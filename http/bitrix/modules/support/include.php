<?
global $DB, $APPLICATION;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/filter_tools.php");
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/errors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/support_tools.php"); 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/".strtolower($DB->type)."/support.php");
?>