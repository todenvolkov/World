<?
$module_id = "bizproc";
$BIZPROC_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($BIZPROC_RIGHT>="R") :

if (strlen($strWarning) > 0)
	CAdminMessage::ShowMessage($strWarning);

global $MESS;
include(GetLangFileName($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/", "/options.php"));
include(GetLangFileName($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/lang/", "/options.php"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("BIZPROC_TAB_SET"), "ICON" => "bizproc_settings", "TITLE" => GetMessage("BIZPROC_TAB_SET_ALT")),
//	array("DIV" => "edit2", "TAB" => GetMessage("BIZPROC_TAB_RIGHTS"), "ICON" => "bizproc_settings", "TITLE" => GetMessage("BIZPROC_TAB_RIGHTS_ALT")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>
<?
$tabControl->Begin();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&lang=<?=LANGUAGE_ID?>"><?
$tabControl->BeginNextTab();
?>
No parameters.
<!--
<input type="submit" <?if ($BIZPROC_RIGHT<"W") echo "disabled" ?> name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
<input type="hidden" name="Update" value="Y">
<input type="reset" name="reset" value="<?echo GetMessage("MAIN_RESET")?>">
-->
<?$tabControl->End();?>
</form>
<?endif;?>
