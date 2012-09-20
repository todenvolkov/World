<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));


Class learning extends CModule
{
	var $MODULE_ID = "learning";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $errors;

	function learning()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = LEARNING_VERSION;
			$this->MODULE_VERSION_DATE = LEARNING_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("LEARNING_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("LEARNING_MODULE_DESC");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_learn_course WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/db/".strtolower($DB->type)."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			RegisterModule("learning");
			RegisterModuleDependences("main", "OnGroupDelete", "learning", "CCourse", "OnGroupDelete");
			RegisterModuleDependences("main", "OnBeforeLangDelete", "learning", "CCourse", "OnBeforeLangDelete");
			RegisterModuleDependences("main", "OnUserDelete", "learning", "CCourse", "OnUserDelete");
			RegisterModuleDependences("main", "OnSiteDelete", "learning", "CSitePath", "DeleteBySiteID");
			RegisterModuleDependences("search", "OnReindex", "learning", "CCourse", "OnSearchReindex");

			if ($DB->Query("SELECT 'x' FROM b_learn_site_path WHERE 1=0", true))
			{
				$sites = CLang::GetList($by, $order, Array("ACTIVE"=>"Y"));
				while($site = $sites->Fetch())
				{
					$path = "/learning/";
					if($_REQUEST["copy_".$site["LID"]] == "Y" && !empty($_REQUEST["path_".$site["LID"]]))
					{
						$path = $_REQUEST["path_".$site["LID"]];
					}

					$DB->Query(
						"INSERT INTO b_learn_site_path(ID, SITE_ID, PATH,TYPE) ".
						"VALUES".
						"(NULL , '".$site["LID"]."', '".$path."course/index.php?COURSE_ID=#COURSE_ID#&INDEX=Y', 'C'),".
						"(NULL , '".$site["LID"]."', '".$path."course/index.php?COURSE_ID=#COURSE_ID#&CHAPTER_ID=#CHAPTER_ID#', 'H'),".
						"(NULL , '".$site["LID"]."', '".$path."course/index.php?COURSE_ID=#COURSE_ID#&LESSON_ID=#LESSON_ID#', 'L')"
					, true);
				}
			}

			return true;
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/db/".strtolower($DB->type)."/uninstall.sql");
		}

		//delete agents
		CAgent::RemoveModuleAgents("learning");

		if (CModule::IncludeModule("search"))
			CSearch::DeleteIndex("learning");

		UnRegisterModuleDependences("search", "OnReindex", "learning", "CCourse", "OnSearchReindex");
		UnRegisterModuleDependences("main", "OnGroupDelete", "learning", "CCourse", "OnGroupDelete");
		UnRegisterModuleDependences("main", "OnBeforeLangDelete", "learning", "CCourse", "OnBeforeLangDelete");
		UnRegisterModuleDependences("main", "OnUserDelete", "learning", "CCourse", "OnUserDelete");

		UnRegisterModule("learning");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;
	}

	function InstallEvents()
	{

		global $DB;
		$sIn = "'NEW_LEARNING_TEXT_ANSWER'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/events/set_events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/events/del_events.php");
		return true;
	}

	function InstallFiles($arParams = array())
	{
		global $DB;

		//Admin files
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", false);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/images/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/learning", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/public/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		//Theme
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", True, True);

		//copy public scripts
		$arSITE_ID = Array();
		$sites = CLang::GetList($by, $order, Array("ACTIVE"=>"Y"));
		while($site = $sites->Fetch())
		{
			if($_REQUEST["copy_".$site["LID"]] == "Y" && !empty($_REQUEST["path_".$site["LID"]]))
			{
				$arSITE_ID[] = $site["LID"];
				$DOC_ROOT = (strlen($site["DOC_ROOT"])<=0) ? $_SERVER["DOCUMENT_ROOT"] : $site["DOC_ROOT"];

				$ldir = $site['LANGUAGE_ID'] == 'ru' ? 'ru' : 'en';

				CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/public/site/$ldir/", $DOC_ROOT.$_REQUEST["path_".$site["LID"]], true,true);
			}
		}

		if(!empty($arSITE_ID))
		{
			if (strlen($_REQUEST["template_id"])<=0)
				$_REQUEST["template_id"] = "learning";

			//Copy Template
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/public/template/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".$_REQUEST["template_id"]."/", true, true);

			foreach($arSITE_ID as $SITE_ID)
			{
				$path = $_REQUEST["path_".$SITE_ID];
				if (strlen($path)<=0)
					continue;

				if(substr($path,-1,1)!="/")
					$path .= "/";

				$cond = "CSite::InDir('".$path."course/')";

				$DB->Query(
				"INSERT INTO b_site_template(SITE_ID, ".CMain::__GetConditionFName().", SORT, TEMPLATE) ".
				"VALUES('".$DB->ForSQL($SITE_ID)."', '".$DB->ForSQL($cond, 255)."', '100', '".$DB->ForSQL(trim($_REQUEST["template_id"]), 255)."')", true);
			}
		}

		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/learning/");//icons
		DeleteDirFilesEx("/bitrix/images/learning/");//images
		DeleteDirFilesEx("/bitrix/js/learning/");//scripts
		return true;
	}

	function DoInstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("LEARNING_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/step1.php");
		}
		elseif($step==2)
		{
			$this->InstallFiles();
			$this->InstallDB();
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("LEARNING_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("LEARNING_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/unstep1.php");
		}
		elseif($step == 2)
		{
			$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("LEARNING_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/unstep2.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D", "W"),
			"reference" => array(
					"[D] ".GetMessage("LEARNING_PERM_D"),
					"[W] ".GetMessage("LEARNING_PERM_W")
				)
			);
		return $arr;
	}
}
?>