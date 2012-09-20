<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
foreach ($arResult["ITEMS"] as $key => $arItem) {
	$res = CIBlockSection::GetList("", array("ID" => $arItem['IBLOCK_SECTION_ID']), false, false, array("SECTION_PAGE_URL", "NAME"));
	if($ar_res = $res->GetNext()){
		$arResult["ITEMS"][$key]["SECTION_URL"] = '<a href="'.$ar_res["SECTION_PAGE_URL"] .'">'.$ar_res["NAME"].'</a>';
	}
	if(is_array($arItem["PREVIEW_PICTURE"]))
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arItem["PREVIEW_PICTURE"],
			array("width" => $arParams["DISPLAY_IMG_WIDTH"], "height" => $arParams["DISPLAY_IMG_HEIGHT"]),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			false
		);
		$arSize = getimagesize($_SERVER["DOCUMENT_ROOT"].$arFileTmp["src"]);

		$arResult["ITEMS"][$key]["PREVIEW_IMG_MEDIUM"] = array(
			"SRC" => $arFileTmp["src"],
			"WIDTH" => IntVal($arSize[0]),
			"HEIGHT" => IntVal($arSize[1]),
		);
	}
	
}
?>