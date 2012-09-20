<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class main extends CModule
{
	var $MODULE_ID = "main";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	function main()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/install/index.php"));
		include($path."/classes/general/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = SM_VERSION;
			$this->MODULE_VERSION_DATE = SM_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("MAIN_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("MAIN_MODULE_DESC");
	}

	function InstallDB()
	{
		global $DB, $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $APPLICATION;

		if (!is_object($APPLICATION))
			$APPLICATION = new CMain;

		$DB = new CDatabase;
		$DB->DebugToFile = false;
		$DB->debug = true;

		if (!defined("DBPersistent"))
			define("DBPersistent", false);

		if (!$DB->Connect($DBHost, $DBName, $DBLogin, $DBPassword))
		{
			$APPLICATION->ThrowException(GetMessage("MAIN_INSTALL_DB_ERROR"));
			return false;
		}

		$result = $DB->Query("SELECT * FROM b_module WHERE ID='main'", true);
		$success = $result && $result->Fetch();
		if ($success)
			return true;

		if ($DBType == "mysql" && defined("MYSQL_TABLE_TYPE") && strlen(MYSQL_TABLE_TYPE)>0)
			$DB->Query("SET table_type = '".MYSQL_TABLE_TYPE."'", true);

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/".$DBType."/install.sql");
		if ($errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		$group = new CGroup;
		
		$arGroups = Array(
			Array(
				"ID" => 1,
				"ACTIVE" => "Y",
				"C_SORT" => 1,
				"NAME" => GetMessage("MAIN_ADMIN_GROUP_NAME"),
				"ANONYMOUS" => "N",
				"DESCRIPTION" => GetMessage("MAIN_ADMIN_GROUP_DESC")
			),
			Array(
				"ID" => 2,
				"ACTIVE" => "Y",
				"C_SORT" => 2,
				"NAME" => GetMessage("MAIN_EVERYONE_GROUP_NAME"),
				"ANONYMOUS" => "Y",
				"DESCRIPTION" => GetMessage("MAIN_EVERYONE_GROUP_DESC")
			),
			Array(
				"ID" => 3,
				"ACTIVE" => "Y",
				"C_SORT" => 3,
				"NAME" => GetMessage("MAIN_VOTE_RATING_GROUP_NAME"),
				"ANONYMOUS" => "N",
				"DESCRIPTION" => GetMessage("MAIN_VOTE_RATING_GROUP_DESC"),
				"STRING_ID" => "RATING_VOTE"
			),
			Array(
				"ID" => 4,
				"ACTIVE" => "Y",
				"C_SORT" => 4,
				"NAME" => GetMessage("MAIN_VOTE_AUTHORITY_GROUP_NAME"),
				"ANONYMOUS" => "N",
				"DESCRIPTION" => GetMessage("MAIN_VOTE_AUTHORITY_GROUP_DESC"),
				"STRING_ID" => "RATING_VOTE_AUTHORITY"
			)
		);

		foreach ($arGroups as $arGroup)
		{
			$rsGroup = CGroup::GetByID($arGroup["ID"]);
			if ($rsGroup->Fetch())
				continue;

			unset($arGroup["ID"]);

			$success = (bool)$group->Add($arGroup);
			if (!$success)
			{
				$APPLICATION->ThrowException($group->LAST_ERROR);
				return false;
			}
		}
		
		// add  ratings
		$arFields = Array(
			'ACTIVE' 	=> 'N',
			'NAME'		=> GetMessage("MAIN_RATING_NAME"),
			'ENTITY_ID' => 'USER',
			'CALCULATION_METHOD' => 'SUM',	
			'~CREATED' 	=> $DB->GetNowFunction(),	
			'CALCULATED'=> 'N',	
			'POSITION' 	=> 'Y',	
			'AUTHORITY'	=> 'N',	
			'CONFIGS' 	=> 'a:3:{s:4:"MAIN";a:2:{s:4:"VOTE";a:1:{s:4:"USER";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:5:"BONUS";a:2:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";}}}s:5:"FORUM";a:2:{s:4:"VOTE";a:2:{s:5:"TOPIC";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}s:4:"POST";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:9:{s:6:"ACTIVE";s:1:"Y";s:16:"TODAY_TOPIC_COEF";s:3:"0.4";s:15:"WEEK_TOPIC_COEF";s:3:"0.2";s:16:"MONTH_TOPIC_COEF";s:3:"0.1";s:14:"ALL_TOPIC_COEF";s:1:"0";s:15:"TODAY_POST_COEF";s:3:"0.2";s:14:"WEEK_POST_COEF";s:3:"0.1";s:15:"MONTH_POST_COEF";s:4:"0.05";s:13:"ALL_POST_COEF";s:1:"0";}}}s:4:"BLOG";a:2:{s:4:"VOTE";a:2:{s:4:"POST";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}s:7:"COMMENT";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:9:{s:6:"ACTIVE";s:1:"Y";s:15:"TODAY_POST_COEF";s:3:"0.4";s:14:"WEEK_POST_COEF";s:3:"0.2";s:15:"MONTH_POST_COEF";s:3:"0.1";s:13:"ALL_POST_COEF";s:1:"0";s:18:"TODAY_COMMENT_COEF";s:3:"0.2";s:17:"WEEK_COMMENT_COEF";s:3:"0.1";s:18:"MONTH_COMMENT_COEF";s:4:"0.05";s:16:"ALL_COMMENT_COEF";s:1:"0";}}}}'
		);
		$ratingId = $GLOBALS["DB"]->Add("b_rating", $arFields, array("CONFIGS"));
		
		// add  authority ratings
		$arFields = Array(
			'ACTIVE' 	=> 'N',
			'NAME'		=> GetMessage("MAIN_RATING_AUTHORITY_NAME"),
			'ENTITY_ID' => 'USER',
			'CALCULATION_METHOD' => 'SUM',	
			'~CREATED' 	=> $DB->GetNowFunction(),	
			'CALCULATED'=> 'N',	
			'POSITION' 	=> 'Y',	
			'AUTHORITY'	=> 'Y',	
			'CONFIGS' 	=> 'a:3:{s:4:"MAIN";a:2:{s:4:"VOTE";a:1:{s:4:"USER";a:3:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:1:"0";}}s:6:"RATING";a:1:{s:5:"BONUS";a:2:{s:6:"ACTIVE";s:1:"Y";s:11:"COEFFICIENT";s:1:"1";}}}s:5:"FORUM";a:2:{s:4:"VOTE";a:2:{s:5:"TOPIC";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}s:4:"POST";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:8:{s:16:"TODAY_TOPIC_COEF";s:2:"20";s:15:"WEEK_TOPIC_COEF";s:2:"10";s:16:"MONTH_TOPIC_COEF";s:1:"5";s:14:"ALL_TOPIC_COEF";s:1:"0";s:15:"TODAY_POST_COEF";s:3:"0.4";s:14:"WEEK_POST_COEF";s:3:"0.2";s:15:"MONTH_POST_COEF";s:3:"0.1";s:13:"ALL_POST_COEF";s:1:"0";}}}s:4:"BLOG";a:2:{s:4:"VOTE";a:2:{s:4:"POST";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}s:7:"COMMENT";a:2:{s:11:"COEFFICIENT";s:1:"1";s:5:"LIMIT";s:2:"30";}}s:6:"RATING";a:1:{s:8:"ACTIVITY";a:8:{s:15:"TODAY_POST_COEF";s:3:"0.4";s:14:"WEEK_POST_COEF";s:3:"0.2";s:15:"MONTH_POST_COEF";s:3:"0.1";s:13:"ALL_POST_COEF";s:1:"0";s:18:"TODAY_COMMENT_COEF";s:3:"0.2";s:17:"WEEK_COMMENT_COEF";s:3:"0.1";s:18:"MONTH_COMMENT_COEF";s:4:"0.05";s:16:"ALL_COMMENT_COEF";s:1:"0";}}}}'
		);
		$ratingId = $DB->Add("b_rating", $arFields, array("CONFIGS"));
		
		// set default rating vote group config
		$rsGroup = $DB->Query("SELECT * FROM b_group WHERE STRING_ID='RATING_VOTE'", true);
		if ($arGroup = $rsGroup->Fetch())
		{
			$arVoteGroup[] = array(
				'GROUP_ID' => 1,
				'TYPE' => "'R'"
			);
			$arVoteGroup[] = array(
				'GROUP_ID' => $arGroup['ID'],
				'TYPE' => "'R'"
			);
			foreach($arVoteGroup as $key => $arField)
				$DB->Insert("b_rating_vote_group", $arField);
			
			$arFields = Array(
				'ACTIVE' => 'N',
				'NAME' => GetMessage("MAIN_RULE_ADD_GROUP_RATING_NAME"),
				'ENTITY_TYPE_ID'	=> 'USER',
				'CONDITION_NAME' 	=> 'AUTHORITY',
				'CONDITION_CLASS' 	=> 'CRatingRulesMain',
				'CONDITION_METHOD' 	=> 'ratingCheck',
				'CONDITION_CONFIG'	=> Array(
					'AUTHORITY' 	=> Array(
						'RATING_CONDITION' => 1,
						'RATING_VALUE' => 1
					),
				),
				'ACTION_NAME' 		=> 'ADD_TO_GROUP',
				'ACTION_CONFIG'	  	=> Array(
					'ADD_TO_GROUP'	=> Array(
						'GROUP_ID'	=> $newGroupId
					),
				),
				'ACTIVATE' 			=> 'N',
				'ACTIVATE_CLASS' 	=> 'CRatingRulesMain',
				'ACTIVATE_METHOD' 	=> 'addToGroup',
				'DEACTIVATE'		=> 'N',
				'DEACTIVATE_CLASS' 	=> 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'addToGroup',
				"~CREATED"			=> $DB->GetNowFunction(),
				"~LAST_MODIFIED"	=> $DB->GetNowFunction(),
			);
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, array("ACTION_CONFIG", "CONDITION_CONFIG"));
			
			$arFields = Array(
				'ACTIVE' 			=> 'N',
				'NAME' 				=> GetMessage("MAIN_RULE_REM_GROUP_RATING_NAME"),
				'ENTITY_TYPE_ID'	=> 'USER',
				'CONDITION_NAME'	=> 'AUTHORITY',
				'CONDITION_CLASS' 	=> 'CRatingRulesMain',
				'CONDITION_METHOD' 	=> 'ratingCheck',
				'CONDITION_CONFIG' 	=> Array(
					'AUTHORITY' 	=> Array(
						'RATING_CONDITION' => 2,
						'RATING_VALUE' => 1
					),
				),
				'ACTION_NAME' => 'REMOVE_FROM_GROUP',
				'ACTION_CONFIG' => Array(
					'REMOVE_FROM_GROUP' => Array(
						'GROUP_ID' => $newGroupId
					),
				),
				'ACTIVATE' 			=> 'N',
				'ACTIVATE_CLASS'	=> 'CRatingRulesMain',
				'ACTIVATE_METHOD' 	=> 'removeFromGroup',
				'DEACTIVATE' 		=> 'N',
				'DEACTIVATE_CLASS'  => 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'removeFromGroup',
				"~CREATED"			=> $DB->GetNowFunction(),
				"~LAST_MODIFIED"	=> $DB->GetNowFunction(),
			);
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, array("ACTION_CONFIG", "CONDITION_CONFIG"));
		}
		$rsGroup = $DB->Query("SELECT * FROM b_group WHERE STRING_ID='RATING_VOTE_AUTHORITY'", true);
		if ($arGroup = $rsGroup->Fetch())
		{
			$arVoteGroup[] = array(
				'GROUP_ID' => 1,
				'TYPE' => "'A'"
			);
			$arVoteGroup[] = array(
				'GROUP_ID' => $arGroup['ID'],
				'TYPE' => "'A'"
			);
			foreach($arVoteGroup as $key => $arField)
				$DB->Insert("b_rating_vote_group", $arField);
			
			$arFields = Array(
				'ACTIVE' => 'N',
				'NAME' => GetMessage("MAIN_RULE_ADD_GROUP_AUTHORITY_NAME"),
				'ENTITY_TYPE_ID'	=> 'USER',
				'CONDITION_NAME' 	=> 'AUTHORITY',
				'CONDITION_CLASS' 	=> 'CRatingRulesMain',
				'CONDITION_METHOD' 	=> 'ratingCheck',
				'CONDITION_CONFIG'	=> Array(
					'AUTHORITY' 	=> Array(
						'RATING_CONDITION' => 1,
						'RATING_VALUE' => 2
					),
				),
				'ACTION_NAME' 		=> 'ADD_TO_GROUP',
				'ACTION_CONFIG'	  	=> Array(
					'ADD_TO_GROUP'	=> Array(
						'GROUP_ID'	=> $newGroupId
					),
				),
				'ACTIVATE' 			=> 'N',
				'ACTIVATE_CLASS' 	=> 'CRatingRulesMain',
				'ACTIVATE_METHOD' 	=> 'addToGroup',
				'DEACTIVATE'		=> 'N',
				'DEACTIVATE_CLASS' 	=> 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'addToGroup',
				"~CREATED"			=> $DB->GetNowFunction(),
				"~LAST_MODIFIED"	=> $DB->GetNowFunction(),
			);
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, array("ACTION_CONFIG", "CONDITION_CONFIG"));
			
			$arFields = Array(
				'ACTIVE' 			=> 'N',
				'NAME' 				=> GetMessage("MAIN_RULE_REM_GROUP_AUTHORITY_NAME"),
				'ENTITY_TYPE_ID'	=> 'USER',
				'CONDITION_NAME'	=> 'AUTHORITY',
				'CONDITION_CLASS' 	=> 'CRatingRulesMain',
				'CONDITION_METHOD' 	=> 'ratingCheck',
				'CONDITION_CONFIG' 	=> Array(
					'AUTHORITY' 	=> Array(
						'RATING_CONDITION' => 2,
						'RATING_VALUE' => 2
					),
				),
				'ACTION_NAME' => 'REMOVE_FROM_GROUP',
				'ACTION_CONFIG' => Array(
					'REMOVE_FROM_GROUP' => Array(
						'GROUP_ID' => $newGroupId
					),
				),
				'ACTIVATE' 			=> 'N',
				'ACTIVATE_CLASS'	=> 'CRatingRulesMain',
				'ACTIVATE_METHOD' 	=> 'removeFromGroup',
				'DEACTIVATE' 		=> 'N',
				'DEACTIVATE_CLASS'  => 'CRatingRulesMain ',
				'DEACTIVATE_METHOD' => 'removeFromGroup',
				"~CREATED"			=> $DB->GetNowFunction(),
				"~LAST_MODIFIED"	=> $DB->GetNowFunction(),
			);
			$arFields['CONDITION_CONFIG'] = serialize($arFields['CONDITION_CONFIG']);
			$arFields['ACTION_CONFIG'] = serialize($arFields['ACTION_CONFIG']);
			$DB->Add("b_rating_rule", $arFields, array("ACTION_CONFIG", "CONDITION_CONFIG"));
		}
		
		// rating default config
		COption::SetOptionString("main", "rating_community_size", 1);	
		COption::SetOptionString("main", "rating_community_authority", round(1*3*1000, 4));
		COption::SetOptionString("main", "rating_vote_weight", 1000);	
		COption::SetOptionString("main", "rating_normalization", 1000);
		COption::SetOptionString("main", "rating_count_vote", 10);
		COption::SetOptionString("main", "rating_authority_weight_formula", 'Y');
		COption::SetOptionString("main", "rating_community_last_visit", 90);

		$arLanguages = Array(
			Array(
				"LID" => LANGUAGE_ID,
				"ACTIVE" => "Y",
				"SORT" => 1,
				"DEF" => "Y",
				"NAME" => GetMessage("MAIN_DEFAULT_LANGUAGE_NAME"),
				"FORMAT_DATE" => GetMessage("MAIN_DEFAULT_LANGUAGE_FORMAT_DATE"),
				"FORMAT_DATETIME" => GetMessage("MAIN_DEFAULT_LANGUAGE_FORMAT_DATETIME"),
				"CHARSET" => (defined("BX_UTF") ? "UTF-8" : GetMessage("MAIN_DEFAULT_LANGUAGE_FORMAT_CHARSET"))
			)
		);

		if (LANGUAGE_ID <> "en")
			$arLanguages[] = Array(
				"LID" => "en",
				"ACTIVE" => "Y",
				"SORT" => 2,
				"DEF" => "N",
				"NAME" => "English",
				"FORMAT_DATE" => "MM/DD/YYYY",
				"FORMAT_DATETIME" => "MM/DD/YYYY HH:MI:SS",
				"CHARSET" => (defined("BX_UTF") ? "UTF-8" : "iso-8859-1")
			);

		$lang = new CLanguage;
		foreach ($arLanguages as $arLanguage)
		{
			$rsLang = CLanguage::GetByID($arLanguage["LID"]);
			if ($rsLang->Fetch())
				continue;

			$success = (bool)$lang->Add($arLanguage);
			if (!$success)
			{
				$APPLICATION->ThrowException($lang->LAST_ERROR);
				return false;
			}
		}

		$arSite = Array(
			"LID" => "s1",
			"ACTIVE" => "Y",
			"SORT" => 1,
			"DEF" => "Y",
			"NAME" => GetMessage("MAIN_DEFAULT_SITE_NAME"),
			"DIR" => "/",
			"FORMAT_DATE" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_DATE"),
			"FORMAT_DATETIME" => GetMessage("MAIN_DEFAULT_SITE_FORMAT_DATETIME"),
			"CHARSET" =>  (defined("BX_UTF") ? "UTF-8" : GetMessage("MAIN_DEFAULT_SITE_FORMAT_CHARSET")),
			"LANGUAGE_ID" => LANGUAGE_ID,
		);

		$rsSites = CSite::GetByID($arSite["LID"]);
		if (!$rsSites->Fetch())
		{
			$site = new CSite;
			$success = (bool)$site->Add($arSite);
			if (!$success)
			{
				$APPLICATION->ThrowException($site->LAST_ERROR);
				return false;
			}
		}

		RegisterModule("main");
		RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'main', 'CIBlockPropertyUserID', 'GetUserTypeDescription', 100, '/modules/main/tools/prop_userid.php');
		RegisterModuleDependences('main','OnUserDelete','main', 'CFavorites','OnUserDelete', 100, "/modules/main/classes/".strtolower($GLOBALS["DB"]->type)."/favorites.php");
		RegisterModuleDependences('main','OnLanguageDelete','main', 'CFavorites','OnLanguageDelete', 100, "/modules/main/classes/".strtolower($GLOBALS["DB"]->type)."/favorites.php");
		RegisterModuleDependences('main','OnUserDelete','main', 'CUserOptions','OnUserDelete', 100, "/modules/main/classes/".strtolower($GLOBALS["DB"]->type)."/favorites.php");
		RegisterModuleDependences('main','OnChangeFile','main', 'CMain','OnChangeFileComponent');
		RegisterModuleDependences('main','OnUserTypeRightsCheck','main', 'CUser','UserTypeRightsCheck');
		RegisterModuleDependences('main','OnUserLogin', 'main', 'UpdateTools','CheckUpdates');
		RegisterModuleDependences('main','OnModuleUpdate', 'main', 'UpdateTools','SetUpdateResult');
		RegisterModuleDependences('main','OnUpdateCheck', 'main', 'UpdateTools','SetUpdateError');
		RegisterModuleDependences('main','OnPanelCreate', 'main', 'CUndo', 'CheckNotifyMessage');
		RegisterModuleDependences('main', 'OnAfterAddRating', 	 'main', 'CRatingsComponentsMain', 'OnAfterAddRating');
		RegisterModuleDependences('main', 'OnAfterUpdateRating', 'main', 'CRatingsComponentsMain', 'OnAfterUpdateRating');
		RegisterModuleDependences('main', 'OnSetRatingsConfigs', 'main', 'CRatingsComponentsMain', 'OnSetRatingConfigs');
		RegisterModuleDependences('main', 'OnGetRatingsConfigs', 'main', 'CRatingsComponentsMain', 'OnGetRatingConfigs');
		RegisterModuleDependences('main', 'OnGetRatingsObjects', 'main', 'CRatingsComponentsMain', 'OnGetRatingObject');
		RegisterModuleDependences('main', 'OnAfterAddRatingRule', 	 'main', 'CRatingRulesMain', 'OnAfterAddRatingRule');
		RegisterModuleDependences('main', 'OnAfterUpdateRatingRule', 'main', 'CRatingRulesMain', 'OnAfterUpdateRatingRule');
		RegisterModuleDependences('main', 'OnGetRatingRuleObjects',  'main', 'CRatingRulesMain', 'OnGetRatingRuleObjects');
		RegisterModuleDependences('main', 'OnGetRatingRuleConfigs',  'main', 'CRatingRulesMain', 'OnGetRatingRuleConfigs');
		RegisterModuleDependences('main', 'OnAfterUserRegister', 'main', 'CRatings', 'OnAfterUserRegister');
		RegisterModuleDependences('main', 'OnAfterUserSimpleRegister', 'main', 'CRatings', 'OnAfterUserRegister');
		RegisterModuleDependences('main', 'OnAfterUserAdd', 'main', 'CRatings', 'OnAfterUserRegister');

		COption::SetOptionString('main', 'auth_comp2', 'Y');
		COption::SetOptionString("main", "PARAM_MAX_SITES", "2");
		COption::SetOptionString("main", "PARAM_MAX_USERS", "0");
		COption::SetOptionString("main", "distributive6", "Y");
		COption::SetOptionString("main", "new_license7_sign", "Y");
		COption::SetOptionString("main", "GROUP_DEFAULT_TASK", "1");

		if (LANGUAGE_ID == "ru")
			COption::SetOptionString("main", "vendor", "1c_bitrix");
		else
			COption::SetOptionString("main", "vendor", "bitrix");

		COption::SetOptionString("main", "admin_lid", LANGUAGE_ID);
		COption::SetOptionString("main", "update_site", "www.bitrixsoft.com");
		COption::SetOptionString("main", "update_site_ns", "Y");

		CAgent::AddAgent("CEvent::CleanUpAgent();","main", "Y", 86400);
		CAgent::AddAgent("CUser::CleanUpHitAuthAgent();","main", "Y", 86400);
		CAgent::AddAgent("CCaptchaAgent::DeleteOldCaptcha(3600);","main", "N", 3600);
		CAgent::AddAgent("CUndo::CleanUpOld();", "main", "Y", 86400);
		
		return true;
	}

	function UnInstallDB()
	{
		global $DBType, $DBHost, $DBLogin, $DBPassword, $DBName, $APPLICATION;

		if (!is_object($APPLICATION))
			$APPLICATION = new CMain;

		$DB = new CDatabase;
		$DB->DebugToFile = false;
		//$DB->debug = true;
		define("DBPersistent", false);

		if (!$DB->Connect($DBHost, $DBName, $DBLogin, $DBPassword))
		{
			$APPLICATION->ThrowException(GetMessage("MAIN_INSTALL_DB_ERROR"));
			return false;
		}

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/".$DBType."/uninstall.sql");
		if ($errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		return true;
	}

	function InstallEvents()
	{
		$arEventTypes = Array();
		$langs = CLanguage::GetList(($b=""), ($o=""));
		while($language = $langs->Fetch())
		{
			$lid = $language["LID"];
			IncludeModuleLangFile(__FILE__, $lid);

			$arEventTypes[] = Array(
				"LID" => $lid,
				"EVENT_NAME" => "NEW_USER",
				"NAME" => GetMessage("MAIN_NEW_USER_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_NEW_USER_TYPE_DESC"),
				"SORT" => 1
			);
			$arEventTypes[] = Array(
				"LID" => $lid,
				"EVENT_NAME" => "USER_INFO",
				"NAME" => GetMessage("MAIN_USER_INFO_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INFO_TYPE_DESC"),
				"SORT" => 2
			);
			$arEventTypes[] = Array(
				"LID" => $lid,
				"EVENT_NAME" => "NEW_USER_CONFIRM",
				"NAME" => GetMessage("MAIN_NEW_USER_CONFIRM_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_NEW_USER_CONFIRM_TYPE_DESC"),
				"SORT" => 3
			);
			$arEventTypes[] = Array(
				"LID" => $lid,
				"EVENT_NAME" => "USER_PASS_REQUEST",
				"NAME" => GetMessage("MAIN_USER_PASS_REQUEST_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INFO_TYPE_DESC"),
				"SORT" => 4
			);
			$arEventTypes[] = Array(
				"LID" => $lid,
				"EVENT_NAME" => "USER_PASS_CHANGED",
				"NAME" => GetMessage("MAIN_USER_PASS_CHANGED_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INFO_TYPE_DESC"),
				"SORT" => 5
			);
			$arEventTypes[] = Array(
				"LID" => $lid,
				"EVENT_NAME" => "USER_INVITE",
				"NAME" => GetMessage("MAIN_USER_INVITE_TYPE_NAME"),
				"DESCRIPTION" => GetMessage("MAIN_USER_INVITE_TYPE_DESC"),
				"SORT" => 6
			);
			$arEventTypes[] = Array(
				"LID" => $lid,
				"EVENT_NAME" => "FEEDBACK_FORM",
				"NAME" => GetMessage("MF_EVENT_NAME"),
				"DESCRIPTION" => GetMessage("MF_EVENT_DESCRIPTION"),
				"SORT" => 7
			);
		}

		$type = new CEventType;
		foreach ($arEventTypes as $arEventType)
			$type->Add($arEventType);

		IncludeModuleLangFile(__FILE__);

		$arMessages = Array();
		$arMessages[] = Array(
			"EVENT_NAME" => "NEW_USER",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#DEFAULT_EMAIL_FROM#",
			"SUBJECT" => GetMessage("MAIN_NEW_USER_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_NEW_USER_EVENT_DESC")
		);
		$arMessages[] = Array(
			"EVENT_NAME" => "USER_INFO",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_INFO_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_INFO_EVENT_DESC")
		);
		$arMessages[] = Array(
			"EVENT_NAME" => "USER_PASS_REQUEST",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_PASS_REQUEST_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_PASS_REQUEST_EVENT_DESC")
		);
		$arMessages[] = Array(
			"EVENT_NAME" => "USER_PASS_CHANGED",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_PASS_CHANGED_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_PASS_CHANGED_EVENT_DESC")
		);
		$arMessages[] = Array(
			"EVENT_NAME" => "NEW_USER_CONFIRM",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_NEW_USER_CONFIRM_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_NEW_USER_CONFIRM_EVENT_DESC")
		);
		$arMessages[] = Array(
			"EVENT_NAME" => "USER_INVITE",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"SUBJECT" => GetMessage("MAIN_USER_INVITE_EVENT_NAME"),
			"MESSAGE" => GetMessage("MAIN_USER_INVITE_EVENT_DESC")
		);
		$arMessages[] = Array(
			"EVENT_NAME" => "FEEDBACK_FORM",
			"LID" => "s1",
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("MF_EVENT_SUBJECT"),
			"MESSAGE" => GetMessage("MF_EVENT_MESSAGE")
		);

		$message = new CEventMessage;
		foreach ($arMessages as $arMessage)
			$message->Add($arMessage);

		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix", true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/install/admin", $_SERVER['DOCUMENT_ROOT']."/bitrix/admin");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/sounds", $_SERVER["DOCUMENT_ROOT"]."/bitrix/sounds", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/components/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/gadgets/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix/gadgets/bitrix", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/image_uploader", $_SERVER["DOCUMENT_ROOT"]."/bitrix/image_uploader", true, true);

		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/bitrix/", $_SERVER["DOCUMENT_ROOT"]."/bitrix");
		DeleteDirFilesEx("/bitrix/js/");
		DeleteDirFilesEx("/bitrix/admin/");
		DeleteDirFilesEx("/bitrix/components/bitrix");
		DeleteDirFilesEx("/bitrix/gadgets/bitrix");
		DeleteDirFilesEx("/bitrix/templates/");
		DeleteDirFilesEx("/bitrix/tools/");
		DeleteDirFilesEx("/bitrix/themes/");
		DeleteDirFilesEx("/bitrix/images/");

		return true;
	}

	function DoInstall()
	{

	}

	function DoUninstall()
	{

	}
}
?>