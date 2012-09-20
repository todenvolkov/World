<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
endif;

/********************************************************************
				Input params
********************************************************************/
$arParams["SQUARE"] = ($arParams["SQUARE"] == "N" ? "N" : "Y");
$arParams["THUMBS_SIZE"] = intVal(intVal($arParams["THUMBS_SIZE"]) > 0 ? $arParams["THUMBS_SIZE"] : 80);
/********************************************************************
				Input params
********************************************************************/

if ($arParams["AJAX_CALL"] == "Y"):
	$APPLICATION->RestartBuffer();
?>

<script src="/bitrix/components/bitrix/photogallery.section.edit.icon/templates/.default/script.js"></script>
<?
else:
	CAjax::Init();
endif;
?>

<?if ($arResult["ERROR_MESSAGE"] != ""):?>
<script>
window.oPhotoEditIconDialogError = "<?= CUtil::JSEscape($arResult["ERROR_MESSAGE"]); ?>";
</script>
<?
if ($arParams["AJAX_CALL"] == "Y") {die();}
endif;
?>

<script>
BX.ready(function(){
	if (window.oPhotoEditAlbumDialog)
	{
		window.oPhotoEditAlbumDialog.SetTitle('<?= GetMessage('P_EDIT_ALBUM_ICON_TITLE')?>');
		if (!window.BXPH_MESS)
			BXPH_MESS = {};

		BXPH_MESS.UnknownError = '<?= GetMessage('P_UNKNOWN_ERROR')?>';
	}
});
</script>

<div class="photo-window-edit" id="photo_section_edit_form">
<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="form_photo" id="form_photo" onsubmit="return CheckFormEditIcon(this);" class="photo-form">
	<input type="hidden" name="edit" value="Y" />
	<input type="hidden" name="sessid" value="<?=bitrix_sessid()?>" />
	<input type="hidden" name="IBLOCK_SECTION_ID" value="<?=$arResult["FORM"]["IBLOCK_SECTION_ID"]?>" />
<table class="photo-popup">
	<tr>
		<td class="table-body">
			<div class="photo-info-box photo-info-box-section-edit-icon">
				<div class="photo-info-box-inner">

<p>
<span id="bxph_error_cont" style="display: none; color: red!important;">
<?if (!empty($arResult["ERROR_MESSAGE"]))
	ShowError($arResult["ERROR_MESSAGE"]);
?>
</span>
<?=(count($arResult["ITEMS"]) <= 0 ? GetMessage("P_EMPTY_PHOTO") : GetMessage("P_SELECT_PHOTO"))?>
</p>

<?
if (count($arResult["ITEMS"]) > 0):
$_REQUEST["photos"] = (is_array($_REQUEST["photos"]) ? $_REQUEST["photos"] : array($_REQUEST["photos"]));
?>
<div class="photo-edit-fields photo-edit-fields-section-icon">
	<div id="photo-cover-images">

<?
foreach ($arResult["ITEMS"]	as $key => $arItem):
	if (!is_array($arItem)):
		continue;
	endif;
	$res = array(
		"width" => intVal($arItem["PICTURE"]["WIDTH"]),
		"height" => intVal($arItem["PICTURE"]["HEIGHT"]),
		"left" => 0,
		"top" => 0);
	if ($res["width"] > 0 && $res["height"] > 0):
		$koeff = ($arParams["THUMBS_SIZE"] / min($res["width"], $res["height"]));
		if ($koeff < 1):
			$res["width"] = intVal($res["width"] * $koeff);
			$res["height"] = intVal($res["height"] * $koeff);
		endif;
		$res["left"] = 0 - intVal(($res["width"] - $arParams["THUMBS_SIZE"])/2);
		$res["top"] = 0 - intVal(($res["height"] - $arParams["THUMBS_SIZE"])/2);
	elseif ($res["width"] == 0):
		$res["height"] = $arParams["THUMBS_SIZE"];
	else:
		$res["width"] = $arParams["THUMBS_SIZE"];
	endif;
	$sTitle = htmlspecialcharsEx($arItem["~NAME"]);

?>

	<div class="photo-edit-field photo-edit-field-image photo-photo" style="width:<?=$arParams["THUMBS_SIZE"]?>px; height:<?=$arParams["THUMBS_SIZE"]?>px; overflow:hidden; display:inline;">
		<input type="checkbox" name="photos[]" id="photo_ch_<?=$arItem["ID"]?>" value="<?=$arItem["ID"]?>" <?= (in_array($arItem["ID"], $_REQUEST["photos"]) ? 'checked="checked"' : '')?> /><label for="photo_ch_<?= $arItem["ID"]?>"><img border="0" src="<?=$arItem["PICTURE"]["SRC"]?>" id="photo_img_<?=$arItem["ID"]?>" alt="<?=$sTitle?>" title="<?=$sTitle?>" style="margin-left:<?=$res["left"]?>px; margin-top: <?=$res["top"]?>px; position:static; width:<?=$res["width"]?>px; height:<?=$res["height"]?>px;" /></label>
	</div>
<?
endforeach;
?>
	</div><div class="empty-clear"></div>
</div>

<div id="photo-cover-navigation">
<?
if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 1 && $arResult["NAV_RESULT"]->NavPageNomer > 1):
?>
	<a href="<?=$APPLICATION->GetCurPageParam(
		"PAGEN_".$arResult["NAV_RESULT"]->NavNum."=".($arResult["NAV_RESULT"]->NavPageNomer - 1),
		array("PAGEN_".$arResult["NAV_RESULT"]->NavNum, "AJAX_CALL"))?>" onclick="return __photo_send_by_covers(this);"><?=GetMessage("P_PHOTO_MORE")?></a>
<?
endif;
?>
</div>
<?
endif;

?>
				</div>

<? if ($arParams["AJAX_CALL"] != "Y"):?>
				<div style="margin:20px 0 0 !important;">
					<input type="submit" name="name_submit" value="<?=GetMessage("P_SUBMIT");?>" />
					<input type="submit" name="cancel" value="<?=GetMessage("P_CANCEL");?>" />
				</div>
<?endif;?>

			</div>
		</td>
	</tr>
</table>
</form>
</div>
<?

if ($arParams["AJAX_CALL"] == "Y"):
	die();
else:
?><script>
function CheckFormEditIconCancel(pointer)
{
	if (pointer.form)
	{
		pointer.form.edit.value = 'cancel';
		pointer.form.submit();
	}
	return false;
}
function CheckFormEditIcon()
{
	return true;
}
</script><?
endif;
?>