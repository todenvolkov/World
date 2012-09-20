<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//$arResult['USE_OPENID'] = $arResult['USE_LIVEID'] = 'Y';
?>
<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_header.php',Array(),Array("MODE"=>"php"));
?>
<p>&nbsp;</p>
<p class="Header">Вход на сайт</p>
<p>&nbsp;</p>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td width="250">
<span class="active">
<?
	ShowMessage($arParams["~AUTH_RESULT"]);
	ShowMessage($arResult['ERROR_MESSAGE']);
?>
</span>
	<div class="content-form login-form">
	<div class="fields">
		<form name="form_auth" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
			<input type="hidden" name="AUTH_FORM" value="Y" />
			<input type="hidden" name="TYPE" value="AUTH" />
			<?if (strlen($arResult["BACKURL"]) > 0):?>
			<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
			<?endif?>
			<?
			foreach ($arResult["POST"] as $key => $value)
			{
			?>
				<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
			<?
			}
			?>
			<p>&nbsp;</p>
			<div class="field">
				<label class="field-title"><?=GetMessage("AUTH_LOGIN")?></label>
				<div class="form-input"><input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" class="input-field" /></div>
			</div>	
			<p>&nbsp;</p>
			<div class="field">
				<label class="field-title"><?=GetMessage("AUTH_PASSWORD")?></label>
				<div class="form-input"><input type="password" name="USER_PASSWORD" maxlength="50" class="input-field" /></div>
			</div>
                       	<?if($arResult["CAPTCHA_CODE"]):?>
			<p>&nbsp;</p>
			<div class="field">
				<label class="field-title"><?=GetMessage("AUTH_CAPTCHA_PROMT")?></label>
				<div class="form-input"><input type="text" name="captcha_word" maxlength="50" class="input-field" /></div>
				<p style="clear: left;"><input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" /><img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></p>
			</div>
			<?endif;?>
			<?
			if ($arResult["STORE_PASSWORD"] == "Y")
			{
			?>
			<p>&nbsp;</p>
			<div class="field field-option">
				<input type="checkbox" id="USER_REMEMBER" name="USER_REMEMBER" value="Y" /><label for="USER_REMEMBER">&nbsp;<?=GetMessage("AUTH_REMEMBER_ME")?></label>
			</div>
			<?
			}
			?>
			<p>&nbsp;</p>
			<div class="field field-button">
				<input type="submit" class="input-submit" name="Login" value="<?=GetMessage("AUTH_AUTHORIZE")?>" />
			</div>
		</form>
	</div>
	</div>
</td>
<td valign="top" class="C10" width="500">
	<?
	if ($arParams["NOT_SHOW_LINKS"] != "Y")
	{
	?>
	<?
	if($arResult["NEW_USER_REGISTRATION"] == "Y" && $arParams["AUTHORIZE_REGISTRATION"] != "Y")
	{
	?>
		<div class="field">
			<p><a href="<?=$arResult["AUTH_REGISTER_URL"]?>" rel="nofollow"><b><?=GetMessage("AUTH_REGISTER")?></b></a></p><br />
			<p><?=GetMessage("AUTH_FIRST_ONE")?><br><a href="<?=$arResult["AUTH_REGISTER_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_REG_FORM")?></a></p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
		</div>
	<?
	}
	?>
	<div class="field">
		<a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" rel="nofollow"><b><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></b></a><br />
		<?=GetMessage("AUTH_GO")?> <a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_GO_AUTH_FORM")?></a><br />
		<?=GetMessage("AUTH_MESS_1")?> <a href="<?=$arResult["AUTH_CHANGE_PASSWORD_URL"]?>" rel="nofollow"><?=GetMessage("AUTH_CHANGE_FORM")?></a>
	</div>
	<?
	}
	?>
</td>
</tr>
</table>
<script type="text/javascript">
<?
if (strlen($arResult["LAST_LOGIN"])>0)
{
?>
try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
<?
}
else
{
?>
try{document.form_auth.USER_LOGIN.focus();}catch(e){}
<?
}
?>
</script>

<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_footer.php',Array(),Array("MODE"=>"php"));
?>
