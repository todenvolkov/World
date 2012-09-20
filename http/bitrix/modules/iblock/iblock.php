<?
if(!defined("CACHED_b_iblock_type")) define("CACHED_b_iblock_type", 36000);
if(!defined("CACHED_b_iblock")) define("CACHED_b_iblock", 36000);
if(!defined("CACHED_b_iblock_count")) define("CACHED_b_iblock_count", 300);
if(!defined("CACHED_b_iblock_property_enum")) define("CACHED_b_iblock_property_enum", 36000);
if(!defined("CACHED_b_iblock_property_enum_bucket_size")) define("CACHED_b_iblock_property_enum_bucket_size", 100);


global $DBType;
$arClasses = array(
	"CIBlockPropertyResult" => "classes/general/iblockpropresult.php",
	"CIBlockResult" => "classes/general/iblockresult.php",
	"_CIBElement" => "classes/general/iblock_element.php",
	"CIBlockType" => "classes/general/iblocktype.php",
	"CAllIBlock" => "classes/general/iblock.php",
	"CIBlock" => "classes/".$DBType."/iblock.php",
	"CAllIBlockSection" => "classes/general/iblocksection.php",
	"CIBlockSection" => "classes/".$DBType."/iblocksection.php",
	"CAllIBlockProperty" => "classes/general/iblockproperty.php",
	"CIBlockPropertyEnum" => "classes/general/iblockpropertyenum.php",
	"CIBlockProperty" => "classes/".$DBType."/iblockproperty.php",
	"CAllIBlockElement" => "classes/general/iblockelement.php",
	"CIBlockElement" => "classes/".$DBType."/iblockelement.php",
	"CAllIBlockRSS" => "classes/general/iblockrss.php",
	"CIBlockRSS" => "classes/".$DBType."/iblockrss.php",
	"CIBlockPropertyDateTime" => "classes/general/prop_datetime.php",
	"CIBlockPropertyXmlID" => "classes/general/prop_xmlid.php",
	"CIBlockPropertyFileMan" => "classes/general/prop_fileman.php",
	"CIBlockPropertyHTML" => "classes/general/prop_html.php",
	"CIBlockPropertyElementList" => "classes/general/prop_element_list.php",
	"CIBlockXMLFile" => "classes/".$DBType."/cml2.php",
	"CIBlockCMLImport" => "classes/general/cml2.php",
	"CIBlockCMLExport" => "classes/general/cml2.php",
	"CIBlockFindTools" => "classes/general/comp_findtools.php",
	"CIBlockPriceTools" => "classes/general/comp_pricetools.php",
	"CIBlockParameters" => "classes/general/comp_parameters.php",
	"CIBlockFormatProperties" => "classes/general/comp_formatprops.php",
	"CIBlockSequence" => "classes/".$DBType."/iblocksequence.php",
	"CIBlockPropertySequence" => "classes/general/prop_seq.php",
	"CIBlockPropertyElementAutoComplete" => "/classes/general/prop_element_auto.php",
);

if(IsModuleInstalled('bizproc'))
{
	$arClasses["CIBlockDocument"] = "classes/general/iblockdocument.php";
}

CModule::AddAutoloadClasses("iblock", $arClasses);

IncludeModuleLangFile(__FILE__);

/********************************************************************
*  Information blocks classes
********************************************************************/
function CIBlockPropertyDateTime_GetUserTypeDescription()
{
	return array(
		"PROPERTY_TYPE" => "S",
		"USER_TYPE" => "DateTime",
		"DESCRIPTION" => GetMessage("IBLOCK_PROP_DATETIME_DESC"),
		//optional handlers
		"GetPublicViewHTML" => array("CIBlockPropertyDateTime","GetPublicViewHTML"),
		"GetPublicEditHTML" => array("CIBlockPropertyDateTime","GetPublicEditHTML"),
		"GetAdminListViewHTML" => array("CIBlockPropertyDateTime","GetAdminListViewHTML"),
		"GetPropertyFieldHtml" => array("CIBlockPropertyDateTime","GetPropertyFieldHtml"),
		"CheckFields" => array("CIBlockPropertyDateTime","CheckFields"),
		"ConvertToDB" => array("CIBlockPropertyDateTime","ConvertToDB"),
		"ConvertFromDB" => array("CIBlockPropertyDateTime","ConvertFromDB"),
		"GetSettingsHTML" => array("CIBlockPropertyDateTime","GetSettingsHTML"),
		"GetAdminFilterHTML" => array("CIBlockPropertyDateTime","GetAdminFilterHTML"),
		"GetPublicFilterHTML" => array("CIBlockPropertyDateTime","GetPublicFilterHTML"),
		"AddFilterFields" => array("CIBlockPropertyDateTime","AddFilterFields"),
	);
}
AddEventHandler("iblock", "OnIBlockPropertyBuildList", "CIBlockPropertyDateTime_GetUserTypeDescription");

