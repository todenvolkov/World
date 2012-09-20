<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams['KEY'] = trim($arParams['KEY']);

if (!$arParams['KEY'])
{
	$MAP_KEY = '';
	$strMapKeys = COPtion::GetOptionString('fileman', 'map_yandex_keys');

	$strDomain = $_SERVER['HTTP_HOST'];
	$wwwPos = strpos($strDomian, 'www.');
	if ($wwwPos === 0)
		$strDomain = substr($strDomain, 4);

	if ($strMapKeys)
	{
		$arMapKeys = unserialize($strMapKeys);
		
		if (array_key_exists($strDomain, $arMapKeys))
			$MAP_KEY = $arMapKeys[$strDomain];
	}
	
	if (!$MAP_KEY)
	{
		ShowError(GetMessage('MYMS_ERROR_NO_KEY'));
		return;
	}
	else
		$arParams['KEY'] = $MAP_KEY;
}

$arParams['MAP_ID'] = 
	(strlen($arParams["MAP_ID"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["MAP_ID"])) ? 
	'MAP_'.RandString() : $arParams['MAP_ID']; 

$current_search = $_GET['ys'];

if (($strPositionInfo = $arParams['~MAP_DATA']) && CheckSerializedData($strPositionInfo) && ($arResult['POSITION'] = unserialize($strPositionInfo)))
{
	$arParams['INIT_MAP_LON'] = $arResult['POSITION']['yandex_lon'];
	$arParams['INIT_MAP_LAT'] = $arResult['POSITION']['yandex_lat'];
	$arParams['INIT_MAP_SCALE'] = $arResult['POSITION']['yandex_scale'];
}

$this->IncludeComponentTemplate();
?>