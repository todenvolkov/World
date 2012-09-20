<?
IncludeModuleLangFile(__FILE__); 
/**********************************************************************/
/************** FORUM TOPIC *******************************************/
/**********************************************************************/
class CAllForumTopic
{
	function CanUserViewTopic($TID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$TID = intVal($TID);
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W"):
				return true;
			endif;
			$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups) : $ExternalPermission);
			if ($strPerms >= "Y")
				return true;
			if ($strPerms < "E" || ($strPerms < "Q" && $arTopic["APPROVED"] != "Y"))
				return false;
			$arForum = CForumNew::GetByID($arTopic["FORUM_ID"]);
			return ($arForum["ACTIVE"] == "Y" ? true : false);
		}
		return false;
	}

	function CanUserAddTopic($FID, $arUserGroups, $iUserID = 0, $arForum = false, $ExternalPermission = false)
	{
		if (!$arForum || (!is_array($arForum)) || (intVal($arForum["ID"]) != intVal($FID)))
			$arForum = CForumNew::GetByID($FID);
		if (is_array($arForum) && $arForum["ID"] = $FID)
		{
			if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W"):
				return true;
			endif;
			if (!CForumUser::IsLocked($iUserID)):
				$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($arForum["ID"], $arUserGroups) : $ExternalPermission);
			else:
				$strPerms = CForumNew::GetPermissionUserDefault($arForum["ID"]);
			endif;
			if ($strPerms >= "Y")
				return true;
			if ($strPerms < "M")
				return false;
			return ($arForum["ACTIVE"] == "Y" ? true : false);
		}
		return false;
	}

	function CanUserUpdateTopic($TID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$TID = intVal($TID);
		$iUserID = intVal($iUserID);
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W"):
				return true;
			endif;
			if (!CForumUser::IsLocked($iUserID)):
				$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups) : $ExternalPermission);
			else:
				$strPerms = CForumNew::GetPermissionUserDefault($arTopic["FORUM_ID"]);
			endif;
			if ($strPerms >= "Y")
				return true;
			elseif ($strPerms < "M" || ($strPerms < "Q" && ($arTopic["APPROVED"] != "Y" || $arTopic["STATE"] != "Y"))) 
				return false;
			$arForum = CForumNew::GetByID($arTopic["FORUM_ID"]);
			if ($arForum["ACTIVE"] != "Y")
				return false;
			elseif ($strPerms >= "U")
				return true;
			$db_res = CForumMessage::GetList(array("ID"=>"ASC"), array("TOPIC_ID"=>$TID, "FORUM_ID"=>$arTopic["FORUM_ID"]), False, 2);
			$iCnt = 0; $iOwner = 0;
			if (!($db_res && $res = $db_res->Fetch()))
				return false;
			else
			{
				$iCnt++; $iOwner = intVal($ar_res["AUTHOR_ID"]);
				if ($res = $db_res->Fetch())
					return false;
			}
			if ($iOwner <= 0 || $iUserID <= 0 || $iOwner != $iUserID) 
				return false;
			return true;
		}
		return false;
	}

	function CanUserDeleteTopic($TID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$TID = intVal($TID);
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W"):
				return true;
			endif;
			if (!CForumUser::IsLocked($iUserID)):
				$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups) : $ExternalPermission);
			else:
				$strPerms = CForumNew::GetPermissionUserDefault($arTopic["FORUM_ID"]);
			endif;
			if ($strPerms >= "Y")
				return true;
			elseif ($strPerms < "U")
				return false;
			$arForum = CForumNew::GetByID($arTopic["FORUM_ID"]);
			return ($arForum["ACTIVE"] == "Y" ? true : false);
		}
		return false;
	}

	function CanUserDeleteTopicMessage($TID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$TID = intVal($TID);
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W"):
				return true;
			endif;
			if (!CForumUser::IsLocked($iUserID)):
				$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups) : $ExternalPermission);
			else:
				$strPerms = CForumNew::GetPermissionUserDefault($arTopic["FORUM_ID"]);
			endif;
			if ($strPerms >= "Y") 
				return true;
			elseif ($strPerms < "U")
				return false;
			$arForum = CForumNew::GetByID($arTopic["FORUM_ID"]);
			return ($arForum["ACTIVE"] == "Y" ? true : false);
		}
		return false;
	}

	function CheckFields($ACTION, &$arFields)
	{
		// Fatal Errors
		if (is_set($arFields, "TITLE") || $ACTION=="ADD")
		{
			$arFields["TITLE"] = trim($arFields["TITLE"]);
			if (strLen($arFields["TITLE"]) <= 0)
				return false;
 		}
 		
		if (is_set($arFields, "USER_START_NAME") || $ACTION=="ADD")
		{
			$arFields["USER_START_NAME"] = trim($arFields["USER_START_NAME"]);
			if (strLen($arFields["USER_START_NAME"]) <= 0)
				return false;
		}
		
		if (is_set($arFields, "FORUM_ID") || $ACTION=="ADD")
		{
			$arFields["FORUM_ID"] = intVal($arFields["FORUM_ID"]);
			if ($arFields["FORUM_ID"] <= 0)
				return false;
		}
		if (is_set($arFields, "LAST_POSTER_NAME") || $ACTION=="ADD")
		{
			$arFields["LAST_POSTER_NAME"] = trim($arFields["LAST_POSTER_NAME"]);
			if (strLen($arFields["LAST_POSTER_NAME"]) <= 0 && $arFields["APPROVED"] !== "N" && $arFields["STATE"] !== "L")
				return false;
		}
		if (is_set($arFields, "ABS_LAST_POSTER_NAME") || $ACTION=="ADD")
		{
			$arFields["ABS_LAST_POSTER_NAME"] = trim($arFields["ABS_LAST_POSTER_NAME"]);
			if (strLen($arFields["ABS_LAST_POSTER_NAME"]) <= 0 && $ACTION == "ADD" && !empty($arFields["LAST_POSTER_NAME"]))
				$arFields["ABS_LAST_POSTER_NAME"] = $arFields["LAST_POSTER_NAME"];
			elseif (strLen($arFields["ABS_LAST_POSTER_NAME"]) <= 0 && $arFields["APPROVED"] !== "N" && $arFields["STATE"] !== "L")
				return false;
		}
		
		// Check Data
		if (is_set($arFields, "USER_START_ID") || $ACTION=="ADD")
			$arFields["USER_START_ID"] = (intVal($arFields["USER_START_ID"]) > 0 ? intVal($arFields["USER_START_ID"]) : false);
		if (is_set($arFields, "LAST_POSTER_ID") || $ACTION=="ADD")
			$arFields["LAST_POSTER_ID"] = (intVal($arFields["LAST_POSTER_ID"]) > 0 ? intVal($arFields["LAST_POSTER_ID"]) : false);
		if (is_set($arFields, "LAST_MESSAGE_ID") || $ACTION=="ADD")
			 $arFields["LAST_MESSAGE_ID"] = (intVal($arFields["LAST_MESSAGE_ID"]) > 0 ? intVal($arFields["LAST_MESSAGE_ID"]) : false);
		if (is_set($arFields, "ICON_ID") || $ACTION=="ADD") 
			$arFields["ICON_ID"] = (intVal($arFields["ICON_ID"]) > 0 ? intVal($arFields["ICON_ID"]) : false);
		if (is_set($arFields, "STATE") || $ACTION=="ADD") 
			$arFields["STATE"] = (in_array($arFields["STATE"], array("Y", "N", "L")) ?  $arFields["STATE"] : "Y");
		if (is_set($arFields, "APPROVED") || $ACTION=="ADD") 
			$arFields["APPROVED"] = ($arFields["APPROVED"] == "N" ? "N" : "Y");
		if (is_set($arFields, "SORT") || $ACTION=="ADD")
			$arFields["SORT"] = (intVal($arFields["SORT"]) > 0 ? intVal($arFields["SORT"]) : 150);
		if (is_set($arFields, "VIEWS") || $ACTION=="ADD")
			$arFields["VIEWS"] = (intVal($arFields["VIEWS"]) > 0 ? intVal($arFields["VIEWS"]) : 0); 
		if (is_set($arFields, "POSTS") || $ACTION=="ADD")
			$arFields["POSTS"] = (intVal($arFields["POSTS"]) > 0 ? intVal($arFields["POSTS"]) : 0);
		if (is_set($arFields, "TOPIC_ID")) 
			$arFields["TOPIC_ID"]=intVal($arFields["TOPIC_ID"]);
		if (is_set($arFields, "SOCNET_GROUP_ID") || $ACTION=="ADD")
			$arFields["SOCNET_GROUP_ID"] = (intVal($arFields["SOCNET_GROUP_ID"]) > 0 ? intVal($arFields["SOCNET_GROUP_ID"]) : false);
		if (is_set($arFields, "OWNER_ID") || $ACTION=="ADD")
			$arFields["OWNER_ID"] = (intVal($arFields["OWNER_ID"]) > 0 ? intVal($arFields["OWNER_ID"]) : false);
		return True;
	}

	function Add($arFields)
	{
		global $DB;

		$arFields["VIEWS"] = 0;
		$arFields["POSTS"] = 0;
		$arFields["STATE"] = (in_array($arFields["STATE"], array("Y", "N", "L")) ? $arFields["STATE"] : "Y");
		
		if (!CForumTopic::CheckFields("ADD", $arFields)):
			return false;
		endif;
/***************** Event onBeforeTopicAdd **************************/
		$events = GetModuleEvents("forum", "onBeforeTopicAdd");
		while ($arEvent = $events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;
		}
/***************** /Event ******************************************/
		if (empty($arFields))
			return false;

		foreach (array("START_DATE", "LAST_POST_DATE") as $key)
		{
			if (!is_set($arFields, $key) || empty($arFields[$key])) 
			{
				$arFields[$key] = $DB->GetNowFunction();
			}
			elseif (($DB->type == "MSSQL" && strPos($arFields[$key], "convert (datetime") === false) || 
					($DB->type == "ORACLE" && strPos($arFields[$key], "TO_DATE") === false) ||
					$DB->type == "MYSQL")
			{
				
				$arFields[$key] = $DB->CharToDateFunction(str_replace(array("'", '"'), "", $arFields[$key]));
			}
		}
		
		if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
		{
			$arr = array(
				"TITLE"=>CFilterUnquotableWords::Filter($arFields["TITLE"]), 
				"DESCRIPTION" => CFilterUnquotableWords::Filter($arFields["DESCRIPTION"]),
				"LAST_POSTER_NAME" => CFilterUnquotableWords::Filter($arFields["LAST_POSTER_NAME"]),
				"USER_START_NAME" => CFilterUnquotableWords::Filter($arFields["USER_START_NAME"]),
				"TAGS" => CFilterUnquotableWords::Filter($arFields["TAGS"]));
			
			foreach ($arr as $key => $val):
				if (empty($val) && !empty($arFields[$key])):
					$arr[$key] = "*";
				endif;
			endforeach;
			$arr["ABS_LAST_POSTER_NAME"] = $arr["LAST_POSTER_NAME"];
			$arFields["HTML"] = serialize($arr);
		}
		
		$Fields = array(
			"TITLE" => "'".$DB->ForSQL($arFields["TITLE"], 255)."'",
			"USER_START_NAME" => "'".$DB->ForSQL($arFields["USER_START_NAME"], 255)."'",
			"FORUM_ID" => intVal($arFields["FORUM_ID"]),
			"LAST_POSTER_NAME" => "'".$DB->ForSQL($arFields["LAST_POSTER_NAME"], 255)."'",
			"ABS_LAST_POSTER_NAME" => "'".$DB->ForSQL($arFields["LAST_POSTER_NAME"], 255)."'",
			"TAGS" => "'".$DB->ForSQL($arFields["TAGS"], 255)."'",
			"HTML" => "'".$DB->ForSQL($arFields["HTML"])."'",
			
			"STATE" => "'".$arFields["STATE"]."'",
			"APPROVED" => "'".$arFields["APPROVED"]."'",
			
			"START_DATE" => $arFields["START_DATE"],
			"LAST_POST_DATE" => $arFields["LAST_POST_DATE"],
			"ABS_LAST_POST_DATE" => $arFields["LAST_POST_DATE"],
			
			"SORT" => intVal($arFields["SORT"]),
			"POSTS" => intVal($arFields["POSTS"]),
			"VIEWS" => intVal($arFields["VIEWS"]),
			"TOPIC_ID" => intVal($arFields["TOPIC_ID"]));
		if (strLen(trim($arFields["DESCRIPTION"])) > 0)
			$Fields["DESCRIPTION"] = "'".$DB->ForSQL($arFields["DESCRIPTION"], 255)."'";
		if (strLen(trim($arFields["XML_ID"])) > 0)
			$Fields["XML_ID"] = "'".$DB->ForSQL($arFields["XML_ID"], 255)."'";
		if (intVal($arFields["USER_START_ID"]) > 0)
			$Fields["USER_START_ID"] = intVal($arFields["USER_START_ID"]);
		if (intVal($arFields["ICON_ID"]) > 0)
			$Fields["ICON_ID"] = intVal($arFields["ICON_ID"]);
		if (intVal($arFields["LAST_MESSAGE_ID"]) > 0)
			$Fields["LAST_MESSAGE_ID"] = intVal($arFields["LAST_MESSAGE_ID"]);
		if ($arFields["LAST_POSTER_ID"])
			$Fields["LAST_POSTER_ID"] = intVal($arFields["LAST_POSTER_ID"]);
		if ($arFields["SOCNET_GROUP_ID"])
			$Fields["SOCNET_GROUP_ID"] = intVal($arFields["SOCNET_GROUP_ID"]);
		if ($arFields["OWNER_ID"])
			$Fields["OWNER_ID"] = intVal($arFields["OWNER_ID"]);
		
		$ID = $DB->Insert("b_forum_topic", $Fields, "File: ".__FILE__."<br>Line: ".__LINE__);
/***************** Event onAfterTopicAdd ***************************/
		$events = GetModuleEvents("forum", "onAfterTopicAdd");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
/***************** /Event ******************************************/
		return $ID;
	}
	
	function Update($ID, $arFields, $skip_counts = False)
	{
		global $DB;
		$ID = intVal($ID);
		$arFields1 = array();
		$arFieldsForFilter = array();
		$bNeedFilter = false;

		if ($ID <= 0 || !CForumTopic::CheckFields("UPDATE", $arFields))
			return false;
/***************** Event onBeforeTopicUpdate **************************/
		$events = GetModuleEvents("forum", "onBeforeTopicUpdate");
		while ($arEvent = $events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$ID, &$arFields)) === false)
				return false;
		}
