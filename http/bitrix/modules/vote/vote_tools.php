<?
global $VOTE_CACHE_VOTING;
$VOTE_CACHE_VOTING = Array();

function GetAnswerTypeList()
{
	$arr = array(
		"reference_id" => array(0,1,2,3,4,5),
		"reference" => array("radio","checkbox","dropdown","multiselect","text","textarea")
		);
	return $arr;
}

function GetVoteDiagramArray()
{
	$object =& CVoteDiagramType::getInstance();
	return $object->arType;
}

function GetVoteDiagramList()
{
	$object =& CVoteDiagramType::getInstance();

	return Array(
		"reference_id" => array_keys($object->arType),
		"reference" => array_values($object->arType)
		);
}

// vote data
function GetVoteDataByID($VOTE_ID, &$arChannel, &$arVote, &$arQuestions, &$arAnswers, &$arDropDown, &$arMultiSelect, &$arGroupAnswers, $getGroupAnswers)
{
	$VOTE_ID = intval($VOTE_ID);
	$arChannel = array();
	$arVote = array();
	$arQuestions = array();
	$arAnswers = array();
	$arDropDown = array();
	$arMultiSelect = array();
	$GLOBALS["VOTE_CACHE_VOTING"] = (is_array($GLOBALS["VOTE_CACHE_VOTING"]) ? $GLOBALS["VOTE_CACHE_VOTING"] : array());
	$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID] = (is_array($GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]) ? $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID] : array());

	//$arVote
	if (!empty($GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["V"])):
		$arVote = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["V"];
	else:
		$db_res = CVote::GetByID($VOTE_ID);
		if (!($db_res && $arVote = $db_res->GetNext(true, true))):
			return false;
		endif;
		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["V"] = $arVote;
	endif;

	//$arChannel
	if (!empty($GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["C"])):
		$arChannel = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["C"];
	else:
		$db_res = CVoteChannel::GetByID($arVote["CHANNEL_ID"]);
		if (!($db_res && $arChannel = $db_res->GetNext(true, false))):
			return false;
		endif;
		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["C"] = $arChannel;
	endif;
	
	if (!empty($GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]))
	{
		$arQuestions =  $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["Q"];
		$arAnswers = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["A"];
		$arMultiSelect = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["M"];
		$arDropDown = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["D"];

		if ($GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["GA"] == "N" && $getGroupAnswers == "Y")
		{
            $db_res = CVoteEvent::GetUserAnswerStat($VOTE_ID);
            while($res = $db_res->GetNext(true, false))
            {
                $arGroupAnswers[$res['ANSWER_ID']][] = $res;
            }
			$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["G"] = $arGroupAnswers;
		}
		else
		{
			$arGroupAnswers = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["G"];
		}
	}
	else
	{
		$db_res = CVoteQuestion::GetList($VOTE_ID, ($by="s_c_sort"), ($order="asc"), array("ACTIVE" => "Y"), $is_filtered);
		while ($res = $db_res->GetNext(true,false)) 
		{
			$arQuestions[] = $res;
			$w = CVoteAnswer::GetList($res["ID"],($by="s_c_sort"), ($order="asc"), array("ACTIVE"=>"Y"));
			while ($wr=$w->GetNext(true,true))
			{
				$arAnswers[$res["ID"]][] = $wr;
			}
		}
        if ($getGroupAnswers=="Y")
        {
            $db_res = CVoteEvent::GetUserAnswerStat($VOTE_ID);
            while($res = $db_res->GetNext(true, false))
            {
                $arGroupAnswers[$res['ANSWER_ID']][] = $res;
            }
        }

		// dropdown and multiselect and text inputs
		foreach ($arQuestions as $key => $arQ)
		{
			$QUESTION_ID = $arQ["ID"];
			$arDropReference = array();
			$arDropReferenceID = array();
			$arMultiReference = array();
			$arMultiReferenceID = array();
			if (!is_array($arAnswers[$QUESTION_ID])):
				continue;
			endif;
			foreach ($arAnswers[$QUESTION_ID] as $keya => $arA)
			{
				switch ($arA["FIELD_TYPE"])
				{
					case 2:
						$arDropReference[] = $arA["MESSAGE"];
                        $arDropReferenceTilda[] = $arA["~MESSAGE"];
						$arDropReferenceID[] = $arA["ID"];
						break;
					case 3:
						$arMultiReference[] = $arA["MESSAGE"];
                        $arMultiReferenceTilda[] = $arA["~MESSAGE"];
						$arMultiReferenceID[] = $arA["ID"];
                        break;
				}
			}
			if (count($arDropReference) > 0)
				$arDropDown[$QUESTION_ID] = array("reference"=>$arDropReference, "~reference"=>$arDropReferenceTilda, "reference_id"=>$arDropReferenceID);
			if (count($arMultiReference)>0)
				$arMultiSelect[$QUESTION_ID] = array("reference"=>$arMultiReference, "~reference"=>$arMultiReferenceTilda, "reference_id"=>$arMultiReferenceID);
		}
		reset($arChannel);
		reset($arVote);
		reset($arQuestions);
		reset($arDropDown);
		reset($arMultiSelect);
		reset($arAnswers);

		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["Q"] = $arQuestions;
		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["A"] = $arAnswers;
		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["M"] = $arMultiSelect;
		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["D"] = $arDropDown;
		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["G"] = $arGroupAnswers;
		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["GA"] = ($getGroupAnswers == "Y" ? "Y" : "N");

	}

	return $arVote["ID"];
}

