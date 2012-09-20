<?
IncludeModuleLangFile(__FILE__);

class CMainAdmin
{
	function GetTemplateList($rel_dir)
	{
		$arrTemplate = array();
		$arrTemplateDir = array();
		$rel_dir = str_replace("\\", "/", $rel_dir);

		$path = BX_PERSONAL_ROOT."/templates/";
		$handle=@opendir($_SERVER["DOCUMENT_ROOT"].$path);
		if($handle)
		{
			while (false!==($dir_name = readdir($handle)))
			{
				if (is_dir($_SERVER["DOCUMENT_ROOT"].$path.$dir_name) && $dir_name!="." && $dir_name!="..")
					$arrTemplateDir[] = $path.$dir_name;
			}
			closedir($handle);
		}
		$arrS = explode("/", $rel_dir);
		if (is_array($arrS) && count($arrS)>0)
		{
			$module_id = $arrS[0];
			$path = "/bitrix/modules/".$module_id."/install/templates/";
			if (is_dir($_SERVER["DOCUMENT_ROOT"].$path)) $arrTemplateDir[] = $path;
		}

		if (is_array($arrTemplateDir) && count($arrTemplateDir)>0)
		{
			foreach($arrTemplateDir as $template_dir)
			{
				$path = $template_dir."/".$rel_dir;
				$path = str_replace("\\", "/", $path);
				$path = str_replace("//", "/", $path);
				$handle=@opendir($_SERVER["DOCUMENT_ROOT"].$path);
				if($handle)
				{
					while (false!==($file_name = readdir($handle)))
					{
						if (is_file($_SERVER["DOCUMENT_ROOT"].$path.$file_name) && $file_name!="." && $file_name!="..")
							$arrTemplate[$file_name] = $file_name;
					}
					closedir($handle);
				}
			}
		}
		$arrTemplate = array_values($arrTemplate);
		usort($arrTemplate, create_function('$v1,$v2','if ($v1>$v2) return 1; elseif ($v1<$v2) return -1;'));
		return $arrTemplate;
	}
}

class CTemplates
{
	function GetList($arFilter = Array(), $arCurrentValues = Array(), $template_id = Array())
	{
		if(!is_set($arFilter, "FOLDER"))
		{
			$arr = CTemplates::GetFolderList();
			$arFilter["FOLDER"] = array_keys($arr);
		}

		foreach($arFilter["FOLDER"] as $folder)
		{
			$arTemplates[$folder] = Array();
			$arPath = Array(
					"/bitrix/modules/".$folder."/install/templates/",
					BX_PERSONAL_ROOT."/templates/.default/"
				);
			if(is_array($template_id)>0)
			{
				foreach($template_id as $v)
					$arPath[] = BX_PERSONAL_ROOT."/templates/".$v."/";
			}
			elseif(strlen($template_id)>0)
				$arPath[] = BX_PERSONAL_ROOT."/templates/".$template_id."/";

			for($i=0; $i<count($arPath); $i++)
			{
				$path = $arPath[$i];
				CTemplates::__FindTemplates($path, $arTemplates[$folder], $arCurrentValues, $folder);
			}
			if(count($arTemplates[$folder])<=0)
				unset($arTemplates[$folder]);
			else
			{
				$arTemplate = $arTemplates[$folder];
				$arTemplateTemp = Array();
				$arSeparators = Array();
				foreach($arTemplate as $k=>$val)
					if($val["SEPARATOR"]=="Y")
						$arSeparators[$k] = $val;

				foreach($arSeparators as $sep_id=>$val_sep)
				{
					$arTemplateTemp[$sep_id] = $val_sep;
					reset($arTemplate);
					while(list($k, $val) = each($arTemplate))
					{
						if($val===false)
							continue;

						if($k==$sep_id)
						{
							while(list($k, $val) = each ($arTemplate))
							{
								if($val === false)
									continue;
								if($val["SEPARATOR"]=="Y")
									break;
								if(strlen($val["PARENT"])>0 && $val["PARENT"]!=$sep_id)
									continue;

								$arTemplateTemp[$k] = $val;
								$arTemplate[$k] = false;
							}
							//continue;
						}
						if($val["PARENT"]==$sep_id)
						{
							$arTemplateTemp[$k] = $val;
							$arTemplate[$k] = false;
						}
					}
				}

				$bW = true;
				foreach($arTemplate as $k=>$val)
				{
					if($val===false || $val["SEPARATOR"] == "Y")
						continue;
					if($bW)
					{
						if(count($arSeparators)>0)
							$arTemplateTemp[md5(uniqid(rand(), true))] = Array("NAME"=> "----------------------------", "SEPARATOR"=>"Y");
						$bW = false;
					}
					$arTemplateTemp[$k] = $val;
					$arTemplate[$k] = false;
				}

				$arTemplates[$folder] = $arTemplateTemp;
			}
		}
		return $arTemplates;
	}

