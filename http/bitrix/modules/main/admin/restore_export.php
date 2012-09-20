<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!$USER->CanDoOperation('edit_php'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$filename = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/restore.php";
if(is_file($filename))
{
	if(CModule::IncludeModule("compression"))
		Ccompress::DisableCompression();

	$file = @fopen($filename, "rb");
	$contents = @fread($file, filesize($filename));
	fclose($file);
	
	$contents = str_replace("%DEFAULT_LANG_ID%", LANG, $contents);

	header("Content-Type: application/octet-stream");
	header("Content-Length: ".(function_exists('mb_strlen')?mb_strlen($contents, 'ISO-8859-1'):strlen($contents)));
	header("Content-Disposition: attachment; filename=\"restore.php\"");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	
	echo $contents;
	die();
}
else
{
	IncludeModuleLangFile(__FILE__);
	$APPLICATION->SetTitle(GetMessage("MAIN_DUMP_EXPORT_ERROR"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	CAdminMessage::ShowMessage(GetMessage("MAIN_DUMP_EXPORT_ERROR_MSG"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
?>
