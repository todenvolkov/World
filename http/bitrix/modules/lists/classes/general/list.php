<?
IncludeModuleLangFile(__FILE__);

class CList
{
	var $fields = null;
	var $new_ids = array();

	function __construct($iblock_id)
	{
		$this->fields = new CListFieldList($iblock_id);
	}

	function is_field($type_id)
	{
		return CListFieldTypeList::IsField($type_id);
	}

	function is_readonly($field_id)
	{
		$obField = $this->fields->GetByID($field_id);
		if(is_object($obField))
			return $obField->IsReadonly();
		else
			return false;
	}

	function GetFields()
	{
		static $arFields = false;
		if(!$arFields)
		{
			foreach($this->fields->GetFields() as $FIELD_ID)
				$arFields[$FIELD_ID] = $this->fields->GetArrayByID($FIELD_ID);
		}
		return $arFields;
	}

	function GetAllTypes()
	{
		return CListFieldTypeList::GetTypesNames();
	}

	function GetAvailableTypes($ID = "")
	{
		$arTypeNames = CListFieldTypeList::GetTypesNames();
		foreach($this->fields->GetFields() as $FIELD_ID)
			if($FIELD_ID != $ID)
				unset($arTypeNames[$FIELD_ID]);
		return $arTypeNames;
	}

	function DeleteField($field_id)
	{
		return $this->fields->DeleteField($field_id);
	}

	function AddField($arFields)
	{
		return $this->fields->AddField($arFields);
	}

	function GetNewID($TEMP_ID)
	{
		return $this->new_ids[$TEMP_ID];
	}

	function UpdateField($field_id, $arFields)
	{
		$arFields["NAME"] = trim($arFields["NAME"], " \n\r\t");
		$this->new_ids[$field_id] = $this->fields->UpdateField($field_id, $arFields);
		return $this->new_ids[$field_id];
	}

	function Save()
	{
	}

	function UpdatePropertyList($prop_id, $list)
	{
		foreach($list as $id => $arEnum)
		{
			$value = trim($arEnum["VALUE"], " \t\n\r");
			if(strlen($value))
			{
				$dbEnum = CIBlockPropertyEnum::GetByID($id);
				if(is_array($dbEnum))
				{
					$def = isset($arEnum["DEF"])? $arEnum["DEF"]: $dbEnum["DEF"];
					$sort = intval($arEnum["SORT"]);
					if(
						$dbEnum["VALUE"] != $value
						|| $dbEnum["SORT"] != $sort
						|| $dbEnum["DEF"] != $def
					)
					{
						$dbEnum["VALUE"] = $value;
						$dbEnum["SORT"] = $sort;
						$dbEnum["DEF"] = $def;
						CIBlockPropertyEnum::Update($id, $dbEnum);
					}
				}
				else
				{
					$arEnum["PROPERTY_ID"] = $prop_id;
					CIBlockPropertyEnum::Add($arEnum);
				}
			}
			else
			{
				CIBlockPropertyEnum::Delete($id);
			}
		}
	}
}
?>