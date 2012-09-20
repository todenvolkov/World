<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*******************************************************************/
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/.default/script.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/popup/script.js"></script>', true);
IncludeAJAX();
$arUserSettings = array("smiles" => "hide");
if ($GLOBALS["USER"]->IsAuthorized())
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
	$arUserSettings = @unserialize(CUserOptions::GetOption("forum", "default_template", ""));
}

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] != "N" ? "Y" : "Y");
$arParams["FILES_COUNT"] = intVal(intVal($arParams["FILES_COUNT"]) > 0 ? $arParams["FILES_COUNT"] : 5);
$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 100);
$arParams["SMILES_COUNT"] = (intVal($arParams["SMILES_COUNT"]) > 0 ? intVal($arParams["SMILES_COUNT"]) : 0);
$arParams["VOTE_COUNT_QUESTIONS"] = (intVal($arParams["VOTE_COUNT_QUESTIONS"]) > 0 ? intVal($arParams["VOTE_COUNT_QUESTIONS"]) : 10);
$arParams["VOTE_COUNT_ANSWERS"] = (intVal($arParams["VOTE_COUNT_ANSWERS"]) > 0 ? intVal($arParams["VOTE_COUNT_ANSWERS"]) : 20);
$arParams["form_index"] = $_REQUEST["INDEX"];
if (!empty($arParams["form_index"]))
	$arParams["form_index"] = preg_replace("/[^a-z0-9]/is", "_", $arParams["form_index"]);
$tabIndex = 10;
/*******************************************************************/
if (LANGUAGE_ID == 'ru')
{
	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/ru/script.php");
	@include_once($path);
}
/********************************************************************
				/Input params
********************************************************************/
?>
<a name="postform"></a>
<div class="forum-header-box">
	<div class="forum-header-options">
		<span class="forum-option-bbcode"><a href="<?=$arResult["URL"]["HELP"]?>#bbcode">BBCode</a></span>&nbsp;&nbsp;
		<span class="forum-option-rules"><a href="<?=$arResult["URL"]["RULES"]?>"><?=GetMessage("F_RULES")?></a></span>
	</div>
	<div class="forum-header-title"><span><?
if ($arResult["MESSAGE_TYPE"] == "NEW"):
	?><?=GetMessage("F_CREATE_IN_FORUM")?>: <a href="<?=$arResult["URL"]["LIST"]?>"><?=$arResult["FORUM"]["NAME"]?></a><?
elseif ($arResult["MESSAGE_TYPE"] == "REPLY"):
	?><?=GetMessage("F_REPLY_FORM")?><?
else:
	?><?=GetMessage("F_EDIT_FORM")?> <?=GetMessage("F_IN_TOPIC")?>: 
		<a href="<?=$arResult["URL"]["READ"]?>"><?=htmlspecialcharsEx($arResult["TOPIC_FILTER"]["TITLE"])?></a>, <?=GetMessage("F_IN_FORUM")?>: 
		<a href="<?=$arResult["URL"]["LIST"]?>"><?=$arResult["FORUM"]["NAME"]?></a><?
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

<form name="REPLIER<?=$arParams["form_index"]?>" id="REPLIER<?=$arParams["form_index"]?>" action="<?=POST_FORM_ACTION_URI?>#postform"<?
	?> method="POST" enctype="multipart/form-data" onsubmit="return ValidateForm(this, '<?=$arParams["AJAX_TYPE"]?>');"<?
	?> onkeydown="if(null != init_form){init_form(this)}" onmouseover="if(init_form){init_form(this)}" class="forum-form">
	<input type="hidden" name="PAGE_NAME" value="<?=$arParams["PAGE_NAME"];?>" />
	<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
	<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
	<input type="hidden" name="MID" value="<?=$arResult["MID"];?>" />
	<input type="hidden" name="MESSAGE_TYPE" value="<?=$arParams["MESSAGE_TYPE"];?>" />
	<input type="hidden" name="AUTHOR_ID" value="<?=$arResult["TOPIC"]["AUTHOR_ID"];?>" />
	<input type="hidden" name="forum_post_action" value="save" />
	<input type="hidden" name="MESSAGE_MODE" value="NORMAL" />
	<?=bitrix_sessid_post()?>
<?
if (($arResult["SHOW_PANEL_NEW_TOPIC"] == "Y" || $arResult["SHOW_PANEL_GUEST"] == "Y") && $arParams["AJAX_CALL"] == "N"):
?>
	<div class="forum-reply-fields">
<?
/* NEW TOPIC */
if ($arResult["SHOW_PANEL_NEW_TOPIC"] == "Y"):
?>
		<div class="forum-reply-field forum-reply-field-title">
			<label for="TITLE<?=$arParams["form_index"]?>"><?=GetMessage("F_TOPIC_NAME")?><span class="forum-required-field">*</span></label>
			<input name="TITLE" id="TITLE<?=$arParams["form_index"]?>" type="text" value="<?=$arResult["TOPIC"]["TITLE"];?>" tabindex="<?=$tabIndex++;?>" size="70" /></div>
		<div class="forum-reply-field forum-reply-field-desc">
			<label for="DESCRIPTION<?=$arParams["form_index"]?>"><?=GetMessage("F_TOPIC_DESCR")?></label>
			<input name="DESCRIPTION" id="DESCRIPTION<?=$arParams["form_index"]?>" type="text" value="<?=$arResult["TOPIC"]["DESCRIPTION"];?>" tabindex="<?=$tabIndex++;?>" size="70"/></div>
