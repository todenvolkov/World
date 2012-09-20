<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// *****************************************************************************************
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum/templates/.default/script.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/.default/script.js"></script>', true);
if ($arResult["sSection"] == "MESSAGE_APPR")
	$arParams["AJAX_TYPE"] = "N";
if ($arParams["AJAX_TYPE"] != "N")
	IncludeAJAX();
if ($arParams["TMPLT_SHOW_AUTH_FORM"] != "INPUT")
	$arParams["TMPLT_SHOW_AUTH_FORM"] = "LINK";
/* POPUPS */
if ($arResult["MAIN_PANEL"]["FORUMS_LIST"] == "Y")
{
	
	foreach ($arResult["GROUPS_FORUMS"] as $res):
		if ($res["TYPE"] == "GROUP"):
			$arResult["popup"]["forums"][] = 
				array(
					"CONTENT" => $res["~NAME"],
					"CLASS" => "forum-group level".$res["DEPTH"]);
		elseif ($res["TYPE"] == "FORUM"):
			$arResult["popup"]["forums"][] = 
				array(
					"CONTENT" => $res["~NAME"],
					"CLASS" => "forums level".$res["DEPTH"], 
					"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($res["LINK"])."');");
		endif;
	endforeach;
}

if ($arResult["MANAGE_PANEL"]["SUBSCRIBE"] == "Y")
{
	$arResult["popup"]["subscribe"][] = array(
		"TITLE" => GetMessage("FMI_SUBSCRIBE_ON_NEW"),
		"CONTENT" => GetMessage("FMI_SUBSCRIBE_ON_NEW_MESS"),
		"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($arResult["~forum_subscribe"])."');");
						
	$arResult["popup"]["subscribe"][] = array(
		"TITLE" => GetMessage("FMI_SUBSCRIBE_ON_NEW_TOPICS"),
		"CONTENT" => GetMessage("FMI_NEW_TOPICS"),
		"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($arResult["~forum_subscribe_topics"])."');");

	if ($arResult["sSection"]=="READ" && $arResult["TOPIC"]["STATE"]=="Y"):
		$arResult["popup"]["subscribe"][] = array(
			"TITLE" => GetMessage("FMI_NEW_MESS_THIS_TOPIC"),
			"CONTENT" => GetMessage("FMI_THIS_TOPIC1"),
			"ONCLICK" => "jsUtils.Redirect([], '".CUtil::JSEscape($arResult["~forum_subscribe_this_topic"])."');");
	endif;
}
	

if ($arResult["MANAGE_PANEL"]["TOPICS"] == "Y")
{
	if ($arResult["sSection"] == "LIST")
	{
		$arResult["popup"]["topics"][] = array(
			"CONTENT" => array(
				'<input type="radio" name="ACTION" id="SET_ORDINARY_'.$arResult['IndexForForm'].'" value="SET_ORDINARY" checked="checked" />',
				'<label for="SET_ORDINARY_'.$arResult['IndexForForm'].'">'.GetMessage("FMI_TOPIC_UNPIN1").'</label>'),
			"ONCLICK" => "document.getElementById('forum_form_topic_topic".$arResult["IndexForForm"]."').ACTION.value = 'SET_ORDINARY';".
				"document.getElementById('SET_ORDINARY_".$arResult["IndexForForm"]."').checked = true");
						
		$arResult["popup"]["topics"][] = array(
			"CONTENT" => array(
				'<input type="radio" name="ACTION" id="SET_TOP_'.$arResult['IndexForForm'].'" value="SET_TOP" />',
				'<label for="SET_TOP_'.$arResult['IndexForForm'].'">'.GetMessage("FMI_TOPIC_PIN1").'</label>'),
			"ONCLICK" => "document.getElementById('forum_form_topic_topic".$arResult["IndexForForm"]."').ACTION.value = 'SET_TOP';".
				"document.getElementById('SET_TOP_".$arResult["IndexForForm"]."').checked = true");
						
		$arResult["popup"]["topics"][] = array(
			"CONTENT" => array(
				'<input type="radio" name="ACTION" id="STATE_Y_'.$arResult['IndexForForm'].'" value="STATE_Y" />',
				'<label for="STATE_Y_'.$arResult['IndexForForm'].'">'.GetMessage("FMI_TOPIC_OPEN1").'</label>'),
			"ONCLICK" => "document.getElementById('forum_form_topic_topic".$arResult["IndexForForm"]."').ACTION.value = 'STATE_Y';".
				"document.getElementById('STATE_Y_".$arResult["IndexForForm"]."').checked = true");
						
		$arResult["popup"]["topics"][] = array(
			"CONTENT" => array(
				'<input type="radio" name="ACTION" id="STATE_N_'.$arResult['IndexForForm'].'" value="STATE_N" />',
				'<label for="STATE_N_'.$arResult['IndexForForm'].'">'.GetMessage("FMI_TOPIC_CLOSE1").'</label>'),
			"ONCLICK" => "document.getElementById('forum_form_topic_topic".$arResult["IndexForForm"]."').ACTION.value = 'STATE_N';".
				"document.getElementById('STATE_N_".$arResult["IndexForForm"]."').checked = true");
	}
	else
	{
		$arResult["popup"]["topics"][] = array(
			"CONTENT" => array(
				'<input type="radio" name="ACTION" id="SET_ORDINARY_'.$arResult['IndexForForm'].'" value="'.$arResult["PIN"]["value"].'" checked="checked" />',
				'<label for="SET_ORDINARY_'.$arResult['IndexForForm'].'">'.(($arResult["PIN"]["value"] == "SET_ORDINARY") ? GetMessage("FMI_TOPIC_UNPIN1") : GetMessage("FMI_TOPIC_PIN1")).'</label>'),
			"ONCLICK" => "document.getElementById('forum_form_topic_topic".$arResult["IndexForForm"]."').ACTION.value = '".$arResult["PIN"]["value"]."';".
				"document.getElementById('SET_ORDINARY_".$arResult["IndexForForm"]."').checked = true");
						
		$arResult["popup"]["topics"][] = array(
			"CONTENT" => array(
				'<input type="radio" name="ACTION" id="STATE_Y_'.$arResult['IndexForForm'].'" value="'.$arResult["OPEN"]["value"].'" />',
				'<label for="STATE_Y_'.$arResult['IndexForForm'].'">'.(($arResult["OPEN"]["value"] == "STATE_Y") ? GetMessage("FMI_TOPIC_OPEN1") : GetMessage("FMI_TOPIC_CLOSE1")).'</label>'),
			"ONCLICK" => "document.getElementById('forum_form_topic_topic".$arResult["IndexForForm"]."').ACTION.value = '".$arResult["OPEN"]["value"]."';".
				"document.getElementById('STATE_Y_".$arResult["IndexForForm"]."').checked = true");
	}
	
	$arResult["popup"]["topics"][] = array(
		"CONTENT" => array(
			'<input type="radio" name="ACTION" id="MOVE_TOPIC_'.$arResult['IndexForForm'].'" value="MOVE_TOPIC" />',
			'<label for="MOVE_TOPIC_'.$arResult['IndexForForm'].'">'.GetMessage("FMI_TOPIC_MOVE1").'</label>'),
		"ONCLICK" => "document.getElementById('forum_form_topic_topic".$arResult["IndexForForm"]."').ACTION.value = 'MOVE_TOPIC';".
			"document.getElementById('MOVE_TOPIC_".$arResult["IndexForForm"]."').checked = true");
					
	if ($arResult["CanUserDeleteTopic"] == "Y"):
	$arResult["popup"]["topics"][] = array(
		"CONTENT" => array(
			'<input type="radio" name="ACTION" id="DEL_TOPIC_'.$arResult['IndexForForm'].'" value="DEL_TOPIC" />',
			'<label for="DEL_TOPIC_'.$arResult['IndexForForm'].'">'.GetMessage("FMI_TOPIC_DELETE1").'</label>'),
		"ONCLICK" => "document.getElementById('forum_form_topic_topic".$arResult["IndexForForm"]."').ACTION.value = 'DEL_TOPIC';".
			"document.getElementById('DEL_TOPIC_".$arResult["IndexForForm"]."').checked = true");
	endif;
	$arResult["popup"]["topics"][] = array(
		"CONTENT" => '<input type="button" name="SEND_FORM" id="topic'.$arResult["IndexForForm"].'" '.
			'onclick="if(typeof(forumForm) == \'object\'){forumForm.Send(\'topic\', this.id); fMenu.PopupHide();}" value="'.GetMessage("FMI_EXEC").'" />',
		"CLASS" => "forum-submit");

?>	<form id="forum_form_topic_topic<?=$arResult["IndexForForm"]?>" action="<?=$APPLICATION->GetCurPageParam()?>" method="get" class="forum-form">
		<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
		<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
		<input type="hidden" name="MID" value="<?=$arParams["MID"]?>" />
		<input type="hidden" name="AJAX_TYPE" value="N" />
		<input type="hidden" name="ACTION" value="<?=($arResult["sSection"] == "LIST" ? "SET_ORDINARY" : $arResult["PIN"]["value"])?>" />
		<input type="hidden" name="sessid" value="<?=bitrix_sessid()?>" />
		<input type="hidden" name="PAGE_NAME" value="<?=strToLower($arResult["sSection"])?>" />
	</form><?

}

if ($arResult["MANAGE_PANEL"]["MESSAGES"] == "Y")
{
	$arResult["popup"]["messages"][] = array(
		"CONTENT" => array(
			'<input type="radio" name="ACTION" id="SHOW_'.$arResult['IndexForForm'].'" value="SHOW" checked="checked" onclick="return true;" />',
			'<label for="SHOW_'.$arResult['IndexForForm'].'">'.GetMessage("FMI_MESSAGE_SHOW1").'</label>'),
		"ONCLICK" => "document.getElementById('forum_form_message_".$arResult["IndexForForm"]."').ACTION.value = 'SHOW';".
			"document.getElementById('SHOW_".$arResult["IndexForForm"]."').checked = true");
	if ($arResult["sSection"] == "READ"):
		$arResult["popup"]["messages"][] = array(
		"CONTENT" => array(
			'<input type="radio" name="ACTION" id="HIDE_'.$arResult['IndexForForm'].'" value="HIDE"  onclick="return true;" />',
			'<label for="HIDE_'.$arResult['IndexForForm'].'">'.GetMessage("FMI_MESSAGE_HIDE1").'</label>'),
		"ONCLICK" => "document.getElementById('forum_form_message_".$arResult["IndexForForm"]."').ACTION.value = 'HIDE';".
			"document.getElementById('HIDE_".$arResult["IndexForForm"]."').checked = true");
			
		$arResult["popup"]["messages"][] = array(
			"CONTENT" => array(
				'<input type="radio" name="ACTION" id="MOVE_'.$arResult['IndexForForm'].'" value="MOVE"  onclick="return true;" />',
				'<label for="MOVE_'.$arResult['IndexForForm'].'">'.GetMessage("FMI_MESSAGE_MOVE1").'</label>'),
		"ONCLICK" => "document.getElementById('forum_form_message_".$arResult["IndexForForm"]."').ACTION.value = 'MOVE';".
			"document.getElementById('MOVE_".$arResult["IndexForForm"]."').checked = true");
	endif;
		
	if ($arResult["CanUserDeleteTopic"] == "Y"):
	$arResult["popup"]["messages"][] = array(
		"CONTENT" => array(
			'<input type="radio" name="ACTION" id="DEL_'.$arResult['IndexForForm'].'" value="DEL"  onclick="return true;" />',
			'<label for="DEL_'.$arResult['IndexForForm'].'">'.GetMessage("FMI_MESSAGE_DELETE1").'</label>'),
		"ONCLICK" => "document.getElementById('forum_form_message_".$arResult["IndexForForm"]."').ACTION.value = 'DEL';".
			"document.getElementById('DEL_".$arResult["IndexForForm"]."').checked = true");
	endif;
	$arResult["popup"]["messages"][] = array(
		"CONTENT" => '<input type="button" name="SEND_FORM" id="'.$arResult["IndexForForm"].'" '.
			'onclick="if(typeof(forumForm) == \'object\'){forumForm.Send(\'message\', this.id); fMenu.PopupHide();}" value="'.GetMessage("FMI_EXEC").'"/>',
		"CLASS" => "forum-submit");

?>	<form id="forum_form_message_<?=$arResult["IndexForForm"]?>" action="<?=$APPLICATION->GetCurPageParam()?>" method="get" class="forum-form">
		<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
		<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
		<input type="hidden" name="MID" value="<?=$arParams["MID"]?>" />
		<input type="hidden" name="AJAX_TYPE" value="<?=$arParams["AJAX_TYPE"]?>" />
		<input type="hidden" name="ACTION" value="SHOW" />
		<input type="hidden" name="sessid" value="<?=bitrix_sessid()?>" />
		<input type="hidden" name="PAGE_NAME" value="<?=strToLower($arResult["sSection"])?>" />
	</form><?
}

?><table cellspacing="0" class="forum-menu"><tr><td class="forumtoolbar">
	<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td><div class="forumtoolsection"></div></td>
			<td><div class="forumtoolsection"></div></td>
			<td><div class="icon-flist" onclick="window.location='<?=$arResult["index"]?>'" title="<?=GetMessage("FMI_2TOPIC_LIST")?>"></div></td>
			<td><a href="<?=$arResult["index"]?>" title="<?=GetMessage("FMI_2TOPIC_LIST")?>"><?=GetMessage("FMI_FORUM_LIST")?></a></td><?
			
	if ($arResult["MAIN_PANEL"]["FORUMS_LIST"] == "Y"):
			?><td>
			<?$APPLICATION->IncludeComponent(
				"bitrix:forum.interface", "popup", 
				array("DATA" => $arResult["popup"]["forums"]), $component, 
				array("HIDE_ICONS" => "Y"));?></td><?
	endif;
	
	if ($arResult["sSection"]=="READ" || $arResult["sSection"]=="TOPIC_NEW"):
			?><td><div class="forumtoolseparator"></div></td>
			<td><div class="icon-flist" onclick="window.location='<?=$arResult["list"]?>'" title="<?=GetMessage("FMI_2TOPICS")?>"></div></td>
			<td><a href="<?=$arResult["list"]?>" title="<?=GetMessage("FMI_2TOPICS")?>"><?=GetMessage("FMI_2TOPICS_LIST1")?></a></td><?
	endif;
	
			?><td><div class="forumtoolseparator"></div></td>
			<td><div class="icon-active" onclick="window.location='<?=$arResult["active"]?>'" title="<?=GetMessage("FMI_FORUM_ACTIVE_TITLE")?>"></div></td>
			<td><a href="<?=$arResult["active"]?>" title="<?=GetMessage("FMI_FORUM_ACTIVE_TITLE")?>"><?=GetMessage("FMI_FORUM_ACTIVE")?></a></td><?
			
	if ($arResult["SHOW_SEARCH"] == "Y"):
			?><td><div class="forumtoolseparator"></div></td>
			<td><div class="icon-search" onclick="window.location='<?=$arResult["search"]?>'" title="<?=GetMessage("FMI_FORUM_SEARCH")?>"></div></td>
			<td><noindex><a href="<?=$arResult["search"]?>" title="<?=GetMessage("FMI_FORUM_SEARCH_TITLE")?>" rel="nofollow"><?=GetMessage("FMI_FORUM_SEARCH")?></a></noindex></td><?
	endif;
	
			?><td><div class="forumtoolseparator"></div></td>
			<td><div class="icon-user_list" onclick="window.location='<?=$arResult["user_list"]?>'" title="<?=GetMessage("FMI_LIST_USER_TITLE")?>"></div></td>
			<td><a href="<?=$arResult["user_list"]?>" title="<?=GetMessage("FMI_LIST_USER_TITLE")?>"><?=GetMessage("FMI_LIST_USER")?></a></td>
			<td><div class="forumtoolsection"></div></td>
			<td><div class="forumtoolsection"></div></td>
			<td><div class="icon-rules" onclick="window.location='<?=$arResult["rules"]?>'" title="<?=GetMessage("FMI_FORUM_RULES_TITLE")?>"></div></td>
			<td><a href="<?=$arResult["rules"]?>" title="<?=GetMessage("FMI_FORUM_RULES_TITLE")?>" class="attention"><?=GetMessage("FMI_FORUM_RULES")?></a></td>
			<td><div class="forumtoolseparator"></div></td>
			<td><div class="icon-help" onclick="window.location='<?=$arResult["help"]?>'" title="<?=GetMessage("FMI_FORUM_HELP")?>"></div></td>
			<td><a href="<?=$arResult["help"]?>" title="<?=GetMessage("FMI_FORUM_HELP")?>"><?=GetMessage("FMI_FORUM_HELP")?></a></td>
		</tr></table></td></tr>
	<tr>
		<td class="forumtoolbar">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td><div class="forumtoolsection"></div></td>
					<td><div class="forumtoolsection"></div></td><?
	if ($arResult["IsAuthorized"] == "Y"):
					?><td><div class="icon-profile" onclick="window.location='<?=$arResult["profile_view"]?>'" title="<?=GetMessage("FMI_FORUM_PROFILE")?>"></div></td>
					<td><a href="<?=$arResult["profile_view"]?>" title="<?=GetMessage("FMI_FORUM_PROFILE")?>"><?=GetMessage("FMI_FORUM_PROFILE1")?></a></td>
					<td><div class="forumtoolseparator"></div></td>
					<td><div class="icon-pm" onclick="window.location='<?=$arResult["pm_list"]?>'" title="<?=GetMessage("FMI_PM_VIEW")?>"></div></td>
					<td><a href="<?=$arResult["pm_list"]?>" title="<?=GetMessage("FMI_PM_VIEW")?>"><?=GetMessage("FMI_PM_VIEW"); ?> <?=$arResult["UNREAD_PM"]?></a></td>
					<td><div class="forumtoolseparator"></div></td>
					<td><div class="icon-subscribe" onclick="window.location='<?=$arResult["subscr_list"]?>'" title="<?=GetMessage("FMI_FORUM_SUBSCR_CHANGE")?>"></div></td>
					<td><a href="<?=$arResult["subscr_list"]?>" title="<?=GetMessage("FMI_FORUM_SUBSCR_CHANGE")?>"><?=GetMessage("FMI_SUBSCR_CHANGE")?></a></td>
					<td><div class="forumtoolseparator"></div></td>
					<td><div class="icon-logout" onclick="window.location='<?=$arResult["AUTH"]["LOGOUT"]?>'" title="<?=GetMessage("FMI_LOGOUT")?>"></div></td>
					<td><a href="<?=$arResult["AUTH"]["LOGOUT"]?>" title="<?=GetMessage("FMI_LOGOUT")?>"><?=GetMessage("FMI_OUT")?></a></td><?
	else: 
					?><td><div class="icon-login" onclick="window.location='<?=$arResult["AUTH"]["LOGIN"]?>'" title="<?=GetMessage("FMI_AUTHORIZE")?>"></div></td><?
		if ($arParams["TMPLT_SHOW_AUTH_FORM"] == "INPUT"):
					?><noindex><td><a href="<?=$arResult["AUTH"]["LOGIN"]?>" target="_self" title="<?=GetMessage("FMI_AUTHORIZE_TITLE")?>" rel="nofollow"><?=GetMessage("FMI_AUTHORIZE")?>:</a>&nbsp;</td>
					<td nowrap="nowrap">
						<form action="<?=POST_FORM_ACTION_URI?>" method="post" name="FORUM_AUTH_<?=$arResult["IndexForForm"]?>" id="FORUM_AUTH_<?=$arResult["IndexForForm"]?>" target="_self">
							<input type="hidden" name="auth" value='yes' />
							<input type="hidden" name="backurl" value='<?=$arResult["backurl_ecode"]?>' />
							<input type="hidden" name="AUTH_FORM" value="Y" />
							<input type="hidden" name="TYPE" value="AUTH" />
							<input type="text" id="USER_LOGIN" name="USER_LOGIN" value="<?=(strLen($arResult["AUTH"]["USER_LOGIN"]) > 0 ? $arResult["AUTH"]["USER_LOGIN"] : GetMessage("FMI_LOGIN"))?>" onfocus="if (this.value == '<?=GetMessage("FMI_LOGIN")?>') this.value = '';"/>
							<input type="password" id="USER_PASSWORD" name="USER_PASSWORD" value="<?=(strLen($arResult["AUTH"]["USER_LOGIN"]) > 0 ? "" : GetMessage("FMI_PASSWORD"))?>" onfocus="if (this.value == '<?=GetMessage("FMI_PASSWORD")?>') this.value = '';" />
							<input type="submit" id="ENTER" name="ENTER" value="<?=GetMessage("FMI_ENTER")?>" />
						</form>
					</td></noindex><?
		else:
					?><noindex><td><a href="<?=$arResult["AUTH"]["LOGIN"]?>" title="<?=GetMessage("FMI_AUTHORIZE")?>" target="_self" rel="nofollow"><?=GetMessage("FMI_ENTER")?></a></td></noindex><?
		endif;
		
		/* registration information */
		if($arResult["new_user_registration"] == "Y"):
					?><td><div class="forumtoolseparator"></div></td>
					<td><div class="icon-register" onclick="window.location='<?=$arResult["AUTH"]["REGISTER"]?>'" title="<?=GetMessage("FMI_REGISTER_TITLE")?>"></div></td>
					<td><noindex><a href="<?=$arResult["AUTH"]["REGISTER"]?>" title="<?=GetMessage("FMI_REGISTER_TITLE")?>" target="_self" rel="nofollow"><?=GetMessage("FMI_REGISTER")?></a></noindex></td><?
		endif;
		
		if ($arResult["MAIN_PANEL"]["FORUMS"] == "Y"):
			$arResult["set_be_read"] = ForumAddPageParams(
				$arResult[strToLower($arResult["PAGE_NAME"])], 
				array("ACTION" => "SET_BE_READ"))."&amp;".bitrix_sessid_get();
			$title = (($arResult["sSection"] == "INDEX") ? GetMessage("FMI_MARK_READ_FORUMS") : GetMessage("FMI_MARK_READ_FORUM"));
			$descr = (($arResult["sSection"] == "INDEX") ? GetMessage("FMI_MARK_READ_FORUMS_TITLE") : GetMessage("FMI_MARK_READ_FORUM_TITLE"));
					?><td><div class="forumtoolsection"></div></td>
					<td><div class="forumtoolsection"></div></td>
					<td><div class="icon-forum-read" onclick="window.location='<?=$arResult["set_be_read"]?>';" title="<?=$descr?>"></div></td>
					<td><a href="<?=$arResult["set_be_read"]?>" title="<?=$descr?>"><?=$title?></a></td><?
		endif;
		
	endif;
				?></tr>
			</table>
		</td>
	</tr><?
	
/* Action tools */
if ($arResult["MANAGE_PANEL"]["SHOW"] == "Y"):
	?><tr>
		<td class="forumtoolbar">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr valign="middle">
					<td><div class="forumtoolsection"></div></td>
					<td><div class="forumtoolsection"></div></td><?
		/* Subscribe */
		if ($arResult["MANAGE_PANEL"]["SUBSCRIBE"] == "Y"):
					?><td>
						<?$APPLICATION->IncludeComponent(
							"bitrix:forum.interface", "popup", 
							array(
							"HEAD" => array(
								"CLASS" => "",
								"ICON" => "icon-subscribe",
								"TITLE" => "",
								"CONTENT" => GetMessage("FMI_SUBSCRIBE_ON")),
							
							"DATA" => $arResult["popup"]["subscribe"]), 
							$component,
							array("HIDE_ICONS" => "Y"));?></td><?
		endif;
		
		/* Manage topics */
		if ($arResult["MANAGE_PANEL"]["TOPICS"] == "Y"):
			?><td><div class="forumtoolseparator"></div></td>
			<td>
				<?$APPLICATION->IncludeComponent(
					"bitrix:forum.interface", "popup", 
					array(
						"HEAD" => array(
							"CLASS" => "",
							"ICON" => "icon-topics",
							"TITLE" => "",
							"CONTENT" => GetMessage("FMI_MANAGE_TOPICS")),
						
						"DATA" => $arResult["popup"]["topics"]), 
					$component,
					array("HIDE_ICONS" => "Y"));?></td><?
		endif;
		
		/* Manage messages */
		if ($arResult["MANAGE_PANEL"]["MESSAGES"] == "Y"):
			?><td><div class="forumtoolseparator"></div></td>
			<td>
				<?$APPLICATION->IncludeComponent(
					"bitrix:forum.interface", "popup", 
					array(
						"HEAD" => array(
							"CLASS" => "",
							"ICON" => "icon-message",
							"TITLE" => "",
							"CONTENT" => GetMessage("FMI_MANAGE_MESSAGES")),
						"DATA" => $arResult["popup"]["messages"]), 
					$component,
					array("HIDE_ICONS" => "Y"));?></td><?
		endif;
		
		if ($arResult["MANAGE_PANEL"]["FORUMS"] == "Y"):
			$title = (($arResult["sSection"] == "INDEX") ? GetMessage("FMI_MARK_READ_FORUMS") : GetMessage("FMI_MARK_READ_FORUM"));
			$descr = (($arResult["sSection"] == "INDEX") ? GetMessage("FMI_MARK_READ_FORUMS_TITLE") : GetMessage("FMI_MARK_READ_FORUM_TITLE"));
			if ($arResult["sSection"] != "INDEX"):
			?><td><div class="forumtoolseparator"></div></td><?
			endif;
			?><td><div class="icon-forum-read" onclick="window.location='<?=$arResult["set_be_read"]?>';" title="<?=$descr?>"></div></td>
					<td><a href="<?=$arResult["set_be_read"]?>" title="<?=$descr?>"><?=$title?></a></td><?
		endif;
			?></tr>
			</table>
		</td>
	</tr><?
endif;


if (($arResult["IsAuthorized"] == "Y") && ($arResult["sSection"]=="PM")):?>
	<tr>
		<td class="forumtoolbar">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td><div class="forumtoolsection"></div></td>
					<td><div class="forumtoolsection"></div></td>
					<td><div class="icon-pm_sent" onclick="window.location='<?=$arResult["pm_edit"]?>'" title="<?=GetMessage("FMI_PM_SENT")?>"></div></td>
					<td><a href="<?=$arResult["pm_edit"]?>" title="<?=GetMessage("FMI_PM_SENT")?>"><?=GetMessage("FMI_PM_SENT")?></a></td>
					<td><div class="forumtoolseparator"></div></td>
					<td><div class="icon-pm_inbox" onclick="window.location='<?=$arResult["pm_list"]?>'" title="<?=GetMessage("FMI_PM_INBOX")?>"></div></td>
					<td><a href="<?=$arResult["pm_list"]?>" title="<?=GetMessage("FMI_PM_INBOX")?>"><?=GetMessage("FMI_PM_INBOX")?></a></td>
					<?if ($arParams["pm_version"] == 1):?>
					<td><div class="forumtoolseparator"></div></td>
					<td><div class="icon-pm_send" onclick="window.location='<?=$arResult["pm_list_outcoming"]?>'" title="<?=GetMessage("FMI_PM_SEND")?>"></div></td>
					<td><a href="<?=$arResult["pm_list_outcoming"]?>" title="<?=GetMessage("FMI_PM_SEND")?>"><?=GetMessage("FMI_PM_SEND")?></a></td>
					<?endif;?>
					<td><div class="forumtoolseparator"></div></td>
					<td><div class="icon-pm_outbox" onclick="window.location='<?=$arResult["pm_list_outbox"]?>'" title="<?=GetMessage("FMI_PM_OUTBOX")?>"></div></td>
					<td><a href="<?=$arResult["pm_list_outbox"]?>" title="<?=GetMessage("FMI_PM_OUTBOX")?>"><?=GetMessage("FMI_PM_OUTBOX")?></a></td>
					<td><div class="forumtoolseparator"></div></td>
					<td><div class="icon-pm_recyled" onclick="window.location='<?=$arResult["pm_list_recycled"]?>'" title="<?=GetMessage("FMI_PM_RECYLED")?>"></div></td>
					<td><a href="<?=$arResult["pm_list_recycled"]?>" title="<?=GetMessage("FMI_PM_RECYLED")?>"><?=GetMessage("FMI_PM_RECYLED")?></a></td>
	<?if (count($arResult["FOLDER_USER"]) <= 0):?>
					<td>&nbsp;</td>
					<td><div class="forumtoolsection"></div></td>
					<td><div class="forumtoolsection"></div></td>
					<td><div class="icon-folders" onclick="window.location='<?=$arResult["pm_folder"]?>'" title="<?=GetMessage("FMI_PM_FOLDER")?>"></div></td>
					<td><a href="<?=$arResult["pm_folder"]?>" title="<?=GetMessage("FMI_PM_FOLDER")?>"><?=GetMessage("FMI_PM_FOLDER")?></a></td>
	<?endif;?>
				</tr>
			</table>
		</td>
	</tr>
	<?if (count($arResult["FOLDER_USER"]) > 0):?>
	<tr>
		<td class="forumtoolbar">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td><div class="forumtoolsection"></div></td>
					<td><div class="forumtoolsection"></div></td>
					<td><div class="icon-folders" onclick="window.location='<?=$arResult["pm_folder"]?>'" title="<?=GetMessage("FMI_PM_FOLDER")?>"></div></td>
					<td><a href="<?=$arResult["pm_folder"]?>" title="<?=GetMessage("FMI_PM_FOLDER")?>"><?=GetMessage("FMI_PM_FOLDER")?></a></td>
					<td><div class="forumtoolseparator"></div></td>
				<?$ii=0;?>
				<?foreach ($arResult["FOLDER_USER"] as $res):?>
					<?if ($ii != 0):?>
					<td><div class="forumtoolseparator"></div></td>
					<?$ii=1;?>
					<?endif;?>
					<td><div class="icon-folder" onclick="window.location='<?=$res["pm_list"]?>'" title="<?=$res["TITLE"]?>"></div></td>
					<td><a href="<?=$res["pm_list"]?>" title="<?=$res["TITLE"]?>"><?=$res["TITLE"]?></a></td>
				<?endforeach;?>
				</tr>
			</table>
		</td>
	</tr>
	<?endif;?>
<?endif?>
</table><?
if ($arResult["MANAGE_PANEL"]["MESSAGES"] == "Y"):
endif;?>
<script>
if (typeof oText != "object")
		var oText = {};
oText['no_data'] = '<?=CUtil::addslashes((($arResult["sSection"] == "LIST") ? GetMessage('JS_NO_TOPICS') : GetMessage('JS_NO_MESSAGES')))?>';
oText['no_sessid'] = '<?=CUtil::addslashes(GetMessage("JS_NO_SESSID"))?>';
oText['no_page_name'] = '<?=CUtil::addslashes(GetMessage("JS_NO_PAGE_NAME"))?>';
oText['no_fid'] = '<?=CUtil::addslashes(GetMessage("JS_NO_FID"))?>';
oText['no_tid'] = '<?=CUtil::addslashes(GetMessage("JS_NO_TID"))?>';
oText['del_topic'] = '<?=CUtil::addslashes(GetMessage("JS_DEL_TOPIC"))?>';
oText['del_topics'] = '<?=CUtil::addslashes(GetMessage("JS_DEL_TOPICS"))?>';
oText['del_message'] = '<?=CUtil::addslashes(GetMessage("JS_DEL_MESSAGE"))?>';
oText['del_messages'] = '<?=CUtil::addslashes(GetMessage("JS_DEL_MESSAGES"))?>';
</script>