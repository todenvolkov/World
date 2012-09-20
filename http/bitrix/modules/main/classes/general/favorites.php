<?
IncludeModuleLangFile(__FILE__);

class CFavorites
{
	function err_mess()
	{
		return "<br>Class: CFavorites<br>File: ".__FILE__;
	}

	function GetByID($ID)
	{
		global $DB;
		$ID = intval($ID);
		if($ID<=0)
			return false;
		return ($DB->Query("
			SELECT F.*,
				".$DB->DateToCharFunction("F.TIMESTAMP_X")." as TIMESTAMP_X,
				".$DB->DateToCharFunction("F.DATE_CREATE")." as	DATE_CREATE
			FROM b_favorite F
			WHERE ID=".$ID,
			false, "File: ".__FILE__."<br>Line: ".__LINE__));
	}

	function GetList($aSort=array(), $arFilter=Array())
	{
		$err_mess = (CFavorites::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB, $USER;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for ($i=0; $i<count($filter_keys); $i++)
			{
				$val = $arFilter[$filter_keys[$i]];
				if (strlen($val)<=0 || $val=="NOT_REF") continue;
				switch(strtoupper($filter_keys[$i]))
				{
				case "ID":
					$arSqlSearch[] = GetFilterQuery("F.ID",$val,"N");
					break;
				case "USER_ID":
					$arSqlSearch[] = "F.USER_ID = ".intval($val);
					break;
				case "MENU_FOR_USER":
					$arSqlSearch[] = "(F.USER_ID=".intval($val)." OR F.COMMON='Y')";
					break;
				case "COMMON":
					$arSqlSearch[] = "F.COMMON = '".$DB->ForSql($val,1)."'";
					break;
				case "LANGUAGE_ID":
					$arSqlSearch[] = "F.LANGUAGE_ID = '".$DB->ForSql($val,2)."'";
					break;
				case "DATE1":
					$arSqlSearch[] = "F.TIMESTAMP_X >= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y"),"d.m.Y")."')";
					break;
				case "DATE2":
					$arSqlSearch[] = "F.TIMESTAMP_X <= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y")." 23:59:59","d.m.Y")."')";
					break;
				case "MODIFIED":
					$arSqlSearch[] = GetFilterQuery("UM.ID, UM.LOGIN, UM.LAST_NAME, UM.NAME", $val);
					break;
				case "MODIFIED_ID":
					$arSqlSearch[] = "F.MODIFIED_BY = ".intval($val);
					break;
				case "CREATED":
					$arSqlSearch[] = GetFilterQuery("UC.ID, UC.LOGIN, UC.LAST_NAME, UC.NAME", $val);
					break;
				case "CREATED_ID":
					$arSqlSearch[] = "F.CREATED_BY = ".intval($val);
					break;
				case "KEYWORDS":
					$arSqlSearch[] = GetFilterQuery("F.COMMENTS", $val);
					break;
				case "NAME":
					$arSqlSearch[] = GetFilterQuery("F.NAME", $val);
					break;
				case "URL":
					$arSqlSearch[] = GetFilterQuery("F.URL", $val);
					break;
				case "MODULE_ID":
					$arSqlSearch[] = "F.MODULE_ID='".$DB->ForSql($val,50)."'";
					break;
				}
			}
		}

		$sOrder = "";
		foreach($aSort as $key=>$val)
		{
			$ord = (strtoupper($val) <> "ASC"? "DESC":"ASC");
			switch (strtoupper($key))
			{
				case "ID":		$sOrder .= ", F.ID ".$ord; break;
				case "LANGUAGE_ID":	$sOrder .= ", F.LANGUAGE_ID ".$ord; break;
				case "COMMON":	$sOrder .= ", F.COMMON ".$ord; break;
				case "USER_ID":	$sOrder .= ", F.USER_ID ".$ord; break;
				case "TIMESTAMP_X":	$sOrder .= ", F.TIMESTAMP_X ".$ord; break;
				case "MODIFIED_BY":	$sOrder .= ", F.MODIFIED_BY ".$ord; break;
				case "NAME":	$sOrder .= ", F.NAME ".$ord; break;
				case "URL":	$sOrder .= ", F.URL ".$ord; break;
				case "SORT":		$sOrder .= ", F.C_SORT ".$ord; break;
				case "MODULE_ID":		$sOrder .= ", F.MODULE_ID ".$ord; break;
			}
		}
		if (strlen($sOrder)<=0)
			$sOrder = "F.ID DESC";
		$strSqlOrder = " ORDER BY ".TrimEx($sOrder,",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				F.ID, F.C_SORT, F.NAME, F.URL, F.MODIFIED_BY, F.CREATED_BY, F.MODULE_ID, F.LANGUAGE_ID,
				F.COMMENTS, F.COMMON, F.USER_ID, UM.LOGIN AS M_LOGIN, UC.LOGIN as C_LOGIN, U.LOGIN,
				".$DB->DateToCharFunction("F.TIMESTAMP_X")."	TIMESTAMP_X,
				".$DB->DateToCharFunction("F.DATE_CREATE")."	DATE_CREATE,
				".$DB->Concat($DB->IsNull("UM.NAME", "''"), "' '", $DB->IsNull("UM.LAST_NAME", "''"))." as M_USER_NAME,
				".$DB->Concat($DB->IsNull("UC.NAME", "''"), "' '", $DB->IsNull("UC.LAST_NAME", "''"))." as C_USER_NAME,
				".$DB->Concat($DB->IsNull("U.NAME", "''"), "' '", $DB->IsNull("U.LAST_NAME", "''"))." as USER_NAME
			FROM
				b_favorite F
				LEFT JOIN b_user UM ON (UM.ID = F.MODIFIED_BY)
				LEFT JOIN b_user UC ON (UC.ID = F.CREATED_BY)
				LEFT JOIN b_user U ON (U.ID = F.USER_ID)
			WHERE
			".$strSqlSearch."
			".$strSqlOrder;

		//echo "<pre>".$strSql."</pre>";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	function CheckFields($arFields) // проверка на наличие обязательных полей
	{
		$aMsg = array();
		if(is_set($arFields, "NAME") && trim($arFields["NAME"])=="")
			$aMsg[] = array("id"=>"NAME", "text"=>GetMessage("fav_general_err_name"));
		if(is_set($arFields, "URL") && trim($arFields["URL"])=="")
			$aMsg[] = array("id"=>"URL", "text"=>GetMessage("fav_general_err_url"));
		if(is_set($arFields, "USER_ID"))
		{
			if(intval($arFields["USER_ID"]) > 0)
			{
				$res = CUser::GetByID(intval($arFields["USER_ID"]));
				if(!$res->Fetch())
					$aMsg[] = array("id"=>"USER_ID", "text"=>GetMessage("fav_general_err_user"));
			}
			elseif($arFields["COMMON"] == "N")
				$aMsg[] = array("id"=>"USER_ID", "text"=>GetMessage("fav_general_err_user1"));
		}
		if(is_set($arFields, "LANGUAGE_ID"))
		{
			if($arFields["LANGUAGE_ID"] <> "")
			{
				$res = CLanguage::GetByID($arFields["LANGUAGE_ID"]);
				if(!$res->Fetch())
					$aMsg[] = array("id"=>"LANGUAGE_ID", "text"=>GetMessage("fav_general_err_lang"));
			}
			else
				$aMsg[] = array("id"=>"LANGUAGE_ID", "text"=>GetMessage("fav_general_err_lang1"));
		}

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		return true;
	}

	//Addition
	function Add($arFields)
	{
		global $DB;

		if(!CFavorites::CheckFields($arFields))
			return false;

		$ID = $DB->Add("b_favorite", $arFields);
		return $ID;
	}

	//Update
	function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);

		if(!CFavorites::CheckFields($arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_favorite", $arFields);
		if($strUpdate!="")
		{
			$strSql = "UPDATE b_favorite SET ".$strUpdate." WHERE ID=".$ID;
			if(!$DB->Query($strSql))
				return false;
		}
		return true;
	}

	// delete by ID
	function Delete($ID)
	{
		global $DB;
		return ($DB->Query("DELETE FROM b_favorite WHERE ID='".intval($ID)."'", false, "File: ".__FILE__."<br>Line: ".__LINE__));
	}

	//*****************************
	// Events
	//*****************************

	//user deletion event
	function OnUserDelete($user_id)
	{
		global $DB;
		return ($DB->Query("DELETE FROM b_favorite WHERE USER_ID=". intval($user_id), false, "File: ".__FILE__."<br>Line: ".__LINE__));
	}

	//interface language delete event
	function OnLanguageDelete($language_id)
	{
		global $DB;
		return ($DB->Query("DELETE FROM b_favorite WHERE LANGUAGE_ID='".$DB->ForSQL($language_id, 2)."'", false, "File: ".__FILE__."<br>Line: ".__LINE__));
	}
}

