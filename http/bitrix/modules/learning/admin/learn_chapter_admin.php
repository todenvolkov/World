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
	$APPLICATION->SetTitle(GetMessage('LEARNING_CHAPTERS'));
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



$sTableID = "t_chapter_admin";
$oSort = new CAdminSorting($sTableID, "sort", "asc");// sort initialization
$lAdmin = new CAdminList($sTableID, $oSort);// list initialization


$arFilterFields = Array(
	"filter_name",
	"filter_active",
	"filter_chapter_id",
);

$lAdmin->InitFilter($arFilterFields);// filter initialization

$arFilter = Array(
	"ACTIVE" => $filter_active,
	"?NAME" => $filter_name,
	"COURSE_ID" => $COURSE_ID,
	"CHAPTER_ID" => $filter_chapter_id,
);

if($filter_chapter_id=="")
	unset($arFilter["CHAPTER_ID"]);


if ($lAdmin->EditAction()) //save from the list
{
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		$ob = new CChapter;
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
		$rsData = CChapter::GetList(Array($by=>$order), $arFilter);
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
			$ch = new CChapter;
			if(!$ch->Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("LEARNING_DELETE_ERROR"), $ID);
			}
			$DB->Commit();
			break;
		case "activate":
		case "deactivate":
			$ch = new CChapter;
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$ch->Update($ID, $arFields))
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			break;
		}
	}

	if(isset($return_url) && strlen($return_url)>0 && check_bitrix_sessid())
		LocalRedirect($return_url);
}

// fetch data
$rsData = CChapter::GetList(Array($by=>$order),$arFilter, true);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_CHAPTERS")));


// list header
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"TIMESTAMP_X","content"=>GetMessage('LEARNING_COURSE_ADM_DATECH'), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('LEARNING_NAME'),	"sort"=>"name", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('LEARNING_COURSE_ADM_SORT'),"sort"=>"sort", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage('LEARNING_COURSE_ADM_ACT'),"sort"=>"active", "default"=>true),
	array("id"=>"CHAPTERS", "content"=>GetMessage('LEARNING_CHAPTERS'), "default"=>true),
	array("id"=>"LESSONS", "content"=>GetMessage('LEARNING_LESSONS'), "default"=>true),
	array("id"=>"CODE", "content"=>GetMessage('LEARNING_CODE'),	"sort"=>"code", "default"=>false),
));


// building list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddCheckField("ACTIVE");
	$row->AddInputField("NAME",Array("size"=>"35"));
	$row->AddInputField("SORT", Array("size"=>"3"));
	$row->AddInputField("CODE");

	$row->AddViewField("LESSONS", '<a title="'.GetMessage("LEARNING_LESSON_TITLE").'" href="learn_lesson_admin.php?COURSE_ID='.$COURSE_ID.'&lang='.LANG.GetFilterParams("filter_").'&filter_chapter_id='.$f_ID.'">'.intval($f_ELEMENT_CNT).'</a> [<a title="'.GetMessage("LEARNING_LESSON_ADD").'" href="learn_lesson_edit.php?COURSE_ID='.$COURSE_ID.'&lang='.LANG.GetFilterParams("filter_").'&CHAPTER_ID='.$f_ID.'&filter_chapter_id='.$filter_chapter_id.'&from=learn_chapter_admin">+</a>]');


	$row->AddViewField("CHAPTERS", '<a title="'.GetMessage("LEARNING_CHAPTER_TITLE").'" href="learn_chapter_admin.php?COURSE_ID='.$COURSE_ID.'&lang='.LANG.GetFilterParams("filter_").'&filter_chapter_id='.$f_ID.'">'.CChapter::GetCount(Array("COURSE_ID"=>$COURSE_ID,"CHAPTER_ID"=>$f_ID)).'</a> [<a title="'.GetMessage("LEARNING_CHAPTER_ADD").'" href="learn_chapter_edit.php?COURSE_ID='.$COURSE_ID.'&lang='.LANG.'&CHAPTER_ID='.$f_ID.GetFilterParams("filter_").'&filter_chapter_id='.$filter_chapter_id.'&from=learn_chapter_admin">+</a>]');



	$arActions = Array();

/*
	$arActions[] = array(
			"ICON"=>"list",
			"TEXT"=>GetMessage("LEARNING_CHAPTERS")." (".CChapter::GetCount(Array("COURSE_ID"=>$COURSE_ID,"CHAPTER_ID"=>$f_ID)).")",
			"ACTION"=>$lAdmin->ActionRedirect("learn_chapter_admin.php?COURSE_ID=".$COURSE_ID."&lang=".LANG.GetFilterParams("filter_")."&filter_chapter_id=".$f_ID)
		);
*/

	$arActions[] = array(
			"ICON"=>"add",
			"TEXT"=>GetMessage("LEARNING_CHAPTER_ADD"),
			"ACTION"=>$lAdmin->ActionRedirect("learn_chapter_edit.php?COURSE_ID=".$COURSE_ID."&lang=".LANG."&CHAPTER_ID=".$f_ID.GetFilterParams("filter_", false)."&filter_chapter_id=".$filter_chapter_id."&from=learn_chapter_admin")
		);

	//$arActions[] = array("SEPARATOR"=>true);

