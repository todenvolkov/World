<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = array();
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch())
{
	if ($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID)): 
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["~NAME"];
	endif;
}

$arIBlock = array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
while($arUGroups = $dbUGroups -> Fetch())
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}
$res = unserialize(COption::GetOptionString("photogallery", "pictures"));
$arSights = array();
if (is_array($res))
{
	foreach ($res as $key => $val)
	{
		$arSights[str_pad($key, 5, "_").$val["code"]] = $val["title"];
	}
}

$arFiles = array("" => "...");
$path = str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT']."/".BX_ROOT."/modules/photogallery/fonts/");
CheckDirPath($path);
$handle = opendir($path);
$file_exist = false;
if ($handle)
{
	while($file = readdir($handle)) 
	{
		if ($file == "." || $file == ".." || !is_file($path.$file)): 
			continue;
		endif;
		$file_exist = true;
		$arFiles[$file] = $file;
	}
}
if (!$file_exist)
{
	$arFiles = array("" => GetMessage("P_FONTS_NONE"));
}

$hidden = ($arCurrentValues["USE_LIGHT_VIEW"] == "Y" ? "Y" : "N");

if (empty($arCurrentValues["SEF_URL_TEMPLATES_index"]) && !empty($arCurrentValues["SEF_URL_TEMPLATES_sections_top"])): 
	$arCurrentValues["SEF_URL_TEMPLATES_index"] = $arCurrentValues["SEF_URL_TEMPLATES_sections_top"]; 
