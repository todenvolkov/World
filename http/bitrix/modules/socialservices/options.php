<?
if(!$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$module_id = "socialservices";
CModule::IncludeModule($module_id);

$GLOBALS["APPLICATION"]->SetAdditionalCSS("/bitrix/js/socialservices/css/ss.css");

$oAuthManager = new CSocServAuthManager();
$arServices = $oAuthManager->GetAuthServices();
$arOptions = $oAuthManager->GetSettings();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["Update"].$_POST["Apply"].$_POST["RestoreDefaults"] <> '' && check_bitrix_sessid())
{
	if($_POST["RestoreDefaults"] <> '')
	{
		COption::RemoveOption($module_id);
	}
	else
	{
		COption::SetOptionString("socialservices", "auth_services", serialize($_POST["AUTH_SERVICES"]));
		foreach($arOptions as $option)
			__AdmSettingsSaveOption($module_id, $option);
	}

	if(strlen($_REQUEST["back_url_settings"]) > 0)
	{
		if($_POST["Apply"] <> '' || $_POST["RestoreDefaults"] <> '')
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect($_REQUEST["back_url_settings"]);
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
	}
}

?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=urlencode(LANGUAGE_ID)?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr valign="top">
		<td width="50%"><?echo GetMessage("soc_serv_opt_allow")?></td>
		<td valign="middle" width="50%">
<script type="text/javascript">
function MoveRowUp(a)
{
	var table = BX('auth_services_table');
	var index = BX.findParent(a, {'tag':'tr'}).rowIndex;
	if(index == 0)
		return;
	table.rows[index].parentNode.insertBefore(table.rows[index], table.rows[index-1]);
	a.focus();
}
function MoveRowDown(a)
{
	var table = BX('auth_services_table');
	var index = BX.findParent(a, {'tag':'tr'}).rowIndex;
	if(index == table.rows.length-1)
		return;
	table.rows[index].parentNode.insertBefore(table.rows[index+1], table.rows[index]);
	a.focus();
}
</script>
			<table cellpadding="0" cellspacing="3" border="0" width="" id="auth_services_table">
<?
foreach($arServices as $id=>$service):
?>
				<tr>
					<td><input type="hidden" name="AUTH_SERVICES[<?=htmlspecialchars($id)?>]" value="N"><input type="checkbox" name="AUTH_SERVICES[<?=htmlspecialchars($id)?>]" id="AUTH_SERVICES_<?=htmlspecialchars($id)?>" value="Y"<?if($service["__active"] == true) echo " checked"?> <?if($service["DISABLED"] == true) echo " disabled"?>></td>
					<td><div class="bx-ss-icon <?=htmlspecialchars($service["ICON"])?>"></div></td>
					<td><label for="AUTH_SERVICES_<?=htmlspecialchars($id)?>"><?=htmlspecialchars($service["NAME"])?></label></td>
					<td>&nbsp;</td>
					<td><a href="javascript:void(0)" onclick="MoveRowUp(this)"><img src="/bitrix/images/socialservices/up.gif" width="16" height="16" alt="<?echo GetMessage("soc_serv_opt_up")?>" border="0"></a></td>
					<td><a href="javascript:void(0)" onclick="MoveRowDown(this)"><img src="/bitrix/images/socialservices/down.gif" width="16" height="16" alt="<?echo GetMessage("soc_serv_opt_down")?>" border="0"></a></td>
				</tr>
<?endforeach?>
			</table>
		</td>
	</tr>
<?
foreach($arOptions as $option)
{
	if(!is_array($option))
		$option = GetMessage("soc_serv_opt_settings_of", array("#SERVICE#"=>$option));
	__AdmSettingsDrawRow($module_id, $option);
}
?>
<?$tabControl->Buttons();?>
	<?if($_REQUEST["back_url_settings"] <> ''):?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
	<?endif?>
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if($_REQUEST["back_url_settings"] <> ''):?>
	<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
	<input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" onclick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