// return vote id for channel sid with check permissions and ACTIVE vote
function GetCurrentVote($GROUP_SID, $site_id=SITE_ID, $access=1)
{
	$z = CVoteChannel::GetList($by, $order, array("SID"=>$GROUP_SID, "SID_EXACT_MATCH"=>"Y", "SITE"=>$site_id, "ACTIVE"=>"Y"), $is_filtered);
	if ($zr = $z->Fetch())
	{
		$perm = CVoteChannel::GetGroupPermission($zr["ID"]);
		if (intval($perm)>=$access)
		{
			$v = CVote::GetList($by, $order, array("CHANNEL_ID"=>$zr["ID"], "LAMP"=>"green"), $is_filtered);
			if ($vr = $v->Fetch()) return $vr["ID"];
		}
	}
	return 0;
}

// return PREIOUS vote id for channel sid with check permissions and ACTIVE vote
function GetPrevVote($GROUP_SID, $level=1, $site_id=SITE_ID, $access=1)
{
	$z = CVoteChannel::GetList($by, $order, array("SID"=>$GROUP_SID, "SID_EXACT_MATCH"=>"Y", "SITE"=>$site_id, "ACTIVE"=>"Y"), $is_filtered);
	if ($zr = $z->Fetch())
	{
		$perm = CVoteChannel::GetGroupPermission($zr["ID"]);
		if (intval($perm)>=$access)
		{
			$v = CVote::GetList(($by = "s_date_start"), ($order = "desc"), array("CHANNEL_ID"=>$zr["ID"], "LAMP"=>"red"), $is_filtered);
			$i = 0;
			while ($vr=$v->Fetch())
			{
				$i++;
				if ($level==$i) 
				{
					$VOTE_ID = $vr["ID"];
					break;
				}
			}
		}
	}
	return intval($VOTE_ID);
}

// return votes list id for channel sid with check permissions and ACTIVE vote
function GetVoteList($GROUP_SID = "", $strSqlOrder = "ORDER BY C.C_SORT, C.ID, V.DATE_START desc", $site_id = SITE_ID)
{
	$arFilter["SITE"] = $site_id;
	if (is_array($GROUP_SID) && !empty($GROUP_SID)):
		$arr = array();
		foreach ($GROUP_SID as $v):
			$v = trim($v);
			if (strlen($v) > 0):
				$arr[] = $v;
			endif;
		endforeach;
		if (!empty($arr)):
			$arFilter["CHANNEL"] = $arr;
		endif;
	elseif (strlen($GROUP_SID) > 0):
		$arFilter["CHANNEL"] = $GROUP_SID;
	endif;
	$z = CVote::GetPublicList($arFilter, $strSqlOrder);
	return $z;
}