endif;
$arComponentParameters = array(
	"GROUPS" => array(
		"PAGE_SETTINGS" => array("NAME" => GetMessage("P_PAGE_SETTINGS"), "SORT" => "100"),
		"PHOTO_SETTINGS" => array("NAME" => GetMessage("P_PHOTO_SETTINGS"), "SORT" => "150"),
		"RATING_SETTINGS" => array("NAME" => GetMessage("T_IBLOCK_DESC_RATING_SETTINGS")),
		"TAGS_CLOUD" => array("NAME" => GetMessage("T_TAGS_CLOUD"))),
	"PARAMETERS" => array(
		"USE_LIGHT_VIEW" => array(
			"PARENT" => "BASE",
	        "NAME" => GetMessage("P_USE_LIGHT_VIEW"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y"),
		"VARIABLE_ALIASES" => Array(
			"SECTION_ID" => Array("NAME" => GetMessage("SECTION_ID_DESC")),
			"ELEMENT_ID" => Array("NAME" => GetMessage("ELEMENT_ID_DESC")),
			"PAGE_NAME" => Array("NAME" => GetMessage("PAGE_NAME_DESC")),
			"ACTION" => Array("NAME" => GetMessage("ACTION_DESC"))),
		"SEF_MODE" => Array(
			"index" => array(
				"NAME" => GetMessage("INDEX_PAGE"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array()),
			"section" => array(
				"NAME" => GetMessage("SECTION_PAGE"),
				"DEFAULT" => "#SECTION_ID#/",
				"VARIABLES" => array("SECTION_ID")),
			"section_edit" => array(
				"NAME" => GetMessage("SECTION_EDIT_PAGE"),
				"DEFAULT" => "#SECTION_ID#/action/#ACTION#/",
				"VARIABLES" => array("SECTION_ID", "ACTION")),
			"section_edit_icon" => array(
				"NAME" => GetMessage("SECTION_EDIT_ICON_PAGE"),
				"DEFAULT" => "#SECTION_ID#/icon/action/#ACTION#/",
				"VARIABLES" => array("SECTION_ID", "ACTION")),
			"upload" => array(
				"NAME" => GetMessage("UPLOAD_PAGE"),
				"DEFAULT" => "#SECTION_ID#/action/upload/",
				"VARIABLES" => array("SECTION_ID")),
			"detail" => array(
				"NAME" => GetMessage("DETAIL_PAGE"),
				"DEFAULT" => "#SECTION_ID#/#ELEMENT_ID#/",
				"VARIABLES" => array("ELEMENT_ID", "SECTION_ID")),
			"detail_edit" => array(
				"NAME" => GetMessage("DETAIL_EDIT_PAGE"),
				"DEFAULT" => "#SECTION_ID#/#ELEMENT_ID#/action/#ACTION#/",
				"VARIABLES" => array("ELEMENT_ID", "SECTION_ID")), 
			"detail_slide_show" => array(
				"NAME" => GetMessage("DETAIL_SLIDE_SHOW_PAGE"),
				"DEFAULT" => "#SECTION_ID#/#ELEMENT_ID#/slide_show/",
				"VARIABLES" => array("SECTION_ID", "ELEMENT_ID")),
			"detail_list" => array(
				"NAME" => GetMessage("DETAIL_LIST_PAGE"),
				"DEFAULT" => "list/",
				"VARIABLES" => array())),
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y"),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y"),
		"SECTION_SORT_BY" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_SORT_FIELD"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"ID" => "ID",
				"NAME" => GetMessage("IBLOCK_SORT_NAME"),
				"SORT" => GetMessage("IBLOCK_SORT_SORT"), 
				"ELEMENTS_CNT" => GetMessage("IBLOCK_SORT_ELEMENTS_CNT"),
				"UF_DATE" => GetMessage("IBLOCK_SORT_DATE"),),
			"DEFAULT" => "UF_DATE", 
			"HIDDEN" => $hidden
		),
		"SECTION_SORT_ORD" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"ASC" => GetMessage("IBLOCK_SORT_ASC"),
				"DESC" => GetMessage("IBLOCK_SORT_DESC")
			),
			"DEFAULT" => "DESC", 
			"HIDDEN" => $hidden
		),
		"ELEMENT_SORT_FIELD" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"shows" => GetMessage("IBLOCK_SORT_SHOWS"),
				"sort" => GetMessage("IBLOCK_SORT_SORT"),
				"timestamp_x" => GetMessage("IBLOCK_SORT_TIMESTAMP"),
				"name" => GetMessage("IBLOCK_SORT_NAME"),
				"id" => GetMessage("IBLOCK_SORT_ID"),
				"rating" => GetMessage("IBLOCK_SORT_RATING"),
				"comments" => GetMessage("IBLOCK_SORT_COMMENTS")),
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "name", 
			"HIDDEN" => $hidden
		),
		"ELEMENT_SORT_ORDER" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"asc" => GetMessage("IBLOCK_SORT_ASC"),
				"desc" => GetMessage("IBLOCK_SORT_DESC")
			),
			"DEFAULT" => "desc", 
			"HIDDEN" => $hidden
		),
		"ELEMENTS_USE_DESC_PAGE" => Array(
			"PARENT" => "PAGE_SETTINGS",
			"NAME" => GetMessage("T_ELEMENTS_USE_DESC_PAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y", 
			"HIDDEN" => $hidden
		),

		"SECTION_PAGE_ELEMENTS" => array(
			"PARENT" => "PAGE_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_PAGE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "15", 
			"HIDDEN" => $hidden
		),
		"ELEMENTS_PAGE_ELEMENTS" => array(
			"PARENT" => "PAGE_SETTINGS",
			"NAME" => GetMessage("IBLOCK_ELEMENTS_PAGE_ELEMENTS"),
			"TYPE" => "STRING",
			"DEFAULT" => '50', 
			"HIDDEN" => $hidden
		),
		"PAGE_NAVIGATION_TEMPLATE" => array(
			"PARENT" => "PAGE_SETTINGS",
			"NAME" => GetMessage("P_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "", 
			"HIDDEN" => $hidden),

		"UPLOAD_MAX_FILE_SIZE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => str_replace("#upload_max_filesize#", ini_get('upload_max_filesize'), GetMessage("P_UPLOAD_MAX_FILE_SIZE")),
			"TYPE" => "STRING",
			"DEFAULT" => ini_get('upload_max_filesize')),
			
		"ALBUM_PHOTO_THUMBS_SIZE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ALBUM_PHOTO_THUMBS_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "200"),
		"ALBUM_PHOTO_SIZE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ALBUM_PHOTO_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "120"),
		"THUMBS_SIZE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_THUMBS_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "250"),
		"JPEG_QUALITY1" => Array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_JPEG_QUALITY1"),
			"TYPE" => "STRING",
			"DEFAULT" => "95", 
			"HIDDEN" => $hidden),
		"PREVIEW_SIZE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_PREVIEW_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "700"),
		"JPEG_QUALITY2" => Array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_JPEG_QUALITY2"),
			"TYPE" => "STRING",
			"DEFAULT" => "95", 
			"HIDDEN" => $hidden),
		"ORIGINAL_SIZE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ORIGINAL_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "0"), 
		"JPEG_QUALITY" => Array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_JPEG_QUALITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "90", 
			"HIDDEN" => $hidden),
		"ADDITIONAL_SIGHTS" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ADDITIONAL_SIGHTS"),
			"TYPE" => "LIST",
			"VALUES" => $arSights,
			"DEFAULT" => array(),
			"MULTIPLE" => "Y", 
			"HIDDEN" => $hidden
		),
		"WATERMARK_MIN_PICTURE_SIZE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_MIN_PICTURE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "200", 
			"HIDDEN" => $hidden),
		"PATH_TO_FONT" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_PATH_TO_FONT"),
			"TYPE" => "LIST",
			"VALUES" => $arFiles
		),
		"WATERMARK_RULES" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_RULES"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"USER" => GetMessage("P_WATERMARK_RULES_USER"),
				"ALL" => GetMessage("P_WATERMARK_RULES_ALL")), 
			"DEFAULT" => "USER", 
			"REFRESH" => "Y"
		), 
		// "DISPLAY_PANEL" => Array(
			// "PARENT" => "ADDITIONAL_SETTINGS",
			// "NAME" => GetMessage("T_IBLOCK_DESC_NEWS_PANEL"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N", 
			// "HIDDEN" => $hidden),
