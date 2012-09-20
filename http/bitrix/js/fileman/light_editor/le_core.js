function JCLightHTMLEditor(arConfig) {this.Init(arConfig);}

JCLightHTMLEditor.prototype = {
Init: function(arConfig)
{
	var _this = this;
	this.arConfig = arConfig;
	this.bxTags = {};
	this.id = arConfig.id;
	this.bKillPInIE = true;
	this.bPopup = false;
	this.arBBTags = ['p', 'u', 'div', 'table', 'tr', 'img', 'td', 'a'];

	if (this.arConfig.arBBTags)
		this.arBBTags = this.arBBTags.concat(this.arConfig.arBBTags);

	this.arConfig.width = this.arConfig.width ? parseInt(this.arConfig.width) + (this.arConfig.width.indexOf('%') == -1 ? "px" : '%') : "100%";
	this.arConfig.height = this.arConfig.height ? parseInt(this.arConfig.height) + (this.arConfig.height.indexOf('%') == -1 ? "px" : '%') : "100%";
	this.SetConstants();
	this.sEditorMode = 'html';
	this.toolbarLineCount = 1;

	this.CACHE = {};
	this.arVideos = {};
	// Set content from config;
	this.content = this.arConfig.content;
	this.oSpecialParsers = {};
	this.bIEplusDoctype = BX.browser.IsDoctype() && BX.browser.IsIE();

	if (arConfig.parsers)
	{
		for (var p in arConfig.parsers)
		{
			if (arConfig.parsers[p])
				this.oSpecialParsers[p] = arConfig.parsers[p];
		}
	}

	this.bDialogOpened = false;

	// Sceleton
	this.pFrame = BX('bxlhe_frame_' + this.id);
	this.pFrame.style.display = "block";

	var pWw = BX('bxlhe_ww_' + this.id);
	if (pWw && pWw.parentNode)
	{
		BX.closeWait(pWw);
		pWw.parentNode.removeChild(pWw);
	}

	this.pFrame.style.width = this.arConfig.width;
	this.pFrame.style.height = this.arConfig.height;

	this.pFrameTable = this.pFrame.firstChild;
	this.pButtonsCell = this.pFrameTable.rows[0].cells[0];
	this.pButtonsCont = this.pButtonsCell.firstChild;
	this.pEditCont = this.pFrameTable.rows[1].cells[0];

	if (this.arConfig.height.indexOf('%') == -1)
	{
		h = parseInt(this.arConfig.height) - this.toolbarLineCount * 25;
		if (h > 0)
			this.pEditCont.style.height = h + 'px';
	}

	// iFrame
	this.CreateFrame();

	// Textarea
	this.pSourceDiv = this.pEditCont.appendChild(BX.create("DIV", {props: {className: 'lha-source-div' }}));
	this.pTextarea = this.pSourceDiv.appendChild(BX.create("TEXTAREA", {props: {className: 'lha-textarea', rows: 25, id: this.arConfig.inputId}}));
	this.pHiddenInput = this.pFrame.appendChild(BX.create("INPUT", {props: {type: 'hidden', name: this.arConfig.inputName}}));

	this.pTextarea.onfocus = function(){_this.bTextareaFocus = true;};
	this.pTextarea.onblur = function(e){_this.bTextareaFocus = false;};

	// Sort smiles
	if (this.arConfig.arSmiles && this.arConfig.arSmiles.length > 0)
		this.sortedSmiles = this.arConfig.arSmiles.sort(function(a, b){return b.code.length - a.code.length;});

	if (!this.arConfig.bBBCode && this.arConfig.bConvertContentFromBBCodes)
		this.arConfig.bBBCode = true;

	if (this.arConfig.bBBCode)
		this.InitBBCode();

	this.bBBCode = this.arConfig.bBBCode;
	this.bBBParseImageSize = this.arConfig.bBBParseImageSize;

	if (this.arConfig.bResizable)
	{
		this.pResizer = BX('bxlhe_resize_' + this.id);
		this.pResizer.style.width = this.arConfig.width;
		this.pResizer.title = LHE_MESS.ResizerTitle;

		if (!this.arConfig.minHeight || parseInt(this.arConfig.minHeight) <= 0)
			this.arConfig.minHeight = 100;
		if (!this.arConfig.maxHeight || parseInt(this.arConfig.maxHeight) <= 0)
			this.arConfig.maxHeight = 2000;

		this.pResizer.unselectable = "on";
		this.pResizer.ondragstart = function (e){return BX.PreventDefault(e);};
		this.pResizer.onmousedown = function(){_this.InitResizer(); return false;};

		BX.bind(this.pTextarea, 'keydown', BX.proxy(this.AutoResize, this));

		if (this.arConfig.bAutoResize)
			BX.addCustomEvent(this, 'onShow', BX.proxy(this.AutoResize, this));
	}

	// Add buttons
	this.AddButtons();

	this.SetEditorContent(this.content);
	this.oTransOverlay = new LHETransOverlay({zIndex: 995}, this);
	// TODO: Fix it
	//this.oContextMenu = new LHEContextMenu({zIndex: 1000}, this);

	BX.onCustomEvent(window, 'LHE_OnInit', [this]);

	// Init events
	BX.bind(this.pEditorDocument, 'click', BX.proxy(this.OnClick, this));
	BX.bind(this.pEditorDocument, 'mousedown', BX.proxy(this.OnMousedown, this));
	//BX.bind(this.pEditorDocument, 'contextmenu', BX.proxy(this.OnContextMenu, this));

	if (this.arConfig.bSaveOnBlur)
		BX.bind(document, "mousedown", BX.proxy(this.OnDocMousedown, this));

	if (this.arConfig.ctrlEnterHandler && typeof window[this.arConfig.ctrlEnterHandler] == 'function')
		this.ctrlEnterHandler = window[this.arConfig.ctrlEnterHandler];

	if (this.arConfig.bSetDefaultCodeView && this.sourseBut)
		this.sourseBut.oBut.handler(this.sourseBut);

	BX.ready(function(){
		if (_this.pFrame.offsetWidth == 0 && _this.pFrame.offsetWidth == 0)
		{
			_this.onShowInterval = setInterval(function(){
				if (_this.pFrame.offsetWidth != 0 && _this.pFrame.offsetWidth != 0)
				{
					BX.onCustomEvent(_this, 'onShow');
					clearInterval(_this.onShowInterval);
				}
			}, 500);
		}
		else
		{
			BX.onCustomEvent(_this, 'onShow');
		}
	});
},

CreateFrame: function()
{
	if (this.iFrame && this.iFrame.parentNode)
	{
		this.pEditCont.removeChild(this.iFrame);
		this.iFrame = null;
	}

	this.iFrame = this.pEditCont.appendChild(BX.create("IFRAME", {props: { id: 'LHE_iframe_' + this.id, className: 'lha-iframe', src: "javascript:void(0)", frameborder: 0}}));

	if (this.iFrame.contentDocument && !BX.browser.IsIE())
		this.pEditorDocument = this.iFrame.contentDocument;
	else
		this.pEditorDocument = this.iFrame.contentWindow.document;
	this.pEditorWindow = this.iFrame.contentWindow;
},

SetConstants: function()
{
	this.reBlockElements = /^(BR|TITLE|TABLE|SCRIPT|TR|TBODY|P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI)$/i;
	this.oneGif = this.arConfig.oneGif;
	this.imagePath = this.arConfig.imagePath;

	this.arColors = [
	'#FF0000', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#FF00FF', '#FFFFFF', '#EBEBEB', '#E1E1E1', '#D7D7D7', '#CCCCCC', '#C2C2C2', '#B7B7B7', '#ACACAC', '#A0A0A0', '#959595',
	'#EE1D24', '#FFF100', '#00A650', '#00AEEF', '#2F3192', '#ED008C', '#898989', '#7D7D7D', '#707070', '#626262', '#555', '#464646', '#363636', '#262626', '#111', '#000000',
	'#F7977A', '#FBAD82', '#FDC68C', '#FFF799', '#C6DF9C', '#A4D49D', '#81CA9D', '#7BCDC9', '#6CCFF7', '#7CA6D8', '#8293CA', '#8881BE', '#A286BD', '#BC8CBF', '#F49BC1', '#F5999D',
	'#F16C4D', '#F68E54', '#FBAF5A', '#FFF467', '#ACD372', '#7DC473', '#39B778', '#16BCB4', '#00BFF3', '#438CCB', '#5573B7', '#5E5CA7', '#855FA8', '#A763A9', '#EF6EA8', '#F16D7E',
	'#EE1D24', '#F16522', '#F7941D', '#FFF100', '#8FC63D', '#37B44A', '#00A650', '#00A99E', '#00AEEF', '#0072BC', '#0054A5', '#2F3192', '#652C91', '#91278F', '#ED008C', '#EE105A',
	'#9D0A0F', '#A1410D', '#A36209', '#ABA000', '#588528', '#197B30', '#007236', '#00736A', '#0076A4', '#004A80', '#003370', '#1D1363', '#450E61', '#62055F', '#9E005C', '#9D0039',
	'#790000', '#7B3000', '#7C4900', '#827A00', '#3E6617', '#045F20', '#005824', '#005951', '#005B7E', '#003562', '#002056', '#0C004B', '#30004A', '#4B0048', '#7A0045', '#7A0026'
	];

	this.systemCSS = "img.bxed-anchor{background-image: url(" + this.imagePath + "lhe_iconkit.gif)!important; background-position: -260px 0!important; height: 20px!important; width: 20px!important;}\n" +
	"p{padding:0!important; margin: 0!important;}\n" +
	"span.bxed-noscript{color: #0000a0!important; padding: 2px!important; font-style:italic!important; font-size: 90%!important;}\n" +
	"span.bxed-noindex{color: #004000!important; padding: 2px!important; font-style:italic!important; font-size: 90%!important;}\n" +
	"img.bxed-flash{border: 1px solid #B6B6B8!important; background: url(" + this.imagePath + "flash.gif) #E2DFDA center center no-repeat !important;}\n" +
	"table{border: 1px solid #B6B6B8!important; border-collapse: collapse;}\n" +
	"table td{border: 1px solid #B6B6B8!important;}\n" +
	"img.bxed-video{border: 1px solid #B6B6B8!important; background-color: #E2DFDA!important; background-image: url(" + this.imagePath + "video.gif); background-position: center center!important; background-repeat:no-repeat!important;}\n" +
	"img.bxed-hr{padding: 2px!important; width: 100%!important; height: 2px!important;}\n";

	this.tabNbsp = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; // &nbsp; x 6
	this.tabNbspRe1 = new RegExp(String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160), 'ig'); //
	this.tabNbspRe2 = new RegExp(String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + ' ', 'ig'); //

},

