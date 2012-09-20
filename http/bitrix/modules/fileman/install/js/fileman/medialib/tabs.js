function MedialibTabControl(uniq_id, aTabs, mtime)
{
	var _this = this;
	this.uniq_id = uniq_id;
	this.aTabs = aTabs;

	/* Applying styles */
	if(!window.fileman_css)
	{
		BX.loadCSS('/bitrix/js/fileman/medialib/tabs.css'+(top.jsUtils.IsOpera()? '':'?'+mtime));
		window.fileman_css = true;
	}

	this.SelectTab = function(tab_td)
	{
		var tabs_tr = tab_td.parentNode;
		var first_td = tabs_tr.cells[0];

		if(this.aTabs[0]["TD_ID"] == tab_td.id)
			first_td.className = 'imgtab-none-sel';
		else
			first_td.className = 'imgtab-none-some';

		for (var i = 0, cnt = this.aTabs.length; i < cnt; i++)
		{
			var tab = BX(this.aTabs[i]["TD_ID"]);
			if(this.aTabs[i]["TD_ID"] == tab_td.id)
			{
				tabs_tr.cells[1+i*2].className = 'imgtab-sel';
				if(i == (cnt-1))
				{
					tabs_tr.cells[2+i*2].className = 'imgtab-sel-none';
				}
				else
				{
					tabs_tr.cells[2+i*2].className = 'imgtab-sel-some';
				}
				var div = BX(this.aTabs[i]["DIV_ID"]);
				div.style.display='block';
				this.SetState(div, false);
			}
			else
			{
				tabs_tr.cells[1+i*2].className = 'imgtab-some';
				if(i == (cnt-1))
				{
					tabs_tr.cells[2+i*2].className = 'imgtab-some-none';
				}
				else
				{
					if(this.aTabs[i+1]["TD_ID"] == tab_td.id)
						tabs_tr.cells[2+i*2].className = 'imgtab-some-sel';
					else
						tabs_tr.cells[2+i*2].className = 'imgtab-some-some';
				}
				var div = BX(this.aTabs[i]["DIV_ID"]);
				div.style.display='none';
				this.SetState(div, true);
			}
		}
	}

	this.SetState = function(obj, flag)
	{
		if(!obj)
			return null;

		var n = obj.childNodes.length;
		for(var j=0; j<n; j++)
		{
			var child = obj.childNodes[j];
			if(child.type)
			{
				switch(child.type.toLowerCase())
				{
					case 'select-one':
					case 'file':
					case 'text':
					case 'textarea':
					case 'hidden':
					case 'radio':
					case 'checkbox':
					case 'select-multiple':
						child.disabled = flag;
						break;
					default:
						break;
				}
			}
			this.SetState(child, flag);
		}
	},

	this.Destroy = function()
	{
		for(var i = 0, cnt = this.aTabs.length; i < cnt; i++)
		{
			var tab = BX(this.aTabs[i]["TD_ID"]);
			if (!tab)
				continue;
			tab.onclick = null;
		}
		_this = null;
	}
}
