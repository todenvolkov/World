<?
//<title>CSV</title>
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/import_setup_templ.php"));
$startImportExecTime = getmicrotime();

global $USER;
$bTmpUserCreated = False;

if (!isset($USER) || !is_a($GLOBALS['USER'], 'CUser'))
{
	$bTmpUserCreated = True;
	if (isset($USER))
	{
		$USER_TMP = $USER;
	}
	
	$USER = new CUser();
}

$strImportErrorMessage = "";
$strImportOKMessage = "";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");

$NUM_CATALOG_LEVELS = IntVal(COption::GetOptionString("catalog", "num_catalog_levels", 3));

$max_execution_time = IntVal($max_execution_time);
if ($max_execution_time <= 0)
	$max_execution_time = 0;

if (defined("CATALOG_LOAD_NO_STEP") && CATALOG_LOAD_NO_STEP)
	$max_execution_time = 0;

$bAllLinesLoaded = True;

if (!function_exists('__TTT'))
{
	function __TTT($txt)
	{
		if (strlen($txt) > 0)
		{
			$fff = fopen($_SERVER["DOCUMENT_ROOT"]."/__ttt.txt", "a");
			fwrite($fff, $txt."\n");
			fclose($fff);
		}
	}
}

if (!function_exists('CSVCheckTimeout'))
{
	function CSVCheckTimeout($max_execution_time)
	{
		return ($max_execution_time <= 0) || (getmicrotime()-START_EXEC_TIME <= $max_execution_time);
	}
}

$DATA_FILE_NAME = "";

if (strlen($URL_DATA_FILE) > 0)
{
	$URL_DATA_FILE = Rel2Abs("/", $URL_DATA_FILE);
	if (!$bTmpUserCreated && file_exists($_SERVER["DOCUMENT_ROOT"].$URL_DATA_FILE) && is_file($_SERVER["DOCUMENT_ROOT"].$URL_DATA_FILE) && $GLOBALS["APPLICATION"]->GetFileAccessPermission($URL_DATA_FILE)>="W")
		$DATA_FILE_NAME = $URL_DATA_FILE;
}

if (strlen($DATA_FILE_NAME) <= 0)
	$strImportErrorMessage .= GetMessage("CATI_NO_DATA_FILE")."<br>";

$IBLOCK_ID = IntVal($IBLOCK_ID);
$arIBlockres = CIBlock::GetList(Array("sort"=>"asc"), Array("ID"=>IntVal($IBLOCK_ID), 'MIN_PERMISSION' => 'W'));
$arIBlockres = new CIBlockResult($arIBlockres);
if ($IBLOCK_ID <= 0 || !($arIBlock = $arIBlockres->GetNext()))
	$strImportErrorMessage .= GetMessage("CATI_NO_IBLOCK")."<br>";

if (strlen($strImportErrorMessage) <= 0)
{
	$bIBlockIsCatalog = False;
	if (CCatalog::GetByID($IBLOCK_ID))
		$bIBlockIsCatalog = True;

	$csvFile = new CCSVData();
	$csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);

	if ($fields_type!="F" && $fields_type!="R")
		$strImportErrorMessage .= GetMessage("CATI_NO_FILE_FORMAT")."<br>";
}

