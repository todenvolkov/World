<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!$USER->CanDoOperation('fileman_upload_files'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);
$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');

$strWarning = "";

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

while(($l=strlen($path))>0 && $path[$l-1]=="/")
	$path = substr($path, 0, $l-1);

$path = Rel2Abs("/", $path);
$arPath = Array($site, $path);
$arParsedPath = CFileMan::ParsePath($arPath, true, false, "", $logical == "Y");
$abs_path = $DOC_ROOT.$path;

$bCan = false;

// Check permissions
if(!$USER->CanDoFileOperation('fm_upload_file',$arPath))
	$strWarning = GetMessage("ACCESS_DENIED");
else
{
	$bCan = true;
	if($REQUEST_METHOD=="POST" && strlen($save) > 0 && check_bitrix_sessid())
	{
		$nums = IntVal($nums);
		if($nums > 0)
		{
			for($i = 1; $i <= $nums; $i++)
			{
				$arFile = $HTTP_POST_FILES["file_".$i];
				if(strlen($arFile["name"])<=0 || $arFile["tmp_name"]=="none")
					continue;

				$arFile["name"] = CFileman::GetFileName($arFile["name"]);
				$filename = ${"filename_".$i};
				if(strlen($filename) <= 0)
					$filename = $arFile["name"];

				$pathto = Rel2Abs($path, $filename);
				if(!$USER->CanDoFileOperation('fm_upload_file',Array($site, $pathto)))
				{
					$strWarning .= GetMessage("FILEMAN_FILEUPLOAD_ACCESS_DENIED")." \"".$pathto."\"\n";
				}
				elseif($arFile["error"] == 1 || $arFile["error"] == 2)
				{
					$strWarning .= GetMessage("FILEMAN_FILEUPLOAD_SIZE_ERROR", Array('#FILE_NAME#' => $pathto))."\n";
				}
				elseif(($mess = CFileMan::CheckFileName(str_replace('/', '', $pathto))) !== true)
				{
					$strWarning .= $mess.".\n";
				}
				else if(file_exists($DOC_ROOT.$pathto))
				{
					$strWarning .= GetMessage("FILEMAN_FILEUPLOAD_FILE_EXISTS1")." \"".$pathto."\" ".GetMessage("FILEMAN_FILEUPLOAD_FILE_EXISTS2").".\n";
				}
				elseif(!$USER->IsAdmin() && (in_array(CFileman::GetFileExtension($pathto), CFileMan::GetScriptFileExt()) || substr(CFileman::GetFileName($pathto), 0, 1)=="."))
				{
					$strWarning .= GetMessage("FILEMAN_FILEUPLOAD_PHPERROR")." \"".$pathto."\".\n";
				}
				else
				{
					$bQuota = true;
					if (COption::GetOptionInt("main", "disk_space") > 0)
					{
						$bQuota = false;
						$size = filesize($arFile["tmp_name"]);
						$quota = new CDiskQuota();
						if ($quota->checkDiskQuota(array("FILE_SIZE" => $size)))
							$bQuota = true;
					}
					
					if ($bQuota)
					{
						if(!copy($arFile["tmp_name"], $DOC_ROOT.$pathto))
							$strWarning .= GetMessage("FILEMAN_FILEUPLOAD_FILE_CREATE_ERROR")." \"".$pathto."\"\n";
						elseif(COption::GetOptionInt("main", "disk_space") > 0)
							CDiskQuota::updateDiskQuota("file", $size, "copy");
						@chmod($DOC_ROOT.$pathto, BX_FILE_PERMISSIONS);
					}
					else
						$strWarning .= $quota->LAST_ERROR."\n";
				}
			}
		}

		if(strlen($strWarning) <= 0)
		{
			if (!empty($_POST["apply"]))
				LocalRedirect("/bitrix/admin/fileman_file_upload.php?".$addUrl."&site=".$site."&path=".UrlEncode($path));
			else
				LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path));
		}
	}
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

$APPLICATION->SetTitle(GetMessage("FILEMAN_FILE_UPLOAD_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?CAdminMessage::ShowMessage($strWarning);?>

<?if(strlen($strWarning) <= 0 || $bCan):?>
	<script>
	function NewFileName(ob)
	{
		var str_filename;
		var filename;
		var str_file = ob.value;
		var num = ob.name;
		num  = num.substr(num.lastIndexOf("_")+1);
		str_file = str_file.replace(/\\/g, '/');
		filename = str_file.substr(str_file.lastIndexOf("/")+1);
		document.ffilemanupload["filename_"+num].value = filename;
		if(document.ffilemanupload.nums.value==num)
		{
			num++;
			var tbl = document.getElementById("t");
			var cnt = tbl.rows.length;
			var oRow = tbl.insertRow(cnt);
			var oCell = oRow.insertCell(0);
			oCell.align = 'center';
			oCell.className = 'tablebody1';
			oCell.innerHTML = '<font class="tablebodytext"><input type="text" class="typeinput" name="filename_'+num+'" size="30" maxlength="255" value="">';
			var oCell = oRow.insertCell(1);
			oCell.align = 'center';
			oCell.className = 'tablebody3';
			oCell.innerHTML = '<font class="tablebodytext"><input type="file" class="typeinput" name="file_'+num+'" size="30" maxlength="255" value="" onChange="NewFileName(this)">';

			document.ffilemanupload.nums.value = num;
		}
	}
	</script>
	<form method="POST" action="<?echo $APPLICATION->GetCurPage()."?".$addUrl."&site=".$site."&path=".UrlEncode($path);?>" name="ffilemanupload" enctype="multipart/form-data">
	<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="save" value="Y">
	<?=bitrix_sessid_post()?>

	<?
	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage('FILEMAN_UPL_TAB'), "ICON" => "fileman", "TITLE" => GetMessage('FILEMAN_UPL_TAB_ALT')),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>

	<tr><td colspan="2" align="left">
		<table id="t"><tr><td>
			<?echo GetMessage("FILEMAN_FILEUPLOAD_NAME")?>
		</td>
		<td>
			<?echo GetMessage("FILEMAN_FILEUPLOAD_FILE")?>
		</td>
		</tr>
		<input type="hidden" name="nums" value="5">
		<?for($i=1; $i<=5; $i++):?>
			<tr>
			<td>
				<input type="text" name="filename_<?echo $i?>" size="30" maxlength="255" value="">
			</td>
			<td>
				<input type="file" name="file_<?echo $i?>" size="30" maxlength="255" value="" onChange="NewFileName(this)">
			</td>
			</tr>
		<?endfor?>
	</table></td></tr>
	<?$tabControl->EndTab();
	$tabControl->Buttons(
		array(
			"disabled" => false,
			"back_url" => "/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path)
		)
	);
	$tabControl->End();
	?>
	</form>
<?endif;?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>