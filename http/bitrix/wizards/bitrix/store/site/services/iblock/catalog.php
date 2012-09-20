<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock"))
	return;

if(COption::GetOptionString("store", "wizard_installed", "N", WIZARD_SITE_ID) == "Y" && !WIZARD_INSTALL_DEMO_DATA)
	return;
	
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/catalog.xml"; 
$iblockXMLFilePrices = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/catalog_prices.xml"; 
$iblockCode = "furniture_".WIZARD_SITE_ID; 
$iblockType = "catalog"; 

$rsIBlock = CIBlock::GetList(array(), array("XML_ID" => $iblockCode, "TYPE" => $iblockType));
$iblockID = false; 
if ($arIBlock = $rsIBlock->Fetch())
{
	$iblockID = $arIBlock["ID"]; 
	if (WIZARD_INSTALL_DEMO_DATA)
	{
		CIBlock::Delete($arIBlock["ID"]); 
		$iblockID = false; 
	}
}

CModule::IncludeModule("catalog");
$dbResultList = CCatalogGroup::GetList(Array(), Array("CODE" => "BASE"));
if(!($dbResultList->Fetch()))
{
	$arFields = Array();
	$rsLanguage = CLanguage::GetList($by, $order, array());
	while($arLanguage = $rsLanguage->Fetch())
	{
		WizardServices::IncludeServiceLang("catalog.php", $arLanguage["ID"]);
		$arFields["USER_LANG"][$arLanguage["ID"]] = GetMessage("WIZ_PRICE_NAME");
	}
	$arFields["BASE"] = "Y";
	$arFields["SORT"] = 100;
	$arFields["NAME"] = "BASE";
	$arFields["USER_GROUP"] = Array(1, 2);
	$arFields["USER_GROUP_BUY"] = Array(1, 2);
	CCatalogGroup::Add($arFields);
}


if($iblockID == false)
{
	$permissions = Array(
			"1" => "X",
			"2" => "R"
		);
	$dbGroup = CGroup::GetList($by = "", $order = "", Array("STRING_ID" => "sale_administrator"));
	if($arGroup = $dbGroup -> Fetch())
	{
		$permissions[$arGroup["ID"]] = 'W';
	}
	$dbGroup = CGroup::GetList($by = "", $order = "", Array("STRING_ID" => "content_editor"));
	if($arGroup = $dbGroup -> Fetch())
	{
		$permissions[$arGroup["ID"]] = 'W';
	}
	$iblockID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFile,
		"furniture",
		$iblockType,
		WIZARD_SITE_ID,
		$permissions
	);
	$iblockID1 = WizardServices::ImportIBlockFromXML(
		$iblockXMLFilePrices,
		"furniture",
		$iblockType."_prices",
		WIZARD_SITE_ID,
		$permissions
	);

	if ($iblockID < 1)
		return;
	//IBlock fields
	$iblock = new CIBlock;
	$arFields = Array(
		"ACTIVE" => "Y",
		"FIELDS" => array ( 'IBLOCK_SECTION' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ), 'ACTIVE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'Y', ), 'ACTIVE_FROM' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'ACTIVE_TO' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SORT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'NAME' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ), 'PREVIEW_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ), 'PREVIEW_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'PREVIEW_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, ), ), 'DETAIL_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'DETAIL_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'CODE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ), 'TAGS' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_NAME' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ), 'SECTION_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ), 'SECTION_DESCRIPTION_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'SECTION_DESCRIPTION' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, ), ), 'SECTION_XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_CODE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ), ),
		"CODE" => "furniture", 
		"XML_ID" => $iblockCode,
		//"NAME" => "[".WIZARD_SITE_ID."] ".$iblock->GetArrayByID($iblockID, "NAME")
	);
	
	$iblock->Update($iblockID, $arFields);
}
else
{
	$arSites = array(); 
	$db_res = CIBlock::GetSite($iblockID);
	while ($res = $db_res->Fetch())
		$arSites[] = $res["LID"]; 
	if (!in_array(WIZARD_SITE_ID, $arSites))
	{
		$arSites[] = WIZARD_SITE_ID;
		$iblock = new CIBlock;
		$iblock->Update($iblockID, array("LID" => $arSites));
	}
}

$arProperty = Array();
$dbProperty = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID" => $iblockID));
while($arProp = $dbProperty->Fetch())
	$arProperty[$arProp["CODE"]] = $arProp["ID"];
	
