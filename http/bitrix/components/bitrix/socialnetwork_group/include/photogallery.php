<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/../lang/".LANGUAGE_ID."/include/photogallery.php")));
__IncludeLang($file);

$object = (strPos($componentPage, "group_photo")!== false ? "group" : "user");
$arParams["PHOTO_COMMENTS_TYPE"] = "forum";
$arParams["SHOW_LINK_TO_FORUM"] = "N";
$arParams["PHOTO_PREORDER"] = "Y";
$arParams["PHOTO_GALLERY_AVATAR_THUMBS_SIZE"] = 150;
$arParams["PHOTO_GALLERY_AVATAR_SIZE"] = 150;
$arParams["GALLERY_AVATAR_SIZE"] = $arParams["PHOTO_GALLERY_AVATAR_SIZE"];

if (array_key_exists("PHOTO_PATH_TO_FONT", $arParams) && is_array($arParams["PHOTO_PATH_TO_FONT"]))
	$arParams["PHOTO_PATH_TO_FONT"] = $arParams["PHOTO_PATH_TO_FONT"][0];

if ($componentPage == "user_photo_gallery")
	$componentPage = "user_photo";
elseif ($componentPage == "group_photo_gallery")
	$componentPage = "group_photo";
/********************************************************************
				Permission
********************************************************************/
if (($object == "user" && !CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "photo", "view", CSocNetUser::IsCurrentUserModuleAdmin())) || 
	($object == "group" && !CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "photo", "view", CSocNetUser::IsCurrentUserModuleAdmin())))
{
	$arParams["ERROR_MESSAGE"] = GetMessage("SONET_ACCESS_DENIED");
	return -1;
}
/********************************************************************
				/Permission
********************************************************************/
/************** Navigation *****************************************/
$strTitle = "";
if ($arParams["SET_NAV_CHAIN"] == "Y" || $arParams["SET_TITLE"] == "Y")
{

	$feature = "photo";
	$arEntityActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames((($object == 'group') ? SONET_ENTITY_GROUP : SONET_ENTITY_USER), (($object == 'group') ? $arResult["VARIABLES"]["group_id"] : $arResult["VARIABLES"]["user_id"]));		
	$strFeatureTitle = ((array_key_exists($feature, $arEntityActiveFeatures) && StrLen($arEntityActiveFeatures[$feature]) > 0) ? $arEntityActiveFeatures[$feature] : GetMessage("SONET_PHOTO"));

	if($object == "group")
	{
		$arGroup = CSocNetGroup::GetByID($arResult["VARIABLES"]["group_id"]);
		if ($arParams["SET_NAV_CHAIN"] == "Y")
		{
			$APPLICATION->AddChainItem($arGroup["NAME"], 
				CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_GROUP"], array("group_id" => $arGroup["ID"])));
			$APPLICATION->AddChainItem($strFeatureTitle, 
				CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_GROUP_PHOTO"], 
					array("group_id" => $arGroup["ID"], "path" => "")));
		}
		$strTitle = $arGroup["NAME"].": ".$strFeatureTitle;
		$arResult["GROUP"] = $arGroup;
	}
	else
	{
		if (strlen($arParams["NAME_TEMPLATE"]) <= 0)		
			$arParams["NAME_TEMPLATE"] = '#NOBR##NAME# #LAST_NAME##/NOBR#';
				
		$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
			array("#NOBR#", "#/NOBR#"), 
			array("", ""), 
			$arParams["NAME_TEMPLATE"]
		);
		$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;	
	
		$name = "";
		if ($USER->IsAuthorized() && $arResult["VARIABLES"]["user_id"] == $USER->GetID())
		{
			$arTmpUser = array(
				"NAME" => $USER->GetFirstName(), 
				"LAST_NAME" => $USER->GetLastName(),
				"SECOND_NAME" => $USER->GetParam("SECOND_NAME"),
				"LOGIN" => $USER->GetLogin(),
			);
			$name = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
		}
		else 
		{
			$dbUser = CUser::GetByID($arResult["VARIABLES"]["user_id"]);
			$arUser = $dbUser->Fetch();
			$name = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arUser, $bUseLogin);					
		}
		
