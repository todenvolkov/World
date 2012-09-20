<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/classes/general/attempt.php");

class CTestAttempt extends CAllTestAttempt
{
	function DoInsert($arInsert, $arFields)
	{
		global $DB;

		if (strlen($arInsert[0]) <= 0 || strlen($arInsert[0])<= 0)
			return false;

		$strSql =
			"INSERT INTO b_learn_attempt(DATE_START, ".$arInsert[0].") ".
			"VALUES(".$DB->CurrentTimeFunction().", ".$arInsert[1].")";

		if($DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return $DB->LastID();

		return false;
	}


	function CreateAttemptQuestions($ATTEMPT_ID)
	{
		global $APPLICATION, $DB;

		$ATTEMPT_ID = intval($ATTEMPT_ID);

		$attempt = CTestAttempt::GetByID($ATTEMPT_ID);
		if (!$arAttempt = $attempt->Fetch())
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_ATTEMPT_ID_EX"), "ERROR_NO_ATTEMPT_ID");die;
			return false;
		}

		$test = CTest::GetByID($arAttempt["TEST_ID"]);
		if (!$arTest = $test->Fetch())
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_ID_EX"), "ERROR_NO_TEST_ID");
			return false;
		}

		$strSql = "DELETE FROM b_learn_test_result WHERE ATTEMPT_ID = ".$ATTEMPT_ID;
		if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		if ($arTest["QUESTIONS_FROM"] == "C" || $arTest["QUESTIONS_FROM"] == "L")
		{
			if ($arTest["QUESTIONS_FROM"] == "C")
			{
				$strSql =
				"SELECT Q.ID as QUESTION_ID, C.ID as FROM_ID ".
				"FROM b_learn_lesson L ".
				"INNER JOIN b_learn_question Q ON L.ID = Q.LESSON_ID ".
				"LEFT JOIN b_learn_chapter C ON C.ID = L.CHAPTER_ID ".
				"WHERE L.COURSE_ID = '".$arTest["COURSE_ID"]."' ".
				"AND L.CHAPTER_ID IS NOT NULL AND Q.ACTIVE = 'Y' ".
				($arTest["INCLUDE_SELF_TEST"] != "Y" ? "AND Q.SELF = 'N' " : "").
				"ORDER BY ".($arTest["RANDOM_QUESTIONS"] == "Y" ? CTest::GetRandFunction() : "C.SORT, L.SORT, Q.SORT");
			}
			else
			{
				$strSql =
				"SELECT Q.ID as QUESTION_ID, L.ID as FROM_ID ".
				"FROM b_learn_lesson L ".
				"INNER JOIN b_learn_question Q ON L.ID = Q.LESSON_ID ".
				"WHERE L.COURSE_ID = '".$arTest["COURSE_ID"]."' AND Q.ACTIVE = 'Y' ".
				($arTest["INCLUDE_SELF_TEST"] != "Y" ? "AND Q.SELF = 'N' " : "").
				"ORDER BY ".($arTest["RANDOM_QUESTIONS"] == "Y" ? CTest::GetRandFunction() : "L.SORT, Q.SORT");
			}

			if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
				return false;

			$Values = "";
			$tmp = Array();
			while ($arRecord = $res->Fetch())
			{
				if (is_set($tmp, $arRecord["FROM_ID"]))
				{
					if ($tmp[$arRecord["FROM_ID"]] < $arTest["QUESTIONS_AMOUNT"])
						$tmp[$arRecord["FROM_ID"]]++;
					else
						continue;
				}
				else
				{
					$tmp[$arRecord["FROM_ID"]] = 1;
				}
				$Values .= "(".$ATTEMPT_ID.",".$arRecord["QUESTION_ID"]."),";
			}

			if ($Values == "")
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_IS_EMPTY"), "ERROR_TEST_IS_EMPTY");
				return false;
			}
			$Values = substr($Values, 0, -1);
			$strSql = "INSERT INTO b_learn_test_result (ATTEMPT_ID, QUESTION_ID) VALUES ".$Values;

