JCLightHTMLEditor.prototype.InitBBCode = function()
{
	// this.bBBCode = this.arConfig.bBBCode;
	// this.bBBParseImageSize = this.arConfig.bBBParseImageSize;
	this.stack = [];

	var _this = this;

	// this.pTextarea.onfocus = function(){_this.bTextareaFocus = true;};
	// this.pTextarea.onblur = function(e){_this.bTextareaFocus = false;};

	this.pTextarea.onkeydown = BX.proxy(this.OnKeyDownBB, this);

	// Backup parser functions
	this._GetNodeHTMLLeft = this.GetNodeHTMLLeft;
	this._GetNodeHTMLRight = this.GetNodeHTMLRight;

	this.GetNodeHTMLLeft = this.GetNodeHTMLLeftBB;
	this.GetNodeHTMLRight = this.GetNodeHTMLRightBB;
};

JCLightHTMLEditor.prototype.ShutdownBBCode = function()
{
	this.bBBCode = false;
	this.arConfig.bBBCode = false;

	// this.pTextarea.onfocus = null;
	// this.pTextarea.onblur = null;
	this.pTextarea.onkeydown = null;

	// Restore parser functions
	this.GetNodeHTMLLeft = this._GetNodeHTMLLeft;
	this.GetNodeHTMLRight = this._GetNodeHTMLRight;

	this.arConfig.bConvertContentFromBBCodes = false;
};

JCLightHTMLEditor.prototype.FormatBB = function(params)
{
	var
		pBut = params.pBut,
		value = params.value,
		tag = params.tag.toUpperCase(),
		tag_end = tag;

	if (tag == 'FONT' || tag == 'COLOR' || tag == 'SIZE')
		tag += "=" + value;

	if ((!BX.util.in_array(tag, this.stack) || this.GetTextSelection()) && !(tag == 'FONT' && value == 'none'))
	{
		if (!this.WrapWith("[" + tag + "]", "[/" + tag_end + "]"))
		{
			this.stack.push(tag);

			if (pBut && pBut.Check)
				pBut.Check(true);
		}
	}
	else
	{
		var res = false;
		while (res = this.stack.pop())
		{
			this.WrapWith("[/" + res + "]", "");
			if (pBut && pBut.Check)
				pBut.Check(false);

			if (res == tag)
				break;
		}
	}
};

JCLightHTMLEditor.prototype.GetTextSelection = function()
{
	var res = false;
	if (typeof this.pTextarea.selectionStart != 'undefined')
	{
		res = this.pTextarea.value.substr(this.pTextarea.selectionStart, this.pTextarea.selectionEnd - this.pTextarea.selectionStart);
	}
	else if (document.selection && document.selection.createRange)
	{
		res = document.selection.createRange().text;
	}
	else if (window.getSelection)
	{
		res = window.getSelection();
		res = res.toString();
	}

	return res;
};

JCLightHTMLEditor.prototype.IESetCarretPos = function(oRange)
{
	if (!oRange || !BX.browser.IsIE() || oRange.text.length != 0 /* text selected*/)
		return;

	oRange.moveStart('character', - this.pTextarea.value.length);
	var pos = oRange.text.length;

	var range = this.pTextarea.createTextRange();
	range.collapse(true);
	range.moveEnd('character', pos);
	range.moveStart('character', pos);
	range.select();
};

