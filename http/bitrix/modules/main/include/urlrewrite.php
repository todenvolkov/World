<?
error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");

//try to fix REQUEST_URI under IIS
$aProtocols = array('http', 'https');
foreach($aProtocols as $prot)
{
	$marker = "404;".$prot."://";
	if(($p = strpos($_SERVER["QUERY_STRING"], $marker)) !== false)
	{
		$uri = $_SERVER["QUERY_STRING"];
		if(($p = strpos($uri, "/", $p+strlen($marker))) !== false)
		{
			if($_SERVER["REQUEST_URI"] == '' || $_SERVER["REQUEST_URI"] == '/404.php' || strpos($_SERVER["REQUEST_URI"], $marker) !== false)
				$_SERVER["REQUEST_URI"] = $REQUEST_URI = substr($uri, $p);
			$_SERVER["REDIRECT_STATUS"] = '404';
			$_SERVER["QUERY_STRING"] = $QUERY_STRING = "";
			$_GET = array();
			break;
		}
	}
}

if (!defined("AUTH_404"))
	define("AUTH_404", "Y");

$arUrlRewrite = array();
if(file_exists($_SERVER['DOCUMENT_ROOT']."/urlrewrite.php"))
	include($_SERVER['DOCUMENT_ROOT']."/urlrewrite.php");

if(isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] == '404' || isset($_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"]))
{
	if(isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] == '404')
		$url = $_SERVER["REQUEST_URI"];
	else
		$url = $_SERVER["REQUEST_URI"] = $REQUEST_URI = (is_array($_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"])? '':$_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"]);

	if(($pos=strpos($url, "?"))!==false)
	{
		$params = substr($url, $pos+1);
		parse_str($params, $vars);

		$_GET += $vars;
		$_REQUEST += $vars;
		$GLOBALS += $vars;
		$_SERVER["QUERY_STRING"] = $QUERY_STRING = $params;
	}

	$HTTP_GET_VARS=$_GET;
	$sUrlPath = GetPagePath();
	$strNavQueryString = DeleteParam(array("SEF_APPLICATION_CUR_PAGE_URL"));
	if($strNavQueryString != "")
		$sUrlPath = $sUrlPath."?".$strNavQueryString;
	define("POST_FORM_ACTION_URI",htmlspecialchars("/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL=".urlencode($sUrlPath)));
}

foreach($arUrlRewrite as $val)
{
	if(preg_match($val["CONDITION"], $_SERVER["REQUEST_URI"]))
	{
		if (strlen($val["RULE"]) > 0)
			$url = preg_replace($val["CONDITION"], (strlen($val["PATH"]) > 0 ? $val["PATH"]."?" : "").$val["RULE"], $_SERVER["REQUEST_URI"]);
		else
			$url = $val["PATH"];

		if(($pos=strpos($url, "?"))!==false)
		{
			$params = substr($url, $pos+1);
			parse_str($params, $vars);

			$_GET += $vars;
			$_REQUEST += $vars;
			$GLOBALS += $vars;
			$_SERVER["QUERY_STRING"] = $QUERY_STRING = $params;
			$url = substr($url, 0, $pos);
		}
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].$url) || !is_file($_SERVER['DOCUMENT_ROOT'].$url))
			continue;

		CHTTP::SetStatus("200 OK");

		$_SERVER["REAL_FILE_PATH"] = $url;

		include_once($_SERVER['DOCUMENT_ROOT'].$url);

		die();
	}
}
?>