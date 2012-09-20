<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__); 

class CAllVoteChannel
{
	function err_mess()
	{
		$module_id = "vote";
		return "<br>Module: ".$module_id."<br>Class: CAllVoteChannel<br>File: ".__FILE__;
	}
	
	function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered)
	{
		$err_mess = (CVoteChannel::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("C.ID",$val,$match);
						break;
					case "SITE_ID":
					case "SITE":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("CS.SITE_ID", $val, $match);
						$left_join = "LEFT JOIN b_vote_channel_2_site CS ON (C.ID = CS.CHANNEL_ID)";
						break;
					case "TITLE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("C.TITLE",$val,$match);
						break;
					case "SID":
					case "SYMBOLIC_NAME":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("C.SYMBOLIC_NAME",$val,$match);
						break;
					case "ACTIVE":
						$arSqlSearch[] = ($val=="Y") ? "C.ACTIVE='Y'" : "C.ACTIVE='N'";
						break;
					case "FIRST_SITE_ID":
					case "LID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("C.FIRST_SITE_ID",$val,$match);
						break;
				}
			}
		}

		if ($by == "s_id")					$strSqlOrder = "ORDER BY C.ID";
		elseif ($by == "s_timestamp")		$strSqlOrder = "ORDER BY C.TIMESTAMP_X";
		elseif ($by == "s_c_sort")			$strSqlOrder = "ORDER BY C.C_SORT";
		elseif ($by == "s_active")			$strSqlOrder = "ORDER BY C.ACTIVE";
		elseif ($by == "s_symbolic_name")	$strSqlOrder = "ORDER BY C.SYMBOLIC_NAME";
		elseif ($by == "s_title")			$strSqlOrder = "ORDER BY C.TITLE ";
		elseif ($by == "s_votes")			$strSqlOrder = "ORDER BY VOTES";
		else
		{
			$by = "s_id";
			$strSqlOrder = "ORDER BY C.ID";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
		SELECT CC.*, C.*, C.FIRST_SITE_ID LID, C.SYMBOLIC_NAME SID,
				".$DB->DateToCharFunction("C.TIMESTAMP_X")." TIMESTAMP_X
		FROM (
			SELECT C.ID, count(V.ID) VOTES
			FROM b_vote_channel C
				LEFT JOIN b_vote V ON (V.CHANNEL_ID = C.ID)
				".$left_join."
			WHERE ".$strSqlSearch."
			GROUP BY C.ID) CC
		INNER JOIN b_vote_channel C ON (C.ID = CC.ID)
		".$strSqlOrder;

		$is_filtered = IsFiltered($strSqlSearch);

		if (VOTE_CACHE_TIME===false || strpos($_SERVER['REQUEST_URI'], '/bitrix/admin/')!==false)
		{
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			return $res;
		}
		else
		{
			global $CACHE_MANAGER;
			$md5 = md5($strSql);
			if($CACHE_MANAGER->Read(VOTE_CACHE_TIME, "b_vote_channel_".$md5, "b_vote_channel"))
			{
				$arCache = $CACHE_MANAGER->Get("b_vote_channel_".$md5);
			}
			else
			{
				$res = $DB->Query($strSql, false, $err_mess.__LINE__);
				while($ar = $res->Fetch())
					$arCache[] = $ar;

				$CACHE_MANAGER->Set("b_vote_channel_".$md5, $arCache);
			}

			$r = new CDBResult();
			$r->InitFromArray($arCache);
			unset($arCache);
			return $r;
		}
	}

	function GetSiteArray($CHANNEL_ID)
	{
		$err_mess = (CAllVoteChannel::err_mess())."<br>Function: GetSiteArray<br>Line: ";
		global $DB;
		$CHANNEL_ID = intval($CHANNEL_ID);
		if ($CHANNEL_ID<=0) return false;

		$arCache = Array();

		if (VOTE_CACHE_TIME===false)
		{
			$arrRes = array();
			$strSql = "SELECT CS.SITE_ID FROM b_vote_channel_2_site CS WHERE CS.CHANNEL_ID = ".$CHANNEL_ID;
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($ar = $rs->Fetch()) $arrRes[] = $ar["SITE_ID"];
			return $arrRes;
		}
		else
		{
			global $CACHE_MANAGER;
			if($CACHE_MANAGER->Read(VOTE_CACHE_TIME, "b_vote_channel_2_site"))
			{
				$arCache = $CACHE_MANAGER->Get("b_vote_channel_2_site");
			}
			else
			{
				$strSql = "SELECT * FROM b_vote_channel_2_site";
				$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
				while ($ar = $rs->Fetch()) 
					$arCache[$ar["CHANNEL_ID"]][] = $ar["SITE_ID"];

				$CACHE_MANAGER->Set("b_vote_channel_2_site", $arCache);
			}

			if (array_key_exists($CHANNEL_ID, $arCache))
				return $arCache[$CHANNEL_ID];
			else
				return array();
		}

	}

	function Delete($ID)
	{
		global $DB, $CACHE_MANAGER;
		$err_mess = (CAllVoteChannel::err_mess())."<br>Function: Delete<br>Line: ";
		$ID = intval($ID);
		if ($ID <= 0):
			return true;
		endif;
		// drop votes
		$strSql = "SELECT ID FROM b_vote WHERE CHANNEL_ID='$ID'";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($zr = $z->Fetch()) CVote::Delete($zr["ID"]);
		
		// drop permissions
		if (VOTE_CACHE_TIME!==false): 
			$CACHE_MANAGER->CleanDir("b_vote_perm");
            if (defined("BX_COMP_MANAGED_CACHE"))
            {
                $CACHE_MANAGER->ClearByTag("vote_form_channel_".$ID);
            } 
            $CACHE_MANAGER->CleanDir("b_vote_channel");
		endif;
		$DB->Query("DELETE FROM b_vote_channel_2_group WHERE CHANNEL_ID=".$ID, false, $err_mess.__LINE__);
		$DB->Query("DELETE FROM b_vote_channel_2_site WHERE CHANNEL_ID=".$ID, false, $err_mess.__LINE__);
		$res = $DB->Query("DELETE FROM b_vote_channel WHERE ID=".$ID, false, $err_mess.__LINE__);
		return $res;
	}

	function GetByID($ID)
	{
		$err_mess = (CAllVoteChannel::err_mess())."<br>Function: GetByID<br>Line: ";
		global $DB;
		$ID = intval($ID);
		if ($ID<=0) return;
		$res = CVoteChannel::GetList($by, $order, array("ID" => $ID), $is_filtered);
		return $res;
	}

	function GetArrayGroupPermission($channel_id)
	{
		global $DB;

		$strSql =
			"SELECT * ".
			"FROM b_vote_channel_2_group ".
			"WHERE CHANNEL_ID = '".intval($channel_id)."'";

		$dbres = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arRes = Array();
		while($res = $dbres->Fetch())
			$arRes[$res["GROUP_ID"]] = $res["PERMISSION"];

		return $arRes;

	}

	function GetGroupPermission($channel_id, $arGroups=false, $get_from_database="")
	{
		global $DB, $USER, $CACHE_MANAGER;
		$err_mess = (CAllVoteChannel::err_mess())."<br>Function: GetGroupPermission<br>Line: ";
		$channel_id = intval($channel_id);
		
		if ($arGroups === false):
			$arGroups = $USER->GetUserGroupArray();
			$arGroups = (!is_array($arGroups) ? array(2) : $arGroups);
		elseif (!is_array($arGroups)):
			$arGroups = array($arGroups);
		endif;
		$groups = array();
		foreach ($arGroups as $grp):
			if (intVal($grp) > 0):
				$groups[] = intVal($grp);
			endif;
		endforeach;
		$arGroups = $groups;
		$groups = implode(",", $arGroups);
		
		$cache_id = "b_vote_perm".$channel_id."_".$groups;
		
		if ($USER->IsAdmin() && $get_from_database != "Y")
			return 2;
		elseif (empty($arGroups))
		{
		}
		elseif (VOTE_CACHE_TIME !== false && $CACHE_MANAGER->Read(VOTE_CACHE_TIME, $cache_id, "b_vote_perm"))
		{
			$right = $CACHE_MANAGER->Get($cache_id);
			return intval($right);
		}
		else
		{
			$strSql = "
				SELECT
					max(PERMISSION) as PERMISSION
				FROM
					b_vote_channel_2_group
				WHERE
					CHANNEL_ID = '".$channel_id."'
				and GROUP_ID in ($groups)";

			$t = $DB->Query($strSql, false, $err_mess.__LINE__);
			$tr = $t->Fetch();
			if (VOTE_CACHE_TIME !== false):
				$CACHE_MANAGER->Set($cache_id, intval($tr["PERMISSION"]));
			endif;
			return intval($tr["PERMISSION"]);
		}
		return 0;
	}
}

