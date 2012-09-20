if (!window.LHEButtons)
	LHEButtons = {};

LHEButtons['Source'] = {
	id : 'Source',
	width: 44,
	name : LHE_MESS.Source,
	OnBeforeCreate: function(pLEditor, pBut)
	{
		if (pLEditor.bBBCode && !pLEditor.arConfig.bConvertContentFromBBCodes)
		{
			pBut.id = 'SourceBB';
			pBut.name = pBut.title = LHE_MESS.BBSource;
		}
		pBut.title += ": " + LHE_MESS.Off;
		return pBut;
	},
	handler : function(pBut)
	{
		var bHtml = pBut.pLEditor.sEditorMode == 'html';
		pBut.pWnd.title = pBut.oBut.name + ": " + (bHtml ? LHE_MESS.On : LHE_MESS.Off);
		pBut.pLEditor.SetView(bHtml ? 'code' : 'html');
		pBut.Check(bHtml);
	}
};

// BASE
LHEButtons['Anchor'] = {
	id: 'Anchor',
	name: LHE_MESS.Anchor,
	bBBHide: true,
	handler: function(pBut)
	{
		pBut.pLEditor.OpenDialog({ id: 'Anchor'});
	},
	parser:
	{
		name: "anchor",
		obj: {
			Parse: function(sName, sContent, pLEditor)
			{
				return sContent.replace(
					/<a(\s[\s\S]*?)(?:>\s*?<\/a)?(?:\/?)?>/ig,
					function(sContent)
					{
						if(sContent.toLowerCase().indexOf("href") > 0)
							return sContent;

						var id = pLEditor.SetBxTag(false, {tag: "anchor", params: {value : sContent}});
						return '<img id="' + id + '" src="' + pLEditor.oneGif + '" class="bxed-anchor" />';
					}
				);
			},
			UnParse: false
		}
	}
};

LHEButtons['CreateLink'] = {
	id : 'CreateLink',
	name : LHE_MESS.CreateLink,
	name_edit : LHE_MESS.EditLink,
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	handler : function (pBut)
	{
		var p = (pBut.arSelectedElement && pBut.arSelectedElement['A']) ? pBut.arSelectedElement['A'] : pBut.pLEditor.GetSelectionObject();
		pBut.pLEditor.OpenDialog({id : 'Link', obj: p, bCM: !!pBut.menu});
	},
	parser: {
		name: "a",
		obj: {
			Parse: function(sName, sContent, pLEditor)
			{
				// Link
				return sContent.replace(
					/(<noindex>)*?<a([\s\S]*?(?:.*?[^\?]{1})??)(>[\s\S]*?<\/a>)(<\/noindex>)*/ig,
					function(str, s0, s1, s2, s3)
					{
						var arParams = pLEditor.GetAttributesList(s1), i , val, res = "", bPhp = false;
						if (s0 && s3 && s0.toLowerCase().indexOf('noindex') != -1 && s3.toLowerCase().indexOf('noindex') != -1)
						{
							arParams.noindex = true;
							arParams.rel = "nofollow";
						}

						res = "<a id=\"" + pLEditor.SetBxTag(false, {tag: 'a', params: arParams}) + "\" ";
						for (i in arParams)
						{
							if (typeof arParams[i] == 'string' && i != 'id' && i != 'noindex')
							{
								res += i + '="' + BX.util.htmlspecialchars(arParams[i]) + '" ';
							}
						}
						res += s2;
						return res;
					}
				);
			},
			UnParse: function(bxTag, pNode, pLEditor)
			{
				if (!bxTag.params)
					return '';

				var i, res = '<a ';

				// Only for BBCodes
				if (pLEditor.bBBCode)
				{
					var innerHtml = "";
					for(i = 0; i < pNode.arNodes.length; i++)
						innerHtml += pLEditor._RecursiveGetHTML(pNode.arNodes[i]);

					if (BX.util.trim(innerHtml) == BX.util.trim(bxTag.params.href))
						res = "[url]" + bxTag.params.href + "[/url]";
					else
						res = "[url=" + bxTag.params.href + "]" + innerHtml + "[/url]";

					return res;
				}

				bxTag.params['class'] = pNode.arAttributes['class'] ||'';
				for (i in bxTag.params)
					if (bxTag.params[i] && i != 'noindex')
						res += i + '="' + BX.util.htmlspecialchars(bxTag.params[i]) + '" ';

				res += '>';

				for(i = 0; i < pNode.arNodes.length; i++)
					res += pLEditor._RecursiveGetHTML(pNode.arNodes[i]);

				res += '</a>';

				if (bxTag.params.noindex)
					res = '<noindex>' + res + '</noindex>';

				return res;
			}
		}
	}
};

LHEButtons['DeleteLink'] = {
	id : 'DeleteLink',
	name : LHE_MESS.DeleteLink,
	cmd : 'Unlink',
	disableOnCodeView: true,
	handler : function(pBut)
	{
		var p = (pBut.arSelectedElement && pBut.arSelectedElement['A']) ? pBut.arSelectedElement['A'] : pBut.pLEditor.GetSelectionObject();
		if(p && p.tagName != 'A')
			p = BX.findParent(pBut.pLEditor.GetSelectionObject(), {tagName: 'A'});

		if (BX.browser.IsIE() && !p)
		{
			var oRange = pBut.pLEditor.GetSelectionRange();
			if (pBut.pLEditor.GetSelectedText(oRange) == '')
			{
				pBut.pLEditor.InsertHTML('<img id="bx_lhe_temp_bogus_node" src="' + pBut.pLEditor.oneGif + '" _moz_editor_bogus_node="on" style="border: 0px !important;"/>');
				var bogusImg = pBut.pLEditor.pEditorDocument.getElementById('bx_lhe_temp_bogus_node');
				if (bogusImg)
				{
					p = BX.findParent(bogusImg, {tagName: 'A'});
					bogusImg.parentNode.removeChild(bogusImg);
				}
			}
		}

		if (p)
		{
			if (!BX.browser.IsIE())
				pBut.pLEditor.SelectElement(p);
			pBut.pLEditor.executeCommand('Unlink');
		}
	}
};