function CIBlockPropertyXmlID_GetUserTypeDescription()
{
	return array(
		"PROPERTY_TYPE"		=>"S",
		"USER_TYPE"		=>"ElementXmlID",
		"DESCRIPTION"		=>GetMessage("IBLOCK_PROP_XMLID_DESC"),
		"GetPublicViewHTML"	=>array("CIBlockPropertyXmlID","GetPublicViewHTML"),
		"GetAdminListViewHTML"	=>array("CIBlockPropertyXmlID","GetAdminListViewHTML"),
		"GetPropertyFieldHtml"	=>array("CIBlockPropertyXmlID","GetPropertyFieldHtml"),
		"GetSettingsHTML"	=>array("CIBlockPropertyXmlID","GetSettingsHTML"),
	);
}
AddEventHandler("iblock", "OnIBlockPropertyBuildList", "CIBlockPropertyXmlID_GetUserTypeDescription");

function CIBlockPropertyFileMan_GetUserTypeDescription()
{
	return array(
		"PROPERTY_TYPE"		=>"S",
		"USER_TYPE"		=>"FileMan",
		"DESCRIPTION"		=>GetMessage("IBLOCK_PROP_FILEMAN_DESC"),
		"GetPropertyFieldHtml"	=>array("CIBlockPropertyFileMan","GetPropertyFieldHtml"),
		"ConvertToDB"		=>array("CIBlockPropertyFileMan","ConvertToDB"),
		"ConvertFromDB"		=>array("CIBlockPropertyFileMan","ConvertFromDB"),
	);
}
AddEventHandler("iblock", "OnIBlockPropertyBuildList", "CIBlockPropertyFileMan_GetUserTypeDescription");

function CIBlockPropertyHTML_GetUserTypeDescription()
{
	return array(
		"PROPERTY_TYPE" => "S",
		"USER_TYPE" => "HTML",
		"DESCRIPTION" => GetMessage("IBLOCK_PROP_HTML_DESC"),
		"GetPublicViewHTML" => array("CIBlockPropertyHTML","GetPublicViewHTML"),
		"GetPublicEditHTML" => array("CIBlockPropertyHTML","GetPublicEditHTML"),
		"GetAdminListViewHTML" => array("CIBlockPropertyHTML","GetAdminListViewHTML"),
		"GetPropertyFieldHtml" => array("CIBlockPropertyHTML","GetPropertyFieldHtml"),
		"ConvertToDB" => array("CIBlockPropertyHTML","ConvertToDB"),
		"ConvertFromDB" => array("CIBlockPropertyHTML","ConvertFromDB"),
		"GetLength" =>array("CIBlockPropertyHTML","GetLength"),
		"PrepareSettings" =>array("CIBlockPropertyHTML","PrepareSettings"),
		"GetSettingsHTML" =>array("CIBlockPropertyHTML","GetSettingsHTML"),
	);
}
AddEventHandler("iblock", "OnIBlockPropertyBuildList", "CIBlockPropertyHTML_GetUserTypeDescription");

function CIBlockPropertyElementList_GetUserTypeDescription()
{
	return array(
		"PROPERTY_TYPE" => "E",
		"USER_TYPE" => "EList",
		"DESCRIPTION" => GetMessage("IBLOCK_PROP_ELIST_DESC"),
		"GetPropertyFieldHtml" => array("CIBlockPropertyElementList","GetPropertyFieldHtml"),
		"GetPropertyFieldHtmlMulty" => array("CIBlockPropertyElementList","GetPropertyFieldHtmlMulty"),
		"GetPublicEditHTML" => array("CIBlockPropertyElementList","GetPropertyFieldHtml"),
		"GetPublicEditHTMLMulty" => array("CIBlockPropertyElementList","GetPropertyFieldHtmlMulty"),
		"GetAdminFilterHTML" => array("CIBlockPropertyElementList","GetAdminFilterHTML"),
		"PrepareSettings" =>array("CIBlockPropertyElementList","PrepareSettings"),
		"GetSettingsHTML" =>array("CIBlockPropertyElementList","GetSettingsHTML"),
	);
}
AddEventHandler("iblock", "OnIBlockPropertyBuildList", "CIBlockPropertyElementList_GetUserTypeDescription");

