<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

//Functions
function BXDeleteFromSystem($absoluteFilePath, $path, $site)
{
	@chmod($absoluteFilePath, BX_FILE_PERMISSIONS);

	if (COption::GetOptionInt("main", "disk_space") > 0)
	{
		$file_size = @filesize($absoluteFilePath);
		$quota = new CDiskQuota();
		$quota->UpdateDiskQuota("file", $file_size, "delete");
	}

	$sucess = @unlink($absoluteFilePath);

	if (!$sucess)
		return false;

	$GLOBALS["APPLICATION"]->RemoveFileAccessPermission(Array($site, $path));

	if (CModule::IncludeModule("search"))
		CSearch::DeleteIndex("main", $site."|".$path);

	//Delete from rewrite rule
	CUrlRewriter::Delete(Array("SITE_ID" => $site, "PATH" => $path));

	return true;
}

function BXDeleteFromMenu($documentRoot, $path, $site)
{
	if (!CModule::IncludeModule("fileman"))
		return false;

	if (!$GLOBALS["USER"]->CanDoOperation("fileman_edit_menu_elements") || !$GLOBALS["USER"]->CanDoOperation("fileman_edit_existent_files"))
		return false;

	$arMenuTypes = GetMenuTypes($site);
	if (empty($arMenuTypes))
		return false;

	$currentPath = $path;
	$result = array();

	while (true)
	{
		$currentPath = rtrim($currentPath, "/");

		if (strlen($currentPath) <= 0)
		{
			$currentPath = "/";
			$slash = "";
		}
		else
		{
			//Find parent folder
			$position = strrpos($currentPath, "/");
			if ($position === false)
				break;
			$currentPath = substr($currentPath, 0, $position);
			$slash = "/";
		}

		foreach ($arMenuTypes as $menuType => $menuDesc)
		{
			$menuFile = $currentPath.$slash.".".$menuType.".menu.php";

			if (file_exists($documentRoot.$menuFile) && $GLOBALS["USER"]->CanDoFileOperation("fm_edit_existent_file", Array($site, $menuFile)))
			{
				$arFound = BXDeleteFromMenuFile($menuFile, $documentRoot, $site, $path);
				if ($arFound)
					$result[] = $arFound;
			}
		}

		if (strlen($currentPath)<=0)
			break;
	}

	return $result;
}

function BXDeleteFromMenuFile($menuFile, $documentRoot, $site, $path)
{
	$aMenuLinks = Array();

	$arMenu = CFileman::GetMenuArray($documentRoot.$menuFile);
	if (empty($arMenu["aMenuLinks"]))
		return false;

	$arFound = false;
	foreach ($arMenu["aMenuLinks"] as $menuIndex => $arItem)
	{
		if (!isset($arItem[1]))
			continue;

		$menuLink = $arItem[1];
		$position = strpos($menuLink, "?");
		if ($position !== false)
			$menuLink = substr($menuLink, 0, $position);

		if ($menuLink != "/")
			$menuLink = rtrim($menuLink, "/");

		$filename = basename($path);
		$dirName = str_replace("\\", "/", dirname($path));

		if ($menuLink == $path || ($filename == "index.php" && $menuLink == $dirName))
		{
			$arFound = array(
				'menuFile' => $menuFile,
				'menuIndex' => $menuIndex,
				'menuItem' => $arItem
			);
			unset($arMenu["aMenuLinks"][$menuIndex]);
		}
	}

	if ($arFound)
		CFileMan::SaveMenu(Array($site, $menuFile), $arMenu["aMenuLinks"], $arMenu["sMenuTemplate"]);

	return $arFound;
}

IncludeModuleLangFile(__FILE__);

$popupWindow = new CJSPopup(GetMessage("PAGE_DELETE_WINDOW_TITLE"), array("SUFFIX"=>($_GET['subdialog'] == 'Y'? 'subdialog':'')));

if (IsModuleInstalled("fileman"))
{
	if (!$USER->CanDoOperation('fileman_admin_files'))
		$popupWindow->ShowError(GetMessage("PAGE_DELETE_ACCESS_DENIED"));
}

//Page path
$path = "/";
if (isset($_REQUEST["path"]) && strlen($_REQUEST["path"]) > 0)
{
	$path = $_REQUEST["path"];
	$path = Rel2Abs("/", $path);
}

//Lang
if (!isset($_REQUEST["lang"]) || strlen($_REQUEST["lang"]) <= 0)
	$lang = LANGUAGE_ID;

