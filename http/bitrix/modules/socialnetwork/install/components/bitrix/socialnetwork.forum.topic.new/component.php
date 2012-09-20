<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return false;
elseif (!CModule::IncludeModule("socialnetwork")):
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return false;
elseif (intVal($arParams["FID"]) <= 0):
	ShowError(GetMessage("F_FID_IS_EMPTY"));
	return false;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intVal($arParams["FID"]);
	$arParams["TID"] = 0;
	$arParams["MID"] = (intVal($arParams["MID"]) <= 0 ? $_REQUEST["MID"] : $arParams["MID"]);
	$arParams["MESSAGE_TYPE"] = (empty($arParams["MESSAGE_TYPE"]) ? $_REQUEST["MESSAGE_TYPE"] : $arParams["MESSAGE_TYPE"]);
	$arParams["MESSAGE_TYPE"] = ($arParams["MESSAGE_TYPE"]!="EDIT" ? "NEW" : "EDIT");
	
	$arParams["SOCNET_GROUP_ID"] = intVal($arParams["SOCNET_GROUP_ID"]);
	$arParams["MODE"] = ($arParams["SOCNET_GROUP_ID"] > 0 ? "GROUP" : "USER");
	$arParams["USER_ID"] = intVal(intVal($arParams["USER_ID"]) > 0 ? $arParams["USER_ID"] : $USER->GetID());
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"topic_list" => "PAGE_NAME=topic_list",
			"message" => "PAGE_NAME=message&TID=#TID#&MID=#MID#", 
			"profile_view" => "PAGE_NAME=user&UID=#UID#");
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
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default values
********************************************************************/
//************** SocNet Activity ***********************************/
if (($arParams["MODE"] == "GROUP" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum")) ||
	($arParams["MODE"] != "GROUP" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "forum"))):
	ShowError(GetMessage("FORUM_SONET_MODULE_NOT_AVAIBLE"));
	return false;
endif;

//************** Forum *********************************************/
	$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
	$arResult["TOPIC"] = array();
	$arResult["MESSAGE"] = array();
	$arParams["PERMISSION_ORIGINAL"] = ForumCurrUserPermissions($arParams["FID"]);
	$arParams["PERMISSION"] = "A";
	$arResult["ERROR_MESSAGE"] = "";
	$arResult["OK_MESSAGE"] = "";

	$arError = array();
	$arNote = array();
	$user_id = $USER->GetID();
//************** Permission ****************************************/

$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

if (empty($arResult["FORUM"]))
{
	CHTTP::SetStatus("404 Not Found");
	$arError[] = array(
		"id" => "forum_is_lost", 
		"text" => GetMessage("F_FID_IS_LOST"));
}
elseif ($arParams["MODE"] == "GROUP")
{
	if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "full", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "Y";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "newtopic", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "M";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "answer", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "I";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "view", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "E";
}
else
{
	if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "full", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "Y";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "newtopic", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "M";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "answer", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "I";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "forum", "view", $bCurrentUserIsAdmin))
		$arParams["PERMISSION"] = "E";
}
/************** Message ********************************************/
	if ($arParams["MESSAGE_TYPE"] == "EDIT")
	{
		$res = CForumMessage::GetByIDEx($arParams["MID"], array("GET_TOPIC_INFO" => "Y"));
		if (!is_array($res) || empty($res)):
			$arError[] = array(
				"id" => "mid_is_lost",
				"text" => GetMessage("F_MID_IS_LOST"));
		elseif ($res["FORUM_ID"] != $arParams["FID"]):
			$arError[] = array(
				"id" => "mid_is_lost",
				"text" => GetMessage("F_MID_IS_LOST_IN_FORUM"));
		elseif (($arParams["MODE"] == "GROUP" && $res["TOPIC_INFO"]["SOCNET_GROUP_ID"] == $arParams["SOCNET_GROUP_ID"]) || 
			($arParams["MODE"] != "GROUP" && $res["TOPIC_INFO"]["OWNER_ID"] == $arParams["USER_ID"])):
			$arResult["MESSAGE"] = $res;
			$arParams["TID"] = $res["TOPIC_INFO"]["ID"];
			$arResult["TOPIC"] = $res["TOPIC_INFO"];
			$arResult["TOPIC_FILTER"] = CForumTopic::GetByID($arParams["TID"]);
		else:
			$arError[] = array(
				"id" => "mid_is_lost",
				"text" => GetMessage("F_MID_IS_LOST"));
		endif;
	}
