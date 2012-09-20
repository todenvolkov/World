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
	$arParams["TID"] = intVal(empty($arParams["TID"]) ? $_REQUEST["TID"] : $arParams["TID"]);
	$arParams["MID"] = intVal(empty($arParams["MID"]) ? $_REQUEST["MID"] : $arParams["MID"]);
	
	$arParams["PAGE_NAME"] = (empty($arParams["PAGE_NAME"]) ? $_REQUEST["PAGE_NAME"] : $arParams["PAGE_NAME"]);
	$arParams["MESSAGE_TYPE"] = (in_array(strToUpper($arParams["MESSAGE_TYPE"]), array("REPLY", "EDIT", "NEW")) ? strToUpper($arParams["MESSAGE_TYPE"]):"NEW");
	$arParams["bVarsFromForm"] = ($arParams["bVarsFromForm"] == "Y" || $arParams["bVarsFromForm"] === true ? "Y" : "N");
	
	$arParams["SOCNET_GROUP_ID"] = intVal($arParams["SOCNET_GROUP_ID"]);
	$arParams["MODE"] = ($arParams["SOCNET_GROUP_ID"] > 0 ? "GROUP" : "USER");
	$arParams["USER_ID"] = intVal(intVal($arParams["USER_ID"]) > 0 ? $arParams["USER_ID"] : $USER->GetID());
/***************** URL *********************************************/
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	$URL_NAME_DEFAULT = array(
		"topic_list" => "PAGE_NAME=topic_list&FID=#FID#",
		"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["AJAX_TYPE"] = ($arParams["AJAX_TYPE"] == "Y" ? "Y" : "N");
	$arParams["AJAX_CALL"] = (($_REQUEST["AJAX_CALL"] == "Y" && $arParams["AJAX_TYPE"] == "Y") ? "Y" : "N");
	
	$arParams["PATH_TO_SMILE"] = (empty($arParams["PATH_TO_SMILE"]) ? "/bitrix/images/forum/smile/" : $arParams["PATH_TO_SMILE"]);
	$arParams["PATH_TO_ICON"] = (empty($arParams["PATH_TO_ICON"]) ? "/bitrix/images/forum/icon/" : $arParams["PATH_TO_ICON"]);
	$arParams["SMILE_TABLE_COLS"] = (intval($arParams["SMILE_TABLE_COLS"]) > 0 ? intval($arParams["SMILE_TABLE_COLS"]) : 3);

/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params # 1
********************************************************************/
	$arResult["TOPIC"] = array();
	$arResult["TOPIC_FILTER"] = array();
	$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
	$arParams["PERMISSION_ORIGINAL"] = $arParams["PERMISSION"] = ForumCurrUserPermissions($arParams["FID"]);
	$arResult["SHOW_SEARCH"] = (IsModuleInstalled("search") ? "Y" : "N");
	$arResult["IS_AUTHORIZED"] = ($USER->IsAuthorized() ? "Y" : "N");
	$arResult["ERROR_MESSAGE"] = trim($arParams["ERROR_MESSAGE"]);

	$arError = array();
	$arNote = array();
/********************************************************************
				/Default params #1
********************************************************************/

/********************************************************************
				Main Data & Permissions
********************************************************************/

	$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
	
	if (empty($arResult["FORUM"])):
		ShowError(GetMessage("F_FID_IS_LOST"));
		return false;
	elseif (($arParams["MODE"] == "GROUP" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum")) ||
		($arParams["MODE"] != "GROUP" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "forum"))):
		ShowError(GetMessage("FORUM_SONET_MODULE_NOT_AVAIBLE"));
		return false;
	elseif ($arParams["PERMISSION_ORIGINAL"] < "Y"):
		$arParams["PERMISSION"] = "A";
		$user_id = $USER->GetID();
		if ($arParams["MODE"] == "GROUP")
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
		if ("E" <= $arParams["PERMISSION_ORIGINAL"] && $arParams["PERMISSION"] < $arParams["PERMISSION_ORIGINAL"])
		{
			$arParams["PERMISSION"] = $arParams["PERMISSION_ORIGINAL"];
		}
	endif;
	
/************** Message / Topic ************************************/
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
			$arResult["TOPIC_FILTER"] = CForumTopic::GetByID($res["TOPIC_ID"]);
		else:
			$arError[] = array(
				"id" => "mid_is_lost",
				"text" => str_replace("#SOCNET_OBJECT#", ($arParams["MODE"] == "GROUP" ? 
					GetMessage("F_GROUPS") : GetMessage("F_USERS")), GetMessage("F_MID_IS_LOST_IN_OBJECT")));
		endif;
	}
