<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/*
$catalogModulePermissions = $APPLICATION->GetGroupRight("catalog");
$CATALOG_RIGHT = $catalogModulePermissions;
if ($catalogModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
*/

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_export_edit') || $USER->CanDoOperation('catalog_export_exec')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bCanEdit = $USER->CanDoOperation('catalog_export_edit');
$bCanExec = $USER->CanDoOperation('catalog_export_exec');

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");

if ($ex = $APPLICATION->GetException())
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");
	
	$strError = $ex->GetString();
	ShowError($strError);
	
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

set_time_limit(0);
$strErrorMessage = "";
$strOKMessage = "";

/////////////////////////////////////////////////////////////////////
function GetReportsList($strPath2Export)
{
	$arReports = array();

	CheckDirPath($_SERVER["DOCUMENT_ROOT"].$strPath2Export);
	if ($handle = opendir($_SERVER["DOCUMENT_ROOT"].$strPath2Export))
	{
		while (($file = readdir($handle)) !== false)
		{
			if ($file == "." || $file == "..") continue;
			if (is_file($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$file) && substr($file, strlen($file)-8)=="_run.php")
			{
				$export_name = substr($file, 0, strlen($file)-8);

				$rep_title = $export_name;
				$file_handle = fopen($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$file, "rb");
				$file_contents = fread($file_handle, 1500);
				fclose($file_handle);

				$arMatches = array();
				if (preg_match("#<title[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches))
				{
					$arMatches[1] = Trim($arMatches[1]);
					if (strlen($arMatches[1])>0) $rep_title = $arMatches[1];
				}

				$arReports[$export_name] = array(
					"PATH" => $strPath2Export,
					"FILE_RUN" => $strPath2Export.$file,
					"TITLE" => $rep_title
					);
				if (file_exists($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$export_name."_setup.php"))
				{
					$arReports[$export_name]["FILE_SETUP"] = $strPath2Export.$export_name."_setup.php";
				}
			}
		}
	}
	closedir($handle);

	return $arReports;
}

$arReportsList = GetReportsList(CATALOG_PATH2EXPORTS);

/////////////////////////////////////////////////////////////////////
// В пошаговом мастере настройки 
//   $FINITE = True    на последнем шаге
//   $SETUP_FIELDS_LIST    список полей, сохраняемых в профиль экспорта, через запятую
//   $STEP    текущий шаг мастера
//   $SETUP_PROFILE_NAME    имя создаваемого профиля должно прийти из мастера, если $ACTION=="EXPORT_SETUP"
//   $SETUP_FILE_NAME    если установлен путь к файлу экспорта, то имя файла показывается после загрузки. Может быть относительным или абсолютным
//   $strExportErrorMessage    строка ошибок после выполнения экспорта
/////////////////////////////////////////////////////////////////////

if (/*$catalogModulePermissions=="W"*/ ($bCanEdit || $bCanExec) && check_bitrix_sessid())
{
	if (strlen($_REQUEST["ACTION"])>0 && strlen($_REQUEST["ACT_FILE"])<=0)
	{
		$strErrorMessage .= GetMessage("CES_ERROR_NO_FILE")."\n";
	}
	elseif (strlen($_REQUEST["ACTION"])<=0 && strlen($_REQUEST["ACT_FILE"])>0)
	{
		$strErrorMessage .= GetMessage("CES_ERROR_NO_ACTION")."\n";
	}

	if (strlen($strErrorMessage)<=0 && strlen($_REQUEST["ACTION"])>0)
	{
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$arReportsList[$_REQUEST["ACT_FILE"]]["FILE_RUN"])
			|| !is_file($_SERVER["DOCUMENT_ROOT"].$arReportsList[$_REQUEST["ACT_FILE"]]["FILE_RUN"])
			)
			$strErrorMessage .= GetMessage("CES_ERROR_FILE_NOT_EXIST")." (".$arReportsList[$_REQUEST["ACT_FILE"]]["FILE_RUN"].").\n";

		if (strlen($strErrorMessage)<=0)
		{
			$PROFILE_ID = IntVal($_REQUEST["PROFILE_ID"]);

			//////////////////////////////////////////////
			// ЭКСПОРТ
			//////////////////////////////////////////////
			if ($_REQUEST["ACTION"]=="EXPORT" && $bCanExec)
			{
				// Если профиль "по умолчанию", то
				//		Если есть setup, то setup и run с параметрами из setup
				//		Иначе run
				// Иначе инициализация параметров и run
				$bDefaultProfile = True;
				if ($PROFILE_ID > 0)
				{
					$ar_profile = CCatalogExport::GetByID($PROFILE_ID);
					if ($ar_profile)
					{
						if ($ar_profile["DEFAULT_PROFILE"] != "Y")
							$bDefaultProfile = False;
					}
					else
					{
						$PROFILE_ID = 0;
					}
				}

				// Если профиль не указан, то ищем профиль по умолчанию
				if ($PROFILE_ID <= 0)
				{
					$db_profile = CCatalogExport::GetList(
						array(),
						array("DEFAULT_PROFILE" => "Y", "FILE_NAME" => $_REQUEST["ACT_FILE"])
					);
					if ($ar_profile = $db_profile->Fetch())
						$PROFILE_ID = IntVal($ar_profile["ID"]);
				}

				if ($bDefaultProfile)
				{
					if (strlen($arReportsList[$_REQUEST["ACT_FILE"]]["FILE_SETUP"]) > 0)
					{
						$STEP = IntVal($_REQUEST["STEP"]);
						if (isset($_POST['backButton'])) $STEP-=2;
						if ($STEP <= 0) $STEP = 1;
						$FINITE = False;

						ob_start();
						$APPLICATION->SetTitle($arReportsList[$_REQUEST["ACT_FILE"]]["TITLE"]);
						include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
						
						// compatibility hack!
						$CATALOG_RIGHT = 'W';
						include($_SERVER["DOCUMENT_ROOT"].$arReportsList[$_REQUEST["ACT_FILE"]]["FILE_SETUP"]);
						
						if ($FINITE!==True)
						{
							ob_end_flush();
							include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
							die();
						}
						ob_end_clean();
					}
				}
				else
				{
					parse_str($ar_profile["SETUP_VARS"]);
				}

				$strExportErrorMessage = "";

				include($_SERVER["DOCUMENT_ROOT"].$arReportsList[$_REQUEST["ACT_FILE"]]["FILE_RUN"]);

				if (strlen($strExportErrorMessage) > 0)
					$strErrorMessage .= $strExportErrorMessage;

				if (strlen($strErrorMessage) <= 0)
				{
					if ($PROFILE_ID > 0)
					{
						CCatalogExport::Update(
							$PROFILE_ID, 
							array("=LAST_USE" => $DB->GetNowFunction())
						);
					}
					else
					{
						$PROFILE_ID = CCatalogExport::Add(
							array(
								"=LAST_USE" => $DB->GetNowFunction(),
								"FILE_NAME" => $_REQUEST["ACT_FILE"],
								"NAME" => $arReportsList[$_REQUEST["ACT_FILE"]]["TITLE"],
								"DEFAULT_PROFILE" => "Y",
								"IN_MENU" => "N",
								"IN_AGENT" => "N",
								"IN_CRON" => "N",
								"SETUP_VARS" => false
							)
						);
					}

					LocalRedirect("cat_export_setup.php?lang=".LANG."&success_export=Y&SETUP_FILE_NAME=".urlencode($SETUP_FILE_NAME));
				}
			}
			//////////////////////////////////////////////
			// МЕНЮ
			//////////////////////////////////////////////
			elseif ($_REQUEST["ACTION"]=="MENU" && $bCanEdit)
			{
				if ($PROFILE_ID>0)
				{
					$ar_profile = CCatalogExport::GetByID($PROFILE_ID);
					if (!$ar_profile)
						$PROFILE_ID = 0;
				}

				// Если профиль не указан, то ищем профиль по умолчанию
				if ($PROFILE_ID <= 0)
				{
					$db_profile = CCatalogExport::GetList(array(), array("DEFAULT_PROFILE"=>"Y", "FILE_NAME"=>$_REQUEST["ACT_FILE"]));
					if ($ar_profile = $db_profile->Fetch())
						$PROFILE_ID = IntVal($ar_profile["ID"]);
				}

				$arFields = array(
					"ID" => $_REQUEST["ACT_FILE"],
					"NAME" => $arReportsList[$_REQUEST["ACT_FILE"]]["TITLE"],
					"IN_MENU" => "Y"
					);

				if ($PROFILE_ID > 0)
				{
					CCatalogExport::Update(
						$PROFILE_ID,
						array("IN_MENU" => ($ar_profile["IN_MENU"]=="Y" ? "N" : "Y"))
					);
				}
				else
				{
					$PROFILE_ID = CCatalogExport::Add(array(
						"LAST_USE"		=> false,
						"FILE_NAME"		=> $_REQUEST["ACT_FILE"],
						"NAME"			=> $arReportsList[$_REQUEST["ACT_FILE"]]["TITLE"],
						"DEFAULT_PROFILE" => "Y",
						"IN_MENU"		=> "Y",
						"IN_AGENT"		=> "N",
						"IN_CRON"		=> "N",
						"SETUP_VARS"	=> false
						));
				}

				if (strlen($strErrorMessage)<=0)
				{
					LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&success_export=Y");
				}
			}
			//////////////////////////////////////////////
			// АГЕНТ
			//////////////////////////////////////////////
			elseif ($_REQUEST["ACTION"]=="AGENT" && $USER->CanDoOperation('edit_php'))
			{
				$bDefaultProfile = True;
				if ($PROFILE_ID>0)
				{
					$ar_profile = CCatalogExport::GetByID($PROFILE_ID);
					if ($ar_profile)
					{
						if ($ar_profile["DEFAULT_PROFILE"] != "Y")
							$bDefaultProfile = False;
					}
					else
					{
						$PROFILE_ID = 0;
					}
				}

				// Если профиль не указан, то ищем профиль по умолчанию
				if ($PROFILE_ID<=0)
				{
					$db_profile = CCatalogExport::GetList(array(), array("DEFAULT_PROFILE"=>"Y", "FILE_NAME"=>$_REQUEST["ACT_FILE"]));
					if ($ar_profile = $db_profile->Fetch())
					{
						$PROFILE_ID = IntVal($ar_profile["ID"]);
					}
				}

				if ($bDefaultProfile && strlen($arReportsList[$_REQUEST["ACT_FILE"]]["FILE_SETUP"])>0)
				{
					$strErrorMessage .= GetMessage("CES_ERROR_NOT_AGENT")."\n";
				}

				if (strlen($strErrorMessage)<=0)
				{
					$agent_period = IntVal($_REQUEST["agent_period"]);
					if ($agent_period<=0) $agent_period = 24;

					if ($PROFILE_ID>0)
					{
						if ($ar_profile["IN_AGENT"]=="Y")
							CAgent::RemoveAgent("CCatalogExport::PreGenerateExport(".$PROFILE_ID.");", "catalog");
						else
							CAgent::AddAgent("CCatalogExport::PreGenerateExport(".$PROFILE_ID.");", "catalog", "N", $agent_period*60*60, "", "Y");

						CCatalogExport::Update($PROFILE_ID, array(
							"IN_AGENT" => ($ar_profile["IN_AGENT"]=="Y" ? "N" : "Y")
							));
					}
					else
					{
						$PROFILE_ID = CCatalogExport::Add(array(
							"LAST_USE"		=> false,
							"FILE_NAME"		=> $_REQUEST["ACT_FILE"],
							"NAME"			=> $arReportsList[$_REQUEST["ACT_FILE"]]["TITLE"],
							"DEFAULT_PROFILE" => "Y",
							"IN_MENU"		=> "N",
							"IN_AGENT"		=> "Y",
							"IN_CRON"		=> "N",
							"SETUP_VARS"	=> false
							));
						if (IntVal($PROFILE_ID)>0)
						{
							CAgent::AddAgent("CCatalogExport::PreGenerateExport(".$PROFILE_ID.");", "catalog", "N", $agent_period*60*60, "", "Y");
						}
						else
						{
							$strErrorMessage .= GetMessage("CES_ERROR_ADD_PROFILE")."\n";
						}
					}
				}

				if (strlen($strErrorMessage)<=0)
				{
					LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&success_export=Y");
				}
			}
			//////////////////////////////////////////////
			// CRON
			//////////////////////////////////////////////
			elseif ($_REQUEST["ACTION"]=="CRON" && $USER->CanDoOperation('edit_php'))
			{
				$bDefaultProfile = True;
				if ($PROFILE_ID>0)
				{
					$ar_profile = CCatalogExport::GetByID($PROFILE_ID);
					if ($ar_profile)
					{
						if ($ar_profile["DEFAULT_PROFILE"]!="Y") $bDefaultProfile = False;
					}
					else
					{
						$PROFILE_ID = 0;
					}
				}

				// Если профиль не указан, то ищем профиль по умолчанию
				if ($PROFILE_ID<=0)
				{
					$db_profile = CCatalogExport::GetList(array(), array("DEFAULT_PROFILE"=>"Y", "FILE_NAME"=>$_REQUEST["ACT_FILE"]));
					if ($ar_profile = $db_profile->Fetch())
					{
						$PROFILE_ID = IntVal($ar_profile["ID"]);
					}
				}

				if ($bDefaultProfile && strlen($arReportsList[$_REQUEST["ACT_FILE"]]["FILE_SETUP"])>0)
				{
					$strErrorMessage .= GetMessage("CES_ERROR_NOT_CRON")."\n";
				}

				if (strlen($strErrorMessage)<=0)
				{
					$agent_period = IntVal($_REQUEST["agent_period"]);
					$agent_hour = Trim($_REQUEST["agent_hour"]);
					$agent_minute = Trim($_REQUEST["agent_minute"]);

					if ($agent_period<=0 && (strlen($agent_hour)<=0 || strlen($agent_minute)<=0))
					{
						$agent_period = 24;
						$agent_hour = "";
						$agent_minute = "";
					}
					elseif ($agent_period>0 && strlen($agent_hour)>0 && strlen($agent_minute)>0)
					{
						$agent_period = 0;
					}

					$agent_php_path = Trim($_REQUEST["agent_php_path"]);
					if (strlen($agent_php_path)<=0) $agent_php_path = "/usr/local/php/bin/php";

					if (!file_exists($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."cron_frame.php"))
					{
						CheckDirPath($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS);
						$tmp_file_size = filesize($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS_DEF."cron_frame.php");
						$fp = fopen($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS_DEF."cron_frame.php", "rb");
						$tmp_data = fread($fp, $tmp_file_size);
						fclose($fp);

						$tmp_data = str_replace("#DOCUMENT_ROOT#", $_SERVER["DOCUMENT_ROOT"], $tmp_data);
						$tmp_data = str_replace("#PHP_PATH#", $agent_php_path, $tmp_data);

						$fp = fopen($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."cron_frame.php", "ab");
						fwrite($fp, $tmp_data);
						fclose($fp);
					}

					$cfg_data = "";
					if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg"))
					{
						$cfg_file_size = filesize($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg");
						$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", "rb");
						$cfg_data = fread($fp, $cfg_file_size);
						fclose($fp);
					}

					CheckDirPath($_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."logs/");
					if ($PROFILE_ID>0)
					{
						if ($ar_profile["IN_CRON"]=="Y")
						{
							// remove
							$cfg_data = preg_replace("#^.*?".preg_quote(CATALOG_PATH2EXPORTS)."cron_frame.php +".$PROFILE_ID." *>.*?$#im", "", $cfg_data);
						}
						else
						{
							if ($agent_period>0)
								$strTime = "* */".$agent_period." * * * ";
							else
								$strTime = IntVal($agent_minute)." ".IntVal($agent_hour)." * * * ";

							// add
							if (strlen($cfg_data)>0) $cfg_data .= "\n";
							$cfg_data .= $strTime.$agent_php_path." -f ".$_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."cron_frame.php ".$PROFILE_ID." >".$_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."logs/".$PROFILE_ID.".txt\n";
						}

						CCatalogExport::Update($PROFILE_ID, array(
							"IN_CRON" => ($ar_profile["IN_CRON"]=="Y" ? "N" : "Y")
							));
					}
					else
					{
						$PROFILE_ID = CCatalogExport::Add(array(
							"LAST_USE"		=> false,
							"FILE_NAME"		=> $_REQUEST["ACT_FILE"],
							"NAME"			=> $arReportsList[$_REQUEST["ACT_FILE"]]["TITLE"],
							"DEFAULT_PROFILE" => "Y",
							"IN_MENU"		=> "N",
							"IN_AGENT"		=> "N",
							"IN_CRON"		=> "Y",
							"SETUP_VARS"	=> false
							));
						if (IntVal($PROFILE_ID)>0)
						{
							// add
							if ($agent_period>0)
								$strTime = "* */".$agent_period." * * * ";
							else
								$strTime = IntVal($agent_minute)." ".IntVal($agent_hour)." * * * ";

							if (strlen($cfg_data)>0) $cfg_data .= "\n";
							$cfg_data .= $strTime.$agent_php_path." -f ".$_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."cron_frame.php ".$PROFILE_ID." >".$_SERVER["DOCUMENT_ROOT"].CATALOG_PATH2EXPORTS."logs/".$PROFILE_ID.".txt\n";
						}
						else
						{
							$strErrorMessage .= GetMessage("CES_ERROR_ADD_PROFILE")."\n";
						}
					}
					if (strlen($strErrorMessage)<=0)
					{
						CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/");
						$cfg_data = preg_replace("#[\r\n]{2,}#im", "\n", $cfg_data);
						$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", "wb");
						fwrite($fp, $cfg_data);
						fclose($fp);

						if ($_REQUEST["auto_cron_tasks"]=="Y")
						{
							$arRetval = array();
							@exec("crontab ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", $arRetval, $return_var);
							if (IntVal($return_var)!=0)
							{
								$strErrorMessage .= GetMessage("CES_ERROR_ADD2CRON")." \n";
								if (is_array($arRetval) && count($arRetval)>0)
								{
									for ($ier = 0; $ier < count($arRetval); $ier++)
									{
										$strErrorMessage .= $arRetval[$i]." \n";
									}
								}
								else
								{
									$strErrorMessage .= GetMessage("CES_ERROR_UNKNOWN")."\n";
								}
							}
						}
					}
				}

				if (strlen($strErrorMessage)<=0)
				{
					LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&success_export=Y");
				}
			}
			//////////////////////////////////////////////
			// DEL_PROFILE
			//////////////////////////////////////////////
			elseif ($_REQUEST["ACTION"]=="DEL_PROFILE" && $bCanEdit)
			{
				$ar_profile = CCatalogExport::GetByID($PROFILE_ID);
				if (!$ar_profile)
					$strErrorMessage .= GetMessage("CES_ERROR_NO_PROFILE1").$PROFILE_ID." ".GetMessage("CES_ERROR_NO_PROFILE2")."\n";

				if (strlen($strErrorMessage)<=0)
				{
					if ($ar_profile["IN_AGENT"]=="Y")
					{
						CAgent::RemoveAgent("CCatalogExport::PreGenerateExport(".$PROFILE_ID.");", "catalog");
					}
					if ($ar_profile["IN_CRON"]=="Y")
					{
						$cfg_data = "";
						if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg"))
						{
							$cfg_file_size = filesize($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg");
							$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", "rb");
							$cfg_data = fread($fp, $cfg_file_size);
							fclose($fp);

							$cfg_data = preg_replace("#^.*?".preg_quote(CATALOG_PATH2EXPORTS)."cron_frame.php +".$PROFILE_ID." *>.*?$#im", "", $cfg_data);

							$cfg_data = preg_replace("#[\r\n]{2,}#im", "\n", $cfg_data);
							$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", "wb");
							fwrite($fp, $cfg_data);
							fclose($fp);

							$arRetval = array();
							@exec("crontab ".$_SERVER["DOCUMENT_ROOT"]."/bitrix/crontab/crontab.cfg", $arRetval, $return_var);
							if (IntVal($return_var)!=0)
							{
								$strErrorMessage .= GetMessage("CES_ERROR_ADD2CRON")." \n";
								if (is_array($arRetval) && count($arRetval)>0)
								{
									for ($ier = 0; $ier < count($arRetval); $ier++)
									{
										$strErrorMessage .= $arRetval[$i]." \n";
									}
								}
								else
								{
									$strErrorMessage .= GetMessage("CES_ERROR_UNKNOWN")."\n";
								}
							}
						}
					}
					CCatalogExport::Delete($PROFILE_ID);
				}
			}
			//////////////////////////////////////////////
			// EXPORT_SETUP
			//////////////////////////////////////////////
			elseif ($_REQUEST["ACTION"]=="EXPORT_SETUP" && $bCanEdit)
			{
				if (strlen($arReportsList[$_REQUEST["ACT_FILE"]]["FILE_SETUP"])>0)
				{
					$STEP = IntVal($_REQUEST["STEP"]);
					if (isset($_POST['backButton'])) $STEP-=2;
					if ($STEP<=0) $STEP = 1;
					$FINITE = False;


					
					ob_start();
					$APPLICATION->SetTitle($arReportsList[$_REQUEST["ACT_FILE"]]["TITLE"]);
					// compatibility hack!
					$CATALOG_RIGHT = 'W';
					include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
					include($_SERVER["DOCUMENT_ROOT"].$arReportsList[$_REQUEST["ACT_FILE"]]["FILE_SETUP"]);

					if ($FINITE!==True)
					{
						ob_end_flush();
						include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
						die();
					}
					ob_end_clean();

					// Сохранение профиля экспорта
					if (strlen($SETUP_FIELDS_LIST)<=0) $SETUP_FIELDS_LIST = $_REQUEST["SETUP_FIELDS_LIST"];
					$arProfileFields = Split(",", $SETUP_FIELDS_LIST);
					$strSETUP_VARS = "";
					for ($i = 0; $i < count($arProfileFields); $i++)
					{
						$arProfileFields[$i] = Trim($arProfileFields[$i]);

						$vValue = ${$arProfileFields[$i]};
						if (strlen($vValue)<=0) $vValue = $_REQUEST[$arProfileFields[$i]];

						if (is_array($vValue))
						{
							foreach ($vValue as $key1 => $value1)
							{
								if (strlen($strSETUP_VARS)>0) $strSETUP_VARS .= "&";
								$strSETUP_VARS .= $arProfileFields[$i]."[".(is_numeric($key1)?"":"\"").$key1.(is_numeric($key1)?"":"\"")."]=".urlencode($value1);
							}
						}
						else
						{
							if (strlen($strSETUP_VARS)>0) $strSETUP_VARS .= "&";
							$strSETUP_VARS .= $arProfileFields[$i]."=".urlencode($vValue);
						}
					}

					if (strlen($SETUP_PROFILE_NAME)<=0) $SETUP_PROFILE_NAME = $_REQUEST["SETUP_PROFILE_NAME"];
					if (strlen($SETUP_PROFILE_NAME)<=0) $SETUP_PROFILE_NAME = $arReportsList[$_REQUEST["ACT_FILE"]]["TITLE"];

					$PROFILE_ID = CCatalogExport::Add(array(
						"LAST_USE"		=> false,
						"FILE_NAME"		=> $_REQUEST["ACT_FILE"],
						"NAME"			=> $SETUP_PROFILE_NAME,
						"DEFAULT_PROFILE" => "N",
						"IN_MENU"		=> "N",
						"IN_AGENT"		=> "N",
						"IN_CRON"		=> "N",
						"SETUP_VARS"	=> $strSETUP_VARS
						));

					if (IntVal($PROFILE_ID)<=0)
					{
						$strErrorMessage .= GetMessage("CES_ERROR_SAVE_PROFILE")."\n";
					}
				}
				else
				{
					$strErrorMessage .= GetMessage("CES_ERROR_NO_SETUP_FILE")."\n";
				}
				if (strlen($strErrorMessage)<=0)
				{
					LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&success_export=Y");
				}
			}
		}
	}
}
/////////////////////////////////////////////////////////////////////

// Set NEW_OS in GET string to test other operational systems!!!
$bWindowsHosting = False;
if (isset($_GET["NEW_OS"]))
{
	if (strlen(trim($_GET["NEW_OS"]))<=0)
		unset($_SESSION["TMP_MY_NEW_OS"]);
	else
		$_SESSION["TMP_MY_NEW_OS"] = $_GET["NEW_OS"];
}
$strCurrentOS = PHP_OS;
if (isset($_SESSION["TMP_MY_NEW_OS"]) && strlen($_SESSION["TMP_MY_NEW_OS"])>0)
	$strCurrentOS = $_SESSION["TMP_MY_NEW_OS"];
if (strtoupper(substr($strCurrentOS, 0, 3)) === "WIN")
{
   $bWindowsHosting = True;
}

$sTableID = "export_setup";
// инициализация списка
$lAdmin = new CAdminList($sTableID);

// заголовок списка
$lAdmin->AddHeaders(array(
	array("id"=>"NAME", "content"=>GetMessage("export_setup_name"), "default"=>true),
	array("id"=>"FILE", "content"=>GetMessage("export_setup_file"), "default"=>true),
	array("id"=>"PROFILE", "content"=>GetMessage("CES_PROFILE"), "default"=>true),
	array("id"=>"IN_MENU", "content"=>GetMessage("CES_IN_MENU"), "default"=>true),
	array("id"=>"IN_AGENT", "content"=>GetMessage("CES_IN_AGENT"), "default"=>true),
	array("id"=>"IN_CRON", "content"=>GetMessage("CES_IN_CRON"), "default"=>true),
	array("id"=>"USED", "content"=>GetMessage("CES_USED"), "default"=>true),
));
?>

<div id="agents_form_shadow" style="display:none;" class="float-form-shadow">&nbsp;</div>
<?
$index_form = 0;
$index_vars = 0;
$aAdd = array();
foreach($arReportsList as $ReportFile=>$ReportParams):
	if(strlen($ReportParams["FILE_SETUP"])>0)
		$aAdd[] = array(
			"TEXT"=>htmlspecialchars($ReportParams["TITLE"]),
			"TITLE"=>GetMessage("export_setup_script")." &quot;".$ReportFile."&quot;",
			"ACTION"=>"window.location='".addslashes($APPLICATION->GetCurPage()."?lang=".LANG."&ACT_FILE=".urlencode($ReportFile)."&ACTION=EXPORT_SETUP"."&".bitrix_sessid_get())."';"
		);

	$db_prof_res = CCatalogExport::GetList(array("DEFAULT_PROFILE"=>"DESC", "LAST_USE"=>"DESC", "NAME"=>"ASC"), array("FILE_NAME"=>$ReportFile));
	$ar_prof_res = $db_prof_res->Fetch();

	if(!$ar_prof_res || $ar_prof_res["DEFAULT_PROFILE"]!="Y"):
		if(strlen($ReportParams["FILE_SETUP"])<=0):
?>
<div id="agents_form_<?echo $index_form ?>" style="display:none;" class="float-form">
<form name="agentsform_<?echo $index_form ?>" action="<?echo $APPLICATION->GetCurPage() ?>?lang=<?echo LANG ?>&amp;ACT_FILE=<?echo urlencode($ReportFile) ?>&amp;<?= bitrix_sessid_get() ?>&amp;ACTION=AGENT&amp;PROFILE_ID=" method="post">
<table cellspacing="0">
	<tr>
		<td><?echo GetMessage("CES_RUN_INTERVAL");?></td>
		<td><input type="text" name="agent_period" value="" size="10"></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" value="<?echo GetMessage("CES_SET");?>">
			<input type="button" value="<?echo GetMessage("CES_CLOSE");?>" onclick="hideAgentsForm(<?echo $index_form ?>, 'agents')">
		</td>
	</tr>
</table>
</form>
</div>

<div id="cron_form_<?echo $index_form ?>" style="display:none;" class="float-form">
<form name="cronform_<?echo $index_form ?>" action="<?echo $APPLICATION->GetCurPage() ?>?lang=<?echo LANG ?>&amp;ACT_FILE=<?echo urlencode($ReportFile) ?>&amp;<?= bitrix_sessid_get() ?>&amp;ACTION=CRON&amp;PROFILE_ID=" method="post">
<table cellspacing="0">
	<tr>
		<td><?echo GetMessage("CES_RUN_INTERVAL");?></td>
		<td><input type="text" name="agent_period" value="" size="10"></td>
	</tr>
	<tr>
		<td colspan="2"><b><?echo GetMessage("CES_OR");?></b></td>
	</tr>
	<tr>
		<td><?echo GetMessage("CES_RUN_TIME");?></td>
		<td><input type="text" name="agent_hour" value="" size="2">:<input type="text" name="agent_minute" value="" size="2"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("CES_PHP_PATH");?></td>
		<td><input type="text" name="agent_php_path" value="/usr/local/php/bin/php" size="25"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("CES_AUTO_CRON");?></td>
		<td><input type="checkbox" name="auto_cron_tasks" value="Y"></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" value="<?echo GetMessage("CES_SET");?>">
			<input type="button" value="<?echo GetMessage("CES_CLOSE");?>" onclick="hideAgentsForm(<?echo $index_form ?>, 'cron')">
		</td>
	</tr>
</table>
</form>
</div>
<?
			$index_form++;
		endif;
	endif; //!$ar_prof_res || $ar_prof_res["DEFAULT_PROFILE"]!="Y"

	if($ar_prof_res):
		do
		{
			if($ar_prof_res["DEFAULT_PROFILE"]!="Y"):
?>
<div id="vars_div_<?echo $index_vars?>" style="display:none;" class="float-form">
<div class="data">
	<?
	$arVars = explode('&', preg_replace("/[\n\r]+/i", "", $ar_prof_res["SETUP_VARS"]));
	foreach ($arVars as $key => $value) $arVars[$key] = htmlspecialchars(urldecode($value));
	echo implode('<br />', $arVars);
	?>
	<?//echo urldecode(str_replace("&", "<br />", preg_replace("/[\n\r]+/i", "", $ar_prof_res["SETUP_VARS"])))?>
</div>
<div align="center">
	<input type="button" value="<?echo GetMessage("CES_CLOSE");?>" onclick="hideFloatDiv('vars_div_<?echo $index_vars?>')">
</div>
</div>
<?			
				$index_vars++;
			endif;
			if($ar_prof_res["DEFAULT_PROFILE"]!="Y" || strlen($ReportParams["FILE_SETUP"])<=0):
?>
<div id="agents_form_<?echo $index_form ?>" style="display:none;" class="float-form">
<form name="agentsform_<?echo $index_form ?>" action="<?echo $APPLICATION->GetCurPage() ?>?lang=<?echo LANG ?>&amp;ACT_FILE=<?echo urlencode($ReportFile)?>&amp;<?= bitrix_sessid_get() ?>&amp;ACTION=AGENT&amp;PROFILE_ID=<?echo $ar_prof_res["ID"] ?>" method="post">
<table cellspacing="0">
	<tr>
		<td><?echo GetMessage("CES_RUN_INTERVAL");?></td>
		<td><input type="text" name="agent_period" value="" size="10"></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" value="<?echo GetMessage("CES_SET");?>">
			<input type="button" value="<?echo GetMessage("CES_CLOSE");?>" onclick="hideAgentsForm(<?echo $index_form ?>, 'agents')">
		</td>
	</tr>
</table>
</form>
</div>

<div id="cron_form_<?echo $index_form ?>" style="display:none;" class="float-form">
<form name="cronform_<?echo $index_form ?>" action="<?echo $APPLICATION->GetCurPage() ?>?lang=<?echo LANG ?>&amp;ACT_FILE=<?echo urlencode($ReportFile)?>&amp;<?= bitrix_sessid_get() ?>&amp;ACTION=CRON&amp;PROFILE_ID=<?echo $ar_prof_res["ID"] ?>" method="post">
<table cellspacing="0">
	<?if ($ar_prof_res["IN_CRON"]!="Y"):?>
		<tr>
			<td><?echo GetMessage("CES_RUN_INTERVAL");?></td>
			<td><input type="text" name="agent_period" value="" size="10"></td>
		</tr>
		<tr>
			<td colspan="2"><b><?echo GetMessage("CES_OR");?></b></td>
		</tr>
		<tr>
			<td><?echo GetMessage("CES_RUN_TIME");?></td>
			<td><input type="text" name="agent_hour" value="" size="2">:<input type="text" name="agent_minute" value="" size="2"></td>
		</tr>
		<tr>
			<td><?echo GetMessage("CES_PHP_PATH");?></td>
			<td><input type="text" name="agent_php_path" value="/usr/local/php/bin/php" size="25"></td>
		</tr>
	<?endif;?>
	<tr>
		<td><?echo ($ar_prof_res["IN_CRON"]=="Y") ? GetMessage("CES_AUTO_CRON_DEL") : GetMessage("CES_AUTO_CRON");?></td>
		<td><input type="checkbox" name="auto_cron_tasks" value="Y"></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" value="<?echo ($ar_prof_res["IN_CRON"]=="Y") ? GetMessage("CES_DELETE") : GetMessage("CES_SET");?>">
			<input type="button" value="<?echo GetMessage("CES_CLOSE");?>" onclick="hideAgentsForm(<?echo $index_form ?>, 'cron')">
		</td>
	</tr>
</table>
</form>
</div>
<?
				$index_form++;
			endif;
		}
		while($ar_prof_res = $db_prof_res->Fetch());
	endif;
endforeach;


//$lAdmin->BeginPrologContent();
//$lAdmin->EndPrologContent();

$aContext = array();
if(!empty($aAdd) && $bCanEdit)
{
	$aContext[] = array(
		"TEXT"=>GetMessage("CES_ADD_PROFILE"),
		"TITLE"=>GetMessage("CES_ADD_PROFILE_DESCR"),
		"ICON"=>"btn_new",
		"MENU"=>$aAdd
	);
}
$lAdmin->AddAdminContextMenu($aContext, false);

$n = 0;
$index_form = 0;
$index_vars = 0;
foreach($arReportsList as $ReportFile=>$ReportParams)
{
	// построение списка
	$db_prof_res = CCatalogExport::GetList(array("DEFAULT_PROFILE"=>"DESC", "LAST_USE"=>"DESC", "NAME"=>"ASC"), array("FILE_NAME"=>$ReportFile));
	$ar_prof_res = $db_prof_res->Fetch();

	if(!$ar_prof_res || $ar_prof_res["DEFAULT_PROFILE"]!="Y")
	{
		$row = &$lAdmin->AddRow(0, null);

		$row->AddField("NAME", htmlspecialchars($ReportParams["TITLE"]));
		$row->AddField("FILE", $ReportFile);
		if ($bCanEdit)
			$row->AddField("PROFILE", '<a href="'.$APPLICATION->GetCurPage()."?lang=".LANG."&amp;ACT_FILE=".urlencode($ReportFile)."&amp;".bitrix_sessid_get()."&amp;ACTION=EXPORT&amp;PROFILE_ID=".'" title="'.GetMessage("export_setup_begin").'"><i>'.GetMessage("CES_DEFAULT").'</i></a>');
		else
			$row->AddViewField('PROFILE', '<i>'.GetMessage("CES_DEFAULT").'</i>');
		
		$row->AddField("IN_MENU", GetMessage("CES_NO"));
		$row->AddField("IN_AGENT", GetMessage("CES_NO"));
		$row->AddField("IN_CRON", GetMessage("CES_NO"));

		$arActions = array();
		
		if ($bCanExec)
			$arActions[] = array(
				"DEFAULT"=>true,
				"TEXT"=>GetMessage("CES_RUN_EXPORT"),
				"TITLE"=>GetMessage("CES_RUN_EXPORT_DESCR"),
				"ACTION"=>$lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ACT_FILE=".urlencode($ReportFile)."&".bitrix_sessid_get()."&ACTION=EXPORT&PROFILE_ID="), 
			);
		
		if ($bCanEdit && $bCanExec)
			$arActions[] = array("SEPARATOR"=>true);
		
		if ($bCanEdit)
			$arActions[] = array(
				"TEXT"=>GetMessage("CES_TO_LEFT_MENU"),
				"TITLE"=>GetMessage("CES_TO_LEFT_MENU_DESCR"),
				"ACTION"=>$lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ACT_FILE=".urlencode($ReportFile)."&".bitrix_sessid_get()."&ACTION=MENU&PROFILE_ID="), 
			);
		
		if(strlen($ReportParams["FILE_SETUP"])<=0 && $USER->CanDoOperation('edit_php'))
		{
			$arActions[] = array(
				"TEXT"=>GetMessage("CES_TO_AGENT"),
				"TITLE"=>GetMessage("CES_TO_AGENT_DESCR"),
				"ACTION"=>"showAgentsForm(".$index_form.", 'agents');", 
			);
			$arActions[] = array(
				"DISABLED"=>($bWindowsHosting? true:false),
				"TEXT"=>GetMessage("CES_TO_CRON"),
				"TITLE"=>GetMessage("CES_TO_CRON_DESCR"),
				"ACTION"=>"showAgentsForm(".$index_form.", 'cron');", 
			);
			$index_form++;
		}
		
		$row->AddActions($arActions);

		$n++;
	} //!$ar_prof_res || $ar_prof_res["DEFAULT_PROFILE"]!="Y"

	if($ar_prof_res)
	{
		do
		{
			$row = &$lAdmin->AddRow(0, null);
	
			$row->AddField("NAME", htmlspecialchars($ReportParams["TITLE"]));
			$row->AddField("FILE", $ReportFile);

			if ($bCanExec)
				$row->AddField("PROFILE", '<a href="'.($ar_prof_res["IN_MENU"]=="Y"? "cat_exec_exp.php":$APPLICATION->GetCurPage())."?lang=".LANG."&amp;ACT_FILE=".urlencode($ReportFile)."&amp;ACTION=EXPORT&amp;PROFILE_ID=".$ar_prof_res["ID"]."&amp;".bitrix_sessid_get().'" title="'.GetMessage("export_setup_begin").'">'.($ar_prof_res["DEFAULT_PROFILE"]!="Y"? htmlspecialchars($ar_prof_res["NAME"]):'<i>'.GetMessage("CES_DEFAULT").'</i>').'</a>');
			else
				$row->AddViewField('PROFILE', ($ar_prof_res["DEFAULT_PROFILE"]!="Y"? htmlspecialchars($ar_prof_res["NAME"]):'<i>'.GetMessage("CES_DEFAULT").'</i>'));
			
			$row->AddField("IN_MENU", ($ar_prof_res["IN_MENU"]=="Y"? GetMessage("CES_YES") : GetMessage("CES_NO")));
			$row->AddField("IN_AGENT", ($ar_prof_res["IN_AGENT"]=="Y"? GetMessage("CES_YES") : GetMessage("CES_NO")));
			$row->AddField("IN_CRON", ($ar_prof_res["IN_CRON"]=="Y"? GetMessage("CES_YES") : GetMessage("CES_NO")));
			$row->AddField("USED", $ar_prof_res["LAST_USE_FORMAT"]);
	
			$arActions = array();
			if ($bCanExec)
				$arActions[] = array(
					"DEFAULT"=>true,
					"TEXT"=>GetMessage("CES_RUN_EXPORT"),
					"TITLE"=>GetMessage("CES_RUN_EXPORT_DESCR"),
					"ACTION"=>$lAdmin->ActionRedirect(($ar_prof_res["IN_MENU"]=="Y"? "cat_exec_exp.php":$APPLICATION->GetCurPage())."?lang=".LANG."&ACT_FILE=".urlencode($ReportFile)."&".bitrix_sessid_get()."&ACTION=EXPORT&PROFILE_ID=".$ar_prof_res["ID"]), 
				);
			
			if($ar_prof_res["DEFAULT_PROFILE"]!="Y")
			{
				$arActions[] = array(
					"TEXT"=>GetMessage("CES_SHOW_VARS_LIST"),
					"TITLE"=>GetMessage("CES_SHOW_VARS_LIST_DESCR"),
					"ACTION"=>"showFloatDiv('vars_div_".$index_vars."')", 
				);
				$index_vars++;
			}
			
			if ($bCanEdit && $bCanExec)
				$arActions[] = array("SEPARATOR"=>true);
			
			if ($bCanEdit)
				$arActions[] = array(
					"TEXT"=>($ar_prof_res["IN_MENU"]=="Y"? GetMessage("CES_TO_LEFT_MENU_DEL") : GetMessage("CES_TO_LEFT_MENU")),
					"TITLE"=>($ar_prof_res["IN_MENU"]=="Y"? GetMessage("CES_TO_LEFT_MENU_DESCR_DEL") : GetMessage("CES_TO_LEFT_MENU_DESCR")),
					"ACTION"=>$lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ACT_FILE=".urlencode($ReportFile)."&".bitrix_sessid_get()."&ACTION=MENU&PROFILE_ID=".$ar_prof_res["ID"]), 
				);

			if($USER->CanDoOperation('edit_php') && ($ar_prof_res["DEFAULT_PROFILE"]!="Y" || strlen($ReportParams["FILE_SETUP"])<=0))
			{
				$arActions[] = array(
					"TEXT"=>($ar_prof_res["IN_AGENT"]=="Y"? GetMessage("CES_TO_AGENT_DEL") : GetMessage("CES_TO_AGENT")),
					"TITLE"=>($ar_prof_res["IN_AGENT"]=="Y"? GetMessage("CES_TO_AGENT_DESCR_DEL") : GetMessage("CES_TO_AGENT_DESCR")),
					"ACTION"=>($ar_prof_res["IN_AGENT"]=="Y"? $lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ACT_FILE=".urlencode($ReportFile)."&".bitrix_sessid_get()."&ACTION=AGENT&PROFILE_ID=".$ar_prof_res["ID"]) : "showAgentsForm(".$index_form.", 'agents');"), 
				);
				$arActions[] = array(
					"DISABLED"=>($bWindowsHosting? true:false),
					"TEXT"=>($ar_prof_res["IN_CRON"]=="Y"? GetMessage("CES_TO_CRON_DEL") : GetMessage("CES_TO_CRON")),
					"TITLE"=>($ar_prof_res["IN_CRON"]=="Y"? GetMessage("CES_TO_CRON_DESCR_DEL") : GetMessage("CES_TO_CRON_DESCR")),
					"ACTION"=>"showAgentsForm(".$index_form.", 'cron');", 
				);
				$index_form++;
			}
			
			if($bCanEdit && $ar_prof_res["DEFAULT_PROFILE"]!="Y")
			{
				$arActions[] = array("SEPARATOR"=>true);
				$arActions[] = array(
					"TEXT"=>GetMessage("CES_DELETE_PROFILE"),
					"TITLE"=>GetMessage("CES_DELETE_PROFILE_DESCR"),
					"ACTION"=>"if(confirm('".GetMessage("CES_DELETE_PROFILE_CONF")."')) window.location='".$APPLICATION->GetCurPage()."?lang=".LANG."&ACT_FILE=".urlencode($ReportFile)."&".bitrix_sessid_get()."&ACTION=DEL_PROFILE&PROFILE_ID=".$ar_prof_res["ID"]."';", 
				);
			}
			$row->AddActions($arActions);
	
			$n++;
		}
		while($ar_prof_res = $db_prof_res->Fetch());
	}
}

// "подвал" списка
$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $n
		)
	)
);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("TITLE_EXPORT_PAGE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/

if (strlen($strErrorMessage) > 0)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("CES_ERRORS"), "DETAILS"=>$strErrorMessage));

