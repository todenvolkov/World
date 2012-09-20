<?
IncludeModuleLangFile(__FILE__);

class CSecurityFilter
{
	var $_tmp_dir = false;
	var $_filter_action = false;
	var $_filter_stop = false;
	var $_filter_log = false;
	var $_found_vars = array();

	var $_filters = false;
	var $_filters_keys = false;
	var $_filters_values = false;
	var $_sql_filters = false;
	var $_sql_filters_keys = false;
	var $_sql_filters_values = false;
	var $_php_filters = false;
	var $_php_filters_keys = false;
	var $_php_filters_values = false;
	var $_blocked = false;

	function __construct($char = false)
	{
		return $this->CSecurityFilter($char);
	}

	function CSecurityFilter($char = false)
	{
		if($char === false)
			$char = " ";
		if(!$this->_tmp_dir)
			$this->_tmp_dir = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload");

		$this->_filter_action = COption::GetOptionString("security", "filter_action");
		$this->_filter_stop = COption::GetOptionString("security", "filter_stop");
		$this->_filter_log = COption::GetOptionString("security", "filter_log");

		$_M='(?:[\x09\x0a\x0d\\\\]*)';
		$_M3='(?:[\x09\x0a\x0d\\\\\s]*)';
		$_M2='(?:(?:[\x09\x0a\x0d\\\\\s]|(?:\/\*.*?\*\/))*)';

		$_Jj ="(?:j|(?:\\\\0*[64]a))";
		$_Ja ="(?:a|(?:\\\\0*[64]1))";
		$_Jb ="(?:b|(?:\\\\0*[64]2))";

		$_Jv ="(?:v|(?:\\\\0*[75]6))";
		$_Js ="(?:s|(?:\\\\0*[75]3))";
		$_Jc ="(?:c|(?:\\\\0*[64]3))";
		$_Jr ="(?:r|(?:\\\\0*[75]2))";
		$_Ji ="(?:i|(?:\\\\0*[64]9))";
		$_Jp ="(?:p|(?:\\\\0*[75]0))";
		$_Jt ="(?:t|(?:\\\\0*[75]4))";

		$_Je ="(?:e|(?:\\\\0*[64]5))";
		$_Jx ="(?:x|(?:\\\\0*[75]8))";
		$_Jo ="(?:o|(?:\\\\0*[64]f))";
		$_Jn ="(?:n|(?:\\\\0*[64]e))";

		$_Jm ="(?:m|(?:\\\\0*[64]d))";

		$_Jh ="(?:h|(?:\\\\0*[64]8))";

		$_Jgav ="(?:@|(?:\\\\0*40))";

		$_Jdd="(?:\\:|(?:\\\\0*3a))";
		$_Jss="(?:\\(|(?:\\\\0*28))";

		$_Jvopr="(?:\\?|(?:\\\\0*3f))";
		$_Jgalka="(?:\\<|(?:\\\\0*3c))";

		$_WS_OPT = "[\\x00\\x09\\x0A\\x0B\\x0C\\x0D\\s]*";

		if(!$this->_filters)
		{
			$this->_filters = array(
				"/({$_Jb}{$_M}{$_Je}{$_M}{$_Jh}{$_M})({$_Ja}{$_M}{$_Jv}{$_M}{$_Ji}{$_M}{$_Jo}{$_M}{$_Jr}{$_WS_OPT}{$_Jdd})/is" => "\\1 * \\2", //space is not enought

				"/(\<{$_M}s{$_M}c{$_M})(r{$_M}i{$_M}p{$_M}t)/is" => "\\1{$char}\\2",
				"/(\<{$_M}x{$_M}:{$_M}s{$_M}c{$_M})(r{$_M}i{$_M}p{$_M}t)/is" => "\\1{$char}\\2",
				"/(\<{$_M}a{$_M}p{$_M}p{$_M})(l{$_M}e{$_M}t)/is" => "\\1{$char}\\2",
				"/(\<{$_M}e{$_M}m{$_M}b)(e{$_M}d)/is" => "\\1{$char}\\2",
				"/(\<{$_M}s{$_M}t{$_M})(y{$_M}l{$_M}e)/is" => "\\1{$char}\\2",

				"/([\\x00\\x09\\x0A\\x0B\\x0C\\x0D\\s\\x2f]s{$_M}t{$_M})(y{$_M}l{$_M}e{$_WS_OPT}\=)(?!\\s*\"(\\s*[a-z-]+\\s*:\\s*([0-9a-z\\s%,.#-]+|rgb\\s*\\([0-9,\\s]+\\))\\s*;{0,1}){0,}\\s*\")/is" => "\\1{$char}\\2",

				"/(o{$_M}n{$_M}A{$_M})(b{$_M}o{$_M}r{$_M}t{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}B{$_M})(l{$_M}u{$_M}r{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}C{$_M})(h{$_M}a{$_M}n{$_M}g{$_M}e{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}C{$_M})(l{$_M}i{$_M}c{$_M}k{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}D{$_M})(b{$_M}l{$_M}C{$_M}l{$_M}i{$_M}c{$_M}k{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}E{$_M})(r{$_M}r{$_M}o{$_M}r{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}F{$_M})(o{$_M}c{$_M}u{$_M}s{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}K{$_M})(e{$_M}y{$_M}D{$_M}o{$_M}w{$_M}n{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}K{$_M})(e{$_M}y{$_M}P{$_M}r{$_M}e{$_M}s{$_M}s{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}K{$_M})(e{$_M}y{$_M}U{$_M}p{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}L{$_M})(o{$_M}a{$_M}d{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}M{$_M})(o{$_M}u{$_M}s{$_M}e{$_M}D{$_M}o{$_M}w{$_M}n{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}M{$_M})(o{$_M}u{$_M}s{$_M}e{$_M}M{$_M}o{$_M}v{$_M}e{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}M{$_M})(o{$_M}u{$_M}s{$_M}e{$_M}O{$_M}u{$_M}t{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}M{$_M})(o{$_M}u{$_M}s{$_M}e{$_M}O{$_M}v{$_M}e{$_M}r{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}M{$_M})(o{$_M}u{$_M}s{$_M}e{$_M}U{$_M}p{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}M{$_M})(o{$_M}v{$_M}e{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}R{$_M})(e{$_M}s{$_M}e{$_M}t{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}R{$_M})(e{$_M}s{$_M}i{$_M}z{$_M}e{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}S{$_M})(e{$_M}l{$_M}e{$_M}c{$_M}t{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}S{$_M})(u{$_M}b{$_M}m{$_M}i{$_M}t{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}U{$_M})(n{$_M}l{$_M}o{$_M}a{$_M}d{$_WS_OPT}\=)/is" => "\\1{$char}\\2",

				"/(o{$_M}n{$_M}m{$_M}o{$_M})(u{$_M}s{$_M}e{$_M}l{$_M}e{$_M}a{$_M}v{$_M}e{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}m{$_M}o{$_M}u{$_M})(s{$_M}e{$_M}e{$_M}n{$_M}t{$_M}e{$_M}r{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}s{$_M}e{$_M}l{$_M})(e{$_M}c{$_M}t{$_M}s{$_M}t{$_M}a{$_M}r{$_M}t{$_WS_OPT}\=)/is" => "\\1{$char}\\2",
				"/(o{$_M}n{$_M}s{$_M}e{$_M}l{$_M})(e{$_M}c{$_M}t{$_M}e{$_M}n{$_M}d{$_WS_OPT}\=)/is" => "\\1{$char}\\2",

				"/(u{$_M}r{$_M}n{$_M2}\:{$_M2}s{$_M})(c{$_M}h{$_M}e{$_M}m{$_M}a{$_M}s{$_M}\-{$_M}m{$_M}i{$_M}c{$_M}r{$_M}o{$_M}s{$_M}o{$_M}f{$_M}t{$_M}\-{$_M}c{$_M}o{$_M}m{$_M2}\:)/"=>"\\1{$char}\\2",

				"/(d{$_M}a{$_M}t{$_M})(a{$_M}\:)/is" => "\\1{$char}\\2",
				"/(\<{$_M}f{$_M}r{$_M}a{$_M})(m{$_M}e)/is" => "\\1{$char}\\2",
				"/(\<{$_M}i{$_M}f{$_M}r{$_M})(a{$_M}m{$_M}e)/is" => "\\1{$char}\\2",
				"/(\<{$_M}f{$_M}o{$_M})(r{$_M}m)/is" => "\\1{$char}\\2",
				"/(\.{$_M}c{$_M}o{$_M})(o{$_M}k{$_M}i{$_M}e)/is" => "\\1{$char}\\2",
				"/(\<{$_M}o{$_M}b{$_M})(j{$_M}e{$_M}c{$_M}t)/is" => "\\1{$char}\\2",
				"/(\<{$_M}l{$_M}i{$_M})(n{$_M}k)/is" => "\\1{$char}\\2",

				"/({$_Jgav}{$_M}{$_Ji}{$_M}{$_Jm})({$_M}{$_Jp}{$_M}{$_Jo}{$_M}{$_Jr}{$_M}{$_Jt})/"=> "\\1 * \\2", //space is not enought

				"/(\<{$_M}m{$_M}e{$_M}t)({$_M}a)/is" => "\\1{$char}\\2",
				"/(\<{$_M}L{$_M}A{$_M}Y{$_M})(E{$_M}R)/is" => "\\1{$char}\\2",
				"/(\-{$_M}m{$_M}o{$_M}z{$_M}\-{$_M}b{$_M}i{$_M})(n{$_M}d{$_M}i{$_M}n{$_M}g{$_M}{$_WS_OPT}\:{$_WS_OPT}{$_M}u{$_M}r{$_M}l)/is" => "\\1{$char}\\2",

				"/(<h{$_M}t{$_M})(m{$_M}l)/is" => "\\1{$char}\\2",
				"/(<x{$_M}m{$_M})(l)/is" => "\\1{$char}\\2",

				"/({$_Jgalka}{$_Jvopr}{$_M}{$_Ji}{$_M})({$_Jm}{$_M}{$_Jp}{$_M}{$_Jo}{$_M}{$_Jr}{$_M}{$_Jt})/is" => "\\1 * \\2", //space is not enought

				"/({$_Jj}{$_M3}{$_Ja}{$_M3}{$_Jv}{$_M3})({$_Ja}{$_M3}{$_Js}{$_M3}{$_Jc}{$_M3}{$_Jr}{$_M3}{$_Ji}{$_M3}{$_Jp}{$_M3}{$_Jt}{$_M3}{$_Jdd})/is" => "\\1 * \\2", //space is not enought
				"/({$_Jv}{$_M3}{$_Jb}{$_M3})({$_Js}{$_M3}{$_Jc}{$_M3}{$_Jr}{$_M3}{$_Ji}{$_M3}{$_Jp}{$_M3}{$_Jt}{$_M3}{$_Jdd})/is" => "\\1 * \\2", //space is not enought
				"/({$_Je}{$_M2}{$_Jx}{$_M2})({$_Jp}{$_M2}{$_Jr}{$_M2}{$_Je}{$_M2}{$_Js}{$_M2}{$_Js}{$_M2}{$_Ji}{$_M2}{$_Jo}{$_M2}{$_Jn}{$_M2}{$_Jss})/is" => "\\1 * \\2", //space is not enought

				"/(f{$_M}r{$_M}o{$_M}m)({$_M}c{$_M}h{$_M}a{$_M}r{$_M}c{$_M}o{$_M}d{$_M}e{$_M3}\()/"=>"\\1{$char}\\2",
				"/(u{$_M}n{$_M}e{$_M})(s{$_M}c{$_M}a{$_M}p{$_M}e{$_M3}\()/"=>"\\1{$char}\\2",

			);
		}

		$this->_filters_keys = array_keys($this->_filters);
		$this->_filters_values = array_values($this->_filters);

		$sql_space = "(?:[\\x00-\\x20\(\)\'\"\`]|(?:\\/\\*.*?\\*\\/)|(?:--.*?[\\n\\r]))+";
		if(!$this->_sql_filters)
		{
			$this->_sql_filters = array(
				"/(uni)(on{$sql_space}.+{$sql_space}sel)(ect)/is" => "\\1{$char}\\2{$char}\\3",
				"/(uni)(on{$sql_space}sel)(ect)/is" => "\\1{$char}\\2{$char}\\3",

				"/(sel)(ect{$sql_space}.+{$sql_space}fr)(om)/is" => "\\1{$char}\\2{$char}\\3",
				"/(fr)(om{$sql_space}.+{$sql_space}wh)(ere)/is" => "\\1{$char}\\2{$char}\\3",

				"/(alt)(er)({$sql_space})(database|table|function|procedure|server|event|view|index)/is" => "\\1{$char}\\2\\3\\4",
				"/(cre)(ate)({$sql_space})(database|table|function|procedure|server|event|view|index)/is" => "\\1{$char}\\2\\3\\4",
				"/(dr)(op)({$sql_space})(database|table|function|procedure|server|event|view|index)/is" => "\\1{$char}\\2\\3\\4",

				"/(upd)(ate{$sql_space}.+{$sql_space}se)(t)/is" => "\\1{$char}\\2{$char}\\3",
				"/(ins)(ert{$sql_space}.+{$sql_space}val)(ue)/is" => "\\1{$char}\\2{$char}\\3",
				"/(ins)(ert{$sql_space}.+{$sql_space}se)(t)/is" => "\\1{$char}\\2{$char}\\3",
				"/(i)(nto{$sql_space}out)(file)/is" => "\\1{$char}\\2{$char}\\3",
				"/(i)(nto{$sql_space}dump)(file)/is" => "\\1{$char}\\2{$char}\\3",
				"/(load_)(file\\()/is" => "\\1{$char}\\2",

				"/({$sql_space}[sx]p)(_\w+\()/" => "\\1{$char}\\2",

				"/(ex)(ec\()/"=>"\\1{$char}\\2",

				"/(fr)(om.+lim)(it)/is" => "\\1{$char}\\2{$char}\\3",

				"/(ben)(chmark\\()/is" => "\\1{$char}\\2",
				"/(sl)(eep\\()/is" => "\\1{$char}\\2",
				"/(us)(er\\()/is" => "\\1{$char}\\2",
				"/(ver)(sion\\()/is" => "\\1{$char}\\2",
				"/(dat)(abase\\()/is" => "\\1{$char}\\2",
				"/(sche)(ma\\()/is" => "\\1{$char}\\2",
				"/(sub)(string\\()/is" => "\\1{$char}\\2",
			);
		}

		$this->_sql_filters_keys = array_keys($this->_sql_filters);
		$this->_sql_filters_values = array_values($this->_sql_filters);

		if(!$this->_php_filters)
		{
			$this->_php_filters = array(
				"/(\\.)(\\.[\\\\\/])/is" => "\\1{$char}\\2", //directory up, ../
				"/[\\.\\/\\\\\\x20\\x22\\x3c\\x3e\\x5c]{30,}/" => " X "
			);
		}

		$this->_php_filters_keys = array_keys($this->_php_filters);
		$this->_php_filters_values = array_values($this->_php_filters);

	}

