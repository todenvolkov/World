<?
/*patchlimitationmutatormark1*/
CModule::AddAutoloadClasses(
	"fileman",
	array(
		"CLightHTMLEditor" => "classes/general/light_editor.php",
		"CEditorUtils" => "classes/general/editor_utils.php",
		"CMedialib" => "classes/general/medialib.php",
		"CMedialibTabControl" => "classes/general/medialib.php"
	)
);

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/lang.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin_tools.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/fileman.php");
include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$GLOBALS["DBType"]."/favorites.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/properties.php");
/*patchlimitationmutatormark2*/
?>