<?
class CSearchLanguage
{
	var $arStemInfo;
	var $_lang_id;
	var $_lang_bigramm_cache;
	var $_trigrams = array();

	function __construct($lang_id)
	{
		$this->_lang_id = $lang_id;
	}

	//Function loads language class
	static function GetLanguage($sLang)
	{
		static $arLanguages = array();

		if(!isset($arLanguages[$sLang]))
		{
			$class_name = strtolower("CSearchLanguage".$sLang);
			if(!class_exists($class_name))
			{
				//First try to load customized class
				$strDirName = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".$sLang."/search";
				$strFileName = $strDirName."/language.php";
				if(file_exists($strFileName))
					@include($strFileName);
				if(!class_exists($class_name))
				{
					//Then module class
					$strDirName = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/tools/".$sLang;
					$strFileName = $strDirName."/language.php";
					if(file_exists($strFileName))
						@include($strFileName);
					if(!class_exists($class_name))
					{
						$class_name = "CSearchLanguage";
					}
				}
			}
			$arLanguages[$sLang] =  new $class_name($sLang);
			$arLanguages[$sLang]->LoadTrigrams($strDirName);
			$arLanguages[$sLang]->arStemInfo = stemming_init($sLang);
		}

		return $arLanguages[$sLang];
	}

	//Reads file with trigrams (combinations not allowed in the words)
	function LoadTrigrams($dir_name)
	{
		if(empty($this->_trigrams))
		{
			$file_name = $dir_name."/trigram";
			if(file_exists($file_name) && is_file($file_name))
			{
				$text = file_get_contents($file_name);
				$ar = explode("\n", $text);
				foreach($ar as $trigramm)
				{
					if(strlen($trigramm) == 3)
					{
						$arScanCodesTmp1 = $this->ConvertToScancode($trigramm);
						$arScanCodesTmp2 = array_filter($arScanCodesTmp1);
						if(count($arScanCodesTmp2) == 3)
						{
							$key = implode(" ", $arScanCodesTmp2);
							$this->_trigrams[$key] = true;
						}
					}
				}
			}
		}
	}

	function HasTrigrams()
	{
		return !empty($this->_trigrams);
	}

	//Check phrase against trigrams
	function CheckTrigrams($arScanCodes)
	{
		$result = 0;
		$c = count($arScanCodes)-2;
		for($i = 0; $i < $c; $i++)
		{
			if(isset($this->_trigrams[$arScanCodes[$i]." ".$arScanCodes[$i+1]." ".$arScanCodes[$i+2]]))
			{
				$result++;
			}
		}
		return $result;
	}

	//This function returns positions of the letters
	//on the keyboard. This one is default English layout
	function GetKeyboardLayout()
	{
		return array(
			"lo" => "`          - ".
				"qwertyuiop[]".
				"asdfghjkl;'".
				"zxcvbnm,. ",
			"hi" => "~            ".
				"QWERTYUIOP{}".
				"ASDFGHJKL:\"".
				"ZXCVBNM<> "
		);
	}

	function ConvertFromScancode($arScancode)
	{
		$result = "";
		$keyboard = $this->GetKeyboardLayout();
		foreach($arScancode as $code)
			$result .= substr($keyboard["lo"], $code, 1);
		return $result;
	}

	//This function converts text between layouts
	static function ConvertKeyboardLayout($text, $from, $to)
	{
		static $keyboards = array();
		//Fill local cache
		if(!array_key_exists($from, $keyboards))
		{
			$ob = CSearchLanguage::GetLanguage($from);
			$keyboard = $ob->GetKeyboardLayout();
			if(is_array($keyboard))
				$keyboards[$from] = $keyboard["lo"].$keyboard["hi"];
			else
				$keyboards[$from] = null;
		}

		if(!array_key_exists($to, $keyboards))
		{
			$ob = CSearchLanguage::GetLanguage($to);
			$keyboard = $ob->GetKeyboardLayout();
			if(is_array($keyboard))
				$keyboards[$to] = $keyboard["lo"].$keyboard["hi"];
			else
				$keyboards[$to] = null;
		}

		//when both layouts defined
		if(isset($keyboards[$from]) && isset($keyboards[$to]))
		{
			$result = "";
			//go throught text char by char
			$len = strlen($text);
			for($i = 0; $i < $len; $i++)
			{
				$ch = substr($text, $i ,1);
				if($ch !== " ")
				{
					//and replace char with alternative
					$p = strpos($keyboards[$from], $ch);
					if($p != false)
						$ch = substr($keyboards[$to], $p, 1);
				}
				$result .= $ch;
			}

			return $result;
		}
		else
		{
			return $text;
		}
	}

