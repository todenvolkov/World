<?
global $DB;
$db_type = strtolower($DB->type);
$arClasses = array(
	"CBPActivity" => "classes/general/activity.php",
	"CBPActivityCondition" => "classes/general/activitycondition.php",
	"CBPCompositeActivity" => "classes/general/compositeactivity.php",
	"CBPActivityExecutionStatus" => "classes/general/constants.php",
	"CBPActivityExecutionResult" => "classes/general/constants.php",
	"CBPWorkflowStatus" => "classes/general/constants.php",
	"CBPActivityExecutorOperationType" => "classes/general/constants.php",
	"CBPDocumentEventType" => "classes/general/constants.php",
	"CBPCanUserOperateOperation" => "classes/general/constants.php",
	"CBPDocument" => "classes/general/document.php",
	"CBPDocumentService" => "classes/general/documentservice.php",
	"CBPArgumentException" => "classes/general/exception.php",
	"CBPArgumentNullException" => "classes/general/exception.php",
	"CBPArgumentOutOfRangeException" => "classes/general/exception.php",
	"CBPArgumentTypeException" => "classes/general/exception.php",
	"CBPInvalidOperationException" => "classes/general/exception.php",
	"CBPNotSupportedException" => "classes/general/exception.php",
	"CBPHelper" => "classes/general/helper.php",
	"CBPAllHistoryService" => "classes/general/historyservice.php",
	"CBPHistoryService" => "classes/".$db_type."/historyservice.php",
	"CBPHistoryResult" => "classes/general/historyservice.php",
	"CBPRuntime" => "classes/general/runtime.php",
	"CBPRuntimeService" => "classes/general/runtimeservice.php",
	"CBPSchedulerService" => "classes/general/schedulerservice.php",
	"CBPAllStateService" => "classes/general/stateservice.php",
	"CBPStateService" => "classes/".$db_type."/stateservice.php",
	"CBPAllTaskService" => "classes/general/taskservice.php",
	"CBPTaskService" => "classes/".$db_type."/taskservice.php",
	"CBPTaskResult" => "classes/general/taskservice.php",
	"CBPAllTrackingService" => "classes/general/trackingservice.php",
	"CBPTrackingService" => "classes/".$db_type."/trackingservice.php",
	"CBPTrackingType" => "classes/general/trackingservice.php",
	"CBPVirtualDocument" => "classes/general/virtualdocument.php",
	"CBPWorkflow" => "classes/general/workflow.php",
	"CBPAllWorkflowPersister" => "classes/general/workflowpersister.php",
	"CBPWorkflowPersister" => "classes/".$db_type."/workflowpersister.php",
	"CAllBPWorkflowTemplateLoader" => "classes/general/workflowtemplateloader.php",
	"CBPWorkflowTemplateLoader" => "classes/".$db_type."/workflowtemplateloader.php",
	"CBPWorkflowTemplateResult" => "classes/general/workflowtemplateloader.php",
	"CBPWorkflowTemplateUser" => "classes/general/workflowtemplateloader.php",
);
CModule::AddAutoloadClasses("bizproc", $arClasses);
/*patchlimitationmutatormark1*/
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/classes/general/interface.php");
?>