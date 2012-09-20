<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Photo");
?><?$APPLICATION->IncludeComponent("bitrix:photogallery_user", ".default", array(
	"IBLOCK_TYPE" => "photos",
	"IBLOCK_ID" => "#IBLOCK_ID#",
	"SECTION_PAGE_ELEMENTS" => "20",
	"ELEMENTS_PAGE_ELEMENTS" => "50",
	"PAGE_NAVIGATION_TEMPLATE" => "",
	"ELEMENTS_USE_DESC_PAGE" => "Y",
	"USE_LIGHT_VIEW" => "N",
	"GALLERY_GROUPS" => array(
	),
	"ONLY_ONE_GALLERY" => "Y",
	"MODERATION" => "N",
	"SECTION_SORT_BY" => "UF_DATE",
	"SECTION_SORT_ORD" => "DESC",
	"ELEMENT_SORT_FIELD" => "id",
	"ELEMENT_SORT_ORDER" => "desc",
	"ANALIZE_SOCNET_PERMISSION" => "Y",
	"UPLOAD_MAX_FILE_SIZE" => "1024M",
	"GALLERY_AVATAR_SIZE" => "50",
	"ALBUM_PHOTO_THUMBS_SIZE" => "120",
	"ALBUM_PHOTO_SIZE" => "120",
	"THUMBS_SIZE" => "250",
	"PREVIEW_SIZE" => "700",
	"ORIGINAL_SIZE" => "0",
	"JPEG_QUALITY1" => "95",
	"JPEG_QUALITY2" => "95",
	"JPEG_QUALITY" => "90",
	"ADDITIONAL_SIGHTS" => array(
	),
	"WATERMARK_MIN_PICTURE_SIZE" => "250",
	"PATH_TO_FONT" => "",
	"WATERMARK_RULES" => "USER",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "#SITE_DIR#photo/",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"DATE_TIME_FORMAT_SECTION" => "M j, Y",
	"DATE_TIME_FORMAT_DETAIL" => "M j, Y",
	"DISPLAY_PANEL" => "N",
	"SET_TITLE" => "Y",
	"USE_RATING" => "Y",
	"MAX_VOTE" => "5",
	"VOTE_NAMES" => array(
		0 => "1",
		1 => "2",
		2 => "3",
		3 => "4",
		4 => "5",
		5 => "",
	),
	"SHOW_TAGS" => "Y",
	"TAGS_PAGE_ELEMENTS" => "150",
	"TAGS_PERIOD" => "",
	"TAGS_INHERIT" => "Y",
	"TAGS_FONT_MAX" => "30",
	"TAGS_FONT_MIN" => "10",
	"TAGS_COLOR_NEW" => "3E74E6",
	"TAGS_COLOR_OLD" => "C0C0C0",
	"TAGS_SHOW_CHAIN" => "Y",
	"USE_COMMENTS" => "Y",
	"PATH_TO_SMILE" => "/bitrix/images/forum/smile/",
	"DISPLAY_AS_RATING" => "vote_avg",
	"INDEX_PAGE_TOP_ELEMENTS_COUNT" => "45",
	"SHOW_ONLY_PUBLIC" => "N",
	"USE_LIGHT_TEMPLATE" => "N",
	"WATERMARK" => "Y",
	"WATERMARK_COLORS" => array(
		0 => "FF0000",
		1 => "FFFF00",
		2 => "FFFFFF",
		3 => "000000",
		4 => "",
	),
	"SLIDER_COUNT_CELL" => "4",
	"SEF_URL_TEMPLATES" => array(
		"index" => "index.php",
		"galleries" => "galleries/#USER_ID#/",
		"gallery" => "#SITE_DIR#people/user/#USER_ID#/photo/gallery/#USER_ALIAS#/",
		"gallery_edit"	=>	"#SITE_DIR#people/user/#USER_ID#/photo/gallery/#USER_ALIAS#/action/#ACTION#/",
		"section"	=>	"#SITE_DIR#people/user/#USER_ID#/photo/album/#USER_ALIAS#/#SECTION_ID#/",
		"section_edit"	=>	"#SITE_DIR#people/user/#USER_ID#/photo/album/#USER_ALIAS#/#SECTION_ID#/action/#ACTION#/",
		"section_edit_icon"	=>	"#SITE_DIR#people/user/#USER_ID#/photo/album/#USER_ALIAS#/#SECTION_ID#/icon/action/#ACTION#/",
		"upload"	=>	"#SITE_DIR#people/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/action/upload/",
		"detail"	=>	"#SITE_DIR#people/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/",
		"detail_edit"	=>	"#SITE_DIR#people/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/action/#ACTION#/",
		"detail_slide_show"	=>	"#SITE_DIR#people/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/slide_show/",
		"detail_list"	=>	"list/",
		"search"	=>	"search/",
		"tags"	=>	"tags/",
	)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>