<?
global $APPLICATION, $MESS, $DBType;
IncludeModuleLangFile(__FILE__);
$GLOBALS["aSortTypes"] = array(
	"reference" => array(GetMessage("FDATE_LAST_MESSAGE"), GetMessage("FMESSAGE_TOPIC"), GetMessage("FNUM_ANSWERS"), GetMessage("FNUM_VIEWS"), GetMessage("FSTART_DATE"), GetMessage("FAUTHOR_TOPIC")),
	"reference_id" => array("P", "T", "N", "V", "D", "A"));

$GLOBALS["aSortDirection"] = array(
	"reference" => array(GetMessage("FASC"), GetMessage("FDESC")),
	"reference_id" => array("ASC", "DESC"));

// A < E < I < M < Q < U < Y
// A - NO ACCESS		E - READ			I - ANSWER
// M - NEW TOPIC		Q - MODERATE	U - EDIT			Y - FULL_ACCESS
$GLOBALS["aForumPermissions"] = array(
	"reference" => array(GetMessage("FNO_ACCESS"), GetMessage("FREAD_ACCESS"), GetMessage("FANSWER_ACCESS"), GetMessage("FNEW_MESSAGE_ACCESS"), GetMessage("FMODERATE_ACCESS"), GetMessage("FEDIT_ACCESS"), GetMessage("FFULL_ACCESS")),
	"reference_id" => array("A", "E", "I", "M", "Q", "U", "Y"));
$GLOBALS["FORUMS_PER_PAGE"] = intVal(COption::GetOptionString("forum", "FORUMS_PER_PAGE", "10"));
$GLOBALS["FORUM_TOPICS_PER_PAGE"] = intVal(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
$GLOBALS["FORUM_MESSAGES_PER_PAGE"] = intVal(COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));

$arNameStatuses = @unserialize(COption::GetOptionString("forum", "statuses_name"));
$name = array("guest" => "Guest", "user" => "User", "moderator" => "Moderator", "editor" => "Editor", "administrator" => "Administrator");
foreach ($name as $k => $v):
	$name[$k] = (!empty($arMess["F_".strToUpper($k)]) ? $arMess["F_".strToUpper($k)] : $name[$k]);
endforeach;
if (!is_array($arNameStatuses) || empty($arNameStatuses)):
	if (!is_array($arNameStatuses[LANGUAGE_ID])):
		$arNameStatuses[LANGUAGE_ID] = $name;
	else:
		foreach ($name as $k => $v):
			if(empty($arNameStatuses[LANGUAGE_ID][$k])):
				$arNameStatuses[LANGUAGE_ID][$k] = $v;
			endif;
		endforeach;
	endif;
endif;

foreach ($arNameStatuses[LANGUAGE_ID] as $k => $v)
    $arNameStatuses[LANGUAGE_ID][$k] = htmlspecialcharsEx($v);
$GLOBALS["FORUM_STATUS_NAME"] = $arNameStatuses[LANGUAGE_ID];

$GLOBALS["SHOW_FORUM_DEBUG_INFO"] = false;
$GLOBALS["FORUM_CACHE"] = array(
	"FORUM" => array(), 
	"MESSAGE" => array(), 
	"USER" => array(), 
	"TOPIC" => array(), 
	"TOPIC_INFO" => array());
/* cache structure:
	[forum] [forum_id]
			main - main info about forum
			ex - extra (additional) info
			ex_site - extra info with site path
			permission - array permission for group on forum array(implode("-", group_id_array) => permission)
			permissions - array permission with permissions for all groups on forum array(group_id => permission)
			sites - site for forum array(site_id => path)
	message - i do not know
	topic - i do not know
	topic_filter - i do not know
	user - i do not know
	path to clear cache 
*/
if(!defined("CACHED_b_forum_group")) 
	define("CACHED_b_forum_group", 3600);
if(!defined("CACHED_b_forum")) 
	define("CACHED_b_forum", 3600);
if(!defined("CACHED_b_forum_perms")) 
	define("CACHED_b_forum_perms", 3600);
if(!defined("CACHED_b_forum2site")) 
	define("CACHED_b_forum2site", 3600);
if(!defined("CACHED_b_forum_smile")) 
	define("CACHED_b_forum_smile", 3600);
if(!defined("CACHED_b_forum_filter")) 
	define("CACHED_b_forum_filter", 3600);
if(!defined("CACHED_b_forum_user")) 
	define("CACHED_b_forum_user", 3600);

CModule::AddAutoloadClasses(
	"forum",
	array(
        "textParser" => "classes/general/functions.php",

        "CForumNew" =>   "classes/".$DBType."/forum_new.php",
        "CForumGroup" => "classes/".$DBType."/forum_new.php",
        "CForumSmile" => "classes/".$DBType."/forum_new.php",
        "_CForumDBResult"=>"classes/general/forum_new.php",

        "CForumTopic" => "classes/".$DBType."/topic.php",
        "_CTopicDBResult" => "classes/general/topic.php",
        
        "CForumMessage" => "classes/".$DBType."/message.php",
        "CForumFiles" => "classes/".$DBType."/message.php",
        "_CMessageDBResult" => "classes/general/message.php",

		"CForumEventLog" => "classes/general/event_log.php", 
		
		"CFilterDictionary" => "classes/".$DBType."/filter_dictionary.php", 
		"CFilterLetter" => "classes/".$DBType."/filter_dictionary.php", 
		"CFilterUnquotableWords" => "classes/".$DBType."/filter_dictionary.php", 
		
		"CForumPMFolder" => "classes/".$DBType."/private_message.php", 
		"CForumPrivateMessage" => "classes/".$DBType."/private_message.php", 
		
		"CForumPoints" => "classes/".$DBType."/points.php", 
		"CForumPoints2Post" => "classes/".$DBType."/points.php", 
		"CForumUserPoints" => "classes/".$DBType."/points.php", 
		
		"CForumRank" => "classes/".$DBType."/user.php", 
		"CForumStat" => "classes/".$DBType."/user.php", 
		"CForumSubscribe" => "classes/".$DBType."/user.php", 
		"CForumUser" => "classes/".$DBType."/user.php", 
		
		"CForumParameters" => "tools/components_lib.php", 
		"CForumEMail" => "mail/mail.php", 
		"CForumFormat" => "tools/components_lib.php",
		"CRatingsComponentsForum" => "classes/".$DBType."/ratings_components.php",
	));

	
	
