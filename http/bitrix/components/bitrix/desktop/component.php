<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/desktop/include.php');
if(!class_exists('CUserOptions'))
	include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/classes/".$GLOBALS['DBType']."/favorites.php");

$arParams["ID"] = (isset($arParams["ID"])?$arParams["ID"]:"gdholder1");

if (array_key_exists("DEFAULT_ID", $arParams) && strlen(trim($arParams["DEFAULT_ID"])) > 0)
{
	$user_option_id = 0;
	$arUserOptionsDefault = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["DEFAULT_ID"], false, 0);
}
else
{
	$user_option_id = false;
	$arParams["DEFAULT_ID"] = false;
	$arUserOptionsDefault = false;
}

if (IsModuleInstalled('intranet'))
{
	if (strlen(trim($arParams["NAME_TEMPLATE"])) <= 0)
		$arParams["NAME_TEMPLATE"] = GetMessage('CMDESKTOP_NAME_TEMPLATE_DEFAULT');
	$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";

	if (!array_key_exists("PM_URL", $arParams))
		$arParams["PM_URL"] = "/company/personal/messages/chat/#USER_ID#/";
	if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
		$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
	if (IsModuleInstalled("video") && !array_key_exists("PATH_TO_VIDEO_CALL", $arParams))
		$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#USER_ID#/";
}

if (!array_key_exists("GADGETS_FIXED", $arParams))
	$arParams["GADGETS_FIXED"] = array();

$arResult = Array();

if($USER->IsAuthorized() && $APPLICATION->GetFileAccessPermission($APPLICATION->GetCurPage())>"R" && !$arParams["DEFAULT_ID"])
	$arResult["PERMISSION"] = "X";
elseif(
	$USER->IsAuthorized()
	&& $arParams["DEFAULT_ID"]
	&& (
		$GLOBALS["USER"]->IsAdmin()
		|| (
			CModule::IncludeModule('socialnetwork')
			&& CSocNetUser::IsCurrentUserModuleAdmin()
		)
	)
)
	$arResult["PERMISSION"] = "X";
elseif($USER->IsAuthorized() && $arParams["CAN_EDIT"]=="Y")
	$arResult["PERMISSION"] = "W";
else
	$arResult["PERMISSION"] = "R";

$arParams["PERMISSION"] = $arResult["PERMISSION"];

if($USER->IsAuthorized() && $arResult["PERMISSION"]>"R")
{
	if($_SERVER['REQUEST_METHOD']=='POST')
	{
		if($_POST['holderid'] == $arParams["ID"])
		{
			$gdid = $_POST['gid'];
			$p = strpos($gdid, "@");
			if($p === false)
			{
				$gadget_id = $gdid;
				$gdid = $gdid."@".rand();
			}
			else
			{
				$gadget_id = substr($gdid, 0, $p);
			}

			$arGadget = BXGadget::GetById($gadget_id);
			if($arGadget && !is_array($arParams["GADGETS"]) || in_array($arGadget["ID"], $arParams["GADGETS"]) || in_array("ALL", $arParams["GADGETS"]))
			{
				if($_POST['action']=='add')
				{
					$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);
					if(!is_array($arUserOptions["GADGETS"]))
						$arUserOptions["GADGETS"] = Array();

					foreach($arUserOptions["GADGETS"] as $tempid=>$tempgadget)
						if($tempgadget["COLUMN"]==0)
							$arUserOptions["GADGETS"][$tempid]["ROW"]++;

			   		$arUserOptions["GADGETS"][$gdid] = Array("COLUMN"=>0, "ROW"=>0);
					CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions, false, $user_option_id);

					LocalRedirect($_SERVER['REQUEST_URI']);
				}
				elseif($_POST['action']=='update')
				{
					$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);
					if(!is_array($arUserOptions["GADGETS"]))
						$arUserOptions["GADGETS"] = Array();

			   		$arUserOptions["GADGETS"][$gdid]["SETTINGS"] = $_POST["settings"];
					CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions, false, $user_option_id);

					LocalRedirect($_SERVER['REQUEST_URI']);
				}
			}
		}
	}
}

