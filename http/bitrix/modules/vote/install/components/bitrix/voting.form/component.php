<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();
if (!IsModuleInstalled("vote")): 
	ShowError(GetMessage("VOTE_MODULE_IS_NOT_INSTALLED"));
	return;
elseif (intVal($arParams["VOTE_ID"]) <= 0):
	ShowError(GetMessage("VOTE_EMPTY"));
	return;
endif;

require_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/functions.php");
/********************************************************************
				Input params
********************************************************************/
/************** BASE ***********************************************/
	$arParams["VOTE_ID"] = intVal($arParams["VOTE_ID"]);
/************** URL ************************************************/
	$URL_NAME_DEFAULT = array(
			"vote_result" => "PAGE_NAME=vote_result&VOTE_ID=#VOTE_ID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE):
		if (strLen(trim($arParams[strToUpper($URL)."_TEMPLATE"])) <= 0)
			$arParams[strToUpper($URL)."_TEMPLATE"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_TEMPLATE"] = $arParams[strToUpper($URL)."_TEMPLATE"];
		$arParams[strToUpper($URL)."_TEMPLATE"] = htmlspecialchars($arParams["~".strToUpper($URL)."_TEMPLATE"]);
	endforeach;
/************** CACHE **********************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["ADDITIONAL_CACHE_ID"] = (isset($arParams["ADDITIONAL_CACHE_ID"]) && strlen($arParams["ADDITIONAL_CACHE_ID"]) > 0 ?
		$arParams["ADDITIONAL_CACHE_ID"] : $USER->GetGroups());
/********************************************************************
				/Input params
********************************************************************/
global $VOTING_ID;
if (($GLOBALS["VOTING_OK"] == "Y")  && ($VOTING_ID == $arParams["VOTE_ID"]))
{
	$strNavQueryString = DeleteParam(array("VOTE_ID", "VOTING_OK", "VOTE_SUCCESSFULL"));
	$strNavQueryString = ($strNavQueryString <> "" ? "&".$strNavQueryString : $strNavQueryString);
    $delimiter = (strpos($arParams["VOTE_RESULT_TEMPLATE"], "?") === false)?"?":"&";
    if (strpos($arParams["VOTE_RESULT_TEMPLATE"], "#VOTE_ID#") === false)
    {
        $arParams["VOTE_RESULT_TEMPLATE"] .= $delimiter."VOTE_ID=".$_REQUEST['VOTE_ID'];
        $url = CComponentEngine::MakePathFromTemplate(
            $arParams["VOTE_RESULT_TEMPLATE"]."&VOTE_SUCCESSFULL=Y".$strNavQueryString);
    } else {
        $delimiter = (strpos($arParams["VOTE_RESULT_TEMPLATE"], "?") === false)?"?":"&";
        $url = CComponentEngine::MakePathFromTemplate(
            $arParams["VOTE_RESULT_TEMPLATE"].$delimiter."VOTE_SUCCESSFULL=Y".$strNavQueryString,
            array('VOTE_ID' => $arParams['VOTE_ID']));
    }
    LocalRedirect($url);
}
/********************************************************************
				Default values
********************************************************************/
$arResult["OK_MESSAGE"] = "";
$arResult["ERROR_MESSAGE"] = "";
$arResult["CHANNEL"] = array();
$arResult["VOTE"] = array();
$arResult["QUESTIONS"] = array();
$arResult["GROUP_ANSWERS"] = array();
$arResult["~CURRENT_PAGE"] = $APPLICATION->GetCurPageParam("", array("VOTE_ID","VOTING_OK","VOTE_SUCCESSFULL"));
$arResult["CURRENT_PAGE"] = htmlspecialchars($arResult["~CURRENT_PAGE"]);
$cache = new CPHPCache;
$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName);

$arError = array(); $arNote = array();
if (($GLOBALS["VOTING_OK"] == "Y" || $_REQUEST["VOTE_SUCCESSFULL"] == "Y") && ($_REQUEST['VOTE_ID'] == $arParams['VOTE_ID'])) 
	$arNote[] = array(
		"id" => "ok", 
		"text" => GetMessage("VOTE_OK"));

if (($GLOBALS["USER_ALREADY_VOTE"] == "Y") && ($_REQUEST['VOTE_ID'] == $arParams["VOTE_ID"]))
	$arError[] = array(
		"id" => "already vote", 
		"text" => GetMessage("VOTE_ALREADY_VOTE"));
if (($GLOBALS["VOTING_LAMP"] == "red") && ($_REQUEST['VOTE_ID'] == $arParams["VOTE_ID"]))
	$arError[] = array(
		"id" => "red lamp", 
		"text" => GetMessage("VOTE_RED_LAMP"));
if (($GLOBALS["NO_CAPTCHA"] == "Y") && ($_REQUEST['VOTE_ID'] == $arParams["VOTE_ID"]))
	$arError[] = array(
		"id" => "no captcha", 
		"text" => GetMessage("VOTE_NO_CAPTCHA"));
if (($GLOBALS["BAD_CAPTCHA"] == "Y") && ($_REQUEST['VOTE_ID'] == $arParams["VOTE_ID"]))
	$arError[] = array(
		"id" => "bad captcha", 
        "text" => GetMessage("VOTE_BAD_CAPTCHA"));
if ((($GLOBALS["VOTE_REQUIRED_MISSING"]) == "Y")  && ($_REQUEST['VOTE_ID'] == $arParams["VOTE_ID"]))
{
	$arError[] = array(
		"id" => "required question is not answered", 
        "text" => GetMessage("VOTE_REQUIRED_MISSING"));
}

if (($GLOBALS["USER_VOTE_EMPTY"] == "Y") && (empty($arError)) && ($VOTING_ID == $arParams["VOTE_ID"]))
    $arError[] = array(
            "id" => "user vote empty", 
            "text" => GetMessage("USER_VOTE_EMPTY"));
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$obCache = new CPHPCache;
$cache_id = "vote_form_".serialize($arParams); //."_".$USER->GetGroups();
$cache_path = $cache_path_main;

CModule::IncludeModule("vote");
if ($obCache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$arVars = $obCache->GetVars();
	$arResult = $arVars["arResult"];
}
else
{
	$arParams["VOTE_ID"] = GetVoteDataByID($arParams["VOTE_ID"], $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, "N");
	if (intval($arParams["VOTE_ID"]) <= 0)
	{
		ShowError(GetMessage("VOTE_NOT_FOUND"));
		return;
	}
	elseif (CVoteChannel::GetGroupPermission($arChannel["ID"]) < 2)
	{
		$arError[] = array(
			"id" => "access denied", 
			"text" => GetMessage("VOTE_ACCESS_DENIED"));
	}
	else
	{
		$defaultWidth = "10";
		$defaultHeight = "5";
		$questionSize = count($arQuestions);
		for ($questionIndex = 0; $questionIndex < $questionSize; $questionIndex++)
		{
			$QuestionID = $arQuestions[$questionIndex]["ID"];
	
			if (!array_key_exists($QuestionID, $arAnswers))
			{
				unset($arQuestions[$questionIndex]);
				unset($arAnswers[$QuestionID]);
				continue;
			}
	
			$arQuestions[$questionIndex]["ANSWERS"] = Array();
	
			$foundDropdown = $foundMultiselect = false;
			
			foreach ($arAnswers[$QuestionID] as $arAnswer)
			{
				if ($arAnswer["FIELD_TYPE"] == 2)
				{
					if ($foundDropdown == false)
					{
						$arQuestions[$questionIndex]["ANSWERS"][] = $arAnswer + Array(
							"DROPDOWN" => _GetAnswerArray($QuestionID, $arAnswer["FIELD_TYPE"], $arAnswers),
							"MULTISELECT" => Array(),
						);
						$foundDropdown = true;
					}
				}
				elseif($arAnswer["FIELD_TYPE"] == 3)
				{
					if ($foundMultiselect == false)
					{
						$arQuestions[$questionIndex]["ANSWERS"][] = $arAnswer + Array(
							"MULTISELECT" => _GetAnswerArray($QuestionID, $arAnswer["FIELD_TYPE"], $arAnswers),
							"DROPDOWN" => Array(),
						);
						$foundMultiselect = true;
					}
				}
				else
				{
					if ($arAnswer["FIELD_TYPE"] == 4)
					{
						$arAnswer["FIELD_WIDTH"] = (intval($arAnswer["FIELD_WIDTH"]) > 0 ? intval($arAnswer["FIELD_WIDTH"]) : $defaultWidth);
					}
					elseif($arAnswer["FIELD_TYPE"] == 5)
					{
						$arAnswer["FIELD_WIDTH"] = (intval($arAnswer["FIELD_WIDTH"]) > 0 ? intval($arAnswer["FIELD_WIDTH"]) : $defaultWidth);
						$arAnswer["FIELD_HEIGHT"] = (intval($arAnswer["FIELD_HEIGHT"]) > 0 ? intval($arAnswer["FIELD_HEIGHT"]) : $defaultHeight);
					}
	
					$arQuestions[$questionIndex]["ANSWERS"][] = $arAnswer + Array("DROPDOWN" => Array(), "MULTISELECT" => Array());
				}
			}
			//Images
			$arQuestions[$questionIndex]["IMAGE"] = CFile::GetFileArray($arQuestions[$questionIndex]["IMAGE_ID"]);
		}
	
		//Vote Image
		$arVote["IMAGE"] = CFile::GetFileArray($arVote["IMAGE_ID"]);
		
		$arResult["CHANNEL"] = $arChannel;
		$arResult["VOTE"] = $arVote;
		$arResult["QUESTIONS"] = $arQuestions;
		$arResult["GROUP_ANSWERS"] = $arGroupAnswers;

        global $CACHE_MANAGER;
		$obCache->StartDataCache();
        $CACHE_MANAGER->StartTagCache($cache_path);
        $CACHE_MANAGER->RegisterTag("vote_form_channel_".$arChannel["ID"]);
        $CACHE_MANAGER->RegisterTag("vote_form_vote_".$arVote["ID"]);
        foreach($arQuestions as $question)
        {
            $CACHE_MANAGER->RegisterTag("vote_form_question_".$question["ID"]);
        }
        $CACHE_MANAGER->EndTagCache();
		$obCache->EndDataCache(array("arResult" => $arResult));
	}
}
if ($arResult["CHANNEL"]["USE_CAPTCHA"] == "Y") 
{
    include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
    $cpt = new CCaptcha();
    $captchaPass = COption::GetOptionString("main", "captcha_password", "");
    if (strlen($captchaPass) <= 0)
    {
        $captchaPass = randString(10);
        COption::SetOptionString("main", "captcha_password", $captchaPass);
    }
    $cpt->SetCodeCrypt($captchaPass);
    $arResult["CAPTCHA_CODE"] = htmlspecialchars($cpt->GetCodeCrypt());            
}

if (!empty($arNote)):
	$e = new CAdminException($arNote);
	$arResult["OK_MESSAGE"] = $e->GetString();
endif;
if (!empty($arError)):
	$e = new CAdminException($arError);
	$arResult["ERROR_MESSAGE"] = $e->GetString();
endif;
/********************************************************************
				/Data
********************************************************************/
unset($arQuestions);
unset($arChannel);
unset($arVote);
unset($arAnswers);
unset($arDropDown);
unset($arMultiSelect);

$this->IncludeComponentTemplate();
?>