OnMousedown: function(e)
{
	if (!e)
		e = window.event;

	if (BX.browser.IsOpera() && e.shiftKey)
	{
		//this.OnContextMenu(e);
		//BX.PreventDefault(e);
	}
},

OnClick: function(e)
{
	//if(!e)
	//	e = window.event;
	//if (this.arConfig.bArisingToolbar)
	//	this.ShowFloatToolbar(true);
},

OnDblClick: function(e)
{
	return;
},

OnContextMenu: function(e, pElement)
{
	return;
	var
		_this = this,
		oFramePos,
		x, y;
	if (!e) e = this.pEditorWindow.event;

	if(e.pageX || e.pageY)
	{
		x = e.pageX - this.pEditorDocument.body.scrollLeft;
		y = e.pageY - this.pEditorDocument.body.scrollTop;
	}
	else if(e.clientX || e.clientY)
	{
		x = e.clientX;
		y = e.clientY;
	}

	oFramePos = this.CACHE['frame_pos'];
	if (!oFramePos)
		this.CACHE['frame_pos'] = oFramePos = BX.pos(this.pEditCont);

	x += oFramePos.left;
	y += oFramePos.top;

	var targ;
	if (e.target)
		targ = e.target;
	else if (e.srcElement)
		targ = e.srcElement;
	if (targ.nodeType == 3) // defeat Safari bug
		targ = targ.parentNode;

	if (!targ || !targ.nodeName)
		return;
	var res = this.oContextMenu.Show({oPos: {left : x, top : y}, pElement: targ});

	return BX.PreventDefault(e);
},

