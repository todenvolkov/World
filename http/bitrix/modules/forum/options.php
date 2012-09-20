<?
ClearVars();
$module_id = "forum";
$FORUM_RIGHT = $APPLICATION->GetGroupRight($module_id);
if (($FORUM_RIGHT>="R") && (CModule::IncludeModule("forum"))):

if ($REQUEST_METHOD=="GET" && $FORUM_RIGHT=="W" && strlen($RestoreDefaults)>0)
{
	COption::RemoveOption("forum");
	$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
	while($zr = $z->Fetch())
		$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
}

$arLangs = array();
$arNameStatusesDefault = array();
$arNameStatuses = @unserialize(COption::GetOptionString("forum", "statuses_name"));

$db_res = CLanguage::GetList(($b="sort"), ($o="asc"));
if ($db_res && $res = $db_res->Fetch())
{
	do 
	{
		$arLangs[$res["LID"]] = $res;
		$name = array("guest" => "Guest", "user" => "User", "moderator" => "Moderator", "editor" => "Editor", "administrator" => "Administrator");
		$arMess = IncludeModuleLangFile(__FILE__, $res["LID"], true);
		foreach ($name as $k => $v):
			$name[$k] = (!empty($arMess["F_".strToUpper($k)]) ? $arMess["F_".strToUpper($k)] : $name[$k]);
		endforeach;
		$arNameStatusesDefault[$res["LID"]] = $name;
		if (empty($arNameStatuses[$res["LID"]]) || !is_array($arNameStatuses[$res["LID"]])):
			$arNameStatuses[$res["LID"]] = $name;
		else:
			foreach ($name as $k => $v):
				if(empty($arNameStatuses[$res["LID"]][$k])):
					$arNameStatuses[$res["LID"]][$k] = $v;
				endif;
			endforeach;
		endif;
	} while ($res = $db_res->Fetch());
	$tmp = array_diff(array_keys($arNameStatuses), array_keys($arNameStatusesDefault)); 
	foreach ($arNameStatuses as $k => $v):
		if (!is_set($arNameStatusesDefault, $k))
			unset($arNameStatuses[$k]); 
	endforeach;
}


IncludeModuleLangFile(__FILE__);


