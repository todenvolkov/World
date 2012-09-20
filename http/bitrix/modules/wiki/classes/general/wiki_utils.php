<?php

IncludeModuleLangFile(__FILE__);

class CWikiUtils
{
	static function getRightsLinks($arPage)
	{   
		global $arParams, $APPLICATION; 	
		if (!is_array($arPage))
			$arPage = array($arPage);    		    	    		
				
		$arLinks = array();    	
		$arParams['ELEMENT_NAME'] = htmlspecialcharsback($arParams['ELEMENT_NAME']);
		$arParams['ELEMENT_NAME'] = urlencode($arParams['ELEMENT_NAME']);

		if (in_array('categories', $arPage))
			return array();

		if (in_array('article', $arPage) && !in_array('add', $arPage))
		{
			$arLinks['article'] = array(
				'NAME' => GetMessage('PAGE_ARTICLE'),
				'TITLE' => GetMessage('PAGE_ARTICLE_TITLE'),
				'CURRENT' => in_array('article', $arPage),
				'LINK' => CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST'], 
					array(
						'wiki_name' => $arParams['ELEMENT_NAME'], 
						'group_id' => CWikiSocnet::$iSocNetId
					)
				),
				'ID' => 'article',
				'TYPE' => 'page',
				'IS_RED' => in_array('add', $arPage) ? 'Y' : 'N'
			);
		}
		
