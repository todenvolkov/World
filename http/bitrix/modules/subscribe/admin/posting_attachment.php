<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/prolog.php");
define("HELP_FILE", "add_issue.php");

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$rsFile = CPosting::GetFileList($POSTING_ID, $FILE_ID);
if($arFile = $rsFile->Fetch())
{
	$filename = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
	$filesize = filesize($filename);

	$name = strlen($arFile["ORIGINAL_NAME"])>0 ? $arFile["ORIGINAL_NAME"] : $arFile["FILE_NAME"];

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
		CHTTP::SetStatus("206 Partial Content");
	}
	else
	{
		session_cache_limiter('');
		session_start();
		ob_end_clean();
		session_write_close();
		CHTTP::SetStatus("200 OK");
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
else
{
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
	echo ShowError(GetMessage("POST_ERROR_ATTACH_NOT_FOUND"));
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
}
?>