function ForumCurrUserPermissions($FID)
{
	if ($GLOBALS["USER"]->IsAdmin() || $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W")
		return "Y";
	$strPerms = CForumNew::GetUserPermission($FID, $GLOBALS["USER"]->GetUserGroupArray());
	if ($strPerms <= "E"):
		return $strPerms;
	elseif (CForumUser::IsLocked($GLOBALS["USER"]->GetID())):
		$strPerms = CForumNew::GetPermissionUserDefault($GLOBALS["USER"]->GetID());
		return ($strPerms >= "E" ? $strPerms : "E");
	endif;
	return $strPerms;
}

function ForumSubscribeNewMessagesEx($FID, $TID, $NEW_TOPIC_ONLY, &$strErrorMessage, &$strOKMessage, $strSite = false, $SOCNET_GROUP_ID = false)
{
	if ($strSite===false)
		$strSite = SITE_ID;

	return ForumSubscribeNewMessages($FID, $TID, $strErrorMessage, $strOKMessage, $NEW_TOPIC_ONLY, $strSite, $SOCNET_GROUP_ID);
}

function ForumSubscribeNewMessages($FID, $TID, &$strErrorMessage, &$strOKMessage, $NEW_TOPIC_ONLY = "N", $strSite = false, $SOCNET_GROUP_ID = false)
{
	global $USER;

	$strSite = ($strSite===false ? SITE_ID : $strSite);
	$FID = IntVal($FID);
	$TID = IntVal($TID);
	$arError = array();
	$arNote = array();

	if (!$USER->IsAuthorized())
	{
		$arError[] = GetMessage("FORUM_SUB_ERR_AUTH");
	}
	elseif ($SOCNET_GROUP_ID==false && !CForumSubscribe::CanUserAddSubscribe($FID, $USER->GetUserGroupArray()))
	{
		$arError[] = GetMessage("FORUM_SUB_ERR_PERMS");
	}
	else 
	{
		$arFields = array(
			"USER_ID" => $USER->GetID(),
			"FORUM_ID" => $FID,
			"SITE_ID" => $strSite,
			"TOPIC_ID" => ($TID>0) ? $TID : false);
		if($SOCNET_GROUP_ID>0)
			$arFields['SOCNET_GROUP_ID'] = $SOCNET_GROUP_ID;
		$db_res = CForumSubscribe::GetListEx(array(), $arFields);
		if ($db_res && ($res = $db_res->Fetch()))
		{
			$sError = GetMessage("FORUM_SUB_ERR_ALREADY_TOPIC");
			if ($TID <= 0)
			{
				if ($res["NEW_TOPIC_ONLY"] == "Y")
				{
					$sError = GetMessage("FORUM_SUB_ERR_ALREADY_NEW");
					if ($NEW_TOPIC_ONLY != $res["NEW_TOPIC_ONLY"])
						$sError = str_replace("#FORUM_NAME#", htmlspecialcharsEx($res["FORUM_NAME"]), 
							GetMessage("FORUM_SUB_ERR_ALREADY_ALL_HELP"));
				}
				else
				{
					$sError = GetMessage("FORUM_SUB_ERR_ALREADY_ALL");
					if ($NEW_TOPIC_ONLY != $res["NEW_TOPIC_ONLY"])
						$sError = str_replace("#FORUM_NAME#", htmlspecialcharsEx($res["FORUM_NAME"]), 
							GetMessage("FORUM_SUB_ERR_ALREADY_NEW_HELP"));
				}
			}
			$arError[] = $sError;
		}
		else 
		{
			$arFields["NEW_TOPIC_ONLY"] = (($arFields["TOPIC_ID"]!==false) ? "N" : $NEW_TOPIC_ONLY );

			$subid = CForumSubscribe::Add($arFields);
			if (IntVal($subid)>0)
			{
				if ($TID>0)
					$arNote[] = GetMessage("FORUM_SUB_OK_MESSAGE_TOPIC");
				else 
					$arNote[] = GetMessage("FORUM_SUB_OK_MESSAGE");
			}
			else
			{
				$arError[] = GetMessage("FORUM_SUB_ERR_UNKNOWN");
			}
		}
	}

	if (!empty($arError))
		$strErrorMessage .= implode(".\n",$arError);
	if (!empty($arError))
		$strOKMessage .= implode(".\n",$arNote);
		
	if (empty($arError))
		return True;
	else
		return False;
}

function ForumGetRealIP()
{
	$ip = False;
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
		for ($i = 0; $i < count($ips); $i++)
		{
			// Skip RFC 1918 IP's 10.0.0.0/8, 172.16.0.0/12 and 192.168.0.0/16
			if (!preg_match("/^(10|172\.16|192\.168)\./", $ips[$i]) && preg_match("^[^.]+\.[^.]+\.[^.]+\.[^.]+", $ips[$i]))
			{
				$ip = $ips[$i];
				break;
			}
		}
	}
	// Return with the found IP or the remote address
	return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

function ForumAddMessage($MESSAGE_TYPE, $FID, $TID, $MID, $arFieldsG, &$strErrorMessage, &$strOKMessage, $iFileSize = false, $captcha_word = "", $captcha_sid = 0, $captcha_code = "")
{
	global $USER, $DB, $APPLICATION;
	$APPLICATION->ResetException();
	$strErrorMessage1 = "";
	$strOKMessage1 = "";
	$bUpdateTopic = False;
	$bAddEditNote = ($MESSAGE_TYPE == "EDIT");
	$arParams = array(
		"PERMISSION" => false);
// ************ External Permission *********************************
	if (!empty($arFieldsG["PERMISSION_EXTERNAL"]))
	{
		$arParams["PERMISSION"] = CForumNew::GetUserPermission($FID, $USER->GetUserGroupArray());
		if ($arParams["PERMISSION"] < "Q")
		{
			$arParams["PERMISSION"] = $arFieldsG["PERMISSION_EXTERNAL"];
		}
		unset($arFieldsG["PERMISSION_EXTERNAL"]);
	}
	elseif (!empty($arFieldsG["SONET_PERMS"]))
	{
		$arParams["PERMISSION"] = CForumNew::GetUserPermission($FID, $USER->GetUserGroupArray());
		if ($arParams["PERMISSION"] < "Q")
		{
			if ($arFieldsG["SONET_PERMS"]["bCanFull"] === true)
				$arParams["PERMISSION"] = "Y";
			elseif ($arFieldsG["SONET_PERMS"]["bCanNew"] === true)
				$arParams["PERMISSION"] = "M";
			elseif ($arFieldsG["SONET_PERMS"]["bCanWrite"] === true)
				$arParams["PERMISSION"] = "I";
			else
				$arParams["PERMISSION"] = "A";
		}
		unset($arFieldsG["SONET_PERMS"]);
	}
	
	$DB->StartTransaction();

	if ($MESSAGE_TYPE!="NEW" && $MESSAGE_TYPE!="EDIT" && $MESSAGE_TYPE!="REPLY")
		$strErrorMessage1 .= GetMessage("ADDMES_NO_TYPE").". \n";

	$MID = IntVal($MID);
	$TID = IntVal($TID);
	$FID = IntVal($FID);
	$arFieldsG["EDIT_ADD_REASON"] = ($arFieldsG["EDIT_ADD_REASON"] == "Y" ? "Y" : "N");
	if ($MID>0)
	{
		$arMessage = CForumMessage::GetByID($MID, array("FILTER" => "N"));
		if ($arMessage)
		{
			$TID = IntVal($arMessage["TOPIC_ID"]);
			$FID = IntVal($arMessage["FORUM_ID"]);
		}
	}
	$arTopic = array();
	if ($TID>0)
	{
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			$FID = IntVal($arTopic["FORUM_ID"]);
		}
	}
	$arForum = CForumNew::GetByID($FID);
//************************* Input params **************************************************************************
	if ($MESSAGE_TYPE=="NEW" && !CForumTopic::CanUserAddTopic($FID, $USER->GetUserGroupArray(), $USER->GetID(), $arForum, $arParams["PERMISSION"]))
		$strErrorMessage1 .= GetMessage("ADDMESS_NO_PERMS2NEW").". \n";
	elseif ($MESSAGE_TYPE=="EDIT" && !CForumMessage::CanUserUpdateMessage($MID, $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"]))
		$strErrorMessage1 .= GetMessage("ADDMESS_NO_PERMS2EDIT").". \n";
	elseif ($MESSAGE_TYPE=="REPLY" && !CForumMessage::CanUserAddMessage($TID, $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"]))
		$strErrorMessage1 .= GetMessage("ADDMESS_NO_PERMS2REPLY").". \n";

	if ($MESSAGE_TYPE=="NEW" || ($MESSAGE_TYPE=="EDIT" && 
			(is_set($arFieldsG, "TITLE") || is_set($arFieldsG, "DESCRIPTION") || 
			is_set($arFieldsG, "ICON_ID") || is_set($arFieldsG, "TAGS") || is_set($arFieldsG, "OWNER_ID") || is_set($arFieldsG, "SOCNET_GROUP_ID")) && 
		CForumTopic::CanUserUpdateTopic($TID, $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"])))
	{
		$bUpdateTopic = True;
	}
	
	if ($MESSAGE_TYPE =="EDIT" && (ForumCurrUserPermissions($FID) > "Q" && $arFieldsG["EDIT_ADD_REASON"] == "N"))
		$bAddEditNote = false;
	//*************************!CAPTCHA********************************************************************************
	if (!$USER->IsAuthorized() && $arForum["USE_CAPTCHA"]=="Y")
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");

		$cpt = new CCaptcha();
		if (strlen($captcha_code) > 0)
		{
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if (!$cpt->CheckCodeCrypt($captcha_word, $captcha_code, $captchaPass))
				$strErrorMessage1 .= GetMessage("FORUM_POSTM_CAPTCHA").". \n";
		}
		else
		{
			if (!$cpt->CheckCode($captcha_word, $captcha_sid))
				$strErrorMessage1 .= GetMessage("FORUM_POSTM_CAPTCHA").". \n";
		}
	}
	//*************************!CAPTCHA********************************************************************************

	$arFieldsG["POST_MESSAGE"] = trim($arFieldsG["POST_MESSAGE"]);
	if (strLen($arFieldsG["POST_MESSAGE"])<=0)
		$strErrorMessage1 .= GetMessage("ADDMESS_INPUT_MESSAGE").". \n";

	if ($bUpdateTopic && strlen(trim($arFieldsG["TITLE"]))<=0)
		$strErrorMessage1 .= GetMessage("ADDMESS_INPUT_TITLE").". \n";

//	*************************!QUOTA**********************************************************************************
//	if (strLen($strErrorMessage1) <= 0)
//	{
//		$quota = new CDiskQuota();
//		if ($MESSAGE_TYPE=="EDIT")
//		{
//			if (!$quota->checkDiskQuota(
//				intVal(strLen($arFieldsG["POST_MESSAGE"]) - strLen($arMessage["POST_MESSAGE"]))))
//			{
//				if (!$quota->LAST_ERROR)
//					$strErrorMessage1 .= GetMessage("MAIN_QUOTA_BAD")."( ".COption::GetOptionInt("main", "disk_space")." ). \n";
//				else 
//					$strErrorMessage1 .= $quota->LAST_ERROR;
//			}
//		}
//		elseif (!$quota->checkDiskQuota($arFieldsG["POST_MESSAGE"]))
//		{
//			if (!$quota->LAST_ERROR)
//				$strErrorMessage1 .= GetMessage("MAIN_QUOTA_BAD")."( ".COption::GetOptionInt("main", "disk_space")." ). \n";
//			else 
//				$strErrorMessage1 .= $quota->LAST_ERROR;
//		}
//	}
//		*************************!QUOTA**********************************************************************************
	if (strLen($strErrorMessage1) <= 0)
	{
//		*************************!ATTACH_IMG*****************************************************************************
		if (is_set($arFieldsG, "ATTACH_IMG") && strlen($arFieldsG["ATTACH_IMG"]["name"]) <= 0 && strlen($arFieldsG["ATTACH_IMG"]["del"]) <= 0)
			unset($arFieldsG["ATTACH_IMG"]);
		if (is_set($arFieldsG, "ATTACH_IMG"))
		{
			$arFieldsG["ATTACH_IMG"]["FILE_ID"] = $arMessage["ATTACH_IMG"]; 
			$arFieldsG["FILES"] = array($arFieldsG["ATTACH_IMG"]);
		}
		unset($arFieldsG["ATTACH_IMG"]);
		
		if (!empty($arFieldsG["FILES"]) && is_array($arFieldsG["FILES"]))
		{
			foreach ($arFieldsG["FILES"] as $key => $val):
				if (intVal($val["FILE_ID"]) > 0):
					$arFieldsG["FILES"][$key]["del"] = ($val["del"] == "Y" ? "Y" : "");
				endif;
			endforeach;
			$res = array("FORUM_ID" => $arForum["ID"], "TOPIC_ID" => 0, "MESSAGE_ID" => 0, "USER_ID" => $USER->GetID());
			if (!in_array($arForum["ALLOW_UPLOAD"], array("Y", "F", "A")))
				unset($arFieldsG["FILES"]);
			elseif (!CForumFiles::CheckFields($arFieldsG["FILES"], $res, "NOT_CHECK_DB"))
			{
				if ($ex = $APPLICATION->GetException())
					$strErrorMessage1 .= $ex->GetString();
				else 
					$strErrorMessage1 .= "File upload error.";
			}
		}
	}
//		*************************!ATTACH_IMG*****************************************************************************

	if (strlen($strErrorMessage1)<=0 && ($MESSAGE_TYPE=="NEW" || $MESSAGE_TYPE=="REPLY"))
	{
		$AUTHOR_ID = IntVal($USER->GetParam("USER_ID"));

		if ($USER->IsAuthorized())
		{
			$bSHOW_NAME = true;
			$res = CForumUser::GetByUSER_ID($USER->GetID());
			if ($res)
				$bSHOW_NAME = ($res["SHOW_NAME"]=="Y");
				
			if ($bSHOW_NAME)
				$arFieldsG["AUTHOR_NAME"] = $USER->GetFullName();

			if (strlen(Trim($arFieldsG["AUTHOR_NAME"]))<=0)
				$arFieldsG["AUTHOR_NAME"] = $USER->GetLogin();
		}

		if (strlen($arFieldsG["AUTHOR_NAME"])<=0)
			$strErrorMessage1 .= GetMessage("ADDMESS_INPUT_AUTHOR").". \n";
	}
	elseif (strlen($strErrorMessage1)<=0 && $MESSAGE_TYPE=="EDIT")
	{
		$AUTHOR_ID = IntVal($arMessage["AUTHOR_ID"]);
		if (is_set($arFieldsG, "AUTHOR_NAME") && strlen($arFieldsG["AUTHOR_NAME"]) <= 0):
			if ($AUTHOR_ID <= 0):
				$strErrorMessage1 .= GetMessage("ADDMESS_INPUT_AUTHOR").". \n";
			else:
				$res = CForumUser::GetByUSER_ID($AUTHOR_ID);
				$bSHOW_NAME = ($res["SHOW_NAME"] == "Y");
				if ($USER->GetID() == $AUTHOR_ID):
					if ($bSHOW_NAME):
						$arFieldsG["AUTHOR_NAME"] = $USER->GetFullName();
					endif;
					if (strlen(trim($arFieldsG["AUTHOR_NAME"]))<=0):
						$arFieldsG["AUTHOR_NAME"] = $USER->GetLogin();
					endif;
				else:
					$res = CForumUser::GetByUSER_IDEx($AUTHOR_ID);
					if ($res):
						if ($bSHOW_NAME):
							$arFieldsG["AUTHOR_NAME"] = trim($res["NAME"]." ".$res["LAST_NAME"]);
						endif;
						if (strlen(trim($arFieldsG["AUTHOR_NAME"]))<=0):
							$arFieldsG["AUTHOR_NAME"] = $res["LOGIN"];
						endif;
					else:
						unset($arFieldsG["AUTHOR_NAME"]);
					endif;
				endif;
			endif;
		endif;
		
		if ($USER->IsAuthorized())
		{
			$bSHOW_NAME = true;
			$res = CForumUser::GetByUSER_ID($USER->GetID());
			if ($res)
				$bSHOW_NAME = ($res["SHOW_NAME"]=="Y");
				
			if ($bSHOW_NAME)
				$arFieldsG["EDITOR_NAME"] = $USER->GetFullName();

			if (strlen(Trim($arFieldsG["EDITOR_NAME"]))<=0)
				$arFieldsG["EDITOR_NAME"] = $USER->GetLogin();
		}
		if ($bAddEditNote && empty($arFieldsG["EDITOR_NAME"]))
			$strErrorMessage1 .= GetMessage("ADDMESS_INPUT_EDITOR").". \n";
	}
//*************************/Input params ***************************************************************************

//************************* Actions ********************************************************************************
//************************* Add/edit topic *************************************************************************
	if (strlen($strErrorMessage1)<=0)
	{
		// The longest step by time. Actualization of topic, user and forum statistic info (~0.7-0.8 sec)
		if (($MESSAGE_TYPE == "EDIT") && ($arMessage["APPROVED"] == "Y" || $arMessage["APPROVED"] == "N"))
		{
			$arFieldsG["APPROVED"] = $arMessage["APPROVED"];
		}
		elseif (!empty($arTopic) && $arTopic["APPROVED"] != "Y")
		{
			$arFieldsG["APPROVED"] = "N";
		}
		else
		{
			$arFieldsG["APPROVED"] = ($arForum["MODERATION"]=="Y") ? "N" : "Y";
			if (ForumCurrUserPermissions($FID)>="Q")
				$arFieldsG["APPROVED"] = "Y";
		}

		if ($bUpdateTopic)
		{
			$arFields = Array(
				"TITLE"			=> $arFieldsG["TITLE"],
				"DESCRIPTION"	=> $arFieldsG["DESCRIPTION"],
				"ICON_ID"		=> $arFieldsG["ICON_ID"],
				"TAGS"			=> $arFieldsG["TAGS"]
			);
			
			if ($MESSAGE_TYPE=="NEW")
			{
				$arFields["FORUM_ID"] = $FID;
				$arFields["USER_START_ID"] = $AUTHOR_ID;
				$arFields["USER_START_NAME"] = $arFieldsG["AUTHOR_NAME"];
				$arFields["LAST_POSTER_NAME"] = $arFieldsG["AUTHOR_NAME"];
				$arFields["APPROVED"] = $arFieldsG["APPROVED"];
				$arFields["OWNER_ID"] = $arFieldsG["OWNER_ID"];
				$arFields["SOCNET_GROUP_ID"] = $arFieldsG["SOCNET_GROUP_ID"];

				$TID = CForumTopic::Add($arFields);
				if (IntVal($TID)<=0)
					$strErrorMessage1 .= GetMessage("ADDMESS_ERROR_ADD_TOPIC").". \n";
			}
			else
			{
				if (is_set($arFieldsG, "AUTHOR_NAME")):
					if ($arTopic["LAST_MESSAGE_ID"] == $MID && $arMessage["LAST_POSTER_NAME"] != $arFieldsG["AUTHOR_NAME"]):
						$arFields["LAST_POSTER_NAME"] = $arFieldsG["AUTHOR_NAME"];
					endif;
					if ($arTopic["ABS_LAST_MESSAGE_ID"] == $MID && $arMessage["ABS_LAST_POSTER_NAME"] != $arFieldsG["AUTHOR_NAME"]):
						$arFields["ABS_LAST_POSTER_NAME"] = $arFieldsG["AUTHOR_NAME"];
					endif;
					if ($arTopic["USER_START_NAME"] == $arMessage["USER_START_NAME"] && $arTopic["USER_START_NAME"] != $arFieldsG["AUTHOR_NAME"]):
						$arFields["USER_START_NAME"] = $arFieldsG["AUTHOR_NAME"];
					endif;
				endif;

				$TID1 = CForumTopic::Update($TID, $arFields);
				if (intVal($TID1) <= 0):
					$strErrorMessage1 .= GetMessage("ADDMESS_ERROR_EDIT_TOPIC").". \n";
				else:
					$res_log = array();
					foreach ($arFields as $key => $val):
						if ($arFields[$key] != $arTopic[$key]):
							$res_log[$key] = array(
								"before" => $arTopic[$key], 
								"after" => $arFields[$key]); 
						endif;
					endforeach;
					if (!empty($res_log)):
						CForumEventLog::Log("topic", "edit", $TID, print_r($res_log, true));
					endif;
				endif;
				
				if (is_set($arFieldsG, "AUTHOR_NAME") && $arForum["LAST_MESSAGE_ID"] == $MID && $arForum["LAST_POSTER_NAME"] != $arFieldsG["AUTHOR_NAME"]):
					$arFieldsForum = array("LAST_POSTER_NAME" => $arFieldsG["AUTHOR_NAME"]);
					if ($arForum["ABS_LAST_MESSAGE_ID"] == $MID):
						$arFieldsForum["LAST_POSTER_NAME"] = $arFieldsG["AUTHOR_NAME"];
					endif;
					CForumNew::Update($arForum["ID"], $arFieldsForum);
				endif;
			}
		}
	}
//*************************/Add/edit topic *************************************************************************

//************************* Add/edit message ***********************************************************************
	if (strlen($strErrorMessage1)<=0)
	{
		$arFields = Array(
			"POST_MESSAGE"	=> $arFieldsG["POST_MESSAGE"],
			"USE_SMILES"	=> ($arFieldsG["USE_SMILES"]=="Y") ? "Y" : "N",
			"APPROVED"		=> $arFieldsG["APPROVED"]
		);
		if (is_set($arFieldsG, "ATTACH_IMG")):
			$arFields["ATTACH_IMG"] = $arFieldsG["ATTACH_IMG"];
		elseif (is_set($arFieldsG, "FILES")):
			$arFields["FILES"] = $arFieldsG["FILES"];
		endif;
		if (is_set($arFieldsG, "PARAM1")):
			$arFields["PARAM1"] = $arFieldsG["PARAM1"];
		endif;
		if (is_set($arFieldsG, "PARAM2")):
			$arFields["PARAM2"] = $arFieldsG["PARAM2"];
		elseif ($MESSAGE_TYPE != "NEW"):
			$db_res = CForumMessage::GetList(array(), array("TOPIC_ID" => $TID, "NEW_TOPIC" => "Y"));
			if ($db_res && $res = $db_res->Fetch())
				$res["PARAM2"] = $res["PARAM2"];//??????
		endif;

		if ($MESSAGE_TYPE=="NEW" || $MESSAGE_TYPE=="REPLY")
		{
			$arFields["AUTHOR_NAME"] = $arFieldsG["AUTHOR_NAME"];
			$arFields["AUTHOR_EMAIL"] = $arFieldsG["AUTHOR_EMAIL"];
			$arFields["AUTHOR_ID"] = $AUTHOR_ID;
			$arFields["FORUM_ID"] = $FID;
			$arFields["TOPIC_ID"] = $TID;
			
			$AUTHOR_IP = ForumGetRealIP();
			
			$AUTHOR_IP_tmp = $AUTHOR_IP;
			$AUTHOR_REAL_IP = $_SERVER['REMOTE_ADDR'];
			if (COption::GetOptionString("forum", "FORUM_GETHOSTBYADDR", "N") == "Y")
			{
				$AUTHOR_IP = @gethostbyaddr($AUTHOR_IP);
				
				if ($AUTHOR_IP_tmp==$AUTHOR_REAL_IP)
					$AUTHOR_REAL_IP = $AUTHOR_IP;
				else
					$AUTHOR_REAL_IP = @gethostbyaddr($AUTHOR_REAL_IP);
			}

			$arFields["AUTHOR_IP"] = ($AUTHOR_IP!==False) ? $AUTHOR_IP : "<no address>";
			$arFields["AUTHOR_REAL_IP"] = ($AUTHOR_REAL_IP!==False) ? $AUTHOR_REAL_IP : "<no address>";
			$arFields["NEW_TOPIC"] = ($MESSAGE_TYPE=="NEW") ? "Y" : "N";
			$arFields["GUEST_ID"] = $_SESSION["SESS_GUEST_ID"];
			
			$MID = CForumMessage::Add($arFields, false);
			if (intVal($MID)<=0)
			{
				$str = $APPLICATION->GetException();
				if ($str && $str->GetString())
					$strErrorMessage1 .= $str->GetString();
				else 
					$strErrorMessage1 .= GetMessage("ADDMESS_ERROR_ADD_MESSAGE").".";
				if ($MESSAGE_TYPE=="NEW")
				{
					CForumTopic::Delete($TID);
					$TID = 0;
				}
			}
		
		}
		else
		{
			if (IntVal($AUTHOR_ID)<=0)
			{
				if (is_set($arFieldsG, "AUTHOR_NAME"))
					$arFields["AUTHOR_NAME"] = $arFieldsG["AUTHOR_NAME"];
				if (is_set($arFieldsG, "AUTHOR_EMAIL"))
					$arFields["AUTHOR_EMAIL"] = $arFieldsG["AUTHOR_EMAIL"];
			}
			
			if ($bAddEditNote)
			{
				$arFields["EDITOR_NAME"] = $arFieldsG["EDITOR_NAME"];
				$arFields["EDITOR_EMAIL"] = $arFieldsG["EDITOR_EMAIL"];
				$arFields["EDIT_REASON"] = $arFieldsG["EDIT_REASON"];
				$arFields["EDIT_DATE"] = "";
				
				if ($GLOBALS["USER"]->IsAuthorized())
				{
					$res = CForumUser::GetByUSER_ID($USER->GetID());
					$arFields["EDITOR_NAME"] = trim($res["SHOW_NAME"] == "Y" ? $USER->GetFullName() : "");
					$arFields["EDITOR_NAME"] = (empty($arFields["EDITOR_NAME"]) ? $USER->GetLogin() : $arFields["EDITOR_NAME"]);
					$arFields["EDITOR_EMAIL"] = "";
                    $arFields["EDITOR_ID"] = $GLOBALS["USER"]->GetID();
				}
			}
			$MID1 = CForumMessage::Update($MID, $arFields);
			if (IntVal($MID1)<=0)
			{
				$ex = $GLOBALS['APPLICATION']->GetException();
				if ($ex)
					$strErrorMessage1 .= $ex->GetString();
				else
					$strErrorMessage1 .= GetMessage("ADDMESS_ERROR_EDIT_MESSAGE").". \n";
			}
			elseif ($AUTHOR_ID == $GLOBALS["USER"]->GetId() && COption::GetOptionString("forum", "LOGS", "Q") < "U")
			{}
			else
			{
				$res_log = array();
				foreach ($arFields as $key => $val):
					if ($arFields[$key] != $arMessage[$key]):
						if ($key == "FILES" || $key == "ATTACH_IMG"):
							$res_log[$key] = GetMessage("F_ATTACH_IS_MODIFIED");
							continue;
						endif;
						$res_log[$key] = array("before" => $arMessage[$key], "after" => $arFields[$key]);
					endif;
				endforeach;
				if (!empty($res_log)):
					$res_log = print_r($res_log, true);
					CForumEventLog::Log("message", "edit", $MID, ($AUTHOR_ID == $GLOBALS["USER"]->GetId() ? GetMessage("F_MESSAGE_EDITED_BY_AUTHOR") : "").$res_log);
				endif;
			}
		}
	}
//*************************/Add/edit message ***********************************************************************
	if (strlen($strErrorMessage1)<=0)
	{
		$DB->Commit();
	}
	else
	{
		$DB->Rollback();
	}
	if (strlen($strErrorMessage1)<=0)
	{
		if (CModule::IncludeModule("statistic"))
		{
			$F_EVENT1 = $arForum["EVENT1"];
			$F_EVENT2 = $arForum["EVENT2"];
			$F_EVENT3 = $arForum["EVENT3"];
			if (strlen($F_EVENT3)<=0)
			{
				$arForumSite_tmp = CForumNew::GetSites($FID);
				if (defined("ADMIN_SECTION") && ADMIN_SECTION===true)
				{
					$arForumSiteCode_tmp = array_keys($arForumSite_tmp);
					$F_EVENT3 = CForumNew::PreparePath2Message($arForumSite_tmp[$arForumSiteCode_tmp[0]], array("FORUM_ID"=>$FID, "TOPIC_ID"=>$TID, "MESSAGE_ID"=>$MID));
				}
				else
				{
					$F_EVENT3 = CForumNew::PreparePath2Message($arForumSite_tmp[SITE_ID], array("FORUM_ID"=>$FID, "TOPIC_ID"=>$TID, "MESSAGE_ID"=>$MID));
				}
			}
			CStatistic::Set_Event($F_EVENT1, $F_EVENT2, $F_EVENT3);
		}
	}
	
	$strErrorMessage .= $strErrorMessage1;
	if (strlen($strErrorMessage1)<=0)
	{
		if ($MESSAGE_TYPE=="NEW" || $MESSAGE_TYPE=="REPLY")
		{
			CForumMessage::SendMailMessage($MID, array(), false, "NEW_FORUM_MESSAGE");
			$strOKMessage1 .= GetMessage("ADDMESS_SUCCESS_ADD").". \n";
		}
		else
		{
			CForumMessage::SendMailMessage($MID, array(), false, "EDIT_FORUM_MESSAGE");
			$strOKMessage1 .= GetMessage("ADDMESS_SUCCESS_EDIT").". \n";
		}

		if ($arFieldsG["APPROVED"]!="Y")
			$strOKMessage1 .= GetMessage("ADDMESS_AFTER_MODERATE").". \n";

		$strOKMessage .= $strOKMessage1;

		return $MID;
	}
	else
	{
		return false;
	}
}

function ForumModerateMessage($message, $TYPE, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$arError = array();
	$arOK = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);
	$message = ForumDataToArray($message);
	if (empty($message))
	{
		$arError[] = GetMessage("DELMES_NO_MESS").". \n";
	}
	else 
	{
		$db_res = CForumMessage::GetList(array(), array("@ID" => implode(",", $message)));
		if ($db_res)
		{
			while ($arMessage = $db_res->Fetch())
			{
				if (!(ForumCurrUserPermissions($arMessage["FORUM_ID"]) >= "Q" || 
					CForumMessage::CanUserUpdateMessage($arMessage["ID"], $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"])))
					$arError[] = GetMessage("MODMESS_NO_PERMS")." (MID=".$arMessage["ID"]."). \n";
				else 
				{
					$arFields = array();
					$arFields["APPROVED"] = ($TYPE == "SHOW") ? "Y" : "N";
					$ID = CForumMessage::Update($arMessage["ID"], $arFields);
					if (IntVal($ID)<=0)
					{
						$arError[] = GetMessage("MODMESS_ERROR_MODER")." (MID=".$arMessage["ID"]."). \n";
					}
					else
					{
						if ($TYPE == "SHOW"):
							$arOK[] = GetMessage("MODMESS_SUCCESS_SHOW")." (MID=".$arMessage["ID"]."). \n";
							CForumMessage::SendMailMessage($arMessage["ID"], array(), false, "NEW_FORUM_MESSAGE");
							CForumEventLog::Log("message", "approve", $arMessage["ID"], print_r(array(
								"ID" => $arMessage["ID"], 
								"AUTHOR_NAME" => $arMessage["AUTHOR_NAME"], 
								"POST_MESSAGE" => $arMessage["POST_MESSAGE"]), true));
						else:
							$arOK[] = GetMessage("MODMESS_SUCCESS_HIDE")." (MID=".$arMessage["ID"]."). \n";
							CForumMessage::SendMailMessage($arMessage["ID"], array(), false, "EDIT_FORUM_MESSAGE");
							CForumEventLog::Log("message", "unapprove", $arMessage["ID"], print_r(array(
								"ID" => $arMessage["ID"], 
								"AUTHOR_NAME" => $arMessage["AUTHOR_NAME"], 
								"POST_MESSAGE" => $arMessage["POST_MESSAGE"]), true));
						endif;
					}
				}
			}
		}
		else 
			$arError[] = GetMessage("DELMES_NO_MESS").". \n";
	}
	$strErrorMessage .= implode("", $arError);
	$strOKMessage .= implode("", $arOK);
	
	if (count($arError) <= 0)
		return true;
	else
		return false;
}

function ForumOpenCloseTopic($topic, $TYPE, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$arError = array();
	$arOk = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);
	
	$topic = ForumDataToArray($topic);
	if (empty($topic))
	{
		$arError[] = GetMessage("OCTOP_NO_TOPIC");
	}
	else 
	{
		if (!$USER->IsAdmin() && !$arAddParams["PERMISSION"])
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic), "PERMISSION_STRONG" => true));
		else 
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic)));
		if ($db_res && $res = $db_res->Fetch())
		{
			$arFields = array();
			$arFields["STATE"] = ($TYPE == "OPEN") ? "Y" : "N";
			do 
			{
				if ($res["STATE"] == "L")
				{
					if ($TYPE=="OPEN")
						$arError[] = GetMessage("OCTOP_ERROR_OPEN")." (TID=".intVal($res["ID"]).")";
					else
						$arError[] = GetMessage("OCTOP_ERROR_CLOSE")." (TID=".intVal($res["ID"]).")";
					continue;
				}
				elseif ($arAddParams["PERMISSION"] && !CForumTopic::CanUserUpdateTopic($res["ID"], $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				{
					$arError[] = GetMessage("FMT_NO_PERMS_EDIT")." (TID=".intVal($res["ID"]).")";
					continue;
				}
				$ID = CForumTopic::Update($res["ID"], $arFields, True);
				if (IntVal($ID)<=0)
				{
					if ($TYPE=="OPEN")
						$arError[] = GetMessage("OCTOP_ERROR_OPEN")." (TID=".intVal($res["ID"]).")";
					else
						$arError[] = GetMessage("OCTOP_ERROR_CLOSE")." (TID=".intVal($res["ID"]).")";
				}
				else
				{
					$arTopic["SORT"] = $arFields["SORT"];
					if ($TYPE=="OPEN"): 
						$arOk[] = GetMessage("OCTOP_SUCCESS_OPEN")." (TID=".intVal($res["ID"]).")";
						CForumEventLog::Log("topic", "open", $res["ID"], print_r($res, true));
					else: 
						$arOk[] = GetMessage("OCTOP_SUCCESS_CLOSE")." (TID=".intVal($res["ID"]).")";
						CForumEventLog::Log("topic", "close", $res["ID"], print_r($res, true));
					endif;
				}
			}while ($res = $db_res->Fetch());
		}
		else
		{
			$arError[] = GetMessage("FMT_NO_PERMS_EDIT");
		}
	}
	if (count($arError) > 0)
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (count($arOk) > 0)
		$strOKMessage .= implode(".\n", $arOk).".\n";
	if (count($arError) > 0)
		return false;
	else
		return true;
}

function ForumTopOrdinaryTopic($topic, $TYPE, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$arError = array();
	$arOk = array();
	$arFields = array("SORT" => ($TYPE == "TOP" ? 100 : 150));
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);

	$topic = ForumDataToArray($topic);
	if (empty($topic))
	{
		$arError[] = GetMessage("TOTOP_NO_TOPIC");
	}
	else 
	{
		if (!$USER->IsAdmin() && !$arAddParams["PERMISSION"])
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic), "PERMISSION_STRONG" => true));
		else 
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic)));
		if ($db_res && $res = $db_res->Fetch())
		{
			do 
			{
				if ($arAddParams["PERMISSION"] && !CForumTopic::CanUserUpdateTopic($res["ID"], $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				{
					$arError[] = GetMessage("FMT_NO_PERMS_MODERATE")." (TID=".intVal($res["ID"]).")";
					continue;
				}
				$ID = CForumTopic::Update($res["ID"], $arFields, True);
				if (IntVal($ID)<=0)
				{
					if ($TYPE=="TOP")
						$arError[] = GetMessage("TOTOP_ERROR_TOP")." (TID=".intVal($res["ID"]).")";
					else
						$arError[] = GetMessage("TOTOP_ERROR_TOP1")." (TID=".intVal($res["ID"]).")";
				}
				else
				{
					$arTopic["SORT"] = $arFields["SORT"];
					if ($TYPE=="TOP"): 
						$arOk[] = GetMessage("TOTOP_SUCCESS_TOP")." (TID=".intVal($res["ID"]).")";
						CForumEventLog::Log("topic", "stick", $res["ID"], print_r($res, true));
					else: 
						$arOk[] = GetMessage("TOTOP_SUCCESS_TOP1")." (TID=".intVal($res["ID"]).")";
						CForumEventLog::Log("topic", "unstick", $res["ID"], print_r($res, true));
					endif;
				}
			}while ($res = $db_res->Fetch());
		}
		else
		{
			$arError[] = GetMessage("FMT_NO_PERMS_EDIT");
		}
	}
	if (count($arError) > 0)
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (count($arOk) > 0)
		$strOKMessage .= implode(".\n", $arOk).".\n";
		
	if (empty($arError))
		return true;
	else
		return false;
}