$dbSite = CSite::GetByID(WIZARD_SITE_ID);
if($arSite = $dbSite -> Fetch())
	$lang = $arSite["LANGUAGE_ID"];
if(strlen($lang) <= 0)
	$lang = "ru";
WizardServices::IncludeServiceLang("catalog.php", $lang);
CUserOptions::SetOption("form", "form_element_".$iblockID, array ( 'tabs' => 'edit1--#--'.GetMessage("WZD_OPTION_CATALOG_1").'--,--ACTIVE--#--'.GetMessage("WZD_OPTION_CATALOG_2").'--,--NAME--#--'.GetMessage("WZD_OPTION_CATALOG_3").'--,--CODE--#--'.GetMessage("WZD_OPTION_CATALOG_4").'--,--DETAIL_PICTURE--#--'.GetMessage("WZD_OPTION_CATALOG_5").'--,--PROPERTY_'.$arProperty["SPECIALOFFER"].'--#--'.GetMessage("WZD_OPTION_CATALOG_10").'--,--PROPERTY_'.$arProperty["NEWPRODUCT"].'--#--'.GetMessage("WZD_OPTION_CATALOG_11").'--,--PROPERTY_'.$arProperty["SALELEADER"].'--#--'.GetMessage("WZD_OPTION_CATALOG_29").'--,--PROPERTY_'.$arProperty["SIZE"].'--#--'.GetMessage("WZD_OPTION_CATALOG_13").'--,--PROPERTY_'.$arProperty["S_SIZE"].'--#--'.GetMessage("WZD_OPTION_CATALOG_14").'--,--PROPERTY_'.$arProperty["ARTNUMBER"].'--#--'.GetMessage("WZD_OPTION_CATALOG_15").'--,--PROPERTY_'.$arProperty["MATERIAL"].'--#--'.GetMessage("WZD_OPTION_CATALOG_16").'--,--PROPERTY_'.$arProperty["MANUFACTURER"].'--#--'.GetMessage("WZD_OPTION_CATALOG_17").'--,--PROPERTY_'.$arProperty["RECOMMEND"].'--#--'.GetMessage("WZD_OPTION_CATALOG_31").'--,--PROPERTY_'.$arProperty["MORE_PHOTO"].'--#--'.GetMessage("WZD_OPTION_CATALOG_18").'--,--CATALOG--#--'.GetMessage("WZD_OPTION_CATALOG_20").'--;--cedit1--#--'.GetMessage("WZD_OPTION_CATALOG_27").'--,--PREVIEW_TEXT--#--'.GetMessage("WZD_OPTION_CATALOG_6").'--,--DETAIL_TEXT--#--'.GetMessage("WZD_OPTION_CATALOG_7").'--,--cedit1_csection1--#----'.GetMessage("WZD_OPTION_CATALOG_9").'--,--SECTIONS--#--'.GetMessage("WZD_OPTION_CATALOG_30").'--;--', ));

CUserOptions::SetOption("form", "form_section_".$iblockID, array ( 'tabs' => 'edit1--#--'.GetMessage("WZD_OPTION_CATALOG_21").'--,--ACTIVE--#--'.GetMessage("WZD_OPTION_CATALOG_22").'--,--IBLOCK_SECTION_ID--#--'.GetMessage("WZD_OPTION_CATALOG_23").'--,--NAME--#--'.GetMessage("WZD_OPTION_CATALOG_24").'--,--CODE--#--'.GetMessage("WZD_OPTION_CATALOG_25").'--,--SORT--#--'.GetMessage("WZD_OPTION_CATALOG_28").'--,--PICTURE--#--'.GetMessage("WZD_OPTION_CATALOG_26").'--,--DESCRIPTION--#--'.GetMessage("WZD_OPTION_CATALOG_27").'--;--', ));

CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockType.".".$iblockID), array ( 'columns' => 'DETAIL_PICTURE,PROPERTY_'.$arProperty["ARTNUMBER"].',NAME,CATALOG_GROUP_1,PROPERTY_'.$arProperty["SPECIALOFFER"].',PROPERTY_'.$arProperty["NEWPRODUCT"].',PROPERTY_'.$arProperty["SALELEADER"].'', 'by' => 'timestamp_x', 'order' => 'desc', 'page_size' => '20', ));

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/catalog/furniture/index.php", array("CATALOG_IBLOCK_ID" => $iblockID));
?>