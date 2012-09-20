<?
//<title>Туктук Яндекс</title>
set_time_limit(0);

global $USER;
$bTmpUserCreated = False;
if (!isset($USER))
{
	$bTmpUserCreated = True;
	$USER = new CUser;
}

function yandex_replace_special($arg)
{
	if (in_array($arg[0], array("&quot;", "&amp;", "&lt;", "&gt;")))
		return $arg[0];
	else
		return " ";
}

function yandex_text2xml($text, $bHSC = false)
{
	if ($bHSC)
		$text = htmlspecialchars($text);
	$text = ereg_replace("[\x1-\x8\xB-\xC\xE-\x1F]", "", $text);
	$text = ereg_replace("'", "&apos;", $text);
	return $text; 
}

$strExportErrorMessage = "";

$IBLOCK_ID = IntVal($IBLOCK_ID);
$db_iblock = CIBlock::GetByID($IBLOCK_ID);
if (!($ar_iblock = $db_iblock->Fetch()))
	$strExportErrorMessage .= "Information block #".$IBLOCK_ID." does not exist.\n";

if (strlen($strExportErrorMessage)<=0)
{
	$bAllSections = False;
	$arSections = array();
	if (is_array($V))
	{
		foreach ($V as $key => $value)
		{
			if (trim($value)=="0")
			{
				$bAllSections = True;
				break;
			}
			if (IntVal($value)>0)
			{
				$arSections[] = IntVal($value);
			}
		}
	}

	if (!$bAllSections && count($arSections)<=0)
		$strExportErrorMessage .= "Section list is not set.\n";
}

if (!$fp = @fopen($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME, "wb"))
{
	$strExportErrorMessage .= "Can not open \"".$_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME."\" file for writing.\n";
}
else
{
	if (!@fwrite($fp, '<?if (strlen($_GET["referer1"])<=0) $_GET["referer1"] = "yandext"?>'))
	{
		$strExportErrorMessage .= "Can not write in \"".$_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME."\" file.\n";
		@fclose($fp);
	}
}

