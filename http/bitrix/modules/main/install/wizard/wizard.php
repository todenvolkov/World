<?
//@define("PRE_LANGUAGE_ID","en");
//@define("PRE_INSTALL_CHARSET","iso-8859-1");
//@define("PRE_LANGUAGE_ID","ru");
//@define("PRE_INSTALL_CHARSET","windows-1251");
@define("PRE_LANGUAGE_ID","de");
@define("PRE_INSTALL_CHARSET","iso-8859-15");

//Disable statistics
define("STOP_STATISTICS", true);
define("NO_AGENT_STATISTIC", true);

@set_time_limit(3600);
@ignore_user_abort(true);

define("BX_PRODUCT_INSTALLATION", true);

if (defined("DEBUG_MODE"))
	error_reporting(E_ALL);
else
	error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);

if (isset($_REQUEST["clear_db"]))
	setcookie("clear_db", $_REQUEST["clear_db"], time()+3600);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php"); //Wizard API
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/version.php"); //Sitemanager version
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard/template.php"); //Wizard template
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard/utils.php"); //Wizard utils

@set_time_limit(3600);

//wizard customization file
$bxProductConfig = array();
if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php"))
	include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php");

//require to register trial and to get trial license key
if(isset($bxProductConfig["saas"]))
{
	define("TRIAL_RENT_VERSION", true);
	define("TRIAL_RENT_VERSION_MAX_USERS", $bxProductConfig["saas"]["max_users"]);
}

//Get Wizard Settings
$GLOBALS["arWizardConfig"] = BXInstallServices::GetWizardsSettings();

if($GLOBALS["arWizardConfig"]["LANGUAGE_ID"])
	define("LANGUAGE_ID", $GLOBALS["arWizardConfig"]["LANGUAGE_ID"]);
else
	define("LANGUAGE_ID", PRE_LANGUAGE_ID);

if($GLOBALS["arWizardConfig"]["INSTALL_CHARSET"])
	define("INSTALL_CHARSET", $GLOBALS["arWizardConfig"]["INSTALL_CHARSET"]);
else
	define("INSTALL_CHARSET", PRE_INSTALL_CHARSET);

//Lang files
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/en/install.php");
if (LANGUAGE_ID != "en" && file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".LANGUAGE_ID."/install.php"))
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".LANGUAGE_ID."/install.php");

//Magic quotes
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");
UnQuoteAll();

bx_accelerator_reset();

class WelcomeStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("welcome");
		$this->SetNextStep("agreement");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetTitle(InstallGetMessage("INS_STEP1_TITLE"));
	}

	function ShowStep()
	{
		global $arWizardConfig;

		//wizard customization file
		$bxProductConfig = array();
		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php"))
			include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php");

		if(isset($bxProductConfig["product_wizard"]["welcome_text"]))
			$this->content .= $bxProductConfig["product_wizard"]["welcome_text"];
		else
			$this->content .= (isset($arWizardConfig["welcomeText"]) ? $arWizardConfig["welcomeText"] : InstallGetMessage("FIRST_PAGE"));
		$wizard =& $this->GetWizard();
	}
}

class AgreementStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("agreement");
		$this->SetPrevStep("welcome");
		$this->SetNextStep("select_database");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetPrevCaption(InstallGetMessage("PREVIOUS_BUTTON"));
		$this->SetTitle(InstallGetMessage("INS_STEP2_TITLE"));
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();

		if ($wizard->IsPrevButtonClick())
			return;

		$agreeLicense = $wizard->GetVar("agree_license");
		if ($agreeLicense !== "Y")
			$this->SetError(InstallGetMessage("ERR_AGREE_LICENSE"), "agree_license");
	}

	function ShowStep()
	{
		$this->content = '<iframe name="license_text" src="/license.html" width="100%" height="250" border="0" frameBorder="1" scrolling="yes"></iframe><br /><br />';
		$this->content .= $this->ShowCheckboxField("agree_license", "Y", Array("id" => "agree_license_id", "tabindex" => "1"));
		$this->content .= '&nbsp;<label for="agree_license_id">'.InstallGetMessage("LICENSE_AGREE_PROMT").'</label>';

		$wizard =& $this->GetWizard();
		$this->content .= '<script type="text/javascript">setTimeout(function() {document.getElementById("agree_license_id").focus();}, 500);</script>';
	}

}

class AgreementStep4VM extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("agreement");
//		$this->SetPrevStep("welcome");
		$this->SetNextStep("check_license_key");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetPrevCaption(InstallGetMessage("PREVIOUS_BUTTON"));
		$this->SetTitle(InstallGetMessage("INS_STEP2_TITLE"));
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();

		if ($wizard->IsPrevButtonClick())
			return;

		$agreeLicense = $wizard->GetVar("agree_license");
		if ($agreeLicense !== "Y")
			$this->SetError(InstallGetMessage("ERR_AGREE_LICENSE"), "agree_license");
	}

	function ShowStep()
	{
		$this->content = '<iframe name="license_text" src="/license.html" width="100%" height="250" border="0" frameBorder="1" scrolling="yes"></iframe><br /><br />';
		$this->content .= $this->ShowCheckboxField("agree_license", "Y", Array("id" => "agree_license_id", "tabindex" => "1"));
		$this->content .= '&nbsp;<label for="agree_license_id">'.InstallGetMessage("LICENSE_AGREE_PROMT").'</label>';

		$wizard =& $this->GetWizard();
		$this->content .= '<script type="text/javascript">setTimeout(function() {document.getElementById("agree_license_id").focus();}, 500);</script>';
	}

}

class DBTypeStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("select_database");
		$this->SetPrevStep("agreement");
		$this->SetNextStep("requirements");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetPrevCaption(InstallGetMessage("PREVIOUS_BUTTON"));

		$arDBTypes = BXInstallServices::GetDBTypes();
		if (count($arDBTypes) > 1)
			$this->SetTitle(InstallGetMessage("INS_DB_SELECTION"));
		else
			$this->SetTitle(InstallGetMessage("INS_LICENSE_HEAD"));

		$wizard =& $this->GetWizard();

		if (defined("TRIAL_VERSION") || defined("TRIAL_RENT_VERSION"))
		{
			$wizard->SetDefaultVar("lic_key_variant", "Y");
		}

		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/license_key.php'))
		{
			$LICENSE_KEY = '';
			include($_SERVER['DOCUMENT_ROOT'].'/bitrix/license_key.php');
			$wizard->SetDefaultVar("license", $LICENSE_KEY);
		}

		$defaultDbType = "mysql";
		foreach ($arDBTypes as $dbType => $active)
		{
			$defaultDbType = $dbType;
			if ($active)
				break;
		}

		$wizard->SetDefaultVar("dbType", $defaultDbType);
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();

		if ($wizard->IsPrevButtonClick())
			return;

		$dbType = $wizard->GetVar("dbType");
		$arDBTypes = BXInstallServices::GetDBTypes();

		if (count($arDBTypes) > 1 && (!array_key_exists($dbType, $arDBTypes) || $arDBTypes[$dbType] === false))
			$this->SetError(InstallGetMessage("ERR_NO_DATABSEL"), "dbType");

		$licenseKey = $wizard->GetVar("license");

		//if (!defined("TRIAL_VERSION") && !defined("TRIAL_RENT_VERSION") && function_exists("preg_match") && !preg_match('/[A-Z0-9]{3}-[A-Z]{2}-?[A-Z0-9]{12,18}/i', $licenseKey))
		//	$this->SetError(InstallGetMessage("BAD_LICENSE_KEY"), "license");

		if ($dbType == "mssql")
			$wizard->SetVar("utf8", "N");

		if(defined("TRIAL_VERSION") || defined("TRIAL_RENT_VERSION"))
		{
			$lic_key_variant = $wizard->GetVar("lic_key_variant");

			if((defined("TRIAL_RENT_VERSION") || (defined("TRIAL_VERSION") && $lic_key_variant == "Y")) && strlen($licenseKey) <= 0)
			{
				$lic_key_user_surname = $wizard->GetVar("user_surname");
				$lic_key_user_name = $wizard->GetVar("user_name");
				$lic_key_email = $wizard->GetVar("email");

				$bError = false;
				if(trim($lic_key_user_name) == '')
				{
					$this->SetError(InstallGetMessage("ACT_KEY_BAD_NAME"), "user_name");
					$bError = true;
				}
				if(trim($lic_key_user_surname) == '')
				{
					$this->SetError(InstallGetMessage("ACT_KEY_BAD_LAST_NAME"), "user_surname");
					$bError = true;
				}
				if(trim($lic_key_email) == '' || !check_email($lic_key_email))
				{
					$this->SetError(InstallGetMessage("ACT_KEY_BAD_EMAIL"), "email");
					$bError = true;
				}

				if(!$bError)
				{
					$lic_site = $_SERVER["HTTP_HOST"];
					if(strlen($lic_site) <= 0)
						$lic_site = "localhost";
					$lic_db = $dbType;

					$arClientModules = Array();
					$handle = @opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules");
					if ($handle)
					{
						while (false !== ($dir = readdir($handle)))
						{
							if (is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$dir)
								&& $dir!="." && $dir!="..")
							{
								$module_dir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$dir;
								if (file_exists($module_dir."/install/index.php"))
								{
									$arClientModules[] = $dir;
								}
							}
						}
						closedir($handle);
					}

					$lic_edition = serialize($arClientModules);

					if (defined("INSTALL_CHARSET") && strlen(INSTALL_CHARSET) > 0)
						$charset = INSTALL_CHARSET;
					else
						$charset = "windows-1251";

					if(LANGUAGE_ID == "ru")
						$host = "www.1c-bitrix.ru";
					else
						$host = "www.bitrixsoft.com";

					$path = "/bsm_register_key.php";
					$port = 80;
					$query = "sur_name=$lic_key_user_surname&first_name=$lic_key_user_name&email=$lic_key_email&site=$lic_site&modules=".urlencode($lic_edition)."&db=$dbType&lang=".LANGUAGE_ID."&bx=Y&max_users=".TRIAL_RENT_VERSION_MAX_USERS;
					
					if(defined("install_license_type"))
						$query .= "&cp_type=".install_license_type;
					if(defined("install_edition"))
						$query .= "&edition=".install_edition;
						
					$fp = @fsockopen("$host", "$port", $errnum, $errstr, 30);
					if ($fp)
					{
						fputs($fp, "POST {$path} HTTP/1.1\r\n");
						fputs($fp, "Host: {$host}\r\n");
						fputs($fp, "Content-type: application/x-www-form-urlencoded; charset=\"".$charset."\"\r\n");
						fputs($fp, "User-Agent: bitrixKeyReq\r\n");
						fputs($fp, "Content-length: ".(function_exists("mb_strlen")? mb_strlen($query, 'latin1'): strlen($query))."\r\n");
						fputs($fp, "Connection: close\r\n\r\n");
						fputs($fp, $query."\r\n\r\n");
						$page_content = "";
						$headersEnded = 0;
						while(!feof($fp))
						{
							$returned_data = fgets($fp, 128);
							if($returned_data=="\r\n")
							{
								$headersEnded = 1;
							}

							if($headersEnded==1)
							{
								$page_content .= htmlspecialchars($returned_data);
							}
						}
						fclose($fp);
					}
					$arContent = explode("\n", $page_content);

					$bEr = false;
					$bOk = false;
					$key = "";
					foreach($arContent as $v)
					{
						if($v == "ERROR")
							$bEr = true;
						elseif($v == "OK")
							$bOk = true;

						if(strlen($v) > 10)
							$key = trim($v);
					}

					if($bOk && strlen($key) >0)
					{
						$wizard->SetVar("license", $key);
					}
					elseif(defined("TRIAL_RENT_VERSION"))
						$this->SetError(InstallGetMessage("ACT_KEY_REQUEST_ERROR"), "email");
				}
			}
		}
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		if (BXInstallServices::SetSession())
			$this->content .= '<input type="hidden" name="'.ini_get("session.name").'" value="'.session_id().'" />';

		$this->content .= '
		<table border="0" class="data-table">
			<tr>
				<td colspan="2" class="header">'.InstallGetMessage("INS_LICENSE_HEAD").'</td>
			</tr>';

		if(!defined("TRIAL_VERSION") && !defined("TRIAL_RENT_VERSION"))
		{
			$this->content .= '<tr>
				<td nowrap align="right" width="40%" valign="top">
					<span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_LICENSE").'
				</td>
				<td width="60%" valign="top">'.$this->ShowInputField("text", "license", Array("size" => "30", "tabindex" => "1", "id" =>"license_id")).'
					<br>
					<small>'.InstallGetMessage("INS_LICENSE_NOTE_SOURCE").'<br></small>
				</td>
				</tr>';
		}
		else
		{
			$this->content .= '
			<script>
				function changeLicKey(val)
				{
					if(val)
					{
						document.getElementById("lic_key_activation").style.display = "block";
					}
					else
					{
						document.getElementById("lic_key_activation").style.display = "none";
					}
				}
			</script>

					';
			if(!defined("TRIAL_RENT_VERSION"))
				$this->content .= '<tr><td colspan="2">'.$this->ShowCheckboxField("lic_key_variant", "Y", Array("id" => "lic_key_variant", "onclick" => "javascript:changeLicKey(this.checked)")).'<label for="lic_key_variant">'.InstallGetMessage("ACT_KEY").'</label></td></tr>';


			$lic_key_variant = $wizard->GetVar("lic_key_variant", $useDefault = true);
			$this->content .= '
			</table>
			<div id="lic_key_activation">
			<table border="0" class="data-table" style="border-top:none;">
			<tr>
				<td align="right" width="40%" style="border-top:none;"><span style="color:red">*</span>&nbsp;'.InstallGetMessage("ACT_KEY_NAME").':</td>
				<td width="60%" valign="top" style="border-top:none;">'.$this->ShowInputField("text", "user_name", Array("size" => "30", "tabindex" => "4", "id" => "user_name")).'</td>
			</tr>
			<tr>
				<td align="right" width="40%" style="border-top:none;"><span style="color:red">*</span>&nbsp;'.InstallGetMessage("ACT_KEY_LAST_NAME").':</td>
				<td width="60%" valign="top" style="border-top:none;">'.$this->ShowInputField("text", "user_surname", Array("size" => "30", "tabindex" => "5", "id" => "user_surname")).'</td>
			</tr>
			<tr>
				<td align="right" width="40%" style="border-top:none;"><span style="color:red">*</span>&nbsp;Email:</td>
				<td width="60%" valign="top" style="border-top:none;">'.$this->ShowInputField("text", "email", Array("size" => "30", "tabindex" => "6", "id" => "email")).'</td>
			</tr>
			</table>
			</div>
			<script>
			changeLicKey('.(($lic_key_variant == "Y") ? 'true' : 'false').');
			</script>
			';
		}
		$this->content .= '<br /><table border="0" class="data-table">';

		$arDBTypes = BXInstallServices::GetDBTypes();

		if (count($arDBTypes) > 1)
		{
			$strDBTypes = "";
			foreach ($arDBTypes as $dbType => $active)
			{
				$arParams = ($active ? Array() : Array("disabled" => "disabled"));
				$strDBTypes .= $this->ShowRadioField("dbType", $dbType, Array("id" => "dbType_".$dbType, "onclick" => "NeedUTFSection(this.value)") + $arParams);

				if ($dbType == "mysql")
					$dbName = "MySQL";
				elseif ($dbType == "oracle")
					$dbName = "Oracle";
				elseif ($dbType == "mssql")
					$dbName = "Microsoft SQL Server (ODBC)";
				elseif ($dbType == "mssql_native")
					$dbName = "Microsoft SQL Server (Native)";

				$strDBTypes .= '<label for="'."dbType_".$dbType.'">&nbsp;'.$dbName.'</label><br>';
			}

			$this->content .= '
				<tr>
					<td colspan="2" class="header">'.InstallGetMessage("INS_DB_SELECTION").'</td>
				</tr>
				<tr>
					<td align="right" valign="top" width="40%">
						<span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_DB_PROMT").':<br><small>'.InstallGetMessage("INS_DB_PROMT_ALT").'<br></small>
					</td>
					<td valign="top" width="60%">
						'.$strDBTypes.'
						<small>'.InstallGetMessage("INS_DB_PROMT_HINT").'<br></small>
					</td>
				</tr>';
		}
		else
		{
			$wizard->SetVar("dbType", $wizard->GetDefaultVar("dbType"));
		}

		$dbType = $wizard->GetVar("dbType", $useDefault = true);
		$rowStyle = (($dbType == "mssql" || $dbType == "mssql_native")? ' style="display:none;"' : "");

		$this->content .= '
			<tr id="utf-row-one"'.$rowStyle.'>
				<td colspan="2" class="header">'.InstallGetMessage("INS_UTF_PARAMS").'</td>
			</tr>
			<tr id="utf-row-two"'.$rowStyle.'>
				<td colspan="2">
					'.$this->ShowCheckboxField("utf8", "Y", Array("id" => "utf8_inst")).'<label for="utf8_inst">&nbsp;'.InstallGetMessage("INSTALL_IN_UTF8").'</label>
				</td>
			</tr>
			</table>
			<script type="text/javascript">
				setTimeout(function() {
					if(document.getElementById("license_id"))
					{
						document.getElementById("license_id").focus();
					}
					else
					{
						if(document.getElementById("lic_key_variant"))
							document.getElementById("lic_key_variant").focus();
					}
				}, 500);
			</script>
		';
	}

}

class RequirementStep extends CWizardStep
{
	var $memoryMin = 32;
	var $memoryRecommend = 64;
	var $diskSizeMin = 50;

	var $phpMinVersion = "5.0.0";
	var $apacheMinVersion = "1.3";
	var $iisMinVersion = "5.0.0";

	var $arCheckFiles = Array();

	function InitStep()
	{
		$this->SetStepID("requirements");
		$this->SetNextStep("create_database");
		$this->SetPrevStep("select_database");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetPrevCaption(InstallGetMessage("PREVIOUS_BUTTON"));
		$this->SetTitle(InstallGetMessage("INS_STEP4_TITLE"));

		$wizard =& $this->GetWizard();
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();

		if ($wizard->IsPrevButtonClick())
			return;

		$dbType = $wizard->GetVar("dbType");
		$utf8 = $wizard->GetVar("utf8");

		if ($utf8 == "Y" && !BXInstallServices::IsUTF8Support())
		{
			$this->SetError(InstallGetMessage("INST_UTF8_RECOMENDATION"));
			return;
		}
		elseif ($utf8 != "Y" && extension_loaded("mbstring") && strtoupper(ini_get("mbstring.internal_encoding")) == "UTF-8" && intval(ini_get("mbstring.func_overload")) > 0)
		{
			$this->SetError(InstallGetMessage("ERR_MBSTRING_EXISTS"));
			return;
		}
		elseif ($dbType == "oracle" && $utf8 == "Y" && strtoupper(substr(PHP_OS,0,3)) != "WIN" && strtolower(substr(getenv("NLS_LANG"), -5)) != ".utf8")
		{
			$this->SetError(InstallGetMessage("INST_ORACLE_NLS_LANG_ERROR"));
			return false;
		}

		if (!BXInstallServices::CheckSession())
		{
			$this->SetError(InstallGetMessage("INST_SESSION_NOT_SUPPORT"));
			return false;
		}

		if (!$this->CheckRequirements($dbType))
			return;
	}

