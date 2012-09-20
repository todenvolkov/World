<?
IncludeModuleLangFile(__FILE__);
$arSubMenu = $arSubCourse = Array();

function __chapter_menu_gen($COURSE_ID, $CHAPTER_ID)
{
	$aMenu = Array();
	$arFilter = Array(
		"COURSE_ID" => $COURSE_ID,
		"CHAPTER_ID" => $CHAPTER_ID,
	);
	$aMenu[] = Array(
		"text" => GetMessage("LEARNING_LESSONS"),
			"url" => "learn_lesson_admin.php?lang=".LANG."&amp;COURSE_ID=".$COURSE_ID."&amp;CHAPTER_ID=".$CHAPTER_ID."&amp;set_filter=Y&amp;filter_chapter_id=".$CHAPTER_ID,
			"title" => GetMessage("LEARNING_LESSONS"),
			"icon" => "learning_menu_icon_lessons",
			"page_icon" => "learning_page_icon_lessons",
			"skip_chain" => true,
			"module_id" => "learning",
			"more_url" => Array(
				"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$CHAPTER_ID."&set_filter=Y&filter_chapter_id=".$CHAPTER_ID,
				"learn_lesson_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$CHAPTER_ID,
				"learn_question_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$CHAPTER_ID,
				"learn_question_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&CHAPTER_ID=".$CHAPTER_ID,
			),
		);
	$chapter = CChapter::GetList(Array(),$arFilter, true);
	while($arChapter = $chapter->GetNext())
	{


		$aMenu[] = Array(
			"text" => $arChapter["NAME"],
			"url" => "learn_chapter_admin.php?lang=".LANG."&amp;COURSE_ID=".$COURSE_ID."&amp;set_filter=Y&amp;filter_chapter_id=".$arChapter["ID"],
			"title" => $arChapter["NAME"],
			"icon" => "learning_menu_icon_chapters",
			"module_id" => "learning",
			"more_url" => Array(
					"learn_chapter_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&set_filter=Y&filter_chapter_id=".$arChapter["ID"],
					"learn_chapter_edit.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&set_filter=Y&filter_chapter_id=".$arChapter["ID"],
					"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$COURSE_ID."&set_filter=Y&filter_chapter_id=".$arChapter["ID"],
				),
			"items_id" => "menu_learning_course_".$COURSE_ID."_".$arChapter["ID"],
			"items" =>__chapter_menu_gen($COURSE_ID, $arChapter["ID"]),
		);
	}
	return $aMenu;
}

$module_id = "learning";
$LEARNING_RIGHT = $APPLICATION->GetGroupRight($module_id);

//if(isset($_REQUEST['admin_mnu_menu_id']) && substr($_REQUEST['admin_mnu_menu_id'],0,strlen("menu_learning")) == "menu_learning" || intval($GLOBALS["COURSE_ID"]) > 0)
if(method_exists($this, "IsSectionActive") && $this->IsSectionActive("menu_learning_courses") || intval($GLOBALS["COURSE_ID"]) > 0)
{
	$LEARNING_MENU_MAX_COURSES = COption::GetOptionString("learning", "menu_max_courses", "10");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");

	$arFilter = Array("MIN_PERMISSION"=>"W");
	$res = CCourse::GetList(Array("SORT"=>"ASC", "NAME" => "ASC"),$arFilter);
	while($arCourse = $res->Fetch())
	{
		if ($LEARNING_MENU_MAX_COURSES>0 && count($arSubMenu) < $LEARNING_MENU_MAX_COURSES)
		{

//			if ( substr($_REQUEST['admin_mnu_menu_id'],0,strlen("menu_learning_course_".$arCourse["ID"])) == "menu_learning_course_".$arCourse["ID"] || $GLOBALS["COURSE_ID"] == $arCourse["ID"])
			if (method_exists($this, "IsSectionActive") && $this->IsSectionActive("menu_learning_course_".$arCourse["ID"]) || $GLOBALS["COURSE_ID"] == $arCourse["ID"])
			{
				$arSubCourse = Array(
					array(
						"text" => GetMessage("LEARNING_CHAPTERS"),
						"url" => "learn_chapter_admin.php?lang=".LANG."&amp;COURSE_ID=".$arCourse["ID"]."&amp;set_filter=Y&amp;filter_chapter_id=0",
						"icon" => "learning_menu_icon_chapters",
						"page_icon" => "learning_page_icon_chapters",
						"items_id" => "menu_learning_course_".$arCourse["ID"]."_0",
						"skip_chain" => true,
						"module_id" => "learning",
						//"dynamic" =>true,

						"more_url" =>
							Array(
								"learn_chapter_admin.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"],
								"learn_chapter_edit.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"],
							),
						"title" => GetMessage("LEARNING_CHAPTERS_LIST"),
						"items" =>  __chapter_menu_gen($arCourse["ID"], 0),
					),

					array(
						"text" => GetMessage("LEARNING_LESSONS"),
						"url" => "learn_lesson_admin.php?lang=".LANG."&amp;COURSE_ID=".$arCourse["ID"]."&amp;set_filter=Y",
						"skip_chain"=>true,
						"icon" => "learning_menu_icon_lessons",
						"page_icon" => "learning_page_icon_lessons",
						"more_url" =>
							Array(
								"learn_lesson_admin.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"],
								//"learn_lesson_edit.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"],
								//"learn_question_admin.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"],
								//"learn_question_edit.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"],
							),
						"title" => GetMessage("LEARNING_LESSONS_LIST"),
					),

					array(
						"text" => GetMessage("LEARNING_QUESTION"),
						"url" => "learn_question_admin.php?lang=".LANG."&amp;COURSE_ID=".$arCourse["ID"]."&amp;set_filter=Y&amp;from=learn_menu",
						"icon" => "learning_menu_icon_question",
						"page_icon" => "learning_page_icon_question",
						"more_url" =>
							Array(
								"learn_question_admin.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"],
								"learn_question_edit.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"],
							),
						"title" => GetMessage("LEARNING_QUESTION_LIST"),
					),

					array(
						"text" => GetMessage("LEARNING_TESTS"),
						"url" => "learn_test_admin.php?lang=".LANG."&amp;COURSE_ID=".$arCourse["ID"],
						"icon" => "learning_menu_icon_tests",
						"page_icon" => "learning_page_icon_tests",
						"more_url" =>
							Array(
								"learn_test_admin.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"],
								"learn_test_edit.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"],
							),
						"title" => GetMessage("LEARNING_TESTS_LIST"),
					)

				);
			}


			$arSubMenu[] = Array(
				"text" => htmlspecialchars($arCourse["NAME"]),
				"url" => "learn_course_index.php?lang=".LANG."&amp;COURSE_ID=".$arCourse["ID"],
				"more_url" =>
					Array(
						"learn_course_index.php?lang=".LANG."&COURSE_ID=".$arCourse["ID"]
					),
				"title" => htmlspecialchars($arCourse["NAME"]),
				"module_id" => "learning",
				"dynamic" => true,
				"items_id" => "menu_learning_course_".$arCourse["ID"],
				"items" => $arSubCourse,
			);


		}
		else
		{
			$arSubMenu[] = Array(
				"text" => GetMessage("LEARNING_MENU_COURSES_OTHER"),
				"url" => "learn_course_admin.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_COURSES_ALT"),
				"more_url"=>array(
					"learn_test_admin.php",
					"learn_test_edit.php",
					"learn_lesson_admin.php",
					"learn_lesson_edit.php",
					"learn_chapter_admin.php",
					"learn_chapter_edit.php",
					"learn_question_admin.php",
					"learn_question_edit.php",
				),
			);
			break;
		}
	}
}

