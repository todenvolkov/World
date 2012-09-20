<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!$USER->CanDoOperation('fileman_admin_files'))
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
$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$abs_path = $DOC_ROOT.$path;
$arPath = Array($site, $path);

$arFiles = Array();
if(is_array($files))
{
	foreach($files as $ind => $file)
	{
		if(!$USER->CanDoFileOperation('fm_rename_file', Array($site, $path."/".$file)))
			$strWarning .= GetMessage("FILEMAN_RENAME_ACCESS_DENIED")." \"".$file."\".\n";
		else
			$arFiles[$ind] = $file;
	}
}

//проверим права на доступ в этот файл
if(!$USER->CanDoFileOperation('fm_rename_file', $arPath))
	$strWarning .= GetMessage("ACCESS_DENIED");
else if(!file_exists($abs_path))
	$strWarning .= GetMessage("FILEMAN_FILEORFOLDER_NOT_FOUND");
else
{
	if($REQUEST_METHOD=="POST" && strlen($save)>0 && check_bitrix_sessid())
	{
		$pathTmp = $path;
		foreach($arFiles as $ind => $file)
		{
			$newfilename = $filename[$ind];
			if(strlen($newfilename)<=0)
			{
				$strWarning .= GetMessage("FILEMAN_RENAME_NEW_NAME")." \"".$file."\"!\n";
			}
			elseif (($mess = CFileMan::CheckFileName($newfilename)) !== true)
			{
				$strWarning = $mess;
			}
			else
			{
				$pathto = Rel2Abs($path, $newfilename);
				if(!$USER->CanDoFileOperation('fm_create_new_file',Array($site, $pathto)))
					$strWarning .= GetMessage("FILEMAN_RENAME_ACCESS_ERROR")."\n";
				elseif(!$USER->CanDoOperation('edit_php') && // if not admin and renaming from non PHP to PHP
				(substr(CFileman::GetFileName($file), 0, 1)=="." || 
				substr(CFileman::GetFileName($pathto), 0, 1)=="." ||
				(!in_array(CFileman::GetFileExtension($file), CFileMan::GetScriptFileExt()) &&
				in_array(CFileman::GetFileExtension($pathto), CFileMan::GetScriptFileExt()))))
					$strWarning .= GetMessage("FILEMAN_RENAME_TOPHPFILE_ERROR")."\n";
				elseif(!$USER->CanDoOperation('edit_php') // if not admin and renaming from PHP to non PHP
				&& (in_array(CFileman::GetFileExtension($file), CFileMan::GetScriptFileExt()))
				&& (!in_array(CFileman::GetFileExtension($pathto), CFileMan::GetScriptFileExt())))
					$strWarning .= GetMessage("FILEMAN_RENAME_FROMPHPFILE_ERROR")."\n";
				else
				{
					$pathparsedtmp = CFileMan::ParsePath(Array($site, $pathto), false, false, "", $logical == "Y");
					$strWarningTmp = CFileMan::CreateDir($pathparsedtmp["PREV"]);

					if(strlen($strWarningTmp)>0)
						$strWarning .= $strWarningTmp;
					else
					{
						if(!file_exists($DOC_ROOT.$path."/".$file))
							$strWarning .= GetMessage("FILEMAN_RENAME_FILE")." \"".$path."/".$file."\" ".GetMessage("FILEMAN_RENAME_NOT_FOUND")."!\n";
						elseif(!@rename($DOC_ROOT.$path."/".$file, $DOC_ROOT.$pathto))
							$strWarning .= GetMessage("FILEMAN_RENAME_ERROR")." \"".$path."/".$file."\" ".GetMessage("FILEMAN_RENAME_IN")." \"".$pathto."\"!\n";
						else
						{
							$APPLICATION->CopyFileAccessPermission(Array($site, $path."/".$file), Array($site, $pathto));
							$APPLICATION->RemoveFileAccessPermission(Array($site, $path."/".$file));
							$arParsedPathTmp = CFileMan::ParsePath(Array($site, $pathto), false, false, "", $logical == "Y");
							$arFiles[$ind] = $arParsedPathTmp["LAST"];
							$pathTmp = $arParsedPathTmp["PREV"];
						}
					}
				}
			}
		}

		if(strlen($strWarning)<=0)
		{
			$path = $pathTmp;
			$arParsedPath = CFileMan::ParsePath(Array($site, $path), false, false, "", $logical == "Y");
			$abs_path = $DOC_ROOT.$path;
			LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path));
		}
	}
}

$APPLICATION->SetTitle(GetMessage("FILEMAN_RENAME_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<?ShowError($strWarning);?>
<?if(count($arFiles)>0):?>
<form action="fileman_rename.php?lang=<?=LANG?>&path=<?=UrlEncode($path)?>&site=<?=Urlencode($site)?>" method="POST">
	<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="save" value="Y">
	<?foreach($arFiles as $ind => $file):?>
	<input type="hidden" name="files[<?echo htmlspecialchars($ind)?>]" value="<?echo htmlspecialchars($file)?>">
	<input type="text" class="typeinput" name="filename[<?echo htmlspecialchars($ind)?>]" value="<?echo htmlspecialchars($file)?>" size="30" maxlength="255"><br>
	<?endforeach?>
	<p><input type="submit" class="button" name="saveb" value="<?echo GetMessage("FILEMAN_RENAME_SAVE")?>">&nbsp;<input class="button" type="reset" value="<?=GetMessage("FILEMAN_RENAME_RESET")?>" onclick="javascript:window.location='/bitrix/admin/fileman_admin.php?<?=$addUrl?>&site=<?=$site?>&path=<?=UrlEncode($path)?>'"></p>
</form>
<?else://if(count($arFiles)>0):?>
	<font class="text"><?echo GetMessage("FILEMAN_RENAME_LIST_EMPTY")?></font>
<?endif;//if(count($arFiles)>0):?>
<br>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
