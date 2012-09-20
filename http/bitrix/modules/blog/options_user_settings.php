<?
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/lang/", "/options_user_settings.php"));

if (CModule::IncludeModule("blog")):
	$ID = IntVal($ID);
	$str_blog_AVATAR = "";
	ClearVars("str_blog_");
	$db_res = CBlogUser::GetList(array(), array("USER_ID" => $ID));
	if (!$db_res->ExtractFields("str_blog_", True))
	{
		if (!isset($str_blog_ALLOW_POST) || ($str_blog_ALLOW_POST!="Y" && $str_blog_ALLOW_POST!="N"))
			$str_blog_ALLOW_POST = "Y";
	}

	if($COPY_ID > 0)
		$str_blog_AVATAR = "";

	if (strlen($strError)>0)
	{
		$DB->InitTableVarsForEdit("b_blog_user", "blog_", "str_blog_");
		$DB->InitTableVarsForEdit("b_user", "blog_", "str_blog_");
	}
	?>
	<input type="hidden" name="profile_module_id[]" value="blog">
	<?if ($USER->IsAdmin()):?>
		<tr>
			<td><?=GetMessage("blog_ALLOW_POST")?></td>
			<td><input type="checkbox" name="blog_ALLOW_POST" value="Y" <?if ($str_blog_ALLOW_POST=="Y") echo "checked";?>></td>
		</tr>
	<?endif;?>
	<tr>
		<td><?=GetMessage('blog_ALIAS')?></td>
		<td><input class="typeinput" type="text" name="blog_ALIAS" size="30" maxlength="255" value="<?=$str_blog_ALIAS?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage('blog_DESCRIPTION')?></td>
		<td><input class="typeinput" type="text" name="blog_DESCRIPTION" size="30" maxlength="255" value="<?=$str_blog_DESCRIPTION?>"></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage('blog_INTERESTS')?></td>
		<td><textarea class="typearea" name="blog_INTERESTS" rows="3" cols="35"><?echo $str_blog_INTERESTS; ?></textarea></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("blog_AVATAR")?></td>
		<td><?
			echo CFile::InputFile("blog_AVATAR", 20, $str_blog_AVATAR);
			if (IntVal($str_blog_AVATAR)>0):
				?><br><?
				echo CFile::ShowImage($str_blog_AVATAR, 150, 150, "border=0", "", true);
			endif;
			?></td>
	</tr>
	<?
endif;
?>