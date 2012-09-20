<?$file = $_REQUEST["file"];
$file = preg_replace("#[\\\\\\/]+#", "/", $file);
$file = preg_replace("#\\.+[\\\\\\/]#", "", $file);
if (strpos($file, "/vote/")!==false)
{
	if (strpos($file, "/bitrix/modules/vote/install/templates/vote/")===0 ||
		strpos($file, "/bitrix/templates/")===0) @include($_SERVER["DOCUMENT_ROOT"]."/".$file);
}
?>