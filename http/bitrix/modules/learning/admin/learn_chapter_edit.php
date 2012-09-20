<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$strWarning = "";
$message = null;
$bVarsFromForm = false;
$ID = intval($ID);
$COURSE_ID = intval($COURSE_ID);
$CHAPTER_ID = intval($CHAPTER_ID);

$course = CCourse::GetByID($COURSE_ID);

if($arCourse = $course->Fetch())
	$bBadCourse=(CCourse::GetPermission($COURSE_ID)<"W");
else
	$bBadCourse = true;

$aTabs = array(
	array(
		"DIV" => "edit1",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_ADMIN_TAB1"),
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB1_EX")
	),

	array(
		"DIV" => "edit2",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_ADMIN_TAB2"),
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB2_EX")
	),

	array(
		"DIV" => "edit3",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_ADMIN_TAB3"),
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB3_EX")
	),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if (!$bBadCourse && $_SERVER["REQUEST_METHOD"] == "POST" && strlen($Update)>0 && check_bitrix_sessid())
{
	$arPREVIEW_PICTURE = $_FILES["PREVIEW_PICTURE"];
	$arPREVIEW_PICTURE["del"] = ${"PREVIEW_PICTURE_del"};
	$arPREVIEW_PICTURE["MODULE_ID"] = "learning";

	$arDETAIL_PICTURE = $_FILES["DETAIL_PICTURE"];
	$arDETAIL_PICTURE["del"] = ${"DETAIL_PICTURE_del"};
	$arDETAIL_PICTURE["MODULE_ID"] = "learning";

	$ch = new CChapter;

	$arFields = Array(
		"ACTIVE" => $ACTIVE,
		"CHAPTER_ID" => $CHAPTER_ID,
		"COURSE_ID" => $COURSE_ID,
		"NAME" => $NAME,
		"SORT" => $SORT,
		"CODE" => $CODE,

		"DETAIL_PICTURE" => $arDETAIL_PICTURE,
		"DETAIL_TEXT" => $DETAIL_TEXT,
		"DETAIL_TEXT_TYPE" => $DETAIL_TEXT_TYPE,

		"PREVIEW_PICTURE" => $arPREVIEW_PICTURE,
		"PREVIEW_TEXT" => $PREVIEW_TEXT,
		"PREVIEW_TEXT_TYPE" => $PREVIEW_TEXT_TYPE
	);



	if($ID>0)
	{
		$res = $ch->Update($ID, $arFields);
	}
	else
	{
		$ID = $ch->Add($arFields);
		$res = ($ID>0);
	}



	if(!$res)
	{
		//$strWarning .= $ch->LAST_ERROR."<br>";
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
		$bVarsFromForm = true;
	}
	else
	{
		if(strlen($apply)<=0)
		{
			if($from == "learn_admin")
				LocalRedirect("/bitrix/admin/learn_course_admin.php?lang=".LANG."&".GetFilterParams("filter_", false));
			elseif (strlen($return_url)>0)
			{
				if(strpos($return_url, "#CHAPTER_ID#")!==false)
				{
					$return_url = str_replace("#CHAPTER_ID#", $ID, $return_url);
				}
				LocalRedirect($return_url);
			}
			else
				LocalRedirect("/bitrix/admin/learn_chapter_admin.php?lang=". LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_", false));
		}

		LocalRedirect("/bitrix/admin/learn_chapter_edit.php?ID=".$ID."&CHAPTER_ID=".$CHAPTER_ID."&lang=". LANG."&COURSE_ID=".$COURSE_ID."&tabControl_active_tab=".urlencode($tabControl_active_tab).GetFilterParams("filter_", false));
	}
}

if (!$bBadCourse)
{
	if ($ID > 0)
		$APPLICATION->SetTitle($arCourse["NAME"].": ".GetMessage("LEARNING_CHAPTERS").": ".GetMessage("LEARNING_EDIT_TITLE"));
	else
		$APPLICATION->SetTitle($arCourse["NAME"].": ".GetMessage('LEARNING_CHAPTERS').": ".GetMessage("LEARNING_NEW_TITLE"));
}
else
	$APPLICATION->SetTitle(GetMessage('LEARNING_CHAPTERS').": ".GetMessage("LEARNING_EDIT_TITLE"));


//Defaults
$str_ACTIVE = "Y";
$str_DETAIL_TEXT_TYPE = $str_PREVIEW_TEXT_TYPE = "text";
$str_SORT = "500";
$str_CHAPTER_ID = $CHAPTER_ID;

$result = CChapter::GetByID($ID);
if(!$result->ExtractFields("str_"))
	$ID = 0;

if($bVarsFromForm)
{
	$ACTIVE = ($ACTIVE != "Y"? "N":"Y");
	$DB->InitTableVarsForEdit("b_learn_chapter", "", "str_");
}



//$adminChain->AddItem(array("TEXT"=>htmlspecialcharsex($arCourse["NAME"]), "LINK"=>"learn_chapter_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id="));


if(intval($CHAPTER_ID)>0)
{
	/*$nav = CChapter::GetNavChain($COURSE_ID, $str_CHAPTER_ID);
	while($nav->ExtractFields("nav_"))
	{
		$adminChain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>"learn_chapter_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id=".$nav_ID));
	}*/
}
else
{
	$adminChain->AddItem(array("TEXT"=>"<b>".GetMessage("LEARNING_CONTENT")."</b>", "LINK"=>""));
}


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


if (!$bBadCourse):


$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=>"learn_chapter_admin.php?COURSE_ID=".$COURSE_ID."&lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("MAIN_ADMIN_MENU_LIST")
	),
);


