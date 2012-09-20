<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/.default/script.js"></script>', true);
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["SHOW_MAIL"] = (($arParams["SEND_MAIL"] <= "A" || ($arParams["SEND_MAIL"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y");

$iIndex = rand();
if ($_SERVER['REQUEST_METHOD'] == "POST"):
	$message = (empty($_POST["MID_ARRAY"]) ? $_POST["MID"] : $_POST["MID_ARRAY"]);
	$message = (empty($message) ? $_POST["message_id"] : $message);
	$action = strToUpper($_POST["ACTION"]);
else:
	$message = (empty($_GET["MID_ARRAY"]) ? $_GET["MID"] : $_GET["MID_ARRAY"]);
	$message = (empty($message) ? $_GET["message_id"] : $message);
	$action = strToUpper($_GET["ACTION"]);
endif;
$message = (is_array($message) ? $message : array($message));
/********************************************************************
				/Input params
********************************************************************/
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
	<div class="forum-header-options">
<?
if ($arParams["TID"] > 0):
?>
		<span class="forum-option-topic"><a href="<?=$arResult["read"]?>"><?=$arResult["TOPIC"]["TITLE"]?></a></span>
<?
endif;
?>
	<span class="forum-option-subscribe"><a href="<?=$arResult["list"]?>"><?=$arResult["FORUM"]["NAME"]?></a></span>
	</div>
	<div class="forum-header-title"><span><?=GetMessage("F_TITLE")?></span></div>
</div>
<?

if (empty($arResult["MESSAGE_LIST"])):
?>

<div class="forum-info-box forum-posts-notapproved">
	<div class="forum-info-box-inner">
		<?=GetMessage("F_EMPTY_RESULT")?>
	</div>
</div>
<?
	return false;
endif;

?>
<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" <?
		?>onsubmit="return Validate(this)" name="MESSAGES" id="MESSAGES" >
	<input type="hidden" name="PAGE_NAME" value="message_approve">
	<?=bitrix_sessid_post()?>
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
				 ?><?=($res["APPROVED"] == "Y" ? "" : "forum-post-hidden ")?> <?=(in_array($res["ID"], $message) ? "forum-post-selected" : "")?>">
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
								?><input type="checkbox" name="message_id[]" value="<?=$res["ID"]?>" id="message_id_<?=$res["ID"]?>_" <?
									if (in_array($res["ID"], $message)):
									?> checked="checked" <?
									endif;
									?> onclick="SelectPost(this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode)" /><?
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
							<div class="forum-action-links">
								<span class="forum-action-show"><a href="<?=$res["URL"]["MODERATE"]?>"><?=GetMessage("F_SHOW")?></a></span>&nbsp;&nbsp;
<?
					if ($res["PANELS"]["DELETE"] == "Y"):
?>
								 <span class="forum-action-delete"><a href="<?=$res["URL"]["DELETE"]?>" <?
								 	?>onclick="return confirm(oText['cdm']);"><?=GetMessage("F_DELETE")?></a></span>&nbsp;&nbsp;
<?
					endif;
					if ($res["PANELS"]["EDIT"] == "Y"):
?>
								 <span class="forum-action-edit"><a href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT")?></a></span>&nbsp;&nbsp;
<?
					endif;
?>
							</div>
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
							<div class="forum-footer-inner">
								<?=bitrix_sessid_post()?>
								<input type="hidden" name="PAGE_NAME" value="read" />
								<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
								<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
								<div class="forum-post-moderate">
									<select name="ACTION">
										<option value=""><?=GetMessage("F_MANAGE_MESSAGES")?></option>
										<option value="SHOW" <?=($action == "SHOW" ? " selected='selected' " : "")?>><?=GetMessage("F_SHOW_MESSAGES")?></option>
<?
	if ($arResult["USER"]["RIGHTS"]["EDIT"] == "Y"):
?>
										<option value="DEL" <?=($action == "DEL" ? " selected='selected' " : "")?>><?=GetMessage("F_DELETE_MESSAGES")?></option>
<?
	endif;
?>
									</select>&nbsp;<input type="submit" value="OK" />
								</div>
							</div>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
</form>
<?

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
oText['cdm'] = '<?=CUtil::addslashes(GetMessage("F_DELETE_CONFIRM"))?>';
oText['cdms'] = '<?=CUtil::addslashes(GetMessage("F_DELETE_MESSAGES_CONFIRM"))?>';
oText['ml'] = '<?=CUtil::addslashes(GetMessage("F_ANCHOR_TITLE"))?>';
oText['no_data'] = '<?=CUtil::addslashes(GetMessage('JS_NO_MESSAGES'))?>';
oText['no_action'] = '<?=CUtil::addslashes(GetMessage('JS_NO_ACTION'))?>';
</script>