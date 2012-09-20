var phpVars;
if(!phpVars)
{
	phpVars = {
		ADMIN_THEME_ID: '.default',
		LANGUAGE_ID: 'en',
		FORMAT_DATE: 'DD.MM.YYYY',
		FORMAT_DATETIME: 'DD.MM.YYYY HH:MI:SS',
		opt_context_ctrl: false,
		cookiePrefix: 'BITRIX_SM',
		titlePrefix: '',
		bitrix_sessid: '',
		messHideMenu: '',
		messShowMenu: '',
		messHideButtons: '',
		messShowButtons: '',
		messFilterInactive: '',
		messFilterActive: '',
		messFilterLess: '',
		messLoading: 'Loading...',
		messMenuLoading: '',
		messMenuLoadingTitle: '',
		messNoData: '',
		messExpandTabs: '',
		messCollapseTabs: '',
		messPanelFixOn: '',
		messPanelFixOff: '',
		messPanelCollapse: '',
		messPanelExpand: ''
	};
}

function JCSplitter(params)
{
	this.params = params;

	this.Highlight = function(on)
	{
		var control = document.getElementById(this.params.control);
		var div = document.getElementById(this.params.divShown);
		if(div.style.display!="none")
			control.className = this.params.classShown+(on? 'sel':'');
		else
			control.className = this.params.classHidden+(on? 'sel':'');
	}

	this.Toggle = function()
	{
		var visible = jsUtils.ToggleDiv(this.params.divShown);
		jsUtils.ToggleDiv(this.params.divHidden);
		this.Highlight(false);
		document.getElementById(this.params.control).title = (visible? this.params.messHide : this.params.messShow);
		return visible;
	}
}

/************************************************/

function JCAdminMenu(sOpenedSections)
{
	var _this = this;
	this.sMenuSelected='';
	this.x = 0;
	this.divToResize = null;
	this.divToBound = null;
	this.toggle = false;
	this.oSections = {};
	this.request = new JCHttpRequest();

	var aSect = sOpenedSections.split(',');
	for(var i in aSect)
		this.oSections[aSect[i]] = true;

	this.verSplitter = new JCSplitter({
		control:'vdividercell',
		divShown:'menudiv', divHidden:'hiddenmenucontainer',
		messHide:phpVars.messHideMenu, messShow:phpVars.messShowMenu,
		classShown:'vdividerknob vdividerknobleft', classHidden:'vdividerknob vdividerknobright'
	});
	this.horSplitter = new JCSplitter({
		control:'hdividercell',
		divShown:'buttonscontainer', divHidden:'smbuttonscontainer',
		messHide:phpVars.messHideButtons, messShow:phpVars.messShowButtons,
		classShown:'hdividerknob hdividerknobup', classHidden:'hdividerknob hdividerknobdown'
	});

	this.verSplitterToggle = function()
	{
		var visible = this.verSplitter.Toggle();
		jsUserOptions.SaveOption('admin_menu', 'pos', 'ver', (visible? 'on':'off'));
	}

	this.horSplitterToggle = function()
	{
		var visible = this.horSplitter.Toggle();
		jsUserOptions.SaveOption('admin_menu', 'pos', 'hor', (visible? 'on':'off'));
	}

	this.ToggleMenu = function(menu_id, menu_text)
	{
		var div = document.getElementById(menu_id);
		if(div.style.display!="none")
			return;

		/*menu div*/
		if(this.sMenuSelected != "")
			document.getElementById(this.sMenuSelected).style.display = 'none';
		div.style.display = "block";

		/*button*/
		document.getElementById('menutitle').innerHTML = menu_text;

		document.getElementById('btn_'+this.sMenuSelected).className = 'button';
		document.getElementById('smbtn_'+this.sMenuSelected).className = 'smbutton';
		document.getElementById('btn_'+menu_id).className = 'button buttonsel';
		document.getElementById('smbtn_'+menu_id).className = 'smbutton smbuttonsel';

		this.sMenuSelected = menu_id;
	}

	this.StartDrag = function()
	{
		if(this.toggle)
			return;
		if(document.getElementById('menudiv').style.display == 'none')
			return;

		this.divToBound = document.getElementById("menu_min_width");
		this.divToResize = document.getElementById('menucontainer');
		this.x = this.divToResize.offsetWidth;

		jsUtils.addEvent(document, "mousemove", _this.ResizeMenu);
		document.onmouseup = this.StopDrag;

		var b = document.body;
	    b.ondrag = jsUtils.False;
	    b.onselectstart = jsUtils.False;
	    b.style.MozUserSelect = 'none';
	    b.style.cursor = 'e-resize';
    }

	this.StopDrag = function(e)
	{
		jsUtils.removeEvent(document, "mousemove", _this.ResizeMenu);
		document.onmouseup = null;

		var b = document.body;
		b.ondrag = null;
		b.onselectstart = null;
		b.style.MozUserSelect = '';
	    b.style.cursor = '';

	    if(window.onresize)
	    	window.onresize();

		jsUserOptions.SaveOption('admin_menu', 'pos', 'width', parseInt(_this.divToResize.style.width));
	}

	this.ResizeMenu = function(e)
	{
		var x = e.clientX + document.body.scrollLeft;
		if(	_this.x == x)
			return;

		var div = _this.divToResize;
		var mnu = _this.divToBound;

		if(x < mnu.offsetWidth)
		{
			div.style.width = mnu.offsetWidth+'px';
			_this.x = x;
			return;
		}

		div.style.width = div.offsetWidth+(x - _this.x)+'px';
		_this.x = x;
	}

	this.ToggleSection = function(cell, div_id, level)
	{
		if(jsUtils.ToggleDiv(div_id))
		{
			if(level <= 2)
				this.oSections[div_id] = true;
			cell.className='sign signminus';
		}
		else
		{
			this.oSections[div_id] = false;
			cell.className='sign signplus';
		}

		if(level <= 2)
		{
			var sect='';
			for(var i in this.oSections)
				if(this.oSections[i] == true)
					sect += (sect != ''? ',':'')+i;
			jsUserOptions.SaveOption('admin_menu', 'pos', 'sections', sect);
		}
	}

	this.ToggleDynSection = function(cell, module_id, div_id, level)
	{
		function MenuText(text)
		{
			var s = '';
			for(var i=0; i<level; i++)
				s += '<td><div class="menuindent"></div></td>\n';
			return(
				'<div class="menuline">'+
				'<table cellspacing="0">'+
				'	<tr>'+s+
				'		<td class="menutext menutext-loading">'+text+'</td>'+
				'	</tr>'+
				'</table>'+
				'</div>');
		}

		var div = document.getElementById(div_id);
		if(div.innerHTML == '')
		{
			div.innerHTML = MenuText(phpVars.messMenuLoading);

			this.request.Action = function(result)
			{
				result = jsUtils.trim(result);
				div.innerHTML = (result != ''? result : MenuText(phpVars.messNoData));
			}
			this.request.Send('/bitrix/admin/get_menu.php?lang='+phpVars.LANGUAGE_ID+'&admin_mnu_module_id='+module_id+'&admin_mnu_menu_id='+encodeURIComponent(div_id));
		}
		this.ToggleSection(cell, div_id, level);
	}
}

/************************************************/