JCLightHTMLEditor.prototype.WrapWith = function (tagBegin, tagEnd, postText)
{
	if (!tagBegin)
		tagBegin = "";
	if (!tagEnd)
		tagEnd = ""

	if (!postText)
		postText = "";

	if (tagBegin.length <= 0 && tagEnd.length <= 0 && postText.length <= 0)
		return true;

	var bReplaceText = !!postText;
	var sSelectionText = this.GetTextSelection();

	// TODO:
	if (!this.bTextareaFocus)
		this.pTextarea.focus(); // BUG IN IE

	var isSelect = (sSelectionText ? 'select' : bReplaceText ? 'after' : 'in');

	if (bReplaceText)
		postText = tagBegin + postText + tagEnd;
	else if (sSelectionText)
		postText = tagBegin + sSelectionText + tagEnd;
	else
		postText = tagBegin + tagEnd;

	if (typeof this.pTextarea.selectionStart != 'undefined')
	{
		var
			currentScroll = this.pTextarea.scrollTop,
			start = this.pTextarea.selectionStart,
			end = this.pTextarea.selectionEnd;

		this.pTextarea.value = this.pTextarea.value.substr(0, start) + postText + this.pTextarea.value.substr(end);

		if (isSelect == 'select')
		{
			this.pTextarea.selectionStart = start;
			this.pTextarea.selectionEnd = start + postText.length;
		}
		else if (isSelect == 'in')
		{
			this.pTextarea.selectionStart = this.pTextarea.selectionEnd = start + tagBegin.length;
		}
		else
		{
			this.pTextarea.selectionStart = this.pTextarea.selectionEnd = start + postText.length;
		}
		this.pTextarea.scrollTop = currentScroll;
	}
	else if (document.selection && document.selection.createRange)
	{
		var sel = document.selection.createRange();
		var selection_copy = sel.duplicate();
		postText = postText.replace(/\r?\n/g, '\n');
		sel.text = postText;
		sel.setEndPoint('StartToStart', selection_copy);
		sel.setEndPoint('EndToEnd', selection_copy);

		if (isSelect == 'select')
		{
			sel.collapse(true);
			postText = postText.replace(/\r\n/g, '1');
			sel.moveEnd('character', postText.length);
		}
		else if (isSelect == 'in')
		{
			sel.collapse(false);
			sel.moveEnd('character', tagBegin.length);
			sel.collapse(false);
		}
		else
		{
			sel.collapse(false);
			sel.moveEnd('character', postText.length);
			sel.collapse(false);
		}
		sel.select();
	}
	else
	{
		// failed - just stuff it at the end of the message
		this.pTextarea.value += postText;
	}
	return true;
};

