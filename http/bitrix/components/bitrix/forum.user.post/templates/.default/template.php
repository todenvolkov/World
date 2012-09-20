<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["PATH_TO_ICON"] = (empty($arParams["PATH_TO_ICON"]) ? $templateFolder."/images/icon" : $arParams["PATH_TO_ICON"]);
$arParams["PATH_TO_ICON"] = str_replace("//", "/", $arParams["PATH_TO_ICON"]."/");
/********************************************************************
				/Input params
********************************************************************/
$filter_value_fid = array(
	"0" => GetMessage("F_ALL_FORUMS"));
if (is_array($arResult["GROUPS_FORUMS"])):
	foreach ($arResult["GROUPS_FORUMS"] as $key => $res):
		if ($res["TYPE"] == "GROUP"):
			$filter_value_fid["GROUP_".$res["ID"]] = array(
				"NAME" => ($res["DEPTH"] > 0 ? str_pad("", ($res["DEPTH"] - 1)*4, " ") : "").$res["~NAME"], 
				"CLASS" => "forums-selector-optgroup level".$res["DEPTH"], 
				"TYPE" => "OPTGROUP");
		else:
			$filter_value_fid[$res["ID"]] = array(
				"NAME" => ($res["DEPTH"] > 0 ? str_pad("", ($res["DEPTH"] + 1)*4, " ") : "").$res["~NAME"], 
				"CLASS" => "forums-selector-option level".$res["DEPTH"], 
				"TYPE" => "OPTION");
		endif;
	endforeach;
endif;
?>
<div class="forum-info-box forum-filter">
	<div class="forum-info-box-inner">
<?
$APPLICATION->IncludeComponent("bitrix:forum.interface", "filter_simple", 
	array(
		"FIELDS" => array(
			array(
				"NAME" => "PAGE_NAME",
				"TYPE" => "HIDDEN",
				"VALUE" => "user_post"),
			array(
				"NAME" => "UID",
				"TYPE" => "HIDDEN",
				"VALUE" => $arParams["UID"]),
			array(
				"NAME" => "mode",
				"TYPE" => "HIDDEN",
				"VALUE" => $arParams["mode"]),
			array(
				"TITLE" => GetMessage("LU_FORUM"),
				"NAME" => "fid",
				"TYPE" => "SELECT",
				"CLASS" => "forums-selector-single",
				"VALUE" => $filter_value_fid,
				"ACTIVE" => $_REQUEST["fid"]),
			array(
				"TITLE" => GetMessage("LU_DATE_CREATE"),
				"NAME" => "date_create",
				"NAME_TO" => "date_create1",
				"TYPE" => "PERIOD",
				"VALUE" => $_REQUEST["date_create"],
				"VALUE_TO" => $_REQUEST["date_create1"]),
			array(
				"TITLE" => GetMessage("LU_TOPIC"),
				"NAME" => "topic",
				"TYPE" => "TEXT",
				"VALUE" => $_REQUEST["topic"]),
			array(
				"TITLE" => GetMessage("LU_MESSAGE"),
				"NAME" => "message",
				"TYPE" => "TEXT",
				"VALUE" => $_REQUEST["message"]),
			array(
				"TITLE" => GetMessage("LU_SORT"),
				"NAME" => "sort",
				"TYPE" => "SELECT",
				"VALUE" => array(
					"topic" => array("NAME" => GetMessage("LU_BY_TOPIC")), 
					"message" => array("NAME" => GetMessage("LU_BY_MESSAGE"))), 
				"ACTIVE" => $_REQUEST["sort"]))),

		array(
			"HIDE_ICONS" => "Y"));?><?
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

if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
?><div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;

/*
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=($arParams["mode"] == "lta" ? GetMessage("LU_TITLE_LTA") : (
		$arParams["mode"] == "lt" ? GetMessage("LU_TITLE_LT") : GetMessage("LU_TITLE_ALL")))?></span></div>
</div>
<?
*/
if (empty($arResult["FORUMS"])):
?>
<div class="forum-info-box forum-user-posts">
	<div class="forum-info-box-inner">
		<?=GetMessage("FR_EMPTY")?>
	</div>
</div>
<?
	return false;
