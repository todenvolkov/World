<?
class CSocServGoogleOAuth extends CSocServAuth
{
	const ID = "GoogleOAuth";

	public function GetDescription()
	{
		return array(
			"ID" => self::ID,
			"CLASS" => "CSocServGoogleOAuth",
			"NAME" => "Google",
			"ICON" => "google",
		);
	}
	
	public function GetSettings()
	{
		return array(
			array("google_appid", "Идентификатор (Client ID):", "", Array("text", 40)),
			array("google_appsecret", "Секретный код (Client secret):", "", Array("text", 40)),
			array("note"=>'Необходимо <a href="http://code.google.com/apis/console">создать приложение</a> (вкладка Identity) для получения идентификаторов.'),
		);
	}

	public function GetFormHtml($arParams)
	{
		$redirect_uri = CSocServUtil::GetCurUrl('auth_service_id='.self::ID);

		$appID = self::GetOption("google_appid");
		$appSecret = self::GetOption("google_appsecret");

		$gAuth = new CGoogleOAuthInterface($appID, $appSecret);
		$url = $gAuth->GetAuthUrl($redirect_uri);

		return '<a href="javascript:void(0)" onclick="BX.util.popup(\''.htmlspecialchars(CUtil::JSEscape($url)).'\', 580, 400)" class="bx-ss-button google-button"></a><span class="bx-spacer"></span><span>'.'Используйте вашу учетную запись Google для входа на сайт.'.'</span>';
	}
	
	public function Authorize()
	{
		$GLOBALS["APPLICATION"]->RestartBuffer();
		$bSuccess = false;
		if(isset($_REQUEST["code"]) && $_REQUEST["code"] <> '')
		{
			$redirect_uri = CSocServUtil::GetCurUrl('auth_service_id='.self::ID, array("code"));

			$appID = self::GetOption("google_appid");
			$appSecret = self::GetOption("google_appsecret");

			$gAuth = new CGoogleOAuthInterface($appID, $appSecret, $_REQUEST["code"]);

			if($gAuth->GetAccessToken($redirect_uri) !== false)
			{
				$arGoogleUser = $gAuth->GetCurrentUser();
/*
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
*/
			}
		}

		$aRemove = array("logout", "auth_service_error", "auth_service_id", "code", "error_reason", "error", "error_description");
		$url = $GLOBALS['APPLICATION']->GetCurPageParam(($bSuccess? '':'auth_service_id='.self::ID.'&auth_service_error=1'), $aRemove);
/*
		echo '
<script type="text/javascript">
if(window.opener)
	window.opener.location = \''.CUtil::JSEscape($url).'\';
window.close();
</script>
';
*/
		die();
	}
}

class CGoogleOAuthInterface
{
	const AUTH_URL = "https://accounts.google.com/o/oauth2/auth";
	const TOKEN_URL = "https://accounts.google.com/o/oauth2/token";
	const USER_URL = "https://www-opensocial.googleusercontent.com/api/people/";
	const EMAIL_URL = "https://www.googleapis.com/userinfo";

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
$redirect_uri = "http://office.bitrix.ru/vad/main.profile.php?auth_service_id=GoogleOAuth";
		return self::AUTH_URL.
			"?client_id=".urlencode($this->appID).
			"&redirect_uri=".urlencode($redirect_uri).
			"&scope=https://www.google.com/m8/feeds/".//urlencode(self::USER_URL).
			"&response_type=code";
	}
	
	public function GetAccessToken($redirect_uri)
	{
$redirect_uri = "http://office.bitrix.ru/vad/main.profile.php?auth_service_id=GoogleOAuth";
		if($this->code === false)
			return false;

		$result = CHTTP::sPost(self::TOKEN_URL, array(
			"code"=>$this->code,
			"client_id"=>$this->appID,
			"client_secret"=>$this->appSecret,
			"redirect_uri"=>$redirect_uri,
			"grant_type"=>"authorization_code",
		));

		$arResult = CUtil::JsObjectToPhp($result);

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

echo $this->access_token."<hr>";

		$result = CHTTP::sGet(self::USER_URL.'contacts/default/full?oauth_token='.urlencode($this->access_token));
		echo($result);

//		$result = CHTTP::sGet(self::EMAIL_URL.'/email?oauth_token='.$this->access_token);
//		print_r($result);

//		return CUtil::JsObjectToPhp($result);
	}
}

AddEventHandler("socialservices", "OnAuthServicesBuildList", array("CSocServGoogleOAuth", "GetDescription"));
?>