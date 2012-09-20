<?php
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2010 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "utilities/dump.php");

if(!$USER->CanDoOperation('edit_php'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!defined("START_EXEC_TIME"))
	define("START_EXEC_TIME", microtime(true));

if (!defined("BX_DIR_PERMISSIONS"))
	define("BX_DIR_PERMISSIONS", 0777);

if (!defined("BX_FILE_PERMISSIONS"))
	define("BX_FILE_PERMISSIONS", 0666);

if(!defined("DUMP_BASE_TIME_CONST"))
	define("DUMP_BASE_TIME_CONST", 0.4);

if(!defined("DUMP_FILE_TIME_CONST"))
	define("DUMP_FILE_TIME_CONST", 0.8);

IncludeModuleLangFile(__FILE__);

$bUseCompression = $_REQUEST['dump_disable_gzip'] != 'Y';
if(!extension_loaded('zlib') || !function_exists("gzcompress"))
	$bUseCompression = False;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/fileman.php");

if (function_exists('mb_internal_encoding'))
	mb_internal_encoding('ISO-8859-1');

define('DOCUMENT_ROOT', rtrim(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),'/'));

$com_marker = "--";
$filr_id = "";
$sTableID = "tbl_dump";

$oSort = new CAdminSorting($sTableID, "timestamp", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

if($dumping == "Y" && check_bitrix_sessid())
{
	$strErr = '';
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	if ($DB->type != 'MYSQL')
		$dump_base = '';

	if($_REQUEST['start'])
	{
		$max_file_size = intVal($max_file_size);
		$max_execution_time = intVal($max_execution_time);
		if ($max_execution_time < 5)
			$max_execution_time = 5;

		COption::SetOptionString("main", "dump_max_exec_time_sleep", intval($_REQUEST['dump_max_exec_time_sleep']));
		COption::SetOptionString("main", "dump_disable_gzip", $_REQUEST['dump_disable_gzip'] == 'Y' ? 'Y' : 'N');
		COption::SetOptionString("main", "dump_integrity_check", $_REQUEST['dump_integrity_check'] == 'Y' ? 'Y' : 'N');
		COption::SetOptionString("main", "dump_max_file_size", $max_file_size);
		COption::SetOptionString("main", "dump_max_exec_time", $max_execution_time);
		COption::SetOptionString("main", "dump_file_public", $dump_public);
		COption::SetOptionString("main", "dump_file_kernel", $dump_kernel);
		COption::SetOptionString("main", "dump_base_true", $dump_base);
		COption::SetOptionString("main", "dump_base_stat", $stat);
		COption::SetOptionString("main", "dump_base_index", $index);
		COption::SetOptionString("main", "skip_symlinks", $skip_symlinks);
		COption::SetOptionString("main", "skip_mask", $skip_mask);

		$skip_mask_array = array();
		if ($skip_mask && is_array($_REQUEST['arMask']))
		{
			$arMask = array_unique($_REQUEST['arMask']);
			foreach($arMask as $mask)
				if (trim($mask))
				{
					$mask = str_replace('\\','/',trim($mask));
					if (strpos($mask,'/')>0) // incorrect mask: no head slash + slash inside
						continue;
					$skip_mask_array[] = $mask;
				}
			COption::SetOptionString("main", "skip_mask_array", serialize($skip_mask_array));
		}

		$NS=Array();
		if (false == $name = getArcName())
			$strErr = GetMessage('BACKUP_NO_PERMS');
		else
		{
			$NS["arc_name"] = $name["arc_name"];
			$NS["ptab"] = null;
			$NS["num"] = 0;
			$NS["st_row"] = -1;
			$NS["backup_name"] = $name["base_dump"];
			$NS["del"] = false;
			$NS["end"] = false;
			$NS["pos"] = 0;
			$NS["step"] = 1;
		}
	}
	else
	{
		$NS = $_SESSION['BX_DUMP_NS'];		
		$ar = unserialize(COption::GetOptionString("main","skip_mask_array"));
		$skip_mask_array = is_array($ar) ? $ar : array();
	}
	$arRes = array();
	$after_file = str_replace('.sql','_after_connect.sql',$NS['backup_name']);


	$arRes["end"] = false;
	$bWork = false;

	// Step 1: Dump
	if($NS["step"] == 1)
	{
		if ($dump_base == "Y")
		{
			$bres = BaseDump($NS["backup_name"], $NS["num"], $NS["st_row"], ($stat == "Y"), ($index == "Y"));
			$NS["ptab"] = $bres["ptab"];
			$NS["num"] = $bres["num"];
			$NS["st_row"] = $bres["st_row"];

			if ($bres['end'])
			{
				$f = mysql_fetch_array(mysql_query('SHOW VARIABLES LIKE "character_set_results"'));
				if ($charset = $f[1])
				{
					$rs = fopen($after_file,'wb');
					fwrite($rs,"SET NAMES '$charset'");
					fclose($rs);
				}
				$NS["step"]++;
			}
			$bWork = 1;
		}
		else
			$NS["step"]++;
	}

	// Step 2: pack dump 
	if($NS["step"] == 2 && !$bWork)
	{
		if ($dump_base == "Y")
		{
			$tar = new CTar;
			$tar->ArchiveSizeMax = COption::GetOptionString('main', 'dump_archive_size_max', 1024 * 1024 * 1024);
			$tar->gzip = $bUseCompression;
			$tar->lang = LANGUAGE_ID == 'ru' ? 'ru' : 'en';
			$tar->path = DOCUMENT_ROOT;
			$tar->ReadBlockCurrent = intval($NS['ReadBlockCurrent']);

			if (!$tar->openWrite($NS["arc_name"]))
				$strErr = GetMessage('DUMP_NO_PERMS');
			else
			{
				if (!$tar->ReadBlockCurrent && file_exists($f = DOCUMENT_ROOT.BX_ROOT.'/.config.php'))
					$tar->addFile($f);

				$Block = $tar->Block;
				while(haveTime() && ($r = $tar->addFile($NS['backup_name'])) && $tar->ReadBlockCurrent > 0);
				
				if ($r === false)
					$strErr = implode('<br>',$tar->err);
				else
				{
					$arRes = array(
						"size" => $NS['size'] + 512 * ($tar->Block - $Block),
						"ReadBlockCurrent" => $tar->ReadBlockCurrent,
					);

					if($tar->ReadBlockCurrent == 0)
					{
						$tar->addFile($after_file);
						unlink($NS["backup_name"]) && (!file_exists($after_file) || unlink($after_file));
						$NS["step"]++;
					}
				}
				$tar->close();
			}
			$bWork = 1;
		}
		else
			$NS["step"]++;
	}

	// Step 3: Tar Files
	if($NS["step"] == 3 && !$bWork)
	{
		if ($dump_public == "Y" || $dump_kernel == "Y")
		{
			$DOCUMENT_ROOT_SITE = DOCUMENT_ROOT;
			if ($_REQUEST['dump_site_id'])
			{
				$rs = CSite::GetList($by='sort', $order='asc', array('ID'=>$_REQUEST['dump_site_id']));
				if ($f = $rs->Fetch())
					$DOCUMENT_ROOT_SITE = rtrim(str_replace('\\','/',$f['ABS_DOC_ROOT']));
			}
			if (!defined('DOCUMENT_ROOT_SITE'))
				define('DOCUMENT_ROOT_SITE', $DOCUMENT_ROOT_SITE);

			$tar = new CTar;
			$tar->ArchiveSizeMax = COption::GetOptionString('main', 'dump_archive_size_max', 1024 * 1024 * 1024);
			$tar->gzip = $bUseCompression;
			$tar->lang = LANGUAGE_ID == 'ru' ? 'ru' : 'en';
			$tar->path = DOCUMENT_ROOT_SITE;
			$tar->ReadBlockCurrent = intval($NS['ReadBlockCurrent']);

			if (!$tar->openWrite($NS["arc_name"]))
				$strErr = GetMessage('DUMP_NO_PERMS');
			else
			{
				$Block = $tar->Block;
				$DirScan = new CDirRealScan;

				if (!$NS['startPath'])
				{
					if (!$dump_base && file_exists($f = DOCUMENT_ROOT.BX_ROOT.'/.config.php'))
						$tar->addFile($f);
				}
				else
					$DirScan->startPath = $NS['startPath'];

				$r = $DirScan->Scan(DOCUMENT_ROOT_SITE);

				if ($r === false)
					$strErr = implode('<br>',array_merge($DirScan->err,$tar->err));
				else
				{
					$arRes = array(
						"size" => $NS['size'] + 512 * ($tar->Block - $Block),
						"ReadBlockCurrent" => $tar->ReadBlockCurrent,
						"startPath" => $DirScan->nextPath,
						"cnt" => $DirScan->FileCount,
					);

					if ($r !== 'BREAK')
						$NS["step"]++;
				}
				$tar->close();
			}
			$bWork = 1;
		}
		else
			$NS["step"]++;
	}
	
	// Step 4: Integrity check
	if($NS["step"] == 4 && !$bWork)
	{
		if ($_REQUEST['dump_integrity_check'])
		{
			$tar = new CTarCheck;
			$tar->lang = LANGUAGE_ID == 'ru' ? 'ru' : 'en';

			if (!$tar->openRead($NS["arc_name"]))
				$strErr = GetMessage('DUMP_NO_PERMS_READ');
			else
			{
				if($Block = intval($NS['Block']))
					$tar->Skip($Block);

				while(($r = $tar->extractFile()) && haveTime());

				if ($r === false)
					$strErr = implode('<br>',$tar->err);
				else
				{
					$arRes = array(
						"Block" => $tar->Block,
					);
				}
				$tar->close();

				if ($r === 0)
					$NS["step"]++;
			}
			$bWork = 1;
		}
		else
			$NS["step"]++;
	}
	if ($NS["step"] > 4)
		$arRes["end"] = true;

	$arRes["arc_name"] = $NS["arc_name"];
	$arRes["backup_name"] = $NS["backup_name"];
	$arRes["cnt"] += $NS["cnt"];
	if (!$arRes['size'])
		$arRes["size"] = $NS["size"]; // читаем из сессии

	$arRes["ptab"] = $NS["ptab"];
	$arRes["num"] = $NS["num"];
	$arRes["st_row"] = $NS["st_row"];
	$arRes["add"] = $NS["add"];
	$arRes["del"] = $NS["del"];
	$arRes["step"] = $NS["step"];
	$arRes["time"] = $NS["time"] + workTime();

	$status_msg = "";

	if ($strErr)
	{
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage("MAIN_DUMP_ERROR"),
			"DETAILS" =>  $strErr,
			"TYPE" => "ERROR",
			"HTML" => true));
	} 
	elseif(!$arRes["end"])
	{
		switch ($NS['step'])
		{
			case 1:
				$status_title = GetMessage('DUMP_DB_CREATE');
				$status_msg = GetMessage("MAIN_DUMP_TABLE_FINISH")." <b>".($arRes["num"])."</b>"
					.'<br>'.GetMessage('TIME_SPENT').' <b>'.HumanTime($arRes["time"]).'</b>';
			break;
			case 2:
				$status_title = GetMessage("MAIN_DUMP_DB_PROC");
				$status_msg = 
				GetMessage('CURRENT_POS').' <b>'.HumanSize($arRes['size']) .'</b>  (<b>'.round(100 * $arRes['size'] / filesize($NS['backup_name'])).'%</b>) '
					.'<br>'.GetMessage('TIME_SPENT').' <b>'.HumanTime($arRes["time"]).'</b>';
			break;
			case 3:
				$status_title = GetMessage("MAIN_DUMP_SITE_PROC");
				$status_msg .= GetMessage("MAIN_DUMP_FILE_CNT")." <b>".$arRes["cnt"]."</b><br>".
				GetMessage("MAIN_DUMP_FILE_SIZE")." <b>".HumanSize($arRes["size"])."</b> ";

				if (is_object($DirScan))
					$status_msg.= '<br>'.GetMessage('DUMP_CUR_PATH').' <b>'.substr($DirScan->nextPath,strlen(DOCUMENT_ROOT_SITE)).'</b>';

				$status_msg .= '<br>'.GetMessage('TIME_SPENT').' <b>'.HumanTime($arRes["time"]).'</b>';
			break;
			case 4:
				$status_title = GetMessage('INTEGRITY_CHECK');
				$status_msg = GetMessage("MAIN_DUMP_FILE_SIZE")." <b>".HumanSize($arRes["size"])."</b><br>".
				GetMessage('CURRENT_POS').' <b>'.HumanSize(512 * $tar->Block).'</b>  (<b>'.round(100 * $tar->Block * 512 / $arRes['size']).'%</b>) '
					.'<br>'.GetMessage('TIME_SPENT').' <b>'.HumanTime($arRes["time"]).'</b>';
		}

		CAdminMessage::ShowMessage(array(
			"MESSAGE" => $status_title,
			"DETAILS" =>  $status_msg,
			"TYPE" => "OK",
			"HTML" => true));
			
		$_SESSION['BX_DUMP_NS'] = $arRes;
?>
		<input type="hidden" id="DoNext">
<?	}
	else
	{
		$arc_size = 0;
		$arc_name = $NS["arc_name"];
		$i = 0;
		while(file_exists($arc_name))
		{
			$arc_size += filesize($arc_name);
			$arc_name = $NS["arc_name"].'.'.(++$i);
		}

		if (COption::GetOptionInt("main", "disk_space") > 0)
			CDiskQuota::updateDiskQuota("file", $arc_size, "add");

		if($dump_public == "Y" || $dump_kernel == "Y")
		{
			$status_msg = $dump_base == "Y" ? '' : GetMessage("MAIN_DUMP_TABLE_FINISH")." <b>".$arRes["num"]."</b><br>";

			$status_msg .= GetMessage("MAIN_DUMP_FILE_CNT")." <b>".$arRes["cnt"]."</b><br>".
			GetMessage("MAIN_DUMP_FILE_SIZE")." <b>".HumanSize($arRes["size"])."</b><br>".
			GetMessage("MAIN_DUMP_ARC_SIZE")." <b>".HumanSize($arc_size)."</b><br>". 
			GetMessage('TIME_SPENT').' <b>'.HumanTime($arRes["time"]).'</b>';
		}
		elseif($dump_base == "Y")
			$status_msg = GetMessage("MAIN_DUMP_TABLE_FINISH")." <b>".$arRes["num"]."</b><br>".
			GetMessage("MAIN_DUMP_ARC_SIZE")." <b>".HumanSize($arc_size)."</b>";

		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage("MAIN_DUMP_FILE_FINISH"),
			"DETAILS" => $status_msg ,
			"TYPE" => "OK",
			"HTML" => true));

