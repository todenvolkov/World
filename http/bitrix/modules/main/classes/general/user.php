<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/user.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/admin/task_description.php");

global $BX_GROUP_POLICY;
$BX_GROUP_POLICY = Array(
	"SESSION_TIMEOUT"	=>	0, //minutes
	"SESSION_IP_MASK"	=>	"0.0.0.0",
	"MAX_STORE_NUM"		=>	10,
	"STORE_IP_MASK"		=>	"0.0.0.0",
	"STORE_TIMEOUT"		=>	60*24*365, //minutes
	"CHECKWORD_TIMEOUT"	=>	60*24*365,  //minutes
	"PASSWORD_LENGTH"	=>	false,
	"PASSWORD_UPPERCASE"	=>	"N",
	"PASSWORD_LOWERCASE"	=>	"N",
	"PASSWORD_DIGITS"	=>	"N",
	"PASSWORD_PUNCTUATION"	=>	"N",
	"LOGIN_ATTEMPTS"	=>	0,
);

class CAllUser extends CDBResult
{
	var $LAST_ERROR="";

	function GetParam($name)
	{
		return $_SESSION["SESS_AUTH"][$name];
	}

	function GetSecurityPolicy()
	{
		if(!is_set($_SESSION["SESS_AUTH"], "POLICY"))
			$_SESSION["SESS_AUTH"]["POLICY"] = CUser::GetGroupPolicy($_SESSION["SESS_AUTH"]["USER_ID"]);
		return $_SESSION["SESS_AUTH"]["POLICY"];
	}

	function SetParam($name, $value)
	{
		$_SESSION["SESS_AUTH"][$name] = $value;
	}

	function GetID()
	{
		return $_SESSION["SESS_AUTH"]["USER_ID"];
	}

	function GetLogin()
	{
		return $_SESSION["SESS_AUTH"]["LOGIN"];
	}

	function GetEmail()
	{
		return $_SESSION["SESS_AUTH"]["EMAIL"];
	}

	function GetFullName()
	{
		return $_SESSION["SESS_AUTH"]["NAME"];
	}

	function GetFirstName()
	{
		return $_SESSION["SESS_AUTH"]["FIRST_NAME"];
	}

	function GetLastName()
	{
		return $_SESSION["SESS_AUTH"]["LAST_NAME"];
	}

	function GetUserGroupArray()
	{
		$res = $_SESSION["SESS_AUTH"]["GROUPS"];
		$res[] = 2;
		return array_values(array_unique($res));
	}

	function SetUserGroupArray($arr)
	{
		$arr[] = 2;
		$_SESSION["SESS_AUTH"]["GROUPS"] = array_values(array_unique($arr));
	}

	function GetUserGroupString()
	{
		return $this->GetGroups();
	}

	function GetGroups()
	{
		return implode(",", $this->GetUserGroupArray());
	}

	// It could be refactored by returning False instead of call die() for failure.
	function RequiredHTTPAuthBasic($Realm = "Bitrix")
	{
		header("WWW-Authenticate: Basic realm=\"{$Realm}\"");
		if(stristr(php_sapi_name(), "cgi") !== false)
			header("Status: 401 Unauthorized");
		else
			header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");

		//ShowError("This realm=\"{$Realm}\" requires Basic HTTP Auth.");
		return false;
	}