	/*
	Function is used in regular expressions in order to decode characters presented as &#123;
	*/
	function _decode_cb($in)
	{
		$ad = $in[2];
		if($ad == ';')
			$ad="";
		$num = intval($in[1]);
		return chr($num).$ad;
	}

	/*
	Function is used in regular expressions in order to decode characters presented as  &#xAB;
	*/
	function _decode_cb_hex($in)
	{
		$ad = $in[2];
		if($ad==';')
			$ad="";
		$num = intval(hexdec($in[1]));
		return chr($num).$ad;
	}

	/*
	Decodes string from html codes &#***;
	One pass!
	-- Decode only a-zA-Z:().=, because only theese are used in filters
	*/
	function _decode($str)
	{
		$str= preg_replace_callback("/\&\#(\d+)([^\d])/is", array("CSecurityFilter", "_decode_cb"), $str);
		return preg_replace_callback("/\&\#x([\da-f]+)([^\da-f])/is", array("CSecurityFilter", "_decode_cb_hex"), $str);
	}

	function _log($str, $var_name, $type)
	{
		$this->_found_vars[$var_name] = $str;
		CEventLog::Log("SECURITY", "SECURITY_FILTER_".$type, "security", $var_name, "==".base64_encode($str));
	}

	function _block($ip)
	{
		static $blocked = array();
		if(!array_key_exists($ip, $blocked))
		{
			$rule = new CSecurityIPRule;
			$rule->Add(array(
				"RULE_TYPE" => "A",
				"ACTIVE" => "Y",
				"ADMIN_SECTION" => "Y",
				"NAME" => GetMessage("SECURITY_FILTER_IP_RULE", array("#IP#" => $ip)),
				"ACTIVE_FROM" => ConvertTimeStamp(false, "FULL"),
				"ACTIVE_TO" => ConvertTimeStamp(time()+COption::GetOptionInt("security", "filter_duration")*60, "FULL"),
				"INCL_IPS" => array($ip),
				"INCL_MASKS" => array("*"),
			));
			$blocked[$ip] = true;
			$this->_blocked = true;
		}
	}