	function CheckRequirements($dbType)
	{
		if ($this->CheckServerVersion($serverName, $serverVersion, $serverMinVersion) === false)
		{
			$this->SetError(InstallGetMessage("SC_WEBSERVER_VER_ER"));
			return false;
		}

		if (!$this->CheckPHPVersion())
		{
			$this->SetError(InstallGetMessage("SC_PHP_VER_ER"));
			return false;
		}

		if ($this->GetPHPSetting("safe_mode")=="ON")
		{
			$this->SetError(InstallGetMessage("SC_SAFE_MODE_ER"));
			return false;
		}

/*
		if ($this->GetPHPSetting("allow_call_time_pass_reference") != "ON")
		{
			$this->SetError(InstallGetMessage("INST_ALLOW_CALL_REFERENCE"));
			return false;
		}
*/

		$arDBTypes = BXInstallServices::GetDBTypes();
		if (!array_key_exists($dbType, $arDBTypes) || $arDBTypes[$dbType] === false)
		{
			if ($dbType == "mssql")
				$errorCode = "SC_ERR_NO_ODBC";
			elseif ($dbType == "oracle")
				$errorCode = "SC_NO_ORA_LIB_ER";
			else
				$errorCode = "SC_NO_MYS_LIB_ER";

			$this->SetError(InstallGetMessage($errorCode));
			return false;
		}

		if (!function_exists("eregi"))
		{
			$this->SetError(InstallGetMessage("SC_NO_EREG_LIB_ER"));
			return false;
		}

		if (!function_exists("preg_match"))
		{
			$this->SetError(InstallGetMessage("SC_NO_PERE_LIB_ER"));
			return false;
		}

		if (!$this->CheckFileAccess())
		{
			$files = "";
			foreach ($this->arCheckFiles as $arFile)
				if (!$arFile["ACCESS"])
					$files .= "<br />&nbsp;".$arFile["PATH"];

			$this->SetError(InstallGetMessage("INST_ERROR_ACCESS_FILES").$files);
			return false;
		}

		return true;
	}

	function GetPHPSetting($value)
	{
		return (ini_get($value) == "1" || strtoupper(ini_get($value)) == "ON" ? "ON" : "OFF");
	}

	function ShowResult($resultText, $type = "OK")
	{
		if (strlen($resultText) <= 0)
			return "";

		if (strtoupper($type) == "ERROR" || $type === false)
			return "<b><span style=\"color:red\">".$resultText."</span></b>";
		elseif (strtoupper($type) == "OK" || $type === true)
			return "<b><span style=\"color:green\">".$resultText."</span></b>";
		elseif (strtoupper($type) == "NOTE" || strtoupper($type) == "N")
			return "<b><span style=\"color:black\">".$resultText."</span></b>";
	}

	function CheckServerVersion(&$serverName, &$serverVersion, &$serverMinVersion)
	{
		$serverSoftware = $_SERVER["SERVER_SOFTWARE"];
		if (strlen($serverSoftware)<=0)
			$serverSoftware = $_SERVER["SERVER_SIGNATURE"];
		$serverSoftware = trim($serverSoftware);

		$serverName = "";
		$serverVersion = "";
		$serverMinVersion = "";

		if (!function_exists("preg_match") || !preg_match("#^([a-zA-Z-]+).*?([\d]+\.[\d]+(\.[\d]+)?)#i", $serverSoftware, $arMatch))
			return null;

		$serverName = $arMatch[1];
		$serverVersion = $arMatch[2];

		if (strtoupper($serverName)=="APACHE")
		{
			$serverMinVersion = $this->apacheMinVersion;
			return BXInstallServices::VersionCompare($serverVersion, $this->apacheMinVersion);
		}
		elseif (strtoupper($serverName)=="MICROSOFT-IIS")
		{
			$serverMinVersion = $this->iisMinVersion;
			return BXInstallServices::VersionCompare($serverVersion, $this->iisMinVersion);
		}

		return null;
	}

	function CheckPHPVersion()
	{
		return BXInstallServices::VersionCompare(phpversion(), $this->phpMinVersion);
	}

	function CheckFileAccess()
	{
		$this->arCheckFiles = Array(
			Array("PATH" => $_SERVER["DOCUMENT_ROOT"], "DESC" => InstallGetMessage("SC_DISK_PUBLIC"), "RESULT" => "", "ACCESS" => true),
			Array("PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix", "DESC" => InstallGetMessage("SC_DISK_BITRIX"), "RESULT" => "", "ACCESS" => true),
			Array("PATH" => $_SERVER["DOCUMENT_ROOT"]."/index.php", "DESC" => InstallGetMessage("SC_FILE"), "RESULT" => "", "ACCESS" => true),
			Array("PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules", "DESC" => InstallGetMessage("SC_CATALOG"), "RESULT" => "", "ACCESS" => true),
		);

		$success = true;
		foreach ($this->arCheckFiles as $index => $arFile)
		{
			$readable = is_readable($arFile["PATH"]);
			$writeable = is_writeable($arFile["PATH"]);

			if ($readable && $writeable)
			{
				$this->arCheckFiles[$index]["RESULT"] = $this->ShowResult(InstallGetMessage("SC_DISK_AVAIL_READ_WRITE1"), "OK");
				continue;
			}

			$success = false;
			$this->arCheckFiles[$index]["ACCESS"] = false;

			if (!$writeable)
				$this->arCheckFiles[$index]["RESULT"] .= $this->ShowResult(InstallGetMessage("SC_CAN_NOT_WRITE"), "ERROR");

			if (!$writeable && !$readable)
				$this->arCheckFiles[$index]["RESULT"] .= " ".InstallGetMessage("SC_AND")." ";

			if (!$readable)
				$this->arCheckFiles[$index]["RESULT"] .= $this->ShowResult(InstallGetMessage("SC_CAN_NOT_READ"), "ERROR");
		}

		if ($success === false)
			return false;

		return $this->CreateTemporaryFiles();
	}

	function CreateTemporaryFiles()
	{
		$htaccessTest = $_SERVER["DOCUMENT_ROOT"]."/bitrix/httest";
		$rootTest = $_SERVER["DOCUMENT_ROOT"]."/bxtest";

		if (!file_exists($htaccessTest) && @mkdir($htaccessTest, 0770) === false)
		{
			$this->arCheckFiles[]= Array(
				"PATH" => $htaccessTest,
				"DESC" => InstallGetMessage("SC_CATALOG"),
				"RESULT" => $this->ShowResult(InstallGetMessage("SC_CAN_NOT_WRITE"), "ERROR"),
				"ACCESS" => false
			);
			return false;
		}

		if (!file_exists($rootTest) && @mkdir($rootTest, 0770) === false)
		{
			$this->arCheckFiles[]= Array(
				"PATH" => $rootTest,
				"ACCESS" => false,
				"RESULT" => $this->ShowResult(InstallGetMessage("SC_CAN_NOT_WRITE"), "ERROR"),
				"DESC" => InstallGetMessage("SC_CATALOG")
			);
			return false;
		}

		$arFiles = Array(
			Array(
				"PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/httest/.htaccess",
				"CONTENT" => 'ErrorDocument 404 /bitrix/httest/404.php

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^.+\.php$ /bitrix/httest/404.php
</IfModule>',
			),
			Array(
				"PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/httest/404.php",
				"CONTENT" => "<"."?\n".
							"$"."cgi = (stristr(php_sapi_name(), \"cgi\") !== false);\n".
							"$"."fastCGI = ("."$"."cgi && stristr("."$"."_SERVER[\"SERVER_SOFTWARE\"], \"Microsoft-IIS\") !== false);\n".
							"if ("."$"."cgi && !"."$"."fastCGI)\n".
							"	header(\"Status: 200 OK\");\n".
							"else\n".
							"	header(\"HTTP/1.0 200 OK\");\n".
							"echo \"SUCCESS\";\n".
							"?".">",
			),
			Array(
				"PATH" => $_SERVER["DOCUMENT_ROOT"]."/bxtest/test.php",
				"CONTENT" => "test",
			),
		);

		foreach ($arFiles as $arFile)
		{
			if (!$fp = @fopen($arFile["PATH"], "wb"))
			{
				$this->arCheckFiles[]= Array("PATH" => $arFile["PATH"], "ACCESS" => false, "SKIP" => true);
				return false;
			}

			if (!fwrite($fp, $arFile["CONTENT"]))
			{
				$this->arCheckFiles[]= Array("PATH" => $arFile["PATH"], "ACCESS" => false, "SKIP" => true);
				return false;
			}
		}

		return true;
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();

		if (BXInstallServices::CheckSession())
			$this->content .= '<input type="hidden" name="'.ini_get("session.name").'" value="'.session_id().'" />';

		$this->content .= '<h3>'.InstallGetMessage("SC_SUBTITLE_REQUIED").'</h3>'.InstallGetMessage("SC_SUBTITLE_REQUIED_DESC").'<br><br>';

		$this->content .= '
		<table border="0" class="data-table">
			<tr>
				<td class="header">'.InstallGetMessage("SC_PARAM").'</td>
				<td class="header">'.InstallGetMessage("SC_REQUIED").'</td>
				<td class="header">'.InstallGetMessage("SC_CURRENT").'</td>
			</tr>';

		//Web server version
		$success = $this->CheckServerVersion($serverName, $serverVersion, $serverMinVersion);
		$this->content .= '
		<tr>
			<td valign="top">
					'.str_replace("#SERVER#", ((strlen($serverName)>0) ? $serverName : InstallGetMessage("SC_UNKNOWN")), InstallGetMessage("SC_SERVER_VERS")).'
			</td>
			<td valign="top">
				'.(strlen($serverMinVersion)>0 ? str_replace("#VER#", $serverMinVersion, InstallGetMessage("SC_VER_VILKA1")) : "").'
			</td>
			<td valign="top">
				'.($success !== null ? $this->ShowResult($serverVersion, $success) : $this->ShowResult(InstallGetMessage("SC_UNKNOWN1"), "ERROR")).'
			</td>
		</tr>';

		//PHP version
		$success = $this->CheckPHPVersion();
		$this->content .= '
		<tr>
			<td valign="top">'.InstallGetMessage("SC_PHP_VERS").'</td>
			<td valign="top">
					'.(strlen($this->phpMinVersion) > 0 ? str_replace("#VER#", $this->phpMinVersion, InstallGetMessage("SC_VER_VILKA1")) : "").'
			</td>
			<td valign="top">'.$this->ShowResult(phpversion(), $success).'</td>
		</tr>';

		//Save mode
		$this->content .= '
		<tr>
			<td colspan="3"><b>'.InstallGetMessage("SC_PHP_SETTINGS").'</b></td>
		</tr>';
/*
		<tr>
			<td valign="top">&nbsp; - allow_call_time_pass_reference</td>
			<td valign="top">'.InstallGetMessage("SC_TURN_ON").'</td>
			<td valign="top">
					'.($this->GetPHPSetting("allow_call_time_pass_reference")=="ON" ?
						$this->ShowResult(InstallGetMessage("SC_TURN_ON"), "OK") :
						$this->ShowResult(InstallGetMessage("SC_TURN_OFF"), "ERROR")
					).'
			</td>
		</tr>
*/
		$this->content .= '
		<tr>
			<td valign="top">&nbsp; - safe mode</td>
			<td valign="top">'.InstallGetMessage("SC_TURN_OFF").'</td>
			<td valign="top">
					'.($this->GetPHPSetting("safe_mode")=="ON" ?
						$this->ShowResult(InstallGetMessage("SC_TURN_ON"), "ERROR") :
						$this->ShowResult(InstallGetMessage("SC_TURN_OFF"), "OK")
					).'
			</td>
		</tr>';

		//Database support in PHP
		$dbType = $wizard->GetVar("dbType");
		$arDBTypes = BXInstallServices::GetDBTypes();
		$success = (array_key_exists($dbType, $arDBTypes) && $arDBTypes[$dbType] === true);

		if ($dbType == "mysql")
			$library = '<a href="http://www.php.net/manual/en/ref.mysql.php" target="_blank">'.InstallGetMessage("SC_MOD_MYSQL").'</a>';
		elseif ($dbType == "mssql")
			$library = '<a href="http://www.php.net/manual/en/ref.uodbc.php" target="_blank">'.InstallGetMessage("SC_ODBC_FUNCTIONS").'</a>';
		elseif ($dbType == "mssql_native")
			$library = '<a href="http://msdn.microsoft.com/en-us/library/ee229551(v=SQL.10).aspx" target="_blank">SQL Server Native</a>';
		elseif ($dbType == "oracle")
			$library = '<a href="http://www.php.net/manual/en/ref.oci8.php" target="_blank">'.InstallGetMessage("SC_MOD_ORACLE").'</a>';