if (strlen($strExportErrorMessage)<=0)
{
	@fwrite($fp, '<?echo "<?xml version=\"1.0\" encoding=\"windows-1251\"?>"?>');
	@fwrite($fp, "\n<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n");
	@fwrite($fp, "<yml_catalog date=\"".Date("Y-m-d H:i")."\">\n");
	@fwrite($fp, "<shop>\n");
	@fwrite($fp, "<name>".htmlspecialchars(COption::GetOptionString("main", "site_name", ""))."</name>\n");
	@fwrite($fp, "<company>".htmlspecialchars(COption::GetOptionString("main", "site_name", ""))."</company>\n");
	@fwrite($fp, "<url>http://".htmlspecialchars(COption::GetOptionString("main", "server_name", ""))."</url>\n");

	$db_acc = CCurrency::GetList(($by="sort"), ($order="asc"));
	$strTmp = "<currencies>\n";
	while ($arAcc = $db_acc->Fetch())
	{
		if ($arAcc["CURRENCY"]=="RUR" || $arAcc["CURRENCY"]=="USD")
		{
			$strTmp.= "<currency id=\"".$arAcc["CURRENCY"]."\" rate=\"".(CCurrencyRates::ConvertCurrency(1, $arAcc["CURRENCY"], "RUR"))."\"/>\n";
		}
	}
	$strTmp.= "</currencies>\n";

	@fwrite($fp, $strTmp);

	//*****************************************//

	$arSelect = array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO", "NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT", "PREVIEW_TEXT_TYPE", "DETAIL_PICTURE", "LANG_DIR", "DETAIL_PAGE_URL");
	$db_res = CCatalogGroup::GetGroupsList(array("GROUP_ID"=>2));
	$arPTypes = array();
	while ($ar_res = $db_res->Fetch())
	{
		if (!in_array($ar_res["CATALOG_GROUP_ID"], $arPTypes))
		{
			$arPTypes[] = $ar_res["CATALOG_GROUP_ID"];
			$arSelect[] = "CATALOG_GROUP_".$ar_res["CATALOG_GROUP_ID"];
		}
	}

	$strTmpCat = "";
	$strTmpOff = "";

	$arAvailGroups = array();
	if (!$bAllSections)
	{
		for ($i = 0; $i < count($arSections); $i++)
		{
			$filter_tmp = $filter;
			$db_res = CIBlockSection::GetNavChain($IBLOCK_ID, $arSections[$i]);
			$curLEFT_MARGIN = 0;
			$curRIGHT_MARGIN = 0;
			while ($ar_res = $db_res->Fetch())
			{
				$curLEFT_MARGIN = IntVal($ar_res["LEFT_MARGIN"]);
				$curRIGHT_MARGIN = IntVal($ar_res["RIGHT_MARGIN"]);
				$arAvailGroups[] = array(
					"ID" => IntVal($ar_res["ID"]),
					"IBLOCK_SECTION_ID" => IntVal($ar_res["IBLOCK_SECTION_ID"]),
					"NAME" => $ar_res["NAME"]
					);
			}

			$filter = Array("IBLOCK_ID"=>$IBLOCK_ID, ">LEFT_MARGIN"=>$curLEFT_MARGIN, "<RIGHT_MARGIN"=>$curRIGHT_MARGIN, "ACTIVE"=>"Y", "IBLOCK_ACTIVE"=>"Y", "GLOBAL_ACTIVE"=>"Y");
			$db_res = CIBlockSection::GetList(array("left_margin"=>"asc"), $filter);
			while ($ar_res = $db_res->Fetch())
			{
				$arAvailGroups[] = array(
					"ID" => IntVal($ar_res["ID"]),
					"IBLOCK_SECTION_ID" => IntVal($ar_res["IBLOCK_SECTION_ID"]),
					"NAME" => $ar_res["NAME"]
					);
			}
		}
		$cnt_arAvailGroups = count($arAvailGroups);
		for ($i = 0; $i < $cnt_arAvailGroups-1; $i++)
		{
			if (!isset($arAvailGroups[$i])) continue;

			for ($j = $i + 1; $j < $cnt_arAvailGroups; $j++)
			{
				if (!isset($arAvailGroups[$j])) continue;

				if ($arAvailGroups[$i]["ID"]==$arAvailGroups[$j]["ID"])
				{
					unset($arAvailGroups[$j]);
				}
			}
		}
	}
	else
	{
		$filter = Array("IBLOCK_ID"=>$IBLOCK_ID, "ACTIVE"=>"Y", "IBLOCK_ACTIVE"=>"Y", "GLOBAL_ACTIVE"=>"Y");
		$db_res = CIBlockSection::GetList(array("left_margin"=>"asc"), $filter);
		while ($ar_res = $db_res->Fetch())
		{
			$arAvailGroups[] = array(
				"ID" => IntVal($ar_res["ID"]),
				"IBLOCK_SECTION_ID" => IntVal($ar_res["IBLOCK_SECTION_ID"]),
				"NAME" => $ar_res["NAME"]
				);
		}
	}

	$arSectionIDs = array();
	foreach ($arAvailGroups as $key => $value)
	{
		$strTmpCat.= "<category id=\"".$value["ID"]."\"".(IntVal($value["IBLOCK_SECTION_ID"])>0?" parentId=\"".$value["IBLOCK_SECTION_ID"]."\"":"").">".yandex_text2xml($value["NAME"], true)."</category>\n";
		$arSectionIDs[] = $value["ID"];
	}

	//*****************************************//

	$filter = Array("IBLOCK_ID"=>$IBLOCK_ID, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
	if (!$bAllSections)
	{
		$filter["INCLUDE_SUBSECTIONS"] = "Y";
		$filter["SECTION_ID"] = $arSectionIDs;
	}
	$res = CIBlockElement::GetList(array(), $filter, false, false, $arSelect);
	$db_acc = new CIBlockResult($res);

	$total_sum = 0;
	$is_exists = false;
	$cnt = 0;

	while ($arAcc = $db_acc->GetNext())
	{
		$str_QUANTITY = IntVal($arAcc["CATALOG_QUANTITY"]);
		if ($str_QUANTITY<=0) $str_QUANTITY=1;

		$minPrice = 0;
		$minPriceRUR = 0;
		$minPriceGroup = 0;
		$minPriceCurrency = "";
		for ($i = 0; $i < count($arPTypes); $i++)
		{
			if (strlen($arAcc["CATALOG_CURRENCY_".$arPTypes[$i]])<=0) continue;

			$tmpPrice = CCurrencyRates::ConvertCurrency($arAcc["CATALOG_PRICE_".$arPTypes[$i]], $arAcc["CATALOG_CURRENCY_".$arPTypes[$i]], "RUR");
			if ($minPriceRUR<=0 || $minPriceRUR>$tmpPrice)
			{
				$minPriceRUR = $tmpPrice;
				$minPrice = $arAcc["CATALOG_PRICE_".$arPTypes[$i]];
				$minPriceGroup = $arPTypes[$i];
				$minPriceCurrency = $arAcc["CATALOG_CURRENCY_".$arPTypes[$i]];
				if ($minPriceCurrency!="USD" && $minPriceCurrency!="RUR")
				{
					$minPriceCurrency = "RUR";
					$minPrice = $tmpPrice;
				}
			}
		}

		if ($minPrice <= 0) continue;

		$bNoActiveGroup = True;
		$strTmpOff_tmp = "";
		$db_res1 = CIBlockElement::GetElementGroups($arAcc["ID"]);
		while ($ar_res1 = $db_res1->Fetch())
		{
			if (in_array(IntVal($ar_res1["ID"]), $arSectionIDs))
			{
				$strTmpOff_tmp.= "<categoryId>".$ar_res1["ID"]."</categoryId>\n";
				$bNoActiveGroup = False;
			}
		}
		if ($bNoActiveGroup) continue;

		$strTmpOff.= "<offer id=\"".$arAcc["ID"]."\">\n";
		$strTmpOff.= "<url>http://".COption::GetOptionString("main", "server_name", "").$arAcc["DETAIL_PAGE_URL"]."&amp;r1=<?echo \$_GET[\"referer1\"] ?>&amp;r2=<?echo \$_GET[\"referer2\"] ?></url>\n";

		$strTmpOff.= "<price>".$minPrice."</price>\n";
		$strTmpOff.= "<currencyId>".$minPriceCurrency."</currencyId>\n";

		$strTmpOff.= $strTmpOff_tmp;

		if (IntVal($arAcc["DETAIL_PICTURE"])>0 || IntVal($arAcc["PREVIEW_PICTURE"])>0)
		{
			$pictNo = IntVal($arAcc["DETAIL_PICTURE"]);
			if ($pictNo<=0) $pictNo = IntVal($arAcc["PREVIEW_PICTURE"]);

			$db_file = CFile::GetByID($pictNo);
			if ($ar_file = $db_file->Fetch())
			{
				$strFile = "/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$ar_file["SUBDIR"]."/".$ar_file["FILE_NAME"];
				$strFile = str_replace("//", "/", $strFile);
				$strTmpOff.="<picture>http://".COption::GetOptionString("main", "server_name", "").$strFile."</picture>\n";
			}
		}

		$strTmpOff.= "<name>".yandex_text2xml($arAcc["NAME"], true)."</name>\n";
		$strTmpOff.= 
			"<description>".
			yandex_text2xml(TruncateText(
				($arAcc["PREVIEW_TEXT_TYPE"]=="html"? 
				strip_tags(preg_replace_callback("'&[^;]*;'", "replace_special", $arAcc["~PREVIEW_TEXT"])) : $arAcc["PREVIEW_TEXT"]),
				255), true).
			"</description>\n";
		$strTmpOff.= "</offer>\n";
	}

	@fwrite($fp, "<categories>\n");
	@fwrite($fp, $strTmpCat);
	@fwrite($fp, "</categories>\n");

	@fwrite($fp, "<offers>\n");
	@fwrite($fp, $strTmpOff);
	@fwrite($fp, "</offers>\n");

	@fwrite($fp, "</shop>\n");
	@fwrite($fp, "</yml_catalog>\n");

	@fclose($fp);
}

if ($bTmpUserCreated) unset($USER);
?>