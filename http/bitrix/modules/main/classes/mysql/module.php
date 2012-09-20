<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/module.php");
class CModule extends CAllModule
{
	function err_mess()
	{
		return "<br>Class: CModule;<br>File: ".__FILE__;
	}

	function GetDropDownList($strSqlOrder="ORDER BY ID")
	{
		global $DB;
		$err_mess = (CModule::err_mess())."<br>Function: GetDropDownList<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				ID as REFERENCE
			FROM 
				b_module
			$strSqlOrder
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}
}
?>