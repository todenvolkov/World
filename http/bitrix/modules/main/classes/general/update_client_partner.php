<?
/**********************************************************************/
/**    DO NOT MODIFY THIS FILE                                       **/
/**    MODIFICATION OF THIS FILE WILL ENTAIL SITE FAILURE            **/
/**********************************************************************/


//TODO: СИСТЕМА ОБНОВЛЕНИЙ, module.php, module_admin.php, 
//все файлы с CModule::CreateModuleObject ИЗМЕНЕНЫ!


if (!defined("BX_DIR_PERMISSIONS"))
	define("BX_DIR_PERMISSIONS", 0777);

define("DEFAULT_UPDATE_SERVER", "www.bitrixsoft.com");
//define("DEFAULT_UPDATE_SERVER", "mysql.smn");

IncludeModuleLangFile(__FILE__);

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

if (!defined("US_SHARED_KERNEL_PATH"))
	define("US_SHARED_KERNEL_PATH", "/bitrix");

if (!defined("US_CALL_TYPE"))
	define("US_CALL_TYPE", "ALL");

if (!defined("US_BASE_MODULE"))
	define("US_BASE_MODULE", "main");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_class.php");

$GLOBALS["UPDATE_STRONG_UPDATE_CHECK"] = "";
$GLOBALS["CACHE4UPDATESYS_LICENSE_KEY"] = "";

class CUpdateClientPartner
{
	function RegisterModules(&$strError, $lang = false, $stableVersionsOnly = false)
	{
		$strError_tmp = "";

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::RegisterModules");

		$strQuery = CUpdateClientPartner::__CollectRequestData(
			$strError_tmp,
			$lang,
			$stableVersionsOnly,
			array(),
			array()
		);
		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
		{
			$strError .= $strError_tmp;
			CUpdateClientPartner::AddMessage2Log("Empty query list", "GUL01");
			return False;
		}

		CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

		$stime = CUpdateClientPartner::__GetMicroTime();
		$content = CUpdateClientPartner::__GetHTTPPage("REG", $strQuery, $strError_tmp);

		if (strlen($content) <= 0)
		{
			if (StrLen($strError_tmp) <= 0)
				$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
		}

		CUpdateClientPartner::AddMessage2Log("TIME RegisterModules(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");

		if (strlen($strError_tmp) <= 0)
		{
			if (!($fp1 = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", "wb")))
				$strError_tmp .= "[URV02] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates", GetMessage("SUPP_RV_ER_TEMP_FILE")).". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			if (!fwrite($fp1, $content))
				$strError_tmp .= "[URV03] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", GetMessage("SUPP_RV_WRT_TEMP_FILE")).". ";

			@fclose($fp1);
		}

		if (strlen($strError_tmp) <= 0)
		{
			$updatesDirTmp = "";
			if (!CUpdateClientPartner::UnGzipArchive($updatesDirTmp, $strError_tmp, true))
				$strError_tmp .= "[URV04] ".GetMessage("SUPP_RV_BREAK").". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			$updatesDirFull = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$updatesDirTmp;
			if (!file_exists($updatesDirFull."/update_info.xml") || !is_file($updatesDirFull."/update_info.xml"))
				$strError_tmp .= "[URV05] ".str_replace("#FILE#", $updatesDirFull."/update_info.xml", GetMessage("SUPP_RV_ER_DESCR_FILE")).". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			if (!is_readable($updatesDirFull."/update_info.xml"))
				$strError_tmp .= "[URV06] ".str_replace("#FILE#", $updatesDirFull."/update_info.xml", GetMessage("SUPP_RV_READ_DESCR_FILE")).". ";
		}

		if (strlen($strError_tmp) <= 0)
			$content = file_get_contents($updatesDirFull."/update_info.xml");

		//echo "!1!".htmlspecialchars($content)."!2!";

		if (strlen($strError_tmp) <= 0)
		{
			$arRes = Array();
			CUpdateClientPartner::__ParseServerData($content, $arRes, $strError_tmp);
		}

		if (strlen($strError_tmp) <= 0)
		{
			if (isset($arRes["DATA"]["#"]["ERROR"]) && is_array($arRes["DATA"]["#"]["ERROR"]) && count($arRes["DATA"]["#"]["ERROR"]) > 0)
			{
				for ($i = 0; $i < count($arRes["DATA"]["#"]["ERROR"]); $i++)
				{
					if (strlen($arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]) > 0)
						$strError_tmp .= "[".$arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ";

					$strError_tmp .= $arRes["DATA"]["#"]["ERROR"][$i]["#"].". ";
				}
			}
		}

		if (strlen($strError_tmp) <= 0)
		{
			$handle = @opendir($updatesDirFull);
			if ($handle)
			{
				while (false !== ($dir = readdir($handle)))
				{
					if ($dir == "." || $dir == "..")
						continue;

					if (file_exists($_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/".$dir))
					{
						$strError_tmp1 = "";
						CUpdateClientPartner::__CopyDirFiles($updatesDirFull."/".$dir, $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/".$dir, $strError_tmp1, False);
						if (strlen($strError_tmp1) > 0)
							$strError_tmp .= $strError_tmp1;
					}
				}
				closedir($handle);
			}
		}

		if (strlen($strError_tmp) <= 0)
		{
			CUpdateClientPartner::AddMessage2Log("Modules registered successfully!", "CURV");
			CUpdateClientPartner::__DeleteDirFilesEx($updatesDirFull);
		}

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "CURV");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return True;
	}

	function LoadModuleNoDemand($moduleId, &$strError, $stableVersionsOnly = "Y", $lang = false)
	{
		$strError_tmp = "";

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::LoadModuleNoDemand");

		$stableVersionsOnly = (($stableVersionsOnly == "N") ? "N" : "Y");

		if ($lang === false)
			$lang = LANGUAGE_ID;

		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, array($moduleId), array(), true);
		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
		{
			if (StrLen($strError_tmp) <= 0)
				$strError_tmp = "[GNSU01] ".GetMessage("SUPZ_NO_QSTRING").". ";
		}

		if (StrLen($strError_tmp) <= 0)
		{
			CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

			$stime = CUpdateClientPartner::__GetMicroTime();
			$content = CUpdateClientPartner::__GetHTTPPage("MODULE", $strQuery, $strError_tmp);
			if (strlen($content) <= 0)
			{
				if (StrLen($strError_tmp) <= 0)
					$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
			}

			CUpdateClientPartner::AddMessage2Log("TIME LoadModuleNoDemand(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");
		}

		if (StrLen($strError_tmp) <= 0)
		{
			if (!($fp1 = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", "wb")))
				$strError_tmp = "[GNSU03] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates", GetMessage("SUPP_RV_ER_TEMP_FILE")).". ";
		}

		if (StrLen($strError_tmp) <= 0)
		{
			fwrite($fp1, $content);
			fclose($fp1);
		}

		if (strlen($strError_tmp) <= 0)
		{
			$temporaryUpdatesDir = "";
			if (!CUpdateClientPartner::UnGzipArchive($temporaryUpdatesDir, $strError_tmp, true))
			{
				$strError_tmp .= "[CL02] ".GetMessage("SUPC_ME_PACK").". ";
				CUpdateClientPartner::AddMessage2Log(GetMessage("SUPC_ME_PACK"), "CL02");
			}
		}

		$arStepUpdateInfo = array();
		if (strlen($strError_tmp) <= 0)
			$arStepUpdateInfo = CUpdateClientPartner::GetStepUpdateInfo($temporaryUpdatesDir, $strError_tmp);

		if (StrLen($strError_tmp) <= 0)
		{
			if (isset($arStepUpdateInfo["DATA"]["#"]["ERROR"]))
			{
				for ($i = 0, $cnt = count($arStepUpdateInfo["DATA"]["#"]["ERROR"]); $i < $cnt; $i++)
					$strError_tmp .= "[".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ".$arStepUpdateInfo["DATA"]["#"]["ERROR"][$i]["#"];
			}
		}

		if (strlen($strError_tmp) <= 0)
		{
			if (!CUpdateClientPartner::UpdateStepModules($temporaryUpdatesDir, $strError_tmp))
			{
				$strError_tmp .= "[CL04] ".GetMessage("SUPC_ME_UPDATE").". ";
				CUpdateClientPartner::AddMessage2Log(GetMessage("SUPC_ME_UPDATE"), "CL04");
			}
		}

		if (strlen($strError_tmp) > 0)
		{
			CUpdateSystem::AddMessage2Log($strError_tmp, "CURV");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return True;
	}

	function SearchModulesEx($arOrder, $arFilter, $searchPage, $lang, &$strError)
	{
		$strError_tmp = "";
		$arResult = array();

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::SearchModulesEx");

		$arOrderKeys = array_keys($arOrder);

		$strQuery = CUpdateClientPartner::__CollectRequestData(
			$strError_tmp,
			$lang,
			$stableVersionsOnly,
			array(),
			array(
				"search_module_id" => $arFilter["ID"],
				"search_module" => $arFilter["NAME"],
				"search_category" => $arFilter["CATEGORY"],
				"search_type" => (is_array($arFilter["TYPE"]) ? implode(",", $arFilter["TYPE"]) : $arFilter["TYPE"]),
				"search_order" => $arOrder[$arOrderKeys[0]],
				"search_order_by" => $arOrderKeys[0],
				"search_page" => $searchPage
			)
		);
		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
		{
			$strError .= $strError_tmp;
			CUpdateClientPartner::AddMessage2Log("Empty query list", "GUL01");
			return False;
		}

		CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

		$stime = CUpdateClientPartner::__GetMicroTime();
		$content = CUpdateClientPartner::__GetHTTPPage("SEARCH", $strQuery, $strError_tmp);

		CUpdateClientPartner::AddMessage2Log("TIME SearchModulesEx(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");

		$arResult = Array();
		if (strlen($strError_tmp) <= 0)
			CUpdateClientPartner::__ParseServerData($content, $arResult, $strError_tmp);

		//echo "<pre>";print_r($arResult);echo "</pre>";

		if (strlen($strError_tmp) <= 0)
		{
			if (!isset($arResult["DATA"]) || !is_array($arResult["DATA"]))
				$strError_tmp .= "[UGAUT01] ".GetMessage("SUPP_GAUT_SYSERR").". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			$arResult = $arResult["DATA"]["#"];
			if (!is_array($arResult["CLIENT"]) && (!isset($arResult["ERROR"]) || !is_array($arResult["ERROR"])))
				$strError_tmp .= "[UGAUT01] ".GetMessage("SUPP_GAUT_SYSERR").". ";
		}

		if (isset($arResult["ERROR"]))
		{
			for ($i = 0, $cnt = count($arResult["ERROR"]); $i < $cnt; $i++)
				$strError_tmp .= "[".$arResult["ERROR"][$i]["@"]["TYPE"]."] ".$arResult["ERROR"][$i]["#"];
		}

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "GUL02");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return $arResult;
	}

	function SearchModules($searchModule, $lang)
	{
		$strError_tmp = "";
		$arResult = array();

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::SearchModules");

		$strQuery = CUpdateClientPartner::__CollectRequestData(
			$strError_tmp,
			$lang,
			$stableVersionsOnly,
			array(),
			array("search_module" => $searchModule)
		);
		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
		{
			$strError .= $strError_tmp;
			CUpdateClientPartner::AddMessage2Log("Empty query list", "GUL01");
			return False;
		}

		CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

		$stime = CUpdateClientPartner::__GetMicroTime();
		$content = CUpdateClientPartner::__GetHTTPPage("SEARCH", $strQuery, $strError_tmp);

		CUpdateClientPartner::AddMessage2Log("TIME SearchModules(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");

		$arResult = Array();
		if (strlen($strError_tmp) <= 0)
			CUpdateClientPartner::__ParseServerData($content, $arResult, $strError_tmp);

		//echo "<pre>";print_r($arResult);echo "</pre>";

		if (strlen($strError_tmp) <= 0)
		{
			if (!isset($arResult["DATA"]) || !is_array($arResult["DATA"]))
				$strError_tmp .= "[UGAUT01] ".GetMessage("SUPP_GAUT_SYSERR").". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			$arResult = $arResult["DATA"]["#"];
			if (!is_array($arResult["CLIENT"]) && (!isset($arResult["ERROR"]) || !is_array($arResult["ERROR"])))
				$strError_tmp .= "[UGAUT01] ".GetMessage("SUPP_GAUT_SYSERR").". ";
		}

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "GUL02");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return $arResult;
	}
	
