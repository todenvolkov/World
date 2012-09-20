<?
if (!CModule::IncludeModule("iblock") || !class_exists("CIBlockDocument"))
	return;

IncludeModuleLangFile(__FILE__);

class CBPVirtualDocument
	extends CIBlockDocument
{
	function CanUserOperateDocument($operation, $userId, $documentId, $arParameters = array())
	{
		$documentId = trim($documentId);
		if (strlen($documentId) <= 0)
			return false;

		$userId = intval($userId);

		if (array_key_exists("UserIsAdmin", $arParameters))
		{
			if ($arParameters["UserIsAdmin"] === true)
				return true;
		}
		else
		{
			$arGroups = CUser::GetUserGroup($userId);
			if (in_array(1, $arGroups))
				return true;
		}

		if (!array_key_exists("TargetUser", $arParameters) || !array_key_exists("DocumentType", $arParameters))
		{
			$dbElementList = CIBlockElement::GetList(
				array(),
				array("ID" => $documentId, "SHOW_NEW" => "Y"),
				false,
				false,
				array("ID", "IBLOCK_ID", "CREATED_BY")
			);
			$arElement = $dbElementList->Fetch();

			if (!$arElement)
				return false;

			$arParameters["TargetUser"] = $arElement["CREATED_BY"];
			$arParameters["DocumentType"] = "type_".$arElement["IBLOCK_ID"];
		}

		if (!array_key_exists("AllUserGroups", $arParameters))
		{
			if (!array_key_exists("UserGroups", $arParameters))
				$arParameters["UserGroups"] = CUser::GetUserGroup($userId);

			$arParameters["AllUserGroups"] = $arParameters["UserGroups"];
			if ($userId == $arParameters["TargetUser"])
				$arParameters["AllUserGroups"][] = "Author";
		}

		if (!array_key_exists("DocumentStates", $arParameters))
		{
			$arParameters["DocumentStates"] = CBPDocument::GetDocumentStates(
				array("bizproc", "CBPVirtualDocument", $arParameters["DocumentType"]),
				array("bizproc", "CBPVirtualDocument", $documentId)
			);
		}

		if (array_key_exists("WorkflowId", $arParameters))
		{
			if (array_key_exists($arParameters["WorkflowId"], $arParameters["DocumentStates"]))
				$arParameters["DocumentStates"] = array($arParameters["WorkflowId"] => $arParameters["DocumentStates"][$arParameters["WorkflowId"]]);
			else
				return false;
		}

		$arAllowableOperations = CBPDocument::GetAllowableOperations(
			$userId,
			$arParameters["AllUserGroups"],
			$arParameters["DocumentStates"]
		);

		// $arAllowableOperations == null - поток не является автоматом
		// $arAllowableOperations == array() - в автомате нет допустимых операций
		// $arAllowableOperations == array("read", ...) - допустимые операции
		if (!is_array($arAllowableOperations))
			return in_array("Author", $arParameters["AllUserGroups"]);

		$r = false;
		switch ($operation)
		{
			case 0:				// DOCUMENT_OPERATION_VIEW_WORKFLOW
				$r = in_array("read", $arAllowableOperations);
				break;
			case 1:				// DOCUMENT_OPERATION_START_WORKFLOW
				$r = in_array("create", $arAllowableOperations);
				break;
			case 4:				// DOCUMENT_OPERATION_CREATE_WORKFLOW
				$r = false;
				break;
			case 2:				// DOCUMENT_OPERATION_WRITE_DOCUMENT
				$r = in_array("create", $arAllowableOperations);
				break;
			case 3:				// DOCUMENT_OPERATION_READ_DOCUMENT
				$r = in_array("read", $arAllowableOperations);
				break;
			default:
				$r = false;
		}

		return $r;
	}

	function CanUserOperateDocumentType($operation, $userId, $documentType, $arParameters = array())
	{
		$documentType = trim($documentType);
		if (strlen($documentType) <= 0)
			return false;

		$userId = intval($userId);

		if (array_key_exists("UserIsAdmin", $arParameters))
		{
			if ($arParameters["UserIsAdmin"] === true)
				return true;
		}
		else
		{
			$arGroups = CUser::GetUserGroup($userId);
			if (in_array(1, $arGroups))
				return true;
		}

		if (!array_key_exists("AllUserGroups", $arParameters))
		{
			if (!array_key_exists("UserGroups", $arParameters))
				$arParameters["UserGroups"] = CUser::GetUserGroup($userId);

			$arParameters["AllUserGroups"] = $arParameters["UserGroups"];
			$arParameters["AllUserGroups"][] = "Author";
		}

		if (!array_key_exists("DocumentStates", $arParameters))
		{
			$arParameters["DocumentStates"] = CBPDocument::GetDocumentStates(
				array("bizproc", "CBPVirtualDocument", $documentType),
				null
			);
		}

		if (array_key_exists("WorkflowId", $arParameters))
		{
			if (array_key_exists($arParameters["WorkflowId"], $arParameters["DocumentStates"]))
				$arParameters["DocumentStates"] = array($arParameters["WorkflowId"] => $arParameters["DocumentStates"][$arParameters["WorkflowId"]]);
			else
				return false;
		}

		$arAllowableOperations = CBPDocument::GetAllowableOperations(
			$userId,
			$arParameters["AllUserGroups"],
			$arParameters["DocumentStates"]
		);

		// $arAllowableOperations == null - поток не является автоматом
		// $arAllowableOperations == array() - в автомате нет допустимых операций
		// $arAllowableOperations == array("read", ...) - допустимые операции
		if (!is_array($arAllowableOperations) && $operation != 4)
			return true;

		if ($operation == 4)
			return true;

		$r = false;
		switch ($operation)
		{
			case 0:				// DOCUMENT_OPERATION_VIEW_WORKFLOW
				$r = false;
				break;
			case 1:				// DOCUMENT_OPERATION_START_WORKFLOW
				$r = in_array("create", $arAllowableOperations);
				break;
			case 4:				// DOCUMENT_OPERATION_CREATE_WORKFLOW
				$r = false;
				break;
			case 2:				// DOCUMENT_OPERATION_WRITE_DOCUMENT
				$r = in_array("create", $arAllowableOperations);
				break;
			case 3:				// DOCUMENT_OPERATION_READ_DOCUMENT
				$r = false;
				break;
			default:
				$r = false;
		}

		return $r;
	}

	function GetList($arOrder = array("SORT" => "ASC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields=array())
	{
		global $USER;

		$arFilter["SHOW_NEW"] = "Y";
		$arFilter["ACTIVE"] = "Y";

		if (count($arSelectFields) > 0)
		{
			if (!in_array("ID", $arSelectFields))
				$arSelectFields[] = "ID";
			if (!in_array("IBLOCK_ID", $arSelectFields))
				$arSelectFields[] = "IBLOCK_ID";
			if (!in_array("CREATED_BY", $arSelectFields))
				$arSelectFields[] = "CREATED_BY";
		}

		$arResultList = array();

		$dbTasksList = CIBlockElement::GetList(
			$arOrder,
			$arFilter,
			$arGroupBy,
			$arNavStartParams,
			$arSelectFields
		);
		while ($obTask = $dbTasksList->GetNextElement())
		{
			$arResult = array();

			$arFields = $obTask->GetFields();
			foreach ($arFields as $fieldKey => $fieldValue)
			{
				if (substr($fieldKey, 0, 1) == "~")
					continue;

				$arResult[$fieldKey] = $fieldValue;
				$arResult[$fieldKey."_PRINTABLE"] = $fieldValue;

				if (in_array($fieldKey, array("MODIFIED_BY", "CREATED_BY")))
				{
					$arResult[$fieldKey] = "user_".$fieldValue;
					$arResult[$fieldKey."_PRINTABLE"] = self::PrepareUserForPrint($fieldValue);
				}
			}

			$arProperties = $obTask->GetProperties();
			foreach ($arProperties as $propertyKey => $propertyValue)
			{
				$arResult["PROPERTY_".$propertyKey] = $propertyValue["VALUE"];
				$arResult["PROPERTY_".$propertyKey."_PRINTABLE"] = $propertyValue["VALUE"];

				if (strlen($propertyValue["USER_TYPE"]) > 0)
				{
					if ($propertyValue["USER_TYPE"] == "UserID")
					{
						if (is_array($propertyValue["VALUE"]))
						{
							$arResult["PROPERTY_".$propertyKey] = array();
							foreach ($propertyValue["VALUE"] as $v)
							{
								$v = intval($v);
								if ($v > 0)
									$arResult["PROPERTY_".$propertyKey][] = "user_".$v;
							}
						}
						else
						{
							$arResult["PROPERTY_".$propertyKey] = "";
							if (intval($propertyValue["VALUE"]) > 0)
								$arResult["PROPERTY_".$propertyKey] = "user_".intval($propertyValue["VALUE"]);
						}
						$arResult["PROPERTY_".$propertyKey."_PRINTABLE"] = self::PrepareUserForPrint($propertyValue["VALUE"]);
					}
				}
				elseif ($arField["PROPERTY_TYPE"] == "G")
				{
					$arResult["PROPERTY_".$propertyKey."_PRINTABLE"] = array();
					$vx = self::PrepareSectionForPrint($propertyValue["VALUE"], $propertyValue["LINK_IBLOCK_ID"]);
					foreach ($vx as $vx1 => $vx2)
						$arResult["PROPERTY_".$propertyKey."_PRINTABLE"][$vx1] = $vx2["NAME"];
				}
				elseif ($propertyValue["PROPERTY_TYPE"] == "L")
				{
					$arResult["PROPERTY_".$propertyKey] = array();

					$arPropertyValue = $propertyValue["VALUE"];
					$arPropertyKey = $propertyValue["VALUE_ENUM_ID"];
					if (!is_array($arPropertyValue))
					{
						$arPropertyValue = array($arPropertyValue);
						$arPropertyKey = array($arPropertyKey);
					}

					for ($i = 0, $cnt = count($arPropertyValue); $i < $cnt; $i++)
						$arResult["PROPERTY_".$propertyKey][$arPropertyKey[$i]] = $arPropertyValue[$i];

					$arResult["PROPERTY_".$propertyKey."_PRINTABLE"] = $arResult["PROPERTY_".$propertyKey];
				}
			}

			$arResultList[] = $arResult;
		}

		$dbTasksList1 = new CDBResult();
		$dbTasksList1->InitFromArray($arResultList);

		return array($dbTasksList1, $dbTasksList);
	}

	private function PrepareUserForPrint($value)
	{
		$arReturn = array();

		$valueTmp = $value;
		if (!is_array($valueTmp))
			$valueTmp = array($valueTmp);

		foreach ($valueTmp as $val)
		{
			$dbUser = CUser::GetByID($val);
			if ($arUser = $dbUser->GetNext())
			{
				$name = trim($arUser["NAME"]);
				$lastName = trim($arUser["LAST_NAME"]);
				$login = trim($arUser["LOGIN"]);

				$formatName = $name;
				if (strlen($formatName) > 0 && StrLen($lastName) > 0)
					$formatName .= " ";
				$formatName .= $lastName;
				if (strlen($formatName) <= 0)
					$formatName = $login;

				$arReturn[] = $formatName." <".$arUser["EMAIL"]."> [".$arUser["ID"]."]";
			}
		}

		return (is_array($value) ? $arReturn : ((count($arReturn) > 0) ? $arReturn[0] : ""));
	}

	private function PrepareSectionForPrint($value, $iblockId = 0)
	{
		if ($iblockId <= 0)
			$iblockId = COption::GetOptionInt("intranet", "iblock_tasks", 0);
		if ($iblockId <= 0)
			return false;

		$arReturn = array();

		$valueTmp = $value;
		if (!is_array($valueTmp))
			$valueTmp = array($valueTmp);

		foreach ($valueTmp as $val)
		{
			$ar = array();

			$dbSectionsList = CIBlockSection::GetNavChain($iblockId, $val);
			while ($arSection = $dbSectionsList->GetNext())
				$ar[$arSection["ID"]] = array("NAME" => $arSection["NAME"], "XML_ID" => $arSection["XML_ID"]);

			$arReturn[] = $ar;
		}

		return (is_array($value) ? $arReturn : ((count($arReturn) > 0) ? $arReturn[0] : array()));
	}

	/**
	* Метод по коду документа возвращает ссылку на страницу документа в административной части.
	*
	* @param string $documentId - код документа.
	* @return string - ссылка на страницу документа в административной части.
	*/
	public function GetDocumentAdminPage($documentId)
	{
		return null;

		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new CBPArgumentNullException("documentId");

		$db = CIBlockElement::GetList(
			array(),
			array("ID" => $documentId, "SHOW_NEW"=>"Y"),
			false,
			false,
			array("ID", "IBLOCK_ID", "IBLOCK_TYPE_ID")
		);
		if ($ar = $db->Fetch())
			return "/bitrix/admin/iblock_element_edit.php?view=Y&ID=".$documentId."&IBLOCK_ID=".$ar["IBLOCK_ID"]."&type=".$ar["IBLOCK_TYPE_ID"];

		return null;
	}

	public function GetDocument($documentId)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new CBPArgumentNullException("documentId");

		$arResult = null;

		$dbDocumentList = CIBlockElement::GetList(
			array(),
			array("ID" => $documentId, "SHOW_NEW" => "Y")
		);
		if ($objDocument = $dbDocumentList->GetNextElement())
		{
			$arDocumentFields = $objDocument->GetFields();
			$arDocumentProperties = $objDocument->GetProperties();

			foreach ($arDocumentFields as $fieldKey => $fieldValue)
			{
				if (substr($fieldKey, 0, 1) == "~")
					continue;

				$arResult[$fieldKey] = $fieldValue;
				if (in_array($fieldKey, array("MODIFIED_BY", "CREATED_BY")))
				{
					$arResult[$fieldKey] = "user_".$fieldValue;
					$arResult[$fieldKey."_PRINTABLE"] = $arDocumentFields[($fieldKey == "MODIFIED_BY") ? "USER_NAME" : "CREATED_USER_NAME"];
				}
			}

			foreach ($arDocumentProperties as $propertyKey => $propertyValue)
			{
				if (strlen($propertyValue["USER_TYPE"]) > 0)
				{
					if ($propertyValue["USER_TYPE"] == "UserID")
					{
						if (!is_array($propertyValue["VALUE"]))
						{
							$db = CUser::GetByID($propertyValue["VALUE"]);
							if ($ar = $db->GetNext())
							{
								$arResult["PROPERTY_".$propertyKey] = "user_".intval($propertyValue["VALUE"]);
								$arResult["PROPERTY_".$propertyKey."_PRINTABLE"] = "(".$ar["LOGIN"].")".((strlen($ar["NAME"]) > 0 || strlen($ar["LAST_NAME"]) > 0) ? " " : "").$ar["NAME"].((strlen($ar["NAME"]) > 0 && strlen($ar["LAST_NAME"]) > 0) ? " " : "").$ar["LAST_NAME"];
							}
						}
						else
						{
							for ($i = 0, $cnt = count($propertyValue["VALUE"]); $i < $cnt; $i++)
							{
								$db = CUser::GetByID($propertyValue["VALUE"][$i]);
								if ($ar = $db->GetNext())
								{
									$arResult["PROPERTY_".$propertyKey][] = "user_".intval($propertyValue["VALUE"][$i]);
									$arResult["PROPERTY_".$propertyKey."_PRINTABLE"][$propertyValue["VALUE"][$i]] = "(".$ar["LOGIN"].")".((strlen($ar["NAME"]) > 0 || strlen($ar["LAST_NAME"]) > 0) ? " " : "").$ar["NAME"].((strlen($ar["NAME"]) > 0 && strlen($ar["LAST_NAME"]) > 0) ? " " : "").$ar["LAST_NAME"];
								}
							}
						}
					}
					else
					{
						$arResult["PROPERTY_".$propertyKey] = $propertyValue["VALUE"];
					}
				}
				elseif ($propertyValue["PROPERTY_TYPE"] == "L")
				{
					$arPropertyValue = $propertyValue["VALUE"];
					$arPropertyKey = $propertyValue["VALUE_XML_ID"];
					if (!is_array($arPropertyValue))
					{
						$arPropertyValue = array($arPropertyValue);
						$arPropertyKey = array($arPropertyKey);
					}

					for ($i = 0, $cnt = count($arPropertyValue); $i < $cnt; $i++)
						$arResult["PROPERTY_".$propertyKey][$arPropertyKey[$i]] = $arPropertyValue[$i];
				}
				elseif ($propertyValue["PROPERTY_TYPE"] == "F")
				{
					if (!is_array($propertyValue["VALUE"]))
					{
						if ((intval($propertyValue["VALUE"]) > 0) && ($ar = CFile::GetFileArray($propertyValue["VALUE"])))
						{
							$arResult["PROPERTY_".$propertyKey] = $propertyValue["VALUE"];
							$arResult["PROPERTY_".$propertyKey."_PRINTABLE"] = $ar["SRC"];
						}
					}
					else
					{
						for ($i = 0, $cnt = count($propertyValue["VALUE"]); $i < $cnt; $i++)
						{
							if ((intval($propertyValue["VALUE"][$i]) > 0) && ($ar = CFile::GetFileArray($propertyValue["VALUE"][$i])))
							{
								$arResult["PROPERTY_".$propertyKey][] = $propertyValue["VALUE"][$i];
								$arResult["PROPERTY_".$propertyKey."_PRINTABLE"][$propertyValue["VALUE"][$i]] = $ar["SRC"];
							}
						}
					}
				}
				else
				{
					$arResult["PROPERTY_".$propertyKey] = $propertyValue["VALUE"];
				}
			}
		}

		return $arResult;
	}

	public function GetDocumentType($documentId)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new CBPArgumentNullException("documentId");

		$dbResult = CIBlockElement::GetList(array(), array("ID" => $documentId, "SHOW_NEW" => "Y"), false, false, array("ID", "IBLOCK_ID"));
		$arResult = $dbResult->Fetch();
		if (!$arResult)
			throw new Exception("Element is not found");

		return "type_".$arResult["IBLOCK_ID"];
	}

	public function GetDocumentFields($documentType)
	{
		$v = substr($documentType, strlen("type_"));
		if (intval($v)."!" != $v."!")
			throw new CBPArgumentOutOfRangeException("documentType", $documentType);
		$iblockId = intval($v);

		$arDocumentFieldTypes = self::GetDocumentFieldTypes($documentType);

		$arResult = array(
			"ID" => array(
				"Name" => GetMessage("BPVDX_FIELD_ID"),
				"Type" => "N",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"TIMESTAMP_X" => array(
				"Name" => GetMessage("BPVDX_FIELD_TIMESTAMP_X"),
				"Type" => "S:DateTime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"MODIFIED_BY" => array(
				"Name" => GetMessage("BPVDX_FIELD_MODYFIED"),
				"Type" => "S:UserID",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"MODIFIED_BY_PRINTABLE" => array(
				"Name" => GetMessage("BPVDX_FIELD_MODIFIED_BY_USER_PRINTABLE"),
				"Type" => "S",
				"Filterable" => false,
				"Editable" => false,
				"Required" => false,
			),
			"DATE_CREATE" => array(
				"Name" => GetMessage("BPVDX_FIELD_DATE_CREATE"),
				"Type" => "S:DateTime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"CREATED_BY" => array(
				"Name" => GetMessage("BPVDX_FIELD_CREATED"),
				"Type" => "S:UserID",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"CREATED_BY_PRINTABLE" => array(
				"Name" => GetMessage("BPVDX_FIELD_CREATED_BY_USER_PRINTABLE"),
				"Type" => "S",
				"Filterable" => false,
				"Editable" => false,
				"Required" => false,
			),
			"IBLOCK_ID" => array(
				"Name" => GetMessage("BPVDX_FIELD_IBLOCK_ID"),
				"Type" => "N",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"ACTIVE" => array(
				"Name" => GetMessage("BPVDX_FIELD_ACTIVE"),
				"Type" => "B",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			/*"BP_PUBLISHED" => array(
				"Name" => GetMessage("BPVDX_FIELD_BP_PUBLISHED"),
				"Type" => "B",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),*/
			"ACTIVE_FROM" => array(
				"Name" => GetMessage("BPVDX_FIELD_DATE_ACTIVE_FROM"),
				"Type" => "S:DateTime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"ACTIVE_TO" => array(
				"Name" => GetMessage("BPVDX_FIELD_DATE_ACTIVE_TO"),
				"Type" => "S:DateTime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"SORT" => array(
				"Name" => GetMessage("BPVDX_FIELD_SORT"),
				"Type" => "N",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"NAME" => array(
				"Name" => GetMessage("BPVDX_FIELD_NAME"),
				"Type" => "S",
				"Filterable" => true,
				"Editable" => true,
				"Required" => true,
			),
			"PREVIEW_PICTURE" => array(
				"Name" => GetMessage("BPVDX_FIELD_PREVIEW_PICTURE"),
				"Type" => "F",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"PREVIEW_TEXT" => array(
				"Name" => GetMessage("BPVDX_FIELD_PREVIEW_TEXT"),
				"Type" => "T",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"PREVIEW_TEXT_TYPE" => array(
				"Name" => GetMessage("BPVDX_FIELD_PREVIEW_TEXT_TYPE"),
				"Type" => "L",
				"Options" => array(
					"text" => GetMessage("BPVDX_DESC_TYPE_TEXT"),
					"html" => "Html",
				),
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"DETAIL_PICTURE" => array(
				"Name" => GetMessage("BPVDX_FIELD_DETAIL_PICTURE"),
				"Type" => "F",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"DETAIL_TEXT" => array(
				"Name" => GetMessage("BPVDX_FIELD_DETAIL_TEXT"),
				"Type" => "T",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"DETAIL_TEXT_TYPE" => array(
				"Name" => GetMessage("BPVDX_FIELD_DETAIL_TEXT_TYPE"),
				"Type" => "L",
				"Options" => array(
					"text" => GetMessage("BPVDX_DESC_TYPE_TEXT"),
					"html" => "Html",
				),
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"CODE" => array(
				"Name" => GetMessage("BPVDX_FIELD_CODE"),
				"Type" => "S",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"XML_ID" => array(
				"Name" => GetMessage("BPVDX_FIELD_XML_ID"),
				"Type" => "S",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
		);

		$arKeys = array_keys($arResult);
		foreach ($arKeys as $key)
			$arResult[$key]["Multiple"] = false;

		$dbProperties = CIBlockProperty::GetList(
			array("sort" => "asc", "name" => "asc"),
			array("IBLOCK_ID" => $iblockId)
		);
		while ($arProperty = $dbProperties->Fetch())
		{
			if (strlen(trim($arProperty["CODE"])) > 0)
				$key = "PROPERTY_".$arProperty["CODE"];
			else
				$key = "PROPERTY_".$arProperty["ID"];

			$arResult[$key] = array(
				"Name" => $arProperty["NAME"],
				"Filterable" => ($arProperty["FILTRABLE"] == "Y"),
				"Editable" => true,
				"Required" => ($arProperty["IS_REQUIRED"] == "Y"),
				"Multiple" => ($arProperty["MULTIPLE"] == "Y"),
				"Type" => $arProperty["PROPERTY_TYPE"],
			);

			if (strlen($arProperty["USER_TYPE"]) > 0)
			{
				$arResult[$key]["Type"] = "S:".$arProperty["USER_TYPE"];

				if ($arProperty["USER_TYPE"] == "UserID")
				{
					$arResult[$key."_PRINTABLE"] = array(
						"Name" => $arProperty["NAME"].GetMessage("BPVDX_FIELD_USERNAME_PROPERTY"),
						"Filterable" => false,
						"Editable" => false,
						"Required" => false,
						"Multiple" => ($arProperty["MULTIPLE"] == "Y"),
						"Type" => "S",
					);
				}
			}
			elseif ($arProperty["PROPERTY_TYPE"] == "L")
			{
				$arResult[$key]["Options"] = array();
				$dbPropertyEnums = CIBlockProperty::GetPropertyEnum($arProperty["ID"]);
				while ($arPropertyEnum = $dbPropertyEnums->GetNext())
					$arResult[$key]["Options"][$arPropertyEnum["XML_ID"]] = $arPropertyEnum["VALUE"];
			}
			elseif ($arProperty["PROPERTY_TYPE"] == "S" && intval($arProperty["ROW_COUNT"]) > 1)
				$arResult[$key]["Type"] = "T";
		}

		$arKeys = array_keys($arResult);
		foreach ($arKeys as $k)
			$arResult[$k]["BaseType"] = $arDocumentFieldTypes[$arResult[$k]["Type"]]["BaseType"];

		return $arResult;
	}

	public function GetDocumentFieldTypes($documentType)
	{
		$v = substr($documentType, strlen("type_"));
		if (intval($v)."!" != $v."!")
			throw new CBPArgumentOutOfRangeException("documentType", $documentType);
		$iblockId = intval($v);

		$arResult = array(
			"S" => array("Name" => GetMessage("BPVDX_STRING"), "BaseType" => "string"),
			"T" => array("Name" => GetMessage("BPVDX_TEXT"), "BaseType" => "text"),
			"N" => array("Name" => GetMessage("BPVDX_NUM"), "BaseType" => "double"),
			"L" => array("Name" => GetMessage("BPVDX_LIST"), "BaseType" => "select"),
			"F" => array("Name" => GetMessage("BPVDX_FILE"), "BaseType" => "file"),
			//"G" => array("Name" => GetMessage("BPVDX_SECT"), "BaseType" => "int"),
			//"E" => array("Name" => GetMessage("BPVDX_ELEM"), "BaseType" => "int"),
			"B" => array("Name" => GetMessage("BPVDX_YN"), "BaseType" => "bool"),
		);

		foreach (CIBlockProperty::GetUserType() as  $ar)
		{
			$t = $ar["PROPERTY_TYPE"].":".$ar["USER_TYPE"];

			if (COption::GetOptionString("bizproc", "SkipNonPublicCustomTypes", "N") == "Y"
				&& !array_key_exists("GetPublicEditHTML", $ar) && $t != "S:UserID" && $t != "S:DateTime")
				continue;

			$arResult[$t] = array("Name" => $ar["DESCRIPTION"], "BaseType" => "string");
			if ($t == "S:UserID")
				$arResult[$t]["BaseType"] = "user";
			elseif ($t == "S:DateTime")
				$arResult[$t]["BaseType"] = "datetime";
			elseif (!array_key_exists("GetPublicEditHTML", $ar))
				continue;
		}

		return $arResult;
	}

	public function AddDocumentField($documentType, $arFields)
	{
		$iblockId = intval(substr($documentType, strlen("type_")));
		if ($iblockId <= 0)
			throw new CBPArgumentOutOfRangeException("documentType", $documentType);

		if (substr($arFields["code"], 0, strlen("PROPERTY_")) == "PROPERTY_")
			$arFields["code"] = substr($arFields["code"], strlen("PROPERTY_"));

		$arFieldsTmp = array(
			"NAME" => $arFields["name"],
			"ACTIVE" => "Y",
			"SORT" => 150,
			"CODE" => $arFields["code"],
			"MULTIPLE" => $arFields["multiple"],
			"IS_REQUIRED" => $arFields["required"],
			"IBLOCK_ID" => $iblockId,
			"FILTRABLE" => "Y",
		);

		if (array_key_exists("additional_type_info", $arFields))
			$arFieldsTmp["LINK_IBLOCK_ID"] = intval($arFields["additional_type_info"]);

		if (strstr($arFields["type"], ":") !== false)
		{
			list($arFieldsTmp["PROPERTY_TYPE"], $arFieldsTmp["USER_TYPE"]) = explode(":", $arFields["type"], 2);
		}
		else
		{
			$arFieldsTmp["PROPERTY_TYPE"] = $arFields["type"];
			$arFieldsTmp["USER_TYPE"] = false;
		}

		if ($arFieldsTmp["PROPERTY_TYPE"] == "T")
		{
			$arFieldsTmp["PROPERTY_TYPE"] = "S";
			$arFieldsTmp["ROW_COUNT"] = 5;
		}

		if (strlen($arFields["options"]) > 0)
		{
			$a = explode("\n", $arFields["options"]);
			foreach ($a as $v)
			{
				$v = trim(trim($v), "\r\n");
				$v1 = $v2 = $v;
				if (substr($v, 0, 1) == "[" && strpos($v, "]") !== false)
				{
					$v1 = substr($v, 1, strpos($v, "]") - 1);
					$v2 = trim(substr($v, strpos($v, "]") + 1));
				}
				$arFieldsTmp["VALUES"][] = array("XML_ID" => $v1, "VALUE" => $v2, "DEF" => "N");
			}
		}

		$ibp = new CIBlockProperty;
		$propId = $ibp->Add($arFieldsTmp);

		if (intval($propId) <= 0)
			throw new Exception($ibp->LAST_ERROR);

		return "PROPERTY_".$arFields["code"];
	}

	public function UpdateDocument($documentId, $arFields)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new CBPArgumentNullException("documentId");

		CIBlockElement::WF_CleanUpHistoryCopies($documentId, 0);

		$arFieldsPropertyValues = array();

		$dbResult = CIBlockElement::GetList(array(), array("ID" => $documentId, "SHOW_NEW" => "Y"), false, false, array("ID", "IBLOCK_ID"));
		$arResult = $dbResult->Fetch();
		if (!$arResult)
			throw new Exception("Element is not found");

		$arDocumentFields = self::GetDocumentFields("type_".$arResult["IBLOCK_ID"]);
		$arDocumentFieldTypes = self::GetDocumentFieldTypes("type_".$arResult["IBLOCK_ID"]);

		$arKeys = array_keys($arFields);
		foreach ($arKeys as $key)
		{
			if (!array_key_exists($key, $arDocumentFields))
				continue;

			if ($arDocumentFields[$key]["Multiple"] && is_string($arFields[$key]))
			{
				$arFieldsTmp = explode(",", $arFields[$key]);
				$arFields[$key] = array();
				foreach ($arFieldsTmp as $value)
					$arFields[$key][] = trim($value);
			}

			if ($arDocumentFieldTypes[$arDocumentFields[$key]["Type"]]["BaseType"] == "user")
			{
				$v = $arFields[$key];
				if (!is_array($v))
					$v = array($v);

				$ar = array();
				foreach ($v as $v1)
				{
					if (substr($v1, 0, strlen("user_")) == "user_")
					{
						$ar[] = substr($v1, strlen("user_"));
					}
					else
					{
						$a1 = self::GetUsersFromUserGroup($v1, $documentId);
						foreach ($a1 as $a11)
							$ar[] = $a11;
					}
				}

				$arFields[$key] = $ar;
			}
			elseif ($arDocumentFieldTypes[$arDocumentFields[$key]["Type"]]["BaseType"] == "select")
			{
				$arFieldTmp = $arFields[$key];
				if (!is_array($arFieldTmp))
					$arFieldTmp = array($arFieldTmp);

				$arFields[$key] = array();
				foreach ($arFieldTmp as $v)
				{
					$db = CIBlockPropertyEnum::GetList(array(), array("XML_ID" => $v, "IBLOCK_ID" => $arResult["IBLOCK_ID"]));
					if ($ar = $db->Fetch())
						$arFields[$key][] = $ar["ID"];
				}
			}

			if (!$arDocumentFields[$key]["Multiple"] && is_array($arFields[$key]))
			{
				if (count($arFields[$key]) > 0)
				{
					$a = array_values($arFields[$key]);
					$arFields[$key] = $a[0];
				}
				else
				{
					$arFields[$key] = null;
				}
			}

			if ($arDocumentFieldTypes[$arDocumentFields[$key]["Type"]]["BaseType"] == "file")
			{
				if (is_array($arFields[$key]))
				{
					$arFieldTmp = $arFields[$key];
					$arFields[$key] = array();
					foreach ($arFieldTmp as $v)
						$arFields[$key][] = CFile::MakeFileArray($v);
				}
				else
				{
					$arFields[$key] = array(CFile::MakeFileArray($arFields[$key]));
				}
			}

			if (substr($key, 0, strlen("PROPERTY_")) == "PROPERTY_")
			{
				$arFieldsPropertyValues[substr($key, strlen("PROPERTY_"))] = is_array($arFields[$key]) ? $arFields[$key] : array($arFields[$key]);
				unset($arFields[$key]);
			}
		}

		$iblockElement = new CIBlockElement();

		if (count($arFieldsPropertyValues) > 0)
			$iblockElement->SetPropertyValuesEx($documentId, $arResult["IBLOCK_ID"], $arFieldsPropertyValues);

		if (count($arFields) > 0)
		{
			$res = $iblockElement->Update($documentId, $arFields);
			if (!$res)
				throw new Exception($iblockElement->LAST_ERROR);
		}
	}

	public function CreateDocument($parentDocumentId, $arFields)
	{
		if (!array_key_exists("IBLOCK_ID", $arFields) || intval($arFields["IBLOCK_ID"]) <= 0)
			throw new Exception("IBlock ID is not found");

		$arFieldsPropertyValues = array();

		$arDocumentFields = self::GetDocumentFields("type_".$arFields["IBLOCK_ID"]);
		$arDocumentFieldTypes = self::GetDocumentFieldTypes("type_".$arFields["IBLOCK_ID"]);

		$arKeys = array_keys($arFields);
		foreach ($arKeys as $key)
		{
			if (!array_key_exists($key, $arDocumentFields))
				continue;

			if ($arDocumentFieldTypes[$arDocumentFields[$key]["Type"]]["BaseType"] == "user")
			{
				$v = $arFields[$key];
				if (!is_array($v))
					$v = array($v);

				$ar = array();
				foreach ($v as $v1)
				{
					if (substr($v1, 0, strlen("user_")) == "user_")
					{
						$ar[] = substr($v1, strlen("user_"));
					}
					else
					{
						$a1 = self::GetUsersFromUserGroup($v1, $documentId);
						foreach ($a1 as $a11)
							$ar[] = $a11;
					}
				}

				$arFields[$key] = $ar;
			}

			if (!$arDocumentFields[$key]["Multiple"] && is_array($arFields[$key]))
			{
				if (count($arFields[$key]) > 0)
				{
					$a = array_values($arFields[$key]);
					$arFields[$key] = $a[0];
				}
				else
				{
					$arFields[$key] = null;
				}
			}

			if (substr($key, 0, strlen("PROPERTY_")) == "PROPERTY_")
			{
				$arFieldsPropertyValues[substr($key, strlen("PROPERTY_"))] = is_array($arFields[$key]) ? $arFields[$key] : array($arFields[$key]);
				unset($arFields[$key]);
			}
		}

		if (count($arFieldsPropertyValues) > 0)
			$arFields["PROPERTY_VALUES"] = $arFieldsPropertyValues;

		$iblockElement = new CIBlockElement();
		$id = $iblockElement->Add($arFields);
		if (!$id || $id <= 0)
			throw new Exception($iblockElement->LAST_ERROR);
		return $id;
	}

	// array("read" => "Ета чтение", "write" => "Ета запысь")
	public function GetAllowableOperations($documentType)
	{
		return array("read" => GetMessage("BPVDX_OP_READ"), "create" => GetMessage("BPVDX_OP_CREATE"), "admin" => GetMessage("BPVDX_OP_ADMIN"));
	}

	// array("1" => "Админы", 2 => "Гости", 3 => ..., "Author" => "Афтар")
	public function GetAllowableUserGroups($documentType)
	{
		$documentType = trim($documentType);
		if (strlen($documentType) <= 0)
			return false;

		$iblockId = intval(substr($documentType, strlen("type_")));

		$arResult = array("Author" => GetMessage("BPVDX_DOCUMENT_AUTHOR"));

//		$arRes = array(1);
//		$arGroups = CIBlock::GetGroupPermissions($iblockId);
//		foreach ($arGroups as $groupId => $perm)
//		{
//			if ($perm > "R")
//				$arRes[] = $groupId;
//		}

		$dbGroupsList = CGroup::GetListEx(array("NAME" => "ASC"), array("ACTIVE" => "Y"));	//array("ID" => $arRes)
		while ($arGroup = $dbGroupsList->Fetch())
			$arResult[$arGroup["ID"]] = $arGroup["NAME"];

		return $arResult;
	}

	public function GetUsersFromUserGroup($group, $documentId)
	{
		if (strtolower($group) == "author")
		{
			$documentId = intval($documentId);
			if ($documentId <= 0)
				return array();

			$db = CIBlockElement::GetList(array(), array("ID" => $documentId, "SHOW_NEW"=>"Y"), false, false, array("ID", "IBLOCK_ID", "CREATED_BY"));
			if ($ar = $db->Fetch())
				return array($ar["CREATED_BY"]);

			return array();
		}

		$group = intval($group);
		if ($group <= 0)
			return array();

		$arResult = array();

		$dbUsersList = CUser::GetList(($b = "ID"), ($o = "ASC"), array("GROUPS_ID" => $group, "ACTIVE" => "Y"));
		while ($arUser = $dbUsersList->Fetch())
			$arResult[] = $arUser["ID"];

		return $arResult;
	}

	public function GetJSFunctionsForFields($documentType, $objectName, $arDocumentFields = array(), $arDocumentFieldTypes = array())
	{
		$iblockId = intval(substr($documentType, strlen("type_")));
		if ($iblockId <= 0)
			return "";

		if (!is_array($arDocumentFields) || count($arDocumentFields) <= 0)
			$arDocumentFields = self::GetDocumentFields($documentType);
		if (!is_array($arDocumentFieldTypes) || count($arDocumentFieldTypes) <= 0)
			$arDocumentFieldTypes = self::GetDocumentFieldTypes($documentType);

		ob_start();

		echo CAdminCalendar::ShowScript();
		?>
		<script type="text/javascript">
		var <?= $objectName ?> = {};
		<?
		$str = "";
		foreach ($arDocumentFields as $fieldKey => $arFieldValue)
		{
			if (strlen($str) > 0)
				$str .= ",";

			$str .= "'".str_replace("'", "\'", $fieldKey)."':{";

			$str .= "'Name':'".CUtil::JSEscape($arFieldValue["Name"])."',";
			$str .= "'Type':'".CUtil::JSEscape($arFieldValue["Type"])."',";
			$str .= "'Multiple':'".CUtil::JSEscape($arFieldValue["Multiple"] ? "Y" : "N")."',";

			$str .= "'Options':{";
			if (array_key_exists("Options", $arFieldValue) && count($arFieldValue["Options"]) > 0)
			{
				$ix = 0;
				foreach ($arFieldValue["Options"] as $k => $v)
				{
					if ($ix > 0)
						$str .= ",";
					$str .= $ix.":{0:'".CUtil::JSEscape($k)."',1:'".CUtil::JSEscape($v)."'}";
					$ix++;
				}
			}
			$str .= "}";

			$str .= "}";
		}
		?>
		<?= $objectName ?>.arDocumentFields = {<?= $str ?>};

		<?
		$str = "";
		$ind = -1;
		foreach ($arDocumentFieldTypes as $typeKey => $arTypeValue)
		{
			$ind++;
			if (strlen($str) > 0)
				$str .= ",";

			$str .= "'".CUtil::JSEscape($typeKey)."':{";

			$str .= "'Name':'".CUtil::JSEscape($arTypeValue["Name"])."',";
			$str .= "'BaseType':'".CUtil::JSEscape($arTypeValue["BaseType"])."',";
			$str .= "'Index':".$ind."";

			$str .= "}";
		}
		?>
		<?= $objectName ?>.arFieldTypes = {<?= $str ?>};

		<?= $objectName ?>.AddField = function(fldCode, fldName, fldType, fldMultiple, fldOptions)
		{
			this.arDocumentFields[fldCode] = {};
			this.arDocumentFields[fldCode]["Name"] = fldName;
			this.arDocumentFields[fldCode]["Type"] = fldType;
			this.arDocumentFields[fldCode]["Multiple"] = fldMultiple;
			this.arDocumentFields[fldCode]["Options"] = {};

			var i = -1;
			for (k in fldOptions)
			{
				i = i + 1;
				this.arDocumentFields[fldCode]["Options"][i] = {};
				this.arDocumentFields[fldCode]["Options"][i][0] = k;
				this.arDocumentFields[fldCode]["Options"][i][1] = fldOptions[k];
			}
		}

		<?= $objectName ?>.GetGUIFieldEdit = function(field, value, showAddButton, inputName)
		{
			if (!this.arDocumentFields[field])
				return "";

			if (typeof showAddButton == "undefined")
				showAddButton = false;

			if (typeof inputName == "undefined")
				inputName = field;

			var type = this.arDocumentFields[field]["Type"];

			var bAddSelection = false;
			var bAddButton = true;

			s = "";
			if (type == "N")
			{
				s += '<input type="text" size="10" id="id_' + field + '" name="' + inputName + '" value="' + this.HtmlSpecialChars(value) + '">';
			}
			else if (type == "L")
			{
				s += '<select name="' + inputName + '_1">';
				s += '<option value=""></option>';
				for (k in this.arDocumentFields[field]["Options"])
				{
					s += '<option value="' + this.arDocumentFields[field]["Options"][k][0] + '"' + (value == this.arDocumentFields[field]["Options"][k][0] ? " selected" : "") + '>' + this.arDocumentFields[field]["Options"][k][1] + '</option>';
					if (value == this.arDocumentFields[field]["Options"][k][0])
						value = "";
				}
				s += '</select>';
				bAddSelection = true;
			}
			else if (type == "F")
			{
				s += '<input type="file" id="id_' + field + '_1" name="' + inputName + '">';
				bAddSelection = true;
				bAddButton = true;
			}
			else if (type == "B")
			{
				s += '<select name="' + inputName + '_1" id="id_' + name + '">';
				s += '<option value=""></option>';
				s += '<option value="Y"' + (value == "Y" ? " selected" : "") + '><?= GetMessage("BPVDX_YES") ?></option>';
				s += '<option value="N"' + (value == "N" ? " selected" : "") + '><?= GetMessage("BPVDX_NO") ?></option>';
				s += '</select>';
				bAddSelection = true;
				if (value == "Y" || value == "N")
					value = "";
			}
			else if (type == "S:DateTime")
			{
				s += '<span style="white-space:nowrap;">';
				s += '<input type="text" name="' + inputName + '" id="id_' + field + '" size="10" value="' + this.HtmlSpecialChars(value) + '">';
				s += '<a href="javascript:void(0);" title="<?= GetMessage("BPVDX_CALENDAR") ?>">';
				s += '<img src="<?= ADMIN_THEMES_PATH ?>/<?= ADMIN_THEME_ID ?>/images/calendar/icon.gif" alt="<?= GetMessage("BPVDX_CALENDAR") ?>" class="calendar-icon" onclick="jsAdminCalendar.Show(this, \'' + inputName + '\', \'\', \'\', ' + ((type == "datetime") ? 'true' : 'false') + ', <?= time() + date("Z") ?>);" onmouseover="this.className+=\' calendar-icon-hover\';" onmouseout="this.className = this.className.replace(/\s*calendar-icon-hover/ig, \'\');">';
				s += '</a></span>';
			}
			//else if (type.substr(0, 2) == "S:" && this.arUserTypes[type.substr(2)])
			//{
			//	s += eval(this.arUserTypes[type.substr(2)] + "(\"" + field + "\", \"" + value + "\")");
			//}
			else // type == "S"
			{
				s += '<input type="text" size="40" id="id_' + field + '" name="' + inputName + '" value="' + this.HtmlSpecialChars(value) + '">';
			}

			if (bAddSelection)
				s += '<br /><input type="text" id="id_' + field + '" name="' + inputName + '" value="' + this.HtmlSpecialChars(value) + '">';

			if (bAddButton && showAddButton)
				s += '<input type="button" value="..." onclick="BPAShowSelector(\'id_' + field + '\', \'' + type + '\');">';

			return s;
		}

		<?= $objectName ?>.SetGUIFieldEdit = function(field)
		{
		}

		<?= $objectName ?>.GetGUIFieldEditSimple = function(type, value, name)
		{
			if (typeof name == "undefined" || name.length <= 0)
				name = "BPVDDefaultValue";

			if (typeof value == "undefined")
			{
				value = "";

				var obj = document.getElementById('id_' + name);
				if (obj)
				{
					if (obj.type.substr(0, "select".length) == "select")
						value = obj.options[obj.selectedIndex].value;
					else
						value = obj.value;
				}
			}

			s = "";
			if (type == "F")
			{
				s += '';
			}
			else if (type == "B")
			{
				s += '<select name="' + name + '" id="id_' + name + '">';
				s += '<option value=""></option>';
				s += '<option value="Y"' + (value == "Y" ? " selected" : "") + '><?= GetMessage("BPVDX_YES") ?></option>';
				s += '<option value="N"' + (value == "N" ? " selected" : "") + '><?= GetMessage("BPVDX_NO") ?></option>';
				s += '</select>';
			}
			else if (type == "S:UserID")
			{
				s += '<input type="text" size="10" id="id_' + name + '" name="' + name + '" value="' + this.HtmlSpecialChars(value) + '">';
				s += '<input type="button" value="..." onclick="BPAShowSelector(\'id_' + name + '\', \'user\')">';
			}
			else
			{
				s += '<input type="text" size="10" id="id_' + name + '" name="' + name + '" value="' + this.HtmlSpecialChars(value) + '">';
			}

			return s;
		}

		<?= $objectName ?>.SetGUIFieldEditSimple = function(type, name)
		{
			if (typeof name == "undefined" || name.length <= 0)
				name = "BPVDDefaultValue";

			s = "";
			if (type != "F")
			{
				var obj = document.getElementById('id_' + name);
				if (obj)
				{
					if (obj.type.substr(0, "select".length) == "select")
						s = obj.options[obj.selectedIndex].value;
					else
						s = obj.value;
				}
			}

			return s;
		}

		<?= $objectName ?>.GetGUITypeEdit = function(type)
		{
			return "";
		}

		<?= $objectName ?>.SetGUITypeEdit = function(type)
		{
			return "";
		}

		<?= $objectName ?>.HtmlSpecialChars = function(string, quote)
		{
			string = string.toString();
			string = string.replace(/&/g, '&amp;');
			string = string.replace(/</g, '&lt;');
			string = string.replace(/>/g, '&gt;');
			string = string.replace(/"/g, '&quot;');

			if (quote)
				string = string.replace(/'/g, '&#039;');

			return string;
		}

		</script>
		<?

		$str = ob_get_contents();
		ob_end_clean();

		return $str;
	}

	function GetGUIFieldEdit($documentType, $formName, $fieldName, $fieldValue, $arDocumentField = null, $bAllowSelection = false)
	{
		$v = substr($documentType, strlen("type_"));
		if (intval($v)."!" != $v."!")
			return "";
		$iblockId = intval($v);

		$fieldName = preg_replace("#[^a-zA-Z0-9_]+#i", "", $fieldName);

		if (!is_array($arDocumentField) || count($arDocumentField) <= 0)
		{
			$arDocumentFields = self::GetDocumentFields($documentType);
			$arDocumentField = $arDocumentFields[$fieldName];
		}

		if (!is_array($arDocumentField) || count($arDocumentField) <= 0)
			return "";

		$fieldType = $arDocumentField["Type"];

		$customMethodName = "";
		if (substr($fieldType, 0, 2) == "S:")
		{
			$ar = CIBlockProperty::GetUserType(substr($fieldType, 2));
			if (array_key_exists("GetPublicEditHTML", $ar))
				$customMethodName = $ar["GetPublicEditHTML"];
		}

		if (!is_array($fieldValue))
			$fieldValue = array($fieldValue);

		if (!array_key_exists("CBPVirtualDocumentAddShowParameterInit_".$documentType, $GLOBALS))
		{
			$GLOBALS["CBPVirtualDocumentAddShowParameterInit_".$documentType] = 1;
			CBPDocument::AddShowParameterInit("bizproc", "only_users", $documentType, "CBPVirtualDocument");
		}

		ob_start();

		if ($fieldType == "L")
		{
			$fieldNameId = 'id_'.$fieldName;
			$fieldNameName = $fieldName.($arDocumentField["Multiple"] ? "[]" : "");
			$fieldValueTmp = $fieldValue;
			?>
			<select id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>"<?= ($arDocumentField["Multiple"] ? ' size="5" multiple' : '') ?>>
				<?
				if (!$arDocumentField["Required"])
					echo '<option value="">['.GetMessage("BPVDX_NOT_SET").']</option>';
				foreach ($arDocumentField["Options"] as $k => $v)
				{
					$ind = array_search($k, $fieldValueTmp);
					echo '<option value="'.htmlspecialchars($k).'"'.($ind !== false ? ' selected' : '').'>'.htmlspecialchars($v).'</option>';
					if ($ind !== false)
						unset($fieldValueTmp[$ind]);
				}
				?>
			</select>
			<?
			if ($bAllowSelection)
			{
				?>
				<br /><input type="text" id="<?= $fieldNameId ?>_1" name="<?= $fieldName ?>_1" value="<?
				if (count($fieldValueTmp) > 0)
				{
					$a = array_values($fieldValueTmp);
					echo $a[0];
				}
				?>">
				<input type="button" value="..." onclick="BPAShowSelector('<?= $fieldNameId ?>_1', 'select');">
				<?
			}
		}
		elseif ($fieldType == "S:UserID")
		{
			$fieldNameId = 'id_'.$fieldName;
			$fieldNameName = $fieldName;
			$fieldValue = CBPHelper::UsersArrayToString($fieldValue, null, array("bizproc", "CBPVirtualDocument", $documentType));
			?><input type="text" size="40" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>" value="<?= htmlspecialchars($fieldValue) ?>"><input type="button" value="..." onclick="BPAShowSelector('<?= $fieldNameId ?>', 'user');"><?
		}
		else
		{
			if (!array_key_exists("CBPVirtualDocumentCloneRowPrinted_".$documentType, $GLOBALS) && $arDocumentField["Multiple"])
			{
				$GLOBALS["CBPVirtualDocumentCloneRowPrinted_".$documentType] = 1;
				?>
				<script language="JavaScript">
				<!--
				function CBPVirtualDocumentCloneRow(tableID)
				{
					var tbl = document.getElementById(tableID);
					var cnt = tbl.rows.length;
					var oRow = tbl.insertRow(cnt);
					var oCell = oRow.insertCell(0);
					var sHTML = tbl.rows[cnt - 1].cells[0].innerHTML;
					var p = 0;
					while (true)
					{
						var s = sHTML.indexOf('[n', p);
						if (s < 0)
							break;
						var e = sHTML.indexOf(']', s);
						if (e < 0)
							break;
						var n = parseInt(sHTML.substr(s + 2, e - s));
						sHTML = sHTML.substr(0, s) + '[n' + (++n) + ']' + sHTML.substr(e + 1);
						p = s + 1;
					}
					var p = 0;
					while (true)
					{
						var s = sHTML.indexOf('__n', p);
						if (s < 0)
							break;
						var e = sHTML.indexOf('_', s + 2);
						if (e < 0)
							break;
						var n = parseInt(sHTML.substr(s + 3, e - s));
						sHTML = sHTML.substr(0, s) + '__n' + (++n) + '_' + sHTML.substr(e + 1);
						p = e + 1;
					}
					oCell.innerHTML = sHTML;
					var patt = new RegExp('<' + 'script' + '>[^\000]*?<' + '\/' + 'script' + '>', 'ig');
					var code = sHTML.match(patt);
					if (code)
					{
						for (var i = 0; i < code.length; i++)
						{
							if (code[i] != '')
							{
								var s = code[i].substring(8, code[i].length - 9);
								jsUtils.EvalGlobal(s);
							}
						}
					}
				}
				//-->
				</script>
				<?
			}

			if ($arDocumentField["Multiple"])
				echo '<table width="100%" border="0" cellpadding="2" cellspacing="2" id="CBPVirtualDocument_'.$fieldName.'_Table">';

			$ind = -1;
			foreach ($fieldValue as $value)
			{
				$ind++;
				$fieldNameId = 'id_'.$fieldName.'__n'.$ind.'_';
				$fieldNameName = $fieldName.($arDocumentField["Multiple"] ? "[n".$ind."]" : "");

				if ($arDocumentField["Multiple"])
					echo '<tr><td>';

				if (is_array($customMethodName) && count($customMethodName) > 0 || !is_array($customMethodName) && strlen($customMethodName) > 0)
				{
					echo call_user_func_array(
						$customMethodName,
						array(
							array(),
							array("VALUE" => $value),
							array(
								"FORM_NAME" => $formName,
								"VALUE" => $fieldNameName
							)
						)
					);
				}
				else
				{
					switch ($fieldType)
					{
						case "N":
							?><input type="text" size="10" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>" value="<?= htmlspecialchars($value) ?>"><?
							break;
						case "F":
							?><input type="file" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>"><?
							break;
						case "B":
							?>
							<select id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>">
								<?
								if (!$arDocumentField["Required"])
									echo '<option value="">['.GetMessage("BPVDX_NOT_SET").']</option>';
								?>
								<option value="Y"<?= (in_array("Y", $fieldValue) ? ' selected' : '') ?>><?= GetMessage("BPVDX_YES") ?></option>
								<option value="N"<?= (in_array("N", $fieldValue) ? ' selected' : '') ?>><?= GetMessage("BPVDX_NO") ?></option>
							</select>
							<?
							break;
						case "T":
							?><textarea rows="5" cols="40" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>"><?= htmlspecialchars($value) ?></textarea><?
							break;
						default:
							?><input type="text" size="40" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>" value="<?= htmlspecialchars($value) ?>"><?
					}
				}

				if ($bAllowSelection)
				{
					if (!in_array($fieldType, array("F", "B")) && strlen($customMethodName) <= 0)
					{
						?><input type="button" value="..." onclick="BPAShowSelector('<?= $fieldNameId ?>', '<?= $arDocumentField["BaseType"] ?>');"><?
					}
				}

				if ($arDocumentField["Multiple"])
					echo '</td></tr>';
			}

			if ($arDocumentField["Multiple"])
				echo "</table>";

			if ($arDocumentField["Multiple"])
				echo '<input type="button" value="'.GetMessage("BPVDX_ADD").'" onclick="CBPVirtualDocumentCloneRow(\'CBPVirtualDocument_'.$fieldName.'_Table\')"/><br />';

			if ($bAllowSelection)
			{
				if (in_array($fieldType, array("F", "B")))
				{
					?>
					<input type="text" id="id_<?= $fieldName ?>_1" name="<?= $fieldName ?>_1" value="<?
					if (count($fieldValueTmp) > 0)
					{
						$a = array_values($fieldValueTmp);
						echo $a[0];
					}
					?>">
					<input type="button" value="..." onclick="BPAShowSelector('id_<?= $fieldNameId ?>_1', '<?= $arDocumentField["BaseType"] ?>');">
					<?
				}
			}
		}

		$s = ob_get_contents();
		ob_end_clean();

		return $s;
	}

	function SetGUIFieldEdit($documentType, $fieldName, $arRequest, &$arErrors, $arDocumentField = null)
	{
		$v = substr($documentType, strlen("type_"));
		if (intval($v)."!" != $v."!")
			return null;
		$iblockId = intval($v);

		$fieldName = preg_replace("#[^a-zA-Z0-9_]+#i", "", $fieldName);

		if (!is_array($arDocumentField) || count($arDocumentField) <= 0)
		{
			$arDocumentFields = self::GetDocumentFields($documentType);
			$arDocumentField = $arDocumentFields[$fieldName];
		}

		if (!is_array($arDocumentField) || count($arDocumentField) <= 0)
			return "";

		$fieldType = $arDocumentField["Type"];

		$result = null;

		$customCheckFieldsMethod = "";
		if (substr($fieldType, 0, 2) == "S:")
		{
			$arCustomType = CIBlockProperty::GetUserType(substr($fieldType, 2));
			if (array_key_exists("CheckFields", $arCustomType))
				$customCheckFieldsMethod = $arCustomType["CheckFields"];
		}

		if ($fieldType == "S:UserID")
		{
			$value = $arRequest[$fieldName];
			if (strlen($value) > 0)
			{
				$result = CBPHelper::UsersStringToArray($value, array("bizproc", "CBPVirtualDocument", $documentType), $arErrors);
				if (count($arErrors) > 0)
				{
					$result = ($arDocumentField["Multiple"]) ? array() : null;
					foreach ($arErrors as $e)
						$arErrors[] = $e;
				}
			}
		}
		elseif (array_key_exists($fieldName, $arRequest))
		{
			$arValue = $arRequest[$fieldName];
			if (!is_array($arValue) || is_array($arValue) && ($fieldType == "F") && array_key_exists("name", $arValue))
				$arValue = array($arValue);

			foreach ($arValue as $value)
			{
				if ($fieldType == "N")
				{
					if (strlen($value) > 0)
					{
						$value = str_replace(" ", "", str_replace(",", ".", $value));
						if ($value."|" == doubleval($value)."|")
						{
							$value = doubleval($value);
						}
						else
						{
							$value = null;
							$arErrors[] = array(
								"code" => "ErrorValue",
								"message" => str_replace("#NAME#", $arDocumentField["Name"], GetMessage("BPCGWTL_INVALID1")),
								"parameter" => $fieldName,
							);
						}
					}
					else
					{
						$value = null;
					}
				}
				elseif ($fieldType == "L")
				{
					if (!is_array($arDocumentField["Options"]) || count($arDocumentField["Options"]) <= 0 || strlen($value) <= 0)
					{
						$value = null;
					}
					elseif (!array_key_exists($value, $arDocumentField["Options"]))
					{
						$value = null;
						$arErrors[] = array(
							"code" => "ErrorValue",
							"message" => str_replace("#NAME#", $arDocumentField["Name"], GetMessage("BPCGWTL_INVALID3")),
							"parameter" => $fieldName,
						);
					}
				}
				elseif ($fieldType == "B")
				{
					if ($value !== "Y" && $value !== "N")
					{
						if ($value === true)
						{
							$value = "Y";
						}
						elseif ($value === false)
						{
							$value = "N";
						}
						elseif (strlen($value) > 0)
						{
							$value = strtolower($value);
							if (in_array($value, array("y", "yes", "true", "1")))
							{
								$value = "Y";
							}
							elseif (in_array($value, array("n", "no", "false", "0")))
							{
								$value = "N";
							}
							else
							{
								$value = null;
								$arErrors[] = array(
									"code" => "ErrorValue",
									"message" => str_replace("#NAME#", $arDocumentField["Name"], GetMessage("BPCGWTL_INVALID4")),
									"parameter" => $fieldName,
								);
							}
						}
						else
						{
							$value = null;
						}
					}
				}
				elseif ($fieldType == "F")
				{
					if (array_key_exists("name", $value) && strlen($value["name"]) > 0)
					{
						$value = CFile::SaveFile($value, "bizproc_wf", true, true);
						if (!$value)
						{
							$value = null;
							$arErrors[] = array(
								"code" => "ErrorValue",
								"message" => str_replace("#NAME#", $arDocumentField["Name"], GetMessage("BPCGWTL_INVALID91")),
								"parameter" => $fieldName,
							);
						}
					}
					else
					{
						$value = null;
					}
				}
				elseif (substr($fieldType, 0, 2) == "S:" && (is_array($customCheckFieldsMethod) && count($customCheckFieldsMethod) > 0 || !is_array($customCheckFieldsMethod) && strlen($customCheckFieldsMethod) > 0))
				{
					$arErrorsTmp1 = call_user_func_array(
						$customCheckFieldsMethod,
						array(
							array(),
							array("VALUE" => $value)
						)
					);
					if (count($arErrorsTmp1) > 0)
					{
						$value = null;
						foreach ($arErrorsTmp1 as $e)
							$arErrors[] = array(
								"code" => "ErrorValue",
								"message" => $e,
								"parameter" => $fieldName,
							);
					}
				}
				else
				{
					if (strlen($value) <= 0)
						$value = null;
				}

				if ($arDocumentField["Multiple"])
				{
					if (!is_array($result))
						$result = array();

					if ($value != null)
						$result[] = $value;
				}
				else
				{
					$result = $value;
					break;
				}
			}
		}
		else
		{
			$result = ($arDocumentField["Multiple"]) ? array() : null;
		}

//		if ($arDocumentField["Required"] && ($arDocumentField["Multiple"] && count($result) <= 0 || !$arDocumentField["Multiple"] && $result == null))
//		{
//			$arErrors[] = array(
//				"code" => "RequiredValue",
//				"message" => str_replace("#NAME#", $arDocumentField["Name"], GetMessage("BPCGWTL_INVALID8")),
//				"parameter" => $fieldName,
//			);
//		}

		return $result;
	}

	function GetFieldValuePrintable($documentId, $fieldName, $fieldType, $fieldValue, $arFieldType)
	{
		$result = $fieldValue;

		switch ($fieldType)
		{
			case "S:UserID":
				if (is_array($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $r)
						$result[] = CBPHelper::ConvertUserToPrintableForm($r);
				}
				else
				{
					$result = CBPHelper::ConvertUserToPrintableForm($fieldValue);
				}
				break;

			case "B":
				if (is_array($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $r)
						$result[] = ((strtoupper($r) == "Y") ? GetMessage("BPVDX_YES") : GetMessage("BPVDX_NO"));
				}
				else
				{
					$result = ((strtoupper($fieldValue) == "Y") ? GetMessage("BPVDX_YES") : GetMessage("BPVDX_NO"));
				}
				break;

			case "F":
				if (is_array($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $r)
					{
						$r = intval($r);
						$dbImg = CFile::GetByID($r);
						if ($arImg = $dbImg->Fetch())
							$result[] = "[url=/bitrix/bizproc_show_file.php?f=".htmlspecialchars($arImg["FILE_NAME"])."&i=".$r."]".htmlspecialchars($arImg["ORIGINAL_NAME"])."[/url]";
					}
				}
				else
				{
					$fieldValue = intval($fieldValue);
					$dbImg = CFile::GetByID($fieldValue);
					if ($arImg = $dbImg->Fetch())
						$result = "[url=/bitrix/bizproc_show_file.php?f=".htmlspecialchars($arImg["FILE_NAME"])."&i=".$fieldValue."]".htmlspecialchars($arImg["ORIGINAL_NAME"])."[/url]";
				}
				break;
			case "L":
				if (isset($arFieldType["Options"][$fieldValue]))
					$result = $arFieldType["Options"][$fieldValue];

				break;
		}

		return $result;
	}
}
?>