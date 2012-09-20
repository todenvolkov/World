<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

$sDocPath = $APPLICATION->GetCurPage();

if(!empty($arAuthResult))
{
	if(!is_array($arAuthResult))
		$arAuthResult = array("MESSAGE"=>$arAuthResult, "TYPE"=>"ERROR");
	CAdminMessage::ShowMessage(array(
		"MESSAGE"=>($arAuthResult["TYPE"] == "ERROR"? GetMessage("admin_authorize_error"):GetMessage("admin_authorize_info")), 
		"DETAILS"=>$arAuthResult["MESSAGE"], 
		"TYPE"=>$arAuthResult["TYPE"]
	));
}
?>	
<form name="form_auth" method="post" target="_top" action="<?echo htmlspecialchars($sDocPath."?forgot_password=yes".(($s=DeleteParam(array("forgot_password"))) == ""? "":"&".$s))?>">
<input type="hidden" name="AUTH_FORM" value="Y">
<input type="hidden" name="TYPE" value="SEND_PWD">

<div class="bx-auth-form">
	<div class="bx-auth-header"><?=GetMessage("AUTH_GET_CHECK_STRING")?></div>

	<div class="bx-auth-picture"></div>
	<div class="bx-auth-table">
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td class="label"><?=GetMessage("AUTH_LOGIN")?>:</td>
			<td><input type="text" name="USER_LOGIN" maxlength="50" size="20" value="<?echo htmlspecialchars($last_login)?>" class="input-text"></td>
		</tr>
		<tr> 
			<td></td>
			<td><label class="bx-label"><?=GetMessage("AUTH_OR")?></label></td>
		</tr>
		<tr>
			<td class="label">E-Mail:</td>
			<td><input type="text" name="USER_EMAIL" maxlength="255" size="20" class="input-text"></td>
		</tr>
		<tr> 
			<td></td>
			<td><input type="submit" name="send_account_info" value="<?=GetMessage("AUTH_SEND")?>"></td>
		</tr>
	</table>
	</div>
	<br clear="all">

	<div class="bx-auth-footer">
		<p><?=GetMessage("AUTH_FORGOT_PASSWORD_1")?></p>
		<p><?=GetMessage("AUTH_MESS_1")?> <a href="<?echo htmlspecialchars($sDocPath."?change_password=yes".($s<>""? "&".$s:""));?>"><?=GetMessage("AUTH_CHANGE_FORM")?></a>.</p>
		<p><?echo GetMessage("admin_authorize_back")?> <a href="<?echo htmlspecialchars($sDocPath.($s == ""? "":"?$s"))?>"><?echo GetMessage("admin_authorize_back_form")?></a>.</p>
	</div>
</div>
</form>

<script type="text/javascript">
document.form_auth.USER_LOGIN.focus();
</script>