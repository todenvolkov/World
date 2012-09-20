<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CAjax::Init();
IncludeAJAX();
CUtil::InitJSCore();

$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/main/utils.js');
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/components/bitrix/photogallery/templates/.default/script.js');
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/components/bitrix/photogallery.interface/templates/.default/script.js');
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/components/bitrix/search.tags.input/templates/.default/script.js');
if (!$this->__component->__parent || strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
endif;
$GLOBALS['APPLICATION']->AddHeadString('<link href="/bitrix/components/bitrix/search.tags.input/templates/.default/style.css" type="text/css" rel="stylesheet" />', true);

if (!function_exists("getIndexImageUploaderOnPage"))
{
	function getIndexImageUploaderOnPage()
	{
		static $iIndexOnPage = 0;
		$iIndexOnPage++;
		return $iIndexOnPage;
	}
}

/*************************************************************************
	Processing of received parameters
*************************************************************************/
$arParams["WATERMARK"] = ($arParams["WATERMARK"] == "N" ? "N" : "Y");
$arParams["TEMPLATE"] = ($arParams["USE_LIGHT_TEMPLATE"] == "Y" ? "LIGHT-APPLET" : "APPLET"); 
$arParams["SHOW_WATERMARK"] = ($arParams["SHOW_WATERMARK"] == "N" ? "N" : "Y");
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
$arResult["SHOW"]["TAGS"] = (($arResult["SHOW"]["TAGS"] == "Y" && $arParams["SHOW_TAGS"] == "Y") ? "Y" : "N");
$arParams["JPEG_QUALITY1"] = intVal($arParams["JPEG_QUALITY1"]) > 0 ? intVal($arParams["JPEG_QUALITY1"]) : 80;
$arParams["JPEG_QUALITY2"] = intVal($arParams["JPEG_QUALITY2"]) > 0 ? intVal($arParams["JPEG_QUALITY2"]) : 90;
$arParams["JPEG_QUALITY"] = intVal($arParams["JPEG_QUALITY"]) > 0 ? intVal($arParams["JPEG_QUALITY"]) : 90;
if (is_array($arParams["WATERMARK_COLORS"]) && !empty($arParams["WATERMARK_COLORS"]))
{
	$arr = $arParams["WATERMARK_COLORS"];
	$arParams["WATERMARK_COLORS"] = array();
	foreach ($arr as $key)
	{
		if (!empty($key))
			$arParams["WATERMARK_COLORS"][strToLower($key)] = (strLen(GetMessage("P_COLOR_".strToUpper($key))) > 0 ? GetMessage("P_COLOR_".strToUpper($key)) : "#".strToUpper($key));
	}
}
else 
{
	$arParams["WATERMARK_COLORS"] = array(
		"ff0000" => GetMessage("P_COLOR_FF0000"), 
		"ffff00" => GetMessage("P_COLOR_FFFF00"), 
		"ffffff" => GetMessage("P_COLOR_FFFFFF"),
		"000000" => GetMessage("P_COLOR_000000"));
}
//		GetMessage("P_COLOR_FFA500"), 
//		GetMessage("P_COLOR_008000"), 
//		GetMessage("P_COLOR_00FFFF"),
//		GetMessage("P_COLOR_800080"));
//		GetMessage("P_WATERMARK_SIZE_BIG"), 
//		GetMessage("P_WATERMARK_SIZE_MIDDLE"), 
//		GetMessage("P_WATERMARK_SIZE_SMALL"), 
$arParams["INDEX_ON_PAGE"] = getIndexImageUploaderOnPage();
$arParams["SHOW_PUBLIC"] = ($arParams["SHOW_PUBLIC"] == "N" ? "N" : "Y");
$arParams["PUBLIC_BY_DEFAULT"] = ($arParams["PUBLIC_BY_DEFAULT"] == "N" ? "N" : "Y");
/********************************************************************
	/Processing of received parameters
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
/************** WaterMark ******************************************/
$arWatermarkColors = array_keys($arParams["WATERMARK_COLORS"]); 

$arWaterMarkPositions = array(
	"tl" => GetMessage("P_WATERMARK_POSITION_TL"),
	"tc" => GetMessage("P_WATERMARK_POSITION_TC"),
	"tr" => GetMessage("P_WATERMARK_POSITION_TR"),
	"ml" => GetMessage("P_WATERMARK_POSITION_ML"),
	"mc" => GetMessage("P_WATERMARK_POSITION_MC"),
	"mr" => GetMessage("P_WATERMARK_POSITION_MR"),
	"bl" => GetMessage("P_WATERMARK_POSITION_BL"),
	"bc" => GetMessage("P_WATERMARK_POSITION_BC"),
	"br" => GetMessage("P_WATERMARK_POSITION_BR"));

$view_mode = (in_array($_REQUEST["view_mode"], array("form", "applet")) ? $_REQUEST["view_mode"] : "form");
$arWatermark = array(
	"copyright" => strToLower($_REQUEST["watermark_copyright"]), 
	"color" => strToLower($_REQUEST["watermark_color"]), 
	"size" => strToLower($_REQUEST["watermark_size"]), 
	"position" => strToLower($_REQUEST["watermark_position"]), 
	"text" => $_REQUEST["watermark"], 
	"resize" => 1);

if ($GLOBALS["USER"]->IsAuthorized())
{
	if (!empty($_REQUEST["change_view_mode_data"]) == "Y")
	{
		require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/components/bitrix/photogallery.upload/user_settings.php");
	}
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
	$arUserSettings = @unserialize(CUserOptions::GetOption("photogallery", "UploadViewMode", ""));
	$arUserSettings = (is_array($arUserSettings) ? $arUserSettings : array("view_mode" => "applet"));
	$view_mode = (in_array($arUserSettings["view_mode"], array("form", "applet")) ? $arUserSettings["view_mode"] : "applet");
	$arWatermark = $arUserSettings;
}

$arWatermark["copyright"] = ($arWatermark["copyright"] == "hide"? "hide" : "show");
$arWatermark["color"] = (!in_array($arWatermark["color"], $arWatermarkColors) ? $arWatermarkColors[0] : $arWatermark["color"]);
$arWatermark["color"] = htmlspecialchars(strToLower($arWatermark["color"]));
$arWatermark["size"] = (!in_array($arWatermark["size"], array("big", "middle", "small")) ? "middle" : $arWatermark["size"]);
$arWatermark["position"] = (!is_set($arWaterMarkPositions, $arWatermark["position"]) ? "br" : $arWatermark["position"]);
$arWatermark["text"] = htmlspecialchars($arWatermark["text"]);

/************** Browser ********************************************/
$str = strToLower($_SERVER['HTTP_USER_AGENT']);
$Browser["isOpera"] = (strpos($str, "opera") !== false);
$Browser["isIE"] = (!$Browser["isOpera"] && strpos($str, "msie") !== false);
$Browser["isWinIE"] = ($Browser["isIE"] && strpos($str, "win") !== false);
$view_mode = ($Browser["isOpera"] ? "form" : $view_mode);

include(str_replace(array("\\", "//"), "/", dirname(__FILE__)."/script.php"));
/********************************************************************
				/Default values
********************************************************************/
?>
<script>
oParams['<?=$arParams["INDEX_ON_PAGE"]?>'] = {
	'object' : null,
	'thumbnail' : null, 
	'index' : '<?=$arParams["INDEX_ON_PAGE"]?>', 
	'mode' : '<?=($view_mode == "form" ? "form" : "applet")?>', 
	'inited' : false, 
	'url' : {
		'this' : '<?=CUtil::JSEscape($arResult["~SECTION_LINK"])?>', 
		'section' : '<?=CUtil::JSEscape($arResult["~SECTION_EMPTY_LINK"])?>', 
		'form' : '<?=str_replace("//", "/", $_SERVER["HTTP_HOST"]."/".POST_FORM_ACTION_URI)?>'}, 
	'min_size_picture' : <?=intVal($arParams["WATERMARK_MIN_PICTURE_SIZE"])?>, 
	'public_by_default' : '<?=($arParams["SHOW_PUBLIC"] != "N" && $arParams["PUBLIC_BY_DEFAULT"] != "Y" ? "N" : "Y")?>', 
	'thumbnail1' : '<?=$arParams["THUMBS_SIZE"]["SIZE"]?>', 
	'thumbnail2' : '<?=$arParams["PREVIEW_SIZE"]["SIZE"]?>', 
	'quality1' : '<?=$arParams["JPEG_QUALITY1"]?>', 
	'quality2' : '<?=$arParams["JPEG_QUALITY2"]?>', 
	'quality3' : '<?=$arParams["JPEG_QUALITY"]?>', 
	'draft' : <?=CUtil::PhpToJSObject($arParams["PICTURES"])?>}; 
</script>
<div id="photo_error_<?=$arParams["INDEX_ON_PAGE"]?>" class="photo-error">
<?
if (!empty($arResult["ERROR_MESSAGE"])):
?>
	<?=ShowError($arResult["ERROR_MESSAGE"]);?>
<?
endif;
?>
</div>
<?
if (!$Browser["isOpera"]):
?>
<div class="photo-info-box photo-upload-more" id="ControlsAppletForm">
	<div class="photo-info-box-inner">
		<?=str_replace("#HREF#", htmlspecialcharsEx($APPLICATION->GetCurPageParam("change_view_mode_data=Y&save=view_mode&view_mode=".
				($view_mode == "form" ? "applet" : "form")."&".bitrix_sessid_get(), 
			array("change_view_mode_data", "save", "view_mode", "sessid"))), ($view_mode == "form" ? GetMessage("P_SHOW_APPLET") : GetMessage("P_SHOW_FORM")))?>
	</div>
</div>
<?
endif;
?>
<div class="image-uploader-objects">
<?
if ($view_mode == "applet" && $arParams["TEMPLATE"] == "LIGHT-APPLET"):
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="image-uploader-table image-uploader-light-applet">
	<tr class="light-applet-buttons-top">
		<td colspan="2">
			<div class="photo-uploader-buttons">
				<div class="photo-uploader-button">
					<input type="button" onclick="if (oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['inited'])<?
						?>{getImageUploader('ImageUploader<?=$arParams["INDEX_ON_PAGE"]?>').AddFolders();}" <?
						?>value="<?=GetMessage("AddFolders")?>" />
				</div>
				<div class="photo-uploader-button">
					<input type="button" onclick="if (oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['inited'])<?
						?>{getImageUploader('ImageUploader<?=$arParams["INDEX_ON_PAGE"]?>').AddFiles();}" <?
						?>value="<?=GetMessage("AddFiles")?>" />
				</div>
				<div class="empty-clear"></div>
			</div>
		</td>
	</tr>
	<tr class="light-applet-object">
		<td id="uploader_<?=$arParams["INDEX_ON_PAGE"]?>" colspan="2">
			<div class="photo-uploader-containers photo-uploader-uploader-loadwindow">
				<div id="photo_error_upload_uploader<?=$arParams["INDEX_ON_PAGE"]?>" class="photo-error">
					<noscript><?=GetMessage("P_JAVASCRIPT_DISABLED")?></noscript>
				</div>
				<div id="waitwindow" class="waitwindow"><?=GetMessage("P_LOADING")?></div>
			</div>
		</td>
	</tr>
	<tr class="light-applet-thumbs">
		<td class="light-applet-thumbs-image">
			<div class="photo-uploader-fields">
				<div class="photo-uploader-field photo-uploader-field-image">
					<div id="thumbnail_<?=$arParams["INDEX_ON_PAGE"]?>"></div>
				</div>
			</div>
		</td>
		<td class="light-applet-thumbs-info">
			<div class="photo-uploader-fields">
<?
	if ($arParams["BEHAVIOUR"] == "USER"):
		if ($arParams["SHOW_PUBLIC"] != "N"):
?>
				<div class="photo-uploader-field photo-uploader-field-public">
					<input name="Public" id="PhotoPublic<?=$arParams["INDEX_ON_PAGE"]?>" class="PhotoPublic" type="checkbox" value="Y" disabled="disabled" <?
						?><?=($arParams["PUBLIC_BY_DEFAULT"] == "Y" ? " checked='checked' " : "")
					?>/>
					<label for="PhotoPublic<?=$arParams["INDEX_ON_PAGE"]?>"><?=GetMessage("Public")?></label>
				</div>
<?
		else:
?>
				<input name="Public" type="hidden" value="Y" />
<?
		endif;
	endif;
?>
				<div class="photo-uploader-field photo-uploader-field-title">
					<label for="PhotoTitle<?=$arParams["INDEX_ON_PAGE"]?>"><?=GetMessage("Title")?></label>
					<input name="Title" id="PhotoTitle<?=$arParams["INDEX_ON_PAGE"]?>" class="Title" type="text" />
				</div>
<?
	if ($arParams["SHOW_TAGS"] == "Y"):
?>
				<div class="photo-uploader-field photo-uploader-field-tags">
					<label for="PhotoTag<?=$arParams["INDEX_ON_PAGE"]?>"><?=GetMessage("Tags")?></label>
					<input name="Tag" id="PhotoTag<?=$arParams["INDEX_ON_PAGE"]?>" class="Tag" type="text" <?
		if (IsModuleInstalled("search")): 
					?>onfocus="SendTags(this);" <?
		endif;
					?>/>
				</div>
<?
	endif;
?>
				<div class="photo-uploader-field photo-uploader-field-description">
					<label for="PhotoDescription<?=$arParams["INDEX_ON_PAGE"]?>"><?=GetMessage("Description")?></label>
					<textarea name="Description" id="PhotoDescription<?=$arParams["INDEX_ON_PAGE"]?>" class="Description"></textarea>
				</div>
			</div>
		</td>		
	</tr>
</table>
<?
elseif ($view_mode != "form"):
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="image-uploader-table image-uploader-applet">
	<tr class="applet-buttons-top">
		<td class="left">
			<div class="photo-uploader-buttons">
				<div class="photo-uploader-button">
					<a href="#AddFolders" onclick="if (oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['inited'])<?
						?>{getImageUploader('ImageUploader<?=$arParams["INDEX_ON_PAGE"]?>').AddFolders();} return false;"><?
						?><div><span><?=GetMessage("AddFolders")?></span></div></a></div>
				<div class="photo-uploader-button">
					<a href="#AddFiles" onclick="if (oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['inited'])<?
						?>{getImageUploader('ImageUploader<?=$arParams["INDEX_ON_PAGE"]?>').AddFiles();} return false;"><?
						?><div><span><?=GetMessage("AddFiles")?></span></div></a></div>
				<div class="empty-clear"></div>
			</div>
		</td>
		<td class="right">
			<div class="photo-uploader-containers">
				<div class="photo-button-removeall"><?
					?><a href="#RemoveFiles" onclick="return false;" <?
						?>onmousedown="if (oParams['<?=$arParams["INDEX_ON_PAGE"]?>']['inited'])<?
							?>{getImageUploader('ImageUploader<?=$arParams["INDEX_ON_PAGE"]?>').RemoveAllFromUploadList();}"><?
						?><span><span><?=GetMessage("RemoveAllFromUploadList")?></span></span>
					</a>
				</div>
				<div class="photo-uploader-container photo-uploader-filecount">
					<div id="photo_count_to_upload_<?=$arParams["INDEX_ON_PAGE"]?>"><?=GetMessage("NoPhoto")?></div> 
					<span><?=GetMessage("Photo")?></span>
				</div>
				<div class="empty-clear"></div>
			</div>
		</td>
	</tr>
	<tr class="separator">
		<td class="left"><div class="hr"></div></td>
		<td class="right"></td>
	</tr>
	<tr class="applet-object">
		<td class="left photo-uploader-uploader" id="uploader_<?=$arParams["INDEX_ON_PAGE"]?>">
			<div class="photo-uploader-containers photo-uploader-uploader-loadwindow">
				<div id="photo_error_upload_uploader<?=$arParams["INDEX_ON_PAGE"]?>" class="photo-error">
					<noscript><?=GetMessage("P_JAVASCRIPT_DISABLED")?></noscript>
				</div>
				<div id="waitwindow" class="waitwindow"><?=GetMessage("P_LOADING")?></div>
			</div>
		</td>
		<td class="right photo-uploader-thumbnail">
			<div class="photo-uploader-fields">
				<div class="photo-uploader-field photo-uploader-field-image">
					<div id="thumbnail_<?=$arParams["INDEX_ON_PAGE"]?>"></div>
				</div>
<?
	if ($arParams["BEHAVIOUR"] == "USER"):
		if ($arParams["SHOW_PUBLIC"] != "N"):
?>
				<div class="photo-uploader-field photo-uploader-field-public">
					<input name="Public" id="PhotoPublic<?=$arParams["INDEX_ON_PAGE"]?>" class="PhotoPublic" type="checkbox" value="Y" disabled="disabled" <?
						?><?=($arParams["PUBLIC_BY_DEFAULT"] == "Y" ? " checked='checked' " : "")
					?>/>
					<label for="PhotoPublic<?=$arParams["INDEX_ON_PAGE"]?>"><?=GetMessage("Public")?></label>
				</div>
<?
		else:
?>
				<input name="Public" type="hidden" value="Y" />
<?
		endif;
	endif;
?>
				<div class="photo-uploader-field photo-uploader-field-title">
					<label for="PhotoTitle<?=$arParams["INDEX_ON_PAGE"]?>"><?=GetMessage("Title")?></label>
					<input name="Title" id="PhotoTitle<?=$arParams["INDEX_ON_PAGE"]?>" class="Title" type="text" />
				</div>
<?
	if ($arParams["SHOW_TAGS"] == "Y"):
?>
				<div class="photo-uploader-field photo-uploader-field-tags">
					<label for="PhotoTag<?=$arParams["INDEX_ON_PAGE"]?>"><?=GetMessage("Tags")?></label>
					<input name="Tag" id="PhotoTag<?=$arParams["INDEX_ON_PAGE"]?>" class="Tag" type="text" <?
		if (IsModuleInstalled("search")): 
					?>onfocus="SendTags(this);" <?
		endif;
					?>/>
				</div>
<?
	endif;
?>
				<div class="photo-uploader-field photo-uploader-field-description">
					<label for="PhotoDescription<?=$arParams["INDEX_ON_PAGE"]?>"><?=GetMessage("Description")?></label>
					<textarea name="Description" id="PhotoDescription<?=$arParams["INDEX_ON_PAGE"]?>" class="Description"></textarea>
				</div>
		</div>
	</td></tr>
	<tr class="separator">
		<td class="left"><div class="hr"></div></td>
		<td class="right"></td>
	</tr>
</table>
<?
else:
?>
<div id="file_object_div_<?=$arParams["INDEX_ON_PAGE"]?>" class="image-uploader-form-files"></div>
<div class="empty-clear"></div>
<?
endif;
?>
</div>

<form id="file_object_form_<?=$arParams["INDEX_ON_PAGE"]?>" action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data" class="photo-form"<?
	?> onsubmit="return false;">
	<input type="hidden" name="save_upload" id="save_upload" value="Y" />
	<input type="hidden" name="sessid" id="sessid" value="<?=bitrix_sessid()?>" />
	<input type="hidden" name="FileCount" value="<?=$arParams["UPLOAD_MAX_FILE"]?>" />
	<input type="hidden" name="PackageGuid" value="<?=time()?>" />
	<input type="hidden" name="SECTION_ID" value="<?=$arParams["SECTION_ID"]?>" />
	
	<noscript id="file_object_noscript_<?=$arParams["INDEX_ON_PAGE"]?>">
	<input type="hidden" name="redirect" value="Y" />
	<div class="image-uploader-form-files">
<?
	for ($ii = 1; $ii <= $arParams["UPLOAD_MAX_FILE"]; $ii++):
?>
	<div class="image-uploader-form-file">
		<div class="wd-t"><div class="wd-r"><div class="wd-b"><div class="wd-l"><div class="wd-c">
			<div class="wd-title"><div class="wd-tr"><div class="wd-br"><div class="wd-bl"><div class="wd-tl">
				<div class="wd-title-header"><?=$ii?></div>
			</div></div></div></div></div>
		
		<div class="form">
			<div class="photo-uploader-field photo-uploader-field-file">
				<label for="PhotoFile_<?=$arParams["INDEX_ON_PAGE"]?>_<?=$ii?>"><?=GetMessage("SourceFile")?></label>
				<input type="file" name="SourceFile_<?=$ii?>" id="PhotoFile_<?=$arParams["INDEX_ON_PAGE"]?>_<?=$ii?>" value="" />
			</div>
			
			<div class="photo-uploader-field photo-uploader-field-title">
				<label for="PhotoTitle_<?=$arParams["INDEX_ON_PAGE"]?>_<?=$ii?>"><?=GetMessage("Title")?></label>
				<input type="text" name="Title_<?=$ii?>" id="PhotoTitle_<?=$arParams["INDEX_ON_PAGE"]?>_<?=$ii?>" value="" />
			</div>
<?
	if ($arParams["BEHAVIOUR"] == "USER"):
		if ($arParams["SHOW_PUBLIC"] != "N"): 
?>
			<div class="photo-uploader-field photo-uploader-field-public">
				<input name="Public_<?=$ii?>" id="PhotoPublic_<?=$arParams["INDEX_ON_PAGE"]?>_<?=$ii?>" type="checkbox" value="Y" <?
					?><?=($arParams["PUBLIC_BY_DEFAULT"] == "Y" ? " checked='checked' " : "")?> />
				<label for="PhotoPublic_<?=$arParams["INDEX_ON_PAGE"]?>_<?=$ii?>"><?=GetMessage("Public")?></label>
			</div>
<?
	
		else:
?>
			<input name="Public_<?=$ii?>" type="hidden" value="Y" />
<?
		endif;
	endif;
	if ($arParams["SHOW_TAGS"] == "Y"):
?>
			<div class="photo-uploader-field photo-uploader-field-tags">
				<label for="PhotoTag_<?=$arParams["INDEX_ON_PAGE"]?>_<?=$ii?>"><?=GetMessage("Tags")?></label>
				<input name="Tag_<?=$ii?>" id="PhotoTag_<?=$arParams["INDEX_ON_PAGE"]?>_<?=$ii?>" type="text" />
			</div>
<?
	endif;
?>
			<div class="photo-uploader-field photo-uploader-field-description">
				<label for="PhotoDescription_<?=$arParams["INDEX_ON_PAGE"]?>_<?=$ii?>"><?=GetMessage("Description")?></label>
				<textarea name="Description_<?=$ii?>" id="PhotoDescription_<?=$arParams["INDEX_ON_PAGE"]?>_<?=$ii?>"></textarea>
			</div>
		</div>
		</div></div></div></div></div>
	</div>
<?
	endfor;
?>
	</div>
	<div class="empty-clear"></div>
	</noscript>

<div class="image-uploader-settings">
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="image-uploader-table image-uploader-settings-table image-uploader-<?=(
	$view_mode == "form" ? "form" : ($arParams["TEMPLATE"] == "LIGHT-APPLET" ? "light-applet" : "applet"))?>-bottom">
	<tr class="image-uploader-settings">
		<td>
			<div class="photo-uploader-containers">
				<div class="photo-uploader-container photo-uploader-container-album">
					<label for="photo_album_id<?=$arParams["INDEX_ON_PAGE"]?>"><?=GetMessage("P_TO_ALBUM")?>:</label>
					<select id="photo_album_id<?=$arParams["INDEX_ON_PAGE"]?>" name="photo_album_id">
						<option value="new" <?=(intVal($arParams["SECTION_ID"]) == 0 ? "selected" : "")?>><?=GetMessage("P_IN_NEW_ALBUM")?></option>
