<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/admin/task_description.php");
IncludeModuleLangFile(__FILE__);

$popupWindow = new CJSPopup('', array("SUFFIX"=>($_GET['subdialog'] == 'Y'? 'subdialog':'')));

if (IsModuleInstalled("fileman"))
{
	if (!$USER->CanDoOperation('fileman_edit_existent_folders') && !$USER->CanDoOperation('fileman_admin_folders'))
		$popupWindow->ShowError(GetMessage("FOLDER_EDIT_ACCESS_DENIED"));
}

//Folder path
$path = "/";
if (isset($_REQUEST["path"]) && strlen($_REQUEST["path"]) > 0)
{
	$path = $_REQUEST["path"];
	$path = Rel2Abs("/", $path);
}

//Site ID
$site = SITE_ID;
if (isset($_REQUEST["site"]) && strlen($_REQUEST["site"]) > 0)
{
	$obSite = CSite::GetByID($_REQUEST["site"]);
	if ($arSite = $obSite->Fetch())
		$site = $_REQUEST["site"];
}

//Document Root
$documentRoot = CSite::GetSiteDocRoot($site);

//Check path permissions
if (!file_exists($documentRoot.$path))
	$popupWindow->ShowError(GetMessage("ACCESS_EDIT_FILE_NOT_FOUND")." (".htmlspecialchars($path).")");
elseif (!$USER->CanDoFileOperation('fm_edit_existent_folder', Array($site, $path)))
	$popupWindow->ShowError(GetMessage("FOLDER_EDIT_ACCESS_DENIED"));
elseif (!$USER->CanDoFileOperation('fm_edit_permission', Array($site, $path)))
	$popupWindow->ShowError(GetMessage("EDIT_ACCESS_TO_DENIED")." \"".htmlspecialchars($path)."\"");

//Lang
if (!isset($_REQUEST["lang"]) || strlen($_REQUEST["lang"]) <= 0)
	$lang = LANGUAGE_ID;

//BackUrl
$back_url = (isset($_REQUEST["back_url"]) ? $_REQUEST["back_url"] : "");

//Is a folder?
$isFolder = is_dir($documentRoot.$path);

//Get only used user group from .access.php file
$arUserGroupsID = Array();
$assignFileName = "";
$assignFolderName = "";

$currentPath = $path;
while(true)
{
	//Cut / from the end
	$currentPath = rtrim($currentPath, "/");

	if (strlen($currentPath) <= 0)
	{
		$accessFile = "/.access.php";
		$name = "/";
	}
	else
	{
		//Find file or folder name
		$position = strrpos($currentPath, "/");
		if ($position === false)
			break;

		$name = substr($currentPath, $position+1);
		$name = rtrim($name, "\0.\\/+ "); //security fix: under Windows "my." == "my"

		//Find parent folder
		$currentPath = substr($currentPath, 0, $position + 1);
		$accessFile = $currentPath.".access.php";
	}

	$PERM = Array();
	if (file_exists($documentRoot.$accessFile))
		include($documentRoot.$accessFile);

	if ($assignFileName == "")
	{
		$assignFileName = $name;
		$assignFolderName = ($name == "/" ? "/" : $currentPath);
	}

	if (isset($PERM[$name]) && is_array($PERM[$name]))
		$arUserGroupsID = array_merge($arUserGroupsID, array_keys($PERM[$name]));

	if (strlen($currentPath)<=0)
		break;
}

$arUserGroupsID = array_unique($arUserGroupsID);

// Get subordinate
/*
if ($USER->CanDoOperation('edit_subordinate_users') && !$USER->CanDoOperation('edit_all_users'))
{
	$arSubordGroups = Array();
	$arGroups = $USER->GetUserGroupArray();
	foreach ($arGroups as $groupID)
		$arSubordGroups = array_merge($arSubordGroups, CGroup::GetSubordinateGroups($groupID));
	$arSubordGroups = array_unique($arSubordGroups);
}*/

//Get all tasks
$arPermTypes = Array();
$obTask = CTask::GetList(Array("LETTER" => "ASC"), Array("MODULE_ID" => "main", "BINDING" => "file"));
while($arTask = $obTask->Fetch())
	$arPermTypes[$arTask["ID"]] = CTask::GetLangTitle($arTask["NAME"]);

//Current file/folder permissions
$currentPermission = Array();
if(file_exists($documentRoot.$assignFolderName.".access.php"))
{
	$PERM = Array();
	include($documentRoot.$assignFolderName.".access.php");
	$currentPermission = $PERM;
}

