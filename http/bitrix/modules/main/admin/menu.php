<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

if(!method_exists($USER, "CanDoOperation"))
	return false;

IncludeModuleLangFile(__FILE__);
global $DBType, $adminMenu, $adminPage;

$aMenu = array();
if($USER->CanDoOperation('view_all_users') || $USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('edit_own_profile') || $USER->CanDoOperation('view_groups') || $USER->CanDoOperation('view_other_settings'))
{
	$aMenu[] = array(
		"parent_menu" => "global_menu_settings",
		"sort" => 50,
		"text" => GetMessage("MAIN_MENU_FAVORITE_HEADER"),
		"title" => GetMessage("MAIN_MENU_FAVORITE_ALT"),
		"url" => "favorite_list.php?lang=".LANGUAGE_ID,
		"more_url" => array("favorite_edit.php"),
		"icon" => "fav_menu_icon",
		"page_icon" => "fav_page_icon",
	);
}

if($USER->CanDoOperation('view_all_users') || $USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('edit_subordinate_users') || $USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('view_groups') || $USER->CanDoOperation('view_tasks'))
{
	$array_user_items = array();
	if ($USER->CanDoOperation('view_all_users') || $USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('edit_subordinate_users') || $USER->CanDoOperation('edit_all_users'))
		$array_user_items[] = array(
				"text" => GetMessage("MAIN_MENU_USER_LIST"),
				"url" => "user_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("user_edit.php"),
				"title" => GetMessage("MAIN_MENU_USERS_ALT"),
			);

	if ($USER->CanDoOperation('view_groups'))
		$array_user_items[] = array(
			"text" => GetMessage("MAIN_MENU_GROUPS"),
			"url" => "group_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("group_edit.php"),
			"title" => GetMessage("MAIN_MENU_GROUPS_ALT"),
		);

	if ($USER->CanDoOperation('view_tasks'))
		$array_user_items[] = array(
			"text" => GetMessage("MAIN_MENU_TASKS"),
			"url" => "task_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("task_edit.php"),
			"title" => GetMessage("MAIN_MENU_TASKS_ALT"),
		);

	if ($USER->CanDoOperation('edit_all_users'))
		$array_user_items[] = array(
			"text" => GetMessage("MAIN_MENU_USER_IMPORT"),
			"url" => "user_import.php?lang=".LANGUAGE_ID,
			"title" => GetMessage("MAIN_MENU_USER_IMPORT_ALT"),
		);

	$aMenu[] = array(
		"parent_menu" => "global_menu_settings",
		"section" => "GENERAL",
		"sort" => 100,
		"text" => GetMessage("MAIN_MENU_MANAG"),
		"title" => GetMessage("MAIN_MENU_USERS_ALT"),
		"icon" => "user_menu_icon",
		"page_icon" => "user_page_icon",
		"items_id" => "menu_users",
		"url" => "user_index.php?lang=".LANGUAGE_ID,
		"items" => $array_user_items,
	);
}

if($USER->CanDoOperation('edit_own_profile')  && !($USER->CanDoOperation('view_all_users') || $USER->CanDoOperation('view_subordinate_users')))
{
	$aMenu[] = array(
		"parent_menu" => "global_menu_settings",
		"section" => "GENERAL",
		"sort" => 100,
		"text" => GetMessage("MAIN_MENU_PROFILE"),
		"title" => GetMessage("MAIN_MENU_PROFILE_ALT"),
		"icon" => "user_menu_icon",
		"page_icon" => "user_page_icon",
		"url" => "user_edit.php?lang=".LANGUAGE_ID."&amp;ID=".$USER->GetID(),
		"more_url" => array("user_edit.php"),
	);

	$aMenu[] = array(
		"parent_menu" => "global_menu_settings",
		"sort" => 200,
		"text" => GetMessage("MAIN_MENU_INTERFACE"),
		"title" => GetMessage("MAIN_MENU_INTERFACE_TITLE"),
		"icon" => "sys_menu_icon",
		"page_icon" => "sys_page_icon",
		"url" => "user_settings.php?lang=".LANGUAGE_ID,
	);
}

