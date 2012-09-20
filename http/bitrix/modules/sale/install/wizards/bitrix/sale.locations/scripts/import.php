<?
define("STOP_STATISTICS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
{
	echo GetMessage('WSL_IMPORT_ERROR_ACCESS_DENIED');
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");

$wizard =  new CWizard("bitrix:sale.locations");
$wizard->IncludeWizardLang("scripts/import.php");

$step_length = intval($_REQUEST["STEP_LENGTH"]);
if ($step_length <= 0) $step_length = 10;

define('ZIP_WRITE_TO_LOG', 0);
define('ZIP_STEP_LENGTH', $step_length);
define('LOC_STEP_LENGTH', $step_length);
define('DLZIPFILE', 'zip_ussr.csv');

function writeToLog($cur_op)
{
	if (defined('ZIP_WRITE_TO_LOG') && ZIP_WRITE_TO_LOG === 1)
	{
		global $start_time;
	
		list($usec, $sec) = explode(" ", microtime());
		$cur_time = ((float)$usec + (float)$sec);

		$fp = fopen('log.txt', 'a');
		fwrite($fp, $cur_time.": ");
		fwrite($fp, $cur_op."\r\n");
		fclose($fp);
	}
}

CModule::IncludeModule('sale');

$STEP = intval($_REQUEST['STEP']);
$CSVFILE = $_REQUEST["CSVFILE"];
$LOADZIP = $_REQUEST["LOADZIP"];

$bSync = $_REQUEST["SYNC"] == "Y";

if (strlen($CSVFILE) > 0 && !in_array($CSVFILE, array('loc_ussr.csv', 'loc_usa.csv', 'loc_cntr.csv')))
{
	echo GetMessage('WSL_IMPORT_ERROR_FILES');
}
else
{
	if ($STEP == 1 && strlen($CSVFILE) <= 0) 
	{
		if ($LOADZIP == 'Y') $STEP = 2;
		else $STEP = 3;
	}

	switch($STEP)
	{
		case 0:
			echo GetMessage('WSL_IMPORT_FILES_LOADING');
			echo "<script>Import(1)</script>";
		break;

		case 1:
		
			$time_limit = ini_get('max_execution_time');
			if ($time_limit < LOC_STEP_LENGTH) set_time_limit(LOC_STEP_LENGTH + 5);
		
			$start_time = time();
			$finish_time = $start_time + LOC_STEP_LENGTH;
		
			$file_url = $_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/bitrix/sale.locations/upload/'.$CSVFILE;

			if (!file_exists($file_url))
				$strWarning = GetMessage('WSL_IMPORT_ERROR_NO_LOC_FILE')."<br />";

			if (strlen($strWarning)<=0)
			{
				$bFinish = true;
			
				$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
				while ($arLang = $db_lang->Fetch())
				{
					$arSysLangs[] = $arLang["LID"];
				}

				$arLocations = array();

				if (!$bSync)
				{
					if (!is_set($_SESSION["LOC_POS"])) 
					{
						CSaleLocation::DeleteAll();
					}
				}
				else
				{
					$dbLocations = CSaleLocation::GetList(array(), array(), false, false, array("ID", "COUNTRY_ID", "CITY_ID"));
					while ($arLoc = $dbLocations->Fetch())
					{
						$arLocations[$arLoc["ID"]] = $arLoc;
					}
				}

				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/csv_data.php");

				$csvFile = new CCSVDataSale();
				$csvFile->LoadFile($file_url);
				$csvFile->SetFieldsType("R");
				$csvFile->SetFirstHeader(false);
				$csvFile->SetDelimiter(",");

				$arRes = $csvFile->Fetch();
				if (!is_array($arRes) || count($arRes)<=0 || strlen($arRes[0])!=2)
				{
					$strWarning .= GetMessage('WSL_IMPORT_ERROR_WRONG_LOC_FILE')."<br />";
				}

				if (strlen($strWarning)<=0)
				{
					$DefLang = $arRes[0];
					if (!in_array($DefLang, $arSysLangs))
					{
						$strWarning .= GetMessage('WSL_IMPORT_ERROR_NO_LANG')."<br />";
					}
				}

				if (strlen($strWarning)<=0)
				{
				
					if (is_set($_SESSION["LOC_POS"]))
					{
						$csvFile->SetPos($_SESSION["LOC_POS"]);
						
						$CurCountryID = $_SESSION["CUR_COUNTRY_ID"];
						$numCountries = $_SESSION["NUM_COUNTRIES"];
						$numCities = $_SESSION["NUM_CITIES"];
						$numLocations = $_SESSION["NUM_LOCATIONS"];
						
						//WriteToLog('current step: '.$_SESSION["LOC_POS"]);
						//WriteToLog('current country: '.$CurCountryID);
					}
					else
					{
						$CurCountryID = 0;
						$numCounties = 0;
						$numCities = 0;
						$numLocations = 0;
						
						//WriteToLog('start');
					}
					
					while ($arRes = $csvFile->Fetch())
					{
						$arArrayTmp = array();
						for ($ind = 1; $ind < count($arRes); $ind+=2)
						{
							if (in_array($arRes[$ind], $arSysLangs))
							{
								$arArrayTmp[$arRes[$ind]] = array(
										"LID" => $arRes[$ind],
										"NAME" => $arRes[$ind + 1]
									);

								if ($arRes[$ind] == $DefLang)
								{
									$arArrayTmp["NAME"] = $arRes[$ind + 1];
								}
							}
						}

						//WriteToLog(print_r($arArrayTmp, true));
						
						if (is_array($arArrayTmp) && strlen($arArrayTmp["NAME"])>0)
						{
							if (strtoupper($arRes[0])=="S")
							{
								$db_contList = CSaleLocation::GetCountryList(
									Array(), 
									Array(
										"NAME"=>$arArrayTmp["NAME"]
									), 
									$DefLang
								);
								if ($arContList = $db_contList->Fetch())
								{
									$CurCountryID = $arContList["ID"];
									$CurCountryID = IntVal($CurCountryID);
									//WriteToLog('Country found: '.$CurCountryID);
								}
								else
								{
									$CurCountryID = CSaleLocation::AddCountry($arArrayTmp);
									$CurCountryID = IntVal($CurCountryID);
									if ($CurCountryID>0)
									{
										$numCounties++;
										$LLL = CSaleLocation::AddLocation(array("COUNTRY_ID" => $CurCountryID));
										if (IntVal($LLL)>0) $numLocations++;
									}
									
									//WriteToLog('Country not found: '.$CurCountryID);
								}
							}
							elseif (strtoupper($arRes[0])=="T" && IntVal($CurCountryID)>0)
							{
								$city_id = 0;
								$LLL = 0;
								
								if ($bSync)
								{
									$db_cityList = CSaleLocation::GetList(
										Array(), 
										Array(
											"COUNTRY_ID" => $CurCountryID, 
											"CITY_NAME" => $arArrayTmp["NAME"], 
											"LID" => $DefLang
										)
									);
									
									if ($arCityList = $db_cityList->Fetch())
									{
										$LLL = $arCityList["ID"];
										$city_id = $arCityList["CITY_ID"];
										$city_id = IntVal($city_id);
									}
									else
									{
										//WriteToLog('city not found');
									}
								}

								if ($city_id <= 0)
								{
									$city_id = CSaleLocation::AddCity($arArrayTmp);
									$city_id = IntVal($city_id);
									
									if ($city_id > 0)$numCities++;
									
									//WriteToLog('city add: '.$city_id);
								}
								
								if ($city_id > 0)
								{
								/*
									$LLL = 0;
									
									if ($bSync)
									{
										foreach ($arLocations as $location => $arLoc)
										{
											if ($arLoc["COUNTRY_ID"] == $CurCountryID && $arLoc["CITY_ID"] == $city_id)
											{
												$LLL = $location;
												break;
											}
										}
									}
								*/
									
									if (IntVal($LLL) <= 0)
									{
										$LLL = CSaleLocation::AddLocation(
											array(
												"COUNTRY_ID" => $CurCountryID,
												"CITY_ID" => $city_id
											));
									
										//WriteToLog('location add: '.$LLL);
									
										if (intval($LLL) > 0) $numLocations++;
									}
								}
							}
						}
						
						$cur_time = time();
						
						if ($cur_time >= $finish_time)
						{
							$cur_step = $csvFile->GetPos();
							$amount = $csvFile->iFileLength;
							
							$_SESSION["LOC_POS"] = $cur_step;
							
							$_SESSION["CUR_COUNTRY_ID"] = $CurCountryID;
							$_SESSION["NUM_COUNTRIES"] = $numCountries;
							$_SESSION["NUM_CITIES"] = $numCities;
							$_SESSION["NUM_LOCATIONS"] = $numLocations;
							
							$bFinish = false;
							
							//WriteToLog('proceed to next step: country - '.$_SESSION["CUR_COUNTRY_ID"].' - step - '.$_SESSION["LOC_POS"]);
							
							echo "<script>Import(1, {AMOUNT:".CUtil::JSEscape($amount).",POS:".CUtil::JSEscape($cur_step)."})</script>";
							
							break;
						}						
					}
				}
				else
				{
					echo $strWarning."<br />";
				}
			}

			if ($bFinish)
			{
				unset($_SESSION["LOC_POS"]);
				
				$strOK = GetMessage('WSL_IMPORT_LOC_STATS').'<br />';
				$strOK = str_replace('#NUMCOUNTRIES#', intval($numCountries), $strOK);
				$strOK = str_replace('#NUMCITIES#', intval($numCitites), $strOK);
				$strOK = str_replace('#NUMLOCATIONS#', intval($numLocations), $strOK);
				
				echo $strOK;
				echo '<script>Import('.($LOADZIP == "Y" ? 2 : 3).')</script>';
			}
		
		break;
		
		case 2:
			$time_limit = ini_get('max_execution_time');
			if ($time_limit < ZIP_STEP_LENGTH) set_time_limit(ZIP_STEP_LENGTH + 5);
			
			
			$start_time = time();
			$finish_time = $start_time + ZIP_STEP_LENGTH;

			if ($LOADZIP == "Y" && file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/bitrix/sale.locations/upload/'.DLZIPFILE))
			{
				//WriteToLog('locations load start');
				$rsLocations = CSaleLocation::GetList(array(), array("LID" => 'ru'), false, false, array("ID", "CITY_NAME_LANG"));
				$arLocationMap = array();
				while ($arLocation = $rsLocations->Fetch())
				{
					$arLocationMap[$arLocation["CITY_NAME_LANG"]] = $arLocation["ID"];
				}
				//WriteToLog('locations load finish');

				$DB->StartTransaction();
				
				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/csv_data.php");

				$csvFile = new CCSVDataSale();
				$csvFile->LoadFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/wizards/bitrix/sale.locations/upload/'.DLZIPFILE);
				$csvFile->SetFieldsType("R");
				$csvFile->SetFirstHeader(false);
				$csvFile->SetDelimiter(";");

				if (is_set($_SESSION, 'ZIP_POS')) 
				{
					$numZIP = $_SESSION["NUM_ZIP"];

					$csvFile->SetPos($_SESSION["ZIP_POS"]);
					
					//writeToLog('ZIP_POS = '.$_SESSION["ZIP_POS"]);
				}
				else
				{
					CSaleLocation::ClearAllLocationZIP();
				
					unset($_SESSION["NUM_ZIP"]);
					$numZIP = 0;
				}

				$bFinish = true;
				
				$arLocationsZIP = array();
					
				while ($arRes = $csvFile->Fetch())
				{
					$CITY = $arRes[1];
					
					if (array_key_exists($CITY, $arLocationMap))
					{
						$ID = $arLocationMap[$CITY];
						//writeToLog('CITY: '.$CITY);
					}
					else
					{
						$ID = 0;
					}
					
					if ($ID)
					{
						//WriteToLog('Add location zip start');
						CSaleLocation::AddLocationZIP($ID, $arRes[2]);
						//WriteToLog('Add location zip finish');
						$numZIP++;
					}
					
					$cur_time = time();
					if ($cur_time >= $finish_time)
					{
						$cur_step = $csvFile->GetPos();
						$amount = $csvFile->iFileLength;
						
						$_SESSION["ZIP_POS"] = $cur_step;
						$_SESSION["NUM_ZIP"] = $numZIP;
						
						$bFinish = false;
						
						echo "<script>Import(2, {AMOUNT:".CUtil::JSEscape($amount).",POS:".CUtil::JSEscape($cur_step)."})</script>";
						
						break;
					}
				}
				
				$DB->Commit();				
				
				if ($bFinish)
				{
					unset($_SESSION["ZIP_POS"]);
					
					$numCity = CSaleLocation::_GetZIPImportStats();
					
					$strOK = GetMessage('WSL_IMPORT_ZIP_STATS');
					$strOK = str_replace('#NUMZIP#', intval($numZIP), $strOK);
					$strOK = str_replace('#NUMCITIES#', intval($numCity), $strOK);
					
					echo $strOK;
					echo '<script>Import(3); jsPB.Remove(true);</script>';
				}
			}
			else
			{
				echo GetMessage('WSL_IMPORT_ERROR_NO_ZIP_FILE');
				echo '<script>Import(3);</script>';
			}

		break;

		case 3:
			echo GetMessage('WSL_IMPORT_ALL_DONE');
			echo '<script>EnableButton();</script>';
		break;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>