JCLightHTMLEditor.prototype.ParseBB = function (sContent)  // BBCode -> WYSIWYG
{
	sContent = BX.util.htmlspecialchars(sContent);

	// Table
	sContent = sContent.replace(/[\r\n\s\t]?\[table\][\r\n\s\t]*?\[tr\]/ig, '[TABLE][TR]');
	sContent = sContent.replace(/\[tr\][\r\n\s\t]*?\[td\]/ig, '[TR][TD]');
	sContent = sContent.replace(/\[\/td\][\r\n\s\t]*?\[td\]/ig, '[/TD][TD]');
	sContent = sContent.replace(/\[\/tr\][\r\n\s\t]*?\[tr\]/ig, '[/TR][TR]');
	sContent = sContent.replace(/\[\/td\][\r\n\s\t]*?\[\/tr\]/ig, '[/TD][/TR]');
	sContent = sContent.replace(/\[\/tr\][\r\n\s\t]*?\[\/table\][\r\n\s\t]?/ig, '[/TR][/TABLE]');

	// List
	sContent = sContent.replace(/[\r\n\s\t]*?\[\/list\]/ig, '[/LIST]');
	sContent = sContent.replace(/[\r\n\s\t]*?\[\*\]?/ig, '[*]');

	var
		arSimpleTags = [
			'b','u', 'i', ['s', 'del'], // B, U, I, S
			'table', 'tr', 'td'//, // Table
		],
		bbTag, tag, i, l = arSimpleTags.length, re;

	for (i = 0; i < l; i++)
	{
		if (typeof arSimpleTags[i] == 'object')
		{
			bbTag = arSimpleTags[i][0];
			tag = arSimpleTags[i][1];
		}
		else
		{
			bbTag = tag = arSimpleTags[i];
		}

		sContent = sContent.replace(new RegExp('\\[(\\/?)' + bbTag + '\\]', 'ig'), "<$1" + tag + ">");
	}

	// Link
	sContent = sContent.replace(/\[url\]((?:\s|\S)*?)\[\/url\]/ig, "<a href=\"$1\">$1</a>");
	sContent = sContent.replace(/\[url=((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/url\]/ig, "<a href=\"$1\">$2</a>");

	// Img
	var _this = this;
	sContent = sContent.replace(/\[img(?:\s*?width=(\d+)\s*?height=(\d+))?\]((?:\s|\S)*?)\[\/img\]/ig,
		function(str, w, h, src)
		{
			var strSize = "";
			w = parseInt(w);
			h = parseInt(h);

			if (w && h && _this.bBBParseImageSize)
				strSize = ' width="' + w + '" height="' + h + '"';

			return '<img  src="' + src + '"' + strSize + '/>';
		}
	);

	// Font color
	i = 0;
	while (sContent.toLowerCase().indexOf('[color=') != -1 && sContent.toLowerCase().indexOf('[/color]') != -1 && i++ < 20)
		sContent = sContent.replace(/\[color=((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/color\]/ig, "<font color=\"$1\">$2</font>");

	// List
	i = 0;
	while (sContent.toLowerCase().indexOf('[list=') != -1 && sContent.toLowerCase().indexOf('[/list]') != -1 && i++ < 20)
		sContent = sContent.replace(/\[list=1\]((?:\s|\S)*?)\[\/list\]/ig, "<ol>$1</ol>");

	i = 0;
	while (sContent.toLowerCase().indexOf('[list') != -1 && sContent.toLowerCase().indexOf('[/list]') != -1 && i++ < 20)
		sContent = sContent.replace(/\[list\]((?:\s|\S)*?)\[\/list\]/ig, "<ul>$1</ul>");

	sContent = sContent.replace(/\[\*\]/ig, "<li>");

	// Font
	i = 0;
	while (sContent.toLowerCase().indexOf('[font=') != -1 && sContent.toLowerCase().indexOf('[/font]') != -1 && i++ < 20)
		sContent = sContent.replace(/\[font=((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/font\]/ig, "<font face=\"$1\">$2</font>");

	// Font size
	i = 0;
	while (sContent.toLowerCase().indexOf('[size=') != -1 && sContent.toLowerCase().indexOf('[/size]') != -1 && i++ < 20)
		sContent = sContent.replace(/\[size=((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/size\]/ig, "<font size=\"$1\">$2</font>");

	// Replace \n => <br/>
	sContent = sContent.replace(/\n/ig, "<br />");

	return sContent;
};

JCLightHTMLEditor.prototype.UnParseBB = function () // WYSIWYG -> BBCode
{
	// (!) Links, images unParsers are in le_toolbarbuttons.js
};

JCLightHTMLEditor.prototype.UnParseNodeBB = function (pNode) // WYSIWYG -> BBCode
{
	if (pNode.text == "br")
		return "\n";

	//[CODE] Handle code tag
	if (pNode.text == "pre" && pNode.arAttributes['class'] == 'lhe-code')
		return "[CODE]" + this.RecGetCodeContent(pNode) + "[/CODE]";

	pNode.bbHide = true;
	if (pNode.text == 'font' && pNode.arAttributes.color)
	{
		pNode.bbHide = false;
		pNode.text = 'color';
		pNode.bbValue = pNode.arAttributes.color;
	}
	else if (pNode.text == 'font' && pNode.arAttributes.size)
	{
		pNode.bbHide = false;
		pNode.text = 'size';
		pNode.bbValue = pNode.arAttributes.size;
	}
	else if (pNode.text == 'font' && pNode.arAttributes.face)
	{
		pNode.bbHide = false;
		pNode.text = 'font';
		pNode.bbValue = pNode.arAttributes.face;
	}
	else if(pNode.text == 'del')
	{
		pNode.bbHide = false;
		pNode.text = 's';
	}
	else if(pNode.text == 'strong' || pNode.text == 'b')
	{
		pNode.bbHide = false;
		pNode.text = 'b';
	}
	else if(pNode.text == 'em' || pNode.text == 'i')
	{
		pNode.bbHide = false;
		pNode.text = 'i';
	}
	else if(pNode.text == 'blockquote')
	{
		pNode.bbHide = false;
		pNode.text = 'quote';
	}
	else if(pNode.text == 'ol')
	{
		pNode.bbHide = false;
		pNode.text = 'list';
		pNode.bbBreakLineRight = true;
		pNode.bbValue = '1';
	}
	else if(pNode.text == 'ul')
	{
		pNode.bbHide = false;
		pNode.text = 'list';
		pNode.bbBreakLineRight = true;
	}
	else if(pNode.text == 'li')
	{
		pNode.bbHide = false;
		pNode.text = '*';
		pNode.bbBreakLine = true;
		pNode.bbHideRight = true;
	}
	else if(pNode.text == 'a')
	{
		pNode.bbHide = false;
		pNode.text = 'url';
		pNode.bbValue = pNode.arAttributes.href;
	}
	else if(BX.util.in_array(pNode.text, this.arBBTags))
	{
		pNode.bbHide = false;
	}

	return false;
};

JCLightHTMLEditor.prototype.RecGetCodeContent = function(pNode) // WYSIWYG -> BBCode
{
	if (!pNode || !pNode.arNodes || !pNode.arNodes.length)
		return '';

	var res = '';
	for (var i = 0; i < pNode.arNodes.length; i++)
	{
		if (pNode.arNodes[i].type == 'text')
			res += pNode.arNodes[i].text;
		else if (pNode.arNodes[i].type == 'element' && pNode.arNodes[i].text == "br")
			res += "\n";
		else if (pNode.arNodes[i].arNodes)
			res += this.RecGetCodeContent(pNode.arNodes[i]);
	}

	if (BX.browser.IsIE())
		res = res.replace(/\n/ig, "\r\n");

	return res;
};

JCLightHTMLEditor.prototype.GetNodeHTMLLeftBB = function (pNode)
{
	if(pNode.type == 'text')
	{
		var text = BX.util.htmlspecialchars(pNode.text);
		text = text.replace(/\[/ig, "&#91;");
		text = text.replace(/\]/ig, "&#93;");
		return text;
	}

	var res = "";
	if (pNode.bbBreakLine)
		res += "\n";

	if(pNode.type == 'element' && !pNode.bbHide)
	{
		res += "[" + pNode.text.toUpperCase();
		if (pNode.bbValue)
			res += '=' + pNode.bbValue;
		res += "]";
	}

	return res;
};

JCLightHTMLEditor.prototype.GetNodeHTMLRightBB = function (pNode)
{
	var res = "";
	if (pNode.bbBreakLineRight)
		res += "\n";

	if(pNode.type == 'element' && (pNode.arNodes.length > 0 || this.IsPairNode(pNode.text)) && !pNode.bbHide && !pNode.bbHideRight)
		res += "[/" + pNode.text.toUpperCase() + "]";

	return res;
};

JCLightHTMLEditor.prototype.OptimizeBB = function (str)
{
	//return str;
	// TODO: kill links without text and names
	// TODO: Kill multiple line ends

	var
		iter = 0,
		bReplasing = true,
		arTags = ['b', 'i', 'u', 's', 'color', 'font', 'size', 'quote'],
		replaceEmptyTags = function(){i--; bReplasing = true; return ' ';},
		re, tagName, i, l;

	while(iter++ < 20 && bReplasing)
	{
		bReplasing = false;
		for (i = 0, l = arTags.length; i < l; i++)
		{
			tagName = arTags[i];
			// Replace empties: [b][/b]  ==> ""
			re = new RegExp('\\[' + tagName + '[^\\]]*?\\]\\s*?\\[/' + tagName + '\\]', 'ig');
			str = str.replace(re, replaceEmptyTags);

			if (tagName !== 'quote')
			{
				re = new RegExp('\\[((' + tagName + '+?)(?:\\s+?[^\\]]*?)?)\\]([\\s\\S]+?)\\[\\/\\2\\](\\s*?)\\[\\1\\]([\\s\\S]+?)\\[\\/\\2\\]', 'ig');
				str = str.replace(re, function(str, b1, b2, b3, spacer, b4)
					{
						if (spacer.indexOf("\n") != -1)
							return str;
						bReplasing = true;
						return '[' + b1 + ']' + b3 + ' ' + b4 + '[/' + b2 + ']';
					}
				);

				// Replace [b]1 [b]2[/b] 3[/b] ===>>  [b]1 2 3[/b]
				re = new RegExp('(\\[' + tagName + '(?:\\s+?[^\\]]*?)?\\])([\\s\\S]+?)\\1([\\s\\S]+?)(\\[\\/' + tagName + '\\])([\\s\\S]+?)\\4', 'ig');
				str = str.replace(re, function(str, b1, b2, b3, b4, b5)
					{
						bReplasing = true;
						return b1 + b2 + b3 + b5 + b4;
					}
				);
			}
		}
	}

	//
	str = str.replace(/[\r\n\s\t]*?\[\/list\]/ig, "\n[/LIST]");

	return str;
}

JCLightHTMLEditor.prototype.RemoveFormatBB = function()
{
	var str = this.GetTextSelection();
	if (str)
	{
		var
			it = 0,
			arTags = ['b', 'i', 'u', 's', 'color', 'font', 'size'],
			i, l = arTags.length;

		//[b]123[/b]  ==> 123
		while (it < 30)
		{
			str1 = str;
			for (i = 0; i < l; i++)
				str = str.replace(new RegExp('\\[(' + arTags[i] + ')[^\\]]*?\\]([\\s\\S]*?)\\[/\\1\\]', 'ig'), "$2");

			if (str == str1)
				break;
			it++;
		}

		this.WrapWith('', '', str);
	}
};

JCLightHTMLEditor.prototype.OnKeyDownBB = function(e)
{
	if(!e) e = window.event;

	var key = e.which || e.keyCode;
	if (e.ctrlKey && !e.shiftKey && !e.altKey)
	{
		switch (key)
		{
			case 66 : // B
			case 98 : // b
				this.FormatBB({tag: 'B'});
				return BX.PreventDefault(e);
			case 105 : // i
			case 73 : // I
				this.FormatBB({tag: 'I'});
				return BX.PreventDefault(e);
			case 117 : // u
			case 85 : // U
				this.FormatBB({tag: 'U'});
				return BX.PreventDefault(e);
			case 81 : // Q - quote
				this.FormatBB({tag: 'QUOTE'});
				return BX.PreventDefault(e);
		}
	}

	// Tab
	if (key == 9)
	{
		this.WrapWith('', '', "\t");
		return BX.PreventDefault(e);
	}

	// Ctrl + Enter
	if ((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey && this.ctrlEnterHandler)
	{
		this.SaveContent();
		this.ctrlEnterHandler();
	}
};

JCLightHTMLEditor.prototype.GetCutHTML = function(e)
{
	if (this.curCutId)
	{
		var pCut = this.pEditorDocument.getElementById(this.curCutId);
		if (pCut)
		{
			pCut.parentNode.insertBefore(BX.create("BR", {}, this.pEditorDocument), pCut);
			pCut.parentNode.removeChild(pCut);
		}
	}

	this.curCutId = this.SetBxTag(false, {tag: "cut"});
	return '<img src="' + this.oneGif+ '" class="bxed-cut" id="' + this.curCutId + '" title="' + LHE_MESS.CutTitle + '"/>';
}
