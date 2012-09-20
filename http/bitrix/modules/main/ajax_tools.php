<?
define ('BX_AJAX_PARAM_ID', 'bxajaxid');

class CAjax
{
	function Init()
	{
		global $APPLICATION;

		$APPLICATION->SetTemplateCSS('ajax/ajax.css');
		$APPLICATION->AddHeadScript('/bitrix/js/main/ajax.js');
	}
	
	function GetComponentID($componentName, $componentTemplate, $additionalID)
	{
		if(function_exists("debug_backtrace"))
		{
			$aTrace = debug_backtrace();
			
			$trace_count = count($aTrace);
			$trace_current = $trace_count-1;
			for ($i = 0; $i<$trace_count; $i++)
			{
				if (strtolower($aTrace[$i]['function']) == 'includecomponent' && (($c = strtolower($aTrace[$i]['class'])) == 'callmain' || $c == 'cmain'))
				{
					$trace_current = $i;
					break;
				}
			}
			
			$sSrcFile = strtolower(str_replace("\\", "/", $aTrace[$trace_current]["file"]));
			$iSrcLine = intval($aTrace[$trace_current]["line"]);
			
			$bSrcFound = false;

			if($iSrcLine > 0 && $sSrcFile <> "")
			{
				// try to covert absolute path to file within DOCUMENT_ROOT
				//$doc_root = strtolower(str_replace("\\", "/", $_SERVER["DOCUMENT_ROOT"]));
				$doc_root = rtrim(str_replace(Array("\\\\", "//", "\\"), Array("\\", "/", "/"), realpath($_SERVER["DOCUMENT_ROOT"])), "\\/");
				$doc_root = strtolower($doc_root);
				
				if(strpos($sSrcFile, $doc_root) === 0)
				{
					//within
					$sSrcFile = substr($sSrcFile, strlen($doc_root));
					$bSrcFound = true;
				}
				else
				{
					//outside
					$sRealBitrix = strtolower(str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"]."/bitrix")));
					if(strpos($sSrcFile, $sRealBitrix) === 0)
					{
						$sSrcFile = "/bitrix".substr($sSrcFile, strlen($sRealBitrix));
						$bSrcFound = true;
					}
				}
			}
			
			if (!$bSrcFound) 
				return false;
		
			$session_string = $sSrcFile.'|'.$iSrcLine.'|'.$componentName;
			
			if (strlen($componentTemplate) > 0)
				$session_string .= '|'.$componentTemplate;
			else
				$session_string .= '|.default';

			$session_string .= '|'.$additionalID;
				
			return md5($session_string);
		}
		
		return false;
	}
	
	function GetSession()
	{
		if (is_set($_REQUEST, BX_AJAX_PARAM_ID))
			return $_REQUEST[BX_AJAX_PARAM_ID];
		else
			return false;
	}
	
	function GetSessionParam($ajax_id = false)
	{
		if (!$ajax_id) $ajax_id = CAjax::GetSession();
		if (!$ajax_id) return '';
		else return BX_AJAX_PARAM_ID.'='.$ajax_id;
	}

	function AddSessionParam($url, $ajax_id = false)
	{
		$url_anchor = strstr($url, '#');
		if ($url_anchor !== false)
			$url = substr($url, 0, -strlen($url_anchor));
		
		$url .= strpos($url, "?") === false ? '?' : '&';
		$url .= CAjax::GetSessionParam($ajax_id);

		if (is_set($_REQUEST['AJAX_CALL'])) $url .= '&AJAX_CALL=Y';
		
		if ($url_anchor !== false)
			$url .= $url_anchor;
	
		return $url;
	}
	
	// $text = htmlspecialchars;
	function GetLinkEx($real_url, $public_url, $text, $container_id, $additional = '', $bReplace = true, $bShadow = true)
	{
		if (!$public_url) $public_url = $real_url;
	
		return sprintf(
			'<a href="%s" onclick="jsAjaxUtil.%sDataToNode(\'%s\', \'%s\', %s); return false;" %s>%s</a>',
			
			$public_url,
			$bReplace ? 'Insert' : 'Append',
			$real_url,
			$container_id,
			$bShadow ? 'true' : 'false',
			$additional,
			$text
		);
	}
	
	// $text - no htmlspecialchars
	function GetLink($url, $text, $container_id, $additional = '', $bReplace = true, $bShadow = true)
	{
		return CAjax::GetLinkEx($url, false, htmlspecialchars($text), htmlspecialchars($container_id), $additional, (bool)$bReplace ? true : false, (bool)$bShadow ? true : false);
	}
	
	function GetForm($form_params, $container_id, $ajax_id, $bReplace = true, $bShadow = true)
	{
		preg_match_all('/onsubmit=(["\']{1})(.*?)\1/i', $form_params, $arParams);
		
		if (count($arParams[0]) <= 0)
			return '<form '.trim($form_params).' '.CAjax::GetFormEvent($container_id, $bReplace, $bShadow).'><input type="hidden" name="'.BX_AJAX_PARAM_ID.'" value="'.$ajax_id.'" />';
		else
		{
			$event_attr = $arParams[0][0];
			$event_delimiter = $arParams[1][0];
			$event_string = $arParams[2][0];
			
			if ($pos = strpos($event_string, 'return') === false)
			{
				$event_string = $event_string.'; return '.CAjax::GetFormEventValue($container_id, $bReplace, $bShadow, $event_delimiter);
			}
			else
			{
				if ($pos > 0) 
				{
					$event_string_start = substr($event_string, 0, $pos).'; ';
					$event_string = substr($event_string, $pos);
				}
				else
					$event_string_start = '';
				
				$event_string = str_replace('return', '', $event_string);
				$event_string = trim($event_string);
				$event_string = rtrim($event_string, ';');
				$event_string = $event_string_start.'return '.$event_string.' && '.CAjax::GetFormEventValue($container_id, $bReplace, $bShadow, $event_delimiter);
			}

			$event_attr_new = 'onsubmit='.$event_delimiter.$event_string.$event_delimiter;
			$form_params = str_replace($event_attr, $event_attr_new, $form_params);
			
			return '<form '.$form_params.'><input type="hidden" name="'.BX_AJAX_PARAM_ID.'" value="'.$ajax_id.'" />';
		}
	}
	
	function ClearForm($form_params, $ajax_id = false)
	{
		$form_params = str_replace(CAjax::GetSessionParam($ajax_id), '', $form_params);
		
		return '<form '.trim($form_params).'>';
	}

	function GetFormEvent($container_id, $bReplace = true, $bShadow = true)
	{
		return 'onsubmit="return jsAjaxUtil.'.($bReplace ? 'Insert' : 'Append').'FormDataToNode(this, \''.$container_id.'\', '.($bShadow ? 'true' : 'false').');"';
	}
	
	function GetFormEventValue($container_id, $bReplace = true, $bShadow = true, $event_delimiter = '\'')
	{
		$delimiter = $event_delimiter == '\'' ? '"' : '\'';
		return 'jsAjaxUtil.'.($bReplace ? 'Insert' : 'Append').'FormDataToNode(this, '.$delimiter.$container_id.$delimiter.', '.($bShadow ? 'true' : 'false').')';
	}
	
	function encodeURI($str)
	{
		$str = 'view'.$str;
		
		return $str;
	}

	function decodeURI($str)
	{
		global $APPLICATION;
	
		$pos = strpos($str, 'view');
		if ($pos !== 0) 
		{
			$APPLICATION->ThrowException(GetMessage('AJAX_REDIRECTOR_BAD_URL'));
			return false;
		}

		$str = substr($str, 4);
		
		if (substr($str, 0, 8) == '/bitrix/')
		{
			$APPLICATION->ThrowException(GetMessage('AJAX_REDIRECTOR_BAD_URL'));
			return false;
		}
		
		return $str;
	}
}
?>