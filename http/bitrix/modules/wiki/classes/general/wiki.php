<?php

IncludeModuleLangFile(__FILE__);

class CWiki
{	
	/**
	 * 
	 * 
	 * @var CIBlockElement
	 */
	var $cIB_E = null;
	
	function __construct()
	{
		$this->cIB_E = new CIBlockElement();
	}
	
	function Add($arFields)
	{
		global $USER;
	    		
		$arFields['XML_ID'] = $arFields['NAME'];

		$arCats = array();
		$CWikiParser = new CWikiParser();
		$arFields['DETAIL_TEXT'] = $CWikiParser->parseBeforeSave($arFields['DETAIL_TEXT'], $arCats);
		if (CWikiSocnet::IsSocNet())	   
			$arFields['IBLOCK_SECTION_ID'] = CWikiSocnet::$iCatId;		    
	    			
		//add item		
		$ID = $this->cIB_E->Add($arFields);

		//serve category / bindings
		$this->UpdateCategory($ID, $arFields['IBLOCK_ID'], $arCats);
		
		//$this->UpdateHistory($ID, $arFields['IBLOCK_ID']);
		
		return $ID;
	}
	
	function Update($ID, $arFields)
	{
		global $USER, $CACHE_MANAGER;
				
		$arCats = array();
		$CWikiParser = new CWikiParser();
		$arFields['DETAIL_TEXT'] = $CWikiParser->parseBeforeSave($arFields['DETAIL_TEXT'], $arCats);
		
		//save item	
		$this->cIB_E->Update($ID, $arFields);		
		$CACHE_MANAGER->ClearByTag('wiki_'.$ID);
		
		//serve category / bindings
		$this->UpdateCategory($ID, $arFields['IBLOCK_ID'], $arCats);
		
		$this->UpdateHistory($ID, $arFields['IBLOCK_ID']);
		
		return true;
	}
	
	function Recover($HISTORY_ID, $ID, $IBLOCK_ID)
	{
		$rIBlock = CIBlock::getList(Array(), array('ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N'));
		$arIBlock = $rIBlock->GetNext();	    
		if ($arIBlock['BIZPROC'] == 'Y' && CModule::IncludeModule('bizproc')) 
		{
			if (CBPHistoryService::RecoverDocumentFromHistory($HISTORY_ID))
			{	    	               
				if ($this->UpdateHistory($ID, $IBLOCK_ID))
					return true;
				else     	        
					return false;
			}
			else 
				return false;
		} 
		else 
		    return false;
	}
	
	function UpdateHistory($ID, $IBLOCK_ID)
	{
		global $USER;
	    
		$rIBlock = CIBlock::getList(Array(), array('ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N'));
		$arIBlock = $rIBlock->GetNext();	    
	    
		// add changes history		
		if ($arIBlock['BIZPROC'] == 'Y' && CModule::IncludeModule('bizproc')) 
		{
			$cRuntime = CBPRuntime::GetRuntime();
			$cRuntime->StartRuntime();			
			$documentService = $cRuntime->GetService('DocumentService');
	
			$historyIndex = CBPHistoryService::Add(
				array(
					'DOCUMENT_ID' => array('iblock', 'CWikiDocument', $ID),
					'NAME' => 'New',
					'DOCUMENT' => null,
					'USER_ID' => $USER->GetID()
				)
			);
	
			$arDocument = $documentService->GetDocumentForHistory(array('iblock', 'CWikiDocument', $ID), $historyIndex);
			if (is_array($arDocument))
			{			
				CBPHistoryService::Update(
					$historyIndex,
					array(
						'NAME' => $arDocument['NAME'],
						'DOCUMENT' => $arDocument,
					)
				);		
			}
			return true;
		}	    
		return false;
	}
	