/***************** /Event ******************************************/
		if (empty($arFields))
			return false;
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1)=="=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}
		if (!$skip_counts && is_set($arFields, "FORUM_ID") || 
			COption::GetOptionString("forum", "FILTER", "Y") == "Y" ||
			(is_set($arFields, "TITLE") || is_set($arFields, "TAGS")) && IsModuleInstalled("search"))
		{
			$arTopic_prev = CForumTopic::GetByID($ID, array("NoFilter" => true));
		}
		// Fields "HTML".
		if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
		{
			$arFieldsForFilter = array(
				"TITLE" => (is_set($arFields, "TITLE") ? $arFields["TITLE"] : $arTopic_prev["TITLE"]), 
				"TAGS" => (is_set($arFields, "TAGS") ? $arFields["TAGS"] : $arTopic_prev["TAGS"]), 
				"DESCRIPTION" => (is_set($arFields, "DESCRIPTION") ? $arFields["DESCRIPTION"] : $arTopic_prev["DESCRIPTION"]), 
				"LAST_POSTER_NAME" => (is_set($arFields, "LAST_POSTER_NAME") ? $arFields["LAST_POSTER_NAME"] : $arTopic_prev["LAST_POSTER_NAME"]), 
				"ABS_LAST_POSTER_NAME" => (is_set($arFields, "ABS_LAST_POSTER_NAME") ? $arFields["ABS_LAST_POSTER_NAME"] : $arTopic_prev["ABS_LAST_POSTER_NAME"]), 
				"USER_START_NAME" => (is_set($arFields, "USER_START_NAME") ? $arFields["USER_START_NAME"] : $arTopic_prev["USER_START_NAME"]));

			$bNeedFilter = false;
			foreach ($arFieldsForFilter as $key => $val):
				if (is_set($arFields, $key)):
					$bNeedFilter = true;
					break;
				endif;
			endforeach;
			if ($bNeedFilter)
			{
				foreach ($arFieldsForFilter as $key => $val)
				{
					$res = CFilterUnquotableWords::Filter($val);
					if (empty($res) && !empty($val))
						$res = "*";
					$arFieldsForFilter[$key] = $res;
				}
				$arFields["HTML"] = serialize($arFieldsForFilter);
			}
		}
		
		$strUpdate = $DB->PrepareUpdate("b_forum_topic", $arFields);
		
		foreach ($arFields1 as $key => $value)
		{
			if (strLen($strUpdate)>0) $strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}
		if (!empty($strUpdate))
		{
			$strSql = "UPDATE b_forum_topic SET ".$strUpdate." WHERE ID = ".$ID;
			$DB->QueryBind($strSql, array("HTML"=>$arFields["HTML"]), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
		
		$res = array_merge($arFields1, $arFields);
		if (count($res) == 1 && !empty($res["VIEWS"]))
		{
			if (intVal($res["VIEWS"]) <= 0)
			{
				$GLOBALS["FORUM_CACHE"]["TOPIC"][$ID]["VIEWS"]++;
				$GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID]["VIEWS"]++;
			}
			else
			{
				$GLOBALS["FORUM_CACHE"]["TOPIC"][$ID]["VIEWS"] = intVal($res["VIEWS"]);
				$GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID]["VIEWS"] = intVal($res["VIEWS"]);
			}
		}
		else
		{
			unset($GLOBALS["FORUM_CACHE"]["FORUM"][$arTopic_prev["FORUM_ID"]]);
			unset($GLOBALS["FORUM_CACHE"]["TOPIC"][$ID]);
			unset($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID]);
			if (intVal($arFields1["FORUM_ID"]) > 0)
				unset($GLOBALS["FORUM_CACHE"]["FORUM"][intVal($arFields1["FORUM_ID"])]);
			if (intVal($arFields["FORUM_ID"]) > 0)
				unset($GLOBALS["FORUM_CACHE"]["FORUM"][intVal($arFields["FORUM_ID"])]);
		}
		if (count($res) == 1 && !empty($res["VIEWS"]))
			return $ID;
		if (is_set($arFields, "FORUM_ID") && intVal($arFields["FORUM_ID"]) != intVal($arTopic_prev["FORUM_ID"])):
			$arFiles = array();
			$db_res = CForumFiles::GetList(array(), array("TOPIC_ID" => $ID));
			if ($db_res && $res = $db_res->Fetch()):
				do
				{
					$arFiles[] = $res["ID"];
				} while ($res = $db_res->Fetch());
			endif;
			CForumFiles::UpdateByID($arFiles, array("FORUM_ID" => $arFields["FORUM_ID"]));
		endif;