if($_REQUEST['gd_ajax']==$arParams["ID"])
{
	if($USER->IsAuthorized() && $arResult["PERMISSION"]>"R")
	{
		$APPLICATION->RestartBuffer();
		switch($_REQUEST['gd_ajax_action'])
		{
			case 'get_settings':
				$gdid = $_REQUEST['gid'];

				$p = strpos($gdid, "@");
				if($p === false)
					break;

				$gadget_id = substr($gdid, 0, $p);

				// Р·Р°РїСЂРµС‰РµРЅРЅС‹Рµ Р°РґРјРёРЅРёСЃС‚СЂР°С‚РѕСЂРѕРј
				if(is_array($arParams["GADGETS"]) && !in_array($gadget_id, $arParams["GADGETS"]) && !in_array("ALL", $arParams["GADGETS"]))
					break;

				// РїРѕР»СѓС‡РёРј РїРѕР»СЊР·РѕРІР°С‚РµР»СЊСЃРєРёРµ РїР°СЂР°РјРµС‚СЂС‹ РіР°РґР¶РµС‚Р°
				$arGadget = BXGadget::GetById($gadget_id, true, $arParams);
				if($arGadget)
				{
					// РїРѕР»СѓС‡РёРј Р·РЅР°С‡РµРЅРёСЏ РїР°СЂР°РјРµС‚СЂРѕРІ
					$arGadgetParams = $arGadget["USER_PARAMETERS"];
					foreach($arParams as $id=>$p)
					{
						$pref = "GU_".$gadget_id."_";
						if(strpos($id, $pref)===0 && is_set($arGadgetParams, substr($id, strlen($pref))))
							$arGadgetParams[substr($id, strlen($pref))]["VALUE"] = $p;
					}

					$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);
					if(is_array($arUserOptions) && is_array($arUserOptions["GADGETS"]) && is_array($arUserOptions["GADGETS"][$gdid]) && is_array($arUserOptions["GADGETS"][$gdid]["SETTINGS"]))
					{
						foreach($arUserOptions["GADGETS"][$gdid]["SETTINGS"] as $p=>$v)
							if(is_set($arGadgetParams, $p))
								$arGadgetParams[$p]["VALUE"] = $v;
					}

					// РІРµСЂРЅРµРј РїРѕР»СЊР·РѕРІР°С‚РµР»СЋ
					echo CUtil::PhpToJSObject($arGadgetParams);
				}
				break;

			case 'clear_settings':
				CUserOptions::DeleteOption("intranet", "~gadgets_".$arParams["ID"], false, $user_option_id);
				break;

			case 'save_default':
				GDCSaveSettings($arParams, $_REQUEST['POS']);
				
				if ($arResult["PERMISSION"] > "W")
				{
					$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);
					if (array_key_exists("DEFAULT_ID", $arParams) && strlen(trim($arParams["DEFAULT_ID"])) > 0)
						CUserOptions::SetOption("intranet", "~gadgets_".$arParams["DEFAULT_ID"], $arUserOptions, false, 0);
					else
						CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions, true);
				}
				break;

			case 'update_position':
				GDCSaveSettings($arParams, $_REQUEST['POS']);
				break;
		}
	}
	else
		echo GetMessage("CMDESKTOP_AUTH_ERR");
	die();
}


$arResult["COLS"] = (intval($arParams["COLUMNS"])>0 && intval($arParams["COLUMNS"])<10)?intval($arParams["COLUMNS"]):3;
for($i=0; $i<$arResult["COLS"]; $i++)
	$arResult["COLUMN_WIDTH"][$i] = $arParams["COLUMN_WIDTH_".$i];

$arResult["GADGETS"] = Array();
$arResult["ID"] = $arParams["ID"];
$arParams["UPD_URL"] = $arResult["UPD_URL"] = POST_FORM_ACTION_URI;

$parts = explode("?", $arResult['UPD_URL'], 2);
if (count($parts) == 2)
{
	$string = $parts[0]."?";
	$arTmp = array();
	$params = explode("&", $parts[1]);
	foreach ($params as $param)
	{
		$tmp = explode("=", $param);
		if (count($tmp) == 2)
		{
			if ($tmp[0] != "logout")
				$arTmp[] = $param;
		}
		else
			$arTmp[] = $param;
	}
	$string .= implode("&", $arTmp);
	$arParams["UPD_URL"] = $arResult["UPD_URL"] = $string;
}

$arGDList = Array();

$arUserOptions = false;
if(($USER->IsAuthorized() && $arResult["PERMISSION"]>"R") || $user_option_id !== false)
	$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, $user_option_id);
else
	$arUserOptions = CUserOptions::GetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptionsDefault, 99999999);

