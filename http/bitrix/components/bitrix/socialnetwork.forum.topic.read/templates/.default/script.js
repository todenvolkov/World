function SelectPost(table)
{
	
	if (table == null)
		return;

	if(table.className.match(/forum-post-selected/))
		table.className = table.className.replace(/\s*forum-post-selected/i, '');
	else
		table.className += ' forum-post-selected';
}

function Validate(form)
{
	if (typeof(form) != "object" || form == null)
		return false;
	var oError = [];
	if (form.name.substr(0, 8) == 'MESSAGES')
	{
		var items = form.getElementsByTagName('input');
		if (items && typeof items == "object" )
		{
			if (!items.length || (typeof(items.length) == 'undefined'))
			{
				items = [items];
			}
			var bEmptyData = true;
			for (ii = 0; ii < items.length; ii++)
			{
				if (!(items[ii].type == "checkbox" && items[ii].name == 'message_id[]'))
					continue;
				if (items[ii].checked)
				{
					bEmptyData = false;
					break;
				}
			}
			if (bEmptyData)
				oError.push(oText['no_data']);
		}
	}
	if (form['ACTION'].value == '')
		oError.push(oText['no_action']);
	if (oError.length > 0)
	{
		alert(oError.join('\n'));
		return false;
	}
	if (form['ACTION'].value == 'DEL_TOPIC')
		return confirm(oText['cdt']);
	else if (form['ACTION'].value == 'DEL')
		return confirm(oText['cdms']);
	return true;
}