function CIBlockPropertySequence_GetUserTypeDescription()
{
	return array(
		"PROPERTY_TYPE" => "N",
		"USER_TYPE" => "Sequence",
		"DESCRIPTION" => GetMessage("IBLOCK_PROP_SEQUENCE_DESC"),
		"GetPropertyFieldHtml" => array("CIBlockPropertySequence","GetPropertyFieldHtml"),
		"GetPublicEditHTML" => array("CIBlockPropertySequence","GetPropertyFieldHtml"),
		"PrepareSettings" =>array("CIBlockPropertySequence","PrepareSettings"),
		"GetSettingsHTML" =>array("CIBlockPropertySequence","GetSettingsHTML"),
		"GetAdminFilterHTML" => array("CIBlockPropertySequence","GetPublicFilterHTML"),
		"GetPublicFilterHTML" => array("CIBlockPropertySequence","GetPublicFilterHTML"),
		"AddFilterFields" => array("CIBlockPropertySequence","AddFilterFields"),
	);
}
AddEventHandler("iblock", "OnIBlockPropertyBuildList", "CIBlockPropertySequence_GetUserTypeDescription");

function CIBlockPropertyElementAutoComplete_GetUserTypeDescription()
{
	return array(
		"PROPERTY_TYPE" => "E",
		"USER_TYPE" => "EAutocomplete",
		"DESCRIPTION" => GetMessage("IBLOCK_PROP_EAUTOCOMPLETE_DESC"),
		"GetPropertyFieldHtml" => array("CIBlockPropertyElementAutoComplete", "GetPropertyFieldHtml"), 
		"GetPropertyFieldHtmlMulty" => array('CIBlockPropertyElementAutoComplete','GetPropertyFieldHtmlMulty'),
        "GetPublicViewHTML" => array("CIBlockPropertyElementAutoComplete", "GetPublicViewHTML"), 
		"GetAdminListViewHTML" => array("CIBlockPropertyElementAutoComplete","GetAdminListViewHTML"), 
		"GetPublicViewHTML" => array('CIBlockPropertyElementAutoComplete','GetPublicViewHTML'),
		"GetPublicEditHTML" => array('CIBlockPropertyElementAutoComplete','GetPublicEditHTML'),
		"GetAdminFilterHTML" => array('CIBlockPropertyElementAutoComplete','GetAdminFilterHTML'),
		"GetSettingsHTML" => array('CIBlockPropertyElementAutoComplete','GetSettingsHTML'),
		"PrepareSettings" => array('CIBlockPropertyElementAutoComplete','PrepareSettings'),
		//"ConvertToDB" => array('CIBlockPropertyElementAutoComplete','ConvertToDB'),
		"AddFilterFields" => array('CIBlockPropertyElementAutoComplete','AddFilterFields'),
	);
}

AddEventHandler("iblock", "OnIBlockPropertyBuildList", "CIBlockPropertyElementAutoComplete_GetUserTypeDescription");

/*********************************************
Public helper functions
*********************************************/
function GetIBlockListWithCnt($type, $arTypesInc = Array(), $arTypesExc = Array(), $arOrder=Array("SORT"=>"ASC"), $cnt=0)
{
	if(!is_array($arTypesInc))
		$arTypesInc = Array($arTypesInc);

	$arIDsInc = Array();
	$arCODEsInc = Array();
	for($i=0; $i<count($arTypesInc); $i++)
		if(IntVal($arTypesInc[$i])>0)
			$arIDsInc[] = $arTypesInc[$i];
		else
			$arCODEsInc[] = $arTypesInc[$i];

	if(!is_array($arTypesExc))
		$arTypesExc = Array($arTypesExc);

	$arIDsExc = Array();
	$arCODEsExc = Array();
	for($i=0; $i<count($arTypesExc); $i++)
		if(IntVal($arTypesExc[$i])>0)
			$arIDsExc[] = $arTypesExc[$i];
		else
			$arCODEsExc[] = $arTypesExc[$i];

	$res = CIBlock::GetList($arOrder, Array("type"=>$type, "LID"=>LANG, "ACTIVE"=>"Y", "ID"=>$arIDsInc, "CNT_ACTIVE"=>"Y", "CODE"=>$arCODEsInc, "!ID"=>$arIDsExc, "!CODE"=>$arCODEsExc), true);
	$dbr = new  CIBlockResult($res);
	if($cnt>0)
		$dbr->NavStart($cnt);
	return $dbr;
}

