<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/learning/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$message = null;
$bVarsFromForm = false;
$ID = intval($ID);
$COURSE_ID = intval($COURSE_ID);

if(CCourse::GetPermission($COURSE_ID)<"W")
{
	$APPLICATION->SetTitle(GetMessage('LEARNING_TESTS'));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aContext = array(
		array(
			"ICON" => "btn_list",
			"TEXT"=>GetMessage("LEARNING_BACK_TO_ADMIN"),
			"LINK"=>"learn_course_admin.php?lang=".LANG.GetFilterParams("filter_"),
			"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
		),
	);
	$context = new CAdminContextMenu($aContext);
	$context->Show();

	CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_COURSE"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_POST["Update"])>0 && check_bitrix_sessid())
{
	$test = new CTest;


	$QUESTIONS_AMOUNT = (intval(${"QUESTIONS_AMOUNT_".$QUESTIONS_FROM})>0 ? intval(${"QUESTIONS_AMOUNT_".$QUESTIONS_FROM}) : 0);
	$QUESTIONS_FROM_ID = (intval(${"QUESTIONS_FROM_ID_".$QUESTIONS_FROM})>0 ? intval(${"QUESTIONS_FROM_ID_".$QUESTIONS_FROM}) : 0);

	if ($CURRENT_INDICATION == "Y")
	{
		$CURRENT_INDICATION =
			($CURRENT_INDICATION_PERCENT == "Y" ? 1 : 0) +
			($CURRENT_INDICATION_MARK == "Y" ? 2 : 0);
	}
	else
	{
		$CURRENT_INDICATION = 0;
	}

	if ($FINAL_INDICATION == "Y")
	{
		$FINAL_INDICATION =
			($FINAL_INDICATION_CORRECT_COUNT == "Y" ? 1 : 0) +
			($FINAL_INDICATION_SCORE == "Y" ? 2 : 0) +
			($FINAL_INDICATION_MARK == "Y" ? 4 : 0) +
			($FINAL_INDICATION_MESSAGE == "Y" ? 8 : 0);
	}
	else
	{
		$FINAL_INDICATION = 0;
	}

	$MIN_TIME_BETWEEN_ATTEMPTS = $MIN_TIME_BETWEEN_ATTEMPTS_D * 60 * 24 + $MIN_TIME_BETWEEN_ATTEMPTS_H * 60 + $MIN_TIME_BETWEEN_ATTEMPTS_M;

	$NEXT_QUESTION_ON_ERROR = ($SHOW_ERRORS == "Y" && $NEXT_QUESTION_ON_ERROR == "N" && $PASSAGE_TYPE == "2") ? "N" : "Y";

	$arFields = Array(
		"ACTIVE" => $ACTIVE,
		"COURSE_ID" => $COURSE_ID,
		"NAME" => $NAME,
		"CODE" => $CODE,
		"SORT" => $SORT,
		"DESCRIPTION" => $DESCRIPTION,
		"DESCRIPTION_TYPE" => $DESCRIPTION_TYPE,

		"TIME_LIMIT" => $TIME_LIMIT,
		"ATTEMPT_LIMIT" => $ATTEMPT_LIMIT,
		"COMPLETED_SCORE" => $COMPLETED_SCORE,

		"QUESTIONS_FROM" => $QUESTIONS_FROM,
		"QUESTIONS_AMOUNT" => $QUESTIONS_AMOUNT,
		"QUESTIONS_FROM_ID" => $QUESTIONS_FROM_ID,

		"RANDOM_QUESTIONS" => $RANDOM_QUESTIONS,
		"RANDOM_ANSWERS" => $RANDOM_ANSWERS,

		"APPROVED" => $APPROVED,
		"INCLUDE_SELF_TEST" => $INCLUDE_SELF_TEST,

		"PASSAGE_TYPE" => $PASSAGE_TYPE,

		"PREVIOUS_TEST_ID" => $PREVIOUS_TEST_ID,
		"PREVIOUS_TEST_SCORE" => $PREVIOUS_TEST_SCORE,

		"INCORRECT_CONTROL" => $INCORRECT_CONTROL,

		"CURRENT_INDICATION" => $CURRENT_INDICATION,
		"FINAL_INDICATION" => $FINAL_INDICATION,

		"SHOW_ERRORS" => $SHOW_ERRORS,
		"NEXT_QUESTION_ON_ERROR" => $NEXT_QUESTION_ON_ERROR,

		"MIN_TIME_BETWEEN_ATTEMPTS" => $MIN_TIME_BETWEEN_ATTEMPTS,
	);

	if (strlen($arFields["COMPLETED_SCORE"]) <=0)
	{
		unset($arFields["COMPLETED_SCORE"]);
		$arFields["APPROVED"] = "N";
	}

	if (intval($arFields["PREVIOUS_TEST_ID"]) <= 0)
	{
		$arFields["PREVIOUS_TEST_ID"] = false;
	}
	if (strlen($arFields["PREVIOUS_TEST_SCORE"]) <=0)
	{
		$arFields["PREVIOUS_TEST_SCORE"] = 0;
	}

	if($ID>0)
	{
		$res = $test->Update($ID, $arFields);
	}
	else
	{
		$ID = $test->Add($arFields);
		$res = ($ID>0);
	}

	if(!$res)
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
		$bVarsFromForm = true;
	}
	else
	{
		//Marks
		$marks = CLTestMark::GetList(Array(),Array("TEST_ID" => $ID));

		$arMarks = $arScores = array();

		$DB->StartTransaction();

		while ($m = $marks->GetNext())
		{
			//delete?
			if (${"MARK_".$m["ID"]."_DEL"} == "Y")
			{
					if(!CLTestMark::Delete($m["ID"]))
					{
						$message = new CAdminMessage(Array("MESSAGE" => GetMessage("LEARNING_DELETE_ERROR").$m["ID"]));
						$bVarsFromForm = true;
					}
			}

			if(in_array(${"SCORE_".$m["ID"]}, $arScores))
			{
				$message = new CAdminMessage(Array("MESSAGE" =>  str_replace("##SCORE##", ${"SCORE_".$m["ID"]}, GetMessage("LEARNING_SCORE_EXISTS_ERROR"))));
				$bVarsFromForm = true;
			}
			elseif(in_array(${"MARK_".$m["ID"]}, $arMarks))
			{
				$message = new CAdminMessage(Array("MESSAGE" => str_replace("##MARK##", ${"MARK_".$m["ID"]}, GetMessage("LEARNING_MARK_EXISTS_ERROR"))));
				$bVarsFromForm = true;
			}
			else
			{
				if (${"MARK_".$m["ID"]."_DEL"} != "Y")
				{
					$arMarks[] = ${"MARK_".$m["ID"]};
					$arScores[] = ${"SCORE_".$m["ID"]};
				}
				$arFields = Array(
					"TEST_ID" => $ID,
					"SCORE" => ${"SCORE_".$m["ID"]},
					"MARK" => ${"MARK_".$m["ID"]},
					"DESCRIPTION" => ${"DESCRIPTION_".$m["ID"]},
				);

				$mrk = new CLTestMark;
				$res = $mrk->Update($m["ID"], $arFields);
				if (!$res)
				{
					$message = new CAdminMessage(Array("MESSAGE" => GetMessage("LEARNING_SAVE_ERROR").$m["ID"]));
					$bVarsFromForm = true;
				}
			}
		}

		//add new
		for ($i=1; $i<6; $i++)
		{
			if (strlen(${"N_MARK_".$i})<=0 && strlen(${"N_SCORE_".$i})<=0) continue;

			if(in_array(${"N_SCORE_".$i}, $arScores))
			{
				$message = new CAdminMessage(Array("MESSAGE" => str_replace("##SCORE##", ${"N_SCORE_".$i}, GetMessage("LEARNING_SCORE_EXISTS_ERROR"))));
				$bVarsFromForm = true;
			}
			elseif(in_array(${"N_MARK_".$i}, $arMarks))
			{
				$message = new CAdminMessage(Array("MESSAGE" => str_replace("##MARK##", ${"N_MARK_".$i}, GetMessage("LEARNING_MARK_EXISTS_ERROR"))));
				$bVarsFromForm = true;
			}
			else
			{
				$arMarks[] = ${"N_MARK_".$i};
				$arScores[] = ${"N_SCORE_".$i};
				$arFields = Array(
					"SCORE" => ${"N_SCORE_".$i},
					"MARK" => ${"N_MARK_".$i},
					"DESCRIPTION" => ${"DESCRIPTION_".$m["ID"]},
					"TEST_ID" => $ID,
				);

				$mark = new CLTestMark;
				$MarkID = $mark->Add($arFields);
				if (intval($MarkID)<=0)
				{
					if ($e = $APPLICATION->GetException())
						$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
					$bVarsFromForm = true;
				}
			}
		}

		if (sizeof($arScores) && !in_array(100, $arScores))
		{
			$message = new CAdminMessage(Array("MESSAGE" => GetMessage("LEARNING_MAX_MARK_ERROR")));
			$bVarsFromForm = true;
		}

		//Redirect
		if (!$bVarsFromForm)
		{
			$DB->Commit();

			if(strlen($apply)<=0)
			{
				if($from == "learn_admin")
					LocalRedirect("/bitrix/admin/learn_course_admin.php?lang=".LANG."&".GetFilterParams("filter_", false));
				elseif (strlen($return_url)>0)
				{
					if(strpos($return_url, "#TEST_ID#")!==false)
					{
						$return_url = str_replace("#TEST_ID#", $ID, $return_url);
					}
					LocalRedirect($return_url);
				}
				else
					LocalRedirect("/bitrix/admin/learn_test_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_", false));
			}
			LocalRedirect("/bitrix/admin/learn_test_edit.php?lang=".LANG."&ID=".$ID."&COURSE_ID=".$COURSE_ID."&tabControl_active_tab=".urlencode($tabControl_active_tab).GetFilterParams("filter_", false));

		}
		else
		{
			$DB->Rollback();
		}
	}
}

