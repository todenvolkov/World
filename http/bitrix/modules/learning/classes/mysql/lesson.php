<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/classes/general/lesson.php");

class CLesson extends CAllLesson
{

	function GetList($arOrder=Array(), $arFilter=Array())
	{
		global $DB, $USER, $APPLICATION;

		$arSqlSearch = CLesson::GetFilter($arFilter);

		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
			if(strlen($arSqlSearch[$i])>0)
				$strSqlSearch .= " AND ".$arSqlSearch[$i]." ";


		//$BlockMinPerm = false;
		//if ($arFilter["CHECK_PERMISSIONS"] == "Y" && !$USER->IsAdmin())
				//$BlockMinPerm = (strlen($arFilter["MIN_PERMISSION"])==1 ? $arFilter["MIN_PERMISSION"] : "R");

		$bCheckPerm = ($APPLICATION->GetUserRight("learning") < "W" && !$USER->IsAdmin() && $arFilter["CHECK_PERMISSIONS"] != "N");

		$strSql =
		"SELECT DISTINCT CL.*, CH.NAME as CHAPTER_NAME, ".
		$DB->DateToCharFunction("CL.TIMESTAMP_X")." as TIMESTAMP_X, ".
		$DB->DateToCharFunction("CL.DATE_CREATE")." as DATE_CREATE, ".
		$DB->Concat("'('",'UC.LOGIN',"') '",'UC.NAME',"' '", 'UC.LAST_NAME')." as CREATED_USER_NAME ".
		//"concat('(',UC.LOGIN,') ',UC.NAME,' ',UC.LAST_NAME) as CREATED_USER_NAME ".
		"FROM b_learn_lesson CL ".
		"INNER JOIN b_learn_course C ON CL.COURSE_ID = C.ID ".
		"LEFT JOIN b_learn_chapter CH ON CL.CHAPTER_ID = CH.ID ".
		($bCheckPerm ? "LEFT JOIN b_learn_course_permission CP ON CP.COURSE_ID = C.ID " : "").
		"LEFT JOIN b_user UC ON UC.ID = CL.CREATED_BY ".
		"WHERE 1=1 ".
		($bCheckPerm?
		"AND CP.USER_GROUP_ID IN (".$USER->GetGroups().") ".
		"AND CP.PERMISSION >= '".(strlen($arFilter["MIN_PERMISSION"])==1 ? $arFilter["MIN_PERMISSION"] : "R")."' ".
		"AND (CP.PERMISSION='X' OR C.ACTIVE='Y')"
		:"").
		$strSqlSearch;


		if (!is_array($arOrder))
			$arOrder = Array();

		foreach($arOrder as $by=>$order)
		{
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order!="asc")
				$order = "desc";

			if ($by == "id") $arSqlOrder[] = " CL.ID ".$order." ";
			elseif ($by == "name") $arSqlOrder[] = " CL.NAME ".$order." ";
			elseif ($by == "active") $arSqlOrder[] = " CL.ACTIVE ".$order." ";
			elseif ($by == "sort") $arSqlOrder[] = " CL.SORT ".$order." ";
			elseif ($by == "created" || $by == "date_create") $arSqlOrder[] = " CL.DATE_CREATE ".$order." ";
			elseif ($by == "created_by") $arSqlOrder[] = " CL.CREATED_BY ".$order." ";
			elseif ($by == "chapter_name") $arSqlOrder[] = " CH.NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " CL.TIMESTAMP_X ".$order." ";
				$by = "timestamp_x";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		for ($i=0; $i<count($arSqlOrder); $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;

		//echo $strSql;

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	function DoInsert($arInsert, $arFields)
	{
		global $DB, $USER;

		$user_id = intval($USER->GetID());
		$strSql =
			"INSERT INTO b_learn_lesson(".
				(!is_set($arFields, "DATE_CREATE")?"DATE_CREATE, ":"").
				($user_id>0 && !is_set($arFields, "CREATED_BY")?"CREATED_BY, ":"").
				$arInsert[0].") ".
			"VALUES(".
				(!is_set($arFields, "DATE_CREATE")?$DB->CurrentTimeFunction().", ":"").
				($user_id>0 && !is_set($arFields, "CREATED_BY")?$user_id.", ":"").
				$arInsert[1].")";

		if($DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return $DB->LastID();

		return false;
	}

}

?>