LHEButtons['Image'] = {
	id : 'Image',
	name : LHE_MESS.Image,
	name_edit : LHE_MESS.EditImage,
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	handler : function (pBut)
	{
		var p = (pBut.arSelectedElement && pBut.arSelectedElement['IMG']) ? pBut.arSelectedElement['IMG'] : pBut.pLEditor.GetSelectionObject();
		if (!p || p.tagName != 'IMG')
			p = false;
		pBut.pLEditor.OpenDialog({id : 'Image', obj: p});
	},
	parser: {
		name: "img",
		obj: {
			Parse: function(sName, sContent, pLEditor)
			{
				// Image
				return sContent.replace(
					/<img([\s\S]*?(?:.*?[^\?]{1})??)>/ig,
					function(str, s1)
					{
						var arParams = pLEditor.GetAttributesList(s1), i , val, res = "", bPhp = false;
						if (arParams && arParams.id)
						{
							var oTag = pLEditor.GetBxTag(arParams.id);
							if (oTag.tag)
								return str;
						}

						res = "<img id=\"" + pLEditor.SetBxTag(false, {tag: 'img', params: arParams}) + "\" ";
						for (i in arParams)
						{
							if (typeof arParams[i] == 'string' && i != 'id')
								res += i + '="' + BX.util.htmlspecialchars(arParams[i]) + '" ';
						}
						res += " />";
						return res;
					}
				);
			},
			UnParse: function(bxTag, pNode, pLEditor)
			{
				if (!bxTag.params)
					return '';

				// width, height
				var
					w = parseInt(pNode.arStyle.width) || parseInt(pNode.arAttributes.width),
					h = parseInt(pNode.arStyle.height) || parseInt(pNode.arAttributes.height);

				if (pLEditor.bBBCode)
				{
					var strSize = (w && h && pLEditor.bBBParseImageSize) ? ' WIDTH=' + w + ' HEIGHT=' + h : '';
					return res = "[IMG" + strSize + "]" + bxTag.params.src + "[/IMG]";
				}

				if (w && !isNaN(w))
					bxTag.params.width = w;
				if (h && !isNaN(h))
					bxTag.params.height = h;

				bxTag.params['class'] = pNode.arAttributes['class'] ||'';

				var i, res = '<img ';
				for (i in bxTag.params)
					if (bxTag.params[i])
						res += i + '="' + BX.util.htmlspecialchars(bxTag.params[i]) + '" ';

				res += ' />';

				return res;
			}
		}
	}
};

// LHEButtons['SpecialChar'] = {
	// id : 'SpecialChar',
	// name : LHE_MESS.SpecialChar,
	// handler : function (pBut) {pBut.pLEditor.OpenDialog({id : 'SpecialChar'});}
// };

LHEButtons['Bold'] =
{
	id : 'Bold',
	name : LHE_MESS.Bold + " (Ctrl + B)",
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	cmd : 'Bold',
	bbHandler: function(pBut)
	{
		pBut.pLEditor.FormatBB({tag: 'B', pBut: pBut});
	}
};

LHEButtons['Italic'] =
{
	id : 'Italic',
	name : LHE_MESS.Italic + " (Ctrl + I)",
	cmd : 'Italic',
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	bbHandler: function(pBut)
	{
		pBut.pLEditor.FormatBB({tag: 'I', pBut: pBut});
	}
};

LHEButtons['Underline'] =
{
	id : 'Underline',
	name : LHE_MESS.Underline + " (Ctrl + U)",
	cmd : 'Underline',
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	bbHandler: function(pBut)
	{
		pBut.pLEditor.FormatBB({tag: 'U', pBut: pBut});
	}
};
LHEButtons['RemoveFormat'] =
{
	id : 'RemoveFormat',
	name : LHE_MESS.RemoveFormat,
	//cmd : 'RemoveFormat',
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	handler : function (pBut)
	{
		pBut.pLEditor.executeCommand('RemoveFormat');

		var
			pElement = pBut.pLEditor.GetSelectionObject(),
			i, arNodes = [];

		if (pElement)
		{
			var arNodes = BX.findChildren(pElement, {tagName: 'del'}, true);
			if (!arNodes || !arNodes.length)
				arNodes = [];

			var pPar = BX.findParent(pElement, {tagName: 'del'});
			if (pPar)
				arNodes.push(pPar);

			if (pElement.nodeName && pElement.nodeName.toLowerCase() == 'del')
				arNodes.push(pElement);
		}

		if (arNodes && arNodes.length > 0)
		{
			for (i = 0; i < arNodes.length; i++)
			{
				arNodes[i].style.textDecoration = "";
				pBut.pLEditor.RidOfNode(arNodes[i], true);
			}
		}
	},
	bbHandler: function(pBut)
	{
		pBut.pLEditor.RemoveFormatBB();
	}
};

