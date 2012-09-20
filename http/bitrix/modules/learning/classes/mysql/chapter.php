<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/classes/general/chapter.php");

class CChapter extends CAllChapter
{

	function GetList($arOrder=Array(), $arFilter=Array(), $bIncCnt = false)
	{
		global $DB, $USER, $APPLICATION;

		$arSqlSearch = CChapter::GetFilter($arFilter);


		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
			if(strlen($arSqlSearch[$i])>0)
				$strSqlSearch .= " AND ".$arSqlSearch[$i]." ";

		$bCheckPerm = ($APPLICATION->GetUserRight("learning") < "W" && !$USER->IsAdmin() && $arFilter["CHECK_PERMISSIONS"] != "N");

		if (!$bIncCnt)
		{
			$strSql =
				"SELECT DISTINCT CH.*, ".
				$DB->DateToCharFunction("CH.TIMESTAMP_X")." as TIMESTAMP_X ".
				"FROM b_learn_chapter CH ".
				"INNER JOIN b_learn_course C ON CH.COURSE_ID = C.ID ".
				($bCheckPerm ? "LEFT JOIN b_learn_course_permission CP ON CP.COURSE_ID = C.ID " : "").
				"WHERE 1=1 ".
				($bCheckPerm ?
				"AND CP.USER_GROUP_ID IN (".$USER->GetGroups().") ".
				"AND CP.PERMISSION >= '".(strlen($arFilter["MIN_PERMISSION"])==1 ? $arFilter["MIN_PERMISSION"] : "R")."' ".
				"AND (CP.PERMISSION='X' OR C.ACTIVE='Y')"
				:"").
				$strSqlSearch;
		}
		else
		{
			$strSql =
				"SELECT DISTINCT CH.*, ".
				$DB->DateToCharFunction("CH.TIMESTAMP_X")." as TIMESTAMP_X, ".
				"COUNT(DISTINCT CL.ID) as ELEMENT_CNT ".
				"FROM b_learn_chapter CH ".
				"INNER JOIN b_learn_course C ON CH.COURSE_ID = C.ID ".
				($bCheckPerm ? "LEFT JOIN b_learn_course_permission CP ON CP.COURSE_ID = C.ID " : "").
				"LEFT JOIN b_learn_lesson CL ON CL.CHAPTER_ID = CH.ID ".
				"WHERE 1=1 ".
				($arFilter["CNT_ACTIVE"]=="Y"?
				"AND CL.ACTIVE='Y' "
				:"").
				($bCheckPerm ?
				"AND CP.USER_GROUP_ID IN (".$USER->GetGroups().") ".
				"AND CP.PERMISSION >= '".(strlen($arFilter["MIN_PERMISSION"])==1 ? $arFilter["MIN_PERMISSION"] : "R")."' ".
				"AND (CP.PERMISSION='X' OR C.ACTIVE='Y')"
				:"").
				$strSqlSearch.
				"GROUP BY CH.ID ";
		}

		if (!is_array($arOrder))
			$arOrder = Array();

		foreach($arOrder as $by=>$order)
		{
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order!="asc")
				$order = "desc";

			if ($by == "id")						$arSqlOrder[] = " CH.ID ".$order." ";
			elseif ($by == "name")			$arSqlOrder[] = " CH.NAME ".$order." ";
			elseif ($by == "active")			$arSqlOrder[] = " CH.ACTIVE ".$order." ";
			elseif ($by == "sort")				$arSqlOrder[] = " CH.SORT ".$order." ";
			else
			{
				$arSqlOrder[] = " CH.TIMESTAMP_X ".$order." ";
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


}

?>