function ForumDeleteTopic($topic, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;

	$arError = array();
	$arOk = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams);
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);
	
	$topic = ForumDataToArray($topic);
	if (empty($topic))
	{
		$arError[] = GetMessage("DELTOP_NO_TOPIC");
	}
	else 
	{
		if (!$USER->IsAdmin() && !$arAddParams["PERMISSION"])
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic), "PERMISSION_STRONG" => true));
		else 
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic)));
		if ($db_res && $res = $db_res->Fetch())
		{
			do 
			{
				if (CForumTopic::CanUserDeleteTopic($res["ID"], $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				{
					if (CForumTopic::Delete($res["ID"]))
					{
						$arOk[] = GetMessage("DELTOP_OK")." (TID=".intVal($res["ID"]).")";
						CForumEventLog::Log("topic", "delete", $res["ID"], print_r($res, true));
					}
					else 
					{
						$arError[] = GetMessage("DELTOP_NO")." (TID=".intVal($res["ID"]).")";
					}
				}
				else 
				{
					$arError[] = GetMessage("DELTOP_NO_PERMS")." (TID=".intVal($res["ID"]).")";
				}
				
			}while ($res = $db_res->Fetch());
		}
		else
		{
			$arError[] = GetMessage("FMT_NO_PERMS_EDIT");
		}
	}
	if (count($arError) > 0)
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (count($arOk) > 0)
		$strOKMessage .= implode(".\n", $arOk).".\n";
		
	if (count($arError) > 0)
		return false;
	else
		return true;
}

