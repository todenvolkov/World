<?
class CAllBlogSmile
{
	//---------------> User insert, update, delete
	function CheckFields($ACTION, &$arFields)
	{
		if ((is_set($arFields, "SMILE_TYPE") || $ACTION=="ADD") && $arFields["SMILE_TYPE"]!="I" && $arFields["SMILE_TYPE"]!="S") return False;
		if ((is_set($arFields, "IMAGE") || $ACTION=="ADD") && strlen($arFields["IMAGE"])<=0) return False;

		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && IntVal($arFields["SORT"])<=0) $arFields["SORT"] = 150;

		if (is_set($arFields, "LANG") || $ACTION=="ADD")
		{
			for ($i = 0; $i<count($arFields["LANG"]); $i++)
			{
				if (!is_set($arFields["LANG"][$i], "LID") || strlen($arFields["LANG"][$i]["LID"])<=0) return false;
				if (!is_set($arFields["LANG"][$i], "NAME") || strlen($arFields["LANG"][$i]["NAME"])<=0) return false;
			}

			$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
			while ($arLang = $db_lang->Fetch())
			{
				$bFound = False;
				for ($i = 0; $i<count($arFields["LANG"]); $i++)
				{
					if ($arFields["LANG"][$i]["LID"]==$arLang["LID"])
						$bFound = True;
				}
				if (!$bFound) return false;
			}
		}

		return True;
	}

	function Delete($ID)
	{
		global $DB, $CACHE_MANAGER;
		$ID = IntVal($ID);

		$DB->Query("UPDATE b_blog_comment SET ICON_ID = NULL WHERE ICON_ID = ".$ID, True);

		$DB->Query("DELETE FROM b_blog_smile_lang WHERE SMILE_ID = ".$ID, True);
		$DB->Query("DELETE FROM b_blog_smile WHERE ID = ".$ID, True);
		$CACHE_MANAGER->Clean("b_blog_smile");

		return true;
	}

	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.SMILE_TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, ".
			"	FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_blog_smile FR ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function GetByIDEx($ID, $strLang)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.SMILE_TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, ".
			"	FRL.LID, FRL.NAME, FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_blog_smile FR ".
			"	LEFT JOIN b_blog_smile_lang FRL ON (FR.ID = FRL.SMILE_ID AND FRL.LID = '".$DB->ForSql($strLang)."') ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function GetLangByID($SMILE_ID, $strLang)
	{
		global $DB;

		$SMILE_ID = IntVal($SMILE_ID);
		$strSql = 
			"SELECT FRL.ID, FRL.SMILE_ID, FRL.LID, FRL.NAME ".
			"FROM b_blog_smile_lang FRL ".
			"WHERE FRL.SMILE_ID = ".$SMILE_ID." ".
			"	AND FRL.LID = '".$DB->ForSql($strLang)."' ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}
}
?>