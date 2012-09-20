<?
class PHPParser
{
	function ReplString($str, $arAllStr)
	{
		if(preg_match("'^\x01([0-9]+)\x02$'s", $str))
			return preg_replace("'\x01([0-9]+)\x02'es", "\$arAllStr['\\1']", $str);
		if(strval(floatVal($str)) == $str)
			return preg_replace("'\x01([0-9]+)\x02'es", "'\"'.\$arAllStr['\\1'].'\"'", $str);
		elseif($str=="")
			return "";
		else
			return "={".preg_replace("'\x01([0-9]+)\x02'es", "'\"'.\$arAllStr['\\1'].'\"'", $str)."}";
	}

	function GetParams($params)
	{
		$arParams = Array();
		$sk = 0;
		$param_tmp = "";
		for($i=0; $i<strlen($params); $i++)
		{
			$ch = substr($params, $i, 1);
			if($ch=="(")
				$sk++;
			elseif($ch==")")
				$sk--;
			elseif($ch=="," && $sk==0)
			{
				$arParams[] = $param_tmp;
				$param_tmp = "";
				continue;
			}

			if($sk<0)
				break;

			$param_tmp .= $ch;
		}
		if($param_tmp!="")
			$arParams[] = $param_tmp;
		return $arParams;
	}

	function GetParamsRec($params, &$arAllStr, &$arResult)
	{
		if (strtolower(substr($params, 0, 6)) == 'array(')
		{
			$arParams = PHPParser::GetParams(substr($params, 6));

			for ($i = 0; $i < count($arParams); $i++)
			{
				$el = $arParams[$i];
				$p = strpos($el, "=>");
				if ($p === False)
				{
					PHPParser::GetParamsRec($el, $arAllStr, $arResult[]);
				}
				else
				{
					$el_ind = PHPParser::ReplString(substr($el, 0, $p), $arAllStr);
					$el_val = substr($el, $p + 2);

					PHPParser::GetParamsRec($el_val, $arAllStr, $arResult[$el_ind]);
				}
			}
		}
		else
		{
			$arResult = PHPParser::ReplString($params, $arAllStr);
		}
	}

	// Parse string and check if it is a component call. Return call params array
	function CheckForComponent($str)
	{
		if(substr($str, 0, 5)=="<?"."php")
			$str = substr($str, 5);
		else
			$str = substr($str, 2);

		$str = substr($str, 0, -2);

		$bSlashed = false;
		$bInString = false;
		$arAllStr = Array();
		$new_str = "";
		$i=-1;
		while($i<strlen($str)-1)
		{
			$i++;
			$ch = substr($str, $i, 1);
			if(!$bInString)
			{
				if($string_tmp!="")
				{
					$arAllStr[] = $string_tmp;
					$string_tmp = "";
					$new_str .= chr(1).(count($arAllStr)-1).chr(2);
				}

				//проверяем что не начинается комментарий
				if($ch == "/" && $i+1<strlen($str))
				{
					$ti = 0;
					if(substr($str, $i+1, 1)=="*" && ($ti = strpos($str, "*/", $i+2))!==false)
						$ti += 2;
					elseif(substr($str, $i+1, 1)=="/" && ($ti = strpos($str, "\n", $i+2))!==false)
						$ti += 1;

					if($ti)
						$i = $ti;

					continue;
				} // if($ch == "/" && $i+1<strlen($filesrc))

				if($ch == " " || $ch == "\r" || $ch == "\n" || $ch == "\t")
					continue;
			} // if(!$bInString)

			if($bInString && $ch == "\\" && !$bSlashed)
			{
				$bSlashed = true;
				continue;
			}

			if($ch == "\"" || $ch == "'")
			{
				if($bInString)
				{
					if(!$bSlashed && $quote_ch == $ch)
					{
						$bInString = false;
						continue;
					}
				}
				else
				{
					$bInString = true;
					$quote_ch = $ch;
					continue;
				}
			}
			elseif($bInString && $ch == "\\")
				$bSlashed = true;

			$bSlashed = false;
			if($bInString)
			{
				$string_tmp .= $ch;
				continue;
			}

			$new_str .= $ch;
		} // while($i<strlen($filesrc)-1)

		if($pos = strpos($new_str, "("))
		{
			$func_name = substr($new_str, 0, $pos+1);
			if(preg_match("/^(\\\$[A-Z_][A-Z0-9_]*)(\s*=\s*)/i", $func_name, $arMatch))
			{
				$var_name = $arMatch[1];
				$func_name = substr($func_name, strlen($arMatch[0]));
			}
			else
			{
				$var_name = "";
			}
			$func_name = preg_replace("'\\\$GLOBALS\[(\"|\')(.+?)(\"|\')\]'s", "\$\\2", $func_name);
			switch(strtoupper($func_name))
			{
			case '$APPLICATION->INCLUDEFILE(':
				$params = substr($new_str, $pos+1);
				$arParams = PHPParser::GetParams($params);
				$arIncludeParams = Array();

				if(strtolower(substr($arParams[1], 0, 6))=='array(')
				{
					$arParams2 = PHPParser::GetParams(substr($arParams[1], 6));
					for($i=0; $i<count($arParams2); $i++)
					{
						$el = $arParams2[$i];
						$p = strpos($el, "=>");
						$el_ind = PHPParser::ReplString(substr($el, 0, $p), $arAllStr);
						$el_val = substr($el, $p+2);
						if(strtolower(substr($el_val, 0, 6))=='array(')
						{
							$res_ar = Array();
							$arParamsN = PHPParser::GetParams(substr($el_val, 6));
							for($j = 0; $j<count($arParamsN); $j++)
								$res_ar[] = PHPParser::ReplString($arParamsN[$j], $arAllStr);

							$arIncludeParams[$el_ind] = $res_ar;
						}
						else
							$arIncludeParams[$el_ind] = PHPParser::ReplString($el_val, $arAllStr);
					}
				}

				return Array(
						"SCRIPT_NAME"=>PHPParser::ReplString($arParams[0], $arAllStr),
						"PARAMS"=>$arIncludeParams,
						"VARIABLE"=>$var_name,
						);
			}
		}
		return false;
	}

