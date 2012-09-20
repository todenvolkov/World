<?
define("PATH2CONVERT_TABLES", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/cvtables/");
global $BX_CHARSET_TABLE_CACHE;
$BX_CHARSET_TABLE_CACHE = Array();

class CharsetConverter
{
	function HexToUtf($utfCharInHex)
	{
		$result = "";

		$utfCharInDec = hexdec($utfCharInHex);
		if ($utfCharInDec < 128)
			$result .= chr($utfCharInDec);
		elseif ($utfCharInDec < 2048)
			$result .= chr(($utfCharInDec >> 6) + 192).chr(($utfCharInDec & 63) + 128);
		elseif ($utfCharInDec < 65536)
			$result .= chr(($utfCharInDec >> 12) + 224).chr((($utfCharInDec >> 6) & 63) + 128).chr(($utfCharInDec & 63) + 128);
		elseif ($utfCharInDec < 2097152)
			$result .= chr($utfCharInDec >> 18 + 240).chr((($utfCharInDec >> 12) & 63) + 128).chr(($utfCharInDec >> 6) & 63 + 128). chr($utfCharInDec & 63 + 128);

		return $result;
	}


	function BuildConvertTable()
	{
		global $BX_CHARSET_TABLE_CACHE;
		$this->errorMessage = "";

		for ($i = 0; $i < func_num_args(); $i++)
		{
			$fileName = func_get_arg($i);

			if($BX_CHARSET_TABLE_CACHE[$fileName])
				continue;

			$BX_CHARSET_TABLE_CACHE[$fileName] = Array();

			if(!file_exists(PATH2CONVERT_TABLES.$fileName))
			{
				$this->errorMessage .= str_replace("#FILE#", PATH2CONVERT_TABLES.$fileName, "File #FILE# is not found.");
				continue;
			}

			if (!is_file(PATH2CONVERT_TABLES.$fileName))
			{
				$this->errorMessage .= str_replace("#FILE#", PATH2CONVERT_TABLES.$fileName, "File #FILE# is not a file.");
				continue;
			}

			if (!($hFile = fopen(PATH2CONVERT_TABLES.$fileName, "r")))
			{
				$this->errorMessage .= str_replace("#FILE#", PATH2CONVERT_TABLES.$fileName, "Can not open file #FILE# for reading.");
				continue;
			}

			while (!feof($hFile))
			{
				if ($line = trim(fgets($hFile, 1024)))
				{
					if (substr($line, 0, 1) != "#")
					{
						$hexValue = preg_split("/[\s,]+/", $line, 3);
						if (substr($hexValue[1], 0, 1) != "#")
						{
							$key = strtoupper(str_replace("0x", "", $hexValue[1]));
							$value = strtoupper(str_replace("0x", "", $hexValue[0]));
							$BX_CHARSET_TABLE_CACHE[func_get_arg($i)][$key] = $value;
						}
					}
				}
			}

			fclose($hFile);
		}

		return $BX_CHARSET_TABLE_CACHE;
	}


	function Convert($sourceString, $charsetFrom, $charsetTo)
	{
		$this->errorMessage = "";

		if (strlen($sourceString) <= 0)
		{
			$this->errorMessage .= "Nothing to convert.";
			return false;
		}

		if (strlen($charsetFrom) <= 0)
		{
			$this->errorMessage .= "Source charset is not set.";
			return false;
		}

		if (strlen($charsetTo) <= 0)
		{
			$this->errorMessage .= "Destination charset is not set.";
			return false;
		}

		$charsetFrom = strtolower($charsetFrom);
		$charsetTo = strtolower($charsetTo);

		if($charsetFrom == $charsetTo)
			return $sourceString;

		$resultString = "";
		if($charsetFrom == "ucs-2")
		{
			$arConvertTable = $this->BuildConvertTable($charsetTo);
			for($i = 0; $i < strlen($sourceString); $i+=2)
			{
				$hexChar = strtoupper(dechex(ord($sourceString[$i])).dechex(ord($sourceString[$i+1])));
				$hexChar = str_pad($hexChar, 4, "0", STR_PAD_LEFT);
				if($arConvertTable[$charsetTo][$hexChar])
				{
					if($charsetTo != "utf-8")
						$resultString .= chr(hexdec($arConvertTable[$charsetTo][$hexChar]));
					else
						$resultString .= $this->HexToUtf($arConvertTable[$charsetTo][$hexChar]);
				}
			}
		}
		elseif($charsetFrom == "utf-16")
		{
			$arConvertTable = $this->BuildConvertTable($charsetTo);
			for($i = 0; $i < strlen($sourceString); $i+=2)
			{
				$hexChar = sprintf("%02X%02X", ord($sourceString[$i+1]), ord($sourceString[$i]));
				if($arConvertTable[$charsetTo][$hexChar])
				{
					if($charsetTo != "utf-8")
						$resultString .= chr(hexdec($arConvertTable[$charsetTo][$hexChar]));
					else
						$resultString .= $this->HexToUtf($arConvertTable[$charsetTo][$hexChar]);
				}
			}
		}
		elseif($charsetFrom != "utf-8")
		{
			if($charsetTo != "utf-8")
				$arConvertTable = $this->BuildConvertTable($charsetFrom, $charsetTo);
			else
				$arConvertTable = $this->BuildConvertTable($charsetFrom);

			if(!$arConvertTable)
				return false;

			$stringLength = (extension_loaded("mbstring") ? mb_strlen($sourceString, $charsetFrom) : strlen($sourceString));
			for ($i = 0; $i < $stringLength; $i++)
			{
				$hexChar = "";
				$unicodeHexChar = "";
				$hexChar = strtoupper(dechex(ord($sourceString[$i])));

				if(strlen($hexChar) == 1)
					$hexChar = "0".$hexChar;

				if(($charsetFrom == "gsm0338") && ($hexChar == '1B'))
				{
					$i++;
					$hexChar .= strtoupper(dechex(ord($sourceString[$i])));
				}

				if($charsetTo != "utf-8")
				{
					if(in_array($hexChar, $arConvertTable[$charsetFrom]))
					{
						$unicodeHexChar = array_search($hexChar, $arConvertTable[$charsetFrom]);
						$arUnicodeHexChar = explode("+", $unicodeHexChar);
						for ($j = 0; $j < count($arUnicodeHexChar); $j++)
						{
							if (array_key_exists($arUnicodeHexChar[$j], $arConvertTable[$charsetTo]))
								$resultString .= chr(hexdec($arConvertTable[$charsetTo][$arUnicodeHexChar[$j]]));
							else
								$this->errorMessage .= str_replace("#CHAR#", $sourceString[$i], "Can not find maching char \"#CHAR#\" in destination encoding table.");
						}
					}
					else
						$this->errorMessage .= str_replace("#CHAR#", $sourceString[$i], "Can not find maching char \"#CHAR#\" in source encoding table.");
				}
				else
				{
					if(in_array("$hexChar", $arConvertTable[$charsetFrom]))
					{
						$unicodeHexChar = array_search($hexChar, $arConvertTable[$charsetFrom]);
						$arUnicodeHexChar = explode("+", $unicodeHexChar);
						for ($j = 0; $j < count($arUnicodeHexChar); $j++)
							$resultString .= $this->HexToUtf($arUnicodeHexChar[$j]);
					}
					else
						$this->errorMessage .= str_replace("#CHAR#", $sourceString[$i], "Can not find maching char \"#CHAR#\" in source encoding table.");
				}
			}
		}
		else
		{
			$hexChar = "";
			$unicodeHexChar = "";

			$arConvertTable = $this->BuildConvertTable($charsetTo);
			if(!$arConvertTable)
				return false;

			foreach($arConvertTable[$charsetTo] as $unicodeHexChar => $hexChar)
			{
				$EntitieOrChar = chr(hexdec($hexChar));
				$sourceString = str_replace($this->HexToUtf($unicodeHexChar), $EntitieOrChar, $sourceString);
			}
			$resultString = $sourceString;
		}

		return $resultString;
	}
}
?>