global $__USER_OPTIONS_CACHE;
$__USER_OPTIONS_CACHE = array();
class CUserOptions
{
	function GetList($arOrder = array("ID" => "ASC"), $arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();

		$filter_keys = Array_Keys($arFilter);
		for ($i = 0; $i < Count($filter_keys); $i++)
		{
			$val = $arFilter[$filter_keys[$i]];
			$key = StrToUpper($filter_keys[$i]);
			switch ($key)
			{
				case "ID":
					$arSqlSearch[] = "UO.ID = ".IntVal($val);
					break;
				case "USER_ID":
					$arSqlSearch[] = "UO.USER_ID = ".IntVal($val);
					break;
				case "USER_ID_EXT":
					$arSqlSearch[] = "(UO.USER_ID = ".IntVal($val)." OR UO.COMMON='Y')";
					break;
				case "CATEGORY":
					$arSqlSearch[] = "UO.CATEGORY = '".$DB->ForSql($val)."'";
					break;
				case "NAME":
					$arSqlSearch[] = "UO.NAME = '".$DB->ForSql($val)."'";
					break;
				case "COMMON":
					$arSqlSearch[] = "UO.COMMON = '".$DB->ForSql($val)."'";
					break;
			}
		}

		$strSqlSearch = "";
		for ($i = 0; $i < Count($arSqlSearch); $i++)
			if (StrLen($arSqlSearch[$i]) > 0)
				$strSqlSearch .= " AND  (".$arSqlSearch[$i].") ";

		$strSql =
			"SELECT UO.ID, UO.USER_ID, UO.CATEGORY, UO.NAME, UO.COMMON, UO.VALUE ".
			"FROM b_user_option UO ".
			"WHERE 1 = 1 ".
			$strSqlSearch;

		$arSqlOrder = array();
		if (Is_Array($arOrder))
		{
			foreach ($arOrder as $by => $order)
			{
				$by = StrToUpper($by);
				$order = StrToUpper($order);
				if ($order != "ASC")
					$order = "DESC";

				if ($by == "ID")
					$arSqlOrder[$by] = " UO.ID ".$order." ";
				elseif ($by == "USER_ID")
					$arSqlOrder[$by] = " UO.USER_ID ".$order." ";
				elseif ($by == "CATEGORY")
					$arSqlOrder[$by] = " UO.CATEGORY ".$order." ";
				elseif ($by == "NAME")
					$arSqlOrder[$by] = " UO.NAME ".$order." ";
				elseif ($by == "COMMON")
					$arSqlOrder[$by] = " UO.COMMON ".$order." ";
			}
		}

		if (count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(",", $arSqlOrder);
		else
			$strSqlOrder = "";

		$res = $DB->Query($strSql.$strSqlOrder, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	function GetOption($category, $name, $default_value=false, $user_id=false)
	{
		global $DB, $USER, $__USER_OPTIONS_CACHE;

		if($user_id === false)
			$user_id = $USER->GetID();
		$user_id = intval($user_id);

		if(!isset($__USER_OPTIONS_CACHE[$user_id]))
		{
			//user (or default) options
			$res = $DB->Query(
				"SELECT CATEGORY, NAME, VALUE, COMMON FROM b_user_option ".
				"WHERE (USER_ID=".$user_id." OR USER_ID IS NULL AND COMMON='Y') ");

			while($res_array = $res->Fetch())
			{
				$row_cache_key = $res_array["CATEGORY"].".".$res_array["NAME"];
				if(!isset($__USER_OPTIONS_CACHE[$user_id][$row_cache_key]) || $res_array["COMMON"] <> 'Y')
					$__USER_OPTIONS_CACHE[$user_id][$row_cache_key] = unserialize($res_array["VALUE"]);
			}
		}

		$cache_key = $category.".".$name;
		if(!isset($__USER_OPTIONS_CACHE[$user_id][$cache_key]))
			$__USER_OPTIONS_CACHE[$user_id][$cache_key] = $default_value;

		return $__USER_OPTIONS_CACHE[$user_id][$cache_key];
	}

	function SetOption($category, $name, $value, $bCommon=false, $user_id=false)
	{
		global $DB, $USER, $__USER_OPTIONS_CACHE;

		if($user_id === false)
			$user_id = $USER->GetID();
		$user_id = intval($user_id);

		$arFields = array(
			"USER_ID"=>($bCommon? false:$user_id),
			"CATEGORY"=>$category,
			"NAME"=>$name,
			"VALUE"=>serialize($value),
			"COMMON"=>($bCommon? "Y":"N"),
		);
		$res = $DB->Query(
			"SELECT ID FROM b_user_option ".
			"WHERE ".
			($bCommon? "USER_ID IS NULL AND COMMON='Y' ":"USER_ID=".$user_id).
			"	AND CATEGORY='".$DB->ForSql($category, 50)."' ".
			"	AND NAME='".$DB->ForSql($name, 255)."'");
		if($res_array = $res->Fetch())
		{
			$strUpdate = $DB->PrepareUpdate("b_user_option", $arFields);
			if($strUpdate!="")
			{
				$strSql = "UPDATE b_user_option SET ".$strUpdate." WHERE ID=".$res_array["ID"];
				if(!$DB->QueryBind($strSql, array("VALUE"=>$arFields["VALUE"])))
					return false;
			}
		}
		else
		{
			if(!$DB->Add("b_user_option", $arFields, array("VALUE")))
				return false;
		}
		$__USER_OPTIONS_CACHE = array();
		return true;
	}

	function SetOptionsFromArray($aOptions)
	{
		foreach($aOptions as $opt)
		{
			if($opt["c"] <> "" && $opt["n"] <> "")
			{
				$val = $opt["v"];
				if(is_array($opt["v"]))
				{
					$val = CUserOptions::GetOption($opt["c"], $opt["n"], array());
					foreach($opt["v"] as $k=>$v)
						$val[$k] = $v;
				}
				CUserOptions::SetOption($opt["c"], $opt["n"], $val);
				if($opt["d"] == "Y" && $GLOBALS["USER"]->CanDoOperation('edit_other_settings'))
					CUserOptions::SetOption($opt["c"], $opt["n"], $val, true);
			}
		}
	}

	function DeleteOption($category, $name, $bCommon=false, $user_id=false)
	{
		global $DB, $USER, $__USER_OPTIONS_CACHE;

		if($user_id === false)
			$user_id = $USER->GetID();
		$user_id = intval($user_id);

		$strSql =
			"DELETE FROM b_user_option ".
			"WHERE ".($bCommon? "USER_ID IS NULL AND COMMON='Y' ":"USER_ID=".$user_id).
			"	AND CATEGORY='".$DB->ForSql($category, 50)."' ".
			"	AND NAME='".$DB->ForSql($name, 255)."'";
		if($DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
		{
			$__USER_OPTIONS_CACHE = array();
			return true;
		}
		return false;
	}

	function DeleteCommonOptions()
	{
		global $DB, $__USER_OPTIONS_CACHE;
		if($DB->Query("DELETE FROM b_user_option WHERE COMMON='Y' AND NAME NOT LIKE '~%'", false, "File: ".__FILE__."<br>Line: ".__LINE__))
		{
			$__USER_OPTIONS_CACHE = array();
			return true;
		}
		return false;
	}

	function DeleteUsersOptions($user_id=false)
	{
		global $DB, $__USER_OPTIONS_CACHE;
		if($DB->Query("DELETE FROM b_user_option WHERE USER_ID IS NOT NULL AND NAME NOT LIKE '~%'  ".($user_id <> false? " AND USER_ID=".intval($user_id):""), false, "File: ".__FILE__."<br>Line: ".__LINE__))
		{
			$__USER_OPTIONS_CACHE = array();
			return true;
		}
		return false;
	}

	//*****************************
	// Events
	//*****************************

	//user deletion event
	function OnUserDelete($user_id)
	{
		global $DB;
		return ($DB->Query("DELETE FROM b_user_option WHERE USER_ID=". intval($user_id), false, "File: ".__FILE__."<br>Line: ".__LINE__));
	}
}
?>