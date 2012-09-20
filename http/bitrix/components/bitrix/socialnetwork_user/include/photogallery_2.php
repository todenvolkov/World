<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/../lang/".LANGUAGE_ID."/include/photogallery_2.php")));
__IncludeLang($file);
if (($componentPage == "user_photo_element_upload" || 
	$componentPage == "group_photo_element_upload") && $_REQUEST["save_upload"] == "Y" && 
	check_bitrix_sessid())
{
	$object = ($componentPage == "group_photo_element_upload" ? "group" : "user");
	if ($object == "group")
		CSocNetGroup::SetLastActivity($arResult["VARIABLES"]["group_id"], false);

	if (!empty($arParams["ANSWER_UPLOAD_PAGE"]) && !empty($_REQUEST["PackageGuid"]))
	{
		$files = $arParams["ANSWER_UPLOAD_PAGE"]["current_files"];
		$error = false;
		$arID = array();
		$sExternalId = "package_guid_".preg_replace("/[^a-z0-9]/is", "_", $_REQUEST["PackageGuid"]);
		foreach ($files as $file)
		{
			if ($file["status"] != "success")
			{
				$error = true;
				continue;
			}
			if (count($arID) < 3) 
				$arID[] = $file["id"];
		}
		$arIDAll = array();
		
		foreach ($arParams["ANSWER_UPLOAD_PAGE"]["files"] as $name => $file)
		{
			if ($file["status"] != "success")
				continue;
			$arIDAll[] = $name;
		}
		
		$sAuthorName = GetMessage("SONET_LOG_GUEST"); 
		$sAuthorUrl = "";
		if ($USER->IsAuthorized())
		{
			$sAuthorName = trim($USER->GetFullName());
			$sAuthorName = (empty($sAuthorName) ? $USER->GetLogin() : $sAuthorName);
			$sAuthorUrl = CComponentEngine::MakePathFromTemplate($arResult["~PATH_TO_USER"], 
				array("USER_ID" => $USER->GetID()));
		}
		
		$db_res = CSocNetLog::GetList(array(), array("EXTERNAL_ID" => $sExternalId));
		if ($db_res && $res = $db_res->Fetch())
		{
			$arFields = array(
				"TITLE" => str_replace(array("#AUTHOR_NAME#", "#COUNT#"), 
					array($sAuthorName, count($arIDAll)), GetMessage("SONET_PHOTO_LOG_2")),
				"=LOG_DATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"PARAMS" => "count=".count($arIDAll)
			);
			CSocNetLog::Update($res["ID"], $arFields);
		}
		else
		{
			$arFields = array(
				"ENTITY_TYPE" => ($object == "group" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER),
				"ENTITY_ID" => ($object == "group" ? $arResult["VARIABLES"]["group_id"] : $arResult["VARIABLES"]["user_id"]),
				"EVENT_ID" => "photo",
				"=LOG_DATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"TITLE_TEMPLATE" => str_replace("#AUTHOR_NAME#", $sAuthorName, GetMessage("SONET_PHOTO_LOG_1")),
				"TITLE" => str_replace("#COUNT#", count($arIDAll), GetMessage("SONET_PHOTO_LOG_2")),
				"MESSAGE" => "",
				"TEXT_MESSAGE" => "",
				"URL" => CComponentEngine::MakePathFromTemplate(
					($object == "group" ? $arResult["~PATH_TO_GROUP_PHOTO_SECTION"] : $arResult["~PATH_TO_USER_PHOTO_SECTION"]), 
					array("USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"], 
						"SECTION_ID" => $arParams["ANSWER_UPLOAD_PAGE"]["section_id"])),
				"MODULE_ID" => false,
				"CALLBACK_FUNC" => false,
				"EXTERNAL_ID" => $sExternalId,
				"PARAMS" => "count=".count($arIDAll)
			);

			if ($USER->IsAuthorized())
				$arFields["USER_ID"] = $USER->GetID();

			if (!empty($arID))
			{
				$res_pictures = array();
				$res_links = array();
				$db_res = CIBlockElement::GetList(array(), array("%ID" => $arID), false, false, 
					array("NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT", "TAGS", "ID", "IBLOCK_SECTION_ID"));
				if ($db_res && $res = $db_res->Fetch())
				{
					do 
					{
						$res["url"] = CComponentEngine::MakePathFromTemplate(
							($object == "group" ? $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT"] : $arResult["~PATH_TO_USER_PHOTO_ELEMENT"]), 
							array("USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"], 
								"SECTION_ID" => $res["IBLOCK_SECTION_ID"], "ELEMENT_ID" => $res["ID"]));
						$res["~PREVIEW_PICTURE"] = $res["PREVIEW_PICTURE"];
						$res["PREVIEW_PICTURE"] = CFile::GetFileArray($res["~PREVIEW_PICTURE"]);
						$res_pictures[] = CFile::ShowImage($res["~PREVIEW_PICTURE"], 70, 70, null, $res["url"]);
						$res_links[] = $res["NAME"];
					}
					while ($db_res && $res = $db_res->Fetch());
				}
				$arFields["MESSAGE"] = str_replace(
					array("#LINKS#", "#HREF#", "#AUTHOR_NAME#", "#AUTHOR_URL#"),
					array(implode(" ", $res_pictures), $arFields["URL"], htmlspecialcharsEx($sAuthorName), $sAuthorUrl), 
					GetMessage("SONET_PHOTO_LOG_TEXT").($USER->IsAuthorized() ? GetMessage("SONET_LOG_TEMPLATE_AUTHOR") : 
						GetMessage("SONET_LOG_TEMPLATE_GUEST")));

				$arFields["TEXT_MESSAGE"] = str_replace(
					array("#LINKS#", "#HREF#", "#AUTHOR_NAME#", "#AUTHOR_URL#"),
					array(implode(", ", $res_links), $arFields["URL"], $sAuthorName, "http://".SITE_SERVER_NAME.$sAuthorUrl), 
					GetMessage("SONET_PHOTO_LOG_MAIL_TEXT").($USER->IsAuthorized() ? GetMessage("SONET_LOG_TEMPLATE_AUTHOR_MAIL") : 
						GetMessage("SONET_LOG_TEMPLATE_GUEST")));

				$logID = CSocNetLog::Add($arFields, false);
				if (intval($logID) > 0)
					CSocNetLog::Update($logID, array("TMP_ID" => $logID));
					
				CSocNetLog::SendEvent($logID, "SONET_NEW_EVENT", $logID);					
			}
		}
		
		$result = $arParams["ANSWER_UPLOAD_PAGE"];
		unset($result["current_files"]);
		$error = ($error ? $error : !empty($result["fatal_errors"]));
		if ($_REQUEST["AJAX_CALL"] == "Y")
		{
			if ($_REQUEST["CONVERT"] == "Y")
			{
				include_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT."/componens/bitrix/photogallery.upload/functions.php"); 
				array_walk($result, '__Escape');
			}
			$APPLICATION->RestartBuffer();
			?><?=CUtil::PhpToJSObject($result);?><?
			die();
		}
		elseif (!$error)
		{
			LocalRedirect($arParams["ANSWER_UPLOAD_PAGE"]["url"]);
		}
	}
}
?>