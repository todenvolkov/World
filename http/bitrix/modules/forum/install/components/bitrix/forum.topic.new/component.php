<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return false;
endif;
	$strErrorMessage = "";
	$strOKMessage = "";
	$arError = array();
	$arNote = array();
	$bVarsFromForm = false;
	$arResult["SHOW_MESSAGE_FOR_AJAX"] = "N";
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intVal(intVal($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["MID"] = (intVal($arParams["MID"]) <= 0 ? $_REQUEST["MID"] : $arParams["MID"]);
	$arParams["MESSAGE_TYPE"] = (empty($arParams["MESSAGE_TYPE"]) ? $_REQUEST["MESSAGE_TYPE"] : $arParams["MESSAGE_TYPE"]);
	$arParams["MESSAGE_TYPE"] = ($arParams["MESSAGE_TYPE"]!="EDIT" ? "NEW" : "EDIT");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"forums" => "PAGE_NAME=forums&GID=#GID#", 
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#", 
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#", 
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["PATH_TO_SMILE"] = (empty($arParams["PATH_TO_SMILE"]) ? "/bitrix/images/forum/smile/" : $arParams["PATH_TO_SMILE"]);
	$arParams["PATH_TO_ICON"] = (empty($arParams["PATH_TO_ICON"]) ? "/bitrix/images/forum/icons/" : $arParams["PATH_TO_ICON"]);
	if ($arParams["AJAX_TYPE"] == "Y" || ($arParams["AJAX_TYPE"] == "A" && COption::GetOptionString("main", "component_ajax_on", "Y") == "Y"))
		$arParams["AJAX_TYPE"] = "Y";
	else
		$arParams["AJAX_TYPE"] = "N";
	$arParams["AJAX_CALL"] = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
	$arParams["AJAX_CALL"] = (($arParams["AJAX_TYPE"] == "Y" && $arParams["AJAX_CALL"] == "Y") ? "Y" : "N");
	$arParams["VOTE_CHANNEL_ID"] = intVal($arParams["VOTE_CHANNEL_ID"]);
	$arParams["SHOW_VOTE"] = ($arParams["SHOW_VOTE"] == "Y" && $arParams["VOTE_CHANNEL_ID"] > 0 && IsModuleInstalled("vote") ? "Y" : "N");
	$arParams["VOTE_GROUP_ID"] = (!is_array($arParams["VOTE_GROUP_ID"]) || empty($arParams["VOTE_GROUP_ID"]) ? array() : $arParams["VOTE_GROUP_ID"]);
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/

$arResult["MESSAGE"] = array();
$arResult["TOPIC"] = array();
if ($arParams["SHOW_VOTE"] == "Y" && CModule::IncludeModule("vote"))
{
	if (CVoteChannel::GetGroupPermission($arParams["VOTE_CHANNEL_ID"]) < 2) 
		$arParams["SHOW_VOTE"] = "N";
    $res = array_intersect($USER->GetUserGroupArray(), $arParams["VOTE_GROUP_ID"]);
    $arParams["SHOW_VOTE"] = (empty($res) ? "N" : $arParams["SHOW_VOTE"]);
}

if ($arParams["MESSAGE_TYPE"] == "EDIT")
{
	if ($arParams["MID"] <= 0):
		$arError = array(
			"code" => "mid_is_lost",
			"title" => GetMessage("F_MID_IS_LOST"),
			"link" => $arResult["index"]);
	else:
		$arResult["MESSAGE"] = CForumMessage::GetByIDEx($arParams["MID"], array("GET_TOPIC_INFO" => "Y", "GET_FORUM_INFO" => "Y", "FILTER" => "N"));
		if (empty($arResult["MESSAGE"])):
			$arError = array(
				"code" => "mid_is_lost",
				"title" => GetMessage("F_MID_IS_LOST"),
				"link" => $arResult["index"]);
		else:
			$arResult["TOPIC"] = $arResult["MESSAGE"]["TOPIC_INFO"];
			$arResult["FORUM"] = $arResult["MESSAGE"]["FORUM_INFO"];
			$arParams["TID"] = $arResult["TOPIC"]["ID"];
			$arParams["FID"] = $arResult["FORUM"]["ID"];
			
			if ($arParams["SHOW_VOTE"] == "Y" && $arResult["MESSAGE"]["PARAM1"] == "VT" && intVal($arResult["MESSAGE"]["PARAM2"]) > 0)
			{
				$db_res = CVoteQuestion::GetListEx(array("ID" => "ASC"), array("CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"], "VOTE_ID" => $arResult["MESSAGE"]["PARAM2"]));
				if ($db_res && $res = $db_res->Fetch())
				{
					do 
					{
						$arResult["~QUESTIONS"][$res["ID"]] = $res;
						$arResult["~QUESTIONS"][$res["ID"]]["ANSWERS"] = array();
					} while ($res = $db_res->Fetch());
				}
				if (!empty($arResult["~QUESTIONS"]))
				{
					$db_res = CVoteAnswer::GetListEx(array("ID" => "ASC"), array("VOTE_ID" => $arResult["MESSAGE"]["PARAM2"]));
					if ($db_res && $res = $db_res->Fetch())
					{
						do 
						{
							if (is_set($arResult["~QUESTIONS"], $res["QUESTION_ID"]))
								$arResult["~QUESTIONS"][$res["QUESTION_ID"]]["ANSWERS"][$res["ID"]] = $res;
						} while ($res = $db_res->Fetch());
					}
				}
				$arResult["QUESTIONS"] = $arResult["~QUESTIONS"];
			}
		endif;
	endif;
}
else
{
	$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
	if (empty($arResult["FORUM"]))
		$arError = array(
			"code" => "fid_is_lost",
			"title" => GetMessage("F_FID_IS_LOST"),
			"link" => $arResult["index"]);
}
if (empty($arError) && ($arParams["MESSAGE_TYPE"]=="NEW" && 
	!CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID())) || ($arParams["MESSAGE_TYPE"]=="EDIT" && 
	!CForumMessage::CanUserUpdateMessage($arParams["MID"], $USER->GetUserGroupArray(), $USER->GetID()))):
	$arError = array(
		"code" => "rightsn_new",
		"title" => ($arParams["MESSAGE_TYPE"]=="NEW" ? GetMessage("F_NO_NPERMS") : GetMessage("F_NO_EPERMS")),
		"link" => $arResult["index"]);
