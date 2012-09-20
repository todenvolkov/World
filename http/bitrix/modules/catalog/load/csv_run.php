<?
//<title>CSV Export</title>
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

	// Мы не можем слинковать более 30 таблиц. Поэтому количество экспортируемых полей ограничено
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

		// Подготовим массивы для загрузки групп
		$strAvailGroupFields = COption::GetOptionString("catalog", "allowed_group_fields", $defCatalogAvailGroupFields);
		$arAvailGroupFields = split(",", $strAvailGroupFields);
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

		// Подготовим массивы для загрузки товаров
		$strAvailProdFields = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailProdFields);
		$arAvailProdFields = split(",", $strAvailProdFields);
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

		// Подготовим массивы для загрузки товаров (для каталога)
		$strAvailPriceFields = COption::GetOptionString("catalog", "allowed_product_fields", $defCatalogAvailPriceFields);
		$arAvailPriceFields = split(",", $strAvailProdFields);
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

		$selectArray = array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID");
		foreach ($arAvailProdFields_names as $key => $value)
		{
			$selectArray[] = $value["field"];
		}

		$bNeedGroups = False;
		for ($i = 0; $i < count($field_code); $i++)
		{
			if (substr($field_code[$i], 0, strlen("CR_PRICE_"))=="CR_PRICE_" && $field_needed[$i]=="Y")
			{
				$sPriceTmp = substr($field_code[$i], strlen("CR_PRICE_"));
				$arPriceTmp = Split("_", $sPriceTmp);
				if (IntVal($arPriceTmp[0])>0)
				{
					$selectArray[] = "CATALOG_GROUP_".IntVal($arPriceTmp[0]);
				}
			}
			elseif (substr($field_code[$i], 0, strlen("IP_PROP"))=="IP_PROP" && $field_needed[$i]=="Y")
			{
				$selectArray[] = "PROPERTY_".substr($field_code[$i], strlen("IP_PROP"));
			}

			if (!$bNeedGroups)
			{
				foreach ($arAvailGroupFields_names as $key => $value)
				{
					if ($key==substr($field_code[$i], 0, strlen($key))
						&& is_numeric(substr($field_code[$i], strlen($key))))
					{
						$bNeedGroups = True;
						break;
					}
				}
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

		$res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID), false, false, $selectArray);
		while ($res1 = $res->Fetch())
		{
			$arResSections = array();
			if ($bNeedGroups)
			{
				$indreseg = 0;
				$reseg = CIBlockElement::GetElementGroups($res1["ID"]);
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
				/*$sections_path = GetIBlockSectionPath($IBLOCK_ID, $res1["IBLOCK_SECTION_ID"]);
				while ($arSection = $sections_path->GetNext())
				{
					$arResSectionTmp = array();
					foreach ($arAvailGroupFields_names as $key => $value)
					{
						$arResSectionTmp[$key] = $arSection[$value["field"]];
					}
					$arResSections[0][] = $arResSectionTmp;
				}*/
			}

			for ($inds = 0; $inds < count($arResSections); $inds++)
			{
				$arResFields = array();
				for ($i = 0; $i < count($arNeedFields); $i++)
				{
					$bFieldOut = False;
					if (is_set($arAvailProdFields_names, $arNeedFields[$i]))
					{
						$bFieldOut = True;
						$arResFields[$i] = $res1[$arAvailProdFields_names[$arNeedFields[$i]]["field"]];
						if ($arNeedFields[$i]=="IE_PREVIEW_PICTURE" || $arNeedFields[$i]=="IE_DETAIL_PICTURE")
						{
							if (IntVal($arResFields[$i])>0)
							{
								$db_z = CFile::GetByID(IntVal($arResFields[$i]));
								if ($z = $db_z->Fetch())
								{
									$arResFields[$i] = $z["FILE_NAME"];
								}
							}
							else
							{
								$arResFields[$i] = "";
							}
						}
					}

					if (!$bFieldOut && is_set($arAvailPriceFields_names, $arNeedFields[$i]))
					{
						$bFieldOut = True;
						$arResFields[$i] = $res1["CATALOG_".$arAvailPriceFields_names[$arNeedFields[$i]]["field"]];
					}

					if (!$bFieldOut)
					{
						if (substr($arNeedFields[$i], 0, strlen("IP_PROP"))=="IP_PROP")
						{
							$arResFields[$i] = $res1["PROPERTY_".substr($arNeedFields[$i], strlen("IP_PROP"))."_VALUE"];
							$bFieldOut = True;
						}
						elseif (substr($arNeedFields[$i], 0, strlen("CR_PRICE_"))=="CR_PRICE_")
						{
							$sPriceTmp = substr($arNeedFields[$i], strlen("CR_PRICE_"));
							$arPriceTmp = Split("_", $sPriceTmp);
							if (strlen($res1["CATALOG_CURRENCY_".IntVal($arPriceTmp[0])])>0
								&& $res1["CATALOG_CURRENCY_".IntVal($arPriceTmp[0])]!=$arPriceTmp[1])
							{
								$arResFields[$i] = Round(CCurrencyRates::ConvertCurrency($res1["CATALOG_PRICE_".IntVal($arPriceTmp[0])], $res1["CATALOG_CURRENCY_".IntVal($arPriceTmp[0])], $arPriceTmp[1]), 2);
							}
							else
							{
								$arResFields[$i] = $res1["CATALOG_PRICE_".IntVal($arPriceTmp[0])];
							}
							$bFieldOut = True;
						}
					}

					if (!$bFieldOut)
					{
						foreach ($arAvailGroupFields_names as $key => $value)
						{
							if ($key==substr($arNeedFields[$i], 0, strlen($key))
								&& is_numeric(substr($arNeedFields[$i], strlen($key))))
							{
								$bFieldOut = True;
								$arResFields[$i] = $arResSections[$inds][IntVal(substr($arNeedFields[$i], strlen($key)))][$key];
								break;
							}
						}
					}
				}

				$csvFile->SaveFile($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME, $arResFields);
				$num_rows_writed++;
			}
		}
	}
	//*****************************************************************//
}


if ($bTmpUserCreated) unset($USER);
?>