if (strlen($strImportErrorMessage) <= 0)
{
	$arDataFileFields = array();
	$fields_type = (($fields_type=="F") ? "F" : "R" );

	$csvFile->SetFieldsType($fields_type);

	if ($fields_type == "R")
	{
		$first_names_r = (($first_names_r=="Y") ? "Y" : "N" );
		$csvFile->SetFirstHeader(($first_names_r=="Y") ? true : false);

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

		if (strlen($delimiter_r_char) != 1)
			$strImportErrorMessage .= GetMessage("CATI_NO_DELIMITER")."<br>";

		if (strlen($strImportErrorMessage) <= 0)
			$csvFile->SetDelimiter($delimiter_r_char);
	}
	else
	{
		$first_names_f = (($first_names_f=="Y") ? "Y" : "N" );
		$csvFile->SetFirstHeader(($first_names_f=="Y") ? true : false);

		if (strlen($metki_f) <= 0)
			$strImportErrorMessage .= GetMessage("CATI_NO_METKI")."<br>";

		if (strlen($strImportErrorMessage) <= 0)
		{
			$arMetkiTmp = preg_split("/[\D]/i", $metki_f);

			$arMetki = array();
			for ($i = 0; $i < count($arMetkiTmp); $i++)
			{
				if (IntVal($arMetkiTmp[$i]) > 0)
				{
					$arMetki[] = IntVal($arMetkiTmp[$i]);
				}
			}

			if (!is_array($arMetki) || count($arMetki)<1)
				$strImportErrorMessage .= GetMessage("CATI_NO_METKI")."<br>";

			if (strlen($strImportErrorMessage) <= 0)
				$csvFile->SetWidthMap($arMetki);
		}
	}

	if (strlen($strImportErrorMessage) <= 0)
	{
		$bFirstHeaderTmp = $csvFile->GetFirstHeader();
		$csvFile->SetFirstHeader(false);
		if ($arRes = $csvFile->Fetch())
		{
			for ($i = 0; $i < count($arRes); $i++)
			{
				$arDataFileFields[$i] = $arRes[$i];
			}
		}
		else
		{
			$strImportErrorMessage .= GetMessage("CATI_NO_DATA")."<br>";
		}
		global $NUM_FIELDS;
		$NUM_FIELDS = count($arDataFileFields);
	}
}

if (strlen($strImportErrorMessage) <= 0)
{
	$bFieldsPres = False;
	for ($i = 0; $i < $NUM_FIELDS; $i++)
	{
		if (strlen(${"field_".$i})>0)
		{
			$bFieldsPres = True;
			break;
		}
	}
	if (!$bFieldsPres)
		$strImportErrorMessage .= GetMessage("CATI_NO_FIELDS")."<br>";
}

