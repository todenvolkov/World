<?
IncludeModuleLangFile(__FILE__);

class CSocServFacebook extends CSocServAuth
{
	const ID = "Facebook";

	public function GetDescription()
	{
		return array(
			"ID" => self::ID,
			"CLASS" => "CSocServFacebook",
			"NAME" => "Facebook",
			"ICON" => "facebook",
		);
	}
	
	public function GetSettings()
	{
		return array(
			array("facebook_appid", GetMessage("socserv_fb_id"), "", Array("text", 40)),
			array("facebook_appsecret", GetMessage("socserv_fb_secret"), "", Array("text", 40)),
			array("note"=>GetMessage("socserv_fb_sett_note")),
		);
	}

	public function GetFormHtml($arParams)
	{
		$redirect_uri = CSocServUtil::GetCurUrl('auth_service_id='.self::ID);

		$appID = self::GetOption("facebook_appid");
		$appSecret = self::GetOption("facebook_appsecret");

		$fb = new CFacebookInterface($appID, $appSecret);
		$url = $fb->GetAuthUrl($redirect_uri);

		return '<a href="javascript:void(0)" onclick="BX.util.popup(\''.htmlspecialchars(CUtil::JSEscape($url)).'\', 580, 400)" class="bx-ss-button facebook-button"></a><span class="bx-spacer"></span><span>'.GetMessage("socserv_fb_note").'</span>';
	}
	
	public function Authorize()
	{
		$GLOBALS["APPLICATION"]->RestartBuffer();
		$bSuccess = false;
		if(isset($_REQUEST["code"]) && $_REQUEST["code"] <> '')
		{
			$redirect_uri = CSocServUtil::GetCurUrl('auth_service_id='.self::ID, array("code"));

			$appID = self::GetOption("facebook_appid");
			$appSecret = self::GetOption("facebook_appsecret");

			$fb = new CFacebookInterface($appID, $appSecret, $_REQUEST["code"]);

			if($fb->GetAccessToken($redirect_uri) !== false)
			{
				$arFBUser = $fb->GetCurrentUser();
				if(isset($arFBUser["id"]))
				{
					$dbUsers = $GLOBALS["USER"]->GetList($by, $ord, array('XML_ID'=>$arFBUser["id"], 'EXTERNAL_AUTH_ID'=>self::ID));
					if($arUser = $dbUsers->Fetch())
					{
						$USER_ID = $arUser["ID"];
					}
					else
					{
						$arFields = array(
							'EXTERNAL_AUTH_ID' => self::ID,
							'LOGIN' => $arFBUser["email"],
							'XML_ID' => $arFBUser["id"],
							'EMAIL' => $arFBUser["email"],
							'NAME'=> $arFBUser["first_name"],
							'LAST_NAME'=> $arFBUser["last_name"],
							'PASSWORD' => randString(30),
						);
						$def_group = COption::GetOptionString('main', 'new_user_registration_def_group', '');
						if($def_group != '')
							$arFields['GROUP_ID'] = explode(',', $def_group);
		
						if(!($USER_ID = $GLOBALS["USER"]->Add($arFields)))
							return false;
					}
					$GLOBALS["USER"]->Authorize($USER_ID);
					$bSuccess = true;
				}
			}
		}

		$aRemove = array("logout", "auth_service_error", "auth_service_id", "code", "error_reason", "error", "error_description");
		$url = $GLOBALS['APPLICATION']->GetCurPageParam(($bSuccess? '':'auth_service_id='.self::ID.'&auth_service_error=1'), $aRemove);
		echo '
<script type="text/javascript">
if(window.opener)
	window.opener.location = \''.CUtil::JSEscape($url).'\';
window.close();
</script>
';
		die();
	}
}

class CFacebookInterface
{
	const AUTH_URL = "https://www.facebook.com/dialog/oauth";
	const GRAPH_URL = "https://graph.facebook.com";

	protected $appID;
	protected $appSecret;
	protected $code = false;
	protected $access_token = false;
	
	public function __construct($appID, $appSecret, $code=false)
	{
		$this->appID = $appID;
		$this->appSecret = $appSecret;
		$this->code = $code;
	}

	public function GetAuthUrl($redirect_uri)
	{
		return self::AUTH_URL."?client_id=".$this->appID."&redirect_uri=".urlencode($redirect_uri)."&scope=email&display=popup";
	}
	
	public function GetAccessToken($redirect_uri)
	{
		if($this->code === false)
			return false;

		$result = CHTTP::sGet(self::GRAPH_URL.'/oauth/access_token?client_id='.$this->appID.'&client_secret='.$this->appSecret.'&redirect_uri='.urlencode($redirect_uri).'&code='.urlencode($this->code));

		$arResult = array();
		parse_str($result, $arResult);
		if(isset($arResult["access_token"]) && $arResult["access_token"] <> '')
		{
			$this->access_token = $arResult["access_token"];
			return true;
		}
		return false;
	}
	
	public function GetCurrentUser()
	{
		if($this->access_token === false)
			return false;

		$result = CHTTP::sGet(self::GRAPH_URL.'/me?access_token='.$this->access_token);

		return CUtil::JsObjectToPhp($result);
	}
}

AddEventHandler("socialservices", "OnAuthServicesBuildList", array("CSocServFacebook", "GetDescription"));
?>