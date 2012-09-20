<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.messages_menu",
	"",
	Array(
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"PATH_TO_MESSAGES_INPUT" => $arResult["PATH_TO_MESSAGES_INPUT"],
		"PATH_TO_MESSAGES_OUTPUT" => $arResult["PATH_TO_MESSAGES_OUTPUT"],
		"PATH_TO_USER_BAN" => $arResult["PATH_TO_USER_BAN"],
		"PATH_TO_MESSAGES_USERS" => $arResult["PATH_TO_MESSAGES_USERS"],
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_LOG" => $arResult["PATH_TO_LOG"],
		"PATH_TO_SUBSCRIBE" => $arResult["PATH_TO_SUBSCRIBE"],
		"PATH_TO_BIZPROC" => $arResult["PATH_TO_BIZPROC"],
		"PATH_TO_TASKS" => $arResult["PATH_TO_TASKS"],
		"PAGE_ID" => "bizproc",
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
	),
	$component
);
?>

<?$APPLICATION->IncludeComponent(
	"bitrix:bizproc.task.list", 
	"", 
	Array(
		"USER_ID" => $arResult["VARIABLES"]["user_id"], 
		"WORKFLOW_ID" => "", 
		"TASK_EDIT_URL" => str_replace("#task_id#", "#ID#", $arResult["PATH_TO_BIZPROC_EDIT"]),
		"PAGE_ELEMENTS" => "20", 
		"PAGE_NAVIGATION_TEMPLATE" => "", 
		"SHOW_TRACKING" => "N", 
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>