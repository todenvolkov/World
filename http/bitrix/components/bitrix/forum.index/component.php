<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["GID"] = intVal($arParams["GID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"forums" => "PAGE_NAME=forums&GID=#GID#",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"message_appr" => "PAGE_NAME=message_appr&FID=#FID#&TID=#TID#", 
			"rss" => "PAGE_NAME=rss&TYPE=#TYPE#&MODE=#MODE#&IID=#IID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["FORUMS_PER_PAGE"] = intVal(intVal($arParams["FORUMS_PER_PAGE"]) > 0 ? $arParams["FORUMS_PER_PAGE"] : COption::GetOptionString("forum", "FORUMS_PER_PAGE", "10"));
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["FID_RANGE"] = (is_array($arParams["FID"]) && !empty($arParams["FID"]) ? $arParams["FID"] : array());
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
	
	$arParams["SHOW_FORUM_ANOTHER_SITE"] = ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "Y" ? "Y" : "N");
	$arParams["SHOW_FORUMS_LIST"] = ($arParams["SHOW_FORUMS_LIST"] == "Y" ? "Y" : "N");
	$arParams["MINIMIZE_SQL"] = ($arParams["MINIMIZE_SQL"] == "Y" ? "Y" : "N");
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

/********************************************************************
				Default values
********************************************************************/
$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
$arResult["GROUP"] = $arResult["GROUPS"][$arParams["GID"]];
if (empty($arResult["GROUP"]))
	$arParams["GID"] = 0; 
$arResult["GROUP_NAVIGATION"] = array();
$arResult["USER"] = array(
	"CAN_MODERATE" => "N", 
	"HIDDEN_GROUPS" => array(), 
	"HIDDEN_FORUMS" => array());
	
$arResult["URL"] = array(
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_INDEX"], array()), 
	"~INDEX" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_INDEX"], array()), 
	"RSS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], 
				array("TYPE" => "default", "MODE" => "forum", "IID" => "all")), 
	"~RSS" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"], 
				array("TYPE" => "default", "MODE" => "forum", "IID" => "all")), 
	"~RSS_DEFAULT" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RSS"], 
			array("TYPE" => "rss2", "MODE" => "forum", "IID" => "all")), 
	"RSS_DEFAULT" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RSS"], 
			array("TYPE" => "rss2", "MODE" => "forum", "IID" => "all")), 
);
$arGroupForum = array();
$parser = new textParser(false, false, false, "light");
$parser->MaxStringLen = $arParams["WORD_LENGTH"];
$arResult["PARSER"] = $parser;
$arResult["FORUMS_FOR_GUEST"] = array();
$arResult["FORUMS_LIST"] = array();
/*******************************************************************/
if ($GLOBALS["USER"]->IsAuthorized())
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
	$res = @unserialize(CUserOptions::GetOption("forum", "user_info", ""));
	$arResult["USER"]["HIDDEN_GROUPS"] = (is_array($res["groups"]) ? $res["groups"] : array());
	$arResult["USER"]["HIDDEN_FORUMS"] = (is_array($res["forums"]) ? $res["forums"] : array());
}
foreach ($arParams["FID_RANGE"] as $key => $val)
{
	if (intVal($val) > 0)
		$res[] = $val;
}
$arParams["FID_RANGE"] = $res;
$cache = new CPHPCache();
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/");
/********************************************************************
				/Default values
********************************************************************/
ForumSetLastVisit(0);
/********************************************************************
				Data
********************************************************************/
	$arFilter = array();
	if ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N" || $GLOBALS["APPLICATION"]->GetGroupRight("forum") < "W")
		$arFilter["LID"] = SITE_ID;
	if (!empty($arParams["FID_RANGE"]) && ($GLOBALS["APPLICATION"]->GetGroupRight("forum") < "W" || $arParams["SHOW_FORUMS_LIST"] == "Y")):
		$arFilter["@ID"] = $arParams["FID_RANGE"];
	endif;
	if ($GLOBALS["APPLICATION"]->GetGroupRight("forum") < "W"):
		$arFilter["PERMS"] = array($USER->GetGroups(), 'A'); 
		$arFilter["ACTIVE"] = "Y";
	endif;

	if ($arParams["GID"] > 0)
	{
		if (intVal($arResult["GROUP"]["RIGHT_MARGIN"]) - intVal($arResult["GROUP"]["LEFT_MARGIN"]) > 1)
		{
			reset($arResult["GROUPS"]);
			$val = array($arParams["GID"]);
			$res = current($arResult["GROUPS"]);
			$bFounded = false;
			do
			{
				if (!$bFounded && intVal($res["ID"]) != $arParams["GID"]):
					continue;
				elseif (intVal($res["ID"]) == $arParams["GID"]):
					$bFounded = true;
					continue;
				elseif ($res["LEFT_MARGIN"] > $arResult["GROUP"]["RIGHT_MARGIN"]):
					break;
				endif;
				$val[] = $res["ID"];
			} while ($res = next($arResult["GROUPS"]));
			$arFilter["@FORUM_GROUP_ID"] = $val;
		}
		else
		{
			$arFilter["FORUM_GROUP_ID"] = $arParams["GID"];
		}
	}
