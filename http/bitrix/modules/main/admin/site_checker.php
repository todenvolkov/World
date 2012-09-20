<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2010 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
ini_set("track_errors", "1");
ini_set('display_errors', 1);
error_reporting(E_ALL &~E_NOTICE);
$message = null;

if (!function_exists("file_get_contents"))
{
	function file_get_contents($filename)
	{
		$fd = fopen("$filename", "rb");
		$content = fread($fd, filesize($filename));
		fclose($fd);
		return $content;
	}
}

if (!function_exists("ob_get_clean"))
{
	function ob_get_clean()
	{
		$ob_contents = ob_get_contents();
		ob_end_clean();
		return $ob_contents;
	}
}


# NO AUTH TESTS
if ($_REQUEST['unique_id'] && $_REQUEST['unique_id'] == checker_get_unique_id())
{
	if ($_GET['socket_test']) 
	{
		echo "SUCCESS";
	} 
	elseif ($_GET['upload_test']) 
	{
		$tmp_name = $_FILES['test_file']['tmp_name'];
		$image = $_SERVER['DOCUMENT_ROOT'].'/bitrix/site_checker.gif';

		if (move_uploaded_file($tmp_name, $image))
		{
			$BinaryData0 = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/images/1.gif');
			$BinaryData1 = file_get_contents($image);
			if ($BinaryData0 == $BinaryData1)
				echo "SUCCESS";
			unlink($image);
		}
	} 
	elseif ($_GET['memory_test']) 
	{
		@ini_set("memory_limit", "512M");
		$max = intval($_GET['max']);
		if ($max) 
		{
			for($i=1;$i<=$max;$i++)
			       $a[] = str_repeat(chr($i),1024*1024); // 1 Mb

			echo "SUCCESS";
		}
	} 
	elseif ($_GET['auth_test']) 
	{
		$remote_user = $_SERVER["REMOTE_USER"] ? $_SERVER["REMOTE_USER"] : $_SERVER["REDIRECT_REMOTE_USER"];
		$strTmp = base64_decode(substr($remote_user,6));
		if ($strTmp)
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $strTmp);
		if ($_SERVER['PHP_AUTH_USER']=='test_user' && $_SERVER['PHP_AUTH_PW']=='test_password') 
			echo('SUCCESS');
	} 
	elseif ($_GET['session_test']) 
	{
		session_start();
		echo $_SESSION['CHECKER_CHECK_SESSION'];
		$_SESSION['CHECKER_CHECK_SESSION'] = 'SUCCESS';
	} 
	elseif ($_GET['redirect_test'])
	{
		function SetStatus($status)
		{
			$bCgi = (stristr(php_sapi_name(), "cgi") !== false);
			$bFastCgi = ($bCgi && (array_key_exists('FCGI_ROLE', $_SERVER) || array_key_exists('FCGI_ROLE', $_ENV)));
			if($bCgi && !$bFastCgi)
				header("Status: ".$status);
			else
				header($_SERVER["SERVER_PROTOCOL"]." ".$status);
		}

		function IsHTTPS()
		{
			return ($_SERVER["SERVER_PORT"]==443 || strtolower($_SERVER["HTTPS"])=="on");
		}

		function LocalRedirect($url, $skip_security_check=false, $status="302 Found")
		{
			$HTTP_HOST = $_SERVER['HTTP_HOST'];
			$SERVER_PORT = $_SERVER['SERVER_PORT'];

			$url = str_replace("&amp;","&",$url);

			$sessid = session_id();
			$sessname = session_name();

			if (strlen($sessid)>0 && false)
			{
				if(strpos($url,$sessname."=")===false)
				{
					$arr_url = explode("#",$url);
					$url_1 = $arr_url[0];
					$url_2 = $arr_url[1];
					if(!(strpos($url, "?") === false))
						$url_1 .= "&".$sessname."=".$sessid;
					else
						$url_1 .= "?".$sessname."=".$sessid;
					$url = $url_1.((strlen($url_2)>0) ? "#".$url_2 : "");
				}
			}
			$arr = explode("?",$url);
			if (strpos($arr[0],"/")===false) $url = dirname($_SERVER['PHP_SELF']).$url;

			// http response splitting defence
			$url = str_replace ("\r", "", $url);
			$url = str_replace ("\n", "", $url);

			SetStatus($status);

			if(
				strtolower(substr($url,0,7))=="http://" ||
				strtolower(substr($url,0,8))=="https://" ||
				strtolower(substr($url,0,6))=="ftp://")
			{
				header("Request-URI: $url");
				header("Content-Location: $url");
				header("Location: $url");
			}
			else
			{
				if($SERVER_PORT <> 80 && $SERVER_PORT <> 443 && $SERVER_PORT > 0 && strpos($HTTP_HOST, ":") === false)
					$HTTP_HOST .= ":".$SERVER_PORT;

				$protocol = (IsHTTPS() ? "https" : "http");

				header("Request-URI: $protocol://$HTTP_HOST$url");
				header("Content-Location: $protocol://$HTTP_HOST$url");
				header("Location: $protocol://$HTTP_HOST$url");
			}
		}

		if ($_REQUEST['done'])
			echo 'SUCCESS';
		else
			LocalRedirect($_SERVER['PHP_SELF']."?redirect_test=Y&done=Y&unique_id=".checker_get_unique_id());
	}
	die();
}
# END NO AUTH TESTS

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if ($_REQUEST['read_log']) // after prolog to sent correct charset
{
	$oTest = new CTest();
	echo file_get_contents($_SERVER['DOCUMENT_ROOT'].$oTest->LogFile);
	die();
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/prolog.php");
define("HELP_FILE", "utilities/site_checker.php");
error_reporting(E_ALL &~E_NOTICE);

////////////////////////////////////////////////////////////////////////
//////////   PARAMS   //////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
define(SUPPORT_PAGE, LANGUAGE_ID == 'ru' ? 'http://www.1c-bitrix.ru/support/' : 'http://www.bitrixsoft.com/support/');

$SYSTEM_min_avail_memory = 32;		// Min & recomended memory in Mb
$SYSTEM_rec_avail_memory = 64;

$SYSTEM_min_avail_disk = 100;		// Min disk size in Mb
$SYSTEM_min_avail_disk_tmp = 20;		// Min tmp disk size in Mb

$PHP_vercheck_min = "5.0.0";
$PHP_vercheck_max = "";

$Apache_vercheck_min = "1.3.0";
$Apache_vercheck_max = "";

$IIS_vercheck_min = "5.0.0";
$IIS_vercheck_max = "";

$MySql_vercheck_min = "4.1.11";
$MySql_vercheck_max = "";

$Oracle_vercheck_min = "10.0";
$Oracle_vercheck_max = "";

$MSSQL_vercheck_min = "9.0";
$MSSQL_vercheck_max = "";
////////////////////////////////////////////////////////////////////////
//////////   END PARAMS   //////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);


$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SC_TAB_1"), "ICON" => "site_check", "TITLE" => GetMessage("SC_SUBTITLE_REQUIED")),
	array("DIV" => "edit2", "TAB" => GetMessage("SC_TAB_2"), "ICON" => "site_check", "TITLE" => GetMessage("SC_SUBTITLE_DISK")),
	array("DIV" => "edit3", "TAB" => GetMessage('SC_TEST_CONFIG'), "ICON" => "site_check", "TITLE" => GetMessage('SC_TEST_CONFIG')),
//	array("DIV" => "edit4", "TAB" => GetMessage("SC_TAB_4"), "ICON" => "site_check", "TITLE" => GetMessage("SC_SUBTITLE_SITE_MODULES")),
	array("DIV" => "edit5", "TAB" => GetMessage("SC_TAB_5"), "ICON" => "site_check", "TITLE" => GetMessage("SC_TIK_TITLE")),
);

if ($_POST['access_check'] && check_bitrix_sessid())
{
		$ob = new CSearchFiles;
		$ob->TimeLimit = 10;

		if ($_REQUEST['break_point']) 
			$ob->SkipPath = $_REQUEST['break_point'];

		$path = $_SERVER['DOCUMENT_ROOT'];
		$check_type = $_REQUEST['check_type'];

		if ($check_type == 'upload')
			$path .= '/'.COption::GetOptionString('main', 'upload_dir', 'upload');
		elseif($check_type == 'kernel')
			$path .= BX_ROOT;
		elseif($check_type == 'personal')
			$path .= BX_PERSONAL_ROOT;
		else
			$check_type = 'full';

		if ($ob->Search($path))
		{
			if ($ob->BreakPoint)
			{
				$cnt_total = intval($_REQUEST['cnt_total']) + $ob->FilesCount;
				?><form method=post id=postform>
					<input type=hidden name=access_check value="Y">
					<?=bitrix_sessid_post();?>
					<input type=hidden name=cnt_total value="<?=$cnt_total?>">
					<input type=hidden name=check_type value="<?=$check_type?>">
					<input type=hidden name=break_point value="<?=htmlspecialchars($ob->BreakPoint)?>">
				</form>
				<?
				CAdminMessage::ShowMessage(array(
					'TYPE' => 'OK',
					'HTML' => true,
					'MESSAGE' => GetMessage('SC_TESTING'),
					'DETAILS' => str_replace(array('#NUM#','#PATH#'),array($cnt_total,$ob->BreakPoint),GetMessage('SC_FILES_CHECKED')),
					)
				);
				?>
				<script>
				if (parent.document.getElementById('access_submit').disabled)
					window.setTimeout("parent.ShowWaitWindow();document.getElementById('postform').submit()",1000);
				</script><? 
			}
			else
			{
				CAdminMessage::ShowMessage(Array("TYPE"=>"OK", "MESSAGE"=>GetMessage("SC_FILES_OK")));
				?><script>parent.access_check_start(0);</script><?
			}
		}
		else
		{
			CAdminMessage::ShowMessage(array(
				'TYPE' => 'ERROR',
				'MESSAGE' => GetMessage("SC_FILES_FAIL"),
				'DETAILS' => implode("<br>\n",$ob->arFail),
				'HTML' => true
				)
			);
			?><script>parent.access_check_start(0);</script><?

		}
		die();
}
elseif($_REQUEST['test_start'] && check_bitrix_sessid())
{
	$test_mysql = (strtolower($DB->type) == 'mysql');
	$oTest = new CTest($_REQUEST['step'], $test_mysql);
	$oTest->test_last_value = base64_decode($_REQUEST['test_last_value']);

	$oTest->Start();
	echo 'CurrentStatus = Array('.$oTest->percent.',"'.$oTest->strNextTestName.'", "'.($oTest->percent < 100 ? '&step='.$oTest->step.'&test_last_value='.base64_encode($oTest->test_last_value) : '').'","'.$oTest->strCurrentTestName.'","'.CUtil::JSEscape(str_replace(array("\r","\n"),"",$oTest->strResult)).'");';
	die();
}


