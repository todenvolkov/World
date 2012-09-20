<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once(substr(__FILE__, 0, strlen(__FILE__) - strlen("/include.php"))."/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/start.php");
//определяем язык
$GLOBALS["APPLICATION"] = new CMain;

if(defined("SITE_ID"))
	define("LANG", SITE_ID);

if(defined("LANG"))
{
	if(defined("ADMIN_SECTION") && ADMIN_SECTION===true)
		$db_lang = CLangAdmin::GetByID(LANG);
	else
		$db_lang = CLang::GetByID(LANG);

	$arLang = $db_lang->Fetch();
}
else
{
	$arLang = $GLOBALS["APPLICATION"]->GetLang(); //определим переменную lang будто она пришла от пользователя (если действительно не пришла)
	define("LANG", $arLang["LID"]);
}

$lang = $arLang["LID"];
define("SITE_ID", $arLang["LID"]);
define("SITE_DIR", $arLang["DIR"]);
define("SITE_SERVER_NAME", $arLang["SERVER_NAME"]);
define("SITE_CHARSET", $arLang["CHARSET"]);
define("FORMAT_DATE", $arLang["FORMAT_DATE"]);
define("FORMAT_DATETIME", $arLang["FORMAT_DATETIME"]);
define("LANG_DIR", $arLang["DIR"]);
define("LANG_CHARSET", $arLang["CHARSET"]);
define("LANG_ADMIN_LID", $arLang["LANGUAGE_ID"]);
define("LANGUAGE_ID", $arLang["LANGUAGE_ID"]);

error_reporting(COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE));

if(!defined("BX_COMP_MANAGED_CACHE") && COption::GetOptionString("main", "component_managed_cache_on", "Y") <> "N")
	define("BX_COMP_MANAGED_CACHE", true);

if($domain = $GLOBALS["APPLICATION"]->GetCookieDomain())
	ini_set("session.cookie_domain", $domain);

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/main.php");

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/filter_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/ajax_tools.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/database.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/tools.php");


			class CBXFeatures
			{
				public static function IsFeatureEnabled($featureId)
				{
					return true;
				}

				public static function IsFeatureEditable($featureId)
				{
					return true;
				}

				public static function SetFeatureEnabled($featureId, $bEnabled = true)
				{
				}

				public static function SaveFeaturesSettings($arEnabledEditions, $arEnabledFeatures)
				{
				}

				public static function GetFeaturesList()
				{
					return array();
				}
			}
						//Do not remove this

/***************************/
$GLOBALS["arCustomTemplateEngines"] = array();
/***************************/

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/urlrewriter.php");

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/module.php"); //подключаемые модули
CModule::AddAutoloadClasses(
	"main",
	array(
		"CBitrixComponent" => "classes/general/component.php",
		"CComponentEngine" => "classes/general/component_engine.php",
		"CComponentAjax" => "classes/general/component_ajax.php",
		"CBitrixComponentTemplate" => "classes/general/component_template.php",
		"CComponentUtil" => "classes/general/component_util.php",
		"CControllerClient" => "classes/general/controller_member.php",
		"PHPParser" => "classes/general/php_parser.php",
		"CDiskQuota" => "classes/".$DBType."/quota.php",
		"CEventLog" => "classes/general/event_log.php",
		"CAdminFileDialog" => "classes/general/file_dialog.php",
		"WLL_User" => "classes/general/liveid.php",
		"WLL_ConsentToken" => "classes/general/liveid.php",
		"WindowsLiveLogin" => "classes/general/liveid.php",
		"COpenIDClient" => "classes/general/openid.php",
		"CAllFile" => "classes/general/file.php",
		"CFile" => "classes/".$DBType."/file.php",
		"CFavorites" => "classes/general/favorites.php",
		"CUserOptions" => "classes/general/favorites.php",
		"CGridOptions" => "classes/general/grids.php",
		"CUndo" => "/classes/general/undo.php",
 		"CRatings" => "classes/".$DBType."/ratings.php",
		"CRatingsComponentsMain" => "classes/".$DBType."/ratings_components.php",
		"CRatingRule" => "classes/general/rating_rule.php",
		"CRatingRulesMain" => "classes/general/rating_rules.php",
		"CTopPanel" => "public/top_panel.php",
		"CEditArea" => "public/edit_area.php",
		"CComponentPanel" => "public/edit_area.php",
	)
);

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/agent.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/user.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/event.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/menu.php");
AddEventHandler("main", "OnAfterEpilog", array("CCacheManager", "_Finalize"));
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/usertype.php");

