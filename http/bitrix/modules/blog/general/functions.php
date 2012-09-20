<?
IncludeModuleLangFile(__FILE__);

class blogTextParser
{
	var $smiles = array();
	var $arFontSize = array(
		1 => 40, //"xx-small"
		2 => 60, //"x-small"
		3 => 80, //"small"
		4 => 100, //"medium"
		5 => 120, //"large"
		6 => 140, //"x-large"
		7 => 160); //"xx-large"
	var $word_separator = "\s.,;:!?\#\-\*\|\[\]\(\)\{\}";
	
	function blogTextParser($strLang = False, $pathToSmile = false)
	{
		global $DB, $CACHE_MANAGER;
		if ($strLang===False)
			$strLang = LANGUAGE_ID;
		$this->path_to_smile = $pathToSmile;
		
		$this->imageWidth = COption::GetOptionString("blog", "image_max_width", 600);		
		$this->imageHeight = COption::GetOptionString("blog", "image_max_height", 600);

		$this->smiles = array();

		if($CACHE_MANAGER->Read(10, "b_blog_smile"))
		{
			$arSmiles = $CACHE_MANAGER->Get("b_blog_smile");
		}
		else
		{
			$db_res = CBlogSmile::GetList(array("SORT" => "ASC"), array("SMILE_TYPE" => "S"/*, "LANG_LID" => $strLang*/), false, false, Array("LANG_LID", "ID", "IMAGE", "DESCRIPTION", "TYPING", "SMILE_TYPE", "SORT"));
			while ($res = $db_res->Fetch())
			{
				$tok = strtok($res["TYPING"], " ");
				while ($tok)
				{ 
					$arSmiles[$res["LANG_LID"]][] = array(
										"TYPING" => $tok,
										"IMAGE"  => stripslashes($res["IMAGE"]),
										"DESCRIPTION" => stripslashes($res["NAME"]));
					
					$tok = strtok(" ");
				} 
			}
			
			function sortlen($a, $b) {
			    if (strlen($a["TYPING"]) == strlen($b["TYPING"])) {
			        return 0;
			    }
			    return (strlen($a["TYPING"]) > strlen($b["TYPING"])) ? -1 : 1;
			}

			foreach ($arSmiles as $LID => $arSmilesLID)
			{
				uasort($arSmilesLID, 'sortlen');
				$arSmiles[$LID] = $arSmilesLID;
			}

			$CACHE_MANAGER->Set("b_blog_smile", $arSmiles);

		}
		$this->smiles = $arSmiles[$strLang];
	}