LHEButtons['Strike'] = {
	id : 'Strike',
	name : LHE_MESS.Strike,
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	handler : function (pBut)
	{
		var
			pElement = pBut.pLEditor.GetSelectionObject(),
			arNodes = [];

		if (pElement)
		{
			var arNodes = BX.findChildren(pElement, {tagName: 'del'}, true);
			if (!arNodes || !arNodes.length)
				arNodes = [];

			var pPar = BX.findParent(pElement, {tagName: 'del'});
			if (pPar)
				arNodes.push(pPar);

			if (pElement.nodeName && pElement.nodeName.toLowerCase() == 'del')
				arNodes.push(pElement);
		}

		if (arNodes && arNodes.length > 0)
		{
			for (var i = 0; i < arNodes.length; i++)
			{
				arNodes[i].style.textDecoration = "";
				pBut.pLEditor.RidOfNode(arNodes[i], true);
			}
			pBut.Check(false);
		}
		else
		{
			pBut.pLEditor.WrapSelectionWith("del");
			//this.pMainObj.OnEvent("OnSelectionChange");
		}
	},
	OnSelectionChange: function () // ????
	{
		var
			pElement = this.pMainObj.GetSelectedNode(true),
			bFind = false, st;

		while(!bFind)
		{
			if (!pElement)
				break;

			if (pElement.nodeType == 1 && (BX.style(pElement, 'text-decoration', null) == "line-through" || pElement.nodeName.toLowerCase() == 'strike'))
			{
				bFind = true;
				break;
			}
			else
				pElement = pElement.parentNode;
		}

		pBut.Check(bFind);
	},
	bbHandler: function(pBut)
	{
		pBut.pLEditor.FormatBB({tag: 'S', pBut: pBut});
	}
};

LHEButtons['Quote'] = {
	id : 'Quote',
	name : LHE_MESS.Quote + " (Ctrl + Q)",
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;

		pLEditor.systemCSS += "blockquote.bx-quote {border: 1px solid #C0C0C0!important; background: #fff4ca url(" + pLEditor.imagePath + "font_quote.gif) left top no-repeat; padding: 4px 4px 4px 24px; color: #373737!important;}\n";
		return pBut;
	},
	handler: function(pBut)
	{
		if (pBut.pLEditor.arConfig.bQuoteFromSelection)
		{
			if (document.selection && document.selection.createRange)
				res = document.selection.createRange().text;
			else if (window.getSelection)
				res = window.getSelection().toString();

			var strId = '';
			if (!pBut.pLEditor.bBBCode)
				strId = " id\"=" + pBut.pLEditor.SetBxTag(false, {tag: "quote"}) + "\"";

			if (res && res.length > 0)
				return pBut.pLEditor.InsertHTML('<blockquote class="bx-quote"' + strId + ">" + res + "</blockquote>");
		}

		// Catch all blockquotes
		var
			arBQ = pBut.pLEditor.pEditorDocument.getElementsByTagName("blockquote"),
			i, l = arBQ.length;

		// Set specific name to nodes
		for (i = 0; i < l; i++)
			arBQ[i].name = "__bx_temp_quote";

		// Create new qoute
		pBut.pLEditor.executeCommand('Indent');

		// Search for created node and try to adjust new style end id
		setTimeout(function(){
			var
				arNewBQ = pBut.pLEditor.pEditorDocument.getElementsByTagName("blockquote"),
				i, l = arNewBQ.length;

			for (i = 0; i < l; i++)
			{
				if (arBQ[i].name == "__bx_temp_quote")
				{
					arBQ[i].removeAttribute("name");
				}
				else
				{
					arBQ[i].className = "bx-quote";
					arBQ[i].id = pBut.pLEditor.SetBxTag(false, {tag: "quote"});
				}
			}
		}, 10);
	},
	bbHandler: function(pBut)
	{
		if (pBut.pLEditor.arConfig.bQuoteFromSelection)
		{
			if (document.selection && document.selection.createRange)
				res = document.selection.createRange().text;
			else if (window.getSelection)
				res = window.getSelection().toString();

			if (res && res.length > 0)
				return pBut.pLEditor.WrapWith('[QUOTE]', '[/QUOTE]', res);
		}

		pBut.pLEditor.FormatBB({tag: 'QUOTE', pBut: pBut});
	},
	parser: {
		name: 'quote',
		obj: {
			Parse: function(sName, sContent, pLEditor)
			{
				sContent = sContent.replace(/\[quote\]/ig, '<blockquote class="bx-quote" id="' + pLEditor.SetBxTag(false, {tag: "quote"}) + '">');
				sContent = sContent.replace(/\[\/quote\]/ig, '</blockquote>');
				return sContent;
			},
			UnParse: function(bxTag, pNode, pLEditor)
			{
				if (bxTag.tag == 'quote')
				{
					var i, l = pNode.arNodes.length, res = "[QUOTE]";
					for (i = 0; i < l; i++)
						res += pLEditor._RecursiveGetHTML(pNode.arNodes[i]);
					res += "[/QUOTE]";
					return res;
				}
				return "";
			}
		}
	}
};

LHEButtons['Code'] = {
	id : 'Code',
	name : LHE_MESS.InsertCode,
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;

		pLEditor.systemCSS += ".lhe-code{border: 1px solid #C0C0C0!important; white-space: pre!important; padding: 5px!important; display: block;}\n .lhe-code *, .lhe-code{background: #eaeaea!important; color: #000080!important; font-weight: normal!important; line-height: normal!important; text-decoration: none!important; font-size: 11px!important;font-family:Verdana!important;}";
		return pBut;
	},
	handler : function(pBut)
	{
		var arProps = {className: "lhe-code", title: LHE_MESS.CodeDel};
		if (!pBut.pLEditor.bBBCode)
			arProps.id = pBut.pLEditor.SetBxTag(false, {tag: "code"});

		var arEl =  pBut.pLEditor.WrapSelectionWith("pre", {props: arProps});
		if (arEl && arEl.length > 0)
		{
			var
				firstEl = arEl[0],
				lastEl = arEl[arEl.length - 1];

			if (firstEl)
				firstEl.parentNode.insertBefore(BX.create("BR", {}, pBut.pLEditor.pEditorDocument), firstEl);

			if (lastEl && lastEl.parentNode)
			{
				var pBr = BX.create("BR", {}, pBut.pLEditor.pEditorDocument);
				if (lastEl.nextSibling)
					lastEl.parentNode.insertBefore(pBr, lastEl.nextSibling);
				else
					lastEl.parentNode.appendChild(pBr);
			}
		}
		else
		{
			var strId = '';

			if (!pBut.pLEditor.bBBCode)
				strId = "id=\"" + pBut.pLEditor.SetBxTag(false, {tag: "code"}) + "\" ";

			pBut.pLEditor.InsertHTML('<br/><pre ' + strId + 'class="lhe-code" title="' + LHE_MESS.CodeDel + '"><br id="lhe_bogus_code_br"/> </pre><br/>');
			setTimeout(
				function()
				{
					var br = pBut.pLEditor.pEditorDocument.getElementById('lhe_bogus_code_br');
					if (br)
						pBut.pLEditor.SelectElement(br);
				},
				100
			);
		}
	},
	bbHandler: function(pBut)
	{
		pBut.pLEditor.FormatBB({tag: 'CODE', pBut: pBut});
	},
	parser: {
		name: 'code',
		obj: {
			UnParse: function(bxTag, pNode, pLEditor)
			{
				if (bxTag.tag == 'code')
					return pLEditor.UnParseNodeBB(pNode);
				return "";
			}
		}
	}
};