if ($ID > 0)
{
	$aContext[] = 	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_CREATE"),
		"LINK"=>"learn_chapter_edit.php?COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$CHAPTER_ID."&lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_ADD")
	);

	$aContext[] = 	array(
		"ICON" => "btn_delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("LEARNING_CONFIRM_DEL_MESSAGE")."'))window.location='learn_chapter_admin.php?COURSE_ID=".$COURSE_ID."&action=delete&ID=".$ID."&lang=".LANG."&".bitrix_sessid_get().GetFilterParams("filter_")."';",
	);

}
$context = new CAdminContextMenu($aContext);
$context->Show();


if($message)
	echo $message->Show();
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>&COURSE_ID=<?echo $COURSE_ID?>" enctype="multipart/form-data" name="chapter_edit">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?echo $ID?>">
<input type="hidden" name="from" value="<?echo htmlspecialchars($from)?>">
<?if(strlen($return_url)>0):?><input type="hidden" name="return_url" value="<?=htmlspecialchars($return_url)?>"><?endif?>

<?$tabControl->Begin();?>
<?$tabControl->BeginNextTab();?>
	<?if($ID>0):?>
	<tr>
		<td>ID:</td>
		<td><?echo $str_ID?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LEARNING_LAST_UPDATE")?>:</td>
		<td><?echo $str_TIMESTAMP_X?></td>
	</tr>
	<?endif;?>
	<tr>
		<td><?echo GetMessage("LEARNING_ACTIVE")?>:</td>
		<td>
			<input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("LEARNING_PARENT_CHAPTER_ID")?>:</td>
		<td>
		<?$l = CChapter::GetTreeList($COURSE_ID);?>
		<select name="CHAPTER_ID" style="width:60%;">
			<option value="0"><?echo GetMessage("LEARNING_CONTENT")?></option>
		<?
			while($l->ExtractFields("l_")):
				?><option value="<?echo $l_ID?>"<?if($str_CHAPTER_ID == $l_ID)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $l_DEPTH_LEVEL)?><?echo $l_NAME?></option><?
			endwhile;
		?>
		</select>
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?echo GetMessage("LEARNING_NAME")?>:</td>
		<td valign="top">
			<input type="text" name="NAME" size="50" maxlength="255" value="<?echo $str_NAME?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("LEARNING_SORT")?>:</td>
		<td>
			<input type="text" name="SORT" size="7" maxlength="10" value="<?echo $str_SORT?>">
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_CODE")?>:</td>
		<td>
			<input type="text" name="CODE" size="20" maxlength="255" value="<?echo $str_CODE?>">
		</td>
	</tr>

