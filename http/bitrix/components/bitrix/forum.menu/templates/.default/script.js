function ForumReplaceNoteError(data, not_follow_url)
{
	follow_url = (not_follow_url == true ? false : true);

	eval('result = ' + data + ';');
	if (typeof(result) == "object")
	{
		for (id in {"error" : "", "note" : ""})
		{
			if (typeof result[id] == "object" && result[id].length != "undefined")
			{
				document.getElementById("forum_" + id + "s_top").innerHTML = "";
				document.getElementById("forum_" + id + "s_bottom").innerHTML = "";
				if (result[id]["title"])
				{
					document.getElementById("forum_" + id + "s_top").innerHTML = result[id]["title"];
					document.getElementById("forum_" + id + "s_bottom").innerHTML = result[id]["title"];
				}
				if (result[id]["link"] && result[id]["link"].length > 0)
				{
					var url = result[id]["link"];
					if (url.lastIndexOf("?") == -1)
						url += "?"
					else
						url += "&";
					url += "result=" + result[id]["code"];
					jsUtils.Redirect([], url);
				}
			}
		}
	}
	FCloseWaitWindow('send_message');
	return;
}

function ForumSendData()
{
	this.form = false;
	this.id = false;
	this.oData = [];
	this.MainInfo = [];
	this.oData = [];
		
	this.Send = function(id, index)
	{
		if (!this.InitData(id, index))
			return false;
		if (!this.CheckData(id))
			return false;

		if (this.MainInfo['AJAX_TYPE'] == "Y")
		{
			var aSend = this.MainInfo;
			if (this.id == "topics")
				aSend['TID'] = this.oData;
			else
				aSend['MID'] = this.oData;
				
			aSend["AJAX_CALL"] = "Y";

			TID = CPHttpRequest.InitThread();
			
			FShowWaitWindow('send_data');
			CPHttpRequest.SetAction(TID, function(data){ForumReplaceNoteError(data);});
			CPHttpRequest.Send(TID, document.location.pathname, aSend);
		}
		else
		{
			if (this.id == "topic" && (this.MainInfo['PAGE_NAME'] == 'list'))
				this.form.TID.value = this.oData;
			else if (this.id != "topic")
				this.form.MID.value = this.oData;
			if (this.form.onsubmit)
				this.form.onsubmit();
			if (this.form.submit)
				this.form.submit();
		}
		return false;
	}
	
	this.CheckData = function()
	{
		aError = [];
		sNote = "";
		if (this.oData.length <= 0)
			aError.push(oText['no_data']);
		else if (!this.MainInfo['sessid'] || this.MainInfo['sessid'].length <= 0)
			aError.push(oText['no_sessid']);
		else if (!this.MainInfo['PAGE_NAME'] || this.MainInfo['PAGE_NAME'].length <= 0)
			aError.push(oText['no_page_name']);
		else if (parseInt(this.MainInfo['FID']) <= 0)
			aError.push(oText['no_fid']);
		else if (this.MainInfo['PAGE_NAME'] == 'read' && this.id == 'messages' && parseInt(this.MainInfo['TID']) <= 0)
			aError.push(oText['no_tid']);
		else if (this.MainInfo['ACTION'].substring(0,3).toLowerCase() == "del")
		{
			if (this.id == "topics")
				sNote = (this.oData.length > 1 ? oText['del_topics'] : oText['del_topic']);
			else
				sNote = (this.oData.length > 1 ? oText['del_messages'] : oText['del_message']);
				
			if (!confirm(sNote))
				return false;
		}
		
		if (aError.length > 0)
		{
			alert(aError.join("\n"));
			return false;
		}
		return true;
	}
	
	this.InitData = function(id, index)
	{
		this.form = document.getElementById('forum_form_' + id + '_' + index);
		if (!this.form || typeof this.form != "object")
			return false;

		this.id = id;
		this.oData = [];
		
//		try
//		{
			this.MainInfo["FID"] = this.form.FID.value;
			this.MainInfo["TID"] = this.form.TID.value;
			this.MainInfo["MID"] = this.form.MID.value;
			this.MainInfo["AJAX_TYPE"] = this.form.AJAX_TYPE.value;
			this.MainInfo["sessid"] = this.form.sessid.value;
			this.MainInfo["PAGE_NAME"] = this.form.PAGE_NAME.value;
			this.MainInfo["ACTION"] = this.form.ACTION.value;
			if (this.id == 'topic' && (this.MainInfo["PAGE_NAME"] == 'read') && 
				typeof this.form['TID'] == "object")
			{
				this.oData.push(this.form['TID'].value);
			}
			else
			{
				var items = document.getElementsByName(id + '_id[]');
				
				if (items && typeof items == "object")
				{
					if (!items.length || (typeof(items.length) == 'undefined'))
					{
						items = [items];
					}
					
					for (ii=0; ii < items.length; ii++)
					{
						if (items[ii].type == "checkbox" && items[ii].checked == true)
						{
							this.oData.push(items[ii].value);
						}
					}
				}
			}
//		}
//		catch(e)
//		{
//			return false;
//		}
		return true;
	}
}
var forumForm = new ForumSendData();