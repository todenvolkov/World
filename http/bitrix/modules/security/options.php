<?
$module_id = "security";
$RIGHT_R = $USER->CanDoOperation('security_module_settings_read');
$RIGHT_W = $USER->CanDoOperation('security_module_settings_write');
if($RIGHT_R || $RIGHT_W) :

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$arAllOptions = Array(
	array("ipcheck_allow_self_block", GetMessage("SEC_OPTIONS_IPCHECK_ALLOW_SELF_BLOCK")." ", array("checkbox")),
	array("ipcheck_disable_file", GetMessage("SEC_OPTIONS_IPCHECK_DISABLE_FILE")." ", array("text", 45)),
);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "security_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "security_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

CModule::IncludeModule($module_id);

if($REQUEST_METHOD=="POST" && strlen($Update.$Apply.$RestoreDefaults) > 0 && $RIGHT_W && check_bitrix_sessid())
{

	if(strlen($RestoreDefaults)>0)
	{
		COption::RemoveOption($module_id);
	}
	else
	{
		foreach($arAllOptions as $arOption)
		{
			$name = $arOption[0];
			$val = trim($_REQUEST[$name], " \t\n\r");
			if($arOption[2][0]=="checkbox" && $val!="Y")
				$val="N";
			COption::SetOptionString($module_id, $name, $val, $arOption[1]);
		}
	}

	ob_start();
	$Update = $Update.$Apply;
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights2.php");
	ob_end_clean();

	if(strlen($_REQUEST["back_url_settings"]) > 0)
	{
		if((strlen($Apply) > 0) || (strlen($RestoreDefaults) > 0))
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect($_REQUEST["back_url_settings"]);
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
	}
}

$message = CSecurityIPRule::CheckAntiFile(true);
if($message)
	echo $message->Show();

?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();

	foreach($arAllOptions as $arOption):
		$val = COption::GetOptionString($module_id, $arOption[0]);
		$type = $arOption[2];
	?>
	<tr>
		<td valign="top" width="50%">
			<label for="<?echo htmlspecialchars($arOption[0])?>"><?echo $arOption[1]?>:</label>
		<td valign="top" width="50%">
			<?if($type[0]=="checkbox"):?>
				<input type="checkbox" name="<?echo htmlspecialchars($arOption[0])?>" id="<?echo htmlspecialchars($arOption[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
			<?elseif($type[0]=="text"):?>
				<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialchars($val)?>" name="<?echo htmlspecialchars($arOption[0])?>" id="<?echo htmlspecialchars($arOption[0])?>">
			<?elseif($type[0]=="textarea"):?>
				<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialchars($arOption[0])?>" id="<?echo htmlspecialchars($arOption[0])?>"><?echo htmlspecialchars($val)?></textarea>
			<?elseif($type[0]=="selectbox"):
				$arr = $type[1];
				$arr_keys = array_keys($arr);
				?><select name="<?echo htmlspecialchars($arOption[0])?>"><?
					for($j=0; $j<count($arr_keys); $j++):
						?><option value="<?echo $arr_keys[$j]?>"<?if($val==$arr_keys[$j])echo" selected"?>><?echo htmlspecialchars($arr[$arr_keys[$j]])?></option><?
					endfor;
					?></select><?
			endif?>
		</td>
	</tr>
	<?endforeach?>
<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights2.php");?>
<?$tabControl->Buttons();?>
	<input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
	<input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input <?if(!$RIGHT_W) echo "disabled" ?> type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
<?endif;?>
