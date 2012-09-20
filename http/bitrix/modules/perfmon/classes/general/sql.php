<?
class CAllPerfomanceSQL
{
	function GetList($arSelect, $arFilter, $arOrder, $bGroup)
	{
		global $DB;

		if(!is_array($arSelect))
			$arSelect = array();
		if(count($arSelect) < 1)
			$arSelect = array(
				"ID",
			);

		if(!is_array($arOrder))
			$arOrder = array();
		if(count($arOrder) < 1)
			$arOrder = array(
				"HIT_ID" => "DESC",
				"NN" => "ASC",
			);

		$arQueryOrder = array();
		foreach($arOrder as $strColumn => $strDirection)
		{
			$strColumn = strtoupper($strColumn);
			$strDirection = strtoupper($strDirection)=="ASC"? "ASC": "DESC";
			switch($strColumn)
			{
				case "ID":
				case "HIT_ID":
				case "NN":
				case "MODULE_NAME":
				case "COMPONENT_NAME":
					$arSelect[] = $strColumn;
					$arQueryOrder[$strColumn] = $strColumn." ".$strDirection;
					break;
				case "SQL_TEXT":
				case "QUERY_TIME":
					if(!$bGroup)
					{
						$arSelect[] = $strColumn;
						$arQueryOrder[$strColumn] = $strColumn." ".$strDirection;
					}
					break;
				case "MAX_QUERY_TIME":
				case "MIN_QUERY_TIME":
				case "AVG_QUERY_TIME":
				case "SUM_QUERY_TIME":
					if($bGroup)
					{
						$arSelect[] = $strColumn;
						$arQueryOrder[$strColumn] = $strColumn." ".$strDirection;
					}
					break;
				case "COUNT":
					if($bGroup)
					{
						$arSelect[] = $strColumn;
						$arQueryOrder[$strColumn] = $strColumn." ".$strDirection;
					}
					break;
			}
		}

		$arQueryGroup = array();
		$arQuerySelect = array();
		foreach($arSelect as $strColumn)
		{
			$strColumn = strtoupper($strColumn);
			switch($strColumn)
			{
				case "ID":
				case "HIT_ID":
				case "NN":
				case "MODULE_NAME":
				case "COMPONENT_NAME":
					if($bGroup)
						$arQueryGroup[$strColumn] = "s.".$strColumn;
					$arQuerySelect[$strColumn] = "s.".$strColumn;
					break;
				case "SQL_TEXT":
				case "QUERY_TIME":
					if(!$bGroup)
						$arQuerySelect[$strColumn] = "s.".$strColumn;
					break;
				case "MAX_QUERY_TIME":
				case "MIN_QUERY_TIME":
				case "AVG_QUERY_TIME":
				case "SUM_QUERY_TIME":
					if($bGroup)
					{
						$arQuerySelect[$strColumn] = substr($strColumn, 0, 3)."(s.".substr($strColumn, 4).") ".$strColumn;
					}
					break;
				case "COUNT":
					if($bGroup)
					{
						$arQuerySelect[$strColumn] = "COUNT(s.ID) ".$strColumn;
					}
					break;
			}
		}

		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields(array(
			"HIT_ID" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "HIT_ID",
				"FIELD_TYPE" => "int", //int, double, file, enum, int, string, date, datetime
				"JOIN" => false,
				//"LEFT_JOIN" => "lt",
			),
			"COMPONENT_ID" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "s.COMPONENT_ID",
				"FIELD_TYPE" => "int",
				"JOIN" => false,
			),
			"ID" => array(
				"TABLE_ALIAS" => "s",
				"FIELD_NAME" => "ID",
				"FIELD_TYPE" => "int",
				"JOIN" => false,
			),
		));

		if(count($arQuerySelect) < 1)
			$arQuerySelect = array("ID"=>"s.ID");

		$strSql = "
			SELECT
			".implode(", ", $arQuerySelect)."
			FROM
				b_perf_sql s
		";
		if(!is_array($arFilter))
			$arFilter = array();
		if($strQueryWhere = $obQueryWhere->GetQuery($arFilter))
		{
			$strSql .= "
				WHERE
				".$strQueryWhere."
			";
		}
		if($bGroup)
		{
			$strSql .= "
				GROUP BY
				".implode(", ", $arQueryGroup)."
			";
		}
		if(count($arQueryOrder) > 0)
		{
			$strSql .= "
				ORDER BY
				".implode(", ", $arQueryOrder)."
			";
		}
		//echo "<pre>",htmlspecialchars($strSql),"</pre><hr>";
		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	function Format($strSql)
	{
		$strSql = preg_replace("/[\\n\\r\\t\\s ]+/", " ", $strSql);
		$strSql = preg_replace("/^ +/", "", $strSql);
		$strSql = preg_replace("/ (INNER JOIN|OUTER JOIN|LEFT JOIN|SET|LIMIT) /i", "\n\\1 ", $strSql);
		$strSql = preg_replace("/(INSERT INTO [A-Z_0-1]+?)\\s/i", "\\1\n", $strSql);
		$strSql = preg_replace("/(INSERT INTO [A-Z_0-1]+?)([(])/i", "\\1\n\\2", $strSql);
		$strSql = preg_replace("/([\\s)])(VALUES)([\\s(])/i", "\\1\n\\2\n\\3", $strSql);
		$strSql = preg_replace("/ (FROM|WHERE|ORDER BY|GROUP BY|HAVING) /i", "\n\\1\n", $strSql);
		if(preg_match("/.*WHERE(.+)\s(ORDER BY|GROUP BY|HAVING|$)/is", $strSql." ", $arMatch))
		{
			$strWhere = $arMatch[1];
			$len = strlen($strWhere);
			$res = "";
			$group = 0;
			for($i = 0; $i < $len; $i++)
			{
				$char = substr($strWhere, $i, 1);
				if($char == "(")
					$group++;
				elseif($char == ")")
					$group--;
				elseif($group == 0)
				{
					if(preg_match("/^(\s)(AND|OR|NOT)([\s(])/is", substr($strWhere, $i), $match))
					{
						$char = "\n    ".$match[2];
						$i += strlen($match[1].$match[2]) - 1;
					}
				}
				$res .= $char;
			}
			$strSql = str_replace($arMatch[1], $res, $strSql);
		}
		if(preg_match("/.*?SELECT(.+)\s(FROM)/is", $strSql." ", $arMatch))
		{
			$strWhere = $arMatch[1];
			$len = strlen($strWhere);
			$res = "";
			$group = 0;
			for($i = 0; $i < $len; $i++)
			{
				$char = substr($strWhere, $i, 1);
				if($char == "(")
					$group++;
				elseif($char == ")")
					$group--;
				elseif($group == 0 && $char == ",")
				{
					$char = "\n    ".$char;
				}
				$res .= $char;
			}
			$strSql = str_replace($arMatch[1], $res, $strSql);
		}
		if(preg_match("/.*?UPDATE\s.+?\sSET\s(.+?)WHERE/is", $strSql." ", $arMatch))
		{
			$strWhere = $arMatch[1];
			$len = strlen($strWhere);
			$res = "";
			$group = 0;
			for($i = 0; $i < $len; $i++)
			{
				$char = substr($strWhere, $i, 1);
				if($char == "(")
					$group++;
				elseif($char == ")")
					$group--;
				elseif($group == 0 && $char == ",")
				{
					$char = "\n    ".$char;
				}
				$res .= $char;
			}
			$strSql = str_replace($arMatch[1], $res, $strSql);
		}
		return $strSql;
	}

	function Clear()
	{
		global $DB;
		return $DB->Query("TRUNCATE TABLE b_perf_sql");
	}
}
?>