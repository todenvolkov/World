if (typeof oForum != "object")
	var oForum = {};
if (typeof oForum["selectors"] != "object")
	oForum["selectors"] = {};
	

function FSelectAll(oObj, name)
{
	if (typeof oObj != "object" || oObj == null || !name)
		return false;
	var sSelectorName = 'all_' + name.replace(/[^a-z0-9]/ig, "_");
	var items = oObj.form.getElementsByTagName('input');
	if (items && typeof items == "object" )
	{
		if (!items.length || (typeof(items.length) == 'undefined'))
		{
			items = [items];
		}
		window.oForum["selectors"][sSelectorName] = {"count" : 0, "current" : 0};
		for (var ii = 0; ii < items.length; ii++)
		{
			if (!(items[ii].type == "checkbox" && items[ii].name == name))
				continue;
			window.oForum["selectors"][sSelectorName]["count"]++;
			onClickCheckbox(items[ii], (oObj.checked ? "Y" : "N"));
		}
		if (oObj.checked)
			window.oForum["selectors"][sSelectorName]["current"] = window.oForum["selectors"][sSelectorName]["count"];
		else
			window.oForum["selectors"][sSelectorName]["current"] = 0;
	}
	return;
}

function Validate(form)
{
	var bError = true;
	var items = form.getElementsByTagName('input');
	if (items && typeof items == "object" )
	{
		
		if (!items.length || (typeof(items.length) == 'undefined'))
			items = [items];
		for (var ii = 0; ii < items.length; ii++)
		{
			if (!(items[ii].type == "checkbox" && items[ii].name == 'SID[]' && items[ii].checked))
				continue;
			bError = false;
			break;
		}
	}
	if (bError)
	{
		alert(oText['s_no_data']);
		return false;
	}
	if (form.ACTION.value == 'DEL')
		return confirm(oText['s_del']);
	return true;
}

function onClickCheckbox(oCheckBox, sSetValue)
{
	if (!oCheckBox)
		return false;
	var sSelectorName = 'all_' + oCheckBox.name.replace(/[^a-z0-9]/ig, "_");
	if (typeof(window.oForum["selectors"][sSelectorName]) != "object" || window.oForum["selectors"][sSelectorName] == null)
	{
		var bChecked = oCheckBox.checked;
		FSelectAll(oCheckBox.form[sSelectorName], oCheckBox.name);
		oCheckBox.checked = bChecked;
	}
	if (sSetValue == "N")
	{
		window.oForum["selectors"][sSelectorName]["current"]--;
		oCheckBox.checked = false;
	}
	else if (sSetValue == "Y")
	{
		window.oForum["selectors"][sSelectorName]["current"]++;
		oCheckBox.checked = true;
	}
	else
	{
		if (oCheckBox.checked)
		{
			window.oForum["selectors"][sSelectorName]["current"]++;
		}
		else
		{
			window.oForum["selectors"][sSelectorName]["current"]--;
		}
		
		if (oCheckBox.form[sSelectorName])
		{
			if (window.oForum["selectors"][sSelectorName]["current"] == window.oForum["selectors"][sSelectorName]["count"])
				oCheckBox.form[sSelectorName].checked = true;
			else
				oCheckBox.form[sSelectorName].checked = false;
		}
	}
}