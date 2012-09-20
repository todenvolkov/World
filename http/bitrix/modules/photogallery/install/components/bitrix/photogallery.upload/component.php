<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arParams["WATERMARK_MIN_PICTURE_SIZE"] = intVal($arParams["WATERMARK_MIN_PICTURE_SIZE"]);
if (!CModule::IncludeModule("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif(!CModule::IncludeModule("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return 0;
elseif ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"])):
	ShowError(GetMessage("P_GALLERY_EMPTY"));
	return 0;
endif;
include_once(str_replace(array("\\", "//"), "/", dirname(__FILE__)."/functions.php"));

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intVal($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["USER_ALIAS"] = trim($arParams["USER_ALIAS"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);

	$arParams["IMAGE_UPLOADER_ACTIVEX_CLSID"] = "718B3D1E-FF0C-4EE6-9F3B-0166A5D1C1B9";
	$arParams["IMAGE_UPLOADER_ACTIVEX_CONTROL_VERSION"] = "6,0,20,0";
	$arParams["IMAGE_UPLOADER_JAVAAPPLET_VERSION"] = "6.0.20.0";

	$arParams["THUMBNAIL_ACTIVEX_CLSID"] = "58C8ACD5-D8A6-4AC8-9494-2E6CCF6DD2F8";
	$arParams["THUMBNAIL_ACTIVEX_CONTROL_VERSION"] = "3,5,204,0";
	$arParams["THUMBNAIL_JAVAAPPLET_VERSION"] = "1.1.81.0";
	$arParams["PATH_TO_TMP"] = preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/uploader/");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"index" => "",
			"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
			"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#");

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["UPLOAD_MAX_FILE"] = ((intVal($arParams["UPLOAD_MAX_FILE"]) <= 0 || intVal($arParams["UPLOAD_MAX_FILE"]) > 10) ? 10 : intVal($arParams["UPLOAD_MAX_FILE"]));
	$arParams["UPLOAD_MAX_FILE"] = 1;
	$arParams["UPLOAD_MAX_FILE_SIZE"] = intVal($arParams["UPLOAD_MAX_FILE_SIZE"]);

	// Additional sights
	$arParams["PICTURES_INFO"] = @unserialize(COption::GetOptionString("photogallery", "pictures"));
	$arParams["PICTURES_INFO"] = (is_array($arParams["PICTURES_INFO"]) ? $arParams["PICTURES_INFO"] : array());
	$arParams["PICTURES"] = array();
	if (!empty($arParams["PICTURES_INFO"]) && is_array($arParams["ADDITIONAL_SIGHTS"]) && !empty($arParams["ADDITIONAL_SIGHTS"])):
		foreach ($arParams["PICTURES_INFO"] as $key => $val):
			if (in_array(str_pad($key, 5, "_").$val["code"], $arParams["ADDITIONAL_SIGHTS"]))
				$arParams["PICTURES"][$val["code"]] = array(
					"size" => $arParams["PICTURES_INFO"][$key]["size"],
					"quality" => $arParams["PICTURES_INFO"][$key]["quality"]);
		endforeach;
	endif;
	$arParams["MODERATION"] = ($arParams["MODERATION"] == "Y" ? "Y" : "N");
	$arParams["PUBLIC_BY_DEFAULT"] = ($arParams["SHOW_PUBLIC"] == "N" || $arParams["PUBLIC_BY_DEFAULT"] == "Y" ? "Y" : "N");
	$arParams["APPROVE_BY_DEFAULT"] = ($arParams["APPROVE_BY_DEFAULT"] == "Y" ? "Y" : "N");

	$arParams["WATERMARK_RULES"] = ($arParams["WATERMARK_RULES"] == "ALL" ? "ALL" : "USER");
	$arParams["WATERMARK_TYPE"] = ($arParams["WATERMARK_TYPE"] == "TEXT" ? "TEXT" : "PICTURE");
	$arParams["WATERMARK_TEXT"] = trim($arParams["WATERMARK_TEXT"]);

	if ($arParams["PATH_TO_FONT"] && strlen($arParams["PATH_TO_FONT"]) > 0)
	{
		$arParams["PATH_TO_FONT"] = str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT']."/".BX_ROOT."/modules/photogallery/fonts/".trim($arParams["PATH_TO_FONT"]));
		$arParams["PATH_TO_FONT"] = (file_exists($arParams["PATH_TO_FONT"]) ? $arParams["PATH_TO_FONT"] : "");
	}
	else
	{
		$arParams["PATH_TO_FONT"] = "";
	}

	$arParams["WATERMARK_COLOR"] = trim($arParams["WATERMARK_COLOR"]);
	$arParams["WATERMARK_SIZE"] = intVal($arParams["WATERMARK_SIZE"]);

	$arParams["WATERMARK_FILE"] = str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT']."/".trim($arParams["WATERMARK_FILE"]));
	$arParams["WATERMARK_FILE"] = (file_exists($arParams["WATERMARK_FILE"]) ? $arParams["WATERMARK_FILE"] : "");

	$arParams["WATERMARK_FILE_ORDER"] = strtolower($arParams["WATERMARK_FILE_ORDER"]);

	$arParams["WATERMARK_POSITION"] = trim($arParams["WATERMARK_POSITION"]);
	$arParams["WATERMARK_TRANSPARENCY"] = trim($arParams["WATERMARK_TRANSPARENCY"]);
	$arParams["WATERMARK_MIN_PICTURE_SIZE"] = (intVal($arParams["WATERMARK_MIN_PICTURE_SIZE"]) > 0 ? intVal($arParams["WATERMARK_MIN_PICTURE_SIZE"]) : 800);

	$arParams["GD_INSTALL"] = "N";
	if (function_exists("gd_info")):
		$arGDInfo = gd_info();
		$arParams["GD_INSTALL"] = ((strpos($arGDInfo['GD Version'], "2.") !== false) ? "2" : "1");
	endif;

	$arParams["WATERMARK"] = ($arParams["GD_INSTALL"] == false ? "N" : "Y");

	$arParams["GALLERY_SIZE"] = intVal($arParams["GALLERY_SIZE"])*1024*1024;
	$arParams["ALBUM_PHOTO"] = array("SIZE" => (intVal($arParams["ALBUM_PHOTO_WIDTH"]) > 0 ? intVal($arParams["ALBUM_PHOTO_WIDTH"]) : 200));
	$arParams["ALBUM_PHOTO_THUMBS"] = array("SIZE" => (intVal($arParams["ALBUM_PHOTO_THUMBS_WIDTH"]) > 0 ? intVal($arParams["ALBUM_PHOTO_THUMBS_WIDTH"]) : 120));
	$arParams["THUMBS_SIZE"] = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBS_SIZE"]));
	list($arParams["THUMBS_SIZE"]["WIDTH"], $arParams["THUMBS_SIZE"]["HEIGHT"]) = explode("/", $arParams["THUMBS_SIZE"]["STRING"]);
	$arParams["THUMBS_SIZE"]["SIZE"] = (intVal($arParams["THUMBS_SIZE"]["WIDTH"]) > 0 ? intVal($arParams["THUMBS_SIZE"]["WIDTH"]) : 200);
	$arParams["PREVIEW_SIZE"] = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["PREVIEW_SIZE"]));
	list($arParams["PREVIEW_SIZE"]["WIDTH"], $arParams["PREVIEW_SIZE"]["HEIGHT"]) = explode("/", $arParams["PREVIEW_SIZE"]["STRING"]);
	$arParams["PREVIEW_SIZE"]["SIZE"] = (intVal($arParams["PREVIEW_SIZE"]["WIDTH"]) > 0 ? intVal($arParams["PREVIEW_SIZE"]["WIDTH"]) : 600);
	$arParams["ORIGINAL_SIZE"] = intVal($arParams["ORIGINAL_SIZE"]);
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Main data
********************************************************************/
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/".$arParams["IBLOCK_ID"]."/");

$oPhoto = new CPGalleryInterface(
	array(
		"IBlockID" => $arParams["IBLOCK_ID"],
		"GalleryID" => $arParams["USER_ALIAS"],
		"Permission" => $arParams["PERMISSION_EXTERNAL"]),
	array(
		"cache_time" => $arParams["CACHE_TIME"],
		"cache_path" => $cache_path_main,
		"show_error" => "Y",
		"set_404" => $arParams["SET_STATUS_404"]
		)
	);

if (!$oPhoto)
	return false;

$arResult["GALLERY"] = $oPhoto->Gallery;
$arParams["PERMISSION"] = $oPhoto->User["Permission"];
$arResult["SECTION"] = array();
/************** SECTION *************************************************/
if ($arParams["PERMISSION"] < "W")
{
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return false;
}
elseif ($arParams["SECTION_ID"] > 0)
{
	$res = $oPhoto->GetSection($arParams["SECTION_ID"], $arResult["SECTION"]);
	if ($res > 400)
		return false;
	elseif ($res == 301)
	{
		$url = CComponentEngine::MakePathFromTemplate(
			$arParams["~SECTION_URL"],
			array(
				"USER_ALIAS" => $arGallery["CODE"],
				"SECTION_ID" => $arParams["SECTION_ID"]));
		LocalRedirect($url, false, "301 Moved Permanently");
		return false;
	}
}
$arParams["ABS_PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
if ($arParams["ABS_PERMISSION"] < "W" && 0 < $arParams["GALLERY_SIZE"] &&
		$arParams["GALLERY_SIZE"] < intVal($arResult["GALLERY"]["UF_GALLERY_SIZE"])):
	ShowError(GetMessage("P_GALLERY_NOT_SIZE"));
	return false;
endif;
/********************************************************************
				/Main data
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$arError = array();
$arResult["RETURN_DATA"] = array();
$arResult["UPLOAD_MAX_FILE_SIZE"] = $arParams["UPLOAD_MAX_FILE_SIZE"]*1024*1024;
$arResult["SHOW"]["TAGS"] = (IsModuleInstalled("search") ? "Y" : "N");
/************** URL ************************************************/
$arResult["URL"] = array(
	"SECTION" => CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array(
		"USER_ALIAS" => $arResult["GALLERY"]["CODE"], "SECTION_ID" => $arParams["SECTION_ID"])),
	"SECTION_EMPTY" => CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array(
		"USER_ALIAS" => $arResult["GALLERY"]["CODE"], "SECTION_ID" => "#SECTION_ID#")),
	"GALLERY" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"], array(
		"USER_ALIAS" => $arResult["GALLERY"]["CODE"])),
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["~INDEX_URL"], array()));
foreach ($arResult["URL"] as $key => $val):
	$arResult["URL"]["~".$key] = $val;
	$arResult["URL"][$key] = htmlspecialchars($val);