?>
		<?echo bitrix_sessid_post()?>
		<script><?=$lAdmin->ActionPost(htmlspecialchars($APPLICATION->GetCurPageParam("mode=frame", Array("mode", "PAGEN_1"))))?></script>
<?
	}
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
	die();
}
else
	$DB->Query("UNLOCK TABLES",true);


######### Admin list #######
$arFilterFields = array();
$lAdmin->InitFilter($arFilterFields);
$lAdmin->BeginPrologContent();

if(strlen($strMess)>0 && strlen($strSucc)>0)
{
	$mes = Array("MESSAGE"=>$strSucc, "DETAILS"=>$strMess, "TYPE"=>"OK", "HTML"=>true);
	$m = new CAdminMessage($mes);
	echo $m->Show();
}

$site = CSite::GetSiteByFullPath(DOCUMENT_ROOT);
$path = BX_ROOT."/backup";

if (($arID = $lAdmin->GroupAction()))
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();

		if (!CSite::IsDistinctDocRoots() || strlen($site) > 0 || strlen($path) > 0)
		{
			$DOC_ROOT = CSite::GetSiteDocRoot($site);

			$path = Rel2Abs("/", rtrim($path, "/"));
			$arParsedPath = ParsePath(Array($site, $path));

			$abs_path = $DOC_ROOT.$path;

			GetDirList(Array($site, $path), $arDirs, $arFiles, $arFilter, Array($by => $order), "F");

			foreach ($arFiles as $File)
				$arID[] = $File["NAME"];
		}
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		$CurPerm = $APPLICATION->GetFileAccessPermission(array($site, $path."/".$ID));
		if ($CurPerm < "W")
			continue;

		switch ($_REQUEST['action'])
		{
			case "export":
				?>
				<script language="JavaScript">
					exportData('<?=$ID?>');
				</script>
				<?
			break;
			case "delete":
				$strWarning_tmp = CFileMan::DeleteEx(Array($site, CFileMan::NormalizePath($path."/".$ID)));

				if (strlen($strWarning_tmp) > 0)
					$lAdmin->AddGroupError($strWarning_tmp, $ID);
			break;
		}
	}
}
InitSorting();

$arDirs = array();
$arFiles = array();
$arFilter = array("EXTENSIONS"=>"sql,tar,gz,1,2,3,4,5,6,7,8,9");

GetDirList(Array($site, $path), $arDir, $arFiles, $arFilter, Array($by=>$order), "F");
$rsDirContent = new CDBResult;
$rsDirContent->InitFromArray($arFiles);
$rsDirContent->sSessInitAdd = $path;
$rsDirContent = new CAdminResult($rsDirContent, $sTableID);
$rsDirContent->NavStart(20);

