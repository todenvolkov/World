<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/learning/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");

set_time_limit(0);
$STEP = IntVal($STEP);
if ($STEP <= 0)
	$STEP = 1;
if ($REQUEST_METHOD == "POST" && strlen($backButton) > 0)
	$STEP = $STEP - 2;
if ($REQUEST_METHOD == "POST" && strlen($backButton2) > 0)
	$STEP = 1;

$COURSE_ID = intval($COURSE_ID);
$strError = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && $STEP > 1 && check_bitrix_sessid())
{
	if ($STEP > 1)
	{
		$res = CCourse::GetList(Array("sort" => "asc"),Array("ID" => $COURSE_ID,"MIN_PERMISSION" => "W"));
		if (!$arCourse = $res->GetNext())
			$strError .= GetMessage("LEARNING_BAD_COURSE")."<br>";

		if (strlen($strError) > 0)
			$STEP = 1;
	}

	if ($STEP > 2)
	{
		$tmp_dir = BX_PERSONAL_ROOT."/tmp/learning/".uniqid(rand());
		CheckDirPath($_SERVER["DOCUMENT_ROOT"].$tmp_dir);

		$DATA_FILE_NAME = Rel2Abs("/", $DATA_FILE_NAME);

		if (strlen($DATA_FILE_NAME) <= 0)
		{
			$strError .= GetMessage("LEARNING_NO_DATA_FILE")."<br>";
		}
		else
		{
			$bUseCompression = true;
			if(!extension_loaded('zlib') || !function_exists("gzcompress"))
				$bUseCompression = false;

			if (substr($DATA_FILE_NAME, -6) != "tar.gz")
				$DATA_FILE_NAME .= ".tar.gz";

			if (is_file($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME))
				@unlink($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);

			if ($arCourse["SCORM"] == "Y")
			{
				$dir = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/learning/scorm/".$COURSE_ID."/";

				$arc = new CArchiver($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, $bUseCompression);
				$res = $arc->Add("\"".$_SERVER["DOCUMENT_ROOT"].$dir."\"", false, $_SERVER["DOCUMENT_ROOT"].$dir);

				if (!$res)
				{
					$arErrors = &$arc->GetErrors();
					foreach ($arErrors as $value)
						$strError .= "[".$value[0]."] ".$value[1]."<br>";
				}
			}
			else
			{
				$package = new CCoursePackage($COURSE_ID);

				if (strlen($package->LAST_ERROR) <= 0)
				{
					$success = $package->CreatePackage($tmp_dir);

					if ($success)
					{
						$arc = new CArchiver($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME, $bUseCompression);
						$res = $arc->Add("\"".$_SERVER['DOCUMENT_ROOT'].$tmp_dir."\"", false, $_SERVER['DOCUMENT_ROOT'].$tmp_dir);

						if (!$res)
						{
							$arErrors = &$arc->GetErrors();
							foreach ($arErrors as $value)
								$strError .= "[".$value[0]."] ".$value[1]."<br>";
						}

						DeleteDirFilesEx($tmp_dir);
					}
					else
					{
						$strError .= $package->LAST_ERROR;
					}
				}
				else
				{
					$strError .= $package->LAST_ERROR;
				}
			}
		}

		if (strlen($strError) > 0)
			$STEP = 2;
	}
}

$APPLICATION->SetTitle(GetMessage("LEARNING_PAGE_TITLE")." ".$STEP);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (defined("LEARNING_ADMIN_ACCESS_DENIED"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"), false);
CAdminMessage::ShowMessage($strError);
?>


<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG ?>" ENCTYPE="multipart/form-data">

<input type="hidden" name="STEP" value="<?echo $STEP + 1;?>">
<?=bitrix_sessid_post()?>
<?
if ($STEP > 1)
{
	?><input type="hidden" name="COURSE_ID" value="<?echo $COURSE_ID ?>"><?
}
?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("LEARNING_ADMIN_TAB1"), "TITLE" => GetMessage("LEARNING_ADMIN_TAB1_EX")),
		array("DIV" => "edit2", "TAB" => GetMessage("LEARNING_ADMIN_TAB2"),  "TITLE" => GetMessage("LEARNING_ADMIN_TAB2_EX")),
		array("DIV" => "edit3", "TAB" => GetMessage("LEARNING_ADMIN_TAB3"), "TITLE" => GetMessage("LEARNING_ADMIN_TAB3_EX"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();

if ($STEP < 2)
{
	?>
	<tr>
		<td><?echo GetMessage("LEARNING_COURSES") ?>:</td>
		<td>
			<select name="COURSE_ID" style="width:300px;">
				<?
				$course = CCourse::GetList(array("SORT" => "ASC"), array("MIN_PERMISSION" => "W"));
				while ($course->ExtractFields("f_"))
				{
					?><option value="<?echo $f_ID ?>" <?if (IntVal($f_ID)==$COURSE_ID) echo "selected";?>><?echo $f_NAME ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();

if ($STEP == 2)
{
	?>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("LEARNING_DATA_FILE_NAME") ?></td>
	</tr>
	<tr>
		<td valign="top"><?echo GetMessage("LEARNING_DATA_FILE_NAME1") ?>:<br><small><?echo GetMessage("LEARNING_DATA_FILE_NAME1_T") ?></small></td>
		<td valign="top">
			<input type="text" name="DATA_FILE_NAME" size="40" value="<?echo (strlen($DATA_FILE_NAME)>0)?htmlspecialchars($DATA_FILE_NAME):"/".COption::GetOptionString("main", "upload_dir", "upload")."/package".$COURSE_ID.".tar.gz"?>"><br>
			<small><?echo GetMessage("LEARNING_DATA_FILE_NAME1_DESC") ?></small>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();

if ($STEP > 2)
{

	?>
	<tr>
		<td colspan="2"><b><?echo GetMessage("LEARNING_SUCCESS") ?></b></td>
	</tr>
	<tr>
		<td colspan="2">
			<?echo str_replace("%DATA_URL%", "<a href=\"".htmlspecialchars($DATA_FILE_NAME)."\" target=\"_blank\">".htmlspecialchars($DATA_FILE_NAME)."</a>", GetMessage("LEARNING_SU_ALL1")) ?>
		</td>
	</tr>
	<?
}
$tabControl->EndTab();
?>

<?
$tabControl->Buttons();
?>

<?if ($STEP < 3):?>
	<?if ($STEP > 1):?>
		<input type="submit" name="backButton" value="&lt;&lt; <?echo GetMessage("LEARNING_BACK") ?>">
	<?endif?>
	<input type="submit" value="<?echo ($STEP==2)?GetMessage("LEARNING_NEXT_STEP_F"):GetMessage("LEARNING_NEXT_STEP") ?> &gt;&gt;" name="submit_btn">
<?else:?>
	<input type="submit" name="backButton2" value="&lt;&lt; <?echo GetMessage("LEARNING_2_1_STEP") ?>">
<?endif;?>

<?
$tabControl->End();
?>

</form>

<script language="JavaScript">
<!--
<?if ($STEP < 2):?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
<?elseif ($STEP == 2):?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit3");
<?elseif ($STEP > 2):?>
tabControl.SelectTab("edit3");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");
<?endif;?>
//-->
</script>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>