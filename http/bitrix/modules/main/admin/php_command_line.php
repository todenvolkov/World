<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "utilities/php_command_line.php");
require_once ($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/filter_tools.php");

if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_php_commandline";
$lAdmin = new CAdminList($sTableID);

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST['query']<>'' && $isAdmin && check_bitrix_sessid())
{
	$lAdmin->BeginPrologContent();
	echo "<h2>".GetMessage("php_cmd_result")."</h2><p>";
	eval($_REQUEST['query']);
	echo "</p>";
	$lAdmin->EndPrologContent();
}

$lAdmin->BeginEpilogContent();
?>
	<input type="hidden" name="query" id="query" value="<?=htmlspecialchars($_REQUEST['query'])?>">
<?
$lAdmin->EndEpilogContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("php_cmd_title"));

if($mode!="silent")
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<script>
function __FPHPSubmit()
{
	if(confirm('<?echo GetMessage("php_cmd_confirm")?>'))
	{
		document.getElementById('query').value = document.getElementById('php').value;
		window.scrollTo(0, 500);
		<?=$lAdmin->ActionPost(CUtil::JSEscape($APPLICATION->GetCurPageParam("mode=frame", Array("mode", "PAGEN_1"))))?>
	}
}
</script>
<?
$aTabs = array(
	array("DIV"=>"tab1", "TAB"=>GetMessage("php_cmd_input"), "TITLE"=>GetMessage("php_cmd_php")),
);
$editTab = new CAdminTabControl("editTab", $aTabs);
?>
<form name="form1" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>" method="POST">
<?=bitrix_sessid_post()?>
<?
$editTab->Begin();
$editTab->BeginNextTab();
?>
<tr valign="top">
	<td width="100%" colspan="2">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<textarea cols="60" name="php" id="php" rows="15" wrap="OFF" style="width:100%;"><? echo htmlspecialchars($_REQUEST['query']); ?></textarea><br />	</td>
</tr>
<?$editTab->Buttons();
?>
<input<?if(!$isAdmin) echo " disabled"?> type="button" accesskey="x" name="execute" value="<?echo GetMessage("php_cmd_button")?>" onclick="return __FPHPSubmit();">
<input type="reset" value="<?echo GetMessage("php_cmd_button_clear")?>">
<?
$editTab->End();
?>
</form>

<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>