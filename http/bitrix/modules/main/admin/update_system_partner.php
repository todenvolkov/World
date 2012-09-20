<?
//**********************************************************************/
//**    DO NOT MODIFY THIS FILE                                       **/
//**    MODIFICATION OF THIS FILE WILL ENTAIL SITE FAILURE            **/
//**********************************************************************/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");

@set_time_limit(0);
ini_set("track_errors", "1");
ignore_user_abort(true);

IncludeModuleLangFile(__FILE__);

if(!$USER->CanDoOperation('view_other_settings') && !$USER->CanDoOperation('install_updates'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$errorMessage = "";

$myaddmodule = preg_replace("#[^a-zA-Z0-9.-_]#i", "", $_REQUEST["addmodule"]);

$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");
$bLockUpdateSystemKernel = false;//CUpdateSystemPartner::IsInCommonKernel();
$arRequestedModules = CUpdateClientPartner::GetRequestedModules($myaddmodule);

$strTitle = GetMessage("SUP_TITLE_BASE");
$APPLICATION->SetTitle($strTitle);
$APPLICATION->SetAdditionalCSS("/bitrix/themes/".ADMIN_THEME_ID."/sysupdate.css");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arMenu = array(
	array(
		"TEXT" => GetMessage("SUP_CHECK_UPDATES"),
		"LINK" => "/bitrix/admin/update_system_partner.php?refresh=Y&amp;lang=".LANGUAGE_ID."&amp;addmodule=".urlencode($myaddmodule),
		"ICON" => "btn_update_partner",
	),
	array("SEPARATOR" => "Y"),
	/*array(
		"TEXT" => GetMessage("SUP_CHECK_UPDATES_SYSTEM"),
		"LINK" => "/bitrix/admin/update_system.php?refresh=Y&lang=".LANGUAGE_ID,
		"ICON" => "btn_update_partner",
	),
	array("SEPARATOR" => "Y"),*/
	array(
		"TEXT" => GetMessage("SUP_SETTINGS"),
		"LINK" => "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=main&tabControl_active_tab=edit5&back_url_settings=%2Fbitrix%2Fadmin%2Fupdate_system_partner.php%3Flang%3D".LANGUAGE_ID."",
	),
//	array("SEPARATOR" => "Y"),
//	array(
//		"TEXT" => GetMessage("SUP_HISTORY"),
//		"LINK" => "/bitrix/admin/sysupdate_log.php?lang=".LANGUAGE_ID,
//		"ICON" => "btn_update_log",
//	)
);

$context = new CAdminContextMenu($arMenu);
$context->Show();

if (!$bLockUpdateSystemKernel)
{
	if (!$arUpdateList = CUpdateClientPartner::GetUpdatesList($errorMessage, LANG, $stableVersionsOnly, $arRequestedModules))
		$errorMessage .= "<br>".GetMessage("SUP_CANT_CONNECT").". ";
}
else
{
	$errorMessage .= "<br>".GetMessage("SUP_CANT_CONTRUPDATE").". ";
}

$strError_tmp = "";
$arClientModules = CUpdateClientPartner::GetCurrentModules($strError_tmp);
if (StrLen($strError_tmp) > 0)
	$errorMessage .= $strError_tmp;

if ($arUpdateList)
{
	if (isset($arUpdateList["ERROR"]))
	{
		for ($i = 0, $cnt = count($arUpdateList["ERROR"]); $i < $cnt; $i++)
			$errorMessage .= "[".$arUpdateList["ERROR"][$i]["@"]["TYPE"]."] ".$arUpdateList["ERROR"][$i]["#"];
	}
}

if (strlen($errorMessage) > 0)
	echo CAdminMessage::ShowMessage(Array("DETAILS" => $errorMessage, "TYPE" => "ERROR", "MESSAGE" => GetMessage("SUP_ERROR"), "HTML" => true));

?>
<script language="JavaScript">
<!--
	var updRand = 0;

	function PrepareString(str)
	{
		str = str.replace(/^\s+|\s+$/, '');
		while (str.length > 0 && str.charCodeAt(0) == 65279)
			str = str.substring(1);
		return str;
	}
//-->
</script>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<?=bitrix_sessid_post()?>

<?
$arTabs = array(
	array(
		"DIV" => "tab1",
		"TAB" => GetMessage("SUP_TAB_UPDATES"),
		"ICON" => "",
		"TITLE" => GetMessage("SUP_TAB_UPDATES_ALT"),
	),
	array(
		"DIV" => "tab2",
		"TAB" => GetMessage("SUP_TAB_UPDATES_LIST"),
		"ICON" => "",
		"TITLE" => GetMessage("SUP_TAB_UPDATES_LIST_ALT"),
	),
//	array(
//		"DIV" => "tab3",
//		"TAB" => GetMessage("SUP_TAB_SEARCH"),
//		"ICON" => "",
//		"TITLE" => GetMessage("SUP_TAB_SEARCH_ALT"),
//	),
);

$tabControl = new CAdminTabControl("tabControl", $arTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<tr>
		<td colspan="2">

			<?
			$countModuleUpdates = 0;
			$countTotalImportantUpdates = 0;
			$bLockControls = False;

			if ($arUpdateList)
			{
				if (isset($arUpdateList["MODULE"]) && is_array($arUpdateList["MODULE"]) && is_array($arUpdateList["MODULE"]))
					$countModuleUpdates = count($arUpdateList["MODULE"]);

				$countTotalImportantUpdates = 0;
				if ($countModuleUpdates > 0)
				{
					for ($i = 0, $cnt = count($arUpdateList["MODULE"]); $i < $cnt; $i++)
					{
						$countTotalImportantUpdates += count($arUpdateList["MODULE"][$i]["#"]["VERSION"]);
						if (!array_key_exists($arUpdateList["MODULE"][$i]["@"]["ID"], $arClientModules))
							$countTotalImportantUpdates += 1;
					}
				}
				?>

				<div id="upd_success_div" style="display:none">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= GetMessage("SUP_SUB_SUCCESS") ?></B></td>
						</tr>
						<tr>
							<td valign="top"><div id="upd_success_div_text"></div></td>
						</tr>
					</table>
				</div>

				<div id="upd_error_div" style="display:none">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= GetMessage("SUP_SUB_ERROR") ?></B></td>
						</tr>
						<tr>
							<td valign="top"><div id="upd_error_div_text"></td>
						</tr>
					</table>
				</div>

				<?
				if ($arUpdateList !== false && isset($arUpdateList["REG"]) && count($arUpdateList["REG"]) > 0)
				{
					?>
					<div id="upd_register_div">
						<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
							<tr class="heading">
								<td><b><?= GetMessage("SUPP_SUBR_REG") ?></b></td>
							</tr>
							<tr>
								<td valign="top">
									<table cellpadding="0" cellspacing="0">
										<tr>
											<td class="icon-new"><div class="icon icon-licence"></div></td>
											<td>
												<?= GetMessage("SUPP_SUBR_HINT") ?><br><br>
												<?
												for ($i = 0; $i < count($arUpdateList["REG"]); $i++)
												{
													$arM = $arUpdateList["REG"][$i];
													?><?= $arM["@"]["NAME"] ?> (<?= $arM["@"]["ID"] ?>)<br /><?
												}
												?>
												<br>
												<input TYPE="button" id="id_register_btn" NAME="register_btn" value="<?= GetMessage("SUPP_SUBR_BUTTON") ?>" onclick="RegisterSystem()">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<br>
					</div>
					<SCRIPT LANGUAGE="JavaScript">
					<!--
					function RegisterSystem()
					{
						ShowWaitWindow();
						document.getElementById("id_register_btn").disabled = true;

						CHttpRequest.Action = function(result)
						{
							CloseWaitWindow();
							result = PrepareString(result);
							document.getElementById("id_register_btn").disabled = false;
							if (result == "Y")
							{
								var udl = document.getElementById("upd_register_div");
								udl.style["display"] = "none";
							}
							else
							{
								alert("<?= GetMessage("SUPP_SUBR_ERR") ?>: " + result);
							}
						}

						updRand++;
						CHttpRequest.Send('/bitrix/admin/update_system_partner_act.php?query_type=register&<?= bitrix_sessid_get() ?>&updRand=' + updRand);
					}
					//-->
					</SCRIPT>
					<?
				}
				?>

				<div id="upd_install_div" style="display:none">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= GetMessage("SUP_SUB_PROGRESS") ?></B></td>
						</tr>
						<tr>
							<td valign="top">
								<table border="0" cellspacing="5" cellpadding="3" width="100%">
									<tr>
										<td valign="top" width="5%">
										</td>
										<td valign="top">
											<script language="JavaScript">
											<!--
											var ns4 = (document.layers) ? true : false;
											var ie4 = (document.all) ? true : false;

											var txt = '';
											if (ns4)
											{
												txt += '<table border=0 cellpadding=0 cellspacing=0><tr><td>';
												txt += '<layer width="300" height="15" bgcolor="#365069" top="0" left="0"></layer>';
												txt += '<layer width="298" height="13" bgcolor="#ffffff" top="1" left="1"></layer>';
												txt += '<layer name="PBdoneD" width="298" height="13" bgcolor="#D5E7F3" top="1" left="1"></layer>';
												txt += '</td></tr></table>';
												txt += '<br>';
												txt += '<table border=0 cellpadding=0 cellspacing=0><tr><td>';
												txt += '<layer width="300" height="15" bgcolor="#365069" top="0" left="0"></layer>';
												txt += '<layer width="298" height="13" bgcolor="#ffffff" top="1" left="1"></layer>';
												txt += '<layer name="PBdone" width="298" height="13" bgcolor="#D5E7F3" top="1" left="1"></layer>';
												txt += '</td></tr></table>';
											}
											else
											{
												txt += '<div style="top:0px; left:0px; width:300; height:15px; background-color:#365069; font-size:1px;">';
												txt += '<div style="position:relative; top:1px; left:1px; width:298px; height:13px; background-color:#ffffff; font-size:1px;">';
												txt += '<div id="PBdoneD" style="position:relative; top:0px; left:0px; width:0px; height:13px; background-color:#D5E7F3; font-size:1px;">';
												txt += '</div></div></div>';
												txt += '<br>';
												txt += '<div style="top:0px; left:0px; width:300; height:15px; background-color:#365069; font-size:1px;">';
												txt += '<div style="position:relative; top:1px; left:1px; width:298px; height:13px; background-color:#ffffff; font-size:1px;">';
												txt += '<div id="PBdone" style="position:relative; top:0px; left:0px; width:0px; height:13px; background-color:#D5E7F3; font-size:1px;">';
												txt += '</div></div></div>';
											}
											document.write(txt);
											//-->
											</script>
											<br>
											<div id="install_progress_hint"></div>
										</td>
										<td valign="top" align="right">
											<input TYPE="button" NAME="stop_updates" id="id_stop_updates" value="<?= GetMessage("SUP_SUB_STOP") ?>" onclick="StopUpdates()">
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>

				<div id="upd_select_div" style="display:block">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= ($countModuleUpdates > 0) ? GetMessage("SUP_SU_TITLE1") : GetMessage("SUP_SU_TITLE2") ?></B></td>
						</tr>
						<tr>
							<td valign="top">
									<table cellpadding="0" cellspacing="0">
										<tr>
											<td class="icon-new"><div class="icon icon-main-partner"></div></td>
											<td>
								<b><?= GetMessage("SUP_SU_RECOMEND") ?>:</b> 
								<?
								$bComma = False;
								if ($countModuleUpdates > 0)
								{
									echo str_replace("#NUM#", $countModuleUpdates, GetMessage("SUP_SU_RECOMEND_MOD"));
									$bComma = True;
								}
								if ($countModuleUpdates <= 0)
									echo GetMessage("SUP_SU_RECOMEND_NO");
								?>
								<br><br>
								<input TYPE="button" ID="install_updates_button" NAME="install_updates"<?= (($countModuleUpdates <= 0 || $bLockControls) ? " disabled" : "") ?> value="<?= GetMessage("SUP_SU_UPD_BUTTON") ?>" onclick="InstallUpdates()">
								<br><br>
								<span id="id_view_updates_list_span"><a id="id_view_updates_list" href="javascript:tabControl.SelectTab('tab2');"><?= GetMessage("SUP_SU_UPD_VIEW") ?></a></span>
								<br><br>
								<?
								if ($stableVersionsOnly == "N")
									echo GetMessage("SUP_STABLE_OFF_PROMT");
								else
									echo GetMessage("SUP_STABLE_ON_PROMT");
								?>
								<br><br>
								<?= GetMessage("SUP_SU_UPD_HINT") ?>
											</td>
										</tr>
									</table>
							</td>
						</tr>
					</table>
				</div>

				<script language="JavaScript">
				<!--
				var updSelectDiv = document.getElementById("upd_select_div");
				var updInstallDiv = document.getElementById("upd_install_div");
				var updSuccessDiv = document.getElementById("upd_success_div");
				var updErrorDiv = document.getElementById("upd_error_div");

				var PBdone = (ns4) ? findlayer('PBdone', document) : (ie4) ? document.all['PBdone'] : document.getElementById('PBdone');
				var PBdoneD = (ns4) ? findlayer('PBdoneD', document) : (ie4) ? document.all['PBdoneD'] : document.getElementById('PBdoneD');

				var aStrParams;

				var globalQuantity = <?= $countTotalImportantUpdates ?>;
				var globalCounter = 0;
				var globalQuantityD = 100;
				var globalCounterD = 0;

				var cycleModules = <?= ($countModuleUpdates > 0) ? "true" : "false" ?>;

				var bStopUpdates = false;

				function findlayer(name, doc)
				{
					var i,layer;
					for (i = 0; i < doc.layers.length; i++)
					{
						layer = doc.layers[i];
						if (layer.name == name)
							return layer;
						if (layer.document.layers.length > 0)
							if ((layer = findlayer(name, layer.document)) != null)
								return layer;
					}
					return null;
				}

				function SetProgress(val)
				{
					if (ns4)
					{
						PBdone.clip.left = 0;
						PBdone.clip.top = 0;
						PBdone.clip.right = val*298/100;
						PBdone.clip.bottom = 13;
					}
					else
						PBdone.style.width = (val*298/100) + 'px';
				}

				function SetProgressD()
				{
					globalCounterD++;
					if (globalCounterD > globalQuantityD)
						globalCounterD = 0;

					var val = globalCounterD * 100 / globalQuantityD;

					if (ns4)
					{
						PBdoneD.clip.left = 0;
						PBdoneD.clip.top = 0;
						PBdoneD.clip.right = val * 298 / 100;
						PBdoneD.clip.bottom = 13;
					}
					else
						PBdoneD.style.width = (val * 298 / 100) + 'px';

					if (!bStopUpdates)
						setTimeout(SetProgressD, 1000);
				}

				function SetProgressHint(val)
				{
					var installProgressHintDiv = document.getElementById("install_progress_hint");
					installProgressHintDiv.innerHTML = val;
				}

				function InstallUpdates()
				{
					SetProgressHint("<?= GetMessage("SUP_INITIAL") ?>");

					aStrParams = "addmodule=<?= CUtil::JSEscape($myaddmodule) ?>";
					
					__InstallUpdates();
					SetProgressD();
				}

				function __InstallUpdates()
				{
					updSelectDiv.style["display"] = "none";
					updSuccessDiv.style["display"] = "none";
					updErrorDiv.style["display"] = "none";
					updInstallDiv.style["display"] = "block";

					CHttpRequest.Action = function(result)
					{
						InstallUpdatesAction(result);
					}

					var param;
					if (cycleModules)
					{
						param = "M";
					}

					updRand++;
					CHttpRequest.Send('/bitrix/admin/update_system_partner_call.php?' + aStrParams + "&<?= bitrix_sessid_get() ?>&query_type=" + param + "&updRand=" + updRand);
				}

				function InstallUpdatesDoStep(data)
				{
					if (data.length > 0)
					{
						arData = data.split("|");
						globalCounter += parseInt(arData[0]);
						if (arData.length > 1)
							SetProgressHint("<?= GetMessage("SUP_SU_UPD_INSMED") ?> " + arData[1]);
						if (globalCounter > globalQuantity)
							globalCounter = 0;
						SetProgress(globalCounter * 100 / globalQuantity);
					}

					__InstallUpdates();
				}

				function InstallUpdatesAction(result)
				{
					//alert(result + "; " + result.length);
					result = PrepareString(result);

					if (result == "*")
					{
						window.location.reload(false);
						return;
					}

					var code = result.substring(0, 3);
					var data = result.substring(3);
					//alert("code=" + code + "; data=" + data);

					if (bStopUpdates)
					{
						CloseWaitWindow();
						code = "FIN";
						cycleModules = false;
					}

					if (code == "FIN")
					{
						if (cycleModules)
						{
							cycleModules = false;
						}

						if (cycleModules)
						{
							InstallUpdatesDoStep(data);
						}
						else
						{
							updSelectDiv.style["display"] = "none";
							updErrorDiv.style["display"] = "none";
							updInstallDiv.style["display"] = "none";
							updSuccessDiv.style["display"] = "block";
							DisableUpdatesTable();

							var updSuccessDivText = document.getElementById("upd_success_div_text");
							updSuccessDivText.innerHTML = "<?= GetMessage("SUP_SU_UPD_INSSUC") ?>: " + globalCounter;
						}
					}
					else
					{
						if (code == "STP")
						{
							InstallUpdatesDoStep(data);
						}
						else
						{
							updSelectDiv.style["display"] = "none";
							updSuccessDiv.style["display"] = "none";
							updInstallDiv.style["display"] = "none";
							updErrorDiv.style["display"] = "block";

							var updErrorDivText = document.getElementById("upd_error_div_text");
							updErrorDivText.innerHTML = data;
						}
					}
				}

				function StopUpdates()
				{
					bStopUpdates = true;
					document.getElementById("id_stop_updates").disabled = true;
					ShowWaitWindow();
				}
				//-->
				</script>
				<?
			}
			?>

		</td>
	</tr>
	<tr>
		<td colspan="2">
			<br>
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><b><?echo GetMessage("SUP_SERVER_ANSWER")?></b></td>
						</tr>
						<tr>
							<td valign="top">
									<table cellpadding="0" cellspacing="0">
										<tr>
											<td class="icon-new"><div class="icon icon-update-partner"></div></td>
											<td>

			<table border="0" cellspacing="1" cellpadding="3">
				<!--tr>
					<td valign="top">
						<?= GetMessage("SUP_SUBI_CHECK") ?>:&nbsp;&nbsp;
					</td>
					<td valign="top">
						<?= COption::GetOptionString("main", "update_system_check", "-") ?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<?= GetMessage("SUP_SUBI_UPD") ?>:&nbsp;&nbsp;
					</td>
					<td valign="top">
						<?= COption::GetOptionString("main", "update_system_update", "-") ?>
					</td>
				</tr-->
				<?if (is_array($arUpdateList) && array_key_exists("CLIENT", $arUpdateList)):?>
					<tr>
						<td><?echo GetMessage("SUP_REGISTERED")?>&nbsp;&nbsp;</td>
						<td><?echo $arUpdateList["CLIENT"][0]["@"]["NAME"]?></td>
					</tr>
				<?endif;?>
				
				<tr>
					<td><b><?= GetMessage("SUP_LICENSE_KEY_MD5") ?>:&nbsp;&nbsp;</b></td>
					<td><b><?= md5("BITRIX".CUpdateClientPartner::GetLicenseKey()."LICENCE"); ?></b></td>
				</tr>
				<?if (is_array($arUpdateList) && array_key_exists("CLIENT", $arUpdateList)):?>
					<!--tr>
						<td><?echo GetMessage("SUP_EDITION")?>&nbsp;&nbsp;</td>
						<td><?echo $arUpdateList["CLIENT"][0]["@"]["LICENSE"]?></td>
					</tr>
					<tr>
						<td><?echo GetMessage("SUP_SITES")?>&nbsp;&nbsp;</td>
						<td><?echo ($arUpdateList["CLIENT"][0]["@"]["MAX_SITES"] > 0? $arUpdateList["CLIENT"][0]["@"]["MAX_SITES"] : GetMessage("SUP_CHECK_PROMT_2"))?></td>
					</tr>
					<tr>
						<td><?echo GetMessage("SUP_USERS")?>&nbsp;&nbsp;</td>
						<td><?echo ($arUpdateList["CLIENT"][0]["@"]["MAX_USERS"] > 0? $arUpdateList["CLIENT"][0]["@"]["MAX_USERS"] : GetMessage("SUP_CHECK_PROMT_21"))?></td>
					</tr-->
					<tr>
						<td><?echo GetMessage("SUP_ACTIVE")?>&nbsp;&nbsp;</td>
						<td><?echo GetMessage("SUP_ACTIVE_PERIOD", array("#DATE_TO#"=>((strlen($arUpdateList["CLIENT"][0]["@"]["DATE_TO"]) > 0) ? $arUpdateList["CLIENT"][0]["@"]["DATE_TO"] : "<i>N/A</i>"), "#DATE_FROM#" => ((strlen($arUpdateList["CLIENT"][0]["@"]["DATE_FROM"]) > 0) ? $arUpdateList["CLIENT"][0]["@"]["DATE_FROM"] : "<i>N/A</i>")));?></td>
					</tr>
					<tr>
						<td><?echo GetMessage("SUP_SERVER")?>&nbsp;&nbsp;</td>
						<td><?echo $arUpdateList["CLIENT"][0]["@"]["HTTP_HOST"]?></td>
					</tr>
				<?else:?>
					<tr>
						<td><?echo GetMessage("SUP_SERVER")?>&nbsp;&nbsp;</td>
						<td><?echo (($s=COption::GetOptionString("main", "update_site"))==""? "-":$s)?></td>
					</tr>
				<?endif;?>
			</table>
			
											</td>
										</tr>
									</table>
							</td>
						</tr>
					</table>
			
		</td>
	</tr>

<?
$tabControl->EndTab();
$tabControl->BeginNextTab();
?>

	<tr>
		<td colspan="2">

			<table border="0" cellspacing="1" cellpadding="3" width="100%">
				<tr>
					<td>
						<?= GetMessage("SUP_SULL_CNT") ?>: <?= $countModuleUpdates ?><BR><BR>
						<input TYPE="button" ID="install_updates_sel_button" NAME="install_updates"<?= (($countModuleUpdates <= 0) ? " disabled" : "") ?> value="<?= GetMessage("SUP_SULL_BUTTON") ?>" onclick="InstallUpdatesSel()">
					</td>
				</tr>
			</table>
			<br>

			<?
			if ($arUpdateList)
			{
				?>
				<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal" id="table_updates_sel_list">
					<tr>
						<td class="heading"><INPUT TYPE="checkbox" NAME="select_all" id="id_select_all" title="<?= GetMessage("SUP_SULL_CBT") ?>" onClick="SelectAllRows(this);"></td>
						<td class="heading"><B><?= GetMessage("SUP_SULL_PARTNER_NAME") ?></B></td>
						<td class="heading"><B><?= GetMessage("SUP_SULL_NAME") ?></B></td>
						<td class="heading"><B><?= GetMessage("SUP_SULL_TYPE") ?></B></td>
						<td class="heading"><B><?= GetMessage("SUP_SULL_REL") ?></B></td>
						<td class="heading"><B><?= GetMessage("SUP_SULL_NOTE") ?></B></td>
					</tr>
					<?
					if (isset($arUpdateList["MODULE"]))
					{
						?>
						<tr>
							<td colspan="6"><?= GetMessage("SUP_SU_RECOMEND") ?></td>
						</tr>
						<?
					}

					if (isset($arUpdateList["MODULE"]))
					{
						for ($i = 0, $cnt = count($arUpdateList["MODULE"]); $i < $cnt; $i++)
						{
							$arModuleTmp = $arUpdateList["MODULE"][$i];
							$strTitleTmp = $arModuleTmp["@"]["NAME"]." (".$arModuleTmp["@"]["ID"].")\n".$arModuleTmp["@"]["DESCRIPTION"]."\n";
							if (is_array($arModuleTmp["#"]) && array_key_exists("VERSION", $arModuleTmp["#"]) && count($arModuleTmp["#"]["VERSION"]) > 0)
								for ($j = 0, $cntj = count($arModuleTmp["#"]["VERSION"]); $j < $cntj; $j++)
									$strTitleTmp .= str_replace("#VER#", $arModuleTmp["#"]["VERSION"][$j]["@"]["ID"], GetMessage("SUP_SULL_VERSION"))."\n".$arModuleTmp["#"]["VERSION"][$j]["#"]["DESCRIPTION"][0]["#"]."\n";
							$strTitleTmp = htmlspecialchars(preg_replace("/<.+?>/i", "", $strTitleTmp));
							?>
							<tr title="<?= $strTitleTmp ?>" ondblclick="ShowDescription('<?= htmlspecialchars($arModuleTmp["@"]["ID"]) ?>')">
								<td><INPUT TYPE="checkbox" NAME="select_module_<?= htmlspecialchars($arModuleTmp["@"]["ID"]) ?>" value="Y" onClick="ModuleCheckboxClicked(this, '<?= htmlspecialchars($arModuleTmp["@"]["ID"]) ?>', new Array());" checked id="id_select_module_<?= htmlspecialchars($arModuleTmp["@"]["ID"]) ?>"></td>
								<td><label for="id_select_module_<?= htmlspecialchars($arModuleTmp["@"]["ID"]) ?>"><?= htmlspecialchars($arModuleTmp["@"]["PARTNER_NAME"]) ?></label></td>
								<td><a target="_blank" href="<?= str_replace("#NAME#", htmlspecialchars($arModuleTmp["@"]["ID"]), GetMessage("SUP_SULL_MODULE_PATH")) ?>"><?= str_replace("#NAME#", htmlspecialchars($arModuleTmp["@"]["NAME"]), GetMessage("SUP_SULL_MODULE")) ?></a></td>
								<td><?= (array_key_exists($arUpdateList["MODULE"][$i]["@"]["ID"], $arClientModules) ? GetMessage("SUP_SULL_REF_O") : GetMessage("SUP_SULL_REF_N")) ?></td>
								<td><?= (isset($arModuleTmp["#"]["VERSION"]) ? $arModuleTmp["#"]["VERSION"][count($arModuleTmp["#"]["VERSION"]) - 1]["@"]["ID"] : "") ?></td>
								<td><a href="javascript:ShowDescription('<?= htmlspecialchars($arModuleTmp["@"]["ID"]) ?>')"><?= GetMessage("SUP_SULL_NOTE_D") ?></a></td>
							</tr>
							<?
						}
					}
					?>
				</table>
				<SCRIPT LANGUAGE="JavaScript">
				<!--
					var arModuleUpdatesDescr = {<?
					if (isset($arUpdateList["MODULE"]))
					{
						for ($i = 0, $cnt = count($arUpdateList["MODULE"]); $i < $cnt; $i++)
						{
							$arModuleTmp = $arUpdateList["MODULE"][$i];

							$strTitleTmp  = '<div class="title"><table cellspacing="0" width="100%"><tr>';
							$strTitleTmp .= '<td width="100%" class="title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById(\'updates_float_div\'));">'.GetMessage("SUP_SULD_DESC").'</td>';
							$strTitleTmp .= '<td width="0%"><a class="close" href="javascript:CloseDescription();" title="'.GetMessage("SUP_SULD_CLOSE").'"></a></td>';
							$strTitleTmp .= '</tr></table></div>';
							$strTitleTmp .= '<div class="content" style="overflow:auto;overflow-y:auto;height:400px;">';
							$strTitleTmp .= '<h2>'.$arModuleTmp["@"]["NAME"].' ('.$arModuleTmp["@"]["ID"].')'.'</h2>';
							$strTitleTmp .= '<table cellspacing="0"><tr><td>'.$arModuleTmp["@"]["DESCRIPTION"].'</td></tr></table><br>';

							if (isset($arModuleTmp["#"]["VERSION"]))
							{
								$strTitleTmp .= '<table cellspacing="0">';
								for ($j = count($arModuleTmp["#"]["VERSION"]) - 1; $j >= 0; $j--)
								{
									$strTitleTmp .= '<tr><td><b>';
									$strTitleTmp .= str_replace("#VER#", $arModuleTmp["#"]["VERSION"][$j]["@"]["ID"], GetMessage("SUP_SULL_VERSION"));
									$strTitleTmp .= '</b></td></tr>';
									$strTitleTmp .= '<tr><td>';
									$strTitleTmp .= strip_tags($arModuleTmp["#"]["VERSION"][$j]["#"]["DESCRIPTION"][0]["#"], "<b><i><u><li><ul><span><p>");
									$strTitleTmp .= '</td></tr>';
								}
								$strTitleTmp .= '</table>';
							}

							$strTitleTmp = addslashes(preg_replace("/\n/", "<br>", preg_replace("/\r/", "", $strTitleTmp)));

							if ($i > 0)
								echo ",\n";
							echo "\"".htmlspecialchars($arModuleTmp["@"]["ID"])."\" : \"".$strTitleTmp."\"";
						}
					}
					?>};

					var arModuleUpdatesCnt = {<?
					if ($countModuleUpdates > 0)
					{
						for ($i = 0, $cnt = count($arUpdateList["MODULE"]); $i < $cnt; $i++)
						{
							if ($i > 0)
								echo ", ";
							echo "\"".$arUpdateList["MODULE"][$i]["@"]["ID"]."\" : ";
							if (!array_key_exists($arUpdateList["MODULE"][$i]["@"]["ID"], $arClientModules))
								echo count($arUpdateList["MODULE"][$i]["#"]["VERSION"]) + 1;
							else
								echo count($arUpdateList["MODULE"][$i]["#"]["VERSION"]);
						}
					}
					?>};

					var arModuleUpdatesControl = {<?
					if ($countModuleUpdates > 0)
					{
						for ($i = 0, $cnt = count($arUpdateList["MODULE"]); $i < $cnt; $i++)
						{
							if ($i > 0)
								echo ", ";
							echo "\"".$arUpdateList["MODULE"][$i]["@"]["ID"]."\" : [";
							$bFlagTmp = False;
							if (isset($arUpdateList["MODULE"][$i]["#"]["VERSION"])
								&& is_array($arUpdateList["MODULE"][$i]["#"]["VERSION"]))
							{
								for ($i1 = 0, $cnt1 = count($arUpdateList["MODULE"][$i]["#"]["VERSION"]); $i1 < $cnt1; $i1++)
								{
									if (isset($arUpdateList["MODULE"][$i]["#"]["VERSION"][$i1]["#"]["VERSION_CONTROL"]) && is_array($arUpdateList["MODULE"][$i]["#"]["VERSION"][$i1]["#"]["VERSION_CONTROL"]))
									{
										for ($i2 = 0, $cnt2 = count($arUpdateList["MODULE"][$i]["#"]["VERSION"][$i1]["#"]["VERSION_CONTROL"]); $i2 < $cnt2; $i2++)
										{
											if ($bFlagTmp)
												echo ", ";
											echo "\"".$arUpdateList["MODULE"][$i]["#"]["VERSION"][$i1]["#"]["VERSION_CONTROL"][$i2]["@"]["MODULE"]."\"";
											$bFlagTmp = true;
										}
									}
								}
							}
							echo "]";
						}
					}
					?>};

					function ShowDescription(module)
					{
						if (document.getElementById("updates_float_div"))
							CloseDescription();

						var div = document.body.appendChild(document.createElement("DIV"));
						div.id = "updates_float_div";
						div.className = "settings-float-form";
						div.style.position = 'absolute';
						div.innerHTML = arModuleUpdatesDescr[module];

						var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
						var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);

						jsFloatDiv.Show(div, left, top);

						jsUtils.addEvent(document, "keypress", DescriptionOnKeyPress);
					}

					function DescriptionOnKeyPress(e)
					{
						if (!e)
							e = window.event;
						if (!e)
							return;
						if (e.keyCode == 27)
							CloseDescription();
					}

					function CloseDescription()
					{
						jsUtils.removeEvent(document, "keypress", DescriptionOnKeyPress);
						var div = document.getElementById("updates_float_div");
						jsFloatDiv.Close(div);
						div.parentNode.removeChild(div);
					}

					function DisableUpdatesTable()
					{
						document.getElementById("install_updates_sel_button").disabled = true;

						var tableUpdatesSelList = document.getElementById("table_updates_sel_list");
						var i;
						var n = tableUpdatesSelList.rows.length;
						for (i = 0; i < n; i++)
						{
							var box = tableUpdatesSelList.rows[i].cells[0].childNodes[0];
							if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
							{
								box.disabled = true;
							}
						}
					}

					function InstallUpdatesSel()
					{
						SetProgressHint("<?= GetMessage("SUP_INITIAL") ?>");

						var moduleList = "";

						globalQuantity = 0;

						var tableUpdatesSelList = document.getElementById("table_updates_sel_list");
						var i;
						var n = tableUpdatesSelList.rows.length;
						for (i = 1; i < n; i++)
						{
							var box = tableUpdatesSelList.rows[i].cells[0].childNodes[0];
							if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
							{
								if (box.checked)
								{
									if (box.name.substring(0, 14) == "select_module_")
									{
										if (moduleList.length > 0)
											moduleList += ",";
										moduleList += box.name.substring(14);
										globalQuantity += arModuleUpdatesCnt[box.name.substring(14)];
									}
								}
							}
						}

						var additionalParams = "";
						cycleModules = false;
						if (moduleList.length > 0)
						{
							cycleModules = true;
							if (additionalParams.length > 0)
								additionalParams += "&";
							additionalParams += "reqm=" + moduleList;
						}

						aStrParams = additionalParams;

						tabControl.SelectTab('tab1');
						__InstallUpdates();
						SetProgressD();
					}

					function in_array(val, arr)
					{
						for (var i = 0, l = arr.length; i < l; i++)
							if (arr[i] == val)
								return true;

						return false;
					}

					function ModuleCheckboxClicked(checkbox, module, arProcessed)
					{
						arProcessed[arProcessed.length] = module;
						if (checkbox.checked && arModuleUpdatesControl[module].length > 0)
						{
							var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
							var i;
							var n = tbl.rows.length;
							for (i = 1; i < n; i++)
							{
								var box = tbl.rows[i].cells[0].childNodes[0];
								if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
								{
									if (box.name.substr(0, 14) == "select_module_")
									{
										var moduleTmp = box.name.substr(14);
										if (!in_array(moduleTmp, arProcessed))
										{
											var i1;
											var n1 = arModuleUpdatesControl[module].length;
											for (i1 = 0; i1 < n1; i1++)
											{
												if (moduleTmp == arModuleUpdatesControl[module][i1]
													&& arModuleUpdatesControl[module][i1] != module)
												{
													arProcessed[arProcessed.length] = moduleTmp;
													box.checked = checkbox.checked;
													ModuleCheckboxClicked(box, arModuleUpdatesControl[module][i1], arProcessed);
													break;
												}
											}
										}
									}
								}
							}
						}
						if (!checkbox.checked)
						{
							var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
							var i;
							var n = tbl.rows.length;
							for (i = 1; i < n; i++)
							{
								var box = tbl.rows[i].cells[0].childNodes[0];
								if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
								{
									if (box.name.substr(0, 14) == "select_module_")
									{
										var moduleTmp = box.name.substr(14);
										if (moduleTmp != module && !in_array(moduleTmp, arProcessed) && arModuleUpdatesControl[moduleTmp].length > 0)
										{
											var i1;
											var n1 = arModuleUpdatesControl[moduleTmp].length;
											for (i1 = 0; i1 < n1; i1++)
											{
												if (module == arModuleUpdatesControl[moduleTmp][i1])
												{
													arProcessed[arProcessed.length] = moduleTmp;
													box.checked = checkbox.checked;
													ModuleCheckboxClicked(box, moduleTmp, arProcessed);
													break;
												}
											}
										}
									}
								}
							}
						}
						
						EnableInstallButton(checkbox);
					}

					function EnableInstallButton(checkbox)
					{
						var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
						var bEnable = false;
						var i;
						var n = tbl.rows.length;
						for (i = 1; i < n; i++)
						{
							var box = tbl.rows[i].cells[0].childNodes[0];
							if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
							{
								if (box.checked && !box.disabled)
								{
									bEnable = true;
									break;
								}
							}
						}
						var installUpdatesSelButton = document.getElementById("install_updates_sel_button");
						installUpdatesSelButton.disabled = !bEnable;
					}

					function SelectAllRows(checkbox)
					{
						var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
						var bChecked = checkbox.checked;
						var i;
						var n = tbl.rows.length;
						for (i = 1; i < n; i++)
						{
							var box = tbl.rows[i].cells[0].childNodes[0];
							if (box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
							{
								if (box.checked != bChecked && !box.disabled)
									box.checked = bChecked;
							}
						}
						var installUpdatesSelButton = document.getElementById("install_updates_sel_button");
						installUpdatesSelButton.disabled = !bChecked;
					}

					function LockControls()
					{
						tabControl.SelectTab('tab1');
						//tabControl.DisableTab('tab1');
						tabControl.DisableTab('tab2');
						tabControl.DisableTab('tab3');
						document.getElementById("install_updates_button").disabled = true;
						document.getElementById("id_view_updates_list_span").innerHTML = "<u><?= GetMessage("SUP_SU_UPD_VIEW") ?></u>";
						document.getElementById("id_view_updates_list_span").disabled = true;
					}

					function UnLockControls()
					{
						tabControl.EnableTab('tab1');
						tabControl.EnableTab('tab2');
						tabControl.EnableTab('tab3');
						document.getElementById("install_updates_button").disabled = <?= (($countModuleUpdates <= 0) ? "true" : "false") ?>;
						document.getElementById("id_view_updates_list_span").disabled = false;
						document.getElementById("id_view_updates_list_span").innerHTML = '<a id="id_view_updates_list" href="javascript:tabControl.SelectTab(\'tab2\');"><?= GetMessage("SUP_SU_UPD_VIEW") ?></a>';

						var cnt = document.getElementById("id_register_btn");
						if (cnt != null)
							cnt.disabled = false;
					}
				//-->
				</SCRIPT>
				<?
			}
			?>
		</td>
	</tr>

<?
$tabControl->EndTab();
if (false):
$tabControl->BeginNextTab();
?>

	<tr>
		<td colspan="2">

			<?
			if (!$bLockUpdateSystemKernel)
			{
				?>
				<div id="upd_add_module_div">
					<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
						<tr class="heading">
							<td><B><?= GetMessage("SUP_SUAC_MOD") ?></B></td>
						</tr>
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0">
									<tr>
										<td class="icon-new"><div class="icon icon-licence"></div></td>
										<td>
											<?= str_replace("#URI#", urlencode(($APPLICATION->IsHTTPS()?"https://":"http://").$_SERVER["HTTP_HOST"]."/bitrix/admin/update_system_partner.php?lang=".LANG."&addmodule=#MODULE#"), GetMessage("SUP_SUAC_HINT")) ?>
											<br><br>
											<?= GetMessage("SUP_SUAC_PROMT") ?>:<br>
											<INPUT TYPE="text" ID="id_module" onkeypress="return OnAddModuleKeyPress(event)" NAME="ADD_MODULE" value="" size="35">
											<input TYPE="button" ID="id_module_btn" NAME="module_btn" value="<?= GetMessage("SUP_SUAC_BUTTON") ?>" onclick="SearchModule()">
											<div id="searchTableDiv"></div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
				<SCRIPT LANGUAGE="JavaScript">
				<!--
				function OnAddModuleKeyPress(e)
				{
					if (window.event) // IE
						keynum = e.keyCode;
					else if(e.which) // Netscape/Firefox/Opera
						keynum = e.which;
					
					if (keynum == 13)
					{
						SearchModule();
						return false;
					}
					
					return true;
				}

				function SearchModule()
				{
					document.getElementById("id_module_btn").disabled = true;
					ShowWaitWindow();

					var searchTableDiv = document.getElementById("searchTableDiv");
					searchTableDiv.style["display"] = "none";
					searchTableDiv.innerHTML = "";

					CHttpRequest.Action = function(result)
					{
						CloseWaitWindow();
						result = PrepareString(result);
						if (result.length > 0)
						{
							var searchTableDiv = document.getElementById("searchTableDiv");
							searchTableDiv.style["display"] = "block";
							searchTableDiv.innerHTML = "<br /><br />";

							arData = result.split("~##~");
							for (var i = 0; i < arData.length; i++)
							{
								if (arData[i].length > 0)
								{
									arData1 = arData[i].split("~#~");

									var d = document.createElement("div");

									if (arData1[0].length > 0)
									{
										h = 50;
										if (arData1[6].length > 0)
											h = parseInt(arData1[6]);
										w = 50;
										if (arData1[7].length > 0)
											w = parseInt(arData1[7]);
										if (w > h)
										{
											if (w > 50)
											{
												h = 50 * h / w;
												w = 50;
											}
										}
										else
										{
											if (h > 50)
											{
												w = 50 * w / h;
												h = 50;
											}
										}
										d.innerHTML += "<img src='" + arData1[0] + "' align='left' width='" + w + "' height='" + h + "'>";
									}

									d.innerHTML += "<b>" + arData1[2] + "</b><br />";
									if (arData1[3].length > 0)
										d.innerHTML += arData1[3] + "<br />";
									d.innerHTML += "<small>" + arData1[4];
									if (arData1[4].length > 0 && arData1[5].length > 0)
										d.innerHTML += ", ";
									 d.innerHTML += arData1[5] + "</small><br />";
									d.innerHTML += "<input type='button' onclick='SearchModuleMakeLink(\"" + escape(arData1[1]) + "\")' value=\"<?= GetMessage("SUP_SUAC_LOAD_M_BUTTON") ?>\"><br /><br />";

									searchTableDiv.appendChild(d);
								}
							}
						}
						else
						{
							alert("<?= GetMessage("SUP_SUAC_ERROR") ?>");
						}
						document.getElementById("id_module_btn").disabled = false;
					}

					var param = document.getElementById("id_module").value;

					if (param.length > 0)
					{
						updRand++;
						CHttpRequest.Send('/bitrix/admin/update_system_partner_act.php?query_type=search&<?= bitrix_sessid_get() ?>&search_module=' + escape(param) + "&updRand=" + updRand);
					}
					else
					{
						document.getElementById("id_module_btn").disabled = false;
						CloseWaitWindow();
						alert("<?= GetMessage("SUP_SUAC_NO_COUP") ?>");
					}
				}

				function SearchModuleMakeLink(module)
				{
					if (!confirm("<?= GetMessage("SUP_SUAC_LOAD_M_BUTTON_CONF") ?>"))
						return;
					window.location="update_system_partner.php?lang=<?= LANG ?>&addmodule=" + escape(module);
				}

				function AddModule()
				{
					document.getElementById("id_module_btn").disabled = true;

					var param = document.getElementById("id_module").value;
					if (param.length > 0)
					{
						var i = param.indexOf(".");
						if (i >= 0)
						{
							alert("<?= GetMessage("SUP_SUAC_SUCCESS") ?>");
							window.location.href = "update_system_partner.php?lang=<?= LANG ?>&addmodule=" + escape(param);
						}
						else
						{
							document.getElementById("id_module_btn").disabled = false;
							CloseWaitWindow();
							alert("<?= GetMessage("SUP_SUAC_NO_COUP1") ?>");
						}
					}
					else
					{
						document.getElementById("id_module_btn").disabled = false;
						CloseWaitWindow();
						alert("<?= GetMessage("SUP_SUAC_NO_COUP") ?>");
					}
				}
				//-->
				</SCRIPT>
				<?
			}
			?>
		</td>
	</tr>

<?
$tabControl->EndTab();
endif;
$tabControl->End();
?>

<SCRIPT LANGUAGE="JavaScript">
<!--
	<?
	if ($bLockControls)
		echo "LockControls();";
	?>
//-->
</SCRIPT>

</form>

<?echo BeginNote();?>
<b><?= GetMessage("SUP_SUG_NOTES") ?></b><br><br>
<?= GetMessage("SUP_SUG_NOTES1") ?>
<?echo EndNote(); ?>

<?
COption::SetOptionString("main", "update_system_check", Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>