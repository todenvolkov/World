<?
define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/prolog.php");

$RIGHT_R = $USER->CanDoOperation('security_stat_activity_settings_read');
$RIGHT_W = $USER->CanDoOperation('security_stat_activity_settings_write');
if(!$RIGHT_R && !$RIGHT_W)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$module_id = "statistic";
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

IncludeModuleLangFile(__FILE__);

$aTabs = array(
	array(
		"DIV" => "main",
		"TAB" => GetMessage("SEC_STATACT_MAIN_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_STATACT_MAIN_TAB_TITLE"),
	),
	array(
		"DIV" => "params",
		"TAB" => GetMessage("SEC_STATACT_PARAMS_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_STATACT_PARAMS_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID); // Id of the edited record
$strError = "";
$bVarsFromForm = false;
$bShowForce = false;

if($REQUEST_METHOD == "POST" && ($save || $apply || $DEFENCE_OFF || $DEFENCE_ON) && $RIGHT_W && check_bitrix_sessid())
{
	if(array_key_exists("DEFENCE_OFF", $_POST))
		COption::SetOptionString($module_id, "DEFENCE_ON", "N");
	elseif(array_key_exists("DEFENCE_ON", $_POST))
		COption::SetOptionString($module_id, "DEFENCE_ON", "Y");

	COption::SetOptionInt($module_id, "DEFENCE_STACK_TIME", $DEFENCE_STACK_TIME);
	COption::SetOptionInt($module_id, "DEFENCE_MAX_STACK_HITS", $DEFENCE_MAX_STACK_HITS);
	COption::SetOptionInt($module_id, "DEFENCE_DELAY", $DEFENCE_DELAY);
	COption::SetOptionString($module_id, "DEFENCE_LOG", $DEFENCE_LOG==="Y"? "Y": "N");

	if($save!="" && $_GET["return_url"]!="")
		LocalRedirect($_GET["return_url"]);
	LocalRedirect("/bitrix/admin/security_stat_activity.php?lang=".LANGUAGE_ID.($return_url? "&return_url=".urlencode($_GET["return_url"]): "")."&".$tabControl->ActiveTabParam());
}

$DEFENCE_ON = COption::GetOptionString($module_id, "DEFENCE_ON");
$DEFENCE_STACK_TIME = COption::GetOptionString($module_id, "DEFENCE_STACK_TIME");
$DEFENCE_MAX_STACK_HITS = COption::GetOptionString($module_id, "DEFENCE_MAX_STACK_HITS");
$DEFENCE_DELAY = COption::GetOptionString($module_id, "DEFENCE_DELAY");
$DEFENCE_LOG = COption::GetOptionString($module_id, "DEFENCE_LOG");

$APPLICATION->SetTitle(GetMessage("SEC_STATACT_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($message)
	echo $message->Show();
?>

<form method="POST" action="security_stat_activity.php?lang=<?echo LANGUAGE_ID?><?echo $_GET["return_url"]? "&amp;return_url=".urlencode($_GET["return_url"]): ""?>"  enctype="multipart/form-data" name="editform">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<tr>
	<td valign="top" colspan="2" align="left">
		<?if(COption::GetOptionString($module_id, "DEFENCE_ON")==="Y"):?>
			<span style="color:green;"><b><?echo GetMessage("SEC_STATACT_ON")?>.</b></span>
		<?else:?>
			<span style="color:red;"><b><?echo GetMessage("SEC_STATACT_OFF")?>.</b></span>
		<?endif?>
	</td>
</tr>
<tr>
	<td valign="top" colspan="2" align="left">
		<?if(COption::GetOptionString($module_id, "DEFENCE_ON")==="Y"):?>
			<input type="submit" name="DEFENCE_OFF" value="<?echo GetMessage("SEC_STATACT_BUTTON_OFF")?>"<?if(!$RIGHT_W) echo " disabled"?>>
		<?else:?>
			<input type="submit" name="DEFENCE_ON" value="<?echo GetMessage("SEC_STATACT_BUTTON_ON")?>"<?if(!$RIGHT_W) echo " disabled"?>>
		<?endif?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?echo GetMessage("SEC_STATACT_NOTE")?>
		<?echo EndNote(); ?>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
?>
<?if (CModule::IncludeModule("fileman")):?>
	<tr>
		<td width="40%"><?echo GetMessage("SEC_STATACT_503_TEMPLATE")?>:</td>
		<td width="60%"><a href="/bitrix/admin/fileman_file_edit.php?lang=<?=LANGUAGE_ID?>&amp;full_src=Y&amp;path=%2Fbitrix%2Factivity_limit.php"><?echo GetMessage("SEC_STATACT_GRABBER_EDIT_503_TEMPLATE_LINK")?></a></td>
	</tr>
<?endif;?>
	<tr>
		<td width="40%"><?echo GetMessage("SEC_STATACT_DEFENCE_DELAY")?>:</td>
		<td width="60%"><input size="3" type="text" name="DEFENCE_DELAY" id="DEFENCE_DELAY" value="<?=htmlspecialchars($DEFENCE_DELAY)?>">&nbsp;<?echo GetMessage("SEC_STATACT_DEFENCE_DELAY_MEAS")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SEC_STATACT_DEFENCE_STACK_TIME")?></td>
		<td><input size="3" type="text" name="DEFENCE_STACK_TIME" id="DEFENCE_STACK_TIME" value="<?=htmlspecialchars($DEFENCE_STACK_TIME)?>">&nbsp;<?echo GetMessage("SEC_STATACT_DEFENCE_STACK_TIME_MEAS")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SEC_STATACT_DEFENCE_MAX_HITS")?></td>
		<td><input size="3" type="text" name="DEFENCE_MAX_STACK_HITS" id="DEFENCE_MAX_STACK_HITS" value="<?=htmlspecialchars($DEFENCE_MAX_STACK_HITS)?>">&nbsp;<?echo GetMessage("SEC_STATACT_DEFENCE_MAX_HITS_MEAS")?></td>
	</tr>
	<tr>
		<td nowrap><label for="DEFENCE_LOG"><?echo GetMessage("SEC_STATACT_DEFENCE_LOG", array("#HREF#"=>"/bitrix/admin/event_log.php?lang=".LANGUAGE_ID."&set_filter=Y&find_type=audit_type_id&find_audit_type[]=STAT_ACTIVITY_LIMIT"))?></label></td>
		<td><?echo InputType("checkbox", "DEFENCE_LOG", "Y", $DEFENCE_LOG)?></td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=>(!$RIGHT_W),
		"back_url"=>$_GET["return_url"]? $_GET["return_url"]: "security_stat_activity.php?lang=".LANG,
	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<?
$tabControl->End();
?>
</form>

<?
$tabControl->ShowWarnings("editform", $message);
?>

<?/*echo BeginNote();?>
<span class="required">*</span><?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();*/?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>