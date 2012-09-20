<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?= $javascriptFunctions ?>
<tr>
	<td colspan="2">
		<table width="100%" border="0" cellpadding="2" cellspacing="2" id="bwfvc_addrow_table">
			<?
			$defaultFieldValue = "";
			$t = array_keys($arVariables);
			if (count($t) > 0)
				$defaultFieldValue = $t[0];
			$ind = -1;
			foreach ($arCurrentValues as $variableKey => $variableValue)
			{
				if (!array_key_exists($variableKey, $arVariables))
					continue;
				if (strlen($defaultFieldValue) <= 0)
					$defaultFieldValue = $variableKey;
				$ind++;
				?>
				<tr id="delete_row_<?= $ind ?>">
					<td>
						<select name="variable_field_<?= $ind ?>" onchange="BWFVCChangeFieldType(<?= $ind ?>, bwfvc_arFieldTypes[this.options[this.selectedIndex].value], this.options[this.selectedIndex].value)">
							<?
							foreach ($arVariables as $k => $v)
							{
								?><option value="<?= htmlspecialchars($k) ?>"<?= ($k == $variableKey) ? " selected" : "" ?>><?= htmlspecialchars($v["Name"]) ?></option><?
							}
							?>
						</select>
					</td>
					<td>=</td>
					<td id="id_td_variable_value_<?= $ind ?>">
						<input type="text" name="<?= htmlspecialchars($variableKey) ?>" value="<?= htmlspecialchars($variableValue) ?>">
					</td>
					<td align="right">
						<a href="#" onclick="BWFVCDeleteCondition(<?= $ind ?>); return false;"><?= GetMessage("BPSVA_PD_DELETE") ?></a>
					</td>
				</tr>
				<?
			}
			?>
		</table>

		<?= CAdminCalendar::ShowScript() ?>
		<script language="JavaScript">
		var bwfvc_arFieldTypes = {<?
		$fl = false;
		foreach ($arVariables as $key => $value)
		{
			if ($fl)
				echo ",";
			echo "'".CUtil::JSEscape($key)."':'".CUtil::JSEscape($value["Type"])."'";
			$fl = true;
		}
		?>};
		var bwfvc_arFieldOptions = {<?
		$fl = false;
		foreach ($arVariables as $key => $value)
		{
			if ($value["Type"] != "select")
				continue;
			if ($fl)
				echo ",";
			echo "'".CUtil::JSEscape($key)."':{";
			$ix = 0;
			foreach ($value["Options"] as $k => $v)
			{
				if ($ix > 0)
					echo ",";
				echo $ix.":{0:'".CUtil::JSEscape($k)."',1:'".CUtil::JSEscape($v)."'}";
				$ix++;
			}
			echo "}";

			$fl = true;
		}
		?>};

		function BWFVCHtmlSpecialChars(string, quote)
		{
			string = string.toString();
			string = string.replace(/&/g, '&amp;');
			string = string.replace(/</g, '&lt;');
			string = string.replace(/>/g, '&gt;');
			string = string.replace(/"/g, '&quot;');

			if (quote)
				string = string.replace(/'/g, '&#039;');

			return string;
		}

		function BWFVCChangeFieldType(ind, type, field)
		{
			var s = "";

			var valueTd = document.getElementById('id_td_variable_value_' + ind);

			var valueOld = "";
			if (document.forms["<?= $formName ?>"][field])
			{
				if (document.forms["<?= $formName ?>"][field].type == "select")
					valueOld = document.forms["<?= $formName ?>"][field].options[document.<?= $formName ?>[field].selectedIndex].value;
				else
					valueOld = document.forms["<?= $formName ?>"][field].value;

				if (typeof valueOld == "undefined")
					valueOld = "";
			}

			valueTd.innerHTML = objFieldsVars.GetGUIFieldEdit(field, valueOld, true);
		}

		var bwfvc_counter = <?= $ind ?>;

		function BWFVCAddCondition()
		{
			var addrowTable = document.getElementById('bwfvc_addrow_table');

			bwfvc_counter++;
			var newRow = addrowTable.insertRow(-1);
			newRow.id = "delete_row_" + bwfvc_counter;

			var newCell = newRow.insertCell(-1);
			var newSelect = document.createElement("select");
			newSelect.setAttribute('bwfvc_counter', bwfvc_counter);
			newSelect.onchange = function(){BWFVCChangeFieldType(this.getAttribute("bwfvc_counter"), bwfvc_arFieldTypes[this.options[this.selectedIndex].value], this.options[this.selectedIndex].value)};
			newSelect.name = "variable_field_" + bwfvc_counter;
			<?
			$i = -1;
			foreach ($arVariables as $key => $value)
			{
				$i++;
				?>newSelect.options[<?= $i ?>] = new Option("<?= CUtil::JSEscape($value["Name"]) ?>", "<?= CUtil::JSEscape($key) ?>");
				<?
			}
			?>
			newCell.appendChild(newSelect);

			var newCell = newRow.insertCell(-1);
			newCell.innerHTML = "=";

			var newCell = newRow.insertCell(-1);
			newCell.id = "id_td_variable_value_" + bwfvc_counter;
			newCell.innerHTML = "";

			var newCell = newRow.insertCell(-1);
			newCell.align="right";
			newCell.innerHTML = '<a href="#" onclick="BWFVCDeleteCondition(' + bwfvc_counter + '); return false;"><?= GetMessage("BPSVA_PD_DELETE") ?></a>';

			BWFVCChangeFieldType(bwfvc_counter, bwfvc_arFieldTypes['<?= CUtil::JSEscape($defaultFieldValue) ?>'], '<?= CUtil::JSEscape($defaultFieldValue) ?>');
		}

		function BWFVCDeleteCondition(ind)
		{
			var addrowTable = document.getElementById('bwfvc_addrow_table');

			var cnt = addrowTable.rows.length;
			for (i = 0; i < cnt; i++)
			{
				if (addrowTable.rows[i].id != 'delete_row_' + ind)
					continue;

				addrowTable.deleteRow(i);

				break;
			}
		}

		<?
		$i = -1;
		foreach ($arCurrentValues as $variableKey => $variableValue)
		{
			if (!array_key_exists($variableKey, $arVariables))
				continue;

			$i++;
			?>
			BWFVCChangeFieldType(<?= $i ?>, bwfvc_arFieldTypes['<?= CUtil::JSEscape($variableKey) ?>'], '<?= CUtil::JSEscape($variableKey) ?>');
			<?
		}

		if ($i < 0)
		{
			?>BWFVCAddCondition();<?
		}
		?>
		</script>
		<a href="#" onclick="BWFVCAddCondition(); return false;"><?= GetMessage("BPSVA_PD_ADD") ?></a>

	</td>
</tr>