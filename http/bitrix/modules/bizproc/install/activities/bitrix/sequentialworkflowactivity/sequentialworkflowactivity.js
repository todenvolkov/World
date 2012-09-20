/////////////////////////////////////////////////////////////////////////////////////
// SequentialWorkflowActivity
/////////////////////////////////////////////////////////////////////////////////////
SequentialWorkflowActivity = function()
{
	var ob = new SequenceActivity();
	ob.Type = 'SequentialWorkflowActivity';
	ob.swfWorkspaceDiv = null;
	ob.swfToolBoxDiv = null;
	ob.Table = null;

	ob.DrawSequenceActivity = ob.Draw;
	ob.Draw = function (div)
	{
		ob.Table = div.appendChild(_crt(1, 3));

		ob.swfWorkspaceDiv = ob.Table.rows[0].cells[0].appendChild(document.createElement('DIV'));
		ob.swfWorkspaceDiv.className = 'swfworkspace';
		if(parseInt(div.clientHeight)>50)
			ob.swfWorkspaceDiv.style.height = parseInt(div.clientHeight) + 'px';

		ob.ShowActivities(ob.Table.rows[0].cells[2]);

		ob._table = ob.swfWorkspaceDiv.appendChild(_crt(3, 1));
		ob.Table.rows[0].cells[0].width = '82%';
		ob.Table.rows[0].cells[1].width = '3px';
		ob.Table.rows[0].cells[1].style.borderLeft = '1px #d4d4d4 dotted';
		ob.Table.rows[0].cells[1].innerHTML = '<img src="/bitrix/images/1.gif" width="3">';
		ob.Table.rows[0].cells[2].width = '18%';

		ob.Table.rows[0].cells[0].vAlign = 'top';
		ob.Table.rows[0].cells[2].vAlign = 'top';
		ob.Table.rows[0].cells[2].align = 'left';


		var begin = ob._table.rows[0].cells[0].appendChild(document.createElement('DIV'));
		begin.style.margin = '0px auto';
		begin.style.textAlign = 'center';
		begin.style.width = '120px';
		begin.innerHTML = '<div style="background: url(/bitrix/images/bizproc/beg_bg.gif);"><div style="background: url(/bitrix/images/bizproc/beg_r.gif) right top no-repeat;"><div style="background: url(/bitrix/images/bizproc/beg_l.gif) left top no-repeat; height: 23px;"><div style="padding-top: 3px; font-size: 12px; color:#194d0b;">'+BPMESS['SEQWF_BEG']+'</div></div></div></div>';

		ob.DrawActivities();

		var end = ob._table.rows[2].cells[0].appendChild(document.createElement('DIV'));
		end.style.margin = '0px auto';
		end.style.textAlign = 'center';
		end.style.width = '120px';
		end.innerHTML = '<div style="background: url(/bitrix/images/bizproc/beg_bg.gif);"><div  style="background: url(/bitrix/images/bizproc/beg_r.gif) right top no-repeat;"><div style="background: url(/bitrix/images/bizproc/beg_l.gif) left top no-repeat; height: 23px;"><div style="padding-top: 3px; font-size: 12px; color:#194d0b;">'+BPMESS['SEQWF_END']+'</div></div></div></div>';
	}

	ob.DrawActivities = function ()
	{
		while(ob._table.rows[1].cells[0].childNodes.length>0)
			ob._table.rows[1].cells[0].removeChild(ob._table.rows[1].cells[0].childNodes[0]);

		ob.DrawSequenceActivity(ob._table.rows[1].cells[0]);
	}

	ob.ShowActivities = function (div)
	{
		ob.swfToolboxDiv = div.appendChild(document.createElement('DIV'));
		if(parseInt(div.clientHeight)>50)
			ob.swfToolboxDiv.style.height = parseInt(div.clientHeight) + 'px';
		ob.swfToolboxDiv.style.overflowX = 'hidden';
		ob.swfToolboxDiv.className = 'swftoolbox'

		var groupId, divGroup;
		for (groupId in arAllActGroups)
		{
			var divGroup = ob.swfToolboxDiv.appendChild(document.createElement('DIV'));
			divGroup.className = 'swftoolboxgroupclosed';
			divGroup.onclick = function (e) {this.className = (this.className=='swftoolboxgroupclosed'?'swftoolboxgroupopened':'swftoolboxgroupclosed');};

			var divGroupHeader = divGroup.appendChild(document.createElement('DIV'));

			divGroupHeader.className = 'swftoolboxgroupheader';
			divGroupHeader.innerHTML = '<div class="t"><div class="tr"><div class="tl"><div class="imarr"></div><div class="swftoolboxgrheadtext" title="'+HTMLEncode(arAllActGroups[groupId])+'">'+HTMLEncode(arAllActGroups[groupId])+'</div></div></div></div>';

			var divGroupList = divGroup.appendChild(document.createElement('DIV'));
			divGroupList.className = 'swftoolboxgrouplist';
			var dCont, bCat, cat;
			for(var act_i in arAllActivities)
			{
				if(!arAllActivities[act_i]["CATEGORY"] || arAllActivities[act_i]["CATEGORY"]["ID"]!=groupId)
					continue;

				if(act_i == 'setstateactivity' && rootActivity.Type == ob.Type)
					continue;

				dCont = divGroupList.appendChild(document.createElement('DIV'));

				var t = dCont.appendChild(_crt(1, 2));

				t.rows[0].style.height = '30px';
				t.rows[0].cells[0].style.width = '30px';
				if(arAllActivities[act_i]['ICON'])
					t.rows[0].cells[0].style.background = 'url('+arAllActivities[act_i]['ICON']+') 3px 3px no-repeat';
				else
					t.rows[0].cells[0].style.background = 'url(/bitrix/images/bizproc/act_icon.gif) 3px 3px no-repeat';

				//d.style.borderBottom = "1px #EBEBEB solid"
				t.rows[0].cells[0].style.cursor = 'pointer';
				t.rows[0].cells[1].style.cursor = 'pointer';

				t.rows[0].cells[1].style.fontSize = '11px';

				t.rows[0].cells[1].innerHTML = HTMLEncode(arAllActivities[act_i]['NAME']);
				t.rows[0].cells[1].align = 'left';

				t.insertRow(-1);
				t.rows[1].insertCell(-1).innerHTML = '<table width="100%" style="border-collapse: collapse" cellpadding="0" cellspacing="0" border="0"><tr><td width="5"></td><td style="border-bottom: 1px #EBEBEB solid; height: 1px;"><img src="/bitrix/images/1.gif" width="1" height="1"></td><td width="5"></td></tr></table>';
				t.rows[1].cells[0].colSpan = "2";

				dCont.activityTemplate = {'Properties': {'Title': arAllActivities[act_i]['NAME']}, 'Type': arAllActivities[act_i]['CLASS'], 'Children': []};

				dCont.onmousedown = function (e)
				{
					if(!e)
						e = window.event;

					var div = DragNDrop.StartDrag(e, this.activityTemplate);

					div.innerHTML = this.innerHTML;
					div.style.width = this.parentNode.offsetWidth + 'px';
				}
			}

		}
	}

	ob.RemoveResourcesSequenceActivity = ob.RemoveResources;
	ob.RemoveResources = function ()
	{
		ob.RemoveResourcesSequenceActivity();
		if(ob.Table)
		{
			ob.Table.parentNode.removeChild(ob.Table);
			ob.Table = null;
		}
	}

	return ob;
}
