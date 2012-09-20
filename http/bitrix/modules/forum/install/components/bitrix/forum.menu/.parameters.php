<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	
	"PARAMETERS" => Array(
		"FID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["FID"]}'),
		"TID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_TID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["FID"]}'),
		"PAGE_NAME" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_PAGE_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),

		"URL_TEMPLATES_INDEX" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_INDEX_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
		"URL_TEMPLATES_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		"URL_TEMPLATES_SUBSCR_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_SUBSCR_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "subscr_list.php"),
		"URL_TEMPLATES_ACTIVE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_ACTIVE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "active.php"),
		"URL_TEMPLATES_SEARCH" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_SEARCH_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "search.php"),
		"URL_TEMPLATES_HELP" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_HELP_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "help.php"),
		"URL_TEMPLATES_USER_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_USER_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "user_list.php"),
		"URL_TEMPLATES_PM_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_list.php"),
		"URL_TEMPLATES_PM_EDIT" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_EDIT_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_edit.php?FID=#FID#&MID=#MID#&UID=#UID#&mode=#mode#"),
		"URL_TEMPLATES_PM_FOLDER" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_FOLDER_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_folder.php"),
		"URL_TEMPLATES_PM_FOLDER" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_FOLDER_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_folder.php"),
		"URL_TEMPLATES_RULES" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_RULES_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "rules.php"),
		"PATH_TO_AUTH_FORM" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PATH_TO_AUTH_FORM"),
			"TYPE" => "STRING",
			"DEFAULT" => "/auth.php"),
		
		"FID_RANGE" => CForumParameters::GetForumsMultiSelect(GetMessage("F_DEFAULT_FID"), "ADDITIONAL_SETTINGS"), 
		"SHOW_FORUM_ANOTHER_SITE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SHOW_FORUM_ANOTHER_SITE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"AJAX_TYPE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_AJAX_TYPE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),

		"CACHE_TIME" => Array(),
	)
);
?>
