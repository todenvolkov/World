<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("socialnetwork"))
	return false;

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
$userProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$userProp[$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$userPropBlock1 = array(
	"ID" => GetMessage("SONET_UP1_ID"),
	"LOGIN" => GetMessage("SONET_UP1_LOGIN"),
	"NAME" => GetMessage("SONET_UP1_NAME"),
	"SECOND_NAME" => GetMessage("SONET_UP1_SECOND_NAME"),
	"LAST_NAME" => GetMessage("SONET_UP1_LAST_NAME"),
	"EMAIL" => GetMessage("SONET_UP1_EMAIL"),
	"LAST_LOGIN" => GetMessage("SONET_UP1_LAST_LOGIN"),
	"DATE_REGISTER" => GetMessage("SONET_UP1_DATE_REGISTER"),
	"LID" => GetMessage("SONET_UP1_LID"),
);

$userPropBlockPassword = array(
	"PASSWORD" => GetMessage("SONET_UP1_PASSWORD"),
);

$userPropBlock2 = array(
	"PERSONAL_BIRTHDAY" => GetMessage("SONET_UP1_PERSONAL_BIRTHDAY"),
	"PERSONAL_BIRTHDAY_YEAR" => GetMessage("SONET_UP1_PERSONAL_BIRTHDAY_YEAR"),
	"PERSONAL_BIRTHDAY_DAY" => GetMessage("SONET_UP1_PERSONAL_BIRTHDAY_DAY"),

	"PERSONAL_PROFESSION" => GetMessage("SONET_UP1_PERSONAL_PROFESSION"),
	"PERSONAL_WWW" => GetMessage("SONET_UP1_PERSONAL_WWW"),
	"PERSONAL_ICQ" => GetMessage("SONET_UP1_PERSONAL_ICQ"),
	"PERSONAL_GENDER" => GetMessage("SONET_UP1_PERSONAL_GENDER"),
	"PERSONAL_PHOTO" => GetMessage("SONET_UP1_PERSONAL_PHOTO"),
	"PERSONAL_NOTES" => GetMessage("SONET_UP1_PERSONAL_NOTES"),

	"PERSONAL_PHONE" => GetMessage("SONET_UP1_PERSONAL_PHONE"),
	"PERSONAL_FAX" => GetMessage("SONET_UP1_PERSONAL_FAX"),
	"PERSONAL_MOBILE" => GetMessage("SONET_UP1_PERSONAL_MOBILE"),
	"PERSONAL_PAGER" => GetMessage("SONET_UP1_PERSONAL_PAGER"),

	"PERSONAL_COUNTRY" => GetMessage("SONET_UP1_PERSONAL_COUNTRY"),
	"PERSONAL_STATE" => GetMessage("SONET_UP1_PERSONAL_STATE"),
	"PERSONAL_CITY" => GetMessage("SONET_UP1_PERSONAL_CITY"),
	"PERSONAL_ZIP" => GetMessage("SONET_UP1_PERSONAL_ZIP"),
	"PERSONAL_STREET" => GetMessage("SONET_UP1_PERSONAL_STREET"),
	"PERSONAL_MAILBOX" => GetMessage("SONET_UP1_PERSONAL_MAILBOX"),

	"WORK_COMPANY" => GetMessage("SONET_UP1_WORK_COMPANY"),
	"WORK_DEPARTMENT" => GetMessage("SONET_UP1_WORK_DEPARTMENT"),
	"WORK_POSITION" => GetMessage("SONET_UP1_WORK_POSITION"),
	"WORK_WWW" => GetMessage("SONET_UP1_WORK_WWW"),
	"WORK_PROFILE" => GetMessage("SONET_UP1_WORK_PROFILE"),
	"WORK_LOGO" => GetMessage("SONET_UP1_WORK_LOGO"),
	"WORK_NOTES" => GetMessage("SONET_UP1_WORK_NOTES"),

	"WORK_PHONE" => GetMessage("SONET_UP1_WORK_PHONE"),
	"WORK_FAX" => GetMessage("SONET_UP1_WORK_FAX"),
	"WORK_PAGER" => GetMessage("SONET_UP1_WORK_PAGER"),

	"WORK_COUNTRY" => GetMessage("SONET_UP1_WORK_COUNTRY"),
	"WORK_STATE" => GetMessage("SONET_UP1_WORK_STATE"),
	"WORK_CITY" => GetMessage("SONET_UP1_WORK_CITY"),
	"WORK_ZIP" => GetMessage("SONET_UP1_WORK_ZIP"),
	"WORK_STREET" => GetMessage("SONET_UP1_WORK_STREET"),
	"WORK_MAILBOX" => GetMessage("SONET_UP1_WORK_MAILBOX"),
);


$userProp1 = array_merge($userPropBlock1, $userPropBlock2);
$userPropEdit = array_merge($userPropBlock1, $userPropBlockPassword, $userPropBlock2);

if (IsModuleInstalled("forum"))
{
	$userPropBlockForum = array(
		'FORUM_SHOW_NAME' => GetMessage('SONET_UP1_FORUM_PREFIX').GetMessage('SONET_UP1_FORUM_SHOW_NAME'),
		'FORUM_DESCRIPTION' => GetMessage('SONET_UP1_FORUM_PREFIX').GetMessage('SONET_UP1_FORUM_DESCRIPTION'),
		'FORUM_INTERESTS' => GetMessage('SONET_UP1_FORUM_PREFIX').GetMessage('SONET_UP1_FORUM_INTERESTS'),
		'FORUM_SIGNATURE' => GetMessage('SONET_UP1_FORUM_PREFIX').GetMessage('SONET_UP1_FORUM_SIGNATURE'),
		'FORUM_AVATAR' => GetMessage('SONET_UP1_FORUM_PREFIX').GetMessage('SONET_UP1_FORUM_AVATAR'),
		'FORUM_HIDE_FROM_ONLINE' => GetMessage('SONET_UP1_FORUM_PREFIX').GetMessage('SONET_UP1_FORUM_HIDE_FROM_ONLINE'),
		'FORUM_SUBSC_GET_MY_MESSAGE' => GetMessage('SONET_UP1_FORUM_PREFIX').GetMessage('SONET_UP1_FORUM_SUBSC_GET_MY_MESSAGE'),
	);
	$userProp1 = array_merge($userProp1, $userPropBlockForum);
	$userPropEdit = array_merge($userPropEdit, $userPropBlockForum);
}

if (IsModuleInstalled("blog"))
{
	$userPropBlockBlog = array(
		'BLOG_ALIAS' => GetMessage('SONET_UP1_BLOG_PREFIX').GetMessage('SONET_UP1_BLOG_ALIAS'),
		'BLOG_DESCRIPTION' => GetMessage('SONET_UP1_BLOG_PREFIX').GetMessage('SONET_UP1_BLOG_DESCRIPTION'),
		'BLOG_INTERESTS' => GetMessage('SONET_UP1_BLOG_PREFIX').GetMessage('SONET_UP1_BLOG_INTERESTS'),
		'BLOG_AVATAR' => GetMessage('SONET_UP1_BLOG_PREFIX').GetMessage('SONET_UP1_BLOG_AVATAR'),
	);

	$userProp1 = array_merge($userProp1, $userPropBlockBlog);
	$userPropEdit = array_merge($userPropEdit, $userPropBlockBlog);
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => Array(
			"user_id" => Array(
				"NAME" => GetMessage("SONET_USER_VAR"),
				"DEFAULT" => "user_id",
			),
			"page" => Array(
				"NAME" => GetMessage("SONET_PAGE_VAR"),
				"DEFAULT" => "page",
			),
			"group_id" => Array(
				"NAME" => GetMessage("SONET_GROUP_VAR"),
				"DEFAULT" => "group_id",
			),
			"message_id" => Array(
				"NAME" => GetMessage("SONET_MESSAGE_VAR"),
				"DEFAULT" => "message_id",
			),
			"task_id" => Array(
				"NAME" => GetMessage("SONET_TASK_VAR"),
				"DEFAULT" => "task_id",
			),
		),
		"SEF_MODE" => Array(
			"index" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_INDEX"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array(),
			),
			"user_reindex" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_REINDEX"),
				"DEFAULT" => "user_reindex.php",
				"VARIABLES" => array(),
			),
			"user_content_search" => array(
				"NAME" => GetMessage("SONET_SEF_USER_CONTENT_SEARCH"),
				"DEFAULT" => "user/#user_id#/search/",
				"VARIABLES" => array(),
			),	
			"user" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_USER"),
				"DEFAULT" => "user/#user_id#/",
				"VARIABLES" => array("user_id"),
			),
			"user_friends" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_USER_FRIENDS"),
				"DEFAULT" => "user/#user_id#/friends/",
				"VARIABLES" => array("user_id"),
			),
			"user_friends_add" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_USER_FRIENDS_ADD"),
				"DEFAULT" => "user/#user_id#/friends/add/",
				"VARIABLES" => array("user_id"),
			),
			"user_friends_delete" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_USER_FRIENDS_DELETE"),
				"DEFAULT" => "user/#user_id#/friends/delete/",
				"VARIABLES" => array("user_id"),
			),
			"user_groups" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_USER_GROUPS"),
				"DEFAULT" => "user/#user_id#/groups/",
				"VARIABLES" => array("user_id"),
			),
			"user_groups_add" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_USER_GROUPS_ADD"),
				"DEFAULT" => "user/#user_id#/groups/add/",
				"VARIABLES" => array("user_id"),
			),
			"group_create" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_GROUP_CREATE"),
				"DEFAULT" => "user/#user_id#/groups/create/",
				"VARIABLES" => array("user_id"),
			),
			"user_profile_edit" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_USER_PROFILE_EDIT"),
				"DEFAULT" => "user/#user_id#/edit/",
				"VARIABLES" => array("user_id"),
			),
			"user_settings_edit" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_USER_SETTINGS_EDIT"),
				"DEFAULT" => "user/#user_id#/settings/",
				"VARIABLES" => array("user_id"),
			),
			"user_features" => array(
				"NAME" => GetMessage("SONET_SEF_USER_FEATURES"),
				"DEFAULT" => "user/#user_id#/features/",
				"VARIABLES" => array("user_id"),
			),

			"group_request_group_search" => array(
				"NAME" => GetMessage("SONET_GROUP_REQUEST_GROUP_SEARCH"),
				"DEFAULT" => "group/#user_id#/group_search/",
				"VARIABLES" => array("user_id"),
			),
			"group_request_user" => array(
				"NAME" => GetMessage("SONET_GROUP_REQUEST_USER"),
				"DEFAULT" => "group/#group_id#/user/#user_id#/request/",
				"VARIABLES" => array("user_id", "group_id"),
			),

			"search" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_SEARCH"),
				"DEFAULT" => "search.php",
				"VARIABLES" => array(),
			),

			"message_form" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_MESSAGE_FORM"),
				"DEFAULT" => "messages/form/#user_id#/",
				"VARIABLES" => array("user_id"),
			),
			"message_form_mess" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_MESSAGE_FORM_MESS"),
				"DEFAULT" => "messages/chat/#user_id#/#message_id#/",
				"VARIABLES" => array("user_id", "message_id"),
			),
			"user_ban" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_USER_BAN"),
				"DEFAULT" => "messages/ban/",
				"VARIABLES" => array(),
			),
			"messages_chat" => array(
				"NAME" => GetMessage("SONET_MESSAGES_CHAT"),
				"DEFAULT" => "messages/chat/#user_id#/",
				"VARIABLES" => array("user_id"),
			),
			"messages_input" => array(
				"NAME" => GetMessage("SONET_MESSAGES_INPUT"),
				"DEFAULT" => "messages/input/",
				"VARIABLES" => array(),
			),
			"messages_input_user" => array(
				"NAME" => GetMessage("SONET_MESSAGES_INPUT_USER"),
				"DEFAULT" => "messages/input/#user_id#/",
				"VARIABLES" => array("user_id"),
			),
			"messages_output" => array(
				"NAME" => GetMessage("SONET_MESSAGES_OUTPUT"),
				"DEFAULT" => "messages/output/",
				"VARIABLES" => array(),
			),
			"messages_output_user" => array(
				"NAME" => GetMessage("SONET_MESSAGES_OUTPUT_USER"),
				"DEFAULT" => "messages/output/#user_id#/",
				"VARIABLES" => array("user_id"),
			),
			"messages_users" => array(
				"NAME" => GetMessage("SONET_SEF_MESSAGES_USERS"),
				"DEFAULT" => "messages/",
				"VARIABLES" => array(),
			),
			"messages_users_messages" => array(
				"NAME" => GetMessage("SONET_SEF_MESSAGES_USERS_MESSAGES"),
				"DEFAULT" => "messages/#user_id#/",
				"VARIABLES" => array(),
			),
			"log" => array(
				"NAME" => GetMessage("SONET_SEF_LOG"),
				"DEFAULT" => "log/",
				"VARIABLES" => array(),
			),
			"activity" => array(
				"NAME" => GetMessage("SONET_SEF_ACTIVITY"),
				"DEFAULT" => "user/#user_id#/activity/",
				"VARIABLES" => array(),
			),
			"subscribe" => array(
				"NAME" => GetMessage("SONET_SEF_SUBSCRIBE"),
				"DEFAULT" => "subscribe/",
				"VARIABLES" => array(),
			),
			"user_subscribe" => array(
				"NAME" => GetMessage("SONET_SEF_USER_SUBSCRIBE"),
				"DEFAULT" => "user/#user_id#/subscribe/",
				"VARIABLES" => array(),
			),

			"user_photo" => array(
				"NAME" => GetMessage("SONET_SEF_USER_PHOTO"),
				"DEFAULT" => "user/#user_id#/photo/",
				"VARIABLES" => array("user_id", "user_alias"),
			),
			"user_calendar" => array(
				"NAME" => GetMessage("SONET_SEF_USER_CALENDAR"),
				"DEFAULT" => "user/#user_id#/calendar/",
				"VARIABLES" => array("user_id"),
			),
			"user_files" => array(
				"NAME" => GetMessage("SONET_SEF_USER_FILES"),
				"DEFAULT" => "user/#user_id#/files/lib/#path#",
				"VARIABLES" => array("user_id"),
			),

			"user_blog" => array(
				"NAME" => GetMessage("SONET_SEF_USER_BLOG"),
				"DEFAULT" => "user/#user_id#/blog/",
				"VARIABLES" => array("user_id"),
			),
			"user_blog_post_edit" => array(
				"NAME" => GetMessage("SONET_SEF_USER_BLOG_POST_EDIT"),
				"DEFAULT" => "user/#user_id#/blog/edit/#post_id#/",
				"VARIABLES" => array("user_id", "post_id"),
			),
			"user_blog_rss" => array(
				"NAME" => GetMessage("SONET_SEF_USER_BLOG_RSS"),
				"DEFAULT" => "user/#user_id#/blog/rss/#type#/",
				"VARIABLES" => array("user_id", "type"),
			),
			"user_blog_draft" => array(
				"NAME" => GetMessage("SONET_SEF_USER_BLOG_DRAFT"),
				"DEFAULT" => "user/#user_id#/blog/draft/",
				"VARIABLES" => array("user_id"),
			),
			"user_blog_post" => array(
				"NAME" => GetMessage("SONET_SEF_USER_BLOG_POST"),
				"DEFAULT" => "user/#user_id#/blog/#post_id#/",
				"VARIABLES" => array("user_id", "post_id"),
			),
			"user_blog_moderation" => array(
				"NAME" => GetMessage("SONET_SEF_USER_BLOG_MODERATION"),
				"DEFAULT" => "user/#user_id#/blog/moderation/",
				"VARIABLES" => array("user_id"),
			),

			"user_forum" => array(
				"NAME" => GetMessage("SONET_SEF_USER_FORUM"),
				"DEFAULT" => "user/#user_id#/forum/",
				"VARIABLES" => array("user_id"),
			),
			"user_forum_topic_edit" => array(
				"NAME" => GetMessage("SONET_SEF_USER_FORUM_TOPIC_EDIT"),
				"DEFAULT" => "user/#user_id#/forum/edit/#topic_id#/",
				"VARIABLES" => array("user_id", "topic_id"),
			),
			"user_forum_topic" => array(
				"NAME" => GetMessage("SONET_SEF_USER_FORUM_TOPIC"),
				"DEFAULT" => "user/#user_id#/forum/#topic_id#/",
				"VARIABLES" => array("user_id", "topic_id"),
			),
			"bizproc" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_BIZPROC"),
				"DEFAULT" => "bizproc/",
				"VARIABLES" => array(),
			),
			"bizproc_edit" => array(
				"NAME" => GetMessage("SONET_SEF_PATH_BIZPROC_EDIT"),
				"DEFAULT" => "bizproc/#task_id#/",
				"VARIABLES" => array("task_id"),
			),
			"video_call" => array(
				"NAME" => GetMessage("SONET_SEF_VIDEO_CALL"),
				"DEFAULT" => "video/#user_id#/",
				"VARIABLES" => array("user_id"),
			),
		),
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/socialnetwork/smile/",
		),
		"PATH_TO_BLOG_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_PATH_TO_BLOG_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/blog/smile/",
		),
		"PATH_TO_FORUM_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_PATH_TO_FORUM_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/forum/smile/",
		),
		"SONET_PATH_TO_FORUM_ICON" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_PATH_TO_FORUM_ICON"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/forum/icon/",
		),


		"CACHE_TIME"  =>  Array("DEFAULT" => 3600),
		"SET_TITLE" => Array(),
		"CACHE_TIME_LONG" => array(
			"NAME" => GetMessage("SONET_CACHE_TIME_LONG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "604800",
			"COLS" => 25,
			"PARENT" => "CACHE_SETTINGS",
		),
		"SET_NAV_CHAIN" => Array(
		  	"NAME" => GetMessage("SONET_SET_NAV_CHAIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"ITEM_DETAIL_COUNT" => Array(
			"NAME" => GetMessage("SONET_ITEM_DETAIL_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => 32,
			"PARENT" => "VISUAL",
		),
		"ITEM_MAIN_COUNT" => Array(
			"NAME" => GetMessage("SONET_ITEM_MAIN_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "6",
			"PARENT" => "VISUAL",
		),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("SONET_DATE_TIME_FORMAT"), "VISUAL"),	
		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("SONET_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => GetMessage("SONET_NAME_TEMPLATE_DEFAULT"),
			"PARENT" => "VISUAL",
		),
		"SHOW_LOGIN" => Array(
			"NAME" => GetMessage("SONET_SHOW_LOGIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "VISUAL",
		),
		"CAN_OWNER_EDIT_DESKTOP" => Array(
			"NAME" => GetMessage("SONET_CAN_OWNER_EDIT_DESKTOP"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>(IsModuleInstalled("intranet") ? "N" : "Y"),
			"PARENT" => "VISUAL",
		),
		"USER_FIELDS_MAIN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_FIELDS_MAIN"),
			"TYPE" => "LIST",
			"VALUES" => $userProp1,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"USER_PROPERTY_MAIN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_PROPERTY_MAIN"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"USER_FIELDS_CONTACT" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_FIELDS_CONTACT"),
			"TYPE" => "LIST",
			"VALUES" => $userProp1,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"USER_PROPERTY_CONTACT" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_PROPERTY_CONTACT"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"USER_FIELDS_PERSONAL" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_FIELDS_PERSONAL"),
			"TYPE" => "LIST",
			"VALUES" => $userProp1,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"USER_PROPERTY_PERSONAL" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_PROPERTY_PERSONAL"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"SET_NAV_CHAIN" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_SET_NAVCHAIN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"
		),
		"PATH_TO_GROUP" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_GROUP"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_GROUP_SUBSCRIBE" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_GROUP_SUBSCRIBE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),

		"PATH_TO_GROUP_SEARCH" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_GROUP_SEARCH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_SEARCH_EXTERNAL" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_SEARCH_EXTERNAL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
/*
		"AJAX_LONG_TIMEOUT" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_AJAX_LONG_TIMEOUT"),
			"TYPE" => "STRING",
			"DEFAULT" => 60
		),
		"AJAX_MODE" => Array(),
*/
		"EDITABLE_FIELDS"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_EDITABLE_FIELDS"),
			"TYPE" => "LIST",
			"VALUES" => array_merge($userPropEdit, $userProp),
			"MULTIPLE" => "Y",
			"DEFAULT" => array('LOGIN', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'EMAIL', 'PERSONAL_BIRTHDAY', 'PERSONAL_CITY', 'PERSONAL_COUNTRY', 'PERSONAL_FAX', 'PERSONAL_GENDER', 'PERSONAL_ICQ', 'PERSONAL_MAILBOX', 'PERSONAL_MOBILE', 'PERSONAL_PAGER', 'PERSONAL_PHONE', 'PERSONAL_PHOTO', 'PERSONAL_STATE', 'PERSONAL_STREET', 'PERSONAL_WWW', 'PERSONAL_ZIP'),
		),
		"SHOW_YEAR" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_SHOW_YEAR"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"Y" => GetMessage("SONET_SHOW_YEAR_VALUE_Y"),
				"M" => GetMessage("SONET_SHOW_YEAR_VALUE_M"),
				"N" => GetMessage("SONET_SHOW_YEAR_VALUE_N")
			),
			"MULTIPLE" => "N",
			"DEFAULT" => "Y"
		),	
		"USER_FIELDS_SEARCH_SIMPLE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_FIELDS_SEARCH_SIMPLE"),
			"TYPE" => "LIST",
			"VALUES" => $userProp1,
			"MULTIPLE" => "Y",
			"DEFAULT" => array("PERSONAL_GENDER", "PERSONAL_CITY"),
		),
		"USER_PROPERTIES_SEARCH_SIMPLE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_PROPERTIES_SEARCH_SIMPLE"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"USER_FIELDS_SEARCH_ADV" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_FIELDS_SEARCH_ADV"),
			"TYPE" => "LIST",
			"VALUES" => $userProp1,
			"MULTIPLE" => "Y",
			"DEFAULT" => array("PERSONAL_GENDER", "PERSONAL_CITY", "PERSONAL_COUNTRY"),
		),
		"USER_PROPERTIES_SEARCH_ADV" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_USER_PROPERTIES_SEARCH_ADV"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"SONET_USER_FIELDS_LIST" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_SONET_USER_FIELDS_LIST"),
			"TYPE" => "LIST",
			"VALUES" => $userProp1,
			"MULTIPLE" => "Y",
			"DEFAULT" => array("PERSONAL_GENDER", "PERSONAL_BIRTHDAY", "PERSONAL_CITY"),
		),
		"SONET_USER_PROPERTY_LIST" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_SONET_USER_PROPERTY_LIST"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
		"SONET_USER_FIELDS_SEARCHABLE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_SONET_USER_FIELDS_SEARCHABLE"),
			"TYPE" => "LIST",
			"VALUES" => $userProp1,
			"MULTIPLE" => "Y",
			"DEFAULT" => array("NAME", "SECOND_NAME", "LAST_NAME", "LOGIN", "PERSONAL_BIRTHDAY", "PERSONAL_BIRTHDAY_YEAR", "PERSONAL_BIRTHDAY_DAY", "PERSONAL_PROFESSION", "PERSONAL_GENDER", "PERSONAL_COUNTRY", "PERSONAL_STATE", "PERSONAL_CITY", "PERSONAL_ZIP", "PERSONAL_STREET", "PERSONAL_MAILBOX", "WORK_COMPANY", "WORK_DEPARTMENT", "WORK_POSITION", "WORK_COUNTRY", "WORK_STATE", "WORK_CITY", "WORK_ZIP", "WORK_STREET", "WORK_MAILBOX"),
		),
		"SONET_USER_PROPERTY_SEARCHABLE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_SONET_USER_PROPERTY_SEARCHABLE"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
	),
);

