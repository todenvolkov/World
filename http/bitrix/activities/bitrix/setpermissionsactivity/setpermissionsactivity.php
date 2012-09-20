<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSetPermissionsActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "", "Permission" => array());
	}

	public function Execute()
	{
		$stateService = $this->workflow->GetService("StateService");

		$stateService->SetStatePermissions($this->GetWorkflowInstanceId(), $this->Permission);

		return CBPActivityExecutionStatus::Closed;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$documentService = $runtime->GetService("DocumentService");
		$arAllowableOperations = $documentService->GetAllowableOperations($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]) && array_key_exists("Permission", $arCurrentActivity["Properties"]))
			{
				foreach ($arAllowableOperations as $operationKey => $operationValue)
				{
					$arCurrentValues["permission_".$operationKey] = CBPHelper::UsersArrayToString(
						$arCurrentActivity["Properties"]["Permission"][$operationKey],
						$arWorkflowTemplate,
						$documentType
					);
				}
			}
		}
		
		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arAllowableOperations" => $arAllowableOperations,
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arProperties = array("Permission" => array());

		$documentService = $runtime->GetService("DocumentService");
		$arAllowableOperations = $documentService->GetAllowableOperations($documentType);

		foreach ($arAllowableOperations as $operationKey => $operationValue)
		{
			$arProperties["Permission"][$operationKey] = CBPHelper::UsersStringToArray($arCurrentValues["permission_".$operationKey], $documentType, $arErrors);
			if (count($arErrors) > 0)
				return false;
		}

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}
?>