/***************** Event onAfterTopicUpdate ************************/
		$events = GetModuleEvents("forum", "onAfterTopicUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
/***************** /Event ******************************************/
		// recalc statistic if topic removed from another forum	
		if (!$skip_counts && is_set($arFields, "FORUM_ID") && intVal($arFields["FORUM_ID"]) != intVal($arTopic_prev["FORUM_ID"]))
		{
			$DB->StartTransaction();
				$db_res = CForumMessage::GetList(array(), array("TOPIC_ID" => $ID));
				while ($ar_res = $db_res->Fetch())
				{
					CForumMessage::Update($ar_res["ID"], array("FORUM_ID" => $arFields["FORUM_ID"]), true);
				}
				$db_res = CForumSubscribe::GetList(array(), array("TOPIC_ID" => $ID));
				while ($ar_res = $db_res->Fetch())
				{
					CForumSubscribe::Update($ar_res["ID"], array("FORUM_ID" => $arFields["FORUM_ID"]));
				}
			$DB->Commit();
			CForumNew::SetStat($arFields["FORUM_ID"]);
			CForumNew::SetStat($arTopic_prev["FORUM_ID"]);
		}
		if (IsModuleInstalled("search"))
		{
			$bNeedDeleteIndex = false;
			if (is_set($arFields, "FORUM_ID") && intVal($arFields["FORUM_ID"]) != intVal($arTopic_prev["FORUM_ID"]))
			{
				$res = CForumNew::GetByID($arFields["FORUM_ID"]);
				$bNeedDeleteIndex = ($res["INDEXATION"] != "Y" ? true : false);
			}
			if ($bNeedDeleteIndex)
			{
				CModule::IncludeModule("search");
				CSearch::DeleteIndex("forum", false, $arTopic_prev["FORUM_ID"], $ID);
			}
			elseif (is_set($arFields, "TITLE") || is_set($arFields, "TAGS") || is_set($arFields, "DESCRIPTION"))
			{
				$arReindex = array();
				$arFields["FORUM_ID"] = (is_set($arFields, "FORUM_ID") ? $arFields["FORUM_ID"] : $arTopic_prev["FORUM_ID"]);
				
				if (is_set($arFields, "TITLE") && trim($arTopic_prev["TITLE"]) != trim($arFields["TITLE"])):
					$arReindex["TITLE"] = ($bNeedFilter ? $arFieldsForFilter["TITLE"] : $arFields["TITLE"]);
				endif;
				if (is_set($arFields, "DESCRIPTION") && trim($arTopic_prev["DESCRIPTION"]) != trim($arFields["DESCRIPTION"])):
					$title = (is_set($arReindex, "TITLE") ? $arReindex["TITLE"] : ($bNeedFilter ? $arFieldsForFilter["TITLE"] : $arTopic_prev["TITLE"]));
					$description = ($bNeedFilter ? $arFieldsForFilter["DESCRIPTION"] : $arFields["DESCRIPTION"]);
					$arReindex["TITLE_FOR_FIRST_POST"] = $title.(!empty($description) ? ", ".$description : "");
				endif;
				if (is_set($arFields, "TAGS") && trim($arTopic_prev["TAGS"]) != trim($arFields["TAGS"])):
					$arReindex["TAGS"] = ($bNeedFilter ? $arFieldsForFilter["TAGS"] : $arFields["TAGS"]);
				endif;
				
				if (!empty($arReindex))
				{
					CModule::IncludeModule("search");
					if (is_set($arReindex, "TITLE")):
						CSearch::ChangeIndex("forum", array("TITLE" => $arReindex["TITLE"]), false, $arFields["FORUM_ID"], $ID);
					endif;
					if (is_set($arReindex, "TITLE_FOR_FIRST_POST") || is_set($arReindex, "TAGS")):
						unset($arReindex["TITLE"]);
						if (is_set($arReindex, "TITLE_FOR_FIRST_POST")):
							$arReindex["TITLE"] = $arReindex["TITLE_FOR_FIRST_POST"];
							unset($arReindex["TITLE_FOR_FIRST_POST"]);
						endif;
						$db_res = CForumMessage::GetList(array("ID" => "ASC"), array("TOPIC_ID" => $ID, "NEW_TOPIC" => "Y"));
						if ($db_res && $res = $db_res->Fetch()):
							CSearch::ChangeIndex("forum", $arReindex, $res["ID"], $arFields["FORUM_ID"], $ID);
						endif;
					endif;
				}
			}
		}
		return $ID;
	}

	function MoveTopic2Forum($TID, $FID, $leaveLink = "N")
	{
		global $DB;
		$FID = intVal($FID);
		$arForum = CForumNew::GetByID($FID);
		$arTopics = (is_array($TID) ? $TID : (intVal($TID) > 0 ? array($TID) : array()));
		$leaveLink = (strToUpper($leaveLink) == "Y" ? "Y" : "N");
		$arMsg = array();
		$arForums = array();
		
		if (empty($arForum))
		{
			$arMsg[] = array(
				"id" => "FORUM_NOT_EXIST",
				"text" =>  GetMessage("F_ERR_FORUM_NOT_EXIST", array("#FORUM_ID#" => $FID)));
		}
		if (empty($arTopics))
		{
			$arMsg[] = array(
				"id" => "TOPIC_EMPTY",
				"text" =>  GetMessage("F_ERR_EMPTY_TO_MOVE"));
		}
		
		if (!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		
		$arTopicsCopy = $arTopics;
		$arTopics = array();
		foreach ($arTopicsCopy as $res)
		{
			$arTopics[intVal($res)] = array("ID" => intVal($res));
		}
			
		$db_res = CForumTopic::GetList(array(), array("@ID" => implode(", ", array_keys($arTopics))));
		if ($db_res && ($res = $db_res->Fetch()))
		{
			do 
			{
				if (intVal($res["FORUM_ID"]) == $FID)
				{
					$arMsg[] = array(
						"id" => "FORUM_ID_IDENTICAL", 
						"text" => GetMessage("F_ERR_THIS_TOPIC_IS_NOT_MOVE", 
							array("#TITLE#" => $res["TITLE"], "#ID#" => $res["ID"])));
					continue;
				}
				
//				$DB->StartTransaction();
				
				if ($leaveLink != "N")
				{
					CForumTopic::Add(
						array(
							"TITLE" => $res["TITLE"],
							"DESCRIPTION" => $res["DESCRIPTION"],
							"STATE" => "L",
							"USER_START_NAME" => $res["USER_START_NAME"],
							"START_DATE" => $res["START_DATE"],
							"ICON_ID" => $res["ICON_ID"],
							"POSTS" => "0",
							"VIEWS" => "0",
							"FORUM_ID" => $res["FORUM_ID"],
							"TOPIC_ID" => $res["ID"],
							"APPROVED" => $res["APPROVED"],
							"SORT" => $res["SORT"],
							"LAST_POSTER_NAME" => $res["LAST_POSTER_NAME"],
							"LAST_POST_DATE" => $res["LAST_POST_DATE"],
							"HTML" => $res["HTML"],
							"USER_START_ID" => $res["USER_START_ID"],
							"SOCNET_GROUP_ID" => $res["SOCNET_GROUP_ID"],
							"OWNER_ID" => $res["OWNER_ID"]));
				}
				
				CForumTopic::Update($res["ID"], array("FORUM_ID" => $FID), true);
				// move message
				$strSql = "UPDATE b_forum_message SET FORUM_ID=".$FID.", POST_MESSAGE_HTML='' WHERE TOPIC_ID=".$res["ID"];
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				// move subscribe
				$strSql = "UPDATE b_forum_subscribe SET FORUM_ID=".intVal($FID)." WHERE TOPIC_ID=".$res["ID"];
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				
				$arForums[$res["FORUM_ID"]] = $res["FORUM_ID"];
				unset($GLOBALS["FORUM_CACHE"]["TOPIC"][$res["ID"]]);
				unset($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$res["ID"]]);
				$arTopics[intVal($res["ID"])] = $res;
//				$DB->Commit();
				
				$res_log = str_replace(array("#TOPIC_TITLE#", "#TOPIC_ID#", "#FORUM_TITLE#", "#FORUM_ID#"), 
					array($res["TITLE"], $res["ID"], $arForum["NAME"], $arForum["ID"]), 
					($leaveLink != "N" ? GetMessage("F_LOGS_MOVE_TOPIC_WITH_LINK") : GetMessage("F_LOGS_MOVE_TOPIC")));
				CForumEventLog::Log("topic", "move", $res["ID"], $res_log);
			} while ($res = $db_res->Fetch());
		}
/***************** Cleaning cache **********************************/
		unset($GLOBALS["FORUM_CACHE"]["FORUM"][$FID]);
		if(CACHED_b_forum !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum");
/***************** Cleaning cache/**********************************/
		CForumNew::SetStat($FID);
		foreach ($arForums as $key)
			CForumNew::SetStat($key);
		if (!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
		}
		return true;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = intVal($ID);
		$arTopic = CForumTopic::GetByID($ID);
		if (empty($arTopic)):
			return false;
		endif;
/***************** Event onBeforeTopicDelete ***********************/
		$events = GetModuleEvents("forum", "onBeforeTopicDelete");
		while ($arEvent = $events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$ID, $arTopic)) === false)
				return false;
		}
