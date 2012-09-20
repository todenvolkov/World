<?

class CCourseSCORM
{
	var $package_dir;
	var $LAST_ERROR = "";
	var $arManifest = Array();
	var $arSITE_ID = Array();
	var $COURSE_ID = 0;
	var $objXML;
	var $arDraftFields = Array("detail_text", "preview_text", "description");
	var $arUnsetFields = Array("id", "timestamp_x", "chapter_id", "course_id", "lesson_id", "question_id", "created_by");
	var $arPicture = Array("detail_picture", "preview_picture", "file_id");
	var $arDate = Array("active_from", "active_to", "date_create");
	var $arWarnings = Array();
	var $arResources = array();

	function CCourseSCORM($PACKAGE_DIR, $arSITE_ID)
	{
		global $APPLICATION;

		//Cut last slash
		if (substr($PACKAGE_DIR,-1, 1) == "/")
			$PACKAGE_DIR = substr($PACKAGE_DIR, 0, -1);

		$this->package_dir = $_SERVER["DOCUMENT_ROOT"].$PACKAGE_DIR;

		//Dir exists?
		if (!is_dir($this->package_dir))
		{
			$this->LAST_ERROR = GetMessage("LEARNING_BAD_PACKAGE")."<br>";
			return false;
		}

		//Manifest exists?
		if (!is_file($this->package_dir."/imsmanifest.xml"))
		{
			$this->LAST_ERROR = GetMessage("LEARNING_MANIFEST_NOT_FOUND")."<br>";
			return false;
		}

		//Sites check
		if (!is_array($arSITE_ID) || empty($arSITE_ID))
		{
			$this->LAST_ERROR = GetMessage("LEARNING_BAD_SITE_ID")."<br>";
			return false;
		}

		$this->arSITE_ID = $arSITE_ID;

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");

		$this->objXML = new CDataXML();
		if (!$this->objXML->Load($this->package_dir."/imsmanifest.xml"))
		{
			$this->LAST_ERROR = GetMessage("LEARNING_MANIFEST_NOT_FOUND")."<br>";
			return false;
		}

		return true;
	}

	function CreateCourse()
	{
		global $APPLICATION;

		if (strlen($this->LAST_ERROR)>0)
			return false;

		if (!$title = $this->objXML->SelectNodes("/manifest/organizations/organization/title"))
		{
			$this->LAST_ERROR = GetMessage("LEARNING_BAD_NAME");
			return false;
		}

		$arFields = Array(
			"NAME" => $title->content,
			"SITE_ID" => $this->arSITE_ID,
			"SCORM" => "Y",
		);

		$course = new CCourse;
		$this->COURSE_ID = $course->Add($arFields);
		$res = ($this->COURSE_ID);

		if(!$res)
		{
			if($e = $APPLICATION->GetException())
				$this->LAST_ERROR = $e->GetString();
			return false;
		}

		return true;

	}

	function CreateContent($arItems = Array(), $PARENT_ID = 0)
	{

		if (strlen($this->LAST_ERROR)>0)
			return false;

		if (empty($arItems))
		{
			if ($items = $this->objXML->SelectNodes("/manifest/organizations/organization/"))
			{
				$arItems = $items->__toArray();
				$arItems = $arItems["#"]["item"];
			}
		}

		foreach ($arItems as $ar)
		{
			$title = $ar["#"]["title"][0]["#"];
			$type = (!is_set($ar["#"], "item") && is_set($ar["@"], "identifierref")) ? "LES" : "CHA";
			$launch = "";
			if ($type == "LES")
			{
				foreach($this->arResources as $res)
				{
					if ($res["@"]["identifier"] == $ar["@"]["identifierref"])
					{
						$launch = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/learning/scorm/".$this->COURSE_ID."/";
						$launch .= $res["@"]["href"];
						if(is_set($ar["@"]["parameters"]))
						{
							$launch .= $ar["@"]["parameters"];
						}
					}
				}

			}

			$ID = $this->_MakeItems($title, $type, $launch, $PARENT_ID);

			if (is_set($ar["#"], "item"))
				$this->CreateContent($ar["#"]["item"], $ID);
		}
	}

	function _MakeItems($TITLE, $TYPE, $LAUNCH, $PARENT_ID)
	{
		global $APPLICATION;

		if ($TYPE == "LES")
		{
			$arFields = Array(
				"NAME" => $TITLE,
				"CHAPTER_ID" => $PARENT_ID,
				"LAUNCH" => $LAUNCH,
				"DETAIL_TEXT_TYPE" => "file",
				"COURSE_ID" => $this->COURSE_ID
			);

			$cl = new CLesson;
		}
		elseif ($TYPE == "CHA")
		{
			$arFields = Array(
				"NAME" => $TITLE,
				"CHAPTER_ID" => $PARENT_ID,
				"COURSE_ID" => $this->COURSE_ID
			);

			$cl = new CChapter;
		}
		else
			return $PARENT_ID;


		$ID = $cl->Add($arFields);
		unset($cl);

		if($ID > 0)
			return $ID;
		else
		{
			if($e = $APPLICATION->GetException())
				$this->arWarnings[$TYPE][] = Array("TITLE" => $TITLE, "TEXT" =>$e->GetString());
		}
	}
	function ImportPackage()
	{
		$resources = $this->objXML->SelectNodes("/manifest/resources/");
		$this->arResources = $resources->__toArray();
		$this->arResources = $this->arResources["#"]["resource"];

		if (!$this->CreateCourse())
			return false;

		$this->CreateContent();

		CopyDirFiles($this->package_dir, $_SERVER["DOCUMENT_ROOT"]."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/learning/scorm/".$this->COURSE_ID, true, true);

		return true;
	}

}

?>