	//This function converts text into array of character positions
	//on the keyboard. Not defined chars turns into "false" value.
	function ConvertToScancode($text, $strict=false)
	{
		$keyboard = $this->GetKeyboardLayout();
		$keyboard_lo = $keyboard["lo"];
		$keyboard_hi = $keyboard["hi"];

		$result = array();
		$len = strlen($text);
		for($i = 0; $i < $len; $i++)
		{
			$ch = substr($text, $i ,1);
			if($ch === " ")
			{
				$result[] = false;
			}
			else
			{
				//First search in lower case
				//then in upper (shift pressed)
				$p1 = strpos($keyboard_lo, $ch);
				if($p1 === false)
					$p1 = strpos($keyboard_hi, $ch);

				if($strict && is_array($this->arStemInfo))
				{
					if(strpos($this->arStemInfo["abc"], $ch))
						$result[] = $p1;
					else
						$result[] = false;
				}
				else
				{
					$result[] = $p1;
				}
			}
		}

		return $result;
	}

	function GuessLanguage($text)
	{
		if(strlen($text) <= 0)
			return false;

		static $arLanguages = array();
		if(empty($arLanguages))
		{
			$arLanguages[] = "en";//English is always in mind and on the first place
			$rsLanguages = CLanguage::GetList(($b=""), ($o=""));
			while($arLanguage = $rsLanguages->Fetch())
				if($arLanguage["LID"] != "en")
					$arLanguages[] = $arLanguage["LID"];
		}

		if(count($arLanguages) < 2)
			return false;

		$languages_from = array();
		$max_len = 0;

		//First try to detect language which
		//was used to type the phrase
		foreach($arLanguages as $lang)
		{
			$ob = CSearchLanguage::GetLanguage($lang);

			$arScanCodesTmp1 = $ob->ConvertToScancode($text, true);
			$arScanCodesTmp2_cnt = count(array_filter($arScanCodesTmp1));

			//It will be one with most converted chars
			if($arScanCodesTmp2_cnt > $max_len)
			{
				$max_len = $arScanCodesTmp2_cnt;
				$languages_from = array($lang => $arScanCodesTmp1);
			}
			elseif($arScanCodesTmp2_cnt == $max_len)
			{
				$languages_from[$lang] = $arScanCodesTmp1;
			}
		}

		if($max_len < 2)
			return false;

		if(count($languages_from) <= 0)
			return false;

		//If more than one language is detected as input
		//try to get one with best trigram info
		if(count($languages_from) > 1)
		{
			$arDetection = array();
			$i = 0;
			foreach($languages_from as $lang => $arScanCodes)
			{
				$arDetection[$lang] = array();

				$ob = CSearchLanguage::GetLanguage($lang);

				$arDetection[$lang][] = $ob->HasTrigrams();
				$arDetection[$lang][] = $ob->CheckTrigrams($arScanCodes);
				$deviation = $ob->GetDeviation($arScanCodes);
				$arDetection[$lang][] = $deviation[1];
				$arDetection[$lang][] = $deviation[0];
				$arDetection[$lang][] = $i;
				$i++;
			}
			uasort($arDetection, array("CSearchLanguage", "cmp"));

			$language_from = key($arDetection);
			$arScanCodes = $languages_from[$language_from];
		}
		else
		{
			$language_from = key($languages_from);
			$arScanCodes = $languages_from[$language_from];
		}

		//Now try the best to detect the language
		$arDetection = array();
		$i = 0;
		foreach($arLanguages as $lang)
		{
			$arDetection[$lang] = array();

			$ob = CSearchLanguage::GetLanguage($lang);

			$arDetection[$lang][] = $ob->HasBigrammInfo();
			$arDetection[$lang][] = $ob->CheckTrigrams($arScanCodes);

			//Calculate how far sequence of scan codes
			//is from language model
			$deviation = $ob->GetDeviation($arScanCodes);

			$arDetection[$lang][] = $deviation[1];
			$arDetection[$lang][] = $deviation[0];
			$arDetection[$lang][] = $i;
			$i++;
		}

		uasort($arDetection, array("CSearchLanguage", "cmp"));
		$language_to = key($arDetection);
		//echo "<pre>",print_r($arDetection,1),"<pre>";
		$alt_text = CSearchLanguage::ConvertKeyboardLayout($text, $language_from, $language_to);
		if($alt_text === $text)
			return false;

		return array("from" => $language_from, "to" => $language_to);
	}

