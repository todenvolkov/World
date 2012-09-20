<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
if ($arResult["ALL"] <= 0)
	return 0;
/********************************************************************
				Input params
********************************************************************/
$arParams["SEO_USER"] = ($arParams["SEO_USER"] == "Y" ? "Y" : "N");
/********************************************************************
				/Input params
********************************************************************/

if (in_array("USERS_ONLINE", $arParams["SHOW"])):

if ($arParams["TID"] > 0):
	$text = GetMessage("F_NOW_TOPIC_READ");
else: 
	$text = GetMessage("F_NOW_FORUM");
endif;

$text .= " ".str_replace(
	array("#TIME_INTERVAL#", "#COUNT_USERS#", "#GUESTS#", "#USERS#", "#HIDDEN_USERS#"),
	array("<span>".intVal($arParams["PERIOD"] / 60)."</span>", "<span>".$arResult["ALL"]."</span>", 
		"<span>".intVal($arResult["GUEST"])."</span>", "<span>".intVal($arResult["REGISTER"])."</span>", 
		"<span>".count($arResult["USERS_HIDDEN"])."</span>"),
	GetMessage("F_NOW_ONLINE"));
?>
<div class="forum-info-box forum-users-online">
	<div class="forum-info-box-inner">
		<span class="forum-users-online"><?=$text?> <?
$first = true;
foreach ($arResult["USERS"] as $res)
{
	?><?=(!$first ? ", ": "")?><?
	if ($arParams["SEO_USER"] == "Y"):
	?><noindex><a rel="nofollow" href="<?=$res["profile_view"]?>" title="<?=GetMessage("F_USER_PROFILE")?>"><?
		if(($arParams["WORD_WRAP_CUT"] > 0) && (strLen($res["~SHOW_NAME"])>$arParams["WORD_WRAP_CUT"]))
			$res["SHOW_NAME"] = htmlspecialcharsEx(subStr($res["~SHOW_NAME"], 0, $arParams["WORD_WRAP_CUT"]))."...";
		?><?=$res["SHOW_NAME"]?></a></noindex><?
	else:
	?><a href="<?=$res["profile_view"]?>" title="<?=GetMessage("F_USER_PROFILE")?>"><?
		if(($arParams["WORD_WRAP_CUT"] > 0) && (strLen($res["~SHOW_NAME"])>$arParams["WORD_WRAP_CUT"]))
			$res["SHOW_NAME"] = htmlspecialcharsEx(subStr($res["~SHOW_NAME"], 0, $arParams["WORD_WRAP_CUT"]))."...";
		?><?=$res["SHOW_NAME"]?></a><?
	endif;
	$first = false;
}
		?></span>
	</div>
</div>
<?
endif;

if (in_array("BIRTHDAY", $arParams["SHOW"]) && !empty($arResult["USERS_BIRTHDAY"])):
?>
<div class="forum-info-box forum-users-birthday">
	<div class="forum-info-box-inner">
		<span class="forum-users-birthday"><?=GetMessage("F_TODAY_BIRTHDAY")?> <?
$first = true;
foreach ($arResult["USERS_BIRTHDAY"] as $res)
{
	?><?=((!$first)? ", ":"")?><?
	if ($arParams["SEO_USER"] == "Y"):
	?><noindex><a rel="nofollow" href="<?=$res["profile_view"]?>" title="<?=GetMessage("F_USER_PROFILE")?>"><?=$res["SHOW_NAME"]?></a></noindex><?
	else:
	?><a href="<?=$res["profile_view"]?>" title="<?=GetMessage("F_USER_PROFILE")?>"><?=$res["SHOW_NAME"]?></a> <?
	endif;
		?>(<span><?=$res["AGE"]?></span>)<?
	$first = false;
}
		?></span>
	</div>
</div>
<?
endif;

if (in_array("STATISTIC", $arParams["SHOW"])):
?>
<div class="forum-info-box forum-statistics">
	<div class="forum-info-box-inner">
<?
	if (empty($arParams["FID"])):
?>
		<div class="forum-statistics-allusers"><?=GetMessage("F_REGISTER_USERS")?>:&nbsp;<span><?=intVal($arResult["STATISTIC"]["USERS_ON_FORUM"])?></span></div>
		<div class="forum-statistics-users"><?=GetMessage("F_ACTIVE_USERS")?>:&nbsp;<span><?=intVal($arResult["STATISTIC"]["USERS_ON_FORUM_ACTIVE"])?></span></div>
<?/*?>		<div class="forum-statistics-forums"><?=GetMessage("F_FORUMS_ALL")?>:&nbsp;<span><?=$arResult["STATISTIC"]["FORUMS"]?></span></div><?*/?>
<?
	endif;
?>
		<div class="forum-statistics-topics"><?=GetMessage("F_TOPICS_ALL")?>:&nbsp;<span><?=intVal($arResult["STATISTIC"]["TOPICS"])?></span></div>
		<div class="forum-statistics-replies"><?=GetMessage("F_POSTS_ALL")?>:&nbsp;<span><?=intVal($arResult["STATISTIC"]["POSTS"])?></span></div>
		<div class="forum-clear-float"></div>
	</div>
	
</div>
<?
endif;
?>