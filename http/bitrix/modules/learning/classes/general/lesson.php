<?

class CAllLesson
{

	function CheckFields($arFields, $ID = false)
	{
		global $DB;
		$arMsg = Array();

		if ( (is_set($arFields, "NAME") || $ID === false) && strlen($arFields["NAME"]) <= 0)
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage("LEARNING_BAD_NAME"));

		if (is_set($arFields, "PREVIEW_PICTURE"))
		{
			$error = CFile::CheckImageFile($arFields["PREVIEW_PICTURE"]);
			if (strlen($error)>0)
				$arMsg[] = array("id"=>"PREVIEW_PICTURE", "text"=> $error);
		}

		if (is_set($arFields, "DETAIL_PICTURE"))
		{
			$error = CFile::CheckImageFile($arFields["DETAIL_PICTURE"]);
			if (strlen($error)>0)
				$arMsg[] = array("id"=>"DETAIL_PICTURE", "text"=> $error);
		}

		if ($ID===false && !is_set($arFields, "COURSE_ID"))
			$arMsg[] = array("id"=>"COURSE_ID", "text"=> GetMessage("LEARNING_BAD_COURSE_ID"));

		if (is_set($arFields, "COURSE_ID"))
		{
			$r = CCourse::GetByID($arFields["COURSE_ID"]);
			if(!$r->Fetch())
				$arMsg[] = array("id"=>"COURSE_ID", "text"=> GetMessage("LEARNING_BAD_COURSE_ID_EX"));
		}

