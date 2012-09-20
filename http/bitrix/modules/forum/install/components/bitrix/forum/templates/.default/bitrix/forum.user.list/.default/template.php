<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
********************************************************************/
$arParams["SEO_USER"] = ($arParams["SEO_USER"] == "Y" ? "Y" : "N");
/********************************************************************
				/Input params
********************************************************************/
$arSort = array(
	"NUM_POSTS" => array("NAME" => GetMessage("LU_FILTER_SORT_NUM_POSTS")), 
	"SHOW_ABC" => array("NAME" => GetMessage("LU_FILTER_SORT_NAME")), 
);
if ($arResult["SHOW_VOTES"] == "Y"):
	$arSort["POINTS"] = array("NAME" => GetMessage("LU_FILTER_SORT_POINTS"));
endif;
$arSort["DATE_REGISTER"] = array("NAME" => GetMessage("LU_FILTER_SORT_DATE_REGISTER"));
$arSort["LAST_VISIT"] = array("NAME" => GetMessage("LU_FILTER_SORT_LAST_VISIT"));
$arFields = array(
	array(
		"NAME" => "PAGE_NAME",
		"TYPE" => "HIDDEN",
		"VALUE" => "user_list"),
	array(
		"TITLE" => GetMessage("LU_FILTER_USER_NAME"),
		"NAME" => "user_name",
		"TYPE" => "TEXT",
		"VALUE" => $_REQUEST["user_name"]),
	array(
		"TITLE" => GetMessage("LU_FILTER_LAST_VISIT"),
		"NAME" => "date_last_visit1",
		"NAME_TO" => "date_last_visit2",
		"TYPE" => "PERIOD",
		"VALUE" => $_REQUEST["date_last_visit1"],
		"VALUE_TO" => $_REQUEST["date_last_visit2"]), 
	array(
		"TITLE" => GetMessage("LU_FILTER_AVATAR"),
		"NAME" => "avatar",
		"TYPE" => "CHECKBOX",
		"VALUE" => "Y", 
		"ACTIVE" => $_REQUEST["avatar"], 
		"LABEL" => GetMessage("LU_FILTER_AVATAR_TITLE")));
