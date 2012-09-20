<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:blog.rss",
	"",
	Array(
		"MESSAGE_COUNT" => "10", 
		"PATH_TO_BLOG" => $arResult["PATH_TO_GROUP_BLOG"], 
		"PATH_TO_POST" => $arResult["PATH_TO_GROUP_BLOG_POST"], 
		"PATH_TO_USER" => $arResult["PATH_TO_USER"], 
		"TYPE" => $arResult["VARIABLES"]["type"], 
		"CACHE_TYPE" => $arResult["CACHE_TYPE"], 
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["blog_page"],
		"POST_VAR" => $arResult["ALIASES"]["post_id"],
		"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"USE_SOCNET" => "Y",
		"POST_ID" => $arResult["VARIABLES"]["post_id"],
		"MODE" => "C",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],

	),
	$component 
);
?>