function GetIBlockList($type, $arTypesInc = Array(), $arTypesExc = Array(), $arOrder=Array("SORT"=>"ASC"), $cnt=0)
{
	return GetIBlockListLang(LANG, $type, $arTypesInc, $arTypesExc, $arOrder, $cnt);
}

function GetIBlockListLang($lang, $type, $arTypesInc = Array(), $arTypesExc = Array(), $arOrder=Array("SORT"=>"ASC"), $cnt=0)
{
	if(!is_array($arTypesInc))
		$arTypesInc = Array($arTypesInc);

	$arIDsInc = Array();
	$arCODEsInc = Array();
	for($i=0; $i<count($arTypesInc); $i++)
		if(IntVal($arTypesInc[$i])>0)
			$arIDsInc[] = $arTypesInc[$i];
		else
			$arCODEsInc[] = $arTypesInc[$i];

	if(!is_array($arTypesExc))
		$arTypesExc = Array($arTypesExc);

	$arIDsExc = Array();
	$arCODEsExc = Array();
	for($i=0; $i<count($arTypesExc); $i++)
		if(IntVal($arTypesExc[$i])>0)
			$arIDsExc[] = $arTypesExc[$i];
		else
			$arCODEsExc[] = $arTypesExc[$i];

	$res = CIBlock::GetList($arOrder, Array("type"=>$type, "LID"=>$lang, "ACTIVE"=>"Y", "ID"=>$arIDsInc, "CODE"=>$arCODEsInc, "!ID"=>$arIDsExc, "!CODE"=>$arCODEsExc));
	$dbr = new  CIBlockResult($res);
	if($cnt>0)
		$dbr->NavStart($cnt);
	return $dbr;
}

function GetIBlock($ID, $type="")
{
	return GetIBlockLang(LANG, $ID, $type);
}

function GetIBlockLang($lang, $ID, $type="")
{
	$res = CIBlock::GetList(Array("sort"=>"asc"), Array("ID"=>IntVal($ID), "TYPE"=>$type, "LID"=>$lang, "ACTIVE"=>"Y"));
	$res = new CIBlockResult($res);
	return $arRes = $res->GetNext();
}

/**************************
Elements helper functions
**************************/
function GetIBlockElementListEx($type, $arTypesInc=Array(), $arTypesExc=Array(), $arOrder=Array("sort"=>"asc"), $cnt=0, $arFilter = Array(), $arSelect=Array(), $arGroupBy=false)
{
	return GetIBlockElementListExLang(LANG, $type, $arTypesInc, $arTypesExc, $arOrder, $cnt, $arFilter, $arSelect, $arGroupBy);
}

function GetIBlockElementCountEx($type, $arTypesInc=Array(), $arTypesExc=Array(), $arOrder=Array("sort"=>"asc"), $cnt=0, $arFilter = Array())
{
	return GetIBlockElementCountExLang(LANG, $type, $arTypesInc, $arTypesExc, $arOrder, $cnt, $arFilter);
}

function GetIBlockElementListExLang($lang, $type, $arTypesInc=Array(), $arTypesExc=Array(), $arOrder=Array("sort"=>"asc"), $cnt=0, $arFilter = Array(), $arSelect=Array(), $arGroupBy=false)
{
	$filter = _GetIBlockElementListExLang_tmp($lang, $type, $arTypesInc, $arTypesExc, $arOrder, $cnt, $arFilter);
	if(is_array($cnt))
		$arNavParams = $cnt; //Array("nPageSize"=>$cnt, "bShowAll"=>false);
	elseif($cnt>0)
		$arNavParams = Array("nPageSize"=>$cnt);
	else
		$arNavParams = false;

	$dbr = CIBlockElement::GetList($arOrder, $filter, $arGroupBy, $arNavParams, $arSelect);
	if(!is_array($cnt) && $cnt>0)
		$dbr->NavStart($cnt);

	return $dbr;
}

