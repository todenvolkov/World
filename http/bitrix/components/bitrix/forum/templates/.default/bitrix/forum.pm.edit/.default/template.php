<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/.default/script.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/popup/script.js"></script>', true);
IncludeAJAX();
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
/*******************************************************************/
if (LANGUAGE_ID == 'ru')
{
	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/ru/script.php");
	@include_once($path);
}
$tabIndex = 1;
/********************************************************************
				/Input params
********************************************************************/
?>
<div style="float:right;">
	<div class="out"><div class="in" style="width:<?=$arResult["count"]?>%">&nbsp;</div></div>
	<div class="out1"><div class="in1"><?=GetMessage("F_POST_FULLY")." ".$arResult["count"]?>%</div></div>
</div>
<div class="forum-clear-float"></div>

<a name="postform"></a>
<div class="forum-header-box">
	<div class="forum-header-options">
<?
if ($arResult["mode"] != "new"):
?>
	<span class="forum-option-folder"><a href="<?=$arResult["URL"]["HELP"]?>"><?=$arResult["FolderName"]?></a></span>
	
<?
endif;
?>
	</div>
	<div class="forum-header-title"><span><?
if ($arResult["mode"] != "new"):
?>
	<?=$arResult["FolderName"]?>
<?
else:
?>
	<?=GetMessage("F_NEW_PM")?>
<?
endif;
	?></span></div>
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
<IFRAME style="width:0px; height:0px; border: 0px" src="javascript:void(0)" name="frame_USER_ID" id="frame_USER_ID"></IFRAME>
<form name="REPLIER" id="REPLIER" action="<?=POST_FORM_ACTION_URI?>" method="POST" onsubmit="return ValidateForm(this);"<?
	?> onkeydown="if(null != init_form){init_form(this)}" onmouseover="if(init_form){init_form(this)}" class="forum-form">
	<input type="hidden" name="PAGE_NAME" value="pm_edit" />
	<input type="hidden" name="action" id="action" value="<?=$arResult["action"]?>" />
	<input type="hidden" name="FID" value="<?=$arResult["FID"]?>" />
	<input type="hidden" name="MID" value="<?=$arResult["MID"]?>" />
	<input type="hidden" name="mode" value="<?=$arResult["mode"]?>" />
	<input type="hidden" name="USER_ID" id="USER_ID" value="<?=$arResult["POST_VALUES"]["USER_ID"]?>" readonly="readonly" />
	<?=bitrix_sessid_post()?>
	
	<div class="forum-reply-fields">
		<div class="forum-reply-field forum-reply-field-title">
			<label for="POST_SUBJ"><?=GetMessage("F_HEAD_SUBJ")?><span class="forum-required-field">*</span></label>
			<input name="POST_SUBJ" id="POST_SUBJ" type="text" value="<?=$arResult["POST_VALUES"]["POST_SUBJ"];?>" tabindex="<?=$tabIndex++;?>" size="70" />
		</div>
		<div class="forum-reply-field-user">
			<div class="forum-reply-field forum-reply-field-author"><label for="input_USER_ID"><?=GetMessage("F_HEAD_TO")
				?><span class="forum-required-field">*</span></label>
				<span><input type="text" name="input_USER_ID" id="input_USER_ID" tabindex="<?=$tabIndex++;?>" <?
					?>value="<?
					if (!empty($arResult["POST_VALUES"]["SHOW_NAME"]["text"])):
						?><?=$arResult["POST_VALUES"]["SHOW_NAME"]["text"]?><?
					elseif (!empty($arResult["POST_VALUES"]["USER_ID"])):
						?><?=$arResult["POST_VALUES"]["USER_ID"]?><?
					endif;
					?>" onfocus="fSearchUser()" /></span>
			</div>
			<div class="forum-reply-field-user-sep">&nbsp;</div>
			<div class="forum-reply-field forum-reply-field-email"><br />
				<span class="forum-pmessage-recipient">
<?
	if ($arResult["mode"] != "edit"):
?>
				<a href="javascript:void(0);" onclick="window.open('<?=$arResult["pm_search"]?>', '', 'scrollbars=yes,resizable=yes,width=760,height=500,<?
					?>top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));" title="<?=GetMessage("F_SEARCH_USER")?>">
					<?=GetMessage("F_FIND_USER")?></a>
<?
	endif;
