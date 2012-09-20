<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");
IncludeModuleLangFile(__FILE__);

$COURSE_ID = intval($COURSE_ID);
$course = CCourse::GetByID($COURSE_ID);

if($arCourse = $course->Fetch())
	$bBadCourse=(CCourse::GetPermission($COURSE_ID)<"W");
else
	$bBadCourse = true;


if($bBadCourse)
{
	$APPLICATION->SetTitle(GetMessage('LEARNING_COURSES'));
	if($_REQUEST["mode"] == "list")
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	else
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aContext = array(
		array(
			"ICON" => "btn_list",
			"TEXT"=>GetMessage("LEARNING_BACK_TO_ADMIN"),
			"LINK"=>"learn_course_admin.php?lang=".LANG.GetFilterParams("filter_"),
			"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
		),
	);
	$context = new CAdminContextMenu($aContext);

	if (!$arCourse)
	{
		$context->Show();
		CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_COURSE"));
	}
	else
	{
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}

	if($_REQUEST["mode"] == "list")
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
	else
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}





$APPLICATION->SetTitle($arCourse["NAME"]);
if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminPage->ShowSectionIndex("menu_learning_course_".$COURSE_ID, "learning");

if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>