/*
		"USE_PERMISSIONS" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_PERMISSIONS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y", 
			"HIDDEN" => $hidden),
		"GROUP_PERMISSIONS" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_GROUP_PERMISSIONS"),
			"TYPE" => "LIST",
			"VALUES" => $arUGroupsEx,
			"DEFAULT" => Array(1),
			"MULTIPLE" => "Y", 
			"HIDDEN" => $hidden),
*/
		"DATE_TIME_FORMAT_SECTION" => CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT_SECTION"), "ADDITIONAL_SETTINGS"),
		"DATE_TIME_FORMAT_DETAIL" => CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT_DETAIL"), "ADDITIONAL_SETTINGS"),
		
		"SET_TITLE" => Array(),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),

		"USE_RATING" => Array(
			"PARENT" => "RATING_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_RATING"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y"),

		"SHOW_TAGS" => array(
			"PARENT" => "TAGS_CLOUD",
	        "NAME" => GetMessage("P_SHOW_TAGS"),
			"TYPE" => "CHECKBOX",
			"REFRESH" => (IsModuleInstalled("search") ? "Y" : "N"),
			"DEFAULT" => "N"),
		)
	);

$arComponentParameters["PARAMETERS"]["DATE_TIME_FORMAT_SECTION"]["HIDDEN"] = $hidden;
$arComponentParameters["PARAMETERS"]["DATE_TIME_FORMAT_DETAIL"]["HIDDEN"] = $hidden;
if ($arCurrentValues["USE_PERMISSIONS"] != "Y"):
	unset($arComponentParameters["PARAMETERS"]["GROUP_PERMISSIONS"]);
endif;

if ($arCurrentValues["WATERMARK_RULES"] == "ALL")
{
	$arComponentParameters["PARAMETERS"]["WATERMARK_TYPE"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_WATERMARK_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"TEXT" => GetMessage("P_WATERMARK_TYPE_TEXT"),
			"PICTURE" => GetMessage("P_WATERMARK_TYPE_PICTURE")
		), 
		"DEFAULT" => "PICTURE", 
		"REFRESH" => "Y"
	);
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
			"TYPE" => "LIST",
			"VALUES" => array(
				"usual" => GetMessage("P_WATERMARK_FILE_ORDER_USUAL"),
				"resize" => GetMessage("P_WATERMARK_FILE_ORDER_RESIZE"), 
				"repeat" => GetMessage("P_WATERMARK_FILE_ORDER_REPEAT")
			), 
			"DEFAULT" => "usual"
		);
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
		"DEFAULT" => "mc"
	);
	$arComponentParameters["PARAMETERS"]["WATERMARK_TRANSPARENCY"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_WATERMARK_TRANSPARENCY"),
		"TYPE" => "STRING",
		"DEFAULT" => "20");
}