function GetIBlockElementCountExLang($lang, $type, $arTypesInc=Array(), $arTypesExc=Array(), $arOrder=Array("sort"=>"asc"), $cnt=0, $arFilter = Array())
{
	$filter = _GetIBlockElementListExLang_tmp($lang, $type, $arTypesInc, $arTypesExc, $arOrder, $cnt, $arFilter);
	return CIBlockElement::GetList($arOrder, $filter, true);
}


function _GetIBlockElementListExLang_tmp($lang, $type, $arTypesInc=Array(), $arTypesExc=Array(), $arOrder=Array("sort"=>"asc"), $cnt=0, $arFilter = Array(), $arSelect=Array())
{
	global $DB;
	if(!is_array($arTypesInc))
	{
		if($arTypesInc!==false)
			$arTypesInc = Array($arTypesInc);
		else
			$arTypesInc = Array();
	}

	$arIDsInc = Array();
	$arCODEsInc = Array();
	for($i=0; $i<count($arTypesInc); $i++)
		if(IntVal($arTypesInc[$i])>0)
			$arIDsInc[] = $arTypesInc[$i];
		else
			$arCODEsInc[] = $arTypesInc[$i];

	if(!is_array($arTypesExc))
	{
		if($arTypesExc!==false)
			$arTypesExc = Array($arTypesExc);
		else
			$arTypesExc = Array();
	}

	$arIDsExc = Array();
	$arCODEsExc = Array();
	for($i=0; $i<count($arTypesExc); $i++)
		if(IntVal($arTypesExc[$i])>0)
			$arIDsExc[] = $arTypesExc[$i];
		else
			$arCODEsExc[] = $arTypesExc[$i];

	$filter = Array(
			"IBLOCK_ID"=>$arIDsInc, "IBLOCK_LID"=>$lang, "IBLOCK_ACTIVE"=>"Y",
			"IBLOCK_CODE"=>$arCODEsInc, "!IBLOCK_ID"=>$arIDsExc,
			"!IBLOCK_CODE"=>$arCODEsExc, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "CHECK_PERMISSIONS"=>"Y"
			);

	if($type!=false && strlen($type)>0)
		$filter["IBLOCK_TYPE"]=$type;

	if(is_array($arFilter) && count($arFilter)>0)
		$filter = array_merge($filter, $arFilter);

	return $filter;
}

function GetIBlockElementCount($IBLOCK, $SECT_ID=false, $arOrder=Array("sort"=>"asc"), $cnt=0)
{
	$filter = Array("IBLOCK_ID"=>IntVal($IBLOCK), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "CHECK_PERMISSIONS"=>"Y");
	if($SECT_ID!==false)
		$filter["SECTION_ID"]=IntVal($SECT_ID);

	return CIBlockElement::GetList($arOrder, $filter, true);
}

function GetIBlockElementList($IBLOCK, $SECT_ID=false, $arOrder=Array("sort"=>"asc"), $cnt=0, $arFilter=array(), $arSelect=array())
{
	$filter = Array("IBLOCK_ID"=>IntVal($IBLOCK), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "CHECK_PERMISSIONS"=>"Y");
	if($SECT_ID!==false)
		$filter["SECTION_ID"]=IntVal($SECT_ID);

	if (is_array($arFilter) && count($arFilter)>0)
		$filter = array_merge($filter, $arFilter);

	$dbr = CIBlockElement::GetList($arOrder, $filter, false, false, $arSelect);
	if($cnt>0)
		$dbr->NavStart($cnt);

	return $dbr;
}

