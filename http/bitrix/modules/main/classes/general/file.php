<?
IncludeModuleLangFile(__FILE__);

class CAllFile
{
	function SaveForDB(&$arFields, $field, $dir)
	{
		$arFile = $arFields[$field];
		if(isset($arFile) && is_array($arFile))
		{
			if($arFile["name"] <> '' || $arFile["del"] <> '' || $arFile["description"] <> '')
			{
				$res = CFile::SaveFile($arFile, $dir);
				if($res !== false)
				{
					$arFields[$field] = (intval($res) > 0? $res : false);
					return true;
				}
			}
		}
		unset($arFields[$field]);
		return false;
	}

	function SaveFile($arFile, $strSavePath, $bForceMD5=false, $bSkipExt=false)
	{
		global $DB;

		$strFileName = bx_basename($arFile["name"]);	/* filename.gif */

		if(strlen($arFile["del"]) > 0)
		{
			CFile::DoDelete($arFile["old_file"]);
			if(strlen($strFileName) <= 0)
				return "NULL";
		}

		if(strlen($arFile["name"]) <= 0)
		{
			if(is_set($arFile, "description") && intval($arFile["old_file"])>0)
				CFile::UpdateDesc($arFile["old_file"], $arFile["description"]);
			return false;
		}

		if(is_set($arFile, "content") && !is_set($arFile, "size"))
			$arFile["size"] = strlen($arFile["content"]);
		else
			$arFile["size"] = filesize($arFile["tmp_name"]);

		/****************************** QUOTA ******************************/
		if (COption::GetOptionInt("main", "disk_space") > 0)
		{
			$quota = new CDiskQuota();
			if (!$quota->checkDiskQuota($arFile))
				return false;
		}
		/****************************** QUOTA ******************************/

		$sOriginalName = $strFileName;

		//check for double extension vulnerability
		$strFileName = RemoveScriptExtension($strFileName);
		if($strFileName == '')
			return false;

		//check .htaccess etc.
		if(IsFileUnsafe($strFileName))
			return false;

		$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");

		if($bForceMD5 != true && COption::GetOptionString("main", "save_original_file_name", "N")=="Y")
		{
			if(COption::GetOptionString("main", "convert_original_file_name", "Y")=="Y")
				$strFileName = preg_replace('/([^'.BX_VALID_FILENAME_SYMBOLS.'])/e', "chr(rand(97, 122))", $strFileName);

			$dir_add = "";
			$i=0;
			while(true)
			{
				$dir_add = substr(md5(uniqid(mt_rand(), true)), 0, 3);
				if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$strSavePath."/".$dir_add."/".$strFileName))
					break;
				if($i>=25)
				{
					$dir_add = md5(uniqid(mt_rand(), true));
					break;
				}
				$i++;
			}
			if(substr($strSavePath, -1, 1) <> "/")
				$strSavePath .= "/".$dir_add;
			else
				$strSavePath .= $dir_add."/";

