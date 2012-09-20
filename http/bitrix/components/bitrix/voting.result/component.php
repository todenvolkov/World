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
global $arrSaveColor;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");
/********************************************************************
				Input params
********************************************************************/
/************** BASE ***********************************************/
	$arParams["VOTE_ID"] = intVal($arParams["VOTE_ID"]);
/************** URL ************************************************/
	$URL_NAME_DEFAULT = array(
			"vote_form" => "PAGE_NAME=vote_new&VOTE_ID=#VOTE_ID#",
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
		$arParams["ADDITIONAL_CACHE_ID"] : $USER->GetGroups() );
/********************************************************************
				/Input params
********************************************************************/
$arParamsCache = array(
	$arParams["ADDITIONAL_CACHE_ID"], $arParams["CACHE_TYPE"]);

if ($GLOBALS["VOTING_OK"] =="Y" || $GLOBALS["USER_ALREADY_VOTE"] =="Y" || (isset($_REQUEST['VOTE_SUCCESSFULL']) && ($_REQUEST['VOTE_SUCCESSFULL'] == 'Y')))
    $this->ClearResultCache($arParamsCache);
elseif (!$this->StartResultCache(false, $arParamsCache))
    return;
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
$arParams["NEED_SORT"] = ($arParams["NEED_SORT"] == "N" ? "N" : "Y");
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
CModule::IncludeModule("vote");
$arChannel = array(); $arVote = array(); $arQuestions = array(); $arAnswers = array(); $arDropDown = array(); $arMultiSelect = array(); $$arGroupAnswers = array();
$arParams["VOTE_ID"] = GetVoteDataByID($arParams["VOTE_ID"], $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, $arParams['VOTE_ALL_RESULTS']);
if (intVal($arParams["VOTE_ID"]) <= 0):
	$this->AbortResultCache();
	ShowError(GetMessage("VOTE_NOT_FOUND"));
	return;
endif;


$arError = array(); $arNote = array();
if (($GLOBALS["VOTING_OK"] == "Y" || $_REQUEST["VOTE_SUCCESSFULL"] == "Y") && ($_REQUEST['VOTE_ID'] == $arParams['VOTE_ID'])) 
	$arNote[] = array(
		"id" => "ok", 
		"text" => GetMessage("VOTE_OK"));
if (($GLOBALS["USER_ALREADY_VOTE"] == "Y") && ($_REQUEST['VOTE_ID'] == $arParams['VOTE_ID']))
	$arError[] = array(
		"id" => "already vote", 
		"text" => GetMessage("VOTE_ALREADY_VOTE"));
if (($GLOBALS["VOTING_LAMP"] == "red") && ($_REQUEST['VOTE_ID'] == $arParams['VOTE_ID']))
	$arError[] = array(
		"id" => "red lamp", 
		"text" => GetMessage("VOTE_RED_LAMP"));

$VOTE_ID = intval($arParams['VOTE_ID']);