/************** Topic **********************************************/
	elseif ($arParams["MESSAGE_TYPE"] == "REPLY")
	{
		$arFilter = array("FORUM_ID" => $arParams["FID"], "ID" => $arParams["TID"], "SOCNET_GROUP_ID" => false);
		if ($arParams["MODE"] == "GROUP"):
			$arFilter["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
		else:
			$arFilter["OWNER_ID"] = $arParams["USER_ID"];
		endif;
		
		$db_res = CForumTopic::GetList(array(), $arFilter); 
		if (!($db_res && $res = $db_res->GetNext())):
			$res = CForumTopic::GetByID($arParams["TID"]);
			if (empty($res) || !is_array($res)):
				$arError[] = array(
					"id" => "topic is not found",
					"text" => GetMessage("F_TID_IS_LOST"));
			elseif ($arParams["MODE"] == "GROUP" && $res["SOCNET_GROUP_ID"] != $arParams["SOCNET_GROUP_ID"] || 
				$arParams["MODE"] != "GROUP" && $res["OWNER_ID"] != $arParams["USER_ID"]):
				$arError[] = array(
					"id" => "not correct socnet_object",
					"text" => str_replace("#SOCNET_OBJECT#", ($arParams["MODE"] == "GROUP" ? 
					GetMessage("F_GROUPS") : GetMessage("F_USERS")), GetMessage("F_TID_IS_LOST_IN_OBJECT")));
			elseif ($res["FORUM_ID"] != $arParams["FID"]):
				$arError[] = array(
					"id" => "not correct forum_id",
					"text" => GetMessage("F_TID_IS_LOST_IN_FORUM"));
			endif;
		elseif ($res["STATE"] == "L"):
			$arError[] = array(
				"id" => "topic is topic-link",
				"text" => GetMessage("F_TID_IS_LINK"));
		elseif ($res["APPROVED"] != "Y" && $arParams["PERMISSION"] < "Q"):
			$arError[] = array(
				"id" => "topic is not approved",
				"text" => GetMessage("F_TID_IS_NOT_APPROVED"));
		else:
			$arResult["TOPIC"] = $res;
		endif;
	}
/************** Permission *****************************************/
	if (empty($arError))
	{
		if ($arParams["MESSAGE_TYPE"] == "NEW" && 
			!CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID(), $arResult["FORUM"], $arParams["PERMISSION"])):
			$arError[] = array(
				"id" => "user cannot add topic",
				"text" => GetMessage("F_NO_NPERMS"));
		elseif ($arParams["MESSAGE_TYPE"] == "EDIT" && 
			!CForumMessage::CanUserUpdateMessage($arParams["MID"], $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"])):
			$arError[] = array(
				"id" => "user cannot edit message",
				"text" => GetMessage("F_NO_EPERMS"));
		elseif ($arParams["MESSAGE_TYPE"] == "REPLY" && 
			!CForumMessage::CanUserAddMessage($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"])):
			return false;
		endif;
	}
		
	if (!empty($arError))
	{
		$e = new CAdminException($arError);
		$res = $e->GetString();
		ShowError($res);
		return false;
	}
/********************************************************************
				/Main Data & Permissions
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$_REQUEST["FILES"] = is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array();
$_REQUEST["FILES_TO_UPLOAD"] = is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array();

//************* Message ********************************************/
$arResult["DATA"] = array(
	"USE_SMILES" => "Y", 
	"AUTHOR_ID" => $USER->GetID(),
	"AUTHOR_NAME" => $GLOBALS["FORUM_STATUS_NAME"]["guest"],
	"AUTHOR_EMAIL" => "",
	"TITLE" => "",
	"TAGS" => "",
	"DESCRIPTION" => "",
	"POST_MESSAGE" => "",
	"ICON_ID" => "",
	"ATTACH_IMG" => "", 
	"ATTACH_IMG_FILE" => false, 
	"CAPTCHA_CODE" => "", 
	"EDITOR_NAME" => $GLOBALS["FORUM_STATUS_NAME"]["guest"], 
	"EDITOR_EMAIL" => "quest@guest.com", 
	"EDIT_REASON" => "", 
	"FILES" => array());

if ($arParams["MESSAGE_TYPE"] == "EDIT")
{
	$arResult["DATA"]["AUTHOR_NAME"] = $arResult["MESSAGE"]["AUTHOR_NAME"];
	$arResult["DATA"]["AUTHOR_EMAIL"] = $arResult["MESSAGE"]["AUTHOR_EMAIL"];
	$arResult["DATA"]["TITLE"] = $arResult["TOPIC"]["TITLE"];
	$arResult["DATA"]["TAGS"] = $arResult["TOPIC"]["TAGS"];
	$arResult["DATA"]["DESCRIPTION"] = $arResult["TOPIC"]["DESCRIPTION"];
	$arResult["DATA"]["POST_MESSAGE"] = $arResult["MESSAGE"]["POST_MESSAGE"];
	$arResult["DATA"]["ICON_ID"] = $arResult["TOPIC"]["ICON_ID"];
	$arResult["DATA"]["USE_SMILES"] = ($arResult["MESSAGE"]["USE_SMILES"]=="Y") ? "Y" : "N";
	$arResult["DATA"]["AUTHOR_ID"] = $arResult["MESSAGE"]["AUTHOR_ID"];
	$arResult["DATA"]["ATTACH_IMG"] = $arResult["MESSAGE"]["ATTACH_IMG"];
	$arResult["DATA"]["EDITOR_NAME"] = $arResult["MESSAGE"]["EDITOR_NAME"];
	$arResult["DATA"]["EDITOR_EMAIL"] = $arResult["MESSAGE"]["EDITOR_EMAIL"];
	$arResult["DATA"]["EDIT_REASON"] = $arResult["MESSAGE"]["EDIT_REASON"];
	$db_res = CForumFiles::GetList(array(), array("MESSAGE_ID" => $arParams["MID"]));
	if ($db_res && $res = $db_res->Fetch())
	{
		do 
		{
			$arResult["DATA"]["FILES"][$res["FILE_ID"]] = $res;
		} while ($res = $db_res->Fetch());
	}
}

if ($arParams["bVarsFromForm"] == "Y")
{
	foreach ($arResult["DATA"] as $key => $val)
		$arResult["DATA"][$key] = $_REQUEST[$key];

	$arResult["DATA"]["USE_SMILES"] = ((!is_set($_REQUEST["USE_SMILES"]) || $_REQUEST["USE_SMILES"]=="Y") ? "Y" : "N");
	$arResult["DATA"]["AUTHOR_ID"] = $USER->GetID();
	$arResult["DATA"]["FILES"] = array();
	
	foreach ($_REQUEST["FILES"] as $key => $val):
		if (intVal($val) <= 0)
			return false;
		$arResult["DATA"]["FILES"][$val] = $val;
	endforeach;
}

//************* Page info ******************************************/
$arResult["INFO"] = array(
	"HEADER" => ($arParams["MESSAGE_TYPE"] == "NEW" ? GetMessage("FPF_CREATE") : GetMessage("FPF_EDIT")), 
	"SUBMIT" => ($arParams["MESSAGE_TYPE"] == "NEW" ? GetMessage("FPF_SEND") : GetMessage("FPF_EDIT")), 
	"ICONS_LIST" => "", 
	"SMILES_LIST" => "");

if ($arParams["MESSAGE_TYPE"]=="REPLY")
{
	$arResult["INFO"]["HEADER"] = GetMessage("FPF_REPLY");
	$arResult["INFO"]["SUBMIT"] = GetMessage("FPF_REPLY");
}
//************* Panels *********************************************/
$arResult["SHOW_PANEL"] = array(
	"GUEST" => (($arParams["MESSAGE_TYPE"] != "EDIT" && !$USER->IsAuthorized() || 
		$arParams["MESSAGE_TYPE"]=="EDIT" && $arResult["DATA"]["AUTHOR_ID"] <= 0) ? "Y" : "N"), 
	"TOPIC" => ($arParams["MESSAGE_TYPE"]=="NEW" || ($arParams["MESSAGE_TYPE"]=="EDIT" && 
		CForumTopic::CanUserUpdateTopic($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID(), $arParams["PERMISSION"]))  ? "Y" : "N"), 
	"SUBSCRIBE" => "N",
	"ATTACH" => (in_array($arResult["FORUM"]["ALLOW_UPLOAD"], array("Y", "F", "A")) ? "Y" : "N"), 
	"CAPTCHA" => ((!$USER->IsAuthorized() && $arResult["FORUM"]["USE_CAPTCHA"]=="Y") ? "Y" : "N"), 
	"CLOSE_ALL" => (($arResult["FORUM"]["ALLOW_BIU"] == "Y" || $arResult["FORUM"]["ALLOW_FONT"] == "Y" || $arResult["FORUM"]["ALLOW_ANCHOR"] == "Y" || $arResult["FORUM"]["ALLOW_IMG"] == "Y" || 
		$arResult["FORUM"]["ALLOW_QUOTE"] == "Y" || $arResult["FORUM"]["ALLOW_CODE"] == "Y" || $arResult["FORUM"]["ALLOW_LIST"] == "Y") ? "Y" : "N"), 
	"TRANSLIT" => (LANGUAGE_ID == "ru" ? "Y" : "N"), 
	"EDIT_INFO" => ($arParams["MESSAGE_TYPE"] == "EDIT" ? "Y" : "N"), 
	"EDIT_INFO_FOR_GUEST" => (!$USER->IsAuthorized() ? "Y" : "N"), 
	"EDIT_INFO_ASK" => ($USER->IsAdmin() ? "Y" : "N"));
	

if ($arResult["SHOW_PANEL"]["GUEST"] == "Y")
{
	$arResult["DATA"]["AUTHOR_NAME"] = (!empty($arResult["DATA"]["AUTHOR_NAME"]) ? $arResult["DATA"]["AUTHOR_NAME"] : GetMessage("FPF_GUEST"));
}
if ($arResult["SHOW_PANEL"]["TOPIC"] == "Y")
{
	$arResult["ICONS_LIST"] = ForumPrintIconsList(7, "ICON_ID", $arResult["DATA"]["ICON_ID"], GetMessage("FPF_NO_ICON"), LANGUAGE_ID, $arParams["PATH_TO_ICON"], $arParams["CACHE_TIME"]);
}
if ($arResult["FORUM"]["ALLOW_SMILES"] == "Y")
{
	$arResult["SMILES_LIST"] = ForumPrintSmilesList($arParams["SMILE_TABLE_COLS"], LANGUAGE_ID, $arParams["PATH_TO_SMILE"], $arParams["CACHE_TIME"]);
	$arResult["SMILES"] = CForumSmile::GetByType("S", LANGUAGE_ID);
}

$arResult["SHOW_SUBSCRIBE"] = "N";

if ($arResult["SHOW_PANEL"]["ATTACH"] == "Y")
{
	foreach ($arResult["DATA"]["FILES"] as $key => $val):
		if (intVal($val) <= 0)
			return false;
		$arResult["DATA"]["FILES"][$key] = CFile::GetFileArray($key);
	endforeach;
/************** For custom component *******************************/
	$arResult["DATA"]["ATTACH_IMG_FILE"] = false;
	if (strlen($arResult["DATA"]["ATTACH_IMG"]) > 0)
	{
		$arResult["DATA"]["ATTACH_IMG_FILE"] = $arResult["MESSAGE"]["FILES"][$arResult["MESSAGE"]["ATTACH_IMG"]];
		if ($arResult["DATA"]["ATTACH_IMG_FILE"])
			$arResult["DATA"]["ATTACH_IMG"] = CFile::ShowImage($arResult["MESSAGE"]["ATTACH_IMG_FILE"]["SRC"], 200, 200, "border=0");
	}
/************** For custom component/*******************************/
}

if ($arResult["SHOW_PANEL"]["CAPTCHA"] == "Y")
{
	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
	$cpt = new CCaptcha();
	$captchaPass = COption::GetOptionString("main", "captcha_password", "");
	if (strlen($captchaPass) <= 0)
	{
		$captchaPass = randString(10);
		COption::SetOptionString("main", "captcha_password", $captchaPass);
	}
	$cpt->SetCodeCrypt($captchaPass);
	$arResult["DATA"]["CAPTCHA_CODE"] = $cpt->GetCodeCrypt();
}
//************* Paths **********************************************/
$arResult["URL"] = array(
	"LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC_LIST"], array("FID" => $arParams["FID"], 
		"TID" => $arParams["TID"], "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"])), 
	"READ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], array("FID" => $arParams["FID"], 
		"TID" => $arParams["TID"], "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"], 
		"MID"=>((intVal($arParams["MID"]) > 0) ? intVal($arParams["MID"]) : "s"))));
/************** Submit *********************************************/
$arResult["SUBMIT"] = GetMessage("FPF_EDIT");
$arResult["str_HEADER"] = GetMessage("FPF_EDIT_FORM");
if ($arParams["MESSAGE_TYPE"]=="NEW"):
	$arResult["SUBMIT"] = GetMessage("FPF_SEND");
	$arResult["str_HEADER"] = GetMessage("FPF_CREATE_IN_FORUM")." ".$arResult["FORUM"]["NAME"];
elseif ($arParams["MESSAGE_TYPE"]=="REPLY"):
	$arResult["SUBMIT"] = GetMessage("FPF_REPLY");
	$arResult["str_HEADER"] = GetMessage("FPF_REPLY_FORM");
endif;
/********************************************************************
				/Data
********************************************************************/

foreach ($arResult["DATA"] as $key => $val):
	$arResult["DATA"]["~".$key] = $val;
	$arResult["DATA"][$key] = htmlspecialcharsEx($val);
endforeach;

$this->IncludeComponentTemplate();
?>