function GetIBlockElement($ID, $TYPE="")
{
	$filter = Array("ID"=>IntVal($ID), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "CHECK_PERMISSIONS"=>"Y");
	if($TYPE!="")
		$filter["IBLOCK_TYPE"]=$TYPE;

	$iblockelement = CIBlockElement::GetList(Array(), $filter);
	if($obIBlockElement = $iblockelement->GetNextElement())
	{
		$arIBlockElement = $obIBlockElement->GetFields();
		if($arIBlock = GetIBlock($arIBlockElement["IBLOCK_ID"], $TYPE))
		{
			$arIBlockElement["IBLOCK_ID"] = $arIBlock["ID"];
			$arIBlockElement["IBLOCK_NAME"] = $arIBlock["NAME"];
			$arIBlockElement["~IBLOCK_NAME"] = $arIBlock["~NAME"];
			$arIBlockElement["PROPERTIES"] = $obIBlockElement->GetProperties();
			return $arIBlockElement;
		}
	}

	return false;
}

/******************************
Sections functions
******************************/
function GetIBlockSectionListWithCnt($IBLOCK, $SECT_ID=false, $arOrder = Array("left_margin"=>"asc"), $cnt=0, $arFilter=Array())
{
	$filter = Array("IBLOCK_ID"=>IntVal($IBLOCK), "ACTIVE"=>"Y", "CNT_ACTIVE"=>"Y");
	if($SECT_ID!==false)
		$filter["SECTION_ID"]=IntVal($SECT_ID);

	if(is_array($arFilter) && count($arFilter)>0)
		$filter = array_merge($filter, $arFilter);

	$dbr = CIBlockSection::GetList($arOrder, $filter, true);
	if($cnt>0)
		$dbr->NavStart($cnt);

	return $dbr;
}

function GetIBlockSectionList($IBLOCK, $SECT_ID=false, $arOrder = Array("left_margin"=>"asc"), $cnt=0, $arFilter=Array())
{
	$filter = Array("IBLOCK_ID"=>IntVal($IBLOCK), "ACTIVE"=>"Y", "IBLOCK_ACTIVE"=>"Y");
	if($SECT_ID!==false)
		$filter["SECTION_ID"]=IntVal($SECT_ID);

	if(is_array($arFilter) && count($arFilter)>0)
		$filter = array_merge($filter, $arFilter);

	$dbr = CIBlockSection::GetList($arOrder, $filter);
	if($cnt>0)
		$dbr->NavStart($cnt);

	return $dbr;
}

function GetIBlockSection($ID, $TYPE="")
{
	$ID = intval($ID);
	if($ID>0)
	{
		$iblocksection = CIBlockSection::GetList(Array(), Array("ID"=>$ID, "ACTIVE"=>"Y"));
		if($arIBlockSection = $iblocksection->GetNext())
		{
			if($arIBlock = GetIBlock($arIBlockSection["IBLOCK_ID"], $TYPE))
			{
				$arIBlockSection["IBLOCK_ID"] = $arIBlock["ID"];
				$arIBlockSection["IBLOCK_NAME"] = $arIBlock["NAME"];
				return $arIBlockSection;
			}
		}
	}
	return false;
}

function GetIBlockSectionPath($IBLOCK, $SECT_ID)
{
	return CIBlockSection::GetNavChain(IntVal($IBLOCK), IntVal($SECT_ID));
}

/***************************************************************
RSS
***************************************************************/
function xmlize_rss($data)
{
	$data = trim($data);
	$vals = $index = $array = array();
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $vals, $index);
	xml_parser_free($parser);

	$i = 0;

	$tagname = $vals[$i]['tag'];
	if (isset($vals[$i]['attributes']))
		$array[$tagname]['@'] = $vals[$i]['attributes'];
	else
		$array[$tagname]['@'] = array();

	$array[$tagname]["#"] = xml_depth_rss($vals, $i);

	return $array;
}

function xml_depth_rss($vals, &$i)
{
	$children = array();

	if (isset($vals[$i]['value']))
		array_push($children, $vals[$i]['value']);

	while (++$i < count($vals))
	{
		switch ($vals[$i]['type'])
		{
		   case 'open':
				if (isset($vals[$i]['tag']))
					$tagname = $vals[$i]['tag'];
				else
					$tagname = '';

				if (isset($children[$tagname]))
					$size = sizeof($children[$tagname]);
				else
					$size = 0;

				if (isset($vals[$i]['attributes']))
					$children[$tagname][$size]['@'] = $vals[$i]["attributes"];

				$children[$tagname][$size]['#'] = xml_depth_rss($vals, $i);
			break;

			case 'cdata':
				array_push($children, $vals[$i]['value']);
			break;

			case 'complete':
				$tagname = $vals[$i]['tag'];

				if(isset($children[$tagname]))
					$size = sizeof($children[$tagname]);
				else
					$size = 0;

				if(isset($vals[$i]['value']))
					$children[$tagname][$size]["#"] = $vals[$i]['value'];
				else
					$children[$tagname][$size]["#"] = '';

				if (isset($vals[$i]['attributes']))
					$children[$tagname][$size]['@'] = $vals[$i]['attributes'];
			break;

			case 'close':
				return $children;
			break;
		}

	}

	return $children;
}

