<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

if($_GET["back_url_pub"] <> "" && !is_array($_GET["back_url_pub"]))
	$_SESSION["BACK_URL_PUB"] = $_GET["back_url_pub"];

$params = DeleteParam(array("logout", "back_url_pub"));

$arPanelButtons = array();
$arLangs = CLanguage::GetLangSwitcherArray();

if($USER->IsAuthorized())
{
	//Favorites
	if(is_callable(array($USER,'CanDoOperation')) && ($USER->CanDoOperation('edit_own_profile') || $USER->CanDoOperation('edit_other_settings') || $USER->CanDoOperation('view_other_settings')))
	{
		$aFav = array();
		if(is_callable(array('CUtil', 'addslashes')))
		{
			$aFav = array(
				array(
					"TEXT"=>GetMessage("top_panel_add_fav"),
					"TITLE"=>GetMessage("MAIN_ADD_PAGE_TO_FAVORITES"),
					"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes(BX_ROOT."/admin/favorite_edit.php?lang=".LANG."&name=".urlencode($APPLICATION->GetTitle()))."&addurl='+encodeURIComponent(document.getElementById('navchain-link').getAttribute('href', 2)));"
				),
				array(
					"TEXT"=>GetMessage("top_panel_org_fav"),
					"TITLE"=>GetMessage("top_panel_org_fav_title"),
					"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes(BX_ROOT."/admin/favorite_list.php?lang=".LANG)."');"
				),
			);
			if(is_callable(array('CFavorites','Delete')))
			{
				$db_fav = CFavorites::GetList(array("COMMON"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), array("MENU_FOR_USER"=>$USER->GetID(), "LANGUAGE_ID"=>LANGUAGE_ID));
				$prevCommon = "";
				while($db_fav_arr = $db_fav->Fetch())
				{
					if($db_fav_arr["COMMON"] == "Y" && $db_fav_arr["MODULE_ID"] <> "" && $APPLICATION->GetGroupRight($db_fav_arr["MODULE_ID"]) < "R")
						continue;
					if($db_fav_arr["COMMON"] <> $prevCommon)
					{
						$aFav[] = array("SEPARATOR"=>true);
						$prevCommon = $db_fav_arr["COMMON"];
					}

					$sTitle = $db_fav_arr["COMMENTS"];
					$sTitle = (strlen($sTitle)>100? substr($sTitle, 0, 100)."..." : $sTitle);
					$sTitle = str_replace("\r\n", "\n", $sTitle);
					$sTitle = str_replace("\r", "\n", $sTitle);
					$sTitle = str_replace("\n", " ", $sTitle);

					$aFav[] = array(
						"TEXT"=>htmlspecialchars($db_fav_arr["NAME"]),
						"TITLE"=>htmlspecialchars($sTitle),
						"ICON"=>"favorites",
						"ACTION"=>"jsUtils.Redirect([], '".CUtil::addslashes($db_fav_arr["URL"])."');",
					);
				}
			}
		}
		$arPanelButtons[] = array(
			"TEXT"=>GetMessage("top_panel_favorites"),
			"TITLE"=>GetMessage("top_panel_fav"),
			"ICON"=>"bx-panel-admin-button-favorites-icon",
			"MENU"=>$aFav,
		);
	}

	$bCanViewSettings = (is_callable(array($USER,'CanDoOperation')) && ($USER->CanDoOperation('view_other_settings') || $USER->CanDoOperation('edit_other_settings')));
	if($bCanViewSettings)
	{
		//Settings
		$arPanelButtons[] = array(
			"TEXT"=>GetMessage("top_panel_settings"),
			"TITLE"=>GetMessage("button_settings"),
			"LINK"=>BX_ROOT."/admin/settings.php?lang=".LANG."&amp;mid=".(defined("ADMIN_MODULE_NAME")? ADMIN_MODULE_NAME:"main").($APPLICATION->GetCurPage() <> BX_ROOT."/admin/settings.php"? "&amp;back_url_settings=".urlencode($_SERVER["REQUEST_URI"]):""),
			"ICON"=>"bx-panel-admin-button-settings-icon",
		);
	}

	//Help
	$page = (defined("HELP_FILE") && strpos(HELP_FILE, '/') === false? HELP_FILE : basename($APPLICATION->GetCurPage()));
	$module = (defined("ADMIN_MODULE_NAME")? ADMIN_MODULE_NAME:"main");
	$aActiveSection = $adminMenu->ActiveSection();
	if(LANGUAGE_ID == "ru")
		$help_link = "http://dev.1c-bitrix.ru/".(IsModuleInstalled('intranet')?"intranet":"user")."_help/".$aActiveSection["help_section"]."/".(defined("HELP_FILE") && strpos(HELP_FILE, '/') !== false?  HELP_FILE : $module."/".$page);
	else
		$help_link = "http://www.bitrixsoft.com/help/index.html?page=".urlencode("source/".$module."/help/en/".$page.".html");

	$arPanelButtons[] = array(
		"TEXT"=>GetMessage("top_panel_help"),
		"TITLE"=>GetMessage("MAIN_HELP"),
		"LINK"=>$help_link,
		"ICON"=>"bx-panel-admin-button-help-icon",
	);

	if($USER->CanDoOperation('install_updates'))
	{
		$update_res = UpdateTools::GetUpdateResult();
	
		//update
		
		$arUpdateBtn = array(
			"TITLE"=>($update_res["result"] == true? GetMessage("top_panel_updates_hint") : GetMessage("top_panel_update")),
			"LINK"=>"/bitrix/admin/sysupdate.php?lang=".LANGUAGE_ID,
			"ICON"=> ($update_res["result"] == true ? "bx-panel-admin-button-new-updates-icon" : "bx-panel-admin-button-updates-icon"),
			//"TOOLTIP"=>$update_res['tooltip'],
			//"TOOLTIP_ACTION"=>"jsUserOptions.SaveOption('update', 'options', 'tooltip', 'off');",
		);
		
		if ($update_res['result'] === true)
		{
			$arUpdateBtn['TOOLTIP'] = $update_res['tooltip'];
			$arUpdateBtn['TOOLTIP_ID'] = 'update_tooltip';
		}

		
		$arPanelButtons[] = array("SEPARATOR"=>true);
		$arPanelButtons[] = $arUpdateBtn;
	}

	$arPanelButtons[] = array(
		"SEPARATOR"=>true,
	);
}

