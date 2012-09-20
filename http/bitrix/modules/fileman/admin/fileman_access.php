<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!($USER->CanDoOperation('fileman_edit_existent_folders') || $USER->CanDoOperation('fileman_admin_folders')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars("g_");

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

while(($l=strlen($path))>0 && $path[$l-1]=="/")
	$path = substr($path, 0, $l-1);

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');

$path = Rel2Abs("/", $path);
$arPath = Array($site, $path);
$strNotice = "";

$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");

$abs_path = $DOC_ROOT.$path;


$arPermTypes = Array();
$res = CTask::GetList(Array('LETTER' => 'asc'), Array('MODULE_ID' => 'main','BINDING' => 'file'));
while($arRes = $res->Fetch())
{
	$name = '';
	if ($arRes['SYS'])
		$name = GetMessage(strtoupper($arRes['NAME']));
	if (strlen($name) == 0)
		$name = $arRes['NAME'];

	$arPermTypes[$arRes['ID']] = Array(
		'title' => $name,
		'letter' => $arRes['LETTER']
	);
}
$arPermTypes['NOT_REF'] = Array(
	'title' => GetMessage("FILEMAN_FOLDER_ACCESS_INHERIT"),
	'letter' => 'N'
);

$strWarning = "";
$arFiles = Array();
if (count($files) > 0)
{
	for($i=0; $i<count($files); $i++)
	{
		if(!$USER->CanDoFileOperation('fm_edit_permission',Array($site, $path."/".$files[$i])))
			$strWarning .= GetMessage("FILEMAN_ACCESS_TO_DENIED")." \"".$files[$i]."\".\n";
		elseif($files[$i] != '.')
			$arFiles[] = $files[$i];
	}
}
else
{
	$arPDirs = array();
	$arPFiles = array();
	CFileMan::GetDirList(Array($site, $path), $arPDirs, $arPFiles, array("MIN_PERMISSION" => "X"), array(), "DF");

	foreach ($arPDirs as $dir)
	{
		if($USER->CanDoFileOperation('fm_edit_permission',Array($site, $dir['ABS_PATH'])))
			$arFiles[] = $dir["NAME"];
	}
	foreach ($arPFiles as $file)
	{
		if($USER->CanDoFileOperation('fm_edit_permission',Array($site, $file['ABS_PATH'])))
			$arFiles[] = $file["NAME"];
	}
}
$filesCount = count($arFiles);

function GetAccessArrTmp($path)
{
	global $DOC_ROOT;
	if(file_exists($DOC_ROOT.$path))
	{
		@include($DOC_ROOT.$path."/.access.php");
		return $PERM;
	}
	return Array();
}

// If user can manage only subordinate groups
if ($USER->CanDoOperation('edit_subordinate_users') && !$USER->CanDoOperation('edit_all_users'))
{
	$arSubordGroups = Array();
	$arGroups = explode(',', $USER->GetGroups());
	for ($i = 0,$l = count($arGroups);$i < $l;$i++)
		$arSubordGroups = array_merge($arSubordGroups,CGroup::GetSubordinateGroups($arGroups[$i]));
	$arSubordGroups = array_values(array_unique($arSubordGroups));
}

if($REQUEST_METHOD=="POST" && is_array($files) && count($files)>0 && strlen($saveperm)>0 && check_bitrix_sessid() && $USER->CanDoOperation('fileman_admin_folders'))
{
	$CUR_PERM = GetAccessArrTmp($path);
	$arPermissions=Array();
	$arNotSetPerm=Array();

	$db_groups = CGroup::GetList($order="sort", $by="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
	while($arGroup = $db_groups->Fetch())
	{
		if(isset($arSubordGroups) && !in_array($arGroup['ID'],$arSubordGroups))
		{
			$arNotSetPerm[] = $arGroup["ID"];
			continue;
		}
		else
		{
			$gperm = isset($_POST["g_".$arGroup["ID"]]) ? $_POST["g_".$arGroup["ID"]] : '';

			if ($gperm == '')
			{
				$arNotSetPerm[] = $arGroup["ID"];
				continue;
			}

			if ($gperm == 'NOT_REF')
				$gperm = '';
			if (intval($gperm) > 0)
			{
				$z = CTask::GetById($gperm);
				$r = $z->Fetch();
				if ($r && $r['LETTER'] && $r['SYS'] == 'Y')
					$gperm = $r['LETTER'];
				else
					$gperm = 'T_'.$gperm;
			}
			$arPermissions[$arGroup["ID"]] = $gperm;
		}
	}
	$gperm = $_POST['g_ALL'];

	if ($gperm == '')
		$arNotSetPerm[] = "*";
	else
	{
		if ($gperm == 'NOT_REF')
			$gperm = '';
		if (intval($gperm) > 0)
		{
			$z = CTask::GetById($gperm);
			$r = $z->Fetch();
			if ($r && $r['LETTER'] && $r['SYS'] == 'Y')
				$gperm = $r['LETTER'];
			else
				$gperm = 'T_'.$gperm;
		}
		$arPermissions["*"] = $gperm;
	}

	for($i = 0; $i < $filesCount; $i++)
	{
		$arPermissionsTmp = $arPermissions;
		for($j=0; $j<count($arNotSetPerm); $j++)
			$arPermissionsTmp[$arNotSetPerm[$j]] = $CUR_PERM[$arFiles[$i]][$arNotSetPerm[$j]];

		$APPLICATION->SetFileAccessPermission(Array($site, $path."/".$arFiles[$i]), $arPermissionsTmp);
	}

	if ($e = $APPLICATION->GetException())
		$strNotice = $e->msg;
	elseif(strlen($strWarning)<=0 && strlen($apply) <= 0)
		LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path));
}

