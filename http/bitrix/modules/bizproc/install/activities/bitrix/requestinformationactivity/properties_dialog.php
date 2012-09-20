<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?= $javascriptFunctions ?>
<script language="JavaScript">
var bwfvc_counter = 0;

function BPRIAEditForm(b)
{
	var f = document.getElementById('ria_pd_edit_form');
	var l = document.getElementById('ria_pd_list_form');

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

	//document.getElementById('btn_popup_save').disabled = b;
	//document.getElementById('btn_popup_cancel').disabled = b;
	//document.getElementById('btn_popup_close').disabled = b;

	if (b)
	{
		l.style.display = 'none';
		try{
			f.style.display = 'table-row';
		}catch(e){
			f.style.display = 'inline';
		}
	}
	else
	{
		f.style.display = 'none';
		try{
			l.style.display = 'table-row';
		}catch(e){
			l.style.display = 'inline';
		}
	}
}

function BPRIANewParam()
{
	BPRIAEditForm(true);

	document.getElementById("id_fri_title").value = "";
	document.getElementById("id_fri_name").value = "";
	document.getElementById("id_fri_options").value = "";
	//document.getElementById("id_fri_default").value = "";
	document.getElementById("id_fri_required").checked = false;
	document.getElementById("id_fri_multiple").checked = false;
	document.getElementById("id_fri_id").value = "0";

	document.getElementById("id_fri_type").selectedIndex = 0;
}

function BPRIAHtmlSpecialChars(string, quote)
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

function BPRIAAddParam(vId, vName, vTitle, vType, vOptions, vDefault, vRequired, vMultiple)
{
	var oTable = document.getElementById('ria_pd_list_table');

	var newRow;
	if (vId <= 0)
	{
		bwfvc_counter++;
		newRow = oTable.insertRow(-1);
		newRow.id = "delete_row_" + bwfvc_counter;
		vId = bwfvc_counter;
	}
	else
	{
		var cnt = oTable.rows.length;
		for (i = 0; i < cnt; i++)
		{
			if (oTable.rows[i].id == 'delete_row_' + vId)
			{
				newRow = oTable.rows[i];
				break;
			}
		}

		if (!newRow)
			return;

		while (newRow.cells.length > 0)
			newRow.deleteCell(-1);
	}

	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = vName + '<input type="hidden" id="requested_information_' + vId + '_Name" name="requested_information[' + vId + '][Name]" value="' + vName + '"><input type="hidden" id="requested_information_' + vId + '_Title" name="requested_information[' + vId + '][Title]" value="' + vTitle + '"><input type="hidden" id="requested_information_' + vId + '_Type" name="requested_information[' + vId + '][Type]" value="' + vType + '"><input type="hidden" id="requested_information_' + vId + '_Options" name="requested_information[' + vId + '][Options]" value="' + vOptions + '"><input type="hidden" id="requested_information_' + vId + '_Default" name="requested_information[' + vId + '][Default]" value="' + vDefault + '"><input type="hidden" id="requested_information_' + vId + '_Required" name="requested_information[' + vId + '][Required]" value="' + vRequired + '"><input type="hidden" id="requested_information_' + vId + '_Multiple" name="requested_information[' + vId + '][Multiple]" value="' + vMultiple + '">';

	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = vTitle;
	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = objFields.arFieldTypes[vType] ? objFields.arFieldTypes[vType]["Name"] : "";
	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = (vRequired == "Y" ? "<?= GetMessage('BPSFA_PD_YES') ?>" : "<?= GetMessage('BPSFA_PD_NO') ?>");
	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = (vMultiple == "Y" ? "<?= GetMessage('BPSFA_PD_YES') ?>" : "<?= GetMessage('BPSFA_PD_NO') ?>");

	var newCell = newRow.insertCell(-1);
	newCell.align="right";
	newCell.innerHTML = '<a href="javascript:void(0);" onclick="moveRowUp(this); return false;"><?= GetMessage("BP_WF_UP") ?></a> | <a href="javascript:void(0);" onclick="moveRowDown(this); return false;"><?= GetMessage("BP_WF_DOWN") ?></a> | <a href="#" onclick="BPRIAParamEditParam(' + vId + '); return false;"><?= GetMessage("BPSFA_PD_CHANGE") ?></a>&nbsp;|&nbsp;<a href="#" onclick="BPRIADeleteRow(' + vId + '); return false;"><?= GetMessage("BPSFA_PD_DELETE") ?></a>';
}

