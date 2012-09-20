<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$arParams['IN_COMPLEX'] = 'N';
if (($arParent =  $this->GetParent()) !== NULL)
    $arParams['IN_COMPLEX'] = 'Y';
if(empty($arParams['PAGE_VAR']))
	$arParams['PAGE_VAR'] = 'title';
if(empty($arParams['OPER_VAR']))
	$arParams['OPER_VAR'] = 'oper';

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
if(empty($arParams['PATH_TO_POST']))
	$arParams['PATH_TO_POST'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");
	
$arParams['PATH_TO_POST_EDIT'] = trim($arParams['PATH_TO_POST_EDIT']);
if(strlen($arParams['PATH_TO_POST_EDIT'])<=0)
{
	$arParams['PATH_TO_POST_EDIT'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams["PATH_TO_POST_EDIT"] = $this->GetParent()->arResult['PATH_TO_POST_EDIT'];	
}

$arParams['PATH_TO_HISTORY'] = trim($arParams['PATH_TO_HISTORY']);
if(strlen($arParams['PATH_TO_HISTORY'])<=0)
	$arParams['PATH_TO_HISTORY'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");

$arParams['PATH_TO_HISTORY_DIFF'] = trim($arParams['PATH_TO_HISTORY_DIFF']);
if(strlen($arParams['PATH_TO_HISTORY_DIFF'])<=0)
	$arParams['PATH_TO_HISTORY_DIFF'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");		
	
$arParams['PATH_TO_DISCUSSION'] = trim($arParams['PATH_TO_DISCUSSION']);
if(strlen($arParams['PATH_TO_DISCUSSION'])<=0) {
	$arParams['PATH_TO_DISCUSSION'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[PAGE_VAR]=#wiki_name#");
}
	
$arParams['PATH_TO_CATEGORY'] = trim($arParams['PATH_TO_POST']);	

$arParams['PATH_TO_CATEGORIES'] = trim($arParams['PATH_TO_CATEGORIES']);
if(strlen($arParams['PATH_TO_CATEGORIES'])<=0)
	$arParams['PATH_TO_CATEGORIES'] = htmlspecialchars($APPLICATION->GetCurPage()."?$arParams[OPER_VAR]=categories");
	
$arParams['PATH_TO_USER'] = trim($arParams['PATH_TO_USER']);	
if(strlen($arParams['PATH_TO_USER'])<=0)
{
	if ($arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'Y')
		$arParams['PATH_TO_USER'] = $this->GetParent()->arParams['PATH_TO_USER'];	
}	
	
$arResult['PREVIEW'] = !empty($_POST['preview']) && $_POST['preview'] == 'Y' ? 'Y' : 'N';
$arResult['IMAGE_UPLOAD'] = isset($_GET['image_upload']) || ($_POST['do_upload']) ? 'Y' : 'N';
$arResult['INSERT_LINK'] = isset($_GET['insert_link']) ? 'Y' : 'N';
$arResult['INSERT_IMAGE'] = isset($_GET['insert_image']) ? 'Y' : 'N';
$arResult['INSERT_CATEGORY'] = isset($_GET['insert_category']) ? 'Y' : 'N';
$arResult['LOAD_EDITOR'] = isset($_GET['load_editor']) ? 'Y' : 'N';


$arResult['WIKI_oper'] = 'edit';
if (isset($_REQUEST[$arParams['OPER_VAR']]))
	$arResult['WIKI_oper'] = $_REQUEST[$arParams['OPER_VAR']];

$GLOBALS['arParams'] = $arParams;

if (!CModule::IncludeModule('wiki'))
{
	ShowError(GetMessage('WIKI_MODULE_NOT_INSTALLED'));
	return;
}

$arResult['ALLOW_HTML'] = CWikiUtils::isAllowHTML() ? 'Y' : 'N';

if(!CModule::IncludeModule('iblock'))
{
	ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

if (IsModuleInstalled('search'))
	AddEventHandler('search', 'BeforeIndex', array('CWikiUtils', 'OnBeforeIndex'));

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

$arResult['SOCNET'] = false;
if (CWikiSocnet::isEnabledSocnet() && !empty($arParams['SOCNET_GROUP_ID'])) 
{
	if (!CWikiSocnet::Init($arParams['SOCNET_GROUP_ID'], $arParams["IBLOCK_ID"])) 
	{
		ShowError(GetMessage('WIKI_SOCNET_INITIALIZING_FAILED'));
		return;
	}
	$arResult['SOCNET'] = true;
}   

if (!CWikiUtils::IsWriteable() || ($arResult['WIKI_oper'] == 'delete' && !CWikiUtils::IsDeleteable())) 
{	
	ShowError(GetMessage('WIKI_ACCESS_DENIED'));
	return; 
}

$CWiki = new CWiki();
$arParams['ELEMENT_NAME'] = urldecode($arParams['ELEMENT_NAME']);	
$arFilter = array(
	'IBLOCK_ID' => $arParams['IBLOCK_ID'],        		
	'CHECK_PERMISSIONS' => 'N'
);    	

if (empty($arParams['ELEMENT_NAME'])) 
	$arParams['ELEMENT_NAME'] = CWiki::GetDefaultPage($arParams['IBLOCK_ID']);	
$arResult['ELEMENT'] = array(); 

if ($arResult['WIKI_oper'] == 'delete') 
{
	$arResult['ELEMENT'] = CWiki::getElementById($arParams['ELEMENT_NAME'], $arFilter);
	$arParams['ELEMENT_NAME'] = $arResult['ELEMENT']['NAME'];
}

$bNotPage = true;

if (!empty($arParams['ELEMENT_NAME']) && ($arResult['ELEMENT'] = CWiki::getElementByName($arParams['ELEMENT_NAME'], $arFilter)) != false)
{
	$arParams['ELEMENT_ID'] = $arResult['ELEMENT']['ID'];
	if ($arResult['WIKI_oper'] != 'delete')
	{    	
		if ($arResult['ELEMENT']['ACTIVE'] == 'N')
			$arResult['WIKI_oper'] = 'add';		
		elseif ($arResult['WIKI_oper'] == 'add')
			$bNotPage = false;
	}
}
else
	$arResult['WIKI_oper'] = 'add';	

// localize the name of the stream	
$sPageName = $arParams['ELEMENT_NAME'];
$sCatName = '';
if (CWikiUtils::IsCategoryPage($sPageName, $sCatName))
	$sPageName = preg_replace('/^category:/i'.BX_UTF_PCRE_MODIFIER, GetMessage('CATEGORY_NAME').':', $sPageName); 	
	
CUtil::InitJSCore(array('window', 'ajax'));

if (empty($arResult['ELEMENT']) || !$bNotPage)
{
	if ($arResult['WIKI_oper'] == 'add') 
	{
		// Check name
		if (!$bNotPage)
		{
			$i = 2;
			$strName = $arParams['ELEMENT_NAME']." ($i)";
			while(CWiki::getElementByName($strName, $arFilter) !== false)
			{				
				$i++;
				$strName = $arParams['ELEMENT_NAME']." ($i)";
			}
			$arParams['ELEMENT_NAME'] = $strName;
		}	
 		
		// Create a temporary item	
		$arFields=array(
			'NAME'		=> htmlspecialchars(CWikiUtils::htmlspecialcharsback($arParams['ELEMENT_NAME'])),
			'IBLOCK_ID'	=> $arParams['IBLOCK_ID'],
			'IBLOCK_TYPE'	=> $arParams['IBLOCK_TYPE'],
			'DETAIL_TEXT_TYPE' => $arResult["ALLOW_HTML"] == 'Y' ? 'html' : 'text',
			'DETAIL_TEXT' => GetMessage('WIKI_DEFAULT_DETAIL_TEXT', array(
				'%HEAD%' => $arResult["ALLOW_HTML"] == 'Y' ? '<h1>'.htmlspecialchars(CWikiUtils::htmlspecialcharsback($sPageName, false)).'</h1>' : '= '.htmlspecialchars(CWikiUtils::htmlspecialcharsback($sPageName, false)).' =', 
				'%NEWLINE%' => $arResult["ALLOW_HTML"] == 'Y' ? '<br />' : "\n"
			)),
			'~DETAIL_TEXT' => GetMessage('WIKI_DEFAULT_DETAIL_TEXT', array(
				'%HEAD%' => $arResult['ALLOW_HTML'] == 'Y' ? '<h1>'.htmlspecialchars(CWikiUtils::htmlspecialcharsback($sPageName,false)).'</h1>' : '= '.htmlspecialchars(CWikiUtils::htmlspecialcharsback($sPageName, false)).' =',  
				'%NEWLINE%' => $arResult['ALLOW_HTML'] == 'Y' ? '<br />' : "\n"
			)),
			'ACTIVE' => 'N'
		);	

		$arParams['ELEMENT_ID'] = $CWiki->Add($arFields);
		
		$arResult['ELEMENT'] = 	$arFields;
		$arResult['ELEMENT']['ID'] = $arParams['ELEMENT_ID'];			   	
	}
	else 
	{
		$arResult['ELEMENT']['NAME'] = $arParams['ELEMENT_NAME'];
		$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_PAGE_NOT_FIND');
	}
	$arResult['WIKI_oper'] = 'edit';	
}			

$arResult['ELEMENT']['NAME_LOCALIZE'] = $sPageName;
$arResult['PAGE_VAR'] = $arParams['PAGE_VAR'];
$arResult['OPER_VAR'] = $arParams['OPER_VAR'];
$arResult['PATH_TO_DELETE'] = CHTTP::urlAddParams(
	CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'], 
		array(
			'wiki_name' => $arResult['ELEMENT']['ID'], 
			'group_id' => CWikiSocnet::$iSocNetId
		)
	),
	$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams["OPER_VAR"] => 'delete') : array()
);

if ($arResult['INSERT_LINK'] == 'Y' || $arResult['INSERT_IMAGE'] == 'Y' || $arResult['LOAD_EDITOR'] == 'Y'
	|| $arResult['INSERT_CATEGORY'] == 'Y' || ($_SERVER['REQUEST_METHOD'] != 'POST'  && $arResult['WIKI_oper'] == 'delete'))
{
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache"); 
	if ($arResult['INSERT_CATEGORY'] == 'Y')
	{ 	
		if (CWikiSocnet::IsSocNet())
		{    		    
		    $arFilter['>LEFT_BORDER'] = CWikiSocnet::$iCatLeftBorder;
		    $arFilter['<RIGHT_BORDER'] = CWikiSocnet::$iCatRightBorder;
		}
      
		$rsTree = CIBlockSection::GetList(Array('left_margin' => 'asc'), $arFilter);
		$arTree = array('-1' => GetMessage('WIKI_CHOISE_CATEGORY'));
		$_iLevel = 0;
		while($arElement = $rsTree->GetNext()) 
		{
			$_iLevel = (int)$arElement['DEPTH_LEVEL'] - (CWikiSocnet::IsSocNet() ? 2 : 1);
			$_sSeparator = '';
			if ($_iLevel > 0)
				$_sSeparator  = str_pad('', $_iLevel, '--'); 
			$arTree[$arElement['NAME']] = $_sSeparator.$arElement['NAME'];       
		}
		$arResult['TREE'] = $arTree;
	}
}    		
else if ($arResult['IMAGE_UPLOAD'] == 'Y')
{
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
	if (isset($_POST["do_upload"])) 
	{
		if (!empty($_FILES["FILE_ID"]) && $_FILES["FILE_ID"]["size"] > 0)
		{	    		    
			if (CFile::IsImage($_FILES['FILE_ID']['name'])) 
			{    		    		
				$_imgID = $CWiki->addImage($arParams['ELEMENT_ID'], $arParams['IBLOCK_ID'], $_FILES['FILE_ID']);
				$rsFile = CFile::GetByID($_imgID);
				$arFile = $rsFile->Fetch();				
				$arResult['IMAGE'] = array(
					'ID' => $_imgID,
					'ORIGINAL_NAME' => $arFile['ORIGINAL_NAME'],
					'FILE_SHOW' => CFile::ShowImage($_imgID, 100, 100, "id=\"$_imgID\" border=\"0\" style=\"cursor:pointer;\" onclick=\"doInsert(\'[File:".CUtil::JSEscape($arFile['ORIGINAL_NAME'])."]\',\'\',false, \'$_imgID\')\" title=\"".GetMessage('WIKI_IMAGE_INSERT')."\"")
				);
			}
			else
				$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_IMAGE_UPLOAD_ERROR');							
		} 
		else
			$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_IMAGE_UPLOAD_ERROR');
	}
} 
else 
{		
	//$arResult['TOPLINKS'] = CWikiUtils::getRightsLinks(array('article', $arResult['WIKI_oper']), $arParams);

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && ((isset($_POST['apply']) || isset($_POST['save']) && !isset($_POST['preview'])))) 
	{ 
		if (check_bitrix_sessid())
		{
			// checking the data entered
			$arFields = Array();	

			switch ($arResult['WIKI_oper'])
			{
				case 'edit':
				case 'add':
				default: //add				
					
					$arFields = array(
						'DETAIL_TEXT'		=> $arResult["ALLOW_HTML"] == 'Y' && $_POST['POST_MESSAGE_TYPE'] == 'html' ? $_POST['POST_MESSAGE_HTML'] : $_POST['POST_MESSAGE'],
						'DETAIL_TEXT_TYPE'	=> $arResult["ALLOW_HTML"] == 'Y' ? $_POST['POST_MESSAGE_TYPE'] : 'text',
						'TAGS' => $_POST['TAGS'],						
						'IBLOCK_ID'		=> $arParams['IBLOCK_ID'],
						'IBLOCK_TYPE'		=> $arParams['IBLOCK_TYPE'],
						'ACTIVE' => 'Y'
					);		
					
					if (isset($_POST['POST_TITLE']))
					{
						$arFields['NAME'] = $_POST['POST_TITLE'];
						if (empty($_POST['POST_TITLE']))
							$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_ERROR_NAME_EMPTY');  					
						else if (strpos($_POST['POST_TITLE'], '/') !== false)
							$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_ERROR_NAME_BAD_SYMBOL');   					
						else 
						{ 	
							if (!$CWiki->Rename($arParams['ELEMENT_ID'], $arFields)) 
								$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_ERROR_RENAME');
							else 
								$arParams['ELEMENT_NAME'] = $_POST['POST_TITLE'];	
						}					
					}

					$arResult['ELEMENT']['NAME'] = htmlspecialchars(CWikiUtils::htmlspecialcharsback($_POST['POST_TITLE']));  
     
					if (empty($arResult['ERROR_MESSAGE']))
					{
						if(is_array($_POST['IMAGE_ID_del']))
						{
							foreach($_POST['IMAGE_ID_del'] as $_imgID => $_)
							{
								if (in_array($_imgID, $arResult['ELEMENT']['IMAGES']))
								{	
                        		    $rsFile = CFile::GetByID($_imgID);
                        			$arFile = $rsFile->Fetch();								    
									$CWiki->deleteImage($_imgID, $arResult['ELEMENT']['ID'], $arParams['IBLOCK_ID']);
									$arFields['DETAIL_TEXT'] = preg_replace('/\[?\[(:)?(File|'.GetMessage('FILE_NAME').'):('.$_imgID.'|'.preg_quote($arFile['ORIGINAL_NAME'], '/').')\]\]?/iU'.BX_UTF_PCRE_MODIFIER, '', $arFields['DETAIL_TEXT']);
								}
							}
						}							
	
						$CWiki->Update($arParams['ELEMENT_ID'], $arFields);
						
						if(CWikiSocnet::IsSocNet())
						{
							$parser = new CSocNetTextParser();
							$text4message = $parser->html_cut($arFields['DETAIL_TEXT'], 200);
							
							$arSoFields = Array(
								'ENTITY_TYPE' 		=> SONET_SUBSCRIBE_ENTITY_GROUP,
								'ENTITY_ID' 		=> intval($arParams['SOCNET_GROUP_ID']),
								'EVENT_ID' 			=> 'wiki',
								'USER_ID' 			=> $GLOBALS['USER']->GetID(),			
								'=LOG_DATE' 		=> $GLOBALS['DB']->CurrentTimeFunction(),
								'TITLE_TEMPLATE' 	=> GetMessage('WIKI_SONET_LOG_TITLE_TEMPLATE'),
								'TITLE' 			=> $arFields['NAME'],
								'MESSAGE' 			=> $text4message,
								'TEXT_MESSAGE' 		=> '',
								'MODULE_ID' 		=> 'wiki',
								'URL'				=> str_replace(
														array('#group_id#', '#wiki_name#'), 
														array(intval($arParams['SOCNET_GROUP_ID']), urlencode($arFields['NAME'])),
														$arParams['~PATH_TO_POST']
													),
								'CALLBACK_FUNC' 	=> false
							);
							
							$logID = CSocNetLog::Add($arSoFields, false);

							if (intval($logID) > 0)
								CSocNetLog::Update($logID, array('TMP_ID' => $logID));

							CSocNetLog::SendEvent($logID, 'SONET_NEW_EVENT', $logID);
						}
					}
					$arResult['ELEMENT'] = $arFields + $arResult['ELEMENT'];
					break;
				case 'edit_title':
					break;			
				case 'delete':
				
					if(CWikiSocnet::IsSocNet())
					{
						$dbResTmp = CIBlockElement::GetByID($arParams['ELEMENT_ID']);
						if($arResTmp = $dbResTmp->GetNext())
							$strTitleTmp = $arResTmp['NAME'];
					}
				
					$CWiki->Delete($arParams['ELEMENT_ID'], $arParams['IBLOCK_ID']);
					
					if(
						CWikiSocnet::IsSocNet()
						&& strlen($strTitleTmp) > 0
					)
					{
						$arSoFields = Array(
							'ENTITY_TYPE' 		=> SONET_SUBSCRIBE_ENTITY_GROUP,
							'ENTITY_ID' 		=> intval($arParams['SOCNET_GROUP_ID']),
							'EVENT_ID' 			=> 'wiki_del',
							'USER_ID' 			=> $GLOBALS['USER']->GetID(),			
							'=LOG_DATE' 		=> $GLOBALS['DB']->CurrentTimeFunction(),
							'TITLE_TEMPLATE' 	=> GetMessage('WIKI_DEL_SONET_LOG_TITLE_TEMPLATE'),
							'TITLE' 			=> $strTitleTmp,
							'MESSAGE' 			=> '',
							'TEXT_MESSAGE' 		=> '',
							'MODULE_ID' 		=> 'wiki',
							'URL'				=> '',
							'CALLBACK_FUNC' 	=> false
						);
							
						$logID = CSocNetLog::Add($arSoFields, false);

						if (intval($logID) > 0)
							CSocNetLog::Update($logID, array('TMP_ID' => $logID));

						CSocNetLog::SendEvent($logID, 'SONET_NEW_EVENT', $logID);
					}
					
					break;
			}
					
			if (empty($arResult['ERROR_MESSAGE'])) 
			{	            		    
				if (!isset($_POST['apply'])) 				    
					LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST'], 
						array(
							'wiki_name' => urlencode($arParams['ELEMENT_NAME']), 
							'group_id' => CWikiSocnet::$iSocNetId
						))
					);
				else 
					LocalRedirect(CHTTP::urlAddParams(CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'], 
						array(
							'wiki_name' => urlencode($arParams['ELEMENT_NAME']), 
							'group_id' => CWikiSocnet::$iSocNetId
						)),
						$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => $arResult['WIKI_oper']) : array())
					);
			} 
			else
			    $arParams['ELEMENT_NAME'] = $arResult['ELEMENT']['NAME']; 					
		}
		else 			
			$arResult['ERROR_MESSAGE'] = GetMessage('WIKI_SESS_TIMEOUT');			
	} 
	else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['preview']))
	{	    
		$arResult['ELEMENT']['~DETAIL_TEXT'] = $arResult['ALLOW_HTML'] == 'Y' && $_POST['POST_MESSAGE_TYPE'] == 'html' ? $_POST['POST_MESSAGE_HTML'] : $_POST['POST_MESSAGE'];
		$arResult['ELEMENT']['DETAIL_TEXT_TYPE'] = $arResult['ALLOW_HTML'] == 'Y' ? $_POST['POST_MESSAGE_TYPE'] : 'text';		        		    

		$arResult['PREVIEW'] = 'Y';
		$arResult['ELEMENT_PREVIEW'] = array();
		$CWikiParser = new CWikiParser();
		$CWikiSecurity = new CWikiSecurity();
		$CWikiSecurity->clear($arResult['ELEMENT']['~DETAIL_TEXT']);
		$arCat = array();
		$arResult['ELEMENT_PREVIEW']['DETAIL_TEXT'] = $CWikiParser->parseBeforeSave($arResult['ELEMENT']['~DETAIL_TEXT'], $arCat);
		$arResult['ELEMENT_PREVIEW']['DETAIL_TEXT'] = $CWikiParser->Parse($arResult['ELEMENT_PREVIEW']['DETAIL_TEXT'], $arResult['ELEMENT']['DETAIL_TEXT_TYPE'], $arResult['ELEMENT']['IMAGES']);
		$arResult['ELEMENT']['TAGS'] = htmlspecialchars($_POST['TAGS']);
		$arResult['ELEMENT']['~TAGS'] = htmlspecialchars($_POST['TAGS']);
	}
	
	$CWikiSecurity = new CWikiSecurity();
	$CWikiSecurity->clear($arResult['ELEMENT']['~DETAIL_TEXT']);	
	// obtain a list of pictures page
	$arResult['IMAGES'] = array();
	if (!empty($arResult['ELEMENT']['IMAGES']))
	{
		foreach ($arResult['ELEMENT']['IMAGES'] as $_imgID)
		{
			$rsFile = CFile::GetByID($_imgID);
			$arFile = $rsFile->Fetch();
			$aImg = array();
			$aImg['ID'] = $_imgID;
			$aImg['ORIGINAL_NAME'] = $arFile['ORIGINAL_NAME'];
			$aImg['FILE_SHOW'] = CFile::ShowImage($_imgID, 100, 100, "id=\"$_imgID\" border=\"0\" style=\"cursor:pointer;\" onclick=\"doInsert('[File:".CUtil::JSEscape($arFile['ORIGINAL_NAME'])."]','',false, '$_imgID')\" title='".GetMessage('WIKI_IMAGE_INSERT')."'");
			$arResult['IMAGES'][] = $aImg;	
		}
	}

	include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/wiki/include/nav.php');

	$arResult['PATH_TO_POST_EDIT'] = CHTTP::urlAddParams(
		CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'], 
			array(
				'wiki_name' => urlencode($arParams['ELEMENT_NAME']), 
				'group_id' => CWikiSocnet::$iSocNetId
			)
		),
		$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => $arResult['WIKI_oper']) : array()
	);
}

$this->IncludeComponentTemplate();
unset($GLOBALS['arParams']);

?>