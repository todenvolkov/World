<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/classes/general/trackingservice.php");

class CBPTrackingService
	extends CBPAllTrackingService
{
	public function Write($workflowId, $type, $actionName, $executionStatus, $executionResult, $actionTitle = "", $actionNote = "", $modifiedBy = 0)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$actionName = trim($actionName);
		if (strlen($actionName) <= 0)
			throw new Exception("actionName");

		$type = intval($type);
		$executionStatus = intval($executionStatus);
		$executionResult = intval($executionResult);
		$actionNote = trim($actionNote);

		$modifiedBy = intval($modifiedBy);

		$DB->Query(
			"INSERT INTO b_bp_tracking(WORKFLOW_ID, TYPE, MODIFIED, ACTION_NAME, ACTION_TITLE, EXECUTION_STATUS, EXECUTION_RESULT, ACTION_NOTE, MODIFIED_BY) ".
			"VALUES('".$DB->ForSql($workflowId, 32)."', ".intval($type).", ".$DB->CurrentTimeFunction().", '".$DB->ForSql($actionName, 128)."', '".$DB->ForSql($actionTitle, 255)."', ".intval($executionStatus).", ".intval($executionResult).", ".(strlen($actionNote) > 0 ? "'".$DB->ForSql($actionNote)."'" : "NULL").", ".($modifiedBy > 0 ? $modifiedBy : "NULL").")"
		);
	}

	public static function GetList($arOrder = array("ID" => "DESC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
 		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "WORKFLOW_ID", "TYPE", "MODIFIED", "ACTION_NAME", "ACTION_TITLE", "EXECUTION_STATUS", "EXECUTION_RESULT", "ACTION_NOTE", "MODIFIED_BY");

		static $arFields = array(
			"ID" => Array("FIELD" => "T.ID", "TYPE" => "int"),
			"WORKFLOW_ID" => Array("FIELD" => "T.WORKFLOW_ID", "TYPE" => "string"),
			"TYPE" => Array("FIELD" => "T.TYPE", "TYPE" => "int"),
			"ACTION_NAME" => Array("FIELD" => "T.ACTION_NAME", "TYPE" => "string"),
			"ACTION_TITLE" => Array("FIELD" => "T.ACTION_TITLE", "TYPE" => "string"),
			"MODIFIED" => Array("FIELD" => "T.MODIFIED", "TYPE" => "datetime"),
			"EXECUTION_STATUS" => Array("FIELD" => "T.EXECUTION_STATUS", "TYPE" => "int"),
			"EXECUTION_RESULT" => Array("FIELD" => "T.EXECUTION_RESULT", "TYPE" => "int"),
			"ACTION_NOTE" => Array("FIELD" => "T.ACTION_NOTE", "TYPE" => "string"),
			"MODIFIED_BY" => Array("FIELD" => "T.MODIFIED_BY", "TYPE" => "int"),
		);

		$arSqls = CBPHelper::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_bp_tracking T ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialchars($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_bp_tracking T ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_bp_tracking T ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialchars($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if (strlen($arSqls["GROUPBY"]) <= 0)
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				// ТОЛЬКО ДЛЯ MYSQL!!! ДЛЯ ORACLE ДРУГОЙ КОД
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.3!=".htmlspecialchars($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".$arNavStartParams["nTopCount"];

			//echo "!3!=".htmlspecialchars($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}
?>