<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = array();
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch()) {
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID)) {
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["~NAME"]; } }
$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch()) {
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"]; }

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
while($arUGroups = $dbUGroups -> Fetch()) {
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"]; }
$res = unserialize(COption::GetOptionString("photogallery", "pictures"));
$arSights = array();
if (is_array($res)) {
	foreach ($res as $key => $val) {
		$arSights[str_pad($key, 5, "_").$val["code"]] = $val["title"]; } }
if (empty($arCurrentValues["INDEX_URL"]) && !empty($arCurrentValues["SECTIONS_TOP_URL"]))
	$arCurrentValues["INDEX_URL"] = $arCurrentValues["SECTIONS_TOP_URL"]; 
$arComponentParameters = array(
	"GROUPS" => array(
		"PHOTO_SETTINGS" => array(
			"NAME" => GetMessage("P_PHOTO_SETTINGS")),
		"THUMBS_SETTINGS" => array(
			"NAME" => GetMessage("P_PREVIEW"),
			"PARENT" => "PHOTO_SETTINGS"),
		"DETAIL_SETTINGS" => array(
			"NAME" => GetMessage("P_DETAIL"),
			"PARENT" => "PHOTO_SETTINGS"),
		"ORIGINAL_SETTINGS" => array(
			"NAME" => GetMessage("P_ORIGINAL"),
			"PARENT" => "PHOTO_SETTINGS")),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
		),
		"SECTION_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SECTION_ID"]}',
		),
		"INDEX_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_INDEX_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
		"SECTION_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SECTION_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "section.php?SECTION_ID=#SECTION_ID#"),
		
/*		"UPLOAD_MAX_FILE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_UPLOAD_MAX_FILE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"1" => "1",
				"2" => "2",
				"3" => "3",
				"4" => "4",
				"5" => "5"),
			"DEFAULT" => array("2"),
			"MULTIPLE" => "N"),*/
		"UPLOAD_MAX_FILE_SIZE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_UPLOAD_MAX_FILE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "7"),
		"ADDITIONAL_SIGHTS" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ADDITIONAL_SIGHTS"),
			"TYPE" => "LIST",
			"VALUES" => $arSights,
			"DEFAULT" => array(),
			"MULTIPLE" => "Y"
		),
		"MODERATION" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_MODERATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		"WATERMARK_RULES" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_RULES"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"USER" => GetMessage("P_WATERMARK_RULES_USER"),
				"ALL" => GetMessage("P_WATERMARK_RULES_ALL")), 
			"DEFAULT" => "USER", 
			"REFRESH" => "Y"),
		)
	);

if ($arCurrentValues["WATERMARK_RULES"] == "ALL")
{
	$arComponentParameters["PARAMETERS"]["WATERMARK_TYPE"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_WATERMARK_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"TEXT" => GetMessage("P_WATERMARK_TYPE_TEXT"),
			"PICTURE" => GetMessage("P_WATERMARK_TYPE_PICTURE")), 
		"DEFAULT" => "PICTURE", 
		"REFRESH" => "Y");
	if ($arCurrentValues["WATERMARK_TYPE"] == "TEXT")
	{
		$arComponentParameters["PARAMETERS"]["WATERMARK_TEXT"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_TEXT"),
			"TYPE" => "STRING",
			"VALUES" => "");
		$arComponentParameters["PARAMETERS"]["WATERMARK_COLOR"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_COLOR"),
			"TYPE" => "STRING",
			"VALUES" => "FF00EE"); 
		$arComponentParameters["PARAMETERS"]["WATERMARK_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_SIZE"),
			"TYPE" => "STRING",
			"VALUES" => "10"); 
	}
	else 
	{
		$arComponentParameters["PARAMETERS"]["WATERMARK_FILE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_FILE"),
			"TYPE" => "STRING",
			"VALUES" => "");
		$arComponentParameters["PARAMETERS"]["WATERMARK_FILE_ORDER"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_FILE_ORDER"),
			"VALUES" => array(
				"usual" => GetMessage("P_WATERMARK_FILE_ORDER_USUAL"),
				"resize" => GetMessage("P_WATERMARK_FILE_ORDER_RESIZE"), 
				"repeat" => GetMessage("P_WATERMARK_FILE_ORDER_REPEAT")), 
			"DEFAULT" => "usual");
		
	}
	
	$arComponentParameters["PARAMETERS"]["WATERMARK_POSITION"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_WATERMARK_POSITION"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"tl" => GetMessage("P_WATERMARK_POSITION_TL"),
			"tc" => GetMessage("P_WATERMARK_POSITION_TC"),
			"tr" => GetMessage("P_WATERMARK_POSITION_TR"),
			"ml" => GetMessage("P_WATERMARK_POSITION_ML"),
			"mc" => GetMessage("P_WATERMARK_POSITION_MC"),
			"mr" => GetMessage("P_WATERMARK_POSITION_MR"),
			"bl" => GetMessage("P_WATERMARK_POSITION_BL"),
			"bc" => GetMessage("P_WATERMARK_POSITION_BC"),
			"br" => GetMessage("P_WATERMARK_POSITION_BR")),
		"DEFAULT" => "mc");
	$arComponentParameters["PARAMETERS"]["WATERMARK_TRANSPARENCY"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_WATERMARK_TRANSPARENCY"),
		"TYPE" => "STRING",
		"DEFAULT" => "20");
}