function BPRIADeleteRow(ind)
{
	var addrowTable = document.getElementById('ria_pd_list_table');

	var cnt = addrowTable.rows.length;
	for (i = 0; i < cnt; i++)
	{
		if (addrowTable.rows[i].id != 'delete_row_' + ind)
			continue;

		addrowTable.deleteRow(i);

		break;
	}
}

function BPRIAParamEditParam(ind)
{
	BPRIAEditForm(true);

	document.getElementById("id_fri_title").value = document.getElementById("requested_information_" + ind + "_Title").value;
	document.getElementById("id_fri_name").value = document.getElementById("requested_information_" + ind + "_Name").value;
	document.getElementById("id_fri_options").value = document.getElementById("requested_information_" + ind + "_Options").value;
	//document.getElementById("id_fri_default").value = document.getElementById("requested_information_" + ind + "_Default").value;
	document.getElementById("id_fri_required").checked = (document.getElementById("requested_information_" + ind + "_Required").value == "Y");
	document.getElementById("id_fri_multiple").checked = (document.getElementById("requested_information_" + ind + "_Multiple").value == "Y");
	document.getElementById("id_fri_id").value = ind;

	document.getElementById("id_fri_type").selectedIndex = objFields.arFieldTypes[document.getElementById("requested_information_" + ind + "_Type").value] ? objFields.arFieldTypes[document.getElementById("requested_information_" + ind + "_Type").value]["Index"] : 0;
	BPRIAChangeFieldType(
		document.getElementById("requested_information_" + ind + "_Type").value,
		document.getElementById("requested_information_" + ind + "_Default").value
	);
}

function BPRIAChangeFieldType(type, valueOld)
{
	var s = "";

	var field = "fri_default";
	var valueTd = document.getElementById('id_td_document_value');

//	var valueOld = value;
//	if (valueOld.length <= 0)
//	{
//		if (document.forms["<?= $formName ?>"][field])
//		{
//			if (document.forms["<?= $formName ?>"][field].type == "select")
//				valueOld = document.forms["<?= $formName ?>"][field].options[document.<?= $formName ?>[field].selectedIndex].value;
//			else
//				valueOld = document.forms["<?= $formName ?>"][field].value;
//		}
//	}

	valueTd.innerHTML = objFields.GetGUIFieldEditSimple(type, valueOld, 'fri_default');

	if (!objFields.arFieldTypes[type] || objFields.arFieldTypes[type]["BaseType"] != 'select')
	{
		document.getElementById('id_tr_pbria_options').style.display = 'none';
	}
	else
	{
		try{
			document.getElementById('id_tr_pbria_options').style.display = 'table-row';
		}catch(e){
			document.getElementById('id_tr_pbria_options').style.display = 'inline';
		}
	}
}

function BPRIAParamSaveForm()
{
	if (document.getElementById("id_fri_title").value.replace(/^\s+|\s+$/g, '').length <= 0)
	{
		alert('<?= GetMessage("BPSFA_PD_EMPTY_TITLE") ?>');
		document.getElementById("id_fri_title").focus();
		return;
	}
	if (document.getElementById("id_fri_name").value.replace(/^\s+|\s+$/g, '').length <= 0)
	{
		alert('<?= GetMessage("BPSFA_PD_EMPTY_NAME") ?>');
		document.getElementById("id_fri_name").focus();
		return;
	}
	if (document.getElementById("id_fri_name").value.match(/[^A-Za-z0-9\s._-]/g))
	{
		alert('<?= GetMessage("BPSFA_PD_WRONG_NAME") ?>');
		document.getElementById("id_fri_name").focus();
		return;
	}

	var friTitle = BPRIAHtmlSpecialChars(document.getElementById("id_fri_title").value);
	var friName = BPRIAHtmlSpecialChars(document.getElementById("id_fri_name").value);
	var friType = document.getElementById("id_fri_type").options[document.getElementById("id_fri_type").selectedIndex].value;
	var friOptions = BPRIAHtmlSpecialChars(document.getElementById("id_fri_options").value);
	var friDefault = BPRIAHtmlSpecialChars(objFields.SetGUIFieldEditSimple(friType, 'fri_default'));
	//document.getElementById("id_fri_default").value;
	var friRequired = (document.getElementById("id_fri_required").checked ? "Y" : "N");
	var friMultiple = (document.getElementById("id_fri_multiple").checked ? "Y" : "N");
	var friId = document.getElementById("id_fri_id").value;

	BPRIAAddParam(friId, friName, friTitle, friType, friOptions, friDefault, friRequired, friMultiple);

	document.getElementById("id_fri_title").value = "";
	document.getElementById("id_fri_name").value = "";
	document.getElementById("id_fri_options").value = "";
	//document.getElementById("id_fri_default").value = "";
	document.getElementById("id_fri_required").checked = false;
	document.getElementById("id_fri_multiple").checked = false;
	document.getElementById("id_fri_id").value = "0";

	document.getElementById("id_fri_type").selectedIndex = 0;

	BPRIAEditForm(false);
}

