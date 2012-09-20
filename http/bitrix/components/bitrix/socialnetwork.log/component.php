<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

if(!class_exists('CUserOptions'))
	include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/classes/".$GLOBALS['DBType']."/favorites.php");
	
if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (!array_key_exists("SUBSCRIBE_ONLY", $arParams) || strLen($arParams["SUBSCRIBE_ONLY"]) <= 0)
	$arParams["SUBSCRIBE_ONLY"] = "Y";

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
{
	$arParams["~PATH_TO_GROUP"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#";
	$arParams["PATH_TO_GROUP"] = htmlspecialchars($arParams["~PATH_TO_GROUP"]);
}

if ($arParams["SHOW_EVENT_ID_FILTER"] == "Y")
{
	$arParams["PATH_TO_LOG_RSS"] = trim($arParams["PATH_TO_LOG_RSS"]);
	if (strlen($arParams["PATH_TO_LOG_RSS"]) <= 0)
	{
		if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
			$arParams["~PATH_TO_LOG_RSS"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_log_rss&entity_id=#group_id#&bx_hit_hash=#sign#&events=#events#";
		else
			$arParams["~PATH_TO_LOG_RSS"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_log_rss&entity_id=#user_id#&bx_hit_hash=#sign#&events=#events#";
		
		$arParams["PATH_TO_LOG_RSS"] = htmlspecialchars($arParams["~PATH_TO_LOG_RSS"]);
	}
}
	
$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);

$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);
if ($arParams["GROUP_ID"] <= 0)
	$arParams["GROUP_ID"] = IntVal($_REQUEST["flt_group_id"]);
$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = IntVal($_REQUEST["flt_user_id"]);
$arParams["EVENT_ID"] = Trim($arParams["EVENT_ID"]);
if (StrLen($arParams["EVENT_ID"]) <= 0)
{
	if (
		array_key_exists("flt_event_id_all", $_REQUEST) 
		&& 
		(
			$_REQUEST["flt_event_id_all"] == "Y"
			|| !array_key_exists("flt_event_id", $_REQUEST)
		)
	)
	{
		$arParams["EVENT_ID"] = $_REQUEST["flt_event_id"] = array("all");
		CUserOptions::DeleteOption("socialnetwork", "~log_".$arParams["ENTITY_TYPE"]."_".($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]));
	}
	elseif (array_key_exists("flt_event_id", $_REQUEST))
	{
		if (!is_array($_REQUEST["flt_event_id"]))
			$arParams["EVENT_ID"] = array($_REQUEST["flt_event_id"]);
		else
			$arParams["EVENT_ID"] = $_REQUEST["flt_event_id"];
	
		CUserOptions::SetOption("socialnetwork", "~log_".$arParams["ENTITY_TYPE"]."_".($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]), $arParams["EVENT_ID"]);
	}
	elseif(array_key_exists("log_filter_submit", $_REQUEST))
		$arParams["EVENT_ID"] = $_REQUEST["flt_event_id"] = array("all");
	else
	{
		$arParams["EVENT_ID"] = $_REQUEST["flt_event_id"] = CUserOptions::GetOption("socialnetwork", "~log_".$arParams["ENTITY_TYPE"]."_".($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]));
		if (!$_REQUEST["flt_event_id"])
			$_REQUEST["flt_event_id"] = array("all");
	}
	
}	
$arParams["FLT_ALL"] = StrToUpper(Trim($arParams["FLT_ALL"]));
if (StrLen($arParams["FLT_ALL"]) <= 0)
	$arParams["FLT_ALL"] = StrToUpper(Trim($_REQUEST["flt_all"]));

$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE"] ? $arParams["NAME_TEMPLATE"] : '#NOBR##NAME# #LAST_NAME##/NOBR#';
$arParams["NAME_TEMPLATE_WO_NOBR"] = str_replace(
			array("#NOBR#", "#/NOBR#"), 
			array("", ""), 
			$arParams["NAME_TEMPLATE"]
	);
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
	