if (CModule::IncludeModule("intranet"))
{
	$arComponentParameters["PARAMETERS"]["PATH_TO_CONPANY_DEPARTMENT"] = array(
		"NAME" => GetMessage("SONET_PATH_TO_CONPANY_DEPARTMENT"),
		"DEFAULT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
		"PARENT" => "URL_TEMPLATES",
	);

	/* *** EVENT CALENDAR *** */
	$arComponentParameters["GROUPS"]["EVENT_CALENDAR_SETTINGS"] = array("NAME" => GetMessage("SONET_EVENT_CALENDAR_SETTINGS"));
	$arIBlockType = array();
	$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
	while ($arr=$rsIBlockType->Fetch())
	{
		if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
			$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
	}

	$arIBlock=array();
	$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["CALENDAR_IBLOCK_TYPE"], "ACTIVE"=>"Y"));
	while($arr=$rsIBlock->Fetch())
		$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];

	$arComponentParameters["PARAMETERS"]["CALENDAR_IBLOCK_TYPE"] = Array(
		"PARENT" => "EVENT_CALENDAR_SETTINGS",
		"NAME" => GetMessage("SONET_CALENDAR_IBLOCK_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => $arIBlockType,
		"REFRESH" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["CALENDAR_USER_IBLOCK_ID"] = array(
		"PARENT" => "EVENT_CALENDAR_SETTINGS",
		"NAME" => GetMessage("SONET_CALENDAR_USER_IBLOCK_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arIBlock,
		"REFRESH" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["CALENDAR_WEEK_HOLIDAYS"] = array(
		"PARENT" => "EVENT_CALENDAR_SETTINGS",
		"NAME" => GetMessage("SONET_CALENDAR_WEEK_HOLIDAYS"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => array(GetMessage('EC_P_MON_F'),GetMessage('EC_P_TUE_F'),GetMessage('EC_P_WEN_F'),GetMessage('EC_P_THU_F'),GetMessage('EC_P_FRI_F'),GetMessage('EC_P_SAT_F'),GetMessage('EC_P_SAN_F')),
		"DEFAULT" => array(5,6),
	);
	$arComponentParameters["PARAMETERS"]["CALENDAR_YEAR_HOLIDAYS"] = array(
		"PARENT" => "EVENT_CALENDAR_SETTINGS",
		"NAME" => GetMessage("SONET_CALENDAR_YEAR_HOLIDAYS"),
		"TYPE" => 'STRING',
		"ROWS" => 3,
		"DEFAULT" => '1.01,7.01,23.02,8.03',
	);
	$arComponentParameters["PARAMETERS"]["CALENDAR_WORK_TIME_START"] = array(
		"PARENT" => "EVENT_CALENDAR_SETTINGS",
		"NAME" => GetMessage("SONET_CALENDAR_WORK_TIME_START"),
		"DEFAULT" => "9"
	);
	$arComponentParameters["PARAMETERS"]["CALENDAR_WORK_TIME_END"] = array(
		"PARENT" => "EVENT_CALENDAR_SETTINGS",
		"NAME" => GetMessage("SONET_CALENDAR_WORK_TIME_END"),
		"DEFAULT" => "19"
	);

	// SUPERPOSE
	$arComponentParameters["PARAMETERS"]["CALENDAR_ALLOW_SUPERPOSE"] = Array(
		"PARENT" => "EVENT_CALENDAR_SETTINGS",
		"NAME" => GetMessage("SONET_CALENDAR_ALLOW_SUPERPOSE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
	);

	if ($arCurrentValues["CALENDAR_ALLOW_SUPERPOSE"] == 'Y')
	{
		$arComponentParameters["PARAMETERS"]["CALENDAR_SUPERPOSE_CAL_IDS"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_SP_CAL_IDS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arIBlock
		);

		// Cur user
		$arComponentParameters["PARAMETERS"]["CALENDAR_SUPERPOSE_CUR_USER_CALS"] = Array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_SP_CUR_USER_CALS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y",
		);
		// Users
		$arComponentParameters["PARAMETERS"]["CALENDAR_SUPERPOSE_USERS_CALS"] = Array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_SP_USERS_CALS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y",
		);
		// Groups
		$arComponentParameters["PARAMETERS"]["CALENDAR_SUPERPOSE_GROUPS_CALS"] = Array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_SP_GROUPS_CALS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y",
		);

		$arComponentParameters["PARAMETERS"]["CALENDAR_SUPERPOSE_GROUPS_IBLOCK_ID"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_SP_GROUPS_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock
		);
	}

	/* Reserve Meeting Rooms*/
	$arComponentParameters["PARAMETERS"]["CALENDAR_ALLOW_RES_MEETING"] = Array(
		"PARENT" => "EVENT_CALENDAR_SETTINGS",
		"NAME" => GetMessage("SONET_CALENDAR_ALLOW_RES_MEETING"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y",
	);

	if ($arCurrentValues["CALENDAR_ALLOW_RES_MEETING"] != 'N')
	{
		$arComponentParameters["PARAMETERS"]["CALENDAR_RES_MEETING_IBLOCK_ID"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_RES_MEETING_IBLOCK"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		);

		$arComponentParameters["PARAMETERS"]["CALENDAR_PATH_TO_RES_MEETING"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_PATH_TO_RES_MEETING"),
			"DEFAULT" => "/services/res.php?page=meeting&meeting_id=#id#",
		);

		/* Access to Reserve Meeting */
		$arUserGroups = array();
		$dbGroups = CGroup::GetList($b = "NAME", $o = "ASC", array("ACTIVE" => "Y"));
		while ($arGroup = $dbGroups->GetNext())
			$arUserGroups[$arGroup["ID"]] = "[".$arGroup["ID"]."] ".$arGroup["NAME"];

		$arComponentParameters["PARAMETERS"]["CALENDAR_RES_MEETING_USERGROUPS"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_RES_MEETING_USERGROUPS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arUserGroups,
			"DEFAULT" => Array(1)
		);
	}
	/* Reserve Video-Meeting Rooms*/
	if(IsModuleInstalled("video"))
	{
		if(IsModuleInstalled("intranet"))
		{
			$arComponentParameters["PARAMETERS"]["CALENDAR_ALLOW_VIDEO_MEETING"] = Array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_ALLOW_VIDEO_MEETING"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"REFRESH" => "Y",
			);
		}
		
		$arComponentParameters["PARAMETERS"]["CALENDAR_VIDEO_MEETING_IBLOCK_ID"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_VIDEO_MEETING_IBLOCK"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"REFRESH" => "N",
		);
		$arComponentParameters["PARAMETERS"]["CALENDAR_PATH_TO_VIDEO_MEETING_DETAIL"] = array(
			"PARENT" => "EVENT_CALENDAR_SETTINGS",
			"NAME" => GetMessage("SONET_CALENDAR_PATH_TO_VIDEO_MEETING_DETAIL"),
			"DEFAULT" => "/services/video/detail.php?ID=#ID#",
		);

		

		if ($arCurrentValues["CALENDAR_ALLOW_VIDEO_MEETING"] != 'N' && IsModuleInstalled("intranet"))
		{

			$arComponentParameters["PARAMETERS"]["CALENDAR_PATH_TO_VIDEO_MEETING"] = array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_PATH_TO_VIDEO_MEETING"),
				"DEFAULT" => "/services/video/",
			);


			/* Access to Reserve Video-Meeting */
			$arUserGroups = array();
			$dbGroups = CGroup::GetList($b = "NAME", $o = "ASC", array("ACTIVE" => "Y"));
			while ($arGroup = $dbGroups->GetNext())
				$arUserGroups[$arGroup["ID"]] = "[".$arGroup["ID"]."] ".$arGroup["NAME"];

			$arComponentParameters["PARAMETERS"]["CALENDAR_VIDEO_MEETING_USERGROUPS"] = array(
				"PARENT" => "EVENT_CALENDAR_SETTINGS",
				"NAME" => GetMessage("SONET_CALENDAR_VIDEO_MEETING_USERGROUPS"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arUserGroups,
				"DEFAULT" => Array(1)
			);
		}
	}
	
	$arComponentParameters["PARAMETERS"]["CALENDAR_REINVITE_PARAMS_LIST"] = array(
		"PARENT" => "EVENT_CALENDAR_SETTINGS",
		"NAME" => GetMessage("SONET_CALENDAR_REINVITE_PARAMS_LIST"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => array(
			'name' => GetMessage('SONET_CALENDAR_EV_NAME'),
			'desc' => GetMessage('SONET_CALENDAR_EV_DESC'),
			'from' => GetMessage('SONET_CALENDAR_EV_FROM'),
			'to' => GetMessage('SONET_CALENDAR_EV_TO'),
			'location' => GetMessage('SONET_CALENDAR_LOCATION'),
			'guest_list' => GetMessage('SONET_CALENDAR_GUEST_LIST'),
			'repeating' => GetMessage('SONET_CALENDAR_REPEATING'),
			'meet_text' => GetMessage('SONET_CALENDAR_MEET_TEXT'),
			'importance' => GetMessage('SONET_CALENDAR_IMPORTANCE')
		),
		"DEFAULT" => Array("from", "to", "location")
	);
	/* *** END **** EVENT CALENDAR *** */

	// Tasks
	$arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["task_id"] = array(
		"NAME" => GetMessage("SONET_TASK_VAR"),
		"DEFAULT" => "task_id",
	);
	$arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["view_id"] = array(
		"NAME" => GetMessage("SONET_VIEW_VAR"),
		"DEFAULT" => "view_id",
	);
	$arComponentParameters["PARAMETERS"]["VARIABLE_ALIASES"]["action"] = array(
		"NAME" => GetMessage("SONET_ACTION_VAR"),
		"DEFAULT" => "action",
	);

	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["user_tasks"] = array(
		"NAME" => GetMessage("SONET_SEF_USER_TASKS"),
		"DEFAULT" => "user/#user_id#/tasks/",
		"VARIABLES" => array("user_id"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["user_tasks_task"] = array(
		"NAME" => GetMessage("SONET_SEF_USER_TASKS_TASK"),
		"DEFAULT" => "user/#user_id#/tasks/task/#action#/#task_id#/",
		"VARIABLES" => array("user_id", "action", "task_id"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["user_tasks_view"] = array(
		"NAME" => GetMessage("SONET_SEF_USER_TASKS_VIEW"),
		"DEFAULT" => "user/#user_id#/tasks/view/#action#/#view_id#/",
		"VARIABLES" => array("user_id", "action", "view_id"),
	);

	$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_TASKS"] = Array(
		"NAME" => GetMessage("SONET_PATH_TO_GROUP_TASKS"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "",
		"COLS" => 25,
		"PARENT" => "URL_TEMPLATES",
	);
	$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_TASKS_TASK"] = Array(
		"NAME" => GetMessage("SONET_PATH_TO_GROUP_TASKS_TASK"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "",
		"COLS" => 25,
		"PARENT" => "URL_TEMPLATES",
	);
	$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_TASKS_VIEW"] = Array(
		"NAME" => GetMessage("SONET_PATH_TO_GROUP_TASKS_VIEW"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "",
		"COLS" => 25,
		"PARENT" => "URL_TEMPLATES",
	);

	$arIBlockTypeTask = array();
	$rsIBlockTypeTask = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
	while ($arrTask=$rsIBlockTypeTask->Fetch())
	{
		if($arTask=CIBlockType::GetByIDLang($arrTask["ID"], LANGUAGE_ID))
			$arIBlockTypeTask[$arrTask["ID"]] = "[".$arrTask["ID"]."] ".$arTask["NAME"];
	}

	$arIBlockTask=array();
	$rsIBlockTask = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["TASK_IBLOCK_TYPE"], "ACTIVE"=>"Y"));
	while($arrTask=$rsIBlockTask->Fetch())
		$arIBlockTask[$arrTask["ID"]] = "[".$arrTask["ID"]."] ".$arrTask["NAME"];

	$arTasksFields = array(
		"ID" => "[ID] ".GetMessage("INTL_TF_ID"),
		"NAME" => "[NAME] ".GetMessage("INTL_TF_NAME"),
		"CODE" => "[CODE] ".GetMessage("INTL_TF_CODE"),
		"XML_ID" => "[XML_ID] ".GetMessage("INTL_TF_XML_ID"),
		"MODIFIED_BY" => "[MODIFIED_BY] ".GetMessage("INTL_TF_MODIFIED_BY"),
		"DATE_CREATE" => "[DATE_CREATE] ".GetMessage("INTL_TF_DATE_CREATE"),
		"CREATED_BY" => "[CREATED_BY] ".GetMessage("INTL_TF_CREATED_BY"),
		"DATE_ACTIVE_FROM" => "[DATE_ACTIVE_FROM] ".GetMessage("INTL_TF_DATE_ACTIVE_FROM"),
		"DATE_ACTIVE_TO" => "[DATE_ACTIVE_TO] ".GetMessage("INTL_TF_DATE_ACTIVE_TO"),
		"IBLOCK_SECTION" => "[IBLOCK_SECTION] ".GetMessage("INTL_TF_IBLOCK_SECTION"),
		"DETAIL_TEXT" => "[DETAIL_TEXT] ".GetMessage("INTL_TF_DETAIL_TEXT"),
	);

	$dbTasksCustomProps = CIBlockProperty::GetList(
		array("sort" => "asc", "name" => "asc"),
		array("ACTIVE" => "Y", "IBLOCK_ID" => $arCurrentValues["TASK_IBLOCK_ID"])
	);
	while ($arTasksCustomProp = $dbTasksCustomProps->Fetch())
	{
		$ind = ((StrLen($arTasksCustomProp["CODE"]) > 0) ? $arTasksCustomProp["CODE"] : $arTasksCustomProp["ID"]);
		$arTasksFields[StrToUpper($ind)] = "[".$ind."] ".$arTasksCustomProp["NAME"];
	}

	$arComponentParameters["GROUPS"]["TASKS"] = array("NAME" => GetMessage("INT_TASKS_GROUP"));

	$arComponentParameters["PARAMETERS"]["TASK_IBLOCK_TYPE"] = Array(
		"PARENT" => "TASKS",
		"NAME" => GetMessage("INTL_TASK_IBLOCK_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => $arIBlockTypeTask,
		"REFRESH" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["TASK_IBLOCK_ID"] = array(
		"PARENT" => "TASKS",
		"NAME" => GetMessage("INTL_TASK_IBLOCK"),
		"TYPE" => "LIST",
		"VALUES" => $arIBlockTask,
		"REFRESH" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["TASKS_FIELDS_SHOW"] = array(
		"PARENT" => "TASKS",
		"NAME" => GetMessage("INTL_TASKS_FIELDS_SHOW"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arTasksFields,
	);

	if (CModule::IncludeModule("forum"))
	{
		$arForumTask = array();
		$db_resTask = CForumNew::GetListEx();
		if ($db_resTask && $resTask = $db_resTask->GetNext())
		{
			do
			{
				$arForumTask[$resTask["ID"]] = "[".$resTask["ID"]."] ".$resTask["NAME"];
			} while($resTask = $db_resTask->GetNext());
		}

		$arComponentParameters["PARAMETERS"]["TASK_FORUM_ID"] = array(
			"PARENT" => "TASKS",
			"NAME" => GetMessage("INTL_TASK_FORUM_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arForumTask,
			"REFRESH" => "N",
			"MULTIPLE" => "N");
	}
}
$arIBlockType = array();
if(CModule::IncludeModule("iblock"))
{
	$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
	while ($arr=$rsIBlockType->Fetch())
	{
		if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
		{
			$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
		}
	}

	$arIBlock=array();
	$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["FILES_USER_IBLOCK_TYPE"], "ACTIVE"=>"Y"));
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

	if (IsModuleInstalled("webdav"))
	{
		$arComponentParameters["GROUPS"]["WEBDAV_SETTINGS"] = array(
			"NAME" => GetMessage("SONET_WEBDAV_SETTINGS"));
		$arComponentParameters["PARAMETERS"]["FILES_USER_IBLOCK_TYPE"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y");
		$arComponentParameters["PARAMETERS"]["FILES_USER_IBLOCK_ID"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock);
		$arComponentParameters["PARAMETERS"]["FILES_USE_AUTH"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_FILES_USE_AUTH"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y");
		$arComponentParameters["PARAMETERS"]["FILE_NAME_FILE_PROPERTY"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_NAME_FILE_PROPERTY"),
			"TYPE" => "STRING",
			"DEFAULT" => "FILE");
		$arComponentParameters["PARAMETERS"]["FILES_UPLOAD_MAX_FILESIZE"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => str_replace("#upload_max_filesize#", ini_get('upload_max_filesize'), GetMessage("SONET_UPLOAD_MAX_FILESIZE")),
			"TYPE" => "STRING",
			"DEFAULT" => intVal(ini_get('upload_max_filesize')));
		$arComponentParameters["PARAMETERS"]["FILES_UPLOAD_MAX_FILE"] = array(
			"PARENT" => "WEBDAV_SETTINGS",
			"NAME" => GetMessage("SONET_UPLOAD_MAX_FILE"),
			"TYPE" => "STRING",
			"DEFAULT" => 4);
		if (IsModuleInstalled("forum"))
		{
			$arComponentParameters["PARAMETERS"]["FILES_USE_COMMENTS"] = array(
					"PARENT" => "WEBDAV_SETTINGS",
					"NAME" => GetMessage("SONET_USE_COMMENTS"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "N",
					"REFRESH" => "Y");

			if ($arCurrentValues["FILES_USE_COMMENTS"]=="Y")
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
				$arComponentParameters["PARAMETERS"]["FILES_FORUM_ID"] = Array(
					"PARENT" => "WEBDAV_SETTINGS",
					"NAME" => GetMessage("SONET_FORUM_ID"),
					"TYPE" => "LIST",
					"VALUES" => $arForum,
					"DEFAULT" => $fid);
				$arComponentParameters["PARAMETERS"]["FILES_USE_CAPTCHA"] = Array(
					"PARENT" => "WEBDAV_SETTINGS",
					"NAME" => GetMessage("SONET_USE_CAPTCHA"),
					"TYPE" => "CHECKBOX",
					"MULTIPLE" => "N",
					"DEFAULT" => "Y");
			}
		}
	}
}
if(CModule::IncludeModule("blog"))
{
	$arBlogGroup = array();
	$dbBlogGr = CBlogGroup::GetList();
	while($arBlogGr = $dbBlogGr->GetNext())
	{
		$arBlogGroup[$arBlogGr["ID"]] = "(".$arBlogGr["SITE_ID"].") ".$arBlogGr["NAME"];
	}

	if(!empty($arBlogGroup))
	{
		$arComponentParameters["PARAMETERS"]["BLOG_GROUP_ID"] = array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_BLOG_GROUP_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arBlogGroup,
			"REFRESH" => "N",
			"MULTIPLE" => "N",
			);
	}
}

if (CModule::IncludeModule("forum"))
{
	$arForum = array();
	$db_res = CForumNew::GetListEx();
	if ($db_res && $res = $db_res->Fetch())
	{
		do
		{
			$arForum[$res["ID"]] = "[".$res["ID"]."] ".$res["NAME"];
		} while($res = $db_res->Fetch());
	}

	if (!empty($arForum))
	{
	$arComponentParameters["GROUPS"]["FORUM_SETTINGS"] = array(
		"NAME" => GetMessage("SONET_FORUM_SETTINGS"));
	$arComponentParameters["PARAMETERS"]["FORUM_ID"] = array(
		"PARENT" => "FORUM_SETTINGS",
		"NAME" => GetMessage("SONET_FORUM_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arForum,
		"REFRESH" => "N",
		"MULTIPLE" => "N");
	$arThemesMessages = array(
		"beige" => GetMessage("F_THEME_BEIGE"),
		"blue" => GetMessage("F_THEME_BLUE"),
		"fluxbb" => GetMessage("F_THEME_FLUXBB"),
		"gray" => GetMessage("F_THEME_GRAY"),
		"green" => GetMessage("F_THEME_GREEN"),
		"orange" => GetMessage("F_THEME_ORANGE"),
		"red" => GetMessage("F_THEME_RED"),
		"white" => GetMessage("F_THEME_WHITE"));

	$arThemes = array();
	$dir = trim(preg_replace("'[\\\\/]+'", "/", $_SERVER['DOCUMENT_ROOT']."/bitrix/components/bitrix/forum/templates/.default/themes/"));
	if (is_dir($dir) && $directory = opendir($dir)):

		while (($file = readdir($directory)) !== false)
		{
			if ($file != "." && $file != ".." && is_dir($dir.$file))
				$arThemes[$file] = (!empty($arThemesMessages[$file]) ? $arThemesMessages[$file] : strtoupper(substr($file, 0, 1)).strtolower(substr($file, 1)));
		}
		closedir($directory);
	endif;
	$arComponentParameters["PARAMETERS"]["FORUM_THEME"] = array(
		"PARENT" => "FORUM_SETTINGS",
		"NAME" => GetMessage("F_THEMES"),
		"TYPE" => "LIST",
		"VALUES" => $arThemes,
		"MULTIPLE" => "N",
		"DEFAULT" => "blue",
		"ADDITIONAL_VALUES" => "Y");
	}
}


if(!empty($arIBlockType) || CModule::IncludeModule("iblock"))
{
	if (empty($arIBlockType))
	{
		$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
		while ($arr=$rsIBlockType->Fetch())
		{
			if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
			{
				$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
			}
		}
	}

	$arIBlock=array();
	$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["PHOTO_USER_IBLOCK_TYPE"], "ACTIVE"=>"Y"));
	while($arr=$rsIBlock->Fetch())
	{
		$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
	}
	$arIBlockGroup=array();
	$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["PHOTO_GROUP_IBLOCK_TYPE"], "ACTIVE"=>"Y"));
	while($arr=$rsIBlock->Fetch())
	{
		$arIBlockGroup[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
	}

	if (IsModuleInstalled("photogallery"))
	{
		$arComponentParameters["GROUPS"]["PHOTO_SETTINGS"] = array(
			"NAME" => GetMessage("SONET_PHOTO_SETTINGS"));
		$arComponentParameters["PARAMETERS"]["PHOTO_USER_IBLOCK_TYPE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_IBLOCK_TYPE").GetMessage("SONET_USER"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y");
		$arComponentParameters["PARAMETERS"]["PHOTO_USER_IBLOCK_ID"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_IBLOCK_ID").GetMessage("SONET_USER"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock);
/*		$arComponentParameters["PARAMETERS"]["PHOTO_GROUP_IBLOCK_TYPE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_IBLOCK_TYPE").GetMessage("SONET_GROUP"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y");
		$arComponentParameters["PARAMETERS"]["PHOTO_GROUP_IBLOCK_ID"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_IBLOCK_ID").GetMessage("SONET_GROUP"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockGroup);
*/
		$arComponentParameters["PARAMETERS"]["PHOTO_MODERATION"] = array(
			"PARENT" => "PHOTO_SETTINGS",
	        "NAME" => GetMessage("P_GLOBAL_MODERATE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N");

		$arComponentParameters["PARAMETERS"]["PHOTO_SECTION_PAGE_ELEMENTS"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_PAGE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "15");
		$arComponentParameters["PARAMETERS"]["PHOTO_ELEMENTS_PAGE_ELEMENTS"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("IBLOCK_ELEMENTS_PAGE_ELEMENTS"),
			"TYPE" => "STRING",
			"DEFAULT" => '50');
		$arComponentParameters["PARAMETERS"]["PHOTO_SLIDER_COUNT_CELL"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_SLIDER_COUNT_CELL"),
			"TYPE" => "STRING",
			"DEFAULT" => "3");
		$arComponentParameters["PARAMETERS"]["CELL_COUNT"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_TEMPLATE_CELL_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "0");
		$arComponentParameters["PARAMETERS"]["PHOTO_ALBUM_PHOTO_THUMBS_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_ALBUM_PHOTO_THUMBS_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "150");
		$arComponentParameters["PARAMETERS"]["PHOTO_ALBUM_PHOTO_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_ALBUM_PHOTO_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "150");

		$arComponentParameters["PARAMETERS"]["PHOTO_THUMBS_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_THUMBS_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "250");
		$arComponentParameters["PARAMETERS"]["PHOTO_PREVIEW_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_PREVIEW_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "700");
		$arComponentParameters["PARAMETERS"]["PHOTO_ORIGINAL_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ORIGINAL_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "0");
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_MIN_PICTURE_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_WATERMARK_MIN_PICTURE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "200");

		$arFiles = array(
			"" => "...");
		$path = str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT']."/".BX_ROOT."/modules/photogallery/fonts/");
		CheckDirPath($path);
		$handle = opendir($path);
		$file_exist = false;
		if ($handle)
		{
			while($file = readdir($handle))
			{
				if ($file == "." || $file == ".." || !is_file($path.$file))
					continue;
				$file_exist = true;
				$arFiles[$file] = $file;
			}
		}
		if (!$file_exist)
		{
			$arFiles = array(
				"" => GetMessage("SONET_FONTS_NONE"));
		}

		$arComponentParameters["PARAMETERS"]["PHOTO_PATH_TO_FONT"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_PATH_TO_FONT"),
			"TYPE" => "LIST",
			"VALUES" => $arFiles,
			"DEFAULT" => array(""),
			"MULTIPLE" => "N");
		$arComponentParameters["PARAMETERS"]["PHOTO_SHOW_WATERMARK"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_SHOW_WATERMARK"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y");
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_RULES"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_RULES"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"USER" => GetMessage("P_WATERMARK_RULES_USER"),
				"ALL" => GetMessage("P_WATERMARK_RULES_ALL")),
			"DEFAULT" => "USER",
			"REFRESH" => "Y");
		if ($arCurrentValues["PHOTO_WATERMARK_RULES"] == "ALL")
		{
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_TYPE"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_WATERMARK_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"TEXT" => GetMessage("P_WATERMARK_TYPE_TEXT"),
			"PICTURE" => GetMessage("P_WATERMARK_TYPE_PICTURE")),
		"DEFAULT" => "PICTURE",
		"REFRESH" => "Y");
		if ($arCurrentValues["PHOTO_WATERMARK_TYPE"] == "TEXT")
		{
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_TEXT"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_TEXT"),
			"TYPE" => "STRING",
			"VALUES" => "");
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_COLOR"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_COLOR"),
			"TYPE" => "STRING",
			"VALUES" => "FF00EE");
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_SIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_SIZE"),
			"TYPE" => "STRING",
			"VALUES" => "10");
		}
		else
		{
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_FILE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_FILE"),
			"TYPE" => "STRING",
			"VALUES" => "");
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_FILE_ORDER"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_FILE_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"usual" => GetMessage("P_WATERMARK_FILE_ORDER_USUAL"),
				"resize" => GetMessage("P_WATERMARK_FILE_ORDER_RESIZE"),
				"repeat" => GetMessage("P_WATERMARK_FILE_ORDER_REPEAT")),
			"DEFAULT" => "usual");

		}

		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_POSITION"] = array(
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
		$arComponentParameters["PARAMETERS"]["PHOTO_WATERMARK_TRANSPARENCY"] = array(
		"PARENT" => "PHOTO_SETTINGS",
		"NAME" => GetMessage("P_WATERMARK_TRANSPARENCY"),
		"TYPE" => "STRING",
		"DEFAULT" => "20");
		}
		$arComponentParameters["PARAMETERS"]["PHOTO_PHOTO_UPLOAD_MAX_FILESIZE"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => str_replace("#upload_max_filesize#", ini_get('upload_max_filesize'), GetMessage("SONET_UPLOAD_MAX_FILESIZE")),
			"TYPE" => "STRING",
			"DEFAULT" => intVal(ini_get('upload_max_filesize')));

		$arComponentParameters["PARAMETERS"]["PHOTO_USE_RATING"] = array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("SONET_USE_RATING"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y");

		if($arCurrentValues["PHOTO_USE_RATING"]=="Y")
		{
			$arComponentParameters["PARAMETERS"]["PHOTO_MAX_VOTE"] = array(
				"PARENT" => "PHOTO_SETTINGS",
				"NAME" => GetMessage("IBLOCK_MAX_VOTE"),
				"TYPE" => "STRING",
				"DEFAULT" => "5");
			$arComponentParameters["PARAMETERS"]["PHOTO_VOTE_NAMES"] = array(
				"PARENT" => "PHOTO_SETTINGS",
				"NAME" => GetMessage("IBLOCK_VOTE_NAMES"),
				"TYPE" => "STRING",
				"VALUES" => array(),
				"MULTIPLE" => "Y",
				"DEFAULT" => array("1","2","3","4","5"),
				"ADDITIONAL_VALUES" => "Y");
			$arComponentParameters["PARAMETERS"]["PHOTO_DISPLAY_AS_RATING"] = array(
				"NAME" => GetMessage("TP_CBIV_DISPLAY_AS_RATING"),
				"PARENT" => "PHOTO_SETTINGS",
				"TYPE" => "LIST",
				"VALUES" => array(
					"rating" => GetMessage("TP_CBIV_RATING"),
					"vote_avg" => GetMessage("TP_CBIV_AVERAGE"),
				),
				"DEFAULT" => "rating");
		}
		if (IsModuleInstalled("forum"))
		{
			$arComponentParameters["PARAMETERS"]["PHOTO_USE_COMMENTS"] = array(
					"PARENT" => "PHOTO_SETTINGS",
					"NAME" => GetMessage("SONET_USE_COMMENTS"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "N",
					"REFRESH" => "Y");

			if ($arCurrentValues["PHOTO_USE_COMMENTS"]=="Y")
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
				$arComponentParameters["PARAMETERS"]["PHOTO_FORUM_ID"] = Array(
					"PARENT" => "PHOTO_SETTINGS",
					"NAME" => GetMessage("SONET_FID"),
					"TYPE" => "LIST",
					"VALUES" => $arForum,
					"DEFAULT" => $fid);
				$arComponentParameters["PARAMETERS"]["PHOTO_USE_CAPTCHA"] = Array(
					"PARENT" => "PHOTO_SETTINGS",
					"NAME" => GetMessage("SONET_USE_CAPTCHA"),
					"TYPE" => "CHECKBOX",
					"MULTIPLE" => "N",
					"DEFAULT" => "Y");
			}
		}
	}
}

$arComponentParameters["PARAMETERS"]["ALLOW_GROUP_CREATE_REDIRECT_REQUEST"] = Array(
	"PARENT" => "URL_TEMPLATES",
	"NAME" => GetMessage("SONET_ALLOW_GROUP_CREATE_REDIRECT_REQUEST"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
	"REFRESH" => "Y",
);

if ($arCurrentValues["ALLOW_GROUP_CREATE_REDIRECT_REQUEST"] == 'Y')
{
	$arComponentParameters["PARAMETERS"]["GROUP_CREATE_REDIRECT_REQUEST"] = array(
		"PARENT" => "URL_TEMPLATES",
		"NAME" => GetMessage("SONET_GROUP_CREATE_REDIRECT_REQUEST"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "/workgroups/group/#group_id#/user_search/",
		"COLS" => 25,
	);
}


if (IsModuleInstalled("search"))
{
	$arComponentParameters["GROUPS"]["SEARCH_SETTINGS"] = array(
		"NAME" => GetMessage("SONET_SEARCH_SETTINGS"));
	$arComponentParameters["PARAMETERS"]["SEARCH_DEFAULT_SORT"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_DEFAULT_SORT"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"DEFAULT" => "rank",
		"VALUES" => array(
			"rank" => GetMessage("SONET_SEARCH_DEFAULT_SORT_RANK"),
			"date" => GetMessage("SONET_SEARCH_DEFAULT_SORT_DATE"),
		),
	);
	$arComponentParameters["PARAMETERS"]["SEARCH_PAGE_RESULT_COUNT"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_PAGE_RESULT_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => 10);	
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_PAGE_ELEMENTS"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_PAGE_ELEMENTS"),
		"TYPE" => "STRING",
		"DEFAULT" => 100);
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_PERIOD"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_PERIOD"),
		"TYPE" => "STRING",
		"DEFAULT" => "");
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_FONT_MAX"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_FONT_MAX"),
		"TYPE" => "STRING",
		"DEFAULT" => "50");
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_FONT_MIN"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_FONT_MIN"),
		"TYPE" => "STRING",
		"DEFAULT" => "10");
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_COLOR_NEW"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_COLOR_NEW"),
		"TYPE" => "STRING",
		"DEFAULT" => "3E74E6");
	$arComponentParameters["PARAMETERS"]["SEARCH_TAGS_COLOR_OLD"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_TAGS_COLOR_OLD"),
		"TYPE" => "STRING",
		"DEFAULT" => "C0C0C0");
	$arComponentParameters["PARAMETERS"]["SEARCH_FILTER_NAME"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_FILTER_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => "sonet_search_filter");
	$arComponentParameters["PARAMETERS"]["SEARCH_FILTER_DATE_NAME"] = Array(
		"PARENT" => "SEARCH_SETTINGS",
		"NAME" => GetMessage("SONET_SEARCH_FILTER_DATE_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => "sonet_search_filter_date");
}

$arSMThemesMessages = array(
		"grey" => GetMessage("SONET_SM_THEME_GREY"),
		"red" => GetMessage("SONET_SM_THEME_RED"),
		"green" => GetMessage("SONET_SM_THEME_GREEN"),
		"blue" => GetMessage("SONET_SM_THEME_BLUE"),
		"lightblue" => GetMessage("SONET_SM_THEME_LIGHTBLUE"),
		"brown" => GetMessage("SONET_SM_THEME_BROWN"),
);

$arSMThemes = array();
$dir = trim(preg_replace("'[\\\\/]+'", "/", $_SERVER['DOCUMENT_ROOT']."/bitrix/components/bitrix/socialnetwork.menu/templates/.default/themes/"));
if (is_dir($dir) && $directory = opendir($dir)):
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arSMThemes[$file] = (!empty($arSMThemesMessages[$file]) ? $arSMThemesMessages[$file] : strtoupper(substr($file, 0, 1)).strtolower(substr($file, 1)));
	}
	closedir($directory);