function JCAdminFilter(filter_id, aRows)
{
	var _this = this;
	this.filter_id = filter_id;
	this.aRows = aRows;
	this.oVisRows = null;

	this.ToggleFilterRow = function(row_id, on, bSave)
	{
		var row = document.getElementById(row_id);
		var delimiter = document.getElementById(row_id+'_delim');
		if(!row)
			return;

		var short_id = row_id.substr((this.filter_id+'_row_').length);

		if(on != true && on != false)
			on = (row.style.display == 'none');

		//filter popup menu
		var filterMenu = window[this.filter_id+"_menu"];
		if(on == true)
		{
			try{
				row.style.display = 'table-row';
				delimiter.style.display = 'table-row';
			}
			catch(e){
				row.style.display = 'block';
				delimiter.style.display = 'block';
			}
			if(filterMenu)
				filterMenu.SetItemIcon('gutter_'+row_id, "checked");
			this.oVisRows[short_id] = true;
		}
		else
		{
			row.style.display = 'none';
			delimiter.style.display = 'none';
			if(filterMenu)
				filterMenu.SetItemIcon('gutter_'+row_id, "");
			this.oVisRows[short_id] = false;
		}

		if(bSave != false)
			this.SaveRowsOption();
	}

	this.SaveRowsOption = function()
	{
		var sRows = '';
		for(var key in this.oVisRows)
			if(this.oVisRows[key] == true)
				sRows += (sRows != ''? ',':'')+key;
		jsUserOptions.SaveOption('filter', this.filter_id, 'rows', sRows);
	}

	this.ToggleAllFilterRows = function(on)
	{
		var tbl = document.getElementById(this.filter_id);
		if(!tbl)
			return;

		var n = tbl.rows.length;
		for(var i=0; i<n; i++)
		{
			var row = tbl.rows[i];
			if(row.id && row.cells[0].className != 'delimiter')
				this.ToggleFilterRow(row.id, on, false);
		}
		this.SaveRowsOption();
	}

	this.InitFilter = function(oVisRows)
	{
		this.oVisRows = oVisRows;

		var i;
		var tbl = document.getElementById(this.filter_id);
		if(!tbl)
			return;

		//filter popup menu
		var filterMenu = window[this.filter_id+"_menu"];

		var n=tbl.rows.length;
		for(i=0; i<n; i++)
		{
			var row = tbl.rows[i];
			var td = row.insertCell(-1);
			td.className = 'filterless';
			if(i>0)
			{
				row.id = this.filter_id+'_row_'+this.aRows[i-1];
				if(this.oVisRows[this.aRows[i-1]] == true)
				{
					if(filterMenu)
						filterMenu.SetItemIcon('gutter_'+row.id, "checked");
				}
				else
					row.style.display = 'none';

				td.innerHTML = '<a href="javascript:void(0)" onclick="this.blur();'+this.filter_id+'.ToggleFilterRow(\''+row.id+'\');" hidefocus="true" title="'+phpVars.messFilterLess+'" class="context-button icon" id="filterless"></a>';
			}
		}

		for(i=0; i<n; i++)
		{
			var tr = tbl.insertRow(i*2+1);
			if(i > 0)
			{
				if(this.oVisRows[this.aRows[i-1]] != true)
					tr.style.display = 'none';
				tr.id = this.filter_id+'_row_'+this.aRows[i-1]+'_delim';
			}
			var td = tr.insertCell(-1);
			td.colSpan = 3;
			td.className = 'delimiter';
			td.innerHTML = '<div class="empty"></div>';
		}

		try{
			tbl.style.display = 'table';}
		catch(e){
			tbl.style.display = 'block';}

		this.DisplayVisibleRows();
		this.SetActive(this.CheckActive());
	}

	this.GetParameters = function()
	{
		var form = jsUtils.FindParentObject(document.getElementById(this.filter_id), "form");
		if(!form)
			return;

		var i, s = "";
		var n = form.elements.length;
		for(i=0; i<n; i++)
		{
			var el = form.elements[i];
			if(el.disabled)
				continue;
			var tr = jsUtils.FindParentObject(el, 'tr');
			if(tr && tr.style && tr.style.display == 'none')
				continue;

			var val = "";
			switch(el.type.toLowerCase())
			{
				case 'select-one':
				case 'text':
				case 'textarea':
				case 'hidden':
					val = el.value;
					break;
				case 'radio':
				case 'checkbox':
					if(el.checked)
						val = el.value;
					break;
				case 'select-multiple':
					var j;
					var l = el.options.length;
					for(j=0; j<l; j++)
						if(el.options[j].selected)
							s += '&' + el.name + '=' + encodeURIComponent(el.options[j].value);
					break;
				default:
					break;
			}
			if(val != "")
				s += '&' + el.name + '=' + encodeURIComponent(val);
		}
		return s;
	}

	this.ClearParameters = function()
	{
		var form = jsUtils.FindParentObject(document.getElementById(this.filter_id), "form");
		if(!form)
			return;

		var i;
		var n = form.elements.length;
		for(i=0; i<n; i++)
		{
			var el = form.elements[i];
			switch(el.type.toLowerCase())
			{
				case 'text':
				case 'textarea':
					el.value = '';
					break;
				case 'select-one':
					el.selectedIndex = 0;
					if(el.onchange)
						el.onchange();
					break;
				case 'select-multiple':
					var j;
					var l = el.options.length;
					for(j=0; j<l; j++)
						el.options[j].selected = false;
					break;
				default:
					break;
			}
		}
	}

	this.OnSet = function(table_id, url)
	{
		this.SetActive(this.CheckActive());
		window[table_id].GetAdminList(url+'set_filter=Y'+this.GetParameters());
	}

	this.OnClear = function(table_id, url)
	{
		this.ClearParameters();
		this.SetActive(false);
		window[table_id].GetAdminList(url+'del_filter=Y'+this.GetParameters());
	}

	this.SetActive = function(on)
	{
		var div = document.getElementById(this.filter_id+'_active_lamp');
		div.className = (on? 'active':'inactive');
		div.title = (on? phpVars.messFilterActive:phpVars.messFilterInactive);
	}

	this.CheckActive = function()
	{
		var form = jsUtils.FindParentObject(document.getElementById(this.filter_id), "form");
		if(!form)
			return;

		var i;
		var n = form.elements.length;
		for(i=0; i<n; i++)
		{
			var el = form.elements[i];
			if(el.disabled)
				continue;
			var tr = jsUtils.FindParentObject(el, 'tr');
			if(tr && tr.style && tr.style.display == 'none')
				continue;

			switch(el.type.toLowerCase())
			{
				case 'select-one':
					if(el.options[0].value.length != 0 && (el.options[0].value.toUpperCase() != 'NOT_REF' || el.value.toUpperCase() == 'NOT_REF'))
						break;
				case 'text':
				case 'textarea':
					if(el.value.length > 0)
						return true;
					break;
				case 'checkbox':
					if(el.checked)
						return true;
					break;
				case 'select-multiple':
					var j;
					var l = el.options.length;
					for(j=0; j<l; j++)
						if(el.options[j].selected && el.options[j].value != '')
							return true;
					break;
				default:
					break;
			}
		}
		return false;
	}

	this.DisplayVisibleRows = function()
	{
		var form = jsUtils.FindParentObject(document.getElementById(this.filter_id), "form");
		if(!form)
			return;

		var i;
		var n = form.elements.length;
		for(i=0; i<n; i++)
		{
			var el = form.elements[i];
			if(el.disabled)
				continue;

			var bVisible = false;
			switch(el.type.toLowerCase())
			{
				case 'select-one':
					if(el.value.length>0 && (el.options[0].value.length == 0 || (el.options[0].value != el.value)))
						bVisible = true;
					break;
				case 'text':
				case 'textarea':
					if(el.value.length>0)
						bVisible = true;
					break;
				case 'checkbox':
					if(el.checked)
						bVisible = true;
					break;
				case 'select-multiple':
					var j;
					var l = el.options.length;
					for(j=0; j<l; j++)
						if(el.options[j].selected && el.options[j].value != '')
						{
							bVisible = true;
							break;
						}
					break;
				default:
					break;
			}
			if(bVisible)
			{
				var tr = jsUtils.FindParentObject(el, 'tr');
				if(tr.id)
					this.ToggleFilterRow(tr.id, true);
			}
		}
	}
}

/************************************************/

