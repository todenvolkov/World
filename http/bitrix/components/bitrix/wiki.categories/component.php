<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$arParams['IN_COMPLEX'] = 'N';
if (($arParent =  $this->GetParent()) !== NULL)
	$arParams['IN_COMPLEX'] = 'Y';

if(empty($arParams['PAGE_VAR']))
	$arParams['PAGE_VAR'] = 'title';
if(empty($arParams['OPER_VAR']))
	$arParams['OPER_VAR'] = 'oper';    	
$arParams['PATH_TO_POST'] = trim($arParams['PATH_TO_POST']);
if(empty($arParams['SEF_MODE']))
{
	$arParams['SEF_MODE'] = 'N';
	if ($arParams['IN_COMPLEX'] == 'Y')
		$arParams['SEF_MODE'] = $this->GetParent()->arResult['SEF_MODE'];
} 

if(empty($arParams['SOCNET_GROUP_ID']) && $arParams['IN_COMPLEX'] == 'Y')
{
	if (strpos($this->GetParent()->GetName(), 'socialnetwork') !== false &&
		!empty($this->GetParent()->arResult['VARIABLES']['group_id']))
		$arParams['SOCNET_GROUP_ID'] = $this->GetParent()->arResult['VARIABLES']['group_id'];
}

$arParams['PATH_TO_POST'] = trim($arParams['PATH_TO_POST']);
if(strlen($arParams['PATH_TO_POST'])<=0)
	$arParams['PATH_TO_POST'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");
	     
$arParams['PATH_TO_CATEGORY'] = trim($arParams['PATH_TO_POST']);
     
$arParams['PATH_TO_CATEGORIES'] = trim($arParams['PATH_TO_CATEGORIES']);
if(strlen($arParams['PATH_TO_CATEGORIES'])<=0)
{
	$arParams['PATH_TO_CATEGORIES'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[OPER_VAR]=categories");
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == "Y")
		$arParams['PATH_TO_CATEGORIES'] = $this->GetParent()->arResult['PATH_TO_CATEGORIES'];	
} 	

$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);	
if(strlen($arParams['PATH_TO_USER'])<=0)
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_USER'] = $this->GetParent()->arParams['PATH_TO_USER'];	
}

if (empty($arParams['CATEGORY_COUNT']))
	$arParams['CATEGORY_COUNT'] = 20;	


$sCategoryName = !empty($GLOBALS['q']) ? $GLOBALS['q'] :  '';

if(!empty($sCategoryName)) 
	$arResult['QUERY'] = htmlspecialchars($sCategoryName);

$GLOBALS['arParams'] = $arParams;
	
if (!CModule::IncludeModule('wiki'))
{
	ShowError(GetMessage('WIKI_MODULE_NOT_INSTALLED'));
	return;
}

if(!CModule::IncludeModule('iblock'))
{
	ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

if (empty($arParams['IBLOCK_ID']))
{
	ShowError(GetMessage('IBLOCK_NOT_ASSIGNED'));
	return;
} 

if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID'])) 
{
	if(!CModule::IncludeModule('socialnetwork'))
	{	    
		ShowError(GetMessage('SOCNET_MODULE_NOT_INSTALLED'));
		return;	    
	}
}	

if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID'])) 
{
	if (!CWikiSocnet::Init($arParams['SOCNET_GROUP_ID'], $arParams['IBLOCK_ID'])) 
	{
		ShowError(GetMessage('WIKI_SOCNET_INITIALIZING_FAILED'));
		return;
	}
}  	

if (!CWikiUtils::IsReadable()) 
{	
	ShowError(GetMessage('WIKI_ACCESS_DENIED'));
	return; 
} 

//$arResult['TOPLINKS'] = CWikiUtils::getRightsLinks('service', $arParams);

// looking for a page without categories
/*$arFilter = array();
$arFilter = array( 
	"IBLOCK_LID" => SITE_ID,
	"IBLOCK_ID" => $arParams['IBLOCK_ID'],
	"CHECK_PERMISSIONS" => 'N',
	"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
    "ACTIVE" => 'Y',
    "!NAME" => 'Category%',
    "SECTION_ID" => false
); 
if (CWikiSocnet::IsSocNet())
    $arFilter['SUBSECTION'] = CWikiSocnet::$iCatId;                  
$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
$iUnsorted = 0;
while($arElement = $rsElement->GetNext())  
  	$iUnsorted++; */   	

$arParams['ELEMENT_NAME'] = urldecode($arParams['ELEMENT_NAME']);

$arFilter = Array();
$arFilter['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
$arFilter['CHECK_PERMISSIONS'] = 'N';  
$arFilter['GLOBAL_ACTIVE'] = 'Y';
$arFilter['CNT_ACTIVE'] = 'Y';
$arFilter['ELEMENT_SUBSECTIONS'] = 'Y';
if (CWikiSocnet::IsSocNet())
{    		    
	$arFilter['>LEFT_BORDER'] = CWikiSocnet::$iCatLeftBorder;
	$arFilter['<RIGHT_BORDER'] = CWikiSocnet::$iCatRightBorder;
}        
if (!empty($sCategoryName)) 
	$arFilter['NAME'] = '%'.$sCategoryName.'%';
 
$dbList = CIBlockSection::GetList(Array('NAME'=>'ASC'), $arFilter, true);  
$dbList->NavStart($arParams['CATEGORY_COUNT'], false);
$arResult['DB_LIST'] = &$dbList;
$arResult['CATEGORIES'] = array();

$arCatName = array();
$arCatNameExists = array();
while($arCat = $dbList->GetNext())  
{
	$arCatName[] = 'Category:'.$arCat['NAME'];
	$arResult['CATEGORIES'][strtolower($arCat['NAME'])] = array(
		'TITLE' => $arCat['NAME'],
		'NAME' => $arCat['NAME'],
		'CNT' => $arCat['ELEMENT_CNT'],
		'IS_RED' => 'Y',
		'LINK' => CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CATEGORY'], 
			array(
				'wiki_name' => urlencode('Category:'.$arCat['NAME']), 
				'group_id' => CWikiSocnet::$iSocNetId
			)
		)
	);              
}  

if (!empty($arCatName))
{
	// checking the category on the "red link"
	$arFilter = array( 
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'CHECK_PERMISSIONS' => 'N',
		'ACTIVE' => 'Y',
		'NAME' => $arCatName
	);   
    
	if (CWikiSocnet::IsSocNet())
		$arFilter['SUBSECTION'] = CWikiSocnet::$iCatId;    
           
	$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());    		
	while($arElement = $rsElement->GetNext())  
		$arCatNameExists[] = substr($arElement['NAME'], strpos($arElement['NAME'], ':') + 1);

	if (!empty($arCatNameExists))
	{
		foreach ($arCatNameExists as $sCatName)
		{  
			$sCatName = strtolower($sCatName);                  
			if (isset($arResult['CATEGORIES'][$sCatName])) 
			{
				$arResult['CATEGORIES'][$sCatName]['IS_RED'] = 'N';
				// exclude the very category page
				$arResult['CATEGORIES'][$sCatName]['CNT']--;
			}
		}
	}    
}

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/wiki/include/nav.php');
    
$this->IncludeComponentTemplate();

unset($GLOBALS['arParams']);

?>