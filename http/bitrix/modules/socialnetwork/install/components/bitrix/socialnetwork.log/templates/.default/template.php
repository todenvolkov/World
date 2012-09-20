<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	CAjax::Init();
	CUtil::InitJSCore(array("ajax", "window", "tooltip"));
	?><script language="JavaScript">
	<!--
		BX.message({
			sonetLGetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log/ajax.php')?>',
			sonetLSetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log/ajax.php')?>',
			sonetLSessid: '<?=bitrix_sessid_get()?>',
			sonetLLangId: '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
			sonetLSiteId: '<?=CUtil::JSEscape(SITE_ID)?>',
			sonetLNoSubscriptions: '<?=CUtil::JSEscape(GetMessage("SONET_C30_NO_SUBSCRIPTIONS"))?>',
			sonetLInherited: '<?=CUtil::JSEscape(GetMessage("SONET_C30_INHERITED"))?>',
			sonetLDialogClose: '<?=CUtil::JSEscape(GetMessage("SONET_C30_DIALOG_CLOSE_BUTTON"))?>',
			sonetLTransportTitle: '<?=CUtil::JSEscape(GetMessage("SONET_C30_DIALOG_TRANSPORT_TITLE"))?>'
		});	
	//-->
	</script>
	<?
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}
	
	unset($arResult["ActiveFeatures"]["all"]);
	if ($arParams["SHOW_EVENT_ID_FILTER"] == "Y" && !empty($arResult["ActiveFeatures"]))
	{
		?>
		<div id="bx_sl_filter_hidden" class="sonet-log-filter" style="display: block;">
			<div class="sonet-log-filter-lt">
				<div class="sonet-log-filter-rt"></div>
			</div>
			<div id="bx_sl_filter_content">
				<span class="sonet-log-filter-lamp <?=($arResult["IS_FILTERED"] ? "sonet-log-filter-lamp-a" : "sonet-log-filter-lamp-na") ?>"></span>
				<span class="sonet-log-filter-title"><?=GetMessage("SONET_C30_T_FILTER_TITLE");?></span>
				<a id="sonet_log_filter_show" href="javascript:void(0)" onclick="__logFilterShow(); return false;"><?=GetMessage("SONET_C30_T_FILTER_SHOW");?></a>
			</div>
			<div class="sonet-log-filter-lb">
				<div class="sonet-log-filter-rb"></div>
			</div>
		</div>

		<div id="bx_sl_filter" class="sonet-log-filter" style="display: none;">
			<div class="sonet-log-filter-lt">
				<div class="sonet-log-filter-rt"></div>
			</div>
			<div id="bx_sl_filter_content">
				<span class="sonet-log-filter-lamp <?=($arResult["IS_FILTERED"] ? "sonet-log-filter-lamp-a" : "sonet-log-filter-lamp-na") ?>"></span>
				<span class="sonet-log-filter-title"><?=GetMessage("SONET_C30_T_FILTER_TITLE");?></span>
				<a id="sonet_log_filter_hide" href="javascript:void(0)" onclick="__logFilterShow(); return false;"><?=GetMessage("SONET_C30_T_FILTER_HIDE");?></a>
			<div class="sonet-log-filter-line"></div>
			<form method="GET" name="log_filter">
			<script type="text/javascript">
				var arFltFeaturesID = new Array();
			</script>
			<div class="log-filter-title"><?=GetMessage("SONET_C30_T_FILTER_FEATURES_TITLE")?></div>
			<? 
			$max_cols = 5;
			?>
			<table cellspacing="0" border="0">
			<tr>
				<? 
				$bCheckedAll = true;
				$cnt_cols = 1;
				
				foreach ($arResult["ActiveFeatures"] as $featureID => $featureName):
				
					if ($cnt_cols > $max_cols)
					{
						$cnt_cols = 1;
						?></tr><tr><?
					}
					?>
					<td width="<?=intval(100/$max_cols)?>%" valign="top">
						<script type="text/javascript">
							arFltFeaturesID.push('<?=$featureID?>');
						</script>
						<?
						if (!$featureName)
							$featureName = GetMessage(toUpper("SONET_C30_T_FEATURE_".$arParams["ENTITY_TYPE"]."_".$featureID));
							
						if (array_key_exists("flt_event_id", $_REQUEST) && in_array($featureID, $_REQUEST["flt_event_id"]) || empty($arParams["EVENT_ID"]) || in_array("all", $arParams["EVENT_ID"]))
							$bChecked = true;
						else
						{
							$bChecked = false;
							$bCheckedAll = false;
						}
						?>
						<div class="sonet-log-filter-checkbox"><nobr><input type="checkbox" id="flt_event_id_<?=$featureID?>" name="flt_event_id[]" value="<?=$featureID?>" <?=($bChecked ? "checked" : "")?> onclick="__logFilterClick('<?=$featureID?>')"> <label for="flt_event_id_<?=$featureID?>"><?=$featureName?></label></nobr></div>
					</td>
					<?
					$cnt_cols++;

				endforeach;
				
				for ($i = $cnt_cols; $i <= $max_cols; $i++)
				{
					?><td width="<?=intval(100/$cnt_cols)?>%" valign="top">&nbsp;</td><?
				}
				?>
			</tr>
			</table>
			<div class="sonet-log-filter-line"></div>
			<table cellspacing="0" border="0">
			<tr>
				<td valign="top">
				<div style="width: 200px;">
					<div class="sonet-log-filter-createdby-title"><?=GetMessage("SONET_C30_T_FILTER_CREATED_BY");?>:</div>
					<? 
					if (IsModuleInstalled("intranet")):
						?>
						<?
						$GLOBALS["APPLICATION"]->IncludeComponent('bitrix:intranet.user.selector', '', array(
							'INPUT_NAME' => "flt_created_by_id",
							'INPUT_NAME_STRING' => "flt_created_by_string",
							'INPUT_NAME_SUSPICIOUS' => "flt_created_by_suspicious",
							'INPUT_VALUE_STRING' => htmlspecialcharsback($_REQUEST["flt_created_by_string"]),
							'EXTERNAL' => 'A',
							'MULTIPLE' => 'N',
							),
							false,
							array("HIDE_ICONS" => "Y")
						);
						?>
						<?
					else:
						?>
						<?
						$APPLICATION->IncludeComponent("bitrix:socialnetwork.user_search_input", ".default", array(
								"TEXT" => 'size="20"',
								"EXTRANET" => "I",
								"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
								"SHOW_LOGIN" => $arParams["SHOW_LOGIN"]
							)
						);								
						?>
						<?
					endif;
					?>
				</div>
				</td>
				<td valign="top">
				<div class="sonet-log-filter-date-title"><?=GetMessage("SONET_C30_T_FILTER_DATE");?>:</div>
					<?$APPLICATION->IncludeComponent(
						"bitrix:main.calendar",
						"",
						Array(
							"SHOW_INPUT" => "Y", 
							"FORM_NAME" => "log_filter", 
							"INPUT_NAME" => "flt_date_from", 
							"INPUT_NAME_FINISH" => "flt_date_to", 
							"INPUT_VALUE" => $_REQUEST["flt_date_from"], 
							"INPUT_VALUE_FINISH" => $_REQUEST["flt_date_to"], 
							"SHOW_TIME" => "N" 
						)
					);?>
				</td>
			</tr>
			</table>
			<?
			if (array_key_exists("flt_show_hidden", $_REQUEST) && $_REQUEST["flt_show_hidden"] == "Y")
				$bChecked = true;
			else
				$bChecked = false;
			?>
			<div><nobr><input type="checkbox" id="flt_show_hidden" name="flt_show_hidden" value="Y" <?=($bChecked ? "checked" : "")?> onclick="__logFilterClick('<?=$featureID?>')"> <label for="flt_show_hidden"><?=GetMessage("SONET_C30_T_SHOW_HIDDEN")?></label></nobr></div>
			<div class="sonet-log-filter-line"></div>
			<div class="sonet-log-filter-submit"><input type="submit" name="log_filter_submit" value="<?=GetMessage("SONET_C30_T_SUBMIT")?>"></div>
			<input type="hidden" id="flt_event_id_all" name="flt_event_id_all" value="<?=($bCheckedAll ? "Y" : "")?>">
			</form>
		
			</div>
			<div class="sonet-log-filter-lb">
				<div class="sonet-log-filter-rb"></div>
			</div>
		</div>

		<div class="sonet-profile-line"></div>		
		<br><br>
		
		<div style="float: right;">
		<?
		
		$APPLICATION->IncludeComponent(
			"bitrix:socialnetwork.log.rss.link",
			"",
			Array(
				"PATH_TO_RSS" => $arParams["~PATH_TO_LOG_RSS"],
				"PATH_TO_RSS_MASK" => $arParams["~PATH_TO_LOG_RSS_MASK"],
				"ENTITY_TYPE" => $arParams["ENTITY_TYPE"],
				"ENTITY_ID" => ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? $arParams["GROUP_ID"] : $arParams["USER_ID"]),
				"EVENT_ID" => $arParams["EVENT_ID"]
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
		?></div><?
	}
	
	if ($arResult["EventsNew"] && is_array($arResult["EventsNew"]) && count($arResult["EventsNew"]) > 0)
	{
		$ind = 0;
		$day_cnt = 0;
		?>
		<script type="text/javascript">
			var arDays = [];
		</script>

		<table cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout:fixed">
		
		<col class="sonet-log-table-message">
		<col class="sonet-log-message-createdby">
		<col class="sonet-log-message-where">
		
		<?
		foreach ($arResult["EventsNew"] as $date => $arEvents)
		{
			if (
				$arResult["PAGE_NUMBER"] == 1
				&& $ind > ($arParams["PAGE_SIZE"] / 2)
			)
				$bDateOpen = false;
			else	
				$bDateOpen = true;
			?>
			<tr>
				<td colspan="3">
				<table width="100%" class="sonet-log-table">
				<tr>
					<td class="sonet-log-header">
					<table width="100%">
					<tr>
						<td class="sonet-log-header-left" onclick="__logDayShow('<?=$day_cnt?>'); return false;"><img src="/bitrix/images/1.gif" width="2" height="29"></td>
						<td class="sonet-log-header-center" nowrap onclick="__logDayShow('<?=$day_cnt?>'); return false;"><div class="sonet-log-header-day"><nobr><b><?= $date ?></b></nobr></div></td>
						<td class="sonet-log-header-center" onclick="__logDayShow('<?=$day_cnt?>'); return false;"><div class="sonet-log-header-day-counter"><div id="sonet_log_day_counter_<?=$day_cnt?>" style="display: <?=(!$bDateOpen ? "block" : "none")?>;">(<?=count($arEvents)?>)</div></div></td>
						<td class="sonet-log-header-center" onclick="__logDayShow('<?=$day_cnt?>'); return false;"><div class="sonet-log-header-day-arrow"><div id="sonet_log_day_arrow_<?=$day_cnt?>" class="sonet-log-header-arrow <?=(!$bDateOpen ? " sonet-log-header-arrow-a" : " sonet-log-header-arrow-na")?>"></div></div></td>
						<td class="sonet-log-header-right" onclick="__logDayShow('<?=$day_cnt?>'); return false;"><img src="/bitrix/images/1.gif" width="2" height="29"></td>
						<td class="sonet-log-header-line"><img src="/bitrix/images/1.gif" width="1" height="1"></td>
					</tr>
					</table>
					</td>
				</tr>
				</table>
				</td>
			</tr>
			<?
			foreach ($arEvents as $arEvent)
			{
				if (!empty($arEvent["EVENT"]))
				{
					?>
					<tr class="sonet-log-row" id="sonet_log_day_row_<?=$ind?>" style="display: <?=($bDateOpen ? "table-row" : "none")?>;" onmouseover="__logRowOver(this);" onmouseout="__logRowOut(this);">
						<td class="sonet-log-table-message">
							<!-- event -->						
							<table cellspacing="0" cellpadding="0" width="100%" style="table-layout:fixed;">
							<tr>
								<td width="1"><div class="sonet-log-message-icons"></div></td>
								<td width="25" valign="top">
									<div class="sonet-log-event sonet-log-event-<?=str_replace("_", "-", $arEvent["EVENT"]["EVENT_ID"])?>"></div>
									<a class="sonet-log-subscribe" href="javascript:__logShowSubscribeDialog(<?=$ind ?>, '<?=$arEvent["EVENT"]["ENTITY_TYPE"] ?>', <?=$arEvent["EVENT"]["ENTITY_ID"] ?>, '<?=$arEvent["EVENT"]["EVENT_ID"] ?>', '<?=$arEvent["EVENT"]["USER_ID"] ?>', true)"></a>
									<div id="sonet_log_subscribe_<?=$ind ?>"></div>
								</td>
								<td valign="top" class="sonet-log-message-body">
									<span class="sonet-log-date"><?=$arEvent["LOG_TIME_FORMAT"]?></span><br />
									<?
									if (
										array_key_exists("EVENT_FORMATTED", $arEvent)
										&& is_array($arEvent["EVENT_FORMATTED"])
										&& array_key_exists("TITLE", $arEvent["EVENT_FORMATTED"])
										&& strlen($arEvent["EVENT_FORMATTED"]["TITLE"]) > 0
									)
										echo $arEvent["EVENT_FORMATTED"]["TITLE"];
									?>
									<?
									if (strlen($arEvent["EVENT_FORMATTED"]["MESSAGE"]) > 0):
										?>
										<div id="sonet_log_message_short_<?=$ind ?>" class="sonet-log-message-short sonet-log-message-show">
											<?= substr(HTMLToTxt(htmlspecialcharsback($arEvent["EVENT_FORMATTED"]["MESSAGE"])), 0, 200); ?>
										</div>
										<div id="sonet_log_message_full_<?=$ind ?>" class="sonet-log-message-full sonet-log-message-hide">
											<?= CSocNetTextParser::closetags(htmlspecialcharsback($arEvent["EVENT_FORMATTED"]["MESSAGE"])); ?>
										</div>
										<a class="sonet-log-message-switch" href="javascript:__logSwitchBody(<?=$ind ?>);" style="display: inline-block;" id="sonet_log_message_switch_show_<?=$ind?>"><?= GetMessage("SONET_C30_T_MESSAGE_SHOW") ?></a>
										<a class="sonet-log-message-switch" href="javascript:__logSwitchBody(<?=$ind ?>);" style="display: none;" id="sonet_log_message_switch_hide_<?=$ind?>"><?= GetMessage("SONET_C30_T_MESSAGE_HIDE") ?></a>
										<?
									endif;
									?>
									
								</td>
							</tr>
							</table>
						</td>
						<td class="sonet-log-message-createdby">
							<!-- created by -->
							<?
							if (
								array_key_exists("CREATED_BY", $arEvent)
								&& is_array($arEvent["CREATED_BY"])
								&& array_key_exists("TOOLTIP_FIELDS", $arEvent["CREATED_BY"])
								&& is_array($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"])								
							)
							{
								?><div class="sonet-log-createdby-pre"><?

								if (
									array_key_exists("ACTION_TYPE", $arEvent["CREATED_BY"])
									&& strlen($arEvent["CREATED_BY"]["ACTION_TYPE"]) > 0
								)
									echo GetMessage("SONET_C30_T_ACTION_".strtoupper($arEvent["CREATED_BY"]["ACTION_TYPE"]));
								else
									echo GetMessage("SONET_C30_T_ACTION_AUTHOR");

								?></div><?

								?><div class="sonet-log-createdby"><?
								$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
													'',
													$arEvent["CREATED_BY"]["TOOLTIP_FIELDS"],
													false,
													array("HIDE_ICONS" => "Y")
												);
								?></div><?
							}
							?>
						</td>
						<td class="sonet-log-message-where">
							<!-- entity -->
							<?
							if (
								array_key_exists("ENTITY", $arEvent)
								&& is_array($arEvent["ENTITY"])
							)
							{
								if (
									array_key_exists("TOOLTIP_FIELDS", $arEvent["ENTITY"])
									&& is_array($arEvent["ENTITY"]["TOOLTIP_FIELDS"])
								)
								{
									?><div class="sonet-log-where-pre"><?
									echo $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][SONET_SUBSCRIBE_ENTITY_USER]["TITLE_ENTITY"]."<br>";
									?></div><?
									?><div class="sonet-log-where"><?
									$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
														'',
														$arEvent["ENTITY"]["TOOLTIP_FIELDS"],
														false,
														array("HIDE_ICONS" => "Y")
													);
									?></div><?
								}
								elseif (
									array_key_exists("FORMATTED", $arEvent["ENTITY"])
									&& is_array($arEvent["ENTITY"]["FORMATTED"])
									&& array_key_exists("NAME", $arEvent["ENTITY"]["FORMATTED"])
									&& strlen($arEvent["ENTITY"]["FORMATTED"]["NAME"]) > 0
								)
								{
									if (
										array_key_exists("TYPE_NAME", $arEvent["ENTITY"]["FORMATTED"])
										&& strlen($arEvent["ENTITY"]["FORMATTED"]["TYPE_NAME"]) > 0
									)
									{
										?><div class="sonet-log-where-pre"><?
										echo $arEvent["ENTITY"]["FORMATTED"]["TYPE_NAME"];
										?></div><?
									}

									?><div class="sonet-log-where"><?
									if (
										array_key_exists("URL", $arEvent["ENTITY"]["FORMATTED"])
										&& strlen($arEvent["ENTITY"]["FORMATTED"]["URL"]) > 0
									)
										echo '<a href="'.$arEvent["ENTITY"]["FORMATTED"]["URL"].'">'.$arEvent["ENTITY"]["FORMATTED"]["NAME"].'</a>';
									else
										echo $arEvent["ENTITY"]["FORMATTED"]["NAME"];
									?></div><?
								}
									
							}
							?>
							<script type="text/javascript">
								if (!arDays[<?=$day_cnt?>])
									arDays[<?=$day_cnt?>] = [];
								arDays[<?=$day_cnt?>][arDays[<?=$day_cnt?>].length++] = <?=$ind?>;
							</script>
						</td>
					</tr>
					<?
				}
				else
				{
					?>
					<tr style="display: <?=($bDateOpen ? "table-row" : "none")?>;">
						<td colspan="3">
							<!-- empty -->
						<script type="text/javascript">
							if (!arDays[<?=$day_cnt?>])
								arDays[<?=$day_cnt?>] = [];			
							arDays[<?=$day_cnt?>][arDays[<?=$day_cnt?>].length++] = <?=$ind?>;
						</script>							
						</td>
					</tr>
					<?
				}
				$ind++;
			}
			$day_cnt++;
		}
		?>
		</table>
		<?
		if (StrLen($arResult["NAV_STRING"]) > 0)
		{
			?><div class="sonet-log-nav"><?
			echo $arResult["NAV_STRING"];
			?></div><?
		}
		
	}
	else
	{
		if ($arParams["SUBSCRIBE_ONLY"] == "Y")
			echo GetMessage("SONET_C30_T_NO_UPDATES");
		else
			echo GetMessage("SONET_C30_T_NO_UPDATES_NO_SUBSCRIBE");
	}
}
?>