<?
		if (is_array($arResult["SECTION_LIST"]))
		{
						?><optgroup><?
			foreach ($arResult["SECTION_LIST"] as $key => $val):
							?><option value="<?=$key?>" <?=($arParams["SECTION_ID"] == $key ? "selected" : "")?>><?=$val?></option><?
			endforeach;
						?></optgroup><?
		}
?>
					</select>
				</div>
<?
		if ($arParams["ORIGINAL_SIZE"] >= 640 || $arParams["ORIGINAL_SIZE"] <= 0)
		{
?>
				<div class="photo-uploader-container photo-uploader-container-resize">
					<label for="photo_resize_size"><?=GetMessage("P_RESIZE")?>:</label>
					<select name="photo_resize_size" id="photo_resize_size" onchange="IUSendData('save=resize&position=' + this.value);">
<?
					if ($arParams["ORIGINAL_SIZE"] <= 0):
?>
						<option value="0" <?=($arWatermark["resize"] == 0 ? "selected" : "")?>><?=GetMessage("P_ORIGINAL")?></option>
						<option value="3" <?=($arWatermark["resize"] == 3 ? "selected" : "")?>>640x480</option>
						<option value="2" <?=($arWatermark["resize"] == 2 ? "selected" : "")?>>800x600</option>
						<option value="1" <?=($arWatermark["resize"] == 1 ? "selected" : "")?>>1024x768</option>
<?
					else:
						?><option value="0" <?=($arWatermark["resize"] == 0 ? "selected" : "")?>>...</option><?
						if ($arParams["ORIGINAL_SIZE"] > 640):
						?><option value="3" <?=($arWatermark["resize"] == 3 ? "selected" : "")?>>640x480</option><?
							if ($arParams["ORIGINAL_SIZE"] > 800):
						?><option value="2" <?=($arWatermark["resize"] == 2 ? "selected" : "")?>>800x600</option><?
								if ($arParams["ORIGINAL_SIZE"] > 1024):
						?><option value="1" <?=($arWatermark["resize"] == 1 ? "selected" : "")?>>1024x768</option><?
								endif;
							endif;
						endif;
					endif;
?>
					</select>
				</div>
<?
		}
		
		if ($arParams["WATERMARK"] == "Y" && $arParams["SHOW_WATERMARK"] == "Y")
		{
?>
				<div class="photo-uploader-container photo-uploader-container-watermark">
					<label for="watermark"><?=GetMessage("P_WATERMARK")?>:</label>
					<input type="text" id="watermark" name="watermark" value="<?=$arWatermark["text"]?>" size="15" <?
						?>onblur="WaterMark.ChangeText(this)" />
					<input type="hidden" id="watermark_copyright" name="watermark_copyright" value="<?=$arWatermark["copyright"]?>" />
					<div id="watermark_copyright_main" class="watermark-container-main">
						<div class="watermark-switcher">
							<a href="#Copyright" onclick="WaterMark.ShowMenu('copyright'); return false;" <?
								?>id="watermark_copyright_switcher" class="<?=$arWatermark["copyright"]?>" <?
								?>title="<?=GetMessage("P_WATERMARK_COPYRIGHT")?>"><span></span></a>
						</div>
						<div class="watermark-container" id="watermark_copyright_container">
							<div class="watermark-container-inner"><?
							foreach (array("show", "hide") as $value):
								$title = ($value == "show" ? 
										GetMessage("P_WATERMARK_COPYRIGHT_SHOW") : GetMessage("P_WATERMARK_COPYRIGHT_HIDE"));
								?><a id="copyright_<?=$value?>" class="<?=($value == $arWatermark["copyright"] ? 'active' : '')?>" <?
									?>href="#Copyright" title="<?=$title?>" onclick="WaterMark.ChangeData('<?=$value?>'); return false;">
									<span><?=$title?></span></a>
								<?
							endforeach;
							?></div>
						</div>
					</div>
		<?

			if (is_array($arParams["WATERMARK_COLORS"]))
			{
		?>
					<input type="hidden" id="watermark_color" name="watermark_color" value="<?=$arWatermark["color"]?>" />
					<div id="watermark_color_main" class="watermark-container-main">
						<div class="watermark-switcher">
							<a href="#CopyrightColor" onclick="WaterMark.ShowMenu('color'); return false;" <?
								?>id="watermark_color_switcher" title="<?=GetMessage("P_WATERMARK_COLOR_TITLE")?>"><?
									?><span style="background-color:#<?=$arWatermark["color"]?>;"></span></a>
						</div>
						<div class="watermark-container" id="watermark_color_container">
							<div class="watermark-container-inner"><?
						foreach ($arParams["WATERMARK_COLORS"] as $value => $title):
							$value = htmlspecialChars(strToLower($value));
							?><a href="#CopyrightColor" onclick="WaterMark.ChangeData('<?=$value?>'); return false;" <?
								?>id="color_<?=$value?>" class="<?=($value == $arWatermark["color"] ? "active" : "")?>" <?
								?>title="<?=$title?>"><span><div class="color_icon" style="background-color:#<?=$value?>;"></div><?=$title?></span></a><?
						endforeach;
						?></div>
						</div>
					</div>
		<?
			}
			
		?>
					<input type="hidden" id="watermark_size" name="watermark_size" value="<?=$arWatermark["size"]?>" />
					<div id="watermark_size_main" class="watermark-container-main">
						<div class="watermark-switcher">
							<a href="#CopyrightSize" onclick="WaterMark.ShowMenu('size'); return false;" <?
								?>id="watermark_size_switcher" class="<?=$arWatermark["size"]?>" <?
								?>title="<?=GetMessage("P_WATERMARK_SIZE_TITLE")?>"><span></span>
							</a>
						</div>
						<div class="watermark-container" id="watermark_size_container">
							<div class="watermark-container-inner"><?
						foreach (array("big", "middle", "small") as $value):
							?><a href="#CopyrightSize" onclick="WaterMark.ChangeData('<?=$value?>'); return false;" <?
								?>id="size_<?=$value?>" class="<?=($value == $arWatermark["size"] ? "active" : "")?>" <?
								?>title="<?=GetMessage("P_WATERMARK_SIZE_".strToUpper($value))?>"><span><?=GetMessage("P_WATERMARK_SIZE_".strToUpper($value))?></span></a><?
						endforeach;
						?></div>
						</div>
					</div>
					<input type="hidden" id="watermark_position" name="watermark_position" value="<?=$arWatermark["position"]?>" />
					<div id="watermark_position_main" class="watermark-container-main">
						<div class="watermark-switcher">
							<a href="#CopyrightPosition" onclick="WaterMark.ShowMenu('position'); return false;" <?
								?>id="watermark_position_switcher" class="<?=$arWatermark["position"]?>" <?
								?>title="<?=GetMessage("P_WATERMARK_POSITION_TITLE")?>"><span></span></a>
						</div>
						<div class="watermark-container" id="watermark_position_container">
							<div class="watermark-container-inner"><?
						foreach ($arWaterMarkPositions as $value => $name):
							?><a href="#CopyrightPosition" onclick="WaterMark.ChangeData('<?=$value?>'); return false;" <?
								?>id="position_<?=$value?>" class="<?=$value?><?=($value == $arWatermark["position"] ? ' active' : '')?>" <?
								?>title="<?=$name?>"><div class="inner1"><div class="inner2"><span></span></div></div></a><?
						endforeach;
							?></div>
						</div>
					</div>

				</div>
				<div class="empty-clear"></div>
			</div>
<?
		}
