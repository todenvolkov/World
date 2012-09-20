<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$module_id = "learning";
$LEARNING_RIGHT = $APPLICATION->GetGroupRight($module_id);

$sTableID = "t_course_admin";
$oSort = new CAdminSorting($sTableID, "sort", "asc");// sort initialization
$lAdmin = new CAdminList($sTableID, $oSort);// list initialization

$filter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		GetMessage("LEARNING_SITE_ID2"),
		GetMessage("LEARNING_F_ACTIVE2"),
	)
);

$arFilterFields = Array(
	"filter_name",
	"filter_id",
	"filter_site_id",
	"filter_active",
);

$lAdmin->InitFilter($arFilterFields);// filter initialization


$arFilter = Array(
	"ID" => $filter_id,
	"ACTIVE" => $filter_active,
	"SITE_ID" => $filter_site_id,
	"?NAME" => $filter_name,
	"MIN_PERMISSION"=>"W"
);

if($lAdmin->EditAction()) //save from the list
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		$ID = IntVal($ID);

		$perm = CCourse::GetPermission($ID);
		if($perm < "X")
			continue;

		$ob = new CCourse;
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
if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CCourse::GetList(Array($by => $order), $arFilter);
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
			if($LEARNING_RIGHT < "W")
				break;

			@set_time_limit(0);
			$DB->StartTransaction();
			if(!CCourse::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("LEARNING_DELETE_ERROR"), $ID);
			}
			$DB->Commit();
			break;
		case "activate":
		case "deactivate":
			$ob = new CCourse;
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$ob->Update($ID, $arFields))
			{
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			}
			break;
		}
	}
}

// fetch data
$rsData = CCourse::GetList(Array($by=>$order),$arFilter, true);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_COURSES")));


// list header
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"TIMESTAMP_X","content"=>GetMessage('LEARNING_COURSE_ADM_DATECH'), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('LEARNING_NAME'),	"sort"=>"name", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('LEARNING_COURSE_ADM_SORT'),"sort"=>"sort", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage('LEARNING_COURSE_ADM_ACT'),"sort"=>"active", "default"=>true),
	array("id"=>"SITE_ID", "content"=>GetMessage('LEARNING_SITE_ID2'), "default"=>true),
	array("id"=>"CHAPTERS", "content"=>GetMessage('LEARNING_CHAPTERS'), "default"=>true),
	array("id"=>"LESSONS", "content"=>GetMessage('LEARNING_LESSONS'), "default"=>true),
	array("id"=>"TESTS", "content"=>GetMessage('LEARNING_TESTS'), "default"=>true),
	array("id"=>"CODE", "content"=>GetMessage('LEARNING_CODE'),	"sort"=>"code", "default"=>false),
));


// building list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$f_LID = '';
	$db_LID = CCourse::GetSite($f_ID);
	while($ar_LID = $db_LID->Fetch())
		$f_LID .= ($f_LID!=""?" / ":"").htmlspecialchars($ar_LID["LID"]);

	$row->AddViewField("SITE_ID", $f_LID);

	$row->AddViewField("LESSONS", '<a title="'.GetMessage("LEARNING_LESSON_TITLE").'" href="learn_lesson_admin.php?COURSE_ID='.$f_ID.'&lang='.LANG.'&filter_chapter_id=-1'.GetFilterParams("filter_").'">'.intval($f_ELEMENT_CNT).'</a>&nbsp;[<a title="'.GetMessage("LEARNING_LESSON_ADD").'" href="learn_lesson_edit.php?COURSE_ID='.$f_ID.'&lang='.LANG.GetFilterParams("filter_").'&filter_chapter_id=0&from=learn_admin">+</a>]');
	$row->AddViewField("CHAPTERS", '<a title="'.GetMessage("LEARNING_CHAPTER_TITLE").'" href="learn_chapter_admin.php?COURSE_ID='.$f_ID.'&lang='.LANG."&set_filter=Y&filter_chapter_id=0".'">'.CChapter::GetCount(Array("COURSE_ID"=>$f_ID)).'</a> [<a title="'.GetMessage("LEARNING_CHAPTER_ADD").'" href="learn_chapter_edit.php?COURSE_ID='.$f_ID.'&lang='.LANG.GetFilterParams("filter_").'&from=learn_admin">+</a>]');

	$row->AddViewField("TESTS", '<a title="'.GetMessage("LEARNING_TEST_TITLE").'" href="learn_test_admin.php?lang='.LANG.'&COURSE_ID='.$f_ID.'">'.CTest::GetCount(Array("COURSE_ID"=>$f_ID)).'</a> [<a title="'.GetMessage("LEARNING_TEST_ADD").'" href="learn_test_edit.php?lang='.LANG.'&COURSE_ID='.$f_ID.'&from=learn_admin">+</a>]');

	if ($LEARNING_RIGHT == "W")
	{
		$row->AddInputField("NAME",Array("size"=>"35"));
		$row->AddInputField("SORT", Array("size"=>"3"));
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("CODE");
	}
	else
	{
		$row->AddCheckField("ACTIVE", false);
	}


	$arActions = Array();