function _GetAccessEditGroups($arPath)
{
	$arAccessEditGroups = Array();
	$arCurrentUserGroup = $GLOBALS["USER"]->GetUserGroupArray();

	if (in_array(1, $arCurrentUserGroup))
		return $arAccessEditGroups;

	foreach ($arCurrentUserGroup as $groupID)
	{
		$arTask = $GLOBALS["APPLICATION"]->GetFileAccessPermission($arPath, Array($groupID), true);

		$arOperations = Array();
		foreach ($arTask as $taskID)
			$arOperations += CTask::GetOperations($taskID, true);

		if (in_array("fm_edit_existent_folder", $arOperations) && in_array("fm_edit_permission", $arOperations))
			$arAccessEditGroups[] = $groupID;
	}

	return $arAccessEditGroups;
}

//Save permissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && !check_bitrix_sessid())
{
	CUtil::JSPostUnescape();
	$strWarning = GetMessage("MAIN_SESSION_EXPIRED");
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["save"]))
{
	CUtil::JSPostUnescape();
	$strWarning = "";
	$arSavePermission = Array();

	if($_POST["REMOVE_PERMISSIONS"] == "Y")
	{
		if($path != "/")
		{
			$APPLICATION->RemoveFileAccessPermission(Array($site, $path));
	
			if ($e = $APPLICATION->GetException())
				$strWarning = $e->msg;
		}
	}
	else
	{
		if (isset($_POST["PERMISSION"]) && is_array($_POST["PERMISSION"]))
		{
			if (isset($currentPermission[$assignFileName]) && is_array($currentPermission[$assignFileName]))
				$arSavePermission = $currentPermission[$assignFileName];
	
			$arCurrentUserGroup = $USER->GetUserGroupArray();
			$isAdmin = in_array(1, $arCurrentUserGroup);
	
			if (!$isAdmin)
			{
				$arAccessEditGroups = _GetAccessEditGroups(Array($site, $path)); //Get groups who can edit access
				$accessEditNum = count($arAccessEditGroups);
			}
	
			foreach ($_POST["PERMISSION"] as $groupID => $taskID)
			{
				if ($groupID !== "*")
				{
					$groupID = intval($groupID);
					if ($groupID <= 0)
						continue;
				}
				elseif (!$isAdmin)
					continue;
	
				//if ($arSubordGroups && !in_array($groupID, $arSubordGroups))
					//continue;
	
				//If set permission for group who can edit access
				if (!$isAdmin)
				{
					$arOperations = CTask::GetOperations($taskID, true);
	
					if (in_array("fm_edit_existent_folder", $arOperations) && in_array("fm_edit_permission", $arOperations) && in_array($groupID, $arCurrentUserGroup))
						$accessEditNum++;
					elseif (in_array($groupID, $arAccessEditGroups))
						$accessEditNum--;
				}
	
				// if not set task - delete permission
				$taskID = intval($taskID);
				if ($taskID <= 0)
				{
					unset($arSavePermission[$groupID]);
					continue;
				}
	
				$obTask = CTask::GetById($taskID);
				if ( ($arTask = $obTask->Fetch()) && $arTask["LETTER"] && $arTask["SYS"] == "Y")
					$permLetter = $arTask["LETTER"];
				else
					$permLetter = "T_".$taskID;
	
				$arSavePermission[$groupID] = $permLetter;
			}
		}
	
		if (isset($accessEditNum) && $accessEditNum <= 0)
		{
			$strGroups = "";
			foreach ($arAccessEditGroups as $groupID)
			{
				$obGroup = CGroup::GetByID($groupID);
				if ($arGroup = $obGroup->Fetch())
					$strGroups .= ($strGroups != "" ? ", " : "").htmlspecialcharsEx($arGroup["NAME"]);
			}
			$strWarning .= str_replace("#GROUPS#", $strGroups, GetMessage("EDIT_ACCESS_OWN_CHANGE_RESTRICT"));
		}
		else
		{
			$APPLICATION->SetFileAccessPermission(Array($site, $path), $arSavePermission);
	
			if ($e = $APPLICATION->GetException())
				$strWarning = $e->msg;
		}
	}

	//Close window
	if ($strWarning == "")
	{
		$popupWindow->Close($bReload=($_GET['subdialog'] <> 'Y'), $back_url);
		die();
	}
}

//HTML output
if ($isFolder)
	$popupWindow->ShowTitlebar(GetMessage("EDIT_ACCESS_TO_FOLDER"));
