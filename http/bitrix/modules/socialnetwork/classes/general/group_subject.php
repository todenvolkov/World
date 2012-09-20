<?
IncludeModuleLangFile(__FILE__);

class CAllSocNetGroupSubject
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB;

		if ($ACTION != "ADD" && IntVal($ID) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("System error 870164", "ERROR");
			return false;
		}

		if ((is_set($arFields, "SITE_ID") || $ACTION=="ADD") && strlen($arFields["SITE_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_GS_EMPTY_SITE_ID"), "EMPTY_SITE_ID");
			return false;
		}
		elseif (is_set($arFields, "SITE_ID"))
		{
			$dbResult = CSite::GetByID($arFields["SITE_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["SITE_ID"], GetMessage("SONET_GS_ERROR_NO_SITE")), "ERROR_NO_SITE");
				return false;
			}
		}

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && strlen($arFields["NAME"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_GS_EMPTY_NAME"), "EMPTY_NAME");
			return false;
		}
		
		if (is_set($arFields, "SORT") || $ACTION=="ADD")
			$arFields["SORT"] = (intVal($arFields["SORT"]) > 0 ? intVal($arFields["SORT"]) : 100);
		
		return True;
	}

	function Delete($ID)
	{
		global $DB, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);
		$bSuccess = True;

		$bCanDelete = true;
		$dbResult = CSocNetGroup::GetList(
			array(),
			array("SUBJECT_ID" => $ID)
		);
		if ($arResult = $dbResult->Fetch())
			$bCanDelete = false;

		if (!$bCanDelete)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SONET_GS_NOT_EMPTY_SUBJECT"), "NOT_EMPTY_SUBJECT");
			return false;
		}

		$bSuccess = $DB->Query("DELETE FROM b_sonet_group_subject WHERE ID = ".$ID."", true);

		if (CACHED_b_sonet_group_subjects != false)
			$CACHE_MANAGER->CleanDir("b_sonet_group_subjects");

		return $bSuccess;
	}
	
	function Update($ID, $arFields)
	{
		global $DB, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1) == "=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSocNetGroupSubject::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sonet_group_subject", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($strUpdate) > 0)
				$strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		if (strlen($strUpdate) > 0)
		{
			$strSql =
				"UPDATE b_sonet_group_subject SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			if (CACHED_b_sonet_group_subjects != false)
				$CACHE_MANAGER->CleanDir("b_sonet_group_subjects");
		}
		else
		{
			$ID = False;
		}

		return $ID;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	function GetByID($ID)
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($ID))
			return false;

		$ID = IntVal($ID);

		$dbResult = CSocNetGroupSubject::GetList(Array(), Array("ID" => $ID));
		if ($arResult = $dbResult->GetNext())
		{
			return $arResult;
		}

		return False;
	}
	
	/***************************************/
	/*************    *************/
	/***************************************/

}
?>