	/** Пишет сообщения в лог файл системы обновлений. Чистит лог, если нужно. **/
	function AddMessage2Log($sText, $sErrorCode = "")
	{
		$MAX_LOG_SIZE = 1000000;
		$READ_PSIZE = 8000;
		$LOG_FILE = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/updater_partner.log";
		$LOG_FILE_TMP = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/updater_partner_tmp1.log";

		if (strlen($sText)>0 || strlen($sErrorCode)>0)
		{
			$old_abort_status = ignore_user_abort(true);

			if (file_exists($LOG_FILE))
			{
				$log_size = @filesize($LOG_FILE);
				$log_size = IntVal($log_size);

				if ($log_size > $MAX_LOG_SIZE)
				{
					if (!($fp = @fopen($LOG_FILE, "rb")))
					{
						ignore_user_abort($old_abort_status);
						return False;
					}

					if (!($fp1 = @fopen($LOG_FILE_TMP, "wb")))
					{
						ignore_user_abort($old_abort_status);
						return False;
					}

					$iSeekLen = IntVal($log_size-$MAX_LOG_SIZE/2.0);
					fseek($fp, $iSeekLen);

					do
					{
						$data = fread($fp, $READ_PSIZE);
						if (strlen($data) == 0)
							break;

						@fwrite($fp1, $data);
					}
					while(true);

					@fclose($fp);
					@fclose($fp1);

					@copy($LOG_FILE_TMP, $LOG_FILE);
					@unlink($LOG_FILE_TMP);
				}
				clearstatcache();
			}

			if ($fp = @fopen($LOG_FILE, "ab+"))
			{
				if (flock($fp, LOCK_EX))
				{
					@fwrite($fp, date("Y-m-d H:i:s")." - ".$sErrorCode." - ".$sText."\n");
					@fflush($fp);
					@flock($fp, LOCK_UN);
					@fclose($fp);
				}
			}
			ignore_user_abort($old_abort_status);
		}
	}

	function GetRequestedModules($strAddModule)
	{
		$arRequestedModules = array();

		$arClientModules = CUpdateClientPartner::GetCurrentModules($strError_tmp);
		if (strlen($strError_tmp) <= 0)
		{
			if (count($arClientModules) > 0)
			{
				foreach ($arClientModules as $key => $value)
				{
					if (strpos($key, ".") !== false)
						$arRequestedModules[] = $key;
				}
			}
		}

		if (strlen($strAddModule) > 0)
		{
			$arAddModule = explode(",", $strAddModule);
			foreach ($arAddModule as $value)
			{
				$value = trim($value);
				if (strlen($value) > 0 && strpos($value, ".") !== false)
					$arRequestedModules[] = $value;
			}
		}

		return $arRequestedModules;
	}

	/** Получение лицензионного ключа текущего клиента **/
	function GetLicenseKey()
	{
		if (defined("US_LICENSE_KEY"))
			return US_LICENSE_KEY;
		if (defined("LICENSE_KEY"))
			return LICENSE_KEY;
		if (!isset($GLOBALS["CACHE4UPDATESYS_LICENSE_KEY"])	|| $GLOBALS["CACHE4UPDATESYS_LICENSE_KEY"]=="")
		{
			$LICENSE_KEY = "demo";
			if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php"))
				include($_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php");
			$GLOBALS["CACHE4UPDATESYS_LICENSE_KEY"] = $LICENSE_KEY;
		}
		return $GLOBALS["CACHE4UPDATESYS_LICENSE_KEY"];
	}

	/* Получить обновления следующего шага */
	function GetNextStepUpdates(&$strError, $lang = false, $stableVersionsOnly = "Y", $arRequestedModules = array(), $bStrongList = false)
	{
		$strError_tmp = "";

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::GetNextStepUpdates");

		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, $arRequestedModules, array(), $bStrongList);
		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
		{
			if (StrLen($strError_tmp) <= 0)
				$strError_tmp = "[GNSU01] ".GetMessage("SUPZ_NO_QSTRING").". ";
		}

		if (StrLen($strError_tmp) <= 0)
		{
			CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

			$stime = CUpdateClientPartner::__GetMicroTime();
			$content = CUpdateClientPartner::__GetHTTPPage("STEPM", $strQuery, $strError_tmp);
			if (strlen($content) <= 0)
			{
				if (StrLen($strError_tmp) <= 0)
					$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
			}

			CUpdateClientPartner::AddMessage2Log("TIME GetNextStepUpdates(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");
		}

		if (StrLen($strError_tmp) <= 0)
		{
			if (!($fp1 = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", "wb")))
				$strError_tmp = "[GNSU03] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates", GetMessage("SUPP_RV_ER_TEMP_FILE")).". ";
		}

		if (StrLen($strError_tmp) <= 0)
		{
			fwrite($fp1, $content);
			fclose($fp1);
		}

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "GNSU00");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return True;
	}