endforeach;
$bVarsFromForm = false;
/********************************************************************
				/Default params
********************************************************************/

/********************************************************************
				Action
********************************************************************/
if ($_REQUEST["save_upload"] == "Y" || ($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST) && !empty($_REQUEST["sessid"])))
{
	$result = array("FILE" => array(), "FILE_INFO" => array());
	$arProperties = array(); $arPropertiesNeed = array();
	$arFiles = array(); $arProp = array();
	$arWaterMark = array(); $arSection = array();
	CheckDirPath($arParams["PATH_TO_TMP"]);
/************** Saved data *****************************************/
	$sTmpId = md5(serialize(array("PackageGuid " => $_REQUEST["PackageGuid"], "sessid" => bitrix_sessid()))).".tmp";
	$sTmpPath = $arParams["PATH_TO_TMP"].$sTmpId;
	$arSavedData = array();
	if ($_REQUEST["CACHE_RESULT"] == "Y" && file_exists($sTmpPath)):
		$arSavedData = file_get_contents($sTmpPath);
		$arSavedData = (is_string($arSavedData) ? unserialize($arSavedData) : array());
	endif;

	if ($_REQUEST["AJAX_CALL"] == "Y"):
		array_walk($_REQUEST, '__UnEscape');
		array_walk($_FILES, '__UnEscape');
	endif;

	if ($arParams["WATERMARK"] == "Y"):
		$arWaterMark["USER"] = array(
			"text" => trim($_REQUEST["watermark"]),
			"color" => $_REQUEST["watermark_color"],
			"size" => $_REQUEST["watermark_size"],
			"position" =>  $_REQUEST["watermark_position"],
			"use_copyright" => ($_REQUEST["watermark_copyright"] == "hide" ? "N" : "Y"),
//			"coeff" => $_REQUEST["watermark_coefficient"],  // always empty
			"min_size_picture" => $arParams["WATERMARK_MIN_PICTURE_SIZE"],
			"path_to_font" => $arParams["PATH_TO_FONT"],
			);

		if ($arParams["WATERMARK_RULES"] == "ALL"):
			$arWaterMark["ALL"] = array(
				"text" => "",
				"path_to_font" => $arParams["PATH_TO_FONT"],
				"color" => $arParams["WATERMARK_COLOR"],
				"coeff" => $arParams["WATERMARK_SIZE"],

				"file" => "",
				"type" => $arParams["WATERMARK_FILE_ORDER"],
				"position" => $arParams["WATERMARK_POSITION"],
				"alpha_level" => $arParams["WATERMARK_TRANSPARENCY"],
				"min_size_picture" => $arParams["WATERMARK_MIN_PICTURE_SIZE"]);

			if ($arParams["WATERMARK_TYPE"] == "TEXT"):
				$arWaterMark["ALL"]["text"] = $arParams["WATERMARK_TEXT"];
			else:
				$arWaterMark["ALL"]["file"] = $arParams["WATERMARK_FILE"];
			endif;

		endif;
	endif;

	if ($_SERVER['REQUEST_METHOD'] == "POST" && empty($_POST))
	{
		$arError["bad_post"] = array(
			"id" => "bad_post",
			"text" => str_replace("#SIZE#", intVal(ini_get('post_max_size')), GetMessage("P_ERROR_BAD_POST")));
	}
	elseif (!check_bitrix_sessid())
	{
		$arError["bad_sessid"] = array(
			"id" => "bad_sessid",
			"text" => GetMessage("IBLOCK_WRONG_SESSION"));
	}
	elseif (empty($_FILES))
	{
		$arError["empty_files"] = array(
			"id" => "empty_files",
			"text" => GetMessage("P_ERROR_BAD_FILES"));
	}
/************** Section ********************************************/
	elseif ($_REQUEST["photo_album_id"] == "new")
	{
		if (!empty($arSavedData) && $arSavedData["SECTION_ID"] > 0):
			$arParams["SECTION_ID"] = intVal($arSavedData["SECTION_ID"]);
		else:
			$arFields = Array(
				"ACTIVE" => "Y",
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"IBLOCK_SECTION_ID" => ($arParams["BEHAVIOUR"] == "USER" ? $arResult["GALLERY"]["ID"] : 0),
				"DATE" => ConvertTimeStamp(time()),
				"UF_DATE" => ConvertTimeStamp(time()),
				"NAME" => (strLen(GetMessage("P_NEW_ALBUM")) <= 0 ? "New album" : GetMessage("P_NEW_ALBUM")));
			$GLOBALS["UF_DATE"] = $arFields["UF_DATE"];

			$bs = new CIBlockSection;
			$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
			$ID = $bs->Add($arFields);
			if ($ID <= 0):
				$arError["bad_section"] = array(
					"id" => "bad_section",
					"text" => $bs->LAST_ERROR);
			else:
				CIBlockSection::ReSort($arParams["IBLOCK_ID"]);
				$arParams["SECTION_ID"] = intVal($ID);
				$arFilter = array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "CODE" => $arParams["USER_ALIAS"], "SECTION_ID" => 0);
				$db_res = CIBlockSection::GetList(array(), $arFilter, false, array("ID", "NAME", "CREATED_BY", "RIGHT_MARGIN", "LEFT_MARGIN", "CODE", "UF_GALLERY_SIZE"));
				$arResult["GALLERY"] = $db_res->Fetch();
			endif;
		endif;

		if (empty($arError)):
			$arSavedData["SECTION_ID"] = $arParams["SECTION_ID"];
			$arSavedData["TIME"] = time();

			if ($handle = fopen($sTmpPath, "wb+")):
				$written = fwrite($handle, serialize($arSavedData));
				fclose($handle);
			endif;
		endif;
	}
	elseif (intVal($_REQUEST["photo_album_id"]) > 0)
	{
		$arParams["SECTION_ID"] = intVal($_REQUEST["photo_album_id"]);
	}
	if (empty($arError))
	{
		$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
		if ($arParams["SECTION_ID"] <= 0)
		{
			$arError["bad_section"] = array(
				"id" => "empty section",
				"text" => GetMessage("P_ERROR_EMPTY_SECTION"));
		}
		else
		{
			$arFilter = array("ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"], "ID" => intVal($arParams["SECTION_ID"]));
			if ($arParams["BEHAVIOUR"] == "USER"):
				$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
				$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
				$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
			endif;
			$db_res = CIBlockSection::GetList(Array(), $arFilter);
			if (!($db_res && $res = $db_res->GetNext())):
				$arError["bad_section"] = array(
					"id" => "section is not found",
					"text" => GetMessage("P_ERROR_BAD_SECTION"));
			else:
				$arSection = $res;
			endif;
		}
	}