<?
/*?> for the future
	<tr title="<?=GetMessage("F_TOPIC_ICON_DESCRIPTION")?>"><td>
		<span class="title title-icons"><?=GetMessage("F_TOPIC_ICON")?></span>
		<span class="value value-icons"><?=$arResult["ForumPrintIconsList"];?></span></td></tr>
<?*/
endif;
/* GUEST PANEL */
if ($arResult["SHOW_PANEL_GUEST"] == "Y"):
?>
		<div class="forum-reply-field-user">
			<div class="forum-reply-field forum-reply-field-author"><label for="AUTHOR_NAME<?=$arParams["form_index"]?>"><?=GetMessage("F_TYPE_NAME")?><?
				?><span class="forum-required-field">*</span></label>
				<span><input name="AUTHOR_NAME" id="AUTHOR_NAME<?=$arParams["form_index"]?>" size="30" type="text" value="<?=$arResult["MESSAGE"]["AUTHOR_NAME"];?>" tabindex="<?=$tabIndex++;?>" /></span></div>
<?		
	if ($arResult["FORUM"]["ASK_GUEST_EMAIL"]=="Y"):
?>
			<div class="forum-reply-field-user-sep">&nbsp;</div>
			<div class="forum-reply-field forum-reply-field-email"><label for="AUTHOR_EMAIL<?=$arParams["form_index"]?>"><?=GetMessage("F_TYPE_EMAIL")?></label>
				<span><input type="text" name="AUTHOR_EMAIL" id="AUTHOR_EMAIL<?=$arParams["form_index"]?>" size="30" value="<?=$arResult["MESSAGE"]["AUTHOR_EMAIL"];?>" tabindex="<?=$tabIndex++;?>" /></span></div>
<?
	endif;
?>
			<div class="forum-clear-float"></div>
		</div>
<?
endif;
	
if ($arResult["SHOW_PANEL_NEW_TOPIC"] == "Y" && $arParams["SHOW_TAGS"] == "Y"):
	$iIndex = $tabIndex++;
?>
		<div class="forum-reply-field forum-reply-field-tags" <?
	if (!empty($arResult["TOPIC"]["TAGS"])):
			?> style="display:block; "<?
	endif;
			?>>
			<label for="TAGS"><?=GetMessage("F_TOPIC_TAGS")?></label>
<?
		if ($arResult["SHOW_SEARCH"] == "Y"):
		$APPLICATION->IncludeComponent(
			"bitrix:search.tags.input", 
			"", 
			array(
				"VALUE" => $arResult["TOPIC"]["~TAGS"], 
				"NAME" => "TAGS",
				"TEXT" => 'tabindex="'.$iIndex.'" size="70" onmouseover="CorrectTags(this)"', 
				"TMPL_IFRAME" => "N"),
			$component,
			array("HIDE_ICONS" => "Y"));
		?><iframe id="TAGS_div_frame" name="TAGS_div_frame" src="javascript:void(0);" style="display:none;"/></iframe><?
		else:
			?><input name="TAGS" type="text" value="<?=$arResult["TOPIC"]["TAGS"]?>" tabindex="<?=$iIndex?>" size="70" /><?
		endif;
?>
		</div><?
	if (empty($arResult["TOPIC"]["TAGS"])):
		?><div class="forum-reply-field forum-reply-field-addtags"><?
			?><a href="#" onclick="return AddTags(this);" onfocus="AddTags(this);" tabindex="<?=$iIndex?>"><?
				?><?=GetMessage("F_TOPIC_TAGS_DESCRIPTION")?><?
			?></a><?
		if ($arResult["SHOW_PANEL_NEW_TOPIC"] == "Y" && $arResult["SHOW_PANEL_VOTE"] == "Y" && empty($arResult["QUESTIONS"])):
			?>
			&nbsp;
			<a href="#" onclick="return ShowVote(this);" name="from_tag"><?=GetMessage("F_ADD_VOTE")?></a><?
		endif;
		?></div><?
	endif;
endif;
if ($arResult["SHOW_PANEL_NEW_TOPIC"] == "Y" && $arResult["SHOW_PANEL_VOTE"] == "Y" && empty($arResult["QUESTIONS"])):
		?><div class="forum-reply-field forum-reply-field-vote" id="vote_switcher" <?
			if (empty($arResult["TOPIC"]["TAGS"])):
		?>style="display:none;"<?
			endif;
		?>><a href="#" onclick="return ShowVote(this);"><?=GetMessage("F_ADD_VOTE")?></a>
		</div><?	
endif;
?>
	</div>
<?

