<?
//error_reporting(E_ALL);
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

if(strlen($APPLICATION->GetTitle())<=0)
	$APPLICATION->SetTitle(GetMessage("MAIN_PROLOG_ADMIN_TITLE"));
$direction = "";
$direct = CLanguage::GetByID(LANGUAGE_ID);
$arDirect = $direct -> Fetch();
if($arDirect["DIRECTION"] == "N")
	$direction = ' dir="rtl"';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html<?=$direction?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=htmlspecialchars(LANG_CHARSET)?>">
<title><?echo COption::GetOptionString("main","site_name", $_SERVER["SERVER_NAME"])?> - <?echo htmlspecialcharsex($APPLICATION->GetTitle(false, true))?></title>
<?
$APPLICATION->AddBufferContent(array($adminPage, "ShowCSS"));
echo $adminPage->ShowScript();
$APPLICATION->ShowHeadScripts();
$APPLICATION->ShowHeadStrings();
?>
</head>

<?
$bShowAdminMenu = ($USER->IsAuthorized());
if($bShowAdminMenu)
{
	$adminPage->Init();
	$adminMenu->Init($adminPage->aModules);
	if(empty($adminMenu->aGlobalMenu))
		$bShowAdminMenu = false;
	if($bShowAdminMenu && class_exists("CUserOptions"))
		$aOptMenuPos = CUserOptions::GetOption("admin_menu", "pos", array());
}
?>
<body>
<?
if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/admin_header.php"))
	include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/admin_header.php");
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="height:100%">
	<tr>
		<td valign="top" style="height:61px;"><?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/top_panel.php");?></td>
	</tr>
	<tr>
		<td>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" style="height:100%">
				<tr>
					<td class="toppanel-shadow"><div class="empty"></div></td>
<?if($bShowAdminMenu):?>
					<td class="vdivider-top-bg" onmousedown="JsAdminMenu.StartDrag();"><div class="empty"></div></td>
					<td class="toppanel-shadow"><div class="empty"></div></td>
<?endif;?>
				</tr>
				<tr>
<?if($bShowAdminMenu):?>
					<td valign="top" width="0%">

<?
//Left Column
?>
						<div id="hiddenmenucontainer" style="display:<?echo ($aOptMenuPos["ver"] == "off"? "block":"none")?>;" onClick="JsAdminMenu.verSplitterToggle();" title="<?echo GetMessage("prolog_main_show_menu")?>">
						<?echo GetMessage("prolog_main_m_e_n_u")?>
						</div>

						<div id="menudiv" style="display:<?echo ($aOptMenuPos["ver"] <> "off"? "block":"none")?>;">

						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<tr>
								<td>
<?
//Buttons Container
?>
<?
$aActiveSection = $adminMenu->ActiveSection();
?>
									<div id="menutitle"><?echo $aActiveSection["text"]?></div>

									<div id="buttonscontainer" style="display:<?echo ($aOptMenuPos["hor"] <> "off"? "block":"none")?>;">
									<table cellspacing="0" class="buttons">
<?
foreach($adminMenu->aGlobalMenu as $menu):
?>
										<tr>
											<td id="btn_<?echo $menu["items_id"]?>" class="button<?if($menu["items_id"] == $aActiveSection["items_id"]) echo " buttonsel";?>"
											onMouseOver="this.className+=' buttonover';"
											onMouseOut="this.className = this.className.replace(/\s*buttonover/ig, '');"
											onClick="JsAdminMenu.ToggleMenu('<?echo $menu["items_id"]?>', '<?echo $menu["text"]?>');" title="<?echo $menu["title"]?>">
												<table cellspacing="0">
													<tr>
														<td class="left"></td>
														<td class="center"><div id="<?echo $menu["icon"]?>"><?echo $menu["text"]?></div></td>
														<td class="right"></td>
													</tr>
												</table>
											</td>
										</tr>
<?endforeach;?>
									</table>
									</div>

									<div id="smbuttonscontainer" style="display:<?echo ($aOptMenuPos["hor"] == "off"? "block":"none")?>;">
<?foreach($adminMenu->aGlobalMenu as $menu):?>
										<div id="smbtn_<?echo $menu["items_id"]?>" class="smbutton<?if($menu["items_id"] == $aActiveSection["items_id"]) echo " smbuttonsel";?>"
										onMouseOver="this.className+=' smbuttonover';"
										onMouseOut="this.className=this.className.replace(/\s*smbuttonover/ig, '');"
										onClick="JsAdminMenu.ToggleMenu('<?echo $menu["items_id"]?>', '<?echo $menu["text"]?>');"
										title="<?echo $menu["text"]?>"><div id="<?echo $menu["icon"]?>"></div></div>
