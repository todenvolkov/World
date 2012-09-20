<?
IncludeModuleLangFile(__FILE__);
ClearVars("str_forum_");
if (CModule::IncludeModule("forum")):
	$ID = IntVal($ID);
	$db_res = CForumUser::GetList(array(), array("USER_ID" => $ID));
	$db_res->ExtractFields("str_forum_", True);
	if (!isset($str_forum_ALLOW_POST) || ($str_forum_ALLOW_POST!="Y" && $str_forum_ALLOW_POST!="N"))
		$str_forum_ALLOW_POST = "Y";
	if (!isset($str_forum_SHOW_NAME) || ($str_forum_SHOW_NAME!="Y" && $str_forum_SHOW_NAME!="N"))
		$str_forum_SHOW_NAME = "Y";
	$str_forum_SUBSC_GET_MY_MESSAGE = ($str_forum_SUBSC_GET_MY_MESSAGE == "Y" ? "Y" : "N");

	if($COPY_ID > 0)
		$str_forum_AVATAR = "";

	if (strlen($strError)>0)
	{
		$DB->InitTableVarsForEdit("b_forum_user", "forum_", "str_forum_");
		$DB->InitTableVarsForEdit("b_user", "forum_", "str_forum_");
	}
	?>
	<input type="hidden" name="profile_module_id[]" value="forum">
	<?if ($USER->IsAdmin() || $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W"):?>
		<tr valign="top">
			<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage("forum_ALLOW_POST")?></font></td>
			<td width="60%"><input type="checkbox" name="forum_ALLOW_POST" value="Y" <?if ($str_forum_ALLOW_POST=="Y") echo "checked";?>></td>
		</tr>
	<?endif;?>
	<tr valign="top">
		<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage("forum_HIDE_FROM_ONLINE")?></font></td>
		<td width="60%"><input type="checkbox" name="forum_HIDE_FROM_ONLINE" value="Y" <?if ($str_forum_HIDE_FROM_ONLINE=="Y") echo "checked";?>></td>
	</tr>
	<tr valign="top">
		<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage("forum_SUBSC_GET_MY_MESSAGE")?></font></td>
		<td width="60%"><input type="checkbox" name="forum_SUBSC_GET_MY_MESSAGE" value="Y" <?if ($str_forum_SUBSC_GET_MY_MESSAGE=="Y") echo "checked";?>></td>
	</tr>
	<tr valign="top">
		<td align="right" width="40%"><font class="tablefieldtext"><?=GetMessage("forum_SHOW_NAME")?></font></td>
		<td width="60%"><input type="checkbox" name="forum_SHOW_NAME" value="Y" <?if ($str_forum_SHOW_NAME=="Y") echo "checked";?>></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('forum_DESCRIPTION')?></font></td>
		<td><input class="typeinput" type="text" name="forum_DESCRIPTION" size="30" maxlength="255" value="<?=$str_forum_DESCRIPTION?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage('forum_INTERESTS')?></font></td>
		<td><textarea class="typearea" name="forum_INTERESTS" rows="3" cols="35"><?echo $str_forum_INTERESTS; ?></textarea></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage("forum_SIGNATURE")?></font></td>
		<td><textarea class="typearea" name="forum_SIGNATURE" rows="3" cols="35"><?echo $str_forum_SIGNATURE; ?></textarea></td>
	</tr>
	<tr valign="top">
		<td align="right"><font class="tablefieldtext"><?=GetMessage("forum_AVATAR")?></font></td>
		<td><font class="tablebodytext"><?
			echo CFile::InputFile("forum_AVATAR", 20, $str_forum_AVATAR);
			if (strlen($str_forum_AVATAR)>0):
				?><br><?
				echo CFile::ShowImage($str_forum_AVATAR, 150, 150, "border=0", "", true);
			endif;
			?></font></td>
	</tr>
	<?
endif;
/*
GetMessage("forum_TAB");
GetMessage("forum_TAB_TITLE");
GetMessage("forum_INFO");
*/
?>