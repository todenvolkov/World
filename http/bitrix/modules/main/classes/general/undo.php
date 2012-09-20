<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
// ***** CUndo *****
IncludeModuleLangFile(__FILE__);
class CUndo
{
	function Add($params = array())
	{
		global $DB, $USER;

		$ID = '1'.md5(uniqid(rand(), true));

		$strContent = serialize($params['arContent']);

		$arFields = array(
			'ID' => $ID,
			'MODULE_ID' => $params['module'],
			'UNDO_TYPE'  => $params['undoType'],
			'UNDO_HANDLER'  => $params['undoHandler'],
			'CONTENT' => $strContent,
			'USER_ID' => $USER->GetId(),
			'TIMESTAMP_X' => time()
		);

		CDatabase::Add("b_undo", $arFields, Array("CONTENT"));
		return $ID;
	}

	function Escape($ID, $params = array())
	{
		global $USER;
		if(!isset($USER) || !is_object($USER) || !$USER->IsAuthorized())
			return false;

		$arUndos = CUndo::GetList(array('arFilter' => array('ID' => $ID, 'USER_ID' => $USER->GetId())));
		if (count($arUndos) <= 0)
			return false;

		$arUndo = $arUndos[0];

		// Include module
		if ($arUndo['MODULE_ID'] && strlen($arUndo['MODULE_ID']) > 0)
			CModule::IncludeModule($arUndo['MODULE_ID']);

		// Get params for Escaping
		$arParams = unserialize($arUndo['CONTENT']);

		// Check and call Undo handler
		if (function_exists($arUndo['UNDO_HANDLER'])) // function
		{
			call_user_func($arUndo['UNDO_HANDLER'], array($arParams, $arUndo['UNDO_TYPE']));
		}
		elseif(strpos($arUndo['UNDO_HANDLER'], "::") !== false) // Static method
		{
			$p = strpos($arUndo['UNDO_HANDLER'], "::");
			$className = substr($arUndo['UNDO_HANDLER'], 0, $p);
			$methodName = substr($arUndo['UNDO_HANDLER'], $p + 2);

			if (class_exists($className))
				call_user_func_array(array($className, $methodName), array($arParams, $arUndo['UNDO_TYPE']));
		}

		// Del entry
		CUndo::Delete($ID);
		return true;
	}

	function GetList($Params = array())
	{
		global $DB, $USER;

		$arFilter = $Params['arFilter'];
		$arOrder = isset($Params['arOrder']) ? $Params['arOrder'] : Array('ID' => 'asc');

		$arFields = array(
			"ID" => Array("FIELD_NAME" => "U.ID", "FIELD_TYPE" => "string"),
			"MODULE_ID" => Array("FIELD_NAME" => "U.MODULE_ID", "FIELD_TYPE" => "string"),
			"UNDO_TYPE" => Array("FIELD_NAME" => "U.UNDO_TYPE", "FIELD_TYPE" => "string"),
			"UNDO_HANDLER" => Array("FIELD_NAME" => "U.UNDO_HANDLER", "FIELD_TYPE" => "string"),
			"CONTENT" => Array("FIELD_NAME" => "U.CONTENT", "FIELD_TYPE" => "string"),
			"USER_ID" => Array("FIELD_NAME" => "U.USER_ID", "FIELD_TYPE" => "int"),
			"TIMESTAMP_X" => Array("FIELD_NAME" => "U.TIMESTAMP_X", "FIELD_TYPE" => "int")
		);

		$err_mess = "CUndo::GetList<br>Line: ";
		$arSqlSearch = array();
		$strSqlSearch = "";

		if(is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for($i=0, $l = count($filter_keys); $i<$l; $i++)
			{
				$n = strtoupper($filter_keys[$i]);
				$val = $arFilter[$filter_keys[$i]];
				if ($n == 'ID')
					$arSqlSearch[] = GetFilterQuery("U.ID", $val, 'N');
				elseif(isset($arFields[$n]))
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val);
			}
		}

		$strOrderBy = '';
		foreach($arOrder as $by=>$order)
			if(isset($arFields[strtoupper($by)]))
				$strOrderBy .= $arFields[strtoupper($by)]["FIELD_NAME"].' '.(strtolower($order)=='desc'?'desc'.(strtoupper($DB->type)=="ORACLE"?" NULLS LAST":""):'asc'.(strtoupper($DB->type)=="ORACLE"?" NULLS FIRST":"")).',';

		if(strlen($strOrderBy)>0)
			$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				U.*
			FROM
				b_undo U
			WHERE
				$strSqlSearch
			$strOrderBy";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$arResult = Array();
		while($arRes = $res->Fetch())
			$arResult[]=$arRes;

		return $arResult;
	}

	function Delete($ID)
	{
		global $DB;
		$strSql = "DELETE FROM b_undo WHERE ID='".$DB->ForSql($ID)."'";
		$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}

	function CleanUpOld()
	{
		global $DB;
		// All entries older than one day
		$timestamp = mktime(date("H"), date("i"), 0, date("m"),   date("d") - 1,   date("Y"));
		$strSql = "delete from b_undo where TIMESTAMP_X <= ".$timestamp." ";
		$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return "CUndo::CleanUpOld();";
	}

	function ShowUndoMessage($ID, $params = array())
	{
		$_SESSION['BX_UNDO_ID'] = $ID;
	}

	function CheckNotifyMessage()
	{
		global $USER, $APPLICATION;
		if(!is_array($_SESSION) || !array_key_exists("BX_UNDO_ID", $_SESSION))
			return;

		$ID = $_SESSION['BX_UNDO_ID'];
		unset($_SESSION['BX_UNDO_ID']);

		$arUndos = CUndo::GetList(array('arFilter' => array('ID' => $ID, 'USER_ID' => $USER->GetId())));
		if (count($arUndos) <= 0)
			return;
		$arUndo = $arUndos[0];
		$detail = GetMessage('MAIN_UNDO_TYPE_'.strtoupper($arUndo['UNDO_TYPE']));

		$s = "
<script>
window.BXUndoLastChanges = function()
{
	if (!confirm(\"".GetMessage("MAIN_UNDO_ESCAPE_CHANGES_CONFIRM")."\"))
		return;

	BX.ajax.get(\"/bitrix/admin/public_undo.php?undo=".$ID."&".bitrix_sessid_get()."\", false, function(result)
	{
		if (result && result.toUpperCase().indexOf(\"ERROR\") != -1)
			BX.admin.panel.Notify(\"".GetMessage("MAIN_UNDO_ESCAPE_ERROR")."\");
		else
			window.location = window.location;
	});
}
BX.ready(function()
{
	setTimeout(function()
	{
		BX.admin.panel.Notify('".$detail." <a href=\"javascript: void(0);\" onclick=\"window.BXUndoLastChanges(); return false;\" title=\"".GetMessage("MAIN_UNDO_ESCAPE_CHANGES_TITLE")."\">".GetMessage("MAIN_UNDO_ESCAPE_CHANGES")."</a>');
	}, 100);
});
</script>";

		$APPLICATION->AddHeadString($s);
	}
}
?>