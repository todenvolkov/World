<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/prolog.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$arStatus = Array(
	"B" => GetMessage('LEARNING_ATTEMPT_STATUS_B'),
	"D" => GetMessage('LEARNING_ATTEMPT_STATUS_D'),
	"F" => GetMessage('LEARNING_ATTEMPT_STATUS_F'),
	"N" => GetMessage('LEARNING_ATTEMPT_STATUS_N'),
);

$message = null;
$bVarsFromForm = false;
$ID = intval($ID);
$bBadAttempt = false;

if (!$bBadAttempt)
{
	$r = CTestAttempt::GetByID($ID);
	if(!$r->ExtractFields("str_"))
		$bBadAttempt = true;

	$ar = $r->Fetch();
}

if($bBadAttempt)
{
	$APPLICATION->SetTitle(GetMessage("LEARNING_ADMIN_TITLE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aContext = array(
		array(
			"ICON" => "btn_list",
			"TEXT"=>GetMessage("LEARNING_BACK_TO_ADMIN"),
			"LINK"=>"learn_attempt_admin.php?lang=".LANG,
			"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
		),
	);
	$context = new CAdminContextMenu($aContext);
	$context->Show();

	CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_ATTEMPT_ID_EX"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$aTabs = array(
	array(
		"DIV" => "edit1",
		"ICON"=>"main_user_edit",
		"TAB" => GetMessage("LEARNING_ADMIN_TAB1"),
		"TITLE"=>GetMessage("LEARNING_ADMIN_TAB1_EX")
	),
);
$aTabs[] = $USER_FIELD_MANAGER->EditFormTab("LEARN_ATTEMPT");

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if (!$bBadAttempt && $_SERVER["REQUEST_METHOD"] == "POST" && strlen($Update)>0 && check_bitrix_sessid())
{
	$ta = new CTestAttempt;

	$arFields = Array(
		"SCORE" => $SCORE,
		"MAX_SCORE" => $MAX_SCORE,
		"COMPLETED" => $COMPLETED == "Y" ? "Y" : "N",
		"STATUS" => $STATUS,
		"DATE_START" => $DATE_START,
		"DATE_END" => $DATE_END,
	);
	$USER_FIELD_MANAGER->EditFormAddFields("LEARN_ATTEMPT", $arFields);

	$res = $ta->Update($ID, $arFields);

	if(!$res)
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);

		$bVarsFromForm = true;
	}
	else
	{
		if(strlen($apply)<=0)
		{
			if (strlen($return_url)>0)
				LocalRedirect($return_url);
			else
				LocalRedirect("/bitrix/admin/learn_attempt_admin.php?lang=". LANG.GetFilterParams("filter_", false));
		}

		LocalRedirect("/bitrix/admin/learn_attempt_edit.php?ID=".$ID."&tabControl_active_tab=".urlencode($tabControl_active_tab).GetFilterParams("filter_", false));
	}
}

$APPLICATION->SetTitle(GetMessage("LEARNING_ADMIN_TITLE"));

if($bVarsFromForm)
{
	$DB->InitTableVarsForEdit("b_learn_attempt", "", "str_");
}


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($message)
	echo $message->Show();

$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=>"learn_attempt_admin.php?lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("MAIN_ADMIN_MENU_LIST")
	),
);


if ($ID > 0)
{
	$aContext[] = 	array(
		"ICON" => "btn_delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("LEARNING_CONFIRM_DEL_MESSAGE")."'))window.location='learn_attempt_admin.php?lang=".LANG."&action=delete&ID=".$ID."&lang=".LANG."&".bitrix_sessid_get().GetFilterParams("filter_")."';",
	);

}
$context = new CAdminContextMenu($aContext);
$context->Show();


?>

<form method="post" action="learn_attempt_edit.php?lang=<?echo LANG?><?echo GetFilterParams("filter_");?>" enctype="multipart/form-data" name="form_attempt">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="from" value="<?echo htmlspecialchars($from)?>">
<input type="hidden" name="return_url" value="<?echo htmlspecialchars($return_url)?>">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?$tabControl->Begin();?>
<?$tabControl->BeginNextTab();?>

<tr>
	<td width="50%"><?=GetMessage("LEARNING_ADMIN_USER")?>:</td>
	<td width="50%">
		[<a href="user_edit.php?lang=<?php echo LANG?>&ID=<?php echo $str_USER_ID?>" title="<?php echo GetMessage("LEARNING_CHANGE_USER_PROFILE")?>"><?php echo $str_USER_ID?></a>] <?php echo $str_USER_NAME?>
	</td>
</tr>

<tr>
	<td><?=GetMessage("LEARNING_ADMIN_TEST")?>:</td>
	<td>
		<?php echo $str_TEST_NAME?>
	</td>
</tr>

<tr>
	<td><?=GetMessage("LEARNING_ADMIN_QUESTIONS")?>:</td>
	<td>
		<a href="learn_test_result_admin.php?lang=<?php echo LANG?>&ATTEMPT_ID=<?php echo $str_ID?>"><?php echo $str_QUESTIONS?></a>
	</td>
</tr>

<tr>
	<td><?=GetMessage("LEARNING_ADMIN_DATE_START")?>:</td>
	<td>
		<?echo CalendarDate("DATE_START", $str_DATE_START, "form_attempt", "20")?>
	</td>
</tr>

<tr>
	<td><?=GetMessage("LEARNING_ADMIN_DATE_END")?>:</td>
	<td>
		<?echo CalendarDate("DATE_END", $str_DATE_END, "form_attempt", "20")?>
	</td>
</tr>

<tr>
	<td><?=GetMessage("LEARNING_ADMIN_STATUS")?>:</td>
	<td>
		<select name="STATUS">
			<?php foreach($arStatus as $key=>$value):?>
				<option value="<?php echo $key?>"<?php echo ($key == $str_STATUS ? " selected" : "")?>><?php echo $value?></option>
			<?php endforeach?>
		</select>
	</td>
</tr>

<tr>
	<td><?=GetMessage("LEARNING_ADMIN_COMPLETED")?>:</td>
	<td>
		<input type="checkbox" name="COMPLETED" value="Y"<?if($str_COMPLETED=="Y")echo " checked"?>>
	</td>
</tr>

<tr>
	<td width="50%"><?=GetMessage("LEARNING_ADMIN_SCORE")?>:</td>
	<td width="50%">
		<input type="text" name="SCORE" size="4" maxlength="255" value="<?echo $str_SCORE?>">
	</td>
</tr>

<tr>
	<td><?=GetMessage("LEARNING_ADMIN_MAX_SCORE")?>:</td>
	<td>
		<input type="text" name="MAX_SCORE" size="4" maxlength="255" value="<?echo $str_MAX_SCORE?>">
	</td>
</tr>
<?
$tabControl->BeginNextTab();
$USER_FIELD_MANAGER->EditFormShowTab("LEARN_ATTEMPT", $bVarsFromForm, $ID);
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(Array("back_url" =>"learn_attempt_admin.php?lang=". LANG.GetFilterParams("filter_", false)));
$tabControl->End();?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>