	/*
	PHP Injection detection
	*/
	function _dostrphp(&$str, $var_name)
	{
		if($this->_filter_action === "clear" || $this->_filter_action === "none")
		{
			foreach($this->_php_filters_keys as $flt)
			{
				if(preg_match($flt, $str))
				{
					if($this->_filter_stop === "Y")
						$this->_block($_SERVER["REMOTE_ADDR"]);
					if($this->_filter_log === "Y")
						$this->_log($str, $var_name, "PHP");
					if($this->_filter_action === "clear")
						$str = "";
					return 1;
				}
			}
		}
		else
		{
			$str2 = preg_replace($this->_php_filters_keys, $this->_php_filters_values, $str);
			if($str2 <> $str)
			{
				if($this->_filter_stop === "Y")
					$this->_block($_SERVER["REMOTE_ADDR"]);
				if($this->_filter_log === "Y")
					$this->_log($str, $var_name, "PHP");
				$str = $str2;
				return 1;
			}
		}
		return 0;
	}

	/*
	SQL Injection detection
	*/
	function _dostrsql(&$str, $var_name)
	{
		if($this->_filter_action === "clear" || $this->_filter_action === "none")
		{
			foreach($this->_sql_filters_keys as $flt)
			{
				if(preg_match($flt, $str))
				{
					if($this->_filter_stop === "Y")
						$this->_block($_SERVER["REMOTE_ADDR"]);
					if($this->_filter_log === "Y")
						$this->_log($str, $var_name, "SQL");
					if($this->_filter_action === "clear")
						$str = "";
					return 1;
				}
			}
		}
		else
		{
			$str2 = "";
			$strX = $str;
			while($str2 <> $strX)
			{
				$str2 = $strX;
				$strX = preg_replace($this->_sql_filters_keys, $this->_sql_filters_values, $str2);
			}

			if($str2 <> $str)
			{
				if($this->_filter_stop === "Y")
					$this->_block($_SERVER["REMOTE_ADDR"]);
				if($this->_filter_log === "Y")
					$this->_log($str, $var_name, "SQL");
				$str = $str2;
				$this->_dostrphp($str, $var_name);
				return 1;
			}
		}
		return $this->_dostrphp($str, $var_name);
	}

