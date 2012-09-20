<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
$FORUM_RIGHT = $APPLICATION->GetGroupRight("forum");
if ($FORUM_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$ID = IntVal($ID);
$arForum = CForumNew::GetByID($ID);
if (!$arForum)
{
	LocalRedirect("forum_admin.php?lang=".$lang);
	die();
}

if (CForumNew::GetUserPermission($ID, $USER->GetUserGroupArray())<"Q")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$strErrorMessage = "";
$strOKMessage = "";

if ($REQUEST_METHOD=="GET" && $ACTION=="SHOW" && $MID>0 && $FORUM_RIGHT>="R"
	&& CForumNew::GetUserPermission($ID, $USER->GetUserGroupArray())>="Q"
	&& check_bitrix_sessid())
{
	CForumMessage::Update($MID, array("APPROVED"=>"Y"));
	CForumMessage::SendMailMessage($MID, array(), false, "NEW_FORUM_MESSAGE");
}
elseif ($REQUEST_METHOD=="GET" && $ACTION=="DEL" && $MID>0 && $FORUM_RIGHT>="W"
	&& CForumNew::GetUserPermission($ID, $USER->GetUserGroupArray())>="U"
	&& check_bitrix_sessid())
{
	if (!CForumMessage::CanUserDeleteMessage($MID, $USER->GetUserGroupArray(), $USER->GetID()))
		$strErrorMessage .= GetMessage("FM_NO_PERMS2DEL").". \n";

	if (strlen($strErrorMessage)<=0)
	{
		CForumMessage::Delete($MID);
	}
}

$APPLICATION->SetTitle(GetMessage("FORUM_MODERATE"));
$APPLICATION->SetTemplateCSS("forum/forum_tmpl_1/forum.css");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>
<font class="text">
<a href="forum_admin.php?lang=<?echo $lang?>"><?echo GetMessage("BACK");?></a>
</font>
<br>
<br>
<?
echo ShowError($strErrorMessage);

$arAllow = array(
	"HTML" => $arForum["ALLOW_HTML"],
	"ANCHOR" => $arForum["ALLOW_ANCHOR"],
	"BIU" => $arForum["ALLOW_BIU"],
	"IMG" => $arForum["ALLOW_IMG"],
	"VIDEO" => $arForum["VIDEO"],
	"LIST" => $arForum["ALLOW_LIST"],
	"QUOTE" => $arForum["ALLOW_QUOTE"],
	"CODE" => $arForum["ALLOW_CODE"],
	"FONT" => $arForum["ALLOW_FONT"],
	"SMILES" => $arForum["ALLOW_SMILES"],
	"UPLOAD" => $arForum["ALLOW_UPLOAD"],
	"NL2BR" => $arForum["ALLOW_NL2BR"]
	);

$parser = new textParser(LANG);
$arMessages = CForumMessage::GetListEx(array("ID"=>"ASC"), array("FORUM_ID" => $ID, "APPROVED" => "N"));
$arMessages->NavStart($FORUM_MESSAGES_PER_PAGE);

while ($arMessages->NavNext(true, "f_", false)):
	$arCurTopic = CForumTopic::GetByID($f_TOPIC_ID);
	?>
	<table width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
			<td class="tablehead4" colspan="2">
				<font class="tableheadtext">
				<b><?echo htmlspecialchars($arCurTopic["TITLE"]);?></b>,
				<?echo htmlspecialchars($arCurTopic["DESCRIPTION"]);?>
				</font>
			</td>
		</tr>
		<tr>
			<td class="tablebody1" valign="top">
				<font class="tablebodytext">
				<a name="message<?echo $f_ID;?>"></a>
				<?echo htmlspecialchars($f_AUTHOR_NAME);?>
				<?if (strlen($f_DESCRIPTION)>0) echo "<br>".htmlspecialcharsEx($f_DESCRIPTION);?>
				<?
				if (IntVal($f_AUTHOR_ID)>0)
				{
					$arMessageUserGroups = CUser::GetUserGroup($f_AUTHOR_ID);
					if (!in_array(2, $arMessageUserGroups))
						$arMessageUserGroups[] = 2;
					$strMessageUserPerms = CForumNew::GetUserPermission($ID, $arMessageUserGroups);
					if ($strMessageUserPerms=="Q") echo "<br><b>".GetMessage("FM_MODER")."</b>";
					elseif ($strMessageUserPerms=="U") echo "<br><b>".GetMessage("FM_EDITOR")."</b>";
					elseif ($strMessageUserPerms=="Y") echo "<br><b>".GetMessage("FM_ADMIN")."</b>";
					elseif (IntVal($f_RANK_ID)>0)
					{
						$arRank = CForumRank::GetLangByID($f_RANK_ID, LANG);
						echo "<br>".$arRank["NAME"];
					}
				}
				else
				{
					echo "<br><i>".GetMessage("FM_GUEST")."</i>";
				}
				?>
				<br>
				<?if (strlen($f_AVATAR)>0):?>
					<center><br>
					<?echo CFile::ShowImage($f_AVATAR, 90, 90, "border=0", "", true)?>
					</center>
				<?else:?>
					<br>
				<?endif;?>

				<?if (IntVal($f_NUM_POSTS)>0):?>
					<small><nobr><?echo GetMessage("FM_NUM_ALL_MESS");?>: <?echo $f_NUM_POSTS;?></nobr><br></small>
				<?endif;?>
				<?if (strlen($f_DATE_REG)>0):?>
					<small><?echo GetMessage("FM_DATE_REG");?>: <?echo $f_DATE_REG;?><br></small>
				<?endif;?>
				<?if (ForumCurrUserPermissions($ID)>="Q" && CModule::IncludeModule("statistic") && IntVal($f_GUEST_ID)>0 && $APPLICATION->GetGroupRight("statistic")!="D"):?>
					<small><nobr><?echo GetMessage("FM_GUEST_ID");?>: <a href="/bitrix/admin/guest_list.php?lang=<?=LANG?>&find_id=<?=$f_GUEST_ID?>&set_filter=Y"><?echo $f_GUEST_ID;?></a></nobr><br></small>
				<?endif;?>
				<?if (ForumCurrUserPermissions($ID)>="Q"):?>
					<small><nobr>IP: 
					<?
					$bIP = False;
					if (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $f_AUTHOR_IP)) $bIP = True;
					if ($bIP) echo GetWhoisLink($f_AUTHOR_IP);
					else echo $f_AUTHOR_IP;
					?>
					</nobr><br>
					<nobr>IP (<?echo GetMessage("FM_REAL");?>): 
					<?
					$bIP = False;
					if (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $f_AUTHOR_REAL_IP)) $bIP = True;
					if ($bIP) echo GetWhoisLink($f_AUTHOR_REAL_IP);
					else echo $f_AUTHOR_REAL_IP;
					?>
					</nobr><br></small>
				<?endif;?>
				</font>
			</td>
			<td width="100%" class="tablebody3" valign="top">
				<font class="tablebodytext">
				<?
				$arAllow["SMILES"] = $arForum["ALLOW_SMILES"];
				if ($f_USE_SMILES!="Y") $arAllow["SMILES"] = "N";
				echo $parser->convert($f_POST_MESSAGE, $arAllow);
				?>
				</font>
			</td>
		</tr>
		<tr>
			<td class="tablebody1">
				<font class=forumbodytext>
				<font size=-1><b><?echo GetMessage("CREATED");?></b><br>
				<nobr><?echo $f_POST_DATE;?></nobr>
				</font></font>
			</td>
			<td nowrap class="tablebody3" valign="bottom">
				<table cellspacing="0" cellpadding="2">
					<tr>
						<td width="100%" valign="bottom">
							<font class="tablebodytext">
							<?if ($FORUM_RIGHT>="R"):?>
								<?if (CForumNew::GetUserPermission($ID, $USER->GetUserGroupArray())>="Q"):?>
									<a href="forum_mod.php?ID=<?echo $ID; ?>&MID=<?echo $f_ID ?>&ACTION=SHOW&lang=<?echo $lang?>&<?=bitrix_sessid_get()?>" title="<?=GetMessage("SHOW_DESCR");?>"><?echo GetMessage("SHOW");?></a>
									<?if (CForumNew::GetUserPermission($ID, $USER->GetUserGroupArray())>="U"):?>
										&nbsp;|&nbsp;<a href="forum_mod.php?ID=<?echo $ID; ?>&MID=<?echo $f_ID ?>&ACTION=DEL&lang=<?echo $lang?>&<?=bitrix_sessid_get()?>" title="<?=GetMessage("DEL_DESCR");?>"><?echo GetMessage("DEL");?></a>
									<?endif;?>
								<?endif;?>
							<?else:?>
								<?echo GetMessage("SHOW");?>
								&nbsp;|&nbsp;<?echo GetMessage("DEL");?>
							<?endif;?>
							</font>
						</td>
						<td align="right" valign="bottom">
							<font class="tablebodytext">
							<a href="javascript:scroll(0,0);"><?=GetMessage("TO_TOP");?></a>
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
	<?
endwhile;
?>
<p><?echo $arMessages->NavPrint(GetMessage("MESSAGES"))?></p>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>