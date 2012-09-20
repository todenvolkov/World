<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (is_set($arParams, "USE_RSS") && $arParams["USE_RSS"] == "N"): // out-of-date params
	return 0;
endif;
if (!function_exists("__create_uuid"))
{
	function __create_uuid($params)
	{
		$uuid = md5($params);
		return substr($uuid,  0, 8).'-'.
			substr($uuid,  8, 4).'-'.
			substr($uuid, 12, 4).'-'.
			substr($uuid, 16, 4).'-'.
			substr($uuid, 20);
	}
}

$arResult["TYPE_RSS"] = array("RSS1" => "RSS .92", "RSS2" => "RSS 2.0", "ATOM" => "Atom .3");
$arResult["FORUMS"] = array();
$arResult["TOPIC"] = array();
$arResult["SERVER_NAME"] = (defined("SITE_SERVER_NAME") && strLen(SITE_SERVER_NAME) > 0) ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name");
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["TYPE_RANGE"] = (is_array($arParams["TYPE_RANGE"]) ? $arParams["TYPE_RANGE"] : array("RSS1", "RSS2", "ATOM"));
	$arParams["TYPE_RANGE"] = array_intersect($arParams["TYPE_RANGE"], array_keys($arResult["TYPE_RSS"]));
	$arParams["TYPE_DEFAULT"] = (in_array($arParams["TYPE_DEFAULT"], $arParams["TYPE_RANGE"]) ? $arParams["TYPE_DEFAULT"] : $arParams["TYPE_RANGE"][0]);
	$arParams["TYPE"] = (strToUpper($arParams["TYPE"]) == "DEFAULT" ? $arParams["TYPE_DEFAULT"] : $arParams["TYPE"]);
	$arParams["TYPE"] = strToUpper(!empty($arResult["TYPE_RSS"][strToUpper($arParams["TYPE"])]) ? $arParams["TYPE"] : false);
	
	$arParams["FID_RANGE"] = (is_array($arParams["FID_RANGE"]) ? $arParams["FID_RANGE"] : array());

	$arParams["MODE"] = (in_array(strToLower($arParams["MODE"]), array("forum", "topic", "link")) ? strToLower($arParams["MODE"]) : "link");
	$arParams["MODE_DATA"] = trim($arParams["MODE_DATA"]);
	if (empty($arParams["MODE_DATA"]))
		$arParams["MODE_DATA"] = ($arParams["MODE"] == "topic" ? "topic" : "forum");
	else 
		$arParams["MODE_DATA"] = ($arParams["MODE_DATA"] == "topic" ? "topic" : "forum");
	$arParams["MODE"] = ($arParams["MODE"] == "link" || $arParams["TYPE"] == false ? "link" : "rss");
	$arParams["IID"] = intVal(intVal($arParams["IID"]) <= 0 ? $_REQUEST["IID"] : $arParams["IID"]);
	$arParams["FID"] = ($arParams["MODE_DATA"] == "forum" ? $arParams["IID"] : 0);
	$arParams["TID"] = ($arParams["MODE_DATA"] == "topic" ? $arParams["IID"] : 0);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"list" => "PAGE_NAME=list&FID=#FID#",
		"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
		"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
		"rss" => "PAGE_NAME=rss&TYPE=#TYPE#&MODE=#MODE#&IID=#IID#");
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
	$arParams["COUNT"] = intVal(intVal($arParams["COUNT"]) > 0 ? $arParams["COUNT"] : ($arParams["MODE_DATA"] == "forum" ? 
		COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10") : COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10")));
	$arParams["COUNT"] = ($arParams["COUNT"] > 0 ? $arParams["COUNT"] : 10);
	$arParams["MAX_FILE_SIZE"] = (intVal($arParams["MAX_FILE_SIZE"]) <= 0 ? 10*1024*1024 : intVal($arParams["MAX_FILE_SIZE"])*1024*1024);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["DESIGN_MODE"] = ($GLOBALS["APPLICATION"]->GetShowIncludeAreas() && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin() ? "Y" : "N");
	$arParams["TEMPLATES_TITLE_FORUMS"] = ($arParams["TEMPLATES_TITLE_FORUMS"] ? $arParams["TEMPLATES_TITLE_FORUMS"] : GetMessage("F_TEMPLATES_TITLE_FORUMS"));
	$arParams["TEMPLATES_TITLE_FORUM"] = ($arParams["TEMPLATES_TITLE_FORUM"] ? $arParams["TEMPLATES_TITLE_FORUM"] : GetMessage("F_TEMPLATES_TITLE_FORUM"));
	$arParams["TEMPLATES_TITLE_TOPIC"] = ($arParams["TEMPLATES_TITLE_TOPIC"] ? $arParams["TEMPLATES_TITLE_TOPIC"] : GetMessage("F_TEMPLATES_TITLE_TOPIC"));
	
	$arParams["TEMPLATES_DESCRIPTION_FORUMS"] = ($arParams["TEMPLATES_DESCRIPTION_FORUMS"] ? $arParams["TEMPLATES_DESCRIPTION_FORUMS"] : GetMessage("F_TEMPLATES_DESCRIPTION_FORUMS"));
	$arParams["TEMPLATES_DESCRIPTION_FORUM"] = ($arParams["TEMPLATES_DESCRIPTION_FORUM"] ? $arParams["TEMPLATES_DESCRIPTION_FORUM"] : GetMessage("F_TEMPLATES_DESCRIPTION_FORUM"));
	$arParams["TEMPLATES_DESCRIPTION_TOPIC"] = ($arParams["TEMPLATES_DESCRIPTION_TOPIC"] ? $arParams["TEMPLATES_DESCRIPTION_TOPIC"] : GetMessage("F_TEMPLATES_DESCRIPTION_TOPIC"));
	
