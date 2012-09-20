<?

IncludeModuleLangFile(__FILE__);

class CWikiParser
{	
	public $arNowiki = array();	
	public $arLink = array();
	public $arLinkExists = array();
	public $arFile = array();
	public $arVersionFile = array();
	
	function __construct()
	{
		
	}
	
	function Parse($text, $type = 'text', $arFile = array())
	{
		$type = ($type == 'html' ? 'html' : 'text');
	    
		$this->arNowiki = array();
		$this->arLink = array();
		$this->arLinkExists = array();
		$this->arFile = $arFile;
		$this->arVersionFile = array();
		// An array can be either array (23,45,67), and array ('file_name' => 'file_path'), if this version of the document in the history of
		if (!is_array($this->arFile))
		    $this->arFile = array();
		foreach ($this->arFile as $_k => $file)
		{
			if (!is_numeric($file) && !is_numeric($_k))		    
			{
				$this->arVersionFile[$_k] = $file;
				unset($this->arFile[$_k]);
			}
		}
		reset($this->arFile);
		
		// cut nowiki
		$text = preg_replace_callback('/(<nowiki>(.*)<\/nowiki>)/isU'.BX_UTF_PCRE_MODIFIER, array(&$this, '_noWikiCallback'), $text);		
		
		// bi
		$text = preg_replace(
			array(
				'/\'{3}(.*)\'{2}(.+)\'{2}(.*)\'{3}/imU'.BX_UTF_PCRE_MODIFIER,
				'/\'{3}(.+)\'{3}/imU'.BX_UTF_PCRE_MODIFIER,
				'/\'{2}(.+)\'{2}/imU'.BX_UTF_PCRE_MODIFIER
			),
			array(
				'<b>\\1<i>\\2</i>\\3</b>',
				'<b>\\1</b>',
				'<i>\\1</i>'
			),
			$text);							

		// hr	
		$text = preg_replace( '/-----*/'.BX_UTF_PCRE_MODIFIER, '\\1<hr />', $text );

		// Header
		for($i = 6; $i >= 1; $i--)
		{
			$_H = str_repeat('=', $i);
			$text = preg_replace('/^\s*'.$_H.'(.+?)'.$_H.'\s*$/miU'.BX_UTF_PCRE_MODIFIER, '<H'.$i.'>\\1</H'.$i.'>', $text);
		}		
				
		// Internal link		
		$text = $this->processInternalLink($text);

		// External link
		$text = preg_replace_callback('/\[((http|https|ftp)(.+))( (.+))?\]/iU'.BX_UTF_PCRE_MODIFIER, array(&$this, '_processExternalLinkCallback'), $text);
				
		// images and other files
		$text = preg_replace_callback('/\[?\[(:)?(File|'.GetMessage('FILE_NAME').'):(.+)\]\]?/iU'.BX_UTF_PCRE_MODIFIER, array(&$this, '_processFileCallback'), $text);		
		
		// TOC
		$text = $this->processToc($text);			
		
		// Paste nowiki	
		if (!empty($this->arNowiki))	
			$text = preg_replace_callback('/(##NOWIKI(\d+)##)/isU'.BX_UTF_PCRE_MODIFIER, array(&$this, '_noWikiReturnCallback'), $text);				
		
		if ($type == 'text')
			$text = nl2br(trim($text));		
		
		$text .= '<div style="clear:both"></div>';    
		    
		return $text;
	}
	
