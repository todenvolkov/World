<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
IncludeAJAX();
// *****************************************************************************************
$_REQUEST["ACTION"] = ($_REQUEST["ACTION"] == "MOVE_TO_NEW" ? "MOVE_TO_NEW" : "MOVE_TO_TOPIC");
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
$arParams["SHOW_MAIL"] = (($arParams["SEND_MAIL"] <= "A" || ($arParams["SEND_MAIL"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y");
// *****************************************************************************************
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
?>
<form method="POST" name="MESSAGES" id="MESSAGES" action="<?=POST_FORM_ACTION_URI?>" onsubmit="this.send_form.disabled=true; return true;" class="forum-form">
	<input type="hidden" name="PAGE_NAME" value="message_move" />
	<?=$arResult["sessid"]?>
	<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
	<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
	<input type="hidden" name="step" value="1" />
<div class="forum-info-box forum-post-move">
	<div class="forum-info-box-inner">
		<div class="forum-post-entry">
			<?=GetMessage("F_MOVE_TO")?>
			<input type="radio" name="ACTION" value="MOVE_TO_TOPIC" id="MOVE_TO_TOPIC" <?=($_REQUEST["ACTION"] == "MOVE_TO_TOPIC" ? "checked='checked'" : "")?> <?
				?>onclick="document.getElementById('MOVE_TO_TOPIC_DIV').style.display=(this.checked ? '' : 'none'); <?
					?>document.getElementById('MOVE_TO_NEW_DIV').style.display=(this.checked ? 'none' : '');" />
			<label for="MOVE_TO_TOPIC"><?=GetMessage("F_HEAD_TO_EXIST_TOPIC")?></label>
			<input type="radio" name="ACTION" value="MOVE_TO_NEW" id="MOVE_TO_NEW" <?=($_REQUEST["ACTION"] == "MOVE_TO_NEW" ? "checked='checked'" : "")?> <?
				?>onclick="document.getElementById('MOVE_TO_TOPIC_DIV').style.display=(this.checked ? 'none' : ''); <?
					?>document.getElementById('MOVE_TO_NEW_DIV').style.display=(this.checked ? '' : 'none');" />
			<label for="MOVE_TO_NEW"><?=GetMessage("F_HEAD_TO_NEW_TOPIC")?></label>
		</div>
		
		<div id="MOVE_TO_TOPIC_DIV" <?=($_REQUEST["ACTION"] == "MOVE_TO_NEW" ? "style='display:none;'" : "")?> class="forum-post-move-to-topic">
	<div class="forum-reply-fields">
		<div class="forum-reply-field forum-reply-field-topic">	
			<label for="newTID"><?=GetMessage("F_TOPIC_ID")?><span class="forum-required-field">*</span></label>
			<input type="text" name="newTID" id="newTID" value="<?=intVal($_REQUEST["newTID"])?>" <?
				?> onfocus="ForumSearchTopic(this, 'Y');" onblur="ForumSearchTopic(this, 'N');" size="2" />
			<input type="button" name="search" value="..." onClick="window.open('<?=CUtil::JSEscape($arResult["topic_search"])?>', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));" />
			<span id="TOPIC_INFO"><?
				if (!empty($arResult["NEW_TOPIC"]["TOPIC"])):
					?>&laquo;<?=$arResult["NEW_TOPIC"]["TOPIC"]["TITLE"]?>&raquo; ( <?=GetMessage("F_TITLE_ON_FORUM")?>: <?=$arResult["NEW_TOPIC"]["FORUM"]["NAME"]?>)<?
				elseif (intVal($_REQUEST["newTID"]) > 0):
					?><?=GetMessage("F_TOPIC_NOT_FOUND")?><?
				else:
				endif;
			?></span>
		</div>
		<div class="forum-reply-field forum-reply-field-info">	
			<i>(<?=GetMessage("F_TOPIC_SEARCH_TITLE")?>)</i>
		</div>
	</div>
		</div>
		
		<div id="MOVE_TO_NEW_DIV" <?=($_REQUEST["ACTION"] == "MOVE_TO_NEW" ? "" : "style='display:none;'")?> class="forum-post-move-to-new-topic">
	<div class="forum-reply-fields">
	
		<div class="forum-reply-field forum-reply-field-title">
			<label for="TITLE"><?=GetMessage("F_TOPIC_NAME")?><span class="forum-required-field">*</span></label>
			<input name="TITLE" id="TITLE" type="text" value="<?=htmlSpecialChars($_REQUEST["TITLE"])?>" size="70" /></div>
		<div class="forum-reply-field forum-reply-field-desc">
			<label for="DESCRIPTION"><?=GetMessage("F_TOPIC_DESCR")?></label>
			<input name="DESCRIPTION" id="DESCRIPTION" type="text" value="<?=htmlSpecialChars($_REQUEST["DESCRIPTION"])?>" size="70"/></div>
<?
if ($arParams["SHOW_TAGS"] == "Y"):
?>
		<div class="forum-reply-field forum-reply-field-tags" style="display:block;">
			<label for="TAGS"><?=GetMessage("F_TOPIC_TAGS")?></label>
<?
		if (IsModuleInstalled("search")):
		$APPLICATION->IncludeComponent(
			"bitrix:search.tags.input", 
			"", 
			array(
				"VALUE" => htmlSpecialChars($_REQUEST["TAGS"]), 
				"NAME" => "TAGS",
				"TEXT" => ' size="70" '),
			$component,
			array("HIDE_ICONS" => "Y"));
		else:
			?><input name="TAGS" type="text" value="<?=htmlSpecialChars($_REQUEST["TAGS"])?>"  size="70"/><?
		endif;
?>
		</div>
<?
endif;
?>
	</div>
		</div>

		<div class="forum-reply-buttons">
			<input type="submit" name="send_form" value="<?=GetMessage("F_BUTTON_MOVE")?>" />
		</div>
	</div>
</div>
<div class="forum-header-box">
	<div class="forum-header-options">
		<span class="forum-option-topic"><a href="<?=$arResult["TOPIC"]["read"]?>"><?=$arResult["TOPIC"]["TITLE"]?></a></span>&nbsp;&nbsp;
		<span class="forum-option-forum"><a href="<?=$arResult["FORUM"]["list"]?>"><?=$arResult["FORUM"]["NAME"]?></a></span>
	</div>
	<div class="forum-header-title"><span><?=GetMessage("F_TITLE")?></span></div>
</div>

<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
<?
$iCount = 0;
foreach ($arResult["MESSAGE_LIST"] as $res):
$iCount++;
?>
			<table cellspacing="0" border="0" class="forum-post-table <?=($iCount == 1 ? "forum-post-first " : (
				 $iCount == count($arResult["MESSAGE_LIST"]) ? "forum-post-last " : ""))?><?=($iCount%2 == 1 ? "forum-post-odd " : "forum-post-even ")?><?
				 ?><?=($res["APPROVED"] == "Y" ? "" : "forum-post-hidden ")?> forum-post-selected">
				<tbody>
					<tr>
						<td class="forum-cell-user">
							<div class="forum-user-info">
<?
		if ($res["AUTHOR_ID"] > 0):
?>
								<div class="forum-user-name"><a href="<?=$res["URL"]["AUTHOR"]?>"><span><?=$res["AUTHOR_NAME"]?></span></a></div>
<?
			if (is_array($res["AVATAR"]) && (strLen($res["AVATAR"]["HTML"]) > 0)):
?>
								<div class="forum-user-avatar"><?
									?><a href="<?=$res["URL"]["AUTHOR"]?>" title="<?=GetMessage("F_AUTHOR_PROFILE")?>"><?
										?><?=$res["AVATAR"]["HTML"]?></a></div>
<?
			else:
?>
								<div class="forum-user-register-avatar"><?
									?><a href="<?=$res["URL"]["AUTHOR"]?>" title="<?=GetMessage("F_AUTHOR_PROFILE")?>"><span><!-- ie --></span></a></div>
<?
			endif;
		else:
?>
								<div class="forum-user-name"><span><?=$res["AUTHOR_NAME"]?></span></div>
								<div class="forum-user-guest-avatar"><!-- ie --></div>
<?
		endif;
 		
/*		if (strLen(trim($res["AUTHOR_STATUS"]))):
?>
								<div class="forum-user-status <?=(!empty($res["AUTHOR_STATUS_CODE"]) ? "forum-user-".$res["AUTHOR_STATUS_CODE"]."-status" : "")?>"><?
									?><span><?=$res["AUTHOR_STATUS"]?></span></div>
<?
		endif;
*/
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
<?
		if (strlen($res["DESCRIPTION"]) > 0):
?>
								<div class="forum-user-description"><span><?=$res["DESCRIPTION"]?></span></div>
<?
		endif;

?>
							</div>
						</td>
						<td class="forum-cell-post">
							<div class="forum-post-date">
								<div class="forum-post-number"><a href="http://<?=$_SERVER["HTTP_HOST"]?><?=$res["URL"]["MESSAGE"]?>#message<?=$res["ID"]?>" <?
									?>onclick="prompt(oText['ml'], this.href); return false;" title="<?=GetMessage("F_ANCHOR")?>" rel="nofollow">#<?=$res["NUMBER"]?></a><?
							if ($arResult["USER"]["PERMISSION"] >= "Q"):
								?>&nbsp;<?
							endif;
								?></div>
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
										?><a href="<?=$res["URL"]["EDITOR"]?>"><?=$res["EDITOR_NAME"]?></a><?
								else:
										?><?=$res["EDITOR_NAME"]?><?
								endif;
									?></span> - <span class="forum-post-lastedit-date"><?=$res["EDIT_DATE"]?></span>
<?
								if (!empty($res["EDIT_REASON"])):
?>
								<span class="forum-post-lastedit-reason">(<span><?=$res["EDIT_REASON"]?></span>)</span>
<?
								endif;
?>
							</span></div><?
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
<?
		if ($arResult["USER"]["PERMISSION"] >= "Q"):
?>
							<div class="forum-post-entry forum-user-additional forum-user-moderate-info">
<?
			if ($res["IP_IS_DIFFER"] == "Y"):
?>								
									<span>IP<?=GetMessage("F_REAL_IP")?>: <span><?=$res["AUTHOR_IP"];?> / <?=$res["AUTHOR_REAL_IP"];?></span></span>
<?
			else:
?>								
									<span>IP: <span><?=$res["AUTHOR_IP"];?></span></span>
<?
			endif;
			if ($res["PANELS"]["STATISTIC"] == "Y"):
?>
									<span><?=GetMessage("F_USER_ID")?>: <span><a href="/bitrix/admin/guest_list.php?lang=<?=LANG_ADMIN_LID?><?
										?>&amp;find_id=<?=$res["GUEST_ID"]?>&amp;set_filter=Y"><?=$res["GUEST_ID"];?></a></span></span>
<?
			endif;
					
			if ($res["PANELS"]["MAIN"] == "Y"):
?>
									<span><?=GetMessage("F_USER_ID_USER")?>: <span><?
										?><a href="/bitrix/admin/user_edit.php?lang=<?=LANG_ADMIN_LID?>&amp;ID=<?=$res["AUTHOR_ID"]?>"><?=$res["AUTHOR_ID"];?></a></span></span>
<?
			endif;
?>
							</div>
<?
		endif;
?>						</td>
					</tr>
					<tr>
						<td class="forum-cell-contact">
							<div class="forum-contact-links">
<?
					if ($res["AUTHOR_ID"] > 0 && $USER->IsAuthorized()):
?>
								<span class="forum-contact-message"><a href="<?=$res["URL"]["AUTHOR_PM"]?>" title="<?=GetMessage("F_PRIVATE_MESSAGE_TITLE")?>"><?
									?><?=GetMessage("F_PRIVATE_MESSAGE")?></a></span>&nbsp;&nbsp;
<?
					endif;
					if ($arParams["SHOW_MAIL"] == "Y" && strlen($res["EMAIL"]) > 0):
?>
							<span class="forum-contact-email"><a href="<?=$res["URL"]["AUTHOR_EMAIL"]?>" title="<?=GetMessage("F_EMAIL_TITLE")?>">E-mail</a></span>
<?
					elseif (!($res["AUTHOR_ID"] > 0 && $USER->IsAuthorized())):
?>
							&nbsp;
<?
					endif;
?>
							</div>
						</td>
						<td class="forum-cell-actions">

						</td>
					</tr>
				</tbody>
<?
	if ($iCount < count($arResult["MESSAGE_LIST"])):
?>
			</table>
<?
	endif;
endforeach;
?>
				 <tfoot>
					<tr>
						<td colspan="5" class="forum-column-footer">
							&nbsp;
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
</form>
<script>
if (typeof oForum != "object")
	var oForum = {};
oForum['topic_search'] = {
	'url' : '<?=CUtil::JSEscape($arResult["topic_search"])?>',
	'object' : false,
	'value' : '<?=intVal($arResult["newTID"])?>', 
	'action' : 'search', 
	'fined' : {}};

if (typeof oText != "object")
		var oText = {};
oText['topic_not_found'] = '<?=CUtil::addslashes(GetMessage("F_BAD_TOPIC"))?>';
oText['topic_bad'] = '<?=CUtil::addslashes('<font class="starrequired">'.GetMessage("F_BAD_NEW_TOPIC").'</font>')?>';
oText['topic_wait'] = '<?=CUtil::addslashes('<i>'.GetMessage("FORUM_MAIN_WAIT").'</i>')?>';
oText['cdms'] = '<?=CUtil::addslashes(GetMessage("F_DELETE_MESSAGES_CONFIRM"))?>';
</script>