if ($LEARNING_RIGHT < "W")
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/include.php");

if ($LEARNING_RIGHT == "W" || CCourse::IsHaveCourse($MIN_PERMISSION = "W"))
{
	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "learning",
		"sort" => 600,
		"text" => GetMessage("LEARNING_MENU_LEARNING"),
		"title" => GetMessage("LEARNING_MENU_LEARNING_TITLE"),
		"icon" => "learning_menu_icon",
		"page_icon" => "learning_page_icon",
		"items_id" => "menu_learning",
		"url" => "learn_index.php?lang=".LANG,
		"items" =>
		Array(
			//Courses
			Array(
				"text" => GetMessage("LEARNING_MENU_COURSES"),
				"url" => "learn_course_admin.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_COURSES_ALT"),
				"items_id" => "menu_learning_courses",
				"icon" => "learning_menu_icon_courses",
				"page_icon" => "learning_page_icon_courses",
				"dynamic"=>true,
				"module_id" => "learning",
				"more_url" =>
					Array(
						"learn_course_edit.php",

					),
				"items" => $arSubMenu,
			),

			//Certification
			Array(
				"text" => GetMessage("LEARNING_MENU_CERTIFICATION"),
				"url" => "learn_certification_admin.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_CERTIFICATION_ALT"),
				"items_id" => "menu_learning_certification",
				"icon" => "learning_menu_icon_certification",
				"page_icon" => "learning_page_icon_certification",
				"more_url" =>
					Array(
						"learn_certification_edit.php",
					),
			),

			//Gradebook
			Array(
				"text" => GetMessage("LEARNING_MENU_GRADEBOOK"),
				"url" => "learn_gradebook_admin.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_GRADEBOOK_ALT"),
				"items_id" => "menu_learning_gradebook",
				"icon" => "learning_menu_icon_gradebook",
				"page_icon" => "learning_page_icon_gradebook",
				"more_url" =>
					Array(
						"learn_gradebook_edit.php",
					),
			),

			//Attempts
			Array(
				"text" => GetMessage("LEARNING_MENU_ATTEMPT"),
				"url" => "learn_attempt_admin.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_ATTEMPT_ALT"),
				"items_id" => "menu_learning_attempt",
				"icon" => "learning_menu_icon_attempts",
				"page_icon" => "learning_page_icon_attempts",
				"more_url" =>
					Array(
						"learn_attempt_edit.php",
						"learn_test_result_edit.php",
						"learn_test_result_admin.php",
					),
			),


	Array(
				"text" => GetMessage("LEARNING_MENU_EXPORT"),
				"url" => "learn_export.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_EXPORT_ALT"),
				"items_id" => "menu_learning_export",
				"icon" => "learning_menu_icon_export",
				"page_icon" => "learning_page_icon_export",
				"more_url" =>
					Array(
					),
			),

		)
	);

	if ($LEARNING_RIGHT == "W")
		$aMenu["items"][] = Array(
				"text" => GetMessage("LEARNING_MENU_IMPORT"),
				"url" => "learn_import.php?lang=".LANG,
				"title" => GetMessage("LEARNING_MENU_IMPORT_ALT"),
				"items_id" => "menu_learning_export",
				"icon" => "learning_menu_icon_export",
				"page_icon" => "learning_page_icon_export",
				"more_url" =>
					Array(
					),
			);
}
else
{
	define("LEARNING_ADMIN_ACCESS_DENIED","Y");
	return false;
}
return $aMenu;
?>