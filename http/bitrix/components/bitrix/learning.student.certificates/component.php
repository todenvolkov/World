<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//Authorized?
if (!$USER->IsAuthorized())
	$APPLICATION->AuthForm(GetMessage("LEARNING_NO_AUTHORIZE"));

//Module
if (!CModule::IncludeModule("learning"))
{
	ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
	return;
}

//Params
$arParams["TESTS_LIST_TEMPLATE"] = 
(
	strlen($arParams["TESTS_LIST_TEMPLATE"]) > 0 ?
	htmlspecialchars($arParams["TESTS_LIST_TEMPLATE"]) :
	"course/test_list.php?COURSE_ID=#COURSE_ID#"
);

$arParams["COURSE_DETAIL_TEMPLATE"] = 
(
	strlen($arParams["COURSE_DETAIL_TEMPLATE"]) > 0 ? 
	htmlspecialchars($arParams["COURSE_DETAIL_TEMPLATE"]):
	"course/index.php?COURSE_ID=#COURSE_ID#"
);


$arResult = Array(
	"COURSES" => Array(),
	"CERTIFICATES" => Array(),
);


//Certificates
$rsCertificate = CCertification::GetList(
	Array("ID"=>"DESC"),
	Array(
		"STUDENT_ID"=>intval($USER->GetID()),
		 "ACTIVE" => "Y"
	)
);

while ($arCertificate = $rsCertificate->GetNext())
	$arResult["CERTIFICATES"][$arCertificate["COURSE_ID"]] = $arCertificate;


//Courses
$rsCourse = CCourse::GetList(
	Array("SORT" => "ASC"), 
	Array(
		"ACTIVE" => "Y", 
		"ACTIVE_DATE" => "Y", 
		"SITE_ID" => LANG
	)
);

while ($arCourse = $rsCourse->GetNext())
{
	//Test list Url
	$arCourse["TESTS_LIST_URL"] = CComponentEngine::MakePathFromTemplate($arParams["TESTS_LIST_TEMPLATE"],Array("COURSE_ID" => $arCourse["ID"]));
	//Course Url
	$arCourse["COURSE_DETAIL_URL"] = CComponentEngine::MakePathFromTemplate($arParams["COURSE_DETAIL_TEMPLATE"], Array("COURSE_ID" => $arCourse["ID"]));

	$arCourse["COMPLETED"] = (array_key_exists($arCourse["ID"], $arResult["CERTIFICATES"]));

	$arResult["COURSES"][] = $arCourse;
}

unset($arCertificate);
unset($arCourse);

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("LEARNING_CERTIFICATES_TITLE"));

$this->IncludeComponentTemplate();
?>