if($arCurrentValues["USE_RATING"]=="Y")
{
	$arComponentParameters["PARAMETERS"]["MAX_VOTE"] = array(
		"PARENT" => "RATING_SETTINGS",
		"NAME" => GetMessage("IBLOCK_MAX_VOTE"),
		"TYPE" => "STRING",
		"DEFAULT" => "5");
	$arComponentParameters["PARAMETERS"]["VOTE_NAMES"] = array(
		"PARENT" => "RATING_SETTINGS",
		"NAME" => GetMessage("IBLOCK_VOTE_NAMES"),
		"TYPE" => "STRING",
		"VALUES" => array(),
		"MULTIPLE" => "Y",
		"DEFAULT" => array("1","2","3","4","5"),
		"ADDITIONAL_VALUES" => "Y"
	);
	$arComponentParameters["PARAMETERS"]["DISPLAY_AS_RATING"] = array(
		"NAME" => GetMessage("TP_CBIV_DISPLAY_AS_RATING"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"rating" => GetMessage("TP_CBIV_RATING"),
			"vote_avg" => GetMessage("TP_CBIV_AVERAGE"),
		),
		"DEFAULT" => "rating",
	);
}

if (IsModuleInstalled("blog") || IsModuleInstalled("forum"))
{
	$arComponentParameters["GROUPS"]["REVIEW_SETTINGS"] = array(
		"NAME" => GetMessage("T_IBLOCK_DESC_REVIEW_SETTINGS"));

	$arComponentParameters["PARAMETERS"]["USE_COMMENTS"] = array(
			"PARENT" => "REVIEW_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_COMMENTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y");

	if ($arCurrentValues["USE_COMMENTS"]=="Y")
	{
		$arr = array();
		$default = "";
		
		if (IsModuleInstalled("blog"))
		{
			$arr["blog"] = GetMessage("P_COMMENTS_TYPE_BLOG");
			$default = "blog";
		}
		if (IsModuleInstalled("forum"))
		{
			$arr["forum"] = GetMessage("P_COMMENTS_TYPE_FORUM");
			$default = "forum";
		}

		$arComponentParameters["PARAMETERS"]["COMMENTS_TYPE"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("P_COMMENTS_TYPE"),
				"TYPE" => "LIST",
				"VALUES" => $arr,
				"DEFAULT" => $default,
				"REFRESH" => "Y");

		$arCurrentValues["COMMENTS_TYPE"] = ($arCurrentValues["COMMENTS_TYPE"] == "forum" || $arCurrentValues["COMMENTS_TYPE"] == "blog" ? $arCurrentValues["COMMENTS_TYPE"] : $default);


		if (IsModuleInstalled("blog") && $arCurrentValues["COMMENTS_TYPE"]=="blog")
		{
			$arBlogs = array();
			if(CModule::IncludeModule("blog"))
			{
				$rsBlog = CBlog::GetList();
				while($arBlog=$rsBlog->Fetch())
				{
					$arBlogs[$arBlog["URL"]] = $arBlog["NAME"];
					$url = $arBlog["URL"];
				}
			}
			$arComponentParameters["PARAMETERS"]["BLOG_URL"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_BLOG_URL"),
				"TYPE" => "LIST",
				"VALUES" => $arBlogs,
				"DEFAULT" => $url);
			$arComponentParameters["PARAMETERS"]["COMMENTS_COUNT"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_COMMENTS_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 25, 
				"HIDDEN" => $hidden);
			$arComponentParameters["PARAMETERS"]["PATH_TO_USER"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("P_PATH_TO_USER"),
				"TYPE" => "STRING",
				"DEFAULT" => "");
			$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("P_PATH_TO_BLOG"),
				"TYPE" => "STRING",
				"DEFAULT" => "");
			$arComponentParameters["PARAMETERS"]["PATH_TO_SMILE"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PATH_TO_SMILE"),
				"TYPE" => "STRING",
				"DEFAULT" => "/bitrix/images/blog/smile/", 
				"HIDDEN" => $hidden);
		}
		elseif (IsModuleInstalled("forum") && $arCurrentValues["COMMENTS_TYPE"]=="forum")
		{
			$arForum = array();
			$fid = 0;
			if (CModule::IncludeModule("forum"))
			{
				$db_res = CForumNew::GetList(array(), array());
				if ($db_res && ($res = $db_res->GetNext()))
				{
					do
					{
						$arForum[intVal($res["ID"])] = $res["NAME"];
						$fid = intVal($res["ID"]);
					}while ($res = $db_res->GetNext());
				}
			}
			$arComponentParameters["PARAMETERS"]["FORUM_ID"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_FORUM_ID"),
				"TYPE" => "LIST",
				"VALUES" => $arForum,
				"DEFAULT" => $fid);
			$arComponentParameters["PARAMETERS"]["COMMENTS_COUNT"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_COMMENTS_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"), 
				"HIDDEN" => $hidden);
			$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_READ"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_READ_TEMPLATE"),
				"TYPE" => "STRING",
				"DEFAULT" => "", 
				"HIDDEN" => $hidden);
			$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_PROFILE_VIEW"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
				"TYPE" => "STRING",
				"DEFAULT" => "", 
				"HIDDEN" => $hidden);
			$arComponentParameters["PARAMETERS"]["PATH_TO_SMILE"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PATH_TO_SMILE"),
				"TYPE" => "STRING",
				"DEFAULT" => "/bitrix/images/forum/smile/", 
				"HIDDEN" => $hidden);
			$arComponentParameters["PARAMETERS"]["USE_CAPTCHA"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_USE_CAPTCHA"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N", 
				"HIDDEN" => $hidden);
			$arComponentParameters["PARAMETERS"]["SHOW_LINK_TO_FORUM"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_SHOW_LINK_TO_FORUM"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N", 
				"HIDDEN" => $hidden);
			$arComponentParameters["PARAMETERS"]["PREORDER"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PREORDER"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N", 
				"HIDDEN" => $hidden);
			$arComponentParameters["PARAMETERS"]["POST_FIRST_MESSAGE"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_POST_FIRST_MESSAGE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N", 
				"HIDDEN" => $hidden
			);
		}
	}
}