function ForumDeleteMessage($message, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$arError = array();
	$arOK = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);
	
	$message = ForumDataToArray($message);
	if (empty($message))
	{
		$arError[] = GetMessage("DELMES_NO_MESS");
	}
	else 
	{
		foreach ($message as $MID)
		{
			if (!CForumMessage::CanUserDeleteMessage($MID, $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				$arError[] = GetMessage("DELMES_NO_PERMS")."(MID=".$MID.")";
			else 
			{
				$arMessage = CForumMessage::GetByID($MID, array("FILTER" => "N"));
				if (CForumMessage::Delete($MID)):
					$arOK[] = GetMessage("DELMES_OK")."(MID=".$MID.")";
					CForumEventLog::Log("message", "delete", $MID, print_r($arMessage, true));
				else: 
					$arError[] = GetMessage("DELMES_NO")."(MID=".$MID.")";
				endif;
			}
		}
	}
	if (!empty($arError))
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (!empty($arOK))
		$strOKMessage .= implode(".\n", $arOK).".\n";
	return (empty($arError) ? true : false);
}

function ForumSpamTopic($topic, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;

	$arError = array();
	$arOk = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams);
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);
	
	$topic = ForumDataToArray($topic);
	if (empty($topic))
	{
		$arError[] = GetMessage("SPAMTOP_NO_TOPIC");
	}
	else 
	{
		if (!$USER->IsAdmin() && !$arAddParams["PERMISSION"])
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic), "PERMISSION_STRONG" => true));
		else 
			$db_res = CForumTopic::GetListEx(array(), array("@ID" => implode(",", $topic)));
		if ($db_res && $res = $db_res->Fetch())
		{
			do 
			{
				if (CForumTopic::CanUserDeleteTopic($res["ID"], $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				{
					$db_mes = CForumMessage::GetList(array("ID"=>"ASC"), array("TOPIC_ID" => $res["ID"])); 
					if ($db_mes && $mes = $db_mes->Fetch() && CModule::IncludeModule("mail"))
					{
						CMailMessage::MarkAsSpam($mes["XML_ID"], "Y");
					}
					
					if (CForumTopic::Delete($res["ID"]))
					{
						$arOk[] = GetMessage("SPAMTOP_OK")." (TID=".intVal($res["ID"]).")";
						CForumEventLog::Log("topic", "spam", $res["ID"], print_r($res, true).print_r($mes, true));
					}
					else 
					{
						$arError[] = GetMessage("SPAMTOP_NO")." (TID=".intVal($res["ID"]).")";
					}
				}
				else 
				{
					$arError[] = GetMessage("SPAMTOP_NO_PERMS")." (TID=".intVal($res["ID"]).")";
				}
			}while ($res = $db_res->Fetch());
		}
		else
		{
			$arError[] = GetMessage("FMT_NO_PERMS_EDIT");
		}
	}
	if (count($arError) > 0)
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (count($arOk) > 0)
		$strOKMessage .= implode(".\n", $arOk).".\n";
		
	if (count($arError) > 0)
		return false;
	else
		return true;	
}