function moveRowUp(a)
{
	var row = a.parentNode.parentNode;
	if (row.previousSibling.previousSibling)
		row.parentNode.insertBefore(row, row.previousSibling);
}

function moveRowDown(a)
{
	var row = a.parentNode.parentNode;
	if (row.nextSibling)
	{
		if (row.nextSibling.nextSibling)
			row.parentNode.insertBefore(row, row.nextSibling.nextSibling);
		else
			row.parentNode.appendChild(row);
	}
}
</script>

<tr id="ria_pd_list_form">
	<td colspan="2">
		<table width="100%" border="0" cellpadding="2" cellspacing="2">
			<tr>
				<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPRIA_PD_APPROVERS") ?>:</td>
				<td width="60%">
					<?=CBPDocument::ShowParameterField("user", 'requested_users', $arCurrentValues['requested_users'], Array('rows'=>'1'))?>
				</td>
			</tr>
			<tr>
				<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPRIA_PD_NAME") ?>:</td>
				<td width="60%">
					<?=CBPDocument::ShowParameterField("string", 'requested_name', $arCurrentValues['requested_name'], Array('size'=>'50'))?>
				</td>
			</tr>
			<tr>
				<td align="right" width="40%" valign="top"><?= GetMessage("BPRIA_PD_DESCR") ?>:</td>
				<td width="60%" valign="top">
					<?=CBPDocument::ShowParameterField("text", 'requested_description', $arCurrentValues['requested_description'], Array('rows'=>'3'))?>
				</td>
			</tr>
		</table>
		<b><?= GetMessage("BPSFA_PD_FIELDS") ?></b><br>
		<style>
		.aaa {border-collapse:collapse !important;border-style:none solid solid !important;}
		.aaa td {
color:black !important;
font-family:Verdana,Arial,sans-serif !important;
font-style:normal !important;
font-size:11px !important;
font-variant:normal !important;
font-weight:normal !important;
letter-spacing:normal !important;
line-height:normal !important;

table-layout:auto !important;
text-decoration:none !important;

vertical-align:middle !important;
white-space:normal !important;
word-spacing:normal !important;
border:1px solid #CFD6E9 !important;
font-size:11px !important;
padding:3px !important;
		border-bottom:medium none !important;
border-top:medium none !important;}
		.aaa tr.heading td {background-color:#E7EAF5 !important;
color:#525355 !important;
font-weight:normal !important;
text-align:center !important;}
		</style>
		<table width="100%" id="ria_pd_list_table" class="aaa" border="1" style="width:100% !important;">
			<tr class="heading">
				<td style="border:1px solid #CFD6E9 !important; border-bottom:medium none !important; border-top:medium none !important;"><?= GetMessage("BPSFA_PD_F_NAME") ?></td>
				<td style="border:1px solid #CFD6E9 !important; border-bottom:medium none !important; border-top:medium none !important;"><?= GetMessage("BPSFA_PD_F_TITLE") ?></td>
				<td style="border:1px solid #CFD6E9 !important; border-bottom:medium none !important; border-top:medium none !important;"><?= GetMessage("BPSFA_PD_F_TYPE") ?></td>
				<td style="border:1px solid #CFD6E9 !important; border-bottom:medium none !important; border-top:medium none !important;"><?= GetMessage("BPSFA_PD_F_REQ") ?></td>
				<td style="border:1px solid #CFD6E9 !important; border-bottom:medium none !important; border-top:medium none !important;"><?= GetMessage("BPSFA_PD_F_MULT") ?></td>
				<td style="border:1px solid #CFD6E9 !important; border-bottom:medium none !important; border-top:medium none !important;">&nbsp;</td>
			</tr>
		</table>
		<br>
		<span style="padding: 10px;" ><a href="javascript:void(0);" onclick="BPRIANewParam()"><?= GetMessage("BPSFA_PD_F_ADD") ?></a></span>

		<script language="JavaScript">
		<?
		foreach ($arCurrentValues["requested_information"] as $arRequestedInformation)
		{
			?>BPRIAAddParam(0, '<?= CUtil::JSEscape(htmlspecialchars($arRequestedInformation["Name"])) ?>', '<?= CUtil::JSEscape(htmlspecialchars($arRequestedInformation["Title"])) ?>', '<?= CUtil::JSEscape($arRequestedInformation["Type"]) ?>', '<?= str_replace("\r\n", "\\r\\n", CUtil::JSEscape(htmlspecialchars($arRequestedInformation["Options"]))) ?>', '<?= CUtil::JSEscape(htmlspecialchars($arRequestedInformation["Default"])) ?>', '<?= CUtil::JSEscape($arRequestedInformation["Required"]) ?>', '<?= CUtil::JSEscape($arRequestedInformation["Multiple"]) ?>');<?
		}
		?>
		</script>
	</td>