$arFilter["ENTITY_TYPE"] = Trim($arFilter["ENTITY_TYPE"]);
if ($arFilter["ENTITY_TYPE"] != SONET_ENTITY_GROUP && $arFilter["ENTITY_TYPE"] != SONET_ENTITY_USER)
	$arFilter["ENTITY_TYPE"] = "";
if (StrLen($arParams["ENTITY_TYPE"]) <= 0)
	$arParams["ENTITY_TYPE"] = Trim($_REQUEST["flt_entity_type"]);
if ($arFilter["ENTITY_TYPE"] != SONET_ENTITY_GROUP && $arFilter["ENTITY_TYPE"] != SONET_ENTITY_USER)
	$arFilter["ENTITY_TYPE"] = "";

$arParams["LOG_DATE_DAYS"] = IntVal($arParams["LOG_DATE_DAYS"]);

if ($arParams["LOG_DATE_DAYS"] <= 0 && $arParams["SUBSCRIBE_ONLY"] != "Y")
	$arParams["LOG_DATE_DAYS"] = 7;
	
if (array_key_exists("flt_date_from", $_REQUEST))
	$arParams["LOG_DATE_FROM"] = trim($_REQUEST["flt_date_from"]);
	
if (array_key_exists("flt_date_to", $_REQUEST))
	$arParams["LOG_DATE_TO"] = trim($_REQUEST["flt_date_to"]);

if (is_array($_REQUEST["flt_created_by_id"]))
	$_REQUEST["flt_created_by_id"] = $_REQUEST["flt_created_by_id"][0];

if (IntVal($_REQUEST["flt_created_by_id"]) > 0)
	$arParams["CREATED_BY_ID"] = $_REQUEST["flt_created_by_id"];

if (
	array_key_exists("SUBSCRIBE_ONLY", $arParams) 
	&& $arParams["SUBSCRIBE_ONLY"] == "Y"
	&& array_key_exists("flt_show_hidden", $_REQUEST)
	&& $_REQUEST["flt_show_hidden"] == "Y"
)
	$arParams["SHOW_HIDDEN"] = true;
else	
	$arParams["SHOW_HIDDEN"] = false;
			
$arParams["AUTH"] = ((StrToUpper($arParams["AUTH"]) == "Y") ? "Y" : "N");

$arParams["LOG_CNT"] = (array_key_exists("LOG_CNT", $arParams) && intval($arParams["LOG_CNT"]) > 0 ? $arParams["LOG_CNT"] : 0);

$arParams["PAGE_SIZE"] = intval($arParams["PAGE_SIZE"]);
if($arParams["PAGE_SIZE"] <= 0)
	$arParams["PAGE_SIZE"] = 50;
$arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);
	
$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