$tabControl = new CAdminTabControl("tabControl", $aTabs);

$APPLICATION->SetTitle(GetMessage("SC_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

echo BeginNote();
echo GetMessage("SC_NOTES1");
echo EndNote();
?>

<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2"><?=GetMessage("SC_SUBTITLE_REQUIED_DESC")?></td>
	</tr>
	<tr>
	<td colspan="2">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="internal">
		<tr class="heading">
			<td align="center"><b><?=GetMessage("SC_PARAM")?></b></td>
			<td align="center"><b><?=GetMessage("SC_REQUIED")?></b></td>
			<td align="center"><b><?=GetMessage("SC_CURRENT")?></b></td>
		</tr>
	<?
	$strSERVER_SOFTWARE = $_SERVER["SERVER_SOFTWARE"];
	if (strlen($strSERVER_SOFTWARE)<=0)
		$strSERVER_SOFTWARE = $_SERVER["SERVER_SIGNATURE"];

	$strWebServer = "";
	$strWebServerVersion = "";
	$strSERVER_SOFTWARE = Trim($strSERVER_SOFTWARE);
	if (preg_match("#^([a-zA-Z-]+).*?([\d]+\.[\d]+(\.[\d]+)?)#i", $strSERVER_SOFTWARE, $arSERVER_SOFTWARE))
	{
		$strWebServer = $arSERVER_SOFTWARE[1];
		$strWebServerVersion = $arSERVER_SOFTWARE[2];

		$vercheck_min = "";
		$vercheck_max = "";
		if (strtoupper($strWebServer)=="APACHE")
		{
			$vercheck_min = $Apache_vercheck_min;
			$vercheck_max = $Apache_vercheck_max;
		}
		elseif (strtoupper($strWebServer)=="MICROSOFT-IIS")
		{
			$vercheck_min = $IIS_vercheck_min;
			$vercheck_max = $IIS_vercheck_max;
		}
	}
	?>
	<tr>
		<td valign="top"><?=str_replace("#SERVER#", ((strlen($strWebServer)>0) ? $strWebServer : GetMessage("SC_UNKNOWN")), GetMessage("SC_SERVER_VERS"))?></td>
		<td valign="top"><?
				if (strlen($vercheck_min)>0)
					echo str_replace("#VER#", $vercheck_min, GetMessage("SC_VER_VILKA1"));
				if (strlen($vercheck_min)>0 && strlen($vercheck_max)>0)
					echo GetMessage("SC_VER_VILKA2");
				if (strlen($vercheck_max)>0)
					echo str_replace("#VER#", $vercheck_max, GetMessage("SC_VER_VILKA3"));
				?></td>
		<td valign="top"><?
				if (strlen($strWebServerVersion)>0)
					ShowResult($strWebServerVersion, ((CheckVersionMinimax($strWebServerVersion, $vercheck_min, $vercheck_max)) ? "O" : "E"));
				else
					ShowResult(GetMessage("SC_UNKNOWN1"), "E");
				?></td>
	</tr>

	<?
	if (strtolower($DB->type)=="oracle")
	{
		$vercheck_min = $Oracle_vercheck_min;
		$vercheck_max = $Oracle_vercheck_max;
	}
	elseif (strtolower($DB->type)=="mssql")
	{
		$vercheck_min = $MSSQL_vercheck_min;
		$vercheck_max = $MSSQL_vercheck_max;
	}
	else
	{
		$vercheck_min = $MySql_vercheck_min;
		$vercheck_max = $MySql_vercheck_max;
	}
	?>
	<tr>
		<td valign="top"><?=str_replace("#DB#", $DB->type, GetMessage("SC_DB_VERS"))?></td>
		<td valign="top"><?
				if (strlen($vercheck_min)>0)
					echo str_replace("#VER#", $vercheck_min, GetMessage("SC_VER_VILKA1"));
				if (strlen($vercheck_min)>0 && strlen($vercheck_max)>0)
					echo GetMessage("SC_VER_VILKA2");
				if (strlen($vercheck_max)>0)
					echo str_replace("#VER#", $vercheck_max, GetMessage("SC_VER_VILKA3"));
				?>&nbsp;</td>
		<td valign="top"><?
				if ($version = $DB->GetVersion())
				{
					if (preg_match("#^([\d]+\.[\d]+(\.[\d]+)?)#i", $version, $arVers))
						ShowResult($arVers[1], ((CheckVersionMinimax($arVers[1], $vercheck_min, $vercheck_max)) ? "O" : "E"));
					else
						ShowResult(GetMessage("SC_UNKNOWN1"), "E");
				}
				else
					ShowResult(GetMessage("SC_ERR_QUERY_VERS"), "E");
				?></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("SC_PHP_VERS")?></td>
		<td valign="top"><?
				if (strlen($PHP_vercheck_min)>0)
					echo str_replace("#VER#", $PHP_vercheck_min, GetMessage("SC_VER_VILKA1"));
				if (strlen($PHP_vercheck_min)>0 && strlen($PHP_vercheck_max)>0)
					echo GetMessage("SC_VER_VILKA2");
				if (strlen($PHP_vercheck_max)>0)
					echo str_replace("#VER#", $PHP_vercheck_max, GetMessage("SC_VER_VILKA3"));
				?>&nbsp;</td>
		<td valign="top"><?ShowResult(phpversion(), (!CheckVersionMinimax(phpversion(), $PHP_vercheck_min, $PHP_vercheck_max) ? "E" : "O"))?></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("SC_OS_VERSION")?></td>
		<td valign="top"></td>
		<td valign="top"><?=ShowResult(htmlspecialchars(PHP_OS), "O")?></td>
	</tr>
	<?
		$bDomainFound = false;
		$bUtf = false;
		$bChar = false;
		$arDocRoot = array();

		$rs = CSite::GetList($by,$order,array('ACTIVE'=>'Y'));
		while($f = $rs->Fetch())
		{
			$charset = strtolower($f['CHARSET']);
			$bFound = strpos($charset,'utf')!==false;

			$bUtf = $bUtf || $bFound;
			$bChar = $bChar || !$bFound;
			$arDocRoot[] = trim($f['DOC_ROOT']);

			if ($f['SERVER_NAME'] == $_SERVER['SERVER_NAME'] || strpos($f['DOMAINS'],$_SERVER['HTTP_HOST'])!==false)
				$bDomainFound = true;
		}
	?>
	<? if (!$bDomainFound) : ?>
		<tr>
			<td valign="top">&nbsp;- <?=GetMessage("SC_VAR_VALUE")?> <i>$_SERVER['HTTP_HOST']</i></td>
			<td valign="top"><?=GetMessage("SC_DOC_ROOT_HELP")?></td>
			<td valign="top"><?=ShowResult(htmlspecialchars($_SERVER['HTTP_HOST']), "E")?></td>
		</tr>
	<? endif; ?>
	<? if ($bUtf && (!defined('BX_UTF') || BX_UTF !== true)) : ?>
		<tr>
			<td valign="top">&nbsp;- <?=GetMessage("SC_CONSTANT")?> BX_UTF</td>
			<td valign="top"><?=GetMessage("SC_UTF_HELP")?></td>
			<td valign="top"><?=ShowResult(GetMessage("SC_BX_UTF_FAIL"), "E")?></td>
		</tr>
	<? endif; ?>
	<? if ($bUtf && $bChar) : ?>
		<tr>
			<td valign="top">&nbsp;- <?=GetMessage("SC_SITE_CHARSET")?></td>
			<td valign="top"><?=GetMessage("SC_SITE_CHARSET_HELP")?></td>
			<td valign="top"><?=ShowResult(GetMessage("SC_SITE_CHARSET_FAIL"), "E")?></td>
		</tr>
	<? endif; ?>
	<?
		$ms_ok = true;
		if (count($arDocRoot) == 1)
		{
			if ($arDocRoot[0])
			{
				$ms_req = GetMessage('SC_PATH_FAIL');
				$ms_cur = htmlspecialchars(trim($arDocRoot[0]));
				$ms_ok = false;
			}
		}
		else
		{
			foreach($arDocRoot as $root)
			{
				if ($root)
				{
					if (!is_readable($root.'/bitrix'))
					{
						$ms_req = GetMessage('SC_ROOT_ACCESS');
						$ms_cur = GetMessage('SC_NO_ACCESS').' '.$root.'/bitrix';
						$ms_ok = false;
						break;
					}
				}
			}
		}
	?>
	<? if (!$ms_ok) : ?>
		<tr>
			<td valign="top">&nbsp;- <?=GetMessage("SC_MULTISITE")?></td>
			<td valign="top"><?=$ms_req?></td>
			<td valign="top"><?=ShowResult($ms_cur, "E")?></td>
		</tr>
	<? endif; ?>
	<tr>
		<td colspan="3"><b><?=GetMessage("SC_PHP_SETTINGS")?></b></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - safe mode</td>
		<td valign="top"><?=GetMessage("SC_TURN_OFF")?></td>
		<td valign="top"><?=((GetPHPSetting("safe_mode")=="ON") ? ShowResult(GetMessage("SC_TURN_ON"), "E") : ShowResult(GetMessage("SC_TURN_OFF"), "O"))?></td>
	</tr>
	<?

		$overload  = intval(ini_get('mbstring.func_overload'));
		$encoding = strtolower(ini_get('mbstring.internal_encoding'));

		if ($bUtf)
		{
			$mb_string_req = 'mbstring.func_overload=2<br>mbstring.internal_encoding=utf-8';
			$mb_string_cur = 'mbstring.func_overload='.$overload.'<br>mbstring.internal_encoding='.$encoding;
			$mb_string_ok = ($overload == 2) && ($encoding == 'utf8' || $encoding == 'utf-8');
		}
		else
		{
			if ($overload == 2)
			{
				$mb_string_ok = false === strpos($encoding,'utf');
				$mb_string_req = 'mbstring.internal_encoding=cp1251';
				$mb_string_cur = 'mbstring.internal_encoding='.$encoding;
			}
			else
			{
				$mb_string_req = 'mbstring.func_overload=0';
				$mb_string_cur = 'mbstring.func_overload='.$overload;
				$mb_string_ok = $overload == 0;
				
			}
		}
	?>
	<tr>
		<td valign="top">&nbsp; - mbstring</td>
		<td valign="top"><?=$mb_string_req?></td>
		<td valign="top"><?=ShowResult($mb_string_cur, $mb_string_ok ? "O" : "E")?></td>
	</tr>
	<?
		$val = ini_get_bool('magic_quotes_sybase');
	?>
	<tr>
		<td valign="top">&nbsp; - magic_quotes_sybase</td>
		<td valign="top"><?=GetMessage("SC_TURN_OFF")?></td>
		<td valign="top"><?=ShowResult($val ? GetMessage("SC_TURN_ON") : GetMessage("SC_TURN_OFF"), $val ? "E" : "O")?></td>
	</tr>
	<?
		$val = ini_get_bool('allow_call_time_pass_reference');
	?>
	<tr>
		<td valign="top">&nbsp; - allow_call_time_pass_reference</td>
		<td valign="top"><?=GetMessage("SC_TURN_ON")?></td>
		<td valign="top"><?=ShowResult($val ? GetMessage("SC_TURN_ON") : GetMessage("SC_TURN_OFF"), $val ? "O" : "E")?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <?=GetMessage("SC_AVAIL_MEMORY")?></td>
		<td valign="top"><?
				if (IntVal($SYSTEM_min_avail_memory)>0)
					echo str_replace("#SIZE#", $SYSTEM_min_avail_memory, GetMessage("SC_AVAIL_MEMORY_MIN"));
				if (IntVal($SYSTEM_min_avail_memory)>0 && IntVal($SYSTEM_rec_avail_memory)>0)
					echo ", ";
				if (IntVal($SYSTEM_rec_avail_memory)>0)
					echo str_replace("#SIZE#", $SYSTEM_rec_avail_memory, GetMessage("SC_AVAIL_MEMORY_REC"));
				?></td>
		<td valign="top"><?
				$memory_limit = ini_get('memory_limit');
				if (!$memory_limit || strlen($memory_limit)<=0)
					$memory_limit = get_cfg_var('memory_limit');

				$memory_limit = IntVal(Trim($memory_limit));
				echo (($memory_limit < $SYSTEM_min_avail_memory) ? ShowResult($memory_limit." Mb", "E") : ShowResult($memory_limit." Mb", "O") );
				?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <?=GetMessage("SC_ALLOW_UPLOAD")?> (file_uploads)</td>
		<td valign="top"><?=GetMessage("SC_TURN_ON1")?></td>
		<td valign="top"><?
				if (GetPHPSetting("file_uploads")=="ON")
				{
					ShowResult(GetMessage("SC_TURN_ON1"), "O");
					$sp = ini_get("upload_tmp_dir");
					if (strlen($sp)>0)
					{
						echo ", ";
						echo GetMessage("SC_TEMP_FOLDER")." <b>".$sp."</b> ";
						if (file_exists($sp))
						{
							if (is_writable($sp))
								ShowResult(GetMessage("SC_CAN_WRITE1"), "O");
							else
								ShowResult(GetMessage("SC_CAN_NOT_WRITE1"), "E");
						}
						else
							ShowResult(GetMessage("SC_NOT_EXISTS"), "E");
					}
#					else
#						ShowResult(GetMessage("SC_NO_TEMP_FOLDER"), "E");
				}
				else
					ShowResult(GetMessage("SC_TURN_OFF1"), "E");
				?></td>
	</tr>

	<tr>
		<td colspan="3"><b><?=GetMessage("SC_REQUIED_PHP_MODS")?></b></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/function.fsockopen.php" target="_blank"><?=GetMessage("SC_SOCKET_F")?></a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?=((function_exists("fsockopen")) ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.xml.php" target="_blank"><?=GetMessage("SC_MOD_XML")?></a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?=((extension_loaded('xml') && function_exists("xml_parser_create")) ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.regex.php" target="_blank"><?=GetMessage("SC_MOD_POSIX_REG")?></a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?=(function_exists("eregi") ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.pcre.php" target="_blank"><?=GetMessage("SC_MOD_PERL_REG")?></a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?=(function_exists("preg_match") ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/function.imagettftext.php" target="_blank">FreeType library</a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?=(function_exists("imagettftext") ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.zlib.php" target="_blank">Zlib Compression</a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?= ((extension_loaded('zlib') && function_exists("gzcompress")) ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E") )?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/ref.image.php" target="_blank"><?=GetMessage("SC_MOD_GD_F")?></a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?= (function_exists("imagecreatetruecolor") && function_exists("imagecreatefromjpeg") ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E"))?></td>
	</tr>
	<? if ($bUtf) : ?>
	<tr>
		<td valign="top">&nbsp; - <a title="<?=GetMessage("SC_EXTINSION_VIEW")?>" href="http://www.php.net/manual/en/book.mbstring.php" target="_blank"><?=GetMessage("SC_MBSTRING_F")?></a></td>
		<td valign="top"><?=GetMessage("SC_SETTED")?></td>
		<td valign="top"><?=(function_exists("mb_substr")) ? ShowResult(GetMessage("SC_SETTED"), "O") : ShowResult(GetMessage("SC_NOT_SETTED"), "E")?></td>
	</tr>
	<? endif; ?>
	</table>
	</td>
	</tr>
<?flush();

$tabControl->BeginNextTab();?>
	<tr>
		<td colspan="2"><?echo GetMessage("SC_SUBTITLE_DISK_DESC");?></td>
	</tr>
	<tr>
		<td colspan="2">
		<script>
		function onFrameLoad(ob)
		{
			CloseWaitWindow();
			if (ob.contentDocument)
				oDoc = ob.contentDocument;
			else
				oDoc = ob.contentWindow.document;

			document.getElementById('access_result').innerHTML = oDoc.body.innerHTML
		}

		function access_check_start(val)
		{
			document.getElementById('access_submit').disabled = val ? 'disabled' : '';
			document.getElementById('access_stop').disabled = val ? '' : 'disabled';

			if (val)
				ShowWaitWindow();
			else
				CloseWaitWindow();
		}
		</script>
			<? # CAdminMessage::ShowMessage(Array("MESSAGE"=>GetMessage("SC_CHECK_FILES_ATTENTION"), "TYPE"=>"ERROR","DETAILS"=>GetMessage("SC_CHECK_FILES_WARNING")));	?>
			<form method="POST" action="site_checker.php" target="access_frame" onsubmit="access_check_start(1)">
			<input type=hidden name=access_check value=Y>
			<?=bitrix_sessid_post();?>
			<label><input type=radio name=check_type value=full checked> <?=GetMessage("SC_CHECK_FULL")?></label><br>
			<label><input type=radio name=check_type value=upload> <?=GetMessage("SC_CHECK_UPLOAD")?></label><br>
			<label><input type=radio name=check_type value=kernel> <?=GetMessage("SC_CHECK_KERNEL")?></label><br>
			<? if (BX_ROOT != BX_PERSONAL_ROOT): ?>
				<label><input type=radio name=check_type value=cache> <?=GetMessage("SC_CHECK_FOLDER")?> <b><?=BX_PERSONAL_ROOT?></b></label><br>
			<? endif; ?>
			<br>
			<input type=submit value="<?=GetMessage("SC_CHECK_B")?>" id="access_submit">
			<input type=button value="<?=GetMessage("SC_STOP_B")?>" disabled id="access_stop" onclick="access_check_start(0)">
			</form>
			<div width="100%" id="access_result"></div>
			<iframe name="access_frame" style="width:1px;height:1px;visibility:hidden" onload="onFrameLoad(this)"></iframe>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2"><?=GetMessage("SC_CONF_HELP")?></td>
	</tr>
	<tr>
	<td colspan="2">
	<?
	$clean_test_table = '<table id="result_table" cellpadding="0" cellspacing="0" border="0" width="100%" class="internal"><tr class="heading"><td width="40%"><b>'.GetMessage("SC_TEST_NAME").'</b></td><td><b>'.GetMessage("SC_TEST_RES").'</b></td></tr></table>';

	$oTest = new CTest(); 
	?>

	<script>
		var bTestFinished = false;
		var bSubmit;
		function set_start(val)
		{
			document.getElementById('test_start').disabled = val ? 'disabled' : '';
			document.getElementById('test_stop').disabled = val ? '' : 'disabled';
			document.getElementById('progress').style.display = val ? 'block' : 'none';

			if (val)
			{
				ShowWaitWindow();
				document.getElementById('result').innerHTML = '<?=$clean_test_table?>';
				document.getElementById('status').innerHTML = '<?=$oTest->strCurrentTestName?>';

				document.getElementById('percent').innerHTML = '0%';
				document.getElementById('indicator').style.width = '0%';

				CHttpRequest.Action = test_onload;
				CHttpRequest.Send('site_checker.php?test_start=Y&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>');
			}
			else
				CloseWaitWindow();
		}

		function test_onload(result)
		{
			try
			{
				eval(result);

				iPercent = CurrentStatus[0];
				strTestName = CurrentStatus[1];
				strNextRequest = CurrentStatus[2];
				strCurrentTestName = CurrentStatus[3];
				strResult = CurrentStatus[4];
				
				document.getElementById('percent').innerHTML = iPercent + '%';
				document.getElementById('indicator').style.width = iPercent + '%';
 
				document.getElementById('status').innerHTML = strTestName;
				if (strResult != '')
				{
					oTable = document.getElementById('result_table');
					oRow = oTable.insertRow(-1);
					oCell = oRow.insertCell(-1);
					oCell.innerHTML = strCurrentTestName;
					oCell = oRow.insertCell(-1);
					oCell.innerHTML = strResult;
				}

				if (strNextRequest && document.getElementById('test_start').disabled)
					CHttpRequest.Send('site_checker.php?test_start=Y&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>' + strNextRequest);
				else // Finish
				{
					set_start(0);
					bTestFinished = true;
					if (bSubmit)
					{
						if (window.tabControl)
							tabControl.SelectTab('edit5');
						SubmitToSupport();	
					}
				}

			}
			catch(e) 
			{
				CloseWaitWindow();
				document.getElementById('test_start').disabled = '';
				document.getElementById('result').innerHTML += result;
				alert('<?=GetMessage("SC_TEST_FAIL")?>');
			}
		}
	</script>

		<input type=button value="<?=GetMessage("SC_START_TEST_B")?>" id="test_start" onclick="set_start(1)">
		<input type=button value="<?=GetMessage("SC_STOP_TEST_B")?>" disabled id="test_stop" onclick="bSubmit=false;set_start(0)">
		<div id="progress" style="display:none;" width="100%">
		<br>
			<div id="status"></div>
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr>
					<td height="10">
						<div style="border:1px solid #B9CBDF">
							<div id="indicator" style="height:10px; width:0%; background-color:#B9CBDF"></div>
						</div>
					</td>
					<td width=30>&nbsp;<span id="percent">0%</span></td>
				</tr>
			</table>
		</div>
		<div id="result" style="padding-top:10px"></div>




	</td>
	</tr>
<?
flush();

$tabControl->BeginNextTab();

if(!isset($strTicketError))
	$strTicketError = "";

if (false && $send_ticket=="Y")
{
	if (strlen($ticket_email)<=0)
	{
		$strTicketError .= GetMessage("SC_TIK_NO_EMAIL").". ";
		$aMsg[] = array("id"=>"ticket_email", "text"=>GetMessage("SC_TIK_NO_EMAIL"));
	}
	elseif (!check_email($ticket_email))
	{
		$strTicketError .= GetMessage("SC_TIK_EMAIL_ERR").". ";
		$aMsg[] = array("id"=>"ticket_email", "text"=>GetMessage("SC_TIK_EMAIL_ERR"));
	}

	if (strlen($ticket_text)<=0)
	{
		$strTicketError .= GetMessage("SC_TIK_NO_DESCR").". ";
		$aMsg[] = array("id"=>"ticket_text", "text"=>GetMessage("SC_TIK_NO_DESCR"));
	}

	if (strlen($strTicketError)<=0)
	{
		// E-Mail address
		if (defined("THIS_SITE_SUPPORT_EMAIL") && strlen(THIS_SITE_SUPPORT_EMAIL)>0)
			$strMailEMailTo = THIS_SITE_SUPPORT_EMAIL;
		else
		{
			if ($lang=="ru")
				$strMailEMailTo = "support@bitrixsoft.ru";
			else
				$strMailEMailTo = "support@bitrixsoft.com";
		}

		if (defined("THIS_SITE_SUPPORT_CHARSET") && strlen(THIS_SITE_SUPPORT_CHARSET)>0)
			$sCharset = THIS_SITE_SUPPORT_CHARSET;
		else
		{
			if(defined('BX_UTF'))
				$sCharset = "UTF-8";
			elseif($lang=="ru")
				$sCharset = "windows-1251";
			else
				$sCharset = "iso-8859-1";
		}

		// Subject
		$strMailSubject = "";
		if (false && strlen($ticket_number)>0)
		{
			$ticket_number = preg_replace("/[\D]+/i", "", $ticket_number);
			if ($lang=="ru")
				$strMailSubject .= "[TID#".$ticket_number."] www.bitrixsoft.ru: ".GetMessage("SC_RUS_L");
			else
				$strMailSubject .= "[TID#".$ticket_number."] www.bitrixsoft.com: Changes in request";
		}
		else
		{
			if ($lang=="ru")
				$strMailSubject = GetMessage("SC_RUS_L1")." ".$_SERVER["SERVER_NAME"];
			else
				$strMailSubject = "Request from ".$_SERVER["SERVER_NAME"];
		}

		// Body
		$sMimeBoundary = '==Multipart_Boundary_X'.md5(time()).'X';

		$strMailHeader =
			"From: $ticket_email\n".
			"Reply-To: $ticket_email\n".
			"X-Priority: 3 (Normal)\n".
			"Content-Transfer-Encoding: 8bit\n".
			"Content-Type: multipart/mixed;\n boundary=\"".$sMimeBoundary."\"\n";
			"MIME-Version: 1.0";

		$strMailText = "";

		// Body text
		$strMailText .= "--".$sMimeBoundary."\n";
		$strMailText .= "Content-Type: text/plain; charset=".$sCharset."\nContent-Transfer-Encoding: 8bit\n\n";

		$strMailText .= rtrim($ticket_text);
		if (strlen($_REQUEST["last_error_query"])>0)
			$strMailText .= "\n\nLast query error:\n".$_REQUEST["last_error_query"];

		$strMailText .= "\n\nLicense key: ".(LICENSE_KEY == "DEMO"? "DEMO" : md5("BITRIX".LICENSE_KEY."LICENCE"));
		$strMailText .= "\n\nVersion: ".(defined("DEMO") ? "DEMO" : (defined("ENCODE") ? "ENCODE" : "FULL"));

		$strMailText .= "\n\n\$_SERVER array content:\n<code>".print_r($_SERVER, True);
		$strMailText .= "</code>\n\n\$_ENV array content:\n<code>".print_r($_ENV, True);
		$strMailText .= "</code>\n\nCurrent time: ".date("Y-m-d H:i:s");

		$strMailText .= "\n";

		// Body attachment 1
		if ($ticket_phpinfo=="Y")
		{
			ob_start();
			phpinfo();
			$PHPinfo = ob_get_clean();

			$PHPinfo = chunk_split(base64_encode($PHPinfo));
			$strMailText .= "--".$sMimeBoundary."\n";
			$strMailText .= "Content-Type: text/html;\n name=\"phpinfo.html\"\n";
			$strMailText .= "Content-Transfer-Encoding: base64\n";
			$strMailText .= "Content-Disposition: attachment;\n filename=\"phpinfo.html\"\n\n";
			$strMailText .= $PHPinfo;
		}

		// Body attachment 2
		$strMailCheckerPage = chunk_split(base64_encode($strMailCheckerPage));
		$strMailText .= "--".$sMimeBoundary."\n";
		$strMailText .= "Content-Type: text/html;\n name=\"data.html\"\n";
		$strMailText .= "Content-Transfer-Encoding: base64\n";
		$strMailText .= "Content-Disposition: attachment;\n filename=\"data.html\"\n\n";
		$strMailText .= $strMailCheckerPage;

		$strMailText .= "--".$sMimeBoundary."--\n";

		// Mail
		$php_errormsg = "";
		if (bxmail($strMailEMailTo, $strMailSubject, $strMailText, $strMailHeader, COption::GetOptionString("main", "mail_additional_parameters", "")))
		{
			LocalRedirect("/bitrix/admin/site_checker.php?lang=".LANGUAGE_ID."&ticket_sent=Y&tabControl_active_tab=edit5&ticket_email=".$ticket_email);
		}
		else
		{
			$strTicketError .= GetMessage("SC_TIK_SEND_ERROR");
			if (strlen($php_errormsg)>0)
				$strTicketError .= ": ".$php_errormsg;
			$strTicketError .= ". ";
		}
	}
	LocalRedirect("/bitrix/admin/site_checker.php?lang=".LANGUAGE_ID."&tabControl_active_tab=edit5&strTicketError=".urlencode($strTicketError)."&ticket_sent=N&ticket_text=".urlencode($ticket_text)."&ticket_email=".urlencode($ticket_email));
}
?>
<tr><td colspan="2"><?
	if(isset($ticket_sent))
	{
		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			if($e = $APPLICATION->GetException())
			{
				$message = new CAdminMessage(GetMessage("SC_ERROR"), $e);
				if($message)
					echo $message->Show();
			}
		}

		if(strlen($strTicketError)>0 && !$message)
			CAdminMessage::ShowMessage($strTicketError);
		elseif(!$message)
			CAdminMessage::ShowNote(str_replace("#EMAIL#", $ticket_email, GetMessage("SC_TIK_SEND_SUCCESS")));
	}
		?></td>
</tr>
<script>
	function SubmitToSupport()
	{
		frm = document.forms.fticket;

		if (frm.ticket_text.value == '')
		{
			alert('<?=GetMessage("SC_NOT_FILLED")?>');
			return false;
		}

//		frm.submit_button.disabled = 'disabled';

		if (!bTestFinished && frm.ticket_test.checked)
		{
			alert('<?=GetMessage("SC_TEST_WARN")?>');
			if (window.tabControl)
				tabControl.SelectTab('edit3');
			bSubmit = true; // submit after test 
			set_start(1);
		}
		else if(frm.ticket_test.checked)
		{
			CHttpRequest.Action = function (result)
			{
				document.forms.fticket.test_file_contents.value = result;
				frm.submit();
			}
			CHttpRequest.Send('?read_log=Y');
		}
		else
			frm.submit();
	}
</script>
<?
		?>
<form method="POST" action="<?=SUPPORT_PAGE?>" name="fticket">
<input type="hidden" name="send_ticket" value="Y">
<input type="hidden" name="license_key" value="<?=(LICENSE_KEY == "DEMO"? "DEMO" : md5("BITRIX".LICENSE_KEY."LICENCE"))?>">
<input type="hidden" name="test_file_name" value="<?=$oTest->LogFile;?>">
<input type="hidden" name="test_file_contents" value="">
<input type="hidden" name="ticket_title" value="<?=GetMessage('SC_RUS_L1').' '.htmlspecialchars($_SERVER['HTTP_HOST'])?>">
<input type="hidden" name="BX_UTF" value="<?=(defined('BX_UTF') && BX_UTF==true)?'Y':'N'?>">
<input type="hidden" name="tabControl_active_tab" value="edit5">
<tr>
	<td valign="top"><span class="required">*</span><?=GetMessage("SC_TIK_DESCR")?><br>
			<small><?=GetMessage("SC_TIK_DESCR_DESCR")?></small></td>
	<td valign="top"><textarea name="ticket_text" rows="6" cols="60"><?= htmlspecialchars($ticket_text)?></textarea></td>
</tr>
<tr>
	<td valign="top"><label for="ticket_test"><?=GetMessage("SC_TIK_ADD_TEST")?></label></td>
	<td valign="top"><input type="checkbox" id="ticket_test" name="ticket_test" value="Y" checked></td>
</tr>
<? if (false) { ?>
<tr>
	<td valign="top"><label for="ticket_phpinfo"><?=GetMessage("SC_TIK_ADD_PHPINFO")?></label></td>
	<td valign="top"><input type="checkbox" id="ticket_phpinfo" name="ticket_phpinfo" value="Y" checked></td>
</tr>
<? } ?>
<?if (strlen($_REQUEST["last_error_query"])>0):?>
	<tr>
		<td valign="top"><?=GetMessage("SC_TIK_LAST_ERROR")?></td>
		<td valign="top">
			<?=GetMessage("SC_TIK_LAST_ERROR_ADD")?>
			<input type="hidden" name="last_error_query" value="<?= htmlspecialchars($_REQUEST["last_error_query"])?>">
		</td>
	</tr>
<?endif;?>
<tr>
	<td></td>
	<td>
		<input type="button" name="submit_button" onclick="SubmitToSupport()" value="<?=GetMessage("SC_TIK_SEND_MESS")?>">
	</td>
</tr>
<tr>
	<td colspan=2>
	<?
	echo BeginNote();
	echo GetMessage("SC_SUPPORT_COMMENT").' <a href="'.SUPPORT_PAGE.'" target=_blank>'.SUPPORT_PAGE.'</a>';
	echo EndNote();
	?>
	</td>
</tr>
</form>
<?
//$tabControl->Buttons();
$tabControl -> End();
$tabControl->ShowWarnings("fticket", $message);

#echo BeginNote();
#echo GetMessage("SC_COMMENT");
#echo EndNote();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

////////////////////////////////////////////////////////////////////////
//////////   FUNCTIONS   ///////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
function GetPHPSetting($val)
{
	return ((ini_get($val) == "1" || strtoupper(ini_get($val)) == "ON") ? "ON" : "OFF");
}

function ShowResult($strRes, $strType = "OK", $return = false)
{
	if (strlen($strRes)>0)
	{
		if (strtoupper($strType) == "ERROR" || strtoupper($strType) == "E")
			$res = "<span style=\"color:red;\"><b>".$strRes."</b></span>";
		elseif (strtoupper($strType) == "NOTE" || strtoupper($strType) == "N")
			$res = "<b>".$strRes."</b>";
		else
			$res = "<span style=\"color:green;\"><b>".$strRes."</b></span>";

		if ($return)
			return $res;
		else
			echo $res;
	}
}

function CheckVersionMinimax($strCurver, $strMinver, $strMaxver)
{
	$curver = explode(".", $strCurver);  for ($i = 0; $i < 3; $i++) $curver[$i] = IntVal($curver[$i]);
	$minver = explode(".", $strMinver);  for ($i = 0; $i < 3; $i++) $minver[$i] = IntVal($minver[$i]);
	$maxver = explode(".", $strMaxver);  for ($i = 0; $i < 3; $i++) $maxver[$i] = IntVal($maxver[$i]);

	if (($minver[0]>0 || $minver[1]>0 || $minver[2]>0)
		&&
		($curver[0]<$minver[0]
			|| (($curver[0]==$minver[0]) && ($curver[1]<$minver[1]))
			|| (($curver[0]==$minver[0]) && ($curver[1]==$minver[1]) && ($curver[2]<$minver[2]))
		))
		return false;
	elseif (($maxver[0]>0 || $maxver[1]>0 || $maxver[2]>0)
		&&
		($curver[0]>$maxver[0]
			|| (($curver[0]==$maxver[0]) && ($curver[1]>$maxver[1]))
			|| (($curver[0]==$maxver[0]) && ($curver[1]==$maxver[1]) && ($curver[2]>=$maxver[2]))
		))
		return false;
	else
		return true;
}

function CheckGetModuleInfo($path)
{
	include_once($path);

	$arr = explode("/", $path);
	$i = array_search("modules", $arr);
	$class_name = $arr[$i+1];

	return CModule::CreateModuleObject($class_name);
}

class CSearchFiles
{
	function CSearchFiles()
	{
		$this->StartTime = time();
		$this->arFail = array();
		$this->FilesCount = 0;
		$this->MaxFail = 9;
		$this->TimeLimit = 0;

		$this->SkipPath = '';
		$this->BreakPoint = '';

	}

	function Search($path) 
	{
		if (time() - $this->StartTime > $this->TimeLimit)
		{
			$this->BreakPoint = $path;
			return count($this->arFail) == 0;
		}

		if (count($this->arFail) > $this->MaxFail)
			return false;

		if ($this->SkipPath)
		{
			if (0!==strpos($this->SkipPath, dirname($path)))
				return;

			if ($this->SkipPath == $path)
				unset($this->SkipPath);
		}

		if (is_dir($path))
		{
			if (is_readable($path))
			{
				if (!is_writable($path))
					$this->arFail[] = $path;

				$dir = opendir($path);
				while($item = readdir($dir))
				{
					if ($item == '.' || $item == '..')
						continue;

					$this->Search($path.'/'.$item);
					if ($this->BreakPoint)
						break;
				}
				closedir($dir);
			}
			else
				$this->arFail[] = $path;
		}
		elseif (!$this->SkipPath)
		{
			$this->FilesCount++;
			if (!is_readable($path) || !is_writable($path))
				$this->arFail[] = $path;
		}
		return count($this->arFail) == 0;
	}
}


function getHttpResponse($res, $strRequest, &$strHeaders)
{
	fputs($res, $strRequest);

	$strHeaders = "";
	$bChunked = False;
	$Content_Length = false;
	while (!feof($res) && ($line = fgets($res, 4096)) && $line != "\r\n")
	{
		$strHeaders .= $line;
		if (preg_match("/Transfer-Encoding: +chunked/i", $line))
			$bChunked = True;

		if (preg_match("/Content-Length: +([0-9]+)/i", $line, $regs))
			$Content_Length = $regs[1];
				
	}

	$strRes = "";
	if ($bChunked)
	{
		$maxReadSize = 4096;

		$length = 0;
		$line = fgets($res, $maxReadSize);
		$line = StrToLower($line);

		$strChunkSize = "";
		$i = 0;
		while ($i < StrLen($line) && in_array($line[$i], array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f")))
		{
			$strChunkSize .= $line[$i];
			$i++;
		}

		$chunkSize = hexdec($strChunkSize);

		while ($chunkSize > 0)
		{
			$processedSize = 0;
			$readSize = (($chunkSize > $maxReadSize) ? $maxReadSize : $chunkSize);

			while ($readSize > 0 && $line = fread($res, $readSize))
			{
				$strRes .= $line;
				$processedSize += StrLen($line);
				$newSize = $chunkSize - $processedSize;
				$readSize = (($newSize > $maxReadSize) ? $maxReadSize : $newSize);
			}
			$length += $chunkSize;

			$line = FGets($res, $maxReadSize);

			$line = FGets($res, $maxReadSize);
			$line = StrToLower($line);

			$strChunkSize = "";
			$i = 0;
			while ($i < StrLen($line) && in_array($line[$i], array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f")))
			{
				$strChunkSize .= $line[$i];
				$i++;
			}

			$chunkSize = hexdec($strChunkSize);
		}
	}
	elseif ($Content_Length !== false)
	{
		if ($Content_Length > 0)
			$strRes = fread($res, $Content_Length);
	}
	else
		while ($line = fread($res, 4096))
			$strRes .= $line;

	fclose($res);
	return $strRes;
}

class CTest
{
	function CTest($step = 0, $test_mysql = false)
	{
		$this->step = intval($step);
		$this->test_percent = 0;
		$this->test_last_value = '';
		$this->strResult = '';
		$this->strError = '';
		$this->timeout = 10; // sec for one step

		$this->arTest = array(
			array('OpenLog' => GetMessage('SC_T_LOG')),
			array('check_docroot' => GetMessage('SC_T_DOCROOT')),
			array('check_socket' => GetMessage('SC_T_SOCK')),
			array('check_upload' => GetMessage('SC_T_UPLOAD')),
			array('check_mail' => GetMessage('SC_T_MAIL')),
			array('check_mail_big' => GetMessage('SC_T_MAIL_BIG')),
			array('check_mail_b_event' => GetMessage('SC_T_MAIL_B_EVENT')),
			array('check_localredirect' => GetMessage('SC_T_REDIRECT')),
			array('check_memory_limit' => GetMessage('SC_T_MEMORY')),
			array('check_session' => GetMessage('SC_T_SESS')),
			array('check_session_ua' => GetMessage('SC_T_SESS_UA')),
			array('check_cache' => GetMessage('SC_T_CACHE')),
			array('check_update' => GetMessage('SC_UPDATE_ACCESS')),
			array('check_http_auth' => GetMessage('SC_T_AUTH')),
			array('check_exec' => GetMessage('SC_T_EXEC')),
//			array('check_basename' => GetMessage('SC_T_BASENAME')), # http://bugs.php.net/bug.php?id=33898
			array('check_suhosin' => GetMessage('SC_T_SUHOSIN')),
			array('check_security' => GetMessage('SC_T_SECURITY')),
			array('check_divider' => GetMessage('SC_T_DELIMITER')),
			array('check_wincache' => GetMessage('SC_T_WINCACHE')),
		);

		$this->arTestDB = array(
			array('check_mysql_bug_version' => GetMessage('SC_T_MYSQL_VER')),
			array('check_mysql_time' => GetMessage('SC_T_TIME')),
			array('check_mysql_mode' => GetMessage('SC_T_SQL_MODE')),
			array('check_mysql_increment' => GetMessage('SC_T_AUTOINC')),
			array('check_mysql_table_charset' => GetMessage('SC_T_CHARSET')),
			array('check_mysql_table_status' => GetMessage('SC_T_STATUS')),
		);

		if ($test_mysql)
			$this->arTest = array_merge($this->arTest, $this->arTestDB);

		list($this->function, $this->strCurrentTestName) = each($this->arTest[$this->step]);
		$this->strNextTestName = $this->strCurrentTestName;

		include($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/license_key.php');
		if ($LICENSE_KEY)
			$this->LogFile = BX_ROOT.'/site_checker_'.md5('SITE_CHECKER'.$LICENSE_KEY).'.log';
		else
			$this->LogFile = false;
	}

	function Start()
	{
		$this->test_percent = 100; // by default

		@$this->OpenLog('ADD');
		if ($this->function != 'OpenLog' && $f = $this->LogResourse)
		{
			$text = date('Y-M-d H:i:s').' '.($this->test_last_value ? GetMessage('SC_CONT') : GetMessage('SC_START')).' '.$this->strCurrentTestName.' ('.$this->function.")\n";
			fwrite($f, $text);
		}

		ob_start();
		$result = call_user_func(array($this,$this->function));
		$this->strError = ob_get_clean();


		if ($result === null) // must return something
			$this->strResult = $this->Result(false,GetMessage('SC_INT_ERROR').' '.$this->function);
		elseif ($result === false || $result === true)
			$this->strResult = $this->Result($result);
		elseif ($result)
			$this->strResult = $result;

		if ($f)
		{
			$text = date('Y-M-d H:i:s').' '.GetMessage('SC_END').' '.$this->strCurrentTestName.' ('.$this->function.")\n";
			if ($this->test_percent < 100)
				$text .= $this->test_percent.'% '.GetMessage('SC_PARTIAL').' '.$this->test_last_value."\n";

			if ($result === true)
				$text .= "OK\n";
			elseif ($result === false)
				$text .= "Fail\n";
			else
				$text .= strip_tags($result)."\n";

			if ($this->strError)
				$text .= strip_tags($this->strError)."\n";

			fwrite($f, $text);
		}

		$this->percent = floor(($this->step + $this->test_percent / 100) / count($this->arTest) * 100);

		if ($this->test_percent >= 100  && ($this->step + 1) < count($this->arTest)) // next step
		{
			$this->step++;
			$this->test_percent = 0;
			$this->test_last_value = '';
			list($this->function, $this->strNextTestName) = each($this->arTest[$this->step]);
		}
		elseif ($result === true)
			$this->strResult = ''; // in case of temporary result on this step
	}

	function Result($res, $text = '')
	{
		$type = ($res === null) ? 'NOTE' : ($res ? 'OK' : 'ERROR');

		return ShowResult($text ? $text : ($res ? GetMessage('SC_TEST_SUCCESS') : GetMessage('SC_ERROR')), $type, true);
	}

	function OpenLog($add = false)
	{
		if (!$this->LogFile)
			return false;

		if ($this->LogResourse = fopen($_SERVER['DOCUMENT_ROOT'].$this->LogFile, $add ? 'ab' : 'wb'))
			return $this->Result(null, GetMessage('SC_LOG_OK').' <a href="'.$this->LogFile.'" target="_blank">'.GetMessage('SC_F_OPEN').'</a>');
		else
			return $this->Result(false,GetMessage('SC_CHECK_FILES'));

	}

	###### TESTS #######
	function check_mail($big = false)
	{
		$body = "Test message.\nDelete it.";
		if ($big)
		{
			$str = file_get_contents(__FILE__);
			if (!$str)
				return $this->Result(false, GetMessage('SC_CHECK_FILES'));

			$body = str_repeat($str, 10);
		}

		list($usec0, $sec0) = explode(" ", microtime());
		$val = mail("hosting_test@bitrix.ru","Bitrix site checker",$body);
		list($usec1, $sec1) = explode(" ", microtime());
		$time = round($sec1 + $usec1 - $sec0 - $usec0, 2);
		if ($val)
		{
			if ($time > 1)
				return $this->Result(false, GetMessage('SC_SENT').' '.$time.' '.GetMessage('SC_SEC'));
		}
		else
			return false;

		return true;
	}

	function check_mail_big()
	{
		return $this->check_mail($big = true);
	}

	function check_mail_b_event()
	{
		$res = $GLOBALS['DB']->Query("SELECT COUNT(1) AS A FROM b_event WHERE SUCCESS_EXEC = 'N'");
		$f = $res->Fetch();
		if ($f['A'] > 0)
			return $this->Result(false, GetMessage('SC_T_MAIL_B_EVENT_ERR').' '.$f['A']);
		return true;
	}

	function check_socket()
	{
		$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost';
		$port = $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80;
		$ssl = $port == 443 ? 'ssl://' : '';

		$strRequest = "GET ".$_SERVER['PHP_SELF']."?socket_test=Y&unique_id=".checker_get_unique_id()." HTTP/1.1\r\n";
		$strRequest.= "Host: ".$host."\r\n";
		$strRequest.= "\r\n";

		$res = fsockopen($ssl.$host, $port, $errno, $errstr, 5);
		if (!$res)
			return $this->Result(false,str_replace(array('#SERVER#','#PORT#'),array($host,$port),GetMessage('SC_NO_CONNECT')));

		$strRes = getHttpResponse($res, $strRequest, $strHeaders);

		return trim($strRes) == 'SUCCESS';
	}

	function check_upload()
	{
		$BinaryData = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/images/1.gif');
		if (!$BinaryData)
			return $this->Result(false,GetMessage('SC_CHECK_FILES'));

		$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost';
		$port = $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80;
		$ssl = $port == 443 ? 'ssl://' : '';

		$boundary = '--------'.md5(checker_get_unique_id());

		$POST = "--$boundary\r\n";
		$POST.= 'Content-Disposition: form-data; name="test_file"; filename="1.gif"'."\r\n";
		$POST.= 'Content-Type: image/gif'."\r\n";
		$POST.= "\r\n";
		$POST.= $BinaryData."\r\n";
		$POST.= "--$boundary\r\n";

		$strRequest = "POST ".$_SERVER['PHP_SELF']."?upload_test=Y&unique_id=".checker_get_unique_id()." HTTP/1.1\r\n";
		$strRequest.= "Host: ".$host."\r\n";
		$strRequest.= "Content-Type: multipart/form-data; boundary=$boundary\r\n";
		$strRequest.= "Content-Length: ".(function_exists('mb_strlen') ? mb_strlen($POST, 'ISO-8859-1') : strlen($POST))."\r\n";
		$strRequest.= "\r\n";
		$strRequest.= $POST;

		$res = fsockopen($ssl.$host, $port, $errno, $errstr, 5);
		if (!$res)
			return $this->Result(false,str_replace(array('#SERVER#','#PORT#'),array($host,$port),GetMessage('SC_NO_CONNECT')));

		$strRes = getHttpResponse($res, $strRequest, $strHeaders);

		return trim($strRes) == 'SUCCESS';

	}
	function check_memory_limit()
	{

		$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost';
		$port = $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80;
		$ssl = $port == 443 ? 'ssl://' : '';

		$total_steps = 7;
		$total_fail = 3;

		if (!$this->test_last_value)
		{
			$last_success = 0;
			$max = 6;
			$step = 1;
		}
		else
			list($last_success, $max, $step) = unserialize($this->test_last_value);

		$strRequest = "GET ".$_SERVER['PHP_SELF']."?memory_test=Y&unique_id=".checker_get_unique_id()."&max=".($max - 1)." HTTP/1.1\r\n";
		$strRequest.= "Host: ".$host."\r\n";
		$strRequest.= "\r\n";

		$res = fsockopen($ssl.$host, $port, $errno, $errstr, 5);
		if (!$res)
			return $this->Result(false,str_replace(array('#SERVER#','#PORT#'),array($host,$port),GetMessage('SC_NO_CONNECT')));

		$strRes = getHttpResponse($res, $strRequest, $strHeaders);

		if (trim($strRes) == 'SUCCESS')
		{
			$last_success = $max;
			$max *= 2;
		}
		else
			$max = floor(($last_success + $max) / 2);

		if ($max < 8)
			return false;

		if ($step < $total_steps)
		{
			$this->test_percent = floor(100 / $total_steps * $step);
			$step++;
			$this->test_last_value = serialize(array($last_success, $max, $step));
			return true;
		}

		return $this->Result(intval($last_success) > 32, GetMessage('SC_NOT_LESS',array('#VAL#'=>$last_success)));
	}

	function check_session()
	{
		if (!$this->test_last_value)
		{
			$_SESSION['CHECKER_CHECK_SESSION'] = 'SUCCESS';
			$this->test_percent = 50;
			$this->test_last_value = 'Y';
		}
		else
		{
			if ($_SESSION['CHECKER_CHECK_SESSION'] != 'SUCCESS')
				return false;
			unset($_SESSION['CHECKER_CHECK_SESSION']);
		}
		return true;
	}

	function check_session_ua()
	{

		$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost';
		$port = $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80;
		$ssl = $port == 443 ? 'ssl://' : '';

		$strRequest = "GET ".$_SERVER['PHP_SELF']."?session_test=Y&unique_id=".checker_get_unique_id()." HTTP/1.1\r\n";
		$strRequest.= "Host: ".$host."\r\n";

		if ($this->test_last_value) // second step: put session id
			$strRequest.= "Cookie: ".$this->test_last_value."\r\n";

		$strRequest.= "\r\n";

		$res = fsockopen($ssl.$host, $port, $errno, $errstr, 5);
		if (!$res)
			return $this->Result(false,str_replace(array('#SERVER#','#PORT#'),array($host,$port),GetMessage('SC_NO_CONNECT')));

		$strRes = getHttpResponse($res, $strRequest, $strHeaders);

		if (!$this->test_last_value) // first step: read session id
		{
			if (!preg_match('#Set-Cookie: ('.session_name().'=[a-z0-9\-\_]+?);#i',$strHeaders,$regs))
				return false;

			$this->test_last_value = $regs[1];
			$this->test_percent = 50;
			return true;
		}
		else
			return trim($strRes) == 'SUCCESS';
	}

	function check_http_auth()
	{
		$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost';
		$port = $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80;
		$ssl = $port == 443 ? 'ssl://' : '';

		$strRequest = "GET ".$_SERVER['PHP_SELF']."?auth_test=Y&unique_id=".checker_get_unique_id()." HTTP/1.1\r\n";
		$strRequest.= "Host: ".$host."\r\n";
		$strRequest.= "Authorization: Basic dGVzdF91c2VyOnRlc3RfcGFzc3dvcmQ=\r\n";
		$strRequest.= "\r\n";

		$res = fsockopen($ssl.$host, $port, $errno, $errstr, 5);
		if (!$res)
			return $this->Result(false,str_replace(array('#SERVER#','#PORT#'),array($host,$port),GetMessage('SC_NO_CONNECT')));

		$strRes = getHttpResponse($res, $strRequest, $strHeaders);

		return trim($strRes) == 'SUCCESS';
	}

	function check_update()
	{
		$ServerIP = COption::GetOptionString("main", "update_site", "www.bitrixsoft.com");
		$ServerPort = 80;

		$proxyAddr = COption::GetOptionString("main", "update_site_proxy_addr", "");
		$proxyPort = COption::GetOptionString("main", "update_site_proxy_port", "");
		$proxyUserName = COption::GetOptionString("main", "update_site_proxy_user", "");
		$proxyPassword = COption::GetOptionString("main", "update_site_proxy_pass", "");

		$bUseProxy = !$this->test_last_value && strlen($proxyAddr) > 0 && strlen($proxyPort) > 0;

		if ($bUseProxy)
		{
			$proxyPort = IntVal($proxyPort);
			if ($proxyPort <= 0)
				$proxyPort = 80;

			$requestIP = $proxyAddr;
			$requestPort = $proxyPort;
		}
		else
		{
			$requestIP = $ServerIP;
			$requestPort = $ServerPort;
		}

		$strRequest = "";
		$page = "us_updater_list.php";
		if ($bUseProxy)
		{
			$strRequest .= "POST http://".$ServerIP."/bitrix/updates/".$page." HTTP/1.0\r\n";
			if (strlen($proxyUserName) > 0)
				$strRequest .= "Proxy-Authorization: Basic ".base64_encode($proxyUserName.":".$proxyPassword)."\r\n";
		}
		else
			$strRequest .= "POST /bitrix/updates/".$page." HTTP/1.0\r\n";

		$strRequest.= "User-Agent: BitrixSMUpdater\r\n";
		$strRequest.= "Accept: */*\r\n";
		$strRequest.= "Host: ".$ServerIP."\r\n";
		$strRequest.= "Accept-Language: en\r\n";
		$strRequest.= "Content-type: application/x-www-form-urlencoded\r\n";
		$strRequest.= "Content-length: 7\r\n\r\n";
		$strRequest.= "lang=en";
		$strRequest.= "\r\n";

		$res = fsockopen($requestIP, $requestPort, $errno, $errstr, 5);

		if (!$res)
		{
			if ($bUseProxy)
				return $this->Result(false, GetMessage('SC_NO_PROXY'). ' ('.$errstr.')');
			else
				return $this->Result(false, GetMessage('SC_UPDATE_ERROR') . ' ('.$errstr.')');
		}
		else
		{
			$strRes = getHttpResponse($res, $strRequest, $strHeaders);

			$strRes = strtolower(strip_tags($strRes)); 
			if ($strRes == "license key is invalid" || $strRes == "license key is required")
				return true;
			else
			{
				if ($bUseProxy)
					return $this->Result(false, GetMessage('SC_PROXY_ERR_RESP'));
				else
					return $this->Result(false, GetMessage('SC_UPDATE_ERR_RESP'));
			}
		}
	}

	function check_cache()
	{
		$dir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/cache";
		$file0 = $dir."/".md5(mt_rand());
		$file1 = $file0.".tmp";
		$file2 = $file0.".php";
		if (!file_exists($dir))
			mkdir($dir, BX_DIR_PERMISSIONS);

		return ($f = fopen($file1, 'wb')) && (fclose($f)) && (rename($file1,$file2)) && (unlink($file2));
	}

	function check_exec()
	{
		$path = BX_ROOT.'/site_check_exec.php';
		if (file_exists($_SERVER['DOCUMENT_ROOT'].$path))
			return $this->Result(false,GetMessage('SC_FILE_EXISTS').' '.$path);
		if (!($f = fopen($_SERVER['DOCUMENT_ROOT'].$path, 'wb')))
			return $this->Result(false,GetMessage('SC_CHECK_FILES'));

		chmod($_SERVER['DOCUMENT_ROOT'].$path, BX_FILE_PERMISSIONS);

		fwrite($f,'<'.'? echo "SUCCESS"; ?'.'>');
		fclose($f);

		$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost';
		$port = $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80;
		$ssl = $port == 443 ? 'ssl://' : '';

		$strRequest = "GET ".$path." HTTP/1.1\r\n";
		$strRequest.= "Host: ".$host."\r\n";
		$strRequest.= "\r\n";

		$res = fsockopen($ssl.$host, $port, $errno, $errstr, 5);
		if (!$res)
			return $this->Result(false,str_replace(array('#SERVER#','#PORT#'),array($host,$port),GetMessage('SC_NO_CONNECT')));

		$strRes = getHttpResponse($res, $strRequest, $strHeaders);

		unlink($_SERVER['DOCUMENT_ROOT'].$path);

		return trim($strRes) == 'SUCCESS';

	}

	function check_basename()
	{
		$file = "\xD0\xBC\xD0\xB0\xD0\xBC\xD0\xB0.jpg";
		return basename('/test/path/'.$file) == $file;
	}

	function check_suhosin()
	{
		if (in_array('suhosin',get_loaded_extensions()))
			return $this->Result(null,GetMessage('SC_WARN_SUHOSIN',array('#VAL#',ini_get('suhosin.simulation'))));
		return true;
	}

	function check_security()
	{
		if (function_exists('apache_get_modules') && in_array('mod_security',apache_get_modules()))
			return $this->Result(null,GetMessage('SC_WARN_SECURITY'));
		return true;
	}
	
	function check_divider()
	{
		$locale_info = localeconv();
		$delimiter = $locale_info['decimal_point'];
		if ($delimiter != '.')
			return $this->Result(false,GetMessage('SC_DELIMITER_ERR',array('#VAL#',$delimiter)));

		return true;
	}

	function check_wincache()
	{
		return intval(ini_get('wincache.chkinterval')) == 0;
	}

	function check_localredirect()
	{
		if (!$this->test_last_value)
		{
			$host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost';
			$port = $_SERVER['SERVER_PORT'] ? $_SERVER['SERVER_PORT'] : 80;
			$ssl = $port == 443 ? 'ssl://' : '';

			$strRequest = "GET ".$_SERVER['PHP_SELF']."?redirect_test=Y&unique_id=".checker_get_unique_id()." HTTP/1.1\r\n";
			$strRequest.= "Host: ".$host."\r\n";
			$strRequest.= "\r\n";

			$res = fsockopen($ssl.$host, $port, $errno, $errstr, 5);
			if (!$res)
				return $this->Result(false,str_replace(array('#SERVER#','#PORT#'),array($host,$port),GetMessage('SC_NO_CONNECT')));

			$strRes = getHttpResponse($res, $strRequest, $strHeaders);

			if (preg_match('#Location: (.+)#', $strHeaders, $regs))
			{
				$url = trim($regs[1]);
				if (!$url)
					return false;

				$this->test_last_value = $url;
				$this->test_percent = 50;

				return true;
			}

			return false;
		}
		else
		{
			$url = $this->test_last_value;
			if (!$url)
				return false;

			$ar = parse_url($url);

			$host = $ar['host'];
			$port = intval($ar['port']) ? intval($ar['port']) : 80;
			$ssl = $ar['scheme'] == 'https' ? 'ssl://' : '';

			$res = fsockopen($ssl.$host, $port, $errno, $errstr, 5);
			if (!$res)
				return $this->Result(false,str_replace(array('#SERVER#','#PORT#'),array($host,$port),GetMessage('SC_NO_CONNECT')));

			$strRequest = "GET ".$_SERVER['PHP_SELF']."?redirect_test=Y&unique_id=".checker_get_unique_id()."&done=Y HTTP/1.1\r\n";
			$strRequest.= "Host: ".$host."\r\n";
			$strRequest.= "\r\n";

			$strRes = getHttpResponse($res, $strRequest, $strHeaders);

			return trim($strRes) == 'SUCCESS';
		}
	}

	function check_docroot()
	{
		$dir0 = realpath(str_replace('\\','/',dirname(__FILE__)));
		$dir1 = realpath(str_replace('\\','/',rtrim($_SERVER['DOCUMENT_ROOT'],'\\/').BX_ROOT).'/modules/main/admin');

		if ($dir0 != $dir1)
			return $this->Result(false,GetMessage('SC_DOCROOT_FAIL',array('#DIR0#'=>$dir0, '#DIR1#'=>$dir1)));
		return true;
	}

	function check_mysql_bug_version()
	{
		$ver = $GLOBALS['DB']->GetVersion();
		if ($ver == '4.1.21' // sorting
			|| $ver == '5.1.34' // auto_increment
			|| $ver == '5.0.41' // search
			)
			return $this->Result(false,GetMessage('SC_DB_ERR').' '.$ver);

		return true;
	}

	function check_mysql_mode()
	{
		$res = $GLOBALS['DB']->Query('SHOW VARIABLES LIKE \'sql_mode\'');
		$f = $res->Fetch();

		if (strlen($f['Value']) > 0)
			return $this->Result(false,GetMessage('SC_DB_ERR_MODE').' '.$f['Value']);
		return true;
	}

	function check_mysql_time()
	{
		$res = $GLOBALS['DB']->Query('SELECT UNIX_TIMESTAMP() AS A');
		$f = $res->Fetch();

		return abs(time() - $f['A']) < 10;
	}

	function check_mysql_increment()
	{
		global $DB;
		$ID = array();
		$table = 'b_site_checker_test';
		$DB->Query('DROP TABLE IF EXISTS '.$table);
		$DB->Query('CREATE TABLE '.$table.' (ID int(18) NOT NULL auto_increment, TEST varchar(50) default NULL, PRIMARY KEY  (`ID`))');
		$DB->Query('INSERT INTO '.$table.'(TEST) VALUES("TEST")');
		$DB->Query('INSERT INTO '.$table.'(TEST) VALUES("TEST")');

		$res = $DB->Query('SELECT ID FROM '.$table);
		while($f = $res->Fetch())
			$ID[] = $f['ID'];
		$increment = $ID[1] - $ID[0];
		$DB->Query('DROP TABLE IF EXISTS '.$table);
		return $increment == 1;
	}

	function check_mysql_table_charset()
	{
		global $DB;

		$time = time();
		$res = $DB->Query('SHOW VARIABLES LIKE "character_set_database"');
		$f = $res->Fetch();
		$charset = trim($f['Value']);
		$result = '';

		$bSiteUtf = defined('BX_UTF') && BX_UTF === true;
		$bCharUTf = $charset == 'utf8';

		if ($bSiteUtf != $bCharUTf)
			$result.= $this->Result(false,GetMessage('SC_DB_ERR_CHARSET',array('#CHAR#'=>$charset)))."<br>\n";

		$i = 0;
		$res = $DB->Query('SHOW TABLES LIKE "b_%"');
		while($f = $res->Fetch())
		{
			$i++;
			list($k,$table) = each($f);

			if ($this->test_last_value)
			{
				if ($this->test_last_value == $table)
					unset($this->test_last_value);
				continue;
			}
			
			$res0 = $DB->Query('SHOW CREATE TABLE `'.$table.'`');
			$f0 = $res0->Fetch();

			if (preg_match('/DEFAULT CHARSET=([^ ]+)/i',$f0['Create Table'],$regs))
			{
				$str = str_replace($regs[0],'',$f0['Create Table']);
				$t_char = trim($regs[1]);	
				if ($charset != $t_char)
					$result.= $this->Result(false,str_replace(array('#TBL#','#T_CHAR#','#CHARSET#'),array($table,$t_char,$charset),GetMessage('SC_DB_MISC_CHARSET')))."<br>\n";
				elseif (preg_match('/character set ([^ ]+)/i',$str,$regs) && trim($regs[1])!=$charset)
					$result.= $this->Result(false,GetMessage('SC_TABLE_CHARSET_WARN',array('#TABLE#'=>$table)))."<br>\n";
			}

			if (time()-$time >= $this->timeout)
			{
				$cnt = $res->SelectedRowsCount();
				$this->test_last_value = $table;
				$this->test_percent = floor($i/$cnt) * 100;
				break;
			}
		}
		return $result ? $result : true;
	}

	function check_mysql_table_status()
	{
		global $DB;

		$time = time();
		$result = '';
		$res = $DB->Query('SHOW VARIABLES LIKE "collation_database"');
		$f = $res->Fetch();
		$collation = trim($f['Value']);
		$warn_size = 1024 * 1024 * 1024 / 2; // 512M

		$i = 0;
		$res = $DB->Query('SHOW TABLES LIKE "b_%"');
		while($f = $res->Fetch())
		{
			$i++;
			list($k,$table) = each($f);

			if ($this->test_last_value)
			{
				if ($this->test_last_value == $table)
					unset($this->test_last_value);
				continue;
			}
			
			$res0 = $DB->Query('SHOW TABLE STATUS LIKE "'.$table.'"');
			$f0 = $res0->Fetch();

			if ($f0['Collation'] != $collation)
				$result.= $this->Result(false,GetMessage('SC_COLLATE_WARN',array('#TABLE#'=>$table,'#VAL0#'=>$f0['Collation'],'#VAL1#'=>$collation)))."<br>\n";

			if ($f0['Data_length'] > $warn_size)
				$result.= $this->Result(null,GetMessage('SC_TABLE_SIZE_WARN',array('#TABLE#'=>$table,'#SIZE#'=>floor($f0['Data_length']/1024/1024))))."<br>\n";

			if (time()-$time >= $this->timeout)
			{
				$cnt = $res->SelectedRowsCount();
				$this->test_last_value = $table;
				$this->test_percent = floor($i/$cnt) * 100;
				break;
			}
		}
		return $result ? $result : true;
	}
	###############
}

function checker_get_unique_id()
{
	@include($_SERVER['DOCUMENT_ROOT'].'/bitrix/license_key.php');
	if (!$LICENSE_KEY)
		$LICENSE_KEY = 'DEMO';
	return md5($_SERVER['DOCUMENT_ROOT'].filemtime(__FILE__).$LICENSE_KEY);
}
////////////////////////////////////////////////////////////////////////
//////////   END FUNCTIONS   ///////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
?>
