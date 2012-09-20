<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<script language="JavaScript" type="text/javascript">
<!--

var text_enter_url = "<?echo GetMessage("WIKI_TEXT_ENTER_URL");?>";
var text_enter_url_external = "<?echo GetMessage("WIKI_TEXT_ENTER_URL_EXTERNAL");?>";
var text_enter_url_name = "<?echo GetMessage("WIKI_TEXT_ENTER_URL_NAME");?>";
var text_enter_url_name_external = "<?echo GetMessage("WIKI_TEXT_ENTER_URL_NAME_EXTERNAL");?>";
var text_enter_image = "<?echo GetMessage("WIKI_TEXT_ENTER_IMAGE");?>";
var error_no_url = "<?echo GetMessage("WIKI_ERROR_NO_URL");?>";
var error_no_title = "<?echo GetMessage("WIKI_ERROR_NO_TITLE");?>";

var wikiConvertBr = 'N';
var myAgent = navigator.userAgent.toLowerCase();
var myVersion = parseInt(navigator.appVersion);

var wikitags = Array();
var arWikiImg = {};

var rng = null;
var sel = null;
var selLength = null;
var selStart = null;
var selEnd = null;	
var oPrevRange = null;

var is_ie = ((myAgent.indexOf("msie") != -1) && (myAgent.indexOf("opera") == -1));
var is_nav = ((myAgent.indexOf('mozilla')!=-1) && (myAgent.indexOf('spoofer')==-1)
 && (myAgent.indexOf('compatible') == -1) && (myAgent.indexOf('opera')==-1)
 && (myAgent.indexOf('webtv')==-1) && (myAgent.indexOf('hotjava')==-1));
var is_opera = (myAgent.indexOf("opera") != -1);

var is_win = ((myAgent.indexOf("win")!=-1) || (myAgent.indexOf("16bit") != -1));
var is_mac = (myAgent.indexOf("mac")!=-1);

if (!window.phpVars)  // For anonymus  users
	window.phpVars = {};


function wiki_bold()
{
	simpletag("'''"); 
}

function wiki_italic()
{
	simpletag("''"); 
}

function wiki_header()
{
	simpletag("=="); 
}

function wiki_line()
{
	doInsert('----', '', false);
}

function wiki_signature()
{
	if(window.pLEditorWiki  && document.getElementById('wki-text-html').checked)
	{			
		window.pLEditorWiki.InsertHTML('--~~~~');
	}
	else
	{
		doInsert('--~~~~', '', false);		
	}		
}

function wiki_nowiki()
{
	simpletag("<NOWIKI>");
}

// Insert simple tags: B, I, U, CODE, QUOTE
function simpletag(thetag)
{
    if (doInsert(thetag, checkTag(thetag), true))
    {    	
	// Change the button status			
	pushstack(wikitags, thetag);
	cstat();
    }

}

function ShowImageUpload()
{
	var params = {		
		content_url: '<?=CHTTP::urlAddParams($arResult['PATH_TO_POST_EDIT'], array('image_upload' => 'Y'))?>',
		height: 150,
		width: 400,
		min_height: 150,
		min_width: 400	    
	};
			
	var bxd = new BX.CDialog(params);
	bxd.Show();	
}

function ShowImageInsert()
{
	var params = {		
		content_url: '<?=CHTTP::urlAddParams($arResult['PATH_TO_POST_EDIT'], array('insert_image' => 'Y'))?>',
		height: 150,
		width: 400,
		min_height: 150,
		min_width: 400	    
	};
			
	var bxd = new BX.CDialog(params);
	bxd.Show();	
}

function ShowCategoryInsert()
{
	var params = {		
		content_url: '<?=CHTTP::urlAddParams($arResult['PATH_TO_POST_EDIT'], array('insert_category' => 'Y'))?>',
		height: 150,
		height: 150,
		width: 400,
		min_height: 150,
		min_width: 400	    
	};
			
	var bxd = new BX.CDialog(params);
	bxd.Show();	
}

