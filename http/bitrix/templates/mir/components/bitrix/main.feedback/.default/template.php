<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>

<?if(!empty($arResult["ERROR_MESSAGE"]))
{
	foreach($arResult["ERROR_MESSAGE"] as $v)
		ShowError($v);
}
if(strlen($arResult["OK_MESSAGE"]) > 0)
{
	ShowNote($arResult["OK_MESSAGE"]);
}
?>

<form action="" method="POST">
<?=bitrix_sessid_post()?>
<div class="content-form feedback-form">
	<div class="fields">
		<div class="field field-name">
		<label class="field-title"><?=GetMessage("MFT_NAME")?></label>
		<div class="form-input"><input type="text" name="user_name" value="<?=$arResult["AUTHOR_NAME"]?>"></div>	
		</div>
	
		<div class="field field-property_email">
		<label class="field-title"><?=GetMessage("MFT_EMAIL")?></label>
		<div class="form-input"><input type="text" name="user_email" value="<?=$arResult["AUTHOR_EMAIL"]?>"></div>	
		</div>
			
		<div class="field field-detailtext">
		<label class="field-title"><?=GetMessage("MFT_MESSAGE")?></label>
		<div class="form-input"><textarea name="MESSAGE" rows="5" cols="40"><?=$arResult["MESSAGE"]?></textarea></div>	
		</div>
	
		<?if($arParams["USE_CAPTCHA"] == "Y"):?>
		<div class="field field-captcha">
		<label class="field-title"><?=GetMessage("MFT_CAPTCHA_CODE")?></label>
		<div class="form-input">
			<input type="text" name="captcha_word" size="30" maxlength="50" value="">
			<input type="hidden" name="captcha_sid" value="<?=$arResult["capCode"]?>"><br />
			<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["capCode"]?>" width="180" height="40" alt="CAPTCHA">
		</div>	
		</div>
		<?endif;?>
	
		<div class="field field-button">
		<input type="submit" class="input-submit" name="submit" value="<?=GetMessage("MFT_SUBMIT")?>">
		</div>
	</div>
</div>	
</form>