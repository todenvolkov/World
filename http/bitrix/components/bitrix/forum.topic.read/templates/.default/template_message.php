<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/lang/".LANGUAGE_ID."/template_message.php")));
if (!file_exists($file))
	$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/lang/en/template_message.php")));
global $MESS;
include_once($file);

if (function_exists("__forum_default_template_show_message"))
	return false;

function __forum_default_template_show_message($arResult, $message, $arRes, $arAddParams, $arParams, $component = false)
{
	$iCount = 0;
	$message = (is_array($message) ? $message : array());
	$arResult = (is_array($arResult) ? $arResult : array($arResult));
	$arRes = (is_array($arRes) ? $arRes : array($arRes));
	static $bShowedMessage = false;
		
	if ($arParams["SHOW_RATING"] == 'Y'):
		$arAuthorId = array();
		$arPostId = array();
		$arTopicId = array();
		foreach ($arResult as $res)
		{
			$arAuthorId[] = $res['AUTHOR_ID'];
			if ($res['NEW_TOPIC'] == "Y")
				$arTopicId[] = $res['TOPIC_ID'];
			else
				$arPostId[] = $res['ID'];
		}
		if (!empty($arAuthorId)):
			$arRatingResult = CRatings::GetRatingResult($arParams["RATING_ID"], array_unique($arAuthorId));	
		endif;
			
	    if (!empty($arPostId))
			$arRatingVote['FORUM_POST'] = CRatings::GetRatingVoteResult('FORUM_POST', $arPostId);
						
	    if (!empty($arTopicId))
			$arRatingVote['FORUM_TOPIC'] = CRatings::GetRatingVoteResult('FORUM_TOPIC', $arTopicId);				
	endif;
	
foreach ($arResult as $res):
	$iCount++;
	
	$bNameShowed = false;
	if ($arParams["SHOW_VOTE"] == "Y" && $res["PARAM1"] == "VT" && intVal($res["PARAM2"]) > 0 && IsModuleInstalled("vote")):
?>
<div class="forum-info-box forum-post-vote">
	<div class="forum-info-box-inner">
	<a name="message<?=$res["ID"]?>"></a>
	<?
	$bNameShowed = true;
	?><?$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:voting.current", $arParams["VOTE_TEMPLATE"], 
		array(
			"VOTE_ID" => $res["PARAM2"], 
			"VOTE_CHANNEL_ID" => $arParams["VOTE_CHANNEL_ID"], 
			"VOTE_RESULT_TEMPLATE" => $arRes["~CURRENT_PAGE"], 
			"CACHE_TIME" => 0, /*$arParams["CACHE_TIME"]*/
			"NEED_SORT" => "N"),
		(($component && $component->__component && $component->__component->__parent) ? $component->__component->__parent : null),
		array("HIDE_ICONS" => "Y"));?>
	</div>
</div>
<?
	endif;
	
if (!$bShowedMessage && $arRes["USER"]["RIGHTS"]["MODERATE"] == "Y" && $arAddParams["single_message"] !== "Y"):
?>
<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" <?
	?>onsubmit="return Validate(this)" name="MESSAGES_<?=$arParams["iIndex"]?>" id="MESSAGES_<?=$arParams["iIndex"]?>">
<?
$bShowedMessage = true;
endif;
?>
			<table cellspacing="0" border="0" class="forum-post-table <?=($iCount == 1 ? "forum-post-first " : "")?><?
				?><?=($iCount == count($arResult) ? "forum-post-last " : "")?><?
				?><?=($iCount%2 == 1 ? "forum-post-odd " : "forum-post-even ")?><?
				?><?=($res["APPROVED"] == "Y" ? "" : " forum-post-hidden ")?><?
				?><?=(in_array($res["ID"], $message) ? " forum-post-selected " : "")?>" <?
				?><?=(!$bNameShowed ? "id=\"message".$res["ID"]."\"" : "")?>>
				<tbody>
					<tr>
						<td class="forum-cell-user">
							<div class="forum-user-info">
<?
		if ($res["AUTHOR_ID"] > 0):