		$this->content .= '
		<tr>
			<td colspan="3"><b>'.InstallGetMessage("SC_REQUIED_PHP_MODS").'</b></td>
		</tr>
		<tr>
			<td valign="top">&nbsp; - '.$library.'</td>
			<td valign="top">'.InstallGetMessage("SC_SETTED").'</td>
			<td valign="top">
			'.(
				$success ?
					$this->ShowResult(InstallGetMessage("SC_SETTED"), "OK") :
					$this->ShowResult(InstallGetMessage("SC_NOT_SETTED"), "ERROR")
				).'</td>
		</tr>';

		$this->content .= '
		<tr>
			<td valign="top">
					&nbsp; - <a href="http://www.php.net/manual/en/ref.regex.php" target="_blank">'.InstallGetMessage("SC_MOD_POSIX_REG").'</a>
			</td>
			<td valign="top">'.InstallGetMessage("SC_SETTED").'</td>
			<td valign="top">
				'.(
					function_exists("eregi") ?
						$this->ShowResult(InstallGetMessage("SC_SETTED"), "OK") :
						$this->ShowResult(InstallGetMessage("SC_NOT_SETTED"), "ERROR")
					).'
			</td>
		</tr>
		<tr>
			<td valign="top">
				&nbsp; - <a href="http://www.php.net/manual/en/ref.pcre.php" target="_blank">'.InstallGetMessage("SC_MOD_PERL_REG").'</a>
			</td>
			<td valign="top">'.InstallGetMessage("SC_SETTED").'</td>
			<td valign="top">
					'.(function_exists("preg_match") ? $this->ShowResult(InstallGetMessage("SC_SETTED"), "OK") : $this->ShowResult(InstallGetMessage("SC_NOT_SETTED"), "ERROR")).'
			</td>
		</tr>';

		if (!BXInstallServices::CheckSession())
		{
			$this->content .= '
				<tr>
					<td valign="top">
							&nbsp; - <a href="http://www.php.net/manual/en/book.session.php" target="_blank">'.InstallGetMessage("INST_SESSION_SUPPORT").'</a>
					</td>
					<td valign="top">'.InstallGetMessage("INST_YES").'</td>
					<td valign="top">'.$this->ShowResult(InstallGetMessage("INST_NO").". ".InstallGetMessage("INST_SESSION_NOT_SUPPORT"),"ERROR").'</td>
				</tr>';
		}


		//UTF-8
		$utf8 = $wizard->GetVar("utf8");
		$utf8 = ($utf8 == "Y");
		if ($dbType != "mssql" && $dbType != "mssql_native" && $utf8)
		{
			$this->content .= '
				<tr>
					<td colspan="3"><b>'.InstallGetMessage("UTF8_SUPPORT").'</b></td>
				</tr>
				<tr>
					<td valign="top">
						&nbsp; - <a href="http://www.php.net/manual/en/ref.mbstring.php" target="_blank">Multibyte String</a>
					</td>
					<td valign="top">'.InstallGetMessage("SC_SETTED").'</td>
					<td valign="top">
						'.(extension_loaded("mbstring") ? $this->ShowResult(InstallGetMessage("SC_SETTED"), "OK") : $this->ShowResult(InstallGetMessage("SC_NOT_SETTED"), "ERROR")).'
					</td>
				</tr>';

			$funcOverload = ini_get("mbstring.func_overload");
			if ($funcOverload == "")
				$funcOverload = $this->ShowResult(InstallGetMessage("SC_NOT_SETTED"), "ERROR");
			else
				$funcOverload = $this->ShowResult($funcOverload, $funcOverload >= 2);

			$this->content .= '
				<tr>
					<td valign="top">&nbsp; - mbstring.func_overload</td>
					<td valign="top">&gt;=2</td>
					<td valign="top">'.$funcOverload.'</td>
				</tr>';

			$encoding = strtoupper(ini_get("mbstring.internal_encoding"));
			if ($encoding == "")
				$encoding = $this->ShowResult(InstallGetMessage("SC_NOT_SETTED"), "ERROR");
			else
				$encoding = $this->ShowResult($encoding, $encoding == "UTF-8");

			$this->content .= '
				<tr>
					<td valign="top">&nbsp; - mbstring.internal_encoding</td>
					<td valign="top">UTF-8</td>
					<td valign="top">'.$encoding.'</td>
				</tr>';
		}
		elseif (/*$dbType != "mssql" &&*/ !$utf8 && extension_loaded("mbstring") && intval(ini_get("mbstring.func_overload")) > 0  && strtoupper(ini_get("mbstring.internal_encoding")) == "UTF-8")
		{
			$this->content .= '
				<tr>
					<td valign="top">&nbsp; - mbstring.func_overload</td>
					<td valign="top">0</td>
					<td valign="top">'.$this->ShowResult(ini_get("mbstring.func_overload")." (".ini_get("mbstring.internal_encoding").")", "ERROR").'</td>
				</tr>';
		}

		if ($dbType == "oracle" && $utf8 && strtoupper(substr(PHP_OS,0,3)) != "WIN" && strtolower(substr(getenv("NLS_LANG"), -5)) != ".utf8")
		{
			$this->content .= '
				<tr>
					<td valign="top">&nbsp; - <a href="http://download.oracle.com/docs/cd/B19306_01/server.102/b14225/ch3globenv.htm#sthref195" target="_blank">NLS_LANG</a></td>
					<td valign="top">&lt;'.InstallGetMessage("NLS_LANGUAGE_TERRITORY").'&gt;.utf8</td>
					<td valign="top">'.$this->ShowResult(strlen(getenv("NLS_LANG")) > 0 ? getenv("NLS_LANG") : InstallGetMessage("SC_NOT_SETTED"), "ERROR").'</td>
				</tr>';
		}

		$this->content .= '</table>';

		//File and folder permissons
		$this->content .= '<h3>'.InstallGetMessage("SC_SUBTITLE_DISK").'</h3>'.InstallGetMessage("SC_SUBTITLE_DISK_DESC").'<br><br>';
		$this->content .= '
		<table border="0" class="data-table">
		<tr>
			<td class="header">'.InstallGetMessage("SC_PARAM").'</td>
			<td class="header">'.InstallGetMessage("SC_VALUE").'</td>
		</tr>';

		$this->CheckFileAccess();
		foreach ($this->arCheckFiles as $arFile)
		{
			if (isset($arFile["SKIP"]))
				continue;

			$this->content .= '
			<tr>
				<td valign="top">'.$arFile["DESC"].' <i>'.$arFile["PATH"].'</i></td>
				<td valign="top">'.$arFile["RESULT"].'</td>
			</tr>';
		}

		$this->content .= '</table>';

		//Recommend
		$this->content .= '<h3>'.InstallGetMessage("SC_SUBTITLE_RECOMMEND").'</h3>'.InstallGetMessage("SC_SUBTITLE_RECOMMEND_DESC").'<br><br>';
		$this->content .= '
		<table border="0" class="data-table">
			<tr>
				<td class="header">'.InstallGetMessage("SC_PARAM").'</td>
				<td class="header">'.InstallGetMessage("SC_RECOMMEND").'</td>
				<td class="header">'.InstallGetMessage("SC_CURRENT").'</td>
			</tr>';

		if(strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache')!==false)
		{
			$this->content .= '
			<tr>
				<td valign="top">'.InstallGetMessage("SC_HTACCESS").'</td>
				<td valign="top">'.InstallGetMessage("SC_TURN_ON2").'</td>
				<td valign="top"><span id="httest">'.$this->ShowResult(InstallGetMessage("SC_TESTING"), "N").'</span>
				<script type="text/javascript">
					req = false;
					if(window.XMLHttpRequest)
					{
						try{req = new XMLHttpRequest();}
						catch(e) {req = false;}
					} else if(window.ActiveXObject)
					{
						try {req = new ActiveXObject("Msxml2.XMLHTTP");}
						catch(e)
						{
							try {req = new ActiveXObject("Microsoft.XMLHTTP");}
							catch(e) {req = false;}
						}
					}

					if (req)
					{
						req.onreadystatechange = processReqChange;
						req.open("GET", "/bitrix/httest/test_404.php?random=" + Math.random(), true);
						req.send("");
					}
					else
						document.getElementById("httest").innerHTML = \''.$this->ShowResult(InstallGetMessage("SC_HTERR"), "ERROR").'\';

					function processReqChange() {
						if (req.readyState == 4) {
							if (req.responseText == "SUCCESS") {
								res = \''.$this->ShowResult(InstallGetMessage("SC_TURN_ON2"), "OK").'\';
							} else {
								res = \''.$this->ShowResult(InstallGetMessage("SC_TURN_OFF2"), "ERROR").'\';
							}
							document.getElementById("httest").innerHTML = res;
						}
					}
				</script>
				</td>
			</tr>';
		}

		$freeSpace = @disk_free_space($_SERVER["DOCUMENT_ROOT"]);
		$freeSpace = $freeSpace * 1.0 / 1000000.0;
		$this->content .= '
		<tr>
			<td valign="top">'.InstallGetMessage("SC_AVAIL_DISK_SPACE").'</td>
			<td valign="top">
				'.(intval($this->diskSizeMin) > 0 ? str_replace("#SIZE#", $this->diskSizeMin, InstallGetMessage("SC_AVAIL_DISK_SPACE_SIZE")) : "").'&nbsp;
			</td>
			<td valign="top">
					'.($freeSpace > $this->diskSizeMin ? $this->ShowResult(round($freeSpace, 1)." Mb", "OK") : $this->ShowResult(round($freeSpace, 1)." Mb", "ERROR")).'
			</td>
		</tr>';

		$memoryLimit = ini_get('memory_limit');
		if (!$memoryLimit || strlen($memoryLimit)<=0)
			$memoryLimit = get_cfg_var('memory_limit');
		$memoryLimit = intval(trim($memoryLimit));

		if($memoryLimit>0 && $memoryLimit < $this->memoryMin)
		{
			@ini_set("memory_limit", "64M");
			$memoryLimit = intval(trim(ini_get('memory_limit')));
		}

		$recommendMemory = "";
		if (intval($this->memoryMin)>0)
			$recommendMemory .= str_replace("#SIZE#", $this->memoryMin, InstallGetMessage("SC_AVAIL_MEMORY_MIN"));
		if (intval($this->memoryMin)>0 && intval($this->memoryRecommend)>0)
			$recommendMemory .= ", ";
		if (intval($this->memoryRecommend)>0)
			$recommendMemory .= str_replace("#SIZE#", $this->memoryRecommend, InstallGetMessage("SC_AVAIL_MEMORY_REC"));

		$this->content .= '
		<tr>
			<td colspan="3"><b>'.InstallGetMessage("SC_RECOM_PHP_SETTINGS").'</b></td>
		</tr>
		<tr>
			<td valign="top">&nbsp; - '.InstallGetMessage("SC_AVAIL_MEMORY").'</td>
			<td valign="top">'.$recommendMemory.'</td>
			<td valign="top">
					'.($memoryLimit < $this->memoryMin ? $this->ShowResult($memoryLimit." Mb", "ERROR") : $this->ShowResult($memoryLimit." Mb", "OK")).'
			</td>
		</tr>';

		$this->content .= '
		<tr>
			<td valign="top">&nbsp; - '.InstallGetMessage("SC_ALLOW_UPLOAD").' (file_uploads)</td>
			<td valign="top">'.InstallGetMessage("SC_TURN_ON1").'</td>
			<td valign="top">
					'.($this->GetPHPSetting("file_uploads")=="ON" ? $this->ShowResult(InstallGetMessage("SC_TURN_ON1"), "OK") : $this->ShowResult(InstallGetMessage("SC_TURN_OFF1"), "ERROR")).'
			</td>
		</tr>
		<tr>
			<td valign="top">&nbsp; - '.InstallGetMessage("SC_SHOW_ERRORS").' (display_errors)</td>
			<td valign="top">'.InstallGetMessage("SC_TURN_ON1").'</td>
			<td valign="top">
					'.($this->GetPHPSetting("display_errors")=="ON" ? $this->ShowResult(InstallGetMessage("SC_TURN_ON1"), "OK") : $this->ShowResult(InstallGetMessage("SC_TURN_OFF1"), "ERROR")).'
			</td>
		</tr>
		<tr>
			<td valign="top">&nbsp; - '.InstallGetMessage("SC_magic_quotes_sybase").' (magic_quotes_sybase)</td>
			<td valign="top">'.InstallGetMessage("SC_TURN_OFF1").'</td>
			<td valign="top">
					'.($this->GetPHPSetting("magic_quotes_sybase")=="ON" ? $this->ShowResult(InstallGetMessage("SC_TURN_ON1"), "ERROR") : $this->ShowResult(InstallGetMessage("SC_TURN_OFF1"), "OK")).'
			</td>
		</tr>';

		/*
		$sessionInfo = "";
		$sessionPath = session_save_path();
		if (strlen($sessionPath) > 0)
		{
			$sessionInfo .= $this->ShowResult($sessionPath, "OK")." ";

			if (file_exists($sessionPath))
			{
				if (is_writable($sessionPath))
					$sessionInfo .= $this->ShowResult(InstallGetMessage("SC_CAN_WRITE"), "OK");
				else
					$sessionInfo .= $this->ShowResult(InstallGetMessage("SC_CAN_NOT_WRITE"), "NOTE");
			}
			else
				$sessionInfo .= $this->ShowResult(InstallGetMessage("SC_NOT_EXISTS"), "NOTE");
		}
		else
			$sessionInfo .= $this->ShowResult(InstallGetMessage("SC_NOT_SETTED"), "NOTE");

		$this->content .= '
		<tr>
			<td valign="top">&nbsp; - '.InstallGetMessage("SC_SESS_PATH").'</td>
			<td valign="top">'.InstallGetMessage("SC_SETTED").'</td>
			<td valign="top">'.$sessionInfo.'</td>
		</tr>';
		*/

		//Recommended extensions
		$this->content .= '
		<tr>
			<td colspan="3"><b>'.InstallGetMessage("SC_RECOM_PHP_MODULES").'</b></td>
		</tr>
		<tr>
			<td valign="top">
				&nbsp; - <a href="http://www.php.net/manual/en/ref.zlib.php" target="_blank">Zlib Compression</a>
			</td>
			<td valign="top">'.InstallGetMessage("SC_SETTED").'</td>
			<td valign="top">
					'.(extension_loaded("zlib") && function_exists("gzcompress") ? $this->ShowResult(InstallGetMessage("SC_SETTED"), "OK") : $this->ShowResult(InstallGetMessage("SC_NOT_SETTED"), "ERROR")).'
			</td>
		</tr>
		<tr>
			<td valign="top">
				&nbsp; - <a href="http://www.php.net/manual/en/ref.image.php" target="_blank">'.InstallGetMessage("SC_MOD_GD").'</a>
			</td>
			<td valign="top">'.InstallGetMessage("SC_SETTED").'</td>
			<td valign="top">
					'.(function_exists("imagecreate") ? $this->ShowResult(InstallGetMessage("SC_SETTED"), "OK") : $this->ShowResult(InstallGetMessage("SC_NOT_SETTED"), "ERROR")).'
			</td>
		</tr>
		<tr>
			<td valign="top">&nbsp; - <a href="http://www.freetype.org" target="_blank">Free Type Library</a></td>
			<td valign="top">'.InstallGetMessage("SC_SETTED").'</td>
			<td valign="top">
					'.(function_exists("imagettftext") ? $this->ShowResult(InstallGetMessage("SC_SETTED"), "OK") : $this->ShowResult(InstallGetMessage("SC_NOT_SETTED"), "ERROR")).'
			</td>
		</tr>';


		$this->content .='</table>';

		$this->content .= '<br /><br /><table class="data-table"><tr><td width="0%">'.InstallGetMessage("SC_NOTES1").'</td></tr></table>';
	}
}


class CreateDBStep extends CWizardStep
{
	var $dbType;
	var $DBSQLServerType;
	var $dbUser;
	var $dbPassword;
	var $dbHost;
	var $dbName;

	var $createDatabase;
	var $createUser;

	var $createCharset = false;
	var $createDBType;

	var $rootUser;
	var $rootPassword;

	var $filePermission;
	var $folderPermission;

	var $utf8;

	var $needCodePage = false;
	var $DB = null;
	var $sqlMode = false;

