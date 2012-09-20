<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$FORUM_RIGHT = $APPLICATION->GetGroupRight("forum");
if ($FORUM_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
ClearVars();
IncludeModuleLangFile(__FILE__); 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$ID = IntVal($ID);

$db_lang = CLang::GetList(($b="sort"), ($o="asc"));
$langCount = 0;
while ($arLang = $db_lang->Fetch())
{
	$arSysLangs[$langCount] = $arLang["LID"];
	$arSysLangNames[$langCount] = htmlspecialchars($arLang["NAME"]);
	$langCount++;
}


$strErrorMessage = "";
$bInitVars = false;
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && $FORUM_RIGHT=="W" && check_bitrix_sessid())
{
	$MIN_NUM_POSTS = IntVal($MIN_NUM_POSTS);
	if ($MIN_NUM_POSTS<=0)
		$strErrorMessage .= GetMessage("ERROR_NO_MIN_NUM_POSTS")."<br>";

	for ($i = 0; $i<count($arSysLangs); $i++)
	{
		${"NAME_".$arSysLangs[$i]} = Trim(${"NAME_".$arSysLangs[$i]});
		if (strlen(${"NAME_".$arSysLangs[$i]})<=0)
			$strErrorMessage .= GetMessage("ERROR_NO_NAME")." [".$arSysLangs[$i]."] ".$arSysLangNames[$i].".<br>";
	}

	if (strlen($strErrorMessage)<=0)
	{
		$arFields = array("MIN_NUM_POSTS" => $MIN_NUM_POSTS);

		for ($i = 0; $i<count($arSysLangs); $i++)
		{
			$arFields["LANG"][] = array(
				"LID" => $arSysLangs[$i],
				"NAME" => ${"NAME_".$arSysLangs[$i]}
				);
		}

		if ($ID>0)
		{
			$ID1 = CForumRank::Update($ID, $arFields);
			if (IntVal($ID1)<=0)
				$strErrorMessage .= GetMessage("ERROR_EDIT_RANK")."<br>";
		}
		else
		{
			$ID = CForumRank::Add($arFields);
			if (IntVal($ID)<=0)
				$strErrorMessage .= GetMessage("ERROR_ADD_RANK")."<br>";
		}
	}

	if (strlen($strErrorMessage)>0) $bInitVars = True;

	if (strlen($save)>0 && strlen($strErrorMessage)<=0)
		LocalRedirect("forum_rank.php?lang=".LANG."&".GetFilterParams("filter_", false));
}

if ($ID>0)
{
	$db_rank = CForumRank::GetList(array(), array("ID" => $ID));
	$db_rank->ExtractFields("str_", False);
}

if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_forum_rank", "", "str_");
}

$sDocTitle = ($ID>0) ? str_replace("#ID#", $ID, GetMessage("FORUM_EDIT_RECORD")) : GetMessage("FORUM_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<a name="tb"></a>
<font class="text">
<a href="forum_rank.php?lang=<?echo LANG?>&<?echo GetFilterParams("filter_", false)?>"><?=GetMessage("FORUM_RECORDS_LIST")?></a>
</font>
<br><br>
<?echo ShowError($strErrorMessage);?>

<form method="POST" action="forum_rank_edit.php?lang=<?echo LANG ?>#tb" name="fform">
<table border="0" cellspacing="1" cellpadding="3" width="100%" class="edittable">
	<tr>
		<td valign="top" align="center" class="tablehead" colspan="2">
			<?=bitrix_sessid_post()?>
			<font class="tableheadtext"><b><?echo GetMessage("FORUM_PT_PROPS")?></b></font>
		</td>
	</tr>
	<?if ($ID>0):?>
	<tr>
		<td valign="top" align="right" class="tablebody" width="50%">
			<font class="tablefieldtext"><?echo GetMessage("FORUM_CODE")?>:</font>
		</td>
		<td valign="top" align="left" class="tablebody" width="50%">
			<input type="hidden" name="ID" value="<?echo $ID ?>">
			<font class="tablebodytext"><?echo $ID ?></font>
		</td>
	</tr>
	<?endif;?>

	<tr>
		<td valign="top" align="right" class="tablebody" width="50%">
			<font class="tablefieldtext"><?echo GetMessage("FORUM_SORT")?>:</font>
		</td>
		<td valign="top" align="left" class="tablebody" width="50%">
			<input type="text" class="typeinput" name="MIN_NUM_POSTS" value="<?=htmlspecialcharsEx($str_MIN_NUM_POSTS)?>" size="10" />
		</td>
	</tr>

	<?
	for ($i = 0; $i<count($arSysLangs); $i++):
		$arRankLang = CForumRank::GetLangByID($ID, $arSysLangs[$i]);
		$str_NAME = ($bInitVars ? ${"NAME_".$arSysLangs[$i]} : $arRankLang["NAME"]);
		?>
		<tr>
			<td valign="top" align="center" class="tablebody" colspan="2">
				<b><font class="tablefieldtext">[<?echo $arSysLangs[$i];?>] <?echo $arSysLangNames[$i];?>:</font></b>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="tablebody" width="50%">
				<font class="tableheadtext"><font color="#FF0000">*</font><?echo GetMessage("FORUM_NAME")?>:</font>
			</td>
			<td valign="top" align="left" class="tablebody" width="50%">
				<input type="text" class="typeinput" name="NAME_<?echo $arSysLangs[$i] ?>" value="<?=htmlspecialcharsEx($str_NAME)?>" size="30">
			</td>
		</tr>
	<?endfor;?>

</table>

<br>
<div align="left">
	<input type="submit" <?if ($FORUM_RIGHT<"W") echo "disabled" ?> name="save" value="<?if (strlen($ID)>0) : echo GetMessage("FORUM_SAVE"); else : echo GetMessage("FORUM_ADD"); endif; ?>" class="button">
	&nbsp;
	<input type="submit" class="button" <?if ($FORUM_RIGHT<"W") echo "disabled" ?> name="apply" value="<?=GetMessage("FORUM_APPLY")?>">
	&nbsp;
	<input type="reset" value="<?echo GetMessage("FORUM_RESET")?>" class="button">
</div>
</form>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>