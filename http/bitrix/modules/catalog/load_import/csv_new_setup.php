<?
//<title>CSV (new)</title>
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/import_setup_templ.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");

$NUM_CATALOG_LEVELS = IntVal(COption::GetOptionString("catalog", "num_catalog_levels", 3));

$strCSVError = "";

//********************  ACTIONS  **************************************//
if ($STEP > 1)
{
	if (strlen($URL_DATA_FILE) > 0 && file_exists($_SERVER["DOCUMENT_ROOT"].$URL_DATA_FILE) && is_file($_SERVER["DOCUMENT_ROOT"].$URL_DATA_FILE) && $APPLICATION->GetFileAccessPermission($URL_DATA_FILE)>="W")
		$DATA_FILE_NAME = $URL_DATA_FILE;

	if (strlen($DATA_FILE_NAME) <= 0)
		$strCSVError .= GetMessage("CATI_NO_DATA_FILE")."<br>";

	if (strlen($strCSVError) <= 0)
	{
		$IBLOCK_ID = IntVal($IBLOCK_ID);
		$arIBlockres = CIBlock::GetList(Array("sort"=>"asc"), Array("ID"=>IntVal($IBLOCK_ID), 'MIN_PERMISSION' => 'W'));
		$arIBlockres = new CIBlockResult($arIBlockres);
		if ($IBLOCK_ID <= 0 || !($arIBlock = $arIBlockres->GetNext()))
			$strCSVError .= GetMessage("CATI_NO_IBLOCK")."<br>";
	}

	if (strlen($strCSVError) <= 0)
	{
		$bIBlockIsCatalog = False;
		if (CCatalog::GetByID($IBLOCK_ID))
			$bIBlockIsCatalog = True;
	}

	if (strlen($strCSVError) > 0)
	{
		$STEP = 1;
	}
}

if ($STEP > 2)
{
	$csvFile = new CCSVData();
	$csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);

	if ($fields_type != "F" && $fields_type != "R")
		$strCSVError .= GetMessage("CATI_NO_FILE_FORMAT")."<br>";

	$arDataFileFields = array();
	if (strlen($strCSVError)<=0)
	{
		$fields_type = (($fields_type == "F") ? "F" : "R" );

		$csvFile->SetFieldsType($fields_type);

		if ($fields_type == "R")
		{
			$first_names_r = (($first_names_r=="Y") ? "Y" : "N" );
			$csvFile->SetFirstHeader(($first_names_r == "Y") ? true : false);

			$delimiter_r_char = "";
			switch ($delimiter_r)
			{
				case "TAB":
					$delimiter_r_char = "\t";
					break;
				case "ZPT":
					$delimiter_r_char = ",";
					break;
				case "SPS":
					$delimiter_r_char = " ";
					break;
				case "OTR":
					$delimiter_r_char = substr($delimiter_other_r, 0, 1);
					break;
				case "TZP":
					$delimiter_r_char = ";";
					break;
			}

			if (strlen($delimiter_r_char) != 1)
				$strCSVError .= GetMessage("CATI_NO_DELIMITER")."<br>";

			if (strlen($strCSVError) <= 0)
			{
				$csvFile->SetDelimiter($delimiter_r_char);
			}
		}
		else
		{
			$first_names_f = (($first_names_f == "Y") ? "Y" : "N" );
			$csvFile->SetFirstHeader(($first_names_f == "Y") ? true : false);

			if (strlen($metki_f) <= 0)
				$strCSVError .= GetMessage("CATI_NO_METKI")."<br>";

			if (strlen($strCSVError) <= 0)
			{
				$arMetkiTmp = preg_split("/[\D]/i", $metki_f);

				$arMetki = array();
				for ($i = 0; $i < count($arMetkiTmp); $i++)
				{
					if (IntVal($arMetkiTmp[$i]) > 0)
					{
						$arMetki[] = IntVal($arMetkiTmp[$i]);
					}
				}

				if (!is_array($arMetki) || count($arMetki)<1)
					$strCSVError .= GetMessage("CATI_NO_METKI")."<br>";

				if (strlen($strCSVError)<=0)
				{
					$csvFile->SetWidthMap($arMetki);
				}
			}
		}

		if (strlen($strCSVError) <= 0)
		{
			$bFirstHeaderTmp = $csvFile->GetFirstHeader();
			$csvFile->SetFirstHeader(false);
			if ($arRes = $csvFile->Fetch())
			{
				for ($i = 0; $i < count($arRes); $i++)
				{
					$arDataFileFields[$i] = $arRes[$i];
				}
			}
			else
			{
				$strCSVError .= GetMessage("CATI_NO_DATA")."<br>";
			}
			$NUM_FIELDS = count($arDataFileFields);
		}
	}

	if (strlen($strCSVError) > 0)
	{
		$STEP = 2;
	}
}