//		$name = trim(htmlspecialcharsback($name));
		$strTitle = $name.": ".$strFeatureTitle;
		if ($arParams["SET_NAV_CHAIN"] == "Y")
		{
//			$APPLICATION->AddChainItem(htmlspecialcharsEx($name),  CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER"], array("user_id" => $arResult["VARIABLES"]["user_id"])));
			$APPLICATION->AddChainItem($name, 
				CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER"], array("user_id" => $arResult["VARIABLES"]["user_id"])));
			$APPLICATION->AddChainItem($strFeatureTitle, 
				CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER_PHOTO"], 
					array("user_id" => $arResult["VARIABLES"]["user_id"], "path" => "")));
		}
	}
}
if ($arParams["SET_TITLE"] == "Y" && !empty($strTitle))
{
//	$APPLICATION->SetTitle(htmlspecialcharsEx($strTitle));
	$APPLICATION->SetTitle($strTitle);
	if ($componentPage == "user_photo")
		$arParams["SET_TITLE"] = "N";
	elseif ($componentPage == "user_photo_gallery" && empty($arResult["VARIABLES"]["section_id"]))
		$arParams["SET_TITLE"] = "N";
}
/************** Navigation/*****************************************/
/********************************************************************
				Fatal errors
********************************************************************/
if (($object == "user" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "photo")) || 
	($object == "group" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "photo"))):
	$arParams["ERROR_MESSAGE"] = GetMessage("SONET_PHOTO_IS_NOT_ACTIVE");
	return 0;
elseif (!CModule::IncludeModule("photogallery")):
	$arParams["ERROR_MESSAGE"] = GetMessage("SONET_P_MODULE_IS_NOT_INSTALLED");
	return 0;
elseif (!CModule::IncludeModule("iblock")):
	$arParams["ERROR_MESSAGE"] = GetMessage("SONET_IB_MODULE_IS_NOT_INSTALLED");
	return 0;
elseif (($object == "user" && $arParams["PHOTO_USER_IBLOCK_ID"] <= 0) ||
	($object == "group" && $arParams["PHOTO_GROUP_IBLOCK_ID"] <= 0)):
	$arParams["ERROR_MESSAGE"] = GetMessage("SONET_IBLOCK_ID_EMPTY");
	return 0;
endif;
/********************************************************************
				/Fatal errors
********************************************************************/

