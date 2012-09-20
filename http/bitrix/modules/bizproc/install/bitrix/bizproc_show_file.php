<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CModule::IncludeModule("bizproc");

$fileName = trim($_REQUEST["f"]);
$fileName = preg_replace("/[^A-Za-z0-9_.-]+/i", "", $fileName);

$fileId = intval($_REQUEST["i"]);

$fileAction = ($_REQUEST["act"] == "v" ? "view" : "download");

if (strlen($fileName) <= 0 || $fileId <= 0 || strlen($fileAction) <= 0)
	die("Error1");

$dbImg = CFile::GetByID($fileId);
$arImg = $dbImg->Fetch();
if (!$arImg)
	die("Error2");

if (strlen($arImg["FILE_NAME"]) != strlen($fileName) || $arImg["FILE_NAME"] != $fileName)
	die("Error3");

if (strlen($arImg["SUBDIR"]) <= 0 || substr($arImg["SUBDIR"], 0, strlen("bizproc_wf/")) != "bizproc_wf/")
	die("Error4");

$filePath = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$arImg["SUBDIR"]."/".$arImg["FILE_NAME"];
$filePath = str_replace("//", "/", $filePath);
if (defined("BX_IMG_SERVER"))
	$filePath = BX_IMG_SERVER.$filePath;

function bizprocView($filePath, $fileNameOriginal, $contentType)
{
	$filePathFull = $_SERVER["DOCUMENT_ROOT"].$filePath;

	if ($f = fopen($filePathFull, "rb"))
	{
		if (strlen($contentType) <= 0)
			$contentType = "text/html; charset=".LANG_CHARSET;
		header("Content-type: ".$contentType);
		header("Content-Disposition: filename=\"".$fileNameOriginal."\"");
		header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0"); 
		header("Expires: 0"); 
		header("Pragma: public"); 
		while ($buffer = fread($f, 4096))
			echo $buffer;
		fclose ($f);
	}
}

function bizprocDownload($filePath, $fileName)
{
	$filePathFull = $_SERVER["DOCUMENT_ROOT"].$filePath;
	$fileSize = filesize($filePathFull);

	$sapi = (stristr(php_sapi_name(), "cgi") !== false ? "cgi" : "");

	$f = fopen($filePathFull, "rb");
	$curPos = 0;
	$size = $fileSize - 1;

	$p = strpos($_SERVER["HTTP_RANGE"], "=");
	if (intval($p) > 0)
	{
		$bytes = substr($_SERVER["HTTP_RANGE"], $p + 1);
		$p = strpos($bytes, "-");
		if ($p !== false)
		{
			$curPos = intval(substr($bytes, 0, $p));
			$size = intval(substr($bytes, $p + 1));
			if ($size <= 0)
				$size = $fileSize - 1;
			if ($curPos > $size)
			{
				$curPos = 0;
				$size = $fileSize - 1;
			}
			fseek($f, $curPos);
		}
	}

	if (intval($curPos) > 0 && $_SERVER["SERVER_PROTOCOL"] == "HTTP/1.1")
	{
		if ($sapi == "cgi")
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
		if ($sapi == "cgi") 
			header("Status: 200 OK"); 
		else 
			header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
	}

	header("Content-Type: application/force-download; name=\"".$fileName."\"");
	header("Content-Disposition: attachment; filename=\"".$fileName."\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".($size - $curPos + 1));
	header("Accept-Ranges: bytes");
	header("Content-Range: bytes ".$curPos."-".$size."/".$fileSize);
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	header("Expires: 0"); 
	header("Pragma: public"); 

	$str = "";
	while ($curPos <= $size)
	{
		$bufsize = 32768;
		if ($bufsize + $curPos > $size)
			$bufsize = $size - $curPos + 1;
		$curPos += $bufsize;
		$p = fread($f, $bufsize);
		echo $p;
		flush();
	}
	fclose($f);
	die();
}

$fileNameOriginal = (strlen($arImg["ORIGINAL_NAME"]) > 0 ? $arImg["ORIGINAL_NAME"] : $arImg["FILE_NAME"]);

set_time_limit(0);

if ($fileAction == "download")
{
	bizprocDownload($filePath, $fileNameOriginal);
}
else
{
	$filePathFull = $_SERVER["DOCUMENT_ROOT"].$filePath;

	$contentType = strtolower($arImg["CONTENT_TYPE"]);
	if (strpos($contentType, "image/") !== false && GetImageSize($filePathFull))
		$contentType = $contentType;
	elseif (strpos($contentType, "excel") !== false)
		$contentType = "application/vnd.ms-excel";
	elseif (strpos($contentType, "word") !== false)
		$contentType = "application/msword";
	elseif (strpos($contentType, "flash") !== false)
		$contentType = "application/x-shockwave-flash";
	elseif (strpos($contentType, "pdf") !== false)
		$contentType = "application/pdf";
	elseif (strpos($contentType, "text") !== false)
		$contentType = "text/xml";
	else
		$contentType = "application/octet-stream";

	bizprocView($filePath, $fileNameOriginal, $contentType);
}
?>