		if (self::IsWriteable() && 
			((!in_array('history', $arPage) || in_array('history_diff', $arPage)) &&
			(!in_array('add', $arPage) && !in_array('edit', $arPage) && !in_array('delete', $arPage) && !in_array('rename', $arPage))))
		{
			if(IsModuleInstalled('bizproc'))
			{
				$arLinks['history'] = array(
					'NAME' => GetMessage('PAGE_HISTORY'),
					'TITLE' => GetMessage('PAGE_HISTORY_TITLE'),
					'CURRENT' => in_array('history', $arPage),
					'LINK' => CHTTP::urlAddParams(
						CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_HISTORY'], 
							array(
								'wiki_name' => $arParams['ELEMENT_NAME'], 
								'group_id' => CWikiSocnet::$iSocNetId
							)
						),
						$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'history') : array()
					),
					'ID' => 'history',
					'TYPE' => 'page',
					'IS_RED' => 'N'
				);	
			}	
		}
		
		if ($arParams['USE_REVIEW'] == 'Y')
		{
			$arLinks['discussion'] = array(
				'NAME' => GetMessage('PAGE_DISCUSSION'),
				'TITLE' => GetMessage('PAGE_DISCUSSION_TITLE'),
				'CURRENT' => in_array('discussion', $arPage),
				'LINK' => CHTTP::urlAddParams(
					CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_DISCUSSION'], 
						array(
							'wiki_name' => $arParams['ELEMENT_NAME'], 
							'group_id' => CWikiSocnet::$iSocNetId
						)
					),
					$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'discussion') : array()
				),
				'ID' => 'discussion',
				'TYPE' => 'page',
				'IS_RED' => 'N'
			);
		}

		if (self::IsWriteable() && (!in_array('history', $arPage) && !in_array('history_diff', $arPage))) 
		{
			$arLinks['add'] = array(
				'NAME' => GetMessage('PAGE_ADD'),
				'TITLE' => GetMessage('PAGE_ADD_TITLE'),
				'CURRENT' => in_array('add', $arPage),
				'LINK' => CHTTP::urlAddParams(
					CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'], 
						array(
							'wiki_name' => GetMessage('WIKI_NEW_PAGE_TITLE'), 
							'group_id' => CWikiSocnet::$iSocNetId
						)
					),
					array($arParams['OPER_VAR'] => 'add')
				),
				'ID' => 'add',
				'TYPE' => 'edit',                
				'IS_RED' => in_array('add', $arPage) ? 'Y' : 'N'
			);

			if (!in_array('add', $arPage)) 
			{			
    			$arLinks['edit'] = array(
    				'NAME' => GetMessage('PAGE_EDIT'),
    				'TITLE' => GetMessage('PAGE_EDIT_TITLE'),
    				'CURRENT' => in_array('edit', $arPage),
    				'LINK' => CHTTP::urlAddParams(
    					CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'], 
    						array(
    							'wiki_name' => $arParams['ELEMENT_NAME'], 
    							'group_id' => CWikiSocnet::$iSocNetId
    						)
    					),
    					$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'edit') : array()
    				),
    				'ID' => 'edit',
    				'TYPE' => 'edit',
    				'IS_RED' => in_array('add', $arPage) ? 'Y' : 'N'
    			);
			

				$url = $APPLICATION->GetPopupLink(
					array(
						'URL' => CHTTP::urlAddParams(
							CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'], 
								array(
									'wiki_name' => $arParams['ELEMENT_ID'], 
									'group_id' => CWikiSocnet::$iSocNetId
								)
							), 
							array($arParams['OPER_VAR'] => 'delete')
						),
						'PARAMS' => array(
							'width' => 400,
							'height' => 150,
							'resizable' => false
						)
					)
				);
				if (self::IsDeleteable()) 
				{        		
					$arLinks['delete'] = array(
						'NAME' => GetMessage('PAGE_DELETE'),
						'TITLE' => GetMessage('PAGE_DELETE_TITLE'), 
						'CURRENT' => in_array('delete', $arPage),
						'LINK' => 'javascript:'.$url,			
						'ID' => 'delete',
						'TYPE' => 'edit',
						'IS_RED' => 'N'
					);
				}
			}
		/**	$arLinks['access'] = array(
				'NAME' => GetMessage('PAGE_ACCESS'),
				'TITLE' => GetMessage('PAGE_ACCESS_TITLE'),
				'CURRENT' => in_array('access', $arPage),
				'LINK' => CHTTP::urlAddParams(
					CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST_EDIT'], 
						array(
							'wiki_name' => $arParams['ELEMENT_NAME'], 
							'group_id' => CWikiSocnet::$iSocNetId
						)
					), 
					array($arParams['OPER_VAR'] => 'access')
				),
				'ID' => 'access',
				'TYPE' => 'edit',
				'IS_RED' => 'N'
			); **/   	
		}
		return $arLinks;
	}  

	static function IsReadable()
	{
		return self::CheckAccess('view');        
	}
    
	static function IsWriteable()
	{
		return self::CheckAccess('write');          
	}
    
	static function isAllowHTML()
	{
		if (COption::GetOptionString('wiki', 'allow_html', 'Y') == 'N')
			return false;
	    
		if (!$GLOBALS['USER']->IsAuthorized()) 
			return false;
		     	    
		return true;          
	}    
    
	static function IsDeleteable()
	{
		return self::CheckAccess('delete');        
	}    
    
	static function CheckAccess($access = 'view')
	{
		global $APPLICATION, $USER, $arParams;
        
		if ($USER->IsAdmin())
		    return true;                   
            
		if (CWikiSocnet::IsSocNet())
		{       
			if (CSocNetUser::IsCurrentUserModuleAdmin())
				return true;            

			$socnetRole = CSocNetUserToGroup::GetUserRole($USER->GetID(), CWikiSocnet::$iSocNetId);
			if (!in_array($socnetRole, array(SONET_ROLES_OWNER, SONET_ROLES_MODERATOR, SONET_ROLES_USER)))
				return false;  
			
			if (!CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, CWikiSocnet::$iSocNetId, 'wiki', $access))
				return false;                    
			
			return true;
		} 
		else
		{ 
			$letter = 'R';
			$letterI = 'R';
			switch ($access)
			{                
				case 'write': $letter = 'W'; $letterI = 'W'; break;
				case 'delete': $letter = 'Y'; $letterI = 'W'; break;
				case 'perm': $letter = 'Z'; $letterI = 'X'; break;
			}

			$wikiModulePermission = $APPLICATION->GetGroupRight('wiki');
			$iblockPermission = CIBlock::GetPermission($arParams['IBLOCK_ID']); 
			return $wikiModulePermission >= $letter && $iblockPermission >= $letterI;
		}         
	}    
    
	static function CheckServicePage($NAME, &$SERVICE_NAME)
	{
		$arStream = array('category', strtolower(GetMessage('CATEGORY_NAME')));
		$arSplit = explode(':', $NAME);
		if (count($arSplit) >= 2)
		{
			$SERVICE_PAGE = strtolower($arSplit[0]);
			if (in_array($SERVICE_PAGE, $arStream))
			{	
				unset($arSplit[0]);		    
				$SERVICE_NAME =  implode(':', $arSplit);			
				return $SERVICE_PAGE;
			}
			else 
				return '';
		}  
		else
		{
			return '';
		}        
	} 

	static function IsCategoryPage($NAME, &$CATEGORY_NAME)
	{
		$sServiceName = self::CheckServicePage($NAME, $CATEGORY_NAME); 
		return $sServiceName == 'category' || $sServiceName == strtolower(GetMessage('CATEGORY_NAME'));
	}
    
	static function OnBeforeIndex($arFields)	
	{	
		$CWikiParser = new CWikiParser();
		$arFields['BODY'] = $CWikiParser->parseForSearch($arFields['BODY']);
		return $arFields;
	}         
    
	static function GetUserLogin($arUserData = array())
	{
		global $USER;
		if (!empty($arUserData)) 
		{
			$userLogin = isset($arUserData['USER_LOGIN']) ? $arUserData['USER_LOGIN'] : $arUserData['LOGIN'];
			$userFName = isset($arUserData['USER_NAME']) ? $arUserData['USER_NAME'] : $arUserData['NAME'];
			$userLName = isset($arUserData['USER_LAST_NAME']) ? $arUserData['USER_LAST_NAME'] : $arUserData['LAST_NAME'];
		} 
		else 
		{	    
			$userLogin = $USER->GetLogin();
			$userFName = $USER->GetFirstName();
			$userLName = $USER->GetLastName();
		}

		if (!empty($userFName))
		{
			$userLogin = $userFName;
			if (!empty($userLName))
				$userLogin .= ' '.$userLName;
		}
		return $userLogin;
	}
		
	static function htmlspecialcharsback($str, $end = true)
	{
		while (strpos($str, '&amp;') !== false || strpos($str, '%26amp%3B') !== false) 
			$str = htmlspecialcharsback($str);
		
		if ($end)
			$str = htmlspecialcharsback($str);
		return  $str;	    
	}
}

?>