if(!$USER->CanDoOperation('view_other_settings') && $USER->CanDoOperation('lpa_template_edit'))
{
	$aMenu[] = array(
		"parent_menu" => "global_menu_settings",
		"section" => "MAIN",
		"sort" => 1700,
		"text" => GetMessage("MAIN_MENU_SETTINGS"),
		"title" => GetMessage("MAIN_MENU_SETTINGS_TITLE"),
		"url" => "settings_index.php?lang=".LANGUAGE_ID,
		"icon" => "sys_menu_icon",
		"page_icon" => "sys_page_icon",
		"items_id" => "menu_system",
		"items" => array(
				array(
					"text" => GetMessage("MAIN_MENU_SITES_LIST"),
					"url" => "site_admin.php?lang=".LANGUAGE_ID,
					"more_url" => array("site_edit.php"),
					"title" => GetMessage("MAIN_MENU_SITES_ALT"),
				),
				array(
					"text" => GetMessage("MAIN_MENU_TEMPLATE"),
					"title" => GetMessage("MAIN_MENU_TEMPL_TITLE"),
					"url" => "template_admin.php?lang=".LANGUAGE_ID,
					"more_url" => array(
						"template_edit.php",
						"template_load.php"
					),
				),
			),
		);
}

if($USER->CanDoOperation('view_other_settings'))
{
	$aModuleItems = array();
	if(method_exists($adminMenu, "IsSectionActive"))
	{
		if($adminMenu->IsSectionActive("menu_module_settings") || ($APPLICATION->GetCurPage() == "/bitrix/admin/settings.php" && $_REQUEST["mid_menu"]<>""))
		{
			$adminPage->Init();
			foreach($adminPage->aModules as $module)
			{
				if($module <> "main")
				{
					if($APPLICATION->GetGroupRight($module) < "R")
						continue;

					$ifile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module."/install/index.php";
					$ofile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module."/options.php";
					if(!file_exists($ifile) || !file_exists($ofile))
						continue;

					$info = CModule::CreateModuleObject($module);
					$name = $info->MODULE_NAME;
					$sort = $info->MODULE_SORT;
				}
				else
				{
					if(!$USER->CanDoOperation('view_other_settings'))
						continue;
					$name = GetMessage("MAIN_MENU_MAIN_MODULE");
					$sort = -1;
				}

				$aModuleItems[] = array(
					"text" => $name,
					"url" => "settings.php?lang=".LANGUAGE_ID."&amp;mid=".$module."&amp;mid_menu=1",
					"more_url"=>array("settings.php?lang=".LANGUAGE_ID."&mid=".$module."&mid_menu=1"),
					"title" => GetMessage("MAIN_MENU_MODULE_SETT")." &quot;".$name."&quot;",
					"sort" => $sort,
				);
			}
			usort($aModuleItems, create_function('$a, $b', 'if($a["sort"] == $b["sort"]) return strcasecmp($a["text"], $b["text"]); return ($a["sort"] < $b["sort"])? -1 : 1;'));
		}
	}

	$aMenu[] = array(
		"parent_menu" => "global_menu_settings",
		"section" => "MAIN",
		"sort" => 1700,
		"text" => GetMessage("MAIN_MENU_SETTINGS"),
		"title" => GetMessage("MAIN_MENU_SETTINGS_TITLE"),
		"url" => "settings_index.php?lang=".LANGUAGE_ID,
		"icon" => "sys_menu_icon",
		"page_icon" => "sys_page_icon",
		"items_id" => "menu_system",
		"items" => array(
			array(
				"text" => GetMessage("MAIN_MENU_SITES"),
				"title" => GetMessage("MAIN_MENU_SITES_TITLE"),
				"url" => "site_admin.php?lang=".LANGUAGE_ID,
				"items_id" => "menu_site",
				"items" => array(
					array(
						"text" => GetMessage("MAIN_MENU_SITES_LIST"),
						"url" => "site_admin.php?lang=".LANGUAGE_ID,
						"more_url" => array("site_edit.php"),
						"title" => GetMessage("MAIN_MENU_SITES_ALT"),
					),
					array(
						"text" => GetMessage("MAIN_MENU_TEMPLATE"),
						"title" => GetMessage("MAIN_MENU_TEMPL_TITLE"),
						"url" => "template_admin.php?lang=".LANGUAGE_ID,
						"more_url" => array(
							"template_edit.php",
							"template_load.php"
						),
					),
				),
			),
			array(
				"text" => GetMessage("MAIN_MENU_LANG"),
				"url" => "lang_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("lang_edit.php"),
				"title" => GetMessage("MAIN_MENU_LANGS_ALT"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_EVENT"),
				"title" => GetMessage("MAIN_MENU_EVENT_TITLE"),
				"url" => "message_admin.php?lang=".LANGUAGE_ID,
				"items_id" => "menu_templates",
				"items" => array(
					array(
						"text" => GetMessage("MAIN_MENU_TEMPLATES"),
						"url" => "message_admin.php?lang=".LANGUAGE_ID,
						"more_url" => array("message_edit.php"),
						"title" => GetMessage("MAIN_MENU_TEMPLATES_ALT"),
					),
					array(
						"text" => GetMessage("MAIN_MENU_EVENT_TYPES"),
						"title" => GetMessage("MAIN_MENU_EVENT_TYPES_TITLE"),
						"url" => "type_admin.php?lang=".LANGUAGE_ID,
						"more_url" => array(
							"type_edit.php"
						),
					),
				),
			),
			array(
				"text" => GetMessage("MAIN_MENU_MODULES"),
				"url" => "module_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("module_edit.php"),
				"title" => GetMessage("MAIN_MENU_MODULES_ALT"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_CACHE"),
				"url" => "cache.php?lang=".LANGUAGE_ID,
				"more_url" => array(),
				"title" => GetMessage("MAIN_MENU_CACHE_ALT"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_URLREWRITE"),
				"url" => "urlrewrite_list.php?lang=".LANGUAGE_ID,
				"more_url" => array("urlrewrite_edit.php", "urlrewrite_reindex.php"),
				"title" => GetMessage("MAIN_MENU_URLREWRITE_ALT"),
			),


			array(
				"text" => GetMessage("MAIN_MENU_WIZARDS"),
				"url" => "wizard_list.php?lang=".LANGUAGE_ID,
				"more_url" => array("wizard_load.php", "wizard_export.php"),
				"title" => GetMessage("MAIN_MENU_WIZARDS_TITLE"),
			),

			array(
				"text" => GetMessage("MAIN_MENU_MODULE_SETTINGS"),
				"url" => "settings.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("MAIN_MENU_SETTINGS_ALT"),
				"dynamic"=>true,
				"module_id"=>"main",
				"items_id"=>"menu_module_settings",
				"items"=>$aModuleItems,
			),
			array(
				"text" => GetMessage("MAIN_MENU_INTERFACE"),
				"url" => "user_settings.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("MAIN_MENU_INTERFACE_TITLE"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_USER_FIELD"),
				"url" => "userfield_admin.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("MAIN_MENU_USER_FIELD_TITLE"),
				"more_url" => array("userfield_admin.php", "userfield_edit.php"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_CAPTCHA"),
				"url" => "captcha.php?lang=".LANGUAGE_ID,
				"title" => GetMessage("MAIN_MENU_CAPTCHA_TITLE"),
				"more_url" => array("captcha.php"),
			),
		),
	);

	$aMenu[] = array(
		"parent_menu" => "global_menu_settings",
		"section" => "TOOLS",
		"sort" => 1800,
		"text" => GetMessage("MAIN_MENU_TOOLS"),
		"title" => GetMessage("MAIN_MENU_TOOLS_TITLE"),
		"url" => "tools_index.php?lang=".LANGUAGE_ID,
		"icon" => "util_menu_icon",
		"page_icon" => "util_page_icon",
		"items_id" => "menu_util",
		"items" => array(
			array(
				"text" => GetMessage("MAIN_MENU_SITE_CHECKER"),
				"url" => "site_checker.php?lang=".LANGUAGE_ID,
				"more_url" => array(),
				"title" => GetMessage("MAIN_MENU_SITE_CHECKER_ALT"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_FILE_CHECKER"),
				"url" => "file_checker.php?lang=".LANGUAGE_ID,
				"more_url" => array(),
				"title" => GetMessage("MAIN_MENU_FILE_CHECKER_ALT"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_PHPINFO"),
				"url" => "phpinfo.php?test_var1=AAA&amp;test_var2=BBB",
				"more_url" => array("phpinfo.php"),
				"title" => GetMessage("MAIN_MENU_PHPINFO_ALT"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_SQL"),
				"url" => "sql.php?lang=".LANGUAGE_ID."&amp;del_query=Y",
				"more_url" => array("sql.php"),
				"title" => GetMessage("MAIN_MENU_SQL_ALT"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_PHP"),
				"url" => "php_command_line.php?lang=".LANGUAGE_ID."",
				"more_url" => array("php_command_line.php"),
				"title" => GetMessage("MAIN_MENU_PHP_ALT"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_AGENT"),
				"url" => "agent_list.php?lang=".LANGUAGE_ID,
				"more_url" => array("agent_list.php", "agent_edit.php"),
				"title" => GetMessage("MAIN_MENU_AGENT_ALT"),
			),
			array(
				"text" => GetMessage("MAIN_MENU_DUMP"),
				"url" => "dump.php?lang=".LANGUAGE_ID,
				"more_url" => array("dump.php", "restore_export.php"),
				"title" => GetMessage("MAIN_MENU_DUMP_ALT"),
			),
			(strtoupper($DBType) == "MYSQL"?
				Array(
					"text" => GetMessage("MAIN_MENU_REPAIR_DB"),
					"url" => "repair_db.php?lang=".LANGUAGE_ID,
					"more_url" => array(),
					"title" => GetMessage("MAIN_MENU_REPAIR_DB_ALT"),
				)
				:null
			),
			(strtoupper($DBType) == "MYSQL"?
				Array(
					"text" => GetMessage("MAIN_MENU_OPTIMIZE_DB"),
					"url" => "repair_db.php?lang=".LANGUAGE_ID."&optimize_tables=Y",
					"more_url" => array("repair_db.php?optimize_tables=Y"),
					"title" => GetMessage("MAIN_MENU_OPTIMIZE_DB_ALT"),
				)
				:null
			),
			($USER->CanDoOperation('view_event_log')?
				Array(
					"text" => GetMessage("MAIN_MENU_EVENT_LOG"),
					"url" => "event_log.php?lang=".LANGUAGE_ID,
					"more_url" => array(),
					"title" => GetMessage("MAIN_MENU_EVENT_LOG_ALT"),
				)
				:null
			),
		),
	);

	$aMenu[] = array(
		"parent_menu" => "global_menu_settings",
		"sort" => 1900,
		"icon" => "update_menu_icon_partner",
		"page_icon" => "update_page_icon_partner",
		"text" => GetMessage("MAIN_MENU_UPDATES_MARKET"),
		"url" => "update_system_market.php?lang=".LANGUAGE_ID,
		"more_url" => array("update_system_market_detail.php"),
		"title" => GetMessage("MAIN_MENU_UPDATES_MARKET_ALT"),
		"items_id" => "menu_marketplace",
		"items" => array(
			array(
				"page_icon" => "update_page_icon_partner",
				"text" => GetMessage("MAIN_MENU_UPDATES_MARKET"),
				"url" => "update_system_market.php?lang=".LANGUAGE_ID,
				"more_url" => array("update_system_market_detail.php"),
				"title" => GetMessage("MAIN_MENU_UPDATES_MARKET_ALT"),
			),
			array(
				"page_icon" => "update_page_icon_partner",
				"text" => GetMessage("MAIN_MENU_UPDATES_PARTNER"),
				"url" => "update_system_partner.php?lang=".LANGUAGE_ID,
				"more_url" => array(),
				"title" => GetMessage("MAIN_MENU_UPDATES_PARTNER_ALT"),
			),
		),
	);

	$aMenu[] = array(
		"parent_menu" => "global_menu_settings",
		"sort" => 2000,
		"icon" => "update_menu_icon",
		"page_icon" => "update_page_icon",
		"text" => GetMessage("MAIN_MENU_UPDATES"),
		"url" => "update_system.php?lang=".LANGUAGE_ID,
		"more_url" => array("sysupdate_log.php", "sysupdate.php"),
		"title" => GetMessage("MAIN_MENU_UPDATES_ALT"),
	);
}
if($USER->CanDoOperation('edit_ratings'))
{
	$aMenu[] = array(
		"parent_menu" => "global_menu_services",
		"section" => "rating",
		"sort" => 300,
		"text" => GetMessage("MAIN_MENU_RATING"),
		"title" => GetMessage("MAIN_MENU_RATING_ALT"),
		"icon" => "rating_menu_icon",
		"page_icon" => "rating_page_icon",
		"items_id" => "menu_rating",
		"url" => "rating_index.php?lang=".LANGUAGE_ID,
		"items" => array(
			array(
				"page_icon" => "rating_page_icon",
				"text" => GetMessage("MAIN_MENU_RATING_LIST"),
				"title" => GetMessage("MAIN_MENU_RATING_LIST_ALT"),
				"url" => "rating_list.php?lang=".LANGUAGE_ID,
				"more_url" => array("rating_edit.php"),
			),
			array(
				"page_icon" => "rating_rule_page_icon",
				"text" => GetMessage("MAIN_MENU_RATING_RULE_LIST"),
				"title" => GetMessage("MAIN_MENU_RATING_RULE_LIST_ALT"),
				"url" => "rating_rule_list.php?lang=".LANGUAGE_ID,
				"more_url" => array("rating_rule_edit.php"),
			),
			array(
				"page_icon" => "rating_settings_page_icon",
				"text" => GetMessage("MAIN_MENU_RATING_SETTINGS"),
				"title" => GetMessage("MAIN_MENU_RATING_SETTINGS_ALT"),
				"url" => "rating_settings.php?lang=".LANGUAGE_ID,
			),
		),
	);
}

//print_r($aMenu);
return $aMenu;
?>