	function GetByID($id, $arCurrentValues = Array(), $templateID = Array())
	{
		$folder = substr($id, 0, strpos($id, "/"));
		$arRes = CTemplates::GetList(Array("FOLDER"=>Array($folder)), $arCurrentValues, $templateID);
		$all_templates = $arRes[$folder];
		if(is_set($all_templates, $id))
			return $all_templates[$id];
		return false;
	}

	function __FindTemplates($root, &$arTemplates, $arCurrentValues=Array(), $init="")
	{
		if(is_dir($_SERVER['DOCUMENT_ROOT'].$root.$init))
		{
			$arTemplateDescription = Array();
			if(file_exists($_SERVER['DOCUMENT_ROOT'].$root.$init."/.description.php"))
			{
				include($_SERVER['DOCUMENT_ROOT'].$root.$init."/.description.php");
				foreach($arTemplateDescription as $path=>$desc)
				{
					$desc["REAL_PATH"] = $root.$init."/".$path;
					if(strlen($desc["PARENT"])>0)
						$desc["PARENT"] = $init."/".$desc["PARENT"];
					$arTemplates[$init."/".$path] = $desc;
				}
			}

			if($handle = @opendir($_SERVER["DOCUMENT_ROOT"].$root.$init))
			{
				while(($file = readdir($handle)) !== false)
				{
					if($file == "." || $file == "..") continue;
					CTemplates::__FindTemplates($root, $arTemplates, $arCurrentValues, $init."/".$file);
				}
			}
		}
	}

