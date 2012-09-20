<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
endif;
?>

<? if ($arResult["SECTION"]["ID"] > 0 && $arParams["ACTION"] != "NEW"): ?>
	<noindex>
	<div class="photo-controls photo-controls-albums">
		<ul class="photo-controls">
<? if ($arResult["SECTION"]["ELEMENTS_CNT"] > 0): ?>
			<li class="photo-control photo-control-first photo-control-album-edit-icon">
				<a rel="nofollow" href="<?=$arResult["SECTION"]["EDIT_ICON_LINK"]?>"><span><?=GetMessage("P_SECTION_EDIT_ICON")?></span></a>
			</li>
<?endif; ?>
			<li class="photo-control photo-control-last photo-control-album-drop">
				<a rel="nofollow" href="<?=$arResult["SECTION"]["DROP_LINK"]?>" onclick="return confirm('<?=CUtil::JSEscape(GetMessage('P_SECTION_DELETE_ASK'))?>');"><?
					?><span><?=GetMessage("P_SECTION_DELETE")?></span></a>
			</li>
		</ul>
		<div class="empty-clear"></div>
	</div>
	</noindex>
<? endif; ?>

<?
if ($arParams["AJAX_CALL"] == "Y")
	$GLOBALS["APPLICATION"]->RestartBuffer();
?>
<script>window.oPhotoEditAlbumDialogError = false;</script>

<?if ($arResult["ERROR_MESSAGE"] != ""):?>
<script>
window.oPhotoEditAlbumDialogError = "<?= CUtil::JSEscape($arResult["ERROR_MESSAGE"]); ?>";
</script>
<?
if ($arParams["AJAX_CALL"] == "Y") {die();}
endif;
?>

<script>
BX.ready(function(){
	if (!window.BXPH_MESS)
		BXPH_MESS = {};
	BXPH_MESS.UnknownError = '<?= GetMessage('P_UNKNOWN_ERROR')?>';

	if (window.oPhotoEditAlbumDialog)
	{
		window.oPhotoEditAlbumDialog.SetTitle('<?= GetMessage('P_EDIT_ALBUM_TITLE')?>');
	}
	else if (window.oPhotoEditAlbumDialogError)
	{
		var pError = BX('bxph_error_row');
		if (pError)
		{
			pError.style.display = "";
			pError.cells[0].innerHTML = window.oPhotoEditAlbumDialogError;
		}
	}

	<? if ($arParams["AJAX_CALL"] != "Y"):?>
	if (BX('bxph_pass_row'))
	{
		BX('bxph_use_password').onclick = function()
		{
			var ch = !!this.checked;
			BX('bxph_pass_row').style.display = ch ? '' : 'none';
			BX('bxph_photo_password').disabled = !ch;
			if (ch)
				BX.focus(BX('bxph_photo_password'));

			if (window.oEditAlbumDialog)
				oEditAlbumDialog.adjustSizeEx();
		};
	}
	<?endif;?>
});
</script>


<div class="photo-window-edit" id="photo_section_edit_form">
<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="form_photo" id="form_photo" onsubmit="if (window.CheckForm) return CheckForm(this);" class="photo-form">
	<input type="hidden" name="save_edit" value="Y" />
	<input type="hidden" name="edit" value="Y" />
	<input type="hidden" name="sessid" value="<?=bitrix_sessid()?>" />
	<input type="hidden" name="IBLOCK_SECTION_ID" value="<?=$arResult["FORM"]["IBLOCK_SECTION_ID"]?>" />

	<table class="photo-dialog-table" <? if ($arParams["AJAX_CALL"] != "Y") echo 'style="width: 600px;"'?>>
	<tr id="bxph_error_row" style="display: none;">
		<td class="photo-dialog-warning" colSpan="2" style="color: red!important;"></td>
	</tr>
	<? if ($arParams["ACTION"] != "CHANGE_ICON"):?>
	<tr>
		<td class="photo-dialog-prop-title photo-dialog-req"><label for="bxph_name"><?=GetMessage("P_ALBUM_NAME")?>:</label></td>
		<td class="photo-dialog-prop-param photo-inp-width">
		<input type="text" name="NAME" id="bxph_name" value="<?=$arResult["FORM"]["NAME"]?>" />
		</td>
	</tr>
	<tr>
		<td class="photo-dialog-prop-title"><label for="DATE_CREATE"><?=GetMessage("P_ALBUM_DATE")?>:</label></td>
		<td class="photo-dialog-prop-param">
		<?$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:system.field.edit",
				$arResult["FORM"]["~DATE"]["USER_TYPE"]["USER_TYPE_ID"],
				array(
					"bVarsFromForm" => $arResult["bVarsFromForm"],
					"arUserField" => $arResult["FORM"]["~DATE"],
					"form_name" => "form_photo"),
				$component,
			array("HIDE_ICONS"=>"Y"));?>
		</td>
	</tr>

	<tr>
		<td class="photo-dialog-prop-title" valign="top"><label for="bxph_description"><?=GetMessage("P_ALBUM_DESCRIPTION")?>:</label></td>
		<td class="photo-dialog-prop-param"><textarea id="bxph_description" name="DESCRIPTION"><?=$arResult["FORM"]["DESCRIPTION"]?></textarea></td>
	</tr>


	<? if (!empty($arResult["FORM"]["~PASSWORD"]["VALUE"])): /* pasword already exist - we can only drop it down*/?>
	<tr>
		<td class="photo-dialog-prop-title">
		<input type="hidden" id="DROP_PASSWORD" name="DROP_PASSWORD" value="N" />
		<input type="checkbox" id="USE_PASSWORD" name="USE_PASSWORD" value="Y" onclick="this.form.DROP_PASSWORD.value = this.checked ? 'N' : 'Y';" checked="checked" />
		</td>
		<td class="photo-dialog-prop-param"><label for="USE_PASSWORD"><?=GetMessage("P_SET_PASSWORD")?></label></td>
	</tr>
	<?else:?>
	<tr>
		<td class="photo-dialog-prop-title"><input type="checkbox" id="bxph_use_password" name="USE_PASSWORD" value="Y"/></td>
		<td class="photo-dialog-prop-param"><label for="bxph_use_password"><?=GetMessage("P_SET_PASSWORD")?></label></td>
	</tr>
	<tr id="bxph_pass_row" style="display: none;">
		<td class="photo-dialog-prop-title"></td>
		<td class="photo-dialog-prop-param"><label for="bxph_photo_password"><?=GetMessage("P_PASSWORD")?>:</label>&nbsp;&nbsp;&nbsp;<input type="password" name="PASSWORD" id="bxph_photo_password" value="" disabled="disabled" /></td>
	</tr>
	<?endif;/* !empty($arResult["FORM"]["~PASSWORD"]["VALUE"]) */?>

	<?endif; /* $arParams["ACTION"] != "CHANGE_ICON" */?>

	<? if ($arParams["AJAX_CALL"] != "Y"):?>
	<tr>
		<td colSpan="2">
			<br />
			<input type="submit" name="name_submit" value="<?=GetMessage("P_SUBMIT");?>" />
			<input type="submit" name="cancel" value="<?=GetMessage("P_CANCEL");?>" />
		</td>
	</tr>
	<?endif;?>
	</table>
</form>
</div>

<?
if ($arParams["AJAX_CALL"] == "Y"):
?>

<link href="/bitrix/components/bitrix/main.calendar/templates/.default/style.css" type="text/css" rel="stylesheet" />
<?
	$GLOBALS["APPLICATION"]->ShowHeadScripts();
	$GLOBALS["APPLICATION"]->ShowHeadStrings();
	die();
endif;
?>