if (IsModuleInstalled("search"))
{
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["search"] = array(
		"NAME" => GetMessage("SEARCH_PAGE"),
		"DEFAULT" => "search/",
		"VARIABLES" => array());

	if($arCurrentValues["SHOW_TAGS"]=="Y")
	{
		$arComponentParameters["PARAMETERS"]["TAGS_PAGE_ELEMENTS"] = array(
			"PARENT" => "TAGS_CLOUD",
			"NAME" => GetMessage("SEARCH_PAGE_ELEMENTS"),
			"TYPE" => "STRING",
			"DEFAULT" => "150");
		$arComponentParameters["PARAMETERS"]["TAGS_PERIOD"] = array(
			"PARENT" => "TAGS_CLOUD",
			"NAME" => GetMessage("SEARCH_PERIOD"),
			"TYPE" => "STRING",
			"DEFAULT" => "", 
			"HIDDEN" => $hidden);
		$arComponentParameters["PARAMETERS"]["TAGS_INHERIT"] = array(
			"PARENT" => "TAGS_CLOUD",
			"NAME" => GetMessage("SEARCH_TAGS_INHERIT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y", 
			"HIDDEN" => $hidden);
		$arComponentParameters["PARAMETERS"]["TAGS_FONT_MAX"] = array(
			"PARENT" => "TAGS_CLOUD",
			"NAME" => GetMessage("SEARCH_FONT_MAX"),
			"TYPE" => "STRING",
			"DEFAULT" => "30");
		$arComponentParameters["PARAMETERS"]["TAGS_FONT_MIN"] = array(
			"NAME" => GetMessage("SEARCH_FONT_MIN"),
			"PARENT" => "TAGS_CLOUD",
			"TYPE" => "STRING",
			"DEFAULT" => "10");
		$arComponentParameters["PARAMETERS"]["TAGS_COLOR_NEW"] = array(
	    	"NAME" => GetMessage("SEARCH_COLOR_NEW"),
			"PARENT" => "TAGS_CLOUD",
			"TYPE" => "STRING",
			"DEFAULT" => "3E74E6");
		$arComponentParameters["PARAMETERS"]["TAGS_COLOR_OLD"] = array(
			"NAME" => GetMessage("SEARCH_COLOR_OLD"),
			"PARENT" => "TAGS_CLOUD",
			"TYPE" => "STRING",
			"DEFAULT" => "C0C0C0");
		$arComponentParameters["PARAMETERS"]["TAGS_SHOW_CHAIN"] = array(
			"NAME" => GetMessage("SEARCH_SHOW_CHAIN"),
			"PARENT" => "TAGS_CLOUD",
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y", 
			"HIDDEN" => $hidden);
	}
}
?>