if ($GLOBALS["USER"]->IsAuthorized() || $arParams["AUTH"] == "Y" || $arParams["SUBSCRIBE_ONLY"] != "Y")
{
	if ($arParams["SHOW_EVENT_ID_FILTER"] == "Y")
	{
		if (array_key_exists("ENTITY_TYPE", $arParams) && strlen("ENTITY_TYPE") > 0 && array_key_exists("ENTITY_ID", $arParams) && intval("ENTITY_ID") > 0)
		{
			$arResult["ActiveFeatures"] = CSocNetFeatures::GetActiveFeaturesNames($arParams["ENTITY_TYPE"], ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]));

			foreach($arResult["ActiveFeatures"] as $featureID => $featureName)
			{
				$minoperation = $GLOBALS["arSocNetFeaturesSettings"][$featureID]["minoperation"];
				$bCanView = (array_key_exists($featureID, $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arParams["ENTITY_TYPE"], ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]), $featureID, $minoperation[count($minoperation)-1], CSocNetUser::IsCurrentUserModuleAdmin()));
				if (!$bCanView)
					unset($arResult["ActiveFeatures"][$featureID]);
			}
		}
		else
		{
			$arResult["ActiveFeatures"] = array();
			foreach ($GLOBALS["arSocNetFeaturesSettings"] as $featureID => $arFeature)
			{
				if (array_key_exists("subscribe_events", $arFeature) && is_array($arFeature["subscribe_events"]))
				{
					foreach($arFeature["subscribe_events"] as $event_id => $arEventTmp)
					{
						if (array_key_exists("HIDDEN", $arEventTmp) && $arEventTmp["HIDDEN"])
							continue;
						
						$arTitleTmp = array();
						
						if (array_key_exists("ENTITIES", $arEventTmp) && is_array($arEventTmp["ENTITIES"]))
						{
							foreach($arEventTmp["ENTITIES"] as $entity_type_tmp => $arDescTmp)
								if (array_key_exists("TITLE", $arDescTmp) && !in_array($arDescTmp["TITLE"], $arTitleTmp))
									$arTitleTmp[] = $arDescTmp["TITLE"];
						}
						
						if (count($arTitleTmp) > 0)
							$arResult["ActiveFeatures"][$event_id] = implode("/", $arTitleTmp);
					}
				}
			}
		}

		$arSystemEvents = array();
		foreach ($GLOBALS["arSocNetLogEvents"] as $event_id_tmp => $arEventTmp)
		{
			if (array_key_exists("HIDDEN", $arEventTmp) && $arEventTmp["HIDDEN"])
				continue;

			$arTitleTmp = array();
						
			if (array_key_exists("ENTITIES", $arEventTmp) && is_array($arEventTmp["ENTITIES"]))
			{
				foreach($arEventTmp["ENTITIES"] as $entity_type_tmp => $arDescTmp)
					if (array_key_exists("TITLE", $arDescTmp) && !in_array($arDescTmp["TITLE"], $arTitleTmp))
						$arTitleTmp[] = $arDescTmp["TITLE"];
			}
						
			if (count($arTitleTmp) > 0)
				$arSystemEvents[$event_id_tmp] = implode("/", $arTitleTmp);
		}
		
		$arResult["ActiveFeatures"] = array_merge(array("all" => false), $arSystemEvents, $arResult["ActiveFeatures"]);

		if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
		{
			$sUserRole = CSocNetUserToGroup::GetUserRole(
							$GLOBALS["USER"]->GetID(),
							$arParams["GROUP_ID"]
						);

			if (!in_array($sUserRole, array(SONET_ROLES_USER, SONET_ROLES_MODERATOR, SONET_ROLES_OWNER)) && !CSocNetUser::IsCurrentUserModuleAdmin())
				unset($arResult["ActiveFeatures"]["system"]);
		}
	}

	$arResult["IS_FILTERED"] = false;
	if ($arParams["SUBSCRIBE_ONLY"] != "Y" || $GLOBALS["USER"]->IsAuthorized())
	{
		if (($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N") && $arParams["SUBSCRIBE_ONLY"] == "N")
		{
			if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_USER)
			{
				$rsUser = CUser::GetByID($arParams["USER_ID"]);
				if ($arResult["User"] = $rsUser->Fetch())
					$strTitleFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arResult["User"], $bUseLogin);
			}
			elseif ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
				$arResult["Group"] = CSocNetGroup::GetByID($arParams["GROUP_ID"]);			
		}
		
		if ($arParams["SET_TITLE"] == "Y")
		{
			if ($arParams["SUBSCRIBE_ONLY"] == "Y")
				$APPLICATION->SetTitle(GetMessage("SONET_C73_PAGE_TITLE"));
			elseif ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP && $arResult["Group"])
				$APPLICATION->SetTitle($arResult["Group"]["NAME"].": ".GetMessage("SONET_C73_PAGE_TITLE"));	
			elseif ($arParams["ENTITY_TYPE"] == SONET_ENTITY_USER)
				$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C73_PAGE_TITLE"));	
		}
		
		if ($arParams["SET_NAV_CHAIN"] != "N")
		{
			if ($arParams["SUBSCRIBE_ONLY"] == "Y")
				$APPLICATION->AddChainItem(GetMessage("SONET_C73_PAGE_TITLE"));
			elseif ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP && $arResult["Group"])
			{
				$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
				$APPLICATION->AddChainItem($arResult["Group"]["NAME"], $arResult["Urls"]["Group"]);	
				$APPLICATION->AddChainItem(GetMessage("SONET_C73_PAGE_TITLE"));
			}
			elseif ($arParams["ENTITY_TYPE"] == SONET_ENTITY_USER)
			{
				$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["USER_ID"]));
				$APPLICATION->AddChainItem(strTitleFormatted.": ".GetMessage("SONET_C73_PAGE_TITLE"), $arResult["Urls"]["User"]);				
				$APPLICATION->AddChainItem(GetMessage("SONET_C73_PAGE_TITLE"));
			}
		}

		$arResult["Urls"]["ViewAll"] = htmlspecialchars($APPLICATION->GetCurPageParam("flt_all=Y", array("flt_entity_type", "flt_group_id", "flt_user_id", "flt_event_id", "flt_all"))); 
		$arResult["Urls"]["ViewSubscr"] = htmlspecialchars($APPLICATION->GetCurPageParam("", array("flt_entity_type", "flt_group_id", "flt_user_id", "flt_event_id", "flt_all"))); 
		
		if (CBXFeatures::IsFeatureEnabled("Workgroups"))
			$arResult["Urls"]["ViewGroups"] = htmlspecialchars($APPLICATION->GetCurPageParam("flt_entity_type=".SONET_ENTITY_GROUP, array("flt_entity_type", "flt_group_id", "flt_user_id", "flt_event_id", "flt_all")));
		else
			$arResult["Urls"]["ViewGroups"] = "";
		
		$arResult["Urls"]["ViewUsers"] = htmlspecialchars($APPLICATION->GetCurPageParam("flt_entity_type=".SONET_ENTITY_USER, array("flt_entity_type", "flt_group_id", "flt_user_id", "flt_event_id", "flt_all")));

		$arResult["Events"] = false;
		$arResult["EventsNew"] = false;

		$arFilter = array();
		if ($arParams["GROUP_ID"] > 0)
		{
			$arFilter["ENTITY_TYPE"] = SONET_ENTITY_GROUP;
			$arFilter["ENTITY_ID"] = $arParams["GROUP_ID"];
		}
		elseif ($arParams["USER_ID"] > 0)
		{
			$arFilter["ENTITY_TYPE"] = SONET_ENTITY_USER;
			$arFilter["ENTITY_ID"] = $arParams["USER_ID"];
		}
		elseif (StrLen($arParams["ENTITY_TYPE"]) > 0)
			$arFilter["ENTITY_TYPE"] = $arParams["ENTITY_TYPE"];

		if (is_array($arParams["EVENT_ID"]))
		{
			if (!in_array("all", $arParams["EVENT_ID"]))
			{
				$arFilter["EVENT_ID"] = $arParams["EVENT_ID"];
				$arResult["IS_FILTERED"] = true;
			}
		}
		elseif ($arParams["EVENT_ID"] && $arParams["EVENT_ID"] != "all")
		{
			$arFilter["EVENT_ID"] = $arParams["EVENT_ID"];
			$arResult["IS_FILTERED"] = true;
		}

		if (IntVal($arParams["CREATED_BY_ID"]) > 0)
		{
			$arFilter["USER_ID"] = $arParams["CREATED_BY_ID"];
			$arResult["IS_FILTERED"] = true;
		}

		if ($arParams["FLT_ALL"] == "Y")
			$arFilter["ALL"] = "Y";

		if (
			array_key_exists("ENTITY_TYPE", $arFilter) && strlen($arFilter["ENTITY_TYPE"]) > 0 
			&& array_key_exists("ENTITY_ID", $arFilter) && intval($arFilter["ENTITY_ID"]) > 0 
			&& !array_key_exists("EVENT_ID", $arFilter)
		)
		{
			$arFilter["EVENT_ID"] = array();

			foreach($GLOBALS["arSocNetLogEvents"] as $event_id_tmp => $arEventTmp)
			{
				if (
					array_key_exists("HIDDEN", $arEventTmp)
					&& $arEventTmp["HIDDEN"]
				)
					continue;

				$arFilter["EVENT_ID"][] = $event_id_tmp;
			}
			
			$arFeatures = CSocNetFeatures::GetActiveFeatures($arFilter["ENTITY_TYPE"], $arFilter["ENTITY_ID"]);
			foreach($arFeatures as $feature_id)
			{
				if(
					array_key_exists($feature_id, $GLOBALS["arSocNetFeaturesSettings"])
					&& array_key_exists("FULL_SET", $GLOBALS["arSocNetFeaturesSettings"][$feature_id])
				)
				{
					unset($arFilter["EVENT_ID"][$feature_id]);
					$arFilter["EVENT_ID"] = $GLOBALS["arSocNetFeaturesSettings"][$feature_id]["FULL_SET"];
				}
				else
					$arFilter["EVENT_ID"][] = $feature_id;
			}
		}
		
		if (
			!$arFilter["EVENT_ID"] 
			|| (is_array($arFilter["EVENT_ID"]) && count($arFilter["EVENT_ID"]) <= 0)
		)
			unset($arFilter["EVENT_ID"]);

		if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
		{
			$arFilter["SITE_ID"] = SITE_ID;
			if (!array_key_exists("ENTITY_TYPE", $arFilter))
				$arFilter["!ENTITY_TYPE"] = SONET_SUBSCRIBE_ENTITY_USER;
		}
		else
			$arFilter["SITE_ID"] = array(SITE_ID, false);

		if ($arParams["LOG_DATE_DAYS"] > 0)
		{
			if ($arParams["SUBSCRIBE_ONLY"] == "Y")
				$arFilter["LOG_DATE_DAYS"] = $arParams["LOG_DATE_DAYS"];
			else
			{
				$arrAdd = array(
					"DD"	=> -($arParams["LOG_DATE_DAYS"]),
					"MM"	=> 0,
					"YYYY"	=> 0,
					"HH"	=> 0,
					"MI"	=> 0,
					"SS"	=> 0,
				);
				$stmp = AddToTimeStamp($arrAdd, time());				
				$arFilter[">=LOG_DATE"] = ConvertTimeStamp($stmp, "FULL");
			}
		}
	
		if (
			array_key_exists("LOG_DATE_FROM", $arParams) 
			&& strlen(trim($arParams["LOG_DATE_FROM"])) > 0
			&& MakeTimeStamp($arParams["LOG_DATE_FROM"], CSite::GetDateFormat("SHORT")) < time()
		)
		{
			$arFilter[">=LOG_DATE"] = $arParams["LOG_DATE_FROM"];
			$arResult["IS_FILTERED"] = true;
		}
		else
			unset($_REQUEST["flt_date_from"]);
		
		if (
			array_key_exists("LOG_DATE_TO", $arParams) 
			&& strlen(trim($arParams["LOG_DATE_TO"])) > 0
			&& MakeTimeStamp($arParams["LOG_DATE_TO"], CSite::GetDateFormat("SHORT")) < time()
		)
		{
			$arFilter["<=LOG_DATE"] = ConvertTimeStamp(MakeTimeStamp($arParams["LOG_DATE_TO"], CSite::GetDateFormat("SHORT"))+86399, "FULL");
			$arResult["IS_FILTERED"] = true;
		}
		else
		{
			$arFilter["<=LOG_DATE"] = ConvertTimeStamp(time(), "FULL");
			unset($_REQUEST["flt_date_to"]);
		}

			
		if ($arParams["SUBSCRIBE_ONLY"] == "Y")
		{
			foreach($GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"] as $entity_type_tmp => $arEntityTypeTmp)
				if (
					array_key_exists("HAS_MY", $arEntityTypeTmp)
					&& $arEntityTypeTmp["HAS_MY"] == "Y"
					&& array_key_exists("CLASS_MY", $arEntityTypeTmp)
					&& array_key_exists("METHOD_MY", $arEntityTypeTmp)
					&& strlen($arEntityTypeTmp["CLASS_MY"]) > 0
					&& strlen($arEntityTypeTmp["METHOD_MY"]) > 0
					&& method_exists($arEntityTypeTmp["CLASS_MY"], $arEntityTypeTmp["METHOD_MY"])
				)
					$arMyEntities[$entity_type_tmp] = call_user_func(array($arEntityTypeTmp["CLASS_MY"], $arEntityTypeTmp["METHOD_MY"]));

			$arListParams = array(
								"USER_ID" 			=> ($bCurrentUserIsAdmin ? "A" : $GLOBALS["USER"]->GetID()),
								"USE_SUBSCRIBE" 	=> "Y",
								"SUBSCRIBE_USER_ID"	=> $GLOBALS["USER"]->GetID(),
								"MY_ENTITIES"		=> $arMyEntities,
								"MIN_ID_JOIN" 		=> true
							);
							
			if (!$arParams["SHOW_HIDDEN"])
				$arListParams["VISIBLE"] = "Y";
			else
				$arResult["IS_FILTERED"] = true;
				
			$dbEvents = CSocNetLog::GetList(
							array("LOG_DATE"=>"DESC"), 
							$arFilter, 
							false, 
							array(
								"nPageSize"				=> $arParams["PAGE_SIZE"],
								"bDescPageNumbering" 	=> false,
								"bShowAll" 				=> false,
							), 
							array(),
							$arListParams
						);

			$arResult["NAV_STRING"] = $dbEvents->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C73_NAV"), "", false);
			$arResult["PAGE_NUMBER"] = $dbEvents->NavPageNomer;
		}
		else
			$dbEvents = CSocNetLog::GetList(array("LOG_DATE"=>"DESC"), $arFilter, false, false, array(), array("USER_ID" => ($bCurrentUserIsAdmin ? "A" : $GLOBALS["USER"]->GetID())));

		$cnt = 0;

		$arModuleEvents = array();
		$module_events = GetModuleEvents("socialnetwork", "OnSocNetLogFormatEvent");
		while ($arModuleEvent = $module_events->Fetch())
			$arModuleEvents[] = $arModuleEvent;

		while ($arEvents = $dbEvents->GetNext())
		{
			if (intval($arParams["LOG_CNT"]) > 0 && $cnt >= $arParams["LOG_CNT"])
				break;

			if ($arResult["Events"] == false)
				$arResult["Events"] = array();

			if ($arResult["EventsNew"] == false)
				$arResult["EventsNew"] = array();

			$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE_WO_NOBR"];
			
			if (
				array_key_exists($arEvents["EVENT_ID"], $GLOBALS["arSocNetLogEvents"])				
				&& is_array($GLOBALS["arSocNetLogEvents"][$arEvents["EVENT_ID"]])
				&& array_key_exists("CLASS_FORMAT", $GLOBALS["arSocNetLogEvents"][$arEvents["EVENT_ID"]])
				&& array_key_exists("METHOD_FORMAT", $GLOBALS["arSocNetLogEvents"][$arEvents["EVENT_ID"]])
			)
				$arEvents["FIELDS_FORMATTED"] = call_user_func(array($GLOBALS["arSocNetLogEvents"][$arEvents["EVENT_ID"]]["CLASS_FORMAT"], $GLOBALS["arSocNetLogEvents"][$arEvents["EVENT_ID"]]["METHOD_FORMAT"]), $arEvents, $arParams);


			if (!array_key_exists("FIELDS_FORMATTED", $arEvents))
			{
				foreach ($GLOBALS["arSocNetFeaturesSettings"] as $featureID => $arFeature)
				{
					if (array_key_exists("subscribe_events", $arFeature) && is_array($arFeature["subscribe_events"]))
					{
						foreach($arFeature["subscribe_events"] as $event_id_tmp => $arEventTmp)
						{
							if ($event_id_tmp != $arEvents["EVENT_ID"])
								continue;

							if (
								array_key_exists("CLASS_FORMAT", $arEventTmp)
								&& array_key_exists("METHOD_FORMAT", $arEventTmp)
							)
							{
								$arEvents["FIELDS_FORMATTED"] = call_user_func(array($arEventTmp["CLASS_FORMAT"], $arEventTmp["METHOD_FORMAT"]), $arEvents, $arParams);
								break;
							}
						}
					}
				}
			}

			if ($arEvents["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
				$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arEvents["ENTITY_ID"]));
			else
				$path2Entity = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arEvents["ENTITY_ID"]));
				
			$arDateTmp = ParseDateTime((array_key_exists("LOG_DATE_FORMAT", $arEvents) ? $arEvents["LOG_DATE_FORMAT"] : $arEvents["LOG_DATE"]), CSite::GetDateFormat('FULL'));
			$day = IntVal($arDateTmp["DD"]);
			$month = IntVal($arDateTmp["MM"]);
			$year = IntVal($arDateTmp["YYYY"]);
			$dateFormated = $day.' '.ToLower(GetMessage('MONTH_'.$month.'_S')).' '.$year;