<?$tabControl->BeginNextTab();?>

	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"PREVIEW_TEXT",
				$str_PREVIEW_TEXT,
				"PREVIEW_TEXT_TYPE",
				$str_PREVIEW_TEXT_TYPE,
				300,
				"N",
				0,
				"",
				"",
				false,
				true,
				false,
				array('toolbarConfig' => CFileman::GetEditorToolbarConfig("learning_".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? 'public' : 'admin')))
			);?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td align="right"><?echo GetMessage("LEARNING_DESC_TYPE")?>:</td>
		<td>

				<input type="radio" name="PREVIEW_TEXT_TYPE" value="text"<?if($str_PREVIEW_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?> / <input type="radio" name="PREVIEW_TEXT_TYPE" value="html"<?if($str_PREVIEW_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>

		</td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<textarea style="width:100%;height:300px;" name="PREVIEW_TEXT" wrap="virtual"><?echo $str_PREVIEW_TEXT?></textarea>
		</td>
	</tr>
	<?endif;?>

	<tr>
		<td valign="top"><?echo GetMessage("LEARNING_PICTURE")?></td>
		<td>
			<?echo CFile::InputFile("PREVIEW_PICTURE", 20, $str_PREVIEW_PICTURE, false, 0, "IMAGE", "", 40);?><br>
			<?echo CFile::ShowImage($str_PREVIEW_PICTURE, 200, 200, "border=0", "", true)?>

		</td>
	</tr>

<?$tabControl->BeginNextTab();?>
	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td valign="top" colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"DETAIL_TEXT",
				$str_DETAIL_TEXT,
				"DETAIL_TEXT_TYPE",
				$str_DETAIL_TEXT_TYPE,
				300,
				"N",
				0,
				"",
				"",
				false,
				true,
				false,
				array('toolbarConfig' => CFileman::GetEditorToolbarConfig("learning_".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? 'public' : 'admin')))
			);?>
		</td>
	</tr>
	<?else:?>
	<tr>
		<td valign="top" align="right"><?echo GetMessage("LEARNING_DESC_TYPE")?></td>
		<td valign="top">

				<input type="radio" name="DETAIL_TEXT_TYPE" value="text"<?if($str_DETAIL_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?> / <input type="radio" name="DETAIL_TEXT_TYPE" value="html"<?if($str_DETAIL_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>

		</td>
	</tr>
	<tr>
		<td valign="top" align="center" colspan="2">
			<textarea style="width:100%;height:300px;" name="DETAIL_TEXT" wrap="virtual"><?echo $str_DETAIL_TEXT?></textarea>
		</td>
	</tr>
	<?endif?>

	<tr>
		<td valign="top"><?echo GetMessage("LEARNING_PICTURE")?></td>
		<td>
			<?echo CFile::InputFile("DETAIL_PICTURE", 20, $str_DETAIL_PICTURE, false, 0, "IMAGE", "", 40);?><br>
			<?echo CFile::ShowImage($str_DETAIL_PICTURE, 200, 200, "border=0", "", true)?>

		</td>
	</tr>



<?$tabControl->EndTab();?>
<?$tabControl->Buttons(Array("back_url" =>"learn_chapter_admin.php?lang=". LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_", false)));?>
<?$tabControl->End();?>
</form>
<?$tabControl->ShowWarnings("chapter_edit", $message);?>

<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?else: //if (!$bBadCourse)

$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=>"learn_course_admin.php?lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
	),
);

$context = new CAdminContextMenu($aContext);
$context->Show();

CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_COURSE"));
endif?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>