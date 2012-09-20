<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);
if(strlen($arParams["SITE_LIST"])<=0)
	$arParams["SITE_LIST"] = "";

	$arParams["USE_ZIP"] = $arParams["USE_ZIP"]!="N";
$arParams["EXPORT_PAYED_ORDERS"] = (($arParams["EXPORT_PAYED_ORDERS"]=="Y")?true:false);
$arParams["EXPORT_ALLOW_DELIVERY_ORDERS"] = (($arParams["EXPORT_ALLOW_DELIVERY_ORDERS"]=="Y")?true:false);
$arParams["REPLACE_CURRENCY"] = htmlspecialcharsEx($arParams["REPLACE_CURRENCY"]);

@set_time_limit(0);

$bUSER_HAVE_ACCESS = false;
if(isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
{
	$arUserGroupArray = $GLOBALS["USER"]->GetUserGroupArray();
	foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
	{
		if(in_array($PERM, $arUserGroupArray))
		{
			$bUSER_HAVE_ACCESS = true;
			break;
		}
	}
}

$bDesignMode = $GLOBALS["APPLICATION"]->GetShowIncludeAreas() && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin();
if(!$bDesignMode)
{
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
}

ob_start();

$curPage = substr($APPLICATION -> GetCurPage(), 0, 22);
if($_GET["mode"] == "checkauth" && $USER->IsAuthorized())
{
	echo "success\n";
	echo session_name()."\n";
	echo session_id() ."\n";

	COption::SetOptionString("sale", "export_session_name_".$curPage, session_name());
	COption::SetOptionString("sale", "export_session_id_".$curPage, session_id());
}
elseif(!$USER->IsAuthorized())
{
	echo "failure\n",GetMessage("CC_BSC1_ERROR_AUTHORIZE");
}
elseif(!$bUSER_HAVE_ACCESS)
{
	echo "failure\n",GetMessage("CC_BSC1_PERMISSION_DENIED");
}
elseif(!(CModule::IncludeModule('sale') && CModule::IncludeModule('catalog')))
{
	echo "failure\n",GetMessage("CC_BSC1_ERROR_MODULE");
}
else
{
	if($_GET["mode"] == "query")
	{
		$arFilter = Array();
		if($arParams["EXPORT_PAYED_ORDERS"])
			$arFilter["PAYED"] = "Y";
		if($arParams["EXPORT_ALLOW_DELIVERY_ORDERS"])
			$arFilter["ALLOW_DELIVERY"] = "Y";
		if(strlen($arParams["EXPORT_FINAL_ORDERS"])>0)		
		{
			$bNextExport = false;
			$arStatusToExport = Array();
			$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID));
			while ($arStatus = $dbStatus->Fetch())
			{
				if($arStatus["ID"] == $arParams["EXPORT_FINAL_ORDERS"])
					$bNextExport = true;
				if($bNextExport)
					$arStatusToExport[] = $arStatus["ID"];
			}

			$arFilter["STATUS_ID"] = $arStatusToExport;		
		}
		if(strlen($arParams["SITE_LIST"])>0)
			$arFilter["LID"] = $arParams["SITE_LIST"];
		if(strlen(COption::GetOptionString("sale", "last_export_time_committed_".$curPage, ""))>0)
			$arFilter[">=DATE_UPDATE"] = ConvertTimeStamp(COption::GetOptionString("sale", "last_export_time_committed_".$curPage, ""), "FULL");
		COption::SetOptionString("sale", "last_export_time_".$curPage, time());

		CSaleExport::ExportOrders2Xml($arFilter, false, $arParams["REPLACE_CURRENCY"]);
	}
	elseif($_GET["mode"]=="success")
	{
     	if($_COOKIE[COption::GetOptionString("sale", "export_session_name_".$curPage, "")] == COption::GetOptionString("sale", "export_session_id_".$curPage, ""))
     	{
			COption::SetOptionString("sale", "last_export_time_committed_".$curPage, COption::GetOptionString("sale", "last_export_time_".$curPage, ""));
     		echo "success\n";
		}
		else
			echo "error\n";
	}
		elseif($_GET["mode"]=="init")
	{
		$DIR_NAME = "/".COption::GetOptionString("main", "upload_dir")."/1c_exchange/";
		DeleteDirFilesEx($DIR_NAME);
	 	CheckDirPath($_SERVER["DOCUMENT_ROOT"].$DIR_NAME."/");
		if(!is_dir($_SERVER["DOCUMENT_ROOT"].$DIR_NAME))
		{
			echo "failure\n",GetMessage("CC_BSC1_ERROR_INIT");
		}
		else
		{
			$ht_name = $_SERVER["DOCUMENT_ROOT"].$DIR_NAME."/.htaccess";
			if(!file_exists($ht_name))
			{
				$fp = fopen($ht_name, "w");
				if($fp)
				{
					fwrite($fp, "Deny from All");
					fclose($fp);
					@chmod($ht_name, BX_FILE_PERMISSIONS);
				}
			}

			$_SESSION["BX_CML2_EXPORT"]["zip"] = $arParams["USE_ZIP"] && function_exists("zip_open");
			echo "zip=".($_SESSION["BX_CML2_EXPORT"]["zip"]? "yes": "no")."\n";
			echo "file_limit=0\n";
		}
	}
	elseif($_GET["mode"] == "file")
	{
		$DIR_NAME = "/".COption::GetOptionString("main", "upload_dir")."/1c_exchange/";
		$ABS_FILE_NAME = false;
		$WORK_DIR_NAME = false;

		if(isset($_GET["filename"]) && (strlen($_GET["filename"])>0))
		{
			$filename = trim(str_replace("\\", "/", trim($_GET["filename"])), "/");
			$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"].$DIR_NAME, "/".$filename);
			if((strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename))
			{
				$ABS_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$DIR_NAME.$FILE_NAME;
				$WORK_DIR_NAME = substr($ABS_FILE_NAME, 0, strrpos($ABS_FILE_NAME, "/")+1);
			}
		}

		if($ABS_FILE_NAME)
		{
			if(function_exists("file_get_contents"))
				$DATA = file_get_contents("php://input");
			elseif(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
				$DATA = &$GLOBALS["HTTP_RAW_POST_DATA"];
				else
					$DATA = false;
			if($DATA !== false)
			{
				CheckDirPath($ABS_FILE_NAME);
				if($fp = fopen($ABS_FILE_NAME, "ab"))
				{
					$result = fwrite($fp, $DATA);
					if($result === (function_exists("mb_strlen")? mb_strlen($DATA, 'latin1'): strlen($DATA)))
					{
						if($_SESSION["BX_CML2_EXPORT"]["zip"])
							$_SESSION["BX_CML2_EXPORT"]["zip"] = $ABS_FILE_NAME;
					}
					else
					{
						echo "failure\n",GetMessage("CC_BSC1_ERROR_FILE_WRITE", array("#FILE_NAME#"=>$FILE_NAME));
					}
					fclose($fp);
				}
				else
				{
					echo "failure\n",GetMessage("CC_BSC1_ERROR_FILE_OPEN", array("#FILE_NAME#"=>$FILE_NAME));
				}
			}
			else
			{
				echo "failure\n",GetMessage("CC_BSC1_ERROR_HTTP_READ");
			}

			if(strlen($_SESSION["BX_CML2_EXPORT"]["zip"])>0)
			{
				$file_name = $_SESSION["BX_CML2_EXPORT"]["zip"];

				if(function_exists("zip_open"))
				{
					$dir_name = substr($file_name, 0, strrpos($file_name, "/")+1);
					if(strlen($dir_name) <= strlen($_SERVER["DOCUMENT_ROOT"]))
						return false;

					$hZip = zip_open($file_name);
					if($hZip)
					{
						while($entry = zip_read($hZip))
						{
							$entry_name = zip_entry_name($entry);
							//Check for directory
							if(zip_entry_filesize($entry))
							{
								$ABS_FILE_NAME = $dir_name.$entry_name;
								$file_name = $dir_name.$entry_name;
								CheckDirPath($file_name);
								$fout = fopen($file_name, "wb");
								if($fout)
								{
									while($data = zip_entry_read($entry, 102400))
									{
										$result = fwrite($fout, $data);
										if($result !== (function_exists("mb_strlen")? mb_strlen($data, 'latin1'): strlen($data)))
											return false;
									}
								}
							}
							zip_entry_close($entry);
						}
						zip_close($hZip);
					}
				}
				else
					echo "error\n".GetMessage("CC_BSC1_UNZIP_ERROR");
			}
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");
			$sResult = "";
	        $new_file_name = $ABS_FILE_NAME;

			$f = fopen($new_file_name, "rb");
			$sResult = fread($f, filesize($new_file_name));
			fclose($f);
			if(strlen($sResult)>0)
			{
				if(toUpper(LANG_CHARSET) != "UTF-8")
					$sResult = $APPLICATION->ConvertCharset($sResult, "UTF-8", LANG_CHARSET);

				$objXML = new CDataXML();
				$objXML->LoadString($sResult);
				$arResult = $objXML->GetArray();

				$SumFormat = ".";
				$QuantityFormat = ".";
				foreach ($arResult[GetMessage("CC_BSC1_COM_INFO")]["@"] as $key => $val)
				{
					if($key == GetMessage("SALE_EXPORT_FORM_SUMM"))
					{
						$arFormat = explode(";", $val);
						foreach($arFormat as $val)
						{
							if(strpos($val, GetMessage("SALE_EXPORT_FORM_CRD")) !== false)
								$SumFormat = trim(substr($val, strpos($val, "=")+1));
						}	
					}
					elseif($key == GetMessage("SALE_EXPORT_FORM_QUANT"))
					{
						$arFormat = explode(";", $val);
						foreach($arFormat as $val)
						{
							if(strpos($val, GetMessage("SALE_EXPORT_FORM_CRD")) !== false)
								$QuantityFormat = trim(substr($val, strpos($val, "=")+1));
						}	
					}
				}

				foreach($arResult[GetMessage("CC_BSC1_COM_INFO")]["#"][GetMessage("CC_BSC1_DOCUMENT")] as $value)
				{
					if($value["#"][GetMessage("CC_BSC1_OPERATION")][0]["#"] == GetMessage("CC_BSC1_ORDER"))
					{
						$orderId = IntVal($value["#"][GetMessage("CC_BSC1_NUMBER")][0]["#"]);
						$arOrder[$orderId] = Array();
						$arItem = Array();
						$arOrder[$orderId]["AMOUNT"] = $value["#"][GetMessage("CC_BSC1_SUMM")][0]["#"];
						$arOrder[$orderId]["AMOUNT"] = str_replace($SumFormat, ".", $arOrder[$orderId]["AMOUNT"]);
						
						$arOrder[$orderId]["COMMENT"] = $value["#"][GetMessage("CC_BSC1_COMMENT")][0]["#"];
						
						foreach($value["#"][GetMessage("CC_BSC1_REK_VALUES")][0]["#"][GetMessage("CC_BSC1_REK_VALUE")] as $val)
							$arOrder[$orderId]["TRAITS"][$val["#"][GetMessage("CC_BSC1_NAME")][0]["#"]] = $val["#"][GetMessage("CC_BSC1_VALUE")][0]["#"];
						
						$taxValue = 0;
						$taxValueTmp = 0;
						$taxName = "";
						if(is_array($value["#"][GetMessage("CC_BSC1_ITEMS")][0]["#"][GetMessage("CC_BSC1_ITEM")]))
						{
							foreach($value["#"][GetMessage("CC_BSC1_ITEMS")][0]["#"][GetMessage("CC_BSC1_ITEM")] as $val)
							{
								$val = $val["#"];
								$productID = $val[GetMessage("CC_BSC1_ID")][0]["#"];
								$bGood = false;
								$price = str_replace($SumFormat, ".", $val[GetMessage("CC_BSC1_PRICE_PER_UNIT")][0]["#"]);
								$quantity = str_replace($QuantityFormat, ".", $val[GetMessage("CC_BSC1_QUANTITY")][0]["#"]);
								$arItem[$productID] = Array(
										"NAME" => $val[GetMessage("CC_BSC1_NAME")][0]["#"],
										"PRICE" => $price,
										"QUANTITY" => $quantity,
										
									);
								
								if(is_array($val[GetMessage("CC_BSC1_PROPS_ITEMS")][0]["#"][GetMessage("CC_BSC1_PROP_ITEM")]))
								{
									foreach($val[GetMessage("CC_BSC1_PROPS_ITEMS")][0]["#"][GetMessage("CC_BSC1_PROP_ITEM")] as $val1)
										$arItem[$productID]["ATTRIBUTES"][$val1["#"][GetMessage("CC_BSC1_NAME")][0]["#"]] = $val1["#"][GetMessage("CC_BSC1_VALUE")][0]["#"];
								}
									
								if(is_array($val[GetMessage("CC_BSC1_REK_VALUES")][0]["#"][GetMessage("CC_BSC1_REK_VALUE")]))
								{
									foreach($val[GetMessage("CC_BSC1_REK_VALUES")][0]["#"][GetMessage("CC_BSC1_REK_VALUE")] as $val1)
									{
										if($val1["#"][GetMessage("CC_BSC1_NAME")][0]["#"] == GetMessage("CC_BSC1_ITEM_TYPE"))
											$arItem[$productID]["TYPE"] = $val1["#"][GetMessage("CC_BSC1_VALUE")][0]["#"];
									}
								}
								
								if(strlen($value["#"][GetMessage("CC_BSC1_TAXES")][0]["#"][GetMessage("CC_BSC1_TAX")][0]["#"][GetMessage("CC_BSC1_NAME")][0]["#"])>0)
								{
									$taxValueTmp = $val[GetMessage("CC_BSC1_TAXES")][0]["#"][GetMessage("CC_BSC1_TAX")][0]["#"][GetMessage("CC_BSC1_TAX_VALUE")][0]["#"];
									$arItem[$productID]["VAT_RATE"] = $taxValueTmp/100;

									if(IntVal($taxValueTmp) > IntVal($taxValue))
									{
										$taxName = $val[GetMessage("CC_BSC1_TAXES")][0]["#"][GetMessage("CC_BSC1_TAX")][0]["#"][GetMessage("CC_BSC1_NAME")][0]["#"];
										$taxValue = $taxValueTmp;
									}
								}
							}
						}
						if(IntVal($taxValue)>0)
						{
							$price = str_replace($SumFormat, ".", $value["#"][GetMessage("CC_BSC1_TAXES")][0]["#"][GetMessage("CC_BSC1_TAX")][0]["#"][GetMessage("CC_BSC1_SUMM")][0]["#"]);
							$arOrder[$orderId]["TAX"] = Array(
									"NAME" => $taxName, 
									"VALUE" =>$taxValue, 
									"IS_IN_PRICE" => ($value["#"][GetMessage("CC_BSC1_TAXES")][0]["#"][GetMessage("CC_BSC1_TAX")][0]["#"][GetMessage("CC_BSC1_IN_PRICE")][0]["#"]=="true"?"Y":"N"),
									"VALUE_MONEY" => $price,
								);
							
						}

						$arOrder[$orderId]["items"] = $arItem;
					}
				}

				if(count($arOrder)<=0)
				{
					echo "failure\n",GetMessage("CC_BSC1_NO_ORDERS_IN_IMPORT");
				}
				else
				{
					$strError = "";
					foreach($arOrder as $k => $v)
					{
						if($orderInfo = CSaleOrder::GetByID($k))
						{
							if($orderInfo["PAYED"] != "Y" && $orderInfo["ALLOW_DELIVERY"] != "Y" && $orderInfo["STATUS_ID"] != "F")
							{
								$dbOrderTax = CSaleOrderTax::GetList(
									array(),
									array("ORDER_ID" => $k),
									false,
									false,
									array("ID", "TAX_NAME", "VALUE", "VALUE_MONEY", "CODE", "IS_IN_PRICE")
								);
								$bTaxFound = false;
								if($arOrderTax = $dbOrderTax->Fetch())
								{
									$bTaxFound = true;
									if(IntVal($arOrderTax["VALUE"]) != IntVal($v["TAX"]["VALUE"]) || ($arOrderTax["IS_IN_PRICE"] != $v["TAX"]["IS_IN_PRICE"]))
									{
										if(IntVal($v["TAX"]["VALUE"])>0)
										{	
											$arFields = Array(
													"TAX_NAME" => $v["TAX"]["NAME"],
													"ORDER_ID" => $k,
													"VALUE" => $v["TAX"]["VALUE"],
													"IS_PERCENT" => "Y",
													"IS_IN_PRICE" => $v["TAX"]["IS_IN_PRICE"],
													"VALUE_MONEY" => $v["TAX"]["VALUE_MONEY"],
													"CODE" => "VAT1C",
													"APPLY_ORDER" => "100"
												);
											CSaleOrderTax::Update($arOrderTax["ID"], $arFields);
											CSaleOrder::Update($k, Array("TAX_VALUE" => $v["TAX"]["VALUE_MONEY"]));
										}
										else
										{
											CSaleOrderTax::Delete($arOrderTax["ID"]);
											CSaleOrder::Update($k, Array("TAX_VALUE" => 0));
										}
									}
								}
								
								if(!$bTaxFound)
								{
									if(IntVal($v["TAX"]["VALUE"])>0)
									{
										$arFields = Array(
												"TAX_NAME" => $v["TAX"]["NAME"],
												"ORDER_ID" => $k,
												"VALUE" => $v["TAX"]["VALUE"],
												"IS_PERCENT" => "Y",
												"IS_IN_PRICE" => $v["TAX"]["IS_IN_PRICE"],
												"VALUE_MONEY" => $v["TAX"]["VALUE_MONEY"]
											);
										CSaleOrderTax::Update($k, $arFields);
										CSaleOrder::Update($k, Array("TAX_VALUE" => $v["TAX"]["VALUE_MONEY"]));
									}
								}
								
								$dbBasket = CSaleBasket::GetList(
										array("NAME" => "ASC"),
										array("ORDER_ID" => $k)
									);
								$basketSum = 0;

								while ($arBasket = $dbBasket->Fetch())
								{
									$arFields = Array();
									if(!empty($v["items"][$arBasket["PRODUCT_XML_ID"]]))
									{
										if($arBasket["QUANTITY"] != $v["items"][$arBasket["PRODUCT_XML_ID"]]["QUANTITY"])
											$arFields["QUANTITY"] = $v["items"][$arBasket["PRODUCT_XML_ID"]]["QUANTITY"];
										if($arBasket["PRICE"] != $v["items"][$arBasket["PRODUCT_XML_ID"]]["PRICE"])
											$arFields["PRICE"] = $v["items"][$arBasket["PRODUCT_XML_ID"]]["PRICE"];
										if($arBasket["VAT_RATE"] != $v["items"][$arBasket["PRODUCT_XML_ID"]]["VAT_RATE"])
											$arFields["VAT_RATE"] = $v["items"][$arBasket["PRODUCT_XML_ID"]]["VAT_RATE"];
										
										if(count($arFields)>0)
											CSaleBasket::Update($arBasket["ID"], $arFields);
										
										$v["items"][$arBasket["PRODUCT_XML_ID"]]["CHECKED"] = "Y";
									}
									else
									{
										CSaleBasket::Delete($arBasket["ID"]);
									}
								}
								foreach($v["items"] as $itemID => $arItem)
								{
									if($arItem["CHECKED"] != "Y")
									{
										if($arItem["TYPE"] == GetMessage("CC_BSC1_ITEM"))
										{
											CModule::IncludeModule("iblock");
											$dbIBlockElement = CIBlockElement::GetList(array(), array(
															"XML_ID" => $itemID,
															"ACTIVE_DATE" => "Y",
															"ACTIVE" => "Y",
															"CHECK_PERMISSIONS" => "Y",
														), false, false, array(
															"ID",
															"IBLOCK_ID",
															"XML_ID",
															"NAME",
															"DETAIL_PAGE_URL",
											));
											if($arIBlockElement = $dbIBlockElement->GetNext())
											{
												$dbIBlock = CIBlock::GetList(
														array(),
														array("ID" => $arIBlockElement["IBLOCK_ID"])
													);
												if ($arIBlock = $dbIBlock->Fetch())
												{
													$arProps[] = array(
															"NAME" => "Catalog XML_ID",
															"CODE" => "CATALOG.XML_ID",
															"VALUE" => $arIBlock["XML_ID"]
														);
												}

												$arProps[] = array(
														"NAME" => "Product XML_ID",
														"CODE" => "PRODUCT.XML_ID",
														"VALUE" => $arIBlockElement["XML_ID"]
													);
												$arProduct = CCatalogProduct::GetByID($arIBlockElement["ID"]);
												
												$arFields = array(
														"ORDER_ID" => $k,
														"PRODUCT_ID" => $arIBlockElement["ID"],
														"PRICE" => $arItem["PRICE"],
														"CURRENCY" => $orderInfo["CURRENCY"],
														"WEIGHT" => $arProduct["WEIGHT"],
														"QUANTITY" => $arItem["QUANTITY"],
														"LID" => $orderInfo["LID"],
														"DELAY" => "N",
														"CAN_BUY" => "Y",
														"NAME" => $arIBlockElement["~NAME"],
														"CALLBACK_FUNC" => "CatalogBasketCallback",
														"MODULE" => "catalog",
														"NOTES" => $arProduct["CATALOG_GROUP_NAME"],
														"ORDER_CALLBACK_FUNC" => "CatalogBasketOrderCallback",
														"CANCEL_CALLBACK_FUNC" => "CatalogBasketCancelCallback",
														"PAY_CALLBACK_FUNC" => "CatalogPayOrderCallback",
														"DETAIL_PAGE_URL" => $arIBlockElement["DETAIL_PAGE_URL"],
														"CATALOG_XML_ID" => $arIBlock["XML_ID"],
														"PRODUCT_XML_ID" => $arIBlockElement["XML_ID"],
														"IGNORE_CALLBACK_FUNC" => "Y",
														"VAT_RATE" => $arItem["VAT_RATE"],
													);
/*
												if (is_array($arProductParams) && count($arProductParams) > 0)
												{
													for ($i = 0; $i < count($arProductParams); $i++)
													{
														$arProps[] = array(
																"NAME" => $arProductParams[$i]["NAME"],
																"CODE" => $arProductParams[$i]["CODE"],
																"VALUE" => $arProductParams[$i]["VALUE"]
															);
													}
												}
												$arFields["PROPS"] = $arProps;
*/
												CSaleBasket::Add($arFields);
											}
											else
											{
												$strError .= "\n".GetMessage("CC_BSC1_PRODUCT_NOT_FOUND").$k." - [".$itemID."] ".$arItem["NAME"];
											}
										
										}
										elseif($arItem["TYPE"] == GetMessage("CC_BSC1_SERVICE"))
										{
											if(IntVal($arItem["PRICE"]) != IntVal($orderInfo["PRICE_DELIVERY"]))
												CSaleOrder::Update($k, Array("PRICE_DELIVERY" => $arItem["PRICE"]));
										}
									}
								}
								
								
								if($v["AMOUNT"] != $orderInfo["PRICE"])
									CSaleOrder::Update($k, Array("PRICE" => $v["AMOUNT"]));
							}
							else
							{
								$strError .= "\n".GetMessage("CC_BSC1_FINAL_NOT_EDIT", Array("#ID#" => $k));
							}

							$arAditFields = Array();							
							if($v["TRAITS"][GetMessage("CC_BSC1_CANCELED")] == "true")
							{
								if($orderInfo["CANCELED"] == "N")
									CSaleOrder::CancelOrder($k, "Y", $v["COMMENT"]);
							}
							else
							{
								if(strlen($v["TRAITS"][GetMessage("CC_BSC1_1C_PAYED_DATE")])>1)
								{
									if($orderInfo["PAYED"]=="N")
										CSaleOrder::PayOrder($k, "Y");
									$arAditFields["PAY_VOUCHER_DATE"] = CDatabase::FormatDate($v["TRAITS"][GetMessage("CC_BSC1_1C_PAYED_DATE")], "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL", LANG));
									if(strlen($v["TRAITS"][GetMessage("CC_BSC1_1C_PAYED_NUM")])>0)
										$arAditFields["PAY_VOUCHER_NUM"] = $v["TRAITS"][GetMessage("CC_BSC1_1C_PAYED_NUM")];
								}
								if(strlen($v["TRAITS"][GetMessage("CC_BSC1_1C_DELIVERY_DATE")])>1)
								{
									if($orderInfo["ALLOW_DELIVERY"]=="N")
										CSaleOrder::DeliverOrder($k, "Y");
									$arAditFields["DATE_ALLOW_DELIVERY"] = CDatabase::FormatDate($v["TRAITS"][GetMessage("CC_BSC1_1C_DELIVERY_DATE")], "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL", LANG));
									if(strlen($arParams["FINAL_STATUS_ON_DELIVERY"])>0 && $orderInfo["STATUS_ID"] != "F" && $orderInfo["STATUS_ID"] != $arParams["FINAL_STATUS_ON_DELIVERY"])
										CSaleOrder::StatusOrder($k, $arParams["FINAL_STATUS_ON_DELIVERY"]);
								}
							}
							
							if(count($arAditFields)>0)
								CSaleOrder::Update($k, $arAditFields);

						}
						else
							$strError .= "\n".GetMessage("CC_BSC1_ORDER_NOT_FOUND", Array("#ID#" => $k));
					}

					echo "success\n";
					if(strlen($strError)>0)
						echo $strError;
				}
			}
			else
				echo "failure\n".GetMessage("CC_BSC1_EMPTY_CML");
		}
	}
	else
	{
		echo "failure\n",GetMessage("CC_BSC1_ERROR_UNKNOWN_COMMAND");
	}
}

