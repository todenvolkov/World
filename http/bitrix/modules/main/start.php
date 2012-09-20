<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);

require_once(substr(__FILE__, 0, strlen(__FILE__) - strlen("/start.php"))."/bx_root.php");

function getmicrotime()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

define("START_EXEC_TIME", getmicrotime());
define("B_PROLOG_INCLUDED", true);

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/version.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/tools.php");

if(version_compare(PHP_VERSION, "5.0.0")>=0 && @ini_get_bool("register_long_arrays") != true)
{
	$HTTP_POST_FILES  = $_FILES;
	$HTTP_SERVER_VARS = $_SERVER;
	$HTTP_GET_VARS = $_GET;
	$HTTP_POST_VARS = $_POST;
	$HTTP_COOKIE_VARS = $_COOKIE;
	$HTTP_ENV_VARS = $_ENV;
}

UnQuoteAll();
FormDecode();

//read database connection parameters require_once
require_once($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php");

if(defined('BX_UTF'))
	define('BX_UTF_PCRE_MODIFIER', 'u');
else
	define('BX_UTF_PCRE_MODIFIER', '');

if(!defined("CACHED_b_lang")) define("CACHED_b_lang", 3600);
if(!defined("CACHED_b_option")) define("CACHED_b_option", 3600);
if(!defined("CACHED_b_lang_domain")) define("CACHED_b_lang_domain", 3600);
if(!defined("CACHED_b_site_template")) define("CACHED_b_site_template", 3600);
if(!defined("CACHED_b_event")) define("CACHED_b_event", 3600);
if(!defined("CACHED_b_agent")) define("CACHED_b_agent", 3660);
if(!defined("CACHED_menu")) define("CACHED_menu", 3600);
if(!defined("CACHED_b_file")) define("CACHED_b_file", false);
if(!defined("CACHED_b_file_bucket_size")) define("CACHED_b_file_bucket_size", 100);
if(!defined("CACHED_b_user_field")) define("CACHED_b_user_field", 3600);
if(!defined("CACHED_b_user_field_enum")) define("CACHED_b_user_field_enum", 3600);
if(!defined("CACHED_b_task")) define("CACHED_b_task", 3600);
if(!defined("CACHED_b_task_operation")) define("CACHED_b_task_operation", 3600);
if(!defined("CACHED_b_rating")) define("CACHED_b_rating", 3600);

//connect to database, from here global variable $DB is available (CDatabase class)
if(isset($DBSQLServerType) && $DBSQLServerType == "NATIVE")
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/database_ms.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/database.php");

$GLOBALS["DB"] = new CDatabase;
$GLOBALS["DB"]->debug = $DBDebug;
$GLOBALS["DB"]->DebugToFile = $DBDebugToFile;

//magic parameters: show sql queries statistics
$show_sql_stat = "";
if(array_key_exists("show_sql_stat", $_GET))
{
	$show_sql_stat = (strtoupper($_GET["show_sql_stat"]) == "Y"? "Y":"");
	setcookie("show_sql_stat", $show_sql_stat, false, "/");
}
elseif(array_key_exists("show_sql_stat", $_COOKIE))
{
	$show_sql_stat = $_COOKIE["show_sql_stat"];
}
$GLOBALS["DB"]->ShowSqlStat = ($show_sql_stat == "Y");

if(!defined("POST_FORM_ACTION_URI"))
{
	define("POST_FORM_ACTION_URI", htmlspecialchars($_SERVER["REQUEST_URI"]));
}
if(!($GLOBALS["DB"]->Connect($DBHost, $DBName, $DBLogin, $DBPassword)))
{
	if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn_error.php"))
		include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn_error.php");
	else
		include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/dbconn_error.php");
	die();
}

//language independed classes
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/main.php");	//main class
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/option.php");	//options and settings class
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/cache.php");	//various cache classes
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/cache_html.php");	//html cache class support

error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);

if (file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_db_updater.php"))
{
	$US_HOST_PROCESS_MAIN = True;
	include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_db_updater.php");
}
?>