	function LoginByHash($login, $hash)
	{
		global $DB, $APPLICATION;
		$result_message = true;
		$user_id = 0;
		$arParams = Array(
			"LOGIN"		=>	&$login,
			"HASH"	=>	&$hash,
			);

		$APPLICATION->ResetException();
		$bOk = true;
		$db_events = GetModuleEvents("main", "OnBeforeUserLoginByHash");
		while($arEvent = $db_events->Fetch())
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams))===false)
			{
				if($err = $APPLICATION->GetException())
					$result_message = Array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$result_message = Array("MESSAGE"=>"Unknown error"."<br>", "TYPE"=>"ERROR");
				}

				$bOk = false;
				break;
			}
		}

		if($bOk)
		{
			$strSql =
				"SELECT U.ID, U.ACTIVE, U.STORED_HASH ".
				"FROM b_user U ".
				"WHERE U.LOGIN='".$DB->ForSQL($arParams['LOGIN'], 50)."' ".
				"	AND (U.EXTERNAL_AUTH_ID IS NULL OR U.EXTERNAL_AUTH_ID='') ";
			$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			if(strlen($arParams['HASH'])>0 && $arUser = $result->Fetch())
			{
				$stored_id = 0;
				if(
					// если старый вариант (strlen(STORED_HASH)>0) и полное совпадение
					(strlen($arUser["STORED_HASH"])>0 && $arUser["STORED_HASH"] == $arParams['HASH'])
					|| // или авторизация по новому варианту
					(CUser::CheckStoredHash($arUser["ID"], $arParams['HASH']))
				)
				{
					if($arUser["ACTIVE"] == "Y")
					{
						$_SESSION["SESS_AUTH"]["SESSION_HASH"] = $arParams['HASH'];
						$this->bLoginByHash = true;
						$this->Authorize($arUser["ID"], true);
					}
					else
					{
						$APPLICATION->ThrowException(GetMessage("LOGIN_BLOCK"));
						$result_message = Array("MESSAGE"=>GetMessage("LOGIN_BLOCK")."<br>", "TYPE"=>"ERROR");
					}
				}
				else
				{
					$APPLICATION->ThrowException(GetMessage("USER_WRONG_HASH"));
					$result_message = Array("MESSAGE"=>GetMessage("USER_WRONG_HASH")."<br>", "TYPE"=>"ERROR");
				}
			}
			else
			{
				$APPLICATION->ThrowException(GetMessage("WRONG_LOGIN"));
			 	$result_message = Array("MESSAGE"=>GetMessage("WRONG_LOGIN")."<br>", "TYPE"=>"ERROR");
			}
		}

		$arParams["USER_ID"] = &$user_id;
		$arParams["RESULT_MESSAGE"] = &$result_message;

		$events = GetModuleEvents("main", "OnAfterUserLoginByHash");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$arParams));

		if(($result_message !== true) && (COption::GetOptionString("main", "event_log_login_fail", "N") === "Y"))
			CEventLog::Log("SECURITY", "USER_LOGINBYHASH", "main", $login, $result_message["MESSAGE"]);

		return $arParams["RESULT_MESSAGE"];
	}

	function LoginHitByHash()
	{

		global $USER, $DB, $APPLICATION;

		if ($USER->IsAuthorized())
			return true;

		$hash = trim($_REQUEST["bx_hit_hash"]);
		if (strlen($hash) <= 0)
			return false;

		$result_message = true;
		$user_id = 0;

		$APPLICATION->ResetException();

		$strSql =
			"SELECT UH.USER_ID AS USER_ID ".
			"FROM b_user_hit_auth UH ".
			"INNER JOIN b_user U ON U.ID = UH.USER_ID AND U.ACTIVE ='Y' ".
			"WHERE UH.HASH = '".$DB->ForSQL($hash, 32)."' ".
			"	AND '".$DB->ForSqlLike($GLOBALS["APPLICATION"]->GetCurPageParam("", array(), true), 500)."' LIKE ".$DB->Concat("UH.URL", "'%'");

		if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
			$strSql .= " AND UH.SITE_ID = '".SITE_ID."'";

		$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if($arUser = $result->Fetch())
		{
			register_shutdown_function('session_destroy');
			$this->Authorize($arUser["USER_ID"], false);

			$DB->Query("UPDATE b_user_hit_auth SET TIMESTAMP_X = ".$DB->GetNowFunction()." WHERE HASH='".$DB->ForSQL($hash, 32)."'");
			return true;
		}
		else
			return false;
	}

	function AddHitAuthHash($url, $user_id = false, $site_id = false)
	{
		global $USER, $DB;

		if (strlen($url) <= 0)
			return false;

		if (!$user_id)
			$user_id = $USER->GetID();

		if (!$site_id && (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true))
			$site_id = SITE_ID;

		$hash = false;

		if ($user_id)
		{
			$hash = md5(uniqid(rand(), true));
			$arFields = Array(
				'USER_ID' => $user_id,
				'URL' => $DB->ForSqlLike(trim($url), 500),
				'HASH' => $hash,
				'SITE_ID' => $DB->ForSQL(trim($site_id), 2),
				'~TIMESTAMP_X'=>$DB->CurrentTimeFunction()
			);
			$stored_id = CDatabase::Add("b_user_hit_auth", $arFields);
		}

		return $hash;
	}

	function GetHitAuthHash($url_mask, $userID = false)
	{

		global $USER, $DB, $APPLICATION;

		$url_mask = trim($url_mask);
		if (strlen($url_mask) <= 0)
			return false;

		if (!$userID)
		{
			if (!$USER->IsAuthorized())
				return false;
			else
				$userID = $USER->GetID();
		}

		$strSql =
			"SELECT ID, HASH ".
			"FROM b_user_hit_auth ".
			"WHERE URL = '".$DB->ForSqlLike($url_mask, 500)."' AND USER_ID = ".intval($userID);

		$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if($arTmp = $result->Fetch())
			return $arTmp["HASH"];
		else
			return false;
	}

	function CleanUpHitAuthAgent()
	{
		global $DB;
		$cleanup_days = COption::GetOptionInt("main", "hit_auth_cleanup_days", 30);
		if($cleanup_days > 0)
		{
			$arDate = localtime(time());
			$date = mktime(0, 0, 0, $arDate[4]+1, $arDate[3]-$cleanup_days, 1900+$arDate[5]);
			$DB->Query("DELETE FROM b_user_hit_auth WHERE TIMESTAMP_X <= ".$DB->CharToDateFunction(ConvertTimeStamp($date, "FULL")));

		}
		return "CUser::CleanUpHitAuthAgent();";
	}

	/*
	Авторизация
		- Инициализация всех сессионных параметров
		- Запоминание пользователя на сервере
		- Размножение авторизационных параметров между сайтами
	Логин
	 	- проверка имени/пароля
		- "Авторизация"
	Логин по хешу
		- проверка имени/хеша
		- "Авторизация"
	*/

	function Authorize($id, $bSave = false)
	{
		global $DB, $APPLICATION;
		$strSql =
			"SELECT U.* ".
			"FROM b_user U  ".
			"WHERE U.ID='".IntVal($id)."' ";

		unset($_SESSION["OPERATIONS"]);
		$_SESSION["BX_LOGIN_NEED_CAPTCHA"] = false;

		$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if($arUser = $result->Fetch())
		{
			/*
			if(COption::GetOptionString("security", "session", "N") === "Y" && CModule::IncludeModule("security"))
				CSecuritySession::UpdateSessID();
			else
				session_regenerate_id();
			*/

			$_SESSION["SESS_AUTH"]["AUTHORIZED"] = "Y";
			$_SESSION["SESS_AUTH"]["USER_ID"] = $arUser["ID"];
			$_SESSION["SESS_AUTH"]["LOGIN"] = $arUser["LOGIN"];
			$_SESSION["SESS_AUTH"]["LOGIN_COOKIES"] = $arUser["LOGIN"];
			$_SESSION["SESS_AUTH"]["EMAIL"] = $arUser["EMAIL"];
			$_SESSION["SESS_AUTH"]["PASSWORD_HASH"] = $arUser["PASSWORD"];
			$_SESSION["SESS_AUTH"]["NAME"] = $arUser["NAME"].(strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0?"":" ").$arUser["LAST_NAME"];
			$_SESSION["SESS_AUTH"]["FIRST_NAME"] = $arUser["NAME"];
			$_SESSION["SESS_AUTH"]["SECOND_NAME"] = $arUser["SECOND_NAME"];
			$_SESSION["SESS_AUTH"]["LAST_NAME"] = $arUser["LAST_NAME"];
			$_SESSION["SESS_AUTH"]["GROUPS"] = array();
			$_SESSION["SESS_AUTH"]["ADMIN"] = false;
			$_SESSION["SESS_AUTH"]["POLICY"] = CUser::GetGroupPolicy($arUser["ID"]);

			$strSql =
				"SELECT G.ID ".
				"FROM b_group G  ".
				"WHERE G.ANONYMOUS='Y' ".
				"	AND G.ACTIVE='Y' ";

			$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			while($ar = $result->Fetch())
			{
				$_SESSION["SESS_AUTH"]["GROUPS"][] = IntVal($ar["ID"]);
				if(IntVal($ar["ID"])==1)
					$_SESSION["SESS_AUTH"]["ADMIN"] = true;
			}

			$strSql =
				"SELECT G.ID ".
				"FROM b_user_group UG, b_group G  ".
				"WHERE UG.USER_ID = ".$arUser["ID"]." ".
				"	AND G.ID=UG.GROUP_ID  ".
				"	AND G.ACTIVE='Y' ".
				"	AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) ".
				"	AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")) ".
				"	AND (G.ANONYMOUS<>'Y' OR G.ANONYMOUS IS NULL) ";

			$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			while($ar = $result->Fetch())
			{
				$_SESSION["SESS_AUTH"]["GROUPS"][] = IntVal($ar["ID"]);
				if(IntVal($ar["ID"])==1)
					$_SESSION["SESS_AUTH"]["ADMIN"] = true;
			}

			$DB->Query("
				UPDATE b_user SET
					STORED_HASH = NULL,
					LAST_LOGIN = ".$DB->GetNowFunction().",
					TIMESTAMP_X = TIMESTAMP_X,
					LOGIN_ATTEMPTS = 0
				WHERE
					ID=".$_SESSION["SESS_AUTH"]["USER_ID"]."
			");

			$APPLICATION->set_cookie("LOGIN", $_SESSION["SESS_AUTH"]["LOGIN_COOKIES"], time()+60*60*24*30*60, '/', false, false, COption::GetOptionString("main", "auth_multisite", "N")=="Y");
			if($bSave || COption::GetOptionString("main", "auth_multisite", "N")=="Y")
			{
				$hash = $this->GetSessionHash();
				$secure = (COption::GetOptionString("main", "use_secure_password_cookies", "N")=="Y" && CMain::IsHTTPS());

				if($bSave)
					$APPLICATION->set_cookie("UIDH", $hash, time()+60*60*24*30*60, '/', false, $secure, BX_SPREAD_SITES | BX_SPREAD_DOMAIN);
				else
					$APPLICATION->set_cookie("UIDH", $hash, 0, '/', false, $secure, BX_SPREAD_SITES);

				$stored_id = CUser::CheckStoredHash($arUser["ID"], $hash);
				if($stored_id)
				{
					$DB->Query(
						"UPDATE b_user_stored_auth SET
							LAST_AUTH=".$DB->CurrentTimeFunction().",
							".($this->bLoginByHash?"":"TEMP_HASH='".($bSave?"N":"Y")."', ")."
							IP_ADDR='".sprintf("%u", ip2long($_SERVER["REMOTE_ADDR"]))."'
						WHERE ID=".$stored_id
					);
				}
				else
				{
					$arFields = Array(
							'USER_ID'=>$arUser["ID"],
							'~DATE_REG'=>$DB->CurrentTimeFunction(),
							'~LAST_AUTH'=>$DB->CurrentTimeFunction(),
							'TEMP_HASH'=>($bSave?"N":"Y"),
							'~IP_ADDR'=>sprintf("%u", ip2long($_SERVER["REMOTE_ADDR"])),
							'STORED_HASH'=>$hash
						);
					$stored_id = CDatabase::Add("b_user_stored_auth", $arFields);
				}
				$_SESSION["SESS_AUTH"]["STORED_AUTH_ID"] = $stored_id;
			}

			$arParams = Array(
					"user_fields" => $arUser,
					"save" => $bSave
				);

			$events = GetModuleEvents("main", "OnAfterUserAuthorize");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array(&$arParams));

			$events = GetModuleEvents("main", "OnUserLogin");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($_SESSION["SESS_AUTH"]["USER_ID"]));

			if(COption::GetOptionString("main", "event_log_login_success", "N") === "Y")
				CEventLog::Log("SECURITY", "USER_AUTHORIZE", "main", $arUser["ID"]);

			return true;
		}
		return false;
	}

	function GetSessionHash()
	{
		$cookie_md5pass = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_UIDH";
		if(strlen($_SESSION["SESS_AUTH"]["SESSION_HASH"])<=0)
		{
			if(strlen($_COOKIE[$cookie_md5pass])==32)
				$_SESSION["SESS_AUTH"]["SESSION_HASH"] = $_COOKIE[$cookie_md5pass];
			else
				$_SESSION["SESS_AUTH"]["SESSION_HASH"] = md5(uniqid(rand(), true));
		}
		return $_SESSION["SESS_AUTH"]["SESSION_HASH"];
	}

	function GetPasswordHash($PASSWORD_HASH)
	{
		// deprecated
		$add = COption::GetOptionString("main", "pwdhashadd", "");
		if(strlen($add)<=0)
		{
			$add = md5(uniqid(rand(), true));
			COption::SetOptionString("main", "pwdhashadd", $add);
		}

		return md5($add.$PASSWORD_HASH);
	}

	function SavePasswordHash()
	{
		// deprecated
		global $APPLICATION;
		$hash = $this->GetSessionHash();
		//$hash = CUser::GetPasswordHash($_SESSION["SESS_AUTH"]["PASSWORD_HASH"]);
		$time = time()+60*60*24*30*60;
		$secure = 0;
		if(COption::GetOptionString("main", "use_secure_password_cookies", "N")=="Y" && CMain::IsHTTPS())
				$secure=1;

		$APPLICATION->set_cookie("UIDH", $hash, $time, '/', false, $secure, COption::GetOptionString("main", "auth_multisite", "N")=="Y");
	}

	//авторизуем пользователя
	function Login($login, $password, $remember="N", $password_original="Y")
	{
		global $DB, $APPLICATION;
		$result_message = true;
		$user_id = 0;
		$arParams = Array(
			"LOGIN"		=>	&$login,
			"PASSWORD"	=>	&$password,
			"REMEMBER" 	=> 	&$remember,
			"PASSWORD_ORIGINAL"	=>	&$password_original
			);

		unset($_SESSION["OPERATIONS"]);
		$_SESSION["BX_LOGIN_NEED_CAPTCHA"] = false;

		$bOk = true;
		$APPLICATION->ResetException();
		$db_events = GetModuleEvents("main", "OnBeforeUserLogin");
		while($arEvent = $db_events->Fetch())
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams))===false)
			{
				if($err = $APPLICATION->GetException())
					$result_message = Array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");
				else
				{
					$APPLICATION->ThrowException("Unknown login error");
					$result_message = Array("MESSAGE"=>"Unknown login error"."<br>", "TYPE"=>"ERROR");
				}

				$bOk = false;
				break;
			}
		}

		if($bOk)
		{
			$db_events = GetModuleEvents("main", "OnUserLoginExternal");
			while($arEvent = $db_events->Fetch())
			{
				$user_id = ExecuteModuleEventEx($arEvent, array(&$arParams));
				if($user_id>0)
					break;
			}

			if($user_id<=0)
			{
				$strSql =
					"SELECT U.ID, U.ACTIVE, U.PASSWORD, U.LOGIN_ATTEMPTS ".
					"FROM b_user U  ".
					"WHERE U.LOGIN='".$DB->ForSQL($arParams["LOGIN"])."' ".
					"	AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') ";

				$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
				if($arUser = $result->Fetch())
				{
					if(strlen($arUser["PASSWORD"]) > 32)
					{
						$salt = substr($arUser["PASSWORD"], 0, strlen($arUser["PASSWORD"]) - 32);
						$db_password = substr($arUser["PASSWORD"], -32);
					}
					else
					{
						$salt = "";
						$db_password = $arUser["PASSWORD"];
					}

					if($arParams["PASSWORD_ORIGINAL"] == "Y")
					{
						$user_password =  md5($salt.$arParams["PASSWORD"]);
					}
					else
					{
						if(strlen($arParams["PASSWORD"]) > 32)
							$user_password = substr($arParams["PASSWORD"], -32);
						else
							$user_password = $arParams["PASSWORD"];
					}

					$arPolicy = CUser::GetGroupPolicy($arUser["ID"]);
					$pol_login_attempts = intval($arPolicy["LOGIN_ATTEMPTS"]);
					$usr_login_attempts = intval($arUser["LOGIN_ATTEMPTS"])+1;
					if($pol_login_attempts > 0 && $usr_login_attempts > $pol_login_attempts)
					{
						$_SESSION["BX_LOGIN_NEED_CAPTCHA"] = true;
						if(!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]))
						{
							$user_password = false;
						}
					}

					if($db_password === $user_password)
					{
						if((strlen($salt) <= 0) && ($arParams["PASSWORD_ORIGINAL"] == "Y"))
						{
							$salt = randString(8, array(
								"abcdefghijklnmopqrstuvwxyz",
								"ABCDEFGHIJKLNMOPQRSTUVWXYZ",
								"0123456789",
								",.<>/?;:[]{}\|~!@#\$%^&*()-_+=",
							));
							$new_password = $salt.md5($salt.$arParams["PASSWORD"]);
							$DB->Query("UPDATE b_user SET PASSWORD='".$DB->ForSQL($new_password)."' WHERE ID = ".intval($arUser["ID"]));
						}

						if($arUser["ACTIVE"] == "Y")
						{
							$user_id = $arUser["ID"];
						}
						else
						{
							$APPLICATION->ThrowException(GetMessage("LOGIN_BLOCK"));
							$result_message = Array("MESSAGE"=>GetMessage("LOGIN_BLOCK")."<br>", "TYPE"=>"ERROR");
						}
					}
					else
					{
						$DB->Query("UPDATE b_user SET LOGIN_ATTEMPTS = ".$usr_login_attempts." WHERE ID = ".intval($arUser["ID"]));
						$APPLICATION->ThrowException(GetMessage("WRONG_LOGIN"));
						$result_message = Array("MESSAGE"=>GetMessage("WRONG_LOGIN")."<br>", "TYPE"=>"ERROR");
					}
				}
				else
				{
					$APPLICATION->ThrowException(GetMessage("WRONG_LOGIN"));
					$result_message = Array("MESSAGE"=>GetMessage("WRONG_LOGIN")."<br>", "TYPE"=>"ERROR");
				}
			}
		}

		// All except Admin
		if ($user_id > 1)
		{
			$limitUsersCount = IntVal(COption::GetOptionInt("main", "PARAM_MAX_USERS", 0));
			if ($limitUsersCount > 0)
			{
				$users_cnt = CUser::GetActiveUsersCount();
				if ($users_cnt > $limitUsersCount)
				{
					$strSql =
						"SELECT 'x' ".
						"FROM b_user ".
						"WHERE ACTIVE = 'Y' ".
						"	AND ID = ".IntVal($user_id)." ".
						"	AND LAST_LOGIN IS NULL ";
					$result = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					if ($result->Fetch())
					{
						$user_id = 0;
						$APPLICATION->ThrowException(GetMessage("LIMIT_USERS_COUNT"));
						$result_message = Array("MESSAGE"=>GetMessage("LIMIT_USERS_COUNT")."<br>", "TYPE"=>"ERROR");
					}
				}
			}
		}

		if($user_id>0)
			$this->Authorize($user_id, $arParams["REMEMBER"]=="Y");

		$arParams["USER_ID"] = $user_id;
		$arParams["RESULT_MESSAGE"] = $result_message;

		$APPLICATION->ResetException();
		$events = GetModuleEvents("main", "OnAfterUserLogin");
		while($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$arParams));

		if($result_message !== true && (COption::GetOptionString("main", "event_log_login_fail", "N") === "Y"))
			CEventLog::Log("SECURITY", "USER_LOGIN", "main", $login, $result_message["MESSAGE"]);

		return $arParams["RESULT_MESSAGE"];
	}

	function ChangePassword($LOGIN, $CHECKWORD, $PASSWORD, $CONFIRM_PASSWORD, $SITE_ID=false)
	{
		global $DB, $APPLICATION, $USER;

		$result_message = Array("MESSAGE"=>GetMessage('PASSWORD_CHANGE_OK')."<br>", "TYPE"=>"OK");

		$arParams = Array(
			"LOGIN"			=>	&$LOGIN,
			"CHECKWORD"			=>	&$CHECKWORD,
			"PASSWORD" 		=>	&$PASSWORD,
			"CONFIRM_PASSWORD" =>	&$CONFIRM_PASSWORD,
			"SITE_ID"		=>	&$SITE_ID
			);

		$APPLICATION->ResetException();
		$bOk = true;
		$db_events = GetModuleEvents("main", "OnBeforeUserChangePassword");
		while($arEvent = $db_events->Fetch())
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams))===false)
			{
				if($err = $APPLICATION->GetException())
					$result_message = Array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");

				$bOk = false;
				break;
			}
		}

		if($bOk)
		{
			$strAuthError = "";
			if(strlen($arParams["LOGIN"])<3)
				$strAuthError .= GetMessage('MIN_LOGIN')."<br>";
			if(strlen($arParams["PASSWORD"])<6)
				$strAuthError .= GetMessage('MIN_PASSWORD1')."<br>";
			if($arParams["PASSWORD"]<>$arParams["CONFIRM_PASSWORD"])
				$strAuthError .= GetMessage('WRONG_CONFIRMATION')."<br>";

			if(strlen($strAuthError)>0)
				return Array("MESSAGE"=>$strAuthError, "TYPE"=>"ERROR");

			$db_check = $DB->Query(
				"SELECT ID, LID, CHECKWORD, ".$DB->DateToCharFunction("CHECKWORD_TIME", "FULL")." as CHECKWORD_TIME ".
				"FROM b_user ".
				"WHERE LOGIN='".$DB->ForSql($arParams["LOGIN"], 0)."' AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='')");
			if(!($res = $db_check->Fetch()))
				return Array("MESSAGE"=>preg_replace("/#LOGIN#/i", htmlspecialchars($arParams["LOGIN"]), GetMessage('LOGIN_NOT_FOUND')), "TYPE"=>"ERROR");

			$salt = substr($res["CHECKWORD"], 0, 8);
			if(strlen($res["CHECKWORD"])<=0 || $res["CHECKWORD"] != $salt.md5($salt.$arParams["CHECKWORD"]))
				return Array("MESSAGE"=>preg_replace("/#LOGIN#/i", htmlspecialchars($arParams["LOGIN"]), GetMessage("CHECKWORD_INCORRECT"))."<br>", "TYPE"=>"ERROR");

			$arPolicy = CUser::GetGroupPolicy($res["ID"]);
			$site_format = CSite::GetDateFormat();
			if(mktime()-$arPolicy["CHECKWORD_TIMEOUT"]*60 > MakeTimeStamp($res["CHECKWORD_TIME"], $site_format))
				return Array("MESSAGE"=>preg_replace("/#LOGIN#/i", htmlspecialchars($arParams["LOGIN"]), GetMessage("CHECKWORD_EXPIRE"))."<br>", "TYPE"=>"ERROR");

			if($arParams["SITE_ID"] === false)
			{
				if(defined("ADMIN_SECTION") && ADMIN_SECTION===true)
					$arParams["SITE_ID"] = CSite::GetDefSite($res["LID"]);
				else
					$arParams["SITE_ID"] = SITE_ID;
			}

			// меняем пароль
			$ID = $res["ID"];
			$obUser = new CUser;
			$res = $obUser->Update($ID, Array("PASSWORD"=>$arParams["PASSWORD"]));
			if(!$res && (strlen($obUser->LAST_ERROR) > 0))
				return Array("MESSAGE"=>$obUser->LAST_ERROR."<br>", "TYPE"=>"ERROR");
			CUser::SendUserInfo($ID, $arParams["SITE_ID"], GetMessage('CHANGE_PASS_SUCC'), true, 'USER_PASS_CHANGED');
		}

		return $result_message;
	}

	//функция отсылающая регистрационную информацию пользователю на электронную почту
	function SendUserInfo($ID, $SITE_ID, $MSG, $bImmediate=false, $eventName="USER_INFO")
	{
		global $DB, $APPLICATION, $USER;

		// меняем пароль
		$ID = IntVal($ID);
		$salt = randString(8);
		$checkword = randString(8);
		$strSql =	"UPDATE b_user SET ".
					"	CHECKWORD = '".$salt.md5($salt.$checkword)."', ".
					"	CHECKWORD_TIME = ".$DB->CurrentTimeFunction().", ".
					"	LID = '".$DB->ForSql($SITE_ID, 2)."' ".
					"WHERE ID = '".$ID."'".
					"	AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') ";

		$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$res = $DB->Query(
				"SELECT u.* ".
				"FROM b_user u ".
				"WHERE ID='".$ID."'".
				"	AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') "
			);

		if($res_array = $res->Fetch())
		{
			$event = new CEvent;
			$arFields = Array(
				"USER_ID"=>$res_array["ID"],
				"STATUS"=>($res_array["ACTIVE"]=="Y"?GetMessage("STATUS_ACTIVE"):GetMessage("STATUS_BLOCKED")),
				"MESSAGE"=>$MSG,
				"LOGIN"=>$res_array["LOGIN"],
				"CHECKWORD"=>$checkword,
				"NAME"=>$res_array["NAME"],
				"LAST_NAME"=>$res_array["LAST_NAME"],
				"EMAIL"=>$res_array["EMAIL"]
				);


			$arParams = Array(
				"FIELDS" => &$arFields,
				"USER_FIELDS" => $res_array,
				"SITE_ID" => &$SITE_ID,
				"EVENT_NAME" => &$eventName,
			);

			$events = GetModuleEvents("main", "OnSendUserInfo");
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array(&$arParams));

			if (!$bImmediate)
				$event->Send($eventName, $SITE_ID, $arFields);
			else
				$event->SendImmediate($eventName, $SITE_ID, $arFields);
		}
	}

	function SendPassword($LOGIN, $EMAIL, $SITE_ID = false)
	{
		global $DB, $APPLICATION, $USER;

		$arParams = Array(
			"LOGIN"			=>	$LOGIN,
			"EMAIL"			=>	$EMAIL,
			"SITE_ID"		=>	$SITE_ID
			);

		$result_message = Array("MESSAGE"=>GetMessage('ACCOUNT_INFO_SENT')."<br>", "TYPE"=>"OK");
		$APPLICATION->ResetException();
		$bOk = true;
		$db_events = GetModuleEvents("main", "OnBeforeUserSendPassword");
		while($arEvent = $db_events->Fetch())
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams))===false)
			{
				if($err = $APPLICATION->GetException())
					$result_message = Array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");

				$bOk = false;
				break;
			}
		}

		if($bOk)
		{
			$f = false;
			$sFilter = "";
			if($arParams["LOGIN"] <> '')
				$sFilter .= "LOGIN='".$DB->ForSQL($arParams["LOGIN"])."'";
			if($arParams["EMAIL"] <> '')
				$sFilter .= ($sFilter <> ''? ' OR ':'')."EMAIL='".$DB->ForSQL($arParams["EMAIL"])."'";

			if($sFilter <> '')
			{
				$strSql =
					"SELECT ID, LID ".
					"FROM b_user u ".
					"WHERE (".$sFilter.") ".
					"	AND ACTIVE='Y' ".
					"	AND (EXTERNAL_AUTH_ID IS NULL OR EXTERNAL_AUTH_ID='') ";
				$res = $DB->Query($strSql);

				while($arUser = $res->Fetch())
				{
					if($arParams["SITE_ID"]===false)
					{
						if(defined("ADMIN_SECTION") && ADMIN_SECTION===true)
							$arParams["SITE_ID"] = CSite::GetDefSite($arUser["LID"]);
						else
							$arParams["SITE_ID"] = SITE_ID;
					}

					// отсылаем данные
					$f = true;
					CUser::SendUserInfo($arUser["ID"], $arParams["SITE_ID"], GetMessage("INFO_REQ"), true, 'USER_PASS_REQUEST');

					if(COption::GetOptionString("main", "event_log_password_request", "N") === "Y")
						CEventLog::Log("SECURITY", "USER_INFO", "main", $arUser["ID"]);
				}
			}
			if(!$f)
				return Array("MESSAGE"=>GetMessage('DATA_NOT_FOUND')."<br>", "TYPE"=>"ERROR");
		}
		return $result_message;
	}

	function Register($USER_LOGIN, $USER_NAME, $USER_LAST_NAME, $USER_PASSWORD, $USER_CONFIRM_PASSWORD, $USER_EMAIL, $SITE_ID = false, $captcha_word = "", $captcha_sid = 0)
	{
		global $APPLICATION, $DB;
		$APPLICATION->ResetException();
		if(defined("ADMIN_SECTION") && ADMIN_SECTION===true && $SITE_ID!==false)
		{
			$APPLICATION->ThrowException(GetMessage("MAIN_FUNCTION_REGISTER_NA_INADMIN"));
			return Array("MESSAGE"=>GetMessage("MAIN_FUNCTION_REGISTER_NA_INADMIN"), "TYPE"=>"ERROR");
		}

		$strError = "";

		if (COption::GetOptionString("main", "captcha_registration", "N") == "Y")
		{
			if (!($GLOBALS["APPLICATION"]->CaptchaCheckCode($captcha_word, $captcha_sid)))
			{
				$strError .= GetMessage("MAIN_FUNCTION_REGISTER_CAPTCHA")."<br>";
			}
		}

		if($strError)
		{
			if(COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
			{
				CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", false, $strError);
			}

			$APPLICATION->ThrowException($strError);
			return Array("MESSAGE"=>$strError, "TYPE"=>"ERROR");
		}

		if($SITE_ID===false)
			$SITE_ID = SITE_ID;

		global $REMOTE_ADDR;
		$checkword = randString(8);
		$bConfirmReq = COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y";
		$arFields = Array(
				"LOGIN" => $USER_LOGIN,
				"NAME" => $USER_NAME,
				"LAST_NAME" => $USER_LAST_NAME,
				"PASSWORD" => $USER_PASSWORD,
				"CHECKWORD" => $checkword,
				"~CHECKWORD_TIME" => $DB->CurrentTimeFunction(),
				"CONFIRM_PASSWORD" => $USER_CONFIRM_PASSWORD,
				"EMAIL" => $USER_EMAIL,
				"ACTIVE" => $bConfirmReq? "N": "Y",
				"CONFIRM_CODE" => $bConfirmReq? randString(8): "",
				"SITE_ID" => $SITE_ID,
				"USER_IP" => $_SERVER["REMOTE_ADDR"],
				"USER_HOST" => @gethostbyaddr($REMOTE_ADDR)
			);
		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("USER", $arFields);

		$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
		if($def_group!="")
			$arFields["GROUP_ID"] = explode(",", $def_group);

		$bOk = true;
		$result_message = true;
		$events = GetModuleEvents("main", "OnBeforeUserRegister");
		while($arEvent = $events->Fetch())
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				if($err = $APPLICATION->GetException())
					$result_message = Array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$result_message = Array("MESSAGE"=>"Unknown error"."<br>", "TYPE"=>"ERROR");
				}

				$bOk = false;
				break;
			}
		}

		if($bOk)
		{
			$arFields["LID"] = $arFields["SITE_ID"];
			if($ID = $this->Add($arFields))
			{
				if($arFields["ACTIVE"] === "Y")
					$this->Authorize($ID);
				$arFields["USER_ID"] = $ID;

				$arEventFields = $arFields;
				unset($arEventFields["PASSWORD"]);
				unset($arEventFields["CONFIRM_PASSWORD"]);
				unset($arEventFields["~CHECKWORD_TIME"]);

				$event = new CEvent;
				$event->SendImmediate("NEW_USER", $arEventFields["SITE_ID"], $arEventFields);
				if($bConfirmReq)
					$event->SendImmediate("NEW_USER_CONFIRM", $arEventFields["SITE_ID"], $arEventFields);
				$result_message = Array("MESSAGE"=>GetMessage("USER_REGISTER_OK"), "TYPE"=>"OK");
			}
			else
			{
				$APPLICATION->ThrowException($this->LAST_ERROR);
				$result_message = Array("MESSAGE"=>$this->LAST_ERROR, "TYPE"=>"ERROR");
			}
		}

		if(is_array($result_message))
		{
			if($result_message["TYPE"] == "OK")
			{
				if(COption::GetOptionString("main", "event_log_register", "N") === "Y")
					CEventLog::Log("SECURITY", "USER_REGISTER", "main", $ID);
			}
			else
			{
				if(COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
				{
					CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", $ID, $result_message["MESSAGE"]);
				}
			}
		}

		$arFields["RESULT_MESSAGE"] = $result_message;
		$events = GetModuleEvents("main", "OnAfterUserRegister");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		return $arFields["RESULT_MESSAGE"];
	}

	function SimpleRegister($USER_EMAIL, $SITE_ID = false, $captcha_word = "", $captcha_sid = 0)
	{
		global $APPLICATION, $DB;
		$APPLICATION->ResetException();
		if(defined("ADMIN_SECTION") && ADMIN_SECTION===true && $SITE_ID===false)
		{
			$APPLICATION->ThrowException(GetMessage("MAIN_FUNCTION_SIMPLEREGISTER_NA_INADMIN"));
			return Array("MESSAGE"=>GetMessage("MAIN_FUNCTION_SIMPLEREGISTER_NA_INADMIN"), "TYPE"=>"ERROR");
		}

		if($SITE_ID===false)
			$SITE_ID = SITE_ID;

		global $REMOTE_ADDR;

		$checkword = randString(8);
		$arFields = Array(
			"CHECKWORD" => $checkword,
			"~CHECKWORD_TIME" => $DB->CurrentTimeFunction(),
			"EMAIL" => $USER_EMAIL,
			"ACTIVE" => "Y",
			"NAME"=>"",
			"LAST_NAME"=>"",
			"USER_IP"=>$REMOTE_ADDR,
			"USER_HOST"=>@gethostbyaddr($REMOTE_ADDR),
			"SITE_ID" => $SITE_ID
			);

		$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
		if($def_group!="")
		{
			$arFields["GROUP_ID"] = explode(",", $def_group);
			$arPolicy = $this->GetGroupPolicy($arFields["GROUP_ID"]);
		}
		else
		{
			$arPolicy = $this->GetGroupPolicy(array());
		}
		$password_min_length = intval($arPolicy["PASSWORD_LENGTH"]);
		if($password_min_length <= 0)
			$password_min_length = 6;
		$password_chars = array(
			"abcdefghijklnmopqrstuvwxyz",
			"ABCDEFGHIJKLNMOPQRSTUVWXYZ",
			"0123456789",
		);
		if($arPolicy["PASSWORD_PUNCTUATION"] === "Y")
			$password_chars[] = ",.<>/?;:'\"[]{}\|`~!@#\$%^&*()-_+=";
		$arFields["PASSWORD"] = $arFields["CONFIRM_PASSWORD"] = randString($password_min_length, $password_chars);

		$bOk = true;
		$result_message = false;
		$events = GetModuleEvents("main", "OnBeforeUserSimpleRegister");
		while($arEvent = $events->Fetch())
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				if($err = $APPLICATION->GetException())
					$result_message = Array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$result_message = Array("MESSAGE"=>"Unknown error"."<br>", "TYPE"=>"ERROR");
				}

				$bOk = false;
				break;
			}
		}

		$bRandLogin = true;
		if(!is_set($arFields, "LOGIN"))
			$arFields["LOGIN"] = randString(50);
		else
			$bRandLogin = false;

		if($bOk)
		{
			$arFields["LID"] = $arFields["SITE_ID"];
			$arFields["CHECKWORD"] = $checkword;
			if($ID = $this->Add($arFields))
			{
				$this->Update($ID, array("LOGIN"=>"user".$ID));
				$this->Authorize($ID);

				if($bRandLogin);
					$arFields["LOGIN"]= "user".$ID;

				$event = new CEvent;
				$arFields["USER_ID"] = $ID;

				$arEventFields = $arFields;
				unset($arEventFields["PASSWORD"]);
				unset($arEventFields["CONFIRM_PASSWORD"]);

				$event->SendImmediate("NEW_USER", $arEventFields["SITE_ID"], $arEventFields);
				CUser::SendUserInfo($ID, $arEventFields["SITE_ID"], GetMessage("USER_REGISTERED_SIMPLE"), true);
				$result_message = Array("MESSAGE"=>GetMessage("USER_REGISTER_OK"), "TYPE"=>"OK");
			}
			else
				$result_message = Array("MESSAGE"=>$this->LAST_ERROR, "TYPE"=>"ERROR");
		}

		if(is_array($result_message))
		{
			if($result_message["TYPE"] == "OK")
			{
				if(COption::GetOptionString("main", "event_log_register", "N") === "Y")
					CEventLog::Log("SECURITY", "USER_REGISTER", "main", $ID);
			}
			else
			{
				if(COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
				{
					CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", $ID, $result_message["MESSAGE"]);
				}
			}
		}

		$arEventFields = $arFields;
		$arEventFields["RESULT_MESSAGE"] = $result_message;
		$events = GetModuleEvents("main", "OnAfterUserSimpleRegister");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$arEventFields));

		return $result_message;
	}

	function IsAuthorized()
	{
		return ($_SESSION["SESS_AUTH"]["AUTHORIZED"]=="Y");
	}

	function IsAdmin()
	{
		if(COption::GetOptionString("main", "controller_member", "N") == "Y" && COption::GetOptionString("main", "~controller_limited_admin", "N") == "Y")
			return ($_SESSION["SESS_AUTH"]["CONTROLLER_ADMIN"] === true);

		return $_SESSION["SESS_AUTH"]["ADMIN"];
	}

	function SetControllerAdmin($isAdmin=true)
	{
		$_SESSION["SESS_AUTH"]["CONTROLLER_ADMIN"] = $isAdmin;
	}

	function Logout()
	{
		global $APPLICATION, $DB;
		$USER_ID = $_SESSION["SESS_AUTH"]["USER_ID"];

		$arParams = Array(
			"USER_ID" => &$USER_ID
			);

		$APPLICATION->ResetException();
		$bOk = true;
		$db_events = GetModuleEvents("main", "OnBeforeUserLogout");
		while($arEvent = $db_events->Fetch())
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arParams))===false)
			{
				if($err = $APPLICATION->GetException())
					$result_message = Array("MESSAGE"=>$err->GetString()."<br>", "TYPE"=>"ERROR");
				else
				{
					$APPLICATION->ThrowException("Unknown logout error");
					$result_message = Array("MESSAGE"=>"Unknown error"."<br>", "TYPE"=>"ERROR");
				}

				$bOk = false;
				break;
			}
		}

		if($bOk)
		{
			$events = GetModuleEvents("main", "OnUserLogout");
			while($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array($USER_ID));

			if($_SESSION["SESS_AUTH"]["STORED_AUTH_ID"]>0)
				$DB->Query("DELETE FROM b_user_stored_auth WHERE ID=".IntVal($_SESSION["SESS_AUTH"]["STORED_AUTH_ID"]));

			$_SESSION["SESS_AUTH"] = Array();
			unset($_SESSION["SESS_AUTH"]);
			unset($_SESSION["OPERATIONS"]);
			unset($_SESSION["SESS_PWD_HASH_TESTED"]);

			$APPLICATION->set_cookie("UIDH", "", 0, '/', false, false, COption::GetOptionString("main", "auth_multisite", "N")=="Y");
		}

		$arParams["SUCCESS"] = $bOk;
		$events = GetModuleEvents("main", "OnAfterUserLogout");
		while($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$arParams));

		if(COption::GetOptionString("main", "event_log_logout", "N") === "Y")
			CEventLog::Log("SECURITY", "USER_LOGOUT", "main", $USER_ID);
	}

	function GetUserGroup($ID)
	{
		global $DB;

		$strSql =
			"SELECT UG.GROUP_ID ".
			"FROM b_user_group UG ".
			"WHERE UG.USER_ID = ".IntVal($ID)." ".
			"	AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) ".
			"	AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")) ";

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		while($r = $res->Fetch())
			$arr[] = $r["GROUP_ID"];

		if (!in_array(2, $arr))
			$arr[] = 2;

		return $arr;
	}

	function GetUserGroupEx($ID)
	{
		global $DB;

		$strSql = "
			SELECT UG.GROUP_ID, G.STRING_ID,
				".$DB->DateToCharFunction("UG.DATE_ACTIVE_FROM", "FULL")." as DATE_ACTIVE_FROM,
				".$DB->DateToCharFunction("UG.DATE_ACTIVE_TO", "FULL")." as DATE_ACTIVE_TO
			FROM b_user_group UG INNER JOIN b_group G ON G.ID=UG.GROUP_ID
			WHERE UG.USER_ID = ".IntVal($ID)."
			and ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction()."))
			and ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction()."))
			UNION SELECT 2, 'everyone', NULL, NULL ".(strtoupper($DB->type) == "ORACLE"? " FROM dual " : "");
		//echo "<pre>".$strSql."</pre>";
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	function GetUserGroupList($ID)
	{
		global $DB;

		$strSql = "
			SELECT
				UG.GROUP_ID,
				".$DB->DateToCharFunction("UG.DATE_ACTIVE_FROM", "FULL")." as DATE_ACTIVE_FROM,
				".$DB->DateToCharFunction("UG.DATE_ACTIVE_TO", "FULL")." as DATE_ACTIVE_TO
			FROM
				b_user_group UG
			WHERE
				UG.USER_ID = ".IntVal($ID)."
			UNION SELECT 2, NULL, NULL ".(strtoupper($DB->type) == "ORACLE"? " FROM dual " : "");
		//echo "<pre>".$strSql."</pre>";
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	/*Обработка вставки, редактирования, удаления*/
	function CheckFields(&$arFields, $ID=false)
	{
		global $DB, $APPLICATION, $USER;
		$this->LAST_ERROR = "";

		$bInternal = false;
		if($ID>0 && (!is_set($arFields, "EXTERNAL_AUTH_ID")))
		{
			$strSql = "SELECT EXTERNAL_AUTH_ID FROM b_user WHERE ID=".IntVal($ID);
			$dbr = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			if(($ar = $dbr->Fetch()) && strlen($ar['EXTERNAL_AUTH_ID'])<=0)
				$bInternal = true;

		}
		elseif(!is_set($arFields, "EXTERNAL_AUTH_ID") || strlen(trim($arFields["EXTERNAL_AUTH_ID"]))<=0)
			$bInternal = true;


		if($bInternal)
		{
			if($ID === false)
			{
				if(!isset($arFields["LOGIN"]))
					$this->LAST_ERROR .= GetMessage("user_login_not_set")."<br>";

				if(!isset($arFields["PASSWORD"]))
					$this->LAST_ERROR .= GetMessage("user_pass_not_set")."<br>";

				if(!isset($arFields["EMAIL"]))
					$this->LAST_ERROR .= GetMessage("user_email_not_set")."<br>";
			}
			if(is_set($arFields, "LOGIN") && $arFields["LOGIN"]!=Trim($arFields["LOGIN"]))
				$this->LAST_ERROR .= GetMessage("LOGIN_WHITESPACE")."<br>";

			if(is_set($arFields, "LOGIN") && strlen($arFields["LOGIN"])<3)
				$this->LAST_ERROR .= GetMessage("MIN_LOGIN")."<br>";

			if(is_set($arFields, "PASSWORD"))
			{
				if(array_key_exists("GROUP_ID", $arFields))
				{
					$arGroups = array();
					foreach($arFields["GROUP_ID"] as $arGroup)
					{
						if(is_array($arGroup))
							$arGroups[] = $arGroup["GROUP_ID"];
						else
							$arGroups[] = $arGroup;
					}
					$arPolicy = $this->GetGroupPolicy($arGroups);
				}
				elseif($ID !== false)
				{
					$arPolicy = $this->GetGroupPolicy($ID);
				}
				else
				{
					$arPolicy = $this->GetGroupPolicy(array());
				}

				$password_min_length = intval($arPolicy["PASSWORD_LENGTH"]);
				if($password_min_length <= 0)
					$password_min_length = 6;
				if(strlen($arFields["PASSWORD"]) < $password_min_length)
					$this->LAST_ERROR .= GetMessage("MAIN_FUNCTION_REGISTER_PASSWORD_LENGTH", array("#LENGTH#" => $arPolicy["PASSWORD_LENGTH"]))."<br>";

				if(($arPolicy["PASSWORD_UPPERCASE"] === "Y") && !preg_match("/[A-Z]/", $arFields["PASSWORD"]))
					$this->LAST_ERROR .= GetMessage("MAIN_FUNCTION_REGISTER_PASSWORD_UPPERCASE")."<br>";

				if(($arPolicy["PASSWORD_LOWERCASE"] === "Y") && !preg_match("/[a-z]/", $arFields["PASSWORD"]))
					$this->LAST_ERROR .= GetMessage("MAIN_FUNCTION_REGISTER_PASSWORD_LOWERCASE")."<br>";

				if(($arPolicy["PASSWORD_DIGITS"] === "Y") && !preg_match("/[0-9]/", $arFields["PASSWORD"]))
					$this->LAST_ERROR .= GetMessage("MAIN_FUNCTION_REGISTER_PASSWORD_DIGITS")."<br>";

				if(($arPolicy["PASSWORD_PUNCTUATION"] === "Y") && !preg_match("/[,.<>\\/?;:'\"[\\]\{\}\\\\|`~!@#\$%^&*()_+=-]/", $arFields["PASSWORD"]))
					$this->LAST_ERROR .= GetMessage("MAIN_FUNCTION_REGISTER_PASSWORD_PUNCTUATION")."<br>";
			}

			if(is_set($arFields, "EMAIL"))
			{
				if(strlen($arFields["EMAIL"])<3 || !check_email($arFields["EMAIL"]))
					$this->LAST_ERROR .= GetMessage("WRONG_EMAIL")."<br>";
				elseif(($ID === false) && COption::GetOptionString("main", "new_user_email_uniq_check", "N") === "Y")
				{
					$res = CUser::GetList($b, $o, array("=EMAIL" => $arFields["EMAIL"]));
					if($res->Fetch())
						$this->LAST_ERROR .= GetMessage("USER_WITH_EMAIL_EXIST", array("#EMAIL#" => htmlspecialchars($arFields["EMAIL"])))."<br>";
				}
			}

			if(is_set($arFields, "PASSWORD") && is_set($arFields, "CONFIRM_PASSWORD") && $arFields["PASSWORD"]!=$arFields["CONFIRM_PASSWORD"])
				$this->LAST_ERROR .= GetMessage("WRONG_CONFIRMATION")."<br>";

			if (is_array($arFields["GROUP_ID"]) && count($arFields["GROUP_ID"]) > 0)
			{
				if (is_array($arFields["GROUP_ID"][0]) && count($arFields["GROUP_ID"][0]) > 0)
				{
					foreach($arFields["GROUP_ID"] as $arGroup)
					{
						if(strlen($arGroup["DATE_ACTIVE_FROM"])>0 && !CheckDateTime($arGroup["DATE_ACTIVE_FROM"]))
						{
							$error = str_replace("#GROUP_ID#", $arGroup["GROUP_ID"], GetMessage("WRONG_DATE_ACTIVE_FROM"));
							$this->LAST_ERROR .= $error."<br>";
						}

						if(strlen($arGroup["DATE_ACTIVE_TO"])>0 && !CheckDateTime($arGroup["DATE_ACTIVE_TO"]))
						{
							$error = str_replace("#GROUP_ID#", $arGroup["GROUP_ID"], GetMessage("WRONG_DATE_ACTIVE_TO"));
							$this->LAST_ERROR .= $error."<br>";
						}
					}
				}
			}
		}

		if(is_set($arFields, "PERSONAL_PHOTO") && strlen($arFields["PERSONAL_PHOTO"]["name"])<=0 && strlen($arFields["PERSONAL_PHOTO"]["del"])<=0)
			unset($arFields["PERSONAL_PHOTO"]);

		if(is_set($arFields, "PERSONAL_PHOTO"))
		{
			$res = CFile::CheckImageFile($arFields["PERSONAL_PHOTO"]);
			if(strlen($res)>0)
				$this->LAST_ERROR .= $res."<br>";
		}

		if(is_set($arFields, "PERSONAL_BIRTHDAY") && strlen($arFields["PERSONAL_BIRTHDAY"])>0 && !CheckDateTime($arFields["PERSONAL_BIRTHDAY"]))
			$this->LAST_ERROR .= GetMessage("WRONG_PERSONAL_BIRTHDAY")."<br>";

		if(is_set($arFields, "WORK_LOGO") && strlen($arFields["WORK_LOGO"]["name"])<=0 && strlen($arFields["WORK_LOGO"]["del"])<=0)
			unset($arFields["WORK_LOGO"]);

		if(is_set($arFields, "WORK_LOGO"))
		{
			$res = CFile::CheckImageFile($arFields["WORK_LOGO"]);
			if(strlen($res)>0)
				$this->LAST_ERROR .= $res."<br>";
		}

		if(is_set($arFields, "LOGIN"))
		{
			$res = $DB->Query(
				"SELECT 'x' ".
				"FROM b_user ".
				"WHERE LOGIN='".$DB->ForSql($arFields["LOGIN"], 50)."'	".
				"	".($ID===false ? "" : " AND ID<>".IntVal($ID)).
				"	".(!$bInternal ? "	AND EXTERNAL_AUTH_ID='".$DB->ForSql($arFields["EXTERNAL_AUTH_ID"])."' " : " AND (EXTERNAL_AUTH_ID IS NULL OR ".$DB->Length("EXTERNAL_AUTH_ID")."<=0)")
				);

			if($res->Fetch())
				$this->LAST_ERROR .= str_replace("#LOGIN#", htmlspecialchars($arFields["LOGIN"]), GetMessage("USER_EXIST"))."<br>";
		}

		if(is_object($APPLICATION))
		{
			$APPLICATION->ResetException();

			if($ID===false)
				$db_events = GetModuleEvents("main", "OnBeforeUserAdd");
			else
			{
				$arFields["ID"] = $ID;
				$db_events = GetModuleEvents("main", "OnBeforeUserUpdate");
			}


			while($arEvent = $db_events->Fetch())
			{
				$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
				if($bEventRes===false)
				{
					if($err = $APPLICATION->GetException())
						$this->LAST_ERROR .= $err->GetString()." ";
					else
					{
						$APPLICATION->ThrowException("Unknown error");
						$this->LAST_ERROR .= "Unknown error. ";
					}
					break;
				}
			}
		}

		if(is_object($APPLICATION))
			$APPLICATION->ResetException();
		if (!$GLOBALS["USER_FIELD_MANAGER"]->CheckFields("USER", $ID, $arFields))
		{
			if(is_object($APPLICATION) && $APPLICATION->GetException())
			{
				$e = $APPLICATION->GetException();
				$this->LAST_ERROR .= $e->GetString();
				$APPLICATION->ResetException();
			}
			else
			{
				$this->LAST_ERROR .= "Unknown error. ";
			}
		}

		if(strlen($this->LAST_ERROR)>0)
			return false;

		return true;
	}

	///////////////////////////////////////////////////////////////////
	//Функция выборки одного пользователя по коду
	///////////////////////////////////////////////////////////////////
	function GetByID($ID)
	{
		global $DB;
		$rs = CUser::GetList(($by="id"), ($order="asc"), array("ID_EQUAL_EXACT"=>IntVal($ID)), array("SELECT"=>array("UF_*")));
		return $rs;
	}

	function GetByLogin($LOGIN)
	{
		global $DB;
		$rs = CUser::GetList(($by="id"), ($order="asc"), array("LOGIN_EQUAL_EXACT"=>$LOGIN), array("SELECT"=>array("UF_*")));
		return $rs;
	}

	function Update($ID, $arFields)
	{
		global $DB, $USER_FIELD_MANAGER;

		$ID = intval($ID);

		if(!$this->CheckFields($arFields, $ID))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			unset($arFields["ID"]);

			if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
				$arFields["ACTIVE"]="N";

			if(is_set($arFields, "PERSONAL_GENDER") && ($arFields["PERSONAL_GENDER"]!="M" && $arFields["PERSONAL_GENDER"]!="F"))
				$arFields["PERSONAL_GENDER"] = "";

			if(is_set($arFields, "PASSWORD"))
			{
				$salt = randString(8, array(
					"abcdefghijklnmopqrstuvwxyz",
					"ABCDEFGHIJKLNMOPQRSTUVWXYZ",
					"0123456789",
					",.<>/?;:[]{}\|~!@#\$%^&*()-_+=",
				));
				$arFields["PASSWORD"] = $salt.md5($salt.$arFields["PASSWORD"]);
				$rUser = CUser::GetByID($ID);
				if($arUser = $rUser->Fetch())
				{
					if($arUser["PASSWORD"]!=$arFields["PASSWORD"])
						$DB->Query("DELETE FROM b_user_stored_auth WHERE USER_ID=".$ID);
				}
				if(COption::GetOptionString("main", "event_log_password_change", "N") === "Y")
					CEventLog::Log("SECURITY", "USER_PASSWORD_CHANGED", "main", $ID);
				//$arFields["STORED_HASH"] = CUser::GetPasswordHash($arFields["PASSWORD"]);
			}
			unset($arFields["STORED_HASH"]);

			$checkword = '';
			if(!is_set($arFields, "CHECKWORD"))
			{
				if(is_set($arFields, "PASSWORD") || is_set($arFields, "EMAIL") || is_set($arFields, "LOGIN")  || is_set($arFields, "ACTIVE"))
				{
					$salt =  randString(8);
					$checkword = randString(8);
					$arFields["CHECKWORD"] = $salt.md5($salt.$checkword);
				}
			}
			else
			{
				$salt =  randString(8);
				$checkword = $arFields["CHECKWORD"];
				$arFields["CHECKWORD"] = $salt.md5($salt.$checkword);
			}

			if(is_set($arFields, "CHECKWORD") && !is_set($arFields, "CHECKWORD_TIME"))
				$arFields["~CHECKWORD_TIME"] = $DB->CurrentTimeFunction();

			if(is_set($arFields, "WORK_COUNTRY"))
				$arFields["WORK_COUNTRY"] = IntVal($arFields["WORK_COUNTRY"]);

			if(is_set($arFields, "PERSONAL_COUNTRY"))
				$arFields["PERSONAL_COUNTRY"] = IntVal($arFields["PERSONAL_COUNTRY"]);

			CFile::SaveForDB($arFields, "PERSONAL_PHOTO", "main");
			CFile::SaveForDB($arFields, "WORK_LOGO", "main");

			$strUpdate = $DB->PrepareUpdate("b_user", $arFields);

			if(!is_set($arFields, "TIMESTAMP_X"))
				$strUpdate .= ($strUpdate <> ""? ",":"")." TIMESTAMP_X = ".$DB->GetNowFunction();

			$strSql = "UPDATE b_user SET ".$strUpdate." WHERE ID=".$ID;
			//echo "<pre>".$strSql."</pre>";
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			$USER_FIELD_MANAGER->Update("USER", $ID, $arFields);

			if(is_set($arFields, "GROUP_ID"))
				CUser::SetUserGroup($ID, $arFields["GROUP_ID"]);

			$Result = true;
			$arFields["CHECKWORD"] = $checkword;
		}

		$arFields["ID"] = $ID;
		$arFields["RESULT"] = &$Result;

		$events = GetModuleEvents("main", "OnAfterUserUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		if(defined("BX_COMP_MANAGED_CACHE"))
			$GLOBALS["CACHE_MANAGER"]->ClearByTag("USER_CARD_".intval($ID / 100));

		return $Result;
	}

	function SetUserGroup($USER_ID, $arGroups)
	{
		global $DB, $APPLICATION, $USER;
		$USER_ID = IntVal($USER_ID);

		if(COption::GetOptionString("main", "event_log_user_groups", "N") === "Y")
		{
			//remember previous groups of the user
			$aPrevGroups = array();
			$res = CUser::GetUserGroupList($USER_ID);
			while($res_arr = $res->Fetch())
				if($res_arr["GROUP_ID"] <> 2)
					$aPrevGroups[] = $res_arr["GROUP_ID"];
		}

		$DB->Query("DELETE FROM b_user_group WHERE USER_ID=".$USER_ID, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (is_array($arGroups) && count($arGroups) > 0)
		{
			if (is_array($arGroups[0]) && count($arGroups[0]) > 0)
			{
				$arTmp = array();
				for ($i = 0; $i < count($arGroups); $i++)
				{
					if (IntVal($arGroups[$i]["GROUP_ID"]) > 0
						&& !in_array(IntVal($arGroups[$i]["GROUP_ID"]), $arTmp))
					{
						$arInsert = $DB->PrepareInsert("b_user_group", $arGroups[$i]);
						$strSql = "
							INSERT INTO b_user_group (
								USER_ID, ".$arInsert[0]."
							) VALUES (
								".$USER_ID.",
								".$arInsert[1]."
							)
							";
						//echo "<pre>".$strSql."</pre>";
						$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

						$arTmp[] = IntVal($arGroups[$i]["GROUP_ID"]);
					}
				}
				$arGroups = $arTmp;
			}
			else
			{
				$strGroups = "0";

				array_walk($arGroups, create_function("&\$item", "\$item=intval(\$item);"));
				$arGroups = array_unique($arGroups);
				if (count($arGroups)>0)
					$strGroups = implode(",", $arGroups);

				$strSql =
					"INSERT INTO b_user_group(USER_ID, GROUP_ID) ".
					"SELECT ".$USER_ID.", ID ".
					"FROM b_group ".
					"WHERE ID in (".$strGroups.")";

				$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			}
		}
		else
			$arGroups = array();

		if(COption::GetOptionString("main", "event_log_user_groups", "N") === "Y")
		{
			//compare previous groups of the user with new
			$aDiff = array_diff($aPrevGroups, $arGroups);
			if(empty($aDiff))
				$aDiff = array_diff($arGroups, $aPrevGroups);
			if(!empty($aDiff))
			{
				sort($aPrevGroups);
				sort($arGroups);
				CEventLog::Log("SECURITY", "USER_GROUP_CHANGED", "main", $USER_ID, "(".implode(", ", $aPrevGroups).") => (".implode(", ", $arGroups).")");
			}
		}
	}

	function GetCount()
	{
		global $DB;
		$r = $DB->Query("SELECT COUNT('x') as C FROM b_user");
		$r = $r->Fetch();
		return Intval($r["C"]);
	}

	function Delete($ID)
	{
		global $DB, $APPLICATION;
		$ID = intval($ID);

		@set_time_limit(600);

		$rsUser = $DB->Query("SELECT ID, LOGIN FROM b_user WHERE ID=".$ID." AND ID<>1");
		$arUser = $rsUser->Fetch();
		if(!$arUser)
			return false;

		$db_events = GetModuleEvents("main", "OnBeforeUserDelete");
		while($arEvent = $db_events->Fetch())
			if(ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				if(COption::GetOptionString("main", "event_log_user_delete", "N") === "Y")
					CEventLog::Log("SECURITY", "USER_DELETE", "main", $ID, $err);
				return false;
			}

		//проверка - оставил ли тут какой-нибудь модуль обработчик на OnDelete
		$events = GetModuleEvents("main", "OnUserDelete");
		while($arEvent = $events->Fetch())
			if(ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				if(COption::GetOptionString("main", "event_log_user_delete", "N") === "Y")
					CEventLog::Log("SECURITY", "USER_DELETE", "main", $ID, $err);
				return false;
			}

		$strSql = "SELECT F.ID FROM	b_user U, b_file F WHERE U.ID='$ID' and (F.ID=U.PERSONAL_PHOTO or F.ID=U.WORK_LOGO)";
		$z = $DB->Query($strSql, false, "FILE: ".__FILE__." LINE:".__LINE__);
		while ($zr = $z->Fetch())
			CFile::Delete($zr["ID"]);

		if(!$DB->Query("DELETE FROM b_user_group WHERE USER_ID=".$ID." AND USER_ID<>1", true))
			return false;

		$GLOBALS["USER_FIELD_MANAGER"]->Delete("USER", $ID);

		if(COption::GetOptionString("main", "event_log_user_delete", "N") === "Y")
			CEventLog::Log("SECURITY", "USER_DELETE", "main", $arUser["LOGIN"], "OK");

		return $DB->Query("DELETE FROM b_user WHERE ID=".$ID." AND ID<>1", true);
	}

	function GetExternalAuthList()
	{
		$arAll = Array();
		$events = GetModuleEvents("main", "OnExternalAuthList");
		while($arEvent = $events->Fetch())
		{
			$arRes = ExecuteModuleEventEx($arEvent);
			foreach($arRes as $v)
				$arAll[] = $v;
		}

		$result = new CDBResult;
		$result->InitFromArray($arAll);
		return $result;
	}

	function GetGroupPolicy($iUserId)
	{
		global $DB;
		static $arPOLICY_CACHE;
		if(!is_array($arPOLICY_CACHE))
			$arPOLICY_CACHE = Array();
		$CACHE_ID = md5(serialize($iUserId));
		if(array_key_exists($CACHE_ID, $arPOLICY_CACHE))
			return $arPOLICY_CACHE[$CACHE_ID];

		global $BX_GROUP_POLICY;
		$arPolicy = $BX_GROUP_POLICY;
		if($arPolicy["SESSION_TIMEOUT"]<=0)
			$arPolicy["SESSION_TIMEOUT"] = ini_get("session.gc_maxlifetime")/60;

		$arSql = Array();
		$arSql[] =
			"SELECT G.SECURITY_POLICY ".
			"FROM b_group G ".
			"WHERE G.ID=2";

		if(is_array($iUserId))
		{
			$arGroups = array();
			foreach($iUserId as $value)
			{
				$value = intval($value);
				if($value > 0 && $value != 2)
					$arGroups[$value] = $value;
			}
			if(count($arGroups) > 0)
			{
				$arSql[] =
					"SELECT G.ID GROUP_ID, G.SECURITY_POLICY ".
					"FROM b_group G ".
					"WHERE G.ID in (".implode(", ", $arGroups).")";
			}
		}
		elseif(IntVal($iUserId) > 0)
		{
			$arSql[] =
				"SELECT UG.GROUP_ID, G.SECURITY_POLICY ".
				"FROM b_user_group UG, b_group G ".
				"WHERE UG.USER_ID = ".IntVal($iUserId)." ".
				"	AND UG.GROUP_ID = G.ID ".
				"	AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) ".
				"	AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")) ";
		}

		foreach($arSql as $strSql)
		{
			$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			while($ar = $res->Fetch())
			{
				if($ar["SECURITY_POLICY"])
					$arGroupPolicy = unserialize($ar["SECURITY_POLICY"]);
				else
					continue;

				if(!is_array($arGroupPolicy))
					continue;

				foreach($arGroupPolicy as $key=>$val)
				{
					switch($key)
					{
					case "STORE_IP_MASK":
					case "SESSION_IP_MASK":
						if($arPolicy[$key]<$val)
							$arPolicy[$key] = $val;
						break;
					case "SESSION_TIMEOUT":
						if($arPolicy[$key]<=0 || $arPolicy[$key]>$val)
							$arPolicy[$key] = $val;
						break;
					case "PASSWORD_LENGTH":
						if($arPolicy[$key]<=0 || $arPolicy[$key] < $val)
							$arPolicy[$key] = $val;
						break;
					case "PASSWORD_UPPERCASE":
					case "PASSWORD_LOWERCASE":
					case "PASSWORD_DIGITS":
					case "PASSWORD_PUNCTUATION":
						if($val === "Y")
							$arPolicy[$key] = "Y";
						break;
					case "LOGIN_ATTEMPTS":
						if($val > 0 && ($arPolicy[$key] <= 0 || $arPolicy[$key] > $val))
							$arPolicy[$key] = $val;
						break;
					default:
						if($arPolicy[$key]>$val)
							$arPolicy[$key] = $val;
					}
				}
			}
			if($arPolicy["PASSWORD_LENGTH"] === false)
				$arPolicy["PASSWORD_LENGTH"] = 6;
		}
		$ar = array(
			GetMessage("MAIN_GP_PASSWORD_LENGTH", array("#LENGTH#" => intval($arPolicy["PASSWORD_LENGTH"])))
		);
		if($arPolicy["PASSWORD_UPPERCASE"] === "Y")
			$ar[] = GetMessage("MAIN_GP_PASSWORD_UPPERCASE");
		if($arPolicy["PASSWORD_LOWERCASE"] === "Y")
			$ar[] = GetMessage("MAIN_GP_PASSWORD_LOWERCASE");
		if($arPolicy["PASSWORD_DIGITS"] === "Y")
			$ar[] = GetMessage("MAIN_GP_PASSWORD_DIGITS");
		if($arPolicy["PASSWORD_PUNCTUATION"] === "Y")
			$ar[] = GetMessage("MAIN_GP_PASSWORD_PUNCTUATION");
		$arPolicy["PASSWORD_REQUIREMENTS"] = implode(", ", $ar).".";

		if(count($arPOLICY_CACHE)<=10)
			$arPOLICY_CACHE[$CACHE_ID] = $arPolicy;

		return $arPolicy;
	}

	function CheckStoredHash($iUserId, $sHash)
	{
		global $DB;
		$arPolicy = CUser::GetGroupPolicy($iUserId);
		$strSql =
			"SELECT A.*, ".
			"	".$DB->DateToCharFunction("A.DATE_REG", "FULL")." as DATE_REG, ".
			"	".$DB->DateToCharFunction("A.LAST_AUTH", "FULL")." as LAST_AUTH ".
			"FROM b_user_stored_auth A ".
			"WHERE A.USER_ID = ".IntVal($iUserId)." ".
			"ORDER BY A.LAST_AUTH DESC";

		$cnt = 0;
		$auth_id = false;
		$res = $DB->Query($strSql);
		$site_format = CSite::GetDateFormat();
		while($ar = $res->Fetch())
		{
			if($ar["TEMP_HASH"]=="N")
				$cnt++;
			if($arPolicy["MAX_STORE_NUM"] < $cnt
				|| ($ar["TEMP_HASH"]=="N" && mktime()-$arPolicy["STORE_TIMEOUT"]*60 > MakeTimeStamp($ar["LAST_AUTH"], $site_format))
				|| ($ar["TEMP_HASH"]=="Y" && mktime()-$arPolicy["SESSION_TIMEOUT"]*60 > MakeTimeStamp($ar["LAST_AUTH"], $site_format))
			)
			{
				$DB->Query("DELETE FROM b_user_stored_auth WHERE ID=".$ar["ID"]);
			}
			elseif(!$auth_id)
			{
				$remote_net = ip2long($arPolicy["STORE_IP_MASK"]) & ip2long($_SERVER["REMOTE_ADDR"]);
				$stored_net = ip2long($arPolicy["STORE_IP_MASK"]) & (float)$ar["IP_ADDR"];
				if($sHash == $ar["STORED_HASH"] && $remote_net == $stored_net)
					$auth_id = $ar["ID"];
			}
		}
		return $auth_id;
	}


	function GetAllOperations()
	{
		global $DB;
		$userGroups = $this->GetGroups();

		$sql_str = "
			SELECT O.NAME OPERATION_NAME
			FROM b_group_task GT
				INNER JOIN b_task_operation T_O ON T_O.TASK_ID=GT.TASK_ID
				INNER JOIN b_operation O ON O.ID=T_O.OPERATION_ID
			WHERE GT.GROUP_ID IN(".$userGroups.")
			UNION
			SELECT O.NAME OPERATION_NAME
			FROM b_option OP
				INNER JOIN b_task_operation T_O ON T_O.TASK_ID=".$DB->ToChar("OP.VALUE", 18)."
				INNER JOIN b_operation O ON O.ID=T_O.OPERATION_ID
			WHERE OP.NAME='GROUP_DEFAULT_TASK'
			UNION
			SELECT O.NAME OPERATION_NAME
			FROM b_option OP
				INNER JOIN b_task T ON T.MODULE_ID=OP.MODULE_ID AND T.BINDING='module' AND T.LETTER=".$DB->ToChar("OP.VALUE", 1)." AND T.SYS='Y'
				INNER JOIN b_task_operation T_O ON T_O.TASK_ID=T.ID
				INNER JOIN b_operation O ON O.ID=T_O.OPERATION_ID
			WHERE OP.NAME='GROUP_DEFAULT_RIGHT'
		";

		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		while($r = $z->Fetch())
			$arr[]=$r['OPERATION_NAME'];

		return $arr;
	}

	function CanDoOperation($op_name)
	{
		if ($this->IsAdmin())
			return true;

		if(!is_set($_SESSION["OPERATIONS"]))
			$_SESSION["OPERATIONS"] = $this->GetAllOperations();

		$arAllOperations = $_SESSION["OPERATIONS"];

		if (!isset($arAllOperations))
			return false;

		return in_array($op_name,$arAllOperations);
	}

	function GetFileOperations($arPath, $arGroups=false)
	{
		global $APPLICATION;
		$ar = $APPLICATION->GetFileAccessPermission($arPath, $arGroups, true);
		$arFileOperations = Array();

		for ($i = 0, $len = count($ar); $i < $len; $i++)
			$arFileOperations = array_merge($arFileOperations, CTask::GetOperations($ar[$i], true));
		$arFileOperations = array_values(array_unique($arFileOperations));

		return $arFileOperations;
	}


	function CanDoFileOperation($op_name,$arPath)
	{
		if ($this->IsAdmin())
			return true;
		global $APPLICATION, $USER;

		if(!isset($APPLICATION->FILEMAN_OPERATION_CACHE))
			$APPLICATION->FILEMAN_OPERATION_CACHE = Array();

		$k = addslashes($arPath[0].'|'.$arPath[1]);
		if(array_key_exists($k, $APPLICATION->FILEMAN_OPERATION_CACHE))
			$arFileOperations = $APPLICATION->FILEMAN_OPERATION_CACHE[$k];
		else
		{
			$arFileOperations = $this->GetFileOperations($arPath);
			$APPLICATION->FILEMAN_OPERATION_CACHE[$k] = $arFileOperations;
		}

		$arAlowedOperations = Array('fm_delete_file','fm_rename_folder','fm_view_permission');
		if(substr($arPath[1], -10)=="/.htaccess" && !$USER->CanDoOperation('edit_php') && !in_array($op_name,$arAlowedOperations))
			return false;
		if(substr($arPath[1], -12)=="/.access.php")
			return false;

		return in_array($op_name, $arFileOperations);
	}

	function UserTypeRightsCheck($entity_id)
	{
		if($entity_id == "USER" && $GLOBALS["USER"]->CanDoOperation('edit_other_settings'))
		{
			return "W";
		}
		else
			return "D";
	}

	function CleanUpAgent()
	{
		$bTmpUser = False;
		if (!isset($GLOBALS["USER"]) || !is_object($GLOBALS["USER"]))
		{
			$bTmpUser = True;
			$GLOBALS["USER"] = new CUser;
		}

		$cleanup_days = COption::GetOptionInt("main", "new_user_registration_cleanup_days", 7);
		if($cleanup_days > 0 && COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") === "Y")
		{
			$arDate = localtime(time());
			$date = mktime(0, 0, 0, $arDate[4]+1, $arDate[3]-$cleanup_days, 1900+$arDate[5]);
			$arFilter = array(
				"!CONFIRM_CODE" => false,
				"ACTIVE" => "N",
				"DATE_REGISTER_2" => ConvertTimeStamp($date),
			);
			$rsUsers = CUser::GetList($by, $order, $arFilter);
			while($arUser = $rsUsers->Fetch())
			{
				CUser::Delete($arUser["ID"]);
			}
		}
		if ($bTmpUser)
		{
			unset($GLOBALS["USER"]);
		}

		return "CUser::CleanUpAgent();";
	}

	function GetActiveUsersCount()
	{
		$q = "SELECT COUNT(ID) as C FROM b_user WHERE ACTIVE = 'Y' AND LAST_LOGIN IS NOT NULL";
		if (IsModuleInstalled("intranet"))
			$q = "SELECT COUNT(U.ID) as C FROM b_user U WHERE U.ACTIVE = 'Y' AND U.LAST_LOGIN IS NOT NULL AND EXISTS(SELECT 'x' FROM b_utm_user UF, b_user_field F WHERE F.ENTITY_ID = 'USER' AND F.FIELD_NAME = 'UF_DEPARTMENT' AND UF.FIELD_ID = F.ID AND UF.VALUE_ID = U.ID AND UF.VALUE_INT IS NOT NULL AND UF.VALUE_INT <> 0)";

		$dbRes = $GLOBALS["DB"]->Query($q, true);
		if ($dbRes && ($arRes = $dbRes->Fetch()))
			return $arRes["C"];
		else
			return 0;
	}

	function SetLastActivityDate($id)
	{
		global $DB;

		$id = IntVal($id);
		if ($id <= 0)
			return;

		$DB->Query("UPDATE b_user SET ".
			"TIMESTAMP_X = ".(strtoupper($DB->type) == "ORACLE"? "NULL":"TIMESTAMP_X").", ".
			"LAST_ACTIVITY_DATE = ".$DB->CurrentTimeFunction()." WHERE ID = ".$id.""
			, false, "", array("ignore_dml"=>true));
	}

	function SearchUserByName($arName, $email = "", $bLoginMode = false)
	{
		global $DB;

		$arNameReady = array();
		foreach ($arName as $s)
		{
			$s = Trim($s);
			if (StrLen($s) > 0)
				$arNameReady[] = $s;
		}

		if (Count($arNameReady) <= 0)
			return false;

		$strSqlWhereEMail = ((StrLen($email) > 0) ? " AND upper(U.EMAIL) = upper('".$DB->ForSql($email)."') " : "");

		if ($bLoginMode)
		{
			if (Count($arNameReady) > 3)
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE (";
				$bFirst = true;
				for ($i = 0; $i < 4; $i++)
				{
					for ($j = 0; $j < 4; $j++)
					{
						if ($i == $j)
							continue;

						for ($k = 0; $k < 4; $k++)
						{
							if ($i == $k || $j == $k)
								continue;

							for ($l = 0; $l < 4; $l++)
							{
								if ($i == $l || $j == $l || $k == $l)
									continue;

								if (!$bFirst)
									$strSql .= " OR ";

								$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
									"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
									"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$k])."%') ".
									"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$l])."%'))";

								$bFirst = false;
							}
						}
					}
				}
				$strSql .= ")";
			}
			elseif (Count($arNameReady) == 3)
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE (";
				$bFirst = true;
				for ($i = 0; $i < 3; $i++)
				{
					for ($j = 0; $j < 3; $j++)
					{
						if ($i == $j)
							continue;

						for ($k = 0; $k < 3; $k++)
						{
							if ($i == $k || $j == $k)
								continue;

							if (!$bFirst)
								$strSql .= " OR ";

							$strSql .= "(";
							$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
								"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
								"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$k])."%'))";
							$strSql .= " OR ";
							$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
								"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
								"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$k])."%'))";
							$strSql .= " OR ";
							$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
								"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
								"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$k])."%'))";
							$strSql .= " OR ";
							$strSql .= "(U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
								"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
								"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$k])."%'))";
							$strSql .= ")";

							$bFirst = false;
						}
					}
				}
				$strSql .= ")";
			}
			elseif (Count($arNameReady) == 2)
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE (";
				$bFirst = true;
				for ($i = 0; $i < 2; $i++)
				{
					for ($j = 0; $j < 2; $j++)
					{
						if ($i == $j)
							continue;

						if (!$bFirst)
							$strSql .= " OR ";

						$strSql .= "(";
						$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= " OR ";
						$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= " OR ";
						$strSql .= "(U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= " OR ";
						$strSql .= "(U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= " OR ";
						$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= " OR ";
						$strSql .= "(U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[$j])."%'))";
						$strSql .= ")";
						$bFirst = false;
					}
				}
				$strSql .= ")";
			}
			else
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE (U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[0])."%') ".
					"	OR U.LOGIN IS NOT NULL AND upper(U.LOGIN) LIKE upper('".$DB->ForSql($arNameReady[0])."%') ".
					"	OR U.EMAIL IS NOT NULL AND upper(U.EMAIL) LIKE upper('".$DB->ForSql($arNameReady[0])."%') ".
					"	OR U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[0])."%')) ";
			}
			$strSql .= $strSqlWhereEMail;
		}
		else
		{
			if (Count($arNameReady) >= 3)
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE ";
				$bFirst = true;
				for ($i = 0; $i < 3; $i++)
				{
					for ($j = 0; $j < 3; $j++)
					{
						if ($i == $j)
							continue;

						for ($k = 0; $k < 3; $k++)
						{
							if ($i == $k || $j == $k)
								continue;

							if (!$bFirst)
								$strSql .= " OR ";

							$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
								"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%') ".
								"AND U.SECOND_NAME IS NOT NULL AND upper(U.SECOND_NAME) LIKE upper('".$DB->ForSql($arNameReady[$k])."%')".$strSqlWhereEMail.")";

							$bFirst = false;
						}
					}
				}
			}
			elseif (Count($arNameReady) == 2)
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE ";
				$bFirst = true;
				for ($i = 0; $i < 2; $i++)
				{
					for ($j = 0; $j < 2; $j++)
					{
						if ($i == $j)
							continue;

						if (!$bFirst)
							$strSql .= " OR ";

						$strSql .= "(U.NAME IS NOT NULL AND upper(U.NAME) LIKE upper('".$DB->ForSql($arNameReady[$i])."%') ".
							"AND U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[$j])."%')".$strSqlWhereEMail.")";

						$bFirst = false;
					}
				}
			}
			else
			{
				$strSql =
					"SELECT U.ID, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.EMAIL ".
					"FROM b_user U ".
					"WHERE U.LAST_NAME IS NOT NULL AND upper(U.LAST_NAME) LIKE upper('".$DB->ForSql($arNameReady[0])."%') ".
					$strSqlWhereEMail;
			}
		}

		$dbRes = $DB->Query($strSql);
		return $dbRes;
	}

	function FormatName($NAME_TEMPLATE, $arUser, $bUseLogin = true, $bHTMLSpec = true)
	{
		$NAME = trim($arUser['NAME']);
		$LAST_NAME = trim($arUser['LAST_NAME']);
		$SECOND_NAME = trim($arUser['SECOND_NAME']);
		$LOGIN = trim($arUser['LOGIN']);

		if (array_key_exists("EMAIL", $arUser))
			$EMAIL = trim($arUser['EMAIL']);
		else
			$EMAIL = '';

		if (array_key_exists("ID", $arUser))
			$ID = intval($arUser['ID']);
		else
			$ID = '';

		$NAME_SHORT = $NAME ? substr($NAME, 0, 1).'.' : '';
		$LAST_NAME_SHORT = $LAST_NAME ? substr($LAST_NAME, 0, 1).'.' : '';
		$SECOND_NAME_SHORT = $SECOND_NAME ? substr($SECOND_NAME, 0, 1).'.' : '';

		$res = str_replace(
			array('#NAME#', '#LAST_NAME#', '#SECOND_NAME#', '#NAME_SHORT#', '#LAST_NAME_SHORT#', '#SECOND_NAME_SHORT#', '#EMAIL#', '#ID#'),
			array( $NAME,    $LAST_NAME,    $SECOND_NAME,    $NAME_SHORT,    $LAST_NAME_SHORT,    $SECOND_NAME_SHORT,   $EMAIL,    $ID),
			$NAME_TEMPLATE
		);

		$res_check = "";
		if (strpos($NAME_TEMPLATE, '#NAME#') !== false || strpos($NAME_TEMPLATE, '#NAME_SHORT#') !== false)
			$res_check .= $arUser['NAME'];
		if (strpos($NAME_TEMPLATE, '#LAST_NAME#') !== false || strpos($NAME_TEMPLATE, '#LAST_NAME_SHORT#') !== false)
			$res_check .= $arUser['LAST_NAME'];
		if (strpos($NAME_TEMPLATE, '#SECOND_NAME#') !== false || strpos($NAME_TEMPLATE, '#SECOND_NAME_SHORT#') !== false)
			$res_check .= $arUser['SECOND_NAME'];

		if (strlen(trim($res_check)) <= 0)
		{
			if ($bUseLogin && strlen($arUser['LOGIN']) > 0)
				$res = $arUser['LOGIN'];
			else
				$res = GetMessage('FORMATNAME_NONAME');
		}

		if ($bHTMLSpec)
			$res = htmlspecialchars($res);

		$res = str_replace(array('#NOBR#', '#/NOBR#'), array('<nobr>', '</nobr>'), $res);

		if (strpos($res, '<nobr>') !== false && strpos($res, '</nobr>') === false)
			$res .= '</nobr>';

		return $res;
	}
}