endif;
	
$tmp_site = ($_REQUEST["site"] <> '' ? $_REQUEST["site"] : ($_REQUEST["src_site"] <> '' ? $_REQUEST["src_site"] : false));


$dbSiteRes = CSite::GetTemplateList($tmp_site);
while($arSiteRes = $dbSiteRes->Fetch())
{
	if (empty($arSiteRes["CONDITION"]))
	{
		$site_template = $arSiteRes["TEMPLATE"];
		break;
	}
}

if (strpos($site_template, "bright") === 0)
	$DefaultSMTheme = "grey";
else
{
	if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite($tmp_site))
		$theme_id = COption::GetOptionString("main", "wizard_".$site_template."_theme_id_extranet");
	else
		$theme_id = COption::GetOptionString("main", "wizard_".$site_template."_theme_id");

	if (strlen($theme_id) > 0)
		$DefaultSMTheme = $theme_id;
	else
		$DefaultSMTheme = "grey";
}
				
$arComponentParameters["PARAMETERS"]["SM_THEME"] = Array(
	"NAME" => GetMessage("SONET_SM_THEME"),
	"TYPE" => "LIST",
	"VALUES" => $arSMThemes,
	"MULTIPLE" => "N",
	"DEFAULT" => $DefaultSMTheme,
	"PARENT" => "VISUAL",
);