	function GetFolderList($template_id =  false)
	{
		$arTemplateFolders = Array();
		$arTemplateFoldersSort = Array();
		$path = "/bitrix/modules";
		if($handle = @opendir($_SERVER["DOCUMENT_ROOT"].$path))
		{
			while(($module_name = readdir($handle)) !== false)
			{
				if($module_name == "." || $module_name == "..") continue;
				if(is_dir($_SERVER["DOCUMENT_ROOT"].$path."/".$module_name))
				{
					$path_mod = $path."/".$module_name."/install/templates";
					if(file_exists($_SERVER["DOCUMENT_ROOT"].$path_mod))
					{
						if($handle_mod = @opendir($_SERVER["DOCUMENT_ROOT"].$path_mod))
						{
							while(($file_templ = readdir($handle_mod)) !== false)
							{
								if($file_templ == "." || $file_templ == ".." || $file_templ=="lang") continue;
								if(is_dir($_SERVER["DOCUMENT_ROOT"].$path_mod."/".$file_templ))
								{
									$sSectionName = false;
									$iSort = 500;
									if(file_exists($_SERVER["DOCUMENT_ROOT"].$path_mod."/".$file_templ."/.description.php"))
									{
										if(file_exists($_SERVER["DOCUMENT_ROOT"].$path_mod."/lang/en/".$module_name."/.description.php"))
											__IncludeLang($_SERVER["DOCUMENT_ROOT"].$path_mod."/lang/en/".$module_name."/.description.php");
										if(LANGUAGE_ID!="ru" && file_exists($_SERVER["DOCUMENT_ROOT"].$path_mod."/lang/".LANGUAGE_ID."/".$module_name."/.description.php"))
											__IncludeLang($_SERVER["DOCUMENT_ROOT"].$path_mod."/lang/".LANGUAGE_ID."/".$module_name."/.description.php");
										include($_SERVER["DOCUMENT_ROOT"].$path_mod."/".$file_templ."/.description.php");
									}
									if($sSectionName)
									{
										$arTemplateFolders[$module_name] = $sSectionName;
										$arTemplateFoldersSort[] = Array($iSort, $module_name);
									}
									/*
									elseif(!isset($arTemplateFolders[$folder_name]))
										$arTemplateFolders[$module_name] = $module_name;
									*/
								}
							}
							@closedir($handle_mod);
						} // if($handle = @opendir($_SERVER["DOCUMENT_ROOT"].$path_mod))
					} // if(file_exists($_SERVER["DOCUMENT_ROOT"].$path_mod))
				} // if(is_dir($_SERVER["DOCUMENT_ROOT"].$path."/".$file))
			} // while(($file = readdir($handle)) !== false)
			@closedir($handle);
		} // if($handle = @opendir($_SERVER["DOCUMENT_ROOT"].$path))

		$arPath = Array(BX_PERSONAL_ROOT."/templates/.default");
		if($template_id)
			$arPath[] = BX_PERSONAL_ROOT."/templates/".$template_id;

		for($i=0; $i<count($arPath); $i++)
		{
			$path = $arPath[$i];
			if($handle = @opendir($_SERVER["DOCUMENT_ROOT"].$path))
			{
				while(($folder_name = readdir($handle)) !== false)
				{
					if($folder_name == "." || $folder_name == ".." || $folder_name=="lang") continue;
					if(is_dir($_SERVER["DOCUMENT_ROOT"].$path."/".$folder_name))
					{
						$sSectionName = false;
						$iSort = 500;
						if(file_exists($_SERVER["DOCUMENT_ROOT"].$path."/".$folder_name."/.description.php"))
							include($_SERVER["DOCUMENT_ROOT"].$path."/".$folder_name."/.description.php");
						if($sSectionName)
						{
							$arTemplateFolders[$folder_name] = $sSectionName;
							$arTemplateFoldersSort[] = Array($iSort, $folder_name);
						}
						/*
						elseif(!isset($arTemplateFolders[$folder_name]))
							$arTemplateFolders[$folder_name] = $folder_name;
						*/
					}
				}
				@closedir($handle);
			} // if($handle = @opendir($_SERVER["DOCUMENT_ROOT"].$path))
		} // for($i=0; $i<count($arPath); $i++)

		for($i=0; $i<count($arTemplateFoldersSort)-1; $i++)
			for($j=$i+1; $j<count($arTemplateFoldersSort); $j++)
				if($arTemplateFoldersSort[$i][0]>$arTemplateFoldersSort[$j][0])
				{
					$x = $arTemplateFoldersSort[$i];
					$arTemplateFoldersSort[$i] = $arTemplateFoldersSort[$j];
					$arTemplateFoldersSort[$j] = $x;
				}

		$arTemplateFoldersRes = Array();
		for($i=0; $i<count($arTemplateFoldersSort); $i++)
			$arTemplateFoldersRes[$arTemplateFoldersSort[$i][1]] = $arTemplateFolders[$arTemplateFoldersSort[$i][1]];

		return $arTemplateFoldersRes;
	}
}

