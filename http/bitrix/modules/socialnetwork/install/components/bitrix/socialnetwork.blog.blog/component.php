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

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 20;
$arParams["SORT_BY1"] = (strlen($arParams["SORT_BY1"])>0 ? $arParams["SORT_BY1"] : "DATE_PUBLISH");
$arParams["SORT_ORDER1"] = (strlen($arParams["SORT_ORDER1"])>0 ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = (strlen($arParams["SORT_BY2"])>0 ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = (strlen($arParams["SORT_ORDER2"])>0 ? $arParams["SORT_ORDER2"] : "DESC");

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["YEAR"] = (IntVal($arParams["YEAR"])>0 ? IntVal($arParams["YEAR"]) : false);
$arParams["MONTH"] = (IntVal($arParams["MONTH"])>0 ? IntVal($arParams["MONTH"]) : false);
$arParams["DAY"] = (IntVal($arParams["DAY"])>0 ? IntVal($arParams["DAY"]) : false);
$arParams["CATEGORY_ID"] = (IntVal($arParams["CATEGORY_ID"])>0 ? IntVal($arParams["CATEGORY_ID"]) : false);
$arParams["NAV_TEMPLATE"] = (strlen($arParams["NAV_TEMPLATE"])>0 ? $arParams["NAV_TEMPLATE"] : "");
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
$arParams["SOCNET_GROUP_ID"] = IntVal($arParams["SOCNET_GROUP_ID"]);

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
{
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	$arParams["CACHE_TIME_LONG"] = intval($arParams["CACHE_TIME_LONG"]);
	if(IntVal($arParams["CACHE_TIME_LONG"]) <= 0 && IntVal($arParams["CACHE_TIME"]) > 0)
		$arParams["CACHE_TIME_LONG"] = $arParams["CACHE_TIME"];

}
else
{
	$arParams["CACHE_TIME"] = 0;	
	$arParams["CACHE_TIME_LONG"] = 0;

}
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");

$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);
$arSelectFields = Array("ID", "NAME", "DESCRIPTION", "URL", "DATE_CREATE", "DATE_UPDATE", "ACTIVE", "OWNER_ID", "OWNER_NAME", "LAST_POST_DATE", "LAST_POST_ID", "BLOG_USER_AVATAR", "BLOG_USER_ALIAS");

CpageOption::SetOptionString("main", "nav_page_in_session", "N");

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if(strlen($arParams["PATH_TO_BLOG_CATEGORY"])<=0)
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#");
	
$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"])<=0)
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
	
$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);

$bGroupMode = false;
if(IntVal($arParams["SOCNET_GROUP_ID"]) > 0)
	$bGroupMode = true;