//			$timeFormated = $arDateTmp["HH"].':'.$arDateTmp["MI"].':'.$arDateTmp["SS"];
			$timeFormated = $arDateTmp["HH"].':'.$arDateTmp["MI"];

			$arEvents["MESSAGE_FORMAT"] = htmlspecialcharsback($arEvents["MESSAGE"]);
			if (StrLen($arEvents["CALLBACK_FUNC"]) > 0)
			{
				if (StrLen($arEvents["MODULE_ID"]) > 0)
					CModule::IncludeModule($arEvents["MODULE_ID"]);

				$arEvents["MESSAGE_FORMAT"] = call_user_func($arEvents["CALLBACK_FUNC"], $arEvents);
			}

			foreach($arModuleEvents as $arModuleEvent)
				$arEvents = ExecuteModuleEventEx($arModuleEvent, array($arEvents, $arParams));
			
			$arTmpUser = array(
				"NAME" => $arEvents["~USER_NAME"],
				"LAST_NAME" => $arEvents["~USER_LAST_NAME"],
				"SECOND_NAME" => $arEvents["~USER_SECOND_NAME"],
				"LOGIN" => $arEvents["~USER_LOGIN"]
			);
			
			$arTmpEvent = array(
				"ID" => $arEvents["ID"],
				"ENTITY_TYPE" => $arEvents["ENTITY_TYPE"],
				"ENTITY_ID" => $arEvents["ENTITY_ID"],
				"EVENT_ID" => $arEvents["EVENT_ID"],
				"LOG_DATE" => $arEvents["LOG_DATE"],
				"LOG_DATE_FORMAT" => $arEvents["LOG_DATE_FORMAT"],
				"LOG_TIME_FORMAT" => $timeFormated,
				"TITLE_TEMPLATE" => $arEvents["TITLE_TEMPLATE"],
				"TITLE" => $arEvents["TITLE"],
				"TITLE_FORMAT" => CSocNetLog::MakeTitle($arEvents["TITLE_TEMPLATE"], $arEvents["TITLE"], $arEvents["URL"], true),
				"MESSAGE" => $arEvents["MESSAGE"],
				"MESSAGE_FORMAT" => $arEvents["MESSAGE_FORMAT"],
				"URL" => $arEvents["URL"],
				"MODULE_ID" => $arEvents["MODULE_ID"],
				"CALLBACK_FUNC" => $arEvents["CALLBACK_FUNC"],
				"ENTITY_NAME" => (($arEvents["ENTITY_TYPE"] == SONET_ENTITY_GROUP) ? $arEvents["GROUP_NAME"] : CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin)),
				"ENTITY_PATH" => $path2Entity,
			);
			
			if (intval($arEvents["USER_ID"]) > 0)
			{
				$arTmpEvent["TITLE_FORMAT_EXT"] = $arTmpEvent["TITLE_FORMAT"];
				
				$arTmpEvent["CREATED_BY_NAME"] = $arEvents["~CREATED_BY_NAME"];
				$arTmpEvent["CREATED_BY_LAST_NAME"] = $arEvents["~CREATED_BY_LAST_NAME"];
				$arTmpEvent["CREATED_BY_SECOND_NAME"] = $arEvents["~CREATED_BY_SECOND_NAME"];
				$arTmpEvent["CREATED_BY_LOGIN"] = $arEvents["~CREATED_BY_LOGIN"];
				$arTmpEvent["USER_ID"] = $arEvents["USER_ID"];
			}
			else
				$arTmpEvent["TITLE_FORMAT_EXT"] = "";

			if (preg_match("/#USER_NAME#/i".BX_UTF_PCRE_MODIFIER, $arTmpEvent["TITLE_FORMAT"], $res))
			{
				if (intval($arEvents["USER_ID"]) > 0)
				{
					$arTmpCreatedBy = array(
						"NAME" 			=> 	$arEvents["~CREATED_BY_NAME"],
						"LAST_NAME" 	=> 	$arEvents["~CREATED_BY_LAST_NAME"],
						"SECOND_NAME" 	=> 	$arEvents["~CREATED_BY_SECOND_NAME"],
						"LOGIN" 		=> 	$arEvents["~CREATED_BY_LOGIN"]
					);

					$name_formatted = CUser::FormatName(
						$arParams["NAME_TEMPLATE_WO_NOBR"], 
						$arTmpCreatedBy, 
						$bUseLogin
					);
				}
				else
					$name_formatted = GetMessage("SONET_C73_CREATED_BY_ANONYMOUS");

				$arTmpEvent["TITLE_FORMAT"] = str_replace("#USER_NAME#", $name_formatted, $arTmpEvent["TITLE_FORMAT"]);
			}
			
			if ($arEvents["ENTITY_TYPE"] == SONET_ENTITY_USER)
			{
				$arTmpEvent["USER_NAME"] = $arTmpUser["NAME"];
				$arTmpEvent["USER_LAST_NAME"] = $arTmpUser["LAST_NAME"];
				$arTmpEvent["USER_SECOND_NAME"] = $arTmpUser["SECOND_NAME"];
				$arTmpEvent["USER_LOGIN"] = $arTmpUser["LOGIN"];
			}
			
			if (
				strlen($arTmpEvent["TITLE_FORMAT"]) <= 0
				&& strlen($arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["TITLE"]) > 0
			)
				$arTmpEvent["TITLE_FORMAT"] = $arEvents["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["TITLE"];

			$arEvents["FIELDS_FORMATTED"]["LOG_TIME_FORMAT"] = $timeFormated;
			
			$arResult["Events"][$dateFormated][] = $arTmpEvent;
			$arResult["EventsNew"][$dateFormated][] = $arEvents["FIELDS_FORMATTED"];
			$cnt++;
		}
	}
	else
		$arResult["NEED_AUTH"] = "Y";
}
else
	$arResult["NEED_AUTH"] = "Y";

$this->IncludeComponentTemplate();
?>