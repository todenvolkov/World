<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPFieldCondition
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
		$documentId = $rootActivity->GetDocumentId();

		$documentService = $ownerActivity->workflow->GetService("DocumentService");
		$document = $documentService->GetDocument($documentId);

		$result = true;
		foreach ($this->condition as $cond)
		{
			if (array_key_exists($cond[0], $document))
			{
				if (!$this->CheckCondition($document[$cond[0]], $cond[1], $cond[2]))
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

		if (is_array($field))
		{
			$arKeys = array_keys($field);
			$field = $arKeys[0];
		}

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
			case "in":
				$result = (strpos($field, $value) !== false);
				break;
			default:
				$result = ($field == $value);
		}

		return $result;
	}

	public static function GetPropertiesDialog($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $defaultValue, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFieldsTmp = $documentService->GetDocumentFields($documentType);

		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();
			if (is_array($defaultValue))
			{
				$i = 0;
				foreach ($defaultValue as $value)
				{
					if (strlen($arCurrentValues["field_condition_count"]) > 0)
						$arCurrentValues["field_condition_count"] .= ",";
					$arCurrentValues["field_condition_count"] .= $i;

					$arCurrentValues["field_condition_field_".$i] = $value[0];
					$arCurrentValues["field_condition_condition_".$i] = $value[1];
					$arCurrentValues["field_condition_value_".$i] = $value[2];

					if ($arDocumentFieldsTmp[$arCurrentValues["field_condition_field_".$i]]["BaseType"] == "user")
					{
						if (intval($arCurrentValues["field_condition_value_".$i])."!" == $arCurrentValues["field_condition_value_".$i]."!")
							$arCurrentValues["field_condition_value_".$i] = "user_".$arCurrentValues["field_condition_value_".$i];
						$arCurrentValues["field_condition_value_".$i] = CBPHelper::UsersArrayToString(array($arCurrentValues["field_condition_value_".$i]), $arWorkflowTemplate, $documentType);
					}

					$i++;
				}
			}
		}

		$arDocumentFields = array();
		foreach ($arDocumentFieldsTmp as $key => $value)
		{
			if (!$value["Filterable"])
				continue;
			$arDocumentFields[$key] = $value;
		}

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFieldsFC", $arDocumentFields, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arDocumentFields" => $arDocumentFields,
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$runtime = CBPRuntime::GetRuntime();
		$arErrors = array();

		if (!array_key_exists("field_condition_count", $arCurrentValues) || strlen($arCurrentValues["field_condition_count"]) <= 0)
		{
			$arErrors[] = array(
				"code" => "",
				"message" => GetMessage("BPFC_NO_WHERE"),
			);
			return null;
		}

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFieldsTmp = $documentService->GetDocumentFields($documentType);

		$arResult = array();

		$arFieldConditionCount = explode(",", $arCurrentValues["field_condition_count"]);
		foreach ($arFieldConditionCount as $i)
		{
			if (intval($i)."!" != $i."!")
				continue;

			$i = intval($i);

			if (!array_key_exists("field_condition_field_".$i, $arCurrentValues) || strlen($arCurrentValues["field_condition_field_".$i]) <= 0)
				continue;

			if ($arDocumentFieldsTmp[$arCurrentValues["field_condition_field_".$i]]["BaseType"] == "user")
			{
				$ar = array();
				$v1 = CBPHelper::UsersStringToArray($arCurrentValues["field_condition_value_".$i], $documentType, $arErrors);
				if (count($arErrors) > 0)
					continue;
				foreach ($v1 as $v2)
					$ar[] = (substr($v2, 0, strlen("user_")) == "user_") ? substr($v2, strlen("user_")) : $v2;

				if (count($ar) == 0)
					continue;
				elseif (count($ar) == 1)
					$arCurrentValues["field_condition_value_".$i] = $ar[0];
				else
					$arCurrentValues["field_condition_value_".$i] = $ar;

				$arResult[] = array(
					$arCurrentValues["field_condition_field_".$i],
					htmlspecialcharsback($arCurrentValues["field_condition_condition_".$i]),
					$arCurrentValues["field_condition_value_".$i]
				);
			}
			else
			{
				$arResult[] = array(
					$arCurrentValues["field_condition_field_".$i],
					htmlspecialcharsback($arCurrentValues["field_condition_condition_".$i]),
					(strlen($arCurrentValues["field_condition_value_".$i]) > 0 ? $arCurrentValues["field_condition_value_".$i] : $arCurrentValues["field_condition_value_".$i."_1"]),
				);
			}
		}

		if (count($arResult) <= 0)
		{
			$arErrors[] = array(
				"code" => "",
				"message" => GetMessage("BPFC_NO_WHERE"),
			);
			return null;
		}

		return $arResult;
	}
}
?>