foreach ($arParsedPath["AR_PATH"] as $chainLevel)
{
	$adminChain->AddItem(
		array(
			"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
			"LINK" => ((strlen($chainLevel["LINK"]) > 0) ? $chainLevel["LINK"] : ""),
		)
	);
}

$APPLICATION->SetTitle(GetMessage("FILEMAN_ACCESS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("FILEMAN_CMENU_CAT"),
		"LINK" => "fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path)
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<?CAdminMessage::ShowMessage($strNotice);?>
<?CAdminMessage::ShowMessage($strWarning);?>
<?if(strlen($strWarning) <= 0):?>
<script>
function Conf(ob)
{
	if(ob.selectedIndex!=0 && !confirm("<?= GetMessage("FILEMAN_ACCESS_DIFFERENT")?>"))
		ob.selectedIndex=0;
}
</script>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="faform">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="site" value="<?= htmlspecialchars($site) ?>">
<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
<input type="hidden" name="path" value="<?= htmlspecialchars($path) ?>">
<input type="hidden" name="saveperm" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<?=bitrix_sessid_post()?>
<?for($i = 0; $i < $filesCount; $i++):?>
	<input type="hidden" name="files[]" value="<?echo htmlspecialchars($arFiles[$i])?>">
<?endfor?>

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("FILEMAN_TAB"), "ICON" => "fileman", "TITLE" => GetMessage("FILEMAN_TAB_ALT"))
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
<?$tabControl->BeginNextTab();?>

<tr>
	<td colspan="2">
		<?
		echo GetMessage("FILEMAN_ACCESS_CHANGE_TO");
		if ($filesCount > 0)
		{
			echo GetMessage("FILEMAN_ACCESS_FOLDERS_FILES").'<br>';
			for($i = 0; $i < $filesCount; $i++)
				echo '&quot;'.$APPLICATION->UnJSEscape(htmlspecialchars($arFiles[$i])).'&quot;<br>';
		}
		else
		{
			echo GetMessage("FILEMAN_ACCESS_FILE")." &quot;".htmlspecialchars($arFiles[0])."&quot;";
		}
		?>
	</td>
</tr>

<tr>
	<td colspan="2">

<table border="0" cellspacing="1" cellpadding="0" width="100%" class="internal">
<tr class="heading">
	<td valign="middle" align="center" nowrap>
		<?= GetMessage("FILEMAN_ACCESS_GROUP")?>
	</td>
	<td valign="top" align="center" nowrap>
		<?= GetMessage("FILEMAN_ACCESS_LEVEL")?>
	</td>
	<td valign="top" align="center" nowrap>
		<?= GetMessage("FILEMAN_ACCESS_LEVEL_CUR")?>
	</td>
</tr>
	<?
	//возьмем массив прав доступа для всей папки
	$CUR_PERM = GetAccessArrTmp($path);

	//for each groups
	$db_groups = CGroup::GetList($order="sort", $by="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
	while($db_groups->ExtractFields("g_")):
		if($g_ANONYMOUS=="Y")
			$anonym = $g_NAME;

		if(isset($arSubordGroups) && !in_array($g_ID,$arSubordGroups))
			continue;

		//**** Inherit access level *******
		if (!$bDiff)
		{
			$pAr = $APPLICATION->GetFileAccessPermission(Array($site, $path."/".$arFiles[0]), Array($g_ID), true);
			if (count($pAr) > 0)
				$pr_taskId = $pAr[0];
			else
				$pr_taskId = 'NOT_REF';
		}
		// *****************************

		$arTask = Array();
		for($i = 0; $i < $filesCount; $i++)
		{
			if($path=="" && $arFiles[$i]=="")
				$perm = $CUR_PERM["/"][$g_ID];
			else
				$perm = $CUR_PERM[$arFiles[$i]][$g_ID];

			//echo "!".$perm."!";

			if (substr($perm,0,2) == 'T_')
				$taskId = intval(substr($perm,2));
			elseif(strlen($perm) == 1)
				$taskId = CTask::GetIdByLetter($perm, 'main','file');
			else
				$taskId = 'NOT_REF';

			if ($taskId != 'NOT_REF')
			{
				$z = CTask::GetById($taskId);
				if (!($r = $z->Fetch()))
					$taskId = 'NOT_REF';
			}
			$arTask[$taskId][]=$arFiles[$i];
		}

		if(count($arTask)>1)
			$bDiff=true;
		else
			$bDiff=false;
	?>
	<tr valign="top">
		<td>
			[<a href="/bitrix/admin/group_edit.php?ID=<?=$g_ID?>&lang=<?=LANGUAGE_ID?>"><?=$g_ID?></a>]&nbsp;<?echo $g_NAME?>:
		</td>
		<td>
			<select name="g_<?= $g_ID?>" class="typeselect" <?if($bDiff):?>onChange="Conf(this)"<?endif?>>
				<option value=""><?= GetMessage("FILEMAN_ACCESS_LEVEL_NOTCH")?></option>
				<?foreach ($arPermTypes as $id => $ar):?>
					<option value="<?=$id?>"<?if(($id == $taskId) && !$bDiff) echo" selected";?>>
					<?echo htmlspecialchars($ar['title']);
					if($id == "NOT_REF" && !$bDiff)
						echo "[".$arPermTypes[$pr_taskId]['title']."]";?>
					</option>
				<?endforeach;?>
			</select>
		</td>
		<td>
		<?if($bDiff):?>
			<?= GetMessage("FILEMAN_ACCESS_DIFF_GROUP")?><br>
			<table border="0" cellspacing="1" cellpadding="2" width="100%" class="internal in_internal">
				<tr class="heading">
					<td valign="middle" align="center" nowrap>
						<?=GetMessage("FILEMAN_FILE_OR_FOLDER")?>
					</td>
					<td valign="top" align="center" nowrap>
						<?=GetMessage("FILEMAN_ACCESS_LEVEL")?>
					</td>
				</tr>
			<?
			foreach ($arTask as $tid => $tmpAr):?>
				<tr>
					<td valign="top" align="center" align="left">
						<?=$arPermTypes[$tid]['title']?>:
					</td>
					<td valign="top" align="left">
						<?for($i=0; $i<count($tmpAr); $i++)
							echo ($i>0 ? ', ' : '')."&quot;/".$APPLICATION->UnJSEscape($tmpAr[$i])."&quot;";?>
					</td>
				</tr>
			<?endforeach;?>
			</table>
		<?else:?>
			<?=$arPermTypes[($taskId=="NOT_REF" && !$bDiff) ? $pr_taskId : $taskId]['title']?>
		<?endif?>
		</td>
	</tr>
	<?endwhile;?>
	<?
	$arTask = Array();
	for($i = 0; $i < $filesCount; $i++)
	{
		if($path=='' && $arFiles[$i]=='')
			$perm = $CUR_PERM['/']['*'];
		else
			$perm = $CUR_PERM[$arFiles[$i]]['*'];

		if (substr($perm,0,2) == 'T_')
			$taskId = intval(substr($perm,2));
		elseif(strlen($perm) == 1)
			$taskId = CTask::GetIdByLetter($perm,'main','file');
		else
			$taskId = 'NOT_REF';

		if ($taskId != 'NOT_REF')
		{
			$z = CTask::GetById($taskId);
			if (!($r = $z->Fetch()))
				$taskId = 'NOT_REF';
		}
		$arTask[$taskId][]=$arFiles[$i];
	}

	if(count($arTask)>1)
		$bDiff=true;
	else
		$bDiff=false;
	?>
	<tr valign="top">
		<td align="left">
			<?= GetMessage("FILEMAN_ACCESS_GROUP_INHERIT")?>
		</td>
		<td align="left">
			<select name="g_ALL" class="typeselect" <?if($bDiff):?>onChange="Conf(this)"<?endif?>>
				<option value=""><?= GetMessage("FILEMAN_ACCESS_LEVEL_NOTCH")?></option>
				<?foreach ($arPermTypes as $id => $ar):?>
					<option value="<?=$id?>"<?if(($id == $taskId) && !$bDiff) echo" selected";?>>
					<?echo htmlspecialchars($ar['title'])?>
					<?if($id=="NOT_REF")
						echo "[".$arPermTypes[$pr_taskId]['title']."]";?>
					</option>
				<?endforeach;?>
			</select>
		</td>
		<td align="left">
		<?if($bDiff):?>
			<?= GetMessage("FILEMAN_ACCESS_DIFF_GROUP")?><br>
			<table border="0" cellspacing="1" cellpadding="2" width="100%" class="internal in_internal">
				<tr class="heading">
					<td valign="middle" align="center" nowrap>
						<?=GetMessage("FILEMAN_FILE_OR_FOLDER")?>
					</td>
					<td valign="top" align="center" nowrap>
						<?=GetMessage("FILEMAN_ACCESS_LEVEL")?>
					</td>
				</tr>
			<?
			foreach ($arTask as $tid => $tmpAr):
				?>
				<tr>
					<td valign="top" align="center" align="left">
						<?=$arPermTypes[$tid]['title']?>:
					</td>
					<td valign="top" align="left">
						<?for($i=0; $i<count($tmpAr); $i++)
							echo ($i>0 ? ', ' : '')."&quot;/".$APPLICATION->UnJSEscape($tmpAr[$i])."&quot;";?>
					</td>
				</tr>
			<?endforeach;?>
			</table>
		<?else:?>
			<?=$arPermTypes[($taskId=="NOT_REF") ? $pr_taskId : $taskId]['title']?>
		<?endif?>
		</td>
	</tr>
</table>

</td>
</tr>

<?$tabControl->EndTab();?>

<?
$tabControl->Buttons(
	array(
		"disabled" => false,
		"back_url" => "fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path)
	)
);
?>

<?$tabControl->End();?>

<script>
function ChF()
{
	if(document.faform.g_2.selectedIndex==3 || document.faform.g_2.selectedIndex==4)
		if(!confirm("<?= GetMessage("FILEMAN_ACCESS_FOR_GROUP").' '.CUtil::JSEscape($anonym).' '.GetMessage("FILEMAN_ACCESS_FOR_GROUP2")?>"))
			return false;
	return true;
}
</script>

</form>
<?endif;?>
<br>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>