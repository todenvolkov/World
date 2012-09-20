<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2005 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'') + '&' + bitrix_sessid_get();

$strWarning = "";

while (($l=strlen($path))>0 && $path[$l-1]=="/")
	$path = substr($path, 0, $l-1);

// если новый файл и задано новое имя
if(strlen($new)>0 && strlen($filename)>0)
	$path = $path."/".$filename;		// присвоим нашему пути это новое имя

$path = Rel2Abs("/", $path);
$site = CFileMan::__CheckSite($site);
if(!$site)
	$site = CSite::GetSiteByFullPath($_SERVER["DOCUMENT_ROOT"].$path);

$template = false;
$db_t = CSite::GetTemplateList($site);
while($ar_t = $db_t->Fetch())
{
	if(strlen($ar_t["CONDITION"])<=0)
	{
		$template = $ar_t["TEMPLATE"];
		break;
	}
}

$DOC_ROOT = CSite::GetSiteDocRoot($site);
$abs_path = $DOC_ROOT.$path;
$arPath = Array($site, $path);

if((strlen($new)<=0 || strlen($filename)<=0) && !file_exists($abs_path))
{
	$p = strrpos($path, "/");
	if($p!==false)
	{
		$new = "Y";
		$filename = substr($path, $p+1);
		$path = substr($path, 0, $p);
	}
}

$bFullPHP = ($full_src=="Y") && $USER->CanDoOperation('edit_php');
$NEW_ROW_CNT = 1;

$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");

$bVarsFromForm = false;		// флаг, указывающий, откуда брать контент из файла или из запостченой формы
$bCheckExecFile = !$USER->CanDoOperation('edit_php') && (strlen($new)>0 || in_array(CFileman::GetFileExtension($path), CFileMan::GetScriptFileExt()));

//Check access to file
if((strlen($new) > 0 &&
!($USER->CanDoOperation('fileman_admin_files') &&
$USER->CanDoFileOperation('fm_create_new_file',$arPath))) ||
(strlen($new) < 0 &&
!$USER->CanDoOperation('fileman_edit_existent_files') ||
!$USER->CanDoFileOperation('fm_edit_existent_file',$arPath)))
{
	$strWarning = GetMessage("ACCESS_DENIED");
}
else
{

	if(strlen($new)>0 && strlen($filename)>0 && file_exists($abs_path))// если мы хотим создать новый файл, но уже такой есть - ругаемся
	{
		$strWarning = GetMessage("FILEMAN_FILEEDIT_FILE_EXISTS")." ";
		$bEdit = false;
		$bVarsFromForm = true;
		$path = Rel2Abs("/", $arParsedPath["PREV"]);
		$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
		$abs_path = $DOC_ROOT.$path;
	}
	elseif(!$USER->IsAdmin() && substr(CFileman::GetFileName($abs_path), 0, 1)==".")
	{
		$strWarning = GetMessage("FILEMAN_FILEEDIT_BAD_FNAME")." ";
		$bEdit = false;
		$bVarsFromForm = true;
		$path = Rel2Abs("/", $arParsedPath["PREV"]);
		$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
		$abs_path = $DOC_ROOT.$path;
	}
	elseif(strlen($new)>0)
	{
		$bEdit = false;
	}
	else
	{
		if(!is_file($abs_path))
			$strWarning = GetMessage("FILEMAN_FILEEDIT_FOLDER_EXISTS")." ";
		else
			$bEdit = true;
	}
}