class CPageTemplate
{
	function GetList($arSiteTemplates=array())
	{
		$arDirs = array("/templates/.default/page_templates");
		foreach($arSiteTemplates as $val)
			$arDirs[] = "/templates/".$val."/page_templates";

		$arFiles = array();
		foreach($arDirs as $dir)
		{
			$template_dir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT.$dir;
			if(!file_exists($template_dir))
				continue;
			if($handle = opendir($template_dir))
			{
				while(($file = readdir($handle)) !== false)
				{
					$template_file = $template_dir."/".$file."/template.php";
					if(!file_exists($template_file))
						continue;

					if($GLOBALS['APPLICATION']->GetFileAccessPermission(BX_PERSONAL_ROOT.$dir."/".$file."/template.php") < "R")
						continue;

					$arFiles[$file] = $template_file;
				}
				closedir($handle);
			}
		}

		$res = array();
		foreach($arFiles as $file=>$template_file)
		{
			$pageTemplate = false;
			include_once($template_file);

			if(!$pageTemplate || !is_callable(array($pageTemplate, 'GetDescription')))
				continue;

			$arRes = array(
				"name"=>$file,
				"description"=>"",
				"icon"=>"",
				"file"=>$file,
				"sort"=>150,
				"type"=>"",
			);

			$arDesc = $pageTemplate->GetDescription();

			if(is_array($arDesc["modules"]))
				foreach($arDesc["modules"] as $module)
					if(!IsModuleInstalled($module))
						continue 2;

			foreach($arDesc as $key=>$val)
				$arRes[$key] = $val;

			$res[$file] = $arRes;
		}

		uasort($res, array('CPageTemplate', '_templ_sort'));
		return $res;
	}

	function _templ_sort($a, $b)
	{
		if($a["sort"] < $b["sort"])
			return -1;
		elseif($a["sort"] > $b["sort"])
			return 1;
		else
			return strcmp($a["name"], $b["name"]);
	}

	function GetTemplate($template, $arSiteTemplates=array())
	{
		$arDirs = array("/templates/.default/page_templates");
		foreach($arSiteTemplates as $val)
			$arDirs[] = "/templates/".$val."/page_templates";

		$template = str_replace("/", "", str_replace("\\", "/", basename($template)));
		$sFile = false;
		foreach($arDirs as $dir)
		{
			$template_dir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT.$dir;
			$template_file = $template_dir."/".$template."/template.php";
			if(!file_exists($template_file))
				continue;

			if($GLOBALS['APPLICATION']->GetFileAccessPermission(BX_PERSONAL_ROOT.$dir."/".$template."/template.php") < "R")
				continue;

			$sFile = $template_file;
		}
		if($sFile !== false)
		{
			$pageTemplate = false;
			include_once($sFile);

			if(is_object($pageTemplate))
				return $pageTemplate;
		}
		return false;
	}

	function IncludeLangFile($filepath)
	{
		$file = basename($filepath);
		$dir = dirname($filepath);

		if(LANGUAGE_ID <> "en" && LANGUAGE_ID <> "ru" && file_exists($dir."/lang/en/".$file))
			__IncludeLang($dir."/lang/en/".$file);

		if(file_exists($dir."/lang/".LANGUAGE_ID."/".$file))
			__IncludeLang($dir."/lang/".LANGUAGE_ID."/".$file);
	}
}

function GetTemplateContent($filename, $lang=LANG, $arTemplates=Array())
{
	global $DOCUMENT_ROOT, $APPLICATION;

	$filename = str_replace("/", "", str_replace("\\", "/", basename($filename)));

	$arDirs = Array();
	foreach($arTemplates as $val)
		$arDirs[] = "/templates/".$val."/page_templates";
	$arDirs[] = "/templates/.default/page_templates";
	$arDirs[] = "/php_interface/".$lang."templates";
	$arDirs[] = "/php_interface/templates";

	for($i=0, $n=count($arDirs); $i<$n; $i++)
		if(is_file($DOCUMENT_ROOT.BX_PERSONAL_ROOT.$arDirs[$i]."/".$filename))
			return $APPLICATION->GetFileContent($DOCUMENT_ROOT.BX_PERSONAL_ROOT.$arDirs[$i]."/".$filename);

	return false;
}

