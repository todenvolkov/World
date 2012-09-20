<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/

// ***** CAdminFileDialog *****
IncludeModuleLangFile(__FILE__);
class CAdminFileDialog
{
	function ShowScript($arConfig)
	{
		CUtil::InitJSCore(array('ajax', 'window'));

		if(CModule::IncludeModule("fileman"))
		{
			$arConfig['path'] = (isset($arConfig['arPath']['PATH']) ? $arConfig['arPath']['PATH'] : '');
			$arConfig['site'] = (isset($arConfig['arPath']['SITE']) ? $arConfig['arPath']['SITE'] : '');
			$arConfig['lang'] = (isset($arConfig['lang']) ? $arConfig['lang'] : LANGUAGE_ID);
			$arConfig['zIndex'] = isset($arConfig['zIndex']) ? $arConfig['zIndex'] : 2500;

			$path = Rel2Abs("/", $arConfig['path']);
			$path = CFileMan::SecurePathVar($path);
			$rootPath = CSite::GetSiteDocRoot($Params['site']);

			while (!file_exists($rootPath.$path) || !is_dir($rootPath.$path))
			{
				$rpos = strrpos($path, '/');
				if ($rpos === false || $rpos < 1)
				{
					$path = '/';
					break;
				}
				$path = rtrim(substr($path, 0, $rpos), " /\\");
			}
			if (!$path || $path == '')
				$path = '/';
			$arConfig['path'] = $path;

			$functionError = "";
			if (!isset($arConfig['event']))
			{
				$functionError .= GetMessage("BX_FD_NO_EVENT").". ";
			}
			else
			{
				$arConfig['event'] = preg_replace("/[^a-zA-Z0-9_]/i", "", $arConfig['event']);
				if (strlen($arConfig['event']) <= 0)
					$functionError .= GetMessage("BX_FD_NO_EVENT").". ";
			}

			$resultDest = "";
			if (!isset($arConfig['arResultDest']) || !is_array($arConfig['arResultDest']))
			{
				$functionError .= GetMessage("BX_FD_NO_RETURN_PRM").". ";
			}
			else
			{
				if (isset($arConfig['arResultDest']["FUNCTION_NAME"]) && strlen($arConfig['arResultDest']["FUNCTION_NAME"]) > 0)
				{
					$arConfig['arResultDest']["FUNCTION_NAME"] = preg_replace("/[^a-zA-Z0-9_]/i", "", $arConfig['arResultDest']["FUNCTION_NAME"]);
					if (strlen($arConfig['arResultDest']["FUNCTION_NAME"]) <= 0)
						$functionError .= GetMessage("BX_FD_NO_RETURN_FNC").". ";
					else
						$resultDest = "FUNCTION";
				}
				elseif (isset($arConfig['arResultDest']["FORM_NAME"]) && strlen($arConfig['arResultDest']["FORM_NAME"]) > 0
					&& isset($arConfig['arResultDest']["FORM_ELEMENT_NAME"]) && strlen($arConfig['arResultDest']["FORM_ELEMENT_NAME"]) > 0)
				{
					$arConfig['arResultDest']["FORM_NAME"] = preg_replace("/[^a-zA-Z0-9_]/i", "", $arConfig['arResultDest']["FORM_NAME"]);
					$arConfig['arResultDest']["FORM_ELEMENT_NAME"] = preg_replace("/[^a-zA-Z0-9_]/i", "", $arConfig['arResultDest']["FORM_ELEMENT_NAME"]);
					if (strlen($arConfig['arResultDest']["FORM_NAME"]) <= 0 || strlen($arConfig['arResultDest']["FORM_ELEMENT_NAME"]) <= 0)
						$functionError .= GetMessage("BX_FD_NO_RETURN_FRM").". ";
					else
						$resultDest = "FORM";
				}
				elseif (isset($arConfig['arResultDest']["ELEMENT_ID"]) && strlen($arConfig['arResultDest']["ELEMENT_ID"]) > 0)
				{
					$arConfig['arResultDest']["ELEMENT_ID"] = preg_replace("/[^a-zA-Z0-9_]/i", "", $arConfig['arResultDest']["ELEMENT_ID"]);
					if (strlen($arConfig['arResultDest']["ELEMENT_ID"]) <= 0)
						$functionError .= GetMessage("BX_FD_NO_RETURN_ID").". ";
					else
						$resultDest = "ID";
				}
				else
				{
					$functionError .= GetMessage("BX_FD_BAD_RETURN").". ";
				}
			}
		}
		else
		{
			$functionError = GetMessage("BX_FD_NO_FILEMAN");
		}

		if (strlen($functionError) <= 0)
		{
			?>
			<script>
			var mess_SESS_EXPIRED = '<?=GetMessage('BX_FD_ERROR').': '.GetMessage('BX_FD_SESS_EXPIRED')?>';
			var mess_ACCESS_DENIED = '<?=GetMessage('BX_FD_ERROR').': '.GetMessage('BX_FD_NO_PERMS')?>';
			window.<?= CUtil::JSEscape($arConfig['event'])?> = function(bLoadJS)
			{
				<?if(!$GLOBALS['USER']->CanDoOperation('fileman_view_file_structure')):?>
					return alert(mess_ACCESS_DENIED);
				<?else:?>
				<?
				$fd_config = stripslashes(CUserOptions::GetOption("fileman", "file_dialog_config", "N"));
				if ($fd_config == "N" || $arConfig['saveConfig'] === false)
				{
				?>
					var UserConfig =
					{
						site : '<?= CUtil::JSEscape($arConfig['site'])?>',
						path : '<?= CUtil::JSEscape($arConfig['path'])?>',
						view : "list",
						sort : "type",
						sort_order : "asc"
					};
				<?
				}
				else
				{
					$res = explode(";", $fd_config);
					if ($res[0])
						$arConfig['site'] = $res[0];
					if ($res[1])
						$arConfig['path'] = rtrim($res[1], " /\\");

					if (!file_exists($rootPath.$arConfig['path']) && !is_dir($rootPath.$arConfig['path']))
						$arConfig['path'] = '/';
					?>
					var UserConfig =
					{
						site : '<?= CUtil::JSEscape($arConfig['site'])?>',
						path : '<?= CUtil::JSEscape($arConfig['path'])?>',
						view : '<?= CUtil::JSEscape($res[2])?>',
						sort : '<?= CUtil::JSEscape($res[3])?>',
						sort_order : '<?= CUtil::JSEscape($res[4])?>'
					};
					<?
				}
				?>

				if (!window.BXFileDialog)
				{
					if (bLoadJS !== false)
						jsUtils.loadJSFile("/bitrix/js/main/file_dialog.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/file_dialog.js')?>");
					return setTimeout(function(){window['<?= CUtil::JSEscape($arConfig['event'])?>'](false)}, 50);
				}

				var oConfig =
				{
					submitFuncName : '<?= CUtil::JSEscape($arConfig['event'])?>Result',
					select : '<?= CUtil::JSEscape($arConfig['select'])?>',
					operation: '<?= CUtil::JSEscape($arConfig['operation'])?>',
					showUploadTab : <?= $arConfig['showUploadTab'] ? 'true' : 'false';?>,
					showAddToMenuTab : <?= $arConfig['showAddToMenuTab'] ? 'true' : 'false';?>,
					site : '<?= CUtil::JSEscape($arConfig['site'])?>',
					path : '<?= CUtil::JSEscape($arConfig['path'])?>',
					lang : '<?= CUtil::JSEscape($arConfig['lang'])?>',
					fileFilter : '<?= CUtil::JSEscape($arConfig['fileFilter'])?>',
					allowAllFiles : <?= $arConfig['allowAllFiles'] !== false ? 'true' : 'false';?>,
					saveConfig : <?= $arConfig['saveConfig'] !== false ? 'true' : 'false';?>,
					sessid: "<?=bitrix_sessid()?>",
					checkChildren: true,
					genThumb: <?= COption::GetOptionString("fileman", "file_dialog_gen_thumb", "Y") == 'Y' ? 'true' : 'false';?>,
					zIndex: <?= CUtil::JSEscape($arConfig['zIndex'])?>
				};

				if(window.oBXFileDialog && window.oBXFileDialog.UserConfig)
				{
					UserConfig = oBXFileDialog.UserConfig;
					oConfig.path = UserConfig.path;
					oConfig.site = UserConfig.site;
				}

				oBXFileDialog = new BXFileDialog();
				oBXFileDialog.Open(oConfig, UserConfig);
				<?endif;?>
			};
			window.<?= CUtil::JSEscape($arConfig['event'])?>Result = function(filename, path, site, title, menu)
			{
				path = jsUtils.trim(path);
				path = path.replace(/\\/ig,"/");
				path = path.replace(/\/\//ig,"/");
				if (path.substr(path.length-1) == "/")
					path = path.substr(0, path.length-1);
				var full = path + '/' + filename;
				if (path == '')
					path = '/';

				if ('<?= CUtil::JSEscape($arConfig['select'])?>' == 'D')
					name = full;

				<?if ($resultDest == "FUNCTION"): ?>
					<?= CUtil::JSEscape($arConfig['arResultDest']["FUNCTION_NAME"])?>(filename, path, site, title || '', menu || '');
				<?elseif($resultDest == "FORM"): ?>
					document.<?= CUtil::JSEscape($arConfig['arResultDest']["FORM_NAME"])?>.<?= CUtil::JSEscape($arConfig['arResultDest']["FORM_ELEMENT_NAME"])?>.value = full;
				<?elseif($resultDest == "ID"): ?>
					BX('<?= CUtil::JSEscape($arConfig['arResultDest']["ELEMENT_ID"])?>').value = full;
				<?endif;?>
			};
			<?self::AttachJSScripts();?>
			</script>
			<?
		}
		else
		{
			echo "<font color=\"#FF0000\">".htmlspecialchars($functionError)."</font>";
		}
	}

	function AttachJSScripts()
	{
		if(!defined("BX_B_FILE_DIALOG_SCRIPT_LOADED"))
		{
			define("BX_B_FILE_DIALOG_SCRIPT_LOADED", true);
?>
if (window.jsUtils)
{
	jsUtils.addEvent(window, 'load', function(){jsUtils.loadJSFile("/bitrix/js/main/file_dialog.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/file_dialog.js')?>");}, false);
}
<?
		}
	}

	function Start($Params)
	{
		$arSites = Array();
		$dbSitesList = CSite::GetList($b = "SORT", $o = "asc");
		$arSitesPP = Array();
		while($arSite = $dbSitesList->GetNext())
		{
			$arSites[$arSite["ID"]] = $arSite["NAME"] ? $arSite["NAME"] : $arSite["ID"];
			$arSitesPP[] = array(
				"ID" => $arSite["ID"],
				"TEXT" => '['.$arSite["ID"].'] '.$arSite["NAME"],
				"ONCLICK" => "oBXDialogControls.SiteSelectorOnChange('".$arSite["ID"]."')",
				"ICON" => ($arSite["ID"] == $Params['site']) ? 'checked' : ''
			);
		}

		$Params['arSites'] = $arSites;
		$Params['arSitesPP'] = $arSitesPP;
		$Params['site'] = ($Params['site'] && isset($arSites[$Params['site']])) ? $Params['site'] : key($arSites); // Secure site var
		if (!in_array(strtolower($Params['lang']), array('en', 'ru'))) // Secure lang var
		{
			$res = CLanguage::GetByID($Params['lang']);
			if($lang = $res->Fetch())
				$Params['lang'] = $lang['ID'];
			else
				$Params['lang'] = 'en';
		}

		if ($Params['bAddToMenu'])
		{
			$armt = self::GetMenuTypes($Params['site'], $Params['path']);
			$Params['arMenuTypes'] = $armt[0];
			$Params['arMenuTypesScript'] = $armt[1];
			$Params['menuItems'] = $armt[2];
		}

		self::BuildDialog($Params);
		self::ShowJS($Params);
	}

	function LoadItems($Params)
	{
		global $APPLICATION;

		echo '<script>';
		if ($Params['bAddToMenu'])
			self::GetMenuTypes($Params['site'], $Params['path'], true);

		if ($Params['loadRecursively'])
			self::GetItemsRecursively(array('path' => $Params['path'], 'site' => $Params['site'], 'bCheckEmpty' => true, 'getFiles' => $Params['getFiles'], 'loadRoot' => $Params['loadRoot'], 'bThrowException' => true));
		else
			self::GetItems(array('path' => $Params['path'], 'site' => $Params['site'], 'bCheckEmpty' => true, 'getFiles' => $Params['getFiles']));

		if ($e = $APPLICATION->GetException())
			echo 'window.action_warning = "'.addslashes($e->GetString()).'";';
		else
			echo 'window.load_items_correct = true;';

		echo '</script>';
	}

	function BuildDialog($Params)
	{
		$arSites = $Params['arSites'];
		if (count($arSites) > 1) // Site selector
		{
			$u = new CAdminPopup("fd_site_list", "fd_site_list", $Params['arSitesPP'], array('zIndex' => 2500, 'dxShadow' => 0));
			$u->Show();
		}
		?>
<form id="file_dialog" name="file_dialog" onsubmit="return false;">
<table class="bx-file-dialog">
<tr>
	<td class= "bxfd-cntrl-cell">
		<div id="__bx_fd_top_controls_container">
			<table class="bx-fd-top-contr-tbl">
				<tr>
					<?if (count($arSites) > 1):?>
						<td style="width:26px !important; padding: 0px 4px 0px 5px !important;">
						<div id="__bx_site_selector" bxvalue='<?= CUtil::JSEscape($Params['site'])?>' onclick="oBXDialogControls.SiteSelectorOnClick(this);" class="fd_iconkit site_selector_div"><span><?= strtoupper(CUtil::JSEscape($Params['site']))?></span></div>
						</td>
					<?endif;?>
					<td style="width:320px !important; padding: 0px 2px 0px 2px !important;">
						<input class="fd_input" type="text" id="__bx_dir_path_bar"></input>
					</td>
					<td style="width:170px !important; padding: 0px 2px 0px 2px !important;">
						<img src="/bitrix/images/1.gif" class="fd_iconkit go_button" id="__bx_dir_path_go" title="<?=GetMessage("FD_GO_TO")?>"/>
						<img src="/bitrix/images/1.gif" __bx_disable="Y" class="fd_iconkit path_back_dis" title="<?=GetMessage("FD_GO_BACK")?>" id="__bx_dir_path_back"/>
						<img src="/bitrix/images/1.gif" __bx_disable="Y" class="fd_iconkit path_forward_dis" title="<?=GetMessage("FD_GO_FORWARD")?>" id="__bx_dir_path_forward"/>
						<img src="/bitrix/images/1.gif" class="fd_iconkit dir_path_up" title="<?=GetMessage("FD_GO_UP")?>" id="__bx_dir_path_up" />
						<img src="/bitrix/images/1.gif" class="fd_iconkit dir_path_root" title="<?=GetMessage("FD_GO_TO_ROOT")?>" id="__bx_dir_path_root" />
						<img src="/bitrix/images/1.gif" class="fd_iconkit new_dir" title="<?=GetMessage("FD_NEW_FOLDER")?>" id="__bx_new_dir" />
						<img src="/bitrix/images/1.gif" class="fd_iconkit refresh" title="<?=GetMessage("FD_REFRESH")?>" onclick="oBXDialogControls.RefreshOnclick(this);"/>
						<?
						$arSitesPP = Array();
						$arViews = Array(
							Array("ID" => 'list', "TEXT" => GetMessage("FD_VIEW_LIST"), "ONCLICK" => "oBXDialogControls.ViewSelector.OnChange('list')"),
							Array("ID" => 'detail', "TEXT" => GetMessage("FD_VIEW_DETAIL"), "ONCLICK" => "oBXDialogControls.ViewSelector.OnChange('detail')"),
							Array("ID" => 'preview', "TEXT" => GetMessage("FD_VIEW_PREVIEW"), "ONCLICK" => "oBXDialogControls.ViewSelector.OnChange('preview')")
						);
						$u = new CAdminPopup("fd_view_list", "fd_view_list", $arViews, array('zIndex' => 2500, 'dxShadow' => 0));
						$u->Show();
						?>
						<img onclick="oBXDialogControls.ViewSelector.OnClick();" src="/bitrix/images/1.gif" id="__bx_view_selector" class="fd_iconkit view_selector"  title="<?=GetMessage("FD_SELECT_VIEW")?>"/>
					</td>
					<td style="width:180px !important; padding: 0px 6px 0px 3px !important; text-align:right !important;" align="right">
						<?=GetMessage("FD_SORT_BY")?>:
						<select class="fd_select" id="__bx_sort_selector" title="<?=GetMessage("FD_SORT_BY")?>" style="font-size:11px !important;">
							<option value="name"><?=GetMessage("FD_SORT_BY_NAME")?></option>
							<option value="type"><?=GetMessage("FD_SORT_BY_TYPE")?></option>
							<option value="size"><?=GetMessage("FD_SORT_BY_SIZE")?></option>
							<option value="date"><?=GetMessage("FD_SORT_BY_DATE")?></option>
						</select>
					</td>
					<td style="width:20px !important; padding: 0px 6px 0px 3px !important;">
						<img src="/bitrix/images/1.gif" class="fd_iconkit sort_up" title="<?=GetMessage("FD_CHANGE_SORT_ORDER")?>" __bx_value="asc" id="__bx_sort_order" />
					</td>
				</tr>
			</table>
		</div>
	</td>
</tr>
<tr>
	<td style="vertical-align:top !important; height:398px !important;">
		<div id="__bx_fd_tree_and_window" style="display:block">
			<table style="width:743px !important; height:250px !important;">
				<tr>
					<td class="bxfd-tree-cont">
						<div id="__bx_treeContainer" class="fd_window bxfd-tree-cont-div"></div>
					</td>
					<td class="bxfd-window-cont">
						<div class="fd_window" ><div id="__bx_windowContainer" class="bxfd-win-cont"></div></div>
					</td>
				</tr>
			</table>
		</div>
		<div id="__bx_fd_preview_and_panel" style="display:block;">
			<table style="width:100% !important;height:132px !important; padding:0px !important;" border="0">
				<tr>
					<td style="width:25% !important; height: 100% !important;">
							<div style="margin: 3px 8px 3px 5px;border:1px solid #C6C6C6"><div style="height:127px;">
							<div id="bxfd_previewContainer"></div>
							<div id="bxfd_addInfoContainer"></div>
						</div></div>
					</td>
					<td style="width:70% !important; vertical-align:top !important;">
						<div class="bxfd-save-cont">
							<table>
								<tr>
									<td class="bxfd-sc-cell" colspan="2">
										<input type="text" style="width:98% !important;margin-bottom:5px !important;" id="__bx_file_path_bar">
										<select style="width:98% !important; display:none; margin-bottom:5px !important;" id="__bx_file_filter"></select>
										<div id="__bx_page_title_cont" style="display:none;">
										<?=GetMessage('FD_PAGE_TITLE')?>:<br/>
										<input type="text" style="width:98% !important;" id="__bx_page_title1">
										</div>
									</td>
								</tr>
								<tr>
									<td class="bxfd-sc-cell2">
										<table id="add2menu_cont" style="display:none"><tr>
											<td><input type="checkbox" id="__bx_fd_add_to_menu"></td>
											<td><label for="__bx_fd_add_to_menu"><?=GetMessage("FD_ADD_PAGE_2_MENU")?></label></td>
										</tr></table>
									</td>
									<td  class="bxfd-sc-cell3">
										<input style="width:100px !important;" type="button" id="__bx_fd_submit_but" value="">
										<input style="width:100px !important;" type="button" onclick="oBXFileDialog.Close()" value="<?=GetMessage("FD_BUT_CANCEL");?>">
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div id="__bx_fd_load" style="display:none;">
			<div id="bxfd_upload_container"><iframe id="bxfd_iframe_upload" src="javascript:''" frameborder="0"></iframe></div>
		</div>
		<div id="__bx_fd_container_add2menu" class="bxfd-add-2-menu-tab"><? if ($Params['bAddToMenu']) :?><table class="bx-fd-add-2-menu-tbl">
			<tr>
				<td style="height:30px">
					<table class="fd_tab_title">
						<tr>
							<td class="icon"><img class="bxfd-add-to-menu-icon" src="/bitrix/images/1.gif" width="32" height="32"/></td>
							<td class="title"><?=GetMessage("FD_ADD_PAGE_2_MENU_TITLE")?></td>
						</tr>
						<tr>
							<td colspan="2" class="delimiter"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="height:310px !important; vertical-align:top !important;">
					<table id="add2menuTable" class="bxfd-add-2-menu-tbl">
						<tr>
							<td style="width:200px !important; text-align:right !important;"><?=GetMessage("FD_FILE_NAME")?></td>
							<td style="width:250px !important;" id="__bx_fd_file_name"></td>
						</tr>
						<tr>
							<td align="right"><?=GetMessage("FD_PAGE_TITLE")?>:</td>
							<td><input type="text" id="__bx_page_title2" value=""></input></td>
						</tr>
						<tr>
							<td align="right"><?=GetMessage("FD_MENU_TYPE")?></td>
							<td>
								<select id="__bx_fd_menutype" name="menutype">
								<?for($i = 0; $i < count($Params['arMenuTypes']); $i++): ?>
								<option value='<?= CUtil::JSEscape($Params['arMenuTypes'][$i]['key'])?>'><?= CUtil::JSEscape($Params['arMenuTypes'][$i]['title'])?></option>
								<? endfor;?>
								</select>
							</td>
						</tr>
						<tr id="e0">
							<td style="vertical-align:top !important; text-align:right !important;"><?=GetMessage("FD_MENU_POINT")?></td>
							<td>
								<input type="radio" name="itemtype" id="__bx_fd_itemtype_n" value="n" checked> <label for="__bx_fd_itemtype_n"><?=GetMessage("FD_ADD_NEW")?></label><br>
								<input type="radio" name="itemtype" id="__bx_fd_itemtype_e" value="e"> <label for="__bx_fd_itemtype_e"><?=GetMessage("FD_ATTACH_2_EXISTENT")?></label>
							</td>
						</tr>
						<tr id="__bx_fd_e1">
							<td align="right"><?=GetMessage("FD_NEW_ITEM_NAME")?></td>
							<td><input type="text" name="newp" id="__bx_fd_newp" value=""></td>
						</tr>
						<tr id="__bx_fd_e2">
							<td align="right"><?=GetMessage("FD_ATTACH_BEFORE")?></td>
							<td>
								<select name="newppos" id="__bx_fd_newppos">
									<?for($i = 0; $i < count($Params['menuItems']); $i++):?>
									<option value="<?= $i + 1 ?>"><?= CUtil::JSEscape($Params['menuItems'][$i])?></option>
									<?endfor;?>
									<option value="0" selected="selected"><?=GetMessage("FD_LAST_POINT")?></option>
								</select>
							</td>
						</tr>
						<tr id="__bx_fd_e3" style="display:none;">
							<td  align="right"><?=GetMessage("FD_ATTACH_2_ITEM")?></td>
							<td>
								<select name="menuitem" id="__bx_fd_menuitem">
									<?for($i = 0; $i < count($Params['menuItems']); $i++):?>
									<option value="<?= $i + 1 ?>"><?= CUtil::JSEscape($Params['menuItems'][$i])?></option>
									<?endfor;?>
								</select>
							</td>
						</tr>

						<tr>
							<td>
							</td>
							<td>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="bx-fd-buttons-cont">
					<input type="button" id="__bx_fd_submit_but2" value=""></input>
					<input type="button" onclick="oBXFileDialog.Close()" value="<?=GetMessage("FD_BUT_CANCEL");?>"></input>
				</td>
			</tr>
		</table><?endif;?></div>
	</td>
</tr>
<tr>
	<td id="__bx_tab_cont" style="background-color: #D7D7D7;"></td>
</tr>
</table>
</form>
<div id="__bx_get_real_size_cont"></div>
		<?
	}

	function ShowJS($Params)
	{
		global $APPLICATION;
		$fd_engine_js_src = '/bitrix/js/main/file_dialog_engine.js';
		$fd_css_src = '/bitrix/themes/.default/file_dialog.css';
		$arSites = $Params['arSites'];
		?>
<script>
BXSite = "<?= CUtil::JSEscape($Params['site'])?>";
BXLang = "<?= CUtil::JSEscape($Params['lang'])?>";
if (!window.arFDDirs || !window.arFDFiles || !window.arFDPermission)
{
	arFDDirs = {};
	arFDFiles = {};
	arFDPermission = {};
}
if (!window.arFDMenuTypes)
	arFDMenuTypes = {};
<?
		if ($Params['arMenuTypesScript'])
			echo $Params['arMenuTypesScript'];

		self::GetItemsRecursively(array('path' => $Params['path'], 'site' => $Params['site'], 'bCheckEmpty' => true, 'getFiles' => $Params['getFiles'], 'loadRoot' => true, 'bFindCorrectPath' => true, 'bThrowException' => false));

		if ($e = $APPLICATION->GetException())
			echo 'alert("'.CUtil::JSEscape($e->GetString()).'");';
?>

// Sites array
var arSites = [];
<?foreach ($arSites as $key => $val):?>
arSites['<?= CUtil::JSEscape($key)?>'] = '<?= CUtil::JSEscape($val)?>';
<?endforeach;?>

<?self::AppendLangMess();?>
function OnLoad()
{
	if (!window.BXWaitWindow || !window.BXDialogTree || !window.BXDialogWindow)
		return setTimeout(function(){OnLoad();}, 20);

	window.oWaitWindow = new BXWaitWindow();
	window.oBXDialogTree = new BXDialogTree();
	if(oBXFileDialog.oConfig.operation == 'S' && oBXFileDialog.oConfig.showAddToMenuTab)
		window.oBXMenuHandling = new BXMenuHandling();
	window.oBXDialogControls = new BXDialogControls();
	window.oBXDialogWindow = new BXDialogWindow();
	window.oBXDialogTabs = new BXDialogTabs();
	window.oBXFDContextMenu = false;

	if (oBXFileDialog.oConfig.operation == 'O' && oBXFileDialog.oConfig.showUploadTab)
	{
		oBXDialogTabs.AddTab('tab1', '<?= GetMessage("FD_OPEN_TAB_TITLE")?>', _Show_tab_OPEN, true);
		oBXDialogTabs.AddTab('tab2', '<?= GetMessage("FD_LOAD_TAB_TITLE")?>',_Show_tab_LOAD, false);
	}
	else if(oBXFileDialog.oConfig.operation == 'S' && oBXFileDialog.oConfig.showAddToMenuTab)
	{
		oBXDialogTabs.AddTab('tab1', '<?= GetMessage("FD_SAVE_TAB_TITLE")?>', _Show_tab_SAVE, true);
		oBXDialogTabs.AddTab('tab2', '<?= GetMessage("FD_MENU_TAB_TITLE")?>', _Show_tab_MENU, false);
		BX('add2menu_cont').style.display = 'block';
	}
	oBXDialogTabs.DisplayTabs();

	oBXDialogTree.Append();
	oBXFileDialog.SubmitFileDialog = SubmitFileDialog;
}

// Append CSS
if (!window.fd_styles_link || !window.fd_styles_link.parentNode)
	window.fd_styles_link = jsUtils.loadCSSFile("<?=$fd_css_src.'?v='.@filemtime($_SERVER['DOCUMENT_ROOT'].$fd_css_src)?>");

// Append file with File Dialog engine
if (!window.BXDialogTree)
	jsUtils.loadJSFile("<?=$fd_engine_js_src.'?v='.@filemtime($_SERVER['DOCUMENT_ROOT'].$fd_engine_js_src)?>", OnLoad);
else
	OnLoad();

</script>
<?
	}

	function AppendLangMess()
	{
		//*  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *
		// FD_MESS - Array of messages for JS files
		//*  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *  *
		?>
var FD_MESS =
{
	FD_SAVE_TAB_TITLE : '<?=GetMessage('FD_SAVE_TAB_TITLE')?>',
	FD_OPEN_DIR : '<?=GetMessage('FD_OPEN_DIR')?>',
	FD_OPEN_TAB_TITLE : '<?=GetMessage('FD_OPEN_TAB_TITLE')?>',
	FD_CLOSE : '<?=GetMessage('FD_CLOSE')?>',
	FD_SORT_SIZE : '<?=GetMessage('FD_SORT_SIZE')?>',
	FD_SORT_DATE : '<?=GetMessage('FD_SORT_DATE')?>',
	FD_SORT_NAME : '<?=GetMessage('FD_SORT_NAME')?>',
	FD_SORT_TYPE : '<?=GetMessage('FD_SORT_TYPE')?>',
	FD_BUT_OPEN : '<?=GetMessage('FD_BUT_OPEN')?>',
	FD_BUT_SAVE : '<?=GetMessage('FD_BUT_SAVE')?>',
	FD_ALL_FILES : '<?=GetMessage('FD_ALL_FILES')?>',
	FD_ALL_IMAGES : '<?=GetMessage('FD_ALL_IMAGES')?>',
	FD_BYTE : '<?=GetMessage('FD_BYTE')?>',
	FD_EMPTY_FILENAME : '<?=GetMessage('FD_EMPTY_FILENAME')?>',
	FD_INPUT_NEW_PUNKT_NAME : '<?=GetMessage('FD_INPUT_NEW_PUNKT_NAME')?>',
	FD_LAST_POINT : '<?=GetMessage('FD_LAST_POINT')?>',
	FD_NEWFOLDER_EXISTS : '<?=GetMessage('FD_NEWFOLDER_EXISTS')?>',
	FD_NEWFILE_EXISTS : '<?=GetMessage('FD_NEWFILE_EXISTS')?>',
	FD_RENAME : '<?=GetMessage('FD_RENAME')?>',
	FD_DELETE : '<?=GetMessage('FD_DELETE')?>',
	FD_RENAME_TITLE : '<?=GetMessage('FD_RENAME_TITLE')?>',
	FD_DELETE_TITLE : '<?=GetMessage('FD_DELETE_TITLE')?>',
	FD_CONFIRM_DEL_DIR : '<?=GetMessage('FD_CONFIRM_DEL_DIR')?>',
	FD_CONFIRM_DEL_FILE : '<?=GetMessage('FD_CONFIRM_DEL_FILE')?>',
	FD_EMPTY_NAME : '<?=GetMessage('FD_EMPTY_NAME')?>',
	FD_INCORRECT_NAME : '<?=GetMessage('FD_INCORRECT_NAME')?>',
	FD_LOADIND : '<?=GetMessage('FD_LOADING')?>...',
	FD_EMPTY_NAME : '<?=GetMessage('FD_EMPTY_NAME')?>',
	FD_INCORRECT_EXT : '<?=GetMessage('FD_INCORRECT_EXT')?>',
	FD_LOAD_EXIST_CONFIRM : '<?=GetMessage('FD_LOAD_EXIST_CONFIRM')?>',
	FD_SESS_EXPIRED : '<?=GetMessage('BX_FD_ERROR').': '.GetMessage('BX_FD_SESS_EXPIRED')?>',
	FD_ERROR : '<?=GetMessage('BX_FD_ERROR')?>',
	FD_FILE : '<?=GetMessage('FD_FILE')?>',
	FD_FOLDER : '<?=GetMessage('FD_FOLDER')?>',
	FD_IMAGE : '<?=GetMessage('FD_IMAGE')?>'
};
<?
	}

	function GetMenuTypes($site, $path, $bEchoResult = false)
	{
		global $USER, $APPLICATION;

		if(!CModule::IncludeModule("fileman"))
			return $APPLICATION->ThrowException(GetMessage("BX_FD_NO_FILEMAN"));

		$path = Rel2Abs("/", $path);
		$path = CFileMan::SecurePathVar($path);
		$path = rtrim($path, " /\\");

		$armt = GetMenuTypes($site);
		$arAllItems = Array();
		$arMenuTypes = Array();
		$strSelected = "";
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		foreach($armt as $key => $title)
		{
			$menuname = $path."/.".$key.".menu.php";
			if(!$USER->CanDoFileOperation('fm_view_file', Array($site, $menuname)))
				continue;

			$arItems = Array();

			$res = CFileMan::GetMenuArray($DOC_ROOT.$menuname);
			$aMenuLinksTmp = $res["aMenuLinks"];

			$itemcnt = 0;
			for($j=0; $j<count($aMenuLinksTmp); $j++)
			{
				$aMenuLinksItem = $aMenuLinksTmp[$j];
				$arItems[] = htmlspecialchars($aMenuLinksItem[0]);
			}
			$arAllItems[$key] = $arItems;
			if($strSelected == "")
				$strSelected = $key;
			$arMenuTypes[] = array('key' => $key, 'title' => $title." [".$key."]");
		}

		$arTypes = array_keys($arAllItems);
		$strTypes="";
		$strItems="";
		for($i = 0; $i < count($arTypes); $i++)
		{
			if($i>0)
			{
				$strTypes .= ",";
				$strItems .= ",";
			}
			$strTypes.="'".CUtil::JSEscape($arTypes[$i])."'";
			$arItems = $arAllItems[$arTypes[$i]];
			$strItems .= "[";
			for($j = 0; $j < count($arItems); $j++)
			{
				if($j>0)$strItems .= ",";
				$strItems.="'".CUtil::JSEscape($arItems[$j])."'";
			}
			$strItems .= "]";
		}

		$scriptRes = "\n".'arFDMenuTypes["'.CUtil::JSEscape($path).'"] = {types: ['.$strTypes.'], items: ['.$strItems.']};'."\n";

		if ($bEchoResult)
			echo $scriptRes;
		else
			return array($arMenuTypes, $scriptRes, $arAllItems[$strSelected]);
	}

	function GetItems($Params)
	{
		global $APPLICATION,$USER;
		static $checkChildren, $genTmb;

		if (!isset($checkChildren, $genTmb))
		{
			$checkChildren = COption::GetOptionString("fileman", "file_dialog_check_children", "Y") == 'Y';
			$genTmb = COption::GetOptionString("fileman", "file_dialog_gen_thumb", "Y") == 'Y';
		}

		$site = $Params['site'];
		$path = $Params['path'];
		$path_js = $path == "" ? "/" : addslashes(htmlspecialcharsex($path));
		$path_js = str_replace("//", "/", $path_js);
		$bCheckEmpty = $Params['bCheckEmpty'];

		$rootPath = CSite::GetSiteDocRoot($site);
		if (!file_exists($rootPath.$path) && $Params['bThrowException'] === true)
			return $APPLICATION->ThrowException(GetMessage('BX_FD_ERROR').': '.GetMessage('BX_FD_PATH_CORRUPT'), 'path_corrupt');
		elseif (!$USER->CanDoFileOperation('fm_view_listing', array($site, $path)))
			return $APPLICATION->ThrowException(GetMessage('BX_FD_ERROR').': '.GetMessage('BX_FD_ACCESS_DENIED'), 'access_denied');

		$arDirs = array(); $arFiles = array();
		GetDirList(array($site, $path), $arDirs, $arFiles, array(), array("name" => "asc"), "DF", false, true);

?>
arFDDirs['<?=$path_js?>'] = [];
arFDFiles['<?=$path_js?>'] = [];
<?
		$ind = -1;
		foreach ($arDirs as $Dir)
		{
			$name = addslashes(htmlspecialcharsex($Dir["NAME"]));
			$path_i = addslashes(htmlspecialcharsex($path))."/".$name;
			$path_i = str_replace("//", "/", $path_i);
			$arPath_i = Array($site, $path_i);

			if (!$USER->CanDoFileOperation('fm_view_listing', $arPath_i))
				continue;
			$ind++;

			$empty = true;
			if ($bCheckEmpty) // Find subfolders inside
			{
				if(($handle = @opendir($rootPath.$path.'/'.$name)))
				{
					while(false !== ($file = @readdir($handle)))
					{
						$arFile = Array();
						if($file == "." || $file == ".." || !is_dir($rootPath.$path.'/'.$name."/".$file))
							continue;
						$empty = false;
						break;
					}
				}
			}
			$perm_del = $USER->CanDoFileOperation('fm_delete_folder', $arPath_i) ? 'true' : 'false';
			$perm_ren = $USER->CanDoFileOperation('fm_rename_folder', $arPath_i) ? 'true' : 'false';

?>
arFDDirs['<?=$path_js?>'][<?=$ind?>] =
{
	name : '<?= $name?>',
	path : '<?=$path_i?>',
	empty: <?= $empty ? 'true' : 'false';?>,
	permission : {del : <?=$perm_del?>, ren : <?=$perm_ren?>},
	date : '<?=$Dir["DATE"];?>',
	timestamp : '<?=$Dir["TIMESTAMP"];?>',
	size : 0
};
<?
		}

		if ($Params['getFiles'])
		{
			$ind = -1;
			foreach ($arFiles as $File)
			{
				$name = addslashes(htmlspecialcharsex($File["NAME"]));
				$path_i = addslashes(htmlspecialcharsex($File["ABS_PATH"]));
				$path_i = str_replace("//", "/", $path_i);
				$arPath_i = Array($site, $path_i);

				if (!$USER->CanDoFileOperation('fm_view_file', $arPath_i))
					continue;
				$ind++;

				$perm_del = $USER->CanDoFileOperation('fm_delete_file', $arPath_i) ? 'true' : 'false';
				$perm_ren = $USER->CanDoFileOperation('fm_rename_file', $arPath_i) ? 'true' : 'false';

				$imageAddProps = '';
				if ($genTmb)
				{
					$ext = strtolower(GetFileExtension($name));
					if (in_array($ext, array('gif','jpg','jpeg','png','jpe','bmp'))) // It is image
					{
						$tmbPath = BX_PERSONAL_ROOT."/tmp/fd_tmb".$path_i;
						$destinationFile = $rootPath.$tmbPath;
						if (!file_exists($destinationFile))
						{
							$sourceFile = $File['PATH'];
							if (CFile::ResizeImageFile($sourceFile, $destinationFile, array('width' => 140, 'height' => 110)))
								$imageAddProps = ",\n".'tmb_src : \''.CUtil::JSEscape($tmbPath).'\'';
						}
						else
							$imageAddProps = ",\n".'tmb_src : \''.CUtil::JSEscape($tmbPath).'\'';
					}
				}
?>
arFDFiles['<?=$path_js?>'][<?=$ind?>] =
{
	name : '<?=$name?>',
	path : '<?=$path_i?>',
	permission : {del : <?=$perm_del?>, ren : <?=$perm_ren?>},
	date : '<?=$Dir["DATE"];?>',
	timestamp : '<?=$Dir["TIMESTAMP"];?>',
	size : '<?=$File["SIZE"];?>'<?=$imageAddProps?>
};
<?
			}
		}

		$arPath = array($site, $path);
		?>
arFDPermission['<?=$path_js?>'] = {
	new_folder : <?echo ($USER->CanDoFileOperation('fm_create_new_folder',$arPath) ? 'true' : 'false');?>,
	upload : <?echo ($USER->CanDoFileOperation('fm_upload_file',$arPath) ? 'true' : 'false');?>
};
<?
	}

	function GetItemsRecursively($Params)
	{
		global $APPLICATION, $USER;
		$path = $Params['path'];
		$rootPath = CSite::GetSiteDocRoot($Params['site']);

		if (!file_exists($rootPath.$path))
		{
			if ($Params['bThrowException'] === true)
				return $APPLICATION->ThrowException(GetMessage('BX_FD_ERROR').': '.GetMessage('BX_FD_PATH_CORRUPT'), 'path_corrupt');
			$path = '/';
		}

		$arPath = explode('/', $path);

		if ($Params['loadRoot'] !== false)
		{
			$Params['path'] = '/';
			self::GetItems($Params);
		}

		$curPath = '';
		for ($i = 0, $l = count($arPath); $i < $l; $i++)
		{
			$catalog = trim($arPath[$i], " /\\");

			if ($catalog != "")
			{
				$curPath .= '/'.$catalog;
				$Params['path'] = $curPath;
				self::GetItems($Params);
			}
		}
	}

	function MakeNewDir($Params)
	{
		if(CModule::IncludeModule("fileman"))
		{
			global $USER, $APPLICATION;
			$path = Rel2Abs("/", $Params['path']);
			$site = $Params['site'];
			$arPath = Array($site, $path);
			$DOC_ROOT = CSite::GetSiteDocRoot($site);
			$abs_path = $DOC_ROOT.$path;
			$dirname = str_replace("/", "_", $APPLICATION->UnJSEscape($Params['name']));
			$strWarning = '';

			//Check access to folder
			if (!$USER->CanDoFileOperation('fm_create_new_folder', $arPath))
				$strWarning = GetMessage("ACCESS_DENIED");
			else if(!is_dir($abs_path))
				$strWarning = GetMessage("FD_FOLDER_NOT_FOUND", array('#PATH#' => addslashes(htmlspecialchars($path))));
			else
			{
				if (strlen($dirname) > 0 && ($mess = self::CheckFileName($dirname)) !== true)
					$strWarning = $mess;
				else if(strlen($dirname) <= 0)
					$strWarning = GetMessage("FD_NEWFOLDER_ENTER_NAME");
				else
				{
					$pathto = Rel2Abs($path, $dirname);
					if(file_exists($DOC_ROOT.$pathto))
						$strWarning = GetMessage("FD_NEWFOLDER_EXISTS");
					else
						$strWarning = CFileMan::CreateDir(Array($site, $pathto));
				}
			}
		}
		else
		{
			$strWarning = GetMessage("BX_FD_NO_FILEMAN");
		}

		self::EchoActionStatus($strWarning);

		if ($strWarning == '')
			self::LoadItems(array('path' => $path, 'site' => $site, 'bAddToMenu' => $Params['bAddToMenu'], 'loadRecursively' => false, 'getFiles' => $Params['getFiles']));
	}

	function Remove($Params)
	{
		if(CModule::IncludeModule("fileman"))
		{
			global $USER;
			$path = Rel2Abs("/", $Params['path']);
			$path = CFileMan::SecurePathVar($path);
			$site = $Params['site'];
			$arPath = Array($site, $path);
			$DOC_ROOT = CSite::GetSiteDocRoot($site);
			$abs_path = $DOC_ROOT.$path;
			$strWarning = '';

			$type = false;
			if (is_dir($abs_path))
				$type = 'folder';
			if (is_file($abs_path))
				$type = 'file';

			//Check access to folder or file
			if (!$type) // Not found
				$strWarning = GetMessage("FD_ELEMENT_NOT_FOUND", array('#PATH#' => addslashes(htmlspecialchars($path))));
			elseif (!$USER->CanDoFileOperation('fm_delete_'.$type, $arPath)) // Access denied
				$strWarning = GetMessage("ACCESS_DENIED");
			else // Ok, delete it!
				$strWarning = CFileMan::DeleteEx($path);
		}
		else
		{
			$strWarning = GetMessage("BX_FD_NO_FILEMAN");
		}

		self::EchoActionStatus($strWarning);

		if ($strWarning == '')
		{
			// get parent dir path and load content
			$parPath = substr($path, 0, strrpos($path, '/'));
			self::LoadItems(array('path' => $parPath, 'site' => $site, 'bAddToMenu' => $Params['bAddToMenu'], 'loadRecursively' => false, 'getFiles' => $Params['getFiles']));
		}
	}

	function Rename($Params)
	{
		if(CModule::IncludeModule("fileman"))
		{
			global $USER, $APPLICATION;

			$name = str_replace("/", "_", $APPLICATION->UnJSEscape($Params['name']));
			$oldName = str_replace("/", "_", $APPLICATION->UnJSEscape($Params['old_name']));

			$path = Rel2Abs("/", $Params['path']);
			$path = CFileMan::SecurePathVar($path);
			$site = $Params['site'];
			$arPath = Array($site, $path);
			$DOC_ROOT = CSite::GetSiteDocRoot($site);

			$oldPath = Rel2Abs($path, $oldName);
			$newPath = Rel2Abs($path, $name);
			$oldAbsPath = $DOC_ROOT.$oldPath;
			$newAbsPath = $DOC_ROOT.$newPath;
			$arPath1 = Array($site, $oldPath);
			$arPath2 = Array($site, $newPath);
			$strWarning = '';

			$type = false;
			if (is_dir($oldAbsPath))
				$type = 'folder';
			if (is_file($oldAbsPath))
				$type = 'file';

			$ext1 = GetFileExtension($oldName);
			$ext2 = GetFileExtension($name);
			$ScriptExt = GetScriptFileExt();

			if (
				$type == 'file' &&
				!$USER->CanDoOperation('edit_php') &&
				(
					substr($oldName, 0, 1) == "."
					||
					substr($name, 0, 1) == "."
					||
					(
						in_array($ext1, $ScriptExt) &&
						!in_array($ext2, $ScriptExt)
					)
					||
					(
						in_array($ext2, $ScriptExt) &&
						!in_array($ext1, $ScriptExt)
					)
				)
			)
			{
				$strWarning = GetMessage("ACCESS_DENIED");
			}
			elseif (!$type)
				$strWarning = GetMessage("FD_ELEMENT_NOT_FOUND", array('#PATH#' => addslashes(htmlspecialchars($path))));
			elseif (!$USER->CanDoFileOperation('fm_rename_'.$type,$arPath1) || !$USER->CanDoFileOperation('fm_rename_'.$type,$arPath2))
				$strWarning = GetMessage("ACCESS_DENIED");
			else
			{
				if (strlen($name) > 0 && ($mess = self::CheckFileName($name)) !== true)
					$strWarning = $mess;
				else if(strlen($name) <= 0)
					$strWarning = GetMessage("FD_ELEMENT_ENTER_NAME");
				else
				{
					if(file_exists($DOC_ROOT.$newPath))
						$strWarning = GetMessage("FD_ELEMENT_EXISTS");
					elseif(!rename($oldAbsPath, $newAbsPath))
						$strWarning = GetMessage("FD_RENAME_ERROR");
				}
			}
		}
		else
		{
			$strWarning = GetMessage("BX_FD_NO_FILEMAN");
		}

		self::EchoActionStatus($strWarning);

		if ($strWarning == '')
			self::LoadItems(array('path' => $path, 'site' => $site, 'bAddToMenu' => $Params['bAddToMenu'], 'loadRecursively' => false, 'getFiles' => $Params['getFiles']));
	}

	function CheckFileName($str)
	{
		if (preg_match("/[^a-zA-Z0-9\s!\$\(\)\[\]\{\}\-\.;=@\^_\~]/is", $str))
			return GetMessage("FD_INCORRECT_NAME");
		return true;
	}

	function EchoActionStatus($strWarning = '')
	{
?>
		<script>
		<? if ($strWarning == ''): ?>
			window.action_status = true;
		<?else: ?>
			window.action_warning = '<?= CUtil::JSEscape($strWarning)?>';
			window.action_status = false;
		<?endif;?>
		</script>
<?
	}

	function SetUserConfig($Params)
	{
		global $APPLICATION;
		$Params['path'] = $APPLICATION->UnJSEscape($Params['path']);
		$Params['site'] = $APPLICATION->UnJSEscape($Params['site']);
		$Params['view'] = in_array($Params['view'], array('detail', 'preview')) ? $Params['view'] : 'list';
		$Params['sort'] = in_array($Params['sort'], array('size', 'type', 'date')) ? $Params['sort'] : 'name';
		$Params['sort_order'] = ($Params['sort_order'] == 'asc') ? 'asc' : 'des';

		CUserOptions::SetOption("fileman", "file_dialog_config", addslashes($Params['site'].';'.$Params['path'].';'.$Params['view'].';'.$Params['sort'].';'.$Params['sort_order']));
	}

	function PreviewFlash($Params)
	{
		if(CModule::IncludeModule("fileman"))
		{
			global $APPLICATION, $USER;

			if(CModule::IncludeModule("compression"))
				CCompress::Disable2048Spaces();

			$path = $Params['path'];
			$path = CFileMan::SecurePathVar($path);
			$path = Rel2Abs("/", $path);
			$arPath = Array($Params['site'], $path);

			if(!$USER->CanDoFileOperation('fm_view_file', $arPath))
				$path = '';

			if ($path == "")
				return;

			$APPLICATION->RestartBuffer();
?>
<HTML>
<HEAD></HEAD>
<BODY id="__flash" style="margin:0px; border-width: 0px;">
<embed id="__flash_preview" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" name="__flash_preview" quality="high" width="<?=$Params['width']?>" height="<?=$Params['height']?>" src="<?=htmlspecialcharsex($path)?>" />
</BODY>
</HTML>
<?
			die();
		}
	}

	function ShowUploadForm($Params)
	{
		$lang = htmlspecialcharsex($Params['lang']);
		$site = htmlspecialcharsex($Params['site']);
		$res = $Params['file'] ? self::UploadFile($Params) : '';
		?>
<HTML>
<HEAD><?=$res?></HEAD>
<BODY style="margin:0px !important; background-color:#F4F4F4; font-family:Verdana;">
<form name="frmLoad" action="file_dialog.php?action=uploader&lang=<?=$lang?>&site=<?=$site?>&<?=bitrix_sessid_get()?>" onsubmit="return parent.oBXDialogControls.Uploader.OnSubmit();" method="post" enctype="multipart/form-data">
	<table style="width: 540px; height: 123px; font-size:70%">
		<tr height="0%">
			<td style="width:40%;" align="left">
				<?=GetMessage('FD_LOAD_FILE')?>:
			</td>
			<td style="width:60%; padding-top: 0px;" valign="top" align="left">
				<input id="__bx_fd_load_file" size="45" type="file" name="load_file">
			</td>
		</tr>
		<tr height="0%">
			<td style="width:40%;" align="left">
				<?=GetMessage("FD_FILE_NAME_ON_SERVER");?>
			</td>
			<td style="width:60%;" align="left">
				<input id="__bx_fd_server_file_name" style="width:100%;" type="text">
			</td>
		</tr>
		<tr height="100%">
			<td style="width:100%;" valign="top" align="left" colspan="2">
				<table style="font-size:100%"><tr>
				<td><input id="_bx_fd_upload_and_open" value="Y" type="checkbox" name="upload_and_open" checked="checked"></td>
				<td><label for="_bx_fd_upload_and_open"> <?=GetMessage("FD_UPLOAD_AND_OPEN");?></label></td>
				</tr></table>
			</td>
		</tr>
		<tr height="0%">
			<td style="width:100%; padding:0px 8px 5px 0px" valign="bottom" align="right" colSpan="2">
				<input  type="submit" value="<?=GetMessage("FD_BUT_LOAD");?>"></input>
				<input style="width:100px;" type="button" onclick="parent.oBXFileDialog.Close()" value="<?=GetMessage("FD_BUT_CANCEL");?>"></input>
			</td>
		</tr>
	</table>
	<input type="hidden" name="MAX_FILE_SIZE" value="1000000000">
	<input type="hidden" name="lang" value="<?=$lang?>">
	<input type="hidden" name="site" value="<?=$site?>">
	<input id="__bx_fd_rewrite" type="hidden" name="rewrite" value="N">
	<input id="__bx_fd_upload_path" type="hidden" name="path" value="">
	<input id="__bx_fd_upload_fname" type="hidden" name="filename" value="">
</form>
</BODY>
</HTML>
<?
	}

	function UploadFile($Params)
	{
		$buffer = 'parent.oWaitWindow.Hide();';
		$F = $Params['file'];

		if (isset($F["tmp_name"]) && strlen($F["tmp_name"]) > 0 && strlen($F["name"]) > 0 || is_uploaded_file($F["tmp_name"]))
		{
			global $APPLICATION, $USER;
			$strWarning = '';
			$filename = $Params['filename'];
			$path = $Params['path'];
			$site = $Params['site'];
			$upload_and_open = $Params['upload_and_open'];
			$rewrite = $Params['rewrite'];
			$rootPath = CSite::GetSiteDocRoot($site);

			if(strlen($filename) == 0)
				$filename = $F["name"];

			$pathto = Rel2Abs($path, $filename);
			$pathto = urldecode($pathto);

			if (strlen($filename) > 0 && ($mess = self::CheckFileName($filename)) !== true)
				$strWarning = $mess;

			$fn = basename($pathto);
			if($APPLICATION->GetFileAccessPermission(array($site, $pathto)) > "R" &&
				($USER->IsAdmin() || (!in_array(GetFileExtension($fn), GetScriptFileExt()) && substr($fn, 0, 1) != ".")) &&
				strlen($strWarning) == 0
			)
			{
				if(!file_exists($rootPath.$pathto) || $_REQUEST["rewrite"] == "Y")
				{
					//************************** Quota **************************//
					$bQuota = true;
					if(COption::GetOptionInt("main", "disk_space") > 0)
					{
						$bQuota = false;
						$quota = new CDiskQuota();
						if ($quota->checkDiskQuota(array("FILE_SIZE"=>filesize($F["tmp_name"]))))
							$bQuota = true;
					}
					//************************** Quota **************************//
					if ($bQuota)
					{
						copy($F["tmp_name"], $rootPath.$pathto);
						@chmod($rootPath.$pathto, BX_FILE_PERMISSIONS);
						if(COption::GetOptionInt("main", "disk_space") > 0)
							CDiskQuota::updateDiskQuota("file", filesize($rootPath.$pathto), "copy");

						$buffer = 'setTimeout(function(){parent.oBXDialogControls.Uploader.OnAfterUpload("'.$filename.'", '.($upload_and_open == "Y" ? 'true' : 'false').');}, 50);';
					}
					else
					{
						$strWarning = $quota->LAST_ERROR;
					}
				}
				else
				{
					$strWarning = GetMessage("FD_LOAD_EXIST_ALERT");
				}
			}
			elseif(strlen($strWarning) == 0)
			{
				$strWarning = GetMessage("FD_LOAD_DENY_ALERT");
			}
		}
		else
		{
			$strWarning = GetMessage("FD_LOAD_ERROR_ALERT");
		}

		if (strlen($strWarning) > 0)
			$buffer = 'alert("'.addslashes(htmlspecialcharsex($strWarning)).'");';

		return '<script>'.$buffer.'</script>';
	}

}
?>