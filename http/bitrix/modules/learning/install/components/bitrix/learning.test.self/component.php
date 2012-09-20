<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//Params
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));
$arParams["LESSON_ID"] = (isset($arParams["LESSON_ID"]) ? intval($arParams["LESSON_ID"]) : intval($_REQUEST["LESSON_ID"]));
$arParams["CHECK_PERMISSIONS"] = (isset($arParams["CHECK_PERMISSIONS"]) && $arParams["CHECK_PERMISSIONS"]=="N" ? "N" : "Y");

if($this->StartResultCache(false, $USER->GetGroups()))
{
	//Module
	if (!CModule::IncludeModule("learning"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_MODULE_NOT_FOUND"));
		return;
	}

	//Lesson
	$obcourse = CLesson::GetList(
		Array(),
		Array(
			"ID" => $arParams["LESSON_ID"],
			"COURSE_ID" => $arParams["COURSE_ID"],
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"]
		)
	);

	if (!$arLesson = $obcourse->GetNext())
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_LESSON_DENIED"));
		return;
	}

	//Images
	$arLesson["PREVIEW_PICTURE_ARRAY"] = CFile::GetFileArray($arLesson["PREVIEW_PICTURE"]);
	$arLesson["DETAIL_PICTURE_ARRAY"] = CFile::GetFileArray($arLesson["DETAIL_PICTURE"]);

	//arResult
	$arResult = Array(
		"LESSON" => $arLesson,
		"QUESTIONS" => Array(),
		"QUESTIONS_COUNT" => 0,
		"ERROR_MESSAGE" => ""
	);

	//Questions
	$obquestion = CLQuestion::GetList(
		$arOrder=Array("SORT" => "ASC", "ID" => "ASC"),
		$arFilter=Array("LESSON_ID" => $arParams["LESSON_ID"], "ACTIVE" => "Y", "SELF" => "Y")
	);

	while ($arQuestion = $obquestion->GetNext())
	{
		$obanswer = CLAnswer::GetList(
			Array("SORT" => "ASC"),
			Array("QUESTION_ID" => $arQuestion["ID"])
		);

		$arQuestion["FILE"] = CFile::GetFileArray($arQuestion["FILE_ID"]);
		$arQuestion["ANSWERS"] = Array();

		while($arAnswer = $obanswer->GetNext())
			$arQuestion["ANSWERS"][] = $arAnswer;

		if ($arQuestion["QUESTION_TYPE"] == "R")
		{
			$arQuestion["ANSWERS_ORIGINAL"] = $arQuestion["ANSWERS"];
			shuffle($arQuestion["ANSWERS"]);
		}

		$arResult["QUESTIONS"][] = $arQuestion;
	}

	$arResult["QUESTIONS_COUNT"] = count($arResult["QUESTIONS"]);

	//Errors
	if ($arResult["QUESTIONS_COUNT"] <= 0)
		$arResult["ERROR_MESSAGE"] = GetMessage("LEARNING_BAD_SELFTEST");

	unset($arLesson);
	unset($arQuestion);
	unset($arAnswer);

	$this->IncludeComponentTemplate();
}

//Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["LESSON"]["NAME"]);
?>