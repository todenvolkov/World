<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$arParams['IN_COMPLEX'] = 'N';
if (($arParent =  $this->GetParent()) !== NULL)
	$arParams['IN_COMPLEX'] = 'Y';

if(empty($arParams['USE_REVIEW']))
	$arParams['USE_REVIEW'] = 'Y';    
    
if ($arParams['IN_COMPLEX'] == 'Y' && strpos($this->GetParent()->GetName(), 'socialnetwork') === false)
	$arParams['USE_REVIEW'] = $this->GetParent()->arResult['USE_REVIEW']; 

if($arParams['USE_REVIEW'] == 'N')
	return;

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

if(empty($arParams['PATH_TO_POST']))
	$arParams['PATH_TO_POST'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");    

$arParams['PATH_TO_POST_EDIT'] = trim($arParams['PATH_TO_POST_EDIT']);
if(strlen($arParams['PATH_TO_POST_EDIT'])<=0)
	$arParams['PATH_TO_POST_EDIT'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY'] = trim($arParams['PATH_TO_HISTORY']);
if(strlen($arParams['PATH_TO_HISTORY'])<=0)
	$arParams['PATH_TO_HISTORY'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY_DIFF'] = trim($arParams['PATH_TO_HISTORY_DIFF']);
if(strlen($arParams['PATH_TO_HISTORY_DIFF'])<=0)
	$arParams['PATH_TO_HISTORY_DIFF'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");	
	
$arParams['PATH_TO_DISCUSSION'] = trim($arParams['PATH_TO_DISCUSSION']);
if(strlen($arParams['PATH_TO_DISCUSSION'])<=0) 
{
	$arParams['PATH_TO_DISCUSSION'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_DISCUSSION'] = $this->GetParent()->arResult['PATH_TO_DISCUSSION'];
}
	
$arParams['PATH_TO_CATEGORY'] = trim($arParams['PATH_TO_POST']);	

$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);	
if(strlen($arParams['PATH_TO_USER'])<=0)
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_USER'] = $this->GetParent()->arParams['PATH_TO_USER'];	
}

$GLOBALS['arParams'] = $arParams;

if (!CModule::IncludeModule('wiki'))
{
	ShowError(GetMessage('WIKI_MODULE_NOT_INSTALLED'));
	return;
}

if (!CModule::IncludeModule('iblock'))
{
	ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

if (!CModule::IncludeModule('forum'))
{
	ShowError(GetMessage('FORUM_MODULE_NOT_INSTALLED'));
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

$arParams['ELEMENT_NAME'] = urldecode($arParams['ELEMENT_NAME']);
$arFilter = array(
	'IBLOCK_LID' => SITE_ID,
	'IBLOCK_ID' => $arParams['IBLOCK_ID'],
	'CHECK_PERMISSIONS' => 'N',
	'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE']
);

if (empty($arParams['ELEMENT_NAME'])) 
	$arParams['ELEMENT_NAME'] = CWiki::GetDefaultPage($arParams['IBLOCK_ID']);

$arResult['ELEMENT'] = array();    	
if (!empty($arParams['ELEMENT_NAME']) && ($arResult['ELEMENT'] = CWiki::getElementByName($arParams['ELEMENT_NAME'], $arFilter)) != false)
{	
	$arParams['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];		
}
else 	
    return ;     

//$arResult['TOPLINKS'] = CWikiUtils::getRightsLinks('discussion', $arParams);

$arResult['CACHE_TYPE'] = $arParams['CACHE_TYPE'];
$arResult['CACHE_TIME'] = $arParams['CACHE_TIME'];
$arResult['MESSAGES_PER_PAGE'] = $arParams['MESSAGES_PER_PAGE'];
$arResult['USE_CAPTCHA'] = $arParams['USE_CAPTCHA'];
$arResult['PATH_TO_SMILE'] = $arParams['PATH_TO_SMILE'];
$arResult['URL_TEMPLATES_READ'] = $arParams['URL_TEMPLATES_READ'];
$arResult['SHOW_LINK_TO_FORUM'] = $arParams['SHOW_LINK_TO_FORUM'];
$arResult['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];
$arResult['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
$arResult['FORUM_ID'] = $arParams['FORUM_ID'];
$arResult['POST_FIRST_MESSAGE'] = $arParams['POST_FIRST_MESSAGE'];
$arResult['URL_TEMPLATES_DETAIL'] = $arParams['URL_TEMPLATES_DETAIL'];

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/wiki/include/nav.php');    		    	    	

$this->IncludeComponentTemplate();
unset($GLOBALS['arParams']);

?>