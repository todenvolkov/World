<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["form_index"] = $_REQUEST["INDEX"];
if (!empty($arParams["form_index"]))
	$arParams["form_index"] = preg_replace("/[^a-z0-9]/is", "_", $arParams["form_index"]);
$tabIndex = 10;
/********************************************************************
				/Input params
********************************************************************/

if (!empty($arResult["OK_MESSAGE"])): 
	ShowNote($arResult["OK_MESSAGE"], "forum-note");
endif;

?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=($arParams["TYPE"] == "ICQ" ? GetMessage("F_TITLE_ICQ") : GetMessage("F_TITLE_MAIL"))?> 
		<a href="<?=$arResult["URL"]["RECIPIENT"]?>"><?=$arResult["ShowName"]?></a></span></div>
</div>

<div class="forum-reply-form">
<?
if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;
?>
<form action="<?=POST_FORM_ACTION_URI?>" method="post" name="REPLIER" class="forum-form">
	<input type="hidden" name="PAGE_NAME" value="message_send" />
	<input type="hidden" name="ACTION" value="SEND" />
	<input type="hidden" name="TYPE" value="<?=$arParams["TYPE"]?>" />
	<input type="hidden" name="UID" value="<?=$arParams["UID"]?>" />
	<?=bitrix_sessid_post()?>
	
	<div class="forum-reply-fields">
		<div class="forum-reply-field forum-reply-field-title">
			<label for="SUBJECT<?=$arParams["form_index"]?>"><?=GetMessage("F_TOPIC")?><span class="forum-required-field">*</span></label>
			<input name="SUBJECT" id="SUBJECT<?=$arParams["form_index"]?>" type="text" value="<?=$arResult["MailSubject"]?>" <?
				?>tabindex="<?=$tabIndex++;?>" size="70" maxlength="50" />
		</div>
<?
if ($arResult["IsAuthorized"] != "Y" || empty($arResult["AuthorContacts"])):
?>
		<div class="forum-reply-field-user">
<?
	if ($arResult["IsAuthorized"] != "Y"):
?>
			<div class="forum-reply-field forum-reply-field-author"><label for="NAME<?=$arParams["form_index"]?>"><?=GetMessage("F_NAME")?><?
				?><span class="forum-required-field">*</span></label>
				<span>
					<input type="text" name="NAME" id="NAME<?=$arParams["form_index"]?>" value="<?=$arResult["AuthorName"]?>" size="30" tabindex="<?=$tabIndex++;?>" />
				</span>
			</div>
<?		
	endif;
	if (empty($arResult["AuthorContacts"])):
?>
			<div class="forum-reply-field-user-sep">&nbsp;</div>
			<div class="forum-reply-field forum-reply-field-email"><label for="EMAIL<?=$arParams["form_index"]?>"><?
				?><?=($arParams["TYPE"] == "ICQ" ? GetMessage("F_ICQ") : GetMessage("F_EMAIL"))?><?
				?><span class="forum-required-field">*</span></label>
				<span>
					<input type="text" name="EMAIL" id="EMAIL<?=$arParams["form_index"]?>"  tabindex="<?=$tabIndex++;?>"value="<?=$arResult["AuthorMail"]?>" size="30" />
				</span>
			</div>
<?
	endif;
?>
			<div class="forum-clear-float"></div>
		</div>
<?
endif;
?>
	</div>
	<div class="forum-reply-header"><?=GetMessage("F_TEXT")?><span class="forum-required-field">*</span></div>
	<div class="forum-reply-fields">
		<div class="forum-reply-field forum-reply-field-text">
			<textarea name="MESSAGE" cols="55" rows="14" tabindex="<?=$tabIndex++;?>"><?=$arResult["MailMessage"]?></textarea>
		</div>
<?
if (!empty($arResult["CAPTCHA_CODE"])):
?>
		<div class="forum-reply-field forum-reply-field-captcha">
			<input type="hidden" name="captcha_code" value="<?=$arResult["CAPTCHA_CODE"]?>"/>
			<div class="forum-reply-field-captcha-label">
				<label for="captcha_word"><?=GetMessage("F_CAPTCHA_PROMT")?><span class="forum-required-field">*</span></label>
				<input type="text" size="30" name="captcha_word" tabindex="<?=$tabIndex++;?>" />
			</div>
			<div class="forum-reply-field-captcha-image">
				<img src="/bitrix/tools/captcha.php?captcha_code=<?=$arResult["CAPTCHA_CODE"]?>" alt="<?=GetMessage("F_CAPTCHA_TITLE")?>" />
			</div>
		</div>
<?
endif;
?>
		<div class="forum-reply-buttons">
			<input type="submit" value="<?=GetMessage("F_SEND")?> <?=$arResult["strTextType"]?>"  tabindex="<?=$tabIndex++;?>" />
		</div>
	</div>
</div>
</form>