function ShowInsertLink(external)
{
	<?
	$arUrlP = array('insert_link' => 'Y');
	?>
	var content_url =  '<?=CHTTP::urlAddParams($arResult['PATH_TO_POST_EDIT'], $arUrlP);?>&'+ (external ? 'external=1' : '');	
	var params = {		
		content_url: content_url,
		height: 150,
		width: 400,
		min_height: 150,
		min_width: 400	    
	};
			
	var bxd = new BX.CDialog(params);
	bxd.Show();	
}
	

// Insert url tag
function wiki_tag_url(URL, TEXT)
{	
	var text = '';
	if (TEXT &&  !URL)
		URL = TEXT;
	text = '[[' + URL + (TEXT && TEXT != '' ? '|' + TEXT : '') + ']]';

	if(window.pLEditorWiki && document.getElementById('wki-text-html').checked)
	{
		window.pLEditorWiki.SelectRange(oPrevRange);
		window.pLEditorWiki.InsertHTML(text);	
	} 
	else
	{
		doInsert(text, "", false, null, true);	
	} 
}

function wiki_tag_url_external(URL, TEXT)
{
	var text = '';
	if (URL == 'http://' || URL == '' || !URL)
		return ;

	text = '[' + URL + (TEXT && TEXT != '' ? ' ' + TEXT : '') + ']';

	if(window.pLEditorWiki && document.getElementById('wki-text-html').checked)
	{
		window.pLEditorWiki.SelectRange(oPrevRange);
		window.pLEditorWiki.InsertHTML(text);				
	} 
	else
	{
		doInsert(text, "", false, null, true);
	} 
}

// Insert image tag
function wiki_tag_image(URL)
{
	if (URL)
	{	
		if(window.pLEditorWiki && document.getElementById('wki-text-html').checked)
		{
			_str = '<img id="' + window.pLEditorWiki.SetBxTag(false, {'tag': 'wiki_img', 'params': {'id' : URL, 'file_name' : URL}}) + '"  \
					src="'+URL+'">';

			window.pLEditorWiki.SelectRange(oPrevRange);
			window.pLEditorWiki.InsertHTML(_str);				
		}
		else
		{
			doInsert("[<?=GetMessage('FILE_NAME');?>:"+URL+"]", "", false, null, true);
		}
	}
}

//Insert image tag
function wiki_tag_category(TEXT)
{
	if (TEXT)
	{	
		if(window.pLEditorWiki && document.getElementById('wki-text-html').checked)
		{
			_str = "[[<?=GetMessage('CATEGORY_NAME');?>:"+TEXT+"]]<br />";
			
			window.pLEditorWiki.SelectRange(oPrevRange);
			window.pLEditorWiki.InsertHTML(_str);	
		}
		else
		{
			doInsert("[[<?=GetMessage('CATEGORY_NAME');?>:"+TEXT+"]]\n", "", false, null, true);
		}
	}
}

// Close all tags
function closeall()
{
	if (wikitags[0]) 
	{
		while (wikitags[0]) 
		{
			tagRemove = popstack(wikitags);
			document.getElementById("MESSAGE").value += checkTag(tagRemove);

		}
	}

	wikitags = new Array();
	cstat();
}

// Stack functions
function pushstack(thearray, newval)
{
	arraysize = stacksize(thearray);
	thearray[arraysize] = newval;
}

function popstack(thearray)
{
	arraysize = stacksize(thearray);
	theval = thearray[arraysize - 1];
	delete thearray[arraysize - 1];
	return theval;
}

function stacksize(thearray)
{
	for (i = 0 ; i < thearray.length; i++ )
	{
		if ( (thearray[i] == "") || (thearray[i] == null) || (thearray == 'undefined') ) 
		{
			return i;
		}
	}

	return thearray.length;
}

// Show statistic
function cstat()
{
	document.getElementById("MESSAGE").focus();
}

