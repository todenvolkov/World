<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!CModule::IncludeModule("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
elseif ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"])):
	ShowError(GetMessage("P_GALLERY_EMPTY"));
	return 0;
endif;

/***************** Functions ***************************************/
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
/***************** Functions/***************************************/
if (empty($arParams["INDEX_URL"]) && !empty($arParams["SECTIONS_TOP_URL"]))
	$arParams["INDEX_URL"] = $arParams["SECTIONS_TOP_URL"];

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);

	$arParams["ACTION"] = (empty($arParams["ACTION"]) ? $_REQUEST["ACTION"] : $arParams["ACTION"]);
	$arParams["ACTION"] = strToUpper(empty($arParams["ACTION"]) ? "EDIT" : $arParams["ACTION"]);

	$arParams["AJAX_CALL"] = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#",
		"section_edit" => "PAGE_NAME=section_edit".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).	"&SECTION_ID=#SECTION_ID#",
		"section_edit_icon" => "PAGE_NAME=section_edit_icon".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
		"section_drop" => "PAGE_NAME=section_drop".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#"
	);

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] : $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
	$arParams["SHOW_PHOTO_USER"] = ($arParams["SHOW_PHOTO_USER"] == "Y" ? "Y" : "N");// hidden params for custom components
	$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
	$arParams["SET_STATUS_404"] = ($arParams["SET_STATUS_404"] == "Y" ? "Y" : "N");
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/
if ($arParams["AJAX_CALL"] == "Y")
	$GLOBALS['APPLICATION']->RestartBuffer();

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

$bError = true;
if ($oPhoto)
{
	$bError = false;
	$arResult["GALLERY"] = $oPhoto->Gallery;
	$arParams["PERMISSION"] = $oPhoto->User["Permission"];
	if ($arParams["PERMISSION"] < "U")
	{
		ShowError(GetMessage("P_ACCESS_DENIED"));
		$bError = true;
	}
	elseif ($arParams["SECTION_ID"] > 0 && ($oPhoto->GetSection($arParams["SECTION_ID"], $arResult["SECTION"]) > 300))
	{
		$bError = true;
	}
}

if ($bError)
{
	if ($arParams["AJAX_CALL"] == "Y")
		die();
	return false;
}
/********************************************************************
				Default params
********************************************************************/
	$strWarning = "";
	$bVarsFromForm = false;
	$cache = new CPHPCache;
/********************************************************************
				/Default params
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** PROPERTIES *****************************************/
$arUserFields = $arResult["USER_FIELDS"];
if (empty($arUserFields) || empty($arUserFields["UF_DATE"]))
{
	$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", "FIELD_NAME" => "UF_DATE"));
	if (!($db_res && $res = $db_res->GetNext()))
	{
		$arFields = Array(
			"ENTITY_ID" => "IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION",
			"FIELD_NAME" => "UF_DATE",
			"USER_TYPE_ID" => "datetime",
			"MULTIPLE" => "N",
			"MANDATORY" => "N");
		$arFieldName = array();
		$rsLanguage = CLanguage::GetList($by, $order, array());
		while($arLanguage = $rsLanguage->Fetch()):
			$arFieldName[$arLanguage["LID"]] = GetMessage("IBLOCK_DATE");
			$arFieldName[$arLanguage["LID"]] = (empty($arFieldName[$arLanguage["LID"]]) ? "Date" : $arFieldName[$arLanguage["LID"]]);
		endwhile;
		$arFields["EDIT_FORM_LABEL"] = $arFieldName;
		$obUserField  = new CUserTypeEntity;
		$obUserField->Add($arFields);
		$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
	}

}
if (empty($arUserFields) || empty($arUserFields["UF_PASSWORD"]))
{
	$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", "FIELD_NAME" => "UF_PASSWORD"));
	if (!($db_res && $res = $db_res->GetNext()))
	{
		$arFields = Array(
			"ENTITY_ID" => "IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION",
			"FIELD_NAME" => "UF_PASSWORD",
			"USER_TYPE_ID" => "string",
			"MULTIPLE" => "N",
			"MANDATORY" => "N");
		$arFieldName = array();
		$rsLanguage = CLanguage::GetList($by, $order, array());
		while($arLanguage = $rsLanguage->Fetch()):
			$arFieldName[$arLanguage["LID"]] = GetMessage("IBLOCK_PASSWORD");
			$arFieldName[$arLanguage["LID"]] = (empty($arFieldName[$arLanguage["LID"]]) ? "Password" : $arFieldName[$arLanguage["LID"]]);
		endwhile;
		$arFields["EDIT_FORM_LABEL"] = $arFieldName;
		$obUserField  = new CUserTypeEntity;
		$obUserField->Add($arFields);
		$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
	}
}

