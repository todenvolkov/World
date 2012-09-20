<?
//<title>Froogle</title>

global $USER;
$bTmpUserCreated = False;
if (!isset($USER))
{
	$bTmpUserCreated = True;
	$USER = new CUser;
}

function PrepareString($str, $KillTags = False)
{
	if ($KillTags)
	{
		$str = strip_tags($str);
	}
	$str = str_replace("\r", "", str_replace("\n", "", str_replace("\t", " ", $str)));
	return $str;
}

$strExportErrorMessage = "";

if (CModule::IncludeModule("iblock") && CModule::IncludeModule("catalog"))
{
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

	if (strlen($strExportErrorMessage)<=0)
	{
		$arFilter = array("IBLOCK_ID" => $IBLOCK_ID, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
		if (!$bAllSections)
		{
			$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
			$arFilter["SECTION_ID"] = $arSections;
		}

		$arSelect = array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT", "PREVIEW_TEXT_TYPE", "DETAIL_PICTURE", "LANG_DIR", "DETAIL_PAGE_URL", "EXTERNAL_ID");
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

		$arSectionPaths = array();

		$SETUP_FILE_NAME = Rel2Abs("/", $SETUP_FILE_NAME);
		if (strtolower(substr($SETUP_FILE_NAME, strlen($SETUP_FILE_NAME)-4)) != ".txt")
			$SETUP_FILE_NAME .= ".txt";
		if ($GLOBALS["APPLICATION"]->GetFileAccessPermission($SETUP_FILE_NAME) < "W")
			$strExportErrorMessage .= str_replace("#FILE#", $SETUP_FILE_NAME, "У вас не достаточно прав для перезаписи файла #FILE#")."<br>";
	}

	if (strlen($strExportErrorMessage)<=0)
	{
		if (!$fp = @fopen($_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME, "wb"))
		{
			$strExportErrorMessage .= "Can not open \"".$_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME."\" file for writing.\n";
		}
		else
		{
			if (!@fwrite($fp, "product_url	name	description	image_url	category	price\n"))
			{
				$strExportErrorMessage .= "Can not write in \"".$_SERVER["DOCUMENT_ROOT"].$SETUP_FILE_NAME."\" file.\n";
				@fclose($fp);
			}
		}
	}

	if (strlen($strExportErrorMessage)<=0)
	{
		if (!($ar_usd_cur = CCurrency::GetByID("USD")))
		{
			$strExportErrorMessage .= "USD currency is not found.\n";
		}
	}

	if (strlen($strExportErrorMessage)<=0)
	{
		$db_elems = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
		while ($ar_elems = $db_elems->GetNext())
		{
			$strImage = "";
			$db_file = false;
			if (IntVal($ar_elems["DETAIL_PICTURE"])>0)
			{
				$db_file = CFile::GetByID($ar_elems["DETAIL_PICTURE"]);
				$ar_file = $db_file->Fetch();
			}
			if (!$ar_file && IntVal($ar_elems["PREVIEW_PICTURE"])>0)
			{
				$db_file = CFile::GetByID($ar_elems["PREVIEW_PICTURE"]);
				$ar_file = $db_file->Fetch();
			}
			if ($ar_file)
			{
				$strImage = "http://".COption::GetOptionString("main", "server_name", $SERVER_NAME)."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$ar_file["SUBDIR"]."/".$ar_file["FILE_NAME"];
				$strImage = str_replace("//", "/", $strImage);
			}

			if (!is_set($arSectionPaths, IntVal($ar_elems["IBLOCK_SECTION_ID"])))
			{
				$strCategory = $ar_iblock["NAME"];
				$sections_path = GetIBlockSectionPath($IBLOCK_ID, $ar_elems["IBLOCK_SECTION_ID"]);
				while ($arSection = $sections_path->GetNext())
				{
					if (strlen($strCategory)>0) $strCategory .= ">";
					$strCategory .= $arSection["NAME"];
				}
				$arSectionPaths[IntVal($ar_elems["IBLOCK_SECTION_ID"])] = PrepareString($strCategory);
			}

			$minPrice = 0;
			for ($i = 0; $i < count($arPTypes); $i++)
			{
				if (strlen($ar_elems["CATALOG_CURRENCY_".$arPTypes[$i]])<=0) continue;
				$tmpPrice = Round(CCurrencyRates::ConvertCurrency($ar_elems["CATALOG_PRICE_".$arPTypes[$i]], $ar_elems["CATALOG_CURRENCY_".$arPTypes[$i]], "USD"), 2);
				if ($minPrice<=0 || $minPrice>$tmpPrice)
				{
					$minPrice = $tmpPrice;
				}
			}

			if ($minPrice <= 0) continue;

			@fwrite($fp, "http://".
				COption::GetOptionString("main", "server_name", $SERVER_NAME).
				str_replace("//", "/", $ar_elems["DETAIL_PAGE_URL"]).
				"	".
				$ar_elems["~NAME"].
				"	".
				PrepareString($ar_elems["~PREVIEW_TEXT"], true).
				"	".
				$strImage.
				"	".
				$arSectionPaths[IntVal($ar_elems["IBLOCK_SECTION_ID"])].
				"	".
				$minPrice."\n");
		}
		@fclose($fp);
	}
}

if ($bTmpUserCreated) unset($USER);
?>