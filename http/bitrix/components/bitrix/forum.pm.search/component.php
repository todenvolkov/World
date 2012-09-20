<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("PM_AUTH"));
	return 0;
endif;

if(!function_exists("__UnEscape"))
{
	function __UnEscape(&$item, $key)
	{
		if(is_array($item))
			array_walk($item, '__UnEscape');
		else
		{
			if(strpos($item, "%u") !== false)
				$item = $GLOBALS["APPLICATION"]->UnJSEscape($item);
		}
	}
}

array_walk($_REQUEST, '__UnEscape');
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$UID = $arParams["UID"] = intVal($_REQUEST["UID"]);
	$mode = $_REQUEST["mode"];
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
		"pm_list" => "PAGE_NAME=pm_list&FID=#FID#",
		"pm_read" => "PAGE_NAME=pm_read&MID=#MID#",
		"pm_edit" => "PAGE_NAME=pm_edit&MID=#MID#",
		"pm_search" => "PAGE_NAME=pm_search");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "FID", "TID", "UID", BX_AJAX_PARAM_ID));
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
// ************************* ADDITIONAL ****************************************************************
	$arParams["PM_USER_PAGE"] = intVal($arParams["PM_USER_PAGE"] > 0 ? $arParams["PM_USER_PAGE"] : 10);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
// *************************/Input params***************************************************************

	ForumSetLastVisit();
	$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_SEARCH"], array());
// *****************************************************************************************
	$arResult["sessid"] = bitrix_sessid_post();
	$arResult["SITE_CHARSET"] = SITE_CHARSET;
// *****************************************************************************************
	$arResult["~search_template"] = trim($_REQUEST["search_template"]);
	$arResult["search_template"] = htmlspecialcharsEx($arResult["~search_template"]);
// *****************************************************************************************
	$arResult["SHOW_SEARCH_RESULT"] = "N";
	$arResult["SEARCH_RESULT"] = array();
	
	if (strLen($arResult["~search_template"]) > 0 && ($arResult["~search_template"] != "*")) 
	{
		$arResult["SHOW_SEARCH_RESULT"] = "Y";
		$reqSearch = CForumUser::SearchUser(str_replace("**", "*", "*".$arResult["~search_template"]."*"));
		$reqSearch->NavStart($arParams["PM_USER_PAGE"], false);
		$arResult["NAV_RESULT"] = $reqSearch;
		$arResult["NAV_STRING"] = $reqSearch->GetPageNavStringEx($navComponentObject, GetMessage("PM_SEARCH_RESULT"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
		
		if ($reqSearch && ($res = $reqSearch->GetNext()))
		{
			do 
			{
				$arResult["SEARCH_RESULT"][] = array_merge(
					array(
						"link" => ForumAddPageParams(
							$arResult["CURRENT_PAGE"], 
							array("search_insert" => "Y", "UID" => intVal($res["ID"]), "sessid" => bitrix_sessid()))), 
					$res);
			}
			while ($res = $reqSearch->GetNext());
		}
	}
	$arResult["SHOW_SELF_CLOSE"] = "N";
	if ((($_REQUEST["search_insert"] == "Y") && (intVal($UID) > 0)) || (strLen($_REQUEST["search_by_login"]) > 0))
	{
		if (strLen($_REQUEST["search_by_login"]) <= 0)
		{
			$db_res = CForumUser::GetList(array(), array("USER_ID" => $UID, "SHOW_ABC" => ""));
			if ($db_res && ($res = $db_res->GetNext()))
			{
				$arResult["SHOW_SELF_CLOSE"] = "Y";
				$arResult["UID"] = $UID;
				$arResult["SHOW_NAME"] = $res["SHOW_ABC"];
				$arResult["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $UID));
			}
		}
		else
		{
			$arResult["SHOW_SELF_CLOSE"] = "Y";
			$arResult["SHOW_MODE"] = "none";
			$db_res = CUser::GetByLogin($_REQUEST["search_by_login"]);
			if ($db_res && ($res = $db_res->GetNext()))
			{
				$forum_user = CForumUser::GetByUSER_ID($res["ID"]);
				if ($forum_user["SHOW_NAME"]=="Y")
					$res["SHOW_ABC"] = trim($res["NAME"]." ".$res["LAST_NAME"]);
				if (empty($res["SHOW_ABC"]))
					$res["SHOW_ABC"] = $res["LOGIN"];
				if ($res["SHOW_ABC"] == $_REQUEST["search_by_login"])
				{
					$arResult["SHOW_MODE"] = "full";
					$arResult["SHOW_NAME"] = $res["SHOW_ABC"];
					$arResult["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["ID"]));
				}
				else
					$arResult["SHOW_MODE"] = "light";
				$arResult["UID"] = $res["ID"];
			}
		}
        $arResult['SHOW_NAME'] = htmlspecialchars_decode($arResult['SHOW_NAME']);
	}
// *****************************************************************************************
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
	$this->IncludeComponentTemplate();
	die();
// *****************************************************************************************
?>