	function InitStep()
	{
		$this->SetStepID("create_database");
		$this->SetNextStep("create_modules");
		$this->SetPrevStep("requirements");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetPrevCaption(InstallGetMessage("PREVIOUS_BUTTON"));
		$this->SetTitle(InstallGetMessage("INS_STEP5_TITLE"));

		$wizard =& $this->GetWizard();

		$wizard->SetDefaultVars(Array(
			"folder_access_perms" => "0755",
			"file_access_perms" => "0644",
			"create_user" => "N",
			"create_database" => "N",
		));

		$dbType = $wizard->GetVar("dbType");

		if ($dbType != "oracle")
			$wizard->SetDefaultVar("database", "sitemanager");

		if ($dbType == "mysql")
			$wizard->SetDefaultVar("host", "localhost");
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();

		if ($wizard->IsPrevButtonClick())
			return;

		if($wizard->GetVar("dbType") == "mssql_native")
		{
			$this->DBSQLServerType = "NATIVE";
			$this->dbType = "mssql";
		}
		else
			$this->dbType = $wizard->GetVar("dbType");
		$this->dbUser = $wizard->GetVar("user");
		$this->dbPassword = $wizard->GetVar("password");
		$this->dbHost = $wizard->GetVar("host");
		$this->dbName = $wizard->GetVar("database");

		$this->createDatabase = $wizard->GetVar("create_database");
		$this->createDatabase = ($this->createDatabase && $this->createDatabase == "Y");

		$this->createUser = $wizard->GetVar("create_user");
		$this->createUser = ($this->createUser && $this->createUser == "Y");

		$this->createDBType = $wizard->GetVar("create_database_type");

		$this->rootUser = $wizard->GetVar("root_user");
		$this->rootPassword = $wizard->GetVar("root_password");

		$this->filePermission = intval($wizard->GetVar("file_access_perms"));
		$this->folderPermission = intval($wizard->GetVar("folder_access_perms"));

		//UTF-8
		$this->utf8 = $wizard->GetVar("utf8");
		$this->utf8 = ($this->utf8 && $this->utf8 == "Y" && BXInstallServices::IsUTF8Support() && $this->dbType != "mssql");

		// /bitrix/admin permissions
		BXInstallServices::CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", octdec($this->folderPermission));
		if(!is_readable($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/") || !is_writeable($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/"))
		{
			$this->SetError("Access denied: /bitrix/admin/");
			return;
		}

		// define.php
		$definePath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/define.php";
		if(file_exists($definePath))
		{
			if(!is_readable($definePath) || !is_writeable($definePath))
			{
				$this->SetError("Access denied: /bitrix/modules/main/admin/define.php");
				return;
			}
		}
		else
		{
			$sc = false;
			if($fp = @fopen($definePath, "wb"))
			{
				if(fwrite($fp, "test"))
					$sc = true;

				@fclose($fp);
				@unlink($definePath);
			}

			if(!$sc)
			{
				$this->SetError("Access denied: /bitrix/modules/main/admin/define.php");
				return;
			}
		}

		//Bad database type
		$arDBTypes = BXInstallServices::GetDBTypes();
		if(!array_key_exists($wizard->GetVar("dbType"), $arDBTypes) || $arDBTypes[$wizard->GetVar("dbType")] === false)
		{
			$this->SetError(InstallGetMessage("ERR_INTERNAL_NODEF"));
			return;
		}

		//Empty database user
		if (strlen($this->dbUser) <= 0)
		{
			$this->SetError(InstallGetMessage("ERR_NO_USER"), "user");
			return;
		}

		if ($this->dbType == "mysql" && !$this->CreateMySQL())
			return;
		elseif ($this->dbType == "mssql" && !$this->CreateMSSQL())
			return;
		elseif ($this->dbType == "oracle" && !$this->CreateOracle())
			return;

		if (!$this->CreateAfterConnect())
			return;

		if($this->dbType == "mssql" && $this->DBSQLServerType=="NATIVE")
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$this->dbType."/database_ms.php");
		else
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$this->dbType."/database.php");
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$this->dbType."/main.php");
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");
		IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/main.php");

		global $DB;
		$DB = new CDatabase;
		$this->DB =& $DB;

		$DB->DebugToFile = false;

		if (!function_exists("mysql_pconnect"))
			define("DBPersistent", false);

		if (!$DB->Connect($this->dbHost, $this->dbName, $this->dbUser, $this->dbPassword))
		{
			$this->SetError(InstallGetMessage("COULD_NOT_CONNECT")." ".$DB->db_Error);
			return;
		}

		$DB->debug = true;

		if ($this->IsBitrixInstalled())
			return;

		if (!$this->CheckDBOperation())
			return;

		if (!$this->CreateDBConn())
			return;

		$this->CreateLicenseFile();

		//Delete cache files if exists
		BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/bitrix/managed_cache");
		BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/bitrix/stack_cache");
	}


	function IsBitrixInstalled()
	{
		$DB =& $this->DB;

		$res = $DB->Query("SELECT COUNT(ID) FROM b_user", true);
		if ($res && $res->Fetch())
		{
			$this->SetError(str_replace("#DB#", $this->dbName, InstallGetMessage("ERR_ALREADY_INST1")));
			return true;
		}

		return false;
	}

	function CreateMySQL()
	{
		if ($this->createDatabase || $this->createUser)
			$dbConn = @mysql_connect($this->dbHost, $this->rootUser, $this->rootPassword);
		else
			$dbConn = @mysql_connect($this->dbHost, $this->dbUser, $this->dbPassword);

		if (!$dbConn)
		{
			$this->SetError(InstallGetMessage("ERR_CONNECT2MYSQL")." ".mysql_error());
			return false;
		}

		//Check MySQL version
		$mysqlVersion = false;
		$dbResult = @mysql_query("select VERSION() as ver", $dbConn);
		if ($arVersion = mysql_fetch_assoc($dbResult))
		{
			$mysqlVersion = trim($arVersion["ver"]);
			if (!BXInstallServices::VersionCompare($mysqlVersion, "4.1.11"))
			{
				$this->SetError(InstallGetMessage("SC_DB_VERS_MYSQL_ER"));
				return false;
			}

			$this->needCodePage = true; //BXInstallServices::VersionCompare($mysqlVersion, "4.1.2");

			if (!$this->needCodePage && $this->utf8)
			{
				$this->SetError(InstallGetMessage("INS_CREATE_DB_CHAR_NOTE"));
				return false;
			}
		}

		//SQL mode
		$dbResult = @mysql_query("SELECT @@sql_mode", $dbConn);
		if ($arResult = @mysql_fetch_row($dbResult))
		{
			$sqlMode = trim($arResult[0]);
			if (strpos($sqlMode, "STRICT_TRANS_TABLES") !== false )
			{
				$this->sqlMode = preg_replace("~,?STRICT_TRANS_TABLES~i", "", $sqlMode);
				$this->sqlMode = ltrim($this->sqlMode, ",");
			}
		}

		if ($this->createDatabase)
		{
			if (mysql_select_db($this->dbName, $dbConn))
			{
				$this->SetError(str_replace("#DB#", $this->dbName, InstallGetMessage("ERR_EXISTS_DB1")));
				return false;
			}

			@mysql_query("CREATE DATABASE ".$this->dbName, $dbConn);
			if (!mysql_select_db($this->dbName, $dbConn))
			{
				$this->SetError(str_replace("#DB#", $this->dbName, InstallGetMessage("ERR_CREATE_DB1")));
				return false;
			}
		}
		else
		{
			if (!mysql_select_db($this->dbName, $dbConn))
			{
				$this->SetError(str_replace("#DB#", $this->dbName, InstallGetMessage("ERR_CONNECT_DB1")));
				return false;
			}

			if (defined("DEBUG_MODE") || (isset($_COOKIE["clear_db"]) && $_COOKIE["clear_db"] == "Y") )
			{
				$result = @mysql_query("SHOW TABLES LIKE 'b_%'");
				while ($arTable = mysql_fetch_row($result))
					@mysql_query("DROP TABLE ".$arTable[0]);
			}
		}

		if ($this->dbUser != $this->rootUser)
		{
			$host = $this->dbHost;
			if ($position = strpos($host, ":"))
				$host = substr($host, 0, $position);

			if ($this->createUser)
			{
				$query = "GRANT ALL ON `".addslashes($this->dbName)."`.* TO '".addslashes($this->dbUser)."'@'".$host."' IDENTIFIED BY '".addslashes($this->dbPassword)."'";
				@mysql_query($query, $dbConn);
				$error = mysql_error();

				if ($error != "")
				{
					$this->SetError(InstallGetMessage("ERR_CREATE_USER")." ".$error);
					return false;
				}

				//if ($mysqlVersion !== false && BXInstallServices::VersionCompare($mysqlVersion, "4.1.1") && !BXInstallServices::VersionCompare(mysql_get_client_info(), "4.1.1"))
				//	@mysql_query("SET PASSWORD FOR '".addslashes($this->dbUser)."'@'".$host."' = OLD_PASSWORD('".addslashes($this->dbPassword)."')", $dbConn);
			}
			elseif ($this->createDatabase)
			{
				$query = "GRANT ALL ON `".addslashes($this->dbName)."`.* TO '".addslashes($this->dbUser)."'@'".$host."' ";
				@mysql_query($query, $dbConn);
				$error=mysql_error();

				if ($error != "")
				{
					$this->SetError(InstallGetMessage("ERR_GRANT_USER")." ".$error);
					return false;
				}
			}
		}

		if ($this->needCodePage)
		{
			if ($this->utf8)
				$codePage = "utf8";
			elseif (LANGUAGE_ID == "ru")
				$codePage = "cp1251";
			elseif ($this->createCharset != '')
				$codePage = $this->createCharset;
			else
				$codePage = 'latin1';

			if ($codePage)
			{
				if ($codePage == "utf8")
					@mysql_query("ALTER DATABASE `".$this->dbName."` CHARACTER SET UTF8 COLLATE utf8_unicode_ci", $dbConn);
				else
					@mysql_query("ALTER DATABASE `".$this->dbName."` CHARACTER SET ".$codePage, $dbConn);

				$error = mysql_error();
				if ($error != "")
				{
					$this->SetError(InstallGetMessage("ERR_ALTER_DB"));
					return false;
				}

				@mysql_query("SET NAMES '".$codePage."'", $dbConn);
			}
		}

		//Set InnoDB
		//if (strlen($this->createDBType) > 0)
			//@mysql_query("SET table_type = '".$this->createDBType."'", $dbConn);

		return true;
	}

	function CreateMSSQL()
	{
		if($this->DBSQLServerType=="NATIVE")
			return $this->CreateMSSQLNative();

		if ($this->createDatabase || $this->createUser)
			$dbConn = @odbc_connect($this->dbHost, $this->rootUser, $this->rootPassword);
		else
			$dbConn = @odbc_connect($this->dbHost, $this->dbUser, $this->dbPassword);

		if (!$dbConn)
		{
			$this->SetError(InstallGetMessage("ERR_CONNECT2MYSQL"));
			return false;
		}

		if ($this->createDatabase)
		{
			$query = 'CREATE DATABASE "'.$this->dbName.'"';
			if (!@odbc_exec($dbConn, $query))
			{
				$this->SetError(str_replace("#DB#", $this->dbName, InstallGetMessage("ERR_CREATE_DB1"))." ".odbc_errormsg($dbConn));
				return false;
			}
		}

		$query = 'USE "'.$this->dbName.'"';
		if (!@odbc_exec($dbConn, $query))
		{
			$this->SetError(str_replace("#DB#", $this->dbName, InstallGetMessage("ERR_CONNECT_DB1"))." ".odbc_errormsg($dbConn));
			return false;
		}

		if ($this->createUser)
		{
			$query = 'sp_addlogin "'.$this->dbUser.'", "'.addslashes($this->dbPassword).'", "'.$this->dbName.'"';
			if (!@odbc_exec($dbConn, $query))
			{
				$this->SetError(InstallGetMessage("ERR_CREATE_USER")." ".odbc_errormsg($dbConn));
				return false;
			}
		}

		if ($this->dbUser != $this->rootUser && ($this->createUser || $this->createDatabase))
		{
			$query = 'sp_grantdbaccess "'.$this->dbUser.'";
						EXEC sp_addrolemember  "db_owner","'.$this->dbUser.'";';
			if (!@odbc_exec($dbConn, $query))
			{
				$this->SetError(InstallGetMessage("ERR_GRANT_USER")." ".odbc_errormsg($dbConn));
				return false;
			}
		}

		return true;
	}

	function MSSQLNativeErrors()
	{
		$str = "";
		if( ($errors = sqlsrv_errors() ) != null)
			foreach( $errors as $error)
				$str .= "SQLSTATE: ".$error['SQLSTATE']."; code: ".$error['code']."; message: ".$error['message'].";\n ";

		return $str;
	}

	function CreateMSSQLNative()
	{
		if ($this->createDatabase || $this->createUser)
			$connectionInfo = array("UID" => $this->rootUser, "PWD" => $this->rootPassword, 'ReturnDatesAsStrings'=> true/*, "CharacterSet" => 'utf-8'*/);
		else
			$connectionInfo = array("UID" => $this->dbUser, "PWD" => $this->dbPassword, 'ReturnDatesAsStrings'=> true/*, "CharacterSet" => 'utf-8'*/);

		$dbConn = @sqlsrv_connect($this->dbHost, $connectionInfo);

		if (!$dbConn)
		{
			$this->SetError(InstallGetMessage("ERR_CONNECT2MYSQL"));
			return false;
		}

		if ($this->createDatabase)
		{
			$query = 'CREATE DATABASE "'.$this->dbName.'"';
			if (!@sqlsrv_query($dbConn, $query))
			{
				$this->SetError(str_replace("#DB#", $this->dbName, InstallGetMessage("ERR_CREATE_DB1"))." ".$this->MSSQLNativeErrors() );
				return false;
			}
		}

		$query = 'USE "'.$this->dbName.'"';
		if (!@sqlsrv_query($dbConn, $query))
		{
			$this->SetError(str_replace("#DB#", $this->dbName, InstallGetMessage("ERR_CONNECT_DB1"))." ".$this->MSSQLNativeErrors() );
			return false;
		}

		if ($this->createUser)
		{
			$query = 'sp_addlogin "'.$this->dbUser.'", "'.addslashes($this->dbPassword).'", "'.$this->dbName.'"';
			if (!@sqlsrv_query($dbConn, $query))
			{
				$this->SetError(InstallGetMessage("ERR_CREATE_USER")." ".$this->MSSQLNativeErrors() );
				return false;
			}
		}

		if ($this->dbUser != $this->rootUser && ($this->createUser || $this->createDatabase))
		{
			$query = 'sp_grantdbaccess "'.$this->dbUser.'";
						EXEC sp_addrolemember  "db_owner","'.$this->dbUser.'";';
			if (!@sqlsrv_query($dbConn, $query))
			{
				$this->SetError(InstallGetMessage("ERR_GRANT_USER")." ".$this->MSSQLNativeErrors() );
				return false;
			}
		}

		return true;
	}

	function CreateOracle()
	{
		if ($this->createUser)
			$dbConn = @OCILogon($this->rootUser, $this->rootPassword, $this->dbName);
		else
			$dbConn = @OCILogon($this->dbUser, $this->dbPassword, $this->dbName);

		if (!$dbConn)
		{
			$this->SetError(InstallGetMessage("ERR_CONNECT2MYSQL"));
			return false;
		}

		if ($this->utf8)
		{
			$query = "SELECT * FROM nls_database_parameters WHERE PARAMETER='NLS_CHARACTERSET' OR PARAMETER='NLS_NCHAR_CHARACTERSET'";
			$result = @OCIParse($dbConn, $query);

			if (!$result || !@OCIExecute($result) || !OCIFetchstatement($result, $arResult, 0, -1, OCI_FETCHSTATEMENT_BY_ROW))
			{
				$error = OCIError($result);
				$this->SetError(InstallGetMessage("INST_ORACLE_CHARSET_ERROR").($error['message'] ? ": ".$error['message']." ":" "));
				return false;
			}

			$arOracleParams = Array(
				"NLS_CHARACTERSET" => "",
				"NLS_NCHAR_CHARACTERSET" => "",
			);

			foreach ($arResult as $arParam)
				$arOracleParams[$arParam["PARAMETER"]] = $arParam["VALUE"];

			$arNLS_NCHAR_CHARACTERSETs = array("UTF8", "AL16UTF16");

			if(
				$arOracleParams["NLS_CHARACTERSET"] != "AL32UTF8"
				|| !in_array($arOracleParams["NLS_NCHAR_CHARACTERSET"], $arNLS_NCHAR_CHARACTERSETs)
			)
			{
				$this->SetError(InstallGetMessage("INST_ORACLE_UTF_ERROR"));
				return false;
			}
		}

		if ($this->createUser)
		{
			$query = "CREATE USER ".$this->dbUser." IDENTIFIED BY \"".$this->dbPassword.'"';
			$result = @OCIParse($dbConn, $query);

			if (!$result || !@OCIExecute($result))
			{
				$error = OCIError($result);
				$this->SetError(InstallGetMessage("ERR_CREATE_USER").($error['message'] ? ": ".$error['message']." ":" "));
				return false;
			}

			$query = "GRANT connect,resource,QUERY REWRITE TO ".$this->dbUser;
			$result = @OCIParse($dbConn, $query);
			if (!$result || !@OCIExecute($result))
			{
				$error = OCIError($result);
				$this->SetError(InstallGetMessage("ERR_GRANT_USER").($error['message'] ? ": ".$error['message']." ":" "));
				return false;
			}

			$query = "GRANT execute on dbms_lock TO ".$this->dbUser;
			$result = @OCIParse($dbConn, $query);
			@OCIExecute($result);
		}

		$result = @OCIParse($dbConn, "alter session set NLS_LENGTH_SEMANTICS = 'CHAR'");
		@OCIExecute($result);

		$result = @OCIParse($dbConn, "alter session set NLS_NUMERIC_CHARACTERS = '. '");
		@OCIExecute($result);

		if (defined("DEBUG_MODE") || (isset($_COOKIE["clear_db"]) && $_COOKIE["clear_db"] == "Y") )
		{
			$sql = '
				begin
					declare
					v_count number := 1;
					begin
						while v_count > 0
						loop
							for reco in (select object_type, object_name from user_objects where object_type not in (\'PACKAGE BODY\',\'LOB\',\'INDEX\',\'TRIGGER\',\'DATABASE LINK\') and object_name not like \'BIN$%$0\')
								loop
									begin
									execute immediate \'drop \'||\' \'||reco.object_type||\' \'||user||\'.\'||reco.object_name;
									exception when others then null;
									end;
								end loop;
							select count(*) into v_count from user_objects  where object_type not in (\'PACKAGE BODY\',\'LOB\',\'INDEX\',\'TRIGGER\',\'DATABASE LINK\') and object_name not like \'BIN$%$0\';
						end loop;
					end;
				end;';
		}

		$result = @OCIParse($dbConn, $sql);
		@OCIExecute($result);

		return true;
	}

	function CreateDBConn()
	{
		$filePath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php";

		if (!BXInstallServices::CheckDirPath($filePath, octdec($this->folderPermission)))
		{
			$this->SetError(str_replace("#ROOT#", BX_PERSONAL_ROOT."/", InstallGetMessage("ERR_C_SAVE_DBCONN")));
			return false;
		}

		$iconv='';
		if (function_exists('iconv'))
		{
			if (iconv("UTF-8", "WINDOWS-1251"."//IGNORE", "\xD0\xBC\xD0\xB0\xD0\xBC\xD0\xB0")!="\xEC\xE0\xEC\xE0")
				$iconv = 'define("BX_ICONV_DISABLE", true);'."\n";
		}

		// Connection params
		$fileContent = "<"."?\n".
			"define(\"DBPersistent\", false);\n".
			"$"."DBType = \"".$this->dbType."\";\n".
			($this->DBSQLServerType=="NATIVE"?"\$DBSQLServerType = 'NATIVE';\n":"").
			"$"."DBHost = \"".$this->dbHost."\";\n".
			"$"."DBLogin = \"".$this->dbUser."\";\n".
			"$"."DBPassword = \"".EscapePHPString($this->dbPassword)."\";\n".
			"$"."DBName = \"".$this->dbName."\";\n".
			"$"."DBDebug = false;\n".
			"$"."DBDebugToFile = false;\n".
			($this->createDBType=='innodb'?'define("MYSQL_TABLE_TYPE", "INNODB");'."\n":'').
			"\n".
			"@set_time_limit(60);\n".
			"\n".
			(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet") ? "@ini_set(\"memory_limit\", \"1024M\");\n" : "").
			$iconv.
			"define(\"DELAY_DB_CONNECT\", true);\n".
			"define(\"CACHED_b_file\", 3600);\n".
			"define(\"CACHED_b_file_bucket_size\", 10);\n".
			"define(\"CACHED_b_lang\", 3600);\n".
			"define(\"CACHED_b_option\", 3600);\n".
			"define(\"CACHED_b_lang_domain\", 3600);\n".
			"define(\"CACHED_b_site_template\", 3600);\n".
			"define(\"CACHED_b_event\", 3600);\n".
			"define(\"CACHED_b_agent\", 3660);\n".
			"define(\"CACHED_menu\", 3600);\n".
			"\n".
			($this->utf8 ? "define(\"BX_UTF\", true);\n" : "");

		if ($this->filePermission > 0)
			$fileContent .= "define(\"BX_FILE_PERMISSIONS\", 0".$this->filePermission.");\n";

		if ($this->folderPermission > 0)
		{
			$fileContent .= "define(\"BX_DIR_PERMISSIONS\", 0".$this->folderPermission.");\n";
			$fileContent .= "@umask(~BX_DIR_PERMISSIONS);\n";
		}

		if ($this->dbType == "mssql" && $this->DBSQLServerType!="NATIVE")
			$fileContent .= "@ini_set(\"odbc.defaultlrl\", 200000);\n";


		$memoryLimit = ini_get('memory_limit');
		if (!$memoryLimit || strlen($memoryLimit)<=0)
			$memoryLimit = get_cfg_var('memory_limit');
		$memoryLimit = intval(trim($memoryLimit));

		if($memoryLimit>0 && $memoryLimit < 256)
		{
			@ini_set("memory_limit", "512M");
			$memoryLimit = intval(trim(ini_get('memory_limit')));
			if($memoryLimit == 512)
				$fileContent .= "@ini_set(\"memory_limit\", \"512M\");\n";
		}

		$fileContent .= "define(\"BX_DISABLE_INDEX_PAGE\", true);\n";

		$fileContent .= "?".">";

		if (!$fp = @fopen($filePath, "wb"))
		{
			$this->SetError(str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN")));
			return false;
		}

		if (!fwrite($fp, $fileContent))
		{
			$this->SetError(str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN")));
			return false;
		}

		@fclose($fp);
		if ($this->filePermission > 0)
			@chmod($filePath, octdec($this->filePermission));

		return true;
	}

	function CreateAfterConnect()
	{
		if ($this->dbType == "mysql")
		{
			$codePage = "";
			if ($this->needCodePage)
			{
				if ($this->utf8)
					$codePage = "utf8";
				elseif (LANGUAGE_ID == "ru")
					$codePage = "cp1251";
				else
					$codePage = $this->createCharset;
			}

			$after_conn = "<"."?\n".
				(strlen($codePage) > 0 ? "$"."DB->Query(\"SET NAMES '".$codePage."'\");\n" : "").
				($this->sqlMode !== false ? "$"."DB->Query(\"SET sql_mode='".$this->sqlMode."'\");\n" : "").
				"?".">";

		}
		elseif ($this->dbType == "oracle")
		{
			$after_conn = "<"."?\n".
				"$"."DB->Query(\"alter session set NLS_LENGTH_SEMANTICS = 'CHAR'\");\n".
				"$"."DB->Query(\"alter session set NLS_NUMERIC_CHARACTERS = '. '\");\n".
				"?".">";
		}
		else
		{
			$after_conn = "<"."?\n"."?".">";
		}

		$filePath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/after_connect.php";

		if (!BXInstallServices::CheckDirPath($filePath, octdec($this->folderPermission)))
		{
			$this->SetError(str_replace("#ROOT#", BX_PERSONAL_ROOT."/", InstallGetMessage("ERR_C_SAVE_DBCONN")));
			return false;
		}

		if (!$fp = @fopen($filePath, "wb"))
		{
			$this->SetError(str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN")));
			return false;
		}

		if (!fwrite($fp, $after_conn))
		{
			$this->SetError(str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN")));
			return false;
		}

		@fclose($fp);
		if ($this->filePermission > 0)
			@chmod($filePath, octdec($this->filePermission));

		//Create .htaccess
		$filePath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/.htaccess";
		if (file_exists($filePath))
			return true;

		if (!$fp = @fopen($filePath, "wb"))
		{
			$this->SetError(str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN")));
			return false;
		}

		if (!fwrite($fp, "Deny From All"))
		{
			$this->SetError(str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], InstallGetMessage("ERR_C_SAVE_DBCONN")));
			return false;
		}

		@fclose($fp);
		if ($this->filePermission > 0)
			@chmod($filePath, octdec($this->filePermission));

		return true;
	}

	function CreateLicenseFile()
	{
		$wizard =& $this->GetWizard();
		$licenseKey = $wizard->GetVar("license");

		if (strlen($licenseKey) < 0)
			$licenseKey = "DEMO";

		$filePath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php";

		if (!$fp = @fopen($filePath, "wb"))
			return false;

		$fileContent = "<"."? \$"."LICENSE_KEY = \"".addslashes($licenseKey)."\"; ?".">";

		if (!fwrite($fp, $fileContent))
			return false;

		@fclose($fp);

		if ($this->filePermission > 0)
			@chmod($filePath, octdec($this->filePermission));

		return true;
	}

	function CheckDBOperation()
	{
		if (!is_object($this->DB))
			return;

		$DB =& $this->DB;
		$tableName = "b_tmp_bx";

		//Create table
		if ($this->dbType == "mysql")
			$strSql = "CREATE TABLE $tableName(ID INT)";
		elseif ($this->dbType == "oracle")
			$strSql = "CREATE TABLE $tableName(ID NUMBER(18))";
		else
			$strSql = "CREATE TABLE $tableName(ID INT)";

		$DB->Query($strSql, true);

		if (strlen($DB->db_Error) > 0)
		{
			$this->SetError(InstallGetMessage("ERR_C_CREATE_TBL"));
			return false;
		}

		//Alter table
		if ($this->dbType == "mysql")
			$strSql = "ALTER TABLE $tableName ADD COLUMN CLMN VARCHAR(100)";
		elseif ($this->dbType == "oracle")
			$strSql = "ALTER TABLE $tableName ADD CLMN VARCHAR2(100)";
		else
			$strSql = "ALTER TABLE $tableName ADD CLMN VARCHAR(100)";

		$DB->Query($strSql, true);

		if (strlen($DB->db_Error) > 0)
		{
			$this->SetError(InstallGetMessage("ERR_C_ALTER_TBL"));
			return false;
		}

		//Drop table
		if ($this->dbType == "mysql")
			$strSql = "DROP TABLE IF EXISTS $tableName";
		elseif ($this->dbType == "oracle")
			$strSql = "DROP TABLE $tableName CASCADE CONSTRAINTS";
		else
			$strSql = "DROP TABLE $tableName";

		$DB->Query($strSql, true);

		if (strlen($DB->db_Error) > 0)
		{
			$this->SetError(InstallGetMessage("ERR_C_DROP_TBL"));
			return false;
		}

		return true;
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		$dbType = $wizard->GetVar("dbType");

		if (BXInstallServices::CheckSession())
			$this->content .= '<input type="hidden" name="'.ini_get("session.name").'" value="'.session_id().'" />';

		$this->content .= '
		<table border="0" class="data-table">
			<tr>
				<td colspan="2" class="header">'.InstallGetMessage("INS_DATABASE_SETTINGS").'</td>
			</tr>';

		if ($dbType=="mysql")
			$this->content .= '
			<tr>
				<td nowrap align="right" valign="top" width="40%" >
					<span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_HOST").'
				</td>
				<td width="60%" valign="top">
					'.$this->ShowInputField("text", "host", Array("size" => "30")).'
					<br /><small>'.InstallGetMessage("INS_HOST_DESCR").'<br></small>
				</td>
			</tr>';

		elseif ($dbType == "mssql")
			$this->content .= '
			<tr>
				<td nowrap align="right" valign="top" width="40%" >
					<span style="color:red">*</span>&nbsp;DSN:
				</td>
				<td width="60%" valign="top">
					'.$this->ShowInputField("textarea", "host", Array("style" => "width:90%", "rows" => "3")).'<br />
					<small>'.InstallGetMessage("INS_HOST_DESCR_MSSQL").'<br></small>

				</td>
			</tr>';

		elseif ($dbType == "mssql_native")
			$this->content .= '
			<tr>
				<td nowrap align="right" valign="top" width="40%" >
					<span style="color:red">*</span>&nbsp;Server:
				</td>
				<td width="60%" valign="top">
					'.$this->ShowInputField("text", "host", Array("size"=>"30")).'<br />
					<small>IP\instance or Hostname\instance<br></small>

				</td>
			</tr>';

		elseif ($dbType=="oracle")
			$this->content .= '
			<tr>
				<td nowrap align="right" valign="top"><span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_DATABASE_OR").'</td>
				<td valign="top">
					'.$this->ShowInputField("textarea", "database", Array("style" => "width:90%", "rows" => "6")).'<br />
					<small>'.InstallGetMessage("INS_DB_ORACLE").'<br></small>
				</td>
			</tr>';

		$this->content .= '
		<tr>
			<td align="right" valign="top">'.InstallGetMessage("INS_CREATE_USER").'</td>
			<td valign="top">
				'.$this->ShowRadioField("create_user", "N", Array("id" => "create_user_N", "onclick" => "NeedRootUser()")).' <label for="create_user_N">'.InstallGetMessage("INS_USER").'</label><br>
				'.$this->ShowRadioField("create_user", "Y", Array("id" => "create_user_Y", "onclick" => "NeedRootUser()")).'  <label for="create_user_Y">'.InstallGetMessage("INS_USER_NEW").'</label>
			</td>
		</tr>';

		if ($dbType=="oracle")
			$this->content .= '
			<tr>
				<td nowrap align="right" valign="top"><span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_USERNAME").'</td>
				<td valign="top">
					'.$this->ShowInputField("text", "user", Array("size" => "30")).'<br />
					<small>'.InstallGetMessage("INS_USER_OR_DESCR").'<br></small>
				</td>
			</tr>
			<tr>
				<td nowrap align="right" valign="top">'.InstallGetMessage("INS_PASSWORD").'</td>
				<td valign="top">
					'.$this->ShowInputField("password", "password", Array("size" => "30")).'<br />
					<small>'.InstallGetMessage("INS_PASSWORD_OR_DESCR").'<br></small>
				</td>
			</tr>';

		else
			$this->content .= '
			<tr>
				<td nowrap align="right" valign="top"><span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_USERNAME").'</td>
				<td valign="top">
					'.$this->ShowInputField("text", "user", Array("size" => "30")).'<br />
					<small>'.InstallGetMessage("INS_USER_DESCR").'<br></small>
				</td>
			</tr>
			<tr>
				<td nowrap align="right" valign="top">'.InstallGetMessage("INS_PASSWORD").'</td>
				<td valign="top">
					'.$this->ShowInputField("password", "password", Array("size" => "30")).'<br />
					<small>'.InstallGetMessage("INS_PASSWORD_DESCR").'<br></small>
				</td>
			</tr>
			<tr>
				<td nowrap align="right" valign="top">'.InstallGetMessage("INS_CREATE_DB").'</td>
				<td valign="top">
					'.$this->ShowRadioField("create_database", "N", Array("id" => "create_db_N", "onclick" => "NeedRootUser()")).' <label for=create_db_N>'.InstallGetMessage("INS_DB_EXISTS").'</label><br>
					'.$this->ShowRadioField("create_database", "Y", Array("id" => "create_db_Y", "onclick" => "NeedRootUser()")).'  <label for=create_db_Y>'.InstallGetMessage("INS_DB_NEW").'</label>
				</td>
			</tr>
			<tr>
				<td nowrap align="right" valign="top">
					<div id="db_exists"><span style="color:red">*</span>'.InstallGetMessage("INS_DATABASE").'</div>
					<div id="db_new" style="display:none"><span style="color:red">*</span>'.InstallGetMessage("INS_DATABASE_NEW").'</div>
				</td>
				<td valign="top">
					'.$this->ShowInputField("text", "database", Array("size" => "30")).'<br />
					<small>'.InstallGetMessage("INS_DATABASE_MY_DESC").'<br></small>
				</td>
			</tr>';

		if ($dbType=="mysql")
		{
			$this->content .= '
			<tr>
				<td nowrap align="right" valign="top" >'.InstallGetMessage("INS_CREATE_DB_TYPE").'</td>
				<td valign="top">
					'.$this->ShowSelectField("create_database_type", Array("" => InstallGetMessage("INS_C_DB_TYPE_STAND"), "innodb" => "Innodb")).'<br>
				</td>
			</tr>';
		}

		$this->content .= '
			<tr id="line1">
				<td colspan="2" class="header">'.InstallGetMessage("ADMIN_PARAMS").'</td>
			</tr>
			<tr id="line2">
				<td nowrap align="right" valign="top">
					<span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_ROOT_USER").'</td>
				<td valign="top">
					'.$this->ShowInputField("text", "root_user", Array("size" => "30", "id" => "root_user")).'<br />
					<small>'.InstallGetMessage("INS_ROOT_USER_DESCR").'<br></small>
				</td>
			</tr>
			<tr id="line3">
				<td nowrap align="right" valign="top">
					'.InstallGetMessage("INS_ROOT_PASSWORD").'
				</td>
				<td valign="top">
					'.$this->ShowInputField("password", "root_password", Array("size" => "30", "id" => "root_password")).'<br />
					<small>'.InstallGetMessage("INS_ROOT_PASSWORD_DESCR").'<br></small>
				</td>
			</tr>';

		$this->content .= '
			<tr>
				<td colspan="2" class="header">'.InstallGetMessage("INS_ADDITIONAL_PARAMS").'</td>
			</tr>
			<tr>
				<td nowrap align="right" width="40%" valign="top">'.InstallGetMessage("INS_AP_FAP").':</td>
				<td width="60%" valign="top">
					'.$this->ShowInputField("text", "file_access_perms", Array("size" => "10")).'<br />
					<small>'.InstallGetMessage("INS_AP_FAP_DESCR").'<br></small>
				</td>
			</tr>
			<tr>
				<td nowrap align="right" width="40%" valign="top">'.InstallGetMessage("INS_AP_PAP").':</td>
				<td width="60%" valign="top">
					'.$this->ShowInputField("text", "folder_access_perms", Array("size" => "10")).'<br />
					<small>'.InstallGetMessage("INS_AP_PAP_DESCR").'<br></small>
				</td>
			</tr>
		</table>
		<script type="text/javascript">NeedRootUser();</script>';
	}
}

class CreateModulesStep extends CWizardStep
{
	var $arSteps = Array();
	var $singleSteps = Array();

	function InitStep()
	{
		$this->SetStepID("create_modules");
		$this->SetTitle(InstallGetMessage("INST_PRODUCT_INSTALL"));
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();
		$currentStep = $wizard->GetVar("nextStep");
		$currentStepStage = $wizard->GetVar("nextStepStage");

		if ($currentStep == "__finish")
		{
			$wizard->SetCurrentStep("create_admin");
			return;
		}

		$this->singleSteps = Array(
			//"remove_help" => InstallGetMessage("INST_REMOVE_HELP"),
			"remove_mysql" => InstallGetMessage("INST_REMOVE_TEMP_FILES")." (MySQL)",
			"remove_mssql" => InstallGetMessage("INST_REMOVE_TEMP_FILES")." (MS SQL Server)",
			"remove_oracle" => InstallGetMessage("INST_REMOVE_TEMP_FILES")." (Oracle)",
			"remove_misc" => InstallGetMessage("INST_REMOVE_TEMP_FILES"),
		);

		$this->arSteps = array_merge($this->GetModuleList(), array_keys($this->singleSteps));

		if($GLOBALS["arWizardConfig"]["skipInstallModules"]!='')
			$arSkipInstallModules = preg_split('/[\s,]+/', $GLOBALS["arWizardConfig"]["skipInstallModules"], -1, PREG_SPLIT_NO_EMPTY);
		else
			$arSkipInstallModules = false;

		$searchIndex = array_search($currentStep, $this->arSteps);
		if ($searchIndex === false || $searchIndex === null)
			$currentStep = "main";

		if (array_key_exists($currentStep, $this->singleSteps) && $currentStepStage != "skip")
			$success = $this->InstallSingleStep($currentStep);
		else
		{
			if($arSkipInstallModules!==false && in_array($currentStep, $arSkipInstallModules) && $currentStepStage!="utf8")
				$success = true;
			else
				$success = $this->InstallModule($currentStep, $currentStepStage);
		}

		if ($currentStep == "main" && $success === false)
			$this->SendResponse("window.onbeforeunload = null; window.ajaxForm.StopAjax(); window.ajaxForm.SetStatus('0', '".InstallGetMessage("INST_MAIN_INSTALL_ERROR")."');");

		list($nextStep, $nextStepStage, $percent, $status) = $this->GetNextStep($currentStep, $currentStepStage, $success);

		$response = "";
		if ($nextStep == "__finish")
			$response .= "window.onbeforeunload = null; window.ajaxForm.StopAjax();";
		$response .= "window.ajaxForm.SetStatus('".$percent."'); window.ajaxForm.Post('".$nextStep."', '".$nextStepStage."','".$status."');";

		$this->SendResponse($response);
	}

	function SendResponse($response)
	{
		header("Content-Type: text/html; charset=".INSTALL_CHARSET);
		die("[response]".$response."[/response]");
	}

	function GetModuleList()
	{
		$arModules = Array();

		$handle = @opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules");
		if (!$handle)
			return $arModules;

		while (false !== ($dir = readdir($handle)))
		{
			$module_dir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$dir;
			if(is_dir($module_dir) && $dir != "." && $dir !=".." && $dir!="main" && file_exists($module_dir."/install/index.php"))
				$arModules[] = $dir;
		}
		closedir($handle);

		uasort($arModules, create_function('$a, $b', 'return strcasecmp($a, $b);'));
		array_unshift($arModules, "main");

		return $arModules;
	}

	function GetNextStep($currentStep, $currentStepStage, $stepSuccess)
	{
		//Next step and next stage
		$stepIndex = array_search($currentStep, $this->arSteps);

		if ($currentStepStage == "utf8")
		{
			$nextStep = $currentStep;
			$nextStepStage = "database";
		}
		elseif ($currentStepStage == "database" && $stepSuccess)
		{
			$nextStep = $currentStep;
			$nextStepStage = "files";
		}
		else
		{
			if (!isset($this->arSteps[$stepIndex+1]))
				return Array("__finish", "", 100, InstallGetMessage("INST_INSTALL_COMPLETE"));

			$nextStep = $this->arSteps[$stepIndex+1];

			if (array_key_exists($nextStep, $this->singleSteps))
				$nextStepStage = "single";
			elseif (defined("BX_UTF"))
				$nextStepStage = "utf8";
			else
				$nextStepStage = "database";
		}

		//Percent
		$singleSteps = count($this->singleSteps);
		$moduleSteps = count($this->arSteps) - $singleSteps;
		$moduleStageCnt = (defined("BX_UTF") ? 3 : 2); //Each module have 2 or 3 steps

		$stepCount= $moduleSteps * $moduleStageCnt + $singleSteps;

		if ($currentStepStage == "files" || ($currentStepStage == "skip" && !array_key_exists($currentStep, $this->singleSteps)))
			$completeSteps = ++$stepIndex*$moduleStageCnt;
		elseif ($currentStepStage=="database")
			$completeSteps = ++$stepIndex*$moduleStageCnt-1;
		elseif ($currentStepStage == "utf8")
			$completeSteps = ++$stepIndex*$moduleStageCnt-2;
		else
			$completeSteps = $moduleSteps*$moduleStageCnt + ($stepIndex+1-$moduleSteps);

		$percent = floor( $completeSteps / $stepCount * 100 );

		//Status
		$arStatus = Array(
			"utf8" => "UTF-8",
			"database" => InstallGetMessage("INST_INSTALL_DATABASE"),
			"files" => InstallGetMessage("INST_INSTALL_FILES"),
		);

		if (array_key_exists($nextStep , $this->singleSteps))
			$status = $this->singleSteps[$nextStep];
		elseif ($nextStep == "main")
			$status = InstallGetMessage("INST_MAIN_MODULE")." (".$arStatus[$nextStepStage].")";
		else
		{
			$module =& $this->GetModuleObject($nextStep);
			$moduleName =
				(is_object($module) ?
					(defined("BX_UTF") && ($nextStepStage == "files" || BXInstallServices::IsUTFString($module->MODULE_NAME)) ?
						mb_convert_encoding($module->MODULE_NAME, INSTALL_CHARSET, "utf-8"):
						$module->MODULE_NAME
					):
					$nextStep
				);

			$status = InstallGetMessage("INST_INSTALL_MODULE")." &quot;".$moduleName."&quot; (".$arStatus[$nextStepStage].")";
		}

		return Array($nextStep, $nextStepStage, $percent, $status);
	}

	function InstallSingleStep($code)
	{
		global $DBType;
		require_once($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php");

		$wizard =& $this->GetWizard();
		$removeHelp = ($wizard->GetVar("help_inst") != "Y");

		if ($code == "remove_help" && $removeHelp)
			BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/bitrix/help");
		elseif ($code == "remove_mysql" && $DBType != "mysql")
			BXInstallServices::DeleteDbFiles("mysql");
		elseif ($code == "remove_mssql" && $DBType != "mssql")
			BXInstallServices::DeleteDbFiles("mssql");
		elseif ($code == "remove_oracle" && $DBType != "oracle")
			BXInstallServices::DeleteDbFiles("oracle");
		elseif ($code == "remove_misc")
		{
			BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/bitrix/httest");
			BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/bxtest");
		}

		return true;
	}

	function &GetModuleObject($moduleID)
	{
		if(!class_exists('CModule'))
		{
			global $DB, $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $DBDebug, $DBDebugToFile, $APPLICATION, $USER, $DBSQLServerType;
			require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include.php");
		}

		$installFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/install/index.php";
		if (!file_exists($installFile))
			return false;
		include_once($installFile);

		$moduleIDTmp = str_replace(".", "_", $moduleID);
		if (!class_exists($moduleIDTmp))
			return false;

		return new $moduleIDTmp;
	}

	function InstallModule($moduleID, $currentStepStage)
	{
		if ($moduleID == "main")
		{
			error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);
			global $DB, $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $DBDebug, $DBDebugToFile, $APPLICATION, $DBSQLServerType;
			require_once($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php");
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/module.php");
			if($DBType=="mssql" && $DBSQLServerType=="NATIVE")
				require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/mssql/database_ms.php");
			else
				require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$DBType."/database.php");
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$DBType."/main.php");
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/cache.php");
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$DBType."/usertype.php");
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$DBType."/user.php");
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$DBType."/option.php");
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$DBType."/event.php");
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/".$DBType."/agent.php");
		}
		else
		{
			global $DB, $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $DBDebug, $DBDebugToFile, $APPLICATION, $USER, $DBSQLServerType;
			require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include.php");

			if (strtolower($DB->type)=="mysql" && defined("MYSQL_TABLE_TYPE") && strlen(MYSQL_TABLE_TYPE)>0)
				$DB->Query("SET table_type = '".MYSQL_TABLE_TYPE."'", true);

			if (IsModuleInstalled($moduleID) && $currentStepStage == "database")
				return true;
		}

		@set_time_limit(3600);

		$module =& $this->GetModuleObject($moduleID);
		if (!is_object($module))
			return true;

		if ($currentStepStage == "skip")
		{
			return true;
		}
		elseif ($currentStepStage == "utf8")
		{
			if (!$this->IsModuleEncode($moduleID))
			{
				if ($moduleID == "main")
				{
					$this->EncodeDemoWizard();
				}

				BXInstallServices::EncodeDir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID, INSTALL_CHARSET);
				$this->SetEncodeModule($moduleID);
			}

			return true;
		}
		elseif ($currentStepStage == "database")
		{
			$DBDebug = true;
			if (!$module->InstallDB())
			{
				if ($ex = $APPLICATION->GetException())
					BXInstallServices::Add2Log($ex->GetString(), "DATABASE_ERROR");
				return false;
			}

			$module->InstallEvents();

			if ($moduleID == "main")
			{
				

				
			}
		}
		elseif ($currentStepStage == "files")
		{
			if (!$module->InstallFiles())
			{
				if ($ex = $APPLICATION->GetException())
					BXInstallServices::Add2Log($ex->GetString(), "FILES_ERROR");
				return false;
			}
		}

		return true;
	}

	function IsModuleEncode($moduleID)
	{
		$filePath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/utf.log";

		if (!is_file($filePath))
			return false;

		$fileContent = file_get_contents($filePath);

		return (strpos($fileContent, $moduleID."/") !== false);
	}

	function SetEncodeModule($moduleID)
	{
		$filePath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/utf.log";

		if (!$handle = @fopen($filePath, "ab+"))
			return false;

		@fwrite($handle, $moduleID."/");
		@fclose($handle);
	}

	function EncodeDemoWizard()
	{
		$wizardName = BXInstallServices::GetDemoWizard();
		if ($wizardName === false)
			return;

		$charset = BXInstallServices::GetWizardCharset($wizardName);
		if ($charset === false)
			$charset = INSTALL_CHARSET;

		//wizard customization file
		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php"))
			BXInstallServices::EncodeFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php", $charset);

		//convert all wizards to UTF
		$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix";
		if($dir = opendir($path))
		{
			while(($file = readdir($dir)) !== false)
			{
				if($file == "." || $file == "..")
					continue;

				if(is_dir($path."/".$file))
					BXInstallServices::EncodeDir($path."/".$file, $charset, $encodeALL = true);
			}
			closedir($dir);
		}

//		BXInstallServices::EncodeDir($_SERVER["DOCUMENT_ROOT"].CWizardUtil::GetRepositoryPath().CWizardUtil::MakeWizardPath($wizardName), $charset, $encodeALL = true);
	}

	function ShowStep()
	{
		@include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php");

		$this->content .= '
		<div id="result">
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr>
					<td colspan="2"><div id="status"></div></td>
				</tr>
				<tr>
					<td width=90% height="10">
						<div style="border:1px solid #B9CBDF">
						<div id="indicator" style="height:10px; width:0%; background-color:#B9CBDF"></div>
						</div>
					</td>
					<td width=10%>&nbsp;<span id="percent">0%</span></td>
				</tr>
			</table>
		</div>

		<div id="wait" align=center>
			<br /><br />
			<table width=200 cellspacing=0 cellpadding=0 border=0 style="border:1px solid #EFCB69" bgcolor="#FFF7D7">
				<tr>
					<td height=50 width="50" valign="middle" align=center><img src="/bitrix/images/install/wait.gif"></td>
					<td height=50 width=150>'.InstallGetMessage("INST_LOAD_WAIT").'</td>
				</tr>
			</table>
		</div><br />
		<div id="error_container" style="display:none">
			<div id="error_notice"><span style="color:red;">'.InstallGetMessage("INST_ERROR_OCCURED").'<br />'.InstallGetMessage("INST_TEXT_ERROR").':</span></div>
			<div id="error_text"></div>
			<div><span style="color:red;">'.InstallGetMessage("INST_ERROR_NOTICE").'</span></div>
			<div id="error_buttons" align="center">
			<br /><input type="button" value="'.InstallGetMessage("INST_RETRY_BUTTON").'" id="error_retry_button" onclick="" />&nbsp;<input type="button" id="error_skip_button" value="'.InstallGetMessage("INST_SKIP_BUTTON").'" onclick="" />&nbsp;</div>
		</div>

		'.$this->ShowHiddenField("nextStep", "main").'
		'.$this->ShowHiddenField("nextStepStage", "database").'
		<iframe style="display:none;" id="iframe-post-form" name="iframe-post-form" src="javascript:\'\'"></iframe>
		';

		$wizard =& $this->GetWizard();

		$formName = $wizard->GetFormName();
		$NextStepVarName = $wizard->GetRealName("nextStep");
		$firstStage = (defined("BX_UTF") ? "utf8" : "database");

		$this->content .= '
			<script type="text/javascript">
				var ajaxForm = new CAjaxForm("'.$formName.'", "iframe-post-form", "'.$NextStepVarName.'");
				ajaxForm.Post("main", "'.$firstStage.'", "'.InstallGetMessage("INST_MAIN_MODULE").' ('.( defined("BX_UTF") ? "UTF-8" : InstallGetMessage("INST_INSTALL_DATABASE") ).')");
			</script>
		';
	}

}

class CreateAdminStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("create_admin");
		$this->SetNextStep("select_wizard");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetTitle(InstallGetMessage("INST_CREATE_ADMIN"));

		$wizard =& $this->GetWizard();
		$wizard->SetDefaultVar("login", "admin");
		$wizard->SetDefaultVar("email", "");
	}

	function OnPostForm()
	{
		global $DB, $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $DBDebug, $DBDebugToFile, $APPLICATION, $USER, $DBSQLServerType;
		require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include.php");

		$wizard =& $this->GetWizard();

		$email = $wizard->GetVar("email");
		$login = $wizard->GetVar("login");
		$adminPass = $wizard->GetVar("admin_password");
		$adminPassConfirm = $wizard->GetVar("admin_password_confirm");
		$userName = $wizard->GetVar("user_name");
		$userSurname = $wizard->GetVar("user_surname");

		/*if (defined("BX_UTF"))
		{
			foreach (Array("email", "login", "adminPass", "adminPassConfirm", "userName", "userSurname") as $variable)
				$$variable = mb_convert_encoding($$variable, "utf-8", INSTALL_CHARSET);
		}*/

		if (strlen($email)<=0)
		{
			$this->SetError(InstallGetMessage("INS_FORGOT_EMAIL"));
			return;
		}
		elseif (!check_email($email))
		{
			$this->SetError(InstallGetMessage("INS_WRONG_EMAIL"));
			return;
		}

		if (strlen($login)<=0)
		{
			$this->SetError(InstallGetMessage("INS_FORGOT_LOGIN"));
			return;
		}
		elseif (strlen($login)<3)
		{
			$this->SetError(InstallGetMessage("INS_LOGIN_MIN"));
			return;
		}

		if (strlen($adminPass)<=0)
		{
			$this->SetError(InstallGetMessage("INS_FORGOT_PASSWORD"));
			return;
		}
		elseif (strlen($adminPass)<6)
		{
			$this->SetError(InstallGetMessage("INS_PASSWORD_MIN"));
			return;
		}
		elseif ($adminPass != $adminPassConfirm)
		{
			$this->SetError(InstallGetMessage("INS_WRONG_CONFIRM"));
			return;
		}

		$admin = $DB->Query("SELECT * FROM b_user WHERE ID=1", true);
		if ($admin === false)
			return;

		$isAdminExists = ($admin->Fetch() ? true : false);

		$arFields = Array(
			"NAME" => $userName,
			"LAST_NAME" => $userSurname,
			"EMAIL" => $email,
			"LOGIN" => $login,
			"ACTIVE" => "Y",
			"GROUP_ID" => Array("1"),
			"PASSWORD" => $adminPass,
			"CONFIRM_PASSWORD" => $adminPassConfirm,
		);

		if ($isAdminExists)
		{
			$userID = 1;
			$success = $USER->Update($userID, $arFields);
		}
		else
		{
			$userID = $USER->Add($arFields);
			$success = (intval($userID) > 0);
		}

		if (!$success)
		{
			$this->SetError($USER->LAST_ERROR);
			return false;
		}

		COption::SetOptionString("main", "email_from", $email);
		$USER->Authorize($userID, true);

		//Delete utf log
		BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/utf.log");

		if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet") && !defined("FIRST_EDITION"))
			RegisterModuleDependences('main', 'OnBeforeProlog', 'main', 'CWizardSolPanel', 'ShowPanel', 100, '/modules/main/install/wizard_sol/panel_button.php');

		$arWizardsList = BXInstallServices::GetWizardsList();
		if (count($arWizardsList) <= 0)
		{
			$wizardName = BXInstallServices::GetDemoWizard();
			if ($wizardName)
			{
				if (BXInstallServices::CreateWizardIndex($wizardName, $errorMessageTmp))
				{
					BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/license.html");
					BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/readme.html");
					BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/install.config");

					if (defined("BX_UTF"))
						BXInstallServices::EncodeFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".LANGUAGE_ID."/install.php", INSTALL_CHARSET);

					BXInstallServices::LocalRedirect("/index.php");
				}
				else
				{
					$this->SetError($errorMessageTmp);
				}
			}
		}

/*		if ($this->CreateWizardIndex())
		{
			BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/license.html");
			BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/readme.html");
			BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/install.config");
			BXInstallServices::LocalRedirect("/index.php");
		}*/

		return true;
	}

