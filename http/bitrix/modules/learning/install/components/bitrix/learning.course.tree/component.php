<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/functions.php");

if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
	return;
}

//Params
$arParams["CHAPTER_DETAIL_TEMPLATE"] = (strlen($arParams["CHAPTER_DETAIL_TEMPLATE"]) > 0 ? $arParams["CHAPTER_DETAIL_TEMPLATE"]: "chapter.php?CHAPTER_ID=#CHAPTER_ID#");
$arParams["LESSON_DETAIL_TEMPLATE"] = (strlen($arParams["LESSON_DETAIL_TEMPLATE"]) > 0 ? $arParams["LESSON_DETAIL_TEMPLATE"] : "lesson.php?LESSON_ID=#LESSON_ID#");
$arParams["SELF_TEST_TEMPLATE"] = (strlen($arParams["SELF_TEST_TEMPLATE"]) > 0 ? $arParams["SELF_TEST_TEMPLATE"] : "self.php?LESSON_ID=#LESSON_ID#");
$arParams["TESTS_LIST_TEMPLATE"] = (strlen($arParams["TESTS_LIST_TEMPLATE"]) > 0 ? $arParams["TESTS_LIST_TEMPLATE"] :"course/test_list.php?COURSE_ID=#COURSE_ID#");
$arParams["TEST_DETAIL_TEMPLATE"] = (strlen($arParams["TEST_DETAIL_TEMPLATE"]) > 0 ? $arParams["TEST_DETAIL_TEMPLATE"] :"course/test.php?COURSE_ID=#COURSE_ID#&TEST_ID=#TEST_ID#");
$arParams["COURSE_DETAIL_TEMPLATE"] = (strlen($arParams["COURSE_DETAIL_TEMPLATE"]) > 0 ? $arParams["COURSE_DETAIL_TEMPLATE"] :"course/index.php?COURSE_ID=#COURSE_ID#");

//Check permissions
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));


$rsCourse = CCourse::GetList(
	Array(),
	Array(
		"ID" => $arParams["COURSE_ID"],
		"ACTIVE" => "Y",
		"ACTIVE_DATE" => "Y",
		"SITE_ID" => LANG,
		"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"]
	)
);


if (!$arCourse = $rsCourse->GetNext())
{
	ShowError(GetMessage("LEARNING_COURSE_DENIED"));
	return;
}

//arResult
$arResult = Array(
	"ITEMS" => Array(),
	"COURSE" => $arCourse,
);

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["COURSE"]["NAME"]);


$parent = &$this->GetParent();

//Course description item
$url = CComponentEngine::MakePathFromTemplate($arParams["COURSE_DETAIL_TEMPLATE"], Array("COURSE_ID" => $arParams["COURSE_ID"]));
$arResult["ITEMS"][] = Array(
	"NAME" => GetMessage("LEARNING_COURSE_DESCRIPTION"),
	"URL" => $url,
	"TYPE" => "CD",
	"SELECTED" => $parent->arResult["VARIABLES"]["INDEX"] == "Y",
	"DEPTH_LEVEL" => 1
);

//Get Course Content
$lessonCount = 0;
$lessonCurrent = 0;
$rsContent = CCourse::GetCourseContent($arParams["COURSE_ID"], Array());
while($arContent = $rsContent->GetNext())
{
	if($arContent["TYPE"]=="CH")
	{
		$itemURL = CComponentEngine::MakePathFromTemplate($arParams["CHAPTER_DETAIL_TEMPLATE"],
			Array("CHAPTER_ID" => $arContent["ID"],"COURSE_ID" => $arParams["COURSE_ID"])
		);

		$arContent["URL"] = htmlspecialchars($itemURL);
		$arContent["SELECTED"] = $parent->arResult["VARIABLES"]["CHAPTER_ID"] == $arContent["ID"];
		$arContent["CHAPTER_OPEN"] = $arContent["SELECTED"];
	}
	else
	{

		$itemURL = CComponentEngine::MakePathFromTemplate($arParams["LESSON_DETAIL_TEMPLATE"],
			Array("LESSON_ID" => $arContent["ID"],"COURSE_ID" => $arParams["COURSE_ID"])
		);

		/*$selftestURL = CComponentEngine::MakePathFromTemplate($arParams["SELF_TEST_TEMPLATE"],
			Array("LESSON_ID" => $arContent["ID"], "SELF_TEST_ID" => $arContent["ID"], "COURSE_ID" => $arParams["COURSE_ID"])
		);*/

		$arContent["URL"] = htmlspecialchars($itemURL);
		$arContent["SELECTED"] = $parent->arResult["VARIABLES"]["LESSON_ID"] == $arContent["ID"];//_IsItemSelected(Array($itemURL, $selftestURL));

		$lessonCount++;
	}

	if ($arContent["SELECTED"])
		$lessonCurrent = $lessonCount;

	$arResult["ITEMS"][] = $arContent;
}

//Page Properties
$APPLICATION->SetPageProperty("learning_course_name", $arResult["COURSE"]["NAME"]);
$APPLICATION->SetPageProperty("learning_lesson_count", $lessonCount);
$APPLICATION->SetPageProperty("learning_lesson_current", $lessonCurrent);

//Test list item
$url = CComponentEngine::MakePathFromTemplate($arParams["TESTS_LIST_TEMPLATE"], Array("COURSE_ID" => $arParams["COURSE_ID"]));
$arSelectedItems = Array($url);

$rsTest = CTest::GetList(array(), array("COURSE_ID"=>$arParams["COURSE_ID"], "ACTIVE" => "Y"));
$rsTest->NavStart(100);
while ($arTest = $rsTest->Fetch())
{
	$arSelectedItems[] = CComponentEngine::MakePathFromTemplate(
		$arParams["TEST_DETAIL_TEMPLATE"],
		Array("TEST_ID" => $arTest["ID"],"COURSE_ID" => $arParams["COURSE_ID"])
	);
}

$arResult["ITEMS"][] = Array(
	"NAME" => GetMessage('LEARNING_TEST_LIST')."&nbsp;(".$rsTest->SelectedRowsCount().")",
	"URL" => $url,
	"TYPE" => "TL",
	"SELECTED" => $parent->arResult["VARIABLES"]["TEST_LIST"] == "Y",//_IsItemSelected($arSelectedItems),
	"DEPTH_LEVEL" => 1,
);

unset($arContent);
unset($rsContent);

//Open chapters from Cookies
$arOpenChapters = Array();
if (array_key_exists("LEARN_MENU_".$arParams["COURSE_ID"],$_COOKIE))
	$arOpenChapters = explode(",", $_COOKIE["LEARN_MENU_".$arParams["COURSE_ID"]]);

//Chapter open if child selected
for ($itemIndex = 0, $size = count($arResult["ITEMS"]); $itemIndex < $size; $itemIndex++)
{
	if ($arResult["ITEMS"][$itemIndex]["TYPE"] != "CH" || $arResult["ITEMS"][$itemIndex]["SELECTED"] === true)
		continue;

	$arResult["ITEMS"][$itemIndex]["CHAPTER_OPEN"] = (
		in_array($arResult["ITEMS"][$itemIndex]["ID"], $arOpenChapters) || _IsInsideSelect($arResult["ITEMS"], ($itemIndex+1), $arResult["ITEMS"][$itemIndex]["DEPTH_LEVEL"])
	);
}


$this->IncludeComponentTemplate();
?>