<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.user.post",
	"",
	array(
		"UID" =>  $arResult["UID"],
		"mode" =>  $arResult["mode"],
		
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" => $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_USER_LIST" =>  $arResult["URL_TEMPLATES_USER_LIST"],
		"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"URL_TEMPLATES_USER_POST" =>  $arResult["URL_TEMPLATES_USER_POST"],
		
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
		"FID_RANGE" => $arParams["FID"],
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"],
		"DATE_TIME_FORMAT" =>  $arParams["DATE_TIME_FORMAT"],
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
		"PATH_TO_ICON" => $arParams["PATH_TO_ICON"],
		"WORD_LENGTH" => $arParams["WORD_LENGTH"],
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"],
		
		"SET_TITLE" => $arParams["SET_TITLE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	),
	$component 
);
?>