function ForumSpamMessage($message, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$arError = array();
	$arOK = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);
	$message = ForumDataToArray($message);
	if (empty($message))
	{
		$arError[] = GetMessage("SPAM_NO_MESS");
	}
	else 
	{
		foreach ($message as $MID)
		{
			if (!CForumMessage::CanUserDeleteMessage($MID, $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
				$arError[] = GetMessage("SPAM_NO_PERMS")."(MID=".$MID.")";
			else 
			{
				$arMessage = CForumMessage::GetByID($MID, array("FILTER" => "N"));
				if (CModule::IncludeModule("mail"))
				{
					CMailMessage::MarkAsSpam($arMessage["XML_ID"], "Y");
				}
				if (CForumMessage::Delete($MID)):
					$arOK[] = GetMessage("SPAM_OK")."(MID=".$MID.")";
					CForumEventLog::Log("message", "spam", $MID, print_r($arMessage, true));
				else: 
					$arError[] = GetMessage("SPAM_NO")."(MID=".$MID.")";
				endif;
			}
		}
	}
	if (!empty($arError))
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (!empty($arOK))
		$strOKMessage .= implode(".\n", $arOK).".\n";
	return (empty($arError) ? true : false);	
}
function ForumMessageExistInArray($message = array())
{
	$message_exist = false;
	$result = array();
	if (!is_array($message))
		$message = explode(",", $message);
	
	foreach ($message as $message_id)
	{
		if (intVal(trim($message_id)) > 0)
		{
			$result[] = intVal(trim($message_id));
			$message_exist = true;
		}
	}
		
	if ($message_exist)
		return $result;
	else 
		return false;
}

function ForumDeleteMessageArray($message, &$strErrorMessage, &$strOKMessage)
{
	return ForumDeleteMessage($message, $strErrorMessage, $strOKMessage);
}

function ForumModerateMessageArray($message, $TYPE, &$strErrorMessage, &$strOKMessage)
{
	return ForumModerateMessage($message, $TYPE, $strErrorMessage, $strOKMessage);
}


function ForumShowTopicPages($nMessages, $strUrl, $pagen_var = "PAGEN_1", $PAGE_ELEMENTS = false)
{
	global $FORUM_MESSAGES_PER_PAGE;
	$res_str = "";
	
	if ((!$PAGE_ELEMENTS) && (intVal($PAGE_ELEMENTS) <= 0))
		$PAGE_ELEMENTS = $FORUM_MESSAGES_PER_PAGE;

	if (strpos($strUrl, "?") === false)
		$strUrl = $strUrl."?";
	else 
		$strUrl = $strUrl."&amp;";
		
	if ($nMessages > $PAGE_ELEMENTS)
	{
		$res_str .= "<small>(".GetMessage("FSTP_PAGES").": ";

		$nPages = IntVal(ceil($nMessages / $PAGE_ELEMENTS));
		$typeDots = true;
		for ($i = 1; $i <= $nPages; $i++)
		{
			if ($i<=3 || $i>=$nPages-2 || ($nPages == 7 && $i == 3))
			{
				$res_str .= "<a href=\"".$strUrl.$pagen_var."=".$i."\">".$i."</a> ";
			}
			elseif ($typeDots)
			{
				$res_str .= "... ";
				$typeDots = false;
			}
		}
		$res_str .= ")</small>";
	}
	return $res_str;
}

function ForumMoveMessage($FID, $TID, $Message, $NewTID = 0, $arFields, &$strErrorMessage, &$strOKMessage, $iFileSize = false)
{
	global $USER, $DB;
	$arError = array();
	$arOK = array();
	$NewFID = 0;
	$arForum = array();
	$arTopic = array();
	$arNewForum = array();
	$arNewTopic = array();
	$arCurrUser = array();
	$SendSubscribe = false;
	
//************************* Input params **************************************************************************
	$TID = IntVal($TID);
	$FID = IntVal($FID);
	$NewTID = IntVal($NewTID);
	$Message = ForumDataToArray($Message);
	if (empty($Message))
		$arError[] = GetMessage("FMM_NO_MESSAGE");
	if ($TID <= 0)
		$arError[] = GetMessage("FMM_NO_TOPIC_SOURCE0");
	else
	{
		$arTopic = CForumTopic::GetByID($TID);
		if ($arTopic)
		{
			$FID = IntVal($arTopic["FORUM_ID"]);
			$arForum = CForumNew::GetByID($FID);
		}
		else 
			$arError[] = GetMessage("FMM_NO_TOPIC_SOURCE1");
	}
	
	if (($NewTID <= 0) && (strLen(trim($arFields["TITLE"])) <= 0))
		$arError[] = GetMessage("FMM_NO_TOPIC_RECIPIENT0");
	elseif($NewTID > 0)
	{
		if ($NewTID == $TID)
			$arError[] = GetMessage("FMM_NO_TOPIC_EQUAL");
		$arNewTopic = CForumTopic::GetByID($NewTID);
		
		if (!$arNewTopic)
			$arError[] = GetMessage("FMM_NO_TOPIC_RECIPIENT1");
		elseif ($arNewTopic["STATE"] == "L")
			$arError[] = GetMessage("FMM_TOPIC_IS_LINK");
		else
		{
			$NewFID =  $arNewTopic["FORUM_ID"];
			$arNewForum = CForumNew::GetByID($NewFID);
		}
	}
//*************************/Input params **************************************************************************
//*************************!Proverka prav pol'zovatelya na forume-istochnike i forume-poluchatele*********************
// Tak kak realizovan mehanizm peremeweniya tem s forumov, gde tekuwij pol'zovatel' yavlyaetsya moderatorom na forumy, 
// gde on moderatorov ne yavlyaetsya, to v dannom sluchae budet ispol'zovan tot zhe samyj shablon dejstvij. Isklyucheniem 
// yavlyaetsya to, chto esli pol'zovatel' na forume-poluchatele ne obladaet pravami moderirovaniya, tema budet neaktivna.
//*************************!Proverka prav pol'zovatelya*************************************************************
	$arCurrUser["Perms"]["FID"] = ForumCurrUserPermissions($FID);
	$arCurrUser["Perms"]["NewFID"] = ForumCurrUserPermissions($NewFID);
	if ($arCurrUser["Perms"]["FID"] < "Q")
		$arError[] = GetMessage("FMM_NO_MODERATE");
//************************* Actions *******************************************************************************
	$DB->StartTransaction();
	if (count($arError) <= 0)
	{
		// Create topic
		if ($NewTID <= 0)
		{
			$arFields["APPROVED"] = ($arNewForum["MODERATION"]=="Y") ? "N" : "Y";
			if ($arCurrUser["Perms"]["NewFID"] >= "Q")
				$arFields["APPROVED"] = "Y";

			$arRes = array("NAME" => GetMessage("FR_GUEST"));
			$ShowName = GetMessage("FR_GUEST");
			$db_res = CForumMessage::GetList(array("ID" => "ASC"), array("@ID" => implode(",", $Message), "TOPIC_ID" => $TID));
			if ($db_res && $res = $db_res->Fetch())
			{
				$arRes["NAME"] = $res["AUTHOR_NAME"];
				$arRes["ID"] = $res["AUTHOR_ID"];
			}
				
			$arFieldsTopic = array(
				"TITLE"			=> $arFields["TITLE"],
				"DESCRIPTION"	=> $arFields["DESCRIPTION"],
				"ICON_ID"		=> $arFields["ICON_ID"],
				"TAGS"		=> $arFields["TAGS"],
				"FORUM_ID"		=> $FID,
				"USER_START_ID" => $arRes["ID"],
				"USER_START_NAME" => $arRes["NAME"],
				"LAST_POSTER_NAME" => $arRes["NAME"],
				"LAST_POSTER_ID" => $arRes["ID"],
				"APPROVED" => $arFields["APPROVED"],
			);
			$NewTID = CForumTopic::Add($arFieldsTopic);
			if (IntVal($NewTID)<=0)
				$arError[] = GetMessage("FMM_NO_TOPIC_NOT_CREATED");
			else
			{ 
				$arNewTopic = CForumTopic::GetByID($NewTID);
				if ($arNewTopic)
				{
					$NewFID = $FID;
					$arNewForum = $arForum;
					$SendSubscribe = true;
				}
				else 
					$arError[] = GetMessage("FMM_NO_TOPIC_NOT_CREATED");
			}
		}
	}
	
	if (count($arError) <= 0)
	{
		// Move message
		$db_res = CForumMessage::GetList(array(), array("@ID" => implode(",", $Message), "TOPIC_ID" => $TID));
		if ($db_res && $res = $db_res->Fetch())
		{
			do 
			{
//				echo "NewFID: ".$NewFID." -- FID:".$FID."<br/>";
				$arMessage = array();
				if ($NewFID != $FID)
				{
					$arMessage["APPROVED"] = ($arNewForum["MODERATION"]=="Y") ? "N" : "Y";
					if ($arCurrUser["Perms"]["NewFID"] >= "Q")
						$arMessage["APPROVED"] = "Y";
						
					$arMessage["FORUM_ID"] = $NewFID;
					$arMessage["POST_MESSAGE_HTML"] = "";
					// check attach
					if (false && intVal($res["ATTACH_IMG"]) > 0)
					{
						$iFileSize = COption::GetOptionString("forum", "file_max_size", 50000);
						$attach_img = CFile::GetByID(intVal($res["ATTACH_IMG"]));
						$attach = "";
						if ($attach_img && is_set($attach_img, "ORIGINAL_NAME"))
						{
							// Y - Image files		F - Files of specified type		A - All files
							if ($arNewForum["ALLOW_UPLOAD"]=="Y")
								$attach = CFile::CheckImageFile($attach_img["ORIGINAL_NAME"], $iFileSize, 0, 0);
							elseif ($arNewForum["ALLOW_UPLOAD"]=="F")
								$attach = CFile::CheckFile($attach_img["ORIGINAL_NAME"], $iFileSize, false, $arNewForum["ALLOW_UPLOAD_EXT"]);
							elseif ($arNewForum["ALLOW_UPLOAD"]=="A")
								$attach = CFile::CheckFile($attach_img["ORIGINAL_NAME"], $iFileSize, false, false);
							if (strLen($attach) > 0)
								$arMessage["ATTACH_IMG"] = "";
						}
					}
				}
				
				if ($NewTID != $TID)
					$arMessage["TOPIC_ID"] = $NewTID;
				
				if (count($arMessage) > 0)
				{
					$MID = CForumMessage::Update($res["ID"], $arMessage, true);
					$res_log = ($SendSubscribe == true ? GetMessage("F_MESSAGE_WAS_MOVED_TO_NEW") : GetMessage("F_MESSAGE_WAS_MOVED"));
					$res_log = str_replace(array("#ID#", "#TOPIC_TITLE#", "#TOPIC_ID#", "#NEW_TOPIC_TITLE#", "#NEW_TOPIC_ID#"), 
						array($MID, $arTopic["TITLE"], $arTopic["ID"], $arNewTopic['TITLE'], $arNewTopic['ID']), $res_log);
					CForumEventLog::Log("message", "move", $MID, $res_log.print_r($res, true));
					$db_res2 = CForumFiles::GetList(array(), array("FILE_MESSAGE_ID" => $res["ID"]));
					if ($db_res2 && $res2 = $db_res2->Fetch())
					{
						$arFiles = array();
						do 
						{
							$arFiles[] = $res2["FILE_ID"];
						} while ($res2 = $db_res2->Fetch());
						CForumFiles::UpdateByID($arFiles, $arMessage);
					}
					if (IntVal($MID) <= 0)
					{
						$arError[] = str_replace("##", $res["ID"], GetMessage("FMM_NO_MESSAGE_MOVE"));
						break;
					}
				}
			}while ($res = $db_res->Fetch());
		}
	}
	
	if (count($arError) <= 0)
	{
		$db_res = CForumMessage::GetList(array(), array("TOPIC_ID" => $TID), false, 1);
		if (!($db_res && $res = $db_res->Fetch())):
			CForumTopic::Delete($TID);
		else: 
			CForumTopic::SetStat($TID);
		endif;

		$db_res = CForumMessage::GetList(array(), array("TOPIC_ID" => $NewTID), false, 1);
		if (!($db_res && $res = $db_res->Fetch())):
			CForumTopic::Delete($NewTID);
		else: 
			CForumTopic::SetStat($NewTID);
		endif;
		
		CForumNew::SetStat($FID);
		if ($NewFID != $FID)
			CForumNew::SetStat($NewFID);
	}
	if (count($arError) <= 0)
		$DB->Commit();
	else
		$DB->Rollback();

	if (count($arError) > 0)
		$strErrorMessage .= implode(". \n", $arError).". \n";
	else 
	{
		$strOKMessage .= GetMessage("FMM_YES_MESSAGE_MOVE");
		if ($SendSubscribe)
		{
			foreach ($Message as $MID)
				CForumMessage::SendMailMessage($MID, array(), false, "NEW_FORUM_MESSAGE");
		}
		return true;
	}
	return false;
}

function ForumPrintIconsList($num_cols, $varField, $varValue, $defValue, $strLang = False, $strPath2Icons = False)
{
	$strLang = ($strLang === false ? LANGUAGE_ID : $strLang);
	$strPath2Icons = ($strPath2Icons === false ? "/bitrix/images/forum/icon/" : $strPath2Icons);
	if (strrpos($strPath2Icons, "/") != (strLen($strPath2Icons)-1))
		$strPath2Icons .= "/";
	$arSmile = CForumSmile::GetByType("I", $strLang);
	
	$ind = 0;
	$res_str = "<table width=\"0%\" align=\"left\" cellspacing=\"1\" cellpadding=\"5\" border=\"0\" class=\"forum-icons\">\n";
	foreach ($arSmile as $res)
	{
		if ($ind == 0) 
			$res_str .= "<tr align=\"left\">\n";
		$res_str .= "<td width=\"".IntVal(100/$num_cols)."%\" nowrap=\"nowrap\">\n";
		$res_str .= "<img src=\"".$strPath2Icons.$res['IMAGE']."\" alt=\"".$res['NAME']."\" border=\"0\" class=\"icons\"";
		if (IntVal($res['IMAGE_WIDTH'])>0) $res_str .= " width=\"".$res['IMAGE_WIDTH']."\"";
		if (IntVal($res['IMAGE_HEIGHT'])>0) $res_str .= " height=\"".$res['IMAGE_HEIGHT']."\"";
		$res_str .= "/>\n";
		$res_str .= "<input type=\"radio\" name=\"".$varField."\" value=\"".$res['ID']."\"";
		if (IntVal($res['ID'])==IntVal($varValue)) $res_str .= " checked";
		$res_str .= "/>\n&nbsp;\n";
		$res_str .= "</td>\n";
		$ind++;
		if ($ind >= $num_cols)
		{
			$ind = 0;
			$res_str .= "</tr>";
		}
	}
	if (($ind==0) || ($num_cols-$ind)<3)
	{
		if (($num_cols-$ind)<3)
		{
			for ($i=0; $i<$num_cols-$ind; $i++)
			{
				$res_str .= "<td width='10%'> </td>";
			}
			$res_str .= "</tr>";
			$ind = 0;
		}
		$res_str .= "<tr align=\"left\">";
	}
	
	$res_str .= "<td colspan=\"3\" nowrap><font class=\"forumbodytext\">";
	$res_str .= $defValue;
	$res_str .= "<input type=\"radio\" name=\"".$varField."\" value=\"0\"";
	if (0==IntVal($varValue)) $res_str .= " checked ";
	$res_str .= "/>\n&nbsp;\n</font>";
	$res_str .= "</td>\n";
	
	$ind = $ind + 3;
	if ($ind >= $num_cols)
	{
		$ind = 0;
		$res_str .= "</tr>";
	}
	else
	{
		for ($i=0; $i<$num_cols-$ind; $i++)
		{
			$res_str .= "<td width='10%'> </td>";
		}
		$res_str .= "</tr>";
	}
	$res_str .= "</table>";	
		
	return $res_str;
}

function ForumPrintSmilesList($num_cols, $strLang = false, $strPath2Icons = false)
{
	$num_cols = intVal($num_cols);
	$num_cols = $num_cols > 0 ? $num_cols : 3;
	$strLang = ($strLang === false ? LANGUAGE_ID : $strLang);
	$strPath2Icons = ($strPath2Icons === false ? "/bitrix/images/forum/smile/" : $strPath2Icons);
	$arSmile = CForumSmile::GetByType("S", $strLang);
	
	$res_str = "";
	$ind = 0;
	foreach ($arSmile as $res)
	{
		if ($ind == 0) {$res_str .= "<tr align=\"center\">";}
		$res_str .= "<td width=\"".IntVal(100/$num_cols)."%\">";
		$strTYPING = strtok($res['TYPING'], " ");
		$res_str .= "<img src=\"".$strPath2Icons.$res['IMAGE']."\" alt=\"".$res['NAME']."\" title=\"".$res['NAME']."\" border=\"0\"";
		if (IntVal($res['IMAGE_WIDTH'])>0) {$res_str .= " width=\"".$res['IMAGE_WIDTH']."\"";}
		if (IntVal($res['IMAGE_HEIGHT'])>0) {$res_str .= " height=\"".$res['IMAGE_HEIGHT']."\"";}
		$res_str .= " class=\"smiles-list\" alt=\"smile".$strTYPING."\" onclick=\"if(emoticon){emoticon('".$strTYPING."');}\" name=\"smile\"  id='".$strTYPING."' ";
		$res_str .= "/>&nbsp;</td>\n";
		$ind++;
		if ($ind >= $num_cols)
		{
			$ind = 0;
			$res_str .= "</tr>";
		}
	}
	if ($ind < $num_cols)
	{
		for ($i=0; $i<$num_cols-$ind; $i++)
		{
			$res_str .= "<td> </td>";
		}
	}
	
	return $res_str;
}

function ForumMoveMessage2Support($MID, &$strErrorMessage, &$strOKMessage, $arAddParams = array())
{
	global $USER;
	$MID = IntVal($MID);
	$sError = array();
	$sNote = array();
	$arAddParams = (!is_array($arAddParams) ? array($arAddParams) : $arAddParams );
	$arAddParams["PERMISSION"] = (!empty($arAddParams["PERMISSION"]) ? $arAddParams["PERMISSION"] : false);
	if ($MID<=0)
		$arError[] = GetMessage("MOVEMES_NO_MESS_EX");

	if (!CModule::IncludeModule("support"))
		$arError[] = GetMessage("MOVEMES_NO_SUPPORT");

	if (empty($arError))
	{
		$arMessage = CForumMessage::GetByID($MID, array("FILTER" => "N"));
		if (!$arMessage)
		{
			$arError[] = GetMessage("MOVEMES_NO_MESS_EX");
		}
		elseif (IntVal($arMessage["AUTHOR_ID"])<=0) 
		{
			$arError[] = GetMessage("MOVEMES_NO_ANONYM");
		}
		elseif (!CForumMessage::CanUserDeleteMessage($MID, $USER->GetUserGroupArray(), $USER->GetID(), $arAddParams["PERMISSION"]))
		{
			$arError[] = GetMessage("MOVEMES_NO_PERMS2MOVE");
		}
		else
		{
			$arTopic = CForumTopic::GetByID($arMessage["TOPIC_ID"]);
			$arIMAGE = array();
			if (intVal($arMessage["ATTACH_IMG"])>0)
			{
				$db_file_tmp = CFile::GetByID($arMessage["ATTACH_IMG"]);
				if ($ar_file_tmp = $db_file_tmp->Fetch())
				{
					$strFile_tmp = $_SERVER["DOCUMENT_ROOT"]."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$ar_file_tmp["SUBDIR"]."/".$ar_file_tmp["FILE_NAME"];
					$strFile_tmp = str_replace("//", "/", $strFile_tmp);
					$arIMAGE = array(
						"name" => $ar_file_tmp["ORIGINAL_NAME"],
						"type" => $ar_file_tmp["CONTENT_TYPE"],
						"size" => $ar_file_tmp["FILE_SIZE"],
						"tmp_name" => $strFile_tmp,
						"MODULE_ID" => "support"
						);
				}
			}
			$arFieldsSu = array(
				"CLOSE"			=> "N",
				"TITLE"			=> $arTopic["TITLE"],
				"MESSAGE"		=> $arMessage["POST_MESSAGE"],
				"OWNER_USER_ID"	=> $arMessage["AUTHOR_ID"],
				"OWNER_SID"		=> $arMessage["AUTHOR_NAME"],
				"SOURCE_SID"	=> "forum",
				"FILES"			=> array($arIMAGE)
				);
			$SuID = CTicket::SetTicket($arFieldsSu);
			$SuID = IntVal($SuID);
	
			if ($SuID>0)
			{
				$sNote[] = GetMessage("MOVEMES_SUCCESS_SMOVE");
			}
			else
			{
				$arError[] = GetMessage("MOVEMES_ERROR_SMOVE");
			}
		}
	}
	if (!empty($arError))
		$strErrorMessage .= implode(".\n",$arError).".\n";
	if (!empty($arNote))
		$strOKMessage .= implode(".\n",$arNote).".\n";
		
	if (empty($arError))
		return $SuID;
	else
		return False;
}
// out of time function
function ForumSetAllMessagesReaded($FID = false) // out of time function
{
	global $USER;

	if ($FID!==false)
	{	
		$FID = IntVal($FID);
		CForumNew::SetLabelsBeRead($FID, $USER->GetUserGroupArray());
		return true;
	}

	$arFilter = array();
	if (!$USER->IsAdmin())
	{
		$arFilter["LID"] = LANG;
		$arFilter["PERMS"] = array($USER->GetGroups(), 'A');
		$arFilter["ACTIVE"] = "Y";
	}
	$db_Forum = CForumNew::GetList(array(), $arFilter);
	while ($ar_Forum = $db_Forum->Fetch())
	{
		CForumNew::SetLabelsBeRead($ar_Forum["ID"], $USER->GetUserGroupArray());
	}

	return false;
}
// out of time function
function ForumSetReader($FID)
{
	global $USER;
	$FID = intVal($FID);
	$_SESSION["FORUM"]["LAST_VISIT_FORUM_".$FID] = CForumNew::GetNowTime("timestamp");
	return false;	
}

function ForumSetAllMessagesRead($FID = false)
{
	ForumSetReadForum($FID);
}

function ForumVote4User($UID, $VOTES, $bDelVote, &$strErrorMessage, &$strOKMessage)
{
	global $USER;
	$arError = array();
	$arNote = array();
	
	$UID = IntVal($UID);
	$VOTES = IntVal($VOTES);
	$bDelVote = ($bDelVote ? true : false);
	$CurrUserID = 0;				
	
	if ($UID <= 0)
	{
		$arError[] = GetMessage("F_NO_VPERS");
	}
	else 
	{
		if (!$USER->IsAuthorized())
		{
			$arError[] = GetMessage("FORUM_GV_ERROR_AUTH");
		}
		else 
		{
			$CurrUserID = IntVal($USER->GetParam("USER_ID"));
			if ($CurrUserID == $UID && !$USER->IsAdmin())
			{
				$arError[] = GetMessage("FORUM_GV_OTHER");
			}
			else
			{
				$arUserRank = CForumUser::GetUserRank($CurrUserID);
		
				if (IntVal($arUserRank["VOTES"])<=0 && !$bDelVote && !$USER->IsAdmin())
				{
					$arError[] = GetMessage("FORUM_GV_ERROR_NO_VOTE");
				}
				else 
				{
					if (!$USER->IsAdmin() || $VOTES<=0)
						$VOTES = IntVal($arUserRank["VOTES"]);
			
					$arFields = array(
						"POINTS" => $VOTES
						);
	
					$arUserPoints = CForumUserPoints::GetByID($CurrUserID, $UID);
					if ($arUserPoints)
					{
						if ($bDelVote || $VOTES<=0)
						{
							if (CForumUserPoints::Delete($CurrUserID, $UID))
								$arNote[] = GetMessage("FORUM_GV_SUCCESS_UNVOTE");
							else
								$arError[] = GetMessage("FORUM_GV_ERROR_VOTE");
						}
						else
						{
							if (IntVal($arUserPoints["POINTS"])<IntVal($arUserRank["VOTES"])
								|| $USER->IsAdmin())
							{
								if (CForumUserPoints::Update(IntVal($USER->GetParam("USER_ID")), $UID, $arFields))
									$arNote[] = GetMessage("FORUM_GV_SUCCESS_VOTE_UPD");
								else
									$arError[] = GetMessage("FORUM_GV_ERROR_VOTE_UPD");
							}
							else
							{
								$arError[] = GetMessage("FORUM_GV_ALREADY_VOTE");
							}
						}
					}
					else
					{
						if (!$bDelVote && $VOTES>0)
						{
							$arFields["FROM_USER_ID"] = $USER->GetParam("USER_ID");
							$arFields["TO_USER_ID"] = $UID;
			
							if (CForumUserPoints::Add($arFields))
								$arNote[] = GetMessage("FORUM_GV_SUCCESS_VOTE_ADD");
							else
								$arError[] = GetMessage("FORUM_GV_ERROR_VOTE_ADD");
						}
						else
						{
							$arError[] = GetMessage("FORUM_GV_ERROR_A");
						}
					}
				}
			}
		}
	}
	
	if (!empty($arError))
		$strErrorMessage .= implode(".\n", $arError).".\n";
	if (!empty($arNote))
		$strOKMessage .= implode(".\n", $arNote).".\n";
		
	if (empty($arError))
		return True;
	else
		return False;
}

function ForumDeleteSubscribe($ID, &$strErr, &$strOk)
{
	global $USER;
	$ID = IntVal($ID);
	if (CForumSubscribe::CanUserDeleteSubscribe($ID, $USER->GetUserGroupArray(), $USER->GetID()))
	{
		CForumSubscribe::Delete($ID);
		return true;
	}
	else
	{
		$strErr = GetMessage("FSUBSC_NO_SPERMS").". \n";
	}
	return false;
}

function ShowActiveUser($arFields = array())
{
	$period = intVal($arFields["PERIOD"]);
	if ($period <= 0)
		$period = 600;
		
	$date = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)), time()-$period);
	$arField = array(">=LAST_VISIT" => $date, "COUNT_GUEST"=>true);
	if (intVal($arFields["FORUM_ID"]) > 0 )
		$arField["FORUM_ID"] = $arFields["FORUM_ID"];
	if (intVal($arFields["TOPIC_ID"]) > 0 )
		$arField["TOPIC_ID"] = $arFields["TOPIC_ID"];
	
	$db_res = CForumStat::GetListEx(array("USER_ID" => "DESC"), $arField);
	$OnLineUser = array();
	$arOnLineUser = array();
	$OnLineUserStr = "";
	$UserHideOnLine = 0;
	$UserOnLine = 0;
	$result = array();
	$result["NONE"]	= "N";
	if ($db_res && ($res = $db_res->GetNext()))
	{
		$OnLineUser["USER"] = array();
		do 
		{
			if (($res["USER_ID"] > 0) && ($res["HIDE_FROM_ONLINE"] != "Y"))
			{
				$OnLineUser["USER"][] = "<a href=\"view_profile.php?UID=".$res["USER_ID"]."\" title='".GetMessage("FORUM_USER_PROFILE")."'>".$res["SHOW_NAME"]."</a>";
				$arOnLineUser[] = array_merge($res, array("UID"=>$res["USER_ID"], "title" => GetMessage("FORUM_USER_PROFILE"), "text" => $res["SHOW_NAME"]));
			}
			elseif(($res["USER_ID"] > 0) && ($res["HIDE_FROM_ONLINE"] == "Y"))
				$UserHideOnLine++;				
			else
				$OnLineUser["GUEST"] = intVal($res["COUNT_USER"]);
		}while ($res = $db_res->GetNext());
		
		$CountAllUsers = count($OnLineUser["USER"]) + $UserHideOnLine + $OnLineUser["GUEST"];
		$result["GUEST"] = $OnLineUser["GUEST"];
		$result["HIDE"] = $UserHideOnLine;
		$result["REGISTER"] = IntVal(count($OnLineUser["USER"])+$UserHideOnLine);
		$result["ALL"] = $CountAllUsers;
		
		if ($CountAllUsers > 0)
		{
			if (intVal($arFields["TOPIC_ID"]) <= 0)
			{
				$result["PERIOD"] = round($period/60);
				$result["HEAD"] = str_replace("##", "<b>".round($period/60)."</b>", GetMessage("FORUM_AT_LAST_PERIOD"))." ".
				GetMessage("FORUM_COUNT_ALL_USER").": <b>".$CountAllUsers."</b><br/>";
			}	
			$OnLineUserStr = GetMessage("FORUM_COUNT_GUEST").": <b>".intVal($OnLineUser["GUEST"])."</b>, ".
				GetMessage("FORUM_COUNT_USER").": <b>".IntVal(count($OnLineUser["USER"])+$UserHideOnLine)."</b>, 
				".GetMessage("FORUM_FROM_THIS")." ".GetMessage("FORUM_COUNT_USER_HIDEFROMONLINE").": <b>".$UserHideOnLine."</b>";
				
			if (count($OnLineUser["USER"]) > 0)
			{
				$OnLineUserStr .= "<br/>".implode(", ", $OnLineUser["USER"])."<br/>";
				$result["USER"] = $arOnLineUser;
			}
		}
		else 
		{
			$OnLineUserStr = GetMessage("FORUM_NONE");
			$result["NONE"] = "Y";
		}
	}
	else 
	{
		$OnLineUserStr = GetMessage("FORUM_NONE");
		$result["NONE"] = "Y";
	}
	$result["BODY"] = $OnLineUserStr;
	return $result;
}