class CAllGroup
{
	function err_mess()
	{
		return "<br>Class: CAllGroup<br>File: ".__FILE__;
	}

	function CheckFields($arFields, $ID=false)
	{
		global $DB;
		$this->LAST_ERROR = "";

		if(is_set($arFields, "NAME") && strlen($arFields["NAME"])<3)
			$this->LAST_ERROR .= GetMessage("BAD_GROUP_NAME")."<br>";

		if (is_array($arFields["USER_ID"]) && count($arFields["USER_ID"]) > 0)
		{
			if (is_array($arFields["USER_ID"][0]) && count($arFields["USER_ID"][0]) > 0)
			{
				foreach($arFields["USER_ID"] as $arUser)
				{
					if(strlen($arUser["DATE_ACTIVE_FROM"])>0 && !CheckDateTime($arUser["DATE_ACTIVE_FROM"]))
					{
						$error = str_replace("#USER_ID#", $arUser["USER_ID"], GetMessage("WRONG_USER_DATE_ACTIVE_FROM"));
						$this->LAST_ERROR .= $error."<br>";
					}

					if(strlen($arUser["DATE_ACTIVE_TO"])>0 && !CheckDateTime($arUser["DATE_ACTIVE_TO"]))
					{
						$error = str_replace("#USER_ID#", $arUser["USER_ID"], GetMessage("WRONG_USER_DATE_ACTIVE_TO"));
						$this->LAST_ERROR .= $error."<br>";
					}
				}
			}
		}
		if (isset($arFields['STRING_ID']) && strlen($arFields['STRING_ID']) > 0)
		{
			$sql_str = "SELECT G.ID
					FROM b_group G
					WHERE G.STRING_ID='".$DB->ForSql($arFields['STRING_ID'])."'";
			$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			if ($r = $z->Fetch())
			{
				if ($ID === false || $ID != $r['ID'])
					$this->LAST_ERROR .= GetMessage('MAIN_ERROR_STRING_ID')."<br>";
			}
		}
		if(strlen($this->LAST_ERROR)>0)
			return false;

		return true;
	}