/*
	$arActions[] = array(
			"ICON"=>"list",
			"TEXT"=>GetMessage("LEARNING_CHAPTERS")." (".CChapter::GetCount(Array("COURSE_ID"=>$f_ID)).")",
			"ACTION"=>$lAdmin->ActionRedirect("learn_chapter_admin.php?COURSE_ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_"))
		);
*/

	$arActions[] = array(
			"ICON"=>"add",
			"TEXT"=>GetMessage("LEARNING_CHAPTER_ADD"),
			"ACTION"=>$lAdmin->ActionRedirect("learn_chapter_edit.php?COURSE_ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_")."&from=learn_admin")
		);

	//$arActions[] = array("SEPARATOR"=>true);

/*
	$arActions[] = array(
			"ICON"=>"list",
			"TEXT"=>GetMessage("LEARNING_LESSONS")." (".intval($f_ELEMENT_CNT).")",
			"ACTION"=>$lAdmin->ActionRedirect("learn_lesson_admin.php?COURSE_ID=".$f_ID."&lang=".LANG."&filter_chapter_id=-1".GetFilterParams("filter_"))
		);
*/

	$arActions[] = array(
			"ICON"=>"add",
			"TEXT"=>GetMessage("LEARNING_LESSON_ADD"),
			"ACTION"=>$lAdmin->ActionRedirect("learn_lesson_edit.php?COURSE_ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_")."&filter_chapter_id=0&from=learn_admin")
		);

	if ($LEARNING_RIGHT == "W")
	{
		//Actions

		$arActions[] = array("SEPARATOR"=>true);


		$arActions[] = array(
			"ICON"=>"edit",
			"DEFAULT" => "Y",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
			"ACTION"=>$lAdmin->ActionRedirect("learn_course_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_"))
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
			"ACTION"=>"if(confirm('".GetMessage('LEARNING_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

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
if ($LEARNING_RIGHT == "W")
{
	$lAdmin->AddGroupActionTable(Array(
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		));
}

// context menu
$aContext = Array();
if($LEARNING_RIGHT == "W")
{
	$aContext = array(
		array(
			"ICON" => "btn_new",
			"TEXT"=>GetMessage("LEARNING_ADD"),
			"LINK"=>"learn_course_edit.php?lang=".LANG.GetFilterParams("filter_"),
			"TITLE"=>GetMessage("LEARNING_ADD_ALT")
		),
	);
}

$lAdmin->AddAdminContextMenu($aContext);


// list mode check (if AJAX then terminate the script)
$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("LEARNING_ADMIN_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if (defined("LEARNING_ADMIN_ACCESS_DENIED"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"), false);
?>

<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>" onsubmit="return this.set_filter.onclick();">
<?$filter->Begin();?>
	<tr>
		<td><b><?echo GetMessage("LEARNING_NAME")?>:</b></td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialchars($filter_name)?>" size="47">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>


 	<tr>
		<td>ID:</b></td>
		<td><input type="text" name="filter_id" value="<?echo htmlspecialchars($filter_id)?>" size="47"></td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_SITE_ID2");?>:</td>
		<td>
			<select name="filter_site_id">
				<option value=""><?echo GetMessage("LEARNING_ALL")?></option>
			<?
			$l = CLang::GetList($b="sort", $o="asc", Array("VISIBLE"=>"Y"));
			while($l->ExtractFields("l_")):
				?><option value="<?echo $l_LID?>"<?if($filter_site_id==$l_LID)echo " selected"?>><?echo $l_NAME?></option><?
			endwhile;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("LEARNING_F_ACTIVE")?>:</td>
		<td>
			<?
			$arr = array("reference"=>array(GetMessage("LEARNING_YES"), GetMessage("LEARNING_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("filter_active", $arr, htmlspecialcharsex($filter_active), GetMessage('LEARNING_ALL'));
			?>
		</td>
	</tr>
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?$lAdmin->DisplayList();?>


<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
