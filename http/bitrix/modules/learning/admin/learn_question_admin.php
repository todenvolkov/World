<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$COURSE_ID = intval($COURSE_ID);
$LESSON_ID = intval($LESSON_ID);
$filter_lesson_id = intval($filter_lesson_id);

if (isset($from) && strlen($from) > 0)
	$str_from = "&from=".htmlspecialchars($from);
else
	$str_from = "";
//Course
$course = CCourse::GetByID($COURSE_ID);
if($arCourse = $course->Fetch())
	$bBadCourse=(CCourse::GetPermission($COURSE_ID)<"W");
else
	$bBadCourse = true;


$arLesson = Array("CHAPTER_ID" => 0);
if ($filter_lesson_id > 0 || $LESSON_ID > 0 )
{
	$lesson = CLesson::GetList(Array(), Array("COURSE_ID" => $COURSE_ID, "ID" => $filter_lesson_id > 0 ? $filter_lesson_id : $LESSON_ID));
	$arLesson = $lesson->Fetch();
}

//Lesson
/*
if (!$bBadCourse)
{
	$lesson = CLesson::GetList(Array(), Array("COURSE_ID" => $COURSE_ID, "ID" => $LESSON_ID));
	if ($arLesson = $lesson->Fetch())
		$bBadLesson = false;
	else
		$bBadLesson = true;
}
else
{
	$bBadLesson = true;
}
*/


//if($bBadLesson)
if($bBadCourse)
{
	$APPLICATION->SetTitle(GetMessage('LEARNING_QUESTION'));
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

	if ($bBadCourse)
		CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_COURSE"));
	else
		CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_LESSON"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$sTableID = "t_question_admin";
$oSort = new CAdminSorting($sTableID, "timestamp_x", "desc");// sort initializing
$lAdmin = new CAdminList($sTableID, $oSort);// list initializing


$arFilterFields = Array(
	"filter_name",
	"filter_lesson_id",
	"filter_self",
	"filter_active",
	"filter_required",
);

$lAdmin->InitFilter($arFilterFields);// filter initializing


$arFilter = Array(
	"COURSE_ID" => $COURSE_ID,
	"LESSON_ID" => $filter_lesson_id,
	"SELF" => $filter_self,
	"ACTIVE" => $filter_active,
	"CORRECT_REQUIRED" => $filter_required,
	"?NAME" => $filter_name,
);

if ($filter_lesson_id <= 0)
	unset($arFilter["LESSON_ID"]);

if ($lAdmin->EditAction()) // save from the list
{
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		$ob = new CLQuestion;
		if(!$ob->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
			{
				$e = $APPLICATION->GetException();
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
				$DB->Rollback();
			}
		}
		$DB->Commit();
	}
}

// group and single actions processing
if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CLQuestion::GetList(Array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
		$ID = intval($ID);

		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			$cl = new CLQuestion;
			if(!$cl->Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("LEARNING_DELETE_ERROR"), $ID);
			}
			$DB->Commit();
			break;
		case "self":
		case "deself":
			$cl = new CLQuestion;
			$arFields = Array("SELF"=>($_REQUEST['action']=="self"?"Y":"N"));
			if(!$cl->Update($ID, $arFields))
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			break;

		case "activate":
		case "deactivate":
			$cl = new CLQuestion;
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$cl->Update($ID, $arFields))
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			break;

		case "required":
		case "derequired":
			$cl = new CLQuestion;
			$arFields = Array("CORRECT_REQUIRED"=>($_REQUEST['action']=="required"?"Y":"N"));
			if(!$cl->Update($ID, $arFields))
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			break;
		}
	}
}

// fetch data
$rsData = CLQuestion::GetList(Array($by=>$order),$arFilter, true);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_QUESTION")));

// list header
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage('LEARNING_COURSE_ADM_ACT'),"sort"=>"active", "default"=>true),
	array("id"=>"TIMESTAMP_X","content"=>GetMessage('LEARNING_COURSE_ADM_DATECH'), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('LEARNING_NAME'),	"sort"=>"name", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('LEARNING_COURSE_ADM_SORT'),"sort"=>"sort", "default"=>true),
	array("id"=>"SELF", "content"=>GetMessage('LEARNING_QUESTION_ADM_SELF'),"sort"=>"self", "default"=>true),
	array("id"=>"CORRECT_REQUIRED", "content"=>GetMessage('LEARNING_QUESTION_ADM_REQUIRED'),"sort"=>"correct_required", "default"=>true),
	array("id"=>"QUESTION_TYPE", "content"=>GetMessage('LEARNING_QUESTION_ADM_TYPE'),"sort"=>"type", "default"=>true),
	array("id"=>"POINT", "content"=>GetMessage('LEARNING_QUESTION_ADM_POINT'),"sort"=>"point", "default"=>true),
	array("id"=>"ANSWERS_STATS", "content"=>GetMessage('LEARNING_QUESTION_ADM_STATS'), "default"=>true),
));

