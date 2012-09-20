<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/classes/general/runtimeservice.php");

class CBPDocumentService
	extends CBPRuntimeService
{
	private $arDocumentsCache = array();

	public function GetDocument($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			return $this->arDocumentsCache[$k];

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
		{
			$this->arDocumentsCache[$k] = call_user_func_array(array($entity, "GetDocument"), array($documentId));
			return $this->arDocumentsCache[$k];
		}

		return null;
	}

	public function UpdateDocument($parameterDocumentId, $arFields)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "UpdateDocument"), array($documentId, $arFields));

		return false;
	}

	public function CreateDocument($parameterDocumentId, $arFields)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "CreateDocument"), array($documentId, $arFields));

		return false;
	}

	public function PublishDocument($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "PublishDocument"), array($documentId));

		return false;
	}

	public function UnpublishDocument($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "UnpublishDocument"), array($documentId));

		return false;
	}

	public function LockDocument($parameterDocumentId, $workflowId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "LockDocument"), array($documentId, $workflowId));

		return false;
	}

	public function UnlockDocument($parameterDocumentId, $workflowId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "UnlockDocument"), array($documentId, $workflowId));

		return false;
	}

	public function DeleteDocument($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "DeleteDocument"), array($documentId));

		return false;
	}

	public function IsDocumentLocked($parameterDocumentId, $workflowId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "IsDocumentLocked"), array($documentId, $workflowId));

		return false;
	}

	public function SubscribeOnUnlockDocument($parameterDocumentId, $workflowId,  $eventName)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);
		RegisterModuleDependences($moduleId, $entity."_OnUnlockDocument", "bizproc", "CBPDocumentService", "OnUnlockDocument", 100, "", array($workflowId,  $eventName));
	}

	public function UnsubscribeOnUnlockDocument($parameterDocumentId, $workflowId, $eventName)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);
		UnRegisterModuleDependences($moduleId, $entity."_OnUnlockDocument", "bizproc", "CBPDocumentService", "OnUnlockDocument", "", array($workflowId,  $eventName));
	}

	public static function OnUnlockDocument($workflowId, $eventName, $documentId = array())
	{
		CBPRuntime::SendExternalEvent($workflowId, $eventName, array());
	}

	public function GetDocumentType($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity) && method_exists($entity, "GetDocumentType"))
			return array($moduleId, $entity, call_user_func_array(array($entity, "GetDocumentType"), array($documentId)));

		return null;
	}

	public function GetDocumentFields($parameterDocumentType)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
		{
			$ar = call_user_func_array(array($entity, "GetDocumentFields"), array($documentType));
			if (is_array($ar))
			{
				$arKeys = array_keys($ar);
				if (!array_key_exists("BaseType", $ar[$arKeys[0]]) || strlen($ar[$arKeys[0]]["BaseType"]) <= 0)
				{
					foreach ($arKeys as $key)
					{
						if (in_array($ar[$key]["Type"], array("int", "datetime", "user", "string", "bool", "file", "text", "select")))
							$ar[$key]["BaseType"] = $ar[$key]["Type"];
						else
							$ar[$key]["BaseType"] = "string";
					}
				}
			}

			return $ar;
		}

		return null;
	}

	public function GetDocumentFieldTypes($parameterDocumentType)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity) && method_exists($entity, "GetDocumentFieldTypes"))
			return call_user_func_array(array($entity, "GetDocumentFieldTypes"), array($documentType));

		return CBPHelper::GetDocumentFieldTypes();
	}

	public function AddDocumentField($parameterDocumentType, $arFields)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "AddDocumentField"), array($documentType, $arFields));

		return false;
	}

	public function GetJSFunctionsForFields($parameterDocumentType, $objectName, $arDocumentFields = array(), $arDocumentFieldTypes = array())
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		$result = "";
		if (class_exists($entity) && method_exists($entity, "GetJSFunctionsForFields"))
		{
			$result = call_user_func_array(array($entity, "GetJSFunctionsForFields"), array($documentType, $objectName, $arDocumentFields, $arDocumentFieldTypes));
		}
		else
		{
			if (!is_array($arDocumentFields) || count($arDocumentFields) <= 0)
				$arDocumentFields = $this->GetDocumentFields($parameterDocumentType);
			if (!is_array($arDocumentFieldTypes) || count($arDocumentFieldTypes) <= 0)
				$arDocumentFieldTypes = $this->GetDocumentFieldTypes($parameterDocumentType);

			$result = CBPHelper::GetJSFunctionsForFields($objectName, $arDocumentFields, $arDocumentFieldTypes);
		}

		return $result;
	}

	public function GetGUIFieldEdit($parameterDocumentType, $formName, $fieldName, $fieldValue, $arDocumentField = array(), $bAllowSelection = false)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (!is_array($arDocumentField) || count($arDocumentField) <= 0)
		{
			$arDocumentFields = $this->GetDocumentFields($parameterDocumentType);
			$arDocumentField = $arDocumentFields[$fieldName];
		}

		if (class_exists($entity) && method_exists($entity, "GetGUIFieldEdit"))
			return call_user_func_array(array($entity, "GetGUIFieldEdit"), array($documentType, $formName, $fieldName, $fieldValue, $arDocumentField, $bAllowSelection));

		return CBPHelper::GetGUIFieldEdit($parameterDocumentType, $formName, $fieldName, $fieldValue, $arDocumentField, $bAllowSelection);
	}

	public function SetGUIFieldEdit($parameterDocumentType, $fieldName, $arRequest, &$arErrors, $arDocumentField = array())
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (!is_array($arDocumentField) || count($arDocumentField) <= 0)
		{
			$arDocumentFields = $this->GetDocumentFields($parameterDocumentType);
			$arDocumentField = $arDocumentFields[$fieldName];
		}

		if (class_exists($entity) && method_exists($entity, "SetGUIFieldEdit"))
			return call_user_func_array(array($entity, "SetGUIFieldEdit"), array($documentType, $fieldName, $arRequest, &$arErrors, $arDocumentField));

		return CBPHelper::SetGUIFieldEdit($parameterDocumentType, $fieldName, $arRequest, $arErrors, $arDocumentField);
	}

	public function GetFieldValuePrintable($parameterDocumentId, $fieldName, $fieldType, $fieldValue, $arFieldType = null)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity) && method_exists($entity, "GetFieldValuePrintable"))
			return call_user_func_array(array($entity, "GetFieldValuePrintable"), array($documentId, $fieldName, $fieldType, $fieldValue, $arFieldType));

		return CBPHelper::GetFieldValuePrintable($fieldName, $fieldType, $fieldValue, $arFieldType);
	}

	public function GetDocumentAdminPage($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetDocumentAdminPage"), array($documentId));

		return "";
	}

	public function GetDocumentForHistory($parameterDocumentId, $historyIndex)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetDocumentForHistory"), array($documentId, $historyIndex));

		return null;
	}

	public function RecoverDocumentFromHistory($parameterDocumentId, $arDocument)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "RecoverDocumentFromHistory"), array($documentId, $arDocument));

		return false;
	}

	public function GetUsersFromUserGroup($group, $parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetUsersFromUserGroup"), array($group, $documentId));

		return array();
	}

	public function GetAllowableUserGroups($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
		{
			$result = call_user_func_array(array($entity, "GetAllowableUserGroups"), array($documentId));
			$result1 = array();
			foreach ($result as $key => $value)
				$result1[strtolower($key)] = $value;
			return $result1;
		}

		return array();
	}

	public function GetAllowableOperations($parameterDocumentType)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetAllowableOperations"), array($documentType));

		return array();
	}
}
?>