function JCAdminList(table_id)
{
	var _this = this;
	this.table_id = table_id;

	this.InitTable = function()
	{
		var tbl = document.getElementById(this.table_id);
		if(!tbl || tbl.rows.length<1 || tbl.rows[0].cells.length<1)
			return;

		var i;
		var nCols = tbl.rows[0].cells.length;
		var sortedIndex = -1;

		/*head row mousover action*/
		for(i=0; i<nCols; i++)
		{
			var j;
			var cell_sort = tbl.rows[1].cells[i];
			var sort_table = jsUtils.FindChildObject(cell_sort, "table", "sorting");

			for(j=0; j<2; j++)
			{
				var cell = tbl.rows[j].cells[i];

				cell.onmouseover = function(){_this.HighlightGutter(this, true)};
				cell.onmouseout = function(){_this.HighlightGutter(this, false)};

				/*expand sorting table behaviour on parent cell*/
				if(sort_table)
				{
					cell.onclick = sort_table.onclick;
					cell.title = sort_table.title;
					cell.style.cursor = "pointer";

					if(j == 0)
					{
						var cl = sort_table.rows[0].cells[1].className.toLowerCase();
						if(cl == "sign up" || cl == "sign down")
						{
							cell.className += ' sorted';
							sortedIndex = i;
						}
					}
				}
			}
			if(sort_table)
				sort_table.onclick = null;
		}

		var n = tbl.rows.length;
		for(i=0; i<n; i++)
		{
			var row = tbl.rows[i];

			/*first and last columns style classes*/
			row.cells[0].className += ' left';
	 		row.cells[row.cells.length-1].className += ' right';

	 		if(row.className && row.className == 'footer')
	 			continue;

			/*sorted column*/
			if(sortedIndex != -1 && sortedIndex < row.cells.length)
				row.cells[sortedIndex].className += ' sorted';

			if(i>=2)
			{
				/*first column checkbox action*/
				var checkbox = row.cells[0].childNodes[0];
				if(checkbox && checkbox.tagName && checkbox.tagName.toUpperCase() == "INPUT" && checkbox.type.toUpperCase() == "CHECKBOX")
				{
					checkbox.onclick = function(){_this.SelectRow(this); _this.EnableActions()};
					jsUtils.addEvent(row, "click", _this.OnClickRow);
				}

				/*rows mousover action*/
				row.onmouseover = function(){_this.HighlightRow(this, true)};
				row.onmouseout = function(){_this.HighlightRow(this, false)};

				if(i%2 == 0)
					row.className += ' odd';
				else
					row.className += ' even';

				if(row.oncontextmenu)
				{
					jsUtils.addEvent(row, "contextmenu",
						function(e)
						{
							if(!e) e = window.event;
							if(!phpVars.opt_context_ctrl && e.ctrlKey || phpVars.opt_context_ctrl && !e.ctrlKey)
								return;

							var targetElement;
							if(e.target) targetElement = e.target;
							else if(e.srcElement) targetElement = e.srcElement;

							while(targetElement && !targetElement.oncontextmenu)
								targetElement = jsUtils.FindParentObject(targetElement, "tr");

							var x = e.clientX + document.body.scrollLeft;
							var y = e.clientY + document.body.scrollTop;
							var pos = {};
							pos['left'] = pos['right'] = x;
							pos['top'] = pos['bottom'] = y;

							var menu = window[_this.table_id+"_menu"];
							menu.PopupHide();
							menu.SetItems(targetElement.oncontextmenu());
							menu.BuildItems();
							menu.PopupShow(pos);

							e.returnValue = false;
							if(e.preventDefault) e.preventDefault();
						}
					);
				}
			}
		}

		if(tbl.rows.length > 2)
		{
			tbl.rows[2].className += ' top';
			tbl.rows[tbl.rows.length-1].className += ' bottom';
		}
	}

	this.Destroy = function(bLast)
	{
		var tbl = document.getElementById(this.table_id);
		if(!tbl || tbl.rows.length<1 || tbl.rows[0].cells.length<1)
			return;

		var i;
		var nCols = tbl.rows[0].cells.length;
		for(i=0; i<nCols; i++)
		{
			var j;
			for(j=0; j<2; j++)
			{
				var cell = tbl.rows[j].cells[i];
				cell.onmouseover = null;
				cell.onmouseout = null;
				cell.onclick = null;
			}
		}
		var n = tbl.rows.length;
		for(i=0; i<n; i++)
		{
			var row = tbl.rows[i];
			var checkbox = row.cells[0].childNodes[0];
			if(checkbox && checkbox.onclick)
				checkbox.onclick = null;
			row.onmouseover = null;
			row.onmouseout = null;
			jsUtils.removeAllEvents(row);
		}
		if(bLast == true)
			_this = null;
	}

	this.HighlightGutter = function(cell, on)
	{
		var table = cell.parentNode.parentNode.parentNode;
		var gutter = table.rows[0].cells[cell.cellIndex];
		if(on)
			gutter.className += ' over';
		else
			gutter.className = gutter.className.replace(/\s*over/i, '');
	}

	this.HighlightRow = function(row, on)
	{
		if(on)
			row.className += ' over';
		else
			row.className = row.className.replace(/\s*over/i, '');
	}

	this.SelectRow = function(checkbox)
	{
		var row = checkbox.parentNode.parentNode;
		var tbl = row.parentNode.parentNode;
		var span = document.getElementById(tbl.id+'_selected_span');
		var selCount = parseInt(span.innerHTML);

		if(checkbox.checked)
		{
			row.className += ' selected';
			selCount++;
		}
		else
		{
			row.className = row.className.replace(/\s*selected/ig, '');
			selCount--;
		}
		span.innerHTML = selCount;

		var checkAll = document.getElementById(tbl.id+'_check_all');
		if(selCount == tbl.rows.length-2)
			checkAll.checked = true;
		else
			checkAll.checked = false;
	}

	this.OnClickRow = function(e)
	{
		if(!e)
			var e = window.event;
		if(!e.ctrlKey)
			return;
		var obj = (e.target? e.target : (e.srcElement? e.srcElement : null));
		if(!obj)
			return;
		if(!obj.parentNode.cells)
			return;
		var checkbox = obj.parentNode.cells[0].childNodes[0];
		if(checkbox && checkbox.tagName && checkbox.tagName.toUpperCase() == "INPUT" && checkbox.type.toUpperCase() == "CHECKBOX" && !checkbox.disabled)
		{
			checkbox.checked = !checkbox.checked;
			_this.SelectRow(checkbox);
		}
		_this.EnableActions();
	}

	this.SelectAllRows = function(checkbox)
	{
		var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
		var bChecked = checkbox.checked;
		var i;
		var n = tbl.rows.length;
		for(i=2; i<n; i++)
		{
			var box = tbl.rows[i].cells[0].childNodes[0];
			if(box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
			{
				if(box.checked != bChecked && !box.disabled)
				{
					box.checked = bChecked;
					this.SelectRow(box);
				}
			}
		}
		this.EnableActions();
	}

	this.EnableActions = function()
	{
		var form = document.forms['form_'+this.table_id];
		if(!form) return;

		var bEnabled = this.IsActionEnabled();
		var bEnabledEdit = this.IsActionEnabled('edit');

		if(form.apply) form.apply.disabled = !bEnabled;
		var b = document.getElementById('action_edit_button');
		if(b) b.className = 'context-button icon action-edit-button'+(bEnabledEdit? '':'-dis');
		b = document.getElementById('action_delete_button');
		if(b) b.className = 'context-button icon action-delete-button'+(bEnabled? '':'-dis');
	}

	this.IsActionEnabled = function(action)
	{
		var form = document.forms['form_'+this.table_id];
		if(!form) return;

		var bChecked = false;
		var span = document.getElementById(this.table_id+'_selected_span');
		if(span && parseInt(span.innerHTML)>0)
			bChecked = true;

		if(action == 'edit')
			return !(form.action_target && form.action_target.checked) && bChecked;
		else
			return (form.action_target && form.action_target.checked) || bChecked;
	}

	this.SetActiveResult = function(callback, url)
	{
		CHttpRequest.Action = function(result)
		{
			CloseWaitWindow();
			_this.Destroy(false);
			document.getElementById(_this.table_id+"_result_div").innerHTML = result;
			_this.InitTable();
			jsAdminChain.AddItems(_this.table_id+"_navchain_div");
			if(callback)
				callback(url);
		}
	}

	this.GetAdminList = function(url, callback)
	{
		ShowWaitWindow();

		var re = new RegExp('&mode=list&table_id='+escape(_this.table_id), 'g');
		url = url.replace(re, '');

		var link = document.getElementById('navchain-link');
		if(link)
			link.href = url;

		if(url.indexOf('?')>=0)
			url += '&mode=list&table_id='+escape(_this.table_id);
		else
			url += '?&mode=list&table_id='+escape(_this.table_id);

		_this.SetActiveResult(callback, url);
		CHttpRequest.Send(url);
	}

	this.Sort = function(url, bCheckCtrl, args)
	{
		if(bCheckCtrl == true)
		{
			var e = null, bControl = false;
			if(args.length > 0)
				e = args[0];
			if(!e)
				e = window.event;
			if(e)
				bControl = e.ctrlKey;
			url += (bControl? 'desc':'asc');
		}
		this.GetAdminList(url);
	}

	this.PostAdminList = function(url)
	{
		if(url.indexOf('?')>=0)
			url += '&mode=frame&table_id='+escape(this.table_id);
		else
			url += '?mode=frame&table_id='+escape(this.table_id);

		var frm = document.getElementById('form_'+this.table_id);

		try{frm.action.act.parentNode.removeChild(frm.action);}catch(e){}

		frm.action = url;
		frm.onsubmit();
		frm.submit();
	}

	this.ShowSettings = function(url)
	{
		if(document.getElementById("settings_float_div"))
			return;

		CHttpRequest.Action = function(result)
		{
			CloseWaitWindow();

			if(result == '')
				return;

			var div = document.body.appendChild(document.createElement("DIV"));
			div.id = "settings_float_div";
			div.className = "settings-float-form";
			div.style.position = 'absolute';
			div.style.zIndex = 1000;
			div.innerHTML = result;

			var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
			var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);
			jsFloatDiv.Show(div, left, top);

			jsUtils.addEvent(document, "keypress", _this.SettingsOnKeyPress);
		}
		ShowWaitWindow();
		CHttpRequest.Send(url);
	}

	this.CloseSettings =  function()
	{
		jsUtils.removeEvent(document, "keypress", _this.SettingsOnKeyPress);
		var div = document.getElementById("settings_float_div");
		jsFloatDiv.Close(div);
		div.parentNode.removeChild(div);
	}

	this.SettingsOnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.CloseSettings();
	}

	this.SaveSettings =  function()
	{
		ShowWaitWindow();

		var sCols='', sBy='', sOrder='', sPageSize='';

		var oSelect = document.list_settings.selected_columns;
		var n = oSelect.length;
		for(var i=0; i<n; i++)
			sCols += (sCols != ''? ',':'')+oSelect[i].value;

		oSelect = document.list_settings.order_field;
		if(oSelect)
			sBy = oSelect[oSelect.selectedIndex].value;

		oSelect = document.list_settings.order_direction;
		if(oSelect)
			sOrder = oSelect[oSelect.selectedIndex].value;

		oSelect = document.list_settings.nav_page_size;
		sPageSize = oSelect[oSelect.selectedIndex].value;

		var bCommon = (document.list_settings.set_default && document.list_settings.set_default.checked);

		jsUserOptions.SaveOption('list', this.table_id, 'columns', sCols, bCommon);
		jsUserOptions.SaveOption('list', this.table_id, 'by', sBy, bCommon);
		jsUserOptions.SaveOption('list', this.table_id, 'order', sOrder, bCommon);
		jsUserOptions.SaveOption('list', this.table_id, 'page_size', sPageSize, bCommon);

		var url = window.location.href;
		jsUserOptions.SendData(function(){_this.GetAdminList(url, _this.CloseSettings);});
	}

	this.DeleteSettings = function(bCommon)
	{
		ShowWaitWindow();
		var url = window.location.href;
		jsUserOptions.DeleteOption('list', this.table_id, bCommon, function(){_this.GetAdminList(url, _this.CloseSettings);});
	}
}