//BackUrl
$back_url = (isset($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : "");
if ($back_url == "")
	$back_url = (basename($path) == "index.php" ? "/" : str_replace("\\", "/", dirname($path)));

//Site ID
$site = SITE_ID;
if (isset($_REQUEST["site"]) && strlen($_REQUEST["site"]) > 0)
{
	$obSite = CSite::GetByID($_REQUEST["site"]);
	if ($arSite = $obSite->Fetch())
		$site = $_REQUEST["site"];
}

$documentRoot = CSite::GetSiteDocRoot($site);
$absoluteFilePath = $documentRoot.$path;

//Check permissions
if (!is_file($absoluteFilePath) || preg_match("~\/\.access\.php$~i", $path))
	$popupWindow->ShowError(GetMessage("PAGE_DELETE_FILE_NOT_FOUND")." (".htmlspecialchars($path).")");
elseif (!$USER->CanDoFileOperation('fm_delete_file',Array($site, $path)))
	$popupWindow->ShowError(GetMessage("PAGE_DELETE_ACCESS_DENIED"));

//Check post values
$strWarning = "";
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$deleteFromMenu = (isset($_REQUEST["delete_from_menu"]) && $_REQUEST["delete_from_menu"] == "Y");
	if (!check_bitrix_sessid())
		$strWarning = GetMessage("MAIN_SESSION_EXPIRED");
}
else
{
	if (basename($path) == "index.php")
		$strWarning = GetMessage("PAGE_DELETE_INDEX_WARNING");
	$deleteFromMenu = true;
}

//Delete File
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["save"]) && $strWarning == "")
{
	CUtil::JSPostUnescape();
	CModule::IncludeModule("fileman");

	$arUndoParams = array(
		'module' => 'fileman',
		'undoType' => 'delete_file',
		'undoHandler' => 'CFileman::UndoFileDelete',
		'arContent' => array(
			'path' => $path,
			'content' => $APPLICATION->GetFileContent($absoluteFilePath),
			'site' => $site,
			'perm' => CFileMan::FetchFileAccessPerm(Array($site, $path)),
			'SEF' => CUrlRewriter::GetList(array("PATH" => $path))
		)
	);

	$success = BXDeleteFromSystem($absoluteFilePath, $path, $site);
	if ($success)
	{
		if ($deleteFromMenu)
			$arUndoParams['arContent']['menu'] = BXDeleteFromMenu($documentRoot, $path, $site);

		if($_GET['subdialog'] == 'Y')
			echo "<script>structReload('".urlencode($_REQUEST["path"])."');</script>";

		$ID = CUndo::Add($arUndoParams);

		CUndo::ShowUndoMessage($ID);

		//CUndo::Escape($ID);

		$popupWindow->Close($bReload=($_GET['subdialog'] <> 'Y'), $back_url);
	}
	else
	{
		$strWarning = GetMessage("PAGE_DELETE_ERROR_OCCURED");
	}
}

//HTML Output
$popupWindow->ShowTitlebar(GetMessage("PAGE_DELETE_WINDOW_TITLE"));
$popupWindow->StartDescription("bx-delete-page");
?>
<p><?=str_replace("#FILENAME#", htmlspecialchars($path), GetMessage("PAGE_DELETE_CONFIRM_TEXT"))?></p>
<?
$popupWindow->EndDescription("bx-delete-page");
$popupWindow->StartContent();
if (isset($strWarning) && $strWarning != "")
	$popupWindow->ShowValidationError($strWarning);
?>
<?if (IsModuleInstalled("fileman")):?>
	<input type="checkbox" name="delete_from_menu" value="Y" id="bx_delete_from_menu" <?=($deleteFromMenu ? "checked" : "")?>> <label for="bx_delete_from_menu"><?=GetMessage("PAGE_DELETE_FROM_MENU")?></label>
<?endif?>


<?$popupWindow->StartButtons();?>

<input name="btn_popup_save" type="button" value="<?=GetMessage("PAGE_DELETE_BUTTON_YES")?>" title="<?=GetMessage("PAGE_DELETE_BUTTON_YES")?>" onclick="BXDeletePage();"/>
&nbsp;&nbsp;&nbsp;<input name="btn_popup_close" type="button" value="<?=GetMessage("PAGE_DELETE_BUTTON_NO")?>" onclick="<?=$popupWindow->jsPopup?>.CloseDialog()" title="<?=GetMessage("PAGE_DELETE_BUTTON_NO")?>" />

<?$popupWindow->EndButtons();?>

<script>
window.BXDeletePage = function()
{
	var params = 'save=Y&<?=bitrix_sessid_get()?>';
	var deleteFromMenu = document.getElementById("bx_delete_from_menu");
	if (deleteFromMenu)
		params += "&delete_from_menu=" + (deleteFromMenu.checked ? "Y" : "N");

	<?=$popupWindow->jsPopup?>.PostParameters(params);
}
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>