if (strlen($strImportErrorMessage) <= 0)
{
	$csvFile->SetPos($CUR_FILE_POS);
	$arRes = $csvFile->Fetch();
	if ($CUR_FILE_POS<=0 && $bFirstHeaderTmp)
	{
		$arRes = $csvFile->Fetch();
	}

	$bs = new CIBlockSection;
	$el = new CIBlockElement;
	$bWasIterations = false;
	
	if ($arRes)
	{
		$bWasIterations = true;
		
		if ($bFirstLoadStep)
		{
			$tmpid = md5(uniqid(""));
			$line_num = 0;
			$correct_lines = 0;
			$error_lines = 0;
			$killed_lines = 0;
			$arIBlockProperty = array();
			$arIBlockPropertyValue = array();
			$bThereIsGroups = False;
			$bDeactivationStarted = False;
			$arProductGroups = array();
		}

		// Prepare load arrays
		global $defCatalogAvailGroupFields, $arCatalogAvailGroupFields;
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

		// Prepare load arrays
		global $defCatalogAvailProdFields, $arCatalogAvailProdFields;
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

		// Prepare load arrays
		global $defCatalogAvailPriceFields, $arCatalogAvailPriceFields;
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

		// Prepare load arrays
		global $defCatalogAvailValueFields, $arCatalogAvailValueFields;
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

		// main
		do
		{
			$strErrorR = "";
			$line_num++;

			//
			$arGroupsTmp = array();
			for ($i = 0; $i < $NUM_CATALOG_LEVELS; $i++)
			{
				$arGroupsTmp1 = array();
				foreach ($arAvailGroupFields_names as $key => $value)
				{
					$ind = -1;
					for ($i_tmp = 0; $i_tmp < $NUM_FIELDS; $i_tmp++)
					{
						if (${"field_".$i_tmp} == $key.$i)
						{
							$ind = $i_tmp;
							break;
						}
					}

					if ($ind>-1)
					{
						$arGroupsTmp1[$value["field"]] = Trim($arRes[$ind]);
						$bThereIsGroups = True;
					}
				}
				$arGroupsTmp[] = $arGroupsTmp1;
			}

			//
			$i = count($arGroupsTmp)-1;
			while ($i>=0)
			{
				foreach ($arAvailGroupFields_names as $key => $value)
				{
					if ($value["important"]=="Y" && strlen($arGroupsTmp[$i][$value["field"]])>0)
					{
						break 2;
					}
				}
				unset($arGroupsTmp[$i]);
				$i--;
			}

			//
			for ($i = 0; $i < count($arGroupsTmp); $i++)
			{
				if (strlen($arGroupsTmp[$i]["NAME"])<=0) $arGroupsTmp[$i]["NAME"] = GetMessage("CATI_NOMAME");
				$arGroupsTmp[$i]["TMP_ID"] = $tmpid;
			}

			//
			$LAST_GROUP_CODE = 0;
			for ($i = 0; $i < count($arGroupsTmp); $i++)
			{
				$arFilter = array("IBLOCK_ID"=>$IBLOCK_ID);
				if (strlen($arGroupsTmp[$i]["XML_ID"])>0)
				{
					$arFilter["XML_ID"] = $arGroupsTmp[$i]["XML_ID"];
				}
				elseif (strlen($arGroupsTmp[$i]["NAME"])>0)
				{
					$arFilter["NAME"] = $arGroupsTmp[$i]["NAME"];
				}

				if ($LAST_GROUP_CODE>0)
				{
					$arFilter["SECTION_ID"] = $LAST_GROUP_CODE;
					$arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = $LAST_GROUP_CODE;
				}
				else
				{
					$arFilter["SECTION_ID"] = 0;
					$arGroupsTmp[$i]["IBLOCK_SECTION_ID"] = false;
				}

				$res = CIBlockSection::GetList(array(), $arFilter);
				if ($arr = $res->Fetch())
				{
					$LAST_GROUP_CODE = $arr["ID"];
					$res = $bs->Update($LAST_GROUP_CODE, $arGroupsTmp[$i]);
				}
				else
				{
					$arGroupsTmp[$i]["IBLOCK_ID"] = $IBLOCK_ID;
					if ($arGroupsTmp[$i]["ACTIVE"]!="N") $arGroupsTmp[$i]["ACTIVE"] = "Y";
					$LAST_GROUP_CODE = $bs->Add($arGroupsTmp[$i]);
				}
			}

			//CIBlockSection::ReSort($IBLOCK_ID);

			//
			$arLoadProductArray = Array(
				"MODIFIED_BY"		=>	$USER->GetID(),
				"IBLOCK_ID"			=>	$IBLOCK_ID,
				"TMP_ID"				=> $tmpid
				);
			foreach ($arAvailProdFields_names as $key => $value)
			{

				$ind = -1;
				for ($i_tmp = 0; $i_tmp < $NUM_FIELDS; $i_tmp++)
				{
					if (${"field_".$i_tmp} == $key)
					{
						$ind = $i_tmp;
						break;
					}
				}

				if ($ind>-1)
				{
					$arLoadProductArray[$value["field"]] = Trim($arRes[$ind]);
				}
			}

			$arFilter = array("IBLOCK_ID"=>$IBLOCK_ID);
			if (strlen($arLoadProductArray["XML_ID"])>0)
			{
				$arFilter["XML_ID"] = $arLoadProductArray["XML_ID"];
			}
			else
			{
				if (strlen($arLoadProductArray["NAME"])>0)
				{
					$arFilter["NAME"] = $arLoadProductArray["NAME"];
				}
				else
				{
					$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".GetMessage("CATI_NOIDNAME")."<br>";
				}
			}

			if (strlen($strErrorR)<=0)
			{
				if (is_set($arLoadProductArray, "PREVIEW_PICTURE"))
				{
					$bFilePres = False;
					if (strlen($arLoadProductArray["PREVIEW_PICTURE"])>0)
					{
						$strPictureName = $arLoadProductArray["PREVIEW_PICTURE"];
						if (file_exists($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName))
						{
							$arLoadProductArray["PREVIEW_PICTURE"] = array();
							$arLoadProductArray["PREVIEW_PICTURE"]["name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
							$arImageProps = getimagesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
							if ($arImageProps[2]==1) $arLoadProductArray["PREVIEW_PICTURE"]["type"] = "image/gif";
							elseif ($arImageProps[2]==2) $arLoadProductArray["PREVIEW_PICTURE"]["type"] = "image/jpeg";
							elseif ($arImageProps[2]==3) $arLoadProductArray["PREVIEW_PICTURE"]["type"] = "image/png";
							$arLoadProductArray["PREVIEW_PICTURE"]["size"] = filesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
							$arLoadProductArray["PREVIEW_PICTURE"]["tmp_name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
							$bFilePres = True;
						}
					}

					if (!$bFilePres)
					{
						unset($arLoadProductArray["PREVIEW_PICTURE"]);
					}
				}

				if (is_set($arLoadProductArray, "DETAIL_PICTURE"))
				{
					$bFilePres = False;
					if (strlen($arLoadProductArray["DETAIL_PICTURE"])>0)
					{
						$strPictureName = $arLoadProductArray["DETAIL_PICTURE"];
						if (file_exists($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName))
						{
							$arLoadProductArray["DETAIL_PICTURE"] = array();
							$arLoadProductArray["DETAIL_PICTURE"]["name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
							$arImageProps = getimagesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
							if ($arImageProps[2]==1) $arLoadProductArray["DETAIL_PICTURE"]["type"] = "image/gif";
							elseif ($arImageProps[2]==2) $arLoadProductArray["DETAIL_PICTURE"]["type"] = "image/jpeg";
							elseif ($arImageProps[2]==3) $arLoadProductArray["DETAIL_PICTURE"]["type"] = "image/png";
							$arLoadProductArray["DETAIL_PICTURE"]["size"] = filesize($_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName);
							$arLoadProductArray["DETAIL_PICTURE"]["tmp_name"] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES."/".$strPictureName;
							$bFilePres = True;
						}
					}

					if (!$bFilePres)
					{
						unset($arLoadProductArray["DETAIL_PICTURE"]);
					}
				}

				$res = CIBlockElement::GetList(Array(), $arFilter);
				if ($arr = $res->Fetch())
				{
					$PRODUCT_ID = $arr["ID"];
					if (is_set($arLoadProductArray, "PREVIEW_PICTURE") && IntVal($arr["PREVIEW_PICTURE"])>0)
					{
						$arLoadProductArray["PREVIEW_PICTURE"]["old_file"] = $arr["PREVIEW_PICTURE"];
					}
					if (is_set($arLoadProductArray, "DETAIL_PICTURE") && IntVal($arr["DETAIL_PICTURE"])>0)
					{
						$arLoadProductArray["DETAIL_PICTURE"]["old_file"] = $arr["DETAIL_PICTURE"];
					}
					if ($bThereIsGroups)
					{
						$LAST_GROUP_CODE_tmp = (($LAST_GROUP_CODE > 0) ? $LAST_GROUP_CODE : false);
						if (!is_array($arProductGroups[$PRODUCT_ID]) || !in_array($LAST_GROUP_CODE_tmp, $arProductGroups[$PRODUCT_ID]))
						{
							$arProductGroups[$PRODUCT_ID][] = $LAST_GROUP_CODE_tmp;
						}
						$arLoadProductArray["IBLOCK_SECTION"] = $arProductGroups[$PRODUCT_ID];
					}
					$res = $el->Update($PRODUCT_ID, $arLoadProductArray, false, false);
				}
				else
				{
					if ($bThereIsGroups)
					{
						$arLoadProductArray["IBLOCK_SECTION"] = (($LAST_GROUP_CODE>0) ? $LAST_GROUP_CODE : false);
					}
					if ($arLoadProductArray["ACTIVE"] != "N")
						$arLoadProductArray["ACTIVE"] = "Y";
					$PRODUCT_ID = $el->Add($arLoadProductArray);
					if ($bThereIsGroups)
					{
						$arProductGroups[$PRODUCT_ID][] = (($LAST_GROUP_CODE > 0) ? $LAST_GROUP_CODE : false);
					}
					$res = ($PRODUCT_ID > 0);
				}

				if (!$res)
				{
					$strErrorR .= GetMessage("CATI_LINE_NO")." ".$line_num.". ".GetMessage("CATI_ERROR_LOADING")." ".$el->LAST_ERROR."<br>";
				}
			}

			if (strlen($strErrorR)<=0)
			{
				$PROP = array();
				for ($i = 0; $i < $NUM_FIELDS; $i++)
				{
					if (substr(${"field_".$i}, 0, 7) == "IP_PROP")
					{
						$cur_prop_id = IntVal(substr(${"field_".$i}, 7));
						if (!is_set($arIBlockProperty, $cur_prop_id))
						{
							$res1 = CIBlockProperty::GetByID($cur_prop_id, $IBLOCK_ID);
							if ($arRes1 = $res1->Fetch())
								$arIBlockProperty[$cur_prop_id] = $arRes1;
							else
								$arIBlockProperty[$cur_prop_id] = array();
						}
						if (is_array($arIBlockProperty[$cur_prop_id]) && count($arIBlockProperty[$cur_prop_id])>0)
						{
							if ($arIBlockProperty[$cur_prop_id]["PROPERTY_TYPE"]=="L")
							{
								$res2 = CIBlockProperty::GetPropertyEnum(
										$cur_prop_id,
										array(),
										array("IBLOCK_ID" => $IBLOCK_ID, "VALUE" => Trim($arRes[$i]))
									);
								if ($arRes2 = $res2->Fetch())
								{
									$arRes[$i] = $arRes2["ID"];
								}
								else
								{
									$arRes[$i] = CIBlockPropertyEnum::Add(
											array(
													"PROPERTY_ID" => $cur_prop_id,
													"VALUE" => Trim($arRes[$i]),
													"TMP_ID" => $tmpid
												)
										);
								}
							}
							elseif ($arIBlockProperty[$cur_prop_id]["PROPERTY_TYPE"]=="F")
							{
								$arRes[$i] = $_SERVER["DOCUMENT_ROOT"].$PATH2IMAGE_FILES.$arRes[$i];

								if (file_exists($arRes[$i]))
									$arRes[$i] = CFile::MakeFileArray($arRes[$i]);
								else
									$arRes[$i] = '';
							}

							if ($arIBlockProperty[$cur_prop_id]["MULTIPLE"]=="Y")
							{
								if (
									!isset($arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id])
									|| !is_array($arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id])
									|| !in_array(Trim($arRes[$i]), $arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id])
								)
									$arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id][] = is_array($arRes[$i]) ? $arRes[$i] : Trim($arRes[$i]);

								$PROP[$cur_prop_id] = $arIBlockPropertyValue[$PRODUCT_ID][$cur_prop_id];
							}
							else
							{
								$PROP[$cur_prop_id][] = is_array($arRes[$i]) ? $arRes[$i] : Trim($arRes[$i]);
							}
						}
					}
				}

				CIBlockElement::SetPropertyValuesEx($PRODUCT_ID, $IBLOCK_ID, $PROP);
				// foreach ($PROP as $keyip => $valueip)
				// {
					// CIBlockElement::SetPropertyValuesEx($PRODUCT_ID, $IBLOCK_ID, $valueip, $keyip);
				// }

				CIBlockElement::UpdateSearch($PRODUCT_ID);
			}

			if (strlen($strErrorR) <= 0 && $bIBlockIsCatalog)
			{
				//
				$arLoadOfferArray = Array(
					"ID" => $PRODUCT_ID
				);
				foreach ($arAvailPriceFields_names as $key => $value)
				{
					$ind = -1;
					for ($i_tmp = 0; $i_tmp < $NUM_FIELDS; $i_tmp++)
					{
						if (${"field_".$i_tmp} == $key)
						{
							$ind = $i_tmp;
							break;
						}
					}

					if ($ind > -1)
						$arLoadOfferArray[$value["field"]] = Trim($arRes[$ind]);
				}

				CCatalogProduct::Add($arLoadOfferArray);

				$quantityFrom = 0;
				$quantityTo = 0;
				for ($j = 0; $j < $NUM_FIELDS; $j++)
				{
					if (${"field_".$j} == "CV_QUANTITY_FROM")
						$quantityFrom = IntVal($arRes[$j]);
					elseif (${"field_".$j} == "CV_QUANTITY_TO")
						$quantityTo = IntVal($arRes[$j]);
				}

				$arFields = array();
				for ($j = 0; $j < $NUM_FIELDS; $j++)
				{
					foreach ($arAvailValueFields_names as $key => $value)
					{
						if (substr(${"field_".$j}, 0, strlen($key) + 1) == $key."_")
						{
							if (!isset($arFields[IntVal(substr(${"field_".$j}, strlen($key) + 1))]))
							{
								$arFields[IntVal(substr(${"field_".$j}, strlen($key) + 1))] = array(
										"PRODUCT_ID" => $PRODUCT_ID,
										"CATALOG_GROUP_ID" => IntVal(substr(${"field_".$j}, strlen($key) + 1)),
										"QUANTITY_FROM" => (($quantityFrom > 0) ? $quantityFrom : False),
										"QUANTITY_TO" => (($quantityTo > 0) ? $quantityTo : False)
									);
							}

							$arFields[IntVal(substr(${"field_".$j}, strlen($key) + 1))][$value["field"]] = Trim($arRes[$j]);
						}
					}
				}

				foreach ($arFields as $key => $value)
				{
					if (isset($value["PRICE"]))
					{
						$value["PRICE"] = str_replace(",", ".", Trim($value["PRICE"]));
						$value["PRICE"] = preg_replace("/[^\d.]/i", "", $value["PRICE"]);
						$value["PRICE"] = DoubleVal($value["PRICE"]);
					}
					else
					{
						$value["PRICE"] = False;
					}
					$value["TMP_ID"] = $tmpid;

					$res = CPrice::GetList(
							array(),
							array(
									"PRODUCT_ID" => $PRODUCT_ID,
									"CATALOG_GROUP_ID" => $key,
									"QUANTITY_FROM" => $quantityFrom,
									"QUANTITY_TO" => $quantityTo
								)
						);
					if ($arr = $res->Fetch())
					{
						CPrice::Update($arr["ID"], $value);
					}
					else
					{
						CPrice::Add($value);
					}
				}
			}

			if (strlen($strErrorR)<=0)
			{
				$correct_lines++;
			}
			else
			{
				$error_lines++;
				$strImportErrorMessage .= $strErrorR;
			}

			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time))) break;
		}
		while ($arRes = $csvFile->Fetch());
		
		CIBlockSection::ReSort($IBLOCK_ID);
	}