	//Compare to results of text analysis
	function cmp($a, $b)
	{
		if($a[0] && !$b[0]) //On first place we check if model supports bigrams check
			return -1;
		elseif($b[0] && !$a[0])
			return 1;
		else
		{
			if($a[1] < $b[1]) //Second check if text has minimum trigrams
				return -1;
			elseif($a[1] > $b[1])
				return 1;
			else
			{
				if($a[2] < $b[2]) //Third check is for erorr bigrams
					return -1;
				elseif($a[2] > $b[2])
					return 1;
				else
				{
					if($a[3] < $b[3]) //Then we check deviation from the model
						return -1;
					elseif($a[3] > $b[3])
						return 1;
					else
					{
						if($a[4] < $b[4]) //Just sort
							return -1;
						else
							return 1;
					}
				}
			}
		}
	}

	//Function returns distance of the text (sequence of scan codes)
	//from language model
	function GetDeviation($arScanCodes)
	{
		//This is language model
		$lang_bigrams = $this->GetBigrammScancodeFreq();
		$lang_count = $lang_bigrams["count"];
		unset($lang_bigrams["count"]);

		//This is text model
		$text_bigrams = $this->ConvertToBigramms($arScanCodes);
		$count = $text_bigrams["count"];
		unset($text_bigrams["count"]);

		$deviation = 0;
		$zeroes = 0;
		foreach($text_bigrams as $key => $value)
		{
			if(!isset($lang_bigrams[$key]))
			{
				$zeroes++;
				$deviation += $value/$count;
			}
			else
			{
				$deviation += abs($value/$count - $lang_bigrams[$key]/$lang_count);
			}
		}

		return array($deviation, $zeroes);
	}

	//Function returns bigramms of the text (array of scancodes)
	//For example "FAT RAT" will be
	//array("FA", "AT", "RA", "AT")
	//This is model of the text
	function ConvertToBigramms($arScancodes)
	{
		$result = array();

		$len = count($arScancodes)-1;
		for($i = 0; $i < $len; $i++)
		{
			$code1 = $arScancodes[$i];
			$code2 = $arScancodes[$i+1];
			if($code1 !== false && $code2 !== false)
			{
				$result["count"]++;
				$result[$code1." ".$code2]++;
			}
		}
		return $result;
	}

	function HasBigrammInfo()
	{
		return is_callable(array($this, "getbigrammletterfreq"));
	}

	//Function returns model of the language
	function GetBigrammScancodeFreq()
	{
		if(!$this->HasBigrammInfo())
			return array("count"=>1);

		if(!isset($this->_lang_bigramm_cache))
		{
			$bigramms = $this->GetBigrammLetterFreq();
			$keyboard = $this->GetKeyboardLayout();
			$keyboard_lo = $keyboard["lo"];
			$keyboard_hi = $keyboard["hi"];

			$result = array();
			foreach($bigramms as $letter1 => $row)
			{
				$p1 = strpos($keyboard_lo, $letter1);
				if($p1 === false)
					$p1 = strpos($keyboard_hi, $letter1);

				$i = 0;
				foreach($bigramms as $letter2 => $tmp)
				{
					$p2 = strpos($keyboard_lo, $letter2);
					if($p2 === false)
						$p2 = strpos($keyboard_hi, $letter2);

					$weight = $row[$i];
					$result["count"] += $weight;
					$result[$p1." ".$p2] = $weight;
					$i++;
				}
			}
			$this->_lang_bigramm_cache = $result;
		}
		return $this->_lang_bigramm_cache;
	}
}
?>
