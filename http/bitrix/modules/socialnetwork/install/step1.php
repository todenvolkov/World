<form action="<?= $APPLICATION->GetCurPage()?>" name="sonet_install">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?= LANG ?>">
	<input type="hidden" name="id" value="socialnetwork">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">

	<script language="JavaScript">
	<!--
	function ChangeInstallPublic(val)
	{
		var name1 = 'is404';
		var name2 = 'public_path';
		var name3 = 'public_rewrite';
		var b = (val.length > 0);
		document.getElementById(name1).disabled = !b;
		document.getElementById(name2).disabled = !b;
		document.getElementById(name3).disabled = !b;
	}
	//-->
	</script>
	<?
	$arSites= Array();
	$dbSites = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
	while ($site = $dbSites->Fetch())
	{ 
		$arSites[] = Array("SITE_ID" => $site["LID"], "NAME" => $site["NAME"], "DIR" => $site["DIR"]);
	}
	?>
	<table class="list-table">
		<tr class="head">
			<td colspan="2"><?= GetMessage("SONET_INSTALL_TITLE") ?></td>
		</tr>
		<!--tr>
			<td width="50%" align="right"><label for="id_install_templates"><?=GetMessage("SONETP_INSTALL_EMAIL") ?>:</label></td>
			<td width="50%"><input type="checkbox" name="install_templates" value="Y" id="id_install_templates" checked></td>
		</tr-->
		<tr>
			<td width="50%" align="right"><label for="id_install_smiles"><?=GetMessage("SONETP_INSTALL_SMILES") ?>:</label></td>
			<td width="50%"><input type="checkbox" name="install_smiles" value="Y" id="id_install_smiles" checked></td>
		</tr>
		<tr>
			<td width="50%" align="right"><?=GetMessage("SONETP_COPY_PUBLIC_FILES") ?>:</td>
			<td>
				<select name="install_site_id" onchange="ChangeInstallPublic(this[this.selectedIndex].value)">
					<option value="" selected>[<?=GetMessage("SONETP_NOT_INSTALL_P") ?>]</option>
					<?foreach ($arSites as $arSite):?>
						<option value="<?= $arSite["SITE_ID"] ?>">[<?= $arSite["SITE_ID"] ?>] <?= $arSite["NAME"] ?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?=GetMessage("SONETP_INSTALL_404") ?>:</td>
			<td><input type="checkbox" name="is404" id="is404" value="Y" checked></td>
		</tr>
		<tr>
			<td width="50%" align="right"><?=GetMessage("SONETP_COPY_FOLDER") ?>:</td>
			<?foreach($arSites as $fSite):?>
				<td><input type="text" name="public_path" id="public_path" value="club"></td>
			<?endforeach;?>
		</tr>			
		<tr>
			<td width="50%" align="right"><?= GetMessage("SONET_INSTALL_PUBLIC_REW") ?>:</td>
			<?foreach($arSites as $fSite):?>
				<td><input type="checkbox" name="public_rewrite" id="public_rewrite" value="Y"></td>
			<?endforeach;?>
		</tr>
	</table>

	<script language="JavaScript">
	<!--
	ChangeInstallPublic('');
	//-->
	</script>
	<br>
	<input type="submit" name="inst" value="<?= GetMessage("MOD_INSTALL")?>">
</form>