// return true if user already vote on this vote
function IsUserVoted($PUBLIC_VOTE_ID)
{
	$PUBLIC_VOTE_ID = intval($PUBLIC_VOTE_ID);

	if ($PUBLIC_VOTE_ID <= 0)
		return false;

	$res = CVote::GetByID($PUBLIC_VOTE_ID);
	if($res && ($arVote = $res->GetNext(true, false)))
	{
		$VOTE_USER_ID = intval($GLOBALS["APPLICATION"]->get_cookie("VOTE_USER_ID"));
		$res = CVote::UserAlreadyVote($arVote["ID"], $VOTE_USER_ID, $arVote["UNIQUE_TYPE"], $arVote["KEEP_IP_SEC"], $GLOBALS["USER"]->GetID());
		return $res;
	}

	return false;
}

// return random unvoted vote id for user whith check permissions
function GetAnyAccessibleVote($site_id=SITE_ID)
{
	$z = CVoteChannel::GetList($by="s_c_sort", $order="asc", array("ACTIVE"=>"Y","SITE"=>$site_id), $is_filtered);
	while ($zr = $z->Fetch())
	{
		$perm = CVoteChannel::GetGroupPermission($zr["ID"]);
		if (intval($perm)>=2)
		{
			$VOTE_ID = GetCurrentVote($zr["SYMBOLIC_NAME"], $site_id, 2);
			$VOTE_ID = intval($VOTE_ID);
			if ($VOTE_ID>0)
			{
				if (!(IsUserVoted($VOTE_ID))) return $VOTE_ID;
			}
		}
	}
	return false;
}


/********************************************************************
				Functions for old templates
/*******************************************************************/
function GetTemplateList($type="SV", $path="xxx")
{
	global $DOCUMENT_ROOT;
	if ($path=="xxx") 
	{
		if ($type=="SV")
			$path = COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH");
		elseif ($type=="RV")
			$path = COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH_VOTE");
		elseif ($type=="RQ")
			$path = COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH_QUESTION");
	}
	$arr = array();
	$handle=@opendir($_SERVER["DOCUMENT_ROOT"].$path);
	if($handle)
	{
		while (false!==($fname = readdir($handle))) 
		{
			if (is_file($_SERVER["DOCUMENT_ROOT"].$path.$fname) && $fname!="." && $fname!="..")
			{
				$arReferenceId[] = $fname;
				$arReference[] = $fname;
			}
		}
		closedir($handle);
	}
	$arr = array("reference"=>$arReference,"reference_id"=>$arReferenceId);
	return $arr;
}

function arrAnswersSort(&$arr, $order="desc")
{
	for ($key1=0; $key1<count($arr); $key1++)
	{
		for ($key2=0; $key2<count($arr); $key2++)
		{
			$sort1 = intval($arr[$key1]["COUNTER"]);
			$sort2 = intval($arr[$key2]["COUNTER"]);
			if ($order=="asc")
			{
				if ($sort1<$sort2)
				{
					$arr_tmp = $arr[$key1];
					$arr[$key1] = $arr[$key2];
					$arr[$key2] = $arr_tmp;
				}
			}
			else
			{
				if ($sort1>$sort2)
				{
					$arr_tmp = $arr[$key1];
					$arr[$key1] = $arr[$key2];
					$arr[$key2] = $arr_tmp;
				}
			}
		}
	}
}

