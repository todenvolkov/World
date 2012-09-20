<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?= $javascriptFunctions ?>
<?
$arC = array(
	"=" => GetMessage("BPFC_PD_EQ"),
	">" => GetMessage("BPFC_PD_GT"),
	">=" => GetMessage("BPFC_PD_GE"),
	"<" => GetMessage("BPFC_PD_LT"),
	"<=" => GetMessage("BPFC_PD_LE"),
	"!=" => GetMessage("BPFC_PD_NE"),
	"in" => GetMessage("BPFC_PD_IN"),
);

$arFieldConditionCount = array(1);
if (array_key_exists("field_condition_count", $arCurrentValues) && strlen($arCurrentValues["field_condition_count"]) > 0)
	$arFieldConditionCount = explode(",", $arCurrentValues["field_condition_count"]);

$defaultFieldValue = "";
$arCurrentValues["field_condition_count"] = "";
$bwffcCounter = 0;
foreach ($arFieldConditionCount as $i)
{
	if (intval($i)."!" != $i."!")
		continue;

	$i = intval($i);
	if (strlen($arCurrentValues["field_condition_count"]) > 0)
	{
		$arCurrentValues["field_condition_count"] .= ",";
		?>
		<tr id="bwffc_deleterow_tr_<?= $i ?>">
			<td align="right" width="40%"><?= GetMessage("BPFC_PD_AND") ?></td>
			<td align="right" width="60%"><a href="#" onclick="BWFFCDeleteCondition(<?= $i ?>); return false;"><?= GetMessage("BPFC_PD_DELETE") ?></a></td>
		</tr>
		<?
	}
	$arCurrentValues["field_condition_count"] .= $i;
	if ($i > $bwffcCounter)
		$bwffcCounter = $i;
	?>
	<tr>
		<td align="right" width="40%"><?= GetMessage("BPFC_PD_FIELD") ?>:</td>
		<td width="60%">
			<select name="field_condition_field_<?= $i ?>" onchange="BWFFCChangeFieldType(<?= $i ?>, bwffc_arFieldTypes[this.options[this.selectedIndex].value], this.options[this.selectedIndex].value)">
				<?
				foreach ($arDocumentFields as $key => $value)
				{
					if (strlen($defaultFieldValue) <= 0)
						$defaultFieldValue = $key;
					?><option value="<?= htmlspecialchars($key) ?>"<?= ($arCurrentValues["field_condition_field_".$i] == $key) ? " selected" : "" ?>><?= htmlspecialchars($value["Name"]) ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%"><?= GetMessage("BPFC_PD_CONDITION") ?>:</td>
		<td width="60%">
			<select name="field_condition_condition_<?= $i ?>">
				<?
				foreach ($arC as $key => $value)
				{
					?><option value="<?= $key ?>"<?= ($arCurrentValues["field_condition_condition_".$i] == $key) ? " selected" : "" ?>><?= $value ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%"><?= GetMessage("BPFC_PD_VALUE") ?>:</td>
		<td width="60%" id="id_td_field_condition_value_<?= $i ?>">
			<input type="text" name="field_condition_value_<?= $i ?>" value="<?= htmlspecialchars($arCurrentValues["field_condition_value_".$i]) ?>">
		</td>
	</tr>
	<?
}
?>
<tr id="bwffc_addrow_tr">
	<td align="center" colspan="2">
		<?= CAdminCalendar::ShowScript() ?>
		<script language="JavaScript">
		var bwffc_arFieldTypes = {<?
		$fl = false;
		foreach ($arDocumentFields as $key => $value)
		{
			if ($fl)
				echo ",";
			echo "'".CUtil::JSEscape($key)."':'".CUtil::JSEscape($value["Type"])."'";
			$fl = true;
		}
		?>};

		var bwffc_counter = <?= $bwffcCounter + 1 ?>;

		function BWFFCChangeFieldType(ind, type, field)
		{
			var s = "";

			var valueTd = document.getElementById('id_td_field_condition_value_' + ind);

			var valueOld = "";
			if (document.forms["<?= $formName ?>"]["field_condition_value_" + ind])
			{
				if (document.forms["<?= $formName ?>"]["field_condition_value_" + ind].type == "select")
					valueOld = document.forms["<?= $formName ?>"]["field_condition_value_" + ind].options[document.<?= $formName ?>["field_condition_value_" + ind].selectedIndex].value;
				else
					valueOld = document.forms["<?= $formName ?>"]["field_condition_value_" + ind].value;
			}

			valueTd.innerHTML = objFieldsFC.GetGUIFieldEdit(field, valueOld, true, "field_condition_value_"+ind);
		}

		function BWFFCAddCondition()
		{
			var addrowTr = document.getElementById('bwffc_addrow_tr');
			var parentAddrowTr = addrowTr.parentNode;

			var cnt = parentAddrowTr.rows.length;
			for (i = 0; i < cnt; i++)
			{
				if (parentAddrowTr.rows[i].id != "bwffc_addrow_tr")
					continue;

				var newRow = parentAddrowTr.insertRow(i);
				newRow.id = "bwffc_deleterow_tr_" + bwffc_counter;
				var newCell = newRow.insertCell(-1);
				newCell.width="40%";
				newCell.align="right";
				newCell.innerHTML = "<?= GetMessage("BPFC_PD_AND") ?>";
				var newCell = newRow.insertCell(-1);
				newCell.width="60%";
				newCell.align="right";
				newCell.innerHTML = '<a href="#" onclick="BWFFCDeleteCondition(' + bwffc_counter + '); return false;"><?= GetMessage("BPFC_PD_DELETE") ?></a>';

				var newRow = parentAddrowTr.insertRow(i + 1);
				var newCell = newRow.insertCell(-1);
				newCell.width="40%";
				newCell.align="right";
				newCell.innerHTML = "<?= GetMessage("BPFC_PD_FIELD") ?>:";
				var newCell = newRow.insertCell(-1);
				newCell.width="60%";
				var newSelect = document.createElement("select");
				newSelect.setAttribute('bwffc_counter', bwffc_counter);
				newSelect.onchange = function(){BWFFCChangeFieldType(this.getAttribute("bwffc_counter"), bwffc_arFieldTypes[this.options[this.selectedIndex].value], this.options[this.selectedIndex].value)};
				newSelect.name = "field_condition_field_" + bwffc_counter;
				<?
				$i = -1;
				foreach ($arDocumentFields as $key => $value)
				{
					$i++;
					?>newSelect.options[<?= $i ?>] = new Option("<?= CUtil::JSEscape($value["Name"]) ?>", "<?= CUtil::JSEscape($key) ?>");
					<?
				}
				?>
				newCell.appendChild(newSelect);

				var newRow = parentAddrowTr.insertRow(i + 2);
				var newCell = newRow.insertCell(-1);
				newCell.width="40%";
				newCell.align="right";
				newCell.innerHTML = "<?= GetMessage("BPFC_PD_CONDITION") ?>:";
				var newCell = newRow.insertCell(-1);
				newCell.width="60%";
				var newSelect = document.createElement("select");
				newSelect.name = "field_condition_condition_" + bwffc_counter;
				<?
				$i = -1;
				foreach ($arC as $key => $value)
				{
					$i++;
					?>newSelect.options[<?= $i ?>] = new Option("<?= CUtil::JSEscape($value) ?>", "<?= CUtil::JSEscape($key) ?>");
					<?
				}
				?>
				newCell.appendChild(newSelect);

				var newRow = parentAddrowTr.insertRow(i + 3);
				var newCell = newRow.insertCell(-1);
				newCell.width="40%";
				newCell.align="right";
				newCell.innerHTML = "<?= GetMessage("BPFC_PD_VALUE") ?>:";
				var newCell = newRow.insertCell(-1);
				newCell.width="60%";
				newCell.id="id_td_field_condition_value_" + bwffc_counter;
				var newSelect = document.createElement("input");
				newSelect.type = "text";
				newSelect.name = "field_condition_value_" + bwffc_counter;
				newCell.appendChild(newSelect);

				BWFFCChangeFieldType(bwffc_counter, bwffc_arFieldTypes['<?= CUtil::JSEscape($defaultFieldValue) ?>'], '<?= CUtil::JSEscape($defaultFieldValue) ?>');

				document.getElementById('id_field_condition_count').value += "," + bwffc_counter;
				bwffc_counter++;

				break;
			}
		}

		function BWFFCDeleteCondition(ind)
		{
			var deleterowTr = document.getElementById('bwffc_deleterow_tr_' + ind);
			var parentDeleterowTr = deleterowTr.parentNode;

			var cnt = parentDeleterowTr.rows.length;
			for (i = 0; i < cnt; i++)
			{
				if (parentDeleterowTr.rows[i].id != 'bwffc_deleterow_tr_' + ind)
					continue;

				parentDeleterowTr.deleteRow(i + 3);
				parentDeleterowTr.deleteRow(i + 2);
				parentDeleterowTr.deleteRow(i + 1);
				parentDeleterowTr.deleteRow(i);

				var value = document.getElementById('id_field_condition_count').value;
				var ar = value.split(",");
				value = "";
				for (j = 0; j < ar.length; j++)
				{
					if (ar[j] != ind)
					{
						if (value.length > 0)
							value += ",";
						value += ar[j];
					}
				}
				document.getElementById('id_field_condition_count').value = value;

				break;
			}
		}

		<?
		foreach ($arFieldConditionCount as $i)
		{
			if (intval($i)."!" != $i."!")
				continue;

			$i = intval($i);
			$v = (array_key_exists("field_condition_field_".$i, $arCurrentValues) ? $arCurrentValues["field_condition_field_".$i] : $defaultFieldValue);
			?>
			BWFFCChangeFieldType(<?= $i ?>, bwffc_arFieldTypes['<?= CUtil::JSEscape($v) ?>'], '<?= CUtil::JSEscape($v) ?>');
			<?
		}
		?>
		</script>
		<input type="hidden" name="field_condition_count" id="id_field_condition_count" value="<?= htmlspecialchars($arCurrentValues["field_condition_count"]) ?>">
		<a href="#" onclick="BWFFCAddCondition(); return false;"><?= GetMessage("BPFC_PD_ADD") ?></a>
	</td>
</tr>