/************************************************/

function TabControl(name, unique_name, aTabs)
{
	var _this = this;
	this.name = name;
	this.unique_name = unique_name;
	this.aTabs = aTabs;
	this.aTabsDisabled = {};
	this.bExpandTabs = false;

	this.SelectTab = function(tab_id)
	{
		var div = document.getElementById(tab_id);
		if(div.style.display != 'none')
			return;

		for (var i = 0, cnt = this.aTabs.length; i < cnt; i++)
		{
			var tab = document.getElementById(this.aTabs[i]["DIV"])
			if(tab.style.display != 'none')
			{
				this.ShowTab(this.aTabs[i]["DIV"], false);
				tab.style.display = 'none';
				break;
			}
		}

		this.ShowTab(tab_id, true);
		div.style.display = 'block';

		document.getElementById(this.name+'_active_tab').value = tab_id;

		for (var i = 0, cnt = this.aTabs.length; i < cnt; i++)
			if(this.aTabs[i]["DIV"] == tab_id)
			{
				this.aTabs[i]["_ACTIVE"] = true;
				if(this.aTabs[i]["ONSELECT"])
					eval(this.aTabs[i]["ONSELECT"]);
				break;
			}
	}

	this.ShowTab = function(tab_id, on)
	{
		var sel = (on? '-selected':'');
		try{
		document.getElementById('tab_cont_'+tab_id).className = 'tab-container'+sel;
		document.getElementById('tab_left_'+tab_id).className = 'tab-left'+sel;
		document.getElementById('tab_'+tab_id).className = 'tab'+sel;
		if(tab_id != this.aTabs[this.aTabs.length-1]["DIV"])
			document.getElementById('tab_right_'+tab_id).className = 'tab-right'+sel;
		else
			document.getElementById('tab_right_'+tab_id).className = 'tab-right-last'+sel;
		}catch(e){}
	}

	this.HoverTab = function(tab_id, on)
	{
		var tab = document.getElementById('tab_'+tab_id);
		if(tab.className == 'tab-selected')
			return;

		document.getElementById('tab_left_'+tab_id).className = (on? 'tab-left-hover':'tab-left');
		tab.className = (on? 'tab-hover':'tab');
		var tab_right = document.getElementById('tab_right_'+tab_id);
		if(tab_id != this.aTabs[this.aTabs.length-1]["DIV"])
			tab_right.className = (on? 'tab-right-hover':'tab-right');
		else
			tab_right.className = (on? 'tab-right-last-hover':'tab-right-last');
	}

	this.InitEditTables = function()
	{
		for(var tab = 0, cnt = this.aTabs.length; tab < cnt; tab++)
		{
			var div = document.getElementById(this.aTabs[tab]["DIV"]);
			var tbl = jsUtils.FindChildObject(div.firstChild, 'table', 'edit-table');
			if(!tbl)
			{
				var tbl = jsUtils.FindChildObject(div, 'table', 'edit-table');
				if (!tbl)
					continue;
			}

			var n = tbl.rows.length;
			for(var i=0; i<n; i++)
				if(tbl.rows[i].cells.length > 1)
					tbl.rows[i].cells[0].className = 'field-name';
		}
	}

	this.DisableTab = function(tab_id)
	{
		this.aTabsDisabled[tab_id] = true;
		this.ShowDisabledTab(tab_id, true);
		if(this.bExpandTabs)
		{
			var div = document.getElementById(tab_id);
			div.style.display = 'none';
		}
	}

	this.EnableTab = function(tab_id)
	{
		this.aTabsDisabled[tab_id] = false;
		this.ShowDisabledTab(tab_id, this.bExpandTabs);
		if(this.bExpandTabs)
		{
			var div = document.getElementById(tab_id);
			div.style.display = 'block';
		}
	}

	this.ShowDisabledTab = function(tab_id, disabled)
	{
		var tab = document.getElementById('tab_cont_'+tab_id);
		if(disabled)
		{
			tab.className = 'tab-container-disabled';
			tab.onclick = null;
			tab.onmouseover = null;
			tab.onmouseout = null;
		}
		else
		{
			tab.className = 'tab-container';
			tab.onclick = function(){_this.SelectTab(tab_id);};
			tab.onmouseover = function(){_this.HoverTab(tab_id, true);};
			tab.onmouseout = function(){_this.HoverTab(tab_id, false);};
		}
	}

	this.Destroy = function()
	{
		//for(var i in this.aTabs)
		for(var i = 0, cnt = this.aTabs.length; i < cnt; i++)
		{
			var tab = document.getElementById('tab_cont_'+this.aTabs[i]["DIV"]);
			if (!tab)
				continue;
			tab.onclick = null;
			tab.onmouseover = null;
			tab.onmouseout = null;
		}
		_this = null;
	}

	this.ToggleTabs = function()
	{
		this.bExpandTabs = !this.bExpandTabs;

		var a = document.getElementById(this.name+'_expand_link');
		a.title = (this.bExpandTabs? phpVars.messCollapseTabs : phpVars.messExpandTabs);
		a.className = (this.bExpandTabs? a.className.replace(/\s*down/ig, ' up') : a.className.replace(/\s*up/ig, ' down'));

		for(var i in this.aTabs)
		{
			var tab_id = this.aTabs[i]["DIV"];
			this.ShowTab(tab_id, false);
			this.ShowDisabledTab(tab_id, (this.bExpandTabs || this.aTabsDisabled[tab_id]));
			var div = document.getElementById(tab_id);
			div.style.display = (this.bExpandTabs && !this.aTabsDisabled[tab_id]? 'block':'none');
			if(i > 0)
			{
				var tbl = jsUtils.FindChildObject(div.firstChild, 'table', 'edit-tab-title');
				if(this.bExpandTabs)
				{
					try{
						tbl.rows[0].style.display = 'table-row';
					}
					catch(e){
						tbl.rows[0].style.display = 'block';
					}
				}
				else
					tbl.rows[0].style.display = 'none';
			}
		}
		if(!this.bExpandTabs)
		{
			this.ShowTab(this.aTabs[0]["DIV"], true);
			var div = document.getElementById(this.aTabs[0]["DIV"]);
			div.style.display = 'block';
		}
		jsUserOptions.SaveOption('edit', this.unique_name, 'expand', (this.bExpandTabs? 'on': 'off'));

		jsUtils.onCustomEvent('OnToggleTabs');
	}

	this.ShowWarnings = function(form_name, warnings)
	{
		var form = document.forms[form_name];
		if(!form)
			return;
		for(var i in warnings)
		{
			var e = form.elements[warnings[i]['name']];
			if(!e)
				continue;

			var type = (e.type? e.type.toLowerCase():'');
			var bBefore = false;
			if(e.length > 1 && type != 'select-one' && type != 'select-multiple')
			{
				e = e[0];
				bBefore = true;
			}
			if(type == 'textarea' || type == 'select-multiple')
				bBefore = true;

			var td = e.parentNode;
			var img;
			if(bBefore)
			{
				img = td.insertBefore(new Image(), e);
				td.insertBefore(document.createElement("BR"), e);
			}
			else
			{
				img = td.insertBefore(new Image(), e.nextSibling);
				img.hspace = 2;
				img.vspace = 2;
				img.style.verticalAlign = 'bottom';
			}
			img.src = '/bitrix/themes/'+phpVars.ADMIN_THEME_ID+'/images/icon_warn.gif';
			img.title = warnings[i]['title'];
		}
	}

	this.ShowSettings = function(url)
	{
		if(document.getElementById("settings_float_div"))
			return;

		CHttpRequest.Action = function(result)
		{
			CloseWaitWindow();

			if(result == '')
				return;

			var div = document.body.appendChild(document.createElement("DIV"));
			div.id = "settings_float_div";
			div.className = "settings-float-form";
			div.style.position = 'absolute';
			div.style.zIndex = 1000;
			div.innerHTML = result;

			var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
			var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);
			jsFloatDiv.Show(div, left, top);

			jsUtils.addEvent(document, "keypress", _this.SettingsOnKeyPress);
		}
		ShowWaitWindow();
		CHttpRequest.Send(url);
	}

	this.CloseSettings =  function()
	{
		jsUtils.removeEvent(document, "keypress", _this.SettingsOnKeyPress);
		var div = document.getElementById("settings_float_div");
		jsFloatDiv.Close(div);
		div.parentNode.removeChild(div);
	}

	this.SettingsOnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.CloseSettings();
	}

	this.SaveSettings =  function()
	{
		ShowWaitWindow();

		var sTabs='', s='';

		var oFieldsSelect;
		var oSelect = document.getElementById('selected_tabs');
		if(oSelect)
		{
			var k = oSelect.length;
			for(var i=0; i<k; i++)
			{
				s = oSelect[i].value + '--#--' + oSelect[i].text;
				oFieldsSelect = document.getElementById('selected_fields[' + oSelect[i].value + ']');
				if(oFieldsSelect)
				{
					var n = oFieldsSelect.length;
					for(var j=0; j<n; j++)
					{
						s += '--,--' + oFieldsSelect[j].value + '--#--' + jsUtils.trim(oFieldsSelect[j].text);
					}
				}
				sTabs += s + '--;--';
			}
		}

		var bCommon = (document.form_settings.set_default && document.form_settings.set_default.checked);

		var request = new JCHttpRequest;
		request.Action = function () {BX.reload()};

		var sParam = '';
		sParam += '&p[0][c]=form';
		sParam += '&p[0][n]='+encodeURIComponent(this.name);
		if(bCommon)
			sParam += '&p[0][d]=Y';
		sParam += '&p[0][v][tabs]=' + encodeURIComponent(sTabs);

		var options_url = '/bitrix/admin/user_options.php?lang='+phpVars.LANGUAGE_ID+'&sessid='+phpVars.bitrix_sessid;
		options_url += '&action=delete&c=form&n='+this.name+'_disabled';

		request.Post(options_url, sParam);
	}

	this.DeleteSettings = function(bCommon)
	{
		ShowWaitWindow();
		jsUserOptions.DeleteOption('form', this.name, bCommon, function () {BX.reload()});
	}

	this.DisableSettings = function()
	{
		var request = new JCHttpRequest;
		request.Action = function () {BX.reload()};
		var sParam = '';
		sParam += '&p[0][c]=form';
		sParam += '&p[0][n]='+encodeURIComponent(this.name+'_disabled');
		sParam += '&p[0][v][disabled]=Y';
		request.Send('/bitrix/admin/user_options.php?lang=' + phpVars.LANGUAGE_ID + sParam + '&sessid='+phpVars.bitrix_sessid);
	}

	this.EnableSettings = function()
	{
		var request = new JCHttpRequest;
		request.Action = function () {BX.reload()};
		var sParam = '';
		sParam += '&c=form';
		sParam += '&n='+encodeURIComponent(this.name)+'_disabled';
		sParam += '&action=delete';
		request.Send('/bitrix/admin/user_options.php?lang=' + phpVars.LANGUAGE_ID + sParam + '&sessid='+phpVars.bitrix_sessid);
	}
}

