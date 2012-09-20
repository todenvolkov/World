<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

// lpa is not allowed!
if (!($USER->CanDoOperation('edit_php')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CUtil::JSPostUnescape();

$obJSPopup = new CJSPopup();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

IncludeModuleLangFile(__FILE__);

$strWarning = "";

while (($l=strlen($path))>0 && $path[$l-1]=="/")
	$path = substr($path, 0, $l-1);

$bVarsFromForm = false;
if (strlen($filename) > 0 && ($mess = CFileMan::CheckFileName($filename)) !== true)
{
	$filename2 = $filename;
	$filename = '';
	$strWarning = $mess;
	$bVarsFromForm = true;
}

$path = Rel2Abs("/", $path);
$site = CFileMan::__CheckSite($site);
if(!$site)
	$site = CSite::GetSiteByFullPath($_SERVER["DOCUMENT_ROOT"].$path);

$DOC_ROOT = CSite::GetSiteDocRoot($site);
$abs_path = $DOC_ROOT.$path;

if(strlen($new)>0 && strlen($filename)>0)
	$abs_path .= "/".$filename; 
	
if((strlen($new) <= 0 || strlen($filename)<=0) && !file_exists($abs_path))
{
	$p = strrpos($path, "/");
	if($p!==false)
	{
		$new = "Y";
		$filename = substr($path, $p+1);
		$path = substr($path, 0, $p);
	}
}

if(strlen($new) > 0 && strlen($filename) > 0 && file_exists($abs_path))		// если мы хотим создать новый файл, но уже такой есть - ругаемся
{
	$strWarning = GetMessage("FILEMAN_FILEEDIT_FILE_EXISTS")." ";
	$bEdit = false;
	$bVarsFromForm = true;
}
elseif(strlen($new) > 0)
{
	if (strlen($filename) < 0)
		$strWarning = GetMessage("FILEMAN_FILEEDIT_FILENAME_EMPTY")." ";
	$bEdit = false;
}
else
{
	if(!is_file($abs_path))
		$strWarning = GetMessage("FILEMAN_FILEEDIT_FOLDER_EXISTS")." ";
	else
		$bEdit = true;
}

if(strlen($strWarning)<=0)
{
	if($bEdit)
	{
		$filesrc_tmp = $APPLICATION->GetFileContent($abs_path);
	}
	else
	{
		$site_template = false;
		$rsSiteTemplates = CSite::GetTemplateList($site);
		while($arSiteTemplate = $rsSiteTemplates->Fetch())
		{
			if(strlen($arSiteTemplate["CONDITION"])<=0)
			{
				$site_template = $arSiteTemplate["TEMPLATE"];
				break;
			}
		}

		$arTemplates = CFileman::GetFileTemplates(LANGUAGE_ID, array($site_template));
		if(strlen($template)>0)
		{
			for ($i=0; $i<count($arTemplates); $i++)
			{
				if($arTemplates[$i]["file"] == $template)
				{
					$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[$i]["file"], LANGUAGE_ID, array($site_template));
					break;
				}
			}
		}
		else
			$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[0]["file"], LANGUAGE_ID, array($site_template));
	}

	if($REQUEST_METHOD=="POST" && strlen($save)>0)
	{
		if(!check_bitrix_sessid())
		{
			$strWarning = GetMessage("FILEMAN_SESSION_EXPIRED");
			$bVarsFromForm = true;
		}

		// lpa was denied earlier, so use file src as is
		$filesrc_for_save = $_POST['filesrc'];

		if(strlen($strWarning) <= 0)
		{
			if (!CFileMan::CheckOnAllowedComponents($filesrc_for_save))
			{
				$str_err = $APPLICATION->GetException();
				if($str_err && ($err = $str_err ->GetString()))
					$strWarning .= $err;
				$bVarsFromForm = true;
			}
		}

		if(strlen($strWarning) <= 0)
		{
			$arUndoParams = array(
				'module' => 'fileman',
				'undoType' => 'edit_file',
				'undoHandler' => 'CFileman::UndoEditFile',
				'arContent' => array(
					'absPath' => $abs_path,
					'content' => $APPLICATION->GetFileContent($abs_path)
				)
			);

			if(!$APPLICATION->SaveFileContent($abs_path, $filesrc_for_save))
			{
				if($str_err = $APPLICATION->GetException())
				{
					if ($str_err && ($err = $str_err->GetString()))
						$strWarning = $err;

					$bVarsFromForm = true;
				}

				if (empty($strWarning))
				{
					$strWarning = GetMessage("pub_src_edit_err");
				}
			}
			else
			{
				$bEdit = true;
				CUndo::ShowUndoMessage(CUndo::Add($arUndoParams));
			}

			if(strlen($strWarning)<=0)
			{
?>
<script type="text/javascript" bxrunfirst="true">
top.BX.showWait();
top.BX.reload('<?=CUtil::JSEscape($_REQUEST["back_url"])?>', true);
top.<?=$obJSPopup->jsPopup?>.Close();
</script>
<?
				die();
			}

			$filesrc_tmp = $filesrc_for_save;
		}
	}
}