endif;
foreach ($arResult["FORUMS"] as $arForum):
/*?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=GetMessage("LU_FORUM")?>: <?=trim($arForum["NAME"])?></a></span></div>
</div>
<?
*/	foreach ($arForum["TOPICS"] as $key => $arTopic)
	{
if ($_REQUEST["sort"] == "topic"):
?>
<div class="forum-header-box">
	<div class="forum-header-options">
		<span class="forum-option-messages"><a href="<?=$arTopic["URL"]["TOPIC"]?>"><?
		?><?=GetMessage("LU_USER_POSTS_ON_TOPIC")?>: <span><?=$arTopic["COUNT_MESSAGE"]?></span><?
		?></a></span>
	</div>
	<div class="forum-header-title"><span><?
	if ($arTopic["STATE"] != "Y"):
		?><span class="forum-header-title-closed">[ <span><?=GetMessage("F_CLOSED")?></span> ]</span> <?
	endif;
/*	if (strlen($arTopic["IMAGE"])>0):
	?><img src="<?=$arParams["PATH_TO_ICON"].$arTopic["IMAGE"];?>" alt="<?=$arTopic["IMAGE_DESCR"]?>" /><?
	endif;
*/	?><?=trim($arTopic["TITLE"])?><?
 		if (strlen($arTopic["DESCRIPTION"])>0):
			?>, <?=trim($arTopic["DESCRIPTION"])?><?
		endif;
	
	?></span></div>
</div>
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
<?
endif;
		$iCount = 0;
		foreach ($arTopic["MESSAGES"] as $res)
		{

			if ($_REQUEST["sort"] != "topic"):
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?
	if ($arTopic["STATE"] != "Y"):
		?><span class="forum-header-title-closed">[ <span><?=GetMessage("F_CLOSED")?></span>]</span> <?
	endif;
/*	if (strlen($arTopic["IMAGE"])>0):
	?><img src="<?=$arParams["PATH_TO_ICON"].$arTopic["IMAGE"];?>" alt="<?=$arTopic["IMAGE_DESCR"]?>" /><?
	endif;
*/	?><?=trim($arTopic["TITLE"])?><?
 		if (strlen($arTopic["DESCRIPTION"])>0):
			?>, <?=trim($arTopic["DESCRIPTION"])?><?
		endif;
	
	?></span></div>
</div>
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
<?
			endif;

			$iCount++;
			$res["AUTHOR_STATUS"] = $arForum["AUTHOR_STATUS"];
			$res["AUTHOR_STATUS_CODE"] = $arForum["AUTHOR_STATUS_CODE"];
?>
			<table cellspacing="0" border="0" class="forum-post-table <?=($iCount == 1 ? "forum-post-first " : (
				 $iCount == count($arTopic["MESSAGES"]) ? "forum-post-last " : ""))?><?=($iCount%2 == 1 ? "forum-post-odd" : "forum-post-even")?><?
				 ?><?=($res["APPROVED"] == "Y" ? "" : "forum-post-hidden")?>">
				<tbody>
					<tr>
						<td class="forum-cell-user">
							<div class="forum-user-info">
								<div class="forum-user-name"><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR"]?>"><span><?=$res["AUTHOR_NAME"]?></span></a></noindex></div>
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
									?><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR"]?>" title="<?=GetMessage("F_AUTHOR_PROFILE")?>"><span><!-- ie --></span></a></noindex></div>
<?
			endif;

		if (strLen(trim($res["AUTHOR_STATUS"]))):
?>
								<div class="forum-user-status <?=(!empty($res["AUTHOR_STATUS_CODE"]) ? "forum-user-".$res["AUTHOR_STATUS_CODE"]."-status" : "")?>"><?
									?><span><?=$res["AUTHOR_STATUS"]?></span></div>
<?
		endif;
?>
								<div class="forum-user-additional">
<?
		if (intVal($res["NUM_POSTS"]) > 0):
?>
									<span><?=GetMessage("F_NUM_MESS")?> <span><?=$res["NUM_POSTS"]?></span></span>