	/*
CModule::IncludeModule('security');
$flt = new CSecurityFilter;
$s = '';
$a = $flt->_teststr($s);
echo "<pre>",htmlspecialchars(print_r($a, true)),"</pre>";
	*/
	function _teststr($str)
	{
		$arResult = array(
			"INPUT" => $str,
		);

		$str1="";
		$strY=$str;
		while($str1 <> $strY)
		{
			$str1 = $strY;
			$strY = $this->_decode($strY);
			$strY = str_replace("\x00", "", $strY);
			$strY = preg_replace("/\&\#0+(;|([^\d;]))/is", "\\2", $strY);
			$strY = preg_replace("/\&\#x0+(;|([^\da-f;]))/is", "\\2", $strY);
		}

		$arResult["DECODE"] = $str1;

		foreach($this->_filters_keys as $i => $flt)
		{
			if(preg_match($flt, $str1, $match))
			{
				$arResult["XSS_FOUND"] = array(
					"INDEX" => $i,
					"FILTER" => $flt,
					"MATCH" => $match,
				);
				return $arResult;
			}
		}

		foreach($this->_sql_filters_keys as $flt)
		{
			if(preg_match($flt, $str1, $match))
			{
				$arResult["SQL_FOUND"] = array(
					"FILTER" => $flt,
					"MATCH" => $match,
				);
				return $arResult;
			}
		}

		foreach($this->_php_filters_keys as $flt)
		{
			if(preg_match($flt, $str1, $match))
			{
				$arResult["PHP_FOUND"] = array(
					"FILTER" => $flt,
					"MATCH" => $match,
				);
				return $arResult;
			}
		}

		$arResult["NOT_FOUND"] = true;
		return $arResult;
	}