/***************** /Event ******************************************/
		$arAuthor = array(); $arVotes = array();
		$db_res = CForumMessage::GetList(array("ID" => "ASC"), array("TOPIC_ID" => $ID));
		while ($res = $db_res->Fetch())
		{
			if (intVal($res["AUTHOR_ID"]) > 0)
				$arAuthor[intVal($res["AUTHOR_ID"])] = $res["AUTHOR_ID"];
			if ($res["PARAM1"] == "VT" && intVal($res["PARAM2"]) > 0)
				$arVotes[] = intVal($res["PARAM2"]);
		}
		if (!empty($arVotes) && IsModuleInstalled("vote") && CModule::IncludeModule("vote")):
			foreach ($arVotes as $res) { CVote::Delete($res); }
		endif;

//		$DB->StartTransaction();
			CForumFiles::Delete(array("TOPIC_ID" => $ID), array("DELETE_TOPIC_FILE" => "Y"));
			$DB->Query("DELETE FROM b_forum_subscribe WHERE TOPIC_ID = ".$ID."");
			$DB->Query("DELETE FROM b_forum_message WHERE TOPIC_ID = ".$ID."");
			$DB->Query("DELETE FROM b_forum_user_topic WHERE TOPIC_ID = ".$ID."");
			$DB->Query("DELETE FROM b_forum_topic WHERE ID = ".$ID."");
			$DB->Query("DELETE FROM b_forum_topic WHERE TOPIC_ID = ".$ID."");
			$DB->Query("DELETE FROM b_forum_stat WHERE TOPIC_ID = ".$ID."");