/************************************************/

function ViewTabControl(aTabs)
{
	var _this = this;
	this.aTabs = aTabs;

	this.SelectTab = function(tab_id)
	{
		var div = document.getElementById(tab_id);
		if(div.style.display != 'none')
			return;

		for(var i in this.aTabs)
		{
			var tab_div = document.getElementById(this.aTabs[i]["DIV"]);
			if(tab_div.style.display != 'none')
			{
				var tab = document.getElementById('view_tab_'+this.aTabs[i]["DIV"]);
				tab.innerHTML = this.aTabs[i]["HTML"];
				tab.className = 'view-tab';
				this.ToggleDelimiter(tab, true);
				tab_div.style.display = 'none';
				break;
			}
		}

		var active_tab = document.getElementById('view_tab_'+tab_id);
		active_tab.className = 'view-tab view-tab-active';
		this.ToggleDelimiter(active_tab, false);
		div.style.display = 'block';

		this.RebuildTabs();

		for(var i in this.aTabs)
		{
			if(this.aTabs[i]["DIV"] == tab_id)
			{
				this.ReplaceAnchor(this.aTabs[i]);
				if(this.aTabs[i]["ONSELECT"])
					eval(this.aTabs[i]["ONSELECT"]);
				break;
			}
		}
	}

	this.ToggleDelimiter = function(tab, on)
	{
		var d;
		if((d = jsUtils.FindNextSibling(tab, 'div')) && d.className.indexOf('view-tab-delimiter') != -1)
			d.className = 'view-tab-delimiter'+(on? '':' view-tab-hide-delimiter');
		if((d = jsUtils.FindPreviousSibling(tab, 'div')) && d.className.indexOf('view-tab-delimiter') != -1)
			d.className = 'view-tab-delimiter'+(on? '':' view-tab-hide-delimiter');
	}

	this.DisableTab = function(tab_id)
	{
	}

	this.EnableTab = function(tab_id)
	{
	}

	this.ReplaceAnchor = function(tab)
	{
		var tab_div = document.getElementById('view_tab_'+tab["DIV"]);
		tab["HTML"] = tab_div.innerHTML;
		var a = jsUtils.FindChildObject(tab_div, "a");
		tab_div.innerHTML = a.innerHTML;
	}

	this.RebuildTabs = function()
	{
		var container = jsUtils.FindParentObject(document.getElementById('view_tab_'+_this.aTabs[0]["DIV"]), "div");
		var aPos = [0];
		var selectedIndex = -1;
		var prevTop = -1;
		var last;
		var n = container.childNodes.length;
		for(var i=0; i<n; i++)
		{
			var div = container.childNodes[i];
			if(!div.id)
				continue;

			if(prevTop > -1 && div.offsetTop > prevTop)
				aPos[aPos.length] = i;
			prevTop = div.offsetTop;

			if(selectedIndex == -1 && div.className.indexOf('view-tab-active') != -1)
				selectedIndex = aPos.length-1;
			last = div;
		}

		if(selectedIndex < aPos.length && selectedIndex > -1)
		{
			var aDiv = new Array();
			var div = container.childNodes[aPos[selectedIndex]];
			for(var i = aPos[selectedIndex]; i<aPos[selectedIndex+1]; i++)
			{
				aDiv[aDiv.length] = div;
				div = div.nextSibling;
			}
			if(aDiv.length > 0)
			{
				for(var i in aDiv)
					container.removeChild(aDiv[i]);

				while(last.nextSibling)
				{
					last = last.nextSibling;
					if(last.tagName && last.tagName.toUpperCase() == 'BR' && last.className && last.className == 'tab-break')
						break;
				}

				var br = document.createElement("BR");
				br.style.clear='both';
				container.insertBefore(br, last);

				for(var i in aDiv)
				{
					if(aDiv[i].tagName && aDiv[i].tagName.toUpperCase() == 'BR')
						continue;
					container.insertBefore(aDiv[i], last);
				}
			}
		}
	}

	this.Init = function()
	{
		if(this.aTabs.length == 0)
			return;
		for(var i in this.aTabs)
		{
			var div = document.getElementById(this.aTabs[i]["DIV"]);
			if(div.style.display != 'none')
			{
				this.ReplaceAnchor(this.aTabs[i]);
				this.ToggleDelimiter(document.getElementById('view_tab_'+this.aTabs[i]["DIV"]), false);
				break;
			}
		}
		setTimeout(this.RebuildTabs, 10);
		window.onresize = this.RebuildTabs;
	}

	this.Init();
}