/********************************************************************
				Input params
********************************************************************/
$arParams["PHOTO"] = array(
/***************** BASE ********************************************/
	"USER" => array(
		"IBLOCK_TYPE" => trim($arParams["PHOTO_USER_IBLOCK_TYPE"]), 
		"IBLOCK_ID" => intVal($arParams["PHOTO_USER_IBLOCK_ID"])), 
	"GROUP" => array(
		"IBLOCK_TYPE" => trim($arParams["PHOTO_GROUP_IBLOCK_TYPE"]), 
		"IBLOCK_ID" => intVal($arParams["PHOTO_GROUP_IBLOCK_ID"])), 
		
	"ALL" => array(
		"GALLERY_GROUPS" => array(2), 
		"ONLY_ONE_GALLERY" => "Y", 
		"SECTION_SORT_BY" => trim($arParams["PHOTO_SECTION_SORT_BY"]), 
		"SECTION_SORT_ORD" => trim($arParams["PHOTO_SECTION_SORT_ORD"]), 
		"ELEMENT_SORT_FIELD" => trim($arParams["PHOTO_ELEMENT_SORT_FIELD"]), 
		"ELEMENT_SORT_ORDER" => trim($arParams["PHOTO_ELEMENT_SORT_ORDER"]), 
		
		"PROPERTY_CODE" => array(), 
		"MODERATION" => ($arParams["PHOTO_MODERATION"] == "Y" ? "Y" : "N"), 
/***************** ADDITIONAL **************************************/
		"SECTION_PAGE_ELEMENTS" => intVal($arParams["PHOTO_SECTION_PAGE_ELEMENTS"]), 
		"ELEMENTS_PAGE_ELEMENTS" => intVal($arParams["PHOTO_ELEMENTS_PAGE_ELEMENTS"]), 
		"PAGE_NAVIGATION_TEMPLATE" => trim($arParams["PHOTO_PAGE_NAVIGATION_TEMPLATE"]), 
		"ELEMENTS_USE_DESC_PAGE" => "Y",
		"DATE_TIME_FORMAT_SECTION" => "",
		"DATE_TIME_FORMAT_DETAIL" => "",
		"USE_PERMISSIONS" => "N",
		"GROUP_PERMISSIONS" => array(),
		"TEMPLATE_LIST" => ($arParams["PHOTO_TEMPLATE_LIST"] == "table" ? "table" : ".default"), 
/***************** UPLOAD ******************************************/
		"UPLOAD_MAX_FILE_SIZE" => intVal($arParams["PHOTO_UPLOAD_MAX_FILESIZE"]), 
		"UPLOAD_MAX_FILE" => intVal($arParams["PHOTO_UPLOAD_MAX_FILE"]), 
		
		"GALLERY_AVATAR_SIZE" => intVal($arParams["PHOTO_GALLERY_AVATAR_SIZE"]), 
		"GALLERY_AVATAR_THUMBS_SIZE" => intVal($arParams["PHOTO_GALLERY_AVATAR_THUMBS_SIZE"]), 
		
		"ALBUM_PHOTO_THUMBS_SIZE" => intVal($arParams["PHOTO_ALBUM_PHOTO_THUMBS_SIZE"]), 
		"ALBUM_PHOTO_SIZE" => intVal($arParams["PHOTO_ALBUM_PHOTO_SIZE"]), 
		
		"THUMBS_SIZE" => intVal($arParams["PHOTO_THUMBS_SIZE"]), 
		"JPEG_QUALITY1" => intVal($arParams["PHOTO_JPEG_QUALITY1"]), 
		"PREVIEW_SIZE" => intVal($arParams["PHOTO_PREVIEW_SIZE"]), 
		"JPEG_QUALITY2" => intVal($arParams["PHOTO_JPEG_QUALITY2"]), 
		"ORIGINAL_SIZE" => intVal($arParams["PHOTO_ORIGINAL_SIZE"]), 
		"JPEG_QUALITY" => intVal($arParams["PHOTO_JPEG_QUALITY"]), 
		
		"ADDITIONAL_SIGHTS" => array(), 
		"WATERMARK_RULES" => $arParams["PHOTO_WATERMARK_RULES"], 
		"WATERMARK_TYPE" => $arParams["PHOTO_WATERMARK_TYPE"], 
		"WATERMARK_TEXT" => $arParams["PHOTO_WATERMARK_TEXT"], 
		"WATERMARK_COLOR" => $arParams["PHOTO_WATERMARK_COLOR"], 
		"WATERMARK_SIZE" => $arParams["PHOTO_WATERMARK_SIZE"], 
		"WATERMARK_FILE" => $arParams["PHOTO_WATERMARK_FILE"], 
		"WATERMARK_FILE_ORDER" => $arParams["PHOTO_WATERMARK_FILE_ORDER"], 
		"WATERMARK_POSITION" => $arParams["PHOTO_WATERMARK_POSITION"], 
		"WATERMARK_TRANSPARENCY" => $arParams["PHOTO_WATERMARK_TRANSPARENCY"], 
		"WATERMARK_MIN_PICTURE_SIZE" => intVal($arParams["PHOTO_WATERMARK_MIN_PICTURE_SIZE"]), 
		"PATH_TO_FONT" => trim($arParams["PHOTO_PATH_TO_FONT"]), 
/***************** RATING ******************************************/
		"USE_RATING" => ($arParams["PHOTO_USE_RATING"] == "Y" ? "Y" : "N"), 
		"MAX_VOTE" => intVal($arParams["PHOTO_MAX_VOTE"]), 
		"VOTE_NAMES" => $arParams["PHOTO_VOTE_NAMES"], 
		"DISPLAY_AS_RATING" => $arParams["PHOTO_DISPLAY_AS_RATING"], 
/***************** COMMENTS ****************************************/
		"USE_COMMENTS" => ($arParams["PHOTO_USE_COMMENTS"] == "Y" && $arResult["GROUP"]["CLOSED"] != "Y" ? "Y" : "N"), 
		"PATH_TO_SMILE" => $arParams["PATH_TO_FORUM_SMILE"], 
		"COMMENTS_TYPE" => "FORUM", 
		"BLOG_URL" => "", 
		"COMMENTS_COUNT" => "", 
		"PATH_TO_USER" => "", 
		"PATH_TO_BLOG" => "", 
		"FORUM_ID" => $arParams["PHOTO_FORUM_ID"], 
		"URL_TEMPLATES_READ" => $arParams["PHOTO_URL_TEMPLATES_READ"], 
		"USE_CAPTCHA" => $arParams["PHOTO_USE_CAPTCHA"], 
		"PREORDER" => $arParams["PHOTO_PREORDER"], 
/***************** TAGS ******************************************/
		"SHOW_TAGS" => "N", 
		),
	"TEMPLATE" => array(
		"SHOW_PHOTO_USER" => "N",
		"SHOW_SHOWS" => "",
		"CELL_COUNT" => intVal($arParams["PHOTO_COUNT_CELL"] > 0 ? $arParams["PHOTO_COUNT_CELL"] : 0),
		"TEMPLATE_LIST" => ($arParams["PHOTO_TEMPLATE_LIST"] == "table" ? "table" : ".default"), 
		"SLIDER_COUNT_CELL" => intVal($arParams["PHOTO_SLIDER_COUNT_CELL"] > 0 ? $arParams["PHOTO_SLIDER_COUNT_CELL"] : 3), 
		"GALLERY_AVATAR_SIZE" => intVal($arParams["PHOTO_GALLERY_AVATAR_SIZE"]), 
		"WATERMARK" => ($arParams["PHOTO_SHOW_WATERMARK"] == "N" ? "N" : "Y"), 
		"WATERMARK_COLORS" => array(), 
		)
	);