/************** Permission *****************************************/
	if ($arParams["MESSAGE_TYPE"]=="NEW" && !CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID(), false, $arParams["PERMISSION"])):
		$arError[] = array(
			"id" => "acces denied", 
			"text" => GetMessage("F_NO_NPERMS"));
	elseif ($arParams["MESSAGE_TYPE"]=="EDIT" && !CForumMessage::CanUserUpdateMessage($arParams["MID"], $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"])):
		$arError[] = array(
			"id" => "acces denied", 
			"text" => GetMessage("F_NO_EPERMS"));
	endif;
/************** Fatal Errors ***************************************/
if (!empty($arError))
{
	$e = new CAdminException($arError);
	$res = $e->GetString();
	ShowError($res);
	return false;
}
/*******************************************************************/
$strErrorMessage = ""; $strOKMessage = "";
$bVarsFromForm = false;
$_REQUEST["FILES"] = (is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array());
$_REQUEST["FILES_TO_UPLOAD"] = (is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array());

$arResult["MESSAGE_VIEW"] = array();
$arResult["VIEW"] = ((strToUpper($_REQUEST["MESSAGE_MODE"]) == "VIEW" && $_SERVER["REQUEST_METHOD"] == "POST") ? "Y" : "N");
$arAllow = array(
	"HTML" => $arResult["FORUM"]["ALLOW_HTML"],
	"ANCHOR" => $arResult["FORUM"]["ALLOW_ANCHOR"],
	"BIU" => $arResult["FORUM"]["ALLOW_BIU"],
	"IMG" => $arResult["FORUM"]["ALLOW_IMG"],
	"LIST" => $arResult["FORUM"]["ALLOW_LIST"],
	"QUOTE" => $arResult["FORUM"]["ALLOW_QUOTE"],
	"CODE" => $arResult["FORUM"]["ALLOW_CODE"],
	"FONT" => $arResult["FORUM"]["ALLOW_FONT"],
	"SMILES" => $arResult["FORUM"]["ALLOW_SMILES"],
	"UPLOAD" => $arResult["FORUM"]["ALLOW_UPLOAD"],
	"NL2BR" => $arResult["FORUM"]["ALLOW_NL2BR"],
	"SMILES" => ($_POST["USE_SMILES"] == "Y" ? "Y" : "N"));
/*******************************************************************/
$arResult["URL"] = array(
	"~LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_TOPIC_LIST"], 
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "UID" => $arParams["USER_ID"])), 
	"LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_LIST"], 
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "UID" => $arParams["USER_ID"])), 
	"~READ" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
		array("UID" => $arParams["USER_ID"], "FID" => $arParams["FID"], "TID" => $arParams["TID"], 
			"MID"=>((intVal($arParams["MID"]) > 0) ? intVal($arParams["MID"]) : "s"))), 
	"READ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
		array("UID" => $arParams["USER_ID"], "FID" => $arParams["FID"], "TID" => $arParams["TID"], 
			"MID"=>((intVal($arParams["MID"]) > 0) ? intVal($arParams["MID"]) : "s"))));
/*******************************************************************/
$parser = new textParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"], $arParams["CACHE_TIME"]);
/********************************************************************
				/Default params
********************************************************************/

ForumSetLastVisit($arParams["FID"], $arParams["TID"]);

