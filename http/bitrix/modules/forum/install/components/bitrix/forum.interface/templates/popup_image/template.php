<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
$arParams["URL"] = trim($arParams["~URL"]);
// *************************/BASE **********************************************************************
// ************************* ADDITIONAL ****************************************************************
$arParams["WIDTH"] = (intVal($arParams["WIDTH"]) > 0 ? intVal($arParams["WIDTH"]) : 100);
$arParams["HEIGHT"] = (intVal($arParams["HEIGHT"]) > 0 ? intVal($arParams["HEIGHT"]) : 0);
$arParams["CONVERT"] = ($arParams["CONVERT"] == "N" ? "N" : "Y");
$arParams["FAMILY"] = trim($arParams["FAMILY"]);
$arParams["FAMILY"] = CUtil::addslashes(empty($arParams["FAMILY"]) ? "FORUM" : $arParams["FAMILY"]);
$arParams["SINGLE"] = ($arParams["SINGLE"] == "N" ? "N" : "Y");
$arParams["RETURN"] = ($arParams["RETURN"] == "Y" ? "Y" : "N");
// *************************/ADDITIONAL ****************************************************************
// *************************/Input params***************************************************************
if (empty($arParams["URL"]))
	return false;
if ($arParams["CONVERT"] == "Y")
	$arParams["URL"] = htmlspecialcharsEx($arParams["URL"]);
if (!function_exists("__GetPopupID"))
{
	function __GetPopupID()
	{
		static $ImageId = array();
		$sId = rand();
		while (in_array($sId, $ImageId))
		{
			$sId = rand();
		}
		$ImageId[] = $sId;
		return $sId;
	}
}
	
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);

