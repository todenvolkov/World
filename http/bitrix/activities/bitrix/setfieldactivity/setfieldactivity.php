<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSetFieldActivity
	extends CBPActivity
	implements IBPActivityExternalEventListener
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"FieldValue" => null
		);
	}

	public function Execute()
	{
		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		if (!is_array($this->FieldValue) || count($this->FieldValue) <= 0)
			return CBPActivityExecutionStatus::Closed;

		$documentService = $this->workflow->GetService("DocumentService");

		if ($documentService->IsDocumentLocked($documentId, $this->GetWorkflowInstanceId()))
		{
			$this->workflow->AddEventHandler($this->name, $this);
			$documentService->SubscribeOnUnlockDocument($documentId, $this->GetWorkflowInstanceId(), $this->name);
			return CBPActivityExecutionStatus::Executing;
		}

		$documentService->UpdateDocument($documentId, $this->FieldValue);

		return CBPActivityExecutionStatus::Closed;
	}

	public function OnExternalEvent($arEventParameters = array())
	{
		if ($this->executionStatus != CBPActivityExecutionStatus::Closed)
		{
			$rootActivity = $this->GetRootActivity();
			$documentId = $rootActivity->GetDocumentId();

			$documentService = $this->workflow->GetService("DocumentService");
			if ($documentService->IsDocumentLocked($documentId, $this->GetWorkflowInstanceId()))
				return;

			if (count($this->FieldValue) > 0)
				$documentService->UpdateDocument($documentId, $this->FieldValue);

			$documentService->UnsubscribeOnUnlockDocument($documentId, $this->GetWorkflowInstanceId(), $this->name);
			$this->workflow->RemoveEventHandler($this->name, $this);
			$this->workflow->CloseActivity($this);
		}
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!is_array($arTestProperties)
			|| !array_key_exists("FieldValue", $arTestProperties)
			|| !is_array($arTestProperties["FieldValue"])
			|| count($arTestProperties["FieldValue"]) <= 0)
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "FieldValue", "message" => GetMessage("BPSFA_EMPTY_FIELDS"));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null)
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFieldsTmp = $documentService->GetDocumentFields($documentType);

		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]) 
				&& array_key_exists("FieldValue", $arCurrentActivity["Properties"])
				&& is_array($arCurrentActivity["Properties"]["FieldValue"]))
			{
				foreach ($arCurrentActivity["Properties"]["FieldValue"] as $k => $v)
				{
					$arCurrentValues[$k] = $v;

					if ($arDocumentFieldsTmp[$k]["BaseType"] == "user")
					{
						if (!is_array($arCurrentValues[$k]))
							$arCurrentValues[$k] = array($arCurrentValues[$k]);

						$ar = array();
						foreach ($arCurrentValues[$k] as $v)
						{
							if (intval($v)."!" == $v."!")
								$v = "user_".$v;
							$ar[] = $v;
						}

						$arCurrentValues[$k] = CBPHelper::UsersArrayToString($ar, $arWorkflowTemplate, $documentType);
					}
				}
			}
		}

		$arDocumentFields = array();
		$defaultFieldValue = "";
		foreach ($arDocumentFieldsTmp as $key => $value)
		{
			if (!$value["Editable"])
				continue;

			$arDocumentFields[$key] = $value;
			if (strlen($defaultFieldValue) <= 0)
				$defaultFieldValue = $key;

			if ($value["BaseType"] == "select" || $value["BaseType"] == "bool")
			{
				if (array_key_exists($key."_1", $arCurrentValues)
					&& ($value["Multiple"] && count($arCurrentValues[$key."_1"]) > 0
						|| !$value["Multiple"] && strlen($arCurrentValues[$key."_1"]) > 0)
					)
				{
					$arCurrentValues[$key] = $arCurrentValues[$key."_1"];
				}
			}
		}

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFields", $arDocumentFields, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"arDocumentFields" => $arDocumentFields,
				"formName" => $formName,
				"defaultFieldValue" => $defaultFieldValue,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
				"documentType" => $documentType,
				"popupWindow" => &$popupWindow,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arProperties = array("FieldValue" => array());

		$documentService = $runtime->GetService("DocumentService");

		$arNewFieldsMap = array();
		if (array_key_exists("new_field_name", $arCurrentValues) && is_array($arCurrentValues["new_field_name"]))
		{
			$arNewFieldKeys = array_keys($arCurrentValues["new_field_name"]);
			foreach ($arNewFieldKeys as $k)
			{
				$code = trim($arCurrentValues["new_field_code"][$k]);
				if (!array_key_exists($code, $arCurrentValues))
					continue;

				$arFieldsTmp = array(
					"name" => $arCurrentValues["new_field_name"][$k],
					"code" => $code,
					"type" => $arCurrentValues["new_field_type"][$k],
					"multiple" => $arCurrentValues["new_field_mult"][$k],
					"required" => $arCurrentValues["new_field_req"][$k],
					"options" => $arCurrentValues["new_field_options"][$k],
				);

				$newCode = $documentService->AddDocumentField($documentType, $arFieldsTmp);
				$arNewFieldsMap[$newCode] = $code;
			}
		}

		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		foreach ($arDocumentFields as $fieldKey => $fieldValue)
		{
			if (!$fieldValue["Editable"])
				continue;

			$fieldKey1 = (array_key_exists($fieldKey, $arNewFieldsMap) ? $arNewFieldsMap[$fieldKey] : $fieldKey);
			if (array_key_exists($fieldKey1, $arCurrentValues))
			{
				$arProperties["FieldValue"][$fieldKey] = (strlen($arCurrentValues[$fieldKey1]) > 0 ? $arCurrentValues[$fieldKey1] : $arCurrentValues[$fieldKey1."_1"]);
				if ($fieldValue["BaseType"] == "user")
				{
					if (!is_array($arProperties["FieldValue"][$fieldKey]))
						$arProperties["FieldValue"][$fieldKey] = array($arProperties["FieldValue"][$fieldKey]);

					$ar = array();
					foreach ($arProperties["FieldValue"][$fieldKey] as $v)
					{
						$v1 = CBPHelper::UsersStringToArray($v, $documentType, $arErrors);
						if (count($arErrors) > 0)
							return false;
						foreach ($v1 as $v2)
							$ar[] = (substr($v2, 0, strlen("user_")) == "user_") ? substr($v2, strlen("user_")) : $v2;
					}

					if (count($ar) == 0)
						$arProperties["FieldValue"][$fieldKey] = null;
					elseif (count($ar) == 1)
						$arProperties["FieldValue"][$fieldKey] = $ar[0];
					else
						$arProperties["FieldValue"][$fieldKey] = $ar;
				}
			}
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