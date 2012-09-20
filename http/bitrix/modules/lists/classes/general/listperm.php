<?
IncludeModuleLangFile(__FILE__);

class CListPermissions
{
	const WRONG_IBLOCK_TYPE = -1;
	const WRONG_IBLOCK = -2;
	const LISTS_FOR_SONET_GROUP_DISABLED = -3;

	const ACCESS_DENIED = 'D';
	const CAN_READ = 'R';
	const CAN_BIZPROC = 'U';
	const CAN_WRITE = 'W';
	const IS_ADMIN = 'X';

	static public function CheckAccess($USER, $iblock_type_id, $iblock_id = false, $socnet_group_id = 0)
	{
		if($socnet_group_id > 0 && CModule::IncludeModule('socialnetwork'))
		{
			if(CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $socnet_group_id, "group_lists"))
			{
				if($iblock_id !== false)
					return CListPermissions::_socnet_check($USER, $iblock_type_id, $iblock_id, intval($socnet_group_id));
				else
					return CListPermissions::_socnet_type_check($USER, $iblock_type_id, $socnet_group_id);
			}
			else
			{
				return CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED;
			}
		}
		else
		{
			if($iblock_id !== false)
				return CListPermissions::_lists_check($USER, $iblock_type_id, $iblock_id);
			else
				return CListPermissions::_lists_type_check($USER, $iblock_type_id);
		}
 	}

	static protected function _socnet_check($USER, $iblock_type_id, $iblock_id, $socnet_group_id)
	{
		$type_check = CListPermissions::_socnet_type_check($USER, $iblock_type_id, $socnet_group_id);
		if($type_check < 0)
			return $type_check;

		$iblock_check = CListPermissions::_iblock_check($iblock_type_id, $iblock_id);
		if($iblock_check < 0)
			return $iblock_check;

		$iblock_socnet_group_id = CIBlock::GetArrayByID($iblock_id, "SOCNET_GROUP_ID");
		if($iblock_socnet_group_id != $socnet_group_id)
			return CListPermissions::ACCESS_DENIED;

		$socnet_role = CSocNetUserToGroup::GetUserRole($USER->GetID(), $socnet_group_id);

		static $roles = array("A", "E", "K", "T");
		if(!in_array($socnet_role, $roles))
		{
			if($USER->IsAuthorized())
				$socnet_role = "L";
			else
				$socnet_role = "N";
		}

		$arSocnetPerm = CLists::GetSocnetPermission($iblock_id);

		return $arSocnetPerm[$socnet_role];
 	}

	static protected function _socnet_type_check($USER, $iblock_type_id, $socnet_group_id)
	{
		if($iblock_type_id === COption::GetOptionString("lists", "socnet_iblock_type_id"))
		{
			$socnet_role = CSocNetUserToGroup::GetUserRole($USER->GetID(), $socnet_group_id);
			if($socnet_role == "A")
				return CListPermissions::IS_ADMIN;
			else
				return CListPermissions::CAN_READ;
		}
		else
		{
			return CListPermissions::WRONG_IBLOCK_TYPE;
		}
 	}

	static protected function _lists_type_check($USER, $iblock_type_id)
	{
		$arListsPerm = CLists::GetPermission($iblock_type_id);
		if(!count($arListsPerm))
			return CListPermissions::ACCESS_DENIED;

		$arUSER_GROUPS = $USER->GetUserGroupArray();
		if(count(array_intersect($arListsPerm, $arUSER_GROUPS)) > 0)
			return CListPermissions::IS_ADMIN;

		return CListPermissions::CAN_READ;
 	}

	static protected function _lists_check($USER, $iblock_type_id, $iblock_id)
	{
		$iblock_check = CListPermissions::_iblock_check($iblock_type_id, $iblock_id);
		if($iblock_check < 0)
			return $iblock_check;

		$arListsPerm = CLists::GetPermission($iblock_type_id);
		if(!count($arListsPerm))
			return CListPermissions::ACCESS_DENIED;

		$arUSER_GROUPS = $USER->GetUserGroupArray();
		if(count(array_intersect($arListsPerm, $arUSER_GROUPS)) > 0)
			return CListPermissions::IS_ADMIN;

		return CIBlock::GetPermission($iblock_id);
 	}

	static protected function _iblock_check($iblock_type_id, $iblock_id)
	{
		$iblock_id = intval($iblock_id);
		if($iblock_id > 0)
		{
			$iblock_type = CIBlock::GetArrayByID($iblock_id, "IBLOCK_TYPE_ID");
			if($iblock_type_id === $iblock_type)
				return 0;
			else
				return CListPermissions::WRONG_IBLOCK;
		}
		else
		{
			return CListPermissions::WRONG_IBLOCK;
		}
 	}
}
?>