endif;
if (!empty($arError)):
	if ($arParams["AJAX_CALL"] == "Y"):
		$res = array("error" => $arError, "note" => $arNote, "id" => $arParams["MID"], "post" => ShowError($arError["title"]));
		if ($_REQUEST["CONVERT_DATA"] == "Y")
			array_walk($res, "htmlspecialcharsEx");
		$APPLICATION->RestartBuffer();
		?><?=CUtil::PhpToJSObject()?><?
		die();
	endif;
	ShowError($arError["title"]);
	return false;
endif;

ForumSetLastVisit();

/********************************************************************
				Default params
********************************************************************/
$arResult["index"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array());
$arResult["list"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"]));
$arResult["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
	array("FID" => $arParams["FID"], "TID" => intVal($arParams["TID"]), "MID"=>(intVal($arParams["MID"]) > 0 ? intVal($arParams["MID"]) : "s")));
$arResult["URL"] = array(
	"INDEX" => $arResult["index"], 
	"~INDEX" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_INDEX"], array()), 
	"LIST" => $arResult["list"], 
	"~LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $arParams["FID"])), 
	"READ" => $arResult["read"], 
	"~READ" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
	array("FID" => $arParams["FID"], "TID" => intVal($arParams["TID"]), "MID"=>(intVal($arParams["MID"]) > 0 ? intVal($arParams["MID"]) : "s"))));
$arResult["VIEW"] = ((strToUpper($_REQUEST["MESSAGE_MODE"]) == "VIEW" && $_SERVER["REQUEST_METHOD"] == "POST") ? "Y" : "N");
$_REQUEST["FILES"] = (is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array());
$_REQUEST["FILES_TO_UPLOAD"] = (is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array());

$arResult["MESSAGE_VIEW"] = array();
$arAllow = array(
	"HTML" => $arResult["FORUM"]["ALLOW_HTML"],
	"ANCHOR" => $arResult["FORUM"]["ALLOW_ANCHOR"],
	"BIU" => $arResult["FORUM"]["ALLOW_BIU"],
	"IMG" => $arResult["FORUM"]["ALLOW_IMG"],
	"VIDEO" => $arResult["FORUM"]["ALLOW_VIDEO"],
	"LIST" => $arResult["FORUM"]["ALLOW_LIST"],
	"QUOTE" => $arResult["FORUM"]["ALLOW_QUOTE"],
	"CODE" => $arResult["FORUM"]["ALLOW_CODE"],
	"FONT" => $arResult["FORUM"]["ALLOW_FONT"],
	"SMILES" => $arResult["FORUM"]["ALLOW_SMILES"],
	"UPLOAD" => $arResult["FORUM"]["ALLOW_UPLOAD"],
	"NL2BR" => $arResult["FORUM"]["ALLOW_NL2BR"],
	"SMILES" => ($_POST["USE_SMILES"] == "Y" ? "Y" : "N"));
