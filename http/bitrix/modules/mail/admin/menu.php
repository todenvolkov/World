<?
IncludeModuleLangFile(__FILE__);
$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if($MOD_RIGHT!="D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "mail",
		"sort" => 700,
		"text" => GetMessage("MAIL_MENU_MAIL"),
		"title" => GetMessage("MAIL_MENU_MAIL_TITLE"),
		"url" => "mail_index.php?lang=".LANGUAGE_ID,
		"icon" => "mail_menu_icon",
		"page_icon" => "mail_page_icon",
		"items_id" => "menu_mail",
		"items" => array(
			array(
				"text" => GetMessage("MAIL_MENU_MSG"),
				"url" => "mail_message_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array(
					"mail_message_view.php",
					"mail_check_new_messages.php"
				),
				"title" => GetMessage("MAIL_MENU_MSG_ALT")
			)
		)
	);


	if ($MOD_RIGHT>="R")
	{
		$aMenu["items"][] = array(
				"text" => GetMessage("MAIL_MENU_LOG"),
				"url" => "mail_log.php?lang=".LANGUAGE_ID,
				"more_url" => array(),
				"title" => GetMessage("MAIL_MENU_LOG_ALT")
			);
		$aMenu["items"][] = array(
				"text" => GetMessage("MAIL_MENU_RULES"),
				"url" => "mail_filter_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("mail_filter_edit.php"),
				"title" => GetMessage("MAIL_MENU_RULES_ALT")
			);
		$aMenu["items"][] = array(
				"text" => GetMessage("MAIL_MENU_MAILBOXES"),
				"url" => "mail_mailbox_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("/bitrix/admin/mail_mailbox_edit.php"),
				"title" => GetMessage("MAIL_MENU_MAILBOXES_ALT")
			);
	}
	return $aMenu;
}
return false;
?>