	function GetComponentParams($instruction, $arAllStr)
	{
		if ($pos = strpos($instruction, "("))
		{
			$func_name = substr($instruction, 0, $pos + 1);
			if (preg_match("/^(\\\$[A-Z_][A-Z0-9_]*)(\s*=\s*)/i", $func_name, $arMatch))
			{
				$var_name = $arMatch[1];
				$func_name = substr($func_name, strlen($arMatch[0]));
			}
			else
			{
				$var_name = "";
			}

			$params = substr($instruction, $pos + 1);
			$arParams = PHPParser::GetParams($params);

			$arIncludeParams = array();
			$arFuncParams = array();
			PHPParser::GetParamsRec($arParams[2], $arAllStr, $arIncludeParams);
			PHPParser::GetParamsRec($arParams[4], $arAllStr, $arFuncParams);

			return array(
				"COMPONENT_NAME" => PHPParser::ReplString($arParams[0], $arAllStr),
				"TEMPLATE_NAME" => PHPParser::ReplString($arParams[1], $arAllStr),
				"PARAMS" => $arIncludeParams,
				"PARENT_COMP" => $arParams[3],
				"VARIABLE" => $var_name,
				"FUNCTION_PARAMS" => $arFuncParams,
			);
		}
	}

	function ParseScript($scriptContent)
	{
		$arComponents = array();
		$componentNumber = -1;

		$bInComponent = False;
		$bInPHP = False;

		$bInString = False;
		$quoteChar = "";
		$bSlashed = False;

		$string = False;
		$instruction = "";

		$scriptContentLength = StrLen($scriptContent);
		$ind = -1;
		while ($ind < $scriptContentLength - 1)
		{
			$ind++;
			$ch = SubStr($scriptContent, $ind, 1);

			if ($bInPHP)
			{
				if (!$bInString)
				{
					if (!$bInComponent && StrLen($instruction) > 0)
					{
						if (preg_match("#^\s*((\\\$[A-Z_][A-Z0-9_]*)\s*=)?\s*\\\$APPLICATION->IncludeComponent\s*\(#is", $instruction, $arMatches))
						{
							$arAllStr = array();
							$bInComponent = True;
							$componentNumber++;

							$arMatches[0] = LTrim($arMatches[0]);
							$arComponents[$componentNumber] = array(
								"START" => ($ind - StrLen($arMatches[0])),
								"END" => False,
								"DATA" => array()
							);
						}
					}
					if ($string !== False)
					{
						if ($bInComponent)
						{
							$arAllStr[] = $string;
							$instruction .= Chr(1).(Count($arAllStr) - 1).Chr(2);
						}
						$string = False;
					}
					if ($ch == ";")
					{
						//echo "<font color=\"#6600FF\">".$instruction."</font><br>";
						if ($bInComponent)
						{
							$bInComponent = False;
							$arComponents[$componentNumber]["END"] = $ind + 1/* - 1*/;
							$arComponents[$componentNumber]["DATA"] = PHPParser::GetComponentParams(preg_replace("#[ \r\n\t]#", "", $instruction), $arAllStr);
						}
						$instruction = "";
						continue;
					}
					if ($ch == "/" && $ind < $scriptContentLength - 2)
					{
						if (SubStr($scriptContent, $ind + 1, 1) == "/")
						{
							$endPos = StrPos($scriptContent, "\n", $ind + 2);

							if ($endPos === False)
								$ind = $scriptContentLength - 1;
							else
								$ind = $endPos;

							continue;
						}
						elseif (SubStr($scriptContent, $ind + 1, 1) == "*")
						{
							$endPos = StrPos($scriptContent, "*/", $ind + 2);

							if ($endPos === False)
								$ind = $scriptContentLength - 1;
							else
								$ind = $endPos + 1;

							continue;
						}
					}

					if ($ch == "\"" || $ch == "'")
					{
						$bInString = True;
						$string = "";
						$quoteChar = $ch;
						continue;
					}

					if ($ch == "?" && $ind < $scriptContentLength - 2 && SubStr($scriptContent, $ind + 1, 1) == ">")
					{
						$ind += 1;
						if ($bInComponent)
						{
							//echo "<font color=\"#6600FF\">".$instruction."</font><br>";
							$bInComponent = False;
							$arComponents[$componentNumber]["END"] = $ind - 1;
							$arComponents[$componentNumber]["DATA"] = PHPParser::GetComponentParams(preg_replace("#[ \r\n\t]#", "", $instruction), $arAllStr);
						}
						$instruction = "";
						$bInPHP = False;
						continue;
					}

					$instruction .= $ch;

					if ($ch == " " || $ch == "\r" || $ch == "\n" || $ch == "\t")
						continue;

					/*
					if ($bInComponent)
					{
						if ($ch == ",")
						{

							if ($parenthesesLevel == 1)
							{
								$arComponents[$componentNumber]["DATA"][$callParam] = $string;
								$callParam++;
							}
							else
							{

							}
						}
						elseif ($ch == "(")
						{
							$parenthesesLevel++;
						}
						elseif ($ch == ")")
						{
							$parenthesesLevel--;
						}
					}
					*/
				}
				else
				{
					if ($ch == "\\" && !$bSlashed)
					{
						$bSlashed = True;
						continue;
					}
					if ($ch == $quoteChar && !$bSlashed)
					{
						$bInString = False;
						continue;
					}
					$bSlashed = False;

					$string .= $ch;
				}

				if ($bInComponent)
				{

				}
			}
			else
			{
				if ($ch == "<")
				{
					if ($ind < $scriptContentLength - 5 && SubStr($scriptContent, $ind + 1, 4) == "?php")
					{
						$bInPHP = True;
						$ind += 4;
					}
					elseif ($ind < $scriptContentLength - 2 && SubStr($scriptContent, $ind + 1, 1) == "?")
					{
						$bInPHP = True;
						$ind += 1;
					}
				}
			}
		}

		return $arComponents;
	}

