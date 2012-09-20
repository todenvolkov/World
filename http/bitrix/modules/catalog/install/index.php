<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install.php"));

Class catalog extends CModule
{
	var $MODULE_ID = "catalog";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function catalog()
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
			$this->MODULE_VERSION = CATALOG_VERSION;
			$this->MODULE_VERSION_DATE = CATALOG_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("CATALOG_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("CATALOG_INSTALL_DESCRIPTION");
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step, $errors;
		
		$step = IntVal($step);
		$errors = false;
		
		if(!IsModuleInstalled("currency"))
			$errors = GetMessage("CATALOG_UNINS_CURRENCY");
		elseif(!IsModuleInstalled("iblock"))
			$errors = GetMessage("CATALOG_UNINS_IBLOCK");
		else
		{
			$this->InstallFiles();
			$this->InstallDB();
			$this->InstallEvents();
		}
		
		$APPLICATION->IncludeAdminFile(GetMessage("CATALOG_INSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/catalog/install/step1.php");
	}
	
	function InstallFiles()
	{
		global $DOCUMENT_ROOT;
	
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/admin", $DOCUMENT_ROOT."/bitrix/admin");

		$ToDir = $DOCUMENT_ROOT."/bitrix/images/catalog";
		
		CheckDirPath($ToDir);
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/images", $ToDir);
		
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/themes/", $DOCUMENT_ROOT."/bitrix/themes", true, true);
		
		$strDefTemplateDir = $DOCUMENT_ROOT."/bitrix/templates/.default/catalog";
		
		CheckDirPath($strDefTemplateDir."/images/");
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/templates/catalog/images", $strDefTemplateDir."/images", False, True, False);

		$ToDir = $DOCUMENT_ROOT."/bitrix/php_interface/include";
		$ToDir1 = $DOCUMENT_ROOT."/bitrix/tools";
		
		CheckDirPath($ToDir."/catalog_export/");
		CheckDirPath($ToDir1."/catalog_export/");
		CheckDirPath($ToDir."/catalog_import/");
		
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/public"."/catalog_export/", $ToDir."/catalog_export/");
		
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/public"."/catalog_export/froogle_util.php", $ToDir1."/catalog_export/froogle_util.php");
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/public"."/catalog_export/yandex_util.php", $ToDir1."/catalog_export/yandex_util.php");
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/public"."/catalog_export/yandex_detail.php", $ToDir1."/catalog_export/yandex_detail.php");
		
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/public"."/catalog_import/", $ToDir."/catalog_import/");
	
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/images", $DOCUMENT_ROOT."/bitrix/images/catalog", False, true);
		CopyDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/components", $DOCUMENT_ROOT."/bitrix/components", True, True);

		return true;
	}
	
	function InstallDB()
	{
		global $APPLICATION, $DOCUMENT_ROOT, $DB, $errors;

		
		if(!$DB->Query("SELECT 'x' FROM b_catalog_group", true))
			$errors = $DB->RunSQLBatch($DOCUMENT_ROOT."/bitrix/modules/catalog/install/db/".strtolower($DB->type)."/install.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors)); 
			return false;
		}
		
		RegisterModule("catalog");

		RegisterModuleDependences("iblock", "OnIBlockDelete", "catalog", "CCatalog", "OnIBlockDelete");
		RegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CCatalogProduct", "OnIBlockElementDelete");
		RegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CPrice", "OnIBlockElementDelete");
		RegisterModuleDependences("currency", "OnCurrencyDelete", "catalog", "CPrice", "OnCurrencyDelete");
		RegisterModuleDependences("main", "OnGroupDelete", "catalog", "CCatalogProductGroups", "OnGroupDelete");
		RegisterModuleDependences("iblock", "OnAfterIBlockElementUpdate", "catalog", "CCatalogProduct", "OnAfterIBlockElementUpdate");
		RegisterModuleDependences("currency", "OnModuleUnInstall", "catalog", "", "CurrencyModuleUnInstallCatalog");

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/install/tasks/install.php");
		
		return true;
	}
	
	function InstallEvents()
	{
		return true;
	}

	function DoUnInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step, $errors;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("CATALOG_INSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/catalog/install/unstep1.php");
		}
		elseif($step==2)
		{
			$errors = false;
			
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			
			$this->UnInstallFiles(array(
				"savedata" => $_REQUEST["savedata"],
			));
			
			$APPLICATION->IncludeAdminFile(GetMessage("CATALOG_INSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/catalog/install/unstep2.php");
		}
	}
	
	function UnInstallFiles()
	{
		global $DOCUMENT_ROOT;
	
		DeleteDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/admin", $DOCUMENT_ROOT."/bitrix/admin");
		DeleteDirFiles($DOCUMENT_ROOT."/bitrix/modules/catalog/install/themes/.default/", $DOCUMENT_ROOT."/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/catalog/");//icons
		DeleteDirFilesEx("/bitrix/images/catalog/");//images
		
		return true;
	}
	
	function UnInstallDB($arParams = array())
	{
		global $APPLICATION, $DOCUMENT_ROOT, $DB, $errors;
		
		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$errors = $DB->RunSQLBatch($DOCUMENT_ROOT."/bitrix/modules/catalog/install/db/".strtolower($DB->type)."/uninstall.sql");
			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors)); 
				return false;
			}
		}
		
		UnRegisterModuleDependences("iblock", "OnIBlockDelete", "catalog", "CCatalog", "OnIBlockDelete");
		UnRegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CProduct", "OnIBlockElementDelete");
		UnRegisterModuleDependences("iblock", "OnIBlockElementDelete", "catalog", "CPrice", "OnIBlockElementDelete");
		UnRegisterModuleDependences("currency", "OnCurrencyDelete", "catalog", "CPrice", "OnCurrencyDelete");
		UnRegisterModuleDependences("iblock", "OnAfterIBlockElementUpdate", "catalog", "CCatalogProduct", "OnAfterIBlockElementUpdate");
		UnRegisterModuleDependences("currency", "OnModuleUnInstall", "catalog", "", "CurrencyModuleUnInstallCatalog");
		
		COption::RemoveOption("catalog");
		UnRegisterModule("catalog");
		
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/install/tasks/uninstall.php");
		
		return true;
	}
	
	function UnInstallEvents()
	{
		return true;
	}
}
?>