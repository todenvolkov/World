<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

global $CACHE_MANAGER;

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

$arCache = array();
if(empty($arParams['SOCNET_GROUP_ID']) && $arParams['IN_COMPLEX'] == 'Y')
{
	if (strpos($this->GetParent()->GetName(), 'socialnetwork') !== false &&
		!empty($this->GetParent()->arResult['VARIABLES']['group_id']))
	{
		$arParams['SOCNET_GROUP_ID'] = $this->GetParent()->arResult['VARIABLES']['group_id'];
		$arCache['SOCNET_GROUP_ID'] = $arParams['SOCNET_GROUP_ID']; 
	}
} 
if (!empty($arParams["SOCNET_GROUP_ID"]))
	$arCache = array('SOCNET_GROUP_ID' => $arParams['SOCNET_GROUP_ID']);
     
if(empty($arParams['PATH_TO_POST']))
{    
	$arParams['PATH_TO_POST'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_POST'] = $this->GetParent()->arResult['PATH_TO_POST'];
}    

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
	$arParams['PATH_TO_DISCUSSION'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

	
$arParams['PATH_TO_CATEGORY'] = trim($arParams['PATH_TO_POST']);	

$arParams['PATH_TO_CATEGORIES'] = trim($arParams['PATH_TO_CATEGORIES']);
if(strlen($arParams['PATH_TO_CATEGORIES'])<=0)
	$arParams['PATH_TO_CATEGORIES'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[OPER_VAR]=categories");

if(strlen($arParams['PATH_TO_SEARCH'])<=0)
{
	$arParams['PATH_TO_SEARCH'] = htmlspecialchars($APPLICATION->GetCurPage());
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y' && 
		strpos($this->GetParent()->GetName(), 'socialnetwork') === false)
		$arParams['PATH_TO_SEARCH'] = $this->GetParent()->arResult['PATH_TO_SEARCH'];
}

$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);	
if(strlen($arParams['PATH_TO_USER'])<=0)
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_USER'] = $this->GetParent()->arParams['PATH_TO_USER'];	
}

$historyId = 0;
if (isset($_REQUEST['oldid'])) 
{	
	$historyId = intval($_REQUEST['oldid']);
	$arCache['oldid'] = $historyId;
}

$GLOBALS['arParams'] = &$arParams;

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

if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID'])) 
{
	if(!CModule::IncludeModule('socialnetwork'))
	{	    
		ShowError(GetMessage('SOCNET_MODULE_NOT_INSTALLED'));
		return;	    
	}
}

if ($historyId > 0)
{
	if(!CModule::IncludeModule('bizproc'))
	{
		ShowError(GetMessage('BIZPROC_MODULE_NOT_INSTALLED'));
		return;
	}	        
}	

if (empty($arParams['IBLOCK_ID']))
{
	ShowError(GetMessage('IBLOCK_NOT_ASSIGNED'));
	return;
} 

$arResult['SOCNET'] = false;
if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID'])) 
{
	if (!CWikiSocnet::Init($arParams['SOCNET_GROUP_ID'], $arParams['IBLOCK_ID'])) 
	{
		ShowError(GetMessage('WIKI_SOCNET_INITIALIZING_FAILED'));
		return;
	}
	$arResult['SOCNET'] = true;
}   

if (!CWikiUtils::IsReadable()) 
{	    
	ShowError(GetMessage('WIKI_ACCESS_DENIED'));
	return;    	
}

$_arParams = $arParams;