foreach($arLangs as $adminLang)
{
	$arPanelButtons[] = array(
		"TEXT"=>$adminLang["LID"],
		"TITLE"=>GetMessage("top_panel_lang")." ".$adminLang["NAME"],
		"LINK"=>$adminLang["PATH"],
		"SELECTED"=>$adminLang["SELECTED"],
	);
}

if(!defined("BX_AUTH_FORM") && $USER->IsAuthorized() && CModule::IncludeModule("bizproc"))
{
	$arFilter = array("USER_ID" => $USER->GetID());
	$dbResultList = CBPTaskService::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);

	if($dbResultList->Fetch())
	{
		$arPanelButtons[] = array(
			"SEPARATOR"=>true,
		);
		$arPanelButtons[] = array(
			"TEXT"=> GetMessage('top_panel_bizproc_tasks'),
			"TITLE"=> GetMessage('top_panel_bizproc_title'),
			"LINK"=> "/bitrix/admin/bizproc_task_list.php?lang=".LANGUAGE_ID,
			"ICON"=> "bx-panel-admin-button-biz-proc-icon",
		);
	}
}

$sPubUrl = ($_SESSION["BACK_URL_PUB"] <> ""?
	htmlspecialchars($_SESSION["BACK_URL_PUB"]).(strpos($_SESSION["BACK_URL_PUB"], "?") !== false? "&amp;":"?") : '/?').
	'back_url_admin='.urlencode($APPLICATION->GetCurPage().($params<>""? "?".$params:""));
	
	
