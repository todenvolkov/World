<?

class CAllCourse
{

	function CheckFields($arFields, $ID = false)
	{
		global $DB;
		$arMsg = array();

		if ( (is_set($arFields, "NAME") || $ID === false) && strlen($arFields["NAME"]) <= 0)
		{
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage("LEARNING_BAD_NAME"));
		}

		if (is_set($arFields, "ACTIVE_FROM") && strlen($arFields["ACTIVE_FROM"])>0 && (!$DB->IsDate($arFields["ACTIVE_FROM"], false, LANG, "FULL")))
		{
			$arMsg[] = array("id"=>"ACTIVE_FROM", "text"=> GetMessage("LEARNING_BAD_ACTIVE_FROM"));
		}

		if (is_set($arFields, "ACTIVE_TO") && strlen($arFields["ACTIVE_TO"])>0 && (!$DB->IsDate($arFields["ACTIVE_TO"], false, LANG, "FULL")))
		{
			$arMsg[] = array("id"=>"ACTIVE_TO", "text"=> GetMessage("LEARNING_BAD_ACTIVE_TO"));
		}


		if (is_set($arFields, "PREVIEW_PICTURE"))
		{
			$error = CFile::CheckImageFile($arFields["PREVIEW_PICTURE"]);
			if (strlen($error)>0)
			{
				$arMsg[] = array("id"=>"PREVIEW_PICTURE", "text"=> $error);
			}
		}

