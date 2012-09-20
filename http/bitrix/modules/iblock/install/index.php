<?
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
IncludeModuleLangFile($strPath2Lang."/install.php");

Class iblock extends CModule
{
	var $MODULE_ID = "iblock";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	var $errors;

	function iblock()
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
			$this->MODULE_VERSION = IBLOCK_VERSION;
			$this->MODULE_VERSION_DATE = IBLOCK_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("IBLOCK_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("IBLOCK_INSTALL_DESCRIPTION");
	}


	function InstallDB()
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!$DB->Query("SELECT 'x' FROM b_iblock_type", true))
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/iblock/install/db/".$DBType."/install.sql");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		RegisterModule("iblock");
		RegisterModuleDependences("main", "OnGroupDelete", "iblock", "CIBlock", "OnGroupDelete");
		RegisterModuleDependences("main", "OnBeforeLangDelete", "iblock", "CIBlock", "OnBeforeLangDelete");
		RegisterModuleDependences("main", "OnLangDelete", "iblock", "CIBlock", "OnLangDelete");
		RegisterModuleDependences("main", "OnUserTypeRightsCheck", "iblock", "CIBlockSection", "UserTypeRightsCheck");
		RegisterModuleDependences("search", "OnReindex", "iblock", "CIBlock", "OnSearchReindex");
		RegisterModuleDependences("search", "OnSearchGetURL", "iblock", "CIBlock", "OnSearchGetURL");
		RegisterModuleDependences("main", "OnEventLogGetAuditTypes", "iblock", "CIBlock", "GetAuditTypes");

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;
		$arSQLErrors = array();

		if(CModule::IncludeModule("search"))
			CSearch::DeleteIndex("iblock");
		if(!CModule::IncludeModule("iblock"))
			return false;

		$arSql = $arErr = array();
		if(!array_key_exists("savedata", $arParams) || ($arParams["savedata"] != "Y"))
		{
			$rsIBlock = CIBlock::GetList(array("ID"=>"ASC"), array(), false);
			while ($arIBlock = $rsIBlock->Fetch())
			{
				if($arIBlock["VERSION"] == 2)
				{
					$arSql[] = "DROP TABLE b_iblock_element_prop_s".$arIBlock["ID"];
					$arSql[] = "DROP TABLE b_iblock_element_prop_m".$arIBlock["ID"];
					if($DBType=="oracle")
						$arSql[] = "DROP SEQUENCE sq_b_iblock_element_prop_m".$arIBlock["ID"];
				}
				$GLOBALS["USER_FIELD_MANAGER"]->OnEntityDelete("IBLOCK_".$arIBlock["ID"]."._SECTION");
			}

			foreach($arSql as $strSql)
			{
				if(!$DB->Query($strSql, true))
					$arSQLErrors[] = "<hr><pre>Query:\n".$strSql."\n\nError:\n<font color=red>".$DB->db_Error."</font></pre>";
			}

			$db_res = $DB->Query("SELECT ID FROM b_file WHERE MODULE_ID = 'iblock'");
			while($arRes = $db_res->Fetch())
				CFile::Delete($arRes["ID"]);

			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/iblock/install/db/".$DBType."/uninstall.sql");
		}

		if(is_array($this->errors))
			$arSQLErrors = array_merge($arSQLErrors, $this->errors);

		if(!empty($arSQLErrors))
		{
			$this->errors = $arSQLErrors;
			$APPLICATION->ThrowException(implode("", $arSQLErrors));
			return false;
		}

		UnRegisterModuleDependences("main", "OnGroupDelete", "iblock", "CIBlock", "OnGroupDelete");
		UnRegisterModuleDependences("main", "OnBeforeLangDelete", "iblock", "CIBlock", "OnBeforeLangDelete");
		UnRegisterModuleDependences("main", "OnLangDelete", "iblock", "CIBlock", "OnLangDelete");
		UnRegisterModuleDependences("main", "OnUserTypeRightsCheck", "iblock", "CIBlockSection", "UserTypeRightsCheck");
		UnRegisterModuleDependences("search", "OnReindex", "iblock", "CIBlock", "OnSearchReindex");
		UnRegisterModuleDependences("search", "OnSearchGetURL", "iblock", "CIBlock", "OnSearchGetURL");
		UnRegisterModuleDependences("main", "OnEventLogGetAuditTypes", "iblock", "CIBlock", "GetAuditTypes");

		UnRegisterModule("iblock");

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/install/admin', $_SERVER['DOCUMENT_ROOT']."/bitrix/admin");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/iblock/", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/images/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/iblock", true, true);
			if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/public/rss.php"))
				@copy($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/public/rss.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/rss.php");
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			DeleteDirFilesEx("/bitrix/images/iblock/");//images
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/public/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/");
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");//css
			DeleteDirFilesEx("/bitrix/themes/.default/icons/iblock/");//icons
			DeleteDirFilesEx("/bitrix/js/iblock/");//javascript
		}
		return true;
	}


	function DoInstall()
	{
		global $APPLICATION, $step, $obModule;
		$step = IntVal($step);
		if($step<2)
			$APPLICATION->IncludeAdminFile(GetMessage("IBLOCK_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/step1.php");
		elseif($step==2)
		{
			if($this->InstallDB())
			{
				$this->InstallFiles();
			}
			$obModule = $this;
			$APPLICATION->IncludeAdminFile(GetMessage("IBLOCK_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/step2.php");
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step, $obModule;
		$step = IntVal($step);
		if($step<2)
			$APPLICATION->IncludeAdminFile(GetMessage("IBLOCK_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/unstep1.php");
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$GLOBALS["CACHE_MANAGER"]->CleanAll();
			$GLOBALS["stackCacheManager"]->CleanAll();
			$this->UnInstallFiles();
			$obModule = $this;
			$APPLICATION->IncludeAdminFile(GetMessage("IBLOCK_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/install/unstep2.php");
		}
	}
}
?>