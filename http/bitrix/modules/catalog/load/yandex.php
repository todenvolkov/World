<?
set_time_limit(0);

// глупость конечно, но выхода нет
global $USER;
$USER = new CUser;

if (!function_exists("yandex_replace_special"))
{
	function yandex_replace_special($arg)
	{
		if (in_array($arg[0], array("&quot;", "&amp;", "&lt;", "&gt;")))
			return $arg[0];
		else
			return " ";
	}
}

if (!function_exists("yandex_text2xml"))
{
	function yandex_text2xml($text, $bHSC = false)
	{
		if ($bHSC)
			$text = htmlspecialchars($text);
		$text = ereg_replace("[\x1-\x8\xB-\xC\xE-\x1F]", "", $text);
		$text = ereg_replace("'", "&apos;", $text);
		return $text; 
	}
}

$strAll = '<?if (strlen($_GET["referer1"])<=0) $_GET["referer1"] = "yandext"?>';
$strAll.= '<?echo "<?xml version=\"1.0\" encoding=\"windows-1251\"?>"?>';
$strAll.= "\n<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n";
$strAll.= "<yml_catalog date=\"".Date("Y-m-d H:i")."\">\n";
$strAll.= "<shop>\n";
$strAll.= "<name>".htmlspecialchars(COption::GetOptionString("main", "site_name", ""))."</name>\n";
$strAll.= "<company>".htmlspecialchars(COption::GetOptionString("main", "site_name", ""))."</company>\n";
$strAll.= "<url>http://".htmlspecialchars(COption::GetOptionString("main", "server_name", ""))."</url>\n";

//*****************************************//

$arCurrencyAllowed = array('RUR', 'RUB', 'USD', 'EUR', 'UAH');
$db_acc = CCurrency::GetList(($by="sort"), ($order="asc"));
$strTmp = "<currencies>\n";
while ($arAcc = $db_acc->Fetch())
{
	if (in_array($arAcc['CURRENCY'], $arCurrencyAllowed))
		$strTmp .= "<currency id=\"".$arAcc["CURRENCY"]."\" rate=\"".(CCurrencyRates::ConvertCurrency(1, $arAcc["CURRENCY"], "RUR"))."\"/>\n";
}
$strTmp.= "</currencies>\n";

$strAll.= $strTmp;

//*****************************************//

$arSelect = array("ID", "LID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "ACTIVE", "ACTIVE_FROM", "ACTIVE_TO", "NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT", "PREVIEW_TEXT_TYPE", "DETAIL_PICTURE", "LANG_DIR", "DETAIL_PAGE_URL");
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

$arSiteServers = array();

$db_catalog_list = CCatalog::GetList(($b="SORT"), ($o="ASC"), Array("YANDEX_EXPORT" => "Y"));
while ($arCatalog_list = $db_catalog_list->Fetch())
{
	$filter = Array("IBLOCK_ID"=>IntVal($arCatalog_list["IBLOCK_ID"]), "ACTIVE"=>"Y", "IBLOCK_ACTIVE"=>"Y", "GLOBAL_ACTIVE"=>"Y");
	$db_acc = CIBlockSection::GetList(array("left_margin"=>"asc"), $filter);

	$arAvailGroups = array();
	while ($arAcc = $db_acc->Fetch())
	{
		$strTmpCat.= "<category id=\"".$arAcc["ID"]."\"".(IntVal($arAcc["IBLOCK_SECTION_ID"])>0?" parentId=\"".$arAcc["IBLOCK_SECTION_ID"]."\"":"").">".yandex_text2xml($arAcc["NAME"], true)."</category>\n";
		$arAvailGroups[] = IntVal($arAcc["ID"]);
	}

	//*****************************************//

	$filter = Array("IBLOCK_ID"=>IntVal($arCatalog_list["IBLOCK_ID"]), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
	$res = CIBlockElement::GetList(array(), $filter, false, false, $arSelect);
	$db_acc = new CIBlockResult($res);

	$total_sum=0;
	$is_exists=false;
	$cnt=0;

	while ($arAcc = $db_acc->GetNext())
	{
		if (!array_key_exists($arAcc['LID'], $arSiteServers))
		{
			$rsSite = CSite::GetList(($b="sort"), ($o="asc"), array("LID" => $arAcc["LID"]));
			if($arSite = $rsSite->Fetch())
				$arAcc["SERVER_NAME"] = $arSite["SERVER_NAME"];
			if(strlen($arAcc["SERVER_NAME"])<=0 && defined("SITE_SERVER_NAME"))
				$arAcc["SERVER_NAME"] = SITE_SERVER_NAME;
			if(strlen($arAcc["SERVER_NAME"])<=0)
				$arAcc["SERVER_NAME"] = COption::GetOptionString("main", "server_name", "");
				
			$arSiteServers[$arAcc['LID']] = $arAcc['SERVER_NAME'];
		}
		else
		{
			$arAcc['SERVER_NAME'] = $arSiteServers[$arAcc['LID']];
		}	
	
		$str_QUANTITY = DoubleVal($arAcc["CATALOG_QUANTITY"]);
		$str_QUANTITY_TRACE = $arAcc["CATALOG_QUANTITY_TRACE"];
		if (($str_QUANTITY <= 0) && ($str_QUANTITY_TRACE == "Y"))
			$str_AVAILABLE = ' available="false"';
		else
			$str_AVAILABLE = ' available="true"';


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
			$strTmpOff_tmp.= "<categoryId>".$ar_res1["ID"]."</categoryId>\n";
			if ($bNoActiveGroup && in_array(IntVal($ar_res1["ID"]), $arAvailGroups))
			{
				$bNoActiveGroup = False;
			}
		}
		if ($bNoActiveGroup) continue;

		if (strlen($arAcc['DETAIL_PAGE_URL']) <= 0) $arAcc['DETAIL_PAGE_URL'] = '/';
		else $arAcc['DETAIL_PAGE_URL'] = str_replace(' ', '%20', $arAcc['DETAIL_PAGE_URL']);

		$strTmpOff.= "<offer id=\"".$arAcc["ID"]."\"".$str_AVAILABLE.">\n";
		$strTmpOff.= "<url>http://".$arAcc['SERVER_NAME'].htmlspecialchars($arAcc["DETAIL_PAGE_URL"]).(strstr($arAcc['DETAIL_PAGE_URL'], '?') === false ? '?' : '&amp;')."r1=<?echo \$_GET[\"referer1\"] ?>&amp;r2=<?echo \$_GET[\"referer2\"] ?></url>\n";

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
				$strTmpOff.="<picture>http://".$arAcc['SERVER_NAME'].$strFile."</picture>\n";
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

}

$strAll.= "<categories>\n";
$strAll.= $strTmpCat;
$strAll.= "</categories>\n";

$strAll.= "<offers>\n";
$strAll.= $strTmpOff;
$strAll.= "</offers>\n";

$strAll.= "</shop>\n";
$strAll.= "</yml_catalog>\n";

if ($fp = @fopen($_SERVER["DOCUMENT_ROOT"].(COption::GetOptionString("catalog", "export_default_path", "/upload/"))."/yandex.php", 'wb'))
{
	@fwrite($fp, $strAll);
	@fclose($fp);
}

unset($USER);
?>