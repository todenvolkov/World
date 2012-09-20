<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/classes/general/event.php");

class CVoteEvent extends CAllVoteEvent
{
	function err_mess()
	{
		$module_id = "vote";
		return "<br>Module: ".$module_id."<br>Class: CVoteEvent<br>File: ".__FILE__;
	}
}
?>