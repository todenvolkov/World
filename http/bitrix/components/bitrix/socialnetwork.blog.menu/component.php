<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";

$arParams["PATH_TO_DRAFT"] = trim($arParams["PATH_TO_DRAFT"]);
if(strlen($arParams["PATH_TO_DRAFT"])<=0)
	$arParams["PATH_TO_DRAFT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=draft&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"])<=0)
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");
	
$arParams["PATH_TO_MODERATION"] = trim($arParams["PATH_TO_MODERATION"]);
if(strlen($arParams["PATH_TO_MODERATION"])<=0)
	$arParams["PATH_TO_MODERATION"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=moderation&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);

$user_id = $USER->GetID();
$arParams["SOCNET_GROUP_ID"] = IntVal($arParams["SOCNET_GROUP_ID"]);

$bGroupMode = false;
if(IntVal($arParams["SOCNET_GROUP_ID"]) > 0)
	$bGroupMode = true;
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

if(IntVal($user_id)>0)
{
	CBlogUser::SetLastVisit();
}

$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

if($bGroupMode)
{
	$arGroup = CSocNetGroup::GetByID($arParams["SOCNET_GROUP_ID"]);
	if(!empty($arGroup))
	{
		if($bGroupMode && CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog"))
		{
			$arResult["BLOG"] = CBlog::GetBySocNetGroupID($arParams["SOCNET_GROUP_ID"], $arParams["GROUP_ID"]);
			$arResult["PostPerm"] = BLOG_PERMS_DENY;
			
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post", $bCurrentUserIsAdmin) || $APPLICATION->GetGroupRight("blog") >= "W")
				$arResult["PostPerm"] = BLOG_PERMS_FULL;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "moderate_post", $bCurrentUserIsAdmin))
				$arResult["PostPerm"] = BLOG_PERMS_MODERATE;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "write_post", $bCurrentUserIsAdmin))
				$arResult["PostPerm"] = BLOG_PERMS_WRITE;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "premoderate_post", $bCurrentUserIsAdmin))
				$arResult["PostPerm"] = BLOG_PERMS_PREMODERATE;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "view_post", $bCurrentUserIsAdmin))
				$arResult["PostPerm"] = BLOG_PERMS_READ;

			if ($arParams["SET_NAV_CHAIN"] != "N" || $arParams["SET_TITLE"] != "N")
			{
				$feature = "blog";
				$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_GROUP, $arGroup["ID"]);		
				$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && StrLen($arEntityActiveFeatures[$feature]) > 0) ? $arEntityActiveFeatures[$feature] : GetMessage("BM_BLOG_CHAIN_GROUP"));
			}
		
			if ($arParams["SET_NAV_CHAIN"] != "N")
			{
				$APPLICATION->AddChainItem($arGroup["NAME"], CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroup["ID"])));
							
				$APPLICATION->AddChainItem($strFeatureTitle, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG"], array("blog" => $arResult["BLOG"]["URL"], "group_id" => $arParams["SOCNET_GROUP_ID"])));
			}
			if ($arParams["SET_TITLE"] != "N")
				$APPLICATION->SetTitle($arGroup["NAME"].": ".$strFeatureTitle);
		}
	}
}
elseif(IntVal($arParams["USER_ID"]) > 0)
{
	$dbUser = CUser::GetByID($arParams["USER_ID"]);
	$arUser = $dbUser->Fetch();
	if(!empty($arUser))
	{
		if(CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog"))
		{
			$blogOwnerID = $arParams["USER_ID"];
			$arResult["BLOG"] = CBlog::GetByOwnerID($arParams["USER_ID"], $arParams["GROUP_ID"]);
			$arResult["PostPerm"] = BLOG_PERMS_DENY;
		
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "full_post", $bCurrentUserIsAdmin) || $APPLICATION->GetGroupRight("blog") >= "W" || $blogOwnerID == $user_id)
				$arResult["PostPerm"] = BLOG_PERMS_FULL;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "moderate_post", $bCurrentUserIsAdmin))
				$arResult["PostPerm"] = BLOG_PERMS_MODERATE;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "write_post", $bCurrentUserIsAdmin))
				$arResult["PostPerm"] = BLOG_PERMS_WRITE;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "premoderate_post", $bCurrentUserIsAdmin))
				$arResult["PostPerm"] = BLOG_PERMS_PREMODERATE;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "view_post", $bCurrentUserIsAdmin))
				$arResult["PostPerm"] = BLOG_PERMS_READ;

			if ($arParams["SET_TITLE"] != "N" || $arParams["SET_NAV_CHAIN"] != "N")
			{
				$feature = "blog";
				$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_USER, $arParams["USER_ID"]);		
				$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && StrLen($arEntityActiveFeatures[$feature]) > 0) ? $arEntityActiveFeatures[$feature] : GetMessage("BM_BLOG_CHAIN_USER"));
			
				if (strlen($arParams["NAME_TEMPLATE"]) <= 0)		
					$arParams["NAME_TEMPLATE"] = '#NOBR##NAME# #LAST_NAME##/NOBR#';
							
				$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
					array("#NOBR#", "#/NOBR#"), 
					array("", ""), 
					$arParams["NAME_TEMPLATE"]
				);

				$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;
				$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arUser, $bUseLogin);	
			}

			if ($arParams["SET_NAV_CHAIN"] != "N")
			{
				$APPLICATION->AddChainItem($strTitleFormatted, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"])));
				$APPLICATION->AddChainItem($strFeatureTitle, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("user_id" => $arUser["ID"], "blog" => $arResult["BLOG"]["URL"])));
			}
		
			if ($arParams["SET_TITLE"] != "N")
				$APPLICATION->SetTitle($strTitleFormatted.": ".$strFeatureTitle);
		}
	}
}

if(!empty($arResult["BLOG"]))
{
	if($arResult["PostPerm"] >= BLOG_PERMS_WRITE)
	{
		$arResult["urlToDraft"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DRAFT"], array("blog" => $arResult["BLOG"]["URL"], "user_id" => $blogOwnerID, "group_id" => $arParams["SOCNET_GROUP_ID"]));
		$dbPost = CBlogPost::GetList(
			array(),
			Array(
					"BLOG_ID" => $arResult["BLOG"]["ID"],
					"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_DRAFT,
					"AUTHOR_ID" => $user_id,
			),
			Array("COUNT" => "ID"),
			false,
			Array("ID", "BLOG_ID")
		);
		if($arPost = $dbPost->Fetch())
			$arResult["CntToDraft"] = $arPost["ID"];

		if($arResult["PostPerm"] >= BLOG_PERMS_MODERATE)
		{
			$arResult["urlToModeration"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MODERATION"], array("blog" => $arResult["BLOG"]["URL"], "user_id" => $blogOwnerID, "group_id" => $arParams["SOCNET_GROUP_ID"]));
			$dbPost = CBlogPost::GetList(
				array(),
				Array(
						"BLOG_ID" => $arResult["BLOG"]["ID"],
						"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY
				),
				Array("COUNT" => "ID"),
				false,
				Array("ID", "BLOG_ID")
			);
			if($arPost = $dbPost->Fetch())
				$arResult["CntToModerate"] = $arPost["ID"];
		}
	}
}

if($arResult["PostPerm"] >= BLOG_PERMS_PREMODERATE)
	$arResult["urlToNewPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arResult["BLOG"]["URL"], "post_id" => "new", "user_id" => $blogOwnerID, "group_id" => $arParams["SOCNET_GROUP_ID"]));

$this->IncludeComponentTemplate();
?>