if($ID>0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("LEARNING_EDIT_TITLE2")));
else
	$APPLICATION->SetTitle(GetMessage("LEARNING_EDIT_TITLE1"));

//Defaults
$str_ACTIVE = "Y";
$str_SORT = "500";
//$str_APPROVED = "Y";
$str_COMPLETED_SCORE = "95";
//$str_INCLUDE_SELF_TEST = "N";
$str_RANDOM_QUESTIONS = "Y";
$str_RANDOM_ANSWERS="Y";
$str_QUESTIONS_FROM="A";
$str_QUESTIONS_AMOUNT = "0";
$str_TIME_LIMIT = "0";
$str_ATTEMPT_LIMIT = "0";
$str_DESCRIPTION_TYPE = "text";
$str_SKIP_QUESTION = "N";
$str_FINAL_RESPONSE = "Y";
$str_PASSAGE_TYPE = "0";
$str_PREVIOUS_TEST_ID = "0";
$str_PREVIOUS_TEST_SCORE = "95";
$str_INCORRECT_CONTROL = "N";
$str_CURRENT_INDICATION_PERCENT = "N";
$str_CURRENT_INDICATION_MARK = "N";
$str_CURRENT_INDICATION = "N";
$str_FINAL_INDICATION_CORRECT_COUNT = "N";
$str_FINAL_INDICATION_SCORE = "N";
$str_FINAL_INDICATION_MARK = "N";
$str_FINAL_INDICATION_MESSAGE = "N";
$str_FINAL_INDICATION = "N";
$str_SHOW_ERRORS = "N";
$str_NEXT_QUESTION_ON_ERROR = "Y";

