<?
/************************************************************************/
/********************* XML Classes **************************************/
/************************************************************************/

/**********************************************************************/
/*********   CDataXMLNode   ****************************************/
/**********************************************************************/
class CDataXMLNode
{
	var $name;				// Name of the node
	var $content;			// Content of the node
	var $children;			// Subnodes
	var $attributes;		// Attributes

	function CDataXMLNode()
	{
	}

	function name() { return $this->name; }
	function children() { return $this->children; }
	function textContent() { return $this->content; }

	function getAttribute($attribute)
	{
		if(is_array($this->attributes))
			foreach ($this->attributes as $anode)
				if ($anode->name == $attribute)
					return $anode->content;
		return "";
	}

	function getAttributes()
	{
		return $this->attributes;
	}

	function namespaceURI()
	{
		return $this->getAttribute("xmlns");
	}

	function elementsByName($tagname)
	{
		$result = array();

		if ($this->name == $tagname)
			array_push($result, $this);

		if (count($this->children))
		foreach ($this->children as $node)
		{
			$more = $node->elementsByName($tagname);
			if (is_array($more) and count($more))
				foreach($more as $mnode)
					array_push($result, $mnode);
		}
		return $result;
	}

	function _SaveDataType_OnDecode(&$result, $name, $value)
	{
		if (isset($result[$name]))
		{
			$i = 1;
			while (isset($result[$i.":".$name])) $i++;
			$result[$i.":".$name] = $value;
			return "indexed";
		}
		else
		{
			$result[$name] = $value;
			return "common";
		}
	}

	function decodeDataTypes($attrAsNodeDecode = false)
	{
		$result = array();

		if (!$this->children)
			$this->_SaveDataType_OnDecode($result, $this->name(), $this->textContent());
		else
			foreach ($this->children() as $child)
			{
				$cheese = $child->children();
				if (!$cheese or !count($cheese))
				{
					$this->_SaveDataType_OnDecode($result, $child->name(), $child->textContent());
				}
				else
				{
					$cheresult = $child->decodeDataTypes();
					if (is_array($cheresult))
						$this->_SaveDataType_OnDecode($result, $child->name(), $cheresult);
				}
			}

		if ($attrAsNodeDecode)
			foreach ($this->getAttributes() as $child)
			{
				$this->_SaveDataType_OnDecode($result, $child->name(), $child->textContent());
			}

		return $result;
	}

	function &__toString()
	{
		$ret = "";

		switch ($this->name)
		{
			case "cdata-section":
				$ret = "<![CDATA[";
				$ret .= $this->content;
				$ret .= "]]>";
				break;

			default:
				$isOneLiner = false;

				if (count($this->children)==0 && (strlen($this->content)<=0))
					$isOneLiner = true;

				$attrStr = "";

				if (count($this->attributes) > 0)
					foreach ($this->attributes as $attr)
					{
						$attrStr .= " ".$attr->name."=\"".CDataXML::xmlspecialchars($attr->content)."\" ";
					}

				if ($isOneLiner)
					$oneLinerEnd = " /";
				else
					$oneLinerEnd = "";

				$ret = "<".$this->name.$attrStr.$oneLinerEnd.">";

				if (count($this->children)>0)
					foreach ($this->children as $child)
					{
						$ret .= $child->__toString();
					}

				if (!$isOneLiner)
				{
					if (strlen($this->content)>0)
						$ret .= CDataXML::xmlspecialchars($this->content);

					$ret .= "</".$this->name.">";
				}

				break;
		}

		return $ret;
	}

	function &__toArray()
	{
		$arInd = array();
		$retHash = array();

		$retHash["@"] = array();
		if (count($this->attributes) > 0)
			foreach ($this->attributes as $attr)
			{
				$retHash["@"][$attr->name] = $attr->content;
				$numAdded++;
			}

		$retHash["#"] = "";
		if (strlen($this->content)>0)
		{
			$retHash["#"] = $this->content;
		}
		else
		{
			if (count($this->children)>0)
			{
				$ar = array();
				foreach ($this->children as $child)
				{
					if (array_key_exists($child->name, $arInd))
						$arInd[$child->name] = $arInd[$child->name] + 1;
					else
						$arInd[$child->name] = 0;

					$ar[$child->name][$arInd[$child->name]] = $child->__toArray();
				}
				$retHash["#"] = $ar;
			}
		}

		return $retHash;
	}
}



