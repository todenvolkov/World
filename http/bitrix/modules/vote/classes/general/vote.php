<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__); 

class CAllVote
{
	function err_mess()
	{
		$module_id = "vote";
		return "<br>Module: ".$module_id."<br>Class: CAllVote<br>File: ".__FILE__;
	}
	
	function CheckFields($ACTION, &$arFields, $ID = 0, $arParams = array())
	{
		global $DB;
		$aMsg = array();
		$ID = intVal($ID);
		$date_start = 0;
		$date_end = 0;
		$arVote = array();
		if ($ID > 0):
			$db_res = CVote::GetByID($ID);
			if ($db_res && $res = $db_res->Fetch()):
				$arVote = $res;
			endif;
		endif;
		
		unset($arFields["ID"]);
		if (is_set($arFields, "CHANNEL_ID") || $ACTION == "ADD") 
		{
			$arFields["CHANNEL_ID"] = intVal($arFields["CHANNEL_ID"]);
			if ($arFields["CHANNEL_ID"] <= 0):
				$aMsg[] = array(
					"id" => "CHANNEL_ID", 
					"text" => GetMessage("VOTE_EMPTY_CHANNEL_ID"));
			endif;
		}
		
		if (is_set($arFields, "C_SORT")) { $arFields["C_SORT"] = intVal($arFields["C_SORT"]); }
		if (is_set($arFields, "ACTIVE") || $ACTION == "ADD") { $arFields["ACTIVE"] = ($arFields["ACTIVE"] == "N" ? "N" : "Y"); }
		
		unset($arFields["TIMESTAMP_X"]);
		$date_start = false;
		if (is_set($arFields, "DATE_START") || $ACTION == "ADD") 
		{ 
			$arFields["DATE_START"] = trim($arFields["DATE_START"]); 
			$date_start = MakeTimeStamp($arFields["DATE_START"]);
			if (!$date_start):
				$aMsg[] = array(
					"id" => "DATE_START", 
					"text" => GetMessage("VOTE_WRONG_DATE_START"));
			endif;
		}
		
		if (is_set($arFields, "DATE_END") || $ACTION == "ADD") 
		{ 
			$arFields["DATE_END"] = trim($arFields["DATE_END"]); 
			$date_end = false;
			if (strLen($arFields["DATE_END"]) <= 0):
				if ($date_start != false):
					$date_end = $date_start + 2592000;
					$arFields["DATE_END"] = GetTime($date_end, "FULL");
				else:
					$date_end = 1924984799; // '31.12.2030 23:59:59'
					$arFields["DATE_END"] = GetTime($date_end, "FULL");
				endif;
			else:
				$date_end = MakeTimeStamp($arFields["DATE_END"]);
			endif;
			if (!$date_end):
				$aMsg[] = array(
					"id" => "DATE_END", 
					"text" => GetMessage("VOTE_WRONG_DATE_END"));
			elseif ($date_start >= $date_end && !empty($arFields["DATE_START"])):
				$aMsg[] = array(
					"id" => "DATE_END", 
					"text" => GetMessage("VOTE_WRONG_DATE_TILL"));
			endif;
		}
		if (empty($aMsg) && (is_set($arFields, "DATE_START") || is_set($arFields, "DATE_END") || is_set($arFields, "CHANNEL_ID") || is_set($arFields, "ACTIVE")))
		{
			$vid = 0;
			if ($ACTION == "ADD" && $arFields["ACTIVE"] == "Y")
			{
				$vid = CVote::WrongDateInterval(0, $arFields["DATE_START"], $arFields["DATE_END"], $arFields["CHANNEL_ID"]);
			}
			elseif ($ACTION != "ADD" && !(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y"))
			{
				$res = array(
					"DATE_START" => (is_set($arFields, "DATE_START") ? $arFields["DATE_START"] : false), 
					"DATE_END" => (is_set($arFields, "DATE_END") ? $arFields["DATE_END"] : false), 
					"CHANNEL_ID" => (is_set($arFields, "CHANNEL_ID") ? $arFields["CHANNEL_ID"] : false));
				$vid = CVote::WrongDateInterval($ID, $res["DATE_START"], $res["DATE_END"], $res["CHANNEL_ID"]);
			}
			if (intVal($vid) > 0):
				$aMsg[] = array(
					"id" => "DATE_START", 
					"text" => str_replace("#ID#", $vid, GetMessage("VOTE_WRONG_INTERVAL")));
			endif;
		}
		if (is_set($arFields, "IMAGE_ID") && strLen($arFields["IMAGE_ID"]["name"]) <= 0 && strLen($arFields["IMAGE_ID"]["del"]) <= 0)
		{
			unset($arFields["IMAGE_ID"]);
		}
		elseif (is_set($arFields, "IMAGE_ID"))
		{
			if ($str = CFile::CheckImageFile($arFields["IMAGE_ID"])):
				$aMsg[] = array(
					"id" => "IMAGE_ID", 
					"text" => $str);
			else:
				$arFields["IMAGE_ID"]["MODULE_ID"] = "vote";
				if (!empty($arVote)):
					$arFields["IMAGE_ID"]["old_file"] = $arVote["IMAGE_ID"];
				endif;
			endif;
		}
		
		if (is_set($arFields, "COUNTER")) { $arFields["COUNTER"] = intVal($arFields["COUNTER"]); }
		if (is_set($arFields, "TITLE")) { $arFields["TITLE"] = trim($arFields["TITLE"]); }
		if (is_set($arFields, "DESCRIPTION")) { $arFields["DESCRIPTION"] = trim($arFields["DESCRIPTION"]); }
		if (is_set($arFields, "DESCRIPTION_TYPE") || $ACTION == "ADD") { $arFields["DESCRIPTION_TYPE"] = ($arFields["DESCRIPTION_TYPE"] == "html" ? "html" : "text"); }
		
		if (is_set($arFields, "EVENT1")) { $arFields["EVENT1"] = trim($arFields["EVENT1"]); }
		if (is_set($arFields, "EVENT2")) { $arFields["EVENT2"] = trim($arFields["EVENT2"]); }
		if (is_set($arFields, "EVENT3")) { $arFields["EVENT3"] = trim($arFields["EVENT3"]); }
		if (is_set($arFields, "UNIQUE_TYPE")) { $arFields["UNIQUE_TYPE"] = intVal($arFields["UNIQUE_TYPE"]); }
		
		if (is_set($arFields, "DELAY_TYPE") || $ACTION == "ADD") { 
			$arFields["DELAY_TYPE"] = trim($arFields["DELAY_TYPE"]); 
			$arFields["DELAY_TYPE"] = (in_array($arFields["DELAY_TYPE"], array("S", "M", "H", "D")) ? $arFields["DELAY_TYPE"] : "D");
		}
		if (is_set($arFields, "DELAY") || $ACTION == "ADD") { $arFields["DELAY"] = intVal($arFields["DELAY"]); }
		
		unset($arFields["KEEP_IP_SEC"]);
        $sec = 1;
        switch ($arFields["DELAY_TYPE"]) {
            case "S": $sec = 1; break;
            case "M": $sec = 60; break;
            case "H": $sec = 3600; break;
            case "D": $sec = 86400; break; }
        $arFields["KEEP_IP_SEC"] = intval($arFields["DELAY"]) * $sec;

        if ((is_set($arFields, "UNIQUE_TYPE")) && ($arFields['UNIQUE_TYPE'] < 5))
        {
            switch($arFields['UNIQUE_TYPE'])
            {
                case 0:$arFields['UNIQUE_TYPE']=5;
                    break;
                case 1:$arFields['UNIQUE_TYPE']=6;
                    break;
                case 2:$arFields['UNIQUE_TYPE']=8;
                    break;
                case 3:$arFields['UNIQUE_TYPE']=12;
                    break;
                case 4:$arFields['UNIQUE_TYPE']=20;
                    break;
            }
        }
            
		if (CVote::IsOldVersion() != "Y")
		{
			unset($arFields["TEMPLATE"]);
			unset($arFields["RESULT_TEMPLATE"]);
		}
		
		if (is_set($arFields, "TEMPLATE")) { $arFields["TEMPLATE"] = trim($arFields["TEMPLATE"]); }
		if (is_set($arFields, "RESULT_TEMPLATE")) { $arFields["RESULT_TEMPLATE"] = trim($arFields["RESULT_TEMPLATE"]); }
		if (is_set($arFields, "NOTIFY")) { $arFields["NOTIFY"] = ($arFields["NOTIFY"] == "Y" ? "Y" : "N"); }
        if (is_set($arFields, "REQUIRED")) { $arFields["REQUIRED"] = ($arFields["REQUIRED"] == "Y" ? "Y" : "N"); }

		if(!empty($aMsg))
		{
			$e = new CAdminException(array_reverse($aMsg));
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		return true;
	}
	
	function Add($arFields, $strUploadDir = false)
	{
		global $DB, $CACHE_MANAGER;
		$arBinds = array();
		$strUploadDir = ($strUploadDir === false ? "vote" : $strUploadDir);
		
		if (!CVote::CheckFields("ADD", $arFields))
			return false;
/***************** Event onBeforeVoteAdd ***************************/
		$events = GetModuleEvents("vote", "onBeforeVoteAdd");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$arFields));
/***************** /Event ******************************************/
		if (empty($arFields))
			return false;
		
		CFile::SaveForDB($arFields, "IMAGE_ID", $strUploadDir);
		$arFields["~TIMESTAMP_X"] = $DB->GetNowFunction();
		if (is_set($arFields, "DESCRIPTION"))
			$arBinds["DESCRIPTION"] = $arFields["DESCRIPTION"];

		$ID = $DB->Add("b_vote", $arFields, $arBinds);
		
		if (defined("BX_COMP_MANAGED_CACHE"))
        {
            $CACHE_MANAGER->ClearByTag("vote_form_vote_".$ID);
        } 
/***************** Event onAfterVoteAdd ****************************/
		$events = GetModuleEvents("vote", "onAfterVoteAdd");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
/***************** /Event ******************************************/
		return $ID;
	}

	function Update($ID, $arFields, $strUploadDir = false)
	{
		global $DB, $CACHE_MANAGER;
		$arBinds = array();
		$strUploadDir = ($strUploadDir === false ? "vote" : $strUploadDir);
		$ID = intVal($ID);

		if ($ID <= 0 || !CVote::CheckFields("UPDATE", $arFields, $ID))
			return false;

/***************** Event onBeforeVoteUpdate ************************/
		$events = GetModuleEvents("vote", "onBeforeVoteUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$arFields));
/***************** /Event ******************************************/
		if (empty($arFields))
			return false;
/***************** Cleaning cache **********************************/

		if (defined("BX_COMP_MANAGED_CACHE"))
        {
            $CACHE_MANAGER->ClearByTag("vote_form_vote_".$ID);
        } 
/***************** Cleaning cache/**********************************/

		$arFields["~TIMESTAMP_X"] = $DB->GetNowFunction();
		if (is_set($arFields, "DESCRIPTION"))
			$arBinds["DESCRIPTION"] = $arFields["DESCRIPTION"];

		CFile::SaveForDB($arFields, "IMAGE_ID", $strUploadDir);

		$strUpdate = $DB->PrepareUpdateBind("b_vote", $arFields, $strUploadDir, false, $arBinds);
		
		if (!empty($strUpdate)):
			$strSql = "UPDATE b_vote SET ".$strUpdate." WHERE ID=".$ID;
			$DB->QueryBind($strSql, $arBinds);
		endif;
/***************** Event onAfterVoteUpdate *************************/
		$events = GetModuleEvents("vote", "onAfterVoteUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
/***************** /Event ******************************************/
		return $ID;
	}

	function Delete($ID)
	{
		global $DB, $CACHE_MANAGER;
		$err_mess = (CVote::err_mess())."<br>Function: Delete<br>Line: ";
		$ID = intval($ID);
		if ($ID <= 0):
			return false;
		endif;
		
		@set_time_limit(1000);
		$DB->StartTransaction();
		
		if (defined("BX_COMP_MANAGED_CACHE"))
        {
            $CACHE_MANAGER->ClearByTag("vote_form_vote_".$ID);
        } 
		// delete questions
		CVoteQuestion::Delete(false, $ID);
		// delete vote images
		$strSql = "SELECT IMAGE_ID FROM b_vote WHERE ID = ".$ID." AND IMAGE_ID > 0";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($zr = $z->Fetch()) { CFile::Delete($zr["IMAGE_ID"]); }
		
		// delete vote events
		$DB->Query("DELETE FROM b_vote_event WHERE VOTE_ID='$ID'", false, $err_mess.__LINE__);
		// delete vote
		$res = $DB->Query("DELETE FROM b_vote WHERE ID='$ID'", false, $err_mess.__LINE__);
		$DB->Commit();
		return $res;
	}

	function Reset($ID)
	{
		global $DB,$CACHE_MANAGER;
		$err_mess = (CVote::err_mess())."<br>Function: Reset<br>Line: ";
		$ID = intval($ID);
		if ($ID <= 0):
			return false;
		endif;
		// zeroize questions
		CVoteQuestion::Reset(false, $ID);
		// zeroize events
		$DB->Query("DELETE FROM b_vote_event WHERE VOTE_ID='$ID'", false, $err_mess.__LINE__);
		// zeroize vote counter
		unset($GLOBALS["VOTE_CACHE_VOTING"][$ID]);
		$DB->Update("b_vote", array("COUNTER"=>"0"), "WHERE ID=".$ID, $err_mess.__LINE__);
		if (defined("BX_COMP_MANAGED_CACHE"))
        {
            $CACHE_MANAGER->ClearByTag("vote_form_vote_".$ID);
        } 
	}

    function Copy($ID)
    {
		global $DB, $CACHE_MANAGER;
		$err_mess = (CVote::err_mess())."<br>Function: Copy<br>Line: ";
		$ID = intval($ID);
		if ($ID <= 0):
			return false;
		endif;
        $rCurrentVote = CVote::GetByID($ID);
        if (!$arCurrentVote = $rCurrentVote->Fetch())
            return false;
        unset($arCurrentVote["ID"]);
        $arCurrentVote['ACTIVE'] = "N";

        $newImageId = false;
        if (intval($arCurrentVote['IMAGE_ID'] > 0))
        {
            $imageId = $arCurrentVote['IMAGE_ID'];
            $newImageId = CFile::CopyFile($imageId);
            $arCurrentVote["IMAGE_ID"] = NULL;
        }
        $newID = CVote::Add($arCurrentVote);
        if ($newID === false)
            return false;
		$DB->Update("b_vote", array("COUNTER"=>"0"), "WHERE ID=".$newID, $err_mess.__LINE__);
        if ($newImageId)
        {
            $DB->Update("b_vote", array("IMAGE_ID"=>$newImageId), "WHERE ID=".$newID, $err_mess.__LINE__);
        }

        $state = true;
        $rQuestions = CVoteQuestion::GetList($ID, $by, $order, array(), $is_filtered);
        while ($arQuestion = $rQuestions->Fetch())
        {
            $state = $state && ( CVoteQuestion::Copy($arQuestion['ID'], $newID) !== false);
        }

        if ($state == true)
            return $newID; 
        else return $state;
    }

	function IsOldVersion()
	{
		$res = "N";
		$arr = GetTemplateList("RV");
		if (is_array($arr) && count($arr["reference"])>0) $res = "Y";
		else
		{
			$arr = GetTemplateList("SV");
			if (is_array($arr) && count($arr["reference"])>0) $res = "Y";
			else
			{
				$arr = GetTemplateList("RQ");
				if (is_array($arr) && count($arr["reference"])>0) $res = "Y";
			}
		}
		return $res;
	}

	function GetByID($ID)
	{
		$err_mess = (CAllVote::err_mess())."<br>Function: GetByID<br>Line: ";
		$ID = intval($ID);
		//if($ID<=0) 
			//return false;
		$res = CVote::GetList($by, $order, array("ID" => $ID), $is_filtered);
		return $res;
	}

	function UserAlreadyVote($VOTE_ID, $VOTE_USER_ID, $UNIQUE_TYPE, $KEEP_IP_SEC, $USER_ID = false)
	{
		global $DB;
		$err_mess = (CAllVote::err_mess())."<br>Function: UserAlreadyVote<br>Line: ";
		$VOTE_ID = intval($VOTE_ID);
		$UNIQUE_TYPE = intval($UNIQUE_TYPE);
		$VOTE_USER_ID = intval($VOTE_USER_ID);
		$USER_ID = intVal($USER_ID);

        if ($VOTE_ID <= 0)
            return false;

        if ($UNIQUE_TYPE <= 0)
            return false;

        $UNIQUE_TYPE -= 5;
        //No restrictions
        if ($UNIQUE_TYPE <= 0)
            return false;

		//One session
		if (($UNIQUE_TYPE & 1) && is_array($_SESSION["VOTE_ARRAY"]) && in_array($VOTE_ID, $_SESSION["VOTE_ARRAY"]))
			return true;

        $arSqlSearch = array();

        //Same cookie
        if ($UNIQUE_TYPE & 2)
        {
            if (($VOTE_USER_ID > 0) && ($UNIQUE_TYPE != 6))
            {
                $arSqlSearch[] = "VE.VOTE_USER_ID='".$VOTE_USER_ID."'";
            }
        }

        // Same IP
        if ($UNIQUE_TYPE & 4)
        { 
            $arSqlSearch[] = CVote::CheckVotingIP($VOTE_ID, $_SERVER["REMOTE_ADDR"], $KEEP_IP_SEC, array("RETURN_SEARCH_STRING" => "Y"));
        }
		// Same ID	
        if ($UNIQUE_TYPE & 8)
        {
            if ($USER_ID > 0)
            {
                $arSqlSearch[] = "VU.AUTH_USER_ID=".$USER_ID;
            } else {
                return true;
            }
        }

        if (!empty($arSqlSearch))
        {
            $strSql = "
                SELECT 'x' 
                FROM b_vote_event VE
                LEFT JOIN b_vote_user VU ON (VE.VOTE_USER_ID = VU.ID)
                WHERE VE.VOTE_ID=".$VOTE_ID." AND ((".implode(") OR (", $arSqlSearch)."))";
            $db_res = $DB->Query($strSql, false, $err_mess.__LINE__);
            if ($db_res && $res = $db_res->Fetch())
                return true;
        }

		return false;
	}

	function UserGroupPermission($CHANNEL_ID)
	{
		return CVoteChannel::GetGroupPermission($CHANNEL_ID, $GLOBALS['USER']->GetUserGroupArray());
	}

	function SetVoteUserID()
	{
		global $DB, $USER;
		$err_mess = (CAllVote::err_mess())."<br>Function: SetVoteUserID<br>Line: ";
		$COOKIE_VOTE_USER_ID = intval($GLOBALS["APPLICATION"]->get_cookie("VOTE_USER_ID"));
		$_SESSION["VOTE_USER_ID"] = $COOKIE_VOTE_USER_ID;
		$arFields = array(
			"LAST_IP"		=> "'".$DB->ForSql($_SERVER["REMOTE_ADDR"],15)."'",
			"DATE_LAST"		=> $DB->GetNowFunction(),
			"STAT_GUEST_ID"	=> "'".intval($_SESSION["SESS_GUEST_ID"])."'",
			"AUTH_USER_ID"	=> "'".intval($USER->GetID())."'"
			);
		$rows = $DB->Update("b_vote_user", $arFields, "WHERE (ID='".$COOKIE_VOTE_USER_ID."') AND (AUTH_USER_ID='".intval($USER->GetID())."')", $err_mess.__LINE__);
		// insert user if not exists
		if (intval($rows)<=0)
		{
			$arFields["DATE_LAST"] = $DB->GetNowFunction();
			$arFields["DATE_FIRST"] = $DB->GetNowFunction();
			$_SESSION["VOTE_USER_ID"] = $DB->Insert("b_vote_user",$arFields, $err_mess.__LINE__);
			$_SESSION["VOTE_USER_ID"] = intval($_SESSION["VOTE_USER_ID"]);
			$GLOBALS["APPLICATION"]->set_cookie("VOTE_USER_ID", $_SESSION["VOTE_USER_ID"]);
		}
		return $_SESSION["VOTE_USER_ID"];
	}

	function UpdateVoteUserID($VOTE_USER_ID)
	{
		global $DB;
		$err_mess = (CAllVote::err_mess())."<br>Function: UpdateVoteUserID<br>Line: ";

		$VOTE_USER_ID = intval($VOTE_USER_ID);
		$arFields = array(
			"DATE_LAST"		=> $DB->GetNowFunction(), 
			"COUNTER"		=> "COUNTER+1"
			);
		$rows = $DB->Update("b_vote_user", $arFields, "WHERE ID='".$VOTE_USER_ID."'", $err_mess.__LINE__);
	}

	function KeepVoting()
	{
		global $DB, $VOTING_LAMP, $USER_ALREADY_VOTE, $USER_ALREADY_VOTE, $USER_GROUP_ALLOW_VOTING, $USER;
		$err_mess = (CAllVote::err_mess())."<br>Function: KeepVoting<br>Line: ";

		$PUBLIC_VOTE_ID = intval($_REQUEST["PUBLIC_VOTE_ID"]);
		if (!($_SERVER['REQUEST_METHOD'] == "POST" && strlen($_REQUEST["vote"]) > 0 && $PUBLIC_VOTE_ID > 0 )):
			return false;
		elseif (($VOTE_ID = intVal(GetVoteDataByID($PUBLIC_VOTE_ID, $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, "N"))) 
			&& ($VOTE_ID <= 0 || $arVote["LAMP"] != "green")):
			$GLOBALS["VOTING_OK"] = "N";
			$VOTING_LAMP = "red";
			return false;
		endif;

        $channelRes = CVoteChannel::GetList($by, $order, array('ID' => $arVote['CHANNEL_ID']), $is_filtered);
        if ($arChannel = $channelRes->Fetch())
        {
            if ($arChannel["USE_CAPTCHA"] == "Y") 
            {
                include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
                $cpt = new CCaptcha();
                if (strlen($_REQUEST["captcha_word"]) > 0)
                {
                    $captchaPass = COption::GetOptionString("main", "captcha_password", "");
                    if (!$cpt->CheckCodeCrypt($_REQUEST["captcha_word"], $_REQUEST["captcha_code"], $captchaPass))
                    {
                        $GLOBALS["VOTING_OK"] = "N";
                        $GLOBALS["BAD_CAPTCHA"] = "Y";
                        return false;
                    }
                }
                else
                {
                    if (!$cpt->CheckCode($_REQUEST["captcha_word"], 0))
                    {
                        $GLOBALS["VOTING_OK"] = "N";
                        $GLOBALS["NO_CAPTCHA"] = "Y";
                        return false;
                    }
                }
            }
        } else {
            return false;
        }

		$VOTING_LAMP = "green";
		// get user id
		$_SESSION["VOTE_USER_ID"] = CVote::SetVoteUserID();
		// check: can user vote
		$UNIQUE_TYPE = $arVote["UNIQUE_TYPE"];
		$KEEP_IP_SEC = $arVote["KEEP_IP_SEC"];
		$USER_ALREADY_VOTE = (CVote::UserAlreadyVote($VOTE_ID, $_SESSION["VOTE_USER_ID"], $UNIQUE_TYPE, $KEEP_IP_SEC, $USER->GetID())) ? "Y" : "N";
		$CHANNEL_ID = $arVote["CHANNEL_ID"];
		$USER_GROUP_PERMISSION = CVote::UserGroupPermission($CHANNEL_ID);
		
		$arSqlAnswers = array();
		// if user can vote that 
		if ($USER_ALREADY_VOTE == "N" && $USER_GROUP_PERMISSION >= 2)
		{
			// check answers 
			foreach ($arQuestions as $arQuestion)
			{
				$QUESTION_ID = $arQuestion["ID"];
				$radio = "N"; $checkbox = "N"; $multiselect = "N"; $dropdown = "N";
				
				if (!array_key_exists($QUESTION_ID, $arAnswers))
					continue;
					
				$arSqlAnswers[$QUESTION_ID] = array();
				
				foreach ($arAnswers[$QUESTION_ID] as $arAnswer)
				{
					$ANSWER_ID = 0;
					switch ($arAnswer["FIELD_TYPE"]) :
						case 0: // radio
							if ($radio == "N")
							{
								$field_name = "vote_radio_".$QUESTION_ID;
								global $$field_name;
								$ANSWER_ID = intval($$field_name);
								if ($ANSWER_ID > 0)
								{
									$arSqlAnswers[$QUESTION_ID][$ANSWER_ID] = array("ANSWER_ID" => $ANSWER_ID);
									$radio = "Y";
								}
							}
							break;
						case 1: // checkbox
							if ($checkbox == "N")
							{
								$field_name = "vote_checkbox_".$QUESTION_ID;
								global $$field_name;
								if (is_array($$field_name) && count($$field_name)>0)
								{
									reset($$field_name);
									foreach($$field_name as $ANSWER_ID)
									{
										$ANSWER_ID = intval($ANSWER_ID);
										if ($ANSWER_ID>0)
										{
											$arSqlAnswers[$QUESTION_ID][$ANSWER_ID] = array("ANSWER_ID" => $ANSWER_ID);
										}
									}
									$checkbox = "Y";
								}
							}
							break;
						case 2: // dropdown list
							if ($dropdown=="N")
							{
								$field_name = "vote_dropdown_".$QUESTION_ID;
								global $$field_name;
								$ANSWER_ID = intval($$field_name);
								if ($ANSWER_ID>0)
								{
									$arSqlAnswers[$QUESTION_ID][$ANSWER_ID] = array(
										"ANSWER_ID"			=> $ANSWER_ID);
									$dropdown = "Y";
								}
							}
							break;
						case 3: // multiselect list
							if ($multiselect=="N")
							{
								$field_name = "vote_multiselect_".$QUESTION_ID;
								global $$field_name;
								if (is_array($$field_name) && count($$field_name)>0)
								{
									reset($$field_name);
									foreach($$field_name as $ANSWER_ID)
									{
										$ANSWER_ID = intval($ANSWER_ID);
										if ($ANSWER_ID>0)
										{
											$arSqlAnswers[$QUESTION_ID][$ANSWER_ID] = array(
												"ANSWER_ID"			=> $ANSWER_ID);
										}
									}
									$multiselect = "Y";
								}
							}
							break;
						case 4: // field
							$field_name = "vote_field_".$arAnswer["ID"];
							global $$field_name;
							$ANSWER_ID = $arAnswer["ID"];
							$MESSAGE = $$field_name;
							if (strlen($MESSAGE) > 0)
							{
								$arSqlAnswers[$QUESTION_ID][$ANSWER_ID] = array(
									"ANSWER_ID"			=> $ANSWER_ID, 
									"MESSAGE"			=> "'".$DB->ForSql(trim($MESSAGE),2000)."'");
							}
							break;
						case 5: // text
							$field_name = "vote_memo_".$arAnswer["ID"];
							global $$field_name;
							$ANSWER_ID = $arAnswer["ID"];
							$MESSAGE = $$field_name;
							if (strlen($MESSAGE) > 0)
							{
								$arSqlAnswers[$QUESTION_ID][$ANSWER_ID] = array(
									"ANSWER_ID"			=> $ANSWER_ID, 
									"MESSAGE"			=> "'".$DB->ForSql(trim($MESSAGE),2000)."'");
							}
							break;
					endswitch;
				}
				if (empty($arSqlAnswers[$QUESTION_ID])):
					unset($arSqlAnswers[$QUESTION_ID]);
				endif;
                if (($arQuestion['REQUIRED'] == 'Y') && (!isset($arSqlAnswers[$QUESTION_ID])))
                {
                    $GLOBALS["VOTING_OK"] = "N";
                    $GLOBALS["VOTE_REQUIRED_MISSING"] = "Y";
                    return;
                }
			}
        } else {
            $GLOBALS["VOTING_OK"] = "N";
        }
        $GLOBALS["VOTING_ID"] = $VOTE_ID;
		if (!empty($arSqlAnswers))
		{
			unset($GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]);
			unset($GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["V"]);
			
			// vote event
			$arFields = array(
				"VOTE_ID"			=> $VOTE_ID,
				"VOTE_USER_ID"		=> intval($_SESSION["VOTE_USER_ID"]),
				"DATE_VOTE"			=> $DB->GetNowFunction(),
				"STAT_SESSION_ID"	=> intval($_SESSION["SESS_SESSION_ID"]),
				"IP"				=> "'".$DB->ForSql($_SERVER["REMOTE_ADDR"],15)."'",
				"VALID"				=> "'Y'");
			$EVENT_ID = $DB->Insert("b_vote_event", $arFields, $err_mess.__LINE__);
			$EVENT_ID = intval($EVENT_ID);
			if ($EVENT_ID > 0)
			{
				$arSqlQuestionsID = array();
				$arSqlAnswersID = array();
				
				foreach ($arSqlAnswers as $QUESTION_ID => $arSqlAnswer):
					$arFields = array("EVENT_ID" => $EVENT_ID, "QUESTION_ID" => $QUESTION_ID);
					$EVENT_QUESTION_ID = intval($DB->Insert("b_vote_event_question", $arFields, $err_mess.__LINE__));
					if ($EVENT_QUESTION_ID > 0):
						$arSqlQuestionsID[] = $QUESTION_ID;
						foreach ($arSqlAnswer as $res):
							$arSqlAnswersID[] = $res["ANSWER_ID"];
							$res["EVENT_QUESTION_ID"] = $EVENT_QUESTION_ID;
							$DB->Insert("b_vote_event_answer", $res, $err_mess.__LINE__);
						endforeach;
					endif;
				endforeach;
				
				if (empty($arSqlQuestionsID) || empty($arSqlAnswersID)):
					$EVENT_ID = $DB->Query("DELETE FROM b_vote_event WHERE ID=".$EVENT_ID, $arFields, $err_mess.__LINE__);
				else:
					$arFields = array("COUNTER" => "COUNTER+1");
					$DB->Update("b_vote", $arFields, "WHERE ID='".$VOTE_ID."'", $err_mess.__LINE__);
					$DB->Update("b_vote_question", $arFields, "WHERE ID in (".implode(", ", $arSqlQuestionsID).")",$err_mess.__LINE__);
					$DB->Update("b_vote_answer", $arFields, "WHERE ID in (".implode(", ", $arSqlAnswersID).")", $err_mess.__LINE__);
					
					// increment user counter
					CVote::UpdateVoteUserID($_SESSION["VOTE_USER_ID"]);
                    $GLOBALS["VOTING_OK"] = "Y";
					$_SESSION["VOTE_ARRAY"][] = $VOTE_ID;
					// statistic module
					if (CModule::IncludeModule("statistic")) 
					{
						$event3 = $arVote["EVENT3"];
						if (strlen($event3) <= 0):
							$event3 = "http://".$_SERVER["HTTP_HOST"]."/bitrix/admin/vote_user_results.php?EVENT_ID=". $EVENT_ID."&lang=".LANGUAGE_ID;
						endif;
						CStatEvent::AddCurrent($arVote["EVENT1"], $arVote["EVENT2"], $event3);
					}
					
					if ($arVote["NOTIFY"]=="Y")
					{
						// send message 
						$arEventFields = array(
							"ID"				=> $EVENT_ID,
							"TIME"				=> GetTime(time(),"FULL"),
							"VOTE_TITLE"		=> $arVote["TITLE"],
							"VOTE_DESCRIPTION"	=> $arVote["DESCRIPTION"],
							"VOTE_ID"			=> $arVote["ID"],
							"CHANNEL"			=> $arChannel["TITLE"],
							"CHANNEL_ID"		=> $arChannel["ID"],
							"VOTER_ID"			=> $_SESSION["VOTE_USER_ID"],
							"USER_NAME"			=> $USER->GetFullName(),
							"LOGIN"				=> $USER->GetLogin(),
							"USER_ID"			=> $USER->GetID(),
							"STAT_GUEST_ID"		=> intval($_SESSION["SESS_GUEST_ID"]),
							"SESSION_ID"		=> intval($_SESSION["SESS_SESSION_ID"]),
							"IP"				=> $_SERVER["REMOTE_ADDR"]);
						$arrSites = CVoteChannel::GetSiteArray($arChannel["ID"]);
						CEvent::Send("VOTE_NEW", $arrSites,$arEventFields);
					}
				endif;
			}
		} else {
            $GLOBALS["VOTING_OK"] = "N";
			$GLOBALS["USER_VOTE_EMPTY"] = "Y";
		}

	}
	
	function GetNextSort($CHANNEL_ID)
	{
		global $DB;
		$err_mess = (CAllVote::err_mess())."<br>Function: GetNextSort<br>Line: ";
		$CHANNEL_ID = intval($CHANNEL_ID);
		$strSql = "SELECT max(C_SORT) MAX_SORT FROM b_vote WHERE CHANNEL_ID='$CHANNEL_ID'";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		return intval($zr["MAX_SORT"])+100;
	}
}
?>