$test = new CTest;
$res = $test->GetByID($ID);
if(!$res->ExtractFields("str_"))
{
	$ID = 0;
}
else
{
	if ($str_CURRENT_INDICATION > 0)
	{
		$str_CURRENT_INDICATION_PERCENT = ($str_CURRENT_INDICATION & 1) ? "Y" : "N";
		$str_CURRENT_INDICATION_MARK = ($str_CURRENT_INDICATION & 2) >> 1 ? "Y" : "N";
		$str_CURRENT_INDICATION = "Y";
	}

	if ($str_FINAL_INDICATION > 0)
	{
		$str_FINAL_INDICATION_CORRECT_COUNT = ($str_FINAL_INDICATION & 1) ? "Y" : "N";
		$str_FINAL_INDICATION_SCORE = ($str_FINAL_INDICATION & 2) >> 1 ? "Y" : "N";
		$str_FINAL_INDICATION_MARK = ($str_FINAL_INDICATION & 4) >> 2 ? "Y" : "N";
		$str_FINAL_INDICATION_MESSAGE = ($str_FINAL_INDICATION & 8) >> 3 ? "Y" : "N";
		$str_FINAL_INDICATION = "Y";
	}

	$str_MIN_TIME_BETWEEN_ATTEMPTS_D = floor($str_MIN_TIME_BETWEEN_ATTEMPTS / (60 * 24));
	$str_MIN_TIME_BETWEEN_ATTEMPTS_H = floor(($str_MIN_TIME_BETWEEN_ATTEMPTS - $str_MIN_TIME_BETWEEN_ATTEMPTS_D * 60 * 24) / 60);
	$str_MIN_TIME_BETWEEN_ATTEMPTS_M = $str_MIN_TIME_BETWEEN_ATTEMPTS - $str_MIN_TIME_BETWEEN_ATTEMPTS_D * 60 * 24 - $str_MIN_TIME_BETWEEN_ATTEMPTS_H * 60;
}