if ($arResult["SHOW_PANEL_NEW_TOPIC"] == "Y" && $arResult["SHOW_PANEL_VOTE"] == "Y"):
$iCountQuestions = count($arResult["QUESTIONS"]);
$iCountQuestions = ($iCountQuestions > 0 ? $iCountQuestions : 1);
?>
<script>
var arVoteParams = {
	'count_q': <?=$iCountQuestions?>, 
	'coun_max_q': <?=($arParams["VOTE_COUNT_QUESTIONS"])?>, 
	'count_max_a': <?=($arParams["VOTE_COUNT_ANSWERS"])?>, 
	'template_answer' : ('<input type="text" name="ANSWER[#Q#][#A#]" value="" />' + 
				'<label>[<a onclick=\'return vote_remove_answer(this, "#Q#", "Y")\' title="<?=CUtil::JSEscape(GetMessage("F_VOTE_DROP_ANSWER"))?>" href="#">X</a>]</label>'), 
	'template_question' : ('<div class="forum-reply-field-vote-question-title">' + 
			'<input type="text" name="QUESTION[#Q#]" name="QUESTION_#Q#" value="" />' + 
			'<label>[<a onclick=\'return vote_remove_question(this, "Y")\' title="<?=CUtil::JSEscape(GetMessage("F_VOTE_DROP_QUESTION"))?>" href="#">X</a>]</label>' + 
		'</div>' + 
		'<div class="forum-reply-field-vote-question-options">' + 
			'<input type="checkbox" value="Y" name="MULTI[#Q#]" id="MULTI_#Q#" class="checkbox" />' + 
			'<label for="MULTI_#Q#"><?=CUtil::JSEscape(GetMessage("F_VOTE_MULTI"))?></label>' + 
		'</div>' + 
		'<ol class="forum-reply-field-vote-answers">' + 
			'<li><input type="text" name="ANSWER[#Q#][1]" value="" />' + 
				'<label>[<a onclick=\'return vote_remove_answer(this, "#Q#", "Y")\' title="<?=CUtil::JSEscape(GetMessage("F_VOTE_DROP_ANSWER"))?>" href="#">X</a>]</label></li>' + 
			'<li>[<a onclick=\'return vote_add_answer(this.parentNode, #Q#, 1)\' href="#"><?=CUtil::JSEscape(GetMessage("F_VOTE_ADD_ANSWER"))?></a>]</li>' + 
		'</ol>')};
</script>
	<div id="vote_params" <?
		if (empty($arResult["QUESTIONS"])):
	?>style="display:none;"<?
		endif;?>>
	<div class="forum-reply-header"><?=GetMessage("F_VOTE")?></div>
	<div class="forum-reply-fields">
		<div class="forum-reply-field forum-reply-field-vote">
<?
		$qq = 0;
		foreach ($arResult["QUESTIONS"] as $arQuestion):
			if ($arQuestion["DEL"] == "Y"):
				?><input type="hidden" name="QUESTION_ID[<?=$qq?>]" value="<?=$arQuestion["ID"]?>" /><?
				?><input type="hidden" name="QUESTION[<?=$qq?>]" value="<?=$arQuestion["QUESTION"]?>" /><?
				?><input type="hidden" name="QUESTION_DEL[<?=$qq?>]" value="Y" /><?
				continue;
			endif;
			$qq++;
?>		
			<div class="forum-reply-field-vote-question">
				<div class="forum-reply-field-vote-question-title">
					<input type="hidden" name="QUESTION_ID[<?=$qq?>]" value="<?=$arQuestion["ID"]?>" /><?
					?><input type="text" name="QUESTION[<?=$qq?>]" value="<?=$arQuestion["QUESTION"]?>" /><?
					?><input type="hidden" name="QUESTION_DEL[<?=$qq?>]" value="N" /><?
					?><label>[<a onclick='return vote_remove_question(this);' title="<?=GetMessage("F_VOTE_DROP_QUESTION")?>" href="#">X</a>]</label>
				</div>
				<div class="forum-reply-field-vote-question-options">
					<input type="checkbox" value="Y" name="MULTI[<?=$qq?>]" id="MULTI_<?=$qq?>" class="checkbox" <?
						?><?=($arQuestion["MULTI"] == "Y" ? "checked='checked'" : "")?> />
					<label for="MULTI_<?=$qq?>"><?=GetMessage("F_VOTE_MULTI")?></label>
				</div><?
				?><ol class="forum-reply-field-vote-answers"><?
			$aa = 0;
			foreach ($arQuestion["ANSWERS"] as $aa => $arAnswer):
				if ($arAnswer["DEL"] == "Y"):
					?><input type="hidden" name="ANSWER_ID[<?=$qq?>][<?=$aa?>]" value="<?=$arAnswer["ID"]?>" /><?
					?><input type="text" name="ANSWER[<?=$qq?>][<?=$aa?>]" value="<?=$arAnswer["MESSAGE"]?>" /><?
					?><input type="hidden" name="ANSWER_DEL[<?=$qq?>][<?=$aa?>]" value="Y" /><?
					continue;
				endif;
			
				$aa++;
					?><li><input type="hidden" name="ANSWER_ID[<?=$qq?>][<?=$aa?>]" value="<?=$arAnswer["ID"]?>" /><?
						?><input type="text" name="ANSWER[<?=$qq?>][<?=$aa?>]" value="<?=$arAnswer["MESSAGE"]?>" /><?
						?><input type="hidden" name="ANSWER_DEL[<?=$qq?>][<?=$aa?>]" value="N" /><?
						?><label>[<a onclick='return vote_remove_answer(this, "<?=$qq?>", "N");' title="<?=GetMessage("F_VOTE_DROP_ANSWER")?>" href="#">X</a>]</label></li><?
			endforeach;
					?><li <?=($aa >= $arParams["VOTE_COUNT_ANSWERS"] ? "style='display:none;'" : "")?>><?
						?>[<a onclick='return vote_add_answer(this.parentNode, "<?=$qq?>", "<?=$aa?>");' href="#"><?=GetMessage("F_VOTE_ADD_ANSWER")?></a>]</li><?
				?></ol>
			</div><?
		endforeach;
		if (empty($arResult["QUESTIONS"])):
			$qq++;
			?><div class="forum-reply-field-vote-question">
				<div class="forum-reply-field-vote-question-title"><?
					?><input type="text" name="QUESTION[1]" name="QUESTION_1" value="" /><?
					?><label>[<a onclick='return vote_remove_question(this, "Y")' title="<?=GetMessage("F_VOTE_DROP_QUESTION")?>" href="#">X</a>]</label><?
					?>
				</div>
				<div class="forum-reply-field-vote-question-options">
					<input type="checkbox" value="Y" name="MULTI[1]" id="MULTI_1" class="checkbox" />
					<label for="MULTI_1"><?=GetMessage("F_VOTE_MULTI")?></label>
				</div><?
				?><ol class="forum-reply-field-vote-answers"><?
					?><li><input type="text" name="ANSWER[1][1]" value="" /><?
					?><label>[<a onclick='return vote_remove_answer(this, "1", "Y")' title="<?=GetMessage("F_VOTE_DROP_ANSWER")?>" href="#">X</a>]</label></li><?
					?><li>[<a onclick='return vote_add_answer(this.parentNode, 1, 1)' href="#"><?=GetMessage("F_VOTE_ADD_ANSWER")?></a>]</li><?
				?></ol>
			</div><?
		endif;
			?>
			<div class="forum-reply-field-vote-question" id="vote_question_add" <?=($qq >= $arParams["VOTE_COUNT_QUESTIONS"] ? "style='display:none;'" : "")?>>
				<a onclick="return vote_add_question('<?=$qq?>', this.parentNode);" href="#"><?=GetMessage("F_VOTE_ADD_QUESTION")?></a>
			</div>
		</div>
	</div>
	</div>