if($REQUEST_METHOD=="POST" && strlen($Update)>0 && $FORUM_RIGHT=="W" && check_bitrix_sessid())
{
	COption::SetOptionString("forum", "avatar_max_size", $avatar_max_size);
	COption::SetOptionString("forum", "avatar_max_width", $avatar_max_width);
	COption::SetOptionString("forum", "avatar_max_height", $avatar_max_height);
	COption::SetOptionString("forum", "file_max_size", $file_max_size);
	COption::SetOptionString("forum", "parser_nofollow", ($parser_nofollow == "Y" ? "Y" : "N"));

	COption::SetOptionString("forum", "FORUM_FROM_EMAIL", $FORUM_FROM_EMAIL);
	COption::SetOptionString("forum", "FORUMS_PER_PAGE", $FORUMS_PER_PAGE_MAIN);
	COption::SetOptionString("forum", "TOPICS_PER_PAGE", $TOPICS_PER_PAGE);
	COption::SetOptionString("forum", "MESSAGES_PER_PAGE", $MESSAGES_PER_PAGE);
	COption::SetOptionString("forum", "SHOW_VOTES", (($SHOW_VOTES=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "SHOW_TASKBAR_ICON", (($SHOW_TASKBAR_ICON=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "SHOW_ICQ_CONTACT", (($SHOW_ICQ_CONTACT=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "MaxPrivateMessages", $MaxPrivateMessages);
	COption::SetOptionString("forum", "UsePMVersion", $UsePMVersion);
	COption::SetOptionString("forum", "MESSAGE_HTML", ($MESSAGE_HTML=="Y" ? "Y" : "N" ));
	COption::SetOptionString("forum", "FORUM_GETHOSTBYADDR", (($FORUM_GETHOSTBYADDR=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "FILTER", (($FILTER=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "FILTER_ACTION", $FILTER_ACTION);
	COption::SetOptionString("forum", "FILTER_RPL", $FILTER_RPL);
	COption::SetOptionString("forum", "FILTER_MARK", $FILTER_MARK);
	COption::SetOptionString("forum", "search_message_count", $search_message_count);
	
	COption::SetOptionString("forum", "USER_EDIT_OWN_POST", (($USER_EDIT_OWN_POST=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "USER_SHOW_NAME", (($USER_SHOW_NAME=="Y") ? "Y" : "N" ));
	COption::SetOptionString("forum", "USE_COOKIE", (($USE_COOKIE=="Y") ? "Y" : "N" ));
	if ($LOGS == "Y"):
		$LOGS = ($LOGS_ADDITIONAL == "Y" ? "U" : "Q");
	else:
		$LOGS = "A";
	endif;
//	A - no logs, Q - log for moderate, U - log for all
	COption::SetOptionString("forum", "LOGS", $LOGS);
//****************************************************************************************************************
	foreach ($FILTER_DICT as $l => $val)
	{
		COption::SetOptionString("forum", "FILTER_DICT_W", $val["W"], false, $l);
		COption::SetOptionString("forum", "FILTER_DICT_T", $val["T"], false, $l);
	}
	foreach ($arNameStatuses as $lid => $names):
		foreach ($names as $key => $val):
			$arNameStatuses[$lid][$key] = (!empty($STATUS_NAME[$lid][$key]) ? $STATUS_NAME[$lid][$key] : $arNameStatuses[$lid][$key]);
		endforeach;
	endforeach;
	
	COption::SetOptionString("forum", "statuses_name", serialize($arNameStatuses));
//*****************************************************************************************************************

	if ($SHOW_TASKBAR_ICON=="Y")
		RegisterModuleDependences("main", "OnPanelCreate", "forum", "CForumNew", "OnPanelCreate");
	else
		UnRegisterModuleDependences("main", "OnPanelCreate", "forum", "CForumNew", "OnPanelCreate");
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "vote_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "vote_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
	array("DIV" => "edit3", "TAB" => GetMessage("USE_FILTER"), "ICON" => "vote_settings", "TITLE" => GetMessage("USE_FILTER")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>
<?
$tabControl->Begin();
?><form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&lang=<?=LANGUAGE_ID?>" id="FORMACTION"><?
?><?=bitrix_sessid_post()?><?
$tabControl->BeginNextTab();
?>

	<tr>
		<td valign="top"  width="50%"><?echo GetMessage("FORUM_FROM_EMAIL")?>:</td>
		<td valign="middle" width="50%">
			<?$val = COption::GetOptionString("forum", "FORUM_FROM_EMAIL", "nomail@nomail.nomail");?>
			<input type="text" size="35" maxlength="255" value="<?=htmlspecialchars($val)?>" name="FORUM_FROM_EMAIL"></td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("FORUMS_PER_PAGE")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "FORUMS_PER_PAGE", "10");?>
			<input type="text" size="35" maxlength="255" value="<?=htmlspecialchars($val)?>" name="FORUMS_PER_PAGE_MAIN"></td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("TOPICS_PER_PAGE")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10");?>
			<input type="text" size="35" maxlength="255" value="<?=htmlspecialchars($val)?>" name="TOPICS_PER_PAGE"></td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("MESSAGES_PER_PAGE")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10");?>
			<input type="text" size="35" maxlength="255" value="<?=htmlspecialchars($val)?>" name="MESSAGES_PER_PAGE"></td>
	</tr>
	<tr>
		<td valign="top"><label for="SHOW_VOTES"><?= GetMessage("FORUM_GG_SHOW_VOTE") ?>:</label></td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "SHOW_VOTES", "Y");?>
			<input type="checkbox" value="Y" name="SHOW_VOTES" id="SHOW_VOTES" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td valign="top"><label for="SHOW_ICQ_CONTACT"><?= GetMessage("SHOW_ICQ_CONTACT")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "SHOW_ICQ_CONTACT", "N");?>
			<input type="checkbox" value="Y" name="SHOW_ICQ_CONTACT" id="SHOW_ICQ_CONTACT" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("FORUM_GETHOSTBYADDR")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "FORUM_GETHOSTBYADDR", "N");?>
			<input type="checkbox" value="Y" name="FORUM_GETHOSTBYADDR" id="FORUM_GETHOSTBYADDR" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("MESSAGE_HTML")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "MESSAGE_HTML", "Y");?>
			<input type="checkbox" value="Y" name="MESSAGE_HTML" id="MESSAGE_HTML" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td valign="middle"><label for="USE_COOKIE"><?= GetMessage("FORUM_USE_COOKIE") ?>:</label></td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "USE_COOKIE", "N");?>
			<input type="checkbox" value="Y" name="USE_COOKIE" id="USE_COOKIE" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td valign="middle"><label for="LOGS"><?=GetMessage("FORUM_LOGS_TITLE")?>:</label></td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "LOGS", "Q");?>
			<div>
				<input type="checkbox" name="LOGS" id="LOGS" value="Y" <?=($val > "A" ? "checked='checked'" : "")?> <?
				?>onclick="this.parentNode.nextSibling.style.display=(this.checked ? 'block' : 'none')"><label for="LOGS"><?=GetMessage("FORUM_LOGS")?></label>
			</div><?
			?><div <?=($val <= "A" ? "style='display:none;'" : "")?>>
				<input type="checkbox" name="LOGS_ADDITIONAL" ID="LOGS_ADDITIONAL" value="Y" <?=($val > "Q" ? "checked='checked'" : "")?>>
				<label for="LOGS_ADDITIONAL"><?=GetMessage("FORUM_LOGS_ADDITIONAL")?></label>
			</div>
		</td>
	</tr>
	<tr class="heading"><td colspan="2"><?=GetMessage("F_USER_SETTINGS")?>:</td></tr>
	<tr>
		<td valign="middle"><label for="USER_EDIT_OWN_POST"><?=GetMessage("FORUM_USER_EDIT_OWN_POST") ?>:</label></td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "N");?>
			<input type="checkbox" value="Y" name="USER_EDIT_OWN_POST" id="USER_EDIT_OWN_POST" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td valign="middle"><label for="USER_SHOW_NAME"><?=GetMessage("FORUM_USER_SHOW_NAME") ?>:</label></td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "USER_SHOW_NAME", "Y");?>
			<input type="checkbox" value="Y" name="USER_SHOW_NAME" id="USER_SHOW_NAME" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td valign="middle"><label for="parser_nofollow"><?=GetMessage("F_PARSER_NOFOLLOW")?>:</label></td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "parser_nofollow", "Y");?>
			<input type="checkbox" value="Y" name="parser_nofollow" id="parser_nofollow" <?if ($val=="Y") echo "checked";?>></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("FORUM_GG_AVATAR_S")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "avatar_max_size", 10000);?>
			<input type="text" size="35" maxlength="255" value="<?=htmlspecialchars($val)?>" name="avatar_max_size"></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("FORUM_GG_AVATAR_W")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "avatar_max_width", 90);?>
			<input type="text" size="14" maxlength="255" value="<?=htmlspecialchars($val)?>" name="avatar_max_width">&nbsp;/&nbsp;
			<?$val = COption::GetOptionString("forum", "avatar_max_height", 90);?>
			<input type="text" size="14" maxlength="255" value="<?=htmlspecialchars($val)?>" name="avatar_max_height">			
			</td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("FORUM_GG_FILE_S")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "file_max_size", 50000);?>
			<input type="text" size="35" maxlength="255" value="<?=htmlspecialchars($val)?>" name="file_max_size"></td>
	</tr>
	<tr class="heading"><td colspan="2"><?=GetMessage("F_PM_SETTINGS")?>:</td></tr>
	<tr>
		<td valign="top"><?=GetMessage("UsePMVersion")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "UsePMVersion", "2");?>
			<select name="UsePMVersion" id="UsePMVersion">
				<option value="1" <?if ($val=="1") echo "selected";?>>1.0</option>
				<option value="2" <?if ($val=="2") echo "selected";?>>2.0</option>
			</select>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("FORUM_PRIVATE_MESSAGE")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "MaxPrivateMessages", 100);?>
			<input type="text" size="35" maxlength="255" value="<?=intVal($val)?>" name="MaxPrivateMessages"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("F_SEARCH_HEADER")?>:</td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("F_SEARCH_COUNT")?>:</td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "search_message_count", 0);?>
			<input type="text" size="35" maxlength="255" value="<?=intVal($val)?>" name="search_message_count"></td>
	</tr>
	<tr class="heading"><td colspan="2"><?=GetMessage("F_FORUM_STATUSES")?>:</td></tr>
	<tr>
		<td valign="middle" colspan="2" align="center">
			<table border="0" cellspacing="6">
				<tr>
					<td align="center"><?=GetMessage("LANG")?></td>
					<?
		foreach ($arNameStatusesDefault[LANGUAGE_ID] as $key => $val):
					?><td><?=$val?></td><?
		endforeach;
					?>
				</tr>