if($this->StartResultCache(false, array($USER->GetGroups(), $arCache), false))
{	
	$arParams['ELEMENT_NAME'] = urldecode($arParams['ELEMENT_NAME']);	       
	$arFilter = array(
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],        		
		'CHECK_PERMISSIONS' => 'N',
		'ACTIVE' => 'Y'
	);

	$bNotPage = false;  
	if (empty($arParams['ELEMENT_NAME']))
	{ 
		$bNotPage = true;
		$arParams['ELEMENT_NAME'] = CWiki::GetDefaultPage($arParams['IBLOCK_ID']);
	}

	$arResult['ELEMENT'] = array();  
	if (!empty($arParams['ELEMENT_NAME']) && ($arResult['ELEMENT'] = CWiki::getElementByName($arParams['ELEMENT_NAME'], $arFilter)) != false)
	{	
		$arParams['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];		
	}
	else 
	{
		if ($bNotPage || empty($arParams['ELEMENT_NAME'])) 
		{
			$arResult['ELEMENT']['NAME'] = !empty($arParams['ELEMENT_NAME']) ? $arParams['ELEMENT_NAME'] : GetMessage('WIKI_DEFAULT_PAGE_NAME');
			$arParams['ELEMENT_NAME'] = $arResult['ELEMENT']['NAME'];
			$arResult['ELEMENT']['~DETAIL_TEXT'] = GetMessage('WIKI_DEFAULT_PAGE_TEXT', array('%NAME%' => $arResult['ELEMENT']['NAME']));
			CWiki::SetDefaultPage($arParams['IBLOCK_ID'], $arResult['ELEMENT']['NAME']);
		} 
		else
		{  
			$arResult['ELEMENT']['NAME'] = $arParams['ELEMENT_NAME'];      		    
			$arResult['ELEMENT']['~DETAIL_TEXT'] = GetMessage(CWiki::GetDefaultPage($arParams['IBLOCK_ID']) == $arParams['ELEMENT_NAME'] ? 'WIKI_DEFAULT_PAGE_TEXT' : 'WIKI_PAGE_TEXT', array('%NAME%' => $arParams["ELEMENT_NAME"]));  
		}
		
		$arParams['ELEMENT_ID'] = 0;
		$arResult['ELEMENT']['ID'] = 0;    		    
		$arResult['WIKI_oper'] = 'add';  
	}    	    	

	if ($historyId > 0 && !empty($arResult['ELEMENT']['ID']))
	{

		$documentId = array('iblock', 'CWikiDocument', $arParams['ELEMENT_ID']);
		$arErrorsTmp = array();
		$arHistoryResult = CBPDocument::GetDocumentFromHistory($historyId, $arErrorsTmp);

		if (count($arErrorsTmp) > 0)
		{
			foreach ($arErrorsTmp as $e)
				$arResult['FATAL_MESSAGE'] .= $e['message'];
				
			$this->AbortResultCache();
			ShowError($arResult['FATAL_MESSAGE']);
			return; 				
		}

		$canRead = CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::ReadDocument,
			$GLOBALS['USER']->GetID(),
			$documentId,
			array('UserGroups' => $GLOBALS['USER']->GetUserGroupArray())
		);
		if (!$canRead)
		{
			$this->AbortResultCache();
			ShowError(GetMessage('WIKI_ACCESS_DENIED'));
			return;    	
		} 

		if (!empty($arHistoryResult))
		{            
			$history = new CBPHistoryService();
			$arFilter = array('DOCUMENT_ID' => $documentId);
			$dbResultList = $history->GetHistoryList(
				array('ID' => 'DESC'),
				$arFilter,
				false,
				false,
				array('ID')
			);

			$sTableID = 'tbl_bizproc_document_history';
			$rsHistory= new CDBResult($dbResultList, $sTableID);
			$iPrevHistoryId = 0;        		
			$iNextHistoryId = 0;
			$iCurHistoryId = 0;
			$arPrevHistory = array();        		
			$arNextHistory = array();    
			$bFirst = true; 		
			while($arHistory = $rsHistory->GetNext())  
			{ 
				if ($bFirst) 
				{
					$bFirst = false;
					$iCurHistoryId = $arHistory['ID'];
				}
				if ($arHistory['ID'] == $historyId)
				{
					$arPrevHistory = $rsHistory->GetNext();
					break;
				}
				$arNextHistory = $arHistory;
			}
			if (!empty($arPrevHistory))
				$iPrevHistoryId = $arPrevHistory['ID'];
			if (!empty($arNextHistory))
				$iNextHistoryId = $arNextHistory['ID'];                     

			$arResult['ELEMENT'] = $arHistoryResult['DOCUMENT']['FIELDS'];
			$arResult['ELEMENT']['~DETAIL_TEXT'] = $arResult['ELEMENT']['DETAIL_TEXT'];
			if (is_array($arHistoryResult['DOCUMENT']['PROPERTIES']['IMAGES']['VALUE']))
			{
				foreach ($arHistoryResult['DOCUMENT']['PROPERTIES']['IMAGES']['VALUE'] as $_sImg)
				{
					$arResult['ELEMENT']['IMAGES'][strtolower(bx_basename($_sImg))] = $_sImg;
				}
			}
			$arParams['ELEMENT_ID'] = $arHistoryResult['DOCUMENT']['FIELDS']['ID'];
            
			$rsUser = CUser::GetByID($arHistoryResult['USER_ID']);
			$arUser = $rsUser->Fetch();
				
			$arResult['VERSION'] = array();
			$arResult['VERSION']['USER_LOGIN'] = CWikiUtils::GetUserLogin($arUser);
			$arResult['VERSION']['MODIFIED'] = $arHistoryResult['MODIFIED'];
			if ($iCurHistoryId != $historyId)
			{
				$arResult['VERSION']['CUR_LINK'] = CHTTP::urlAddParams(
					CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], 
						array(
							'wiki_name' => urlencode($arParams['ELEMENT_NAME']), 
							'group_id' => CWikiSocnet::$iSocNetId
						)
					),
					array('oldid' => $iCurHistoryId)
				);
			}
			if (!empty($iPrevHistoryId))
			{
				$arResult['VERSION']['PREV_LINK'] = CHTTP::urlAddParams(
					CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], 
						array(
							'wiki_name' => urlencode($arParams['ELEMENT_NAME']), 
							'group_id' => CWikiSocnet::$iSocNetId
							)
						),
						array('oldid' => $iPrevHistoryId
					)
				);
			}
			if (!empty($iNextHistoryId))
			{
				$arResult['VERSION']['NEXT_LINK'] = CHTTP::urlAddParams(
					CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST'], 
						array(
							'wiki_name' => urlencode($arParams['ELEMENT_NAME']), 
							'group_id' => CWikiSocnet::$iSocNetId
						)
					),
					array('oldid' => $iNextHistoryId)
				);
			}        
	    
        	$arHp = array('oldid' => $historyId, 'sessid' => bitrix_sessid());
        	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N')	
        		$arHp[$arParams["OPER_VAR"]] = 'history';	
        	$arResult['VERSION']['CANCEL_LINK'] = CHTTP::urlAddParams(
        		CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_HISTORY'], 
        			array(
        				'wiki_name' => urlencode($arParams['ELEMENT_NAME']), 
        				'group_id' => CWikiSocnet::$iSocNetId
        			)
        		),
        		$arHp
        	);		    
		}
	}
	
	$CACHE_MANAGER->StartTagCache($this->GetCachePath());
	$CACHE_MANAGER->RegisterTag('wiki_'.$arParams['ELEMENT_ID']);
	$CACHE_MANAGER->EndTagCache();		
	
	/*$arPages = array('article');
	if (isset($arResult['WIKI_oper']) && $arResult['WIKI_oper'] == 'add')
	    $arPages[] = 'add';
	$arResult['TOPLINKS'] = CWikiUtils::getRightsLinks($arPages, $arParams);*/

	$CWikiParser = new CWikiParser();
	$CWikiSecurity = new CWikiSecurity();
	$CWikiSecurity->clear($arResult['ELEMENT']['~DETAIL_TEXT']);    	
	$arResult['ELEMENT']['DETAIL_TEXT'] =  $CWikiParser->Parse($arResult['ELEMENT']['~DETAIL_TEXT'], $arResult['ELEMENT']['DETAIL_TEXT_TYPE'], $arResult['ELEMENT']['IMAGES']);	

	$SERVICE_PAGE_NAME = '';
	$arResult['SERVICE_PAGE'] = CWikiUtils::CheckServicePage($arParams['ELEMENT_NAME'], $SERVICE_PAGE_NAME);	
                
	$this->IncludeComponentTemplate();	
}

$arParams = $_arParams;
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/wiki/include/nav.php');

unset($GLOBALS['arParams']);
return $arResult['ELEMENT']['ID']; 

?>