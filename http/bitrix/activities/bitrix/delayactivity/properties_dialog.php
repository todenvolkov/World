<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tr>
	<td align="right" width="40%"><?= GetMessage("CPAD_DP_TIME") ?>:</td>
	<td width="60%">
		<input type="text" name="delay_time" id="id_delay_time" value="<?= htmlspecialchars($arCurrentValues["delay_time"]) ?>" size="20" />
		<input type="button" value="..." onclick="BPAShowSelector('id_delay_time', 'int');" />
		<select name="delay_type">
			<option value="s"<?= ($arCurrentValues["delay_type"] == "s") ? " selected" : "" ?>><?= GetMessage("CPAD_DP_TIME_S") ?></option>
			<option value="m"<?= ($arCurrentValues["delay_type"] == "m") ? " selected" : "" ?>><?= GetMessage("CPAD_DP_TIME_M") ?></option>
			<option value="h"<?= ($arCurrentValues["delay_type"] == "h") ? " selected" : "" ?>><?= GetMessage("CPAD_DP_TIME_H") ?></option>
			<option value="d"<?= ($arCurrentValues["delay_type"] == "d") ? " selected" : "" ?>><?= GetMessage("CPAD_DP_TIME_D") ?></option>
		</select>
	</td>
</tr>