$contents = ob_get_contents();
ob_end_clean();

if(!$bDesignMode)
{
	if(toUpper(LANG_CHARSET) != "WINDOWS-1251")
		$contents = $APPLICATION->ConvertCharset($contents, LANG_CHARSET, "windows-1251");
	$str = (function_exists("mb_strlen")? mb_strlen($contents, 'latin1'): strlen($contents));
	if($_GET["mode"] == "query")
	{
		header("Content-Type: application/xml; charset=windows-1251");
		header("Content-Length: ".$str);
	}
	else
	{
		header("Content-Type: text/html; charset=windows-1251");
	}

	echo $contents;
	die();
}
else
{
	$this->IncludeComponentLang(".parameters.php");
	$arStatuses = Array("" => GetMessage("CP_BCI1_NO"));
	CModule::IncludeModule("sale");
	$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID));
	while ($arStatus = $dbStatus->GetNext())
	{
		$arStatuses[$arStatus["ID"]] = "[".$arStatus["ID"]."] ".$arStatus["NAME"];
	}

	?><table class="data-table">
	<tr><td><?echo GetMessage("CP_BCI1_SITE_LIST")?></td><td><?echo $arParams["SITE_LIST"]?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_EXPORT_PAYED_ORDERS")?></td><td><?echo $arParams["EXPORT_PAYED_ORDERS"]? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_EXPORT_ALLOW_DELIVERY_ORDERS")?></td><td><?echo $arParams["EXPORT_ALLOW_DELIVERY_ORDERS"]? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_EXPORT_FINAL_ORDERS")?></td><td><?echo $arStatuses[$arParams["EXPORT_FINAL_ORDERS"]]?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_FINAL_STATUS_ON_DELIVERY")?></td><td><?echo $arStatuses[$arParams["FINAL_STATUS_ON_DELIVERY"]]?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_REPLACE_CURRENCY")?></td><td><?echo $arParams["REPLACE_CURRENCY"]?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_USE_ZIP")?></td><td><?echo $arParams["USE_ZIP"]? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")?></td></tr>
	</table>
	<?
}
?>