if($bVarsFromForm)
{
	$ACTIVE = ($ACTIVE != "Y"? "N":"Y");
	$APPROVED = ($APPROVED != "Y"? "N":"Y");
	$RANDOM_QUESTIONS = ($RANDOM_QUESTIONS != "Y"? "N":"Y");
	$RANDOM_ANSWERS = ($RANDOM_ANSWERS != "Y"? "N":"Y");
	$INCORRECT_CONTROL = ($INCORRECT_CONTROL != "Y"? "N":"Y");
	$CURRENT_INDICATION = ($CURRENT_INDICATION == 0 ? "N":"Y");
	$FINAL_INDICATION = ($FINAL_INDICATION == 0 ? "N":"Y");

	$SHOW_ERRORS = ($SHOW_ERRORS != "Y"? "N":"Y");
	$NEXT_QUESTION_ON_ERROR = ($NEXT_QUESTION_ON_ERROR != "Y"? "N":"Y");
	$DB->InitTableVarsForEdit("b_learn_test", "", "str_");

	$str_CURRENT_INDICATION_PERCENT = ($CURRENT_INDICATION_PERCENT != "Y"? "N":"Y");
	$str_CURRENT_INDICATION_MARK = ($CURRENT_INDICATION_MARK != "Y"? "N":"Y");
	$str_FINAL_INDICATION_CORRECT_COUNT = ($FINAL_INDICATION_CORRECT_COUNT != "Y"? "N":"Y");
	$str_FINAL_INDICATION_SCORE = ($FINAL_INDICATION_SCORE != "Y"? "N":"Y");
	$str_FINAL_INDICATION_MARK = ($FINAL_INDICATION_MARK != "Y"? "N":"Y");
	$str_FINAL_INDICATION_MESSAGE = ($FINAL_INDICATION_MESSAGE != "Y"? "N":"Y");

	$str_MIN_TIME_BETWEEN_ATTEMPTS_D = $MIN_TIME_BETWEEN_ATTEMPTS_D;
	$str_MIN_TIME_BETWEEN_ATTEMPTS_H = $MIN_TIME_BETWEEN_ATTEMPTS_H;
	$str_MIN_TIME_BETWEEN_ATTEMPTS_M = $MIN_TIME_BETWEEN_ATTEMPTS_M;
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<?
if ($message)
	echo $message->Show();



$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=>"learn_test_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("MAIN_ADMIN_MENU_LIST")
	),
);


if ($ID > 0)
{
	$aContext[] = 	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_CREATE"),
		"LINK"=>"learn_test_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_ADD")
	);

	$aContext[] = 	array(
		"ICON" => "btn_delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("LEARNING_CONFIRM_DEL_MESSAGE")."'))window.location='learn_test_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&action=delete&ID=".$ID."&".bitrix_sessid_get().GetFilterParams("filter_")."';",
	);

}
$context = new CAdminContextMenu($aContext);
$context->Show();
?>

<form method="post" enctype="multipart/form-data" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>&ID=<?=$ID?>&COURSE_ID=<?=$COURSE_ID?>" name="form1">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?echo $ID?>">
<input type="hidden" name="COURSE_ID" value="<?echo $COURSE_ID?>">
<input type="hidden" name="from" value="<?echo htmlspecialchars($from)?>">
<?if(strlen($return_url)>0):?><input type="hidden" name="return_url" value="<?=htmlspecialchars($return_url)?>"><?endif?>
<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage('LEARNING_TEST'), "ICON"=>"main_user_edit", "TITLE"=>GetMessage('LEARNING_TEST_TITLE')),
	array("DIV" => "edit2", "TAB" => GetMessage('LEARNING_DESC'), "ICON"=>"main_user_edit", "TITLE"=>GetMessage('LEARNING_DESC_TITLE')),
	array("DIV" => "edit3", "TAB" => GetMessage('LEARNING_MARKS'), "ICON"=>"main_user_edit", "TITLE"=>GetMessage('LEARNING_MARKS_TITLE')),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