if (!function_exists("__show_image_script_popup"))
{
	function __show_image_script_popup()
	{
		$GLOBALS["bShowImageScriptPopup"] = in_array($GLOBALS["bShowImageScriptPopup"], array(false, true)) ? $GLOBALS["bShowImageScriptPopup"] : false;
		if ($GLOBALS["bShowImageScriptPopup"] != true && !defined("PUBLIC_AJAX_MODE"))
		{
			$GLOBALS["bShowImageScriptPopup"] = true;
?>
<script>
if (phpVars == null || typeof(phpVars) != "object")
{
	var phpVars = {
		'ADMIN_THEME_ID': '.default',
		'titlePrefix': '<?=CUtil::JSEscape(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))?> - '};
}

window.onForumImageLoad = function(oImg, w, h, family, oImg1)
{
	if (typeof oImg == "string")
	{
		oImg = document.getElementById(oImg);
	}
	if (oImg == null || typeof oImg != "object")
	{
		return false;
	}
	
	family = (family && family.length > 0 ? family : "");
    var img = {'width' : 0, 'height' : 0};
    
	if (oImg.naturalWidth)
	{
		img['width'] = oImg.naturalWidth;
		img['height'] = oImg.naturalHeight;
	}
	else
	{
		img['width'] = oImg.width;
		img['height'] = oImg.height;
	}
	var k = 1;
	w = parseInt(w);
	w = (w > 0 ? w : 100);
	h = parseInt(h);
	
	
	if (h <= 0 && img['width'] > w)
	{
    	k = w/img['width'];
	}
	else if (h > 0 && (img['width'] > w || h > img['height']))
	{
		if (img['width'] <= 0)
			k = h/img['height'];
		else
			k = Math.min(w/img['width'], h/img['height']);
	}
	
	if (0 < k && k < 1)
	{
        oImg.style.cursor = 'pointer';
        oImg.onclick = new Function("onForumImageClick(this, '" + img['width'] + "', '" + img['height'] + "', '" + family +"')");
        if (h > 0)
        {
	        var width = parseInt(img['width'] * k);
	        var height = parseInt(img['height'] * k);
	        oImg.width = width;
	        oImg.height = height;
        }
	}
}
window.onForumImageClick = function(oImg, w, h, family)
{
	if (oImg == null || typeof oImg != "object")
		return false;

	w = (w <= 0 ? 100 : w);
	h = (h <= 0 ? 100 : h);
	family = (family && family.length > 0 ? family : "");
	var div = null;
	var id = 'div_image' + (family.length > 0 ? family : oImg.id);
	if (family.length > 0)
	{
		div = document.getElementById(id);
		if (div != null && typeof div == "object")
			div.parentNode.removeChild(div);
	}
	div = document.createElement('div');
	div.id = id;
	div.className = 'forum-popup-image';
	div.style.position = 'absolute';
	div.style.width = w + 'px';
	div.style.height = h + 'px';
	div.style.zIndex = 80;
	div.onclick = function(){
		jsFloatDiv.Close(this);
		this.parentNode.removeChild(this);};
	
	var pos = {};
	var res = jsUtils.GetRealPos(oImg);
	var win = jsUtils.GetWindowScrollPos();
	var win_size = jsUtils.GetWindowInnerSize();
	var img = new Image();
	var div1 = document.createElement('div');
	
	pos['top'] = parseInt(res['top'] + oImg.offsetHeight/2 - h/2);
	if ((parseInt(pos['top']) + parseInt(h)) > (win['scrollTop'] + win_size['innerHeight']))
	{
		pos['top'] = (win['scrollTop'] + win_size['innerHeight'] - h - 10);
	}
	if (pos['top'] <= win['scrollTop'])
	{
		pos['top'] = win['scrollTop'] + 10;
	}
	
	pos['left'] = parseInt(res['left'] + oImg.offsetWidth/2 - w/2);
	pos['left'] = (pos['left'] <= 0 ? 10 : pos['left']);
	
	div1.style.left = (w - 14) + "px";
	div1.style.top = "0px";
	
	div1.className = 'empty';
	div1.style.zIndex = 82;
	div1.style.position = 'absolute';
	div1.style
	div.appendChild(div1);
	
	img.width = w;
	img.height = h;
	img.style.cursor = 'pointer';
	img.src = oImg.src;
	
	div.appendChild(img);
	document.body.appendChild(div);
	jsFloatDiv.Show(div, pos['left'], pos['top']);
}

function onForumImagesLoad()
{
	if (oForumForm && oForumForm['images_for_resize'] && oForumForm['images_for_resize'].length > 0)
	{
		for (var ii = 0; ii < oForumForm['images_for_resize'].length; ii++)
		{
			var img = document.getElementById(oForumForm['images_for_resize'][ii]);
			if (img != 'null' && img && img.tagName == "IMG")
			{
				img.onload();
			}
		}
	}
}
if (jsUtils.IsIE())
{
	jsUtils.addEvent(window, "load", onForumImagesLoad);
}
if (typeof oForumForm != "object")
	var oForumForm = {};
oForumForm['images_for_resize'] = [];
</script>
<?
		}
	}
}
__show_image_script_popup();
$id = "popup_".__GetPopupID();
?>
<script>oForumForm['images_for_resize'].push('<?=$id?>');</script>
<?

if ($arParams["RETURN"] == "Y")
{
	$arParams["RETURN_DATA"] = 
	"<img src=\"".$arParams["URL"]."\"  alt=\"".GetMessage("FRM_IMAGE_ALT")."\" ".
		"onload=\"try{window.onForumImageLoad(this, '".$arParams["WIDTH"]."', '".$arParams["HEIGHT"]."', '".
		$arParams["FAMILY"]."');}catch(e){}\" ".
		" id=\"".$id."\" border=\"0\" />";
	$this->__component->arParams["RETURN_DATA"] = $arParams["RETURN_DATA"];
}
else 
{
	?><img src="<?=$arParams["URL"]?>"  alt="<?=GetMessage("FRM_IMAGE_ALT")?>" <?
		?>onload="try{window.onForumImageLoad(this, '<?=$arParams["WIDTH"]?>', '<?=$arParams["HEIGHT"]?>', '<?=$arParams["FAMILY"]?>');}catch(e){}" <?
		?>id="<?=$id?>" border="0" /><?
}
?>
