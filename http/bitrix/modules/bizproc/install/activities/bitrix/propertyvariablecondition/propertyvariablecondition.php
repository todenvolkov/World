<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPPropertyVariableCondition
	extends CBPActivityCondition
{
	public $condition = null;

	public function __construct($condition)
	{
		$this->condition = $condition;
	}

	public function Evaluate(CBPActivity $ownerActivity)
	{
		if ($this->condition == null || !is_array($this->condition) || count($this->condition) <= 0)
			return true;

		if (!is_array($this->condition[0]))
			$this->condition = array($this->condition);

		$rootActivity = $ownerActivity->GetRootActivity();

		$result = true;
		foreach ($this->condition as $cond)
		{
			if ($rootActivity->IsPropertyExists($cond[0]))
			{
				if (!$this->CheckCondition($rootActivity->{$cond[0]}, $cond[1], $cond[2]))
				{
					$result = false;
					break;
				}
			}
			elseif ($rootActivity->IsVariableExists($cond[0]))
			{
				if (!$this->CheckCondition($rootActivity->GetVariable($cond[0]), $cond[1], $cond[2]))
				{
					$result = false;
					break;
				}
			}
		}

		return $result;
	}

	private function CheckCondition($field, $operation, $value)
	{
		$result = false;

		switch ($operation)
		{
			case ">":
				$result = ($field > $value);
				break;
			case ">=":
				$result = ($field >= $value);
				break;
			case "<":
				$result = ($field < $value);
				break;
			case "<=":
				$result = ($field <= $value);
				break;
			case "!=":
				$result = ($field != $value);
				break;
			default:
				$result = ($field == $value);
		}

		return $result;
	}

	public static function GetPropertiesDialog($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $defaultValue, $arCurrentValues = null, $formName = "")
	{
		if (count($arWorkflowParameters) <= 0 && count($arWorkflowVariables) <= 0)
			return null;

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();
			if (is_array($defaultValue))
			{
				$i = 0;
				foreach ($defaultValue as $value)
				{
					if (strlen($arCurrentValues["variable_condition_count"]) > 0)
						$arCurrentValues["variable_condition_count"] .= ",";
					$arCurrentValues["variable_condition_count"] .= $i;

					$arCurrentValues["variable_condition_field_".$i] = $value[0];
					$arCurrentValues["variable_condition_condition_".$i] = $value[1];
					$arCurrentValues["variable_condition_value_".$i] = $value[2];

					if (array_key_exists($value[0], $arWorkflowParameters))
					{
						if ($arFieldTypes[$arWorkflowParameters[$value[0]]["Type"]]["BaseType"] == "user")
						{
							if (!is_array($arCurrentValues["variable_condition_value_".$i]))
								$arCurrentValues["variable_condition_value_".$i] = array($arCurrentValues["variable_condition_value_".$i]);

							$arCurrentValues["variable_condition_value_".$i] = CBPHelper::UsersArrayToString($arCurrentValues["variable_condition_value_".$i], $arWorkflowTemplate, $documentType);
						}
					}
					elseif (array_key_exists($value[0], $arWorkflowVariables))
					{
						if ($arFieldTypes[$arWorkflowVariables[$value[0]]["Type"]]["BaseType"] == "user")
						{
							if (!is_array($arCurrentValues["variable_condition_value_".$i]))
								$arCurrentValues["variable_condition_value_".$i] = array($arCurrentValues["variable_condition_value_".$i]);

							$arCurrentValues["variable_condition_value_".$i] = CBPHelper::UsersArrayToString($arCurrentValues["variable_condition_value_".$i], $arWorkflowTemplate, $documentType);
						}
					}

					$i++;
				}
			}
		}

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFieldsPVC", $arWorkflowParameters + $arWorkflowVariables, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"arProperties" => $arWorkflowParameters,
				"arVariables" => $arWorkflowVariables,
				"formName" => $formName,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		if (!array_key_exists("variable_condition_count", $arCurrentValues) || strlen($arCurrentValues["variable_condition_count"]) <= 0)
		{
			$arErrors[] = array(
				"code" => "",
				"message" => GetMessage("BPPVC_EMPTY_CONDITION"),
			);
			return null;
		}

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		$arResult = array();

		$arVariableConditionCount = explode(",", $arCurrentValues["variable_condition_count"]);
		foreach ($arVariableConditionCount as $i)
		{
			if (intval($i)."!" != $i."!")
				continue;

			$i = intval($i);

			if (!array_key_exists("variable_condition_field_".$i, $arCurrentValues) || strlen($arCurrentValues["variable_condition_field_".$i]) <= 0)
				continue;

			if (array_key_exists($arCurrentValues["variable_condition_field_".$i], $arWorkflowParameters))
			{
				if ($arFieldTypes[$arWorkflowParameters[$arCurrentValues["variable_condition_field_".$i]]["Type"]]["BaseType"] == "user")
					$arCurrentValues["variable_condition_value_".$i] = CBPHelper::UsersStringToArray($arCurrentValues["variable_condition_value_".$i], $documentType, $ae);
			}
			elseif (array_key_exists($arCurrentValues["variable_condition_field_".$i], $arWorkflowVariables))
			{
				if ($arFieldTypes[$arWorkflowVariables[$arCurrentValues["variable_condition_field_".$i]]["Type"]]["BaseType"] == "user")
					$arCurrentValues["variable_condition_value_".$i] = CBPHelper::UsersStringToArray($arCurrentValues["variable_condition_value_".$i], $documentType, $ae);
			}

			$arResult[] = array(
				$arCurrentValues["variable_condition_field_".$i],
				htmlspecialcharsback($arCurrentValues["variable_condition_condition_".$i]),
				(strlen($arCurrentValues["variable_condition_value_".$i]) > 0 ? $arCurrentValues["variable_condition_value_".$i] : $arCurrentValues["variable_condition_value_".$i."_1"]),
			);
		}

		if (count($arResult) <= 0)
		{
			$arErrors[] = array(
				"code" => "",
				"message" => GetMessage("BPPVC_EMPTY_CONDITION"),
			);
			return null;
		}

		return $arResult;
	}

}
?>