<?
		foreach ($arNameStatuses as $lid => $names):
?>
				<tr>
					<td><?=$arLangs[$lid]["NAME"]?> [ <?=$lid?> ]</td>
<?
			foreach ($names as $key => $val):
?>
					<td><input type="text" name="STATUS_NAME[<?=$lid?>][<?=$key?>]" value="<?=htmlspecialcharsEx($val)?>" /></td>
<?				
			endforeach;
?>
				</tr>
<?
		endforeach;
?>
				</table>
		</td>
	</tr>

<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->BeginNextTab();?>
	<tr>
		<td valign="top"><label for="FILTER"><?=GetMessage("FILTER")?>:</label></td>
		<td valign="middle">
			<?$val = COption::GetOptionString("forum", "FILTER", "Y");?>
			<input type="checkbox" value="Y" name="FILTER" id="FILTER" <?if ($val=="Y") echo "checked";?> onclick="DisableAction(this)"></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("FILTER_ACTION")?>:</td>
		<td valign="middle">
			<?echo SelectBoxFromArray("FILTER_ACTION", array("REFERENCE" => array(GetMessage("non"), GetMessage("del"), GetMessage("rpl")), "REFERENCE_ID" => array("non", "del", "rpl")), COption::GetOptionString("forum", "FILTER_ACTION", "rpl"))?>
		</td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("FILTER_RPL")?>:</td>
		<?$val = COption::GetOptionString("forum", "FILTER_RPL", "*");?>
		<td valign="middle"><input type="text" value="<?=htmlspecialcharsEx($val)?>" name="FILTER_RPL" id="FILTER_RPL"></td>
	</tr>
	<script language="JavaScript">
	function DisableAction(CheckB)
	{
		var Form = document.getElementById('FORMACTION');
		if (CheckB.checked)
		{
			Form.FILTER_ACTION.disabled = false;
			Form.FILTER_ACTION.value = '<?=CUtil::JSEscape(COption::GetOptionString("forum", "FILTER_ACTION", "rpl"))?>';
			Form.FILTER_RPL.disabled = false;
			Form.FILTER_RPL.value = '<?=CUtil::JSEscape(COption::GetOptionString("forum", "FILTER_RPL", "*"))?>';
		}
		else
		{
			Form.FILTER_ACTION.disabled = true;
			Form.FILTER_RPL.disabled = true;
		}
		return false;
	}
	<?if ($val = COption::GetOptionString("forum", "FILTER", "Y")!="Y"):?>
	var Form = document.getElementById('FORMACTION');
	Form.FILTER_ACTION.disabled = true;
	Form.FILTER_RPL.disabled = true;
	<?endif;?>
	</script>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("ASSOC_LANG_PARAMS")?>:</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table border="0" cellspacing="6">
				<tr>
					<td align="center"><?=GetMessage("LANG")?></td>
					<td align="center"><span class="required">*</span><?=GetMessage("DICTINARY_AND_EREG")?></td>
					<td align="center"><span id="SECTION_NAME_TITLE"><?=GetMessage("TRANSCRIPTION_DICTIONARY")?></span></td>
				</tr><?
			$db_res =  CFilterDictionary::GetList();
			$Dict = array();
			while ($res = $db_res->Fetch())
			{
				$Dict[$res["TYPE"]]["reference_id"][] = $res["ID"];
				$Dict[$res["TYPE"]]["reference"][] = $res["TITLE"];
			}
			$Dict['W']["reference_id"][] = "";
			$Dict['W']["reference"][] = GetMessage("DICTIONARY_NONE");
			$Dict['T']["reference_id"][] = "";
			$Dict['T']["reference"][] = GetMessage("DICTIONARY_NONE");
			$l = CLanguage::GetList($lby="sort", $lorder="asc");
			while($ar = $l->ExtractFields("l_"))
			{
				?><tr>
					<td><font class="tablefieldtext"><?=$ar["NAME"]?> [ <?=$ar["LID"]?> ]:</font></td>
					<td><?=SelectBoxFromArray("FILTER_DICT[".$ar["LID"]."][W]", $Dict["W"], COption::GetOptionString("forum", "FILTER_DICT_W", '', $ar["LID"]))?></td>
					<td><?=SelectBoxFromArray("FILTER_DICT[".$ar["LID"]."][T]", $Dict["T"], COption::GetOptionString("forum", "FILTER_DICT_T", '', $ar["LID"]))?></td>
				</tr><?
			}
			?></table>
		</td>
	</tr>
<?$tabControl->Buttons();?>
<script language="JavaScript">
function RestoreDefaults()
{
	if(confirm('<?=CUtil::JSEscape(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
		window.location = "<?=$APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid)?>";
}
</script>
<input <?if ($FORUM_RIGHT<"W") echo "disabled" ?> type="submit" name="Update" value="<?echo GetMessage("PATH_SAVE")?>">
<input type="hidden" name="Update" value="Y">
<input type="reset" name="reset" value="<?echo GetMessage("PATH_RESET")?>">
<input <?if ($FORUM_RIGHT<"W") echo "disabled" ?> type="button" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
<?$tabControl->End();?>
</form>
<?endif;?>