if ($_GET["success_export"]=="Y")
{
	CAdminMessage::ShowNote(GetMessage("CES_SUCCESS"));
	if (strlen($SETUP_FILE_NAME) > 0)
	{
		$SETUP_FILE_NAME = htmlspecialchars($SETUP_FILE_NAME);
		if (substr($SETUP_FILE_NAME, 0, strlen($_SERVER["DOCUMENT_ROOT"]))==$_SERVER["DOCUMENT_ROOT"])
		{
			$SETUP_FILE_NAME = substr($SETUP_FILE_NAME, strlen($_SERVER["DOCUMENT_ROOT"]));
		}
		echo "<p>".GetMessage("CES_EXPORT_FILE")." <a href=\"".$SETUP_FILE_NAME."\">".$_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME."</a></p>";
	}
}
?>
<script type="text/javascript">

function showFloatDiv(div_id)
{
	var div = document.getElementById(div_id);
	var sh = document.getElementById('agents_form_shadow');
	div.style.display = "block";
	sh.style.display = "block";
	var l = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
	var t = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);
	div.style.left = l + "px";
	div.style.top = t + "px";
	sh.style.left = (l+4) + "px";
	sh.style.top = (t+4) + "px";
	sh.style.width = div.offsetWidth + 'px';
	sh.style.height = div.offsetHeight + 'px';
}