// return current vote form for channel
function ShowCurrentVote($GROUP_SID, $site_id=SITE_ID)
{
	$CURRENT_VOTE_ID = GetCurrentVote($GROUP_SID, $site_id, 2);
	if (intval($CURRENT_VOTE_ID)>0) ShowVote($CURRENT_VOTE_ID);
}
// return previous vote results
function ShowPrevVoteResults($GROUP_SID, $level=1, $site_id=SITE_ID)
{
	$PREV_VOTE_ID = GetPrevVote($GROUP_SID, $level, $site_id);
	if (intval($PREV_VOTE_ID)>0) ShowVoteResults($PREV_VOTE_ID);
}
// return current vote results
function ShowCurrentVoteResults($GROUP_SID, $site_id=SITE_ID)
{
	$CURRENT_VOTE_ID = GetCurrentVote($GROUP_SID,  $site_id);
	if (intval($CURRENT_VOTE_ID)>0) ShowVoteResults($CURRENT_VOTE_ID);
}

// return current vote form with check permissions
function ShowVote($VOTE_ID, $template1="")
{
	global $MESS, $VOTING_LAMP, $VOTING_OK, $USER_ALREADY_VOTE, $USER_GROUP_PERMISSION, $VOTE_USER_ID, $VOTE_PERMISSION, $DOCUMENT_ROOT, $APPLICATION;
	$VOTE_ID = GetVoteDataByID($VOTE_ID, $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, "N");
	if (intval($VOTE_ID)>0)
	{
		$perm = CVoteChannel::GetGroupPermission($arChannel["ID"]);
		if (intval($perm)>=2)
		{
			$template = (strlen($arVote["TEMPLATE"])<=0) ? "default.php" : $arVote["TEMPLATE"];
			$VOTE_PERMISSION = CVote::UserGroupPermission($arChannel["ID"]);
			require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");
			@include_once (GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/lang/", "/".$template));
			$path = COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH");
			if (strlen($template1)>0) $template = $template1;

			if ($APPLICATION->GetShowIncludeAreas())
			{
				$arIcons = Array();
				if (CModule::IncludeModule("fileman"))
				{
					$arIcons[] =
							Array(						
								"URL" => "/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&full_src=Y&path=". urlencode($path.$template),
								"SRC" => "/bitrix/images/vote/panel/edit_template.gif",
								"ALT" => GetMessage("VOTE_PUBLIC_ICON_TEMPLATE")
							);
					$arrUrl = parse_url($_SERVER["REQUEST_URI"]);
					$arIcons[] =
							Array(						
								"URL" => "/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&full_src=Y&path=". urlencode($arrUrl["path"]),
								"SRC" => "/bitrix/images/vote/panel/edit_file.gif",
								"ALT" => GetMessage("VOTE_PUBLIC_ICON_HANDLER")
							);
				}
				$arIcons[] =
						Array(						
							"URL" => "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&ID=".$VOTE_ID,
							"SRC" => "/bitrix/images/vote/panel/edit_vote.gif",
							"ALT" => GetMessage("VOTE_PUBLIC_ICON_SETTINGS")
						);
				echo $APPLICATION->IncludeStringBefore($arIcons);
			}
			include($_SERVER["DOCUMENT_ROOT"].$path.$template);
			if ($APPLICATION->GetShowIncludeAreas())
			{
				echo $APPLICATION->IncludeStringAfter();
			}
		}
	}
}
// return current vote results with check permissions
function ShowVoteResults($VOTE_ID, $template1="")
{
	global $MESS, $VOTING_LAMP, $VOTING_OK, $USER_ALREADY_VOTE, $USER_GROUP_PERMISSION, $VOTE_USER_ID, $VOTE_PERMISSION, $DOCUMENT_ROOT, $APPLICATION;
	$VOTE_ID = GetVoteDataByID($VOTE_ID, $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, "Y");
	if (intval($VOTE_ID)>0)
	{
		$perm = CVoteChannel::GetGroupPermission($arChannel["ID"]);
		if (intval($perm)>=1)
		{
			$template = (strlen($arVote["RESULT_TEMPLATE"])<=0) ? "default.php" : $arVote["RESULT_TEMPLATE"];
			$VOTE_PERMISSION = CVote::UserGroupPermission($arChannel["ID"]);
			require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");
			@include_once (GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/lang/", "/".$template));
			$path = COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH_VOTE");
			if (strlen($template1)>0) $template = $template1;
			if ($APPLICATION->GetShowIncludeAreas())
			{
				$arIcons = Array();
				if (CModule::IncludeModule("fileman"))
				{
					$arIcons[] =
							Array(						
								"URL" => "/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&full_src=Y&path=". urlencode($path.$template),
								"SRC" => "/bitrix/images/vote/panel/edit_template.gif",
								"ALT" => GetMessage("VOTE_PUBLIC_ICON_TEMPLATE")
							);
					$arrUrl = parse_url($_SERVER["REQUEST_URI"]);
					$arIcons[] =
							Array(						
								"URL" => "/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&full_src=Y&path=". urlencode($arrUrl["path"]),
								"SRC" => "/bitrix/images/vote/panel/edit_file.gif",
								"ALT" => GetMessage("VOTE_PUBLIC_ICON_HANDLER")
							);
				}
				$arIcons[] =
						Array(						
							"URL" => "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&ID=".$VOTE_ID,
							"SRC" => "/bitrix/images/vote/panel/edit_vote.gif",
							"ALT" => GetMessage("VOTE_PUBLIC_ICON_SETTINGS")
						);
				echo $APPLICATION->IncludeStringBefore($arIcons);
			}
			include($_SERVER["DOCUMENT_ROOT"].$path.$template);
			if ($APPLICATION->GetShowIncludeAreas())
			{
				echo $APPLICATION->IncludeStringAfter();
			}
		}
	}
}

function fill_arc($start, $end, $color) 
{
	global $diameter, $centerX, $centerY, $im, $radius;
	$radius = $diameter/2;
	imagearc($im, $centerX, $centerY, $diameter, $diameter, $start, $end+1, $color);
	imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($start)) * $radius, $centerY + sin(deg2rad($start)) * $radius, $color);
	imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($end)) * $radius, $centerY + sin(deg2rad($end)) * $radius, $color);
	$x = $centerX + $radius * 0.5 * cos(deg2rad($start+($end-$start)/2));
	$y = $centerY + $radius * 0.5 * sin(deg2rad($start+($end-$start)/2));
	imagefill ($im, $x, $y, $color);
}