/***************** CACHE *******************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
if (empty($arParams["TYPE_RANGE"])):
	ShowError(GetMessage("F_EMPTY_TYPE"));
	return 0;
else:
	$arFilter = (!empty($arParams["FID_RANGE"]) ? array("@ID" => $arParams["FID_RANGE"]) : array());
	$arFilter["LID"] = SITE_ID;
    if (!$GLOBALS['USER']->IsAdmin())
        $arFilter["PERMS"] = array($USER->GetGroups(), 'A');
	$arFilter["ACTIVE"] = "Y";
	$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
	if ($db_res && ($res = $db_res->Fetch()))
	{
		do 
		{
			foreach ($res as $key => $val):
				$res["~".$key] = $val;
				$res[$key] = htmlspecialchars($val);
			endforeach;
			$res["ALLOW"] = array(
				"HTML" => $res["ALLOW_HTML"],
				"ANCHOR" => $res["ALLOW_ANCHOR"],
				"BIU" => $res["ALLOW_BIU"],
				"IMG" => $res["ALLOW_IMG"],
				"VIDEO" => $res["ALLOW_VIDEO"],
				"LIST" => $res["ALLOW_LIST"],
				"QUOTE" => $res["ALLOW_QUOTE"],
				"CODE" => $res["ALLOW_CODE"],
				"FONT" => $res["ALLOW_FONT"],
				"SMILES" => $res["ALLOW_SMILES"],
				"UPLOAD" => $res["ALLOW_UPLOAD"],
				"NL2BR" => $res["ALLOW_NL2BR"]);
			$res["~FORUM_DESCRIPTION"] = $res["~DESCRIPTION"];
			$res["FORUM_DESCRIPTION"] = $res["DESCRIPTION"];
			$res["~FORUM_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], array("FID" => $res["ID"]));
			$res["FORUM_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"]));
			$res["~URL"] = "http://".$arResult["SERVER_NAME"].$res["~FORUM_LINK"];
			$res["URL"] = "http://".htmlspecialcharsEx($arResult["SERVER_NAME"]).$res["FORUM_LINK"];
			$arResult["FORUMS"][$res["ID"]] = $res;
		}while ($res = $db_res->Fetch());
	}
	if (empty($arResult["FORUMS"])):
		ShowError(GetMessage("F_EMPTY_FORUMS"));
		CHTTP::SetStatus("404 Not Found");
		return false;
	elseif ($arParams["MODE_DATA"] == "topic" && $arParams["TID"] <= 0):
		ShowError(GetMessage("F_EMPTY_TOPIC_ID"));
		CHTTP::SetStatus("404 Not Found");
		return false;
	elseif ($arParams["MODE_DATA"] == "topic"):
		$db_res = CForumTopic::GetList(array(), array("ID" => $arParams["TID"]));
		if (!($db_res && ($res = $db_res->Fetch()))):
			ShowError(GetMessage("F_EMPTY_TOPIC"));
			CHTTP::SetStatus("404 Not Found");
			return false;
		elseif (empty($arResult["FORUMS"][$res["FORUM_ID"]])):
			ShowError(GetMessage("F_EMPTY_TOPIC"));
			CHTTP::SetStatus("404 Not Found");
			return false;
		endif;
		foreach ($res as $key => $val):
			$res["~".$key] = $val;
			$res[$key] = htmlspecialchars($val);
		endforeach;
		$arResult["TOPIC"] = $res;
	endif;
endif;
if (empty($arResult["FORUMS"])):
	return false;
elseif ($arParams["MODE_DATA"] == "forum" && $arParams["FID"] > 0 && empty($arResult["FORUMS"][$arParams["FID"]])):
	if ($arParams["MODE"] != "link"):
		ShowError(GetMessage("F_ERR_BAD_FORUM"));
		CHTTP::SetStatus("404 Not Found");
	endif;
	return false;
elseif ($arParams["MODE_DATA"] == "topic" && $arParams["TID"] > 0 && empty($arResult["FORUMS"][$arResult["TOPIC"]["FORUM_ID"]])):
	if ($arParams["MODE"] != "link"):
		ShowError(GetMessage("F_ERR_BAD_FORUM"));
		CHTTP::SetStatus("404 Not Found");
	endif;
	return false;
endif;
/********************************************************************
				Data № 1
********************************************************************/
if ($arParams["MODE"] == "link"):
	$arResult["rss_link"] = array();
	foreach ($arParams["TYPE_RANGE"] as $key)
	{
		$rss = strToLower($key);
		$arResult["rss_link"][$rss] = array(
			"type" => $rss, 
			"name" => $arResult["TYPE_RSS"][$key], 
			"link" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], 
				array("TYPE" => $rss, "MODE" => $arParams["MODE_DATA"], "IID" => $arParams["IID"])));
	}
	$this->IncludeComponentTemplate();
	return false;