//		$DB->Commit();

		unset($GLOBALS["FORUM_CACHE"]["TOPIC"][$ID]);
		unset($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID]);
		foreach ($arAuthor as $key)
			CForumUser::SetStat($key);
		CForumNew::SetStat($arTopic["FORUM_ID"]);

		if (IsModuleInstalled("search") && CModule::IncludeModule("search"))
		{
			CSearch::DeleteIndex("forum", false, $arTopic["FORUM_ID"], $ID);
		}
/***************** Event onAfterTopicDelete ************************/
		$events = GetModuleEvents("forum", "onAfterTopicDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$ID, $arTopic));
/***************** /Event ******************************************/
		return true;
	}

	function GetByID($ID, $arAddParams = array())
	{
		global $DB;
		$ID = intVal($ID);
		if ($ID <= 0):
			return false;
		endif;		
		
		$NoFilter = ($arAddParams["NoFilter"] == true || COption::GetOptionString("forum", "FILTER", "Y") != "Y" ? true : false);
		
		if ($NoFilter && isset($GLOBALS["FORUM_CACHE"]["TOPIC"][$ID]) && is_array($GLOBALS["FORUM_CACHE"]["TOPIC"][$ID]) && is_set($GLOBALS["FORUM_CACHE"]["TOPIC"][$ID], "ID"))
		{
			return $GLOBALS["FORUM_CACHE"]["TOPIC"][$ID];
		}
		elseif (!$NoFilter && isset($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID]) && is_array($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID]) && is_set($GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID], "ID"))
		{
			return $GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID];
		}
		else
		{
			$strSql = 
				"SELECT FT.*, 
					".$DB->DateToCharFunction("FT.START_DATE", "FULL")." as START_DATE, 
					".$DB->DateToCharFunction("FT.LAST_POST_DATE", "FULL")." as LAST_POST_DATE 
				FROM b_forum_topic FT 
				WHERE FT.ID = ".$ID;
				
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($db_res && $res = $db_res->Fetch())
			{
				$GLOBALS["FORUM_CACHE"]["TOPIC"][$ID] = $res;
				if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
				{
					$db_res_filter = new CDBResult;
					$db_res_filter->InitFromArray(array($res));
					$db_res_filter = new _CTopicDBResult($db_res_filter);
					if ($res_filter = $db_res_filter->Fetch())
						$GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][$ID] = $res_filter;
				}
				if (!$NoFilter)
					$res = $res_filter;
				return $res;
			}
		}
		return False;
	}

	function GetByIDEx($ID, $arAddParams = array())
	{
		global $DB;
		$ID = intVal($ID);
		if ($ID <= 0):
			return false;
		endif;
		
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array($arAddParams));
		$arAddParams["GET_FORUM_INFO"] = ($arAddParams["GET_FORUM_INFO"] == "Y" ? "Y" : "N");
		$arSqlSelect = array();
		$arSqlFrom = array();
		if ($arAddParams["GET_FORUM_INFO"] == "Y")
		{
			$arSqlSelect[] = CForumNew::GetSelectFields(array("sPrefix" => "F_", "sReturnResult" => "string"));
			$arSqlFrom[] =  "INNER JOIN b_forum F ON (FT.FORUM_ID = F.ID)";
		}

		$strSql = 
			"SELECT FT.*, 
				".$DB->DateToCharFunction("FT.START_DATE", "FULL")." as START_DATE, 
				".$DB->DateToCharFunction("FT.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, 
				FS.IMAGE, '' as IMAGE_DESCR".
				(!empty($arSqlSelect) ? ", ".implode(", ", $arSqlSelect) : "")."
			FROM b_forum_topic FT 
				LEFT JOIN b_forum_smile FS ON (FT.ICON_ID = FS.ID) 
				".implode(" ", $arSqlFrom)."
			WHERE FT.ID = ".$ID;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
			$db_res = new _CTopicDBResult($db_res);
		if ($res = $db_res->Fetch())
		{
			if (is_array($res))
			{
				// Cache topic data for hits
				if ($arAddParams["GET_FORUM_INFO"] == "Y")
				{
					$res["TOPIC_INFO"] = array();
					$res["FORUM_INFO"] = array();
					foreach ($res as $key => $val)
					{
						if (substr($key, 0, 2) == "F_")
							$res["FORUM_INFO"][substr($key, 2)] = $val;
						else 
							$res["TOPIC_INFO"][$key] = $val;
					}
					if (!empty($res["TOPIC_INFO"]))
					{
						$GLOBALS["FORUM_CACHE"]["TOPIC"][intVal($res["TOPIC_INFO"]["ID"])] = $res["TOPIC_INFO"];
						if (COption::GetOptionString("forum", "FILTER", "Y") == "Y")
						{
							$db_res_filter = new CDBResult;
							$db_res_filter->InitFromArray(array($res["TOPIC_INFO"]));
							$db_res_filter = new _CTopicDBResult($db_res_filter);
							if ($res_filter = $db_res_filter->Fetch())
								$GLOBALS["FORUM_CACHE"]["TOPIC_FILTER"][intVal($res["TOPIC_INFO"]["ID"])] = $res_filter;
						}
					}
					if (!empty($res["FORUM_INFO"]))
					{
						$GLOBALS["FORUM_CACHE"]["FORUM"][intVal($res["FORUM_INFO"]["ID"])] = $res["FORUM_INFO"];
					}
				}
			}
			return $res;
		}
		return false;
	}

	function GetNeighboringTopics($TID, $arUserGroups) // out-of-date function
	{
		$TID = intVal($TID);
		$arTopic = CForumTopic::GetByID($TID);
		if (!$arTopic) return False;

		//-- PREV_TOPIC
		$arFilter = array(
			"FORUM_ID" => $arTopic["FORUM_ID"],
			"<LAST_POST_DATE" => $arTopic["LAST_POST_DATE"]
			);
		if (CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups)<"Q")
			$arFilter["APPROVED"] = "Y";

		$db_res = CForumTopic::GetList(array("LAST_POST_DATE"=>"DESC"), $arFilter, false, 1);
		$PREV_TOPIC = 0;
		if ($ar_res = $db_res->Fetch()) $PREV_TOPIC = $ar_res["ID"];

		//-- NEXT_TOPIC
		$arFilter = array(
			"FORUM_ID" => $arTopic["FORUM_ID"],
			">LAST_POST_DATE" => $arTopic["LAST_POST_DATE"]
			);
		if (CForumNew::GetUserPermission($arTopic["FORUM_ID"], $arUserGroups)<"Q")
			$arFilter["APPROVED"] = "Y";

		$db_res = CForumTopic::GetList(array("LAST_POST_DATE"=>"ASC"), $arFilter, false, 1);
		$NEXT_TOPIC = 0;
		if ($ar_res = $db_res->Fetch()) $NEXT_TOPIC = $ar_res["ID"];

		return array($PREV_TOPIC, $NEXT_TOPIC);
	}
	
	function GetSelectFields($arAddParams = array())
	{
		global $DB;
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array());
		$arAddParams["sPrefix"] = $DB->ForSql(empty($arAddParams["sPrefix"]) ? "FT." : $arAddParams["sPrefix"]);
		$arAddParams["sTablePrefix"] = $DB->ForSql(empty($arAddParams["sTablePrefix"]) ? "FT." : $arAddParams["sTablePrefix"]);
		$arAddParams["sReturnResult"] = ($arAddParams["sReturnResult"] == "string" ? "string" : "array");
		
		$res = array(
			$arAddParams["sPrefix"]."ID" => $arAddParams["sTablePrefix"]."ID",
			$arAddParams["sPrefix"]."TITLE" => $arAddParams["sTablePrefix"]."TITLE",
			$arAddParams["sPrefix"]."TAGS" => $arAddParams["sTablePrefix"]."TAGS",
			$arAddParams["sPrefix"]."DESCRIPTION" => $arAddParams["sTablePrefix"]."DESCRIPTION",
			$arAddParams["sPrefix"]."VIEWS" => $arAddParams["sTablePrefix"]."VIEWS",
			$arAddParams["sPrefix"]."LAST_POSTER_ID" => $arAddParams["sTablePrefix"]."LAST_POSTER_ID",
			($arAddParams["sPrefix"] == $arAddParams["sTablePrefix"] ? "" : $arAddParams["sPrefix"]).
				"START_DATE" => $DB->DateToCharFunction($arAddParams["sTablePrefix"]."START_DATE", "FULL"),
			$arAddParams["sPrefix"]."USER_START_NAME" => $arAddParams["sTablePrefix"]."USER_START_NAME",
			$arAddParams["sPrefix"]."USER_START_ID" => $arAddParams["sTablePrefix"]."USER_START_ID",
			$arAddParams["sPrefix"]."POSTS" => $arAddParams["sTablePrefix"]."POSTS",
			$arAddParams["sPrefix"]."LAST_POSTER_NAME" => $arAddParams["sTablePrefix"]."LAST_POSTER_NAME",
			($arAddParams["sPrefix"] == $arAddParams["sTablePrefix"] ? "" : $arAddParams["sPrefix"]).
				"LAST_POST_DATE" => $DB->DateToCharFunction($arAddParams["sTablePrefix"]."LAST_POST_DATE", "FULL"),
			$arAddParams["sPrefix"]."LAST_MESSAGE_ID" => $arAddParams["sTablePrefix"]."LAST_MESSAGE_ID",
			$arAddParams["sPrefix"]."APPROVED" => $arAddParams["sTablePrefix"]."APPROVED",
			$arAddParams["sPrefix"]."STATE" => $arAddParams["sTablePrefix"]."STATE",
			$arAddParams["sPrefix"]."FORUM_ID" => $arAddParams["sTablePrefix"]."FORUM_ID",
			$arAddParams["sPrefix"]."TOPIC_ID" => $arAddParams["sTablePrefix"]."TOPIC_ID",
			$arAddParams["sPrefix"]."ICON_ID" => $arAddParams["sTablePrefix"]."ICON_ID",
			$arAddParams["sPrefix"]."SORT" => $arAddParams["sTablePrefix"]."SORT",
			$arAddParams["sPrefix"]."SOCNET_GROUP_ID" => $arAddParams["sTablePrefix"]."SOCNET_GROUP_ID",
			$arAddParams["sPrefix"]."OWNER_ID" => $arAddParams["sTablePrefix"]."OWNER_ID");


		if ($arAddParams["sReturnResult"] == "string")
		{
			$arRes = array();
			foreach ($res as $key => $val)
			{
				$arRes[] = $val.($key != $val ? " AS ".$key : "");
			}
			$res = implode(", ", $arRes);
		}
		return $res;
	}

	function SetReadLabels($ID, $arUserGroups) // out-of-date function
	{
		$ID = intVal($ID);
		$arTopic = CForumTopic::GetByID($ID);
		if ($arTopic)
		{
			$FID = intVal($arTopic["FORUM_ID"]);
			if (is_null($_SESSION["read_forum_".$FID]) || strLen($_SESSION["read_forum_".$FID])<=0)
			{
				$_SESSION["read_forum_".$FID] = "0";
			}
			
			$_SESSION["first_read_forum_".$FID] = intVal($_SESSION["first_read_forum_".$FID]);

			$arFilter = array(
				"FORUM_ID" => $FID,
				"TOPIC_ID" => $ID
				);
			if (intVal($_SESSION["first_read_forum_".$FID])>0)
				$arFilter[">ID"] = intVal($_SESSION["first_read_forum_".$FID]);
			if ($_SESSION["read_forum_".$FID]!="0")
				$arFilter["!@ID"] = $_SESSION["read_forum_".$FID];
			if (CForumNew::GetUserPermission($FID, $arUserGroups)<"Q")
				$arFilter["APPROVED"] = "Y";
			$db_res = CForumMessage::GetList(array(), $arFilter);
			if ($db_res)
			{
				while ($ar_res = $db_res->Fetch())
				{
					$_SESSION["read_forum_".$FID] .= ",".intVal($ar_res["ID"]);
				}
			}
			CForumTopic::Update($ID, array("=VIEWS"=>"VIEWS+1"));
		}
	}

	function SetReadLabelsNew($ID, $update = false, $LastVisit = false, $arAddParams = array())
	{
		global $DB, $USER;
		
		$ID = intVal($ID);
		$result = false;
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array());
		$arAddParams["UPDATE_TOPIC_VIEWS"] = ($arAddParams["UPDATE_TOPIC_VIEWS"] == "N" ? "N" : "Y");

		if (!$update)
		{
			$arTopic = CForumTopic::GetByID($ID, array("NoFilter" => true));
			if ($arTopic)
			{
				if ($arAddParams["UPDATE_TOPIC_VIEWS"] == "Y")
					CForumTopic::Update($ID, array("=VIEWS"=>"VIEWS+1"));
				
				if (!$USER->IsAuthorized())
					return false;
					
				$USER_ID = intVal($USER->GetID());
				
				$Fields = array("USER_ID" => $USER_ID, "LAST_VISIT" => $DB->GetNowFunction(),
					"FORUM_ID" => $arTopic["FORUM_ID"], "TOPIC_ID" => $ID);
					
				if (intVal($LastVisit) > 0):
					$Fields["LAST_VISIT"] = $DB->CharToDateFunction($DB->ForSql(Date(
						CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL")), $LastVisit)), "FULL");
				endif;
		
				$rows = $DB->Update("b_forum_user_topic", $Fields, "WHERE (TOPIC_ID=".$ID." AND USER_ID=".$USER_ID.")", "File: ".__FILE__."<br>Line: ".__LINE__);
				if (intVal($rows)<=0)
					return $DB->Insert("b_forum_user_topic", $Fields, "File: ".__FILE__."<br>Line: ".__LINE__);
				else
					return true;
			}
		}
		elseif ($USER->IsAuthorized())
		{
			$Fields = array("LAST_VISIT" => $DB->GetNowFunction());
			return $DB->Update("b_forum_user_topic", $Fields, "WHERE (FORUM_ID=".$ID." AND USER_ID=".intVal($USER->GetID()).")", "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return false;
	}
	
	function CleanUp($period = 168)
	{
		global $DB;
		$period = intVal($period)*3600;
		$date = $DB->CharToDateFunction($DB->ForSql(Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), time()-$period)), "FULL") ;
		$strSQL = "DELETE FROM b_forum_user_topic 
					WHERE (LAST_VISIT
					< ".$date.")";
		$DB->Query($strSQL, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return "CForumTopic::CleanUp();";
	}
	

	//---------------> Topic utils
	function SetStat($ID = 0, $arParams = array())
	{
		global $DB;
		$ID = intVal($ID);
		if ($ID <= 0):
			return false;
		endif;
		$arParams = (is_array($arParams) ? $arParams : array());
		$arMessage = (is_array($arParams["MESSAGE"]) ? $arParams["MESSAGE"] : array());
		if ($arMessage["TOPIC_ID"] != $ID)
			$arMessage = array();
		$arFields = array();

		if (!empty($arMessage)):
			$arFields = array(
				"ABS_LAST_POSTER_ID" => ((intVal($arMessage["AUTHOR_ID"])>0) ? $arMessage["AUTHOR_ID"] : false), 
				"ABS_LAST_POSTER_NAME" => $arMessage["AUTHOR_NAME"], 
				"ABS_LAST_POST_DATE" => $arMessage["POST_DATE"], 
				"ABS_LAST_MESSAGE_ID" => $arMessage["ID"]);
			if ($arMessage["APPROVED"] == "Y"):
				$arFields["APPROVED"] = "Y";
				$arFields["LAST_POSTER_ID"] = $arFields["ABS_LAST_POSTER_ID"];
				$arFields["LAST_POSTER_NAME"] = $arFields["ABS_LAST_POSTER_NAME"];
				$arFields["LAST_POST_DATE"] = $arFields["ABS_LAST_POST_DATE"];
				$arFields["LAST_MESSAGE_ID"] = $arFields["ABS_LAST_MESSAGE_ID"];
				if ($arMessage["NEW_TOPIC"] != "Y"):
					$arFields["=POSTS"] = "POSTS+1";
				endif;
			else:
				$arFields["=POSTS_UNAPPROVED"] = "POSTS_UNAPPROVED+1";
			endif;
			
		else:
			$res = CForumMessage::GetList(array(), array("TOPIC_ID" => $ID), "cnt_not_approved");
			$res["CNT"] = (intVal($res["CNT"]) - intVal($res["CNT_NOT_APPROVED"]));
			$res["CNT"] = ($res["CNT"] > 0 ? $res["CNT"] : 0);
			
			$arFields = array(
				"APPROVED" => ($res["CNT"] > 0 ? "Y" : "N"),
				"POSTS" => ($res["CNT"] > 0 ? ($res["CNT"] - 1) : 0), 
				"LAST_POSTER_ID" => false, 
				"LAST_POSTER_NAME" => false, 
				"LAST_POST_DATE" => false, 
				"LAST_MESSAGE_ID" => intVal($res["LAST_MESSAGE_ID"]), 
				"POSTS_UNAPPROVED" => intVal($res["CNT_NOT_APPROVED"]), 
				"ABS_LAST_POSTER_ID" => false, 
				"ABS_LAST_POSTER_NAME" => false, 
				"ABS_LAST_POST_DATE" => false, 
				"ABS_LAST_MESSAGE_ID" => intVal($res["ABS_LAST_MESSAGE_ID"]));
	
			if ($arFields["ABS_LAST_MESSAGE_ID"] > 0):
				$res = CForumMessage::GetByID($arFields["ABS_LAST_MESSAGE_ID"], array("FILTER" => "N"));	
				$arFields["ABS_LAST_POSTER_ID"] = (intVal($res["AUTHOR_ID"]) > 0 ? $res["AUTHOR_ID"] : false);
				$arFields["ABS_LAST_POSTER_NAME"] = $res["AUTHOR_NAME"];
				$arFields["ABS_LAST_POST_DATE"] = $res["POST_DATE"];
				if (intVal($arFields["LAST_MESSAGE_ID"]) > 0):
					if ($arFields["LAST_MESSAGE_ID"] < $arFields["ABS_LAST_MESSAGE_ID"]):
						$res = CForumMessage::GetByID($arFields["LAST_MESSAGE_ID"], array("FILTER" => "N"));
					endif;
					$arFields["LAST_POSTER_ID"] = (intVal($res["AUTHOR_ID"]) > 0 ? $res["AUTHOR_ID"] : false);
					$arFields["LAST_POSTER_NAME"] = $res["AUTHOR_NAME"];
					$arFields["LAST_POST_DATE"] = $res["POST_DATE"];
				endif;
			endif;

			if ($arFields["LAST_POST_DATE"] == false):
				unset($arFields["LAST_POST_DATE"]);
				$arFields["=LAST_POST_DATE"] = "START_DATE";
			endif;
			if ($arFields["ABS_LAST_POST_DATE"] == false):
				unset($arFields["ABS_LAST_POST_DATE"]);
				$arFields["=ABS_LAST_POST_DATE"] = "START_DATE";
			endif;
			
			if ($arFields["LAST_POSTER_NAME"] == false):
				unset($arFields["LAST_POSTER_NAME"]);
				$arFields["=LAST_POSTER_NAME"] = "USER_START_NAME";
			endif;
			if ($arFields["ABS_LAST_POSTER_NAME"] == false):
				unset($arFields["ABS_LAST_POSTER_NAME"]);
				$arFields["=ABS_LAST_POSTER_NAME"] = "USER_START_NAME";
			endif;
		endif;
		return CForumTopic::Update($ID, $arFields);
	}
	
	function OnBeforeIBlockElementDelete($ELEMENT_ID)
	{
		$ELEMENT_ID = intVal($ELEMENT_ID); 
		if ($ELEMENT_ID > 0)
		{
			CModule::IncludeModule("iblock"); 
			$db_res = CIBlockElement::GetList(
				array("ID" => "ASC"), 
				array(
					"ID" => $ELEMENT_ID, 
					"SHOW_HISTORY" => "Y"), 
				false, 
				false, 
				array("ID", "NAME", "IBLOCK_ID", "IBLOCK_SECTION_ID", "WF_PARENT_ELEMENT_ID", "PROPERTY_FORUM_TOPIC_ID"));
			
			if ($db_res && $res = $db_res->Fetch())
			{
				if ($res["PROPERTY_FORUM_TOPIC_ID_VALUE"] > 0 && ($res["WF_PARENT_ELEMENT_ID"] == 0))
					CForumTopic::Delete($res["PROPERTY_FORUM_TOPIC_ID_VALUE"]); 

			}
		}
		return true; 
	}
}
class _CTopicDBResult extends CDBResult
{
	function _CTopicDBResult($res)
	{
		parent::CDBResult($res);
	}
	function Fetch()
	{
		global $DB;
		if($res = parent::Fetch())
		{
			if (COption::GetOptionString("forum", "FILTER", "Y") == "Y"):
				if (!empty($res["HTML"])):
					$arr = unserialize($res["HTML"]);
					if (is_array($arr) && is_set($arr, "TITLE"))
					{
						foreach ($arr as $key => $val)
						{
							if (strLen($val)>0)
								$res[$key] = $val;
						}	
//						return $res;
					}
				endif;
				if (!empty($res["F_HTML"])):
					$arr = unserialize($res["F_HTML"]);
					if (is_array($arr))
					{
						foreach ($arr as $key => $val)
						{
							$res["F_".$key] = $val;
						}
					}
					if (!empty($res["TITLE"]))
						$res["F_TITLE"] = $res["TITLE"];
				endif;
			endif;
			
			/* For CForumUser::UserAddInfo only */
			if (is_set($res, "FIRST_POST") || is_set($res, "LAST_POST")):
				$arSqlSearch = array(); 
				if (is_set($res, "FIRST_POST"))
					$arSqlSearch["FIRST_POST"] = "FM.ID=".intVal($res["FIRST_POST"]);
				if (is_set($res, "LAST_POST"))
					$arSqlSearch["LAST_POST"] = "FM.ID=".intVal($res["LAST_POST"]);
				if (!empty($arSqlSearch)):
					$strSql = "SELECT FM.ID, ".$DB->DateToCharFunction("FM.POST_DATE", "FULL")." AS POST_DATE ".
						"FROM b_forum_message FM WHERE ".implode(" OR ", $arSqlSearch);
					$db_res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					if($db_res && $val = $db_res->Fetch()):
						do 
						{
							if (is_set($res, "FIRST_POST") && $res["FIRST_POST"] == $val["ID"])
								$res["FIRST_POST_DATE"] = $val["POST_DATE"];
							if (is_set($res, "LAST_POST") && $res["LAST_POST"] == $val["ID"])
								$res["LAST_POST_DATE"] = $val["POST_DATE"];
						}while ($val = $db_res->Fetch());
					endif;
				endif;
			endif;
		}
		return $res;
	}
}

?>