<?
		endif;
		
		if (COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y" && $res["NUM_POINTS"] > 0):
?>
									<span><?=GetMessage("F_POINTS")?> <span><?=$res["NUM_POINTS"]?></span></span>
<?
		endif;
		if (strlen($res["~DATE_REG"]) > 0):
?>
									<span><?=GetMessage("F_DATE_REGISTER")?> <span><?=$res["DATE_REG"]?></span></span>
<?
		endif;
?>
								</div>
							</div>
						</td>
						<td class="forum-cell-post">
							<div class="forum-post-date">
								<div class="forum-post-number"><noindex><a rel="nofollow" href="http://<?=$_SERVER["HTTP_HOST"]?><?=$res["URL"]["MESSAGE"]?>#message<?=$res["ID"]?>" <?
									?>onclick="prompt(oText['ml'], this.href); return false;" title="<?=GetMessage("F_ANCHOR")?>" rel="nofollow">#<?=$res["NUMBER"]?></a></noindex>
								</div>
								<span><?=$res["POST_DATE"]?></span>
							</div>
							<div class="forum-post-entry">
								<div class="forum-post-text" id="message_text_<?=$res["ID"]?>"><?=$res["POST_MESSAGE_TEXT"]?></div>
<?
							if (!empty($res["FILES"])):
?>								
								<div class="forum-post-attachments">
									<label><?=GetMessage("F_ATTACH_FILES")?></label>
<?
								foreach ($res["FILES"] as $arFile): 
?>								
									<div class="forum-post-attachment"><?
									?><?$GLOBALS["APPLICATION"]->IncludeComponent(
										"bitrix:forum.interface", "show_file",
										Array(
											"FILE" => $arFile,
											"WIDTH" => $arResult["PARSER"]->image_params["width"],
											"HEIGHT" => $arResult["PARSER"]->image_params["height"],
											"CONVERT" => "N",
											"FAMILY" => "FORUM",
											"SINGLE" => "Y",
											"RETURN" => "N",
											"SHOW_LINK" => "Y"),
										null,
										array("HIDE_ICONS" => "Y"));
									?></div>
<?
								endforeach;
?>
								</div>
<?
							endif;

							if (!empty($res["EDITOR_NAME"])):
							?><div class="forum-post-lastedit">
								<span class="forum-post-lastedit"><?=GetMessage("F_EDIT_HEAD")?> 
									<span class="forum-post-lastedit-user"><?
								if (!empty($res["URL"]["EDITOR"])):
									?><noindex><a rel="nofollow" href="<?=$res["URL"]["EDITOR"]?>"><?=$res["EDITOR_NAME"]?></a></noindex><?
								else:
									?><?=$res["EDITOR_NAME"]?><?
								endif;
								?></span> - <span class="forum-post-lastedit-date"><?=$res["EDIT_DATE"]?></span><?
								if (!empty($res["EDIT_REASON"])):
								?><span class="forum-post-lastedit-reason">(<span><?=$res["EDIT_REASON"]?></span>)</span><?
								endif;
								?></span>
							</div><?
							endif;
							
							if (strLen($res["SIGNATURE"]) > 0):
?>
								<div class="forum-user-signature">
									<div class="forum-signature-line"></div>
									<span><?=$res["SIGNATURE"]?></span>
								</div>
<?
							endif;
?>
							</div>
						</td>
					</tr>
					<tr>
						<td class="forum-cell-contact">
							<div class="forum-contact-links">
								&nbsp;
							</div>
						</td>
						<td class="forum-cell-actions">
							<div class="forum-action-links">
<?
/*					if ($res["PANELS"]["EDIT"] == "Y"):
?>
								 <span class="forum-action-edit"><a href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT")?></a></span>&nbsp;&nbsp;
<?
					endif;
*/			
?>
								<span class="forum-action-goto"><noindex><a rel="nofollow" href="<?=$res["URL"]["MESSAGE"]?>"><?
									?><?=GetMessage("F_GOTO")?></a></noindex></span>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?
			if ($_REQUEST["sort"] != "topic"):
?>
		</div>
	</div>
</div>
<?
			endif;

	}
if ($_REQUEST["sort"] == "topic"):
?>
		</div>
	</div>
</div>
<?
endif;
}
endforeach;

if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
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
<script type="text/javascript">
if (typeof oText != "object")
	var oText = {};
oText['ml'] = '<?=CUtil::addslashes(GetMessage("F_ANCHOR_TITLE"))?>';
</script>