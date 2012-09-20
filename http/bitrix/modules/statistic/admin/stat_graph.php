<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$width = intval($_GET["width"]);
$max_width = COption::GetOptionInt("statistic", "GRAPH_WEIGHT");
if($width <= 0 || $width > $max_width)
	$width = $max_width;

$height = intval($_GET["height"]);
$max_height = COption::GetOptionInt("statistic", "GRAPH_HEIGHT");
if($height <= 0 || $height > $max_height)
	$height = $max_height;

// create image canvas
$ImageHandle = CreateImageHandle($width, $height);

$arrX=Array();
$arrY=Array();
$arrayX=Array();
$arrayY=Array();

/******************************************************
                Plot data
*******************************************************/
$site_filtered = (strlen($find_site_id)>0 && $find_site_id!="NOT_REF") ? true : false;
$arFilter = Array(
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2,
	"SITE_ID"	=> $find_site_id
	);

$dynamic = CStat::GetDynamicList(($by="s_date"), ($order="asc"), $arMaxMin, $arFilter, $is_filtered);
while($arData = $dynamic->Fetch())
{
	$date = mktime(0, 0, 0, $arData["MONTH"], $arData["DAY"], $arData["YEAR"]);

	$date_tmp = 0;
	// dates flow has gaps
	$next_date = AddTime($prev_date,1,"D");
	if(($date > $next_date) && (intval($prev_date) > 0))
	{
		// fill it
		$date_tmp = $next_date;
		while($date_tmp < $date)
		{
			$arrX[] = $date_tmp;
			if ($find_hits=="Y") $arrY_hits[] = 0;
			if ($find_hosts=="Y") $arrY_hosts[] = 0;
			if ($find_sessions=="Y") $arrY_sessions[] = 0;
			if ($find_events=="Y") $arrY_events[] = 0;
			if (!$site_filtered)
			{
				if ($find_guests=="Y") $arrY_guests[] = 0;
				if ($find_new_guests=="Y") $arrY_new_guests[] = 0;
			}
			$date_tmp = AddTime($date_tmp, 1, "D");
		}
	}
	$arrX[] = $date;
	if ($find_hits=="Y") $arrY_hits[] = intval($arData["HITS"]);
	if ($find_hosts=="Y") $arrY_hosts[] = intval($arData["C_HOSTS"]);
	if ($find_sessions=="Y") $arrY_sessions[] = intval($arData["SESSIONS"]);
	if ($find_events=="Y") $arrY_events[] = intval($arData["C_EVENTS"]);
	if (!$site_filtered)
	{
		if ($find_guests=="Y") $arrY_guests[] = intval($arData["GUESTS"]);
		if ($find_new_guests=="Y") $arrY_new_guests[] = intval($arData["NEW_GUESTS"]);
	}
	$prev_date = $date;
}

/******************************************************
                 axis X
*******************************************************/
$arrayX = GetArrayX($arrX, $MinX, $MaxX);

/******************************************************
                 axis Y
*******************************************************/
$arrY = array();
if ($find_hits=="Y")		$arrY = array_merge($arrY,$arrY_hits);
if ($find_hosts=="Y")		$arrY = array_merge($arrY,$arrY_hosts);
if ($find_sessions=="Y")	$arrY = array_merge($arrY,$arrY_sessions);
if ($find_events=="Y")		$arrY = array_merge($arrY,$arrY_events);
if (!$site_filtered)
{
	if ($find_guests=="Y")		$arrY = array_merge($arrY,$arrY_guests);
	if ($find_new_guests=="Y")	$arrY = array_merge($arrY,$arrY_new_guests);
}
$arrayY = GetArrayY($arrY, $MinY, $MaxY);

//EchoGraphData($arrayX, $MinX, $MaxX, $arrayY, $MinY, $MaxY, $arrX, $arrY);

/******************************************************
                Draw grid
*******************************************************/

DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);


/******************************************************
                     Plot
*******************************************************/

if ($find_hits=="Y")
	Graf($arrX, $arrY_hits, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["HITS"]);

if ($find_hosts=="Y")
	Graf($arrX, $arrY_hosts, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["HOSTS"]);

if ($find_sessions=="Y")
	Graf($arrX, $arrY_sessions, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["SESSIONS"]);

if ($find_events=="Y")
	Graf($arrX, $arrY_events, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["EVENTS"]);

if (!$site_filtered)
{
	if ($find_guests=="Y")
		Graf($arrX, $arrY_guests, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["GUESTS"]);

	if ($find_new_guests=="Y")
		Graf($arrX, $arrY_new_guests, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["NEW_GUESTS"]);
}

/******************************************************
                send to client
*******************************************************/

ShowImageHeader($ImageHandle);
?>