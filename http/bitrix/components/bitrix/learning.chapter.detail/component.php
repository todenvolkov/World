<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//Params
$arParams["COURSE_ID"] = (isset($arParams["COURSE_ID"]) && intval($arParams["COURSE_ID"]) > 0 ? intval($arParams["COURSE_ID"]) : intval($_REQUEST["COURSE_ID"]));
$arParams["CHAPTER_ID"] = (isset($arParams["CHAPTER_ID"]) && intval($arParams["CHAPTER_ID"]) > 0 ? intval($arParams["CHAPTER_ID"]) : intval($_REQUEST["CHAPTER_ID"]));
$arParams["CHAPTER_DETAIL_TEMPLATE"] = (strlen($arParams["CHAPTER_DETAIL_TEMPLATE"]) > 0 ? htmlspecialchars($arParams["CHAPTER_DETAIL_TEMPLATE"]): "chapter.php?CHAPTER_ID=#CHAPTER_ID#");
$arParams["LESSON_DETAIL_TEMPLATE"] = (strlen($arParams["LESSON_DETAIL_TEMPLATE"]) > 0 ? htmlspecialchars($arParams["LESSON_DETAIL_TEMPLATE"]) : "lesson.php?LESSON_ID=#LESSON_ID#");
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

	//Chapter
	$rsChapter = CChapter::GetList(
		Array(),
		Array(
			"ID" => $arParams["CHAPTER_ID"],
			"COURSE_ID" => $arParams["COURSE_ID"],
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => $arParams["CHECK_PERMISSIONS"]
		)
	);

	if (!$arChapter = $rsChapter->GetNext())
	{
		$this->AbortResultCache();
		ShowError(GetMessage("LEARNING_CHAPTER_DENIED"));
		return;
	}

	//Images
	$arChapter["PREVIEW_PICTURE_ARRAY"] = CFile::GetFileArray($arChapter["PREVIEW_PICTURE"]);
	$arChapter["DETAIL_PICTURE_ARRAY"] = CFile::GetFileArray($arChapter["DETAIL_PICTURE"]);

	$arResult = Array(
		"CHAPTER" => $arChapter,
		"CONTENTS" => Array()
	);


	//Included chapters and lessons
	$rsContent = CCourse::GetCourseContent($arChapter["COURSE_ID"], Array());
	$foundChapter = false;
	while ($arContent = $rsContent->GetNext())
	{
		if ($foundChapter)
		{
			if ($arContent["DEPTH_LEVEL"] <= $baseDepthLevel)
				break;

			$arContent["DEPTH_LEVEL"] -= $baseDepthLevel;

			if ($arContent["TYPE"] == "CH")
				$arContent["URL"] = CComponentEngine::MakePathFromTemplate(
					$arParams["CHAPTER_DETAIL_TEMPLATE"],
					Array(
						"CHAPTER_ID" => $arContent["ID"],
						"COURSE_ID" => $arChapter["COURSE_ID"]
					)
				);
			else
				$arContent["URL"] = CComponentEngine::MakePathFromTemplate(
					$arParams["LESSON_DETAIL_TEMPLATE"],
					Array(
						"LESSON_ID" => $arContent["ID"],
						"COURSE_ID" => $arChapter["COURSE_ID"]
					)
				);

			$arResult["CONTENTS"][] = $arContent;
		}

		if ($arContent["ID"]==$arChapter["ID"] && $arContent["TYPE"]=="CH")
		{
			$foundChapter = true;
			$baseDepthLevel = $arContent["DEPTH_LEVEL"];
		}

	}

	unset($rsContent);
	unset($arContent);
	unset($rsChapter);
	unset($arChapter);

	$this->IncludeComponentTemplate();
}

//Set Title
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle($arResult["CHAPTER"]["NAME"]);

if ($APPLICATION->GetUserRight("learning") == "W" || $USER->IsAdmin())
{
	$deleteReturnUrl = "";
	if ($parent = $this->GetParent())
	{
		if ($arResult["CHAPTER"]["CHAPTER_ID"])
		{
			$deleteReturnUrl = CComponentEngine::MakePathFromTemplate($parent->arResult["URL_TEMPLATES"]["chapter.detail"], Array("CHAPTER_ID" => $arResult["CHAPTER"]["CHAPTER_ID"],"COURSE_ID" => $arResult["CHAPTER"]["COURSE_ID"]));
		}
		else
		{
			$deleteReturnUrl = CComponentEngine::MakePathFromTemplate($parent->arResult["URL_TEMPLATES"]["course.detail"], Array("COURSE_ID" => $arResult["CHAPTER"]["COURSE_ID"]));
		}
	}

	$arAreaButtons = array(
		array(
			"TEXT" => GetMessage("LEARNING_COURSES_CHAPTER_EDIT"),
			"TITLE" => GetMessage("LEARNING_COURSES_CHAPTER_EDIT"),
			"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
				array(
					"URL" => "/bitrix/admin/learn_chapter_edit.php?ID=".$arParams["CHAPTER_ID"]."lang=".LANGUAGE_ID."&COURSE_ID=".$arParams["COURSE_ID"]."&bxpublic=Y&from_module=learning",
					"PARAMS" => array(
						"width" => 700, 'height' => 500, 'resize' => false,
					),
				)
			),
			"ICON" => "bx-context-toolbar-edit-icon",
			"ID" => "bx-context-toolbar-edit-chapter",
		),

		array(
			"TEXT" => GetMessage("LEARNING_COURSES_CHAPTER_DELETE"),
			"TITLE" => GetMessage("LEARNING_COURSES_CHAPTER_DELETE"),
			"URL" => "javascript:if(confirm('".GetMessage("LEARNING_COURSES_CHAPTER_DELETE_CONF")."'))jsUtils.Redirect([], '".CUtil::JSEscape("/bitrix/admin/learn_chapter_admin.php?ID=".$arParams["CHAPTER_ID"]."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&COURSE_ID=".$arParams["COURSE_ID"]).(strlen($deleteReturnUrl) ? "&return_url=".urlencode($deleteReturnUrl) : "")."')",
			"ICON" => "bx-context-toolbar-delete-icon",
			"ID" => "bx-context-toolbar-delete-chapter",
		),
	);

	$this->AddIncludeAreaIcons($arAreaButtons);
}
?>