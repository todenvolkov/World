<?
IncludeModuleLangFile(__FILE__);

class CAllSocNetLog
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $arSocNetAllowedEntityTypes, $arSocNetAllowedSubscribeEntityTypes, $arSocNetFeaturesSettings, $arSocNetLogEvents;

		if ($ACTION == "ADD" && (!is_set($arFields, "SITE_ID") || StrLen($arFields["SITE_ID"]) <= 0))
			$arFields["SITE_ID"] = SITE_ID;

		if ($ACTION != "ADD" && IntVal($ID) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("System error 870164", "ERROR");
			return false;
		}

		$newEntityType = "";

		if ((is_set($arFields, "ENTITY_TYPE") || $ACTION=="ADD") && StrLen($arFields["ENTITY_TYPE"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_EMPTY_ENTITY_TYPE"), "EMPTY_ENTITY_TYPE");
			return false;
		}
		elseif (is_set($arFields, "ENTITY_TYPE"))
		{
			if (!in_array($arFields["ENTITY_TYPE"], $arSocNetAllowedSubscribeEntityTypes))
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_ERROR_NO_ENTITY_TYPE"), "ERROR_NO_ENTITY_TYPE");
				return false;
			}

			$newEntityType = $arFields["ENTITY_TYPE"];
		}

		if ((is_set($arFields, "ENTITY_ID") || $ACTION=="ADD") && IntVal($arFields["ENTITY_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_EMPTY_ENTITY_ID"), "EMPTY_ENTITY_ID");
			return false;
		}
		elseif (is_set($arFields, "ENTITY_ID"))
		{
			if (StrLen($newEntityType) <= 0 && $ID > 0)
			{
				$arRe = CAllSocNetLog::GetByID($ID);
				if ($arRe)
					$newEntityType = $arRe["ENTITY_TYPE"];
			}
			if (StrLen($newEntityType) <= 0)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_ERROR_CALC_ENTITY_TYPE"), "ERROR_CALC_ENTITY_TYPE");
				return false;
			}

			if ($newEntityType == SONET_ENTITY_GROUP)
			{
				$arResult = CSocNetGroup::GetByID($arFields["ENTITY_ID"]);
				if ($arResult == false)
				{
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_ERROR_NO_ENTITY_ID"), "ERROR_NO_ENTITY_ID");
					return false;
				}
			}
			elseif ($newEntityType == SONET_ENTITY_USER)
			{
				$dbResult = CUser::GetByID($arFields["ENTITY_ID"]);
				if (!$dbResult->Fetch())
				{
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_ERROR_NO_ENTITY_ID"), "ERROR_NO_ENTITY_ID");
					return false;
				}
			}
		}

		if ((is_set($arFields, "EVENT_ID") || $ACTION=="ADD") && StrLen($arFields["EVENT_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_EMPTY_EVENT_ID"), "EMPTY_EVENT_ID");
			return false;
		}
		elseif (is_set($arFields, "EVENT_ID"))
		{
			$arFields["EVENT_ID"] = strtolower($arFields["EVENT_ID"]);
			
			$bFound = false;
			
			if (
				array_key_exists($arFields["EVENT_ID"], $arSocNetLogEvents)
				&& array_key_exists("ENTITIES", $arSocNetLogEvents[$arFields["EVENT_ID"]])
				&& array_key_exists($arFields["ENTITY_TYPE"], $arSocNetLogEvents[$arFields["EVENT_ID"]]["ENTITIES"])
			)
				$bFound = true;

			if (!$bFound)
			{
				foreach($arSocNetFeaturesSettings as $feature => $arFeature)
				{
					if (
						array_key_exists("subscribe_events", $arFeature)
						&& array_key_exists($arFields["EVENT_ID"], $arFeature["subscribe_events"])
						&& array_key_exists("ENTITIES", $arFeature["subscribe_events"][$arFields["EVENT_ID"]])
						&& array_key_exists($arFields["ENTITY_TYPE"], $arFeature["subscribe_events"][$arFields["EVENT_ID"]]["ENTITIES"])
					)
					{
						$bFound = true;
						break;
					}
				}
			}
			
			if (!$bFound)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_ERROR_NO_FEATURE_ID"), "ERROR_NO_FEATURE");
				return false;
			}
		}

		if (is_set($arFields, "USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_ERROR_NO_USER_ID"), "ERROR_NO_USER_ID");
				return false;
			}
		}

		if (is_set($arFields, "LOG_DATE") && (!$DB->IsDate($arFields["LOG_DATE"], false, LANG, "FULL")))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_EMPTY_DATE_CREATE"), "EMPTY_LOG_DATE");
			return false;
		}

		if ((is_set($arFields, "TITLE") || $ACTION=="ADD") && StrLen($arFields["TITLE"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_EMPTY_TITLE"), "EMPTY_TITLE");
			return false;
		}

		return True;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_WRONG_PARAMETER_ID"), "ERROR_NO_ID");
			return false;
		}

		$bSuccess = True;

		if ($bSuccess)
			$bSuccess = $DB->Query("DELETE FROM b_sonet_log WHERE ID = ".$ID."", true);

		return $bSuccess;
	}

	function DeleteNoDemand($userID)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
			return false;

		$DB->Query("DELETE FROM b_sonet_log WHERE ENTITY_TYPE = 'U' AND ENTITY_ID = ".$userID."", true);

		return true;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_L_WRONG_PARAMETER_ID"), "ERROR_NO_ID");
			return false;
		}

		$dbResult = CSocNetLog::GetList(Array(), Array("ID" => $ID));
		if ($arResult = $dbResult->GetNext())
		{
			return $arResult;
		}

		return False;
	}


	function MakeTitle($titleTemplate, $title, $url = "", $bHtml = true)
	{
		if (StrLen($url) > 0)
			$title = ($bHtml ? "<a href=\"".$url."\">".$title."</a>" : $title." [".$url."]");

		if (StrLen($titleTemplate) > 0)
		{
			if (StrPos($titleTemplate, "#TITLE#") !== false)
				return Str_Replace("#TITLE#", $title, $titleTemplate);
			else
				return $titleTemplate." \"".$title."\"";
		}
		else
		{
			return $title;
		}
	}

	/***************************************/
	/**********  SEND EVENTS  **************/
	/***************************************/
	function __InitUserTmp($userID)
	{
		$title = "";

		$dbUser = CUser::GetByID($userID);
		if ($arUser = $dbUser->GetNext())
			$title .= CSocNetUser::FormatName($arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]);

		return $title;
	}

	function __InitUsersTmp($message, $titleTemplate1, $titleTemplate2)
	{
		$arUsersID = explode(",", $message);

		$title = "";

		$bFirst = true;
		$count = 0;
		foreach ($arUsersID as $userID)
		{
			$titleTmp = CSocNetLog::__InitUserTmp($userID);

			if (StrLen($titleTmp) > 0)
			{
				if (!$bFirst)
					$title .= ", ";
				$title .= $titleTmp;
				$count++;
			}

			$bFirst = false;
		}

		return Str_Replace("#TITLE#", $title, (($count > 1) ? $titleTemplate2 : $titleTemplate1));
	}

	function __InitGroupTmp($groupID)
	{
		$title = "";

		$arGroup = CSocNetGroup::GetByID($groupID);
		if ($arGroup)
			$title .= $arGroup["NAME"];

		return $title;
	}

	function __InitGroupsTmp($message, $titleTemplate1, $titleTemplate2)
	{
		$arGroupsID = explode(",", $message);

		$title = "";

		$bFirst = true;
		$count = 0;
		foreach ($arGroupsID as $groupID)
		{
			$titleTmp = CSocNetLog::__InitGroupTmp($groupID);

			if (StrLen($titleTmp) > 0)
			{
				if (!$bFirst)
					$title .= ", ";
				$title .= $titleTmp;
				$count++;
			}

			$bFirst = false;
		}

		return Str_Replace("#TITLE#", $title, (($count > 1) ? $titleTemplate2 : $titleTemplate1));
	}

	function SendEvent($ID, $mailTemplate = "SONET_NEW_EVENT", $tmp_id = false)
	{
		$ID = IntVal($ID);
		if ($ID <= 0)
			return false;

		if (intval($tmp_id) > 0)
			$arFilter = array("TMP_ID" => $tmp_id);
		else
			$arFilter = array("ID" => $ID);
		
		$dbLog = CSocNetLog::GetList(
			array(),
			$arFilter,
			false,
			false,
			array("ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "EVENT_ID", "LOG_DATE", "TITLE_TEMPLATE", "TITLE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID", "CALLBACK_FUNC", "SITE_ID", "PARAMS", "GROUP_NAME", "CREATED_BY_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_LOGIN"),
			array("MIN_ID_JOIN" => true)
		);
		$arLog = $dbLog->Fetch();
		if (!$arLog)
			return false;

		if (MakeTimeStamp($arLog["LOG_DATE"]) > time())
		{
			CAgent::AddAgent("CSocNetLog::SendEvent(".$ID.", '".$mailTemplate."', ".($tmp_id ? $tmp_id : 'false').");", "socialnetwork", "N", 0, $arLog["LOG_DATE"], "Y", $arLog["LOG_DATE"]);
			return false;
		}

		if (
			array_key_exists($arLog["EVENT_ID"], $GLOBALS["arSocNetLogEvents"])
			&& is_array($GLOBALS["arSocNetLogEvents"][$arLog["EVENT_ID"]])
			&& array_key_exists("CLASS_FORMAT", $GLOBALS["arSocNetLogEvents"][$arLog["EVENT_ID"]])
			&& array_key_exists("METHOD_FORMAT", $GLOBALS["arSocNetLogEvents"][$arLog["EVENT_ID"]])
		)
			$arLog["FIELDS_FORMATTED"] = call_user_func(array($GLOBALS["arSocNetLogEvents"][$arLog["EVENT_ID"]]["CLASS_FORMAT"], $GLOBALS["arSocNetLogEvents"][$arLog["EVENT_ID"]]["METHOD_FORMAT"]), $arLog, array(), true);


		if (!array_key_exists("FIELDS_FORMATTED", $arLog))
		{
			foreach ($GLOBALS["arSocNetFeaturesSettings"] as $featureID => $arFeature)
			{
				if (array_key_exists("subscribe_events", $arFeature) && is_array($arFeature["subscribe_events"]))
				{
					foreach($arFeature["subscribe_events"] as $event_id_tmp => $arEventTmp)
					{
						if ($event_id_tmp != $arLog["EVENT_ID"])
							continue;

						if (
							array_key_exists("CLASS_FORMAT", $arEventTmp)
							&& array_key_exists("METHOD_FORMAT", $arEventTmp)
						)
						{
							$arLog["FIELDS_FORMATTED"] = call_user_func(array($arEventTmp["CLASS_FORMAT"], $arEventTmp["METHOD_FORMAT"]), $arLog, array(), true);
							break;
						}
					}
				}
			}
		}		
			
		if (
			array_key_exists($arLog["ENTITY_TYPE"], $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"])
			&& array_key_exists("HAS_MY", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arLog["ENTITY_TYPE"]])
			&& $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arLog["ENTITY_TYPE"]]["HAS_MY"] == "Y"
			&& array_key_exists("CLASS_OF", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arLog["ENTITY_TYPE"]])
			&& array_key_exists("METHOD_OF", $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arLog["ENTITY_TYPE"]])
			&& strlen($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arLog["ENTITY_TYPE"]]["CLASS_OF"]) > 0
			&& strlen($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arLog["ENTITY_TYPE"]]["METHOD_OF"]) > 0
			&& method_exists($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arLog["ENTITY_TYPE"]]["CLASS_OF"], $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arLog["ENTITY_TYPE"]]["METHOD_OF"])
		)
			$arOfEntities = call_user_func(array($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arLog["ENTITY_TYPE"]]["CLASS_OF"], $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arLog["ENTITY_TYPE"]]["METHOD_OF"]), $arLog["ENTITY_ID"]);

		$arListParams = array(
							"USE_SUBSCRIBE" 	=> "Y",
							"ENTITY_TYPE" 		=> $arLog["ENTITY_TYPE"],
							"ENTITY_ID" 		=> $arLog["ENTITY_ID"],
							"EVENT_ID" 			=> $arLog["EVENT_ID"],
							"USER_ID" 			=> $arLog["USER_ID"],
							"OF_ENTITIES"		=> $arOfEntities,
							"TRANSPORT" 		=> array("M", "X")
						);

		$dbSubscribers = CSocNetLogEvents::GetList(
			array(
				"TRANSPORT"		=> "DESC"
			),
			array(
				"USER_ACTIVE"	=> "Y"
			),
			false,
			false,
			array("USER_ID", "SITE_ID", "USER_NAME", "USER_LAST_NAME", "USER_LOGIN", "USER_LID", "USER_EMAIL", "TRANSPORT"),
			$arListParams
		);		
		
		$arSentUserID = array("M" => array(), "X" => array());
		while ($arSubscriber = $dbSubscribers->Fetch())
		{
			if (
				array_key_exists($arSubscriber["TRANSPORT"], $arSentUserID)
				&& in_array($arSubscriber["USER_ID"], $arSentUserID[$arSubscriber["TRANSPORT"]])
			)
				continue;

			$arSentUserID[$arSubscriber["TRANSPORT"]][] = $arSubscriber["USER_ID"];
	
			$bHasAccess = false;	
			if (CSocNetUser::IsUserModuleAdmin($arSubscriber["USER_ID"]))
				$bHasAccess = true;
			elseif (CSocNetEventUserView::CheckPermissionsByEvent(
									$arLog["ENTITY_TYPE"], 
									$arLog["ENTITY_ID"],
									$arLog["EVENT_ID"],
									$arSubscriber["USER_ID"]
								)
			)
				$bHasAccess = true;			

			if (!$bHasAccess)
				continue;

			switch ($arSubscriber["TRANSPORT"])
			{
				case "X":

					if (
						array_key_exists("URL", $arLog["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
						&& strlen($arLog["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL"]) > 0
					)
						$link = GetMessage("SONET_GL_SEND_EVENT_LINK").$arLog["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL"];
					else
						$link = "";

					$arMessageFields = array(
						"FROM_USER_ID" 	=> (intval($arLog["USER_ID"]) > 0 ? $arLog["USER_ID"] : 1),
						"TO_USER_ID" 	=> $arSubscriber["USER_ID"],
						"MESSAGE" 		=> $arLog["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["TITLE"]."#BR#".$arLog["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"].(strlen($link) > 0 ? "#BR#".$link : ""),
						"=DATE_CREATE" 	=> $GLOBALS["DB"]->CurrentTimeFunction(),
						"MESSAGE_TYPE" 	=> SONET_MESSAGE_SYSTEM,
						"IS_LOG" 		=> "Y"
					);
					CSocNetMessages::Add($arMessageFields);
					break;				
				case "M":
					$arFields["SUBSCRIBER_ID"] = $arSubscriber["USER_ID"];
					$arFields["SUBSCRIBER_NAME"] = $arSubscriber["USER_NAME"];
					$arFields["SUBSCRIBER_LAST_NAME"] = $arSubscriber["USER_LAST_NAME"];
					$arFields["SUBSCRIBER_LOGIN"] = $arSubscriber["USER_LOGIN"];
					$arFields["SUBSCRIBER_EMAIL"] = $arSubscriber["USER_EMAIL"];
					$arFields["EMAIL_TO"] = $arSubscriber["USER_EMAIL"];
					$arFields["TITLE"] = str_replace("#BR#", "\n", $arLog["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["TITLE"]);
					$arFields["MESSAGE"] = str_replace("#BR#", "\n", $arLog["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"]);
					$arFields["ENTITY"] = $arLog["FIELDS_FORMATTED"]["ENTITY"]["FORMATTED"];
					$arFields["ENTITY_TYPE"] = $arLog["FIELDS_FORMATTED"]["ENTITY"]["TYPE_MAIL"];
					
					if (
						array_key_exists("URL", $arLog["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
						&& strlen($arLog["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL"]) > 0
					)
						$arFields["URL"] = $arLog["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["URL"];
					else
						$arFields["URL"] = $arLog["URL"];

					$siteID = (strlen($arLog["SITE_ID"]) > 0 ? $arLog["SITE_ID"] : (defined("SITE_ID") ? SITE_ID : $arSubscriber["SITE_ID"]));

					if (StrLen($siteID) <= 0)
						$siteID = $arSubscriber["USER_LID"];
					if (StrLen($siteID) <= 0)
						continue;

					$event = new CEvent;
					$event->Send($mailTemplate, $siteID, $arFields, "N");				
					break;
				default:
			}
		}

		return true;
	}

	function ClearOldAgent()
	{
		CSocNetLog::ClearOld(IntVal(COption::GetOptionString("socialnetwork", "log_cleanup_days", "7")));
		return "CSocNetLog::ClearOldAgent();";
	}

	function GetSign($url, $userID = false, $site_id = false)
	{
		if (!$url || strlen(trim($url)) <= 0)
			return false;
			
		if (!$userID)
			$userID = $GLOBALS["USER"]->GetID();

		if ($hash = CUser::GetHitAuthHash($url, $userID))
			return $hash;
		else
		{
			$hash = CUser::AddHitAuthHash($url, $userID, $site_id);
			return $hash;
		}
	}

	function CheckSign($sign, $userId)
	{
		return (md5($userId."||".CSocNetLog::GetUniqLogID()) == $sign);
	}

	function OnSocNetLogFormatEvent($arEvent, $arParams)
	{
		if ($arEvent["EVENT_ID"] == "system" || $arEvent["EVENT_ID"] == "system_friends" || $arEvent["EVENT_ID"] == "system_groups")
		{
			$arEvent["TITLE_TEMPLATE"] = "";
			$arEvent["URL"] = "";

			switch ($arEvent["TITLE"])
			{
				case "join":
					list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_JOIN1"), GetMessage("SONET_L_TITLE_JOIN2"), $arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "unjoin":
					list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_UNJOIN1"), GetMessage("SONET_L_TITLE_UNJOIN2"), $arParams);
					$arEvents["TITLE"] = $titleTmp;
					$arEvents["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "moderate":
					list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_MODERATE1"), GetMessage("SONET_L_TITLE_MODERATE2"), $arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "unmoderate":
					list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_UNMODERATE1"), GetMessage("SONET_L_TITLE_UNMODERATE2"), $arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "owner":
					list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_OWNER1"), GetMessage("SONET_L_TITLE_OWNER1"), $arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "friend":
					list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_FRIEND1"), GetMessage("SONET_L_TITLE_FRIEND1"), $arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "unfriend":
					list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_UNFRIEND1"), GetMessage("SONET_L_TITLE_UNFRIEND1"), $arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "group":
					list($titleTmp, $messageTmp) = CSocNetLog::InitGroupsTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_GROUP1"), GetMessage("SONET_L_TITLE_GROUP1"), $arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "ungroup":
					list($titleTmp, $messageTmp) = CSocNetLog::InitGroupsTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_UNGROUP1"), GetMessage("SONET_L_TITLE_UNGROUP1"), $arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "exclude_user":
					list($titleTmp, $messageTmp) = CSocNetLog::InitGroupsTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_EXCLUDE_USER1"), GetMessage("SONET_L_TITLE_EXCLUDE_USER1"), $arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				case "exclude_group":
					list($titleTmp, $messageTmp) = CSocNetLog::InitUsersTmp($arEvent["MESSAGE"], GetMessage("SONET_L_TITLE_EXCLUDE_GROUP1"), GetMessage("SONET_L_TITLE_EXCLUDE_GROUP1"), $arParams);
					$arEvent["TITLE"] = $titleTmp;
					$arEvent["MESSAGE_FORMAT"] = $messageTmp;
					break;
				default:
					continue;
					break;
			}
		}
		return $arEvent;
	}

	function InitUserTmp($userID, $arParams, $bCurrentUserIsAdmin = "unknown", $bRSS = false)
	{
		$title = "";
		$message = "";
		$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

		$dbUser = CUser::GetByID($userID);
		if ($arUser = $dbUser->Fetch())
		{
			if ($bCurrentUserIsAdmin == "unknown")
				$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

			$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUser["ID"], "viewprofile", $bCurrentUserIsAdmin);
			$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"]));

			if (!$bRSS && $canViewProfile)
				$title .= "<a href=\"".$pu."\">";
			$title .= CUser::FormatName($arParams['NAME_TEMPLATE'], $arUser, $bUseLogin);
			if (!$bRSS && $canViewProfile)
				$title .= "</a>";
				
			if (intval($arUser["PERSONAL_PHOTO"]) <= 0)
			{
				switch ($arUser["PERSONAL_GENDER"])
				{
					case "M":
						$suffix = "male";
						break;
					case "F":
						$suffix = "female";
							break;
					default:
						$suffix = "unknown";
				}
				$arUser["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
			}
			$arImage = CSocNetTools::InitImage($arUser["PERSONAL_PHOTO"], 100, "/bitrix/images/socialnetwork/nopic_user_100.gif", 100, $pu, $canViewProfile);

			$message = $arImage["IMG"];
		}

		return array($title, $message);
	}

	function InitUsersTmp($message, $titleTemplate1, $titleTemplate2, $arParams, $bCurrentUserIsAdmin = "unknown", $bRSS = false)
	{
		$arUsersID = explode(",", $message);

		$message = "";
		$title = "";

		$bFirst = true;
		$count = 0;
		
		if ($bCurrentUserIsAdmin == "unknown")
			$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
			
		foreach ($arUsersID as $userID)
		{
			list($titleTmp, $messageTmp) = CSocNetLog::InitUserTmp($userID, $arParams, $bCurrentUserIsAdmin, $bRSS);
	
			if (StrLen($titleTmp) > 0)
			{
				if (!$bFirst)
					$title .= ", ";
				$title .= $titleTmp;
				$count++;
			}

			if (StrLen($messageTmp) > 0)
			{
				if (!$bFirst)
					$message .= " ";
				$message .= $messageTmp;
			}

			$bFirst = false;
		}
		return array(Str_Replace("#TITLE#", $title, (($count > 1) ? $titleTemplate2 : $titleTemplate1)), $message);
	}

	function InitGroupTmp($groupID, $arParams, $bRSS = false)
	{
		$title = "";
		$message = "";

		$arGroup = CSocNetGroup::GetByID($groupID);
		if ($arGroup)
		{
			$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroup["ID"]));

			if (!$bRSS)
				$title .= "<a href=\"".$pu."\">";
			$title .= $arGroup["NAME"];
			if (!$bRSS)
				$title .= "</a>";

			if (intval($arGroup["IMAGE_ID"]) <= 0)
				$arGroup["IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);

			$arImage = CSocNetTools::InitImage($arGroup["IMAGE_ID"], 100, "/bitrix/images/socialnetwork/nopic_group_100.gif", 100, $pu, true);

			$message = $arImage["IMG"];
		}

		return array($title, $message);
	}

	function InitGroupsTmp($message, $titleTemplate1, $titleTemplate2, $arParams, $bRSS = false)
	{
		$arGroupsID = explode(",", $message);

		$message = "";
		$title = "";

		$bFirst = true;
		$count = 0;
		foreach ($arGroupsID as $groupID)
		{
			list($titleTmp, $messageTmp) = CSocNetLog::InitGroupTmp($groupID, $arParams, $bRSS);

			if (StrLen($titleTmp) > 0)
			{
				if (!$bFirst)
					$title .= ", ";
				$title .= $titleTmp;
				$count++;
			}

			if (StrLen($messageTmp) > 0)
			{
				if (!$bFirst)
					$message .= " ";
				$message .= $messageTmp;
			}

			$bFirst = false;
		}

		return array(Str_Replace("#TITLE#", $title, (($count > 1) ? $titleTemplate2 : $titleTemplate1)), $message);
	}

	function ShowGroup($arEntityDesc, $strEntityURL, $arParams)
	{
		$name = "<a href=\"".$strEntityURL."\">".$arEntityDesc["NAME"]."</a>";
		return $name;
	}
	
	function ShowUser($arEntityDesc, $strEntityURL, $arParams)
	{
		$name = $GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
						'',
						array(
							"ID" => $arEntityDesc["ID"],
							"HTML_ID" => "subscribe_list_".$arEntityDesc["ID"],
							"NAME" => $arEntityDesc["~NAME"],
							"LAST_NAME" => $arEntityDesc["~LAST_NAME"],
							"SECOND_NAME" => $arEntityDesc["~SECOND_NAME"],
							"LOGIN" => $arEntityDesc["~LOGIN"],
							"USE_THUMBNAIL_LIST" => "N",
							"PROFILE_URL" => $strEntityURL,
							"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
							"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
							"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
							"SHOW_FIELDS" => $arParams["SHOW_FIELDS_TOOLTIP"],
							"USER_PROPERTY" => $arParams["USER_PROPERTY_TOOLTIP"],
							"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
							"SHOW_YEAR" => $arParams["SHOW_YEAR"],
							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
							"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
							"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
							"DO_RETURN"	=> "Y",
							"INLINE" => "Y",
						),
						false,
						array("HIDE_ICONS" => "Y")
					);

		return $name;
	}

	function FormatEvent_FillTooltip($arFields, $arParams)
	{
		return array(
						"ID" 							=> $arFields["ID"],
						"NAME" 							=> $arFields["NAME"],
						"LAST_NAME" 					=> $arFields["LAST_NAME"],
						"SECOND_NAME" 					=> $arFields["SECOND_NAME"],
						"LOGIN" 						=> $arFields["LOGIN"],
						"USE_THUMBNAIL_LIST" 			=> "N",
						"PATH_TO_SONET_MESSAGES_CHAT" 	=> $arParams["PATH_TO_MESSAGES_CHAT"],
						"PATH_TO_SONET_USER_PROFILE" 	=> $arParams["PATH_TO_USER"],
						"PATH_TO_VIDEO_CALL" 			=> $arParams["PATH_TO_VIDEO_CALL"],
						"DATE_TIME_FORMAT" 				=> $arParams["DATE_TIME_FORMAT"],
						"SHOW_YEAR" 					=> $arParams["SHOW_YEAR"],
						"CACHE_TYPE" 					=> $arParams["CACHE_TYPE"],
						"CACHE_TIME" 					=> $arParams["CACHE_TIME"],
						"NAME_TEMPLATE" 				=> $arParams["NAME_TEMPLATE"],
						"SHOW_LOGIN" 					=> $arParams["SHOW_LOGIN"],
						"PATH_TO_CONPANY_DEPARTMENT" 	=> $arParams["PATH_TO_CONPANY_DEPARTMENT"],
						"INLINE" 						=> "Y"
					);
	}
	
	function FormatEvent_BlogPostComment($arFields, $arParams, $bMail = false)
	{
		$arResult = array(
				"EVENT"				=> $arFields,
				"CREATED_BY"		=> array(),
				"ENTITY"			=> array(),
				"EVENT_FORMATTED"	=> array(),
			);
			
		if (intval($arFields["USER_ID"]) > 0)
		{
			if ($bMail)
				$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("SONET_GL_EVENT_USER")." ".$arFields["CREATED_BY_NAME"]." ".$arFields["CREATED_BY_LAST_NAME"];
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["USER_ID"],
					"NAME" 			=> $arFields["~CREATED_BY_NAME"],
					"LAST_NAME" 	=> $arFields["~CREATED_BY_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~CREATED_BY_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~CREATED_BY_LOGIN"],
				);
				$arResult["CREATED_BY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
			}
		}
		elseif ($bMail)
			$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("SONET_GL_EVENT_ANONYMOUS_USER");

		$arResult["CREATED_BY"]["ACTION_TYPE"] = "wrote";
		
		if (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["USER_NAME"]." ".$arFields["USER_LAST_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_U");
			}
			else
			{		
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["ENTITY_ID"],
					"NAME" 			=> $arFields["~USER_NAME"],
					"LAST_NAME" 	=> $arFields["~USER_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~USER_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~USER_LOGIN"],
				);
				$arResult["ENTITY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
				$arResult["ENTITY"]["FORMATTED"] = "";
			}
		}
		elseif (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["GROUP_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_G");
			}
			else
			{		
				$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arFields["ENTITY_ID"]));		
				$arResult["ENTITY"]["FORMATTED"]["TYPE_NAME"] = $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arFields["ENTITY_TYPE"]]["TITLE_ENTITY"];
				$arResult["ENTITY"]["FORMATTED"]["URL"] = $url;				
				$arResult["ENTITY"]["FORMATTED"]["NAME"] = $arFields["GROUP_NAME"];
			}
		}

		if ($bMail)
		{
			if ($arFields["EVENT_ID"] == "blog_post")
				$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_".($arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_BLOG_POST_MAIL");
			elseif ($arFields["EVENT_ID"] == "blog_comment")
				$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_".($arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_BLOG_COMMENT_MAIL");
		}
		else
		{
			if ($arFields["EVENT_ID"] == "blog_post")
				$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_BLOG_POST");
			elseif ($arFields["EVENT_ID"] == "blog_comment")
				$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_BLOG_COMMENT");
		}
		
		if (
			!$bMail
			&& array_key_exists("URL", $arFields)
			&& strlen($arFields["URL"]) > 0
		)
			$post_tmp = '<a href="'.$arFields["URL"].'">'.$arFields["TITLE"].'</a>';
		else
			$post_tmp = $arFields["TITLE"];

		$title = str_replace(
						array("#TITLE#", "#ENTITY#", "#CREATED_BY#"),
						array($post_tmp, $arResult["ENTITY"]["FORMATTED"], ($bMail ? $arResult["CREATED_BY"]["FORMATTED"] : "")),
						$title_tmp
					);

		$arResult["EVENT_FORMATTED"] = array(
				"TITLE"		=> $title,
				"MESSAGE"	=> ($bMail ? $arFields["TEXT_MESSAGE"] : $arFields["MESSAGE"])
			);

		$url = false;
		
		if (
			$bMail 
			&& strlen($arFields["URL"]) > 0 
			&& strlen($arFields["SITE_ID"]) > 0
		)
		{
			$rsSites = CSite::GetByID($arFields["SITE_ID"]);
			$arSite = $rsSites->Fetch();

			if (strlen($arSite["SERVER_NAME"]) > 0)
				$server_name = $arSite["SERVER_NAME"];
			else
				$server_name = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);

			$protocol = (CMain::IsHTTPS() ? "https" : "http");
			$url = $protocol."://".$server_name.$arFields["URL"];
		}

		if (strlen($url) > 0)
			$arResult["EVENT_FORMATTED"]["URL"] = $url;

		return $arResult;
	}

	function FormatEvent_Forum($arFields, $arParams, $bMail = false)
	{
		$arResult = array(
				"EVENT"				=> $arFields,
				"CREATED_BY"		=> array(),
				"ENTITY"			=> array(),
				"EVENT_FORMATTED"	=> array(),
			);
			
		if (intval($arFields["USER_ID"]) > 0)
		{
			if ($bMail)
				$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("SONET_GL_EVENT_USER")." ".$arFields["CREATED_BY_NAME"]." ".$arFields["CREATED_BY_LAST_NAME"];
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["USER_ID"],
					"NAME" 			=> $arFields["~CREATED_BY_NAME"],
					"LAST_NAME" 	=> $arFields["~CREATED_BY_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~CREATED_BY_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~CREATED_BY_LOGIN"],
				);
				$arResult["CREATED_BY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
			}
		}
		elseif ($bMail)
			$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("SONET_GL_EVENT_ANONYMOUS_USER");

		if (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["USER_NAME"]." ".$arFields["USER_LAST_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_U");
			}
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["ENTITY_ID"],
					"NAME" 			=> $arFields["~USER_NAME"],
					"LAST_NAME" 	=> $arFields["~USER_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~USER_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~USER_LOGIN"],
				);
				$arResult["ENTITY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
				$arResult["ENTITY"]["FORMATTED"] = "";
			}
		}
		elseif (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["GROUP_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_G");
			}
			else
			{
				$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arFields["ENTITY_ID"]));		
				$arResult["ENTITY"]["FORMATTED"]["TYPE_NAME"] = $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arFields["ENTITY_TYPE"]]["TITLE_ENTITY"];
				$arResult["ENTITY"]["FORMATTED"]["URL"] = $url;				
				$arResult["ENTITY"]["FORMATTED"]["NAME"] = $arFields["GROUP_NAME"];
			}
		}

		$type = false;
		if (strlen($arFields["PARAMS"]) > 0)
		{
			$arFieldsParams = explode("&", $arFields["PARAMS"]);
			if (is_array($arFieldsParams) && count($arFieldsParams) > 0)
			{
				foreach ($arFieldsParams as $tmp)
				{
					list($key, $value) = explode("=", $tmp);
					if ($key == "type")
					{
						$type = $value;
						break;
					}
				}
			}
		}

		if ($bMail)
		{
			if ($type == "T")
				$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_".($arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_FORUM_TOPIC_MAIL");
			else
				$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_".($arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_FORUM_MESSAGE_MAIL");
		}
		else
		{
			if ($type == "T")
				$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_FORUM_TOPIC");
			else
				$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_FORUM_MESSAGE");			
		}

		if (
			!$bMail
			&& array_key_exists("URL", $arFields)
			&& strlen($arFields["URL"]) > 0
		)
			$topic_tmp = '<a href="'.$arFields["URL"].'">'.$arFields["TITLE"].'</a>';
		else
			$topic_tmp = $arFields["TITLE"];

		$title = str_replace(
						array("#TITLE#", "#ENTITY#", "#CREATED_BY#"),
						array($topic_tmp, $arResult["ENTITY"]["FORMATTED"], ($bMail ? $arResult["CREATED_BY"]["FORMATTED"] : "")),
						$title_tmp
					);
					
		$arResult["EVENT_FORMATTED"] = array(
				"TITLE"		=> $title,
				"MESSAGE"	=> ($bMail ? $arFields["TEXT_MESSAGE"] : $arFields["MESSAGE"])
			);

		$url = false;
		
		if (
			$bMail 
			&& strlen($arFields["URL"]) > 0 
			&& strlen($arFields["SITE_ID"]) > 0
		)
		{
			$rsSites = CSite::GetByID($arFields["SITE_ID"]);
			$arSite = $rsSites->Fetch();

			if (strlen($arSite["SERVER_NAME"]) > 0)
				$server_name = $arSite["SERVER_NAME"];
			else
				$server_name = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);

			$protocol = (CMain::IsHTTPS() ? "https" : "http");
			$url = $protocol."://".$server_name.$arFields["URL"];
		}

		if (strlen($url) > 0)
			$arResult["EVENT_FORMATTED"]["URL"] = $url;

		return $arResult;
	}
	
	function FormatEvent_Photo($arFields, $arParams, $bMail = false)
	{
		$arResult = array(
				"EVENT"				=> $arFields,
				"CREATED_BY"		=> array(),
				"ENTITY"			=> array(),
				"EVENT_FORMATTED"	=> array(),
			);
			
		if (intval($arFields["USER_ID"]) > 0)
		{
			if ($bMail)
				$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("SONET_GL_EVENT_USER")." ".$arFields["CREATED_BY_NAME"]." ".$arFields["CREATED_BY_LAST_NAME"];
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["USER_ID"],
					"NAME" 			=> $arFields["~CREATED_BY_NAME"],
					"LAST_NAME" 	=> $arFields["~CREATED_BY_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~CREATED_BY_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~CREATED_BY_LOGIN"],
				);
				$arResult["CREATED_BY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
			}
		}
		elseif ($bMail)
			$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("SONET_GL_EVENT_ANONYMOUS_USER");

		if (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["USER_NAME"]." ".$arFields["USER_LAST_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_U");
			}
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["ENTITY_ID"],
					"NAME" 			=> $arFields["~USER_NAME"],
					"LAST_NAME" 	=> $arFields["~USER_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~USER_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~USER_LOGIN"],
				);
				$arResult["ENTITY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
				$arResult["ENTITY"]["FORMATTED"] = "";
			}
		}
		elseif (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["GROUP_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_G");
			}
			else
			{
				$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arFields["ENTITY_ID"]));
				$arResult["ENTITY"]["FORMATTED"]["TYPE_NAME"] = $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arFields["ENTITY_TYPE"]]["TITLE_ENTITY"];
				$arResult["ENTITY"]["FORMATTED"]["URL"] = $url;				
				$arResult["ENTITY"]["FORMATTED"]["NAME"] = $arFields["GROUP_NAME"];
			}
		}

		
		$count = false;
		if (strlen($arFields["PARAMS"]) > 0)
		{
			$arFieldsParams = explode("&", $arFields["PARAMS"]);
			if (is_array($arFieldsParams) && count($arFieldsParams) > 0)
			{
				foreach ($arFieldsParams as $tmp)
				{
					list($key, $value) = explode("=", $tmp);
					if ($key == "count")
					{
						$count = $value;
						break;
					}
				}
			}
		}

		if (!$count)
			$count_tmp = "";
		else
			$count_tmp = " (".intval($count).")";

		if (
			!$bMail
			&& array_key_exists("URL", $arFields)
			&& strlen($arFields["URL"]) > 0
		)
			$album_tmp = '<a href="'.$arFields["URL"].'">'.GetMessage("SONET_GL_EVENT_TITLE_PHOTO_ALBUM").'</a>';
		else
			$album_tmp = GetMessage("SONET_GL_EVENT_TITLE_PHOTO_ALBUM");

		if ($bMail)
			$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_".($arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_PHOTO_MAIL");
		else
			$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_PHOTO");
		
		$title = str_replace(
						array("#ALBUM#", "#COUNT#", "#ENTITY#", "#CREATED_BY#"),
						array($album_tmp, $count_tmp, $arResult["ENTITY"]["FORMATTED"], ($bMail ? $arResult["CREATED_BY"]["FORMATTED"] : "")),
						$title_tmp
					);
					
		$arResult["EVENT_FORMATTED"] = array(
				"TITLE"		=> $title,
				"MESSAGE"	=> ($bMail ? $arFields["TEXT_MESSAGE"] : $arFields["MESSAGE"])
			);

		$url = false;
		
		if (
			$bMail 
			&& strlen($arFields["URL"]) > 0 
			&& strlen($arFields["SITE_ID"]) > 0
		)
		{
			$rsSites = CSite::GetByID($arFields["SITE_ID"]);
			$arSite = $rsSites->Fetch();

			if (strlen($arSite["SERVER_NAME"]) > 0)
				$server_name = $arSite["SERVER_NAME"];
			else
				$server_name = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);

			$protocol = (CMain::IsHTTPS() ? "https" : "http");
			$url = $protocol."://".$server_name.$arFields["URL"];
		}

		if (strlen($url) > 0)
			$arResult["EVENT_FORMATTED"]["URL"] = $url;

		return $arResult;
	}
	
	function FormatEvent_Files($arFields, $arParams, $bMail = false)
	{
		$arResult = array(
				"EVENT"				=> $arFields,
				"CREATED_BY"		=> array(),
				"ENTITY"			=> array(),
				"EVENT_FORMATTED"	=> array(),
			);
			
		if (intval($arFields["USER_ID"]) > 0)
		{
			if ($bMail)
				$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("SONET_GL_EVENT_USER")." ".$arFields["CREATED_BY_NAME"]." ".$arFields["CREATED_BY_LAST_NAME"];
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["USER_ID"],
					"NAME" 			=> $arFields["~CREATED_BY_NAME"],
					"LAST_NAME" 	=> $arFields["~CREATED_BY_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~CREATED_BY_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~CREATED_BY_LOGIN"],
				);
				$arResult["CREATED_BY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
			}
		}
		elseif ($bMail)
			$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("SONET_GL_EVENT_ANONYMOUS_USER");

		if (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["USER_NAME"]." ".$arFields["USER_LAST_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_U");
			}
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["ENTITY_ID"],
					"NAME" 			=> $arFields["~USER_NAME"],
					"LAST_NAME" 	=> $arFields["~USER_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~USER_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~USER_LOGIN"],
				);
				$arResult["ENTITY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
				$arResult["ENTITY"]["FORMATTED"] = "";
			}
		}
		elseif (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["GROUP_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_G");
			}
			else
			{
				$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arFields["ENTITY_ID"]));		
				$arResult["ENTITY"]["FORMATTED"]["TYPE_NAME"] = $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arFields["ENTITY_TYPE"]]["TITLE_ENTITY"];
				$arResult["ENTITY"]["FORMATTED"]["URL"] = $url;				
				$arResult["ENTITY"]["FORMATTED"]["NAME"] = $arFields["GROUP_NAME"];
			}
		}

		if (
			!$bMail
			&& array_key_exists("URL", $arFields)
			&& strlen($arFields["URL"]) > 0
		)
			$file_tmp = '<a href="'.$arFields["URL"].'">'.$arFields["TITLE"].'</a>';
		else
			$file_tmp = $arFields["TITLE"];

		if ($bMail)
			$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_".($arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_FILE_MAIL");
		else
			$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_FILE");

		$title = str_replace(
						array("#TITLE#", "#ENTITY#", "#CREATED_BY#"),
						array($file_tmp, $arResult["ENTITY"]["FORMATTED"], ($bMail ? $arResult["CREATED_BY"]["FORMATTED"] : "")),
						$title_tmp
					);

		$arResult["EVENT_FORMATTED"] = array(
				"TITLE"		=> $title,
				"MESSAGE"	=> ($bMail ? $arFields["TEXT_MESSAGE"] : $arFields["MESSAGE"])
			);

		return $arResult;
	}		
	
	function FormatEvent_Task($arFields, $arParams, $bMail = false)
	{
		$arResult = array(
				"EVENT"				=> $arFields,
				"CREATED_BY"		=> array(),
				"ENTITY"			=> array(),
				"EVENT_FORMATTED"	=> array(),
			);
			
		if (intval($arFields["USER_ID"]) > 0)
		{
			if ($bMail)
				$arResult["CREATED_BY"]["FORMATTED"] = $arFields["CREATED_BY_NAME"]." ".$arFields["CREATED_BY_LAST_NAME"];
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["USER_ID"],
					"NAME" 			=> $arFields["~CREATED_BY_NAME"],
					"LAST_NAME" 	=> $arFields["~CREATED_BY_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~CREATED_BY_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~CREATED_BY_LOGIN"],
				);
				$arResult["CREATED_BY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
			}
		}
		elseif ($bMail)
			$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("SONET_GL_EVENT_ANONYMOUS_USER");

		if (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["USER_NAME"]." ".$arFields["USER_LAST_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_U");
			}
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["ENTITY_ID"],
					"NAME" 			=> $arFields["~USER_NAME"],
					"LAST_NAME" 	=> $arFields["~USER_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~USER_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~USER_LOGIN"],
				);
				$arResult["ENTITY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
				$arResult["ENTITY"]["FORMATTED"] = "";
			}
		}
		elseif (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["GROUP_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_G");
			}
			else
			{
				$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arFields["ENTITY_ID"]));		
				$arResult["ENTITY"]["FORMATTED"]["TYPE_NAME"] = $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arFields["ENTITY_TYPE"]]["TITLE_ENTITY"];
				$arResult["ENTITY"]["FORMATTED"]["URL"] = $url;				
				$arResult["ENTITY"]["FORMATTED"]["NAME"] = $arFields["GROUP_NAME"];
			}
		}

		if (
			!$bMail
			&& array_key_exists("URL", $arFields)
			&& strlen($arFields["URL"]) > 0
		)
			$task_tmp = '<a href="'.$arFields["URL"].'">'.$arFields["TITLE"].'</a>';
		else
			$task_tmp = $arFields["TITLE"];
			
		$title_tmp = str_replace(
						"#TITLE#", 
						$task_tmp, 
						$arFields["TITLE_TEMPLATE"]
					);
		
		if ($bMail) 		
			$title = str_replace(
						array("#TASK#", "#ENTITY#", "#CREATED_BY#"),
						array($title_tmp, $arResult["ENTITY"]["FORMATTED"], ($bMail ? $arResult["CREATED_BY"]["FORMATTED"] : "")),
						GetMessage("SONET_GL_EVENT_TITLE_".($arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_TASK_MAIL")
					);
		else
			$title = $title_tmp;
			
		$arResult["EVENT_FORMATTED"] = array(
				"TITLE"		=> $title,
				"MESSAGE"	=> ($bMail ? str_replace(array("<nobr>", "</nobr>"), array("", ""), $arFields["TEXT_MESSAGE"]) : $arFields["MESSAGE"])
			);

		$url = false;
		
		if (
			$bMail 
			&& strlen($arFields["URL"]) > 0 
			&& strlen($arFields["SITE_ID"]) > 0
		)
		{
			$rsSites = CSite::GetByID($arFields["SITE_ID"]);
			$arSite = $rsSites->Fetch();

			if (strlen($arSite["SERVER_NAME"]) > 0)
				$server_name = $arSite["SERVER_NAME"];
			else
				$server_name = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);

			$protocol = (CMain::IsHTTPS() ? "https" : "http");
			$url = $protocol."://".$server_name.$arFields["URL"];
		}

		if (strlen($url) > 0)
			$arResult["EVENT_FORMATTED"]["URL"] = $url;

		return $arResult;
	}
	
	function FormatEvent_SystemGroups($arFields, $arParams, $bMail = false)
	{
		$arResult = array(
				"EVENT"				=> $arFields,
				"CREATED_BY"		=> array(),
				"ENTITY"			=> array(),
				"EVENT_FORMATTED"	=> array(),
			);

		if (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["USER_NAME"]." ".$arFields["USER_LAST_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_U");
			}
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["ENTITY_ID"],
					"NAME" 			=> $arFields["~USER_NAME"],
					"LAST_NAME" 	=> $arFields["~USER_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~USER_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~USER_LOGIN"],
				);
				$arResult["ENTITY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
				$arResult["ENTITY"]["FORMATTED"] = "";
			}
		}

		if (intval($arFields["MESSAGE"]) > 0)
		{
			$rsGroup = CSocNetGroup::GetList(
								array("ID" => "DESC"),
								array(
									"ID"		=> $arFields["MESSAGE"],
									"ACTIVE"	=> "Y"
								)
							);
			if ($arGroup = $rsGroup->GetNext())
			{
				if ($bMail)
					$group_tmp = $arGroup["NAME"];
				else
				{
					$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arFields["MESSAGE"]));
					$group_tmp = '<a href="'.$url.'">'.$arGroup["NAME"].'</a>';
				}

				if ($bMail)
					$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_SYSTEM_GROUPS_".strtoupper($arFields["TITLE"])."_MAIL");
				else
					$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_SYSTEM_GROUPS_".strtoupper($arFields["TITLE"]));
			
				$title = str_replace(
								array("#GROUP_NAME#", "#ENTITY#"),
								array($group_tmp, $arResult["ENTITY"]["FORMATTED"]),
								$title_tmp
							);

				$arResult["EVENT_FORMATTED"] = array(
						"TITLE"		=> $title,
						"MESSAGE"	=> false
					);
			}
		}

		return $arResult;
	}

	function FormatEvent_SystemFriends($arFields, $arParams, $bMail = false)
	{
		$arResult = array(
				"EVENT"				=> $arFields,
				"CREATED_BY"		=> array(),
				"ENTITY"			=> array(),
				"EVENT_FORMATTED"	=> array(),
			);

		if (intval($arFields["MESSAGE"]) > 0)
		{
		
			if (
				$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_USER
				&& intval($arFields["ENTITY_ID"]) > 0
			)
			{
				if ($bMail)
				{
					$arResult["ENTITY"]["FORMATTED"] = $arFields["USER_NAME"]." ".$arFields["USER_LAST_NAME"];
					$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_U");
				}
				else
				{
					$arFieldsTooltip = array(
						"ID" 			=> $arFields["ENTITY_ID"],
						"NAME" 			=> $arFields["~USER_NAME"],
						"LAST_NAME" 	=> $arFields["~USER_LAST_NAME"],
						"SECOND_NAME" 	=> $arFields["~USER_SECOND_NAME"],
						"LOGIN" 		=> $arFields["~USER_LOGIN"],
					);
					$arResult["ENTITY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
					$arResult["ENTITY"]["FORMATTED"] = "";
				}
			}

			if ($bMail)
			{
				$dbUser = CUser::GetList(
							($by="id"), 
							($order="asc"), 
							array(
								"ID" => $arFields["MESSAGE"]
							)
						);
				if($arUser = $dbUser->Fetch())
					$user_tmp .= $arUser["NAME"]." ".$arUser["LAST_NAME"];
			}
			else
				$user_tmp = $GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
							'',
							array(
								"ID" => $arFields["MESSAGE"],
								"USE_THUMBNAIL_LIST" => "N",
								"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
								"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
								"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
								"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
								"SHOW_YEAR" => $arParams["SHOW_YEAR"],
								"CACHE_TYPE" => $arParams["CACHE_TYPE"],
								"CACHE_TIME" => $arParams["CACHE_TIME"],
								"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
								"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
								"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
								"INLINE" => "Y",
								"DO_RETURN" => "Y",
							),
							false,
							array("HIDE_ICONS" => "Y")
						);		

			if ($bMail)
				$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_SYSTEM_FRIENDS_".strtoupper($arFields["TITLE"])."_MAIL");
			else
				$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_SYSTEM_FRIENDS_".strtoupper($arFields["TITLE"]));
					
			$title = str_replace(
							array("#USER_NAME#", "#ENTITY#"),
							array($user_tmp, $arResult["ENTITY"]["FORMATTED"]),
							$title_tmp
						);

			$arResult["EVENT_FORMATTED"] = array(
					"TITLE"		=> $title,
					"MESSAGE"	=> false
				);
		}


		return $arResult;
	}

	
	function FormatEvent_System($arFields, $arParams, $bMail = false)
	{
		$arResult = array(
				"EVENT"				=> $arFields,
				"CREATED_BY"		=> array(),
				"ENTITY"			=> array(),
				"EVENT_FORMATTED"	=> array(),
			);

		if (intval($arFields["ENTITY_ID"]) > 0)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["GROUP_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("SONET_GL_EVENT_ENTITY_G");
			}
			else
			{
				$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arFields["ENTITY_ID"]));
				$arResult["ENTITY"]["FORMATTED"]["TYPE_NAME"] = $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arFields["ENTITY_TYPE"]]["TITLE_ENTITY"];
				$arResult["ENTITY"]["FORMATTED"]["URL"] = $url;				
				$arResult["ENTITY"]["FORMATTED"]["NAME"] = $arFields["GROUP_NAME"];
			}
		}

		if (strlen($arFields["MESSAGE"]) > 0)
		{
			$arUsersID = explode(",", $arFields["MESSAGE"]);

			$bFirst = true;
			$count = 0;
			$user_tmp = "";
			
			if ($bMail)
			{
				$dbUser = CUser::GetList(
							($by="last_name"), 
							($order="asc"), 
							array(
								"ID" => implode(" | ", $arUsersID)
							)
						);
				while($arUser = $dbUser->Fetch())
				{
					$count++;
					if (!$bFirst)
						$user_tmp .= ", ";					
						
					$user_tmp .= $arUser["NAME"]." ".$arUser["LAST_NAME"];
					$bFirst = false;
				}
			}
			else
			{
				foreach($arUsersID as $user_id)
				{
					$count++;
					if (!$bFirst)
						$user_tmp .= ", ";

					$user_tmp .= $GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
									'',
									array(
										"ID" => $user_id,
										"USE_THUMBNAIL_LIST" => "N",
										"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
										"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
										"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
										"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
										"SHOW_YEAR" => $arParams["SHOW_YEAR"],
										"CACHE_TYPE" => $arParams["CACHE_TYPE"],
										"CACHE_TIME" => $arParams["CACHE_TIME"],
										"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
										"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
										"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
										"INLINE" => "Y",
										"DO_RETURN" => "Y",
									),
									false,
									array("HIDE_ICONS" => "Y")
								);
					$bFirst = false;
				}
			}
		}

		if ($bMail)
			$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_SYSTEM_".strtoupper($arFields["TITLE"])."_".($count > 1 ? "2" : "1")."_MAIL");
		else
			$title_tmp = GetMessage("SONET_GL_EVENT_TITLE_SYSTEM_".strtoupper($arFields["TITLE"])."_".($count > 1 ? "2" : "1"));

		$title = str_replace(
						array("#USER_NAME#", "#ENTITY#"),
						array($user_tmp, $arResult["ENTITY"]["FORMATTED"]),
						$title_tmp
					);

		$arResult["EVENT_FORMATTED"] = array(
				"TITLE"		=> $title,
				"MESSAGE"	=> false
			);

		return $arResult;
	}	
}
?>