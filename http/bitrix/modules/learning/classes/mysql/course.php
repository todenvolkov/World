<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/classes/general/course.php");

class CCourse extends CAllCourse
{

	function GetList($arOrder=Array(), $arFilter=Array(), $bIncCnt = false)
	{
		global $DB, $USER, $APPLICATION;

		if (!is_array($arFilter))
			$arFilter = Array();

		$arSqlSearch = Array();

		foreach ($arFilter as $key => $val)
		{
			$res = CCourse::MkOperationFilter($key);
			$key = $res["FIELD"];
			$cOperationType = $res["OPERATION"];

			$key = strtoupper($key);

			switch ($key)
			{

				case "ID":
				case "SORT":
					$arSqlSearch[] = CCourse::FilterCreate("C.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "NAME":
				case "CODE":
				case "DESCRIPTION":
					$arSqlSearch[] = CCourse::FilterCreate("C.".$key, $val, "string", $bFullJoin, $cOperationType);
					break;

				case "ACTIVE":
					$arSqlSearch[] = CCourse::FilterCreate("C.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;

				case "SITE_ID":
					$arSqlSearch[] = CCourse::FilterCreate("CS.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;
				case "TIMESTAMP_X":
					$arSqlSearch[] = CCourse::FilterCreate("C.".$key, $val, "date", $bFullJoin, $cOperationType);
					break;

				case "ACTIVE_FROM":
					if (strlen($val)>0)
						$arSqlSearch[] = "(C.ACTIVE_FROM ".($cOperationType=="N"?"<":">=").$DB->CharToDateFunction($DB->ForSql($val), "FULL").($cOperationType=="N"?"":" OR C.ACTIVE_FROM IS NULL").")";
					break;

				case "ACTIVE_TO":
					if(strlen($val)>0)
						$arSqlSearch[] = "(C.ACTIVE_TO ".($cOperationType=="N"?">":"<=").$DB->CharToDateFunction($DB->ForSql($val), "FULL").($cOperationType=="N"?"":" OR C.ACTIVE_TO IS NULL").")";
					break;

				case "ACTIVE_DATE":
					if(strlen($val)>0)
						$arSqlSearch[] = ($cOperationType=="N"?" NOT":"")."((C.ACTIVE_TO >= ".$DB->GetNowFunction()." OR C.ACTIVE_TO IS NULL) AND (C.ACTIVE_FROM <= ".$DB->GetNowFunction()." OR C.ACTIVE_FROM IS NULL))";
					break;

				case "DATE_ACTIVE_FROM":
					$arSqlSearch[] = CCourse::FilterCreate("C.ACTIVE_FROM", $val, "date", $bFullJoin, $cOperationType);
					break;

				case "DATE_ACTIVE_TO":
					$arSqlSearch[] = CCourse::FilterCreate("C.ACTIVE_TO", $val, "date", $bFullJoin, $cOperationType);
					break;
			}
		}

		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
			if(strlen($arSqlSearch[$i])>0)
				$strSqlSearch .= " AND ".$arSqlSearch[$i]." ";


		$bCheckPerm = ($APPLICATION->GetGroupRight("learning") < "W" && $arFilter["CHECK_PERMISSIONS"] != "N");

		if (!$bIncCnt)
		{
			$strSql =
				"SELECT DISTINCT C.*, ".
				$DB->DateToCharFunction("C.TIMESTAMP_X")." as TIMESTAMP_X, ".

				"IF(EXTRACT(HOUR_SECOND FROM C.ACTIVE_FROM)>0, ".
				$DB->DateToCharFunction("C.ACTIVE_FROM", "FULL").", ".
				$DB->DateToCharFunction("C.ACTIVE_FROM", "SHORT").") as ACTIVE_FROM, ".

				"IF(EXTRACT(HOUR_SECOND FROM C.ACTIVE_TO)>0, ".
				$DB->DateToCharFunction("C.ACTIVE_TO", "FULL").", ".
				$DB->DateToCharFunction("C.ACTIVE_TO", "SHORT").") as ACTIVE_TO ".

				"FROM b_learn_course C ".
				"LEFT JOIN b_learn_course_site CS ON C.ID = CS.COURSE_ID ".
				($bCheckPerm ? "LEFT JOIN b_learn_course_permission CP ON CP.COURSE_ID = C.ID " : "").

				"WHERE 1 = 1 ".

				(!$bCheckPerm?"":
				"AND CP.USER_GROUP_ID IN (".$USER->GetGroups().") ".
				"AND CP.PERMISSION >= '".(strlen($arFilter["MIN_PERMISSION"])==1 ? $arFilter["MIN_PERMISSION"] : "R")."' ".
				"AND (CP.PERMISSION='X' OR C.ACTIVE='Y')"
				).
				$strSqlSearch;
		}
		else
		{
			$strSql =
				"SELECT C.*, ".
				"COUNT(DISTINCT CL.ID) as ELEMENT_CNT, ".
				$DB->DateToCharFunction("C.TIMESTAMP_X")." as TIMESTAMP_X, ".

				"IF(EXTRACT(HOUR_SECOND FROM C.ACTIVE_FROM)>0, ".
				$DB->DateToCharFunction("C.ACTIVE_FROM", "FULL").", ".
				$DB->DateToCharFunction("C.ACTIVE_FROM", "SHORT").") as ACTIVE_FROM, ".

				"IF(EXTRACT(HOUR_SECOND FROM C.ACTIVE_TO)>0, ".
				$DB->DateToCharFunction("C.ACTIVE_TO", "FULL").", ".
				$DB->DateToCharFunction("C.ACTIVE_TO", "SHORT").") as ACTIVE_TO ".

				"FROM b_learn_course C ".
				"LEFT JOIN b_learn_course_site CS ON C.ID = CS.COURSE_ID ".
				($bCheckPerm ? "LEFT JOIN b_learn_course_permission CP ON CP.COURSE_ID = C.ID " : "").
				"LEFT JOIN b_learn_lesson CL ON CL.COURSE_ID = C.ID ".
				"WHERE 1 = 1 ".
				($arFilter["CNT_ACTIVE"]=="Y"?
				"AND CL.ACTIVE='Y' "
				:"").
				(!$bCheckPerm?"":
				"AND CP.USER_GROUP_ID IN (".$USER->GetGroups().") ".
				"AND CP.PERMISSION >= '".(strlen($arFilter["MIN_PERMISSION"])==1 ? $arFilter["MIN_PERMISSION"] : "R")."' ".
				"AND (CP.PERMISSION='X' OR C.ACTIVE='Y')"
				).
				$strSqlSearch.
				"GROUP BY C.ID ";
		}


		if (!is_array($arOrder))
			$arOrder = Array();

		foreach($arOrder as $by=>$order)
		{
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order!="asc")
				$order = "desc";

			if ($by == "id")						$arSqlOrder[] = " C.ID ".$order." ";
			elseif ($by == "name")			$arSqlOrder[] = " C.NAME ".$order." ";
			elseif ($by == "active")			$arSqlOrder[] = " C.ACTIVE ".$order." ";
			elseif ($by == "sort")				$arSqlOrder[] = " C.SORT ".$order." ";
			else
			{
				$arSqlOrder[] = " C.TIMESTAMP_X ".$order." ";
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

		//	echo $strSql;

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}


	function _Upper($str)
	{
		return $str;
	}

}
?>