function GetFileTemplates($lang = LANG, $arTemplates = Array())
{
	global $DOCUMENT_ROOT, $APPLICATION;

	$arDirs = Array();
	$arDirs[] = "/php_interface/".$lang."/templates";
	$arDirs[] = "/templates/.default/page_templates";
	$arDirs[] = "/php_interface/templates";
	foreach($arTemplates as $val)
		$arDirs[] = "/templates/".$val."/page_templates";

	$res = Array();

	for($i=0, $n=count($arDirs); $i<$n; $i++)
	{
		$dir = $arDirs[$i];
		$TEMPLATE = Array();
		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT.$dir))
		{
			$sDescFile = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT.$dir."/.content.php";
			if(file_exists($sDescFile))
				include($sDescFile);

			if($handle = @opendir($DOCUMENT_ROOT.BX_PERSONAL_ROOT.$dir))
			{
				while(($file = readdir($handle)) !== false)
				{
					if(is_dir($DOCUMENT_ROOT.BX_PERSONAL_ROOT.$dir."/".$file))
						continue;
					if($file[0] == ".")
						continue;
					if($APPLICATION->GetFileAccessPermission(BX_PERSONAL_ROOT.$dir."/".$file) < "R")
						continue;

					$restmp = Array("name"=>substr($file, 0, bxstrrpos($file, ".")), "file"=>$file, "sort"=>150, "path"=>BX_PERSONAL_ROOT.$dir."/".$file);
					if(is_set($TEMPLATE, $file))
					{
						$TEMPtmp = $TEMPLATE[$file];
						if(is_set($TEMPtmp, "name")) $restmp["name"] = $TEMPtmp["name"];
						if(is_set($TEMPtmp, "sort")) $restmp["sort"] = $TEMPtmp["sort"];
					}

					$bExists = false;
					for($j=0, $nj=count($res); $j<$nj; $j++)
					{
						if($res[$j]["file"] == $file)
						{
							$res[$j] = $restmp;
							$bExists = true;
							break;
						}
					}

					if(!$bExists)
						$res[] = $restmp;
				}
				closedir($handle);
			}
		}
	}

	for($i=0; $i<count($res)-1; $i++)
		for($j=$i+1; $j<count($res); $j++)
			if($res[$i]["sort"]>$res[$j]["sort"])
			{
				$restmp = $res[$i];
				$res[$i] = $res[$j];
				$res[$j] = $restmp;
			}

	return $res;
}

function ParsePath($path, $bLast=false, $url=false, $param="", $bLogical = false)
{
	CMain::InitPathVars($site, $path);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);

	if($url===false)
		$url = BX_ROOT."/admin/fileman_admin.php";

	global $MESS;
	if($site!==false && strlen($site)>0)
	{
		$res = CSite::GetByID($site);
		if(!($arSite = $res->Fetch()))
			$site=false;
	}

	$addUrl = ($bLogical?"logical=Y":"");

	$arDirPath = explode("/",$path);
	$full_path = "";
	$prev_path = "";
	$arPath = array();
	if(strlen($path)>0 || strlen($site)>0 || $bLast)
	{
		$html_path = '<a href="'.$url.'?lang='.LANG.'&'.$addUrl.'">'.GetMessage("MAIN_ROOT_FOLDER").'</a>/';
		/*
		$arPath[] = array(
			"LINK" => $url.'?lang='.LANG,
			"TITLE" => GetMessage("MAIN_ROOT_FOLDER")
		);
		*/
	}
	else
	{
		$html_path = GetMessage("MAIN_ROOT_FOLDER")."/";
		/*
		$arPath[] = array(
			"LINK" => "",
			"TITLE" => GetMessage("MAIN_ROOT_FOLDER")
		);
		*/
	}

	if($site!==false)
	{
		if(strlen($path)>0 || $bLast)
		{
			$html_path .= '<a href="'.$url.'?lang='.LANG.'&'.$addUrl.'&amp;site='.$site.'">'.$arSite["NAME"].'</a>/';
			/*
			$arPath[] = array(
				"LINK" => $url.'?lang='.LANG.'&amp;site='.$site,
				"TITLE" => $arSite["NAME"]
			);
			*/
		}
		else
		{
			$html_path .= $arSite["NAME"]."/";
			/*
			$arPath[] = array(
				"LINK" => "",
				"TITLE" => $arSite["NAME"]
			);
			*/
		}
	}

	for($i=0; $i<count($arDirPath); $i++)
	{
		if(strlen($arDirPath[$i])<=0)
			continue;
		$prev_path = $full_path;
		$full_path .= "/".$arDirPath[$i];
		if($i==count($arDirPath)-1)
			$last = $arDirPath[$i];

		$sSectionName = $arDirPath[$i];
		if($bLogical && is_dir($DOC_ROOT.$full_path))
		{
			if(!file_exists($DOC_ROOT.$full_path."/.section.php"))
				continue;

			include($DOC_ROOT.$full_path."/.section.php");
			if(strlen($sSectionName)<=0)
				$sSectionName = GetMessage("admin_tools_no_name");
		}

		if($i==count($arDirPath)-1 && (!$bLast || !is_dir($DOC_ROOT.$full_path)))
		{
			$html_path .= $sSectionName;
			$arPath[] = array(
				"LINK" => "",
				"TITLE" => $sSectionName
			);
		}
		else
		{
			$html_path .= "<a href=\"".$url."?lang=".LANG.'&'.$addUrl."&path=".UrlEncode($full_path).($site?"&site=".$site : "").($param<>""? "&".$param:"")."\">".$sSectionName."</a>/";
			if(!$arSite || !$bLogical || ($bLogical && rtrim($arSite["DIR"], "/") != rtrim($full_path, "/")))
				$arPath[] = array(
					"LINK" => $url."?lang=".LANG."&".$addUrl."&path=".UrlEncode($full_path).($site?"&site=".$site : "").($param<>""? "&".$param:""),
					"TITLE" => $sSectionName
				);
		}
	}

	return Array("PREV"=>$prev_path, "FULL"=>$full_path, "HTML"=>$html_path, "LAST"=>$last, "AR_PATH" => $arPath);
}