if ($STEP > 3)
{
}
//********************  END ACTIONS  **********************************//

echo ShowError($strCSVError);
?>

<form method="POST" action="<?echo $sDocPath ?>?lang=<?echo LANG ?>" ENCTYPE="multipart/form-data" name="dataload">
<?=bitrix_sessid_post();?>
<?if ($STEP < 4):?>
	<table border="0" cellspacing="1" cellpadding="0" width="99%">
		<tr>
			<td align="left">
				<b><?= str_replace("#STEP#", $STEP, str_replace("#ALL#", 3, GetMessage("CATI_STEPPER_TITLE"))) ?></b>
			</td>
			<td align="right">
				<input type="submit" value="<?echo ($STEP==3) ? (($ACTION=="IMPORT") ? GetMessage("CATI_NEXT_STEP_F") : GetMessage("CICML_SAVE")) : GetMessage("CATI_NEXT_STEP")." &gt;&gt;" ?>" name="submit_btn">
			</td>
		</tr>
	</table>
<?endif;?>

<table border="0" cellspacing="1" cellpadding="3" width="100%" class="list-table">
<?
//*****************************************************************//
if ($STEP == 1):
//*****************************************************************//
?>
	<tr class="head">
		<td valign="middle" colspan="2" align="center" nowrap><b><?echo GetMessage("CATI_DATA_LOADING") ?></b></td>
	</tr>
	<tr>
		<td align="right" nowrap valign="top">
			<?echo GetMessage("CATI_DATA_FILE_SITE") ?>
		</td>
		<td align="left" nowrap>
			<input type="text" name="URL_DATA_FILE" size="40" value="<?= htmlspecialchars($URL_DATA_FILE) ?>">
			<input type="button" value="<?=GetMessage("CATI_BUTTON_CHOOSE")?>" OnClick="cmlBtnSelectClick()">
