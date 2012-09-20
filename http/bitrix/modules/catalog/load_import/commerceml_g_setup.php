<?
//<title>CommerceML MySql Fast</title>
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/import_setup_templ.php"));

$commerceMLError = "";

//********************  ACTIONS  **************************************//
if ($STEP > 1)
{
	$DATA_FILE_NAME = "";

	if (strlen($URL_FILE_1C) > 0 && file_exists($_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C) && is_file($_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C))
		$DATA_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$URL_FILE_1C;

	if (strlen($DATA_FILE_NAME) <= 0)
		$commerceMLError .= GetMessage("CICML_ERROR_NO_DATAFILE")."<br>";

	if (strlen($IBLOCK_TYPE_ID) <= 0)
		$commerceMLError .= GetMessage("CICML_ERROR_NO_IBLOCKTYPE")."<br>";

	if (strlen($commerceMLError)>0)
	{
		$STEP = 1;
	}
}
//********************  END ACTIONS  **********************************//

echo ShowError($commerceMLError);
?>

<form method="POST" action="<?= $sDocPath ?>?lang=<?= LANG ?>" ENCTYPE="multipart/form-data" name="dataload">
<?=bitrix_sessid_post()?>
<?if ($STEP < 2):?>
<table border="0" cellspacing="1" cellpadding="0" width="99%">
	<tr>
		<td align="left">
			<b><?= str_replace("#ALL#", 1, str_replace("#CUR#", $STEP, GetMessage("CICML_STEP_TITLE"))); ?></b>
		</td>
		<td align="right">
			<input type="submit" value="<?echo (($ACTION=="IMPORT")?GetMessage("CICML_NEXT_STEP_F"):GetMessage("CICML_SAVE"))." &gt;&gt;" ?>" name="submit_btn">
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
		<td valign="middle" colspan="2" align="center" nowrap>
			<b><?echo GetMessage("CICML_DATA_IMPORT") ?></b>
		</td>
	</tr>

	<tr>
		<td align="right" nowrap valign="top">
			<?echo GetMessage("CICML_F_DATAFILE2");?>
		</td>
		<td align="left" nowrap>
			<input type="text" name="URL_FILE_1C" size="40" value="<?= htmlspecialchars($URL_FILE_1C) ?>">
			<input type="button" value="<?= GetMessage("CML_S_SELECT") ?>" OnClick="cmlBtnSelectClick()">
