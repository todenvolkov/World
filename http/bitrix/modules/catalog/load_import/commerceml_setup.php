<?
//<title>CommerceML</title>
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/import_setup_templ.php"));

$NUM_CATALOG_LEVELS = IntVal(COption::GetOptionString("catalog", "num_catalog_levels", 3));

$commerceMLError = "";

//********************  ACTIONS  **************************************//
if ($STEP > 1)
{
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
			<?echo GetMessage("CICML_F_DATAFILE2")?>
		</td>
		<td align="left" nowrap>
			<input type="text" name="URL_FILE_1C" size="40" value="<?= htmlspecialchars($URL_FILE_1C) ?>">
			<input type="button" value="<?=GetMessage("CICML_F_BUTTON_CHOOSE")?>" OnClick="cmlBtnSelectClick()">
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
			<?echo GetMessage("CICML_F_OUTFILEACTION");?>:
		</td>
		<td align="left" nowrap>
			<input type="radio" name="outFileAction" value="D" <?if (strlen($outFileAction)<=0 || ($outFileAction=="D")) echo "checked";?>> <?echo GetMessage("CICML_OF_DEL") ?><br>
			<input type="radio" name="outFileAction" value="H" <?if ($outFileAction=="H") echo "checked";?>> <?echo GetMessage("CICML_OF_DEACT") ?><br>
			<input type="radio" name="outFileAction" value="F" <?if ($outFileAction=="F") echo "checked";?>> <?echo GetMessage("CICML_OF_KEEP") ?>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap valign="top">
			<?echo GetMessage("CICML_CONVERT_UTF8");?>:
		</td>
		<td align="left" nowrap>
			<input type="radio" name="CONVERT_UTF8" value="N" <?if (strlen($CONVERT_UTF8)<=0 || ($CONVERT_UTF8=="N")) echo "checked";?>> <?echo GetMessage("CICML_CONVERT_NO");?><br>
			<input type="radio" name="CONVERT_UTF8" value="Y" <?if ($CONVERT_UTF8=="Y") echo "checked";?>> <?echo GetMessage("CICML_CONVERT_YES");?><br>
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
				<input type="hidden" name="SETUP_FIELDS_LIST" value="URL_FILE_1C,IBLOCK_TYPE_ID,outFileAction,CONVERT_UTF8">
				<input type="submit" value="<?echo (($ACTION=="IMPORT")?GetMessage("CICML_NEXT_STEP_F"):GetMessage("CICML_SAVE"))." &gt;&gt;" ?>" name="submit_btn">
			</td>
		  </tr>
	</table>
<?endif;?>
</form>