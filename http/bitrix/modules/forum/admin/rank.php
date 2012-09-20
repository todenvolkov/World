<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
$FORUM_RIGHT = $APPLICATION->GetGroupRight("forum");
if ($FORUM_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__); 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$APPLICATION->SetTitle(GetMessage("RANK_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$strErrorMessage = "";
$strOKMessage = "";

$del_id = IntVal($del_id);
if ($del_id>0 && $FORUM_RIGHT=="W" && check_bitrix_sessid())
{
	if (strlen($strErrorMessage)<=0)
	{
		if (!CForumRank::Delete($del_id))
			$strErrorMessage .= GetMessage("ERROR_DEL_RANK")." \n";
		else
			$strOKMessage .= GetMessage("FR_SUCCESS_DEL").". \n";
	}
}

InitSorting($APPLICATION->GetCurPage());
$db_RANK = CForumRank::GetList(array($by=>$order), array());

$db_RANK->NavStart(20);
?>

<?echo ShowMessage(array("MESSAGE" => $strErrorMessage, "TYPE" => "ERROR"));?>
<?echo ShowMessage(array("MESSAGE" => $strOKMessage, "TYPE" => "OK"));?>

<?if($FORUM_RIGHT>="R"):?>
	<form method="GET" action="forum_rank_edit.php#tb">
		<?echo GetFilterHiddens("filter_");?>
		<input type="hidden" name="lang" value="<?echo LANG?>">
		<input type="submit" class="button" name="Add" value="<?echo GetMessage("FORUM_ADD_RANK")?> &gt;&gt;">
	</form>
<?endif;?>

<p><?$db_RANK->NavPrint(GetMessage("RANK_NAV"));?></p>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
<tr valign="top" align="center"> 
	<td class="tablehead1" align="center"><font class="tableheadtext"><?echo GetMessage("RANK_ID")?><br><?echo SortingEx("ID")?></font></td>
	<td class="tablehead2" align="center"><font class="tableheadtext"><?echo GetMessage("RANK_MIN_NUM_POSTS")?><br><?echo SortingEx("MIN_NUM_POSTS")?></font></td>
	<td class="tablehead2" align="center"><font class="tableheadtext"><?echo GetMessage("FORUM_NAME")?><br></font></td>
	<td class="tablehead3" align="center"><font class="tableheadtext"><?echo GetMessage("FORUM_ACTIONS")?></font></td>
</tr>
<?
while ($db_RANK_arr = $db_RANK->NavNext(true, "f_")):
	$arRankLang = CForumRank::GetLangByID($f_ID, LANG);
	?>
	<tr valign="top">
		<td class="tablebody1"><font class="tablebodytext"><?echo $f_ID?></font></td>
		<td class="tablebody2"><font class="tablebodytext"><?echo $f_MIN_NUM_POSTS ?></font></td>
		<td class="tablebody2">
			<font class="tableheadtext">
			<?echo htmlspecialchars($arRankLang["NAME"]);?>
			</font>
		</td>
		<td class="tablebody3" align="center">
			<font class="tablebodytext actions">
			<?if ($FORUM_RIGHT>="R"):?>
				<a title="<?echo GetMessage("FORUM_EDIT_DESCR")?>" href="forum_rank_edit.php?ID=<?echo $f_ID?>&lang=<?echo LANG?>&<?echo GetFilterParams("filter_");?>#tb"><?echo GetMessage("FORUM_EDIT")?></a><br>
			<?endif;?>
			<?if ($FORUM_RIGHT=="W"):?>
				<a title="<?echo GetMessage("FORUM_DELETE_DESCR")?>" href="javascript:if(confirm('<?echo GetMessage("RANK_DEL_CONF")?>')) window.location='forum_rank.php?del_id=<?echo $f_ID?>&lang=<?echo LANG?>&<?=bitrix_sessid_get()?>&<?echo GetFilterParams("filter_", false);?>#tb'"><?echo GetMessage("RANK_DEL")?></a>
			<?else:?>
				<?echo GetMessage("RANK_DEL")?>
			<?endif;?>
			</font>
		</td>
	</tr>
	<?
endwhile;
?>
</table>
<p><?$db_RANK->NavPrint(GetMessage("RANK_NAV"));?></p>
<br>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>