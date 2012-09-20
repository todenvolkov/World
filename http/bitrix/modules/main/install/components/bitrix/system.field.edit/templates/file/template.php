<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><div class="fields files" id="main_<?=$arParams["arUserField"]["FIELD_NAME"]?>"><?
$postFix = ($arParams["arUserField"]["MULTIPLE"] == "Y" ? "[]" : "");
foreach ($arResult["VALUE"] as $res):
	?><div class="fields files"><input type="hidden" name="<?=$arParams["arUserField"]["~FIELD_NAME"]?>_old_id<?=$postFix?>" value="<?=$res?>" /><?
	?><?=CFile::InputFile($arParams["arUserField"]["FIELD_NAME"], 0, $res, false, 0, "", "", 0, "", ' value="'.$res.'"').
			'<br>'.
			CFile::ShowImage($res);
	?></div><?
endforeach;
?></div><?
if ($arParams["arUserField"]["MULTIPLE"] == "Y" && $arParams["SHOW_BUTTON"] != "N"):?>
<input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onClick="addElement('<?=$arParams["arUserField"]["FIELD_NAME"]?>', this)">
<?endif;?>