	function _processFileCallback($matches)
	{	    
		static $sImageAlign = '';
		$bLink = false;
		if ($matches[1] == ':')
			$bLink = true;
		    
		// if the internal file then get it
		$sFile = $sFileName = $sPath = trim($matches[3]);
		$bOur = false;		
		if (is_numeric($sFile)) 
		{						
			$rsFile = CFile::GetByID($sFile);
			$arFile = $rsFile->Fetch();
			if ($arFile != false) 
			{
				$bOur = true;
				$sPath = '/'.(COption::GetOptionString('main', 'upload_dir', 'upload'))."/$arFile[SUBDIR]/$arFile[FILE_NAME]";
				$sFileName = $arFile['ORIGINAL_NAME'];
			}
		} 
		else if (isset($this->arVersionFile[strtolower($sFile)]))
		{
			$sPath = $this->arVersionFile[strtolower($sFile)];
			$sFileName = $sFile;
		}
		else if (!empty($this->arFile)) 
		{
			$arFilter = array(
				'@ID' => implode(',', $this->arFile)
			);

			$rsFile = CFile::GetList(array(), $arFilter);
			while($arFile = $rsFile->Fetch())
			{
				if ($arFile['ORIGINAL_NAME'] == $sFile) 
				{    			    
					$bOur = true;
					$sFile = $arFile['ID'];

					$sPath = '/'.(COption::GetOptionString('main', 'upload_dir', 'upload'))."/$arFile[SUBDIR]/$arFile[FILE_NAME]";
					$sFileName = $arFile['ORIGINAL_NAME'];
					break;
				}
			}		      
		}

		// if the image is processed as a picture
		$sName = bx_basename($sPath);
		if (CFile::IsImage($sName))
		{
			if ($bOur) 
			{
				if ($bLink)
					$sReturn = '<a href="'.$sPath.'" title="'.$sFileName.'">'.$sFileName.'</a>';
				else 
				{
					$sReturn  = CFile::ShowImage($sFile, 
						COption::GetOptionString('wiki', 'image_max_width', 600), 
						COption::GetOptionString('wiki', 'image_max_height', 600), 
						'border="0" align="'.$sImageAlign.'"'
					);
				}
			} else {
				if ($bLink)
					$sReturn = '<a href="'.$sPath.'" title="'.$sName.'">'.$sName.'</a>';
				else
					$sReturn = '<img src="'.$sPath.'" alt="'.$sFileName.'"/>';
			}
		}
		else if (strpos($sPath, 'http://') === 0)
			$sReturn = ' [ <a href="'.$sFile.'" title="'.GetMessage('FILE_FILE_DOWNLOAD').'">'.GetMessage('FILE_DOWNLOAD').'</a> ] ';				    		
		// otherwise the file				
		else 				    	
			$sReturn = '['.GetMessage('FILE_NAME').':'.(is_numeric($sFile)  || empty($sFileName) ? $sFile : $sFileName).']';
		
		return $sReturn;
	}
	
	function _processExternalLinkCallback($matches)
	{
		$sLink = trim($matches[1]);
		$sName = $sTitle = $sLink;
      
		$matches[5] = isset($matches[5]) ? trim($matches[5]) : '';
		if (!empty($matches[5]))
			$sTitle = trim($matches[5]);
		$sTitle = strip_tags($sTitle);	
			
		$sReturn = '<a href="'.$sLink.'" title="'.$sName.'">'.$sTitle.'</a>';
		return $sReturn;
	}	
	
	function processInternalLink($text)
	{
		global $APPLICATION, $arParams;
		$text = preg_replace_callback('/\[\[(.+)(\|(.*))?\]\]/iU'.BX_UTF_PCRE_MODIFIER, array(&$this, '_processInternalLinkPrepareCallback'), $text);
		// check pages for exists
		if (!empty($this->arLink)) 
		{
			$arFilter = array();
			$arFilter['NAME'] = $this->arLink;
			$arFilter['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
			$arFilter['ACTIVE'] = 'Y';
			$arFilter['CHECK_PERMISSIONS'] = 'N';
			if (CWikiSocnet::IsSocNet())
				$arFilter['SUBSECTION'] = CWikiSocnet::$iCatId;			
			$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, Array());
			while($obElement = $rsElement->GetNextElement())
			{
				$arFields = $obElement->GetFields();
				$this->arLinkExists[] = strtolower($arFields['NAME']);		
			}		
		}
		
		$text = preg_replace_callback('/(##LINK(\d+)##)/isU'.BX_UTF_PCRE_MODIFIER, array(&$this, '_processInternalLinkCallback'), $text);
		
		return $text;
	}	
	
	function _processInternalLinkPrepareCallback($matches)
	{
		$sLink = trim($matches[1]);
		$sName = $sTitle = $sLink;
		$sCatName = '';
		$matches[3] = isset($matches[3]) ? trim($matches[3]) : '';
		if (!empty($matches[3]))
			$sName = $sTitle = $matches[3];
		else 
		{
			if (CWikiUtils::IsCategoryPage($sName, $sCatName)) 
				return '';
		}

		$sTitle = strip_tags($sTitle);	
		$i = count($this->arLink);
		$this->arLink[] = $matches[1];
		$sReturn = '<a ##LINK'.$i.'## title="'.$sTitle.'">'.$sName.'</a>';
		return $sReturn;
	}		
	