if (strlen($strWarning) > 0)
	$obJSPopup->ShowValidationError($strWarning);

if(!$bVarsFromForm)
{
	if(!$bEdit && strlen($filename)<=0)
		$filename = "untitled.php";

	$filesrc = $filesrc_tmp;
}
else
	$filesrc = $_POST['filesrc'];


/*************************************************/

$obJSPopup->ShowTitlebar(($bEdit ? GetMessage("FILEMAN_FILEEDIT_PAGE_TITLE") : GetMessage("FILEMAN_NEWFILEEDIT_TITLE")).": ".htmlspecialchars($path));

$obJSPopup->StartDescription();

echo '<a href="/bitrix/admin/fileman_file_edit.php?path='.urlencode($path).'&amp;full_src=Y&amp;site='.$site.'&amp;lang='.LANGUAGE_ID.'&amp;back_url='.urlencode($_GET["back_url"]).(!$bEdit? '&amp;new=Y&amp;filename='.urlencode($filename).'&amp;template='.urlencode($template):'').($_REQUEST["templateID"]<>''? '&amp;templateID='.urlencode($_REQUEST["templateID"]):'').'" title="'.htmlspecialchars($path).'">'.GetMessage("public_file_edit_edit_cp").'</a>';

$obJSPopup->StartContent();
?>

<input type="hidden" name="site" value="<?= htmlspecialchars($site) ?>">
<input type="hidden" name="path" value="<?= htmlspecialchars($path) ?>">
<input type="hidden" name="save" value="Y">
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
<input type="hidden" name="save" value="Y">
<input type="hidden" name="template" value="<?echo htmlspecialchars($template)?>">
<input type="hidden" name="back_url" value="<?=htmlspecialchars($back_url)?>">
<input type="hidden" name="templateID" value="<?=htmlspecialchars($_REQUEST["templateID"])?>">
<?=bitrix_sessid_post()?>

<?if(!$bEdit):?>
<div id="bx_additional_params">
	<input type="hidden" name="new" value="y">
	<?echo GetMessage("FILEMAN_FILEEDIT_NAME")?><br>
	<?
	if (isset($filename2))
		$filename = $filename2;
	?>
	<input type="text" name="filename" style="width:100%" size="40" maxlength="255" value="<?echo htmlspecialchars($filename)?>"><br><br>
</div>
<?endif;?>

<textarea id="filesrc" name="filesrc" style="height: 99%; width: 100%;"><?echo htmlspecialchars($filesrc)?></textarea>

<script type="text/javascript">
var border = null, ta = null, wnd = BX.WindowManager.Get();

function TAResize(data)
{
	if (null == ta) ta = BX('filesrc');
	if (null == border) border = parseInt(BX.style(ta, 'border-left-width')) + parseInt(BX.style(ta, 'border-right-width'));
	if (isNaN(border)) border = 0;

	var add = BX('bx_additional_params');
	
	if (data.height) ta.style.height = (data.height - border - wnd.PARTS.HEAD.offsetHeight - (add ? add.offsetHeight : 0) - 35) + 'px';
	if (data.width) ta.style.width = (data.width - border - 10) + 'px';
}

BX.addCustomEvent(wnd, 'onWindowResizeExt', TAResize);
TAResize(wnd.GetInnerPos());
</script>
<?
$obJSPopup->StartButtons();
$obJSPopup->ShowStandardButtons(array('save', 'cancel'));
$obJSPopup->EndButtons();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>