/************** Iblock Properties **********************************/
	if (empty($arError))
	{
		$arPictureSights = array(
			"REAL_PICTURE" => array(
				"object_name" => "Thumbnail3_",
				"code" => "real",
				"width" => ($arParams["ORIGINAL_SIZE"] > 0 ? $arParams["ORIGINAL_SIZE"] : false),
				"height" => ($arParams["ORIGINAL_SIZE"] > 0 ? $arParams["ORIGINAL_SIZE"] : false)),
			"THUMBNAIL_PUCTURE" => array(
				"object_name" => "Thumbnail1_",
				"code" => "thumbnail",
				"width" => $arParams["THUMBS_SIZE"]["SIZE"],
				"height" => $arParams["THUMBS_SIZE"]["SIZE"]),
			"PREVIEW_PUCTURE" => array(
				"object_name" => "Thumbnail2_",
				"code" => "preview",
				"width" => $arParams["PREVIEW_SIZE"]["SIZE"],
				"height" => $arParams["PREVIEW_SIZE"]["SIZE"]));
		$counter = 3;
		if (is_array($arParams["PICTURES"]) && !empty($arParams["PICTURES"])):
			foreach ($arParams["PICTURES"] as $key => $val):
				$counter++;
				$arPictureSights[strToUpper($key)."_PICTURE"] = array(
					"object_name" => "Thumbnail".$counter."_",
					"code" => $key,
					"width" => $arParams["PICTURES"][$key]["size"],
					"height" => $arParams["PICTURES"][$key]["size"]);
			endforeach;
		endif;

		foreach ($arPictureSights as $key => $val):
			if ($key == "THUMBNAIL_PUCTURE" || $key == "PREVIEW_PUCTURE"):
				continue;
			endif;
			$db_res = CIBlock::GetProperties($arParams["IBLOCK_ID"], array(), array("CODE" => $key));
			if (!($db_res && $res = $db_res->Fetch())):
				$arPropertiesNeed[] = $key;
			endif;
		endforeach;

		if (!empty($arPropertiesNeed)):
			$obProperty = new CIBlockProperty;
			foreach ($arPropertiesNeed as $Property):
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "F",
					"MULTIPLE" => "N",
					"NAME" => (strLen(GetMessage("P_".strToUpper($Property))) > 0 ? GetMessage("P_".strToUpper($Property)) : strToUpper($Property)),
					"CODE" => strToUpper($Property),
					"FILE_TYPE" => "jpg, gif, bmp, png, jpeg"));
			endforeach;
		endif;

		// Check Public property
		$arPropertiesNeed = array();
		foreach (array("PUBLIC_ELEMENT", "APPROVE_ELEMENT") as $key):
			$db_res = CIBlock::GetProperties($arParams["IBLOCK_ID"], array(), array("CODE" => $key));
			if (!$db_res || !($res = $db_res->Fetch())):
				$arPropertiesNeed[] = $key;
			endif;
		endforeach;

		if (!empty($arPropertiesNeed)):
			$obProperty = new CIBlockProperty;
			foreach ($arPropertiesNeed as $Property):
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "S",
					"MULTIPLE" => "N",
					"NAME" => (strLen(GetMessage("P_".strToUpper($Property))) > 0 ? GetMessage("P_".$Property) : $Property),
					"DEFAULT_VALUE" => "N",
					"CODE" => $Property));
			endforeach;
		endif;
		// Additional properties
