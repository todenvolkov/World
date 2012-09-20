<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 6;
$arParams["PREVIEW_WIDTH"] = IntVal($arParams["PREVIEW_WIDTH"])>0 ? IntVal($arParams["PREVIEW_WIDTH"]): 100;
$arParams["PREVIEW_HEIGHT"] = IntVal($arParams["PREVIEW_HEIGHT"])>0 ? IntVal($arParams["PREVIEW_HEIGHT"]): 100;
$arParams["PERIOD_DAYS"] = IntVal($arParams["PERIOD_DAYS"])>0 ? IntVal($arParams["PERIOD_DAYS"]): 30;
$arParams["SORT_BY1"] = (strlen($arParams["SORT_BY1"])>0 ? $arParams["SORT_BY1"] : "VIEWS");
$arParams["SORT_ORDER1"] = (strlen($arParams["SORT_ORDER1"])>0 ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = (strlen($arParams["SORT_BY2"])>0 ? $arParams["SORT_BY2"] : "DATE_PUBLISH");
$arParams["SORT_ORDER2"] = (strlen($arParams["SORT_ORDER2"])>0 ? $arParams["SORT_ORDER2"] : "DESC");
$arParams["MESSAGE_LENGTH"] = (IntVal($arParams["MESSAGE_LENGTH"])>0)?$arParams["MESSAGE_LENGTH"]:100;
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["USE_SOCNET"] = ($arParams["USE_SOCNET"] == "Y") ? "Y" : "N";
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	

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

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);
	
$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

$UserGroupID = Array(1);
if($USER->IsAuthorized())
	$UserGroupID[] = 2;