if(strlen($strWarning)<=0)
{
	if($bEdit)
		$filesrc_tmp = $APPLICATION->GetFileContent($abs_path);
	else
	{
		$arTemplates = CFileman::GetFileTemplates();
		$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[0]["file"]);
		if(strlen($template)>0)
		{
			if (substr($template, 0, 1)=="/")
			{
				$filesrc_tmp = $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].$template);
			}
			else
			{
				for ($i=0; $i<count($arTemplates); $i++)
				{
					if($arTemplates[$i]["file"] == $template)
					{
						$filesrc_tmp = CFileman::GetTemplateContent($arTemplates[$i]["file"]);
						break;
					}
				}
			}
		}
	}

	if($REQUEST_METHOD=="POST" && strlen($save)>0 && strlen($propeditmore)<=0 && check_bitrix_sessid())
	{
		if(!$bFullPHP)
		{
			if(CFileman::IsPHP($filesrc) && $bCheckExecFile)
			{
				$strWarning = GetMessage("FILEMAN_FILEEDIT_CHANGE")." ";
				$bVarsFromForm = true;
				if(strlen($new)>0 && strlen($filename)>0)
				{
					$bEdit = false;
					$path = Rel2Abs("/", $arParsedPath["PREV"]);
					$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
					$abs_path = $DOC_ROOT.$path;
				}
			}
			else
			{
				$res = CFileman::ParseFileContent($filesrc_tmp);
				$prolog = CFileman::SetTitle($res["PROLOG"], $title);
				for ($i = 0; $i<$numpropsvals; $i++)
				{
					if(strlen(Trim($_POST["CODE_".$i]))>0)
					{
						$prolog = CFileman::SetProperty($prolog, Trim($_POST["CODE_".$i]), Trim($_POST["VALUE_".$i]));
					}
					else
					{
						$prolog = CFileman::SetProperty($prolog, Trim($_POST["CODE_".$i]), "");
					}
				}
				$epilog = $res["EPILOG"];
				$filesrc_for_save = $prolog.$filesrc.$epilog;
			}
		}
		else
		{
			$filesrc_for_save = $filesrc;
		}
		
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

		if(strlen($strWarning)<=0)
		{
			if(!$APPLICATION->SaveFileContent($abs_path, $filesrc_for_save))
			{
				if($APPLICATION->GetException())
				{
					$str_err = $APPLICATION->GetException();
					if ($str_err && ($err = $str_err ->GetString()))
					{
						$strWarning = $err;
					}
					$bVarsFromForm = true;
					$path = Rel2Abs("/", $arParsedPath["PREV"]);
					$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
					$abs_path = $DOC_ROOT.$path;
				}
				
				if (empty($strWarning))
				{
					$strWarning = GetMessage("FILEMAN_FILE_SAVE_ERROR")." ";
				}
			}
			else
				$bEdit = true;

			if(strlen($strWarning)<=0 && strlen($apply)<=0)
			{
				if(strlen($back_url)>0 && strpos($back_url, "/bitrix/admin/fileman_fck_edit.php")!==0)
					LocalRedirect("/".ltrim($back_url, "/"));
				LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".Urlencode($site)."&path=".UrlEncode($arParsedPath["PREV"]));
			}
			else
				LocalRedirect("/bitrix/admin/fileman_fck_edit.php?".$addUrl."&site=".Urlencode($site)."&path=".UrlEncode($path).($full_src=="Y"?"&full_src=Y":""));

			$filesrc_tmp = $filesrc_for_save;

			$path = Rel2Abs("/", $path);
			$arParsedPath = CFileMan::ParsePath($path, true, false, "", $logical == "Y");
			$abs_path = $DOC_ROOT.$path;
		}
	}
}

if(strlen($propeditmore)>0) $bVarsFromForm = True;

$bEditProps = false;
if(!$bVarsFromForm)
{
	if(!$bEdit && strlen($filename)<=0)
		$filename = "untitled.php";

	$filesrc = $filesrc_tmp;
	if(!$bFullPHP)
	{
		$res = CFileman::ParseFileContent($filesrc);
		$filesrc = $res["CONTENT"];
		$bEditProps = (strpos($res["PROLOG"], "prolog_before")>0);
		if($bCheckExecFile && CFileman::IsPHP($filesrc))
			$strWarning=GetMessage("FILEMAN_FILEEDIT_CHANGE_ACCESS");

		$title = $res["TITLE"];
		$pprops = $res["PROPERTIES"];
	}
}
elseif($prop_edit=="Y")
	$bEditProps = true;

if($bEdit)
	$APPLICATION->SetTitle(GetMessage("FILEMAN_FILEEDIT_PAGE_TITLE")." \"".htmlspecialchars($arParsedPath["LAST"])."\"");