	function convert($text, $bPreview = True, $arImages = array(), $allow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "TABLE" => "Y", "CUT_ANCHOR" => "N"), $arParams = Array())
	{
		global $DB;

		$text = preg_replace("#([?&;])PHPSESSID=([0-9a-zA-Z]{32})#is", "\\1PHPSESSID1=", $text);
		if(!is_array($arParams) && strlen($arParams) > 0)
			$type = $arParams;
		elseif(is_array($arParams))
			$type = $arParams["type"];
			
		if(IntVal($arParams["imageWidth"]) > 0)
			$this->imageWidth = IntVal($arParams["imageWidth"]);
		if(IntVal($arParams["imageHeight"]) > 0)
			$this->imageHeight = IntVal($arParams["imageHeight"]);
		
		$type = ($type == "rss" ? "rss" : "html");
		$serverName = "";
		if($type == "rss")
		{
			$dbSite = CSite::GetByID(SITE_ID);
			$arSite = $dbSite->Fetch();
			$serverName = $arSite["SERVER_NAME"];
			if (strLen($serverName) <=0)
			{
				if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
					$serverName = SITE_SERVER_NAME;
				else
					$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
			}
			$serverName = "http://".$serverName;
		}

		$this->quote_error = 0;
		$this->quote_open = 0;
		$this->quote_closed = 0;
		$this->code_error = 0;
		$this->code_open = 0;
		$this->code_closed = 0;
		$this->MaxStringLen = 60;
		$this->preg = array("counter" => 0, "pattern" => array(), "replace" => array());
		$this->allow_img_ext = "gif|jpg|jpeg|png";
		
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
			"SMILES" => ($allow["SMILES"] == "N" ? "N" : "Y"),
			"TABLE" => ($allow["TABLE"] == "N" ? "N" : "Y"),
			"ALIGN" => ($allow["ALIGN"] == "N" ? "N" : "Y"),
			"CUT_ANCHOR" => ($allow["CUT_ANCHOR"] == "Y" ? "Y" : "N"), 
			);
			
		$this->arImages = $arImages;

		$text = str_replace(array("\001", "\002", chr(11), chr(12), chr(34), chr(39)), array("", "", "", "", chr(11), chr(12)), $text);
		
		if ($bPreview)
		{
			$text = preg_replace("#^(.*?)<cut[\s]*(/>|>).*?$#is", "\\1", $text);
			$text = preg_replace("#^(.*?)\[cut[\s]*(/\]|\]).*?$#is", "\\1", $text);
		}
		else
		{
			$text = preg_replace("#<cut[\s]*(/>|>)#is", "[cut]", $text);
		}
		
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

		if ($allow["HTML"] != "Y")
		{
			if ($allow["ANCHOR"]=="Y")
			{
				$text = preg_replace(
					array(
						"#<a[^>]+href\s*=\s*[\011]+(([^\011])+)[\011]+[^>]*>(.*?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER,
						"#<a[^>]+href\s*=\s*[\012]+(([^\012])+)[\012]+[^>]*>(.*?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER,
						"#<a[^>]+href\s*=\s*(([^\012\011\>])+)>(.*?)</a[^>]*>#is".BX_UTF_PCRE_MODIFIER), 
					"[url=\\1]\\3[/url]", $text);
			}
			if ($allow["BIU"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<b([^>]*)\>(.+?)\<\/b([^>]*)>/is".BX_UTF_PCRE_MODIFIER,
						"/\<u([^>]*)\>(.+?)\<\/u([^>]*)>/is".BX_UTF_PCRE_MODIFIER,
						"/\<s([^>a-z]*)\>(.+?)\<\/s([^>a-z]*)>/is".BX_UTF_PCRE_MODIFIER,
						"/\<i([^>]*)\>(.+?)\<\/i([^>]*)>/is".BX_UTF_PCRE_MODIFIER),
					array(
						"[b]\\2[/b]",
						"[u]\\2[/u]",
						"[s]\\2[/s]",
						"[i]\\2[/i]"),
					$text);
			}
			if ($allow["IMG"]=="Y")
			{
				$text = preg_replace(
					"#<img[^>]+src\s*=[\s\011\012]*(((http|https|ftp)://[.-_:a-z0-9@]+)*(\/[-_/=:.a-z0-9@{}&?]+)+)[\s\011\012]*[^>]*>#is".BX_UTF_PCRE_MODIFIER, 
					"[img]\\1[/img]", $text);
			}
			if ($allow["FONT"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<font[^>]+size\s*=[\s\011\012]*([0-9]+)[\s\011\012]*[^>]*\>(.+?)\<\/font[^>]*\>/is".BX_UTF_PCRE_MODIFIER,
						"/\<font[^>]+color\s*=[\s\011\012]*(\#[a-f0-9]{6})[^>]*\>(.+?)\<\/font[^>]*>/is".BX_UTF_PCRE_MODIFIER,
						"/\<font[^>]+face\s*=[\s\011\012]*([a-z\s\-]+)[\s\011\012]*[^>]*>(.+?)\<\/font[^>]*>/is".BX_UTF_PCRE_MODIFIER),
					array(
						"[size=\\1]\\2[/size]",
						"[color=\\1]\\2[/color]",
						"[font=\\1]\\2[/font]"),
					$text);
			}
			if ($allow["LIST"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<ul((\s[^>]*)|(\s*))\>(.+?)<\/ul([^>]*)\>/is".BX_UTF_PCRE_MODIFIER,
						"/\<ol((\s[^>]*)|(\s*))\>(.+?)<\/ol([^>]*)\>/is".BX_UTF_PCRE_MODIFIER,
						"/\<li((\s[^>]*)|(\s*))\>/is".BX_UTF_PCRE_MODIFIER,
						),
					array(
						"[list]\\4[/list]",
						"[list=1]\\4[/list]",
						"[*]",
						),
					$text);
			}
			
			if ($allow["TABLE"]=="Y")
			{
				$text = preg_replace(
					array(
						"/\<table((\s[^>]*)|(\s*))\>(.+?)<\/table([^>]*)\>/is".BX_UTF_PCRE_MODIFIER,
						"/\<tr((\s[^>]*)|(\s*))\>(.*?)<\/tr([^>]*)\>/is".BX_UTF_PCRE_MODIFIER,
						"/\<td((\s[^>]*)|(\s*))\>(.*?)<\/td([^>]*)\>/is".BX_UTF_PCRE_MODIFIER,
						),
					array(
						"[table]\\4[/table]",
						"[tr]\\4[/tr]",
						"[td]\\4[/td]",
						),
					$text);
			}

			if ($allow["QUOTE"]=="Y")
			{
				$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1quote]", $text);
			}

			if (strLen($text)>0)
			{
				$text = str_replace(
					array("<", ">", chr(34)),
					array("&lt;", "&gt;", "&quot;"), 
					$text);
			}
		}

		if ($allow["ANCHOR"]=="Y")
		{
			$word_separator = str_replace("\]", "", $this->word_separator);
			$text = preg_replace("'(?<=^|[".$word_separator."]|\s)((http|https|news|ftp|aim|mailto)://[\.\-\_\:a-z0-9\@]([^\011\s\'\012\[\]\{\}])*)'is", 
				"[url]\\1[/url]", $text);
		}
		
		foreach ($allow as $tag => $val)
		{
			if ($val != "Y")
				continue;

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
					$bHTML = false;
					if($allow["HTML"] == "Y")
						$bHTML = true;
					$text = preg_replace(
								array(	"/\[code([^\]])*\]/is".BX_UTF_PCRE_MODIFIER,
										"/\[\/code([^\]])*\]/is".BX_UTF_PCRE_MODIFIER,
										"/(\001([^\002]+)\002)/ies".BX_UTF_PCRE_MODIFIER,
										"/\001/",
										"/\002/"
										), 
								array(	"\001",
										"\002",
										"\$this->convert_code_tag('\\2', \$type, \$bHTML)",
										"[code]",
										"[/code]"), 
								$text);
					break;
				case "VIDEO":
					$text = preg_replace("/\[video([^\]]*)\](.+?)\[\/video[\s]*\]/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_video('\\1', '\\2')", $text);
					break;
				case "IMG":
						$text = preg_replace("/\[img([^\]]*)id\s*=\s*([0-9]+)([^\]]*)\]/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_blog_image('\\1', '\\2', '\\3', \$type, \$serverName)", $text);
				
						$text = preg_replace("/\[img([^\]]*)\](.+?)\[\/img\]/ies".BX_UTF_PCRE_MODIFIER, "\$this->convert_image_tag('\\2', \$type, \$serverName, '\\1')", $text);
					break;
				case "ANCHOR":
					if($allow["CUT_ANCHOR"] != "Y")
					{
						$text = preg_replace(
									array(	"/\[url\]([^\]]*?)\[\/url\]/ies".BX_UTF_PCRE_MODIFIER,
											"/\[url\s*=\s*([^\]]+?)\s*\](.*?)\[\/url\]/ies".BX_UTF_PCRE_MODIFIER),
									array(	"\$this->convert_anchor_tag('\\1', '\\1', '')",
											"\$this->convert_anchor_tag('\\1', '\\2', '')"
											),
									$text);
						break;
					}
					else
					{
						$text = preg_replace(
									array(	"/\[url\]([^\]]*?)\[\/url\]/ies".BX_UTF_PCRE_MODIFIER,
											"/\[url\s*=\s*([^\]]+?)\s*\](.*?)\[\/url\]/ies".BX_UTF_PCRE_MODIFIER),
									"",
									$text);
						break;
					}
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
					while (preg_match("/\[list\s*=\s*([^\]]+?)\s*\](.+?)\[\/list\]/is".BX_UTF_PCRE_MODIFIER, $text))
					$text = preg_replace(
								array(
									"/\[list\s*=\s*1\](\s*\\n*)(.+?)\[\/list\]/is".BX_UTF_PCRE_MODIFIER,
									"/\[list\s*=\s*a\](\s*\\n*)(.+?)\[\/list\]/is".BX_UTF_PCRE_MODIFIER,
									"/\[\*\]/".BX_UTF_PCRE_MODIFIER,
									), 
								array(
									"<ol>\\2</ol>",
									"<ol type=\"a\">\\2</ol>",
									"<li>",
									),
								$text);
					while (preg_match("/\[list\](.+?)\[\/list\]/is".BX_UTF_PCRE_MODIFIER, $text))
					$text = preg_replace(
								array(
									"/\[list\](\s*\\n*)(.+?)\[\/list\]/is".BX_UTF_PCRE_MODIFIER,
									"/\[\*\]/".BX_UTF_PCRE_MODIFIER,
									), 
								array(
									"<ul>\\2</ul>",
									"<li>",
									),
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
				case "QUOTE":
					$text = preg_replace("#(\[quote([^\]\<\>])*\](.*)\[/quote([^\]\<\>])*\])#ies", "\$this->convert_quote_tag('\\1', \$type)", $text);
					break;
				case "TABLE":
					while (preg_match("/\[table\](.+?)\[\/table\]/is".BX_UTF_PCRE_MODIFIER, $text))
					{
						$text = preg_replace(
								array(
									"/\[table\](\s*\\n*)(.*?)\[\/table\]/is".BX_UTF_PCRE_MODIFIER,
									"/\[tr\](.*?)\[\/tr\](\s*\\n*)/is".BX_UTF_PCRE_MODIFIER,
									"/\[td\](.*?)\[\/td\]/is".BX_UTF_PCRE_MODIFIER,
									), 
								array(
									"<table class=\"data-table\">\\2</table>",
									"<tr>\\1</tr>",
									"<td>\\1</td>",
									),
								$text);
					}
					break;
				case "ALIGN":
					$text = preg_replace(
								array(	
										"/\[left\]([^\]]+?)\[\/left\]/is".BX_UTF_PCRE_MODIFIER,
										"/\[right\]([^\]]+?)\[\/right\]/is".BX_UTF_PCRE_MODIFIER,
										"/\[center\]([^\]]+?)\[\/center\]/is".BX_UTF_PCRE_MODIFIER,
										),
								array(	
										"<div align=\"left\">\\1</div>",
										"<div align=\"right\">\\1</div>",
										"<div align=\"center\">\\1</div>",
										),
								$text);
					break;
			}
			$text = str_replace(array(chr(34), chr(39)), array(chr(11), chr(12)), $text);
		}
		
		if ($allow["HTML"] != "Y")
			$text = str_replace("\n", "<br />", $text);

		$text = str_replace(
			array(
				"(c)", "(C)",
				"(tm)", "(TM)", "(Tm)", "(tM)", 
				"(r)", "(R)"), 
			array(
				"&#169;", "&#169;",
				"&#153;", "&#153;", "&#153;", "&#153;",
				"&#174;", "&#174;"), 
			$text);

		if ($this->MaxStringLen > 0)
		{
			$text = preg_replace(
				array(
					"/(\&\#\d{1,3}\;)/is".BX_UTF_PCRE_MODIFIER,
					"/(?<=^|\>)([^\<]+)(?=\<|$)/ies".BX_UTF_PCRE_MODIFIER, 
					"/(\<\019((\&\#\d{1,3}\;))\>)/is".BX_UTF_PCRE_MODIFIER,), 
				array(
					"<\019\\1>", 
					"\$this->part_long_words('\\1')", 
					"\\2"), 
				$text);
		}

		if (strpos($text, "<nosmile>") !== false)
		{
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
		}
		if (!$bPreview)
		{
			$text = preg_replace("#\[cut[\s]*(/\]|\])#is", "<a name=\"cut\"></a>", $text);
		}

		if ($allow["SMILES"]=="Y")
		{
			if (count($this->smiles) > 0)
			{
				$arPattern = array();
				$arReplace = array();
				foreach ($this->smiles as $a_id => $row)
				{
					//$code  = str_replace(array(chr(34), chr(39)), array(chr(11), chr(12)), $row["TYPING"]);
					//$image = str_replace(array(chr(34), chr(39)), array(chr(11), chr(12)), $row["IMAGE"]);
					
					$code = str_replace(Array("'", "<", ">"), Array("\\'", "&lt;", "&gt;"), $row["TYPING"]);
					$patt = preg_quote($code, "/"); 
					$code = preg_quote(str_replace(array("\x5C"), array("&#092;"), $code)); 

					$image = preg_quote(str_replace("'", "\\'", $row["IMAGE"]));
					$description = preg_quote(htmlspecialchars($row["DESCRIPTION"], ENT_QUOTES), "/");
					
					$arPattern[] = "/(?<=[^\w&])$patt(?=.\W|\W.|\W$)/ei".BX_UTF_PCRE_MODIFIER;
					$arReplace[] = "\$this->convert_emoticon('$code', '$image', '$description', '$serverName')";
				}
				
				if (!empty($arPattern))
					$text = preg_replace($arPattern, $arReplace, ' '.$text.' ');
			}
		}

		if ($this->preg["counter"] > 0)
			$text = str_replace($this->preg["pattern"], $this->preg["replace"], $text);
		$text = str_replace(array(chr(11), chr(12)), array(chr(34), chr(39)), $text);
		return trim($text);
	}
	
	function defended_tags($text, $tag = 'replace')
	{
		switch ($tag) {
			case "replace":
				$this->preg["pattern"][] = "<\017#".$this->preg["counter"].">";
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
				"/\<(\/?)(quote|code|font|color|video|td|tr|table)([^\>]*)\>/is".BX_UTF_PCRE_MODIFIER,
				"/\[(\/?)(b|u|i|s|list|code|quote|font|color|url|img|video|td|tr|table)([^\]]*)\]/is".BX_UTF_PCRE_MODIFIER),
			"", 
			$text);
		return $text;
	}

	function convert4mail($text, $arImages = Array())
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
		$text = preg_replace($arPattern, $arReplace, $text);
		$text = str_replace("&shy;", "", $text);

		$dbSite = CSite::GetByID(SITE_ID);
		$arSite = $dbSite -> Fetch();
		$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
		if (strlen($serverName) <=0)
		{
			if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
				$serverName = SITE_SERVER_NAME;
			else
				$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
		}

		while (is_array($arImages) && list($IMAGE_ID, $FILE_ID)=each($arImages))
		{
			$f = CBlogImage::GetByID($IMAGE_ID);
			$fS = CFile::GetByID($FILE_ID);
			if($arS = $fS -> Fetch())
			{
				$fileSrc = "http://".$serverName."/".COption::GetOptionString("main", "upload_dir", "upload")."/".$arS["SUBDIR"]."/".$arS["FILE_NAME"];
				$text = str_replace("[IMG ID=$IMAGE_ID]", htmlspecialchars($f["TITLE"])." (IMG: ".$fileSrc." )", $text);
				$text = str_replace("[img id=$IMAGE_ID]", htmlspecialchars($f["TITLE"])." (IMG: ".$fileSrc." )", $text);
			}
		}

		return $text;
	}

	function convert_video($params, $path)
	{
		if (strLen($path) <= 0)
			return "";
		$width = ""; $height = ""; $preview = "";
		preg_match("/width\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $params, $width);
		preg_match("/height\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $params, $height);
		
		$params = str_replace(array(chr(11), chr(12)), array("\001", "\002"), $params);
		preg_match("/preview\=\002([^\002]+)\002/is".BX_UTF_PCRE_MODIFIER, $params, $preview);
		if (empty($preview))
			preg_match("/preview\=\001([^\001]+)\001/is".BX_UTF_PCRE_MODIFIER, $params, $preview);
		
		$width = intval($width[1]); 
		$width = ($width > 0 ? $width : 400);
		$height = intval($height[1]); 
		$height = ($height > 0 ? $height : 300);
		$preview = trim($preview[1]);
		$preview = (strLen($preview) > 0 ? $preview : "");

		$arFields = Array(
				"PATH" => $path, 
				"WIDTH" => $width, 
				"HEIGHT" => $height, 
				"PREVIEW" => $preview, 
		);
		$db_events = GetModuleEvents("blog", "videoConvert");
		if ($arEvent = $db_events->Fetch())
			$video = ExecuteModuleEventEx($arEvent, Array($arFields));

		if(strlen($video) > 0)
			return "<nomodify>".$video."</nomodify>";
		return false;
	}
	
	function convert_emoticon($code = "", $image = "", $description = "", $servername = "")
	{
		if (strlen($code)<=0 || strlen($image)<=0) return;
		$code = stripslashes($code); 
		$description = stripslashes($description);
		$image = stripslashes($image);
		
		$alt = "<\018#".$this->preg["counter"].">";
		$this->preg["pattern"][] = $alt;
		$this->preg["replace"][] = 'alt="smile'.$code.'" title="'.$description.'"';
		$this->preg["counter"]++;

		if ($this->path_to_smile !== false)
			return '<img src="'.$servername.$this->path_to_smile.$image.'" border="0" '.$alt.' />';
		return '<img src="'.$servername.'/bitrix/images/blog/smile/'.$image.'" border="0" '.$alt.' />';
	}

	function pre_convert_code_tag ($text = "")
	{
		if (strLen($text)<=0) return;
		$text = str_replace(
			array("&", "<", ">", "[", "]", "\001", "\002"), 
			array("&#38;", "&#60;", "&#62;", "&#91;", "&#93;", "&#91;code&#93;", "&#91;/code&#93;"), $text);
		
		$word_separator = str_replace("\]", "", $this->word_separator);
		$text = preg_replace("'(?<=^|[".$word_separator."]|\s)((http|https|news|ftp|aim|mailto)://[\.\-\_\:a-z0-9\@]([^\011\s\'\012\[\]\{\}])*)'is", 
			"[nomodify]\\1[/nomodify]", $text);

		return $text;
	}
	
	function convert_code_tag($text = "", $type = "html", $allowHTML = false)
	{
		if (strLen($text)<=0) return;
		$type = ($type == "rss" ? "rss" : "html"); 
		$text = str_replace(Array("[nomodify]", "[/nomodify]"), Array("", ""), $text);
		//if(!$allowHTML)
		//{
		
			$text = str_replace(
				array("<", ">", "\\r", "\\n", "\\", "[", "]", "\001", "\002", "  ", "\t"), 
				array("&#60;", "&#62;", "&#92;r", "&#92;n", "&#92;", "&#91;", "&#93;", "&#91;code&#93;", "&#91;/code&#93;", "&nbsp;&nbsp;", "&nbsp;&nbsp;&nbsp;"), $text);

			$text = stripslashes($text);
		
		//}
		
		if ($this->code_open == $this->code_closed && $this->code_error == 0)
		{
				$this->preg["pattern"][] = "<\017#".$this->preg["counter"].">";
				$this->preg["replace"][] = $this->convert_open_tag('code', $type)."<pre>".$text."</pre>".$this->convert_close_tag('code', $type);
				$text = "<\017#".$this->preg["counter"].">";
				$this->preg["counter"]++;
		}
		
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
			return $txt;
		return $text;
	}

	function convert_open_tag($marker = "quote", $type = "html")
	{
		$marker = (strToLower($marker) == "code" ? "code" : "quote");
		$type = ($type == "rss" ? "rss" : "html");
		
		$this->{$marker."_open"}++;
		if ($type == "rss")
			return "\n====".$marker."====\n";
		return "<div class='blog-post-".$marker."'><span>".GetMessage("BLOG_".ToUpper($marker))."</span><table class='blog".$marker."'><tr><td>".$text;
	}
	
	function convert_close_tag($marker = "quote", $type = "html")
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
		return "</td></tr></tbody></table></div>";
	}

	function convert_image_tag($url = "", $type = "html", $serverName="", $params = "")
	{
		$url = trim($url);
		if (strlen($url)<=0) return;
		
		$type = (strToLower($type) == "rss" ? "rss" : "html");
		$extension = preg_replace("/^.*\.(\S+)$/".BX_UTF_PCRE_MODIFIER, "\\1", $url);
		$extension = strtolower($extension);
		$extension = preg_quote($extension, "/");
		
		preg_match("/width\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $params, $width);
		preg_match("/height\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $params, $height);
		$width = intval($width[1]); 
		$height = intval($height[1]); 

		$bErrorIMG = False;
		if (preg_match("/[?&;]/".BX_UTF_PCRE_MODIFIER, $url))
			$bErrorIMG = True;
		if (!$bErrorIMG && !preg_match("/$extension(\||\$)/".BX_UTF_PCRE_MODIFIER, $this->allow_img_ext))
			$bErrorIMG = True;
		if (!$bErrorIMG && !preg_match("/^((http|https|ftp)\:\/\/[-_:.a-z0-9@]+)*(\/[-_+\/=:.a-z0-9@%]+)$/i".BX_UTF_PCRE_MODIFIER, $url)) 
			$bErrorIMG = True;

		if ($bErrorIMG)
			return "[img]".$url."[/img]";
			
		$strPar = "";
		if($width > 0)
		{
			if($width > $this->imageWidth)
			{
				$height = IntVal($height * ($this->imageWidth / $width));
				$width = $this->imageWidth;
			}
		}
		if($height > 0)
		{
			if($height > $this->imageHeight)
			{
				$width = IntVal($width * ($this->imageHeight / $height));
				$height = $this->imageHeight;
			}
		}
		if($width > 0)
			$strPar = " width=\"".$width."\"";
		if($height > 0)
			$strPar .= " height=\"".$height."\"";
			
		if(strlen($serverName) <= 0 || preg_match("/^(http|https|ftp)\:\/\//i".BX_UTF_PCRE_MODIFIER, $url))
			return '<img src="'.$url.'" border="0"'.$strPar.' />';
		else
			return '<img src="'.$serverName.$url.'" border="0"'.$strPar.' />';
	}	
	
	function convert_blog_image($p1 = "", $imageId = "", $p2 = "", $type = "html", $serverName="")
	{		
		$imageId = IntVal($imageId);
		if($imageId <= 0)
			return;
		
		$res = "";
		if(IntVal($this->arImages[$imageId]) > 0)
		{
			if($f = CBlogImage::GetByID($imageId))
			{
				$db_img = CFile::GetByID($this->arImages[$imageId]);
				if($db_img_arr = $db_img->Fetch())
				{
					$strImage = $serverName."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$db_img_arr["SUBDIR"]."/".$db_img_arr["FILE_NAME"];

					$strPar = "";
					preg_match("/width\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $p1, $width);
					preg_match("/height\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $p1, $height);
					$width = intval($width[1]); 
					$height = intval($height[1]); 
					
					if($width <= 0)
					{
						preg_match("/width\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $p2, $width);
						$width = intval($width[1]); 
					}
					if($height <= 0)
					{
						preg_match("/height\=([0-9]+)/is".BX_UTF_PCRE_MODIFIER, $p2, $height);
						$height = intval($height[1]); 
					}

					if(IntVal($width) <= 0)
						$width = $db_img_arr["WIDTH"];
					if(IntVal($height) <= 0)
						$height= $db_img_arr["HEIGHT"];

					if($width > $this->imageWidth || $height > $this->imageHeight)
					{
						$arFileTmp = CFile::ResizeImageGet(
							$db_img_arr,
							array("width" => $this->imageWidth, "height" => $this->imageHeight),
							BX_RESIZE_IMAGE_PROPORTIONAL,
							true
						);
						$strImage = $serverName.$arFileTmp["src"];
						$width = $arFileTmp["width"];
						$height = $arFileTmp["height"];
					}
					
						
					$strPar = ' width="'.$width.'" height="'.$height.'"';

					$res = '<img src="'.$strImage.'" title="'.$f["TITLE"].'" border="0"'.$strPar.'/>';
				}
			}
		}
		return $res;
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
			$value = intVal($value > $count ? ($count - 1) : $value);
			return '<span style="font-size:'.$this->arFontSize[$value].'%;">'.$text.'</span>';
		}
		else if ($attr == 'color')
		{
			$value = preg_replace("/[^\w#]/", "" , $value);
			return '<span style="color:'.$value.'">'.$text.'</span>';
		}
		else if ($attr == 'font')
		{
			$value = preg_replace("/[^\w]/", "" , $value);
			return '<span style="font-family:'.$value.'">'.$text.'</span>';
		}
	}
	// Only for public using
	function wrap_long_words($text="")
	{
		if ($this->MaxStringLen > 0 && !empty($text))
		{
			$text = str_replace(array(chr(11), chr(12), chr(34), chr(39)), array("", "", chr(11), chr(12)), $text);
			$text = preg_replace("/(?<=^|\>)([^\<]+)(?=\<|$)/ies".BX_UTF_PCRE_MODIFIER, "\$this->part_long_words('\\1')", $text);
			$text = str_replace(array(chr(11), chr(12)), array(chr(34), chr(39)), $text);
		}
		return $text;
	}

	function part_long_words($str)
	{
		$word_separator = $this->word_separator; 
		if (($this->MaxStringLen > 0) && (strLen(trim($str)) > 0))
		{
			$str = str_replace(
				array(chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), 
					"&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;", 
					chr(34), chr(39)), 
				array("", "", "", "", "", "", "", "", 
					chr(1), "<", ">", chr(2), chr(3), chr(4), chr(5), chr(6), 
					chr(7), chr(8)), 
				$str);
			$str = preg_replace("/(?<=[".$word_separator."]|^)(([^".$word_separator."]+))(?=[".$word_separator."]|$)/ise".BX_UTF_PCRE_MODIFIER, 
				"\$this->cut_long_words('\\2')", $str);

			$str = str_replace(
				array(chr(1), "<", ">", chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), "&lt;WBR/&gt;", "&lt;WBR&gt;", "&amp;shy;"),
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

	function convert_anchor_tag($url, $text, $pref="")
	{
		if(strlen(trim($text)) <= 0)
			$text = $url;
		$bCutUrl = false;
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
				"/javascript:/i".BX_UTF_PCRE_MODIFIER, 
				"/[".chr(12)."\']/".BX_UTF_PCRE_MODIFIER), 
			array(
				"&", 
				"java script&#58; ", 
				"%27") , 
			$url);
		if (substr($url, 0, 1) != "/" && !preg_match("/^(http|news|https|ftp|aim|mailto)\:\/\//i".BX_UTF_PCRE_MODIFIER, $url))
			$url = "http://".$url;
		if (!preg_match("/^((http|https|news|ftp|aim):\/\/[-_:.a-z0-9@]+)*([^\"\'\011\012])+$/i".BX_UTF_PCRE_MODIFIER, $url))
			return $pref.$text." (".$url.")".$end;

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
		return $pref.(COption::GetOptionString("blog", "parser_nofollow", "N") == "Y" ? '<noindex>' : '').'<a href="'.$url.'" target="_blank"'.(COption::GetOptionString("blog", "parser_nofollow", "N") == "Y" ? ' rel="nofollow"' : '').'>'.$text.'</a>'.(COption::GetOptionString("blog", "parser_nofollow", "N") == "Y" ? '</noindex>' : '').$end;
	}

	function convert_to_rss($text, $arImages = Array(), $arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "TABLE" => "Y", "CUT_ANCHOR" => "N"), $bPreview = true)
	{
		$text = $this->convert($text, $bPreview, $arImages, $arAllow, "rss");			
		return trim($text);
	}
}

class CBlogTools
{
	function htmlspecialcharsExArray($array)
	{
		$res = Array();
		if(!empty($array) && is_array($array))
		{
			foreach($array as $k => $v)
			{
				if(is_array($v))
				{
					foreach($v as $k1 => $v1)
					{
						$res[$k1] = htmlspecialcharsex($v1);
						$res['~'.$k1] = $v1;
					}
				}
				else
				{
					$res[$k] = htmlspecialcharsex($v);
					$res['~'.$k] = $v;
				}
			}
		}
		return $res;
	}
	
	function ResizeImage($aFile, $sizeX, $sizeY)
	{
		if(!is_array($aFile) && IntVal($aFile) > 0)
		{
			$dbFile = CFile::GetByID($aFile);
			if($arFile = $dbFile->Fetch())
				$aFile = $arFile;
			else
				return false;
		}
		elseif(!is_array($aFile))
			return false;
		
		$file = "/".COption::GetOptionString("main", "upload_dir", "upload")."/".$aFile["SUBDIR"]."/".$aFile["FILE_NAME"];
		$orig = @getimagesize($_SERVER["DOCUMENT_ROOT"].$file);
		$image_type = $orig[2];

		switch ($image_type)
		{
			case IMAGETYPE_JPEG:
				if(!function_exists("imageJPEG") || !function_exists("imagecreatefromjpeg"))
					return false;
				else
					$imageInput = imagecreatefromjpeg($_SERVER["DOCUMENT_ROOT"].$file);
			break;
			case IMAGETYPE_GIF:
				if(!function_exists("imageGIF") || !function_exists("imagecreatefromgif"))
					return false;
				else
					$imageInput = imagecreatefromgif($_SERVER["DOCUMENT_ROOT"].$file);
			break;
			case IMAGETYPE_PNG:
				if(!function_exists("imagePNG") || !function_exists("imagecreatefrompng"))
					return false;
				else
					$imageInput = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"].$file);
			break;
		}

		if($imageInput)
		{
			$imgX = imagesx($imageInput);
			$imgY = imagesy($imageInput);
		}

		if ($imgX > $sizeX || $imgY > $sizeY)
		{
			$newX = $sizeX;
			$newY = $imgY * ($newX / $imgX);

			if ($newY > $sizeY)
			{
				$newY = $sizeY;
				$newX = $imgX * ($newY / $imgY);
			}

			if (function_exists("imagecreatetruecolor"))
				$imageOutput = ImageCreateTrueColor($newX, $newY);
			else
				$imageOutput = ImageCreate($newX, $newY);

			if(function_exists("imagecopyresampled"))
				imagecopyresampled($imageOutput, $imageInput, 0, 0, 0, 0, $newX, $newY, $imgX, $imgY);
			else
				imagecopyresized($imageOutput, $imageInput, 0, 0, 0, 0, $newX, $newY, $imgX, $imgY);
			
			$file = "/".COption::GetOptionString("main", "upload_dir", "upload")."/blog/"."resized_".$aFile["FILE_NAME"];
			switch ($image_type)
			{
				case IMAGETYPE_JPEG:
					imageJPEG($imageOutput, $_SERVER["DOCUMENT_ROOT"].$file);
				break;
				case IMAGETYPE_GIF:
					imageGIF($imageOutput, $_SERVER["DOCUMENT_ROOT"].$file);
				break;
				case IMAGETYPE_PNG:
					imagePNG($imageOutput, $_SERVER["DOCUMENT_ROOT"].$file);
				break;
			}
		}
		return $file;
	}
	
	function GetDateTimeFormat()
	{
		$timestamp = mktime(7,30,45,2,22,2007);
		return array(
				"d-m-Y H:i:s" => date("d-m-Y H:i:s", $timestamp),//"22-02-2007 7:30",
				"m-d-Y H:i:s" => date("m-d-Y H:i:s", $timestamp),//"02-22-2007 7:30",
				"Y-m-d H:i:s" => date("Y-m-d H:i:s", $timestamp),//"2007-02-22 7:30",
				"d.m.Y H:i:s" => date("d.m.Y H:i:s", $timestamp),//"22.02.2007 7:30",
				"m.d.Y H:i:s" => date("m.d.Y H:i:s", $timestamp),//"02.22.2007 7:30",
				"j M Y H:i:s" => date("j M Y H:i:s", $timestamp),//"22 Feb 2007 7:30",
				"M j, Y H:i:s" => date("M j, Y H:i:s", $timestamp),//"Feb 22, 2007 7:30",
				"j F Y H:i:s" => date("j F Y H:i:s", $timestamp),//"22 February 2007 7:30",
				"F j, Y H:i:s" => date("F j, Y H:i:s", $timestamp),//"February 22, 2007",
				"d.m.y g:i A" => date("d.m.y g:i A", $timestamp),//"22.02.07 1:30 PM",
				"d.m.y G:i" => date("d.m.y G:i", $timestamp),//"22.02.07 7:30",
				"d.m.Y H:i:s" => date("d.m.Y H:i:s", $timestamp),//"22.02.2007 07:30",
			);
	}
	
	function DeleteDoubleBR($text)
	{
		if(strpos($text, "<br />\r<br />") !== false)
		{
			$text = str_replace("<br />\r<br />", "<br />", $text);
			return CBlogTools::DeleteDoubleBR($text);
		}
		if(strpos($text, "<br /><br />") !== false)
		{
			$text = str_replace("<br /><br />", "<br />", $text);
			return CBlogTools::DeleteDoubleBR($text);
		}		
		
		if(strpos($text, "<br />") == 0 && strpos($text, "<br />") !== false)
		{
			$text = substr($text, 6);
		}		
		return $text;
	}
}
?>
