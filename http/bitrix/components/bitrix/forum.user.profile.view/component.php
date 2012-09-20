<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
if (!function_exists("ForumUrlExtractTmp"))
{
	function ForumUrlExtractTmp($s)
	{
		$x = 0;
		while (strpos(",}])>.", substr($s, -1, 1))!==false)
		{
			$s2 = substr($s, -1, 1);
			$s = substr($s, 0, strlen($s)-1);
		}
		return "<a href=\"".$s."\" target=\"_blank\">".$s."</a>".$s2;
	}
}
if (!function_exists("ForumNumberRusEnding"))
{
	function ForumNumberRusEnding($num)
	{
		if (LANGUAGE_ID == "ru")
		{
			if (strlen($num)>1 && substr($num, strlen($num)-2, 1)=="1")
			{
				return GetMessage("F_ENDING_OV");
			}
			else
			{
				$c = IntVal(substr($num, strlen($num)-1, 1));
				if ($c==0 || ($c>=5 && $c<=9))
					return GetMessage("F_ENDING_OV");
				elseif ($c==1)
					return "";
				else
					return GetMessage("F_ENDING_A");
			}
		}
		else
		{
			if (IntVal($num)>1)
				return "s";
			return "";
		}
	}	
}
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["UID"] = trim(strLen($arParams["UID"]) <= 0 ? $_REQUEST["UID"] : $arParams["UID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"profile" => "PAGE_NAME=profile&UID=#UID#",
			"pm_edit" => "PAGE_NAME=pm_edit&FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#",
			"message_send" => "PAGE_NAME=message_send&TYPE=#TYPE#&UID=#UID#",
			"subscr_list" => "PAGE_NAME=subscr_list",
			"user_post" => "PAGE_NAME=user_post&UID=#UID#&mode=#mode#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) && !empty($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
	$arParams["DATE_TIME_FORMAT"] = trim($arParams["DATE_TIME_FORMAT"]);
	$arParams["DATE_FORMAT"] = trim($arParams["DATE_FORMAT"]);
	if(strlen($arParams["DATE_TIME_FORMAT"])<=0)
		$arParams["DATE_TIME_FORMAT"] = $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL"));
	if(strlen($arParams["DATE_FORMAT"])<=0)
		$arParams["DATE_FORMAT"] = $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT"));
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
/********************************************************************
				/Input params
********************************************************************/
$parser = new textParser();
$parser->MaxStringLen = $arParams["WORD_LENGTH"];

$arResult["USER"] = array();
$arResult["FORUM_USER"] = array();
$bUserFound = false;
if (!empty($arParams["UID"]))
{
	$ar_res = false;
	$db_res = CUser::GetByID(intVal($arParams["UID"]));
	if (!($ar_res = $db_res->Fetch())):
		$db_res = CUser::GetByLogin($arParams["UID"]);
		$ar_res = $db_res->Fetch();
		$arParams["UID"] = intVal($ar_res["ID"]);
	endif;
	$arParams["UID"] = intval($arParams["UID"]);
	if ($ar_res)
	{
		$bUserFound = True;
		foreach ($ar_res as $key => $val):
			$arResult["USER"]["~".$key] = $val;
			if (is_string($val)):
				$arResult["USER"][$key] = $parser->wrap_long_words(htmlspecialcharsex(trim($val)));
			endif;
			if ($key == "PERSONAL_BIRTHDAY" && strLen($val) > 0):
				$arResult["USER"][$key."_FORMATED"] = CForumFormat::FormatDate($val, CLang::GetDateFormat("SHORT"), $arParams["DATE_FORMAT"]);
			endif;
		endforeach;
	}
}
if (!$bUserFound):
	CHTTP::SetStatus("404 Not Found");
	if (strLen($arParams["UID"]) <= 0):	
		ShowError(GetMessage("F_NO_UID"));
	else: 
		ShowError(str_replace("#UID#", htmlspecialcharsEx($arParams["UID"]), GetMessage("F_NO_DUSER")));
	endif;
	return false;
endif;

ForumSetLastVisit();

$arResult["FORUM_USER"] = CForumUser::GetByUSER_ID($arParams["UID"]);
$arResult["FORUM_USER"] = (empty($arResult["FORUM_USER"]) ? array() : $arResult["FORUM_USER"]);
foreach ($arResult["FORUM_USER"] as $key => $val):
	$arResult["FORUM_USER"]["~".$key] = $val;
	$arResult["FORUM_USER"][$key] = htmlspecialcharsEx($val);
	if (!is_array($val))
		$arResult["FORUM_USER"][$key] = $parser->wrap_long_words($arResult["FORUM_USER"][$key]);
endforeach;

/********************************************************************
				Default values
********************************************************************/
$strErrorMessage = "";
$strOKMessage = "";

$arResult["UID"] = $arParams["UID"];
$arResult["FID"] = intVal($_REQUEST["FID"]);
$arResult["TID"] = intVal($_REQUEST["TID"]);
$arResult["MID"] = intVal($_REQUEST["MID"]);
$arResult["IsAuthorized"] = $USER->IsAuthorized() ? "Y" : "N";
$arResult["IsAdmin"] = $USER->IsAdmin() ? "Y" : "N";
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = ($_REQUEST["result"] == "message_send" ? GetMessage("F_OK_MESSAGE_SEND") : "");
$arResult["FORUMS"] = array();

$arResult["SHOW_BACK_URL"] = (($arResult["FID"] > 0 || $arResult["TID"] > 0 || $arResult["MID"] > 0) ? "Y" : "N");
$arResult["SHOW_USER_INFO"] = "Y"; // out of date params
$arResult["SHOW_EDIT_PROFILE"] = ($USER->IsAuthorized() && ((intVal($USER->GetID()) == $arParams["UID"] && $USER->CanDoOperation('edit_own_profile')) || 
	$USER->IsAdmin()) ? "Y" : "N");
$arResult["SHOW_VOTES"] = ((COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y" && $USER->IsAuthorized()
	&& ($GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W" || intVal($USER->GetParam("USER_ID"))!=$arParams["UID"])) ? "Y" : "N");
$arResult["SHOW_RANK"] = 'Y';//(COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y" ? "Y" : "N");
 /******************************************************************/
$arResult["SHOW_ICQ"] = ((COption::GetOptionString("forum", "SHOW_ICQ_CONTACT", "N") != "Y") ? "N" : (($arParams["SEND_ICQ"] <= "A" || ($arParams["SEND_ICQ"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y"));
$arResult["SHOW_MAIL"] = $arParams["SHOW_MAIL"] = (($arParams["SEND_MAIL"] <= "A" || ($arParams["SEND_MAIL"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y");;
/******************************************************************/
$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"]));
$arResult["URL"] = array(
	"PROFILE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE"], array("UID" => $arParams["UID"])),
	"PROFILE_VIEW" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])),
	"~PROFILE_VIEW" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])),
	"USER_EMAIL" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"], array("UID" => $arParams["UID"], "TYPE"=>"mail")), 
	"USER_ICQ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"], array("UID" => $arParams["UID"], "TYPE"=>"icq")), 
	"USER_PM" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_EDIT"], 
		array("FID" => 1, "MID" => 0, "UID" => $arParams["UID"], "mode"=>"new")), 
	"TOPIC" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
		array("FID" => $arResult["FID"], "TID" => $arResult["TID"], "MID" => $arResult["MID"])),
	"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
		array("FID" => $arResult["FID"], "TID" => $arResult["TID"], "MID" => $arResult["MID"])),
	"USER_POSTS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], array("UID" => $arParams["UID"], "mode"=>"all")), 
	"USER_POSTS_MEMBER" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], array("UID" => $arParams["UID"], "mode"=>"lt")),
	"USER_POSTS_AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_USER_POST"], array("UID" => $arParams["UID"], "mode"=>"lta")), 
	"SUBSCRIBE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_SUBSCR_LIST"], array("UID" => $arParams["UID"])), 
	"~SUBSCRIBE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_SUBSCR_LIST"], array("UID" => $arParams["UID"])));
