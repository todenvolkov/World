<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!function_exists("___photogallery_uploader"))
{
	function ___photogallery_uploader()
	{
		static $bPhotogalleryUploadIsFirstOnPage = true;
		$res = $bPhotogalleryUploadIsFirstOnPage;
		$bPhotogalleryUploadIsFirstOnPage = false;
		return $res;
	}
}

?>
<script>
function PackageBeforeUploadLink<?=$arParams["INDEX_ON_PAGE"]?>(PackageIndex){
	if (!window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'])
		InitLink<?=$arParams["INDEX_ON_PAGE"]?>();
	window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'].PackageBeforeUpload(PackageIndex);}
function BeforeUploadLink<?=$arParams["INDEX_ON_PAGE"]?>(){
	if (!window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'])
		InitLink<?=$arParams["INDEX_ON_PAGE"]?>();
	window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'].BeforeUpload();}
function AfterUploadLink<?=$arParams["INDEX_ON_PAGE"]?>(htmlPage){
	if (!window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'])
		InitLink<?=$arParams["INDEX_ON_PAGE"]?>();
	window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'].AfterUpload(htmlPage);}
function ChangeSelectionLink<?=$arParams["INDEX_ON_PAGE"]?>(){
	if (!window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'])
		InitLink<?=$arParams["INDEX_ON_PAGE"]?>();
	window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'].ChangeSelection();}
function ChangeFileCountLink<?=$arParams["INDEX_ON_PAGE"]?>(){
	if (!window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'])
		InitLink<?=$arParams["INDEX_ON_PAGE"]?>();
	window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'].ChangeFileCount();}
function InitLink<?=$arParams["INDEX_ON_PAGE"]?>(){
	window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'] = new BitrixImageUploader();
	window.oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['object'].Init('<?=$arParams["INDEX_ON_PAGE"]?>');}
</script>
<?

// This block showed only once on page
$res = ___photogallery_uploader();
if (!$res):
	return true;
endif;

if (!$Browser["isOpera"]):
	$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/image_uploader/iuembed.js');
	
	include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/image_uploader/version.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/image_uploader/localization.php");
endif;

$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/lang/".LANGUAGE_ID."/script.php")));
__IncludeLang($file);

$arCookie = array();
foreach ($_COOKIE as $key => $val)
	$arCookie[] = $key."=".$val."; ";
	
?>
<script>
if (typeof oParams != "object")
	oParams = {};
oParams['main'] = {
	'user_id' : <?=intVal($GLOBALS["USER"]->GetID())?>, 
	'show_tags' : '<?=($arResult["SHOW"]["TAGS"] == "Y" ? "Y" : "N")?>'};
if (phpVars == null || typeof(phpVars) != "object")
	var phpVars = {'COOKIES' : ""};
phpVars['COOKIES'] = '<?=CUtil::JSEscape(implode("", $arCookie))?>';
phpVars['bitrix_sessid'] = '<?=bitrix_sessid()?>';
if (typeof iu != "object")
	iu = {};
if (typeof t != "object")
	t = {};

function IUChangeMode(){
	IUSendData('save=view_mode&position=change');
	return true;}

function IUSendData(value){
<?
	if ($GLOBALS["USER"]->IsAuthorized()):
?>
	var url = '/bitrix/components/bitrix/photogallery.upload/user_settings.php?sessid=<?=bitrix_sessid()?>&' + value;
	var TID = jsAjax.InitThread();
	jsAjax.Send(TID, url);
	return true;
<?
	else:
?>
	return false;
<?
	endif;
?>
}

<?
	if (!$Browser["isOpera"])
	{
?>
function to_init()
{
	var bInitImageUploader = false;
	try
	{
		bInitImageUploader = (typeof IUCommon == "object" && IUCommon != null && PUtilsIsLoaded == true ? true : false);
	}
	catch(e){}
	
	if (bInitImageUploader != true)
	{
		setTimeout(to_init, 100);
		return;
	}
	else 
	{
		var tmp = navigator.userAgent.toLowerCase();
		var bWinIsIE = (tmp.indexOf("msie")!=-1 && (tmp.indexOf("opera")==-1));
		var bJavaEnabled = navigator.javaEnabled();
		for (var index in oParams)
		{
			if (oParams[index]['index'] && !oParams[index]['inited'])
			{
				if (oParams[index]['mode'] == 'applet')
				{
					if (!bJavaEnabled && !bWinIsIE)
					{
						if (document.getElementById('uploader_' + index))
						{
							document.getElementById('uploader_' + index).innerHTML = '<?=CUtil::JSEscape('<div class="nojava"><span class="starrequired">'.GetMessage("IU_ATTENTION").'</span> '.str_replace("#HREF_SIMPLE#", $APPLICATION->GetCurPageParam("change_view_mode_data=Y&save=view_mode&view_mode=form&".bitrix_sessid_get(), array("change_view_mode_data", "save", "view_mode", "sessid")), GetMessage("IU_DISABLED_JAVA")))?></div>';
							oParams[index]['applet_inited'] = true;
							oParams[index]['thumbnail_inited'] = true;
							oParams[index]['inited'] = true;
						}
					}
					/*else if (!bWinIsIE)
					{
						InitImageUploader(index);
						InitThumbnailWriter(index);
						oParams[index]['applet_inited'] = true;
						oParams[index]['thumbnail_inited'] = true;
						oParams[index]['inited'] = true;
					}*/
					else if (!oParams[index]['applet_inited'])
					{
						oParams[index]['applet_inited'] = true;
						oParams[index]['attempt'] = 0;
						InitImageUploader(index);
						
						setTimeout(to_init, 500);
					}
					else
					{
						var Uploader = getImageUploader("ImageUploader" + index + '');
						if (Uploader)
						{
							oParams[index]['thumbnail_inited'] = true;
							oParams[index]['inited'] = true;
							InitThumbnailWriter(index);
						}
						else
						{
							oParams[index]['attempt']++;
							if (oParams[index]['attempt'] < 10)
							{
								setTimeout(to_init, 500);
							}
							else
							{
								document.getElementById('thumbnail_' + index).innerHTML = "";
							}
						}
					}
				}
				else
				{
					oParams[index]['object'] = new ImageUploadFormClass();
					oParams[index]['object'].Init(index, false);
					oParams[index]['object'].oFields = {
						"SourceFile" : {"type" : "file", "title" : oText["SourceFile"]}, 
<?
		if ($arParams["BEHAVIOUR"] == "USER"):
			if ($arParams["SHOW_PUBLIC"] != "N"):
				if ($arParams["PUBLIC_BY_DEFAULT"] == "Y"):
?>
					"Public" : {"type" : "checkbox", "title" : oText["Public"], "checked" : "Y"}, 
<?
				else: 
?>
						"Public" : {"type" : "checkbox", "title" : oText["Public"]}, 
<?
				endif;
			else:
?>
						"Public" : {"type" : "hidden", "value" : "Y"}, 
<?
			endif;
		endif;
?>
						"Title" : {"type" : "text", "title" : oText["Title"]}, 
<?
		if ($arParams["SHOW_TAGS"] == "Y"):
?>
						"Tags" : {"type" : "text", "title" : oText["Tags"], "use_search" : "Y"}, 
<?
		endif;
?>
						"Description" : {"type" : "textarea", "title" : oText["Description"]}};
					oParams[index]['object'].AddFile();
					oParams[index]['inited'] = true;
				}
			}
		}		
	}
	return;
}
function __photo_get_bgcolor(div)
{
	if (typeof(div) != "object" || div == null)
		return false; 
	var style = ""; 
	var ii = 0; 
	style = BX.style(div, 'background-color');  
	if (style == "" || style == "transparent")
	{
		do 
		{
			style = BX.style(div, 'background-color');  
			style = (style == "" || style == "transparent" ? "" : style); 
			div = div.parentNode; 
		} while (div && style == ""); 
	}
	if (style != "" && style.substr(0, 3) == "rgb")
	{
		var colors = style.replace(/([^\d]+)(\d+)([^\d]+)(\d+)([^\d]+)(\d+)([^\d]+)/gi, "$2,$4,$6").split(",", 3);
		for (var ii in colors) 
		{ 
			colors[ii] = parseInt(colors[ii]); 
			colors[ii] = colors[ii].toString(16); 
			colors[ii] = (colors[ii].length < 2 ? "0" + colors[ii] : colors[ii]); 
		} 
		style = "#" + colors.join(""); 
	}
	else if (style != "" && !style.match(/\#[0-9a-f]{6}/ig))
	{
		style = ""; 
	}
	return style; 
}
function InitImageUploader(index)
{
	var div = document.getElementById('uploader_' + index); 
	if (typeof(div) != "object" || div == null)
		return false; 

	iu[index] = new ImageUploaderWriter("ImageUploader" + index, "100%", 315);
	iu[index].instructionsEnabled = true;
	
	iu[index].addEventListener("SelectionChange", "ChangeSelectionLink" + index);
	iu[index].addEventListener("UploadFileCountChange", "ChangeFileCountLink" + index);
	iu[index].addEventListener("AfterUpload", "AfterUploadLink" + index);
	iu[index].addEventListener("BeforeUpload", "BeforeUploadLink" + index);
	iu[index].addEventListener("PackageBeforeUpload", "PackageBeforeUploadLink" + index);
	iu[index].addEventListener("FullPageLoad", "InitLink" + index);
	
	
	iu[index].addParam("ShowDescriptions", "false");
	iu[index].addParam("AllowRotate", "true");
	iu[index].addParam("PaneLayout", "OnePane");
	iu[index].addParam("UseSystemColors", "false");
	
	var tmp_style = __photo_get_bgcolor(div); 
	tmp_style = (tmp_style == "" ? "#ededed" : tmp_style); 
	
	iu[index].addParam("BackgroundColor", tmp_style);
	iu[index].addParam("UploadPaneBackgroundColor", tmp_style);
	
	iu[index].addParam("UploadPaneBorderStyle", "none");
	iu[index].addParam("PreviewThumbnailBorderColor", "#afafaf");
	iu[index].addParam("PreviewThumbnailBorderHoverColor", "#91a7d3");
	iu[index].addParam("DisplayNameActiveSelectedTextColor", "#000000");
	iu[index].addParam("PreviewThumbnailActiveSelectionColor", "#ff8307");
	iu[index].addParam("PreviewThumbnailInactiveSelectionColor", "#ff8307");
	
	iu[index].addParam("ShowUploadListButtons", "false");
	iu[index].addParam("ShowButtons", "false");
	iu[index].addParam("FolderView", "Thumbnails");

	iu[index].addParam("UploadThumbnail1FitMode", "Fit");
	iu[index].addParam("UploadThumbnail1Width", oParams[index]['thumbnail1']);
	iu[index].addParam("UploadThumbnail1Height", oParams[index]['thumbnail1']);
	iu[index].addParam("UploadThumbnail1JpegQuality", oParams[index]['quality1']);
	
	iu[index].addParam("UploadThumbnail2FitMode", "Fit");
	iu[index].addParam("UploadThumbnail2Width", oParams[index]['thumbnail2']);
	iu[index].addParam("UploadThumbnail2Height", oParams[index]['thumbnail2']);
	iu[index].addParam("UploadThumbnail2JpegQuality", oParams[index]['quality3']);
	
	iu[index].addParam("UploadThumbnail3FitMode", "ActualSize");
	iu[index].addParam("UploadThumbnail3JpegQuality", oParams[index]['quality3']);
	
	//Configure upload settings.
	iu[index].addParam("UploadSourceFile", "false");
	iu[index].addParam("ExtractExif", "ExifDateTime;ExifOrientation;ExifModel");
	iu[index].addParam("FilesPerOnePackageCount", "1"); // !important
	
	//Configure URL files are uploaded to.
	sAction = window.location.protocol + "//" + oParams[index]['url']['form']; 
	iu[index].addParam("Action", sAction);
	iu[index].addParam("UserAgent", window.navigator.userAgent);
	//Configure URL where to redirect after upload.
	//For ActiveX control full path to CAB file (including file name) should be specified.
    
	iu[index].activeXControlCodeBase = "<?=$arAppletVersion["activeXControlCodeBase"]?>";
	iu[index].activeXClassId = "<?=$arAppletVersion["IuActiveXClassId"]?>";
	iu[index].activeXControlVersion = "<?=$arAppletVersion["IuActiveXControlVersion"]?>";
	//For Java applet only path to directory with JAR files should be specified (without file name).
	iu[index].javaAppletCodeBase = "<?=$arAppletVersion["javaAppletCodeBase"]?>";
	iu[index].javaAppletClassName = "<?=$arAppletVersion["javaAppletClassName"]?>"; 
	iu[index].javaAppletJarFileName = "<?=$arAppletVersion["javaAppletJarFileName"]?>"; 
	iu[index].javaAppletCached = true;
	iu[index].javaAppletVersion = "<?=$arAppletVersion["IuJavaAppletVersion"]?>";
	iu[index].addParam("LicenseKey", "Bitrix");
	
	iu[index].showNonemptyResponse = "off";
	
	language_resources.addParams(iu[index]);

	div.innerHTML = iu[index].getHtml();
	
/*	iu[index].writeHtml();
	return false;
*/
}
function InitThumbnailWriter(index)
{
	var div = document.getElementById('thumbnail_' + index); 
	if (typeof(div) != "object" || div == null)
		return false; 

	t[index] = new ThumbnailWriter("Thumbnail" + index, 120, 120);
	
	var tmp_style = __photo_get_bgcolor(div); 
	tmp_style = (tmp_style == "" ? "#d8d8d8" : tmp_style); 
	t[index].addParam("BackgroundColor", tmp_style); 
	
	//For ActiveX control full path to CAB file (including file name) should be specified.
	t[index].activeXControlCodeBase = "<?=$arAppletVersion["activeXControlCodeBase"]?>";
	t[index].activeXClassId = "<?=$arAppletVersion["ThumbnailActiveXClassId"]?>";
	t[index].activeXControlVersion = "<?=$arAppletVersion["ThumbnailActiveXControlVersion"]?>";
	//For Java applet only path to directory with JAR files should be specified (without file name).
	t[index].javaAppletCodeBase = "<?=$arAppletVersion["javaAppletCodeBase"]?>";
	t[index].javaAppletJarFileName = "<?=$arAppletVersion["javaAppletJarFileName"]?>"; 
	t[index].javaAppletCached = true;
	t[index].javaAppletVersion = "<?=$arAppletVersion["ThumbnailJavaAppletVersion"]?>";
	t[index].addParam("ParentControlName", "ImageUploader" + index);
	
	language_resources.addParams(t[index]);
	
	div.innerHTML = t[index].getHtml();
}
<?
	}
	else
	{
?>
function to_init()
{
	var bInitImageUploader = false;
	try
	{
		bInitImageUploader = (PUtilsIsLoaded == true ? true : false);
	}
	catch(e){}
	
	if (bInitImageUploader != true)
	{
		setTimeout(to_init, 100);
		return;
	}
	else 
	{
		for (var index in oParams)
		{
			if (oParams[index]['index'] && !oParams[index]['inited'])
			{

				oParams[index]['object'] = new ImageUploadFormClass();
				oParams[index]['object'].Init(index, false);
				oParams[index]['object'].oFields = {
					"SourceFile" : {"type" : "file", "title" : oText["SourceFile"]}, 
<?
			if ($arParams["BEHAVIOUR"] == "USER"):
				if ($arParams["SHOW_PUBLIC"] != "N"): 
					if ($arParams["PUBLIC_BY_DEFAULT"] == "Y"):
?>
					"Public" : {"type" : "checkbox", "title" : oText["Public"], "checked" : "Y"}, 
<?
					else: 
?>
					"Public" : {"type" : "checkbox", "title" : oText["Public"]}, 
<?
					endif;
				else: 
?>
					"Public" : {"type" : "hidden", "value" : "Y"}, 
<?
				endif;
			endif;
?>
					"Title" : {"type" : "text", "title" : oText["Title"]}, 
<?			if ($arParams["SHOW_TAGS"] == "Y"):
?>
					"Tags" : {"type" : "text", "title" : oText["Tags"], "use_search" : "Y"}, 
<?
			endif;
?>
					"Description" : {"type" : "textarea", "title" : oText["Description"]}};
				oParams[index]['object'].AddFile();
				oParams[index]['inited'] = true;
			}
		}
	}
	return;
}
<?
	}
?>
</script>