</tr>

<tr id="ria_pd_edit_form">
	<td colspan="2">

		<table width="100%" border="0" cellpadding="2" cellspacing="2">
		<tr>
			<td align="center" colspan="2" style="align:center;"><b><?= GetMessage("BPSFA_PD_FIELD") ?></b></td>
		</tr>
		<tr>
			<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPSFA_PD_F_TITLE") ?>:</td>
			<td width="60%">
				<input type="text" size="50" name="fri_title" id="id_fri_title" value="">
			</td>
		</tr>
		<tr>
			<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPSFA_PD_F_NAME") ?>:</td>
			<td width="60%">
				<input type="text" size="20" name="fri_name" id="id_fri_name" value="">
			</td>
		</tr>
		<tr>
			<td align="right" width="40%"><span style="color:#FF0000;">*</span> <?= GetMessage("BPSFA_PD_F_TYPE") ?>:</td>
			<td width="60%">
				<select name="fri_type" id="id_fri_type" onchange="BPRIAChangeFieldType(this.options[this.selectedIndex].value, objFields.SetGUIFieldEditSimple(this.options[this.selectedIndex].value, 'fri_default'))">
					<?
					foreach ($arFieldTypes as $k => $v)
					{
						?><option value="<?= $k ?>"><?= $v["Name"] ?></option><?
					}
					?>
				</select>
			</td>
		</tr>
		<tr id="id_tr_pbria_options" style="display:none">
			<td align="right" width="40%" valign="top"><?= GetMessage("BPSFA_PD_F_VLIST") ?>:<br/><?= GetMessage("BPSFA_PD_F_VLIST_HINT") ?></td>
			<td width="60%">
				<?=CBPDocument::ShowParameterField("text", 'fri_options', "", array("rows" => 3, "cols" => 40, "id" => "id_fri_options"))?>
			</td>
		</tr>
		<tr>
			<td align="right" width="40%"><?= GetMessage("BPSFA_PD_F_DEF") ?>:</td>
			<td width="60%" id="id_td_document_value">
				<?=CBPDocument::ShowParameterField("string", 'fri_default', "", Array('id' => '', 'size'=>'20', "id" => "id_fri_default"))?>
			</td>
		</tr>
		<tr>
			<td align="right" width="40%"><?= GetMessage("BPSFA_PD_F_REQ") ?>:</td>
			<td width="60%">
				<input type="checkbox" name="fri_required" id="id_fri_required" value="Y">
			</td>
		</tr>
		<tr>
			<td align="right" width="40%"><?= GetMessage("BPSFA_PD_F_MULT") ?>:</td>
			<td width="60%">
				<input type="checkbox" name="fri_multiple" id="id_fri_multiple" value="Y">
			</td>
		</tr>
		<tr>
			<td align="center" colspan="2">
				<input type="hidden" name="fri_id" id="id_fri_id">
				<input type="button" value="<?= GetMessage("BPSFA_PD_SAVE") ?>" onclick="BPRIAParamSaveForm()" title="<?= GetMessage("BPSFA_PD_SAVE_HINT") ?>" />
				<input type="button" value="<?= GetMessage("BPSFA_PD_CANCEL") ?>" onclick="BPRIAEditForm(false);" title="<?= GetMessage("BPSFA_PD_CANCEL_HINT") ?>" />
			</td>
		</tr>
	</table>

	</td>
</tr>
<script>
document.getElementById('ria_pd_edit_form').style.display = 'none';
try{
	document.getElementById('ria_pd_list_form').style.display = 'table-row';
}catch(e){
	document.getElementById('ria_pd_list_form').style.display = 'inline';
}
</script>