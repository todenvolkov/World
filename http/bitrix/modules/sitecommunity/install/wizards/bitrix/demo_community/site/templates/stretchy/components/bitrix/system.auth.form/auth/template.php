<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<ul id="user-menu">
<?if ($arResult["FORM_TYPE"] != "login") {?>
	<li><?=GetMessage("AUTH_HELLO_1")?><a href="<?= $arResult["urlToOwnProfile"] ?>"><?= $arResult["USER_NAME"]?></a><?=GetMessage("AUTH_HELLO_2")?></li>
	<? 
	if (!empty($arResult["urlToOwnBlog"])) 
	{
	?>
		<li><a href="<?=$arResult["urlToOwnBlog"]?>"><?=GetMessage("AUTH_BLOG")?></a></li>
		<li><a href="<?=$arResult["urlToCreateMessageInBlog"]?>"><?=GetMessage("AUTH_BLOG_NEW_POST")?></a></li>
	<? 
	} 
	elseif (!empty($arResult["urlToCreateInBlog"])) 
	{
	?>
		<li><a href="<?=$arResult["urlToCreateInBlog"]?>"><?=GetMessage("AUTH_BLOG_CREATE")?></a></li>
	<? 
	}
	?>
	<? if (array_key_exists("PATH_TO_SONET_MESSAGES", $arParams) && strlen($arParams["PATH_TO_SONET_MESSAGES"]) > 0) {?>
		<li><a href="<?=$arParams["PATH_TO_SONET_MESSAGES"]?>"><?=GetMessage("AUTH_SONET_MESSAGES")?></a> 
		<?$APPLICATION->IncludeComponent("bitrix:socialnetwork.events_dyn", "", Array(
				"PATH_TO_USER"	=>	SITE_DIR."people/user/#user_id#/",
				"PATH_TO_GROUP"	=>	SITE_DIR."groups/group/#group_id#/",
				"PATH_TO_MESSAGES"	=>	$arParams["PATH_TO_SONET_MESSAGES"],
				"PATH_TO_MESSAGE_FORM"	=>	SITE_DIR."people/messages/form/#user_id#/",
				"PATH_TO_MESSAGE_FORM_MESS"	=>	SITE_DIR."people/messages/form/#user_id#/#message_id#/",
				"PATH_TO_MESSAGES_CHAT"	=>	SITE_DIR."people/messages/chat/#user_id#/",
				"PATH_TO_SMILE"	=>	"/bitrix/images/socialnetwork/smile/",
				"MESSAGE_VAR"	=>	"message_id",
				"PAGE_VAR"	=>	"page",
				"USER_VAR"	=>	"user_id",
				"UNREAD_CNT_ID"	=>	"bx_events_dyn_unread_cnt",
				"UNREAD_CNT_STR_BEFORE" => "(",
				"UNREAD_CNT_STR_AFTER" => ")"
				)
			);
		?>
		</li>	
	<? } ?>
	<li><a href="<?= $GLOBALS["APPLICATION"]->GetCurPageParam("logout=yes", array("logout")) ?>" title="<?=GetMessage("AUTH_LOGOUT")?>"><?=GetMessage("AUTH_LOGOUT")?></a></li>
<? } else { ?>

	<li><?=GetMessage("AUTH_HELLO_1")?><b><?=GetMessage("AUTH_GUEST")?><?=GetMessage("AUTH_HELLO_2")?></b></li>
	<li><a href="<?= SITE_DIR."auth/?backurl=".$GLOBALS["APPLICATION"]->GetCurPageParam("", array("login", "logout")) ?>" title="<?=GetMessage("AUTH_LOGIN_DESC")?>"><?=GetMessage("AUTH_LOGIN")?></a></li>
	<? if($arResult["NEW_USER_REGISTRATION"] == "Y") {?>
		<li><a href="<?=$arResult["AUTH_REGISTER_URL"]?>" title="<?=GetMessage("AUTH_REGISTER_DESC")?>"><?=GetMessage("AUTH_REGISTER")?></a></li>
	<? } ?>
<?} ?>
</ul>