/*		$arPropertiesNeed = array();
		foreach (array("EXIF") as $key):
			$db_res = CIBlock::GetProperties($arParams["IBLOCK_ID"], array(), array("CODE" => $key));
			if (!$db_res || !($res = $db_res->Fetch())):
				$arPropertiesNeed[] = $key;
			endif;
		endforeach;

		if (!empty($arPropertiesNeed)):
			$obProperty = new CIBlockProperty;
			foreach ($arPropertiesNeed as $Property):
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "S",
					"MULTIPLE" => "N",
					"NAME" => (strLen(GetMessage("P_".strToUpper($Property))) > 0 ? GetMessage("P_".$Property) : $Property),
					"DEFAULT_VALUE" => "",
					"CODE" => $Property));
			endforeach;
		endif;
*/
	}
/************** Post data ******************************************/
	if (empty($arError))
	{
		for ($i = 1; $i <= intVal($_REQUEST["FileCount"]); $i++)
		{
			$sIndex = rand()."_"; $bRealFile = false;
			if (!empty($_FILES["Thumbnail3_".$i]) && !empty($_FILES["Thumbnail3_".$i]["size"])):
				$bRealFile = "Thumbnail3_".$i;
			elseif (!empty($_FILES["SourceFile_".$i]) && !empty($_FILES["SourceFile_".$i]["size"])):
				$bRealFile = "SourceFile_".$i;
			endif;
			if (!$bRealFile):
				continue;
			endif;

			$arRealFile = $_FILES[$bRealFile];
			$arRealFile["pathinfo"] = pathinfo($arRealFile["name"]);
			$arRealFile["basename"] = str_replace(".".$arRealFile["pathinfo"]["extension"], "", $arRealFile["pathinfo"]["basename"]);
			if ($_REQUEST["FileName_".$i]):
				$arRealFile["pathinfo1"] = pathinfo($_REQUEST["FileName_".$i]);
				$arRealFile["basename"] = str_replace(".".$arRealFile["pathinfo1"]["extension"], "", $arRealFile["pathinfo1"]["basename"]);;
			endif;
			$arRealFile["name"] = $arRealFile["basename"].".".$arRealFile["pathinfo"]["extension"];
			$arRealFile["image"] = false;

			if (is_set($_REQUEST, "ExifDateTime_".$i) && !empty($_REQUEST["ExifDateTime_".$i]))
			{
				$arRealFile["ExifDateTime"] = $_REQUEST["ExifDateTime_".$i];
				$arRealFile["ExifTimeStamp"] = MakeTimeStamp($arRealFile["ExifDateTime"], "YYYY:MM:DD HH:MI:SS");
			}
			if (function_exists("exif_read_data"))
			{
				$number = intVal($_REQUEST["PackageIndex"]) * intVal($arParams["UPLOAD_MAX_FILE"]) + $i;

				$arr = exif_read_data($arRealFile["tmp_name"]);
				$arr2 = array();
				if (!empty($arr))
				{
					foreach ($arr as $key_arr => $val_arr)
					{
						if (!empty($val_arr) && is_string($val_arr))
						{
							$arr[$key_arr] = $arr2[strtolower($key_arr)] = $GLOBALS["APPLICATION"]->ConvertCharset($val_arr, ini_get('exif.encode_unicode'), SITE_CHARSET);
						}
					}
				}
				if (empty($arRealFile["ExifTimeStamp"]))
					$arRealFile["ExifTimeStamp"] = $arr["FileDateTime"];
				if (!empty($arr2["keywords"]))
					$_REQUEST["Tags_".$number] .= $arr2["keywords"];
				if (empty($_REQUEST["Description_".$number]) && !empty($arr2["comments"]))
					$_REQUEST["Description_".$number] = $arr2["comments"];
				if (empty($_REQUEST["Title_".$number]) && !empty($arr2["title"]))
					$_REQUEST["Title_".$number] = $arr2["title"];
				$_REQUEST["Exif_".$number] = $arr;
				$arRealFile["ExifTimeStamp"] = $arr["FileDateTime"];
			}

			if (empty($arRealFile["ExifTimeStamp"])):
				$arRealFile["ExifTimeStamp"] = filemtime($arRealFile["tmp_name"]);
			endif;

			foreach ($arPictureSights as $key => $Sight)
			{
				if ($key == "REAL_PICTURE")
				{
					$File = $arRealFile;
					$bNeedCreateThumbnail = ($arParams["WATERMARK_RULES"] == "ALL" || $_REQUEST["photo_resize_size"] > 0 ||
						!empty($arWaterMark["USER"]["text"]));

					$_REQUEST["photo_resize_size"] = intVal($_REQUEST["photo_resize_size"]);
					// To do check for resize
					if ($_REQUEST["photo_resize_size"] > 2):
						$Sight["width"] = 640; $Sight["height"] = 480;
					elseif ($_REQUEST["photo_resize_size"] > 1):
						$Sight["width"] = 800; $Sight["height"] = 600;
					elseif ($_REQUEST["photo_resize_size"] > 0):
						$Sight["width"] = 1024; $Sight["height"] = 768;
					endif;
					if ($arParams["ORIGINAL_SIZE"] > 0):
						$Sight["width"] = intVal($Sight["width"]);
						if ($Sight["width"] <= 0 || $Sight["width"] > $arParams["ORIGINAL_SIZE"]):
							$Sight["width"] = $arParams["ORIGINAL_SIZE"]; $Sight["height"] = $arParams["ORIGINAL_SIZE"];
						endif;
						$bNeedCreateThumbnail = true;
					endif;

					if ($bNeedCreateThumbnail):
						$File["tmp_name"] = $arParams["PATH_TO_TMP"].$sIndex.$File["name"];
						__ResizeImage($File, $arRealFile, $Sight, 1, $arWaterMark);
					endif;
				}
				else
				{
					// $File - info about thumbs
					if (!empty($_FILES[$Sight["object_name"].$i]) && !empty($_FILES[$Sight["object_name"].$i]["size"])):
						$File = $_FILES[$Sight["object_name"].$i];
						$File["name"] = $arRealFile["basename"]."_".$Sight["code"].".".$arRealFile["pathinfo"]["extension"];
						if ($arParams["WATERMARK_RULES"] == "ALL"):
							$FileTmp = $File;
							$File["tmp_name"] = $arParams["PATH_TO_TMP"].$sIndex.$File["name"];
							$APPLICATION->RestartBuffer();
							__ResizeImage($File, $FileTmp, array("width" => false, "height" => false), 1, $arWaterMark);
						endif;
					else:
						$File = $_FILES[$bRealFile];
						$File["name"] = $arRealFile["basename"]."_".$Sight["code"].".".$arRealFile["pathinfo"]["extension"];
						$File["tmp_name"] = $arParams["PATH_TO_TMP"].$sIndex.$File["name"];
						__ResizeImage($File, $arRealFile, $Sight, 1, $arWaterMark);
					endif;
				}
				$arFiles[$i][$key] = $File;
				$arProp[$i][$key] = array("n0" => $File);
			}
			$arFiles[$i]["basename"] = $arRealFile["basename"];
			$arFiles[$i]["name"] = $arRealFile["name"];
			@imagedestroy($arRealFile["image"]);
		}
	}
	if (empty($arError) && empty($arFiles))
	{
		$arError["empty_post"] = array(
			"id" => "empty_post",
			"text" => GetMessage("P_EMPTY_POST"));
	}
	elseif (empty($arError))
	{
/************** Album cover ****************************************/
		if (intVal($arSection["PICTURE"]) <= 0 ? true : false)
		{
			$arAlbumsFoto = array(); $arAlbumSights = array();
			$arAlbumSights["ALBUM_PICTURE"] = array(
				"code" => "album",
				"notes" => "for_album",
				"width" => $arParams["ALBUM_PHOTO"]["SIZE"],
				"height" => $arParams["ALBUM_PHOTO"]["SIZE"]);
			$arAlbumSights["ALBUM_PICTURE_THUMBS"] = array(
				"code" => "album_thumbs",
				"notes" => "for_album",
				"width" => $arParams["ALBUM_PHOTO_THUMBS"]["SIZE"],
				"height" => $arParams["ALBUM_PHOTO_THUMBS"]["SIZE"]);
			$arTmp = $arFiles;
			$arTmp = array_shift($arTmp);
			$arRealFile = $arTmp["REAL_PICTURE"];

			foreach ($arAlbumSights as $key => $Sight)
			{
				$File = $arRealFile;
				$File["basename"] = str_replace(".".$File["pathinfo"]["extension"], "", $File["pathinfo"]["basename"]);;
				$File["name"] = $File["basename"]."_".$Sight["code"].".".$File["pathinfo"]["extension"];
				$File["tmp_name"] = $arParams["PATH_TO_TMP"].$File["name"];
				__ResizeImage($File, $arRealFile, $Sight, 2);
				$File["MODULE_ID"] = "iblock";
				$arAlbumsFoto[$key] = $File;
			}
			@imagedestroy($arRealFile["image"]);

			$bs = new CIBlockSection;
			$arFields = Array(
				"PICTURE" => $arAlbumsFoto["ALBUM_PICTURE_THUMBS"],
				"DETAIL_PICTURE" => $arAlbumsFoto["ALBUM_PICTURE"]);

			$res = $bs->Update($arSection["ID"], $arFields, false, false);

			@unlink($arAlbumsFoto["ALBUM_PICTURE_THUMBS"]["tmp_name"]);
			@unlink($arAlbumsFoto["ALBUM_PICTURE"]["tmp_name"]);
		}
/************** Saving photos **************************************/
		$iFileSize = 0;
		foreach ($arFiles as $i => $File)
		{
			$Prop = $arProp[$i];
			unset($Prop["THUMBNAIL_PUCTURE"]);
			unset($Prop["PREVIEW_PUCTURE"]);

			$number = intVal($_REQUEST["PackageIndex"]) * intVal($arParams["UPLOAD_MAX_FILE"]) + $i;
			$_REQUEST["Public_".$number] = ($_REQUEST["Public_".$number] == "Y" ? "Y" : "N");
			$Prop["PUBLIC_ELEMENT"] = array("n0" => $_REQUEST["Public_".$number]);
			$Prop["APPROVE_ELEMENT"] = array("n0" => (($arParams["ABS_PERMISSION"] >= "U" || $arParams["APPROVE_BY_DEFAULT"] == "Y") &&
				$_REQUEST["Public_".$number] == "Y" ? "Y" : "X"));
/*			if (!empty($_REQUEST["Exif_".$number]) && is_array($_REQUEST["Exif_".$number]))
				$Prop["EXIF"] = array("n0" => serialize($_REQUEST["Exif_".$number]));
*/
			$strError = "";
			$res_file = array("status" => "success");
			$ID = 0;

			if ($arResult["UPLOAD_MAX_FILE_SIZE"] > 0)
			{
				foreach ($File as $k => $v):
					if ($v["size"] > $arResult["UPLOAD_MAX_FILE_SIZE"]):
						$strError = str_replace("#UPLOAD_MAX_FILE_SIZE#", $arParams["UPLOAD_MAX_FILE_SIZE"], GetMessage("P_ERR_UPLOAD_MAX_FILE_SIZE"));
						break;
					endif;
				endforeach;
			}
			$ID = 0;
			if (empty($strError))
			{
				$arFields = Array(
					"ACTIVE" => (($arParams["MODERATION"] == "Y" && $arParams["ABS_PERMISSION"] < "U") ? "N" : "Y"),
					"MODIFIED_BY" => $USER->GetID(),
					"IBLOCK_SECTION" => $arSection["ID"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"NAME" => (!empty($_REQUEST["Title_".$number]) ? $_REQUEST["Title_".$number] : $File["basename"]),
					"CODE" => $File["REAL_PICTURE"]["name"],
					"TAGS" => $_REQUEST["Tags_".$number],
					"PREVIEW_PICTURE" => $File["THUMBNAIL_PUCTURE"],
					"PREVIEW_TEXT" => $_REQUEST["Description_".$number],
					"PREVIEW_TEXT_TYPE" => "text",
					"DETAIL_PICTURE" => $File["PREVIEW_PUCTURE"],
					"DETAIL_TEXT" => $_REQUEST["Description_".$number],
					"DETAIL_TEXT_TYPE" => "text",
					"PROPERTY_VALUES" => $Prop);
				$arFields["NAME"] = (!empty($arFields["NAME"]) ? $arFields["NAME"] : $File["REAL_PICTURE"]["name"]);
				$arFields["DATE_CREATE"] = (intVal($arRealFile["ExifTimeStamp"]) > 0 ?
					ConvertTimeStamp($arRealFile["ExifTimeStamp"], "FULL") : $arFields["DATE_CREATE"]);

				$bs = new CIBlockElement;
				$ID = $bs->Add($arFields);
				if ($ID <= 0):
					$strError = $bs->LAST_ERROR;
					$arTmp = explode("<br>", $strError);
					if (!empty($arTmp) && !empty($arTmp[1])):
						$strError = $arTmp[1];
					endif;
				else:
					CIBlockElement::RecalcSections($ID);
				endif;
			}
			if ($_REQUEST["photo_album_id"] == "new"):
			PClearComponentCache(array(
				"search.page",
				"search.tags.cloud",
				"photogallery.detail",
				"photogallery.detail.comment",
				"photogallery.detail.edit",
				"photogallery.detail.list",
				"photogallery.gallery.edit",
				"photogallery.gallery.list",
				"photogallery.section",
				"photogallery.section.edit",
				"photogallery.section.edit.icon",
				"photogallery.section.list",
				"photogallery.upload",
				"photogallery.user",
				"photogallery_user",
				"photogallery"));
			else:
			PClearComponentCache(array(
				"search.page",
				"search.tags.cloud",
				"photogallery.upload",
				"photogallery.detail/".$arParams["IBLOCK_ID"]."/section".$arSection["ID"],
				"photogallery.detail.list/".$arParams["IBLOCK_ID"]."/detaillist/0",
				"photogallery.detail.list/".$arParams["IBLOCK_ID"]."/detaillist/".$arSection["ID"],
				"photogallery.section/".$arParams["IBLOCK_ID"]."/section0",
				"photogallery.section/".$arParams["IBLOCK_ID"]."/section".$arSection["ID"],
				"photogallery.section/".$arParams["IBLOCK_ID"]."/section".$arSection["IBLOCK_SECTION_ID"],
				"photogallery.section.list/".$arParams["IBLOCK_ID"]."/section0",
				"photogallery.section.list/".$arParams["IBLOCK_ID"]."/section".$arSection["ID"],
				"photogallery.section.list/".$arParams["IBLOCK_ID"]."/section".$arSection["IBLOCK_SECTION_ID"],
				"photogallery.section.list/".$arParams["IBLOCK_ID"]."/sections0",
				"photogallery.section.list/".$arParams["IBLOCK_ID"]."/sections".$arSection["ID"],
				"photogallery.section.list/".$arParams["IBLOCK_ID"]."/sections".$arSection["IBLOCK_SECTION_ID"]));
			endif;

			if (intVal($ID) <= 0)
			{
				$bVarsFromForm = true;
				$res_file = array("status" => "error", "error" => $strError);
			}
			else
			{
				$arFields["ID"] = $ID;
				if(function_exists('BXIBlockAfterSave'))
					BXIBlockAfterSave($arFields);
				$iFileSize += doubleVal($File["REAL_PICTURE"]["size"]);

				$db_res = CIBlockElement::GetList(array("ID" => "DESC"), array("ID" => $ID), false, false, array("PREVIEW_PICTURE"));
				if ($db_res && $res = $db_res->Fetch()):
					$tmp = CFile::GetFileArray($res["PREVIEW_PICTURE"]);
					$res_file["path"] = $tmp["SRC"];
				endif;
			}
			$res_file["id"] = $arFields["ID"];
			$res_file["title"] = $arFields["NAME"];

			// Main info about file
			$result["FILE"][$File["REAL_PICTURE"]["name"]] = $res_file;
			$result["FILE"][$File["REAL_PICTURE"]["name"]]["number"] = $number;
			// Additional info about file
			$res_file["number"] = $i;
			$res_file["description"] = $arFields["PREVIEW_TEXT"];
			$result["FILE_INFO"][$File["REAL_PICTURE"]["name"]] = $res_file;

			foreach ($File as $key => $val)
				@unlink($val["tmp_name"]);
		}
	}
	$bVarsFromForm = ($bVarsFromForm ? $bVarsFromForm : !empty($arError));
/************** Answer *********************************************/
	if (!empty($arError)):
		$arSavedData["status"] = "error";
		$e = new CAdminException($arError);
		$arSavedData["error"] = $e->GetString();
	endif;
	if (is_array($result["FILE"])):
		foreach ($result["FILE"] as $key => $val):
			$arSavedData["files"][$key] = $val;
		endforeach;
	endif;
	if ($_REQUEST["CACHE_RESULT"] == "Y" && ($handle = fopen($sTmpPath, "wb+"))):
		$written = fwrite($handle, serialize($arSavedData));
		fclose($handle);
	endif;

	$uploader = $arSavedData;
	$uploader["status"] = (!empty($uploader["status"]) ? $uploader["status"] : "success");
	$uploader["error"] = trim($uploader["error"]);
	$uploader["files"] = (is_array($uploader["files"]) ? $uploader["files"] : array());
	$uploader["section_id"] = $arParams["SECTION_ID"];
	$uploader["url"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
		array("USER_ALIAS" => $arResult["GALLERY"]["CODE"], "SECTION_ID" => $arSection["ID"]));

	$arResult["RETURN_DATA"] = $uploader;

	if ($_REQUEST["FORMAT_ANSWER"] != "return")
	{
		if ($_REQUEST["redirect"] != "Y"):
			$APPLICATION->RestartBuffer();
			if ($_REQUEST["CONVERT"] == "Y")
				array_walk($uploader, '__Escape');
			?><?=CUtil::PhpToJSObject($uploader);?><?
			die();
		elseif (!$bVarsFromForm):
			LocalRedirect($uploader["url"]);
		endif;
	}
	else
	{
		$arResult["RETURN_DATA"]["current_files"] = $result["FILE_INFO"];
		if ($_REQUEST["AJAX_CALL"] == "Y" || !$bVarsFromForm):
			return $arResult["RETURN_DATA"];
		endif;
	}

	$arResult["ERROR_MESSAGE"] = $arSavedData["error"];
	foreach ($result["FILE"] as $key => $val):
		$arResult["ERROR_MESSAGE"] .= $val;
	endforeach;
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Chain Item *****************************************/
if (!empty($arParams["SECTION_ID"]))
{
	$arFilter = array(
		"ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y");
		$arFilter["ID"]=$arParams["SECTION_ID"];
	$db_res = CIBlockSection::GetList(array(), $arFilter);
	if ($db_res && $arResult["SECTION"] = $db_res->GetNext()):
		$rsPath = GetIBlockSectionPath($arParams["IBLOCK_ID"], $arResult["SECTION"]["ID"]);
		if ($rsPath):
			while ($arPath = $rsPath->GetNext())
			{
				$arResult["SECTION"]["PATH"][] = $arPath;
			}
		endif;
	endif;
}
elseif ($arParams["BEHAVIOUR"] == "USER")
{
	$arResult["SECTION"] = $arResult["GALLERY"];
	$arResult["SECTION"]["PATH"] = array($arResult["GALLERY"]);
}
$arResult["SECTION"]["PATH"] = (is_array($arResult["SECTION"]["PATH"]) ? $arResult["SECTION"]["PATH"] : array());
/************** Sections List **************************************/
$arResult["SECTION_LIST"] = array();
$arFilter = array("ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y",
	"IBLOCK_ID" => $arParams["IBLOCK_ID"], "IBLOCK_ACTIVE" => "Y");
if ($arParams["BEHAVIOUR"] == "USER")
{
	$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
	$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
	$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
}
$rsIBlockSectionList = CIBlockSection::GetTreeList($arFilter);
$iDiff = ($arParams["BEHAVIOUR"] == "USER" ? 2 : 1);
while ($arSection = $rsIBlockSectionList->GetNext())
{
	$len = ($arSection["DEPTH_LEVEL"] - $iDiff);
	$arSection["NAME"] = ($len > 0 ? str_repeat(" . ", $len) : "").$arSection["NAME"];
	$arResult["SECTION_LIST"][$arSection["ID"]] = $arSection["NAME"];
}
/********************************************************************
				/Data
********************************************************************/
/********************************************************************
				For custom components
********************************************************************/
foreach ($arResult["URL"] as $key => $val):
	$arResult[$key."_LINK"] = $val;
endforeach;
/********************************************************************
				/For custom components
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));

$this->IncludeComponentTemplate();

/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("P_TITLE"));
/************** Breadcrumb *****************************************/
if ($arParams["SET_NAV_CHAIN"] == "Y")
{
	$bFound = ($arParams["BEHAVIOUR"] != "USER");
	foreach ($arResult["SECTION"]["PATH"] as $arPath)
	{
		if (!$bFound):
			$bFound = $arResult["GALLERY"]["ID"] == $arPath["ID"];
			continue;
		endif;
		$APPLICATION->AddChainItem($arPath["NAME"], CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"])));
	}
	$APPLICATION->AddChainItem(GetMessage("P_TITLE_CHAIN"));
}

/************** Admin panel ****************************************/
// if ($arParams["DISPLAY_PANEL"] == "Y" && $GLOBALS["USER"]->IsAuthorized()):
	// CIBlock::ShowPanel($arParams["IBLOCK_ID"], $arResult["SECTION"]["ID"], $arResult["SECTION"]["IBLOCK_SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
// endif;
/************** Return Results *************************************/
if ($_REQUEST["FORMAT_ANSWER"] == "return"):
	return $arResult["RETURN_DATA"];
endif;
/********************************************************************
				/Standart
********************************************************************/
/*
GetMessage("P_APPROVE_ELEMENT");
GetMessage("P_PUBLIC_ELEMENT");
GetMessage("P_REAL_PICTURE");
*/
?>