endif;
/********************************************************************
				/Data № 1
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$arFilter = array();
$arItems = array();
$arParams["FID"] = (!empty($arResult["FORUMS"][$arParams["FID"]]) ? $arParams["FID"] : 0);
$arResult["LANGUAGE_ID"] = LANGUAGE_ID;
$arResult["CHARSET"] = (defined("SITE_CHARSET") && strLen(SITE_CHARSET) > 0) ? SITE_CHARSET : "windows-1251";
$arResult["NOW"] = ($arParams["TYPE"] != "ATOM") ? date("r") : date("Y-m-d\TH:i:s").substr(date("O"), 0, 3).":".substr(date("O"), -2, 2);
$arResult["TEMPLATE_ELEMENTS"] = array("AUTHOR_NAME", "AUTHOR_LINK", "SIGNATURE", "DATE_REG", "AVATAR", "POST_MESSAGE", "POST_LINK", 
	"POST_DATE", "ATTACH_IMG", "TITLE", "TOPIC_LINK", 
	"TOPIC_DATE", "TOPIC_DESCRIPTION", "NAME", "FORUM_LINK", "FORUM_DESCRIPTION");
$parser = new textParser(LANGUAGE_ID);
$parser->MaxStringLen = 0; 
$arResult["SITE"] = array();
$db_res = CSite::GetByID(SITE_ID);
if ($db_res && $res = $db_res->GetNext())
	$arResult["SITE"] = $res; 

$arResult["~TITLE"] = $arParams["TEMPLATES_TITLE_FORUMS"]; 
$arResult["~DESCRIPTION"] = $arParams["TEMPLATES_DESCRIPTION_FORUMS"]; 
if ($arParams["MODE_DATA"] == "forum" && $arParams["IID"] > 0)
{
	$arResult["~TITLE"] = $arParams["TEMPLATES_TITLE_FORUM"]; 
	$arResult["~DESCRIPTION"] = $arParams["TEMPLATES_DESCRIPTION_FORUM"]; 
}
elseif ($arParams["MODE_DATA"] == "topic")
{
	$arResult["~TITLE"] = $arParams["TEMPLATES_TITLE_TOPIC"]; 
	$arResult["~DESCRIPTION"] = $arParams["TEMPLATES_DESCRIPTION_TOPIC"]; 
}
$arResult["~TITLE"] = str_replace(
	array("#FORUM_TITLE#", "#FORUM_DESCRIPTION#", "#TOPIC_TITLE#", "#TOPIC_DESCRIPTION#", "#SITE_NAME#", "#SERVER_NAME#"), 
	array($arResult["FORUMS"][$arParams["IID"]]["~NAME"], $arResult["FORUMS"][$arParams["IID"]]["~DESCRIPTION"], 
		$arResult["TOPIC"]["~TITLE"], $arResult["TOPIC"]["~DESCRIPTION"], 
		$arResult["SITE"]["SITE_NAME"], $arResult["SERVER_NAME"]), $arResult["~TITLE"]);
$arResult["~DESCRIPTION"] = str_replace(
	array("#FORUM_TITLE#", "#FORUM_DESCRIPTION#", "#TOPIC_TITLE#", "#TOPIC_DESCRIPTION#", "#SITE_NAME#", "#SERVER_NAME#"), 
	array($arResult["FORUMS"][$arParams["IID"]]["~NAME"], $arResult["FORUMS"][$arParams["IID"]]["~DESCRIPTION"], 
		$arResult["TOPIC"]["~TITLE"], $arResult["TOPIC"]["~DESCRIPTION"], 
		$arResult["SITE"]["SITE_NAME"], $arResult["SERVER_NAME"]), $arResult["~DESCRIPTION"]);

$arResult["TITLE"] = htmlspecialcharsEx($arResult["~TITLE"]);
$arResult["DESCRIPTION"] = htmlspecialcharsEx($arResult["~DESCRIPTION"]);

$arResult["URL"] = array(
	"~ALTERNATE" => "http://".$arResult["SERVER_NAME"], 
	"ALTERNATE" => htmlspecialchars("http://".$arResult["SERVER_NAME"]), 
	"~REAL" => "http://".$arResult["SERVER_NAME"].$APPLICATION->GetCurPageParam(), 
	"REAL" => htmlspecialchars("http://".$arResult["SERVER_NAME"].$APPLICATION->GetCurPageParam()));

$arResult["MESSAGE_LIST"] = array();
/********************************************************************
				/Default values
********************************************************************/
/********************************************************************
				Data № 2
********************************************************************/
$cache_id_array = array("MODE" => $arParams["MODE_DATA"], "IID" => $arParams["IID"], "TYPE" => $arParams["TYPE"], "COUNT" => $arParams["COUNT"], 
	"FID_RANGE" => $arParams["FID_RANGE"], "USER_GROUP" => $GLOBALS["USER"]->GetUserGroupArray(), "LANGUAGE" => $arResult["LANGUAGE_ID"], 
	"SERVER_NAME" => $arResult["SERVER_NAME"], "CHARSET" => $arResult["CHARSET"]);