?>
				<span id="div_USER_ID" name="div_USER_ID"><?
				if (!empty($arResult["POST_VALUES"]["SHOW_NAME"])):
					?>[<a href="<?=$arResult["POST_VALUES"]["SHOW_NAME"]["link"]?>"><?=$arResult["POST_VALUES"]["SHOW_NAME"]["text"]?></a>]<?
				elseif (!empty($arResult["POST_VALUES"]["USER_ID"])):
					?><i><?=GetMessage("PM_NOT_FINED");?></i><?
				endif;
				?></span></span>
				</div>
			
			<div class="forum-clear-float"></div>
		</div>
	</div>
	
	<div class="forum-reply-header"><?=GetMessage("F_HEAD_MESS")?><span class="forum-required-field">*</span></div>

	<div class="forum-reply-fields">

		<div class="forum-reply-field forum-reply-field-bbcode">

			<div class="forum-bbcode-line">
				<a href="#postform" class="forum-bbcode-button forum-bbcode-bold" id="form_b" title="<?=GetMessage("F_BOLD")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-italic" id="form_i" title="<?=GetMessage("F_ITAL")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-underline" id="form_u" title="<?=GetMessage("F_UNDER")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-strike" id="form_s" title="<?=GetMessage("F_STRIKE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-quote" id="form_quote" title="<?=GetMessage("F_QUOTE_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-code" id="form_code" title="<?=GetMessage("F_CODE_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-url" id="form_url" title="<?=GetMessage("F_HYPERLINK_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-img" id="form_img" title="<?=GetMessage("F_IMAGE_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-video" id="form_video" title="<?=GetMessage("F_VIDEO_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-list" id="form_list" title="<?=GetMessage("F_LIST_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-color" id="form_palette" title="<?=GetMessage("F_COLOR_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
if (LANGUAGE_ID == 'ru'):
?>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-translit" id="form_translit" title="<?=GetMessage("F_TRANSLIT_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
?>
				<select name='FONT' class="forum-bbcode-font" id='form_font' title="<?=GetMessage("F_FONT_TITLE")?>">
					<option value='none'><?=GetMessage("F_FONT")?></option>
					<option value='Arial' style='font-family:Arial'>Arial</option>
					<option value='Times' style='font-family:Times'>Times</option>
					<option value='Courier' style='font-family:Courier'>Courier</option>
					<option value='Impact' style='font-family:Impact'>Impact</option>
					<option value='Geneva' style='font-family:Geneva'>Geneva</option>
					<option value='Optima' style='font-family:Optima'>Optima</option>
					<option value='Verdana' style='font-family:Verdana'>Verdana</option>
				</select>
			</div>
			<div class="forum-smiles-line">
<?
	$iMaxH = 0;
	foreach ($arResult["SMILES"] as $res):
		$iMaxH = ($iMaxH > intVal($res['IMAGE_HEIGHT']) ? $iMaxH : intVal($res['IMAGE_HEIGHT']));
	endforeach;
	
	foreach ($arResult["SMILES"] as $res):
		$TYPING = strtok($res['TYPING'], " ");
		
?>
			<span class="forum-smiles-item" style="height:<?=$iMaxH?>px;">
				<a href="#postform" name="smiles" style="margin-top:<?=(round(($iMaxH - $res['IMAGE_HEIGHT'])/2))?>px;">
					<img src="<?=$arParams["PATH_TO_SMILE"].$res['IMAGE']?>" class="smiles"<?
		if (intVal($res['IMAGE_WIDTH']) > 0):
						?> width="<?=$res['IMAGE_WIDTH']?>" <?
		endif;
		if (intVal($res['IMAGE_HEIGHT']) > 0):
						?> height="<?=$res['IMAGE_HEIGHT']?>" <?
		endif;
					?> alt="<?=$TYPING?>" title="<?=$res['NAME']?>" border="0" />
				</a>
			</span>
<?
	endforeach;
?>
			</div>
		</div>
		<div class="forum-reply-field forum-reply-field-text">
			<textarea name="POST_MESSAGE" class="post_message" cols="55" rows="14" tabindex="<?=$tabIndex++;?>"><?=$arResult["POST_VALUES"]["POST_MESSAGE"]?></textarea>
		</div>
		
		<div class="forum-reply-field forum-reply-field-settings">
			<div class="forum-reply-field-setting">
				<input type="checkbox" name="USE_SMILES" id="USE_SMILES" <?
				?>value="Y" <?=($arResult["POST_VALUES"]["USE_SMILES"]=="Y") ? "checked=\"checked\"" : "";?> <?
				?>tabindex="<?=$tabIndex++;?>" />&nbsp;<label for="USE_SMILES"><?=GetMessage("F_WANT_ALLOW_SMILES")?></label></div>
			
<?
	if ($arParams["version"] == 2 && $arResult["action"] == "send"):
?>
			<div class="forum-reply-field-setting">
				<input type="checkbox" name="COPY_TO_OUTBOX" id="COPY_TO_OUTBOX" value="Y" tabindex="<?=$tabIndex++;?>" <?
				?><?=(($arResult["POST_VALUES"]["COPY_TO_OUTBOX"] != "N") ? "checked" : "")?> />&nbsp;<?
				?><label for="COPY_TO_OUTBOX"><?=GetMessage("F_COPY_TO_OUTBOX")?></label></div>
			<div class="forum-reply-field-setting">
				<input type="checkbox" name="REQUEST_IS_READ" id="REQUEST_IS_READ" value="Y" tabindex="<?=$tabIndex++;?>" <?
					?><?=(($arResult["POST_VALUES"]["REQUEST_IS_READ"] == "Y") ? "checked" : "")?> />&nbsp;<?
				?><label for="REQUEST_IS_READ"><?=GetMessage("F_REQUEST_IS_READ")?></label></div><?
	endif;
?>
		</div>
		<div class="forum-reply-buttons">
			<input type="submit" name="SAVE_BUTTON" id="SAVE_BUTTON" tabindex="<?=$tabIndex++;?>" <?
				?> value="<?=($arResult["action"] == "save" ? GetMessage("F_ACT_SAVE") : GetMessage("F_ACT_SEND"))?>" tabindex="<?=$tabIndex++;?>" />
		</div>
	</div>
</div>
</form>

<script language="Javascript">
<?if (!empty($arResult["POST_VALUES"]["SHOW_NAME"]["text"])):?>
window.switcher = '<?=CUtil::JSEscape($arResult["POST_VALUES"]["SHOW_NAME"]["text"])?>';
<?elseif (!empty($arResult["POST_VALUES"]["USER_ID"])):?>
window.switcher = '<?=CUtil::JSEscape($arResult["POST_VALUES"]["USER_ID"])?>';
<?else:?>
window.switcher = '';
<?endif;?>
function fSearchUser()
{
	var name = 'USER_ID';
	var template_path = '<?=CUtil::JSEscape($arResult["pm_search_for_js"])?>';
	var handler = document.getElementById('input_'+name);
	var div_ = document.getElementById('div_'+name);
	if (typeof handler != "object" || null == handler || typeof div_ != "object")
		return false;
	
	
	if (window.switcher != handler.value)
	{
		window.switcher = handler.value;
		handler.form.elements[name].value=handler.value;
		if (handler.value != '')
		{
			div_.innerHTML = '<i><?=CUtil::JSEscape(GetMessage("FORUM_MAIN_WAIT"))?></i>';
			document.getElementById('frame_'+name).src=template_path.replace(/\#LOGIN\#/gi, handler.value);
		}
		else
			div_.innerHTML = '';
	}
	setTimeout(fSearchUser, 1000);
	return true;
}
fSearchUser();

var bSendForm = false;
if (typeof oErrors != "object")
	var oErrors = {};
oErrors['no_topic_name'] = "<?=CUtil::JSEscape(GetMessage("JERROR_NO_TOPIC_NAME"))?>";
oErrors['no_message'] = "<?=CUtil::JSEscape(GetMessage("JERROR_NO_MESSAGE"))?>";
oErrors['max_len'] = "<?=CUtil::JSEscape(GetMessage("JERROR_MAX_LEN"))?>";
oErrors['no_url'] = "<?=CUtil::JSEscape(GetMessage("FORUM_ERROR_NO_URL"))?>";
oErrors['no_title'] = "<?=CUtil::JSEscape(GetMessage("FORUM_ERROR_NO_TITLE"))?>";
oErrors['no_path'] = "<?=CUtil::JSEscape(GetMessage("FORUM_ERROR_NO_PATH_TO_VIDEO"))?>";
if (typeof oText != "object")
	var oText = {};
oText['author'] = " <?=CUtil::JSEscape(GetMessage("JQOUTE_AUTHOR_WRITES"))?>:\n";
oText['enter_url'] = "<?=CUtil::JSEscape(GetMessage("FORUM_TEXT_ENTER_URL"))?>";
oText['enter_url_name'] = "<?=CUtil::JSEscape(GetMessage("FORUM_TEXT_ENTER_URL_NAME"))?>";
oText['enter_image'] = "<?=CUtil::JSEscape(GetMessage("FORUM_TEXT_ENTER_IMAGE"))?>";
oText['list_prompt'] = "<?=CUtil::JSEscape(GetMessage("FORUM_LIST_PROMPT"))?>";
oText['video'] = "<?=CUtil::JSEscape(GetMessage("FORUM_VIDEO"))?>";
oText['path'] = "<?=CUtil::JSEscape(GetMessage("FORUM_PATH"))?>:";
oText['width'] = "<?=CUtil::JSEscape(GetMessage("FORUM_WIDTH"))?>:";
oText['height'] = "<?=CUtil::JSEscape(GetMessage("FORUM_HEIGHT"))?>:";

oText['BUTTON_OK'] = "<?=CUtil::JSEscape(GetMessage("FORUM_BUTTON_OK"))?>";
oText['BUTTON_CANCEL'] = "<?=CUtil::JSEscape(GetMessage("FORUM_BUTTON_CANCEL"))?>";

if (typeof oHelp != "object")
	var oHelp = {};

</script>