$aUserOpt = CUserOptions::GetOption("admin_panel", "settings");	
?>
<!--[if lte IE 6]>
<style type="text/css">#bx-panel {display:none !important;}</style>
<div id="bx-panel-error">
<?echo GetMessage("admin_panel_browser")?>
</div><![endif]-->
<div style="display:none; overflow:hidden;" id="bx-panel-back"></div>
<div id="bx-panel" class="bx-panel-admin-mode">

    <div id="bx-panel-top">
        <div id="bx-panel-tabs"><a href="#"<?if ($USER->IsAuthorized()):?> onclick="jsStartMenu.ShowStartMenu(this);"<?endif?> id="bx-panel-menu"><span><?echo GetMessage("admin_panel_menu")?></span></a><a href="<?=$sPubUrl?>" id="bx-panel-view-tab"><span><?echo GetMessage("admin_panel_site")?></span></a><a href="<?=BX_ROOT."/admin/index.php?lang=".LANGUAGE_ID?>" id="bx-panel-admin-tab"><span><?echo GetMessage("admin_panel_admin")?></span></a></div>
		
		<?			
		
		$userInfo = "";
		if(!defined("BX_AUTH_FORM") && $USER->IsAuthorized())
		{
			$maxQuota = COption::GetOptionInt("main", "disk_space", 0)*1024*1024;
			if ($maxQuota > 0)
			{
				$quota = new CDiskQuota();
				$free = $quota->GetDiskQuota();
				$free = round($free/$maxQuota*100);
				
				$userInfo .= '<span id="bx-panel-quota"><span id="bx-panel-quota-caption">'.GetMessage("admin_panel_free").' '.$free.'%</span><span id="bx-panel-quota-indicator"><span id="bx-panel-quota-slider" style="width:'.(100 - $free).'%;'.($free <= 10 ? ' background-color: #F55 !important;' : '').'"></span></span></span><span class="bx-panel-userinfo-separator bx-panel-expand-mode-only"></span>';
			}
		}

		if($USER->IsAuthorized())
		{
			
			$bCanProfile = $USER->CanDoOperation('view_own_profile') || $USER->CanDoOperation('edit_own_profile');
			if ($bCanProfile)
				$userInfo .= '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$USER->GetID().'" id="bx-panel-user">'.htmlspecialchars($USER->GetFullName()).' ('.htmlspecialchars($USER->GetLogin()).')</a><span class="bx-panel-userinfo-separator"></span>';
			else
				$userInfo .= '<a id="bx-panel-user">'.htmlspecialchars($USER->GetFullName()).' ('.htmlspecialchars($USER->GetLogin()).')</a><span class="bx-panel-userinfo-separator"></span>';
			
			$userInfo .= '<a href="'.$APPLICATION->GetCurPage().'?logout=yes'.htmlspecialchars(($s=DeleteParam(array("logout"))) == ""? "":"&".$s).'" id="bx-panel-logout">'.GetMessage("admin_panel_logout").'</a>';
			
			$userInfo .= '<span class="bx-panel-userinfo-separator"></span>';
			
			$userInfo .= '<a href="javascript:void(0)" id="bx-panel-pin"'.($aUserOpt['fix'] == 'on' ? ' class="bx-panel-pin-fixed"' : '').'></a>';	
		}		
		?>
		
        <div id="bx-panel-userinfo"><?=$userInfo?></div>		
	</div>


	<div id="bx-panel-admin-toolbar">
        <div id="bx-panel-admin-toolbar-inner">
		<?
		foreach($arPanelButtons as $item)
		{
			if($item["SEPARATOR"] == true)
			{
				?><span class="bx-panel-admin-button-separator"></span><?
			}
			else
			{
				$id = isset($item["ICON"]) ? $item["ICON"]."-id" : "bx-panel-admin-button-".ToLower(randString(5))."-id";
				if(is_array($item["MENU"]) && !empty($item["MENU"]))
				{
					//$sMenuUrl = "(new BX.COpener({DIV: this, ATTACH: this, MENU: ".htmlspecialchars(CAdminPopup::PhpToJavaScript($item["MENU"], $dummy)).", EVENTS: {open: 'mouseup'}})).Open(arguments[0])";
					//$sMenuUrl = "this.blur(); topPanelPopup.ShowMenu(this, ".htmlspecialchars(CAdminPopup::PhpToJavaScript($item["MENU"], $dummy)).", jsPanel.IsFixed());";
					
					?><a class="bx-panel-admin-button<?=($item["SELECTED"] == true ? " bx-panel-admin-button-selected": "" )?>" href="javascript:void(0);" <?=(!empty($item["LINK_PARAM"]) ? $item["LINK_PARAM"] : "")?> id="<?=$id?>" hidefocus="true" title="<?=$item["TITLE"]?>"><?if (!empty($item["ICON"])):?><span class="bx-panel-admin-button-icon <?=$item["ICON"]?>"></span><?endif?><span class="bx-panel-admin-button-text"><?=$item["TEXT"]?></span><span class="bx-panel-admin-button-arrow"></span></a>
						<script type="text/javascript">BX.ready(function() { 
							var btn = BX("<?=$id?>");
							var menu = new BX.COpener({DIV: btn, ATTACH:btn, MENU: <?=CUtil::PhpToJSObject($item["MENU"], $dummy)?>, TYPE: 'click'});
							BX.addCustomEvent(menu, "onOpenerMenuOpen", BX.delegate(function() { BX.addClass(this, "bx-panel-admin-button-pressed")}, btn));
							BX.addCustomEvent(menu, "onOpenerMenuClose", BX.delegate(function() { BX.removeClass(this, "bx-panel-admin-button-pressed")}, btn));
						});</script>
					<?
				}
				else
				{
					?><a class="bx-panel-admin-button<?=($item["SELECTED"] == true ? " bx-panel-admin-button-selected" : "" )?>" href="<?=$item["LINK"]?>" <?=(!empty($item["LINK_PARAM"]) ? $item["LINK_PARAM"] : "")?> id="<?=$id?>" hidefocus="true" title="<?=$item["TITLE"]?>"><?if (!empty($item["ICON"])):?><span class="bx-panel-admin-button-icon <?=$item["ICON"]?>"></span><?endif?><span class="bx-panel-admin-button-text"><?=$item["TEXT"]?></span></a><?
				}
				
				if ($item['TOOLTIP'])
				{
					if ($item['TOOLTIP_ID'])
					{
?>
<script type="text/javascript">BX.ready(function() {BX.hint(BX('<?=CUtil::JSEscape($id)?>'), '<?=CUtil::JSEscape($item["TITLE"])?>', '<?=CUtil::JSEscape($item['TOOLTIP'])?>', '<?=CUtil::JSEscape($item['TOOLTIP_ID'])?>')});</script>
<?
					}
				}
			}
		}
		?>
        </div>
    </div>
</div>

<script type="text/javascript">BX.admin.panel.state = {fixed: <?=($aUserOpt["fix"] == "on" ? "true" : "false")?>}</script>

<?	
if($USER->IsAuthorized())
{
	//start menu preload
	$aUserOptGlobal = CUserOptions::GetOption("global", "settings");
	if($aUserOptGlobal["start_menu_preload"] == 'Y')
		echo '<script type="text/javascript">jsUtils.addEvent(window, "load", function(){jsStartMenu.PreloadMenu();});</script>';
}

echo $GLOBALS["adminPage"]->ShowSound();

?>