$arComponentParameters["PARAMETERS"]["PATH_TO_FONT"] = array(
	"PARENT" => "PHOTO_SETTINGS",
	"NAME" => GetMessage("P_PATH_TO_FONT"),
	"TYPE" => "STRING",
	"DEFAULT" => ""
);

$arComponentParameters["PARAMETERS"]["WATERMARK_MIN_PICTURE_SIZE"] = array(
	"PARENT" => "PHOTO_SETTINGS",
	"NAME" => GetMessage("P_WATERMARK_MIN_PICTURE_SIZE"),
	"TYPE" => "STRING",
	"DEFAULT" => "800");

$arComponentParameters["PARAMETERS"]["ALBUM_PHOTO_WIDTH"] = array(
	"PARENT" => "PHOTO_SETTINGS",
	"NAME" => GetMessage("P_ALBUM_PHOTO_WIDTH"),
	"TYPE" => "STRING",
	"DEFAULT" => "200");
$arComponentParameters["PARAMETERS"]["ALBUM_PHOTO_THUMBS_WIDTH"] = array(
	"PARENT" => "PHOTO_SETTINGS",
	"NAME" => GetMessage("P_ALBUM_PHOTO_THUMBS_WIDTH"),
	"TYPE" => "STRING",
	"DEFAULT" => "120");

$arComponentParameters["PARAMETERS"]["THUMBS_SIZE"] = array(
	"PARENT" => "THUMBS_SETTINGS",
	"NAME" => GetMessage("P_SIZE"),
	"TYPE" => "STRING",
	"DEFAULT" => "250");
$arComponentParameters["PARAMETERS"]["JPEG_QUALITY1"] = array(
	"PARENT" => "THUMBS_SETTINGS",
	"NAME" => GetMessage("P_JPEG_QUALITY"),
	"TYPE" => "STRING",
	"DEFAULT" => "95");

$arComponentParameters["PARAMETERS"]["PREVIEW_SIZE"] = array(
	"PARENT" => "DETAIL_SETTINGS",
	"NAME" => GetMessage("P_SIZE"),
	"TYPE" => "STRING",
	"DEFAULT" => "600");
$arComponentParameters["PARAMETERS"]["JPEG_QUALITY2"] = array(
	"PARENT" => "DETAIL_SETTINGS",
	"NAME" => GetMessage("P_JPEG_QUALITY"),
	"TYPE" => "STRING",
	"DEFAULT" => "95");

$arComponentParameters["PARAMETERS"]["ORIGINAL_SIZE"] = array(
	"PARENT" => "ORIGINAL_SETTINGS",
	"NAME" => GetMessage("P_ORIGINAL_SIZE"),
	"TYPE" => "STRING",
	"DEFAULT" => "0");
$arComponentParameters["PARAMETERS"]["JPEG_QUALITY"] = array(
	"PARENT" => "ORIGINAL_SETTINGS",
	"NAME" => GetMessage("P_JPEG_QUALITY"),
	"TYPE" => "STRING",
	"DEFAULT" => "90");

// $arComponentParameters["PARAMETERS"]["DISPLAY_PANEL"] = array(
	// "PARENT" => "ADDITIONAL_SETTINGS",
	// "NAME" => GetMessage("T_IBLOCK_DESC_NEWS_PANEL"),
	// "TYPE" => "CHECKBOX",
	// "DEFAULT" => "N");
$arComponentParameters["PARAMETERS"]["SET_TITLE"] = array();
?>