function getSelectedText()
{

	if(window.pLEditorWiki && document.getElementById('wki-text-html').checked)
	{	
    	oPrevRange = window.pLEditorWiki.GetSelectionRange();
    	var selectedText = false;	
    	
    	if (oPrevRange.startContainer && oPrevRange.endContainer) // DOM Model
    	{
    		if (oPrevRange.startContainer == oPrevRange.endContainer && 
    				(oPrevRange.endContainer.nodeType == 3 || oPrevRange.endContainer.nodeType == 1))
    		{
    			selectedText = oPrevRange.startContainer.textContent.substring(oPrevRange.startOffset, oPrevRange.endOffset) || '';
    		}
    	}
    	else
    	{
    		if (oPrevRange.text == oPrevRange.htmlText)
    			selectedText = oPrevRange.text || '';
    	}  	
	} 
	else
	{
		var textarea = document.getElementById("MESSAGE");
		var currentScroll = textarea.scrollTop;

		bTitleYes = false;
		if (is_ie)
		{
			textarea.focus();
			sel = document.selection;
			rng = sel.createRange();
			var stored_rng = rng.duplicate();
			stored_rng.moveToElementText(textarea);
			stored_rng.setEndPoint('EndToEnd', rng);
			selStart = stored_rng.text.length - rng.text.length;
			selEnd = selStart + rng.text.length;			
			rng.colapse;
			if ((sel.type == "Text" || sel.type == "None") && rng.text.length > 0)			
				selectedText = rng.text;			
		}
		else 
		{
			selLength = textarea.textLength;
			selStart = textarea.selectionStart;
			selEnd = textarea.selectionEnd;				
			if(textarea.selectionEnd > textarea.selectionStart)
				selectedText = (textarea.value).substring(textarea.selectionStart, textarea.selectionEnd);		
		}		
	} 
	return selectedText;
}

function mozillaWr(textarea, open, close, replace)
{
	if (!selLength) {
		selLength = textarea.textLength;
	}
	if (!selStart)
		selStart = textarea.selectionStart;
	if (!selEnd)
		selEnd = textarea.selectionEnd;
	
	if (selEnd == 1 || selEnd == 2)
		selEnd = selLength;

	var s1 = (textarea.value).substring(0,selStart);
	var s2 = (textarea.value).substring(selStart, selEnd)
	var s3 = (textarea.value).substring(selEnd, selLength);

	if (replace == true)
		textarea.value = s1 + open + close + s3; 
	else
		textarea.value = s1 + open + s2 + close + s3; 	
	
	textarea.selectionEnd = 0;
	textarea.selectionStart = selEnd + open.length + close.length;
	textarea.setSelectionRange(selStart, selEnd);
	selLength = null;
	selStart = null;
	selEnd = null;	
	
	return;
}

function checkTag(thetag)
{
	var bracketEnd = '';
	var bracketStart = '';

	if (thetag.substr(0, 1) == '[' || thetag.substr(0, 1) == '<')
	{
		bracketStart = thetag.substr(0, 1) + '/';
		thetag = thetag.substring(1,thetag.length);
		bracketEnd = thetag.substr(thetag.length, 1);
	}
	return bracketStart + thetag + bracketEnd;
}