/********************************************************************
				Action
********************************************************************/
if ($_SERVER["REQUEST_METHOD"] == "GET" && $_GET["ACTION"] == "SET_BE_READ"):
	if (!check_bitrix_sessid()):
	
	elseif ($arParams["GID"] <= 0):
		ForumSetReadForum(false);
	else:
		$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
		$db_res->NavStart($arParams["FORUMS_PER_PAGE"], false);
		while ($res = $db_res->Fetch())
		{
			ForumSetReadForum($res["ID"]);
		}
	endif;
endif;
/********************************************************************
				/Action
********************************************************************/
/************** Forums data ****************************************/
				
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$arFilterForum = $arFilter;
	if ($arParams["MINIMIZE_SQL"] == "Y" && $GLOBALS["USER"]->IsAuthorized()):
		$arFilterForum["RENEW"] = $GLOBALS["USER"]->GetID();
	endif;
	$dbForum = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilterForum);
	$dbForum->NavStart($arParams["FORUMS_PER_PAGE"], false);
	$arResult["NAV_RESULT"] = $dbForum;
	$arResult["NAV_STRING"] = $dbForum->GetPageNavStringEx($navComponentObject, GetMessage("F_FORUM"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arForums = array();
	while ($res = $dbForum->GetNext())
	{
		$res["PERMISSION"] = ForumCurrUserPermissions($res["ID"]);
		$res["MODERATE"] = array("TOPICS" => 0, "POSTS" => intVal($res["POSTS_UNAPPROVED"]));
		$res["mCnt"] = $res["MODERATE"]["POSTS"];
		
		$arResult["USER"]["CAN_MODERATE"] = ($arResult["USER"]["CAN_MODERATE"] == "Y" || $res["PERMISSION"] >= "Q" ? "Y" : "N");
		if ($res["PERMISSION"] >= "Q"):
			$res["~LAST_POSTER_ID"] = $res["~ABS_LAST_POSTER_ID"];
			$res["~LAST_POST_DATE"] = $res["~ABS_LAST_POST_DATE"];
			$res["~LAST_POSTER_NAME"] = $res["~ABS_LAST_POSTER_NAME"];
			$res["~LAST_MESSAGE_ID"] = $res["~ABS_LAST_MESSAGE_ID"];
			$res["LAST_POSTER_ID"] = $res["ABS_LAST_POSTER_ID"];
			$res["LAST_POST_DATE"] = $res["ABS_LAST_POST_DATE"];
			$res["LAST_POSTER_NAME"] = $res["ABS_LAST_POSTER_NAME"];
			$res["LAST_MESSAGE_ID"] = $res["ABS_LAST_MESSAGE_ID"];
			$res["TID"] = $res["ABS_TID"];
			$res["TITLE"] = $res["ABS_TITLE"];
		endif;
		if (intVal($arFilterForum["RENEW"]) <= 0):
			$res["~NewMessage"] = NewMessageForum($res["ID"], $res["LAST_POST_DATE"]);
			$res["NewMessage"] = ($res["~NewMessage"] ? "Y" : "N");
		else:
			$res["~NewMessage"] = intVal($res["TCRENEW"]);
			$res["NewMessage"] = ($res["~NewMessage"] > 0 ? "Y" : "N");
		endif;
			
		$res["TITLE"] = $parser->wrap_long_words($res["TITLE"]);
		$res["LAST_POSTER_NAME"] = $parser->wrap_long_words($res["LAST_POSTER_NAME"]);
		$res["LAST_POST_DATE"] = (intval($res["LAST_MESSAGE_ID"]) > 0 ? 
			CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["LAST_POST_DATE"], CSite::GetDateFormat())) 
			: 
			"");
		
		$res["URL"] = array(
			"MODERATE_MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_APPR"], 
				array("FID" => $res["ID"], "TID" => "s")), 
			"TOPICS" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["ID"])), 
			"MESSAGE" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
				array("FID" => $res["ID"], "TID" => $res["TID"], "MID" => $res["LAST_MESSAGE_ID"]))."#message".$res["LAST_MESSAGE_ID"], 
			"AUTHOR" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], 
				array("UID" => $res["LAST_POSTER_ID"]))	);
