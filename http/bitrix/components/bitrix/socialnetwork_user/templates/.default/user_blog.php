<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.user_menu",
	"",
	Array(
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_USER_EDIT" => $arResult["PATH_TO_USER_PROFILE_EDIT"],
		"PATH_TO_USER_FRIENDS" => $arResult["PATH_TO_USER_FRIENDS"],
		"PATH_TO_USER_GROUPS" => $arResult["PATH_TO_USER_GROUPS"],
		"PATH_TO_USER_FRIENDS_ADD" => $arResult["PATH_TO_USER_FRIENDS_ADD"],
		"PATH_TO_USER_FRIENDS_DELETE" => $arResult["PATH_TO_USER_FRIENDS_DELETE"],
		"PATH_TO_MESSAGES_INPUT" => $arResult["PATH_TO_MESSAGES_INPUT"],
		"PATH_TO_MESSAGE_FORM" => $arResult["PATH_TO_MESSAGE_FORM"],
		"PATH_TO_USER_BLOG" => $arResult["PATH_TO_USER_BLOG"],
		"PATH_TO_USER_PHOTO" => $arResult["PATH_TO_USER_PHOTO"],
		"PATH_TO_USER_FORUM" => $arResult["PATH_TO_USER_FORUM"],
		"PATH_TO_USER_CALENDAR" => $arResult["PATH_TO_USER_CALENDAR"],
		"PATH_TO_USER_FILES" => $arResult["PATH_TO_USER_FILES"],
		"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
		"PATH_TO_USER_CONTENT_SEARCH" => $arResult["PATH_TO_USER_CONTENT_SEARCH"],
		"ID" => $arResult["VARIABLES"]["user_id"],
		"PAGE_ID" => "user_blog",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
	),
	$component
);
?>