$tabControl->BeginNextTab();?>

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

	<tr>
		<td valign="top"><? echo GetMessage("LEARNING_QUESTIONS_FROM")?>:</td>
		<td>
			<table>
			<tr>
				<td colspan="2"><input type="radio" name="QUESTIONS_FROM" value="A"<?if($str_QUESTIONS_FROM=="A" && intval($str_QUESTIONS_AMOUNT)==0)echo " checked"?>  onClick="OnChangeAnswer('');"><? echo GetMessage("LEARNING_QUESTIONS_FROM_ALL")?></td>
			</tr>

			<?$l = CChapter::GetTreeList($COURSE_ID);?>
			<?php if ($arChapter = $l->Fetch()):?>
			<tr>
				<td colspan="2">
					<input type="radio" name="QUESTIONS_FROM" value="H"<?if($str_QUESTIONS_FROM=="H" && intval($str_QUESTIONS_AMOUNT)==0)echo " checked"?>  onClick="OnChangeAnswer('H');"><? echo GetMessage("LEARNING_QUESTIONS_FROM_ALL_CHAPTER")?>
					<select name="QUESTIONS_FROM_ID_H">
						<?php while($arChapter):?>
							<option value="<?echo $arChapter["ID"]?>"<?if($str_QUESTIONS_FROM=="H" && $str_QUESTIONS_FROM_ID == $arChapter["ID"])echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $arChapter["DEPTH_LEVEL"])?><?echo htmlspecialchars($arChapter["NAME"])?></option>
							<?php $arChapter = $l->Fetch()?>
						<?php endwhile?>
					</select>
				</td>
			</tr>
			<?php endif?>

			<?$l = CLesson::GetList(array(), array("COURSE_ID" => $COURSE_ID, "ACTIVE" => "Y"));?>
			<?php if ($arLesson = $l->Fetch()):?>
			<tr>
				<td colspan="2">
					<input type="radio" name="QUESTIONS_FROM" value="S"<?if($str_QUESTIONS_FROM=="S" && intval($str_QUESTIONS_AMOUNT)==0)echo " checked"?>  onClick="OnChangeAnswer('S');"><? echo GetMessage("LEARNING_QUESTIONS_FROM_ALL_LESSON")?>
					<select name="QUESTIONS_FROM_ID_S">
						<?php while($arLesson):?>
							<option value="<?echo $arLesson["ID"]?>"<?if($str_QUESTIONS_FROM=="S" && $str_QUESTIONS_FROM_ID == $arLesson["ID"])echo " selected"?>><?echo htmlspecialchars($arLesson["NAME"])?></option>
							<?php $arLesson = $l->Fetch()?>
						<?php endwhile?>
					</select>
				</td>
			</tr>
			<?php endif?>

			<tr>
				<td><input type="radio" name="QUESTIONS_FROM" value="A"<?if($str_QUESTIONS_FROM=="A" && intval($str_QUESTIONS_AMOUNT)!=0)echo " checked"?> onClick="OnChangeAnswer('A');"><input type="text" name="QUESTIONS_AMOUNT_A" size="2" value="<?echo ($str_QUESTIONS_FROM=="A" && $str_QUESTIONS_AMOUNT!=0? $str_QUESTIONS_AMOUNT : "")?>"></td>
				<td><? echo GetMessage("LEARNING_QUESTIONS_FROM_COURSE")?></td>
			</tr>

			<tr>
				<td><input type="radio" name="QUESTIONS_FROM" value="C"<?if($str_QUESTIONS_FROM=="C")echo " checked"?> onClick="OnChangeAnswer('C');"><input type="text" name="QUESTIONS_AMOUNT_C" size="2" value="<?echo ($str_QUESTIONS_FROM=="C" ? $str_QUESTIONS_AMOUNT : "")?>"></td>
				<td><? echo GetMessage("LEARNING_QUESTIONS_FROM_CHAPTERS")?></td>
			</tr>

			<tr>
				<td><input type="radio" name="QUESTIONS_FROM" value="L"<?if($str_QUESTIONS_FROM=="L")echo " checked"?> onClick="OnChangeAnswer('L');"><input type="text" name="QUESTIONS_AMOUNT_L" size="2" value="<?echo ($str_QUESTIONS_FROM=="L" ? $str_QUESTIONS_AMOUNT : "")?>"></td>
				<td><? echo GetMessage("LEARNING_QUESTIONS_FROM_LESSONS")?></td>
			</tr>
			</table>

<script type="text/javascript">
<?
if ($str_QUESTIONS_AMOUNT == '0' && $str_QUESTIONS_FROM != "S" && $str_QUESTIONS_FROM != "H")
	$str = "";
else
	$str = $str_QUESTIONS_FROM;
?>

var QUESTIONS_FROM = '<?=$str?>';


function OnChangeAnswer(QUESTIONS_FROM)
{
	var arFrom = new Array('A','L','C');

	for (i=0; i<arFrom.length; i++)
	{
		if (arFrom[i] != QUESTIONS_FROM)
			document.forms['form1'].elements['QUESTIONS_AMOUNT_'+arFrom[i]].disabled = true;
		else
			document.forms['form1'].elements['QUESTIONS_AMOUNT_'+arFrom[i]].disabled = false;
	}

	var arFromID = new Array('S','H');

	for (i=0; i<arFromID.length; i++)
	{
		if (document.forms['form1'].elements['QUESTIONS_FROM_ID_'+arFromID[i]])
		{
			if (arFromID[i] != QUESTIONS_FROM)
				document.forms['form1'].elements['QUESTIONS_FROM_ID_'+arFromID[i]].disabled = true;
			else
				document.forms['form1'].elements['QUESTIONS_FROM_ID_'+arFromID[i]].disabled = false;
		}
	}

}