	// Распаковывает архив файлов update_archive.gz в папкy $updatesDir
	function UnGzipArchive(&$updatesDir, &$strError, $bDelArch = true)
	{
		$strError_tmp = "";

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::UnGzipArchive");
		$stime = CUpdateClientPartner::__GetMicroTime();

		$archiveFileName = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz";

		if (!file_exists($archiveFileName) || !is_file($archiveFileName))
			$strError_tmp .= "[UUGZA01] ".str_replace("#FILE#", $archiveFileName, GetMessage("SUPP_UGA_NO_TMP_FILE")).". ";

		if (strlen($strError_tmp) <= 0)
		{
			if (!is_readable($archiveFileName))
				$strError_tmp .= "[UUGZA02] ".str_replace("#FILE#", $archiveFileName, GetMessage("SUPP_UGA_NO_READ_FILE")).". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			$updatesDir = "update_m".time();
			$updatesDirFull = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$updatesDir;
			CUpdateClientPartner::__CheckDirPath($updatesDirFull."/", true);

			if (!file_exists($updatesDirFull) || !is_dir($updatesDirFull))
				$strError_tmp .= "[UUGZA03] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_UGA_NO_TMP_CAT")).". ";
			elseif (!is_writable($updatesDirFull))
				$strError_tmp .= "[UUGZA04] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_UGA_WRT_TMP_CAT")).". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			$bCompressionUsed = True;

			$fd = fopen($archiveFileName, "rb");
			$flabel = fread($fd, strlen("BITRIX"));
			fclose($fd);

			if ($flabel == "BITRIX")
				$bCompressionUsed = False;
		}

		if (strlen($strError_tmp) <= 0)
		{
			if ($bCompressionUsed)
				$zp = gzopen($archiveFileName, "rb9f");
			else
				$zp = fopen($archiveFileName, "rb");

			if (!$zp)
				$strError_tmp .= "[UUGZA05] ".str_replace("#FILE#", $archiveFileName, GetMessage("SUPP_UGA_CANT_OPEN")).". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			if ($bCompressionUsed)
				$flabel = gzread($zp, strlen("BITRIX"));
			else
				$flabel = fread($zp, strlen("BITRIX"));

			if ($flabel != "BITRIX")
			{
				$strError_tmp .= "[UUGZA06] ".str_replace("#FILE#", $archiveFileName, GetMessage("SUPP_UGA_BAD_FORMAT")).". ";

				if ($bCompressionUsed)
					gzclose($zp);
				else
					fclose($zp);
			}
		}

		if (strlen($strError_tmp) <= 0)
		{
			$strongUpdateCheck = COption::GetOptionString("main", "strong_update_check", "Y");

			while (true)
			{
				if ($bCompressionUsed)
					$add_info_size = gzread($zp, 5);
				else
					$add_info_size = fread($zp, 5);

				$add_info_size = Trim($add_info_size);
				if (IntVal($add_info_size) > 0 && IntVal($add_info_size)."!"==$add_info_size."!")
				{
					$add_info_size = IntVal($add_info_size);
				}
				else
				{
					if ($add_info_size != "RTIBE")
						$strError_tmp .= "[UUGZA071] ".str_replace("#FILE#", $archiveFileName, GetMessage("SUPP_UGA_BAD_FORMAT")).". ";

					break;
				}

				if ($bCompressionUsed)
					$add_info = gzread($zp, $add_info_size);
				else
					$add_info = fread($zp, $add_info_size);

				$add_info_arr = explode("|", $add_info);
				if (count($add_info_arr) != 3)
				{
					$strError_tmp .= "[UUGZA072] ".str_replace("#FILE#", $archiveFileName, GetMessage("SUPP_UGA_BAD_FORMAT")).". ";
					break;
				}

				$size = $add_info_arr[0];
				$curpath = $add_info_arr[1];
				$crc32 = $add_info_arr[2];

				$contents = "";
				if (IntVal($size) > 0)
				{
					if ($bCompressionUsed)
						$contents = gzread($zp, $size);
					else
						$contents = fread($zp, $size);
				}

				$crc32_new = dechex(crc32($contents));

				if ($crc32_new != $crc32)
				{
					$strError_tmp .= "[UUGZA073] ".str_replace("#FILE#", $curpath, GetMessage("SUPP_UGA_FILE_CRUSH")).". ";
					break;
				}
				else
				{
					CUpdateClientPartner::__CheckDirPath($updatesDirFull.$curpath, true);

					if (!($fp1 = fopen($updatesDirFull.$curpath, "wb")))
					{
						$strError_tmp .= "[UUGZA074] ".str_replace("#FILE#", $updatesDirFull.$curpath, GetMessage("SUPP_UGA_CANT_OPEN_WR")).". ";
						break;
					}

					if (strlen($contents) > 0 && !fwrite($fp1, $contents))
					{
						$strError_tmp .= "[UUGZA075] ".str_replace("#FILE#", $updatesDirFull.$curpath, GetMessage("SUPP_UGA_CANT_WRITE_F")).". ";
						@fclose($fp1);
						break;
					}
					fclose($fp1);

					if ($strongUpdateCheck == "Y")
					{
						$crc32_new = dechex(crc32(file_get_contents($updatesDirFull.$curpath)));
						if ($crc32_new != $crc32)
						{
							$strError_tmp .= "[UUGZA0761] ".str_replace("#FILE#", $curpath, GetMessage("SUPP_UGA_FILE_CRUSH")).". ";
							break;
						}
					}
				}
			}

			if ($bCompressionUsed)
				gzclose($zp);
			else
				fclose($zp);
		}

		if (strlen($strError_tmp) <= 0)
		{
			if ($bDelArch)
				@unlink($archiveFileName);
		}

		CUpdateClientPartner::AddMessage2Log("TIME UnGzipArchive ".Round(CUpdateClientPartner::__GetMicroTime()-$stime, 3)." sec");

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "CUUGZA");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return True;
	}

	// Возвращает информацию по загруженным в папку $updatesDir обновлениям модулей
	function CheckUpdatability($updatesDir, &$strError)
	{
		$strError_tmp = "";

		$updatesDirFull = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$updatesDir;
		if (!file_exists($updatesDirFull) || !is_dir($updatesDirFull))
			$strError_tmp .= "[UCU01] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_CU_NO_TMP_CAT")).". ";

		if (strlen($strError_tmp) <= 0)
			if (!is_readable($updatesDirFull))
				$strError_tmp .= "[UCU02] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_CU_RD_TMP_CAT")).". ";

		if ($handle = @opendir($updatesDirFull))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				if (is_dir($updatesDirFull."/".$file))
				{
					CUpdateClientPartner::CheckUpdatability($updatesDir."/".$file, $strError_tmp);
				}
				elseif (is_file($updatesDirFull."/".$file))
				{
					$strRealPath = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/".substr($updatesDir."/".$file, strpos($updatesDir."/".$file, "/"));
					if (file_exists($strRealPath))
					{
						if (!is_writeable($strRealPath))
							$strError_tmp .= "[UCU03] ".str_replace("#FILE#", $strRealPath, GetMessage("SUPP_CU_MAIN_ERR_FILE")).". ";
					}
					else
					{
						$p = CUpdateClientPartner::__bxstrrpos($strRealPath, "/");
						$strRealPath = substr($strRealPath, 0, $p);

						if (strlen($strRealPath) > 1)
							$strRealPath = rtrim($strRealPath, "/");

						$p = CUpdateClientPartner::__bxstrrpos($strRealPath, "/");
						while ($p > 0)
						{
							if (file_exists($strRealPath) && is_dir($strRealPath))
							{
								if (!is_writable($strRealPath))
									$strError_tmp .= "[UCU04] ".str_replace("#FILE#", $strRealPath, GetMessage("SUPP_CU_MAIN_ERR_CAT")).". ";

								break;
							}
							$strRealPath = substr($strRealPath, 0, $p);
							$p = CUpdateClientPartner::__bxstrrpos($strRealPath, "/");
						}
					}
				}
			}
			@closedir($handle);
		}

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "CUCU");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return True;
	}

	// Возвращает информацию по загруженным в папку $updatesDir обновлениям модулей
	function GetStepUpdateInfo($updatesDir, &$strError)
	{
		$arResult = array();
		$strError_tmp = "";

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::GetStepUpdateInfo");

		$updatesDirFull = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$updatesDir;
		if (!file_exists($updatesDirFull) || !is_dir($updatesDirFull))
			$strError_tmp .= "[UGLMU01] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_CU_NO_TMP_CAT")).". ";

		if (strlen($strError_tmp) <= 0)
			if (!is_readable($updatesDirFull))
				$strError_tmp .= "[UGLMU02] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_CU_RD_TMP_CAT")).". ";

		if (strlen($strError_tmp) <= 0)
			if (!file_exists($updatesDirFull."/update_info.xml") || !is_file($updatesDirFull."/update_info.xml"))
				$strError_tmp .= "[UGLMU03] ".str_replace("#FILE#", $updatesDirFull."/update_info.xml", GetMessage("SUPP_RV_ER_DESCR_FILE")).". ";

		if (strlen($strError_tmp) <= 0)
			if (!is_readable($updatesDirFull."/update_info.xml"))
				$strError_tmp .= "[UGLMU04] ".str_replace("#FILE#", $updatesDirFull."/update_info.xml", GetMessage("SUPP_RV_READ_DESCR_FILE")).". ";

		if (strlen($strError_tmp) <= 0)
			$content = file_get_contents($updatesDirFull."/update_info.xml");

		//echo "!1!".htmlspecialchars($content)."!2!";

		if (strlen($strError_tmp) <= 0)
		{
			$arResult = Array();
			CUpdateClientPartner::__ParseServerData($content, $arResult, $strError_tmp);
		}

		//echo "!3!".htmlspecialchars($content)."!4!";
		//echo "<pre>";print_r($arRes);echo "</pre>";

		if (strlen($strError_tmp) <= 0)
		{
			if (!isset($arResult["DATA"]) || !is_array($arResult["DATA"]))
				$strError_tmp .= "[UGSMU01] ".GetMessage("SUPP_GAUT_SYSERR").". ";
		}

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "CUGLMU");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return $arResult;
	}

	function __CollectRequestData(&$strError, $lang = false, $stableVersionsOnly = "Y", $arRequestedModules = array(), $arAdditionalData = array(), $bStrongList = false)
	{
		$strResult = "";
		$strError_tmp = "";

		if ($lang === false)
			$lang = LANGUAGE_ID;

		$stableVersionsOnly = (($stableVersionsOnly == "N") ? "N" : "Y");

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::__CollectRequestData");

		CUpdateClientPartner::__CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/", true);

		$arClientModules = CUpdateClientPartner::GetCurrentModules($strError_tmp);

		if (strlen($strError_tmp) <= 0)
		{
			$dbv = $GLOBALS["DB"]->GetVersion();

			$strResult = "utf=".urlencode(defined('BX_UTF') ? "Y" : "N").
				"&lang=".urlencode($lang).
				"&stable=".urlencode($stableVersionsOnly).
				"&CANGZIP=".urlencode((CUpdateClientPartner::__IsGzipInstalled()) ? "Y" : "N").
				"&SUPD_DBS=".urlencode($GLOBALS["DB"]->type).
				"&XE=".urlencode(($GLOBALS["DB"]->XE) ? "Y" : "N").
				"&CLIENT_SITE=".urlencode($_SERVER["SERVER_NAME"]).
				"&LICENSE_KEY=".urlencode(md5(CUpdateClientPartner::GetLicenseKey())).
				"&SUPD_STS=".urlencode(CUpdateClientPartner::__GetFooPath()).
				"&SUPD_URS=".urlencode(CUpdateClientPartner::__GetFooPath1(0)).
				"&SUPD_URSA=".urlencode(CUpdateClientPartner::__GetFooPath1(1)).
				"&TYPENC=".((defined("DEMO") && DEMO=="Y") ? "D" : ((defined("ENCODE") && ENCODE=="Y") ? "E" : "F" )).
				"&CLIENT_PHPVER=".urlencode(phpversion()).
				"&dbv=".urlencode($dbv != false ? $dbv : "");

			$strResultTmp = "";
			if (count($arClientModules) > 0)
			{
				foreach ($arClientModules as $key => $value)
				{
					if (StrLen($strResultTmp) > 0)
						$strResultTmp .= ";";
					$strResultTmp .= $key.",".$value["VERSION"].",".$value["IS_DEMO"];
				}
			}
			if (StrLen($strResultTmp) > 0)
				$strResult .= "&instm=".urlencode($strResultTmp);

//			foreach ($arClientModules as $key => $value)
//				$strResult .= "&m_".$key."=".urlencode($value);

			$strResultTmp = "";
			if (count($arRequestedModules) > 0)
			{
				for ($i = 0, $cnt = count($arRequestedModules); $i < $cnt; $i++)
				{
					if (StrLen($strResultTmp) > 0)
						$strResultTmp .= ",";
					$strResultTmp .= $arRequestedModules[$i];
				}
			}
			if (StrLen($strResultTmp) > 0)
				$strResult .= "&reqm=".urlencode($strResultTmp);
			
			if ($bStrongList)
				$strResult .= "&lim=Y";

			$strResultTmp = "";
			if (count($arAdditionalData) > 0)
			{
				foreach ($arAdditionalData as $key => $value)
				{
					if (StrLen($strResultTmp) > 0)
						$strResultTmp .= "&";
					$strResultTmp .= $key."=".urlencode($value);
				}
			}
			if (StrLen($strResultTmp) > 0)
				$strResult .= "&".$strResultTmp;

			return $strResult;
		}

		CUpdateClientPartner::AddMessage2Log($strError_tmp, "NCRD01");
		$strError .= $strError_tmp;
		return False;
	}

	/** Собирает клиентские модули с версиями **/
	function GetCurrentModules(&$strError)
	{
		$arClientModules = array();

		if (file_exists($_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/classes/general/version.php")
			&& is_file($_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/classes/general/version.php"))
		{
			$p = file_get_contents($_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/classes/general/version.php");

			preg_match("/define\s*\(\s*\"SM_VERSION\"\s*,\s*\"(\d+\.\d+\.\d+)\"\s*\)\s*/im", $p, $arVers);
			$arClientModules["main"] = array("VERSION" => $arVers[1], "IS_DEMO" => ((defined("DEMO") && DEMO == "Y") ? "Y" : "N"));
		}

		if (!array_key_exists("main", $arClientModules) || strlen($arClientModules["main"]["VERSION"]) <= 0)
		{
			CUpdateClientPartner::AddMessage2Log(GetMessage("SUPP_GM_ERR_DMAIN"), "Ux09");
			$strError .= "[Ux09] ".GetMessage("SUPP_GM_ERR_DMAIN").". ";
			return array();
		}

		if ($handle = @opendir($_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules"))
		{
			while (false !== ($dir = readdir($handle)))
			{
				if (is_dir($_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/".$dir)
					&& $dir != "." && $dir != ".." && $dir != "main")
				{
					$module_dir = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/".$dir;
					if (file_exists($module_dir."/install/index.php"))
					{
						$arInfo = CUpdateClientPartner::__GetModuleInfo($module_dir);
						if (!isset($arInfo["VERSION"]) || strlen($arInfo["VERSION"]) <= 0)
						{
							CUpdateClientPartner::AddMessage2Log(str_replace("#MODULE#", $dir, GetMessage("SUPP_GM_ERR_DMOD")), "Ux11");
							$strError .= "[Ux11] ".str_replace("#MODULE#", $dir, GetMessage("SUPP_GM_ERR_DMOD")).". ";

							return array();
						}
						else
						{
							$arClientModules[$dir] = array("VERSION" => $arInfo["VERSION"], "IS_DEMO" => $arInfo["IS_DEMO"]);
						}
					}
					else
					{
						CUpdateClientPartner::AddMessage2Log(str_replace("#MODULE#", $dir, GetMessage("SUPP_GM_ERR_DMOD")), "Ux12");
					}
				}
			}
			closedir($handle);
		}
		else
		{
			CUpdateClientPartner::AddMessage2Log(GetMessage("SUPP_GM_NO_KERNEL"), "Ux15");
			$strError .= "[Ux15] ".GetMessage("SUPP_GM_NO_KERNEL").". ";
			
			return array();
		}

		return $arClientModules;
	}

	/* Получить список доступных обновлений */
	function GetUpdatesList(&$strError, $lang = false, $stableVersionsOnly = "Y", $arRequestedModules = array())
	{
		$strError_tmp = "";
		$arResult = array();

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::GetUpdatesList");

		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, $arRequestedModules);
		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
		{
			$strError .= $strError_tmp;
			CUpdateClientPartner::AddMessage2Log("Empty query list", "GUL01");
			return False;
		}

		CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

		$stime = CUpdateClientPartner::__GetMicroTime();
		$content = CUpdateClientPartner::__GetHTTPPage("LIST", $strQuery, $strError_tmp);

		CUpdateClientPartner::AddMessage2Log("TIME GetUpdatesList(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");

		$arResult = Array();
		if (strlen($strError_tmp) <= 0)
			CUpdateClientPartner::__ParseServerData($content, $arResult, $strError_tmp);

		//echo "<pre>";print_r($arResult);echo "</pre>";

		if (strlen($strError_tmp) <= 0)
		{
			if (!isset($arResult["DATA"]) || !is_array($arResult["DATA"]))
				$strError_tmp .= "[UGAUT01] ".GetMessage("SUPP_GAUT_SYSERR").". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			$arResult = $arResult["DATA"]["#"];
			if (!is_array($arResult["CLIENT"]) && (!isset($arResult["ERROR"]) || !is_array($arResult["ERROR"])))
				$strError_tmp .= "[UGAUT01] ".GetMessage("SUPP_GAUT_SYSERR").". ";
		}

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "GUL02");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return $arResult;
	}

	function ClearUpdateFolder($updatesDirFull)
	{
		CUpdateClientPartner::__DeleteDirFilesEx($updatesDirFull);
		bx_accelerator_reset();
	}

	function UpdateStepModules($updatesDir, &$strError, $bSaveUpdaters = False)
	{
		global $DB;
		$strError_tmp = "";

		if (!defined("US_SAVE_UPDATERS_DIR") || StrLen(US_SAVE_UPDATERS_DIR) <= 0)
			$bSaveUpdaters = False;

		$stime = CUpdateClientPartner::__GetMicroTime();

		$updatesDirFull = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$updatesDir;

		if (!file_exists($updatesDirFull) || !is_dir($updatesDirFull))
			$strError_tmp .= "[UUK01] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_CU_NO_TMP_CAT")).". ";

		if (strlen($strError_tmp) <= 0)
			if (!is_readable($updatesDirFull))
				$strError_tmp .= "[UUK03] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_CU_RD_TMP_CAT")).". ";

		$arModules = array();
		if (StrLen($strError_tmp) <= 0)
		{
			$handle = @opendir($updatesDirFull);
			if ($handle)
			{
				while (false !== ($dir = readdir($handle)))
				{
					if ($dir == "." || $dir == "..")
						continue;
					if (is_dir($updatesDirFull."/".$dir))
						$arModules[] = $dir;
				}
				closedir($handle);
			}
		}

		if (!is_array($arModules) || count($arModules) <= 0)
			$strError_tmp .= "[UUK02] ".GetMessage("SUPP_UK_NO_MODS").". ";

		if (strlen($strError_tmp) <= 0)
		{
			for ($i = 0, $cnt = count($arModules); $i < $cnt; $i++)
			{
				$strError_tmp1 = "";

				$updateDirFrom = $updatesDirFull."/".$arModules[$i];
				$updateDirTo = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/".$arModules[$i];

				CUpdateClientPartner::__CheckDirPath($updateDirTo."/", true);

				if (!file_exists($updateDirTo) || !is_dir($updateDirTo))
					$strError_tmp1 .= "[UUK04] ".str_replace("#MODULE_DIR#", $updateDirTo, GetMessage("SUPP_UK_NO_MODIR")).". ";

				if (strlen($strError_tmp1) <= 0)
					if (!is_writable($updateDirTo))
						$strError_tmp1 .= "[UUK05] ".str_replace("#MODULE_DIR#", $updateDirTo, GetMessage("SUPP_UK_WR_MODIR")).". ";

				if (strlen($strError_tmp1) <= 0)
					if (!file_exists($updateDirFrom) || !is_dir($updateDirFrom))
						$strError_tmp1 .= "[UUK06] ".str_replace("#DIR#", $updateDirFrom, GetMessage("SUPP_UK_NO_FDIR")).". ";

				if (strlen($strError_tmp1) <= 0)
					if (!is_readable($updateDirFrom))
						$strError_tmp1 .= "[UUK07] ".str_replace("#DIR#", $updateDirFrom, GetMessage("SUPP_UK_READ_FDIR")).". ";

				if (strlen($strError_tmp1) <= 0)
				{
					$handle = @opendir($updateDirFrom);
					$arUpdaters = array();
					if ($handle)
					{
						while (false !== ($dir = readdir($handle)))
						{
							if (substr($dir, 0, 7) == "updater")
							{
								$bPostUpdater = "N";
								if (is_file($updateDirFrom."/".$dir))
								{
									$num = substr($dir, 7, strlen($dir) - 11);
									if (substr($dir, strlen($dir) - 9) == "_post.php")
									{
										$bPostUpdater = "Y";
										$num = substr($dir, 7, strlen($dir) - 16);
									}
									$arUpdaters[] = array("/".$dir, Trim($num), $bPostUpdater);
								}
								elseif (file_exists($updateDirFrom."/".$dir."/index.php"))
								{
									$num = substr($dir, 7);
									if (substr($dir, strlen($dir) - 5) == "_post")
									{
										$bPostUpdater = "Y";
										$num = substr($dir, 7, strlen($dir) - 12);
									}
									$arUpdaters[] = array("/".$dir."/index.php", Trim($num), $bPostUpdater);
								}

								if ($bSaveUpdaters)
									CUpdateClientPartner::__CopyDirFiles($updateDirFrom."/".$dir, $_SERVER["DOCUMENT_ROOT"].US_SAVE_UPDATERS_DIR."/".$arModules[$i]."/".$dir, $strError_tmp1, False);
							}
						}
						closedir($handle);
					}

					for ($i1 = 0; $i1 < count($arUpdaters) - 1; $i1++)
					{
						for ($j1 = $i1 + 1; $j1 < count($arUpdaters); $j1++)
						{
							if (CUpdateClientPartner::__CompareVersions($arUpdaters[$i1][1], $arUpdaters[$j1][1]) > 0)
							{
								$tmp1 = $arUpdaters[$i1];
								$arUpdaters[$i1] = $arUpdaters[$j1];
								$arUpdaters[$j1] = $tmp1;
							}
						}
					}
				}

				if (strlen($strError_tmp1) <= 0)
				{
					if (strtolower($DB->type) == "mysql" && defined("MYSQL_TABLE_TYPE") && strlen(MYSQL_TABLE_TYPE) > 0)
					{
						$DB->Query("SET table_type = '".MYSQL_TABLE_TYPE."'", True);
					}
				}

				if (strlen($strError_tmp1) <= 0)
				{
					for ($i1 = 0; $i1 < count($arUpdaters); $i1++)
					{
						if ($arUpdaters[$i1][2] == "N")
						{
							$strError_tmp2 = "";
							CUpdateClientPartner::__RunUpdaterScript($updateDirFrom.$arUpdaters[$i1][0], $strError_tmp2, "/bitrix/updates/".$updatesDir."/".$arModules[$i], $arModules[$i]);
							if (strlen($strError_tmp2) > 0)
							{
								$strError_tmp1 .= 
										str_replace("#MODULE#", $arModules[$i], str_replace("#VER#", $arUpdaters[$i1][1], GetMessage("SUPP_UK_UPDN_ERR"))).": ".
										$strError_tmp2.". ";
								$strError_tmp1 .= str_replace("#MODULE#", $arModules[$i], GetMessage("SUPP_UK_UPDN_ERR_BREAK"))." ";
								break;
							}
						}
					}
				}

				if (strlen($strError_tmp1) <= 0)
					CUpdateClientPartner::__CopyDirFiles($updateDirFrom, $updateDirTo, $strError_tmp1, True);

				if (strlen($strError_tmp1) <= 0)
				{
					for ($i1 = 0; $i1 < count($arUpdaters); $i1++)
					{
						if ($arUpdaters[$i1][2]=="Y")
						{
							$strError_tmp2 = "";
							CUpdateClientPartner::__RunUpdaterScript($updateDirFrom.$arUpdaters[$i1][0], $strError_tmp2, "/bitrix/updates/".$updatesDir."/".$arModules[$i], $arModules[$i]);
							if (strlen($strError_tmp2) > 0)
							{
								$strError_tmp1 .= 
										str_replace("#MODULE#", $arModules[$i], str_replace("#VER#", $arUpdaters[$i1][1], GetMessage("SUPP_UK_UPDY_ERR"))).": ".
										$strError_tmp2.". ";
								$strError_tmp1 .= str_replace("#MODULE#", $arModules[$i], GetMessage("SUPP_UK_UPDN_ERR_BREAK"))." ";
								break;
							}
						}
					}
				}

				if (strlen($strError_tmp1) > 0)
					$strError_tmp .= $strError_tmp1;
			}
			CUpdateClientPartner::ClearUpdateFolder($updatesDirFull);
		}

		CUpdateClientPartner::AddMessage2Log("TIME UpdateStepModules ".Round(CUpdateClientPartner::__GetMicroTime()-$stime, 3)." sec");

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "USM");
			$strError .= $strError_tmp;
			return False;
		}
		else
		{
			$events = GetModuleEvents("main", "OnModuleUpdate");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEvent($arEvent, $arModules);

			return True;
		}
	}

	
	
	
	
	
	
	
	

	
