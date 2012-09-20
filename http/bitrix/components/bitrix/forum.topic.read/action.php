<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum"))
	return 0;
$this->IncludeComponentLang("action.php");

if ((strLen($action) > 0) && ($_REQUEST["MESSAGE_MODE"] != "VIEW") && check_bitrix_sessid())
{
	//*************************!Subscribe***************************************************
	if ($_REQUEST["TOPIC_SUBSCRIBE"] == "Y")
		ForumSubscribeNewMessagesEx($arParams["FID"], $arParams["TID"], "N", $strErrorMessage, $strOKMessage);
	if ($_REQUEST["FORUM_SUBSCRIBE"] == "Y")
		ForumSubscribeNewMessagesEx($arParams["FID"], 0, "N", $strErrorMessage, $strOKMessage);
	$result = false;
	//*************************!Subscribe***************************************************
	if (strLen($action) > 0 && $action != "SUBSCRIBE")
	{
		$arFields = array();
		$url = false;
		$code = false;
		$message = array();
		if ($_SERVER['REQUEST_METHOD'] == "POST"):
			$message = (!empty($_POST["MID_ARRAY"]) ? $_POST["MID_ARRAY"] : $_POST["MID"]);
			if ((empty($message) || $message == "s") && !empty($_POST["message_id"])):
				$message = $_POST["message_id"];
			endif;
		else:
			$message = (!empty($_REQUEST["MID_ARRAY"]) ? $_REQUEST["MID_ARRAY"] : $_REQUEST["MID"]);
			if ((empty($message) || $message == "s") && !empty($_REQUEST["message_id"])):
				$message = $_REQUEST["message_id"];
			endif;
		endif;
		
		switch ($action)
		{
			case "EDIT_TOPIC":
				$MID = 0;
				$db_res = CForumMessage::GetList(array("ID"=>"ASC"), array("TOPIC_ID"=>$arParams["TID"]), false, 1);
				if (($db_res) && ($res = $db_res->Fetch()))
					$MID = intVal($res["ID"]);
				if ($MID > 0)
				{
					$url = ForumAddPageParams(
						CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_NEW"], array("FID" => $arParams["FID"])), 
						array("TID" => $arParams["TID"], "MID" => $MID, "MESSAGE_TYPE" => "EDIT", "sessid" => bitrix_sessid()), false, false);
					LocalRedirect($url);
				}
				break;
			case "REPLY":
				$arFields = array(
						"FID" => $arParams["FID"],
						"TID" => $arParams["TID"],
						"POST_MESSAGE" => $_POST["POST_MESSAGE"],
						"AUTHOR_NAME" => $_POST["AUTHOR_NAME"],
						"AUTHOR_EMAIL" => $_POST["AUTHOR_EMAIL"],
						"USE_SMILES" => $_POST["USE_SMILES"],
						"captcha_word" =>  $_POST["captcha_word"],
						"captcha_code" => $_POST["captcha_code"]);
				if (!empty($_FILES["ATTACH_IMG"]))
				{
					$arFields["ATTACH_IMG"] = $_FILES["ATTACH_IMG"]; 
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
						$arFields["FILES"] = $arFiles; 
				}
				$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE"], 
							array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID"=>"#result#"));
				break;
			case "VOTE4USER":
				$arFields = array(
					"UID" => $_GET["UID"],
					"VOTES" => $_GET["VOTES"],
					"VOTE" => (($_GET["VOTES_TYPE"]=="U") ? True : False));
				$url = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_MESSAGE"], 
					array("FID" => $arParams["FID"], 
						"TID" => $arParams["TID"], 
						"MID" => (intVal($_REQUEST["MID"]) > 0 ? $_REQUEST["MID"] : "s")
					));
				break;
			case "HIDE":
			case "SHOW":
			case "FORUM_MESSAGE2SUPPORT":
				$arFields = array("MID" => $message);
				$mid = (is_array($message) ? $message[0] : $message);
				$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE"], 
						array(
							"FID" => $arParams["FID"], 
							"TID" => $arParams["TID"], 
							"MID" => (!empty($mid) ? $mid : "s")
						));
				if ($action == "FORUM_MESSAGE2SUPPORT")
				{
					$url = "/bitrix/admin/ticket_edit.php?ID=#result#&amp;lang=".LANGUAGE_ID;
				}
				break;
			case "DEL":
				$arFields = array("MID" => $message);
				$url = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
						array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => "#MID#"));
				break;
			case "SET_ORDINARY":
			case "SET_TOP":
			case "STATE_Y":
			case "STATE_N":
				if ($action == "STATE_Y")
					$action = "OPEN";
				elseif ($action == "STATE_N")
					$action = "CLOSE";
				elseif ($action == "SET_ORDINARY")
					$action = "ORDINARY";
				else 
					$action = "TOP";
					
				$arFields = array("TID" => $arParams["TID"]);
				$url = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_MESSAGE"], 
					array("FID" => $arParams["FID"], 
						"TID" => $arParams["TID"], 
						"MID" => ($arParams["MID"] > 0 ? $arParams["MID"] : "s")));
				break;
			case "HIDE_TOPIC":
			case "SHOW_TOPIC":
				$arFields = array("TID" => $arParams["TID"]);
				$url = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_MESSAGE"], 
					array("FID" => $arParams["FID"], 
						"TID" => $arParams["TID"], 
						"MID" => ($arParams["MID"] > 0 ? $arParams["MID"] : "s")));
				break;
			case "DEL_TOPIC":
					$arFields = array("TID" => $arParams["TID"]);
					$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_LIST"], 
						array("FID" => $arParams["FID"]));
				break;
			case "FORUM_SUBSCRIBE":
			case "TOPIC_SUBSCRIBE":
			case "FORUM_SUBSCRIBE_TOPICS":
				$arFields = array(
					"FID" => $arParams["FID"],
					"TID" => (($action=="FORUM_SUBSCRIBE")?0:$arParams["TID"]),
					"NEW_TOPIC_ONLY" => (($action=="FORUM_SUBSCRIBE_TOPICS")?"Y":"N"));
				$url = ForumAddPageParams(
						CComponentEngine::MakePathFromTemplate(
							$arParams["~URL_TEMPLATES_SUBSCR_LIST"], 
							array()
						), 
						array("FID" => $arParams["FID"], "TID" => $arParams["TID"]));
				break;
			case "MOVE":
				$tmp_message = ForumDataToArray($message);
				$url = CComponentEngine::MakePathFromTemplate(
						$arParams["~URL_TEMPLATES_MESSAGE_MOVE"], 
						array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID" => implode(",", $tmp_message)));
				break;
			case "MOVE_TOPIC":
				$url = CComponentEngine::MakePathFromTemplate(
							$arParams["~URL_TEMPLATES_TOPIC_MOVE"], 
							array("FID" => $arParams["FID"], "TID" => $arParams["TID"]));
				break;
		}

		if ($action != "MOVE" && $action != "MOVE_TOPIC")
		{
			$result = ForumActions($action, $arFields, $strErrorMessage, $strOKMessage);
			if ($action == "DEL")
			{
				$arFields = CForumTopic::GetByID($arParams["TID"]);
				if (empty($arFields))
				{
					$url = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], 
						array("FID" => $arParams["FID"]));
					$action = "del_topic";
				}
				else 
				{
					$mid = intVal($message);
					if (is_array($message))
					{
						sort($message);
						$mid = array_pop($message);
					}
					$arFilter = array("TOPIC_ID"=>$arParams["TID"], ">ID" => $mid);
					if ($arResult["PERMISSION"] < "Q") 
						$arFilter["APPROVED"] = "Y";
					$db_res = CForumMessage::GetList(array("ID" => "ASC"), $arFilter, false, 1);
					if ($db_res && $res = $db_res->Fetch()):
						$mid = $res["ID"];
					else: 
						unset($arFilter[">ID"]);
						$arFilter["<ID"] = $mid;
						$db_res = CForumMessage::GetList(array("ID" => "DESC"), $arFilter, false, 1);
						if ($db_res && $res = $db_res->Fetch())
							$mid = $res["ID"];
					endif;
					$mid = (intVal($mid) > 0 ? $mid : "s");
					$url = str_replace("#MID#", $mid, $url);
				}
			}
			elseif ($action == "REPLY")
			{
				$arParams["MID"] = intVal($result);
			}
			
			$url = str_replace("#result#", $result, $url);
		}
		else
			$result = true;
		$action = strToLower($action);
	}
	
	if (!$result)
	{
		$bVarsFromForm = true;
	}
	else 
	{
		$arNote = array(
			"code" => $action,
			"title" => $strOKMessage, 
			"link" => $url);
	}
}
elseif ((strLen($action) > 0) && ($_REQUEST["MESSAGE_MODE"] != "VIEW") && !check_bitrix_sessid())
{
	$bVarsFromForm = true;
	$strErrorMessage = GetMessage("F_ERR_SESS_FINISH");
}
elseif($_POST["MESSAGE_MODE"] == "VIEW")
{
	$View = true;
	$bVarsFromForm = true;
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
		"NL2BR" => $arResult["FORUM"]["ALLOW_NL2BR"]);
	$arAllow["SMILES"] = ($_POST["USE_SMILES"]!="Y" ? "N" : $arResult["FORUM"]["ALLOW_SMILES"]);
	$arResult["POST_MESSAGE_VIEW"] = $parser->convert($_POST["POST_MESSAGE"], $arAllow);
	$arResult["MESSAGE_VIEW"]["AUTHOR_NAME"] = ($USER->IsAuthorized() || empty($_POST["AUTHOR_NAME"]) ? $arResult["USER"]["SHOW_NAME"] : trim($_POST["AUTHOR_NAME"]));
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
	$arResult["VIEW"] = "Y";
}
?>