else
	$APPLICATION->SetTitle(GetMessage("FILEMAN_NEWFILEEDIT_TITLE"));


foreach ($arParsedPath["AR_PATH"] as $chainLevel)
{
	$adminChain->AddItem(
		array(
			"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
			"LINK" => ((strlen($chainLevel["LINK"]) > 0) ? $chainLevel["LINK"] : ""),
		)
	);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<?CAdminMessage::ShowMessage($strWarning);?>
<?if(strlen($strWarning)<=0 || $bVarsFromForm):?>
<?
$aMenu = array();
$aMenu[] = array(
	"TEXT"=>GetMessage("FILEMAN_FILE_VIEW"),
	"LINK"=>"fileman_file_view.php?".$addUrl."&site=".urlencode($site)."&path=".urlencode($path)
);

if($bFullPHP)
{
	$aMenu[] = array(
		"TEXT"=>GetMessage("FILEMAN_FILEEDIT_AS_TXT"),
		"LINK"=>"fileman_file_edit.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($path).(strlen($new)>0? "&amp;new=Y":"").(strlen($back_url)>0? "&amp;back_url=".urlencode($back_url):"")
	);
}
elseif($USER->CanDoOperation('edit_php'))
{
	$aMenu[] = array(
		"TEXT"=>GetMessage("FILEMAN_FILEEDIT_AS_PHP"),
		"LINK"=>"fileman_file_edit.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($path)."&amp;full_src=Y".(strlen($new)>0? "&amp;new=Y":"").(strlen($back_url)>0? "&amp;back_url=".urlencode($back_url):"")
	);
}

if(preg_match('/^\.(.*)?\.menu\.(php|html|php3|php4|php5|phtml)$/', $arParsedPath["LAST"], $regs))
{
	$aMenu[] = array(
		"TEXT"=>GetMessage("FILEMAN_FILEEDIT_AS_MENU"),
		"LINK"=>"fileman_menu_edit.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($arParsedPath["PREV"])."&amp;name=".UrlEncode($regs[1]).(strlen($new)>0? "&amp;new=Y":"").(strlen($back_url)>0? "&amp;back_url=".urlencode($back_url):"")
	);
}
else
{
	$aMenu[] = array(
		"TEXT"=>GetMessage("FILEMAN_FILEEDIT_AS_TXT"),
		"LINK"=>"fileman_file_edit.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($path).(strlen($new)>0? "&amp;new=Y":"").(strlen($back_url)>0? "&amp;back_url=".urlencode($back_url):"")
	);
}

if($bEdit)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	if($USER->CanDoFileOperation('fm_rename_file',$arPath))
	{
		$aMenu[] = array(
			"TEXT"=>GetMessage("FILEMAN_FILEEDIT_RENAME"),
			"LINK"=>"fileman_rename.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($arParsedPath["PREV"])."&amp;files[]=".UrlEncode($arParsedPath["LAST"])
		);
	}

	if($USER->CanDoOperation('fm_download_file') && !in_array(CFileman::GetFileExtension($path), CFileMan::GetScriptFileExt()))
	{
		$aMenu[] = array(
			"TEXT"=>GetMessage("FILEMAN_FILEEDIT_DOWNLOAD"),
			"LINK"=>"fileman_file_download.php?".$addUrl."&amp;site=".Urlencode($site)."&amp;path=".UrlEncode($path)
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<form action="fileman_fck_edit.php?lang=<?=LANG?>&site=<?=Urlencode($site)?>&path=<?=UrlEncode($path)?>" method="post" enctype="multipart/form-data" name="ffilemanedit">
	<input type="hidden" name="logical" value="<?=htmlspecialchars($logical)?>">
	<?if(!$bEdit):?>
	<input type="hidden" name="new" value="y">
	<?endif?>
	<input type="hidden" name="save" value="Y">
	<?if($full_src=="Y"):?>
	<input type="hidden" name="full_src" value="Y">
	<?endif?>
	<input type="hidden" name="template" value="<?echo htmlspecialchars($template)?>">
	<input type="hidden" name="back_url" value="<?=htmlspecialchars($back_url)?>">

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => (($bEdit) ? GetMessage('FILEMAN_EDIT_TAB') : GetMessage('FILEMAN_EDIT_TAB1')), "ICON" => "fileman", "TITLE" => (($bEdit) ? GetMessage('FILEMAN_EDIT_TAB_ALT') : GetMessage('FILEMAN_EDIT_TAB_ALT1'))),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
?>

	<?if(!$bEdit):?>
		<?$arTemplates = CFileman::GetFileTemplates();?>
		<tr><td><font class="tablefieldtext"><?echo GetMessage("FILEMAN_FILEEDIT_TEMPLATE")?></font></td>
		<td><font class="tablebodytext">
		<select name="template" class="typeselect" onchange="window.location='/bitrix/admin/fileman_file_edit.php?lang=<?echo LANG?>&site=<?=Urlencode($site)?>&path=<?echo UrlEncode($path)?>&new=y&template='+escape(this[this.selectedIndex].value)">
			<?for($i=0; $i<count($arTemplates); $i++):?>
			<option value="<?echo htmlspecialchars($arTemplates[$i]["file"])?>"<?if($template==$arTemplates[$i]["file"])echo " selected"?>><?echo htmlspecialchars($arTemplates[$i]["name"])?></option>
			<?endfor;?>
		</select></font></td></tr>

		<tr><td><font class="tablefieldtext"><?echo GetMessage("FILEMAN_FILEEDIT_NAME")?></font></td>
		<td><font class="tablebodytext">
			<input type="text" class="typeinput" name="filename" size="30" maxlength="255" value="<?echo htmlspecialchars($filename)?>">
		</font></td></tr>
	<?endif?>
	<?if(!$bFullPHP):?>
		<tr><td><font class="tablefieldtext"><?echo GetMessage("FILEMAN_FILEEDIT_TITLE")?></font></td>
		<td><font class="tablebodytext">
			<input type="text" class="typeinput" name="title" size="50" maxlength="255" value="<?echo htmlspecialchars($title)?>">
		</font></td></tr>
		<?if($bEditProps):?>
		<input type="hidden" name="prop_edit" value="Y">
		<!-- FILE PROPS -->
		<tr>
			<td valign="top" align="left" nowrap colspan="2"><font class="tablefieldtext"><?echo GetMessage("FILEMAN_EDIT_FILEPROPS")?></font></td>
		</tr>
		<tr>
			<td align="left" colspan="2"><font class="tablebodytext">
				<table border="0" cellspacing="1" cellpadding="3" class="edittable">
					<tr>
						<td class="tablehead" align="center"><font class="tableheadtext"><?echo GetMessage("FILEMAN_EDIT_PROPSCODE")?></font></td>
						<td class="tablehead" align="center"><font class="tableheadtext"><?echo GetMessage("FILEMAN_EDIT_PROPSVAL")?></font></td>
					</tr>
					<?
					$arPropTypes = CFileMan::GetPropstypes($site);

					// sorting
					$pprops_tmp = Array();
					if(is_array($arPropTypes))
					{
						foreach($arPropTypes as $k=>$v)
						{
							if(is_set($pprops, $k))
							{
								$pprops_tmp[$k] = $pprops[$k];
								unset($pprops[$k]);
							}
						}
					}

					if(is_array($pprops))
					{
						foreach($pprops as $k=>$v)
							$pprops_tmp[$k] = $v;
					}
					$pprops = $pprops_tmp;

					if(is_array($pprops))
					{
						foreach ($pprops as $f_CODE => $f_VALUE)
						{
							$ind++;
							$oldind++;
							if($bVarsFromForm)
							{
								$f_CODE = $_POST["CODE_".$oldind];
								$f_VALUE = $_POST["VALUE_".$oldind];
							}

							$bPredefinedProperty = False;
							if(is_set($arPropTypes, $f_CODE))
							{
								$bPredefinedProperty = True;
								$f_CODE_NAME = $arPropTypes[$f_CODE];
								unset($arPropTypes[$f_CODE]);
							}
							?>
							<tr>
								<td class="tablebody" valign="top">
									<?if($bPredefinedProperty):?>
										<input type="hidden" name="CODE_<?echo $ind;?>" value="<?echo htmlspecialchars($f_CODE);?>">
										<input type="text" name="CODE_NAME_<?echo $ind;?>" value="<?echo htmlspecialchars($f_CODE_NAME);?>" size="15" readonly style='background-color:#F1F1F1;'>
										<?echo $f_CODE_NAME;?>
									<?else:?>
										<input type="text" name="CODE_<?echo $ind;?>" value="<?echo htmlspecialchars($f_CODE);?>" size="15">
									<?endif;?>
								</td>
								<td class="tablebody">
									<input type="text" name="VALUE_<?echo $ind;?>" value="<?echo htmlspecialchars($f_VALUE);?>" size="60"><?
									if($APPLICATION->GetDirProperty($f_CODE, Array($site, $path)))
									{
										?><br><font class="text"><small><b><?=GetMessage("FILEMAN_FILE_EDIT_FOLDER_PROP")?></b> <?echo htmlspecialchars($APPLICATION->GetDirProperty($f_CODE, Array($site, $path)));?></small></font><?
									}?>
								</td>
							</tr>
							<?
						}
					}

					$numpropsvals = IntVal($numpropsvals);
					$numnewpropsvals = $numpropsvals-$oldind;
					if($bVarsFromForm && $numnewpropsvals>0)
					{
						for ($i = 0; $i<$numnewpropsvals; $i++)
						{
							$oldind++;
							$f_CODE = $_POST["CODE_".$oldind];
							$f_VALUE = $_POST["VALUE_".$oldind];
							if(strlen($f_CODE)<=0) continue;

							$bPredefinedProperty = False;
							if(is_set($arPropTypes, $f_CODE))
							{
								$bPredefinedProperty = True;
								$f_CODE_NAME = $arPropTypes[$f_CODE];
								unset($arPropTypes[$f_CODE]);
							}

							$ind++;
							?>
							<tr>
								<td class="tablebody" valign="top">
									<?if($bPredefinedProperty):?>
										<input type="hidden" name="CODE_<?echo $ind;?>" value="<?echo htmlspecialchars($f_CODE);?>">
										<input type="text" name="CODE_NAME_<?echo $ind;?>" value="<?echo htmlspecialchars($f_CODE_NAME);?>" size="15" readonly style='background-color:#F1F1F1;'>
										<?echo $f_CODE_NAME;?>
									<?else:?>
										<input type="text" name="CODE_<?echo $ind;?>" value="<?echo htmlspecialchars($f_CODE);?>" size="15">
									<?endif;?>
								</td>
								<td class="tablebody" valign="top"><input type="text" name="VALUE_<?echo $ind;?>" value="<?echo htmlspecialchars($f_VALUE);?>" size="60"><?
									if($APPLICATION->GetDirProperty($f_CODE, Array($site, $path)))
									{
										?><br><font class="text"><small><b><?=GetMessage("FILEMAN_FILE_EDIT_FOLDER_PROP")?></b> <?echo htmlspecialchars($APPLICATION->GetDirProperty($f_CODE, Array($site, $path)));?></small></font><?
									}?></td>
							</tr>
							<?
						}
					}

					if(count($arPropTypes)>0)
					{
						foreach ($arPropTypes as $key => $value)
						{
							$ind++;
							$oldind++;
							$f_CODE = $key;
							$f_CODE_NAME = $value;
							$f_VALUE = "";
							?>
							<tr>
								<td class="tablebody" valign="top">
									<input type="hidden" name="CODE_<?echo $ind;?>" value="<?echo htmlspecialchars($f_CODE);?>">
									<input type="text" name="CODE_NAME_<?echo $ind;?>" value="<?echo htmlspecialchars($f_CODE_NAME);?>" size="15" readonly style='background-color:#F1F1F1;'>
									<?echo $f_CODE_NAME;?>
								</td>
								<td class="tablebody" valign="top"><input type="text" name="VALUE_<?echo $ind;?>" value="<?echo htmlspecialchars($f_VALUE);?>" size="60"><?
									if($APPLICATION->GetDirProperty($f_CODE, Array($site, $path)))
									{
										?><br><font class="text"><small><b><?=GetMessage("FILEMAN_FILE_EDIT_FOLDER_PROP")?></b> <?echo htmlspecialchars($APPLICATION->GetDirProperty($f_CODE, Array($site, $path)));?></small></font><?
									}
									?></td>
							</tr>
							<?
						}
					}

					for ($i=0; $i<$NEW_ROW_CNT; $i++)
					{
						$ind++;
						$oldind++;
						?>
						<tr>
							<td class="tablebody">
								<input type="text" name="CODE_<?echo $ind;?>" value="" size="15">
							</td>
							<td class="tablebody">
								<input type="text" name="VALUE_<?echo $ind;?>" value="" size="60">
							</td>
						</tr>
						<?
					}
					?>
					<tr>
						<td colspan="2" class="tablebody" align="right">
							<input type="hidden" name="numpropsvals" value="<?echo $ind+1; ?>">
							<input type="submit" name="propeditmore" class="button" value="<?echo GetMessage("FILEMAN_EDIT_PROPSMORE")?>">
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<!-- END FILE PROPS -->
		<?endif?>
	<?endif?>
		<tr><td colspan="2">
			<?
			include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/admin/FCKeditor/fckeditor.php");

			$oFCKeditor = new FCKeditor("filesrc") ;
			$oFCKeditor->Width  = '100%' ;
			$oFCKeditor->Height = '400' ;
			$oFCKeditor->Value = $filesrc;
			$oFCKeditor->BasePath = '/bitrix/admin/FCKeditor/';
			$oFCKeditor->Config['AutoDetectLanguage']	= false ;
			$oFCKeditor->Config['DefaultLanguage']		= $lang;
			$oFCKeditor->Config['StylesXmlPath'] = '/bitrix/admin/fileman_fck_styles.php?'.$addUrl.'&template='.$template.'&list=y&site='.$site;
			$oFCKeditor->Config['EditorAreaCSS'] = '/bitrix/admin/fileman_fck_styles.php?'.$addUrl.'&template='.$template.'&site='.$site;
			$oFCKeditor->Config['LinkBrowserURL'] = '/bitrix/admin/fileman_fck_browser.php?'.$addUrl.'&site='.$site;
			$oFCKeditor->Config['LinkBrowserWindowWidth'] = '570';
			$oFCKeditor->Config['LinkBrowserWindowHeight'] = '480';
			$oFCKeditor->Config['ImageBrowserURL'] ='/bitrix/admin/fileman_fck_browser.php?lang='.LANG.'&type=image&site='.$site;
			$oFCKeditor->Config['ImageBrowserWindowWidth'] = '620';
			$oFCKeditor->Config['ImageBrowserWindowHeight'] = '480';
			$oFCKeditor->Config['FlashBrowserURL'] = '/bitrix/admin/fileman_fck_browser.php?lang='.LANG.'&type=flash&site='.$site;
			$oFCKeditor->Config['FlashBrowserWindowWidth'] = '570';
			$oFCKeditor->Config['FlashBrowserWindowHeight'] = '480';
			$oFCKeditor->Config['LinkUploadURL'] = '/bitrix/admin/fileman_fck_upload.php?lang='.LANG.'&site='.$site;
			$oFCKeditor->Config['ImageUploadURL'] = '/bitrix/admin/fileman_fck_upload.php?lang='.LANG.'&type=image&site='.$site;
			$oFCKeditor->Config['FlashUploadURL'] = '/bitrix/admin/fileman_fck_upload.php?lang='.LANG.'&type=flash&site='.$site;

			$oFCKeditor->Create() ;
			?>

<?
$tabControl->EndTab();
$tabControl->Buttons(array(
	"disabled" => false,
	"back_url" => (strlen($back_url)>0 && strpos($back_url, "/bitrix/admin/fileman_fsk_edit.php")!==0) ? htmlspecialchars($back_url) : "/bitrix/admin/fileman_admin.php?".$addUrl."&site=".Urlencode($site)."&path=".UrlEncode($arParsedPath["PREV"])
));
$tabControl->End();
?>
	</form>
<?endif;?>
<br>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
