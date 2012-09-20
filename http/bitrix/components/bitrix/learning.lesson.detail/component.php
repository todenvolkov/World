<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//Params
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));
$arParams["LESSON_ID"] = (isset($arParams["LESSON_ID"]) && intval($arParams["LESSON_ID"]) > 0 ? intval($arParams["LESSON_ID"]) : intval($_REQUEST["LESSON_ID"]));
$arParams["SELF_TEST_TEMPLATE"] = (strlen($arParams["SELF_TEST_TEMPLATE"]) > 0 ? htmlspecialchars($arParams["SELF_TEST_TEMPLATE"]) : "self.php?SELF_TEST_ID=#SELF_TEST_ID#");
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
	$rsLesson = CLesson::GetList(
		Array(),
		Array(
			"ID" => $arParams["LESSON_ID"],
			"COURSE_ID" => $arParams["COURSE_ID"],
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"]
		)
	);

	if (!$arLesson = $rsLesson->GetNext())
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_LESSON_DENIED"));
		return;
	}

	//Images
	$arLesson["PREVIEW_PICTURE_ARRAY"] = CFile::GetFileArray($arLesson["PREVIEW_PICTURE"]);
	$arLesson["DETAIL_PICTURE_ARRAY"] = CFile::GetFileArray($arLesson["DETAIL_PICTURE"]);

	//Self test page URL
	$arLesson["SELF_TEST_URL"] = CComponentEngine::MakePathFromTemplate(
		$arParams["SELF_TEST_TEMPLATE"],
		Array(
			"LESSON_ID" => $arParams["LESSON_ID"],
			"SELF_TEST_ID" => $arParams["LESSON_ID"],
			"COURSE_ID" => $arLesson["COURSE_ID"],
		)
	);

	//Self test exists?
	$rsQuestion = CLQuestion::GetList(
		Array(),
		Array(
			"LESSON_ID" => $arParams["LESSON_ID"],
			"ACTIVE" => "Y",
			"SELF" => "Y",
		)
	);

	$arLesson["SELF_TEST_EXISTS"] = (bool)($rsQuestion->Fetch());
	$urlInfo = parse_url($arLesson["LAUNCH"]);
	$path = $_SERVER["DOCUMENT_ROOT"].$urlInfo["path"];
	if ($arLesson["DETAIL_TEXT_TYPE"] == "file" && !file_exists($path))
	{
		$arLesson["LAUNCH"] = "";
	}

	$arResult = Array(
		"LESSON" => $arLesson
	);

	unset($arLesson);
	unset($rsLesson);
	unset($rsQuestion);

	$APPLICATION->AddHeadScript('/bitrix/js/learning/scorm.js');
	$this->IncludeComponentTemplate();

}

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["LESSON"]["NAME"]);

if ($APPLICATION->GetUserRight("learning") == "W" || $USER->IsAdmin())
{
	$deleteReturnUrl = "";
	if ($parent = $this->GetParent())
	{
		if ($arResult["LESSON"]["CHAPTER_ID"])
		{
			$deleteReturnUrl = CComponentEngine::MakePathFromTemplate($parent->arResult["URL_TEMPLATES"]["chapter.detail"], Array("CHAPTER_ID" => $arResult["LESSON"]["CHAPTER_ID"],"COURSE_ID" => $arResult["LESSON"]["COURSE_ID"]));
		}
		else
		{
			$deleteReturnUrl = CComponentEngine::MakePathFromTemplate($parent->arResult["URL_TEMPLATES"]["course.detail"], Array("COURSE_ID" => $arResult["LESSON"]["COURSE_ID"]));
		}
	}

	$arAreaButtons = array(
		array(
			"TEXT" => GetMessage("LEARNING_COURSES_LESSON_EDIT"),
			"TITLE" => GetMessage("LEARNING_COURSES_LESSON_EDIT"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_lesson_edit.php?ID=".$arParams["LESSON_ID"]."lang=".LANGUAGE_ID."&COURSE_ID=".$arParams["COURSE_ID"]."&CHAPTER_ID=".($arResult["LESSON"]["CHAPTER_ID"] ? $arResult["LESSON"]["CHAPTER_ID"] : 0)."&bxpublic=Y&from_module=learning",
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-edit-icon",
			"ID" => "bx-context-toolbar-edit-lesson",
		),

		array(
			"TEXT" => GetMessage("LEARNING_COURSES_LESSON_DELETE"),
			"TITLE" => GetMessage("LEARNING_COURSES_LESSON_DELETE"),
			"URL" => "javascript:if(confirm('".GetMessage("LEARNING_COURSES_LESSON_DELETE_CONF")."'))jsUtils.Redirect([], '".CUtil::JSEscape("/bitrix/admin/learn_lesson_admin.php?ID=".$arParams["LESSON_ID"]."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&COURSE_ID=".$arParams["COURSE_ID"]).(strlen($deleteReturnUrl) ? "&return_url=".urlencode($deleteReturnUrl) : "")."')",
			"ICON" => "bx-context-toolbar-delete-icon",
			"ID" => "bx-context-toolbar-delete-lesson",
		),

	);

	$this->AddIncludeAreaIcons($arAreaButtons);
}
?>