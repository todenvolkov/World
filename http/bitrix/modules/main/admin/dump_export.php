<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!$USER->CanDoOperation('edit_php'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

LocalRedirect(BX_PERSONAL_ROOT."/backup/"._normalizePath($f_id));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>