if ($USER->IsAdmin() || $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W"):
	$arFields[] = array(
		"TITLE" => GetMessage("F_STATUS_USER"),
		"NAME" => "allow_post",
		"TYPE" => "SELECT",
		"VALUE" => array(
			"" => GetMessage("F_STATUS_NONE"), 
			"Y" => GetMessage("F_ALLOW_POST"), 
			"N" => GetMessage("F_FORBID_POST")), 
		"ACTIVE" => $_REQUEST["allow_post"]);
endif;
$arFields[] = array(
		"TITLE" => GetMessage("LU_FILTER_SORT"),
		"NAME" => "sort",
		"TYPE" => "SELECT",
		"VALUE" => $arSort, 
		"ACTIVE" => $_REQUEST["sort"]);
?>
<div class="forum-info-box forum-filter">
	<div class="forum-info-box-inner">
<?
	$APPLICATION->IncludeComponent("bitrix:forum.interface", "filter_simple", 
		array("FIELDS" => $arFields),
		$component,
		array(
			"HIDE_ICONS" => "Y")
		);?><?
?>
	</div>
</div>

<br/>
<?
if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;
if (!empty($arResult["OK_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<?
endif;

if ($arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;

?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=GetMessage("LU_TITLE_USER")?></span></div>
</div>
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
			<table cellspacing="0" class="forum-table forum-users">
			<thead>
				<tr>
					<th class="forum-first-column forum-column-username"><span><?=GetMessage("FLU_HEAD_NAME")?></span><?/*&nbsp;<br/><?=$arResult["SortingEx"]["SHOW_ABC"]?>*/?></th>
					<th class="forum-column-posts"><span><?=GetMessage("FLU_HEAD_POST")?></span><?/*&nbsp;<br/><?=$arResult["SortingEx"]["NUM_POSTS"]?>*/?></th>
<?
	if ($arResult["SHOW_VOTES"] == "Y"):
?>
					<th class="forum-column-points"><span><?=GetMessage("FLU_HEAD_POINTS")?></span><?/*&nbsp;<br/><?=$arResult["SortingEx"]["POINTS"]?>*/?></th>
<?
	endif;
?>
					<th class="forum-column-datereg"><span><?=GetMessage("FLU_HEAD_DATE_REGISTER")?></span><?/*?>&nbsp;<br/><?=$arResult["SortingEx"]["DATE_REGISTER"]?><?*/?></th>
					<th class="forum-last-column forum-column-lastvisit"><span><?=GetMessage("FLU_HEAD_LAST_VISIT")?></span><?/*?>&nbsp;<br/><?=$arResult["SortingEx"]["LAST_VISIT"]?><?*/?></th>
				</tr>
			</thead>
			<tbody>
<?
if ($arResult["SHOW_RESULT"] != "Y"):
?>
 				<tr class="forum-row-first forum-row-odd">
					<td class="forum-first-column" colspan="<?=($arResult["SHOW_VOTES"] == "Y" ? 5 : 4)?>"><?=GetMessage("FLU_EMPTY")?></td>
				</tr>
<?			
	return false;
endif;

$iCount = 0;
foreach ($arResult["USERS"] as $res):
	$iCount++;
?>
 				<tr class="<?=($iCount == 1 ? "forum-row-first " : (
				 $iCount == count($arResult["USERS"]) ? "forum-row-last " : ""))?><?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
					<td class="forum-first-column forum-column-username">
								<div class="forum-user-name"><?
							if ($arParams["SEO_USER"] == "Y"):
									?><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR"]?>"><span><?=$res["SHOW_ABC"]?></span></a></noindex><?
							else:
									?><a href="<?=$res["URL"]["AUTHOR"]?>"><span><?=$res["SHOW_ABC"]?></span></a><?
							endif;
								?></div>
<?
	if (is_array($res["~AVATAR"]) && (strLen($res["~AVATAR"]["HTML"]) > 0)):
?>
								<div class="forum-user-avatar"><?
									?><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR"]?>" title="<?=GetMessage("F_AUTHOR_PROFILE")?>"><?
										?><?=$res["~AVATAR"]["HTML"]?></a></noindex></div>
<?
	else:
?>
								<div class="forum-user-register-avatar"><?
									?><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR"]?>" title="<?=GetMessage("F_AUTHOR_PROFILE")?>"><?
										?><span><!-- ie --></span></a></noindex></div>
<?
	endif;
					
	if ($arParams["SHOW_USER_STATUS"] == "Y"):
?>
								<div class="forum-user-status <?=(!empty($res["AUTHOR_STATUS_CODE"]) ? "forum-user-".$res["AUTHOR_STATUS_CODE"]."-status" : "")?>"><?
									?><span><?=$res["AUTHOR_STATUS"]?></span></div>
<?
	endif;
?>
					</td>
					<td class="forum-column-posts"><?
	if ($res["NUM_POSTS"] > 0):
					?><noindex><a rel="nofollow" href="<?=$res["URL"]["POSTS"]?>"><?=intVal($res["NUM_POSTS"])?></a></noindex><?
	else:
					?>0<?
	endif;
					?></td>
<?
	if ($arResult["SHOW_VOTES"] == "Y"):
?>
					<td class="forum-column-points"><?=intVal($res["POINTS"])?></td>
<?
	endif;
?>
					<td class="forum-column-datereg">
<?
	if (!empty($res["DATE_REG"])):
?>
						<?=$res["DATE_REG"]?>
<?
	else:
?>
						&nbsp;
<?
	endif;
					?></td>
					<td class="forum-last-column forum-column-lastvisit">
<?
	if (!empty($res["LAST_VISIT"])):
?>
						<?=$res["LAST_VISIT"]?>
<?
	else:
?>
						&nbsp;
<?
	endif;
?>
					</td>
				</tr>
<?
endforeach;
?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?

if ($arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;

?>