?>
								<div class="forum-user-name"><?
			if ($arParams["SEO_USER"] == "Y"):
								?><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR"]?>"><span><?=$res["AUTHOR_NAME"]?></span></a></noindex><?
			else:
								?><a href="<?=$res["URL"]["AUTHOR"]?>"><span><?=$res["AUTHOR_NAME"]?></span></a><?
			endif;
								?></div>
<?
			if (is_array($res["AVATAR"]) && (strLen($res["AVATAR"]["HTML"]) > 0)):
?>
								<div class="forum-user-avatar"><?
									?><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR"]?>" title="<?=GetMessage("F_AUTHOR_PROFILE")?>"><?
										?><?=$res["AVATAR"]["HTML"]?></a></noindex></div>
<?
			else:
?>
								<div class="forum-user-register-avatar"><?
									?><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR"]?>" title="<?=GetMessage("F_AUTHOR_PROFILE")?>"><?
										?><span><!-- ie --></span></a></noindex></div>
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
									<span><?=GetMessage("F_NUM_MESS")?> <span><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR_POSTS"]?>"><?
										?><?=$res["NUM_POSTS"]?></a></noindex></span></span>
<?
		endif;
		
		if (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y" && $res["AUTHOR_ID"] > 0 && 
			($res["NUM_POINTS"] > 0 || $res["VOTES"]["ACTION"] == "VOTE" || $res["VOTES"]["ACTION"] == "UNVOTE")):
?>
									<span><?=GetMessage("F_POINTS")?> <span><?=$res["NUM_POINTS"]?></span><?
			if ($res["VOTES"]["ACTION"] == "VOTE" || $res["VOTES"]["ACTION"] == "UNVOTE"):
									?>&nbsp;(<span class="forum-vote-user"><?
										?><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR_VOTE"]?>" title="<?
											?><?=($res["VOTES"]["ACTION"] == "VOTE" ? GetMessage("F_NO_VOTE_DO") : GetMessage("F_NO_VOTE_UNDO"));?>"><?
											?><?=($res["VOTES"]["ACTION"] == "VOTE" ? "+" : "-");?></a></noindex></span>)<?
			endif;
									?></span>
<?
		endif;
		if ($arParams["SHOW_RATING"] == 'Y' && $res["AUTHOR_ID"] > 0):
?>
				<span>
				<?
				$GLOBALS["APPLICATION"]->IncludeComponent(
					"bitrix:rating.result", "",
					Array(
						"RATING_ID" => $arParams["RATING_ID"],
						"ENTITY_ID" => $arRatingResult[$res['AUTHOR_ID']]['ENTITY_ID'],
						"CURRENT_VALUE" => $arRatingResult[$res['AUTHOR_ID']]['CURRENT_VALUE'],
						"PREVIOUS_VALUE" => $arRatingResult[$res['AUTHOR_ID']]['PREVIOUS_VALUE'],
					),
					null,
					array("HIDE_ICONS" => "Y")
				);
				?>
				</span>						
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
							    <?if ($arParams["SHOW_RATING"] == 'Y'):?>
								<div class="forum-post-rating" style="float: right;padding-left: 10px; padding-top: 2px;">
								<?
								$voteEntityType = $res['NEW_TOPIC'] == "Y" ? "FORUM_TOPIC" : "FORUM_POST";
								$voteEntityId = $res['NEW_TOPIC'] == "Y" ? $res['TOPIC_ID'] : $res['ID'];
								$GLOBALS["APPLICATION"]->IncludeComponent(
									"bitrix:rating.vote", "",
									Array(
										"ENTITY_TYPE_ID" => $voteEntityType,
										"ENTITY_ID" => $voteEntityId,
										"OWNER_ID" => $res['AUTHOR_ID'],
										"USER_HAS_VOTED" => $arRatingVote[$voteEntityType][$voteEntityId]['USER_HAS_VOTED'],
										"TOTAL_VOTES" => $arRatingVote[$voteEntityType][$voteEntityId]['TOTAL_VOTES'],
										"TOTAL_POSITIVE_VOTES" => $arRatingVote[$voteEntityType][$voteEntityId]['TOTAL_POSITIVE_VOTES'],
										"TOTAL_NEGATIVE_VOTES" => $arRatingVote[$voteEntityType][$voteEntityId]['TOTAL_NEGATIVE_VOTES'],
										"TOTAL_VALUE" => $arRatingVote[$voteEntityType][$voteEntityId]['TOTAL_VALUE']
									),
									null,
									array("HIDE_ICONS" => "Y")
								);?>
								</div>
								<?endif;?>
								<div class="forum-post-number"><noindex><a rel="nofollow" href="http://<?=$_SERVER["HTTP_HOST"]?><?=$res["URL"]["MESSAGE"]?>#message<?=$res["ID"]?>" <?
									?>onclick="prompt(oText['ml'], this.href); return false;" title="<?=GetMessage("F_ANCHOR")?>">#<?=$res["NUMBER"]?></a></noindex><?
							if ($arRes["USER"]["PERMISSION"] >= "Q" && $arAddParams["single_message"] !== "Y"):
								?>&nbsp;<input type="checkbox" name="message_id[]" value="<?=$res["ID"]?>" id="message_id_<?=$res["ID"]?>_" <?
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
											"WIDTH" => $arRes["PARSER"]->image_params["width"],
											"HEIGHT" => $arRes["PARSER"]->image_params["height"],
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
		if ($arRes["USER"]["PERMISSION"] >= "Q"):
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
?>
						</td>
					</tr>
					<tr>
						<td class="forum-cell-contact">
							<div class="forum-contact-links">
