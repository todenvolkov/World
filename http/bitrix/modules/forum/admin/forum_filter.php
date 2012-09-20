<?
/********************************************************************
	Profanity dictionary.
********************************************************************/
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
	$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
	if ($forumModulePermissions == "D")
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	IncludeModuleLangFile(__FILE__);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
	$APPLICATION->SetTitle(GetMessage("FLT_TITLE"));
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$adminPage->ShowSectionIndex("menu_forum", "forum");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>