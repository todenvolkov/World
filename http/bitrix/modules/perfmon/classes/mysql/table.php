<?
class CPerfomanceTableList extends CDBResult
{
	function GetList()
	{
		global $DB;
		$rsTables = $DB->Query("show table status");
		return new CPerfomanceTableList($rsTables);
	}

	function Fetch()
	{
		global $DB;
		$ar = parent::Fetch();
		if($ar)
		{
			$ar = array(
				"TABLE_NAME" => $ar["Name"],
				"ENGINE_TYPE" => $ar["Engine"],
			);
		}
		return $ar;
	}
}

class CPerfomanceTable extends CAllPerfomanceTable
{
	var $TABLE_NAME;

	function Init($TABLE_NAME)
	{
		$this->TABLE_NAME = $TABLE_NAME;
	}

	function IsExists($TABLE_NAME = false)
	{
		if($TABLE_NAME===false)
			$TABLE_NAME = $this->TABLE_NAME;
		if(strlen($TABLE_NAME) <= 0)
			return false;
		global $DB;
		$strSql = "
			SHOW TABLES LIKE '".$DB->ForSQL($TABLE_NAME)."'
		";
		$rs = $DB->Query($strSql);
		if($rs->Fetch())
			return true;
		else
			return false;
	}

	function GetUniqueIndexes($TABLE_NAME = false)
	{
		static $cache = array();

		if($TABLE_NAME===false)
			$TABLE_NAME = $this->TABLE_NAME;

		if(!array_key_exists($TABLE_NAME, $cache))
		{
			global $DB;

			$strSql = "
				SHOW INDEXES FROM `".$DB->ForSQL($TABLE_NAME)."`
			";
			$rs = $DB->Query($strSql);
			$arResult = array();
			while($ar = $rs->Fetch())
			{
				if(!$ar["Non_unique"])
					$arResult[$ar["Key_name"]][] = $ar["Column_name"];
			}
			$cache[$TABLE_NAME] = $arResult;
		}

		return $cache[$TABLE_NAME];
	}

	function GetTableFields($TABLE_NAME = false)
	{
		static $cache = array();

		if($TABLE_NAME===false)
			$TABLE_NAME = $this->TABLE_NAME;

		if(!array_key_exists($TABLE_NAME, $cache))
		{
			global $DB;

			$strSql = "
				SHOW COLUMNS FROM `".$DB->ForSQL($TABLE_NAME)."`
			";
			$rs = $DB->Query($strSql);
			$arResult = array();
			while($ar = $rs->Fetch())
			{
				if(preg_match("/^(varchar|char|text)/", $ar["Type"]))
				{
					$ar["DATA_TYPE"] = "string";
				}
				elseif(preg_match("/^datetime/", $ar["Type"]))
				{
					$ar["DATA_TYPE"] = "datetime";
				}
				elseif(preg_match("/^int/", $ar["Type"]))
				{
					$ar["DATA_TYPE"] = "int";
				}
				elseif(preg_match("/^float/", $ar["Type"]))
				{
					$ar["DATA_TYPE"] = "double";
				}
				else
				{
					$ar["DATA_TYPE"] = "unknown";
				}
				$arResult[$ar["Field"]] = $ar["DATA_TYPE"];
			}
			$cache[$TABLE_NAME] = $arResult;
		}
		return $cache[$TABLE_NAME];
	}

	function NavQuery($arNavParams, $arQuerySelect, $strTableName, $strQueryWhere, $arQueryOrder)
	{
		global $DB;
		if(IntVal($arNavParams["nTopCount"]) <= 0)
		{
			$strSql = "
				SELECT
					count(*) C
				FROM
					".$strTableName." t
			";
			if($strQueryWhere)
			{
				$strSql .= "
					WHERE
					".$strQueryWhere."
				";
			}
			$res_cnt = $DB->Query($strSql);
			$res_cnt = $res_cnt->Fetch();
			$cnt = $res_cnt["C"];

			$strSql = "
				SELECT
				".implode(", ", $arQuerySelect)."
				FROM
					".$strTableName." t
			";
			if($strQueryWhere)
			{
				$strSql .= "
					WHERE
					".$strQueryWhere."
				";
			}
			if(count($arQueryOrder) > 0)
			{
				$strSql .= "
					ORDER BY
					".implode(", ", $arQueryOrder)."
				";
			}

			$res = new CDBResult();
			$res->NavQuery($strSql, $cnt, $arNavParams);

			return $res;
		}
		else
		{
			$strSql = "
				SELECT
				".implode(", ", $arQuerySelect)."
				FROM
					".$strTableName." t
			";
			if($strQueryWhere)
			{
				$strSql .= "
					WHERE
					".$strQueryWhere."
				";
			}
			if(count($arQueryOrder) > 0)
			{
				$strSql .= "
					ORDER BY
					".implode(", ", $arQueryOrder)."
				";
			}
			$strSql = $strSql." LIMIT ".IntVal($arNavParams["nTopCount"]);
			return $DB->Query($strSql);
		}
	}
}
?>