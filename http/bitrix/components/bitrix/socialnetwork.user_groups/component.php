<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = IntVal($GLOBALS["USER"]->GetID());

$arParams["PAGE"] = Trim($arParams["PAGE"]);
if ($arParams["PAGE"] != "group_request_group_search" && $arParams["PAGE"] != "user_groups" && $arParams["PAGE"] != "groups_list")
	$arParams["PAGE"] = "user_groups";

$user4Groups = $arParams["USER_ID"];
$user2Request = 0;
if ($arParams["PAGE"] == "group_request_group_search")
{
	$user4Groups = IntVal($GLOBALS["USER"]->GetID());
	$user2Request = $arParams["USER_ID"];
}

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "user_id";
if(strLen($arParams["GROUP_VAR"])<=0)
	$arParams["GROUP_VAR"] = "group_id";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_EDIT"] = trim($arParams["PATH_TO_GROUP_EDIT"]);
if (strlen($arParams["PATH_TO_GROUP_EDIT"]) <= 0)
	$arParams["PATH_TO_GROUP_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_edit&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_CREATE"] = trim($arParams["PATH_TO_GROUP_CREATE"]);
if (strlen($arParams["PATH_TO_GROUP_CREATE"]) <= 0)
	$arParams["PATH_TO_GROUP_CREATE"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_create&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP_REQUEST_USER"] = trim($arParams["PATH_TO_GROUP_REQUEST_USER"]);
if (strlen($arParams["PATH_TO_GROUP_REQUEST_USER"]) <= 0)
	$arParams["PATH_TO_GROUP_REQUEST_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_request_user&".$arParams["USER_VAR"]."=#user_id#&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_LOG"] = trim($arParams["PATH_TO_LOG"]);
if (strlen($arParams["PATH_TO_LOG"]) <= 0)
	$arParams["PATH_TO_LOG"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=log");

$arParams["ITEMS_COUNT"] = IntVal($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 30;

$arParams["COLUMNS_COUNT"] = IntVal($arParams["COLUMNS_COUNT"]);
if ($arParams["COLUMNS_COUNT"] <= 0)
	$arParams["COLUMNS_COUNT"] = 3;

$arParams["DATE_TIME_FORMAT"] = Trim($arParams["DATE_TIME_FORMAT"]);
$arParams["DATE_TIME_FORMAT"] = ((StrLen($arParams["DATE_TIME_FORMAT"]) <= 0) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arResult["FatalError"] = "";

if ($user4Groups <= 0)
{
	$arResult["FatalError"] = GetMessage("SONET_C36_NO_USER_ID").". ";
}

if (StrLen($arResult["FatalError"]) <= 0)
{
	if ($arParams["PAGE"] == "group_request_group_search")
	{
		if ($user2Request <= 0)
			$arResult["FatalError"] = GetMessage("SONET_C36_NO_USER_ID").". ";
		elseif ($user2Request == $user4Groups)
			$arResult["FatalError"] = GetMessage("SONET_C36_SELF").". ";
	}
}

if (StrLen($arResult["FatalError"]) <= 0)
{
	$dbUser = CUser::GetByID($user4Groups);
	$arResult["User"] = $dbUser->GetNext();

	if (!is_array($arResult["User"]))
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").". ";
	if (CModule::IncludeModule('extranet') && !CExtranet::IsProfileViewable($arResult["User"])) 
		return false;
}

if (StrLen($arResult["FatalError"]) <= 0)
{
	$arResult["UserRequest"] = false;
	if ($user2Request > 0)
	{
		$dbUser = CUser::GetByID($user2Request);
		$arResult["UserRequest"] = $dbUser->GetNext();

		if (!is_array($arResult["UserRequest"]))
			$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").". ";
		if (CModule::IncludeModule('extranet') && !CExtranet::IsProfileViewable($arResult["UserRequest"])) 
			return false;
	}
}

if (StrLen($arResult["FatalError"]) <= 0)
{
	$arGroupID = Array();
	
	$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());

	$arResult["ALLOW_CREATE_GROUP"] = (CSocNetUser::IsCurrentUserModuleAdmin() || $GLOBALS["APPLICATION"]->GetGroupRight("socialnetwork") >= "K");

	if ($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N")
	{
		if (strlen($arParams["NAME_TEMPLATE"]) <= 0)		
			$arParams["NAME_TEMPLATE"] = '#NOBR##NAME# #LAST_NAME##/NOBR#';
				
		$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
			array("#NOBR#", "#/NOBR#"), 
			array("", ""), 
			$arParams["NAME_TEMPLATE"]
		);
		$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
		
		if ($arParams["PAGE"] == "group_request_group_search")
		{
			$arTmpUser = array(
					'NAME' => $arResult["UserRequest"]["~NAME"],
					'LAST_NAME' => $arResult["UserRequest"]["~LAST_NAME"],
					'SECOND_NAME' => $arResult["UserRequest"]["~SECOND_NAME"],
					'LOGIN' => $arResult["UserRequest"]["~LOGIN"],
				);
			$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
		}
		elseif ($arParams["PAGE"] == "user_groups")
		{
			$arTmpUser = array(
					'NAME' => $arResult["User"]["~NAME"],
					'LAST_NAME' => $arResult["User"]["~LAST_NAME"],
					'SECOND_NAME' => $arResult["User"]["~SECOND_NAME"],
					'LOGIN' => $arResult["User"]["~LOGIN"],
				);
			$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
		}
	}
		
	if ($arParams["SET_TITLE"] == "Y")
	{
		if ($arParams["PAGE"] == "group_request_group_search")
			$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C36_PAGE_TITLE"));
		elseif ($arParams["PAGE"] == "user_groups")
			$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C36_PAGE_TITLE1"));
		else
			$APPLICATION->SetTitle(GetMessage("SONET_C36_PAGE_TITLE2"));
	}

	if ($arParams["SET_NAV_CHAIN"] != "N")
	{
		if ($arParams["PAGE"] == "group_request_group_search")
		{
			$APPLICATION->AddChainItem($strTitleFormatted, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["UserRequest"]["ID"])));
			$APPLICATION->AddChainItem(GetMessage("SONET_C36_PAGE_TITLE"));
		}
		elseif ($arParams["PAGE"] == "user_groups")
		{
			$APPLICATION->AddChainItem($strTitleFormatted, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"])));
			$APPLICATION->AddChainItem(GetMessage("SONET_C36_PAGE_TITLE1"));
		}
		else
		{
			$APPLICATION->AddChainItem(GetMessage("SONET_C36_PAGE_TITLE2"));
		}
	}


	if ($arResult["CurrentUserPerms"] && $arResult["CurrentUserPerms"]["Operations"]["viewprofile"] && $arResult["CurrentUserPerms"]["Operations"]["viewgroups"])
	{
		$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false);
		$arNavigation = CDBResult::GetNavParams($arNavParams);

		$arResult["Urls"]["GroupsAdd"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_CREATE"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["LogGroups"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG"], array());
		$arResult["Urls"]["LogGroups"] .= ((StrPos($arResult["Urls"]["LogGroups"], "?") !== false) ? "&" : "?")."flt_entity_type=".SONET_ENTITY_GROUP;
		$arResult["CanViewLog"] = ($arResult["User"]["ID"] == $GLOBALS["USER"]->GetID());

		$arResult["Groups"] = false;

		$arGroupFilter = array(
			"USER_ID" => $arResult["User"]["ID"],
			"<=ROLE" => SONET_ROLES_USER,
			"GROUP_SITE_ID" => SITE_ID,
			"GROUP_ACTIVE" => "Y"
		);
		if (COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y")
			$arGroupFilter["GROUP_CLOSED"] = "N";

		if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite()):

			if (!$GLOBALS["USER"]->IsAdmin() && !CSocNetUser::IsCurrentUserModuleAdmin()):

				$arGroupFilterMy = array(
					"USER_ID" => $GLOBALS["USER"]->GetID(),
					"<=ROLE" => SONET_ROLES_USER,
					"GROUP_SITE_ID" => SITE_ID,
					"GROUP_ACTIVE" => "Y"
				);
	
				$dbGroups = CSocNetUserToGroup::GetList(
					array("GROUP_NAME" => "ASC"),
					$arGroupFilterMy,
					false,
					false,
					array("GROUP_ID")
				);

				$arMyGroups = array();
					while ($arGroups = $dbGroups->GetNext())
						$arMyGroups[] = $arGroups["GROUP_ID"];

				if (count($arMyGroups) <= 0)
					$bNoMyGroups = true;

				$arGroupFilter["GROUP_ID"] = $arMyGroups;

			endif;

		else:
			if (!$arResult["CurrentUserPerms"]["IsCurrentUser"] && !CSocNetUser::IsCurrentUserModuleAdmin())
				$arGroupFilter["GROUP_VISIBLE"] = "Y";
		endif;


		if (!$bNoMyGroups):

			$dbGroups = CSocNetUserToGroup::GetList(
				array("GROUP_NAME" => "ASC"),
				$arGroupFilter,
				false,
				$arNavParams,
				array("ID", "GROUP_ID", "GROUP_NAME", "GROUP_DESCRIPTION", "GROUP_IMAGE_ID", "GROUP_VISIBLE", "GROUP_OWNER_ID", "GROUP_INITIATE_PERMS", "GROUP_OPENED")
			);
			if ($dbGroups)
			{
				$arResult["Groups"] = array();
	
				$arResult["Groups"]["List"] = false;
				while ($arGroups = $dbGroups->GetNext())
				{
					if ($arResult["Groups"]["List"] == false)
						$arResult["Groups"]["List"] = array();

					$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroups["GROUP_ID"]));

					if (intval($arGroups["GROUP_IMAGE_ID"]) <= 0)
						$arGroups["GROUP_IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);
					
					$arImage = CSocNetTools::InitImage($arGroups["GROUP_IMAGE_ID"], 150, "/bitrix/images/socialnetwork/nopic_group_150.gif", 150, $pu, true);

					if ($arParams["PAGE"] == "group_request_group_search")
					$arCurrentUserPerms4Group = CSocNetUserToGroup::InitUserPerms($arResult["User"]["ID"], array("ID" => $arGroups["GROUP_ID"], "OWNER_ID" => $arGroups["GROUP_OWNER_ID"], "INITIATE_PERMS" => $arGroups["GROUP_INITIATE_PERMS"], "VISIBLE" => $arGroups["GROUP_VISIBLE"], "OPENED" => $arGroups["GROUP_OPENED"]), CSocNetUser::IsCurrentUserModuleAdmin());

					$arResult["Groups"]["List"][] = array(
						"ID" => $arGroups["ID"],
						"GROUP_ID" => $arGroups["GROUP_ID"],
						"GROUP_NAME" => $arGroups["GROUP_NAME"],
						"GROUP_DESCRIPTION" => SubStr($arGroups["GROUP_DESCRIPTION"], 0, 50)."...",
						"GROUP_PHOTO" => $arGroups["GROUP_IMAGE_ID"],
						"GROUP_PHOTO_FILE" => $arImage["FILE"],
						"GROUP_PHOTO_IMG" => $arImage["IMG"],
						"GROUP_URL" => $pu,
						"GROUP_REQUEST_USER_URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUEST_USER"], array("group_id" => $arGroups["GROUP_ID"], "user_id" => $arResult["UserRequest"]["ID"])),
						"CAN_INVITE2GROUP" => (($arParams["PAGE"] != "user_groups") ? $arCurrentUserPerms4Group && $arCurrentUserPerms4Group["UserCanInitiate"] : false),
					);
					
					$arGroupID[] = $arGroups["GROUP_ID"];
				}
				$arResult["NAV_STRING"] = $dbGroups->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C36_NAV"), "", false);
			}

		endif;

	}

	if (!empty($arGroupID))
	{
		$dbGroup = CSocNetGroup::GetList(
			array("ID" => "DESC"),
			array("ID" => $arGroupID)
		);
		while ($arGroup = $dbGroup->GetNext())
		{
			$key = array_search($arGroup["ID"], $arGroupID);
			if ($key !== false)
				$arResult["Groups"]["List"][$key]["FULL"] = $arGroup;

			$arResult["Groups"]["List"][$key]["FULL"]["DATE_CREATE_FORMATTED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arGroup["DATE_CREATE"], CSite::GetDateFormat("FULL")));
			$arResult["Groups"]["List"][$key]["FULL"]["DATE_UPDATE_FORMATTED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arGroup["DATE_UPDATE"], CSite::GetDateFormat("FULL")));
			$arResult["Groups"]["List"][$key]["FULL"]["DATE_ACTIVITY_FORMATTED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arGroup["DATE_ACTIVITY"], CSite::GetDateFormat("FULL")));
		}
	}
	
	if (CSocNetUser::IsCurrentUserModuleAdmin() && CModule::IncludeModule('intranet')):
		global $INTRANET_TOOLBAR;
	
		$INTRANET_TOOLBAR->AddButton(array(
			'HREF' => "/bitrix/admin/socnet_subject.php?lang=".LANGUAGE_ID,
			"TEXT" => GetMessage('SONET_C36_EDIT_ENTRIES'),
			'ICON' => 'settings',
			"SORT" => 1000,
		));
	endif;

}
$this->IncludeComponentTemplate();
?>