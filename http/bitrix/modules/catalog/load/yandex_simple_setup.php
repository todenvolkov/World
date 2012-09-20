<?
//<title>Yandex simple</title>
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/export_setup_templ.php"));

$strYandexError = "";

if ($STEP>1)
{
	if (!is_array($YANDEX_EXPORT) || count($YANDEX_EXPORT)<=0)
	{
		$strYandexError .= GetMessage("CET_ERROR_NO_IBLOCKS")."<br>";
	}

	if (strlen($SETUP_FILE_NAME)<=0)
		$strYandexError .= GetMessage("CET_ERROR_NO_FILENAME")."<br>";

	if ($ACTION=="EXPORT_SETUP" && strlen($SETUP_PROFILE_NAME)<=0)
		$strYandexError .= GetMessage("CET_ERROR_NO_PROFILE_NAME")."<br>";

	if (strlen($strYandexError)>0)
	{
		$STEP = 1;
	}
}

echo ShowError($strYandexError);

if ($STEP==1)
{
	if (CModule::IncludeModule("iblock"))
	{
		// Get IBlock list
		?>
		<form method="post" action="<?echo $APPLICATION->GetCurPage() ?>">
		<?=bitrix_sessid_post()?>

		<table width="100%">
			<tr>
				<td width="0%" valign="top">
					<font class="text" style="font-size: 20px;">1.&nbsp;&nbsp;&nbsp;</font>
				</td>
				<td valign="top">
					<font class="text"><?echo GetMessage("CET_EXPORT_CATALOGS");?><br><br></font>
					<table width="100%" cellspacing="1" cellpadding="2" border="0">
						<tr>
							<td valign="top" class="tablehead1">
								<font class="tableheadtext"><?echo GetMessage("CET_CATALOG");?></font>
							</td>
							<td valign="top" class="tablehead3">
								<font class="tableheadtext"><?echo GetMessage("CET_EXPORT2YANDEX");?></font>
							</td>
						</tr>
						<?
						$db_res = CIBlock::GetList(Array("iblock_type"=>"asc", "name"=>"asc"));
						while ($res = $db_res->Fetch())
						{
							if ($ar_res1 = CCatalog::GetByID($res["ID"]))
							{
								?>
								<tr>
									<td class="tablebody1">
										<font class="tablebodytext">
										<?echo "[".$res["IBLOCK_TYPE_ID"]."] ".htmlspecialchars($res["NAME"])." (".htmlspecialchars($res["LID"]).")";?>
										</font>
									</td>
									<td class="tablebody3" align="center">
										<font class="tablebodytext">
										<input type="checkbox" name="YANDEX_EXPORT[]" value="<?echo $res["ID"] ?>"<?if (is_array($YANDEX_EXPORT) && in_array($res["ID"], $YANDEX_EXPORT)) echo " checked";?>>
										</font>
									</td>
								</tr>
								<?
							}
						}
						?>
					</table>
					<br>
				</td>
			</tr>

			<tr>
				<td width="0%" valign="top">
					<font class="text" style="font-size: 20px;">2.&nbsp;&nbsp;&nbsp;</font>
				</td>
				<td width="100%" valign="top">
					<font class="text">
					<?echo GetMessage("CET_SERVER_NAME");?> <input type="text" name="SETUP_SERVER_NAME" value="<?echo (strlen($SETUP_SERVER_NAME)>0) ? htmlspecialchars($SETUP_SERVER_NAME) : '' ?>" size="50" /> <input type="button" onclick="this.form['SETUP_SERVER_NAME'].value = window.location.host;" value="<?echo GetMessage('CET_SERVER_NAME_SET_CURRENT')?>" />
					</font>
					<br><br>
				</td>
			</tr>
			
			<tr>
				<td width="0%" valign="top">
					<font class="text" style="font-size: 20px;">3.&nbsp;&nbsp;&nbsp;</font>
				</td>
				<td width="100%" valign="top">
					<font class="text">
					<?echo GetMessage("CET_SAVE_FILENAME");?> <input type="text" name="SETUP_FILE_NAME" value="<?echo (strlen($SETUP_FILE_NAME)>0) ? htmlspecialchars($SETUP_FILE_NAME) : (COption::GetOptionString("catalog", "export_default_path", "/upload/"))."yandex.php" ?>" size="50">
					</font>
					<br><br>
				</td>
			</tr>

			<?if ($ACTION=="EXPORT_SETUP"):?>
				<tr>
					<td width="0%" valign="top">
						<font class="text" style="font-size: 20px;">4.&nbsp;&nbsp;&nbsp;</font>
					</td>
					<td width="100%" valign="top">
						<font class="text">
						<?echo GetMessage("CET_PROFILE_NAME");?> <input type="text" name="SETUP_PROFILE_NAME" value="<?echo htmlspecialchars($SETUP_PROFILE_NAME)?>" size="30">
						</font>
						<br><br>
					</td>
				</tr>
			<?endif;?>

			<tr>
				<td width="0%" valign="top">
					&nbsp;
				</td>
				<td width="100%" valign="top">
					<input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
					<input type="hidden" name="ACT_FILE" value="<?echo htmlspecialchars($_REQUEST["ACT_FILE"]) ?>">
					<input type="hidden" name="ACTION" value="<?echo htmlspecialchars($ACTION) ?>">
					<input type="hidden" name="STEP" value="<?echo intval($STEP) + 1 ?>">
					<input type="hidden" name="SETUP_FIELDS_LIST" value="YANDEX_EXPORT,SETUP_SERVER_NAME,SETUP_FILE_NAME">
					<input type="submit" value="<?echo ($ACTION=="EXPORT")?GetMessage("CET_EXPORT"):GetMessage("CET_SAVE")?>">
				</td>
			</tr>
		</table>
		</form>
		<?
	}
}
elseif ($STEP==2)
{
	$FINITE = True;
}
?>