if (($bGroupMode && CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog")) ||CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog"))
{
	if(strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/i", $arParams["FILTER_NAME"]))
	{
		$arFilter = array();
	}
	else
	{
		global $$arParams["FILTER_NAME"];
		$arFilter = ${$arParams["FILTER_NAME"]};
		if(!is_array($arFilter))
			$arFilter = array();
	}


	$arResult["ERROR_MESSAGE"] = Array();
	$arResultNFCache["OK_MESSAGE"] = Array();
	$arResultNFCache["ERROR_MESSAGE"] = Array();
	if(IntVal($arParams["USER_ID"]) > 0 || $bGroupMode)
	{
		$arFilterblg = Array(
		    "ACTIVE" => "Y",
			"GROUP_ID" => $arParams["GROUP_ID"],
			"GROUP_SITE_ID" => SITE_ID,
			"USE_SOCNET" => "Y",
			);
		if($bGroupMode)
			$arFilterblg["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
		else
			$arFilterblg["OWNER_ID"] = $arParams["USER_ID"];
		$dbBl = CBlog::GetList(Array(), $arFilterblg);
		$arBlog = $dbBl ->Fetch();

		$arResult["BLOG"] = $arBlog;
		$user_id = IntVal($USER->GetID());
		
		$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
		
		if (array_key_exists("USE_SHARE", $arParams) && $arParams["USE_SHARE"] == "Y")
			if (!CSocNetFeaturesPerms::CanPerformOperation(false, ($bGroupMode ? SONET_ENTITY_GROUP : SONET_ENTITY_USER), ($bGroupMode ? $arParams["SOCNET_GROUP_ID"] : $arParams["USER_ID"]), "blog", "view_post"))
				$arParams["USE_SHARE"] = "N";

		if($bGroupMode)
		{
			$arResult["perms"] = BLOG_PERMS_DENY;
			
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post", $bCurrentUserIsAdmin) || $APPLICATION->GetGroupRight("blog") >= "W")
				$arResult["perms"] = BLOG_PERMS_FULL;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "moderate_post", $bCurrentUserIsAdmin))
				$arResult["perms"] = BLOG_PERMS_MODERATE;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "write_post", $bCurrentUserIsAdmin))
				$arResult["perms"] = BLOG_PERMS_WRITE;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "premoderate_post", $bCurrentUserIsAdmin))
				$arResult["perms"] = BLOG_PERMS_PREMODERATE;		
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "view_post", $bCurrentUserIsAdmin))
				$arResult["perms"] = BLOG_PERMS_READ;
		}
		else
		{
			$arResult["perms"] = BLOG_PERMS_DENY;
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "full_post", $bCurrentUserIsAdmin) || $APPLICATION->GetGroupRight("blog") >= "W" || $arParams["USER_ID"] == $user_id)
				$arResult["perms"] = BLOG_PERMS_FULL;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "moderate_post", $bCurrentUserIsAdmin))
				$arResult["perms"] = BLOG_PERMS_MODERATE;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "write_post", $bCurrentUserIsAdmin))
				$arResult["perms"] = BLOG_PERMS_WRITE;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "premoderate_post", $bCurrentUserIsAdmin))
				$arResult["perms"] = BLOG_PERMS_PREMODERATE;		
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "view_post", $bCurrentUserIsAdmin))
				$arResult["perms"] = BLOG_PERMS_READ;
		}

		
		//Message delete
		if (IntVal($_GET["del_id"]) > 0)
		{
		
			if($_GET["success"] == "Y") 
			{
				$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DELED");
			}
			else
			{
				if (check_bitrix_sessid() && CBlogSoNetPost::CanUserDeletePost(IntVal($_GET["del_id"]), $user_id, $arParams["USER_ID"], $arParams["SOCNET_GROUP_ID"]))
				{
					$DEL_ID = IntVal($_GET["del_id"]);
					if(CBlogPost::GetByID($DEL_ID))
					{
						if (CBlogPost::Delete($DEL_ID))
						{
							if($bGroupMode)
								CSocNetGroup::SetLastActivity($arParams["SOCNET_GROUP_ID"]);
							BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/first_page/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/pages/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/calendar/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/post/".$DEL_ID."/");
							BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
							BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
							BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
							BXClearCache(True, "/".SITE_ID."/blog/last_comments/");

							BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/trackback/".$DEL_ID."/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/rss_out/");
							BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/rss_all/");
							BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
							BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
							BXClearCache(True, "/".SITE_ID."/blog/last_messages_list_extranet/");
							

							LocalRedirect($APPLICATION->GetCurPageParam("del_id=".$DEL_ID."&success=Y", Array("del_id", "sessid", "success")));
						}
						else
							$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DEL_ERROR");
					}
				}
				else
					$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DEL_NO_RIGHTS");
			}
		}
		elseif (IntVal($_GET["hide_id"]) > 0)
		{
			if(!empty($arResult["BLOG"]))
			{
				if($_GET["success"] == "Y") 
				{
					$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_HIDED");
				}
				else
				{
					if (check_bitrix_sessid())
					{
						$hide_id = IntVal($_GET["hide_id"]);
						if($arResult["perms"]>=BLOG_PERMS_MODERATE)
						{
							if(CBlogPost::GetByID($hide_id))
							{
								if(CBlogPost::Update($hide_id, Array("PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY)))
								{
									BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/first_page/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/pages/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/calendar/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/post/".$hide_id."/");
									BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
									BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
									BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
									BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
									BXClearCache(True, "/".SITE_ID."/blog/groups/".$arResult["BLOG"]["GROUP_ID"]."/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/trackback/".$hide_id."/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/rss_out/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arResult["BLOG"]["URL"]."/rss_all/");
									BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
									BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
									BXClearCache(True, "/".SITE_ID."/blog/last_messages_list_extranet/");
									

									LocalRedirect($APPLICATION->GetCurPageParam("hide_id=".$hide_id."&success=Y", Array("del_id", "sessid", "success", "hide_id")));
								}
								else
									$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_HIDE_ERROR");
							}
						}
						else
							$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_HIDE_NO_RIGHTS");
					}
				}
			}
			else
			{
				$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
				CHTTP::SetStatus("404 Not Found");
			}

		}			
		if(!isset($_GET["PAGEN_1"]) || IntVal($_GET["PAGEN_1"])<1)
		{
			$CACHE_TIME = $arParams["CACHE_TIME"];
			$cache_path = "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/";
		}
		else
		{
			$CACHE_TIME = $arParams["CACHE_TIME_LONG"];
			$cache_path = "/".SITE_ID."/blog/".$arBlog["URL"]."/pages/".IntVal($_GET["PAGEN_1"])."/";
		}
		
		$cache = new CPHPCache;
		$cache_id = "blog_blog_message_".serialize($arParams)."_".CDBResult::NavStringForCache($arParams["MESSAGE_COUNT"])."_".$arResult["perms"];

		if ($CACHE_TIME > 0 && $cache->InitCache($CACHE_TIME, $cache_id, $cache_path))
		{
			$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvscript.js"></script>', true);
			$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/silverlight.js"></script>', true); 
			$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js"></script>', true);
			$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/mediaplayer/flvscript.js"></script>', true);
			$Vars = $cache->GetVars();
			foreach($Vars["arResult"] as $k=>$v)
				$arResult[$k] = $v;
			CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);	
			$cache->Output();
		}
		else
		{
			if ($CACHE_TIME > 0)
				$cache->StartDataCache($CACHE_TIME, $cache_id, $cache_path);
			
			if(!empty($arBlog) && $arBlog["ACTIVE"] == "Y")
			{
					$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
					if($arGroup["SITE_ID"] == SITE_ID)
					{
						$arBlog["Group"] = $arGroup;
						$arResult["BLOG"] = $arBlog;
						if($arResult["perms"] >= BLOG_PERMS_READ)
						{
							$arResult["enable_trackback"] = COption::GetOptionString("blog","enable_trackback", "Y");
							
							$arFilter["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_PUBLISH;
							$arFilter["BLOG_ID"] = $arBlog["ID"];
							
							if($arParams["YEAR"] && $arParams["MONTH"] && $arParams["DAY"])
							{
								$from = mktime(0, 0, 0, $arParams["MONTH"], $arParams["DAY"], $arParams["YEAR"]);
								$to = mktime(0, 0, 0, $arParams["MONTH"], ($arParams["DAY"]+1), $arParams["YEAR"]);
								if($to>time())
									$to = time();
								$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
								$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
							}
							elseif($arParams["YEAR"] && $arParams["MONTH"])
							{
								$from = mktime(0, 0, 0, $arParams["MONTH"], 1, $arParams["YEAR"]);
								$to = mktime(0, 0, 0, ($arParams["MONTH"]+1), 1, $arParams["YEAR"]);
								if($to>time())
									$to = time();
								$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
								$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
							}
							elseif($arParams["YEAR"])
							{
								$from = mktime(0, 0, 0, 1, 1, $arParams["YEAR"]);
								$to = mktime(0, 0, 0, 1, 1, ($arParams["YEAR"]+1));
								if($to>time())
									$to = time();
								$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
								$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
							}
							else
								$arFilter["<=DATE_PUBLISH"] = ConvertTimeStamp(false, "FULL"); 
								
							if(IntVal($arParams["CATEGORY_ID"])>0)
							{
								$arFilter["CATEGORY_ID_F"] = $arParams["CATEGORY_ID"];
								if($arParams["SET_TITLE"] == "Y")
								{
									$arCat = CBlogCategory::GetByID($arFilter["CATEGORY_ID"]);
									$arResult["title"]["category"] = CBlogTools::htmlspecialcharsExArray($arCat);
								}

							}

							$arResult["filter"] = $arFilter;
							$arFilter["BLOG_USE_SOCNET"] = "Y";

							$dbPost = CBlogPost::GetList(
								$SORT,
								$arFilter,
								false,
								array("bDescPageNumbering"=>true, "nPageSize"=>$arParams["MESSAGE_COUNT"], "bShowAll" => false),
								array("ID", "TITLE", "BLOG_ID", "AUTHOR_ID", "DETAIL_TEXT", "DETAIL_TEXT_TYPE", "DATE_CREATE", "DATE_PUBLISH", "KEYWORDS", "PUBLISH_STATUS", "ATRIBUTE", "ATTACH_IMG", "ENABLE_TRACKBACK", "ENABLE_COMMENTS", "VIEWS", "NUM_COMMENTS", "NUM_TRACKBACKS", "CATEGORY_ID")
							);

							$arResult["NAV_STRING"] = $dbPost->GetPageNavString(GetMessage("MESSAGE_COUNT"), $arParams["NAV_TEMPLATE"]);
							$arResult["POST"] = Array();
						$arResult["IDS"] = Array();
							$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
							
							while($arPost = $dbPost->Fetch())
							{
								$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
								
								$arPost["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array( "user_id" => $arPost["AUTHOR_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
								
								$arPost["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id"=>$arPost["ID"], "user_id" => $arParams["USER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
								$arPost["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arPost["AUTHOR_ID"]));

								$arImages = array();
								$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost['ID'], "BLOG_ID"=>$arBlog['ID']));
								while ($arImage = $res->Fetch())
									$arImages[$arImage['ID']] = $arImage['FILE_ID'];

								if($arPost["DETAIL_TEXT_TYPE"] == "html" && COption::GetOptionString("blog","allow_html", "N") == "Y")
								{
									$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
									if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
										$arAllow["VIDEO"] = "N";
									$arPost["TEXT_FORMATED"] = $p->convert($arPost["~DETAIL_TEXT"], true, $arImages, $arAllow);
								}
								else
								{
									$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
									if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
										$arAllow["VIDEO"] = "N";
									$arPost["TEXT_FORMATED"] = $p->convert($arPost["~DETAIL_TEXT"], true, $arImages, $arAllow);
								}

								$arPost["IMAGES"] = $arImages;
								
								$arPost["BlogUser"] = CBlogUser::GetByID($arPost["AUTHOR_ID"], BLOG_BY_USER_ID); 
								$arPost["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arPost["BlogUser"]);
								
								$dbUser = CUser::GetByID($arPost["AUTHOR_ID"]);
								$arPost["arUser"] = $dbUser->GetNext();
								$arPost["AuthorName"] = CBlogUser::GetUserName($arPost["BlogUser"]["ALIAS"], $arPost["arUser"]["NAME"], $arPost["arUser"]["LAST_NAME"], $arPost["arUser"]["LOGIN"]);
								
								if($arResult["perms"]>=BLOG_PERMS_FULL || ($arResult["perms"]>=BLOG_PERMS_WRITE && $arPost["AUTHOR_ID"] == $user_id))
									$arPost["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id"=>$arPost["ID"], "user_id" => $arParams["USER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
								
								if($arResult["perms"]>=BLOG_PERMS_MODERATE)
									$arPost["urlToHide"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("hide_id=".$arPost["ID"], Array("del_id", "sessid", "success", "hide_id")));
								
								if($arResult["perms"] >= BLOG_PERMS_FULL)
									$arPost["urlToDelete"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("del_id=".$arPost["ID"], Array("del_id", "sessid", "success", "hide_id")));
									
								if (preg_match("/(\[CUT\])/i",$arPost['DETAIL_TEXT']) || preg_match("/(<CUT>)/i",$arPost['DETAIL_TEXT']))
									$arPost["CUT"] = "Y";
								
								if(strlen($arPost["CATEGORY_ID"])>0)
								{
									$arCategory = explode(",",$arPost["CATEGORY_ID"]);
									foreach($arCategory as $v)
									{
										if(IntVal($v)>0)
										{
											$arCatTmp = CBlogTools::htmlspecialcharsExArray(CBlogCategory::GetByID($v));
											$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arBlog["URL"], "category_id" => $v, "group_id" => $arParams["SOCNET_GROUP_ID"], "user_id" => $arParams["USER_ID"]));
											$arPost["CATEGORY"][] = $arCatTmp;
										}
									}
								}
							
								$arPost["POST_PROPERTIES"] = array("SHOW" => "N");
					
								if (!empty($arParams["POST_PROPERTY_LIST"]))
								{
									$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $arPost["ID"], LANGUAGE_ID);
					
									if (count($arParams["POST_PROPERTY_LIST"]) > 0)
									{
										foreach ($arPostFields as $FIELD_NAME => $arPostField)
										{
											if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY_LIST"]))
												continue;
											$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
											$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
											$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
											$arPost["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
										}
									}
									if (!empty($arPost["POST_PROPERTIES"]["DATA"]))
										$arPost["POST_PROPERTIES"]["SHOW"] = "Y";
								}
								$arPost["DATE_PUBLISH_FORMATED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arPost["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
								$arResult["POST"][] = $arPost;
							$arResult["IDS"][] = $arPost["ID"];
							}
						}
					}
					else
						$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
			}
			else
				$arResult["POST"] = Array();
			
			if ($CACHE_TIME > 0)
				$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
		}
	if($arParams["SHOW_RATING"] == "Y" && !empty($arResult["IDS"]))
		$arResult['RATING'] = CRatings::GetRatingVoteResult('BLOG_POST', $arResult["IDS"]);

		if(!empty($arResult["BLOG"]) && $arResult["perms"] < BLOG_PERMS_READ)
		{
			$arResult["MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_FRIENDS_ONLY");
		}
	}
	else
	{
		$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
		CHTTP::SetStatus("404 Not Found");
	}


	if(!empty($arResult["ERROR_MESSAGE"]))
	{
		foreach($arResult["ERROR_MESSAGE"] as $val)
		{
			if(!in_array($val, $arResultNFCache["ERROR_MESSAGE"]))
				$arResultNFCache["ERROR_MESSAGE"][] = $val;
		}
	}
	if(!empty($arResult["OK_MESSAGE"]))
	{
		foreach($arResult["OK_MESSAGE"] as $val)
		{
			if(!in_array($val, $arResultNFCache["OK_MESSAGE"]))
				$arResultNFCache["OK_MESSAGE"][] = $val;
		}
	}
	$arResult = array_merge($arResult, $arResultNFCache);
}
else
{
	$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_SONET_MODULE_NOT_AVAIBLE");
}

$this->IncludeComponentTemplate();
?>