<?endforeach;?>
										<div class="empty" style="clear:both"></div>
									</div>
<script type="text/javascript">
var JsAdminMenu = new JCAdminMenu("<?echo (method_exists($adminMenu, "GetOpenedSections")? $adminMenu->GetOpenedSections():"");?>");
JsAdminMenu.sMenuSelected = "<?echo $aActiveSection["items_id"];?>";
</script>
<?
//End of Buttons Container
?>
								</td>
							</tr>
							<tr>

<?
//Horisontal divider
?>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" class="hdivider">
										<tr>
											<td><div class="empty"></div></td>
											<td id="hdividercell" class="hdividerknob <?echo ($aOptMenuPos["hor"] <> "off"? "hdividerknobup":"hdividerknobdown")?>" onMouseOver="JsAdminMenu.horSplitter.Highlight(true);" onMouseOut="JsAdminMenu.horSplitter.Highlight(false);" onClick="JsAdminMenu.horSplitterToggle();" title="<?echo ($aOptMenuPos["hor"] <> "off"? GetMessage("prolog_main_less_buttons"):GetMessage("prolog_main_more_buttons"))?>"><div class="empty"></div></td>
											<td><div class="empty"></div></td>
										</tr>
									</table>
								</td>
<?
//End of Horisontal divider
?>
							</tr>
							<tr>
								<td>
<?
//Menu Container
?>
<div id="menucontainer"<?if(intval($aOptMenuPos["width"]) > 0) echo ' style="width:'.intval($aOptMenuPos["width"]).'px"'?>>

<?
foreach($adminMenu->aGlobalMenu as $menu):
?>
	<div id="<?echo $menu["items_id"]?>" style="display:<?echo ($menu["items_id"] == $aActiveSection["items_id"]? "block":"none")?>;">
<?
	foreach($menu["items"] as $submenu)
		$adminMenu->Show($submenu);
?>
	</div>
<?
endforeach;
?>

</div>
<?
//End of Menu Container
?>
<div class="empty" id="menu_min_width"></div>
<?
if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/this_site_logo.php"))
	include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/this_site_logo.php");
?>
								</td>
							</tr>
						</table>
						</div>
<?
//End of Left Column
?>
					</td>

<?
//Vertical divider
?>
					<td width="0%" valign="top" class="vdivider-bg" onmousedown="JsAdminMenu.StartDrag();">
						<table cellpadding="0" cellspacing="0" border="0" class="vdivider">
							<tr><td class="top"><div class="empty"></div></td></tr>
							<tr><td class="vdividerknob <?echo ($aOptMenuPos["ver"] <> "off"? "vdividerknobleft":"vdividerknobright")?>" id="vdividercell" onmousedown="JsAdminMenu.toggle=true;" onclick="JsAdminMenu.verSplitterToggle(); JsAdminMenu.toggle=false;" onMouseOver="JsAdminMenu.verSplitter.Highlight(true);" onMouseOut="JsAdminMenu.verSplitter.Highlight(false);" title="<?echo ($aOptMenuPos["ver"] <> "off"? GetMessage("prolog_main_hide_menu"):GetMessage("prolog_main_show_menu"))?>"><div class="empty"></div></td></tr>
						</table>
					</td>
<?
//End of Vertical divider
?>

<?
endif; //if($bShowAdminMenu)
?>
					<td valign="top" width="100%">
<?
//Title
?>
<div class="page-title">
<div style="width:100%;">
<table cellspacing="0" width="100%">
	<tr>
		<td><div class="page-title-icon" id="<?echo ($bShowAdminMenu? $adminMenu->ActiveIcon():"default_page_icon")?>"></div></td>
		<td width="100%"><h1><?echo htmlspecialcharsex($APPLICATION->GetTitle(false, true))?></h1></td>
		<td><a id="navchain-link" href="<?echo htmlspecialchars($_SERVER["REQUEST_URI"])?>" title="<?echo GetMessage("MAIN_PR_ADMIN_CUR_LINK")?>"></a></td>
	</tr>
</table>
</div>
</div>

<?
if($bShowAdminMenu)
{
	//Navigation chain
	$adminChain->Init();
	$adminChain->Show();
}
?>

<?
//Не думайте, что я с ума сошел. Спасибо IE за эти три DIV.
?>
<div id="content_container_hor">
<div style="width:100%;">
<div id="content_container_ver">

<?
//Content
?>
<?
//wizard customization file
$bxProductConfig = array();
if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php"))
	include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php");

