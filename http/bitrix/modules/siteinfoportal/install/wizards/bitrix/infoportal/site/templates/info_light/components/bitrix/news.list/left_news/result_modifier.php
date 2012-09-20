<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
foreach ($arResult["ITEMS"] as $key => $arItem) {
	if(is_array($arItem["PREVIEW_PICTURE"]))
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arItem["PREVIEW_PICTURE"],
			array("width" => $arParams["DISPLAY_IMG_WIDTH"], "height" => $arParams["DISPLAY_IMG_HEIGHT"]),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			false
		);
		$arSize = getimagesize($_SERVER["DOCUMENT_ROOT"].$arFileTmp["src"]);

		$arResult["ITEMS"][$key]["PREVIEW_IMG_SMALL"] = array(
			"SRC" => $arFileTmp["src"],
			"WIDTH" => IntVal($arSize[0]),
			"HEIGHT" => IntVal($arSize[1]),
		);
	}
	
}
?>