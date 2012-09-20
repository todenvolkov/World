<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__); 
/**********************************************************************/
/************** FORUM *************************************************/
/**********************************************************************/
class CAllForumNew
{
	//---------------> Forum insert, update, delete
	function CanUserViewForum($FID, $arUserGroups, $ExternalPermission = false)
	{
		$FID = intVal($FID);
		$arUserGroups = (!is_array($arUserGroups) ? array($arUserGroups) : $arUserGroups);
		if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W"):
			return true;
		endif;
		$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($FID, $arUserGroups) : $ExternalPermission);
		if ($strPerms >= "Y")
			return true;
		$arForum = CForumNew::GetByID($FID);
		if (!is_array($arForum) || $arForum["ACTIVE"] != "Y"):
			return false;
		endif;
		return ($strPerms < "E" ? false : true);
	}

	function CanUserAddForum($arUserGroups, $iUserID = 0)
	{
		$arUserGroups = (!is_array($arUserGroups) ? array($arUserGroups) : $arUserGroups);
		if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W")
			return true;
		return false;
	}

	function CanUserUpdateForum($FID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$FID = intVal($FID);
		if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W"):
			return true;
		elseif (!CForumUser::IsLocked($iUserID)):
			$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($FID, $arUserGroups) : $ExternalPermission);
		else:
			$strPerms = CForumNew::GetPermissionUserDefault($FID, $arUserGroups);
		endif;
		return ($strPerms < "Y" ? false : true);
	}

	function CanUserDeleteForum($FID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$FID = intVal($FID);
		$arUserGroups = (!is_array($arUserGroups) ? array($arUserGroups) : $arUserGroups);
		if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W"):
			return true;
		elseif (!CForumUser::IsLocked($iUserID)):
			$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($FID, $arUserGroups) : $ExternalPermission);
		else:
			$strPerms = CForumNew::GetPermissionUserDefault($FID);
		endif;
		return ($strPerms < "Y" ? false : true);
	}
	
	function CanUserModerateForum($FID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$FID = intVal($FID);
		$arUserGroups = (!is_array($arUserGroups) ? array($arUserGroups) : $arUserGroups);
		if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W"):
			return true;
		elseif (!CForumUser::IsLocked($iUserID)):
			$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($FID, $arUserGroups) : $ExternalPermission);
		else:
			$strPerms = CForumNew::GetPermissionUserDefault($FID);
		endif;
		if ($strPerms >= "Y"):
			return true;
		endif;
		$arForum = CForumNew::GetByID($FID);
		if (!is_array($arForum) || $arForum["ACTIVE"] != "Y"):
			return false;
		endif;
		return ($strPerms < "Q" ? false : true);
	}
	
	function CanUserEditForum($FID, $arUserGroups, $iUserID = 0, $ExternalPermission = false)
	{
		$FID = intVal($FID);
		$arUserGroups = (!is_array($arUserGroups) ? array($arUserGroups) : $arUserGroups);
		if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W"):
			return true;
		elseif (!CForumUser::IsLocked($iUserID)):
			$strPerms = ($ExternalPermission == false ? CForumNew::GetUserPermission($FID, $arUserGroups) : $ExternalPermission);
		else:
			$strPerms = CForumNew::GetPermissionUserDefault($FID);
		endif;
		if ($strPerms >= "Y"):
			return true;
		endif;
		$arForum = CForumNew::GetByID($FID);
		if (!is_array($arForum) || $arForum["ACTIVE"] != "Y"):
			return false;
		endif;
		return ($strPerms < "U" ? false : true);
	}

	function CheckFields($ACTION, &$arFields)
	{
		$aMsg = array();
		if (is_set($arFields, "NAME") || $ACTION == "ADD")
		{
			$arFields["NAME"] = trim($arFields["NAME"]);
			if (empty($arFields["NAME"]))
				$aMsg[] = array(
					"id" => "NAME", 
					"text" => GetMessage("F_ERROR_EMPTY_NAME"));
		}

		if (!is_set($arFields, "SITES") && is_set($arFields, "LID"))
		{
			if (is_set($arFields, "PATH2FORUM_MESSAGE"))
				$arFields["SITES"] = Array($arFields["LID"] => $arFields["PATH2FORUM_MESSAGE"]);
			else
			{
				$sPath = "/";
				$db_res = CSite::GetByID($arFields["LID"]);
				if ($db_res && $res = $db_res->Fetch())
					$sPath = $res["DIR"];
				$arFields["SITES"] = array(
					$arFields["LID"] => $sPath.(COption::GetOptionString("forum", "REL_FPATH", "")).
						"forum/read.php?FID=#FORUM_ID#&TID=#TOPIC_ID#&MID=#MESSAGE_ID##message#MESSAGE_ID#");
			}
		}
		

		if (is_set($arFields, "SITES") || $ACTION == "ADD")
		{
			if (is_array($arFields["SITES"]) && !empty($arFields["SITES"]))
			{
				$db_res = CSite::GetList($sBy = "sort", $sOrder = "asc");
				$arSites = array();
				while ($res = $db_res->Fetch())
				{
					if (is_set($arFields["SITES"], $res["LID"]))
					{
						if (strLen($arFields["SITES"][$res["LID"]]) > 0)
							$arSites[$res["LID"]] = $arFields["SITES"][$res["LID"]];
						else
							$aMsg[] = array(
								"id" => "SITE_PATH[".$res["LID"]."]", 
								"text" => GetMessage("F_ERROR_EMPTY_SITE_PATH", 
									array("#SITE_ID#" => $res["LID"], "#SITE_NAME#" => $res["NAME"])));
					}
				}
				$arFields["SITES"] = $arSites; 
			}
			if (!is_array($arFields["SITES"]) || empty($arFields["SITES"]))
			{
				$aMsg[] = array(
					"id" => "SITES", 
					"text" => GetMessage("F_ERROR_EMPTY_SITES"));
			}
		}
		if(!empty($aMsg))
		{
			$e = new CAdminException(array_reverse($aMsg));
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		if (is_set($arFields, "SORT") || $ACTION=="ADD") {$arFields["SORT"] = intVal(intVal($arFields["SORT"]) <= 0 ? 100 : $arFields["SORT"]);}
		if (is_set($arFields, "FORUM_GROUP_ID") || $ACTION=="ADD") {$arFields["FORUM_GROUP_ID"] = (intVal($arFields["FORUM_GROUP_ID"]) <= 0 ? false : intVal($arFields["FORUM_GROUP_ID"]));}

		if (is_set($arFields, "ACTIVE") || $ACTION=="ADD") {$arFields["ACTIVE"] = ($arFields["ACTIVE"] == "Y" ? "Y" : "N");}
		if (is_set($arFields, "INDEXATION") || $ACTION=="ADD") {$arFields["INDEXATION"] = ($arFields["INDEXATION"] == "N" ? "N" : "Y");}
		if (is_set($arFields, "MODERATION") || $ACTION=="ADD") {$arFields["MODERATION"] = ($arFields["MODERATION"] == "Y" ? "Y" : "N");}

		if (is_set($arFields, "ALLOW_HTML") || $ACTION=="ADD") {$arFields["ALLOW_HTML"] = ($arFields["ALLOW_HTML"] == "Y" ? "Y" : "N");}
		if (is_set($arFields, "ALLOW_ANCHOR") || $ACTION=="ADD") {$arFields["ALLOW_ANCHOR"] = ($arFields["ALLOW_ANCHOR"] == "N" ? "N" : "Y");}
		if (is_set($arFields, "ALLOW_BIU") || $ACTION=="ADD") {$arFields["ALLOW_BIU"] = ($arFields["ALLOW_BIU"] == "N" ? "N" : "Y");}
		if (is_set($arFields, "ALLOW_IMG") || $ACTION=="ADD") {$arFields["ALLOW_IMG"] = ($arFields["ALLOW_IMG"] == "N" ? "N" : "Y");}
		if (is_set($arFields, "ALLOW_VIDEO") || $ACTION=="ADD") {$arFields["ALLOW_VIDEO"] = ($arFields["ALLOW_VIDEO"] == "N" ? "N" : "Y");}
		if (is_set($arFields, "ALLOW_LIST") || $ACTION=="ADD") {$arFields["ALLOW_LIST"] = ($arFields["ALLOW_LIST"] == "N" ? "N" : "Y");}
		if (is_set($arFields, "ALLOW_QUOTE") || $ACTION=="ADD") {$arFields["ALLOW_QUOTE"] = ($arFields["ALLOW_QUOTE"] == "N" ? "N" : "Y");}
		if (is_set($arFields, "ALLOW_CODE") || $ACTION=="ADD") {$arFields["ALLOW_CODE"] = ($arFields["ALLOW_CODE"] == "N" ? "N" : "Y");}
		if (is_set($arFields, "ALLOW_FONT") || $ACTION=="ADD") {$arFields["ALLOW_FONT"] = ($arFields["ALLOW_FONT"] == "N" ? "N" : "Y");}
		if (is_set($arFields, "ALLOW_SMILES") || $ACTION=="ADD") {$arFields["ALLOW_SMILES"] = ($arFields["ALLOW_SMILES"] == "N" ? "N" : "Y");}
		if (is_set($arFields, "ALLOW_SMILES") || $ACTION=="ADD") {$arFields["ALLOW_UPLOAD"] = (in_array($arFields["ALLOW_UPLOAD"], array("Y", "F", "A")) ? $arFields["ALLOW_UPLOAD"] : "N");}
		if (is_set($arFields, "ALLOW_NL2BR") || $ACTION=="ADD") {$arFields["ALLOW_NL2BR"] = ($arFields["ALLOW_NL2BR"] == "Y" ? "Y" : "N");}
		if (is_set($arFields, "ALLOW_KEEP_AMP") || $ACTION=="ADD") {$arFields["ALLOW_KEEP_AMP"] = ($arFields["ALLOW_KEEP_AMP"] == "Y" ? "Y" : "N");}
		if (is_set($arFields, "ALLOW_TOPIC_TITLED") || $ACTION=="ADD") {$arFields["ALLOW_TOPIC_TITLED"] = ($arFields["ALLOW_TOPIC_TITLED"] == "Y" ? "Y" : "N");}
		
		if (is_set($arFields, "ALLOW_MOVE_TOPIC") || $ACTION=="ADD") {$arFields["ALLOW_MOVE_TOPIC"] = ($arFields["ALLOW_MOVE_TOPIC"] == "Y" ? "Y" : "N");}
		if (is_set($arFields, "ASK_GUEST_EMAIL") || $ACTION=="ADD") {$arFields["ASK_GUEST_EMAIL"] = ($arFields["ASK_GUEST_EMAIL"] == "Y" ? "Y" : "N");}
		if (is_set($arFields, "ASK_GUEST_EMAIL") || $ACTION=="ADD") {$arFields["ASK_GUEST_EMAIL"] = ($arFields["ASK_GUEST_EMAIL"] == "Y" ? "Y" : "N");}
		
		if (is_set($arFields, "LAST_POSTER_NAME") && COption::GetOptionString("forum", "FILTER", "Y") == "Y")
		{
			$arr = array("LAST_POSTER_NAME" => CFilterUnquotableWords::Filter($arFields["LAST_POSTER_NAME"]));
			$arr["LAST_POSTER_NAME"] = (empty($arr["LAST_POSTER_NAME"]) ? "*" : $arr["LAST_POSTER_NAME"]);
			$arFields["HTML"] = serialize($arr);
		}
		return true;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$ID = intVal($ID);
		$arForum_prev = array();
		$arProcAuth = array();

		if ($ID <= 0 || !CForumNew::CheckFields("UPDATE", $arFields))
			return false;

		if ($arFields["ACTIVE"] == "N")
			$arForum_prev = CForumNew::GetByID($ID);
/***************** Event onBeforeForumUpdate ***********************/
		$events = GetModuleEvents("forum", "onBeforeForumUpdate");
		while ($arEvent = $events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$ID, &$arFields)) === false)
				return false;
		}
/***************** /Event ******************************************/
		if (empty($arFields))
			return false;