if($USER->IsAuthorized()):
	if(defined("DEMO") && DEMO == "Y"):
		$vendor = COption::GetOptionString("main", "vendor", "1c_bitrix");
		$delta = $SiteExpireDate-time();
		$daysToExpire = ($delta < 0? 0 : ceil($delta/86400));
		$bSaas = (COption::GetOptionString('main', '~SAAS_MODE', "N") == "Y");

		echo BeginNote('width="100%"');
		if(isset($bxProductConfig["saas"])):
			if($bSaas)
			{
				if($daysToExpire > 0)
				{ 
					if($daysToExpire <= $bxProductConfig["saas"]["days_before_warning"])
					{
						$sWarn = $bxProductConfig["saas"]["warning"];
						$sWarn = str_replace("#RENT_DATE#", COption::GetOptionString('main', '~support_finish_date'), $sWarn);
						$sWarn = str_replace("#DAYS#", $daysToExpire, $sWarn);
						echo $sWarn;
					}
				}
				else
				{
					echo str_replace("#RENT_DATE#", COption::GetOptionString('main', '~support_finish_date'), $bxProductConfig["saas"]["warning_expired"]);
				}
			}
			else
			{
				if($daysToExpire > 0)
					echo str_replace("#DAYS#", $daysToExpire, $bxProductConfig["saas"]["trial"]);
				else
					echo $bxProductConfig["saas"]["trial_expired"];
			}
		else:
?>
	<span class="required"><?echo GetMessage("TRIAL_ATTENTION") ?></span>
	<?echo GetMessage("TRIAL_ATTENTION_TEXT1_".$vendor) ?>
	<?if ($daysToExpire >= 0):?>
	<?echo GetMessage("TRIAL_ATTENTION_TEXT2") ?> <span class="required"><b><?echo $daysToExpire?></b></span> <?echo GetMessage("TRIAL_ATTENTION_TEXT3") ?>.
	<?else:?>
	<?echo GetMessage("TRIAL_ATTENTION_TEXT4_".$vendor) ?>
	<?endif;?>
	<?echo GetMessage("TRIAL_ATTENTION_TEXT5_".$vendor) ?>
<?
		endif; //saas
		echo EndNote();
	
	elseif(($USER->CanDoOperation('edit_own_profile') || $USER->CanDoOperation('edit_other_settings') || $USER->CanDoOperation('view_other_settings'))): 
		//show support ending warning
		$supportFinishDate = COption::GetOptionString('main', '~support_finish_date', '');
		if($supportFinishDate <> '' && is_array(($aSupportFinishDate=ParseDate($supportFinishDate, 'ymd'))))
		{
			$aGlobalOpt = CUserOptions::GetOption("global", "settings", array());
			if($aGlobalOpt['messages']['support'] <> 'N')
			{
				$supportFinishStamp = mktime(0,0,0, $aSupportFinishDate[1], $aSupportFinishDate[0], $aSupportFinishDate[2]);
				$supportDateDiff = ceil(($supportFinishStamp - time())/86400);

				$lim = (defined("INTRANET_EDITION") && INTRANET_EDITION == "Y") ? 90 : 30;

				$sSupportMess = '';
				if($supportDateDiff >= 0 && $supportDateDiff <= 14)
					$sSupportMess = GetMessage("prolog_main_support1", array('#FINISH_DATE#'=>GetTime($supportFinishStamp), '#DAYS_AGO#'=>($supportDateDiff == 0? GetMessage("prolog_main_today"):GetMessage('prolog_main_support_days', array('#N_DAYS_AGO#'=>$supportDateDiff)))));
				elseif($supportDateDiff < 0 && $supportDateDiff >= -$lim)
					$sSupportMess = GetMessage("prolog_main_support2", array('#FINISH_DATE#'=>GetTime($supportFinishStamp), '#DAYS_AGO#'=>(-$supportDateDiff)));
				elseif($supportDateDiff < -$lim)
					$sSupportMess = GetMessage("prolog_main_support3", array('#FINISH_DATE#'=>GetTime($supportFinishStamp)));
			
				if($sSupportMess <> '')
				{
					echo BeginNote();
					echo $sSupportMess;
					echo EndNote();
				}
			}
		}
	endif; //defined("DEMO") && DEMO == "Y"

	//diagnostic for spaces in init.php etc.
	//$aHeadersInfo set in the include.php
	if(!empty($aHeadersInfo))
		echo CAdminMessage::ShowMessage(GetMessage("prolog_admin_headers_sent", array("#FILE#"=>$aHeadersInfo['file'], "#LINE#"=>$aHeadersInfo['line'])));

endif; //$USER->IsAuthorized()
?>