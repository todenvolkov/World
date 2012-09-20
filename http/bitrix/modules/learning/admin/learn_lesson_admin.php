<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$COURSE_ID = intval($COURSE_ID);
$course = CCourse::GetByID($COURSE_ID);

if($arCourse = $course->Fetch())
	$bBadCourse=(CCourse::GetPermission($COURSE_ID)<"W");
else
	$bBadCourse = true;

if($bBadCourse)
{
	$APPLICATION->SetTitle(GetMessage('LEARNING_LESSONS'));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aContext = array(
		array(
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

$sTableID = "t_lesson_admin";
$oSort = new CAdminSorting($sTableID, "sort", "asc");// sort initialization
$lAdmin = new CAdminList($sTableID, $oSort);// list initialization



$arFilterFields = Array(
	"filter_name",
	"filter_id",
	"filter_created_user_id",
	"filter_chapter_id",
	"filter_active",
);

$lAdmin->InitFilter($arFilterFields);// filter initialization

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		"ID",
		GetMessage("LEARNING_COURSE_ADM_CREATED"),
		GetMessage("LEARNING_F_CHAPTER"),
		GetMessage("LEARNING_F_ACTIVE2"),
	)
);

$arFilter = Array(
	"COURSE_ID" => $COURSE_ID,
	"CHAPTER_ID" => $filter_chapter_id,
	"ACTIVE" => $filter_active,
	"ID" => $filter_id,
	"?NAME" => $filter_name,
);

if(intval($filter_chapter_id)<0 || strlen($filter_chapter_id)<=0)
	unset($arFilter["CHAPTER_ID"]);

if (!empty($filter_created_by) && strlen($filter_created_by)>0)
	$arFilter["CREATED_BY"] = $filter_created_by;

if (intval($filter_created_user_id) > 0)
	$arFilter["CREATED_BY"] = $filter_created_user_id;


if ($lAdmin->EditAction()) // save from the list
{
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		$ob = new CLesson;
		if(!$ob->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
			{
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
		$rsData = CLesson::GetList(Array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
		$ID = IntVal($ID);

		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			$cl = new CLesson;
			if(!$cl->Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("LEARNING_DELETE_ERROR"), $ID);
			}
			$DB->Commit();
			break;
		case "activate":
		case "deactivate":
			$cl = new CLesson;
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$cl->Update($ID, $arFields))
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			break;
		}
	}

	if(isset($return_url) && strlen($return_url)>0 && check_bitrix_sessid())
		LocalRedirect($return_url);
}

// fetch data
$rsData = CLesson::GetList(Array($by=>$order),$arFilter, true);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_LESSONS")));

// list header
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"TIMESTAMP_X","content"=>GetMessage('LEARNING_COURSE_ADM_DATECH'), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('LEARNING_NAME'),	"sort"=>"name", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('LEARNING_COURSE_ADM_SORT'),"sort"=>"sort", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage('LEARNING_COURSE_ADM_ACT'),"sort"=>"active", "default"=>true),
	array("id"=>"CHAPTER_NAME", "content"=>GetMessage('LEARNING_F_CHAPTER'),"sort"=>"chapter_name","default"=>true),
	array("id"=>"QUESTIONS", "content"=>GetMessage('LEARNING_QUESTION'),"default"=>true),
));

// building list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddCheckField("ACTIVE");
	$row->AddInputField("NAME",Array("size"=>"35"));
	$row->AddInputField("SORT", Array("size"=>"3"));

	$row->AddViewField("QUESTIONS", '<a title="'.GetMessage("LEARNING_QUESTION_TITLE").'" href="learn_question_admin.php?lang='.LANG.'&COURSE_ID='.$COURSE_ID.'&CHAPTER_ID='.$f_CHAPTER_ID.'&filter_lesson_id='.$arRes['ID'].GetFilterParams("filter_").'">'.CLQuestion::GetCount(Array("LESSON_ID" => $arRes['ID'])).'</a> [<a href="learn_question_edit.php?lang='.LANG.'&COURSE_ID='.$COURSE_ID.'&CHAPTER_ID='.$f_CHAPTER_ID.'&LESSON_ID='.$f_ID.GetFilterParams("filter_").'&from=learn_admin" title="'.GetMessage('LEARNING_ADD_QUESTION').'">+</a>]');

	if ($f_CHAPTER_NAME == "")
		$row->AddViewField("CHAPTER_NAME","<i>-".GetMessage("LEARNING_CONTENT")."-</i>");

	$arActions = Array();