if ($arParams["DESIGN_MODE"] != "Y")
{
	$APPLICATION->RestartBuffer();
	header("Content-Type: text/xml");
	header("Pragma: no-cache");
}
if($this->StartResultCache($arParams["CACHE_TIME"], array($cache_id_array, $arParams["DESIGN_MODE"]), "/".SITE_ID."/forum/rss/".$arParams["TYPE"]."/".$arParams["MODE_DATA"]."/"))
{
	$arFilter = array(
		"TOPIC_ID" => $arParams["TID"],
		"APPROVED" => "Y",
		"@FORUM_ID" => implode(",", array_keys($arResult["FORUMS"])), 
		"TOPIC" => "GET_TOPIC_INFO");
	if ($arParams["MODE_DATA"] != "topic")
	{
		$arFilter = array();
		if (intVal($arParams["FID"]) > 0)
		{
			$arFilter["FORUM_ID"] = $arParams["FID"]; 
		}
		else 
		{
			$arFilter["@FORUM_ID"] = implode(",", array_keys($arResult["FORUMS"])); 
		}
		$arFilter["APPROVED"] = "Y";
		$arFilter["NEW_TOPIC"] = "Y";
		$arFilter["TOPIC"] = "GET_TOPIC_INFO";
	}
	$db_res = CForumMessage::GetListEx(array("ID" => "DESC"), $arFilter, 0, $arParams["COUNT"]);
	if ($db_res && ($res = $db_res->Fetch()))
	{
		do 
		{
			foreach ($res as $key => $val):
				$res["~".$key] = $val;
				$res[$key] = htmlspecialchars($val);
			endforeach;
	/************** Message info ***************************************/
		// data
			$arDate = ParseDateTime($res["POST_DATE"], false);
			$date = date("r", mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
			if ($arParams["TYPE"] == "ATOM")
			{
				$timeISO = mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]);
				$date = date("Y-m-d\TH:i:s", $timeISO).substr(date("O", $timeISO), 0, 3).":".substr(date("O", $timeISO), -2, 2);
			}
			$res["POST_DATE"] = $date;
			$res["POST_DATE_FORMATED"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["~POST_DATE"], CSite::GetDateFormat()));
		// text
		$arAllow = $arResult["FORUMS"][$res["FORUM_ID"]]["ALLOW"];
		$arAllow["SMILES"] = ($res["USE_SMILES"] == "Y" ? $arResult["FORUM"]["ALLOW_SMILES"] : "N");
		$res["POST_MESSAGE"] = (COption::GetOptionString("forum", "FILTER", "Y")=="Y" ? $res["~POST_MESSAGE_FILTER"] : $res["~POST_MESSAGE"]);
		$res["POST_MESSAGE"] = $parser->convert_to_rss($res["POST_MESSAGE"], array(), $arAllow);
		// attach
		$res["ATTACH_IMG"] = ""; $res["FILES"] = array();
		$res["~ATTACH_FILE"] = array(); $res["ATTACH_FILE"] = array();
	/************** Message info/***************************************/
	/************** Author info ****************************************/
		// Avatar
		if (strLen($res["AVATAR"]) > 0):
			$res["~AVATAR"] = array("ID" => $res["AVATAR"]);
			$res["~AVATAR"]["FILE"] = CFile::GetFileArray($res["~AVATAR"]["ID"]);
			$res["AVATAR"] = CFile::ShowImage($res["~AVATAR"]["FILE"]["SRC"], COption::GetOptionString("forum", "avatar_max_width", 90), 
				COption::GetOptionString("forum", "avatar_max_height", 90), "border=\"0\"", "", true);
		endif;
		// data
		$res["DATE_REG"] = CForumFormat::DateFormat($arParams["DATE_FORMAT"], MakeTimeStamp($res["DATE_REG"], CSite::GetDateFormat()));
		// Another data
		if (strLen($res["SIGNATURE"]) > 0)
		{
			$arAllow["SMILES"] = "N";
			$res["SIGNATURE"] = $parser->convert_to_rss($res["~SIGNATURE"], $arAllow);
		}
	/************** Author info/****************************************/
			$res["~AUTHOR_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], 
				array("UID" => intVal($res["AUTHOR_ID"])));
			$res["AUTHOR_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], 
				array("UID" => intVal($res["AUTHOR_ID"])));
			$res["~POST_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
				array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => $res["ID"]));
			$res["POST_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => $res["ID"]));
				
			$res["~AUTHOR_URL"] = "http://".$arResult["SERVER_NAME"].$res["~AUTHOR_LINK"];
			$res["AUTHOR_URL"] = "http://".htmlspecialcharsEx($arResult["SERVER_NAME"]).$res["AUTHOR_LINK"];
			$res["~URL"] = "http://".$arResult["SERVER_NAME"].$res["~POST_LINK"];
			$res["URL"] = "http://".htmlspecialcharsEx($arResult["SERVER_NAME"]).$res["POST_LINK"];

			$res["~URL_RSS"] = "http://".$arResult["SERVER_NAME"].CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"], 
				array("TYPE" => strToLower($arParams["TYPE"]), "MODE" => "topic", "IID" => $res["TOPIC_ID"]));
			$res["URL_RSS"] = "http://".htmlspecialcharsEx($arResult["SERVER_NAME"]).
				CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], 
				array("TYPE" => strToLower($arParams["TYPE"]), "MODE" => "topic", "IID" => $res["TOPIC_ID"]));
			$res["UUID"] = __create_uuid($res["~URL"]);
			
			// TOPIC DATA
			$arDate = ParseDateTime($res["START_DATE"], false);
			$date = date("r", mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
			if ($arParams["TYPE"] == "ATOM")
			{
				$timeISO = mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]);
				$date = date("Y-m-d\TH:i:s", $timeISO).substr(date("O", $timeISO), 0, 3).":".substr(date("O", $timeISO), -2, 2);
			}
			$topic = array(
				"ID" => $res["TOPIC_ID"], 
				"TITLE" => $res["TITLE"], 
				"~TITLE" => $res["~TITLE"], 
				"DESCRIPTION" => $res["TOPIC_DESCRIPTION"], 
				"~DESCRIPTION" => $res["~TOPIC_DESCRIPTION"], 
				"TOPIC_DESCRIPTION" => $res["TOPIC_DESCRIPTION"], 
				"~TOPIC_DESCRIPTION" => $res["~TOPIC_DESCRIPTION"], 
				"START_DATE" => $date, 
				"~START_DATE" => $res["~START_DATE"], 
				"START_DATE_FORMATED" => CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["~START_DATE"], 
					CSite::GetDateFormat())), 
				"AUTHOR_NAME" => $res["USER_START_NAME"], 
				"~AUTHOR_NAME" => $res["~USER_START_NAME"], 
				"AUTHOR_ID" => $res["USER_START_ID"], 
				"~AUTHOR_ID" => $res["~USER_START_ID"], 
				"~AUTHOR_LINK" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], 
					array("UID" => intVal($res["~USER_START_ID"]))), 
				"AUTHOR_LINK" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], 
					array("UID" => intVal($res["~USER_START_ID"]))), 
				"~TOPIC_LINK" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_READ"], 
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => "s")), 
				"TOPIC_LINK" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
					array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "MID" => "s")), 
				"MESSAGES" => array());
				
			$topic["~AUTHOR_URL"] = "http://".$arResult["SERVER_NAME"].$topic["~AUTHOR_LINK"];
			$topic["AUTHOR_URL"] = "http://".htmlspecialcharsEx($arResult["SERVER_NAME"]).$topic["AUTHOR_LINK"];
			$topic["~URL"] = "http://".$arResult["SERVER_NAME"].$topic["~TOPIC_LINK"];
			$topic["URL"] = "http://".htmlspecialcharsEx($arResult["SERVER_NAME"]).$topic["TOPIC_LINK"];

			if (empty($arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]))
				$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]] = $topic;
			
			unset($res["TITLE"]);
			unset($res["DESCRIPTION"]);
			if (!empty($arParams["TEMPLATE"]))
			{
				$text = $arParams["TEMPLATE"];
				foreach ($arParams["TEMPLATE_ELEMENTS"] as $element)
				{
					$replace = array($arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]][$element], 
						$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["~".$element]);
					if (strLen($res[$element]) > 0)
						$replace = array($res[$element], $res["~".$element]);
					$text = str_replace(array("#".$res."#", "#~".$res."#"), $replace, $text);
				}
				$res["TEMPLATE"] = $text;
			}
			$arResult["MESSAGE_LIST"][$res["ID"]] = $res;
			$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["ID"]] = $res;
		}while ($res = $db_res->Fetch());
	}
	if (is_array($arItems) && (count($arItems) > 0))
	{
		foreach ($arItems as $key => $val)
			$arItems[$key] = array_merge($arResult["FORUMS"][$key], $val);
	}