/************************************************/

var jsAdminChain =
{
	_chain: '',

	AddItems: function(divId)
	{
		var main_chain = document.getElementById("main_navchain");
		if(!main_chain)
			return;

		if(this._chain == '')
			this._chain = main_chain.innerHTML;
		else
			main_chain.innerHTML = this._chain;

		var div = document.getElementById(divId);
		if(!div)
			return;

		main_chain.innerHTML += '<img src="/bitrix/themes/'+phpVars.ADMIN_THEME_ID+'/images/chain_arrow.gif" alt="" border="0" class="arrow">';
		main_chain.innerHTML += div.innerHTML;
	}
}

/************************************************/

function JCHttpRequest()
{
	this.Action = null; //function(result){}

	this._OnDataReady = function(result)
	{
		if(this.Action)
			this.Action(result);
	}

	this._CreateHttpObject = function()
	{
		var obj = null;
		if(window.XMLHttpRequest)
		{
			try {obj = new XMLHttpRequest();} catch(e){}
		}
        else if(window.ActiveXObject)
        {
            try {obj = new ActiveXObject("Microsoft.XMLHTTP");} catch(e){}
            if(!obj)
            	try {obj = new ActiveXObject("Msxml2.XMLHTTP");} catch (e){}
        }
        return obj;
	}

	this._SetHandler = function(httpRequest)
	{
		var _this = this;
		httpRequest.onreadystatechange = function()
		{
			if(httpRequest.readyState == 4)
			{
//				try
				{
					var s = httpRequest.responseText;
					var code = [];
					var start, end;
					while((start = s.indexOf('<script>')) != -1)
					{
						var end = s.indexOf('</script>', start);
						if(end == -1)
							break;

						code[code.length] = s.substr(start+8, end-start-8);
						s = s.substr(0, start) + s.substr(end+9);
					}
					_this._OnDataReady(s);

					for(var i = 0, cnt = code.length; i < cnt; i++)
						if(code[i] != '')
							jsUtils.EvalGlobal(code[i]);
				}
/*
				catch (e)
				{
					var w = window.open("about:blank");
					w.document.write(httpRequest.responseText);
					w.document.close();
				}
*/
			}
		}
	}

	this.Send = function(url)
	{
		var httpRequest = this._CreateHttpObject();
		if(httpRequest)
		{
			httpRequest.open("GET", url, true);
			this._SetHandler(httpRequest);
			return httpRequest.send("");
  		}
	}

	this.Post = function(url, data)
	{
		var httpRequest = this._CreateHttpObject();
		if(httpRequest)
		{
			httpRequest.open("POST", url, true);
			this._SetHandler(httpRequest);
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			return httpRequest.send(data);
  		}
	}
}
var CHttpRequest = new JCHttpRequest();

/************************************************/

function JCUserOptions()
{
	var _this = this;
	this.options = null;
	this.bSend = false;
	this.request = new JCHttpRequest();

	this.GetParams = function()
	{
		var sParam = '';
		var n = -1;
		var prevParam = '';
		for(var i in _this.options)
		{
			var aOpt = _this.options[i];
			if(prevParam != aOpt[0]+'.'+aOpt[1])
			{
				n++;
				sParam += '&p['+n+'][c]='+encodeURIComponent(aOpt[0]);
				sParam += '&p['+n+'][n]='+encodeURIComponent(aOpt[1]);
				if(aOpt[4] == true)
					sParam += '&p['+n+'][d]=Y';
				prevParam = aOpt[0]+'.'+aOpt[1];
			}
			sParam += '&p['+n+'][v]['+encodeURIComponent(aOpt[2])+']='+encodeURIComponent(aOpt[3]);
		}

		return sParam.substr(1);
	}

	this.SaveOption = function(sCategory, sName, sValName, sVal, bCommon)
	{
		if(!this.options)
			this.options = new Object();

		if(bCommon != true)
			bCommon = false;
		this.options[sCategory+'.'+sName+'.'+sValName] = [sCategory, sName, sValName, sVal, bCommon];

		var sParam = this.GetParams();
		if(sParam != '')
			document.cookie = phpVars.cookiePrefix+"_LAST_SETTINGS=" + sParam + "&sessid="+phpVars.bitrix_sessid+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path=/;";

		if(!this.bSend)
		{
			this.bSend = true;
			setTimeout(function(){_this.SendData(null)}, 5000);
		}
	}

	this.SendData = function(callback)
	{
		var sParam = _this.GetParams();
		_this.options = null;
		_this.bSend = false;
		if(sParam != '')
		{
			document.cookie = phpVars.cookiePrefix+"_LAST_SETTINGS=; path=/;";
			_this.request.Action = callback;
			_this.request.Send('/bitrix/admin/user_options.php?'+sParam+'&sessid='+phpVars.bitrix_sessid);
		}
	}

	this.DeleteOption = function(sCategory, sName, bCommon, callback)
	{
		_this.request.Action = callback;
		_this.request.Send('/bitrix/admin/user_options.php?action=delete&c='+sCategory+'&n='+sName+(bCommon == true? '&common=Y':'')+'&sessid='+phpVars.bitrix_sessid);
	}
}
var jsUserOptions = new JCUserOptions();

