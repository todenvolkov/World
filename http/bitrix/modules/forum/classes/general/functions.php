<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__);
function Error($error)
{
	global $MESS, $DOCUMENT_ROOT;
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/lang/".LANGUAGE_ID."/errors.php");
	$msg = $MESS[$error["MSG"]];
	echo "Error: ".$msg;
}

//===========================
class textParser
{
	var $smiles = array();
	var $preg_smiles = array();
	var $allow_img_ext = "gif|jpg|jpeg|png";
	var $image_params = array(
		"width" => 300, 
		"height" => 300,
		"template" => "popup_image");
	var $LAST_ERROR  = "";
	var $path_to_smile  = false;
	var $quote_error = 0;
	var $quote_open = 0;
	var $quote_closed = 0;
	var $MaxStringLen = 125;
	var $code_error = 0;
	var $code_open = 0;
	var $code_closed = 0;
	var $CacheTime = false;
	var $arFontSize = array(
		0 => 40, //"xx-small"
		1 => 60, //"x-small"
		2 => 80, //"small"
		3 => 100, //"medium"
		4 => 120, //"large"
		5 => 140, //"x-large"
		7 => 160); //"xx-large"
	var $word_separator = "\s.,;:!?\#\-\*\|\[\]\(\)\{\}";
	var $preg = array("counter" => 0, "pattern" => array(), "replace" => array());

	function textParser($strLang = False, $pathToSmile = false)
	{
		global $DB;
		static $arSmiles = array();
		
		$strLang = ($strLang === false ? LANGUAGE_ID : $strLang); 
		$pathToSmile = ($pathToSmile === false ? "/bitrix/images/forum/smile/" : $pathToSmile); 
		$id = md5($pathToSmile."|".$pathToSmile); 

		if (!is_set($arSmiles, $id))
		{
			$arCollection = $arPattern = $arReplacement = array();
			$db_res = CForumSmile::GetByType("S", $strLang);
			foreach ($db_res as $key => $val)
			{
				$tok = strtok($val["TYPING"], " ");
				while ($tok)
				{
					$row = array(
						"TYPING" => $tok,
						"IMAGE"  => stripslashes($val["IMAGE"]),
						"DESCRIPTION" => stripslashes($val["NAME"]));

					$tok = str_replace(array(chr(34), chr(39), "<", ">"), array("\013", "\014", "&lt;", "&gt;"), $tok); 
					$code = preg_quote(str_replace(array("\x5C"), array("&#092;"), $tok)); 
					$patt = preg_quote($tok, "/"); 

					$image = preg_quote($row["IMAGE"]);
					$description = preg_quote(htmlspecialchars($row["DESCRIPTION"], ENT_QUOTES), "/");
					
					$arReplacement[] = "\$this->convert_emoticon('$code', '$image', '$description')";
					$arPattern[] = "/(?<=[^\w&])$patt(?=.\W|\W.|\W$)/ei".BX_UTF_PCRE_MODIFIER;
					
					$arCollection[] = $row; 
					$tok = strtok(" ");
				}
			}
			$arSmiles[$id] = array(
				"smiles" => $arCollection, 
				"pattern" => $arPattern, 
				"replace" => $arReplacement); 
		}
		$this->smiles = $arSmiles[$id]["smiles"];
		$this->preg_smiles = array(
			"pattern" => $arSmiles[$id]["pattern"], 
			"replace" => $arSmiles[$id]["replace"]); 
		$this->path_to_smile = $pathToSmile;
	}

