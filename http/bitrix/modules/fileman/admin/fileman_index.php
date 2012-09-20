<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
$res = CSite::GetList($b = 'SORT', $o = 'asc');
if ($res->Fetch() && !$res->Fetch()) // one site
{
	$hide_physical_struc = COption::GetOptionString("fileman", "hide_physical_struc", false);
	if($hide_physical_struc)
	{
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/fileman_admin.php");
		die();
	}
}

$APPLICATION->SetTitle(GetMessage("FILEMAN_TITLE"));
if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminPage->ShowSectionIndex("menu_fileman", "fileman");

if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>