LHEButtons['InsertCut'] =
{
	id : 'InsertCut',
	name : LHE_MESS.InsertCut,
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;

		pLEditor.systemCSS += "img.bxed-cut {margin: 2px; width: 100%; height: 12px; background: transparent url(" + pLEditor.imagePath + "cut.gif) left top repeat-x;}\n";
		return pBut;
	},
	handler: function(pBut)
	{
		pBut.pLEditor.InsertHTML(pBut.pLEditor.GetCutHTML());
	},
	bbHandler: function(pBut)
	{
		// Todo: check if already exist
		pBut.pLEditor.WrapWith('', '', '[CUT]');
	},
	parser: {
		name: 'cut',
		obj: {
			Parse: function(sName, sContent, pLEditor)
			{
				return sContent.replace(/\[CUT\]/ig, pLEditor.GetCutHTML());
			},
			UnParse: function(bxTag, pNode, pLEditor)
			{
				if (bxTag.tag == 'cut')
					return "[CUT]";
				return "";
			}
		}
	}
};
LHEButtons['Translit'] = {id : 'Translit', name : LHE_MESS.Translit, cmd : 'none'};

// Grouped buttons
LHEButtons['JustifyLeft'] =
LHEButtons['Justify'] =
{
	id : 'JustifyLeft_L',
	name : LHE_MESS.ImgAlign + ": " + LHE_MESS.JustifyLeft,
	type: 'List',
	OnAfterCreate: function(pLEditor, pList)
	{
		pList.arJustifyInd = {justifyleft: 0, justifycenter: 1, justifyright: 2, justifyfull: 3};
		pList.arJustify = [
			{id : 'JustifyLeft', name : LHE_MESS.JustifyLeft, cmd : 'JustifyLeft'},
			{id : 'JustifyCenter', name : LHE_MESS.JustifyCenter, cmd : 'JustifyCenter'},
			{id : 'JustifyRight', name : LHE_MESS.JustifyRight, cmd : 'JustifyRight'},
			{id : 'JustifyFull', name : LHE_MESS.JustifyFull, cmd : 'JustifyFull'}
		];

		var l = pList.arJustify.length, i;

		// Create popup
		BX.addClass(pList.pValuesCont, "lhe-justify-list");
		pList.pPopupTbl = pList.pValuesCont.appendChild(BX.create("TABLE", {props: {className: 'lhe-smiles-cont lhe-justify-cont '}}));

		for (i = 0; i < l; i++)
		{
			pList.arJustify[i].pIcon = pList.pPopupTbl.insertRow(-1).insertCell(-1).appendChild(BX.create("IMG", {props: {
				id: "lhe_btn_" + pList.arJustify[i].id.toLowerCase(),
				src: pList.pLEditor.oneGif,
				className: "lhe-button",
				title: pList.arJustify[i].name
			}}));

			pList.arJustify[i].pIcon.onmouseover = function(){BX.addClass(this, "lhe-tlbr-just-over");};
			pList.arJustify[i].pIcon.onmouseout = function(){BX.removeClass(this, "lhe-tlbr-just-over");};
			pList.arJustify[i].pIcon.onmousedown = function()
			{
				pList.pLEditor.SelectRange(pList.pLEditor.oPrevRange);
				var ind = pList.arJustifyInd[this.id.substr("lhe_btn_".length)];
				pList.oBut.SetJustify(pList.arJustify[ind], pList);
			};
		}
	},
	SetJustify: function(Justify, pList)
	{
		// 1. Set icon
		pList.pWnd.id = "lhe_btn_" + Justify.id.toLowerCase() + "_l";
		pList.pWnd.title = LHE_MESS.ImgAlign + ": " + Justify.name;

		// 2. Set selected
		pList.selected = Justify;

		// Exec command
		if(pList.pLEditor.sEditorMode != 'code')
			pList.pLEditor.executeCommand(Justify.cmd);

		// Close
		if (pList.bOpened)
			pList.Close();
	}
};

LHEButtons['InsertOrderedList'] =
{
	id : 'InsertOrderedList',
	name : LHE_MESS.OrderedList,
	cmd : 'InsertOrderedList',
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	bbHandler: function(pBut)
	{
		pBut.pLEditor.OpenDialog({id: 'List', obj: false, bOrdered: true, bEnterClose: false});
	}
};
LHEButtons['InsertUnorderedList'] =
{
	id : 'InsertUnorderedList',
	name : LHE_MESS.UnorderedList,
	cmd : 'InsertUnorderedList',
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	bbHandler: function(pBut)
	{
		pBut.pLEditor.OpenDialog({ id: 'List', obj: false, bOrdered: false, bEnterClose: false});
	}
};