$arResult["URL"]["VOTE"] = $arResult["URL"]["PROFILE_VIEW"];
/************** For custom ****************************************/
$arResult["profile"] = $arResult["URL"]["PROFILE"];
$arResult["profile_view"] = $arResult["URL"]["PROFILE_VIEW"];
$arResult["message_mail"] = $arResult["URL"]["USER_EMAIL"];
$arResult["message_icq"] = $arResult["URL"]["USER_ICQ"];
$arResult["read"] = $arResult["URL"]["TOPIC"];
$arResult["message"] = $arResult["URL"]["MESSAGE"];
$arResult["pm_edit"] = $arResult["URL"]["USER_PM"];
$arResult["user_post_lta"] = $arResult["URL"]["USER_POSTS_AUTHOR"];
$arResult["user_post_lt"] = $arResult["URL"]["USER_POSTS_MEMBER"];
$arResult["user_post_all"] = $arResult["URL"]["USER_POSTS"];
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Votings ********************************************/
if ($arResult["SHOW_VOTES"] == "Y"):
	if ($_GET["VOTE_USER"] == "Y" && $USER->IsAuthorized() && check_bitrix_sessid()):
		ForumVote4User($arParams["UID"], $_GET["VOTES"], (strlen($_GET["CANCEL_VOTE"]) > 0 ? True : False), $strErrorMessage, $strOKMessage);
		if (empty($strErrorMessage)):
			LocalRedirect($arResult["URL"]["~PROFILE_VIEW"]);
		endif;
	endif;
	
	$strNotesText = "";
	$bCanVote = ($GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W");
	$bCanUnVote = False;
	$arUserRank = CForumUser::GetUserRank(intVal($USER->GetParam("USER_ID")));
	$arUserPoints = CForumUserPoints::GetByID(intVal($USER->GetParam("USER_ID")), $arParams["UID"]);
	if ($arUserPoints)
	{
		$bCanUnVote = True;
		$strNotesText .= str_replace("#POINTS#", $arUserPoints["POINTS"], str_replace("#END#", 
			ForumNumberRusEnding($arUserPoints["POINTS"]), GetMessage("F_ALREADY_VOTED1"))).". \n";
		if ($GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W")
		{
			$strNotesText .= GetMessage("F_ALREADY_VOTED_ADMIN");
		}
		elseif (intVal($arUserPoints["POINTS"]) < intVal($arUserRank["VOTES"]))
		{
			$bCanVote = True;
			$strNotesText .= str_replace("#POINTS#", (intVal($arUserRank["VOTES"])-intVal($arUserPoints["POINTS"])), str_replace("#END#", 
				ForumNumberRusEnding((intVal($arUserRank["VOTES"])-intVal($arUserPoints["POINTS"]))), GetMessage("F_ALREADY_VOTED3")));
		}
	}
	elseif (intVal($arUserRank["VOTES"]) > 0 || $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W")
	{
		
		$bCanVote = True;
		$strNotesText .= GetMessage("F_NOT_VOTED");
		if ($GLOBALS["APPLICATION"]->GetGroupRight("forum") < "W")
		{
			$strNotesText .= str_replace("#POINTS#", $arUserRank["VOTES"], str_replace("#END#", 
				ForumNumberRusEnding($arUserRank["VOTES"]), GetMessage("F_NOT_VOTED1"))).". \n";
		}
		else
		{
			$strNotesText .= GetMessage("F_ALREADY_VOTED_ADMIN");
		}
	}
	
	$arResult["bCanVote"] = $bCanVote;
	$arResult["bCanUnVote"] = $bCanUnVote;
	$arResult["titleVote"] = $strNotesText;
	$arResult["SHOW_VOTES"] = (strlen($strNotesText) > 0 || $bCanVote || $bCanUnVote ? "Y" : "N");
	if ($GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W" && $bCanVote)
		$arResult["VOTES"] = intVal($arUserRank["VOTES"]);
	if ($bCanUnVote):
		$arResult["VOTE_ACTION"] = "UNVOTE";
		$arResult["URL"]["VOTE"] = $APPLICATION->GetCurPageParam("CANCEL_VOTE=Y&VOTE_USER=Y&sessid=".bitrix_sessid(), array("sessid", "VOTE_USER", "VOTES", "CANCEL_VOTE"));
	else:
		$arResult["VOTE_ACTION"] = "VOTE";
		$arResult["URL"]["VOTE"] = $APPLICATION->GetCurPageParam("VOTE_USER=Y&sessid=".bitrix_sessid(), array("sessid", "VOTE_USER", "VOTES", "CANCEL_VOTE"));
	endif;
endif;
/*******************************************************************/
if (strLen($arResult["FORUM_USER"]["DATE_REG"]) > 0)
	$arResult["FORUM_USER"]["DATE_REG_FORMATED"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], 
		MakeTimeStamp($arResult["FORUM_USER"]["DATE_REG"], CSite::GetDateFormat()));
if (strLen($arResult["FORUM_USER"]["LAST_VISIT"]) > 0)
	$arResult["FORUM_USER"]["LAST_VISIT_FORMATED"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], 
		MakeTimeStamp($arResult["FORUM_USER"]["LAST_VISIT"], CSite::GetDateFormat()));

$arResult["~SHOW_NAME"] = trim($arResult["USER"]["~NAME"]." ".$arResult["USER"]["~LAST_NAME"]);
if (!($arResult["FORUM_USER"]["SHOW_NAME"] == "Y" && !empty($arResult["~SHOW_NAME"])))
	$arResult["~SHOW_NAME"] = $arResult["USER"]["~LOGIN"];
$arResult["SHOW_NAME"] = htmlspecialcharsEx($arResult["~SHOW_NAME"]);

$arResult["SHOW_EDIT_PROFILE_TITLE"] = (intVal($USER->GetID())!=$arParams["UID"]) ? GetMessage("F_EDIT_THIS_PROFILE") : GetMessage("F_EDIT_YOUR_PROFILE");
$arResult["SHOW_EDIT_PROFILE_TITLE_BOTTOM"] = ((intVal($USER->GetID())!=$arParams["UID"]) ? GetMessage("F_TO_CHANGE2") : GetMessage("F_TO_CHANGE3"))." ".GetMessage("F_TO_CHANGE4");
if (strlen($arResult["USER"]["PERSONAL_WWW"]) > 0)
{
	$arResult["USER"]["PERSONAL_WWW_FORMATED"] = $arResult["USER"]["PERSONAL_WWW"];
	$strBValueTmp = substr($arResult["USER"]["PERSONAL_WWW_FORMATED"], 0, 6);
	if ($strBValueTmp!="http:/" && $strBValueTmp!="https:" && $strBValueTmp!="ftp://")
		$arResult["USER"]["PERSONAL_WWW_FORMATED"] = "http://".$arResult["USER"]["PERSONAL_WWW_FORMATED"];
	$arResult["USER"]["PERSONAL_WWW"] = "<a href=\"".$arResult["USER"]["PERSONAL_WWW_FORMATED"]."\" target=\"_blank\">".$arResult["USER"]["PERSONAL_WWW_FORMATED"]."</a>";
}

if (strlen($arResult["USER"]["WORK_WWW"]) > 0)
{
	$arResult["USER"]["WORK_WWW_FORMATED"] = $arResult["USER"]["WORK_WWW"];
	$strBValueTmp = substr($arResult["USER"]["WORK_WWW_FORMATED"], 0, 6);
	if ($strBValueTmp!="http:/" && $strBValueTmp!="https:" && $strBValueTmp!="ftp://")
		$arResult["USER"]["WORK_WWW_FORMATED"] = "http://".$arResult["USER"]["WORK_WWW_FORMATED"];

	$arResult["USER"]["WORK_WWW"] = "<a href=\"".$arResult["USER"]["WORK_WWW_FORMATED"]."\" target=\"_blank\">".$arResult["USER"]["WORK_WWW_FORMATED"]."</a>";
}

if ($arResult["USER"]["PERSONAL_GENDER"]=="M")
	$arResult["USER"]["PERSONAL_GENDER"] = GetMessage("F_SEX_MALE");
elseif ($arResult["USER"]["PERSONAL_GENDER"]=="F")
	$arResult["USER"]["PERSONAL_GENDER"] = GetMessage("F_SEX_FEMALE");
	
$arResult["USER"]["PERSONAL_LOCATION"] = GetCountryByID($arResult["USER"]["PERSONAL_COUNTRY"]);
if (strlen($arResult["USER"]["PERSONAL_LOCATION"])>0 && strlen($arResult["USER"]["PERSONAL_CITY"])>0)
	$arResult["USER"]["PERSONAL_LOCATION"] .= ", ";
$arResult["USER"]["PERSONAL_LOCATION"] .= $arResult["USER"]["PERSONAL_CITY"];

$arResult["USER"]["WORK_LOCATION"] = GetCountryByID($arResult["USER"]["WORK_COUNTRY"]);
if (strlen($arResult["USER"]["WORK_LOCATION"])>0 && strlen($arResult["USER"]["WORK_CITY"])>0)
	$arResult["USER"]["WORK_LOCATION"] .= ", ";
$arResult["USER"]["WORK_LOCATION"] .= $arResult["USER"]["WORK_CITY"];

$arAllow = array("HTML" => "N", "ANCHOR" => "Y","BIU" => "Y", "IMG" => "Y", "VIDEO" => "Y", "LIST" => "Y", "QUOTE" => "Y", 
	"CODE" => "Y",  "FONT" => "Y", "SMILES" => "N", "NL2BR" => "Y");
$arResult["FORUM_USER"]["INTERESTS"] = $parser->convert($arResult["FORUM_USER"]["INTERESTS"], $arAllow);

$arResult["FORUM_USER"]["AVATAR"] = "";
if (strlen($arResult["FORUM_USER"]["~AVATAR"]) > 0):
	$arResult["FORUM_USER"]["AVATAR_FILE"] = CFile::GetFileArray($arResult["FORUM_USER"]["~AVATAR"]);
	if ($arResult["FORUM_USER"]["AVATAR_FILE"] !== false)
		$arResult["FORUM_USER"]["AVATAR"] = CFile::ShowImage($arResult["FORUM_USER"]["AVATAR_FILE"]["SRC"], 
			COption::GetOptionString("forum", "avatar_max_width", 90), COption::GetOptionString("forum", "avatar_max_height", 90), "border=0", "", true);
endif;
$arResult["USER"]["PERSONAL_PHOTO"] = "";
if (strlen($arResult["USER"]["~PERSONAL_PHOTO"])>0):
	$arResult["USER"]["PERSONAL_PHOTO_FILE"] = CFile::GetFileArray($arResult["USER"]["~PERSONAL_PHOTO"]);
	if ($arResult["USER"]["PERSONAL_PHOTO_FILE"] !== false)
		$arResult["USER"]["PERSONAL_PHOTO"] = CFile::ShowImage($arResult["USER"]["PERSONAL_PHOTO_FILE"]["SRC"], 200, 200, "border=0 alt=\"\"", "", true);
endif;
/************** Getting User rank **********************************/
$arResult["USER_RANK"] = "";
$arResult["USER_RANK_CODE"] = "";
$arFilter = array();
if ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N" || $GLOBALS["APPLICATION"]->GetGroupRight("forum") < "W")
	$arFilter["LID"] = SITE_ID;
if (!empty($arParams["FID_RANGE"]))
	$arFilter["@ID"] = $arParams["FID_RANGE"];
if ($GLOBALS["APPLICATION"]->GetGroupRight("forum") < "W"):
	$arFilter["PERMS"] = array($USER->GetGroups(), 'A'); 
	$arFilter["ACTIVE"] = "Y";
endif;
$db_res = CForumNew::GetList(array(), $arFilter);
if ($db_res && ($res = $db_res->GetNext())):
	$arUserGroup = CUser::GetUserGroup($arParams["UID"]);
	$arUserPerm = array();
	do 
	{
		$arResult["FORUMS"][$res["ID"]] = $res;
		$arUserPerm[] = CForumNew::GetUserPermission($res["ID"], $arUserGroup);
	}while ($res = $db_res->GetNext());
	rsort($arUserPerm);
	
	if ($arUserPerm[0] == "Q"): 
		$arResult["USER_RANK"] = $GLOBALS["FORUM_STATUS_NAME"]["moderator"];
		$arResult["USER_RANK_CODE"] = 'moderator';
	elseif ($arUserPerm[0] == "U"): 
		$arResult["USER_RANK"] = $GLOBALS["FORUM_STATUS_NAME"]["editor"];
		$arResult["USER_RANK_CODE"] = 'editor';
	elseif ($arUserPerm[0] == "Y"): 
		$arResult["USER_RANK"] = $GLOBALS["FORUM_STATUS_NAME"]["administrator"];
		$arResult["USER_RANK_CODE"] = 'administrator';
	endif;
endif;
if ($arResult["SHOW_RANK"] == "Y"):
	$arRank = CForumUser::GetUserRank($arParams["UID"], LANGUAGE_ID);
	if (empty($arResult["USER_RANK"])):
		$arResult["USER_RANK"] = $arRank["NAME"];
		$arResult["USER_RANK_CODE"] = $arRank["CODE"];
	endif;
	$arRank["NAME"] = $arResult["USER_RANK"];
	$arResult["arRank"] = $arRank;
	$arResult["SHOW_POINTS"] = "N";
	if ($USER->IsAuthorized() && ($GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W" || intVal($USER->GetParam("USER_ID"))==$arParams["UID"]))
	{
		$arResult["SHOW_POINTS"] = "Y";
		$arResult["USER_POINTS"] = (intVal($arRank["VOTES"])>0 ? intVal($arRank["VOTES"]) : GetMessage("F_NO_VOTES"));
	}
endif;
if (empty($arResult["USER_RANK"])):
	$arResult["USER_RANK"] = $GLOBALS["FORUM_STATUS_NAME"]["user"];
	$arResult["USER_RANK_CODE"] = 'user';
endif;
/*******************************************************************/
$arResult["arTopic"] = "N";
if (!empty($arResult["FORUMS"])):
	$arFilter = array("AUTHOR_ID" => $arParams["UID"], "@FORUM_ID" => array_keys($arResult["FORUMS"]));
	$db_res = CForumUser::UserAddInfo(array("LAST_POST"=>"DESC"), $arFilter, "topics");
	if ($db_res && $res = $db_res->GetNext())
	{
		$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
		$res["DESCRIPTION"] = $parser->wrap_long_words($res["DESCRIPTION"]);
		$res["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat()));
		$arResult["arTopic"] = $res;
		$arResult["arTopic"]["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => intVal($res["LAST_POST"])))."#message".intVal($res["LAST_POST"]);
	}
endif;
/************** User properties ************************************/
$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
if (!empty($arParams["USER_PROPERTY"]))
{
	$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", $arParams["UID"], LANGUAGE_ID);
	if (count($arParams["USER_PROPERTY"]) > 0)
	{
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			if (!in_array($FIELD_NAME, $arParams["USER_PROPERTY"]))
				continue;
			$arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
			$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
			$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
			$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
		}
	}
	if (!empty($arResult["USER_PROPERTIES"]["DATA"]))
		$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
}
/*******************************************************************/
$arResult["ERROR_MESSAGE"] .= $strErrorMessage;
$arResult["OK_MESSAGE"] .= $strOKMessage;
/*******************************************************************/
foreach ($arResult["USER"] as $key => $val):
	if (substr($key, 0, 1) == "~")
		$arResult["~f_".substr($key, 1)] = $val;
	else
		$arResult["f_".$key] = $val;
endforeach;
foreach ($arResult["FORUM_USER"] as $key => $val):
	if (substr($key, 0, 1) == "~")
		$arResult["~fu_".substr($key, 1)] = $val;
	else
		$arResult["fu_".$key] = $val;
endforeach;
/********************************************************************
				Data
********************************************************************/
if ($arParams["SET_NAVIGATION"] != "N")
	$APPLICATION->AddChainItem($arResult["~SHOW_NAME"]);
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle($arResult["SHOW_NAME"]);
/*******************************************************************/
	$this->IncludeComponentTemplate();
?>
