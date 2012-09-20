<?
class CCoursePackage
{

	var $ID;
	var $charset;
	var $LAST_ERROR = "";
	var $arCourse = Array();
	var $arSite = Array();
	var $arItems = Array();
	var $arResources = Array();
	var $RefID = 2;
	var $strItems = "";
	var $strResourses = "";
	var $arDraftFields = Array("detail_text", "preview_text", "description");
	var $arPicture = Array("detail_picture", "preview_picture", "file_id");
	var $arDate = Array("active_from", "active_to", "timestamp_x", "date_create");

	function CCoursePackage($COURSE_ID)
	{
		global $DB;
		$this->ID = intval($COURSE_ID);

		//Course exists?
		$res = CCourse::GetByID($this->ID);
		if (!$this->arCourse = $res->Fetch())
		{
			$this->LAST_ERROR = GetMessage("LEARNING_BAD_COURSE_ID_EX");
			return false;
		}

		$res = CCourse::GetSite($this->ID);
		if ($arSite = $res->GetNext())
		{
			$charset = $arSite["CHARSET"];
		}
		else
		{
			$this->LAST_ERROR = GetMessage("LEARNING_BAD_SITE_ID_EX");
			return false;
		}

		//Define charset
		if (strlen($charset) <= 0)
		{
			if (defined("SITE_CHARSET") && strlen(SITE_CHARSET) > 0)
				$charset = SITE_CHARSET;
			else
				$charset = "windows-1251";
		}
		$this->charset = $charset;

		//Get chapters, lessons, questions
		$this->_GetCourseContent();

		//Get tests
		$strSql =
			"SELECT T.*, ".
			$DB->DateToCharFunction("T.TIMESTAMP_X")." as TIMESTAMP_X ".
			"FROM b_learn_test T ".
			"WHERE T.COURSE_ID = ".intval($this->ID)." ".
			"ORDER BY SORT ASC ";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		while ($arRes= $res->Fetch())
		{
				$r = $this->RefID++;
				$this->arItems[$r] = $this->_CreateContent("TES", $arRes, $r);
				$this->strItems .= '<item identifier="TES'.$r.'" identifierref="RES'.$r.'"><title>'.htmlspecialchars($arRes["NAME"]).'</title></item>';
				$this->strResourses  .= '<resource identifier="RES'.$r.'" type="webcontent" href="res'.$r.'.xml">'.$this->_GetResourceFiles($r).'</resource>';
		}
	}


	function CreatePackage($PACKAGE_DIR)
	{
		if (strlen($this->LAST_ERROR)>0)
			return false;

		//Add last slash
		if (substr($PACKAGE_DIR,-1, 1) != "/")
			$PACKAGE_DIR .= "/";

		$path = $_SERVER["DOCUMENT_ROOT"].$PACKAGE_DIR;

		CheckDirPath($path);

		if (!is_dir($path) || !is_writable($path))
		{
			$this->LAST_ERROR = GetMessage("LEARNING_BAD_PACKAGE");
			return false;
		}

		RewriteFile($path."/res1.xml", $this->_CreateCourseToc());
		RewriteFile($path."/imsmanifest.xml", $this->CreateManifest());

		//XML Resource Data
		foreach ($this->arItems as $res_id => $content)
		{
			RewriteFile($path."/res".$res_id.".xml", $content);
		}

		//Resource
		$dbres_path = $path."/dbresources/";
		CheckDirPath($dbres_path);

		foreach ($this->arResources as $res_id => $arFiles)
		{
			$res_path = $path."/resources/res".$res_id."/";

			CheckDirPath($res_path);

			foreach ($arFiles as $arFile)
			{
				if (is_set($arFile,"DB"))
						@copy($_SERVER["DOCUMENT_ROOT"].$arFile["SRC"], $dbres_path.$arFile["ID"]);
					else
						@copy($_SERVER["DOCUMENT_ROOT"].$arFile["SRC"], $res_path.$arFile["ID"]);
			}
		}

		return true;
	}


	function CreateManifest()
	{
		if (strlen($this->LAST_ERROR)>0)
			return false;

		$strManifest = "<"."?xml version=\"1.0\" encoding=\"".$this->charset."\"?".">\n";
		$strManifest .= '<manifest xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2" identifier="toc1">';
		//<Organization>
		$strManifest .= '<organizations default="man1"><organization identifier="man1" structure="hierarchical">';
		$strManifest .= '<item identifier="COR1" identifierref="RES1" parameters=""><title>'.htmlspecialchars($this->arCourse["NAME"]).'</title>';
		$strManifest .= $this->strItems;
		$strManifest .= '</item>';
		$strManifest .= '</organization></organizations>';
		//<Resource>
		$strManifest .= '<resources><resource identifier="RES1" type="webcontent" href="res1.xml">'.$this->_GetResourceFiles(1).'</resource>';
		$strManifest .= $this->strResourses;
		$strManifest .= '</resources>';
		$strManifest .= '</manifest>';

		return $strManifest;
	}



