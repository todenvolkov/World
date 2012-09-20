<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 - 2004 Bitrix           #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

/***************************************************************************
		                         Функции
***************************************************************************/

function sup_view_file($arFile, $content_type=false, $specialchars=true)
{
	$filename = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];

	if (strlen($arFile["EXTENSION_SUFFIX"])>0)
	{
		$real_file_name = substr($arFile["FILE_NAME"],0,"-".strlen($arFile["EXTENSION_SUFFIX"]));
	}
	else
	{
		$real_file_name = $arFile["FILE_NAME"];
	}

	if ($f = fopen($filename, "rb"))
	{
		if ($content_type===false) $content_type = "text/html; charset=".LANG_CHARSET;
		header("Content-type: ".$content_type);
		header("Content-Disposition: filename=\"".$real_file_name."\"");
		header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0"); 
		header("Expires: 0"); 
		header("Pragma: public"); 
		if ($specialchars)
		{
			while ($buffer = fread($f, 4096)) echo htmlspecialchars($buffer);
		}
		else
		{
			while ($buffer = fread($f, 4096)) echo $buffer;
		}
		fclose ($f);
	}
}

function sup_download($arFile)
{
	$filename = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
	$filesize = filesize($filename);

	$name = strlen($arFile["ORIGINAL_NAME"])>0 ? $arFile["ORIGINAL_NAME"] : $arFile["FILE_NAME"];
	if (strlen($arFile["EXTENSION_SUFFIX"])>0) :
		$suffix_length = strlen($arFile["EXTENSION_SUFFIX"]);
		$name = substr($name, 0, strlen($name)-$suffix_length);
	endif;

	// ie filename error fix
	$ua = strtolower($_SERVER["HTTP_USER_AGENT"]); 
	if (strpos($ua, "opera") === false && strpos($ua, "msie") !== false):
		if (SITE_CHARSET != "UTF-8")
			$name = $GLOBALS['APPLICATION']->ConvertCharset($name, SITE_CHARSET, "UTF-8");
		$name = str_replace(" ", "%20", $name);
		$name = urlencode($name);
		$name = str_replace(array("%2520", "%2F"), array("%20", "/"), $name);
	endif;
	
	$sapi = (stristr(php_sapi_name(), "cgi") !== false? "cgi":"");
	$f = fopen($filename, "rb");
	$cur_pos = 0;
	$size = $filesize-1;

	$p = strpos($_SERVER["HTTP_RANGE"], "=");
	if(intval($p)>0)
	{
		$bytes = substr($_SERVER["HTTP_RANGE"], $p+1);
		$p = strpos($bytes, "-");
		if($p!==false)
		{
			$cur_pos = IntVal(substr($bytes, 0, $p));
			$size = IntVal(substr($bytes, $p+1));
			if ($size<=0) $size = $filesize - 1;
			if ($cur_pos>$size)
			{
				$cur_pos = 0;
				$size = $filesize - 1;
			}
			fseek($f, $cur_pos);
		}
	}

	if(intval($cur_pos)>0 && $_SERVER["SERVER_PROTOCOL"] == "HTTP/1.1")
	{
		if($sapi=="cgi")
			header("Status: 206 Partial Content"); 
		else
			header("HTTP/1.1 206 Partial Content");
	}
	else
	{
		session_cache_limiter('');
		session_start();
		ob_end_clean();
		session_write_close();
		if($sapi=="cgi") 
			header("Status: 200 OK"); 
		else 
			header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
	}

	header("Content-Type: application/force-download; name=\"".$name."\"");
	header("Content-Disposition: attachment; filename=\"".$name."\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".($size-$cur_pos+1));
	header("Accept-Ranges: bytes");
	header("Content-Range: bytes ".$cur_pos."-".$size."/".$filesize);
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	header("Expires: 0"); 
	header("Pragma: public"); 

	$str = "";
	while($cur_pos<=$size)
	{
		$bufsize = 32768;
		if($bufsize+$cur_pos>$size)
			$bufsize = $size - $cur_pos + 1;
		$cur_pos += $bufsize;
		$p = fread($f, $bufsize);
		echo $p;
		flush();
	}
	fclose ($f);
	die();
}

/***************************************************************************
                           Обработка GET | POST
***************************************************************************/

if (strlen($hash)>0) :
	if ($rsFiles = CTicket::GetFileList($v1="s_id", $v2="asc", array("HASH"=>$hash))) :
		if ($arFile = $rsFiles->Fetch()) :

			set_time_limit(0);

			// если нужно безусловно скачать то
			if ($action=="download") :

				// скачиваем
				sup_download($arFile);

			else : // иначе просматриваем файл

				// имя файла
				$filename = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];

				// информация о файле
				$ar = pathinfo($arFile["FILE_NAME"]);

				// расширение
				$ext = substr($ar["extension"], 0, strlen($ar["extension"])-strlen($arFile["EXTENSION_SUFFIX"]));

				// если это картинка
				if (CFile::IsImage($arFile["FILE_NAME"], $arFile["CONTENT_TYPE"]) && strpos($arFile["CONTENT_TYPE"], "html")===false && GetImageSize($filename)) 
				{
					// покажем как картинку
					sup_view_file($arFile, $arFile["CONTENT_TYPE"], false);
				}
				else // иначе
				{
					// в зависимости от расширения по разному будем отдавать в поток
					switch(strtolower($ext))
					{
						case "xla":
						case "xlb":
						case "xlc":
						case "xll":
						case "xlm":
						case "xls":
						case "xlsx":
						case "xlt":
						case "xlw":
						case "dbf":
						case "csv":
							sup_view_file($arFile, "application/vnd.ms-excel", false);
							break;
						case "doc":
						case "docx":
						case "dot":
						case "rtf":
							sup_view_file($arFile, "application/msword", false);
							break;
						case "xml":
						case "pdf":
							sup_download($arFile);
							break;							
						case 'rar':
							sup_view_file($arFile, "application/x-rar-compressed", false);
							break;
						case 'zip':
							sup_view_file($arFile, "application/zip", false);
							break;
						default:
							sup_view_file($arFile);
							//sup_download($arFile);
					}
				}
				die();
			endif;
		endif;
	endif;
endif;

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
echo ShowError(GetMessage("SUP_ERROR_ATTACH_NOT_FOUND"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");?>