	function _processInternalLinkCallback($matches)
	{
		global $arParams;

		$sReturn = '';

		if (in_array(strtolower($this->arLink[$matches[2]]), $this->arLinkExists))
		{
			$sURL = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_POST'], 
				array(
					'wiki_name' => urlencode($this->arLink[$matches[2]]), 
					'group_id' => CWikiSocnet::$iSocNetId
				)
			);	
			$sReturn = 'href="'.$sURL.'"';													
		}
		else 
		{
			$sURL = CHTTP::urlAddParams(
				CComponentEngine::MakePathFromTemplate(
					$arParams['PATH_TO_POST_EDIT'], 
					array(
						'wiki_name' => urlencode($this->arLink[$matches[2]]), 
						'group_id' => CWikiSocnet::$iSocNetId
					)
				),
				$arParams['IN_COMPLEX'] == 'Y' && $arParams['SEF_MODE'] == 'N' ? array($arParams['OPER_VAR'] => 'edit') : array()
			);
              															
			$sReturn = 'href="'.$sURL.'" class="wiki_red"';																			
		}
		return $sReturn;	   
	}	
	
	function processToc($text)
	{
		$matches = array();
		if (preg_match_all('/<H(\d{1})>(.*)<\/H\\1>/isU'.BX_UTF_PCRE_MODIFIER, $text, $matches, PREG_SET_ORDER))
		{		
			if (count($matches) > 4)
			{
				$iCurrentToc = 1;
				// work level TOC
				$iCurrentTocLevel = 0;
				// previous user defined level of TOC
				$iRealPrevItemTocLevel = 1;
				// previous working level of TOC
				$iPrevItemTocLevel = 0;
				// current user defined the level of TOC
				$iRealItemTocLevel = 1;
				$sToc = '';

				$bfirst = true;
				$aNumToc = array();
				foreach ($matches as $_m =>  $arMatch)
				{					    
					$iRealItemTocLevel = (int)$arMatch[1];
					$sItemToc = trim($arMatch[2]);
					// normalize levels
					if ($bfirst && $iRealPrevItemTocLevel < $iRealItemTocLevel)
						$iItemTocLevel = 1;						    				    					    
					else if ($iCurrentTocLevel == 1 && $iRealItemTocLevel < $iRealPrevItemTocLevel)
						$iItemTocLevel = $iCurrentTocLevel;
					else if ($iRealItemTocLevel > $iRealPrevItemTocLevel) 
						$iItemTocLevel = $iCurrentTocLevel + 1;
					else if ($iRealItemTocLevel < $iRealPrevItemTocLevel) 
					{
						$_delta = $iRealPrevItemTocLevel - $iRealItemTocLevel;
						$iItemTocLevel = $iCurrentTocLevel - $_delta;
						if ($iItemTocLevel < 1) 
							$iItemTocLevel = 1;					    
					} 
					else 
						$iItemTocLevel = $iCurrentTocLevel;
					
					// create a numbering of TOC
					$iCurrentNumTocLevel = $bfirst ? 1 : $iItemTocLevel;
					$aNumToc[$iCurrentNumTocLevel] =  !isset($aNumToc[$iCurrentNumTocLevel]) ? 1 : $aNumToc[$iCurrentNumTocLevel] + 1;
					if ($iItemTocLevel < $iPrevItemTocLevel)
					{
						for ($i = $iItemTocLevel + 1; $i <= $iPrevItemTocLevel; $i++)	                			
							unset($aNumToc[$i]);
					}  

					// build a TOC
					if ($iItemTocLevel > $iCurrentTocLevel || empty($sToc))
					{
						$iCurrentTocLevel++;					    
						$sToc .= '<ul>';		
					}
					else if ($iItemTocLevel < $iCurrentTocLevel)
					{	
						if ($iItemTocLevel <= 0) 
							$iItemTocLevel = 1;
								      
						if ($iCurrentTocLevel > 1) {  
							for ($i = 0; $i < ($iCurrentTocLevel - $iItemTocLevel); $i++) 			                			
								$sToc .= '</ul>';
						} 
						else 
							$sToc .= '</ul>';

						if ($iCurrentTocLevel > 1)
							$iCurrentTocLevel = $iItemTocLevel;

					} 
 
					$iRealPrevItemTocLevel = $iRealItemTocLevel;
					$iPrevItemTocLevel = $iItemTocLevel;
					$bfirst = false;
					$sNumToc = implode('.', $aNumToc);
					$sItemTocId = str_replace(array('%', '+', '.F2', '..'), array('.', '.', '_', '.'), urlencode($sItemToc.$sNumToc));
					$sToc .= '<li><a href="#'.$sItemTocId.'">'.$sNumToc.' '.strip_tags($sItemToc).'</a></li>';
					$matches[$_m][2] = $sItemToc;               
					$matches[$_m][3] = $sItemTocId;               
				}
			}

			for ($i = $iCurrentTocLevel; $i > 0; $i--)
				$sToc .= '</ul>';
			        
			reset($matches);
			$bfirst = true;    
			foreach ($matches as $arMatch) 
			{
				$sReplase = '<H'.$arMatch[1].'><span id="'.$arMatch[3].'">'.$arMatch[2].'</span></H'.$arMatch[1].'>';
				if ($bfirst)
					$sReplase = $sToc.'<br/>'.$sReplase;   
				// so as not to replace all of the same titles
				$text = preg_replace('/'.preg_quote($arMatch[0], '/').'/'.BX_UTF_PCRE_MODIFIER, $sReplase, $text, 1);
				$bfirst = false;
			}			    
		}
        				
		return $text;
	}
	
	function _noWikiCallback($matches)
	{
		$i = count($this->arNowiki);
		$this->arNowiki[] = $matches[2];
		
		return '##NOWIKI'.$i.'##';	   
	}
	
	function _noWikiReturnCallback($matches)
	{	
		return $this->arNowiki[$matches[2]];	   
	}	
	
	function _noWikiReturn2Callback($matches)
	{	
		return htmlspecialchars($this->arNowiki[$matches[2]]);			   
	}	
	
	function parseBeforeSave($text, &$arCat = array())
	{
		$userLogin = CWikiUtils::GetUserLogin();
	    
		$text = preg_replace_callback('/(<nowiki>(.*)<\/nowiki>)/isU'.BX_UTF_PCRE_MODIFIER, array(&$this, '_noWikiCallback'), $text);
		
		// Subscribe
		$text = preg_replace( '/--~~~~*/'.BX_UTF_PCRE_MODIFIER, '\\1--'.$userLogin.' '.ConvertTimeStamp(false, 'FULL'), $text );
		
		// Category
		$matches = array();
		if (preg_match_all('/\[\[(Category|'.GetMessage('CATEGORY_NAME').'):(.+)\]\]/iU'.BX_UTF_PCRE_MODIFIER, $text, $matches))
			$arCat = array_unique($matches[2]);

		$text = preg_replace_callback('/(##NOWIKI(\d+)##)/isU'.BX_UTF_PCRE_MODIFIER, array(&$this, '_noWikiReturn2Callback'), $text);
		
		return $text;
	} 

	function parseForSearch($text)
	{
		// delete Category
		$text = preg_replace('/\[\[(Category|'.GetMessage('CATEGORY_NAME').'):(.+)\]\]/iU'.BX_UTF_PCRE_MODIFIER, '', $text);
		// delete Files	    
		$text = preg_replace('/\[?\[(:)?(File|'.GetMessage('FILE_NAME').'):(.+)\]\]?/iU'.BX_UTF_PCRE_MODIFIER, '', $text);	    	    
		// delete External Links
		$text = preg_replace('/\[((http|https|ftp)(.+))( (.+))?\]/iU'.BX_UTF_PCRE_MODIFIER, '\\1\\2 \\5', $text);
		// delete Internal Links		
		$text = preg_replace('/\[\[(.+(?!:))(\|(.*))?\]\]/iU'.BX_UTF_PCRE_MODIFIER, '\\1\\2', $text);
		
		// delete Headers
		for($i = 6; $i >= 1; $i--)
		{
			$_H = str_repeat('=', $i);
			$text = preg_replace('/'.$_H.'(.*?)'.$_H.'/miU'.BX_UTF_PCRE_MODIFIER, '\\1', $text);
		}			
		
		return $text;
	}
	
} 



?>