<?
					if ($res["AUTHOR_ID"] > 0 && $GLOBALS["USER"]->IsAuthorized()):
?>
                            <span class="forum-contact-message"><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR_PM"]?>" title="<?=GetMessage("F_PRIVATE_MESSAGE_TITLE")?>"><?
                                ?><?=GetMessage("F_PRIVATE_MESSAGE")?></a></noindex></span>&nbsp;&nbsp;
<?
					endif;
					if ($arParams["SHOW_MAIL"] == "Y" && strlen($res["EMAIL"]) > 0):
?>
							<span class="forum-contact-email"><noindex><a rel="nofollow" href="<?=$res["URL"]["AUTHOR_EMAIL"]?>" <?
								?>title="<?=GetMessage("F_EMAIL_TITLE")?>">E-mail</a></noindex></span>&nbsp;&nbsp;
<?
					endif;
                    if ($arParams["SHOW_ICQ"] == "Y" && strLen($res["PERSONAL_ICQ"]) > 0):
                        $bEmptyCell = false;
?>
                        <span class="forum-contact-icq">
                            <noindex><a rel="nofollow" href="javascript:void(0);" onclick="prompt('ICQ', '<?=CUtil::JSEscape($res["PERSONAL_ICQ"])?>')">ICQ</a></noindex></span>
<?
					elseif (!($res["AUTHOR_ID"] > 0 && $GLOBALS["USER"]->IsAuthorized())):
?>
							&nbsp;
<?
                    endif;
?>
							</div>
						</td>
						<td class="forum-cell-actions">
							<div class="forum-action-links">
<?
				if ($res["NUMBER"] == 1):
					if ($res["PANELS"]["MODERATE"] == "Y"):
						if ($arRes["TOPIC"]["APPROVED"] != "Y"):
?>
								<span class="forum-action-show"><noindex><a rel="nofollow" href="<?
								 	?><?=$GLOBALS["APPLICATION"]->GetCurPageParam("ACTION=SHOW_TOPIC&".bitrix_sessid_get(), array("ACTION", "sessid"))?>"><?
									?><?=GetMessage("F_SHOW_TOPIC")?></a></noindex></span>
<?

						elseif (false):
?>
								<span class="forum-action-hide"><noindex><a rel="nofollow" href="<?
								 	?><?=$GLOBALS["APPLICATION"]->GetCurPageParam("ACTION=HIDE_TOPIC&".bitrix_sessid_get(), array("ACTION", "sessid"))?>"><?
									?><?=GetMessage("F_HIDE_TOPIC")?></a></noindex></span>
<?
						endif;
					endif;
					if ($res["PANELS"]["DELETE"] == "Y"):