			//echo $strSql;
			if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
				return false;
		}
		elseif ($arTest["QUESTIONS_FROM"] == "H" || $arTest["QUESTIONS_FROM"] == "S")
		{
			$lessonIDs = array(-1);
			if ($arTest["QUESTIONS_FROM"] == "H")
			{
				$rsChapters = CChapter::GetTreeList($arTest["COURSE_ID"], $arTest["QUESTIONS_FROM_ID"]);
				$chapterIDs = array($arTest["QUESTIONS_FROM_ID"]);
				while($chapter = $rsChapters->GetNext())
				{
					$chapterIDs[] = $chapter["ID"];
				}
				$rsLessons = CLesson::GetList(array(), array("CHAPTER_ID" => $chapterIDs));
				while($lesson = $rsLessons->GetNext())
				{
					$lessonIDs[] = $lesson["ID"];
				}
			}
			else
			{
				$lessonIDs[] = $arTest["QUESTIONS_FROM_ID"];
			}

			$strSql =
			"INSERT INTO b_learn_test_result (ATTEMPT_ID, QUESTION_ID) ".
			"SELECT ".$ATTEMPT_ID." ,Q.ID ".
			"FROM b_learn_lesson L ".
			"INNER JOIN b_learn_question Q ON L.ID = Q.LESSON_ID ".
			"WHERE L.ID IN (".implode(",", $lessonIDs).") AND Q.ACTIVE = 'Y' ".
			($arTest["INCLUDE_SELF_TEST"] != "Y" ? "AND Q.SELF = 'N' " : "").
			"ORDER BY ".($arTest["RANDOM_QUESTIONS"] == "Y" ? CTest::GetRandFunction() : "Q.SORT ").
			($arTest["QUESTIONS_AMOUNT"] > 0 ? "LIMIT ".$arTest["QUESTIONS_AMOUNT"] :"");

			//echo $strSql;exit;

			$q = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if (!$q || intval($q->AffectedRowsCount())<=0)
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_IS_EMPTY"), "ERROR_TEST_IS_EMPTY");
				return false;
			}
		}
		else
		{
			$strSql =
			"INSERT INTO b_learn_test_result (ATTEMPT_ID, QUESTION_ID) ".
			"SELECT ".$ATTEMPT_ID." ,Q.ID ".
			"FROM b_learn_lesson L ".
			"INNER JOIN b_learn_question Q ON L.ID = Q.LESSON_ID ".
			"WHERE L.COURSE_ID = '".$arTest["COURSE_ID"]."' AND Q.ACTIVE = 'Y' ".
			($arTest["INCLUDE_SELF_TEST"] != "Y" ? "AND Q.SELF = 'N' " : "").
			"ORDER BY ".($arTest["RANDOM_QUESTIONS"] == "Y" ? CTest::GetRandFunction() : "Q.SORT ").
			($arTest["QUESTIONS_AMOUNT"] > 0 ? "LIMIT ".$arTest["QUESTIONS_AMOUNT"] :"");

			//echo $strSql;exit;

			$q = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if (!$q || intval($q->AffectedRowsCount())<=0)
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_IS_EMPTY"), "ERROR_TEST_IS_EMPTY");
				return false;
			}

		}

		$strSql = "UPDATE b_learn_attempt SET QUESTIONS = '".CTestResult::GetCount($ATTEMPT_ID)."' WHERE ID = ".$ATTEMPT_ID;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return true;
	}

	function GetList($arOrder=Array(), $arFilter=Array(), $arSelect = array())
	{
		global $DB, $USER, $APPLICATION, $USER_FIELD_MANAGER;

		$obUserFieldsSql = new CUserTypeSQL;
		$obUserFieldsSql->SetEntity("LEARN_ATTEMPT", "A.ID");
		$obUserFieldsSql->SetSelect($arSelect);
		$obUserFieldsSql->SetFilter($arFilter);
		$obUserFieldsSql->SetOrder($arOrder);

		$arFields = array(
			"ID" => "A.ID",
			"TEST_ID" => "A.TEST_ID",
			"OBY_DATE_END" => "A.DATE_END",
			"STUDENT_ID" => "A.STUDENT_ID",
			"DATE_START" => $DB->DateToCharFunction("A.DATE_START", "FULL"),
			"DATE_END" => $DB->DateToCharFunction("A.DATE_END", "FULL"),
			"STATUS" => "A.STATUS",
			"COMPLETED" =>  "A.COMPLETED",
			"SCORE" => "A.SCORE",
			"MAX_SCORE" => "A.MAX_SCORE",
			"QUESTIONS" => "A.QUESTIONS",
			"TEST_NAME" => "T.NAME",
			"USER_NAME" => $DB->Concat("'('",'U.LOGIN',"') '","CASE WHEN U.NAME IS NULL THEN '' ELSE U.NAME END","' '", "CASE WHEN U.LAST_NAME IS NULL THEN '' ELSE U.LAST_NAME END"),
			"USER_ID" => "U.ID",
			"MARK" => "TM.MARK",
			"MESSAGE" => "TM.DESCRIPTION",
		);

		if (count($arSelect) <= 0 || in_array("*", $arSelect))
			$arSelect = array_keys($arFields);

		$arSqlSelect = array();
		foreach($arSelect as $field)
		{
			$field = strtoupper($field);
			if(array_key_exists($field, $arFields))
				$arSqlSelect[$field] = $arFields[$field]." AS ".$field;
		}

		$sSelect = implode(",\n", $arSqlSelect);

		if (!is_array($arFilter))
			$arFilter = Array();

		$arSqlSearch = CTestAttempt::GetFilter($arFilter);

		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
			if(strlen($arSqlSearch[$i])>0)
				$strSqlSearch .= " AND ".$arSqlSearch[$i]." ";

		$r = $obUserFieldsSql->GetFilter();
		if(strlen($r)>0)
			$strSqlSearch .= " AND (".$r.") ";

		$bCheckPerm = ($APPLICATION->GetUserRight("learning") < "W" && !$USER->IsAdmin() && $arFilter["CHECK_PERMISSIONS"] != "N");

		$strSql =
		"SELECT DISTINCT ".
		$sSelect." ".
		$obUserFieldsSql->GetSelect()." ".
		"FROM b_learn_attempt A ".
		"INNER JOIN b_learn_test T ON A.TEST_ID = T.ID ".
		"INNER JOIN b_user U ON U.ID = A.STUDENT_ID ".
		"LEFT JOIN b_learn_course C ON C.ID = T.COURSE_ID ".
		"LEFT JOIN b_learn_test_mark TM ON A.TEST_ID = TM.TEST_ID ".
		$obUserFieldsSql->GetJoin("A.ID")." ".
		($bCheckPerm ? "LEFT JOIN b_learn_course_permission CP ON CP.COURSE_ID = C.ID " : "").
		"WHERE 1=1 ".
		"AND (TM.SCORE IS NULL OR TM.SCORE = (SELECT MIN(SCORE) FROM b_learn_test_mark WHERE SCORE >= CASE WHEN A.STATUS = 'F' THEN A.SCORE/A.MAX_SCORE*100 ELSE 0 END AND TEST_ID = A.TEST_ID)) ".
		($bCheckPerm ?
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

			if ($by == "id") $arSqlOrder[] = " A.ID ".$order." ";
			elseif ($by == "test_id") $arSqlOrder[] = " A.TEST_ID ".$order." ";
			elseif ($by == "student_id") $arSqlOrder[] = " A.STUDENT_ID ".$order." ";
			elseif ($by == "date_start") $arSqlOrder[] = " A.DATE_START ".$order." ";
			elseif ($by == "date_end") $arSqlOrder[] = " A.DATE_END ".$order." ";
			elseif ($by == "status") $arSqlOrder[] = " A.STATUS ".$order." ";
			elseif ($by == "score") $arSqlOrder[] = " A.SCORE ".$order." ";
			elseif ($by == "max_score") $arSqlOrder[] = " A.MAX_SCORE ".$order." ";
			elseif ($by == "completed") $arSqlOrder[] = " A.COMPLETED ".$order." ";
			elseif ($by == "questions") $arSqlOrder[] = " A.QUESTIONS ".$order." ";
			elseif ($by == "user_name") $arSqlOrder[] = " USER_NAME ".$order." ";
			elseif ($by == "test_name") $arSqlOrder[] = " TEST_NAME ".$order." ";
			elseif ($s = $obUserFieldsSql->GetOrder($by)) $arSqlOrder[$by] = " ".$s." ".$order." ";
			else
			{
				$arSqlOrder[] = " A.ID ".$order." ";
				$by = "id";
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

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("LEARN_ATTEMPT"));

		return $res;
	}
}
?>
