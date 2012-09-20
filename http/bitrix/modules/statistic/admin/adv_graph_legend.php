<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

if (function_exists("FormDecode")) FormDecode();
UnQuoteAll();

// init image
$ImageHandle = CreateImageHandle(45, 2);

$dec=ReColor($color);
$color = ImageColorAllocate($ImageHandle,$dec[0],$dec[1],$dec[2]);
if ($dash=="Y") 
{
	ImageDashedLine ($ImageHandle, 3, 0, 40, 0, $color); 
	ImageDashedLine ($ImageHandle, 3, 1, 40, 1, $color); 
}
else 
{
	ImageLine ($ImageHandle, 3, 0, 40, 0, $color);
	ImageLine ($ImageHandle, 3, 1, 40, 1, $color);
}

/******************************************************
                send image
*******************************************************/

ShowImageHeader($ImageHandle);
?>