if (CVoteChannel::GetGroupPermission($arChannel["ID"]) >= 1)
{
	$counter = intval($arVote['COUNTER']);
	$counter = ($counter <= 0 ? 1 : $counter);
	
    if (($arParams['VOTE_ALL_RESULTS'] == 'Y') && !empty($arGroupAnswers))
    {
        foreach ($arGroupAnswers as $answerId => $answerOptions)
        {
            $userAnswerSum = 0;
            foreach ($answerOptions as $answerOption)
            {
                $userAnswerSum += $answerOption['COUNTER'];
            }

            $ucolor = "\n";
            foreach ($answerOptions as $answerIndex => $answerOption)
            {
                $ucolor = GetNextRGB($ucolor, count($answerOptions));
                $arGroupAnswers[$answerId][$answerIndex]['COLOR'] = $ucolor;
                $arGroupAnswers[$answerId][$answerIndex]['PERCENT'] = ($userAnswerSum > 0 ? round($answerOption['COUNTER']*100/$userAnswerSum) : 0);
            }
        }
    }

	for ($questionIndex = 0, $questionSize = count($arQuestions); $questionIndex < $questionSize; $questionIndex++)
	{
		$questionID = $arQuestions[$questionIndex]["ID"];
		
		//Include in the result chart
		if ($arQuestions[$questionIndex]["DIAGRAM"] == "N")
		{
			unset($arAnswers[$questionID]);
			unset($arQuestions[$questionIndex]);
			continue;
		}
		elseif (!array_key_exists($questionID, $arAnswers))
		{
			unset($arQuestions[$questionIndex]);
			continue;
		}

		//Calculating the sum and maximum value
		$counterSum = $counterMax = 0;
		foreach ($arAnswers[$questionID] as $arAnswer)
		{
			$counterSum += $arAnswer["COUNTER"];
			$counterMax = max(intVal($arAnswer["COUNTER"]), $counterMax);
		}
		if ($arParams["NEED_SORT"] != "N"):
            //Sorting answers
            uasort($arAnswers[$questionID], "_vote_answer_sort");
		endif;
		$color = "";
		foreach ($arAnswers[$questionID] as $answerIndex => $arAnswer)
		{
			$arAnswers[$questionID][$answerIndex]["PERCENT"] = ($counterSum > 0 ? number_format(($arAnswer["COUNTER"]*100/$counter),2,',','') : 0);
            if (($arAnswer['FIELD_TYPE'] == 1) or ($arAnswer['FIELD_TYPE'] == 3)) // checkbox ,  multiselect 
            {
                $arAnswers[$questionID][$answerIndex]["BAR_PERCENT"] = round($arAnswers[$questionID][$answerIndex]["PERCENT"]);
            } else {
                $arAnswers[$questionID][$answerIndex]["BAR_PERCENT"] = ($counterMax > 0 ? round($arAnswer["COUNTER"]*100/$counterMax) : 0);
            }

			if (strlen($arAnswer["COLOR"]) <= 0)
			{
				$color = GetNextRGB($color, count($arAnswers[$questionID]));
				$arAnswers[$questionID][$answerIndex]["COLOR"] = $color;
			}
			else
			{
				$arAnswers[$questionID][$answerIndex]["COLOR"] = TrimEx($arAnswer["COLOR"], "#");
			}
		}
		$arQuestions[$questionIndex]["COUNTER_SUM"] = $counterSum;
		$arQuestions[$questionIndex]["COUNTER_MAX"] = $counterMax;

		//Images
		$arQuestions[$questionIndex]["IMAGE"] = CFile::GetFileArray($arQuestions[$questionIndex]["IMAGE_ID"]);

		//Diagram type
		if (strlen($arParams["QUESTION_DIAGRAM_".$questionID])>0 && $arParams["QUESTION_DIAGRAM_".$questionID]!="-")
			$arQuestions[$questionIndex]["DIAGRAM_TYPE"] = trim($arParams["QUESTION_DIAGRAM_".$questionID]);

		//Answers
		$arQuestions[$questionIndex]["ANSWERS"] = $arAnswers[$questionID];
		unset($arAnswers[$questionID]);
	}

	//Vote Image
	$arVote["IMAGE"] = CFile::GetFileArray($arVote["IMAGE_ID"]);
	
	$arResult["CHANNEL"] = $arChannel;
	$arResult["VOTE"] = $arVote;
	$arResult["QUESTIONS"] = $arQuestions;
	$arResult["GROUP_ANSWERS"] = $arGroupAnswers;
}
else
{
	$arError[] = array(
		"id" => "access denied", 
		"text" => GetMessage("VOTE_ACCESS_DENIED"));
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
if ($this->__templateName == "main_page.blue"):
	$this->__templateName = "main_page";
	$arParams["THEME"] = "blue";
elseif ($this->__templateName == "main_page.green"):
	$this->__templateName = "main_page";
	$arParams["THEME"] = "green";
endif;
$this->IncludeComponentTemplate();
?>
