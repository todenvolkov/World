<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"INDEX_PAGE_TOP_ELEMENTS_COUNT" => array(
		"NAME" => GetMessage("P_INDEX_PAGE_TOP_ELEMENTS_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => "45")
	);
if ($arCurrentValues["ANALIZE_SOCNET_PERMISSION"] == "Y")
{
	$arTemplateParameters = $arTemplateParameters + array(
		"SHOW_ONLY_PUBLIC" => array(
			"NAME" => GetMessage("P_SHOW_ONLY_PUBLIC"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y", 
			"HIDDEN" => "Y")
	); 
	$arCurrentValues["SHOW_ONLY_PUBLIC"] = "Y"; 
}
else
{
	$arTemplateParameters = $arTemplateParameters + array(
		"SHOW_ONLY_PUBLIC" => array(
			"NAME" => GetMessage("P_SHOW_ONLY_PUBLIC"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N", 
			"REFRESH" => "Y")
	); 
}
	
if ($arCurrentValues["SHOW_ONLY_PUBLIC"] != "N")
{
	if ($arCurrentValues["ANALIZE_SOCNET_PERMISSION"] != "Y")
	{
		$arTemplateParameters["PUBLIC_BY_DEFAULT"] = array(
				"NAME" => GetMessage("P_PUBLIC_BY_DEFAULT"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N"); 
	}
	$arTemplateParameters["MODERATE"] = array(
		"NAME" => GetMessage("P_MODERATE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"); 
}
$arTemplateParameters = $arTemplateParameters + array(
	"USE_LIGHT_TEMPLATE" => array(
		"NAME" => GetMessage("P_USE_LIGHT_TEMPLATE"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "N"), 
	"WATERMARK" => array(
        "NAME" => GetMessage("P_SHOW_WATERMARK"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y"));
		
if ($arCurrentValues["WATERMARK"] != "N"):
$arTemplateParameters = $arTemplateParameters + array(
	"WATERMARK_COLORS" => Array(
		"NAME" => GetMessage("P_WATERMARK_COLORS"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"FF0000" => GetMessage("P_COLOR_FF0000"), 
			"FFA500" => GetMessage("P_COLOR_FFA500"), 
			"FFFF00" => GetMessage("P_COLOR_FFFF00"), 
			"008000" => GetMessage("P_COLOR_008000"), 
			"00FFFF" => GetMessage("P_COLOR_00FFFF"), 
			"800080" => GetMessage("P_COLOR_800080"), 
			"FFFFFF" => GetMessage("P_COLOR_FFFFFF"),
			"000000" => GetMessage("P_COLOR_000000")),
		"DEFAULT" => array("FF0000", "FFFF00", "FFFFFF", "000000"),
		"ADDITIONAL_VALUES" => "Y",
		"MULTIPLE" => "Y"));
endif;
/* $arTemplateParameters = $arTemplateParameters + array(
	"SLIDER_COUNT_CELL" => array(
		"NAME" => GetMessage("P_SLIDER_COUNT_CELL"),
		"TYPE" => "STRING",
		"DEFAULT" => "4"),
);*/
?>