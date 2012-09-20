<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 - 2004 Bitrix           #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/prolog.php");

$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mail/lang/", "/admin/mail_attachment_view.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/include.php");

$strSql = "SELECT * FROM b_mail_msg_attachment WHERE ID=".intval($ID);
$dbr = $DB->Query($strSql);
if($dbr_arr = $dbr->Fetch())
{
	if(strpos($dbr_arr["CONTENT_TYPE"], "html")!==false)
	{
		header("Content-Type: application/force-download; name=\"".$dbr_arr["FILE_NAME"]."\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$dbr_arr["FILE_SIZE"]);
		header("Content-Disposition: attachment; filename=\"".$dbr_arr["FILE_NAME"]."\"");
		header("Expires: 0");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
	}
	else
	{
		header("Content-Disposition: attachment; filename=\"".$dbr_arr["FILE_NAME"]."\"");
		header("Content-type: ".$dbr_arr["CONTENT_TYPE"]);
	}

	echo $dbr_arr["FILE_DATA"];
	die();
}

$APPLICATION->SetTitle(GetMessage("EDIT_MESSAGE_TITLE"));

$ID = IntVal($ID);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("MAIL_ATTACH_BACKLINK"),
		"LINK"=>"mail_message_admin.php?lang=".LANG
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

CAdminMessage::ShowMessage(GetMessage("MAIL_ATTACH_ERROR"));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>