/************************************************/

function JCPanel()
{
	var _this = this;

	this.FixPanel = function()
	{
		var a = document.getElementById('admin_panel_fix_link');
		var panel = document.getElementById('bx_top_panel_container');
		var backDiv = document.getElementById('bx_top_panel_back');
		var bFixed = (panel.style.position == 'fixed' || panel.style.position == 'absolute');
		var bIE = jsUtils.IsIE();
		if(bIE)
		{
			try{panel.style.removeExpression("top");} catch(e) {bIE = false;}
		}
		if(bFixed)
		{
			a.title = phpVars.messPanelFixOn;
			a.className = 'fix-link fix-on';
			panel.style.position = '';
			backDiv.style.display = 'none';
			if(bIE)
			{
				panel.style.removeExpression("top");
				panel.style.removeExpression("left");
				panel.style.removeExpression("width");
				panel.style.width = '100%';

				var frame = document.getElementById("admin_panel_frame");
				if(frame)
					frame.style.visibility = 'hidden';
			}
		}
		else
		{
			this.ShowOn();
			if(bIE)
			{
				var frame = document.getElementById("admin_panel_frame");
				if(frame)
					frame.style.visibility = 'visible';
				else
					this.CreateFrame(panel);
			}
		}
		jsUserOptions.SaveOption('admin_panel', 'settings', 'fix', (bFixed? 'off':'on'));
	}

	this.ShowOn = function()
	{
		var a = document.getElementById('admin_panel_fix_link');
		var panel = document.getElementById('bx_top_panel_container');
		var backDiv = document.getElementById('bx_top_panel_back');
		var bIE = jsUtils.IsIE();
		if(bIE)
		{
			try{panel.style.setExpression("top", "0");} catch(e) {bIE = false;}
		}

		a.title = phpVars.messPanelFixOff;
		a.className = 'fix-link fix-off';
		panel.style.position = (bIE? 'absolute':'fixed');
		panel.style.left = '0px';
		panel.style.top = '0px';
		panel.style.zIndex = '1000';
		if(bIE)
		{
			if(document.body.currentStyle.backgroundImage == 'none')
			{
				document.body.style.backgroundImage = "url(/bitrix/images/1.gif)";
				document.body.style.backgroundAttachment = "fixed";
				document.body.style.backgroundRepeat = "no-repeat";
			}
			panel.style.setExpression("top", "eval((document.documentElement && document.documentElement.scrollTop) ? document.documentElement.scrollTop : document.body.scrollTop)");
			panel.style.setExpression("left", "eval((document.documentElement && document.documentElement.scrollLeft) ? document.documentElement.scrollLeft : document.body.scrollLeft)");
			panel.style.setExpression("width", "eval((document.documentElement && document.documentElement.clientWidth) ? document.documentElement.clientWidth : document.body.clientWidth)");
		}
		backDiv.style.height = panel.offsetHeight+'px';
		backDiv.style.display = 'block';
	}

	this.FixOn = function()
	{
		this.ShowOn();
		jsUtils.addEvent(window, "load", this.AdjustBackDiv);
	}

	this.AdjustBackDiv = function()
	{
		var panel = document.getElementById('bx_top_panel_container');
		var backDiv = document.getElementById('bx_top_panel_back');

		var bIE = jsUtils.IsIE();
		if(bIE)
		{
			try{backDiv.style.setExpression("height", "0");} catch(e) {bIE = false;}
		}

		backDiv.style.height = panel.offsetHeight+'px';

		if(bIE)
			_this.CreateFrame(panel);
	}

	this.CreateFrame = function(panel)
	{
		var frame = document.createElement("IFRAME");
		frame.src = "javascript:void(0)";
		frame.id = "admin_panel_frame";
		frame.style.position = 'absolute';
		frame.style.overflow = 'hidden';
		frame.style.zIndex = parseInt(panel.currentStyle.zIndex)-1;
		frame.style.height = panel.offsetHeight + "px";
		document.body.appendChild(frame);
		frame.style.setExpression("top", "eval(document.body.scrollTop)");
		frame.style.setExpression("left", "eval(document.body.scrollLeft)");
		frame.style.setExpression("width", "eval(document.body.clientWidth)");
		return frame;
	}

	this.IsFixed = function()
	{
		var panel = document.getElementById('bx_top_panel_container');
		return (panel && (panel.style.position == 'fixed' || panel.style.position == 'absolute'));
	}

	this.DisplayPanel = function(el)
	{
		var div = document.getElementById('bx_top_panel_splitter');
		if(div.style.display == 'none')
		{
			div.style.display = 'block';
			el.className = 'splitterknob';
			el.title = phpVars.messPanelCollapse;
			jsUserOptions.SaveOption('admin_panel', 'settings', 'collapsed', 'off');
		}
		else
		{
			div.style.display = 'none';
			el.className = 'splitterknob splitterknobdown';
			el.title = phpVars.messPanelExpand;
			jsUserOptions.SaveOption('admin_panel', 'settings', 'collapsed', 'on');
		}
		var panel = document.getElementById('bx_top_panel_container');
		var backDiv = document.getElementById('bx_top_panel_back');
		backDiv.style.height = panel.offsetHeight+'px';
		var frame = document.getElementById("admin_panel_frame");
		if(frame)
			frame.style.height = panel.offsetHeight + "px";
	}
}
var jsPanel = new JCPanel();

//***************************************************

function JCDebugWindow()
{
	var _this = this;
	this.div_id = 'BX_DEBUG_WINDOW';
	this.div_current = null;
	this.div_detail_current = null;

	this.Show = function(info_id)
	{
		var div = document.getElementById(this.div_id);
		if(div)
		{
			div.style.display = 'block';
			var info_div = document.getElementById(info_id);
			if(info_div)
			{
				if(this.div_current)
					this.div_current.style.display = 'none';

				info_div.style.display = 'block';
				this.div_current = info_div;

				this.ShowDetails(info_id+'_1');
			}

			//var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
			//var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);

			var windowSize = jsUtils.GetWindowSize();

			var left = parseInt(windowSize["scrollLeft"] + windowSize["innerWidth"]/2 - div.offsetWidth/2);
			var top = parseInt(windowSize["scrollTop"] + windowSize["innerHeight"]/2 - div.offsetHeight/2);

			jsFloatDiv.Show(div, left, top);
			jsUtils.addEvent(document, "keypress", this.OnKeyPress);
		}
	}

	this.Close = function()
	{
		jsUtils.removeEvent(document, "keypress", this.OnKeyPress);
		var div = document.getElementById(this.div_id);
		jsFloatDiv.Close(div);
		div.style.display = 'none';
	}

	this.OnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.Close();
	}

	this.ShowDetails = function(div_id)
	{
		var div = document.getElementById(div_id);
		if(div)
		{
			if(this.div_detail_current)
				this.div_detail_current.style.display = 'none';

			div.style.display = 'block';
			this.div_detail_current = div;
		}
	}
}
var jsDebugWindow = new JCDebugWindow();

//***************************************************