if ((empty($arUserFields) || empty($arUserFields["UF_DATE"]) || empty($arUserFields["UF_PASSWORD"])))
{
	$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arResult["SECTION"]["ID"], LANGUAGE_ID);
}
$arResult["SECTION"]["~DATE"] = $arUserFields["UF_DATE"];
$arResult["SECTION"]["~PASSWORD"] = $arUserFields["UF_PASSWORD"];
/********************************************************************
				/Data
********************************************************************/

/********************************************************************
				Actions
********************************************************************/
if (isset($_REQUEST["cancel"]))
{
	LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"])));
}
elseif($_REQUEST["save_edit"] == "Y" || $_REQUEST["edit"] == "Y")
{
	array_walk($_REQUEST, '__UnEscape');
	if(!check_bitrix_sessid())
	{
		$strWarning = GetMessage("IBLOCK_WRONG_SESSION")."<br>";
		$bVarsFromForm = true;
	}
	elseif ($arParams["ACTION"] != "NEW" && $arParams["ACTION"] != "DROP")
	{
		$arFields = array("IBLOCK_ID"=>$arParams["IBLOCK_ID"]);

		if (isset($_REQUEST["UF_DATE"]))
		{
			$arFields["UF_DATE"] = $_REQUEST["UF_DATE"];
			$arFields["DATE"] = $_REQUEST["UF_DATE"];
		}

		if (isset($_REQUEST["NAME"]))
			$arFields["NAME"] = $_REQUEST["NAME"];
		if (isset($_REQUEST["DESCRIPTION"]))
			$arFields["DESCRIPTION"] = $_REQUEST["DESCRIPTION"];
		if (isset($_REQUEST["ACTIVE"]))
			$arFields["ACTIVE"] = $_REQUEST["ACTIVE"];

		if ($_REQUEST["DROP_PASSWORD"] == "Y" || ($_REQUEST["USE_PASSWORD"] == "Y" && empty($_REQUEST["PASSWORD"])))
		{
			$arFields["UF_PASSWORD"] = "";
			$GLOBALS["UF_PASSWORD"] = "";
			$_REQUEST["DROP_PASSWORD"] = "Y";
		}
		elseif ($_REQUEST["USE_PASSWORD"] == "Y")
		{
			$arFields["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
			$GLOBALS["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
		}
		else
		{
			$arFields["UF_PASSWORD"] = $arResult["SECTION"]["~PASSWORD"]["VALUE"];
			$GLOBALS["UF_PASSWORD"] = $arResult["SECTION"]["~PASSWORD"]["VALUE"];
		}

		foreach ($_REQUEST as $key => $val)
		{
			if (substr($key, 0, 3) == "UF_")
			{
				$GLOBALS[$key] = $val;
			}
		}

		$bs = new CIBlockSection;
		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
		if ($bs->Update($arResult["SECTION"]["ID"], $arFields))
		{
			$rsSection = CIBlockSection::GetList(
				array(),
				array("ID" => $arResult["SECTION"]["ID"], "IBLOCK_ID" => $arParams["IBLOCK_ID"]),
				false,
				array("UF_DATE", "UF_PASSWORD"));
			$arResultSection = $rsSection->GetNext();
			$arResultFields = Array(
				"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
				"DATE"=>PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResultSection["UF_DATE"], CSite::GetDateFormat())),
				"PASSWORD" => $arResultSection["UF_PASSWORD"],
				"NAME"=>$arResultSection["NAME"],
				"DESCRIPTION"=>$arResultSection["DESCRIPTION"],
				"ID" => $arResult["SECTION"]["ID"],
				"error" => "");
			$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
		}
		elseif ($bs->LAST_ERROR)
		{
			$strWarning .= $bs->LAST_ERROR;
			$bVarsFromForm = true;
		}
		else
		{
			$err = $GLOBALS['APPLICATION']->GetException();
			if ($err) { $strWarning .= $err->GetString(); }
			$bVarsFromForm = true;
		}
	}
	elseif ($arParams["ACTION"] == "NEW")
	{
		$arFields = Array(
			"ACTIVE" => "Y",
			"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
			"DATE"=>$_REQUEST["UF_DATE"],
			"UF_DATE"=>$_REQUEST["UF_DATE"],
			"NAME"=>$_REQUEST["NAME"],
			"DESCRIPTION"=>$_REQUEST["DESCRIPTION"]);
		if (isset($_REQUEST["ACTIVE"]))
			$arFields["ACTIVE"] = $_REQUEST["ACTIVE"];

		if ($arParams["BEHAVIOUR"] == "USER")
		{
			if ($_REQUEST["IBLOCK_SECTION_ID"] > 0)
			{
				$db_res = CIBlockSection::GetByID($_REQUEST["IBLOCK_SECTION_ID"]);
				if ($db_res && $res = $db_res->Fetch())
				{
					if ($res["LEFT_MARGIN"] > $arResult["GALLERY"]["LEFT_MARGIN"] &&
						$res["RIGHT_MARGIN"] < $arResult["GALLERY"]["RIGHT_MARGIN"])
					$arFields["IBLOCK_SECTION_ID"] = $_REQUEST["IBLOCK_SECTION_ID"];
				}
			}
			if (empty($arFields["IBLOCK_SECTION_ID"]))
			{
				$arFields["IBLOCK_SECTION_ID"] = $arResult["GALLERY"]["ID"];
			}
		}
		elseif (intVal($_REQUEST["IBLOCK_SECTION_ID"]) > 0)
		{
			$arFields["IBLOCK_SECTION_ID"] = $_REQUEST["IBLOCK_SECTION_ID"];
		}

		if (!empty($_REQUEST["PASSWORD"]))
		{
			$arFields["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
			$GLOBALS["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
		}

		$bs = new CIBlockSection();
		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
		$ID = $bs->Add($arFields);
		if ($ID > 0)
		{
			$rsSection = CIBlockSection::GetList(Array(), array("ID" => $ID), false);
			$arResultSection = $rsSection->GetNext();
			$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $ID));
			$arResultFields = Array(
				"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
				"DATE"=>PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($_REQUEST["UF_DATE"], CSite::GetDateFormat())),
				"NAME"=>$arResultSection["NAME"],
				"DESCRIPTION"=>$arResultSection["DESCRIPTION"],
				"PASSWORD" => $arResultSection["UF_PASSWORD"],
				"ID" => $ID,
				"error" => "",
				"url" => $arResult["URL"]);
		}
		elseif ($bs->LAST_ERROR)
		{
			$strWarning .= $bs->LAST_ERROR;
			$bVarsFromForm = true;
		}
		else
		{
			$err = $GLOBALS['APPLICATION']->GetException();
			if ($err)
				$strWarning .= $err->GetString();
			$bVarsFromForm = true;
		}
	}
	elseif ($arParams["ACTION"] == "DROP")
	{
		@set_time_limit(1000);

		if (CIBlockSection::Delete($arResult["SECTION"]["ID"]))
		{
			// /Must Be deleted
			if ($arParams["BEHAVIOUR"] == "USER" && intVal($arResult["SECTION"]["IBLOCK_SECTION_ID"]) == intVal($arResult["GALLERY"]["ID"]))
				$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"]));
			elseif (intVal($arResult["SECTION"]["IBLOCK_SECTION_ID"]) > 0)
				$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["IBLOCK_SECTION_ID"]));
			else
				$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~INDEX_URL"],
				array());
			$arResultFields = Array(
				"ID" => $arResult["SECTION"]["ID"],
				"error" => "",
				"url" => $arResult["URL"]);
		}
		elseif ($e = $APPLICATION->GetException())
		{
			$strWarning .= $e->GetString();
		}
		else
		{
			$strWarning .= GetMessage("IBSEC_A_DELERR_REFERERS");
		}
	}

	if (!$bVarsFromForm)
	{
		CIBlockSection::ReSort($arParams["IBLOCK_ID"]);
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
			"photogallery.user"));

		if ($arParams["AJAX_CALL"] == "Y")
		{
			$APPLICATION->RestartBuffer();
			?><?=CUtil::PhpToJSObject($arResultFields);?><?
			die();
		}
		else
		{
			LocalRedirect($arResult["URL"]);
		}
	}
	$arResult["ERROR_MESSAGE"] = $strWarning;
}
/********************************************************************
				/Actions
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["bVarsFromForm"] = false;
if ($arParams["ACTION"] != "NEW")
{
	$arResult["FORM"]["ACTIVE"] = $arResult["SECTION"]["ACTIVE"];
	$arResult["FORM"]["NAME"] = htmlspecialcharsEx($arResult["SECTION"]["~NAME"]);
	$arResult["FORM"]["DESCRIPTION"] = htmlspecialcharsEx($arResult["SECTION"]["~DESCRIPTION"]);
	$arResult["FORM"]["~DATE"] = $arResult["SECTION"]["~DATE"];
	$arResult["FORM"]["~PASSWORD"] = $arResult["SECTION"]["~PASSWORD"];
}
else
{
	$arResult["FORM"]["ACTIVE"] = "";
	$arResult["FORM"]["NAME"] = "";
	$arResult["FORM"]["DESCRIPTION"] = "";
	$arResult["FORM"]["IBLOCK_SECTION_ID"] = ($arParams["SECTION_ID"] > 0 && $arParams["SECTION_ID"] != $arResult["GALLERY"]["ID"] ? $arParams["SECTION_ID"] : 0);
	$arResult["FORM"]["~DATE"] = $arResult["SECTION"]["~DATE"];
	$arResult["FORM"]["~DATE"]["VALUE"] = GetTime(time());
	$arResult["FORM"]["~PASSWORD"] = $arResult["SECTION"]["~PASSWORD"];
	$arResult["FORM"]["~PASSWORD"]["VALUE"] = "";
}

if ($bVarsFromForm)
{
	$arResult["bVarsFromForm"] = true;
	$arResult["FORM"]["ACTIVE"] = ($_REQUEST["ACTIVE"] == "Y" ? "Y" : "N");
	$arResult["FORM"]["NAME"] = htmlSpecialChars($_REQUEST["NAME"]);
	$arResult["FORM"]["DESCRIPTION"] = htmlSpecialChars($_REQUEST["DESCRIPTION"]);
	$arResult["FORM"]["DATE"] = $arResult["SECTION"]["~DATE"];
	$arResult["FORM"]["DATE"]["VALUE"] =  htmlSpecialChars($_REQUEST["UF_DATE"]);
}
if (intVal($arResult["SECTION"]["ID"]) > 0)
{
	$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["SECTION_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
}
elseif ($arParams["BEHAVIOUR"] == "USER")
{
	$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["GALLERY_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"]));
}
else
{
	$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["INDEX_URL"], array());
}

$arResult["SECTION"] = (is_array($arResult["SECTION"]) ? $arResult["SECTION"] : array());
$arResult["SECTION"]["~EDIT_ICON_LINK"] = CComponentEngine::MakePathFromTemplate(
	$arParams["~SECTION_EDIT_ICON_URL"],
	array(
		"USER_ALIAS" => $arParams["USER_ALIAS"],
		"SECTION_ID" => $arParams["SECTION_ID"],
		"ACTION" => "edit"
	));

$arResult["SECTION"]["~DROP_LINK"] = CComponentEngine::MakePathFromTemplate(
	//$arParams["~SECTION_EDIT_URL"],
	$arParams["~SECTION_DROP_URL"],
	array(
		"USER_ALIAS" => $arParams["USER_ALIAS"],
		"SECTION_ID" => $arParams["SECTION_ID"],
		"ACTION" => "drop"
	)).(strpos($arParams["~SECTION_EDIT_URL"], "?") === false ? "?" : "&").bitrix_sessid_get()."&edit=Y";
$arResult["SECTION"]["EDIT_ICON_LINK"] = htmlspecialchars($arResult["SECTION"]["~EDIT_ICON_LINK"]);
$arResult["SECTION"]["DROP_LINK"] = htmlspecialchars($arResult["SECTION"]["~DROP_LINK"]);

/********************************************************************
				/Data
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));

$this->IncludeComponentTemplate();

/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle($arParams["ACTION"] == "NEW" ? GetMessage("IBLOCK_NEW") : $arResult["SECTION"]["~NAME"].GetMessage("IBLOCK_EDIT_TITLE"));
$arResult["SECTION"]["PATH"] = (is_array($arResult["SECTION"]["PATH"]) ? $arResult["SECTION"]["PATH"] : array());
/************** Chain Items ****************************************/
if ($arParams["SET_NAV_CHAIN"] != "N")
{
	$bFound = ($arParams["BEHAVIOUR"] != "USER");
	foreach ($arResult["SECTION"]["PATH"] as $arPath)
	{
		if (!$bFound):
			$bFound = ($arResult["GALLERY"]["ID"] == $arPath["ID"]);
			continue;
		endif;
		$APPLICATION->AddChainItem($arPath["NAME"],
			CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"])));
	}
	$APPLICATION->AddChainItem($arParams["ACTION"] == "NEW" ? GetMessage("IBLOCK_NEW") : GetMessage("IBLOCK_EDIT"));
}
/************** Admin panel ****************************************/
// if ($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized() && CModule::IncludeModule("iblock"))
	// CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
/********************************************************************
				/Standart
********************************************************************/
?>