function DecRGBColor($hex, &$dec1, &$dec2, &$dec3)
{
	if (substr($hex,0,1)!="#") $hex = "#".$hex;
	$dec1 = hexdec(substr($hex,1,2));
	$dec2 = hexdec(substr($hex,3,2));
	$dec3 = hexdec(substr($hex,5,2));
}

function DecColor($hex)
{
	if (substr($hex,0,1)!="#") $hex = "#".$hex;
	$dec = hexdec(substr($hex,1,6));
	return intval($dec);
}

function HexColor($dec)
{
	$hex = sprintf("%06X",$dec); 
	return $hex;
}

function GetNextColor(&$color, &$current_color, $total, $start_color="0000CC", $end_color="FFFFCC")
{
	if (substr($start_color,0,1)=="#") $start_color = substr($start_color,1,6);
	if (substr($end_color,0,1)=="#") $end_color = substr($end_color,1,6);
	if (substr($current_color,0,1)=="#") $current_color = substr($current_color,1,6);
	if (strlen($current_color)<=0) $color = "#".$start_color;
	else
	{
		$step = round((hexdec($end_color)-hexdec($start_color))/$total);
		if (intval($step)<=0) $step = "1500";
		$dec = DecColor($current_color)+intval($step);
		if ($dec<hexdec($start_color)) $dec = $start_color;
		elseif ($dec>hexdec($end_color)) $dec = $end_color;
		elseif ($dec>hexdec("FFFFFF")) $dec = "000000"; 
		else $dec = HexColor($dec);
		$color = "#".$dec;
	}
	$current_color = $color;
}
?>