$arComponentParameters["PARAMETERS"]["USE_MAIN_MENU"] = Array(
	"NAME" => GetMessage("SONET_USE_MAIN_MENU"),
	"TYPE" => "CHECKBOX",
	"MULTIPLE" => "N",
	"VALUE" => "Y",
	"DEFAULT" =>"N",
	"REFRESH" => "Y",	
	"PARENT" => "VISUAL",
);

if ($arCurrentValues["USE_MAIN_MENU"] == 'Y')
{
	$site = ($_REQUEST["site"] <> ''? $_REQUEST["site"] : ($_REQUEST["src_site"] <> ''? $_REQUEST["src_site"] : false));
	$arMenu = GetMenuTypes($site);

	$arComponentParameters["PARAMETERS"]["MAIN_MENU_TYPE"] = Array(
		"NAME" => GetMessage("SONET_MAIN_MENU_TYPE"),
		"TYPE" => "LIST",
		"DEFAULT"=>'left',
		"VALUES" => $arMenu,
		"ADDITIONAL_VALUES"	=> "Y",
		"PARENT" => "VISUAL",
		"COLS" => 45
	);
}

$arComponentParameters["PARAMETERS"]["USE_SHARE"] = Array(
	"NAME" => GetMessage("SONET_USE_SHARE"),
	"TYPE" => "CHECKBOX",
	"MULTIPLE" => "N",
	"VALUE" => "Y",
	"DEFAULT" =>"N",
	"REFRESH" => "Y",	
	"PARENT" => "VISUAL",
);