//	function SubscribeMail($email, &$strError, $lang = false, $stableVersionsOnly = "Y")
//	{
//		$strError_tmp = "";

//		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::SubscribeMail");

//		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, array(), array(), array());
//		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
//		{
//			if (StrLen($strError_tmp) <= 0)
//				$strError_tmp = "[RV01] ".GetMessage("SUPZ_NO_QSTRING").". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			$strQuery .= "&email=".UrlEncode($email)."&query_type=mail";
//			CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

//			/*
//			foreach ($arFields as $key => $value)
//				$strQuery .= "&".$key."=".urlencode($value);
//			*/

//			$stime = CUpdateClientPartner::__GetMicroTime();
//			$content = CUpdateClientPartner::GetHTTPPage("ACTIV", $strQuery, $strError_tmp);
//			if (strlen($content) <= 0)
//			{
//				if (StrLen($strError_tmp) <= 0)
//					$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
//			}

//			CUpdateClientPartner::AddMessage2Log("TIME SubscribeMail(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$arRes = Array();
//			CUpdateClientPartner::ParseServerData($content, $arRes, $strError_tmp);
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (isset($arRes["DATA"]["#"]["ERROR"]) && is_array($arRes["DATA"]["#"]["ERROR"]) && count($arRes["DATA"]["#"]["ERROR"]) > 0)
//			{
//				for ($i = 0; $i < count($arRes["DATA"]["#"]["ERROR"]); $i++)
//				{
//					if (strlen($arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]) > 0)
//						$strError_tmp .= "[".$arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ";

//					$strError_tmp .= $arRes["DATA"]["#"]["ERROR"][$i]["#"].". ";
//				}
//			}
//		}

//		if (strlen($strError_tmp) > 0)
//		{
//			CUpdateClientPartner::AddMessage2Log($strError_tmp, "SM");
//			$strError .= $strError_tmp;
//			return False;
//		}
//		else
//			return True;
//	}

//	function ActivateCoupon($coupon, &$strError, $lang = false, $stableVersionsOnly = "Y")
//	{
//		$strError_tmp = "";

//		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::ActivateCoupon");

//		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, array(), array(), array());
//		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
//		{
//			if (StrLen($strError_tmp) <= 0)
//				$strError_tmp = "[RV01] ".GetMessage("SUPZ_NO_QSTRING").". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			$strQuery .= "&coupon=".UrlEncode($coupon)."&query_type=coupon";
//			CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

//			/*
//			foreach ($arFields as $key => $value)
//				$strQuery .= "&".$key."=".urlencode($value);
//			*/

//			$stime = CUpdateClientPartner::__GetMicroTime();
//			$content = CUpdateClientPartner::GetHTTPPage("ACTIV", $strQuery, $strError_tmp);
//			if (strlen($content) <= 0)
//			{
//				if (StrLen($strError_tmp) <= 0)
//					$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
//			}

//			CUpdateClientPartner::AddMessage2Log("TIME ActivateCoupon(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$arRes = Array();
//			CUpdateClientPartner::ParseServerData($content, $arRes, $strError_tmp);
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (isset($arRes["DATA"]["#"]["ERROR"]) && is_array($arRes["DATA"]["#"]["ERROR"]) && count($arRes["DATA"]["#"]["ERROR"]) > 0)
//			{
//				for ($i = 0; $i < count($arRes["DATA"]["#"]["ERROR"]); $i++)
//				{
//					if (strlen($arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]) > 0)
//						$strError_tmp .= "[".$arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ";

//					$strError_tmp .= $arRes["DATA"]["#"]["ERROR"][$i]["#"].". ";
//				}
//			}
//		}

//		if (strlen($strError_tmp) > 0)
//		{
//			CUpdateClientPartner::AddMessage2Log($strError_tmp, "AC");
//			$strError .= $strError_tmp;
//			return False;
//		}
//		else
//			return True;
//	}

//	function UpdateUpdate(&$strError, $lang = false, $stableVersionsOnly = "Y")
//	{
//		$strError_tmp = "";

//		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::UpdateUpdate");

//		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, array(), array(), array());
//		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
//		{
//			if (StrLen($strError_tmp) <= 0)
//				$strError_tmp = "[RV01] ".GetMessage("SUPZ_NO_QSTRING").". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			$strQuery .= "&query_type=updateupdate";
//			CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

//			/*
//			foreach ($arFields as $key => $value)
//				$strQuery .= "&".$key."=".urlencode($value);
//			*/

//			$stime = CUpdateClientPartner::__GetMicroTime();
//			$content = CUpdateClientPartner::GetHTTPPage("REG", $strQuery, $strError_tmp);
//			if (strlen($content) <= 0)
//			{
//				if (StrLen($strError_tmp) <= 0)
//					$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
//			}

//			CUpdateClientPartner::AddMessage2Log("TIME UpdateUpdate(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (!($fp1 = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", "wb")))
//				$strError_tmp .= "[URV02] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates", GetMessage("SUPP_RV_ER_TEMP_FILE")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (!fwrite($fp1, $content))
//				$strError_tmp .= "[URV03] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", GetMessage("SUPP_RV_WRT_TEMP_FILE")).". ";

//			@fclose($fp1);
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$updatesDirTmp = "";
//			if (!CUpdateClientPartner::UnGzipArchive($updatesDirTmp, $strError_tmp, "Y"))
//				$strError_tmp .= "[URV04] ".GetMessage("SUPP_RV_BREAK").". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$updatesDirFull = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$updatesDirTmp;
//			if (!file_exists($updatesDirFull."/update_info.xml") || !is_file($updatesDirFull."/update_info.xml"))
//				$strError_tmp .= "[URV05] ".str_replace("#FILE#", $updatesDirFull."/update_info.xml", GetMessage("SUPP_RV_ER_DESCR_FILE")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (!is_readable($updatesDirFull."/update_info.xml"))
//				$strError_tmp .= "[URV06] ".str_replace("#FILE#", $updatesDirFull."/update_info.xml", GetMessage("SUPP_RV_READ_DESCR_FILE")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//			$content = file_get_contents($updatesDirFull."/update_info.xml");

		//echo "!1!".htmlspecialchars($content)."!2!";

//		if (strlen($strError_tmp) <= 0)
//		{
//			$arRes = Array();
//			CUpdateClientPartner::ParseServerData($content, $arRes, $strError_tmp);
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (isset($arRes["DATA"]["#"]["ERROR"]) && is_array($arRes["DATA"]["#"]["ERROR"]) && count($arRes["DATA"]["#"]["ERROR"]) > 0)
//			{
//				for ($i = 0; $i < count($arRes["DATA"]["#"]["ERROR"]); $i++)
//				{
//					if (strlen($arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]) > 0)
//						$strError_tmp .= "[".$arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ";

//					$strError_tmp .= $arRes["DATA"]["#"]["ERROR"][$i]["#"].". ";
//				}
//			}
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$updateDirTo = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main";
//			CUpdateClientPartner::__CheckDirPath($updateDirTo."/", true);

//			if (!file_exists($updateDirTo) || !is_dir($updateDirTo))
//				$strError_tmp .= "[UUK04] ".str_replace("#MODULE_DIR#", $updateDirTo, GetMessage("SUPP_UK_NO_MODIR")).". ";

//			if (strlen($strError_tmp) <= 0)
//				if (!is_writable($updateDirTo))
//					$strError_tmp .= "[UUK05] ".str_replace("#MODULE_DIR#", $updateDirTo, GetMessage("SUPP_UK_WR_MODIR")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			CUpdateClientPartner::__CopyDirFiles($updatesDirFull."/main", $updateDirTo, $strError_tmp);
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			CUpdateClientPartner::AddMessage2Log("Update updated successfully!", "CURV");
//			CUpdateClientPartner::__DeleteDirFilesEx($updatesDirFull);
//		}

//		if (strlen($strError_tmp) > 0)
//		{
//			CUpdateClientPartner::AddMessage2Log($strError_tmp, "UU");
//			$strError .= $strError_tmp;
//			return False;
//		}
//		else
//			return True;
//	}

//	function GetPHPSources($errorMessage, $lang, $stableVersionsOnly, $arRequestedModules)
//	{
//		$strError_tmp = "";

//		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::GetNextStepUpdates");

//		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, $arRequestedModules, array(), array());
//		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
//		{
//			if (StrLen($strError_tmp) <= 0)
//				$strError_tmp = "[GNSU01] ".GetMessage("SUPZ_NO_QSTRING").". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

//			$stime = CUpdateClientPartner::__GetMicroTime();
//			$content = CUpdateClientPartner::GetHTTPPage("SRC", $strQuery, $strError_tmp);
//			if (strlen($content) <= 0)
//			{
//				if (StrLen($strError_tmp) <= 0)
//					$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
//			}

//			CUpdateClientPartner::AddMessage2Log("TIME GetNextStepUpdates(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			if (!($fp1 = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", "wb")))
//				$strError_tmp = "[GNSU03] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates", GetMessage("SUPP_RV_ER_TEMP_FILE")).". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			fwrite($fp1, $content);
//			fclose($fp1);
//		}

//		if (strlen($strError_tmp) > 0)
//		{
//			CUpdateClientPartner::AddMessage2Log($strError_tmp, "GNSU00");
//			$strError .= $strError_tmp;
//			return False;
//		}
//		else
//			return True;
//	}


//	function RegisterVersion(&$strError, $lang = false, $stableVersionsOnly = "Y")
//	{
//		$strError_tmp = "";

//		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::RegisterVersion");

//		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, array(), array(), array());
//		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
//		{
//			if (StrLen($strError_tmp) <= 0)
//				$strError_tmp = "[RV01] ".GetMessage("SUPZ_NO_QSTRING").". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			$strQuery .= "&query_type=register";
//			CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

//			/*
//			foreach ($arFields as $key => $value)
//				$strQuery .= "&".$key."=".urlencode($value);
//			*/

//			$stime = CUpdateClientPartner::__GetMicroTime();
//			$content = CUpdateClientPartner::GetHTTPPage("REG", $strQuery, $strError_tmp);
//			if (strlen($content) <= 0)
//			{
//				if (StrLen($strError_tmp) <= 0)
//					$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
//			}