/************** For custom template ********************************/
		$res["topic_list"] = $res["URL"]["TOPICS"];
		$res["message_appr"] = $res["URL"]["MODERATE_MESSAGE"];
		$res["message_list"] = $res["URL"]["MESSAGE"];
		$res["profile_view"] = $res["URL"]["AUTHOR"];
/*******************************************************************/
		$res["FORUM_GROUP_ID"] = intVal($res["FORUM_GROUP_ID"]);
		$arGroupForum[$res["FORUM_GROUP_ID"]]["FORUM"][] = $res;
		$arResult["FORUMS_LIST"][$res["ID"]] = $res["ID"];
	}

	$arGroups = array();
	if (!function_exists("__array_merge"))
	{
		function __array_merge($arr1, $arr2)
		{
			$arResult = $arr1;
			foreach ($arr2 as $key2 => $val2)
			{
				if (!array_key_exists($key2, $arResult))
				{
					$arResult[$key2] = $val2;
					continue;
				}
				elseif ($val2 == $arResult[$key2])
					continue;
				elseif (!is_array($arResult[$key2]))
					$arResult[$key2] = array($arResult[$key2]);
				$arResult[$key2] = __array_merge($arResult[$key2], $val2);
			}
			return $arResult;
		}
	}
	
	foreach ($arGroupForum as $PARENT_ID => $res)
	{
		$bResult = true;
		$res = array("FORUMS" => $res["FORUM"]);

		$count = 0; 
		while (
			intval($PARENT_ID) > 0 
			&& 
			(
				$arParams["GID"] <= 0 
					|| 
				(
					intVal($arResult["GROUPS"][$arParams["GID"]]["LEFT_MARGIN"]) <= intVal($arResult["GROUPS"][$PARENT_ID]["LEFT_MARGIN"]) 
						&& 
					intVal($arResult["GROUPS"][$PARENT_ID]["RIGHT_MARGIN"]) <= intVal($arResult["GROUPS"][$arParams["GID"]]["RIGHT_MARGIN"])
				)
			)
		) 
		{
			if (!array_key_exists("GROUP_".$PARENT_ID, $arResult["URL"]))
			{
				$arResult["URL"]["GROUP_".$PARENT_ID] = CComponentEngine::MakePathFromTemplate(
					$arParams["URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID));
				$arResult["URL"]["~GROUP_".$PARENT_ID] = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID));
			}
			if (!array_key_exists($PARENT_ID, $arResult["GROUPS"]))
			{
				$bResult = false;
				$PARENT_ID = false;
				break;
			}
			$res = array($PARENT_ID => __array_merge($arResult["GROUPS"][$PARENT_ID], $res));
			$PARENT_ID = $arResult["GROUPS"][$PARENT_ID]["PARENT_ID"];
			
			$res = array("GROUPS" => $res);
			if ($PARENT_ID > $arParams["GID"])
				$res = __array_merge($arResult["GROUPS"][$PARENT_ID], $res);
		}
		if ($bResult == true)
			$arGroups = __array_merge($arGroups, $res);
	}
	
	foreach ($arGroupForum as $key => $val)
	{
		$key = intVal($key);
		if (array_key_exists($key, $arResult["GROUPS"]))
			$arGroupForum[$key] = array_merge($arResult["GROUPS"][$key], $val);
	}
	
	$arResult["FORUM"] = $arGroupForum; // out of date
	$arResult["FORUMS"] = $arGroups;