OnChangeAnswer(QUESTIONS_FROM);
</script>

		</td>
	</tr>

	<tr>
		<td><? echo GetMessage("LEARNING_INCLUDE_SELF_TEST")?>:</td>
		<td>
			<input type="checkbox" name="INCLUDE_SELF_TEST" value="Y"<?if($str_INCLUDE_SELF_TEST=="Y")echo " checked"?>>
		</td>
	</tr>


	<tr>
		<td><? echo GetMessage("LEARNING_RANDOM_QUESTIONS")?>:</td>
		<td>
			<input type="checkbox" name="RANDOM_QUESTIONS" value="Y"<?if($str_RANDOM_QUESTIONS=="Y")echo " checked"?>>
		</td>
	</tr>

	<tr>
		<td><? echo GetMessage("LEARNING_RANDOM_ANSWERS")?>:</td>
		<td>
			<input type="checkbox" name="RANDOM_ANSWERS" value="Y"<?if($str_RANDOM_ANSWERS=="Y")echo " checked"?>>
		</td>
	</tr>

	<tr>
		<td><? echo GetMessage("LEARNING_ATTEMPT_LIMIT")?>:</td>
		<td>
			<input type="text" name="ATTEMPT_LIMIT" value="<?echo $str_ATTEMPT_LIMIT?>" size="3"> <? echo GetMessage("LEARNING_ATTEMPT_LIMIT_HINT")?>
		</td>
	</tr>

	<tr>
		<td><? echo GetMessage("LEARNING_MIN_TIME_BETWEEN_ATTEMPTS")?>:</td>
		<td>
			<input type="text" name="MIN_TIME_BETWEEN_ATTEMPTS_D" value="<?echo $str_MIN_TIME_BETWEEN_ATTEMPTS_D?>" size="3"> <? echo GetMessage("LEARNING_MIN_TIME_BETWEEN_ATTEMPTS_D")?> <input type="text" name="MIN_TIME_BETWEEN_ATTEMPTS_H" value="<?echo $str_MIN_TIME_BETWEEN_ATTEMPTS_H?>" size="3"> <? echo GetMessage("LEARNING_MIN_TIME_BETWEEN_ATTEMPTS_H")?> <input type="text" name="MIN_TIME_BETWEEN_ATTEMPTS_M" value="<?echo $str_MIN_TIME_BETWEEN_ATTEMPTS_M?>" size="3"> <? echo GetMessage("LEARNING_MIN_TIME_BETWEEN_ATTEMPTS_M")?>
		</td>
	</tr>

	<tr>
		<td><? echo GetMessage("LEARNING_TIME_LIMIT")?>:</td>
		<td>
			<input type="text" name="TIME_LIMIT" value="<?echo $str_TIME_LIMIT?>" size="3"> <? echo GetMessage("LEARNING_TIME_LIMIT_HINT")?>
		</td>
	</tr>

	<tr>
		<td><? echo GetMessage("LEARNING_APPROVED")?>:</td>
		<td>
			<input type="checkbox" name="APPROVED" value="Y"<?if($str_APPROVED=="Y")echo " checked"?> onclick="OnChangeApproved(this.checked);">
		</td>
	</tr>

	<tr>
		<td><? echo GetMessage("LEARNING_COMPLETED_SCORE")?>:</td>
		<td>
			<input type="text" name="COMPLETED_SCORE" size="3" maxlength="3" value="<?echo $str_COMPLETED_SCORE?>">
			<? echo GetMessage("LEARNING_COMPLETED_SCORE2")?>
		</td>
	</tr>
<script type="text/javascript">


function OnChangeApproved(val)
{
	document.forms['form1'].elements['COMPLETED_SCORE'].disabled = !val;
}
OnChangeApproved(<?=($str_APPROVED=="Y"?"true":"false")?>);
</script>


	<tr>
		<td valign="top"><? echo GetMessage("LEARNING_PASSAGE_TYPE")?>:</td>
		<td>
			<table>
			<tr>
				<td valign="top"><input type="radio" name="PASSAGE_TYPE" value="0"<?if($str_PASSAGE_TYPE=="0")echo " checked"?> onClick="toggleNextQ();"></td>
				<td><?=GetMessage("LEARNING_PASSAGE_TYPE_0")?></td>
			</tr>
			<tr>
				<td valign="top"><input type="radio" name="PASSAGE_TYPE" value="1"<?if($str_PASSAGE_TYPE=="1")echo " checked"?> onClick="toggleNextQ();"></td>
				<td><?=GetMessage("LEARNING_PASSAGE_TYPE_1")?></td>
			</tr>
			<tr>
				<td valign="top"><input type="radio" name="PASSAGE_TYPE" value="2"<?if($str_PASSAGE_TYPE=="2")echo " checked"?> onClick="toggleNextQ();"></td>
				<td><?=GetMessage("LEARNING_PASSAGE_TYPE_2")?></td>
			</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td><? echo GetMessage("LEARNING_PREVIOUS_TEST_ID")?>:</td>
		<td valign="top">
		<?$t = CTest::GetList(array(), array("ACTIVE" => "Y"));?>
		<select name="PREVIOUS_TEST_ID" onchange="OnChangePreviousTest();">
			<option value="0">&lt;<? echo GetMessage("LEARNING_TEST_TEST")?>&gt;</option>
		<?
			while($t->ExtractFields("t_")):
				if (!isset($ID) || $ID != $t_ID):
		?>
				<option value="<?echo $t_ID?>"<?if($str_PREVIOUS_TEST_ID == $t_ID)echo " selected"?>><?echo $t_NAME?></option>
		<?
				endif;
			endwhile;
		?>
		</select>
		<? echo GetMessage("LEARNING_PREVIOUS_TEST_SCORE")?>
		<input type="text" name="PREVIOUS_TEST_SCORE" size="3" maxlength="3" value="<?echo $str_PREVIOUS_TEST_SCORE?>">
		<? echo GetMessage("LEARNING_PREVIOUS_TEST_SCORE2")?>
		</td>
	</tr>