$arResult["GROUP_NAVIGATION"] = array();
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"], $arParams["CACHE_TIME"]);
/********************************************************************
				/Default params
********************************************************************/
	
/********************************************************************
				Action
********************************************************************/
/************** Save message ***************************************/
if ($_SERVER["REQUEST_METHOD"] == "POST"):
	if (!check_bitrix_sessid())
	{
		$strErrorMessage .= GetMessage("F_ERR_SESS_FINISH");
	}
	elseif (!in_array($arResult["FORUM"]["ALLOW_UPLOAD"], array("Y", "A", "F")) && (!empty($_FILES) || !empty($_REQUEST["FILES"]))) 
	{
		$strErrorMessage .= GetMessage("F_ERRRO_FILE_NOT_UPLOAD");
		unset($_REQUEST["FILES"]);
	}
	elseif ($arResult["VIEW"] == "N")
	{
		$arFieldsG = array(
			"POST_MESSAGE" => $_REQUEST["POST_MESSAGE"],
			"USE_SMILES" => $_REQUEST["USE_SMILES"], 
			"FILES" => array());
		if ($arParams["SHOW_VOTE"] == "Y" && !empty($_REQUEST["QUESTION"]))
		{
			$VOTE_ID = ($arResult["MESSAGE"]["PARAM1"] == 'VT' ? intVal($arResult["MESSAGE"]["PARAM2"]) : 0);
			$arVote = array(
				"CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"], 
				"TITLE" => $_REQUEST["TITLE"]);
			if ($VOTE_ID <= 0):
				$timestamp = CForumNew::GetNowTime();
				$arVote["DATE_START"] = GetTime($timestamp, "FULL");
				$arVote["DATE_END"] = GetTime(($timestamp + 30*86400), "FULL");
			endif;
			
			$arVote["QUESTIONS"] = array();
			foreach ($_REQUEST["QUESTION"] as $key => $val):
				$res = array(
					"QUESTION" => trim($val), 
					"MULTI" => ($_REQUEST["MULTI"][$key] == "Y" ? "Y" : "N"));
				if (is_set($arResult["~QUESTIONS"], $_REQUEST["QUESTION_ID"][$key])):
					$res["ID"] = intVal($_REQUEST["QUESTION_ID"][$key]);
					if ($_REQUEST["QUESTION_DEL"][$key] == "Y"):
						$res["DEL"] = "Y";
					endif;
				elseif ($_REQUEST["QUESTION_DEL"][$key] == "Y"):
					continue;
				endif;
				
				$res["ANSWERS"] = array();
				foreach ($_REQUEST["ANSWER"][$key] as $keya => $vala):
					$resa = array(
						"MESSAGE" => trim($vala), 
						"FIELD_TYPE" => ($res["MULTI"] == "Y" ? 1 : 0));
					if ($res["ID"] > 0 && is_set($arResult["~QUESTIONS"][$res["ID"]]["ANSWERS"], $_REQUEST["ANSWER_ID"][$key][$keya])):
						$resa["ID"] = intVal($_REQUEST["ANSWER_ID"][$key][$keya]);
						if ($_REQUEST["ANSWER_DEL"][$key][$keya] == "Y"):
							$resa["DEL"] = "Y";
						endif;
					elseif ($_REQUEST["ANSWER_DEL"][$key][$keya] == "Y" || empty($resa["MESSAGE"])):
						continue;
					endif;
					$res["ANSWERS"][] = $resa;
				endforeach;
				
				if (empty($res["ANSWERS"]) && empty($res["QUESTION"]) && intVal($res["ID"]) <= 0):
					continue;
				endif;
				$arVote["QUESTIONS"][] = $res;
			endforeach;
			if (!empty($arVote["QUESTIONS"])):
				$VOTE_ID = VoteVoteEditFromArray($arParams["VOTE_CHANNEL_ID"], ($VOTE_ID > 0 ? $VOTE_ID : false), $arVote);
				if (intVal($VOTE_ID) > 0):
					$arFieldsG["PARAM1"] = "VT";
					$arFieldsG["PARAM2"] = $VOTE_ID;
				else: 
					$VOTE_ID = false;
					$e = $GLOBALS['APPLICATION']->GetException();
					if ($e):
						$strErrorMessage .= $e->GetString();
					endif;
				endif;
			endif;
		}
		
		if (empty($strErrorMessage))
		{
			foreach (array("AUTHOR_NAME", "AUTHOR_EMAIL", "TITLE", "TAGS", "DESCRIPTION", "ICON_ID") as $res)
			{
				if (is_set($_REQUEST, $res))
					$arFieldsG[$res] = $_REQUEST[$res];
			}
			if (!empty($_FILES["ATTACH_IMG"]))
			{
				$arFieldsG["ATTACH_IMG"] = $_FILES["ATTACH_IMG"]; 
				if ($arParams["MESSAGE_TYPE"] == "EDIT" && $_REQUEST["ATTACH_IMG_del"] == "Y")
					$arFieldsG["ATTACH_IMG"]["del"] = "Y";
			}
			else
			{
				$arFiles = array();
				if (!empty($_REQUEST["FILES"]))
				{
					foreach ($_REQUEST["FILES"] as $key):
						$arFiles[$key] = array("FILE_ID" => $key);
						if (!in_array($key, $_REQUEST["FILES_TO_UPLOAD"]))
							$arFiles[$key]["del"] = "Y";
					endforeach;
				}
				if (!empty($_FILES))
				{
					$res = array();
					foreach ($_FILES as $key => $val):
						if (substr($key, 0, strLen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"])):
							$arFiles[] = $_FILES[$key];
						endif;
					endforeach;
				}
				if (!empty($arFiles))
					$arFieldsG["FILES"] = $arFiles; 
			}
	
			if ($arParams["MESSAGE_TYPE"] == "EDIT")
			{
				$arFieldsG["EDIT_ADD_REASON"] = $_REQUEST["EDIT_ADD_REASON"];
				$arFieldsG["EDITOR_NAME"] = $_REQUEST["EDITOR_NAME"];
				$arFieldsG["EDITOR_EMAIL"] = $_REQUEST["EDITOR_EMAIL"];
				$arFieldsG["EDIT_REASON"] = $_REQUEST["EDIT_REASON"];
			}
			$TID1 = ($arParams["MESSAGE_TYPE"]=="NEW") ? 0 : intVal($arParams["TID"]);
			$MID1 = ($arParams["MESSAGE_TYPE"]=="NEW") ? 0 : intVal($arParams["MID"]);
			
			
			$MID1 = intVal(ForumAddMessage($arParams["MESSAGE_TYPE"], $arParams["FID"], $TID1, $MID1, $arFieldsG, $strErrorMessage, $strOKMessage, false, $_POST["captcha_word"], 0, $_POST["captcha_code"]));
			if ($MID1 > 0 && empty($strErrorMessage))
			{
				$arParams["MID"] = $MID1;
				$db_res = CForumMessage::GetList(array(), array("ID" => $MID1));
				if ($db_res && $res = $db_res->GetNext())
					$arResult["MESSAGE"] = $res;
				
				$addParams = array();
				if ($_REQUEST["TOPIC_SUBSCRIBE"]=="Y"||$_REQUEST["FORUM_SUBSCRIBE"]=="Y")
				{
					$addParams["sessid"] = bitrix_sessid();
					if ($_REQUEST["TOPIC_SUBSCRIBE"]=="Y")
						$addParams["TOPIC_SUBSCRIBE"] = "Y";
					if ($_REQUEST["FORUM_SUBSCRIBE"]=="Y")
						$addParams["FORUM_SUBSCRIBE"] = "Y";
				}
				$arNote = array(
					"code" => strToLower($arParams["MESSAGE_TYPE"]),
					"title" => $strOKMessage, 
					"link" => ForumAddPageParams(CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
							array("FID" => intVal($arParams["FID"]), "TID" => intVal($arResult["MESSAGE"]["TOPIC_ID"]), "MID" => intVal($arParams["MID"]))),
						$addParams));
				if ($arParams["AJAX_CALL"] == "N")
				{
					$url = ForumAddPageParams($arNote["link"], array("result" => $arNote["code"]));
					LocalRedirect($url);
				}
				else 
				{
					$arResult["SHOW_MESSAGE_FOR_AJAX"] = "Y";
				}
			}
			elseif (intVal($arFieldsG["PARAM2"]) > 0 && $arFieldsG["PARAM1"] == "VT")
			{
				CVote::Delete($arFieldsG["PARAM2"]);
			}			
		}
	}