function doInsert(ibTag, ibClsTag, isSingle, imgID, replace)
{
	if(imgID > 0 && window.pLEditorWiki && document.getElementById('wki-text-html').checked)
	{
		if(window.pLEditorWiki)
		{
			var __image = document.getElementById(imgID);
			var imageSrc = __image.src;

			var _img_width = null;
			var _img_height = null;
			var _img_style = '';
				
			if (!__image.naturalWidth) {
				var lgi = new Image();
				lgi.src = imageSrc;
				_img_width = lgi.width;
				_img_height = lgi.height;
			} 
			else
			{
				_img_width = __image.naturalWidth;
				_img_height = __image.naturalHeight;					
			} 

			if (_img_width > <?=COption::GetOptionString("wiki", "image_max_width", 600);?>)			
				_img_style += 'width: <?=COption::GetOptionString("wiki", "image_max_width", 600);?>;';

			var file_name  = arWikiImg[imgID];
			var _str = '<img id="' + window.pLEditorWiki.SetBxTag(false, {'tag': 'wiki_img', 'params': {'id' : imgID, 'file_name' : file_name}}) + '"  \
				src="'+imageSrc+'" style="'+_img_style+'">';
						
			window.pLEditorWiki.InsertHTML(_str);
			return true;
		}
		
	}

	var isClose = false;
	var textarea = document.getElementById("MESSAGE");
	var currentScroll = textarea.scrollTop;

	if (isSingle)
		isClose = true;
	
	if (is_ie)
	{
		textarea.focus();
		if (!sel)
			sel = document.selection;
		if (!rng)
			rng = sel.createRange();


		
		rng.colapse;
		if ((sel.type == "Text" || sel.type == "None") && rng != null)
		{
			if (ibClsTag != "" && rng.text.length > 0)
			{
				ibTag += rng.text + ibClsTag;
				isClose = false;
			} 
			else if (ibClsTag != "")
			{
				ibTag += rng.text + ' ' + ibClsTag;
				isClose = false;
			}

			rng.text = ibTag;
			var new_rng = textarea.createTextRange();
			new_rng.move("character", selStart);
			new_rng.select();					
		}
		rng = null;
		sel = null;
	}
	else 
	{
		if (is_nav && document.getElementById)
		{
			if (ibClsTag != "" && textarea.selectionEnd > textarea.selectionStart)
			{
				mozillaWr(textarea, ibTag, ibClsTag, replace);
				isClose = false;
			}
			else if (ibClsTag != "") 
			{
				mozillaWr(textarea, ibTag + ' ', ibClsTag, false);
			}
			else 
				mozillaWr(textarea, ibTag, '', replace);
		}
		else
			textarea.value += ibTag;
	}

	textarea.scrollTop = currentScroll;
	textarea.focus();
	return isClose;
}

check_ctrl_enter = function(e)
{
	if(!e)
		e = window.event;

	if((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey)
	{
		document.REPLIER.submit();
	}
}

function setEditorContentAfterLoad(pLEditor)
{
	var _content = document.getElementById("MESSAGE").value;
	if (wikiConvertBr == 'Y')
		_content = _content.replace(/(.*)(\r\n|\r|\n)/ig, '$1<br />\n');		
	
	pLEditor.SetEditorContent(_content);
}

function showEditField(type, change, convertBr)
{
	wikiConvertBr = convertBr;
	var oDivIDHtml = document.getElementById("edit-post-html");
	var oDivIDText = document.getElementById("edit-post-text");

	if(type == "html")
	{
		var oDivIDFlag = document.getElementById("editor_loaded");
		oDivIDText.style.display = "none";
		if(oDivIDHtml)
			oDivIDHtml.style.display = "block";

		if(oDivIDFlag.value == "N")
		{
			//load editor
			BX.ajax.insertToNode("<?=CHTTP::urlAddParams($arResult['PATH_TO_POST_EDIT'], array('load_editor' => 'Y'))?>", oDivIDHtml);			
			oDivIDFlag.value = "Y";
		}
		else
		{
			if(change == "Y")
			{
				if(window.pLEditorWiki)
				{
					var _content = document.getElementById("MESSAGE").value
					_content = _content.replace(/(.*)(\r\n|\r|\n)/ig, '$1<br />\n');
					window.pLEditorWiki.SetEditorContent(_content);
				}
			}
		}
	}
	else
	{
		if(oDivIDHtml)
			oDivIDHtml.style.display = "none";
		oDivIDText.style.display = "block";
		if(change == "Y")
		{
			if(window.pLEditorWiki)
			{
				window.pLEditorWiki.SaveContent()
				var _content = window.pLEditorWiki.GetContent();	
				_content = _content.replace(/\s*([\r\n])+/ig, "");
				_content = _content.replace(/<br \/>/ig, "\n");								
				document.getElementById("MESSAGE").value = _content;
			}
		}

	}
	return false;
}

-->
</script>