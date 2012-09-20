<?

class CCourseImport
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

	function CCourseImport($PACKAGE_DIR, $arSITE_ID)
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

		if (!$title = $this->objXML->SelectNodes("/manifest/organizations/organization/item/title"))
		{
			$this->LAST_ERROR = GetMessage("LEARNING_BAD_NAME");
			return false;
		}

		$arFields = Array(
			"NAME" => $title->content,
			"SITE_ID" => $this->arSITE_ID,
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

		$r = new CDataXML();
		if (!$r->Load($this->package_dir."/res1.xml"))
			return false;

		if (!$data = $r->SelectNodes("/coursetoc/"))
			return false;
		$ar = $data->__toArray();
		$arFields =  $this->_MakeFields($ar);
		//unset($r);

		$res = $course->Update($this->COURSE_ID, $arFields);

		if(!$res)
		{
			if($e = $APPLICATION->GetException())
				$this->LAST_ERROR = $e->GetString();
			return false;
		}

		CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/learning/".$this->COURSE_ID);
		CopyDirFiles($this->package_dir."/resources/res1", $_SERVER["DOCUMENT_ROOT"]."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/learning/".$this->COURSE_ID."/res1", true);

		return true;

	}

	function CreateContent($arItems = Array(), $PARENT_ID = 0)
	{

		if (strlen($this->LAST_ERROR)>0)
			return false;

		if (empty($arItems))
		{
			if ($items = $this->objXML->SelectNodes("/manifest/organizations/organization/item/"))
			{
				$arItems = $items->__toArray();
				$arItems = $arItems["#"]["item"];
			}
		}

		foreach ($arItems as $ar)
		{
			//print_r($arItems);

			$type =  substr($ar["@"]["identifier"], 0, 3);
			$res_id = $ar["@"]["identifierref"];
			$title = $ar["#"]["title"][0]["#"];

			$ID = $this->_MakeItems($title, $type, $res_id, $PARENT_ID);

			if (is_set($ar["#"], "item"))
				$this->CreateContent($ar["#"]["item"], $ID);
		}

	}

	function _MakeItems($TITLE, $TYPE, $RES_ID, $PARENT_ID)
	{
		global $APPLICATION;

		if ($TYPE == "LES")
		{
			$arFields = Array(
				"NAME" => $TITLE,
				"CHAPTER_ID" => $PARENT_ID,
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
		elseif ($TYPE == "QUE")
		{
			$arFields = Array(
				"NAME" => $TITLE,
				"LESSON_ID" => $PARENT_ID
			);

			$cl = new CLQuestion;
		}
		elseif ($TYPE == "TES")
		{
			$arFields = Array(
				"NAME" => $TITLE,
				"COURSE_ID" => $this->COURSE_ID
			);

			$cl = new CTest;
		}
		else
			return $PARENT_ID;


		$r = new CDataXML();
		if (!$r->Load($this->package_dir."/".strtolower($RES_ID).".xml"))
			$r = null;

		if ($r !== false)
		{
			if ($TYPE == "QUE")
			{
				if (
					($data = $r->SelectNodes("/questestinterop/item/presentation/"))
					&&
					($resp = $r->SelectNodes("/questestinterop/item/resprocessing/"))
					)
				{
					$arQ = Array();
					$arData = $data->__toArray();
					$arResp = $resp->__toArray();

					if (is_set($arData["#"]["material"][0]["#"], "mattext"))
						$arQ["NAME"] = $arData["#"]["material"][0]["#"]["mattext"][0]["#"];

					if (is_set($arData["#"]["material"][0]["#"], "matimage"))
						$arQ["FILE_ID"] = Array(
							"MODULE_ID" => "learning",
							"name" =>basename($arData["#"]["material"][0]["#"]["matimage"][0]["@"]["uri"]),
							"tmp_name" => $this->package_dir."/".$arData["#"]["material"][0]["#"]["matimage"][0]["@"]["uri"],
							"size" =>@filesize($this->package_dir."/".$arData["#"]["material"][0]["#"]["matimage"][0]["@"]["uri"]),
							"type" => $arData["#"]["material"][0]["#"]["matimage"][0]["@"]["imagtype"]
						);

					if (is_set($arData["#"]["response_lid"][0]["@"], "rcardinality"))
						switch ($arData["#"]["response_lid"][0]["@"]["rcardinality"]) {
							case "Multiple":
								$arQ["QUESTION_TYPE"] = 'M';
								break;
							case "Text":
								$arQ["QUESTION_TYPE"] = 'T';
								break;
							case "Sort":
								$arQ["QUESTION_TYPE"] = 'R';
								break;
							default:
								$arQ["QUESTION_TYPE"] = 'S';
								break;
						}

					if (is_set($arData["#"]["respcondition"][0]["#"], "setvar"))
						$arQ["POINT"] = $arData["#"]["respcondition"][0]["#"]["setvar"];

					//Additional
					if ($bx = $r->SelectNodes("/questestinterop/item/bitrix/"))
					{
						$arQ = array_merge($arQ, $this->_MakeFields($bx->__toArray()));
						unset($bx);
					}

					$arFields = array_merge($arFields,$arQ);

					$cl = new CLQuestion;
					$ID = $cl->Add($arFields);

					if ($ID > 0)
					{
						$PARENT_ID = $ID;
						$arCorrect = Array();
						if (
							is_set($arResp["#"]["respcondition"][0]["#"], "conditionvar")
							&&
							is_set($arResp["#"]["respcondition"][0]["#"]["conditionvar"][0]["#"], "varequal")
							)
						{

							foreach ($arResp["#"]["respcondition"][0]["#"]["conditionvar"][0]["#"]["varequal"] as $ar)
								$arCorrect[] = $ar["#"];
						}

						if (is_set($arData["#"]["response_lid"][0]["#"], "render_choice")
							&&
							is_set($arData["#"]["response_lid"][0]["#"]["render_choice"][0]["#"], "response_label")
							)
						{
							$i = 0;
							foreach ($arData["#"]["response_lid"][0]["#"]["render_choice"][0]["#"]["response_label"] as $ar)
							{
								$i +=10;
								$cl = new CLAnswer;
								$arFields = Array(
									"QUESTION_ID" => $PARENT_ID,
									"SORT" => $i,
									"CORRECT" => (in_array($ar["@"]["ident"],$arCorrect) ? "Y": "N"),
									"ANSWER" => $ar["#"]["material"][0]["#"]["mattext"][0]["#"],
								);

								$AswerID = $cl->Add($arFields);
								$res = ($AswerID > 0);
								if (!$res)
								{
									if ($e = $APPLICATION->GetException())
										$this->arWarnings[$TYPE][] = Array("TITLE" => $TITLE, "TEXT" =>$e->GetString());
								}
							}
						}
					}
					else
					{
						if ($e = $APPLICATION->GetException())
							$this->arWarnings[$TYPE][] = Array("TITLE" => $TITLE, "TEXT" =>$e->GetString());
					}

					unset($cl);
					unset($data);
					unset($arQ);
					unset($resp);
					unset($arData);
					unset($arResp);

					return $PARENT_ID;
				}
			}
			else
			{
				if ($data = $r->SelectNodes("/content/"))
				{
					$ar = $data->__toArray();
					$arFields = array_merge($arFields,$this->_MakeFields($ar));

					if (is_set($arFields, "COMPLETED_SCORE") && intval($arFields["COMPLETED_SCORE"]) <= 0)
						unset($arFields["COMPLETED_SCORE"]);
					if ((is_set($arFields, "PREVIOUS_TEST_ID") && intval($arFields["PREVIOUS_TEST_ID"]) <= 0) || !CTest::GetByID($arFields["PREVIOUS_TEST_ID"])->Fetch())
						unset($arFields["PREVIOUS_TEST_ID"], $arFields["PREVIOUS_TEST_SCORE"]);
				}
			}
		}

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



	function _MakeFields(&$arFields)
	{
		$arRes = Array();
		$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");
		foreach($arFields["#"] as $field => $arValue)
		{
			if (in_array($field, $this->arUnsetFields))
				continue;

			if (in_array($field, $this->arDraftFields) && is_set($arValue[0]["#"], "cdata-section"))
			{
				$arRes[strtoupper($field)] = preg_replace("~([\"'])(cid:resources/(.+?))(\\1)~is", "\\1/".$upload_dir."/learning/".$this->COURSE_ID."/\\3\\1",$arValue[0]["#"]["cdata-section"][0]["#"]);
				continue;
			}

			if (in_array($field, $this->arDate) && strlen($arValue[0]["#"]) > 0)
			{
				$time = date("His", $arValue[0]["#"]);
				$arRes[strtoupper($field)] = ConvertTimeStamp($arValue[0]["#"], $time == "000000" ? "SHORT" : "FULL");
				continue;
			}

			if (in_array($field, $this->arPicture) && intval($arValue[0]["#"]) > 0)
			{
				$file = $this->package_dir."/dbresources/".$arValue[0]["#"];
				$aImage = @getimagesize($file);
				if($aImage === false)
						continue;

				$arRes[strtoupper($field)] = Array(
					"MODULE_ID" => "learning",
					"name" =>$arValue[0]["#"],
					"tmp_name" => $file,
					"size" =>@filesize($file),
					"type" => (function_exists("image_type_to_mime_type")? image_type_to_mime_type($aImage[2]) : CCourseImport::ImageTypeToMimeType($aImage[2]))
				);
				continue;
			}

			$arRes[strtoupper($field)] = $arValue[0]["#"];
		}
		unset($arFields);
		return $arRes;
	}

	function ImportPackage()
	{
		if (!$this->CreateCourse())
			return false;

		$this->CreateContent();

		CopyDirFiles($this->package_dir."/resources", $_SERVER["DOCUMENT_ROOT"]."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/learning/".$this->COURSE_ID, true, true);

		return true;
	}

	function ImageTypeToMimeType($type)
	{
		$aTypes = array(
			1 => "image/gif",
			2 => "image/jpeg",
			3 => "image/png",
			4 => "application/x-shockwave-flash",
			5 => "image/psd",
			6 => "image/bmp",
			7 => "image/tiff",
			8 => "image/tiff",
			9 => "application/octet-stream",
			10 => "image/jp2",
			11 => "application/octet-stream",
			12 => "application/octet-stream",
			13 => "application/x-shockwave-flash",
			14 => "image/iff",
			15 => "image/vnd.wap.wbmp",
			16 => "image/xbm"
		);
		if(!empty($aTypes[$type]))
			return $aTypes[$type];
		else
			return "application/octet-stream";
	}

}

?>