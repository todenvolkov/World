<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class sitestore extends CModule
{
	var $MODULE_ID = "sitestore";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function sitestore()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("SCOM_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SCOM_INSTALL_DESCRIPTION");
	}


	function InstallDB($install_wizard = true)
	{
		global $DB, $DBType, $APPLICATION;

		RegisterModule("sitestore");
		RegisterModuleDependences("main", "OnBeforeProlog", "sitestore", "CSiteStore", "ShowPanel");

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		global $DB, $DBType, $APPLICATION;

		UnRegisterModuleDependences("main", "OnBeforeProlog", "sitestore", "CSiteStore", "ShowPanel"); 
		UnRegisterModule("sitestore");

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
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sitestore/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sitestore/install/wizards/bitrix/store.catalog", $_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix/store.catalog", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sitestore/install/wizards/bitrix/store.mobile", $_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix/store.mobile", true, true);
	
		return true;
	}

	function InstallPublic()
	{
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$this->InstallFiles();
		$this->InstallDB(false);
		$this->InstallEvents();
		$this->InstallPublic();

		$APPLICATION->IncludeAdminFile(GetMessage("SCOM_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sitestore/install/step.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallEvents();
		$APPLICATION->IncludeAdminFile(GetMessage("SCOM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sitestore/install/unstep.php");
	}
}
?>