function ForumInitParams()
{
//	unset($_SESSION["FORUM"]);
	$UserLogin = "GUEST";
	$LastVisit = time();
	if ($GLOBALS["USER"]->IsAuthorized())
	{
		if (!is_array($_SESSION["FORUM"]["USER"]) || $_SESSION["FORUM"]["USER"]["USER_ID"] != $GLOBALS["USER"]->GetID()):
			$_SESSION["FORUM"]["USER"] = CForumUser::GetByUSER_ID($GLOBALS["USER"]->GetID());
			if ($_SESSION["FORUM"]["USER"]):
				$_SESSION["FORUM"]["USER"]["LAST_VISIT_TIMESTAMP"] = MakeTimeStamp($_SESSION["FORUM"]["USER"]["LAST_VISIT"]);
			else:
				$_SESSION["FORUM"]["USER"] = array();
				$_SESSION["FORUM"]["USER"]["LAST_VISIT_TIMESTAMP"] = CForumNew::GetNowTime("timestamp");
			endif;
		elseif (empty($_SESSION["FORUM"]["USER"]["LAST_VISIT_TIMESTAMP"])):
			$_SESSION["FORUM"]["USER"]["LAST_VISIT_TIMESTAMP"] = CForumNew::GetNowTime("timestamp");
		endif;
		
		$arUser = $_SESSION["FORUM"]["USER"];
		$UserLogin = $GLOBALS["USER"]->GetLogin();
		$LastVisit = $_SESSION["FORUM"]["USER"]["LAST_VISIT_TIMESTAMP"];
		
		// if info for this user is not exist that info gets from DB
		if (!is_array($_SESSION["FORUM"][$UserLogin]) || intVal($_SESSION["FORUM"][$UserLogin][0]) <= 0)
		{
			$_SESSION["FORUM"][$UserLogin] = array();
			$db_res = CForumUser::GetListUserForumLastVisit(array(), array("USER_ID" => $GLOBALS["USER"]->GetID()));
			if ($db_res && $res = $db_res->Fetch()):
				do 
				{
					$_SESSION["FORUM"][$UserLogin][intVal($res["FORUM_ID"])] = MakeTimeStamp($res["LAST_VISIT"]);
				}while ($res = $db_res->Fetch());
			endif;
			
			if (intVal($_SESSION["FORUM"][$UserLogin][0]) <= 0):
				$_SESSION["FORUM"][$UserLogin] = array();
				CForumUser::SetUserForumLastVisit($GLOBALS["USER"]->GetID(), 0, false);
				$db_res = CForumUser::GetListUserForumLastVisit(array(), array("USER_ID" => $GLOBALS["USER"]->GetID(), "FORUM_ID" => 0));
				if ($db_res && $res = $db_res->Fetch()):
					$_SESSION["FORUM"][$UserLogin][0] = MakeTimeStamp($res["LAST_VISIT"]);
				else:
					$_SESSION["FORUM"][$UserLogin][0] = $LastVisit;
				endif;
			endif;
		}
		
		// synhronize guest session with authorized user session
		if (is_array($_SESSION["FORUM"]["GUEST_TID"]) && !empty($_SESSION["FORUM"]["GUEST_TID"]))
		{
			foreach ($_SESSION["FORUM"]["GUEST_TID"] as $key => $val):
				CForumTopic::SetReadLabelsNew($key, false, $val, array("UPDATE_TOPIC_VIEWS" => "N"));
			endforeach;
		}
//		if (is_array($_SESSION["FORUM"]["GUEST"]) && (!empty($_SESSION["FORUM"]["GUEST"])))
//		{
//			foreach ($_SESSION["FORUM"]["GUEST"] as $key => $val)
//			{
//				if (intVal($val) > intVal($_SESSION["FORUM"][$UserLogin][intVal($key)]))
//					$_SESSION["FORUM"][$UserLogin][intVal($key)] = intVal($val);
//			}
//		}
		unset($_SESSION["FORUM"]["GUEST_TID"]);
		unset($_SESSION["FORUM"]["GUEST"]);
	}
	else // If user is not authorized that get info from cookies only
	{
		if (!isset($_SESSION["FORUM"]["GUEST"]) || !is_array($_SESSION["FORUM"]["GUEST"]))
		{
			$forum_cookie = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_GUEST";
			if (isset($_COOKIE[$forum_cookie]) && strlen($_COOKIE[$forum_cookie])>0)
			{
				$arForum = explode("/", $_COOKIE[$forum_cookie]);
				if (is_array($arForum) && count($arForum) > 0)
				{
					foreach ($arForum as $forumInfo)
					{
						list($f, $lv) = explode("-", $forumInfo);
						$_SESSION["FORUM"]["GUEST"][intVal($f)] = intVal($lv);
					}
				}
			}
		}
		
		if (!isset($_SESSION["FORUM"]["GUEST"]) || !is_array($_SESSION["FORUM"]["GUEST"]) || (intVal($_SESSION["FORUM"]["GUEST"][0]) < 0))
		{
			$_SESSION["FORUM"]["GUEST"] = array();
			$_SESSION["FORUM"]["GUEST"][0] = CForumNew::GetNowTime();
		}
		// All geting info put in cookies
		if (COption::GetOptionString("forum", "USE_COOKIE", "N") == "Y"):
			$arCookie = array();
			foreach ($_SESSION["FORUM"]["GUEST"] as $key => $val):
				$arCookie[] = $key."-".$val;
			endforeach;
			$GLOBALS["APPLICATION"]->set_cookie("FORUM_GUEST", implode("/", $arCookie), false, "/", false, false, "Y", false);
		endif;
		
//		It need to save info about visited topics for GUEST in cookies
		if (!isset($_SESSION["FORUM"]["GUEST_TID"]) || !is_array($_SESSION["FORUM"]["GUEST_TID"]))
		{
			$_SESSION["FORUM"]["GUEST_TID"] = array();
			$topic_cookie = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_GUEST_TID";
			if (isset($_COOKIE[$topic_cookie]) && strlen($_COOKIE[$topic_cookie]) > 0):
				$arTopic = explode("/", $_COOKIE[$topic_cookie]);
				if (is_array($arTopic) && count($arTopic) > 0):
					foreach ($arTopic as $topicInfo):
						list($f, $lv) = explode("-", $topicInfo);
						$_SESSION["FORUM"]["GUEST_TID"][intVal($f)] = intVal($lv);
					endforeach;
				endif;
			endif;
		}
	}
	// cleaning session date.
	if (is_array($_SESSION["FORUM"]))
	{
		foreach ($_SESSION["FORUM"] as $key => $val):
			if (substr($key, 0, strLen("LAST_VISIT_FORUM_")) == "LAST_VISIT_FORUM_"):
				unset($_SESSION["FORUM"][$key]);
			endif;
		endforeach;
	}
	// and put info in public variable
	if (is_array($_SESSION["FORUM"][$UserLogin])):
		foreach ($_SESSION["FORUM"][$UserLogin] as $key => $val):
			$_SESSION["FORUM"]["LAST_VISIT_FORUM_".$key] = $val;
		endforeach;
	else: 
		$_SESSION["FORUM"]["LAST_VISIT_FORUM_0"] = CForumNew::GetNowTime();
	endif;
	
	return $_SESSION;
}

