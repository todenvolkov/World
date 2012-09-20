<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
$bSupportClient = (CTicket::IsSupportClient()) ? "Y" : "N";
$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
if($bAdmin!="Y" && $bSupportTeam!="Y" && $bDemo!="Y" && $bSupportClient!="Y") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(GetMessage("support_index_title"));
if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminPage->ShowSectionIndex("menu_support", "support");

if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