else
	$popupWindow->ShowTitlebar(GetMessage("EDIT_ACCESS_TO_FILE"));

$popupWindow->StartDescription($isFolder ? "bx-access-folder" : "bx-access-page");

if (isset($strWarning) && $strWarning != "")
	$popupWindow->ShowValidationError($strWarning);
?>

<p><b><?=($isFolder ? GetMessage("EDIT_ACCESS_TO_FOLDER") : GetMessage("EDIT_ACCESS_TO_FILE"))?></b> <?=htmlspecialchars($path);?></p>

<?
$popupWindow->EndDescription();
$popupWindow->StartContent();
?>

<table class="bx-width100" id="bx_permission_table">
	<tr>
		<td width="45%"><b><?=GetMessage("EDIT_ACCESS_USER_GROUP")?></b></td>
		<td><b><?=GetMessage("EDIT_ACCESS_PERMISSION")?></b> </td>
	</tr>
	<tr class="empty">
		<td colspan="2"></td>
	</tr>

<?
//Javascript variables
$jsTaskArray = "window.BXTaskArray = {'0':'".CUtil::JSEscape(GetMessage("EDIT_ACCESS_SET_INHERIT"))."'";
foreach ($arPermTypes as $taskID => $taskTitle)
	$jsTaskArray .= ",'".$taskID."':'".CUtil::JSEscape($taskTitle)."'";
$jsTaskArray .= "};";

$jsUserGroupArray = "window.BXUserGroupArray = {'0':'".CUtil::JSEscape(GetMessage("EDIT_ACCESS_SELECT_GROUP"))."'";
$jsInheritPerm = "";
$addGroupLinkExists = false;


$arCurrentUserGroup = $USER->GetUserGroupArray();

//Get All Group
$obGroups = CGroup::GetList($order="sort", $by="asc", Array("ACTIVE" => "Y", "ADMIN" => "N"));
$arGroup = Array("ID" => "*", "NAME" => GetMessage("EDIT_ACCESS_ALL_GROUPS")); //

$jsInheritPermID = "var jsInheritPermIDs = [";

$bWasCurrentPerm = false;

