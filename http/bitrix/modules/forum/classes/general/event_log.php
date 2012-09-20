<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__);

class CForumEventLog
{
	function Log($object, $action, $id, $description = "", $title = "")
	{
		if (COption::GetOptionString("forum", "LOGS", "Q") <= "A")
			return false;
		$arTypesTitle = array(
			"FORUM_MESSAGE_APPROVE" => GetMessage("FORUM_MESSAGE_APPROVE"),
			"FORUM_MESSAGE_UNAPPROVE" => GetMessage("FORUM_MESSAGE_UNAPPROVE"),
			"FORUM_MESSAGE_MOVE" => GetMessage("FORUM_MESSAGE_MOVE"),
			"FORUM_MESSAGE_EDIT" => GetMessage("FORUM_MESSAGE_EDIT"),
			"FORUM_MESSAGE_DELETE" => GetMessage("FORUM_MESSAGE_DELETE"),
			"FORUM_MESSAGE_SPAM" => GetMessage("FORUM_MESSAGE_SPAM"),
			
			"FORUM_TOPIC_APPROVE" => GetMessage("FORUM_TOPIC_APPROVE"),
			"FORUM_TOPIC_UNAPPROVE" => GetMessage("FORUM_TOPIC_UNAPPROVE"),
			"FORUM_TOPIC_STICK" => GetMessage("FORUM_TOPIC_STICK"),
			"FORUM_TOPIC_UNSTICK" => GetMessage("FORUM_TOPIC_UNSTICK"),
			"FORUM_TOPIC_OPEN" => GetMessage("FORUM_TOPIC_OPEN"),
			"FORUM_TOPIC_CLOSE" => GetMessage("FORUM_TOPIC_CLOSE"),
			"FORUM_TOPIC_MOVE" => GetMessage("FORUM_TOPIC_MOVE"),
			"FORUM_TOPIC_EDIT" => GetMessage("FORUM_TOPIC_EDIT"),
			"FORUM_TOPIC_DELETE" => GetMessage("FORUM_TOPIC_DELETE"),
			"FORUM_TOPIC_SPAM" => GetMessage("FORUM_TOPIC_SPAM"),
			
			"FORUM_FORUM_EDIT" => GetMessage("FORUM_FORUM_EDIT"),
			"FORUM_FORUM_DELETE" => GetMessage("FORUM_FORUM_DELETE")
		);
		$object = strToUpper($object);
		$action = strToUpper($action);
		$type = "FORUM_".$object."_".$action;
		$title = trim($title);
		if (empty($title))
		{
			$title = $arTypesTitle[$type];
		}
		$description = trim($description);
		
		CEventLog::Log("NOTICE", $type, "forum", $id, $description);
	}

	function GetAuditTypes()
	{
		return array(
			"FORUM_MESSAGE_APPROVE" => "[FORUM_MESSAGE_APPROVE] ".GetMessage("FORUM_MESSAGE_APPROVE"),
			"FORUM_MESSAGE_UNAPPROVE" => "[FORUM_MESSAGE_UNAPPROVE] ".GetMessage("FORUM_MESSAGE_UNAPPROVE"),
			"FORUM_MESSAGE_MOVE" => "[FORUM_MESSAGE_MOVE] ".GetMessage("FORUM_MESSAGE_MOVE"),
			"FORUM_MESSAGE_EDIT" => "[FORUM_MESSAGE_EDIT] ".GetMessage("FORUM_MESSAGE_EDIT"),
			"FORUM_MESSAGE_DELETE" => "[FORUM_MESSAGE_DELETE] ".GetMessage("FORUM_MESSAGE_DELETE"),
			"FORUM_MESSAGE_SPAM" => "[FORUM_MESSAGE_DELETE] ".GetMessage("FORUM_MESSAGE_SPAM"),
			
			"FORUM_TOPIC_APPROVE" => "[FORUM_TOPIC_APPROVE] ".GetMessage("FORUM_TOPIC_APPROVE"),
			"FORUM_TOPIC_UNAPPROVE" => "[FORUM_TOPIC_UNAPPROVE] ".GetMessage("FORUM_TOPIC_UNAPPROVE"),
			"FORUM_TOPIC_STICK" => "[FORUM_TOPIC_STICK] ".GetMessage("FORUM_TOPIC_STICK"),
			"FORUM_TOPIC_UNSTICK" => "[FORUM_TOPIC_UNSTICK] ".GetMessage("FORUM_TOPIC_UNSTICK"),
			"FORUM_TOPIC_OPEN" => "[FORUM_TOPIC_OPEN] ".GetMessage("FORUM_TOPIC_OPEN"),
			"FORUM_TOPIC_CLOSE" => "[FORUM_TOPIC_CLOSE] ".GetMessage("FORUM_TOPIC_CLOSE"),
			"FORUM_TOPIC_MOVE" => "[FORUM_TOPIC_MOVE] ".GetMessage("FORUM_TOPIC_MOVE"),
			"FORUM_TOPIC_EDIT" => "[FORUM_TOPIC_EDIT] ".GetMessage("FORUM_TOPIC_EDIT"),
			"FORUM_TOPIC_DELETE" => "[FORUM_TOPIC_DELETE] ".GetMessage("FORUM_TOPIC_DELETE"),
			"FORUM_TOPIC_SPAM" => "[FORUM_TOPIC_DELETE] ".GetMessage("FORUM_TOPIC_SPAM"),
			
//			"FORUM_FORUM_EDIT" => "[FORUM_FORUM_EDIT] ".GetMessage("FORUM_FORUM_EDIT"),
//			"FORUM_FORUM_DELETE" => "[FORUM_FORUM_DELETE] ".GetMessage("FORUM_FORUM_DELETE")
		);
	}
}
?>