if ($arCurrentValues["USE_SHARE"] == "Y")
{
	$arComponentParameters["PARAMETERS"]["SHARE_HIDE"] = array(
		"NAME" => GetMessage("SONET_SHARE_HIDE"),
		"TYPE" => "CHECKBOX",
		"VALUE" => "Y",
		"DEFAULT" => "N",
		"PARENT" => "VISUAL",		
	);
	
	$arComponentParameters["PARAMETERS"]["SHARE_TEMPLATE"] = array(
		"NAME" => GetMessage("SONET_SHARE_TEMPLATE"),
		"DEFAULT" => "",
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"COLS" => 25,
		"REFRESH"=> "Y",
		"PARENT" => "VISUAL",
	);
	
	if (strlen(trim($arCurrentValues["SHARE_TEMPLATE"])) <= 0)
		$shareComponentTemlate = false;
	else
		$shareComponentTemlate = trim($arCurrentValues["SHARE_TEMPLATE"]);

	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/main.share/util.php");

	$arHandlers = __bx_share_get_handlers($shareComponentTemlate);

	$arComponentParameters["PARAMETERS"]["SHARE_HANDLERS"] = array(
		"NAME" => GetMessage("SONET_SHARE_SYSTEM"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arHandlers["HANDLERS"],
		"DEFAULT" => $arHandlers["HANDLERS_DEFAULT"],
		"PARENT" => "VISUAL",		
	);

	$arComponentParameters["PARAMETERS"]["SHARE_SHORTEN_URL_LOGIN"] = array(
		"NAME" => GetMessage("SONET_SHARE_SHORTEN_URL_LOGIN"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "VISUAL",		
	);
	
	$arComponentParameters["PARAMETERS"]["SHARE_SHORTEN_URL_KEY"] = array(
		"NAME" => GetMessage("SONET_SHARE_SHORTEN_URL_KEY"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "VISUAL",
	);
}

$arComponentParameters["PARAMETERS"]["ALLOW_RATING_SORT"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("SONET_ALLOW_RATING_SORT"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N", 
	"REFRESH" => "Y"
);
$arComponentParameters["PARAMETERS"]["SHOW_RATING"] = array(
	"PARENT" => "VISUAL",
	"NAME" => GetMessage("SONET_SHOW_RATING"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N", 
	"REFRESH" => "Y"
);
if ($arCurrentValues["SHOW_RATING"] == "Y" || $arCurrentValues["ALLOW_RATING_SORT"] == "Y")
{
	$arRatingsList = array();
	$db_res = CRatings::GetList($aSort = array("ID" => "ASC"), array("ACTIVE" => "Y", "ENTITY_ID" => "USER"));
	while ($res = $db_res->Fetch())
		$arRatingsList[$res["ID"]] = "[ ".$res["ID"]." ] ".$res["NAME"];
	
	$arComponentParameters["PARAMETERS"]["RATING_ID"] = array(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("SONET_RATING_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arRatingsList,
		"DEFAULT" => "",
		"MULTIPLE"	=> "Y"
	);
}
$arComponentParameters["PARAMETERS"]["ALLOW_POST_MOVE"] = Array(
		  	"NAME" => GetMessage("BPE_ALLOW_POST_MOVE"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"REFRESH" => "Y",
		);

if ($arCurrentValues["ALLOW_POST_MOVE"] == "Y")
{
	$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_POST"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_BLOG_POST"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);	
	$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_POST_EDIT"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_BLOG_POST_EDIT"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);	
	$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_DRAFT"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_BLOG_DRAFT"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);	
	$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_BLOG"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_BLOG_BLOG"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);
	$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_POST"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_GROUP_POST"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);	
	$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_POST_EDIT"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_GROUP_POST_EDIT"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);	
	$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_DRAFT"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_GROUP_DRAFT"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);	
	$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_BLOG"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_GROUP_BLOG"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);
}
?>