/***************** Cleaning cache **********************************/
		unset($GLOBALS["FORUM_CACHE"]["FORUM"][$ID]);
		if(CACHED_b_forum !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum");
		if(CACHED_b_forum2site !== false && is_array($arFields["SITES"]) && count($arFields["SITES"]) > 0)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum2site");
/***************** Cleaning cache/**********************************/
		$strUpdate = $DB->PrepareUpdate("b_forum", $arFields);
		if (!empty($strUpdate))
		{
			$strSql = "UPDATE b_forum SET ".$strUpdate." WHERE ID=".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		
		if (is_array($arFields["SITES"]) && count($arFields["SITES"]) > 0)
		{
			$DB->Query("DELETE FROM b_forum2site WHERE FORUM_ID = ".$ID);
			foreach ($arFields["SITES"] as $key => $value)
			{
				$DB->Query("INSERT INTO b_forum2site (FORUM_ID, SITE_ID, PATH2FORUM_MESSAGE) VALUES(".$ID.", '".$DB->ForSql($key, 2)."', '".$DB->ForSql($value, 250)."')");
			}
		}
		
		if (is_set($arFields, "GROUP_ID") && is_array($arFields["GROUP_ID"]))
			CForumNew::SetAccessPermissions($ID, $arFields["GROUP_ID"]);
/***************** Event onAfterForumUpdate ************************/
		$events = GetModuleEvents("forum", "onAfterForumUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
/***************** /Event ******************************************/

/***************** Update statistic ********************************/
/* If forum became inactive than all statistics for users of this forum will be recalculated.*/
		if ($arFields["ACTIVE"] == "N" && $arForum_prev["ACTIVE"] == "Y")
		{
			$db_res = CForumMessage::GetList(array(), array("FORUM_ID"=>$ID, "!AUTHOR_ID"=>0));
			while ($res = $db_res->Fetch())
			{
				$res["AUTHOR_ID"] = intVal($res["AUTHOR_ID"]);
				if (!in_array($res["AUTHOR_ID"], $arProcAuth))
				{
					CForumUser::SetStat($res["AUTHOR_ID"]);
					$arProcAuth[] = $res["AUTHOR_ID"];
				}
			}
			unset($arProcAuth);
		}
/***************** Update search module ****************************/
		if (CModule::IncludeModule("search"))
		{
			if ($arFields["ACTIVE"] == "N" && $arForum_prev["ACTIVE"] == "Y" || 
				$arFields["INDEXATION"] == "N" && $arForum_prev["INDEXATION"] == "Y")
			{
				CSearch::DeleteIndex("forum", false, $ID);
			}
			elseif (is_set($arFields, "GROUP_ID") && is_array($arFields["GROUP_ID"]))
			{
				$arGroups = CForumNew::GetAccessPermissions($ID);
				$arGPerm = Array();
				for ($i=0; $i<count($arGroups); $i++)
				{
					if ($arGroups[$i][1] >= "E")
					{
						$arGPerm[] = $arGroups[$i][0];
						if ($arGroups[$i][0] == 2) 
							break;
					}
				}
				CSearch::ChangePermission("forum", $arGPerm, false, $ID);
			}
		}
		return $ID;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = intVal($ID);
		$bCanDelete = true;
/***************** Event OnBeforeForumDelete ***********************/
		$events = GetModuleEvents("forum", "OnBeforeForumDelete");
		while ($arEvent = $events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$ID)) === false)
			{
				$bCanDelete = false;
				break;
			}
		}
/***************** /Event ******************************************/
		if (!$bCanDelete) 
			return false;