OnKeyDown: function(e)
{
	if(!e) e = window.event;

	var key = e.which || e.keyCode;
	if (e.ctrlKey && !e.shiftKey && !e.altKey)
	{
		// if (!BX.browser.IsIE() && !BX.browser.IsOpera())
		// {
			switch (key)
			{
				case 66 : // B
				case 98 : // b
					this.executeCommand('Bold');
					return BX.PreventDefault(e);
				case 105 : // i
				case 73 : // I
					this.executeCommand('Italic');
					return BX.PreventDefault(e);
				case 117 : // u
				case 85 : // U
					this.executeCommand('Underline');
					return BX.PreventDefault(e);
				case 81 : // Q - quote
					if (this.quoteBut)
					{
						this.quoteBut.oBut.handler(this.quoteBut);
						return BX.PreventDefault(e);
					}
			}
		//}
	}



	// Shift +Del - Deleting code fragment in WYSIWYG
	if (this.bCodeBut && e.shiftKey && e.keyCode == 46 /* Del*/)
	{
		var pSel = this.GetSelectionObject();
		if (pSel)
		{
			if (pSel.className == 'lhe-code')
			{
				pSel.parentNode.removeChild(pSel);
				return BX.PreventDefault(e);
			}
			else if(pSel.parentNode)
			{
				var pCode = BX.findParent(pSel, {className: 'lhe-code'});
				if (pCode)
				{
					pCode.parentNode.removeChild(pCode);
					return BX.PreventDefault(e);
				}
			}
		}
	}

	// Tab
	if (key == 9 && this.arConfig.bReplaceTabToNbsp)
	{
		this.InsertHTML(this.tabNbsp);
		return BX.PreventDefault(e);
	}

	if (this.bCodeBut && e.keyCode == 13)
	{
		if (BX.browser.IsIE() || BX.browser.IsSafari())
		{
			var pElement = this.GetSelectionObject();
			if (pElement)
			{
				var bFind = false;
				if (pElement && pElement.nodeName && pElement.nodeName.toLowerCase() == 'pre')
					bFind = true;

				if (!bFind)
					bFind = !!BX.findParent(pElement, {tagName: 'pre'});

				if (bFind)
				{
					if (BX.browser.IsIE())
						this.InsertHTML("<br /><img src=\"" + this.oneGif + "\" height=\"20\" width=\"1\"/>");
					else if (BX.browser.IsSafari())
						this.InsertHTML(" \r\n");

					return BX.PreventDefault(e);
				}
			}
		}
	}

	// Ctrl + Enter
	if ((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey && this.ctrlEnterHandler)
	{
		this.SaveContent();
		this.ctrlEnterHandler();
	}

	if (this.arConfig.bAutoResize && this.arConfig.bResizable)
		this.AutoResize();
},

OnDocMousedown: function(e)
{
	if (!e)
		e = window.event;

	var pEl;
	if (e.target)
		pEl = e.target;
	else if (e.srcElement)
		pEl = e.srcElement;
	if (pEl.nodeType == 3)
		pEl = pEl.parentNode;

	if (!this.bPopup && !BX.findParent(pEl, {className: 'bxlhe-frame'}))
		this.SaveContent();
},

SetView: function(sType)
{
	if (this.sEditorMode == sType)
		return;

	this.SaveContent();
	if (sType == 'code')
	{
		this.iFrame.style.display = "none";
		this.pSourceDiv.style.display = "block";
		this.SetCodeEditorContent(this.GetContent());
	}
	else
	{
		this.iFrame.style.display = "block";
		this.pSourceDiv.style.display = "none";
		this.SetEditorContent(this.GetContent());
	}
	this.sEditorMode = sType;
	BX.onCustomEvent(this, "OnChangeView");
},

SaveContent: function()
{
	var sContent = this.sEditorMode == 'code' ? this.GetCodeEditorContent() : this.GetEditorContent();

	if (this.bBBCode)
		sContent = this.OptimizeBB(sContent);

	this.SetContent(sContent);

	// Todo: restore carret pos ?
},

SetContent: function(sContent)
{
	this.pHiddenInput.value = this.pTextarea.value = this.content = sContent;
},

GetContent: function()
{
	return this.content.toString();
},

SetEditorContent: function(sContent)
{
	var _this = this;
	sContent = this.ParseContent(sContent);

	if (this.pEditorDocument.designMode)
	{
		try{
			this.pEditorDocument.designMode = 'off';
		}catch(e){alert('SetEditorContent: designMode=\'off\'');}
	}

	this.pEditorDocument.open();
	this.pEditorDocument.write('<html><head></head><body>' + sContent + '</body></html>');
	this.pEditorDocument.close();

	this.pEditorDocument.body.style.padding = "8px";
	this.pEditorDocument.body.style.margin = "0";
	this.pEditorDocument.body.style.borderWidth = "0";

	// Set events
	BX.bind(this.pEditorDocument, 'keydown', BX.proxy(this.OnKeyDown, this));

	if(BX.browser.IsIE())
	{
		this.pEditorDocument.body.contentEditable = true;
	}
	else if (this.pEditorDocument.designMode)
	{
		this.pEditorDocument.designMode = "on";
		try{
		this.pEditorDocument.execCommand("styleWithCSS", false, false);
		this.pEditorDocument.execCommand("useCSS", false, true);
		}catch(e){}
	}

	if (this.arConfig.bConvertContentFromBBCodes)
		this.ShutdownBBCode();
},

GetEditorContent: function()
{
	var sContent = this.UnParseContent();
	return sContent;
},

SetCodeEditorContent: function(sContent)
{
	this.pHiddenInput.value = this.pTextarea.value = sContent;
},

GetCodeEditorContent: function()
{
	return this.pTextarea.value;
},

OptimizeHTML: function(str)
{
	var
		iter = 0,
		bReplasing = true,
		arTags = ['b', 'em', 'font', 'h\\d', 'i', 'li', 'ol', 'p', 'small', 'span', 'strong', 'u', 'ul'],
		replaceEmptyTags = function(){i--; bReplasing = true; return ' ';},
		re, tagName, i, l;

	while(iter++ < 20 && bReplasing)
	{
		bReplasing = false;
		for (i = 0, l = arTags.length; i < l; i++)
		{
			tagName = arTags[i];
			re = new RegExp('<'+tagName+'[^>]*?>\\s*?</'+tagName+'>', 'ig');
			str = str.replace(re, replaceEmptyTags);

			re = new RegExp('<' + tagName + '\\s+?[^>]*?/>', 'ig');
			str = str.replace(re, replaceEmptyTags);

			// Replace <b>text1</b>    <b>text2</b> ===>>  <b>text1 text2</b>
			re = new RegExp('<((' + tagName + '+?)(?:\\s+?[^>]*?)?)>([\\s\\S]+?)<\\/\\2>\\s*?<\\1>([\\s\\S]+?)<\\/\\2>', 'ig');
			str = str.replace(re, function(str, b1, b2, b3, b4)
				{
					bReplasing = true;
					return '<' + b1 + '>' + b3 + ' ' + b4 + '</' + b2 + '>';
				}
			);
		}
	}
	return str;
},

_RecursiveDomWalker: function(pNode, pParentNode)
{
	var oNode =
	{
		arAttributes : {},
		arNodes : [],
		type : null,
		text : "",
		arStyle : {}
	};

	switch(pNode.nodeType)
	{
		case 9:
			oNode.type = 'document';
			break;
		case 1:
			if(pNode.tagName.length <= 0 || pNode.tagName.substring(0, 1) == "/")
				return;

			oNode.text = pNode.tagName.toLowerCase();
			if (oNode.text == 'script')
				break;

			oNode.type = 'element';
			var
				attr = pNode.attributes,
				j, l = attr.length;

			for(j = 0; j < l; j++)
			{
				if(attr[j].specified || (oNode.text == "input" && attr[j].nodeName.toLowerCase()=="value"))
				{
					var attrName = attr[j].nodeName.toLowerCase();

					if(attrName == "style")
					{
						oNode.arAttributes[attrName] = pNode.style.cssText;
						oNode.arStyle = pNode.style;
					}
					else if(attrName=="src" || attrName=="href"  || attrName=="width"  || attrName=="height")
					{
						oNode.arAttributes[attrName] = pNode.getAttribute(attrName, 2);
					}
					else
					{
						oNode.arAttributes[attrName] = attr[j].nodeValue;
					}
				}
			}
			break;
		case 3:
			oNode.type = 'text';
			var res = pNode.nodeValue;

			if (this.arConfig.bReplaceTabToNbsp)
			{
				res = res.replace(this.tabNbspRe1, "\t");
				res = res.replace(this.tabNbspRe2, "\t");
			}

			if(!pParentNode || (pParentNode.text != 'pre' && pParentNode.arAttributes['class'] != 'lhe-code'))
			{
				res = res.replace(/\n+/g, ' ');
				res = res.replace(/ +/g, ' ');
			}

			oNode.text = res;
			break;
	}

	var arChilds = pNode.childNodes, i, l = arChilds.length;
	for(i = 0; i < l; i++)
		oNode.arNodes.push(this._RecursiveDomWalker(arChilds[i], oNode));

	return oNode;
},

_RecursiveGetHTML: function(pNode)
{
	if (!pNode || typeof pNode != 'object' || !pNode.arAttributes)
		return "";

	var ob, res = "", id = pNode.arAttributes["id"];
	if (id)
	{
		var bxTag = this.GetBxTag(id);
		if(bxTag.tag)
		{
			var parser = this.oSpecialParsers[bxTag.tag];
			if (parser && parser.UnParse)
				return parser.UnParse(bxTag, pNode, this);
			else if (bxTag.params && bxTag.params.value)
				return '\n' + bxTag.params.value + '\n';
			else
				return '';
		}
	}

	if (pNode.arAttributes["_moz_editor_bogus_node"])
		return '';

	if (this.bBBCode)
	{
		var bbRes = this.UnParseNodeBB(pNode);
		if (bbRes !== false)
			return bbRes;
	}

	bFormatted = true;

	if (pNode.text.toLowerCase() != 'body')
		res = this.GetNodeHTMLLeft(pNode);
	var bNewLine = false;

	var sIndent = '';
	if (typeof pNode.bFormatted != 'undefined')
		bFormatted = !!pNode.bFormatted;

	if (bFormatted && pNode.type != 'text')
	{
		if (this.reBlockElements.test(pNode.text) && !(pNode.oParent && pNode.oParent.text && pNode.oParent.text.toLowerCase() == 'pre'))
		{
			for (var j = 0; j < pNode.iLevel - 3; j++)
				sIndent += "  ";
			bNewLine = true;
			res = "\r\n" + sIndent + res;
		}
	}

	for (var i = 0; i < pNode.arNodes.length; i++)
		res += this._RecursiveGetHTML(pNode.arNodes[i]);

	if (pNode.text.toLowerCase() != 'body')
		res += this.GetNodeHTMLRight(pNode);

	if (bNewLine)
		res += "\r\n" + (sIndent == '' ? '' : sIndent.substr(2));

	return res;
},

// Redeclared in BBCode mode
GetNodeHTMLLeft: function(pNode)
{
	if(pNode.type == 'text')
	{
		var text = BX.util.htmlspecialchars(pNode.text);
		return text;
	}

	var atrVal, attrName, res;

	if(pNode.type == 'element')
	{
		res = "<" + pNode.text;
		for(attrName in pNode.arAttributes)
		{
			atrVal = pNode.arAttributes[attrName];
			if(attrName.substring(0,4).toLowerCase() == '_moz')
				continue;

			if(pNode.text.toUpperCase()=='BR' && attrName.toLowerCase() == 'type' && atrVal == '_moz')
				continue;
			if(attrName=='style' && atrVal.length<=0)
				continue;
			res += ' ' + attrName + '="' + (pNode.bDontUseSpecialchars ? atrVal : BX.util.htmlspecialchars(atrVal)) + '"';
		}
		if(pNode.arNodes.length <= 0 && !this.IsPairNode(pNode.text))
			return res + " />";
		return res + ">";
	}
	return "";
},

// Redeclared in BBCode mode
GetNodeHTMLRight: function(pNode)
{
	if(pNode.type == 'element' && (pNode.arNodes.length>0 || this.IsPairNode(pNode.text)))
		return "</" + pNode.text + ">";
	return "";
},

IsPairNode: function(text)
{
	if(text.substr(0, 1) == 'h' || text == 'br' || text == 'img' || text == 'input')
		return false;
	return true;
},

executeCommand: function(commandName, sValue)
{
	this.SetFocus();
	//try{
	var res = this.pEditorWindow.document.execCommand(commandName, false, sValue);
	//}catch(e){};
	this.SetFocus();
	//this.OnEvent("OnSelectionChange");
	//this.OnChange("executeCommand", commandName);

	return res;
},

queryCommand: function(commandName)
{
	var sValue = '';
	if (!this.pEditorDocument.queryCommandEnabled || !this.pEditorDocument.queryCommandValue)
		return null;

	if(!this.pEditorDocument.queryCommandEnabled(commandName))
		return null;

	return this.pEditorDocument.queryCommandValue(commandName);
},

SetFocus: function()
{
	if (this.sEditorMode != 'html')
		return;

	//try{
		if(this.pEditorWindow.focus)
			this.pEditorWindow.focus();
		else
			this.pEditorDocument.body.focus();
	//} catch(e){}
},

SetFocusToEnd: function()
{
	this.CheckBr();
	var ss = BX.GetWindowScrollSize(this.pEditorDocument);
	this.pEditorWindow.scrollTo(0, ss.scrollHeight);

	this.SetFocus();
	this.SelectElement(this.pEditorDocument.body.lastChild);
},

SetCursorFF: function()
{
	if (this.sEditorMode != 'code' && !BX.browser.IsIE())
	{
		var _this = this;
		try{
			this.iFrame.blur();
			this.iFrame.focus();

			setTimeout(function(){
				_this.iFrame.blur();
				_this.iFrame.focus();
			}, 600);

			setTimeout(function(){
				_this.iFrame.blur();
				_this.iFrame.focus();
			}, 1000);
		}catch(e){}
	}
},

CheckBr: function()
{
	var lastChild = this.pEditorDocument.body.lastChild;
	var count = 0;
	if (lastChild)
	{
		if (!lastChild.nodeName || lastChild.nodeName.toLowerCase() != "br")
			count++;
		// var preLastChild = lastChild.previousSibling;
		// if (!preLastChild || !preLastChild.nodeName || preLastChild.nodeName.toLowerCase() != "br")
			// count++;
	}
	else
	{
		count = 1;
	}

	for (var i = 0; i < count; i++)
		this.pEditorDocument.body.appendChild(this.pEditorDocument.createElement("BR"));
},

ParseContent: function(sContent, bJustParse) // HTML -> WYSIWYG
{
	var _this = this;

	//if (this.arConfig.bBBCode || true)
	//{
		var arCodes = [];
		sContent = sContent.replace(/\[code\]((?:\s|\S)*?)\[\/code\]/ig, function(str, code)
		{
			var strId = '';
			if (!_this.bBBCode)
				strId = " id=\"" + _this.SetBxTag(false, {tag: "code"}) + "\" ";

			arCodes.push('<pre ' + strId + 'class="lhe-code" title="' + LHE_MESS.CodeDel + '">' + BX.util.htmlspecialchars(code) + '</pre>');
			return '#BX_CODE' + (arCodes.length - 1) + '#';
		});
	//}

	if (!bJustParse)
		BX.onCustomEvent(this, 'OnParseContent');

	if (this.arConfig.bBBCode)
		sContent = this.ParseBB(sContent);

	sContent = sContent.replace(/(<td[^>]*>)\s*(<\/td>)/ig, "$1<br _moz_editor_bogus_node=\"on\">$2");

	if (this.arConfig.bReplaceTabToNbsp)
		sContent = sContent.replace(/\t/ig, this.tabNbsp);

	if (!BX.browser.IsIE())
	{
		sContent = sContent.replace(/<hr[^>]*>/ig, function(sContent)
			{
				return '<img class="bxed-hr" src="' + _this.imagePath + 'break_page.gif" id="' + _this.SetBxTag(false, {tag: "hr", params: {value : sContent}}) + '"/>';
			}
		);
	}

	for (var p in this.oSpecialParsers)
	{
		if (this.oSpecialParsers[p] && this.oSpecialParsers[p].Parse)
			sContent = this.oSpecialParsers[p].Parse(p, sContent, this);
	}

	if (!bJustParse)
		setTimeout(function(){_this.AppendCSS(_this.systemCSS);}, 300);

	//if (this.arConfig.bBBCode && arCodes.length > 0)
	if (arCodes.length > 0)
	{
		// Replace back CODE content without modifications
		sContent = sContent.replace(/#BX_CODE(\d+)#/ig, function(s, num){return arCodes[num] || s;});
	}

	sContent = BX.util.trim(sContent);
	return sContent;
},

UnParseContent: function() // WYSIWYG - > html
{
	BX.onCustomEvent(this, 'OnUnParseContent');
	var sContent = this._RecursiveGetHTML(this._RecursiveDomWalker(this.pEditorDocument.body, false));

	if (!BX.browser.IsIE())
		sContent = sContent.replace(/\r/ig, '');

	if (this.bBBCode)
	{
		sContent = BX.util.htmlspecialcharsback(sContent);

		// Handle [P] tags from IE
		sContent = sContent.replace(/(?:\n|\r|\s)*?\[\/P\](?:\n|\r|\s)*?\[P\](?:\n|\r|\s)*?\[\/P\]/ig, "\n"); // [/P] [P][/P]  ==> \n for opera

		sContent = sContent.replace(/\[P\](?:\n|\r|\s)*?\[\/P\]/ig, "\n"); // [P][/P]  ==> \n
		sContent = sContent.replace(/\[\/P\](?:\n|\r|\s)*?\[P\]/ig, "\n"); // [/P]    [P]  ==> \n
		sContent = sContent.replace(/^(?:\n|\r|\s)+?\[P\]((?:\s|\S)*?)$/ig, "$1"); //kill [P] in the begining
		sContent = sContent.replace(/\[P\]/ig, "");
		sContent = sContent.replace(/\n*\[\/P\]/ig, "\n");
		//sContent = sContent.replace(/(?:\n|\r|\s)*\[\/P\]/ig, "\n");
		sContent = sContent.replace(/\[\/P\]/ig, "\n");
		sContent = sContent.replace(/^((?:\s|\S)*?)(?:\n|\r|\s)+$/ig, "$1\n\n"); //kill multiple \n in the end

		// Handle  [DIV] tags from safari, chrome
		sContent = sContent.replace(/\[DIV\](?:\n|\r|\s)*?\[\/DIV\]/ig, "\n"); // [DIV][/DIV]  ==> \n
		sContent = sContent.replace(/\[\/DIV\](?:\n|\r|\s)*?\[DIV\]/ig, "\n"); // [/DIV]    [DIV]  ==> \n
		sContent = sContent.replace(/^(?:\n|\r|\s)+?\[DIV\]((?:\s|\S)*?)$/ig, "$1"); //kill [DIV] in the begining
		sContent = sContent.replace(/\[DIV\]/ig, "");
		sContent = sContent.replace(/\[\/DIV\]/ig, "\n");
	}
	else
	{
		// Handle <P> tags from IE
		sContent = sContent.replace(/<P>(?:\n|\r|\s)*?<\/P>/ig, "\n<br>\n"); // </P>    <P>  ==> <br />
		sContent = sContent.replace(/<\/P>(?:\n|\r|\s)*?<P>/ig, "\n<br>\n"); // </P>    <P>  ==> <br />
		sContent = sContent.replace(/^(?:\n|\r|\s)+?<P>((?:\s|\S)*?)$/ig, "$1"); //kill <P> in the begining
		sContent = sContent.replace(/<(\/)??P>/ig, "\n<br>\n"); // <P>|</P>  ==> <br />

		// Handle  <DIV> tags from safari, chrome
		sContent = sContent.replace(/<DIV>(?:\n|\r|\s)*?<\/DIV>/ig, "\n<br>\n"); // </DIV>    <DIV>  ==> <br />
		sContent = sContent.replace(/<\/DIV>(?:\n|\r|\s)*?<DIV>/ig, "\n<br>\n"); // </DIV>    <DIV>  ==> <br />
		sContent = sContent.replace(/^(?:\n|\r|\s)+?<DIV>((?:\s|\S)*?)$/ig, "$1"); //kill <DIV> in the begining
		sContent = sContent.replace(/<(\/)??DIV>/ig, "\n<br>\n"); // <P>|</P>  ==> <br />
	}

	this.__sContent = sContent;
	BX.onCustomEvent(this, 'OnUnParseContentAfter');
	sContent = this.__sContent;

	return sContent;
},

InitResizer: function()
{
	this.oTransOverlay.Show();

	var
		n = BX.browser.IsIE() && !BX.browser.IsDoctype() ? 0 : 2,
		_this = this,
		coreContPos = BX.pos(this.pFrame),
		newHeight = false;

	var MouseMove = function(e)
	{
		e = _this.MousePos(e);
		newHeight = e.realY - coreContPos.top;

		// New height
		if (newHeight < _this.arConfig.minHeight || newHeight > _this.arConfig.maxHeight)
		{
			document.body.style.cursor = "not-allowed";
		}
		else
		{
			document.body.style.cursor = "n-resize";
			_this.pFrame.style.height = newHeight + "px";
			_this.ResizeFrame(newHeight + n);
		}
	};

	var MouseUp = function(e)
	{
		BX.userOptions.save('fileman', 'LHESize_' + _this.id, 'height', newHeight);
		_this.arConfig.height = newHeight;

		document.body.style.cursor = "";
		if (_this.oTransOverlay && _this.oTransOverlay.bShowed)
			_this.oTransOverlay.Hide();

		BX.unbind(document, "mousemove", MouseMove);
		BX.unbind(document, "mouseup", MouseUp);
	};

	BX.bind(document, "mousemove", MouseMove);
	BX.bind(document, "mouseup", MouseUp);
},

AutoResize: function()
{
	var
		newHeight,
		_this = this;

	if (this.autoResizeTimeout)
		clearTimeout(this.autoResizeTimeout);

	this.autoResizeTimeout = setTimeout(function()
	{
		if (_this.sEditorMode == 'html')
		{
			newHeight = _this.pEditorDocument.body.offsetHeight + 80;
			var
				body = _this.pEditorDocument.body,
				offsetTop = false, i;

			if (!body.lastChild || !body.lastChild.offsetTop)
			{
				var pBogus = body.appendChild(BX.create("IMG", {props: {src: _this.oneGif}}));
				pBogus.setAttribute("_moz_editor_bogus_node", "on");
				offsetTop = pBogus.offsetTop;
			}
			else
			{
				offsetTop = body.lastChild.offsetTop;
			}

			if (offsetTop)
				newHeight = offsetTop + 150;
		}
		else
		{
			var rowsCount = _this.pTextarea.value.split("\n").length;
			newHeight = (rowsCount + 5) * 17;
		}

		var ws = BX.GetWindowInnerSize();
		if (newHeight > parseInt(_this.arConfig.height))
		{
			// 80% from screen height
			if (newHeight >= ws.innerHeight * 0.8)
				newHeight = Math.round(ws.innerHeight * 0.8);

			_this.SmoothResizeFrame(newHeight);
		}
	}, 300);
},

MousePos: function (e)
{
	if(window.event)
		e = window.event;

	if(e.pageX || e.pageY)
	{
		e.realX = e.pageX;
		e.realY = e.pageY;
	}
	else if(e.clientX || e.clientY)
	{
		e.realX = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
		e.realY = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
	}
	return e;
},

SmoothResizeFrame: function(height)
{
	var
		n = BX.browser.IsIE() && !BX.browser.IsDoctype() ? 0 : 2,
		_this = this,
		curHeight = parseInt(this.pFrame.offsetHeight) - n,
		count = 0,
		bRise = height > curHeight,
		timeInt = BX.browser.IsIE() ? 50 : 50,
		dy = 5;

	if (this.smoothResizeInterval)
		clearInterval(this.smoothResizeInterval);

	this.smoothResizeInterval = setInterval(function()
		{
			if (bRise)
			{
				curHeight += Math.round(dy * count);
				if (curHeight > height)
				{
					clearInterval(_this.smoothResizeInterval);
					if (curHeight > height)
						curHeight = height;
				}
			}
			else
			{
				curHeight -= Math.round(dy * count);
				if (curHeight < height)
				{
					curHeight = height;
					clearInterval(_this.smoothResizeInterval);
				}
			}

			_this.pFrame.style.height = curHeight + "px";
			_this.ResizeFrame(curHeight + n);
			count++;
		},
		timeInt
	);
},

ResizeFrame: function(newHeight)
{
	var
		n = BX.browser.IsIE() ? 1 : 0,
		resizeHeight = this.arConfig.bResizable ? 10 + n : 0, // resize row
		height = newHeight || parseInt(this.pFrame.offsetHeight);

	this.pFrameTable.style.height = height + 'px';
	var contHeight = height - this.buttonsHeight - resizeHeight - n - (BX.browser.IsIE() ? 0 : 2);

	if (contHeight > 0)
	{
		this.pEditCont.style.height = contHeight + 'px';
		this.pTextarea.style.height = contHeight + 'px';
	}

	this.pButtonsCell.style.height = this.buttonsHeight + 'px';

	if (this.arConfig.bResizable)
		this.pResizer.parentNode.style.height = resizeHeight + 'px';
},

AddButtons: function()
{
	var
		i, l, butId, grInd, arButtons,
		toolbarConfig = this.arConfig.toolbarConfig;
	this.buttonsCount = 0;

	if(!toolbarConfig)
		toolbarConfig = [
			//'Source',
			'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'InsertHR',
			'Anchor',
			'CreateLink', 'DeleteLink', 'Image', //'SpecialChar',
			'Justify',
			'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent',
			'BackColor', 'ForeColor',
			'Video',
			'StyleList', 'HeaderList',
			'FontList', 'FontSizeList',
			'Table'
			//smiles:['SmileList']
		];

	if (oBXLEditorUtils.oTune && oBXLEditorUtils.oTune[this.id])
	{
		var
			ripButtons = oBXLEditorUtils.oTune[this.id].ripButtons,
			addButtons = oBXLEditorUtils.oTune[this.id].buttons;

		if (ripButtons)
		{
			i = 0;
			while(i < toolbarConfig.length)
			{
				if (ripButtons[toolbarConfig[i]])
					toolbarConfig = BX.util.deleteFromArray(toolbarConfig, i);
				else
					i++;
			}
		}

		if (addButtons)
		{
			for (var j = 0, n = addButtons.length; j < n; j++)
			{
				if (addButtons[j].ind == -1 || addButtons[j].ind >= toolbarConfig.length)
					toolbarConfig.push(addButtons[j].but.id);
				else
					toolbarConfig = BX.util.insertIntoArray(toolbarConfig, addButtons[j].ind, addButtons[j].but.id);
			}
		}
	}

	var
		begWidth = 3,
		curLineWidth = begWidth, pCont,
		butContWidth = parseInt(this.pButtonsCont.offsetWidth);

	this.ToolbarStartLine(true);
	for(i in toolbarConfig)
	{
		butId = toolbarConfig[i];
		if (typeof butId != 'string')
			continue;

		if (butId == '=|=')
		{
			this.ToolbarNewLine();
			curLineWidth = begWidth;
		}
		else if (LHEButtons[butId])
		{
			pCont = this.AddButton(LHEButtons[butId], butId);
			if (pCont)
			{
				curLineWidth += parseInt(pCont.style.width) || 23;
				if (curLineWidth + 4 >= butContWidth && butContWidth > 0)
				{
					this.ToolbarNewLine();
					this.pButtonsCont.appendChild(pCont);
					curLineWidth = begWidth;
				}
			}
		}

	}
	this.ToolbarEndLine();

	this.buttonsHeight = this.toolbarLineCount * 25;

	this.arConfig.minHeight += this.buttonsHeight;
	this.arConfig.maxHeight += this.buttonsHeight;

	BX.addCustomEvent(this, 'onShow', BX.proxy(this.ResizeFrame, this));
},

AddButton: function(oBut, buttonId)
{
	//if (oBut.bBBHide && this.arConfig.bBBCode || (!this.arConfig.bBBCode && oBut.bBBShow))
	//	return;

	if (oBut.parser && oBut.parser.obj)
		this.oSpecialParsers[oBut.parser.name] = oBut.parser.obj;

	if (oBut.parsers)
	{
		for(var i = 0, cnt = oBut.parsers.length; i < cnt; i++)
			if (oBut.parsers[i] && oBut.parsers[i].obj)
				this.oSpecialParsers[oBut.parsers[i].name] = oBut.parsers[i].obj;
	}

	this.buttonsCount++;
	if (!oBut.type || !oBut.type == 'button')
	{
		if (buttonId == 'Code')
			this.bCodeBut = true;

		var pButton = new window.LHEButton(oBut, this);
		if (buttonId == 'Source')
			this.sourseBut = pButton;
		else if(buttonId == 'Quote')
			this.quoteBut = pButton;
		return this.pButtonsCont.appendChild(pButton.pCont);
	}
	else if (oBut.type == 'Colorpicker')
	{
		var pColorpicker = new window.LHEColorPicker(oBut, this);
		return this.pButtonsCont.appendChild(pColorpicker.pCont);
	}
	else if (oBut.type == 'List')
	{
		var pList = new window.LHEList(oBut, this);
		return this.pButtonsCont.appendChild(pList.pCont);
	}
},

ToolbarStartLine: function(bFirst)
{
	// Hack for IE 7
	if (!bFirst && BX.browser.IsIE())
		this.pButtonsCont.appendChild(BX.create("IMG", {props: {src: this.oneGif, className: "lhe-line-ie"}}));

	this.pButtonsCont.appendChild(BX.create("DIV", {props: {className: 'lhe-line-begin'}}));
},

ToolbarEndLine: function()
{
	this.pButtonsCont.appendChild(BX.create("DIV", {props: {className: 'lhe-line-end'}}));
},

ToolbarNewLine: function()
{
	this.toolbarLineCount++;
	this.ToolbarEndLine();
	this.ToolbarStartLine();
},

OpenDialog: function(arParams)
{
	var oDialog = new window.LHEDialog(arParams, this);
},

GetSelectionObject: function()
{
	var oSelection, oRange, root;
	if(this.pEditorDocument.selection) // IE
	{
		oSelection = this.pEditorDocument.selection;
		oRange = oSelection.createRange();

		if(oSelection.type=="Control")
			return oRange.commonParentElement();

		return oRange.parentElement();
	}
	else // FF
	{
		oSelection = this.pEditorWindow.getSelection();
		if(!oSelection)
			return false;

		var container, i, rangeCount = oSelection.rangeCount, obj;
		for(var i = 0; i < rangeCount; i++)
		{
			oRange = oSelection.getRangeAt(i);
			container = oRange.startContainer;
			if(container.nodeType != 3)
			{
				if(container.nodeType == 1 && container.childNodes.length <= 0)
					obj = container;
				else
					obj = container.childNodes[oRange.startOffset];
			}
			else
			{
				temp = oRange.commonAncestorContainer;
				while(temp && temp.nodeType == 3)
					temp = temp.parentNode;
				obj = temp;
			}
			root = (i == 0) ? obj : BXFindParentElement(root, obj);
		}
		return root;
	}
},

GetSelectionObjects: function()
{
	var oSelection;
	if(this.pEditorDocument.selection) // IE
	{
		oSelection = this.pEditorDocument.selection;
		var s = oSelection.createRange();

		if(oSelection.type=="Control")
			return s.commonParentElement();

		return s.parentElement();
	}
	else // FF
	{
		oSelection = this.pEditorWindow.getSelection();
		if(!oSelection)
			return false;
		var oRange;
		var container, temp;
		var res = [];
		for(var i = 0; i < oSelection.rangeCount; i++)
		{
			oRange = oSelection.getRangeAt(i);
			container = oRange.startContainer;
			if(container.nodeType != 3)
			{
				if(container.nodeType == 1 && container.childNodes.length <= 0)
					res[res.length] = container;
				else
					res[res.length] = container.childNodes[oRange.startOffset];
			}
			else
			{
				temp = oRange.commonAncestorContainer;
				while(temp && temp.nodeType == 3)
					temp = temp.parentNode;
				res[res.length] = temp;
			}
		}
		if(res.length > 1)
			return res;
		return res[0];
	}
},

GetSelectionRange: function(doc, win)
{
	try{
		var
			oDoc = doc || this.pEditorDocument,
			oWin = win || this.pEditorWindow,
			oRange,
			oSel = this.GetSelection(oDoc, oWin);

			if (oSel)
			{
				if (oDoc.createRange)
					oRange = oSel.getRangeAt(0);
				else
					oRange = oSel.createRange();
			}
			else
			{
				oRange = false;
			}

	} catch(e) {oRange = false;}

	return oRange;
},

SelectRange: function(oRange, doc, win)
{
	var
		oDoc = doc || this.pEditorDocument,
		oWin = win || this.pEditorWindow;

	this.ClearSelection(oDoc, oWin);
	if (oDoc.createRange) // FF
	{
		var oSel = oWin.getSelection();
		oSel.removeAllRanges();
		oSel.addRange(oRange);
	}
	else //IE
	{
		oRange.select();
	}
},

SelectElement: function(pElement)
{
	try{
	var
		oRange,
		oDoc = this.pEditorDocument,
		oWin = this.pEditorWindow;

	if(oWin.getSelection)
	{
		var oSel = oWin.getSelection();
		oSel.selectAllChildren(pElement);
		oRange = oSel.getRangeAt(0);
		if (oRange.selectNode)
			oRange.selectNode(pElement);
	}
	else
	{
		oDoc.selection.empty();
		oRange = oDoc.selection.createRange();
		oRange.moveToElementText(pElement);
		oRange.select();
	}
	return oRange;
	}catch(e){}
},

GetSelectedText: function(oRange)
{
	// Get selected text
	var selectedText = '';
	if (oRange.startContainer && oRange.endContainer) // DOM Model
	{
		if (oRange.startContainer == oRange.endContainer && (oRange.endContainer.nodeType == 3 || oRange.endContainer.nodeType == 1))
			selectedText = oRange.startContainer.textContent.substring(oRange.startOffset, oRange.endOffset);
	}
	else // IE
	{
		if (oRange.text == oRange.htmlText)
			selectedText = oRange.text;
	}
	return selectedText || '';
},

ClearSelection: function(doc, win)
{
	var
		oDoc = doc || this.pEditorDocument,
		oWin = win || this.pEditorWindow;

	if (oWin.getSelection)
		oWin.getSelection().removeAllRanges();
	else
		oDoc.selection.empty();
},

GetSelection: function(oDoc, oWin)
{
	if (!oDoc)
		oDoc = document;
	if (!oWin)
		oWin = window;

	var oSel = false;
	if (oWin.getSelection)
		oSel = oWin.getSelection();
	else if (oDoc.getSelection)
		oSel = oDoc.getSelection();
	else if (oDoc.selection)
		oSel = oDoc.selection;
	return oSel;
},

InsertHTML: function(sContent)
{
	//try{
	this.SetFocus();
		if(BX.browser.IsIE())
		{
			var oRng = this.pEditorDocument.selection.createRange();
			if (oRng.pasteHTML)
			{
				oRng.pasteHTML(sContent);
				oRng.collapse(false);
				oRng.select();
			}
		}
		else
		{
			this.pEditorWindow.document.execCommand('insertHTML', false, sContent);
		}
	//}catch(e){}
	//this.OnChange("insertHTML", "");
},

AppendCSS: function(styles)
{
	styles = BX.util.trim(styles);
	if (styles.length <= 0)
		return false;

	var
		pDoc = this.pEditorDocument,
		pHeads = pDoc.getElementsByTagName("HEAD");

	if(pHeads.length != 1)
		return false;

	if(BX.browser.IsIE())
	{
		setTimeout(function()
		{
			try{
				if (pDoc.styleSheets.length == 0)
					pHeads[0].appendChild(pDoc.createElement("STYLE"));
				pDoc.styleSheets[0].cssText += styles;
			}catch(e){}
		}, 100);
	}
	else
	{
		var xStyle = pDoc.createElement("STYLE");
		pHeads[0].appendChild(xStyle);
		xStyle.appendChild(pDoc.createTextNode(styles));
	}
	return true;
},

SetBxTag: function(pElement, params)
{
	var id;
	if (params.id || pElement && pElement.id)
		id = params.id || pElement.id;

	if (!id)
		id = 'bxid_' + Math.round(Math.random() * 1000000);
	else if (this.bxTags[id] && !params.tag)
		params.tag = this.bxTags[id].tag;

	params.id = id;
	if (pElement)
		pElement.id = params.id;

	this.bxTags[params.id] = params;
	return params.id;
},

GetBxTag: function(id)
{
	if (id)
	{
		if (typeof id != "string" && id.id)
			id = id.id;

		if (id && id.length > 0 && this.bxTags[id] && this.bxTags[id].tag)
		{
			this.bxTags[id].tag = this.bxTags[id].tag.toLowerCase();
			return this.bxTags[id];
		}
	}

	return {tag: false};
},

GetAttributesList: function(str)
{
	str = str + " ";

	var arParams = {}, arPHP = [], bPhp = false, _this = this;
	// 1. Replace PHP by #BXPHP#
	str = str.replace(/<\?.*?\?>/ig, function(s)
	{
		arPHP.push(s);
		return "#BXPHP" + (arPHP.length - 1) + "#";
	});

	// 2.0 Parse params - without quotes
	str = str.replace(/([^\w]??)(\w+?)=([^\s\'"]+?)(\s)/ig, function(s, b0, b1, b2, b3)
	{
		b2 = b2.replace(/#BXPHP(\d+)#/ig, function(s, num){return arPHP[num] || s;});
		arParams[b1.toLowerCase()] = BX.util.htmlspecialcharsback(b2);
		return b0;
	});

	// 2.1 Parse params
	str = str.replace(/([^\w]??)(\w+?)\s*=\s*("|\')([^\3]*?)\3/ig, function(s, b0, b1, b2, b3)
	{
		// 3. Replace PHP back
		b3 = b3.replace(/#BXPHP(\d+)#/ig, function(s, num){return arPHP[num] || s;});
		arParams[b1.toLowerCase()] = BX.util.htmlspecialcharsback(b3);
		return b0;
	});

	return arParams;
},

RidOfNode: function (pNode, bHard)
{
	if (!pNode || pNode.nodeType != 1)
		return;

	var i, nodeName = pNode.tagName.toLowerCase(),
	nodes = ['span', 'strike', 'del', 'font', 'code', 'div'];

	if (BX.util.in_array(nodeName, nodes)) // Check node names
	{
		if (bHard !== true)
		{
			for (i = pNode.attributes.length - 1; i >= 0; i--)
			{
				if (BX.util.trim(pNode.getAttribute(pNode.attributes[i].nodeName.toLowerCase())) != "")
					return false; // Node have attributes, so we cant get rid of it without loosing info
			}
		}

		var arNodes = pNode.childNodes;
		while(arNodes.length > 0)
			pNode.parentNode.insertBefore(arNodes[0], pNode);

		pNode.parentNode.removeChild(pNode);
		//this.OnEvent("OnSelectionChange");
		return true;
	}

	return false;
},

WrapSelectionWith: function (tagName, arAttributes)
{
	this.SetFocus();
	var oRange, oSelection;

	if (!tagName)
		tagName = 'SPAN';

	var sTag = 'FONT', i, pEl, arTags, arRes = [];

	try{this.pEditorDocument.execCommand("styleWithCSS", false, false);}catch(e){}
	this.executeCommand("FontName", "bitrixtemp");

	arTags = this.pEditorDocument.getElementsByTagName(sTag);

	for(i = arTags.length - 1; i >= 0; i--)
	{
		if (arTags[i].getAttribute('face') != 'bitrixtemp')
			continue;

		pEl = BX.create(tagName, arAttributes, this.pEditorDocument);
		arRes.push(pEl);

		while(arTags[i].firstChild)
			pEl.appendChild(arTags[i].firstChild);

		arTags[i].parentNode.insertBefore(pEl, arTags[i]);
		arTags[i].parentNode.removeChild(arTags[i]);
	}

	return arRes;
},

SaveSelectionRange: function()
{
	if (this.sEditorMode == 'code')
		this.oPrevRangeText = this.GetSelectionRange(document, window);
	else
		this.oPrevRange = this.GetSelectionRange();
},

RestoreSelectionRange: function()
{
	if (this.sEditorMode == 'code')
		this.IESetCarretPos(this.oPrevRangeText);
	else
		this.SelectRange(this.oPrevRange);
},

focus: function(el, bSelect)
{
	setTimeout(function()
	{
		try{
			el.focus();
			if(bSelect)
				el.select();
		}catch(e){}
	}, 100);
}
};

BXLEditorUtils = function()
{
	this.oTune = {};
	this.setCurrentEditorId('default');
};
BXLEditorUtils.prototype = {
	setCurrentEditorId: function(id)
	{
		this.curId = id;
	},

	prepare : function()
	{
		if (!this.oTune[this.curId])
			this.oTune[this.curId] =
			{
				buttons: [],
				ripButtons: {}
			};
	},

	addButton : function(pBut, ind)
	{
		if (!pBut || !pBut.id)
			return false;
		if (typeof ind == 'undefined')
			ind = -1;

		this.prepare();
		this.oTune[this.curId].buttons.push({but: pBut, ind: ind});

		return true;
	},

	removeButton: function(id)
	{
		this.prepare();
		this.oTune[this.curId].ripButtons[id] = true;
	}
};
oBXLEditorUtils = new BXLEditorUtils();

function BXFindParentElement(pElement1, pElement2)
{
	var p, arr1 = [], arr2 = [];
	while((pElement1 = pElement1.parentNode) != null)
		arr1[arr1.length] = pElement1;
	while((pElement2 = pElement2.parentNode) != null)
		arr2[arr2.length] = pElement2;

	var min, diff1 = 0, diff2 = 0;
	if(arr1.length<arr2.length)
	{
		min = arr1.length;
		diff2 = arr2.length - min;
	}
	else
	{
		min = arr2.length;
		diff1 = arr1.length - min;
	}

	for(var i=0; i<min-1; i++)
	{
		if(BXElementEqual(arr1[i+diff1], arr2[i+diff2]))
			return arr1[i+diff1];
	}
	return arr1[0];
}

window.BXFindParentByTagName = function (pElement, tagName)
{
	tagName = tagName.toUpperCase();
	while(pElement && (pElement.nodeType!=1 || pElement.tagName.toUpperCase() != tagName))
		pElement = pElement.parentNode;
	return pElement;
}


function SetAttr(pEl, attr, val)
{
	if(attr=='className' && !BX.browser.IsIE())
		attr = 'class';

	if(val.length <= 0)
		pEl.removeAttribute(attr);
	else
		pEl.setAttribute(attr, val);
}

function BXCutNode(pNode)
{
	while(pNode.childNodes.length > 0)
		pNode.parentNode.insertBefore(pNode.childNodes[0], pNode);

	pNode.parentNode.removeChild(pNode);
}