/************** Perview message ************************************/
	elseif ($arResult["VIEW"] == "Y")
	{
		$bVarsFromForm = true;
		$arResult["POST_MESSAGE_VIEW"] = $parser->convert($_POST["POST_MESSAGE"], $arAllow);
		$arResult["MESSAGE_VIEW"]["TEXT"] = $arResult["POST_MESSAGE_VIEW"];
		$arFields = array(
			"FORUM_ID" => intVal($arParams["FID"]), 
			"TOPIC_ID" => intVal($arParams["TID"]), 
			"MESSAGE_ID" => intVal($arParams["MID"]), 
			"USER_ID" => intVal($GLOBALS["USER"]->GetID()));
		$arFiles = array();
		$arFilesExists = array();
		$res = array();
		
		foreach ($_FILES as $key => $val):
			if (substr($key, 0, strLen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"])):
				$arFiles[] = $_FILES[$key];
			endif;
		endforeach;
		foreach ($_REQUEST["FILES"] as $key => $val) 
		{
			if (!in_array($val, $_REQUEST["FILES_TO_UPLOAD"]))
			{
				$arFiles[$val] = array("FILE_ID" => $val, "del" => "Y");
				unset($_REQUEST["FILES"][$key]);
				unset($_REQUEST["FILES_TO_UPLOAD"][$key]);
			}
			else 
			{
				$arFilesExists[$val] = array("FILE_ID" => $val);
			}
		}
		if (!empty($arFiles))
		{
			$res = CForumFiles::Save($arFiles, $arFields);
			$res1 = $GLOBALS['APPLICATION']->GetException();
			if ($res1):
				$strErrorMessage .= $res1->GetString();
			endif;
		}
		$res = is_array($res) ? $res : array();
		foreach ($res as $key => $val)
			$arFilesExists[$key] = $val;
		$arFilesExists = array_keys($arFilesExists);
		sort($arFilesExists);
		$arResult["MESSAGE_VIEW"]["FILES"] = $_REQUEST["FILES"] = $arFilesExists;
	}
	if (!empty($strErrorMessage))
	{
		$arResult["ERROR_MESSAGE"] = $strErrorMessage;
		$bVarsFromForm = true;
	}
endif;
/************** Show message for ajax ******************************/
if ($arResult["SHOW_MESSAGE_FOR_AJAX"] == "Y")
{
	$APPLICATION->RestartBuffer();
	if (empty($arResult["MESSAGE"]))
	{
		$db_res = CForumMessage::GetList(array(), array("ID" => $MID1));
		if ($db_res && $res = $db_res->GetNext())
			$arResult["MESSAGE"] = $res;
	}
	if (!empty($arResult["MESSAGE"]))
	{
		$src = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/";
		if (defined("BX_IMG_SERVER"))
			$src = BX_IMG_SERVER.$src;
		$arResult["MESSAGE"]["FILES"] = array();
		$db_res = CForumFiles::GetList(array(), array("MESSAGE_ID" => $arResult["MESSAGE"]["ID"]));
		if ($db_res && $res = $db_res->GetNext())
		{
			do 
			{
				$res["SRC"] = str_replace("//", "/" , $src.$res["SUBDIR"]."/".$res["FILE_NAME"]);
				$arResult["MESSAGE"]["FILES"][$res["FILE_ID"]] = $res;
			} while ($res = $db_res->GetNext());
		}
		$res = $arResult["MESSAGE"];
		$res["POST_MESSAGE_TEXT"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
		$res["POST_MESSAGE_TEXT"] = $parser->convert($res["POST_MESSAGE_TEXT"], $arAllow);
//				************************message attach img****************************************
		$res["ATTACH_IMG"] = "";
		if (intVal($res["~ATTACH_IMG"])>0 && in_array($arResult["FORUM"]["ALLOW_UPLOAD"], array("Y", "A", "F")))
			$res["ATTACH_IMG"] = CFile::ShowFile($res["~ATTACH_IMG"], 0, 300, 300, true, "border=0", false);
			
		if (!empty($res["EDITOR_ID"]))
		{
			$res["EDITOR_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["EDITOR_ID"]));
		}
		
		if (strLen(trim($res["EDIT_DATE"])) > 0)
		{
			$res["EDIT_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["EDIT_DATE"], CSite::GetDateFormat()));
		}
		$arResult["MESSAGE"] = $res;
	}
}
/************** Navigation *****************************************/
if (intVal($arResult["FORUM"]["FORUM_GROUP_ID"]) > 0):
	$PARENT_ID = intVal($arResult["FORUM"]["FORUM_GROUP_ID"]);
	while ($PARENT_ID > 0)
	{
		$res = $arResult["GROUPS"][$PARENT_ID];
		$res["URL"] = array(
			"GROUP" => CComponentEngine::MakePathFromTemplate(
				$arParams["URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID)), 
			"~GROUP" => CComponentEngine::MakePathFromTemplate(
				$arParams["~URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID)));
		$arResult["GROUP_NAVIGATION"][] = $res;
		$PARENT_ID = intVal($arResult["GROUPS"][$PARENT_ID]["PARENT_ID"]);
	}
	$arResult["GROUP_NAVIGATION"] = array_reverse($arResult["GROUP_NAVIGATION"]);
endif;
// *****************************************************************************************
	/* For custom template only */	
	$arFormParams = array(
		"SEF_MODE" => $arParams["SEF_MODE"],
		"MESSAGE_TYPE" => $arParams["MESSAGE_TYPE"],
		"FID" => $arParams["FID"],
		"TID" => $arParams["TID"],
		"MID" => $arParams["MID"],
		"arForum" => $arResult["FORUM"],
		"bVarsFromForm" => $bVarsFromForm,
		"strErrorMessage" => $strErrorMessage,
		"strOKMessage" => $strOKMessage,
		"View" => ($arResult["VIEW"] == "Y"),
		"PAGE_NAME" => "topic_new");
	if ($bVarsFromForm)
	{
		$arFormParams["AUTHOR_NAME"] = $_POST["AUTHOR_NAME"];
		$arFormParams["AUTHOR_EMAIL"] = $_POST["AUTHOR_EMAIL"];
		$arFormParams["POST_MESSAGE"] = $_POST["POST_MESSAGE"];
		$arFormParams["USE_SMILES"] = $_POST["USE_SMILES"];
		$arFormParams["TITLE"] = $_POST["TITLE"];
		$arFormParams["TAGS"] = $_POST["TAGS"];
		$arFormParams["DESCRIPTION"] = $_POST["DESCRIPTION"];
		$arFormParams["ICON_ID"] = $_POST["ICON_ID"];
	}
	$arFormParams["PATH_TO_SMILE"] = $arParams["PATH_TO_SMILE"];
	$arFormParams["PATH_TO_ICON"] = $arParams["PATH_TO_ICON"];
	$arFormParams["CACHE_TIME"] = $arParams["CACHE_TIME"];
	$arFormParams["URL_TEMPLATES_LIST"] = $arParams["~URL_TEMPLATES_LIST"];
	$arFormParams["URL_TEMPLATES_READ"] = $arParams["~URL_TEMPLATES_MESSAGE"];
	$arResult["arFormParams"] = $arFormParams;
	/* For custom template only */	

/*******************************************************************/
if ($arParams["SET_NAVIGATION"] != "N"):
	foreach ($arResult["GROUP_NAVIGATION"] as $key => $res):
		$APPLICATION->AddChainItem($res["NAME"], $res["URL"]["~GROUP"]);
	endforeach;
	$APPLICATION->AddChainItem(htmlspecialchars($arResult["FORUM"]["NAME"]), $arResult["URL"]["~LIST"]);
	if ($arParams["MESSAGE_TYPE"] == "EDIT")
		$APPLICATION->AddChainItem(htmlspecialchars($arResult["TOPIC"]["TITLE"]), $arResult["URL"]["~READ"]);
endif;

if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle((($arParams["MESSAGE_TYPE"]=="NEW")?GetMessage("F_NTITLE"):GetMessage("F_ETITLE")));
// if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	// CForumNew::ShowPanel($arParams["FID"], $arParams["TID"], false);
/*******************************************************************/
$this->IncludeComponentTemplate();
/*******************************************************************/
return array(
	"MESSAGE_TYPE" => $arParams["MESSAGE_TYPE"],
	"FORUM" => $arResult["FORUM"],
	"MESSAGE" => $arResult["MESSAGE"], 
	"bVarsFromForm" => ($bVarsFromForm ? "Y" : "N"),
	"ERROR_MESSAGE" => $strErrorMessage,
	"OK_MESSAGE" => $strOKMessage);
?>