// установка строки навигации
$lAdmin->NavText($rsDirContent->GetNavPrint(GetMessage("MAIN_DUMP_FILE_PAGES")));
$lAdmin->AddHeaders(array(
		array("id"=>"NAME", "content"=>GetMessage("MAIN_DUMP_FILE_NAME"), "sort"=>"name", "default"=>true),
		array("id"=>"SIZE","content"=>GetMessage("FILE_SIZE"), "sort"=>"size", "default"=>true),
		array("id"=>"DATE", "content"=>GetMessage('MAIN_DUMP_FILE_TIMESTAMP'), "sort"=>"timestamp", "default"=>true)
));

while($Elem = $rsDirContent->NavNext(true, "f_"))
{
	$fname = $documentRoot.$path."/".$Elem["NAME"];

	$showFieldIcon = "";
	$showFieldText = "";
	$curFileType = CFileMan::GetFileTypeEx($Elem["NAME"]);
	$showFieldIcon = "<IMG SRC=\"/bitrix/images/fileman/types/".$curFileType.".gif\" WIDTH=\"16\" HEIGHT=\"16\" BORDER=0 ALT=\"\">";
	$showFieldText = $f_NAME;

	$showField = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"left\">".$showFieldIcon."</td><td align=\"left\" nowrap>&nbsp;".$showFieldText."</td></tr></table>";

	$row =& $lAdmin->AddRow($f_NAME, $Elem);

	$row->AddField("NAME", $showField, $editField);
	$row->AddField("SIZE", HumanSize($f_SIZE));
	$row->AddField("DATE", $f_DATE);

	$arActions = Array();

	if ($Elem["PERMISSION"] > "R")
	{
		$arActions[] = array(
			"ICON" => "export",
			"DEFAULT" => true,
			"TEXT" => GetMessage("MAIN_DUMP_ACTION_DOWNLOAD"),
			"ACTION" => "exportData('".$f_NAME."')"
		);
		$arActions[] = array(
			"ICON" => "restore",
			"TEXT" => GetMessage("MAIN_DUMP_RESTORE"),
			"ACTION" => "if(confirm('".GetMessage("MAIN_RIGHT_CONFIRM_EXECUTE")."')) restoreDump('".$f_NAME."')"
		);

		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("MAIN_DUMP_DELETE"),
			"ACTION" => "if(confirm('".GetMessage('MAIN_DUMP_ALERT_DELETE')."')) ".$lAdmin->ActionDoGroup($f_NAME, "delete", $addUrl."&site=".Urlencode($site)."&path=".UrlEncode($path)."&show_perms_for=".IntVal($show_perms_for))
		);
	}
	$row->AddActions($arActions);
}

// "подвал" списка
$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsDirContent->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")
	)
);