function NewMessageForum($FID, $LAST_POST_DATE = false)
{
	if (intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]) <= 0)
		ForumInitParams();

	$FID = intVal($FID);
	$LAST_VISIT = max($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"], $_SESSION["FORUM"]["LAST_VISIT_FORUM_".$FID]);
	$LAST_POST_DATE = MakeTimeStamp($LAST_POST_DATE);
	
	if (intVal($LAST_POST_DATE) > 0 && $LAST_POST_DATE < $LAST_VISIT):
		"";
	elseif ($GLOBALS["USER"]->IsAuthorized()):
		$arFilter = array("FORUM_ID" => $FID, "RENEW" => $GLOBALS["USER"]->GetID());
		if (ForumCurrUserPermissions($FID) < "Q"):
			$arFilter["APPROVED"] = "Y";
		endif;
		$db_res = CForumTopic::GetListEx(array("ID" => "DESC"), $arFilter, false, 1);
		if ($db_res && $res = $db_res->Fetch()):
			return true;
		endif;
	else:
		$arFilter = array("FORUM_ID" => $FID);
		if (is_array($_SESSION["FORUM"]["GUEST_TID"]) && !empty($_SESSION["FORUM"]["GUEST_TID"])):
			$arFilter["RENEW_TOPIC"][0] = ConvertTimeStamp($LAST_VISIT, "FULL");
			foreach ($_SESSION["FORUM"]["GUEST_TID"] as $key => $val):
				$arFilter["RENEW_TOPIC"][intVal($key)] = ConvertTimeStamp($val, "FULL");
			endforeach;
		else: 
			$arFilter[">LAST_POST_DATE"] = ConvertTimeStamp($LAST_VISIT, "FULL");
		endif;
		if (ForumCurrUserPermissions($FID) < "Q"):
			$arFilter["APPROVED"] = "Y";
		endif;
		$db_res = CForumTopic::GetList(array(), $arFilter, false, 1);
		if ($db_res && $res = $db_res->Fetch()):
			return true;
		endif;
	endif;
	ForumInitParams();
	return false;
}

function NewMessageTopic($FID, $TID, $LAST_POST_DATE, $LAST_VISIT)
{
	if (intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]) <= 0)
		ForumInitParams();

	$TID = intVal($TID);
	$LAST_POST_DATE = intVal(MakeTimeStamp($LAST_POST_DATE));
	$LAST_VISIT = intVal($GLOBALS["USER"]->IsAuthorized() ? MakeTimeStamp($LAST_VISIT) : $_SESSION["FORUM"]["GUEST_TID"][$TID]);
	$LAST_VISIT = max($LAST_VISIT, $_SESSION["FORUM"]["LAST_VISIT_FORUM_0"], intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_".$FID]));
	return ($LAST_POST_DATE > $LAST_VISIT ? true : false);
}

function ForumSetReadForum($FID = false)
{
	$UserLogin = "GUEST";
	$timestamp = CForumNew::GetNowTime("timestamp");
	$FID = intVal($FID);
	
	if ($GLOBALS["USER"]->IsAuthorized()):
		$UserLogin = $GLOBALS["USER"]->GetLogin();
		CForumUser::SetUserForumLastVisit($GLOBALS["USER"]->GetID(), $FID, $timestamp);
	endif;
	
	if ($FID <= 0)
	{
		if (is_array($_SESSION["FORUM"])):
			foreach ($_SESSION["FORUM"] as $key => $val):
				if (substr($key, 0, strLen("LAST_VISIT_FORUM_")) == "LAST_VISIT_FORUM_"):
					unset($_SESSION["FORUM"][$key]);
				endif;
			endforeach;
		endif;
		unset($_SESSION["FORUM"][$UserLogin]);
	}
	$_SESSION["FORUM"][$UserLogin][$FID] = $timestamp;
	$_SESSION["FORUM"]["LAST_VISIT_FORUM_".$FID] = $timestamp;
	return ForumInitParams();
}