?>
								 &nbsp;&nbsp;<span class="forum-action-delete"><noindex><a rel="nofollow" href="<?
								 	?><?=$GLOBALS["APPLICATION"]->GetCurPageParam("ACTION=DEL_TOPIC&".bitrix_sessid_get(), array("ACTION", "sessid"))?>" <?
								 	?> onclick="return confirm(oText['cdt']);"><?=GetMessage("F_DELETE_TOPIC")?></a></noindex></span>
<?
					endif;
					if ($res["PANELS"]["EDIT"] == "Y" && $arRes["USER"]["PERMISSION"] >= "U"):
?>
								 &nbsp;&nbsp;<span class="forum-action-edit"><?
								 	?><noindex><a rel="nofollow" href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT_TOPIC")?></a></noindex></span>
<?
					elseif ($res["PANELS"]["EDIT"] == "Y"):
?>
								 &nbsp;&nbsp;<span class="forum-action-edit"><?
								 	?><noindex><a rel="nofollow" href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT")?></a></noindex></span>
<?
					endif;
				else:
					if ($res["PANELS"]["MODERATE"] == "Y"):
						if ($res["APPROVED"] == "Y"):
?>
								<span class="forum-action-hide"><?
									?><noindex><a rel="nofollow" href="<?=$res["URL"]["MODERATE"]?>"><?=GetMessage("F_HIDE")?></a></noindex></span>&nbsp;&nbsp;
<?
						else:
?>
								<span class="forum-action-show"><?
									?><noindex><a rel="nofollow" href="<?=$res["URL"]["MODERATE"]?>"><?=GetMessage("F_SHOW")?></a></noindex></span>&nbsp;&nbsp;
<?
						endif;
					endif;
					if ($res["PANELS"]["DELETE"] == "Y"):
?>
								 <span class="forum-action-delete"><?
								 	?><noindex><a rel="nofollow" href="<?=$res["URL"]["DELETE"]?>" <?
								 	?>onclick="return confirm(oText['cdm']);"><?=GetMessage("F_DELETE")?></a></noindex></span>&nbsp;&nbsp;
<?
					endif;
					if ($res["PANELS"]["EDIT"] == "Y"):
?>
								 <span class="forum-action-edit"><noindex><a rel="nofollow" href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT")?></a></noindex></span>&nbsp;&nbsp;
<?
					endif;
			endif;
			
			if ($arRes["USER"]["RIGHTS"]["ADD_MESSAGE"] == "Y"):
				if ($res["NUMBER"] == 1):
					?>&nbsp;&nbsp;<?
				endif;
				if ($arRes["FORUM"]["ALLOW_QUOTE"] == "Y"):
?>
								<span class="forum-action-quote"><a href="#postform" <?
									?> onmousedown="if (window['quoteMessageEx']){quoteMessageEx('<?=$res["FOR_JS"]["AUTHOR_NAME"]?>', 'message_text_<?=$res["ID"]?>')}"><?
									?><?=GetMessage("F_QUOTE")?></a></span>
<?
					if ($arParams["SHOW_NAME_LINK"] == "Y"):
?>
								&nbsp;&nbsp;<span class="forum-action-reply"><a href="#postform" title="<?=GetMessage("F_INSERT_NAME")?>" <?
									?> onmousedown="reply2author('<?=$res["FOR_JS"]["AUTHOR_NAME"]?>,', 'message_text_<?=$res["ID"]?>')"><?
									?><?=GetMessage("F_NAME")?></a></span>
<?
					endif;
				elseif ($arParams["SHOW_NAME_LINK"] != "Y"):
?>
								<span class="forum-action-reply"><a href="#postform" <?
									?> onmousedown="reply2author('<?=$res["FOR_JS"]["AUTHOR_NAME"]?>,', 'message_text_<?=$res["ID"]?>')"><?
									?><?=GetMessage("F_REPLY")?></a></span>
<?
				endif;
			else:
?>
							&nbsp;
<?
			endif;
?>
							</div>
						</td>
					</tr>
				</tbody>
<?
	if ($iCount < count($arResult) || $arAddParams["single_message"] == "Y"):
?>
			</table>
<?
	endif;
endforeach;
}
?>
