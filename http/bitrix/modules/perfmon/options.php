<?
$module_id = "perfmon";
$RIGHT = $APPLICATION->GetGroupRight($module_id);
if($RIGHT >= "R") :

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$arAllOptions = Array(
	array("max_display_url", GetMessage("PERFMON_OPTIONS_MAX_DISPLAY_URL")." ", array("text", 6)),
	array("table_browser", GetMessage("PERFMON_OPTIONS_TABLE_BROWSER")." ", array("checkbox")),
	array("sql_log", GetMessage("PERFMON_OPTIONS_SQL_LOG")." ", array("checkbox")),
	array("warning_log", GetMessage("PERFMON_OPTIONS_WARNING_LOG")." ", array("checkbox")),
);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "perfmon_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "perfmon_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

CModule::IncludeModule($module_id);

if($REQUEST_METHOD=="POST" && strlen($Update.$Apply.$RestoreDefaults) > 0 && $RIGHT=="W" && check_bitrix_sessid())
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

	if($_REQUEST["clear_data"] === "y")
	{
		CPerfomanceComponent::Clear();
		CPerfomanceSQL::Clear();
		CPerfomanceHit::Clear();
		CPerfomanceError::Clear();
	}

	if(array_key_exists("ACTIVE", $_REQUEST))
	{
		$ACTIVE = intval($_REQUEST["ACTIVE"]);
		CPerfomanceKeeper::SetActive($ACTIVE > 0, time() + $ACTIVE);
	}

	if(strlen($RestoreDefaults)>0)
	{
		COption::RemoveOption("perfmon");
	}
	else
	{
		foreach($arAllOptions as $arOption)
		{
			$name=$arOption[0];
			$val=$_REQUEST[$name];
			if($arOption[2][0]=="checkbox" && $val!="Y")
				$val="N";
			COption::SetOptionString("perfmon", $name, $val, $arOption[1]);
		}
	}

	ob_start();
	$Update = $Update.$Apply;
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
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

?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();

	foreach($arAllOptions as $arOption):
		$val = COption::GetOptionString("perfmon", $arOption[0]);
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
			<?endif?>
		</td>
	</tr>
	<?endforeach?>
	<? $ACTIVE = CPerfomanceKeeper::IsActive(); ?>
	<tr>
		<td valign="top" width="50%">
			<?echo GetMessage("PERFMON_OPT_ACTIVE")?>:
		</td>
		<td valign="middle" width="50%">
		<?if ($ACTIVE):?>
			<?echo GetMessage("PERFMON_OPT_ACTIVE_Y")?>
		<?else:?>
			<?echo GetMessage("PERFMON_OPT_ACTIVE_N")?>
		</td>
		<?endif;?>
	</tr>
	<?if ($ACTIVE):?>
	<tr>
		<td valign="top" width="50%">
			<?echo GetMessage("PERFMON_OPT_ACTIVE_TO")?>:
		</td>
		<td valign="top" width="50%">
			<?
			$interval = COption::GetOptionInt("perfmon", "end_time") - time();
			$hours = intval($interval / 3600);
			$interval -= $hours * 3600;
			$minutes = intval($interval / 60 );
			$interval -= $minutes * 60;
			$seconds = intval($interval);
			echo GetMessage("PERFMON_OPT_MINUTES", array("#HOURS#" => $hours, "#MINUTES#" => $minutes, "#SECONDS#" => $seconds));
			?>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%">
			<label for="ACTIVE"><?echo GetMessage("PERFMON_OPT_SET_IN_ACTIVE")?></label>:
		</td>
		<td valign="top" width="50%">
			<input type="checkbox" name="ACTIVE" value="0" id="ACTIVE">
		</td>
	</tr>
	<?else:?>
	<tr>
		<td valign="top" width="50%">
			<?echo GetMessage("PERFMON_OPT_SET_ACTIVE")?>:
		</td>
		<td valign="top" width="50%">
			<select name="ACTIVE">
				<option value="0"><?echo GetMessage("PERFMON_OPT_INTERVAL_NO")?></option>
				<option value="60"><?echo GetMessage("PERFMON_OPT_INTERVAL_60_SEC")?></option>
				<option value="300"><?echo GetMessage("PERFMON_OPT_INTERVAL_300_SEC")?></option>
				<option value="600"><?echo GetMessage("PERFMON_OPT_INTERVAL_600_SEC")?></option>
				<option value="1800"><?echo GetMessage("PERFMON_OPT_INTERVAL_1800_SEC")?></option>
				<option value="3600"><?echo GetMessage("PERFMON_OPT_INTERVAL_3600_SEC")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top" width="50%">
			<label for="clear_data"><?echo GetMessage("PERFMON_OPT_CLEAR_DATA")?></label>
		</td>
		<td valign="top" width="50%">
			<input type="checkbox" name="clear_data" id="clear_data" value="y">
		</td>
	</tr>
	<?endif;?>
<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
	<input <?if ($RIGHT<"W") echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
	<input <?if ($RIGHT<"W") echo "disabled" ?> type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input <?if ($RIGHT<"W") echo "disabled" ?> type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
<?endif;?>