/***************** ADDITIONAL **************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"]=="Y" ? "Y" : "N"); //Turn off by default
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params 
********************************************************************/
$arParams["IBLOCK_ID"] = intVal($object == "user" ? $arParams["PHOTO_USER_IBLOCK_ID"] : $arParams["PHOTO_GROUP_IBLOCK_ID"]);
$cache = new CPHPCache;
/************** Permission *****************************************/
$arParams["PERMISSION"]	= "D";
if ($object == "user"):
	if (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "photo", "write", CSocNetUser::IsCurrentUserModuleAdmin())):
		$arParams["PERMISSION"]	= "W";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "photo", "view", CSocNetUser::IsCurrentUserModuleAdmin())):
		$arParams["PERMISSION"]	= "R";
	endif;
else:
	if (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "photo", "write", CSocNetUser::IsCurrentUserModuleAdmin())):
		$arParams["PERMISSION"]	= "W";
	elseif (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "photo", "view", CSocNetUser::IsCurrentUserModuleAdmin())):
		$arParams["PERMISSION"]	= "R";
	endif;
endif;

$user_alias = $arResult["VARIABLES"]["user_alias"];
if (empty($user_alias)):
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
		"SECTION_ID" => 0, 
		"SOCNET_GROUP_ID" => false);
	if ($object == "user")
	{
		$uid = $arResult["VARIABLES"]["user_id"];
		if ($uid <= 0 && $componentPage == "user_photo_my" && $GLOBALS["USER"]->IsAuthorized())
			$uid = $GLOBALS["USER"]->GetId();
		if ($uid <= 0)
		{
			$arParams["ERROR_MESSAGE"] = "User is not exists.";
			return 0;
		}
		$arFilter["CREATED_BY"] = $uid;
	}
	else
	{
		$arFilter["SOCNET_GROUP_ID"] = $arResult["VARIABLES"]["group_id"];
	}
	
	$cache_id = serialize($arFilter);
	$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/".$object."/data/".
		($object == "user" ? $arFilter["CREATED_BY"] : $arFilter["SOCNET_GROUP_ID"])."/";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arResult["VARIABLES"]["GALLERY"] = $res["MY_GALLERY"];
		$arResult["VARIABLES"]["GALLERIES"] = $res["MY_GALLERIES"];
	}
	if (!is_array($arResult["VARIABLES"]["GALLERY"]) || empty($arResult["VARIABLES"]["GALLERY"]))
	{
		CModule::IncludeModule("iblock");
		$db_res = CIBlockSection::GetList(
			array($arParams["PHOTO"]["ALL"]["SECTION_SORT_BY"] => $arParams["PHOTO"]["ALL"]["SECTION_SORT_ORD"], "ID" => "DESC"), 
			$arFilter, false, array("UF_DEFAULT", "UF_GALLERY_SIZE", "UF_GALLERY_RECALC", "UF_DATE"));
		if ($db_res)
		{
			while ($res = $db_res->GetNext())
			{
				if (preg_match("/[^a-z0-9_]/is", $res["~CODE"]))
					$res["CODE"] = "";
				$res["ELEMENTS_CNT"] = intVal(CIBlockSection::GetSectionElementsCount($res["ID"], Array("CNT_ALL"=>"Y")));
				$res["PICTURE"] = CFile::GetFileArray($res["PICTURE"]);
				if ($res["UF_DEFAULT"] == "Y" && $res["ACTIVE"] == "Y")
					$arResult["VARIABLES"]["GALLERY"] = $res;
					
				$arResult["VARIABLES"]["GALLERIES"][] = $res;
			};
			if (empty($arResult["VARIABLES"]["GALLERY"]) && $arResult["VARIABLES"]["GALLERIES"][0]["ACTIVE"] == "Y")
				$arResult["VARIABLES"]["GALLERY"] = $arResult["VARIABLES"]["GALLERIES"][0];
		}
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(
				array(
					"MY_GALLERY" => $arResult["VARIABLES"]["GALLERY"],
					"MY_GALLERIES" => $arResult["VARIABLES"]["GALLERIES"]));
		}
	}
	if (empty($arResult["VARIABLES"]["GALLERY"]) && !empty($arResult["VARIABLES"]["GALLERIES"]))
	{
		$count = 0;
		foreach ($arResult["VARIABLES"]["GALLERIES"] as $key => $res)
		{
			if ($res["ACTIVE"] == "Y")
			{
				$arResult["VARIABLES"]["GALLERY"] = $res;
				break;
			}
			else 
			{
				$count++;
			}
		}
		if (empty($arResult["VARIABLES"]["GALLERY"]) && 
			$count == count($arResult["VARIABLES"]["GALLERIES"]))
		{
			if ($count == 1)
				$arParams["ERROR_MESSAGE"] = GetMessage("SONET_GALLERY_IS_NOT_ACTIVE");
			else 
				$arParams["ERROR_MESSAGE"] = GetMessage("SONET_GALLERIES_IS_NOT_ACTIVE");
			return 0;
		}
	}
	if (empty($arResult["VARIABLES"]["GALLERY"]))
	{
		if ($arParams["PERMISSION"] >= "W" && ($object == "group" || ($object == "user" && $arResult["VARIABLES"]["user_id"] == $USER->GetID())))
		{
			$arFiles = array();
			
			if ($object == "user")
			{
				CheckDirPath($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/");
				$dbUser = CUser::GetByID($USER->GetID());
				$arResult["USER"] = $dbUser->GetNext();
				$arResult["USER"]["PERSONAL_PHOTO"] = intVal($arResult["USER"]["PERSONAL_PHOTO"]);
				
				if ($arResult["USER"]["PERSONAL_PHOTO"] > 0)
				{
					$arFile = CFile::GetFileArray($arResult["USER"]["PERSONAL_PHOTO"]);
					if (!empty($arFile))
					{
						$src = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
						$src = str_replace("//","/",$src);
						
						$arRealFile = array(
							"name" => preg_replace("/[^a-z_.1-9]/is", "_", $arFile["ORIGINAL_NAME"]),
							"type" => $arFile["CONTENT_TYPE"],
							"tmp_name" => $_SERVER["DOCUMENT_ROOT"].$src, 
							"error" => 0,
							"size" => $arFile["FILE_SIZE"]);
						include_once($_SERVER["DOCUMENT_ROOT"]."/".BX_PERSONAL_ROOT."/components/bitrix/photogallery.upload/functions.php"); 
						$arAlbumSights = array(
							"DETAIL_PICTURE" => array(
								"code" => "album",
								"notes" => "for_album",
								"width" => $arParams["PHOTO"]["ALL"]["GALLERY_AVATAR_SIZE"],
								"height" => $arParams["PHOTO"]["ALL"]["GALLERY_AVATAR_SIZE"]),
							"PICTURE" => array(
								"code" => "album_thumbs",
								"notes" => "for_album",
								"width" => $arParams["PHOTO"]["ALL"]["GALLERY_AVATAR_THUMBS_SIZE"],
								"height" => $arParams["PHOTO"]["ALL"]["GALLERY_AVATAR_THUMBS_SIZE"]));
						foreach ($arAlbumSights as $key => $Sight)
						{
							$File = $arRealFile; 
							$File["name"] = "avatar_".$Sight["code"].$arRealFile["name"];
							$File["tmp_name"] = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/".$File["name"];
							__ResizeImage($File, $arRealFile, $Sight, 1);
							$File["MODULE_ID"] = "iblock";
							$arFiles[$key] = $File;
						}
						@imagedestroy($arRealFile["image"]);
					}
				}
			}
				
			
			$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", 0, LANGUAGE_ID);
			if (empty($arUserFields) || empty($arUserFields["UF_DEFAULT"]))
			{
				$db_res = CUserTypeEntity::GetList(array($by=>$order), 
					array("ENTITY_ID" => "IBLOK_".$arParams["IBLOCK_ID"]."_SECTION", "FIELD_NAME" => "UF_DEFAULT"));
				if (!$db_res || !($res = $db_res->GetNext()))
				{
					$arFields = Array(
						"ENTITY_ID" => "IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION",
						"FIELD_NAME" => "UF_DEFAULT",
						"USER_TYPE_ID" => "string",
						"MULTIPLE" => "N",
						"MANDATORY" => "N");
					$arFieldName = array();
					$rsLanguage = CLanguage::GetList($by, $order, array());
					while($arLanguage = $rsLanguage->Fetch()):
						if (LANGUAGE_ID == $arLanguage["LID"])
							$arFieldName[$arLanguage["LID"]] = GetMessage("IBLOCK_DEFAULT_UF");
						if (empty($arFieldName[$arLanguage["LID"]]))
							$arFieldName[$arLanguage["LID"]] = "Default gallery";
					endwhile;
					$arFields["EDIT_FORM_LABEL"] = $arFieldName;
					$obUserField  = new CUserTypeEntity;
					$obUserField->Add($arFields);
					$APPLICATION->GetException();
					$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
				}
			}
			$arFields = Array(
				"ACTIVE" => "Y",
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"NAME" => "",
				"CODE" => "",
				"DESCRIPTION" => "",
				"UF_DEFAULT" => "Y",
				"SOCNET_GROUP_ID" => false,
				"IBLOCK_SECTION_ID" => "0");
				
			if ($object == "user")
			{
				$arFields["NAME"] = trim($USER->GetLastName()." ".$USER->GetFirstName());
				$arFields["NAME"] = trim(!empty($arFields["NAME"]) ? $arFields["NAME"] : $USER->GetLogin());
				$arFields["CODE"] = "user_".$arResult["VARIABLES"]["user_id"];
			}
			else 
			{
				$res = CSocNetGroup::GetByID($arResult["VARIABLES"]["group_id"]);
				if (!$res)
				{
					$arParams["ERROR_MESSAGE"] = GetMessage("SONET_GROUP_NOT_EXISTS");
					return 0;
				}
				$arFields["SOCNET_GROUP_ID"] = $arResult["VARIABLES"]["group_id"];
				$arFields["NAME"] = GetMessage("SONET_GROUP_PREFIX").$res["NAME"];
				$arFields["CODE"] = "group_".$arResult["VARIABLES"]["group_id"];
			}
			
			if (!empty($arFiles))
			{
				$arFields["PICTURE"] = $arFiles["PICTURE"];
			}
			$bs = new CIBlockSection();
			if ($bs->CheckFields($arFields))
			{
				if (!empty($arFiles))
				{
					$arFields["DETAIL_PICTURE"] = $arFiles["DETAIL_PICTURE"];
				}					
				$GLOBALS["UF_DEFAULT"] = $arFields["UF_DEFAULT"];
				$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
				$res = $bs->Add($arFields);
				$db_res = $bs->GetByID($res);
				if ($db_res && $res = $db_res->Fetch())
					$arResult["VARIABLES"]["GALLERY"] = $res;
				BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/");
				if (!empty($arFiles))
				{
					@unlink($arFiles["PICTURE"]["tmp_name"]);
					@unlink($arFiles["DETAIL_PICTURE"]["tmp_name"]);
				}
			}
		}
	}
	"";
elseif ($user_alias != "NEW_ALIAS"):
	$cache_id = serialize(array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ALIAS" => $user_alias,
		"SECTION_ID" => 0));
	$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/gallery/".$user_alias."/";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arResult["VARIABLES"]["GALLERY"] = $res["GALLERY"];
	}
	if (!is_array($arResult["VARIABLES"]["GALLERY"]) || empty($arResult["VARIABLES"]["GALLERY"]))
	{
		CModule::IncludeModule("iblock");
		$arFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"SECTION_ID" => 0, 
			"SOCNET_GROUP_ID" => ($object != "user" ? $arResult["VARIABLES"]["group_id"] : false), 
			"CODE" => $user_alias);
		if ($object == "user")
			$arFilter["CREATED_BY"] = $arResult["VARIABLES"]["user_id"];
		$db_res = CIBlockSection::GetList(array($arParams["SORT_BY"] => $arParams["SORT_ORD"], "ID" => "DESC"), 
			$arFilter, false, array("UF_DEFAULT", "UF_GALLERY_SIZE", "UF_GALLERY_RECALC", "UF_DATE"));
		if ($db_res && $res = $db_res->GetNext())
		{
			$res["ELEMENTS_CNT"] = intVal(CIBlockSection::GetSectionElementsCount($res["ID"], array("CNT_ALL"=>"Y")));
			$res["PICTURE"] = CFile::GetFileArray($res["PICTURE"]);
			$arResult["VARIABLES"]["GALLERY"] = $res;
		}
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("GALLERY" => $arResult["VARIABLES"]["GALLERY"]));
		}
	}
	if ($arResult["VARIABLES"]["GALLERY"]["ACTIVE"] == "N")
	{
		$arParams["NOTE_MESSAGE"] = GetMessage("SONET_GALLERY_IS_NOT_ACTIVE");
		return 0;
	}
