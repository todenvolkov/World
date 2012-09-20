<?
IncludeModuleLangFile(__FILE__);

class CSocServVKontakte extends CSocServAuth
{
	const ID = "VKontakte";
	
	public function GetDescription()
	{
		return array(
			"ID" => self::ID,
			"CLASS" => "CSocServVKontakte",
			"NAME" => GetMessage("socserv_vk_name"),
			"ICON" => "vkontakte",
		);
	}
	
	public function GetSettings()
	{
		return array(
			array("vkontakte_appid", GetMessage("socserv_vk_id"), "", Array("text", 40)),
			array("vkontakte_appsecret", GetMessage("socserv_vk_key"), "", Array("text", 40)),
			array("note"=>GetMessage("socserv_vk_sett_note")),
		);
	}

	public function GetFormHtml($arParams)
	{
		$aRemove = array("logout", "auth_service_error", "auth_service_id");
		$url_err = $GLOBALS['APPLICATION']->GetCurPageParam('auth_service_id='.self::ID.'&auth_service_error=1', $aRemove);
		$url_ok = $GLOBALS['APPLICATION']->GetCurPageParam('', $aRemove);

		$script = '
<script type="text/javascript" src="http://vkontakte.ru/js/api/openapi.js"></script>
<script type="text/javascript">
BX.ready(function(){VK.init({apiId: \''.CUtil::JSEscape(self::GetOption("vkontakte_appid")).'\'});});

function BxVKAuthInfo(response) 
{
	var url_err = \''.CUtil::JSEscape($url_err).'\';
	if(response.session) 
	{
		var url_post = \''.CUtil::JSEscape($arParams["~AUTH_URL"]).'\';
		var url_ok = \''.CUtil::JSEscape($url_ok).'\';
		var data = {
			"auth_service_id": "'.self::ID.'",
			"vk_session": response.session
		};
		BX.ajax.post(url_post, data, function(res){window.location = (res == "OK"? url_ok : url_err);});
	} 
	else 
	{
		window.location = url_err;
	}
}
</script>
';
		CUtil::InitJSCore(array("ajax"));
		$GLOBALS['APPLICATION']->AddHeadString($script, true);

		$s = '
<a href="javascript:void(0)" onclick="VK.Auth.login(BxVKAuthInfo);" class="bx-ss-button vkontakte-button"></a><span class="bx-spacer"></span><span>'.GetMessage("socserv_vk_note").'</span>';
		return $s;
	}
	
	public function Authorize()
	{
		$GLOBALS["APPLICATION"]->RestartBuffer();
		
		if(isset($_REQUEST["vk_session"]["user"]["id"]))
		{
			if(self::CheckUserData($_REQUEST["vk_session"]["sig"]))
			{
				$u_id = $_REQUEST["vk_session"]["user"]["id"];
	
				$dbUsers = $GLOBALS["USER"]->GetList($by, $ord, array('XML_ID'=>$u_id, 'EXTERNAL_AUTH_ID'=>self::ID));
				if($arUser = $dbUsers->Fetch())
				{
					$USER_ID = $arUser["ID"];
				}
				else
				{
					CUtil::decodeURIComponent($_REQUEST);
					$arFields = array(
						'EXTERNAL_AUTH_ID' => self::ID,
						'LOGIN' => "id".$u_id,
						'XML_ID' => $u_id,
						'NAME'=> $_REQUEST["vk_session"]["user"]["first_name"],
						'LAST_NAME'=> $_REQUEST["vk_session"]["user"]["last_name"],
						'PASSWORD' => randString(30),
					);
					$def_group = COption::GetOptionString('main', 'new_user_registration_def_group', '');
					if($def_group != '')
						$arFields['GROUP_ID'] = explode(',', $def_group);
	
					if(!($USER_ID = $GLOBALS["USER"]->Add($arFields)))
						return false;
				}
				$GLOBALS["USER"]->Authorize($USER_ID);
				die("OK");
			}
		}
		die("FAILURE");
	}
	
	protected function CheckUserData($control_sign)
	{
		$APP_ID = self::GetOption("vkontakte_appid");
		$APP_SECRET = self::GetOption("vkontakte_appsecret");

		$app_cookie = $_COOKIE['vk_app_'.$APP_ID];
		if($app_cookie == '') 
			return false;

		$session = array();
		parse_str($app_cookie, $session);

		static $valid_keys = array('expire'=>1, 'mid'=>1, 'secret'=>1, 'sid'=>1, 'sig'=>1);
		foreach($valid_keys as $key=>$v) 
			if(!isset($session[$key])) 
				return false;
    	
    	ksort($session);

		$sign = '';
		foreach($session as $key=>$value) 
			if($key <> 'sig' && array_key_exists($key, $valid_keys)) 
				$sign .= ($key.'='.$value);

		$sign .= $APP_SECRET;
		$sign = md5($sign);

		if($control_sign === $sign && $control_sign === $session['sig'] && $session['expire'] > time()) 
			return true;

  		return false;
	}
}

AddEventHandler("socialservices", "OnAuthServicesBuildList", array("CSocServVKontakte", "GetDescription"));
?>