<?$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.user_profile", 
	"short", 
	Array(
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_USER_EDIT" => $arResult["PATH_TO_USER_PROFILE_EDIT"],
		"PATH_TO_USER_FRIENDS" => $arResult["PATH_TO_USER_FRIENDS"],
		"PATH_TO_USER_GROUPS" => $arResult["PATH_TO_USER_GROUPS"],
		"PATH_TO_USER_FRIENDS_ADD" => $arResult["PATH_TO_USER_FRIENDS_ADD"],
		"PATH_TO_USER_FRIENDS_DELETE" => $arResult["PATH_TO_USER_FRIENDS_DELETE"],
		"PATH_TO_MESSAGE_FORM" => $arResult["PATH_TO_MESSAGE_FORM"],
		"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_MESSAGES_USERS_MESSAGES" => $arResult["PATH_TO_MESSAGES_USERS_MESSAGES"],
		"PATH_TO_USER_SETTINGS_EDIT" => $arResult["PATH_TO_USER_SETTINGS_EDIT"],
		"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
		"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
		"PATH_TO_USER_FEATURES" => $arResult["PATH_TO_USER_FEATURES"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"SET_NAV_CHAIN" => "N",
		"SET_TITLE" => "N", 
		"USER_PROPERTY_MAIN" => $arResult["USER_PROPERTY_MAIN"],
		"USER_PROPERTY_CONTACT" => $arResult["USER_PROPERTY_CONTACT"],
		"USER_PROPERTY_PERSONAL" => $arResult["USER_PROPERTY_PERSONAL"],
		"USER_FIELDS_MAIN" => $arResult["USER_FIELDS_MAIN"],
		"USER_FIELDS_CONTACT" => $arResult["USER_FIELDS_CONTACT"],
		"USER_FIELDS_PERSONAL" => $arResult["USER_FIELDS_PERSONAL"],
		"PATH_TO_USER_FEATURES" => $arResult["PATH_TO_USER_FEATURES"],
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
		"SHORT_FORM" => "Y",
		"ITEMS_COUNT" => $arParams["ITEM_MAIN_COUNT"],
		"ID" => $arResult["VARIABLES"]["user_id"],
		"PATH_TO_GROUP_REQUEST_GROUP_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_GROUP_SEARCH"], 
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"], 
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
	),
	$component,
	array("HIDE_ICONS" => "Y") 
);
?>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
<td align="left" valign="top"><?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.blog.menu",
	"",
	Array(
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_POST_EDIT" => $arResult["PATH_TO_USER_BLOG_POST_EDIT"],
		"PATH_TO_DRAFT" => $arResult["PATH_TO_USER_BLOG_DRAFT"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["blog_page"],
		"POST_VAR" => $arResult["ALIASES"]["post_id"],
		"PATH_TO_BLOG" => $arResult["PATH_TO_USER_BLOG"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"PATH_TO_MODERATION" => $arResult["PATH_TO_USER_BLOG_MODERATION"],
	),
	$component
);
?></td>
<td align="right" valign="top"><?$APPLICATION->IncludeComponent(
	"bitrix:blog.rss.link",
	"group",
	Array(
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["blog_page"],
		"POST_VAR" => $arResult["ALIASES"]["post_id"],
		"PATH_TO_RSS" => $arResult["PATH_TO_USER_BLOG_RSS"], 
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"RSS1" => "N", 
		"RSS2" => "Y", 
		"ATOM" => "N", 
		"MODE" => "B",
		"PARAM_GROUP_ID" => $arParams["BLOG_GROUP_ID"] 
	),
	$component
);?></td>
</tr>
</table>
<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.blog.blog",
	"",
	Array(
		"MESSAGE_COUNT" 		=> "25", 
		"DATE_TIME_FORMAT" 		=> $arParams["DATE_TIME_FORMAT"], 
		"PATH_TO_BLOG" 			=> $arResult["PATH_TO_USER_BLOG"], 
		"PATH_TO_BLOG_CATEGORY"	=> $APPLICATION->GetCurPageParam("category=#category_id#", Array("category")), 
		"PATH_TO_POST" 			=> $arResult["PATH_TO_USER_BLOG_POST"], 
		"PATH_TO_POST_EDIT" 	=> $arResult["PATH_TO_USER_BLOG_POST_EDIT"], 
		"PATH_TO_USER" 			=> $arResult["PATH_TO_USER"], 
		"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"], 
		"PATH_TO_MESSAGES_CHAT"	=> $arResult["PATH_TO_MESSAGES_CHAT"],
		"USER_ID" 				=> $arResult["VARIABLES"]["user_id"], 
		"YEAR" 					=> $year, 
		"MONTH" 				=> $month, 
		"DAY" 					=> $day, 
		"CATEGORY_ID" 			=> $_REQUEST["category"], 
		"CACHE_TYPE" 			=> $arResult["CACHE_TYPE"], 
		"CACHE_TIME" 			=> $arResult["CACHE_TIME"], 
		"CACHE_TIME_LONG" 		=> "604800", 
		"SET_NAV_CHAIN" 		=> "N", 
		"SET_TITLE" 			=> $arResult["SET_TITLE"], 
		"FILTER_NAME" 			=> "", 
		"NAV_TEMPLATE" 			=> "", 
		"POST_PROPERTY_LIST" 	=> array(),
		"USER_VAR" 				=> $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" 				=> $arResult["ALIASES"]["blog_page"],
		"POST_VAR" 				=> $arResult["ALIASES"]["post_id"],
		"GROUP_ID" 				=> $arParams["BLOG_GROUP_ID"],
		"NAME_TEMPLATE" 		=> $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" 			=> $arParams["SHOW_LOGIN"],
		"DATE_TIME_FORMAT" 		=> $arResult["DATE_TIME_FORMAT"],		
		"SHOW_YEAR" 			=> $arParams["SHOW_YEAR"],		
		"PATH_TO_CONPANY_DEPARTMENT" 	=> $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"PATH_TO_VIDEO_CALL" 	=> $arResult["PATH_TO_VIDEO_CALL"],
		"USE_SHARE" 			=> $arParams["USE_SHARE"],
		"SHARE_HIDE" 			=> $arParams["SHARE_HIDE"],
		"SHARE_TEMPLATE" 		=> $arParams["SHARE_TEMPLATE"],
		"SHARE_HANDLERS" 		=> $arParams["SHARE_HANDLERS"],
		"SHARE_SHORTEN_URL_LOGIN"		=> $arParams["SHARE_SHORTEN_URL_LOGIN"],
		"SHARE_SHORTEN_URL_KEY" 		=> $arParams["SHARE_SHORTEN_URL_KEY"],		
		"SHOW_RATING" => $arParams["SHOW_RATING"],
	),
	$component
);
?>