			$newName = $strFileName;
		}
		else
		{
			$strFileExt = ($bSkipExt == true? '' : strrchr($arFile["name"], "."));
			while(true)
			{
				$newName = md5(uniqid(mt_rand(), true)).$strFileExt;
				if(substr($strSavePath, -1, 1) <> "/")
					$strSavePath .= "/".substr($newName, 0, 3);
				else
					$strSavePath .= substr($newName, 0, 3)."/";

				if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$strSavePath."/".$newName))
					break;
			}
		}

		$strDirName = $_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$strSavePath."/";
		$strDbFileNameX = $strDirName.$newName;

		CheckDirPath($strDirName);

		if(is_set($arFile, "content"))
		{
			$f = fopen($strDbFileNameX, "ab");
			if(!$f)
				return false;
			if(!fwrite($f, $arFile["content"]))
				return false;
			fclose($f);
		}
		elseif(!copy($arFile["tmp_name"], $strDbFileNameX))
		{
			CFile::DoDelete($arFile["old_file"]);
			return false;
		}

		CFile::DoDelete($arFile["old_file"]);

		@chmod($strDbFileNameX, BX_FILE_PERMISSIONS);
		$imgArray = getimagesize($strDbFileNameX);

		if(is_array($imgArray))
		{
			$intWIDTH = $imgArray[0];
			$intHEIGHT = $imgArray[1];
		}
		else
		{
			$intWIDTH = 0;
			$intHEIGHT = 0;
		}

		if($arFile["type"]=="image/pjpeg")
			$arFile["type"]="image/jpeg";

		/****************************** QUOTA ******************************/
		if (COption::GetOptionInt("main", "disk_space") > 0)
		{
			CDiskQuota::updateDiskQuota("file", $arFile["size"], "insert");
		}
		/****************************** QUOTA ******************************/

		$NEW_IMAGE_ID = CFile::DoInsert(array(
			"HEIGHT" => $intHEIGHT,
			"WIDTH" => $intWIDTH,
			"FILE_SIZE" => $arFile["size"],
			"CONTENT_TYPE" => $arFile["type"],
			"SUBDIR" => $strSavePath,
			"FILE_NAME" => $newName,
			"MODULE_ID" => $arFile["MODULE_ID"],
			"ORIGINAL_NAME" => $sOriginalName,
			"DESCRIPTION" => $arFile["description"],
		));

		CFile::CleanCache($NEW_IMAGE_ID);
		return $NEW_IMAGE_ID;
	}

	function DoInsert($arFields)
	{
		global $DB;
		$strSql =
			"INSERT INTO b_file(HEIGHT, WIDTH, FILE_SIZE, CONTENT_TYPE, SUBDIR, FILE_NAME, MODULE_ID, ORIGINAL_NAME, DESCRIPTION) ".
			"VALUES('".intval($arFields["HEIGHT"])."', '".intval($arFields["WIDTH"])."', '".intval($arFields["FILE_SIZE"])."', '".
				$DB->ForSql($arFields["CONTENT_TYPE"], 255)."' , '".$DB->ForSql($arFields["SUBDIR"], 255)."', '".
				$DB->ForSQL($arFields["FILE_NAME"], 255)."', '".$DB->ForSQL($arFields["MODULE_ID"], 50)."', '".
				$DB->ForSql($arFields["ORIGINAL_NAME"], 255)."', '".$DB->ForSQL($arFields["DESCRIPTION"], 255)."') ";
		$DB->Query($strSql);
		return $DB->LastID();
	}

	function CleanCache($ID)
	{
		$ID = intval($ID);
		if(CACHED_b_file!==false)
		{
			$bucket_size = intval(CACHED_b_file_bucket_size);
			if($bucket_size<=0) $bucket_size = 10;
			$bucket = intval($ID/$bucket_size);
			$GLOBALS["CACHE_MANAGER"]->Clean("b_file".$bucket, "b_file");
		}
	}

	function GetFromCache($FILE_ID)
	{
		global $CACHE_MANAGER, $DB;

		$bucket_size = intval(CACHED_b_file_bucket_size);
		if($bucket_size<=0) $bucket_size = 10;

		$bucket = intval($FILE_ID/$bucket_size);
		if($CACHE_MANAGER->Read(CACHED_b_file, $cache_id="b_file".$bucket, "b_file"))
		{
			$arFiles = $CACHE_MANAGER->Get($cache_id);
		}
		else
		{
			$arFiles = array();
			$rs = $DB->Query("
				SELECT f.*,".$DB->DateToCharFunction("f.TIMESTAMP_X")." as TIMESTAMP_X FROM b_file f
				WHERE f.ID between ".($bucket*$bucket_size)." AND ".(($bucket+1)*$bucket_size-1)
			);
			while($ar = $rs->Fetch())
				$arFiles[$ar["ID"]]=$ar;
			$CACHE_MANAGER->Set($cache_id, $arFiles);
		}
		return $arFiles;
	}

	function GetByID($FILE_ID)
	{
		global $DB, $CACHE_MANAGER;
		$FILE_ID = intval($FILE_ID);
		if(CACHED_b_file===false)
		{
			$strSql = "SELECT f.*,".$DB->DateToCharFunction("f.TIMESTAMP_X")." as TIMESTAMP_X FROM b_file f WHERE f.ID=".$FILE_ID;
			$z = $DB->Query($strSql, false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
		}
		else
		{
			$arFiles = CFile::GetFromCache($FILE_ID);
			$z = new CDBResult;
			$z->InitFromArray(array_key_exists($FILE_ID, $arFiles)? array($arFiles[$FILE_ID]) : array());
		}
		return $z;
	}

	function GetList($arOrder = Array(), $arFilter = Array(), $arParams = Array())
	{
		global $DB;
		$arSqlSearch = Array();
		$arSqlOrder = Array();
		$strSqlSearch = "";

		if(is_array($arFilter))
		{
			foreach($arFilter as $key => $val)
			{
				$key = strtoupper($key);

				if(substr($key, 0, 1)=="@")
				{
					$key = substr($key, 1);
					$strOperation = "IN";
					$arIn = explode(',', $val);
					$val = '';
					foreach($arIn as $v)
						$val .= ($val <> ''? ',':'')."'".$DB->ForSql(trim($v))."'";
				}
				else
				{
					$val = $DB->ForSql($val);
				}

				if($val == '')
					continue;

				switch($key)
				{
					case "MODULE":
					case "ID":
						if ($strOperation == "IN")
							$arSqlSearch[] = "f.".$key." IN (".$val.")";
						else
							$arSqlSearch[] = "f.".$key." = '".$val."'";
					break;
				}
			}
		}
		if (count($arSqlSearch) > 0)
			$strSqlSearch = " WHERE (".implode(") AND (", $arSqlSearch).")";

		$strSql =
			"SELECT f.*,".$DB->DateToCharFunction("f.TIMESTAMP_X")." as TIMESTAMP_X ".
			"FROM b_file f ".
			$strSqlSearch." ".
			"ORDER BY f.ID ASC";

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	function GetFileArray($FILE_ID)
	{
		if(!is_array($FILE_ID) && intval($FILE_ID) > 0)
		{
			if(CACHED_b_file===false)
			{
				 $res = CFile::GetByID($FILE_ID, true);
				 $arFile = $res->Fetch();
			}
			else
			{
				$res = CFile::GetFromCache($FILE_ID);
				$arFile = $res[$FILE_ID];
			}
			if($arFile)
			{
				$src = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
				$src = str_replace("//", "/", $src);
				if(defined("BX_IMG_SERVER"))
					$src = BX_IMG_SERVER.$src;
				$arFile["SRC"] = $src;

				return $arFile;
			}
		}
		return false;
	}

	function ConvertFilesToPost($source, &$target, $field=false)
	{
		if($field === false)
		{
			foreach($source as $field => $sub_source)
			{
				CAllFile::ConvertFilesToPost($sub_source, $target, $field);
			}
		}
		else
		{
			foreach($source as $id => $sub_source)
			{
				if(!array_key_exists($id, $target))
					$target[$id] = array();
				if(is_array($sub_source))
					CAllFile::ConvertFilesToPost($sub_source, $target[$id], $field);
				else
					$target[$id][$field] = $sub_source;
			}
		}
	}

	function CopyFile($FILE_ID)
	{
		global $DOCUMENT_ROOT, $DB;
		$err_mess = "FILE: ".__FILE__."<br>LINE: ";
		$z = CFile::GetByID($FILE_ID);
		if($zr = $z->Fetch())
		{
			/****************************** QUOTA ******************************/
			if (COption::GetOptionInt("main", "disk_space") > 0)
			{
				$quota = new CDiskQuota();
				if (!$quota->checkDiskQuota($zr))
					return false;
			}
			/****************************** QUOTA ******************************/

			$strDirName = $DOCUMENT_ROOT."/".(COption::GetOptionString("main", "upload_dir", "upload"));

			$strOldFile = $strDirName."/".$zr["SUBDIR"]."/".$zr["FILE_NAME"];
			$strOldFile = str_replace("//","/",$strOldFile);

			$ext = strrchr($zr["FILE_NAME"], ".");
			$newName = md5(uniqid(mt_rand())).$ext;
			$strNewFile = $strDirName."/".$zr["SUBDIR"]."/".$newName;
			$strNewFile = str_replace("//","/",$strNewFile);
			if(!copy($strOldFile, $strNewFile))
			{
				return false;
			}
			else
			{
				$arFields = array(
					"TIMESTAMP_X"	=> $DB->GetNowFunction(),
					"MODULE_ID"		=> "'".$DB->ForSql($zr["MODULE_ID"], 50)."'",
					"HEIGHT"		=> "'".$zr["HEIGHT"]."'",
					"WIDTH"			=> "'".$zr["WIDTH"]."'",
					"FILE_SIZE"		=> "'".$zr["FILE_SIZE"]."'",
					"ORIGINAL_NAME"	=> "'".$DB->ForSql($zr["ORIGINAL_NAME"], 255)."'",
					"DESCRIPTION"	=> "'".$DB->ForSql($zr["DESCRIPTION"], 255)."'",
					"CONTENT_TYPE"	=> "'".$DB->ForSql($zr["CONTENT_TYPE"], 255)."'",
					"SUBDIR"		=> "'".$DB->ForSql($zr["SUBDIR"], 255)."'",
					"FILE_NAME"		=> "'".$DB->ForSql($newName,255)."'"
					);
				$NEW_FILE_ID = $DB->Insert("b_file",$arFields, $err_mess.__LINE__);

				/****************************** QUOTA ******************************/
				if (COption::GetOptionInt("main", "disk_space") > 0)
				{
					CDiskQuota::updateDiskQuota("file", $zr["FILE_SIZE"], "copy");
				}
				/****************************** QUOTA ******************************/

				CFile::CleanCache($NEW_FILE_ID);
			}
		}
		return intval($NEW_FILE_ID);
	}

	function UpdateDesc($ID, $desc)
	{
		global $DB;
		$DB->Query("UPDATE b_file SET DESCRIPTION='".$DB->ForSql($desc, 255)."' WHERE ID=".intval($ID));
		CFile::CleanCache($ID);
	}

	function InputFile($strFieldName, $int_field_size, $strImageID, $strImageStorePath=false, $int_max_file_size=0, $strFileType="IMAGE", $field_file="class=typefile", $description_size=0, $field_text="class=typeinput", $field_checkbox="", $bShowNotes = True)
	{
		if($strImageStorePath===false)
			$strImageStorePath = COption::GetOptionString("main", "upload_dir", "upload");

		$strReturn1 = "";
		$strReturn2 = "";

		if($int_max_file_size != 0)
			$strReturn1 = $strReturn."<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"".$int_max_file_size."\" /> ";

		$strReturn1 = $strReturn1.' <input name="'.$strFieldName.'" '.$field_file.'  size="'.$int_field_size.'" type="file" />';
		global $DOCUMENT_ROOT, $DB;
		$strDescription = "";
		$strImageID=IntVal($strImageID);
		if($strImageID > 0)
		{
			$db_img = CFile::GetByID($strImageID);
			$db_img_arr = $db_img->Fetch();
			if($db_img_arr)
			{
				$strDescription = $db_img_arr["DESCRIPTION"];
				$sImagePath = "/".$strImageStorePath."/".$db_img_arr["SUBDIR"]."/".$db_img_arr["FILE_NAME"];
				$sImagePath = str_replace("//","/",$sImagePath);
				if(($p=strpos($strFieldName, "["))>0)
				{
					$strDelName = substr($strFieldName, 0, $p)."_del".substr($strFieldName, $p);
					$strOldName = substr($strFieldName, 0, $p)."_old".substr($strFieldName, $p);
				}
				else
				{
					$strDelName = $strFieldName."_del";
					$strOldName = $strFieldName."_old";
				}
				//$strReturn = $strReturn."<input type=\"hidden\" name=\"".$strOldName."\" value=\"".$strImageID."\">";

				if($bShowNotes)
				{
					if(file_exists($DOCUMENT_ROOT.$sImagePath))
					{
						$strReturn2 = $strReturn."<br>&nbsp;".GetMessage("FILE_TEXT").": ".$sImagePath;
						if(strtoupper($strFileType)=="IMAGE")
						{
							$intWidth = intval($db_img_arr["WIDTH"]);
							$intHeight = intval($db_img_arr["HEIGHT"]);
							if($intWidth>0 && $intHeight>0)
							{
								$strReturn2 = $strReturn2."<br>&nbsp;".GetMessage("FILE_WIDTH").": $intWidth";
								$strReturn2 = $strReturn2."<br>&nbsp;".GetMessage("FILE_HEIGHT").": $intHeight";
							}
						}
						$a = array("b", "Kb", "Mb", "Gb");
						$pos = 0;
						$size = $db_img_arr["FILE_SIZE"];
						while($size>=1024) {$size /= 1024; $pos++;}
						$intSize = round($size,2)." ".$a[$pos];
						$strReturn2 = $strReturn2."<br>&nbsp;".GetMessage("FILE_SIZE").": $intSize";
					}
					else
					{
						$strReturn2 = $strReturn2."<br>".GetMessage("FILE_NOT_FOUND").": ".$sImagePath;
					}
				}
				$strReturn2 = $strReturn2."<br><input ".$field_checkbox." type=\"checkbox\" name=\"".$strDelName."\" value=\"Y\" id=\"".$strDelName."\" /> <label for=\"".$strDelName."\">".GetMessage("FILE_DELETE")."</label>";
			}
		}

		$strReturn =
				$strReturn1.
				(
					$description_size>0
				?
					'<br>'.
					/*'Description: '.*/'<input type="text" value="'.htmlspecialchars($strDescription).'" name="'.$strFieldName.'_descr" '.$field_text.' size="'.$description_size.'" title="'.GetMessage("MAIN_FIELD_FILE_DESC").'" />'
				:
					''
				).
				$strReturn2;
		return $strReturn;
	}

	function FormatSize($size, $precision = 2)
	{
		static $a = array("b", "Kb", "Mb", "Gb", "Tb");
		$pos = 0;
		while($size >= 1024 && $pos < 4)
		{
			$size /= 1024;
			$pos++;
		}
		return round($size, $precision)." ".$a[$pos];
	}

	function GetImageExtensions()
	{
		return "jpg,bmp,jpeg,jpe,gif,png";
	}

	function GetFlashExtensions()
	{
		return "swf";
	}

	function IsImage($filename, $mime_type=false)
	{
		$filename = trim($filename, ". \r\n\t");
		$arr = explode(".", $filename);
		$ext = strtoupper($arr[count($arr)-1]);
		if(strlen($ext)>0)
		{
			if(in_array($ext, explode(",", strtoupper(CFile::GetImageExtensions()))))
				if(strpos($mime_type, "image/")!==false || $mime_type===false) return true;
		}
		return false;
	}

	function CheckImageFile($arFile, $iMaxSize=0, $iMaxWidth=0, $iMaxHeight=0, $access_typies=array())
	{
		if(strlen($arFile["name"])<=0)
			return "";

		$file_type = GetFileType($arFile["name"]);
		// если тип файла не входит в массив допустимых типов то
		// присваиваем ему тип IMAGE по умолчанию
		if(!in_array($file_type, $access_typies))
			$file_type = "IMAGE";

		switch ($file_type)
		{
			case "FLASH":
				$res = CFile::CheckFile($arFile, $iMaxSize, "application/x-shockwave-flash", CFile::GetFlashExtensions());
				break;
			default:
				$res = CFile::CheckFile($arFile, $iMaxSize, "image/", CFile::GetImageExtensions());
		}

		if(strlen($res)>0)
			return $res;

		$imgArray = getimagesize($arFile["tmp_name"]);

		if(is_array($imgArray))
		{
/*
			$imgfname = $arFile["tmp_name"];
			$imghandle = fopen($imgfname, "rb");
			$imgcontents = fread($imghandle, filesize($imgfname));
			fclose($imghandle);
			'<[a-z0-9]([\x0B\x00][a-z0-9]*)[ \r\n\t\x00\x0B>]'
			if(preg_match("'<script'i", $imgcontents) || )
				return GetMessage("FILE_BAD_FILE_TYPE").".<br>";
*/
			$intWIDTH = $imgArray[0];
			$intHEIGHT = $imgArray[1];
		}
		else
			return GetMessage("FILE_BAD_FILE_TYPE").".<br>";

		//проверка на максимальный размер картинки (ширина/высота)
		if($iMaxWidth > 0 && ($intWIDTH > $iMaxWidth || $intWIDTH == 0) || $iMaxHeight > 0 && ($intHEIGHT > $iMaxHeight || $intHEIGHT == 0))
			return GetMessage("FILE_BAD_MAX_RESOLUTION")." (".$iMaxWidth." * ".$iMaxHeight." ".GetMessage("main_include_dots").").<br>";
	}

	function CheckFile($arFile, $intMaxSize=0, $strMimeType=false, $strExt=false)
	{
		/****************************** QUOTA ******************************/
		if (COption::GetOptionInt("main", "disk_space") > 0)
		{
			$quota = new CDiskQuota;
			if (!$quota->checkDiskQuota($arFile))
				return $quota->LAST_ERROR;
		}
		/****************************** QUOTA ******************************/
		if(strlen($arFile["name"])<=0)
			return "";

		if(COption::GetOptionString("main", "save_original_file_name", "N")=="Y" && COption::GetOptionString("main", "convert_original_file_name", "Y")!="Y")
		{
			$filename = basename($arFile["name"]);
			if(preg_match('/[^'.BX_VALID_FILENAME_SYMBOLS.']/', $filename))
				return GetMessage("MAIN_BAD_FILENAME");
		}

		if($intMaxSize>0 && intval($arFile["size"])>$intMaxSize)
		{
			return GetMessage("FILE_BAD_SIZE")." (".$intMaxSize." ".GetMessage("main_include_bytes").").";
		}

		if($strExt)
		{
			$strFileExt = strrchr($arFile["name"], ".");
			if(strlen($strFileExt) <= 0 )
				return GetMessage("FILE_BAD_TYPE");
		}

		//Check mime_type and ext
		if($strMimeType!==false && substr($arFile["type"], 0, strlen($strMimeType)) != $strMimeType)
			return GetMessage("FILE_BAD_TYPE")."!";

		if($strExt===false)
			return "";

		$IsExtCorrect = true;
		if($strExt)
		{
			$IsExtCorrect=false;
			$tok = strtok($strExt,",");
			while($tok)
			{
				if(".".strtoupper(trim($tok)) == strtoupper(trim($strFileExt)))
				{
					$IsExtCorrect=true;
					break;
				}
				$tok = strtok(",");
			}
		}

		if($IsExtCorrect)
			return "";

		return GetMessage("FILE_BAD_TYPE")." (".$strFileExt.")!";
	}

	function ShowFile($iFileID, $max_file_size=0, $iMaxW=0, $iMaxH=0, $bPopup=false, $sParams=false, $sPopupTitle=false, $iSizeWHTTP=0, $iSizeHHTTP=0)
	{
		global $DB;
		$iFileID = IntVal($iFileID);
		$strResult = "";
		if($iFileID>0)
		{
			$res = CFile::GetByID($iFileID);
			if($ar = $res->Fetch())
			{
				$strFile = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$ar["SUBDIR"]."/".$ar["FILE_NAME"];
				$strFile = str_replace("//", "/", $strFile);

				$max_file_size = IntVal($max_file_size);
				if($max_file_size<=0)
					$max_file_size = 1000000000;
				$ct = $ar["CONTENT_TYPE"];
				if($max_file_size>=$ar["FILE_SIZE"] && (substr($ct, 0, 6) == "video/" || substr($ct, 0, 6) == "audio/"))
					$strResult =
						'<OBJECT ID="WMP64" WIDTH="'.($iMaxW>0?$iMaxW:'250').'" HEIGHT="'.(substr($ct, 0, 6) == "audio/"?'45':($iMaxH>0?$iMaxH:'220')).'" CLASSID="CLSID:22D6f312-B0F6-11D0-94AB-0080C74C7E95" STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject"> '.
						'<PARAM NAME="AutoStart" VALUE="false"> '.
						'<PARAM NAME="ShowDisplay" VALUE="false">'.
						'<PARAM NAME="ShowControls" VALUE="true" >'.
						'<PARAM NAME="ShowStatusBar" VALUE="0">'.
						'<PARAM NAME="FileName" VALUE="'.$strFile.'"> '.
						'</OBJECT>';
				elseif($max_file_size>=$ar["FILE_SIZE"] && substr($ct, 0, 6) == "image/")
					$strResult = ShowImage($strFile, $iMaxW, $iMaxH, $sParams, "", $bPopup, $sPopupTitle, $iSizeWHTTP, $iSizeHHTTP);
				else
					$strResult = ' [ <a href="'.$strFile.'" title="'.GetMessage("FILE_FILE_DOWNLOAD").'">'.GetMessage("FILE_DOWNLOAD").'</a> ] ';
			}
			return $strResult;
		}
		return "";
	}

	function DisableJSFunction($b=true)
	{
		global $SHOWIMAGEFIRST;
		$SHOWIMAGEFIRST = $b;
	}

	function OutputJSImgShw()
	{
		global $SHOWIMAGEFIRST;
		if(!defined("ADMIN_SECTION") && $SHOWIMAGEFIRST!==true)
		{
			echo
'<script type="text/javascript">
function ImgShw(ID, width, height, alt)
{
	var scroll = "no";
	var top=0, left=0;
	if(width > screen.width-10 || height > screen.height-28) scroll = "yes";
	if(height < screen.height-28) top = Math.floor((screen.height - height)/2-14);
	if(width < screen.width-10) left = Math.floor((screen.width - width)/2-5);
	width = Math.min(width, screen.width-10);
	height = Math.min(height, screen.height-28);
	var wnd = window.open("","","scrollbars="+scroll+",resizable=yes,width="+width+",height="+height+",left="+left+",top="+top);
	wnd.document.write(
		"<html><head>"+
		"<"+"script type=\"text/javascript\">"+
		"function KeyPress(e)"+
		"{"+
		"	if (!e) e = window.event;"+
		"	if(e.keyCode == 27) "+
		"		window.close();"+
		"}"+
		"</"+"script>"+
		"<title>"+(alt == ""? "'.GetMessage("main_js_img_title").'":alt)+"</title></head>"+
		"<body topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" onKeyDown=\"KeyPress(arguments[0])\">"+
		"<img src=\""+ID+"\" border=\"0\" alt=\""+alt+"\" />"+
		"</body></html>"
	);
	wnd.document.close();
	wnd.focus();
}
</script>';

			$SHOWIMAGEFIRST=true;
		}
	}

	function _GetImgParams($strImage, $iSizeWHTTP=0, $iSizeHHTTP=0)
	{
		global $DB;
		if(strlen($strImage) <= 0)
			return false;
		$strAlt = "";
		if(IntVal($strImage)>0)
		{
			$db_img = CFile::GetByID($strImage);
			$db_img_arr = $db_img->Fetch();
			if($db_img_arr)
			{
				$strImage = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$db_img_arr["SUBDIR"]."/".$db_img_arr["FILE_NAME"];
				$strImage = str_replace("//","/",$strImage);
				if(defined("BX_IMG_SERVER"))
					$strImage = BX_IMG_SERVER.$strImage;
				$intWidth = intval($db_img_arr["WIDTH"]);
				$intHeight = intval($db_img_arr["HEIGHT"]);
				$strAlt = $db_img_arr["DESCRIPTION"];
			}
			else
				return false;
		}
		else
		{
			if(substr(strtolower($strImage), 0, 7)!="http://")
			{
				if(is_file($_SERVER["DOCUMENT_ROOT"].$strImage))
				{
					$arSize = getimagesize($_SERVER["DOCUMENT_ROOT"].$strImage);
					$intWidth = intval($arSize[0]);
					$intHeight = intval($arSize[1]);
				}
				else
					return false;
			}
			else
			{
				$intWidth = intval($iSizeWHTTP);
				$intHeight = intval($iSizeHHTTP);
			}
		}
		return Array("SRC"=>$strImage, "WIDTH"=>$intWidth, "HEIGHT"=>$intHeight, "ALT"=>$strAlt);
	}

	function GetPath($img_id)
	{
		$res = CFile::_GetImgParams($img_id);
		return $res["SRC"];
	}

	function ShowImage($strImage, $iMaxW=0, $iMaxH=0, $sParams=null, $strImageUrl="", $bPopup=false, $sPopupTitle=false, $iSizeWHTTP=0, $iSizeHHTTP=0)
	{
		global $DOCUMENT_ROOT, $DB;

		if(!($arImgParams = CFile::_GetImgParams($strImage, $iSizeWHTTP, $iSizeHHTTP)))
			return "";

		if($sParams === null || $sParams === false)
			$sParams = 'border="0"';

		$iMaxW = intval($iMaxW);
		$iMaxH = intval($iMaxH);

		$strImage = htmlspecialchars($arImgParams["SRC"]);
		$intWidth = $arImgParams["WIDTH"];
		$intHeight = $arImgParams["HEIGHT"];
		$strAlt = $arImgParams["ALT"];

		if(!preg_match("/(^|\\s)alt\\s*=\\s*([\"']?)(.*?)(\\2)/is", $sParams))
			$sParams .= ' alt="'.htmlspecialcharsEx($strAlt).'"';

		if($sPopupTitle===false)
			$sPopupTitle=GetMessage("FILE_ENLARGE");

		$file_type = GetFileType($strImage);
		switch($file_type):
			case "FLASH":
				$iWidth = $intWidth;
				$iHeight = $intHeight;
				if($iMaxW>0 && $iMaxH>0 && ($intWidth > $iMaxW || $intHeight > $iMaxH))
				{
					$coeff = ($intWidth/$iMaxW > $intHeight/$iMaxH? $intWidth/$iMaxW : $intHeight/$iMaxH);
					$iWidth = intval(roundEx($intHeight/$coeff));
					$iHeight = intval(roundEx($intWidth/$coeff));
				}
				$strReturn = '
					<object
						classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000"
						codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
						id="banner"
						WIDTH="'.$iWidth.'"
						HEIGHT="'.$iHeight.'"
						ALIGN="">
							<PARAM NAME="movie" VALUE="'.$strImage.'" />
							<PARAM NAME="quality" VALUE="high" />
							<PARAM NAME="bgcolor" VALUE="#FFFFFF" />
							<embed
								src="'.$strImage.'"
								quality="high"
								bgcolor="#FFFFFF"
								WIDTH="'.$iWidth.'"
								HEIGHT="'.$iHeight.'"
								NAME="banner"
								ALIGN=""
								TYPE="application/x-shockwave-flash"
								PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer">
							</embed>
					</object>
					';
				return $bPopup? $strReturn : print_url($strImageUrl, $strReturn);

			default:
				$strReturn = "<img src=\"".$strImage."\" ".$sParams." width=\"".$intWidth."\" height=\"".$intHeight."\" />";
				if($iMaxW > 0 && $iMaxH > 0) //need to check scale, maybe show actual size in the popup window
				{
					//check for max dimensions exceeding
					if($intWidth > $iMaxW || $intHeight > $iMaxH)
					{
						$coeff = ($intWidth/$iMaxW > $intHeight/$iMaxH? $intWidth/$iMaxW : $intHeight/$iMaxH);
						$strReturn = "<img src=\"".$strImage."\" ".$sParams." width=\"".intval(roundEx($intWidth/$coeff))."\" height=\"".intval(roundEx($intHeight/$coeff))."\" />";

						if($bPopup) //show in JS window
						{
							if(strlen($strImageUrl)>0)
							{
								$strReturn =
									'<a href="'.$strImageUrl.'" title="'.$sPopupTitle.'" target="_blank">'.
									'<img src="'.$strImage.'" '.$sParams.' width="'.intval(roundEx($intWidth/$coeff)).'" height="'.intval(roundEx($intHeight/$coeff)).' title="'.htmlspecialcharsEx($sPopupTitle).'" />'.
									'</a>';
							}
							else
							{
								CFile::OutputJSImgShw();

								$strReturn =
									"<a title=\"".$sPopupTitle."\" onclick=\"ImgShw('".CUtil::addslashes($strImage)."','".$intWidth."','".$intHeight."', '".CUtil::addslashes(htmlspecialcharsEx(htmlspecialcharsEx($strAlt)))."'); return false;\" href=\"".$strImage."\" target=\"_blank\">".
									"<img src=\"".$strImage."\" ".$sParams." width=\"".intval(roundEx($intWidth/$coeff))."\" height=\"".intval(roundEx($intHeight/$coeff))."\" /></a>";
							}
						}
					}
				}
				return $bPopup? $strReturn : print_url($strImageUrl, $strReturn);

		endswitch;

		return $bPopup? $strReturn : print_url($strImageUrl, $strReturn);
	}

	function Show2Images($strImage1, $strImage2, $iMaxW=0, $iMaxH=0, $sParams=false, $sPopupTitle=false, $iSizeWHTTP=0, $iSizeHHTTP=0)
	{
		global $DOCUMENT_ROOT, $DB;

		if($sPopupTitle===false)
			$sPopupTitle=GetMessage("FILE_ENLARGE");

		if(!($arImgParams = CFile::_GetImgParams($strImage1, $iSizeWHTTP, $iSizeHHTTP)))
			return "";

		if($sParams == false)
			$sParams = 'border="0"';

		$strImage1 = htmlspecialchars($arImgParams["SRC"]);
		$intWidth = $arImgParams["WIDTH"];
		$intHeight = $arImgParams["HEIGHT"];
		$strAlt = $arImgParams["ALT"];

		if(!preg_match("/(^|\\s)alt\\s*=\\s*([\"']?)(.*?)(\\2)/is", $sParams))
			$sParams .= ' alt="'.htmlspecialcharsEx($strAlt).'"';

		$coeff = 1;
		if($iMaxW > 0 && $iMaxH > 0 && ($intWidth > $iMaxW || $intHeight > $iMaxH))
		{
			$coeff = ($intWidth/$iMaxW > $intHeight/$iMaxH? $intWidth/$iMaxW : $intHeight/$iMaxH);
			$strReturn = "<img src=\"".$strImage1."\" ".$sParams." width=".intval(roundEx($intWidth/$coeff))." height=".intval(roundEx($intHeight/$coeff))." />";
		}

		if($arImgParams = CFile::_GetImgParams($strImage2, $iSizeWHTTP, $iSizeHHTTP))
		{
			$strImage2 = htmlspecialchars($arImgParams["SRC"]);
			$intWidth2 = $arImgParams["WIDTH"];
			$intHeight2 = $arImgParams["HEIGHT"];
			$strAlt2 = $arImgParams["ALT"];

			CFile::OutputJSImgShw();

			$strReturn =
				"<a title=\"".$sPopupTitle."\" onclick=\"ImgShw('".CUtil::addslashes($strImage2)."','".$intWidth2."','".$intHeight2."', '".CUtil::addslashes(htmlspecialcharsEx(htmlspecialcharsEx($strAlt2)))."'); return false;\" href=\"".$strImage2."\" target=_blank>".
				"<img src=\"".$strImage1."\" ".$sParams." width=".intval(roundEx($intWidth/$coeff))." height=".intval(roundEx($intHeight/$coeff))." /></a>";
		}

		return $strReturn;
	}

	function MakeFileArray($path, $mimetype=false)
	{
		$arFile = Array();
		if(intval($path)>0)
		{
			$res = CFile::GetByID($path);
			if($ar = $res->Fetch())
			{
				$arFile["name"] = (strlen($ar['ORIGINAL_NAME'])>0?$ar['ORIGINAL_NAME']:$ar['FILE_NAME']);
				$arFile["size"] = $ar['FILE_SIZE'];
				$arFile["tmp_name"] = preg_replace("#[\\\\\\/]+#", "/", $_SERVER['DOCUMENT_ROOT'].'/'.(COption::GetOptionString('main', 'upload_dir', 'upload')).'/'.$ar['SUBDIR'].'/'.$ar['FILE_NAME']);
				$arFile["type"] = $ar['CONTENT_TYPE'];
				$arFile["description"] = $ar['DESCRIPTION'];
				return $arFile;
			}
		}
		if (strpos($path, "http://")===false &&
			strpos($path, "ftp://")===false &&
			strpos($path, "php://")===false)
		{
			$path = preg_replace("#[\\\\\\/]+#", "/", $path);
			if(!file_exists($path)) return NULL;
			$arFile["name"] = basename($path);
			$arFile["size"] = filesize($path);
			$arFile["tmp_name"] = $path;
			$arFile["type"] = $mimetype;
			if(strlen($arFile["type"])<=0 && function_exists("mime_content_type"))
				$arFile["type"] = mime_content_type($path);

			if(strlen($arFile["type"])<=0 && function_exists("image_type_to_mime_type"))
			{
				$arTmp = getimagesize($path);
				$arFile["type"] = $arTmp["mime"];
			}

			if(strlen($arFile["type"])<=0)
			{
				$arTypes = Array("jpeg"=>"image/jpeg", "jpe"=>"image/jpeg", "jpg"=>"image/jpeg", "png"=>"image/png", "gif"=>"image/gif", "bmp"=>"image/bmp");
				$arFile["type"]= $arTypes[strtolower(substr($path, bxstrrpos($path, ".")+1))];
			}
		}
		else
		{
			$content = "";
			if ($fp = fopen($path,"rb"))
			{
				while (!feof($fp)) $content .= fgets($fp,1024);
				if (strlen($content)>0)
				{
					$file_name = basename($path);
					$bname = $_SERVER["DOCUMENT_ROOT"]."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/tmp";
					while(true)
					{
						$dir_add = substr(md5(uniqid(mt_rand(), true)), 0, 3);
						$temp_path = $bname."/".$dir_add."/".$file_name;
						if(!file_exists($temp_path)) break;
						if($i>=25)
						{
							$dir_add = md5(uniqid(mt_rand(), true));
							$temp_path = $bname."/".$dir_add."/".$file_name;
							break;
						}
					}
					if (RewriteFile($temp_path, $content)) $arFile = CFile::MakeFileArray($temp_path);
				}
				fclose($fp);
			}
		}

		if(strlen($arFile["type"])<=0)
			$arFile["type"] = "unknown";

		return $arFile;
	}

	function ChangeSubDir($module_id, $old_subdir, $new_subdir)
	{
		global $DB;

		if ($old_subdir!=$new_subdir)
		{
			$strSql = "
				UPDATE b_file
				SET SUBDIR = REPLACE(SUBDIR,'".$DB->ForSQL($old_subdir)."','".$DB->ForSQL($new_subdir)."')
				WHERE MODULE_ID='".$DB->ForSQL($module_id)."'
			";

			if($rs = $DB->Query($strSql, false, $err_mess.__LINE__))
			{
				$from = "/".COption::GetOptionString("main", "upload_dir", "upload")."/".$old_subdir;
				$to = "/".COption::GetOptionString("main", "upload_dir", "upload")."/".$new_subdir;
				CopyDirFiles($_SERVER["DOCUMENT_ROOT"].$from, $_SERVER["DOCUMENT_ROOT"].$to, true, true, true);
				//Reset All b_file cache
				$GLOBALS["CACHE_MANAGER"]->CleanDir("b_file");
			}
		}
	}

	function ResizeImage(&$arFile, $arSize, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL)
	{
		CheckDirPath($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/");

		$sourceFile = $arFile["tmp_name"];
		$destinationFile = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/".BaseName($sourceFile);

		if (CFile::ResizeImageFile($sourceFile, $destinationFile, $arSize, $resizeType))
		{
			$arFile["tmp_name"] = $destinationFile;
			$arImageSize = getimagesize($destinationFile);
			$arFile["type"] = $arImageSize["mime"];
			$arFile["size"] = filesize($arFile["tmp_name"]);

			return true;
		}

		return false;
	}

	function ResizeImageDeleteCache($arFile)
	{
		CheckDirPath($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/");
		if(SubStr($arFile["tmp_name"], 0, StrLen($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/")) == $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/")
			if(file_exists($arFile["tmp_name"]))
				unlink($arFile["tmp_name"]);
	}

	function ResizeImageGet($file, $arSize, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL, $bInitSizes = false)
	{
		if (!is_array($file) && IntVal($file) > 0)
		{
			$dbRes = CFile::GetByID(IntVal($file));
			$file = $dbRes->Fetch();
		}

		if (!is_array($file) || !array_key_exists("FILE_NAME", $file) || StrLen($file["FILE_NAME"]) <= 0)
			return false;

		if ($resizeType != BX_RESIZE_IMAGE_EXACT && $resizeType != BX_RESIZE_IMAGE_PROPORTIONAL_ALT)
			$resizeType != BX_RESIZE_IMAGE_PROPORTIONAL;

		if (!is_array($arSize))
			$arSize = array();
		if (!array_key_exists("width", $arSize) || IntVal($arSize["width"]) <= 0)
			$arSize["width"] = 0;
		if (!array_key_exists("height", $arSize) || IntVal($arSize["height"]) <= 0)
			$arSize["height"] = 0;
		$arSize["width"] = IntVal($arSize["width"]);
		$arSize["height"] = IntVal($arSize["height"]);

		$uploadDirName = COption::GetOptionString("main", "upload_dir", "upload");

		$imageFile = "/".$uploadDirName."/".$file["SUBDIR"]."/".$file["FILE_NAME"];

		if (($arSize["width"] <= 0 || $arSize["width"] >= $file["WIDTH"])
			&& ($arSize["height"] <= 0 || $arSize["height"] >= $file["HEIGHT"]))
		{
			return array("src" => $imageFile, "width" => IntVal($file["WIDTH"]), "height" => IntVal($file["HEIGHT"]));
		}

		$cacheImageFile = "/".$uploadDirName."/resize_cache/".$file["SUBDIR"]."/".$arSize["width"]."_".$arSize["height"]."_".$resizeType."/".$file["FILE_NAME"];

		$cacheImageFileCheck = $cacheImageFile;
		if ($file["CONTENT_TYPE"] == "image/bmp")
			$cacheImageFileCheck .= ".jpg";

		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$cacheImageFileCheck))
		{
			/****************************** QUOTA ******************************/
			$bDiskQuota = true;
			if (COption::GetOptionInt("main", "disk_space") > 0)
			{
				$quota = new CDiskQuota();
				$bDiskQuota = $quota->checkDiskQuota($file);
			}
			/****************************** QUOTA ******************************/

			if ($bDiskQuota)
			{
				$cacheImageFileTmp = $_SERVER["DOCUMENT_ROOT"].$cacheImageFile;
				if (CFile::ResizeImageFile($_SERVER["DOCUMENT_ROOT"].$imageFile, $cacheImageFileTmp, $arSize, $resizeType))
				{
					$cacheImageFile = SubStr($cacheImageFileTmp, StrLen($_SERVER["DOCUMENT_ROOT"]));

					/****************************** QUOTA ******************************/
					if (COption::GetOptionInt("main", "disk_space") > 0)
						CDiskQuota::updateDiskQuota("file", filesize($cacheImageFileTmp), "insert");
					/****************************** QUOTA ******************************/
				}
				else
				{
					$cacheImageFile = $imageFile;
				}
			}
			else
			{
				$cacheImageFile = $imageFile;
			}

			$cacheImageFileCheck = $cacheImageFile;
		}

		$arImageSize = array(0, 0);
		if ($bInitSizes)
			$arImageSize = getimagesize($_SERVER["DOCUMENT_ROOT"].$cacheImageFileCheck);

		return array("src" => $cacheImageFileCheck, "width" => IntVal($arImageSize[0]), "height" => IntVal($arImageSize[1]));
	}

	function ImageCreateFromBMP($filename)
	{
		if(!$f1 = fopen($filename,"rb"))
			return false;

		//1 : read and parse HEADER
		$FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
		if ($FILE['file_type'] != 19778)
			return false;

		//2 : read and parse BMP data
		$BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
		     '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
		     '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));

		$BMP['colors'] = pow(2,$BMP['bits_per_pixel']);

		if($BMP['colors_used'] > 0)
			$BMP['palette_size'] = $BMP['colors_used'];
		else
			$BMP['palette_size'] = $BMP['colors'];

		if ($BMP['size_bitmap'] == 0)
			$BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
		$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
		$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
		$BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] = 4-(4*$BMP['decal']);
		if ($BMP['decal'] == 4)
			$BMP['decal'] = 0;

		//3 : Read palette
		$PALETTE = array();
		if ($BMP['colors'] < 16777216)
		{
			$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
		}

		//4 : Create an image canvas to draw on
		$res = imagecreatetruecolor($BMP['width'],$BMP['height']);
		$VIDE = chr(0);

		if($BMP['bits_per_pixel'] == 32)
		{
			$dPY = $BMP['decal'];
			$width = $BMP['width'];
			$Y = $BMP['height'] - 1;
			while ($Y >= 0)
			{
				$X = 0;
				while($X < $width)
				{
					$COLOR = unpack("C4", fread($f1, 4));
					imagesetpixel($res, $X, $Y, ($COLOR[4]<<16) | ($COLOR[3]<<8) | ($COLOR[2]));
					$X++;
				}
				$Y--;
				if($dPY > 0)
					fread($f1, $dPY);
			}
		}
		elseif($BMP['bits_per_pixel'] == 24)
		{
			$dPY = $BMP['decal'];
			$width = $BMP['width'];
			$Y = $BMP['height'] - 1;
			while ($Y >= 0)
			{
				$X = 0;
				while($X < $width)
				{
					$COLOR = unpack("V", fread($f1, 3).$VIDE);
					imagesetpixel($res, $X, $Y, $COLOR[1]);
					$X++;
				}
				$Y--;
				if($dPY > 0)
					fread($f1, $dPY);
			}
		}
		elseif($BMP['bits_per_pixel'] == 16 && $BMP['compression'] == 0)
		{
			fseek($f1, $FILE['bitmap_offset'], SEEK_SET);
			$dPY = $BMP['decal'];
			$width = $BMP['width'];
			$Y = $BMP['height'] - 1;
			while ($Y >= 0)
			{
				$X = 0;
				while($X < $width)
				{
					$COLOR = unpack("C2", fread($f1, 2));
					$R = ($COLOR[2] >> 2)  & 0x1f;
					$G = (($COLOR[2] & 0x03) << 3) | ($COLOR[1] >> 5);
					$B = $COLOR[1] & 0x1f;
					imagesetpixel($res, $X, $Y, (($R*8)<<16) | (($G*8)<<8) | ($B*8));
					$X++;
				}
				$Y--;
				if($dPY > 0)
					fread($f1, $dPY);
			}
		}
		elseif($BMP['bits_per_pixel'] == 16)
		{
			fseek($f1, $FILE['bitmap_offset'], SEEK_SET);
			$dPY = $BMP['decal'];
			$width = $BMP['width'];
			$Y = $BMP['height'] - 1;
			while ($Y >= 0)
			{
				$X = 0;
				while($X < $width)
				{
					$COLOR = unpack("C2", fread($f1, 2));
					$R = $COLOR[2] >> 3;
					$G = ($COLOR[2] & 0x07) << 3 | ($COLOR[1] >> 5);
					$B = $COLOR[1] & 0x1f;
					imagesetpixel($res, $X, $Y, (($R*8)<<16) | (($G*4)<<8) | ($B*8));
					$X++;
				}
				$Y--;
				if($dPY > 0)
					fread($f1, $dPY);
			}
		}
		elseif($BMP['bits_per_pixel'] == 8)
		{
			fseek($f1, $FILE['bitmap_offset'], SEEK_SET);
			$dPY = $BMP['decal'];
			$width = $BMP['width'];
			$Y = $BMP['height'] - 1;
			while ($Y >= 0)
			{
				$X = 0;
				while($X < $width)
				{
					$COLOR = unpack("n", $VIDE.fread($f1, 1));
					imagesetpixel($res, $X, $Y, $PALETTE[$COLOR[1]+1]);
					$X++;
				}
				$Y--;
				if($dPY > 0)
					fread($f1, $dPY);
			}
		}
		else
		{
			$IMG = fread($f1,$BMP['size_bitmap']);
			$P = 0;
			$Y = $BMP['height']-1;
			while ($Y >= 0)
			{
				$X=0;
				while ($X < $BMP['width'])
				{
					if ($BMP['bits_per_pixel'] == 4)
					{
						$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
						if (($P*2)%2 == 0)
							$COLOR[1] = ($COLOR[1] >> 4) ;
						else
							$COLOR[1] = ($COLOR[1] & 0x0F);
						$COLOR[1] = $PALETTE[$COLOR[1]+1];
					}
					elseif ($BMP['bits_per_pixel'] == 1)
					{
						$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
						if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
						elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
						elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
						elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
						elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
						elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
						elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
						elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
						$COLOR[1] = $PALETTE[$COLOR[1]+1];
					}
					else
						return FALSE;
					imagesetpixel($res,$X,$Y,$COLOR[1]);
					$X++;
					$P += $BMP['bytes_per_pixel'];
				}
				$Y--;
				$P+=$BMP['decal'];
			}
		}

		fclose($f1);

		return $res;
	}

	function ResizeImageFile($sourceFile, &$destinationFile, $arSize, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL, $arWaterMark = array(), $jpgQuality=false)
	{
		static $bGD2 = false;
		static $bGD2Initial = false;

		if (!$bGD2Initial && function_exists("gd_info"))
		{
			$arGDInfo = gd_info();
			$bGD2 = ((StrPos($arGDInfo['GD Version'], "2.") !== false) ? true : false);
			$bGD2Initial = true;
		}

		$imageInput = false;
		$bNeedCreatePicture = false;
		$picture = false;

		if ($resizeType != BX_RESIZE_IMAGE_EXACT && $resizeType != BX_RESIZE_IMAGE_PROPORTIONAL_ALT)
			$resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;

		if (!is_array($arSize))
			$arSize = array();
		if (!array_key_exists("width", $arSize) || IntVal($arSize["width"]) <= 0)
			$arSize["width"] = 0;
		if (!array_key_exists("height", $arSize) || IntVal($arSize["height"]) <= 0)
			$arSize["height"] = 0;
		$arSize["width"] = IntVal($arSize["width"]);
		$arSize["height"] = IntVal($arSize["height"]);

		$arSourceSize = array("x" => 0, "y" => 0, "width" => 0, "height" => 0);
		$arDestinationSize = array("x" => 0, "y" => 0, "width" => 0, "height" => 0);

		$arSourceFileSizeTmp = getimagesize($sourceFile);
		if (!in_array($arSourceFileSizeTmp[2], array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_BMP)))
			return false;

		if (!file_exists($sourceFile) || !is_file($sourceFile))
			return false;

		if (CopyDirFiles($sourceFile, $destinationFile))
		{
			if (!is_array($arWaterMark))
				$arWaterMark = array();

			$sourceImage = false;
			switch ($arSourceFileSizeTmp[2])
			{
				case IMAGETYPE_GIF:
					$sourceImage = imagecreatefromgif($sourceFile);
					break;
				case IMAGETYPE_PNG:
					$sourceImage = imagecreatefrompng($sourceFile);
					break;
				case IMAGETYPE_BMP:
					$sourceImage = CFile::ImageCreateFromBMP($sourceFile);
					break;
				default:
					$sourceImage = imagecreatefromjpeg($sourceFile);
					break;
			}

			$sourceImageWidth = IntVal(imagesx($sourceImage));
			$sourceImageHeight = IntVal(imagesy($sourceImage));

			if ($sourceImageWidth > 0 && $sourceImageHeight > 0)
			{
				if ($arSize["width"] > 0 && $arSize["height"] > 0)
				{
					switch ($resizeType)
					{
						case BX_RESIZE_IMAGE_EXACT:
							$bNeedCreatePicture = true;
							$width = Max($sourceImageWidth, $sourceImageHeight);
							$height = Min($sourceImageWidth, $sourceImageHeight);

							$iResizeCoeff = Max($arSize["width"] / $width, $arSize["height"] / $height);

							$arDestinationSize["width"] = IntVal($arSize["width"]);
							$arDestinationSize["height"] = IntVal($arSize["height"]);

							if ($iResizeCoeff > 0)
							{
								$arSourceSize["x"] = ((($sourceImageWidth * $iResizeCoeff - $arSize["width"]) / 2) / $iResizeCoeff);
								$arSourceSize["y"] = ((($sourceImageHeight * $iResizeCoeff - $arSize["height"]) / 2) / $iResizeCoeff);
								$arSourceSize["width"] = $arSize["width"] / $iResizeCoeff;
								$arSourceSize["height"] = $arSize["height"] / $iResizeCoeff;
							}

							break;
						default:
							if ($resizeType == BX_RESIZE_IMAGE_PROPORTIONAL_ALT)
							{
								$width = Max($sourceImageWidth, $sourceImageHeight);
								$height = Min($sourceImageWidth, $sourceImageHeight);
							}
							else
							{
								$width = $sourceImageWidth;
								$height = $sourceImageHeight;
							}
							$ResizeCoeff["width"] = $arSize["width"] / $width;
							$ResizeCoeff["height"] = $arSize["height"] / $height;

							$iResizeCoeff = Min($ResizeCoeff["width"], $ResizeCoeff["height"]);
							$iResizeCoeff = ((0 < $iResizeCoeff) && ($iResizeCoeff < 1) ? $iResizeCoeff : 1);
							$bNeedCreatePicture = ($iResizeCoeff != 1 ? true : false);

							$arDestinationSize["width"] = intVal($iResizeCoeff * $sourceImageWidth);
							$arDestinationSize["height"] = intVal($iResizeCoeff * $sourceImageHeight);

							$arSourceSize["x"] = 0;
							$arSourceSize["y"] = 0;
							$arSourceSize["width"] = $sourceImageWidth;
							$arSourceSize["height"] = $sourceImageHeight;
							break;
					}
				}
				else
				{
					$arSourceSize = array("x" => 0, "y" => 0, "width" => $sourceImageWidth, "height" => $sourceImageHeight);
					$arDestinationSize = array("x" => 0, "y" => 0, "width" => $sourceImageWidth, "height" => $sourceImageHeight);

					$arSize["width"] = $sourceImageWidth;
					$arSize["height"] = $sourceImageHeight;
				}

				$bNeedCreatePicture = (!empty($arWaterMark["text"]) ? true : $bNeedCreatePicture);

				if ($bNeedCreatePicture)
				{
					if ($bGD2)
					{
						$picture = ImageCreateTrueColor($arDestinationSize["width"], $arDestinationSize["height"]);
						if($arSourceFileSizeTmp[2] == IMAGETYPE_PNG)
						{
							$transparentcolor = imagecolorallocatealpha($picture, 0, 0, 0, 127);
							imagefilledrectangle($picture, 0, 0, $arDestinationSize["width"], $arDestinationSize["height"], $transparentcolor);
							$transparentcolor = imagecolortransparent($picture, $transparentcolor);


							imagealphablending($picture, false);
							imagecopyresampled($picture, $sourceImage,
								0, 0, $arSourceSize["x"], $arSourceSize["y"],
								$arDestinationSize["width"], $arDestinationSize["height"], $arSourceSize["width"], $arSourceSize["height"]);
							imagealphablending($picture, true);
						}
						elseif($arSourceFileSizeTmp[2] == IMAGETYPE_GIF)
						{
							imagetruecolortopalette($picture, true, imagecolorstotal($sourceImage));
							imagepalettecopy($picture, $sourceImage);

							//Save transparency for GIFs
							$transparentcolor = imagecolortransparent($sourceImage);
							if($transparentcolor >= 0 && $transparentcolor < imagecolorstotal($sourceImage))
							{
								$transparentcolor = imagecolortransparent($picture, $transparentcolor);
								imagefilledrectangle($picture, 0, 0, $arDestinationSize["width"], $arDestinationSize["height"], $transparentcolor);
							}

							imagecopyresampled($picture, $sourceImage,
								0, 0, $arSourceSize["x"], $arSourceSize["y"],
								$arDestinationSize["width"], $arDestinationSize["height"], $arSourceSize["width"], $arSourceSize["height"]);
						}
						else
						{
							imagecopyresampled($picture, $sourceImage,
								0, 0, $arSourceSize["x"], $arSourceSize["y"],
								$arDestinationSize["width"], $arDestinationSize["height"], $arSourceSize["width"], $arSourceSize["height"]);
						}
					}
					else
					{
						$picture = ImageCreate($arDestinationSize["width"], $arDestinationSize["height"]);
						imagecopyresized($picture, $sourceImage,
							0, 0, $arSourceSize["x"], $arSourceSize["y"],
							$arDestinationSize["width"], $arDestinationSize["height"], $arSourceSize["width"], $arSourceSize["height"]);
					}
				}

				if (!empty($arWaterMark["text"]) && !empty($arWaterMark["path_to_font"])
					&& $arDestinationSize["width"] >= $arWaterMark["min_size_picture"]
					&& file_exists($arWaterMark["path_to_font"]))
				{
					$arColor = array("red" => 255, "green" => 255, "blue" => 255);
					$sColor = preg_replace("/[^a-z0-9]/is", "", $arWaterMark["color"]);
					if (strLen($sColor) == 6)
					{
						$arColor = array(
							"red" => hexdec(substr($sColor, 0, 2)),
							"green" => hexdec(substr($sColor, 2, 2)),
							"blue" => hexdec(substr($sColor, 4, 2)));
						$barColor = array(
							"red" => substr($sColor, 0, 2),
							"green" => substr($sColor, 2, 2),
							"blue" => substr($sColor, 4, 2));
					}
					if ($arWaterMark["size"] == "big")
					{
						$iSize = $arSize["width"] * 0.07;
						$iSize = ($iSize > 75 ? 75 : $iSize);
					}
					elseif ($arWaterMark["size"] == "small")
					{
						$iSize = $arSize["width"] * 0.03;
						$iSize = ($iSize > 35 ? 35 : $iSize);
					}
					else
					{
						$iSize = $arSize["width"] * 0.05;
						$iSize = ($iSize > 55 ? 55 : $iSize);
					}
					if ($iSize * strLen($arWaterMark["text"]) * 0.7 > $arDestinationSize["width"])
					{
						$iSize = intVal($arDestinationSize["width"] / (strLen($arWaterMark["text"]) * 0.7));
					}

					if ($iSize < 8)
						$iSize = 8;

					$watermark_position = array(
						"x" => 5,
						"y" => $iSize + 5,
						"width" => (strLen($arWaterMark["text"]) * 0.7 + 1) * $iSize,
						"height" => $iSize
					);
					if (!$bGD2)
					{
						$watermark_position["width"] = strLen($arWaterMark["text"]) * imagefontwidth(5);
						$watermark_position["height"] = imagefontheight(5);
					}

					if (substr($arWaterMark["position"], 0, 1) == "m")
					{
						$watermark_position["y"] = intVal(($arDestinationSize["height"] - $watermark_position["height"]) / 2);
						if ($watermark_position["y"] <= 0)
							$watermark_position["y"] = $watermark_position["height"];
					}
					elseif (substr($arWaterMark["position"], 0, 1) == "b")
					{
						$watermark_position["y"] = intVal(($arDestinationSize["height"] - $watermark_position["height"]));
						if ($watermark_position["y"] <= 0)
							$watermark_position["y"] = $watermark_position["height"];
					}

					if (substr($arWaterMark["position"], 1, 1) == "c")
					{
						$watermark_position["x"] = intVal(($arDestinationSize["width"] - $watermark_position["width"]) / 2);
						if ($watermark_position["x"] <= 0)
							$watermark_position["x"] = 5;
					}
					elseif (substr($arWaterMark["position"], 1, 1) == "r")
					{
						$watermark_position["x"] = intVal(($arDestinationSize["width"] - $watermark_position["width"]));
						if ($watermark_position["x"] <= 0)
							$watermark_position["x"] = 5;
					}

					$text_color = imagecolorallocate($picture, $arColor["red"], $arColor["green"], $arColor["blue"]);
					if ($bGD2)
					{
						if (function_exists("utf8_encode"))
						{
							$text = $GLOBALS["APPLICATION"]->ConvertCharset($arWaterMark["text"], LANG_CHARSET, "UTF-8");
							if ($arWaterMark["use_copyright"] != "N")
								$text = utf8_encode("&#169;").$text;
						}
						else
						{
							$text = $GLOBALS["APPLICATION"]->ConvertCharset($arWaterMark["text"], LANG_CHARSET, "UTF-8");
							if ($arWaterMark["use_copyright"] != "N")
								$text = "©".$text;
						}

						imagettftext($picture, $iSize, 0, $watermark_position["x"], $watermark_position["y"], $text_color, $arWaterMark["path_to_font"], $text);
					}
					else
					{
						imagestring($picture, 3, $watermark_position["x"], $watermark_position["y"], $arWaterMark["text"], $text_color);
					}
				}

				if ($bNeedCreatePicture)
				{
					if(file_exists($destinationFile))
						unlink($destinationFile);
					switch ($arSourceFileSizeTmp[2])
					{
						case IMAGETYPE_GIF:
							imagegif($picture, $destinationFile);
							break;
						case IMAGETYPE_PNG:
							imagealphablending($picture, false );
							imagesavealpha($picture, true);
							imagepng($picture, $destinationFile);
							break;
						default:
							if ($arSourceFileSizeTmp[2] == IMAGETYPE_BMP)
								$destinationFile .= ".jpg";
							if($jpgQuality === false)
								$jpgQuality = intval(COption::GetOptionString('main', 'image_resize_quality', '95'));
							if($jpgQuality <= 0 || $jpgQuality > 100)
								$jpgQuality = 95;
							imagejpeg($picture, $destinationFile, $jpgQuality);
							break;
					}
					imagedestroy($picture);
				}
			}

			return true;
		}

		return false;
	}
}
?>