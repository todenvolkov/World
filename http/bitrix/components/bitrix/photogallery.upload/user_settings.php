<?define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
// **************************************************************************************
if(!function_exists("__UnEscape"))
{
	function __UnEscape(&$item, $key)
	{
		if(is_array($item))
			array_walk($item, '__UnEscape');
		elseif (preg_match("/^.{1}/su", $item) == 1)
			$item = $GLOBALS["APPLICATION"]->ConvertCharset($item, "UTF-8", SITE_CHARSET);
	}
}

array_walk($_REQUEST, '__UnEscape');
if (check_bitrix_sessid() && $GLOBALS["USER"]->IsAuthorized())
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");

	$UploadViewMode = @unserialize(CUserOptions::GetOption("photogallery", "UploadViewMode", ""));
	if (!is_array($UploadViewMode))
		$UploadViewMode = array();
	if ($_REQUEST["save"] == "view_mode")
	{
		if ($_REQUEST["view_mode"] == "form"):
			$UploadViewMode["view_mode"] = "form";
		elseif ($_REQUEST["view_mode"] == "applet"):
			$UploadViewMode["view_mode"] = "applet";
		else:
			$UploadViewMode["view_mode"] = ($UploadViewMode["view_mode"] == "applet" ? "form" : "applet");
			$UploadViewMode["view_mode"] = (empty($UploadViewMode["view_mode"]) ? "applet" : $UploadViewMode["view_mode"]);
		endif;
		$UploadViewMode["view_mode"] = ($UploadViewMode["view_mode"] == "applet" ? "applet" : "form");
		CUserOptions::SetOption("photogallery", "UploadViewMode", serialize($UploadViewMode));
	}
	elseif ($UploadViewMode[$_REQUEST["save"]] != $_REQUEST["position"])
	{
		$UploadViewMode[$_REQUEST["save"]] = $_REQUEST["position"];
		CUserOptions::SetOption("photogallery", "UploadViewMode", serialize($UploadViewMode));
	}
}
?>