	/*
	XSS injection detection.
	Also calls SQL injection detection function.
	*/
	function _dostr(&$str, $var_name)
	{
		if(preg_match("/^[A-Za-z0-9_.,-]*$/", $str))
			return 0;

		$str1="";
		$strY=$str;
		while($str1 <> $strY)
		{
			$str1 = $strY;
			$strY = $this->_decode($strY);
			$strY = str_replace("\x00", "", $strY);
			$strY = preg_replace("/\&\#0+(;|([^\d;]))/is", "\\2", $strY);
			$strY = preg_replace("/\&\#x0+(;|([^\da-f;]))/is", "\\2", $strY);
		}

		if($this->_filter_action === "clear" || $this->_filter_action === "none")
		{
			foreach($this->_filters_keys as $flt)
			{
				if(preg_match($flt, $str1))
				{
					if($this->_filter_stop === "Y")
						$this->_block($_SERVER["REMOTE_ADDR"]);
					if($this->_filter_log === "Y")
						$this->_log($str, $var_name, "XSS");
					if($this->_filter_action === "clear")
						$str = "";
					return 1;
				}
			}
		}
		else
		{
			$str2 = "";
			$strX = $str1;
			while($str2 <> $strX)
			{
				$str2 = $strX;
				$strX = preg_replace($this->_filters_keys, $this->_filters_values, $str2);
			}

			if($str2 <> $str1)
			{
				if($this->_filter_stop === "Y")
					$this->_block($_SERVER["REMOTE_ADDR"]);
				if($this->_filter_log === "Y")
					$this->_log($str, $var_name, "XSS");
				$str = $str2;
				$this->_dostrsql($str, $var_name);
				return 1;
			}
		}


		return $this->_dostrsql($str, $var_name);
	}

	function TestXSS($str, $action = 'clear') /*'replace'*/
	{
		$str1="";
		$strY=$str;
		while($str1 <> $strY)
		{
			$str1 = $strY;
			$strY = $this->_decode($strY);
			$strY = str_replace("\x00", "", $strY);
			$strY = preg_replace("/\&\#0+(;|([^\d;]))/is", "\\2", $strY);
			$strY = preg_replace("/\&\#x0+(;|([^\da-f;]))/is", "\\2", $strY);
		}

		if($action === "replace")
		{
			$str2 = "";
			$strX = $str1;
			while($str2 <> $strX)
			{
				$str2 = $strX;
				$strX = preg_replace($this->_filters_keys, $this->_filters_values, $str2);
			}

			if($str2 <> $str1)
				return $str2;
		}
		else
		{
			foreach($this->_filters_keys as $flt)
			{
				if(preg_match($flt, $str1))
					return "";
			}
		}

		return $str;
	}

	/*
	Calls detection function on array keys and values
	*/
	function _doarray(&$ar, $var_name)
	{
		$ret=0;
		if(!is_array($ar)) return;
		foreach($ar as $k=>$v)
		{
			if(is_array($v))
			{
				$k1=$k;
				$ret+=$this->_dostr($k1, $var_name.'["'.$k1.'"]');

				if($k<>$k1)
				{
					unset($ar[$k]);
					$ar[$k1]=$v;
				}
				$ret+=$this->_doarray($ar[$k1], $var_name.'["'.$k1.'"]');
			}
			else
			{
				$k1=$k;
				$ret+=$this->_dostr($k1, $var_name.'["'.$k1.'"]');

				if($k<>$k1)
				{
					unset($ar[$k]);
					$ar[$k1]=$v;
				}
				$ret+=$this->_dostr($ar[$k1], $var_name.'["'.$k1.'"]');
			}
		}
		return $ret;
	}

	function _fixtmpnames(&$ar)
	{
		if(is_array($ar))
		{
			foreach($ar as $k=>$v)
			{
				$this->_fixtmpnames($ar[$k]);
			}
		}
		else
		{
			$ar=preg_replace("/[^a-z0-9]/i", "", $ar);
			$dir=$this->_tmp_dir;
			$ar=$dir."/MYFILTR_".$ar;
		}
	}

	function _fixsize(&$tmpname, &$size, &$error)
	{
		if(is_array($tmpname))
		{
			foreach($tmpname as $k=>$v)
			{
				$this->_fixsize($tmpname[$k], $size[$k], $error[$k]);
			}
		}
		else
		{
			if(file_exists($tmpname))
			{
				$size=filesize($tmpname);
				$error=0;
			}
		}
	}

	function _initfiles()
	{
		if(is_array($_POST['__SECFILTER_FILES']) && sizeof($_POST['__SECFILTER_FILES'] >0))
		{
			foreach($_POST['__SECFILTER_FILES'] as $k=>$v)
			{
				$nk=$k;
				$this->_fixtmpnames($_POST['__SECFILTER_FILES'][$k]['tmp_name']);
				$this->_fixsize($_POST['__SECFILTER_FILES'][$k]['tmp_name'], $_POST['__SECFILTER_FILES'][$k]['size'], $_POST['__SECFILTER_FILES'][$k]['error']);
				$_FILES=$_POST['__SECFILTER_FILES'];
			}
		}

		unset($_POST['__SECFILTER_FILES']);
	}