function ForumSetReadTopic($FID, $TID)
{
	CForumTopic::SetReadLabelsNew($TID);
	
	if (!$GLOBALS['USER']->IsAuthorized()) 
	{
		if (!isset($_SESSION["FORUM"]["GUEST_TID"]))
			ForumInitParams();
		$_SESSION["FORUM"]["GUEST_TID"][intVal($TID)] = CForumNew::GetNowTime();
		if (COption::GetOptionString("forum", "USE_COOKIE", "N") == "Y")
		{
			$arCookie = array();
			foreach ($_SESSION["FORUM"]["GUEST_TID"] as $key => $val):
				$arCookie[] = intVal($key)."-".intVal($val);
			endforeach;
			$GLOBALS["APPLICATION"]->set_cookie("FORUM_GUEST_TID", implode("/", $arCookie), false, "/", false, false, "Y", false);
		}
	}
}

function ForumSetLastVisit($FID = false, $TID = false)
{
	global $USER, $DB;
	// For custom components
	if ($FID === false && intVal($GLOBALS["FID"]) > 0) 
		$FID = intVal($GLOBALS["FID"]);
	$GLOBALS["FID"] = $FID;
	
	if ($USER->IsAuthorized())
	{
		$GLOBALS["SHOW_FORUM_ICON"] = true; // out-of-date param
		$USER_ID = $USER->GetID();
		$arUserFields = array("=LAST_VISIT" => $DB->GetNowFunction());
		
		if (!is_array($_SESSION["FORUM"]["USER"]) || $_SESSION["FORUM"]["USER"]["USER_ID"] != $GLOBALS["USER"]->GetID()):
			$_SESSION["FORUM"]["USER"] = CForumUser::GetByUSER_ID($GLOBALS["USER"]->GetID());
			if (!$_SESSION["FORUM"]["USER"]):
				$arUserFields["USER_ID"] = $USER_ID;
				$ID = CForumUser::Add($arUserFields);
				$_SESSION["FORUM"]["USER"] = CForumUser::GetByUSER_ID($GLOBALS["USER"]->GetID());
			endif;
			$_SESSION["FORUM"]["SHOW_NAME"] = $_SESSION["FORUM"]["USER"]["SHOW_NAME"];
		endif;
		
		$arUser = $_SESSION["FORUM"]["USER"];
		if (!is_set($arUserFields, "USER_ID")):
			CForumUser::Update($USER_ID, $arUserFields, false, true);
		endif;
	}
	
	ForumInitParams();
	
	if (CModule::IncludeModule("statistic") && ($_SESSION["SESS_SEARCHER_ID"] > 0))
		return;
	else
		CForumStat::RegisterUSER(array("SITE_ID" => SITE_ID, "FORUM_ID" => $FID, "TOPIC_ID" => $TID));
	return true;
}

function ForumGetFirstUnreadMessage($FID, $TID)
{
	global $USER, $DB;
	$TID = intVal($TID);
	if ($TID > 0 )
	{
		if (intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]) <= 0)
			ForumInitParams();
		$LastVisit = max(intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]), intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_".$FID]));
		
		if ($USER->IsAuthorized())
		{
			$db_res = CForumMessage::GetListEx(array("ID" => "ASC"), 
				array("TOPIC_ID" => $TID, "USER_ID" => $USER->GetId(), ">NEW_MESSAGE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), $LastVisit)), 0, 1);
		}
		else 
		{
			$LastVisit = max($LastVisit, intVal($_SESSION["FORUM"]["GUEST_TID"][$TID]));
			$db_res = CForumMessage::GetList(array("ID" => "ASC"), 
				array("TOPIC_ID" => $TID, ">POST_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), $LastVisit)), 0, 1);
		}
		if ($db_res && $res = $db_res->Fetch())
			return $res["ID"];
	}
	return false;
}

function ForumAddPageParams($page_url="", $params=array(), $addIfNull = false, $htmlSpecialChars = true)
{
	$strUrl = "";
	$strParams = "";
	$arParams = array();
	$param = "";
	// Attention: $page_url already is safe.
	if (is_array($params) && (count($params) > 0))
	{
		foreach ($params as $key => $val)
		{
			if ((is_array($val) && (count($val) > 0)) || ((strLen($val)>0) && ($val!="0")) || (intVal($val) > 0) || $addIfNull)
			{
				if (is_array($val))
					$param = implode(",", $val);
				else 
					$param = $val;
				if ((strLen($param) > 0) || ($addIfNull))
				{
					if (strPos($page_url, $key) !== false)
					{
						$page_url = preg_replace("/".$key."\=[^\&]*((\&amp\;)|(\&)*)/", "", $page_url);
					}
					$arParams[] = $key."=".$param;
				}
			}
		}
		
		if (count($arParams) > 0)
		{
			if (strPos($page_url, "?") === false)
				$strParams = "?";
			elseif ((substr($page_url, -5, 5) != "&amp;") && (substr($page_url, -1, 1) != "&") && (substr($page_url, -1, 1) != "?"))
			{
				$strParams = "&";
			}
			$strParams .= implode("&", $arParams);
			if ($htmlSpecialChars)
				$page_url .= htmlspecialchars($strParams);
			else
				$page_url .= $strParams;
		}
	}
	return $page_url;
}

function ForumActions($action, $arFields, &$strErrorMessage, &$strOKMessage)
{
	$result = false;
	$sError = "";
	$sNote = "";
	if (empty($action))
	{
		$sError = GetMessage("FORUM_NO_ACTION");
	}
	else 
	{
		switch ($action)
		{
			case "REPLY":
				$result = ForumAddMessage("REPLY", $arFields["FID"], $arFields["TID"], 0, $arFields, $sError, $sNote, false, $arFields["captcha_word"], 0, $arFields["captcha_code"]);
				break;
			case "DEL":
				$result = ForumDeleteMessage($arFields["MID"], $sError, $sNote, $arFields);
			break;
			case "SHOW":
			case "HIDE":
				$result = ForumModerateMessage($arFields["MID"], $action, $sError, $sNote, $arFields);
				break;
			case "VOTE4USER":
				$result = ForumVote4User($arFields["UID"], $arFields["VOTES"], $arFields["VOTE"], $sError, $sNote, $arFields);
			break;
			case "FORUM_MESSAGE2SUPPORT":
				$result = ForumMoveMessage2Support($arFields["MID"], $sError, $sNote, $arFields);
			break;
			case "FORUM_SUBSCRIBE":
			case "TOPIC_SUBSCRIBE":
			case "FORUM_SUBSCRIBE_TOPICS":
				$result = ForumSubscribeNewMessagesEx($arFields["FID"], $arFields["TID"], $arFields["NEW_TOPIC_ONLY"], $sError, $sNote);
			break;
			case "SET_ORDINARY":
			case "SET_TOP":
			case "ORDINARY":
			case "TOP":
				if ($action == "SET_ORDINARY")
					$action = "ORDINARY";
				elseif ($action == "SET_TOP")
					$action = "TOP";
					
				$result = ForumTopOrdinaryTopic($arFields["TID"], $action, $sError, $sNote, $arFields);
			break;
			case "DEL_TOPIC":
				$result =  ForumDeleteTopic($arFields["TID"], $sError, $sNote, $arFields);
			break;
			case "OPEN":
			case "CLOSE":
			case "STATE_Y":
			case "STATE_N":
				if ($action == "STATE_Y")
					$action = "OPEN";
				elseif ($action == "STATE_N")
					$action = "CLOSE";
				$result = ForumOpenCloseTopic($arFields["TID"], $action, $sError, $sNote, $arFields);
			break;
			case "SHOW_TOPIC":
			case "HIDE_TOPIC":
				$db_res = CForumMessage::GetList(array(), array("TOPIC_ID" => $arFields["TID"], 
					"APPROVED" => ($action == "HIDE_TOPIC" ? "Y" : "N")));
				$message = array();
				if ($db_res && $res = $db_res->Fetch()):
					do
					{
						$message[] = $res["ID"];
					} while ($res = $db_res->Fetch());
				endif;
				if (!empty($message)):
					$s = "";
					$result = ForumModerateMessage($message, ($action == "HIDE_TOPIC" ? "HIDE" : "SHOW"), $sError, $s, $arFields);
				else: 
					$result = true;
				endif;
				CForumEventLog::Log("topic", ($action == "HIDE_TOPIC" ? "unapprove" : "approve"), $arFields["TID"], print_r(CForumTopic::GetByID($arFields["TID"]), true));
			break;
			case "SPAM_TOPIC":
				$result =  ForumSpamTopic($arFields["TID"], $sError, $sNote, $arFields);
			break; 
			case "SPAM": 
				$result = ForumSpamMessage($arFields["MID"], $sError, $sNote, $arFields); 
			break; 
			default:
				$sError = GetMessage("FORUM_NO_ACTION")." (".htmlspecialchars($action).")";
			break;
		}
	}
	$strErrorMessage = $sError;
	$strOKMessage = $sNote;
	return $result;
}

function ForumDataToArray(&$message)
{
	if (!is_array($message))
		$message = explode(",", $message);
	
	foreach ($message as $key => $val)
	{
		$message[$key] = intVal(trim($val));
	}
		
	if (array_sum($message) > 0)
		return $message;
	else 
		return false;
}

function ForumGetTopicSort(&$field_name, &$direction, $arForumInfo = array())
{
	$aSortOrder = array(
		"P" => "LAST_POST_DATE", 
		"T" => "TITLE", 
		"N" => "POSTS", 
		"V" => "VIEWS", 
		"D" => "START_DATE", 
		"A" => "USER_START_NAME");
	if (!empty($arForumInfo))
	{
		$field_name = trim($arForumInfo["ORDER_BY"]);
		$direction = trim($arForumInfo["ORDER_DIRECTION"]);
	}
	$field_name = strToUpper($field_name);
	$direction = strToUpper($direction);
	$field_name = (empty($aSortOrder[$field_name]) ? "LAST_POST_DATE" : $aSortOrder[$field_name]);
	$direction = ($direction == "ASC" ? "ASC" : "DESC");
	return array($field_name => $direction);
}

function ForumShowError($arError, $bShowErrorCode = false)
{
	$bShowErrorCode = ($bShowErrorCode === true ? true : false);
	$sReturn = "";
	$tmp = false; 
	$arRes = array();
	if (empty($arError))
		return $sReturn;
	elseif (!is_array($arError))
		return $arError;
		
	if (!empty($arError["title"]) || !empty($arError["code"]))
	{
		$res = $arError;
		$sReturn .= (!empty($res["title"]) ? $res["title"] : $res["code"]).
			($bShowErrorCode ? "[CODE: ".$res["code"]."]" : "");
		unset($arError["code"]); unset($arError["title"]);
	}
	foreach ($arError as $res):
		$sReturn .= (!empty($res["title"]) ? $res["title"] : $res["code"]).
			($bShowErrorCode ? "[CODE: ".$res["code"]."]" : "")." ";
	endforeach;
	return $sReturn;
}
function ForumClearComponentCache($components)
{
	if (empty($components))
		return false;
	$aComponents = (is_array($components) ? $components : explode(",", $components));
		
	foreach($aComponents as $component_name)
	{
		$componentRelativePath = CComponentEngine::MakeComponentPath($component_name);
		if (strlen($componentRelativePath) > 0)
		{
			$arComponentDescription = CComponentUtil::GetComponentDescr($component_name);
			if (is_array($arComponentDescription) && array_key_exists("CACHE_PATH", $arComponentDescription))
			{
				if($arComponentDescription["CACHE_PATH"] == "Y")
					$arComponentDescription["CACHE_PATH"] = "/".SITE_ID.$componentRelativePath;
				if(strlen($arComponentDescription["CACHE_PATH"]) > 0)
					BXClearCache(true, $arComponentDescription["CACHE_PATH"]);
			}
		}
	}
}

function InitSortingEx($Path=false, $sByVar="by", $sOrderVar="order")
{
    static $ii = -1;
    $ii++;
    global $APPLICATION, $$sByVar, $$sOrderVar;
    $sByVarE = $sByVar . $ii;
    $sOrderVarE = $sOrderVar . $ii;
    global $$sByVarE, $$sOrderVarE;

    if($Path===false)
        $Path = $APPLICATION->GetCurPage();

    $md5Path = md5($Path);
    if (strlen($$sByVarE)>0)
        $_SESSION["SESS_SORT_BY_EX"][$md5Path][$sByVarE] = $$sByVarE;
    else
        $$sByVarE = $_SESSION["SESS_SORT_BY_EX"][$md5Path][$sByVarE];

    if(strlen($$sOrderVarE)>0)
        $_SESSION["SESS_SORT_ORDER_EX"][$md5Path][$sOrderVarE] = $$sOrderVarE;
    else
        $$sOrderVarE = $_SESSION["SESS_SORT_ORDER_EX"][$md5Path][$sOrderVarE];

    strtolower($$sByVarE);
    strtolower($$sOrderVarE);
    $$sByVar = $$sByVarE;
    $$sOrderVar = $$sOrderVarE;
    return $ii;
}

/*
GetMessage("FORUM_NO_MODULE");
*/
?>