$arGroups = Array(
		"personal" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_PERSONAL"),
				"DESCRIPTION" =>GetMessage("CMDESKTOP_GROUP_PERSONAL_DESCR"),
				"GADGETS" => Array(),
			),
		"employees" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_EMPL"),
				"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_EMPL_DESCR"),
				"GADGETS" => Array(),
			),
		"communications" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_COMMUN"),
				"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_COMMUN_DESCR"),
				"GADGETS" => Array(),
			),
		"company" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_COMPANY"),
				"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_COMPANY_DESCR"),
				"GADGETS" => Array(),
			),
		"services" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_SERVICES"),
				"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_SERVICES_DESCR"),
				"GADGETS" => Array(),
			),
		"other" => Array(
				"NAME" => GetMessage("CMDESKTOP_GROUP_OTHER"),
				"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_OTHER_DESCR"),
				"GADGETS" => Array(),
			),
		"sonet" => Array(
				"NAME" => ($arParams["MODE"] == "SG" ? GetMessage("CMDESKTOP_GROUP_SONET_GROUP") : GetMessage("CMDESKTOP_GROUP_SONET_USER")),
				"DESCRIPTION" => ($arParams["MODE"] == "SG" ? GetMessage("CMDESKTOP_GROUP_SONET_GROUP_DESCR") : GetMessage("CMDESKTOP_GROUP_SONET_USER_DESCR")),
				"GADGETS" => Array(),
			),
	);

if (IsModuleInstalled("crm"))
{
	$arGroups["crm"] = array(
		"NAME" => GetMessage("CMDESKTOP_GROUP_CRM"),
		"DESCRIPTION" => GetMessage("CMDESKTOP_GROUP_CRM_DESCR"),
		"GADGETS" => Array(),		
	);
}
		