$cache = new CPHPCache;
$cache_id = "blog_last_messages_".serialize($arParams)."_".serialize($UserGroupID)."_".$USER->IsAdmin();
$cache_path = "/".SITE_ID."/blog/popular_posts/";

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$Vars = $cache->GetVars();
	foreach($Vars["arResult"] as $k=>$v)
		$arResult[$k] = $v;
	CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);	
	$cache->Output();
}
else
{
	if ($arParams["CACHE_TIME"] > 0)
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

	$arFilter = Array(
			"<=DATE_PUBLISH" => ConvertTimeStamp(false, "FULL", false),
			">=DATE_PUBLISH" => ConvertTimeStamp(AddToTimeStamp(Array("DD" => "-".$arParams["PERIOD_DAYS"])), "FULL", false),
			"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
			"BLOG_ACTIVE" => "Y",
			"BLOG_GROUP_SITE_ID" => SITE_ID,
			">PERMS" => BLOG_PERMS_DENY,
			">VIEWS" => 0
		);	

	if(strlen($arParams["BLOG_URL"]) > 0)
		$arFilter["BLOG_URL"] = $arParams["BLOG_URL"];
	if(!empty($arParams["GROUP_ID"]))
		$arFilter["BLOG_GROUP_ID"] = $arParams["GROUP_ID"];
	if($USER->IsAdmin())
		unset($arFilter[">PERMS"]);
	
	$arSelectedFields = array("ID", "BLOG_ID", "TITLE", "DATE_PUBLISH", "AUTHOR_ID", "DETAIL_TEXT", "BLOG_ACTIVE", "BLOG_URL", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "AUTHOR_LOGIN", "AUTHOR_NAME", "AUTHOR_LAST_NAME", "AUTHOR_SECOND_NAME", "BLOG_USER_ALIAS", "BLOG_OWNER_ID", "NUM_COMMENTS", "VIEWS", "ATTACH_IMG", "BLOG_SOCNET_GROUP_ID", "CODE");
	
	if(CModule::IncludeModule("socialnetwork") && IntVal($arParams["SOCNET_GROUP_ID"]) <= 0 && IntVal($arParams["USER_ID"]) <= 0 && $arParams["USE_SOCNET"] == "Y")
	{
		unset($arFilter[">PERMS"]);
		$arSelectedFields[] = "SOCNET_BLOG_READ";
		$arFilter["BLOG_USE_SOCNET"] = "Y";
	}
	elseif((IntVal($arParams["SOCNET_GROUP_ID"]) > 0 || IntVal($arParams["USER_ID"]) > 0) && $arParams["USE_SOCNET"] == "Y")
	{
		$user_id = $USER->GetID();
		$arFilterTmp = Array("ACTIVE" => "Y", "GROUP_SITE_ID" => SITE_ID, "USE_SOCNET" => "Y");

		if(IntVal($arParams["SOCNET_GROUP_ID"]) > 0)
			$arFilterTmp["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
		if(IntVal($arParams["USER_ID"]) > 0)
			$arFilterTmp["OWNER_ID"] = $arParams["USER_ID"];
		if(!empty($arParams["GROUP_ID"]))
			$arFilterTmp["GROUP_ID"] = $arParams["GROUP_ID"];

		$perms = BLOG_PERMS_DENY;
		$dbBlog = CBlog::GetList(Array(), $arFilterTmp, false, Array("nTopCount" => 1), Array("ID"));
		if($arBlog = $dbBlog->Fetch())
		{
			if(IntVal($arParams["SOCNET_GROUP_ID"]) > 0)
			{
				$perms = BLOG_PERMS_DENY;
				if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post", CSocNetUser::IsCurrentUserModuleAdmin()) || $APPLICATION->GetGroupRight("blog") >= "W")
					$perms = BLOG_PERMS_FULL;
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "write_post"))
					$perms = BLOG_PERMS_WRITE;
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "view_post"))
					$perms = BLOG_PERMS_READ;
			}
			else
			{
				$perms = BLOG_PERMS_DENY;
				if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "full_post", CSocNetUser::IsCurrentUserModuleAdmin()) || $APPLICATION->GetGroupRight("blog") >= "W" || $arParams["USER_ID"] == $user_id)
					$perms = BLOG_PERMS_FULL;
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "write_post"))
					$perms = BLOG_PERMS_WRITE;
				elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "view_post"))
					$perms = BLOG_PERMS_READ;
			}
			$arFilter["BLOG_ID"] = $arBlog["ID"];
			unset($arFilter[">PERMS"]);
		}
	}

	if(strlen($perms) <= 0 || (!empty($arFilter["BLOG_ID"]) && $perms >= BLOG_PERMS_READ))
	{
		$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);
		if($arParams["MESSAGE_COUNT"]>0)
			$COUNT = Array("nTopCount" => $arParams["MESSAGE_COUNT"]);
		else
			$COUNT = false;

		$arResult = Array();
		$dbPosts = CBlogPost::GetList(
			$SORT,
			$arFilter,
			false,
			$COUNT,
			$arSelectedFields
		);

		$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
		$itemCnt = 0;
		while ($arPost = $dbPosts->GetNext())
		{
			if(IntVal($arPost["ATTACH_IMG"]) <= 0)
			{
				$dbImage = CBlogImage::GetList(Array("ID" => "ASC"), Array("BLOG_ID" => $arPost["BLOG_ID"], "POST_ID" => $arPost["ID"]));
				if($arImage = $dbImage -> Fetch())
				{
					if($file = CBlogTools::ResizeImage($arImage["FILE_ID"], $arParams["PREVIEW_WIDTH"], $arParams["PREVIEW_HEIGHT"]))
					{
						$arFile = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].$file);
						$arFile["MODULE_ID"] = "blog";
						$fileID = CFile::SaveFile($arFile, "blog");
						if(IntVal($fileID) > 0)
						{
							$id = CBlogPost::Update($arPost["ID"], Array("ATTACH_IMG" => $fileID));
							$arPost["IMG"] = CFile::ShowImage($fileID, false, false, 'align="left" hspace="2" vspace="2"');
						}
						
						$dbFile = CFile::GetByID($arImage["FILE_ID"]);
						if($arFile = $dbFile->Fetch())
						{
							if("/".COption::GetOptionString("main", "upload_dir", "upload")."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"] != $file && is_file($file))
								DeleteDirFilesEx($file);
						}
					}
				}
			}
			else
			{
				$arPost["IMG"] = CFile::ShowImage($arPost["ATTACH_IMG"], false, false, 'align="left" hspace="2" vspace="2"');
			}

			$arTmp = $arPost;
			if($arTmp["AUTHOR_ID"] == $arTmp["BLOG_OWNER_ID"])
			{
				$arTmp["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arPost["BLOG_URL"], "user_id" => $arPost["AUTHOR_ID"]));
			}
			else
			{
				$arOwnerBlog = CBlog::GetByOwnerID($arTmp["AUTHOR_ID"], $arParams["GROUP_ID"]);
				if(!empty($arOwnerBlog))
					$arTmp["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arOwnerBlog["URL"], "user_id" => $arPost["AUTHOR_ID"]));
				else
					$arTmp["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arPost["BLOG_URL"], "user_id" => $arPost["AUTHOR_ID"]));
			}

			if(IntVal($arPost["BLOG_SOCNET_GROUP_ID"]) > 0)
				$arTmp["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG_POST"], array("blog" => $arPost["BLOG_URL"], "post_id"=>CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "group_id" => $arPost["BLOG_SOCNET_GROUP_ID"]));
			else
				$arTmp["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arPost["BLOG_URL"], "post_id"=>CBlogPost::GetPostID($arPost["ID"], $arPost["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arPost["BLOG_OWNER_ID"]));
			
			$arTmp["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arPost["AUTHOR_ID"]));
			
			$arTmp["AuthorName"] = CBlogUser::GetUserName($arPost["BLOG_USER_ALIAS"], $arPost["AUTHOR_NAME"], $arPost["AUTHOR_LAST_NAME"], $arPost["AUTHOR_LOGIN"]);

			if($itemCnt==0)
				$arTmp["FIRST"] = "Y";

			$text = preg_replace("#\[img\](.+?)\[/img\]#ie", "", $arPost["~DETAIL_TEXT"]);
			$text = preg_replace("#\[url(.+?)\](.*?)\[/url\]#is", "\\2", $text);
			$text = preg_replace("#\[video(.+?)\](.+?)\[/video\]#ie", "", $text);
			$text = preg_replace("#^(.+?)<cut[\s]*(/>|>).*?$#is", "\\1", $text);
			$text = preg_replace("#^(.+?)\[cut[\s]*(/\]|\]).*?$#is", "\\1", $text);
			$text = preg_replace("#(\[|<)(/?)(b|u|i|list|code|quote|url|img|color|font|/*)(.*?)(\]|>)#is", "", $text);
			$text = TruncateText($text, $arParams["MESSAGE_LENGTH"]);
			$text = $p->convert($text, true, false, array("HTML" => "N", "ANCHOR" => "N", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "LIST" => "N", "SMILES" => "Y", "NL2BR" => "N"));
			$text = CBlogTools::DeleteDoubleBR($text);
			
			$arTmp["TEXT_FORMATED"] = $text;
			$arTmp["DATE_PUBLISH_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arTmp["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
			
			$arResult[] = $arTmp;
			$itemCnt++;
		}
	}
	if ($arParams["CACHE_TIME"] > 0)
		$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
}
$this->IncludeComponentTemplate();
?>