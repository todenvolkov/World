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
if (!CModule::IncludeModule("form")) die();

/***************************************************************************
		                         Utility
***************************************************************************/

function form_view_file($arFile, $content_type=false, $specialchars=true)
{
	$filename = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];

	if ($f = fopen($filename, "rb"))
	{
		if ($content_type===false) $content_type = "text/html; charset=".LANG_CHARSET;
		header("Content-type: ".$content_type);
		header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0"); 
		header("Expires: 0"); 
		header("Pragma: public"); 
		if ($specialchars)
		{
			echo "<pre>";
			while ($buffer = fread($f, 4096)) echo htmlspecialchars($buffer);
			echo "</pre>";
		}
		else
		{
			while ($buffer = fread($f, 4096)) echo $buffer;
		}
		fclose ($f);
	}
}

function form_download($arFile)
{
	$filename = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
	$filesize = filesize($filename);

	$name = $arFile["ORIGINAL_NAME"];

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

	//if (extension_loaded('iconv')) $name = iconv(LANG_CHARSET, "utf-8", $name);
	
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
                           GET | POST processing
***************************************************************************/

if (strlen($hash)>0) :
	if ($arFile = CFormResult::GetFileByHash($_REQUEST["rid"], $_REQUEST["hash"])) :
		set_time_limit(0);

		// if we need "download"
		if ($_REQUEST["action"]=="download") :

			// download
			form_download($arFile);

		else : // otherwise just view

			// if it's image
			if (CFile::IsImage($arFile["ORIGINAL_NAME"], $arFile["CONTENT_TYPE"]) && strpos($arFile["CONTENT_TYPE"], "html")===false) 
			{
				// display as image
				form_view_file($arFile, $arFile["CONTENT_TYPE"], false);
			}
			else // otherwise
			{
				// check extension
				$ar = pathinfo($arFile["ORIGINAL_NAME"]);
				$ext = $ar["extension"];

				// and choose mime-type
				switch(strtolower($ext))
				{
					case "xla":
					case "xlb":
					case "xlc":
					case "xll":
					case "xlm":
					case "xls":
					case "xlt":
					case "xlw":
					case "dbf":
					case "csv":
						form_view_file($arFile, "application/vnd.ms-excel", false);
						break;
					case "doc":
					case "dot":
					case "rtf":
						form_view_file($arFile, "application/msword", false);
						break;
					// it's better not to set mime for xml and pdf. may be vulnerable
					case "xml":
					case "pdf":
					default:
						form_download($arFile);
				}
			}
			die();
		endif;
	endif;
endif;

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
echo ShowError(GetMessage("FORM_ERROR_FILE_NOT_FOUND"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");?>