<?
CAdminFileDialog::ShowScript(
	array(
		"event" => "cmlBtnSelectClick",
		"arResultDest" => array("FORM_NAME" => "dataload", "FORM_ELEMENT_NAME" => "URL_DATA_FILE"),
		"arPath" => array("PATH" => "/upload/catalog", "SITE" => SITE_ID),
		"select" => 'F',// F - file only, D - folder only, DF - files & dirs
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'csv',
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
?>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap>
			<?echo GetMessage("CATI_INFOBLOCK") ?>
		</td>
		<td align="left" nowrap>
			<select name="IBLOCK_ID">
				<option value=""><?echo GetMessage("CATI_INFOBLOCK_SELECT") ?></option>
				<?
				$dbRes = CIBlock::GetList(array("SORT"=>"ASC"), array('MIN_PERMISSION' => 'W'));
				while ($arRes = $dbRes->GetNext())
				{
					?><option value="<?echo $arRes['ID'] ?>" <?if ($arRes['ID']==$IBLOCK_ID) echo "selected";?>><?echo $arRes['NAME'] ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
<?
//*****************************************************************//
elseif($STEP==2):
//*****************************************************************//
?>
	<tr class="head">
		<td valign="middle" colspan="2" align="center" nowrap>
			<b><?echo GetMessage("CATI_CHOOSE_APPR_FORMAT") ?></b>
		</td>
	</tr>
	<tr>
		<td valign="middle" colspan="2" align="left" nowrap>
			<SCRIPT LANGUAGE="JavaScript">
			function DeactivateAllExtra()
			{
				document.getElementById("table_r").disabled = true;
				document.getElementById("table_f").disabled = true;

				document.dataload.metki_f.disabled = true;
				document.dataload.first_names_f.disabled = true;

				var i;
				for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
				{
					document.dataload.delimiter_r[i].disabled = true;
				}
				document.dataload.delimiter_other_r.disabled = true;
				document.dataload.first_names_r.disabled = true;
			}

			function ChangeExtra()
			{
				if (document.dataload.fields_type[0].checked)
				{
					document.getElementById("table_r").disabled = false;
					document.getElementById("table_f").disabled = true;

					var i;
					for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
					{
						document.dataload.delimiter_r[i].disabled = false;
					}
					document.dataload.delimiter_other_r.disabled = false;
					document.dataload.first_names_r.disabled = false;

					document.dataload.metki_f.disabled = true;
					document.dataload.first_names_f.disabled = true;

					document.dataload.submit_btn.disabled = false;
				}
				else
				{
					if (document.dataload.fields_type[1].checked)
					{
						document.getElementById("table_r").disabled = true;
						document.getElementById("table_f").disabled = false;

						var i;
						for (i = 0 ; i < document.dataload.delimiter_r.length; i++)
						{
							document.dataload.delimiter_r[i].disabled = true;
						}
						document.dataload.delimiter_other_r.disabled = true;
						document.dataload.first_names_r.disabled = true;

						document.dataload.metki_f.disabled = false;
						document.dataload.first_names_f.disabled = false;

						document.dataload.submit_btn.disabled = false;
					}
				}
			}
			</SCRIPT>

			<input type="radio" name="fields_type" id="id_fields_type_r" value="R" <?if ($fields_type=="R" || strlen($fields_type)<=0) echo "checked";?> onClick="ChangeExtra()"><label for="id_fields_type_r"><?echo GetMessage("CATI_RAZDELITEL") ?></label><br>
			<input type="radio" name="fields_type" id="id_fields_type_f" value="F" <?if ($fields_type=="F") echo "checked";?> onClick="ChangeExtra()"><label for="id_fields_type_f"><?echo GetMessage("CATI_FIXED") ?></label>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap>
			<table id="table_r" border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td valign="middle" colspan="2" align="center" nowrap>
						<?echo GetMessage("CATI_RAZDEL1") ?>
					</td>
				</tr>
				<tr>
					<td valign="top" width="50%" align="right">
						<?echo GetMessage("CATI_RAZDEL_TYPE") ?>
					</td>
					<td valign="top" width="50%" align="left" nowrap>
						<input type="radio" name="delimiter_r" value="TZP" <?if ($delimiter_r=="TZP" || strlen($delimiter_r)<=0) echo "checked"?>><?echo GetMessage("CATI_TZP") ?><br>
						<input type="radio" name="delimiter_r" value="ZPT" <?if ($delimiter_r=="ZPT") echo "checked"?>><?echo GetMessage("CATI_ZPT") ?><br>
						<input type="radio" name="delimiter_r" value="TAB" <?if ($delimiter_r=="TAB") echo "checked"?>><?echo GetMessage("CATI_TAB") ?><br>
						<input type="radio" name="delimiter_r" value="SPS" <?if ($delimiter_r=="SPS") echo "checked"?>><?echo GetMessage("CATI_SPS") ?><br>
						<input type="radio" name="delimiter_r" value="OTR" <?if ($delimiter_r=="OTR") echo "checked"?>><?echo GetMessage("CATI_OTR") ?>
						<input type="text" name="delimiter_other_r" size="3" value="<?echo htmlspecialchars($delimiter_other_r) ?>">
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="50%">
						<?echo GetMessage("CATI_FIRST_NAMES") ?>
					</td>
					<td valign="top" align="left" width="50%">
						<input type="checkbox" name="first_names_r" value="Y" <?if ($first_names_r=="Y" || strlen($strError)<=0) echo "checked"?>>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap>
			<table id="table_f" border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td valign="middle" colspan="2" align="center" nowrap>
						<?echo GetMessage("CATI_FIX1") ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="50%">
						<?echo GetMessage("CATI_FIX_MET") ?><br>
						<small><?echo GetMessage("CATI_FIX_MET_DESCR") ?></small>
					</td>
					<td valign="top" align="left" width="50%">
						<textarea name="metki_f" rows="7" cols="3"><?echo htmlspecialchars($metki_f) ?></textarea>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="50%">
						<?echo GetMessage("CATI_FIRST_NAMES") ?>
					</td>
					<td valign="top" align="left" width="50%">
						<input type="checkbox" name="first_names_f" value="Y" <?if ($first_names_f=="Y") echo "checked"?>>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap>
			<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td valign="middle" align="center" nowrap>
						<?echo GetMessage("CATI_DATA_SAMPLES") ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="center" nowrap>
						<?
						$file_id = fopen($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, "rb");
						$sContent = fread($file_id, 10000);
						fclose($file_id);
						?>
						<textarea name="data" wrap="OFF" rows="7" cols="90"><?echo htmlspecialchars($sContent) ?></textarea>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<SCRIPT LANGUAGE="JavaScript">
		DeactivateAllExtra();
		ChangeExtra();
	</SCRIPT>
<?
//*****************************************************************//
elseif ($STEP==3):
//*****************************************************************//
?>
	<tr class="head">
		<td valign="middle" colspan="2" align="center" nowrap>
			<b><?echo GetMessage("CATI_FIELDS_SOOT") ?></b>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="left" nowrap>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			<?
			$arAvailFields = array();

			$strVal = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailProdFields.",".$defCatalogAvailPriceFields);
			$arVal = explode(",", $strVal);
			$arCatalogAvailProdFields_tmp = array_merge($arCatalogAvailProdFields, $arCatalogAvailPriceFields);
			for ($i = 0; $i < count($arVal); $i++)
			{
				for ($j = 0; $j < count($arCatalogAvailProdFields_tmp); $j++)
				{
					if ($arVal[$i]==$arCatalogAvailProdFields_tmp[$j]["value"]
						&& $arVal[$i]!="IE_ID")
					{
						$arAvailFields[] = array("value"=>$arCatalogAvailProdFields_tmp[$j]["value"], "name"=>$arCatalogAvailProdFields_tmp[$j]["name"]);
						break;
					}
				}
			}

/*
			$strAvailValueFields = COption::GetOptionString("catalog", "allowed_price_fields", $defCatalogAvailValueFields);
			$arAvailValueFields = explode(",", $strAvailValueFields);
			$arAvailValueFields_names = array();
			for ($i = 0; $i < count($arAvailValueFields); $i++)
			{
				for ($j = 0; $j < count($arCatalogAvailValueFields); $j++)
				{
					if ($arCatalogAvailValueFields[$j]["value"] == $arAvailValueFields[$i])
					{
						$arAvailFields[] = array("value"=>$arCatalogAvailValueFields[$j]["value"], "name"=>$arCatalogAvailValueFields[$j]["name"]);
						break;
					}
				}
			}
*/
			$properties = CIBlockProperty::GetList(
					array("sort" => "asc", "name" => "asc"),
					array("ACTIVE" => "Y", "IBLOCK_ID" => $IBLOCK_ID)
				);
			while ($prop_fields = $properties->Fetch())
			{
				$arAvailFields[] = array("value"=>"IP_PROP".$prop_fields["ID"], "name"=>GetMessage("CATI_FI_PROPS")." \"".$prop_fields["NAME"]."\"");
			}

			for ($k = 0; $k < $NUM_CATALOG_LEVELS; $k++)
			{
				$strVal = COption::GetOptionString("catalog", "allowed_group_fields", $defCatalogAvailGroupFields);
				$arVal = explode(",", $strVal);
				for ($i = 0; $i < count($arVal); $i++)
				{
					for ($j = 0; $j < count($arCatalogAvailGroupFields); $j++)
					{
						if ($arVal[$i]==$arCatalogAvailGroupFields[$j]["value"])
						{
							$arAvailFields[] = array("value"=>$arCatalogAvailGroupFields[$j]["value"].$k, "name"=>GetMessage("CATI_FI_GROUP_LEV")." ".($k+1).": ".$arCatalogAvailGroupFields[$j]["name"]);
							break;
						}
					}
				}
			}


			$arAvailFields[] = array("value"=>"CV_QUANTITY_FROM", "name"=>GetMessage("DIN_QUANTITY_FROM"));
			$arAvailFields[] = array("value"=>"CV_QUANTITY_TO", "name"=>GetMessage("DIN_QUANTITY_TO"));

			$strVal = COption::GetOptionString("catalog", "allowed_price_fields", $defCatalogAvailValueFields);
			$arVal = explode(",", $strVal);
			$db_prgr = CCatalogGroup::GetList(array("NAME" => "ASC"), Array());
			while ($prgr = $db_prgr->Fetch())
			{
				for ($i = 0; $i < count($arVal); $i++)
				{
					for ($j = 0; $j < count($arCatalogAvailValueFields); $j++)
					{
						if ($arVal[$i]==$arCatalogAvailValueFields[$j]["value"])
						{
							$arAvailFields[] = array("value"=>$arCatalogAvailValueFields[$j]["value"]."_".$prgr["ID"], "name"=>str_replace("#NAME#", $prgr["NAME"], GetMessage("DIN_PRICE_TYPE")).": ".$arCatalogAvailValueFields[$j]["name"]);
							break;
						}
					}
				}
			}

			for ($i = 0; $i < count($arDataFileFields); $i++)
			{
				?>
				<tr>
					<td valign="top">
						<b><?echo GetMessage("CATI_FIELD") ?> <?echo $i+1 ?></b> (<?echo htmlspecialchars(TruncateText($arDataFileFields[$i], 15));?>)
					</td>
					<td valign="top">
						<select name="field_<?echo $i ?>">
							<option value=""> - </option>
							<?
							for ($j = 0; $j < count($arAvailFields); $j++)
							{
								?>
								<option value="<?echo $arAvailFields[$j]["value"] ?>" <?if ($arAvailFields[$j]["value"]=="IE_XML_ID" || $arAvailFields[$j]["value"]=="IE_NAME") echo "style=\"background-color:#FFCCCC\"" ?> <?if (${"field_".$i}==$arAvailFields[$j]["value"] || !isset(${"field_".$i}) && $arAvailFields[$j]["value"]==$arDataFileFields[$i]) echo "selected" ?>><?echo htmlspecialchars($arAvailFields[$j]["name"]) ?></option>
								<?
							}
							?>
						</select>
					</td>
				</tr>
				<?
			}
			?>
			</table>
		</td>
	</tr>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap>
			<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td valign="middle" align="center" colspan="2" nowrap>
						<?echo GetMessage("CATI_ADDIT_SETTINGS") ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" nowrap>
						<?echo GetMessage("CATI_IMG_PATH") ?>
					</td>
					<td valign="top" align="left">
						<input type="text" name="PATH2IMAGE_FILES" size="40" value="<?echo htmlspecialchars($PATH2IMAGE_FILES)?>"><br>
						<small><?echo GetMessage("CATI_IMG_PATH_DESCR") ?><br></small>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<?echo GetMessage("CATI_OUTFILE") ?>
					</td>
					<td valign="top" align="left" nowrap>
						<input type="radio" name="outFileAction" value="H" <?if (strlen($outFileAction)<=0 || ($outFileAction=="H")) echo "checked";?>> <?echo GetMessage("CATI_OF_DEACT") ?><br>
						<input type="radio" name="outFileAction" value="D" <?if ($outFileAction=="D") echo "checked";?>> <?echo GetMessage("CATI_OF_DEL") ?><br>
						<input type="radio" name="outFileAction" value="F" <?if ($outFileAction=="F") echo "checked";?>> <?echo GetMessage("CATI_OF_KEEP") ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<?echo GetMessage("CATI_INACTIVE_PRODS");?>
					</td>
					<td valign="top" align="left" nowrap>
						<input type="radio" name="inFileAction" value="F" <?if (strlen($inFileAction)<=0 || ($inFileAction=="F")) echo "checked";?>> <?echo GetMessage("CATI_KEEP_AS_IS");?><br>
						<input type="radio" name="inFileAction" value="A" <?if ($inFileAction=="A") echo "checked";?>> <?echo GetMessage("CATI_ACTIVATE_PROD");?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right" nowrap>
						<?echo GetMessage("CATI_AUTO_STEP_TIME");?>
					</td>
					<td valign="top" align="left">
						<input type="text" name="max_execution_time" size="40" value="<?echo htmlspecialchars($max_execution_time)?>"><br>
						<small><?echo GetMessage("CATI_AUTO_STEP_TIME_NOTE");?><br></small>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<?if ($ACTION=="IMPORT_SETUP"):?>
		<tr>
			<td valign="middle" colspan="2" align="center" nowrap>
				<b><?= GetMessage("CATI_IMPORT_SCHEME_NAME") ?></b>
			</td>
		</tr>
		<tr>
			<td valign="middle" align="right" nowrap>
				<?= GetMessage("CATI_IMPORT_SCHEME_NAME") ?>
			</td>
			<td valign="top" align="left" nowrap>
				<input type="text" name="SETUP_PROFILE_NAME" size="40" value="<?echo htmlspecialchars($SETUP_PROFILE_NAME)?>">
			</td>
		</tr>
	<?endif;?>

	<tr>
		<td valign="middle" colspan="2" align="center" nowrap>
			<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr>
					<td valign="middle" align="center" nowrap>
						<?echo GetMessage("CATI_DATA_SAMPLES") ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="center" nowrap>

						<?
						$file_id = fopen($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, "rb");
						$sContent = fread($file_id, 10000);
						fclose($file_id);
						?>
						<textarea name="data" wrap="OFF" rows="7" cols="90"><?echo htmlspecialchars($sContent) ?></textarea>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<?
//*****************************************************************//
elseif ($STEP==4):
//*****************************************************************//
	$FINITE = True;
//*****************************************************************//
endif;
//*****************************************************************//
?>
</table>

<?if ($STEP < 4):?>
	<table border="0" cellspacing="1" cellpadding="0" width="99%">
		<tr>
			<td align="right" nowrap colspan="2">
				<input type="hidden" name="STEP" value="<?echo $STEP + 1;?>">
				<input type="hidden" name="lang" value="<?echo htmlspecialchars($lang) ?>">
				<input type="hidden" name="ACT_FILE" value="<?echo htmlspecialchars($_REQUEST["ACT_FILE"]) ?>">
				<input type="hidden" name="ACTION" value="<?echo htmlspecialchars($ACTION) ?>">

				<?if ($STEP > 1):?>
					<input type="hidden" name="IBLOCK_ID" value="<?echo $IBLOCK_ID ?>">
					<input type="hidden" name="URL_DATA_FILE" value="<?echo htmlspecialchars($DATA_FILE_NAME) ?>">
				<?endif;?>

				<?
				if ($STEP > 2)
				{
					?>
					<input type="hidden" name="fields_type" value="<?echo htmlspecialchars($fields_type) ?>">
					<?if ($fields_type == "R"):?>
						<input type="hidden" name="delimiter_r" value="<?echo htmlspecialchars($delimiter_r) ?>">
						<input type="hidden" name="delimiter_other_r" value="<?echo htmlspecialchars($delimiter_other_r) ?>">
						<input type="hidden" name="first_names_r" value="<?echo htmlspecialchars($first_names_r) ?>">
					<?else:?>
						<input type="hidden" name="metki_f" value="<?echo htmlspecialchars($metki_f) ?>">
						<input type="hidden" name="first_names_f" value="<?echo htmlspecialchars($first_names_f) ?>">
					<?endif;?>
					<?
					$fieldsString = "";
					for ($i = 0; $i < count($arDataFileFields); $i++)
						$fieldsString .= ",field_".$i;
					?>
					<input type="hidden" name="SETUP_FIELDS_LIST" value="IBLOCK_ID,URL_DATA_FILE,fields_type,delimiter_r,delimiter_other_r,first_names_r,metki_f,first_names_f,PATH2IMAGE_FILES,outFileAction,inFileAction,max_execution_time<?= $fieldsString ?>">
					<?
				}
				?>


				<?if ($STEP > 1):?>
				<input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage("CATI_BACK") ?>">
				<?endif?>
				<input type="submit" value="<?echo ($STEP==3) ? (($ACTION=="IMPORT") ? GetMessage("CATI_NEXT_STEP_F") : GetMessage("CICML_SAVE")) : GetMessage("CATI_NEXT_STEP")." &gt;&gt;" ?>" name="submit_btn">

			</td>
		  </tr>
	</table>
<?endif;?>
</form>