	// Components 2. Parse string and check if it is a component call. Return call params array
	function CheckForComponent2($str)
	{
		if (SubStr($str, 0, 5) == "<?"."php")
			$str = SubStr($str, 5);
		else
			$str = SubStr($str, 2);

		$str = SubStr($str, 0, -2);

		$bSlashed = false;
		$bInString = false;
		$arAllStr = Array();
		$new_str = "";
		$string_tmp = "";
		$i = -1;
		while ($i < StrLen($str) - 1)
		{
			$i++;
			$ch = SubStr($str, $i, 1);
			if (!$bInString)
			{
				if ($string_tmp != "")
				{
					$arAllStr[] = $string_tmp;
					$string_tmp = "";
					$new_str .= Chr(1).(Count($arAllStr) - 1).Chr(2);
				}

				//проверяем что не начинается комментарий
				if ($ch == "/" && $i+1 < StrLen($str))
				{
					$ti = 0;
					if (SubStr($str, $i+1, 1) == "*" && ($ti = StrPos($str, "*/", $i+2)) !== false)
						$ti += 1;
					elseif (SubStr($str, $i+1, 1)=="/" && ($ti = StrPos($str, "\n", $i+2)) !== false)
						$ti += 0;

					if ($ti)
						$i = $ti;

					continue;
				} // if($ch == "/" && $i+1<strlen($filesrc))

				if ($ch == " " || $ch == "\r" || $ch == "\n" || $ch == "\t")
					continue;
			} // if(!$bInString)

			if ($bInString && $ch == "\\" && !$bSlashed)
			{
				$bSlashed = true;
				continue;
			}

			if ($ch == "\"" || $ch == "'")
			{
				if ($bInString)
				{
					if (!$bSlashed && $quote_ch == $ch)
					{
						$bInString = false;
						continue;
					}
				}
				else
				{
					$bInString = true;
					$quote_ch = $ch;
					continue;
				}
			}
			elseif ($bInString && $ch == "\\")
				$bSlashed = true;

			$bSlashed = false;
			if ($bInString)
			{
				$string_tmp .= $ch;
				continue;
			}

			$new_str .= $ch;
		} // while($i<strlen($filesrc)-1)

		if ($pos = strpos($new_str, "("))
		{
			$func_name = substr($new_str, 0, $pos + 1);
			if (preg_match("/^(\\\$[A-Z_][A-Z0-9_]*)(\s*=\s*)/i", $func_name, $arMatch))
			{
				$var_name = $arMatch[1];
				$func_name = substr($func_name, strlen($arMatch[0]));
			}
			else
			{
				$var_name = "";
			}

			$func_name = preg_replace("'\x01([0-9]+)\x02'es", "\$arAllStr['\\1']", $func_name);
			$func_name = preg_replace("'\\\$GLOBALS\[(.+?)\]'s", "\$\\1", $func_name);

			switch (strtoupper($func_name))
			{
				case '$APPLICATION->INCLUDECOMPONENT(':
					$params = substr($new_str, $pos + 1);
					$arParams = PHPParser::GetParams($params);

					$arIncludeParams = array();
					$arFuncParams = array();
					PHPParser::GetParamsRec($arParams[2], $arAllStr, $arIncludeParams);
					PHPParser::GetParamsRec($arParams[4], $arAllStr, $arFuncParams);

					return array(
						"COMPONENT_NAME" => PHPParser::ReplString($arParams[0], $arAllStr),
						"TEMPLATE_NAME" => PHPParser::ReplString($arParams[1], $arAllStr),
						"PARAMS" => $arIncludeParams,
						"PARENT_COMP" => $arParams[3],
						"VARIABLE" => $var_name,
						"FUNCTION_PARAMS" => $arFuncParams,
					);
			}
		}
		return false;
	}