$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("MAIN_DUMP_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aTabs = array(
	array("DIV"=>"tab1", "TAB"=>GetMessage("TAB_STANDARD"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("TAB_STANDARD_DESC")),
	array("DIV"=>"tab2", "TAB"=>GetMessage("TAB_ADVANCED"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("TAB_ADVANCED_DESC")),
);
$editTab = new CAdminTabControl("editTab", $aTabs);

?>

<div id="dump_result_div"></div>
<? 
echo BeginNote();
echo GetMessage("MAIN_DUMP_HEADER_MSG");
echo EndNote();

CAdminFileDialog::ShowScript(Array
    (
	"event" => "__bx_select_dir",
	"arResultDest" => Array("FUNCTION_NAME" => "mnu_SelectValue"),
	"arPath" => Array('PATH'=>"/"),
	"select" => 'D',
	"operation" => 'O',
	"showUploadTab" => false,
	"showAddToMenuTab" => false,
	"allowAllFiles" => true,
	"SaveConfig" => true 
    )
);		
?>
<script language="JavaScript">
var numRows=0;
function AddTableRow()
{
	oTable = document.getElementById('skip_mask_table');
	numRows = oTable.rows.length;
	oRow = oTable.insertRow(-1);
	oCell = oRow.insertCell(0);
	oCell.innerHTML = '<input name="arMask[]" id="mnu_FILES_' + numRows  +'" size=30><input type="button" id="mnu_FILES_btn_' + numRows  + '" value="..." onclick="showMenu(this, '+ numRows  +')">';
}

var currentID;

function showMenu(div, id)
{
	currentID = id;
	__bx_select_dir();
}

function mnu_SelectValue(filename, path, site, title, menu)
{
	document.getElementById('mnu_FILES_' + currentID).value = path + (path == '/' ? '' : '/') + filename;
}

function CheckActiveStart()
{
	if (ob = document.getElementById('dump_base'))
	{
		document.fd1.dump_stat.disabled = !ob.checked;
		document.fd1.dump_index.disabled = !ob.checked;
	}

	noFiles = !document.fd1.dump_public.checked && !document.fd1.dump_kernel.checked;
	document.fd1.max_file_size.disabled = noFiles;
	document.fd1.skip_symlinks.disabled = noFiles;
	document.fd1.skip_mask.disabled = noFiles;

	if (ob = document.getElementById('dump_site_id'))
		ob.disabled = !document.fd1.dump_public.checked;

	noMask = noFiles || !document.getElementById('skip_mask').checked;

	oTable = document.getElementById('skip_mask_table');
	numRows = oTable.rows.length;
	for(i=0;i<numRows;i++)
	{
		document.getElementById('mnu_FILES_'+i).disabled = noMask;
		document.getElementById('mnu_FILES_btn_'+i).disabled = noMask;
	}
	document.getElementById('more_button').disabled = noMask;
	document.getElementById('start_button').disabled = noFiles && !document.fd1.dump_base.checked;
}

function CheckActiveMode()
{
	standard = document.fd1.dump_public.checked && document.fd1.dump_kernel.checked && (document.fd1.max_file_size.value == 0) && !document.fd1.skip_symlinks.checked && !document.fd1.skip_mask.checked && document.fd1.dump_integrity_check.checked;

	if (standard && (ob_dump = document.getElementById('dump_base')))
		standard = ob_dump.checked && !document.fd1.dump_stat.checked && !document.fd1.dump_index.checked;

	if (!standard)
		return;

	if (document.fd1.max_execution_time.value == 20 && document.fd1.dump_max_exec_time_sleep.value == 3 && !document.fd1.dump_disable_gzip.checked)
		document.getElementById('shared_profile').checked = true;
	else if (document.fd1.max_execution_time.value == 45 && document.fd1.dump_max_exec_time_sleep.value == 0 && !document.fd1.dump_disable_gzip.checked)
		document.getElementById('vps_profile').checked = true;
	else if (document.fd1.max_execution_time.value == 15 && document.fd1.dump_max_exec_time_sleep.value == 10 && document.fd1.dump_disable_gzip.checked)
		document.getElementById('slow_profile').checked = true;

	CheckActiveStart();
}

window.setTimeout(CheckActiveMode, 1000);

function SetMode(ob)
{
	document.fd1.dump_public.checked = true;
	document.fd1.dump_kernel.checked = true;
	document.fd1.max_file_size.value = 0;
	document.fd1.skip_symlinks.checked = false;
	document.fd1.skip_mask.checked = false;

	if (ob_dump = document.getElementById('dump_base'))
	{
		ob_dump.checked = true;
		document.fd1.dump_stat.checked = false;
		document.fd1.dump_index.checked = false;
	}


	switch (ob.value)
	{
		case 'shared':
			document.fd1.max_execution_time.value = 20;
			document.fd1.dump_max_exec_time_sleep.value = 3;
			document.fd1.dump_disable_gzip.checked = false;
			document.fd1.dump_integrity_check.checked = true;
		break;
		case 'vps':
			document.fd1.max_execution_time.value = 45;
			document.fd1.dump_max_exec_time_sleep.value = 0;
			document.fd1.dump_disable_gzip.checked = false;
			document.fd1.dump_integrity_check.checked = true;
		break;
		case 'slow':
		default:
			document.fd1.max_execution_time.value = 15;
			document.fd1.dump_max_exec_time_sleep.value = 10;
			document.fd1.dump_disable_gzip.checked = true;
			document.fd1.dump_integrity_check.checked = true;
		break;
	}
	CheckActiveStart();
}

var stop;
function StartDump()
{
	stop = false;
	document.getElementById('dump_result_div').innerHTML='';
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	DoNext(true);
}

function EndDump()
{
	stop = true;
	document.getElementById('stop_button').disabled = true;
	document.getElementById('start_button').disabled = false;
}

function DoNext(start)
{
	queryString='lang=<?echo htmlspecialchars(LANG)?>';
	queryString+='&dumping=Y';

	if(start)
		queryString+='&start=Y';

	queryString+='&max_execution_time='+document.getElementById('max_execution_time').value;
	queryString+='&dump_max_exec_time_sleep='+document.getElementById('dump_max_exec_time_sleep').value;

	if (document.fd1.dump_disable_gzip.checked)
		queryString += '&dump_disable_gzip=Y';

	if (document.fd1.dump_integrity_check.checked)
		queryString += '&dump_integrity_check=Y';

	if(document.getElementById('dump_public').checked)
		queryString +='&dump_public=Y';

	if(document.getElementById('dump_kernel').checked)
			queryString+='&dump_kernel=Y';

	if(document.getElementById('skip_symlinks').checked)
			queryString+='&skip_symlinks=Y';

	if(document.getElementById('skip_mask').checked)
	{
		queryString+='&skip_mask=Y';

		oTable = document.getElementById('skip_mask_table');
		numRows = oTable.rows.length;

		for(i=0;i<numRows;i++)
			queryString+='&arMask[]=' + document.getElementById('mnu_FILES_'+i).value;
	}

	if(document.getElementById('dump_public').checked || document.getElementById('dump_kernel').checked)
		queryString+='&max_file_size='+document.getElementById('max_file_size').value;


	if(document.getElementById('dump_base').checked)
	{
		queryString +='&dump_base=Y';

		if(!document.getElementById('dump_stat').checked)
			queryString +='&stat=Y';
		if(!document.getElementById('dump_index').checked)
			queryString +='&index=Y';
	}

	if (ob = document.getElementById('dump_site_id'))
		queryString += '&dump_site_id=' + ob.value;

	CHttpRequest.Action = function(result)
	{
		CloseWaitWindow();
		document.getElementById('dump_result_div').innerHTML = result;
		if (result.search('"DoNext"') == -1)
			EndDump();
		else if(!stop)
			setTimeout('DoNext()', 1000 * document.getElementById('dump_max_exec_time_sleep').value);
	}
	ShowWaitWindow();
	CHttpRequest.Send('dump.php?'+queryString+'&<?echo bitrix_sessid_get()?>');
}

function exportData(val)
{
	window.open('dump_export.php?f_id='+val);
}

function restoreDump(val)
{
	window.open('exec_restore.php?f_id='+val+'&<?echo bitrix_sessid_get()?>');
}
</script>


	<form name="fd1" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>" method="GET">
	<?
	$editTab->Begin();
	$editTab->BeginNextTab();
	?>

	<tr>
		<td colspan=2 align=center><? 
		echo BeginNote();
		echo GetMessage('MODE_DESC');
		echo EndNote();
		?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><label><input type="radio" name=arc_profile value=shared id='shared_profile' onclick="SetMode(this)"> <?=GetMessage('MODE_SHARED')?></label></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><label><input type="radio" name=arc_profile value=vps id='vps_profile' onclick="SetMode(this)"> <?=GetMessage('MODE_VPS')?></label></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><label><input type="radio" name=arc_profile value=slow id='slow_profile' onclick="SetMode(this)"> <?=GetMessage('MODE_SLOW')?></label></td>
	</tr>
	<?
	$editTab->BeginNextTab();
	?>
	
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("MAIN_DUMP_FILE_TITLE")?></td>
	</tr>
	<tr>
		<td style="width:50%"><?echo GetMessage("MAIN_DUMP_FILE_KERNEL")?></td>
		<td><input type="checkbox" name="dump_kernel" id="dump_kernel" value="Y" OnClick="CheckActiveStart()" <?if(COption::GetOptionString("main", "dump_file_kernel")=="Y") echo " checked";?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_FILE_PUBLIC")?></td>
		<td><input type="checkbox" name="dump_public" id="dump_public" value="Y" OnClick="CheckActiveStart()" <?if(COption::GetOptionString("main", "dump_file_public")=="Y") echo " checked"?>></td>
	</tr>
	<?
	$arSitePath = array();
	$res = CSite::GetList($by='sort', $order='asc', array('ACTIVE'=>'Y'));
	while($f = $res->Fetch())
		$arSitePath[$f['ABS_DOC_ROOT']] = array($f['ID'] => '['.$f['ID'].'] '.$f['NAME']);

	if (count($arSitePath) > 1)
	{
	?>
	<tr>
		<td><?=GetMessage('PUBLIC_PART')?></td>
		<td>
			<select id=dump_site_id>
			<?
				foreach($arSitePath as $path=>$val)
				{
					$path = rtrim(str_replace('\\','/',$path),'/');
					list($k,$v) = each($val);
					echo '<option value="'.htmlspecialchars($k).'"'.($path == DOCUMENT_ROOT ? ' selected' : '').'>'.htmlspecialchars($v).'</option>';
				}
			?>
			</select>
		</td>
	</tr>
	<?
	}
	?>
	<?
		$bNoFiles = COption::GetOptionString("main", "dump_file_public")!="Y" && COption::GetOptionString("main", "dump_file_kernel")!="Y";
	?>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_FILE_MAX_SIZE")?></td>
		<td><input type="text" name="max_file_size" id="max_file_size" size="10" value="<?echo COption::GetOptionString("main", "dump_max_file_size","0")?>" <? if(COption::GetOptionString("main", "dump_file_public")!="Y" && COption::GetOptionString("main", "dump_file_kernel")!="Y") echo " disabled"?>>
		<?echo GetMessage("MAIN_DUMP_FILE_MAX_SIZE_kb")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_SKIP_SYMLINKS")?></td>
		<td><input type="checkbox" name="skip_symlinks" id="skip_symlinks" <?=$bNoFiles?'disabled':''?> value="Y" <?if(COption::GetOptionString("main", "skip_symlinks", "Y")=="Y") echo " checked";?>></td>
	</tr>
	<? $bMask = COption::GetOptionString("main", "skip_mask", "N")=="Y";?>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_MASK")?><span class="required"><sup>1</sup></span></td>
		<td><input type="checkbox" name="skip_mask" id="skip_mask" <?=$bNoFiles?'disabled':''?> value="Y" <?=$bMask?" checked":'';?> onclick="CheckActiveStart()">
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<table id="skip_mask_table" cellspacing=0 cellpadding=0>
			<?
			$i=-1;

			$res = unserialize(COption::GetOptionString("main","skip_mask_array"));
			$skip_mask_array = is_array($res)?$res:array();

			foreach($skip_mask_array as $mask)
			{
				$i++;
				echo
				'<tr><td>
					<input name="arMask[]" id="mnu_FILES_'.$i.'" value="'.htmlspecialchars($mask).'" '.(!$bMask||$bNoFiles?'disabled':'').' size=30>'.
					'<input type="button" id="mnu_FILES_btn_'.$i.'" '.(!$bMask||$bNoFiles?'disabled':'').' value="..." onclick="showMenu(this, \''.$i.'\')">'.
				'</tr>';
			}
			$i++;
			?>
				<tr><td><input name="arMask[]" id="mnu_FILES_<?=$i?>" size=30 <?=!$bMask||$bNoFiles?'disabled':'';?>><input type="button" id="mnu_FILES_btn_<?=$i?>" value="..." onclick="showMenu(this, '<?=$i?>')" <?=!$bMask||$bNoFiles?'disabled':'';?>></tr>
			</table>
			<input type=button id="more_button" value="<?=GetMessage('MAIN_DUMP_MORE')?>" onclick="AddTableRow()" <?=(!$bMask||$bNoFiles?'disabled':'')?>>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("MAIN_DUMP_BASE_TITLE")?></td>
	</tr>
	<?
	if ($DB->type != 'MYSQL') 
	{
		$strDisableNotMysql = 'disabled';
	?>
	<tr>
		<td colspan=2 align=center><? 
		echo BeginNote();
		echo GetMessage('MAIN_DUMP_MYSQL_ONLY');
		echo EndNote();
		?></td>
	</tr>
	<? 
	} else 
		$strDisableNotMysql = '';
	?>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_BASE_TRUE")?></td>
		<td><input type="checkbox" <?=$strDisableNotMysql?> name="dump_base" id="dump_base" OnClick="CheckActiveStart()" <?=COption::GetOptionString("main", "dump_base_true")=="Y" ? "checked" : "" ?>><?= " (".getTableSize("")." ".GetMessage("MAIN_DUMP_BASE_SIZE").") " ;?>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("MAIN_DUMP_BASE_IGNORE")?></td>
		<td><label><input type="checkbox" <?=$strDisableNotMysql?> name="dump_stat" id="dump_stat" <?=COption::GetOptionString("main", "dump_base_stat")!="Y" ? "checked" : "" ?>> <? echo GetMessage("MAIN_DUMP_BASE_STAT")." (".getTableSize("b_stat")." ".GetMessage("MAIN_DUMP_BASE_SIZE").")" ?></label>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><label><input type="checkbox" <?=$strDisableNotMysql?> name="dump_index" id="dump_index" value="Y"<?=COption::GetOptionString("main", "dump_base_index")!="Y" ? "checked" : "" ?>> <? echo GetMessage("MAIN_DUMP_BASE_SINDEX")." (".getTableSize("b_search")." ".GetMessage("MAIN_DUMP_BASE_SIZE").")" ?></label>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('SERVER_LIMIT')?></td>
	</tr>
	<tr>
		<td><?=GetMessage('STEP_LIMIT')?></td>
		<td>
			<? $cur = COption::GetOptionString("main", "dump_max_exec_time","30"); ?>
			<input name="max_execution_time" id="max_execution_time" value="<?=$cur?>" size=2>
			
			<?echo GetMessage("MAIN_DUMP_FILE_STEP_sec");?>,
			<?echo GetMessage("MAIN_DUMP_FILE_STEP_SLEEP")?>
			<? $cur = COption::GetOptionString("main", "dump_max_exec_time_sleep","10"); ?>
			<input name="dump_max_exec_time_sleep" id="dump_max_exec_time_sleep" value="<?=$cur?>" size=2>
			<?echo GetMessage("MAIN_DUMP_FILE_STEP_sec");?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage('DISABLE_GZIP')?></td>
		<td><input type="checkbox" name="dump_disable_gzip" <?=COption::GetOptionString('main','dump_disable_gzip','N') == 'Y' ? 'checked' : '' ?>>
	</tr>
	<tr>
		<td><?=GetMessage('INTEGRITY_CHECK_OPTION')?></td>
		<td><input type="checkbox" name="dump_integrity_check" <?=COption::GetOptionString('main','dump_integrity_check','Y') == 'Y' ? 'checked' : '' ?>>
	</tr>

	<?$editTab->Buttons();
	?>
	<input type="button" id="start_button" value="<?echo GetMessage("MAIN_DUMP_FILE_DUMP_BUTTON")?> " <?if((COption::GetOptionString("main", "dump_file_public")!="Y") && (COption::GetOptionString("main", "dump_base_true")!="Y") && (COption::GetOptionString("main", "dump_file_kernel")!="Y")) echo " disabled"?> OnClick="StartDump();">
	<input type="button" id="stop_button" value="<?echo GetMessage("MAIN_DUMP_FILE_STOP_BUTTON")?>" OnClick="EndDump();" disabled>

	<?
	$editTab->End();
	?>
	</form>

<?
$lAdmin->DisplayList();

echo BeginNote();
echo '<span class=required><sup>1</sup></span> '.GetMessage("MAIN_DUMP_FOOTER_MASK");
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");





#################################################
################## FUNCTIONS
function skipMask($abs_path)
{
	if ($_REQUEST['skip_mask'] != 'Y')
		return false;

	global $skip_mask_array;
	
	$path = substr($abs_path,strlen(DOCUMENT_ROOT));
	$path = str_replace('\\','/',$path);
	
	static $preg_mask_array;
	if (!$preg_mask_array)
		$preg_mask_array = prepare_preg_escape($skip_mask_array);

	reset($skip_mask_array);
	foreach($skip_mask_array as $k=>$mask)
	{
		if (strpos($mask,'/')===0) // absolute path
		{
			if (strpos($mask,'*')===false) // no asterisk present
			{
				if (rtrim($path,'/') == $mask)
					return true;
			}
			elseif (preg_match('#^'.str_replace('*','[^/]*?',$preg_mask_array[$k]).'$#i',$path))
				return true;
		}
		elseif (strpos($mask, '/')===false)
		{
			if (strpos($mask,'*')===false)
			{
				if (substr($path,-strlen($mask)) == $mask)
					return true;
			}
			elseif (preg_match('#/[^/]*'.str_replace('*','[^/]*?',$preg_mask_array[$k]).'$#i',$path))
				return true;
		}
	}
}

function prepare_preg_escape($skip_mask_array)
{
	static $res;
	if (!isset($res))
		foreach($skip_mask_array as $a)
			$res[] = _preg_escape($a); 
	return $res;
}

function _preg_escape($str)
{
	$search = array('#','[',']','.','?','(',')','^','$','|','{','}');
	$replace = array('\#','\[','\]','\.','\?','\(','\)','\^','\$','\|','\{','\}');
	return str_replace($search, $replace, $str);
}

function createTable($table_name, $drop = true)
{
	global $DB, $com_marker;
	$sql = "SHOW CREATE TABLE `".$table_name."`";

	$res = $DB->Query($sql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	$row = $res->Fetch();

	$com = "\n\n";
	$com .= $com_marker. " --------------------------------------------------------" ."\n";
	$com .= $com_marker. " \n";
	$com .= $com_marker. " Table structure for table `".$table_name."`\n";
	$com .= $com_marker. " \n";
	$com .= "\n";

	$string = $row['Create Table'];
//	$string = preg_replace('#collate [a-z0-9_]+#i','',$string);

	return $com."\n\n\n".($drop ? "DROP TABLE IF EXISTS `".$table_name."`;\n".$string : str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$string)).';';
}

function getData($table, $file, $row_count, $last_row = 0, $mem)
{
	global $DB, $com_marker;
	$dump = "";
	$step = "";

	$com = "\n" .$com_marker. " \n";
	$com .= $com_marker. " Dumping data for table  `".$table."`\n";
	$com .= $com_marker. " \n";
	$com .= "\n";

	fwrite($file, $com."\n");

	$sql = "SHOW COLUMNS FROM `$table`";
	$res = $DB->Query($sql);
	$num = Array();
	$i = 0;

	//Определяем тип поля
	while($row = $res->Fetch())
	{
		if(preg_match("/^(\w*int|year|float|double|decimal)/", $row["Type"]))
			$meta[$i] = 0;
		elseif(preg_match("/^(\w*binary)/", $row["Type"]))
		{
			$meta[$i] = 1;
		} else
			$meta[$i] = 2;
		$i++;
	}

	$sql = "SHOW TABLE STATUS LIKE '$table'";
	$res = $DB->Query($sql);
	$tbl_info = $res->Fetch();
	$step = 1+round($mem * 1048576 * 0.5 / ($tbl_info["Avg_row_length"] + 1));

	$DB->Query("LOCK TABLE `$table` WRITE",true);
	while(($last_row <= ($row_count-1)) && haveTime())
	{
		$sql = "SELECT * FROM `$table` LIMIT $last_row, $step";
		$res = $DB->Query($sql);

		while($row = $res->Fetch())
		{
			$i = 0;
			foreach($row as $key => $val)
			{
				if (!isset($val) || is_null($val))
						$row[$key] = 'NULL';
				else
					switch($meta[$i])
					{
						case 0:
							$row[$key] = $val;
						break;
						case 1:
							if (empty($val) && $val != '0')
								$row[$key] = '\'\'';
							else
								$row[$key] = '0x' . bin2hex($val);
						break;
						case 2:
							$row[$key] = "'".$DB->ForSql($val)."'";
						break;
					}
				$i++;
			}
			fwrite($file, "INSERT INTO `".$table."` VALUES (".implode(",", $row).");\n");
		}
		$last_row += $step;
	}
	$DB->Query("UNLOCK TABLES",true);

	if($last_row >= ($row_count-1))
		return -1;
	else
		return $last_row;
}

function getArcName()
{
	global $bUseCompression;
	$ret = false;

	if(!file_exists(DOCUMENT_ROOT.BX_ROOT."/backup"))
		mkdir(DOCUMENT_ROOT.BX_ROOT."/backup", BX_DIR_PERMISSIONS);

	if(!file_exists(DOCUMENT_ROOT.BX_ROOT."/backup/index.php"))
	{
		$f = fopen(DOCUMENT_ROOT.BX_ROOT."/backup/index.php","w");
		fwrite($f,"<head><meta http-equiv=\"REFRESH\" content=\"0;URL=/bitrix/admin/index.php\"></head>");
		fclose($f);
	}

	if(is_dir(DOCUMENT_ROOT.BX_ROOT."/backup") && (is_writable(DOCUMENT_ROOT.BX_ROOT."/backup")))
	{
		$arc_name = DOCUMENT_ROOT.BX_ROOT."/backup/".date("Y-m-d.H-i-s.");
		if ($_REQUEST['dump_site_id'])
			$arc_name.= $_REQUEST['dump_site_id'].'.';
		$arc_name .= substr(md5(uniqid(rand(), true)), 0, 8);

		$ret["arc_name"] = $arc_name.".tar";
		if ($bUseCompression)
			$ret["arc_name"].=".gz";

		$ret["base_dump"] = $arc_name.".sql";
	}


	return $ret;
}

function ignorePath($path)
{
	global $dump_public, $dump_kernel;
	
	$ignore_path = array(
		BX_PERSONAL_ROOT."/cache",
		BX_PERSONAL_ROOT."/cache_image",
		BX_PERSONAL_ROOT."/managed_cache",
		BX_PERSONAL_ROOT."/stack_cache",
		BX_PERSONAL_ROOT."/html_pages",
		BX_PERSONAL_ROOT."/backup",
		BX_PERSONAL_ROOT."/tmp",
		BX_ROOT."/tmp",
		BX_ROOT."/backup",
		BX_ROOT."/help",
		BX_ROOT."/updates",
	);

	$path_kernel = array(
		"/bitrix/admin",
		"/bitrix/license_key.php",
		"/bitrix/php_interface",
		"/bitrix/activities/bitrix",
		"/bitrix/components/bitrix",
		"/bitrix/images",
		"/bitrix/js",
		"/bitrix/modules",
		"/bitrix/sounds",
		"/bitrix/themes/.default",
		"/bitrix/tools",
		"/bitrix/wizards/bitrix"
	);

	foreach($ignore_path as $value)
		if(DOCUMENT_ROOT_SITE.$value == $path)
			return true;

	// If we make kernel only dump - archive only $path_kernel, if public only - anything other
	if ($dump_public xor $dump_kernel)
	{
		foreach($path_kernel as $value)
		{
			if(DOCUMENT_ROOT_SITE.$value == substr($path, 0, strlen(DOCUMENT_ROOT_SITE.$value)))
				return $dump_public; // Got it
			elseif ($dump_kernel && $path == substr(DOCUMENT_ROOT_SITE.$value, 0, strlen($path)))
				return false; // In case of /bitrix dir need to look deeper
		}
		return $dump_kernel; // Making kernel dump and path not found in $path_kernel - return true = ignore
	}
}

function BaseDump($arc_name="", $tbl_num, $start_row, $stat, $index)
{
	global $DB;

	$ret = array();
	$last_row = $start_row;
	$mem = 32; // Minimum required value

	$sql = "SHOW TABLES;";
	$res = $DB->Query($sql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	$ptab = Array();
	while($row = $res->Fetch())
	{
		$ar = each($row);
		$table = $ar[1];
		$ptab[] = $table;
	}

	$f = fopen($arc_name,"a");
	$i = $tbl_num;

	$dump = "";

	while($i <= (count($ptab) - 1) && haveTime())
	{
		if (strlen($ptab[$i]))
		{
			if($last_row == -1)
			{
				$table = $ptab[$i];
				$drop = $stat || !preg_match("#^b_stat#i",$table); // если не переносим статистику, то только создаём структуру таблицы
				$dump = createTable($ptab[$i], $drop);
				fwrite($f, $dump."\n");
				$next = false;
				$ret["num"] = $i;
				$ret["st_row"] = 0;
				$last_row = 0;
			}

			$res = $DB->Query("SELECT count(*) as count FROM `$ptab[$i]`");
			$row_count = $res->Fetch();

			if($row_count["count"] > 0)
			{
				if($ptab[$i] == 'b_xml_tree')
					$row_next = -1;
				elseif(!$stat && preg_match('#^b_stat#i',$ptab[$i]))
					$row_next = -1;
				elseif(!$index && preg_match("#^(b_search_content_site|b_search_content_group|b_search_content_stem|b_search_content_title|b_search_tags|b_search_content_freq|b_search_content|b_search_suggest)$#i",$ptab[$i]))
					$row_next = -1;
				else
					$row_next = getData($ptab[$i], $f, $row_count["count"], $last_row, $mem);
			}
			else
				$row_next = -1;

			if($row_next == -1)
			{
				$ret["num"] = ++$i;
				$ret["st_row"] = -1;
				$last_row = -1;
			}
			else
			{
				$last_row = $row_next;
				$ret["num"] = $i;
				$ret["st_row"] = $last_row;
			}
		}
	}

	fclose($f);

	if(!($i <= (count($ptab) - 1)))
		$ret["end"] = true;

	return $ret;
}

function getTableSize($prefix)
{
	global $DB;
	if ($DB->type != 'MYSQL')
		return 0;
	$size = 0;

	$sql = "SHOW TABLE STATUS LIKE '".$DB->ForSql($prefix)."%'";
	$res = $DB->Query($sql);

	while($row = $res->Fetch())
		$size += $row["Data_length"];

	return round($size/(1048576), 2);
}

class CDirScan
{
	var $DirCount = 0;
	var $FileCount = 0;
	var $err= array();

	var $bFound = false;
	var $nextPath = '';
	var $startPath = '';
	var $arIncludeDir = false;

	function __construct()
	{
	}

	function ProcessDirBefore($f)
	{
		return true;
	}

	function ProcessDirAfter($f)
	{
		return true;
	}

	function ProcessFile($f)
	{
		return true;
	}

	function Skip($f)
	{
		if ($this->startPath)
		{
			if (strpos($this->startPath.'/', $f.'/') === 0)
			{
				if ($this->startPath == $f)
					unset($this->startPath);
				return false;
			}
			else
				return true;
		}
		return false;
	}

	function Scan($dir)
	{
		$dir = str_replace('\\','/',$dir);

		if ($this->Skip($dir))
			return;

		$this->nextPath = $dir;

		if (is_dir($dir))
		{
		#############################
		# DIR
		#############################
			if (!$this->startPath) // если начальный путь найден или не задан
			{
				$r = $this->ProcessDirBefore($dir);
				if ($r === false)
				{
					$this->err[] = GetMessage('CDIR_FOLDER_ERROR').$dir;
					return false;
				}
			}

			if (!($handle = opendir($dir)))
			{
				$this->err[] = GetMessage('CDIR_FOLDER_OPEN_ERROR').$dir;
				return false;
			}

			while (($item = readdir($handle)) !== false)
			{
				if ($item == '.' || $item == '..')
					continue;

				$f = $dir."/".$item;
				$r = $this->Scan($f);
				if ($r === false || $r === 'BREAK')
				{
					closedir($handle);
					return $r;
				}
			}
			closedir($handle);

			if (!$this->startPath) // если начальный путь найден или не задан
			{
				if ($this->ProcessDirAfter($dir) === false)
				{
					$this->err[] = GetMessage('CDIR_FOLDER_ERROR').$dir;
					return false;
				}
				$this->DirCount++;
			}
		}
		else 
		{
		#############################
		# FILE
		#############################
			$r = $this->ProcessFile($dir);
			if ($r === false)
			{
				$this->err[] = GetMessage('CDIR_FILE_ERROR').$dir;
				return false;
			}
			elseif ($r === 'BREAK') // если файл обработан частично
				return $r;
			$this->FileCount++;
		}
		return true;
	}
}

class CDirRealScan extends CDirScan
{
	function ProcessFile($f)
	{
		global $tar;
		while(haveTime())
		{
			if ($tar->addFile($f) === false)
				return false; // error
			if ($tar->ReadBlockCurrent == 0)
				return true; // finished
		}
		return 'BREAK';
	}

	function ProcessDirBefore($f)
	{
		global $tar;
		return $tar->addFile($f);
	}

	function Skip($f)
	{
		if ($this->startPath)
		{
			if (strpos($this->startPath.'/', $f.'/') === 0)
			{
				if ($this->startPath == $f)
					unset($this->startPath);
				return false;
			}
			else
				return true;
		}

		if ($_REQUEST['skip_symlinks']=='Y' && is_dir($f) && is_link($f))
			return true;

		if (intval($_REQUEST['max_file_size']) > 0 && filesize($f) > $_REQUEST['max_file_size'] * 1024)
			return true;

		return ignorePath($f) || skipMask($f);
	}

}

class CTar
{
	var $gzip;
	var $file;
	var $err = array();
	var $res;
	var $Block = 0;
	var $BlockHeader;
	var $path;
	var $FileCount = 0;
	var $DirCount = 0;
	var $ReadBlockMax = 2000;
	var $ReadBlockCurrent = 0;
	var $header = null;
	var $ArchiveSizeMax;
	var $lang = '';
	const BX_EXTRA = 'BX0000';

	##############
	# READ
	# {
	function openRead($file)
	{
		if (!isset($this->gzip) && (substr($file,-3)=='.gz' || substr($file,-4)=='.tgz'))
			$this->gzip = true;

		return $this->open($file, 'r');
	}

	function readBlock()
	{
		$str = $this->gzip ? gzread($this->res,512) : fread($this->res,512);
		if (!$str && $this->openNext())
			$str = $this->gzip ? gzread($this->res,512) : fread($this->res,512);

		if ($str)
			$this->Block++;

		return $str;
	}

	function SkipFile()
	{
		$this->Skip(ceil($this->header['size']/512));
		$this->header = null;
	}

	function Skip($Block = 0)
	{
		if (!$Block)
			return false;
		$pos = $this->gzip ? gztell($this->res) : ftell($this->res);
		if (file_exists($this->getNextName()))
		{
			while(($BlockLeft = ($this->getArchiveSize($this->file) - $pos)/512) < $Block)
			{
				if ($BlockLeft != floor($BlockLeft))
					return false; // invalid file size
				$this->Block += $BlockLeft;
				$Block -= $BlockLeft;
				if (!$this->openNext())
					return false;
				$pos = 0;
			}
		}

		$this->Block += $Block;
		return 0 === ($this->gzip ? gzseek($this->res,$pos + $Block*512) : fseek($this->res,$pos + $Block*512));
	}

	function readHeader($Long = false)
	{
		$str = '';
		while(trim($str) == '')
			if (!strlen($str = $this->readBlock()))
				return 0; // finish
		if (!$Long)
			$this->BlockHeader = $this->Block - 1;

		if (strlen($str)!=512)
			return $this->Error('TAR_WRONG_BLOCK_SIZE',$this->Block.' ('.strlen($str).')');


		$data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix", $str);
		$chk = $data['devmajor'].$data['devminor'];

		if (!is_numeric(trim($data['checksum'])) || $chk!='' && $chk!=0)
			return $this->Error('TAR_ERR_FORMAT',($this->Block-1).'<hr>Header: <br>'.htmlspecialchars($str)); // быстрая проверка

		$header['filename'] = trim($data['prefix'].'/'.$data['filename'],'/');
		$header['mode'] = OctDec($data['mode']);
		$header['uid'] = OctDec($data['uid']);
		$header['gid'] = OctDec($data['gid']);
		$header['size'] = OctDec($data['size']);
		$header['mtime'] = OctDec($data['mtime']);
		$header['type'] = $data['type'];
//		$header['link'] = $data['link'];

		if (strpos($header['filename'],'./')===0)
			$header['filename'] = substr($header['filename'],2);

		if ($header['type']=='L') // Long header
		{
			$n = ceil($header['size']/512);
			for ($i = 0; $i < $n; $i++)
				$filename .= $this->readBlock();

			$header = $this->readHeader($Long = true);
			$header['filename'] = substr($filename,0,strpos($filename,chr(0)));
		}
		
		if (substr($header['filename'],-1)=='/') // trailing slash
			$header['type'] = 5; // Directory

		if ($header['type']=='5')
			$header['size'] = '';

		if ($header['filename']=='')
			return $this->Error('TAR_EMPTY_FILE',($this->Block-1));

		if (!$this->checkCRC($str, $data))
			return $this->Error('TAR_ERR_CRC',htmlspecialchars($header['filename']));

		$this->header = $header;

		return $header;
	}

	function checkCRC($str, $data)
	{
		$checksum = $this->checksum($str);
		$res = octdec($data['checksum']) == $checksum || $data['checksum']===0 && $checksum==256;
#		if (!$res)
#			var_dump(octdec($data['checksum']) .'=='. $checksum);
		return $res;
	}

	function extractFile()
	{
		if ($this->header === null)
		{
			if(($header = $this->readHeader()) === false || $header === 0 || $header === true)
			{
				if ($header === true)
					$this->SkipFile();
				return $header;
			}

			$this->lastPath = $f = $this->path.'/'.$header['filename'];
		
			if ($this->ReadBlockCurrent == 0)
			{
				if ($header['type']==5) // dir
				{
					if(!file_exists($f) && !self::xmkdir($f))
						return $this->Error('TAR_ERR_FOLDER_CREATE',htmlspecialchars($f));
					//chmod($f, $header['mode']);
				}
				else // file
				{
					if (!self::xmkdir($dirname = dirname($f)))
						return $this->Error('TAR_ERR_FOLDER_CREATE'.htmlspecialchars($dirname));
					elseif (($rs = fopen($f, 'wb'))===false)
						return $this->Error('TAR_ERR_FILE_CREATE',htmlspecialchars($f));
				}
			}
			else
				$this->Skip($this->ReadBlockCurrent);
		}
		else // файл уже частично распакован, продолжаем на том же хите
		{
			$header = $this->header;
			$this->lastPath = $f = $this->path.'/'.$header['filename'];
		}

		if ($header['type'] != 5) // пишем контент в файл 
		{
			if (!$rs)
			{
				if (($rs = fopen($f, 'ab'))===false)
					return $this->Error('TAR_ERR_FILE_OPEN',htmlspecialchars($f));
			}

			$i = 0;
			$FileBlockCount = ceil($header['size'] / 512);
			while(++$this->ReadBlockCurrent <= $FileBlockCount && ($contents = $this->readBlock()))
			{
				if ($this->ReadBlockCurrent == $FileBlockCount && ($chunk = $header['size'] % 512))
					$contents = substr($contents, 0, $chunk);

				fwrite($rs,$contents);

				if ($this->ReadBlockMax && ++$i >= $this->ReadBlockMax)
				{
					fclose($rs);
					return true; // Break
				}
			}
			fclose($rs);

			//chmod($f, $header['mode']);
			if (($s=filesize($f)) != $header['size'])
				return $this->Error('TAR_ERR_FILE_SIZE',htmlspecialchars($header['filename']).' (actual: '.$s.'  expected: '.$header['size'].')');
		}

		if ($this->header['type']==5)
			$this->DirCount++;
		else
			$this->FileCount++;

		$this->debug_header = $this->header;
		$this->BlockHeader = $this->Block;
		$this->ReadBlockCurrent = 0;
		$this->header = null;

		return true;
	}

	function extract()
	{
		while ($r = $this->extractFile());
		return $r === 0;
	}

	function openNext()
	{
		if (file_exists($file = $this->getNextName()))
		{
			$this->close();
			return $this->open($file,$this->mode);
		}
		else
			return false;
	}

	# }
	##############

	##############
	# WRITE 
	# {
	function openWrite($file)
	{
		if (!isset($this->gzip) && (substr($file,-3)=='.gz' || substr($file,-4)=='.tgz'))
			$this->gzip = true;

		if ($this->ArchiveSizeMax > 0)
		{
			while(file_exists($file1 = $this->getNextName($file)))
				$file = $file1;

			$size = 0;
			if (($size = $this->getArchiveSize($file)) >= $this->ArchiveSizeMax)
			{
				$file = $file1;
				$size = 0;
			}
			$this->ArchiveSizeCurrent = $size;
		}
		return $this->open($file, 'a');
	}

	// создадим пустой gzip с экстра полем
	function createEmptyGzipExtra($file)
	{
		if (file_exists($file))
			return false;

		if (!($f = gzopen($file,'wb')))
			return false;
		gzwrite($f,'');
		gzclose($f);

		$data = file_get_contents($file);

		if (!($f = fopen($file, 'w')))
			return false;

		$ar = unpack('A3bin0/A1FLG/A6bin1',substr($data,0,10));
		if ($ar['FLG'] != 0)
			return $this->Error('Error writing extra field: already exists');

		$EXTRA = chr(0).chr(0).chr(strlen(self::BX_EXTRA)).chr(0).self::BX_EXTRA;
		fwrite($f,$ar['bin0'].chr(4).$ar['bin1'].chr(strlen($EXTRA)).chr(0).$EXTRA.substr($data,10));
		fclose($f);
		return true;
	}

	function writeBlock($str)
	{
		$l = strlen($str);
		if ($l!=512)
			return $this->Error('TAR_WRONG_BLOCK_SIZE'.$l);

		if ($this->ArchiveSizeMax && $this->ArchiveSizeCurrent >= $this->ArchiveSizeMax)
		{
			$file = $this->getNextName();
			$this->close();

			if (!$this->open($file,$this->mode))
				return false;

			$this->ArchiveSizeCurrent = 0;
		}

		if ($res = $this->gzip ? gzwrite($this->res, $str) : fwrite($this->res,$str))
		{
			$this->Block++;
			$this->ArchiveSizeCurrent+=512;
		}

		return $res;
	}

	function writeHeader($ar)
	{
		$header0 = pack("a100a8a8a8a12a12", $ar['filename'], decoct($ar['mode']), decoct($ar['uid']), decoct($ar['gid']), decoct($ar['size']), decoct($ar['mtime']));
		$header1 = pack("a1a100a6a2a32a32a8a8a155", $ar['type'],'','','','','','', '', $ar['prefix']);

		$checksum = pack("a8",decoct($this->checksum($header0.'        '.$header1)));
		$header = pack("a512", $header0.$checksum.$header1);
		return $this->writeBlock($header) || $this->Error('TAR_ERR_WRITE_HEADER');
	}

	function addFile($f)
	{
		$f = str_replace('\\', '/', $f);
		$path = substr($f,strlen($this->path) + 1);
		if ($path == '')
			return true;
		if (strlen($path)>512)
			return $this->Error('TAR_PATH_TOO_LONG',htmlspecialchars($path));

		$ar = array();

		if (is_dir($f))
		{
			$ar['type'] = 5;
			$path .= '/';
		}
		else
			$ar['type'] = 0;

		$info = stat($f);
		if ($info)
		{
			if ($this->ReadBlockCurrent == 0) // read from start
			{
				$ar['mode'] = 0777 & $info['mode'];
				$ar['uid'] = $info['uid'];
				$ar['gid'] = $info['gid'];
				$ar['size'] = $ar['type']==5 ? 0 : $info['size'];
				$ar['mtime'] = $info['mtime'];


				if (strlen($path)>100) // Long header
				{
					$ar0 = $ar;
					$ar0['type'] = 'L';
					$ar0['filename'] = '././@LongLink';
					$ar0['size'] = strlen($path);
					if (!$this->writeHeader($ar0))
						return false;
					$path .= str_repeat(chr(0),512 - strlen($path));

					if (!$this->writeBlock($path))
						return false;
					$ar['filename'] = substr($path,0,100);
				}
				else
					$ar['filename'] = $path;

				if (!$this->writeHeader($ar))
					return false;
			}

			if ($ar['type']==0 && $info['size']>0) // File
			{
				if (!($rs = fopen($f, 'rb')))
					return $this->Error('TAR_ERR_FILE_READ',htmlspecialchars($f));

				if ($this->ReadBlockCurrent)
					fseek($rs, $this->ReadBlockCurrent * 512);

				$i = 0;
				while(!feof($rs) && ('' !== $str = fread($rs,512)))
				{
					$this->ReadBlockCurrent++;
					if (feof($rs) && ($l = strlen($str)) && $l < 512)
						$str .= str_repeat(chr(0),512 - $l);

					if (!$this->writeBlock($str))
					{
						fclose($rs);
						return $this->Error('TAR_ERR_FILE_WRITE',htmlspecialchars($f));
					}

					if ($this->ReadBlockMax && ++$i >= $this->ReadBlockMax)
					{
						fclose($rs);
						return true;
					}
				}
				fclose($rs);
				$this->ReadBlockCurrent = 0;
			}
			return true;
		}
		else
			return $this->Error('TAR_ERR_FILE_NO_ACCESS',htmlspecialchars($f));
	}

	# }
	##############

	##############
	# BASE 
	# {
	function open($file, $mode='r')
	{
		$this->file = $file;
		$this->mode = $mode;

		if ($this->gzip) 
		{
			if(!function_exists('gzopen'))
				return $this->Error('TAR_NO_GZIP');
			else
			{
				if ($mode == 'a' && !file_exists($file) && !$this->createEmptyGzipExtra($file))
					return false;
				$this->res = gzopen($file,$mode."b");
			}
		}
		else
			$this->res = fopen($file,$mode."b");

		return $this->res;
	}

	function close()
	{
		if ($this->gzip)
		{
			gzclose($this->res);

			// добавим фактический размер всех несжатых данных в extra поле
			if ($this->mode == 'a')
			{
				$f = fopen($this->file, 'rb+');
#				fseek($f, -4, SEEK_END);
				fseek($f, 18);
				fwrite($f, pack("V", $this->ArchiveSizeCurrent));
				fclose($f);
			}
		}
		else
			fclose($this->res);
	}

	function getNextName($file = '')
	{
		if (!$file)
			$file = $this->file;
		static $CACHE;
		$c = &$CACHE[$file];

		if (!$c)
		{
			$l = strrpos($file, '.');
			$num = substr($file,$l+1);
			if (is_numeric($num))
				$file = substr($file,0,$l+1).++$num;
			else
				$file .= '.1';
			$c = $file;
		}
		return $c;
	}

	function checksum($str)
	{
		static $CACHE;
		$checksum = &$CACHE[md5($str)];
		if (!$checksum)
		{
//			$str = pack("a512",$str);
			for ($i = 0; $i < 512; $i++)
				if ($i>=148 && $i<156)
					$checksum += 32; // ord(' ')
				else
					$checksum += ord($str[$i]);
		}
		return $checksum;
	}

	function getArchiveSize($file = '')
	{
		if (!$file)
			$file = $this->file;
		static $CACHE;
		$size = &$CACHE[$file];

		if (!$size)
		{
			if (!file_exists($file))
				$size = 0;
			else
			{
				if ($this->gzip)
				{
					$f = fopen($file, "rb");
		#			fseek($f, -4, SEEK_END);
					fseek($f, 18);
					$size = end(unpack("V", fread($f, 4)));
					fclose($f);
				}
				else
					$size = filesize($file);
			}
		}
		return $size;
	}

	function Error($err_code, $str = '')
	{
//		echo '<pre>';debug_print_backtrace();echo '</pre>';
//		echo '<pre>';print_r($this);echo '</pre>';

#		if (is_array($this->debug_header))
#			$str .= '<hr>Последний успешный файл: <br><pre>'.(htmlspecialchars(print_r($this->debug_header,1))).'</pre>';
		$this->err[] = self::GetMessage($err_code).' '.$str;
		return false;
	}

	function xmkdir($dir)
	{
		if (!file_exists($dir))
		{
			$upper_dir = dirname($dir);
			if (!file_exists($upper_dir) && !self::xmkdir($upper_dir))
				return false;

			return mkdir($dir);
		}

		return is_dir($dir);
	}

	function GetMessage($code)
	{
		static $arLang;

		if (!$arLang)
		{
			if ($this->lang == 'ru')
				$arLang = array(
					'TAR_WRONG_BLOCK_SIZE' => 'Неверный размер блока: ',
					'TAR_ERR_FORMAT' => 'Архив поврежден, ошибочный блок: ',
					'TAR_EMPTY_FILE' => 'Пустое имя файла, ошибочный блок: ',
					'TAR_ERR_CRC' => 'Ошибка контрольной суммы на файле: ',
					'TAR_ERR_FOLDER_CREATE' => 'Не удалось создать папку: ',
					'TAR_ERR_FILE_CREATE' => 'Не удалось создать файл: ',
					'TAR_ERR_FILE_OPEN' => 'Не удалось открыть файл: ',
					'TAR_ERR_FILE_SIZE' => 'Размер файла отличается: ',
					'TAR_ERR_WRITE_HEADER' => 'Ошибка записи заголовка',
					'TAR_PATH_TOO_LONG' => 'Слишком длинный путь: ',
					'TAR_ERR_FILE_READ' => 'Ошибка чтения файла: ',
					'TAR_ERR_FILE_WRITE' => 'Ошибка записи на файле: ',
					'TAR_ERR_FILE_NO_ACCESS' => 'Нет доступа к файлу: ',
					'TAR_NO_GZIP' => 'Не доступна функция gzopen',
				);
			else
				$arLang = array(
					'TAR_WRONG_BLOCK_SIZE' => 'Wrong block size: ',
					'TAR_ERR_FORMAT' => 'Archive is corrupted, wrong block: ',
					'TAR_EMPTY_FILE' => 'Filename is empty, wrong block: ',
					'TAR_ERR_CRC' => 'Checksum error on file: ',
					'TAR_ERR_FOLDER_CREATE' => 'Can\'t create folder: ',
					'TAR_ERR_FILE_CREATE' => 'Can\'t create file: ',
					'TAR_ERR_FILE_OPEN' => 'Can\'t open file: ',
					'TAR_ERR_FILE_SIZE' => 'File size is wrong: ',
					'TAR_ERR_WRITE_HEADER' => 'Error writing header',
					'TAR_PATH_TOO_LONG' => 'Path is too long: ',
					'TAR_ERR_FILE_READ' => 'Error reading file: ',
					'TAR_ERR_FILE_WRITE' => 'Error writing file: ',
					'TAR_ERR_FILE_NO_ACCESS' => 'No access to file: ',
					'TAR_NO_GZIP' => 'Function &quot;gzopen&quot; is not available',
				);
		}
		return $arLang[$code];
	}

	# }
	##############
}

class CTarCheck extends CTar
{
	function extractFile()
	{
		if(($header = $this->readHeader()) === false || $header === 0)
			return $header;

		$this->SkipFile();
		return true;
	}
}

function haveTime()
{
	return microtime(true) - START_EXEC_TIME < $_REQUEST['max_execution_time'];
}

function workTime()
{
	return microtime(true) - START_EXEC_TIME;
}

function HumanSize($num0, $show0 = false)
{
	$num = $num0;
	$i=0;
	$ar = array(GetMessage('MAIN_DUMP_FILE_MAX_SIZE_b'),GetMessage('MAIN_DUMP_FILE_MAX_SIZE_kb'),GetMessage('MAIN_DUMP_FILE_MAX_SIZE_mb'),GetMessage('MAIN_DUMP_FILE_MAX_SIZE_gb'));
	while($num > 1024)
	{
		$num /= 1024;
		$i++;
	}
	$num = round($num,1);
	return $num." ".$ar[$i].($show0 ? ' ('.$num0.')' : '');
}

function HumanTime($t)
{
	$ar = array(GetMessage('TIME_S'),GetMessage('TIME_M'),GetMessage('TIME_H'));
	if ($t < 60)
		return sprintf('%d '.$ar[0], $t);
	if ($t < 3600)
		return sprintf('%d '.$ar[1].' %d '.$ar[0], floor($t/60), $t%60);
	return sprintf('%d '.$ar[2].'%d '.$ar[1].' %d '.$ar[0], floor($t/3600), floor($t%3600/60), $t%60);
}
?>
