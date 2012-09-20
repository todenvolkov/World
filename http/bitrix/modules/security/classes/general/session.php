<?
global $SECURITY_SESSION_OLD_ID;
$SECURITY_SESSION_OLD_ID = false;

class CSecuritySession
{
	function Init()
	{
		if(CSecurityDB::Init())
		{
			//may return false with session.auto_start is set to On
			if(session_set_save_handler(
				array("CSecuritySession", "open"),
				array("CSecuritySession", "close"),
				array("CSecuritySession", "read"),
				array("CSecuritySession", "write"),
				array("CSecuritySession", "destroy"),
				array("CSecuritySession", "gc")
			))
			{
				register_shutdown_function("session_write_close");
			}
		}
	}

	function CleanUpAgent()
	{
		global $DB;
		$maxlifetime = intval(ini_get("session.gc_maxlifetime"));

		if($maxlifetime)
		{
			$strSql = "
				delete from b_sec_session
				where TIMESTAMP_X < ".CSecurityDB::SecondsAgo($maxlifetime)."
			";
			if(CSecurityDB::Init())
				CSecurityDB::Query($strSql, "Module: security; Class: CSecuritySession; Function: CleanUpAgent; File: ".__FILE__."; Line: ".__LINE__);
			else
				$DB->Query($strSql, false, "Module: security; Class: CSecuritySession; Function: CleanUpAgent; File: ".__FILE__."; Line: ".__LINE__);
		}

		return "CSecuritySession::CleanUpAgent();";
	}

	function UpdateSessID()
	{
		global $SECURITY_SESSION_OLD_ID;

		$old_sess_id = session_id();
		session_regenerate_id();
		if(!version_compare(phpversion(),"4.3.3",">="))
		{
			setcookie(session_name(), session_id(), ini_get("session.cookie_lifetime"), "/");
		}
		$new_sess_id = session_id();

		//Delay database update to session write moment
		if(!$SECURITY_SESSION_OLD_ID)
			$SECURITY_SESSION_OLD_ID = $old_sess_id;
	}

	function CheckSessionId($id)
	{
		return preg_match("/^[\da-z]{1,32}$/i", $id);
	}

	function open($save_path, $session_name)
	{
		return CSecurityDB::Init();
	}

	function close()
	{
		CSecurityDB::Lock(false);

		return true;
	}

	function read($id)
	{
		if(preg_match("/^[\da-z]{1,32}$/i", $id))
		{
			if(!CSecurityDB::Lock($id, 60/*TODO: timelimit from php.ini?*/))
				die('Unable to get session lock within 60 seconds.');

			$rs = CSecurityDB::Query("
				select SESSION_DATA
				from b_sec_session
				where SESSION_ID = '".$id."'
			", "Module: security; Class: CSecuritySession; Function: read; File: ".__FILE__."; Line: ".__LINE__);
			$ar = CSecurityDB::Fetch($rs);
			if($ar)
			{
				$res = base64_decode($ar["SESSION_DATA"]);
				return $res;
			}
		}
	}

	function write($id, $sess_data)
	{
		global $SECURITY_SESSION_OLD_ID;

		if(preg_match("/^[\da-z]{1,32}$/i", $id))
		{
			if($SECURITY_SESSION_OLD_ID && preg_match("/^[\da-z]{1,32}$/i", $SECURITY_SESSION_OLD_ID))
				$old_sess_id = $SECURITY_SESSION_OLD_ID;
			else
				$old_sess_id = $id;

			CSecurityDB::Query("
				delete from b_sec_session
				where SESSION_ID = '".$old_sess_id."'
			", "Module: security; Class: CSecuritySession; Function: write; File: ".__FILE__."; Line: ".__LINE__);

			CSecurityDB::QueryBind("
				insert into b_sec_session
				(SESSION_ID, TIMESTAMP_X, SESSION_DATA)
				values
				('".$id."', ".CSecurityDB::CurrentTimeFunction().", :SESSION_DATA)
			", array("SESSION_DATA" => base64_encode($sess_data))
			, "Module: security; Class: CSecuritySession; Function: write; File: ".__FILE__."; Line: ".__LINE__);
		}
	}

	function destroy($id)
	{
		if(preg_match("/^[\da-z]{1,32}$/i", $id))
		{
			CSecurityDB::Query("
				delete from b_sec_session
				where SESSION_ID = '".$id."'
			", "Module: security; Class: CSecuritySession; Function: destroy; File: ".__FILE__."; Line: ".__LINE__);

			if($SECURITY_SESSION_OLD_ID && preg_match("/^[\da-z]{1,32}$/i", $SECURITY_SESSION_OLD_ID))
				CSecurityDB::Query("
					delete from b_sec_session
					where SESSION_ID = '".$SECURITY_SESSION_OLD_ID."'
				", "Module: security; Class: CSecuritySession; Function: destroy; File: ".__FILE__."; Line: ".__LINE__);
		}
	}

	function gc($maxlifetime)
	{
		CSecurityDB::Query("
			delete from b_sec_session
			where TIMESTAMP_X < ".CSecurityDB::SecondsAgo($maxlifetime)."
			", "Module: security; Class: CSecuritySession; Function: gc; File: ".__FILE__."; Line: ".__LINE__);
	}
}
?>