	function convert($text, $allow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y"), $type = "html")	//, "KEEP_AMP" => "N"
	{
		global $DB;

		$text = preg_replace("#([?&;])PHPSESSID=([0-9a-zA-Z]{32})#is", "\\1PHPSESSID1=", $text);
		$type = ($type == "rss" ? "rss" : "html");

		$this->quote_error = 0;
		$this->quote_open = 0;
		$this->quote_closed = 0;
		$this->code_error = 0;
		$this->code_open = 0;
		$this->code_closed = 0;
		$this->preg = array("counter" => 0, "pattern" => array(), "replace" => array());
		$allow = array(
			"HTML" => ($allow["HTML"] == "Y" ? "Y" : "N"), 
			"NL2BR" => ($allow["NL2BR"] == "Y" ? "Y" : "N"), 
			"CODE" => ($allow["CODE"] == "N" ? "N" : "Y"), 
			"VIDEO" => ($allow["VIDEO"] == "N" ? "N" : "Y"), 
			"ANCHOR" => ($allow["ANCHOR"] == "N" ? "N" : "Y"), 
			"BIU" => ($allow["BIU"] == "N" ? "N" : "Y"), 
			"IMG" => ($allow["IMG"] == "N" ? "N" : "Y"), 
			"QUOTE" => ($allow["QUOTE"] == "N" ? "N" : "Y"), 
			"FONT" => ($allow["FONT"] == "N" ? "N" : "Y"), 
			"LIST" => ($allow["LIST"] == "N" ? "N" : "Y"), 
			"SMILES" => ($allow["SMILES"] == "N" ? "N" : "Y"));
		
		$text = str_replace(
			array("\001", "\002", "\003", "\004", "\005", "\013", "\014", chr(34), chr(39)), 
			array("", "", "", "", "", "", "", "\013", "\014"), $text);

		if ($allow["HTML"] != "Y")
		{
			if ($allow["CODE"]=="Y")
			{
				$text = preg_replace(
					array(
					"#<code(\s+[^>]*>|>)(.+?)</code(\s+[^>]*>|>)#is".BX_UTF_PCRE_MODIFIER,
					"/\[code([^\]])*\]/is".BX_UTF_PCRE_MODIFIER,
					"/\[\/code([^\]])*\]/is".BX_UTF_PCRE_MODIFIER,
					"/(?<=[\001])(([^\002]+))(?=([\002]))/ise".BX_UTF_PCRE_MODIFIER,
					"/\001/",
					"/\002/"), 
					array(
					"[code]\\2[/code]", 
					"\001",
					"\002",
					"\$this->pre_convert_code_tag('\\2')",
					"[code]",
					"[/code]"), $text);
			}
			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace(
					array(
						"#<a[^>]+href\s*=\s*[\013]+(([^\013])+)[\013]+[^>]*>(.+?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER,
						"#<a[^>]+href\s*=\s*[\014]+(([^\014])+)[\014]+[^>]*>(.+?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER,
						"#<a[^>]+href\s*=\s*(([^\014\013\>])+)>(.+?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER), 
					"[url=\\1]\\3[/url]", $text);
			}
			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace(
					"#<img[^>]+src\s*=[\s\013\014]*(((http|https|ftp)://[.-_:a-z0-9@]+)*(\/[-_/=:.a-z0-9@{}&?\s]+)+)[\s\013\014]*[^>]*>#is".BX_UTF_PCRE_MODIFIER, 
					"[img]\\1[/img]", $text);
			}
			if ($allow["QUOTE"]=="Y")
			{
				//$text = preg_replace("#(<quote(.*?)>(.*)</quote(.*?)>)#is", "[quote]\\3[/quote]", $text);
				$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1quote]", $text);
			}
			if ($allow["FONT"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<font[^>]+size\s*=[\s\013\014]*([0-9]+)[\s\013\014]*[^>]*\>(.+?)\<\/font[^>]*\>/is".BX_UTF_PCRE_MODIFIER,
						"/\<font[^>]+color\s*=[\s\013\014]*(\#[a-f0-9]{6})[^>]*\>(.+?)\<\/font[^>]*>/is".BX_UTF_PCRE_MODIFIER,
						"/\<font[^>]+face\s*=[\s\013\014]*([a-z\s\-]+)[\s\013\014]*[^>]*>(.+?)\<\/font[^>]*>/is".BX_UTF_PCRE_MODIFIER),
					array(
						"[size=\\1]\\2[/size]",
						"[color=\\1]\\2[/color]",
						"[font=\\1]\\2[/font]"),
					$text);
			}
			if ($allow["LIST"]=="Y")
			{
				$text = preg_replace(
					array("/\001/is", "/\002/is", 
						"/\<ul((\s[^>]*)|(\s*))\>/is".BX_UTF_PCRE_MODIFIER, 
						"/\<\/ul([^>]*)\>/is".BX_UTF_PCRE_MODIFIER, ),
					array("", "", 
						"\001", 
						"\002"),
					$text);
				while (preg_match("/\001([^\001\002]*)\002/ise".BX_UTF_PCRE_MODIFIER, $text))
					$text = preg_replace("/\001([^\001\002]*)\002/ise".BX_UTF_PCRE_MODIFIER, "\$this->pre_convert_list('[list]\\1[/list]')", $text); 
				$text = preg_replace(
					array("/\001/is", "/\002/is"),
					array("<ul>", "</ul>"),
					$text);
			}
			if ($allow["BIU"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<b([^\>]*)\>(.+?)\<\/b([^\>]*)>/is".BX_UTF_PCRE_MODIFIER,
						"/\<u([^\>]*)\>(.+?)\<\/u([^\>]*)>/is".BX_UTF_PCRE_MODIFIER,
						"/\<s([^\>]*)\>(.+?)\<\/s([^\>]*)>/is".BX_UTF_PCRE_MODIFIER,
						"/\<i([^\>]*)\>(.+?)\<\/i([^\>]*)>/is".BX_UTF_PCRE_MODIFIER),
					array(
						"[b]\\2[/b]",
						"[u]\\2[/u]",
						"[s]\\2[/s]",
						"[i]\\2[/i]"),
					$text);
			}
			
			if (preg_match("/\<cut/is".BX_UTF_PCRE_MODIFIER, $text, $matches))
			{
				$text = preg_replace(
						"/\<cut([^>]*)\>(.+?)\<\/cut>/is".BX_UTF_PCRE_MODIFIER, 
						"[cut=\\1]\\2[/cut]",
					$text);
			}
			if (strLen($text)>0)
			{
				$text = str_replace(
					array("<", ">", chr(34)),
					array("&lt;", "&gt;", "&quot;"), 
					$text);
			}
		}
		elseif ($allow["NL2BR"]=="Y")
		{
			$text = str_replace("\n", "<br />", $text);
		}

		if ($allow["ANCHOR"]=="Y")
		{
			$word_separator = str_replace("\]", "", $this->word_separator);
			$text = preg_replace("'(?<=^|[".$word_separator."]|\s)((http|https|news|ftp|aim|mailto)://[\.\-\_\:a-z0-9\@]([^\013\s\'\014\[\]\{\}])*)'is", 
				"[url]\\1[/url]", $text);
		}

		foreach ($allow as $tag => $val)
		{
			if ($val != "Y"):
				continue;
			endif;
			
			if (strpos($text, "<nomodify>") !== false):
				$text = preg_replace(
					array(
						"/\001/", "/\002/", 
						"/\<nomodify\>/is".BX_UTF_PCRE_MODIFIER, "/\<\/nomodify\>/is".BX_UTF_PCRE_MODIFIER, 
						"/(\001([^\002]+)\002)/ies".BX_UTF_PCRE_MODIFIER,
						"/\001/", "/\002/"
						), 
					array(
						"", "", 
						"\001", "\002", 
						"\$this->defended_tags('\\2', 'replace')", 
						"<nomodify>", "</nomodify>"), 
					$text);
			endif;
			
			switch ($tag)
			{
				case "CODE":
					$text = preg_replace(
								array(	"/\[code([^\]])*\]/is".BX_UTF_PCRE_MODIFIER,
										"/\[\/code([^\]])*\]/is".BX_UTF_PCRE_MODIFIER,
										"/(\001([^\002]+)\002)/ies".BX_UTF_PCRE_MODIFIER,
										"/\001/",
										"/\002/"), 
								array(	"\001",
										"\002",
										"\$this->convert_code_tag('\\2', \$type)",
										"[code]",
										"[/code]"), 
								$text);
					break;
				case "VIDEO":
					$text = preg_replace("/\[video([^\]]*)\](.+?)\[\/video[\s]*\]/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_video('\\1', '\\2')", $text);
					break;
				case "QUOTE":
					$text = preg_replace("#(\[quote([^\]\<\>])*\](.*)\[/quote([^\]\<\>])*\])#ies", "\$this->convert_quote_tag('\\1', \$type)", $text);
					break;
				case "IMG":
					$text = preg_replace("#\[img\](.+?)\[/img\]#ie", "\$this->convert_image_tag('\\1', \$type)", $text);
					break;
				case "ANCHOR":
					$text = preg_replace(
								array(	"/\[url\]([^\]]+?)\[\/url\]/ie".BX_UTF_PCRE_MODIFIER,
										"/\[url\s*=\s*([^\]]+?)\s*\](.*?)\[\/url\]/ies".BX_UTF_PCRE_MODIFIER),
								array(	"\$this->convert_anchor_tag('\\1', '\\1', '' , \$type)",
										"\$this->convert_anchor_tag('\\1', '\\2', '', \$type)"),
								$text);
					break;
				case "BIU":
					$text = preg_replace(	
								array(
									"/\[b\](.+?)\[\/b\]/is".BX_UTF_PCRE_MODIFIER,
									"/\[i\](.+?)\[\/i\]/is".BX_UTF_PCRE_MODIFIER,
									"/\[s\](.+?)\[\/s\]/is".BX_UTF_PCRE_MODIFIER,
									"/\[u\](.+?)\[\/u\]/is".BX_UTF_PCRE_MODIFIER), 
								array(
									"<b>\\1</b>",
									"<i>\\1</i>",
									"<s>\\1</s>",
									"<u>\\1</u>"), 
								$text);
					break;
				case "LIST":
					while (preg_match("/\[list\](.+?)\[\/list\]/is".BX_UTF_PCRE_MODIFIER, $text))
						$text = preg_replace(
								array(
									"/\[list\](.+?)\[\/list\]/is".BX_UTF_PCRE_MODIFIER,
									"/\[\*\]/".BX_UTF_PCRE_MODIFIER), 
								array(
									"<ul>\\1</ul>",
									"<li>"),
								$text);
					break;
				case "FONT":
					while (preg_match("/\[size\s*=\s*([^\]]+)\](.+?)\[\/size\]/is".BX_UTF_PCRE_MODIFIER, $text))
						$text = preg_replace("/\[size\s*=\s*([^\]]+)\](.+?)\[\/size\]/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_font_attr('size', '\\1', '\\2')", $text);
					while (preg_match("/\[font\s*=\s*([^\]]+)\](.*?)\[\/font\]/is".BX_UTF_PCRE_MODIFIER, $text))
						$text = preg_replace("/\[font\s*=\s*([^\]]+)\](.*?)\[\/font\]/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_font_attr('font', '\\1', '\\2')", $text);
					while (preg_match("/\[color\s*=\s*([^\]]+)\](.+?)\[\/color\]/is".BX_UTF_PCRE_MODIFIER, $text))
						$text = preg_replace("/\[color\s*=\s*([^\]]+)\](.+?)\[\/color\]/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_font_attr('color', '\\1', '\\2')", $text);
					break;
			}
		}

		if (preg_match("/\[cut/is".BX_UTF_PCRE_MODIFIER, $text, $matches))
		{
			$text = preg_replace(
				array("/\[cut(([^\]])*)\]/is".BX_UTF_PCRE_MODIFIER,
					"/\[\/cut\]/is".BX_UTF_PCRE_MODIFIER), 
				array("\001\\1\002",
					"\003"), 
				$text);
			while (preg_match("/(\001([^\002]*)\002([^\001\002\003]+)\003)/ies".BX_UTF_PCRE_MODIFIER, $text, $arMatches)) 
				$text = preg_replace("/(\001([^\002]*)\002([^\001\002\003]+)\003)/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_cut_tag('\\3', '\\2')", $text);
			$text = preg_replace(
				array("/\001([^\002]+)\002/",
					"/\001\002/",
					"/\003/"), 
				array("[cut\\1]",
					"[cut]",
					"[/cut]"), 
				$text);
		}

		$text = str_replace(
			array(
				"\n", 
				"(c)", "(C)",
				"(tm)", "(TM)", "(Tm)", "(tM)", 
				"(r)", "(R)"), 
			array(
				"<br />", 
				"&#169;", "&#169;",
				"&#153;", "&#153;", "&#153;", "&#153;",
				"&#174;", "&#174;"), 
			$text);
		$text = preg_replace("/\n/", "<br />", $text);

		if ($this->MaxStringLen > 0)
		{
			$text = preg_replace(
				array(
					"/(\&\#\d{1,3}\;)/is".BX_UTF_PCRE_MODIFIER,
					"/(?<=^|\>)([^\<]+)(?=\<|$)/ies".BX_UTF_PCRE_MODIFIER, 
					"/(\<\019((\&\#\d{1,3}\;))\>)/is".BX_UTF_PCRE_MODIFIER,
					"/[\\".chr(34)."]/", 
					"/[\\".chr(39)."]/"), 
				array(
					"<\019\\1>", 
					"\$this->part_long_words('\\1')", 
					"\\2", 
					"\013", 
					"\014"), 
				$text);
		}

		if (strpos($text, "<nosmile>") !== false):
			$text = preg_replace(
				array(
					"/\001/", "/\002/", 
					"/\<nosmile\>/is".BX_UTF_PCRE_MODIFIER, "/\<\/nosmile\>/is".BX_UTF_PCRE_MODIFIER, 
					"/(\001([^\002]+)\002)/ies".BX_UTF_PCRE_MODIFIER,
					"/\001/is", "/\002/is"
					), 
				array(
					"", "", 
					"\001", "\002", 
					"\$this->defended_tags('\\2', 'replace')", 
					"<nosmile>", "</nosmile>"), 
				$text);
		endif;

		if ($allow["SMILES"]=="Y" && !empty($this->preg_smiles["pattern"]))
			$text = preg_replace($this->preg_smiles["pattern"], $this->preg_smiles["replace"], ' '.$text.' '); 

		if ($this->preg["counter"] > 0)
			$text = preg_replace($this->preg["pattern"], $this->preg["replace"], $text);

		$text = str_replace(array("\013", "\014"), array(chr(34), chr(39)), $text);
		
		return trim($text);
	}
	
	function defended_tags($text, $tag = 'replace')
	{
		switch ($tag) {
			case "replace":
				$this->preg["pattern"][] = "/\<\017\#".$this->preg["counter"]."\>/is".BX_UTF_PCRE_MODIFIER;
				$this->preg["replace"][] = $text;
				$text = "<\017#".$this->preg["counter"].">";
				$this->preg["counter"]++;
				break;
		}
		return $text;
	}

	function killAllTags($text)
	{
		$text = strip_tags($text);
		$text = preg_replace(
			array(
				"/\<(\/?)(quote|code|font|color|video)([^\>]*)\>/is".BX_UTF_PCRE_MODIFIER,
				"/\[(\/?)(b|u|i|s|list|code|quote|font|color|url|img|video)([^\]]*)\]/is".BX_UTF_PCRE_MODIFIER),
			"", 
			$text);
		return $text;
	}

	function convert4mail($text)
	{
		$text = Trim($text);
		if (strlen($text)<=0) return "";
		$arPattern = array();
		$arReplace = array();

		$arPattern[] = "/\[(code|quote)(.*?)\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n>================== \\1 ===================\n";
		
		$arPattern[] = "/\[\/(code|quote)(.*?)\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n>===========================================\n";
		
		$arPattern[] = "/\<WBR[\s\/]?\>/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";
		
		$arPattern[] = "/^(\r|\n)+?(.*)$/";
		$arReplace[] = "\\2";
		
		$arPattern[] = "/\[b\](.+?)\[\/b\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\1";
		
		$arPattern[] = "/\[i\](.+?)\[\/i\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\1";
		
		$arPattern[] = "/\[u\](.+?)\[\/u\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "_\\1_";
		
		$arPattern[] = "/\[s\](.+?)\[\/s\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "_\\1_";
		
		$arPattern[] = "/\[(\/?)(color|font|size)([^\]]*)\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "";
		
		$arPattern[] = "/\[url\](\S+?)\[\/url\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "(URL: \\1)";
		
		$arPattern[] = "/\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\\2 (URL: \\1)";
		
		$arPattern[] = "/\[img\](.+?)\[\/img\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "(IMAGE: \\1)";
		
		$arPattern[] = "/\[video([^\]]*)\](.+?)\[\/video[\s]*\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "(VIDEO: \\2)";
		
		$arPattern[] = "/\[(\/?)list\]/is".BX_UTF_PCRE_MODIFIER;
		$arReplace[] = "\n";

        $arPattern[] = "/\[\*\]/is".BX_UTF_PCRE_MODIFIER;
        $arReplace[] = "- ";

		$text = preg_replace($arPattern, $arReplace, $text);
		$text = str_replace("&shy;", "", $text);
		if (preg_match("/\[cut(([^\]])*)\]/is".BX_UTF_PCRE_MODIFIER, $text, $matches))
		{
			$text = preg_replace(
				array("/\[cut(([^\]])*)\]/is".BX_UTF_PCRE_MODIFIER,
					"/\[\/cut\]/is".BX_UTF_PCRE_MODIFIER), 
				array("\001\\1\002",
					"\003"), 
				$text);
			while (preg_match("/(\001([^\002]*)\002([^\001\002\003]+)\003)/is".BX_UTF_PCRE_MODIFIER, $text, $arMatches)) 
				$text = preg_replace(
					"/(\001([^\002]*)\002([^\001\002\003]+)\003)/is".BX_UTF_PCRE_MODIFIER, 
					"\n>================== CUT ===================\n\\3\n>==========================================\n", 
					$text);
			$text = preg_replace(
				array("/\001([^\002]+)\002/",
					"/\001\002/",
					"/\003/"), 
				array("[cut\\1]",
					"[cut]",
					"[/cut]"), 
				$text);
		}
/*		$text = str_replace("&quot;", "\"", $text);
		$text = str_replace("&#092;", "\\", $text);
		$text = str_replace("&#036;", "\$", $text);
		$text = str_replace("&#33;", "!", $text);
		$text = str_replace("&#39;", "'", $text);
		$text = str_replace("&lt;", "<", $text);
		$text = str_replace("&gt;", ">", $text);
		$text = str_replace("&nbsp;", " ", $text);
		$text = str_replace("&#124;", '|', $text);
		$text = str_replace("&amp;", "&", $text);*/

		return $text;
	}

	function convert_video($params, $path)
	{
		if (strLen($path) <= 0)
			return "";
		$width = ""; $height = ""; $preview = "";
		preg_match("/width\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $params, $width);
		preg_match("/height\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $params, $height);
		
		preg_match("/preview\=\013([^\013]+)\013/is".BX_UTF_PCRE_MODIFIER, $params, $preview);
		if (empty($preview))
			preg_match("/preview\=\014([^\014]+)\014/is".BX_UTF_PCRE_MODIFIER, $params, $preview);
		$width = intval($width[1]); 
		$width = ($width > 0 ? $width : 400);
		$height = intval($height[1]); 
		$height = ($height > 0 ? $height : 300);
		$preview = trim($preview[1]);
		$preview = (strLen($preview) > 0 ? $preview : "");

		ob_start();
		$GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:player", "",
			Array(
				"PLAYER_TYPE" => "auto", 
				"USE_PLAYLIST" => "N", 
				"PATH" => $path, 
				"WIDTH" => $width, 
				"HEIGHT" => $height, 
				"PREVIEW" => $preview, 
				"LOGO" => "", 
				"FULLSCREEN" => "Y", 
				"SKIN_PATH" => "/bitrix/components/bitrix/player/mediaplayer/skins", 
				"SKIN" => "bitrix.swf", 
				"CONTROLBAR" => "bottom", 
				"WMODE" => "transparent", 
				"HIDE_MENU" => "N", 
				"SHOW_CONTROLS" => "Y", 
				"SHOW_STOP" => "N", 
				"SHOW_DIGITS" => "Y", 
				"CONTROLS_BGCOLOR" => "FFFFFF", 
				"CONTROLS_COLOR" => "000000", 
				"CONTROLS_OVER_COLOR" => "000000", 
				"SCREEN_COLOR" => "000000", 
				"AUTOSTART" => "N", 
				"REPEAT" => "N", 
				"VOLUME" => "90", 
				"DISPLAY_CLICK" => "play", 
				"MUTE" => "N", 
				"HIGH_QUALITY" => "Y", 
				"ADVANCED_MODE_SETTINGS" => "N", 
				"BUFFER_LENGTH" => "10", 
				"DOWNLOAD_LINK" => "", 
				"DOWNLOAD_LINK_TARGET" => "_self"));
		$video = ob_get_contents();
		ob_end_clean();
		return "<nomodify>".str_replace(array(chr(34), chr(39)), array("\013", "\014"), $video)."</nomodify>";
	}
	
	function convert_emoticon($code = "", $image = "", $description = "", $servername = "")
	{
		if (strlen($code)<=0 || strlen($image)<=0) return;
		$code = stripslashes($code); 
		$description = stripslashes($description);
		$image = stripslashes($image);
		if ($this->path_to_smile !== false)
			return '<img src="'.$servername.$this->path_to_smile.$image.'" border="0" alt="smile'.$code.'" title="'.$description.'" />';
		return '<img src="'.$servername.'/bitrix/images/forum/smile/'.$image.'" border="0" alt="smile'.$code.'" title="'.$description.'" />';
	}

	function pre_convert_code_tag ($text = "")
	{
		if (strLen($text)<=0) return;
		$text = str_replace(
			array("&", "://", "<", ">", "[", "]", "\001", "\002"), 
			array("&#38;", "&#58;&#47;&#47;", "&#60;", "&#62;", "&#91;", "&#93;", "&#91;code&#93;", "&#91;/code&#93;"), $text);
		return $text;
	}
	
	function pre_convert_list ($text = "")
	{
		return preg_replace(
			array("/\<li((\s[^>]*)|(\s*))\>/is".BX_UTF_PCRE_MODIFIER, "/\<\/(\s*)li(\s*)\>/is".BX_UTF_PCRE_MODIFIER), 
			array("[*]", ""), 
			$text);
	}
	
	function convert_code_tag($text = "", $type = "html")
	{
		if (strLen($text)<=0) return;
		$type = ($type == "rss" ? "rss" : "html");
		$text = str_replace(
			array("<", ">", "\\r", "\\n", "\\", "[", "]", "\001", "\002", "  ", "\t"), 
			array("&#60;", "&#62;", "&#92;r", "&#92;n", "&#92;", "&#91;", "&#93;", "&#91;code&#93;", "&#91;/code&#93;", "&nbsp;&nbsp;", "&nbsp;&nbsp;&nbsp;"), $text);
		$text = stripslashes($text);
//		$text = str_replace(array("  ", "\t", ), array("&nbsp;&nbsp;", "&nbsp;&nbsp;&nbsp;"), $text);
		if ($this->code_open == $this->code_closed && $this->code_error == 0):
			$text = "<nosmile>".str_replace(array(chr(34), chr(39)), array("\013", "\014"), $this->convert_open_tag('code', $type).$text.$this->convert_close_tag('code', $type))."</nosmile>";
		endif;
		return $text;
	}

	function convert_quote_tag($text = "", $type = "html")
	{
		if (strlen($text)<=0) return;
		$txt = $text;
		$type = ($type == "rss" ? "rss" : "html");

		$txt = preg_replace(
			array(
				"/\[quote([^\]\<\>])*\]/ie".BX_UTF_PCRE_MODIFIER,
				"/\[\/quote([^\]\<\>])*\]/ie".BX_UTF_PCRE_MODIFIER), 
			array(
				"\$this->convert_open_tag('quote', \$type)", 
				"\$this->convert_close_tag('quote', \$type)"), $txt);
				
		if (($this->quote_open==$this->quote_closed) && ($this->quote_error==0))
			return str_replace(array(chr(34), chr(39)), array("\013", "\014"), $txt); 
		return $text;
	}

	function convert_open_tag($marker = "quote", $type = "html")
	{
		$marker = (strToLower($marker) == "code" ? "code" : "quote");
		$type = ($type == "rss" ? "rss" : "html");
		
		$this->{$marker."_open"}++;
		if ($type == "rss")
			return "\n====".$marker."====\n";
		return '<table class="forum-'.$marker.'"><thead><tr><th>'.($marker == "quote" ? GetMessage("FRM_QUOTE") : GetMessage("FRM_CODE")).'</th></tr></thead><tbody><tr><td>';
	}
	
	function convert_close_tag($marker = "quote")
	{
		$marker = (strToLower($marker) == "code" ? "code" : "quote");
		$type = ($type == "rss" ? "rss" : "html");
		
		if ($this->{$marker."_open"} == 0)
		{
			$this->{$marker."_error"}++;
			return;
		}
		$this->{$marker."_closed"}++;
		if ($type == "rss")
			return "\n=============\n";
		return '</td></tr></tbody></table>';
	}

	function convert_image_tag($url = "", $type = "html")
	{
		static $bShowedScript = false;
		if (strlen($url)<=0) return;
		$url = trim($url);
		$type = (strToLower($type) == "rss" ? "rss" : "html");
		$extension = preg_replace("/^.*\.(\S+)$/".BX_UTF_PCRE_MODIFIER, "\\1", $url);
		$extension = strtolower($extension);
		$extension = preg_quote($extension, "/");

		$bErrorIMG = False;
		if (strpos($url, "/bitrix/components/bitrix/forum.interface/show_file.php?fid=") === false)
		{
			if (preg_match("/[?&;]/".BX_UTF_PCRE_MODIFIER, $url)) $bErrorIMG = True;
			if (!$bErrorIMG && !preg_match("/$extension(\||\$)/".BX_UTF_PCRE_MODIFIER, $this->allow_img_ext)) $bErrorIMG = True;
			if (!$bErrorIMG && !preg_match("/^((http|https|ftp)\:\/\/[-_:.a-z0-9@]+)*(\/[-_+\/=:.a-z0-9@%\013\s]+)$/i".BX_UTF_PCRE_MODIFIER, $url)) $bErrorIMG = True;
		}
		if ($bErrorIMG)
			return "[img]".$url."[/img]";
		if ($type != "html")
			return '<img src="'.$url.'" alt="'.GetMessage("FRM_IMAGE_ALT").'" border="0" />';

		$result = $GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:forum.interface",
			$this->image_params["template"],
			Array(
				"URL" => $url,
				
				"WIDTH"=> $this->image_params["width"],
				"HEIGHT"=> $this->image_params["height"],
				"CONVERT" => "N",
				"FAMILY" => "FORUM",
				"SINGLE" => "Y",
				"RETURN" => "Y"
			),
			null,
			array("HIDE_ICONS" => "Y"));
		
		return str_replace(array(chr(34), chr(39)), array("\013", "\014"), $result);
	}

	function convert_font_attr($attr, $value = "", $text = "")
	{
		if (strlen($text)<=0) return "";
		if (strlen($value)<=0) return $text;

		if ($attr == "size")
		{
			$count = count($this->arFontSize);
			if ($count <= 0)
				return $text;
			$value = intVal($value >= $count ? ($count - 1) : $value);
			return '<span style="font-size:'.$this->arFontSize[$value].'%;">'.$text.'</span>';
		}
		else if ($attr == 'color')
		{
			$value = preg_replace("/[^\w#]/", "" , $value);
			return '<font color="'.$value.'">'.$text.'</font>';
		}
		else if ($attr == 'font')
		{
			$value = preg_replace("/[^\w]/", "" , $value);
			return '<font face="'.$value.'">'.$text.'</font>';
		}
	}
	// Only for public using
	function wrap_long_words($text="")
	{
		if ($this->MaxStringLen > 0 && !empty($text))
		{
			$text = str_replace(array(chr(34), chr(39)), array("\013", "\014"), $text);
			$text = preg_replace("/(?<=^|\>)([^\<]+)(?=\<|$)/ies".BX_UTF_PCRE_MODIFIER, "\$this->part_long_words('\\1')", $text);
			$text = str_replace(array("\013", "\014"), array(chr(34), chr(39)), $text);
		}
		return $text;
	}

	function part_long_words($str)
	{
		$word_separator = $this->word_separator; 
		if (($this->MaxStringLen > 0) && (strLen(trim($str)) > 0))
		{
			$str = str_replace(
				array(
					chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), 
					"&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;", 
					chr(34), chr(39)), 
				array(
					"", "", "", "", "", "", 
					chr(1), "<", ">", chr(2), chr(3), chr(4), chr(5), chr(6), 
					"\013", "\014"), 
				$str);
			$str = preg_replace("/(?<=[".$word_separator."]|^)(([^".$word_separator."]+))(?=[".$word_separator."]|$)/ise".BX_UTF_PCRE_MODIFIER, 
				"\$this->cut_long_words('\\2')", $str);

			$str = str_replace(
				array(chr(1), "<", ">", chr(2), chr(3), chr(4), chr(5), chr(6), "\013", "\014", "&lt;WBR/&gt;", "&lt;WBR&gt;", "&amp;shy;"),
				array("&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;", chr(34), chr(39), "<WBR/>", "<WBR/>", "&shy;"),
				$str);
		}
		return $str;
	}
	
	function cut_long_words($str)
	{
		if (($this->MaxStringLen > 0) && (strLen($str) > 0))
			$str = preg_replace("/([^ \n\r\t\x01]{".$this->MaxStringLen."})/is".BX_UTF_PCRE_MODIFIER, "\\1<WBR/>&shy;", $str);
		return $str;
	}

	function convert_cut_tag($text, $title="")
	{
		if (empty($text))
			return ""; 
		$title = trim($title); 
		$title = ltrim($title, "="); 
		$title = trim($title); 
		$result = $GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:forum.interface",
			"spoiler",
			Array(
				"TITLE" => $title,
				"TEXT" => $text,
				"RETURN" => "Y"
			),
			null,
			array("HIDE_ICONS" => "Y"));
		return str_replace(array(chr(34), chr(39)), array("\013", "\014"), $result);
	}
	
	function convert_anchor_tag($url, $text, $pref="")
	{
		$bCutUrl = True;
		$text = str_replace("\\\"", "\"", $text);
		$end = "";
		if (preg_match("/([\.,\?]|&#33;)$/".BX_UTF_PCRE_MODIFIER, $url, $match))
		{
			$end = $match[1];
			$url = preg_replace("/([\.,\?]|&#33;)$/".BX_UTF_PCRE_MODIFIER, "", $url);
			$text = preg_replace("/([\.,\?]|&#33;)$/".BX_UTF_PCRE_MODIFIER, "", $text);
		}
		if (preg_match("/\[\/(quote|code)/i", $url)) 
			return $url;
		$url = preg_replace(
			array(
				"/&amp;/".BX_UTF_PCRE_MODIFIER, 
				"/javascript:/i".BX_UTF_PCRE_MODIFIER), 
			array(
				"&", 
				"java script&#58; ") , 
			$url);
		if (substr($url, 0, 1) != "/" && !preg_match("/^(http|news|https|ftp|aim|mailto)\:/i".BX_UTF_PCRE_MODIFIER, $url))
			$url = 'http://'.$url;
		if (!preg_match("/^((http|https|news|ftp|aim):\/\/[-_:.a-z0-9@]+)*([^\"\013])+$/i".BX_UTF_PCRE_MODIFIER, $url))
			return $pref.$text." (".$url.") ".$end;

		if (preg_match("/^<img\s+src/i".BX_UTF_PCRE_MODIFIER, $text)) 
			$bCutUrl = False;
		
		$text = preg_replace(
			array("/&amp;/i".BX_UTF_PCRE_MODIFIER, "/javascript:/i".BX_UTF_PCRE_MODIFIER), 
			array("&", "javascript&#58; "), $text);
		if ($bCutUrl && strlen($text) < 55) 
			$bCutUrl = False;
		if ($bCutUrl && !preg_match("/^(http|ftp|https|news):\/\//i".BX_UTF_PCRE_MODIFIER, $text)) 
			$bCutUrl = False;

		if ($bCutUrl)
		{
			$stripped = preg_replace("/^(http|ftp|https|news):\/\/(\S+)$/i".BX_UTF_PCRE_MODIFIER, "\\2", $text);
			$uri_type = preg_replace("/^(http|ftp|https|news):\/\/(\S+)$/i".BX_UTF_PCRE_MODIFIER, "\\1", $text);
			$text = $uri_type.'://'.substr($stripped, 0, 30).'...'.substr($stripped, -10);
		}

		$result = $pref.'<a href="'.$url.'" target="_blank"'.
			(COption::GetOptionString("forum", "parser_nofollow", "Y") == "Y" ? ' rel="nofollow"' : '').'>'.$text.'</a>'.$end; 
		return str_replace(array(chr(34), chr(39)), array("\013", "\014"), $result);
	}
	
	
	function convert_to_rss($text, $arImages = Array(), $arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N"), $arParams = array())
	{
		global $DB;
		if (empty($arAllow))
			$arAllow = array(
				"HTML" => "N", 
				"ANCHOR" => "Y", 
				"BIU" => "Y", 
				"IMG" => "Y", 
				"QUOTE" => "Y", 
				"CODE" => "Y", 
				"FONT" => "Y", 
				"LIST" => "Y", 
				"SMILES" => "Y", 
				"NL2BR" => "N");
			
		$this->quote_error = 0;
		$this->quote_open = 0;
		$this->quote_closed = 0;
		$this->code_error = 0;
		$this->code_open = 0;
		$this->code_closed = 0;
		$bAllowSmiles = $arAllow["SMILES"];
		if ($arAllow["HTML"]!="Y")
		{
			$text = preg_replace(
				array(
					"#^(.+?)<cut[\s]*(/>|>).*?$#is".BX_UTF_PCRE_MODIFIER,
					"#^(.+?)\[cut[\s]*(/\]|\]).*?$#is".BX_UTF_PCRE_MODIFIER),
				"\\1", $text);
			$arAllow["SMILES"] = "N";
			$text = $this->convert($text, $arAllow, "rss");
		}
		else
		{
			if ($arAllow["NL2BR"]=="Y")
				$text = str_replace("\n", "<br />", $text);
		}

		if (strLen($arParams["SERVER_NAME"]) <= 0)
		{
			$dbSite = CSite::GetByID(SITE_ID);
			$arSite = $dbSite->Fetch();
			$arParams["SERVER_NAME"] = $arSite["SERVER_NAME"];
			if (strLen($arParams["SERVER_NAME"]) <=0)
			{
				if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
					$arParams["SERVER_NAME"] = SITE_SERVER_NAME;
				else
					$arParams["SERVER_NAME"] = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
			}
		}
		
		if ($bAllowSmiles=="Y" && !empty($this->preg_smiles["pattern"]))
			$text = preg_replace($this->preg_smiles["pattern"], $this->preg_smiles["replace"], ' '.$text.' ');
		return trim($text);
	}
}
?>
