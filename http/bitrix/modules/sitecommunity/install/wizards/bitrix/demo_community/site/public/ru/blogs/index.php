<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Блог-лента");
?>
<?
$APPLICATION->IncludeComponent(
		"bitrix:blog.group.blog", 
		"", 
		Array(
			"BLOG_COUNT"				=> "10",
			"SHOW_BLOG_WITHOUT_POSTS"	=> "N",
			"BLOG_VAR"					=> "blog",
			"POST_VAR"					=> "post_id",
			"USER_VAR"					=> "user_id",
			"PAGE_VAR"					=> "page",
			"PATH_TO_BLOG"				=> "#SITE_DIR#people/user/#user_id#/blog/",
			"PATH_TO_POST" => "#SITE_DIR#people/user/#user_id#/blog/#post_id#/",
			"PATH_TO_GROUP_BLOG" => "#SITE_DIR#groups/group/#group_id#/blog/",
			"PATH_TO_GROUP_BLOG_POST" => "#SITE_DIR#groups/group/#group_id#/blog/#post_id#/",
			"PATH_TO_USER" => "#SITE_DIR#people/user/#user_id#/",
			"ID"						=> "#BLOG_GROUP_ID#",
			"CACHE_TYPE"				=> "A",
			"CACHE_TIME"				=> "36000000",
			"SET_TITLE" => "N",
			"USE_SOCNET" => "Y",
			"GROUP_ID" => "#BLOG_GROUP_ID#",
			"PATH_TO_SONET_USER_PROFILE" => "#SITE_DIR#people/user/#user_id#/",
			"PATH_TO_MESSAGES_CHAT" => "#SITE_DIR#people/messages/chat/#user_id#/",
		)
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>