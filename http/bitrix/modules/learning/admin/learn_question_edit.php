<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/prolog.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$strWarning = "";
$message = null;
$bVarsFromForm = false;
$ID = intval($ID);
$COURSE_ID = intval($COURSE_ID);
$LESSON_ID = intval($LESSON_ID);
$filter_lesson_id = intval($filter_lesson_id);

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



$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("LEARNING_ADMIN_TAB1"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB1_EX")),
	array("DIV" => "edit2", "TAB" => GetMessage("LEARNING_ADMIN_TAB2"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB2_EX")),
	array("DIV" => "edit3", "TAB" => GetMessage("LEARNING_ADMIN_TAB3"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB3_EX")),
);


$tabControl = new CAdminTabControl("tabControl", $aTabs);

if (!$bBadCourse && $_SERVER["REQUEST_METHOD"] == "POST" && strlen($Update)>0 && check_bitrix_sessid())
{
	$arFILE_ID = $_FILES["FILE_ID"];
	$arFILE_ID["del"] = ${"FILE_ID_del"};
	$arFILE_ID["MODULE_ID"] = "learning";


	$cq = new CLQuestion;

	$arFields = Array(

		"LESSON_ID" => $LESSON_ID,
		"NAME" => $NAME,
		"QUESTION_TYPE" => $QUESTION_TYPE,
		"SORT" => $SORT,
		"SELF" => $SELF,
		"ACTIVE" => $ACTIVE,
		"CORRECT_REQUIRED" => $CORRECT_REQUIRED,
		"POINT" => $POINT,
		"FILE_ID" => $arFILE_ID,
		"DESCRIPTION" => $DESCRIPTION,
		"DESCRIPTION_TYPE" => $DESCRIPTION_TYPE,
		"INCORRECT_MESSAGE" => $INCORRECT_MESSAGE,
		"EMAIL_ANSWER" => $EMAIL_ANSWER,
	);

	if($ID>0)
	{
		$res = $cq->Update($ID, $arFields);
	}
	else
	{
		$ID = $cq->Add($arFields);
		$res = ($ID>0);
	}

	if(!$res)
	{
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
		$bVarsFromForm = true;
	}
	else
	{

		if ($QUESTION_TYPE != "T")
		{
			//Answers
			$answers = CLAnswer::GetList(Array(),Array("QUESTION_ID" => $ID));

			while ($a = $answers->GetNext())
			{
				//delete?
				if (${"ANSWER_".$a["ID"]."_DEL"} == "Y")
				{
						if(!CLAnswer::Delete($a["ID"]))
						{
							$message = new CAdminMessage(Array("MESSAGE" => GetMessage("LEARNING_DELETE_ERROR")));
							$bVarsFromForm = true;
						}
				}
				else
				{

					$arFields = Array(
						"QUESTION_ID" => $ID,
						"SORT" => ${"ANSWER_".$a["ID"]."_SORT"},
						"ANSWER" => ${"ANSWER_".$a["ID"]."_ANSWER"},
					);

					switch ($QUESTION_TYPE)
					{
						case "M":
							$arFields["CORRECT"] = (${"ANSWER_".$a["ID"]."_CORRECT"} == "Y" ? "Y" : "N");
						break;
						case "S":
						default:
							$arFields["CORRECT"] = ($a["ID"] == $ANSWER_CORRECT ? "Y" : "N");
					}

					$asw = new CLAnswer;
					$res = $asw->Update($a["ID"], $arFields);
					if (!$res)
					{
						$message = new CAdminMessage(Array("MESSAGE" => GetMessage("LEARNING_SAVE_ERROR").$a["ID"]));
						$bVarsFromForm = true;
					}
				}
			}

			//add new
			for ($i=0; $i<5; $i++)
			{
				if (strlen(${"ANSWER_n".$i."_ANSWER"})<=0) continue;

				$arFields = Array(
					"SORT" => ${"ANSWER_n".$i."_SORT"},
					"ANSWER" => ${"ANSWER_n".$i."_ANSWER"},
					"QUESTION_ID" => $ID,
				);

				switch ($QUESTION_TYPE)
				{
					case "M":
						$arFields["CORRECT"] = (${"ANSWER_n".$i."_CORRECT"} == "Y" ? "Y" : "N");
					break;
					case "S":
					default:
						$arFields["CORRECT"] = ("n".$i == $ANSWER_CORRECT ? "Y" : "N");
				}

				$asw = new CLAnswer;
				$AswerID = $asw->Add($arFields);
				if (intval($AswerID)<=0)
				{
					if ($e = $APPLICATION->GetException())
						$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
					$bVarsFromForm = true;
				}

			}
		}
		else
		{
			//Delete answers
			$answers = CLAnswer::GetList(Array(),Array("QUESTION_ID" => $ID));

			while ($a = $answers->GetNext())
			{
				CLAnswer::Delete($a["ID"]);
			}

		}


		//Redirect
		if (!$bVarsFromForm)
		{
			if(strlen($apply)<=0)
			{
				if ($from == "learn_admin")
					LocalRedirect("/bitrix/admin/learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$arLesson["CHAPTER_ID"].GetFilterParams("filter_", false)."&from=learn_admin");
				elseif ($from == "learn_menu")
					LocalRedirect("/bitrix/admin/learn_question_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_", false)."&from=learn_menu");
				elseif (strlen($return_url)>0)
					LocalRedirect($return_url);
				else
					LocalRedirect("/bitrix/admin/learn_question_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$arLesson["CHAPTER_ID"]."&LESSON_ID=".$LESSON_ID.GetFilterParams("filter_", false));
			}
			LocalRedirect("/bitrix/admin/learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$arLesson["CHAPTER_ID"]."&LESSON_ID=".$LESSON_ID."&ID=".$ID."&".$tabControl->ActiveTabParam().GetFilterParams("filter_", false).($from == "learn_admin" ? "&from=learn_admin" :""));
		}


	}

}

//Defaults
$str_SELF = "N";
$str_ACTIVE = "Y";
$str_CORRECT_REQUIRED = "N";
$str_DIRECTION = "V";
$str_DESCRIPTION_TYPE= "text";
$str_SORT = "500";
$str_QUESTION_TYPE = "S";
$str_POINT = 10;
$str_EMAIL_ANSWER = "N";

$result = CLQuestion::GetByID($ID);
if(!$result->ExtractFields("str_"))
	$ID = 0;

if($bVarsFromForm)
{
	$ACTIVE = ($ACTIVE != "Y"? "N":"Y");
	$DB->InitTableVarsForEdit("b_learn_question", "", "str_");
}

if (isset($QUESTION_TYPE))
	$str_QUESTION_TYPE = $QUESTION_TYPE;

if ($str_QUESTION_TYPE == "T")
{
	$tabControl->tabs[1] = $tabControl->tabs[2];
	unset($tabControl->tabs[2]);
}

//$adminChain->AddItem(array("TEXT"=>htmlspecialcharsex($arCourse["NAME"]), "LINK"=>"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id=0"));

/*
$nav = CChapter::GetNavChain($COURSE_ID, $arLesson["CHAPTER_ID"]);
while($nav->ExtractFields("nav_"))
{
	$adminChain->AddItem(array("TEXT"=>$nav_NAME, "LINK"=>"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID.GetFilterParams("filter_")."&filter_chapter_id=".$nav_ID));
}
*/

//$adminChain->AddItem(array("TEXT"=>"<b>".htmlspecialcharsex($arLesson["NAME"])." (".ToLower(GetMessage("LEARNING_QUESTION")).")</b>", "LINK"=>""));

if ($ID > 0)
	$APPLICATION->SetTitle($arCourse["NAME"].": ".GetMessage("LEARNING_QUESTION").": ".GetMessage("LEARNING_EDIT_TITLE"));
else
	$APPLICATION->SetTitle($arCourse["NAME"].": ".GetMessage('LEARNING_QUESTION').": ".GetMessage("LEARNING_NEW_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=>"learn_question_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&LESSON_ID=".$LESSON_ID.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("LEARNING_QUESTION_LIST")
	),
);

if (!$bBadCourse):

//if (!$bBadLesson):

if ($ID > 0)
{
	$arContextPopup = Array(
		Array(
			"TEXT" => GetMessage('LEARNING_SINGLE_CHOICE'),
			"ACTION" => "window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$arLesson["CHAPTER_ID"]."&LESSON_ID=".$LESSON_ID.GetFilterParams("filter_", false)."&QUESTION_TYPE=S".($from=="learn_admin"?"&from=learn_admin":"")."'",

		),
		Array(
			"TEXT" => GetMessage('LEARNING_MULTIPLE_CHOICE'),
			"ACTION" => "window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$arLesson["CHAPTER_ID"]."&LESSON_ID=".$LESSON_ID.GetFilterParams("filter_", false)."&QUESTION_TYPE=M".($from=="learn_admin"?"&from=learn_admin":"")."'",
		),
		Array(
			"TEXT" => GetMessage('LEARNING_SORTING'),
			"ACTION" => "window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$arLesson["CHAPTER_ID"]."&LESSON_ID=".$LESSON_ID.GetFilterParams("filter_", false)."&QUESTION_TYPE=R".($from=="learn_admin"?"&from=learn_admin":"")."'",

		),
		Array(
			"TEXT" => GetMessage('LEARNING_TEXT_ANSWER'),
			"ACTION" => "window.location='learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$arLesson["CHAPTER_ID"]."&LESSON_ID=".$LESSON_ID.GetFilterParams("filter_", false)."&QUESTION_TYPE=T".($from=="learn_admin"?"&from=learn_admin":"")."'",
		),
);



	$aContext[] = 	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_CREATE"),
		"MENU" => $arContextPopup,
		"TITLE"=>GetMessage("LEARNING_ADD")
	);

	$aContext[] = 	array(
		"ICON" => "btn_delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("LEARNING_CONFIRM_DEL_MESSAGE")."'))window.location='learn_question_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$arLesson["CHAPTER_ID"]."&LESSON_ID=".$LESSON_ID."&action=delete&ID=".$ID."&".bitrix_sessid_get().GetFilterParams("filter_", false)."';",
	);
}

$context = new CAdminContextMenu($aContext);
$context->Show();

if ($message)
	echo $message->Show();

?>

<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>&COURSE_ID=<?echo $COURSE_ID?><?echo GetFilterParams("filter_");?>" enctype="multipart/form-data" name="question_edit" onSubmit="return OnSubmit();">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="from" value="<?echo htmlspecialchars($from)?>">
<input type="hidden" name="return_url" value="<?echo htmlspecialchars($return_url)?>">
<input type="hidden" name="ID" value="<?echo $ID?>">

<?$tabControl->Begin();?>

<?$tabControl->BeginNextTab();?>
	<?if($ID>0):?>
	<tr>
		<td width="40%">ID:</td>
		<td width="60%"><?echo $str_ID?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LEARNING_LAST_UPDATE")?>:</td>
		<td><?echo $str_TIMESTAMP_X?></td>
	</tr>
	<?endif;?>
	<tr>
		<td valign="top" width="50%" align="right"><?echo GetMessage("LEARNING_ACTIVE")?>:</td>
		<td valign="top" width="50%">
			<input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("LEARNING_QUESTION_TYPE")?>:</td>
		<td width="60%">
			<select onchange="if(this[this.selectedIndex].value!='') window.location=this[this.selectedIndex].value;"<?php echo (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? "disabled=\"disabled\"" : "")?>>
				<option value="<?=$APPLICATION->GetCurPageParam("QUESTION_TYPE=S", array("QUESTION_TYPE","tabControl_active_tab"))?>" <?if($str_QUESTION_TYPE=="S") echo "selected"?>><?echo GetMessage("LEARNING_SINGLE_CHOICE")?></option>
				<option value="<?=$APPLICATION->GetCurPageParam("QUESTION_TYPE=M", array("QUESTION_TYPE", "tabControl_active_tab"))?>" <?if($str_QUESTION_TYPE=="M") echo "selected"?>><?echo GetMessage("LEARNING_MULTIPLE_CHOICE")?></option>
				<option value="<?=$APPLICATION->GetCurPageParam("QUESTION_TYPE=R", array("QUESTION_TYPE", "tabControl_active_tab"))?>" <?if($str_QUESTION_TYPE=="R") echo "selected"?>><?echo GetMessage("LEARNING_SORTING")?></option>
				<option value="<?=$APPLICATION->GetCurPageParam("QUESTION_TYPE=T", array("QUESTION_TYPE", "tabControl_active_tab"))?>" <?if($str_QUESTION_TYPE=="T") echo "selected"?>><?echo GetMessage("LEARNING_TEXT_ANSWER")?></option>
			</select>
			<input type="hidden" name="QUESTION_TYPE" value="<?=$str_QUESTION_TYPE?>">
		</td>
	</tr>

 	<tr>
		<td><span class="required">*</span><?echo GetMessage("LEARNING_LESSON")?>:</td>
		<td>
			<select name="LESSON_ID" style="width:70%;">
				<?
				$bsections = CLesson::GetList(Array(),Array("COURSE_ID" => $COURSE_ID));
				while($bsections->ExtractFields("s_")):
					?><option value="<?echo $s_ID?>"<?if($s_ID==$filter_lesson_id && intval($LESSON_ID)<=0 || $s_ID==$LESSON_ID)echo " selected"?>><?echo $s_NAME?></option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>

	<tr>
		<td><span class="required">*</span><?echo GetMessage("LEARNING_QUESTION_NAME")?>:</td>
		<td>
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
		<td><?echo GetMessage("LEARNING_POINT")?>:</td>
		<td>
			<input type="text" name="POINT" size="7" maxlength="10" value="<?echo $str_POINT?>">
		</td>
	</tr>

	<?php if ($str_QUESTION_TYPE != "T"):?>
	<tr>
		<td><?echo GetMessage("LEARNING_F_SELF")?>:</td>
		<td>
			<input type="checkbox" name="SELF" value="Y"<?if($str_SELF=="Y")echo " checked"?>>
		</td>
	</tr>
	<?php endif?>

	<tr>
		<td><?echo GetMessage("LEARNING_CORRECT_REQUIRED")?>:</td>
		<td>
			<input type="checkbox" name="CORRECT_REQUIRED" value="Y"<?if($str_CORRECT_REQUIRED=="Y")echo " checked"?>>
		</td>
	</tr>

	<?php if ($str_QUESTION_TYPE != "T"):?>
		<tr>
			<td><?echo GetMessage("LEARNING_INCORRECT_MESSAGE")?>:</td>
			<td>
				<input type="text" name="INCORRECT_MESSAGE" size="50" maxlength="255" value="<?echo $str_INCORRECT_MESSAGE?>">
			</td>
		</tr>
	<?php else:?>
		<tr>
			<td><?echo GetMessage("LEARNING_EMAIL_ANSWER")?>:</td>
			<td>
				<input type="checkbox" name="EMAIL_ANSWER" value="Y"<?if($str_EMAIL_ANSWER=="Y")echo " checked"?>>
			</td>
		</tr>
	<?php endif?>

	<tr>
		<td valign="top"><?echo GetMessage("LEARNING_PICTURE")?>:</td>
		<td>
			<?echo CFile::InputFile("FILE_ID", 20, $str_FILE_ID, false, 0, "IMAGE", "", 40);?><br>
			<?echo CFile::ShowImage($str_FILE_ID, 200, 200, "border=0", "", true)?>

		</td>
	</tr>


<?php if ($str_QUESTION_TYPE != "T"):?>

	<?$tabControl->BeginNextTab();?>

	<tr>
		<td valign="top" colspan="2">
	<?
	switch ($str_QUESTION_TYPE):

	case "M": //Multiple choice
	case "S": //Single choice
	default:?>

	<table cellpadding="0" cellspacing="0" width="100%" class="internal">
		<tr class="heading">
			<td align="center" width="40">ID</td>
			<?php if ($str_QUESTION_TYPE != "R"):?><td align="center" width="40"><?echo GetMessage("LEARNING_QUESTION_ADM_CORRECT")?></td><?php endif?>
			<td align="center"><?echo GetMessage("LEARNING_ANSWER")?></td>
			<td align="center" width="40"><?echo GetMessage("LEARNING_COURSE_ADM_SORT")?></td>
			<td align="center" width="40"><?echo GetMessage("LEARNING_COURSE_ADM_DELETE")?></td>
		</tr>


	<?

	$i = 0;
	function _GetOldAndNew($answers)
	{
		global $i;

		if ($i==0 && ($tmp = $answers->ExtractFields("str_ANSWER_")))
			return $tmp;

		global $str_ANSWER_ID, $str_ANSWER_CORRECT, $str_ANSWER_ANSWER, $str_ANSWER_SORT;

		if($i > 3) return false;

		$str_ANSWER_ID = "n".$i;
		$str_ANSWER_CORRECT = "";
		$str_ANSWER_ANSWER = "";
		$str_ANSWER_SORT = "10";

		$i++;

		return true;
	}

	$SINGLE_ID = "";

	$answers = CLAnswer::GetList(Array("SORT" => "ASC","ID" => "ASC"),Array("QUESTION_ID"=>$ID));
	while ($r = _GetOldAndNew($answers)):

	if ($bVarsFromForm)
	{
		$DB->InitTableVarsForEdit("b_learn_answer", "ANSWER_".$str_ANSWER_ID."_", "str_ANSWER_");

		if ($str_QUESTION_TYPE == "S" && isset($_POST["ANSWER_CORRECT"]))
		{
			$SINGLE_ID = $_POST["ANSWER_CORRECT"];
		}

	}


	?>
		<tr>
			<td align="center" width="40"><?echo ($str_ANSWER_ID>0?$str_ANSWER_ID:"")?></td>
			<?php if ($str_QUESTION_TYPE != "R"):?>
				<td align="center" width="40">
				<?if ($str_QUESTION_TYPE == "M"):?>
					<input type="checkbox" name="ANSWER_<?=$str_ANSWER_ID?>_CORRECT" value="Y"<?if($str_ANSWER_CORRECT=="Y")echo " checked"?>>
				<?else:?>
					<input type="radio" name="ANSWER_CORRECT" value="<?=$str_ANSWER_ID?>"<?if($str_ANSWER_CORRECT=="Y" || $SINGLE_ID == $str_ANSWER_ID)echo " checked"?>>
				<?endif?>
				</td>
			<?php endif?>
			<td align="center">
				<input type="text" name="ANSWER_<?=$str_ANSWER_ID?>_ANSWER" value="<?=$str_ANSWER_ANSWER?>" style="width: 98%">
			</td>
			<td align="center" width="40">
				<input type="text" size="3"  name="ANSWER_<?=$str_ANSWER_ID?>_SORT" value="<?=$str_ANSWER_SORT?>">
			</td>
			<td align="center" width="40">
				<?if(intval($str_ANSWER_ID)>0):?>
					<input type="checkbox" name="ANSWER_<?=$str_ANSWER_ID?>_DEL" value="Y">
				<?endif?>
				<input type="hidden" name="ANSWER_HIDDEN_ID[]" value="<?=$str_ANSWER_ID?>">
			</td>
		</tr>
	<?endwhile;?>



	</table>

	<?endswitch;?>
		</td>
	</tr>

<?php endif?>

<?$tabControl->BeginNextTab();?>

	<?
	CAdminFileDialog::ShowScript(Array
		(
			"event" => "OpenFileBrowserWindMedia",
			"arResultDest" => Array("FUNCTION_NAME" => "SetUrl"),
			"arPath" => Array("SITE" => $_GET["site"], "PATH" =>(strlen($str_FILENAME)>0 ? GetDirPath($str_FILENAME) : '')),
			"select" => 'F',// F - file only, D - folder only,
			"operation" => 'O',// O - open, S - save
			"showUploadTab" => true,
			"showAddToMenuTab" => false,
			"fileFilter" => 'wmv,flv,mp4,wma,mp3',//'' - don't shjow select, 'image' - only images; "ext1,ext2" - Only files with ext1 and ext2 extentions;
			"allowAllFiles" => true,
			"SaveConfig" => true
		)
	);
	?>

	<?if(COption::GetOptionString("learning", "use_htmledit", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td valign="top" colspan="2" align="center">
			<?php function CustomizeEditor() {?>
				<?php ob_start()?>
					<div class="bxed-dialog">
						<table class="bx-image-dialog-tbl">
					        <tr>
					        	<td class="bx-par-title"><?echo GetMessage("LEARNING_PATH_TO_FILE")?>:</td>
					        	<td class="bx-par-val" colspan="3">
					        		<input type="text" size="30" id="mediaPath" />
									<input type="button" value="..." id="OpenFileBrowserWindMedia_button">
					        	</td>
					    	</tr>
					    	<tr>
					        	<td class="bx-par-title"><?echo GetMessage("LEARNING_WIDTH")?>:</td>
					        	<td width="80px"><input type="text" size="3" id="mediaWidth" value="100" /></td>
					        	<td><?echo GetMessage("LEARNING_HEIGHT")?>:</td>
					        	<td class="bx-par-val"><input type="text" size="3" id="mediaHeight" value="100" /></td>
					    	</tr>
						</table>
					</div>
				<?php $dialogHTML = ob_get_clean()?>
				<script type="text/javascript">
					var pEditor;
					var pElement;

					function SetUrl(filename, path, site)
					{
						if (path.substr(-1) == "/")
						{
							path = path.substr(0, path.length - 1);
						}
						var url = path+'/'+filename;
						BX("mediaPath").value = url;
						if(BX("mediaPath").onchange)
							BX("mediaPath").onchange();
					}

					function _mediaParser(_str, pMainObj)
					{
						// **** Parse WMV ****
						// b1, b3 - quotes
						// b2 - id of the div
						// b4 - javascript config
						var ReplaceWMV = function(str, b1, b2, b3, b4)
						{
							var
								id = b2,
								JSConfig, w, h, prPath;

							try {eval('JSConfig = ' + b4); } catch (e) { JSConfig = false; }
							if (!id || !JSConfig)
								return '';

							var w = (parseInt(JSConfig.width) || 50);
							var h = (parseInt(JSConfig.height) || 25);

							var arTagParams = {file: JSConfig.file};
							var bxTag =  pMainObj.GetBxTag(id);

							if (bxTag && bxTag && bxTag.tag == "media")
							{
								arTagParams.id = id;
							}
							return '<img  id="' + pMainObj.SetBxTag(false, {tag: 'media', params: arTagParams}) + '" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url(/bitrix/images/learning/icons/media.gif); background-position: center center; background-repeat: no-repeat; width: '+w+'px; height: '+h+'px;" width="'+w+'" height="'+h+'" />';
						}
						_str = _str.replace(/<script.*?silverlight\.js.*?<\/script>\s*?<script.*?wmvplayer\.js.*?<\/script>\s*?<div.*?id\s*?=\s*?("|\')(.*?)\1.*?<\/div>\s*?<script.*?jeroenwijering\.Player\(document\.getElementById\(("|\')\2\3.*?wmvplayer\.xaml.*?({.*?})\).*?<\/script>/ig, ReplaceWMV);

						// **** Parse FLV ****
						var ReplaceFLV = function(str, attr)
						{
							attr = attr.replace(/[\r\n]+/ig, ' '); attr = attr.replace(/\s+/ig, ' '); attr = jsUtils.trim(attr);
							var
								arParams = {},
								arFlashvars = {},
								w, h, id, prPath;

							attr.replace(/([^\w]??)(\w+?)\s*=\s*("|\')([^\3]+?)\3/ig, function(s, b0, b1, b2, b3)
							{
								b1 = b1.toLowerCase();
								if (b1 == 'src' || b1 == 'type' || b1 == 'allowscriptaccess' || b1 == 'allowfullscreen' || b1 == 'pluginspage' || b1 == 'wmode')
									return '';
								arParams[b1] = b3; return b0;
							});
							id = arParams.id;

							if (!id || !arParams.flashvars)
								return str;

							arParams.flashvars.replace(/(\w+?)=((?:\s|\S)*?)&/ig, function(s, name, val) { arFlashvars[name] = val; return ''; });
							var w = (parseInt(arParams.width) || 50);
							var h = (parseInt(arParams.height) || 25);

							var arTagParams = {file: arFlashvars["file"]};
							var bxTag =  pMainObj.GetBxTag(id);

							if (bxTag && bxTag && bxTag.tag == "media")
							{
								arTagParams.id = id;
							}
							return '<img  id="' + pMainObj.SetBxTag(false, {tag: 'media', params: arTagParams}) + '" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url(/bitrix/images/learning/icons/media.gif); background-position: center center; background-repeat: no-repeat; width: '+w+'px; height: '+h+'px;" width="'+w+'" height="'+h+'" />';
						}

						_str = _str.replace(/<object.*?>.*?<embed((?:\s|\S)*?player\/mediaplayer\/player\.swf(?:\s|\S)*?)(?:>\s*?<\/embed)?(?:\/?)?>.*?<\/object>/ig, ReplaceFLV);
						return _str;
					}
					arContentParsers.unshift(_mediaParser);

					function _mediaUnParser(_node, pMainObj)
					{
						bxTag = pMainObj.GetBxTag(_node.arAttributes["id"]);

						if (bxTag && bxTag.tag && bxTag.tag == "media")
						{
							var ext = bxTag.params.file.substr(bxTag.params.file.length - 3);
							var bWM = ext == "wmv" || ext == "wma";
							if (!bWM) // FL
							{
								var str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" ';
								str += 'id="' + _node.arAttributes["id"] + '" ';
								str += 'width="' + _node.arAttributes["width"] + '" ';
								str += 'height="' + _node.arAttributes["height"] + '" ';
								str += '>';
								str += '<param name="movie" value="/bitrix/components/bitrix/player/mediaplayer/player.swf">';

								var embed = '<embed src="/bitrix/components/bitrix/player/mediaplayer/player.swf" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" pluginspage="http:/' + '/www.macromedia.com/go/getflashplayer" ';
								embed += 'id="' + _node.arAttributes["id"] + '" ';

								var arParams = {
									"menu": "true",
									"wmode": "transparent",
									"width": _node.arAttributes["width"],
									"height": _node.arAttributes["height"],
									"flashvars" : {
										"file" : bxTag.params.file,
										"logo.hide" : "true",
										"skin": "/bitrix/components/bitrix/player/mediaplayer/skins/bitrix.swf",
										"repeat" : "N",
										"bufferlength" : "10",
										"dock" : "true"
									}
								}

								for (i in arParams)
								{
									if (i == 'flashvars')
									{
										embed += 'flashvars="';
										str += '<param name="flashvars" value="';
										for (k in arParams[i])
										{
											embed += k + '=' + arParams[i][k] + '&';
											str += k + '=' + arParams[i][k] + '&';
										}
										embed = embed.substring(0, embed.length - 1) + '" ';
										str = str.substring(0, str.length - 1) + '">';
									}
									else
									{
										embed += i + '="' + arParams[i] + '" ';
										str += '<param name="' + i +'" value="' + arParams[i] +'">';
									}
								}
								embed += '/>';
								str += embed +'</object>';
							}
							else // WM
							{
								str = '<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/silverlight.js" /><\/script>' +
								'<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js"><\/script>' +
								'<div id="' + _node.arAttributes["id"] + '">WMV Player</div>' +
								'<script type="text/javascript">new jeroenwijering.Player(document.getElementById("' + _node.arAttributes["id"] + '"), "/bitrix/components/bitrix/player/wmvplayer/wmvplayer.xaml", {';

								var arParams = {
									"file" : bxTag.params.file,
									"bufferlength" : "10",
									"width": _node.arAttributes["width"],
									"height": _node.arAttributes["height"],
									"windowless": "true"
								}


								for (i in arParams)
									str += i + ': "' + arParams[i] + '", ';
								str = str.substring(0, str.length - 2);

								str += '});<\/script>';
							}
							return str;
						}

						return false;
					}
					oBXEditorUtils.addUnParser(_mediaUnParser);

	       			var pSaveButton = new BX.CWindowButton({
	           			'title': '<?echo GetMessage("LEARNING_SAVE")?>',
	           			'action': function() {
	           				var path = BX('mediaPath').value;
	           				var width = BX('mediaWidth').value;
	           				var height = BX('mediaHeight').value;

	       					this.parentWindow.Close();
							if (path.length > 0 && parseInt(width) > 0 && parseInt(height) > 0)
							{
		       					if (pElement && pElement.getAttribute && pElement.getAttribute("id"))
		       					{
									var bxTag =  pEditor.GetBxTag(pElement.getAttribute("id"))
									if (bxTag && bxTag.tag && bxTag.tag == "media")
									{
										bxTag.params.file = path;
				       					SAttr(pElement, "width", width);
				       					SAttr(pElement, "height", height);
				       					pElement.style.width = width + "px";
				       					pElement.style.height = height + "px";
									}
		       					}
		       					else
		       					{
			       					var arParams = {file: path};

		       						pEditor.insertHTML('<img id="' + pEditor.SetBxTag(false, {tag: 'media', params: arParams}) + '" src="/bitrix/images/1.gif" style="border: 1px solid rgb(182, 182, 184); background-color: rgb(226, 223, 218); background-image: url(/bitrix/images/learning/icons/media.gif); background-position: center center; background-repeat: no-repeat; width: '+width+'px; height: '+height+'px;" width="'+width+'" height="'+height+'" />');
		       					}
							}
							pElement = null;
						}
	       			});
	    			var pDialog = new BX.CDialog({
	    				title : '<?echo GetMessage("LEARNING_VIDEO_AUDIO")?>',
	    				content: '<?php echo CUtil::JSEscape(preg_replace("~>\s+<~", "><",  trim($dialogHTML)))?>',
	    				height: 180,
	    				width: 520,
	    				resizable: false,
	    				buttons: [pSaveButton, BX.CDialog.btnClose]
	    			});
					var pMediaButton = [
                       	'BXButton',
                       	{
                       		id : 'media',
                       		src : '/bitrix/images/learning/icons/media.gif',
                       		name : "<?echo GetMessage("LEARNING_VIDEO_AUDIO")?>",
                       		handler : function () {
                       			pDialog.Show();
                       			pEditor = this.pMainObj;
            					BX("OpenFileBrowserWindMedia_button").onclick = OpenFileBrowserWindMedia;

                   				pElement = pEditor.GetSelectionObject();
                   				if (pElement && pElement.getAttribute && pElement.getAttribute("id"))
                       			{
									var bxTag =  pEditor.GetBxTag(pElement.getAttribute("id"))
									if (bxTag && bxTag.tag && bxTag.tag == "media")
									{
	                       				BX('mediaPath').value = bxTag.params.file;
	                   					BX('mediaWidth').value = pElement.getAttribute("width");
	                   					BX('mediaHeight').value = pElement.getAttribute("height");
									}
                   				}
                   				else
                       			{
                       				BX('mediaPath').value = "";
                   					BX('mediaWidth').value = "400";
                   					BX('mediaHeight').value = "300";
                   				}
                       		}
                       	}
                    ];
                    if (window.lightMode)
                    {
                		for(var i = 0, l = arGlobalToolbar.length; i < l ; i++)
                		{
                			var arButton = arGlobalToolbar[i];
                			if (arButton[1] && arButton[1].id == "insert_flash" && arGlobalToolbar[i+1][1].id != "media") {
                    			arGlobalToolbar.splice(i + 1, 0, pMediaButton);
                				break;
                			}
                		}
                    }
                    else
                    {
						oBXEditorUtils.appendButton("insert_media", pMediaButton, "standart");
                    }
				</script>
			<?php }?>
			<?php AddEventHandler("fileman", "OnIncludeHTMLEditorScript", "CustomizeEditor"); ?>
			<?php CFileMan::AddHTMLEditorFrame(
				"DESCRIPTION",
				$str_DESCRIPTION,
				"DESCRIPTION_TYPE",
				$str_DESCRIPTION_TYPE,
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
		<td><?echo GetMessage("LEARNING_DESC_TYPE")?>:</td>
		<td>
				<input type="radio" name="DESCRIPTION_TYPE" value="text"<?if($str_DESCRIPTION_TYPE!="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_TEXT")?> / <input type="radio" name="DESCRIPTION_TYPE" value="html"<?if($str_DESCRIPTION_TYPE=="html")echo " checked"?>> <?echo GetMessage("LEARNING_DESC_TYPE_HTML")?>

		</td>
	</tr>
	<tr>
		<td valign="top" align="center" colspan="2">
			<textarea style="width:100%; height:300px;" name="PREVIEW_TEXT" wrap="virtual"><?echo $str_DESCRIPTION?></textarea>
		</td>
	</tr>
	<?endif?>
<?
$tabControl->Buttons(
	Array("back_url" =>
		$from == "learn_admin"
		?
		"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$arLesson["CHAPTER_ID"].GetFilterParams("filter_", false)."&from=learn_admin"
		:
		"learn_question_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&LESSON_ID=".$LESSON_ID.GetFilterParams("filter_", false)
	)
);
$tabControl->End();?>
</form>
<?$tabControl->ShowWarnings("question_edit", $message);?>


<script type="text/javascript">

function OnSubmit()
{
	var form = document.forms['question_edit'];
	var ids = form.elements['ANSWER_HIDDEN_ID[]'];

	//Text or Sorting
	if (form.elements['QUESTION_TYPE'].value == "T" || form.elements['QUESTION_TYPE'].value == "R")
	{
		return true;
	}
	//Single
	else if (form.elements['QUESTION_TYPE'].value == "S")
	{
		el = form.elements['ANSWER_CORRECT'];
		for(i=0; i<el.length; i++)
		{
			if (el[i].checked == true && form.elements['ANSWER_'+el[i].value+'_ANSWER'].value != "")
				return true;
		}
	}
	//Multple
	else if (form.elements['QUESTION_TYPE'].value == "M")
	{
		for (i=0; i < ids.length; i++)
		{
			if (form.elements['ANSWER_'+ids[i].value+'_CORRECT'].checked == true && form.elements['ANSWER_'+ids[i].value+'_ANSWER'].value != "")
				return true;
		}
	}

	return confirm("<?=GetMessage("LEARNING_CONFIRM_CHECK_ANSWER")?>");

}

function CheckAnswer()
{
	var form = document.forms['question_edit'];

	for (i=0; ; i++)
	{
		var el = form.elements['ANSWER_n'+i+'_ANSWER'];
		if (el)
		{
			if (el.value == "")
				continue;
			else
				return true;
		}
		else
			return false;
	}

	return false;
}

function CheckRightAnswer()
{
	var form = document.forms['question_edit'];

	var answer = form.elements['ANSWER_CORRECT'];

	if (answer)
	{
		for(i=0; i<answer.length; i++)
		{
			if (answer[i].checked == true)
				return true;
		}
	}
	else
	{
		for (i=0; ; i++)
		{
			var el = form.elements['ANSWER_n'+i+'_CORRECT'];
			if (el)
			{
				if (el.checked == true)
					return true;
			}
			else
				return false;
		}
	}

	return false;
}

</script>

<?echo BeginNote();?>
<span class="required">*</span> - <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?//else: //!bBadLesson
//$context = new CAdminContextMenu($aContext);
//$context->Show();
//CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_LESSON"));
//endif;


else://!bBadCourse

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
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