/**********************************************************************/
/*********   CDataXMLDocument   ******************************************/
/**********************************************************************/
class CDataXMLDocument
{
	var $version;				// XML version
	var $encoding;				// XML encoding

	var $children;
	var $root;

	function CDataXMLDocument()
	{
	}

	function elementsByName($tagname)
	{
		$result = array();
		if (count($this->children))
		foreach ($this->children as $node)
		{
			$more = $node->elementsByName($tagname);
			if (is_array($more) and count($more))
				foreach($more as $mnode)
					array_push($result, $mnode);
		}
		return $result;
	}

	function encodeDataTypes( $name, $value)
	{
		$Xsd = array(
			"string"=>"string", "bool"=>"boolean", "boolean"=>"boolean",
			"int"=>"integer", "integer"=>"integer", "double"=>"double", "float"=>"float", "number"=>"float",
			"array"=>"anyType", "resource"=>"anyType",
			"mixed"=>"anyType", "unknown_type"=>"anyType", "anyType"=>"anyType"
		);

		$node = new CDataXMLNode();
		$node->name = $name;

		$nameSplited = preg_split("/:/", $name);
		if ($nameSplited)
			$name = $nameSplited[count($nameSplited) - 1];

		if (is_object($value))
		{
			$ovars = get_object_vars($value);
			foreach ($ovars as $pn => $pv)
			{
				$decode = CDataXMLDocument::encodeDataTypes( $pn, $pv);
				if ($decode) array_push($node->children, $decode);
			}
		}
		else if (is_array($value))
		{
			foreach ($value as $pn => $pv)
			{
				$decode = CDataXMLDocument::encodeDataTypes( $pn, $pv);
				if ($decode) array_push($node->children, $decode);
			}
		}
		else
		{
			if (isset($Xsd[gettype($value)]))
			{
				$node->content = $value;
			}
		}
		return $node;
	}

	/* Returns a XML string of the DOM document */
	function &__toString()
	{
		$ret = "<"."?xml";
		if (strlen($this->version)>0)
			$ret .= " version=\"".$this->version."\"";
		if (strlen($this->encoding)>0)
			$ret .= " encoding=\"".$this->encoding."\"";
		$ret .= "?".">";

		if (count($this->children) > 0)
			foreach ($this->children as $child)
			{
				$ret .= $child->__toString();
			}

		return $ret;
	}

	/* Returns an array of the DOM document */
	function &__toArray()
	{
		$arRetArray = array();

		if (count($this->children)>0)
			foreach ($this->children as $child)
			{
				$arRetArray[$child->name] = $child->__toArray();
			}

		return $arRetArray;
	}
}



/**********************************************************************/
/*********   CDataXML   **************************************************/
/**********************************************************************/
class CDataXML
{
	var $tree;
	var $TrimWhiteSpace;

	var $delete_ns = true;

	function CDataXML($TrimWhiteSpace = True)
	{
		$this->TrimWhiteSpace = ($TrimWhiteSpace ? True : False);
		$this->tree = False;
	}

	function Load($file)
	{
		global $APPLICATION;

		unset($this->tree);
		$this->tree = False;

		if (file_exists($file))
		{
			$content = file_get_contents($file);
			$charset = "windows-1251";
			if (preg_match("/<"."\?XML[^>]{1,}encoding=[\"']([^>\"']{1,})[\"'][^>]{0,}\?".">/i", $content, $matches))
			{
				$charset = Trim($matches[1]);
			}
			$content = $APPLICATION->ConvertCharset($content, $charset, SITE_CHARSET);
			$this->tree = &$this->__parse($content);
			return $this->tree !== false;
		}

		return false;
	}

	function LoadString($text)
	{
		unset($this->tree);
		$this->tree = False;

		if (strlen($text)>0)
		{
			$this->tree = &$this->__parse($text);
			return $this->tree !== false;
		}

		return false;
	}

	function &GetTree()
	{
		return $this->tree;
	}

	function &GetArray()
	{
		return $this->tree->__toArray();
	}

	function &GetString()
	{
		return $this->tree->__toString();
	}