/************** Attach files ***************************************/
if (!empty($arResult["MESSAGE_LIST"]))
{
	$arFilter = array("@FILE_MESSAGE_ID" => array_keys($arResult["MESSAGE_LIST"]));
	$src = "http://".str_replace("//", "/", $arResult["SERVER_NAME"]."/".BX_ROOT."/components/bitrix/forum.interface/show_file.php?fid=#FILE_ID#");
	$db_files = CForumFiles::GetList(array("MESSAGE_ID" => "ASC"), $arFilter);
	if ($db_files && $res = $db_files->Fetch())
	{
		do 
		{
			if (!in_array($arResult["FORUMS"][$res["FORUM_ID"]]["ALLOW_UPLOAD"], array("Y", "F", "A")))
				continue;
			$res["SRC"] = str_replace("#FILE_ID#", $res["FILE_ID"], $src);
			if ($res["FILE_SIZE"] <= $arParams["MAX_FILE_SIZE"] && strToLower(subStr($res["CONTENT_TYPE"], 0, 6)) == "image/")
				$res["HTML"] = '<img src="'.$res["SRC"]."\" width='".$res["WIDTH"]."' height='".$res["HEIGHT"]." alt='' />";
			else
				$res["HTML"] = ' [ <a href="'.$res["SRC"].'">'.GetMessage("FILE_DOWNLOAD").'</a> ] ';
			
			if ($arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["~ATTACH_IMG"] == $res["FILE_ID"])
			{
			// attach for custom 
				$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["ATTACH_IMG"] = $res["HTML"];
				$res["ID"] = $res["FILE_ID"];
				$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["~ATTACH_IMG"] = $res;
			}
			$arItems[$res["FORUM_ID"]]["TOPICS"][$res["TOPIC_ID"]]["MESSAGES"][$res["MESSAGE_ID"]]["FILES"][$res["FILE_ID"]] = $res;
		}while ($res = $db_files->Fetch());
	}
}
/************** Message List/***************************************/
$arResult["DATA"] = $arItems;
$arParams["TYPE"] = strToLower($arParams["TYPE"]);
if ($arParams["DESIGN_MODE"] != "Y")
{
	$this->IncludeComponentTemplate();
}
else
{
	ob_start();
	$this->IncludeComponentTemplate();
	$contents = ob_get_contents();
	ob_end_clean();
	echo "<pre>",htmlspecialcharsEx($contents),"</pre>";
}
}
if ($arParams["DESIGN_MODE"] != "Y")
{
	die();
	return 0;
}
/********************************************************************
				/Data № 2
********************************************************************/

?>
