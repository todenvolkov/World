<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!CModule::IncludeModule("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return 0;
elseif ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"])):
	ShowError(GetMessage("P_GALLERY_EMPTY"));
	return 0;
endif;
// **************************************************************************************
if(!function_exists("__UnEscape"))
{
	function __UnEscape(&$item, $key)
	{
		if(is_array($item))
			array_walk($item, '__UnEscape');
		elseif (strpos($item, "%u") !== false)
			$item = $GLOBALS["APPLICATION"]->UnJSEscape($item);
		elseif (LANG_CHARSET != "UTF-8" && preg_match("/^.{1}/su", $item) == 1)
			$item = $GLOBALS["APPLICATION"]->ConvertCharset($item, "UTF-8", LANG_CHARSET);
	}
}
/********************************************************************
				Input params
********************************************************************/
//***************** BASE *******************************************/
	$arParams["IBLOCK_TYPE"] = trim(!empty($arParams["IBLOCK_TYPE"]) ? $arParams["IBLOCK_TYPE"] : $_REQUEST["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intVal(intVal($arParams["IBLOCK_ID"]) > 0 ? $arParams["IBLOCK_ID"] : $_REQUEST["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intVal(intVal($arParams["SECTION_ID"]) > 0 ? $arParams["SECTION_ID"] : $_REQUEST["SECTION_ID"]);
	$arParams["ELEMENT_ID"] = intVal(intVal($arParams["ELEMENT_ID"]) > 0 ? $arParams["ELEMENT_ID"] : $_REQUEST["ELEMENT_ID"]);
	$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);	
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	$arParams["ACTION"] = trim(empty($arParams["ACTION"]) ? $_REQUEST["ACTION"] : $arParams["ACTION"]);
	$arParams["ACTION"] = strToUpper(empty($arParams["ACTION"]) ? "EDIT" : $arParams["ACTION"]);
	
	$arParams["AJAX_CALL"] = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"detail" => "PAGE_NAME=detail".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#");

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
//***************** ADDITTIONAL ************************************/
	$arParams["DATE_TIME_FORMAT"] = trim($arParams["DATE_TIME_FORMAT"]);
	if(strlen($arParams["DATE_TIME_FORMAT"])<=0)
		$arParams["DATE_TIME_FORMAT"] = $DB->DateFormatToPHP(CSite::GetDateFormat("FULL"));
	$arParams["SET_STATUS_404"] = ($arParams["SET_STATUS_404"] == "Y" ? "Y" : "N");
//***************** STANDART ***************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
	$strWarning = "";
	$bVarsFromForm = false;
	if ($arParams["AJAX_CALL"] == "Y")
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
	}
	
	$arResult["ELEMENT"] = array();
	$arResult["ERROR_MESSAGE"] = "";
	$arParams["PERMISSION"] = "D";
	$arParams["ABS_PERMISSION"] = "D";

	$cache = new CPHPCache;
	$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/".$arParams["IBLOCK_ID"]."/");
/************** ELEMENT ********************************************/
//SELECT
$arSelect = array(
	"ID",
	"IBLOCK_ID",
	"IBLOCK_SECTION_ID",
	"NAME",
	"ACTIVE", 
	"PREVIEW_TEXT",
	"DETAIL_TEXT",
	"PREVIEW_TEXT_TYPE",
	"DETAIL_TEXT_TYPE",
	"TAGS",
	"DATE_CREATE",
	"CREATED_BY",
	"PROPERTY_PUBLIC_ELEMENT",
	"PROPERTY_APPROVE_ELEMENT");
//WHERE
$arFilter = array(
	"ID" => $arParams["ELEMENT_ID"],
	"IBLOCK_ACTIVE" => "Y",
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"CHECK_PERMISSIONS" => "Y",
	"SECTION_ID" => $arParams["SECTION_ID"]);

//EXECUTE
$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
$obElement = $rsElement->GetNextElement();
if (empty($obElement)):
	ShowError(GetMessage("PHOTO_ELEMENT_NOT_FOUND"));
	if ($arParams["SET_STATUS_404"] == "Y")
		CHTTP::SetStatus("404 Not Found");
	return 0;
endif;

$arResult["ELEMENT"] = $obElement->GetFields();
$arResult["ELEMENT"]["PROPERTIES"] = $obElement->GetProperties();

if ($arParams["SECTION_ID"] != $arResult["ELEMENT"]["IBLOCK_SECTION_ID"]):
	$url = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], 
			"SECTION_ID" => $arResult["ELEMENT"]["IBLOCK_SECTION_ID"], 
			"ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
	LocalRedirect($url, false, "301 Moved Permanently");
	return false;
