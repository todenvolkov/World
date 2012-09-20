<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_header.php',Array(),Array("MODE"=>"php"));
?>
<p>&nbsp;</p>
<p class="Header">Восстановление пароля</p>
<p>&nbsp;</p>
<span class="active">
<?
ShowMessage($arParams["~AUTH_RESULT"]);
?>
</span>
<p>&nbsp;</p>
<div class="content-form forgot-form">
<div class="fields">
<form name="bform" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
<?
if (strlen($arResult["BACKURL"]) > 0)
{
?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?
}
?>
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="SEND_PWD">
		<div class="field">
			<label class="field-title"><?=GetMessage("AUTH_LOGIN")?></label>
			<div class="form-input"><input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" /></div>
		</div>
		<p>&nbsp;</p>
		<div class="field">
			<label class="field-title">E-Mail</label>
			<div class="form-input"><input type="text" name="USER_EMAIL" maxlength="255" /></div>
		</div>
		<p>&nbsp;</p>
		<div class="field field-button"><input type="submit" class="input-submit" name="send_account_info" value="<?=GetMessage("AUTH_SEND")?>" /></div>
	<p>&nbsp;</p>
	<div class="field"><?=GetMessage("AUTH_FORGOT_PASSWORD_1")?></div>
<!--<div class="field"><a href="<?=$arResult["AUTH_AUTH_URL"]?>"><b><?=GetMessage("AUTH_AUTH")?></b></a></div> -->
</form>
<script type="text/javascript">
document.bform.USER_LOGIN.focus();
</script>
</div>
</div>
<?
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/private_footer.php',Array(),Array("MODE"=>"php"));
?>