$arResult["ALL_GADGETS"] = Array();
$arGadgets = BXGadget::GetList();
foreach($arGadgets as $gadget)
{
	// РµСЃР»Рё РЅР°СЃС‚СЂРѕР№РєР°РјРё Р·Р°РїСЂРµС‰РµРЅ СЌС‚РѕС‚ РіР°РґР¶РµС‚, РїСЂРѕРїСѓСЃРєР°РµРј
	if(is_array($arParams["GADGETS"]) && !in_array($gadget["ID"], $arParams["GADGETS"]) && !in_array("ALL", $arParams["GADGETS"]))
		continue;

	if ($arParams["MODE"] != "SU" && $arParams["MODE"] != "SG" && ($gadget["SU_ONLY"] == true || $gadget["SG_ONLY"] == true))
		continue;

	if ($arParams["MODE"] == "SU" && $gadget["SU_ONLY"] != true && $gadget["SU"] != true)
		continue;

	if ($arParams["MODE"] == "SG" && $gadget["SG_ONLY"] != true && $gadget["SG"] != true)
		continue;

	if ($gadget["EXTRANET_ONLY"] == true && (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite()))
		continue;
	if ($gadget["SEARCH_ONLY"] == true && !IsModuleInstalled("search"))
		continue;
	if ($gadget["FORUM_ONLY"] == true && !IsModuleInstalled("forum"))
		continue;
	if ($gadget["BLOG_ONLY"] == true && !IsModuleInstalled("blog"))
		continue;
	if ($gadget["PHOTOGALLERY_ONLY"] == true && !IsModuleInstalled("photogallery"))
		continue;
	if ($gadget["WEBDAV_ONLY"] == true && !IsModuleInstalled("webdav"))
		continue;
	if ($gadget["SUPPORT_ONLY"] == true && !IsModuleInstalled("support"))
		continue;
	if ($gadget["WIKI_ONLY"] == true && !IsModuleInstalled("wiki"))
		continue;
	if ($gadget["VOTE_ONLY"] == true && (!IsModuleInstalled("vote") || !CBXFeatures::IsFeatureEnabled("Vote")))
		continue;
	if ($gadget["TASKS_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("Tasks"))
		continue;
	if ($gadget["MESSENGER_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("WebMessenger"))
		continue;
	if ($gadget["ABSENCE_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("StaffAbsence"))
		continue;
	if ($gadget["STAFF_CHANGES_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("StaffChanges"))
		continue;
	if ($gadget["COMMON_DOCS_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("CommonDocuments"))
		continue;
	if ($gadget["COMPANY_PHOTO_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("CompanyPhoto"))
		continue;
	if ($gadget["COMPANY_CALENDAR_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("CompanyCalendar"))
		continue;
	if ($gadget["CALENDAR_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("Calendar"))
		continue;
	if ($gadget["COMPANY_VIDEO_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("CompanyVideo"))
		continue;
	if ($gadget["WORKGROUPS_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("Workgroups"))
		continue;
	if ($gadget["FRIENDS_ONLY"] == true && !CBXFeatures::IsFeatureEnabled("Friends"))
		continue;
		
	if ($GLOBALS["USER"]->IsAuthorized() && $arResult["PERMISSION"] < "W" && $gadget["SELF_PROFILE_ONLY"] == true && $arParams["MODE"] == "SU" && intval($arParams["USER_ID"]) > 0 && $arParams["USER_ID"] != $GLOBALS["USER"]->GetID())
		continue;

	if ($gadget["BLOG_ONLY"] == true && $gadget["SU_ONLY"] == true && intval($arParams["USER_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog"))
		continue;

	if ($gadget["BLOG_ONLY"] == true && $gadget["SG_ONLY"] == true && intval($arParams["SOCNET_GROUP_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog"))
		continue;

	if ($gadget["FORUM_ONLY"] == true && $gadget["SU_ONLY"] == true && intval($arParams["USER_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "forum"))
		continue;

	if ($gadget["FORUM_ONLY"] == true && $gadget["SG_ONLY"] == true && intval($arParams["SOCNET_GROUP_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum"))
		continue;

	if ($gadget["SEARCH_ONLY"] == true && $gadget["SU_ONLY"] == true && intval($arParams["USER_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "search"))
		continue;

	if ($gadget["SEARCH_ONLY"] == true && $gadget["SG_ONLY"] == true && intval($arParams["SOCNET_GROUP_ID"]) > 0 && CModule::IncludeModule('socialnetwork') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "search"))
		continue;

	if($gadget["GROUP"]["ID"]=="")
		$gadget["GROUP"]["ID"] = "other";

	if (!is_array($gadget["GROUP"]["ID"]))
	{
		$arGroups[$gadget["GROUP"]["ID"]]["GADGETS"][] = $gadget["ID"];
	}
	else
	{
		foreach($gadget["GROUP"]["ID"] as $group_id)
		{
			if (($arParams["MODE"] == "SU" || $arParams["MODE"] == "SG") && $group_id != "sonet")
				continue;
			elseif ($arParams["MODE"] != "SU" && $arParams["MODE"] != "SG" && $group_id == "sonet")
				continue;
			$arGroups[$group_id]["GADGETS"][] = $gadget["ID"];
		}
	}


	$arResult["ALL_GADGETS"][$gadget['ID']] = $gadget;
}

$arResult["GROUPS"] = Array();
foreach($arGroups as $arGroup)
	if(count($arGroup['GADGETS'])>0)
		$arResult['GROUPS'][] = $arGroup;

$arResult["GADGETS"] = Array();
$arResult["GADGETS_LIST"] = Array();
for($i=0; $i<$arResult["COLS"]; $i++)
	$arResult["GADGETS"][$i] = Array();

// РЈР¶Рµ РЅР°СЃС‚СЂРѕРµРЅРЅР°СЏ СЃС‚СЂР°РЅРёС†Р°
if(is_array($arUserOptions))
{
	$bForceRedirect = false;
	foreach($arUserOptions["GADGETS"] as $gdid=>$gadgetUserSettings)
	{
		$gadgetUserSettings = $arUserOptions["GADGETS"][$gdid];

		$p = strpos($gdid, "@");
		if($p === false)
		{
			$gadget_id = $gdid;
			$gdid = $gdid."@".rand();
		}
		else
		{
			$gadget_id = substr($gdid, 0, $p);
		}

		if($arResult["ALL_GADGETS"][$gadget_id])
		{
			$arGadgetParams = $gadgetUserSettings["SETTINGS"];

			$arGadget = $arResult["ALL_GADGETS"][$gadget_id];
			foreach($arParams as $id=>$p)
			{
				$pref = "G_".$gadget_id."_";
				if(strpos($id, $pref)===0)
					$arGadgetParams[substr($id, strlen($pref))]=$p;

				$pref = "GU_".$gadget_id."_";
				if(strpos($id, $pref)===0 && !isset($arGadgetParams[substr($id, strlen($pref))]))
					$arGadgetParams[substr($id, strlen($pref))]=$p;
			}

			if(intval($gadgetUserSettings["COLUMN"])<=0 || intval($gadgetUserSettings["COLUMN"])>=$arResult["COLS"])
				$arUserOptions["GADGETS"][$gdid]["COLUMN"] = 0;

			$arGCol = &$arResult["GADGETS"][$gadgetUserSettings["COLUMN"]];

			if(isset($arGCol[$gadgetUserSettings["ROW"]]))
			{
				ksort($arGCol, SORT_NUMERIC);
				$ks = array_keys($arGCol);
				$arUserOptions["GADGETS"][$gdid]["ROW"] = $ks[count($ks)-1] + 1;
			}

			$arGadget["ID"] = $gdid;
			$arGadget["GADGET_ID"] = $arResult["GADGETS_LIST"][] = $gadget_id;
			$arGadget["TITLE"] = htmlspecialchars($arGadget["NAME"]);
			$arGadget["SETTINGS"] = $arGadgetParams;
			
			if (
				is_array($arGadgetParams)
				&& array_key_exists("TITLE_STD", $arGadgetParams)
				&& strlen($arGadgetParams["TITLE_STD"]) > 0
			)
				$arGadget["TITLE"] = htmlspecialchars($arGadgetParams["TITLE_STD"]);
			
			$arGadget["HIDE"] = $gadgetUserSettings["HIDE"];
			if($arParams["PERMISSION"]>"R")
				$arGadget["USERDATA"] = &$arUserOptions["GADGETS"][$gdid]["USERDATA"];
			else
				$arGadget["USERDATA"] = $arUserOptions["GADGETS"][$gdid]["USERDATA"];
			$arGadget["CONTENT"] = BXGadget::GetGadgetContent($arGadget, $arParams);
			$arResult["GADGETS"][$gadgetUserSettings["COLUMN"]][$gadgetUserSettings["ROW"]] = $arGadget;
			
			if($arGadget["FORCE_REDIRECT"])
				$bForceRedirect = true;
		}
		else
		{
			unset($arUserOptions["GADGETS"][$gdid]);
		}
	}

	for($i=0; $i<$arResult["COLS"]; $i++)
		ksort($arResult["GADGETS"][$i], SORT_NUMERIC);

	$arResult["GADGETS_LIST"] = array_unique($arResult["GADGETS_LIST"]);

	if($bForceRedirect)
	{
		CUserOptions::SetOption("intranet", "~gadgets_".$arParams["ID"], $arUserOptions, false, $user_option_id);
		LocalRedirect($_SERVER['REQUEST_URI']);
	}
}
else
{
	/*
	foreach($arResult["ALL_GADGETS"] as $gadget_id=>$gd)
	{
		$arGadgetParams = Array();
		foreach($arParams as $id=>$p)
		{
			$pref = "G_".$gadget_id."_";
			if(strpos($id, $pref)===0)
				$arGadgetParams[substr($id, strlen($pref))]=$p;

			$pref = "GU_".$gadget_id."_";
			if(strpos($id, $pref)===0 && !isset($arGadgetParams[substr($id, strlen($pref))]))
			{
				$arGadgetParams[substr($id, strlen($pref))]=$p;
			}
		}

		$arGadget = Array(
					"ID"=>$gadget_id."@".rand(),
					"GADGET_ID"=>$gadget_id,
					"NAME"=>htmlspecialchars($arResult["ALL_GADGETS"][$gadget_id]["NAME"]),
					"OBJECT" => new BXGadget($arResult["ALL_GADGETS"][$gadget_id]["PATH"], $arParams, $arGadgetParams)
					);

		$min = 100;$min_i = 0;
		for($i=0; $i<$arResult["COLS"]; $i++)
		{
			if($min > count($arResult["GADGETS"][$i]))
			{
				$min = count($arResult["GADGETS"][$i]);
				$min_i = $i;
			}
		}

		$arResult["GADGETS"][$min_i][] = $arGadget;
	}
	*/
}


$js = '/bitrix/js/main/utils.js';
$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="'.$js.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$js).'"></script>');

$js = '/bitrix/js/main/popup_menu.js';
$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="'.$js.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$js).'"></script>');

$js = '/bitrix/js/main/ajax.js';
$GLOBALS['APPLICATION']->AddHeadString('<script type="text/javascript" src="'.$js.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$js).'"></script>');

$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/themes/.default/pubstyles.css');

$this->IncludeComponentTemplate();
?>
