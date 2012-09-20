<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?
if (strlen($arCurrentValues["mail_charset"]) <= 0)
	$arCurrentValues["mail_charset"] = "windows-1251";
if (strlen($arCurrentValues["mail_message_type"]) <= 0)
	$arCurrentValues["mail_message_type"] = "plain";
?>
<tr>
	<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPMA_PD_FROM") ?>:</td>
	<td width="60%">
		<input type="text" name="mail_user_from" id="id_mail_user_from" value="<?= htmlspecialchars($arCurrentValues["mail_user_from"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_mail_user_from', 'user');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPMA_PD_TO") ?>:</td>
	<td width="60%">
		<input type="text" name="mail_user_to" id="id_mail_user_to" value="<?= htmlspecialchars($arCurrentValues["mail_user_to"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_mail_user_to', 'user');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPMA_PD_SUBJECT") ?>:</td>
	<td width="60%">
		<input type="text" name="mail_subject" id="id_mail_subject" value="<?= htmlspecialchars($arCurrentValues["mail_subject"]) ?>" size="50">
		<input type="button" value="..." onclick="BPAShowSelector('id_mail_subject', 'string');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPMA_PD_BODY") ?>:</td>
	<td width="60%">
		<textarea name="mail_text" id="id_mail_text" rows="7" cols="40"><?= htmlspecialchars($arCurrentValues["mail_text"]) ?></textarea>
		<input type="button" value="..." onclick="BPAShowSelector('id_mail_text', 'string');">
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_MESS_TYPE") ?>:</td>
	<td width="60%">
		<select name="mail_message_type">
			<option value="plain"<?= $arCurrentValues["mail_message_type"] == "plain" ? " selected" : "" ?>><?= GetMessage("BPMA_PD_TEXT") ?></option>
			<option value="html"<?= $arCurrentValues["mail_message_type"] == "html" ? " selected" : "" ?>>Html</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_CP") ?>:</td>
	<td width="60%">
		<input type="text" name="mail_charset" id="id_mail_charset" size="50" value="<?= htmlspecialchars($arCurrentValues["mail_charset"]) ?>" />
		<input type="button" value="..." onclick="BPAShowSelector('id_mail_charset', 'string');">
	</td>
</tr>