/*
	$arActions[] = array(
			"ICON"=>"list",
			"TEXT"=>GetMessage("LEARNING_QUESTION")." (".CLQuestion::GetCount(Array("LESSON_ID" => $arRes['ID'])).")",
			"ACTION"=>$lAdmin->ActionRedirect("learn_question_admin.php?LESSON_ID=".$arRes['ID']."&lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_"))
		);
*/

	$arActions[] = Array(
		"ICON" => "add",
		"TEXT" => GetMessage('LEARNING_ADD_QUESTION'),
		"ACTION" => $lAdmin->ActionRedirect("learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$f_CHAPTER_ID."&LESSON_ID=".$f_ID.GetFilterParams("filter_",false)."&from=learn_admin"),
	);


	$arActions[] = array("SEPARATOR"=>true);


	$arActions[] = array(
		"ICON"=>"edit",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"DEFAULT" => "Y",
		"ACTION"=>$lAdmin->ActionRedirect("learn_lesson_edit.php?lang=".LANG."&ID=".$f_ID."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".intval($f_CHAPTER_ID).GetFilterParams("filter_", false))
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
		"ACTION"=>"if(confirm('".GetMessage('LEARNING_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete",'COURSE_ID='.$COURSE_ID));

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
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);


// context menu
$aContext = array(
	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("LEARNING_ADD"),
		"LINK"=>"learn_lesson_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".IntVal($filter_chapter_id).GetFilterParams("filter_", false),
		"TITLE"=>GetMessage("LEARNING_ADD_ALT")
	),
);



$lAdmin->AddAdminContextMenu($aContext);


//$adminChain->AddItem(array("TEXT"=>GetMessage("LEARNING_COURSES"), "LINK"=>"learn_course_admin.php?lang=".LANG));

//$adminChain->AddItem(array("TEXT"=>htmlspecialcharsex($arCourse["NAME"]), "LINK"=>"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id=-1"));



$chain = $lAdmin->CreateChain();
/*
if(intval($filter_chapter_id)>0)
{
	$nav = CChapter::GetNavChain($COURSE_ID, $filter_chapter_id);
	while($nav->ExtractFields("nav_"))
	{
		if ($filter_chapter_id==$nav_ID)
		{
			$chain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>""));
		}
		else
		{
			$chain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id=".$nav_ID));
		}
	}
}*/

if($filter_chapter_id=="")
{
	$chain->AddItem(array("TEXT"=>"<b>".GetMessage("LEARNING_ALL_LESSONS")."</b>", "LINK"=>""));
}
elseif($filter_chapter_id==0)
{
	$chain->AddItem(array("TEXT"=>"<b>".GetMessage("LEARNING_CONTENT")."</b>", "LINK"=>""));
}


$lAdmin->ShowChain($chain);


// list mode check (if AJAX then terminate the script)
$lAdmin->CheckListMode();

$APPLICATION->SetTitle($arCourse["NAME"].": ".GetMessage('LEARNING_LESSONS'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form method="GET" action="<?echo $APPLICATION->GetCurPage()?>" name="form1" onsubmit="return this.set_filter.onclick();">
<?$filter->Begin();?>

	<tr>
		<td><b><?echo GetMessage("LEARNING_NAME")?>:</b></td>
		<td align="left">
			<input type="text" name="filter_name" size="50" value="<?echo htmlspecialcharsex($filter_name)?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>

	<tr>
		<td>ID:</td>
		<td>
			<input type="text" name="filter_id" size="10" value="<?echo htmlspecialcharsex($filter_id)?>">
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_COURSE_ADM_CREATED2")?>:</td>
		<td align="left"><input type="text" name="filter_created_user_id" value="<?echo htmlspecialcharsex($filter_created_user_id)?>" size="3">&nbsp;<?
		$gr_res = CCourse::GetGroupPermissions($COURSE_ID);
		$res = Array(1);
		foreach($gr_res as $gr=>$perm)
			if($perm>"R")
				$res[] = $gr;
		$res = CUser::GetList($byx="NAME", $orderx="ASC", Array("GROUP_MULTI"=>$res));
		?><select name="filter_created_by">
		<option value=""><?echo GetMessage("LEARNING_ALL")?></option><?
		while($arr = $res->Fetch())
			echo "<option value='".$arr["ID"]."'".($filter_created_by==$arr["ID"]?" selected":"").">(".htmlspecialcharsex($arr["LOGIN"].") ".$arr["NAME"]." ".$arr["LAST_NAME"])."</option>";
		?></select>
		</td>
	</tr>


	<tr>
		<td><?echo GetMessage("LEARNING_F_CHAPTER")?>:</td>
		<td>
			<select name="filter_chapter_id">
				<option value=""><?echo GetMessage("LEARNING_ALL2")?></option>
				<option value="0"<?if($filter_chapter_id=="0")echo" selected"?>><?echo GetMessage("LEARNING_CONTENT")?></option>
				<?
				$bsections = CChapter::GetTreeList($COURSE_ID);
				while($bsections->ExtractFields("s_")):
				?>
				<option value="<?echo $s_ID?>"<?if($s_ID==$filter_chapter_id)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $s_DEPTH_LEVEL)?><?echo $s_NAME?></option>
				<?endwhile;?>

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


<?
$filter->Buttons(array("table_id"=>$sTableID, "url"=>"learn_lesson_admin.php?lang".LANG."&LESSON_ID=".$LESSON_ID."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_"), "form"=>"form1"));
$filter->End();
?>
</form>


<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>