function GetIBlockDropDownList($IBLOCK_ID, $strTypeName, $strIBlockName, $arFilter = false)
{
	$html = '';

	static $arTypes = false;
	static $arIBlocks = false;

	if(!$arTypes)
	{
		$arTypes = array(''=>GetMessage("IBLOCK_CHOOSE_IBLOCK_TYPE"));
		$arIBlocks = array(''=>array(''=>GetMessage("IBLOCK_CHOOSE_IBLOCK")));

		$IBLOCK_TYPE = false;

		if(!is_array($arFilter))
			$arFilter = array();
		$arFilter["MIN_PERMISSION"] = "W";

		$rsIBlocks = CIBlock::GetList(array("IBLOCK_TYPE" => "ASC", "NAME" => "ASC"), $arFilter);
		while($arIBlock = $rsIBlocks->Fetch())
		{
			if($IBLOCK_ID == $arIBlock["ID"])
				$IBLOCK_TYPE = $arIBlock["IBLOCK_TYPE_ID"];
			if(!array_key_exists($arIBlock["IBLOCK_TYPE_ID"], $arTypes))
			{
				$arType = CIBlockType::GetByIDLang($arIBlock["IBLOCK_TYPE_ID"], LANG);
				$arTypes[$arType["~ID"]] = $arType["~NAME"]." [".$arType["~ID"]."]";
				$arIBlocks[$arType["~ID"]] = array(''=>GetMessage("IBLOCK_CHOOSE_IBLOCK"));
			}
			$arIBlocks[$arIBlock["IBLOCK_TYPE_ID"]][$arIBlock["ID"]] = $arIBlock["NAME"]." [".$arIBlock["ID"]."]";
		}

		$html .= '
		<script language="JavaScript">
		function OnTypeChanged(typeSelect, iblockSelectID)
		{
			var arIBlocks = '.CUtil::PhpToJSObject($arIBlocks).';
			var iblockSelect = document.getElementById(iblockSelectID);
			if(iblockSelect)
			{
				for(var i=iblockSelect.length-1; i >= 0; i--)
					iblockSelect.remove(i);
				var n = 0;
				for(var j in arIBlocks[typeSelect.value])
				{
					var newoption = new Option(arIBlocks[typeSelect.value][j], j, false, false);
					iblockSelect.options[n]=newoption;
					n++;
				}
			}
		}
		</script>
		';
	}

	$html .= '<select name="'.htmlspecialchars($strTypeName).'" id="'.htmlspecialchars($strTypeName).'" OnChange="OnTypeChanged(this, \''.CUtil::JSEscape($strIBlockName).'\')">'."\n";
	foreach($arTypes as $key => $value)
	{
		if($IBLOCK_TYPE === false)
			$IBLOCK_TYPE = $key;
		$html .= '<option value="'.htmlspecialchars($key).'"'.($IBLOCK_TYPE===$key? ' selected': '').'>'.htmlspecialchars($value).'</option>'."\n";
	}
	$html .= "</select>\n";

	$html .= "&nbsp;\n";

	$html .= '<select name="'.htmlspecialchars($strIBlockName).'" id="'.htmlspecialchars($strIBlockName).'">'."\n";
	foreach($arIBlocks[$IBLOCK_TYPE] as $key => $value)
	{
		$html .= '<option value="'.htmlspecialchars($key).'"'.($IBLOCK_ID==$key? ' selected': '').'>'.htmlspecialchars($value).'</option>'."\n";
	}
	$html .= "</select>\n";

	return $html;
}

