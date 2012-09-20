<?
IncludeModuleLangFile(__FILE__);

$GLOBALS["SALE_EXPORT"] = Array();

class CAllSaleExport
{
	/*************** ADD, UPDATE, DELETE *****************/
	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "PERSON_TYPE_ID") || $ACTION=="ADD") && IntVal($arFields["PERSON_TYPE_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SALE_EXPORT_NO_PERSON_TYPE_ID"), "EMPTY_PERSON_TYPE_ID");
			return false;
		}

		if (is_set($arFields, "PERSON_TYPE_ID"))
		{
			$arResult = CSalePersonType::GetByID($arFields["PERSON_TYPE_ID"]);
			if (!$arResult)
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["PERSON_TYPE_ID"], GetMessage("SALE_EXPORT_ERROR_PERSON_TYPE_ID")), "ERROR_NO_PERSON_TYPE_ID");
				return false;
			}
		}

		return True;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = IntVal($ID);

		unset($GLOBALS["SALE_EXPORT"]["SALE_EXPORT_CACHE_".$ID]);

		return $DB->Query("DELETE FROM b_sale_export WHERE ID = ".$ID."", true);
	}

	//*************** SELECT *********************/
	function GetByID($ID)
	{
		global $DB;

		$ID = IntVal($ID);

		if (isset($GLOBALS["SALE_EXPORT"]["SALE_EXPORT_CACHE_".$ID]) && is_array($GLOBALS["SALE_EXPORT"]["SALE_EXPORT_CACHE_".$ID]) && is_set($GLOBALS["SALE_EXPORT_CACHE_".$ID], "ID"))
		{
			return $GLOBALS["SALE_EXPORT"]["SALE_EXPORT_CACHE_".$ID];
		}
		else
		{
			$strSql =
				"SELECT E.ID, E.PERSON_TYPE_ID, E.VARS ".
				"FROM b_sale_export E ".
				"WHERE E.ID = ".$ID."";
			$dbResult = $DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arResult = $dbResult->Fetch())
			{
				$GLOBALS["SALE_EXPORT"]["SALE_EXPORT_CACHE_".$ID] = $arResult;
				return $arResult;
			}
		}

		return False;
	}
	
	function ExportOrders2Xml($arFilter = Array(), $nTopCount = 0, $currency = "")
	{
		global $DB;
     	$count = false;
     	if(IntVal($nTopCount)>0)
     	   	$count = Array("nTopCount" => $nTopCount);
     	   	
		$dbOrderList = CSaleOrder::GetList(
				array("ID" => "DESC"),
				$arFilter,
				false,
				$count,
				array("ID", "LID", "PERSON_TYPE_ID", "PAYED", "DATE_PAYED", "EMP_PAYED_ID", "CANCELED", "DATE_CANCELED", "EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "EMP_STATUS_ID", "PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID", "PRICE", "CURRENCY", "DISCOUNT_VALUE", "SUM_PAID", "USER_ID", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "DATE_INSERT_FORMAT", "DATE_UPDATE", "USER_DESCRIPTION", "ADDITIONAL_INFO", "PS_STATUS", "PS_STATUS_CODE", "PS_STATUS_DESCRIPTION", "PS_STATUS_MESSAGE", "PS_SUM", "PS_CURRENCY", "PS_RESPONSE_DATE", "COMMENTS", "TAX_VALUE", "STAT_GID", "RECURRING_ID")
			);

		$dbPaySystem = CSalePaySystem::GetList(Array("ID" => "ASC"), Array("ACTIVE" => "Y"), false, false, Array("ID", "NAME", "ACTIVE"));
		while($arPaySystem = $dbPaySystem -> Fetch())
			$paySystems[$arPaySystem["ID"]] = $arPaySystem["NAME"];
		
		$dbDelivery = CSaleDelivery::GetList(Array("ID" => "ASC"), Array("ACTIVE" => "Y"), false, false, Array("ID", "NAME", "ACTIVE"));
		while($arDelivery = $dbDelivery -> Fetch())
			$delivery[$arDelivery["ID"]] = $arDelivery["NAME"];
			
		$dbDelivery = CSaleDelivery::GetList(Array("ID" => "ASC"), Array("ACTIVE" => "Y"), false, false, Array("ID", "NAME", "ACTIVE"));
		while($arDelivery = $dbDelivery -> Fetch())
			$delivery[$arDelivery["ID"]] = $arDelivery["NAME"];

		$rsDeliveryHandlers = CSaleDeliveryHandler::GetAdminList(array("SID" => "ASC"));
		while ($arHandler = $rsDeliveryHandlers->Fetch())
		{
			if(is_array($arHandler["PROFILES"]))
			{
				foreach($arHandler["PROFILES"] as $k => $v)
				{
					$delivery[$arHandler["SID"].":".$k] = $v["TITLE"]." (".$arHandler["NAME"].")";
				}
			}
		}

		$dbExport = CSaleExport::GetList();
		while($arExport = $dbExport->Fetch())
		{
			$arAgent[$arExport["PERSON_TYPE_ID"]] = unserialize($arExport["VARS"]);
		}
		
		$dateFormat = CSite::GetDateFormat("FULL"); 
	
		echo "<"."?xml version=\"1.0\" encoding=\"windows-1251\"?".">\n";
		?>
		<<?=GetMessage("SALE_EXPORT_COM_INFORMATION")?> <?=GetMessage("SALE_EXPORT_SHEM_VERSION")?>="2.05" <?=GetMessage("SALE_EXPORT_SHEM_DATE_CREATE")?>="<?=date("Y-m-d")?>T<?=date("G:i:s")?>" <?=GetMessage("SALE_EXPORT_DATE_FORMAT")?>="<?=GetMessage("SALE_EXPORT_DATE_FORMAT_DF")?>=yyyy-MM-dd; <?=GetMessage("SALE_EXPORT_DATE_FORMAT_DLF")?>=DT" <?=GetMessage("SALE_EXPORT_DATE_FORMAT_DATETIME")?>="<?=GetMessage("SALE_EXPORT_DATE_FORMAT_DF")?>=<?=GetMessage("SALE_EXPORT_DATE_FORMAT_TIME")?>; <?=GetMessage("SALE_EXPORT_DATE_FORMAT_DLF")?>=T" <?=GetMessage("SALE_EXPORT_DEL_DT")?>="T" <?=GetMessage("SALE_EXPORT_FORM_SUMM")?>="<?=GetMessage("SALE_EXPORT_FORM_CC")?>=18; <?=GetMessage("SALE_EXPORT_FORM_CDC")?>=2; <?=GetMessage("SALE_EXPORT_FORM_CRD")?>=." <?=GetMessage("SALE_EXPORT_FORM_QUANT")?>="<?=GetMessage("SALE_EXPORT_FORM_CC")?>=18; <?=GetMessage("SALE_EXPORT_FORM_CDC")?>=2; <?=GetMessage("SALE_EXPORT_FORM_CRD")?>=.">
		<?
		while($arOrder = $dbOrderList->Fetch())
		{
			$agentParams = $arAgent[$arOrder["PERSON_TYPE_ID"]];
			$arProp = Array();
		
			if (IntVal($arOrder["USER_ID"]) > 0)
			{
				$dbUser = CUser::GetByID($arOrder["USER_ID"]);
				if ($arUser = $dbUser->Fetch())
					$arProp["USER"] = $arUser;
			}
		
			$dbOrderPropVals = CSaleOrderPropsValue::GetList(
					array(),
					array("ORDER_ID" => $arOrder["ID"]),
					false,
					false,
					array("ID", "CODE", "VALUE", "ORDER_PROPS_ID", "PROP_TYPE")
				);
			while ($arOrderPropVals = $dbOrderPropVals->Fetch())
			{
				//$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = $arOrderPropVals["VALUE"];
				if ($arOrderPropVals["PROP_TYPE"] == "CHECKBOX")
				{
					if ($arOrderPropVals["VALUE"] == "Y")
						$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = "true";
					else
						$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = "false";
				}
				elseif ($arOrderPropVals["PROP_TYPE"] == "TEXT" || $arOrderPropVals["PROP_TYPE"] == "TEXTAREA")
				{
					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = $arOrderPropVals["VALUE"];
				}
				elseif ($arOrderPropVals["PROP_TYPE"] == "SELECT" || $arOrderPropVals["PROP_TYPE"] == "RADIO")
				{
					$arVal = CSaleOrderPropsVariant::GetByValue($arOrderPropVals["ORDER_PROPS_ID"], $arOrderPropVals["VALUE"]);
					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = $arVal["NAME"];
				}
				elseif ($arOrderPropVals["PROP_TYPE"] == "MULTISELECT")
				{
					$curVal = explode(",", $arOrderPropVals["VALUE"]);
					for ($i = 0; $i < count($curVal); $i++)
					{
						$arVal = CSaleOrderPropsVariant::GetByValue($arOrderPropVals["ORDER_PROPS_ID"], $curVal[$i]);
						if ($i > 0)
							$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] .=  ", ";
						$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] .=  $arVal["NAME"];
					}
				}
				elseif ($arOrderPropVals["PROP_TYPE"] == "LOCATION")
				{
					$arVal = CSaleLocation::GetByID($arOrderPropVals["VALUE"], LANG);
					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] =  ($arVal["COUNTRY_NAME"].((strlen($arVal["COUNTRY_NAME"])<=0 || strlen($arVal["CITY_NAME"])<=0) ? "" : " - ").$arVal["CITY_NAME"]);
					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]."_CITY"] = $arVal["CITY_NAME"];
					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]."_COUNTRY"] = $arVal["COUNTRY_NAME"];
				}          
				else
				{
					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = $arOrderPropVals["VALUE"];
				}
			}
			
			foreach($agentParams as $k => $v)
			{
				if(!is_array($v))
				{
					$agent[$k] = $v;
				}
				else
				{
				 	if(strlen($v["TYPE"])<=0)
				 	{
				 		$agent[$k] = $v["VALUE"];
				 	}
				 	else
				 	{
						$agent[$k] = $arProp[$v["TYPE"]][$v["VALUE"]];
					}
				
				}
			}
			?>
			<<?=GetMessage("SALE_EXPORT_DOCUMENT")?>>
				<<?=GetMessage("SALE_EXPORT_ID")?>><?=$arOrder["ID"]?></<?=GetMessage("SALE_EXPORT_ID")?>>
				<<?=GetMessage("SALE_EXPORT_NUMBER")?>><?=$arOrder["ID"]?></<?=GetMessage("SALE_EXPORT_NUMBER")?>>
				<<?=GetMessage("SALE_EXPORT_DATE")?>><?=$DB->FormatDate($arOrder["DATE_INSERT_FORMAT"], $dateFormat, "YYYY-MM-DD");?></<?=GetMessage("SALE_EXPORT_DATE")?>>
				<<?=GetMessage("SALE_EXPORT_HOZ_OPERATION")?>><?=GetMessage("SALE_EXPORT_ITEM_ORDER")?></<?=GetMessage("SALE_EXPORT_HOZ_OPERATION")?>>
				<<?=GetMessage("SALE_EXPORT_ROLE")?>><?=GetMessage("SALE_EXPORT_SELLER")?></<?=GetMessage("SALE_EXPORT_ROLE")?>>
				<<?=GetMessage("SALE_EXPORT_CURRENCY")?>><?=htmlspecialchars(((strlen($currency)>0)?substr($currency, 0, 3):substr($arOrder["CURRENCY"], 0, 3)))?></<?=GetMessage("SALE_EXPORT_CURRENCY")?>>
				<<?=GetMessage("SALE_EXPORT_CURRENCY_RATE")?>>1</<?=GetMessage("SALE_EXPORT_CURRENCY_RATE")?>>
				<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arOrder["PRICE"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
				<<?=GetMessage("SALE_EXPORT_CONTRAGENTS")?>>
					<<?=GetMessage("SALE_EXPORT_CONTRAGENT")?>>
						<<?=GetMessage("SALE_EXPORT_ID")?>><?=htmlspecialchars($arOrder["USER_ID"]."#".$arProp["USER"]["LOGIN"]."#".$arProp["USER"]["LAST_NAME"]." ".$arProp["USER"]["NAME"]." ".$arProp["USER"]["SECOND_NAME"])?></<?=GetMessage("SALE_EXPORT_ID")?>>
						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialchars($agent["AGENT_NAME"])?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
						<?
						$address = "<".GetMessage("SALE_EXPORT_PRESENTATION").">".htmlspecialchars($agent["ADDRESS_FULL"])."</".GetMessage("SALE_EXPORT_PRESENTATION").">";
						if(strlen($agent["INDEX"])>0)
						{
							$address .= "<".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">
										<".GetMessage("SALE_EXPORT_TYPE").">".GetMessage("SALE_EXPORT_POST_CODE")."</".GetMessage("SALE_EXPORT_TYPE").">
										<".GetMessage("SALE_EXPORT_VALUE").">".htmlspecialchars($agent["INDEX"])."</".GetMessage("SALE_EXPORT_VALUE").">
									</".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">";
						}
						if(strlen($agent["COUNTRY"])>0)
						{
							$address .= "<".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">
										<".GetMessage("SALE_EXPORT_TYPE").">".GetMessage("SALE_EXPORT_COUNTRY")."</".GetMessage("SALE_EXPORT_TYPE").">
										<".GetMessage("SALE_EXPORT_VALUE").">".htmlspecialchars($agent["COUNTRY"])."</".GetMessage("SALE_EXPORT_VALUE").">
									</".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">";
						}
						if(strlen($agent["REGION"])>0)
						{
							$address .= "<".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">
										<".GetMessage("SALE_EXPORT_TYPE").">".GetMessage("SALE_EXPORT_REGION")."</".GetMessage("SALE_EXPORT_TYPE").">
										<".GetMessage("SALE_EXPORT_VALUE").">".htmlspecialchars($agent["REGION"])."</".GetMessage("SALE_EXPORT_VALUE").">
									</".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">";
						}
						if(strlen($agent["STATE"])>0)
						{
							$address .= "<".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">
										<".GetMessage("SALE_EXPORT_TYPE").">".GetMessage("SALE_EXPORT_STATE")."</".GetMessage("SALE_EXPORT_TYPE").">
										<".GetMessage("SALE_EXPORT_VALUE").">".htmlspecialchars($agent["STATE"])."</".GetMessage("SALE_EXPORT_VALUE").">
									</".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">";
						}
						if(strlen($agent["TOWN"])>0)
						{
							$address .= "<".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">
										<".GetMessage("SALE_EXPORT_TYPE").">".GetMessage("SALE_EXPORT_SMALL_CITY")."</".GetMessage("SALE_EXPORT_TYPE").">
										<".GetMessage("SALE_EXPORT_VALUE").">".htmlspecialchars($agent["TOWN"])."</".GetMessage("SALE_EXPORT_VALUE").">
									</".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">";
						}
						if(strlen($agent["CITY"])>0)
						{
							$address .= "<".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">
										<".GetMessage("SALE_EXPORT_TYPE").">".GetMessage("SALE_EXPORT_CITY")."</".GetMessage("SALE_EXPORT_TYPE").">
										<".GetMessage("SALE_EXPORT_VALUE").">".htmlspecialchars($agent["CITY"])."</".GetMessage("SALE_EXPORT_VALUE").">
									</".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">";
						}
						if(strlen($agent["STREET"])>0)
						{
							$address .= "<".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">
										<".GetMessage("SALE_EXPORT_TYPE").">".GetMessage("SALE_EXPORT_STREET")."</".GetMessage("SALE_EXPORT_TYPE").">
										<".GetMessage("SALE_EXPORT_VALUE").">".htmlspecialchars($agent["STREET"])."</".GetMessage("SALE_EXPORT_VALUE").">
									</".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">";
						}
						if(strlen($agent["HOUSE"])>0)
						{
							$address .= "<".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">
										<".GetMessage("SALE_EXPORT_TYPE").">".GetMessage("SALE_EXPORT_HOUSE")."</".GetMessage("SALE_EXPORT_TYPE").">
										<".GetMessage("SALE_EXPORT_VALUE").">".htmlspecialchars($agent["HOUSE"])."</".GetMessage("SALE_EXPORT_VALUE").">
									</".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">";
						}
						if(strlen($agent["BUILDING"])>0)
						{
							$address .= "<".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">
										<".GetMessage("SALE_EXPORT_TYPE").">".GetMessage("SALE_EXPORT_BUILDING")."</".GetMessage("SALE_EXPORT_TYPE").">
										<".GetMessage("SALE_EXPORT_VALUE").">".htmlspecialchars($agent["BUILDING"])."</".GetMessage("SALE_EXPORT_VALUE").">
									</".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">";
						}
						if(strlen($agent["FLAT"])>0)
						{
							$address .= "<".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">
										<".GetMessage("SALE_EXPORT_TYPE").">".GetMessage("SALE_EXPORT_FLAT")."</".GetMessage("SALE_EXPORT_TYPE").">
										<".GetMessage("SALE_EXPORT_VALUE").">".htmlspecialchars($agent["FLAT"])."</".GetMessage("SALE_EXPORT_VALUE").">
									</".GetMessage("SALE_EXPORT_ADDRESS_FIELD").">";
						}
						
						if($agent["IS_FIZ"]=="Y")
						{
							?>
								<<?=GetMessage("SALE_EXPORT_FULL_NAME")?>><?=htmlspecialchars($agent["FULL_NAME"])?></<?=GetMessage("SALE_EXPORT_FULL_NAME")?>>
								<?
								if(strlen($agent["SURNAME"])>0)
								{
									?><<?=GetMessage("SALE_EXPORT_SURNAME")?>><?=htmlspecialchars($agent["SURNAME"])?></<?=GetMessage("SALE_EXPORT_SURNAME")?>><?
								}
								if(strlen($agent["NAME"])>0)
								{
									?><<?=GetMessage("SALE_EXPORT_NAME")?>><?=htmlspecialchars($agent["NAME"])?></<?=GetMessage("SALE_EXPORT_NAME")?>><?
								}
								if(strlen($agent["SECOND_NAME"])>0)
								{
									?><<?=GetMessage("SALE_EXPORT_MIDDLE_NAME")?>><?=htmlspecialchars($agent["SECOND_NAME"])?></<?=GetMessage("SALE_EXPORT_MIDDLE_NAME")?>><?
								}
								if(strlen($agent["BIRTHDAY"])>0)
								{
									?><<?=GetMessage("SALE_EXPORT_BIRTHDAY")?>><?=htmlspecialchars($agent["BIRTHDAY"])?></<?=GetMessage("SALE_EXPORT_BIRTHDAY")?>><?
								}
								if(strlen($agent["MALE"])>0)
								{
									?><<?=GetMessage("SALE_EXPORT_SEX")?>><?=htmlspecialchars($agent["MALE"])?></<?=GetMessage("SALE_EXPORT_SEX")?>><?
								}
								if(strlen($agent["INN"])>0)
								{
									?><<?=GetMessage("SALE_EXPORT_INN")?>><?=htmlspecialchars($agent["INN"])?></<?=GetMessage("SALE_EXPORT_INN")?>><?
								}
								if(strlen($agent["KPP"])>0)
								{
									?><<?=GetMessage("SALE_EXPORT_KPP")?>><?=htmlspecialchars($agent["KPP"])?></<?=GetMessage("SALE_EXPORT_KPP")?>><?
								}
								?>
								<<?=GetMessage("SALE_EXPORT_REGISTRATION_ADDRESS")?>>
									<?=$address?>
								</<?=GetMessage("SALE_EXPORT_REGISTRATION_ADDRESS")?>>
							<?
						}
						else
						{
							?>
							<<?=GetMessage("SALE_EXPORT_OFICIAL_NAME")?>><?=htmlspecialchars($agent["FULL_NAME"])?></<?=GetMessage("SALE_EXPORT_OFICIAL_NAME")?>>
							<<?=GetMessage("SALE_EXPORT_UR_ADDRESS")?>>
								<?=$address?>
							</<?=GetMessage("SALE_EXPORT_UR_ADDRESS")?>>
							<?
							if(strlen($agent["INN"])>0)
							{
								?><<?=GetMessage("SALE_EXPORT_INN")?>><?=htmlspecialchars($agent["INN"])?></<?=GetMessage("SALE_EXPORT_INN")?>><?
							}
							if(strlen($agent["KPP"])>0)
							{
								?><<?=GetMessage("SALE_EXPORT_KPP")?>><?=htmlspecialchars($agent["KPP"])?></<?=GetMessage("SALE_EXPORT_KPP")?>><?
							}
							if(strlen($agent["EGRPO"])>0)
							{
								?><<?=GetMessage("SALE_EXPORT_EGRPO")?>><?=htmlspecialchars($agent["EGRPO"])?></<?=GetMessage("SALE_EXPORT_EGRPO")?>><?
							}
							if(strlen($agent["OKVED"])>0)
							{
								?><<?=GetMessage("SALE_EXPORT_OKVED")?>><?=htmlspecialchars($agent["OKVED"])?></<?=GetMessage("SALE_EXPORT_OKVED")?>><?
							}
							if(strlen($agent["OKDP"])>0)
							{
								?><<?=GetMessage("SALE_EXPORT_OKDP")?>><?=htmlspecialchars($agent["OKDP"])?></<?=GetMessage("SALE_EXPORT_OKDP")?>><?
							}
							if(strlen($agent["OKOPF"])>0)
							{
								?><<?=GetMessage("SALE_EXPORT_OKOPF")?>><?=htmlspecialchars($agent["OKOPF"])?></<?=GetMessage("SALE_EXPORT_OKOPF")?>><?
							}
							if(strlen($agent["OKFC"])>0)
							{
								?><<?=GetMessage("SALE_EXPORT_OKFC")?>><?=htmlspecialchars($agent["OKFC"])?></<?=GetMessage("SALE_EXPORT_OKFC")?>><?
							}
							if(strlen($agent["OKPO"])>0)
							{
								?><<?=GetMessage("SALE_EXPORT_OKPO")?>><?=htmlspecialchars($agent["OKPO"])?></<?=GetMessage("SALE_EXPORT_OKPO")?>><?
							}
							if(strlen($agent["ACCOUNT_NUMBER"])>0)
							{
								?>
								<<?=GetMessage("SALE_EXPORT_MONEY_ACCOUNTS")?>>
									<<?=GetMessage("SALE_EXPORT_MONEY_ACCOUNT")?>>
										<<?=GetMessage("SALE_EXPORT_ACCOUNT_NUMBER")?>><?=htmlspecialchars($agent["ACCOUNT_NUMBER"])?></<?=GetMessage("SALE_EXPORT_ACCOUNT_NUMBER")?>>
										<<?=GetMessage("SALE_EXPORT_BANK")?>>
											<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialchars($agent["B_NAME"])?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
											<<?=GetMessage("SALE_EXPORT_ADDRESS")?>>
												<<?=GetMessage("SALE_EXPORT_PRESENTATION")?>><?=htmlspecialchars($agent["B_ADDRESS_FULL"])?></<?=GetMessage("SALE_EXPORT_PRESENTATION")?>>
												<?
												if(strlen($agent["B_INDEX"])>0)
												{
													?><<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
													<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_POST_CODE")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
													<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["B_INDEX"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
												</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>><?
												}
												if(strlen($agent["B_COUNTRY"])>0)
												{
													?><<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
													<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_COUNTRY")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
													<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["B_COUNTRY"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
												</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>><?
												}
												if(strlen($agent["B_REGION"])>0)
												{
													?><<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
													<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_REGION")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
													<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["B_REGION"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
												</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>><?
												}
												if(strlen($agent["B_STATE"])>0)
												{
													?><<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
													<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_STATE")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
													<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["B_STATE"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
												</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>><?
												}
												if(strlen($agent["B_TOWN"])>0)
												{
													?><<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
													<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_SMALL_CITY")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
													<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["B_TOWN"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
												</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>><?
												}
												if(strlen($agent["B_CITY"])>0)
												{
													?><<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
													<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_CITY")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
													<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["B_CITY"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
												</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>><?
												}
												if(strlen($agent["B_STREET"])>0)
												{
													?><<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
													<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_STREET")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
													<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["B_STREET"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
												</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>><?
												}
												if(strlen($agent["B_HOUSE"])>0)
												{
													?><<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
													<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_HOUSE")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
													<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["B_HOUSE"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
												</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>><?
												}
												if(strlen($agent["B_BUILDING"])>0)
												{
													?><<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
													<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_BUILDING")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
													<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["B_BUILDING"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
												</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>><?
												}
												if(strlen($agent["B_FLAT"])>0)
												{
													?><<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
													<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_BUILDING")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
													<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["B_FLAT"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
												</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>><?
												}
												?>
											</<?=GetMessage("SALE_EXPORT_ADDRESS")?>>
											<?
											if(strlen($agent["B_BIK"])>0)
											{
												?><<?=GetMessage("SALE_EXPORT_BIC")?>><?=htmlspecialchars($agent["B_BIK"])?></<?=GetMessage("SALE_EXPORT_BIC")?>><?
											}
											?>
										</<?=GetMessage("SALE_EXPORT_BANK")?>>
									</<?=GetMessage("SALE_EXPORT_MONEY_ACCOUNT")?>>
								</<?=GetMessage("SALE_EXPORT_MONEY_ACCOUNTS")?>>
								<?
							}
						}
						if(strlen($agent["F_ADDRESS_FULL"])>0)
						{
							?>
							<<?=GetMessage("SALE_EXPORT_ADDRESS")?>>
								<<?=GetMessage("SALE_EXPORT_PRESENTATION")?>><?=htmlspecialchars($agent["F_ADDRESS_FULL"])?></<?=GetMessage("SALE_EXPORT_PRESENTATION")?>>
								<?
								if(strlen($agent["F_INDEX"])>0)
								{	
									?>
									<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_POST_CODE")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["F_INDEX"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
									<?
								}
								if(strlen($agent["F_COUNTRY"])>0)
								{
									?>
									<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_COUNTRY")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["F_COUNTRY"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
									<?
								}
								if(strlen($agent["F_REGION"])>0)
								{
									?>									
									<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_REGION")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["F_REGION"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
									<?
								}
								if(strlen($agent["F_STATE"])>0)
								{
									?>
									<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_STATE")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["F_STATE"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
									<?
								}
								if(strlen($agent["F_TOWN"])>0)
								{
									?>
									<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_SMALL_CITY")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["F_TOWN"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
									<?
								}
								if(strlen($agent["F_CITY"])>0)
								{
									?>
									<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_CITY")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["F_CITY"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
									<?
								}
								if(strlen($agent["F_STREET"])>0)
								{
									?>
									<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_STREET")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["F_STREET"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
									<?
								}
								if(strlen($agent["F_HOUSE"])>0)
								{
									?>
									<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_HOUSE")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["F_HOUSE"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
									<?
								}
								if(strlen($agent["F_BUILDING"])>0)
								{
									?>
									<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_BUILDING")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["F_BUILDING"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
									<?
								}
								if(strlen($agent["F_BUILDING"])>0)
								{
									?>
									<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_BUILDING")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["F_FLAT"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
									<?
								}
								?>
							</<?=GetMessage("SALE_EXPORT_ADDRESS")?>>
							<?
						}
						if(strlen($agent["PHONE"])>0 || strlen($agent["EMAIL"])>0)
						{	
							?>
							<<?=GetMessage("SALE_EXPORT_CONTACTS")?>>
								<?
								if(strlen($agent["PHONE"])>0)
								{
									?>
									<<?=GetMessage("SALE_EXPORT_CONTACT")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_WORK_PHONE")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["PHONE"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_CONTACT")?>>
									<?
								}
								if(strlen($agent["EMAIL"])>0)
								{
									?>
									<<?=GetMessage("SALE_EXPORT_CONTACT")?>>
										<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_MAIL")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
										<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($agent["EMAIL"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
									</<?=GetMessage("SALE_EXPORT_CONTACT")?>>
									<?
								}
								?>
							</<?=GetMessage("SALE_EXPORT_CONTACTS")?>>
							<?
						}
						if(strlen($agent["CONTACT_PERSON"])>0)
						{
							?>
							<<?=GetMessage("SALE_EXPORT_REPRESENTATIVES")?>>
								<<?=GetMessage("SALE_EXPORT_REPRESENTATIVE")?>>
									<<?=GetMessage("SALE_EXPORT_CONTRAGENT")?>>
										<<?=GetMessage("SALE_EXPORT_RELATION")?>><?=GetMessage("SALE_EXPORT_CONTACT_PERSON")?></<?=GetMessage("SALE_EXPORT_RELATION")?>>
										<<?=GetMessage("SALE_EXPORT_ID")?>><?=md5($agent["CONTACT_PERSON"])?></<?=GetMessage("SALE_EXPORT_ID")?>>
										<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialchars($agent["CONTACT_PERSON"])?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
									</<?=GetMessage("SALE_EXPORT_CONTRAGENT")?>>
								</<?=GetMessage("SALE_EXPORT_REPRESENTATIVE")?>>
							</<?=GetMessage("SALE_EXPORT_REPRESENTATIVES")?>>
							<?
						}?>					
						<<?=GetMessage("SALE_EXPORT_ROLE")?>><?=GetMessage("SALE_EXPORT_BUYER")?></<?=GetMessage("SALE_EXPORT_ROLE")?>>						
					</<?=GetMessage("SALE_EXPORT_CONTRAGENT")?>>
				</<?=GetMessage("SALE_EXPORT_CONTRAGENTS")?>>
				
				<<?=GetMessage("SALE_EXPORT_TIME")?>><?=$DB->FormatDate($arOrder["DATE_INSERT_FORMAT"], $dateFormat, "HH:MI:SS");?></<?=GetMessage("SALE_EXPORT_TIME")?>>
				<<?=GetMessage("SALE_EXPORT_COMMENTS")?>><?=htmlspecialchars($arOrder["COMMENTS"])?></<?=GetMessage("SALE_EXPORT_COMMENTS")?>>
				<?
				$dbOrderTax = CSaleOrderTax::GetList(
					array(),
					array("ORDER_ID" => $arOrder["ID"]),
					false,
					false,
					array("ID", "TAX_NAME", "VALUE", "VALUE_MONEY", "CODE", "IS_IN_PRICE")
				);
				$i=-1;
				while ($arOrderTax = $dbOrderTax->Fetch())
				{
					$i++;
					if($i == 0)
						echo "<".GetMessage("SALE_EXPORT_TAXES").">";
					?>
					<<?=GetMessage("SALE_EXPORT_TAX")?>>
						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialchars($arOrderTax["TAX_NAME"])?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
						<<?=GetMessage("SALE_EXPORT_IN_PRICE")?>><?=(($arOrderTax["IS_IN_PRICE"]=="Y") ? "true" : "false")?></<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>
						<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arOrderTax["VALUE_MONEY"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
					</<?=GetMessage("SALE_EXPORT_TAX")?>>
					<?
				}
				if($i != -1)
					echo "</".GetMessage("SALE_EXPORT_TAXES").">";
				?>
				<?if(IntVal($arOrder["DISCOUNT_VALUE"])>0)
				{
					?>
					<<?=GetMessage("SALE_EXPORT_DISCOUNTS")?>>
						<<?=GetMessage("SALE_EXPORT_DISCOUNT")?>>
							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_DISCOUNT")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
							<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arOrder["DISCOUNT_VALUE"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
							<<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>true</<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>
						</<?=GetMessage("SALE_EXPORT_DISCOUNT")?>>
					</<?=GetMessage("SALE_EXPORT_DISCOUNTS")?>>
					<?
				}
				?>
				<<?=GetMessage("SALE_EXPORT_ITEMS")?>>                              
				<?
				if(IntVal($arOrder["PRICE_DELIVERY"]) > 0)
				{
					?>
					<<?=GetMessage("SALE_EXPORT_ITEM")?>>
						<<?=GetMessage("SALE_EXPORT_ID")?>>ORDER_DELIVERY</<?=GetMessage("SALE_EXPORT_ID")?>>
						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_DELIVERY")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
						<<?=GetMessage("SALE_EXPORT_BASE_UNIT")?> <?=GetMessage("SALE_EXPORT_CODE")?>="796" <?=GetMessage("SALE_EXPORT_FULL_NAME_UNIT");?>="<?=GetMessage("SALE_EXPORT_SHTUKA")?>" <?=GetMessage("SALE_EXPORT_INTERNATIONAL_ABR")?>="<?=GetMessage("SALE_EXPORT_RCE")?>"><?=GetMessage("SALE_EXPORT_SHT")?></<?=GetMessage("SALE_EXPORT_BASE_UNIT")?>>
						<<?=GetMessage("SALE_EXPORT_PRICE_PER_ITEM")?>><?=$arOrder["PRICE_DELIVERY"]?></<?=GetMessage("SALE_EXPORT_PRICE_PER_ITEM")?>>
						<<?=GetMessage("SALE_EXPORT_QUANTITY")?>>1</<?=GetMessage("SALE_EXPORT_QUANTITY")?>>
						<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arOrder["PRICE_DELIVERY"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
						<<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
							<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
								<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_TYPE_NOMENKLATURA")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
								<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=GetMessage("SALE_EXPORT_SERVICE")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
							</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
								<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_TYPE_OF_NOMENKLATURA")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
								<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=GetMessage("SALE_EXPORT_SERVICE")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
							</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						</<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
					</<?=GetMessage("SALE_EXPORT_ITEM")?>>
					<?
				}
				
				$dbBasket = CSaleBasket::GetList(
						array("NAME" => "ASC"),
						array("ORDER_ID" => $arOrder["ID"])
					);
				$basketSum = 0;
				while ($arBasket = $dbBasket->Fetch())
				{
					?>
					<<?=GetMessage("SALE_EXPORT_ITEM")?>>
						<<?=GetMessage("SALE_EXPORT_ID")?>><?=htmlspecialchars($arBasket["PRODUCT_XML_ID"])?></<?=GetMessage("SALE_EXPORT_ID")?>>
						<<?=GetMessage("SALE_EXPORT_CATALOG_ID")?>><?=htmlspecialchars($arBasket["CATALOG_XML_ID"])?></<?=GetMessage("SALE_EXPORT_CATALOG_ID")?>>
						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialchars($arBasket["NAME"])?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
						<<?=GetMessage("SALE_EXPORT_BASE_UNIT")?> <?=GetMessage("SALE_EXPORT_CODE")?>="796" <?=GetMessage("SALE_EXPORT_FULL_NAME_UNIT")?>="<?=GetMessage("SALE_EXPORT_SHTUKA")?>" <?=GetMessage("SALE_EXPORT_INTERNATIONAL_ABR")?>="<?=GetMessage("SALE_EXPORT_RCE")?>"><?=GetMessage("SALE_EXPORT_SHT")?></<?=GetMessage("SALE_EXPORT_BASE_UNIT")?>>
						<?if(DoubleVal($arBasket["VAT_RATE"]) > 0)
						{
							?>
							<<?=GetMessage("SALE_EXPORT_TAX_RATES")?>>
								<<?=GetMessage("SALE_EXPORT_TAX_RATE")?>>
									<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_VAT")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
									<<?=GetMessage("SALE_EXPORT_RATE")?>><?=$arBasket["VAT_RATE"] * 100?></<?=GetMessage("SALE_EXPORT_RATE")?>>
								</<?=GetMessage("SALE_EXPORT_TAX_RATE")?>>
							</<?=GetMessage("SALE_EXPORT_TAX_RATES")?>>
							<?
						}
						
						if(IntVal($arBasket["DISCOUNT_PRICE"])>0)
						{
							?>
							<<?=GetMessage("SALE_EXPORT_DISCOUNTS")?>>
								<<?=GetMessage("SALE_EXPORT_DISCOUNT")?>>
									<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ITEM_DISCOUNT")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
									<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arBasket["DISCOUNT_PRICE"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
									<<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>true</<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>
								</<?=GetMessage("SALE_EXPORT_DISCOUNT")?>>
							</<?=GetMessage("SALE_EXPORT_DISCOUNTS")?>>
							<?
						}
						?>
						<<?=GetMessage("SALE_EXPORT_PRICE_PER_ITEM")?>><?=$arBasket["PRICE"]?></<?=GetMessage("SALE_EXPORT_PRICE_PER_ITEM")?>>
						<<?=GetMessage("SALE_EXPORT_QUANTITY")?>><?=$arBasket["QUANTITY"]?></<?=GetMessage("SALE_EXPORT_QUANTITY")?>>
						<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arBasket["PRICE"]*$arBasket["QUANTITY"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
						<<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
							<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
								<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_TYPE_NOMENKLATURA")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
								<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=GetMessage("SALE_EXPORT_ITEM")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
							</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
								<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_TYPE_OF_NOMENKLATURA")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
								<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=GetMessage("SALE_EXPORT_ITEM")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
							</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						</<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
						<?if(DoubleVal($arBasket["VAT_RATE"]) > 0)
						{
							?>
							<<?=GetMessage("SALE_EXPORT_TAXES")?>>
								<<?=GetMessage("SALE_EXPORT_TAX")?>>
									<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_VAT")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
									<<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>true</<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>
									<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=round($arBasket["VAT_RATE"] * $arBasket["PRICE"] * $arBasket["QUANTITY"], 2)?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
								</<?=GetMessage("SALE_EXPORT_TAX")?>>
							</<?=GetMessage("SALE_EXPORT_TAXES")?>>
							<?
						}
						?>
					</<?=GetMessage("SALE_EXPORT_ITEM")?>>
					<?
					$basketSum += $arBasket["PRICE"]*$arBasket["QUANTITY"];
				}
				?>
				</<?=GetMessage("SALE_EXPORT_ITEMS")?>>
				<<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
					<?if(strlen($arOrder["DATE_PAYED"])>0)
					{
						?>
						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DATE_PAID")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=$DB->FormatDate($arOrder["DATE_PAYED"], $dateFormat, "YYYY-MM-DD HH:MI:SS")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<?
					}
					if(strlen($arOrder["PAY_VOUCHER_NUM"])>0)
					{
						?>
						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_PAY_NUMBER")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=$arOrder["PAY_VOUCHER_NUM"]?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<?
					}
					if(IntVal($arOrder["PAY_SYSTEM_ID"])>0)
					{
						?>
						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_PAY_SYSTEM")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($paySystems[$arOrder["PAY_SYSTEM_ID"]])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<?
					}	
					if(strlen($arOrder["DATE_ALLOW_DELIVERY"])>0)
					{
						?>
						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DATE_ALLOW_DELIVERY")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=$DB->FormatDate($arOrder["DATE_ALLOW_DELIVERY"], $dateFormat, "YYYY-MM-DD HH:MI:SS")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<?
					}
					if(strlen($arOrder["DELIVERY_ID"])>0)
					{
						?>
						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DELIVERY_SERVICE")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($delivery[$arOrder["DELIVERY_ID"]])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<?
					}
					?>
					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_PAID")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=($arOrder["PAYED"]=="Y")?"true":"false";?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ALLOW_DELIVERY")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=($arOrder["ALLOW_DELIVERY"]=="Y")?"true":"false";?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_CANCELED")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=($arOrder["CANCELED"]=="Y")?"true":"false";?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_FINAL_STATUS")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=($arOrder["STATUS_ID"]=="F")?"true":"false";?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_STATUS")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?$arStatus = CSaleStatus::GetLangByID($arOrder["STATUS_ID"]); echo htmlspecialchars("[".$arOrder["STATUS_ID"]."] ".$arStatus["NAME"]);?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
					
					<?if(strlen($arOrder["DATE_CANCELED"])>0)
					{
						?>
						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DATE_CANCEL")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=$DB->FormatDate($arOrder["DATE_CANCELED"], $dateFormat, "YYYY-MM-DD HH:MI:SS")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_CANCEL_REASON")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialchars($arOrder["REASON_CANCELED"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<?
					}
					if(strlen($arOrder["DATE_STATUS"])>0)
					{
						?>
						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DATE_STATUS")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=$DB->FormatDate($arOrder["DATE_STATUS"], $dateFormat, "YYYY-MM-DD HH:MI:SS")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<?
					}
					$dbSite = CSite::GetByID($arOrder["LID"]);
					$arSite = $dbSite->Fetch();
					?>
					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_SITE_NAME")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
						<<?=GetMessage("SALE_EXPORT_VALUE")?>>[<?=$arOrder["LID"]?>] <?=htmlspecialcharsEx($arSite["NAME"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
				</<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
			</<?=GetMessage("SALE_EXPORT_DOCUMENT")?>>
			<?
		}
		?>
		</<?=GetMessage("SALE_EXPORT_COM_INFORMATION")?>>
		<?
	}
}
?>