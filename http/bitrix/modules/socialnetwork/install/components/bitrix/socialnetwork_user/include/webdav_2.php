<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/../lang/".LANGUAGE_ID."/include/webdav_2.php")));
__IncludeLang($file);

if ($componentPage == "user_files_element_upload" || $componentPage == "group_files_element_upload")
{
    $obDavEventHandler = new CSocNetWebDavEvent;
    $obDavEventHandler->SetPaths($arResult);
    AddEventHandler("webdav", "OnFilePut", array($obDavEventHandler, "SocNetFilesPut"));
}

class CSocNetWebDavEvent
{
    var $arPath;

    function SetPaths($arResult)
    {
        $this->arPath['PATH_TO_USER'] = $arResult["~PATH_TO_USER"];
        $this->arPath['PATH_TO_GROUP_FILES_ELEMENT'] = $arResult["~PATH_TO_GROUP_FILES_ELEMENT"];
        $this->arPath['PATH_TO_USER_FILES_ELEMENT'] = $arResult["~PATH_TO_USER_FILES_ELEMENT"];
    }

    function SocNetFilesPut($arParams, $file)
    {
        global $USER;

        $bIsGroup = isset($arParams['ATTRIBUTES']['group_id']);
        if ($bIsGroup)
            CSocNetGroup::SetLastActivity(intval($arParams['attributes']['group_id']), false);

        $sAuthorName = GetMessage("SONET_LOG_GUEST"); 
        $sAuthorUrl = "";
        if ($USER->IsAuthorized())
        {
            $sAuthorName = trim($USER->GetFullName());
            $sAuthorName = (empty($sAuthorName) ? $USER->GetLogin() : $sAuthorName);
            $sAuthorUrl = CComponentEngine::MakePathFromTemplate($this->arPath["PATH_TO_USER"], 
                array("USER_ID" => $USER->GetID()));
        }

        if ($file["status"] == "success")
        {
            $url = ($bIsGroup ? $this->arPath["PATH_TO_GROUP_FILES_ELEMENT"] : $this->arPath["PATH_TO_USER_FILES_ELEMENT"]);
            $arFields = array(
                "ENTITY_TYPE" => ($bIsGroup ? SONET_ENTITY_GROUP : SONET_ENTITY_USER),
                "ENTITY_ID" => ($bIsGroup ? intval($arParams['ATTRIBUTES']['group_id']) : intval($arParams['ATTRIBUTES']['user_id'])),
                "EVENT_ID" => "files",
                "=LOG_DATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
                "TITLE_TEMPLATE" => str_replace("#AUTHOR_NAME#", $sAuthorName, GetMessage("SONET_FILES_LOG")),
                "TITLE" => $file["title"],
                "URL" => CComponentEngine::MakePathFromTemplate($url, 
                array("SECTION_ID" => $arParams["section_id"], "ELEMENT_ID" => $file["id"])),
                "MODULE_ID" => false,
                "CALLBACK_FUNC" => false);

            if ($USER->IsAuthorized())
                $arFields["USER_ID"] = $USER->GetID();
            $serverName = (defined("SITE_SERVER_NAME") && strLen(SITE_SERVER_NAME) > 0) ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name");
            $arFields["MESSAGE"] = str_replace(array("#AUTHOR_NAME#", "#AUTHOR_URL#"), array(htmlspecialcharsEx($sAuthorName), $sAuthorUrl), 
            ($USER->IsAuthorized() ? GetMessage("SONET_LOG_TEMPLATE_AUTHOR") : GetMessage("SONET_LOG_TEMPLATE_GUEST")).""); 
            $arFields["TEXT_MESSAGE"] = str_replace(array("#URL#", "#TITLE#", "#AUTHOR_NAME#", "#AUTHOR_URL#"),
                array("http://".$serverName.$arFields["URL"],  $arFields["TITLE"], $sAuthorName, "http://".$serverName.$sAuthorUrl), 
                GetMessage("SONET_FILES_LOG_TEXT").($USER->IsAuthorized() ? GetMessage("SONET_LOG_TEMPLATE_AUTHOR_MAIL") : 
                GetMessage("SONET_LOG_TEMPLATE_GUEST"))); 

            $logID = CSocNetLog::Add($arFields, false);
            if (intval($logID) > 0)
                CSocNetLog::Update($logID, array("TMP_ID" => $logID));

            CSocNetLog::SendEvent($logID, "SONET_NEW_EVENT", $logID);
        }
    }
}
?>