//			CUpdateClientPartner::AddMessage2Log("TIME RegisterVersion(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (!($fp1 = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", "wb")))
//				$strError_tmp .= "[URV02] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates", GetMessage("SUPP_RV_ER_TEMP_FILE")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (!fwrite($fp1, $content))
//				$strError_tmp .= "[URV03] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", GetMessage("SUPP_RV_WRT_TEMP_FILE")).". ";

//			@fclose($fp1);
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$updatesDirTmp = "";
//			if (!CUpdateClientPartner::UnGzipArchive($updatesDirTmp, $strError_tmp, "Y"))
//				$strError_tmp .= "[URV04] ".GetMessage("SUPP_RV_BREAK").". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$updatesDirFull = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$updatesDirTmp;
//			if (!file_exists($updatesDirFull."/update_info.xml") || !is_file($updatesDirFull."/update_info.xml"))
//				$strError_tmp .= "[URV05] ".str_replace("#FILE#", $updatesDirFull."/update_info.xml", GetMessage("SUPP_RV_ER_DESCR_FILE")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (!is_readable($updatesDirFull."/update_info.xml"))
//				$strError_tmp .= "[URV06] ".str_replace("#FILE#", $updatesDirFull."/update_info.xml", GetMessage("SUPP_RV_READ_DESCR_FILE")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//			$content = file_get_contents($updatesDirFull."/update_info.xml");

		//echo "!1!".htmlspecialchars($content)."!2!";

//		if (strlen($strError_tmp) <= 0)
//		{
//			$arRes = Array();
//			CUpdateClientPartner::ParseServerData($content, $arRes, $strError_tmp);
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (isset($arRes["DATA"]["#"]["ERROR"]) && is_array($arRes["DATA"]["#"]["ERROR"]) && count($arRes["DATA"]["#"]["ERROR"]) > 0)
//			{
//				for ($i = 0; $i < count($arRes["DATA"]["#"]["ERROR"]); $i++)
//				{
//					if (strlen($arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]) > 0)
//						$strError_tmp .= "[".$arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ";

//					$strError_tmp .= $arRes["DATA"]["#"]["ERROR"][$i]["#"].". ";
//				}
//			}
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (!file_exists($updatesDirFull."/include.php") || !is_file($updatesDirFull."/include.php"))
//				$strError_tmp .= "[URV07] ".GetMessage("SUPP_RV_NO_FILE").". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$newfilesize = @filesize($updatesDirFull."/include.php");
//			if (IntVal($newfilesize) != IntVal($arRes["DATA"]["#"]["FILE"][0]["@"]["SIZE"]))
//				$strError_tmp .= "[URV08] ".GetMessage("SUPP_RV_ER_SIZE").". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (!is_writeable($_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/include.php"))
//				$strError_tmp .= "[URV09] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/include.php", GetMessage("SUPP_RV_NO_WRITE")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (!copy($updatesDirFull."/include.php", $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/include.php"))
//				$strError_tmp .= "[URV10] ".GetMessage("SUPP_RV_ERR_COPY").". ";
//			@chmod($_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/include.php", BX_FILE_PERMISSIONS);
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$strongUpdateCheck = COption::GetOptionString("main", "strong_update_check", "Y");

//			if ($strongUpdateCheck == "Y")
//			{
//				$crc32_old = dechex(crc32(file_get_contents($updatesDirFull."/include.php")));
//				$crc32_new = dechex(crc32(file_get_contents($_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/include.php")));
//				if ($crc32_new != $crc32_old)
//					$strError_tmp .= "[URV1011] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/include.php", GetMessage("SUPP_UGA_FILE_CRUSH")).". ";
//			}
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			CUpdateClientPartner::AddMessage2Log("Product registered successfully!", "CURV");
//			CUpdateClientPartner::__DeleteDirFilesEx($updatesDirFull);
//		}

//		if (strlen($strError_tmp) > 0)
//		{
//			CUpdateClientPartner::AddMessage2Log($strError_tmp, "CURV");
//			$strError .= $strError_tmp;
//			return False;
//		}
//		else
//			return True;
//	}

//	/** Активирует лицензионный ключ **/
//	function ActivateLicenseKey($arFields, &$strError, $lang = false, $stableVersionsOnly = "Y")
//	{
//		$strError_tmp = "";

//		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::ActivateLicenseKey");

//		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, array(), array(), array());
//		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
//		{
//			if (StrLen($strError_tmp) <= 0)
//				$strError_tmp = "[GNSU01] ".GetMessage("SUPZ_NO_QSTRING").". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			$strQuery .= "&query_type=activate";
//			CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

//			foreach ($arFields as $key => $value)
//				$strQuery .= "&".$key."=".urlencode($value);

//			$stime = CUpdateClientPartner::__GetMicroTime();
//			$content = CUpdateClientPartner::GetHTTPPage("ACTIV", $strQuery, $strError_tmp);
//			if (strlen($content) <= 0)
//			{
//				if (StrLen($strError_tmp) <= 0)
//					$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
//			}

//			CUpdateClientPartner::AddMessage2Log("TIME ActivateLicenseKey(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$arRes = Array();
//			CUpdateClientPartner::ParseServerData($content, $arRes, $strError_tmp);
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			if (isset($arRes["DATA"]["#"]["ERROR"]) && is_array($arRes["DATA"]["#"]["ERROR"]) && count($arRes["DATA"]["#"]["ERROR"]) > 0)
//			{
//				for ($i = 0; $i < count($arRes["DATA"]["#"]["ERROR"]); $i++)
//				{
//					if (strlen($arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"])>0)
//						$strError_tmp .= "[".$arRes["DATA"]["#"]["ERROR"][$i]["@"]["TYPE"]."] ";

//					$strError_tmp .= $arRes["DATA"]["#"]["ERROR"][$i]["#"].". ";
//				}
//			}
//		}

//		if (strlen($strError_tmp) <= 0)
//			CUpdateClientPartner::AddMessage2Log("License key activated successfully!", "CUALK");

//		if (strlen($strError_tmp) > 0)
//		{
//			CUpdateClientPartner::AddMessage2Log($strError_tmp, "CUALK");
//			$strError .= $strError_tmp;
//			return False;
//		}
//		else
//			return True;
//	}

//	/* Получить обновления языков следующего шага */
//	function GetNextStepLangUpdates(&$strError, $lang = false, $arRequestedLangs = array())
//	{
//		$strError_tmp = "";

//		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::GetNextStepLangUpdates");

//		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, array(), $arRequestedLangs, array());
//		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
//		{
//			if (StrLen($strError_tmp) <= 0)
//				$strError_tmp = "[GNSU01] ".GetMessage("SUPZ_NO_QSTRING").". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

//			$stime = CUpdateClientPartner::__GetMicroTime();
//			$content = CUpdateClientPartner::GetHTTPPage("STEPL", $strQuery, $strError_tmp);
//			if (strlen($content) <= 0)
//			{
//				if (StrLen($strError_tmp) <= 0)
//					$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
//			}

//			CUpdateClientPartner::AddMessage2Log("TIME GetNextStepLangUpdates(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			if (!($fp1 = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", "wb")))
//				$strError_tmp = "[GNSU03] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates", GetMessage("SUPP_RV_ER_TEMP_FILE")).". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			fwrite($fp1, $content);
//			fclose($fp1);
//		}

//		if (strlen($strError_tmp) > 0)
//		{
//			CUpdateClientPartner::AddMessage2Log($strError_tmp, "GNSLU00");
//			$strError .= $strError_tmp;
//			return False;
//		}
//		else
//			return True;
//	}

//	/* Получить обновления помощи следующего шага */
//	function GetNextStepHelpUpdates(&$strError, $lang = false, $arRequestedHelps = array())
//	{
//		$strError_tmp = "";

//		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::GetNextStepHelpUpdates");

//		$strQuery = CUpdateClientPartner::__CollectRequestData($strError_tmp, $lang, $stableVersionsOnly, array(), array(), $arRequestedHelps);
//		if ($strQuery === False || StrLen($strQuery) <= 0 || StrLen($strError_tmp) > 0)
//		{
//			if (StrLen($strError_tmp) <= 0)
//				$strError_tmp = "[GNSU01] ".GetMessage("SUPZ_NO_QSTRING").". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			CUpdateClientPartner::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $strQuery));

//			$stime = CUpdateClientPartner::__GetMicroTime();
//			$content = CUpdateClientPartner::GetHTTPPage("STEPH", $strQuery, $strError_tmp);
//			if (strlen($content) <= 0)
//			{
//				if (StrLen($strError_tmp) <= 0)
//					$strError_tmp = "[GNSU02] ".GetMessage("SUPZ_EMPTY_ANSWER").". ";
//			}

