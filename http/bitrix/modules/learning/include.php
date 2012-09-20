<?
global $DBType;
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/lang.php");

global $LEARNING_CACHE_COURSE;
$LEARNING_CACHE_COURSE = Array();


CModule::AddAutoloadClasses(
	"learning",
	array(
		"CCourse" => "classes/".$DBType."/course.php",
		"CLesson" => "classes/".$DBType."/lesson.php",
		"CChapter" => "classes/".$DBType."/chapter.php",
		"CLQuestion" => "classes/".$DBType."/question.php",
		"CLAnswer" => "classes/".$DBType."/answer.php",
		"CGradeBook" => "classes/".$DBType."/gradebook.php",
		"CGradebook" => "classes/".$DBType."/gradebook.php",
		"CTest" => "classes/".$DBType."/test.php",
		"CTestAttempt" => "classes/".$DBType."/attempt.php",
		"CTestResult" => "classes/".$DBType."/testresult.php",
		"CLTestMark" => "classes/".$DBType."/testmark.php",
		"CCertification" => "classes/".$DBType."/certification.php",
		"CStudent" => "classes/".$DBType."/student.php",
		"CSitePath" => "classes/".$DBType."/sitepath.php",
		"CCourseImport" => "classes/general/import.php",
		"CCourseSCORM" => "classes/general/scorm.php",
		"CCoursePackage" => "classes/general/export.php",
	)
);
?>