	function _GetCourseContent($CHAPTER_ID = 0, $DEPTH_LEVEL = 1)
	{
		global $DB;

		$strSql =
		"SELECT C.*, ".
		$DB->DateToCharFunction("C.DATE_CREATE")." as DATE_CREATE, ".
		$DB->DateToCharFunction("C.TIMESTAMP_X")." as TIMESTAMP_X ".
		"FROM b_learn_lesson C ".
		"WHERE C.CHAPTER_ID ".
		(intval($CHAPTER_ID) > 0 ? "= ".intval($CHAPTER_ID)." " : "IS NULL ").
		"AND C.COURSE_ID = ".intval($this->ID)." ".
		" ORDER BY SORT ASC ";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		while ($arRes= $res->Fetch())
		{
			$r = $this->RefID++;
			$this->arItems[$r] = $this->_CreateContent("LES", $arRes, $r);
			$this->strItems .= '<item identifier="LES'.$r.'" identifierref="RES'.$r.'"><title>'.htmlspecialchars($arRes["NAME"]).'</title>';
			$this->strResourses  .= '<resource identifier="RES'.$r.'" type="webcontent" href="res'.$r.'.xml">'.$this->_GetResourceFiles($r).'</resource>';

			$strSql = "SELECT * FROM b_learn_question WHERE LESSON_ID=".$arRes["ID"]." ORDER BY SORT ASC ";
			$q = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while ($arRes = $q->Fetch())
			{
				$r = $this->RefID++;
				$this->arItems[$r] = $this->CreateQTI($arRes, $r);
				$this->strItems .= '<item identifier="QUE'.$r.'" identifierref="RES'.$r.'"><title>'.htmlspecialchars($arRes["NAME"]).'</title></item>';
				$this->strResourses  .= '<resource identifier="RES'.$r.'" type="imsqti_xmlv1p1" href="res'.$r.'.xml">'.$this->_GetResourceFiles($r).'</resource>';
			}
			$this->strItems .= "</item>";
		}

		$strSql =
		"SELECT C.*, ".
		$DB->DateToCharFunction("C.TIMESTAMP_X")." as TIMESTAMP_X ".
		"FROM b_learn_chapter C ".
		"WHERE C.CHAPTER_ID ".
		(intval($CHAPTER_ID) > 0 ? "= ".intval($CHAPTER_ID)." " : "IS NULL ").
		"AND C.COURSE_ID = ".intval($this->ID)." ".
		"ORDER BY SORT ASC ";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		while ($arRes= $res->Fetch())
		{
			$r = $this->RefID++;
			$this->arItems[$r] = $this->_CreateContent("CHA", $arRes, $r);
			$this->strItems .= '<item identifier="CHA'.$r.'" identifierref="RES'.$r.'"><title>'.htmlspecialchars($arRes["NAME"]).'</title>';
			$this->strResourses .= '<resource identifier="RES'.$r.'" type="webcontent" href="res'.$r.'.xml">'.$this->_GetResourceFiles($r).'</resource>';
			$this->_GetCourseContent($arRes["ID"], $DEPTH_LEVEL+1);
			$this->strItems .= "</item>";
		}

	}





	function _CreateCourseToc()
	{
		$str = "<"."?xml version=\"1.0\" encoding=\"".$this->charset."\"?".">\n";
		$str .= "<coursetoc>";

		foreach ($this->arCourse as $key => $val)
		{
			$key = strtolower($key);
			$str .= "<".$key.">";
			if (in_array($key, $this->arDraftFields) && strlen($val) > 0)
			{
				$str .= "<![CDATA[".$this->_ReplaceImages($val,1)."]]>";
			}
			elseif (in_array($key, $this->arDate) && strlen($val) > 0)
			{
				$str .= MakeTimeStamp($val);
			}
			elseif (in_array($key, $this->arPicture) && strlen($val) > 0)
			{
				$src = CFile::GetPath($val);
				$ext = GetFileExtension($src);
				$this->arResources[$res_id][] = Array("DB" => true, "SRC"=>$src, "ID"=>$val.".".$ext);
				$str .= $val.".".$ext;
			}
			else
			{
				$str .= htmlspecialchars($val);
			}
			$str .= "</".$key.">\n";
		}

		$str .= "</coursetoc>";

		return $str;
	}

	function _GetResourceFiles($res_id)
	{
		$str = "";

		if (is_set($this->arResources,$res_id))
			foreach ($this->arResources[$res_id] as $arFile)
			if (is_set($arFile,"DB"))
				$str .= '<file href="dbresources/'.$arFile["ID"].'" />';
			else
				$str .= '<file href="resources/res'.$res_id.'/'.$arFile["ID"].'" />';
		return $str;
	}

