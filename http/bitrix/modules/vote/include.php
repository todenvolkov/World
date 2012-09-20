<?
global $DB, $MESS, $APPLICATION;

if (!defined("VOTE_CACHE_TIME")) 
	define("VOTE_CACHE_TIME", 3600);
if (!defined("CACHED_b_vote_question")) 
	define("CACHED_b_vote_question", VOTE_CACHE_TIME);

define("VOTE_DEFAULT_DIAGRAM_TYPE", "histogram");

$GLOBALS["VOTE_CACHE"] = array();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin_tools.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/filter_tools.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/vote_tools.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/classes/".strtolower($DB->type)."/channel.php");
CModule::AddAutoloadClasses("vote", array(
	"CVoteAnswer" => "classes/".strtolower($DB->type)."/answer.php", 
	"CVoteEvent" => "classes/".strtolower($DB->type)."/event.php", 
	"CVoteQuestion" => "classes/".strtolower($DB->type)."/question.php", 
	"CVoteUser" => "classes/".strtolower($DB->type)."/user.php", 
	"CVote" => "classes/".strtolower($DB->type)."/vote.php"));

IncludeModuleLangFile(__FILE__);
function VoteVoteEditFromArray($CHANNEL_ID, $VOTE_ID = false, $arFields = array(), $params = array())
{
	$CHANNEL_ID = intVal($CHANNEL_ID);
	if ($CHANNEL_ID <= 0 || empty($arFields)):
		return false;
	elseif (empty($arFields["QUESTIONS"])):
		return false;
	elseif (CVote::UserGroupPermission($CHANNEL_ID) <= 0):
		return false;
	endif;
	$aMsg = array();
	$params = (is_array($params) ? $params : array());
	$params["UNIQUE_TYPE"] = (is_set($params, "UNIQUE_TYPE") ? intVal($params["UNIQUE_TYPE"]) : 4);
	$params["DELAY"] = (is_set($params, "DELAY") ? intVal($params["DELAY"]) : 10);
	$params["DELAY_TYPE"] = (is_set($params, "DELAY_TYPE") ? intVal($params["DELAY_TYPE"]) : "D");
	
	$arVote = array();
	$arQuestions = array();
	$arAnswers = array();
	
	$arFieldsQuestions = array();
	$arFieldsVote = array(
		"CHANNEL_ID" => $CHANNEL_ID, 
		"UNIQUE_TYPE" => $params["UNIQUE_TYPE"], 
		"DELAY" => $params["DELAY"], 
		"DESCRIPTION_TYPE" => $params["DELAY_TYPE"]);
	if (!empty($arFields["DATE_START"]))
		$arFieldsVote["DATE_START"] = $arFields["DATE_START"];
	if (!empty($arFields["DATE_END"]))
		$arFieldsVote["DATE_END"] = $arFields["DATE_END"];
	if (!empty($arFields["TITLE"]))
		$arFieldsVote["TITLE"] = $arFields["TITLE"];
	$arFieldsAnswers = array();
/************** Fatal errors ***************************************/
	if (!CVote::CheckFields("UPDATE", $arFieldsVote)):
		$e = $GLOBALS['APPLICATION']->GetException();
		$aMsg[] = array(
			"id" => "VOTE_ID", 
			"text" => $e->GetString());
	elseif (intval($VOTE_ID) > 0):
		$db_res = CVote::GetByID($VOTE_ID);
		if (!($db_res && $res = $db_res->Fetch())):
			$aMsg[] = array(
				"id" => "VOTE_ID", 
				"text" => GetMessage("VOTE_VOTE_NOT_FOUND", array("#ID#", $VOTE_ID)));
		elseif ($res["CHANNEL_ID"] != $CHANNEL_ID):
			$aMsg[] = array(
				"id" => "CHANNEL_ID", 
				"text" => GetMessage("VOTE_CHANNEL_ID_ERR"));
		else:
			$arVote = $res;
			$db_res = CVoteQuestion::GetList($arVote["ID"], $by = "s_id", $order = "asc", array(), $is_filtered);
			if ($db_res && $res = $db_res->Fetch()):
				do { $arQuestions[$res["ID"]] = $res; } while ($res = $db_res->Fetch());
			endif;
			$db_res = CVoteAnswer::GetListEx(array("ID" => "ASC"), array("VOTE_ID" => $arVote["ID"]));
			if ($db_res && $res = $db_res->Fetch()):
				do { $arAnswers[$res["ID"]] = $res; } while ($res = $db_res->Fetch());
			endif;
		endif;
	endif;
	if (!empty($aMsg)):
		$e = new CAdminException(array_reverse($aMsg));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	endif;
/************** Fatal errors/***************************************/
	
/************** Check Data *****************************************/
	foreach ($arFields["QUESTIONS"] as $key => $arQuestion)
	{
		$arQuestion["QUESTION"] = trim($arQuestion["QUESTION"]);
		if (empty($arQuestion["QUESTION"])):
			$aMsg[] = array(
				"id" => "QUESTION_".$key, 
				"text" => GetMessage("VOTE_QUESTION_EMPTY"));
		elseif ($arQuestion["ID"] > 0 && is_set($arQuestions, $arQuestion["ID"])):
			$arFieldsQuestions[$key] = array(
				"ID" => $arQuestion["ID"], 
				"QUESTION" => $arQuestion["QUESTION"], 
				"DEL" => ($arQuestion["DEL"] == "Y" ? "Y" : "N"));
		elseif ($arQuestion["DEL"] == "Y"):
		else:
			$arFieldsQuestions[$key] = array(
				"QUESTION" => $arQuestion["QUESTION"]);
		endif;
	}
	
	if (empty($aMsg))
	{
		$iQuestionsCount = count($arFieldsQuestions);
		foreach ($arFieldsQuestions as $key => $arQuestion)
		{
			if (!empty($aMsg)):
				break;
			elseif ($arQuestion["DEL"] == "Y"):
				$iQuestionsCount--;
				continue;
			endif;
			
			$arFields["QUESTIONS"][$key]["ANSWERS"] = (is_array($arFields["QUESTIONS"][$key]["ANSWERS"]) ? $arFields["QUESTIONS"][$key]["ANSWERS"] : array());
			$arFieldsQuestions[$key]["ANSWERS"] = array();
			
			foreach ($arFields["QUESTIONS"][$key]["ANSWERS"] as $keya => $arAnswer)
			{
				$arAnswer["ID"] = intVal($arAnswer["ID"]);
				$arAnswer["MESSAGE"] = trim($arAnswer["MESSAGE"]);
				if (empty($arAnswer["MESSAGE"])):
					$aMsg[] = array(
						"id" => "answer_".$keya, 
						"text" => GetMessage("VOTE_ANSWER_EMPTY"));
					break;
				elseif ($arAnswer["ID"] > 0 && is_set($arAnswers, $arAnswer["ID"])):
					$arFieldsQuestions[$key]["ANSWERS"][$keya] = array(
						"ID" => $arAnswer["ID"], 
						"MESSAGE" => $arAnswer["MESSAGE"],
						"FIELD_TYPE" => $arAnswer["FIELD_TYPE"], 
						"DEL" => ($arAnswer["DEL"] == "Y" ? "Y" : "N"));
				elseif ($arQuestion["DEL"] == "Y"):
				else:
					$arFieldsQuestions[$key]["ANSWERS"][$keya] = array(
						"MESSAGE" => $arAnswer["MESSAGE"],
						"FIELD_TYPE" => $arAnswer["FIELD_TYPE"]);
				endif;
			}
			
			if (empty($arFieldsQuestions[$key]["ANSWERS"])):
				if ($arQuestion["ID"] > 0):
					$arFieldsQuestions[$key]["DEL"] = "Y";
				else:
					unset($arFieldsQuestions[$key]);
				endif;
				$iQuestionsCount--;
			endif;
		}
		
		if ($iQuestionsCount <= 0)
		{
			$aMsg[] = array(
				"id" => "questions", 
				"text" => GetMessage("VOTE_VOTE_EMPTY"));
		}
	}
	if (!empty($aMsg)):
		$e = new CAdminException(array_reverse($aMsg));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	endif;
/************** Check Data/*****************************************/
/************** Actions ********************************************/
	if (!empty($arVote)):
		CVote::Update($VOTE_ID, $arFieldsVote);
	else:
		$arFieldsVote["UNIQUE_TYPE"] = $params["UNIQUE_TYPE"];
		$arFieldsVote["DELAY"] = $params["DELAY"];
		$arFieldsVote["DELAY_TYPE"] = $params["DELAY_TYPE"];
		
		$arVote["ID"] = CVote::Add($arFieldsVote);
		
		if (intVal($arVote["ID"]) <= 0):
			$e = $GLOBALS['APPLICATION']->GetException();
			$aMsg[] = array(
				"id" => "VOTE_ID", 
				"text" => $e->GetString());
		endif;
	endif;
	
	if (empty($aMsg))
	{
		$iQuestionsCount = 0;
		foreach ($arFieldsQuestions as $key => $arQuestion)
		{
			if ($arQuestion["DEL"] == "Y"):
				CVoteQuestion::Delete($arQuestion["ID"]);
				continue;
			elseif ($arQuestion["ID"] > 0): 
				$iQuestionsCount++; 
				$arQuestion["C_SORT"] = $iQuestionsCount * 10;
				CVoteQuestion::Update($arQuestion["ID"], $arQuestion);
			else: 
				$arQuestion["C_SORT"] = ($iQuestionsCount + 1) * 10;
				$arQuestion["VOTE_ID"] = $arVote["ID"];
				$arQuestion["ID"] = intVal(CVoteQuestion::Add($arQuestion));
				if ($arQuestion["ID"] <= 0):
					continue;
				endif;
				$iQuestionsCount++; 
			endif;
			$arAnswersCount = 0;
			foreach ($arQuestion["ANSWERS"] as $keya => $arAnswer)
			{
				if ($arAnswer["DEL"] == "Y"):
					CVoteAnswer::Delete($arAnswer["ID"]);
					continue;
				elseif ($arAnswer["ID"] > 0): 
					$arAnswersCount++; 
					$arAnswer["C_SORT"] = $arAnswersCount * 10;
					CVoteAnswer::Update($arAnswer["ID"], $arAnswer);
				else: 
					$arAnswer["QUESTION_ID"] = $arQuestion["ID"];
					$arAnswer["C_SORT"] = ($arAnswersCount + 1)* 10;
					$arAnswer["ID"] = intVal(CVoteAnswer::Add($arAnswer));
					if ($arAnswer["ID"] <= 0):
						continue;
					endif;
					$arAnswersCount++; 
				endif;
			}
			if ($arAnswersCount <= 0)
			{
				CVoteQuestion::Delete($arQuestion["ID"]);
				$iQuestionsCount--; 
			}
		}
		if ($iQuestionsCount <= 0)
		{
			CVote::Delete($arVote["ID"]);
		}
	}
	
	if (!empty($aMsg)):
		$e = new CAdminException(array_reverse($aMsg));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	else:
		return $arVote["ID"];
	endif;
/************** Actions/********************************************/
/*	$arFields = array(
		"ID" => 345, 
		"TITLE" => "test", 
		"...", 
		"QUESTIONS" => array(
			array(
				"ID" => 348, 
				"QUESTION" => "test", 
				"ANSWERS" => array(
					array(
						"ID" => 340, 
						"MESSAGE" => "test"), 
					array(
						"ID" => 0, 
						"MESSAGE" => "test"), 
					array(
						"ID" => 350,
						"DEL" => "Y",  
						"MESSAGE" => "test")
					)
				), 
			array(
				"ID" => 351, 
				"DEL" => "Y", 
				"QUESTION" => "test", 
				"ANSWERS" => array(
					array(
						"ID" => 0, 
						"MESSAGE" => "test"), 
					array(
						"ID" => 478,
						"DEL" => "Y",  
						"MESSAGE" => "test")
					)
				), 
			array(
				"ID" => 0, 
				"QUESTION" => "test", 
				"ANSWERS" => array(
					array(
						"ID" => 0, 
						"MESSAGE" => "test"), 
					)
				), 
			)
		);
*/
	
	
}

?>