/********************************************************************
				Action
********************************************************************/
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$TID1 = ($arParams["MESSAGE_TYPE"]=="NEW") ? 0 : intVal($arParams["TID"]);
	$MID1 = ($arParams["MESSAGE_TYPE"]=="NEW") ? 0 : intVal($arParams["MID"]);

	if (!check_bitrix_sessid())
	{
		$arError[] = array(
			"id" => "bad sessid", 
			"text" => GetMessage("F_ERR_SESS_FINISH"));
	}
	elseif (!in_array($arResult["FORUM"]["ALLOW_UPLOAD"], array("Y", "A", "F")) && (!empty($_FILES) || !empty($_REQUEST["FILES"]))) 
	{
		$arError[] = array(
			"id" => "bad files", 
			"text" => GetMessage("F_ERRRO_FILE_NOT_UPLOAD"));
		unset($_REQUEST["FILES"]);
	}
	elseif ($arResult["VIEW"] == "N")
	{
		$arFieldsG = array(
			"POST_MESSAGE" => $_REQUEST["POST_MESSAGE"],
			"USE_SMILES" => $_REQUEST["USE_SMILES"], 
			"OWNER_ID" => $arParams["USER_ID"], 
			"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"], 
			"PERMISSION_EXTERNAL" => $arParams["PERMISSION"]);

		foreach (array("AUTHOR_NAME", "AUTHOR_EMAIL",
				"TITLE", "TAGS", "DESCRIPTION", 
				"ICON_ID") as $res)
		{
			if (isset($_REQUEST[$res]))
				$arFieldsG[$res] = $_REQUEST[$res];
		}
		
		if (!empty($_FILES["ATTACH_IMG"]))
		{
			$arFieldsG["ATTACH_IMG"] = $_FILES["ATTACH_IMG"];
			if ($arParams["MESSAGE_TYPE"]=="EDIT" && $_REQUEST["ATTACH_IMG_del"] == "Y")
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
		$MID1 = intVal(ForumAddMessage($arParams["MESSAGE_TYPE"], $arParams["FID"], $TID1, $MID1, $arFieldsG, 
			$strErrorMessage, $strOKMessage, false, $_POST["captcha_word"], 0, $_POST["captcha_code"]));
		if ($MID1 > 0)
		{
			$arResult["MESSAGE"] = CForumMessage::GetByID($MID1);
			$arParams["TID"] = $arResult["MESSAGE"]["TOPIC_ID"];
			$arParams["MID"] = $arResult["MESSAGE"]["ID"];
/************** Socialnetwork notification *************************/
			if ($arParams["MESSAGE_TYPE"] == "NEW")
			{
				$sAuthorForMail = $sAuthor = str_replace("#TITLE#", $arResult["MESSAGE"]["AUTHOR_NAME"], GetMessage("SONET_FORUM_LOG_TEMPLATE_GUEST"));
				if (intVal($arResult["MESSAGE"]["AUTHOR_ID"]) > 0)
				{
					$sAuthor = str_replace(array("#URL#", "#TITLE#"), array(CComponentEngine::MakePathFromTemplate(
						$arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arResult["MESSAGE"]["AUTHOR_ID"])), $arResult["MESSAGE"]["AUTHOR_NAME"]), 
						GetMessage("SONET_FORUM_LOG_TEMPLATE_AUTHOR"));
					$sAuthorForMail = str_replace(array("#URL#", "#TITLE#"), array("http://".SITE_SERVER_NAME.CComponentEngine::MakePathFromTemplate(
						$arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arResult["MESSAGE"]["AUTHOR_ID"])), $arResult["MESSAGE"]["AUTHOR_NAME"]), 
						GetMessage("SONET_FORUM_LOG_TEMPLATE_AUTHOR"));
				}

				$sText = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $arResult["MESSAGE"]["POST_MESSAGE_FILTER"] : $arResult["MESSAGE"]["POST_MESSAGE"]);
				
				if ($arParams["MODE"] == "GROUP")
					CSocNetGroup::SetLastActivity($arParams["SOCNET_GROUP_ID"]);
				$logID = CSocNetLog::Add(
					array(
						"ENTITY_TYPE" 		=> ($arParams["MODE"] == "GROUP" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER),
						"ENTITY_ID" 		=> ($arParams["MODE"] == "GROUP" ? $arParams["SOCNET_GROUP_ID"] : $arParams["USER_ID"]),
						"EVENT_ID" 			=> "forum",
						"=LOG_DATE" 		=> $GLOBALS["DB"]->CurrentTimeFunction(),
						"TITLE_TEMPLATE" 	=> str_replace("#AUTHOR_NAME#", $arResult["MESSAGE"]["AUTHOR_NAME"], GetMessage("SONET_FORUM_LOG_TEMPLATE")),
						"TITLE" 			=> $arFieldsG["TITLE"],
						"MESSAGE" 			=> $parser->convert($sText.$sAuthor, $arAllow),
						"TEXT_MESSAGE" 		=> $parser->convert4mail($sText.$sAuthorForMail),
						"URL" 				=> CComponentEngine::MakePathFromTemplate(
												$arParams["~URL_TEMPLATES_MESSAGE"], 
												array(
													"UID" => $arParams["USER_ID"], 
													"FID" => $arParams["FID"], 
													"TID" => $arParams["TID"], 
													"MID" => $arParams["MID"])
												),
						"MODULE_ID" 		=> false,
						"CALLBACK_FUNC" 	=> false,
						"USER_ID" 			=> (intVal($arResult["MESSAGE"]["AUTHOR_ID"]) > 0 ? $arResult["MESSAGE"]["AUTHOR_ID"] : false),
						"PARAMS"			=> "type=T"
					),
					false
				);
					
				if (intval($logID) > 0)
					CSocNetLog::Update($logID, array("TMP_ID" => $logID));

				CSocNetLog::SendEvent($logID, "SONET_NEW_EVENT", $logID);
			}
			$url = ForumAddPageParams(CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
				array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => intVal($arParams["MID"]), 
					"UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])), 
				array("result" => $arNote["code"]));
			LocalRedirect($url);
		}
		else 
		{
			$arError[] = array(
				"id" => $arParams["MESSAGE_TYPE"], 
				"text" => $strErrorMessage);
		}
	}
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
				$arError[] = array(
					"id" => "bad files", 
					"text" => $res1->GetString());
			endif;
		}
		$res = is_array($res) ? $res : array();
		foreach ($res as $key => $val)
			$arFilesExists[$key] = $val;
		$arFilesExists = array_keys($arFilesExists);
		sort($arFilesExists);
		$arResult["MESSAGE_VIEW"]["FILES"] = $_REQUEST["FILES"] = $arFilesExists;
	}
	
	if (!empty($arError))
	{
		$e = new CAdminException($arError);
		$arResult["ERROR_MESSAGE"] = $e->GetString();
		$bVarsFromForm = true;
	}
}
/********************************************************************
				/Action
********************************************************************/