endif;
/************** GALLERY & SECTION & PERMISSION *********************/
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
$arParams["ABS_PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
$arResult["SECTION"] = array();
$res = $oPhoto->GetSection($arParams["SECTION_ID"], $arResult["SECTION"]);
if ($res > 400)
{
	return false;
}
elseif ($res == 301)
{
	ShowError(GetMessage("P_BAD_SECTION"));
	die(); 
	$url = CComponentEngine::MakePathFromTemplate(
		$arParams["~SECTION_URL"],
		array(
			"USER_ALIAS" => $arGallery["CODE"], 
			"SECTION_ID" => $arParams["SECTION_ID"]));
	LocalRedirect($url, false, "301 Moved Permanently");
	return false;
}
elseif ($arParams["PERMISSION"] < "U")
{
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return false;
}

// URL`s
$arResult["~SECTION_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
	array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
$arResult["~DETAIL_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], 
	array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
$arResult["DETAIL_LINK"] = htmlspecialcharsEx($arResult["~DETAIL_LINK"]);
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Action
********************************************************************/
if($_REQUEST["edit"] == "Y" || $arParams["ACTION"] == "DROP")
{
	array_walk($_REQUEST, '__UnEscape');
	$arError = array();
	$result = array();
	
	if(!(check_bitrix_sessid()))
	{
		$arError[] = array(
			"id" => "bad sessid", 
			"text" => GetMessage("IBLOCK_WRONG_SESSION"));
	}
	elseif ($arParams["ACTION"] == "DROP")
	{
		@set_time_limit(0);
		$APPLICATION->ResetException();
		
		if (!CIBlockElement::Delete($arParams["ELEMENT_ID"]))
		{
			if ($ex = $APPLICATION->GetException()):
				$arError[] = array(
					"id" => "delete", 
					"text" => $ex->GetString());
			else:
				$arError[] = array(
					"id" => "delete", 
					"text" => "Element was droped with error.");
			endif;
		}
		else 
		{
			$result = array("url" => $arResult["~SECTION_LINK"]);
			$arResult["URL"] = $arResult["~SECTION_LINK"];
		}
	}
	else
	{
		$arFields = Array(
			"MODIFIED_BY" => $USER->GetID(),
			"IBLOCK_SECTION" => $_REQUEST["TO_SECTION_ID"],
			"TAGS" => $_REQUEST["TAGS"],
			"NAME" => $_REQUEST["TITLE"],
			"PREVIEW_TEXT" => $_REQUEST["DESCRIPTION"],
			"DETAIL_TEXT" => $_REQUEST["DESCRIPTION"],
			"DATE_CREATE" => $_REQUEST["DATE_CREATE"],
			"DETAIL_TEXT_TYPE" => "text",
			"PREVIEW_TEXT_TYPE" => "text");

		if ($arParams["BEHAVIOUR"] == "USER"):
			$_REQUEST["ACTIVE"] = ($_REQUEST["ACTIVE"] == "Y" ? "Y" : "N");
			if ($arParams["ABS_PERMISSION"] >= "U" && $arResult["ELEMENT"]["ACTIVE"] != $_REQUEST["ACTIVE"]):
				$arFields["ACTIVE"] = $_REQUEST["ACTIVE"];
			endif;
		endif;
		
		$bs = new CIBlockElement;
		$ID = $bs->Update($arParams["ELEMENT_ID"], $arFields);
		if ($ID <= 0)
		{
			$arError[] = array(
				"id" => "update", 
				"text" => $bs->LAST_ERROR);
		}
		else
		{
			$_REQUEST["PUBLIC_ELEMENT"] = ($_REQUEST["PUBLIC_ELEMENT"] == "Y" ? "Y" : "N");
			$_REQUEST["APPROVE_ELEMENT"] = ($_REQUEST["APPROVE_ELEMENT"] == "Y" ? "Y" : "N");
			
			if ($arParams["BEHAVIOUR"] == "USER"):
				if (is_set($arResult["ELEMENT"]["PROPERTIES"], "PUBLIC_ELEMENT") && 
					$arResult["ELEMENT"]["PROPERTIES"]["PUBLIC_ELEMENT"]["VALUE"] != $_REQUEST["PUBLIC_ELEMENT"]):
					CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $arParams["IBLOCK_ID"], $_REQUEST["PUBLIC_ELEMENT"], "PUBLIC_ELEMENT"); 
					if ($arParams["ABS_PERMISSION"] < "U" && $_REQUEST["PUBLIC_ELEMENT"] == "Y"):
						CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $arParams["IBLOCK_ID"], 'X', "APPROVE_ELEMENT"); 
					endif;
				endif;
				
				if ($arParams["ABS_PERMISSION"] >= "U" && is_set($arResult["ELEMENT"]["PROPERTIES"], "APPROVE_ELEMENT") && 
					$arResult["ELEMENT"]["PROPERTIES"]["APPROVE_ELEMENT"]["VALUE"] != $_REQUEST["APPROVE_ELEMENT"]):
					CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $arParams["IBLOCK_ID"], $_REQUEST["APPROVE_ELEMENT"], "APPROVE_ELEMENT"); 
				endif;
			endif;
			
			if ($arParams["SECTION_ID"] != $_REQUEST["TO_SECTION_ID"]):
				CIBlockElement::RecalcSections($arParams["SECTION_ID"]);
				CIBlockElement::RecalcSections($_REQUEST["TO_SECTION_ID"]);
			endif;
			
			if ($arParams["AJAX_CALL"] != "Y")
			{}
			elseif ($arParams["SECTION_ID"] != $_REQUEST["TO_SECTION_ID"])
			{
				$result = array(
					"url" => CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], 
						"SECTION_ID" => $_REQUEST["TO_SECTION_ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"])));
			}
			else 
			{
				$arSelect = array("ID", "NAME", "DETAIL_TEXT", "DETAIL_TEXT_TYPE", "TAGS", "DATE_CREATE", "CREATED_BY" );
				$db_res = CIBlockElement::GetList(array(), array("ID" => $arParams["ELEMENT_ID"]), false, false, $arSelect);
				if ($db_res && $res = $db_res->GetNext())
				{
					$result = array(
						"TAGS" => $res["TAGS"],
						"TITLE" => $res["NAME"],
						"DESCRIPTION" => $res["DETAIL_TEXT"],
						"DATE" => $res["DATE_CREATE"]);
				}
				else 
				{
					$result = array(
						"TAGS" => htmlspecialcharsEx($_REQUEST["TAGS"]),
						"TITLE" => htmlspecialcharsEx($_REQUEST["TITLE"]),
						"DESCRIPTION" => htmlspecialcharsEx($_REQUEST["DESCRIPTION"]),
						"DATE" => htmlspecialcharsEx($_REQUEST["DATE_CREATE"]));
				}
			}
			$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], 
				"SECTION_ID" => $_REQUEST["TO_SECTION_ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
		}
	}
	
	if (empty($arError))
	{
			PClearComponentCache(array(
				"search.page",
				"search.tags.cloud", 
				"photogallery.detail/".$arParams["IBLOCK_ID"]."/detail/".$_REQUEST["TO_SECTION_ID"]."/", 
				"photogallery.detail/".$arParams["IBLOCK_ID"]."/detail/".$arResult["SECTION"]["ID"]."/", 
				"photogallery.detail.comment", 
//				"photogallery.detail.edit", 
				"photogallery.detail.list/".$arParams["IBLOCK_ID"]."/detaillist/0", 
				"photogallery.detail.list/".$arParams["IBLOCK_ID"]."/detaillist/".$_REQUEST["TO_SECTION_ID"], 
				"photogallery.detail.list/".$arParams["IBLOCK_ID"]."/detaillist/".$arResult["SECTION"]["ID"], 
//				"photogallery.gallery.edit", 
//				"photogallery.gallery.list", 
				"photogallery.section/".$arParams["IBLOCK_ID"]."/section".$arResult["SECTION"]["ID"], 
				"photogallery.section/".$arParams["IBLOCK_ID"]."/section".$arResult["SECTION"]["IBLOCK_SECTION_ID"], 
//				"photogallery.section.edit", 
//				"photogallery.section.edit.icon", 
				"photogallery.section.list/".$arParams["IBLOCK_ID"]."/section".$arResult["SECTION"]["ID"], 
				"photogallery.section.list/".$arParams["IBLOCK_ID"]."/sections".$arResult["SECTION"]["IBLOCK_SECTION_ID"], 
//				"photogallery.upload", 
//				"photogallery.user"
				)
			);
		
		if ($arParams["AJAX_CALL"] == "Y")
		{
			$APPLICATION->RestartBuffer();
			$result["DATE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($result["DATE"], CSite::GetDateFormat()));
			?><?=CUtil::PhpToJSObject($result);?><?
			die();
		}
		else 
		{
			LocalRedirect($arResult["URL"]);
		}
	}
	else 
	{
		$bVarsFromForm = true;
		$e = new CAdminException($arError);
		$arResult["ERROR_MESSAGE"] = $e->GetString();
	}
}
elseif ($_REQUEST["edit"] == "cancel")
{
	LocalRedirect($arResult["~DETAIL_LINK"]);
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["ELEMENT"]["NAME"] = htmlspecialcharsEx($arResult["ELEMENT"]["~NAME"]);
$arResult["ELEMENT"]["DETAIL_TEXT"] = htmlspecialcharsEx($arResult["ELEMENT"]["~DETAIL_TEXT"]);
$arResult["ELEMENT"]["TAGS"] = htmlspecialcharsEx($arResult["ELEMENT"]["~TAGS"]);
if ($bVarsFromForm)
{
	$arResult["ELEMENT"]["NAME"] = htmlspecialcharsEx($_REQUEST["TITLE"]);
	$arResult["ELEMENT"]["DETAIL_TEXT"] = htmlspecialcharsEx($_REQUEST["DESCRIPTION"]);
	$arResult["ELEMENT"]["TAGS"] = htmlspecialcharsEx($_REQUEST["TAGS"]);
	$arResult["ELEMENT"]["IBLOCK_SECTION_ID"] = htmlspecialcharsEx($_REQUEST["TO_SECTION_ID"]);
	$arResult["ELEMENT"]["DATE_CREATE"] = htmlspecialcharsEx($_REQUEST["DATE"]);
}
	
/********************************************************************
				Sections list
********************************************************************/
$arResult["SECTION_LIST"] = array();
$arFilter = array(
	"ACTIVE" => "Y",
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"IBLOCK_ACTIVE" => "Y");
if ($arParams["BEHAVIOUR"] == "USER")
{
	$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
	$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
	$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
}
$rsIBlockSectionList = CIBlockSection::GetTreeList($arFilter);
while ($arSection = $rsIBlockSectionList->GetNext())
{
	$len = ($arSection["DEPTH_LEVEL"] - 1); 
	$arSection["NAME"] = ($len > 0 ? str_repeat(" . ", $len) : "").$arSection["NAME"];
	$arResult["SECTION_LIST"][$arSection["ID"]] = $arSection["NAME"];
}
$arResult["I"] = array(
	"PERMISSION" => $arParams["PERMISSION"], 
	"ABS_PERMISSION" => $arParams["ABS_PERMISSION"]);
/********************************************************************
				/Data
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));

$this->IncludeComponentTemplate();

/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["ELEMENT"]["NAME"]);
/************** BreadCrumb *****************************************/
if ($arParams["SET_NAV_CHAIN"] == "Y")
{
	$bFounded = ($arParams["BEHAVIOUR"] != "USER");
	foreach($arResult["SECTION"]["PATH"] as $arPath)
	{
		if (!$bFounded):
			$bFounded = ($arResult["GALLERY"]["ID"] == $arPath["ID"]);
			continue;
		endif;
		
		$GLOBALS["APPLICATION"]->AddChainItem($arPath["NAME"], 
			CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"])));
	}
	$APPLICATION->AddChainItem($arResult["ELEMENT"]["NAME"], $arResult["~DETAIL_LINK"]);
}
/************** Admin Panel ****************************************/
// if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized()):
	// CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
// endif;
/********************************************************************
				/Standart
********************************************************************/
?>