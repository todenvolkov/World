<? 
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$strWarning = "";
$strOK = "";
if ($_SERVER["REQUEST_METHOD"]=="POST" && check_bitrix_sessid())
{
	if (!is_uploaded_file($_FILES["location_file"]["tmp_name"])
		|| !file_exists($_FILES["location_file"]["tmp_name"]))
		$strWarning .= GetMessage("NO_LOC_FILE").".<br>";

	if (strlen($strWarning)<=0)
	{
		$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch())
		{
			$arSysLangs[] = $arLang["LID"];
		}

		if ($location_del=="Y")
		{
			CSaleLocation::DeleteAll();
		}

		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/csv_data.php");

		if (LANG_CHARSET != 'windows-1251')
		{
			$fp = fopen($_FILES["location_file"]["tmp_name"], 'r');
			$contents = fread($fp, filesize($_FILES["location_file"]["tmp_name"]));
			fclose($fp);
			
			$contents = $APPLICATION->ConvertCharset($contents, 'windows-1251', LANG_CHARSET);
			
			$fp = fopen($_FILES["location_file"]["tmp_name"], 'w');
			fwrite($fp, $contents);
			fclose($fp);
		}
		
		$csvFile = new CCSVDataSale();
		$csvFile->LoadFile($_FILES["location_file"]["tmp_name"]);
		$csvFile->SetFieldsType("R");
		$csvFile->SetFirstHeader(false);
		$csvFile->SetDelimiter(",");

		$arRes = $csvFile->Fetch();
		if (!is_array($arRes) || count($arRes)<=0 || strlen($arRes[0])!=2)
		{
			$strWarning .= GetMessage("ERROR_LOC_FORMAT").".<br>";
		}

		if (strlen($strWarning)<=0)
		{
			$DefLand = $arRes[0];
			if (!in_array($DefLand, $arSysLangs))
			{
				$strWarning .= GetMessage("NO_MAIN_LANG").".<br>";
			}
		}

		if (strlen($strWarning)<=0)
		{
			$CurCountryID = 0;
			$numCounties = 0;
			$numCities = 0;
			$numLocations = 0;
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

						if ($arRes[$ind] == $DefLand)
						{
							$arArrayTmp["NAME"] = $arRes[$ind + 1];
						}
					}
				}

				if (is_array($arArrayTmp) && strlen($arArrayTmp["NAME"])>0)
				{
					if (strtoupper($arRes[0])=="S")
					{
						$db_contList = CSaleLocation::GetCountryList(Array(), Array("NAME"=>$arArrayTmp["NAME"]), LANG);
						if ($arContList = $db_contList->Fetch())
						{
							$CurCountryID = $arContList["ID"];
							$CurCountryID = IntVal($CurCountryID);
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
						}
					}
					elseif (strtoupper($arRes[0])=="T" && IntVal($CurCountryID)>0)
					{
						$city_id = CSaleLocation::AddCity($arArrayTmp);
						$city_id = IntVal($city_id);
						if ($city_id>0)
						{
							$numCities++;
							$LLL = CSaleLocation::AddLocation(
								array(
									"COUNTRY_ID" => $CurCountryID,
									"CITY_ID" => $city_id
								));
							if (IntVal($LLL)>0) $numLocations++;
						}
					}
				}
			}
			$strOK .= GetMessage("OMLOADED1")."<br> ".
				"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".GetMessage("OMLOADED2").": ".$numCounties."<br>".
				"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".GetMessage("OMLOADED3").": ".$numCities.".<br>".
				GetMessage("OMLOADED4").": ".$numLocations."".".<br>";
		}
	}
}

$APPLICATION->SetTitle(GetMessage("location_admin_import"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

echo ShowError($strWarning);
echo ShowNote($strOK, "oktext");

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("location_admin_import_tab"), "ICON" => "sale", "TITLE" => GetMessage("LOCA_LOADING")),
	array("DIV" => "edit2", "TAB" => GetMessage("location_admin_import_tab_old"), "ICON" => "sale", "TITLE" => GetMessage("LOCA_LOADING_OLD"))
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2"><button onclick="WizardWindow.Open('bitrix:sale.locations', '<?=bitrix_sessid()?>');"><?=GetMessage('LOCA_LOADING_WIZARD')?></button></td>
	</tr>
<?
$tabControl->EndTab();
?>
<form method="POST" action="sale_location_import.php" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="100000000">
<?=bitrix_sessid_post()?>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="30%"><?echo GetMessage("LOCA_FILE")?>:</td>
		<td width="70%"><input type="file" class="typefile" name="location_file"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LOCA_DEL_OLD")?>:</td>
		<td><input type="checkbox" name="location_del" value="Y"></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" name="save"<?=($saleModulePermissions < "W" ? ' disabled="disabled"' : '')?> value="<?=GetMessage('LOCA_SAVE')?>" /></td>
	</tr>
	<tr>
		<td colspan="2">
<?
echo BeginNote();
echo GetMessage('LOCA_HINT');
echo EndNote();
?>
		</td>
	</tr>

<?
$tabControl->EndTab();
?>
</form>
<?
$tabControl->End();

echo BeginNote();
echo GetMessage("LOCA_LOCATIONS_STATS").': <ul style="font-size: 100%">';

$rsLocations = CSaleLocation::GetList(array(), array(), array("COUNTRY_ID", "COUNT" => "CITY_ID"));

$numLocations = 0;
$numCountries = 0;
$numCities = 0;

while ($arStat = $rsLocations->Fetch())
{
	$numCountries++;
	$numCities += $arStat["CITY_ID"];
	$numLocations += $arStat['CNT'];
}

echo '<li>'.GetMessage('LOCA_LOCATIONS_COUNTRY_STATS').': '.$numCountries.'</li>';
echo '<li>'.GetMessage('LOCA_LOCATIONS_CITY_STATS').': '.$numCities.'</li>';
echo '<li>'.GetMessage('LOCA_LOCATIONS_LOC_STATS').': '.$numLocations.'</li>';

$rsLocationGroups = CSaleLocationGroup::GetList();
$numGroups = 0;
while ($arGroup = $rsLocationGroups->Fetch()) $numGroups++;

echo '<li>'.GetMessage('LOCA_LOCATIONS_GROUP_STATS').': '.$numGroups.'</li>';
echo '</ul>';
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>