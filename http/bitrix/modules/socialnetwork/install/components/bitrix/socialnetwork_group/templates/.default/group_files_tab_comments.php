<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
$arCurFileInfo = pathinfo(__FILE__);
$langfile = trim(preg_replace("'[\\\\/]+'", "/", ($arCurFileInfo['dirname']."/lang/".LANGUAGE_ID."/".$arCurFileInfo['basename'])));
__IncludeLang($langfile);
$sTabName =  "tab_comments";
$arSelectParams = $arParams;
$arSelectParams += array("SHOW_VERSION" => "Y");
if ($arParams["USE_COMMENTS"] == "Y")
{
    $arSelectParams["COLUMNS"][] = "PROPERTY_FORUM_MESSAGE_CNT";
    $arSelectParams["COLUMNS"][] = "PROPERTY_FORUM_TOPIC_ID";
}
if(is_array($arInfo) && $arInfo["ELEMENT_ID"] && $arParams["FILES_USE_COMMENTS"]=="Y" && IsModuleInstalled("forum"))
{
    $bShowHide = (false /* (intval($arInfo["ELEMENT"]["PROPERTIES"]["FORUM_TOPIC_ID"]["VALUE"]) <= 0 /*&& 	
        ($arParams['WORKFLOW'] == "bizproc" && $arInfo["ELEMENT"]["BP_PUBLISHED"] != "Y" || 
       $arParams['WORKFLOW'] == "workflow" && (!(intval($arInfo["ELEMENT"]["WF_STATUS_ID"]) == 1 && intval($arInfo["ELEMENT"]["WF_PARENT_ELEMENT_ID"]) <= 0))) */);
    if (!$bShowHide)
    {
        if ($arInfo["ELEMENT"]["PROPERTY_FORUM_MESSAGE_CNT_VALUE"] == null)
            $arInfo["ELEMENT"]["PROPERTY_FORUM_MESSAGE_CNT_VALUE"] = 0;

        $sCurrentTab = (isset($_GET[$arParams["FORM_ID"].'_active_tab']) ? $_GET[$arParams["FORM_ID"].'_active_tab'] : '');
        $_GET[$arParams["FORM_ID"].'_active_tab'] = $sTabName;

        ob_start();
    ?><a name="reviews"></a><?
        $APPLICATION->IncludeComponent(
        "bitrix:forum.topic.reviews",
        "webdav_comments",
        Array(
            "IBLOCK_TYPE"	=>	$arParams["FILES_GROUP_IBLOCK_TYPE"],
            "IBLOCK_ID"	=>	$arParams["FILES_GROUP_IBLOCK_ID"],
            "FORUM_ID" => $arParams["FILES_FORUM_ID"],
            "ELEMENT_ID" => $arInfo["ELEMENT_ID"],
            "ENABLE_HIDDEN" => "Y",
            
            "URL_TEMPLATES_READ" => "",
            "URL_TEMPLATES_PROFILE_VIEW" => str_replace("#USER_ID#", "#UID#", $arResult["~PATH_TO_USER"]),
            "URL_TEMPLATES_DETAIL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT"],

            "POST_FIRST_MESSAGE" => "Y", 
            "POST_FIRST_MESSAGE_TEMPLATE" => GetMessage("WD_TEMPLATE_MESSAGE"), 
            "SUBSCRIBE_AUTHOR_ELEMENT" => "Y", 
            "IMAGE_SIZE" => false, 
            "MESSAGES_PER_PAGE" => 20,
            "DATE_TIME_FORMAT" => false, 
            "USE_CAPTCHA" => $arParams["FILES_USE_CAPTCHA"],
            "PREORDER" => $arParams["FILES_PREORDER"],
            "PAGE_NAVIGATION_TEMPLATE" => false, 
            "DISPLAY_PANEL" => "N", 

            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
            "CACHE_TIME" => $arParams["CACHE_TIME"],

            "PATH_TO_SMILE" => $arParams["FILES_PATH_TO_SMILE"],
            "SHOW_LINK_TO_FORUM" => "N",
        ),
        $component,
        array("HIDE_ICONS" => "Y")
    );



    $this->__component->arResult['TABS'][] = 
        array( "id" => $sTabName, 
               "name" => GetMessage("WD_COMMENTS_NAME" , array("#NUM#" => $arInfo["ELEMENT"]["PROPERTY_FORUM_MESSAGE_CNT_VALUE"])), 
               "title" => GetMessage("WD_COMMENTS_TITLE"), 
               "fields" => array(
                   array(  "id" => "WD_ELEMENT_COMMENTS", 
                            "name" => GetMessage("WD_COMMENTS_NAME"), 
                            "colspan" => true,
                            "type" => "custom", 
                            "value" => ob_get_clean()
                        )
                ) 
        );

    unset($_GET[$arParams["FORM_ID"].'_active_tab']);
    if (!empty($sCurrentTab))
        $_GET[$arParams["FORM_ID"].'_active_tab'] = $sCurrentTab;
    }
}
?>