/*
	function CreateWizardIndex()
	{
		$wizardName = BXInstallServices::GetDemoWizard();
		if ($wizardName === false)
			return false;

		$indexContent = '<'.'?'.
								'require('.'$'.'_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");'.
								'require_once('.'$'.'_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");'.
								'$'.'wizard = new CWizard("'.$wizardName.'");'.
								'$'.'wizard->Install();'.
								'require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");'.
								'?'.'>';

		$handler = @fopen($_SERVER["DOCUMENT_ROOT"]."/index.php","wb");

		if (!$handler)
		{
			$this->SetError(InstallGetMessage("INST_WIZARD_INDEX_ACCESS_ERROR"));
			return false;
		}

		$success = @fwrite($handler, $indexContent);
		if (!$success)
		{
			$this->SetError(InstallGetMessage("INST_WIZARD_INDEX_ACCESS_ERROR"));
			return false;
		}

		if (defined("BX_FILE_PERMISSIONS"))
			@chmod($_SERVER["DOCUMENT_ROOT"]."/index.php", BX_FILE_PERMISSIONS);

		fclose($handler);

		return true;
	}*/

	function ShowStep()
	{
		if (defined("BX_UTF"))
			define("INSTALL_UTF_PAGE", true);

		$this->content = '
		<table border="0" class="data-table">
			<tr>
				<td colspan="2" class="header">'.InstallGetMessage("INS_ADMIN_SETTINGS").'</td>
			</tr>
			<tr>
				<td nowrap align="right" ><span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_LOGIN").'</td>
				<td >'.$this->ShowInputField("text", "login", Array("size" => "30")).'</td>
			</tr>
			<tr>
				<td nowrap align="right"><span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_ADMIN_PASSWORD").'</td>
				<td >'.$this->ShowInputField("password", "admin_password", Array("size" => "30")).'</td>
			</tr>
			<tr>
				<td nowrap align="right"><span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_PASSWORD_CONF").'</td>
				<td>'.$this->ShowInputField("password", "admin_password_confirm", Array("size" => "30")).'</td>
			</tr>
			<tr>
				<td nowrap align="right"><span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_EMAIL").'</td>
				<td>'.$this->ShowInputField("text", "email", Array("size" => "30")).'</td>
			</tr>
			<tr>
				<td nowrap align="right">'.InstallGetMessage("INS_NAME").'</td>
				<td>'.$this->ShowInputField("text", "user_name", Array("size" => "30")).'</td>
			</tr>
			<tr>
				<td nowrap align="right" >'.InstallGetMessage("INS_LAST_NAME").'</td>
				<td>'.$this->ShowInputField("text", "user_surname", Array("size" => "30")).'</td>
			</tr>
		</table>';
	}
}

class SelectWizardStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("select_wizard");
		$this->SetNextStep("finish");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetTitle(InstallGetMessage("INST_SELECT_WIZARD"));
	}

	function OnPostForm()
	{
		global $DB, $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $DBDebug, $DBDebugToFile, $APPLICATION, $USER, $DBSQLServerType;

		require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include.php");

		$wizard =& $this->GetWizard();
		$selectedWizard = $wizard->GetVar("selected_wizard");

		if (strlen($selectedWizard)<=0)
		{
			$this->SetError(InstallGetMessage("INS_WRONG_WIZARD"));
			return;
		}

		if ($selectedWizard == "@")
		{
			$wizard->SetCurrentStep("load_module");
			return true;
		}

		$arTmp = explode(":", $selectedWizard);
		$ar = array();
		foreach ($arTmp as $a)
		{
			$a = preg_replace("#[^a-z0-9_.-]+#i", "", $a);
			if (strlen($a) > 0)
				$ar[] = $a;
		}

		if (count($ar) > 2)
		{
			$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$ar[0]."/install/wizards/".$ar[1]."/".$ar[2];

			if (!file_exists($path) || !is_dir($path))
			{
				$this->SetError(InstallGetMessage("INS_WIZARD_NOT_FOUND"));
				return;
			}

			BXInstallServices::CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$ar[0]."/install/wizards/".$ar[1]."/".$ar[2],
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".$ar[1]."/".$ar[2],
				true,
				(defined("BX_DIR_PERMISSIONS") ? (BX_DIR_PERMISSIONS) : 0755),
				(defined("BX_FILE_PERMISSIONS") ? (BX_FILE_PERMISSIONS) : 0644)
			);

			$ar = array($ar[1], $ar[2]);
		}

		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".$ar[0]."/".$ar[1])
			|| !is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".$ar[0]."/".$ar[1]))
		{
			$this->SetError(InstallGetMessage("INS_WIZARD_NOT_FOUND"));
			return;
		}

		if (BXInstallServices::CreateWizardIndex($ar[0].":".$ar[1], $errorMessageTmp))
		{
			$u = "/index.php";
			if (defined("WIZARD_DEFAULT_SITE_ID"))
			{
				$rsSite = CSite::GetList($by="sort", $order="asc", array("ID" => WIZARD_DEFAULT_SITE_ID));
				$arSite = $rsSite->GetNext();

				$u = "";
				if (is_array($arSite["DOMAINS"]) && strlen($arSite["DOMAINS"][0]) > 0 || strlen($arSite["DOMAINS"]) > 0)
					$u .= "http://";
				if (is_array($arSite["DOMAINS"]))
					$u .= $arSite["DOMAINS"][0];
				else
					$u .= $arSite["DOMAINS"];
				$u .= $arSite["DIR"];
			}
			else
			{
				BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/license.html");
				BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/readme.html");
				BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/install.config");
			}

			if (defined("BX_UTF"))
				BXInstallServices::EncodeFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".LANGUAGE_ID."/install.php", INSTALL_CHARSET);

			BXInstallServices::LocalRedirect($u);
		}
		else
		{
			$this->SetError($errorMessageTmp);
		}

		return true;
	}

	function ShowStep()
	{
		if (defined("BX_UTF"))
			define("INSTALL_UTF_PAGE", true);

		$wizard =& $this->GetWizard();
		$prefixName = $wizard->GetRealName("selected_wizard");

		$arWizardsList = BXInstallServices::GetWizardsList();

		$b = true;

		$this->content = '
			<style type="text/css">
			#solutions-container
			{
				margin-bottom: 15px;
			}

			a.solution-item
			{
				display:block;
				border: 0;
				margin-bottom: 10px;
				color: Black;
				text-decoration: none;
				outline: none;
			}

			a.solution-item h4
			{
				margin: 10px;
				margin-top: 9px; /*compensating 1px padding*/
				font-family:Helvetica;
				font-size:1.5em;
			}
			a.solution-item p
			{
				margin: 10px;
			}

			div.solution-item-wrapper
			{
				width: 97px;
				float: left;
			}

			a.solution-picture-item
			{
				margin: 3px;
				text-align: center;
			}

			div.solution-description
			{
				margin-top: 3px;
				margin-left: 4px;
				color: #999;
				text-align:left;
			}

			a.solution-picture-item img.solution-image
			{
				width: 70px;
				float: none;
				margin: 7px 0px 7px;
			}

			img.solution-image
			{
				width: 100px;
				float: left;
				margin: 10px;
				border: 1px solid #CFCFCF;
			}
			div.solution-inner-item
			{
				padding: 1px;
				overflow: hidden;
				zoom: 1;
			}

			a.solution-item div.solution-inner-item,
			a.solution-item b
			{
				background-color:#F7F7F7;
				cursor: pointer;
				cursor: hand;
			}

			a.solution-item:hover div.solution-inner-item,
			a.solution-item:hover b
			{
				background-color: #FFF0B2;
			}

			a.solution-item-selected div.solution-inner-item,
			a.solution-item-selected b,
			a.solution-item-selected:hover div.solution-inner-item,
			a.solution-item-selected:hover b
			{
				background-color: #CADBEC;
			}

			#solution-preview
			{
				margin-top: 10px;
			}

			#solution-preview div.solution-inner-item,
			#solution-preview b
			{
				background-color:#F7F7F7;
			}

			#solution-preview div.solution-inner-item
			{
				padding: 10px;
				text-align: center;
			}

			#solution-preview-image
			{
				border: 1px solid #CFCFCF;
				width: 450px;
			}

			/* Round Corners */
			.r0, .r1, .r2, .r3, .r4 { overflow: hidden; font-size:1px; display: block; height: 1px;}
			.r4 { margin: 0 4px; }
			.r3 { margin: 0 3px; }
			.r2 { margin: 0 2px; }
			.r1 { margin: 0 1px; }
			</style>';

		$this->content .= '
		<script type="text/javascript">
			function SelectSolution(element, solutionId)
			{
				var container = document.getElementById("solutions-container");
				var anchors = container.getElementsByTagName("A");
				for (var i = 0; i < anchors.length; i++)
				{
					if (anchors[i].parentNode != container)
						continue;
					anchors[i].className = "solution-item";
				}
				element.className = "solution-item solution-item-selected";
				var hidden = document.getElementById("id_'.htmlspecialchars($prefixName).'");
				hidden.value = solutionId;

				document.getElementById("id_radio_"+solutionId).checked=true;
			}
		</script>
		';

		$this->content .= '<div id="solutions-container">';
		foreach ($arWizardsList as $w)
		{
			$this->content .= '<a class="solution-item" href="javascript:void(0);" onclick="SelectSolution(this, \''.htmlspecialchars($w["ID"]).'\');" ondblclick="document.forms[\''.htmlspecialchars($wizard->GetFormName()).'\'].submit();">
				<b class="r3"></b><b class="r1"></b><b class="r1"></b>
				<div class="solution-inner-item">
					<input type="radio" id="id_radio_'.htmlspecialchars($w["ID"]).'" name="redio" style="float:left;margin-left:4px;margin-top:10px;margin-bottom:20px;" />
					'.(strlen($w["IMAGE"]) > 0 ? '<img alt="" src="'.htmlspecialchars($w["IMAGE"]).'" class="solution-image" />' : "").'
					<h4>'.$w["NAME"].'</h4>
					<p>'.$w["DESCRIPTION"].'</p>
				</div>
				<b class="r1"></b><b class="r1"></b><b class="r3"></b>
			</a>';
		}

		$this->content .= '<a class="solution-item" href="javascript:void(0);" onclick="SelectSolution(this, \'@\'); return false;" ondblclick="document.forms[\''.htmlspecialchars($wizard->GetFormName()).'\'].submit();">
			<b class="r3"></b><b class="r1"></b><b class="r1"></b>
			<div class="solution-inner-item">
				<input type="radio" id="id_radio_@" name="redio" style="float:left;margin-left:4px;margin-top:10px;margin-bottom:20px;">
				<img alt="" src="/bitrix/images/install/marketplace.gif" class="solution-image" />
				<h4>'.InstallGetMessage("INS_LOAD_FROM_MARKETPLACE").'</h4>
				<p>'.InstallGetMessage("INS_LOAD_FROM_MARKETPLACE_DESCR").'</p>
			</div>
			<b class="r1"></b><b class="r1"></b><b class="r3"></b>
		</a>';

		$this->content .= '</div>
		<input type="hidden" id="id_'.htmlspecialchars($prefixName).'" name="'.htmlspecialchars($prefixName).'" value="">';
	}
}

class LoadModuleStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("load_module");
		$this->SetNextStep("select_wizard1");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetTitle(InstallGetMessage("INS_MODULE_LOADING"));
	}

	function OnPostForm()
	{
		global $DB, $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $DBDebug, $DBDebugToFile, $APPLICATION, $USER, $DBSQLServerType;

		require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include.php");

		@set_time_limit(3600);

		$wizard =& $this->GetWizard();
		$selectedModule = $wizard->GetVar("selected_module");
		$selectedModule = preg_replace("#[^a-z0-9._-]#i", "", $selectedModule);

		if (strlen($selectedModule)<=0)
		{
			$wizard->SetCurrentStep("select_wizard");
			return true;
		}

		//CUtil::InitJSCore(array('window'));
		$wizard->SetVar("nextStepStage", $selectedModule);
		$wizard->SetCurrentStep("load_module_action");
		return true;

		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$selectedModule))
		{
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");

			$selectedModule = preg_replace("#[^a-z0-9_.-]+#i", "", $selectedModule);

			$errorMessage = "";
			if (!CUpdateClientPartner::LoadModuleNoDemand($selectedModule, $errorMessage, "Y", LANGUAGE_ID))
			{
				$this->SetError($errorMessage);
				return;
			}
		}

		if (!IsModuleInstalled($selectedModule))
		{
			$module =& $this->GetModuleObject($selectedModule);
			if (!is_object($module))
				return;

			if (!$module->InstallDB())
			{
				if ($ex = $APPLICATION->GetException())
					$this->SetError($ex->GetString());
				return;
			}

			$module->InstallEvents();

			if (!$module->InstallFiles())
			{
				if ($ex = $APPLICATION->GetException())
					$this->SetError($ex->GetString());
				return;
			}
		}

		$arWizardsList = BXInstallServices::GetWizardsList($selectedModule);
		if (count($arWizardsList) == 1)
		{
			$arTmp = explode(":", $arWizardsList[0]["ID"]);
			$ar = array();
			foreach ($arTmp as $a)
			{
				$a = preg_replace("#[^a-z0-9_.-]+#i", "", $a);
				if (strlen($a) > 0)
					$ar[] = $a;
			}

			BXInstallServices::CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$ar[0]."/install/wizards/".$ar[1]."/".$ar[2],
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".$ar[1]."/".$ar[2],
				true,
				(defined("BX_DIR_PERMISSIONS") ? (BX_DIR_PERMISSIONS) : 0755),
				(defined("BX_FILE_PERMISSIONS") ? (BX_FILE_PERMISSIONS) : 0644)
			);

			if (BXInstallServices::CreateWizardIndex($ar[1].":".$ar[2]))
			{
				$u = "/index.php";
				if (defined("WIZARD_DEFAULT_SITE_ID"))
				{
					$rsSite = CSite::GetList($by="sort", $order="asc", array("ID" => WIZARD_DEFAULT_SITE_ID));
					$arSite = $rsSite->GetNext();

					$u = "";
					if (is_array($arSite["DOMAINS"]) && strlen($arSite["DOMAINS"][0]) > 0 || strlen($arSite["DOMAINS"]) > 0)
						$u .= "http://";
					if (is_array($arSite["DOMAINS"]))
						$u .= $arSite["DOMAINS"][0];
					else
						$u .= $arSite["DOMAINS"];
					$u .= $arSite["DIR"];
				}
				else
				{
					BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/license.html");
					BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/readme.html");
					BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/install.config");
				}

				if (defined("BX_UTF"))
					BXInstallServices::EncodeFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".LANGUAGE_ID."/install.php", INSTALL_CHARSET);

				BXInstallServices::LocalRedirect($u);
			}
			else
			{
				$this->SetError($errorMessageTmp);
			}
		}
		elseif (count($arWizardsList) == 0)
		{
			$wizard->SetCurrentStep("select_wizard");
			return true;
		}

		$wizard->SetVar("selected_module", $selectedModule);

		return true;
	}

	function &GetModuleObject($moduleID)
	{
		$installFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/install/index.php";
		if (!file_exists($installFile))
			return false;

		@include_once($installFile);
		$moduleIDTmp = str_replace(".", "_", $moduleID);
		if (!class_exists($moduleIDTmp))
			return false;

		return new $moduleIDTmp;
	}

	function ShowStep()
	{
		if (defined("BX_UTF"))
			define("INSTALL_UTF_PAGE", true);

		$wizard =& $this->GetWizard();
		$prefixName = $wizard->GetRealName("selected_module");

		$arModulesList = array();

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");

		if (defined("WIZARD_DEFAULT_TONLY") && WIZARD_DEFAULT_TONLY === true)
			$tTmp = 3;
		else
			$tTmp = array(3, 6);

		$arModules = CUpdateClientPartner::SearchModulesEx(
			array("NAME" => "ASC"),
			array("TYPE" => $tTmp),
			1,
			LANGUAGE_ID,
			$errorMessage
		);
		if (is_array($arModules["ERROR"]))
		{
			foreach ($arModules["ERROR"] as $e)
				$errorMessage .= (defined("BX_UTF") ? mb_convert_encoding($e["#"], PRE_INSTALL_CHARSET, "utf-8") : $e["#"]).". ";
		}
		if (defined("BX_UTF"))
			$errorMessage = mb_convert_encoding($errorMessage, PRE_INSTALL_CHARSET, "utf-8");

		if (is_array($arModules["MODULE"]))
		{
			foreach ($arModules["MODULE"] as $module)
			{
				$arModulesList[] = array(
					"ID" => $module["@"]["ID"],
					"NAME" => (defined("BX_UTF") ? mb_convert_encoding($module["@"]["NAME"], PRE_INSTALL_CHARSET, "utf-8") : $module["@"]["NAME"]),
					"DESCRIPTION" => (defined("BX_UTF") ? mb_convert_encoding($module["@"]["DESCRIPTION"], PRE_INSTALL_CHARSET, "utf-8") : $module["@"]["DESCRIPTION"]),
					"IMAGE" => $module["@"]["IMAGE"],
					"IMAGE_HEIGHT" => $module["@"]["IMAGE_HEIGHT"],
					"IMAGE_WIDTH" => $module["@"]["IMAGE_WIDTH"],
					"VERSION" => $module["@"]["VERSION"],
				);
			}
		}

		if (strlen($errorMessage) > 0)
			$this->SetError($errorMessage);

		$this->content = '
			<style type="text/css">
			#solutions-container
			{
				margin-bottom: 15px;
			}

			a.solution-item
			{
				display:block;
				border: 0;
				margin-bottom: 10px;
				color: Black;
				text-decoration: none;
				outline: none;
			}

			a.solution-item h4
			{
				margin: 10px;
				margin-top: 9px; /*compensating 1px padding*/
				font-family:Helvetica;
				font-size:1.5em;
			}
			a.solution-item p
			{
				margin: 10px;
			}

			div.solution-item-wrapper
			{
				width: 97px;
				float: left;
			}

			a.solution-picture-item
			{
				margin: 3px;
				text-align: center;
			}

			div.solution-description
			{
				margin-top: 3px;
				margin-left: 4px;
				color: #999;
				text-align:left;
			}

			a.solution-picture-item img.solution-image
			{
				width: 70px;
				float: none;
				margin: 7px 0px 7px;
			}

			img.solution-image
			{
				width: 100px;
				float: left;
				margin: 10px;
				border: 1px solid #CFCFCF;
			}
			div.solution-inner-item
			{
				padding: 1px;
				overflow: hidden;
				zoom: 1;
			}

			a.solution-item div.solution-inner-item,
			a.solution-item b
			{
				background-color:#F7F7F7;
				cursor: pointer;
				cursor: hand;
			}

			a.solution-item:hover div.solution-inner-item,
			a.solution-item:hover b
			{
				background-color: #FFF0B2;
			}

			a.solution-item-selected div.solution-inner-item,
			a.solution-item-selected b,
			a.solution-item-selected:hover div.solution-inner-item,
			a.solution-item-selected:hover b
			{
				background-color: #CADBEC;
			}

			#solution-preview
			{
				margin-top: 10px;
			}

			#solution-preview div.solution-inner-item,
			#solution-preview b
			{
				background-color:#F7F7F7;
			}

			#solution-preview div.solution-inner-item
			{
				padding: 10px;
				text-align: center;
			}

			#solution-preview-image
			{
				border: 1px solid #CFCFCF;
				width: 450px;
			}

			/* Round Corners */
			.r0, .r1, .r2, .r3, .r4 { overflow: hidden; font-size:1px; display: block; height: 1px;}
			.r4 { margin: 0 4px; }
			.r3 { margin: 0 3px; }
			.r2 { margin: 0 2px; }
			.r1 { margin: 0 1px; }
			</style>';

		$this->content .= '
		<script type="text/javascript">
			function SelectSolution(element, solutionId)
			{
				var container = document.getElementById("solutions-container");
				var anchors = container.getElementsByTagName("A");
				for (var i = 0; i < anchors.length; i++)
				{
					if (anchors[i].parentNode != container)
						continue;
					anchors[i].className = "solution-item";
				}
				element.className = "solution-item solution-item-selected";
				var hidden = document.getElementById("id_'.htmlspecialchars($prefixName).'");
				hidden.value = solutionId;

				document.getElementById("id_radio_"+solutionId).checked=true;
			}
		</script>
		';

		$this->content .= '<div id="solutions-container">';

		$arCurrentModules = CUpdateClientPartner::GetCurrentModules($errorMessage);

		foreach ($arModulesList as $m)
		{
			$bLoaded = array_key_exists($m["ID"], $arCurrentModules);
			$this->content .= '<a class="solution-item" href="javascript:void(0);" onclick="'.($bLoaded ? 'return false;' : 'SelectSolution(this, \''.htmlspecialchars($m["ID"]).'\');').'" ondblclick="'.($bLoaded ? 'return false;' : 'document.forms[\''.htmlspecialchars($wizard->GetFormName()).'\'].submit();').'">
				<b class="r3"></b><b class="r1"></b><b class="r1"></b>
				<div class="solution-inner-item">
					<input type="radio" id="id_radio_'.htmlspecialchars($m["ID"]).'" name="redio" style="float:left;margin-left:4px;margin-top:10px;margin-bottom:20px;"'.($bLoaded ? ' disabled' : '').'>
					'.(strlen($m["IMAGE"]) > 0 ? '<img alt="" src="'.htmlspecialchars($m["IMAGE"]).'" class="solution-image" />' : "").'
					'.($bLoaded ? '<p><i>'.InstallGetMessage("INS_MODULE_IS_ALREADY_LOADED").'</i></p>' : '').'
					<h4>'.$m["NAME"].'</h4>
					<p>'.$m["DESCRIPTION"].'</p>
				</div>
				<b class="r1"></b><b class="r1"></b><b class="r3"></b>
			</a>';
		}

		$this->content .= '<a class="solution-item" href="javascript:void(0);" onclick="SelectSolution(this, \'\');" ondblclick="document.forms[\''.htmlspecialchars($wizard->GetFormName()).'\'].submit();">
			<b class="r3"></b><b class="r1"></b><b class="r1"></b>
			<div class="solution-inner-item">
				<input type="radio" id="id_radio_" name="redio" style="float:left;margin-left:4px;margin-top:10px;margin-bottom:20px;">
				<h4>'.InstallGetMessage("INS_SKIP_MODULE_LOADING").'</h4>
				<p>'.InstallGetMessage("INS_SKIP_MODULE_LOADING_DESCR").'</p>
			</div>
			<b class="r1"></b><b class="r1"></b><b class="r3"></b>
		</a>';

		$this->content .= '</div>
		<input type="hidden" id="id_'.htmlspecialchars($prefixName).'" name="'.htmlspecialchars($prefixName).'" value="">';
	}
}

class LoadModuleActionStep extends CWizardStep
{
	var $arSteps = Array();
	var $singleSteps = Array();

	function InitStep()
	{
		$this->SetStepID("load_module_action");
		$this->SetTitle(InstallGetMessage("INS_MODULE_LOADING1"));
	}

	function OnPostForm()
	{
		global $DB, $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $DBDebug, $DBDebugToFile, $APPLICATION, $USER, $DBSQLServerType;
		require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include.php");

		@set_time_limit(3600);

		$wizard =& $this->GetWizard();
		$currentStep = $wizard->GetVar("nextStep");
		$selectedModule = $wizard->GetVar("nextStepStage");
		$selectedModule = preg_replace("#[^a-z0-9_.-]+#i", "", $selectedModule);

		if ($selectedModule == "skip")
		{
			$wizard->SetCurrentStep("select_wizard");
			return;
		}

		$this->singleSteps = Array(
			"do_load_module" => InstallGetMessage("INS_MODULE_LOADING"),
			"do_install_module" => InstallGetMessage("INS_MODULE_INSTALLING"),
			"do_load_wizard" => InstallGetMessage("INS_WIZARD_LOADING"),
		);

		$this->arSteps = array_keys($this->singleSteps);

		if (!in_array($currentStep, $this->arSteps))
		{
			if ($currentStep == "LocalRedirect")
			{
				$arWizardsList = BXInstallServices::GetWizardsList($selectedModule);

				$arTmp = explode(":", $arWizardsList[0]["ID"]);
				$ar = array();
				foreach ($arTmp as $a)
				{
					$a = preg_replace("#[^a-z0-9_.-]+#i", "", $a);
					if (strlen($a) > 0)
						$ar[] = $a;
				}

				if (BXInstallServices::CreateWizardIndex($ar[1].":".$ar[2]))
				{
					$u = "/index.php";
					if (defined("WIZARD_DEFAULT_SITE_ID"))
					{
						$rsSite = CSite::GetList($by="sort", $order="asc", array("ID" => WIZARD_DEFAULT_SITE_ID));
						$arSite = $rsSite->GetNext();

						$u = "";
						if (is_array($arSite["DOMAINS"]) && strlen($arSite["DOMAINS"][0]) > 0 || strlen($arSite["DOMAINS"]) > 0)
							$u .= "http://";
						if (is_array($arSite["DOMAINS"]))
							$u .= $arSite["DOMAINS"][0];
						else
							$u .= $arSite["DOMAINS"];
						$u .= $arSite["DIR"];
					}
					else
					{
						BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/license.html");
						BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/readme.html");
						BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/install.config");
					}

					if (defined("BX_UTF"))
						BXInstallServices::EncodeFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".LANGUAGE_ID."/install.php", INSTALL_CHARSET);

					BXInstallServices::LocalRedirect($u);
					return;
				}
			}
			else
			{
				$wizard->SetCurrentStep($currentStep);
				return;
			}
		}

		$nextStep = "do_load_module";
		$percent = 0;
		$status = "";

		if ($currentStep == "do_load_module")
		{
			if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$selectedModule))
			{
				require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");

				$errorMessage = "";
				if (!CUpdateClientPartner::LoadModuleNoDemand($selectedModule, $errorMessage, "Y", LANGUAGE_ID))
					$this->SendResponse("window.onbeforeunload = null; window.ajaxForm.StopAjax(); window.ajaxForm.SetStatus('0', '".$errorMessage."'); window.ajaxForm.ShowError('".$errorMessage."');");
			}
			$nextStep = "do_install_module";
			$status = $this->singleSteps["do_install_module"];
			$percent = 40;
		}
		elseif ($currentStep == "do_install_module")
		{
			if (!IsModuleInstalled($selectedModule))
			{
				$module =& $this->GetModuleObject($selectedModule);
				if (!is_object($module))
					$this->SendResponse("window.onbeforeunload = null; window.ajaxForm.StopAjax(); window.ajaxForm.SetStatus('0', '".InstallGetMessage("INS_MODULE_CANNOT_BE_INSTALLED")."');window.ajaxForm.ShowError('".InstallGetMessage("INS_MODULE_CANNOT_BE_INSTALLED")."');");

				if (!$module->InstallDB())
				{
					if ($ex = $APPLICATION->GetException())
						$this->SendResponse("window.onbeforeunload = null; window.ajaxForm.StopAjax(); window.ajaxForm.SetStatus('0', '".$ex->GetString()."');");
					else
						$this->SendResponse("window.onbeforeunload = null; window.ajaxForm.StopAjax(); window.ajaxForm.SetStatus('0', '".InstallGetMessage("INS_MODULE_DATABASE_ERROR")."');");
				}

				$module->InstallEvents();

				if (!$module->InstallFiles())
				{
					if ($ex = $APPLICATION->GetException())
						$this->SendResponse("window.onbeforeunload = null; window.ajaxForm.StopAjax(); window.ajaxForm.SetStatus('0', '".$ex->GetString()."');");
					else
						$this->SendResponse("window.onbeforeunload = null; window.ajaxForm.StopAjax(); window.ajaxForm.SetStatus('0', '".InstallGetMessage("INS_MODULE_FILES_ERROR")."');");
				}
			}
			$nextStep = "do_load_wizard";
			$status = $this->singleSteps["do_load_wizard"];
			$percent = 80;
		}
		elseif ($currentStep == "do_load_wizard")
		{
			$arWizardsList = BXInstallServices::GetWizardsList($selectedModule);
			if (count($arWizardsList) == 1)
			{
				$arTmp = explode(":", $arWizardsList[0]["ID"]);
				$ar = array();
				foreach ($arTmp as $a)
				{
					$a = preg_replace("#[^a-z0-9_.-]+#i", "", $a);
					if (strlen($a) > 0)
						$ar[] = $a;
				}

				BXInstallServices::CopyDirFiles(
					$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$ar[0]."/install/wizards/".$ar[1]."/".$ar[2],
					$_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".$ar[1]."/".$ar[2],
					true,
					(defined("BX_DIR_PERMISSIONS") ? (BX_DIR_PERMISSIONS) : 0755),
					(defined("BX_FILE_PERMISSIONS") ? (BX_FILE_PERMISSIONS) : 0644)
				);

				$nextStep = "LocalRedirect";
			}
			elseif (count($arWizardsList) == 0)
			{
				$nextStep = "select_wizard";
			}
			else
			{
				$nextStep = "select_wizard1";
			}
			$percent = 100;
			$status = $this->singleSteps["do_load_wizard"];
		}

		$response = "";
		if (!in_array($nextStep, $this->arSteps))
			$response .= "window.onbeforeunload = null; window.ajaxForm.StopAjax();";
		$response .= "window.ajaxForm.SetStatus('".$percent."'); window.ajaxForm.Post('".$nextStep."', '".$selectedModule."','".$status."');";

		$this->SendResponse($response);
	}

	function SendResponse($response)
	{
		header("Content-Type: text/html; charset=".INSTALL_CHARSET);
		die("[response]".$response."[/response]");
	}

	function &GetModuleObject($moduleID)
	{
		$installFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$moduleID."/install/index.php";
		if (!file_exists($installFile))
			return false;

		@include_once($installFile);
		$moduleIDTmp = str_replace(".", "_", $moduleID);
		if (!class_exists($moduleIDTmp))
			return false;

		return new $moduleIDTmp;
	}

	function ShowStep()
	{
		@include_once($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php");

		if (defined("BX_UTF"))
			define("INSTALL_UTF_PAGE", true);

		$wizard =& $this->GetWizard();
		$nextStepStage = $wizard->GetVar("nextStepStage");

		$this->content .= '
		<div id="result">
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr>
					<td colspan="2"><div id="status"></div></td>
				</tr>
				<tr>
					<td width=90% height="10">
						<div style="border:1px solid #B9CBDF">
						<div id="indicator" style="height:10px; width:0%; background-color:#B9CBDF"></div>
						</div>
					</td>
					<td width=10%>&nbsp;<span id="percent">0%</span></td>
				</tr>
			</table>
		</div>

		<div id="wait" align=center>
			<br /><br />
			<table width=200 cellspacing=0 cellpadding=0 border=0 style="border:1px solid #EFCB69" bgcolor="#FFF7D7">
				<tr>
					<td height=50 width="50" valign="middle" align=center><img src="/bitrix/images/install/wait.gif"></td>
					<td height=50 width=150>'.InstallGetMessage("INST_LOAD_WAIT").'</td>
				</tr>
			</table>
		</div><br />
		<div id="error_container" style="display:none">
			<div id="error_notice"><span style="color:red;">'.InstallGetMessage("INST_ERROR_OCCURED").'<br />'.InstallGetMessage("INST_TEXT_ERROR").':</span></div>
			<div id="error_text"></div>
			<div><span style="color:red;">'.InstallGetMessage("INST_ERROR_NOTICE").'</span></div>
			<div id="error_buttons" align="center">
			<br /><input type="button" value="'.InstallGetMessage("INST_RETRY_BUTTON").'" id="error_retry_button" style="display:none" onclick="" />&nbsp;<input type="button" id="error_skip_button" value="'.InstallGetMessage("INST_SKIP_BUTTON").'" onclick="" />&nbsp;</div>
		</div>

		'.$this->ShowHiddenField("nextStep", "do_load_module").'
		'.$this->ShowHiddenField("nextStepStage", $nextStepStage).'
		<iframe style="display:none;" id="iframe-post-form" name="iframe-post-form" src="javascript:\'\'"></iframe>
		';

		$wizard =& $this->GetWizard();

		$formName = $wizard->GetFormName();
		$NextStepVarName = $wizard->GetRealName("nextStep");

		$this->content .= '
			<script type="text/javascript">
				var ajaxForm = new CAjaxForm("'.$formName.'", "iframe-post-form", "'.$NextStepVarName.'");
				ajaxForm.Post("do_load_module", "'.$nextStepStage.'", "'.InstallGetMessage("INS_MODULE_LOADING").'");
			</script>
		';
	}

}

class SelectWizard1Step extends SelectWizardStep
{
	function InitStep()
	{
		$this->SetStepID("select_wizard1");
		$this->SetNextStep("finish");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetTitle(InstallGetMessage("INST_SELECT_WIZARD"));
	}

	function OnPostForm()
	{
		global $DB, $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $DBDebug, $DBDebugToFile, $APPLICATION, $USER, $DBSQLServerType;

		require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include.php");

		$wizard =& $this->GetWizard();
		$selectedWizard = $wizard->GetVar("selected_wizard");

		if (strlen($selectedWizard)<=0)
		{
			$this->SetError(InstallGetMessage("INS_WRONG_WIZARD"));
			return;
		}

		if ($selectedWizard == "@")
		{
			$wizard->SetCurrentStep("load_module");
			return true;
		}

		$arTmp = explode(":", $selectedWizard);
		$ar = array();
		foreach ($arTmp as $a)
		{
			$a = preg_replace("#[^a-z0-9_.-]+#i", "", $a);
			if (strlen($a) > 0)
				$ar[] = $a;
		}

		if (count($ar) > 2)
		{
			$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$ar[0]."/install/wizards/".$ar[1]."/".$ar[2];

			if (!file_exists($path) || !is_dir($path))
			{
				$this->SetError(InstallGetMessage("INS_WIZARD_NOT_FOUND"));
				return;
			}

			BXInstallServices::CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$ar[0]."/install/wizards/".$ar[1]."/".$ar[2],
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".$ar[1]."/".$ar[2],
				true,
				(defined("BX_DIR_PERMISSIONS") ? (BX_DIR_PERMISSIONS) : 0755),
				(defined("BX_FILE_PERMISSIONS") ? (BX_FILE_PERMISSIONS) : 0644)
			);

			$ar = array($ar[1], $ar[2]);
		}

		if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".$ar[0]."/".$ar[1])
			|| !is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".$ar[0]."/".$ar[1]))
		{
			$this->SetError(InstallGetMessage("INS_WIZARD_NOT_FOUND"));
			return;
		}

		if (BXInstallServices::CreateWizardIndex($ar[0].":".$ar[1], $errorMessageTmp))
		{
			$u = "/index.php";
			if (defined("WIZARD_DEFAULT_SITE_ID"))
			{
				$rsSite = CSite::GetList($by="sort", $order="asc", array("ID" => WIZARD_DEFAULT_SITE_ID));
				$arSite = $rsSite->GetNext();

				$u = "";
				if (is_array($arSite["DOMAINS"]) && strlen($arSite["DOMAINS"][0]) > 0 || strlen($arSite["DOMAINS"]) > 0)
					$u .= "http://";
				if (is_array($arSite["DOMAINS"]))
					$u .= $arSite["DOMAINS"][0];
				else
					$u .= $arSite["DOMAINS"];
				$u .= $arSite["DIR"];
			}
			else
			{
				BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/license.html");
				BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/readme.html");
				BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/install.config");
			}

			if (defined("BX_UTF"))
				BXInstallServices::EncodeFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".LANGUAGE_ID."/install.php", INSTALL_CHARSET);

			BXInstallServices::LocalRedirect($u);
		}
		else
		{
			$this->SetError($errorMessageTmp);
		}