<?
endif;

endif;
?>

	<div class="forum-reply-header"><?=GetMessage("F_MESSAGE_TEXT")?><span class="forum-required-field">*</span></div>

	<div class="forum-reply-fields">

		<div class="forum-reply-field forum-reply-field-bbcode">

			<div class="forum-bbcode-line" id="forum_bbcode_line<?=$arParams["form_index"]?>">
<?
if ($arResult["FORUM"]["ALLOW_BIU"] == "Y"):
?>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-bold" id="form_b" title="<?=GetMessage("F_BOLD")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-italic" id="form_i" title="<?=GetMessage("F_ITAL")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-underline" id="form_u" title="<?=GetMessage("F_UNDER")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-strike" id="form_s" title="<?=GetMessage("F_STRIKE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_QUOTE"] == "Y"):
?>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-quote" id="form_quote" title="<?=GetMessage("F_QUOTE_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_CODE"] == "Y"):
?>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-code" id="form_code" title="<?=GetMessage("F_CODE_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_ANCHOR"] == "Y"):
?>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-url" id="form_url" title="<?=GetMessage("F_HYPERLINK_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_IMG"] == "Y"):
?>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-img" id="form_img" title="<?=GetMessage("F_IMAGE_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_VIDEO"] == "Y"):
?>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-video" id="form_video" title="<?=GetMessage("F_VIDEO_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;

if ($arResult["FORUM"]["ALLOW_LIST"] == "Y"):
?>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-list" id="form_list" title="<?=GetMessage("F_LIST_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_FONT"] == "Y"):
?>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-color" id="form_palette" title="<?=GetMessage("F_COLOR_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["SHOW_PANEL_TRANSLIT"] == "Y"):
?>
				<a href="#postform" class="forum-bbcode-button forum-bbcode-translit" id="form_translit" title="<?=GetMessage("F_TRANSLIT_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_FONT"] == "Y"):
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
<?
endif;
?>
			</div>
<?
if ($arResult["FORUM"]["ALLOW_SMILES"]=="Y"):
	$iMaxH = 0;
	foreach ($arResult["SMILES"] as $res):
		$iMaxH = ($iMaxH > intVal($res['IMAGE_HEIGHT']) ? $iMaxH : intVal($res['IMAGE_HEIGHT']));
	endforeach;
	$iPaddingTop = round(($iMaxH-16)/2);
	$iPaddingTop = ($iPaddingTop > 0 ? $iPaddingTop : 0);
if ($arParams["SMILES_COUNT"] <= 0):
?>
<script>
document.write('<style>div.forum-smiles-corrected{height:<?=$iMaxH?>px; overflow:hidden;}</style>');
document.write('<style>div.forum-smiles-none{visibility:hidden;}</style>');
</script>
			<div class="forum-smiles-line forum-smiles-corrected forum-smiles-none" id="forum_smiles_line<?=$arParams["form_index"]?>">
<?
	foreach ($arResult["SMILES"] as $res):
		$TYPING = strtok($res['TYPING'], " ");
?>
				<span class="forum-smiles-item" style="height:<?=$iMaxH?>px;"><?
					?><a href="#postform" name="smiles" style="margin-top:<?=(round(($iMaxH - $res['IMAGE_HEIGHT'])/2))?>px;"><?
						?><img src="<?=$arParams["PATH_TO_SMILE"].$res['IMAGE']?>" class="smiles"<?
		if (intVal($res['IMAGE_WIDTH']) > 0):
							?> width="<?=$res['IMAGE_WIDTH']?>" <?
		endif;
		if (intVal($res['IMAGE_HEIGHT']) > 0):
							?> height="<?=$res['IMAGE_HEIGHT']?>" <?
		endif;
						?> alt="<?=$TYPING?>" title="<?=$res['NAME']?>" border="0" /><?
					?></a></span>
<?
	endforeach;
?>
			</div>
<script>
document.write('<div class="forum-reply-field forum-reply-field-showsmiles" id="forum_smile_switcher<?=$arParams["form_index"]?>" style="visibility:hidden;">');
document.write('<a href="#postform" id="form_smiles_dinamic"><?=CUtil::JSEscape(GetMessage("F_SHOW_SMILE"))?></a></div>');
jsUtils.addEvent(window, "load", function(e){ForumShowSmile(e)});
function ForumShowSmile(e)
{
	var sIndex = '<?=$arParams["form_index"]?>';
	var form = document.forms["REPLIER<?=$arParams["form_index"]?>"];
	var forum_bbcode_line = document.getElementById('forum_bbcode_line<?=$arParams["form_index"]?>');
	var forum_smiles_line = document.getElementById('forum_smiles_line<?=$arParams["form_index"]?>');
	var forum_smile_switcher = document.getElementById('forum_smile_switcher<?=$arParams["form_index"]?>');
	var params = {'width' : form.POST_MESSAGE.offsetWidth, 'width_real' : 0};
	var res = forum_smiles_line.getElementsByTagName('A');
	if (res && res.length > 0)
	{
		for (var ii = 0; ii < res.length; ii++)
		{
			params['width_real'] += parseInt(res[ii].parentNode.offsetWidth);
		}
	}
	if (params['width'] >= params['width_real'])
	{
		var res = document.createElement('DIV');
		res.className = "forum-clear-float";
		forum_smiles_line.parentNode.insertBefore(res, forum_smiles_line);
	}
	if (((params['width'] - forum_bbcode_line.offsetWidth) >= params['width_real']) || 
		params['width'] >= params['width_real'])
	{
		forum_smile_switcher.style.display = 'none';
		forum_smiles_line.className = forum_smiles_line.className.replace('forum-smiles-none', '');
		return true;
	}
	init_form(form);
	oForumForm[form.id].show_smiles_dinamic(forum_smile_switcher.firstChild, '<?=$arUserSettings["smiles"]?>', 'N');
	forum_smiles_line.className = forum_smiles_line.className.replace(/forum\-smiles\-none/gi, '');
	forum_smile_switcher.style.visibility = 'visible';
}
</script>
<?
elseif ($arParams["SMILES_COUNT"] < count($arResult["SMILES"])):
?>
			<div class="forum-clear-float"></div>
			<div class="forum-smiles-line" id="forum_smiles_showed" style="display:<?=($arUserSettings["smiles"] == "show" ? "none" : "block")?>;">
<?
	$ii = $arParams["SMILES_COUNT"];
	foreach ($arResult["SMILES"] as $res):
		$TYPING = strtok($res['TYPING'], " ");
		$ii--;
		if ($ii < 0){break;}
?>
				<span class="forum-smiles-item" style="height:<?=$iMaxH?>px;"><?
					?><a href="#postform" name="smiles" style="margin-top:<?=(round(($iMaxH - $res['IMAGE_HEIGHT'])/2))?>px;"><?
						?><img src="<?=$arParams["PATH_TO_SMILE"].$res['IMAGE']?>" class="smiles"<?
		if (intVal($res['IMAGE_WIDTH']) > 0):
							?> width="<?=$res['IMAGE_WIDTH']?>" <?
		endif;
		if (intVal($res['IMAGE_HEIGHT']) > 0):
							?> height="<?=$res['IMAGE_HEIGHT']?>" <?
		endif;
						?> alt="<?=$TYPING?>" title="<?=$res['NAME']?>" border="0" /><?
					?></a></span>
<?
	endforeach;
?>
				<div class="forum-smiles-item" style="height:<?=$iMaxH?>px;">
					<a href="#postform" id="form_smiles_static" name="smile_show" style="margin-top:<?=$iPaddingTop?>px;">
						<?=GetMessage("F_SHOW_SMILE")?></a>
				</div>
			</div><?
			?><div class="forum-smiles-line" id="forum_smiles_hidden" style="display:<?=($arUserSettings["smiles"] == "show" ? "block" : "none")?>;">
<?
	foreach ($arResult["SMILES"] as $res):
		$TYPING = strtok($res['TYPING'], " ");
?>
				<span class="forum-smiles-item" style="height:<?=$iMaxH?>px;"><?
					?><a href="#postform" name="smiles" style="margin-top:<?=(round(($iMaxH - $res['IMAGE_HEIGHT'])/2))?>px;"><?
						?><img src="<?=$arParams["PATH_TO_SMILE"].$res['IMAGE']?>" class="smiles"<?
		if (intVal($res['IMAGE_WIDTH']) > 0):
							?> width="<?=$res['IMAGE_WIDTH']?>" <?
		endif;
		if (intVal($res['IMAGE_HEIGHT']) > 0):
							?> height="<?=$res['IMAGE_HEIGHT']?>" <?
		endif;
						?> alt="<?=$TYPING?>" title="<?=$res['NAME']?>" border="0" /><?
					?></a></span>
<?
	endforeach;
	
?>
				<div class="forum-smiles-item" style="height:<?=$iMaxH?>px;">
					<a href="#postform" id="form_smiles_static" name="smile_hide" style="margin-top:<?=$iPaddingTop?>px;"><?=GetMessage("F_HIDE_SMILE")?></a>
				</div>
			</div>
<?
else:
?>
<script>
jsUtils.addEvent(window, "load", function(e){ForumShowSmile(e)});
function ForumShowSmile(e)
{
	var sIndex = '<?=$arParams["form_index"]?>';
	var form = document.forms["REPLIER<?=$arParams["form_index"]?>"];
	var forum_bbcode_line = document.getElementById('forum_bbcode_line<?=$arParams["form_index"]?>');
	var forum_smiles_line = document.getElementById('forum_smiles_line<?=$arParams["form_index"]?>');
	var params = {'width' : form.POST_MESSAGE.offsetWidth, 'width_real' : 0};
	var res = forum_smiles_line.getElementsByTagName('A');
	if (res && res.length > 0)
	{
		for (var ii = 0; ii < res.length; ii++)
		{
			params['width_real'] += parseInt(res[ii].parentNode.offsetWidth);
		}
	}
	if ((form.POST_MESSAGE.offsetWidth - forum_bbcode_line.offsetWidth) < params['width_real'])
	{
		var res = document.createElement('DIV');
		res.className = "forum-clear-float";
		forum_smiles_line.parentNode.insertBefore(res, forum_smiles_line);
		forum_smiles_line.style.width = form.POST_MESSAGE.offsetWidth + 'px';
	}
}
</script>
			<div class="forum-smiles-line" id="forum_smiles_line<?=$arParams["form_index"]?>">
<?
	foreach ($arResult["SMILES"] as $res):
		$TYPING = strtok($res['TYPING'], " ");
?>
				<span class="forum-smiles-item" style="height:<?=$iMaxH?>px;"><?
					?><a href="#postform" name="smiles" style="margin-top:<?=(round(($iMaxH - $res['IMAGE_HEIGHT'])/2))?>px;"><?
						?><img src="<?=$arParams["PATH_TO_SMILE"].$res['IMAGE']?>" class="smiles"<?
		if (intVal($res['IMAGE_WIDTH']) > 0):
							?> width="<?=$res['IMAGE_WIDTH']?>" <?
		endif;
		if (intVal($res['IMAGE_HEIGHT']) > 0):
							?> height="<?=$res['IMAGE_HEIGHT']?>" <?
		endif;
						?> alt="<?=$TYPING?>" title="<?=$res['NAME']?>" border="0" /><?
					?></a></span>
<?
	endforeach;
?>
			</div>
<?
endif;
?>
			<div class="forum-clear-float"></div>
<?
endif;
?>
		</div>
		<div class="forum-reply-field forum-reply-field-text">
			<textarea name="POST_MESSAGE" class="post_message" cols="55" rows="14" tabindex="<?=$tabIndex++;?>"><?=$arResult["MESSAGE"]["POST_MESSAGE"];?></textarea>
		</div>
<?
/* EDIT PANEL */
if ($arResult["SHOW_PANEL_EDIT"] == "Y"):
?>
		<div class="forum-reply-field forum-reply-field-lastedit">
<?
	$checked = true;
	if ($arResult["SHOW_PANEL_EDIT_ASK"] == "Y"):
		$checked = ($_REQUEST["EDIT_ADD_REASON"]=="Y" ? true : false);
		?><div class="forum-reply-field-lastedit-view"><?
			?><input type="checkbox" id="EDIT_ADD_REASON" name="EDIT_ADD_REASON<?=$arParams["form_index"]?>" <?=($checked ? "checked=\"checked\"" : "")?> value="Y" <?
				?>onclick="ShowLastEditReason(this.checked, this.parentNode.nextSibling)" />&nbsp;<?
			?><label for="EDIT_ADD_REASON<?=$arParams["form_index"]?>"><?=GetMessage("F_EDIT_ADD_REASON")?></label></div><?
	endif;
	
		?><div class="forum-reply-field-lastedit-reason" <?
		if (!$checked):
			?> style="display:none;" <?	
		endif;
		?>  id=""><?
		if ($arResult["SHOW_EDIT_PANEL_GUEST"] == "Y"):
			?><input name="EDITOR_NAME" type="hidden" value="<?=$arResult["EDITOR_NAME"];?>" /><?
			if ($arResult["FORUM"]["ASK_GUEST_EMAIL"] == "Y"):
			?><input type="hidden" name="EDITOR_EMAIL" value="<?=$arResult["EDITOR_EMAIL"];?>" /></br><?
			endif;
		endif;
		?>
			<label for="EDIT_REASON"><?=GetMessage("F_EDIT_REASON")?></label>
			<input type="text" name="EDIT_REASON" id="" size="70" value="<?=$arResult["EDIT_REASON"]?>" /></div>
		</div>
<?
endif;

/* CAPTHCA */
if (strLen($arResult["CAPTCHA_CODE"]) > 0):
?>
		<div class="forum-reply-field forum-reply-field-captcha">
			<input type="hidden" name="captcha_code" value="<?=$arResult["CAPTCHA_CODE"]?>"/>
			<div class="forum-reply-field-captcha-label">
				<label for="captcha_word"><?=GetMessage("F_CAPTCHA_PROMT")?><span class="forum-required-field">*</span></label>
				<input type="text" size="30" name="captcha_word" tabindex="<?=$tabIndex++;?>" autocomplete="off" />
			</div>
			<div class="forum-reply-field-captcha-image">
				<img src="/bitrix/tools/captcha.php?captcha_code=<?=$arResult["CAPTCHA_CODE"]?>" alt="<?=GetMessage("F_CAPTCHA_TITLE")?>" />
			</div>
		</div>
<?
endif;
/* ATTACH FILES */
if ($arResult["SHOW_PANEL_ATTACH_IMG"] == "Y"):
?>
		<div class="forum-reply-field forum-reply-field-upload">
<?
$iCount = 0;
if (!empty($arResult["MESSAGE"]["FILES"])):
	foreach ($arResult["MESSAGE"]["FILES"] as $key => $val):
	$iCount++;
	$iFileSize = intVal($val["FILE_SIZE"]);
	$size = array(
		"B" => $iFileSize, 
		"KB" => round($iFileSize/1024, 2), 
		"MB" => round($iFileSize/1048576, 2));
	$sFileSize = $size["KB"].GetMessage("F_KB");
	if ($size["KB"] < 1)
		$sFileSize = $size["B"].GetMessage("F_B");
	elseif ($size["MB"] >= 1 )
		$sFileSize = $size["MB"].GetMessage("F_MB");
?>
			<div class="forum-uploaded-file">
				<input type="hidden" name="FILES[<?=$key?>]" value="<?=$key?>" />
				<input type="checkbox" name="FILES_TO_UPLOAD[<?=$key?>]" id="FILES_TO_UPLOAD_<?=$key?>" value="<?=$key?>" checked="checked" />
				<label for="FILES_TO_UPLOAD_<?=$key?>"><?=$val["ORIGINAL_NAME"]?> (<?=$val["CONTENT_TYPE"]?>) <?=$sFileSize?>
					( <a href="/bitrix/components/bitrix/forum.interface/show_file.php?action=download&amp;fid=<?=$key?>"><?=GetMessage("F_DOWNLOAD")?></a> )
				</label>
			</div>
<?
	endforeach;
endif;

if ($iCount < $arParams["FILES_COUNT"]):
$iFileSize = intVal(COption::GetOptionString("forum", "file_max_size", 50000));
$size = array(
	"B" => $iFileSize, 
	"KB" => round($iFileSize/1024, 2), 
	"MB" => round($iFileSize/1048576, 2));
$sFileSize = $size["KB"].GetMessage("F_KB");
if ($size["KB"] < 1)
	$sFileSize = $size["B"].GetMessage("F_B");
elseif ($size["MB"] >= 1 )
	$sFileSize = $size["MB"].GetMessage("F_MB");
?>
			<div class="forum-upload-info" style="display:none;" id="upload_files_info_<?=$arParams["form_index"]?>">
<?
if ($arParams["FORUM"]["ALLOW_UPLOAD"] == "F"):
?>
				<span><?=str_replace("#EXTENSION#", $arParams["FORUM"]["ALLOW_UPLOAD_EXT"], GetMessage("F_FILE_EXTENSION"))?></span>
<?
endif;
?>
				<span><?=str_replace("#SIZE#", $sFileSize, GetMessage("F_FILE_SIZE"))?></span>
			</div>
<?
			
	for ($ii = $iCount; $ii < $arParams["FILES_COUNT"]; $ii++):
?>

			<div class="forum-upload-file" style="display:none;" id="upload_files_<?=$ii?>_<?=$arParams["form_index"]?>">
				<input name="FILE_NEW_<?=$ii?>" type="file" value="" size="30" />
			</div>
<?
	endfor;
?>
			<a href="javascript:void(0);" onclick="AttachFile('<?=$iCount?>', '<?=($ii - $iCount)?>', '<?=$arParams["form_index"]?>', this); return false;">
				<span><?=($arResult["FORUM"]["ALLOW_UPLOAD"]=="Y") ? GetMessage("F_LOAD_IMAGE") : GetMessage("F_LOAD_FILE") ?></span>
			</a>
<?
endif;
?>
		</div>
<?
endif;

?>
		<div class="forum-reply-field forum-reply-field-settings">
<?
/* SMILES */
if ($arResult["FORUM"]["ALLOW_SMILES"] == "Y"):
?>
			<div class="forum-reply-field-setting">
				<input type="checkbox" name="USE_SMILES" id="USE_SMILES<?=$arParams["form_index"]?>" <?
				?>value="Y" <?=($arResult["MESSAGE"]["USE_SMILES"]=="Y") ? "checked=\"checked\"" : "";?> <?
				?>tabindex="<?=$tabIndex++;?>" /><?
			?>&nbsp;<label for="USE_SMILES<?=$arParams["form_index"]?>"><?=GetMessage("F_WANT_ALLOW_SMILES")?></label></div>
<?
endif;
/* SUBSCRIBE */
if ($arResult["SHOW_SUBSCRIBE"] == "Y"):
?>
			<div class="forum-reply-field-setting">
				<input type="checkbox" name="TOPIC_SUBSCRIBE" id="TOPIC_SUBSCRIBE<?=$arParams["form_index"]?>" value="Y" <?
					?><?=($arResult["TOPIC_SUBSCRIBE"] == "Y")? "checked disabled " : "";?> tabindex="<?=$tabIndex++;?>" /><?
				?>&nbsp;<label for="TOPIC_SUBSCRIBE<?=$arParams["form_index"]?>"><?=GetMessage("F_WANT_SUBSCRIBE_TOPIC")?></label></div>
			<div class="forum-reply-field-setting">
				<input type="checkbox" name="FORUM_SUBSCRIBE" id="FORUM_SUBSCRIBE<?=$arParams["form_index"]?>" value="Y" <?
				?><?=($arResult["FORUM_SUBSCRIBE"] == "Y")? "checked disabled " : "";?> tabindex="<?=$tabIndex++;?>"/><?
				?>&nbsp;<label for="FORUM_SUBSCRIBE<?=$arParams["form_index"]?>"><?=GetMessage("F_WANT_SUBSCRIBE_FORUM")?></label></div>
<?
endif;
?>
		</div>
<?

?>
		<div class="forum-reply-buttons">
			<input name="send_button" type="submit" value="<?=$arResult["SUBMIT"]?>" tabindex="<?=$tabIndex++;?>" <?
				?>onclick="this.form.MESSAGE_MODE.value = 'NORMAL';" />
			<input name="view_button" type="submit" value="<?=GetMessage("F_VIEW")?>" tabindex="<?=$tabIndex++;?>" <?
				?>onclick="this.form.MESSAGE_MODE.value = 'VIEW';" />
		</div>

	</div>
</div>
</form>
<script type="text/javascript">
function AttachFile(iNumber, iCount, sIndex, oObj)
{
	var element = null;
	var bFined = false;
	iNumber = parseInt(iNumber);
	iCount = parseInt(iCount);
	
	document.getElementById('upload_files_info_' + sIndex).style.display = 'block';
	for (var ii = iNumber; ii < (iNumber + iCount); ii++)
	{
		element = document.getElementById('upload_files_' + ii + '_' + sIndex);
		if (!element || typeof(element) == null)
			break;
		if (element.style.display == 'none')
		{
			bFined = true;
			element.style.display = 'block';
			break;
		}
	}
	var bHide = (!bFined ? true : (ii >= (iNumber + iCount - 1)));
	if (bHide == true)
		oObj.style.display = 'none';
}
function AddTags(a)
{
	if (a != null)
	{
		var div = a.parentNode.previousSibling;
		div.style.display = "block";
		a.parentNode.style.display = "none";

		var inputs = div.getElementsByTagName("INPUT");
		for (var i = 0 ; i < inputs.length ; i++ )
		{
			if (inputs[i].type.toUpperCase() == "TEXT")
			{
				CorrectTags(inputs[i]);
				inputs[i].focus();
				break;
			}
		}
		if (a.parentNode.lastChild && a.parentNode.lastChild.name == 'from_tag'){
			a.parentNode.lastChild.style.display = 'none';
			if (document.getElementById('vote_switcher')) {
				document.getElementById('vote_switcher').style.display = '';}
		}
	}
	return false;
}
function CorrectTags(oObj)
{
	if (document.getElementById('TAGS_div_frame'))
		document.getElementById('TAGS_div_frame').id = oObj.id + "_div_frame";
}

var bSendForm = false;
if (typeof oErrors != "object")
	var oErrors = {};
oErrors['no_topic_name'] = "<?=CUtil::addslashes(GetMessage("JERROR_NO_TOPIC_NAME"))?>";
oErrors['no_message'] = "<?=CUtil::addslashes(GetMessage("JERROR_NO_MESSAGE"))?>";
oErrors['max_len'] = "<?=CUtil::addslashes(GetMessage("JERROR_MAX_LEN"))?>";
oErrors['no_url'] = "<?=CUtil::addslashes(GetMessage("FORUM_ERROR_NO_URL"))?>";
oErrors['no_title'] = "<?=CUtil::addslashes(GetMessage("FORUM_ERROR_NO_TITLE"))?>";
oErrors['no_path'] = "<?=CUtil::addslashes(GetMessage("FORUM_ERROR_NO_PATH_TO_VIDEO"))?>";
if (typeof oText != "object")
	var oText = {};
oText['author'] = " <?=CUtil::addslashes(GetMessage("JQOUTE_AUTHOR_WRITES"))?>:\n";
oText['enter_url'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_URL"))?>";
oText['enter_url_name'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_URL_NAME"))?>";
oText['enter_image'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_IMAGE"))?>";
oText['list_prompt'] = "<?=CUtil::addslashes(GetMessage("FORUM_LIST_PROMPT"))?>";
oText['video'] = "<?=CUtil::addslashes(GetMessage("FORUM_VIDEO"))?>";
oText['path'] = "<?=CUtil::addslashes(GetMessage("FORUM_PATH"))?>:";
oText['preview'] = "<?=CUtil::addslashes(GetMessage("FORUM_PREVIEW"))?>:";
oText['width'] = "<?=CUtil::addslashes(GetMessage("FORUM_WIDTH"))?>:";
oText['height'] = "<?=CUtil::addslashes(GetMessage("FORUM_HEIGHT"))?>:";
oText['vote_drop_answer_confirm'] = "<?=CUtil::addslashes(GetMessage("F_VOTE_DROP_ANSWER_CONFIRM"))?>";
oText['vote_drop_question_confirm'] = "<?=CUtil::addslashes(GetMessage("F_VOTE_DROP_QUESTION_CONFIRM"))?>";

oText['BUTTON_OK'] = "<?=CUtil::addslashes(GetMessage("FORUM_BUTTON_OK"))?>";
oText['BUTTON_CANCEL'] = "<?=CUtil::addslashes(GetMessage("FORUM_BUTTON_CANCEL"))?>";
oText['smile_hide'] = "<?=CUtil::addslashes(GetMessage("F_HIDE_SMILE"))?>";

if (typeof oHelp != "object")
	var oHelp = {};

function reply2author(name)
{
    name = name.replace(/&lt;/gi, "<").replace(/&gt;/gi, ">").replace(/&quot;/gi, "\"");
	<?if ($arResult["FORUM"]["ALLOW_QUOTE"] == "Y"):?>
	document.REPLIER.POST_MESSAGE.value += "[b]"+name+"[/b]"+" \n";
	<?else:?>
	document.REPLIER.POST_MESSAGE.value += name+" \n";
	<?endif;?>
	return false;
}
</script>