/********************************************************************
				Standart Action
********************************************************************/
/*
$APPLICATION->AddChainItem(GetMessage("FL_FORUM_CHAIN"), $arResult["URL"]["LIST"]);
*/
if ($arParams["MESSAGE_TYPE"] == "EDIT"):
	$APPLICATION->AddChainItem($arResult["TOPIC_FILTER"]["TITLE"], $arResult["URL"]["~READ"]);
endif;
$APPLICATION->AddChainItem(($arParams["MESSAGE_TYPE"]=="NEW" ? GetMessage("F_NTITLE") : GetMessage("F_ETITLE")));
if ($arParams["SET_TITLE"] != "N"):
	$APPLICATION->SetTitle(($arParams["MESSAGE_TYPE"]=="NEW" ? GetMessage("F_NTITLE") : GetMessage("F_ETITLE")));
endif;
/********************************************************************
				Standart Action
********************************************************************/
$this->IncludeComponentTemplate();
return array(
	"PERMISSION" => $arParams["PERMISSION"], 
	"MESSAGE_TYPE" => $arParams["MESSAGE_TYPE"],
	"FORUM" => $arResult["FORUM"],
	"TOPIC" => $arResult["TOPIC"],
	"MESSAGE" => $arResult["MESSAGE_VIEW"],
	"bVarsFromForm" => ($bVarsFromForm ? "Y" : "N"),
	"OK_MESSAGE" => $strOKMessage);

?>