	function _returnfilesar($fname, $index, $tmpname, $name, $type)
	{
		$dir=$this->_tmp_dir;

		if(!is_array($tmpname))
		{
			$newtmpname=md5(uniqid(rand(), true));

			$ret="";

			if(move_uploaded_file($tmpname, $dir."/MYFILTR_".$newtmpname))
			{
				$ret.="
					<input type=hidden name=\"__SECFILTER_FILES[".htmlspecialchars($fname)."][tmp_name]".htmlspecialchars($index)."\" value=\"".htmlspecialchars($newtmpname)."\">
					<input type=hidden name=\"__SECFILTER_FILES[".htmlspecialchars($fname)."][name]".htmlspecialchars($index)."\" value=\"".htmlspecialchars($name)."\">
					<input type=hidden name=\"__SECFILTER_FILES[".htmlspecialchars($fname)."][type]".htmlspecialchars($index)."\" value=\"".htmlspecialchars($type)."\">
				";
			}

		}
		else
		{
			foreach($tmpname as $k=>$v)
			{
				$ret.=$this->_returnfilesar($fname, $index."[$k]", $tmpname[$k], $name[$k], $type[$k]);
			}
		}
		return $ret;
	}

	function _returnfiles()
	{
		global $_UNSECURE;

		$ret="";

		if(is_array($_UNSECURE['_FILES']) && sizeof($_UNSECURE[_FILES])>0)
		{

			foreach($_UNSECURE['_FILES'] as $k=>$v)
			{
				$ret.=$this->_returnfilesar($k, "", $v['tmp_name'], $v['name'], $v['type']);
			}
		}
		return $ret;
	}

	/*
	Show hidden in order to "repost"
	*/
	function _returnhiddens($ar, $prefix)
	{
		$ret="";
		foreach($ar as $k=>$v)
		{
			if(is_array($v))
			{
				if(empty($prefix))
				{
					$ret.=$this->_returnhiddens($v, htmlspecialchars($k));
				}
				else
				{
					$ret.=$this->_returnhiddens($v, $prefix."[".htmlspecialchars($k)."]");
				}
			}
			else
			{
				if(empty($prefix))
				{
					$ret.="<input type=hidden name=\"".htmlspecialchars($k)."\" value=\"".htmlspecialchars($v)."\">\r\n";
				}
				else
				{
					$ret.="<input type=hidden name=\"{$prefix}[".htmlspecialchars($k)."]\" value=\"".htmlspecialchars($v)."\">\r\n";
				}
			}
		}

		return $ret;
	}

	/*
	Returns 1 for users who can submit dangerous code
	*/
	function _usercanexcept()
	{
		global $USER;
		return $USER->CanDoOperation('security_filter_bypass');
	}

	function _cleartmpfiles()
	{
		if (is_dir($this->_tmp_dir))
		{
			if ($dh = opendir($this->_tmp_dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if(preg_match("/^MYFILTR_/", $file) && filemtime($this->_tmp_dir."/".$file)<=time()-86400  )
					{
						@unlink($this->_tmp_dir."/".$file);
					}
				}
				closedir($dh);
			}
		}
	}

