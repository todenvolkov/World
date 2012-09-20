<?
class CAllTestAttempt
{

	function CheckFields(&$arFields, $ID = false)
	{
		global $DB, $APPLICATION;

		if ($ID===false && !is_set($arFields, "TEST_ID"))
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_ID"), "EMPTY_TEST_ID");
			return false;
		}
		elseif (is_set($arFields, "TEST_ID"))
		{
			$r = CTest::GetByID($arFields["TEST_ID"]);
			if(!$r->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_ID_EX"), "ERROR_NO_TEST_ID");
				return false;
			}
		}

		if ($ID===false && !is_set($arFields, "STUDENT_ID"))
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_USER_ID"), "EMPTY_STUDENT_ID");
			return false;
		}
		elseif (is_set($arFields, "STUDENT_ID"))
		{
			$dbResult = CUser::GetByID($arFields["STUDENT_ID"]);
			if (!$dbResult->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_USER_ID_EX"), "ERROR_NO_STUDENT_ID");
				return false;
			}
		}

		if (is_set($arFields, "DATE_START") &&  (!$DB->IsDate($arFields["DATE_START"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_DATE_START"), "ERROR_DATE_START");
			return false;
		}

		if (is_set($arFields, "DATE_END") && strlen($arFields["DATE_END"])>0 && (!$DB->IsDate($arFields["DATE_END"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_DATE_END"), "ERROR_DATE_END");
			return false;
		}

		//Defaults
		if (is_set($arFields, "STATUS") && !in_array($arFields["STATUS"], Array("B", "D", "F", "N")))
			$arFields["STATUS"] = "B";

		if (is_set($arFields, "COMPLETED") && $arFields["COMPLETED"] != "Y")
			$arFields["COMPLETED"] = "N";

		return true;
	}

	function Add($arFields)
	{
		global $DB, $USER_FIELD_MANAGER;

		if(CTestAttempt::CheckFields($arFields) && $USER_FIELD_MANAGER->CheckFields("LEARN_ATTEMPT", 0, $arFields))
		{
			unset($arFields["ID"]);

			//$ID = $DB->Add("b_learn_attempt", $arFields, Array(""), "learning");

			$arInsert = $DB->PrepareInsert("b_learn_attempt", $arFields, "learning");

			$ID = CTestAttempt::DoInsert($arInsert, $arFields);

			CGradeBook::RecountAttempts($arFields["STUDENT_ID"], $arFields["TEST_ID"]);

			if ($ID)
			{
				$USER_FIELD_MANAGER->Update("LEARN_ATTEMPT", $ID, $arFields);
			}

			return $ID;
		}

		return false;
	}

	function Update($ID, $arFields)
	{
		global $DB, $USER_FIELD_MANAGER;

		$ID = intval($ID);
		if ($ID < 1) return false;

		if ($this->CheckFields($arFields, $ID) && $USER_FIELD_MANAGER->CheckFields("LEARN_ATTEMPT", 0, $arFields))
		{
			unset($arFields["ID"]);
			unset($arFields["TEST_ID"]);

			$arBinds=Array(
				//""=>$arFields[""]
			);

			$strUpdate = $DB->PrepareUpdate("b_learn_attempt", $arFields, "learning");
			$strSql = "UPDATE b_learn_attempt SET ".$strUpdate." WHERE ID=".$ID;
			$DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$USER_FIELD_MANAGER->Update("LEARN_ATTEMPT", $ID, $arFields);

			return true;
		}

		return false;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		//Results
		$strSql = "DELETE FROM b_learn_test_result WHERE ATTEMPT_ID = ".$ID;
		if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		//Attempt
		$strSql = "DELETE FROM b_learn_attempt WHERE ID = ".$ID;
		if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		$GLOBALS["USER_FIELD_MANAGER"]->Delete("LEARN_ATTEMPT", $ID);

		return true;
	}


	function GetFilter($arFilter)
	{

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
				case "TEST_ID":
				case "STUDENT_ID":
				case "SCORE":
				case "MAX_SCORE":
				case "QUESTIONS":
					$arSqlSearch[] = CCourse::FilterCreate("A.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "STATUS":
				case "COMPLETED":
					$arSqlSearch[] = CCourse::FilterCreate("A.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;

				case "DATE_START":
				case "DATE_END":
					$arSqlSearch[] = CCourse::FilterCreate("A.".$key, $val, "date", $bFullJoin, $cOperationType);
					break;

				case "USER":
					$arSqlSearch[] = GetFilterQuery("U.ID, U.LOGIN, U.NAME, U.LAST_NAME",$val);
					break;
			}

		}

		return $arSqlSearch;

	}




	function GetByID($ID)
	{
		return CTestAttempt::GetList(Array(), Array("ID" => $ID));
	}


	function GetCount($TEST_ID, $STUDENT_ID)
	{
		global $DB;

		$strSql =
		"SELECT COUNT(*) as C ".
		"FROM b_learn_attempt A ".
		"WHERE A.TEST_ID = '".intval($TEST_ID)."' AND A.STUDENT_ID = '".intval($STUDENT_ID)."'";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$res_cnt = $res->Fetch();

		return intval($res_cnt["C"]);
	}


	function IsTestCompleted($ATTEMPT_ID, $PERCENT)
	{
		global $DB;

		$strSql =
		"SELECT * ".
		"FROM b_learn_test_result TR, b_learn_question Q ".
		"WHERE TR.QUESTION_ID = Q.ID AND TR.CORRECT = 'N' AND Q.CORRECT_REQUIRED = 'Y' AND TR.ATTEMPT_ID = '".intval($ATTEMPT_ID)."'";

		if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		if ($arStat = $res->Fetch())
			return false;

		$strSql =
		"SELECT SUM(Q.POINT) as CNT_ALL, SUM(CASE WHEN TR.CORRECT = 'Y' THEN Q.POINT ELSE 0 END) as CNT_RIGHT ".
		"FROM b_learn_test_result TR, b_learn_question Q ".
		"WHERE TR.ATTEMPT_ID = '".intval($ATTEMPT_ID)."' AND TR.QUESTION_ID = Q.ID";

		if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		if (!$arStat = $res->Fetch())
			return false;

		if($arStat["CNT_RIGHT"]<=0 || $arStat["CNT_ALL"] == 0)
			return false;

		return (round($arStat["CNT_RIGHT"]/$arStat["CNT_ALL"]*100) >= intval($PERCENT));

	}



	function OnAttemptChange($ATTEMPT_ID, $bCOMPLETED = false)
	{
		global $DB;

		$ATTEMPT_ID = intval($ATTEMPT_ID);

		if ($ATTEMPT_ID < 1)
			return false;

		$strSql = "SELECT A.*, T.APPROVED, T.COMPLETED_SCORE, T.COURSE_ID ".
		"FROM b_learn_attempt A ".
		"INNER JOIN b_learn_test T ON A.TEST_ID = T.ID ".
		"WHERE A.ID = '".$ATTEMPT_ID."' AND A.STATUS = 'F' ";
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (!$arAttempt = $res->Fetch())
			return false;


		$COMPLETED = "N";
		if (
			$arAttempt["APPROVED"] == "Y" &&
			intval($arAttempt["COMPLETED_SCORE"])>0 &&
			CTestAttempt::IsTestCompleted($ATTEMPT_ID,$arAttempt["COMPLETED_SCORE"])
		)
			$COMPLETED = "Y";

		if ($bCOMPLETED)
			$COMPLETED = "Y";

		$strSql =
		"UPDATE b_learn_attempt SET COMPLETED = '".$COMPLETED."' ".
		"WHERE ID = '".$ATTEMPT_ID."'";

		if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		$strSql = "SELECT * FROM b_learn_gradebook WHERE STUDENT_ID='".$arAttempt["STUDENT_ID"]."' AND TEST_ID='".$arAttempt["TEST_ID"]."'";
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (!$arGradeBook = $res->Fetch())
		{
			$arFields = Array(
					"STUDENT_ID" => $arAttempt["STUDENT_ID"],
					"TEST_ID" => $arAttempt["TEST_ID"],
					"RESULT" => $arAttempt["SCORE"],
					"MAX_RESULT" => intval($arAttempt["MAX_SCORE"]),
					"COMPLETED" => $COMPLETED,
			);

			$at = new CGradeBook;

			if (!$res = $at->Add($arFields))
				return false;

			CCertification::Certificate($arAttempt["STUDENT_ID"], $arAttempt["COURSE_ID"]);
		}
		else
		{
			$strSql =
			"SELECT A.SCORE, A.MAX_SCORE FROM b_learn_attempt A ".
			"WHERE A.STUDENT_ID = '".$arAttempt["STUDENT_ID"]."' AND A.TEST_ID = '".$arAttempt["TEST_ID"]."'  ORDER BY COMPLETED DESC, SCORE DESC";
			//AND A.COMPLETED = 'Y'
			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if (!$arMaxScore = $res->Fetch())
				return false;

			if ($arGradeBook["COMPLETED"] == "Y")
				$COMPLETED = "Y";

			$strSql =
				"UPDATE b_learn_gradebook SET RESULT = '".intval($arMaxScore["SCORE"])."', MAX_RESULT = '".intval($arMaxScore["MAX_SCORE"])."',COMPLETED = '".$COMPLETED."' ".
				"WHERE ID = '".$arGradeBook["ID"]."'";

			if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
				return false;

			CCertification::Certificate($arAttempt["STUDENT_ID"], $arAttempt["COURSE_ID"]);
		}

		return true;
	}


	function AttemptFinished($ATTEMPT_ID)
	{
		global $DB;

		$ATTEMPT_ID = intval($ATTEMPT_ID);

		if ($ATTEMPT_ID < 1)
			return false;

		$strSql =
		"SELECT SUM(TR.POINT) as SCORE, SUM(Q.POINT) MAX_SCORE ".
		"FROM b_learn_test_result TR ".
		"INNER JOIN b_learn_question Q ON TR.QUESTION_ID = Q.ID ".
		"WHERE ATTEMPT_ID = '".$ATTEMPT_ID."' ";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (!$ar = $res->Fetch())
			return false;

		$res = $this->Update($ATTEMPT_ID,
			array(
				"SCORE" => $ar["SCORE"],
				"MAX_SCORE" => $ar["MAX_SCORE"],
				"STATUS"=>"F",
				"DATE_END"=>ConvertTimeStamp(false,"FULL"),
			)
		);

		if($res)
			return CTestAttempt::OnAttemptChange($ATTEMPT_ID);
		else
			return false;

	}

	function RecountQuestions($ATTEMPT_ID)
	{
		global $DB;

		$ATTEMPT_ID = intval($ATTEMPT_ID);

		if ($ATTEMPT_ID < 1)
			return false;

		$strSql = "SELECT COUNT(*) CNT, SUM(TR.POINT) CNT_SUM, SUM(Q.POINT) MAX_POINT ".
					"FROM b_learn_test_result TR ".
					"INNER JOIN b_learn_question Q ON TR.QUESTION_ID = Q.ID ".
					"WHERE TR.ATTEMPT_ID = ".$ATTEMPT_ID;
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (!$ar = $res->Fetch())
			return false;

		$strSql = "UPDATE b_learn_attempt SET QUESTIONS = '".intval($ar["CNT"])."', SCORE = '".intval($ar["CNT_SUM"])."', MAX_SCORE = '".intval($ar["MAX_POINT"])."' WHERE ID = ".$ATTEMPT_ID;
		if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		return true;
	}

	function IsTestFailed($ATTEMPT_ID, $PERCENT)
	{
		global $DB;

		$strSql =
		"SELECT * ".
		"FROM b_learn_test_result TR, b_learn_question Q ".
		"WHERE TR.QUESTION_ID = Q.ID AND TR.CORRECT = 'N' AND TR.ANSWERED = 'Y' AND Q.CORRECT_REQUIRED = 'Y' AND TR.ATTEMPT_ID = '".intval($ATTEMPT_ID)."'";


		if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return true;

		if ($arStat = $res->Fetch())
			return true;

		$strSql =
		"SELECT SUM(Q.POINT) as CNT_ALL, SUM(CASE WHEN TR.CORRECT = 'N' AND TR.ANSWERED = 'Y' THEN Q.POINT ELSE 0 END) as CNT_WRONG ".
		"FROM b_learn_test_result TR, b_learn_question Q ".
		"WHERE TR.ATTEMPT_ID = '".intval($ATTEMPT_ID)."' AND TR.QUESTION_ID = Q.ID";

		if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return true;

		if (!$arStat = $res->Fetch())
			return true;

		if($arStat["CNT_ALL"] == 0)
		{
			return true;
		}
		elseif ($arStat["CNT_WRONG"]==0)
		{
			return false;
		}

		return (round($arStat["CNT_WRONG"]/$arStat["CNT_ALL"]*100) > 100 - intval($PERCENT));

	}
}

?>