function CompareFiles($f1, $f2, $sort=Array())
{
	$by = key($sort);
	$order = $sort[$by];
	if(strtolower($order)=="desc")
	{
		if($by=="size")	return IntVal($f1["SIZE"])<IntVal($f2["SIZE"]);
		if($by=="timestamp") return IntVal($f1["TIMESTAMP"])<IntVal($f2["TIMESTAMP"]);
		return $f1["NAME"]<$f2["NAME"];
	}
	else
	{
		if($by=="size")	return IntVal($f1["SIZE"])>IntVal($f2["SIZE"]);
		if($by=="timestamp") return IntVal($f1["TIMESTAMP"])>IntVal($f2["TIMESTAMP"]);
		return $f1["NAME"]>$f2["NAME"];
	}
}

function GetDirList($path, &$arDirs, &$arFiles, $arFilter=Array(), $sort=Array(), $type="DF", $bLogical=false,$task_mode=false)
{
	global $USER, $APPLICATION;

	CMain::InitPathVars($site, $path);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);

	$arDirs=Array();
	$arFiles=Array();

	$exts = strtolower($arFilter["EXTENSIONS"]);
	$arexts=explode(",", $exts);
	if(isset($arFilter["TYPE"]))
		$type = strtoupper($arFilter["TYPE"]);

	$path = $path[strlen($path)-1]=="/"?substr($path, 0, strlen($path)-1):$path;
	$abs_path = $DOC_ROOT.$path;

	if(!file_exists($abs_path) || !is_dir($abs_path))
		return false;

	$date_format = CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL"));

	if(($handle = @opendir($abs_path)))
	{
		while(false !== ($file = @readdir($handle)))
		{
			$arFile = Array();
			if($file == "." || $file == "..") continue;

			if(($type=="F" || $type=="") && is_dir($abs_path."/".$file))
				continue;
			if(($type=="D" || $type=="") && is_file($abs_path."/".$file))
				continue;

			if($bLogical)
			{
				if(is_dir($abs_path."/".$file))
				{
					$sSectionName = "";
					if(!file_exists($abs_path."/".$file."/.section.php"))
						continue;

					include($abs_path."/".$file."/.section.php");
					$arFile["LOGIC_NAME"] = $sSectionName;
				}
				else
				{
					if(CFileMan::GetFileTypeEx($file) != "php")
						continue;

					if($file=='.section.php')
						continue;

					if(!preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|php6|phtml)$/', $file, $regs))
					{
						$fd = @fopen($abs_path."/".$file, "rb");
						$filesrc = @fread($fd, filesize($abs_path."/".$file));
						@fclose($fd);
						$arContent = ParseFileContent($filesrc);
						if($arContent["TITLE"]===false)
							continue;
						$arFile["LOGIC_NAME"] = $arContent["TITLE"];
					}
				}
			}

			$arFile["PATH"] = $abs_path."/".$file;
			$arFile["ABS_PATH"] = $path."/".$file;
			$arFile["NAME"] = $file;

			$arPerm = $APPLICATION->GetFileAccessPermission(Array($site, $path."/".$file), $USER->GetUserGroupArray(),$task_mode);
			if ($task_mode)
			{
				$arFile["PERMISSION"] = $arPerm[0];
				if (count($arPerm[1]) > 0)
					$arFile["PERMISSION_EX"] = $arPerm[1];
			}
			else
				$arFile["PERMISSION"] = $arPerm;

			$arFile["TIMESTAMP"] = filemtime($abs_path."/".$file);
			$arFile["DATE"] = date($date_format, filemtime($abs_path."/".$file));

			if (isset($arFilter["TIMESTAMP_1"]) && strtotime($arFile["DATE"]) < strtotime($arFilter["TIMESTAMP_1"]))
				continue;
			if (isset($arFilter["TIMESTAMP_2"]) && strtotime($arFile["DATE"]) > strtotime($arFilter["TIMESTAMP_2"]))
				continue;

			if(is_set($arFilter, "MIN_PERMISSION") && $arFile["PERMISSION"]<$arFilter["MIN_PERMISSION"] && !$task_mode)
				continue;

			if(is_file($abs_path."/".$file) && $arFile["PERMISSION"]<="R"  && !$task_mode)
				continue;

			if ($bLogical)
			{
				if(strlen($arFilter["NAME"])>0 && strpos($arFile["LOGIC_NAME"], $arFilter["NAME"])===false)
					continue;
			}
			else
			{
				if(strlen($arFilter["NAME"])>0 && strpos($arFile["NAME"], $arFilter["NAME"])===false)
					continue;
			}

			//if(strlen($arFilter["NAME"])>0 && strpos($arFile["NAME"], $arFilter["NAME"])===false)
			//	continue;

			if(substr($arFile["ABS_PATH"], 0, strlen(BX_ROOT."/modules"))==BX_ROOT."/modules" && !$USER->CanDoOperation('edit_php') && !$task_mode)
				continue;

			if ($arFile["PERMISSION"]=="U"  && !$task_mode)
			{
				$ftype = GetFileType($arFile["NAME"]);
				if ($ftype!="SOURCE" && $ftype!="IMAGE" && $ftype!="UNKNOWN") continue;
				if (substr($arFile["NAME"], 0,1)==".") continue;
			}

			if(is_dir($abs_path."/".$file))
			{
				$arFile["SIZE"] = 0;
				$arFile["TYPE"] = "D";
				$arDirs[]=$arFile;
			}
			else
			{
				if($exts!="")
					if(!in_array(strtolower(substr($file, bxstrrpos($file,".")+1)), $arexts))
						continue;

				$arFile["TYPE"] = "F";
				$arFile["SIZE"] = filesize($abs_path."/".$file);
				$arFiles[]=$arFile;
			}
		}
		closedir($handle);
	}

	if(is_array($sort) && count($sort)>0)
	{
		$by = key($sort);
		$order = strtolower($sort[$by]);
		$by = strtolower($by);
		if($order!="desc")
			$order="asc";
		if($by!="size" && $by!="timestamp")
			$by="name";

		usort($arDirs, Array("_FilesCmp", "cmp_".$by."_".$order));
		usort($arFiles, Array("_FilesCmp", "cmp_".$by."_".$order));
	}
}

