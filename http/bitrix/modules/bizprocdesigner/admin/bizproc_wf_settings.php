<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/include.php");
IncludeModuleLangFile(__FILE__);

CUtil::DecodeUriComponent($_POST);

if(LANG_CHARSET != "UTF-8" && is_array($_POST["arWorkflowParameters"]))
{
	foreach($_POST["arWorkflowParameters"] as $name=>$param)
	{
		if(is_array($_POST["arWorkflowParameters"][$name]["Options"]))
		{
			$newarr = Array();
			foreach($_POST["arWorkflowParameters"][$name]["Options"] as $k=>$v)
				$newarr[$GLOBALS["APPLICATION"]->ConvertCharset($k, "UTF-8", LANG_CHARSET)] = $v;
			$_POST["arWorkflowParameters"][$name]["Options"] = $newarr;
		}
	}
}

$arWorkflowParameters = $_POST['arWorkflowParameters'];

if(LANG_CHARSET != "UTF-8" && is_array($_POST["arWorkflowVariables"]))
{
	foreach($_POST["arWorkflowVariables"] as $name=>$param)
	{
		if(is_array($_POST["arWorkflowVariables"][$name]["Options"]))
		{
			$newarr = Array();
			foreach($_POST["arWorkflowVariables"][$name]["Options"] as $k=>$v)
				$newarr[$GLOBALS["APPLICATION"]->ConvertCharset($k, "UTF-8", LANG_CHARSET)] = $v;
			$_POST["arWorkflowVariables"][$name]["Options"] = $newarr;
		}
	}
}

$arWorkflowVariables = $_POST['arWorkflowVariables'];

$canWrite = CBPDocument::CanUserOperateDocumentType(
		CBPCanUserOperateOperation::CreateWorkflow,
		$GLOBALS["USER"]->GetID(),
		array(MODULE_ID, ENTITY, $_POST['document_type'])
	);

if(!$canWrite)
{
	$popupWindow->ShowError(GetMessage("ACCESS_DENIED"));
	die();
}

if($_POST["save"] == "Y")
{
	$perms = Array();
	foreach($_POST['perm'] as $t=>$v)
		$perms[$t] = CBPHelper::UsersStringToArray($v, array(MODULE_ID, ENTITY, $_POST['document_type']), $arErrors);

	echo CUtil::PhpToJSObject($perms, false);
	die();
}

