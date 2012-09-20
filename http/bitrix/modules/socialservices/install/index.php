<?
global $MESS;
include(GetLangFileName(substr(__FILE__, 0, -18)."/lang/", "/install/index.php"));

class socialservices extends CModule
{
	var $MODULE_ID = "socialservices";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;

	function socialservices()
	{
		$arModuleVersion = array();

		include(substr(__FILE__, 0,  -10)."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("socialservices_install_name");
		$this->MODULE_DESCRIPTION = GetMessage("socialservices_install_desc");
	}

	function InstallDB($arParams = array())
	{
		RegisterModule("socialservices");

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		UnRegisterModule("socialservices");

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

	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialservices/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images", true, true);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFilesEx("/bitrix/js/socialservices/");
		DeleteDirFilesEx("/bitrix/images/socialservices/");
		return true;
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->InstallDB();
		$this->InstallFiles();
		$APPLICATION->IncludeAdminFile(GetMessage("socialservices_install_title_inst"), $DOCUMENT_ROOT."/bitrix/modules/socialservices/install/step.php");
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->UnInstallFiles();
		$this->UnInstallDB();
		$APPLICATION->IncludeAdminFile(GetMessage("socialservices_install_title_unitst"), $DOCUMENT_ROOT."/bitrix/modules/socialservices/install/unstep.php");
	}
}
?>