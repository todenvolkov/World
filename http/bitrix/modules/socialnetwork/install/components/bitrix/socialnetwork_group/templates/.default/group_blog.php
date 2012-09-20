<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group_menu",
	"",
	Array(
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_MODS" => $arResult["PATH_TO_GROUP_MODS"],
		"PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
		"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
		"PATH_TO_GROUP_REQUEST_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
		"PATH_TO_GROUP_REQUESTS" => $arResult["PATH_TO_GROUP_REQUESTS"],
		"PATH_TO_GROUP_REQUESTS_OUT" => $arResult["PATH_TO_GROUP_REQUESTS_OUT"],
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
		"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
		"PATH_TO_GROUP_PHOTO" => $arResult["PATH_TO_GROUP_PHOTO"],
		"PATH_TO_GROUP_FORUM" => $arResult["PATH_TO_GROUP_FORUM"],
		"PATH_TO_GROUP_CALENDAR" => $arResult["PATH_TO_GROUP_CALENDAR"],
		"PATH_TO_GROUP_FILES" => $arResult["PATH_TO_GROUP_FILES"],
		"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
		"PATH_TO_GROUP_CONTENT_SEARCH" => $arResult["PATH_TO_GROUP_CONTENT_SEARCH"],
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"PAGE_ID" => "group_blog",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
	),
	$component
);
?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group", 
	"short", 
	Array(
		"PATH_TO_USER" => $arParams["PATH_TO_USER"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
		"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
		"PATH_TO_GROUP_REQUEST_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
		"PATH_TO_USER_REQUEST_GROUP" => $arResult["PATH_TO_USER_REQUEST_GROUP"],
		"PATH_TO_GROUP_REQUESTS" => $arResult["PATH_TO_GROUP_REQUESTS"],
		"PATH_TO_GROUP_REQUESTS_OUT" => $arResult["PATH_TO_GROUP_REQUESTS_OUT"],
		"PATH_TO_GROUP_MODS" => $arResult["PATH_TO_GROUP_MODS"],
		"PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
		"PATH_TO_USER_LEAVE_GROUP" => $arResult["PATH_TO_USER_LEAVE_GROUP"],
		"PATH_TO_GROUP_DELETE" => $arResult["PATH_TO_GROUP_DELETE"],
		"PATH_TO_GROUP_FEATURES" => $arResult["PATH_TO_GROUP_FEATURES"],
		"PATH_TO_SEARCH" => $arResult["PATH_TO_SEARCH"],
		"PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
		"PATH_TO_MESSAGE_TO_GROUP" => $arResult["PATH_TO_MESSAGE_TO_GROUP"], 
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
		"SET_NAV_CHAIN" => "N",
		"SET_TITLE" => "N", 
		"SHORT_FORM" => "Y",
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"ITEMS_COUNT" => $arParams["ITEM_MAIN_COUNT"],
	),
	$component 
);
?>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
<td align="left" valign="top"><?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.blog.menu",
	"",
	Array(
		"PATH_TO_USER" => $arParams["PATH_TO_USER"],
		"PATH_TO_POST_EDIT" => $arResult["PATH_TO_GROUP_BLOG_POST_EDIT"],
		"PATH_TO_DRAFT" => $arResult["PATH_TO_GROUP_BLOG_DRAFT"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["blog_page"],
		"POST_VAR" => $arResult["ALIASES"]["post_id"],
		"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"PATH_TO_MODERATION" => $arResult["PATH_TO_GROUP_BLOG_MODERATION"],
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
		"PATH_TO_RSS" => $arResult["PATH_TO_GROUP_BLOG_RSS"], 
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"RSS1" => "N", 
		"RSS2" => "Y", 
		"ATOM" => "N", 
		"MODE" => "B",
		"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"USE_SOCNET" => "Y",
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
		"SHOW_YEAR" 			=> $arParams["SHOW_YEAR"],		
		"PATH_TO_BLOG" 			=> $arResult["PATH_TO_USER_BLOG"], 
		"PATH_TO_GROUP_BLOG" 	=> $arResult["PATH_TO_GROUP_BLOG"], 
		"PATH_TO_BLOG_CATEGORY"	=> $APPLICATION->GetCurPageParam("category=#category_id#", Array("category")), 
		"PATH_TO_POST" 			=> $arResult["PATH_TO_GROUP_BLOG_POST"], 
		"PATH_TO_POST_EDIT" 	=> $arResult["PATH_TO_GROUP_BLOG_POST_EDIT"], 
		"PATH_TO_USER" 			=> $arParams["PATH_TO_USER"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"], 
		"PATH_TO_MESSAGES_CHAT"	=> $arParams["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_VIDEO_CALL" 	=> $arParams["PATH_TO_VIDEO_CALL"],
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
		"SOCNET_GROUP_ID" 		=> $arResult["VARIABLES"]["group_id"],
		"GROUP_ID" 				=> $arParams["BLOG_GROUP_ID"],
		"NAME_TEMPLATE" 		=> $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" 			=> $arParams["SHOW_LOGIN"],
		"PATH_TO_CONPANY_DEPARTMENT"	=> $arParams["PATH_TO_CONPANY_DEPARTMENT"],
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