	function _CreateContent($TYPE, $arParams, $res_id)
	{
		$str = "<"."?xml version=\"1.0\" encoding=\"".$this->charset."\"?".">\n";
		$str .= '<content type="'.$TYPE.'">';

		foreach ($arParams as $key => $val)
		{
			$key = strtolower($key);
			$str .= "<".$key.">";
			if (in_array($key, $this->arDraftFields) && strlen($val) > 0)
			{
				$str .= "<![CDATA[".$this->_ReplaceImages($val,$res_id)."]]>";
			}
			elseif (in_array($key, $this->arPicture) && strlen($val) > 0)
			{
				$src = CFile::GetPath($val);
				$ext = GetFileExtension($src);
				$this->arResources[$res_id][] = Array("DB" => true, "SRC"=>$src,  "ID"=>$val.".".$ext);
				$str .= $val.".".$ext;
			}
			elseif (in_array($key, $this->arDate) && strlen($val) > 0)
			{
				$str .= MakeTimeStamp($val);
			}
			else
			{
				$str .= htmlspecialchars($val);
			}
			$str .= "</".$key.">\n";
		}

		$str .= "</content>";
		return $str;
	}

	function _replace_img($m0, $m1,$m2,$m3,$m4, $m5, $res_id)
	{
		$src = $m3;
		if($src <> "" && is_file($_SERVER["DOCUMENT_ROOT"].$src))
		{
			$dest = basename($src);
			//$uid = uniqid();
			$uid = RandString(5);
			$this->arResources[$res_id][] = Array("SRC"=>$src,  "ID"=>$uid.".".$dest);
			return stripslashes($m1.$m2."cid:resources/res".$res_id."/".$uid.".".$dest.$m4.$m5);
		}
		return stripslashes($m0);
	}

	function _ReplaceImages($text, $res_id)
	{
		return preg_replace(
			"/(<.+?src\s*=\s*)([\"']?)(.*?)(\\2)(\s.+?>|\s*>)/ise",
			"\$this->_replace_img('\\0', '\\1', '\\2', '\\3', '\\4', '\\5', ".$res_id.")",
			$text
		);
	}

	function CreateQTI($arParams, $res_id = 1)
	{
		global $DB;

		if (strlen($this->LAST_ERROR)>0)
			return false;

		$str = "<"."?xml version=\"1.0\" encoding=\"".$this->charset."\"?".">\n";
		$str .= "<questestinterop>";

		$str .= '<item ident="QUE'.$res_id.'">';
		$str .= '<presentation><material><mattext>'.htmlspecialchars($arParams["NAME"]).'</mattext>';

		if (intval($arParams["FILE_ID"]) > 0)
		{
			$file = CFile::GetByID($arParams["FILE_ID"]);
			if ($arFile = $file->Fetch())
			{
				$strImage = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
				$strImage = str_replace("//","/",$strImage);
				if(defined("BX_IMG_SERVER")) $strImage = BX_IMG_SERVER.$strImage;

				$name = $arParams["FILE_ID"].'.'.GetFileExtension($arFile["FILE_NAME"]);

				$this->arResources[$res_id][] = Array("DB" => true, "SRC"=>$strImage,  "ID"=>$name);

				$str .= '<matimage imagtype="'.$arFile["CONTENT_TYPE"].'" width="'.$arFile["WIDTH"].'" height="'.$arFile["HEIGHT"].'" uri="dbresources/'.$name.'"></matimage>';
			}
		}

		$str .= "</material>";
		switch ($arParams["QUESTION_TYPE"]) {
			case "M":
				$qType = 'Multiple';
				break;
			case "T":
				$qType = 'Text';
				break;
			case "R":
				$qType = 'Sort';
				break;
			default:
				$qType = 'Single';
				break;
		}
		$str .= '<response_lid ident="LID'.$res_id.'" rcardinality="'.$qType.'"><render_choice>';

		$strSql =
		"SELECT * FROM b_learn_answer WHERE QUESTION_ID = '".intval($arParams["ID"])."' ORDER BY SORT ASC ";
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);


		$cond = "";
		while ($arAnwer = $res->Fetch())
		{
			if ($arAnwer["CORRECT"] == "Y")
				$cond .= '<varequal respident="LID'.$res_id.'">ANS'.$arAnwer["ID"].'</varequal>';
			$str .= '<response_label ident="ANS'.$arAnwer["ID"].'"><material><mattext>'.htmlspecialchars($arAnwer["ANSWER"]).'</mattext></material></response_label>';
		}


		$str .= "</render_choice></response_lid></presentation>";

		$str .= "<resprocessing>";
		$str .= "<respcondition><conditionvar>".$cond."</conditionvar><setvar>".$arParams["POINT"]."</setvar></respcondition>";
		$str .= "</resprocessing>";

		$str .= "<bitrix>";
		$str .= "<description>";
		if (strlen($arParams["DESCRIPTION"])>0)
			$str .= "<![CDATA[".$this->_ReplaceImages($arParams["DESCRIPTION"],$res_id)."]]>";
		$str .= "</description>";

		$str .= "<description_type>".$arParams["DESCRIPTION_TYPE"]."</description_type>";
		$str .= "<self>".$arParams["SELF"]."</self>";
		$str .= "<sort>".$arParams["SORT"]."</sort>";
		$str .= "<active>".$arParams["ACTIVE"]."</active>";
		$str .= "</bitrix>";

		$str .= "</item></questestinterop>";

		return $str;
	}



}
?>