		if(empty($arMsg))
		{
			if (intval($arFields["CHAPTER_ID"])>0)
			{
				$r = CChapter::GetList(Array(), Array("ID"=>$arFields["CHAPTER_ID"], "COURSE_ID" => $arFields["COURSE_ID"]));
				if(!$r->Fetch())
					$arMsg[] = array("id"=>"CHAPTER_ID", "text"=> GetMessage("LEARNING_BAD_BLOCK_SECTION_PARENT"));
			}
		}

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return true;
	}

	function Add($arFields)
	{
		global $DB, $USER;

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"]="N";

		if (is_set($arFields, "DESCRIPTION_TEXT_TYPE") && $arFields["DESCRIPTION_TEXT_TYPE"] != "html")
			$arFields["DESCRIPTION_TEXT_TYPE"]="text";

		if (is_set($arFields, "PREVIEW_TEXT_TYPE") && $arFields["PREVIEW_TEXT_TYPE"] != "html")
			$arFields["PREVIEW_TEXT_TYPE"]="text";

		if (is_set($arFields, "PREVIEW_PICTURE") && strlen($arFields["PREVIEW_PICTURE"]["name"])<=0 && strlen($arFields["PREVIEW_PICTURE"]["del"])<=0)
			unset($arFields["PREVIEW_PICTURE"]);

		if (is_set($arFields, "DETAIL_PICTURE") && strlen($arFields["DETAIL_PICTURE"]["name"])<=0 && strlen($arFields["DETAIL_PICTURE"]["del"])<=0)
			unset($arFields["DETAIL_PICTURE"]);

		if (intval($arFields["CHAPTER_ID"]) < 1)
			$arFields["CHAPTER_ID"] = false;


		if ($this->CheckFields($arFields))
		{
			unset($arFields["ID"]);

			CFile::SaveForDB($arFields, "PREVIEW_PICTURE", "learning");
			CFile::SaveForDB($arFields, "DETAIL_PICTURE", "learning");

			$arInsert = $DB->PrepareInsert("b_learn_lesson", $arFields);

			$ID = $this->DoInsert($arInsert, $arFields);

			if ($ID && (is_set($arFields, "NAME") || is_set($arFields, "DETAIL_TEXT")))
			{
				if (CModule::IncludeModule("search"))
				{
					$rsLesson = CLesson::GetByID($ID);
					if ($arLesson = $rsLesson->Fetch())
					{
						$strSql = "
							SELECT
								s.SITE_ID,
								p.PATH
							FROM
								b_learn_course_site s,
								b_learn_site_path p
							WHERE
								COURSE_ID = ".$arLesson["COURSE_ID"]."
								AND s.SITE_ID = p.SITE_ID
								AND p.TYPE = 'L'
						";
						$rsSites = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

						$arSiteIds = array();
						while($arSites = $rsSites->Fetch())
						{
							$Url = str_replace("#COURSE_ID#", $arLesson["COURSE_ID"], $arSites["PATH"]);
							$Url = str_replace("#LESSON_ID#", $ID, $Url);
							$arSiteIds[$arSites["SITE_ID"]] = $Url;
						}
						$arSearchIndex = Array(
							"LAST_MODIFIED"	=> $arLesson["TIMESTAMP_X"],
							"TITLE" => $arLesson["NAME"],
							"BODY" => strip_tags($arLesson["DETAIL_TEXT"]) ? strip_tags($arLesson["DETAIL_TEXT"]) : $arLesson["NAME"],
							"SITE_ID" => $arSiteIds,
							"PERMISSIONS" => array(2),
							"PARAM1" => "C".$arLesson["COURSE_ID"],
						);

						if ($arLesson["CHAPTER_ID"]) {
							$arSearchIndex["PARAM2"] = "H".$arLesson["CHAPTER_ID"];
						}

						CSearch::Index("learning", "L".$ID, $arSearchIndex);
					}
				}
			}

			return $ID;
		}

		return false;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"]="N";

		if (is_set($arFields, "DESCRIPTION_TEXT_TYPE") && $arFields["DESCRIPTION_TEXT_TYPE"] != "html")
			$arFields["DESCRIPTION_TEXT_TYPE"]="text";

		if (is_set($arFields, "PREVIEW_TEXT_TYPE") && $arFields["PREVIEW_TEXT_TYPE"] != "html")
			$arFields["PREVIEW_TEXT_TYPE"]="text";

		if (is_set($arFields, "CHAPTER_ID") && intval($arFields["CHAPTER_ID"]) == "0")
			$arFields["CHAPTER_ID"] = false;

		if (is_set($arFields, "PREVIEW_PICTURE"))
		{
			if(strlen($arFields["PREVIEW_PICTURE"]["name"])<=0 && strlen($arFields["PREVIEW_PICTURE"]["del"])<=0)
				unset($arFields["PREVIEW_PICTURE"]);
			else
			{
				$pic_res = $DB->Query("SELECT PREVIEW_PICTURE FROM b_learn_chapter WHERE ID=".$ID);
				if($pic_res = $pic_res->Fetch())
					$arFields["PREVIEW_PICTURE"]["old_file"]=$pic_res["PREVIEW_PICTURE"];
			}
		}

		if (is_set($arFields, "DETAIL_PICTURE"))
		{
			if(strlen($arFields["DETAIL_PICTURE"]["name"])<=0 && strlen($arFields["DETAIL_PICTURE"]["del"])<=0)
				unset($arFields["DETAIL_PICTURE"]);
			else
			{
				$pic_res = $DB->Query("SELECT DETAIL_PICTURE FROM b_learn_chapter WHERE ID=".$ID);
				if($pic_res = $pic_res->Fetch())
					$arFields["DETAIL_PICTURE"]["old_file"]=$pic_res["DETAIL_PICTURE"];
			}
		}


		if ($this->CheckFields($arFields, $ID))
		{
			unset($arFields["ID"]);
			unset($arFields["COURSE_ID"]);

			CFile::SaveForDB($arFields, "PREVIEW_PICTURE", "learning");
			CFile::SaveForDB($arFields, "DETAIL_PICTURE", "learning");

			$strUpdate = $DB->PrepareUpdate("b_learn_lesson", $arFields);

			$arBinds=Array(
				"PREVIEW_TEXT"=>$arFields["PREVIEW_TEXT"],
				"DETAIL_TEXT"=>$arFields["DETAIL_TEXT"]
			);

			$strSql = "UPDATE b_learn_lesson SET ".$strUpdate." WHERE ID=".$ID;
			$DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($ID && (is_set($arFields, "NAME") || is_set($arFields, "DETAIL_TEXT")))
			{
				if (CModule::IncludeModule("search"))
				{
					$rsLesson = CLesson::GetByID($ID);
					if ($arLesson = $rsLesson->Fetch())
					{
						$strSql = "
							SELECT
								s.SITE_ID,
								p.PATH
							FROM
								b_learn_course_site s,
								b_learn_site_path p
							WHERE
								COURSE_ID = ".$arLesson["COURSE_ID"]."
								AND s.SITE_ID = p.SITE_ID
								AND p.TYPE = 'L'
						";
						$rsSites = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

						$arSiteIds = array();
						while($arSites = $rsSites->Fetch())
						{
							$Url = str_replace("#COURSE_ID#", $arLesson["COURSE_ID"], $arSites["PATH"]);
							$Url = str_replace("#LESSON_ID#", $ID, $Url);
							$arSiteIds[$arSites["SITE_ID"]] = $Url;
						}
						$arSearchIndex = Array(
							"LAST_MODIFIED"	=> $arLesson["TIMESTAMP_X"],
							"TITLE" => $arLesson["NAME"],
							"BODY" => strip_tags($arLesson["DETAIL_TEXT"]) ? strip_tags($arLesson["DETAIL_TEXT"]) : $arLesson["NAME"],
							"SITE_ID" => $arSiteIds,
							"PERMISSIONS" => array(2),
							"PARAM1" => "C".$arLesson["COURSE_ID"],
						);

						if ($arLesson["CHAPTER_ID"]) {
							$arSearchIndex["PARAM2"] = "H".$arLesson["CHAPTER_ID"];
						}

						CSearch::Index("learning", "L".$ID, $arSearchIndex);
					}
				}
			}

			return true;
		}
		return false;
	}



	function GetFilter($arFilter=Array())
	{
		global $DB;

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
				case "COURSE_ID":
				case "CREATED_BY":
					$arSqlSearch[] = CCourse::FilterCreate("CL.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "CHAPTER_ID":
					$arSqlSearch[] =  CCourse::FilterCreate("CL.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "NAME":
				case "DETAIL_TEXT":
				case "PREVIEW_TEXT":
					$arSqlSearch[] = CCourse::FilterCreate("CL.".$key, $val, "string", $bFullJoin, $cOperationType);
					break;

				case "ACTIVE":
					$arSqlSearch[] = CCourse::FilterCreate("CL.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;

				case "TIMESTAMP_X":
				case "DATE_CREATE":
					$arSqlSearch[] = CCourse::FilterCreate("CL.".$key, $val, "date", $bFullJoin, $cOperationType);
					break;

			}
		}

		return $arSqlSearch;
	}

	function GetByID($ID)
	{
		return CLesson::GetList($arOrder=Array(), $arFilter=Array("ID" => $ID));
	}

	function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		$strSql = "SELECT PREVIEW_PICTURE, DETAIL_PICTURE FROM b_learn_lesson WHERE ID = ".$ID;
		$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if (!$arRes = $r->Fetch())
			return false;

		//Questions
		$q = CLQuestion::GetList(Array(), Array("LESSON_ID" => $ID));
		while($arQ = $q->Fetch())
		{
			if(!CLQuestion::Delete($arQ["ID"]))
				return false;
		}

		CFile::Delete($arRes["PREVIEW_PICTURE"]);
		CFile::Delete($arRes["DETAIL_PICTURE"]);

		$strSql = "DELETE FROM b_learn_lesson WHERE ID = ".$ID;

		if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		if (CModule::IncludeModule("search"))
		{
			CSearch::DeleteIndex("learning", "L".$ID);
		}

		return true;
	}
}

?>