	/*
	Main filtering loop
	also sets up global vars
	GET POST COOKIE and some $_SERVER keys
	*/
	function _do()
	{
		global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_REQUEST_VARS, $_UNSECURE;

		$this->_cleartmpfiles();

		if(CSecurityFilterMask::Check(SITE_ID, $_SERVER["REQUEST_URI"]))
			return 1;

		$noprocess = array(
			"_GET" => 1,
			"_POST" => 1,
			"_SERVER" => 1,
			"_ENV" => 1,
			"_COOKIE" => 1,
			"_SERVER" => 1,
			"_POST" => 1,
			"_FILES" => 1,
			"_REQUEST" => 1,
			"_SESSION" => 1,
			"GLOBALS" => 1,
			"HTTP_GET_VARS" => 1,
			"HTTP_POST_VARS" => 1,
			"HTTP_SERVER_VARS" => 1,
			"HTTP_ENV_VARS" => 1,
			"HTTP_COOKIE_VARS" => 1,
			"HTTP_SERVER_VARS" => 1,
			"HTTP_POST_VARS" => 1,
			"HTTP_FILES_VARS" => 1,
			"HTTP_REQUEST_VARS" => 1,
			"HTTP_SESSION_VARS" => 1,
			"GLOBALS" => 1,
			"php_errormsg" => 1,
			"HTTP_RAW_POST_DATA" => 1,
			"http_response_header" => 1,
			"argc" => 1,
			"argv" => 1,
			"DOCUMENT_ROOT" => 1,
			"_UNSECURE" => 1,
			"__SECFILTER_FILES" => 1,
		);

		if((!empty($_POST['____SECFILTER_ACCEPT_JS'])) || (!empty($_POST['____SECFILTER_CONVERT_JS'])))
		{
			$this->_initfiles();
		}

		if($this->_usercanexcept())
		{
			if(
				($_SERVER["REQUEST_METHOD"] === "POST")
				&& check_bitrix_sessid()
				&& empty($_POST['____SECFILTER_CONVERT_JS'])
			)
			{
				return 1;
			}
		}

		$_UNSECURE = array(
			'GLOBALS' => $GLOBALS,
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_SERVER' => $_SERVER,
			'_COOKIE' => $_COOKIE,
			'_FILES' => $_FILES,
		);

		//Do not touch those variables who did not come from REQUEST
		foreach($_REQUEST as $k=>$v)
			if(($v === $GLOBALS[$k]) && !array_key_exists($k, $noprocess))
				unset($GLOBALS[$k]);

		$c=0;

		$c+=$this->_doarray($_GET, '$_GET');
		$c+=$this->_doarray($_POST, '$_POST');
		$c+=$this->_doarray($_COOKIE, '$_COOKIE');
		$c+=$this->_doarray($_FILES, '$_FILES');

		foreach($_SERVER as $k=>$v)
		{
			if(strpos($k, "HTTP_")===0)
			{
				$k1=$k;
				$c+=$this->_dostr($k1, '$_SERVER["'.$k1.'"]');

				if($k<>$k1)
				{
					unset($_SERVER[$k]);
					$_SERVER[$k1]=$v;
				}
				$c+=$this->_dostr($_SERVER[$k1], '$_SERVER["'.$k1.'"]');
			}
		}
		$c+=$this->_dostr($_SERVER["QUERY_STRING"], '$_SERVER["QUERY_STRING"]');
		$c+=$this->_dostr($_SERVER["REQUEST_URI"], '$_SERVER["REQUEST_URI"]');
		$c+=$this->_dostr($_SERVER["SCRIPT_URL"], '$_SERVER["SCRIPT_URL"]');
		$c+=$this->_dostr($_SERVER["SCRIPT_URI"], '$_SERVER["SCRIPT_URI"]');

		$_REQUEST=$_GET+$_POST+$_COOKIE;
		$HTTP_GET_VARS=$_GET;
		$HTTP_POST_VARS=$_POST;
		$HTTP_COOKIE_VARS=$_COOKIE;
		$HTTP_REQUEST_VARS=$_REQUEST;
		foreach($_REQUEST as $k=>$v)
			if(!array_key_exists($k, $noprocess) && empty($GLOBALS[$k]))
				$GLOBALS[$k]=$v;

		if(
			$c > 0
			&& $this->_usercanexcept()
		)
		{

			if($this->_filter_action === "none")
				return 1;

			if(empty($_POST['____SECFILTER_CONVERT_JS']))
			{

				//This shows alert text when:
				if(
					//intranet tasks folder created
					($_GET["bx_task_action_request"] == "Y" && $_GET["action"] == "folder_edit")
					//or create ticket with wizard
					|| ($_POST['AJAX_CALL'] == "Y" && $_GET['show_wizard'] == "Y")
					//or by bitrix:search.title
					|| ($_POST['ajax_call'] == "y" && !empty($_POST['q']))
					//or by constant defined on the top of the page
					|| defined('BX_SECURITY_SHOW_MESSAGE')
				)
				{
					echo GetMessage("SECURITY_FILTER_FORM_SUB_TITLE")." ".GetMessage("SECURITY_FILTER_FORM_TITLE").".";
					die();
				}


		?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?echo LANG_CHARSET?>" />
<title><?echo GetMessage("SECURITY_FILTER_FORM_TITLE")?></title>
<link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/adminstyles.css" />
<link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/404.css" />
</head>
<body>
<script>if(document.location!=top.location)top.location=document.location;</script>
<style>
	div.description td { font-family:Verdana,Arial,sans-serif; font-size:70%;  border: 1px solid #BDC6E0; padding:3px; background-color: white; }
	div.description table { border-collapse:collapse; }
	div.description td.head { background-color:#E6E9F4; }
</style>

<div class="error-404">
<table class="error-404" border="0" cellpadding="0" cellspacing="0" align="center">
	<tbody><tr class="top">
		<td class="left"><div class="empty"></div></td>
		<td><div class="empty"></div></td>
		<td class="right"><div class="empty"></div></td>
	</tr>
	<tr>
		<td class="left"><div class="empty"></div></td>
		<td class="content">
			<div class="title">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td><div class="icon"></div></td>
						<td><?echo GetMessage("SECURITY_FILTER_FORM_SUB_TITLE")?></td>
					</tr>
				</table>
			</div>
			<div class="description">
				<?echo GetMessage("SECURITY_FILTER_FORM_MESSAGE")?><br /><br />
				<table cellpadding="0" cellspacing="0" witdh="100%">
					<tr>
						<td class="head" align="center"><?echo GetMessage("SECURITY_FILTER_FORM_VARNAME")?></td>
						<td class="head" align="center"><?echo GetMessage("SECURITY_FILTER_FORM_VARDATA")?></td>
					</tr>
					<?foreach($this->_found_vars as $var_name => $str):?>
					<tr valign="top">
						<td><?echo htmlspecialchars($var_name)?></td>
						<td><?echo htmlspecialchars($str)?></td>
					</tr>
					<?endforeach?>
				</table><br />
				<form method="POST" <?if(defined('POST_FORM_ACTION_URI')):?> action="<?echo POST_FORM_ACTION_URI?>" <?endif?>>
					<?echo $this->_returnhiddens($_UNSECURE['_POST'], "");?>
					<?echo $this->_returnfiles();?>
					<?echo bitrix_sessid_post();?>
					<input type="submit" name='____SECFILTER_ACCEPT_JS' value="<?echo GetMessage('SECURITY_FILTER_FORM_ACCEPT')?>" />
					<input type="submit" name='____SECFILTER_CONVERT_JS' value="<?echo GetMessage('SECURITY_FILTER_FORM_CONVERT')?>" />
				</form>
			</div>
		</td>
		<td class="right"><div class="empty"></div></td>
	</tr>
	<tr class="bottom">
		<td class="left"><div class="empty"></div></td>
		<td><div class="empty"></div></td>
		<td class="right"><div class="empty"></div></td>
	</tr>
</tbody></table>
</div>
</body>
</html>
		<?
				die();
			}
		}
		elseif(
			$c > 0
			&& $this->_blocked
			&& CSecurityIPRule::IsActive()
		)
		{
			CSecurityIPRule::OnPageStart(true);
		}
	}

	function OnBeforeProlog()
	{
		$filter = new CSecurityFilter;
		$filter->_do();
	}

	function IsActive()
	{
		$bActive = false;
		$rsEvents = GetModuleEvents("main", "OnBeforeProlog");
		while($arEvent = $rsEvents->Fetch())
		{
			if(
				$arEvent["TO_MODULE_ID"] == "security"
				&& $arEvent["TO_CLASS"] == "CSecurityFilter"
			)
			{
				$bActive = true;
				break;
			}
		}
		return $bActive;
	}

	function SetActive($bActive = false)
	{
		if($bActive)
		{
			if(!CSecurityFilter::IsActive())
			{
				RegisterModuleDependences("main", "OnBeforeProlog", "security", "CSecurityFilter", "OnBeforeProlog", "1");
			}
		}
		else
		{
			if(CSecurityFilter::IsActive())
			{
				UnRegisterModuleDependences("main", "OnBeforeProlog", "security", "CSecurityFilter", "OnBeforeProlog");
			}
		}
	}

	function GetAuditTypes()
	{
		return array(
			"SECURITY_FILTER_SQL" => "[SECURITY_FILTER_SQL] ".GetMessage("SECURITY_FILTER_SQL"),
			"SECURITY_FILTER_XSS" => "[SECURITY_FILTER_XSS] ".GetMessage("SECURITY_FILTER_XSS"),
			"SECURITY_FILTER_PHP" => "[SECURITY_FILTER_PHP] ".GetMessage("SECURITY_FILTER_PHP"),
			"SECURITY_REDIRECT" => "[SECURITY_REDIRECT] ".GetMessage("SECURITY_REDIRECT"),
		);
	}
}

class CSecurityFilterMask
{
	function Update($arMasks)
	{
		global $DB, $CACHE_MANAGER;

		if(is_array($arMasks))
		{
			$res = $DB->Query("DELETE FROM b_sec_filter_mask", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if($res)
			{
				$arLikeSearch = array("?", "*", ".");
				$arLikeReplace = array("_",  "%", "\\.");
				$arPregSearch = array("\\", ".",  "?", "*",   "'");
				$arPregReplace = array("/",  "\.", ".", ".*?", "\'");

				$added = array();
				$i = 10;
				foreach($arMasks as $arMask)
				{
					$site_id = trim($arMask["SITE_ID"]);
					if($site_id == "NOT_REF")
						$site_id = "";

					$mask = trim($arMask["MASK"]);
					if($mask && !array_key_exists($mask, $added))
					{
						$arMask = array(
							"SORT" => $i,
							"FILTER_MASK" => $mask,
							"LIKE_MASK" => str_replace($arLikeSearch, $arLikeReplace, $mask),
							"PREG_MASK" => str_replace($arPregSearch, $arPregReplace, $mask),
						);
						if($site_id)
							$arMask["SITE_ID"] = $site_id;

						$DB->Add("b_sec_filter_mask", $arMask);
						$i += 10;
						$added[$mask] = true;
					}
				}

				if(CACHED_b_sec_filter_mask !== false)
					$CACHE_MANAGER->CleanDir("b_sec_filter_mask");

			}
		}

		return true;
	}

	function GetList()
	{
		global $DB;
		$res = $DB->Query("SELECT SITE_ID,FILTER_MASK from b_sec_filter_mask ORDER BY SORT");
		return $res;
	}

	function Check($site_id, $uri)
	{
		global $DB, $CACHE_MANAGER;
		$bFound = false;

		if(CACHED_b_sec_filter_mask !== false)
		{
			$cache_id = "b_sec_filter_mask";
			if($CACHE_MANAGER->Read(CACHED_b_sec_filter_mask, $cache_id, "b_sec_filter_mask"))
			{
				$arMasks = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$arMasks = array();

				$rs = $DB->Query("SELECT * FROM b_sec_filter_mask ORDER BY SORT");
				while($ar = $rs->Fetch())
				{
					$site_id = $ar["SITE_ID"]? $ar["SITE_ID"]: "-";
					$arMasks[$site_id][$ar["SORT"]] = $ar["PREG_MASK"];
				}

				$CACHE_MANAGER->Set($cache_id, $arMasks);
			}

			if(is_array($arMasks["-"]))
			{
				foreach($arMasks["-"] as $mask)
				{
					if(preg_match("#^".$mask."$#", $uri))
					{
						$bFound = true;
						break;
					}
				}
			}

			if(!$bFound && array_key_exists($site_id, $arMasks))
			{
				foreach($arMasks[$site_id] as $mask)
				{
					if(preg_match("#^".$mask."$#", $uri))
					{
						$bFound = true;
						break;
					}
				}
			}

		}
		else
		{
			$rs = $DB->Query("
				SELECT m.*
				FROM
					b_sec_filter_mask m
				WHERE
					(m.SITE_ID IS NULL AND '".$DB->ForSQL($uri)."' like m.LIKE_MASK)
					OR (m.SITE_ID = '".$DB->ForSQL($site_id)."' AND '".$DB->ForSQL($uri)."' like m.LIKE_MASK)
			");
			if($rs->Fetch())
				$bFound = true;
		}

		return $bFound;
	}
}
?>