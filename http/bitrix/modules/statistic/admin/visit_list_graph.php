<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/admin/body/".basename($APPLICATION->GetCurPage(),"_graph.php")."_1_".$ID.".php");

$width = intval($_GET["width"]);
$max_width = COption::GetOptionInt("statistic", "GRAPH_WEIGHT");
if($width <= 0 || $width > $max_width)
	$width = $max_width;

$height = intval($_GET["height"]);
$max_height = COption::GetOptionInt("statistic", "GRAPH_HEIGHT");
if($height <= 0 || $height > $max_height)
	$height = $max_height;

$ImageHandle = CreateImageHandle($width,$height);

$arrX=Array();
$arrY=Array();
$arrayX=Array();
$arrayY=Array();

/******************************************************
                Plot data
*******************************************************/

while($arData = $visitors->Fetch())
{
	$date = mktime(0, 0, 0, $arData["MONTH"], $arData["DAY"], $arData["YEAR"]);
	$date_tmp = 0;
	// when dates with gaps or misplaced
	if(($date > ($prev_date+86400)) && (intval($prev_date) > 0))
	{
		// fill gaps with no data
		$date_tmp = $prev_date+86400;
		while($date_tmp < $date)
		{
			$arrX[] = $date_tmp;
			$arrY[] = 0;
			$arrY_b[] = 0;
			$date_tmp += 86400;
		}
	}
	$arrX[] = $date;
	$arrY[] = $arData["ALL_GUESTS"];
	$arrY_b[] = $arData["NEW_GUESTS"];
	$prev_date = $date;
}
$arrX = array_reverse($arrX);
$arrY = array_reverse($arrY);
$arrY_b = array_reverse($arrY_b);

/******************************************************
                   axis X (dates)
*******************************************************/
$arrayX = GetArrayX($arrX, $MinX, $MaxX, 15);

/******************************************************
                 axis Y (values)
*******************************************************/
$arrayY = GetArrayY(array_merge($arrY, $arrY_b), $MinY, $MaxY, 15);

//EchoGraphData($arrayX, $MinX, $MaxX, $arrayY, $MinY, $MaxY, $arrX, $arrY)

if (sizeof($arrayX)<=1 || sizeof($arrayY)<=1) return;

/******************************************************
                Draw grid
*******************************************************/

ImagesCoordinat("","",$arrayX,$arrayY,$width,$height,$ImageHandle,"",$MinX,$MaxX,$MinY,$MaxY,'FFFFFF','FFFFFF','000000',2,(strlen($arrayY[sizeof($arrayY)-1]))*3*$k[2]/2);

/******************************************************
                     Plot
*******************************************************/

Graf($arrX,$arrY,$width,$height,$ImageHandle,'A8A8A8',$MinX,$MaxX,$MinY,$MaxY, (strlen($arrayY[sizeof($arrayY)-1]))*3*$k[2]/2);
Graf($arrX,$arrY_b,$width,$height,$ImageHandle,'2D79E1',$MinX,$MaxX,$MinY,$MaxY, (strlen($arrayY[sizeof($arrayY)-1]))*3*$k[2]/2);

/******************************************************
                Send to client
*******************************************************/

ShowImageHeader($ImageHandle);
?>