	// Parse file and return all PHP blocks in array
	function ParseFile($filesrc)
	{
		$arScripts = Array();
		$p = 0;
		$nLen = strlen($filesrc);
		while(($p = strpos($filesrc, "<?", $p))!==false)
		{
			$i = $p+2;
			$bSlashed = false;
			$bInString = false;
			while($i < $nLen-1)
			{
				$i++;
				$ch = substr($filesrc, $i, 1);
				if(!$bInString)
				{
					//проверяем что не начинается комментарий
					if($ch == "/" && $i+1 < $nLen)
					{
						//найдем позицию окончания php
						$posnext = strpos($filesrc, "?>", $i);
						if($posnext===false)
						{
							//окончания нет - значит скрипт незакончен
							$p = $nLen;
							break;
						}
						$posnext += 2;

						$ti = 0;
						if(substr($filesrc, $i+1, 1)=="*" && ($ti = strpos($filesrc, "*/", $i+2))!==false)
							$ti += 2;
						elseif(substr($filesrc, $i+1, 1)=="/" && ($ti = strpos($filesrc, "\n", $i+2))!==false)
							$ti += 1;

						if($ti)
						{
							// нашли начало($i) и конец комментария ($ti)
							// проверим что раньше конец скрипта или конец комментария (например в одной строке "//comment ? >")
							if($ti>$posnext && substr($filesrc, $i+1, 1)!="*")
							{
								// скрипт закончился раньше комментария
								// вырежем скрипт
								$arScripts[] = Array($p, $posnext, substr($filesrc, $p, $posnext-$p));
								$p = $posnext;
								break;
							}
							else
							{
								// комментарий закончился раньше скрипта
								$i = $ti - 1;
							}
						}
						continue;
					} // if($ch == "/" && $i+1 < $nLen)

					if($ch == "?" && $i+1 < $nLen && substr($filesrc, $i+1, 1)==">")
					{
						$i = $i+2;
						$arScripts[] = Array($p, $i, substr($filesrc, $p, $i-$p));
						$p = $i+1;
						break;
					}
				} // if(!$bInString)

				if($bInString && $ch == "\\" && !$bSlashed)
				{
					$bSlashed = true;
					continue;
				}

				if($ch == "\"" || $ch == "'")
				{
					if($bInString)
					{
						if(!$bSlashed && $quote_ch == $ch)
							$bInString = false;
					}
					else
					{
						$bInString = true;
						$quote_ch = $ch;
					}
				}
				elseif($bInString && $ch == "\\")
					$bSlashed = true;

				$bSlashed = false;
			} // while($i < $nLen-1)
			if($i >= $nLen)
				break;
			$p = $i;
		} // while(($p = strpos("<"."?", $filesrc))!==false)
		return $arScripts;
	}

