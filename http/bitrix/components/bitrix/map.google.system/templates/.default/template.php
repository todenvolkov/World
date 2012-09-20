<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arAllMapOptions = array_merge($arResult['ALL_MAP_OPTIONS'], $arResult['ALL_MAP_CONTROLS']);
$arMapOptions = array_merge($arParams['OPTIONS'], $arParams['CONTROLS']);
?>
<script type="text/javascript">
if (!window.GLOBAL_arMapObjects)
	window.GLOBAL_arMapObjects = {};

function init_<?echo $arParams['MAP_ID']?>() 
{
	if (!window.google && !window.google.maps)
		return;
	
	var opts = {
		zoom: <?echo $arParams['INIT_MAP_SCALE']?>,
		center: new google.maps.LatLng(<?echo $arParams['INIT_MAP_LAT']?>, <?echo $arParams['INIT_MAP_LON']?>),
<?
foreach ($arAllMapOptions as $option => $method)
{
	
	echo "\t\t".(
		in_array($option, $arMapOptions) 
		? str_replace(array('#true#', '#false#'), array('true', 'false'), $method) 
		: str_replace(array('#false#', '#true#'), array('true', 'false'), $method)
	).",\r\n";
}
?>

		mapTypeId: google.maps.MapTypeId.<?echo $arParams['INIT_MAP_TYPE']?>

	}
	
	window.GLOBAL_arMapObjects['<?echo $arParams['MAP_ID']?>'] = new window.google.maps.Map(BX("BX_GMAP_<?echo $arParams['MAP_ID']?>"), opts);

<?
if ($arParams['DEV_MODE'] == 'Y'):
?>
	window.bGoogleMapScriptsLoaded = true;
<?
endif;
?>
}
<?
if ($arParams['DEV_MODE'] == 'Y'):
?>
function BXMapLoader_<?echo $arParams['MAP_ID']?>(MAP_KEY)
{
	if (null == window.bGoogleMapScriptsLoaded)
	{
		if (window.google && window.google.maps)
		{
			window.bGoogleMapScriptsLoaded = true;
			BX.ready(init_<?echo $arParams['MAP_ID']?>);
		}
		else
		{
			BX.loadScript(
				'http://www.google.com/jsapi?rnd=' + Math.random(), 
				function () {
					if (BX.browser.IsIE())
						setTimeout("window.google.load('maps', <?echo intval($arParams['GOOGLE_VERSION'])?>, {callback: init_<?echo $arParams['MAP_ID']?>, other_params: 'sensor=false&language=<?=LANGUAGE_ID?>'})", 1000);
					else
						google.load('maps', <?echo intval($arParams['GOOGLE_VERSION'])?>, {callback: init_<?echo $arParams['MAP_ID']?>, other_params: 'sensor=false&language=<?=LANGUAGE_ID?>'});
				}
			);
		}
	}
	else
	{
		init_<?echo $arParams['MAP_ID']?>();
	}
}
<?
	if (!$arParams['WAIT_FOR_EVENT']):
?>
BXMapLoader_<?echo $arParams['MAP_ID']?>('<?echo $arParams['KEY']?>');
<?
	else:
		echo CUtil::JSEscape($arParams['WAIT_FOR_EVENT']),' = BXMapLoader_',$arParams['MAP_ID'],';';
	endif;
else:
?>
BX.ready(init_<?echo $arParams['MAP_ID']?>);
<?
endif;
?>
</script>
<div id="BX_GMAP_<?echo $arParams['MAP_ID']?>" class="bx-google-map" style="height: <?echo $arParams['MAP_HEIGHT'];?>; width: <?echo $arParams['MAP_WIDTH']?>;"><?echo GetMessage('MYS_LOADING'.($arParams['WAIT_FOR_EVENT'] ? '_WAIT' : ''));?></div>