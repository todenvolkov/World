<?
IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/classes/general/openid.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/classes/general/liveid.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/classes/general/facebook.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/classes/general/twitter.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/classes/general/vkontakte.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/classes/general/mailru.php");

class CSocServAuthManager
{
	private static $arAuthServices = false;

	public function __construct()
	{
		if(!is_array(CSocServAuthManager::$arAuthServices))
		{
			CSocServAuthManager::$arAuthServices = array();

			$db_events = GetModuleEvents("socialservices", "OnAuthServicesBuildList");
			while($arEvent = $db_events->Fetch())
			{
				$res = ExecuteModuleEventEx($arEvent);
				CSocServAuthManager::$arAuthServices[$res["ID"]] = $res;
			}
			
			//user settings: sorting, active
			$arServices = unserialize(COption::GetOptionString("socialservices", "auth_services", ""));
			if(is_array($arServices))
			{
				$i = 0;
				foreach($arServices as $serv=>$active)
				{
					if(isset(CSocServAuthManager::$arAuthServices[$serv]))
					{
						CSocServAuthManager::$arAuthServices[$serv]["__sort"] = $i++;
						CSocServAuthManager::$arAuthServices[$serv]["__active"] = ($active == "Y");
					}
				}
				uasort(CSocServAuthManager::$arAuthServices, create_function('$a, $b', 'if($a["__sort"] == $b["__sort"]) return 0; return ($a["__sort"] < $b["__sort"])? -1 : 1;'));
			}
			
		}
	}
	
	public function GetAuthServices()
	{
		return CSocServAuthManager::$arAuthServices;
	}

	public function GetActiveAuthServices($arParams)
	{
		$aServ = array();
		foreach(CSocServAuthManager::$arAuthServices as $key=>$service)
		{
			if($service["__active"] === true && $service["DISABLED"] !== true)
			{
				$cl = new $service["CLASS"];
				if(is_callable(array($cl, "CheckSettings")))
					if(!call_user_func_array(array($cl, "CheckSettings"), array()))
						continue;

				if(is_callable(array($cl, "GetFormHtml")))
					$service["FORM_HTML"] = call_user_func_array(array($cl, "GetFormHtml"), array($arParams));

				$aServ[$key] = $service;
			}
		}
		return $aServ;
	}
	
	public function GetSettings()
	{
		$arOptions = array();
		foreach(CSocServAuthManager::$arAuthServices as $key=>$service)
		{
			if(is_callable(array($service["CLASS"], "GetSettings")))
			{
				$arOptions[] = htmlspecialchars($service["NAME"]);
				$options = call_user_func_array(array($service["CLASS"], "GetSettings"), array());
				if(is_array($options))
					foreach($options as $opt)
						$arOptions[] = $opt;
			}
		}
		return $arOptions;
	}
	
	public function Authorize($service_id)
	{
		if(isset(CSocServAuthManager::$arAuthServices[$service_id]))
		{
			$service = CSocServAuthManager::$arAuthServices[$service_id];
			if($service["__active"] === true && $service["DISABLED"] !== true)
				if(is_callable(array($service["CLASS"], "Authorize")))
					return call_user_func_array(array($service["CLASS"], "Authorize"), array());
		}
		return false;
	}
	
	public function GetError($service_id, $error_code)
	{
		if(isset(CSocServAuthManager::$arAuthServices[$service_id]))
		{
			$service = CSocServAuthManager::$arAuthServices[$service_id];
			if(is_callable(array($service["CLASS"], "GetError")))
				return call_user_func_array(array($service["CLASS"], "GetError"), array($error_code));
			return GetMessage("socserv_controller_error", array("#SERVICE_NAME#"=>$service["NAME"]));
		}
		return '';
	}
}

class CSocServAuth
{
	public function GetSettings()
	{
		return false;
	}

	public function CheckSettings()
	{
		$arSettings = $this->GetSettings();
		if(is_array($arSettings))
		{
			foreach($arSettings as $sett)
				if(is_array($sett) && !array_key_exists("note", $sett))
					if($this->GetOption($sett[0]) == '')
						return false;
		}
		return true;
	}
	
	protected function GetOption($opt)
	{
		return COption::GetOptionString("socialservices", $opt);
	}
}

class CSocServUtil
{
	public static function GetCurUrl($addParam="", $removeParam=false)
	{
		$arRemove = array("logout", "auth_service_error", "auth_service_id");
		if($removeParam !== false)
			$arRemove = array_merge($arRemove, $removeParam);

		$protocol = (CMain::IsHTTPS() ? "https" : "http");
		$port = ($_SERVER['SERVER_PORT'] > 0 && $_SERVER['SERVER_PORT'] <> 80? ':'.$_SERVER['SERVER_PORT']:'');

		return $protocol.'://'.$_SERVER['SERVER_NAME'].$port.$GLOBALS['APPLICATION']->GetCurPageParam($addParam, $arRemove);
	}
}
?>