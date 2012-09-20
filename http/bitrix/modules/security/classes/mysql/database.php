<?
//We can not use object here due to PHP architecture
global $SECURITY_SESSION_DBH;
$SECURITY_SESSION_DBH = false;

class CSecurityDB
{
	function Init()
	{
		global $SECURITY_SESSION_DBH, $DB;

		if(is_resource($SECURITY_SESSION_DBH))
			return true;

		if(!is_object($DB))
			return false;

		$SECURITY_SESSION_DBH = @mysql_connect($DB->DBHost, $DB->DBLogin, $DB->DBPassword);

		//In case of error just skip it over
		if(!is_resource($SECURITY_SESSION_DBH))
			return false;

		if(!mysql_select_db($DB->DBName, $SECURITY_SESSION_DBH))
			return false;

		return true;
	}

	function CurrentTimeFunction()
	{
		return "now()";
	}

	function SecondsAgo($sec)
	{
		return "DATE_ADD(now(), INTERVAL - ".intval($sec)." SECOND)";
	}

	function Query($strSql, $error_position)
	{
		global $SECURITY_SESSION_DBH;
		if(is_resource($SECURITY_SESSION_DBH))
		{
			$result = @mysql_query($strSql, $SECURITY_SESSION_DBH);
			if($result)
			{
				return $result;
			}
			else
			{
				$db_Error = mysql_error();
				AddMessage2Log($error_position." MySql Query Error: ".$strSql." [".$db_Error."]", "security");
			}
		}
		return false;
	}

	function QueryBind($strSql, $arBinds, $error_position)
	{
		foreach($arBinds as $key => $value)
			$strSql = str_replace(":".$key, "'".$value."'", $strSql);
		return CSecurityDB::Query($strSql, $error_position);
	}

	function Fetch($result)
	{
		if($result)
			return mysql_fetch_array($result, MYSQL_ASSOC);
		else
			return false;
	}

	function Lock($id, $timeout = 60)
	{
		static $lock_id = "";

		if($id === false)
		{
			if($lock_id)
				$rsLock = CSecurityDB::Query("DO RELEASE_LOCK('".$lock_id."')", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
		}
		else
		{
			$rsLock = CSecurityDB::Query("SELECT GET_LOCK('".md5($id)."', ".intval($timeout).") as L", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
			if($rsLock)
			{
				$arLock = CSecurityDB::Fetch($rsLock);
				if($arLock["L"]=="0")
					return false;
				else
					$lock_id = md5($id);
			}
		}
		return is_resource($rsLock);
	}

	function LockTable($table_name, $lock_id)
	{
		$rsLock = CSecurityDB::Query("SELECT GET_LOCK('".md5($lock_id)."', 0) as L", "Module: security; Class: CSecurityDB; Function: LockTable; File: ".__FILE__."; Line: ".__LINE__);
		if($rsLock)
		{
			$arLock = CSecurityDB::Fetch($rsLock);
			if($arLock["L"]=="0")
				return false;
			else
				return array("lock_id" => $lock_id);
		}
		else
		{
			return false;
		}
	}

	function UnlockTable($table_lock)
	{
		if(is_array($table_lock))
			CSecurityDB::Query("SELECT RELEASE_LOCK('".$table_lock["lock_id"]."')", "Module: security; Class: CSecurityDB; Function: UnlockTable; File: ".__FILE__."; Line: ".__LINE__);
	}
}
?>