		return true;
	}

	function ShowStep()
	{
		if (defined("BX_UTF"))
			define("INSTALL_UTF_PAGE", true);

		$wizard =& $this->GetWizard();
		$prefixName = $wizard->GetRealName("selected_wizard");

		$selectedModule = $wizard->GetVar("selected_module");

		$arWizardsList = BXInstallServices::GetWizardsList($selectedModule);

		$this->content = '
			<style type="text/css">
			#solutions-container
			{
				margin-bottom: 15px;
			}

			a.solution-item
			{
				display:block;
				border: 0;
				margin-bottom: 10px;
				color: Black;
				text-decoration: none;
				outline: none;
			}

			a.solution-item h4
			{
				margin: 10px;
				margin-top: 9px; /*compensating 1px padding*/
				font-family:Helvetica;
				font-size:1.5em;
			}
			a.solution-item p
			{
				margin: 10px;
			}

			div.solution-item-wrapper
			{
				width: 97px;
				float: left;
			}

			a.solution-picture-item
			{
				margin: 3px;
				text-align: center;
			}

			div.solution-description
			{
				margin-top: 3px;
				margin-left: 4px;
				color: #999;
				text-align:left;
			}

			a.solution-picture-item img.solution-image
			{
				width: 70px;
				float: none;
				margin: 7px 0px 7px;
			}

			img.solution-image
			{
				width: 100px;
				float: left;
				margin: 10px;
				border: 1px solid #CFCFCF;
			}
			div.solution-inner-item
			{
				padding: 1px;
				overflow: hidden;
				zoom: 1;
			}

			a.solution-item div.solution-inner-item,
			a.solution-item b
			{
				background-color:#F7F7F7;
				cursor: pointer;
				cursor: hand;
			}

			a.solution-item:hover div.solution-inner-item,
			a.solution-item:hover b
			{
				background-color: #FFF0B2;
			}

			a.solution-item-selected div.solution-inner-item,
			a.solution-item-selected b,
			a.solution-item-selected:hover div.solution-inner-item,
			a.solution-item-selected:hover b
			{
				background-color: #CADBEC;
			}

			#solution-preview
			{
				margin-top: 10px;
			}

			#solution-preview div.solution-inner-item,
			#solution-preview b
			{
				background-color:#F7F7F7;
			}

			#solution-preview div.solution-inner-item
			{
				padding: 10px;
				text-align: center;
			}

			#solution-preview-image
			{
				border: 1px solid #CFCFCF;
				width: 450px;
			}

			/* Round Corners */
			.r0, .r1, .r2, .r3, .r4 { overflow: hidden; font-size:1px; display: block; height: 1px;}
			.r4 { margin: 0 4px; }
			.r3 { margin: 0 3px; }
			.r2 { margin: 0 2px; }
			.r1 { margin: 0 1px; }
			</style>';

		$this->content .= '
		<script type="text/javascript">
			function SelectSolution(element, solutionId)
			{
				var container = document.getElementById("solutions-container");
				var anchors = container.getElementsByTagName("A");
				for (var i = 0; i < anchors.length; i++)
				{
					if (anchors[i].parentNode != container)
						continue;
					anchors[i].className = "solution-item";
				}
				element.className = "solution-item solution-item-selected";
				var hidden = document.getElementById("id_'.htmlspecialchars($prefixName).'");
				hidden.value = solutionId;

				document.getElementById("id_radio_"+solutionId).checked=true;
			}
		</script>
		';

		$this->content .= '<div id="solutions-container">';
		foreach ($arWizardsList as $w)
		{
			$this->content .= '<a class="solution-item" href="javascript:void(0);" onclick="SelectSolution(this, \''.htmlspecialchars($w["ID"]).'\');" ondblclick="document.forms[\''.htmlspecialchars($wizard->GetFormName()).'\'].submit();">
				<b class="r3"></b><b class="r1"></b><b class="r1"></b>
				<div class="solution-inner-item">
					<input type="radio" id="id_radio_'.htmlspecialchars($w["ID"]).'" name="redio" style="float:left;margin-left:4px;margin-top:10px;margin-bottom:20px;">
					'.(strlen($w["IMAGE"]) > 0 ? '<img alt="" src="'.htmlspecialchars($w["IMAGE"]).'" class="solution-image" />' : "").'
					<h4>'.$w["NAME"].'</h4>
					<p>'.$w["DESCRIPTION"].'</p>
				</div>
				<b class="r1"></b><b class="r1"></b><b class="r3"></b>
			</a>';
		}

		$this->content .= '<a class="solution-item" href="javascript:void(0);" onclick="SelectSolution(this, \'@\'); return false;" ondblclick="document.forms[\''.htmlspecialchars($wizard->GetFormName()).'\'].submit();">
			<b class="r3"></b><b class="r1"></b><b class="r1"></b>
			<div class="solution-inner-item">
				<input type="radio" id="id_radio_@" name="redio" style="float:left;margin-left:4px;margin-top:10px;margin-bottom:20px;">
				<h4>'.InstallGetMessage("INS_LOAD_FROM_MARKETPLACE").'</h4>
				<p>'.InstallGetMessage("INS_LOAD_FROM_MARKETPLACE_DESCR").'</p>
			</div>
			<b class="r1"></b><b class="r1"></b><b class="r3"></b>
		</a>';

		$this->content .= '</div>
		<input type="hidden" id="id_'.htmlspecialchars($prefixName).'" name="'.htmlspecialchars($prefixName).'" value="">';
	}
}

class FinishStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("finish");
		$this->SetTitle(InstallGetMessage("INS_STEP7_TITLE"));
	}

	function CreateNewIndex()
	{
		$handler = @fopen($_SERVER["DOCUMENT_ROOT"]."/index.php","wb");

		if (!$handler)
		{
			$this->SetError(InstallGetMessage("INST_INDEX_ACCESS_ERROR"));
			return;
		}

		$success = @fwrite($handler,
			'<'.'?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?'.'>'."\n".
			'<br /><a href="/bitrix/admin/">Control panel</a>'."\n".
			'<'.'?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?'.'>'
		);

		if (!$success)
		{
			$this->SetError(InstallGetMessage("INST_INDEX_ACCESS_ERROR"));
			return;
		}

		if (defined("BX_FILE_PERMISSIONS"))
			@chmod($_SERVER["DOCUMENT_ROOT"]."/index.php", BX_FILE_PERMISSIONS);

		fclose($handler);
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		$this->CreateNewIndex();

		BXInstallServices::DeleteDirRec($_SERVER["DOCUMENT_ROOT"]."/install.config");

		$this->content = '
		<p>'.InstallGetMessage("GRETTINGS").'</p>

		<b><a href="/bitrix/admin/sysupdate.php?lang='.LANGUAGE_ID.'">'.InstallGetMessage("GO_TO_REGISTER").'</a></b><br>'.InstallGetMessage("GO_TO_REGISTER_DESCR").'<br><br>

		<table width="100%" cellpadding="2" cellspacing="0" border="0">
			<tr>
				<td><a href="/bitrix/admin/index.php?lang='.LANGUAGE_ID.'"><img src="/bitrix/images/install/admin.gif" width="22" height="22" border="0" title="'.InstallGetMessage("GO_TO_CONTROL").'"></a></td>
				<td width="50%">&nbsp;<a href="/bitrix/admin/index.php?lang='.LANGUAGE_ID.'">'.InstallGetMessage("GO_TO_CONTROL").'</a></td>
				<td><a href="/"><img border="0" src="/bitrix/images/install/public.gif" width="22" height="22" title="'.InstallGetMessage("GO_TO_VIEW").'"></a></td>
				<td width="50%">&nbsp;<a href="/">'.InstallGetMessage("GO_TO_VIEW").'</a></td>
			</tr>
		</table>';
	}
}

class CheckLicenseKey extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("check_license_key");
		$this->SetNextStep("create_modules");
		$this->SetNextCaption(InstallGetMessage("NEXT_BUTTON"));
		$this->SetTitle(InstallGetMessage("INS_LICENSE_HEAD"));

		$wizard =& $this->GetWizard();
		if (defined("TRIAL_VERSION") || defined("TRIAL_RENT_VERSION"))
		{
			$wizard->SetDefaultVar("lic_key_variant", "Y");
		}

		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/license_key.php'))
		{
			$LICENSE_KEY = '';
			include($_SERVER['DOCUMENT_ROOT'].'/bitrix/license_key.php');
			$wizard->SetDefaultVar("license", $LICENSE_KEY);
		}
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();
		$licenseKey = $wizard->GetVar("license");
		global $DBType;

		if (!defined("TRIAL_VERSION") && !defined("TRIAL_RENT_VERSION") && function_exists("preg_match") && !preg_match('/[A-Z0-9]{3}-[A-Z]{2}-?[A-Z0-9]{12,18}/i', $licenseKey))
		{
			//$this->SetError(InstallGetMessage("BAD_LICENSE_KEY"), "license");
			//return;
		}

		if(defined("TRIAL_VERSION") || defined("TRIAL_RENT_VERSION"))
		{
			$lic_key_variant = $wizard->GetVar("lic_key_variant");

			if((defined("TRIAL_RENT_VERSION") || (defined("TRIAL_VERSION") && $lic_key_variant == "Y")) && strlen($licenseKey) <= 0)
			{
				$lic_key_user_surname = $wizard->GetVar("user_surname");
				$lic_key_user_name = $wizard->GetVar("user_name");
				$lic_key_email = $wizard->GetVar("email");

				$bError = false;
				if(trim($lic_key_user_name) == '')
				{
					$this->SetError(InstallGetMessage("ACT_KEY_BAD_NAME"), "user_name");
					$bError = true;
				}
				if(trim($lic_key_user_surname) == '')
				{
					$this->SetError(InstallGetMessage("ACT_KEY_BAD_LAST_NAME"), "user_surname");
					$bError = true;
				}
				if(trim($lic_key_email) == '' || !check_email($lic_key_email))
				{
					$this->SetError(InstallGetMessage("ACT_KEY_BAD_EMAIL"), "email");
					$bError = true;
				}

				if(!$bError)
				{
					$lic_site = $_SERVER["HTTP_HOST"];
					if(strlen($lic_site) <= 0)
						$lic_site = "localhost";

					$arClientModules = Array();
					$handle = @opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules");
					if ($handle)
					{
						while (false !== ($dir = readdir($handle)))
						{
							if (is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$dir)
								&& $dir!="." && $dir!="..")
							{
								$module_dir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$dir;
								if (file_exists($module_dir."/install/index.php"))
								{
									$arClientModules[] = $dir;
								}
							}
						}
						closedir($handle);
					}

					$lic_edition = serialize($arClientModules);

					if (defined("INSTALL_CHARSET") && strlen(INSTALL_CHARSET) > 0)
						$charset = INSTALL_CHARSET;
					else
						$charset = "windows-1251";

					if(LANGUAGE_ID == "ru")
						$host = "www.1c-bitrix.ru";
					else
						$host = "www.bitrixsoft.com";

					$path = "/bsm_register_key.php";
					$port = 80;
					$query = "sur_name=$lic_key_user_surname&first_name=$lic_key_user_name&email=$lic_key_email&site=$lic_site&modules=".urlencode($lic_edition)."&db=$DBType&lang=".LANGUAGE_ID."&bx=Y&max_users=".TRIAL_RENT_VERSION_MAX_USERS;

					if(defined("install_license_type"))
						$query .= "&cp_type=".install_license_type;
					if(defined("install_edition"))
						$query .= "&edition=".install_edition;

					$fp = @fsockopen("$host", "$port", $errnum, $errstr, 30);
					if ($fp)
					{
						fputs($fp, "POST {$path} HTTP/1.1\r\n");
						fputs($fp, "Host: {$host}\r\n");
						fputs($fp, "Content-type: application/x-www-form-urlencoded; charset=\"".$charset."\"\r\n");
						fputs($fp, "User-Agent: bitrixKeyReq\r\n");
						fputs($fp, "Content-length: ".(function_exists("mb_strlen")? mb_strlen($query, 'latin1'): strlen($query))."\r\n");
						fputs($fp, "Connection: close\r\n\r\n");
						fputs($fp, $query."\r\n\r\n");
						$page_content = "";
						$headersEnded = 0;
						while(!feof($fp))
						{
							$returned_data = fgets($fp, 128);
							if($returned_data=="\r\n")
							{
								$headersEnded = 1;
							}

							if($headersEnded==1)
							{
								$page_content .= htmlspecialchars($returned_data);
							}
						}
						fclose($fp);
					}
					$arContent = explode("\n", $page_content);

					$bEr = false;
					$bOk = false;
					$key = "";
					foreach($arContent as $v)
					{
						if($v == "ERROR")
							$bEr = true;
						elseif($v == "OK")
							$bOk = true;

						if(strlen($v) > 10)
							$key = trim($v);
					}

					if($bOk && strlen($key) >0)
					{
						$wizard->SetVar("license", $key);
					}
					elseif(defined("TRIAL_RENT_VERSION"))
						$this->SetError(InstallGetMessage("ACT_KEY_REQUEST_ERROR"), "email");
				}
			}
		}

		$this->CreateLicenseFile();
	}

	function CreateLicenseFile()
	{
		$wizard =& $this->GetWizard();
		$licenseKey = $wizard->GetVar("license");

		if (strlen($licenseKey) < 0)
			$licenseKey = "DEMO";

		$filePath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php";

		if (!$fp = @fopen($filePath, "wb"))
			return false;

		$fileContent = "<"."? \$"."LICENSE_KEY = \"".addslashes($licenseKey)."\"; ?".">";

		if (!fwrite($fp, $fileContent))
			return false;

		@fclose($fp);

		return true;
	}

	function ShowStep()
	{

		$this->content = '
		<table border="0" class="data-table">
			<tr>
				<td colspan="2" class="header">'.InstallGetMessage("INS_LICENSE_HEAD").'</td>
			</tr>';

		if(!defined("TRIAL_VERSION") && !defined("TRIAL_RENT_VERSION"))
		{
			$this->content .= '<tr>
				<td nowrap align="right" width="40%" valign="top">
					<span style="color:red">*</span>&nbsp;'.InstallGetMessage("INS_LICENSE").'
				</td>
				<td width="60%" valign="top">'.$this->ShowInputField("text", "license", Array("size" => "30", "tabindex" => "1", "id" =>"license_id")).'
					<br>
					<small>'.InstallGetMessage("INS_LICENSE_NOTE_SOURCE").'<br></small>
				</td>
				</tr>
				</table>
				';
		}
		else
		{
			$this->content .= '
			<script>
				function changeLicKey(val)
				{
					if(val)
					{
						document.getElementById("lic_key_activation").style.display = "block";
					}
					else
					{
						document.getElementById("lic_key_activation").style.display = "none";
					}
				}
			</script>

					';
			if(!defined("TRIAL_RENT_VERSION"))
				$this->content .= '<tr><td colspan="2">'.$this->ShowCheckboxField("lic_key_variant", "Y", Array("id" => "lic_key_variant", "onclick" => "javascript:changeLicKey(this.checked)")).'<label for="lic_key_variant">'.InstallGetMessage("ACT_KEY").'</label></td></tr>';

			$wizard =& $this->GetWizard();
			$lic_key_variant = $wizard->GetVar("lic_key_variant", $useDefault = true);
			$this->content .= '
			</table>
			<div id="lic_key_activation">
			<table border="0" class="data-table" style="border-top:none;">
			<tr>
				<td align="right" width="40%" style="border-top:none;"><span style="color:red">*</span>&nbsp;'.InstallGetMessage("ACT_KEY_NAME").':</td>
				<td width="60%" valign="top" style="border-top:none;">'.$this->ShowInputField("text", "user_name", Array("size" => "30", "tabindex" => "4", "id" => "user_name")).'</td>
			</tr>
			<tr>
				<td align="right" width="40%" style="border-top:none;"><span style="color:red">*</span>&nbsp;'.InstallGetMessage("ACT_KEY_LAST_NAME").':</td>
				<td width="60%" valign="top" style="border-top:none;">'.$this->ShowInputField("text", "user_surname", Array("size" => "30", "tabindex" => "5", "id" => "user_surname")).'</td>
			</tr>
			<tr>
				<td align="right" width="40%" style="border-top:none;"><span style="color:red">*</span>&nbsp;Email:</td>
				<td width="60%" valign="top" style="border-top:none;">'.$this->ShowInputField("text", "email", Array("size" => "30", "tabindex" => "6", "id" => "email")).'</td>
			</tr>
			</table>
			</div>
			<script>
			changeLicKey('.(($lic_key_variant == "Y") ? 'true' : 'false').');
			</script>
			';
		}
		//$this->content .= '</table>';
	}
}

//Create wizard
$wizard = new CWizardBase(str_replace("#VERS#", SM_VERSION, InstallGetMessage("INS_TITLE")), $package = null);

if (defined("WIZARD_DEFAULT_TONLY") && WIZARD_DEFAULT_TONLY === true)
{
	$arSteps = Array("SelectWizardStep", "LoadModuleStep", "LoadModuleActionStep", "SelectWizard1Step");
}
//Short installation
elseif (BXInstallServices::IsShortInstall() && BXInstallServices::CheckShortInstall())
{
    $arSteps = Array();
    if (defined("VM_INSTALL"))
    {
        //$arSteps = Array("AgreementStep4VM", "CheckLicenseKey", "CreateModulesStep", "CreateAdminStep");
		$arSteps = Array("AgreementStep4VM");
    }
    //else
	//{
        //if (!defined("TRIAL_VERSION"))
            $arSteps[] = "CheckLicenseKey";
        $arSteps[] = "CreateModulesStep";
        $arSteps[] = "CreateAdminStep";
		$arSteps[] = "SelectWizardStep";
		$arSteps[] = "LoadModuleStep";
		$arSteps[] = "LoadModuleActionStep";
		$arSteps[] = "SelectWizard1Step";
    //}
    if (BXInstallServices::GetDemoWizard() === false)
        $arSteps[] = "FinishStep";
}
//if (BXInstallServices::IsShortInstall() && BXInstallServices::CheckShortInstall())
//{
//	$arSteps = Array();
//
//	if (!defined("TRIAL_VERSION"))
//		$arSteps[] = "CheckLicenseKey";
//
//	$arSteps[] = "CreateModulesStep";
//	$arSteps[] = "CreateAdminStep";
//
//	if (BXInstallServices::GetDemoWizard() === false)
//		$arSteps[] = "FinishStep";
//}
else
{
	//Full installation
	$arSteps = Array("WelcomeStep", "AgreementStep", "DBTypeStep", "RequirementStep", "CreateDBStep", "CreateModulesStep", "CreateAdminStep");

	$arWizardsList = BXInstallServices::GetWizardsList();
	if (count($arWizardsList) > 0)
	{
		$arSteps[] = "SelectWizardStep";
		$arSteps[] = "LoadModuleStep";
		$arSteps[] = "LoadModuleActionStep";
		$arSteps[] = "SelectWizard1Step";
	}

	if (BXInstallServices::GetDemoWizard() === false)
		$arSteps[] = "FinishStep";
}

$wizard->AddSteps($arSteps); //Add steps
$wizard->SetTemplate(new WizardTemplate);
$wizard->SetReturnOutput();
$content = $wizard->Display();

if (defined("INSTALL_UTF_PAGE"))
{
	$pageCharset = "UTF-8";
	$content = mb_convert_encoding($content, "utf-8", INSTALL_CHARSET);
}
else
	$pageCharset = INSTALL_CHARSET;

header("Content-Type: text/html; charset=".$pageCharset);
echo $content;
?>
