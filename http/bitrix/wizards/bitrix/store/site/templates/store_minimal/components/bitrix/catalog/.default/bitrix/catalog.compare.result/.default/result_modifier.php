<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult["PROP_ROWS"] = array();
foreach($arParams["PROPERTY_CODE"] as $key => $propCode)
{
	if(empty($arResult["SHOW_PROPERTIES"][$propCode]["ID"]) && empty($arResult["DELETED_PROPERTIES"][$propCode]["ID"]))
	{
		unset($arParams["PROPERTY_CODE"][$key]);
		unset($arResult["SHOW_PROPERTIES"][$propCode]);
	}
}
while(count($arParams["PROPERTY_CODE"])>0)
{
	$arRow = array_splice($arParams["PROPERTY_CODE"], 0, 3);
	while(count($arRow) < 3)
		$arRow[]=false;
	$arResult["PROP_ROWS"][]=$arRow;
}

foreach ($arResult['ITEMS'] as $key => $arElement)
{
	if(is_array($arElement["FIELDS"]["DETAIL_PICTURE"]))
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arElement["FIELDS"]['DETAIL_PICTURE'],
			array("width" => 75, 'height' => 225),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			false
		);
		$arSize = getimagesize($_SERVER["DOCUMENT_ROOT"].$arFileTmp["src"]);
		

		$arResult['ITEMS'][$key]["FIELDS"]["DETAIL_PICTURE"]['PREVIEW_IMG'] = array(
			'SRC' => $arFileTmp["src"],
			'WIDTH' => IntVal($arSize[0]),
			'HEIGHT' => IntVal($arSize[1]),
		);
	}
}

?>