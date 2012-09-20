<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSetVariableActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"VariableValue" => null,
		);
	}

	public function Execute()
	{
		if (!is_array($this->VariableValue) || count($this->VariableValue) <= 0)
			return CBPActivityExecutionStatus::Closed;

		$rootActivity = $this->GetRootActivity();

		foreach ($this->VariableValue as $key => $value)
			$rootActivity->SetVariable($key, $value);

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!is_array($arTestProperties)
			|| !array_key_exists("VariableValue", $arTestProperties)
			|| !is_array($arTestProperties["VariableValue"])
			|| count($arTestProperties["VariableValue"]) <= 0)
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "VariableValue", "message" => GetMessage("BPSVA_EMPTY_VARS"));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"])
				&& array_key_exists("VariableValue", $arCurrentActivity["Properties"])
				&& is_array($arCurrentActivity["Properties"]["VariableValue"]))
			{
				foreach ($arCurrentActivity["Properties"]["VariableValue"] as $k => $v)
				{
					$arCurrentValues[$k] = $v;

					if ($arFieldTypes[$arWorkflowVariables[$k]["Type"]]["BaseType"] == "user")
					{
						if (!is_array($arCurrentValues[$k]))
							$arCurrentValues[$k] = array($arCurrentValues[$k]);

						$arCurrentValues[$k] = CBPHelper::UsersArrayToString($arCurrentValues[$k], $arWorkflowTemplate, $documentType);
					}
				}
			}
		}

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFieldsVars", $arWorkflowVariables, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"arVariables" => $arWorkflowVariables,
				"formName" => $formName,
				"javascriptFunctions" => $javascriptFunctions,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		$arProperties = array("VariableValue" => array());

		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (count($arWorkflowVariables) <= 0)
		{
			$arErrors[] = array(
				"code" => "EmptyVariables",
				"parameter" => "",
				"message" => GetMessage("BPSVA_EMPTY_VARS"),
			);
			return false;
		}

		$ind = 0;
		while (array_key_exists("variable_field_".$ind, $arCurrentValues))
		{
			if (array_key_exists($arCurrentValues["variable_field_".$ind], $arWorkflowVariables))
			{
				$varCode = $arCurrentValues["variable_field_".$ind];

				$arProperties["VariableValue"][$varCode] = (strlen($arCurrentValues[$varCode]) > 0 ? $arCurrentValues[$varCode] : $arCurrentValues[$varCode."_1"]);

				if ($arFieldTypes[$arWorkflowVariables[$varCode]["Type"]]["BaseType"] == "user")
				{
					$v1 = CBPHelper::UsersStringToArray($arProperties["VariableValue"][$varCode], $documentType, $arErrors);
					if (count($arErrors) > 0)
						return false;

					if (count($v1) == 0)
						$arProperties["VariableValue"][$varCode] = null;
					elseif (count($v1) == 1)
						$arProperties["VariableValue"][$varCode] = $v1[0];
					else
						$arProperties["VariableValue"][$varCode] = $v1;
				}
			}

			$ind++;
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