		//Sites
		if (
			($ID === false && !is_set($arFields, "SITE_ID"))
			||
			(is_set($arFields, "SITE_ID"))
			&&
			(!is_array($arFields["SITE_ID"]) || empty($arFields["SITE_ID"]))
			)
		{
			$arMsg[] = array("id"=>"SITE_ID[]", "text"=> GetMessage("LEARNING_BAD_SITE_ID"));
		}
		elseif (is_set($arFields, "SITE_ID"))
		{
			$tmp = "";
			foreach($arFields["SITE_ID"] as $lang)
			{
				$res = CSite::GetByID($lang);
				if(!$res->Fetch())
				{
					$tmp .= "'".$lang."' - ".GetMessage("LEARNING_BAD_SITE_ID_EX")."<br>";
				}
			}
			if ($tmp!="") $arMsg[] = array("id"=>"SITE_ID[]", "text"=> $tmp);
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
		global $DB;

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";

		if (is_set($arFields, "DESCRIPTION_TYPE") && $arFields["DESCRIPTION_TYPE"] != "html")
			$arFields["DESCRIPTION_TYPE"] = "text";

		if (is_set($arFields, "PREVIEW_TEXT_TYPE") && $arFields["PREVIEW_TEXT_TYPE"] != "html")
			$arFields["PREVIEW_TEXT_TYPE"]="text";

		if (is_set($arFields, "PREVIEW_PICTURE") && strlen($arFields["PREVIEW_PICTURE"]["name"])<=0 && strlen($arFields["PREVIEW_PICTURE"]["del"])<=0)
			unset($arFields["PREVIEW_PICTURE"]);

		if($this->CheckFields($arFields))
		{
			unset($arFields["ID"]);

			CFile::SaveForDB($arFields, "PREVIEW_PICTURE", "learning");

			$ID = $DB->Add("b_learn_course", $arFields, Array("PREVIEW_TEXT","DESCRIPTION"));

			//Permissions
			if (is_set($arFields, "GROUP_ID"))
				CCourse::SetPermission($ID, $arFields["GROUP_ID"]);

			//Sites
			$str_LID = "''";
			foreach($arFields["SITE_ID"] as $lang)
					$str_LID .= ", '".$DB->ForSql($lang)."'";
			$strSql = "DELETE FROM b_learn_course_site WHERE COURSE_ID=".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$strSql =
				"INSERT INTO b_learn_course_site(COURSE_ID, SITE_ID) ".
				"SELECT ".$ID.", LID ".
				"FROM b_lang ".
				"WHERE LID IN (".$str_LID.") ";

			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($ID && (is_set($arFields, "NAME") || is_set($arFields, "DESCRIPTION")))
			{
				if (CModule::IncludeModule("search"))
				{
					$rsCourse = CCourse::GetByID($ID);
					if ($arCourse = $rsCourse->Fetch())
					{
						if(is_set($arFields, "SITE_ID"))
						{
							$arSiteIds = array();
							foreach($arFields["SITE_ID"] as $lang)
							{
								$rsSitePaths = CSitePath::GetList(Array(), Array("SITE_ID" => $lang, "TYPE" => "C"));
								if ($arSitePaths = $rsSitePaths->Fetch())
								{
									$strPath = $arSitePaths["PATH"];
								}
								else
								{
									$strPath = "";
								}
								$arSiteIds[$lang] = str_replace("#COURSE_ID#", $ID, $strPath);
							}
							$arSearchIndex = Array(
								"LAST_MODIFIED"	=> $arCourse["TIMESTAMP_X"],
								"TITLE" => $arCourse["NAME"],
								"BODY" => strip_tags($arCourse["DESCRIPTION"]) ? strip_tags($arCourse["DESCRIPTION"]) : $arCourse["NAME"],
								"SITE_ID" => $arSiteIds,
								"PERMISSIONS" => array(2),
							);

							CSearch::Index("learning", "C".$ID, $arSearchIndex);
						}
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
			$arFields["ACTIVE"] = "N";

		if (is_set($arFields, "DESCRIPTION_TYPE") && $arFields["DESCRIPTION_TYPE"] != "html")
			$arFields["DESCRIPTION_TYPE"] = "text";

		if (is_set($arFields, "PREVIEW_TEXT_TYPE") && $arFields["PREVIEW_TEXT_TYPE"] != "html")
			$arFields["PREVIEW_TEXT_TYPE"]="text";

		if (is_set($arFields, "PREVIEW_PICTURE"))
		{
			if(strlen($arFields["PREVIEW_PICTURE"]["name"])<=0 && strlen($arFields["PREVIEW_PICTURE"]["del"])<=0)
				unset($arFields["PREVIEW_PICTURE"]);
			else
			{
				$pic_res = $DB->Query("SELECT PREVIEW_PICTURE FROM b_learn_course WHERE ID=".$ID);
				if($pic_res = $pic_res->Fetch())
					$arFields["PREVIEW_PICTURE"]["old_file"]=$pic_res["PREVIEW_PICTURE"];
			}
		}


		if ($this->CheckFields($arFields, $ID))
		{
			unset($arFields["ID"]);

			CFile::SaveForDB($arFields, "PREVIEW_PICTURE", "learning");

			$strUpdate = $DB->PrepareUpdate("b_learn_course", $arFields);

			$arBinds=Array(
				"PREVIEW_TEXT"=>$arFields["PREVIEW_TEXT"],
				"DESCRIPTION"=>$arFields["DESCRIPTION"]
			);

			$strSql = "UPDATE b_learn_course SET ".$strUpdate." WHERE ID=".$ID;

			$res = $DB->QueryBind($strSql, $arBinds);
			//if ($res->AffectedRowsCount() < 1) return false;

			//Permissions
			if (is_set($arFields, "GROUP_ID"))
				CCourse::SetPermission($ID, $arFields["GROUP_ID"]);

			//Sites
			if(is_set($arFields, "SITE_ID"))
			{
				$str_LID = "''";
				foreach($arFields["SITE_ID"] as $lang)
					$str_LID .= ", '".$DB->ForSql($lang)."'";

				$strSql = "DELETE FROM b_learn_course_site WHERE COURSE_ID=".$ID;
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

				$strSql =
					"INSERT INTO b_learn_course_site(COURSE_ID, SITE_ID) ".
					"SELECT ".$ID.", LID ".
					"FROM b_lang ".
					"WHERE LID IN (".$str_LID.") ";

				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			}

			if ($ID && (is_set($arFields, "NAME") || is_set($arFields, "DESCRIPTION")))
			{
				if (CModule::IncludeModule("search"))
				{
					$rsCourse = CCourse::GetByID($ID);
					if ($arCourse = $rsCourse->Fetch())
					{
						if(is_set($arFields, "SITE_ID"))
						{
							$arSiteIds = array();
							foreach($arFields["SITE_ID"] as $lang)
							{
								$rsSitePaths = CSitePath::GetList(Array(), Array("SITE_ID" => $lang, "TYPE" => "C"));
								if ($arSitePaths = $rsSitePaths->Fetch())
								{
									$strPath = $arSitePaths["PATH"];
								}
								else
								{
									$strPath = "";
								}
								$arSiteIds[$lang] = str_replace("#COURSE_ID#", $ID, $strPath);
							}
							$arSearchIndex = Array(
								"LAST_MODIFIED"	=> $arCourse["TIMESTAMP_X"],
								"TITLE" => $arCourse["NAME"],
								"BODY" => strip_tags($arCourse["DESCRIPTION"]) ? strip_tags($arCourse["DESCRIPTION"]) : $arCourse["NAME"],
								"SITE_ID" => $arSiteIds,
								"PERMISSIONS" => array(2),
							);

							CSearch::Index("learning", "C".$ID, $arSearchIndex);
						}
					}
				}
			}

			return true;
		}
		return false;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;
		$strSql = "SELECT PREVIEW_PICTURE, SCORM FROM b_learn_course WHERE ID = ".$ID;
		$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (!$arCourse = $r->Fetch())
			return false;

		//Certificates
		$certificate = CCertification::GetList(Array(), Array("COURSE_ID" => $ID));
		if ($arCertificate = $certificate->GetNext())
			return false;

		//Tests
		$tests = CTest::GetList(Array(), Array("COURSE_ID" => $ID));
		while($arTest = $tests->Fetch())
		{
			if(!CTest::Delete($arTest["ID"]))
				return false;
		}

		//Chapters
		$chapters = CChapter::GetList(Array(), Array("COURSE_ID"=>$ID, "CHAPTER_ID"=>""));
		while($arChapter = $chapters->Fetch())
		{
			if(!CChapter::Delete($arChapter["ID"]))
				return false;
		}

		//Lessons
		$lessons = CLesson::GetList(Array(), Array("COURSE_ID" => $ID));
		while($arLesson = $lessons->Fetch())
		{
			if(!CLesson::Delete($arLesson["ID"]))
				return false;
		}

		if ($arCourse["SCORM"] == "Y")
		{
			DeleteDirFilesEx("/".(COption::GetOptionString("main", "upload_dir", "upload"))."/learning/scorm/".$ID);
		}

		if (!$DB->Query("DELETE FROM b_learn_course_permission WHERE COURSE_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		if (!$DB->Query("DELETE FROM b_learn_course_site WHERE COURSE_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		if (!$DB->Query("DELETE FROM b_learn_course WHERE ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		CFile::Delete($arCourse["PREVIEW_PICTURE"]);

		if (CModule::IncludeModule("search"))
		{
			CSearch::DeleteIndex("learning", false, "C".$ID);
			CSearch::DeleteIndex("learning", "C".$ID);
		}

		return true;
	}



	function GetByID($ID)
	{
		return CCourse::GetList(Array(),Array("ID" => $ID));
	}

	function SetPermission($COURSE_ID, $arGROUP_ID)
	{
		global $DB;

		$COURSE_ID = intval($COURSE_ID);
		if ($COURSE_ID < 1 || !is_array($arGROUP_ID))
			return;

		$strSql = "DELETE FROM b_learn_course_permission WHERE COURSE_ID = ".$COURSE_ID;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		foreach ($arGROUP_ID as $ID => $Perm)
		{
			if (intval($ID) < 2 || !in_array($Perm, Array("R","W","X")))
				continue;

			$strSql =
				"INSERT INTO b_learn_course_permission(COURSE_ID,USER_GROUP_ID,PERMISSION) ".
				"SELECT ".$COURSE_ID.", ID, '".$Perm."' ".
				"FROM b_group ".
				"WHERE ID = ".intval($ID);

			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
	}


	function GetPermission($COURSE_ID)
	{
		global $USER, $DB, $APPLICATION;

		if ($APPLICATION->GetGroupRight("learning"));
			return "X";

		$strSql =
			"SELECT MAX(PERMISSION) as PERM ".
			"FROM b_learn_course_permission ".
			"WHERE COURSE_ID = ".intval($COURSE_ID)." AND USER_GROUP_ID IN (".$USER->GetUserGroupString().")";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($arRes = $res->Fetch())
		{
			if ($arRes["PERM"] != "")
				return $arRes["PERM"];
		}

		return "D";
	}

	function GetGroupPermissions($COURSE_ID)
	{
		global $DB;

		$strSql =
			"SELECT * ".
			"FROM b_learn_course_permission ".
			"WHERE COURSE_ID = '".intval($COURSE_ID)."'";

		$dbres = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arRes = Array();
		while($res = $dbres->Fetch())
			$arRes[$res["USER_GROUP_ID"]] = $res["PERMISSION"];

		return $arRes;
	}

	function IsHaveCourse($MIN_PERMISSION = "W")
	{
		global $USER, $DB, $APPLICATION;
		if ($APPLICATION->GetUserRight("learning") == "W" || $USER->IsAdmin())
			return true;

		$strSql =
			"SELECT COUNT('x') as CNT ".
			"FROM b_learn_course_permission CP ".
			"WHERE CP.USER_GROUP_ID IN (".$USER->GetGroups().") ".
			"AND CP.PERMISSION >= '".(strlen($MIN_PERMISSION)==1 ? $MIN_PERMISSION : "W")."' ";

		//echo $strSql;

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $res->Fetch();
		if($ar["CNT"] > 0)
			return true;

		return false;
	}

	function GetSite($COURSE_ID)
	{
		global $DB;
		$strSql = "SELECT L.*, CS.* FROM b_learn_course_site CS, b_lang L WHERE L.LID=CS.SITE_ID AND CS.COURSE_ID=".intval($COURSE_ID);

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}


	function MkOperationFilter($key)
	{
		if(substr($key, 0, 1)=="=") //Identical
		{
			$key = substr($key, 1);
			$cOperationType = "I";
		}
		elseif(substr($key, 0, 2)=="!=") //not Identical
		{
			$key = substr($key, 2);
			$cOperationType = "NI";
		}
		elseif(substr($key, 0, 1)=="%") //substring
		{
			$key = substr($key, 1);
			$cOperationType = "S";
		}
		elseif(substr($key, 0, 2)=="!%") //not substring
		{
			$key = substr($key, 2);
			$cOperationType = "NS";
		}
		elseif(substr($key, 0, 1)=="?") //logical
		{
			$key = substr($key, 1);
			$cOperationType = "?";
		}
		elseif(substr($key, 0, 2)=="><") //between
		{
			$key = substr($key, 2);
			$cOperationType = "B";
		}
		elseif(substr($key, 0, 3)=="!><") //not between
		{
			$key = substr($key, 3);
			$cOperationType = "NB";
		}
		elseif(substr($key, 0, 2)==">=") //greater or equal
		{
			$key = substr($key, 2);
			$cOperationType = "GE";
		}
		elseif(substr($key, 0, 1)==">")  //greater
		{
			$key = substr($key, 1);
			$cOperationType = "G";
		}
		elseif(substr($key, 0, 2)=="<=")  //less or equal
		{
			$key = substr($key, 2);
			$cOperationType = "LE";
		}
		elseif(substr($key, 0, 1)=="<")  //less
		{
			$key = substr($key, 1);
			$cOperationType = "L";
		}
		elseif(substr($key, 0, 1)=="!") // not field LIKE val
		{
			$key = substr($key, 1);
			$cOperationType = "N";
		}
		else
			$cOperationType = "E";	// field LIKE val

		return Array("FIELD"=>$key, "OPERATION"=>$cOperationType);
	}

	function FilterCreate($fname, $vals, $type, &$bFullJoin, $cOperationType=false, $bSkipEmpty = true)
	{
		global $DB;
 		if(!is_array($vals))
			$vals=Array($vals);

		if(count($vals)<1)
			return "";

		if(is_bool($cOperationType))
		{
			if($cOperationType===true)
				$cOperationType = "N";
			else
				$cOperationType = "E";
		}

		if($cOperationType=="G")
			$strOperation = ">";
		elseif($cOperationType=="GE")
			$strOperation = ">=";
		elseif($cOperationType=="LE")
			$strOperation = "<=";
		elseif($cOperationType=="L")
			$strOperation = "<";
		else
			$strOperation = "=";

		$bFullJoin = false;
		$bWasLeftJoin = false;

		$res = Array();
		for($i=0; $i<count($vals); $i++)
		{
			$val = $vals[$i];
			if(!$bSkipEmpty || strlen($val)>0 || (is_bool($val) && $val===false))
			{
				switch ($type)
				{
				case "string_equal":
					if(strlen($val)<=0)
						$res[] =
						($cOperationType=="N"?"NOT":"").
						"(".
						$fname." IS NULL OR ".$DB->Length($fname).
						"<=0)";
					else
						$res[] =
						"(".
						($cOperationType=="N"?" ".$fname." IS NULL OR NOT (":"").
						CCourse::_Upper($fname).$strOperation.CCourse::_Upper("'".$DB->ForSql($val)."'").
						($cOperationType=="N"?")":"").
						")";
					break;
				case "string":
					if($cOperationType=="?")
					{
						if(strlen($val)>0)
							$res[] = GetFilterQuery($fname, $val, "Y",array(),"N");
					}
					elseif(strlen($val)<=0)
					{
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL OR ".$DB->Length($fname)."<=0)";
					}
					else
					{
						if($strOperation=="=")
							$res[] =
							"(".
							($cOperationType=="N"?" ".$fname." IS NULL OR NOT (":"").
							(strtoupper($DB->type)=="ORACLE"?CCourse::_Upper($fname)." LIKE ".CCourse::_Upper("'".$DB->ForSqlLike($val)."'")." ESCAPE '\\'" : $fname." ".($strOperation=="="?"LIKE":$strOperation)." '".$DB->ForSqlLike($val)."'").
							($cOperationType=="N"?")":"").
							")";
						else
							$res[] =
							"(".
							($cOperationType=="N"?" ".$fname." IS NULL OR NOT (":"").
							(strtoupper($DB->type)=="ORACLE"?CCourse::_Upper($fname).
							" ".$strOperation." ".CCourse::_Upper("'".$DB->ForSql($val)."'")." " : $fname." ".$strOperation." '".$DB->ForSql($val)."'").
							($cOperationType=="N"?")":"").
							")";
					}
					break;
				case "date":
					if(strlen($val)<=0)
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL)";
					else
						$res[] =
						"(".
						($cOperationType=="N"?" ".$fname." IS NULL OR NOT (":"").
						$fname." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").
						($cOperationType=="N"?")":"").
						")";
					break;
				case "number":
					if(strlen($val)<=0)
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL)";
					else
						$res[] =
						"(".
						($cOperationType=="N"?" ".$fname." IS NULL OR NOT (":"").
						$fname." ".$strOperation." '".DoubleVal($val).
						($cOperationType=="N"?"')":"'").
						")";
					break;
				/*
				case "number_above":
					if(strlen($val)<=0)
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL)";
					else
						$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".$fname." ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				*/
				}

				// INNER JOIN in this case
				if(strlen($val)>0 && $cOperationType!="N")
					$bFullJoin = true;
				else
					$bWasLeftJoin = true;
			}
		}

		$strResult = "";
		for($i=0; $i<count($res); $i++)
		{
			if($i>0)
				$strResult .= ($cOperationType=="N"?" AND ":" OR ");
			$strResult .= $res[$i];
		}

		if (count($res) > 1)
			$strResult = "(".$strResult.")";


		if($bFullJoin && $bWasLeftJoin && $cOperationType!="N")
			$bFullJoin = false;

		return $strResult;


	}


	function GetCourseContent($COURSE_ID, $arAddSelectFileds = Array("DETAIL_TEXT", "DETAIL_TEXT_TYPE", "DETAIL_PICTURE"))
	{
		global $DB;

		$COURSE_ID = intval($COURSE_ID);
		$CHAPTER_ID = intval($CHAPTER_ID);

		$CACHE_ID = $COURSE_ID."|".serialize($arAddSelectFileds);
		$CACHE_ID = md5($CACHE_ID);

		if (array_key_exists($CACHE_ID, $GLOBALS["LEARNING_CACHE_COURSE"]) && is_array($GLOBALS["LEARNING_CACHE_COURSE"][$CACHE_ID]))
		{
			$r = new CDBResult();
			$r->InitFromArray($GLOBALS["LEARNING_CACHE_COURSE"][$CACHE_ID]);
			return $r;
		}

		$arSelectFields = Array("ID", "NAME", "CHAPTER_ID", "SORT");
		$arAllowedFields = Array(
			"PREVIEW_TEXT_TYPE", "PREVIEW_PICTURE",
			"DETAIL_TEXT_TYPE",  "DETAIL_PICTURE"
			//"DETAIL_TEXT","PREVIEW_TEXT", for odbc
		);
		$arSpecial = Array("DETAIL_TEXT","PREVIEW_TEXT");

		$strSelect = $strAddtional = "";
		foreach($arSelectFields as $field)
			$strSelect .= $field.", ";

		if (is_array($arAddSelectFileds) && !empty($arAddSelectFileds))
		{
			foreach($arAddSelectFileds as $field)
			{
				if (in_array($field, $arSpecial))
					$strAddtional .= ", ".$field;
				elseif (in_array($field, $arAllowedFields))
					$strSelect .= $field.", ";
			}
		}

		$strSql =
		"(SELECT ".$strSelect." 'LE' as TYPE".$strAddtional.", LAUNCH FROM b_learn_lesson WHERE COURSE_ID = '".$COURSE_ID."' AND ACTIVE = 'Y') ".
		"UNION ALL ".
		"(SELECT ".$strSelect." 'CH' as TYPE".$strAddtional.", '' AS LAUNCH FROM b_learn_chapter WHERE COURSE_ID = '".$COURSE_ID."' AND ACTIVE = 'Y') ".
		"ORDER BY CHAPTER_ID, TYPE DESC, SORT ";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$resElements = Array();
		while($ar = $res->Fetch())
		{
			$resElements[intval($ar["CHAPTER_ID"])][] = $ar;
		}

		$GLOBALS["LEARNING_CACHE_COURSE"][$CACHE_ID] = Array();
		if (!empty($resElements))
		{
			CCourse::_RecursiveCourseContent($resElements, $GLOBALS["LEARNING_CACHE_COURSE"][$CACHE_ID]);
			unset($resElements);
		}

		$r = new CDBResult();
		$r->InitFromArray($GLOBALS["LEARNING_CACHE_COURSE"][$CACHE_ID]);

		return $r;
	}

	function _RecursiveCourseContent(&$resElements, &$arReturn, $CHAPTER_ID = 0, $DEPTH_LEVEL = 1)
	{
		foreach ($resElements[$CHAPTER_ID] as $element)
		{
			$arReturn[$element["TYPE"].$element["ID"]] = $element;
			$arReturn[$element["TYPE"].$element["ID"]]["DEPTH_LEVEL"] = $DEPTH_LEVEL;

			if ($element["TYPE"] == "LE")
				continue;

			if (isset($resElements[$element["ID"]]))
				CCourse::_RecursiveCourseContent($resElements, $arReturn, $element["ID"], ($DEPTH_LEVEL+1));
		}
	}


	//Handlers
	function OnGroupDelete($GROUP_ID)
	{
		global $DB;
		return $DB->Query("DELETE FROM b_learn_course_permission WHERE USER_GROUP_ID=".intval($GROUP_ID), true);
	}


	function OnBeforeLangDelete($lang)
	{
		global $DB;
		$r = CCourse::GetList(array(), array("SITE_ID"=>$lang));
		return ($r->Fetch() ? false : true);
	}

	function OnUserDelete($user_id)
	{
		return CStudent::Delete($user_id);
	}

	function TimeToStr($seconds)
	{
		$str = "";

		$seconds = intval($seconds);
		if ($seconds <= 0)
			return $str;

		$days = intval($seconds/86400);
		if ($days>0)
		{
			$str .= $days."&nbsp;".GetMessage("LEARNING_DAYS")." ";
			$seconds = $seconds - $days*86400;
		}

		$hours = intval($seconds/3600);
		if ($hours>0)
		{
			$str .= $hours."&nbsp;".GetMessage("LEARNING_HOURS")." ";
			$seconds = $seconds - $hours*3600;
		}

		$minutes = intval($seconds/60);
		if ($minutes>0)
		{
			$str .= $minutes."&nbsp;".GetMessage("LEARNING_MINUTES")." ";
			$seconds = $seconds - $minutes*60;
		}

		$str .= ($seconds%60)."&nbsp;".GetMessage("LEARNING_SECONDS");

		return $str;
	}

	function OnSearchReindex()
	{
		global $DB;

		$arResult = array();
		$arIdsPos = array();

		$strSql = "
			SELECT
				c.*,
				".$DB->DateToCharFunction("c.TIMESTAMP_X")." as TIMESTAMP_X,
				cs.SITE_ID,
				p.PATH
			FROM
				b_learn_course c,
				b_learn_course_site cs,
				b_learn_site_path p
			WHERE
				c.ID = cs.COURSE_ID
				AND cs.SITE_ID = p.SITE_ID
				AND p.TYPE = 'C'
		";

		$rsCourse = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($arCourse = $rsCourse->Fetch()) {
			$Url = str_replace("#COURSE_ID#", $arCourse["ID"], $arCourse["PATH"]);
			if (array_key_exists("C".$arCourse["ID"], $arIdsPos))
			{
				$arResult[$arIdsPos["C".$arCourse["ID"]]]["SITE_ID"][$arCourse["SITE_ID"]] = $Url;
			}
			else
			{
				$Result = Array(
					"ID" => "C".$arCourse["ID"],
					"LAST_MODIFIED"	=> $arCourse["TIMESTAMP_X"],
					"TITLE" => $arCourse["NAME"],
					"BODY" => "aaa",//strip_tags($arCourse["DESCRIPTION"]) ? strip_tags($arCourse["DESCRIPTION"]) : $arCourse["NAME"],
					"SITE_ID" => array($arCourse["SITE_ID"] => $Url),
					"PERMISSIONS" => array(2),
				);
				$arResult[] = $Result;
				$arIdsPos["C".$arCourse["ID"]] = sizeof($arResult) - 1;
			}
		}

		$strSql = "
			SELECT
				c.*,
				".$DB->DateToCharFunction("c.TIMESTAMP_X")." as TIMESTAMP_X,
				cs.SITE_ID,
				p.PATH
			FROM
				b_learn_chapter c,
				b_learn_course_site cs,
				b_learn_site_path p
			WHERE
				c.COURSE_ID = cs.COURSE_ID
				AND cs.SITE_ID = p.SITE_ID
				AND p.TYPE = 'H'
		";
		$rsChapter = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($arChapter = $rsChapter->Fetch()) {
			$Url = str_replace("#COURSE_ID#", $arChapter["COURSE_ID"], $arChapter["PATH"]);
			$Url = str_replace("#CHAPTER_ID#", $arChapter["ID"], $Url);
			if (array_key_exists("H".$arChapter["ID"], $arIdsPos))
			{
				$arResult[$arIdsPos["H".$arChapter["ID"]]]["SITE_ID"][$arChapter["SITE_ID"]] = $Url;
			}
			else
			{
				$Result = Array(
					"ID" => "H".$arChapter["ID"],
					"LAST_MODIFIED"	=> $arChapter["TIMESTAMP_X"],
					"TITLE" => $arChapter["NAME"],
					"BODY" => "aaa",//strip_tags($arChapter["DETAIL_TEXT"]) ? strip_tags($arChapter["DETAIL_TEXT"]) : $arChapter["NAME"],
					"SITE_ID" => array($arChapter["SITE_ID"] => $Url),
					"PERMISSIONS" => array(2),
					"PARAM1" => "C".$arChapter["COURSE_ID"],
				);
				if($arChapter["CHAPTER_ID"])
				{
					$Result["PARAM2"] = "H".$arChapter["CHAPTER_ID"];
				}
				$arResult[] = $Result;
				$arIdsPos["H".$arChapter["ID"]] = sizeof($arResult) - 1;
			}
		}

		$strSql = "
			SELECT
				l.*,
				".$DB->DateToCharFunction("l.TIMESTAMP_X")." as TIMESTAMP_X,
				cs.SITE_ID,
				p.PATH
			FROM
				b_learn_lesson l,
				b_learn_course_site cs,
				b_learn_site_path p
			WHERE
				l.COURSE_ID = cs.COURSE_ID
				AND cs.SITE_ID = p.SITE_ID
				AND p.TYPE = 'L'
		";
		$rsLesson = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($arLesson = $rsLesson->Fetch()) {
			$Url = str_replace("#COURSE_ID#", $arLesson["COURSE_ID"], $arLesson["PATH"]);
			$Url = str_replace("#LESSON_ID#", $arLesson["ID"], $Url);
			if (array_key_exists("L".$arLesson["ID"], $arIdsPos))
			{
				$arResult[$arIdsPos["L".$arLesson["ID"]]]["SITE_ID"][$arLesson["SITE_ID"]] = $Url;
			}
			else
			{
				$Result = Array(
					"ID" => "L".$arLesson["ID"],
					"LAST_MODIFIED"	=> $arLesson["TIMESTAMP_X"],
					"TITLE" => $arLesson["NAME"],
					"BODY" => "aaa",//strip_tags($arLesson["DETAIL_TEXT"]) ? strip_tags($arLesson["DETAIL_TEXT"]) : $arLesson["NAME"],
					"SITE_ID" => array($arLesson["SITE_ID"] => $Url),
					"PERMISSIONS" => array(2),
					"PARAM1" => "C".$arLesson["COURSE_ID"],
				);
				if($arLesson["CHAPTER_ID"])
				{
					$Result["PARAM2"] = "H".$arLesson["CHAPTER_ID"];
				}
				$arResult[] = $Result;
				$arIdsPos["L".$arLesson["ID"]] = sizeof($arResult) - 1;
			}
		}
		return $arResult;
	}
}
?>