class _FilesCmp
{
	function cmp_size_asc($a, $b)
	{
	   if($a["SIZE"] == $b["SIZE"])
	       return 0;
	   return ($a["SIZE"] < $b["SIZE"]) ? -1 : 1;
	}
	function cmp_size_desc($a, $b)
	{
	   if ($a["SIZE"] == $b["SIZE"])
	       return 0;
	   return ($a["SIZE"] > $b["SIZE"]) ? -1 : 1;
	}
	function cmp_timestamp_asc($a, $b)
	{
	   if($a["TIMESTAMP"] == $b["TIMESTAMP"])
	       return 0;
	   return ($a["TIMESTAMP"] < $b["TIMESTAMP"]) ? -1 : 1;
	}
	function cmp_timestamp_desc($a, $b)
	{
	   if ($a["TIMESTAMP"] == $b["TIMESTAMP"])
	       return 0;
	   return ($a["TIMESTAMP"] > $b["TIMESTAMP"]) ? -1 : 1;
	}
	function cmp_name_asc($a, $b)
	{
	   if($a["NAME"] == $b["NAME"])
	       return 0;
	   return ($a["NAME"] < $b["NAME"]) ? -1 : 1;
	}
	function cmp_name_desc($a, $b)
	{
	   if($a["NAME"] == $b["NAME"])
	       return 0;
	   return ($a["NAME"] > $b["NAME"]) ? -1 : 1;
	}
}

