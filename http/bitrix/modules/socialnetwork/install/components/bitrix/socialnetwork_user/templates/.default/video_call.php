<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$APPLICATION->IncludeComponent(
	"bitrix:video.call", 
	"", 
	Array(
		"SET_TITLE" => $arResult["SET_TITLE"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"PATH_TO_VIDEO_MEETING_DETAIL" => $arParams["CALENDAR_PATH_TO_VIDEO_MEETING_DETAIL"],
		"IBLOCK_ID" => $arParams["CALENDAR_VIDEO_MEETING_IBLOCK_ID"],
	),
	$component 
);
?>