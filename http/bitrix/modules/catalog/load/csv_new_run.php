<?
//<title>CSV Export (new)</title>
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/data_export.php"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");

global $USER;
$bTmpUserCreated = False;
if (!isset($USER))
{
	$bTmpUserCreated = True;
	$USER = new CUser;
}

$strExportErrorMessage = "";

$IBLOCK_ID = IntVal($IBLOCK_ID);
$arIBlockres = CIBlock::GetList(Array("sort"=>"asc"), Array("ID"=>IntVal($IBLOCK_ID)));
$arIBlockres = new CIBlockResult($arIBlockres);
if ($IBLOCK_ID<=0 || !($arIBlock = $arIBlockres->GetNext()))
	$strExportErrorMessage .= GetMessage("CATI_NO_IBLOCK")."<br>";

if (strlen($strExportErrorMessage)<=0)
{
	$csvFile = new CCSVData();

	if ($fields_type!="F" && $fields_type!="R")
		$strExportErrorMessage .= GetMessage("CATI_NO_FORMAT")."<br>";

	$csvFile->SetFieldsType($fields_type);

	$first_names_r = (($first_names_r=="Y") ? "Y" : "N" );
	$csvFile->SetFirstHeader(($first_names_r=="Y")?true:false);

	$delimiter_r_char = "";
	switch ($delimiter_r)
	{
		case "TAB":
			$delimiter_r_char = "\t";
			break;
		case "ZPT":
			$delimiter_r_char = ",";
			break;
		case "SPS":
			$delimiter_r_char = " ";
			break;
		case "OTR":
			$delimiter_r_char = substr($delimiter_other_r, 0, 1);
			break;
		case "TZP":
			$delimiter_r_char = ";";
			break;
	}

	if (strlen($delimiter_r_char)!=1)
		$strExportErrorMessage .= GetMessage("CATI_NO_DELIMITER")."<br>";

	if (strlen($strExportErrorMessage)<=0)
	{
		$csvFile->SetDelimiter($delimiter_r_char);
	}

	if (strlen($SETUP_FILE_NAME) <= 0)
	{
		$strExportErrorMessage .= GetMessage("CATI_NO_SAVE_FILE")."<br>";
	}
	else
	{
		$SETUP_FILE_NAME = Rel2Abs("/", $SETUP_FILE_NAME);
		if (strtolower(substr($SETUP_FILE_NAME, strlen($SETUP_FILE_NAME)-4)) != ".csv")
			$SETUP_FILE_NAME .= ".csv";
		if ($GLOBALS["APPLICATION"]->GetFileAccessPermission($SETUP_FILE_NAME) < "W")
			$strExportErrorMessage .= str_replace("#FILE#", $SETUP_FILE_NAME, GetMessage('CATI_NO_RIGHTS_FILE'))."<br>";
	}

	$bFieldsPres = False;
	if (is_array($field_needed))
	{
		for ($i = 0; $i < count($field_needed); $i++)
		{
			if ($field_needed[$i]=="Y")
			{
				$bFieldsPres = True;
				break;
			}
		}
	}
	if (!$bFieldsPres)
		$strExportErrorMessage .= GetMessage("CATI_NO_FIELDS")."<br>";

	/*
	// We can't link more than 30 tables.
	$tableLinksCount = 10;
	for ($i = 0; $i < count($field_code); $i++)
	{
		if (substr($field_code[$i], 0, strlen("CR_PRICE_"))=="CR_PRICE_" && $field_needed[$i]=="Y")
		{
			$tableLinksCount++;
		}
		elseif (substr($field_code[$i], 0, strlen("IP_PROP"))=="IP_PROP" && $field_needed[$i]=="Y")
		{
			$tableLinksCount+=2;
		}
	}
	if ($tableLinksCount>30)
		$strExportErrorMessage .= GetMessage("CATI_TOO_MANY_TABLES")."<br>";
	*/

	if (strlen($strExportErrorMessage)<=0)
	{
		if (!($fp = fopen($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME, "w")))
			$strExportErrorMessage .= GetMessage("CATI_CANNOT_CREATE_FILE")."<br>";
		@fclose($fp);
	}

	$num_rows_writed = 0;
	if (strlen($strExportErrorMessage)<=0)
	{
		global $defCatalogAvailGroupFields, $defCatalogAvailProdFields, $defCatalogAvailPriceFields;

		// Prepare arrays for groups loading
		$strAvailGroupFields = COption::GetOptionString("catalog", "allowed_group_fields", $defCatalogAvailGroupFields);
		$arAvailGroupFields = explode(",", $strAvailGroupFields);
		$arAvailGroupFields_names = array();
		for ($i = 0; $i < count($arAvailGroupFields); $i++)
		{
			for ($j = 0; $j < count($arCatalogAvailGroupFields); $j++)
			{
				if ($arCatalogAvailGroupFields[$j]["value"]==$arAvailGroupFields[$i])
				{
					$arAvailGroupFields_names[$arAvailGroupFields[$i]] = array(
							"field" => $arCatalogAvailGroupFields[$j]["field"],
							"important" => $arCatalogAvailGroupFields[$j]["important"]
						);
					break;
				}
			}
		}

		// Prepare arrays for product loading
		$strAvailProdFields = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailProdFields);
		$arAvailProdFields = explode(",", $strAvailProdFields);
		$arAvailProdFields_names = array();
		for ($i = 0; $i < count($arAvailProdFields); $i++)
		{
			for ($j = 0; $j < count($arCatalogAvailProdFields); $j++)
			{
				if ($arCatalogAvailProdFields[$j]["value"]==$arAvailProdFields[$i])
				{
					$arAvailProdFields_names[$arAvailProdFields[$i]] = array(
						"field" => $arCatalogAvailProdFields[$j]["field"],
						"important" => $arCatalogAvailProdFields[$j]["important"]
						);
					break;
				}
			}
		}

		// Prepare arrays for product loading (for catalog)
		$strAvailPriceFields = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailPriceFields);
		$arAvailPriceFields = explode(",", $strAvailProdFields);
		$arAvailPriceFields_names = array();
		for ($i = 0; $i < count($arAvailPriceFields); $i++)
		{
			for ($j = 0; $j < count($arCatalogAvailPriceFields); $j++)
			{
				if ($arCatalogAvailPriceFields[$j]["value"]==$arAvailPriceFields[$i])
				{
					$arAvailPriceFields_names[$arAvailPriceFields[$i]] = array(
						"field" => $arCatalogAvailPriceFields[$j]["field"],
						"important" => $arCatalogAvailPriceFields[$j]["important"]
						);
					break;
				}
			}
		}

		// ПPrepare arrays for price loading
		$strAvailValueFields = COption::GetOptionString("catalog", "allowed_price_fields", $defCatalogAvailValueFields);
		$arAvailValueFields = explode(",", $strAvailValueFields);
		$arAvailValueFields_names = array();
		for ($i = 0; $i < count($arAvailValueFields); $i++)
		{
			for ($j = 0; $j < count($arCatalogAvailValueFields); $j++)
			{
				if ($arCatalogAvailValueFields[$j]["value"] == $arAvailValueFields[$i])
				{
					$arAvailValueFields_names[$arAvailValueFields[$i]] = array(
						"field" => $arCatalogAvailValueFields[$j]["field"],
						"important" => $arCatalogAvailValueFields[$j]["important"]
						);
					break;
				}
			}
		}

		$selectArray = array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID");
		foreach ($arAvailProdFields_names as $key => $value)
		{
			$selectArray[] = $value["field"];
		}

		$bNeedGroups = False;
		$bNeedPrices = False;
		foreach ($field_needed as $field_needed_key => $field_needed_value)
		{
			if ($field_needed_value == "Y")
			{
				if (!$bNeedPrices)
				{
					foreach ($arAvailValueFields_names as $key => $value)
					{
						if ($key == substr($field_code[$field_needed_key], 0, strlen($key))
							&& is_numeric(substr($field_code[$field_needed_key], strlen($key) + 1)))
						{
							$bNeedPrices = True;
							break;
						}
					}
				}

				if (!$bNeedGroups)
				{
					foreach ($arAvailGroupFields_names as $key => $value)
					{
						if ($key==substr($field_code[$field_needed_key], 0, strlen($key))
							&& is_numeric(substr($field_code[$field_needed_key], strlen($key))))
						{
							$bNeedGroups = True;
							break;
						}
					}
				}

				if ($bNeedGroups && $bNeedPrices)
					break;
			}
		}

		$arNeedFields = array();
		$field_neededTmp = $field_needed;
		$field_numTmp = $field_num;
		$field_codeTmp = $field_code;
		for ($i = 0; $i < count($field_numTmp); $i++)
		{
			for ($j = $i+1; $j < count($field_numTmp); $j++)
			{
				if (IntVal($field_numTmp[$i])>IntVal($field_numTmp[$j]))
				{
					$tmpVal = $field_neededTmp[$i];
					$field_neededTmp[$i] = $field_neededTmp[$j];
					$field_neededTmp[$j] = $tmpVal;

					$tmpVal = IntVal($field_numTmp[$i]);
					$field_numTmp[$i] = IntVal($field_numTmp[$j]);
					$field_numTmp[$j] = $tmpVal;

					$tmpVal = $field_codeTmp[$i];
					$field_codeTmp[$i] = $field_codeTmp[$j];
					$field_codeTmp[$j] = $tmpVal;
				}
			}
		}

		for ($i = 0; $i < count($field_numTmp); $i++)
		{
			if ($field_neededTmp[$i]=="Y")
			{
				$arNeedFields[] = $field_codeTmp[$i];
			}
		}

		if ($first_line_names=="Y")
		{
			$arResFields = array();
			for ($i = 0; $i < count($arNeedFields); $i++)
			{
				$arResFields[$i] = $arNeedFields[$i];
			}
			$csvFile->SaveFile($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME, $arResFields);
		}

		$dbIBlockElement = CIBlockElement::GetList(
				array(),
				array("IBLOCK_ID" => $IBLOCK_ID),
				false,
				false,
				$selectArray
			);
		while ($arIBlockElement = $dbIBlockElement->Fetch())
		{
			$arResSections = array();
			if ($bNeedGroups)
			{
				$indreseg = 0;
				$reseg = CIBlockElement::GetElementGroups($arIBlockElement["ID"]);
				while ($reseg1 = $reseg->Fetch())
				{
					$sections_path = GetIBlockSectionPath($IBLOCK_ID, $reseg1["ID"]);
					while ($arSection = $sections_path->GetNext())
					{
						$arResSectionTmp = array();
						foreach ($arAvailGroupFields_names as $key => $value)
						{
							$arResSectionTmp[$key] = $arSection[$value["field"]];
						}
						$arResSections[$indreseg][] = $arResSectionTmp;
					}
					$indreseg++;
				}
				if (count($arResSections)<=0)
					$arResSections[0] = array();
			}
			else
			{
				$arResSections[0] = array();
			}

			$arResPrices = array();
			if ($bNeedPrices)
			{
				$arResPricesMap = array();
				$mapIndex = -1;

				$dbProductPrice = CPrice::GetListEx(
						array(),
						array("PRODUCT_ID" => $arIBlockElement["ID"]),
						false,
						false,
						array("ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO")
					);
				while ($arProductPrice = $dbProductPrice->Fetch())
				{
					if (!array_key_exists($arProductPrice["QUANTITY_FROM"]."-".$arProductPrice["QUANTITY_TO"], $arResPricesMap))
					{
						$mapIndex++;
						$arResPricesMap[$arProductPrice["QUANTITY_FROM"]."-".$arProductPrice["QUANTITY_TO"]] = $mapIndex;
					}
					$arResPrices[$arResPricesMap[$arProductPrice["QUANTITY_FROM"]."-".$arProductPrice["QUANTITY_TO"]]][IntVal($arProductPrice["CATALOG_GROUP_ID"])] = $arProductPrice;
				}
				if (count($arResPrices)<=0)
					$arResPrices[0] = array();
			}
			else
			{
				$arResPrices[0] = array();
			}

			$arElementPropList = array();

			foreach ($arResSections as $inds => $arResSect)
			{
				foreach ($arResPrices as $jnds => $arResPrice)
				{
					$arResFields = array(0 => array());

					for ($i = 0; $i < count($arNeedFields); $i++)
					{
						for ($j = 0; $j < count($arResFields); $j++)
						{
							$arResFields[$j][$i] = "";
						}

						$bFieldOut = False;

						if (is_set($arAvailProdFields_names, $arNeedFields[$i]))
						{
							$bFieldOut = True;
							$shownValue = $arIBlockElement[$arAvailProdFields_names[$arNeedFields[$i]]["field"]];
							if ($arNeedFields[$i]=="IE_PREVIEW_PICTURE" || $arNeedFields[$i]=="IE_DETAIL_PICTURE")
							{
								if (IntVal($shownValue) > 0)
								{
									$db_z = CFile::GetByID(IntVal($shownValue));
									if ($z = $db_z->Fetch())
									{
										$shownValue = $z["FILE_NAME"];
									}
								}
								else
								{
									$shownValue = "";
								}
							}

							if (count($arResFields) > 0)
							{
								for ($j = 0; $j < count($arResFields); $j++)
								{
									$arResFields[$j][$i] = $shownValue;
								}
							}
							else
							{
								$arResFields[0] = array();
								$arResFields[0][$i] = $shownValue;
							}
						}


						if (!$bFieldOut && is_set($arAvailPriceFields_names, $arNeedFields[$i]))
						{
							$bFieldOut = True;
							$shownValue = "";
							if ($arCatalogProduct = CCatalogProduct::GetByID($arIBlockElement["ID"]))
								$shownValue = $arCatalogProduct[$arAvailPriceFields_names[$arNeedFields[$i]]["field"]];

							if (count($arResFields) > 0)
							{
								for ($j = 0; $j < count($arResFields); $j++)
								{
									$arResFields[$j][$i] = $shownValue;
								}
							}
							else
							{
								$arResFields[0] = array();
								$arResFields[0][$i] = $shownValue;
							}
						}


						if (!$bFieldOut && substr($arNeedFields[$i], 0, strlen("IP_PROP"))=="IP_PROP")
						{
							$propertyCode = substr($arNeedFields[$i], strlen("IP_PROP"));

							if (!isset($arElementPropList) || !is_array($arElementPropList) || count($arElementPropList) <= 0)
							{
								$dbElementProp = CIBlockElement::GetProperty(
										$IBLOCK_ID,
										$arIBlockElement["ID"]
									);
								while ($arElementProp = $dbElementProp->Fetch())
								{
									if ($arElementProp["PROPERTY_TYPE"] == "L")
										$valueTmp = $arElementProp["VALUE_ENUM"];
									else
										$valueTmp = $arElementProp["VALUE"];

									$arElementPropList[$arElementProp["ID"]][] = $valueTmp;
								}
							}

							for ($k = 0; $k < count($arElementPropList[$propertyCode]); $k++)
							{
								$shownValue = $arElementPropList[$propertyCode][$k];

								if ($k == 0)
								{
									if (count($arResFields) > 0)
									{
										for ($j = 0; $j < count($arResFields); $j++)
										{
											$arResFields[$j][$i] = $shownValue;
										}
									}
									else
									{
										$arResFields[0] = array();
										$arResFields[0][$i] = $shownValue;
									}
								}
								else
								{
									$currentNum = count($arResFields);
									for ($m = 0; $m < $currentNum; $m++)
									{
										for ($n = 0; $n < count($arResFields[$m]); $n++)
										{
											$arResFields[$currentNum + $m][$n] = $arResFields[$m][$n];
										}
										$arResFields[$currentNum + $m][$i] = $shownValue;
									}
								}
							}

							$bFieldOut = True;
						}


						if (!$bFieldOut)
						{
							if ($arNeedFields[$i] == "CV_QUANTITY_FROM" || $arNeedFields[$i] == "CV_QUANTITY_TO")
							{
								foreach ($arResPrices[$jnds] as $keyTmp => $valueTmp)
								{
									if ($arNeedFields[$i] == "CV_QUANTITY_FROM")
										$shownValue = $valueTmp["QUANTITY_FROM"];
									else
										$shownValue = $valueTmp["QUANTITY_TO"];
									break;
								}

								if (count($arResFields) > 0)
								{
									for ($j = 0; $j < count($arResFields); $j++)
									{
										$arResFields[$j][$i] = $shownValue;
									}
								}
								else
								{
									$arResFields[0] = array();
									$arResFields[0][$i] = $shownValue;
								}

								$bFieldOut = True;
							}
						}


						if (!$bFieldOut)
						{
							foreach ($arAvailValueFields_names as $key => $value)
							{
								if ($key == substr($arNeedFields[$i], 0, strlen($key))
									&& is_numeric(substr($arNeedFields[$i], strlen($key) + 1)))
								{
									$bFieldOut = True;
									$shownValue = $arResPrices[$jnds][IntVal(substr($arNeedFields[$i], strlen($key) + 1))][$arAvailValueFields_names[$key]["field"]];

									if (count($arResFields) > 0)
									{
										for ($j = 0; $j < count($arResFields); $j++)
										{
											$arResFields[$j][$i] = $shownValue;
										}
									}
									else
									{
										$arResFields[0] = array();
										$arResFields[0][$i] = $shownValue;
									}

									break;
								}
							}
						}


						if (!$bFieldOut)
						{
							foreach ($arAvailGroupFields_names as $key => $value)
							{
								if ($key == substr($arNeedFields[$i], 0, strlen($key))
									&& is_numeric(substr($arNeedFields[$i], strlen($key))))
								{
									$bFieldOut = True;
									$shownValue = $arResSections[$inds][IntVal(substr($arNeedFields[$i], strlen($key)))][$key];

									if (count($arResFields) > 0)
									{
										for ($j = 0; $j < count($arResFields); $j++)
										{
											$arResFields[$j][$i] = $shownValue;
										}
									}
									else
									{
										$arResFields[0] = array();
										$arResFields[0][$i] = $shownValue;
									}

									break;
								}
							}
						}
					}

					$numRows = count($arResFields);
					$numCols = -1;
					for ($j = 0; $j < $numRows; $j++)
					{
						if ($numCols < 0)
							$numCols = count($arResFields[$j]);

						if ($numCols != count($arResFields[$j]))
							die("<b>&gt;&gt;&gt;&gt;&gt;ERROR!!!</b><br>");

						$arResFields[$j][$numCols] = "N";
					}
					$numCols++;

					for ($j = 0; $j < $numRows - 1; $j++)
					{
						for ($k = $j + 1; $k < $numRows; $k++)
						{
							$bIdent = True;
							for ($m = 0; $m < $numCols - 1; $m++)
							{
								if ($arResFields[$j][$m] != $arResFields[$k][$m])
								{
									$bIdent = False;
									break;
								}
							}

							if ($bIdent)
							{
								$arResFields[$k][$numCols - 1] = "Y";
							}
						}
					}

					for ($j = 0; $j < count($arResFields); $j++)
					{
						if ($arResFields[$j][$numCols - 1] == "N")
						{
							unset($arResFields[$j][$numCols - 1]);
							$csvFile->SaveFile($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME, $arResFields[$j]);
							$num_rows_writed++;
						}
					}
				}
			}
		}
	}
	//*****************************************************************//
}
if ($bTmpUserCreated) unset($USER);
?>