?>
			<div class="photo-uploader-buttons">
				<div class="photo-uploader-button">
<?
		if ($arParams["TEMPLATE"] != "LIGHT-APPLET")
		{
?>
					<a href="#SendFiles" id="Send_<?=$arParams["INDEX_ON_PAGE"]?>" class="nonactive" onclick="return false;"><?
						?><div><span><?=GetMessage("Send")?></span></div>
					</a>
<?
		}
		else
		{
?>
					<input type="button" id="Send_<?=$arParams["INDEX_ON_PAGE"]?>" class="nonactive" onclick="return false;" <?
						?>value="<?=GetMessage("Send")?>" />
<?
		}
?>
				</div>
				<noscript>
					<input type="submit" value="<?=GetMessage("Send")?>" />
				</noscript>
				<div class="empty-clear"></div>
			</div>
		</td>
	</tr>
	<tr class="bottom-empty"><td><span></span></td></tr>
</table>
</div>
</form>

<script>
if (typeof oText != "object")
	oText = {};
oText["SourceFile"] = "<?=CUtil::JSEscape(GetMessage("SourceFile"))?>";
oText["Title"] = "<?=CUtil::JSEscape(GetMessage("Title"))?>";
oText["Tags"] = "<?=CUtil::JSEscape(GetMessage("Tags"))?>";
oText["Description"] = "<?=CUtil::JSEscape(GetMessage("Description"))?>";
oText["NoPhoto"] = "<?=CUtil::JSEscape(GetMessage("NoPhoto"))?>";
oText["Public"] = "<?=CUtil::JSEscape(GetMessage("Public"))?>";
oText["ErrorNoData"] = "<?=CUtil::JSEscape(str_replace("#POST_MAX_SIZE#", intVal(ini_get('post_max_size')), GetMessage("ErrorNoData")))?>";
setTimeout(to_init, 100);
</script>
<noscript>
<style>
div.image-uploader-objects, div.photo-uploader-button, #ControlsAppletForm {
	display:none;}
</style>
</noscript>