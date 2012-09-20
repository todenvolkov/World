<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/learning/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$strWarning="";
$message = null;

$bVarsFromForm = false;
$ID= (is_set($_REQUEST["ID"]) ? intval($ID) : 0);
$Perm = CCourse::GetPermission($ID);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("LEARNING_ADMIN_TAB1"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB1_EX")),

	array("DIV" => "edit2", "TAB" => GetMessage("LEARNING_ADMIN_TAB3"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB3_EX")),

	array("DIV" => "edit3", "TAB" => GetMessage("LEARNING_ADMIN_TAB4"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB4_EX")),


	array("DIV" => "edit4", "TAB" => GetMessage("LEARNING_ADMIN_TAB2"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB2_EX")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);


if ($_SERVER["REQUEST_METHOD"] == "POST" && $Perm >= "X" && strlen($_POST["Update"])>0 && check_bitrix_sessid())
{
	$course = new CCourse;

	$arPREVIEW_PICTURE = $_FILES["PREVIEW_PICTURE"];
	$arPREVIEW_PICTURE["del"] = $PREVIEW_PICTURE_del;
	$arPREVIEW_PICTURE["MODULE_ID"] = "learning";

	$arFields = Array(
		"ACTIVE" => $ACTIVE,
		"NAME" => $NAME,
		"CODE" => $CODE,
		"SITE_ID" => $SITE_ID, //Sites
		"GROUP_ID" => $GROUP, //Permission
		"SORT" => $SORT,
		"DESCRIPTION" => $DESCRIPTION,
		"DESCRIPTION_TYPE" => $DESCRIPTION_TYPE,

		"PREVIEW_PICTURE" => $arPREVIEW_PICTURE,
		"PREVIEW_TEXT" => $PREVIEW_TEXT,
		"PREVIEW_TEXT_TYPE" => $PREVIEW_TEXT_TYPE,

		"ACTIVE_FROM" => $ACTIVE_FROM,
		"ACTIVE_TO" => $ACTIVE_TO
	);

	if($ID>0)
	{
		$res = $course->Update($ID, $arFields);
	}
	else
	{
		$ID = $course->Add($arFields);
		$res = ($ID>0);
	}

	if(!$res)
	{
		//$strWarning .= $course->LAST_ERROR."<br>";
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
		$bVarsFromForm = true;
	}
	else
	{
		if(!$bVarsFromForm)
		{
			if(strlen($apply)<=0)
			{
				if(strlen($return_url)>0)
				{
					if(strpos($return_url, "#COURSE_ID#")!==false)
					{
						$return_url = str_replace("#COURSE_ID#", $ID, $return_url);
					}
					LocalRedirect($return_url);
				}
				else
					LocalRedirect("/bitrix/admin/learn_course_admin.php?lang=".LANG."&".GetFilterParams("filter_", false));
			}
			LocalRedirect("/bitrix/admin/learn_course_edit.php?lang=".LANG."&ID=".$ID."&".$tabControl->ActiveTabParam().GetFilterParams("filter_", false));
		}
	}

}

if($ID>0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("LEARNING_EDIT_TITLE2")));
else
	$APPLICATION->SetTitle(GetMessage("LEARNING_EDIT_TITLE1"));

//Defaults
$str_ACTIVE="Y";
$str_SORT="500";
$str_DESCRIPTION_TYPE = $str_PREVIEW_TEXT_TYPE = "text";


$course = new CCourse;
$res = $course->GetByID($ID);

if(!$res->ExtractFields("str_"))
{
	$ID = 0;
}
else
{
	$str_SITE_ID = Array();
	$db_SITE_ID = CCourse::GetSite($ID);
	while($ar_SITE_ID = $db_SITE_ID->Fetch())
		$str_SITE_ID[] = $ar_SITE_ID["LID"];
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if($bVarsFromForm)
{
	$str_SITE_ID = $SITE_ID;
	$ACTIVE = ($ACTIVE != "Y"? "N":"Y");
	$DB->InitTableVarsForEdit("b_learn_course", "", "str_");
}

$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=>"learn_course_admin.php?lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
	),
);

if ($Perm >= "X" && $ID > 0)
{
	$aContext[] = 	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_CREATE"),
		"LINK"=>"learn_course_edit.php?lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_ADD")
	);

	$aContext[] = 	array(
		"ICON" => "btn_delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("LEARNING_CONFIRM_DEL_MESSAGE")."'))window.location='learn_course_admin.php?action=delete&ID=".$ID."&lang=".LANG."&".bitrix_sessid_get().GetFilterParams("filter_")."';",
	);

}

$context = new CAdminContextMenu($aContext);
$context->Show();

if($message)
	echo $message->Show();


if($Perm>="X"):?>


<form method="post" enctype="multipart/form-data" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>&ID=<?=$ID?>" name="form_course">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?if(strlen($return_url)>0):?><input type="hidden" name="return_url" value="<?=htmlspecialchars($return_url)?>"><?endif?>

<?$tabControl->Begin();?>
<?$tabControl->BeginNextTab();?>
<!-- ID -->
	<?if($ID>0):?>
	<tr>
		<td>ID:</td>
		<td><?=$str_ID?></td>
	</tr>

<!-- Timestamp_X -->
	<tr>
		<td><?echo GetMessage("LEARNING_LAST_UPDATE")?>:</td>
		<td><?=$str_TIMESTAMP_X?></td>
	</tr>
	<? endif; ?>

<!-- Active -->
	<tr>
		<td><?echo GetMessage("LEARNING_ACTIVE")?>:</td>
		<td><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>></td>
	</tr>

<!-- Active period-->
	<tr>
		<td><?echo GetMessage("LEARNING_ACTIVE_PERIOD")?>(<?echo CLang::GetDateFormat("SHORT");?>):</td>
		<td>
			<?echo CalendarPeriod("ACTIVE_FROM", $str_ACTIVE_FROM, "ACTIVE_TO", $str_ACTIVE_TO, "form_course", "N", "", "", "19")?>

		</td>
	</tr>

<!-- CODE -->
	<tr>
		<td><? echo GetMessage("LEARNING_CODE")?>:</td>
		<td>
			<input type="text" name="CODE" size="20" maxlength="40" value="<?=$str_CODE?>">
		</td>
	</tr>

<!-- Site -->
	<tr>
		<td valign="top"><span class="required">*</span><?echo GetMessage("LEARNING_SITE_ID")?>:</td>
		<td><?=CLang::SelectBoxMulti("SITE_ID", $str_SITE_ID);?></td>
	</tr>


<!-- Name -->
	<tr>
		<td><span class="required">*</span><? echo GetMessage("LEARNING_NAME")?>:</td>
		<td>
			<input type="text" name="NAME" size="40" maxlength="255" value="<?echo $str_NAME?>">
		</td>
	</tr>

<!-- Sort -->
	<tr>
		<td><? echo GetMessage("LEARNING_SORT")?>:</td>
		<td>
			<input type="text" name="SORT" size="10" maxlength="10" value="<?echo $str_SORT?>">
		</td>
	</tr>
<?$tabControl->BeginNextTab();?>
<!-- 	<tr class="heading">
		<td colspan="2"><?echo GetMessage("LEARNING_ELEMENT_PREVIEW")?></td>
	</tr> -->

	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"PREVIEW_TEXT",
				$str_PREVIEW_TEXT,
				"PREVIEW_TEXT_TYPE",
				$str_PREVIEW_TEXT_TYPE,
				200,
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
		<td align="center"><?echo GetMessage("LEARNING_DESC_TYPE")?>:</td>
		<td>
				<input type="radio" name="PREVIEW_TEXT_TYPE" value="text"<?if($str_PREVIEW_TEXT_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?> / <input type="radio" name="PREVIEW_TEXT_TYPE" value="html"<?if($str_PREVIEW_TEXT_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<textarea style="width:100%; height:200px;" name="PREVIEW_TEXT" wrap="virtual"><?echo $str_PREVIEW_TEXT?></textarea>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td valign="top"><?echo GetMessage("LEARNING_PICTURE")?></td>
		<td>
			<?echo CFile::InputFile("PREVIEW_PICTURE", 20, $str_PREVIEW_PICTURE, false, 0, "IMAGE", "", 40);?><br>
			<?echo CFile::ShowImage($str_PREVIEW_PICTURE, 200, 200, "border=0", "", true)?>
		</td>
	</tr>
<?$tabControl->BeginNextTab();?>
<!-- Description -->
	<!-- <tr class="heading">
		<td colspan="2"><?echo GetMessage("LEARNING_ELEMENT_DETAIL")?></td>
	</tr> -->
	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"DESCRIPTION",
				$str_DESCRIPTION,
				"DESCRIPTION_TYPE",
				$str_DESCRIPTION_TYPE,
				250,
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
		<td align="center"><?echo GetMessage("LEARNING_DESC_TYPE")?>:</td>
		<td>
			<input type="radio" name="DESCRIPTION_TYPE" value="text"<?if($str_DESCRIPTION_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?>
			<input type="radio" name="DESCRIPTION_TYPE" value="html"<?if($str_DESCRIPTION_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<textarea style="width:100%; height:250px;" name="DESCRIPTION" wrap="off"><?echo $str_DESCRIPTION?></textarea>
		</td>
	</tr>
	<?endif?>
<?$tabControl->BeginNextTab();?>

	<?
	$arPermType = Array(
			"D"=>GetMessage("LEARNING_ACCESS_D"),
			"R"=>GetMessage("LEARNING_ACCESS_R"),
			"W"=>GetMessage("LEARNING_ACCESS_W"),
			"X"=>GetMessage("LEARNING_ACCESS_X"));

	$perm = CCourse::GetGroupPermissions($ID);
	$groups = CGroup::GetList($by="sort", $order="asc", Array("ID"=>"~1"));
	while($r = $groups->ExtractFields("g_")):
		if($bVarsFromForm)
			$strSelected = $GROUP[$g_ID];
		else
			$strSelected = $perm[$g_ID];

	if (	$strSelected!="R" &&
		$strSelected!="W" &&
		$strSelected!="X" &&
		$ID>0 && !$bVarsFromForm)
		$strSelected="D";
	?>
	<tr>
		<td width="40%"><?echo $g_NAME?> [<a title="<?=GetMessage("LEARNING_GROUP_ID_TITLE")?>" href="/bitrix/admin/group_edit.php?ID=<?=$g_ID?>&lang=<?=LANGUAGE_ID?>"><?=$g_ID?></a>]:</td>
		<td width="60%">
				<select name="GROUP[<?echo $g_ID?>]">
				<?
				reset($arPermType);
				while(list ($key, $val) = each ($arPermType)):
				?>
					<option value="<?echo $key?>"<?if($strSelected == $key)echo " selected"?>><?echo htmlspecialcharsex($val)?></option>
				<?endwhile?>
				</select>
		</td>
	</tr>
	<?endwhile?>
<?
$tabControl->Buttons(Array("back_url" =>"learn_course_admin.php?lang=". LANG.GetFilterParams("filter_", false)));
$tabControl->End();
?>
</form>
<?$tabControl->ShowWarnings("form_course", $message);?>

<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?else:?>
<?CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_COURSE"));?>
<?endif?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