function SetPrologTitle($prolog, $title)
{
	if(preg_match("'(\\\$APPLICATION->SetTitle\(\")(.*?)(?<!\\\\)(\"\);)'i", $prolog, $regs))
		$prolog = str_replace($regs[1].$regs[2].$regs[3], $regs[1].EscapePHPString($title).$regs[3], $prolog);
	else
	{
		$p = strpos($prolog, "prolog_before");
		if($p===false)
			$p = strpos($prolog, "prolog.php");
		if($p===false)
			$p = strpos($prolog, "header.php");

		if($p===false)
		{
			if(strlen($title)<=0)
				$prolog = preg_replace("#<title>[^<]*</title>#i", "", $prolog);
			elseif(preg_match("#<title>[^<]*</title>#i", $prolog))
				$prolog = preg_replace("#<title>[^<]*</title>#i", "<title>".$title."</title>", $prolog);
			else
				$prolog = $prolog."\n<title>".htmlspecialchars($title)."</title>\n";
		}
		else
		{
			$p = strpos(substr($prolog, $p), ")") + $p;
			$prolog = substr($prolog, 0, $p+1).";\n\$APPLICATION->SetTitle(\"".EscapePHPString($title)."\")".substr($prolog, $p+1);
		}
	}
	return $prolog;
}

function SetPrologProperty($prolog, $property_key, $property_val)
{
	if (preg_match("'(\\\$APPLICATION->SetPageProperty\(\"".preg_quote(EscapePHPString($property_key), "'")."\" *, *\")(.*?)(?<!\\\\)(\"\);[\r\n]*)'i", $prolog, $regs))
	{
		if (strlen($property_val)<=0)
			$prolog = str_replace($regs[1].$regs[2].$regs[3], "", $prolog);
		else
			$prolog = str_replace($regs[1].$regs[2].$regs[3], $regs[1].EscapePHPString($property_val).$regs[3], $prolog);
	}
	else
	{
		if (strlen($property_val)>0)
		{
			$p = strpos($prolog, "prolog_before");
			if($p===false)
				$p = strpos($prolog, "prolog.php");
			if($p===false)
				$p = strpos($prolog, "header.php");
			if($p!==false)
			{
				$p = strpos(substr($prolog, $p), ")") + $p;
				$prolog = substr($prolog, 0, $p+1).";\n\$APPLICATION->SetPageProperty(\"".EscapePHPString($property_key)."\", \"".EscapePHPString($property_val)."\")".substr($prolog, $p+1);
			}
		}
	}
	return $prolog;
}

function IsPHP($src)
{
	if(strpos($src, "<?")!==false)
		return true;
	if(preg_match("/(<script[^>]*language\s*=\s*)('|\"|)php('|\"|)([^>]*>)/i", $src))
		return true;
	return false;
}
?>