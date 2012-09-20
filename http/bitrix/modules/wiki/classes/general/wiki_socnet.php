<?
IncludeModuleLangFile(__FILE__);

class CWikiSocnet
{    
	static public $bActive = false;

	static public $bInit = false;

	static public $iCatId = 0;
	static public $iCatLeftBorder = 0;
	static public $iCatRightBorder = 0;

	static public $iSocNetId = 0;

	static function Init($SOCNET_GROUP_ID, $IBLOCK_ID)
	{        
		if (self::$bInit)
		    return self::$bInit;
        
		if (!self::IsEnabledSocnet())
		    return false;
       
		self::$iSocNetId = $SOCNET_GROUP_ID;   
            
		// detect work group
		$arFilter = Array();
		$arFilter['IBLOCK_ID'] = $IBLOCK_ID;        
		$arFilter['SOCNET_GROUP_ID'] = self::$iSocNetId;
		$arFilter['CHECK_PERMISSIONS'] = 'N';  
		$rsSection = CIBlockSection::GetList(Array($by=>$order), $arFilter, true); 
		$obSection = $rsSection->GetNextElement();

		if ($obSection !== false)
		{		    
			$arResult = $obSection->GetFields();
			self::$iCatId = $arResult['ID'];
			self::$iCatLeftBorder = $arResult['LEFT_MARGIN'];
			self::$iCatRightBorder = $arResult['RIGHT_MARGIN'];
		}       
		else 
		{
			$arWorkGroup = CSocNetGroup::GetById(self::$iSocNetId);
		    
			$arFields = Array(  
				'ACTIVE' => 'Y',    
				'IBLOCK_ID' => $IBLOCK_ID,  
				'SOCNET_GROUP_ID' => self::$iSocNetId,
				'CHECK_PERMISSIONS' => 'N',  
				'NAME' => $arWorkGroup['NAME']
			);		    
			$CIB_S = new CIBlockSection();
			self::$iCatId = $CIB_S->Add($arFields);
			if (self::$iCatId == false) {
				self::$bInit = false;
				return false;   		        
			}
			$rsSection = CIBlockSection::GetList(Array($by=>$order), $arFilter, true); 
			$obSection = $rsSection->GetNextElement();
			if ($obSection == false)
			{
				self::$bInit = false;
				return false;
			} 
			$arResult = $obSection->GetFields();
			self::$iCatLeftBorder = $arResult['LEFT_MARGIN'];
			self::$iCatRightBorder = $arResult['RIGHT_MARGIN']; 		    
		}  
			
		self::$bInit = CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, self::$iSocNetId, 'wiki');
		return self::$bInit;          
	}
        
	static function IsEnabledSocnet()
	{	    
		if (self::$bActive) 
			return self::$bActive;
	    
		$bActive = false;
		$rsEvents = GetModuleEvents('socialnetwork', 'OnFillSocNetFeaturesList');
		while($arEvent = $rsEvents->Fetch())
		{
			if($arEvent['TO_MODULE_ID'] == 'wiki'
				&& $arEvent['TO_CLASS'] == 'CWikiSocnet')
			{
				$bActive = true;
				break;
			}
		}
		return $bActive;
	}

	static function IsSocNet()
	{
		return self::$bInit;
	}	

	static function EnableSocnet($bActive = false)
	{
		if($bActive)
		{
			if(!self::IsEnabledSocnet())
			{
				RegisterModuleDependences('socialnetwork', 'OnFillSocNetFeaturesList', 'wiki', 'CWikiSocnet', 'OnFillSocNetFeaturesList');
				RegisterModuleDependences('socialnetwork', 'OnFillSocNetMenu', 'wiki', 'CWikiSocnet', 'OnFillSocNetMenu');
				RegisterModuleDependences('socialnetwork', 'OnParseSocNetComponentPath', 'wiki', 'CWikiSocnet', 'OnParseSocNetComponentPath');
				RegisterModuleDependences('socialnetwork', 'OnInitSocNetComponentVariables', 'wiki', 'CWikiSocnet', 'OnInitSocNetComponentVariables');
			}
		}
		else
		{
			if(self::IsEnabledSocnet())
			{
				UnRegisterModuleDependences('socialnetwork', 'OnFillSocNetFeaturesList', 'wiki', 'CWikiSocnet', 'OnFillSocNetFeaturesList');
				UnRegisterModuleDependences('socialnetwork', 'OnFillSocNetMenu', 'wiki', 'CWikiSocnet', 'OnFillSocNetMenu');
				UnRegisterModuleDependences('socialnetwork', 'OnParseSocNetComponentPath', 'wiki', 'CWikiSocnet', 'OnParseSocNetComponentPath');
				UnRegisterModuleDependences('socialnetwork', 'OnInitSocNetComponentVariables', 'wiki', 'CWikiSocnet', 'OnInitSocNetComponentVariables');
			}
		}
	}    
    
	static function OnFillSocNetFeaturesList(&$arSocNetFeaturesSettings)
	{
		$arSocNetFeaturesSettings['wiki'] = array(
			'allowed' => array(SONET_ENTITY_GROUP),
			'title' => GetMessage('WIKI_SOCNET_TAB'),
			'operations' => array(
				'view' 		=> array(SONET_ENTITY_GROUP => SONET_ROLES_USER),
				'write' 	=> array(SONET_ENTITY_GROUP => SONET_ROLES_USER),
				'delete' 	=> array(SONET_ENTITY_GROUP => SONET_ROLES_MODERATOR),
			),
			'operation_titles' => array(
				'view' 		=> GetMessage('WIKI_PERM_READ'),         
				'write' 	=> GetMessage('WIKI_PERM_WRITE'),       
				'delete'	=> GetMessage('WIKI_PERM_DELETE')                      
			), 			
			'minoperation' => array('view'),
			'subscribe_events' => array(
				'wiki' =>  array(
					'ENTITIES'	=>	array(
						SONET_SUBSCRIBE_ENTITY_GROUP => array(
							'TITLE' 			=> GetMessage('SOCNET_LOG_WIKI_GROUP'),
							'TITLE_SETTINGS'	=> GetMessage('SOCNET_LOG_WIKI_GROUP_SETTINGS'),
						),
					),
					'OPERATION'		=> 'view',
					'CLASS_FORMAT'	=> 'CWikiSocnet',
					'METHOD_FORMAT'	=> 'FormatEvent_Wiki',
					'HAS_CB'		=> 'Y',
					'FULL_SET' 		=> array('wiki', 'wiki_del')
				),
				'wiki_del' =>  array(
					'ENTITIES'	=>	array(
						SONET_SUBSCRIBE_ENTITY_GROUP => array(
							'TITLE' 			=> GetMessage('SOCNET_LOG_WIKI_DEL_GROUP'),
						),
					),
					'OPERATION'		=> 'view',
					'CLASS_FORMAT'	=> 'CWikiSocnet',
					'METHOD_FORMAT'	=> 'FormatEvent_Wiki',
					'HIDDEN'		=> true,
					'HAS_CB'		=> 'Y'
				)				
			)
		);
	}

	static function OnFillSocNetMenu(&$arResult, $arParams = array())
	{
		$arResult['CanView']['wiki'] = ((array_key_exists('ActiveFeatures', $arResult) ? array_key_exists('wiki', $arResult['ActiveFeatures']) : true) && CSocNetFeaturesPerms::CanPerformOperation($GLOBALS['USER']->GetID(), $arParams['ENTITY_TYPE'], $arParams['ENTITY_ID'], 'wiki', 'view', CSocNetUser::IsCurrentUserModuleAdmin()));

		$arResult['Title']['wiki'] = (array_key_exists('ActiveFeatures', $arResult) && array_key_exists('wiki', $arResult['ActiveFeatures']) && strlen($arResult['ActiveFeatures']['wiki']) > 0 ? $arResult['ActiveFeatures']['wiki'] : GetMessage('WIKI_SOCNET_TAB'));
		
		if (!array_key_exists('SEF_MODE', $arResult) || $arResult['SEF_MODE'] != 'N')
			$arResult['Urls']['wiki'] = $arResult['Urls']['view'].'wiki/';
		else
		{
			if (!array_key_exists('PAGE_VAR', $arResult))
				$arResult['PAGE_VAR'] = 'page';

			if (!array_key_exists('GROUP_VAR', $arResult))
				$arResult['GROUP_VAR'] = 'group_id';

			$arResult['Urls']['wiki'] = '?'.$arResult['PAGE_VAR'].'=group_wiki_index&'.$arResult['GROUP_VAR'].'='.$arResult['Group']['ID'];
		}	
		$arResult['AllowSettings']['wiki'] = true; 
	}

	static function OnParseSocNetComponentPath(&$arUrlTemplates, &$arCustomPagesPath, $arParams)
	{
		if ($arParams['SEF_MODE'] == 'N')
		{
			$arMyUrlTemplates = array(
				'group_wiki_index' => 'page=group_wiki_index&group_id=#group_id#',
				'group_wiki_categories' => 'page=group_wiki_categories&group_id=#group_id#',
				'group_wiki_search' => 'page=group_wiki_search&group_id=#group_id#',
				'group_wiki_post' => 'page=group_wiki_post&group_id=#group_id#&title=#wiki_name#',
				'group_wiki_post_edit' => 'page=group_wiki_post_edit&group_id=#group_id#&title=#wiki_name#',
				'group_wiki_post_history' => 'page=group_wiki_post_history&group_id=#group_id#&title=#wiki_name#',
				'group_wiki_post_history_diff' => 'page=group_wiki_post_history_diff&group_id=#group_id#&title=#wiki_name#',
				'group_wiki_post_discussion' => 'page=group_wiki_post_discussion&group_id=#group_id#&title=#wiki_name#',
				'group_wiki_post_category' => 'page=group_wiki_post_category&group_id=#group_id#&title=#wiki_name#',
			);
		}	    
		else
		{	    
			$arMyUrlTemplates = array(
				'group_wiki_index' => 'group/#group_id#/wiki/',
				'group_wiki_categories' => 'group/#group_id#/wiki/categories/',
				'group_wiki_search' => 'group/#group_id#/wiki/search/',				
				'group_wiki_post' => 'group/#group_id#/wiki/#wiki_name#/',
				'group_wiki_post_edit' => 'group/#group_id#/wiki/#wiki_name#/edit/',
				'group_wiki_post_history' => 'group/#group_id#/wiki/#wiki_name#/history/',
				'group_wiki_post_history_diff' => 'group/#group_id#/wiki/#wiki_name#/history/diff/',
				'group_wiki_post_discussion' => 'group/#group_id#/wiki/#wiki_name#/discussion/',
				'group_wiki_post_category' => 'group/#group_id#/wiki/#wiki_name#/',
			);
		}

		static $base_path = false;
		if(!$base_path)
		{
			if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/wiki/'.SITE_ID.'/group_index.php'))
				$base_path = '/bitrix/php_interface/wiki/'.SITE_ID.'/';
			elseif(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/wiki/group_index.php'))
				$base_path = '/bitrix/php_interface/wiki/';
			else
				$base_path = '/bitrix/modules/wiki/socnet/';
		}

		foreach($arMyUrlTemplates as $page => $url)
		{
			$arUrlTemplates[$page] = $url;
			$arCustomPagesPath[$page] = $base_path;
		}
	}

	static function OnInitSocNetComponentVariables(&$arVariableAliases, &$arCustomPagesPath)
	{
		$arVariableAliases['wiki_name'] = 'wiki_name';
		$arVariableAliases['title'] = 'title';
		$arVariableAliases['oper'] = 'oper';
	}
	
	function FormatEvent_Wiki($arFields, $arParams, $bMail = false)
	{
		$GLOBALS["APPLICATION"]->SetAdditionalCSS("/bitrix/themes/.default/wiki_sonet_log.css");

		$arResult = array(
				"EVENT"				=> $arFields,
				"CREATED_BY"		=> array(),
				"ENTITY"			=> array(),
				"EVENT_FORMATTED"	=> array(),
			);
			
		if (intval($arFields["USER_ID"]) > 0)
		{
			if ($bMail)
				$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("WIKI_SOCNET_LOG_USER")." ".$arFields["CREATED_BY_NAME"]." ".$arFields["CREATED_BY_LAST_NAME"];
			else
			{
				$arFieldsTooltip = array(
					"ID" 			=> $arFields["USER_ID"],
					"NAME" 			=> $arFields["~CREATED_BY_NAME"],
					"LAST_NAME" 	=> $arFields["~CREATED_BY_LAST_NAME"],
					"SECOND_NAME" 	=> $arFields["~CREATED_BY_SECOND_NAME"],
					"LOGIN" 		=> $arFields["~CREATED_BY_LOGIN"],
				);
				$arResult["CREATED_BY"]["TOOLTIP_FIELDS"] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
			}
		}
		elseif ($bMail)
			$arResult["CREATED_BY"]["FORMATTED"] = GetMessage("WIKI_SOCNET_LOG_ANONYMOUS_USER");

		if (
			$arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP
			&& intval($arFields["ENTITY_ID"]) > 0
		)
		{
			if ($bMail)
			{
				$arResult["ENTITY"]["FORMATTED"] = $arFields["GROUP_NAME"];
				$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("WIKI_SOCNET_LOG_ENTITY_G");
			}
			else
			{
				$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arFields["ENTITY_ID"]));	
				$arResult["ENTITY"]["FORMATTED"]["TYPE_NAME"] = $GLOBALS["arSocNetAllowedSubscribeEntityTypesDesc"][$arFields["ENTITY_TYPE"]]["TITLE_ENTITY"];
				$arResult["ENTITY"]["FORMATTED"]["URL"] = $url;				
				$arResult["ENTITY"]["FORMATTED"]["NAME"] = $arFields["GROUP_NAME"];
			}
		}

		if (
			!$bMail
			&& array_key_exists("URL", $arFields)
			&& strlen($arFields["URL"]) > 0
		)
			$wiki_tmp = '<a href="'.$arFields["URL"].'">'.$arFields["TITLE"].'</a>';
		else
			$wiki_tmp = $arFields["TITLE"];

		if ($arFields["EVENT_ID"] == "wiki")
			$title_tmp = ($bMail ? GetMessage("WIKI_SOCNET_LOG_TITLE_MAIL") : GetMessage("WIKI_SOCNET_LOG_TITLE"));
		elseif ($arFields["EVENT_ID"] == "wiki_del")
			$title_tmp = ($bMail ? GetMessage("WIKI_DEL_SOCNET_LOG_TITLE_MAIL") : GetMessage("WIKI_DEL_SOCNET_LOG_TITLE"));
		
		$title = str_replace(
						array("#TITLE#", "#ENTITY#", "#CREATED_BY#"),
						array($wiki_tmp, $arResult["ENTITY"]["FORMATTED"], ($bMail ? $arResult["CREATED_BY"]["FORMATTED"] : "")),
						$title_tmp
					);
					
		$arResult["EVENT_FORMATTED"] = array(
				"TITLE"		=> $title,
				"MESSAGE"	=> ($bMail ? CSocNetTextParser::killAllTags($arFields["MESSAGE"]) : $arFields["MESSAGE"])
			);

		$url = false;
		
		if (
			$bMail 
			&& strlen($arFields["URL"]) > 0 
			&& strlen($arFields["SITE_ID"]) > 0
		)
		{
			$rsSites = CSite::GetByID($arFields["SITE_ID"]);
			$arSite = $rsSites->Fetch();

			if (strlen($arSite["SERVER_NAME"]) > 0)
				$server_name = $arSite["SERVER_NAME"];
			else
				$server_name = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);

			$protocol = (CMain::IsHTTPS() ? "https" : "http");
			$url = $protocol."://".$server_name.$arFields["URL"];
		}

		if (strlen($url) > 0)
			$arResult["EVENT_FORMATTED"]["URL"] = $url;
			
		return $arResult;
	}

}
?>