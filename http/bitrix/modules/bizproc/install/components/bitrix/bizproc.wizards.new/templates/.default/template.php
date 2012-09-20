<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (strlen($arResult["FatalErrorMessage"]) > 0)
{
	?>
	<span class='errortext'><?= $arResult["FatalErrorMessage"] ?></span><br /><br />
	<?
}
else
{
	if (strlen($arResult["ErrorMessage"]) > 0)
	{
		?>
		<span class='errortext'><?= $arResult["ErrorMessage"] ?></span><br /><br />
		<?
	}
	$arButtons = array(
		array(
			"TEXT"=>GetMessage("BPWC_WNCT_2LIST"),
			"TITLE"=>GetMessage("BPWC_WNCT_2LIST"),
			"LINK"=>$arResult["PATH_TO_INDEX"],
			"ICON"=>"btn-list",
		),
	);
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS" => $arButtons
		),
		$component
	);
	?>
	<br>


	<form name="bizprocform" method="post" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data">
		<input type="hidden" name="back_url" value="<?= htmlspecialchars($arResult["BackUrl"]) ?>">
		<?=bitrix_sessid_post()?>
		<?
		if ($arResult["Step"] == 1)
		{
			?>
			<table class="bpwiz1-view-form data-table" cellpadding="0" cellpadding="0" border="0">
				<tr>
					<th colspan="2"><?= $arParams["BLOCK_ID"] > 0 ? GetMessage("BPWC_WNCT_SUBTITLE11") : GetMessage("BPWC_WNCT_SUBTITLE1") ?></th>
				</tr>
				<tr>
					<td valign="top" align="right"><span style="color:red">*</span> <?= GetMessage("BPWC_WNCT_NAME") ?>:</td>
					<td valign="top"><input type="text" size="40" name="bp_name" value="<?= htmlspecialchars($arResult["Data"]["Name"]) ?>"></td>
				</tr>
				<tr>
					<td valign="top" align="right"><?= GetMessage("BPWC_WNCT_DESCR") ?>:</td>
					<td valign="top"><textarea name="bp_description" rows="3" cols="40"><?= htmlspecialchars($arResult["Data"]["Description"]) ?></textarea></td>
				</tr>
				<tr>
					<td valign="top" align="right"><?= GetMessage("BPWC_WNCT_SORT") ?>:</td>
					<td valign="top"><input type="text" size="10" name="bp_sort" value="<?= intval($arResult["Data"]["Sort"]) ?>"></td>
				</tr>
				<tr>
					<td valign="top" align="right"><?= GetMessage("BPWC_WNCT_ICON") ?>:</td>
					<td valign="top">
						<input type="file" name="bp_image">
						<?
						if ($arResult["Data"]["Image"] > 0)
						{
							echo "<br/><br/>".CFile::ShowImage($arResult["Data"]["Image"], 150, 150, "border=0", false, true);
						}
						?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right"><?= GetMessage("BPWC_WNCT_EADD") ?>:</td>
					<td valign="top"><input type="text" size="40" name="bp_element_add" value="<?= htmlspecialchars($arResult["Data"]["ElementAdd"]) ?>"></td>
				</tr>
				<tr>
					<td valign="top" align="right"><?= GetMessage("BPWC_WNCT_PERMS") ?>:</td>
					<td valign="top">
						<select name="bp_user_groups[]" multiple="multiple" size="5">
						<?
						foreach ($arResult["AvailableUserGroups"] as $key => $value)
						{
							?><option value="<?= $key ?>"<?= (in_array($key, $arResult["Data"]["UserGroups"])) ? " selected" : "" ?>><?= htmlspecialchars($value)." [".$key."]" ?></option><?
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right"><?= GetMessage("BPWC_WNCT_VISIBLEFIELDS") ?>:</td>
					<td valign="top">
						<select name="bp_visiblefields[]" multiple="multiple" size="5">
						<?
						foreach ($arResult["DocumentFields"] as $key => $value)
						{
							?><option value="<?= $key ?>"<?= (in_array($key, $arResult["Data"]["VisibleFields"])) ? " selected" : "" ?>><?= htmlspecialchars($value["Name"])." [".$key."]" ?></option><?
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right"><?= GetMessage("BPWC_WNCT_FILTERABLEFIELDS") ?>:</td>
					<td valign="top">
						<select name="bp_filterablefields[]" multiple="multiple" size="5">
						<?
						foreach ($arResult["DocumentFields"] as $key => $value)
						{
							?><option value="<?= $key ?>"<?= (in_array($key, $arResult["Data"]["FilterableFields"])) ? " selected" : "" ?>><?= htmlspecialchars($value["Name"])." [".$key."]" ?></option><?
						}
						?>
						</select>
					</td>
				</tr>
				<?
				if ($arParams["BLOCK_ID"] <= 0)
				{
					?>
					<tr>
						<td valign="top" align="right"><?= GetMessage("BPWC_WNCT_TMPL") ?>:</td>
						<td valign="top">
							<select name="bp_template">
							<?
							if (IsModuleInstalled("bizprocdesigner"))
							{
								?>
								<option value=""><?= GetMessage("BPWC_WNCT_NEW_TMPL") ?></option>
								<option value="-"<?= ("-" == $arResult["Data"]["Template"]) ? " selected" : "" ?>><?= GetMessage("BPWC_WNCT_NEW_TMPL1") ?></option>
								<?
							}

							foreach ($arResult["AvailableTemplates"] as $key => $value)
							{
								?><option value="<?= $key ?>"<?= ($key == $arResult["Data"]["Template"]) ? " selected" : "" ?>><?= htmlspecialchars($value) ?></option><?
							}
							?>
							</select>
						</td>
					</tr>
					<?
				}
				?>
			</table>
			<?
		}
		elseif ($arResult["Step"] == 2)
		{
			?>

			<input type="hidden" name="bp_name" value="<?= htmlspecialchars($arResult["Data"]["Name"]) ?>">
			<input type="hidden" name="bp_description" value="<?= htmlspecialchars($arResult["Data"]["Description"]) ?>">
			<input type="hidden" name="bp_sort" value="<?= intval($arResult["Data"]["Sort"]) ?>">
			<input type="hidden" name="bp_image" value="<?= intval($arResult["Data"]["Image"]) ?>">
			<input type="hidden" name="bp_element_add" value="<?= htmlspecialchars($arResult["Data"]["ElementAdd"]) ?>">
			<?
			foreach ($arResult["Data"]["UserGroups"] as $value)
			{
				?><input type="hidden" name="bp_user_groups[]" value="<?= htmlspecialchars($value) ?>"><?
			}
			?>
			<input type="hidden" name="bp_template" value="<?= htmlspecialchars($arResult["Data"]["Template"]) ?>">

			<table class="bpwiz1-view-form data-table" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<th colspan="2"><?= GetMessage("BPWC_WNCT_SUBTITLE2") ?></th>
				</tr>
			<?
			foreach ($arResult["TemplateParameters"] as $parameterKey => $arParameter)
			{
				?>
				<tr>
					<td align="right" width="40%" valign="top"><?= $arParameter["Required"] ? "<span style=\"color:red\">*</span> " : ""?><?= htmlspecialchars($arParameter["Name"]) ?>:<?if (strlen($arParameter["Description"]) > 0) echo "<br /><small>".htmlspecialchars($arParameter["Description"])."</small><br />";?></td>
					<td width="60%" valign="top"><?
						echo $arResult["DocumentService"]->GetGUIFieldEdit(
							array("bizproc", "CBPVirtualDocument", "type_0"),
							"bizprocform",
							$parameterKey,
							$arParameter["Default"],
							$arParameter,
							false
						);
					?></td>
				</tr>
				<?
			}
			?>
			</table>
			<?
		}
		?>
		<input type="hidden" name="bp_step" value="<?= intval($arResult["Step"]) + 1 ?>">
		<input type="submit" name="doCreate" value="<?= $arParams["BLOCK_ID"] > 0 ? GetMessage("BPWC_WNCT_SAVE0") : (intval($arResult["Step"]) > 1 ? GetMessage("BPWC_WNCT_SAVE1") : GetMessage("BPWC_WNCT_SAVE2")) ?>">
		<input type="submit" name="doCancel"  value="<?= GetMessage("BPWC_WNCT_CANCEL") ?>">
	</form>
	<?
}
?>