function ImportXMLFile($file_name, $iblock_type="-", $site_id=false, $section_action="D", $element_action="D", $use_crc=false, $preview=false, $sync=false, $return_last_error=false)
{
	global $APPLICATION;

	$ABS_FILE_NAME = false;
	$WORK_DIR_NAME = false;
	if(strlen($file_name)>0)
	{
		$filename = trim(str_replace("\\", "/", trim($file_name)), "/");
		$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$filename);
		if((strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename) && ($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W"))
		{
			$ABS_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$FILE_NAME;
			$WORK_DIR_NAME = substr($ABS_FILE_NAME, 0, strrpos($ABS_FILE_NAME, "/")+1);
		}
	}

	if(!$ABS_FILE_NAME)
		return GetMessage("IBLOCK_XML2_FILE_ERROR");

	if(substr($ABS_FILE_NAME, -7) == ".tar.gz")
	{
		include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/tar_gz.php");
		$obArchiver = new CArchiver($ABS_FILE_NAME);
		if(!$obArchiver->ExtractFiles($WORK_DIR_NAME))
		{
			$strError = "";
			if(is_object($APPLICATION))
			{
				$arErrors = $obArchiver->GetErrors();
				if(count($arErrors))
				{
					foreach($arErrors as $error)
						$strError .= $error[1]."<br>";
				}
			}
			if($strError != "")
				return $strError;
			else
				return GetMessage("IBLOCK_XML2_FILE_ERROR");
		}
		$IMP_FILE_NAME = substr($ABS_FILE_NAME, 0, -7).".xml";
	}
	else
	{
		$IMP_FILE_NAME = $ABS_FILE_NAME;
	}

	$fp = fopen($IMP_FILE_NAME, "rb");
	if(!$fp)
		return GetMessage("IBLOCK_XML2_FILE_ERROR");

	if($sync)
		$table_name = "b_xml_tree_sync";
	else
		$table_name = "b_xml_tree";

	$NS = array("STEP"=>0);

	$obCatalog = new CIBlockCMLImport;
	$obCatalog->Init($NS, $WORK_DIR_NAME, $use_crc, $preview, false, false, false, $table_name);

	if($sync)
	{
		if(!$obCatalog->StartSession(bitrix_sessid()))
			return GetMessage("IBLOCK_XML2_TABLE_CREATE_ERROR");

		$obCatalog->ReadXMLToDatabase($fp, $NS, 0, 1024);

		$xml_root = $obCatalog->GetSessionRoot();
		$bUpdateIBlock = false;
	}
	else
	{
		$obCatalog->DropTemporaryTables();

		if(!$obCatalog->CreateTemporaryTables())
			return GetMessage("IBLOCK_XML2_TABLE_CREATE_ERROR");

		$obCatalog->ReadXMLToDatabase($fp, $NS, 0, 1024);

		if(!$obCatalog->IndexTemporaryTables())
			return GetMessage("IBLOCK_XML2_INDEX_ERROR");

		$xml_root = 1;
		$bUpdateIBlock = true;
	}

	fclose($fp);

	$result = $obCatalog->ImportMetaData($xml_root, $iblock_type, $site_id, $bUpdateIBlock);
	if($result !== true)
		return GetMessage("IBLOCK_XML2_METADATA_ERROR").implode("\n", $result);

	$obCatalog->ImportSections();
	$obCatalog->DeactivateSections($section_action);
	$obCatalog->SectionsResort();

	$obCatalog = new CIBlockCMLImport;
	$obCatalog->Init($NS, $WORK_DIR_NAME, $use_crc, $preview, false, false, false, $table_name);
	if($sync)
	{
		if(!$obCatalog->StartSession(bitrix_sessid()))
			return GetMessage("IBLOCK_XML2_TABLE_CREATE_ERROR");
	}
	$SECTION_MAP = false;
	$PRICES_MAP = false;
	$obCatalog->ReadCatalogData($SECTION_MAP, $PRICES_MAP);
	$result = $obCatalog->ImportElements(time(), 0);

	$obCatalog->DeactivateElement($element_action, time(), 0);
	if($sync)
		$obCatalog->EndSession();

	if(substr($FILE_NAME, -7) == ".tar.gz")
	{
		DeleteDirFilesEx(substr($FILE_NAME, 0, -7).".xml");
		DeleteDirFilesEx(substr($FILE_NAME, 0, -7)."_files");
	}

	if($return_last_error)
	{
		if(strlen($obCatalog->LAST_ERROR))
			return $obCatalog->LAST_ERROR;
	}

	return true;
}

?>