<script type="text/javascript">


function OnChangePreviousTest()
{
	document.forms['form1'].elements['PREVIOUS_TEST_SCORE'].disabled = !document.forms['form1'].elements['PREVIOUS_TEST_ID'].selectedIndex;
}
OnChangePreviousTest();

function toggleIndication(visible, num)
{
	if (visible)
		document.getElementById("indication_" + num).style.display = "block";
	else
		document.getElementById("indication_" + num).style.display = "none";
}
</script>

	<tr>
		<td><? echo GetMessage("LEARNING_INCORRECT_CONTROL")?>:</td>
		<td>
			<input type="checkbox" name="INCORRECT_CONTROL" value="Y"<?if($str_INCORRECT_CONTROL=="Y")echo " checked"?>>
		</td>
	</tr>

	<tr>
		<td valign="top"><? echo GetMessage("LEARNING_CURRENT_INDICATION")?>:</td>
		<td>
			<input type="checkbox" name="CURRENT_INDICATION" value="Y"<?if($str_CURRENT_INDICATION == "Y")echo " checked"?> onClick="toggleIndication(this.checked, 1);" id="indication_cb_1">
			<div id="indication_1">
				<label><input type="checkbox" name="CURRENT_INDICATION_PERCENT" value="Y"<?if($str_CURRENT_INDICATION_PERCENT == "Y")echo " checked"?>><? echo GetMessage("LEARNING_CURRENT_INDICATION_PERCENT")?></label><br />
				<label><input type="checkbox" name="CURRENT_INDICATION_MARK" value="Y"<?if($str_CURRENT_INDICATION_MARK =="Y")echo " checked"?>><? echo GetMessage("LEARNING_CURRENT_INDICATION_MARK")?></label>
			</div>
		</td>
	</tr>

	<tr>
		<td valign="top"><? echo GetMessage("LEARNING_FINAL_INDICATION")?>:</td>
		<td>
			<input type="checkbox" name="FINAL_INDICATION" value="Y"<?if($str_FINAL_INDICATION == "Y")echo " checked"?> onClick="toggleIndication(this.checked, 2);" id="indication_cb_2">
			<div id="indication_2">
				<label><input type="checkbox" name="FINAL_INDICATION_CORRECT_COUNT" value="Y"<?if($str_FINAL_INDICATION_CORRECT_COUNT == "Y")echo " checked"?>><? echo GetMessage("LEARNING_FINAL_INDICATION_CORRECT_COUNT")?></label><br />
				<label><input type="checkbox" name="FINAL_INDICATION_SCORE" value="Y"<?if($str_FINAL_INDICATION_SCORE == "Y")echo " checked"?>><? echo GetMessage("LEARNING_FINAL_INDICATION_SCORE")?></label><br />
				<label><input type="checkbox" name="FINAL_INDICATION_MARK" value="Y"<?if($str_FINAL_INDICATION_MARK == "Y")echo " checked"?>><? echo GetMessage("LEARNING_FINAL_INDICATION_MARK")?></label><br />
				<label><input type="checkbox" name="FINAL_INDICATION_MESSAGE" value="Y"<?if($str_FINAL_INDICATION_MESSAGE == "Y")echo " checked"?>><? echo GetMessage("LEARNING_FINAL_INDICATION_MESSAGE")?></label>
			</div>
		</td>
	</tr>
<script type="text/javascript">
toggleIndication(document.getElementById("indication_cb_1").checked, 1);
toggleIndication(document.getElementById("indication_cb_2").checked, 2);