LHEButtons['Outdent'] = {id : 'Outdent', name : LHE_MESS.Outdent, cmd : 'Outdent', bBBHide: true};
LHEButtons['Indent'] = {id : 'Indent', name : LHE_MESS.Indent, cmd : 'Indent', bBBHide: true};

LHEButtons['Video'] = {
	id: 'Video',
	name: LHE_MESS.InsertVideo,
	name_edit: LHE_MESS.EditVideo,
	handler: function(pBut)
	{
		pBut.pLEditor.OpenDialog({ id: 'Video', obj: false});
	},
	parser:
	{
		name: "video",
		obj:
		{
			Parse: function(sName, sContent, pLEditor)
			{
				// **** Parse WMV ****
				// b1, b3 - quotes
				// b2 - id of the div
				// b4 - javascript config
				var ReplaceWMV = function(str, b1, b2, b3, b4)
				{
					var
						id = b2,
						JSConfig, w, h, prPath, bgimg = '';

					try {eval('JSConfig = ' + b4); } catch (e) { JSConfig = false; }
					if (!id || !JSConfig)
						return '';

					w = (parseInt(JSConfig.width) || 50) + 'px';
					h = (parseInt(JSConfig.height) || 25) + 'px';

					if (JSConfig.image)
						bgimg = 'background-image: url(' + JSConfig.image + ')!important; ';

					return '<img class="bxed-video" id="' + pLEditor.SetBxTag(false, {tag: 'video', params: {id: id, JSConfig: JSConfig}}) + '" src="' + pLEditor.oneGif + '" style="' + bgimg + ' width: ' + w + '; height: ' + h + ';" title="' + LHE_MESS.Video + ': ' + JSConfig.file + '"/>';
				}
				sContent = sContent.replace(/<script.*?silverlight\.js.*?<\/script>\s*?<script.*?wmvplayer\.js.*?<\/script>\s*?<div.*?id\s*?=\s*?("|\')(.*?)\1.*?<\/div>\s*?<script.*?jeroenwijering\.Player\(document\.getElementById\(("|\')\2\3.*?wmvplayer\.xaml.*?({.*?})\).*?<\/script>/ig, ReplaceWMV);

				// **** Parse FLV ****
				var ReplaceFLV = function(str, attr)
				{
					attr = attr.replace(/[\r\n]+/ig, ' ');
					attr = attr.replace(/\s+/ig, ' ');
					attr = BX.util.trim(attr);
					var
						arParams = {},
						arFlashvars = {},
						w, h, id, prPath, bgimg = '';

					attr.replace(/([^\w]??)(\w+?)\s*=\s*("|\')([^\3]+?)\3/ig, function(s, b0, b1, b2, b3)
					{
						b1 = b1.toLowerCase();
						if (b1 == 'src' || b1 == 'type' || b1 == 'allowscriptaccess' || b1 == 'allowfullscreen' || b1 == 'pluginspage' || b1 == 'wmode')
							return '';
						arParams[b1] = b3; return b0;
					});

					if (!arParams.flashvars || !arParams.id)
						return str;

					arParams.flashvars += '&';
					arParams.flashvars.replace(/(\w+?)=((?:\s|\S)*?)&/ig, function(s, name, val) { arFlashvars[name] = val; return ''; });
					w = (parseInt(arParams.width) || 50) + 'px';
					h = (parseInt(arParams.height) || 25) + 'px';
					arParams.flashvars = arFlashvars;

					if (arFlashvars.image)
						bgimg = 'background-image: url(' + arFlashvars.image + ')!important; ';

					return '<img class="bxed-video" id="' + pLEditor.SetBxTag(false, {tag: 'video', params: arParams}) + '" src="' + pLEditor.oneGif + '" style="' + bgimg + ' width: ' + w + '; height: ' + h + ';" title="' + LHE_MESS.Video + ': ' + arParams.flashvars.file + '"/>';
				}

				sContent = sContent.replace(/<embed((?:\s|\S)*?player\/mediaplayer\/player\.swf(?:\s|\S)*?)(?:>\s*?<\/embed)?(?:\/?)?>/ig, ReplaceFLV);

				return sContent;
			},
			UnParse: function(bxTag, pNode, pLEditor)
			{
				if (!bxTag.params)
					return '';

				var
					arParams = bxTag.params, i, str;

				var arVidConf = pLEditor.arConfig.videoSettings;
				if (arVidConf.maxWidth && arParams.width && parseInt(arParams.width) > parseInt(arVidConf.maxWidth))
					arParams.width = arVidConf.maxWidth;
				if (arVidConf.maxHeight && arParams.height && parseInt(arParams.height) > parseInt(arVidConf.maxHeight))
					arParams.height = arVidConf.maxHeight;

				if (arParams['flashvars']) // FLV
				{
					str = '<embed src="/bitrix/components/bitrix/player/mediaplayer/player.swf" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" pluginspage="http:/' + '/www.macromedia.com/go/getflashplayer" ';
					str += 'id="' + arParams.id + '" ';
					if (arVidConf.WMode)
						str += 'WMode="' + arVidConf.WMode + '" ';

					for (i in arParams)
					{
						if (i == 'flashvars')
						{
							if (arVidConf.bufferLength)
								arParams[i].bufferlength = arVidConf.bufferLength;
							if (arVidConf.skin)
								arParams[i].skin = arVidConf.skin;
							if (arVidConf.logo)
								arParams[i].logo = arVidConf.logo;
							str += 'flashvars="';
							for (k in arParams[i])
								str += k + '=' + arParams[i][k] + '&';
							str = str.substring(0, str.length - 1) + '" ';
						}
						else
						{
							str += i + '="' + arParams[i] + '" ';
						}
					}
					str += '></embed>';
				}
				else // WMV
				{

					str = '<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/silverlight.js" /></script>' +
				'<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js"></script>' +
				'<div id="' + arParams.id + '">WMV Player</div>' +
				'<script type="text/javascript">new jeroenwijering.Player(document.getElementById("' + arParams.id + '"), "/bitrix/components/bitrix/player/wmvplayer/wmvplayer.xaml", {';

					if (arVidConf.bufferLength)
						arParams.JSConfig.bufferlength = arVidConf.bufferLength;
					if (arVidConf.logo)
						arParams.JSConfig.logo = arVidConf.logo;
					if (arVidConf.windowless)
						arParams.JSConfig.windowless = arVidConf.windowless ? true : false;

					for (i in arParams.JSConfig)
						str += i + ': "' + arParams.JSConfig[i] + '", ';
					str = str.substring(0, str.length - 2);

					str += '});</script>';
				}
				return str;
			}
		}
	}
};

LHEButtons['SmileList'] = {
	id : 'SmileList',
	name : LHE_MESS.SmileList,
	bBBShow: true,
	type: 'List',
	OnAfterCreate: function(pLEditor, pList)
	{
		var n = parseInt(pLEditor.arConfig.smileCountInToolbar);
		// Display some smiles just in toolbar for easy access
		if (n > 0)
		{
			var
				arSmiles = pLEditor.arConfig.arSmiles,
				l = arSmiles.length,
				smileTable = pList.pWnd.parentNode.appendChild(BX.create("TABLE", {props: {className: "lhe-smiles-tlbr-table"}})),
				r = smileTable.insertRow(-1),
				pImg, oSmile, pSmile, k, arImg = [];

			pList.oSmiles = {};
			for (i = 0; i < n; i++)
			{
				oSmile = arSmiles[i];
				if (typeof oSmile != 'object' || !oSmile.path || !oSmile.code)
					continue;

				k = 'smile_' + i + '_' + pLEditor.id;
				pSmile = r.insertCell(-1).appendChild(BX.create("DIV", {props: {className: 'lhe-tlbr-smile-cont', title: oSmile.name || '', id: k}}));
				pImg = pSmile.appendChild(BX.create("IMG", {props: {src: oSmile.path}}));
				pList.oSmiles[k] = oSmile;

				pSmile.onmousedown = function()
				{
					//pLEditor.oPrevRange = pLEditor.GetSelectionRange();
					pList.oBut.SetSmile(this.id, pList);
				};
				pSmile.onmouseover = function(){BX.addClass(this, "lhe-tlbr-smile-over");};
				pSmile.onmouseout = function(){BX.removeClass(this, "lhe-tlbr-smile-over");};

				arImg.push(pImg);
			}

			BX.addClass(pList.pWnd, "lhe-tlbr-smile-more");
			pList.pWnd.id = "";
			r.insertCell(-1).appendChild(pList.pWnd);
			smileTable.parentNode.style.width = (parseInt(smileTable.offsetWidth) + 16 /*left margin*/) + "px";

			var adjustSmiles = function()
			{
				var i, n = arImg.length;
				for (i = 0; i < n; i++)
				{
					var
						h = arImg[i].offsetHeight,
						w = arImg[i].offsetWidth;

					if (h > 20)
					{
						arImg[i].style.height = "20px";
						arImg[i].height = "20";
						h = 20;
					}

					arImg[i].style.marginTop = Math.round((20 - h) / 2) + "px";

					if (w > 20)
					{
						arImg[i].parentNode.style.width = arImg[i].offsetWidth + "px";
						w = 20;
					}
					arImg[i].style.marginLeft = Math.round((20 - w) / 2) + "px";
					arImg[i].style.visibility = "visible";
				}
				smileTable.parentNode.style.width = (parseInt(smileTable.offsetWidth) + 16 /*left margin*/) + "px";
			};

			BX.addCustomEvent(pLEditor, 'onShow', function()
			{
				adjustSmiles();
				setTimeout(adjustSmiles, 1000);
			});
		}
	},
	OnCreate: function(pList)
	{
		var
			arSmiles = pList.pLEditor.arConfig.arSmiles,
			l = arSmiles.length,
			pImg, pSmile, i, oSmile, k;

		pList.pValuesCont.style.width = '100px';
		pList.oSmiles = {};

		var cells = Math.round(Math.sqrt(l * 4 / 3));
		var pTable = pList.pValuesCont.appendChild(BX.create("TABLE", {props: {className: 'lhe-smiles-cont'}}));
		for (i = 0; i < l; i++)
		{
			oSmile = arSmiles[i];
			if (typeof oSmile != 'object' || !oSmile.path || !oSmile.code)
				continue;

			k = 'smile_' + i + '_' + pList.pLEditor.id;
			pSmile = BX.create("DIV", {props: {className: 'lhe-smile-cont', title: oSmile.name || '', id: k}});
			pImg = pSmile.appendChild(BX.create("IMG", {props: {src: oSmile.path, className: 'lhe-smile'}}));

			pImg.onerror = function(){var d = this.parentNode; d.parentNode.removeChild(d);};

			pList.oSmiles[k] = oSmile;

			pSmile.onmousedown = function(){pList.oBut.SetSmile(this.id, pList);};
			pSmile.onmouseover = function(){this.className = 'lhe-smile-cont lhe-smile-cont-over';};
			pSmile.onmouseout = function(){this.className = 'lhe-smile-cont';};

			if (i % cells == 0)
				row = pTable.insertRow(-1);
			row.insertCell(-1).appendChild(pSmile);
		}

		while (row.cells.length < cells)
			row.insertCell(-1);

		if (pTable.offsetWidth > 0)
		{
			pList.pValuesCont.style.width = pTable.offsetWidth + 2 + "px";
		}
		else
		{
			var count = 0;
			// First attempt to adjust smiles
			var ai = setInterval(function(){
				if (pTable.offsetWidth > 0)
				{
					pList.pValuesCont.style.width = pTable.offsetWidth + 2 + "px";
					clearInterval(ai);
				}
				count++;
				if (count > 100)
				{
					clearInterval(ai);
					pList.pValuesCont.style.width = "180px";
				}
			}, 5);
		}

		// Second attempt to adjust smiles
		if (pImg)
			pImg.onload = function()
			{
				pList.pValuesCont.style.width = "";
				setTimeout(function(){pList.pValuesCont.style.width = pTable.offsetWidth + 2 + "px";}, 50);
			};
	},
	SetSmile: function(k, pList)
	{
		//pList.pLEditor.RestoreSelectionRange();
		var oSmile = pList.oSmiles[k];

		if (pList.pLEditor.sEditorMode == 'code') // In BB or in HTML
			pList.pLEditor.WrapWith(false, false, oSmile.code);
		else // WYSIWYG
			pList.pLEditor.InsertHTML('<img id="' + pList.pLEditor.SetBxTag(false, {tag: "smile", params: oSmile}) + '" src="' + oSmile.path + '" title="' + oSmile.name + '"/>');

		if (pList.bOpened)
			pList.Close();
	},
	parser:
	{
		name: "smile",
		obj: {
			Parse: function(sName, sContent, pLEditor)
			{
				// Smiles
				if (pLEditor.sortedSmiles)
				{
					var i, l = pLEditor.arConfig.arSmiles.length, smile;
					for (i = 0; i < l; i++)
					{
						smile = pLEditor.arConfig.arSmiles[i];
						if (smile.path && smile.code)
							sContent = sContent.replace(new RegExp(BX.util.preg_quote(smile.code), 'ig'),
							'<img id="' + pLEditor.SetBxTag(false, {tag: "smile", params: smile}) + '" src="' + smile.path + '" title="' + smile.name + '"/>');
					}
				}
				return sContent;
			},
			UnParse: function(bxTag, pNode, pLEditor)
			{
				if (!bxTag.params || !bxTag.params.code)
					return '';
				return bxTag.params.code;
			}
		}
	}
};


LHEButtons['HeaderList'] = {
	id : 'HeaderList',
	name : LHE_MESS.HeaderList,
	bBBHide: true,
	type: 'List',
	handler: function() {},
	OnCreate: function(pList)
	{
		var
			pIt, pItem, i, oItem;

		pList.arItems = [
			{value: 'p', name: LHE_MESS.Normal},
			{value: 'h1', name: LHE_MESS.Heading + ' 1'},
			{value: 'h2', name: LHE_MESS.Heading + ' 2'},
			{value: 'h3', name: LHE_MESS.Heading + ' 3'},
			{value: 'h4', name: LHE_MESS.Heading + ' 4'},
			{value: 'h5', name: LHE_MESS.Heading + ' 5'},
			{value: 'h6', name: LHE_MESS.Heading + ' 6'},
			{value: 'pre', name: LHE_MESS.Preformatted}
		];

		var innerCont = BX.create("DIV", {props: {className: 'lhe-header-innercont'}});

		for (i = 0; i < pList.arItems.length; i++)
		{
			oItem = pList.arItems[i];
			if (typeof oItem != 'object' || !oItem.name)
				continue;

			pItem = BX.create("DIV", {props: {className: 'lhe-header-cont', title: oItem.name, id: 'lhe_header__' + i}});
			pItem.appendChild(BX.create(oItem.value.toUpperCase(), {text: oItem.name}));

			pItem.onmousedown = function(){pList.oBut.Select(pList.arItems[this.id.substring('lhe_header__'.length)], pList);};
			pItem.onmouseover = function(){this.className = 'lhe-header-cont lhe-header-cont-over';};
			pItem.onmouseout = function(){this.className = 'lhe-header-cont';};

			oItem.pWnd = innerCont.appendChild(pItem);
		}
		pList.pValuesCont.appendChild(innerCont);
	},
	OnOpen: function(pList)
	{
		var
			frm = pList.pLEditor.queryCommand('FormatBlock'),
			i, v;

		if (pList.pSelectedItemId >= 0)
			pList.SelectItem(false);

		if (!frm)
			frm = 'p';
		for (i = 0; i < pList.arItems.length; i++)
		{
			v = pList.arItems[i];
			if (v.value == frm)
			{
				pList.pSelectedItemId = i;
				pList.SelectItem(true);
			}
		}
	},
	Select: function(oItem, pList)
	{
		pList.pLEditor.SelectRange(pList.pLEditor.oPrevRange);
		pList.pLEditor.executeCommand('FormatBlock', '<' + oItem.value + '>');
		pList.Close();
	}
};

LHEButtons['FontList'] = {
	id : 'FontList',
	name : LHE_MESS.FontList,
	//bBBHide: true,
	type: 'List',
	handler: function() {},
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	OnCreate: function(pList)
	{
		var
			pIt, pItem, i, oItem, font;

		pList.arItems = [];
		for (i in pList.pLEditor.arConfig.arFonts)
		{
			font = pList.pLEditor.arConfig.arFonts[i];
			if (typeof font == 'string')
				pList.arItems.push({value: font, name: font});
		}

		for (i = 0; i < pList.arItems.length; i++)
		{
			oItem = pList.arItems[i];
			if (typeof oItem != 'object' || !oItem.name)
				continue;

			pItem = BX.create("DIV", {props: {className: 'lhe-list-item-cont', title: oItem.name, id: 'lhe_font__' + i}});
			pItem.appendChild(BX.create('SPAN', {props: {className: 'lhe-list-font-span'}, style: {fontFamily: oItem.value}, text: oItem.name}));


			pItem.onmousedown = function(){pList.oBut.Select(pList.arItems[this.id.substring('lhe_font__'.length)], pList);};
			pItem.onmouseover = function(){this.className = 'lhe-list-item-cont lhe-list-item-cont-over';};
			pItem.onmouseout = function(){this.className = 'lhe-list-item-cont';};

			oItem.pWnd = pList.pValuesCont.appendChild(pItem);
		}
	},
	OnOpen: function(pList)
	{
		var
			frm = pList.pLEditor.queryCommand('FontName'),
			i, v;
		if (pList.pSelectedItemId >= 0)
			pList.SelectItem(false);

		if (!frm)
			frm = 'p';
		for (i = 0; i < pList.arItems.length; i++)
		{
			v = pList.arItems[i];
			if (v.value.toLowerCase() == frm.toLowerCase())
			{
				pList.pSelectedItemId = i;
				pList.SelectItem(true);
			}
		}
	},
	Select: function(oItem, pList)
	{
		pList.pLEditor.RestoreSelectionRange();

		if (pList.pLEditor.sEditorMode == 'code')
		{
			if (pList.pLEditor.bBBCode)
				pList.pLEditor.FormatBB({tag: 'FONT', pBut: pList, value: oItem.value});
		}
		else
		{
			pList.pLEditor.executeCommand('FontName', oItem.value);
		}
		pList.Close();
	}
};

LHEButtons['FontSizeList'] = {
	id : 'FontSizeList',
	name : LHE_MESS.FontSizeList,
	type: 'List',
	handler: function() {},
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	OnCreate: function(pList)
	{
		var
			pIt, pItem, i, oItem, fontSize;

		pList.arItems = [];
		for (i in pList.pLEditor.arConfig.arFontSizes)
		{
			fontSize = pList.pLEditor.arConfig.arFontSizes[i];
			if (typeof fontSize == 'string')
				pList.arItems.push({value: parseInt(i), name: fontSize});
		}

		for (i = 0; i < pList.arItems.length; i++)
		{
			oItem = pList.arItems[i];
			if (typeof oItem != 'object' || !oItem.name)
				continue;

			pItem = BX.create("DIV", {props: {className: 'lhe-list-item-cont', title: oItem.name, id: 'lhe_font_size__' + i}});
			pItem.appendChild(BX.create('SPAN', {props: {className: 'lhe-list-font-span'}, style: {fontSize: oItem.name}, text: oItem.name}));

			if (BX.browser.IsIE() && !BX.browser.IsDoctype())
				pItem.style.width = "200px";


			pItem.onmousedown = function(){pList.oBut.Select(pList.arItems[this.id.substring('lhe_font_size__'.length)], pList);};
			pItem.onmouseover = function(){this.className = 'lhe-list-item-cont lhe-list-item-cont-over';};
			pItem.onmouseout = function(){this.className = 'lhe-list-item-cont';};

			oItem.pWnd = pList.pValuesCont.appendChild(pItem);
		}
	},
	OnOpen: function(pList)
	{
		var
			frm = pList.pLEditor.queryCommand('FontSize'),
			i, v;
		if (pList.pSelectedItemId >= 0)
			pList.SelectItem(false);

		if (!frm)
			frm = 'p';
		frm = frm.toString().toLowerCase();
		for (i = 0; i < pList.arItems.length; i++)
		{
			v = pList.arItems[i];
			if (v.value.toString().toLowerCase() == frm)
			{
				pList.pSelectedItemId = i;
				pList.SelectItem(true);
			}
		}
	},
	Select: function(oItem, pList)
	{
		pList.pLEditor.RestoreSelectionRange();
		if (pList.pLEditor.sEditorMode == 'code')
		{
			if (pList.pLEditor.bBBCode)
				pList.pLEditor.FormatBB({tag: 'SIZE', pBut: pList, value: oItem.value});
		}
		else
		{
			pList.pLEditor.executeCommand('FontSize', oItem.value);
		}
		pList.Close();
	}
};

LHEButtons['BackColor'] = {
	id : 'BackColor',
	name : LHE_MESS.BackColor,
	bBBHide: true,
	type: 'Colorpicker',
	OnSelect: function(color, pCol)
	{
		if(BX.browser.IsIE())
		{
			pCol.pLEditor.executeCommand('BackColor', color || '');
		}
		else
		{
			try{
				pCol.pLEditor.pEditorDocument.execCommand("styleWithCSS", false, true);
				if (!color)
					pCol.pLEditor.executeCommand('removeFormat');
				else
					pCol.pLEditor.executeCommand('hilitecolor', color);

				pCol.pLEditor.pEditorDocument.execCommand("styleWithCSS", false, false);
			}catch(e){}
		}
	}
};

LHEButtons['ForeColor'] = {
	id : 'ForeColor',
	name : LHE_MESS.ForeColor,
	type: 'Colorpicker',
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	OnSelect: function(color, pCol)
	{
		if (pCol.pLEditor.sEditorMode == 'code')
		{
			if (pCol.pLEditor.bBBCode)
				pCol.pLEditor.FormatBB({tag: 'COLOR', pBut: pCol, value: color});
		}
		else
		{
			if (!color && !BX.browser.IsIE())
				pCol.pLEditor.executeCommand('removeFormat');
			else
				pCol.pLEditor.executeCommand('ForeColor', color || '');
		}
	}
};

LHEButtons['Table'] = {
	id : 'table',
	name : LHE_MESS.InsertTable,
	OnBeforeCreate: function(pLEditor, pBut)
	{
		// Disable in non BBCode mode in html
		pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
		return pBut;
	},
	handler : function (pBut)
	{
		pBut.pLEditor.OpenDialog({ id: 'Table'});
	}
};

//CONTEXT MENU
var LHEContMenu = {};
LHEContMenu["A"] = [LHEButtons['CreateLink'], LHEButtons['DeleteLink']];
LHEContMenu["IMG"] = [LHEButtons['Image']];
LHEContMenu["VIDEO"] = [LHEButtons['Video']];