<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?= $javascriptFunctions ?>
<script language="JavaScript">
var bwfvc_arBaseTypes = {<?
$fl = false;
foreach ($arFieldTypes as $key => $value)
{
	if ($fl)
		echo ",";
	echo "'".CUtil::JSEscape($key)."':'".CUtil::JSEscape($value["BaseType"])."'";
	$fl = true;
}
?>};

var bwfvc_arFieldNames = {<?
$fl = false;
foreach ($arDocumentFields as $key => $value)
{
	if ($fl)
		echo ",";
	echo "'".CUtil::JSEscape($key)."':'".htmlspecialchars(CUtil::JSEscape($value["Name"]))."'";
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

function BWFVCUnHtmlSpecialChars(string, quote)
{
	string = string.toString();

	if (quote)
		string = string.replace(/&#039;/g, "'");

	string = string.replace(/&quot;/g, "\"");
	string = string.replace(/&gt;/g, ">");
	string = string.replace(/&lt;/g, "<");
	string = string.replace(/&amp;/g, '&');

	return string;
}

function BWFVCChangeFieldType(ind, field)
{
	var s = "";

	var valueTd = document.getElementById('id_td_document_value_' + ind);

	var valueOld = "";
	if (document.forms["<?= $formName ?>"][field])
	{
		if (document.forms["<?= $formName ?>"][field].type == "select")
			valueOld = document.forms["<?= $formName ?>"][field].options[document.<?= $formName ?>[field].selectedIndex].value;
		else
			valueOld = document.forms["<?= $formName ?>"][field].value;
	}

	valueTd.innerHTML = objFields.GetGUIFieldEdit(field, valueOld, true);
}

var bwfvc_counter = -1;
var bwfvc_newfield_counter = -1;

function BWFVCAddCondition(field, val)
{
	var addrowTable = document.getElementById('bwfvc_addrow_table');

	bwfvc_counter++;
	var newRow = addrowTable.insertRow(-1);
	newRow.id = "delete_row_" + bwfvc_counter;

	var newCell = newRow.insertCell(-1);
	var newSelect = document.createElement("select");
	newSelect.setAttribute('bwfvc_counter', bwfvc_counter);
	newSelect.onchange = function(){BWFVCChangeFieldType(this.getAttribute("bwfvc_counter"), this.options[this.selectedIndex].value)};
	newSelect.id = "id_document_field_" + bwfvc_counter;
	newSelect.name = "document_field_" + bwfvc_counter;

	var i = -1;
	var i1 = -1;
	for (var key in bwfvc_arFieldNames)
	{
		i++;
		newSelect.options[i] = new Option(BWFVCUnHtmlSpecialChars(bwfvc_arFieldNames[key]), key);
		if (field.length > 0 && key == field)
			i1 = i;
	}
	newSelect.selectedIndex = i1;

	newCell.appendChild(newSelect);

	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = "=";

	var newCell = newRow.insertCell(-1);
	newCell.id = "id_td_document_value_" + bwfvc_counter;
	newCell.innerHTML = '<input type="text" id="id_' + field + '" name="' + field + '" value="' + val + '">';

	var newCell = newRow.insertCell(-1);
	newCell.align="right";
	newCell.innerHTML = '<a href="#" onclick="BWFVCDeleteCondition(' + bwfvc_counter + '); return false;"><?= GetMessage("BPSFA_PD_DELETE") ?></a>';

	BWFVCChangeFieldType(bwfvc_counter, field.length > 0 ? field : '<?= CUtil::JSEscape($defaultFieldValue) ?>');
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

function BWFVCCreateField(b)
{
	var f = document.getElementById('sfa_pd_edit_form');
	var l = document.getElementById('sfa_pd_list_form');
	if (b)
	{
		<?=$popupWindow->jsPopup?>.btnSave.disable();
		<?=$popupWindow->jsPopup?>.btnCancel.disable();
	}
	else
	{
		<?=$popupWindow->jsPopup?>.btnSave.enable();
		<?=$popupWindow->jsPopup?>.btnCancel.enable();
	}
//	document.getElementById('btn_popup_save').disabled = b;
//	document.getElementById('btn_popup_cancel').disabled = b;
	if (b)
	{
		l.style.display = 'none';
		try{f.style.display = 'table-row';}
		catch(e){f.style.display = 'inline';}
	}
	else
	{
		f.style.display = 'none';
		try{l.style.display = 'table-row';}
		catch(e){l.style.display = 'inline';}
	}
}

function BWFVCCreateFieldSave()
{
	var fldName = document.getElementById("id_fld_name").value;
	var fldCode = document.getElementById("id_fld_code").value;
	fldCode = fldCode.replace(/\W+/g, '');
	var fldType = document.getElementById("id_fld_type").options[document.getElementById("id_fld_type").selectedIndex].value;
	var fldAdditionalTypeInfo = objFields.SetGUITypeEdit(fldType);
	var fldMultiple = (document.getElementById("id_fld_multiple").checked ? "Y" : "N");
	var fldRequired = (document.getElementById("id_fld_required").checked ? "Y" : "N");
	var fldOptions = document.getElementById("id_fld_options").value;

	if (fldName.replace(/^\s+|\s+$/g, '').length <= 0)
	{
		alert('<?= GetMessage("BPSFA_PD_EMPTY_NAME") ?>');
		document.getElementById("id_fld_name").focus();
		return;
	}
	if (fldCode.replace(/^\s+|\s+$/g, '').length <= 0)
	{
		alert('<?= GetMessage("BPSFA_PD_EMPTY_CODE") ?>');
		document.getElementById("id_fld_code").focus();
		return;
	}
	if (fldCode.match(/[^A-Za-z0-9\s._-]/g))
	{
		alert('<?= GetMessage("BPSFA_PD_WRONG_CODE") ?>');
		document.getElementById("id_fld_code").focus();
		return;
	}

	var a = {};
	if (fldOptions.length > 0)
	{
		var arr = fldOptions.split("\r\n");
		var p, re = /\[([^]]+)\].+/;
		for (i in arr)
		{
			str = arr[i].replace(/^\s+|\s+$/g, '');
			if (str.length > 0)
			{
				id = str.match(re);
				if (id)
				{
					p = str.indexOf(']');
					id = id[1];
					val = str.substr(p+1);
				}
				else
				{
					val = str;
					id = val;
				}

				a[id] = val;
			}
		}
	}

	//bwfvc_arFieldOptions[fldCode] = a;
	//bwfvc_arFieldTypes[fldCode] = fldType;
	bwfvc_arFieldNames[fldCode] = BWFVCHtmlSpecialChars(fldName);
	objFields.AddField(fldCode, fldName, fldType, fldMultiple, a);

	for (var i = 0; i <= bwfvc_counter; i++)
	{
		var o = document.getElementById("id_document_field_" + i);
		if (o)
			o.options[o.options.length] = new Option(fldName, fldCode);
	}

	document.getElementById("id_fld_name").value = "";
	document.getElementById("id_fld_code").value = "";
	document.getElementById("id_fld_type").selectedIndex = -1;
	document.getElementById("id_fld_multiple").checked = false;
	document.getElementById("id_fld_required").checked = false;
	document.getElementById("id_fld_options").value = "";

	bwfvc_newfield_counter++;
	var cont = document.getElementById("bwfvc_container");
	cont.innerHTML += "<input type='hidden' name='new_field_name[" + bwfvc_newfield_counter + "]' value='" + BWFVCHtmlSpecialChars(fldName) + "'>";
	cont.innerHTML += "<input type='hidden' name='new_field_code[" + bwfvc_newfield_counter + "]' value='" + BWFVCHtmlSpecialChars(fldCode) + "'>";
	cont.innerHTML += "<input type='hidden' name='new_field_type[" + bwfvc_newfield_counter + "]' value='" + BWFVCHtmlSpecialChars(fldType) + "'>";
	cont.innerHTML += "<input type='hidden' name='new_field_type_additional[" + bwfvc_newfield_counter + "]' value='" + BWFVCHtmlSpecialChars(fldAdditionalTypeInfo) + "'>";
	cont.innerHTML += "<input type='hidden' name='new_field_mult[" + bwfvc_newfield_counter + "]' value='" + BWFVCHtmlSpecialChars(fldMultiple) + "'>";
	cont.innerHTML += "<input type='hidden' name='new_field_req[" + bwfvc_newfield_counter + "]' value='" + BWFVCHtmlSpecialChars(fldRequired) + "'>";
	cont.innerHTML += "<input type='hidden' name='new_field_options[" + bwfvc_newfield_counter + "]' value='" + BWFVCHtmlSpecialChars(fldOptions) + "'>";

	BWFVCCreateField(false);

	BWFVCAddCondition(fldCode, "");
}

function BWFVCCreateFieldChangeType(type)
{
	var str = objFields.GetGUITypeEdit(type);
	document.getElementById('WFSAdditionalTypeInfo').innerHTML = str;
	document.getElementById('WFSAdditionalTypeInfo').style.display = ((str.length > 0) ? 'block' : 'none');

	if (bwfvc_arBaseTypes[type] != 'select')
	{
		document.getElementById('id_tr_fld_options').style.display = 'none';
	}
	else
	{
		try{
			document.getElementById('id_tr_fld_options').style.display = 'table-row';
		}catch(e){
			document.getElementById('id_tr_fld_options').style.display = 'inline';
		}
	}
}
</script>

<tr id="sfa_pd_list_form" style="display:block">
	<td colspan="2">
		<table width="100%" border="0" cellpadding="2" cellspacing="2" id="bwfvc_addrow_table">
		</table>
		<a href="#" onclick="BWFVCAddCondition('', ''); return false;"><?= GetMessage("BPSFA_PD_ADD") ?></a>
		<a href="#" onclick="BWFVCCreateField(true); return false;"><?= GetMessage("BPSFA_PD_CREATE") ?></a>
		<span id="bwfvc_container"></span>
	</td>
</tr>

<tr id="sfa_pd_edit_form" style="display:none">
	<td colspan="2">

	<table width="100%" border="0" cellpadding="2" cellspacing="2">
	<tr>
		<td align="center" colspan="2" style="align:center;"><b><?= GetMessage("BPSFA_PD_FIELD") ?></b></td>
	</tr>
	<tr>
		<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPSFA_PD_F_NAME") ?>:</td>
		<td width="60%">
			<input type="text" name="fld_name" id="id_fld_name" value="" />
		</td>
	</tr>
	<tr>
		<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPSFA_PD_F_CODE") ?>:</td>
		<td width="60%">
			<input type="text" name="fld_code" id="id_fld_code" value="" />
		</td>
	</tr>
	<tr>
		<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPSFA_PD_F_TYPE") ?>:</td>
		<td width="60%">
			<select name="fld_type" id="id_fld_type" onchange="BWFVCCreateFieldChangeType(this.options[this.selectedIndex].value)">
				<?
				foreach ($arFieldTypes as $key => $value)
				{
					?><option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value["Name"]) ?></option><?
				}
				?>
			</select>
			<span id="WFSAdditionalTypeInfo"></span>
		</td>
	</tr>
	<tr>
		<td align="right" width="40%"><?= GetMessage("BPSFA_PD_F_MULT") ?>:</td>
		<td width="60%">
			<input type="checkbox" name="fld_multiple" id="id_fld_multiple" value="Y" />
		</td>
	</tr>
	<tr>
		<td align="right" width="40%"><?= GetMessage("BPSFA_PD_F_REQ") ?>:</td>
		<td width="60%">
			<input type="checkbox" name="fld_required" id="id_fld_required" value="Y" />
		</td>
	</tr>
	<tr id="id_tr_fld_options" style="display:none">
		<td align="right" width="40%"><?= GetMessage("BPSFA_PD_F_LIST") ?>:</td>
		<td width="60%">
			<textarea name="fld_options" id="id_fld_options" rows="3" cols="30"></textarea>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<input type="button" value="<?= GetMessage("BPSFA_PD_SAVE") ?>" onclick="BWFVCCreateFieldSave()" title="<?= GetMessage("BPSFA_PD_SAVE_HINT") ?>" />
			<input type="button" value="<?= GetMessage("BPSFA_PD_CANCEL") ?>" onclick="BWFVCCreateField(false);" title="<?= GetMessage("BPSFA_PD_CANCEL_HINT") ?>" />
		</td>
	</tr>
	</table>

	</td>
</tr>
<script>
<?
foreach ($arCurrentValues as $fieldKey => $documentFieldValue)
{
	if (!array_key_exists($fieldKey, $arDocumentFields))
		continue;
	?>
	BWFVCAddCondition('<?= CUtil::JSEscape($fieldKey) ?>', '<?= CUtil::JSEscape($documentFieldValue) ?>');
	<?
}

if (count($arCurrentValues) <= 0)
{
	?>BWFVCAddCondition("", "");<?
}
?>

document.getElementById('sfa_pd_edit_form').style.display = 'none';
try{
	document.getElementById('sfa_pd_list_form').style.display = 'table-row';
}catch(e){
	document.getElementById('sfa_pd_list_form').style.display = 'inline';
}
</script>