function hideFloatDiv(div_id)
{
	document.getElementById('agents_form_shadow').style.display = "none";
	document.getElementById(div_id).style.display = "none";
}

function showAgentsForm(ind, type)
{
	showFloatDiv(type+'_form_'+ind);
	if (document.forms[type+'form_'+ind].agent_period)
		document.forms[type+'form_'+ind].agent_period.focus();
}

function hideAgentsForm(ind, type)
{
	hideFloatDiv(type+'_form_'+ind);
}

</script>

<?echo BeginNote();?>
<?echo GetMessage("export_setup_cat")?> <?echo CATALOG_PATH2EXPORTS?>
<?echo EndNote();?>

<?
$lAdmin->DisplayList();
?>

<?echo BeginNote();?>
	
	<?echo GetMessage("CES_NOTES1");?><br><br>

	<?if ($bWindowsHosting):?>
		<b><?echo GetMessage("CES_NOTES2");?></b>
	<?else:?>
		<?echo GetMessage("CES_NOTES3");?>
		<b><?echo $_SERVER["DOCUMENT_ROOT"];?>/bitrix/crontab/crontab.cfg</b>
		<?echo GetMessage("CES_NOTES4");?><br>
		<?echo GetMessage("CES_NOTES5");?><br>
		<b>crontab <?echo $_SERVER["DOCUMENT_ROOT"];?>/bitrix/crontab/crontab.cfg</b><br>
		<?echo GetMessage("CES_NOTES6");?><br>
		<b>crontab -l</b><br>
		<?echo GetMessage("CES_NOTES7");?><br>
		<b>crontab -r</b><br><br>

		<?
		$arRetval = array();
		@exec("crontab -l", $arRetval);
		if (is_array($arRetval) && count($arRetval)>0)
		{
			?>
			<?echo GetMessage("CES_NOTES8");?><br>
			<textarea name="crontasks" cols="70" rows="5" wrap="off" readonly>
			<?
			for ($i = 0; $i < count($arRetval); $i++)
			{
				echo $arRetval[$i]."\n";
			}
			?>
			</textarea><br>
			<?
		}
		?>
		<?echo GetMessage("CES_NOTES10");?><br><br>

		<?echo GetMessage("CES_NOTES11");?><br>
		<?echo $_SERVER["DOCUMENT_ROOT"];?>/bitrix/php_interface/include/catalog_export/cron_frame.php<br>
		<?echo GetMessage("CES_NOTES12");?>
	<?endif;?>
	
<?echo EndNote();?>

<?
require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");
?>