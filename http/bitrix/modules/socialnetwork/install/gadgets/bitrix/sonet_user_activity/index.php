<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arGadgetParams["TEMPLATE_NAME"] = ($arGadgetParams["TEMPLATE_NAME"]?$arGadgetParams["TEMPLATE_NAME"]:"main");
$arGadgetParams["SHOW_TITLE"] = ($arGadgetParams["SHOW_TITLE"]?$arGadgetParams["SHOW_TITLE"]:"N");
$arGadgetParams["GROUP_ID"] = ($arGadgetParams["GROUP_ID"]?$arGadgetParams["GROUP_ID"]:false);
$arGadgetParams["USER_VAR"] = ($arGadgetParams["USER_VAR"]?$arGadgetParams["USER_VAR"]:"user_id");
$arGadgetParams["GROUP_VAR"] = ($arGadgetParams["GROUP_VAR"]?$arGadgetParams["GROUP_VAR"]:"group_id");
$arGadgetParams["PAGE_VAR"] = ($arGadgetParams["PAGE_VAR"]?$arGadgetParams["PAGE_VAR"]:"page");
$arGadgetParams["PATH_TO_USER"] = ($arGadgetParams["PATH_TO_USER"]?$arGadgetParams["PATH_TO_USER"]:"/company/personal/user/#user_id#/");
$arGadgetParams["PATH_TO_GROUP"] = ($arGadgetParams["PATH_TO_GROUP"]?$arGadgetParams["PATH_TO_GROUP"]:"/workgroups/group/#group_id#/");
$arGadgetParams["LIST_URL"] = ($arGadgetParams["LIST_URL"] ? $arGadgetParams["LIST_URL"] : (IsModuleInstalled("intranet") ? "/company/personal/user/#user_id#/activity/" : "/club/user/#user_id#/activity/"));

$arGadgetParams["LIST_URL"] = CComponentEngine::MakePathFromTemplate($arGadgetParams["LIST_URL"], array("user_id" => $arParams["USER_ID"]));

if (!in_array($arGadgetParams["EVENT_ID"], array("system", "system_groups", "system_friends", "forum", "photo", "blog", "tasks", "files", "calendar")))
	$arGadgetParams["EVENT_ID"] = false;

$arGadgetParams["LOG_CNT"] = ($arGadgetParams["LOG_CNT"] ? $arGadgetParams["LOG_CNT"] : 7);

if ($arGadgetParams["EVENT_ID"] == "system")
	$sTitle = GetMessage('GD_ACTIVITY_SYSTEM');
elseif ($arGadgetParams["EVENT_ID"] == "system_groups")
	$sTitle = GetMessage('GD_ACTIVITY_SYSTEM_GROUPS');
elseif ($arGadgetParams["EVENT_ID"] == "system_friends")
	$sTitle = GetMessage('GD_ACTIVITY_SYSTEM_FRIENDS');
elseif ($arGadgetParams["EVENT_ID"] == "forum")
	$sTitle = GetMessage('GD_ACTIVITY_FORUM');
elseif ($arGadgetParams["EVENT_ID"] == "blog")
	$sTitle = GetMessage('GD_ACTIVITY_BLOG');
elseif ($arGadgetParams["EVENT_ID"] == "tasks")
	$sTitle = GetMessage('GD_ACTIVITY_TASKS');
elseif ($arGadgetParams["EVENT_ID"] == "calendar")
	$sTitle = GetMessage('GD_ACTIVITY_CALENDAR');
elseif ($arGadgetParams["EVENT_ID"] == "photo")
	$sTitle = GetMessage('GD_ACTIVITY_PHOTO');
elseif ($arGadgetParams["EVENT_ID"] == "files")
	$sTitle = GetMessage('GD_ACTIVITY_FILES');
else
	$sTitle = "";

if (strlen($arGadgetParams["EVENT_ID"]) > 0)
	$arGadget["TITLE"] .= " [".$sTitle."]";
	
if($arGadgetParams["SHOW_TITLE"] == "Y"):
	?><h4><?= GetMessage("GD_ACTIVITY_TITLE") ?></h4><?
endif;

$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.activity",
	$arGadgetParams["TEMPLATE_NAME"],
	Array(
		"USER_ID" => $arParams["USER_ID"],
		"EVENT_ID" => $arGadgetParams["EVENT_ID"],
		"USER_VAR" => $arGadgetParams["USER_VAR"],
		"GROUP_VAR" => $arGadgetParams["GROUP_VAR"],
		"PAGE_VAR" => $arGadgetParams["PAGE_VAR"],
		"PATH_TO_USER" => $arParams["PATH_TO_USER"],
		"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
		"SET_TITLE" => "N",
		"AUTH" => "N",
		"LOG_DATE_DAYS" => $arGadgetParams["LOG_DATE_DAYS"],
		"LOG_CNT" => $arGadgetParams["LOG_CNT"],		
		"SET_NAV_CHAIN" => "N",
		"PATH_TO_MESSAGES_CHAT" => $arParams["PM_URL"],
		"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"SHOW_YEAR" => $arParams["SHOW_YEAR"],		
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],		
	),
	$component,
	Array("HIDE_ICONS"=>"Y")
);
?>
<?
if(strlen($arGadgetParams["LIST_URL"])>0):
	?><br /><br />
	<div align="right"><a href="<?=htmlspecialchars($arGadgetParams["LIST_URL"])?>"><?echo GetMessage("GD_ACTIVITY_MORE")?></a> <a href="<?=htmlspecialchars($arGadgetParams["LIST_URL"])?>"><img width="7" height="7" border="0" src="/bitrix/images/socialnetwork/icons/arrows.gif" /></a>
	<br />
	</div><?
endif?>