	function UpdateCategory($ID, $IBLOCK_ID, $arCats)
	{	
		global $CACHE_MANAGER;
	    
		$arFilter = array(
			'IBLOCK_ID' => $IBLOCK_ID,        		
			'CHECK_PERMISSIONS' => 'N'
		);	    
		$arElement = self::GetElementById($ID, $arFilter);
	    
		$bCategoryPage = false;
		$sCatName = '';
		$arCatsID = array();  
		if (CWikiUtils::IsCategoryPage($arElement['NAME'], $sCatName))
			$bCategoryPage = true;	    

		if ($bCategoryPage) 
		{
			// get current category
			$arFilter =  array('NAME' => $sCatName, 'IBLOCK_ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N');
			if (CWikiSocnet::IsSocNet())
			{ 
				$arFilter['>LEFT_BORDER'] = CWikiSocnet::$iCatLeftBorder;
				$arFilter['<RIGHT_BORDER'] = CWikiSocnet::$iCatRightBorder;
			}			    
			$rsCurCats = CIBlockSection::GetList(array(), $arFilter);
			$arCurCat = $rsCurCats->GetNext();  
					
			if (empty($arCurCat)) 
			{
				$CIB_S = new CIBlockSection();
				$_arFields = array();
				$_arFields['IBLOCK_ID'] = $IBLOCK_ID;
				$_arFields['ACTIVE'] = 'Y';
				$_arFields['NAME'] = $sCatName;
				$_arFields['XML_ID'] = $sCatName;
				if (CWikiSocnet::IsSocNet()) 
					$_arFields['IBLOCK_SECTION_ID'] = CWikiSocnet::$iCatId;	
				$iCurCatID = $CIB_S->Add($_arFields);
				if ($iCurCatID != false)
					$arCatsID[] = $iCurCatID;     		    
			} 
			else 
			{
				$iCurCatID = $arCurCat['ID'];
				$arCatsID[] = $arCurCat['ID'];
			}
				
			// Page bind only to this category            
			CIBlockElement::SetElementSection($ID, $arCatsID);

			$CIB_S = new CIBlockSection(); 
			if (!empty($arCats))
			{			   
				// Nova create a category if it still has no
				$arFilter =  array('NAME' => $arCats[0], 'IBLOCK_ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N');
				if (CWikiSocnet::IsSocNet())
				{ 
					$arFilter['>LEFT_BORDER'] = CWikiSocnet::$iCatLeftBorder;
					$arFilter['<RIGHT_BORDER'] = CWikiSocnet::$iCatRightBorder;
				}			    
				$rsCats = CIBlockSection::GetList(array(), $arFilter);
				$arCat = $rsCats->GetNext();  
				             
				if (empty($arCat)) 
				{        		    
					$_arFields = array();
					$_arFields['IBLOCK_ID'] = $IBLOCK_ID;
					$_arFields['ACTIVE'] = 'Y';
					$_arFields['NAME'] = $arCats[0];
					$_arFields['XML_ID'] = $arCats[0];
					$_arFields['CHECK_PERMISSIONS'] = 'N';
					if (CWikiSocnet::IsSocNet()) 
						$_arFields['IBLOCK_SECTION_ID'] = CWikiSocnet::$iCatId;	
					   
					$iCatID = $CIB_S->Add($_arFields);     		    
				}
				else 
					$iCatID = $arCat['ID'];

				$_arFields = array();
				$_arFields['IBLOCK_ID'] = $IBLOCK_ID;
				$_arFields['ACTIVE'] = 'Y';
				$_arFields['IBLOCK_SECTION_ID'] = $iCatID;			
				// current category doing this subcategory
				$CIB_S->Update($iCurCatID, $_arFields);
			} 
			else
			{
				$_arFields = array();
				$_arFields['IBLOCK_ID'] = $IBLOCK_ID;
				$_arFields['ACTIVE'] = 'Y';
				$_arFields['IBLOCK_SECTION_ID'] = 0;
				if (CWikiSocnet::IsSocNet()) 
					$_arFields['IBLOCK_SECTION_ID'] = CWikiSocnet::$iCatId;		
				// bind to the root category
				$CIB_S->Update($iCurCatID, $_arFields);	   
			}                       
		}
		else 
		{
			$arExistsCatsId = array();
			$arDelCatId = array();
			$rsSect = CIBlockElement::GetElementGroups($ID, false);
			$arResult['SECTIONS'] = array(); 
			while($arSect = $rsSect->GetNext()) 
				$arExistsCatsId[] = $arSect['ID'];	    

			if (!empty($arCats))
			{    		    
				$arFilter =  array('NAME' => $arCats, 'IBLOCK_ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N');
				if (CWikiSocnet::IsSocNet())
				{ 
					$arFilter['>LEFT_BORDER'] = CWikiSocnet::$iCatLeftBorder;
					$arFilter['<RIGHT_BORDER'] = CWikiSocnet::$iCatRightBorder;
				}			    
				$rsCats = CIBlockSection::GetList(array(), $arFilter);
				while($arCat = $rsCats->GetNext())
				{
					$arExiststInBlockCats[] = $arCat['~NAME'];
					$arCatsID[] = $arCat['ID'];   			
				}

				$CIB_S = new CIBlockSection();
				foreach ($arCats as $sCatName) 
				{
					if (!in_array($sCatName, $arExiststInBlockCats))
					{
						$_arFields = array();
						$_arFields['IBLOCK_ID'] = $IBLOCK_ID;
						$_arFields['ACTIVE'] = 'Y';
						$_arFields['NAME'] = $sCatName;
						$_arFields['XML_ID'] = $sCatName;
						$_arFields['CHECK_PERMISSIONS'] = 'N';            		    
						if (CWikiSocnet::IsSocNet()) 
							$_arFields['IBLOCK_SECTION_ID'] = CWikiSocnet::$iCatId;	
						$iCatID = $CIB_S->Add($_arFields);
						if ($iCatID != false)
							$arCatsID[] = $iCatID; 
					}
				}    
	    
				//bind to the item
				if (!empty($arCatsID))
				{
					if (CWikiSocnet::IsSocNet()) 
						$arCatsID[] = CWikiSocnet::$iCatId;	    		    
					CIBlockElement::SetElementSection($ID, $arCatsID); 
				}
			} 
			else
			{
				$arCatsID = array();
				if (CWikiSocnet::IsSocNet()) 
					$arCatsID = CWikiSocnet::$iCatId;	            
				CIBlockElement::SetElementSection($ID, $arCatsID); 
			}		
	    
			if (is_array($arCatsID))
				$arDelCatId = array_diff($arExistsCatsId, $arCatsID);
	    
			if (!empty($arDelCatId))
			{
				foreach ($arDelCatId as $_iCatId) 
				{
					if (CIBlockSection::GetCount(array('SECTION_ID' => $_iCatId, 'IBLOCK_ID' => $IBLOCK_ID) == 0)) 
						CIBlockSection::Delete($_iCatId);
				}
			}
		}
		$CACHE_MANAGER->ClearByTag('wiki_'.$ID);    
	}
	
