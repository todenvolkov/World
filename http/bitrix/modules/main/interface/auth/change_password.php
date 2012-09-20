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
$GROUP_POLICY = CUser::GetGroupPolicy($USER->GetID());
?>
<form name="form_auth" method="POST" action="<?echo htmlspecialchars($sDocPath."?change_password=yes".(($s=DeleteParam(array("change_password"))) == ""? "":"&".$s))?>">
<input type="hidden" name="AUTH_FORM" value="Y">
<input type="hidden" name="TYPE" value="CHANGE_PWD">

<div class="bx-auth-form">
	<div class="bx-auth-header"><?=GetMessage("AUTH_CHANGE_PASSWORD")?></div>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td>
	<div class="bx-auth-picture"></div>
	</td>
	<td>
	<div class="bx-auth-table">
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td class="bx-auth-label"><?=GetMessage("AUTH_LOGIN")?>:</td>
			<td><input type="text" name="USER_LOGIN" size="30" maxlength="50" value="<?echo (strlen($USER_LOGIN)>0) ? htmlspecialchars($USER_LOGIN) : htmlspecialchars($last_login)?>" class="bx-auth-input-text"></td>
		</tr>
		<tr>
			<td class="bx-auth-label"><?=GetMessage("AUTH_CHECKWORD")?>:</td>
			<td><input type="text" name="USER_CHECKWORD" size="30" maxlength="50" value="<?echo htmlspecialchars($USER_CHECKWORD)?>" class="bx-auth-input-text"></td>
		</tr>
		<tr>
			<td class="bx-auth-label"><?=GetMessage("AUTH_NEW_PASSWORD")?>:</td>
			<td><input type="password" name="USER_PASSWORD" size="30" maxlength="50" value="<?echo htmlspecialchars($USER_PASSWORD)?>" class="bx-auth-input-text"></td>
		</tr>
		<tr>
			<td class="bx-auth-label"><?=GetMessage("AUTH_NEW_PASSWORD_CONFIRM")?>:</td>
			<td><input type="password" name="USER_CONFIRM_PASSWORD" size="30" maxlength="50" value="<?echo htmlspecialchars($USER_CONFIRM_PASSWORD)?>" class="bx-auth-input-text"></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>"></td>
		</tr>
	</table>
	</div>
	</td>
</tr>
</table>

	<div class="bx-auth-footer">
		<p><?echo GetMessage("admin_authorize_required")?></p>
		<p><?echo GetMessage("admin_authorize_back")?> <a href="<?echo htmlspecialchars($sDocPath.($s == ""? "":"?$s"))?>"><?echo GetMessage("admin_authorize_back_form")?></a>.</p>
	</div>
</div>

</form>

<script type="text/javascript">
document.form_auth.USER_LOGIN.focus();
</script>