<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/classes/general/user.php");

class CForumUser extends CAllForumUser
{
	function GetList($arOrder = Array("ID"=>"ASC"), $arFilter = Array(), $arAddParams = array())
	{
		global $DB;
		$arSqlSearch = array();
		$arSqlOrder = array();
		$strSqlSearch = "";
		$strSqlOrder = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strToUpper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "USER_ID":
					if (intVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(U.ID IS NULL OR U.ID<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" U.ID IS NULL OR NOT ":"")."(U.ID ".$strOperation." ".intVal($val)." )";
					break;
				case "ID":
				case "RANK_ID":
				case "NUM_POSTS":
				case "AVATAR":
					if (intVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FU.".$key." IS NULL OR FU.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FU.".$key." IS NULL OR NOT ":"")."(FU.".$key." ".$strOperation." ".intVal($val)." )";
					break;
				case "SHOW_NAME":
				case "HIDE_FROM_ONLINE":
				case "SUBSC_GROUP_MESSAGE":
				case "SUBSC_GET_MY_MESSAGE":
				case "ALLOW_POST":
					if (strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FU.".$key." IS NULL OR LENGTH(FU.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FU.".$key." IS NULL OR NOT ":"")."(FU.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "ACTIVE":
					if (strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(U.".$key." IS NULL OR LEN(U.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" U.".$key." IS NULL OR NOT ":"")."(U.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "PERSONAL_BIRTHDATE":
					if (strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(U.PERSONAL_BIRTHDATE IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" U.PERSONAL_BIRTHDATE IS NULL OR NOT ":"")."(U.PERSONAL_BIRTHDATE ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				case "PERSONAL_BIRTHDAY":
					if(strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(U.PERSONAL_BIRTHDAY IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" U.PERSONAL_BIRTHDAY IS NULL OR NOT ":"")."(U.PERSONAL_BIRTHDAY ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT").")";
					break;
				case "PERSONAL_BIRTHDAY_DATE":
					$arSqlSearch[] = ($strNegative=="Y"?" U.PERSONAL_BIRTHDAY IS NULL OR NOT ":"")."(DATE_FORMAT(U.PERSONAL_BIRTHDAY, '%m-%d') ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				case "LAST_VISIT":
					if(strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FU.LAST_VISIT IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FU.LAST_VISIT IS NULL OR NOT ":"")."(FU.LAST_VISIT ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
					break;
				case "SHOW_ABC":
					$val = trim($val);
					if ((strLen($val) > 0) && ($val != "Y"))
					{
						$arSqlSearch[] = "((FU.SHOW_NAME='Y') AND (LENGTH(TRIM(CONCAT_WS('',U.NAME,U.LAST_NAME)))>0) AND (CONCAT_WS(' ',U.NAME, U.LAST_NAME) LIKE '%".$DB->ForSQL($val)."%'))
								OR (((FU.SHOW_NAME!='Y' OR (FU.SHOW_NAME IS NULL)) OR ((FU.SHOW_NAME='Y') AND (LENGTH(TRIM(CONCAT_WS('',U.NAME,U.LAST_NAME)))<=0)))
								AND	U.LOGIN LIKE '%".$DB->ForSQL($val)."%')";
					}
					break;
			}
		}
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).") ";
		
		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			
			if ($order!="ASC") $order = "DESC";
			
			if ($by == "USER_ID") $arSqlOrder[] = " U.ID ".$order." ";
			elseif ($by == "SHOW_NAME") $arSqlOrder[] = " FU.SHOW_NAME ".$order." ";
			elseif ($by == "HIDE_FROM_ONLINE") $arSqlOrder[] = " FU.HIDE_FROM_ONLINE ".$order." ";
			elseif ($by == "SUBSC_GROUP_MESSAGE") $arSqlOrder[] = " FU.SUBSC_GROUP_MESSAGE ".$order." ";
			elseif ($by == "SUBSC_GET_MY_MESSAGE") $arSqlOrder[] = " FU.SUBSC_GET_MY_MESSAGE ".$order." ";
			elseif ($by == "NUM_POSTS") $arSqlOrder[] = " FU.NUM_POSTS ".$order." ";
			elseif ($by == "LAST_POST") $arSqlOrder[] = " FU.LAST_POST ".$order." ";
			elseif ($by == "POINTS") $arSqlOrder[] = " FU.POINTS ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " U.NAME ".$order." ";
			elseif ($by == "LAST_NAME") $arSqlOrder[] = " U.LAST_NAME ".$order." ";
			elseif ($by == "LOGIN") $arSqlOrder[] = " U.LOGIN ".$order." ";
			elseif ($by == "LAST_VISIT") $arSqlOrder[] = " FU.LAST_VISIT ".$order." ";
			elseif ($by == "DATE_REGISTER") $arSqlOrder[] = " U.DATE_REGISTER ".$order." ";
			elseif ($by == "SHOW_ABC") $arSqlOrder[] = " SHOW_ABC ".$order." ";
			else
			{
				$arSqlOrder[] = " FU.ID ".$order." ";
				$by = "ID";
			}
		}
		DelDuplicateSort($arSqlOrder); 
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = 
			"SELECT FU.ID, U.ID as USER_ID, FU.SHOW_NAME, FU.DESCRIPTION, FU.IP_ADDRESS, 
				FU.REAL_IP_ADDRESS, FU.AVATAR, FU.NUM_POSTS, FU.POINTS as NUM_POINTS, 
				FU.INTERESTS, FU.SUBSC_GROUP_MESSAGE, FU.SUBSC_GET_MY_MESSAGE, 
				FU.LAST_POST, FU.ALLOW_POST, FU.SIGNATURE, FU.RANK_ID, 
				U.EMAIL, U.NAME, U.LAST_NAME, U.LOGIN, U.PERSONAL_BIRTHDATE, 
				".$DB->DateToCharFunction("FU.DATE_REG", "SHORT")." as DATE_REG, 
				".$DB->DateToCharFunction("FU.LAST_VISIT", "FULL")." as LAST_VISIT, 
				".$DB->DateToCharFunction("FU.LAST_VISIT", "SHORT")." as LAST_VISIT_SHORT, 
			   ".$DB->DateToCharFunction("U.DATE_REGISTER", "SHORT")." as DATE_REGISTER_SHORT, 
				U.PERSONAL_ICQ, U.PERSONAL_WWW, U.PERSONAL_PROFESSION, U.DATE_REGISTER, 
				U.PERSONAL_CITY, U.PERSONAL_COUNTRY, U.PERSONAL_PHOTO, 
				U.PERSONAL_GENDER, FU.POINTS, FU.HIDE_FROM_ONLINE, 
				".$DB->DateToCharFunction("U.PERSONAL_BIRTHDAY", "SHORT")." as PERSONAL_BIRTHDAY ";
		if (array_key_exists("SHOW_ABC", $arFilter))
			$strSql .= ", 
			CASE
		         WHEN ((FU.SHOW_NAME='Y') AND (LENGTH(TRIM(CONCAT_WS('',U.NAME,U.LAST_NAME)))>0)) THEN trim(CONCAT_WS(' ',U.NAME, U.LAST_NAME))
		         ELSE U.LOGIN
         	END AS SHOW_ABC ";
		$strSql .= 
			"FROM b_user U 
				LEFT JOIN b_forum_user FU ON (FU.USER_ID = U.ID)
			WHERE 1 = 1 
			".$strSqlSearch."
			".$strSqlOrder;
			
		if (is_array($arAddParams) && (intVal($arAddParams["nTopCount"])>0))
			$strSql .= " LIMIT 0,".intVal($arAddParams["nTopCount"]);
		
		if (is_array($arAddParams) && is_set($arAddParams, "bDescPageNumbering") && (intVal($arAddParams["nTopCount"])<=0))
		{
			$iCnt = 0;
			$strSqlCount = "
				SELECT COUNT('x') as CNT 
				FROM b_user U 
				LEFT JOIN b_forum_user FU ON (FU.USER_ID = U.ID) 
					WHERE 1 = 1 
					".$strSqlSearch;
			$db_res = $DB->Query($strSqlCount, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($db_res && ($res = $db_res->Fetch()))
				$iCnt = $res["CNT"];
					
			$db_res =  new CDBResult();
			$db_res->NavQuery($strSql, $iCnt, $arAddParams);
		}
		else 
		{
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		
		return $db_res;
	}

	function GetListEx($arOrder = Array("ID"=>"ASC"), $arFilter = Array())
	{
		global $DB;
		$arSqlSearch = array();
		$arSqlSelect = array();
		$arSqlFrom = array();
		$arSqlGroup = array();
		$arSqlOrder = array();
		$arSql = array(); 
		$strSqlSearch = "";
		$strSqlSelect = "";
		$strSqlFrom = "";
		$strSqlGroup = "";
		$strSqlOrder = "";
		$strSql = "";
		$tmp = array();
		$arFilter = (is_array($arFilter) ? $arFilter : array());

		$arMainUserFields = array("LOGIN"=>"S", "NAME"=>"S", "LAST_NAME"=>"S", "PERSONAL_PROFESSION"=>"S", "PERSONAL_WWW"=>"S", "PERSONAL_ICQ"=>"S", "PERSONAL_GENDER"=>"E", "PERSONAL_PHONE"=>"S", "PERSONAL_FAX"=>"S", "PERSONAL_MOBILE"=>"S", "PERSONAL_PAGER"=>"S", "PERSONAL_STREET"=>"S", "PERSONAL_MAILBOX"=>"S", "PERSONAL_CITY"=>"S", "PERSONAL_STATE"=>"S", "PERSONAL_ZIP"=>"S", "PERSONAL_COUNTRY"=>"I", "PERSONAL_NOTES"=>"S", "WORK_COMPANY"=>"S", "WORK_DEPARTMENT"=>"S", "WORK_POSITION"=>"S", "WORK_WWW"=>"S", "WORK_PHONE"=>"S", "WORK_FAX"=>"S", "WORK_PAGER"=>"S", "WORK_STREET"=>"S", "WORK_MAILBOX"=>"S", "WORK_CITY"=>"S", "WORK_STATE"=>"S", "WORK_ZIP"=>"S", "WORK_COUNTRY"=>"I", "WORK_PROFILE"=>"S", "WORK_NOTES"=>"S");
		$arSqlSelectConst = array(
			"FU.ID" => "FU.ID", 
			"USER_ID" => "U.ID", 
			"FU.SHOW_NAME" => "FU.SHOW_NAME", 
			"FU.DESCRIPTION" => "FU.DESCRIPTION", 
			"FU.IP_ADDRESS" => "FU.IP_ADDRESS", 
			"FU.REAL_IP_ADDRESS" => "FU.REAL_IP_ADDRESS", 
			"FU.AVATAR" => "FU.AVATAR", 
			"FU.NUM_POSTS" => "FU.NUM_POSTS", 
			"NUM_POINTS" => "FU.POINTS", 
			"FU.INTERESTS" => "FU.INTERESTS", 
			"FU.SUBSC_GROUP_MESSAGE" => "FU.SUBSC_GROUP_MESSAGE", 
			"FU.SUBSC_GET_MY_MESSAGE" => "FU.SUBSC_GET_MY_MESSAGE", 
			"FU.LAST_POST" => "FU.LAST_POST", 
			"FU.ALLOW_POST" => "FU.ALLOW_POST", 
			"FU.SIGNATURE" => "FU.SIGNATURE", 
			"FU.RANK_ID" => "FU.RANK_ID", 
			"FU.POINTS" => "FU.POINTS", 
			"FU.HIDE_FROM_ONLINE" => "FU.HIDE_FROM_ONLINE", 
			"U.DATE_REGISTER" => "U.DATE_REGISTER", 
			"U.EMAIL" => "U.EMAIL", 
			"U.NAME" => "U.NAME", 
			"U.LAST_NAME" => "U.LAST_NAME", 
			"U.LOGIN" => "U.LOGIN",
			"U.PERSONAL_BIRTHDATE" => "U.PERSONAL_BIRTHDATE", 
			"U.PERSONAL_ICQ" => "U.PERSONAL_ICQ", 
			"U.PERSONAL_WWW" => "U.PERSONAL_WWW", 
			"U.PERSONAL_PROFESSION" => "U.PERSONAL_PROFESSION", 
			"U.PERSONAL_CITY" => "U.PERSONAL_CITY", 
			"U.PERSONAL_COUNTRY" => "U.PERSONAL_COUNTRY", 
			"U.PERSONAL_PHOTO" => "U.PERSONAL_PHOTO", 
			"U.PERSONAL_GENDER" => "U.PERSONAL_GENDER",
			"DATE_REG" => $DB->DateToCharFunction("FU.DATE_REG", "SHORT"),
			"LAST_VISIT" => $DB->DateToCharFunction("FU.LAST_VISIT", "FULL"),
			"PERSONAL_BIRTHDAY" => $DB->DateToCharFunction("U.PERSONAL_BIRTHDAY", "SHORT"),
			"U.WORK_POSITION" => "U.WORK_POSITION", 
			"U.WORK_COMPANY" => "U.WORK_COMPANY" 
			);

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strToUpper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];

			switch ($key)
			{
				case "USER_ID":
					if (intVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(U.ID IS NULL OR U.ID<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" U.ID IS NULL OR NOT ":"")."(U.ID ".$strOperation." ".intVal($val)." )";
					break;
				case "ID":
				case "RANK_ID":
				case "NUM_POSTS":
					if (intVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FU.".$key." IS NULL OR FU.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FU.".$key." IS NULL OR NOT ":"")."(FU.".$key." ".$strOperation." ".intVal($val)." )";
					break;
				case "SHOW_NAME":
				case "HIDE_FROM_ONLINE":
				case "SUBSC_GROUP_MESSAGE":
				case "SUBSC_GET_MY_MESSAGE":
				case "ALLOW_POST":
					if (strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FU.".$key." IS NULL OR LENGTH(FU.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FU.".$key." IS NULL OR NOT ":"")."(FU.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "ACTIVE":
					if (strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(U.".$key." IS NULL OR LEN(U.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" U.".$key." IS NULL OR NOT ":"")."(U.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "PERSONAL_BIRTHDATE":
					if (strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(U.PERSONAL_BIRTHDATE IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" U.PERSONAL_BIRTHDATE IS NULL OR NOT ":"")."(U.PERSONAL_BIRTHDATE ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				case "PERSONAL_BIRTHDAY":
					if(strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(U.PERSONAL_BIRTHDAY IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" U.PERSONAL_BIRTHDAY IS NULL OR NOT ":"")."(U.PERSONAL_BIRTHDAY ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT").")";
					break;
				case "PERSONAL_BIRTHDAY_DATE":
					$arSqlSearch[] = ($strNegative=="Y"?" U.PERSONAL_BIRTHDAY IS NULL OR NOT ":"")."(DATE_FORMAT(U.PERSONAL_BIRTHDAY, '%m-%d') ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				case "LAST_VISIT":
					if(strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FU.LAST_VISIT IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FU.LAST_VISIT IS NULL OR NOT ":"")."(FU.LAST_VISIT ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT").")";
					break;
				case "LOGIN":
				case "EMAIL":
					$arSqlSearch[] = GetFilterQuery("U.".$key, $val);
					break;
				case "NAME":
					$arSqlSearch[] = GetFilterQuery("U.NAME, U.LAST_NAME", $val);
					break;
				case"SUBSC_NEW_TOPIC_ONLY":
					$key = "NEW_TOPIC_ONLY";
					$arSqlFrom["FS"] = "INNER JOIN b_forum_subscribe FS ON (FU.USER_ID = FS.USER_ID)";
					if (strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FS.".$key." IS NULL OR LENGTH(FS.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FS.".$key." IS NULL OR NOT ":"")."(FS.".$key." ".$strOperation." '".$DB->ForSql($val)."' )";
					break;
				case "SUBSC_START_DATE":
					$key = "START_DATE";
					$arSqlFrom["FS"] = "INNER JOIN b_forum_subscribe FS ON (FU.USER_ID = FS.USER_ID)";
					if(strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FS.".$key." IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FS.".$key." IS NULL OR NOT ":"")."(FS.".$key." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT").")";
					break;
				case "SUBSC_FORUM_ID":
				case "SUBSC_TOPIC_ID":
				case "SUBSC":
					$arSqlFrom["FS"] = "INNER JOIN b_forum_subscribe FS ON (FU.USER_ID = FS.USER_ID)";
					unset($arSqlSelectConst["FU.INTERESTS"]);
					$arSqlSelect = $arSqlSelectConst;
					$arSqlSelect["SUBSC_COUNT"] = "COUNT(FS.ID)";
					$arSqlSelect["SUBSC_START_DATE"] = $DB->DateToCharFunction("MIN(FS.START_DATE)", "FULL");
					$arSqlGroup = array_merge($arSqlSelectConst, $arSqlGroup);
					if ($key != "SUBSC")
					{
						$key = substr($key, strLen("SUBSC_"));
						if (intVal($val)<=0)
							$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FS.".$key." IS NULL OR FS.".$key."<=0)";
						else
							$arSqlSearch[] = ($strNegative=="Y"?" FS.".$key." IS NULL OR NOT ":"")."(FS.".$key." ".$strOperation." ".intVal($val)." )";
					}
					break;
				default:
					if (substr($key, 0, strLen("USER_"))=="USER_")
					{
						$strUserKey = substr($key, strLen("USER_"));
						if (array_key_exists($strUserKey, $arMainUserFields))
						{
							if ($arMainUserFields[$strUserKey]=="I")
								$arSqlSearch[] = ($strNegative=="Y"?" U.".$strUserKey." IS NULL OR NOT ":"")."(U.".$strUserKey." ".$strOperation." ".intVal($val)." )";
							elseif ($arMainUserFields[$strUserKey]=="E")
								$arSqlSearch[] = ($strNegative=="Y"?" U.".$strUserKey." IS NULL OR NOT ":"")."(U.".$strUserKey." ".$strOperation." '".$DB->ForSql($val)."' )";
							else
								$arSqlSearch[] = GetFilterQuery("U.".$strUserKey, $val);
						}
					}
			}
		}
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND (".implode(") AND (", $arSqlSearch).") ";
		if (count($arSqlSelect) <= 0)
			$arSqlSelect = $arSqlSelectConst;
		foreach ($arSqlSelect as $key => $val)
		{
			if ($val != $key)
				$tmp[] = $val." AS ".$key;
			else 
				$tmp[] = $val;
		}
		$strSqlSelect = implode(", ", $tmp);
		if (count($arSqlFrom) > 0)
			$strSqlFrom = implode("	", $arSqlFrom);
		if (count($arSqlGroup) > 0)
			$strSqlGroup = " GROUP BY ".implode(", ", $arSqlGroup);

		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			if ($order!="ASC")
				$order = "DESC";

			if ($by == "USER_ID") $arSqlOrder[] = " U.ID ".$order." ";
			elseif ($by == "SHOW_NAME") $arSqlOrder[] = " FU.SHOW_NAME ".$order." ";
			elseif ($by == "HIDE_FROM_ONLINE") $arSqlOrder[] = " FU.HIDE_FROM_ONLINE ".$order." ";
			elseif ($by == "SUBSC_GROUP_MESSAGE") $arSqlOrder[] = " FU.SUBSC_GROUP_MESSAGE ".$order." ";
			elseif ($by == "SUBSC_GET_MY_MESSAGE") $arSqlOrder[] = " FU.SUBSC_GET_MY_MESSAGE ".$order." ";
			elseif ($by == "NUM_POSTS") $arSqlOrder[] = " FU.NUM_POSTS ".$order." ";
			elseif ($by == "LAST_POST") $arSqlOrder[] = " FU.LAST_POST ".$order." ";
			elseif ($by == "POINTS") $arSqlOrder[] = " FU.POINTS ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " U.NAME ".$order." ";
			elseif ($by == "LAST_NAME") $arSqlOrder[] = " U.LAST_NAME ".$order." ";
			elseif ($by == "EMAIL") $arSqlOrder[] = " U.EMAIL ".$order." ";
			elseif ($by == "LOGIN") $arSqlOrder[] = " U.LOGIN ".$order." ";
			elseif ($by == "LAST_VISIT") $arSqlOrder[] = " FU.LAST_VISIT ".$order." ";
			elseif ($by == "DATE_REGISTER") $arSqlOrder[] = " U.DATE_REGISTER ".$order." ";
			elseif ($by == "ID") $arSqlOrder[] = " FU.ID ".$order." ";
			elseif (($by == "SUBSC_COUNT") && array_key_exists("FS", $arSqlFrom)) $arSqlOrder[] = " SUBSC_COUNT ".$order." ";
			elseif (($by == "SUBSC_START_DATE") && array_key_exists("FS", $arSqlFrom)) $arSqlOrder[] = " FS.START_DATE ".$order." ";
			elseif (substr($by, 0, strLen("USER_"))=="USER_")
			{
				$strUserBy = substr($by, strLen("USER_"));
				if (array_key_exists($strUserBy, $arMainUserFields))
				{
					$arSqlOrder[] = " U.".$strUserBy." ".$order." ";
				}
			}
			else
			{
				$arSqlOrder[] = " FU.ID ".$order." ";
				$by = "ID";
			}
		}

		DelDuplicateSort($arSqlOrder); 
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

			$strSql = "SELECT ".$strSqlSelect." 
				FROM b_forum_user FU
					INNER JOIN b_user U ON (FU.USER_ID = U.ID) 
					".$strSqlFrom."
				WHERE 1 = 1 
					".$strSqlSearch."
					".$strSqlGroup."
					".$strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function SearchUser($template)
	{
		global $DB;
		$template = $DB->ForSql(str_replace("*", "%", $template));
		
		$strSql = "
			SELECT U.ID, U.NAME, U.LAST_NAME, U.LOGIN, F.SHOW_NAME,
			CASE
		         WHEN ((F.SHOW_NAME='Y') AND (LENGTH(TRIM(CONCAT_WS('',U.NAME,U.LAST_NAME)))>0)) THEN trim(CONCAT_WS(' ',U.NAME,U.LAST_NAME))
		         ELSE U.LOGIN
         	END AS SHOW_ABC 
			FROM b_user U 
				LEFT JOIN b_forum_user F ON(F.USER_ID = U.ID) 
			WHERE ";
		if (substr($template, 0, 1) == '%')
			$strSql .= "
				(
					(F.SHOW_NAME='Y') AND (LENGTH(TRIM(CONCAT_WS('',U.NAME,U.LAST_NAME)))>0) AND 
					(CONCAT_WS(' ',U.NAME, U.LAST_NAME) LIKE '".$template."')
				)
				OR
				(
					(
						(F.SHOW_NAME='N') OR (F.SHOW_NAME='') OR (F.SHOW_NAME IS NULL) OR 
						(
							(F.SHOW_NAME='Y') AND (LENGTH(TRIM(CONCAT_WS('',U.NAME,U.LAST_NAME)))<=0) 
						)
					)
					AND	U.LOGIN LIKE '".$template."')
				";
		else 
			$strSql .= "
				(F.SHOW_NAME='Y' AND LENGTH(U.NAME)>0 AND U.NAME LIKE '".$template."')
				OR
				(F.SHOW_NAME='Y' AND LENGTH(U.NAME)<=0 AND LENGTH(U.LAST_NAME)>0 AND U.LAST_NAME LIKE '".$template."')
				OR
				(
					(
						(F.SHOW_NAME='N') OR (F.SHOW_NAME='') OR (F.SHOW_NAME IS NULL) OR 
						(
							(F.SHOW_NAME='Y') AND (LENGTH(TRIM(CONCAT_WS('',U.NAME,U.LAST_NAME)))<=0)
						)
					)
					AND	U.LOGIN LIKE '".$template."'
				)
				";
		$strSql .= "ORDER BY SHOW_ABC";		
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $dbRes;
	}
}

class CForumSubscribe extends CAllForumSubscribe
{
}

class CForumRank extends CAllForumRank
{
	// Tekuwie statusy posetitelej srazu ne pereschityvayutsya. Tol'ko postepenno v processe raboty.
	function Add($arFields)
	{
		global $DB;

		if (!CForumRank::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_forum_rank", $arFields);
		$strSql = "INSERT INTO b_forum_rank(".$arInsert[0].") VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ID = intVal($DB->LastID());
		foreach ($arFields["LANG"] as $i => $val)
		{
			$arInsert = $DB->PrepareInsert("b_forum_rank_lang", $arFields["LANG"][$i]);
			$strSql = "INSERT INTO b_forum_rank_lang(RANK_ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return $ID;
	}
}

class CForumStat extends CALLForumStat 
{
	function GetListEx($arOrder = Array("ID"=>"ASC"), $arFilter = Array())
	{
		global $DB;
		$arSqlSearch = array();
		$arSqlSelect = array();
		$arSqlFrom = array();
		$arSqlGroup = array();
		$arSqlOrder = array();
		$arSql = array(); 
		$strSqlSearch = "";
		$strSqlSelect = "";
		$strSqlFrom = "";
		$strSqlGroup = "";
		$strSqlOrder = "";
		$strSql = "";
		$arFilter = (is_array($arFilter) ? $arFilter : array());
		$arSqlSelectConst = array(
			"FSTAT.USER_ID" => "FSTAT.USER_ID", 
			"FSTAT.IPADDRES" => "FSTAT.IPADDRES", 
			"FSTAT.PHPSESSID" => "FSTAT.PHPSESSID", 
			"LAST_VISIT" => $DB->DateToCharFunction("FSTAT.LAST_VISIT", "FULL"), 
			"FSTAT.FORUM_ID" => "FSTAT.FORUM_ID",
			"FSTAT.TOPIC_ID" => "FSTAT.TOPIC_ID"
		);
		$arSqlSelect = $arSqlSelectConst;

		foreach ($arFilter as $key => $val)
		{
			$key_res = CForumNew::GetFilterOperation($key);
			$key = strtoupper($key_res["FIELD"]);
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];
			switch ($key)
			{
				case "TOPIC_ID":
				case "FORUM_ID":
					if (intVal($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FSTAT.".$key." IS NULL OR FSTAT.".$key."<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FSTAT.".$key." IS NULL OR NOT ":"")."(FSTAT.".$key." ".$strOperation." ".intVal($val).")";
					break;
				case "SITE_ID":
					if (is_array($val)):
						$bOrNull = false;
						$res = array();
						foreach ($val as $v):
							$v = trim($v);
							if ($v == "NULL")
								$bOrNull = true;
							elseif (!empty($v))
								$res[] = "'".$DB->ForSql($v)."'";
						endforeach;
						$val = (!empty($res) ? implode(", ", $res) : "");
						$strOperation = (!empty($res) ? "IN" : $strOperation);
					else: 
						$val = "'".$DB->ForSql($val)."'";
					endif;
					if (strlen($val) <= 0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FSTAT.".$key." IS NULL OR LENGTH(FSTAT.".$key.")<=0)";
					elseif ($strOperation == "IN")
						$arSqlSearch[] = ($strNegative=="Y"?" FSTAT.".$key." IS NULL OR NOT ":"")."(FSTAT.".$key." IN (".$val.")".(
							$bOrNull ? " OR (FSTAT.".$key." IS NULL OR LENGTH(FSTAT.".$key.")<=0)" : "").")";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FSTAT.".$key." IS NULL OR NOT ":"")."(FSTAT.".$key." ".$strOperation." ".$val.")";
					break;
				case "LAST_VISIT":
					if(strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FSTAT.".$key." IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FSTAT.".$key." IS NULL OR NOT ":"")."(FSTAT.".$key." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
					break;
				case "PERIOD":
					if(strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FSTAT.LAST_VISIT IS NULL)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FSTAT.LAST_VISIT IS NULL OR NOT ":"").
							"(FROM_UNIXTIME(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - ".intVal($val).") ".$strOperation."  FSTAT.LAST_VISIT)";
						break;
				case "HIDE_FROM_ONLINE":
					$arSqlFrom["FU"] = "LEFT JOIN b_forum_user FU ON (FSTAT.USER_ID=FU.USER_ID)";
					if (strLen($val)<=0)
						$arSqlSearch[] = ($strNegative=="Y"?"NOT":"")."(FU.".$key." IS NULL OR LENGTH(FU.".$key.")<=0)";
					else
						$arSqlSearch[] = ($strNegative=="Y"?" FU.".$key." IS NULL OR NOT ":"")."(((FU.".$key." ".$strOperation." '".$DB->ForSql($val)."' ) AND (FSTAT.USER_ID > 0)) OR (FSTAT.USER_ID <= 0))";
					break;
				break;
				case "COUNT_GUEST":
					$arSqlSelect = array(
						"FSTAT.USER_ID" => "FSTAT.USER_ID", 
						"FSTAT.SHOW_NAME" => "FSTAT.SHOW_NAME", 
						"COUNT_USER" => "COUNT(FSTAT.PHPSESSID) AS COUNT_USER", 
						"FU.HIDE_FROM_ONLINE" => "FU.HIDE_FROM_ONLINE", 
					);
					$arSqlFrom["FU"] = "LEFT JOIN b_forum_user FU ON (FSTAT.USER_ID=FU.USER_ID)";
					$arSqlGroup["FSTAT.USER_ID"] = "FSTAT.USER_ID";
					$arSqlGroup["FSTAT.SHOW_NAME"] = "FSTAT.SHOW_NAME";
					$arSqlGroup["FU.HIDE_FROM_ONLINE"] = "FU.HIDE_FROM_ONLINE";
					break;
				case "ACTIVE":
						$arSqlFrom["U"] = "LEFT JOIN b_user U ON (FSTAT.USER_ID=U.ID)";
						$arSqlSearch[] = ($strNegative=="Y"?" U.".$key." IS NULL OR NOT ":"")."(FSTAT.USER_ID = 0 OR U.ACTIVE = 'Y')";
					break;
			}
		}
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " AND ".implode(" AND ", $arSqlSearch)." ";
		if (count($arSqlSelect) > 0)
			$strSqlSelect = implode(", ", $arSqlSelect);
		if (count($arSqlFrom) > 0)
			$strSqlFrom = implode(" ", $arSqlFrom);
		if (count($arSqlGroup) > 0)
			$strSqlGroup = " GROUP BY ".implode(", ", $arSqlGroup);


		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by); $order = strtoupper($order);
			$order = $order!="ASC" ? $order = "DESC" : "ASC";

			if ($by == "USER_ID") $arSqlOrder[] = " FSTAT.USER_ID ".$order." ";
		}

		DelDuplicateSort($arSqlOrder); 
		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(", ", $arSqlOrder);

		$strSql = "SELECT ".$strSqlSelect."
			FROM b_forum_stat FSTAT
			".$strSqlFrom."
			WHERE 1=1 ".$strSqlSearch."
			".$strSqlGroup."
			".$strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}
}
?>