<?
IncludeModuleLangFile(__FILE__);
class CEventLog
{
	function Log($SEVERITY, $AUDIT_TYPE_ID, $MODULE_ID, $ITEM_ID, $DESCRIPTION = false)
	{
		return CEventLog::Add(array(
			"SEVERITY" => $SEVERITY,
			"AUDIT_TYPE_ID" => $AUDIT_TYPE_ID,
			"MODULE_ID" => $MODULE_ID,
			"ITEM_ID" => $ITEM_ID,
			"DESCRIPTION" => $DESCRIPTION,
		));
	}

	function Add($arFields)
	{
		global $USER, $DB;
		static $arSeverity = array(
			"SECURITY" => 1,
		);

		$url = preg_replace("/(&?sessid=[0-9a-z]+)/", "", $_SERVER["REQUEST_URI"]);
		$arFields = array(
			"SEVERITY" => array_key_exists($arFields["SEVERITY"], $arSeverity)? $arFields["SEVERITY"]: "UNKNOWN",
			"AUDIT_TYPE_ID" => strlen($arFields["AUDIT_TYPE_ID"]) <= 0? "UNKNOWN": $arFields["AUDIT_TYPE_ID"],
			"MODULE_ID" => strlen($arFields["MODULE_ID"]) <= 0? "UNKNOWN": $arFields["MODULE_ID"],
			"ITEM_ID" => strlen($arFields["ITEM_ID"]) <= 0? "UNKNOWN": $arFields["ITEM_ID"],
			"REMOTE_ADDR" => $_SERVER["REMOTE_ADDR"],
			"USER_AGENT" => $_SERVER["HTTP_USER_AGENT"],
			"REQUEST_URI" => $url,
			"SITE_ID" => defined("SITE_ID")? SITE_ID: false,
			"USER_ID" => is_object($USER) && ($USER->GetID() > 0)? $USER->GetID(): false,
			"GUEST_ID" => (isset($_SESSION) && array_key_exists("SESS_GUEST_ID", $_SESSION) && $_SESSION["SESS_GUEST_ID"] > 0? $_SESSION["SESS_GUEST_ID"]: false),
			"DESCRIPTION" => $arFields["DESCRIPTION"],
		);

		return $DB->Add("b_event_log", $arFields, array("DESCRIPTION"), "", false, "", array("ignore_dml"=>true));
	}

	//Agent
	function CleanUpAgent()
	{
		global $DB;
		$cleanup_days = COption::GetOptionInt("main", "event_log_cleanup_days", 7);
		if($cleanup_days > 0)
		{
			$arDate = localtime(time());
			$date = mktime(0, 0, 0, $arDate[4]+1, $arDate[3]-$cleanup_days, 1900+$arDate[5]);
			$DB->Query("DELETE FROM b_event_log WHERE TIMESTAMP_X <= ".$DB->CharToDateFunction(ConvertTimeStamp($date, "FULL")));
		}
		return "CEventLog::CleanUpAgent();";
	}

	function GetList($arOrder = Array("ID" => "DESC"), $arFilter = array())
	{
		global $DB;
		$err_mess = "FILE: ".__FILE__."<br>LINE: ";

		$arSqlSearch = array();
		$arSqlOrder = array();

		$arOFields = array(
			"ID" => "L.ID",
			"TIMESTAMP_X" => "L.TIMESTAMP_X",
		);

		foreach($arFilter as $key => $val)
		{
			if(is_array($val))
			{
				if(count($val) <= 0)
					continue;
			}
			elseif(strlen($val) <= 0)
			{
				continue;
			}
			$key = strtoupper($key);
			switch($key)
			{
				case "ID":
					$arSqlSearch[] = "L.ID=".IntVal($val);
				case "TIMESTAMP_X_1":
					$arSqlSearch[] = "L.TIMESTAMP_X >= ".$DB->CharToDateFunction($DB->ForSql($val), "FULL");
					break;
				case "TIMESTAMP_X_2":
					$arSqlSearch[] = "L.TIMESTAMP_X <= ".$DB->CharToDateFunction($DB->ForSql($val), "FULL");
					break;
				case "=AUDIT_TYPE_ID":
					$arValues = array();
					if(is_array($val))
					{
						foreach($val as $value)
						{
							$value = trim($value);
							if(strlen($value))
								$arValues[$value] = $DB->ForSQL($value);
						}
					}
					elseif(is_string($val))
					{
						$value = trim($val);
						if(strlen($value))
							$arValues[$value] = $DB->ForSQL($value);
					}
					if(!empty($arValues))
						$arSqlSearch[] = "L.AUDIT_TYPE_ID in ('".implode("', '", $arValues)."')";
					break;
				case "SEVERITY":
				case "AUDIT_TYPE_ID":
				case "MODULE_ID":
				case "ITEM_ID":
				case "SITE_ID":
				case "REMOTE_ADDR":
				case "USER_AGENT":
				case "REQUEST_URI":
					$arSqlSearch[] = GetFilterQuery("L.".$key, $val);
					break;
				case "USER_ID":
				case "GUEST_ID":
					$arSqlSearch[] = "L.".$key." = ".intval($val)."";
					break;
			}
		}

		foreach($arOrder as $by => $order)
		{
			$by = strtoupper($by);
			$order = strtoupper($order);
			if (array_key_exists($by, $arOFields))
			{
				if ($order != "ASC")
					$order = "DESC".($DB->type=="ORACLE" ? " NULLS LAST" : "");
				else
					$order = "ASC".($DB->type=="ORACLE" ? " NULLS FIRST" : "");
				$arSqlOrder[$by] = $arOFields[$by]." ".$order;
			}
		}

		$strSql = "
			SELECT L.*,
			".$DB->DateToCharFunction("L.TIMESTAMP_X")." as TIMESTAMP_X
			FROM
				b_event_log L
		";
		if(count($arSqlSearch) > 0)
			$strSql .=  " WHERE ".implode(" AND ", $arSqlSearch);
		if(count($arSqlOrder) > 0)
			$strSql .=  " ORDER BY ".implode(", ", $arSqlOrder);

		return $DB->Query($strSql, false, $err_mess.__LINE__);
	}
}
?>