//			CUpdateClientPartner::AddMessage2Log("TIME GetNextStepHelpUpdates(request) ".Round(CUpdateClientPartner::__GetMicroTime() - $stime, 3)." sec");
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			if (!($fp1 = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/update_archive.gz", "wb")))
//				$strError_tmp = "[GNSU03] ".str_replace("#FILE#", $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates", GetMessage("SUPP_RV_ER_TEMP_FILE")).". ";
//		}

//		if (StrLen($strError_tmp) <= 0)
//		{
//			fwrite($fp1, $content);
//			fclose($fp1);
//		}

//		if (strlen($strError_tmp) > 0)
//		{
//			CUpdateClientPartner::AddMessage2Log($strError_tmp, "GNSHU00");
//			$strError .= $strError_tmp;
//			return False;
//		}
//		else
//			return True;
//	}

//	function UpdateStepHelps($updatesDir, &$strError)
//	{
//		$strError_tmp = "";

//		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::UpdateHelp");
//		$stime = CUpdateClientPartner::__GetMicroTime();

//		$updatesDirFull = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$updatesDir;
//		$helpDirFull = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/help";

//		$arHelp = array();
//		if (StrLen($strError_tmp) <= 0)
//		{
//			$handle = @opendir($updatesDirFull);
//			if ($handle)
//			{
//				while (false !== ($dir = readdir($handle)))
//				{
//					if ($dir == "." || $dir == "..")
//						continue;
//					if (is_dir($updatesDirFull."/".$dir))
//						$arHelp[] = $dir;
//				}
//				closedir($handle);
//			}
//		}

//		if (!is_array($arHelp) || count($arHelp) <= 0)
//			$strError_tmp .= "[UUH00] ".GetMessage("SUPP_UH_NO_LANG").". ";

//		if (!file_exists($updatesDirFull) || !is_dir($updatesDirFull))
//			$strError_tmp .= "[UUH01] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_CU_NO_TMP_CAT")).". ";

//		if (strlen($strError_tmp) <= 0)
//			if (!is_readable($updatesDirFull))
//				$strError_tmp .= "[UUH03] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_CU_RD_TMP_CAT")).". ";

//		if (strlen($strError_tmp) <= 0)
//		{
//			CUpdateClientPartner::__CheckDirPath($helpDirFull."/", true);

//			if (!file_exists($helpDirFull) || !is_dir($helpDirFull))
//				$strError_tmp .= "[UUH02] ".str_replace("#FILE#", $helpDirFull, GetMessage("SUPP_UH_NO_HELP_CAT")).". ";
//			elseif (!is_writable($helpDirFull))
//				$strError_tmp .= "[UUH03] ".str_replace("#FILE#", $helpDirFull, GetMessage("SUPP_UH_NO_WRT_HELP")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			for ($i = 0; $i < count($arHelp); $i++)
//			{
//				$strError_tmp1 = "";

//				$updateDirFrom = $updatesDirFull."/".$arHelp[$i];

//				if (strlen($strError_tmp1) <= 0)
//					if (!file_exists($updateDirFrom) || !is_dir($updateDirFrom))
//						$strError_tmp1 .= "[UUH04] ".str_replace("#FILE#", $updateDirFrom, GetMessage("SUPP_UL_NO_TMP_LANG")).". ";

//				if (strlen($strError_tmp1) <= 0)
//					if (!is_readable($updateDirFrom))
//						$strError_tmp1 .= "[UUH05] ".str_replace("#FILE#", $updateDirFrom, GetMessage("SUPP_UL_NO_READ_LANG")).". ";

//				if (strlen($strError_tmp1) <= 0)
//				{
//					if (file_exists($helpDirFull."/".$arHelp[$i]."_tmp"))
//						CUpdateClientPartner::__DeleteDirFilesEx($helpDirFull."/".$arHelp[$i]."_tmp");
//					if (file_exists($helpDirFull."/".$arHelp[$i]."_tmp"))
//						$strError_tmp1 .= "[UUH06] ".str_replace("#FILE#", $helpDirFull."/".$arHelp[$i]."_tmp", GetMessage("SUPP_UH_CANT_DEL")).". ";
//				}

//				if (strlen($strError_tmp1) <= 0)
//				{
//					if (file_exists($helpDirFull."/".$arHelp[$i]))
//						if (!rename($helpDirFull."/".$arHelp[$i], $helpDirFull."/".$arHelp[$i]."_tmp"))
//							$strError_tmp1 .= "[UUH07] ".str_replace("#FILE#", $helpDirFull."/".$arHelp[$i], GetMessage("SUPP_UH_CANT_RENAME")).". ";
//				}

//				if (strlen($strError_tmp1) <= 0)
//				{
//					CUpdateClientPartner::__CheckDirPath($helpDirFull."/".$arHelp[$i]."/", true);

//					if (!file_exists($helpDirFull."/".$arHelp[$i]) || !is_dir($helpDirFull."/".$arHelp[$i]))
//						$strError_tmp1 .= "[UUH08] ".str_replace("#FILE#", $helpDirFull."/".$arHelp[$i], GetMessage("SUPP_UH_CANT_CREATE")).". ";
//					elseif (!is_writable($helpDirFull."/".$arHelp[$i]))
//						$strError_tmp1 .= "[UUH09] ".str_replace("#FILE#", $helpDirFull."/".$arHelp[$i], GetMessage("SUPP_UH_CANT_WRITE")).". ";
//				}

//				if (strlen($strError_tmp1) <= 0)
//					CUpdateClientPartner::__CopyDirFiles($updateDirFrom, $helpDirFull."/".$arHelp[$i], $strError_tmp1);

//				if (strlen($strError_tmp1) > 0)
//				{
//					$strError_tmp .= $strError_tmp1;
//				}
//				else
//				{
//					if (file_exists($helpDirFull."/".$arHelp[$i]."_tmp"))
//						CUpdateClientPartner::__DeleteDirFilesEx($helpDirFull."/".$arHelp[$i]."_tmp");
//				}
//			}
//			CUpdateClientPartner::ClearUpdateFolder($updatesDirFull);
//		}

//		CUpdateClientPartner::AddMessage2Log("TIME UpdateHelp ".Round(CUpdateClientPartner::__GetMicroTime()-$stime, 3)." sec");

//		if (strlen($strError_tmp)>0)
//		{
//			CUpdateClientPartner::AddMessage2Log($strError_tmp, "USH");
//			$strError .= $strError_tmp;
//			return False;
//		}
//		else
//			return True;
//	}

//	function UpdateStepLangs($updatesDir, &$strError)
//	{
//		global $DB;
//		$strError_tmp = "";

//		$stime = CUpdateClientPartner::__GetMicroTime();

//		$updatesDirFull = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/".$updatesDir;
//		if (!file_exists($updatesDirFull) || !is_dir($updatesDirFull))
//			$strError_tmp .= "[UUL01] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_CU_NO_TMP_CAT")).". ";

//		$arLangs = array();
//		if (StrLen($strError_tmp) <= 0)
//		{
//			$handle = @opendir($updatesDirFull);
//			if ($handle)
//			{
//				while (false !== ($dir = readdir($handle)))
//				{
//					if ($dir == "." || $dir == "..")
//						continue;
//					if (is_dir($updatesDirFull."/".$dir))
//						$arLangs[] = $dir;
//				}
//				closedir($handle);
//			}
//		}

//		if (!is_array($arLangs) || count($arLangs) <= 0)
//			$strError_tmp .= "[UUL02] ".GetMessage("SUPP_UL_NO_LANGS").". ";

//		if (strlen($strError_tmp) <= 0)
//			if (!is_readable($updatesDirFull))
//				$strError_tmp .= "[UUL03] ".str_replace("#FILE#", $updatesDirFull, GetMessage("SUPP_CU_RD_TMP_CAT")).". ";

//		if (strlen($strError_tmp) <= 0)
//		{
//			$updateDirToComp = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/components/bitrix";

//			CUpdateClientPartner::__CheckDirPath($updateDirToComp."/", true);

//			if (file_exists($updateDirToComp) && is_dir($updateDirToComp))
//				if (!is_writable($updateDirToComp))
//					$strError_tmp .= "[UUL0511] ".str_replace("#FILE#", $updateDirToComp, GetMessage("SUPP_UL_NO_WRT_CAT")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$updateDirTo = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules";

//			CUpdateClientPartner::__CheckDirPath($updateDirTo."/", true);

//			if (!file_exists($updateDirTo) || !is_dir($updateDirTo))
//				$strError_tmp .= "[UUL04] ".str_replace("#FILE#", $updateDirTo, GetMessage("SUPP_UL_CAT")).". ";
//			elseif (!is_writable($updateDirTo))
//				$strError_tmp .= "[UUL05] ".str_replace("#FILE#", $updateDirTo, GetMessage("SUPP_UL_NO_WRT_CAT")).". ";
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$arLangModules1 = array();
//			$arLangModules2 = array();
//			$handle1 = @opendir($updateDirToComp);
//			if ($handle1)
//			{
//				while (false !== ($dir1 = readdir($handle1)))
//				{
//					if (is_dir($updateDirToComp."/".$dir1) && $dir1 != "." && $dir1 != "..")
//					{
//						if (!is_writable($updateDirToComp."/".$dir1))
//							$strError_tmp .= "[UUL051] ".str_replace("#FILE#", $updateDirToComp."/".$dir1, GetMessage("SUPP_UL_NO_WRT_CAT")).". ";

//						if (file_exists($updateDirToComp."/".$dir1."/lang") && !is_writable($updateDirToComp."/".$dir1."/lang"))
//							$strError_tmp .= "[UUL052] ".str_replace("#FILE#", $updateDirToComp."/".$dir1."/lang", GetMessage("SUPP_UL_NO_WRT_CAT")).". ";

//						$arLangModules1[] = $dir1;

//						$handle2 = @opendir($updateDirToComp."/".$dir1."/templates");
//						if ($handle2)
//						{
//							while (false !== ($dir2 = readdir($handle2)))
//							{
//								if (is_dir($updateDirToComp."/".$dir1."/templates/".$dir2) && $dir2 != "." && $dir2 != "..")
//								{
//									if (!is_writable($updateDirToComp."/".$dir1."/templates/".$dir2))
//										$strError_tmp .= "[UUL05111] ".str_replace("#FILE#", $updateDirToComp."/".$dir1."/templates/".$dir2, GetMessage("SUPP_UL_NO_WRT_CAT")).". ";

//									if (file_exists($updateDirToComp."/".$dir1."/templates/".$dir2."/lang") && !is_writable($updateDirToComp."/".$dir1."/templates/".$dir2."/lang"))
//										$strError_tmp .= "[UUL05211] ".str_replace("#FILE#", $updateDirToComp."/".$dir1."/templates/".$dir2."/lang", GetMessage("SUPP_UL_NO_WRT_CAT")).". ";

//									$arLangModules2[] = $dir1."@".$dir2;
//								}
//							}
//							closedir($handle2);
//						}
//					}
//				}
//				closedir($handle1);
//			}
//		}

//		if (strlen($strError_tmp) <= 0)
//		{
//			$arLangModules = array();
//			$handle = @opendir($updateDirTo);
//			if ($handle)
//			{
//				while (false !== ($dir = readdir($handle)))
//				{
//					if (is_dir($updateDirTo."/".$dir) && $dir!="." && $dir!="..")
//					{
//						if (!is_writable($updateDirTo."/".$dir))
//							$strError_tmp .= "[UUL051] ".str_replace("#FILE#", $updateDirTo."/".$dir, GetMessage("SUPP_UL_NO_WRT_CAT")).". ";

//						if (file_exists($updateDirTo."/".$dir."/lang") && !is_writable($updateDirTo."/".$dir."/lang"))
//							$strError_tmp .= "[UUL052] ".str_replace("#FILE#", $updateDirTo."/".$dir."/lang", GetMessage("SUPP_UL_NO_WRT_CAT")).". ";

//						$arLangModules[] = $dir;
//					}
//				}
//				closedir($handle);
//			}
//		}


//		if (strlen($strError_tmp) <= 0)
//		{
//			for ($i = 0; $i < count($arLangs); $i++)
//			{
//				$strError_tmp1 = "";

//				$updateDirFrom = $updatesDirFull."/".$arLangs[$i];

//				if (strlen($strError_tmp1) <= 0)
//					if (!file_exists($updateDirFrom) || !is_dir($updateDirFrom))
//						$strError_tmp1 .= "[UUL06] ".str_replace("#FILE#", $updateDirFrom, GetMessage("SUPP_UL_NO_TMP_LANG")).". ";

//				if (strlen($strError_tmp1) <= 0)
//					if (!is_readable($updateDirFrom))
//						$strError_tmp1 .= "[UUL07] ".str_replace("#FILE#", $updateDirFrom, GetMessage("SUPP_UL_NO_READ_LANG")).". ";

//				if (strlen($strError_tmp1) <= 0)
//				{
//					$handle1 = @opendir($updateDirFrom."/__components");
//					if ($handle1)
//					{
//						while (false !== ($dir1 = readdir($handle1)))
//						{
//							if (is_dir($updateDirFrom."/__components/".$dir1) && $dir1 != "." && $dir1 != "..")
//							{
//								if (file_exists($updateDirFrom."/__components/".$dir1."/lang") && in_array($dir1, $arLangModules1))
//									CUpdateClientPartner::__CopyDirFiles($updateDirFrom."/__components/".$dir1."/lang", $updateDirToComp."/".$dir1."/lang", $strError_tmp1);

//								$handle2 = @opendir($updateDirFrom."/__components/".$dir1."/templates");
//								if ($handle2)
//								{
//									while (false !== ($dir2 = readdir($handle2)))
//									{
//										if (is_dir($updateDirFrom."/__components/".$dir1."/templates/".$dir2) && $dir2 != "." && $dir2 != "..")
//										{
//											if (file_exists($updateDirFrom."/__components/".$dir1."/templates/".$dir2."/lang") && in_array($dir1."@".$dir2, $arLangModules2))
//												CUpdateClientPartner::__CopyDirFiles($updateDirFrom."/__components/".$dir1."/templates/".$dir2."/lang", $updateDirToComp."/".$dir1."/templates/".$dir2."/lang", $strError_tmp1);
//										}
//									}
//									closedir($handle2);
//								}
//							}
//						}
//						closedir($handle1);
//					}
//				}

				// Удалить старые файлы

//				if (strlen($strError_tmp1) > 0)
//					$strError_tmp .= $strError_tmp1;

//				CUpdateClientPartner::__DeleteDirFilesEx($updateDirFrom."/__components");
//			}
//		}


//		if (strlen($strError_tmp) <= 0)
//		{
//			for ($i = 0; $i < count($arLangs); $i++)
//			{
//				$strError_tmp1 = "";

//				$updateDirFrom = $updatesDirFull."/".$arLangs[$i];

//				if (strlen($strError_tmp1) <= 0)
//					if (!file_exists($updateDirFrom) || !is_dir($updateDirFrom))
//						$strError_tmp1 .= "[UUL06] ".str_replace("#FILE#", $updateDirFrom, GetMessage("SUPP_UL_NO_TMP_LANG")).". ";

//				if (strlen($strError_tmp1) <= 0)
//					if (!is_readable($updateDirFrom))
//						$strError_tmp1 .= "[UUL07] ".str_replace("#FILE#", $updateDirFrom, GetMessage("SUPP_UL_NO_READ_LANG")).". ";

//				if (strlen($strError_tmp1) <= 0)
//				{
//					for ($j = 0; $j < count($arLangModules); $j++)
//					{
//						if (file_exists($updateDirFrom."/".$arLangModules[$j]) && is_dir($updateDirFrom."/".$arLangModules[$j]))
//							CUpdateClientPartner::__CopyDirFiles($updateDirFrom."/".$arLangModules[$j], $updateDirTo."/".$arLangModules[$j], $strError_tmp1);
//					}
//				}

				// Удалить старые файлы

//				if (strlen($strError_tmp1) > 0)
//					$strError_tmp .= $strError_tmp1;
//			}
//			CUpdateClientPartner::ClearUpdateFolder($updatesDirFull);
//		}

//		CUpdateClientPartner::AddMessage2Log("TIME UpdateLangs ".Round(CUpdateClientPartner::__GetMicroTime()-$stime, 3)." sec");

//		if (strlen($strError_tmp) > 0)
//		{
//			CUpdateClientPartner::AddMessage2Log($strError_tmp, "USL");
//			$strError .= $strError_tmp;
//			return False;
//		}
//		else
//			return True;
//	}

	/** Запускает updater модуля **/
	function __RunUpdaterScript($path, &$strError, $updateDirFrom, $moduleID)
	{
		global $DBType, $DB, $APPLICATION, $USER;

		if (!isset($GLOBALS["UPDATE_STRONG_UPDATE_CHECK"])
			|| ($GLOBALS["UPDATE_STRONG_UPDATE_CHECK"] != "Y" && $GLOBALS["UPDATE_STRONG_UPDATE_CHECK"] != "N"))
		{
			$GLOBALS["UPDATE_STRONG_UPDATE_CHECK"] = ((US_CALL_TYPE != "DB") ? COption::GetOptionString("main", "strong_update_check", "Y") : "Y");
		}
		$strongUpdateCheck = $GLOBALS["UPDATE_STRONG_UPDATE_CHECK"];

		$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

		$path = str_replace("\\", "/", $path);
		$updaterPath = dirname($path);
		$updaterPath = substr($updaterPath, strlen($_SERVER["DOCUMENT_ROOT"]));
		$updaterPath = Trim($updaterPath, " \t\n\r\0\x0B/\\");
		if (strlen($updaterPath) > 0)
			$updaterPath = "/".$updaterPath;

		$updaterName = substr($path, strlen($_SERVER["DOCUMENT_ROOT"]));

		CUpdateClientPartner::AddMessage2Log("Run updater '".$updaterName."'", "CSURUS1");

		$updater = new CUpdater();
		$updater->Init($updaterPath, $DBType, $updaterName, $updateDirFrom, $moduleID, US_CALL_TYPE);

		$errorMessage = "";

		include($path);

		if (strlen($errorMessage) > 0)
			$strError .= $errorMessage;
		if (is_array($updater->errorMessage) && count($updater->errorMessage) > 0)
			$strError .= implode("\n", $updater->errorMessage);

		unset($updater);
	}

	/** Сравнение двух версий в формате XX.XX.XX  **/
	/** Возвращает 1, если $strVers1 > $strVers2  **/
	/** Возвращает -1, если $strVers1 < $strVers2 **/
	/** Возвращает 0, если $strVers1 == $strVers2 **/
	function __CompareVersions($strVers1, $strVers2)
	{
		$strVers1 = Trim($strVers1);
		$strVers2 = Trim($strVers2);

		if ($strVers1 == $strVers2)
			return 0;

		$arVers1 = explode(".", $strVers1);
		$arVers2 = explode(".", $strVers2);

		if (IntVal($arVers1[0]) > IntVal($arVers2[0])
			|| IntVal($arVers1[0]) == IntVal($arVers2[0]) && IntVal($arVers1[1]) > IntVal($arVers2[1])
			|| IntVal($arVers1[0]) == IntVal($arVers2[0]) && IntVal($arVers1[1]) == IntVal($arVers2[1]) && IntVal($arVers1[2]) > IntVal($arVers2[2]))
		{
			return 1;
		}

		if (IntVal($arVers1[0]) == IntVal($arVers2[0]) && IntVal($arVers1[1]) == IntVal($arVers2[1]) && IntVal($arVers1[2]) == IntVal($arVers2[2]))
		{
			return 0;
		}

		return -1;
	}

	/** Запрашивает методом POST страницу $page со списком параметров **/
	/** $strVars и возвращает тело ответа. В параметре $strError      **/
	/** возвращается текст ошибки, если таковая была.                 **/
	function __GetHTTPPage($page, $strVars, &$strError)
	{
		global $SERVER_NAME, $DB;

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::GetHTTPPage");

		$ServerIP = COption::GetOptionString("main", "update_site", DEFAULT_UPDATE_SERVER);
		$ServerPort = 80;

		$proxyAddr = COption::GetOptionString("main", "update_site_proxy_addr", "");
		$proxyPort = COption::GetOptionString("main", "update_site_proxy_port", "");
		$proxyUserName = COption::GetOptionString("main", "update_site_proxy_user", "");
		$proxyPassword = COption::GetOptionString("main", "update_site_proxy_pass", "");

		$bUseProxy = (strlen($proxyAddr) > 0 && strlen($proxyPort) > 0);

		if ($page == "LIST")
			$page = "smp_updater_list.php";
		elseif ($page == "STEPM")
			$page = "smp_updater_modules.php";
		elseif ($page == "SEARCH")
			$page = "smp_updater_search.php";
		elseif ($page == "MODULE")
			$page = "smp_updater_actions.php";
		elseif ($page == "REG")
			$page = "smp_updater_register.php";

		$strVars .= "&product=".(IsModuleInstalled("intranet") ? "CORPORTAL" : "BSM");

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

		$FP = fsockopen($requestIP, $requestPort, $errno, $errstr, 120);

		if ($FP)
		{
			$strRequest = "";

			if ($bUseProxy)
			{
				$strRequest .= "POST http://".$ServerIP."/bitrix/updates/".$page." HTTP/1.0\r\n";
				if (strlen($proxyUserName) > 0)
					$strRequest .= "Proxy-Authorization: Basic ".base64_encode($proxyUserName.":".$proxyPassword)."\r\n";
			}
			else
				$strRequest .= "POST /bitrix/updates/".$page." HTTP/1.0\r\n";

			$strRequest .= "User-Agent: BitrixSMUpdater\r\n";
			$strRequest .= "Accept: */*\r\n";
			$strRequest .= "Host: ".$ServerIP."\r\n";
			$strRequest .= "Accept-Language: en\r\n";
			$strRequest .= "Content-type: application/x-www-form-urlencoded\r\n";
			$strRequest .= "Content-length: ".strlen($strVars)."\r\n\r\n";
			$strRequest .= "$strVars";
			$strRequest .= "\r\n";
//CUpdateClientPartner::AddMessage2Log($strRequest, "!!!!!");
			fputs($FP, $strRequest);

			$bChunked = False;
			while (!feof($FP))
			{
				$line = fgets($FP, 4096);
				if ($line != "\r\n")
				{
					if (preg_match("/Transfer-Encoding: +chunked/i", $line))
						$bChunked = True;
				}
				else
				{
					break;
				}
			}

			$content = "";
			if ($bChunked)
			{
				$maxReadSize = 4096;

				$length = 0;
				$line = FGets($FP, $maxReadSize);
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

					while ($readSize > 0 && $line = fread($FP, $readSize))
					{
						$content .= $line;
						$processedSize += StrLen($line);
						$newSize = $chunkSize - $processedSize;
						$readSize = (($newSize > $maxReadSize) ? $maxReadSize : $newSize);
					}
					$length += $chunkSize;

					$line = FGets($FP, $maxReadSize);

					$line = FGets($FP, $maxReadSize);
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
			else
			{
				while ($line = fread($FP, 4096))
					$content .= $line;
			}

			fclose($FP);
		}
		else
		{
			$content = "";
			$strError .= GetMessage("SUPP_GHTTP_ER").": [".$errno."] ".$errstr.". ";
			if (IntVal($errno) <= 0)
				$strError .= GetMessage("SUPP_GHTTP_ER_DEF")." ";

			CUpdateClientPartner::AddMessage2Log("Error connecting 2 ".$ServerIP.": [".$errno."] ".$errstr."", "ERRCONN");
		}
		//CUpdateClientPartner::AddMessage2Log($content, "!1!");

		//echo "content:<br>".$content."<br><br>";
		return $content;
	}


	/** Проверяет на ошибки ответ сервера $strServerOutput **/
	/** и парсит в массив $arRes                           **/
	function __ParseServerData(&$strServerOutput, &$arRes, &$strError)
	{
		$strError_tmp = "";
		$arRes = array();

		CUpdateClientPartner::AddMessage2Log("exec CUpdateClientPartner::ParseServerData");

		//CUpdateClientPartner::AddMessage2Log($strServerOutput, "!2!");
		//echo "strServerOutput:<br>".htmlspecialchars($strServerOutput)."<br><br>";

		if (strlen($strServerOutput) <= 0)
			$strError_tmp .= "[UPSD01] ".GetMessage("SUPP_AS_EMPTY_RESP").". ";

		if (strlen($strError_tmp) <= 0)
		{
			if (SubStr($strServerOutput, 0, StrLen("<DATA>")) != "<DATA>" && CUpdateClientPartner::__IsGzipInstalled())
				$strServerOutput = @gzuncompress($strServerOutput);
			if (SubStr($strServerOutput, 0, StrLen("<DATA>")) != "<DATA>")
			{
				CUpdateClientPartner::AddMessage2Log(substr($strServerOutput, 0, 100), "UPSD02");
				$strError_tmp .= "[UPSD02] ".GetMessage("SUPP_PSD_BAD_RESPONSE").". ";
			}
		}
		//CUpdateClientPartner::AddMessage2Log($strServerOutput, "!3!");

		//echo "strServerOutput:<br>".htmlspecialchars($strServerOutput)."<br><br>";

		if (strlen($strError_tmp) <= 0)
		{
//			$arRes = CUpdateClientPartner::xmlize($strServerOutput);

			$objXML = new CUpdatesXML();
			$objXML->LoadString($strServerOutput);
			$arRes = $objXML->GetArray();

			if (!is_array($arRes) || !isset($arRes["DATA"]) || !is_array($arRes["DATA"]))
				$strError_tmp .= "[UPSD03] ".GetMessage("SUPP_PSD_BAD_TRANS").". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			if (isset($arRes["DATA"]["#"]["RESPONSE"]))
			{
				$CRCCode = $arRes["DATA"]["#"]["RESPONSE"][0]["@"]["CRC_CODE"];
				if (StrLen($CRCCode) > 0)
					COption::SetOptionString(US_BASE_MODULE, "crc_code", $CRCCode);
			}
			if (isset($arRes["DATA"]["#"]["CLIENT"]) && isset($arRes["DATA"]["#"]["CLIENT"][0]["@"]["DATE_TO_SOURCE"]))
				COption::SetOptionString(US_BASE_MODULE, "~support_finish_date", $arRes["DATA"]["#"]["CLIENT"][0]["@"]["DATE_TO_SOURCE"]);
			if (isset($arRes["DATA"]["#"]["CLIENT"]) && isset($arRes["DATA"]["#"]["CLIENT"][0]["@"]["MAX_SITES"]))
				COption::SetOptionString("main", "PARAM_MAX_SITES", IntVal($arRes["DATA"]["#"]["CLIENT"][0]["@"]["MAX_SITES"]));
			if (isset($arRes["DATA"]["#"]["CLIENT"]) && isset($arRes["DATA"]["#"]["CLIENT"][0]["@"]["MAX_USERS"]))
				COption::SetOptionString("main", "PARAM_MAX_USERS", IntVal($arRes["DATA"]["#"]["CLIENT"][0]["@"]["MAX_USERS"]));
		}

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "CUPSD");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return True;
	}

	/** Собирает из массива модулей строку запроса **/
//	function ModulesArray2Query($arClientModules, $pref = "bitm_")
//	{
//		$strRes = "";
//		if (is_array($arClientModules))
//		{
//			foreach ($arClientModules as $key => $value)
//			{
//				if (strlen($strRes) > 0)
//					$strRes .= "&";

//				$strRes .= $pref.$key."=".urlencode($value);
//			}
//		}

//		return $strRes;
//	}

	/** Проверка на установку GZip компрессии **/
	function __IsGzipInstalled()
	{
		if (function_exists("gzcompress")) return True;
		return False;
	}

	function __GetFooPath()
	{
		$db = CLang::GetList($by="", $order="", array("ACTIVE" => "Y"));
		$cnt = 0;
		while ($ar = $db->Fetch())
			$cnt++;
		return $cnt;
	}

	/** Собирает клиентские языки с датами **/
//	function GetCurrentLanguages(&$strError, $arSelected = false)
//	{
//		/*
//		$arClientLangs = array();

//		$strLangPath = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/lang";

//		$db_res = false;
//		if (class_exists("CLanguage"))
//			$db_res = CLanguage::GetList(($by="sort"), ($order="asc"), array("ACTIVE"=>"Y"));
//		elseif (class_exists("CLang"))
//			$db_res = CLang::GetList(($by="sort"), ($order="asc"), array("ACTIVE"=>"Y"));

//		if ($db_res === false)
//		{
//			CUpdateClientPartner::AddMessage2Log(GetMessage("SUPP_GL_WHERE_LANGS"), "UGL00");
//			$strError .= "[UGL00] ".GetMessage("SUPP_GL_WHERE_LANGS").". ";
//		}
//		else
//		{
//			while ($ar_res = $db_res->Fetch())
//			{
//				if ($arSelected === false || is_array($arSelected) && in_array($ar_res["LID"], $arSelected))
//				{
//					$strLangDate = "";
//					if (file_exists($strLangPath."/".$ar_res["LID"]) && file_exists($strLangPath."/".$ar_res["LID"]."/supd_lang_date.dat"))
//					{
//						$strLangDate = file_get_contents($strLangPath."/".$ar_res["LID"]."/supd_lang_date.dat");
//						$strLangDate = preg_replace("/[\D]+/", "", $strLangDate);

//						if (strlen($strLangDate) != 8)
//						{
//							CUpdateClientPartner::AddMessage2Log(str_replace("#LANG#", $ar_res["LID"], GetMessage("SUPP_GL_ERR_DLANG")), "UGL01");
//							$strError .= "[UGL01] ".str_replace("#LANG#", $ar_res["LID"], GetMessage("SUPP_GL_ERR_DLANG")).". ";
//							$strLangDate = "";
//						}
//					}

//					$arClientLangs[$ar_res["LID"]] = $strLangDate;
//				}
//			}

//			if ($arSelected === false && count($arClientLangs) <= 0)
//			{
//				CUpdateClientPartner::AddMessage2Log(GetMessage("SUPP_GL_NO_SITE_LANGS"), "UGL02");
//				$strError .= "[UGL02] ".GetMessage("SUPP_GL_NO_SITE_LANGS").". ";
//			}
//		}

//		return $arClientLangs;
//		*/
//		$arClientLangs = array();

//		$strLangPath = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/modules/main/lang";

//		$handle = @opendir($strLangPath);
//		if ($handle)
//		{
//			while (false !== ($dir = readdir($handle)))
//			{
//				if (is_dir($strLangPath."/".$dir) && $dir!="." && $dir!="..")
//				{
//					if ($arSelected===false || is_array($arSelected) && in_array($dir, $arSelected))
//					{
//						$strLangDate = "";
//						if (file_exists($strLangPath."/".$dir."/supd_lang_date.dat"))
//						{
//							$strLangDate = file_get_contents($strLangPath."/".$dir."/supd_lang_date.dat");
//							$strLangDate = preg_replace("/[\D]+/", "", $strLangDate);

//							if (strlen($strLangDate) != 8)
//							{
//								CUpdateClientPartner::AddMessage2Log(str_replace("#LANG#", $dir, GetMessage("SUPP_GL_ERR_DLANG")), "UGL01");
//								$strError .= "[UGL01] ".str_replace("#LANG#", $dir, GetMessage("SUPP_GL_ERR_DLANG")).". ";
//								$strLangDate = "";
//							}
//						}

//						$arClientLangs[$dir] = $strLangDate;
//					}
//				}
//			}
//			closedir($handle);
//		}

//		$db_res = false;
//		if (class_exists("CLanguage"))
//			$db_res = CLanguage::GetList(($by="sort"), ($order="asc"), array("ACTIVE"=>"Y"));
//		elseif (class_exists("CLang"))
//			$db_res = CLang::GetList(($by="sort"), ($order="asc"), array("ACTIVE"=>"Y"));

//		if ($db_res===false)
//		{
//			CUpdateClientPartner::AddMessage2Log(GetMessage("SUPP_GL_WHERE_LANGS"), "UGL00");
//			$strError .= "[UGL00] ".GetMessage("SUPP_GL_WHERE_LANGS").". ";
//		}
//		else
//		{
//			while ($ar_res = $db_res->Fetch())
//			{
//				if ($arSelected===false || is_array($arSelected) && in_array($ar_res["LID"], $arSelected))
//				{
//					if (!array_key_exists($ar_res["LID"], $arClientLangs))
//					{
//						$arClientLangs[$ar_res["LID"]] = "";
//					}
//				}
//			}

//			if ($arSelected===false && count($arClientLangs)<=0)
//			{
//				CUpdateClientPartner::AddMessage2Log(GetMessage("SUPP_GL_NO_SITE_LANGS"), "UGL02");
//				$strError .= "[UGL02] ".GetMessage("SUPP_GL_NO_SITE_LANGS").". ";
//			}
//		}

//		return $arClientLangs;
//	}

	function __GetFooPath1($v = 0)
	{
		$q = "SELECT COUNT(ID) as C FROM b_user WHERE ACTIVE = 'Y' AND LAST_LOGIN IS NOT NULL";
		if ($v == 0)
			$q = "SELECT COUNT(U.ID) as C FROM b_user U WHERE U.ACTIVE = 'Y' AND U.LAST_LOGIN IS NOT NULL AND EXISTS(SELECT 'x' FROM b_utm_user UF, b_user_field F WHERE F.ENTITY_ID = 'USER' AND F.FIELD_NAME = 'UF_DEPARTMENT' AND UF.FIELD_ID = F.ID AND UF.VALUE_ID = U.ID AND UF.VALUE_INT IS NOT NULL AND UF.VALUE_INT <> 0)";
		$dbRes = $GLOBALS["DB"]->Query($q, true);
		if ($dbRes && ($arRes = $dbRes->Fetch()))
			return $arRes["C"];
		else
			return 0;
	}

	/** Собирает клиентские help'ы с датами **/
//	function GetCurrentHelps(&$strError, $arSelected = false)
//	{
//		$arClientHelps = array();

//		$strHelpPath = $_SERVER["DOCUMENT_ROOT"].US_SHARED_KERNEL_PATH."/help";

//		$handle = @opendir($strHelpPath);
//		if ($handle)
//		{
//			while (false !== ($dir = readdir($handle)))
//			{
//				if (is_dir($strHelpPath."/".$dir) && $dir!="." && $dir!="..")
//				{
//					if ($arSelected===false || is_array($arSelected) && in_array($dir, $arSelected))
//					{
//						$strHelpDate = "";
//						if (file_exists($strHelpPath."/".$dir."/supd_lang_date.dat"))
//						{
//							$strHelpDate = file_get_contents($strHelpPath."/".$dir."/supd_lang_date.dat");
//							$strHelpDate = preg_replace("/[\D]+/", "", $strHelpDate);

//							if (strlen($strHelpDate)!=8)
//							{
//								CUpdateClientPartner::AddMessage2Log(str_replace("#HELP#", $dir, GetMessage("SUPP_GH_ERR_DHELP")), "UGH01");
//								$strError .= "[UGH01] ".str_replace("#HELP#", $dir, GetMessage("SUPP_GH_ERR_DHELP")).". ";
//								$strHelpDate = "";
//							}
//						}

//						$arClientHelps[$dir] = $strHelpDate;
//					}
//				}
//			}
//			closedir($handle);
//		}

//		$db_res = false;
//		if (class_exists("CLanguage"))
//			$db_res = CLanguage::GetList(($by="sort"), ($order="asc"), array("ACTIVE"=>"Y"));
//		elseif (class_exists("CLang"))
//			$db_res = CLang::GetList(($by="sort"), ($order="asc"), array("ACTIVE"=>"Y"));

//		if ($db_res===false)
//		{
//			CUpdateClientPartner::AddMessage2Log(GetMessage("SUPP_GL_WHERE_LANGS"), "UGH00");
//			$strError .= "[UGH00] ".GetMessage("SUPP_GL_WHERE_LANGS").". ";
//		}
//		else
//		{
//			while ($ar_res = $db_res->Fetch())
//			{
//				if ($arSelected===false || is_array($arSelected) && in_array($ar_res["LID"], $arSelected))
//				{
//					if (!array_key_exists($ar_res["LID"], $arClientHelps))
//					{
//						$arClientHelps[$ar_res["LID"]] = "";
//					}
//				}
//			}

//			if ($arSelected===false && count($arClientHelps)<=0)
//			{
//				CUpdateClientPartner::AddMessage2Log(GetMessage("SUPP_GL_NO_SITE_LANGS"), "UGH02");
//				$strError .= "[UGH02] ".GetMessage("SUPP_GL_NO_SITE_LANGS").". ";
//			}
//		}

//		return $arClientHelps;
//	}

	/** Создание путя, если его нет, и установка прав писать **/
	function __CheckDirPath($path, $bPermission = true)
	{
		$badDirs = Array();
		$path = str_replace("\\", "/", $path);
		$path = str_replace("//", "/", $path);

		if ($path[strlen($path)-1] != "/") //отрежем имя файла
		{
			$p = CUpdateClientPartner::__bxstrrpos($path, "/");
			$path = substr($path, 0, $p);
		}

		while (strlen($path)>1 && $path[strlen($path)-1]=="/") //отрежем / в конце, если есть
			$path = substr($path, 0, strlen($path)-1);

		$p = CUpdateClientPartner::__bxstrrpos($path, "/");
		while ($p > 0)
		{
			if (file_exists($path) && is_dir($path))
			{
				if ($bPermission)
				{
					if (!is_writable($path))
						@chmod($path, BX_DIR_PERMISSIONS);
				}
				break;
			}
			$badDirs[] = substr($path, $p+1);
			$path = substr($path, 0, $p);
			$p = CUpdateClientPartner::__bxstrrpos($path, "/");
		}

		for ($i = count($badDirs)-1; $i>=0; $i--)
		{
			$path = $path."/".$badDirs[$i];
			@mkdir($path, BX_DIR_PERMISSIONS);
		}
	}


	/** Рекурсивное копирование из $path_from в $path_to **/
	function __CopyDirFiles($path_from, $path_to, &$strError, $bSkipUpdater = True)
	{
		$strError_tmp = "";

		while (strlen($path_from) > 1 && $path_from[strlen($path_from)-1] == "/")
			$path_from = substr($path_from, 0, strlen($path_from)-1);

		while (strlen($path_to) > 1 && $path_to[strlen($path_to)-1] == "/")
			$path_to = substr($path_to, 0, strlen($path_to)-1);

		if (strpos($path_to."/", $path_from."/") === 0)
			$strError_tmp .= "[UCDF01] ".GetMessage("SUPP_CDF_SELF_COPY").". ";

		if (strlen($strError_tmp) <= 0)
		{
			if (!file_exists($path_from))
				$strError_tmp .= "[UCDF02] ".str_replace("#FILE#", $path_from, GetMessage("SUPP_CDF_NO_PATH")).". ";
		}

		if (strlen($strError_tmp) <= 0)
		{
			$strongUpdateCheck = COption::GetOptionString("main", "strong_update_check", "Y");

			if (is_dir($path_from))
			{
				CUpdateClientPartner::__CheckDirPath($path_to."/");

				if (!file_exists($path_to) || !is_dir($path_to))
					$strError_tmp .= "[UCDF03] ".str_replace("#FILE#", $path_to, GetMessage("SUPP_CDF_CANT_CREATE")).". ";
				elseif (!is_writable($path_to))
					$strError_tmp .= "[UCDF04] ".str_replace("#FILE#", $path_to, GetMessage("SUPP_CDF_CANT_WRITE")).". ";

				if (strlen($strError_tmp) <= 0)
				{
					if ($handle = @opendir($path_from))
					{
						while (($file = readdir($handle)) !== false)
						{
							if ($file == "." || $file == "..")
								continue;

							if ($bSkipUpdater && substr($file, 0, strlen("updater")) == "updater")
								continue;

							if (is_dir($path_from."/".$file))
							{
								CUpdateClientPartner::__CopyDirFiles($path_from."/".$file, $path_to."/".$file, $strError_tmp);
							}
							elseif (is_file($path_from."/".$file))
							{
								if (file_exists($path_to."/".$file) && !is_writable($path_to."/".$file))
								{
									$strError_tmp .= "[UCDF05] ".str_replace("#FILE#", $path_to."/".$file, GetMessage("SUPP_CDF_CANT_FILE")).". ";
								}
								else
								{
									if ($strongUpdateCheck == "Y")
										$crc32_old = dechex(crc32(file_get_contents($path_from."/".$file)));

									@copy($path_from."/".$file, $path_to."/".$file);
									@chmod($path_to."/".$file, BX_FILE_PERMISSIONS);

									if ($strongUpdateCheck == "Y")
									{
										$crc32_new = dechex(crc32(file_get_contents($path_to."/".$file)));
										if ($crc32_new != $crc32_old)
										{
											$strError_tmp .= "[UCDF061] ".str_replace("#FILE#", $path_to."/".$file, GetMessage("SUPP_UGA_FILE_CRUSH")).". ";
										}
									}
								}
							}
						}
						@closedir($handle);
					}
				}
			}
			else
			{
				$p = CUpdateClientPartner::__bxstrrpos($path_to, "/");
				$path_to_dir = substr($path_to, 0, $p);
				CUpdateClientPartner::__CheckDirPath($path_to_dir."/");

				if (!file_exists($path_to_dir) || !is_dir($path_to_dir))
					$strError_tmp .= "[UCDF06] ".str_replace("#FILE#", $path_to_dir, GetMessage("SUPP_CDF_CANT_FOLDER")).". ";
				elseif (!is_writable($path_to_dir))
					$strError_tmp .= "[UCDF07] ".str_replace("#FILE#", $path_to_dir, GetMessage("SUPP_CDF_CANT_FOLDER_WR")).". ";

				if (strlen($strError_tmp) <= 0)
				{
					if ($strongUpdateCheck == "Y")
						$crc32_old = dechex(crc32(file_get_contents($path_from)));

					@copy($path_from, $path_to);
					@chmod($path_to, BX_FILE_PERMISSIONS);

					if ($strongUpdateCheck == "Y")
					{
						$crc32_new = dechex(crc32(file_get_contents($path_to)));
						if ($crc32_new != $crc32_old)
						{
							$strError_tmp .= "[UCDF0611] ".str_replace("#FILE#", $path_to, GetMessage("SUPP_UGA_FILE_CRUSH")).". ";
						}
					}
				}
			}
		}

		if (strlen($strError_tmp) > 0)
		{
			CUpdateClientPartner::AddMessage2Log($strError_tmp, "CUCDF");
			$strError .= $strError_tmp;
			return False;
		}
		else
			return True;
	}


	/** Рекурсивное удаление $path **/
	function __DeleteDirFilesEx($path)
	{
		if (!file_exists($path))
			return False;

		if (is_file($path))
		{
			@unlink($path);
			return True;
		}

		if ($handle = @opendir($path))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..") continue;

				if (is_dir($path."/".$file))
				{
					CUpdateClientPartner::__DeleteDirFilesEx($path."/".$file);
				}
				else
				{
					@unlink($path."/".$file);
				}
			}
		}
		@closedir($handle);
		@rmdir($path);
		return True;
	}

	function __bxstrrpos($haystack, $needle)
	{
		$index = strpos(strrev($haystack), strrev($needle));
		if($index === false)
			return false;
		$index = strlen($haystack) - strlen($needle) - $index;
		return $index;
	}

	/** Возвращает экземпляр класса-инсталятора модуля по абсолютному пути $path **/
	function __GetModuleInfo($path)
	{
		$arModuleVersion = array();
//		include($path."/install/version.php");
//		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
//			return $arModuleVersion;

		include_once($path."/install/index.php");

		$arr = explode("/", $path);
		$i = array_search("modules", $arr);
		$class_name = $arr[$i+1];

		$class_name = str_replace(".", "_", $class_name);
		$cls = new $class_name;

		return array(
			"VERSION" => $cls->MODULE_VERSION,
			"VERSION_DATE" => $cls->MODULE_VERSION_DATE,
			"IS_DEMO" => ((defined($class_name."_DEMO") && constant($class_name."_DEMO")) ? "Y" : "N")
		);
	}

	function __GetMicroTime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}

?>