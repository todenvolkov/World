<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/classes/general/vote.php");

class CVote extends CAllVote
{
	function err_mess()
	{
		$module_id = "vote";
		return "<br>Module: ".$module_id."<br>Class: CVote<br>File: ".__FILE__;
	}

	function GetDropDownList()
	{
		global $DB;
		$err_mess = (CVote::err_mess())."<br>Function: GetDropDownList<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				concat('[',ID,'] ',TITLE) as REFERENCE
			FROM b_vote
			ORDER BY C_SORT, ID
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	function CheckVotingIP($VOTE_ID, $REMOTE_ADDR, $KEEP_IP_SEC, $params = array())
	{
		global $DB;
		$err_mess = (CVote::err_mess())."<br>Function: CheckVotingIP<br>Line: ";
		$VOTE_ID = intval($VOTE_ID);
		$KEEP_IP_SEC = intval($KEEP_IP_SEC);
		$params = (is_array($params) ? $params : array($params));
		$params["RETURN_SEARCH_STRING"] = ($params["RETURN_SEARCH_STRING"] == "Y" ? "Y" : "N");
		
		$arSqlSearch = array(
			"VE.VOTE_ID='".$VOTE_ID."'", 
			"VE.IP='".$DB->ForSql($REMOTE_ADDR, 15)."'");
		if ($KEEP_IP_SEC > 0):
			$arSqlSearch[] = "(FROM_UNIXTIME(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - ".$KEEP_IP_SEC.") <= VE.DATE_VOTE)";
		endif;
		if ($params["RETURN_SEARCH_STRING"] == "Y"):
			return implode(" AND ", $arSqlSearch);
		endif;
		$strSql = "SELECT VE.ID FROM b_vote_event VE WHERE ".implode(" AND ", $arSqlSearch);
		$db_res = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($db_res && $res = $db_res->Fetch()):
			return false;
		endif;
		return true;
	}

	function GetNextStartDate($CHANNEL_ID)
	{
		global $DB;
		$err_mess = (CVote::err_mess())."<br>Function: GetNextStartDate<br>Line: ";
		$CHANNEL_ID = intval($CHANNEL_ID);
		$strSql = "
			SELECT
				".$DB->DateToCharFunction("max(DATE_ADD(DATE_END, INTERVAL 1 SECOND))")." MIN_DATE_START
			FROM
				b_vote
			WHERE
				CHANNEL_ID = '$CHANNEL_ID'
			";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		if (strlen($zr["MIN_DATE_START"])<=0) return GetTime(time(),"FULL");
		else return $zr["MIN_DATE_START"];
	}

	function WrongDateInterval($CURRENT_VOTE_ID, $DATE_START, $DATE_END, $CHANNEL_ID)
	{
		global $DB;
		$err_mess = (CVote::err_mess())."<br>Function: WrongDateInterval<br>Line: ";
		$CURRENT_VOTE_ID = intval($CURRENT_VOTE_ID);
		$CURRENT_VOTE_ID = ($CURRENT_VOTE_ID > 0 ? $CURRENT_VOTE_ID : false);
		$CHANNEL_ID = intVal($CHANNEL_ID);
		$CHANNEL_ID = ($CHANNEL_ID > 0 ? $CHANNEL_ID : false);
		$DATE_START = ($DATE_START == false ? false : (strLen(trim($DATE_START)) <= 0 ? false : trim($DATE_START)));
		$DATE_END = ($DATE_END == false ? false : (strLen(trim($DATE_END)) <= 0 ? false : trim($DATE_END)));
		
		if ($CURRENT_VOTE_ID == false && $CHANNEL_ID == false):
			return 0;
		elseif ($CHANNEL_ID > 0):
			$db_res = CVoteChannel::GetByID($CHANNEL_ID);
			if ($db_res && $res = $db_res->Fetch()):
				if ($res["VOTE_SINGLE"] != "Y"):
					return 0;
				endif;
			endif;
		endif;
		
		$st = ($DATE_START == false ? "VV.DATE_START" : "FROM_UNIXTIME('".MakeTimeStamp($DATE_START)."')");
		$en = ($DATE_END == false ? "VV.DATE_END" : "FROM_UNIXTIME('".MakeTimeStamp($DATE_END)."')");
		if ($CURRENT_VOTE_ID <= 0):
			if ($DATE_START == false):
				$st = "FROM_UNIXTIME('".time()."')";
			endif;
			if ($DATE_END == false):
				$en = "FROM_UNIXTIME('1924984799')"; // MakeTimeStamp("31.12.2030 23:59:59","DD.MM.YYYY HH:MI:SS")
			endif;
		endif;

		$strSql = "
			SELECT V.ID
			FROM b_vote V 
			".($CURRENT_VOTE_ID > 0 ? 
			"LEFT JOIN b_vote VV ON (VV.ID = ".$CURRENT_VOTE_ID.") " : "")."
			INNER JOIN b_vote_channel VC ON (V.CHANNEL_ID = VC.ID AND VC.VOTE_SINGLE = 'Y')
			WHERE
				V.CHANNEL_ID=".($CHANNEL_ID == false ? "VV.CHANNEL_ID" : $CHANNEL_ID)." AND 
				V.ACTIVE='Y' AND 
				".($CURRENT_VOTE_ID > 0 ? 
				"V.ID<>'$CURRENT_VOTE_ID' AND " : "")."
				(
					($st between V.DATE_START and V.DATE_END) OR
					($en between V.DATE_START and V.DATE_END) OR
					(V.DATE_START between $st and $en) OR
					(V.DATE_END between $st and $en)
				)";
		$db_res = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($db_res && $res = $db_res->Fetch()):
			$vid = intval($res["ID"]);
			return $vid;
		endif;
		return 0;
	}

	function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered)
	{
		global $DB;
		$err_mess = (CVote::err_mess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = array();
		$strSqlSearch = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		$sSubFilter = "";
		foreach ($arFilter as $key => $val)
		{
			if (empty($val) || (is_string($val) && $val === "NOT_REF")): 
				continue;
			endif;
			$key = strtoupper($key);
			switch($key)
			{
				case "ID":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					$arSqlSearch[] = GetFilterQuery("V.ID", $val, $match);
					break;
				case "ACTIVE":
					$arSqlSearch[] = "V.ACTIVE = '".($val == "Y" ? "Y" : "N")."'";
					break;
				case "DATE_START_1":
					$arSqlSearch[] = "V.DATE_START >= ".$DB->CharToDateFunction($val, "SHORT");
					break;
				case "DATE_START_2":
					$arSqlSearch[] = "V.DATE_START < ".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
					break;
				case "DATE_END_1":
					$arSqlSearch[] = "V.DATE_END >= ".$DB->CharToDateFunction($val, "SHORT");
					break;
				case "DATE_END_2":
					$arSqlSearch[] = "V.DATE_END < ".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
					break;
				case "LAMP":
					if ($val == "red")
						$arSqlSearch[] = "(V.ACTIVE<>'Y' or now()<V.DATE_START or now()>V.DATE_END)";
					elseif ($val == "green")
						$arSqlSearch[] = "(V.ACTIVE='Y' and now()>=V.DATE_START and now()<=V.DATE_END)";
					break;
				case "CHANNEL":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "Y" ? "N" : "Y");
					$arSqlSearch[] = GetFilterQuery("C.ID, C.TITLE, C.SYMBOLIC_NAME", $val, $match);
					break;
				case "CHANNEL_ID":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					$arSqlSearch[] = GetFilterQuery("V.CHANNEL_ID", $val, $match);
					$sSubFilter = " AND ".GetFilterQuery("CCC.ID", $val, $match);
					break;
				case "TITLE":
				case "DESCRIPTION":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "Y" ? "N" : "Y");
					$arSqlSearch[] = GetFilterQuery("V.".$key, $val, $match);
					break;
				case "COUNTER_1":
					$arSqlSearch[] = "V.COUNTER>='".intval($val)."'";
					break;
				case "COUNTER_2":
					$arSqlSearch[] = "V.COUNTER<='".intval($val)."'";
					break;
			}
		}
		if ($by == "s_id")					$strSqlOrder = "ORDER BY V.ID";
		elseif ($by == "s_title")			$strSqlOrder = "ORDER BY V.TITLE";
		elseif ($by == "s_date_start")		$strSqlOrder = "ORDER BY V.DATE_START";
		elseif ($by == "s_date_end")		$strSqlOrder = "ORDER BY V.DATE_END";
		elseif ($by == "s_lamp")			$strSqlOrder = "ORDER BY LAMP";
		elseif ($by == "s_counter")			$strSqlOrder = "ORDER BY V.COUNTER";
		elseif ($by == "s_active")			$strSqlOrder = "ORDER BY V.ACTIVE";
		elseif ($by == "s_c_sort")			$strSqlOrder = "ORDER BY V.C_SORT";
		elseif ($by == "s_channel")			$strSqlOrder = "ORDER BY V.CHANNEL_ID";
		else
		{
			$by = "s_id";
			$strSqlOrder = "ORDER BY V.ID";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT VV.*, C.TITLE as CHANNEL_TITLE, V.*,
				".$DB->DateToCharFunction("V.DATE_START")."	DATE_START,
				".$DB->DateToCharFunction("V.DATE_END")." DATE_END,
				UNIX_TIMESTAMP(V.DATE_END) - UNIX_TIMESTAMP(V.DATE_START) PERIOD
			FROM (
				SELECT V.CHANNEL_ID, V.ID, count(Q.ID) QUESTIONS,  
					IF((C.VOTE_SINGLE = 'Y'), 
						(IF(V.ID = VV.ACTIVE_VOTE_ID, 'green', 'red')), 
						(IF(V.ACTIVE = 'Y' AND V.DATE_START <= NOW() AND NOW() <= V.DATE_END, 'green', 'red'))) LAMP 
				FROM b_vote V
					INNER JOIN b_vote_channel C ON (C.ID=V.CHANNEL_ID)
					LEFT JOIN b_vote_question Q ON (Q.VOTE_ID=V.ID)
					LEFT JOIN (
						SELECT VVV.CHANNEL_ID, MAX(VVV.ID) AS ACTIVE_VOTE_ID
						FROM b_vote VVV, b_vote_channel CCC
						WHERE VVV.CHANNEL_ID = CCC.ID AND CCC.VOTE_SINGLE='Y' AND VVV.ACTIVE = 'Y' 
							AND NOW() >= VVV.DATE_START AND VVV.DATE_END >= NOW()
							".$sSubFilter."
						GROUP BY VVV.CHANNEL_ID) VV ON (VV.CHANNEL_ID = V.CHANNEL_ID)
					WHERE
						".$strSqlSearch."
					GROUP BY V.CHANNEL_ID, V.ID
			) VV
			INNER JOIN b_vote_channel C ON (C.ID = VV.CHANNEL_ID)
			INNER JOIN b_vote V ON (V.ID = VV.ID) ".
			$strSqlOrder;
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = IsFiltered($strSqlSearch);
		return $res;
	}

	function GetPublicList($arFilter=Array(), $strSqlOrder="ORDER BY C.C_SORT, C.ID, V.DATE_START desc")
	{
		global $DB, $USER;
		$err_mess = (CVote::err_mess())."<br>Function: GetPublicList<br>Line: ";
		$arSqlSearch = array();
		$strSqlSearch = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		foreach ($arFilter as $key => $val)
		{
			if (empty($val) || (is_string($val) && $val === "NOT_REF")): 
				continue;
			endif;
			$key = strtoupper($key);
			switch($key)
			{
				case "SITE":
					$val = (is_array($val) ? implode(" | ", $val) : $val);
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					$arSqlSearch[] = GetFilterQuery("CS.SITE_ID", $val, $match);
					$left_join = "LEFT JOIN b_vote_channel_2_site CS ON (C.ID = CS.CHANNEL_ID)";
					break;
				case "CHANNEL":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					if (is_array($val)):
						$arr = array();
						foreach ($val as $v):
							$v = trim($v);
							if (strLen($v) > 0):
								$arr[] = GetFilterQuery("C.SYMBOLIC_NAME", $v, $match);
							endif;
						endforeach;
						if (!empty($arr)):
							$arSqlSearch[] = "(".implode(" OR ", $arr).")";
						endif;
					else:
						$arSqlSearch[] = GetFilterQuery("C.SYMBOLIC_NAME", $val, $match);
					endif;
					break;
				case "FIRST_SITE_ID":
				case "LID":
					$match = ($arFilter[$key."_EXACT_MATCH"] == "N" ? "Y" : "N");
					$arSqlSearch[] = GetFilterQuery("C.FIRST_SITE_ID",$val,$match);
					break;
			}
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$is_admin = $USER->IsAdmin();
		$groups = $USER->GetGroups();
		$strSql = "
			SELECT C.TITLE CHANNEL_TITLE, V.*,
				".$DB->DateToCharFunction("V.DATE_START")."	DATE_START,
				".$DB->DateToCharFunction("V.DATE_END")."	DATE_END, 
				V4.MAX_PERMISSION, V4.LAMP
			FROM (
				SELECT V.CHANNEL_ID, V.ID,
					".($is_admin ? "2" : "max(G.PERMISSION)")." as MAX_PERMISSION, 
					IF((C.VOTE_SINGLE = 'Y'), 
						(IF(V.ID = VV.ACTIVE_VOTE_ID, 'green', 'red')), 
						(IF(V.ACTIVE = 'Y' AND V.DATE_START <= NOW() AND NOW() <= V.DATE_END, 'green', 'red'))) LAMP 
				FROM b_vote V
				INNER JOIN b_vote_channel C ON (C.ACTIVE = 'Y' AND V.CHANNEL_ID = C.ID) 
				LEFT JOIN (
					SELECT VVV.CHANNEL_ID, MAX(VVV.ID) AS ACTIVE_VOTE_ID
					FROM b_vote VVV, b_vote_channel CCC
					WHERE VVV.CHANNEL_ID = CCC.ID AND CCC.VOTE_SINGLE='Y' AND VVV.ACTIVE = 'Y' 
						AND NOW() >= VVV.DATE_START AND VVV.DATE_END >= NOW()
					GROUP BY VVV.CHANNEL_ID) VV ON (VV.CHANNEL_ID = V.CHANNEL_ID)
				LEFT JOIN b_vote_channel_2_group G ON (G.CHANNEL_ID = C.ID and G.GROUP_ID in ($groups))
				$left_join
				WHERE
					$strSqlSearch
					AND V.ACTIVE = 'Y' AND V.DATE_START <= NOW()
				GROUP BY V.CHANNEL_ID, V.ID
				".($is_admin ? "" : "
				HAVING MAX_PERMISSION > 0")."
			) V4
			INNER JOIN b_vote V ON (V4.ID = V.ID)
			INNER JOIN b_vote_channel C ON (V4.CHANNEL_ID = C.ID) 
			".$DB->ForSql($strSqlOrder);
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}
}
?>
