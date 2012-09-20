<?
define("START_EXEC_EPILOG_BEFORE_1", microtime());
$GLOBALS["BX_STATE"] = "EB";

if(method_exists($APPLICATION, "ShowSpreadCookieHTML")) //for BitrixUpdate to 4.0
	$APPLICATION->ShowSpreadCookieHTML();

if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1)
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/epilog_main_admin.php");
else
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/epilog_jspopup_admin.php");

?>