	function Delete($ID, $IBLOCK_ID)
	{	
		global $CACHE_MANAGER;
		$rIBlock = CIBlock::getList(Array(), array('ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N'));
		$arIBlock = $rIBlock->GetNext();
	
		// erase the history of changes		
		if ($arIBlock['BIZPROC'] == 'Y' && CModule::IncludeModule('bizproc')) 
		{	
			$historyService = new CBPHistoryService();
			$historyService->DeleteHistoryByDocument(array('iblock', 'CWikiDocument', $ID));
		}
		$arElement = self::GetElementById($ID, array('IBLOCK_ID' => $IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N'));
		// delete item
		$bResult = $this->cIB_E->Delete($ID);				
		
		$CACHE_MANAGER->ClearByTag('wiki_'.$ID);
		return $bResult;
	}	
	
	function AddImage($ID, $IBLOCK_ID, $arImage)
	{
		$arProperties = array();
		$arCurImages = array();		
		$arCurImagesNew = array();		
		$arAddImage = array();		
		$rsProperties = CIBlockElement::GetProperty($IBLOCK_ID, $ID, 'value_id', 'asc', array('ACTIVE' => 'Y', 'CODE' => 'IMAGES'));
		while($arProperty = $rsProperties->Fetch())
		{
			if($arProperty['CODE'] == 'IMAGES') 
			{
				$arProperties['IMAGES'] = $arProperty;
				$arCurImages[] = $arProperty['VALUE'];
			}
		}

		$obProperty = new CIBlockProperty();
		$res = true;
		if(!array_key_exists('IMAGES', $arProperties))
		{
			$res = $obProperty->Add(array(
				'IBLOCK_ID' => $IBLOCK_ID,
				'ACTIVE' => 'Y',
				'PROPERTY_TYPE' => 'F',
				'MULTIPLE' => 'Y',
				'NAME' => 'Images',
				'CODE' => 'IMAGES'
			));
		}		
		
		$arFields = array();		
		
		CFile::ResizeImage($arImage, array(
			'width' => COption::GetOptionString('wiki', 'image_max_width', 600), 
			'height' => COption::GetOptionString('wiki', 'image_max_height', 600)
		));		                                            

		$arFields['PROPERTY_VALUES'] = array('IMAGES' => $arImage);				
		$arFields['BLOCK_ID'] = $IBLOCK_ID;
		$arFields['ELEMENT_ID'] = $ID;		
		
		$this->cIB_E->Update($ELEMENT_ID, $arFields);

		$rsProperties = CIBlockElement::GetProperty($IBLOCK_ID, $ID, 'value_id', 'asc', array('ACTIVE' => 'Y', 'CODE' => 'IMAGES'));
		while($arProperty = $rsProperties->Fetch())
		{
			if($arProperty['CODE'] == 'IMAGES') 
				$arCurImagesNew[] = $arProperty['VALUE'];
		}		
		
		$arAddImage = array_diff($arCurImagesNew, $arCurImages);
		list(, $imgId) = each($arAddImage);
		return $imgId;
	}
	
	function DeleteImage($IMAGE_ID, $ID, $IBLOCK_ID)
	{		
		$rsProperties = CIBlockElement::GetProperty($IBLOCK_ID, $ID, 'value_id', 'asc', array('ACTIVE' => 'Y', 'CODE' => 'IMAGES'));
		$_iPropertyId = 0;
		while($arProperty = $rsProperties->Fetch())
		{
			if($arProperty['CODE'] == 'IMAGES' && $arProperty['VALUE'] == $IMAGE_ID) 
			{
				$_iPropertyId = $arProperty['PROPERTY_VALUE_ID'];
				break;
			}
		}		
	
		if (!empty($_iPropertyId)) 
		{			
			$arPropertyValues = array();
			$arPropertyValues[$_iPropertyId] = array('VALUE' => array('del' => 'Y'), 'DESCRIPTION' => '');			
			$this->cIB_E->SetPropertyValues($ID, $IBLOCK_ID, $arPropertyValues, 'IMAGES');
		}
	}
		
	function Rename($ID, $arFields)
	{
		global $CACHE_MANAGER;
		
		$arFilter = array('IBLOCK_ID' => $arFields['IBLOCK_ID'], 'CHECK_PERMISSIONS' => 'N');		
		
		// checking for the existence of a page with this name
		$arElement = self::getElementByName($arFields['NAME'], $arFilter);
		$arOldElement = self::getElementById($ID, $arFilter);

		$bRename = false;
		if ($arOldElement != false) 
		{
			if ($arElement == false) 
				$bRename = true;
			else if($arElement['ID'] == $ID)
				$bRename = true;
		}

		if ($bRename)
		{
			$arFields['XML_ID'] = $arFields['NAME'];    		
			$this->cIB_E->Update($ID, $arFields);
			
			$sCatName = '';
			if(CWikiUtils::IsCategoryPage($arFields['NAME'], $sCatName)) 
			{
				$sCatNameOld = '';
				if (CWikiUtils::IsCategoryPage($arOldElement['NAME'], $sCatNameOld)) 
				{
					// rename a category	            
					$arFilter =  array('NAME' => $sCatNameOld, 'IBLOCK_ID' => $arFields['IBLOCK_ID'], 'CHECK_PERMISSIONS' => 'N');
					if (CWikiSocnet::IsSocNet())
					{ 
						$arFilter['>LEFT_BORDER'] = CWikiSocnet::$iCatLeftBorder;
						$arFilter['<RIGHT_BORDER'] = CWikiSocnet::$iCatRightBorder;
					}			    
					$rsCats = CIBlockSection::GetList(array(), $arFilter);
					$arCat = $rsCats->GetNext();			    

					if ($arCat != false) 
					{
						$CIB_S = new CIBlockSection();

						$_arFields = array();
						$_arFields['IBLOCK_ID'] = $arFields['IBLOCK_ID'];
						$_arFields['NAME'] = $sCatName;
						$_arFields['XML_ID'] = $sCatName;
						$_arFields['CHECK_PERMISSIONS'] = 'N';         		    

						$CIB_S->Update($arCat['ID'], $_arFields);
					}   	
				}
			}
			
			$arOldElement['NAME'] = CWikiUtils::htmlspecialcharsback($arOldElement['NAME']);  

			if (self::GetDefaultPage($arFields['IBLOCK_ID']) == false 
			    || (self::GetDefaultPage($arFields['IBLOCK_ID']) == $arOldElement['NAME'] 
				    && $arOldElement['NAME'] != $arFields['NAME'])) 
				self::SetDefaultPage($arFields['IBLOCK_ID'], $arFields['NAME']);    		       
				    
			$CACHE_MANAGER->ClearByTag('wiki_'.$ID);

			return true;    	            
		}		    
		    
		return false;
	} 
	
	static function SetDefaultPage($IBLOCK_ID, $NAME)
	{	   	    
		if (CWikiSocnet::IsSocNet())
		{ 
			$ENTITY_ID = 'IBLOCK_'.$IBLOCK_ID.'_SECTION';
			$ELEMENT_ID = CWikiSocnet::$iCatId;
		}
		else
		{ 
			$ENTITY_ID = 'IBLOCK_'.$IBLOCK_ID;
			$ELEMENT_ID = $IBLOCK_ID;
		}
        
		$arElement = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields($ENTITY_ID, $ELEMENT_ID);    

		if ($arElement !== false)
		{
			if (!isset($arElement['UF_WIKI_INDEX']))
			{
				$arFields = array();
				$arFields['ENTITY_ID'] = $ENTITY_ID;
				$arFields['FIELD_NAME'] = 'UF_WIKI_INDEX';
				$arFields['USER_TYPE_ID'] = 'string';
				$CAllUserTypeEntity = new CUserTypeEntity();
				$CAllUserTypeEntity->Add($arFields);
			}

			if (empty($arElement['UF_WIKI_INDEX']['VALUE']) || $arElement['UF_WIKI_INDEX']['VALUE'] != $NAME)
			{
				$arFields = array();
				$arFields['UF_WIKI_INDEX'] = $NAME;
				$GLOBALS['USER_FIELD_MANAGER']->Update($ENTITY_ID, $ELEMENT_ID, $arFields);
			}
			return true;					 
		}
		return false;
	}
	
	static function GetDefaultPage($IBLOCK_ID)
	{
		if (CWikiSocnet::IsSocNet())
		{ 
			$ENTITY_ID = 'IBLOCK_'.$IBLOCK_ID.'_SECTION';
			$ELEMENT_ID = CWikiSocnet::$iCatId;
		}
		else
		{ 
			$ENTITY_ID = 'IBLOCK_'.$IBLOCK_ID;
			$ELEMENT_ID = $IBLOCK_ID;
		}	    

		$arElement = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields(
			$ENTITY_ID,
			$ELEMENT_ID
		); 	    

		return isset($arElement['UF_WIKI_INDEX']['VALUE']) ? $arElement['UF_WIKI_INDEX']['VALUE'] : '';
	} 
	
	function GetCategory($NAME, $IBLOCK_ID)
	{
		global $arParams;
		
		$arResult = array();
		$arResult[] = array(
			'TITLE' => GetMessage('Service:Categories_TITLE'), 
			'NAME' => GetMessage('Service:Categories'), 
			'LINK' => CHTTP::urlAddParams(
				CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CATEGORIES'], 
					array(
						'wiki_name' => 'Service:Categories', 
						'group_id' => CWikiSocnet::$iSocNetId
					)
				),
				array()
			), 
			'IS_RED' => 'N',
			'IS_SERVICE' => 'Y'
		);

		$arFilter['=XML_ID'] = $NAME;
		$arFilter['IBLOCK_ID'] = $IBLOCK_ID;
		$arFilter['CHECK_PERMISSIONS'] = 'N';
		
		if (CWikiSocnet::IsSocNet())
			$arFilter['SUBSECTION'] = CWikiSocnet::$iCatId;

		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
		$arElement = $rsElement->GetNext();		
		
		$sCatName = '';				
		if (CWikiUtils::IsCategoryPage($NAME, $sCatName))
			return 	$arResult;
			
		$arLink = array();
		$arLinkExists = array();
		$arCat = array();
		$rsSect = CIBlockElement::GetElementGroups($arElement['ID'], false);
		while($arSect = $rsSect->GetNext()) 
		{
			$arCat[$arSect['ID']] = $arSect;
			$arLink[] = 'category:'.$arSect['NAME'];
		}	    

		if (CWikiSocnet::IsSocNet() && isset($arCat[CWikiSocnet::$iCatId]))
			unset($arCat[CWikiSocnet::$iCatId]);		
		
		$arFilter = array();
		$arFilter['NAME'] = $arLink;
		$arFilter['IBLOCK_ID'] = $IBLOCK_ID;
		$arFilter['ACTIVE'] = 'Y';
		$arFilter['CHECK_PERMISSIONS'] = 'N';
		if (CWikiSocnet::IsSocNet())
			$arFilter['SUBSECTION'] = CWikiSocnet::$iCatId; 
	 		
		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());		
		
		while($obElement = $rsElement->GetNextElement())
		{
			$arFields = $obElement->GetFields();
			$arLinkExists[] = preg_replace('/^(category|'.GetMessage('CATEGORY_NAME').'):/i'.BX_UTF_PCRE_MODIFIER, '', $arFields['NAME']);
		}		    

		if (!empty($arCat)) 
		{    
			foreach ($arCat as $_arCat)
			{
				$_arResult = array();
				$_arResult['ID'] = $_arCat['ID'];
				$_arResult['IS_RED'] = 'N';
				$_arResult['LINK'] = CHTTP::urlAddParams(
					CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CATEGORY'], 
						array(
							'wiki_name' => urlencode('Category:'.$_arCat['NAME']), 
							'group_id' => CWikiSocnet::$iSocNetId
						)
					),
					array()
				);
				$_arResult['TITLE'] = $_arCat['NAME'];
				$_arResult['NAME'] = $_arCat['NAME'];
				$_arResult['IS_SERVICE'] = 'N';
				if (!in_array($_arCat['NAME'], $arLinkExists))
					$_arResult['IS_RED'] = 'Y';
				$arResult[] = $_arResult;
			}
		}
		return $arResult;
	}
	
	/**
	 * 
	 * 
	 * 
	 * @param int $ID
	 * @return array
	 */
	static function GetElementById($ID, $arFilter)
	{
		global $arParams;
		$arFilter['ID'] = $ID;
		if (CWikiSocnet::IsSocNet())
			$arFilter['SUBSECTION'] = CWikiSocnet::$iCatId;		
		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
		$obElement = $rsElement->GetNextElement();
		$arResult = false;
		if ($obElement !== false)
		{
			$arResult = $obElement->GetFields();
			
			if (isset($arResult['NAME']))
				$arResult['NAME'] = htmlspecialchars($arResult['NAME']);
			$rsProperties = $obElement->GetProperties($arFilter['IBLOCK_ID']);

			foreach ($rsProperties as $arProperty)
				$arResult[$arProperty['CODE']] = $arProperty['VALUE'];	
			
			$arResult['SECTIONS'] = self::GetCategory($arResult['XML_ID'], $arFilter['IBLOCK_ID']);
			if (!empty($arResult['TAGS'])) 
			{
				$_arTAGS = explode(',', $arResult['TAGS']);
				$arResult['_TAGS'] = array();
				foreach ($_arTAGS as $sTag)
				{
					$arTag = array('NAME' => $sTag);
					if (!empty($arParams['PATH_TO_SEARCH']))
					{
						$arP = $arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'search') : array();
						$arP['tags'] = urlencode($sTag);
						$arTag['LINK'] = CHTTP::urlAddParams(
									CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_SEARCH'], 
										array(
											'wiki_name' => $arParams['ELEMENT_NAME'], 
											'group_id' => CWikiSocnet::$iSocNetId)
										),
										$arP
									);
					}                           
					$arResult['_TAGS'][] = $arTag;
				}
			}  		
		}
		return $arResult; 	
	} 
	
	/**
	 * 
	 * 
	 * 
	 * @param string $NAME
	 * @return array
	 */
	static function GetElementByName($NAME, $arFilter)
	{
		global $arParams;
		$NAME = CWikiUtils::htmlspecialcharsback($NAME); 
		$arFilter['=XML_ID'] = $NAME;
		
		if (CWikiSocnet::IsSocNet())
			$arFilter['SUBSECTION'] = CWikiSocnet::$iCatId;

		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
		$obElement = $rsElement->GetNextElement();
		$arResult = false;
		if ($obElement !== false)
		{
			$arResult = $obElement->GetFields();
			if (isset($arResult['NAME']))
				$arResult['NAME'] = htmlspecialchars($arResult['NAME']);			
			$rsProperties = $obElement->GetProperties($arFilter['IBLOCK_ID']);

			foreach ($rsProperties as $arProperty)
				$arResult[$arProperty['CODE']] = $arProperty['VALUE'];
				
			$arResult['SECTIONS'] = self::GetCategory($arResult['XML_ID'], $arFilter['IBLOCK_ID']);
			if (!empty($arResult['TAGS'])) 
			{
				$_arTAGS = explode(',', $arResult['TAGS']);
				$arResult['_TAGS'] = array();
				foreach ($_arTAGS as $sTag)
				{
					$sTag = trim($sTag);
					$arTag = array('NAME' => $sTag);
					if (!empty($arParams['PATH_TO_SEARCH']))
					{
						$arP = $arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'search') : array();
						$arP['tags'] = urlencode($sTag);
						$arTag['LINK'] = CHTTP::urlAddParams(
									CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_SEARCH'], 
										array(
											'wiki_name' => $arParams['ELEMENT_NAME'],
											'group_id' => CWikiSocnet::$iSocNetId
										)
									),
									$arP
								);
					}                           
					$arResult['_TAGS'][] = $arTag;
				}
			}		    
		}
		return $arResult; 	
	}	
} 

?>