class CVoteDiagramType
{
	var $arType = Array();

	function CVoteDiagramType($directCall=true)
	{
		if ($directCall)
		{
			trigger_error("CVoteDiagramType is singleton!", E_USER_ERROR);
			return;
		}

		$this->arType = Array(
			VOTE_DEFAULT_DIAGRAM_TYPE => GetMessage("VOTE_DIAGRAM_TYPE_HISTOGRAM"),
			"circle" => GetMessage("VOTE_DIAGRAM_TYPE_CIRCLE")
		);
	}

	function &getInstance()
	{
		static $instance;
		if (!is_object($instance))
			$instance = new CVoteDiagramType(false);

		return $instance;
	}

}

function VoteGetFilterOperation($key)
{
	$strNegative = "N";
	if (substr($key, 0, 1)=="!")
	{
		$key = substr($key, 1);
		$strNegative = "Y";
	}

	if (substr($key, 0, 2)==">=")
	{
		$key = substr($key, 2);
		$strOperation = ">=";
	}
	elseif (substr($key, 0, 1)==">")
	{
		$key = substr($key, 1);
		$strOperation = ">";
	}
	elseif (substr($key, 0, 2)=="<=")
	{
		$key = substr($key, 2);
		$strOperation = "<=";
	}
	elseif (substr($key, 0, 1)=="<")
	{
		$key = substr($key, 1);
		$strOperation = "<";
	}
	elseif (substr($key, 0, 1)=="@")
	{
		$key = substr($key, 1);
		$strOperation = "IN";
	}
	elseif (substr($key, 0, 1)=="%")
	{
		$key = substr($key, 1);
		$strOperation = "LIKE";
	}
	else
	{
		$strOperation = "=";
	}

	return array("FIELD"=>$key, "NEGATIVE"=>$strNegative, "OPERATION"=>$strOperation);
}

?>
