<?
$module_id = "sale";
$SALE_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($SALE_RIGHT>="R") :

global $MESS;
include(GetLangFileName($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/", "/options.php"));
include(GetLangFileName($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/sale/lang/", "/options.php"));

include_once($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$siteList = array();
$rsSites = CSite::GetList($by="sort", $order="asc", Array());
while($arRes = $rsSites->GetNext())
{
	$siteList[] = Array("ID" => $arRes["ID"], "NAME" => $arRes["NAME"]);
}
$siteCount = count($siteList);

$bWasUpdated = false;

if ($REQUEST_METHOD=="GET" && strlen($RestoreDefaults)>0 && $SALE_RIGHT=="W" && check_bitrix_sessid())
{
	$bWasUpdated = true;
	
	COption::RemoveOption("sale");
	$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
	while($zr = $z->Fetch())
		$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
}

$arAllOptions =
	Array(
		Array("order_email", GetMessage("SALE_EMAIL_ORDER"), "order@".$SERVER_NAME, Array("text", 30)),
		//Array("default_email", GetMessage("SALE_EMAIL_REGISTER"), "admin@".$SERVER_NAME, Array("text", 30)),
		Array("delete_after", GetMessage("SALE_DELETE_AFTER"), "", Array("text", 10)),
		Array("order_list_date", GetMessage("SALE_ORDER_LIST_DATE"), 30, Array("text", 10)),
		Array("MAX_LOCK_TIME", GetMessage("SALE_MAX_LOCK_TIME"), 30, Array("text", 10)),
		Array("GRAPH_WEIGHT", GetMessage("SALE_GRAPH_WEIGHT"), 600, Array("text", 10)),
		Array("GRAPH_HEIGHT", GetMessage("SALE_GRAPH_HEIGHT"), 600, Array("text", 10)),
		Array("path2user_ps_files", GetMessage("SALE_PATH2UPSF"), BX_PERSONAL_ROOT."/php_interface/include/sale_payment/", Array("text", 40)),
		Array("path2custom_view_order", GetMessage("SMO_SALE_PATH2ORDER"), "", Array("text", 40)),
		Array("lock_catalog", GetMessage("SMO_LOCK_CATALOG"), "Y", Array("checkbox", 40)),
		Array("affiliate_param_name", GetMessage("SMOS_AFFILIATE_PARAM"), "partner", Array("text", 40)),
		Array("affiliate_life_time", GetMessage("SMO_AFFILIATE_LIFE_TIME"), "30", Array("text", 10)),
		Array("show_order_sum", GetMessage("SMO_SHOW_ORDER_SUM"), "N", Array("checkbox", 40)),
		Array("show_order_product_xml_id", GetMessage("SMO_SHOW_ORDER_PRODUCT_XML_ID"), "N", Array("checkbox", 40)),
		Array("show_paysystem_action_id", GetMessage("SMO_SHOW_PAYSYSTEM_ACTION_ID"), "N", Array("checkbox", 40)),
		Array("measurement_path", GetMessage("SMO_MEASUREMENT_PATH"), "/bitrix/modules/sale/measurements.php", Array("text", 40)),
		//Array("use_delivery_handlers", GetMessage("SMO_USE_DELIVERY_HANDLERS"), "N", Array("checkbox", 40)),
		Array("delivery_handles_custom_path", GetMessage("SMO_DELIVERY_HANDLERS_CUSTOM_PATH"), BX_PERSONAL_ROOT."/php_interface/include/sale_delivery/", Array("text", 40)),
		Array("use_secure_cookies", GetMessage("SMO_USE_SECURE_COOKIES"), "N", Array("checkbox", 40)),
		Array("recalc_product_list", GetMessage("SALE_RECALC_PRODUCT_LIST"), "N", Array("checkbox", 40)),
		Array("recalc_product_list_period", GetMessage("SALE_RECALC_PRODUCT_LIST_PERIOD"), 7, Array("text", 10)),
		);

$arOrderFlags = array("P" => GetMessage("SMO_PAYMENT_FLAG"), "C" => GetMessage("SMO_CANCEL_FLAG"), "D" => GetMessage("SMO_DELIVERY_FLAG"));

$strWarning = "";
if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && $SALE_RIGHT=="W" && check_bitrix_sessid())
{
	$bWasUpdated = true;

	COption::RemoveOption($module_id, "weight_unit");
	COption::RemoveOption($module_id, "weight_koef");
	
	if (!empty($_REQUEST["WEIGHT_dif_settings"]))
	{
		for ($i = 0; $i < $siteCount; $i++)
		{
			COption::SetOptionString($module_id, "weight_unit", trim($_REQUEST["weight_unit"][$siteList[$i]["ID"]]), false, $siteList[$i]["ID"]);
			COption::SetOptionString($module_id, "weight_koef", floatval($_REQUEST["weight_koef"][$siteList[$i]["ID"]]), false, $siteList[$i]["ID"]);
		}
		COption::SetOptionString($module_id, "WEIGHT_different_set", "Y");
	}
	else
	{
		$site_id = trim($_REQUEST["WEIGHT_current_site"]);
		COption::SetOptionString($module_id, "weight_unit", trim($_REQUEST["weight_unit"][$site_id]));
		COption::SetOptionString($module_id, "weight_koef", floatval($_REQUEST["weight_koef"][$site_id]));
		COption::SetOptionString($module_id, "WEIGHT_different_set", "N");
	}
	
	COption::RemoveOption($module_id, "location_zip");
	COption::RemoveOption($module_id, "location");
	
	if (!empty($_REQUEST["ADDRESS_dif_settings"]))
	{
		for ($i = 0; $i < $siteCount; $i++)
		{
			COption::SetOptionInt($module_id, "location_zip", intval($_REQUEST["location_zip"][$siteList[$i]["ID"]]), false, $siteList[$i]["ID"]);
			COption::SetOptionInt($module_id, "location", intval($_REQUEST["location"][$siteList[$i]["ID"]]), false, $siteList[$i]["ID"]);
		}
		COption::SetOptionString($module_id, "ADDRESS_different_set", "Y");
	}
	else
	{
		$site_id = trim($_REQUEST["ADDRESS_current_site"]);
		COption::SetOptionInt($module_id, "location_zip", intval($_REQUEST["location_zip"][$site_id]));
		COption::SetOptionInt($module_id, "location", intval($_REQUEST["location"][$site_id]));
		COption::SetOptionString($module_id, "ADDRESS_different_set", "N");
	}

	CAgent::RemoveAgent("CSaleUser::DeleteOldAgent(".COption::GetOptionString("sale", "delete_after", "365").");", "sale");
	if(COption::GetOptionString("sale", "recalc_product_list", "N") != $recalc_product_list || COption::GetOptionInt("sale", "recalc_product_list_period", 7) != $recalc_product_list_period)
		CAgent::RemoveAgent("CSaleProduct::RefreshProductList();", "sale");
	if($recalc_product_list == "Y" && IntVal($recalc_product_list_period) > 0 && COption::GetOptionInt("sale", "recalc_product_list_period", 7) != $recalc_product_list_period || COption::GetOptionString("sale", "recalc_product_list", "N") == "N")
		CAgent::AddAgent("CSaleProduct::RefreshProductList();", "sale", "N", 60*60*24*IntVal($recalc_product_list_period), "", "Y");

	for ($i = 0; $i<count($arAllOptions); $i++)
	{
		$name = $arAllOptions[$i][0];
		$val = $$name;
		if ($arAllOptions[$i][3][0]=="checkbox" && $val!="Y")
			$val = "N";
		COption::SetOptionString("sale", $name, $val, $arAllOptions[$i][1]);
	}

	COption::SetOptionString("sale", "affiliate_plan_type", $affiliate_plan_type);
	
	$arAmountSer = Array();
	foreach($amount_val as $key =>$val)
	{
		if(DoubleVal($val) > 0)
			$arAmountSer[$key] = Array("AMOUNT" => DoubleVal($val), "CURRENCY" => $amount_currency[$key]);
	}
	if(!empty($arAmountSer))
		COption::SetOptionString("sale", "pay_amount", serialize($arAmountSer));

	if(!empty($reminder))
		COption::SetOptionString("sale", "pay_reminder", serialize($reminder));
		
	COption::SetOptionString("sale", "default_currency", $CURRENCY_DEFAULT);
	COption::SetOptionString("sale", "crypt_algorithm", $crypt_algorithm);
	COption::SetOptionString("sale", "sale_data_file", $sale_data_file);
	COption::SetOptionString("sale", "status_on_paid", $PAID_STATUS);
	COption::SetOptionString("sale", "status_on_allow_delivery", $ALLOW_DELIVERY_STATUS);

	if (is_array($SELECTED_FIELDS) && count($SELECTED_FIELDS) > 0)
	{
		for ($i = 0; $i < count($SELECTED_FIELDS); $i++)
		{
			if (strlen($saveValue) > 0)
				$saveValue .= ",";

			$saveValue .= $SELECTED_FIELDS[$i];
		}
	}
	else
	{
		$saveValue = "ID,USER,PAY_SYSTEM,PRICE,STATUS,PAYED,PS_STATUS,CANCELED,BASKET";
	}
	COption::SetOptionString("sale", "order_list_fields", $saveValue);

	$db_result_lang = CLang::GetList(($by1="sort"), ($order1="asc"));
	while ($db_result_lang_array = $db_result_lang->Fetch())
	{
		$valCurrency = Trim(${"CURRENCY_".$db_result_lang_array["LID"]});
		UnSet($arFields);
		$arFields["LID"] = $db_result_lang_array["LID"];
		if (strlen($valCurrency)<=0) $valCurrency = false;
		$arFields["CURRENCY"] = $valCurrency;
		if ($arRes = CSaleLang::GetByID($db_result_lang_array["LID"]))
		{
			if ($valCurrency!==false)
			{
				CSaleLang::Update($db_result_lang_array["LID"], $arFields);
			}
			else
			{
				CSaleLang::Delete($db_result_lang_array["LID"]);
			}
		}
		else
		{
			if ($valCurrency!==false)
			{
				CSaleLang::Add($arFields);
			}
		}

		CSaleGroupAccessToSite::DeleteBySite($db_result_lang_array["LID"]);
		if (isset(${"SITE_USER_GROUPS_".$db_result_lang_array["LID"]})
			&& is_array(${"SITE_USER_GROUPS_".$db_result_lang_array["LID"]}))
		{
			for ($i = 0; $i < count(${"SITE_USER_GROUPS_".$db_result_lang_array["LID"]}); $i++)
			{
				if (IntVal(${"SITE_USER_GROUPS_".$db_result_lang_array["LID"]}[$i]) > 0)
				{
					CSaleGroupAccessToSite::Add(
							array(
									"SITE_ID" => $db_result_lang_array["LID"],
									"GROUP_ID" => IntVal(${"SITE_USER_GROUPS_".$db_result_lang_array["LID"]}[$i])
								)
						);
				}
			}
		}
	}

	if (IntVal($delete_after) > 0)
		CAgent::AddAgent("CSaleUser::DeleteOldAgent(".IntVal($delete_after).");", "sale", "N", 8*60*60, "", "Y");
	
	$dbExport = CSaleExport::GetList();
	while($arExport = $dbExport->Fetch())
	{
		$arExportProfile[$arExport["PERSON_TYPE_ID"]] = $arExport["ID"];
	}

	$dbPersonType = CSalePersonType::GetList(
			array("SORT" => "ASC"),
			array("ACTIVE" => "Y")
		);
	while ($arPersonType = $dbPersonType->GetNext())
	{
		$arParams = array();

		if (strlen(${"export_fields_".$arPersonType["ID"]}) > 0)
		{
			$arActFields = explode(",", ${"export_fields_".$arPersonType["ID"]});
			for ($i = 0; $i < count($arActFields); $i++)
			{
				$arActFields[$i] = Trim($arActFields[$i]);

				$typeTmp = ${"TYPE_".$arActFields[$i]."_".$arPersonType["ID"]};
				$valueTmp = ${"VALUE1_".$arActFields[$i]."_".$arPersonType["ID"]};
				if (strlen($typeTmp) <= 0)
					$valueTmp = ${"VALUE2_".$arActFields[$i]."_".$arPersonType["ID"]};

				$arParams[$arActFields[$i]] = array(
						"TYPE" => $typeTmp,
						"VALUE" => $valueTmp
					);
			}
			$arParams["IS_FIZ"] = ((${"person_type_1c_".$arPersonType["ID"]}=="FIZ")?"Y":"N");
		}
		if(IntVal($arExportProfile[$arPersonType["ID"]])>0)
			$res = CSaleExport::Update($arExportProfile[$arPersonType["ID"]], Array("PERSON_TYPE_ID" => $arPersonType["ID"], "VARS" => serialize($arParams)));
		else
			$res = CSaleExport::Add(Array("PERSON_TYPE_ID" => $arPersonType["ID"], "VARS" => serialize($arParams)));
	}
}

$arStatuses = Array("" => GetMessage("SMO_STATUS"));
$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID), false, false, Array("ID", "NAME", "SORT"));
while ($arStatus = $dbStatus->GetNext())
{
	$arStatuses[$arStatus["ID"]] = "[".$arStatus["ID"]."] ".$arStatus["NAME"];
}


$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "sale_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit7", "TAB" => GetMessage("SALE_TAB_WEIGHT"), "ICON" => "sale_settings", "TITLE" => GetMessage("SALE_TAB_WEIGHT_TITLE")),
	array("DIV" => "edit5", "TAB" => GetMessage("SALE_TAB_ADDRESS"), "ICON" => "sale_settings", "TITLE" => GetMessage("SALE_TAB_ADDRESS_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("SALE_TAB_2"), "ICON" => "sale_settings", "TITLE" => GetMessage("SMO_CRYPT_TITLE")),
	array("DIV" => "edit3", "TAB" => GetMessage("SALE_TAB_3"), "ICON" => "sale_settings", "TITLE" => GetMessage("SALE_TAB_3_TITLE")),
	array("DIV" => "edit6", "TAB" => GetMessage("SALE_TAB_6"), "ICON" => "sale_settings", "TITLE" => GetMessage("SALE_TAB_6_TITLE")),	
	array("DIV" => "edit4", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "sale_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);


if(strlen($strWarning)>0)
	CAdminMessage::ShowMessage($strWarning);
/*
elseif ($bWasUpdated)
{
	if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam()); 
}
*/
?>
<?
$tabControl->Begin();
?><form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&lang=<?=LANGUAGE_ID?>" name="opt_form">
<?=bitrix_sessid_post()?>
<?
$tabControl->BeginNextTab();
	for($i=0; $i<count($arAllOptions); $i++):
		$Option = $arAllOptions[$i];
		$val = COption::GetOptionString("sale", $Option[0], $Option[2]);
		$type = $Option[3];

		if ($Option[0]=="assist_LOGIN" || $Option[0]=="assist_PASSWORD")
		{
			if ($SALE_RIGHT!="W") $val = "........";
		}

		?>
		<tr>
			<td valign="top" width="50%"><?	if($type[0]=="checkbox")
							echo "<label for=\"".htmlspecialchars($Option[0])."\">".$Option[1]."</label>";
						else
							echo $Option[1];?></td>
			<td valign="middle" width="50%">

					<?if($type[0]=="checkbox"):?>
						<input type="checkbox" name="<?echo htmlspecialchars($Option[0])?>" id="<?echo htmlspecialchars($Option[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
					<?elseif($type[0]=="text"):?>
						<input type="text" size="<?echo $type[1]?>" value="<?echo htmlspecialchars($val)?>" name="<?echo htmlspecialchars($Option[0])?>">
					<?elseif($type[0]=="textarea"):?>
						<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialchars($Option[0])?>"><?echo htmlspecialchars($val)?></textarea>
					<?endif?>

			</td>
		</tr>
	<?endfor;?>
	<tr>
		<td valign="top">
			<?echo GetMessage("SALE_DEF_CURR")?>
		</td>
		<td valign="middle">
			<?
			$val = COption::GetOptionString("sale", "default_currency", "RUB");
			echo CCurrency::SelectBox("CURRENCY_DEFAULT", $val, "", True, "");
			?>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?echo GetMessage("SMO_AFFILIATE_PLAN_TYPE")?>:
		</td>
		<td valign="middle">
			<?
			$val = COption::GetOptionString("sale", "affiliate_plan_type", "N");
			?>
			<select name="affiliate_plan_type">
				<option value="N"<?if ($val == "N") echo " selected";?>><?= GetMessage("SMO_AFFILIATE_PLAN_TYPE_N") ?></option>
				<option value="S"<?if ($val == "S") echo " selected";?>><?= GetMessage("SMO_AFFILIATE_PLAN_TYPE_S") ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?echo GetMessage("SALE_PAY_TO_STATUS")?>
		</td>
		<td valign="middle">
			<?
			$val = COption::GetOptionString("sale", "status_on_paid", "");
			?>
			<select name="PAID_STATUS">
				<?
				foreach($arStatuses as $statusID => $statusName)
				{
					?><option value="<?=$statusID?>"<?if ($val == $statusID) echo " selected";?>><?=$statusName?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?echo GetMessage("SALE_ALLOW_DELIVERY_TO_STATUS")?>
		</td>
		<td valign="middle">
			<?
			$val = COption::GetOptionString("sale", "status_on_allow_delivery", "");
			?>
			<select name="ALLOW_DELIVERY_STATUS">
				<?
				foreach($arStatuses as $statusID => $statusName)
				{
					?><option value="<?=$statusID?>"<?if ($val == $statusID) echo " selected";?>><?=$statusName?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("SALE_AMOUNT_NAME")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		<table cellspacing="0" cellpadding="0" border="0" class="internal">
		<tr class="heading">
			<td valign="top">
				<?echo GetMessage("SALE_AMOUNT_VAL")?>
			</td>
			<td valign="top">
				<?echo GetMessage("SALE_AMOUNT_CURRENCY")?>
			</td>
		</tr>
		<?
		$val = COption::GetOptionString("sale", "pay_amount", 'a:4:{i:1;a:2:{s:6:"AMOUNT";s:2:"10";s:8:"CURRENCY";s:3:"EUR";}i:2;a:2:{s:6:"AMOUNT";s:2:"20";s:8:"CURRENCY";s:3:"EUR";}i:3;a:2:{s:6:"AMOUNT";s:2:"30";s:8:"CURRENCY";s:3:"EUR";}i:4;a:2:{s:6:"AMOUNT";s:2:"40";s:8:"CURRENCY";s:3:"EUR";}}');
		if(strlen($val) > 0)
		{
			$arAmount = unserialize($val);
			foreach($arAmount as $key => $val)
			{
				?>
				<tr>
					<td><input type="text" name="amount_val[<?=$key?>]" value="<?=$val["AMOUNT"]?>"></td>
					<td><?=CCurrency::SelectBox("amount_currency[".$key."]", $val["CURRENCY"], "", True, "")?></td>
				</tr>
				<?
			}
		}
		if(IntVal($key) <= 0)
			$key = 0;
		?>
		<tr>
			<td><input type="text" name="amount_val[<?=++$key?>]" value=""></td>
			<td><?=CCurrency::SelectBox("amount_currency[".$key."]", $val["CURRENCY"], "", True, "")?></td>
		</tr>
		<tr>
			<td><input type="text" name="amount_val[<?=++$key?>]" value=""></td>
			<td><?=CCurrency::SelectBox("amount_currency[".$key."]", $val["CURRENCY"], "", True, "")?></td>
		</tr>
		<tr>
			<td><input type="text" name="amount_val[<?=++$key?>]" value=""></td>
			<td><?=CCurrency::SelectBox("amount_currency[".$key."]", $val["CURRENCY"], "", True, "")?></td>
		</tr>
		
		</table>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("SMO_ORDER_PAY_REMINDER")?></td>
	</tr>
	<tr>
		<td colspan="2">
			<?
			$reminder = COption::GetOptionString("sale", "pay_reminder", "");
			$arReminder = unserialize($reminder);
			
			$aTabs2 = Array();
			foreach($siteList as $val)
			{
				$aTabs2[] = Array("DIV"=>"reminder".$val["ID"], "TAB" => "[".$val["ID"]."] ".($val["NAME"]), "TITLE" => "[".$val["ID"]."] ".($val["NAME"]));
			}
			$tabControl2 = new CAdminViewTabControl("tabControl2", $aTabs2);
			$tabControl2->Begin();
			foreach($siteList as $val)
			{
				$tabControl2->BeginNextTab();
				?>
				<table cellspacing="5" cellpadding="0" border="0" width="0%" align="center">
				<tr>
					<td align="right"><label for="use-<?=$val["ID"]?>"><?=GetMessage("SMO_ORDER_PAY_REMINDER_USE")?>:</label></td>
					<td><input type="checkbox" name="reminder[<?=$val["ID"]?>][use]" value="Y" id="use-<?=$val["ID"]?>"<?if($arReminder[$val["ID"]]["use"] == "Y") echo " checked";?>></td>
				</tr>				
				<tr>
					<td align="right"><label for="after-<?=$val["ID"]?>"><?=GetMessage("SMO_ORDER_PAY_REMINDER_AFTER")?>:</label></td>
					<td><input type="text" name="reminder[<?=$val["ID"]?>][after]" value="<?=intval($arReminder[$val["ID"]]["after"])?>" size="5" id="after-<?=$val["ID"]?>"></td>
				</tr>				
				<tr>
					<td align="right"><label for="frequency-<?=$val["ID"]?>"><?=GetMessage("SMO_ORDER_PAY_REMINDER_FREQUENCY")?>:</label></td>
					<td><input type="text" name="reminder[<?=$val["ID"]?>][frequency]" value="<?=intval($arReminder[$val["ID"]]["frequency"])?>" size="5" id="frequency-<?=$val["ID"]?>"></td>
				</tr>				
				<tr>
					<td align="right"><label for="period-<?=$val["ID"]?>"><?=GetMessage("SMO_ORDER_PAY_REMINDER_PERIOD")?>:</label></td>
					<td><input type="text" name="reminder[<?=$val["ID"]?>][period]" value="<?=intval($arReminder[$val["ID"]]["period"])?>" size="5" id="period-<?=$val["ID"]?>"></td>
				</tr>
				</table>
				<?
			}
			$tabControl2->End();
			?>
		</td>
	</tr>
	
	<?$tabControl->BeginNextTab();?>
<script language="javascript">
var cur_site = {WEIGHT:'<?=($siteList[0]["ID"])?>',ADDRESS:'<?=($siteList[0]["ID"])?>'};
function changeSiteList(value, add_id)
{
	var SLHandler = document.getElementById(add_id + '_site_id');
	SLHandler.disabled = value;
}

function selectSite(current, add_id)
{
	if (current == cur_site[add_id]) return;
	
	var last_handler = document.getElementById('par_' + add_id + '_' +cur_site[add_id]);
	var current_handler = document.getElementById('par_' + add_id + '_' + current);
	var CSHandler = document.getElementById(add_id + '_current_site');
	
	last_handler.style.display = 'none';
	current_handler.style.display = 'inline';
	
	cur_site[add_id] = current;
	CSHandler.value = current;
	
	return;
}

function setWeightValue(obj)
{
	if (!obj.value) return;
	
	var selectorUnit = document.forms.opt_form['weight_unit[' + cur_site['WEIGHT'] + ']'];
	var selectorKoef = document.forms.opt_form['weight_koef[' + cur_site['WEIGHT'] + ']'];

	if (selectorKoef && selectorUnit)
	{
		selectorKoef.value = obj.value;
		selectorUnit.value = obj.options[obj.selectedIndex].text;
	}
}
</script>
	<tr>
		<td valign="top" width="50%"><?=GetMessage("SMO_PAR_DIF_SETTINGS")?></td>
		<td valign="top" width="50%"><input type="checkbox" name="WEIGHT_dif_settings" id="dif_settings" <? if(COption::GetOptionString($module_id, "WEIGHT_different_set", "N") == "Y") echo " checked=\"checked\"";?> OnClick="changeSiteList(!this.checked, 'WEIGHT')" /></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("SMO_PAR_SITE_LIST")?></td>
		<td valign="top"><select name="site" id="WEIGHT_site_id"<? if(COption::GetOptionString($module_id, "WEIGHT_different_set", "N") != "Y") echo " disabled=\"disabled\""; ?> OnChange="selectSite(this.value, 'WEIGHT')">
			<?
				for($i = 0; $i < $siteCount; $i++)
					echo "<option value=\"".($siteList[$i]["ID"])."\">".($siteList[$i]["NAME"])."</option>";
			?></select><input type="hidden" name="WEIGHT_current_site" id="WEIGHT_current_site" value="<?=($siteList[0]["ID"]);?>" /></td>
	</tr>
	<tr>
		<td valign="top" colspan="2">
	<?for ($i = 0; $i < $siteCount; $i++):?>
			<div id="par_WEIGHT_<?=($siteList[$i]["ID"])?>" style="display: <?=($i == 0 ? "inline" : "none");?>">
			<table cellpadding="0" cellspacing="2" border="0" width="60%" align="center">
			<tr class="heading">
				<td align="center" width="50%"><?echo GetMessage("SMO_PAR_SITE_SET_TITLE")?></td>
				<td align="center" width="50%"><?echo GetMessage("SMO_PAR_SITE_VALUE_TITLE")?></td>
			</tr>
			<tr>
				<td valign="top" align="right"><?echo GetMessage("SMO_PAR_SITE_WEIGHT_UNIT_SALE")?></td>
				<td valign="top"><select name="weight_unit_tmp[<?=$siteList[$i]["ID"]?>]" OnChange="setWeightValue(this)">
						<option selected="selected"></option><?
					$arUnitList = CSalemeasure::GetList("W");
					foreach ($arUnitList as $key => $arM)
					{
						?>
						<option value="<?=floatval($arM["KOEF"])?>"><?=htmlspecialchars($arM["NAME"])?></option>
						<?
					}
				
				?></select></td>
			</tr>
			<tr>
				<td valign="top" align="right"><?=GetMessage('SMO_PAR_WEIGHT_UNIT')?></td>
				<td valign="top"><input type="text" name="weight_unit[<?=$siteList[$i]["ID"]?>]" size="5" value="<?=htmlspecialchars(COption::GetOptionString($module_id, "weight_unit", GetMessage('SMO_PAR_WEIGHT_UNIT_GRAMM'), $siteList[$i]["ID"]))?>" /></td>
			</tr>
			<tr>
				<td valign="top" align="right"><?=GetMessage('SMO_PAR_WEIGHT_KOEF')?></td>
				<td valign="top"><input type="text" name="weight_koef[<?=$siteList[$i]["ID"]?>]" size="5" value="<?=htmlspecialchars(COption::GetOptionString($module_id, "weight_koef", "1", $siteList[$i]["ID"]))?>" /></td>
			</tr>
			</table>
			</div>
	<?endfor;?>
		</td>
	</tr>

<?$tabControl->BeginNextTab();?>
	<tr>
		<td valign="top" width="50%"><?=GetMessage("SMO_DIF_SETTINGS")?></td>
		<td valign="top" width="50%"><input type="checkbox" name="ADDRESS_dif_settings" id="ADDRESS_dif_settings"<? if(COption::GetOptionString($module_id, "ADDRESS_different_set", "N") != "N") echo " checked=\"checked\"";?> OnClick="changeSiteList(!this.checked, 'ADDRESS')" /></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("SMO_SITE_LIST")?></td>
		<td valign="top"><select name="site" id="ADDRESS_site_id"<? if(COption::GetOptionString($module_id, "ADDRESS_different_set", "N") != "Y") echo " disabled=\"disabled\""; ?> onChange="selectSite(this.value, 'ADDRESS')">
			<?
				for($i = 0; $i < $siteCount; $i++)
					echo "<option value=\"".($siteList[$i]["ID"])."\">".($siteList[$i]["NAME"])."</option>";
			?></select><input type="hidden" name="ADDRESS_current_site" id="ADDRESS_current_site" value="<?=($siteList[0]["ID"]);?>" /></td>
	</tr>
	<tr>
		<td colspan="2" valign="top">
<?
for ($i = 0; $i < $siteCount; $i++):
	$location_zip = intval(COption::GetOptionString('sale', 'location_zip', '', $siteList[$i]["ID"]));
	$location = intval(COption::GetOptionString('sale', 'location', '', $siteList[$i]["ID"]));
	
	if ($location_zip == 0) $location_zip = '';
?>
		<div  id="par_ADDRESS_<?=($siteList[$i]["ID"])?>" style="display: <?=($i == 0 ? "inline" : "none");?>">
		<table cellpadding="0" cellspacing="2" border="0" width="60%" align="center">
			<tr class="heading">
				<td align="center" width="50%"><?echo GetMessage("SMO_SITE_SET_TITLE")?></td>
				<td align="center" width="50%"><?echo GetMessage("SMO_SITE_VALUE_TITLE")?></td>
			</tr>
			<tr>
				<td valign="top" align="right"><?echo GetMessage("SMO_LOCATION_ZIP");?></td>
				<td valign="top"><input type="text" name="location_zip[<?=$siteList[$i]["ID"]?>]" value="<?=$location_zip?>" size="5" /></td>
			</tr>
			<tr>
				<td valign="top" align="right"><?echo GetMessage("SMO_LOCATION_CITY");?></td>
				<td valign="top">
					<select name="location[<?=$siteList[$i]["ID"]?>]">
						<option value=''></option>
						<?$dbLocationList = CSaleLocation::GetList(
							Array(
								"SORT"=>"ASC", 
								"COUNTRY_NAME_LANG"=>"ASC", 
								"CITY_NAME_LANG"=>"ASC"
							), 
							array(), 
							LANG)
						?>
						<?while ($arLocation = $dbLocationList->GetNext()):?>
							<option value="<?=$arLocation["ID"]?>"<?=($location == $arLocation["ID"] ? " selected=\"selected\"" : "")?>><?=$arLocation["COUNTRY_NAME"].(strlen($arLocation["CITY_NAME"]) > 0 ? " - ".$arLocation["CITY_NAME"] : "")?></option>
						<?endwhile;?>
					</select>
				</td>
			</tr>
		</table>
		</div>
<?
endfor;
?>
		</td>
	</tr>
	<?$tabControl->BeginNextTab();?>
<?
if (!CSaleUserCards::CheckPassword())
{
	?><tr>
		<td colspan="2"><?CAdminMessage::ShowMessage(str_replace("#ROOT#", $_SERVER["DOCUMENT_ROOT"], GetMessage("SMO_NO_VALID_PASSWORD")))?></td>
	</tr><?
}
?>
	<tr>
		<td valign="top" width="50%">

				<?= GetMessage("SMO_PATH2CRYPT_FILE") ?>

		</td>
		<td valign="middle" width="50%">

				<input type="text" size="40" value="<?= htmlspecialchars(COption::GetOptionString("sale", "sale_data_file", "")) ?>" name="sale_data_file">

		</td>
	</tr>
	<tr>
		<td valign="top">

				<?= GetMessage("SMO_CRYPT_ALGORITHM") ?>

		</td>
		<td valign="middle">

				<?
				$val = COption::GetOptionString("sale", "crypt_algorithm", "RC4");
				?>
				<select name="crypt_algorithm">
					<option value="RC4"<?if ($val=="RC4") echo " selected";?>>RC4</option>
					<option value="AES"<?if ($val=="AES") echo " selected";?>>AES (Rijndael) - <?= GetMessage("SMO_NEED_MCRYPT") ?></option>
					<option value="3DES"<?if ($val=="3DES") echo " selected";?>>3DES (Triple-DES) - <?= GetMessage("SMO_NEED_MCRYPT") ?></option>
				</select>

		</td>
	</tr>

<?$tabControl->BeginNextTab();?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("SMO_ADDITIONAL_SITE_PARAMS")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
		<table cellspacing="0" cellpadding="0" border="0" class="internal">
		<tr class="heading">
			<td valign="top">
				<?echo GetMessage("SALE_LANG")?>
			</td>
			<td valign="top">
				<?echo GetMessage("SALE_CURRENCY")?>
			</td>
			<td valign="top">
				<?= GetMessage("SMO_GROUPS2SITE") ?>
			</td>
		</tr>
		<?
		foreach($siteList as $val)
		{
			?>
			<tr>
				<td valign="top">
					[<a href="site_edit.php?LID=<?=$val["ID"]?>&lang=<?=LANGUAGE_ID?>" title="<?=GetMessage("SALE_SITE_ALT")?>"><?echo $val["ID"] ?></a>] <?echo ($val["NAME"]) ?>
				</td>
				<td valign="top">

					<?
					$arCurr = CSaleLang::GetByID($val["ID"]);
					echo CCurrency::SelectBox("CURRENCY_".$val["ID"], $arCurr["CURRENCY"], GetMessage("SALE_NOT_SET"), True, "");
					?>

				</td>
				<td valign="top">

					<?
					$arCurrentGroups = array();
					$dbSiteGroupsList = CSaleGroupAccessToSite::GetList(
							array(),
							array("SITE_ID" => $val["ID"])
						);
					while ($arSiteGroup = $dbSiteGroupsList->Fetch())
					{
						$arCurrentGroups[] = IntVal($arSiteGroup["GROUP_ID"]);
					}

					if (!isset($LOCAL_USER_GROUPS_CACHE) || !is_array($LOCAL_USER_GROUPS_CACHE))
					{
						$LOCAL_USER_GROUPS_CACHE = array();

						$dbGroups = CGroup::GetList(
								($b = "c_sort"),
								($o = "asc"),
								array("ANONYMOUS" => "N")
							);
						while ($arGroup = $dbGroups->Fetch())
						{
							$arGroup["ID"] = IntVal($arGroup["ID"]);

							if ($arGroup["ID"] == 1 || $arGroup["ID"] == 2)
								continue;

							$LOCAL_USER_GROUPS_CACHE[] = $arGroup;
						}
					}
					?>
					<select name="SITE_USER_GROUPS_<?= $val["ID"] ?>[]" multiple size="5">
						<?
						for ($i = 0; $i < count($LOCAL_USER_GROUPS_CACHE); $i++)
						{
							?><option value="<?= $LOCAL_USER_GROUPS_CACHE[$i]["ID"] ?>"<?if (in_array($LOCAL_USER_GROUPS_CACHE[$i]["ID"], $arCurrentGroups)) echo " selected";?>><?= htmlspecialcharsEx($LOCAL_USER_GROUPS_CACHE[$i]["NAME"]) ?></option><?
						}
						?>
					</select>

				</td>
			</tr>
			<?
		}
		?>
		</table>
		</td>
	</tr>
<?$tabControl->BeginNextTab();?>
<script>
<!--
var arUserFieldsList = new Array("ID", "LOGIN", "NAME", "SECOND_NAME", "LAST_NAME", "EMAIL", "LID", "PERSONAL_PROFESSION", "PERSONAL_WWW", "PERSONAL_ICQ", "PERSONAL_GENDER", "PERSONAL_FAX", "PERSONAL_MOBILE", "PERSONAL_STREET", "PERSONAL_MAILBOX", "PERSONAL_CITY", "PERSONAL_STATE", "PERSONAL_ZIP", "PERSONAL_COUNTRY", "WORK_COMPANY", "WORK_DEPARTMENT", "WORK_POSITION", "WORK_WWW", "WORK_PHONE", "WORK_FAX", "WORK_STREET", "WORK_MAILBOX", "WORK_CITY", "WORK_STATE", "WORK_ZIP", "WORK_COUNTRY");
var arUserFieldsNameList = new Array("<?= GetMessage("SPS_USER_ID") ?>", "<?= GetMessage("SPS_USER_LOGIN") ?>", "<?= GetMessage("SPS_USER_NAME") ?>", "<?= GetMessage("SPS_USER_SECOND_NAME") ?>", "<?= GetMessage("SPS_USER_LAST_NAME") ?>", "EMail", "<?= GetMessage("SPS_USER_SITE") ?>", "<?= GetMessage("SPS_USER_PROF") ?>", "<?= GetMessage("SPS_USER_WEB") ?>", "<?= GetMessage("SPS_USER_ICQ") ?>", "<?= GetMessage("SPS_USER_SEX") ?>", "<?= GetMessage("SPS_USER_FAX") ?>", "<?= GetMessage("SPS_USER_PHONE") ?>", "<?= GetMessage("SPS_USER_ADDRESS") ?>", "<?= GetMessage("SPS_USER_POST") ?>", "<?= GetMessage("SPS_USER_CITY") ?>", "<?= GetMessage("SPS_USER_STATE") ?>", "<?= GetMessage("SPS_USER_ZIP") ?>", "<?= GetMessage("SPS_USER_COUNTRY") ?>", "<?= GetMessage("SPS_USER_COMPANY") ?>", "<?= GetMessage("SPS_USER_DEPT") ?>", "<?= GetMessage("SPS_USER_DOL") ?>", "<?= GetMessage("SPS_USER_COM_WEB") ?>", "<?= GetMessage("SPS_USER_COM_PHONE") ?>", "<?= GetMessage("SPS_USER_COM_FAX") ?>", "<?= GetMessage("SPS_USER_COM_ADDRESS") ?>", "<?= GetMessage("SPS_USER_COM_POST") ?>", "<?= GetMessage("SPS_USER_COM_CITY") ?>", "<?= GetMessage("SPS_USER_COM_STATE") ?>", "<?= GetMessage("SPS_USER_COM_ZIP") ?>", "<?= GetMessage("SPS_USER_COM_COUNTRY") ?>");

var arOrderFieldsList = new Array("ID", "DATE_INSERT", "DATE_INSERT_DATE", "SHOULD_PAY", "CURRENCY", "PRICE", "LID", "PRICE_DELIVERY", "DISCOUNT_VALUE", "USER_ID", "PAYSYSTEM_ID", "DELIVERY_ID", "TAX_VALUE");
var arOrderFieldsNameList = new Array("<?= GetMessage("SPS_ORDER_ID") ?>", "<?= GetMessage("SPS_ORDER_DATETIME") ?>", "<?= GetMessage("SPS_ORDER_DATE") ?>", "<?= GetMessage("SPS_ORDER_PRICE") ?>", "<?= GetMessage("SPS_ORDER_CURRENCY") ?>", "<?= GetMessage("SPS_ORDER_SUM") ?>", "<?= GetMessage("SPS_ORDER_SITE") ?>", "<?= GetMessage("SPS_ORDER_PRICE_DELIV") ?>", "<?= GetMessage("SPS_ORDER_DESCOUNT") ?>", "<?= GetMessage("SPS_ORDER_USER_ID") ?>", "<?= GetMessage("SPS_ORDER_PS") ?>", "<?= GetMessage("SPS_ORDER_DELIV") ?>", "<?= GetMessage("SPS_ORDER_TAX") ?>");

var arPropFieldsList = new Array();
var arPropFieldsNameList = new Array();


function PropertyTypeChange(pkey, ind)
{
	var oType = document.forms["opt_form"].elements["TYPE_" + pkey + "_" + ind];
	var oValue1 = document.forms["opt_form"].elements["VALUE1_" + pkey + "_" + ind];
	var oValue2 = document.forms["opt_form"].elements["VALUE2_" + pkey + "_" + ind];

	eval("var cur_type = ''; if (typeof(param_" + pkey + "_type_" + ind + ") == 'string') cur_type = param_" + pkey + "_type_" + ind + ";");
	eval("var cur_val = ''; if (typeof(param_" + pkey + "_value_" + ind + ") == 'string') cur_val = param_" + pkey + "_value_" + ind + ";");

	var value1_length = oValue1.length;
	while (value1_length > 0)
	{
		value1_length--;
		oValue1.options[value1_length] = null;
	}
	value1_length = 0;

	var typeVal = oType[oType.selectedIndex].value;
	if (typeVal == "USER")
	{
		oValue2.style["display"] = "none";
		oValue1.style["display"] = "block";

		for (i = 0; i < arUserFieldsList.length; i++)
		{
			var newoption = new Option(arUserFieldsNameList[i], arUserFieldsList[i], false, false);
			oValue1.options[value1_length] = newoption;

			if (typeVal == cur_type && cur_val == arUserFieldsList[i])
				oValue1.selectedIndex = value1_length;

			value1_length++;
		}
	}
	else
	{
		if (typeVal == "ORDER")
		{
			oValue2.style["display"] = "none";
			oValue1.style["display"] = "block";

			for (i = 0; i < arOrderFieldsList.length; i++)
			{
				var newoption = new Option(arOrderFieldsNameList[i], arOrderFieldsList[i], false, false);
				oValue1.options[value1_length] = newoption;

				if (typeVal == cur_type && cur_val == arOrderFieldsList[i])
					oValue1.selectedIndex = value1_length;

				value1_length++;
			}
		}
		else
		{
			if (typeVal == "PROPERTY")
			{
				oValue2.style["display"] = "none";
				oValue1.style["display"] = "block";

				for (i = 0; i < arPropFieldsList[ind].length; i++)
				{
					var newoption = new Option(arPropFieldsNameList[ind][i], arPropFieldsList[ind][i], false, false);
					oValue1.options[value1_length] = newoption;

					if (typeVal == cur_type && cur_val == arPropFieldsList[ind][i])
						oValue1.selectedIndex = value1_length;

					value1_length++;
				}
			}
			else
			{
				oValue1.style["display"] = "none";
				oValue2.style["display"] = "block";

				oValue2.value = cur_val;
			}
		}
	}
}

function InitActionProps(pkey, ind)
{
	var oType = document.forms["opt_form"].elements["TYPE_" + pkey + "_" + ind];
	eval("var cur_type = ''; if (typeof(param_" + pkey + "_type_" + ind + ") == 'string') cur_type = param_" + pkey + "_type_" + ind + ";");

	for (i = 0; i < oType.options.length; i++)
	{
		if (oType.options[i].value == cur_type)
		{
			oType.selectedIndex = i;
			break;
		}
	}
	PropertyTypeChange(pkey, ind);
}
function ActionFileChange(ind, type)
{
	ind = parseInt(ind);
	eval("var cur_type_1c = ''; if (typeof(param_person_type_1c_" + ind + ") == 'string') cur_type_1c = param_person_type_1c_" + ind + ";");
	if(cur_type_1c != "" && type == "")
	{
		type = cur_type_1c;
		document.getElementById("person_type_1c_"+ind).value = type;
	}
	window.frames["hidden_action_frame_" + ind].location.replace('/bitrix/admin/sale_options_get.php?lang=<?= htmlspecialchars($lang) ?>&type='+type+'&divInd='+ind);
}
//-->
</script>

	<tr>
		<td colspan="2">
			<script language="JavaScript">
			<!--
			var paySysActVisible_<?= $arPersonType["ID"] ?> = true;
			<?
			$dbExport = CSaleExport::GetList();
			while($arExport = $dbExport->Fetch())
			{
				$arExpParams = unserialize($arExport["VARS"]);
				foreach($arExpParams as $k => $v)
				{
					?>
					var param_<?= $k ?>_type_<?= $arExport["PERSON_TYPE_ID"] ?> = '<?= $v["TYPE"] ?>';
					var param_<?= $k ?>_value_<?= $arExport["PERSON_TYPE_ID"] ?> = '<?= str_replace("'", "\'", $v["VALUE"]) ?>';
					<?
				}
				?>
				var param_person_type_1c_<?=$arExport["PERSON_TYPE_ID"]?> = '<?=(($arExpParams["IS_FIZ"]=="Y")?"FIZ":"UR")?>';
				<?
			}
			?>
			//-->
			</script>
			<?
			$aTabs1 = array();
			$personType = Array();
			$dbPersonType = CSalePersonType::GetList(Array("ID"=>"ASC"), Array("ACTIVE"=>"Y"));
			while($arPersonType = $dbPersonType -> GetNext())
			{
				$aTabs1[] = Array("DIV"=>"oedit".$arPersonType["ID"], "TAB" => $arPersonType["NAME"], "TITLE" => $arPersonType["NAME"]);
				$personType[$arPersonType["ID"]] = $arPersonType;
				?>
				 	<script>
					<!--
					arPropFieldsList[<?= $arPersonType["ID"] ?>] = new Array();
					arPropFieldsNameList[<?= $arPersonType["ID"] ?>] = new Array();
					<?
					$dbOrderProps = CSaleOrderProps::GetList(
							array("SORT" => "ASC", "NAME" => "ASC"),
							array("PERSON_TYPE_ID" => $arPersonType["ID"]),
							false,
							false,
							array("ID", "CODE", "NAME", "TYPE", "SORT")
						);
					$i = -1;
					while ($arOrderProps = $dbOrderProps->Fetch())
					{
						$i++;
						?>
						arPropFieldsList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= str_replace("'", "\'", $arOrderProps["ID"]) ?>';
						arPropFieldsNameList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= str_replace("'", "\'", $arOrderProps["NAME"]) ?>';
						<?
						if ($arOrderProps["TYPE"] == "LOCATION")
						{
							$i++;
							?>
							arPropFieldsList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= str_replace("'", "\'", $arOrderProps["ID"]."_COUNTRY") ?>';
							arPropFieldsNameList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= str_replace("'", "\'", $arOrderProps["NAME"]." (".GetMessage("SPS_JCOUNTRY").")") ?>';
							<?
			
							$i++;
							?>
							arPropFieldsList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= str_replace("'", "\'", $arOrderProps["ID"]."_CITY") ?>';
							arPropFieldsNameList[<?= $arPersonType["ID"] ?>][<?= $i ?>] = '<?= str_replace("'", "\'", $arOrderProps["NAME"]." (".GetMessage("SPS_JCITY").")") ?>';
							<?
						}
					}
					?>
					//-->
					</script>
				<?
			}
			$tabControl1 = new CAdminViewTabControl("tabControl1", $aTabs1);
			$tabControl1->Begin();
			foreach($personType as $val)
			{
				$tabControl1->BeginNextTab();
				?>
				<table cellspacing="5" cellpadding="0" border="0" width="0%">
				<tr>
					<td><?=GetMessage("SO_CHOOSE_1C_PERSON_TYPE")?></td>
					<td><select name="person_type_1c_<?=$val["ID"]?>" id="person_type_1c_<?=$val["ID"]?>" onchange="ActionFileChange(<?=$val["ID"]?>, this.value)">
							<option value="FIZ"><?=GetMessage("SO_PERSON_TYPE_FIZ")?></option>
							<option value="UR"><?=GetMessage("SO_PERSON_TYPE_UR")?></option>
						</select>
					</td>
				</tr>
				</table>
				<br />
				<div id="export_<?= $val["ID"] ?>" style="display: block;"></div>
				<iframe style="width:0px; height:0px; border: 0px" name="hidden_action_frame_<?= $val["ID"] ?>" src="" width="0" height="0"></iframe>
				<input type="hidden" name="export_fields_<?=$val["ID"]?>" id="export_fields_<?=$val["ID"]?>" value="">
				<script language="JavaScript">
				<!--
				ActionFileChange(<?=$val["ID"]?>, '');
				//-->
				</script>
				<?
			}
			$tabControl1->End();
			?>
			<?echo BeginNote();?>
			<font class="legendtext">
			<?=GetMessage("SO_1C_COMMENT")?>
			</font>
			<?echo EndNote(); ?>

		</td>
	</tr>

<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
<script language="JavaScript">
function RestoreDefaults()
{
	if (confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid)?>";
}
</script>

<input type="submit" <?if ($SALE_RIGHT<"W") echo "disabled" ?> name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
<input type="hidden" name="Update" value="Y">
<?if(strlen($_REQUEST["back_url_settings"])>0):?>
	<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" onclick="window.location='<?echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
	<input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>">
<?endif;?>
<input type="button" <?if ($SALE_RIGHT<"W") echo "disabled" ?> title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
<?$tabControl->End();?>
</form>
<?endif;?>