<?
CAdminFileDialog::ShowScript(
	array(
		"event" => "cmlBtnSelectClick",
		"arResultDest" => array("FORM_NAME" => "dataload", "FORM_ELEMENT_NAME" => "URL_FILE_1C"),
		"arPath" => array("PATH" => "/upload/catalog", "SITE" => SITE_ID),
		"select" => 'F',// F - file only, D - folder only, DF - files & dirs
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'xml',
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
?>
		</td>
	</tr>

	<tr>
		<td align="right" nowrap>
			<?echo GetMessage("CICML_F_IBLOCK");?>
		</td>
		<td align="left" nowrap>
			<select name="IBLOCK_TYPE_ID">
				<option value="">- <?echo GetMessage("CICML_F_IBLOCK_SELECT") ?> -</option>
				<?
				$iblocks = CIBlockType::GetList(Array($by=>$order));
				while ($iblocks->ExtractFields("f_"))
				{
					$ibtypelang = CIBlockType::GetByIDLang($f_ID, LANG, true);
					?><option value="<?= $f_ID ?>"<?if ($f_ID == $IBLOCK_TYPE_ID) echo " selected";?>><?echo htmlspecialchars($ibtypelang["NAME"]) ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap valign="top">
			<?= GetMessage("CML_S_KEEP_PRP") ?>:
		</td>
		<td align="left" nowrap>
			<input type="radio" name="keepExistingProperties" id="keepExistingProperties_N" value="N" <?if (strlen($keepExistingProperties)<=0 || ($keepExistingProperties=="N")) echo "checked";?>> <label for="keepExistingProperties_N"><?= GetMessage("CML_S_NO") ?></label><br>
			<input type="radio" name="keepExistingProperties" id="keepExistingProperties_Y" value="Y" <?if ($keepExistingProperties=="Y") echo "checked";?>> <label for="keepExistingProperties_Y"><?= GetMessage("CML_S_YES") ?></label><br>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap valign="top">
			<?= GetMessage("CML_S_KEEP_DATA") ?>:
		</td>
		<td align="left" nowrap>
			<input type="radio" name="keepExistingData" id="keepExistingData_N" value="N" <?if (strlen($keepExistingData)<=0 || ($keepExistingData=="N")) echo "checked";?>> <label for="keepExistingData_N"><?= GetMessage("CML_S_NO") ?></label><br>
			<input type="radio" name="keepExistingData" id="keepExistingData_Y" value="Y" <?if ($keepExistingData=="Y") echo "checked";?>> <label for="keepExistingData_Y"><?= GetMessage("CML_S_YES") ?></label><br>
			<!--<input type="radio" name="keepExistingData" id="keepExistingData_D" value="D" <?if ($keepExistingData=="D") echo "checked";?>> <label for="keepExistingData_D">Deactivate</label><br>-->
		</td>
	</tr>
	<tr>
		<td align="right" nowrap valign="top">
			<?= GetMessage("CML_S_ACT_DATA") ?>:
		</td>
		<td align="left" nowrap>
			<input type="radio" name="activateFileData" id="activateFileData_Y" value="Y" <?if (strlen($activateFileData)<=0 || ($activateFileData=="Y")) echo "checked";?>> <label for="activateFileData_Y"><?= GetMessage("CML_S_YES") ?></label><br>
			<input type="radio" name="activateFileData" id="activateFileData_N" value="N" <?if ($activateFileData=="N") echo "checked";?>> <label for="activateFileData_N"><?= GetMessage("CML_S_NO") ?></label><br>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap valign="top">
			<?= GetMessage("CML_S_COMMNT") ?>:
		</td>
		<td align="left" nowrap>
			<input type="radio" name="deleteComments" id="deleteComments_N" value="N" <?if (strlen($deleteComments)<=0 || ($deleteComments=="N")) echo "checked";?>> <label for="deleteComments_N"><?= GetMessage("CML_S_NO") ?></label><br>
			<input type="radio" name="deleteComments" id="deleteComments_Y" value="Y" <?if ($deleteComments=="Y") echo "checked";?>> <label for="deleteComments_Y"><?= GetMessage("CML_S_YES") ?></label><br>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap valign="top">
			<?= GetMessage("CML_S_FDEB") ?>:
		</td>
		<td align="left" nowrap>
			<input type="radio" name="cmlDebug" id="cmlDebug_N" value="N" <?if (strlen($cmlDebug)<=0 || ($cmlDebug=="N")) echo "checked";?>> <label for="cmlDebug_N"><?= GetMessage("CML_S_NO") ?></label><br>
			<input type="radio" name="cmlDebug" id="cmlDebug_Y" value="Y" <?if ($cmlDebug=="Y") echo "checked";?>> <label for="cmlDebug_Y"><?= GetMessage("CML_S_YES") ?></label><br>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap valign="top">
			<?= GetMessage("CML_S_MEMDEB") ?>:
		</td>
		<td align="left" nowrap>
			<input type="radio" name="cmlMemoryDebug" id="cmlMemoryDebug_N" value="N" <?if (strlen($cmlMemoryDebug)<=0 || ($cmlMemoryDebug=="N")) echo "checked";?>> <label for="cmlMemoryDebug_N"><?= GetMessage("CML_S_NO") ?></label><br>
			<input type="radio" name="cmlMemoryDebug" id="cmlMemoryDebug_Y" value="Y" <?if ($cmlMemoryDebug=="Y") echo "checked";?>> <label for="cmlMemoryDebug_Y"><?= GetMessage("CML_S_YES") ?></label><br>
		</td>
	</tr>

	<?if ($ACTION=="IMPORT_SETUP"):?>
		<tr>
			<td valign="middle" colspan="2" align="center" nowrap>
				<b><?echo GetMessage("CICML_SAVE_SCHEME") ?></b>
			</td>
		</tr>
		<tr>
			<td valign="middle" align="right" nowrap>
				<?echo GetMessage("CICML_SSCHEME_NAME") ?>:
			</td>
			<td valign="top" align="left" nowrap>
				<input type="text" name="SETUP_PROFILE_NAME" size="40" value="<?echo htmlspecialchars($SETUP_PROFILE_NAME)?>">
			</td>
		</tr>
	<?endif;?>
<?
//*****************************************************************//
elseif ($STEP == 2):
//*****************************************************************//
	$FINITE = True;
//*****************************************************************//
endif;
//*****************************************************************//
?>
</table>

<?if ($STEP < 2):?>
	<table border="0" cellspacing="1" cellpadding="0" width="99%">
		<tr>
			<td align="right" nowrap colspan="2">
				<input type="hidden" name="STEP" value="<?echo $STEP + 1;?>">
				<input type="hidden" name="lang" value="<?echo htmlspecialchars($lang) ?>">
				<input type="hidden" name="ACT_FILE" value="<?echo htmlspecialchars($_REQUEST["ACT_FILE"]) ?>">
				<input type="hidden" name="ACTION" value="<?echo htmlspecialchars($ACTION) ?>">
				<input type="hidden" name="SETUP_FIELDS_LIST" value="URL_FILE_1C,IBLOCK_TYPE_ID,keepExistingProperties,keepExistingData,clearTempTables,deleteComments,cmlDebug,cmlMemoryDebug,activateFileData">
				<input type="submit" value="<?echo (($ACTION=="IMPORT")?GetMessage("CICML_NEXT_STEP_F"):GetMessage("CICML_SAVE"))." &gt;&gt;" ?>" name="submit_btn">
			</td>
		  </tr>
	</table>
<?endif;?>
</form>