/***************** Event OnForumDelete *****************************/
		$events = GetModuleEvents("forum", "OnForumDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$ID));
/***************** /Event ******************************************/
/***************** Cleaning cache **********************************/
		unset($GLOBALS["FORUM_CACHE"]["FORUM"][$ID]);
		if(CACHED_b_forum !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum");
		if(CACHED_b_forum_perms !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_perms");
		if(CACHED_b_forum2site !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum2site");
/***************** Cleaning cache/**********************************/
/***************** Search module ***********************************/
		set_time_limit(0);
		if (CModule::IncludeModule("search"))
		{
			CSearch::DeleteIndex("forum", false, $ID);
		}
		CForumFiles::Delete(array("FORUM_ID" => $ID), array("DELETE_FORUM_FILE" => "Y"));
		$DB->StartTransaction();
		// Update USER statistic
		$arProcAuth = array();
		$db_res = CForumMessage::GetList(array(), array("FORUM_ID"=>$ID, "!AUTHOR_ID"=>0));
		while ($res = $db_res->Fetch())
		{
			$res["AUTHOR_ID"] = intVal($res["AUTHOR_ID"]);
			if (!in_array($res["AUTHOR_ID"], $arProcAuth))
			{
				$arProcAuth[] = intVal($res["AUTHOR_ID"]);
			}
		}
		if (IsModuleInstalled("vote"))
		{
			$db_res = CForumMessage::GetList(array(), array("FORUM_ID"=>$ID, "PARAM1" => "VT", "!PARAM2" => 0));
			if ($db_res && $res = $db_res->Fetch()):
				CModule::IncludeModule("vote");
				do {
					CVote::Delete($res["PARAM2"]);
				} while ($res = $db_res->Fetch());
			endif;
		}
		if (!$DB->Query("DELETE FROM b_forum_subscribe WHERE FORUM_ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}
		if (!$DB->Query("DELETE FROM b_forum_message WHERE FORUM_ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}
		if (!$DB->Query("DELETE FROM b_forum_topic WHERE FORUM_ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}
		if (!$DB->Query("DELETE FROM b_forum_perms WHERE FORUM_ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}
		if (!$DB->Query("DELETE FROM b_forum2site WHERE FORUM_ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}
		if (!$DB->Query("DELETE FROM b_forum WHERE ID=".$ID, true))
		{
			$DB->Rollback();
			return false;
		}

		// Update USER statistic
		for ($i = 0; $i < count($arProcAuth); $i++)
		{
			CForumUser::SetStat($arProcAuth[$i]);
		}

		$DB->Commit();
/***************** Event OnAfterForumDelete ************************/
		$events = GetModuleEvents("forum", "OnAfterForumDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID));
/***************** /Event ******************************************/
		return true;
	}

	//---------------> Array of sites (langs) where forum is available
	function GetSites($ID)
	{
		global $DB, $CACHE_MANAGER;
		$ID = intVal($ID);
		$cache_id = "b_forum2site_".$ID;
		if ($ID <= 0):
			return false;
		elseif (!is_array($GLOBALS["FORUM_CACHE"]["FORUM"][$ID])):
			$GLOBALS["FORUM_CACHE"]["FORUM"][$ID] = array();
		endif;
		
		if (!array_key_exists("SITES", $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]))
		{
			if (CACHED_b_forum2site !== false && $CACHE_MANAGER->Read(CACHED_b_forum2site, $cache_id, "b_forum2site"))
			{
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["SITES"] = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$strSql = "SELECT FS.FORUM_ID, FS.SITE_ID, FS.PATH2FORUM_MESSAGE FROM b_forum2site FS WHERE FS.FORUM_ID = ".$ID;
				$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$arRes = array();
				while ($res = $db_res->Fetch())
					$arRes[$res["SITE_ID"]] = $res["PATH2FORUM_MESSAGE"];
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["SITES"] = $arRes;
				if (CACHED_b_forum2site !== false)
					$CACHE_MANAGER->Set($cache_id, $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["SITES"]);
			}
		}
		return $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["SITES"];
	}

	//---------------> Forum permissions
	function GetPermissionUserDefault($ID)
	{
		$arFields = array(2);
		if (COption::GetOptionString("main", "new_user_registration", "") == "Y")
		{
			$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
			if($def_group != "")
			{
				$arFields = explode(",", $def_group);
				$arFields[] = 2;
			}
		}
		$res = CForumNew::GetUserPermission($ID, $arFields);
		if ($res >= "E")
			return "E";
		else 
			return "A";
	}
	
	function GetAccessPermissions($ID, $TYPE = "ONE")
	{
		global $CACHE_MANAGER;
		$ID = intVal($ID);
		$TYPE = ($TYPE == "ONE" ? "ONE" : "ALL");
		$cache_id = "b_forum_perms_".$ID."_all";
		$arRes = array();
		if ($ID <= 0):
			return false;
		elseif (!is_array($GLOBALS["FORUM_CACHE"]["FORUM"][$ID])):
			$GLOBALS["FORUM_CACHE"]["FORUM"][$ID] = array();
		endif;
		
		if (!array_key_exists("PERMISSIONS", $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]))
		{
			if (CACHED_b_forum_perms !== false && $CACHE_MANAGER->Read(CACHED_b_forum_perms, $cache_id, "b_forum_perms"))
			{
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSIONS"] = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$db_res = CForumNew::GetAccessPermsList(array(), array("FORUM_ID" => $ID));
				while ($res = $db_res->Fetch()):
					$arRes[$res["GROUP_ID"]] = $res["PERMISSION"];
				endwhile;
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSIONS"] = $arRes;
				if (CACHED_b_forum_perms !== false)
					$CACHE_MANAGER->Set($cache_id, $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSIONS"]);
			}
		}
		$result = $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSIONS"];
		if ($TYPE == "ONE"):
			$result = array();
			foreach ($GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSIONS"] as $key => $val):
				$result[] = array($key, $val);
			endforeach;
		endif;
		return $result;
	}

	function GetAccessPermsList($arOrder = array("ID"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = array();
		$strSqlSearch = "";
		$arSqlOrder = array();
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "FORUM_ID":
				case "GROUP_ID":
					if (intVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FP.".$key." IS NULL OR FP.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FP.".$key." IS NULL OR NOT ":"")."FP.".$key." ".$strOperation." ".intVal($val)." ";
					break;
				case "PERMISSION":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FP.".$key." IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(FP.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FP.".$key." IS NULL OR NOT ":"")."FP.".$key." ".$strOperation." '".$DB->ForSql($val)."' ";
					break;
			}
		}
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(" AND ", $arSqlSearch).") ";

		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "FORUM_ID") $arSqlOrder[] = " FP.FORUM_ID ".$order." ";
			elseif ($by == "GROUP_ID") $arSqlOrder[] = " FP.GROUP_ID ".$order." ";
			elseif ($by == "PERMISSION") $arSqlOrder[] = " FP.PERMISSION ".$order." ";
			else
			{
				$arSqlOrder[] = " FP.ID ".$order." ";
				$by = "ID";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);
			
		$strSql = 
			"SELECT FP.ID, FP.FORUM_ID, FP.GROUP_ID, FP.PERMISSION 
			FROM b_forum_perms FP 
			WHERE 1 = 1 
			".$strSqlSearch."
			".$strSqlOrder;
		
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function SetAccessPermissions($ID, $arGROUP_ID)
	{
		global $DB, $CACHE_MANAGER, $aForumPermissions;
		$ID = intVal($ID);
		$arGROUP_ID = (is_array($arGROUP_ID) ? $arGROUP_ID : array());
		$arGroups = array();
		if ($ID <= 0 || empty($arGROUP_ID)):
			return false;
		endif;
/***************** Cleaning cache **********************************/
		unset($GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSION"]);
		unset($GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSIONS"]);
		if (CACHED_b_forum_perms !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_perms");
/***************** Cleaning cache/**********************************/
		$db_res = CGroup::GetList($by = "ID", $order = "ASC");
		if ($db_res && $res = $db_res->Fetch())
		{
			do 
			{
				$arGroups[] = intVal($res["ID"]);
			} while ($res = $db_res->Fetch());
			
			$DB->Query("DELETE FROM b_forum_perms WHERE FORUM_ID=".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			
			foreach ($arGROUP_ID as $key => $val)
			{
				$key = intVal($key); $val = strToUpper($val);
				if ($key <= 1 || !in_array($val, $aForumPermissions["reference_id"]) || !in_array($key, $arGroups)):
					continue;
				endif;
				$arFields = array(
					"FORUM_ID" => $ID, 
					"GROUP_ID" => $key, 
					"PERMISSION" => "'".$val."'");
				$DB->Insert("b_forum_perms", $arFields, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
		return true;
	}

	function GetUserPermission($ID, $arUserGroups)
	{
		global $DB, $CACHE_MANAGER, $aForumPermissions;
		$ID = intVal($ID);
		$arUserGroups = (!is_array($arUserGroups) ? array($arUserGroups) : $arUserGroups);
		sort($arUserGroups);
		$key = $ID."_".implode("_", $arUserGroups);
		$cache_id = "b_forum_perms".$key;
		if ($ID <= 0):
			return $aForumPermissions["reference_id"][0];
		elseif (in_array(1, $arUserGroups)):
			return $aForumPermissions["reference_id"][count($aForumPermissions["reference_id"])-1];
		elseif (!is_array($GLOBALS["FORUM_CACHE"]["FORUM"][$ID])):
			$GLOBALS["FORUM_CACHE"]["FORUM"][$ID] = array("PERMISSION" => array());
		elseif (!array_key_exists("PERMISSION", $GLOBALS["FORUM_CACHE"]["FORUM"][$ID])):
			$GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSION"] = array();
		endif;
		
		if (!array_key_exists($key, $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSION"]))
		{
			if (CACHED_b_forum_perms !== false && $CACHE_MANAGER->Read(CACHED_b_forum_perms, $cache_id, "b_forum_perms"))
			{
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSION"][$key] = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$strSql = "SELECT MAX(FP.PERMISSION) as P FROM b_forum_perms FP ".
					"WHERE FP.FORUM_ID=".$ID." AND FP.GROUP_ID IN (".implode(",", $arUserGroups).")";
				$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if ($r = $res->Fetch())
					$GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSION"][$key] = $r["P"];
			}
			if (!in_array($GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSION"][$key], $aForumPermissions["reference_id"]))
			{
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSION"][$key] = $aForumPermissions["reference_id"][0];
			}
			if (CACHED_b_forum_perms !== false)
				$CACHE_MANAGER->Set($cache_id, $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSION"][$key]);
		}
		return $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["PERMISSION"][$key];
	}

	//---------------> Forum Utils
	function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (substr($key, 0, 1)=="!")
		{
			$key = substr($key, 1);
			$strNegative = "Y";
		}

		if (substr($key, 0, 2)==">=")
		{
			$key = substr($key, 2);
			$strOperation = ">=";
		}
		elseif (substr($key, 0, 1)==">")
		{
			$key = substr($key, 1);
			$strOperation = ">";
		}
		elseif (substr($key, 0, 2)=="<=")
		{
			$key = substr($key, 2);
			$strOperation = "<=";
		}
		elseif (substr($key, 0, 1)=="<")
		{
			$key = substr($key, 1);
			$strOperation = "<";
		}
		elseif (substr($key, 0, 1)=="@")
		{
			$key = substr($key, 1);
			$strOperation = "IN";
		}
		elseif (substr($key, 0, 1)=="%")
		{
			$key = substr($key, 1);
			$strOperation = "LIKE";
		}
		else
		{
			$strOperation = "=";
		}

		return array("FIELD"=>$key, "NEGATIVE"=>$strNegative, "OPERATION"=>$strOperation);
	}
	
	function GetSelectFields($arAddParams = array())
	{
		global $DB;
		$arAddParams = (is_array($arAddParams) ? $arAddParams : array());
		$arAddParams["sPrefix"] = $DB->ForSql(empty($arAddParams["sPrefix"]) ? "F." : $arAddParams["sPrefix"]);
		$arAddParams["sTablePrefix"] = $DB->ForSql(empty($arAddParams["sTablePrefix"]) ? "F." : $arAddParams["sTablePrefix"]);
		$arAddParams["sReturnResult"] = ($arAddParams["sReturnResult"] == "string" ? "string" : "array");
		
		$res = array(
			$arAddParams["sPrefix"]."ID" => $arAddParams["sTablePrefix"]."ID",
			$arAddParams["sPrefix"]."NAME" => $arAddParams["sTablePrefix"]."NAME",
			$arAddParams["sPrefix"]."DESCRIPTION" => $arAddParams["sTablePrefix"]."DESCRIPTION",
			$arAddParams["sPrefix"]."SORT" => $arAddParams["sTablePrefix"]."SORT",
			$arAddParams["sPrefix"]."ACTIVE" => $arAddParams["sTablePrefix"]."ACTIVE",
			$arAddParams["sPrefix"]."MODERATION" => $arAddParams["sTablePrefix"]."MODERATION",
			$arAddParams["sPrefix"]."INDEXATION" => $arAddParams["sTablePrefix"]."INDEXATION",
			$arAddParams["sPrefix"]."ALLOW_MOVE_TOPIC" => $arAddParams["sTablePrefix"]."ALLOW_MOVE_TOPIC",
			$arAddParams["sPrefix"]."TOPICS" => $arAddParams["sTablePrefix"]."TOPICS",
			$arAddParams["sPrefix"]."POSTS" => $arAddParams["sTablePrefix"]."POSTS",
			$arAddParams["sPrefix"]."LAST_POSTER_ID" => $arAddParams["sTablePrefix"]."LAST_POSTER_ID",
			$arAddParams["sPrefix"]."LAST_POSTER_NAME" => $arAddParams["sTablePrefix"]."LAST_POSTER_NAME",
			($arAddParams["sPrefix"] == $arAddParams["sTablePrefix"] ? "" : $arAddParams["sPrefix"]).
				"LAST_POST_DATE" => $DB->DateToCharFunction($arAddParams["sTablePrefix"]."LAST_POST_DATE", "FULL"),
			$arAddParams["sPrefix"]."LAST_MESSAGE_ID" => $arAddParams["sTablePrefix"]."LAST_MESSAGE_ID",
			($arAddParams["sPrefix"] == $arAddParams["sTablePrefix"] ? "" : $arAddParams["sPrefix"]).
				"MID" => $arAddParams["sTablePrefix"]."LAST_MESSAGE_ID ",
			$arAddParams["sPrefix"]."LAST_MESSAGE_ID" => $arAddParams["sTablePrefix"]."LAST_MESSAGE_ID",
			
			$arAddParams["sPrefix"]."POSTS_UNAPPROVED" => $arAddParams["sTablePrefix"]."POSTS_UNAPPROVED",
			$arAddParams["sPrefix"]."ABS_LAST_POSTER_ID" => $arAddParams["sTablePrefix"]."ABS_LAST_POSTER_ID",
			$arAddParams["sPrefix"]."ABS_LAST_POSTER_NAME" => $arAddParams["sTablePrefix"]."ABS_LAST_POSTER_NAME",
			($arAddParams["sPrefix"] == $arAddParams["sTablePrefix"] ? "" : $arAddParams["sPrefix"]).
				"ABS_LAST_POST_DATE" => $DB->DateToCharFunction($arAddParams["sTablePrefix"]."ABS_LAST_POST_DATE", "FULL"),
			$arAddParams["sPrefix"]."ABS_LAST_MESSAGE_ID" => $arAddParams["sTablePrefix"]."ABS_LAST_MESSAGE_ID",
			
			$arAddParams["sPrefix"]."ORDER_BY" => $arAddParams["sTablePrefix"]."ORDER_BY",
			$arAddParams["sPrefix"]."ORDER_DIRECTION" => $arAddParams["sTablePrefix"]."ORDER_DIRECTION",
			$arAddParams["sPrefix"]."ALLOW_HTML" => $arAddParams["sTablePrefix"]."ALLOW_HTML",
			$arAddParams["sPrefix"]."ALLOW_ANCHOR" => $arAddParams["sTablePrefix"]."ALLOW_ANCHOR",
			$arAddParams["sPrefix"]."ALLOW_BIU" => $arAddParams["sTablePrefix"]."ALLOW_BIU",
			$arAddParams["sPrefix"]."ALLOW_IMG" => $arAddParams["sTablePrefix"]."ALLOW_IMG",
			$arAddParams["sPrefix"]."ALLOW_VIDEO" => $arAddParams["sTablePrefix"]."ALLOW_VIDEO",
			$arAddParams["sPrefix"]."ALLOW_LIST" => $arAddParams["sTablePrefix"]."ALLOW_LIST",
			$arAddParams["sPrefix"]."ALLOW_QUOTE" => $arAddParams["sTablePrefix"]."ALLOW_QUOTE",
			$arAddParams["sPrefix"]."ALLOW_CODE" => $arAddParams["sTablePrefix"]."ALLOW_CODE",
			$arAddParams["sPrefix"]."ALLOW_FONT" => $arAddParams["sTablePrefix"]."ALLOW_FONT",
			$arAddParams["sPrefix"]."ALLOW_SMILES" => $arAddParams["sTablePrefix"]."ALLOW_SMILES",
			$arAddParams["sPrefix"]."ALLOW_UPLOAD" => $arAddParams["sTablePrefix"]."ALLOW_UPLOAD",
			$arAddParams["sPrefix"]."ALLOW_TOPIC_TITLED" => $arAddParams["sTablePrefix"]."ALLOW_TOPIC_TITLED",
			$arAddParams["sPrefix"]."EVENT1" => $arAddParams["sTablePrefix"]."EVENT1",
			$arAddParams["sPrefix"]."EVENT2" => $arAddParams["sTablePrefix"]."EVENT2",
			$arAddParams["sPrefix"]."EVENT3" => $arAddParams["sTablePrefix"]."EVENT3",
			$arAddParams["sPrefix"]."ALLOW_NL2BR" => $arAddParams["sTablePrefix"]."ALLOW_NL2BR",
			$arAddParams["sPrefix"]."ALLOW_UPLOAD_EXT" => $arAddParams["sTablePrefix"]."ALLOW_UPLOAD_EXT",
			$arAddParams["sPrefix"]."FORUM_GROUP_ID" => $arAddParams["sTablePrefix"]."FORUM_GROUP_ID",
			$arAddParams["sPrefix"]."ASK_GUEST_EMAIL" => $arAddParams["sTablePrefix"]."ASK_GUEST_EMAIL",
			$arAddParams["sPrefix"]."USE_CAPTCHA" => $arAddParams["sTablePrefix"]."USE_CAPTCHA",
			$arAddParams["sPrefix"]."HTML" => $arAddParams["sTablePrefix"]."HTML");

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

	

	//---------------> Forum list
	function GetList($arOrder = Array("SORT"=>"ASC"), $arFilter = Array())
	{
		global $DB;
		$arSqlSearch = Array();
		$arSqlSearchFrom = Array();
		$strSqlSelect = "";
		$strSqlSearchFrom = "";
		$strSqlSearch = "";
		$arSqlOrder = Array();
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		
		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "LID": 
				case "SITE_ID":
					$val = trim($val);
					if (strlen($val)>0)
					{
						$arSqlSearch[] = "F.ID = F2S.FORUM_ID AND ".($strNegative=="Y"?" NOT ":"")."(F2S.SITE_ID ".$strOperation." '".$DB->ForSql($val)."' )";
						$arSqlSearchFrom[] = "b_forum2site F2S";
					}
					break;
				case "INDEXATION":
				case "ACTIVE":
				case "XML_ID":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(F.".$key." IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(F.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" F.".$key." IS NULL OR NOT ":"")."(F.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "ID":
				case "FORUM_GROUP_ID":
				case "TOPICS":
				case "POSTS":
				case "POSTS_UNAPPROVED":
					if ($strOperation == "IN")
					{
						if (is_array($val))
						{
							$val_int = array();
							foreach ($val as $v)
								$val_int[] = intVal($v);
							$val = implode(", ", $val_int);
						}
						$val = trim($val);
					}
					if (($strOperation == "IN" && strLen($val) <= 0) || intVal($val) <= 0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(F.".$key." IS NULL OR F.".$key."<=0)";
					elseif ($strOperation == "IN")
						$arSqlSearch[] = ($strNegative=="Y"?" NOT ":"")."(F.".$key." IN (".$DB->ForSql($val)."))";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" F.".$key." IS NULL OR NOT ":"")."(F.".$key." ".$strOperation." ".intVal($val)." )";
					break;
				case "TEXT":
					$arSqlSearch[] = " (".GetFilterQuery("F.NAME,F.DESCRIPTION", $DB->ForSql($val), "Y").") ";
					break;
				case "PERMS":
					if (is_array($val) && count($val)>1)
					{
						$arSqlSearch[] = "F.ID = FP.FORUM_ID AND FP.GROUP_ID IN (".$DB->ForSql($val[0]).") AND FP.PERMISSION > '".$DB->ForSql($val[1])."' ";
						$arSqlSearchFrom[] = "b_forum_perms FP";
					}
					break;
			}
		}

		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).") ";
		if (count($arSqlSearchFrom) > 0)
			$strSqlSearchFrom = ", ".implode(", ", $arSqlSearchFrom);

		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";
			if ($by == "ID") $arSqlOrder[] = " F.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " F.NAME ".$order." ";
			elseif ($by == "ACTIVE") $arSqlOrder[] = " F.ACTIVE ".$order." ";
			elseif ($by == "MODERATION") $arSqlOrder[] = " F.MODERATION ".$order." ";
			elseif ($by == "FORUM_GROUP_ID") $arSqlOrder[] = " F.FORUM_GROUP_ID ".$order." ";
			elseif ($by == "TOPICS") $arSqlOrder[] = " F.TOPICS ".$order." ";
			elseif ($by == "POSTS") $arSqlOrder[] = " F.POSTS ".$order." ";
			elseif ($by == "POSTS_UNAPPROVED") $arSqlOrder[] = " F.POSTS_UNAPPROVED ".$order." ";
			elseif ($by == "LAST_POST_DATE") $arSqlOrder[] = " F.LAST_POST_DATE ".$order." ";
			else
			{
				$arSqlOrder[] = " F.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);
		
		$strSql = 
			"SELECT F_FORUM.*, F.NAME, F.DESCRIPTION, F.ACTIVE, F.MODERATION, F.INDEXATION, F.ALLOW_MOVE_TOPIC, '' as LID, 
				F.TOPICS, F.POSTS, F.LAST_POSTER_ID, F.LAST_POSTER_NAME, 
				".$DB->DateToCharFunction("F.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, 
				F.LAST_MESSAGE_ID, F.LAST_MESSAGE_ID as MID, 
				F.POSTS_UNAPPROVED, F.ABS_LAST_POSTER_ID, F.ABS_LAST_POSTER_NAME, 
				".$DB->DateToCharFunction("F.ABS_LAST_POST_DATE", "FULL")." as ABS_LAST_POST_DATE, 
				F.ABS_LAST_MESSAGE_ID, F.SORT, F.ORDER_BY, 
				F.ORDER_DIRECTION, F.ALLOW_HTML, F.ALLOW_ANCHOR, F.ALLOW_BIU, 
				F.ALLOW_IMG, F.ALLOW_VIDEO, F.ALLOW_LIST, F.ALLOW_QUOTE, F.ALLOW_CODE, 
				F.ALLOW_FONT, F.ALLOW_SMILES, F.ALLOW_UPLOAD, F.EVENT1, F.EVENT2, 
				F.EVENT3, F.ALLOW_NL2BR, '' as PATH2FORUM_MESSAGE, F.ALLOW_UPLOAD_EXT, F.ALLOW_TOPIC_TITLED, 
				F.FORUM_GROUP_ID, F.ASK_GUEST_EMAIL, F.USE_CAPTCHA 
			FROM 
			(
				SELECT F.ID ".$strSqlSelect."
				FROM b_forum F 
					".$strSqlSearchFrom." 
				WHERE (1=1 ".$strSqlSearch.")
				GROUP BY F.ID
			) F_FORUM
			INNER JOIN b_forum F ON (F_FORUM.ID = F.ID)
			".$strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetListEx($arOrder = Array("SORT"=>"ASC"), $arFilter = Array())
	{
		global $DB;
		$arSqlSearch = array();
		$orSqlSearch = array();
		$arSqlSelect = array();
		$arSqlFrom = array();
		$arSqlOrder = array();
		$strSqlSearch = "";
		$strSqlSelect = "";
		$strSqlSearchOR = "";
		$strSqlFrom = "";
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "LID": 
				case "SITE_ID":
					if (strLen($val) <= 0):
						continue;
					endif;
					$arSqlFrom["F2S"] = "
					INNER JOIN b_forum2site F2S ON (F2S.FORUM_ID=F.ID)";
					$arSqlSearch[] = ($strNegative=="Y"?" NOT ":"")."(F2S.SITE_ID ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				case "INDEXATION":
				case "ACTIVE":
				case "XML_ID":
				case "ALLOW_MOVE_TOPIC":
					if (strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(F.".$key." IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(F.".$key.") <= 0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" F.".$key." IS NULL OR NOT ":"")."(F.".$key." ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				case "ID":
				case "FORUM_GROUP_ID":
				case "TOPICS":
				case "POSTS":
					if ($strOperation == "IN")
					{
						if (is_array($val))
						{
							$val_int = array();
							foreach ($val as $v)
								$val_int[] = intVal($v);
							$val = implode(", ", $val_int);
						}
						$val = trim($val);
					}
					if (($strOperation == "IN" && strLen($val) <= 0) || intVal($val) <= 0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(F.".$key." IS NULL OR F.".$key."<=0)";
					elseif ($strOperation == "IN")
						$arSqlSearch[] = ($strNegative=="Y"?" NOT ":"")."(F.".$key." IN (".$DB->ForSql($val)."))";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" F.".$key." IS NULL OR NOT ":"")."(F.".$key." ".$strOperation." ".intVal($val)." )";
					break;
				case "TEXT":
					$arSqlSearch[] = " (".GetFilterQuery("F.NAME,F.DESCRIPTION", $DB->ForSql($val), "Y").") ";
					break;
				case "PERMS":
					if (!is_array($val) || count($val) <= 0 ):
						continue;
					endif;
					$arSqlFrom["FP"] = "
					INNER JOIN b_forum_perms FP ON (F.ID = FP.FORUM_ID)";
					if (strToUpper($val[1]) == "ALLOW_MOVE_TOPIC")
						$arSqlSearch[] = "FP.GROUP_ID IN (".$DB->ForSql($val[0]).") AND ((FP.PERMISSION > 'M') OR (F.ALLOW_MOVE_TOPIC = 'Y'))";
					else
						$arSqlSearch[] = "FP.GROUP_ID IN (".$DB->ForSql($val[0]).") AND FP.PERMISSION > '".$DB->ForSql($val[1])."' ";
					break;
				case "APPROVED":
					if (strLen($val) <= 0):
						continue;
					endif;
					$arSqlFrom["FMM"] = "
					LEFT JOIN b_forum_message FMM ON (FMM.FORUM_ID=F.ID AND (FMM.APPROVED ".$strOperation." '".$DB->ForSql($val)."'))";
					$arSqlSelect["FMM"] = "count(FMM.ID) MCNT";
					break;
				case "RENEW":
					$val = intVal($val);
					if ($val <= 0):
						continue;
					endif;
					
					$perms = "NOT_CHECK";
					$arUserGroups = $GLOBALS["USER"]->GetGroups();
					if (is_set($arFilter, "PERMS")):
						$perms = "NORMAL";
						$arUserGroups = $arFilter["PERMS"][0];
					elseif (is_set($arFilter, "APPROVED") && $arFilter["APPROVED"] == "Y"):
						$perms = "ONLY_APPROVED";
					endif;
					
					$arSqlSelect["TCRENEW"] = "MAX(BFF.TCRENEW) AS TCRENEW";
					$arSqlFrom["RENEW"] = "
					LEFT JOIN (
						SELECT BF.ID AS RENEW_FORUM_ID, COUNT(FT_RENEW.ID) TCRENEW
						FROM b_forum BF 
						".
							($perms == "NORMAL" ? "
						LEFT JOIN
						(
							SELECT FPP.FORUM_ID, MAX(FPP.PERMISSION) AS PERMISSION
							FROM b_forum_perms FPP
							WHERE FPP.GROUP_ID IN (".$arUserGroups.")
							GROUP BY FPP.FORUM_ID
						) FP ON (FP.FORUM_ID = BF.ID)" : 
						""
							).
						"
						LEFT JOIN b_forum_user_forum FUF ON (FUF.USER_ID=".$val." AND FUF.FORUM_ID = BF.ID)
						LEFT JOIN b_forum_user_forum FUF_ALL ON (FUF_ALL.USER_ID=".$val." AND FUF_ALL.FORUM_ID = 0)
						LEFT JOIN b_forum_topic FT_RENEW ON (BF.ID = FT_RENEW.FORUM_ID AND FT_RENEW.STATE != 'L' AND (FUF_ALL.LAST_VISIT IS NULL OR FT_RENEW.ABS_LAST_POST_DATE > FUF_ALL.LAST_VISIT))
						LEFT JOIN b_forum_user_topic FUT_RENEW ON (FUT_RENEW.FORUM_ID = BF.ID AND FUT_RENEW.TOPIC_ID = FT_RENEW.ID AND FUT_RENEW.USER_ID=".$val.") 
						WHERE 
						(
							FUT_RENEW.LAST_VISIT IS NULL 
							AND 
							(
								(FUF_ALL.LAST_VISIT IS NULL AND FUF.LAST_VISIT IS NULL)
								OR 
								(
									FUF.LAST_VISIT IS NOT NULL
									AND 
						".
							( $perms == "NORMAL" ? "
										(FP.PERMISSION >= 'Q' AND FUF.LAST_VISIT < FT_RENEW.ABS_LAST_POST_DATE)
										OR 
										(FT_RENEW.APPROVED = 'Y' AND FUF.LAST_VISIT < FT_RENEW.LAST_POST_DATE)
										" : 
								( $perms == "NOT_CHECK" ? "
										(FUF.LAST_VISIT < FT_RENEW.ABS_LAST_POST_DATE OR FUF.LAST_VISIT < FT_RENEW.LAST_POST_DATE)
										" : 
										"
										(FT_RENEW.APPROVED = 'Y' AND FUF.LAST_VISIT < FT_RENEW.LAST_POST_DATE)
										"
								)
							)
						."
								)
								OR 
								(
									FUF.LAST_VISIT IS NULL AND FUF_ALL.LAST_VISIT IS NOT NULL 
									AND 
									(
						".
							( $perms == "NORMAL" ? "
										(FP.PERMISSION >= 'Q' AND FUF_ALL.LAST_VISIT < FT_RENEW.ABS_LAST_POST_DATE)
										OR 
										(FT_RENEW.APPROVED = 'Y' AND FUF_ALL.LAST_VISIT < FT_RENEW.LAST_POST_DATE)
										" : 
								( $perms == "NOT_CHECK" ? "
										(FUF_ALL.LAST_VISIT < FT_RENEW.ABS_LAST_POST_DATE OR FUF_ALL.LAST_VISIT < FT_RENEW.LAST_POST_DATE)
										" : 
										"
										(FT_RENEW.APPROVED = 'Y' AND FUF_ALL.LAST_VISIT < FT_RENEW.LAST_POST_DATE)
										"
								)
							)
						."
									)
								)
							)
						)
						OR
						(
							FUT_RENEW.LAST_VISIT IS NOT NULL 
							AND
						".
							( $perms == "NORMAL" ? "
										(FP.PERMISSION >= 'Q' AND FUT_RENEW.LAST_VISIT < FT_RENEW.ABS_LAST_POST_DATE)
										OR 
										(FT_RENEW.APPROVED = 'Y' AND FUT_RENEW.LAST_VISIT < FT_RENEW.LAST_POST_DATE)
										" : 
								( $perms == "NOT_CHECK" ? "
										(FUT_RENEW.LAST_VISIT < FT_RENEW.ABS_LAST_POST_DATE OR FUT_RENEW.LAST_VISIT < FT_RENEW.LAST_POST_DATE)
										" : 
										"
										(FT_RENEW.APPROVED = 'Y' AND FUT_RENEW.LAST_VISIT < FT_RENEW.LAST_POST_DATE)
										"
								)
							)
						."
						)
						GROUP BY BF.ID".
							( $perms == "NORMAL" ? ", FP.PERMISSION" : "")."
					) BFF ON (BFF.RENEW_FORUM_ID = F.ID)
					";
				break;
			}
		}

		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).") ";
		if (count($orSqlSearch) > 0)
			$strSqlSearchOR = " OR (".implode(") AND (", $orSqlSearch).") ";
		if (count($arSqlSelect) > 0)
			$strSqlSelect = ", ".implode(", ", $arSqlSelect); 
		if (count($arSqlFrom) > 0)
			$strSqlFrom = " ".implode(" ", $arSqlFrom); 
		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC".($DB->type=="ORACLE"?" NULLS LAST":"");
			else $order = "ASC".($DB->type=="ORACLE"?" NULLS FIRST":"");

			if ($by == "ID") $arSqlOrder[] = " F_FORUM.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " F.NAME ".$order." ";
			elseif ($by == "ACTIVE") $arSqlOrder[] = " F.ACTIVE ".$order." ";
			elseif ($by == "MODERATION") $arSqlOrder[] = " F.MODERATION ".$order." ";
			elseif ($by == "FORUM_GROUP_ID") $arSqlOrder[] = " F.FORUM_GROUP_ID ".$order." ";
			elseif ($by == "FORUM_GROUP_SORT") $arSqlOrder[] = " FG.SORT ".$order." ";
			elseif ($by == "TOPICS") $arSqlOrder[] = " F.TOPICS ".$order." ";
			elseif ($by == "POSTS") $arSqlOrder[] = " F.POSTS ".$order." ";
			elseif ($by == "POSTS_UNAPPROVED") $arSqlOrder[] = " F.POSTS_UNAPPROVED ".$order." ";
			elseif ($by == "LAST_POST_DATE") $arSqlOrder[] = " F.LAST_POST_DATE ".$order." ";
			elseif ($by == "ABS_LAST_POST_DATE") $arSqlOrder[] = " F.ABS_LAST_POST_DATE ".$order." ";
			else
			{
				$arSqlOrder[] = " F.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = 
			"SELECT F_FORUM.*, F.FORUM_GROUP_ID, F.NAME, F.DESCRIPTION, F.SORT, F.ACTIVE, 
				F.ALLOW_HTML, F.ALLOW_ANCHOR, F.ALLOW_BIU, F.ALLOW_IMG, F.ALLOW_VIDEO, 
				F.ALLOW_LIST, F.ALLOW_QUOTE, F.ALLOW_CODE, F.ALLOW_FONT, F.ALLOW_SMILES, 
				F.ALLOW_UPLOAD, F.ALLOW_UPLOAD_EXT, F.ALLOW_MOVE_TOPIC, 
				F.ALLOW_NL2BR,  F.ALLOW_TOPIC_TITLED, F.ALLOW_KEEP_AMP,
				'' as PATH2FORUM_MESSAGE, 
				F.ASK_GUEST_EMAIL, F.USE_CAPTCHA, F.MODERATION, F.INDEXATION, 
				F.ORDER_BY, F.ORDER_DIRECTION, 
				'' as LID, '' as DIR, 
				F.TOPICS, 
				F.POSTS, F.LAST_POSTER_ID, F.LAST_POSTER_NAME, 
				".$DB->DateToCharFunction("F.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, 
				F.LAST_MESSAGE_ID, FM.TOPIC_ID as TID, F.LAST_MESSAGE_ID as MID, 
				F.POSTS_UNAPPROVED, F.ABS_LAST_POSTER_ID, F.ABS_LAST_POSTER_NAME, 
				".$DB->DateToCharFunction("F.ABS_LAST_POST_DATE", "FULL")." as ABS_LAST_POST_DATE, 
				F.ABS_LAST_MESSAGE_ID, FM_ABS.TOPIC_ID as ABS_TID,
				F.EVENT1, F.EVENT2, F.EVENT3, 
				FT.TITLE, FT_ABS.TITLE as ABS_TITLE, 
				F.HTML, FT.HTML AS TOPIC_HTML, FT_ABS.HTML AS ABS_TOPIC_HTML
			FROM 
			(
				SELECT F.ID ".$strSqlSelect."
				FROM b_forum F 
					".$strSqlFrom." 
				WHERE (1=1 ".$strSqlSearch.")
					".$strSqlSearchOR."
				GROUP BY F.ID
			) F_FORUM
			INNER JOIN b_forum F ON (F_FORUM.ID = F.ID)
			LEFT JOIN b_forum_group FG ON F.FORUM_GROUP_ID = FG.ID 
			LEFT JOIN b_forum_message FM ON F.LAST_MESSAGE_ID = FM.ID 
			LEFT JOIN b_forum_topic FT ON FM.TOPIC_ID = FT.ID 
			LEFT JOIN b_forum_message FM_ABS ON F.ABS_LAST_MESSAGE_ID = FM_ABS.ID 
			LEFT JOIN b_forum_topic FT_ABS ON FM_ABS.TOPIC_ID = FT_ABS.ID 
			".$strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (COption::GetOptionString("forum", "FILTER", "Y") == "N")
			return $db_res;
		$db_res = new _CForumDBResult($db_res);
		return $db_res;
	}
	
	function GetByID($ID)
	{
		global $DB, $CACHE_MANAGER;
		$ID = intVal($ID);
		$cache_id = "b_forum_".$ID;
		if ($ID <= 0):
			return false;
		elseif (!is_array($GLOBALS["FORUM_CACHE"]["FORUM"][$ID])):
			$GLOBALS["FORUM_CACHE"]["FORUM"][$ID] = array();
		endif;

		if (!array_key_exists("MAIN", $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]))
		{
			if (CACHED_b_forum !== false && $CACHE_MANAGER->Read(CACHED_b_forum, $cache_id, "b_forum"))
			{
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["MAIN"] = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$strSql = "SELECT F.ID, F.NAME, F.DESCRIPTION, F.ACTIVE, F.MODERATION, F.INDEXATION, F.ALLOW_MOVE_TOPIC, 
						'' as LID, F.TOPICS, F.POSTS, F.LAST_POSTER_ID, F.LAST_POSTER_NAME, 
						".$DB->DateToCharFunction("F.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, 
						F.LAST_MESSAGE_ID, F.LAST_MESSAGE_ID as MID, 
						F.POSTS_UNAPPROVED, F.ABS_LAST_POSTER_ID, F.ABS_LAST_POSTER_NAME, 
						".$DB->DateToCharFunction("F.ABS_LAST_POST_DATE", "FULL")." as ABS_LAST_POST_DATE, 
						F.ABS_LAST_MESSAGE_ID, F.SORT, F.ORDER_BY, 
						F.ORDER_DIRECTION, F.ALLOW_HTML, F.ALLOW_ANCHOR, F.ALLOW_BIU, F.ALLOW_TOPIC_TITLED, 
						F.ALLOW_IMG, F.ALLOW_VIDEO, F.ALLOW_LIST, F.ALLOW_QUOTE, F.ALLOW_CODE, 
						F.ALLOW_FONT, F.ALLOW_SMILES, F.ALLOW_UPLOAD, F.EVENT1, F.EVENT2, 
						F.EVENT3, F.ALLOW_NL2BR, '' as PATH2FORUM_MESSAGE, F.ALLOW_UPLOAD_EXT, 
						F.FORUM_GROUP_ID, F.ASK_GUEST_EMAIL, F.USE_CAPTCHA 
					FROM b_forum F 
					WHERE F.ID = ".$ID;
				$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["MAIN"] = $db_res->Fetch();
			if (CACHED_b_forum !== false)
				$CACHE_MANAGER->Set($cache_id, $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["MAIN"]);
			}
		}
		return $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]["MAIN"];
	}

	function GetByIDEx($ID, $SITE_ID = false)
	{
		global $DB, $CACHE_MANAGER;
		$ID = intVal($ID);
		$SITE_ID = ($SITE_ID == false || empty($SITE_ID) ? false : $SITE_ID);
		$key = ($SITE_ID == false ? "EX" : "EX_PATH_".strToUpper($SITE_ID));
		$cache_id = "b_forum_".$ID.strToLower($key);
		if ($ID <= 0):
			return false;
		elseif (!is_array($GLOBALS["FORUM_CACHE"]["FORUM"][$ID])):
			$GLOBALS["FORUM_CACHE"]["FORUM"][$ID] = array();
		endif;
		
		if (!array_key_exists($key, $GLOBALS["FORUM_CACHE"]["FORUM"][$ID]))
		{
			if (CACHED_b_forum !== false && $CACHE_MANAGER->Read(CACHED_b_forum, $cache_id, "b_forum"))
			{
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID][$key] = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$strSql = "SELECT F.ID, F.NAME, F.DESCRIPTION, F.ACTIVE, F.MODERATION, F.INDEXATION, 
						F.ALLOW_MOVE_TOPIC, '' as LID, F.TOPICS, F.POSTS, F.LAST_POSTER_ID, F.LAST_POSTER_NAME, 
						".$DB->DateToCharFunction("F.LAST_POST_DATE", "FULL")." as LAST_POST_DATE, 
						F.LAST_MESSAGE_ID, FM.TOPIC_ID as TID, F.LAST_MESSAGE_ID as MID, 
						F.POSTS_UNAPPROVED, F.ABS_LAST_POSTER_ID, F.ABS_LAST_POSTER_NAME, 
						".$DB->DateToCharFunction("F.ABS_LAST_POST_DATE", "FULL")." as ABS_LAST_POST_DATE, 
						F.ABS_LAST_MESSAGE_ID, FT.TITLE, F.SORT, '' as DIR, F.ORDER_BY, F.ORDER_DIRECTION, 
						F.ALLOW_HTML, F.ALLOW_ANCHOR, F.ALLOW_BIU, 
						F.ALLOW_IMG, F.ALLOW_VIDEO, F.ALLOW_LIST, F.ALLOW_QUOTE, F.ALLOW_CODE, F.ALLOW_TOPIC_TITLED, 
						F.ALLOW_FONT, F.ALLOW_SMILES, F.ALLOW_UPLOAD, F.EVENT1, F.EVENT2, 
						F.EVENT3, F.ALLOW_NL2BR, ".(!$SITE_ID ? "''" : "FS.PATH2FORUM_MESSAGE")." as PATH2FORUM_MESSAGE, F.ALLOW_UPLOAD_EXT, 
						F.FORUM_GROUP_ID, F.ASK_GUEST_EMAIL, F.USE_CAPTCHA, F.HTML, FT.HTML AS TOPIC_HTML 
					FROM b_forum F 
						LEFT JOIN b_forum_group FG ON (F.FORUM_GROUP_ID = FG.ID) ".
						(!$SITE_ID ? "" : "
						LEFT JOIN b_forum2site FS ON (F.ID = FS.FORUM_ID AND FS.SITE_ID = '".$DB->ForSql($SITE_ID)."') ")."
						LEFT JOIN b_forum_message FM ON (F.LAST_MESSAGE_ID = FM.ID) 
						LEFT JOIN b_forum_topic FT ON (FM.TOPIC_ID = FT.ID) 
					WHERE (F.ID=".$ID.")";
				$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if ($db_res && COption::GetOptionString("forum", "FILTER", "Y") == "Y")
					$db_res = new _CForumDBResult($db_res);
				$GLOBALS["FORUM_CACHE"]["FORUM"][$ID][$key] = $db_res->Fetch();
				if (CACHED_b_forum !== false)
					$CACHE_MANAGER->Set($cache_id, $GLOBALS["FORUM_CACHE"]["FORUM"][$ID][$key]);
			}
		}
		return $GLOBALS["FORUM_CACHE"]["FORUM"][$ID][$key];
	}

	//---------------> Forum labels
	function InitReadLabels($ID, $arUserGroups) // out-of-date function
	{
		$ID = intVal($ID);
		if ($ID <= 0) 
			return false;

		$arForumCookie = array();
		$iCurFirstReadForum = 0;
		
		$read_forum_cookie = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_0";
		if (isset($_COOKIE[$read_forum_cookie]) && strlen($_COOKIE[$read_forum_cookie]) > 0)
		{
			$arForumCookie = explode("/", $_COOKIE[$read_forum_cookie]);
			$i = 0;
			while ($i < count($arForumCookie))
			{
				if (intVal($arForumCookie[$i]) == $ID)
				{
					$iCurFirstReadForum = intVal($arForumCookie[$i+1]);
					break;
				}
				$i += 2;
			}
		}

		$read_forum_cookie1 = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_".$ID;
		if (isset($_COOKIE[$read_forum_cookie1]) && intVal($_COOKIE[$read_forum_cookie1]) > 0)
		{
			if ($iCurFirstReadForum < intVal($_COOKIE[$read_forum_cookie1]))
			{
				$iCurFirstReadForum = intVal($_COOKIE[$read_forum_cookie1]);
			}
		}

		if (strlen($_SESSION["first_read_forum_".$ID])<=0 || intVal($_SESSION["first_read_forum_".$ID])<0)
		{
			$_SESSION["first_read_forum_".$ID] = $iCurFirstReadForum;
		}

		if (is_null($_SESSION["read_forum_".$ID]) || strlen($_SESSION["read_forum_".$ID])<=0)
		{
			$_SESSION["read_forum_".$ID] = "0";
		}

		$iLastPostID = 0;
		$strPerms = CForumNew::GetUserPermission($ID, $arUserGroups);
		if ($strPerms > "Q"):
			$db_res = CForumMessage::GetList(array("ID"=>"DESC"), array("FORUM_ID" => $ID, "APPROVED" => "N"), false, 1);
			if ($db_res && $res = $db_res->Fetch()):
				$iLastPostID = intVal($res["ID"]);
			endif;
		endif;
		if ($iLastPostID <= 0):
			$res = CForumNew::GetByID($ID);
			$iLastPostID = intVal($res["LAST_MESSAGE_ID"]);
		endif;
		
		if ($iLastPostID > 0)
		{
			$i = 0;
			$arCookieVal = array();
			while ($i < count($arForumCookie))
			{
				if (intVal($arForumCookie[$i]) != $ID)
				{
					$arCookieVal[] = intVal($arForumCookie[$i])."/".intVal($arForumCookie[$i + 1]);
				}
				$i += 2;
			}
			$arCookieVal[] = $ID."/".$iLastPostID;
			
			//$GLOBALS["APPLICATION"]->set_cookie($read_forum_cookie, $strCookieVal, false, "/", false, false, "Y", "");
			$GLOBALS["APPLICATION"]->set_cookie("FORUM_0", implode("/", $arCookieVal), false, "/", false, false, "Y", false);
		}
		return true;
	}

	function SetLabelsBeRead($ID, $arUserGroups) // out-of-date function
	{
		$ID = intVal($ID);
		$_SESSION["read_forum_".$ID] = "0";

		$strPerms = CForumNew::GetUserPermission($ID, $arUserGroups);
		$iCurFirstReadForum = 0;
		if ($strPerms > "Q"):
			$db_res = CForumMessage::GetList(array("ID"=>"DESC"), array("FORUM_ID" => $ID, "APPROVED" => "N"), false, 1);
			if ($db_res && $res = $db_res->Fetch()):
				$iCurFirstReadForum = intVal($res["ID"]);
			endif;
		endif;
		if ($iLastPostID <= 0):
			$res = CForumNew::GetByID($ID);
			$iCurFirstReadForum = intVal($res["LAST_MESSAGE_ID"]);
		endif;
		$_SESSION["first_read_forum_".$ID] = $iCurFirstReadForum;
		
		$arForumCookie = array();
		$read_forum_cookie = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_0";
		if (isset($_COOKIE[$read_forum_cookie]) && strlen($_COOKIE[$read_forum_cookie])>0)
		{
			$arForumCookie = explode("/", $_COOKIE[$read_forum_cookie]);
		}

		$i = 0;
		$arCookieVal = array();
		while ($i < count($arForumCookie))
		{
			if (intVal($arForumCookie[$i])!=$ID)
			{
				$arCookieVal[] = intVal($arForumCookie[$i])."/".intVal($arForumCookie[$i+1]);
			}
			$i += 2;
		}
		$arCookieVal[] = $ID."/".$iCurFirstReadForum;
		
		$_COOKIE[$read_forum_cookie] = implode("/", $arCookieVal);
//		$GLOBALS["APPLICATION"]->set_cookie($read_forum_cookie, $strCookieVal, false, "/", false, false, "Y", "");
		$GLOBALS["APPLICATION"]->set_cookie("FORUM_0", implode("/", $arCookieVal), false, "/", false, false, "Y", false);
		return true;
	}

	//---------------> Forum utils
	function SetStat($ID = 0, $arParams = array())
	{
		global $DB;
		$ID = intVal($ID);
		if ($ID <= 0):
			return false;
		endif;
		$arParams = (is_array($arParams) ? $arParams : array());
		$arMessage = (is_array($arParams["MESSAGE"]) ? $arParams["MESSAGE"] : array());
		if ($arMessage["FORUM_ID"] != $ID)
			$arMessage = array();

		$arForum = CForumNew::GetByID($ID);

		$arParams["ACTION"] = ($arParams["ACTION"] == "DECREMENT" || $arParams["ACTION"] == "UPDATE" ? $arParams["ACTION"] : "INCREMENT");
		$arParams["POSTS"] = intVal($arParams["POSTS"] > 0 ? $arParams["POSTS"] : 1);
		
		$arFields = array();

		if (empty($arMessage))
		{
			// full recount 
		}
		elseif ($arParams["ACTION"] == "INCREMENT")
		{
			if ($arMessage["ID"] > $arForum["ABS_LAST_MESSAGE_ID"]):
				$arFields["ABS_LAST_POSTER_ID"] = ((intVal($arMessage["AUTHOR_ID"])>0) ? $arMessage["AUTHOR_ID"] : false);
				$arFields["ABS_LAST_POSTER_NAME"] = $arMessage["AUTHOR_NAME"]; 
				$arFields["ABS_LAST_POST_DATE"] = $arMessage["POST_DATE"];
				$arFields["ABS_LAST_MESSAGE_ID"] = $arMessage["ID"];
				if ($arMessage["APPROVED"] == "Y"):
					$arFields["LAST_POSTER_ID"] = $arFields["ABS_LAST_POSTER_ID"];
					$arFields["LAST_POSTER_NAME"] = $arFields["ABS_LAST_POSTER_NAME"];
					$arFields["LAST_POST_DATE"] = $arFields["ABS_LAST_POST_DATE"];
					$arFields["LAST_MESSAGE_ID"] = $arFields["ABS_LAST_MESSAGE_ID"];
				endif;
			endif;

			if ($arMessage["APPROVED"] == "Y"):
				$arFields["=POSTS"] = "POSTS+1";
				if ($arMessage["NEW_TOPIC"] == "Y"):
					$arFields["=TOPICS"] = "TOPICS+1";
				endif;
			else:
				$arFields["=POSTS_UNAPPROVED"] = "POSTS_UNAPPROVED+1";
			endif;
		}
		elseif ($arMessage["ID"] > $arForum["ABS_LAST_MESSAGE_ID"])
		{
			// full recount 
		}
		elseif ($arParams["ACTION"] == "DECREMENT" && 
			($arMessage["ID"] == $arForum["ABS_LAST_MESSAGE_ID"] || $arMessage["ID"] == $arForum["LAST_MESSAGE_ID"]))
		{
			// full recount 
		}
		elseif ($arParams["ACTION"] == "DECREMENT")
		{
			if ($arMessage["APPROVED"] == "Y"):
				$arFields["=POSTS"] = "POSTS-1";
				if ($arMessage["NEW_TOPIC"] == "Y"):
					$arFields["=TOPICS"] = "TOPICS-1";
				endif;
			else:
				$arFields["=POSTS_UNAPPROVED"] = "POSTS_UNAPPROVED-1";
			endif;
		}
		elseif ($arParams["ACTION"] == "UPDATE")
		{
			if ($arMessage["APPROVED"] == "Y"):
				if ($arMessage["ID"] > $arForum["LAST_MESSAGE_ID"]):
					$arFields["LAST_POSTER_ID"] = ((intVal($arMessage["AUTHOR_ID"])>0) ? $arMessage["AUTHOR_ID"] : false);
					$arFields["LAST_POSTER_NAME"] = $arMessage["AUTHOR_NAME"]; 
					$arFields["LAST_POST_DATE"] = $arMessage["POST_DATE"];
					$arFields["LAST_MESSAGE_ID"] = $arMessage["ID"];
				endif;
				$arFields["=POSTS"] = "POSTS+1";
				$arFields["=POSTS_UNAPPROVED"] = "POSTS_UNAPPROVED-1";
				if ($arMessage["NEW_TOPIC"] == "Y"):
					$arFields["=TOPICS"] = "TOPICS+1";
				endif;
			elseif ($arMessage["ID"] != $arForum["LAST_MESSAGE_ID"]):
				$arFields["=POSTS"] = "POSTS-1";
				$arFields["=POSTS_UNAPPROVED"] = "POSTS_UNAPPROVED+1";
				if ($arMessage["NEW_TOPIC"] == "Y"):
					$arFields["=TOPICS"] = "TOPICS-1";
				endif;
			endif;
		}
		
		if (empty($arFields))
		{
			$res = CForumMessage::GetList(array(), array("FORUM_ID" => $ID), "cnt_not_approved");
			$res = (is_array($res) ? $res : array());
			$res["CNT"] = intVal($res["CNT"]) - intVal($res["CNT_NOT_APPROVED"]);
			$res["CNT"] = ($res["CNT"] > 0 ? $res["CNT"] : 0);
			
			$arFields = array(
				"TOPICS" => CForumTopic::GetList(array(), array("FORUM_ID" => $ID, "APPROVED" => "Y"), true), 
				"POSTS" => $res["CNT"], 
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
		}
		
		if (!CForumNew::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_forum", $arFields);
		if (!empty($arFields)):
			$res = array();
			foreach ($arFields as $key => $val):
				if (substr($key, 0, 1) == "="):
					$key = substr($key, 1);
					if (!empty($key)):
						$res[] = $key."=".(empty($val) ? $key."+1" : $val);
					endif;
				endif;
			endforeach;
			if (!empty($res)):
				$strUpdate = (empty($strUpdate) ? "" : $strUpdate.",");
				$strUpdate .= implode(", ", $res);
			endif;
		endif;
			
		if (empty($strUpdate))
			return false;
		$strSql = "UPDATE b_forum SET ".$strUpdate." WHERE ID=".$ID;
		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	function PreparePath2Message($strPath, $arVals = array())
	{
		if (strlen($strPath)<=0) 
			return "";
		$pattern = array("//", "#MESSAGE_ID#", "#MID#", "#TOPIC_ID#", "#TID#", "#FORUM_ID#", "#FID#", "#PARAM1#", "#PARAM2#", 
			"SOCNET_GROUP_ID", "OWNER_ID");
		$replace = array("/", $arVals["MESSAGE_ID"], $arVals["MESSAGE_ID"], $arVals["TOPIC_ID"], $arVals["TOPIC_ID"], 
			$arVals["FORUM_ID"], $arVals["FORUM_ID"], $arVals["PARAM1"], $arVals["PARAM2"], $arVals["SOCNET_GROUP_ID"], $arVals["OWNER_ID"]);
		return str_replace($pattern, $replace, $strPath);
	}

	//---------------> Forum actions
	function OnGroupDelete($GROUP_ID)
	{
		global $DB;
		return $DB->Query("DELETE FROM b_forum_perms WHERE GROUP_ID=".intVal($GROUP_ID), true);
	}

	function OnBeforeLangDelete($lang)
	{
		global $DB;
		$r = CForumNew::GetList(array(), array("LID"=>$lang));
		return ($r->Fetch()?false:true);
	}

	function OnPanelCreate() // out-of-date function
	{
		return false;
	}

	function ShowPanel($FID, $TID=0, $bGetIcons=false)
	{
		global $APPLICATION, $REQUEST_URI, $USER;

		if(!(($USER->IsAuthorized() || $APPLICATION->ShowPanel===true) && $APPLICATION->ShowPanel!==false))
			return;
		if (!CModule::IncludeModule("forum"))
			return;
		$arButtons = array();
				
		$module_permission = $APPLICATION->GetGroupRight("forum");
		if ($module_permission > "D")
		{
			$arButtons[] = array(
				"TEXT" => GetMessage("F_FORUMS_LIST"),
				"IMAGE" => "/bitrix/images/forum/toolbar_button1.gif",
				"ACTION" => "jsUtils.Redirect(arguments, '/bitrix/admin/forum_admin.php')");

			if ($module_permission >= "W" && intVal($FID) > 0 && 
				CForumNew::CanUserUpdateForum($FID, $USER->GetUserGroupArray(), $USER->GetID()))
			{
				$arButtons[] = array(
					"TEXT" => GetMessage("F_FORUM_EDIT"),
					"IMAGE" => "/bitrix/images/forum/toolbar_button2.gif",
					"ACTION" => "jsUtils.Redirect(arguments, '/bitrix/admin/forum_edit.php?ID=".intVal($FID)."')");
			}
		}
		if (!empty($arButtons))
		{
			$arButton = array(
				"SRC" => "/bitrix/images/forum/toolbar_button1.gif",
				"ALT" => GetMessage("F_FORUM_TITLE"),
				"TEXT" => GetMessage("F_FORUM"),
				"MAIN_SORT" => 300,
				"MENU" => $arButtons,
				"MODE" => 'configure');
			$APPLICATION->AddPanelButton($arButton);
		}
	}

	function ClearHTML($ID)
	{
		global $DB;
		$ID = intVal($ID);
		$strSql = "UPDATE b_forum_message SET POST_MESSAGE_HTML='', POST_MESSAGE_FILTER='', HTML = '' WHERE FORUM_ID=".$ID;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
		
	}
}

/**********************************************************************/
/************** FORUM GROUP *******************************************/
/**********************************************************************/
class CAllForumGroup
{
	//---------------> User insert, update, delete
	function CanUserAddGroup($arUserGroups)
	{
		if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W") 
			return true;
		return false;
	}

	function CanUserUpdateGroup($ID, $arUserGroups)
	{
		if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W") 
			return true;
		return false;
	}

	function CanUserDeleteGroup($ID, $arUserGroups)
	{
		if (in_array(1, $arUserGroups) || $GLOBALS["APPLICATION"]->GetGroupRight("forum", $arUserGroups) >= "W") 
			return true;
		return false;
	}

	function CheckFields($ACTION, &$arFields, $ID = false)
	{
		$aMsg = array();

		if (is_set($arFields, "LANG") || $ACTION=="ADD")
		{
			$res = (is_array($arFields["LANG"]) ? $arFields["LANG"] : array());
			foreach ($res as $i => $val)
			{
				if (empty($res[$i]["LID"]) || empty($res[$i]["NAME"]))
				{
					unset($res[$i]);
				}
			}
			$db_lang = CLanguage::GetList(($b="sort"), ($o="asc"));
			while ($arLang = $db_lang->Fetch())
			{
				$bFound = false;
				foreach ($res as $i => $val) 
				{
					if ($res[$i]["LID"]==$arLang["LID"])
						$bFound = true;
				}
				if (!$bFound) 
				{
					$aMsg[] = array(
						"id"=>'FORUM_GROUP[LANG]['.$arLang["LID"].'][NAME]', 
						"text" => GetMessage("FG_ERROR_EMPTY_LID", 
							array("#LID#" => $arLang["LID"], "#LID_NAME#" => $arLang["NAME"])));
				}
			}
		}
		
		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && intVal($arFields["SORT"])<=0) $arFields["SORT"] = 150;
		if ((is_set($arFields, "PARENT_ID") || $ACTION=="ADD")) {$arFields["PARENT_ID"] = intVal($arFields["PARENT_ID"]) > 0 ? intVal($arFields["PARENT_ID"]) : false;}
		if ($arFields["PARENT_ID"])
		{
			if ($ACTION != "ADD" && $ID == $arFields["PARENT_ID"])
			{
				$aMsg[] = array(
					"id" => 'FORUM_GROUP[PARENT_ID]', 
					"text" => GetMessage("FG_ERROR_SELF_PARENT_ID"));
			}
			else
			{
				$res = CForumGroup::GetByID($arFields["PARENT_ID"]);
				if (!$res)
				{
					$aMsg[] = array(
						"id" => 'FORUM_GROUP[PARENT_ID]', 
						"text" => GetMessage("FG_ERROR_EMPTY_PARENT_ID"));
				}
				elseif ($ACTION != "ADD")
				{
					$res1 = CForumGroup::GetByID($ID);
					if ($res1["LEFT_MARGIN"] < $res["LEFT_MARGIN"] && $res["RIGHT_MARGIN"] < $res1["RIGHT_MARGIN"])
						$aMsg[] = array(
							"id" => 'FORUM_GROUP[PARENT_ID]', 
							"text" => GetMessage("FG_ERROR_PARENT_ID_IS_CHILD"));
				}
			}
		}
		if(!empty($aMsg))
		{
			$e = new CAdminException(array_reverse($aMsg));
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		return true;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = intVal($ID);
		$aMsg = array();
		$res = CForumGroup::GetByIDEx($ID, LANGUAGE_ID);
		if (!$res)
			return true;

		$db_res = CForumGroup::GetList(array(), array("PARENT_ID" => $ID));
		if ($db_res->Fetch())
			$aMsg[] = array(
				"id" => 'FORUM_GROUP_GROUPS', 
				"text" => str_replace(array("#GROUP_NAME#", "#GROUP_ID#"), array($res["NAME"], $ID), GetMessage("FG_ERROR_CONTENT_GROUP")));
		$db_res = CForumNew::GetList(array(), array("FORUM_GROUP_ID" => $ID));
		if ($db_res->Fetch())
			$aMsg[] = array(
				"id" => 'FORUM_GROUP_FORUMS', 
				"text" => str_replace(array("#GROUP_NAME#", "#GROUP_ID#"), array($res["NAME"], $ID), GetMessage("FG_ERROR_CONTENT_FORUM")));
		if(!empty($aMsg))
		{
			$e = new CAdminException(array_reverse($aMsg));
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		if(CACHED_b_forum_group !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_group");
		$DB->Query("DELETE FROM b_forum_group_lang WHERE FORUM_GROUP_ID = ".$ID, true);
		$DB->Query("DELETE FROM b_forum_group WHERE ID = ".$ID, true);
		CAllForumGroup::Resort();

		return true;
	}

	function GetList($arOrder = array("SORT"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		$arSqlOrder = Array();
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];
			
			switch ($key)
			{
				case "ID":
				case "SORT":
				case "PARENT_ID":
				case "LEFT_MARGIN":
				case "RIGHT_MARGIN":
				case "DEPTH_LEVEL":
					if (intVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.".$key." IS NULL OR FR.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.".$key." IS NULL OR NOT ":"")."(FR.".$key." ".$strOperation." ".intVal($val)." )";
					break;
			}
		}
		if (!empty($arSqlSearch))
			$strSqlSearch = "WHERE (".implode(") AND (", $arSqlSearch).") ";
			
		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " FR.ID ".$order." ";
			elseif ($by == "LEFT_MARGIN") $arSqlOrder[] = " FR.LEFT_MARGIN ".$order." ";
			else
			{
				$arSqlOrder[] = " FR.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (!empty($arSqlOrder))
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.PARENT_ID, FR.LEFT_MARGIN, FR.RIGHT_MARGIN, FR.DEPTH_LEVEL ".
			"FROM b_forum_group FR ".
			$strSqlSearch." ".
			$strSqlOrder." ";
			
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetListEx($arOrder = array("SORT"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		$arSqlOrder = Array();
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "SORT":
				case "PARENT_ID":
				case "LEFT_MARGIN":
				case "RIGHT_MARGIN":
				case "DEPTH_LEVEL":
					if (intVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.".$key." IS NULL OR FR.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.".$key." IS NULL OR NOT ":"")."(FR.".$key." ".$strOperation." ".intVal($val)." )";
					break;
				case "LID":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FRL.LID IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(FRL.LID)<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FRL.LID IS NULL OR NOT ":"")."(FRL.LID ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
			}
		}
		if (!empty($arSqlSearch))
			$strSqlSearch = " WHERE (".implode(") AND (", $arSqlSearch).") ";

		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " FR.ID ".$order." ";
			elseif ($by == "LID") $arSqlOrder[] = " FRL.LID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " FRL.NAME ".$order." ";
			elseif ($by == "LEFT_MARGIN") $arSqlOrder[] = " FR.LEFT_MARGIN ".$order." ";
			else
			{
				$arSqlOrder[] = " FR.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		
		if (!empty($arSqlOrder))
			$strSqlOrder = "ORDER BY ".implode(", ", $arSqlOrder);
			
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.PARENT_ID, FR.LEFT_MARGIN, FR.RIGHT_MARGIN, FR.DEPTH_LEVEL, FRL.LID, FRL.NAME, FRL.DESCRIPTION ".
			"FROM b_forum_group FR ".
			"	LEFT JOIN b_forum_group_lang FRL ON FR.ID = FRL.FORUM_GROUP_ID ".
			$strSqlSearch." ".
			$strSqlOrder." ";
			
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetByID($ID)
	{
		global $DB;
		$ID = intVal($ID);
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.PARENT_ID, FR.LEFT_MARGIN, FR.RIGHT_MARGIN, FR.DEPTH_LEVEL FROM b_forum_group FR WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return false;
	}

	function GetByIDEx($ID, $LANGUAGE_ID)
	{
		global $DB, $CACHE_MANAGER;
		$ID = intVal($ID);
		$LANGUAGE_ID = (!empty($LANGUAGE_ID) ? $LANGUAGE_ID : LANGUAGE_ID);
		$key = $ID."_".$LANGUAGE_ID;
		$cache_id = "b_forum_group".$key;
		if ($ID <= 0):
			return false;
		elseif (!is_array($GLOBALS["FORUM_CACHE"]["GROUP"])):
			$GLOBALS["FORUM_CACHE"]["GROUP"] = array();
		endif;
		
		if (!array_key_exists($key, $GLOBALS["FORUM_CACHE"]["GROUP"]))
		{
			if (CACHED_b_forum_group !== false && $CACHE_MANAGER->Read(CACHED_b_forum_group, $cache_id, "b_forum_group"))
			{
				$GLOBALS["FORUM_CACHE"]["GROUP"][$key] = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$strSql = "SELECT FR.ID, FRL.LID, FRL.NAME, FR.SORT, FRL.DESCRIPTION, 
					FR.PARENT_ID, FR.LEFT_MARGIN, FR.RIGHT_MARGIN, FR.DEPTH_LEVEL ".
					"FROM b_forum_group FR ".
					"	LEFT JOIN b_forum_group_lang FRL ON (FR.ID = FRL.FORUM_GROUP_ID AND FRL.LID = '".$DB->ForSql($LANGUAGE_ID)."') ".
					"WHERE FR.ID = ".$ID."";
				$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$GLOBALS["FORUM_CACHE"]["GROUP"][$key] = $db_res->Fetch();
				if (CACHED_b_forum_group !== false)
					$CACHE_MANAGER->Set($cache_id, $GLOBALS["FORUM_CACHE"]["GROUP"][$key]);
			}
		}
		return $GLOBALS["FORUM_CACHE"]["GROUP"][$key];
	}

	function GetLangByID($FORUM_GROUP_ID, $strLang)
	{
		global $DB;
		$FORUM_GROUP_ID = intVal($FORUM_GROUP_ID);
		
		$strSql = 
			"SELECT FRL.ID, FRL.FORUM_GROUP_ID, FRL.LID, FRL.NAME, FRL.DESCRIPTION ".
			"FROM b_forum_group_lang FRL ".
			"WHERE FRL.FORUM_GROUP_ID = ".$FORUM_GROUP_ID." ".
			"	AND FRL.LID = '".$DB->ForSql($strLang)."' ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return false;
	}
	
	function GetByLang($LANGUAGE_ID)
	{
		global $CACHE_MANAGER;
		$LANGUAGE_ID = (!empty($LANGUAGE_ID) ? $LANGUAGE_ID : LANGUAGE_ID);
		$cache_id = "b_forum_group".$LANGUAGE_ID;
		
		if (!is_array($GLOBALS["FORUM_CACHE"]["GROUPS"])):
			$GLOBALS["FORUM_CACHE"]["GROUPS"] = array();
		endif;
		
		if (!array_key_exists($LANGUAGE_ID, $GLOBALS["FORUM_CACHE"]["GROUPS"]))
		{
			if (CACHED_b_forum_group !== false && $CACHE_MANAGER->Read(CACHED_b_forum_group, $cache_id, "b_forum_group"))
			{
				$GLOBALS["FORUM_CACHE"]["GROUPS"][$LANGUAGE_ID] = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$arRes = array();
				$db_res = CForumGroup::GetListEx(array("LEFT_MARGIN" => "ASC", "SORT" => "ASC"), array("LID" => $LANGUAGE_ID));
				while ($res = $db_res->GetNext())
					$arRes[intVal($res["ID"])] = $res;
				$GLOBALS["FORUM_CACHE"]["GROUPS"][$LANGUAGE_ID] = $arRes;
				if (CACHED_b_forum_group !== false)
					$CACHE_MANAGER->Set($cache_id, $GLOBALS["FORUM_CACHE"]["GROUPS"][$LANGUAGE_ID]);
			}
		}
		return $GLOBALS["FORUM_CACHE"]["GROUPS"][$LANGUAGE_ID];
	}
	
	function Resort($ID=0, $cnt=0, $depth=0)
	{
		global $DB;
		$ID = intVal($ID);
		if($ID > 0)
			$DB->Query("UPDATE b_forum_group SET RIGHT_MARGIN=".intVal($cnt).", LEFT_MARGIN=".intVal($cnt)." WHERE ID=".intVal($ID));
		
		$strSql = "SELECT FG.ID, FG.PARENT_ID FROM b_forum_group FG WHERE ".($ID>0?"FG.PARENT_ID=".$ID:"FG.PARENT_ID IS NULL")." ORDER BY FG.SORT ASC";
		$cnt++;
		$db_res = $DB->Query($strSql);
		while ($res = $db_res->Fetch())
			$cnt = CAllForumGroup::ReSort($res["ID"], $cnt, $depth+1);
		if($ID == 0)
			return true;
		$DB->Query("UPDATE b_forum_group SET RIGHT_MARGIN=".intVal($cnt).", DEPTH_LEVEL=".intVal($depth)." WHERE ID=".intVal($ID));
		return $cnt+1;
	}
}

/**********************************************************************/
/************** FORUM SMILE *******************************************/
/**********************************************************************/
class CAllForumSmile
{
	//---------------> User insert, update, delete
	function CheckFields($ACTION, &$arFields)
	{
		$aMsg = array();

		if ((is_set($arFields, "TYPE") || $ACTION=="ADD") && $arFields["TYPE"]!="I" && $arFields["TYPE"]!="S")
		{
			if (empty($arFields["TYPE"]))
			{
				$aMsg[] = array(
					"id" => "TYPE", 
					"text" => GetMessage("FS_ERROR_EMPTY_TYPE"));
			}
			else 
			{
				$aMsg[] = array(
					"id" => "TYPE", 
					"text" => GetMessage("FS_ERROR_UNKNOWN_TYPE", array("#TYPE#" => $arFields["TYPE"])));
			}
		}
		if (is_set($arFields, "TYPING") && !empty($arFields["TYPING"]))
		{
			if (preg_match("/[\<\>\"\']/is", $arFields["TYPING"]) != false)
			{
				$aMsg[] = array(
					"id" => "TYPING", 
					"text" => GetMessage("FS_ERROR_TYPING"));
			}
		}
		
		if ((is_set($arFields, "IMAGE") || $ACTION=="ADD") && empty($arFields["IMAGE"]))
		{
			$aMsg[] = array(
				"id" => "IMAGE", 
				"text" => GetMessage("FS_ERROR_EMPTY_IMAGE"));
		}
		elseif (is_set($arFields, "IMAGE"))
		{
			$arFile = @getimagesize($_SERVER['DOCUMENT_ROOT'].BX_ROOT."/images/forum/".($arFields["TYPE"] == "I" ? "icon" : "smile")."/".$arFields["IMAGE"]);
			$arFile = (is_array($arFile) ? $arFile : array());
			$arFile = array(
				"name" => $arFields["IMAGE"], 
				"tmp_name" => $_SERVER['DOCUMENT_ROOT'].BX_ROOT."/images/forum/".($arFields["TYPE"] == "I" ? "icon" : "smile")."/".$arFields["IMAGE"],
				"type" => $arFile["mime"], 
				"size" => @filesize($_SERVER['DOCUMENT_ROOT'].BX_ROOT."/images/forum/".($arFields["TYPE"] == "I" ? "icon" : "smile")."/".$arFields["IMAGE"]), 
				"error" => 0, 
				"width" => $arFile[0], 
				"height" => $arFile[1]);
			
			$res = CFile::CheckImageFile($arFile, COption::GetOptionString("forum", "file_max_size", 50000));
			if (strLen($res) > 0)
			{
				$aMsg[] = array(
					"id" => "IMAGE", 
					"text" => $res);
			}
			else
			{
				$arFields["IMAGE_WIDTH"] = $arFile["width"];
				$arFields["IMAGE_HEIGHT"] = $arFile["height"];
			}
		}
		
		if (is_set($arFields, "LANG") || $ACTION == "ADD")
		{
			$res = (is_array($arFields["LANG"]) ? $arFields["LANG"] : array());
			foreach ($res as $i => $val)
			{
				if (empty($res[$i]["LID"]) || empty($res[$i]["NAME"]))
				{
					unset($res[$i]);
				}
			}

			$db_lang = CLanguage::GetList(($b="sort"), ($o="asc"));
			while ($arLang = $db_lang->Fetch())
			{
				$bFound = false;
				foreach ($res as $i => $val)
				{
					if ($res[$i]["LID"] == $arLang["LID"])
						$bFound = true;
				}
				if (!$bFound)
				{
					$aMsg[] = array("id" => 'NAME_'.$arLang["LID"], 
						"text" => GetMessage("FS_ERROR_EMPTY_NAME", 
							array("#LID#" => $arLang["LID"], "#LID_NAME#" => $arLang["NAME"])));
				}
			}
		}
		
		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && intVal($arFields["SORT"]) <= 0)
		{
			$arFields["SORT"] = 150;
		}
		
		if(!empty($aMsg))
		{
			$e = new CAdminException(array_reverse($aMsg));
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		return true;
	}
	
	function Update($ID, $arFields)
	{
		global $DB;
		$ID = intVal($ID);
		if ($ID <= 0):
			return false;
		endif;
		
		if (!CForumSmile::CheckFields("UPDATE", $arFields))
			return false;
		if(CACHED_b_forum_smile !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_smile");

		$strUpdate = $DB->PrepareUpdate("b_forum_smile", $arFields);
		if (!empty($strUpdate))
		{
			$strSql = "UPDATE b_forum_smile SET ".$strUpdate." WHERE ID = ".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		if (!empty($arFields["LANG"]))
		{
			$DB->Query("DELETE FROM b_forum_smile_lang WHERE SMILE_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			
			foreach ($arFields["LANG"] as $i => $val)
			{
				$arInsert = $DB->PrepareInsert("b_forum_smile_lang", $arFields["LANG"][$i]);
				$strSql = "INSERT INTO b_forum_smile_lang(SMILE_ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
		return $ID;
	}	

	function Delete($ID)
	{
		global $DB;
		$ID = intVal($ID);
		if ($ID <= 0):
			return false;
		endif;
		if(CACHED_b_forum_smile !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_smile");
		$DB->Query("UPDATE b_forum_topic SET ICON_ID = NULL WHERE ICON_ID = ".$ID, true);
		$DB->Query("DELETE FROM b_forum_smile_lang WHERE SMILE_ID = ".$ID, true);
		$DB->Query("DELETE FROM b_forum_smile WHERE ID = ".$ID, true);

		return true;
	}

	function GetList($arOrder = array("SORT"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		$arSqlOrder = Array();
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		
		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "SORT":
					if (intVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.".$key." IS NULL OR FR.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.".$key." IS NULL OR NOT ":"")."(FR.".$key." ".$strOperation." ".intVal($val)." )";
					break;
				case "TYPE":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.TYPE IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(FR.TYPE)<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.TYPE IS NULL OR NOT ":"")."(FR.TYPE ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
			}
		}
		if (!empty($arSqlSearch))
			$strSqlSearch = "WHERE (".implode(") AND (", $arSqlSearch).") ";

		foreach ($arOrder as $by => $order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			$order = ($order == "ASC" ? "ASC" : "DESC");
			if ($by == "ID") $arSqlOrder[] = " FR.ID ".$order." ";
			elseif ($by == "TYPE") $arSqlOrder[] = " FR.TYPE ".$order." ";
			else
			{
				$arSqlOrder[] = " FR.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (!empty($arSqlOrder))
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = "SELECT FR.ID, FR.SORT, FR.TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_forum_smile FR ".
			$strSqlSearch." ".
			$strSqlOrder;
			
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetListEx($arOrder = array("SORT"=>"ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		$arSqlOrder = Array();
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		
		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "ID":
				case "SORT":
					if (intVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.".$key." IS NULL OR FR.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.".$key." IS NULL OR NOT ":"")."(FR.".$key." ".$strOperation." ".intVal($val)." )";
					break;
				case "TYPE":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FR.TYPE IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(FR.TYPE)<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FR.TYPE IS NULL OR NOT ":"")."(FR.TYPE ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "LID":
					if (strlen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FRL.LID IS NULL OR ".($DB->type == "MSSQL" ? "LEN" : "LENGTH")."(FRL.LID)<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FRL.LID IS NULL OR NOT ":"")."(FRL.LID ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
			}
		}
		if (!empty($arSqlSearch))
			$strSqlSearch = " WHERE (".implode(") AND (", $arSqlSearch).") ";

		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			$order = ($order == "ASC" ? "ASC" : "DESC");

			if ($by == "ID") $arSqlOrder[] = " FR.ID ".$order." ";
			elseif ($by == "LID") $arSqlOrder[] = " FRL.LID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " FRL.NAME ".$order." ";
			elseif ($by == "TYPE") $arSqlOrder[] = " FR.TYPE ".$order." ";
			else
			{
				$arSqlOrder[] = " FR.SORT ".$order." ";
				$by = "SORT";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (!empty($arSqlOrder))
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);
		
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, ".
			"	FRL.LID, FRL.NAME, FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_forum_smile FR ".
			"	LEFT JOIN b_forum_smile_lang FRL ON FR.ID = FRL.SMILE_ID ".
			$strSqlSearch." ".
			$strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetByID($ID)
	{
		global $DB;
		$ID = intVal($ID);
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, ".
			"	FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_forum_smile FR ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return false;
	}

	function GetByIDEx($ID, $strLang)
	{
		global $DB;
		$ID = intVal($ID);
		
		$strSql = "SELECT FR.ID, FR.SORT, FR.TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, ".
			"	FRL.LID, FRL.NAME, FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_forum_smile FR ".
			"	LEFT JOIN b_forum_smile_lang FRL ON (FR.ID = FRL.SMILE_ID AND FRL.LID = '".$DB->ForSql($strLang)."') ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return false;
	}

	function GetLangByID($SMILE_ID, $strLang)
	{
		global $DB;
		$SMILE_ID = intVal($SMILE_ID);
		
		$strSql = "SELECT FRL.ID, FRL.SMILE_ID, FRL.LID, FRL.NAME ".
			"FROM b_forum_smile_lang FRL ".
			"WHERE FRL.SMILE_ID = ".$SMILE_ID." ".
			"	AND FRL.LID = '".$DB->ForSql($strLang)."' ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return false;
	}
	
	function GetByType($TYPE, $LANGUAGE_ID)
	{
		global $CACHE_MANAGER;
		$arFields = array();
		if (in_array($TYPE, array("S", "I")))
			$arFields["TYPE"] = $TYPE;
		if (!empty($LANGUAGE_ID))
			$arFields["LID"] = $LANGUAGE_ID;
		$cache_id = "b_forum_smile_".implode("_", array_keys($arFields))."_".implode("_", $arFields);
		$result = array();
		if (CACHED_b_forum_smile !== false && $CACHE_MANAGER->Read(CACHED_b_forum_smile, $cache_id, "b_forum_smile"))
		{
			$result = $CACHE_MANAGER->Get($cache_id);
		}
		else
		{
			$db_res = CForumSmile::GetListEx(array("SORT"=>"ASC"), $arFields);
			while ($res = $db_res->Fetch()):
				$result[] = $res;
			endwhile;
			if (CACHED_b_forum_smile !== false)
				$CACHE_MANAGER->Set($cache_id, $result);
		}
		return $result;
	}
}

class _CForumDBResult extends CDBResult
{
	function _CForumDBResult($res)
	{
		parent::CDBResult($res);
	}
	function Fetch()
	{
		global $DB;
		if($res = parent::Fetch())
		{
			if (COption::GetOptionString("forum", "FILTER", "Y") == "Y"):
				if (strLen(trim($res["HTML"])) >0)
				{
					$arr = unserialize($res["HTML"]);
					if (is_array($arr) && count($arr) > 0):
						$res["LAST_POSTER_NAME"] = $arr["LAST_POSTER_NAME"];
					endif;
				}
				if (strLen(trim($res["TOPIC_HTML"])) > 0)
				{
					$arr = unserialize($res["TOPIC_HTML"]);
					if (is_array($arr) && is_set($arr, "TITLE"))
						$res["TITLE"] = $arr["TITLE"];
				}
				if (strLen(trim($res["ABS_TOPIC_HTML"])) > 0)
				{
					$arr = unserialize($res["ABS_TOPIC_HTML"]);
					if (is_array($arr))
					{
						if (is_set($arr, "TITLE"))
							$res["ABS_TITLE"] = $arr["TITLE"];
						if (is_set($arr, "ABS_LAST_POSTER_NAME"))
							$res["ABS_LAST_POSTER_NAME"] = $arr["ABS_LAST_POSTER_NAME"];
					}
				}
			endif;
		}
		return $res;
	}
}
?>