do
{
	//Restore post value if error occured
	$errorOccured = ($strWarning != "" && isset($_POST["PERMISSION"]) && is_array($_POST["PERMISSION"]) && array_key_exists($arGroup["ID"], $_POST["PERMISSION"]));

	if ($arGroup["ID"] != "*")
	{
		//if ($arSubordGroups && !in_array($arGroup["ID"], $arSubordGroups))
			//continue;

		//Skip group
		if (!in_array($arGroup["ID"], $arUserGroupsID) && !$errorOccured)
		{
			$addGroupLinkExists = true;
			$jsUserGroupArray .= ",'".$arGroup["ID"]."':'".CUtil::JSEscape($arGroup["NAME"])."'";
			continue;
		}
	}

	//Inherit Task
	list ($inheritTaskID) = $APPLICATION->GetFileAccessPermission(Array($site, $assignFolderName), Array($arGroup["ID"]), true);

	if (!array_key_exists($inheritTaskID, $arPermTypes))
	{
		if ($arGroup["ID"] == "*")
			$inheritTaskID = CTask::GetIdByLetter("D", "main", "file");
		else
			continue;
	}

	//Current permission
	$currentPerm = false;

	if ($errorOccured)
	{
		//Restore post value if error occured
		$currentPerm = intval($_POST["PERMISSION"][$arGroup["ID"]]);
	}
	elseif (isset($currentPermission[$assignFileName]) && isset($currentPermission[$assignFileName][$arGroup["ID"]]))
	{
		$permLetter = $currentPermission[$assignFileName][$arGroup["ID"]];

		if (substr($permLetter, 0, 2) == "T_")
		{
			$currentPerm = intval(substr($permLetter, 2));
			if (!array_key_exists($currentPerm, $arPermTypes))
				$currentPerm = false;
		}
		else
			$currentPerm = CTask::GetIdByLetter($permLetter, "main", "file");
	}

	if ($currentPerm === false && $arGroup["ID"] == "*" && $path == "/")
		$currentPerm = $inheritTaskID;

	if ($arGroup["ID"] == "*")
		$jsInheritPerm = $inheritTaskID;

	$permissionID = intval($arGroup["ID"])."_".intval($currentPerm)."_".intval($inheritTaskID);?>

	<tr>
		<td><?=htmlspecialcharsEx($arGroup["NAME"])?></td>
		<td>
<?
//removed next "if" because of subordinated groups was already commented
?>
			<?if (false && $arGroup["ID"] == "*" && !in_array(1, $arCurrentUserGroup)): //If not admin disable all group edit?>

				<?=($currentPerm === false ? GetMessage("EDIT_ACCESS_SET_INHERITED")." &quot;".$arPermTypes[$inheritTaskID]."&quot;" : $arPermTypes[$currentPerm])?>

			<?elseif ($currentPerm === false && $path != "/"): //Inherit permission
				$jsInheritPermID .= ",'".$permissionID."'";
			?>

				<div id="bx_permission_view_<?=$permissionID?>" style="overflow:hidden;padding:2px 12px 2px 2px; border:1px solid white; width:90%; cursor:text; box-sizing:border-box; -moz-box-sizing:border-box;background-color:transparent; background-position:right; background-repeat:no-repeat;" onclick="BXEditPermission('<?=$permissionID?>')" onmouseover="this.style.borderColor = '#434B50 #ADC0CF #ADC0CF #434B50'" onmouseout="this.style.borderColor = 'white'" class="edit-field">
					<?=GetMessage("EDIT_ACCESS_SET_INHERITED")." &quot;".htmlspecialcharsEx($arPermTypes[$inheritTaskID])."&quot;"?>
				</div>

				<div id="bx_permission_edit_<?=$permissionID?>" style="display:none;"></div>

			<?
			else: //Current permission
				$bWasCurrentPerm = true;
			?>

				<select name="PERMISSION[<?=$arGroup["ID"]?>]" style="width:90%;" id="bx_task_list_<?=$permissionID?>">

					<?if ($path == "/"):?>
						<option value="0"><?=GetMessage("EDIT_ACCESS_NOT_SET")?></option>
					<?else:?>
						<option value="0"><?=GetMessage("EDIT_ACCESS_SET_INHERIT")." &quot;".htmlspecialcharsEx($arPermTypes[$inheritTaskID])."&quot;"?></option>
					<?endif?>

					<?foreach ($arPermTypes as $taskID => $taskTitle):?>
						<option value="<?=$taskID?>"<?if ($currentPerm == $taskID):?> selected="selected"<?endif?>><?=htmlspecialcharsEx($taskTitle);?></option>
					<?endforeach?>

				</select>

			<?endif?>
		</td>
	</tr>

<?
} while ($arGroup = $obGroups->Fetch());

$jsUserGroupArray .= "};";
$jsInheritPermID .= "];";
?>

</table>

<?if ($addGroupLinkExists):?>
	<p><a href="" onclick="return BXAddNewPermission();"><?=GetMessage("EDIT_ACCESS_ADD_PERMISSION")?></a></p>
<?endif?>

<?if($bWasCurrentPerm && $path != "/"):?>
	<p><b><a href="javascript:void(0)" onclick="BXClearPermission()"><?=($isFolder? GetMessage("EDIT_ACCESS_REMOVE_PERM"):GetMessage("EDIT_ACCESS_REMOVE_PERM_FILE"))?></a></b></p>
	<input type="hidden" name="REMOVE_PERMISSIONS" id="REMOVE_PERMISSIONS" value="">
<?endif?>

<input type="hidden" name="save" value="Y" />
<?
$popupWindow->EndContent();
$popupWindow->ShowStandardButtons();
?>

<script>
<?=$jsTaskArray?>
<?=$jsUserGroupArray?>

window.BXAddNewPermission = function()
{
	var table = document.getElementById("bx_permission_table");

	//Create new row
	var tableRow = table.insertRow(table.rows.length);

	var groupTD = tableRow.insertCell(0);
	var currentTD = tableRow.insertCell(1);

	var permissionID = Math.round(Math.random() * 100000);

	//Insert Task Select
	var taskSelect = BXCreateTaskList(permissionID, 0, 0, 0);
	taskSelect.onblur = "";
	currentTD.appendChild(taskSelect);


	//Generate user group select
	var select = document.createElement("SELECT");
	select.style.width = "90%";
	//select.style.padding = "2px 0";
	select.onchange = function() {BXOnSelectUserGroup(select, permissionID);};

	var selectDocument = select.ownerDocument; //For IE 5.0
	if (!selectDocument)
		selectDocument = select.document;

	for (var groupID in BXUserGroupArray)
	{
		var option = selectDocument.createElement("OPTION");
		option.text = BXUserGroupArray[groupID];
		option.value = groupID;
		select.options.add(option);
	}

	groupTD.appendChild(select);
	select.focus();

	return false;
}