	function &SelectNodes($strNode)
	{
		if (!is_object($this->tree))
			return false;

		$result = &$this->tree;

		$tmp = explode("/", $strNode);
		for ($i = 1; $i < count($tmp); $i++)
		{
			if ($tmp[$i] != "")
			{
				if (!is_array($result->children))
					return false;

				$bFound = False;
				for ($j = 0; $j < count($result->children); $j++)
				{
					if ($result->children[$j]->name==$tmp[$i])
					{
						$result = &$result->children[$j];
						$bFound = True;
						break;
					}
				}

				if (!$bFound)
					return False;
			}
		}

		return $result;
	}

	function xmlspecialchars($str)
	{
		static $search = array("&","<",">","\"","'");
		static $replace = array("&amp;","&lt;","&gt;","&quot;","&apos;");
		return str_replace($search, $replace, $str);
	}

	function xmlspecialcharsback($str)
	{
		static $search = array("&lt;","&gt;","&quot;","&apos;","&amp;");
		static $replace = array("<",">","\"","'","&");
		return str_replace($search, $replace, $str);
	}

	/* Will return an DOM object tree from the well formed XML. */
	function &__parse(&$strXMLText)
	{
		global $APPLICATION;

		static $search = array("&gt;","&lt;","&apos;","&quot;","&amp;");
		static $replace = array(">","<","'",'"',"&");

		$TagStack = array();

		$oXMLDocument = new CDataXMLDocument();

		// stip the !doctype
		$strXMLText = &preg_replace("%<\!DOCTYPE.*?\]>%is", "", $strXMLText);
		$strXMLText = &preg_replace("%<\!DOCTYPE.*?>%is", "", $strXMLText);

		// get document version and encoding from header
		preg_match_all("#<\?(.*?)\?>#i", $strXMLText, $arXMLHeader_tmp);
		foreach ($arXMLHeader_tmp[0] as $strXMLHeader_tmp)
		{
			preg_match_all("/([a-zA-Z:]+=\".*?\")/i", $strXMLHeader_tmp, $arXMLParam_tmp);
			foreach ($arXMLParam_tmp[0] as $strXMLParam_tmp)
			{
				if (strlen($strXMLParam_tmp)>0)
				{
					$arXMLAttribute_tmp = explode("=\"", $strXMLParam_tmp);
					if ($arXMLAttribute_tmp[0]=="version")
						$oXMLDocument->version = substr($arXMLAttribute_tmp[1], 0, strlen($arXMLAttribute_tmp[1]) - 1);
					elseif ($arXMLAttribute_tmp[0]=="encoding")
						$oXMLDocument->encoding = substr($arXMLAttribute_tmp[1], 0, strlen($arXMLAttribute_tmp[1]) - 1);
				}
			}
		}

		// strip header
		$strXMLText = &preg_replace("#<\?.*?\?>#", "", $strXMLText);

		// strip comments
		$strXMLText = &CDataXML::__stripComments($strXMLText);

		$oXMLDocument->root = &$oXMLDocument->children;
		$currentNode = &$oXMLDocument;

		$tok = strtok($strXMLText, "<");
		$arTag = explode(">", $tok);
		if(count($arTag) < 2)
		{//There was whitespace before <, so make another try
			$tok = strtok("<");
			$arTag = explode(">", $tok);
			if(count($arTag) < 2)
			{
				//It's an broken XML
				return false;
			}
		}

		while($tok !== false)
		{
			$tagName = $arTag[0];
			$tagContent = $arTag[1];

			// find tag name with attributes
			// check if it's an endtag </tagname>
			if($tagName[0] == "/")
			{
				$tagName = substr($tagName, 1);
				// strip out namespace; nameSpace:Name
				if($this->delete_ns)
				{
					$colonPos = strpos($tagName, ":");

					if ($colonPos > 0)
						$tagName = substr($tagName, $colonPos + 1);
				}

				if($currentNode->name != $tagName)
				{
					// Error parsing XML, unmatched tags $tagName
					return false;
				}

				$currentNode = $currentNode->_parent;

				// convert special chars
				if ((!$this->TrimWhiteSpace) || (trim($tagContent) != ""))
					$currentNode->content = str_replace($search, $replace, $tagContent);
			}
			elseif(strncmp($tagName, "![CDATA[", 8) === 0)
			{
				//because cdata may contain > and < chars
				//it is special processing needed
				$cdata = "";
				for($i = 0, $c = count($arTag); $i < $c; $i++)
				{
					$cdata .= $arTag[$i].">";
					if(substr($cdata, -3) == "]]>")
					{
						$tagContent = $arTag[$i+1];
						break;
					}
				}

				if(substr($cdata, -3) != "]]>")
				{
					$cdata = substr($cdata, 0, -1)."<";
					do
					{
						$tok = strtok(">");//unfortunatly strtok eats > followed by >
						$cdata .= $tok.">";
						//util end of string or end of cdata found
					} while ($tok !== false && substr($tok, -2) != "]]");
					//$tagName = substr($tagName, 0, -1);
				}

				$cdataSection = substr($cdata, 8, -3);

				// new CDATA node
				$subNode = new CDataXMLNode();
				$subNode->name = "cdata-section";
				$subNode->content = $cdataSection;

				$currentNode->children[] = $subNode;

				// convert special chars
				if ((!$this->TrimWhiteSpace) || (trim($tagContent) != ""))
					$currentNode->content = str_replace($search, $replace, $tagContent);
			}
			else
			{
				// normal start tag
				$firstSpaceEnd = strpos($tagName, " ");
				$firstNewlineEnd = strpos($tagName, "\n");

				if ($firstNewlineEnd != false)
				{
					if ($firstSpaceEnd != false)
					{
						$tagNameEnd = min($firstSpaceEnd, $firstNewlineEnd);
					}
					else
					{
						$tagNameEnd = $firstNewlineEnd;
					}
				}
				else
				{
					if ($firstSpaceEnd != false)
					{
						$tagNameEnd = $firstSpaceEnd;
					}
					else
					{
						$tagNameEnd = 0;
					}
				}

				if ($tagNameEnd > 0)
					$justName = substr($tagName, 0, $tagNameEnd);
				else
					$justName = $tagName;

				// strip out namespace; nameSpace:Name
				if ($this->delete_ns)
				{
					$colonPos = strpos($justName, ":");

					if ($colonPos > 0)
						$justName = substr($justName, $colonPos + 1);
				}

				// remove trailing / from the name if exists
				$justName = rtrim($justName, "/");

				$subNode = new CDataXMLNode();
				$subNode->_parent = $currentNode;
				$subNode->name = $justName;

				// find attributes
				if ($tagNameEnd > 0)
				{
					$attributePart = substr($tagName, $tagNameEnd);

					// attributes
					unset($attr);
					$attr = CDataXML::__parseAttributes($attributePart);

					if ($attr != false)
						$subNode->attributes = $attr;
				}

				// convert special chars
				if ((!$this->TrimWhiteSpace) || (trim($tagContent) != ""))
					$subNode->content = str_replace($search, $replace, $tagContent);

				$currentNode->children[] = $subNode;

				if (substr($tagName, -1) != "/")
					$currentNode = $subNode;
			}

			//Next iteration
			$tok = strtok("<");
			$arTag = explode(">", $tok);
			//There was whitespace before < just after CDATA section, so make another try
			if(count($arTag) < 2 && (strncmp($tagName, "![CDATA[", 8) === 0))
			{
				$tok = strtok("<");
				$arTag = explode(">", $tok);
			}
		}
		return $oXMLDocument;
	}

	function __stripComments(&$str)
	{
		$str = &preg_replace("#<\!--.*?-->#s", "", $str);
		return $str;
	}

	/* Parses the attributes. Returns false if no attributes in the supplied string is found */
	function &__parseAttributes($attributeString)
	{
		$ret = false;

		preg_match_all("/(\\S+)\\s*=\\s*([\"'])(.*?)\\2/s".BX_UTF_PCRE_MODIFIER, $attributeString, $attributeArray);

		foreach ($attributeArray[0] as $i => $attributePart)
		{
			$attributePart = trim($attributePart);
			if ($attributePart != "" && $attributePart != "/")
			{
				$attributeName = $attributeArray[1][$i];

				// strip out namespace; nameSpace:Name
				if ($this->delete_ns)
				{
					$colonPos = strpos($attributeName, ":");

					if ($colonPos > 0)
						$attributeName = substr($attributeName, $colonPos + 1);
				}
				$attributeValue = $attributeArray[3][$i];

				unset($attrNode);
				$attrNode = new CDataXMLNode();
				$attrNode->name = $attributeName;
				$attrNode->content = CDataXML::xmlspecialcharsback($attributeValue);

				$ret[] = &$attrNode;
			}
		}
		return $ret;
	}
}
?>