function ImgShw(ID, width, height, alt)
{
	var scroll = "no";
	var top=0, left=0;
	if(width > screen.width-10 || height > screen.height-28) scroll = "yes";
	if(height < screen.height-28) top = Math.floor((screen.height - height)/2-14);
	if(width < screen.width-10) left = Math.floor((screen.width - width)/2-5);
	width = Math.min(width, screen.width-10);
	height = Math.min(height, screen.height-28);
	var wnd = window.open("","","scrollbars="+scroll+",resizable=yes,width="+width+",height="+height+",left="+left+",top="+top);
	wnd.document.write(
		"<html><head>"+
		"<"+"script type=\"text/javascript\">"+
		"function KeyPress()"+
		"{"+
		"	if(window.event.keyCode == 27) "+
		"		window.close();"+
		"}"+
		"</"+"script>"+
		"<title></title></head>"+
		"<body topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" onKeyPress=\"KeyPress()\">"+
		"<img src=\""+ID+"\" border=\"0\" alt=\""+alt+"\" />"+
		"</body></html>"
	);
	wnd.document.close();
}


function CWizardWindow()
{
	this.iframe = null;
	var _this = this;
	this.messLoading = phpVars.messLoading;

	this.Open = function(wizardName, sessid)
	{
		if(document.getElementById("wizard_install_dialog"))
			this.Close();

		var div = document.body.appendChild(document.createElement("DIV"));
		div.id = "wizard_install_dialog";
		div.className = "settings-float-form";
		div.style.position = 'absolute';
		div.style.width = '600px';
		div.style.height = '400px';
		div.style.zIndex = 2100;
		div.style.overflow = 'hidden';

		div.innerHTML =
			'<div class="title" style="height:18px;">'+
			'<table cellspacing="0" width="100%" border="0">'+
			'	<tr>'+
			'		<td width="100%" class="title-text" height="19" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById(\'wizard_install_dialog\'));" id="wizard_dialog_title"></td></tr>'+
			'</table>'+
			'</div>'+
			'<div class="wizard" style="height:387px;"><iframe class="content" style="background-color: transparent;" allowtransparency="true" scrolling="no" id="wizard_iframe" width="100%" src="/bitrix/admin/wizard_install.php?lang='+phpVars.LANGUAGE_ID+'&wizardName='+wizardName+'&sessid='+sessid+'" height="100%" frameborder="0">'+
			''+
			'</iframe></div>';

		this.ShowWaitWindow();

		var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
		var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);

		jsFloatDiv.Show(div, left, top);

		this.iframe = document.getElementById("wizard_iframe");

		jsUtils.addEvent(this.iframe, "load", _this.OnFrameLoad);
	}

	this.OnFrameLoad = function()
	{
		_this.HideWaitWindow();

		var iframeWindow = _this.iframe.contentWindow;

		if (_this.iframe.contentDocument)
			var iframeDocument = _this.iframe.contentDocument;
		else
			var iframeDocument = _this.iframe.contentWindow.document;

		if(iframeWindow.focus)
			iframeWindow.focus();
		else
			iframeDocument.body.focus();

		var form = iframeDocument.forms["form_auth"];
		if (form)
			form.target = "_self";

	}

	this.Close = function()
	{
		var floatDiv = document.getElementById("wizard_install_dialog")
		jsFloatDiv.Close(floatDiv);
		floatDiv.parentNode.removeChild(floatDiv);
		floatDiv = null;
	}

	this.ShowWaitWindow = function()
	{
		try
		{
			var oDiv = document.createElement("DIV");
			oDiv.id = "__bx_wait_window";
			oDiv.className = "waitwindow";
			oDiv.style.position = "absolute";
			oDiv.style.left = "40%";
			oDiv.style.top = "40%";
			oDiv.style.zIndex = "3000";
			oDiv.innerHTML = this.messLoading;
			document.getElementById("wizard_install_dialog").appendChild(oDiv);
		}
		catch(e){}
	}

	this.HideWaitWindow = function()
	{
		try
		{
			var oDiv = document.getElementById("__bx_wait_window");
			oDiv.parentNode.removeChild(oDiv);
			oDiv = null;
		}catch(e){}
	}
}

var WizardWindow = new CWizardWindow();

//************************************************************

function JCStartMenu()
{
	var menuStart = null;
	var request = new JCHttpRequest();
	var _this = this;

	this.EvalMenu = function(result)
	{
		if(jsUtils.trim(result).length == 0)
			return;

		var menuItems;
		eval(result); // menuItems={'styles':[], 'items':[]}

		if(!menuItems)
			return false;

		//Applying styles
		var head = document.getElementsByTagName("HEAD");
		if(head && head[0])
		{
			var style = document.createElement("STYLE");
			head[0].appendChild(style);
			if(jsUtils.IsIE())
				document.styleSheets[document.styleSheets.length-1].cssText = menuItems['styles'];
			else
				style.appendChild(document.createTextNode(menuItems['styles']));
		}
		return menuItems;
	}

	this.ShowStartMenu = function(button, back_url)
	{
		var dPos = {'left':0, 'top':0, 'right':0, 'bottom':0};
		if(!menuStart || !menuStart.menuItems)
		{
			request.Action = function(result)
			{
				var menuItems = _this.EvalMenu(result);
				if(menuItems)
				{
					//show menu
					menuStart.PopupHide();
					menuStart.ShowMenu(button, menuItems['items'], jsPanel.IsFixed(), dPos);
				}
			}
			//create menu
			menuStart = new PopupMenu('panel_start_menu');
			menuStart.Create(1100);
			menuStart.ShowMenu(button, [{
				'TEXT':phpVars.messMenuLoading,
				'TITLE':phpVars.messMenuLoadingTitle,
				'ICONCLASS':'loading',
				'AUTOHIDE':false}], jsPanel.IsFixed(), dPos);
			request.Send('/bitrix/admin/get_start_menu.php?lang='+phpVars.LANGUAGE_ID+(back_url? '&back_url_pub='+encodeURIComponent(back_url):'')+'&sessid='+phpVars.bitrix_sessid);
		}
		else
		{
			menuStart.ShowMenu(button, null, jsPanel.IsFixed(), dPos);
		}
	}

	this.PreloadMenu = function(back_url)
	{
		if(!menuStart)
		{
			request.Action = function(result)
			{
				var menuItems = _this.EvalMenu(result);
				if(menuItems)
				{
					//show menu
					menuStart.SetItems(menuItems['items']);
					menuStart.BuildItems();
				}
			}
			//create menu
			menuStart = new PopupMenu('panel_start_menu');
			menuStart.Create(1100);
			request.Send('/bitrix/admin/get_start_menu.php?lang='+phpVars.LANGUAGE_ID+(back_url? '&back_url_pub='+encodeURIComponent(back_url):'')+'&sessid='+phpVars.bitrix_sessid);
		}
	}

	this.OpenDynMenu = function(menu, module_id, items_id, back_url)
	{
		request.Action = function(result)
		{
			if(jsUtils.trim(result).length == 0)
				return;

			var menuItems;
			eval(result); // menuItems={'items':[]}

			if(menu && menuItems)
			{
				var bVisible = menu.IsVisible();
				menu.PopupHide();
				menu.SetItems(menuItems['items']);
				menu.BuildItems();
				menu.parentMenu.ShowSubmenu(menu.parentItem, false, !bVisible);
			}
		}
		request.Send('/bitrix/admin/get_start_menu.php?mode=dynamic&lang='+phpVars.LANGUAGE_ID+'&admin_mnu_module_id='+encodeURIComponent(module_id)+'&admin_mnu_menu_id='+encodeURIComponent(items_id)+(back_url? '&back_url_pub='+encodeURIComponent(back_url):'')+'&sessid='+phpVars.bitrix_sessid);
	}

	this.OpenURL = function(item, arguments, url, back_url)
	{
		var itemInfo = menuStart.GetItemInfo(item);
		if(itemInfo)
		{
			request.Action = function(result){}
			request.Send('/bitrix/admin/get_start_menu.php?mode=save_recent&url='+encodeURIComponent(url)+'&text='+encodeURIComponent(itemInfo['TEXT'])+'&title='+encodeURIComponent(itemInfo['TITLE'])+'&icon='+itemInfo['ICON']+'&sessid='+phpVars.bitrix_sessid);
		}
		if(back_url)
			url += (url.indexOf('?')>=0? '&':'?')+'back_url_pub='+encodeURIComponent(back_url);
		jsUtils.Redirect(arguments, url);
	}
}
var jsStartMenu = new JCStartMenu();