$APPLICATION->ShowTitle(GetMessage("BIZPROC_WFS_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$runtime = CBPRuntime::GetRuntime();
$runtime->StartRuntime();

$documentService = $runtime->GetService("DocumentService");
echo $documentService->GetJSFunctionsForFields(array(MODULE_ID, ENTITY, $_POST['document_type']), "objFields");

$arAllowableOperations = $documentService->GetAllowableOperations(array(MODULE_ID, ENTITY, $_POST['document_type']));

$arWorkflowParameterTypesTmp = $documentService->GetDocumentFieldTypes(array(MODULE_ID, ENTITY, $_POST['document_type']));
$arWorkflowParameterTypes = array();
$arWorkflowParameterBaseTypes = array();
foreach ($arWorkflowParameterTypesTmp as $key => $value)
{
	$arWorkflowParameterTypes[$key] = $value["Name"];
	$arWorkflowParameterBaseTypes[$key] = $value["BaseType"];
}

//$arWorkflowParameterTypes = Array(
//		"string" => GetMessage("BIZPROC_WFS_PROP_STRING"),
//		"text" => GetMessage("BIZPROC_WFS_PROP_TEXT"),
//		"int" => GetMessage("BIZPROC_WFS_PROP_INT"),
//		"double" => GetMessage("BIZPROC_WFS_PROP_DOUBLE"),
//		"select" => GetMessage("BIZPROC_WFS_PROP_SELECT"),
//		"bool" => GetMessage("BIZPROC_WFS_PROP_BOOL"),
//		"date" => GetMessage("BIZPROC_WFS_PROP_DATA"),
//		"datetime" => GetMessage("BIZPROC_WFS_PROP_DATETIME"),
//		"user" => GetMessage("BIZPROC_WFS_PROP_USER"),
//	);

CBPDocument::AddShowParameterInit(MODULE_ID, "only_users", $_POST['document_type'], ENTITY);
?>
<script type="text/javascript">
BX.WindowManager.Get().SetTitle('<?= GetMessage("BIZPROC_WFS_TITLE") ?>');

var WFSTypes = <?=CUtil::PhpToJSObject($arWorkflowParameterTypes)?>;
var WFSBaseTypes = <?=CUtil::PhpToJSObject($arWorkflowParameterBaseTypes)?>;

var WFSParams = <?=(is_array($arWorkflowParameters)?CUtil::PhpToJSObject($arWorkflowParameters):'{}')?>;
var WFSVars = <?=(is_array($arWorkflowVariables)?CUtil::PhpToJSObject($arWorkflowVariables):'{}')?>;

function WFSStart()
{
	var id;
	for(id in WFSParams)
	{
		WFSParamAddParam(id, WFSParams[id], 'P');
	}

	for(id in WFSVars)
	{
		WFSParamAddParam(id, WFSVars[id], 'V');
	}

	document.getElementById('WFStemplate_name').value = workflowTemplateName;
	document.getElementById('WFStemplate_description').value = workflowTemplateDescription;

	document.getElementById('WFStemplate_autostart1').checked = workflowTemplateAutostart & 1;
	document.getElementById('WFStemplate_autostart2').checked = workflowTemplateAutostart & 2;
}

function  WFSFSave()
{
	var perm = 'save=Y&document_type=<?=urlencode($_POST['document_type'])?>';
	<?foreach($arAllowableOperations as $op_id=>$op_name):?>
		perm += '&perm[<?=$op_id?>]='+encodeURIComponent(document.getElementById('id_P<?=$op_id?>').value);
	<?endforeach;?>

	BX.showWait();
	BX.ajax({
		'url':'/bitrix/admin/<?=MODULE_ID?>_bizproc_wf_settings.php?lang=<?=LANGUAGE_ID?>&entity=<?=ENTITY?>',
		'method':'POST',
		'data' : perm,
		'dataType': 'json',
		'timeout': 10,
		'async': false,
		'start': true,
		'onsuccess': WFSSaveOK, 
		'onfailure': WFSSaveN
	});
}

function WFSSaveN(o)
{
	BX.closeWait();
	alert('<?=GetMessage("BP_WF_SAVEERR")?>');
}

function WFSSaveOK(permissions)
{
	BX.closeWait();

	arWorkflowParameters = {};
	var i, t = document.getElementById('WFSListP');
	for(i=1; i<t.rows.length; i++)
		arWorkflowParameters[t.rows[i].paramId] = WFSParams[t.rows[i].paramId];

	arWorkflowVariables = WFSVars;
	workflowTemplateName = document.getElementById('WFStemplate_name').value;
	workflowTemplateDescription = document.getElementById('WFStemplate_description').value;
	workflowTemplateAutostart = ((document.getElementById('WFStemplate_autostart1').checked ? 1 : 0) | (document.getElementById('WFStemplate_autostart2').checked ? 2 : 0));

	rootActivity['Properties']['Permission'] = permissions;

	BX.WindowManager.Get().CloseDialog();
}


function WFSParamEditForm (b, Type)
{
	var f = document.getElementById('dparamform'+Type);
	var l = document.getElementById('dparamlist'+Type);
	document.getElementById('dpsavebutton').disabled = b;
	document.getElementById('dpcancelbutton').disabled = b;
	if(b)
	{
		l.style.display = 'none';
		f.style.display = 'block';
	}
	else
	{
		f.style.display = 'none';
		l.style.display = 'block';
	}
}

function WFSParamSetType(t, Type, v)
{
	var p = 0;
	for(var i in WFSTypes)
	{
		if(i == t)
		{
			document.getElementById("WFSFormType"+Type).selectedIndex = p;
			break;
		}
		p++;
	}

	var str = objFields.GetGUITypeEdit(t);
	document.getElementById('WFSAdditionalTypeInfo'+Type).innerHTML = str;
	document.getElementById('WFSAdditionalTypeInfo'+Type).style.display = ((str.length > 0) ? 'block' : 'none');

	document.getElementById('tdWFSFormDefault'+Type).innerHTML = objFields.GetGUIFieldEditSimple(t, v, 'WFSFormDefault'+Type);

	if (WFSBaseTypes[t] != 'select')
	{
		document.getElementById('WFSFormOptionsRow'+Type).style.display = 'none';
	}
	else
	{
		document.getElementById('WFSFormOptionsRow'+Type).style.display = '';
	}
}

function WFSParamDeleteParam(ob, Type)
{
	var id = ob.parentNode.parentNode.paramId;
	if(Type=='V')
		delete WFSVars[id];
	else
		delete WFSParams[id];
	var i, t = document.getElementById('WFSList'+Type);
	for(i=1; i<t.rows.length; i++)
	{
		if(t.rows[i].paramId == id)
		{
			t.deleteRow(i);
			return;
		}
	}
}

var lastEd = false;
function WFSParamEditParam(ob, Type)
{
	WFSParamEditForm(true, Type);

	lastEd = ob.parentNode.parentNode.paramId;

	var s = (Type=='V' ? WFSVars[ob.parentNode.parentNode.paramId] : WFSParams[ob.parentNode.parentNode.paramId]);

	document.getElementById("WFSFormId"+Type).value = lastEd;
	document.getElementById("WFSFormId"+Type).readOnly = true;

	document.getElementById("WFSFormName"+Type).value = s['Name'];
	document.getElementById("WFSFormDesc"+Type).value = s['Description'];

	document.getElementById("WFSFormOptions"+Type).value = '';
	if(WFSBaseTypes[s['Type']] == 'select')
	{
		var str = '', p=0;
		for(var i in s['Options'])
		{
			if(i != s['Options'][i])
				str = str + '[' + i + ']' + s['Options'][i];
			else
				str = str + s['Options'][i];

			str = str + '\r\n';
		}
		document.getElementById("WFSFormOptions"+Type).value = str;
	}

	document.getElementById("WFSFormReq"+Type).checked = (s['Required'] == 1);
	document.getElementById("WFSFormMult"+Type).checked = (s['Multiple'] == 1);

	//document.getElementById("id_WFSFormDefault").value = s['Default'];

	WFSParamSetType(s['Type'], Type, s['Default']);

//	if(s['Type'] == 'bool')
//		document.getElementById("id_WFSFormDefault").selectedIndex = (s['Default']==1? 0 : 1);
//	else
//		document.getElementById("id_WFSFormDefault").value = s['Default'];

	document.getElementById("WFSFormName"+Type).focus();
}

function WFSParamSaveForm(Type)
{
	if(document.getElementById("WFSFormName"+Type).value.replace(/^\s+|\s+$/g, '').length <= 0)
	{
		alert('<?=GetMessage("BIZPROC_WFS_PARAM_REQ")?>');
		document.getElementById("WFSFormName"+Type).focus();
		return;
	}

	if(document.getElementById("WFSFormId"+Type).value.replace(/^\s+|\s+$/g, '').length <= 0)
	{
		alert('<?=GetMessage("BIZPROC_WFS_PARAM_ID")?>');
		document.getElementById("WFSFormId"+Type).focus();
		return;
	}

	if(document.getElementById("WFSFormId"+Type).value.match(/[^A-Za-z0-9\s._-]/g))
	{
		alert('<?=GetMessage("BIZPROC_WFS_PARAM_ID1")?>');
		document.getElementById("WFSFormId"+Type).focus();
		return;
	}
	var N = lastEd;
	if(Type=='V')
		WFSData = WFSVars;
	else
		WFSData = WFSParams;

	if(lastEd == false)
	{
		lastEd = document.getElementById("WFSFormId"+Type).value.replace(/^\s+|\s+$/g, '');
		WFSData[lastEd] = {};
	}

	WFSData[lastEd]['Name'] = document.getElementById("WFSFormName"+Type).value.replace(/^\s+|\s+$/g, '');
	WFSData[lastEd]['Description'] = document.getElementById("WFSFormDesc"+Type).value;
	WFSData[lastEd]['Type'] = document.getElementById("WFSFormType"+Type).value;
	WFSData[lastEd]['Required'] = document.getElementById("WFSFormReq"+Type).checked;
	WFSData[lastEd]['Multiple'] = document.getElementById("WFSFormMult"+Type).checked;

	WFSData[lastEd]['Default'] = objFields.SetGUIFieldEditSimple(WFSData[lastEd]['Type'], 'WFSFormDefault'+Type);

	WFSData[lastEd]['Options'] = {};
	if (WFSBaseTypes[WFSData[lastEd]['Type']] == 'select')
	{
		var i, id, val, str = document.getElementById("WFSFormOptions"+Type).value;
		var arr = str.split(/[\r\n]+/);
		var p, re = /\[([^\]]+)\].+/;
		for(i in arr)
		{
			str = arr[i].replace(/^\s+|\s+$/g, '');
			if(str.length>0)
			{
				id = str.match(re);
				if(id)
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
				WFSData[lastEd]['Options'][id] = val;
			}
		}
	}

	if(N==false)
		WFSParamAddParam(lastEd, WFSData[lastEd], Type);

	WFSParamFillParam(lastEd, WFSData[lastEd], Type);
	WFSParamEditForm(false, Type);
}

function WFSParamFillParam(id, p, Type)
{
	var i, t = document.getElementById('WFSList'+Type);
	for(i=1; i<t.rows.length; i++)
	{
		if(t.rows[i].paramId == id)
		{
			var r = t.rows[i].cells;

			r[0].innerHTML = '<a href="javascript:void(0);" onclick="WFSParamEditParam(this, \''+Type+'\');">'+HTMLEncode(p['Name'])+"</a>";
			r[1].innerHTML = (WFSTypes[p['Type']] ? WFSTypes[p['Type']]: p['Type'] );
			r[2].innerHTML = (p['Required']==1?'<?=GetMessage("BIZPROC_WFS_YES")?>':'<?=GetMessage("BIZPROC_WFS_NO")?>');
			r[3].innerHTML = (p['Multiple']==1?'<?=GetMessage("BIZPROC_WFS_YES")?>':'<?=GetMessage("BIZPROC_WFS_NO")?>');
			if(WFSBaseTypes[p['Type']]=='bool')
			{
				if(p['Default']==1 || p['Default']=='Y')
					r[4].innerHTML = '<?=GetMessage("BIZPROC_WFS_YES")?>';
				else if(p['Default']=='N')
					r[4].innerHTML = '<?=GetMessage("BIZPROC_WFS_NO")?>';
				else
					r[4].innerHTML = '';
			}
			else
				r[4].innerHTML = HTMLEncode(p['Default']);

			return true;
		}
	}
}

function WFSParamNewParam(Type)
{
	lastEd = false;
	WFSParamEditForm(true, Type);

	var i;
	for(i=1; i<10000; i++)
	{
		if(Type != 'V')
		{
			if(!WFSParams['Parameter'+i])
				break;
		}
		else
		{
			if(!WFSVars['Variable'+i])
				break;
		}
	}

	document.getElementById("WFSFormId"+Type).value = (Type != 'V' ? 'Parameter'+i : 'Variable'+i);
	document.getElementById("WFSFormId"+Type).readOnly = false;

	document.getElementById("WFSFormType"+Type).selectedIndex = 0;
	WFSParamSetType('string', Type);

	document.getElementById("WFSFormName"+Type).value = '';
	document.getElementById("WFSFormDesc"+Type).value = '';

	document.getElementById("WFSFormOptions"+Type).value = '';
	document.getElementById("WFSFormReq"+Type).checked = false;
	document.getElementById("WFSFormMult"+Type).checked = false;


	try{
	document.getElementById("id_WFSFormDefault"+Type).value = '';
	} catch(e){}
	document.getElementById("WFSFormName"+Type).focus();
}

function WFSParamAddParam(id, p, Type)
{
	var t = document.getElementById('WFSList'+Type);
	var r = t.insertRow(-1);
	r.paramId = id;
	var c = r.insertCell(-1);
	c = r.insertCell(-1);
	c = r.insertCell(-1);
	c.align="center";
	c = r.insertCell(-1);
	c.align="center";
	c = r.insertCell(-1);
	c = r.insertCell(-1);
	c.innerHTML = ((Type == "P") ? '<a href="javascript:void(0);" onclick="moveRowUp(this); return false;"><?= GetMessage("BP_WF_UP") ?></a> | <a href="javascript:void(0);" onclick="moveRowDown(this); return false;"><?= GetMessage("BP_WF_DOWN") ?></a> | ' : '') + '<a href="javascript:void(0);" onclick="WFSParamEditParam(this, \''+Type+'\');"><?=GetMessage("BIZPROC_WFS_CHANGE_PARAM")?></a> | <a href="javascript:void(0);" onclick="WFSParamDeleteParam(this, \''+Type+'\');"><?=GetMessage("BIZPROC_WFS_DEL_PARAM")?></a>';
	WFSParamFillParam(id, p, Type);
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

setTimeout(WFSStart, 0);
</script>

<form id="bizprocform" name="bizprocform" method="post">
<?=bitrix_sessid_post()?>
<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("BIZPROC_WFS_TAB_MAIN"), "ICON" => "group_edit", "TITLE" => GetMessage("BIZPROC_WFS_TAB_MAIN_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("BIZPROC_WFS_TAB_PARAM"), "ICON" => "group_edit", "TITLE" => GetMessage("BIZPROC_WFS_TAB_PARAM_TITLE")),
	array("DIV" => "edit3", "TAB" => GetMessage("BP_WF_TAB_VARS"), "ICON" => "group_edit", "TITLE" => GetMessage("BP_WF_TAB_VARS_TITLE")),
	array("DIV" => "edit4", "TAB" => GetMessage("BP_WF_TAB_PERM"), "ICON" => "group_edit", "TITLE" => GetMessage("BP_WF_TAB_PERM_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin();

$tabControl->BeginNextTab();
?>
<tr>
	<td><?echo GetMessage("BIZPROC_WFS_PAR_NAME")?></td>
	<td><input type="text" id="WFStemplate_name" value="<?=htmlspecialchars($_POST['workflowTemplateName'])?>" size="40"></td>
</tr>
<tr>
	<td valign="top"><?echo GetMessage("BIZPROC_WFS_PAR_DESC")?></td>
	<td><textarea cols="35" rows="5"  id="WFStemplate_description"><?=htmlspecialchars($_POST['workflowTemplateDescription'])?></textarea></td>
</tr>
<tr>
	<td valign="top"><?echo GetMessage("BIZPROC_WFS_PAR_AUTO")?></td>
	<td>
		<input type="checkbox" id="WFStemplate_autostart1" value="Y"><label for="WFStemplate_autostart1"><?echo GetMessage("BIZPROC_WFS_PAR_AUTO_ADD")?></label><br>
		<input type="checkbox" id="WFStemplate_autostart2" value="Y"><label for="WFStemplate_autostart2"><?echo GetMessage("BIZPROC_WFS_PAR_AUTO_UPD")?></label>
	</td>
</tr>

<?
$tabControl->BeginNextTab();
?>
<tr>
	<td colspan="2">
		<div id="dparamlistP">
			<table width="100%" class="internal" id="WFSListP">
				<tr class="heading">
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_NAME")?></td>
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_TYPE")?></td>
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_REQUIRED")?></td>
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_MULT")?></td>
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_DEF")?></td>
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_ACT")?></td>
				</tr>
			</table>
			<br>
			<span style="padding: 10px;"><a href="javascript:void(0);" onclick="WFSParamNewParam('P')"><?echo GetMessage("BIZPROC_WFS_ADD_PARAM")?></a></span>
		</div>
		<div id="dparamformP" style="display: none">
			<table class="internal">
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAMID")?>:</td>
					<td><input type="text" size="20" id="WFSFormIdP" readonly=readonly></td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAM_NAME")?>:</td>
					<td><input type="text" size="30" id="WFSFormNameP"></td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAMDESC")?>:</td>
					<td><textarea id="WFSFormDescP" rows="2" cols="30"></textarea></td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAM_TYPE")?>:</td>
					<td>
						<select id="WFSFormTypeP" onchange="WFSParamSetType(this.value, 'P');">
								<?foreach($arWorkflowParameterTypes as $k=>$v):?>
									<option value="<?=$k?>"><?=htmlspecialchars($v)?></option>
								<?endforeach;?>
						</select><br />
						<span id="WFSAdditionalTypeInfoP"></span>
					</td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAM_REQUIRED")?>:</td>
					<td><input type="checkbox" id="WFSFormReqP" value="Y"></td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAM_MULT")?>:</td>
					<td><input type="checkbox" id="WFSFormMultP" value="Y"></td>
				</tr>
				<tr id="WFSFormOptionsRowP" style="display: none;">
					<td>
					<?echo GetMessage("BIZPROC_WFS_PARAMLIST")?><br><br>
					<?echo GetMessage("BIZPROC_WFS_PARAMLIST_DESC")?>
					</td>
					<td><textarea id="WFSFormOptionsP" rows="5" cols="30"></textarea></td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAMDEF")?>:</td>
					<td id="tdWFSFormDefaultP">
						<input id="id_WFSFormDefaultP">
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type="button" value="OK" onclick="WFSParamSaveForm('P')"><input type="button" onclick="WFSParamEditForm(false, 'P')" value="<?echo GetMessage("BIZPROC_WFS_BUTTON_CANCEL")?>"></td>
				</tr>
			</table>
		</div>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
?>
<tr>
	<td colspan="2">
		<div id="dparamlistV">
			<table width="100%" class="internal" id="WFSListV">
				<tr class="heading">
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_NAME")?></td>
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_TYPE")?></td>
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_REQUIRED")?></td>
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_MULT")?></td>
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_DEF")?></td>
					<td><?echo GetMessage("BIZPROC_WFS_PARAM_ACT")?></td>
				</tr>
			</table>
			<br>
			<span style="padding: 10px;"><a href="javascript:void(0);" onclick="WFSParamNewParam('V')"><?echo GetMessage("BP_WF_VAR_ADD")?></a></span>
		</div>
		<div id="dparamformV" style="display: none">
			<table class="internal">
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAMID")?>:</td>
					<td><input type="text" size="20" id="WFSFormIdV" readonly=readonly></td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAM_NAME")?>:</td>
					<td><input type="text" size="30" id="WFSFormNameV"></td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAMDESC")?>:</td>
					<td><textarea id="WFSFormDescV" rows="2" cols="30"></textarea></td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAM_TYPE")?>:</td>
					<td>
						<select id="WFSFormTypeV" onchange="WFSParamSetType(this.value, 'V');">
							<?foreach($arWorkflowParameterTypes as $k=>$v):?>
								<option value="<?=$k?>"><?=htmlspecialchars($v)?></option>
							<?endforeach;?>
						</select><br />
						<span id="WFSAdditionalTypeInfoV"></span>
					</td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAM_REQUIRED")?>:</td>
					<td><input type="checkbox" id="WFSFormReqV" value="Y"></td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAM_MULT")?>:</td>
					<td><input type="checkbox" id="WFSFormMultV" value="Y"></td>
				</tr>
				<tr id="WFSFormOptionsRowV" style="display: none;">
					<td>
					<?echo GetMessage("BIZPROC_WFS_PARAMLIST")?><br><br>
					<?echo GetMessage("BIZPROC_WFS_PARAMLIST_DESC")?>
					</td>
					<td><textarea id="WFSFormOptionsV" rows="5" cols="30"></textarea></td>
				</tr>
				<tr>
					<td><?=GetMessage("BIZPROC_WFS_PARAMDEF")?>:</td>
					<td id="tdWFSFormDefaultV">
						<input id="id_WFSFormDefaultV">
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type="button" value="OK" onclick="WFSParamSaveForm('V')"><input type="button" onclick="WFSParamEditForm(false, 'V')" value="<?echo GetMessage("BIZPROC_WFS_BUTTON_CANCEL")?>"></td>
				</tr>
			</table>
		</div>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
$permissions = $_POST['arWorkflowTemplate'][0]['Properties']['Permission'];
foreach($arAllowableOperations as $op_id=>$op_name):
	$parameterKeyExt = 'P'.$op_id;
?>
<tr>
	<td valign="top"><?=htmlspecialchars($op_name)?>:</td>
	<td><?
			$usersP = htmlspecialchars(CBPHelper::UsersArrayToString(
						$permissions[$op_id],
						$_POST['arWorkflowTemplate'],
						array(MODULE_ID, ENTITY, $_POST['document_type'])
					));
	?>
	<textarea name="<?= $parameterKeyExt ?>" id="id_<?= $parameterKeyExt ?>" rows="4" cols="50"><?= $usersP ?></textarea><input type="button" value="..." onclick="BPAShowSelector('id_<?= $parameterKeyExt ?>', 'user', 'all', {'arWorkflowParameters': WFSParams, 'arWorkflowVariables': WFSVars});" />
	</td>
</tr>
<?
endforeach;

$tabControl->EndTab();

$tabControl->Buttons(array("buttons"=>Array(
	Array("name"=>GetMessage("BIZPROC_WFS_BUTTON_SAVE"), "onclick"=>"WFSFSave();", "title"=>"", "id"=>"dpsavebutton"),
	Array("name"=>GetMessage("BIZPROC_WFS_BUTTON_CANCEL"), "onclick"=>"BX.WindowManager.Get().CloseDialog();", "title"=>"", "id"=>"dpcancelbutton")
	)));
$tabControl->End();

?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>