// building list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);
	$arStat = CLAnswer::GetStats($f_ID);

	$row->AddCheckField("SELF");
	$row->AddCheckField("ACTIVE");
	$row->AddCheckField("CORRECT_REQUIRED");
	$row->AddInputField("NAME",Array("size"=>"35"));
	$row->AddInputField("SORT", Array("size"=>"3"));
	$row->AddInputField("POINT", Array("size"=>"3"));

	$row->AddViewField("QUESTION_TYPE", '<div title="'.GetMessage("LEARNING_QUESTION_TYPE_".$f_QUESTION_TYPE).'" class="learning-question-'.strtolower($f_QUESTION_TYPE).'"></div>');

	$row->AddViewField("ANSWERS_STATS", '<a href="learn_test_result_admin.php?lang='.LANG.'&set_filter=Y&filter_correct=Y&filter_answered=Y">'.$arStat["CORRECT_CNT"].'</a> / <a href="learn_test_result_admin.php?lang='.LANG.'">'.$arStat["ALL_CNT"].'</a>');

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.($arLesson["CHAPTER_ID"] > 0 ?"&CHAPTER_ID=".$arLesson["CHAPTER_ID"]:"")."&LESSON_ID=".$f_LESSON_ID."&ID=".$f_ID.GetFilterParams("filter_", false).$str_from)


	);

	/*
	$arActions[] = array(
		"ICON"=>"copy",
		"TEXT"=>GetMessage("MAIN_ADMIN_ADD_COPY"),
		"ACTION"=>$lAdmin->ActionRedirect("learn_course_edit.php?COPY_ID=".$f_ID));
	*/

	$arActions[] = array("SEPARATOR"=>true);

	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION"=>"if(confirm('".GetMessage('LEARNING_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete",'LESSON_ID='.$LESSON_ID.'&COURSE_ID='.$COURSE_ID));

	$row->AddActions($arActions);
}

// list footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

// group actions buttons
$lAdmin->AddGroupActionTable(Array(
	"self"=>GetMessage("LEARNING_ACTION_SELF"),
	"deself"=>GetMessage("LEARNING_ACTION_DESELF"),
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	"required"=>GetMessage("MAIN_ADMIN_LIST_REQUIRED"),
	"derequired"=>GetMessage("MAIN_ADMIN_LIST_NOT_REQUIRED"),
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);

$arContextPopup = Array(
	Array(
		"TEXT" => GetMessage('LEARNING_SINGLE_CHOICE'),
		//"ICON" => "learning-question-s",
		"ACTION" =>$lAdmin->ActionRedirect("learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.($arLesson["CHAPTER_ID"] > 0 ?"&CHAPTER_ID=".$arLesson["CHAPTER_ID"]:"")."&LESSON_ID=".$LESSON_ID."&QUESTION_TYPE=S".GetFilterParams("filter_", false).$str_from)
		//"window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."QUESTION_TYPE=S".GetFilterParams("filter_", false)."'",

	),
	Array(
		"TEXT" => GetMessage('LEARNING_MULTIPLE_CHOICE'),
		//"ICON" => "learning-question-m",
		"ACTION" =>
		$lAdmin->ActionRedirect("learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.($arLesson["CHAPTER_ID"] > 0 ?"&CHAPTER_ID=".$arLesson["CHAPTER_ID"]:"")."&LESSON_ID=".$LESSON_ID."&QUESTION_TYPE=M".GetFilterParams("filter_", false).$str_from)

		//"window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."QUESTION_TYPE=M".GetFilterParams("filter_", false)."'",
	),
	Array(
		"TEXT" => GetMessage('LEARNING_SORTING'),
		//"ICON" => "learning-question-s",
		"ACTION" =>$lAdmin->ActionRedirect("learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.($arLesson["CHAPTER_ID"] > 0 ?"&CHAPTER_ID=".$arLesson["CHAPTER_ID"]:"")."&LESSON_ID=".$LESSON_ID."&QUESTION_TYPE=R".GetFilterParams("filter_", false).$str_from)
		//"window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."QUESTION_TYPE=S".GetFilterParams("filter_", false)."'",

	),
	Array(
		"TEXT" => GetMessage('LEARNING_TEXT_ANSWER'),
		//"ICON" => "learning-question-m",
		"ACTION" =>
		$lAdmin->ActionRedirect("learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.($arLesson["CHAPTER_ID"] > 0 ?"&CHAPTER_ID=".$arLesson["CHAPTER_ID"]:"")."&LESSON_ID=".$LESSON_ID."&QUESTION_TYPE=T".GetFilterParams("filter_", false).$str_from)

		//"window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."QUESTION_TYPE=M".GetFilterParams("filter_", false)."'",
	),
	);


$aContext = array(
	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("LEARNING_ADD"),
		"TITLE"=>GetMessage("LEARNING_ADD_ALT"),
		"MENU" => $arContextPopup
	),
);


$lAdmin->AddAdminContextMenu($aContext);



//$adminChain->AddItem(array("TEXT"=>htmlspecialcharsex($arCourse["NAME"]), "LINK"=>"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id=0"));

/*
if (!empty($arLesson))
{
	$nav = CChapter::GetNavChain($COURSE_ID, $arLesson["CHAPTER_ID"]);
	while($nav->ExtractFields("nav_"))
	{
		$adminChain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id=".$nav_ID));
	}

}
*/

//$adminChain->AddItem(array("TEXT"=>"<b>".htmlspecialcharsex($arLesson["NAME"])." (".ToLower(GetMessage("LEARNING_QUESTION")).")</b>", "LINK"=>""));


// list mode check (if AJAX then terminate the script)
$lAdmin->CheckListMode();

$APPLICATION->SetTitle($arCourse["NAME"].": ".GetMessage('LEARNING_QUESTION'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$filter = new CAdminFilter(
	"filter_id",
	array(
		GetMessage("LEARNING_LESSON"),
		GetMessage("LEARNING_F_SELF"),
		GetMessage("LEARNING_F_ACTIVE2"),
		GetMessage("LEARNING_F_CORRECT_REQUIRED"),
	)
);



?>
<form method="GET" action="<?echo $APPLICATION->GetCurPage()?>" name="find_form" onsubmit="return this.set_filter.onclick();">
<?$filter->Begin();?>

	<tr>
		<td><b><?echo GetMessage("LEARNING_NAME")?>:</b></td>
		<td>
			<input type="text" name="filter_name" size="50" value="<?echo htmlspecialcharsex($filter_name)?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>


 	<tr>
		<td><?echo GetMessage("LEARNING_LESSON")?>:</td>
		<td>
			<select name="filter_lesson_id" style="width:330px;">

				<option value=""><?echo GetMessage("LEARNING_ALL")?></option>
				<?
				$bsections = CLesson::GetList(Array(),Array("COURSE_ID" => $COURSE_ID));
				while($bsections->ExtractFields("s_")):
					?><option value="<?echo $s_ID?>"<?if($s_ID==$filter_lesson_id)echo " selected"?>><?echo $s_NAME?></option><?
				endwhile;
				?>

			</select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_F_SELF")?>:</td>
		<td>
			<select name="filter_self">
				<option value=""><?=htmlspecialcharsex(GetMessage('LEARNING_ALL2'))?></option>
				<option value="Y"<?if($filter_self=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_YES"))?></option>
				<option value="N"<?if($filter_self=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_NO"))?></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_F_ACTIVE")?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?=htmlspecialcharsex(GetMessage('LEARNING_ALL'))?></option>
				<option value="Y"<?if($filter_active=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_YES"))?></option>
				<option value="N"<?if($filter_active=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_NO"))?></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_F_CORRECT_REQUIRED")?>:</td>
		<td>
			<select name="filter_required">
				<option value=""><?=htmlspecialcharsex(GetMessage('LEARNING_ALL'))?></option>
				<option value="Y"<?if($filter_required=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_YES"))?></option>
				<option value="N"<?if($filter_required=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("LEARNING_NO"))?></option>
			</select>
		</td>
	</tr>

<?
$filter->Buttons(array("table_id"=>$sTableID, "url"=>"learn_question_admin.php?COURSE_ID=".$COURSE_ID/*."&LESSON_ID=".$LESSON_ID*/, "form"=>"find_form"));
$filter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
