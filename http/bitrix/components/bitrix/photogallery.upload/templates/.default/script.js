if (typeof oText != "object")
	var oText = {};
function BitrixImageUploader() {
	this.Uploader = null;
	this.FileCount = 0;
	this.Flags = {};
	this.oFile = {}; 
	this.active = {};
	this.photo_album_id = 0;
	this.FieldsContaints = {};
	this.WaterMarkInfo = {};
	this.index = 0;
}

BitrixImageUploader.prototype.Init = function(iIndex) {
	this.Uploader = getImageUploader("ImageUploader" + iIndex + '');
	if (iIndex == null || !iIndex)
	{
		this.ShowError('Empty index image uploader on page.');
		return false;
	}
	else if (!this.Uploader)
	{
		this.ShowError('Error image uploader loading.');
		return false;
	}
	this.index = iIndex;
	return true;
}

BitrixImageUploader.prototype.ChangeFileCount = function() {
	if (!this.Uploader)
		return false;
	var guid = 0;
	this.FileCount = this.Uploader.getUploadFileCount();
	this.FileCount = parseInt(this.FileCount);

	for (var i = 1; i <= this.FileCount; i++)
	{
		guid = this.Uploader.getUploadFileGuid(i);
		if (typeof(this.oFile[guid]) != "object" || !this.oFile[guid] || this.oFile[guid] == null)
		{
			var sFileName = this.Uploader.getUploadFileName(i);
			if (!sFileName || sFileName == 'undefined')
				sFileName = 'noname';
			sFileName = "" + sFileName;
			if (sFileName.search(/\\/) > 0)
				sFileName = sFileName.replace(/\\/g, "/");
			var aFileName = sFileName.split("/");
			if (aFileName && aFileName.length > 0)
			{
				sFileName = aFileName[aFileName.length-1];
			}
			if (sFileName.indexOf(".") != -1)
			{
				sFileName = sFileName.replace(/\.([^\.\>\<\|\?\*\/\\\:\"]+)$/gi, '');
			}
			this.oFile[guid] = {"Title":sFileName, "Public" : (oParams[this.index]["public_by_default"] == "Y" ? "Y" : "N"), "Tag":"", "Description":""};
		}
	}

	if (this.FileCount <= 0)
	{
		if (document.getElementById("photo_count_to_upload_" + this.index))
			document.getElementById("photo_count_to_upload_" + this.index).innerHTML = oText["NoPhoto"];
		if (document.getElementById("Send_" + this.index))
		{
			document.getElementById("Send_" + this.index).onclick = function(){return false;};
			document.getElementById("Send_" + this.index).className = "nonactive";
		}
		this.Flags["SetButtonFunction"] = "N";
	}
	else
	{
		if (this.Flags["SetButtonFunction"] != "Y")
		{
			if (document.getElementById("Send_" + this.index))
			{
				document.getElementById("Send_" + this.index).onclick = new Function("getImageUploader('ImageUploader" + this.index + "').Send(); return false;");
				document.getElementById("Send_" + this.index).className = "";
			}
			this.Flags["SetButtonFunction"] = "Y";
		}
		if (document.getElementById("photo_count_to_upload_" + this.index))
			document.getElementById("photo_count_to_upload_" + this.index).innerHTML = this.FileCount;
	}
}; 

BitrixImageUploader.prototype.ChangeSelection = function() 
{
	var thumbnail1 = getImageUploader("Thumbnail" + this.index);
	try {
		if (!(this.Uploader && thumbnail1 && this.Uploader.getUploadFileSelected)) {
			return false; 
		}
	} catch(e){}
	this.HideDescription();
	for (var i = 1; i <= this.FileCount; i++)
	{
		try	{
			if (this.Uploader.getUploadFileSelected(i))
			{
				this.active.push(this.Uploader.getUploadFileGuid(i));
			}
		} catch (e) {}
	}
	this.ShowDescription();
};

BitrixImageUploader.prototype.ShowDescription = function() {
	var thumbnail1 = getImageUploader("Thumbnail" + this.index);
	try {
		if (!(this.Uploader && thumbnail1) || this.active.length <= 0) {
			return false; }
	}catch(e){return false;}

	var bEmptyFields = {"Title" : false, "Public" : false, "Tag" : false, "Description" : false};
	var sFirstFields = {"Title" : false, "Public" : false, "Tag" : false, "Description" : false};
	if (!document.getElementById("PhotoTag" + this.index))
		bEmptyFields["Tag"] = true;
	this.FieldsContaints["Fields"] = {"Title" : "", "Public" : "", "Tag" : "", "Description" : ""};

	sFirstFields["Title"] = this.oFile[this.active[0]]["Title"];
	sFirstFields["Public"] = (this.oFile[this.active[0]]["Public"] == "Y" ? "Y" : "N");
	sFirstFields["Tag"] = this.oFile[this.active[0]]["Tag"];
	sFirstFields["Description"] = this.oFile[this.active[0]]["Description"];

	for (var ii = 0; ii < this.active.length; ii++)
	{
		if (sFirstFields["Title"] != this.oFile[this.active[ii]]["Title"])
			bEmptyFields["Title"] = true;
		if (sFirstFields["Public"] != this.oFile[this.active[ii]]["Public"])
			bEmptyFields["Public"] = true;
		if (sFirstFields["Tag"] != this.oFile[this.active[ii]]["Tag"])
			bEmptyFields["Tag"] = true;
		if (sFirstFields["Description"] != this.oFile[this.active[ii]]["Description"])
			bEmptyFields["Description"] = true;

		if (bEmptyFields["Title"] && bEmptyFields["Public"] && bEmptyFields["Tags"] && bEmptyFields["Description"])
			break;
	}

	document.getElementById("PhotoTitle" + this.index).disabled = false;
	
	if (document.getElementById("PhotoPublic" + this.index)) {
//		document.getElementById("PhotoPublic" + this.index).disabled = true;
//		if (!bEmptyFields["Public"] && sFirstFields["Public"] == "Y" || this.active.length == 1)
			document.getElementById("PhotoPublic" + this.index).disabled = false;
	}
	if (document.getElementById("PhotoTag" + this.index))
		document.getElementById("PhotoTag" + this.index).disabled = false;
	document.getElementById("PhotoDescription" + this.index).disabled = false;

	document.getElementById("PhotoTitle" + this.index).value = (bEmptyFields["Title"] ? "" : sFirstFields["Title"]);
	if (document.getElementById("PhotoPublic" + this.index))
		document.getElementById("PhotoPublic" + this.index).checked = ((!bEmptyFields["Public"] && sFirstFields["Public"] == "Y") ? true : false);
	if (document.getElementById("PhotoTag" + this.index))
		document.getElementById("PhotoTag" + this.index).value = (bEmptyFields["Tag"] ? "" : sFirstFields["Tag"]);
	document.getElementById("PhotoDescription" + this.index).value = (bEmptyFields["Description"] ? "" : sFirstFields["Description"]);
	thumbnail1.setGuid(this.active[0]);
	this.FieldsContaints["Fields"] = {
		"Title" : (bEmptyFields["Title"] ? "" : sFirstFields["Title"]),
		"Public" : (bEmptyFields["Public"] ? "" : sFirstFields["Public"]),
		"Tag" : (bEmptyFields["Tag"] ? "" : sFirstFields["Tag"]),
		"Description" : (bEmptyFields["Description"] ? "" : sFirstFields["Description"])};
}

BitrixImageUploader.prototype.HideDescription = function() {
	var thumbnail1 = getImageUploader("Thumbnail" + this.index);
	try {
		if (!(this.Uploader && thumbnail1) || this.active.length <= 0) {
			return false; }
	}catch(e){return false;}
	var arValue = {
		"Title" : document.getElementById("PhotoTitle" + this.index).value,
		"Public" : (oParams[this.index]["public_by_default"] == "Y" ? "Y" : "N"),
		"Description" : document.getElementById("PhotoDescription" + this.index).value,
		"bTag" : false};
	if (document.getElementById("PhotoTag" + this.index))
	{
		arValue["Tag"] = document.getElementById("PhotoTag" + this.index).value;
		arValue["bTag"] = true;
	}
	if (document.getElementById("PhotoPublic" + this.index))
	{
		if (document.getElementById("PhotoPublic" + this.index).checked)
			arValue["Public"] = "Y";
	}
	for (var ii = 0; ii < this.active.length; ii++)
	{
		
		if (this.FieldsContaints["Fields"]["Title"] != arValue["Title"])
			this.oFile[this.active[ii]]["Title"] = arValue["Title"];
		if (this.FieldsContaints["Fields"]["Public"] != "")
			this.oFile[this.active[ii]]["Public"] = arValue["Public"];
		if (this.FieldsContaints["Fields"]["Description"] != arValue["Description"])
			this.oFile[this.active[ii]]["Description"] = arValue["Description"];
		if (arValue["bTag"] && (this.FieldsContaints["Fields"]["Tag"] != arValue["Tag"]))
			this.oFile[this.active[ii]]["Tag"] = arValue["Tag"];
	}
	
	this.active = [];
	document.getElementById("PhotoTitle" + this.index).disabled = true;
	document.getElementById("PhotoTitle" + this.index).value = "";
	if (document.getElementById("PhotoPublic" + this.index))
	{
		document.getElementById("PhotoPublic" + this.index).disabled = true;
		document.getElementById("PhotoPublic" + this.index).checked = false;
	}
	if (document.getElementById("PhotoTag" + this.index))
	{
		document.getElementById("PhotoTag" + this.index).disabled = true;
		document.getElementById("PhotoTag" + this.index).value = "";
	}
	document.getElementById("PhotoDescription" + this.index).disabled = true;
	document.getElementById("PhotoDescription" + this.index).value = "";
	thumbnail1.setGuid("");
}

BitrixImageUploader.prototype.PackageBeforeUpload = function(PackageIndex) {
	// Send always 1 file
	oWaterMark = {'width' : this.Uploader.getUploadFileWidth(1), 'height' : this.Uploader.getUploadFileHeight(1)};
	iSize = 0; iHeight = 0; iKoeff = 0;
	
	if (!this.WaterMarkInfo['text'] || this.WaterMarkInfo['text'].length <= 0 || 
		this.Uploader.getFilesPerOnePackageCount() > 1 || oWaterMark['width'] < oParams[this.index]["min_size_picture"] || 
		this.Uploader.getUploadFileWidth(1) < 0) 
	{
		for (var ii = 1; ii <= this.Uploader.getUploadThumbnailCount(); ii++) 
		{
			this.Uploader.setUploadThumbnailWatermark(ii, "");
		}
		return false;
	}

	for (var ii = 1; ii <= this.Uploader.getUploadThumbnailCount(); ii++)
	{
		iKoeff = 1;
		var iWidth = oWaterMark['width'];
		if (this.Uploader.getUploadThumbnailFitMode(ii) != 5)
		{
			iWidth = this.Uploader.getUploadThumbnailWidth(ii);
			if (iWidth < oWaterMark['width'])
			{
				iKoeff = (oWaterMark['width'] > oWaterMark['height'] ? oWaterMark['width'] : oWaterMark['height']);
				iKoeff = (iWidth / iKoeff)
			}
		}
		
		iWidth = parseInt(oWaterMark['width'] * iKoeff);
		var iHeight = parseInt(oWaterMark['height'] * iKoeff);
		if (iWidth < oParams[this.index]["min_size_picture"] - 1)
		{
			this.Uploader.setUploadThumbnailWatermark(ii, "");
		}
		else
		{
			iSize = parseInt(iWidth * 0.07);
			iSize = (iSize > 55 ? 55 : iSize);

			if (this.WaterMarkInfo['size'] == 'big')
			{
				iSize = parseInt(iWidth * 0.11);
				iSize = (iSize > 75 ? 75 : iSize);
			}
			else if (this.WaterMarkInfo['size'] == 'small')
			{
				iSize = parseInt(iWidth * 0.05);
				iSize = (iSize > 35 ? 35 : iSize);
			}
			
			if (parseInt(iSize * this.WaterMarkInfo['text'].length * 0.6) > parseInt(oWaterMark['width'] * iKoeff + 1))
			{
				iSize = parseInt((oWaterMark['width']*iKoeff+1)/(this.WaterMarkInfo['text'].length*0.6));
			}
			iSize = (iSize < 9 ? 9 : iSize);
			
			this.Uploader.setUploadThumbnailWatermark(ii, "opacity=100;Font=Arial;size=" + iSize +
				";FillColor=#" + this.WaterMarkInfo["color"] + ";Position=" + this.WaterMarkInfo["position"] +
				";text='" + this.WaterMarkInfo['text'].split("'").join("''") + "'");
		}
	}
	return;
//		}
//		catch(e){}
}
BitrixImageUploader.prototype.BeforeUpload = function() {
	var thumbnail1 = getImageUploader("Thumbnail" + this.index);
	if (thumbnail1)
		thumbnail1.setGuid("");
	if (!this.Uploader)
		return false;
	this.HideDescription();
	try	{
	var guid = 0;
	this.FileCount = this.Uploader.getUploadFileCount();

	for (var i = 1; i <= this.FileCount; i++)
	{
		guid = this.Uploader.getUploadFileGuid(i);
		this.Uploader.AddField('Description_'+i, this.oFile[guid]["Description"]);
		this.Uploader.setUploadFileDescription(i, this.oFile[guid]["Description"]);
		this.Uploader.AddField('Title_'+i, this.oFile[guid]["Title"]);
		this.Uploader.AddField('Public_'+i, this.oFile[guid]["Public"]);
		if (this.oFile[guid]["Tag"])
			this.Uploader.AddField('Tags_'+i, this.oFile[guid]["Tag"]);
	}
	// Additional sights
	if (oParams[this.index]['draft'] && typeof oParams[this.index]['draft'] == "object")
	{
		for (var ii in oParams[this.index]['draft'])
		{
			if (oParams[this.index]['draft'][ii]['size'] && oParams[this.index]['draft'][ii]['size'] > 0)
			{
				this.Uploader.UploadThumbnailAdd("Fit", oParams[this.index]['draft'][ii]['size'], oParams[this.index]['draft'][ii]['size']);
				this.Uploader.setUploadThumbnailJpegQuality(this.Uploader.getUploadThumbnailCount(), oParams[this.index]['draft'][ii]['quality']);
			}
		}
		oParams[this.index]['draft'] = false;
	}
	sWatermarkText = "";
	if (document.getElementById("watermark"))
		sWatermarkText = document.getElementById("watermark").value;
		// resize
	if (document.getElementById("photo_resize_size") && document.getElementById("photo_resize_size").value > 0)
	{
		this.Uploader.setUploadThumbnail3FitMode("Fit");
		if (document.getElementById("photo_resize_size").value == 1)
		{
			this.Uploader.setUploadThumbnail3Width(1024);
			this.Uploader.setUploadThumbnail3Height(768);
		}
		else if (document.getElementById("photo_resize_size").value == 2)
		{
			this.Uploader.setUploadThumbnail3Width(800);
			this.Uploader.setUploadThumbnail3Height(600);
		}
		else if (document.getElementById("photo_resize_size").value == 3)
		{
			this.Uploader.setUploadThumbnail3Width(640);
			this.Uploader.setUploadThumbnail3Height(480);
		}
//		this.Uploader.setUploadThumbnail3CopyExif(true);
	}
	else if (sWatermarkText.length <= 0)
	{
		this.Uploader.setUploadThumbnail3FitMode("Off");
		this.Uploader.setUploadSourceFile("true");
	}

	// watermark
	if (sWatermarkText.length > 0)
	{
		var watermark = {'color' : 'ffffff', 'size': 'middle', 'position': 'TopLeft'};

		sWatermarkText = (document.getElementById('watermark_copyright') && document.getElementById('watermark_copyright').value == 'hide' ? sWatermarkText : 
			(String.fromCharCode(169)+sWatermarkText)); 

		if (document.getElementById("watermark_color"))
			watermark["color"] = document.getElementById("watermark_color").value;

		if (document.getElementById("watermark_size"))
			watermark["size"] = document.getElementById("watermark_size").value;

		if (document.getElementById("watermark_position"))
		{
			var res = document.getElementById("watermark_position").value.substr(0, 1);
			watermark["position"] = (res == "m" ? "Center" : (res == "b" ? "Bottom" : "Top"));
			var res = document.getElementById("watermark_position").value.substr(1, 1);
			watermark["position"] += (res == "c" ? "Center" : (res == "r" ? "Right" : "Left"));
			watermark["position"] = (watermark["position"] == "CenterCenter" ? "Center" : watermark["position"]);
		}

		this.WaterMarkInfo = {'text' : sWatermarkText, 'color' : watermark["color"],
			'size' : watermark["size"], 'position' : watermark["position"]};
	}

	if (phpVars['bitrix_sessid'])
		this.Uploader.AddField("sessid", phpVars['bitrix_sessid']);
	else if (document.getElementById("sessid"))
		this.Uploader.AddField("sessid", document.getElementById("sessid").value);

	if (document.getElementById("photo_album_id" + this.index))
	{
		this.Uploader.AddField("photo_album_id", document.getElementById("photo_album_id" + this.index).value);
		this.photo_album_id = document.getElementById("photo_album_id" + this.index).value;
	}
	this.Uploader.AddField("save_upload", "Y");
	this.Uploader.AddField("AJAX_CALL", "Y");
	this.Uploader.AddField("CACHE_RESULT", "Y");
	if (IUCommon.browser.isWinIE)
		this.Uploader.AddField("CONVERT", "Y");

	try{
		if (!IUCommon.browser.isWinIE || !this.Uploader.activeXControlEnabled)
		{
			this.Uploader.AddCookie(phpVars['COOKIES']);
		}
	}catch(e){}
	}catch(e){}
}

BitrixImageUploader.prototype.AfterUpload = function(htmlPage) {
	var result = {};
	var error = false;
	if (!document.getElementById("photo_error_" + this.index))
		return;
	document.getElementById("photo_error_" + this.index).innerHTML = "";
	try	{
		eval("result="+htmlPage);
	} catch(e) {}

	if (typeof result != "object")
		result = {};

	if (result["status"] == "success")
	{
		for (var key in result["files"])
		{
			if (result["files"][key] && result["files"][key]["status"] != "success")
			{
				if (result["files"][key]["error"])
				{
					document.getElementById("photo_error_" + this.index).innerHTML += result["files"][key]["error"] + " (" + key +  ")<br />";
					error = true;
				}

			}
		}
	}
	else
	{
		if (result["error"])
		{
			document.getElementById("photo_error_" + this.index).innerHTML = result["error"];
			error = true;
		}
	}
	
	if (!error)
	{
		url = window.oParams[this.index]['url']['this'];
		if (result['section_id'] > 0)
			url = window.oParams[this.index]['url']['section'].replace('#SECTION_ID#', result['section_id']);
		else if (parseInt(this.photo_album_id) > 0)
			url = window.oParams[this.index]['url']['section'].replace('#SECTION_ID#', this.photo_album_id);
		jsUtils.Redirect([], url);
	}
}
SendTags = function(oObj)
{
	try
	{
		if (TcLoadTI)
		{
			if (typeof window.oObject[oObj.id] != 'object')
				window.oObject[oObj.id] = new JsTc(oObj);
			return;
		}
		setTimeout(SendTags(oObj), 10);
	}
	catch(e)
	{
		setTimeout(SendTags(oObj), 10);
	}
}

var WaterMark = {
	oBody : false,
	oSwitcher : false,
	bShowed : false,
	active : "",

	ShowMenu : function(id)
	{
		if (id.length <= 0)
			return false;
		this.Close()

		this.active = id;
		this.oBody = document.getElementById('watermark_' + id + '_container');
		this.oSwitcher = document.getElementById('watermark_' + id + '_switcher');

		this.Show();
		return;
	},

	Show : function()
	{
		if (typeof WaterMark.oBody != "object")
			return false;

		WaterMark.oBody.style.display = 'block';

		jsUtils.addEvent(document, "keypress", WaterMark.CheckKeyPress);
		jsUtils.addEvent(document, "click", WaterMark.CheckClickMouse);
	},

	Close : function()
	{
		if (typeof WaterMark.oBody != "object")
			return false;

		WaterMark.oBody.style.display = 'none';

		jsUtils.removeEvent(document, "keypress", WaterMark.CheckKeyPress);
		jsUtils.removeEvent(document, "click", WaterMark.CheckClickMouse);
		return true;
	},

	CheckKeyPress : function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			WaterMark.Close();
	},

	CheckClickMouse : function(e)
	{
		if (typeof WaterMark.oBody != "object")
			return false;

        var windowSize = jsUtils.GetWindowSize();
        var x = e.clientX + windowSize.scrollLeft;
        var y = e.clientY + windowSize.scrollTop;
		
		//		/*switch region*/
		var pos1 = jsUtils.GetRealPos(WaterMark.oSwitcher);
		if(x >= pos1['left'] && x <= pos1['right'] && y >= pos1['top'] && y <= pos1['bottom'])
			return;
		/*menu region*/
		var pos = jsUtils.GetRealPos(WaterMark.oBody);
		if(x >= pos['left'] && x <= pos['right'] && y >= pos['top'] && y <= pos['bottom'])
			return;
		WaterMark.Close();
	},

	ChangeData : function(value)
	{
		var val = document.getElementById('watermark_' + this.active).value;
		document.getElementById(this.active + '_' + val).className = document.getElementById(this.active + '_' + val).className.replace('active', '').replace('  ', ' ');

		if (this.active == 'color')
			document.getElementById('watermark_' + this.active + '_switcher').firstChild.style.backgroundColor='#' + value;
		else
			document.getElementById('watermark_' + this.active + '_switcher').className = value;
			
		IUSendData('save=' + this.active + '&position=' + value);

		document.getElementById(this.active + '_' + value).className += ' active';
		document.getElementById('watermark_' + this.active).value = value;
	},

	ChangeText : function(obj)
	{
		if (typeof obj != "object")
			return;
		IUSendData('save=text&position=' + obj.value);
	}
}

/* Upload form */ 
ImageUploadFormClass = function()
{
	// Visual data
	this.form = false;
	this.container = false;
	// Main data
	this.oFiles = {}, this.oFields = {};
	this.iFileCount = 0, this.iFileIndex = 0;
	this.oInfo = {'stage' : 'ready', /* ready, upload, stop */
		'stage_upload' : 'ready', /* ready, wait, done*/
		'file_index' : 1};
	_this = this;
}
ImageUploadFormClass.prototype.Init = function(index, bAddFile)
{
	this.index = parseInt(index);
	
	this.container = document.getElementById('file_object_div_' + index);
	this.form = document.getElementById('file_object_form_' + index);
	if (!this.container || !this.form) {
		return false; }

	this.form.onsubmit = function(){_this.onSubmit(this); return false;}
	var res = document.getElementById('file_object_noscript_' + index);
	if (res && res != null) {
		res.parentNode.removeChild(res); }

	if (document.getElementById('Send_' + this.index)) {
		document.getElementById('Send_' + this.index).onclick = function(){_this.onSubmit(_this.form); return false;};
		document.getElementById('Send_' + this.index).style.display = 'block'; }
	
	this.oFields = {
			"SourceFile" : {"type" : "file", "title" : oText["SourceFile"]}, 
			"Title" : {"type" : "text", "title" : oText["Title"]}, 
			"Tags" : {"type" : "text", "title" : oText["Tags"], "use_search" : "Y"}, 
			"Description" : {"type" : "textarea", "title" : oText["Description"]}};
	this.iFileIndex = 0, this.iFileCount = 0;
	if (bAddFile !== false)
	{
		this.AddFile();
	}
}

ImageUploadFormClass.prototype.onChangeFile = function(oFile)
{
	var bEmpty = true; 
	for (var ii = 0; ii <= this.iFileIndex; ii++)
	{
		if (this.CheckData(ii))
		{
			bEmpty = false;
			break;
		}
	}
	document.getElementById('Send_' + this.index).className = (bEmpty ? "nonactive" : "active");

	if (typeof(oFile) != "object" || oFile == null || oFile.value.length <= 0 || this.oInfo['stage'] == 'upload')
		return false;
	var form = document.forms['file_object_form_' + this.index + '_' + this.iFileIndex];
	if (form['SourceFile_1'].value.length > 0)
	{
		if (document.getElementById('file_delete_' + this.index + '_' + this.iFileIndex))
			document.getElementById('file_delete_' + this.index + '_' + this.iFileIndex).style.display = 'block';
		this.AddFile();
	}
}
ImageUploadFormClass.prototype.onDeleteFile = function(oObj)
{
	if (typeof(oObj) != "object" || oObj == null || this.oInfo['stage'] == 'upload')
		return false;
	var file_index = parseInt(oObj.id.replace('file_delete_' + this.index + '_', ''));
	if (this.iFileIndex <= file_index)
		return true;
	this.DeleteElement(file_index);
	this.iFileCount = 0;
	for (var ii = 0; ii <= this.iFileIndex; ii++)
	{
		var title = document.getElementById('file_title_' + this.index + '_' + ii);
		if (title && title != null)
		{
			this.iFileCount++;
			title.innerHTML = this.iFileCount;
		}
	}
	this.onChangeFile(false);
	return true;
}
ImageUploadFormClass.prototype.onSubmit = function(oForm)
{
	if (typeof(oForm) != "object" || oForm == null)
		return false;
	this.oInfo = {'stage' : 'ready', 'stage_upload' : 'ready', 'file_index' : 1};
	this.SendData();
	return false;
}
ImageUploadFormClass.prototype.onFileUpload = function(index, htmlPage)
{
	this.oInfo['stage'] = 'ready';
	this.oInfo['stage_upload'] = 'done';
	this.oInfo['file_index'] = index;
	var result = {'error' : window.oText['ErrorNoData'], 'files' : {}}, bError = false, sError = '';
	var result_default = {'error' : window.oText['ErrorNoData'], 'files' : {}};

	
	try
	{
		eval("var result=" + htmlPage + ";");
		if (typeof result != "object" || result == null)
		{
			result = result_default;
		}
		else if (!result['files'] || result['files'].length <= 0)
		{
			result = result_default;
		}
		else if (result["error"].length <= 0)
		{
			// check structure;
		}
	}
	catch(e)
	{
		result = {'error' : window.oText['ErrorNoData'], 'files' : {}};
	}

	if (result["error"].length > 0 || bError)
	{
		bError = true;
		sError = result["error"] + '<br />';
	}

	var file_name = '';
	
	if (!bError)
	{
		for (var key in result["files"])
		{
			file_name = key;
			if (parseInt(index) != parseInt(result["files"][key]["number"]))
				continue;
			if (result["files"][key] && result["files"][key]["status"] != "success")
			{
				bError = true;
				if (result["files"][key]["error"])
				{
					sError += result["files"][key]["error"];
				}
			}
			break; 
		}
	}
	
	if (bError)
	{
		this.ShowFile(index, 'done', '<span class="error required starrequired">' + sError + '</span>');
	}
	else
	{
		var text = ''; 
		if (result["files"][file_name] && result["files"][file_name]['path'])
		{
			text = '<div class="image-uploader-result-upload-file" style="background-image:url(\'' + result["files"][file_name]['path'] + '\');"></div>';
		}
		this.ShowFile(index, 'done', text);
	}
}

ImageUploadFormClass.prototype.CheckData = function(index)
{
	var bEmpty = true;
	index = parseInt(index);
	if (document.forms['file_object_form_' + this.index + '_' + index] && 
		document.forms['file_object_form_' + this.index + '_' + index]['SourceFile_1'] && 
		document.forms['file_object_form_' + this.index + '_' + index]['SourceFile_1'].value && 
		document.forms['file_object_form_' + this.index + '_' + index]['SourceFile_1'].value.length > 0)
	{
		bEmpty = false; 
	}
	return (bEmpty ? false : true);
}
ImageUploadFormClass.prototype.ShowFile = function(index, status, text)
{
	form = document.forms['file_object_form_' + this.index + '_' + index];
	var div = document.getElementById("file_upload_file_" + this.index + '_' + index);
	var substrate = document.getElementById("substrate_" + this.index + '_' + index);
	
	if (!form || !div)
		return false;

	if (status == 'wait')
	{
		if (!substrate || substrate == null)
		{
			substrate = document.createElement("SPAN");
			substrate.id = 	"substrate_" + this.index + '_' + index;
			substrate.className = 'wd-substrate wd-substrate-wait';
			substrate.style.zIndex = 100;
			substrate.style.position = 	'absolute';
			substrate.style.display = 'none';
			substrate.style.backgroundColor = '#ededed';
			substrate.style.opacity = '0.3';
			if (substrate.style.MozOpacity)
				substrate.style.MozOpacity = '0.3';
			else if (substrate.style.KhtmlOpacity)
				substrate.style.KhtmlOpacity = '0.3';
			if (jsUtils.IsIE())
			{
		 		substrate.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity=30)";
			}
			substrate.style.left = '2px';
			substrate.style.top = '2px';
			substrate.style.width = (parseInt(div.offsetWidth) - 4) + "px";
			substrate.style.height = (parseInt(div.offsetHeight) - 4) + "px";
			this.oInfo['div_width'] = parseInt(div.offsetWidth);
			this.oInfo['div_height'] = parseInt(div.offsetHeight);
			form.appendChild(substrate);
		}
		div.style.position = 'relative';
		substrate.style.display = 'block';
	}
	else if (status == 'done')
	{
		var pos = {'width' : form.offsetWidth, 'height' : form.offsetHeight};
		form.style.display = 'none';
		var div_reply = form.parentNode.appendChild(document.createElement('DIV'));
		div_reply.className =  'reply';
		div_reply.innerHTML = '<div class="inner">' + text + '</div>';
		div_reply.style.width = pos['width'] + "px";
		div_reply.style.height = pos['height'] + "px";
		form.parentNode.removeChild(form);
		this.onChangeFile(false);
	}
	else if (status == 'error')
	{
		if (!(!substrate || substrate == null))
			substrate.style.display = 'none';
	}
}
ImageUploadFormClass.prototype.SendFile = function(index)
{
	form = document.forms['file_object_form_' + this.index + '_' + index];
	form.action = this.form.action;
	if (index != 1)
	{
		for (sFieldName in this.oFields)
		{
			if (sFieldName == 'SourceFile')
				continue; 
			if (!form[sFieldName + '_1'] || form[sFieldName + '_1'].type == "submit" || 
				(form[sFieldName + '_1'].type == "checkbox" && form[sFieldName + '_1'].checked != true))
				continue;
			var input = document.createElement('INPUT');
			input.type = "hidden";
			input.name = sFieldName + '_' + index;
			input.value = form[sFieldName + '_1'].value;
			form.appendChild(input);
		}
	}
	for (var ii = 0; ii < this.form.elements.length; ii++)
	{
		if (this.form.elements[ii]["type"] == "submit" || 
			(this.form.elements[ii]["type"] == "checkbox" && this.form.elements[ii].checked != true))
			continue;
		var input = document.createElement('INPUT');
		input.type = "hidden";
		input.name = this.form.elements[ii].name;
		input.value = this.form.elements[ii].value;
		form.appendChild(input);
	}
	var input = document.createElement('INPUT');
	input.type = "hidden";
	input.name = 'CACHE_RESULT';
	input.value = 'Y';
	form.appendChild(input);
	var input = document.createElement('INPUT');
	input.type = "hidden";
	input.name = 'PackageIndex';
	input.value = (index - 1);
	form.appendChild(input);
	
	eval("jsAjaxUtil.SendForm(form, function(data){_this.onFileUpload(" + index + ", data);})");
	form.submit();
}
ImageUploadFormClass.prototype.SendData = function()
{
	if (this.oInfo['stage'] == 'ready')
	{
		for (var ii = this.oInfo['file_index']; ii <= this.iFileIndex; ii++)
		{
			if (!this.CheckData(ii))
			{
				this.ShowFile(ii, 'error', '');
				continue;
			}
			else
			{
				this.oInfo['stage'] = 'upload';
				this.oInfo['stage_upload'] = 'wait';
				this.oInfo['file_index'] = ii;
	
				this.ShowFile(ii, 'wait', '');
				this.SendFile(ii);
				break;
			}
		}
	}
	if (this.oInfo['stage'] == 'upload')
	{
		setTimeout(function(){_this.SendData()}, 1000);
		return;
	}
	else if (this.oInfo['stage'] == 'stop')
	{
//		debug_info('Loading is stoped by user.');
	}
	else if (this.oInfo['stage'] == 'error')
	{
//		debug_info('Files was load with errors. ');
	}
	else if (this.oInfo['stage'] == 'ready')
	{
//		debug_info('Files is load seccessfuly. ');
	}
	else
	{
//		debug_info('Unknown error. ');
	}
}

ImageUploadFormClass.prototype.AddFile = function()
{
	this.iFileIndex++;
	this.iFileCount++;
	var form_html = "", fields_html = "", prefix = this.index + '_' + this.iFileIndex, field_name = "", text = "";
	for (var sFieldName in this.oFields)
	{
		field_name = sFieldName + '_1';
		text = '<input type="' + this.oFields[sFieldName]["type"] + '" name="' + field_name + '" id="' + field_name + '_' + prefix + '" />';
		if (this.oFields[sFieldName]["type"] == 'checkbox')
			text = '<input type="' + this.oFields[sFieldName]["type"] + '" name="' + field_name + '" id="' + field_name + '_' + prefix + '" ' + 
				(this.oFields[sFieldName]["checked"] == "Y" ? " checked='checked' " : "") + ' value="Y"/>';
		else if (this.oFields[sFieldName]["type"] == 'textarea')
			text = '<textarea name="' + field_name + '" id="' + field_name + '_' + prefix + '"></textarea>';
		else if (this.oFields[sFieldName]["type"] == 'file')
			text = '<input type="file" name="' + field_name + '" id="' + field_name + '_' + prefix + '" onchange="oParams[\'' + this.index + '\'][\'object\'].onChangeFile(this)" />';
		else if (this.oFields[sFieldName]["type"] == 'hidden')
			text = '<input type="hidden" name="' + field_name + '" id="' + field_name + '_' + prefix + '" value="' + this.oFields[sFieldName]["value"] + '"/>';
		else if (this.oFields[sFieldName]["use_search"] == "Y")
			text = '<input type="' + this.oFields[sFieldName]["type"] + '" name="' + field_name + '" id="' + field_name + '_' + prefix + '" onfocus="oParams[\'' + this.index + '\'][\'object\']' + '.SendTags(this)" />';
		if (this.oFields[sFieldName]["type"] == 'hidden')
		{
			fields_html += text;
		}
		else
		{
			if (this.oFields[sFieldName]["type"] == 'checkbox')
			{
				text = text + '<label for="' + field_name + '_' + prefix + '">' + this.oFields[sFieldName]["title"] + '</label>';
			}
			else
			{
				text = '<label for="' + field_name + '_' + prefix + '">' + this.oFields[sFieldName]["title"] + '</label>' + text;
			}
			fields_html += '<div class="photo-uploader-field photo-uploader-field-' + sFieldName.toLowerCase() + '">' + text + '</div>';
		}
	}
	
	form_html +=  '<div class="wd-t"><div class="wd-r"><div class="wd-b"><div class="wd-l"><div class="wd-c">';
	
	form_html +=  '<div class="wd-title"><div class="wd-tr"><div class="wd-br"><div class="wd-bl"><div class="wd-tl">';
	form_html +=   '<div class="wd-del" id="file_delete_' + prefix + '" onclick="oParams[\'' + this.index + '\'][\'object\']' + '.onDeleteFile(this)" style="display:none;"></div>';
	form_html +=   '<div class="wd-title-header" id="file_title_' + prefix + '">' + this.iFileCount + '</div></div></div></div></div></div>';
	form_html +=   '<form id="file_object_form_' + prefix + '" method="POST" enctype="multipart/form-data" class="photo-form">';
	form_html +=    fields_html;
	form_html +=   '</form>';
	form_html +=  '</div></div></div></div></div>';
	var oDiv = this.container.appendChild(document.createElement('DIV'));
	oDiv.id = 'file_upload_file_' + prefix;
	oDiv.className = 'image-uploader-form-file'; 
	oDiv.innerHTML = form_html;
}
ImageUploadFormClass.prototype.DeleteElement = function(index)
{
	index = parseInt(index);
	if (index <= 0)
		return false;
	oDiv = document.getElementById('file_upload_file_' + this.index + '_' + index);
	if (oDiv)
	{
		oDiv.parentNode.removeChild(oDiv);
		this.iFileCount--;
		return true;
	}
	return false;
}

ImageUploadFormClass.prototype.ShowUploadError = function(sText)
{
	if (document.getElementById("photo_error_" + this.index))
		document.getElementById("photo_error_" + this.index).innerHTML = sText;
	else if (document.getElementById("photo_error"))
		document.getElementById("photo_error").innerHTML = sText;
}
ImageUploadFormClass.prototype.SendTags = function(oObj)
{
	if (typeof oObj != "object" || oObj == null)
		return false;
	if (window.TcLoadTI == true)
	{
		if (typeof window.oObject[oObj.id] != 'object')
			window.oObject[oObj.id] = new JsTc(oObj);
		return;
	}
	setTimeout(this.SendTags(oObj), 10);
}

ChangeModeUploader = function(view_mode_handler)
{
	if (!view_mode_handler || !view_mode_handler.form || parseInt(view_mode_handler.form.user_id.value) < 0)
		return false;
	var url = '/bitrix/components/bitrix/photogallery.upload/user_settings.php?save=view_mode&sessid=' + view_mode_handler.form.sessid.value + '&view_mode=' + view_mode_handler.value;
	var TID = jsAjaxUtil.LoadData(url, new Function("jsUtils.Redirect([], '" + view_mode_handler.form.action + "')"));
}

PUtilsIsLoaded = true;