window.BXOnSelectUserGroup = function(select, permissionID)
{
	var selectUserGroup = select.options[select.selectedIndex].value;
	var taskSelect = document.getElementById("bx_task_list_" + permissionID);
	taskSelect.name = "PERMISSION["+selectUserGroup+"]";
}

window.BXBlurEditPermission = function(select, permissionID)
{
	var viewPermission = document.getElementById("bx_permission_view_" + permissionID);
	var setPermission = select.options[select.selectedIndex].value;

	var arPermID = permissionID.split("_");
	var userGroupID = arPermID[0];
	var currentPermission = arPermID[1];

	if (setPermission == currentPermission)
	{
		var editPermission = document.getElementById("bx_permission_edit_" + permissionID);

		viewPermission.style.display = "block";
		editPermission.style.display = "none";

		while (editPermission.firstChild)
			editPermission.removeChild(editPermission.firstChild);
	}
}

window.BXCreateTaskList = function(permissionID, currentPermission, inheritPermission, userGroupID)
{
	var select = document.createElement("SELECT");
	select.name = "PERMISSION["+userGroupID+"]";
	select.style.width = "90%";
	//select.style.margin = "1px 0";
	select.onblur = function () {BXBlurEditPermission(select,permissionID)};
	select.id = "bx_task_list_" + permissionID;

	//For IE 5.0
	var selectDocument = select.ownerDocument;
	if (!selectDocument)
		selectDocument = select.document;

	var selectedIndex = 0;

	<?if ($path == "/"):?>
		BXTaskArray["0"] = "<?=CUtil::JSEscape(GetMessage("EDIT_ACCESS_NOT_SET"))?>";
	<?else:?>
		BXTaskArray["0"] = "<?=CUtil::JSEscape(GetMessage("EDIT_ACCESS_SET_INHERIT"))?>" + " \"" + BXTaskArray[(inheritPermission == 0 ? <?=intval($jsInheritPerm)?> : inheritPermission)] + "\"";
	<?endif?>

	for (var taskID in BXTaskArray)
	{
		var option = selectDocument.createElement("OPTION");
		option.text = BXTaskArray[taskID];
		option.value = taskID;

		select.options.add(option);

		if (taskID == currentPermission)
			selectedIndex = select.options.length - 1;
	}

	select.selectedIndex = selectedIndex;

	return select;
}

window.BXEditPermission = function(permissionID)
{
	if (document.getElementById("bx_task_list_" + permissionID))
		return;

	var arPermID = permissionID.split("_"); //Format permissionID: UserGroup_CurrentPermission_InheritPermission

	var userGroupID = arPermID[0];
	var currentPermission = arPermID[1];
	var inheritPermission = arPermID[2];

	if (userGroupID == "0")
		userGroupID = "*";

	var editPermission = document.getElementById("bx_permission_edit_" + permissionID);
	var viewPermission = document.getElementById("bx_permission_view_" + permissionID);

	editPermission.style.display = "block";
	viewPermission.style.display = "none";

	var taskSelect = BXCreateTaskList(permissionID, currentPermission, inheritPermission, userGroupID);

	editPermission.appendChild(taskSelect);
	taskSelect.focus();
}


window.BXCreateAccessHint = function()
{
	var table = document.getElementById("bx_permission_table");
	var tableRow = table.rows[0];

	var groupTD = tableRow.cells[0];
	var currentTD = tableRow.cells[1];

	oBXHint = new BXHint("<?=CUtil::JSEscape(GetMessage("EDIT_ACCESS_PERMISSION_INFO"))?>");
	currentTD.appendChild(oBXHint.oIcon);


	<?=$jsInheritPermID?>
	
	for (var index = 0; index < jsInheritPermIDs.length; index++)
		oBXHint = new BXHint("<?=CUtil::JSEscape(GetMessage("EDIT_ACCESS_SET_PERMISSION"))?>", document.getElementById("bx_permission_view_"+ jsInheritPermIDs[index]), {"width":200});
}

window.BXClearPermission = function()
{
	if(confirm('<?=CUtil::JSEscape(GetMessage("EDIT_ACCESS_REMOVE_PERM_CONF"))?>'))
	{
		BX("REMOVE_PERMISSIONS").value = "Y";
		BX.WindowManager.Get().PostParameters();
	}
}

window.BXCreateAccessHint();
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>