/*
	$arActions[] = array(
			"ICON"=>"list",
			"TEXT"=>GetMessage("LEARNING_LESSONS")." (".intval($f_ELEMENT_CNT).")",
			"ACTION"=>$lAdmin->ActionRedirect("learn_lesson_admin.php?COURSE_ID=".$COURSE_ID."&lang=".LANG.GetFilterParams("filter_")."&filter_chapter_id=".$f_ID)
		);
*/

	$arActions[] = array(
			"ICON"=>"add",
			"TEXT"=>GetMessage("LEARNING_LESSON_ADD"),
			"ACTION"=>$lAdmin->ActionRedirect("learn_lesson_edit.php?COURSE_ID=".$COURSE_ID."&lang=".LANG.GetFilterParams("filter_", false)."&CHAPTER_ID=".$f_ID."&filter_chapter_id=".$filter_chapter_id."&from=learn_chapter_admin")
		);

	$arActions[] = array("SEPARATOR"=>true);


	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("learn_chapter_edit.php?ID=".$f_ID."&lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$filter_chapter_id.GetFilterParams("filter_", false))

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

$aContext[] =
	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("LEARNING_CHAPTER_ADD"),
		"LINK"=>"learn_chapter_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".IntVal($filter_chapter_id).GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_CHAPTER_ADD")
);

$aContext[] =
	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("LEARNING_LESSON_ADD"),
		//"LINK"=>"learn_chapter_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".IntVal($filter_chapter_id).GetFilterParams("filter_"),
		"LINK"=>"learn_lesson_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".IntVal($filter_chapter_id).GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_LESSON_ADD")
);



$lAdmin->AddAdminContextMenu($aContext);






//$adminChain->AddItem(array("TEXT"=>htmlspecialcharsex($arCourse["NAME"]), "LINK"=>"learn_chapter_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id="));


$chain = $lAdmin->CreateChain();
/*
if (intval($filter_chapter_id)>0)
{

	$nav = CChapter::GetNavChain($COURSE_ID, $filter_chapter_id);
	while($nav->ExtractFields("nav_"))
	{
		if($filter_chapter_id == $nav_ID)
		{
			$chain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>""));

		}
		else
		{
			$chain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>"learn_chapter_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id=".$nav_ID));
		}
	}
}*/


if($filter_chapter_id=="")
{
	$chain->AddItem(array("TEXT"=>"<b>".GetMessage("LEARNING_ALL_CHAPTERS")."</b>", "LINK"=>""));
}
elseif($filter_chapter_id==0)
{
	$chain->AddItem(array("TEXT"=>"<b>".GetMessage("LEARNING_CONTENT")."</b>", "LINK"=>""));
}

$lAdmin->ShowChain($chain);


// list mode check (if AJAX then terminate the script)
$lAdmin->CheckListMode();

$APPLICATION->SetTitle($arCourse["NAME"].": ".GetMessage('LEARNING_CHAPTERS'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$filter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("LEARNING_F_ACTIVE2"),
		GetMessage("LEARNING_F_CHAPTER")
	)
);

?>


<form method="get" action="learn_chapter_admin.php?lang=<?=LANG?>&COURSE_ID=<?=$COURSE_ID?>" name="find_form" onsubmit="return this.set_filter.onclick();">
<?$filter->Begin();?>
	<tr>
		<td align="right"><?echo GetMessage("LEARNING_NAME")?>:</td>
		<td align="left">
			<input type="text" name="filter_name" value="<?echo htmlspecialcharsex($filter_name)?>" size="47">&nbsp;<?=ShowFilterLogicHelp()?>
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
		<td><?echo GetMessage("LEARNING_F_CHAPTER")?>:</td>
		<td>
			<select name="filter_chapter_id" style="width:70%;">

				<option value=""><?echo GetMessage("LEARNING_ALL2")?></option>
				<option value="0" <?if($filter_chapter_id=="0")echo" selected"?>><?echo GetMessage("LEARNING_CONTENT")?></option>
				<?
				$bsections = CChapter::GetTreeList($COURSE_ID);
				while($bsections->ExtractFields("s_")):
					?><option value="<?echo $s_ID?>"<?if($s_ID==$filter_chapter_id)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $s_DEPTH_LEVEL)?><?echo $s_NAME?></option><?
				endwhile;
				?>

			</select>
		</td>
	</tr>

<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>"learn_chapter_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID, "form"=>"find_form"));$filter->End();?>
</form>


<?$lAdmin->DisplayList();?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>