//////////////////////////////
// start additional actions //
//////////////////////////////
	
	// activate 'in-file' sections
	if ($bAllLinesLoaded && $bThereIsGroups && $inFileAction == 'A' && !$bDeactivationStarted)
	{
		$res = CIBlockSection::GetList(
			array(),
			array("IBLOCK_ID" => $IBLOCK_ID, "TMP_ID" => $tmpid, "ACTIVE" => "N")
		);
		while($arr = $res->Fetch())
		{
			$bs->Update($arr["ID"], Array("NAME"=>$arr["NAME"], "ACTIVE" => "Y"));
			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time))) break;
		}
	}
	
	// activate 'in-file' elements
	if ($bAllLinesLoaded && $inFileAction=="A" && !$bDeactivationStarted)
	{
		$res = CIBlockElement::GetList(
			array(),
			array("IBLOCK_ID" => $IBLOCK_ID, "TMP_ID" => $tmpid, "ACTIVE" => "N")
		);
		while($arr = $res->Fetch())
		{
			$el->Update($arr["ID"], Array("ACTIVE" => "Y"), false, false);
			
			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time))) break;
		}
	}

	// update or delete 'not-in-file sections'
	if ($bAllLinesLoaded && $outFileAction != 'F' && $bThereIsGroups)
	{
		$res = CIBlockSection::GetList(
			array(),
			array("IBLOCK_ID" => $IBLOCK_ID, "!TMP_ID" => $tmpid)
		);
		
		while($arr = $res->Fetch())
		{
			if ($outFileAction=="D")
			{
				CIBlockSection::Delete($arr["ID"]);
			}
			elseif ($outFileAction=="F")
			{
			}
			else // H
			{
				$bDeactivationStarted = true;
				$bs->Update($arr["ID"], Array("NAME"=>$arr["NAME"], "ACTIVE" => "N", "TMP_ID" => $tmpid));
			}
			
			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time))) break;
		}
	}
	
	// update or delete 'not-in-file' elements
	if ($bAllLinesLoaded && $outFileAction != "F")
	{
		$res = CIBlockElement::GetList(
			array(),
			array("IBLOCK_ID" => $IBLOCK_ID, "!TMP_ID" => $tmpid)
		);
		while($arr = $res->Fetch())
		{
			if ($outFileAction=="D")
			{
				CIBlockElement::Delete($arr["ID"], "Y", "N");
				$killed_lines++;
			}
			elseif ($outFileAction=="F")
			{
			
			}
			else // H
			{
				$bDeactivationStarted = true;
				$el->Update($arr["ID"], Array("ACTIVE" => "N", "TMP_ID" => $tmpid), false, false);
				$killed_lines++;
			}
			
			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time))) break;
		}
	}
	
	// delete 'not-in-file' element prices
	if ($bAllLinesLoaded && $outFileAction=="D")
	{
		$res = CPrice::GetList(
			array(),
			array("ELEMENT_IBLOCK_ID" => $IBLOCK_ID, "!TMP_ID" => $tmpid),
			false,
			false,
			array("ID")
		);
		
		while($arr = $res->Fetch())
		{
			CPrice::Delete($arr["ID"]);
			
			if (!($bAllLinesLoaded = CSVCheckTimeout($max_execution_time))) break;
		}
	}
	
	if (!$bAllLinesLoaded)
	{
		$bAllDataLoaded = False;

		$INTERNAL_VARS_LIST = "tmpid,line_num,correct_lines,error_lines,killed_lines,arIBlockProperty,bThereIsGroups,arProductGroups,arIBlockPropertyValue,bDeactivationStarted";
		$SETUP_VARS_LIST = "IBLOCK_ID,URL_DATA_FILE,fields_type,first_names_r,delimiter_r,delimiter_other_r,first_names_f,metki_f,PATH2IMAGE_FILES,outFileAction,inFileAction,max_execution_time";
		for ($i = 0; $i < $NUM_FIELDS; $i++)
			$SETUP_VARS_LIST .= ",field_".$i;
		$CUR_FILE_POS = $csvFile->GetPos();
	}
}

if ($bTmpUserCreated) 
{
	unset($USER);
	
	if (isset($USER_TMP))
	{
		$USER = $USER_TMP;
		unset($USER_TMP);
	}
}
?>