function toggleNextQ()
{
	if (document.getElementById("show_errors").checked && document.getElementsByName("PASSAGE_TYPE")[2].checked)
	{
		document.getElementById("next_q_on_error").style.display = "";
	}
	else
	{
		document.getElementById("next_q_on_error").style.display = "none";
	}
}
</script>

	<tr>
		<td><? echo GetMessage("LEARNING_SHOW_ERRORS")?>:</td>
		<td>
			<input type="checkbox" name="SHOW_ERRORS" value="Y"<?if($str_SHOW_ERRORS=="Y")echo " checked"?> onClick="toggleNextQ();" id="show_errors">
		</td>
	</tr>

	<tr id="next_q_on_error">
		<td valign="top"><? echo GetMessage("LEARNING_ON_ERROR")?>:</td>
		<td>
			<input type="radio" name="NEXT_QUESTION_ON_ERROR" value="Y"<?if($str_NEXT_QUESTION_ON_ERROR!="N")echo " checked"?>>&nbsp;<? echo GetMessage("LEARNING_NEXT_QUESTION_ON_ERROR")?><br />
			<input type="radio" name="NEXT_QUESTION_ON_ERROR" value="N"<?if($str_NEXT_QUESTION_ON_ERROR=="N")echo " checked"?>>&nbsp;<? echo GetMessage("LEARNING_PREV_QUESTION_ON_ERROR")?>
		</td>
	</tr>
<script type="text/javascript">toggleNextQ();</script>

<?$tabControl->BeginNextTab();?>

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
	<tr>
		<td colspan="2" align="center">
			<table cellpadding="0" cellspacing="0" width="100%" class="internal">
				<tr class="heading">
					<td align="center" width="10%">ID</td>
					<td align="center" width="15%"><?echo GetMessage("LEARNING_TEST_MARK_SCORE")?></td>
					<td align="center" width="20%"><?echo GetMessage("LEARNING_TEST_MARK")?></td>
					<td align="center" width="50%"><?echo GetMessage("LEARNING_TEST_MARK_MESSAGE")?></td>
					<td align="center" width="10%"><?echo GetMessage("LEARNING_TEST_MARK_DELETE")?></td>
				</tr>
				<?php
					$marks = CLTestMark::GetList(Array("score" => "DESC"),Array("TEST_ID" => $ID));
					while($marks->ExtractFields("s_")):
				?>
					<tr>
						<td align="center"><?php echo $s_ID?></td>
						<td align="center">
							<?echo GetMessage("LEARNING_TEST_SCORE_TILL")?>&nbsp;<input type="text" size="4" name="SCORE_<?php echo $s_ID?>" value="<?php echo isset(${"SCORE_".$s_ID}) ? ${"SCORE_".$s_ID} : $s_SCORE?>">&nbsp;%
						</td>
						<td align="center">
							<input type="text" size="20"  name="MARK_<?php echo $s_ID?>" value="<?php echo isset(${"MARK_".$s_ID}) ? ${"MARK_".$s_ID} : $s_MARK?>">
						</td>
						<td align="center">
							<input type="text" size="60"  name="DESCRIPTION_<?php echo $s_ID?>" value="<?php echo isset(${"DESCRIPTION_".$s_ID}) ? ${"DESCRIPTION_".$s_ID} : $s_DESCRIPTION?>">
						</td>
						<td align="center"><input type="checkbox" name="MARK_<?php echo $s_ID?>_DEL" value="Y"></td>
					</tr>
				<?php endwhile?>
				<?php for ($i = 1; $i < 6; $i++):?>
					<tr>
						<td align="center">&nbsp;</td>
						<td align="center">
							<?echo GetMessage("LEARNING_TEST_SCORE_TILL")?>&nbsp;<input type="text" size="4" name="N_SCORE_<?php echo $i?>" value="<?php echo isset(${"N_SCORE_".$i}) ? ${"N_SCORE_".$i} : ""?>">&nbsp;%
						</td>
						<td align="center">
							<input type="text" size="20"  name="N_MARK_<?php echo $i?>" value="<?php echo isset(${"N_MARK_".$i}) ? ${"N_MARK_".$i} : ""?>">
						</td>
						<td align="center">
							<input type="text" size="60"  name="N_DESCRIPTION_<?php echo $i?>" value="<?php echo isset(${"N_DESCRIPTION_".$i}) ? ${"N_DESCRIPTION_".$i} : ""?>">
						</td>
						<td>&nbsp;</td>
					</tr>
				<?php endfor?>
			</table>
		</td>
	</tr>

<?$tabControl->Buttons(Array("back_url" =>"learn_test_admin.php?lang=". LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("find_", false)));$tabControl->End();?>
</form>



<?$tabControl->ShowWarnings("form1", $message);?>

<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>