/************** Navigation *****************************************/
	if ($arParams["GID"] > 0):
		$PARENT_ID = intVal($arResult["GROUP"]["PARENT_ID"]);
		while ($PARENT_ID > 0)
		{
			$arResult["GROUP_NAVIGATION"][] = $arResult["GROUPS"][$PARENT_ID];
			if (!array_key_exists("GROUP_".$PARENT_ID, $arResult["URL"]))
			{
				$arResult["URL"]["GROUP_".$PARENT_ID] = CComponentEngine::MakePathFromTemplate(
					$arParams["URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID));
				$arResult["URL"]["~GROUP_".$PARENT_ID] = CComponentEngine::MakePathFromTemplate(
					$arParams["~URL_TEMPLATES_FORUMS"], array("GID" => $PARENT_ID));
			}
			$PARENT_ID = intVal($arResult["GROUPS"][$PARENT_ID]["PARENT_ID"]);
		}
		$arResult["GROUP_NAVIGATION"] = array_reverse($arResult["GROUP_NAVIGATION"]);
	endif;
/************** Forums for guest (RSS) *****************************/
unset($arFilter["APPROVED"]);
$arFilter["PERMS"] = array(2, 'A'); 
$arFilter["ACTIVE"] = "Y";
$arFilter["LID"] = SITE_ID;

$cache_id = "forums_for_guest_".serialize(array($arFilter));
$cache_path = $cache_path_main."forums";
$arForums = array();
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	if (is_array($res["arForums"]))
		$arForums = $res["arForums"];
}
if (!is_array($arForums) || count($arForums) <= 0)
{
	$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
	while ($res = $db_res->GetNext())
		$arForums[$res["ID"]] = $res;
		
	if ($arParams["CACHE_TIME"] > 0):
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(array("arForums" => $arForums));
	endif;
}
$arResult["FORUMS_FOR_GUEST"] = (is_array($arForums) ? $arForums : array());
/********************************************************************
				/Data
********************************************************************/

if ($arParams["SET_TITLE"] != "N"):
	$sTitle = ($arParams["GID"] <= 0 ? GetMessage("F_TITLE") : $arResult["GROUP"]["NAME"]);
	$APPLICATION->SetTitle($sTitle);
endif;

if ($arParams["SET_NAVIGATION"] != "N" && $arParams["GID"] > 0):
	foreach ($arResult["GROUP_NAVIGATION"] as $key => $res):
		$APPLICATION->AddChainItem($res["NAME"], $arResult["URL"]["~GROUP_".$res["ID"]]);
	endforeach;
	$APPLICATION->AddChainItem($arResult["GROUP"]["NAME"]);
endif;

// if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
	// CForumNew::ShowPanel(0, 0, false);

/************** For custom template ********************************/
$arResult["index"] = $arResult["URL"]["INDEX"]; 
$arResult["DrawAddColumn"] = ($arResult["USER"]["CAN_MODERATE"] == "Y" ? "Y" : "N");
/************** For custom template/********************************/

/*******************************************************************/
$this->IncludeComponentTemplate();
/*******************************************************************/

return $arResult["FORUMS_LIST"];
?>