	function Update($ID, $arFields)
	{
		global $DB, $APPLICATION;

		$ID = intval($ID);

		if(!$this->CheckFields($arFields, $ID))
			return false;

		$events = GetModuleEvents("main", "OnBeforeGroupUpdate");
		while($arEvent = $events->Fetch())
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array($ID, &$arFields));
			if($bEventRes===false)
			{
				if($err = $APPLICATION->GetException())
					$this->LAST_ERROR .= $err->GetString()."<br>";
				else
					$this->LAST_ERROR .= "Unknown error in OnBeforeGroupUpdate handler."."<br>";
				return false;
			}
		}

		if($ID<=2)
			unset($arFields["ACTIVE"]);

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		$strUpdate = $DB->PrepareUpdate("b_group", $arFields);

		if(!is_set($arFields, "TIMESTAMP_X"))
			$strUpdate .= ", TIMESTAMP_X = ".$DB->GetNowFunction();


		$strSql = "UPDATE b_group SET $strUpdate WHERE ID=$ID";
		if(is_set($arFields, "SECURITY_POLICY"))
		{
			if(COption::GetOptionString("main", "event_log_group_policy", "N") === "Y")
			{
				//get old security policy
				$aPrevPolicy = array();
				$res = $DB->Query("SELECT SECURITY_POLICY FROM b_group WHERE ID=".$ID);
				if(($res_arr = $res->Fetch()) && $res_arr["SECURITY_POLICY"] <> '')
					$aPrevPolicy = unserialize($res_arr["SECURITY_POLICY"]);
				//compare with new one
				$aNewPolicy = array();
				if($arFields["SECURITY_POLICY"] <> '')
					$aNewPolicy = unserialize($arFields["SECURITY_POLICY"]);
				$aDiff = array_diff_assoc($aNewPolicy, $aPrevPolicy);
				if(empty($aDiff))
					$aDiff = array_diff_assoc($aPrevPolicy, $aNewPolicy);
				if(!empty($aDiff))
					CEventLog::Log("SECURITY", "GROUP_POLICY_CHANGED", "main", $ID, print_r($aPrevPolicy, true)." => ".print_r($aNewPolicy, true));
			}
			$DB->QueryBind($strSql, Array("SECURITY_POLICY"=>$arFields["SECURITY_POLICY"]), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
		else
		{
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		if(is_set($arFields, "USER_ID") && is_array($arFields["USER_ID"]))
		{
			if(COption::GetOptionString("main", "event_log_user_groups", "N") === "Y")
			{
				//remember users in the group
				$aPrevUsers = array();
				$res = $DB->Query("SELECT USER_ID FROM b_user_group WHERE GROUP_ID=".$ID.($ID=="1"?" AND USER_ID<>1":""));
				while($res_arr = $res->Fetch())
					$aPrevUsers[] = $res_arr["USER_ID"];
			}

			$DB->Query("DELETE FROM b_user_group WHERE GROUP_ID=".$ID.($ID=="1"?" AND USER_ID<>1":""));

			$aNewUsers = array();
			if (count($arFields["USER_ID"]) > 0)
			{
				if (is_array($arFields["USER_ID"][0]) && count($arFields["USER_ID"][0]) > 0)
				{
					$arTmp = array();
					for ($i = 0; $i < count($arFields["USER_ID"]); $i++)
					{
						if (IntVal($arFields["USER_ID"][$i]["USER_ID"]) > 0
							&& !in_array(IntVal($arFields["USER_ID"][$i]["USER_ID"]), $arTmp)
							&& ($ID != 1 || IntVal($arFields["USER_ID"][$i]["USER_ID"]) != 1))
						{
							$arInsert = $DB->PrepareInsert("b_user_group", $arFields["USER_ID"][$i]);

							$strSql =
								"INSERT INTO b_user_group(GROUP_ID, ".$arInsert[0].") ".
								"VALUES(".$ID.", ".$arInsert[1].")";
							$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

							$arTmp[] = IntVal($arFields["USER_ID"][$i]["USER_ID"]);
						}
					}
					$aNewUsers = $arTmp;
				}
				else
				{
					$strUsers = "0";
					for($i=0; $i<count($arFields["USER_ID"]); $i++)
					{
						$user_id = intval($arFields["USER_ID"][$i]);
						$strUsers.=",".$user_id;
						$aNewUsers[] = $user_id;
					}

					$strSql =
						"INSERT INTO b_user_group(GROUP_ID, USER_ID) ".
						"SELECT ".$ID.", ID ".
						"FROM b_user ".
						"WHERE ID in (".$strUsers.")".($ID==1?" AND ID>1 ":"");

					$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
				}
			}

			if(COption::GetOptionString("main", "event_log_user_groups", "N") === "Y")
			{
				$aNewUsers = array_unique($aNewUsers);
				foreach($aPrevUsers as $user_id)
					if(!in_array($user_id, $aNewUsers))
						CEventLog::Log("SECURITY", "USER_GROUP_CHANGED", "main", $user_id, "-(".$ID.")");
				foreach($aNewUsers as $user_id)
					if(!in_array($user_id, $aPrevUsers))
						CEventLog::Log("SECURITY", "USER_GROUP_CHANGED", "main", $user_id, "+(".$ID.")");
			}
		}

		$events = GetModuleEvents("main", "OnAfterGroupUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID, &$arFields));

		return true;
	}

	function Delete($ID)
	{
		global $APPLICATION, $DB;

		$ID = IntVal($ID);
		if($ID<=2)
			return false;

		@set_time_limit(600);

		$bCanDelete = true;
		$db_events = GetModuleEvents("main", "OnBeforeGroupDelete");
		while($arEvent = $db_events->Fetch())
			if(ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				return false;
			}


		//проверка - оставил ли тут какой-нибудь модуль обработчик на OnDelete
		$events = GetModuleEvents("main", "OnGroupDelete");
		while($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID));

		CMain::DelGroupRight("",array($ID));

		if(!$DB->Query("DELETE FROM b_user_group WHERE GROUP_ID=".$ID." AND GROUP_ID>2", true))
			return false;

		return $DB->Query("DELETE FROM b_group WHERE ID=".$ID." AND ID>2", true);
	}

	function GetGroupUser($ID)
	{
		global $DB;
		$ID = intval($ID);

		if ($ID == 2)
		{
			$strSql = "SELECT U.ID as USER_ID FROM b_user U ";
		}
		else
		{
			$strSql =
				"SELECT UG.USER_ID ".
				"FROM b_user_group UG ".
				"WHERE UG.GROUP_ID = ".$ID." ".
				"	AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) ".
				"	AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")) ";
		}

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		while($r = $res->Fetch())
			$arr[]=$r["USER_ID"];

		return $arr;
	}

	function GetGroupUserEx($ID)
	{
		global $DB;
		$ID = intval($ID);

		if ($ID == 2)
		{
			$strSql = "SELECT U.ID as USER_ID, NULL as DATE_ACTIVE_FROM, NULL as DATE_ACTIVE_TO FROM b_user U ";
		}
		else
		{
			$strSql =
				"SELECT UG.USER_ID, ".
				"	".$DB->DateToCharFunction("UG.DATE_ACTIVE_FROM", "FULL")." as DATE_ACTIVE_FROM, ".
				"	".$DB->DateToCharFunction("UG.DATE_ACTIVE_TO", "FULL")." as DATE_ACTIVE_TO ".
				"FROM b_user_group UG ".
				"WHERE UG.GROUP_ID = ".$ID." ".
				"	AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) ".
				"	AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")) ";
		}
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	function GetMaxSort()
	{
		global $DB;
		$err_mess = (CAllGroup::err_mess())."<br>Function: GetMaxSort<br>Line: ";
		$z = $DB->Query("SELECT max(C_SORT) M FROM b_group", false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		return intval($zr["M"])+100;
	}

	function GetSubordinateGroups($grId)
	{
		global $DB;
		$z = $DB->Query("SELECT AR_SUBGROUP_ID FROM b_group_subordinate WHERE ID=".intval($grId), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$zr = $z->Fetch();
		$strSubordinateGroups = $zr['AR_SUBGROUP_ID'];
		$arSubordinateGroups = explode(",", $strSubordinateGroups);
		if (count($arSubordinateGroups)==1 && !$arSubordinateGroups[0])
			$arSubordinateGroups = Array();
		$arSubordinateGroups[] = 2;
		return $arSubordinateGroups;
	}

	function SetSubordinateGroups($grId, $arSubGroups=false)
	{
		global $DB;
		$z1 = $DB->Query("DELETE FROM b_group_subordinate WHERE ID=".intval($grId), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if (!$arSubGroups)
			return;
		$strSubordinateGroups = $DB->ForSQL(implode(",", $arSubGroups));
		$z2 = $DB->Query("INSERT INTO b_group_subordinate(ID, AR_SUBGROUP_ID) VALUES (".intval($grId).",'".$DB->ForSQL($strSubordinateGroups)."')", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}


	function GetTasks($ID, $onlyMainTasks=true, $module_id=false)
	{
		$arr = array();
		global $DB;
		$sql_str = 'SELECT GT.TASK_ID,T.MODULE_ID,GT.EXTERNAL_ID
				FROM b_group_task GT
				INNER JOIN b_task T ON (T.ID=GT.TASK_ID)
				WHERE GT.GROUP_ID='.intval($ID);
		if ($module_id !== false)
			$sql_str .= ' AND T.MODULE_ID="'.$DB->ForSQL($module_id).'"';

		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = array();
		$ex_arr = array();
		while($r = $z->Fetch())
		{
			if (!$r['EXTERNAL_ID'])
				$arr[$r['MODULE_ID']] = $r['TASK_ID'];
			else
				$ex_arr[] = $r;
		}
		if ($onlyMainTasks)
			return $arr;
		else
			return Array($arr,$ex_arr);
	}


	function SetTasks($ID, $arr)
	{
		global $DB;
		$ID = intval($ID);

		if(COption::GetOptionString("main", "event_log_module_access", "N") === "Y")
		{
			//get old values
			$arOldTasks = array();
			$rsTask = $DB->Query("SELECT TASK_ID FROM b_group_task WHERE GROUP_ID=".$ID);
			while($arTask = $rsTask->Fetch())
				$arOldTasks[] = $arTask["TASK_ID"];
			//compare with new ones
			$aNewTasks = array();
			foreach($arr as $task_id)
				if($task_id > 0)
					$aNewTasks[] = $task_id;
			$aDiff = array_diff($arOldTasks, $aNewTasks);
			if(empty($aDiff))
				$aDiff = array_diff($aNewTasks, $arOldTasks);
			if(!empty($aDiff))
				CEventLog::Log("SECURITY", "MODULE_RIGHTS_CHANGED", "main", $ID, "(".implode(", ", $arOldTasks).") => (".implode(", ", $aNewTasks).")");
		}

		$sql_str = "DELETE FROM b_group_task WHERE GROUP_ID=".$ID.
				" AND (EXTERNAL_ID IS NULL OR EXTERNAL_ID = '')";
		$DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$sID = "0";
		if(is_array($arr))
			foreach($arr as $task_id)
				$sID .= ",".intval($task_id);

		$DB->Query(
			"INSERT INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) ".
			"SELECT '".$ID."', ID, '' ".
			"FROM b_task ".
			"WHERE ID IN (".$sID.") "
			, false, "File: ".__FILE__."<br>Line: ".__LINE__
		);
	}


	function GetTasksForModule($module_id, $onlyMainTasks = true)
	{
		global $DB;
		$arr = array();
		$sql_str = "SELECT GT.TASK_ID,GT.GROUP_ID,GT.EXTERNAL_ID,T.NAME
				FROM b_group_task GT
				INNER JOIN b_task T ON (T.ID=GT.TASK_ID)
				WHERE T.MODULE_ID='".$DB->ForSQL($module_id)."'";

		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$main_arr = array();
		$ext_arr = array();
		while($r = $z->Fetch())
		{
			if (!$r['EXTERNAL_ID'])
			{
				$main_arr[$r['GROUP_ID']] = Array('ID'=>$r['TASK_ID'],'NAME'=>$r['NAME']);
			}
			elseif(!$onlyMainTasks)
			{
				if (!isset($ext_arr[$r['GROUP_ID']]))
					$ext_arr[$r['GROUP_ID']] = array();
				$ext_arr[$r['GROUP_ID']][] = Array('ID'=>$r['TASK_ID'],'NAME'=>$r['NAME'],'EXTERNAL_ID'=>$r['EXTERNAL_ID']);
			}
		}
		if ($onlyMainTasks)
			return $main_arr;
		else
			return Array($main_arr,$ext_arr);
	}


	function SetTasksForModule($module_id, $arGroupTask)
	{
		global $DB;
		$module_id = $DB->ForSql($module_id);
		$sql_str = "SELECT T.ID
				FROM b_task T
				WHERE T.MODULE_ID='".$module_id."'";
		$r = $DB->Query($sql_str, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arIds = Array();
		while($arR = $r->Fetch())
			$arIds[] = $arR['ID'];

		if(COption::GetOptionString("main", "event_log_module_access", "N") === "Y")
		{
			//get old values
			$arOldTasks = array();
			$rsTask = $DB->Query("SELECT GROUP_ID, TASK_ID FROM b_group_task WHERE TASK_ID IN (".implode(",", $arIds).")");
			while($arTask = $rsTask->Fetch())
				$arOldTasks[$arTask["GROUP_ID"]] = $arTask["TASK_ID"];
			//compare with new ones
			foreach($arOldTasks as $gr_id=>$task_id)
				if($task_id <> $arGroupTask[$gr_id]['ID'])
					CEventLog::Log("SECURITY", "MODULE_RIGHTS_CHANGED", "main", $gr_id, $module_id.": (".$task_id.") => (".$arGroupTask[$gr_id]['ID'].")");
			foreach($arGroupTask as $gr_id => $oTask)
				if(intval($oTask['ID']) > 0 && !array_key_exists($gr_id, $arOldTasks))
					CEventLog::Log("SECURITY", "MODULE_RIGHTS_CHANGED", "main", $gr_id, $module_id.": () => (".$oTask['ID'].")");
		}

		$sql_str = "DELETE FROM b_group_task
				WHERE TASK_ID IN (".implode(",", $arIds).")";
		$r = $DB->Query($sql_str, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		foreach($arGroupTask as $gr_id => $oTask)
		{
			if(intval($oTask['ID']) > 0)
			{
				$DB->Query(
					"INSERT INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) ".
					"SELECT G.ID, T.ID, '' ".
					"FROM b_group G, b_task T ".
					"WHERE G.ID = ".intval($gr_id)." AND
					T.ID = ".intval($oTask['ID']),
					false, "File: ".__FILE__."<br>Line: ".__LINE__
				);
			}
		}
	}

	function GetModulePermission($group_id, $module_id)
	{
		global $APPLICATION, $DB;

		// check module permissions mode
		$strSql = "SELECT T.ID, GT.TASK_ID FROM b_task T LEFT JOIN b_group_task GT ON T.ID=GT.TASK_ID AND GT.GROUP_ID=".IntVal($group_id)." WHERE T.MODULE_ID='".$DB->ForSql($module_id)."'";
		$dbr_tasks = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($ar_task = $dbr_tasks->Fetch())
		{
			if($ar_task["TASK_ID"]>0)
				return $ar_task["TASK_ID"];

			return false;
		}

		return $APPLICATION->GetGroupRight($module_id, Array($group_id), "N", "N");
	}

	function SetModulePermission($group_id, $module_id, $permission)
	{
		global $DB, $APPLICATION;

		if(intval($permission)<=0 && $permission != false)
		{
			$strSql = "SELECT T.ID FROM b_task T WHERE T.MODULE_ID='".$DB->ForSql($module_id)."' AND NAME='".$DB->ForSql($permission)."'";
			$db_task = $DB->Query($strSql);
			if($ar_task=$db_task->Fetch())
				$permission = $ar_task['ID'];
		}


		if(intval($permission)>0 || $permission === false)
		{
			$strSql = "SELECT T.ID FROM b_task T WHERE T.MODULE_ID='".$DB->ForSql($module_id)."'";
			$dbr_tasks = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$arIds = Array();
			while($arTask = $dbr_tasks->Fetch())
				$arIds[] = $arTask['ID'];

			if(count($arIds)>0)
			{
				$strSql = "DELETE FROM b_group_task WHERE GROUP_ID=".IntVal($group_id)." AND TASK_ID IN (".implode(",", $arIds).")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

			if(intval($permission)>0)
			{
				$DB->Query(
					"INSERT INTO b_group_task (GROUP_ID, TASK_ID, EXTERNAL_ID) ".
					"SELECT G.ID, T.ID, '' ".
					"FROM b_group G, b_task T ".
					"WHERE G.ID = ".intval($group_id)." AND T.ID = ".intval($permission),
					false,
					"File: ".__FILE__."<br>Line: ".__LINE__
				);

				$permission_letter = CTask::GetLetter($permission);
			}
		}
		else
		{
			$permission_letter = $permission;
		}

		if(strlen($permission_letter)>0)
			$APPLICATION->SetGroupRight($module_id, $group_id, $permission_letter);
		else
			$APPLICATION->DelGroupRight($module_id, array($group_id));
	}

	function GetIDByCode($code)
	{
		if(strval(intval($code)) == $code && $code > 0)
			return $code;

		if(strtolower($code) == 'administrators')
			return 1;

		if(strtolower($code) == 'everyone')
			return 2;

		global $DB;

		$strSql = "SELECT G.ID FROM b_group G WHERE G.STRING_ID='".$DB->ForSQL($code)."'";
		$db_res = $DB->Query($strSql);

		if($ar_res = $db_res->Fetch())
			return $ar_res["ID"];

		return false;
	}
}


class CAllTask
{
	var $TASK_OPERATIONS_CACHE = Array();

	function err_mess()
	{
		return "<br>Class: CAllTask<br>File: ".__FILE__;
	}

	function CheckFields(&$arFields, $ID = false)
	{
		$arErrMsg = Array();

		if($ID>0)
			unset($arFields["ID"]);

		global $DB;
		if(($ID===false || is_set($arFields, "NAME")) && strlen($arFields["NAME"])<=0)
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage('MAIN_ERROR_STRING_ID_EMPTY'));

		$sql_str = "SELECT T.ID
				FROM b_task T
				WHERE T.NAME='".$DB->ForSQL($arFields['NAME'])."'";
		if ($ID !== false)
			$sql_str .= " AND T.ID <> ".intval($ID);

		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if ($r = $z->Fetch())
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage('MAIN_ERROR_STRING_ID_DOUBLE'));

		if (isset($arFields['LETTER']))
		{
			if (preg_match("/[^A-Z]/i", $arFields['LETTER']) || strlen($arFields['LETTER']) > 1)
				$arMsg[] = array("id"=>"LETTER", "text"=> GetMessage('MAIN_TASK_WRONG_LETTER'));
			$arFields['LETTER'] = strtoupper($arFields['LETTER']);
		}
		else
		{
			$arFields['LETTER'] = '';
		}

		if(count($arMsg)>0)
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		if (!isset($arFields['SYS']) || $arFields['SYS'] != "Y")
			$arFields['SYS'] = "N";
		if (!isset($arFields['BINDING']))
			$arFields['BINDING'] = 'module';

		return true;
	}

	function Add($arFields)
	{
		if(!CTask::CheckFields($arFields))
			return false;

		if(CACHED_b_task !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_task");

		$ID = CDatabase::Add("b_task", $arFields);
		return $ID;
	}

	function Update($arFields,$ID)
	{
		if(!CTask::CheckFields($arFields,$ID))
			return false;
		global $DB;

		$strUpdate = $DB->PrepareUpdate("b_task", $arFields);

		if($strUpdate)
		{
			if(CACHED_b_task !== false)
				$GLOBALS["CACHE_MANAGER"]->CleanDir("b_task");
			$strSql =
				"UPDATE b_task SET ".
					$strUpdate.
				" WHERE ID=".IntVal($ID);
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return true;
	}

	function UpdateModuleRights($id, $moduleId, $letter)
	{
		if (!isset($id, $moduleId))
			return false;
		global $DB;
		$sql = "SELECT GT.GROUP_ID
				FROM b_group_task GT
				WHERE GT.TASK_ID=".intval($id);
		$z = $DB->Query($sql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$arGroups = Array();
		while($r = $z->Fetch())
		{
			$g = intval($r['GROUP_ID']);
			if ($g > 0)
				$arGroups[] = $g;
		}
		if (count($arGroups) == 0)
			return false;

		$str_groups = implode(',', $arGroups);
		$moduleId = $DB->ForSQL($moduleId);
		$DB->Query(
			"DELETE FROM b_module_group
			WHERE
				MODULE_ID = '".$moduleId."' AND
				GROUP_ID IN (".$str_groups.")",
			false, "FILE: ".__FILE__."<br> LINE: ".__LINE__
		);

		if (strlen($letter) <= 0)
			return;

		$letter = $DB->ForSQL($letter);
		$DB->Query(
			"INSERT INTO b_module_group (MODULE_ID, GROUP_ID, G_ACCESS) ".
			"SELECT '".$moduleId."', G.ID, '".$letter."' ".
			"FROM b_group G ".
			"WHERE G.ID IN (".$str_groups.")"
			, false, "File: ".__FILE__."<br>Line: ".__LINE__
		);
	}

	function Delete($ID, $protect = true)
	{
		global $DB;
		$ID = intval($ID);

		if(CACHED_b_task !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_task");

		$sql_str = "DELETE FROM b_task WHERE ID=".$ID;
		if ($protect)
			$sql_str .= " AND SYS='N'";
		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (!$protect)
		{
			if(CACHED_b_task_operation !== false)
				$GLOBALS["CACHE_MANAGER"]->CleanDir("b_task_operation");

			$z = $DB->Query("DELETE FROM b_task_operation WHERE TASK_ID=".$ID, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
	}

	function GetList($arOrder = Array('MODULE_ID'=>'asc','LETTER'=>'asc'),$arFilter=Array())
	{
		global $DB, $USER, $CACHE_MANAGER;;

		if(CACHED_b_task !== false)
		{
			$cacheId = "b_task".md5(serialize($arOrder).".".serialize($arFilter));
			if($CACHE_MANAGER->Read(CACHED_b_task, $cacheId, "b_task"))
			{
				$arResult = $CACHE_MANAGER->Get($cacheId);
				$res = new CDBResult;
				$res->InitFromArray($arResult);
				return $res;
			}
		}

		static $arFields = array(
			"ID" => Array("FIELD_NAME" => "T.ID", "FIELD_TYPE" => "int"),
			"NAME" => Array("FIELD_NAME" => "T.NAME", "FIELD_TYPE" => "string"),
			"LETTER" => Array("FIELD_NAME" => "T.LETTER", "FIELD_TYPE" => "string"),
			"MODULE_ID" => Array("FIELD_NAME" => "T.MODULE_ID", "FIELD_TYPE" => "string"),
			"SYS" => Array("FIELD_NAME" => "T.SYS", "FIELD_TYPE" => "string"),
			"BINDING" => Array("FIELD_NAME" => "T.BINDING", "FIELD_TYPE" => "string")
		);

		$err_mess = (CAllTask::err_mess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = array();
		$strSqlSearch = "";
		if(is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for($i=0, $l = count($filter_keys); $i<$l; $i++)
			{
				$n = strtoupper($filter_keys[$i]);
				$val = $arFilter[$filter_keys[$i]];
				if(strlen($val)<=0 || strval($val)=="NOT_REF")
					continue;
				if ($n == 'ID')
					$arSqlSearch[] = GetFilterQuery("T.ID",$val,'N');
				elseif(isset($arFields[$n]))
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"],$val);
			}
		}

		$strOrderBy = '';
		foreach($arOrder as $by=>$order)
			if(isset($arFields[strtoupper($by)]))
				$strOrderBy .= $arFields[strtoupper($by)]["FIELD_NAME"].' '.(strtolower($order)=='desc'?'desc'.(strtoupper($DB->type)=="ORACLE"?" NULLS LAST":""):'asc'.(strtoupper($DB->type)=="ORACLE"?" NULLS FIRST":"")).',';

		if(strlen($strOrderBy)>0)
			$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				T.ID, T.NAME, T.DESCRIPTION, T.MODULE_ID, T.LETTER, T.SYS, T.BINDING
			FROM
				b_task T
			WHERE
				$strSqlSearch
			$strOrderBy";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$arResult = Array();
		while($arRes = $res->Fetch())
		{
			$arRes['TITLE'] = CTask::GetLangTitle($arRes['NAME']);
			$arRes['DESC'] = CTask::GetLangDescription($arRes['NAME'],$arRes['DESCRIPTION']);
			$arResult[]=$arRes;
		}
		$res->InitFromArray($arResult);

		if(CACHED_b_task !== false)
		{
			$CACHE_MANAGER->Set($cacheId, $arResult);
		}

		return $res;
	}


	function GetOperations($ID, $return_names = false)
	{
		global $DB, $USER, $CACHE_MANAGER;
		$ID = intval($ID);

		if (isset($USER->TASK_OPERATIONS_CACHE[$ID]))
			return $USER->TASK_OPERATIONS_CACHE[$ID][$return_names ? 'names' : 'ids'];

		if(CACHED_b_task_operation !== false)
		{
			$cacheId = "b_task_operation_".$ID;
			if($CACHE_MANAGER->Read(CACHED_b_task_operation, $cacheId, "b_task_operation"))
			{
				$USER->TASK_OPERATIONS_CACHE[$ID] = $CACHE_MANAGER->Get($cacheId);
				return $USER->TASK_OPERATIONS_CACHE[$ID][$return_names ? 'names' : 'ids'];
			}
		}

		$sql_str = 'SELECT T_O.OPERATION_ID, O.NAME FROM b_task_operation T_O INNER JOIN b_operation O ON (T_O.OPERATION_ID=O.ID)';
		$sql_str .= ' WHERE T_O.TASK_ID='.$ID;

		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arResult = Array('names'=>Array(),'ids'=>Array());
		while($r = $z->Fetch())
		{
			$arResult['names'][] = $r['NAME'];
			$arResult['ids'][] = $r['OPERATION_ID'];
		}

		$USER->TASK_OPERATIONS_CACHE[$ID] = $arResult;

		if(CACHED_b_task_operation !== false)
		{
			$CACHE_MANAGER->Set($cacheId, $arResult);
		}

		return $arResult[$return_names ? 'names' : 'ids'];
	}

	function SetOperations($ID, $arr, $bOpNames=false)
	{
		global $DB;
		$ID = intval($ID);

		if(COption::GetOptionString("main", "event_log_task", "N") === "Y")
		{
			//get old operations
			$aPrevOp = array();
			$res = $DB->Query("SELECT O.NAME FROM b_operation O INNER JOIN b_task_operation T_OP ON O.ID=T_OP.OPERATION_ID WHERE T_OP.TASK_ID=".$ID." ORDER BY O.ID");
			while(($res_arr = $res->Fetch()))
				$aPrevOp[] = $res_arr["NAME"];
		}

		$sql_str = 'DELETE FROM b_task_operation WHERE TASK_ID='.$ID;
		$DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if(is_array($arr) && count($arr)>0)
		{
			if($bOpNames)
			{
				$sID = "";
				foreach($arr as $op_id)
					$sID .= ",'".$DB->ForSQL($op_id)."'";
				$sID = LTrim($sID, ",");

				$DB->Query(
					"INSERT INTO b_task_operation (TASK_ID, OPERATION_ID) ".
					"SELECT '".$ID."', O.ID ".
					"FROM b_operation O, b_task T ".
					"WHERE O.NAME IN (".$sID.") AND T.MODULE_ID=O.MODULE_ID AND T.ID=".$ID." "
					, false, "File: ".__FILE__."<br>Line: ".__LINE__
				);
			}
			else
			{
				$sID = "0";
				foreach($arr as $op_id)
					$sID .= ",".intval($op_id);

				$DB->Query(
					"INSERT INTO b_task_operation (TASK_ID, OPERATION_ID) ".
					"SELECT '".$ID."', ID ".
					"FROM b_operation ".
					"WHERE ID IN (".$sID.") "
					, false, "File: ".__FILE__."<br>Line: ".__LINE__
				);
			}
		}

		if(COption::GetOptionString("main", "event_log_task", "N") === "Y")
		{
			//get new operations
			$aNewOp = array();
			$res = $DB->Query("SELECT O.NAME FROM b_operation O INNER JOIN b_task_operation T_OP ON O.ID=T_OP.OPERATION_ID WHERE T_OP.TASK_ID=".$ID." ORDER BY O.ID");
			while(($res_arr = $res->Fetch()))
				$aNewOp[] = $res_arr["NAME"];
			//compare with old one
			$aDiff = array_diff($aNewOp, $aPrevOp);
			if(empty($aDiff))
				$aDiff = array_diff($aPrevOp, $aNewOp);
			if(!empty($aDiff))
				CEventLog::Log("SECURITY", "TASK_CHANGED", "main", $ID, "(".implode(", ", $aPrevOp).") => (".implode(", ", $aNewOp).")");
		}

		if(CACHED_b_task_operation !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_task_operation");
	}

	function GetTasksInModules($mode=false, $module_id=false, $binding = false)
	{
		global $DB;
		$arFilter = Array();
		if ($module_id !== false)
			$arFilter["MODULE_ID"] = $module_id;
		if ($binding !== false)
			$arFilter["BINDING"] = $binding;

		$z = CTask::GetList(
			Array(
				"MODULE_ID" => "asc",
				"LETTER" => "asc"
			),
			$arFilter
		);

		$arr = Array();
		if ($mode)
		{
			while($r = $z->Fetch())
			{
				if (!is_array($arr[$r['MODULE_ID']]))
					$arr[$r['MODULE_ID']] = Array('reference_id'=>Array(),'reference'=>Array());

				$arr[$r['MODULE_ID']]['reference_id'][] = $r['ID'];
				$arr[$r['MODULE_ID']]['reference'][] = '['.($r['LETTER'] ? $r['LETTER'] : '..').'] '.CTask::GetLangTitle($r['NAME']);
			}
		}
		else
		{
			while($r = $z->Fetch())
			{
				if (!is_array($arr[$r['MODULE_ID']]))
					$arr[$r['MODULE_ID']] = Array();

				$arr[$r['MODULE_ID']][] = $r;
			}
		}
		return $arr;
	}

	function GetByID($ID)
	{
		return CTask::GetList(Array(), Array("ID" => intval($ID)));
	}

	function GetLangTitle($name)
	{
		if (strlen(GetMessage('TASK_NAME_'.strtoupper($name))))
			return GetMessage('TASK_NAME_'.strtoupper($name));
		return $name;
	}

	function GetLangDescription($name, $desc)
	{
		if (strlen(GetMessage('TASK_DESC_'.strtoupper($name))))
			return GetMessage('TASK_DESC_'.strtoupper($name));
		return $desc;
	}

	function GetLetter($ID)
	{
		$z = CTask::GetById($ID);
		if ($r = $z->Fetch())
			if ($r['LETTER'])
				return $r['LETTER'];
		return false;
	}

	function GetIdByLetter($letter, $module, $binding='module')
	{
		global $DB;
		static $TASK_LETTER_CACHE = Array();
		if (!$letter)
			return false;

		if (!isset($TASK_LETTER_CACHE))
			$TASK_LETTER_CACHE = Array();

		$k = strtoupper($letter.'_'.$module.'_'.$binding);
		if (isset($TASK_LETTER_CACHE[$k]))
			return $TASK_LETTER_CACHE[$k];

		$z = CTask::GetList(
			Array(),
			Array(
				"LETTER" => $letter,
				"MODULE_ID" => $module,
				"BINDING" => $binding,
				"SYS"=>"Y"
			)
		);

		if ($r = $z->Fetch())
		{
			$TASK_LETTER_CACHE[$k] = $r['ID'];
			if ($r['ID'])
				return $r['ID'];
		}

		return false;
	}
}

class CAllOperation
{
	function err_mess()
	{
		return "<br>Class: CAllOperation<br>File: ".__FILE__;
	}

	function GetList($arOrder = Array('MODULE_ID'=>'asc'),$arFilter=Array())
	{
		global $DB, $USER;

		static $arFields = array(
			"ID" => Array("FIELD_NAME" => "O.ID", "FIELD_TYPE" => "int"),
			"NAME" => Array("FIELD_NAME" => "O.NAME", "FIELD_TYPE" => "string"),
			"MODULE_ID" => Array("FIELD_NAME" => "O.MODULE_ID", "FIELD_TYPE" => "string"),
			"BINDING" => Array("FIELD_NAME" => "O.BINDING", "FIELD_TYPE" => "string")
		);

		$err_mess = (CAllOperation::err_mess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = array();
		$strSqlSearch = "";
		if(is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for($i=0, $l = count($filter_keys); $i<$l; $i++)
			{
				$n = strtoupper($filter_keys[$i]);
				$val = $arFilter[$filter_keys[$i]];
				if(strlen($val)<=0 || strval($val)=="NOT_REF")
					continue;

				if ($n == 'ID')
					$arSqlSearch[] = GetFilterQuery("O.ID",$val,'N');
				elseif(isset($arFields[$n]))
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"],$val);
			}
		}

		$strOrderBy = '';
		foreach($arOrder as $by=>$order)
			if(isset($arFields[strtoupper($by)]))
				$strOrderBy .= $arFields[strtoupper($by)]["FIELD_NAME"].' '.(strtolower($order)=='desc'?'desc'.(strtoupper($DB->type)=="ORACLE"?" NULLS LAST":""):'asc'.(strtoupper($DB->type)=="ORACLE"?" NULLS FIRST":"")).',';

		if(strlen($strOrderBy)>0)
			$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT *
			FROM
				b_operation O
			WHERE
				$strSqlSearch
			$strOrderBy";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	function GetAllowedModules()
	{
		global $DB;
		$sql_str = 'SELECT DISTINCT O.MODULE_ID FROM b_operation O';
		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = Array();
		while($r = $z->Fetch())
			$arr[] = $r['MODULE_ID'];
		return $arr;
	}

	function GetBindingList()
	{
		global $DB;
		$sql_str = 'SELECT DISTINCT O.BINDING FROM b_operation O';
		$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$arr = Array();
		while($r = $z->Fetch())
			$arr[] = $r['BINDING'];
		return $arr;
	}



	function GetIDByName($name)
	{
		$err_mess = (CAllOperation::err_mess())."<br>Function: GetIDByName<br>Line: ";
		$z = COperation::GetList(Array('MODULE_ID' => 'asc'), Array("NAME" => $name));
		if ($r = $z->Fetch())
			return $r['ID'];
		return false;
	}


	function GetLangTitle($name)
	{
		if (strlen(GetMessage('OP_NAME_'.strtoupper($name))) > 0)
			return GetMessage('OP_NAME_'.strtoupper($name));
		return $name;
	}

	function GetLangDescription($name,$desc)
	{
		if (strlen(GetMessage('OP_DESC_'.strtoupper($name))) > 0)
			return GetMessage('OP_DESC_'.strtoupper($name));
		return $desc;
	}
}
?>