if (file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_db_updater.php"))
{
	$US_HOST_PROCESS_MAIN = False;
	include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_db_updater.php");
}

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/init.php"))
	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/init.php");

if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/init.php"))
	include_once($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/init.php");

if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".SITE_ID."/init.php"))
	include_once($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".SITE_ID."/init.php");

if(!defined("BX_FILE_PERMISSIONS"))
	define("BX_FILE_PERMISSIONS", 0777);
if(!defined("BX_DIR_PERMISSIONS"))
	define("BX_DIR_PERMISSIONS", 0777);

//global var, is used somewhere
$GLOBALS["sDocPath"] = $GLOBALS["APPLICATION"]->GetCurPage();

if((!(defined("STATISTIC_ONLY") && STATISTIC_ONLY && substr($GLOBALS["APPLICATION"]->GetCurPage(), 0, strlen(BX_ROOT."/admin/"))!=BX_ROOT."/admin/")) && COption::GetOptionString("main", "include_charset", "Y")=="Y" && strlen(LANG_CHARSET)>0)
	header("Content-Type: text/html; charset=".LANG_CHARSET);

if(COption::GetOptionString("main", "set_p3p_header", "Y")=="Y")
	header("P3P: policyref=\"/bitrix/p3p.xml\", CP=\"NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA\"");

//licence key
$LICENSE_KEY = "";
if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/license_key.php"))
	include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/license_key.php");
if($LICENSE_KEY == "" || strtoupper($LICENSE_KEY) == "DEMO")
	define("LICENSE_KEY", "DEMO");
else
	define("LICENSE_KEY", $LICENSE_KEY);

header("X-Powered-CMS: Bitrix Site Manager (".(LICENSE_KEY == "DEMO"? "DEMO" : md5("BITRIX".LICENSE_KEY."LICENCE")).")");

define("BX_CRONTAB_SUPPORT", defined("BX_CRONTAB"));

if(COption::GetOptionString("main", "check_agents", "Y")=="Y")
{
	define("START_EXEC_AGENTS_1", microtime());
	$GLOBALS["BX_STATE"] = "AG";
	$GLOBALS["DB"]->StartUsingMasterOnly();
	CAgent::CheckAgents();
	$GLOBALS["DB"]->StopUsingMasterOnly();
	define("START_EXEC_AGENTS_2", microtime());
	$GLOBALS["BX_STATE"] = "PB";
}

if(COption::GetOptionString("security", "session", "N") === "Y"	&& CModule::IncludeModule("security"))
	CSecuritySession::Init();

//diagnostic for spaces in init.php etc.
//message is shown in the admin section
$GLOBALS["aHeadersInfo"] = array();
if(headers_sent($hs_file, $hs_line))
	$GLOBALS["aHeadersInfo"] = array("file"=>$hs_file, "line"=>$hs_line);

session_start();

$db_events = GetModuleEvents("main", "OnPageStart");
while($arEvent = $db_events->Fetch())
	ExecuteModuleEventEx($arEvent);

//define global user object
$GLOBALS["USER"] = new CUser;

//session control from group policy
$arPolicy = $GLOBALS["USER"]->GetSecurityPolicy();
$currTime = time();
if(
	(
		//IP address changed
		$_SESSION['SESS_IP']
		&& strlen($arPolicy["SESSION_IP_MASK"])>0
		&& (
			(ip2long($arPolicy["SESSION_IP_MASK"]) & ip2long($_SESSION['SESS_IP']))
			!=
			(ip2long($arPolicy["SESSION_IP_MASK"]) & ip2long($_SERVER['REMOTE_ADDR']))
		)
	)
	||
	(
		//session timeout
		$arPolicy["SESSION_TIMEOUT"]>0
		&& $_SESSION['SESS_TIME']>0
		&& $currTime-$arPolicy["SESSION_TIMEOUT"]*60 > $_SESSION['SESS_TIME']
	)
	||
	(
		//session expander control
		$_SESSION["BX_SESSION_TERMINATE_TIME"] > 0
		&& $currTime > $_SESSION["BX_SESSION_TERMINATE_TIME"]
	)
)
{
	$_SESSION = array();
	@session_destroy();

	//session_destroy cleans user sesssion handles in some PHP versions
	//see http://bugs.php.net/bug.php?id=32330 discussion
	if(COption::GetOptionString("security", "session", "N") === "Y"	&& CModule::IncludeModule("security"))
		CSecuritySession::Init();

	session_id(md5(uniqid(rand(), true)));
	session_start();
	$GLOBALS["USER"] = new CUser;
}
$_SESSION['SESS_IP'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['SESS_TIME'] = time();

//session control from security module
if(
	(COption::GetOptionString("main", "use_session_id_ttl", "N") == "Y")
	&& (COption::GetOptionInt("main", "session_id_ttl", 0) > 0)
	&& !defined("BX_SESSION_ID_CHANGE")
)
{
	if(!array_key_exists('SESS_ID_TIME', $_SESSION))
	{
		$_SESSION['SESS_ID_TIME'] = $_SESSION['SESS_TIME'];
	}
	elseif(($_SESSION['SESS_ID_TIME'] + COption::GetOptionInt("main", "session_id_ttl")) < $_SESSION['SESS_TIME'])
	{
		if(COption::GetOptionString("security", "session", "N") === "Y" && CModule::IncludeModule("security"))
		{
			CSecuritySession::UpdateSessID();
		}
		else
		{
			session_regenerate_id();
			if(!version_compare(phpversion(),"4.3.3",">="))
				setcookie(session_name(), session_id(), ini_get("session.cookie_lifetime"), "/");
		}
		$_SESSION['SESS_ID_TIME'] = $_SESSION['SESS_TIME'];
	}
}

define("BX_STARTED", true);

if(!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS!==true)
{
	$bLogout = strtolower($_REQUEST["logout"])=="yes";

	if($bLogout && $GLOBALS["USER"]->IsAuthorized())
		$GLOBALS["USER"]->Logout();

	// authorize by cookie
	$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
	$cookie_login = $_COOKIE[$cookie_prefix.'_LOGIN'];
	$cookie_md5pass = $_COOKIE[$cookie_prefix.'_UIDH'];

	if(COption::GetOptionString("main", "store_password", "Y")=="Y"
		&& strlen($cookie_login)>0
		&& strlen($cookie_md5pass)>0
		&& !$GLOBALS["USER"]->IsAuthorized()
		&& !$bLogout
		&& $_SESSION["SESS_PWD_HASH_TESTED"] != md5($cookie_login."|".$cookie_md5pass)
	)
	{
		$GLOBALS["USER"]->LoginByHash($cookie_login, $cookie_md5pass);
		$_SESSION["SESS_PWD_HASH_TESTED"] = md5($cookie_login."|".$cookie_md5pass);
	}

	// Basic Authorization PHP FastCGI (CGI)
	if (!isset($_SERVER['PHP_AUTH_USER']) && (isset($_SERVER['REDIRECT_REMOTE_USER']) || isset($_SERVER['REMOTE_USER'])))
	{
		$res = (isset($_SERVER['REDIRECT_REMOTE_USER']) ? $_SERVER['REDIRECT_REMOTE_USER'] : $_SERVER['REMOTE_USER']);
		if (!empty($res) && preg_match('/(?<=(basic\s))(.*)$/is', $res, $matches))
		{
			$res = trim($matches[0]);
		    list($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"]) = explode(':', base64_decode($res));
            if (strpos($_SERVER["PHP_AUTH_USER"], $_SERVER['HTTP_HOST']."\\") === 0):
                $_SERVER["PHP_AUTH_USER"] = str_replace($_SERVER['HTTP_HOST']."\\", "", $_SERVER["PHP_AUTH_USER"]);
            elseif (strpos($_SERVER["PHP_AUTH_USER"], $_SERVER['SERVER_NAME']."\\") === 0):
                $_SERVER["PHP_AUTH_USER"] = str_replace($_SERVER['SERVER_NAME']."\\", "", $_SERVER["PHP_AUTH_USER"]);
            endif;
		}
	}

	// Authorize user, if it is http standart authorization, with no remembering
	if (isset($_SERVER["PHP_AUTH_USER"]) && (!$GLOBALS["USER"]->IsAuthorized() || $GLOBALS["USER"]->GetLogin() != $_SERVER["PHP_AUTH_USER"]))
	{
		if (strlen($_SERVER["PHP_AUTH_USER"]) > 0 and
			strlen($_SERVER["PHP_AUTH_PW"]) > 0)
		{
			$arAuthResult = $GLOBALS["USER"]->Login($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"], "N");
			$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
		}
	}

	//Authorize user from authorization html form
	$strAuthRes="";
	if(strlen($_REQUEST["AUTH_FORM"])>0)
	{
		if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
			$USER_LID = LANG;
		else
			$USER_LID = false;

		if($_REQUEST["TYPE"]=="AUTH")
		{
			$arAuthResult = $GLOBALS["USER"]->Login($_REQUEST["USER_LOGIN"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_REMEMBER"]);
		}
		elseif($_REQUEST["TYPE"]=="SEND_PWD")
		{
			$arAuthResult = $GLOBALS["USER"]->SendPassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_EMAIL"], $USER_LID);
		}
		elseif($_SERVER['REQUEST_METHOD']=='POST' && $_REQUEST["TYPE"]=="CHANGE_PWD")
		{
			$arAuthResult = $GLOBALS["USER"]->ChangePassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_CHECKWORD"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $USER_LID);
		}
		elseif(COption::GetOptionString("main", "new_user_registration", "N")=="Y" && $_SERVER['REQUEST_METHOD']=='POST' && $_REQUEST["TYPE"]=="REGISTRATION" && (!defined("ADMIN_SECTION") || ADMIN_SECTION!==true))
		{
			$arAuthResult = $GLOBALS["USER"]->Register($_REQUEST["USER_LOGIN"], $_REQUEST["USER_NAME"], $_REQUEST["USER_LAST_NAME"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $_REQUEST["USER_EMAIL"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
		}

		$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
	}
	elseif(!$GLOBALS["USER"]->IsAuthorized())
	{
		//Authorize by unique URL
		$GLOBALS["USER"]->LoginHitByHash();
	}
}

//define the site template
if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
{
	if(array_key_exists("bitrix_preview_site_template", $_REQUEST) && $_REQUEST["bitrix_preview_site_template"] <> "" && $GLOBALS["USER"]->CanDoOperation('view_other_settings'))
	{
		//preview of site template
		$aTemplates = CSiteTemplate::GetByID($_REQUEST["bitrix_preview_site_template"]);
		if($template = $aTemplates->Fetch())
			define("SITE_TEMPLATE_ID", $template["ID"]);
		else
			define("SITE_TEMPLATE_ID", CSite::GetCurTemplate());
	}
	else
		define("SITE_TEMPLATE_ID", CSite::GetCurTemplate());

	define("SITE_TEMPLATE_PATH", BX_PERSONAL_ROOT.'/templates/'.SITE_TEMPLATE_ID);
}

//magic parameters: show page creation time
if($_GET["show_page_exec_time"]=="Y" || $_GET["show_page_exec_time"]=="N")
	$_SESSION["SESS_SHOW_TIME_EXEC"] = $_GET["show_page_exec_time"];

//magic parameters: show included file processing time
if($_GET["show_include_exec_time"]=="Y" || $_GET["show_include_exec_time"]=="N")
	$_SESSION["SESS_SHOW_INCLUDE_TIME_EXEC"] = $_GET["show_include_exec_time"];

//magic parameters: show include areas
if(isset($_GET["bitrix_include_areas"]) && $_GET["bitrix_include_areas"] <> "")
	$GLOBALS["APPLICATION"]->SetShowIncludeAreas($_GET["bitrix_include_areas"]=="Y");

//magic sound
if($GLOBALS["USER"]->IsAuthorized())
{
	$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
	if(!isset($_COOKIE[$cookie_prefix.'_SOUND_LOGIN_PLAYED']))
		$GLOBALS["APPLICATION"]->set_cookie('SOUND_LOGIN_PLAYED', 'Y', 0);
}

$db_events = GetModuleEvents("main", "OnBeforeProlog");
while($arEvent = $db_events->Fetch())
	ExecuteModuleEventEx($arEvent);

if((!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS!==true) && (!defined("NOT_CHECK_FILE_PERMISSIONS") || NOT_CHECK_FILE_PERMISSIONS!==true))
{
	$real_path = $GLOBALS["APPLICATION"]->GetCurPage(true);
	if (isset($_SERVER["REAL_FILE_PATH"]) && $_SERVER["REAL_FILE_PATH"] != "")
		$real_path = $_SERVER["REAL_FILE_PATH"];

	if(!$GLOBALS["USER"]->CanDoFileOperation('fm_view_file', Array(SITE_ID, $real_path)) || (defined("NEED_AUTH") && NEED_AUTH && !$GLOBALS["USER"]->IsAuthorized()))
	{
		if($GLOBALS["USER"]->IsAuthorized() && strlen($arAuthResult["MESSAGE"])<=0)
			$arAuthResult = array("MESSAGE"=>GetMessage("ACCESS_DENIED").' '.GetMessage("ACCESS_DENIED_FILE", array("#FILE#"=>$real_path)), "TYPE"=>"ERROR");

		if(defined("ADMIN_SECTION") && ADMIN_SECTION==true)
		{
			if ($_REQUEST["mode"]=="list" || $_REQUEST["mode"]=="settings")
			{
				echo "<script>window.location='".$GLOBALS["APPLICATION"]->GetCurPage()."?".DeleteParam(array("mode"))."';</script>";
				die();
			}
			elseif ($_REQUEST["mode"]=="frame")
			{
				echo "<script type=\"text/javascript\">
					var w = (opener? opener.window:parent.window);
					w.location='".$GLOBALS["APPLICATION"]->GetCurPage()."?".DeleteParam(array("mode"))."';
				</script>";
				die();
			}
			elseif ($_REQUEST["mode"]=="public")
			{
				require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/popup_auth.php");
				die();
			}
		}

		$GLOBALS["APPLICATION"]->AuthForm($arAuthResult);
	}
}

       //Do not remove this

if(isset($REDIRECT_STATUS) && $REDIRECT_STATUS==404)
{
	if(COption::GetOptionString("main", "header_200", "N")=="Y")
		CHTTP::SetStatus("200 OK");
}
?>