endif;
if (empty($arResult["VARIABLES"]["GALLERY"]))
{
	if (!($arParams["PERMISSION"] >= "W" && $user_alias == "NEW_ALIAS" && 
		($object == "group" || ($object == "user" && $arResult["VARIABLES"]["user_id"] == $USER->GetID()))))
	{
		$arParams["NOTE_MESSAGE"] = GetMessage("SONET_GALLERY_NOT_FOUND");
		return 0;
	}
}
/********************************************************************
				/Default params 
********************************************************************/
/********************************************************************
				Path
********************************************************************/
foreach ($arDefaultUrlTemplates404 as $url => $value)
{
	if (strPos($url, "user_photo") === false && strPos($url, "group_photo") === false)
		continue;

	$arResult["~PATH_TO_".strToUpper($url)] = str_replace(
		array(
			"#user_id#",
			"#group_id#",
			"#user_alias#",
			"#path#",
			"#section_id#",
			"#element_id#",
			"#element_name#",
			"#action#"),
		array(
			$arResult["VARIABLES"]["user_id"],
			$arResult["VARIABLES"]["group_id"],
			"#USER_ALIAS#",
			"#PATH#",
			"#SECTION_ID#",
			"#ELEMENT_ID#",
			"#ELEMENT_NAME#",
			"#ACTION#"),
		$arResult["PATH_TO_".strToUpper($url)]);
}
$arResult["~PATH_TO_USER_PHOTO"] = $arResult["~PATH_TO_USER_PHOTO_GALLERY"];
$arResult["~PATH_TO_USER"] = str_replace("#user_id#", "#USER_ID#", (empty($arResult["PATH_TO_USER"]) ? $arParams["PATH_TO_USER"] : $arResult["PATH_TO_USER"]));
$arResult["VARIABLES"]["SECTION_ID"] = $arResult["VARIABLES"]["section_id"];
$arResult["VARIABLES"]["ELEMENT_ID"] = $arResult["VARIABLES"]["element_id"];
$arResult["VARIABLES"]["ACTION"] = $arResult["VARIABLES"]["action"];
$arResult["VARIABLES"]["PERMISSION"] = $arParams["PERMISSION"];
if ($componentPage == "user_photo_gallery")
	$componentPage = "user_photo";
elseif ($componentPage == "group_photo_gallery")
	$componentPage = "group_photo";
/********************************************************************
				/Path
********************************************************************/
/********************************************************************
				Activity before
********************************************************************/
if (($componentPage == "group_photo_element_upload" || $componentPage == "group_files_element_upload" || 
	$componentPage == "user_photo_element_upload" || $componentPage == "user_files_element_upload") && 
	$_REQUEST["save_upload"] == "Y")
{
	$_REQUEST["FORMAT_ANSWER"] = "return";
	$arParams["ANSWER_UPLOAD_PAGE"] = array();
}
/********************************************************************
				/Activity before
********************************************************************/
return 1;
?>