	function PreparePHP($str)
	{
		if(substr($str, 0, 2) == "={" && substr($str, -1, 1)=="}" && strlen($str)>3)
			return substr($str, 2, -1);

		return '"'.EscapePHPString($str).'"';
	}

	// Return PHP string of component call params
	function ReturnPHPStr($arVals, $arParams)
	{
		$res = "";
		$un = md5(uniqid(""));
		$i=0;
		foreach($arVals as $key=>$val)
		{
			$i++;
			$comm = (strlen($arParams[$key]["NAME"])>0?"$un|$i|// ".$arParams[$key]["NAME"]:"");
			$res .= "\r\n\t\"".$key."\"\t=>\t";
			if(is_array($val) && count($val)>1)
				$res .= "Array(".$comm."\r\n";

			if(is_array($val) && count($val)>1)
			{
				$zn = '';
				foreach($val as $p)
				{
					if($zn!='') $zn.=",\r\n";
					$zn .= "\t\t\t\t\t".PHPParser::PreparePHP($p);
				}
				$res .= $zn."\r\n\t\t\t\t),";
			}
			elseif(is_array($val))
			{
				$res .= "Array(".PHPParser::PreparePHP($val[0])."),".$comm;
			}
			else
				$res .= PHPParser::PreparePHP($val).",".$comm;
		}

		$max = 0;
		$lngth = Array();
		for($j=1; $j<=$i; $j++)
		{
			$p = strpos($res, "$un|$j|");
			$pn = strrpos(substr($res, 0, $p), "\n");
			$l = ($p-$pn);
			$lngth[$j] = $l;
			if($max<$l)
				$max = $l;
		}

		for($j=1; $j<=$i; $j++)
			$res = str_replace($un."|$j|", str_repeat("\t", intval(($max-$lngth[$j]+7)/8)), $res);

		return Trim($res, " \t,\r\n");
	}


	function ReturnPHPStrRec($arVal, $level, $comm="")
	{
		$result = "";
		$pref = str_repeat("\t", $level);
		if (is_array($arVal))
		{
			$result .= "array(".(($level==1) ? $comm : "")."\r\n";
			foreach ($arVal as $key => $value)
				$result .= $pref."\t".((IntVal($key)."|" == $key."|") ? $key : PHPParser::PreparePHP($key))." => ".PHPParser::ReturnPHPStrRec($value, $level + 1);
			$result .= $pref."),\r\n";
		}
		else
		{
			$result .= PHPParser::PreparePHP($arVal).",".(($level==1) ? $comm : "")."\r\n";
		}
		return $result;
	}

	// Components 2. Return PHP string of component call params
	function ReturnPHPStr2($arVals, $arParams=array())
	{
		$res = "";
		$i = 0;

		foreach($arVals as $key => $val)
		{
			$i++;
			$comm = (strlen($arParams[$key]["NAME"])>0? "\t// ".$arParams[$key]["NAME"]:"");
			$res .= "\t\"".EscapePHPString($key)."\" => ";
			$res .= PHPParser::ReturnPHPStrRec($val, 1, $comm);
		}

		return Trim($res, " \t,\r\n");
	}

	function FindComponent($component_name, $filesrc, $src_line)
	{
		/* parse source file for PHP code */
		$arComponents = PHPParser::ParseScript($filesrc);

		/* identify the component by line number */
		$arComponent = False;
		for ($i = 0, $cnt = count($arComponents); $i < $cnt; $i++)
		{
			$nLineFrom = substr_count(substr($filesrc, 0, $arComponents[$i]["START"]), "\n") + 1;
			$nLineTo = substr_count(substr($filesrc, 0, $arComponents[$i]["END"]), "\n") + 1;

			if ($nLineFrom <= $src_line && $nLineTo >= $src_line)
			{
				if ($arComponents[$i]["DATA"]["COMPONENT_NAME"] == $component_name)
				{
					$arComponent = $arComponents[$i];
					break;
				}
			}
			if ($nLineTo > $src_line)
				break;
		}
		return $arComponent;
	}
}
?>