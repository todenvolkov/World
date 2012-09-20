<?
IncludeModuleLangFile(__FILE__);

class CIBlockCMLImport
{
	var $LAST_ERROR = "";
	var $next_step = false;
	var $files_dir = false;
	var $use_offers = true;
	var $use_iblock_type_id = false;
	var $use_crc = true;
	var $preview = false;
	var $detail = false;

	var $_xml_file = false;

	var $bCatalog = false;
	var $PROPERTY_MAP = false;
	var $SECTION_MAP = false;
	var $PRICES_MAP = false;
	var $arProperties = false;
	var $arSectionCache = array();
	var $arElementCache = array();
	var $arEnumCache = array();
	var $arCurrencyCache = array();
	var $arTaxCache = array();

	var $arTempFiles = array();
	var $arLinkedProps = false;

	var $translit_on_add = false;
	var $translit_on_update = false;
	var $translit_params = array();

	function InitEx(&$next_step, $params)
	{
		$defaultParams = array(
			"files_dir" => false,
			"use_crc" => true,
			"preview" => false,
			"detail" => false,
			"use_offers" => false,
			"use_iblock_type_id" => false,
			"table_name" => "b_xml_tree",
			"translit_on_add" => false,
			"translit_on_update" => false,
			"translit_params" => array(
				"max_len" => 100,
				"change_case" => 'L', // 'L' - toLower, 'U' - toUpper, false - do not change
				"replace_space" => '_',
				"replace_other" => '_',
				"delete_repeat_replace" => true,
			),
		);
		foreach($defaultParams as $key => $value)
			if(!array_key_exists($key, $params))
				$params[$key] = $value;

		$this->Init($next_step,
			$params["files_dir"],
			$params["use_crc"],
			$params["preview"],
			$params["detail"],
			$params["use_offers"],
			$params["use_iblock_type_id"],
			$params["table_name"]
		);

		if($params["translit_on_add"])
			$this->translit_on_add = $params["translit_params"];
		if($params["translit_on_update"])
			$this->translit_on_update = $params["translit_params"];
	}

	function Init(&$next_step, $files_dir = false, $use_crc = true, $preview = false, $detail = false, $use_offers = false, $use_iblock_type_id = false, $table_name = "b_xml_tree")
	{
		$this->next_step = &$next_step;
		$this->files_dir = $files_dir;
		$this->use_offers = $use_offers;
		$this->use_iblock_type_id = $use_iblock_type_id;
		$this->use_crc = $use_crc;

		$this->_xml_file = new CIBlockXMLFile($table_name);

		if(is_array($preview) && count($preview)==2)
			$this->preview = $preview;
		elseif(!is_array($preview) && $preview)
			$this->preview = true;
		else
			$this->preview = false;

		if(is_array($detail) && count($detail)==2)
			$this->detail = $detail;
		else
			$this->detail = false;

		//When preview is "true" we will use iblock settings for picture handling
		//so ignore detail setting
		if(!is_array($preview) && $preview)
			$this->detail = true;

		$this->bCatalog = CModule::IncludeModule('catalog');
		$this->arProperties = array();
		$this->PROPERTY_MAP = array();
		if($this->next_step["IBLOCK_ID"] > 0)
		{
			$obProperty = new CIBlockProperty;
			$rsProperties = $obProperty->GetList(array(), array("IBLOCK_ID"=>$this->next_step["IBLOCK_ID"], "ACTIVE"=>"Y"));
			while($arProperty = $rsProperties->Fetch())
			{
				$this->PROPERTY_MAP[$arProperty["XML_ID"]] = $arProperty["ID"];
				$this->arProperties[$arProperty["ID"]] = $arProperty;
			}
		}
		$this->arTempFiles = array();
		$this->arLinkedProps = false;
	}

	function CheckIfFileIsCML($file_name)
	{
		global $APPLICATION;

		if(file_exists($file_name) && is_file($file_name))
		{
			$fp = fopen($file_name, "rb");
			if(is_resource($fp))
			{
				$header = fread($fp, 1024);
				fclose($fp);

				if(preg_match("/<"."\?XML[^>]{1,}encoding=[\"']([^>\"']{1,})[\"'][^>]{0,}\?".">/i", $header, $matches))
				{
					if(strtoupper($matches[1]) !== strtoupper(LANG_CHARSET))
						$header = $APPLICATION->ConvertCharset($header, $matches[1], LANG_CHARSET);
				}

				if(strpos($header, "<".GetMessage("IBLOCK_XML2_COMMERCE_INFO")) !== false)
					return true;
			}
		}
		return false;
	}

	function DropTemporaryTables()
	{
		return $this->_xml_file->DropTemporaryTables();
	}

	function CreateTemporaryTables()
	{
		return $this->_xml_file->CreateTemporaryTables();
	}

	function IndexTemporaryTables()
	{
		return $this->_xml_file->IndexTemporaryTables();
	}

	function ReadXMLToDatabase($fp, &$NS, $time_limit=0, $read_size = 1024)
	{
		return $this->_xml_file->ReadXMLToDatabase($fp, $NS, $time_limit, $read_size);
	}

	function StartSession($sess_id)
	{
		return $this->_xml_file->StartSession($sess_id);
	}

	function GetSessionRoot()
	{
		return $this->_xml_file->GetSessionRoot();
	}

	function EndSession()
	{
		return $this->_xml_file->EndSession();
	}

	function CleanTempFiles()
	{
		foreach($this->arTempFiles as $file)
			@unlink($file);
		$this->arTempFiles = array();
	}

	function MakeFileArray($file)
	{
		if((strlen($file)>0) && is_file($this->files_dir.$file))
			return CFile::MakeFileArray($this->files_dir.$file);
		else
			return array("tmp_name"=>"", "del"=>"Y");
	}

	function ResizePicture($file, $resize)
	{
		if(strlen($file) <= 0)
			return array("tmp_name"=>"", "del"=>"Y");

		if(file_exists($this->files_dir.$file) && is_file($this->files_dir.$file))
			$file = $this->files_dir.$file;
		elseif(file_exists($file) && is_file($file))
			$file = $file;
		else
			return array("tmp_name"=>"", "del"=>"Y");

		if(!is_array($resize) || !preg_match("#(\\.)([^./\\\\]+?)$#", $file))
			return CFile::MakeFileArray($file);

		$i = 1;
		while(file_exists(preg_replace("#(\\.)([^./\\\\]+?)$#", ".resize".$i.".\\2", $file)))
			$i++;
		$new_file = preg_replace("#(\\.)([^./\\\\]+?)$#", ".resize".$i.".\\2", $file);

		if(!copy($file, $new_file))
			return CFile::MakeFileArray($file);

		$this->arTempFiles[] = $new_file;

		$resized = CIBlock::ResizePicture(CFile::MakeFileArray($new_file), array(
			"WIDTH" => $resize[0],
			"HEIGHT" => $resize[1],
		));

		if(is_array($resized))
			return $resized;
		else
			return array("tmp_name"=>"", "del"=>"Y");
	}

	function GetIBlockByXML_ID($XML_ID)
	{
		if(strlen($XML_ID) > 0)
		{
			$obIBlock = new CIBlock;
			$rsIBlock = $obIBlock->GetList(array(), array("XML_ID"=>$XML_ID, "CHECK_PERMISSIONS" => "N"));
			if($arIBlock = $rsIBlock->Fetch())
				return $arIBlock["ID"];
			else
				return false;
		}
		return false;
	}

	function GetSectionByXML_ID($IBLOCK_ID, $XML_ID)
	{
		if(!array_key_exists($IBLOCK_ID, $this->arSectionCache))
			$this->arSectionCache[$IBLOCK_ID] = array();
		if(!array_key_exists($XML_ID, $this->arSectionCache[$IBLOCK_ID]))
		{
			$obSection = new CIBlockSection;
			$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "EXTERNAL_ID"=>$XML_ID));
			if($arSection = $rsSection->Fetch())
				$this->arSectionCache[$IBLOCK_ID][$XML_ID] = $arSection["ID"];
			else
				$this->arSectionCache[$IBLOCK_ID][$XML_ID] = false;
		}
		return $this->arSectionCache[$IBLOCK_ID][$XML_ID];
	}

	function GetElementByXML_ID($IBLOCK_ID, $XML_ID)
	{
		if(strlen($XML_ID) <= 0)
			return false;
		if(!array_key_exists($IBLOCK_ID, $this->arElementCache))
			$this->arElementCache[$IBLOCK_ID] = array();
		if(!array_key_exists($XML_ID, $this->arElementCache[$IBLOCK_ID]))
		{
			$obElement = new CIBlockElement;
			$rsElement = $obElement->GetList(
					Array("ID"=>"asc"),
					Array("=XML_ID" => $XML_ID, "IBLOCK_ID" => $IBLOCK_ID),
					false, false,
					Array("ID", "XML_ID")
			);
			if($arElement = $rsElement->Fetch())
				$this->arElementCache[$IBLOCK_ID][$XML_ID] = $arElement["ID"];
			else
				$this->arElementCache[$IBLOCK_ID][$XML_ID] = false;
		}
		return $this->arElementCache[$IBLOCK_ID][$XML_ID];
	}

	function GetEnumByXML_ID($PROP_ID, $XML_ID)
	{
		if(!array_key_exists($PROP_ID, $this->arEnumCache))
			$this->arEnumCache[$PROP_ID] = array();
		if(!array_key_exists($XML_ID, $this->arEnumCache[$PROP_ID]))
		{
			$rsEnum = CIBlockPropertyEnum::GetList(
					Array(),
					Array("EXTERNAL_ID" => $XML_ID, "PROPERTY_ID" => $PROP_ID)
			);
			if($arEnum = $rsEnum->Fetch())
				$this->arEnumCache[$PROP_ID][$XML_ID] = $arEnum["ID"];
			else
				$this->arEnumCache[$PROP_ID][$XML_ID] = false;
		}
		return $this->arEnumCache[$PROP_ID][$XML_ID];
	}

	function GetPropertyByXML_ID($IBLOCK_ID, $XML_ID)
	{
		$obProperty = new CIBlockProperty;
		$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$XML_ID));
		if($arProperty = $rsProperty->Fetch())
			return $arProperty["ID"];
		else
			return false;
	}

	function CheckProperty($IBLOCK_ID, $code, $xml_name)
	{
		$obProperty = new CIBlockProperty;
		$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$code));
		if(!$rsProperty->Fetch())
		{
			$arProperty = array(
				"IBLOCK_ID" => $IBLOCK_ID,
				"NAME" => is_array($xml_name)? $xml_name["NAME"]: $xml_name,
				"CODE" => $code,
				"XML_ID" => $code,
				"MULTIPLE" => "N",
				"PROPERTY_TYPE" => "S",
				"ACTIVE" => "Y",
			);
			if(is_array($xml_name))
			{
				foreach($xml_name as $name => $value)
					$arProperty[$name] = $value;
			}
			$ID = $obProperty->Add($arProperty);
			if(!$ID)
				return $obProperty->LAST_ERROR;
		}
		return true;
	}

	function CheckTax($title, $rate)
	{
		$tax_name = $title." ".$rate."%";
		if(!array_key_exists($tax_name, $this->arTaxCache))
		{
			$rsVat = CCatalogVat::GetList(array('CSORT' => 'ASC'), array("NAME" => $tax_name, "NAME_EXACT_MATCH" => "Y", "RATE" => $rate), array("ID"));
			if($arVat = $rsVat->Fetch())
				$this->arTaxCache[$tax_name] = $arVat["ID"];
			else
				$this->arTaxCache[$tax_name] = CCatalogVat::Set(array(
					"ACTIVE" => "Y",
					"NAME" => $tax_name,
					"RATE" => $rate,
				));
		}
		return $this->arTaxCache[$tax_name];
	}

	function CheckCurrency($currency)
	{
		global $CML2_CURRENCY;

		if($currency==GetMessage("IBLOCK_XML2_RUB"))
		{
			$currency="RUB";
		}
		elseif(!preg_match("/^[a-zA-Z0-9]+$/", $currency))
		{
			if(
				is_array($CML2_CURRENCY)
				&& array_key_exists($currency, $CML2_CURRENCY)
				&& is_string($CML2_CURRENCY[$currency])
				&& preg_match("/^[a-zA-Z0-9]+$/", $CML2_CURRENCY[$currency])
			)
			{
				$currency = $CML2_CURRENCY[$currency];
			}
			else
			{
				$currency="RUB";
				$this->LAST_ERROR = GetMessage("IBLOCK_XML2_CURRENCY_ERROR");
			}
		}

		if(!array_key_exists($currency, $this->arCurrencyCache))
		{
			if($this->bCatalog && CModule::IncludeModule('currency'))
			{
				CCurrency::Add(array(
					"CURRENCY" => $currency,
				));
			}
			$this->arCurrencyCache[$currency] = true;
		}

		return $currency;
	}

	function CheckIBlockType($ID)
	{
		$obType = new CIBlockType;
		$rsType = $obType->GetByID($ID);
		if($arType = $rsType->Fetch())
		{
			return $arType["ID"];
		}
		else
		{
			$rsType = $obType->GetByID("1c_catalog");
			if($arType = $rsType->Fetch())
			{
				return $arType["ID"];
			}
			else
			{
				$result = $obType->Add(array(
					"ID" => "1c_catalog",
					"SECTIONS" => "Y",
					"LANG" => array(
						"ru" => array(
							"NAME" => GetMessage("IBLOCK_XML2_CATALOG_NAME"),
							"SECTION_NAME" => GetMessage("IBLOCK_XML2_CATALOG_SECTION_NAME"),
							"ELEMENT_NAME" => GetMessage("IBLOCK_XML2_CATALOG_ELEMENT_NAME"),
						),
					),
				));
				if($result)
					return $result;
				else
					return false;
			}
		}
	}

	function CheckSites($arSite)
	{
		$arResult = array();
		if(!is_array($arSite))
			$arSite = array($arSite);
		foreach($arSite as $site_id)
		{
			if(strlen($site_id) > 0)
			{
				$rsSite = CSite::GetByID($site_id);
				if($rsSite->Fetch())
					$arResult[] = $site_id;
			}
		}
		if(!defined("ADMIN_SECTION"))
		{
			$rsSite = CSite::GetByID(SITE_ID);
			if($rsSite->Fetch())
				$arResult[] = SITE_ID;
		}
		if(count($arResult)<1)
			$arResult[] = CSite::GetDefSite();
		return $arResult;
	}

	function ImportMetaData($xml_root_id, $IBLOCK_TYPE, $IBLOCK_LID, $bUpdateIBlock = true)
	{
		global $DB;

		$rs = $this->_xml_file->GetList(
			array(),
			array("ID" => $xml_root_id, "NAME" => GetMessage("IBLOCK_XML2_COMMERCE_INFO")),
			array("ID", "ATTRIBUTES")
		);
		$ar = $rs->Fetch();
		if($ar && (strlen($ar["ATTRIBUTES"]) > 0))
		{
			$info = unserialize($ar["ATTRIBUTES"]);
			if(is_array($info) && array_key_exists(GetMessage("IBLOCK_XML2_SUM_FORMAT"), $info))
			{
				if(preg_match("#".GetMessage("IBLOCK_XML2_SUM_FORMAT_DELIM")."=(.);{0,1}#", $info[GetMessage("IBLOCK_XML2_SUM_FORMAT")], $match))
				{
					$this->next_step["sdp"] = $match[1];
				}
			}
		}

		$meta_data_xml_id = false;
		$XML_ELEMENTS_PARENT = false;
		$XML_SECTIONS_PARENT = false;
		$XML_PROPERTIES_PARENT = false;
		$XML_PRICES_PARENT = false;

		$this->next_step["bOffer"] = false;
		$rs = $this->_xml_file->GetList(
			array(),
			array("PARENT_ID" => $xml_root_id, "NAME" => GetMessage("IBLOCK_XML2_CATALOG")),
			array("ID", "ATTRIBUTES")
		);
		$ar = $rs->Fetch();
		if(!$ar)
		{
			$rs = $this->_xml_file->GetList(
				array(),
				array("PARENT_ID" => $xml_root_id, "NAME" => GetMessage("IBLOCK_XML2_OFFER_LIST")),
				array("ID", "ATTRIBUTES")
			);
			$ar = $rs->Fetch();
			$this->next_step["bOffer"] = true;
		}

		if($ar)
		{
			if(strlen($ar["ATTRIBUTES"]) > 0)
			{
				$attrs = unserialize($ar["ATTRIBUTES"]);
				if(is_array($attrs))
				{
					if(array_key_exists(GetMessage("IBLOCK_XML2_UPDATE_ONLY"), $attrs))
						$this->next_step["bUpdateOnly"] = ($attrs[GetMessage("IBLOCK_XML2_UPDATE_ONLY")]=="true") || intval($attrs["IBLOCK_XML2_UPDATE_ONLY"])? true: false;
				}
			}

			//Information block fields with following Add/Update
			$arIBlock = array(
			);
			$rs = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $ar["ID"])
			);
			while($ar = $rs->Fetch())
			{
				if(isset($ar["VALUE_CLOB"]))
					$ar["VALUE"] = $ar["VALUE_CLOB"];
				if($ar["NAME"] == GetMessage("IBLOCK_XML2_ID"))
					$arIBlock["XML_ID"] = ($this->use_iblock_type_id? $IBLOCK_TYPE."-": "").$ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_CATALOG_ID"))
					$arIBlock["CATALOG_XML_ID"] = ($this->use_iblock_type_id? $IBLOCK_TYPE."-": "").$ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_NAME"))
					$arIBlock["NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_DESCRIPTION"))
				{
					$arIBlock["DESCRIPTION"] = $ar["VALUE"];
					$arIBlock["DESCRIPTION_TYPE"] = "html";
				}
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_POSITIONS") || $ar["NAME"] == GetMessage("IBLOCK_XML2_OFFERS"))
					$XML_ELEMENTS_PARENT = $ar["ID"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_PRICE_TYPES"))
					$XML_PRICES_PARENT = $ar["ID"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_METADATA_ID"))
					$meta_data_xml_id = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_UPDATE_ONLY"))
					$this->next_step["bUpdateOnly"] = ($ar["VALUE"]=="true") || intval($ar["VALUE"])? true: false;
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_CODE"))
					$arIBlock["CODE"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_SORT"))
					$arIBlock["SORT"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_LIST_URL"))
					$arIBlock["LIST_PAGE_URL"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_DETAIL_URL"))
					$arIBlock["DETAIL_PAGE_URL"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_SECTION_URL"))
					$arIBlock["SECTION_PAGE_URL"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_INDEX_ELEMENTS"))
					$arIBlock["INDEX_ELEMENT"] = ($ar["VALUE"]=="true") || intval($ar["VALUE"])? "Y": "N";
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_INDEX_SECTIONS"))
					$arIBlock["INDEX_SECTION"] = ($ar["VALUE"]=="true") || intval($ar["VALUE"])? "Y": "N";
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_SECTIONS_NAME"))
					$arIBlock["SECTIONS_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_SECTION_NAME"))
					$arIBlock["SECTION_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_ELEMENTS_NAME"))
					$arIBlock["ELEMENTS_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_ELEMENT_NAME"))
					$arIBlock["ELEMENT_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_PICTURE"))
					$arIBlock["PICTURE"] = $this->MakeFileArray($ar["VALUE"]);
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_BX_WORKFLOW"))
					$arIBlock["WORKFLOW"] = ($ar["VALUE"]=="true") || intval($ar["VALUE"])? "Y": "N";
				elseif($ar["NAME"] == GetMessage("IBLOCK_XML2_LABELS"))
				{
					$arLabels = $this->_xml_file->GetAllChildrenArray($ar["ID"]);
					foreach($arLabels as $key => $arLabel)
					{
						$id = $arLabel[GetMessage("IBLOCK_XML2_ID")];
						$label = $arLabel[GetMessage("IBLOCK_XML2_VALUE")];
						if(strlen($id) > 0 && strlen($label) > 0)
							$arIBlock[$id] = $label;
					}
				}
			}
			if($this->next_step["bOffer"] && !$this->use_offers)
			{
				if(strlen($arIBlock["CATALOG_XML_ID"]) > 0)
				{
					$arIBlock["XML_ID"] = $arIBlock["CATALOG_XML_ID"];
					$this->next_step["bUpdateOnly"] = true;
				}
			}

			$obIBlock = new CIBlock;
			$rsIBlocks = $obIBlock->GetList(array(), array("XML_ID"=>$arIBlock["XML_ID"]));
			$ar = $rsIBlocks->Fetch();

			//Also check for non bitrix xml file
			if(!$ar && !array_key_exists("CODE", $arIBlock))
			{
				if($this->next_step["bOffer"] && $this->use_offers)
					$rsIBlocks = $obIBlock->GetList(array(), array("XML_ID"=>"FUTURE-1C-OFFERS"));
				else
					$rsIBlocks = $obIBlock->GetList(array(), array("XML_ID"=>"FUTURE-1C-CATALOG"));
				$ar = $rsIBlocks->Fetch();
			}
			if($ar)
			{
				if($bUpdateIBlock && (!$this->next_step["bOffer"] || $this->use_offers))
				{
					if($obIBlock->Update($ar["ID"], $arIBlock))
						$arIBlock["ID"] = $ar["ID"];
					else
						return $obIBlock->LAST_ERROR;
				}
				else
				{
					$arIBlock["ID"] = $ar["ID"];
				}
			}
			else
			{
				$arIBlock["IBLOCK_TYPE_ID"] = $this->CheckIBlockType($IBLOCK_TYPE);
				if(!$arIBlock["IBLOCK_TYPE_ID"])
					return GetMessage("IBLOCK_XML2_TYPE_ADD_ERROR");
				$arIBlock["GROUP_ID"] = array(2=>"R");
				$arIBlock["LID"] = $this->CheckSites($IBLOCK_LID);
				$arIBlock["ACTIVE"] = "Y";
				$arIBlock["WORKFLOW"] = "N";
				$arIBlock["ID"] = $obIBlock->Add($arIBlock);
				if(!$arIBlock["ID"])
					return $obIBlock->LAST_ERROR;
			}

			//Make this catalog
			if($this->bCatalog && $this->next_step["bOffer"])
			{
				$intParentID = $this->GetIBlockByXML_ID($arIBlock["CATALOG_XML_ID"]);
				if (0 < intval($intParentID))
				{
					$rs = CCatalog::GetList(array(),array("IBLOCK_ID"=>$arIBlock["ID"]));
					if($arOffer = $rs->Fetch())
					{
						CCatalog::Update($arIBlock["ID"],array('OFFERS' => 'Y'));
					}
					else
					{
						CCatalog::Add(array("IBLOCK_ID"=>$arIBlock["ID"], "YANDEX_EXPORT"=>"N", "SUBSCRIPTION"=>"N",'OFFERS' => 'Y','OFFERS_IBLOCK_ID' => 0));
					}
				
					$rs = CCatalog::GetList(array(),array('IBLOCK_ID' => $intParentID));
					if ($arParent = $rs->Fetch())
					{
						CCatalog::Update($arParent['ID'],array('OFFERS_IBLOCK_ID' => $arIBlock["ID"]));
					}
					else
					{
						CCatalog::Add(array("IBLOCK_ID"=>$intParentID, "YANDEX_EXPORT"=>"N", "SUBSCRIPTION"=>"N",'OFFERS' => 'N','OFFERS_IBLOCK_ID' => $arIBlock["ID"]));
					}
				}
				else
				{
					$rs = CCatalog::GetList(array(),array("IBLOCK_ID"=>$arIBlock["ID"]));
					if(!($rs->Fetch()))
						CCatalog::Add(array("IBLOCK_ID"=>$arIBlock["ID"], "YANDEX_EXPORT"=>"N", "SUBSCRIPTION"=>"N",'OFFERS' => 'N','OFFERS_IBLOCK_ID' => '0'));
					
				}
			}

			//For non bitrix xml file
			//Check for mandatory properties and add them as necessary
			if(!array_key_exists("CODE", $arIBlock))
			{
				$arProperties = array(
					"CML2_BAR_CODE" => GetMessage("IBLOCK_XML2_BAR_CODE"),
					"CML2_ARTICLE" => GetMessage("IBLOCK_XML2_ARTICLE"),
					"CML2_ATTRIBUTES" => array(
						"NAME" => GetMessage("IBLOCK_XML2_ATTRIBUTES"),
						"MULTIPLE" => "Y",
						"WITH_DESCRIPTION" => "Y",
						"MULTIPLE_CNT" => 1,
					),
					"CML2_TRAITS" => array(
						"NAME" => GetMessage("IBLOCK_XML2_TRAITS"),
						"MULTIPLE" => "Y",
						"WITH_DESCRIPTION" => "Y",
						"MULTIPLE_CNT" => 1,
					),
					"CML2_BASE_UNIT" => GetMessage("IBLOCK_XML2_BASE_UNIT_NAME"),
					"CML2_TAXES" => array(
						"NAME" => GetMessage("IBLOCK_XML2_TAXES"),
						"MULTIPLE" => "Y",
						"WITH_DESCRIPTION" => "Y",
						"MULTIPLE_CNT" => 1,
					),
					"CML2_PICTURES" => array(
						"NAME" => GetMessage("IBLOCK_XML2_PICTURES"),
						"MULTIPLE" => "Y",
						"WITH_DESCRIPTION" => "Y",
						"MULTIPLE_CNT" => 1,
						"PROPERTY_TYPE" => "F",
						"CODE" => "MORE_PHOTO",
					),
					"CML2_FILES" => array(
						"NAME" => GetMessage("IBLOCK_XML2_FILES"),
						"MULTIPLE" => "Y",
						"WITH_DESCRIPTION" => "Y",
						"MULTIPLE_CNT" => 1,
						"PROPERTY_TYPE" => "F",
						"CODE" => "FILES",
					),
				);
				foreach($arProperties as $k=>$v)
				{
					$result = $this->CheckProperty($arIBlock["ID"], $k, $v);
					if($result!==true)
						return $result;
				}
				//For offers make special property: link to catalog
				if(isset($arIBlock["CATALOG_XML_ID"]) && $this->use_offers)
					$result = $this->CheckProperty($arIBlock["ID"], "CML2_LINK", array(
						"NAME" => GetMessage("IBLOCK_XML2_CATALOG_ELEMENT"),
						"PROPERTY_TYPE" => "E",
						"LINK_IBLOCK_ID" => $this->GetIBlockByXML_ID($arIBlock["CATALOG_XML_ID"]),
						"FILTRABLE" => "Y",
					));
			}

			$this->next_step["IBLOCK_ID"] = $arIBlock["ID"];
			$this->next_step["XML_ELEMENTS_PARENT"] = $XML_ELEMENTS_PARENT;

		}

		if($meta_data_xml_id)
		{
			$rs = $this->_xml_file->GetList(
				array(),
				array("PARENT_ID" => $xml_root_id, "NAME" => GetMessage("IBLOCK_XML2_METADATA")),
				array("ID")
			);
			while($arMetadata = $rs->Fetch())
			{
				//Find referenced metadata
				$bMetaFound = false;
				$meta_roots = array();
				$rsMetaRoots = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $arMetadata["ID"])
				);
				while($arMeta = $rsMetaRoots->Fetch())
				{
					if(isset($arMeta["VALUE_CLOB"]))
						$arMeta["VALUE"] = $arMeta["VALUE_CLOB"];
					if($arMeta["NAME"] == GetMessage("IBLOCK_XML2_ID") && $arMeta["VALUE"] == $meta_data_xml_id)
						$bMetaFound = true;
					$meta_roots[] = $arMeta;
				}
				//Get xml parents of the properties and sections
				if($bMetaFound)
				{
					foreach($meta_roots as $arMeta)
					{
						if($arMeta["NAME"] == GetMessage("IBLOCK_XML2_GROUPS"))
							$XML_SECTIONS_PARENT = $arMeta["ID"];
						elseif($arMeta["NAME"] == GetMessage("IBLOCK_XML2_PROPERTIES"))
							$XML_PROPERTIES_PARENT = $arMeta["ID"];
					}
					break;
				}
			}
		}

		if($XML_PROPERTIES_PARENT)
		{
			$result = $this->ImportProperties($XML_PROPERTIES_PARENT, $arIBlock["ID"]);
			if($result!==true)
				return $result;
		}

		if($XML_PRICES_PARENT)
		{
			if($this->bCatalog)
			{
				$result = $this->ImportPrices($XML_PRICES_PARENT, $arIBlock["ID"], $IBLOCK_LID);
				if($result!==true)
					return $result;
			}
		}

		$this->next_step["section_sort"] = 100;
		$this->next_step["XML_SECTIONS_PARENT"] = $XML_SECTIONS_PARENT;

		return true;
	}

	function ImportSections()
	{
		global $DB;

		if($this->next_step["XML_SECTIONS_PARENT"])
		{
			$rs = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $this->next_step["XML_SECTIONS_PARENT"]),
				array("ID")
			);
			while($ar = $rs->Fetch())
				$this->ImportSection($ar["ID"], $this->next_step["IBLOCK_ID"], false);
		}
	}

	function DeactivateSections($action)
	{
		global $DB;

		if(array_key_exists("bUpdateOnly", $this->next_step) && $this->next_step["bUpdateOnly"])
			return;

		if($action!="D" && $action!="A")
			return;

		$bDelete = $action=="D";

		//This will protect us from deactivating when next_step is lost
		$IBLOCK_ID = intval($this->next_step["IBLOCK_ID"]);
		if($IBLOCK_ID < 1)
			return $counter;

		$arFilter = array(
			"IBLOCK_ID" => $IBLOCK_ID,
		);
		if(!$bDelete)
			$arFilter["ACTIVE"] = "Y";

		$obSection = new CIBlockSection;
		$rsSection = $obSection->GetList(array("ID"=>"asc"), $arFilter);

		while($arSection = $rsSection->Fetch())
		{
			$rs = $this->_xml_file->GetList(
				array(),
				array("PARENT_ID+0" => 0, "LEFT_MARGIN" => $arSection["ID"]),
				array("ID")
			);
			$ar = $rs->Fetch();
			if(!$ar)
			{
				if($bDelete)
				{
					$obSection->Delete($arSection["ID"]);
				}
				else
				{
					$obSection->Update($arSection["ID"], array("ACTIVE"=>"N"));
				}
			}
			else
			{
				$this->_xml_file->Delete($ar["ID"]);
			}
		}
		return;
	}

	function SectionsResort()
	{
		CIBlockSection::ReSort($this->next_step["IBLOCK_ID"]);
	}

	function ImportPrices($XML_PRICES_PARENT, $IBLOCK_ID, $IBLOCK_LID)
	{
		$price_sort = 0;
		$this->next_step["XML_PRICES_PARENT"] = $XML_PRICES_PARENT;

		$arLang = array();
		foreach($IBLOCK_LID as $site_id)
		{
			$rsSite = CSite::GetList($by = "sort",$order = "asc", array("ID" => $site_id));
			while ($site = $rsSite->Fetch())
				$arLang[$site["LANGUAGE_ID"]] = $site["LANGUAGE_ID"];
		}

		$arXMLPrices = $this->_xml_file->GetAllChildrenArray($XML_PRICES_PARENT);
		foreach($arXMLPrices as $key => $arXMLPrice)
		{
			$PRICE_NAME = $arXMLPrice[GetMessage("IBLOCK_XML2_NAME")];
			$rsPrice = CCatalogGroup::GetList(array(), array("NAME"=>$PRICE_NAME));
			if(!$rsPrice->Fetch())
			{
				$price_sort += 100;
				$arPrice = array(
					"NAME" => $PRICE_NAME,
					"XML_ID" => $arXMLPrice[GetMessage("IBLOCK_XML2_ID")],
					"SORT" => $price_sort,
					"USER_LANG" => array(),
					"USER_GROUP" => array(2),
					"USER_GROUP_BUY" => array(2),
				);
				foreach($arLang as $lang)
				{
					$arPrice["USER_LANG"][$lang] = $arXMLPrice[GetMessage("IBLOCK_XML2_NAME")];
				}
				$ID = CCatalogGroup::Add($arPrice);
			}
		}
		return true;
	}

	function ImportProperties($XML_PROPERTIES_PARENT, $IBLOCK_ID)
	{
		global $DB;
		$obProperty = new CIBlockProperty;
		$sort = 100;

		$arElementFields = array(
			"CML2_ACTIVE" => GetMessage("IBLOCK_XML2_BX_ACTIVE"),
			"CML2_CODE" => GetMessage("IBLOCK_XML2_SYMBOL_CODE"),
			"CML2_SORT" => GetMessage("IBLOCK_XML2_SORT"),
			"CML2_ACTIVE_FROM" => GetMessage("IBLOCK_XML2_START_TIME"),
			"CML2_ACTIVE_TO" => GetMessage("IBLOCK_XML2_END_TIME"),
			"CML2_PREVIEW_TEXT" => GetMessage("IBLOCK_XML2_ANONS"),
			"CML2_DETAIL_TEXT" => GetMessage("IBLOCK_XML2_DETAIL"),
			"CML2_PREVIEW_PICTURE" => GetMessage("IBLOCK_XML2_PREVIEW_PICTURE"),
		);

		$rs = $this->_xml_file->GetList(
			array("ID" => "asc"),
			array("PARENT_ID" => $XML_PROPERTIES_PARENT),
			array("ID")
		);
		while($ar = $rs->Fetch())
		{
			$XML_ENUM_PARENT = false;
			$arProperty = array(
			);
			$rsP = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $ar["ID"])
			);
			while($arP = $rsP->Fetch())
			{
				if(isset($arP["VALUE_CLOB"]))
					$arP["VALUE"] = $arP["VALUE_CLOB"];
				if($arP["NAME"]==GetMessage("IBLOCK_XML2_ID"))
				{
					$arProperty["XML_ID"] = $arP["VALUE"];
					if(array_key_exists($arProperty["XML_ID"], $arElementFields))
						break;
				}
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_NAME"))
					$arProperty["NAME"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_MULTIPLE"))
					$arProperty["MULTIPLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_SORT"))
					$arProperty["SORT"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_CODE"))
					$arProperty["CODE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_DEFAULT_VALUE"))
					$arProperty["DEFAULT_VALUE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_SERIALIZED"))
					$arProperty["SERIALIZED"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_PROERTY_TYPE"))
					$arProperty["PROPERTY_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_ROWS"))
					$arProperty["ROW_COUNT"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_COLUMNS"))
					$arProperty["COL_COUNT"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_LIST_TYPE"))
					$arProperty["LIST_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_FILE_EXT"))
					$arProperty["FILE_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_FIELDS_COUNT"))
					$arProperty["MULTIPLE_CNT"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_USER_TYPE"))
					$arProperty["USER_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_WITH_DESCRIPTION"))
					$arProperty["WITH_DESCRIPTION"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_SEARCH"))
					$arProperty["SEARCHABLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_FILTER"))
					$arProperty["FILTRABLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_LINKED_IBLOCK"))
					$arProperty["LINK_IBLOCK_ID"] = $this->GetIBlockByXML_ID($arP["VALUE"]);
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_CHOICE_VALUES"))
					$XML_ENUM_PARENT = $arP["ID"];
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_BX_IS_REQUIRED"))
					$arProperty["IS_REQUIRED"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==GetMessage("IBLOCK_XML2_VALUES_TYPES"))
				{
					//This property metadata contains information about it's type
					$rsTypes = $this->_xml_file->GetList(
						array("ID" => "asc"),
						array("PARENT_ID" => $arP["ID"]),
						array("ID", "LEFT_MARGIN", "RIGHT_MARGIN", "NAME")
					);
					$arType = $rsTypes->Fetch();
					//We'll process only properties with NOT composing types
					//composed types will be supported only as simple string properties
					if($arType && !$rsTypes->Fetch())
					{
						$rsType = $this->_xml_file->GetList(
							array("ID" => "asc"),
							array("PARENT_ID" => $arType["ID"]),
							array("ID", "LEFT_MARGIN", "RIGHT_MARGIN", "NAME", "VALUE")
						);
						while($arType = $rsType->Fetch())
						{
							if($arType["NAME"] == GetMessage("IBLOCK_XML2_TYPE"))
							{
								if($arType["VALUE"] == GetMessage("IBLOCK_XML2_TYPE_LIST"))
									$arProperty["PROPERTY_TYPE"] = "L";
								elseif($arType["VALUE"] == GetMessage("IBLOCK_XML2_TYPE_NUMBER"))
									$arProperty["PROPERTY_TYPE"] = "N";
							}
							elseif($arType["NAME"] == GetMessage("IBLOCK_XML2_CHOICE_VALUES"))
							{
								$XML_ENUM_PARENT = $arType["ID"];
							}
						}
					}
				}
			}

			if(array_key_exists($arProperty["XML_ID"], $arElementFields))
				continue;

			if($arProperty["SERIALIZED"] == "Y")
				$arProperty["DEFAULT_VALUE"] = unserialize($arProperty["DEFAULT_VALUE"]);

			$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$arProperty["XML_ID"]));
			if($arDBProperty = $rsProperty->Fetch())
			{
				$bChanged = false;
				foreach($arProperty as $key=>$value)
				{
					if($arDBProperty[$key] !== $value)
					{
						$bChanged = true;
						break;
					}
				}
				if(!$bChanged)
					$arProperty["ID"] = $arDBProperty["ID"];
				elseif($obProperty->Update($arDBProperty["ID"], $arProperty))
					$arProperty["ID"] = $arDBProperty["ID"];
				else
					return $obProperty->LAST_ERROR;
			}
			else
			{
				$arProperty["IBLOCK_ID"] = $IBLOCK_ID;
				$arProperty["ACTIVE"] = "Y";
				if(!array_key_exists("PROPERTY_TYPE", $arProperty))
					$arProperty["PROPERTY_TYPE"] = "S";
				if(!array_key_exists("SORT", $arProperty))
					$arProperty["SORT"] = $sort;
				$arProperty["ID"] = $obProperty->Add($arProperty);
				if(!$arProperty["ID"])
					return $obProperty->LAST_ERROR;
			}

			if($XML_ENUM_PARENT)
			{
				$arEnumMap = array();
				$arProperty["VALUES"] = array();
				$rsEnum = CIBlockProperty::GetPropertyEnum($arProperty["ID"]);
				while($arEnum = $rsEnum->Fetch())
				{
					$arProperty["VALUES"][$arEnum["ID"]] = $arEnum;
					$arEnumMap[$arEnum["XML_ID"]] = &$arProperty["VALUES"][$arEnum["ID"]];
				}
				$rsE = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $XML_ENUM_PARENT)
				);
				$i = 0;
				while($arE = $rsE->Fetch())
				{
					if(isset($arE["VALUE_CLOB"]))
						$arE["VALUE"] = $arE["VALUE_CLOB"];
					if(
						$arE["NAME"] == GetMessage("IBLOCK_XML2_CHOICE")
						|| $arE["NAME"] == GetMessage("IBLOCK_XML2_CHOICE_VALUE")
					)
					{
						$arE = $this->_xml_file->GetAllChildrenArray($arE);
						if(isset($arE[GetMessage("IBLOCK_XML2_ID")]))
						{
							$xml_id = $arE[GetMessage("IBLOCK_XML2_ID")];
							if(!array_key_exists($xml_id, $arEnumMap))
							{
								$arProperty["VALUES"]["n".$i] = array();
								$arEnumMap[$xml_id] = &$arProperty["VALUES"]["n".$i];
								$i++;
							}
							$arEnumMap[$xml_id]["CML2_EXPORT_FLAG"] = true;
							$arEnumMap[$xml_id]["XML_ID"] = $xml_id;
							if(isset($arE[GetMessage("IBLOCK_XML2_VALUE")]))
								$arEnumMap[$xml_id]["VALUE"] = $arE[GetMessage("IBLOCK_XML2_VALUE")];
							if(isset($arE[GetMessage("IBLOCK_XML2_BY_DEFAULT")]))
								$arEnumMap[$xml_id]["DEF"] = ($arE[GetMessage("IBLOCK_XML2_BY_DEFAULT")]=="true") || intval($arE[GetMessage("IBLOCK_XML2_BY_DEFAULT")])? "Y": "N";
							if(isset($arE[GetMessage("IBLOCK_XML2_SORT")]))
								$arEnumMap[$xml_id]["SORT"] = intval($arE[GetMessage("IBLOCK_XML2_SORT")]);
						}
					}
				}

				$bUpdateOnly = array_key_exists("bUpdateOnly", $this->next_step) && $this->next_step["bUpdateOnly"];
				$sort = 100;

				foreach($arProperty["VALUES"] as $id=>$arEnum)
				{
					if(!isset($arEnum["CML2_EXPORT_FLAG"]))
					{
						//Delete value only when full exchange happend
						if(!$bUpdateOnly)
							$arProperty["VALUES"][$id]["VALUE"] = "";
					}
					elseif(isset($arEnum["SORT"]))
					{
						if($arEnum["SORT"] > $sort)
							$sort = $arEnum["SORT"] + 100;
					}
					else
					{
						$arProperty["VALUES"][$id]["SORT"] = $sort;
						$sort += 100;
					}
				}
				$obProperty->UpdateEnum($arProperty["ID"], $arProperty["VALUES"]);
			}
			$sort += 100;
		}
		return true;
	}

	function ReadCatalogData(&$SECTION_MAP, &$PRICES_MAP)
	{
		global $DB;

		if(!is_array($SECTION_MAP))
		{
			$SECTION_MAP = array();
			$obSection = new CIBlockSection;
			$rsSections = $obSection->GetList(array(), array("IBLOCK_ID"=>$this->next_step["IBLOCK_ID"]), false);
			while($ar = $rsSections->Fetch())
				$SECTION_MAP[$ar["XML_ID"]] = $ar["ID"];
		}
		$this->SECTION_MAP = $SECTION_MAP;

		if(!is_array($PRICES_MAP))
		{
			$PRICES_MAP = array();
			if(isset($this->next_step["XML_PRICES_PARENT"]))
			{
				$rs = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $this->next_step["XML_PRICES_PARENT"])
				);
				while($arParent = $rs->Fetch())
				{
					if(isset($arParent["VALUE_CLOB"]))
						$arParent["VALUE"] = $arParent["VALUE_CLOB"];
					$arXMLPrice = $this->_xml_file->GetAllChildrenArray($arParent);
					$arPrice = array(
						"NAME" => $arXMLPrice[GetMessage("IBLOCK_XML2_NAME")],
						"XML_ID" => $arXMLPrice[GetMessage("IBLOCK_XML2_ID")],
						"CURRENCY" => $arXMLPrice[GetMessage("IBLOCK_XML2_CURRENCY")],
						"TAX_NAME" => $arXMLPrice[GetMessage("IBLOCK_XML2_TAX")][GetMessage("IBLOCK_XML2_NAME")],
						"TAX_IN_SUM" => $arXMLPrice[GetMessage("IBLOCK_XML2_TAX")][GetMessage("IBLOCK_XML2_IN_SUM")],
					);
					if($this->bCatalog)
					{
						$rsPrice = CCatalogGroup::GetList(array(), array("NAME"=>$arPrice["NAME"]));
						if($ar = $rsPrice->Fetch())
							$arPrice["ID"] = $ar["ID"];
						else
							$arPrice["ID"] = 0;
					}
					else
					{
						$obProperty = new CIBlockProperty;
						$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$this->next_step["IBLOCK_ID"], "XML_ID"=>$arPrice["XML_ID"]));
						if($ar = $rsProperty->Fetch())
							$arPrice["ID"] = $ar["ID"];
						else
							$arPrice["ID"] = 0;
					}
					$PRICES_MAP[$arPrice["XML_ID"]] = $arPrice;
				}
			}
		}
		$this->PRICES_MAP = $PRICES_MAP;
	}

	function GetElementCRC($arElement)
	{
		if(!is_array($arElement))
		{
			$parent_id = $arElement;
			$rsElement = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $parent_id)
			);
			$arElement = array();
			while($ar = $rsElement->Fetch())
				$arElement[] = array(
					$ar["NAME"],
					$ar["VALUE"],
					$ar["VALUE_CLOB"],
					$ar["ATTRIBUTES"],
				);
		}
		$c = crc32(print_r($arElement, true));
		if($c > 0x7FFFFFFF)
			$c = -(0xFFFFFFFF - $c + 1);
		return $c;
	}

	function ImportElements($start_time, $interval)
	{
		global $DB;

		$counter = array(
			"ADD" => 0,
			"UPD" => 0,
			"DEL" => 0,
			"DEA" => 0,
			"ERR" => 0,
		);
		if($this->next_step["XML_ELEMENTS_PARENT"])
		{
			$obElement = new CIBlockElement();
			$obElement->CancelWFSetMove();
			$bWF = CModule::IncludeModule("workflow");
			$rsParents = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $this->next_step["XML_ELEMENTS_PARENT"], ">ID" => $this->next_step["XML_LAST_ID"]),
				array("ID", "LEFT_MARGIN", "RIGHT_MARGIN")
			);
			while($arParent = $rsParents->Fetch())
			{
				$arXMLElement = $this->_xml_file->GetAllChildrenArray($arParent);
				if(!$this->next_step["bOffer"] && $this->use_offers)
				{
					$p = strrpos($arXMLElement[GetMessage("IBLOCK_XML2_ID")], "#");
					if($p !== false)
						continue;
				}
				if(array_key_exists(GetMessage("IBLOCK_XML2_STATUS"), $arXMLElement) && ($arXMLElement[GetMessage("IBLOCK_XML2_STATUS")] == GetMessage("IBLOCK_XML2_DELETED")))
				{
					$ID = $this->GetElementByXML_ID($this->next_step["IBLOCK_ID"], $arXMLElement[GetMessage("IBLOCK_XML2_ID")]);
					if($ID && $obElement->Update($ID, array("ACTIVE"=>"N"), $bWF))
					{
						if($this->use_offers)
							$this->ChangeOffersStatus($ID, "N", $bWF);
						$counter["DEA"]++;
					}
					else
					{
						$counter["ERR"]++;
					}
				}
				else
				{
					if($this->next_step["bOffer"] && !$this->use_offers)
						$ID = $this->ImportElementPrices($arXMLElement, $counter, $bWF);
					else
						$ID = $this->ImportElement($arXMLElement, $counter, $bWF, $arParent);
				}

				if($ID)
					$this->_xml_file->Add(array("PARENT_ID" => 0, "LEFT_MARGIN" => $ID));

				$this->next_step["XML_LAST_ID"] = $arParent["ID"];

				if($interval > 0 && (time()-$start_time) > $interval)
					break;
			}
		}
		$this->CleanTempFiles();
		return $counter;
	}

	function ChangeOffersStatus($ELEMENT_ID, $STATUS = "Y", $bWF = true)
	{
		if($this->arLinkedProps === false)
		{
			$this->arLinkedProps = array();
			$obProperty = new CIBlockProperty;
			$rsProperty = $obProperty->GetList(array(), array("LINK_IBLOCK_ID"=>$this->next_step["IBLOCK_ID"], "XML_ID"=>"CML2_LINK"));
			while($arProperty = $rsProperty->Fetch())
				$this->arLinkedProps[] = $arProperty;
		}
		$obElement = new CIBlockElement;
		$obElement->CancelWFSetMove();
		foreach($this->arLinkedProps as $arProperty)
		{
			$rsElements = $obElement->GetList(
				Array("ID"=>"asc"),
				Array(
					"PROPERTY_".$arProperty["ID"] => $ELEMENT_ID,
					"IBLOCK_ID" => $arProperty["IBLOCK_ID"],
					"ACTIVE" => $STATUS=="Y"? "N": "Y",
				),
				false, false,
				Array("ID", "TMP_ID")
			);
			while($arElement = $rsElements->Fetch())
				$obElement->Update($arElement["ID"], array("ACTIVE"=>$STATUS), $bWF);
		}
	}

	function ToFloat($str)
	{
		static $search = false;
		static $replace = false;
		if(!$search)
		{
			if(strlen($this->next_step["sdp"]))
			{
				$search = array("\xa0", " ", $this->next_step["sdp"], ",");
				$replace = array("", "", ".", ".");
			}
			else
			{
				$search = array("\xa0", " ", ",");
				$replace = array("", "", ".");
			}
		}

		$res1 = str_replace($search, $replace, $str);
		$res2 = doubleval($res1);

		return $res2;
	}

	function ToInt($str)
	{
		static $search = false;
		static $replace = false;
		if(!$search)
		{
			if(strlen($this->next_step["sdp"]))
			{
				$search = array("\xa0", " ", $this->next_step["sdp"], ",");
				$replace = array("", "", ".", ".");
			}
			else
			{
				$search = array("\xa0", " ", ",");
				$replace = array("", "", ".");
			}
		}

		$res1 = str_replace($search, $replace, $str);
		$res2 = intval($res1);

		return $res2;
	}

	function ImportElement($arXMLElement, &$counter, $bWF, $arParent)
	{
		global $USER;

		$arElement = array(
			"ACTIVE" => "Y",
			"TMP_ID" => $this->GetElementCRC($arParent["ID"]),
			"PROPERTY_VALUES" => array(),
		);
		if(isset($arXMLElement[GetMessage("IBLOCK_XML2_ID")]))
			$arElement["XML_ID"] = $arXMLElement[GetMessage("IBLOCK_XML2_ID")];

		$obElement = new CIBlockElement;
		$obElement->CancelWFSetMove();
		$rsElement = $obElement->GetList(
			Array("ID"=>"asc"),
			Array("=XML_ID" => $arElement["XML_ID"], "IBLOCK_ID" => $this->next_step["IBLOCK_ID"]),
			false, false,
			Array("ID", "TMP_ID", "ACTIVE")
		);

		$bMatch = false;
		if($arDBElement = $rsElement->Fetch())
 			$bMatch = ($arElement["TMP_ID"] == $arDBElement["TMP_ID"]);

		if($bMatch && $this->use_crc)
		{
			//In case element is not active in database we have to activate it and its offers
			if($arDBElement["ACTIVE"] != "Y")
			{
				$obElement->Update($arDBElement["ID"], array("ACTIVE"=>"Y"), $bWF);
				$this->ChangeOffersStatus($arDBElement["ID"], "Y", $bWF);
			}
			$arElement["ID"] = $arDBElement["ID"];
			$counter["UPD"]++;
		}
		else
		{
			if($arDBElement)
			{
				$rsProperties = $obElement->GetProperty($this->next_step["IBLOCK_ID"], $arDBElement["ID"], "sort", "asc");
				while($arProperty = $rsProperties->Fetch())
				{
					if(!array_key_exists($arProperty["ID"], $arElement["PROPERTY_VALUES"]))
						$arElement["PROPERTY_VALUES"][$arProperty["ID"]] = array(
							"bOld" => true,
						);

					$arElement["PROPERTY_VALUES"][$arProperty["ID"]][$arProperty['PROPERTY_VALUE_ID']] = array(
						"VALUE"=>$arProperty['VALUE'],
						"DESCRIPTION"=>$arProperty["DESCRIPTION"]
					);
				}
			}

			if($this->bCatalog && $this->next_step["bOffer"])
			{
				$p = strpos($arXMLElement[GetMessage("IBLOCK_XML2_ID")], "#");
				if($p !== false)
					$link_xml_id = substr($arXMLElement[GetMessage("IBLOCK_XML2_ID")], 0, $p);
				else
					$link_xml_id = $arXMLElement[GetMessage("IBLOCK_XML2_ID")];
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_LINK"]] = $this->GetElementByXML_ID($this->arProperties[$this->PROPERTY_MAP["CML2_LINK"]]["LINK_IBLOCK_ID"], $link_xml_id);
			}

			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_NAME")]))
				$arElement["NAME"] = $arXMLElement[GetMessage("IBLOCK_XML2_NAME")];
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_BX_TAGS")]))
				$arElement["TAGS"] = $arXMLElement[GetMessage("IBLOCK_XML2_BX_TAGS")];
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_DESCRIPTION")]))
			{
				$arElement["DETAIL_TEXT"] = $arXMLElement[GetMessage("IBLOCK_XML2_DESCRIPTION")];
				if(preg_match('/<[a-zA-Z0-9]+.*?>/', $arElement["DETAIL_TEXT"]))
					$arElement["DETAIL_TEXT_TYPE"] = "html";
				else
					$arElement["DETAIL_TEXT_TYPE"] = "text";
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_FULL_TITLE")]))
			{
				$arElement["PREVIEW_TEXT"] = $arXMLElement[GetMessage("IBLOCK_XML2_FULL_TITLE")];
				if(preg_match('/<[a-zA-Z0-9]+.*?>/', $arElement["PREVIEW_TEXT"]))
					$arElement["PREVIEW_TEXT_TYPE"] = "html";
				else
					$arElement["PREVIEW_TEXT_TYPE"] = "text";
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_BAR_CODE")]))
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BAR_CODE"]] = $arXMLElement[GetMessage("IBLOCK_XML2_BAR_CODE")];
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_BAR_CODE2")]))
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BAR_CODE"]] = $arXMLElement[GetMessage("IBLOCK_XML2_BAR_CODE2")];
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_ARTICLE")]))
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_ARTICLE"]] = $arXMLElement[GetMessage("IBLOCK_XML2_ARTICLE")];

			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_PICTURE")]))
			{
				$rsFiles = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $arParent["ID"], "NAME" => GetMessage("IBLOCK_XML2_PICTURE"))
				);
				$arFile = $rsFiles->Fetch();
				if($arFile)
				{
					$description = false;
					if(strlen($arFile["ATTRIBUTES"]))
					{
						$arAttributes = unserialize($arFile["ATTRIBUTES"]);
						if(is_array($arAttributes) && array_key_exists(GetMessage("IBLOCK_XML2_DESCRIPTION"), $arAttributes))
							$description = $arAttributes[GetMessage("IBLOCK_XML2_DESCRIPTION")];
					}

					$arElement["DETAIL_PICTURE"] = $this->ResizePicture($arFile["VALUE"], $this->detail);
					if($description !== false && is_array($arElement["DETAIL_PICTURE"]))
						$arElement["DETAIL_PICTURE"]["description"] = $description;

					$arElement["PREVIEW_PICTURE"] = $this->ResizePicture($arElement["DETAIL_PICTURE"]["tmp_name"], $this->preview);
					if($description !== false && is_array($arElement["PREVIEW_PICTURE"]))
						$arElement["PREVIEW_PICTURE"]["description"] = $description;

					$prop_id = $this->PROPERTY_MAP["CML2_PICTURES"];
					if($prop_id > 0)
					{
						$i = 1;
						while($arFile = $rsFiles->Fetch())
						{
							$description = false;
							if(strlen($arFile["ATTRIBUTES"]))
							{
								$arAttributes = unserialize($arFile["ATTRIBUTES"]);
								if(is_array($arAttributes) && array_key_exists(GetMessage("IBLOCK_XML2_DESCRIPTION"), $arAttributes))
									$description = $arAttributes[GetMessage("IBLOCK_XML2_DESCRIPTION")];
							}

							$arFile = $this->ResizePicture($arFile["VALUE"], $this->detail);
							if($description !== false && is_array($arFile))
								$arFile = array(
									"VALUE" => $arFile,
									"DESCRIPTION" => $description,
								);
							$arElement["PROPERTY_VALUES"][$prop_id]["n".$i] = $arFile;
							$i++;
						}


						if(is_array($arElement["PROPERTY_VALUES"][$prop_id]))
						{
							foreach($arElement["PROPERTY_VALUES"][$prop_id] as $PROPERTY_VALUE_ID => $PROPERTY_VALUE)
							{
								if(!$PROPERTY_VALUE_ID)
									unset($arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID]);
								elseif(substr($PROPERTY_VALUE_ID, 0, 1)!=="n")
									$arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID] = array(
										"tmp_name" => "",
										"del" => "Y",
									);
							}
							unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
						}
					}
				}
			}

			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_FILE")]))
			{
				$prop_id = $this->PROPERTY_MAP["CML2_FILES"];
				$rsFiles = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $arParent["ID"], "NAME" => GetMessage("IBLOCK_XML2_FILE"))
				);
				$i = 1;
				while($arFile = $rsFiles->Fetch())
				{
					$arElement["PROPERTY_VALUES"][$prop_id]["n".$i] = array(
						"VALUE" => $this->MakeFileArray($arFile["VALUE"]),
						"DESCRIPTION" => "",
					);
					if(strlen($arFile["ATTRIBUTES"]))
					{
						$desc = unserialize($arFile["ATTRIBUTES"]);
						if(is_array($desc) && array_key_exists(GetMessage("IBLOCK_XML2_DESCRIPTION"), $desc))
							$arElement["PROPERTY_VALUES"][$prop_id]["n".$i]["DESCRIPTION"] = $desc[GetMessage("IBLOCK_XML2_DESCRIPTION")];
					}
					$i++;
				}
				if(is_array($arElement["PROPERTY_VALUES"][$prop_id]))
				{
					foreach($arElement["PROPERTY_VALUES"][$prop_id] as $PROPERTY_VALUE_ID => $PROPERTY_VALUE)
					{
						if(!$PROPERTY_VALUE_ID)
							unset($arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID]);
						elseif(substr($PROPERTY_VALUE_ID, 0, 1)!=="n")
							$arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID] = array(
								"tmp_name" => "",
								"del" => "Y",
							);
					}
					unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
				}
			}

			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_GROUPS")]))
			{
				$arElement["IBLOCK_SECTION"] = array();
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_GROUPS")] as $key=>$value)
				{
					if(array_key_exists($value, $this->SECTION_MAP))
						$arElement["IBLOCK_SECTION"][] = $this->SECTION_MAP[$value];
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_PRICES")]))
			{//Collect price information for future use
				$arElement["PRICES"] = array();
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_PRICES")] as $key=>$price)
				{
					if(isset($price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")]) && array_key_exists($price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")], $this->PRICES_MAP))
					{
						$price["PRICE"] = $this->PRICES_MAP[$price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")]];
						$arElement["PRICES"][] = $price;
					}
				}

				$arElement["DISCOUNTS"] = array();
				if(isset($arXMLElement[GetMessage("IBLOCK_XML2_DISCOUNTS")]))
				{
					foreach($arXMLElement[GetMessage("IBLOCK_XML2_DISCOUNTS")] as $key=>$discount)
					{
						if(
							isset($discount[GetMessage("IBLOCK_XML2_DISCOUNT_CONDITION")])
							&& $discount[GetMessage("IBLOCK_XML2_DISCOUNT_CONDITION")]===GetMessage("IBLOCK_XML2_DISCOUNT_COND_VOLUME")
						)
						{
							$discount_value = $this->ToInt($discount[GetMessage("IBLOCK_XML2_DISCOUNT_COND_VALUE")]);
							$discount_percent = $this->ToFloat($discount[GetMessage("IBLOCK_XML2_DISCOUNT_COND_PERCENT")]);
							if($discount_value > 0 && $discount_percent > 0)
								$arElement["DISCOUNTS"][$discount_value] = $discount_percent;
						}
					}
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_AMOUNT")]))
				$arElement["QUANTITY"] = intval($arXMLElement[GetMessage("IBLOCK_XML2_AMOUNT")]);
			else
				$arElement["QUANTITY"] = 0;

			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_ITEM_ATTRIBUTES")]))
			{
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_ATTRIBUTES"]] = array();
				$i = 0;
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_ITEM_ATTRIBUTES")] as $key => $value)
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_ATTRIBUTES"]]["n".$i] = array(
						"VALUE" => $value[GetMessage("IBLOCK_XML2_VALUE")],
						"DESCRIPTION" => $value[GetMessage("IBLOCK_XML2_NAME")],
					);
					$i++;
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_TRAITS_VALUES")]))
			{
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TRAITS"]] = array();
				$i = 0;
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_TRAITS_VALUES")] as $key => $value)
				{
					if(
						!array_key_exists("PREVIEW_TEXT", $arElement)
						&& $value[GetMessage("IBLOCK_XML2_NAME")] == GetMessage("IBLOCK_XML2_FULL_TITLE2")
					)
					{
						$arElement["PREVIEW_TEXT"] = $value[GetMessage("IBLOCK_XML2_VALUE")];
						if(strpos($arElement["PREVIEW_TEXT"], "<")!==false)
							$arElement["PREVIEW_TEXT_TYPE"] = "html";
						else
							$arElement["PREVIEW_TEXT_TYPE"] = "text";
					}
					else
					{
						if($value[GetMessage("IBLOCK_XML2_NAME")] == GetMessage("IBLOCK_XML2_WEIGHT"))
						{
							$arElement["BASE_WEIGHT"] = $this->ToFloat($value[GetMessage("IBLOCK_XML2_VALUE")])*1000;
						}

						$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TRAITS"]]["n".$i] = array(
							"VALUE" => $value[GetMessage("IBLOCK_XML2_VALUE")],
							"DESCRIPTION" => $value[GetMessage("IBLOCK_XML2_NAME")],
						);
						$i++;
					}
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_TAXES_VALUES")]))
			{
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TAXES"]] = array();
				$i = 0;
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_TAXES_VALUES")] as $key => $value)
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TAXES"]]["n".$i] = array(
						"VALUE" => $value[GetMessage("IBLOCK_XML2_TAX_VALUE")],
						"DESCRIPTION" => $value[GetMessage("IBLOCK_XML2_NAME")],
					);
					$i++;
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_BASE_UNIT")]))
			{
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BASE_UNIT"]] = $arXMLElement[GetMessage("IBLOCK_XML2_BASE_UNIT")];
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_PROPERTIES_VALUES")]))
			{
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_PROPERTIES_VALUES")] as $key=>$value)
				{
					if(!array_key_exists(GetMessage("IBLOCK_XML2_ID"), $value))
						continue;

					$prop_id = $value[GetMessage("IBLOCK_XML2_ID")];
					unset($value[GetMessage("IBLOCK_XML2_ID")]);

					//Handle properties which is actually element fields
					if(!array_key_exists($prop_id, $this->PROPERTY_MAP))
					{
						if($prop_id == "CML2_CODE")
							$arElement["CODE"] = array_pop($value);
						elseif($prop_id == "CML2_ACTIVE")
						{
							$value = array_pop($value);
							$arElement["ACTIVE"] = ($value=="true") || intval($value)? "Y": "N";
						}
						elseif($prop_id == "CML2_SORT")
							$arElement["SORT"] = array_pop($value);
						elseif($prop_id == "CML2_ACTIVE_FROM")
							$arElement["ACTIVE_FROM"] = CDatabase::FormatDate(array_pop($value), "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL"));
						elseif($prop_id == "CML2_ACTIVE_TO")
							$arElement["ACTIVE_TO"] = CDatabase::FormatDate(array_pop($value), "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL"));
						elseif($prop_id == "CML2_PREVIEW_TEXT")
						{
							if(array_key_exists(GetMessage("IBLOCK_XML2_VALUE"), $value))
							{
								if(isset($value[GetMessage("IBLOCK_XML2_VALUE")]))
									$arElement["PREVIEW_TEXT"] = $value[GetMessage("IBLOCK_XML2_VALUE")];
								else
									$arElement["PREVIEW_TEXT"] = "";

								if(isset($value[GetMessage("IBLOCK_XML2_TYPE")]))
									$arElement["PREVIEW_TEXT_TYPE"] = $value[GetMessage("IBLOCK_XML2_TYPE")];
								else
									$arElement["PREVIEW_TEXT_TYPE"] = "html";
							}
						}
						elseif($prop_id == "CML2_DETAIL_TEXT")
						{
							if(array_key_exists(GetMessage("IBLOCK_XML2_VALUE"), $value))
							{
								if(isset($value[GetMessage("IBLOCK_XML2_VALUE")]))
									$arElement["DETAIL_TEXT"] = $value[GetMessage("IBLOCK_XML2_VALUE")];
								else
									$arElement["DETAIL_TEXT"] = "";

								if(isset($value[GetMessage("IBLOCK_XML2_TYPE")]))
									$arElement["DETAIL_TEXT_TYPE"] = $value[GetMessage("IBLOCK_XML2_TYPE")];
								else
									$arElement["DETAIL_TEXT_TYPE"] = "html";
							}
						}
						elseif($prop_id == "CML2_PREVIEW_PICTURE")
						{
							if(!is_array($this->preview) || !$arElement["PREVIEW_PICTURE"])
								$arElement["PREVIEW_PICTURE"] = $this->MakeFileArray(array_pop($value));
						}

						continue;
					}

					$prop_id = $this->PROPERTY_MAP[$prop_id];
					$prop_type = $this->arProperties[$prop_id]["PROPERTY_TYPE"];

					if(!array_key_exists($prop_id, $arElement["PROPERTY_VALUES"]))
						$arElement["PROPERTY_VALUES"][$prop_id] = array();

					//check for bitrix extended format
					if(array_key_exists(GetMessage("IBLOCK_XML2_PROPERTY_VALUE"), $value))
					{
						$i = 1;
						$strPV = GetMessage("IBLOCK_XML2_PROPERTY_VALUE");
						$lPV = strlen($strPV);
						foreach($value as $k=>$prop_value)
						{
							if(substr($k, 0, $lPV) === $strPV)
							{
								if(array_key_exists(GetMessage("IBLOCK_XML2_SERIALIZED"), $prop_value))
									$prop_value[GetMessage("IBLOCK_XML2_VALUE")] = unserialize($prop_value[GetMessage("IBLOCK_XML2_VALUE")]);
								if($prop_type=="F")
									$prop_value[GetMessage("IBLOCK_XML2_VALUE")] = $this->MakeFileArray($prop_value[GetMessage("IBLOCK_XML2_VALUE")]);
								elseif($prop_type=="G")
									$prop_value[GetMessage("IBLOCK_XML2_VALUE")] = $this->GetSectionByXML_ID($this->arProperties[$prop_id]["LINK_IBLOCK_ID"], $prop_value[GetMessage("IBLOCK_XML2_VALUE")]);
								elseif($prop_type=="E")
									$prop_value[GetMessage("IBLOCK_XML2_VALUE")] = $this->GetElementByXML_ID($this->arProperties[$prop_id]["LINK_IBLOCK_ID"], $prop_value[GetMessage("IBLOCK_XML2_VALUE")]);
								elseif($prop_type=="L")
									$prop_value[GetMessage("IBLOCK_XML2_VALUE")] = $this->GetEnumByXML_ID($this->arProperties[$prop_id]["ID"], $prop_value[GetMessage("IBLOCK_XML2_VALUE")]);

								if(array_key_exists("bOld", $arElement["PROPERTY_VALUES"][$prop_id]))
								{
									if($prop_type=="F")
									{
										foreach($arElement["PROPERTY_VALUES"][$prop_id] as $PROPERTY_VALUE_ID => $PROPERTY_VALUE)
											$arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID] = array(
												"tmp_name" => "",
												"del" => "Y",
											);
										unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
									}
									else
										$arElement["PROPERTY_VALUES"][$prop_id] = array();
								}
								$arElement["PROPERTY_VALUES"][$prop_id]["n".$i] = array(
									"VALUE" => $prop_value[GetMessage("IBLOCK_XML2_VALUE")],
									"DESCRIPTION" => $prop_value[GetMessage("IBLOCK_XML2_DESCRIPTION")],
								);
							}
							$i++;
						}
					}
					else
					{
						foreach($value as $k=>$prop_value)
						{
							if(array_key_exists("bOld", $arElement["PROPERTY_VALUES"][$prop_id]))
							{
								if($prop_type=="F")
								{
									foreach($arElement["PROPERTY_VALUES"][$prop_id] as $PROPERTY_VALUE_ID => $PROPERTY_VALUE)
										$arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID] = array(
											"tmp_name" => "",
											"del" => "Y",
										);
									unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
								}
								else
								{
									$arElement["PROPERTY_VALUES"][$prop_id] = array();
								}
							}

							if($prop_type == "L" && $k == GetMessage("IBLOCK_XML2_VALUE_ID"))
							{
								$prop_value = $this->GetEnumByXML_ID($this->arProperties[$prop_id]["ID"], $prop_value);
							}
							elseif($prop_type == "N" && isset($this->next_step["sdp"]))
							{
								$prop_value = $this->ToFloat($prop_value);
							}

							$arElement["PROPERTY_VALUES"][$prop_id][] = $prop_value;
						}
					}
				}
			}

			if($arDBElement)
			{
				foreach($arElement["PROPERTY_VALUES"] as $prop_id=>$prop)
				{
					if(is_array($arElement["PROPERTY_VALUES"][$prop_id]) && array_key_exists("bOld", $arElement["PROPERTY_VALUES"][$prop_id]))
					{
						if($this->arProperties[$prop_id]["PROPERTY_TYPE"]=="F")
							unset($arElement["PROPERTY_VALUES"][$prop_id]);
						else
							unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
					}
				}

				if(intval($arElement["MODIFIED_BY"]) <= 0)
					$arElement["MODIFIED_BY"] = intval($USER->GetID());

				if(!array_key_exists("CODE", $arElement) && is_array($this->translit_on_update))
					$arElement["CODE"] = CUtil::translit($arElement["NAME"], LANGUAGE_ID, $this->translit_on_update);

				$obElement->Update($arDBElement["ID"], $arElement, $bWF, true, !is_array($this->preview) && $this->preview);
				//In case element was not active in database we have to activate its offers
				if($arDBElement["ACTIVE"] != "Y")
				{
					$this->ChangeOffersStatus($arDBElement["ID"], "Y", $bWF);
				}
				$arElement["ID"] = $arDBElement["ID"];
				if($arElement["ID"])
				{
					$counter["UPD"]++;
				}
				else
				{
					$this->LAST_ERROR = $obElement->LAST_ERROR;
					$counter["ERR"]++;
				}
			}
			else
			{
				if(!array_key_exists("CODE", $arElement) && is_array($this->translit_on_add))
					$arElement["CODE"] = CUtil::translit($arElement["NAME"], LANGUAGE_ID, $this->translit_on_add);

				$arElement["IBLOCK_ID"] = $this->next_step["IBLOCK_ID"];
				$arElement["ID"] = $obElement->Add($arElement, $bWF, true, !is_array($this->preview) && $this->preview);
				if($arElement["ID"])
				{
					$counter["ADD"]++;
				}
				else
				{
					$this->LAST_ERROR = $obElement->LAST_ERROR;
					$counter["ERR"]++;
				}
			}

			if($arElement["ID"] && $this->bCatalog)
			{
				$CML_LINK = $this->PROPERTY_MAP["CML2_LINK"];

				$arProduct = array(
					"ID" => $arElement["ID"],
				);
				if(isset($arElement["QUANTITY"]))
					$arProduct["QUANTITY"] = $arElement["QUANTITY"];

				if(isset($arElement["BASE_WEIGHT"]))
				{
					$arProduct["WEIGHT"] = $arElement["BASE_WEIGHT"];
				}
				else
				{
					$rsWeight = CIBlockElement::GetProperty($this->arProperties[$CML_LINK]["LINK_IBLOCK_ID"], $arElement["PROPERTY_VALUES"][$CML_LINK], array(), array("CODE" => "CML2_TRAITS"));
					while($arWheight = $rsWeight->Fetch())
					{
						if($arWheight["DESCRIPTION"] == GetMessage("IBLOCK_XML2_WEIGHT"))
							$arProduct["WEIGHT"] = $this->ToFloat($arWheight["VALUE"])*1000;
					}
				}

				if(isset($arElement["PRICES"]))
				{
					//Here start VAT handling

					//Check if all the taxes exists in BSM catalog
					$arTaxMap = array();
					$rsTaxProperty = CIBlockElement::GetProperty($this->arProperties[$CML_LINK]["LINK_IBLOCK_ID"], $arElement["PROPERTY_VALUES"][$CML_LINK], "sort", "asc", array("CODE" => "CML2_TAXES"));
					while($arTaxProperty = $rsTaxProperty->Fetch())
					{
						if(
							strlen($arTaxProperty["VALUE"]) > 0
							&& strlen($arTaxProperty["DESCRIPTION"]) > 0
							&& !array_key_exists($arTaxProperty["DESCRIPTION"], $arTaxMap)
						)
						{
							$arTaxMap[$arTaxProperty["DESCRIPTION"]] = array(
								"RATE" => $this->ToFloat($arTaxProperty["VALUE"]),
								"ID" => $this->CheckTax($arTaxProperty["DESCRIPTION"], $this->ToFloat($arTaxProperty["VALUE"])),
							);
						}
					}

					//First find out if all the prices have TAX_IN_SUM true
					$TAX_IN_SUM = "Y";
					foreach($arElement["PRICES"] as $key=>$price)
					{
						if($price["PRICE"]["TAX_IN_SUM"] !== "true")
						{
							$TAX_IN_SUM = "N";
							break;
						}
					}
					//If there was found not insum tax we'll make shure
					//that all prices has the same flag
					if($TAX_IN_SUM === "N")
					{
						foreach($arElement["PRICES"] as $key=>$price)
						{
							if($price["PRICE"]["TAX_IN_SUM"] !== "false")
							{
								$TAX_IN_SUM = "Y";
								break;
							}
						}
						//Check if there is a mix of tax in sum
						//and correct it by recalculating all the prices
						if($TAX_IN_SUM === "Y")
						{
							foreach($arElement["PRICES"] as $key=>$price)
							{
								if($price["PRICE"]["TAX_IN_SUM"] !== "true")
								{
									$TAX_NAME = $price["PRICE"]["TAX_NAME"];
									if(array_key_exists($TAX_NAME, $arTaxMap))
									{
										$PRICE_WO_TAX = $this->ToFloat($price[GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")]);
										$PRICE = $PRICE_WO_TAX + ($PRICE_WO_TAX / 100.0 * $arTaxMap[$TAX_NAME]["RATE"]);
										$arElement["PRICES"][$key][GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")] = $PRICE;
									}
								}
							}
						}
					}
					foreach($arElement["PRICES"] as $key=>$price)
					{
						$TAX_NAME = $price["PRICE"]["TAX_NAME"];
						if(array_key_exists($TAX_NAME, $arTaxMap))
						{
							$arProduct["VAT_ID"] = $arTaxMap[$TAX_NAME]["ID"];
							break;
						}
					}
					$arProduct["VAT_INCLUDED"] = $TAX_IN_SUM;
				}

				CCatalogProduct::Add($arProduct);

				$this->SetProductPrice($arElement["ID"], $arElement["PRICES"], $arElement["DISCOUNTS"]);

			}
		}

		return $arElement["ID"];
	}

	function ImportElementPrices($arXMLElement, &$counter, $bWF)
	{
		$arElement = array(
			"ID" => 0,
			"XML_ID" => $arXMLElement[GetMessage("IBLOCK_XML2_ID")],
		);

		$obElement = new CIBlockElement;
		$rsElement = $obElement->GetList(
			Array("ID"=>"asc"),
			Array("=XML_ID" => $arElement["XML_ID"], "IBLOCK_ID" => $this->next_step["IBLOCK_ID"]),
			false, false,
			Array("ID", "TMP_ID", "ACTIVE")
		);

		if($arDBElement = $rsElement->Fetch())
		{
			$arElement["ID"] = $arDBElement["ID"];

			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_PRICES")]))
			{//Collect price information for future use
				$arElement["PRICES"] = array();
				foreach($arXMLElement[GetMessage("IBLOCK_XML2_PRICES")] as $key=>$price)
				{
					if(isset($price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")]) && array_key_exists($price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")], $this->PRICES_MAP))
					{
						$price["PRICE"] = $this->PRICES_MAP[$price[GetMessage("IBLOCK_XML2_PRICE_TYPE_ID")]];
						$arElement["PRICES"][] = $price;
					}
				}

				$arElement["DISCOUNTS"] = array();
				if(isset($arXMLElement[GetMessage("IBLOCK_XML2_DISCOUNTS")]))
				{
					foreach($arXMLElement[GetMessage("IBLOCK_XML2_DISCOUNTS")] as $key=>$discount)
					{
						if(
							isset($discount[GetMessage("IBLOCK_XML2_DISCOUNT_CONDITION")])
							&& $discount[GetMessage("IBLOCK_XML2_DISCOUNT_CONDITION")]===GetMessage("IBLOCK_XML2_DISCOUNT_COND_VOLUME")
						)
						{
							$discount_value = $this->ToInt($discount[GetMessage("IBLOCK_XML2_DISCOUNT_COND_VALUE")]);
							$discount_percent = $this->ToFloat($discount[GetMessage("IBLOCK_XML2_DISCOUNT_COND_PERCENT")]);
							if($discount_value > 0 && $discount_percent > 0)
								$arElement["DISCOUNTS"][$discount_value] = $discount_percent;
						}
					}
				}
			}
			if(isset($arXMLElement[GetMessage("IBLOCK_XML2_AMOUNT")]))
				$arElement["QUANTITY"] = intval($arXMLElement[GetMessage("IBLOCK_XML2_AMOUNT")]);
			else
				$arElement["QUANTITY"] = 0;

			if(isset($arElement["PRICES"]) && $this->bCatalog)
			{
				$arProduct = array(
					"ID" => $arElement["ID"],
				);
				if(isset($arElement["QUANTITY"]))
					$arProduct["QUANTITY"] = $arElement["QUANTITY"];

				//Get weight from element traits
				$rsWeight = CIBlockElement::GetProperty($this->next_step["IBLOCK_ID"], $arElement["ID"], array(), array("ID"=>$this->PROPERTY_MAP["CML2_TRAITS"]));
				while($arWheight = $rsWeight->Fetch())
				{
					if($arWheight["DESCRIPTION"] == GetMessage("IBLOCK_XML2_WEIGHT"))
						$arProduct["WEIGHT"] = $this->ToFloat($arWheight["VALUE"])*1000;
				}

				//Here start VAT handling

				//Check if all the taxes exists in BSM catalog
				$arTaxMap = array();
				$rsTaxProperty = CIBlockElement::GetProperty($this->next_step["IBLOCK_ID"], $arElement["ID"], "sort", "asc", array("CODE" => "CML2_TAXES"));
				while($arTaxProperty = $rsTaxProperty->Fetch())
				{
					if(
						strlen($arTaxProperty["VALUE"]) > 0
						&& strlen($arTaxProperty["DESCRIPTION"]) > 0
						&& !array_key_exists($arTaxProperty["DESCRIPTION"], $arTaxMap)
					)
					{
						$arTaxMap[$arTaxProperty["DESCRIPTION"]] = array(
							"RATE" => $this->ToFloat($arTaxProperty["VALUE"]),
							"ID" => $this->CheckTax($arTaxProperty["DESCRIPTION"], $this->ToFloat($arTaxProperty["VALUE"])),
						);
					}
				}

				//First find out if all the prices have TAX_IN_SUM true
				$TAX_IN_SUM = "Y";
				foreach($arElement["PRICES"] as $key=>$price)
				{
					if($price["PRICE"]["TAX_IN_SUM"] !== "true")
					{
						$TAX_IN_SUM = "N";
						break;
					}
				}
				//If there was found not insum tax we'll make shure
				//that all prices has the same flag
				if($TAX_IN_SUM === "N")
				{
					foreach($arElement["PRICES"] as $key=>$price)
					{
						if($price["PRICE"]["TAX_IN_SUM"] !== "false")
						{
							$TAX_IN_SUM = "Y";
							break;
						}
					}
					//Check if there is a mix of tax in sum
					//and correct it by recalculating all the prices
					if($TAX_IN_SUM === "Y")
					{
						foreach($arElement["PRICES"] as $key=>$price)
						{
							if($price["PRICE"]["TAX_IN_SUM"] !== "true")
							{
								$TAX_NAME = $price["PRICE"]["TAX_NAME"];
								if(array_key_exists($TAX_NAME, $arTaxMap))
								{
									$PRICE_WO_TAX = $this->ToFloat($price[GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")]);
									$PRICE = $PRICE_WO_TAX + ($PRICE_WO_TAX / 100.0 * $arTaxMap[$TAX_NAME]["RATE"]);
									$arElement["PRICES"][$key][GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")] = $PRICE;
								}
							}
						}
					}
				}
				foreach($arElement["PRICES"] as $key=>$price)
				{
					$TAX_NAME = $price["PRICE"]["TAX_NAME"];
					if(array_key_exists($TAX_NAME, $arTaxMap))
					{
						$arProduct["VAT_ID"] = $arTaxMap[$TAX_NAME]["ID"];
						break;
					}
				}
				$arProduct["VAT_INCLUDED"] = $TAX_IN_SUM;

				CCatalogProduct::Add($arProduct);

				$this->SetProductPrice($arElement["ID"], $arElement["PRICES"], $arElement["DISCOUNTS"]);

				$counter["UPD"]++;
			}
		}

		return $arElement["ID"];
	}

	function SetProductPrice($PRODUCT_ID, $arPrices, $arDiscounts = false)
	{
		if(is_array($arDiscounts) && count($arDiscounts) > 0)
		{
			if(!array_key_exists(1, $arDiscounts))
				$arDiscounts[1] = 0;
			ksort($arDiscounts);
			$prev = 0;
			foreach($arDiscounts as $volume => $percent)
			{
				if($prev > 0)
				{
					$arDiscounts[$prev] = array(
						"QUANTITY_FROM" => $prev,
						"QUANTITY_TO" => $volume - 1,
						"PERCENT" => $arDiscounts[$prev],
					);
				}
				$prev = $volume;
			}
			$arDiscounts[$prev] = array(
				"QUANTITY_FROM" => $prev,
				"QUANTITY_TO" => "",
				"PERCENT" => $arDiscounts[$prev],
			);
		}
		else
		{
			$arDiscounts =  array(
				array(
					"QUANTITY_FROM" => "",
					"QUANTITY_TO" => "",
					"PERCENT" => 0,
				),
			);
		}

		$arDBPrices = array();
		$rsPrice = CPrice::GetList(array(), array("PRODUCT_ID" => $PRODUCT_ID));
		while($ar = $rsPrice->Fetch())
			$arDBPrices[$ar["CATALOG_GROUP_ID"].":".$ar["QUANTITY_FROM"].":".$ar["QUANTITY_TO"]] = $ar["ID"];

		if(!is_array($arPrices))
			$arPrices = array();

		foreach($arPrices as $key=>$price)
		{

			if(!isset($price[GetMessage("IBLOCK_XML2_CURRENCY")]))
				$price[GetMessage("IBLOCK_XML2_CURRENCY")] = $price["PRICE"]["CURRENCY"];

			$arPrice = Array(
				"PRODUCT_ID" => $PRODUCT_ID,
				"CATALOG_GROUP_ID" => $price["PRICE"]["ID"],
				"^PRICE" => $this->ToFloat($price[GetMessage("IBLOCK_XML2_PRICE_FOR_ONE")]),
				"CURRENCY" => $this->CheckCurrency($price[GetMessage("IBLOCK_XML2_CURRENCY")]),
			);

			foreach($arDiscounts as $arDiscount)
			{
				$arPrice["QUANTITY_FROM"] = $arDiscount["QUANTITY_FROM"];
				$arPrice["QUANTITY_TO"] = $arDiscount["QUANTITY_TO"];
				if($arDiscount["PERCENT"] > 0)
					$arPrice["PRICE"] = $arPrice["^PRICE"] - $arPrice["^PRICE"]/100*$arDiscount["PERCENT"];
				else
					$arPrice["PRICE"] = $arPrice["^PRICE"];

				$id = $arPrice["CATALOG_GROUP_ID"].":".$arPrice["QUANTITY_FROM"].":".$arPrice["QUANTITY_TO"];
				if(array_key_exists($id, $arDBPrices))
				{
					CPrice::Update($arDBPrices[$id], $arPrice);
					unset($arDBPrices[$id]);
				}
				else
				{
					CPrice::Add($arPrice);
				}
			}
		}

		foreach($arDBPrices as $key=>$id)
			CPrice::Delete($id);
	}

	function ImportSection($xml_tree_id, $IBLOCK_ID, $parent_section_id)
	{
		global $DB;
		$this->next_step["section_sort"] += 10;
		$arSection = array(
			"IBLOCK_SECTION_ID" => $parent_section_id,
		);
		$rsS = $this->_xml_file->GetList(
			array("ID" => "asc"),
			array("PARENT_ID" => $xml_tree_id)
		);
		$XML_SECTIONS_PARENT = false;
		while($arS = $rsS->Fetch())
		{
			if(isset($arS["VALUE_CLOB"]))
				$arS["VALUE"] = $arS["VALUE_CLOB"];
			if($arS["NAME"]==GetMessage("IBLOCK_XML2_ID"))
				$arSection["XML_ID"] = $arS["VALUE"];
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_NAME"))
				$arSection["NAME"] = $arS["VALUE"];
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_DESCRIPTION"))
			{
				$arSection["DESCRIPTION"] = $arS["VALUE"];
				$arSection["DESCRIPTION_TYPE"] = "html";
			}
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_GROUPS"))
				$XML_SECTIONS_PARENT = $arS["ID"];
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_BX_SORT"))
				$arSection["SORT"] = intval($arS["VALUE"]);
			elseif($arS["NAME"]==GetMessage("IBLOCK_XML2_BX_CODE"))
				$arSection["CODE"] = $arS["VALUE"];
			elseif($arS["NAME"] == GetMessage("IBLOCK_XML2_BX_PICTURE"))
				$arSection["PICTURE"] = $this->MakeFileArray($arS["VALUE"]);
			elseif($arS["NAME"] == GetMessage("IBLOCK_XML2_BX_DETAIL_PICTURE"))
				$arSection["DETAIL_PICTURE"] = $this->MakeFileArray($arS["VALUE"]);
			elseif($arS["NAME"] == GetMessage("IBLOCK_XML2_BX_ACTIVE"))
				$arSection["ACTIVE"] = ($arS["VALUE"]=="true") || intval($arS["VALUE"])? "Y": "N";
		}
		$obSection = new CIBlockSection;
		$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$arSection["XML_ID"]), false);
		if($arDBSection = $rsSection->Fetch())
		{
			if(!array_key_exists("CODE", $arSection) && is_array($this->translit_on_update))
				$arSection["CODE"] = CUtil::translit($arSection["NAME"], LANGUAGE_ID, $this->translit_on_update);

			$bChanged = false;
			foreach($arSection as $key=>$value)
			{
				if(is_array($arDBSection[$key]) || ($arDBSection[$key] != $value))
				{
					$bChanged = true;
					break;
				}
			}
			if($bChanged)
			{
				$res = $obSection->Update($arDBSection["ID"], $arSection);
				if(!$res)
					$this->LAST_ERROR = $obSection->LAST_ERROR;
			}
			$arSection["ID"] = $arDBSection["ID"];
		}
		else
		{
			if(!array_key_exists("CODE", $arSection) && is_array($this->translit_on_add))
				$arSection["CODE"] = CUtil::translit($arSection["NAME"], LANGUAGE_ID, $this->translit_on_add);

			$arSection["IBLOCK_ID"] = $IBLOCK_ID;
			if(!isset($arSection["SORT"]))
				$arSection["SORT"] = $this->next_step["section_sort"];

			$arSection["ID"] = $obSection->Add($arSection);
			if(!$arSection["ID"])
				$this->LAST_ERROR = $obSection->LAST_ERROR;
		}

		if($arSection["ID"])
			$this->_xml_file->Add(array("PARENT_ID" => 0, "LEFT_MARGIN" => $arSection["ID"]));

		if($XML_SECTIONS_PARENT)
		{
			$rs = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $XML_SECTIONS_PARENT),
				array("ID")
			);
			while($ar = $rs->Fetch())
				$this->ImportSection($ar["ID"], $IBLOCK_ID, $arSection["ID"]);
		}
	}

	function DeactivateElement($action, $start_time, $interval)
	{
		global $DB;

		$counter = array(
			"DEL" => 0,
			"DEA" => 0,
			"NON" => 0,
		);

		if(array_key_exists("bUpdateOnly", $this->next_step) && $this->next_step["bUpdateOnly"])
			return $counter;

		if($action!="D" && $action!="A")
			return $counter;

		$bDelete = $action=="D";

		//This will protect us from deactivating when next_step is lost
		$IBLOCK_ID = intval($this->next_step["IBLOCK_ID"]);
		if($IBLOCK_ID < 1)
			return $counter;

		$arFilter = array(
			">ID" => $this->next_step["LAST_ID"],
			"IBLOCK_ID" => $IBLOCK_ID,
		);
		if(!$bDelete)
			$arFilter["ACTIVE"] = "Y";

		$obElement = new CIBlockElement;
		$rsElement = $obElement->GetList(
			Array("ID"=>"asc"),
			$arFilter,
			false, false,
			Array("ID", "ACTIVE")
		);

		while($arElement = $rsElement->Fetch())
		{
			$rs = $this->_xml_file->GetList(
				array(),
				array("PARENT_ID+0" => 0, "LEFT_MARGIN" => $arElement["ID"]),
				array("ID")
			);
			$ar = $rs->Fetch();
			if(!$ar)
			{
				if($bDelete)
				{
					$obElement->Delete($arElement["ID"]);
					$counter["DEL"]++;
				}
				else
				{
					$obElement->Update($arElement["ID"], array("ACTIVE"=>"N"));
					$counter["DEA"]++;
				}
			}
			else
			{
				$counter["NON"]++;
			}

			$this->next_step["LAST_ID"] = $arElement["ID"];

			if($interval > 0 && (time()-$start_time) > $interval)
				break;

		}
		return $counter;
	}

}

class CIBlockCMLExport
{
	var $fp = false;
	var $IBLOCK_ID = false;
	var $bExtended = false;
	var $work_dir = false;
	var $file_dir = false;
	var $next_step = false;
	var $arIBlock = false;
	var $prices = false;
	var $only_price = false;

	function Init($fp, $IBLOCK_ID, $next_step, $bExtended=false, $work_dir=false, $file_dir=false)
	{
		$this->fp = $fp;
		$this->IBLOCK_ID = intval($IBLOCK_ID);
		$this->bExtended = $bExtended;
		$this->work_dir = $work_dir;
		$this->file_dir = $file_dir;
		$this->next_step = $next_step;
		$this->only_price = false;

		$rsIBlock = CIBlock::GetList(array(), array("ID"=>$this->IBLOCK_ID, "MIN_PERMISSION"=>"W"));
		if(($this->arIBlock = $rsIBlock->Fetch()) && ($this->arIBlock["ID"]==$this->IBLOCK_ID))
		{
			$this->next_step["catalog"] = CModule::IncludeModule('catalog');
			if($this->next_step["catalog"])
			{
				$rs = CCatalog::GetList(array(),array("IBLOCK_ID"=>$this->arIBlock["ID"]));
				if($rs->Fetch())
				{
					$this->next_step["catalog"] = true;
					$this->prices = array();
					$rsPrice = CCatalogGroup::GetList(array(), array());
					while($arPrice = $rsPrice->Fetch())
					{
						$this->prices[$arPrice["ID"]] = $arPrice["NAME"];
					}
				}
				else
				{
					$this->next_step["catalog"] = false;
				}
			}
			return true;
		}
		else
			return false;
	}

	function GetIBlockXML_ID($IBLOCK_ID, $XML_ID=false)
	{
		if($XML_ID === false)
		{
			$IBLOCK_ID = intval($IBLOCK_ID);
			if($IBLOCK_ID>0)
			{
				$obIBlock = new CIBlock;
				$rsIBlock = $obIBlock->GetList(array(), array("ID"=>$IBLOCK_ID));
				if($arIBlock = $rsIBlock->Fetch())
					$XML_ID = $arIBlock["XML_ID"];
				else
					return "";
			}
			else
				return "";
		}
		if(strlen($XML_ID) <= 0)
		{
			$XML_ID = $IBLOCK_ID;
			$obIBlock = new CIBlock;
			$rsIBlock = $obIBlock->GetList(array(), array("XML_ID"=>$XML_ID));
			while($rsIBlock->Fetch())
			{
				$XML_ID = md5(uniqid(mt_rand(), true));
				$rsIBlock = $obIBlock->GetList(array(), array("XML_ID"=>$XML_ID));
			}
			$obIBlock->Update($IBLOCK_ID, array("XML_ID" => $XML_ID));
		}
		return $XML_ID;
	}

	function GetSectionXML_ID($IBLOCK_ID, $SECTION_ID, $XML_ID = false)
	{
		if($XML_ID === false)
		{
			$obSection = new CIBlockSection;
			$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "ID"=>$SECTION_ID));
			if($arSection = $rsSection->Fetch())
			{
				$XML_ID = $arSection["XML_ID"];
			}
		}
		if(strlen($XML_ID) <= 0)
		{
			$XML_ID = $SECTION_ID;
			$obSection = new CIBlockSection;
			$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "EXTERNAL_ID"=>$XML_ID));
			while($rsSection->Fetch())
			{
				$XML_ID = md5(uniqid(mt_rand(), true));
				$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "EXTERNAL_ID"=>$XML_ID));
			}
			$obSection->Update($SECTION_ID, array("XML_ID" => $XML_ID), false, false);
		}
		return $XML_ID;
	}

	function GetElementXML_ID($IBLOCK_ID, $ELEMENT_ID, $XML_ID = false)
	{
		if($XML_ID === false)
		{
			$arFilter = array(
				"ID" => $ELEMENT_ID,
				"SHOW_HISTORY"=>"Y",
			);
			if($IBLOCK_ID > 0)
				$arFilter["IBLOCK_ID"] = $IBLOCK_ID;
			$obElement = new CIBlockElement;
			$rsElement = $obElement->GetList(
					Array("ID"=>"asc"),
					$arFilter,
					false, false,
					Array("ID", "XML_ID")
			);
			if($arElement = $rsElement->Fetch())
			{
				$XML_ID = $arElement["XML_ID"];
			}
		}
		return $XML_ID;
	}

	function GetPropertyXML_ID($IBLOCK_ID, $NAME, $PROPERTY_ID, $XML_ID)
	{
		if(strlen($XML_ID) <= 0)
		{
			$XML_ID = $PROPERTY_ID;
			$obProperty = new CIBlockProperty;
			$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$XML_ID));
			while($rsProperty->Fetch())
			{
				$XML_ID = md5(uniqid(mt_rand(), true));
				$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$XML_ID));
			}
			$obProperty->Update($PROPERTY_ID, array("NAME"=>$NAME, "XML_ID" => $XML_ID));
		}
		return $XML_ID;
	}

	function StartExport()
	{
		fwrite($this->fp, "<"."?xml version=\"1.0\" encoding=\"".LANG_CHARSET."\"?".">\n");
		fwrite($this->fp, "<".GetMessage("IBLOCK_XML2_COMMERCE_INFO")." ".GetMessage("IBLOCK_XML2_SCHEMA_VERSION")."=\"2.021\" ".GetMessage("IBLOCK_XML2_TIMESTAMP")."=\"".date("Y-m-d")."T".date("H:i:s")."\">\n");
	}

	function ExportFile($FILE_ID)
	{
		if($this->work_dir)
		{
			$rsFile = CFile::GetByID($FILE_ID);
			if($arFile = $rsFile->Fetch())
			{
				$strFile = $arFile["SUBDIR"]."/".$arFile["FILE_NAME"];

				$strOldFile = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/".$strFile;
				$strOldFile = str_replace("//","/",$strOldFile);

				$strNewFile = $this->work_dir.$this->file_dir.$strFile;
				$strNewFile = str_replace("//","/",$strNewFile);

				CheckDirPath($strNewFile);
				if(@copy($strOldFile, $strNewFile))
					return $this->file_dir.$strFile;
			}
		}
		return "";
	}

	function StartExportMetadata()
	{
		$xml_id = $this->GetIBlockXML_ID($this->arIBlock["ID"], $this->arIBlock["XML_ID"]);
		$this->arIBlock["XML_ID"] = $xml_id;
		fwrite($this->fp, "\t<".GetMessage("IBLOCK_XML2_METADATA").">\n");
		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");
		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($this->arIBlock["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n");
		if(strlen($this->arIBlock["DESCRIPTION"])>0)
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialchars(FormatText($this->arIBlock["DESCRIPTION"], $this->arIBlock["DESCRIPTION_TYPE"]))."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");
	}

	function ExportSections(&$SECTION_MAP, $start_time, $INTERVAL, $FILTER)
	{
		$counter = 0;
		if(!array_key_exists("CURRENT_DEPTH", $this->next_step))
			$this->next_step["CURRENT_DEPTH"]=0;
		else // this makes second "step"
			return $counter;
		$SECTION_MAP = array();

		if($FILTER === "none")
			return;
		$arFilter = array(
			"IBLOCK_ID" => $this->arIBlock["ID"],
			"GLOBAL_ACTIVE" => "Y",
		);
		if($FILTER === "all")
			unset($arFilter["GLOBAL_ACTIVE"]);

		$rsSections = CIBlockSection::GetList(array("left_margin"=>"asc"), $arFilter);
		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_GROUPS").">\n");
		while($arSection = $rsSections->Fetch())
		{
			$white_space = str_repeat("\t\t", $arSection["DEPTH_LEVEL"]);

			while($this->next_step["CURRENT_DEPTH"] >= $arSection["DEPTH_LEVEL"])
			{
				fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"])."\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");
				fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"]-1)."\t\t\t</".GetMessage("IBLOCK_XML2_GROUP").">\n");
				$this->next_step["CURRENT_DEPTH"]--;
			}

			$xml_id = $this->GetSectionXML_ID($this->arIBlock["ID"], $arSection["ID"], $arSection["XML_ID"]);
			$SECTION_MAP[$arSection["ID"]] = $xml_id;

			fwrite($this->fp,
				$white_space."\t<".GetMessage("IBLOCK_XML2_GROUP").">\n".
				$white_space."\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n".
				$white_space."\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($arSection["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n"
			);
			if(strlen($arSection["DESCRIPTION"])>0)
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialchars(FormatText($arSection["DESCRIPTION"], $arSection["DESCRIPTION_TYPE"]))."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");
			if($this->bExtended)
			{
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_BX_ACTIVE").">".($arSection["ACTIVE"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_ACTIVE").">\n");
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_BX_SORT").">".intval($arSection["SORT"])."</".GetMessage("IBLOCK_XML2_BX_SORT").">\n");
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_BX_CODE").">".htmlspecialchars($arSection["CODE"])."</".GetMessage("IBLOCK_XML2_BX_CODE").">\n");
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_BX_PICTURE").">".htmlspecialchars($this->ExportFile($arSection["PICTURE"]))."</".GetMessage("IBLOCK_XML2_BX_PICTURE").">\n");
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_BX_DETAIL_PICTURE").">".htmlspecialchars($this->ExportFile($arSection["DETAIL_PICTURE"]))."</".GetMessage("IBLOCK_XML2_BX_DETAIL_PICTURE").">\n");
			}

			fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_GROUPS").">\n");

			$this->next_step["CURRENT_DEPTH"] = $arSection["DEPTH_LEVEL"];

			$counter++;
		}
		while($this->next_step["CURRENT_DEPTH"] > 0)
		{
			fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"])."\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");
			fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"]-1)."\t\t\t</".GetMessage("IBLOCK_XML2_GROUP").">\n");
			$this->next_step["CURRENT_DEPTH"]--;
		}
		fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");
		return $counter;
	}

	function ExportProperties(&$PROPERTY_MAP)
	{
		$PROPERTY_MAP = array();

		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_PROPERTIES").">\n");

		if($this->bExtended)
		{
			$arElementFields = array(
				"CML2_ACTIVE" => GetMessage("IBLOCK_XML2_BX_ACTIVE"),
				"CML2_CODE" => GetMessage("IBLOCK_XML2_SYMBOL_CODE"),
				"CML2_SORT" => GetMessage("IBLOCK_XML2_SORT"),
				"CML2_ACTIVE_FROM" => GetMessage("IBLOCK_XML2_START_TIME"),
				"CML2_ACTIVE_TO" => GetMessage("IBLOCK_XML2_END_TIME"),
				"CML2_PREVIEW_TEXT" => GetMessage("IBLOCK_XML2_ANONS"),
				"CML2_DETAIL_TEXT" => GetMessage("IBLOCK_XML2_DETAIL"),
				"CML2_PREVIEW_PICTURE" => GetMessage("IBLOCK_XML2_PREVIEW_PICTURE"),
			);

			foreach($arElementFields as $key => $value)
				fwrite($this->fp,
					"\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY").">\n".
					"\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".$key."</".GetMessage("IBLOCK_XML2_ID").">\n".
					"\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".$value."</".GetMessage("IBLOCK_XML2_NAME").">\n".
					"\t\t\t\t<".GetMessage("IBLOCK_XML2_MULTIPLE").">false</".GetMessage("IBLOCK_XML2_MULTIPLE").">\n".
					"\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY").">\n"
				);
		}

		$arFilter = array(
			"IBLOCK_ID" => $this->arIBlock["ID"],
			"ACTIVE" => "Y",
		);
		$arSort = array(
			"sort" => "asc",
		);
		$arProps = array();
		$obProp = new CIBlockProperty();
		$rsProp = $obProp->GetList($arSort, $arFilter);
		while($arProp = $rsProp->Fetch())
		{
			fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY").">\n");

			$xml_id = $this->GetPropertyXML_ID($this->arIBlock["ID"], $arProp["NAME"], $arProp["ID"], $arProp["XML_ID"]);
			$PROPERTY_MAP[$arProp["ID"]] = $xml_id;
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");

			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($arProp["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_MULTIPLE").">".($arProp["MULTIPLE"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_MULTIPLE").">\n");
			if($arProp["PROPERTY_TYPE"]=="L")
			{
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_CHOICE_VALUES").">\n");
				$rsEnum = CIBlockProperty::GetPropertyEnum($arProp["ID"]);
				while($arEnum = $rsEnum->Fetch())
				{
					fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($arEnum["VALUE"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
					if($this->bExtended)
					{
						fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_CHOICE").">\n");
						fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($arEnum["XML_ID"])."</".GetMessage("IBLOCK_XML2_ID").">\n");
						fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($arEnum["VALUE"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
						fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_BY_DEFAULT").">".($arEnum["DEF"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BY_DEFAULT").">\n");
						fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_SORT").">".intval($arEnum["SORT"])."</".GetMessage("IBLOCK_XML2_SORT").">\n");
						fwrite($this->fp, "\t\t\t\t\t</".GetMessage("IBLOCK_XML2_CHOICE").">\n");
					}
				}
				fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_CHOICE_VALUES").">\n");
			}
			if($this->bExtended)
			{
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_SORT").">".intval($arProp["SORT"])."</".GetMessage("IBLOCK_XML2_BX_SORT").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_CODE").">".htmlspecialchars($arProp["CODE"])."</".GetMessage("IBLOCK_XML2_BX_CODE").">\n");
				if(is_array($arProp["DEFAULT_VALUE"]))
				{
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_DEFAULT_VALUE").">".htmlspecialchars(serialize($arProp["DEFAULT_VALUE"]))."</".GetMessage("IBLOCK_XML2_BX_DEFAULT_VALUE").">\n");
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_SERIALIZED").">1</".GetMessage("IBLOCK_XML2_SERIALIZED").">\n");
				}
				else
				{
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_DEFAULT_VALUE").">".htmlspecialchars($arProp["DEFAULT_VALUE"])."</".GetMessage("IBLOCK_XML2_BX_DEFAULT_VALUE").">\n");
				}
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_PROERTY_TYPE").">".htmlspecialchars($arProp["PROPERTY_TYPE"])."</".GetMessage("IBLOCK_XML2_BX_PROERTY_TYPE").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_ROWS").">".htmlspecialchars($arProp["ROW_COUNT"])."</".GetMessage("IBLOCK_XML2_BX_ROWS").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_COLUMNS").">".htmlspecialchars($arProp["COL_COUNT"])."</".GetMessage("IBLOCK_XML2_BX_COLUMNS").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_LIST_TYPE").">".htmlspecialchars($arProp["LIST_TYPE"])."</".GetMessage("IBLOCK_XML2_BX_LIST_TYPE").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_FILE_EXT").">".htmlspecialchars($arProp["FILE_TYPE"])."</".GetMessage("IBLOCK_XML2_BX_FILE_EXT").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_FIELDS_COUNT").">".htmlspecialchars($arProp["MULTIPLE_CNT"])."</".GetMessage("IBLOCK_XML2_BX_FIELDS_COUNT").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_LINKED_IBLOCK").">".htmlspecialchars($this->GetIBlockXML_ID($arProp["LINK_IBLOCK_ID"]))."</".GetMessage("IBLOCK_XML2_BX_LINKED_IBLOCK").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_WITH_DESCRIPTION").">".($arProp["WITH_DESCRIPTION"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_WITH_DESCRIPTION").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_SEARCH").">".($arProp["SEARCHABLE"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_SEARCH").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_FILTER").">".($arProp["FILTRABLE"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_FILTER").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_USER_TYPE").">".htmlspecialchars($arProp["USER_TYPE"])."</".GetMessage("IBLOCK_XML2_BX_USER_TYPE").">\n");
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_IS_REQUIRED").">".($arProp["IS_REQUIRED"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_IS_REQUIRED").">\n");
			}
			fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY").">\n");
		}
		fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_PROPERTIES").">\n");
	}

	function ExportPrices()
	{
		if($this->next_step["catalog"])
		{
			$rsPrice = CCatalogGroup::GetList(array(), array());
			if($arPrice = $rsPrice->Fetch())
			{
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_PRICE_TYPES").">\n");
				do {
					fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_PRICE_TYPE").">\n");
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($arPrice["NAME"])."</".GetMessage("IBLOCK_XML2_ID").">\n");
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($arPrice["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n");
					fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_PRICE_TYPE").">\n");
				} while ($arPrice = $rsPrice->Fetch());
				fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_PRICE_TYPES").">\n");
			}
		}
	}

	function EndExportMetadata()
	{
		fwrite($this->fp, "\t</".GetMessage("IBLOCK_XML2_METADATA").">\n");
	}

	function StartExportCatalog($with_metadata = true, $changes_only = false)
	{
		if($this->next_step["catalog"])
			fwrite($this->fp, "\t<".GetMessage("IBLOCK_XML2_OFFER_LIST").">\n");
		else
			fwrite($this->fp, "\t<".GetMessage("IBLOCK_XML2_CATALOG").">\n");

		$xml_id = $this->GetIBlockXML_ID($this->arIBlock["ID"], $this->arIBlock["XML_ID"]);
		$this->arIBlock["XML_ID"] = $xml_id;

		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");
		if($with_metadata)
		{
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_METADATA_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_METADATA_ID").">\n");
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($this->arIBlock["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n");

			if(strlen($this->arIBlock["DESCRIPTION"])>0)
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialchars(FormatText($this->arIBlock["DESCRIPTION"], $this->arIBlock["DESCRIPTION_TYPE"]))."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");

			if($this->bExtended)
			{
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_CODE").">".htmlspecialchars($this->arIBlock["CODE"])."</".GetMessage("IBLOCK_XML2_BX_CODE").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_SORT").">".intval($this->arIBlock["SORT"])."</".GetMessage("IBLOCK_XML2_BX_SORT").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_LIST_URL").">".htmlspecialchars($this->arIBlock["LIST_PAGE_URL"])."</".GetMessage("IBLOCK_XML2_BX_LIST_URL").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_DETAIL_URL").">".htmlspecialchars($this->arIBlock["DETAIL_PAGE_URL"])."</".GetMessage("IBLOCK_XML2_BX_DETAIL_URL").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_SECTION_URL").">".htmlspecialchars($this->arIBlock["SECTION_PAGE_URL"])."</".GetMessage("IBLOCK_XML2_BX_SECTION_URL").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_PICTURE").">".htmlspecialchars($this->ExportFile($this->arIBlock["PICTURE"]))."</".GetMessage("IBLOCK_XML2_BX_PICTURE").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_INDEX_ELEMENTS").">".($this->arIBlock["INDEX_ELEMENT"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_INDEX_ELEMENTS").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_INDEX_SECTIONS").">".($this->arIBlock["INDEX_SECTION"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_INDEX_SECTIONS").">\n");
				//fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_SECTIONS_NAME").">".htmlspecialchars($this->arIBlock["SECTIONS_NAME"])."</".GetMessage("IBLOCK_XML2_BX_SECTIONS_NAME").">\n");
				//fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_SECTION_NAME").">".htmlspecialchars($this->arIBlock["SECTION_NAME"])."</".GetMessage("IBLOCK_XML2_BX_SECTION_NAME").">\n");
				//fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_ELEMENTS_NAME").">".htmlspecialchars($this->arIBlock["ELEMENTS_NAME"])."</".GetMessage("IBLOCK_XML2_BX_ELEMENTS_NAME").">\n");
				//fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_ELEMENT_NAME").">".htmlspecialchars($this->arIBlock["ELEMENT_NAME"])."</".GetMessage("IBLOCK_XML2_BX_ELEMENT_NAME").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_BX_WORKFLOW").">".($this->arIBlock["WORKFLOW"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_WORKFLOW").">\n");
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_LABELS").">\n");
				$arLabels = CIBlock::GetMessages($this->arIBlock["ID"]);
				foreach($arLabels as $id => $label)
				{
					fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_LABEL").">\n");
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".$id."</".GetMessage("IBLOCK_XML2_ID").">\n");
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($label)."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
					fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_LABEL").">\n");
				}
				fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_LABELS").">\n");
			}
		}

		if($with_metadata || $this->only_price)
		{
			$this->ExportPrices();
		}

		if($changes_only)
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_UPDATE_ONLY").">true</".GetMessage("IBLOCK_XML2_UPDATE_ONLY").">\n");

		if($this->next_step["catalog"])
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_OFFERS").">\n");
		else
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_POSITIONS").">\n");
	}

	function ExportElements($PROPERTY_MAP, $SECTION_MAP, $start_time, $INTERVAL, $counter_limit = 0, $arElementFilter = false)
	{
		$counter = 0;
		$arSelect = array(
			"ID",
			"IBLOCK_ID",
			"XML_ID",
			"ACTIVE",
			"CODE",
			"NAME",
			"PREVIEW_TEXT",
			"PREVIEW_TEXT_TYPE",
			"ACTIVE_FROM",
			"ACTIVE_TO",
			"SORT",
			"TAGS",
			"DETAIL_TEXT",
			"DETAIL_TEXT_TYPE",
			"PREVIEW_PICTURE",
			"DETAIL_PICTURE",
		);

		if(is_array($arElementFilter))
		{
			$arFilter = $arElementFilter;
		}
		else
		{
			if($arElementFilter === "none")
				return;
			$arFilter = array (
				"IBLOCK_ID"=> $this->arIBlock["ID"],
				"ACTIVE" => "Y",
				">ID" => $this->next_step["LAST_ID"],
			);
			if($arElementFilter === "all")
				unset($arFilter["ACTIVE"]);
		}

		$arOrder = array(
			"ID" => "ASC",
		);
		$arPropOrder = array(
			"sort" => "asc",
			"id" => "asc",
			"enum_sort" => "asc",
			"value_id" => "asc",
		);

		$rsElements = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
		while($arElement = $rsElements->Fetch())
		{
			if($this->next_step["catalog"])
				fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_OFFER").">\n");
			else
				fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_POSITION").">\n");

			if(strlen($arElement["XML_ID"])>0)
				$xml_id = $arElement["XML_ID"];
			else
				$xml_id = $arElement["ID"];

			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");

			if(!$this->only_price)
			{
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialchars($arElement["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n");
				if($this->bExtended)
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_TAGS").">".htmlspecialchars($arElement["TAGS"])."</".GetMessage("IBLOCK_XML2_BX_TAGS").">\n");

				$arSections = array();
				$rsSections = CIBlockElement::GetElementGroups($arElement["ID"]);
				while($arSection = $rsSections->Fetch())
					if(array_key_exists($arSection["ID"], $SECTION_MAP))
						$arSections[] = $SECTION_MAP[$arSection["ID"]];
				if(count($arSections)>0)
				{
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_GROUPS").">\n");
					foreach($arSections as $xml_id)
					{
						fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");
					}
					fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");
				}
				if(strlen($arElement["DETAIL_TEXT"])>0)
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialchars(FormatText($arElement["DETAIL_TEXT"], $arElement["DETAIL_TEXT_TYPE"]))."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");

				if($value = $this->ExportFile($arElement["DETAIL_PICTURE"]))
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_PICTURE").">".htmlspecialchars($value)."</".GetMessage("IBLOCK_XML2_PICTURE").">\n");

				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTIES_VALUES").">\n");

				if($this->bExtended)
				{
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_ACTIVE</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".($arElement["ACTIVE"]=="Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_CODE</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($arElement["CODE"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_SORT</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".intval($arElement["SORT"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_ACTIVE_FROM</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".CDatabase::FormatDate($arElement["ACTIVE_FROM"], CLang::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS")."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_ACTIVE_TO</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".CDatabase::FormatDate($arElement["ACTIVE_TO"], CLang::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS")."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_PREVIEW_TEXT</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($arElement["PREVIEW_TEXT"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_TYPE").">".htmlspecialchars($arElement["PREVIEW_TEXT_TYPE"])."</".GetMessage("IBLOCK_XML2_TYPE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_DETAIL_TEXT</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($arElement["DETAIL_TEXT"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_TYPE").">".htmlspecialchars($arElement["DETAIL_TEXT_TYPE"])."</".GetMessage("IBLOCK_XML2_TYPE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
					fwrite($this->fp,
						"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">CML2_PREVIEW_PICTURE</".GetMessage("IBLOCK_XML2_ID").">\n".
						"\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($this->ExportFile($arElement["PREVIEW_PICTURE"]))."</".GetMessage("IBLOCK_XML2_VALUE").">\n".
						"\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n"
					);
				}

				$rsProps = CIBlockElement::GetProperty($this->arIBlock["ID"], $arElement["ID"], $arPropOrder, array("ACTIVE"=>"Y"));
				$arProps = array();
				while($arProp = $rsProps->Fetch())
				{
					$pid = $arProp["ID"];
					if(!array_key_exists($pid, $arProps))
						$arProps[$pid] = array(
							"PROPERTY_TYPE" => $arProp["PROPERTY_TYPE"],
							"LINK_IBLOCK_ID" => $arProp["LINK_IBLOCK_ID"],
							"VALUES" => array(),
						);

					if($arProp["PROPERTY_TYPE"] == "L")
						$arProps[$pid]["VALUES"][] = array(
							"VALUE" => $arProp["VALUE_ENUM"],
							"DESCRIPTION" => $arProp["DESCRIPTION"],
							"VALUE_ENUM_ID" => $arProp["VALUE"],
						);
					else
						$arProps[$pid]["VALUES"][] = array(
							"VALUE" => $arProp["VALUE"],
							"DESCRIPTION" => $arProp["DESCRIPTION"],
							"VALUE_ENUM_ID" => $arProp["VALUE_ENUM_ID"],
						);
				}

				foreach($arProps as $pid => $arProp)
				{
					$bEmpty = true;
					fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n");
					fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialchars($PROPERTY_MAP[$pid])."</".GetMessage("IBLOCK_XML2_ID").">\n");
					foreach($arProp["VALUES"] as $arValue)
					{
						$value = $arValue["VALUE"];
						if(is_array($value) || strlen($value))
						{
							$bEmpty = false;
							if($this->bExtended)
							{
								if($arProp["PROPERTY_TYPE"]=="L")
								{
									$value = CIBlockPropertyEnum::GetByID($arValue["VALUE_ENUM_ID"]);
									$value = $value["XML_ID"];
								}
								elseif($arProp["PROPERTY_TYPE"]=="F")
								{
									$value = $this->ExportFile($value);
								}
								elseif($arProp["PROPERTY_TYPE"]=="G")
								{
									$value = $this->GetSectionXML_ID($arProp["LINK_IBLOCK_ID"], $value);
								}
								elseif($arProp["PROPERTY_TYPE"]=="E")
								{
									$value = $this->GetElementXML_ID($arProp["LINK_IBLOCK_ID"], $value);
								}
								if(is_array($value))
								{
									$bSerialized = true;
									$value = serialize($value);
								}
								else
								{
									$bSerialized = false;
								}
							}
							fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($value)."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
							if($this->bExtended)
							{
								fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUE").">\n");
								if($bSerialized)
									fwrite($this->fp, "\t\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_SERIALIZED").">true</".GetMessage("IBLOCK_XML2_SERIALIZED").">\n");
								fwrite($this->fp, "\t\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialchars($value)."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
								fwrite($this->fp, "\t\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialchars($arValue["DESCRIPTION"])."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");
								fwrite($this->fp, "\t\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUE").">\n");
							}
						}
					}
					if($bEmpty)
						fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE")."></".GetMessage("IBLOCK_XML2_VALUE").">\n");
					fwrite($this->fp, "\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n");
				}
				fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTIES_VALUES").">\n");
			}

			if($this->next_step["catalog"])
			{
				$arPrices = array();
				$rsPrices = CPrice::GetList(array(), array("PRODUCT_ID" => $arElement["ID"]));
				while($arPrice = $rsPrices->Fetch())
				{
					if(!$arPrice["QUANTITY_FROM"] && !$arPrice["QUANTITY_TO"])
					{
						$arPrices[] = array(
							GetMessage("IBLOCK_XML2_PRICE_TYPE_ID") => $this->prices[$arPrice["CATALOG_GROUP_ID"]],
							GetMessage("IBLOCK_XML2_PRICE_FOR_ONE") => $arPrice["PRICE"],
							GetMessage("IBLOCK_XML2_CURRENCY") => $arPrice["CURRENCY"],
							GetMessage("IBLOCK_XML2_MEASURE") => GetMessage("IBLOCK_XML2_PCS"),
						);
					}
				}
				if(count($arPrices)>0)
				{
					fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_PRICES").">\n");
					foreach($arPrices as $arPrice)
					{
						fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PRICE").">\n");
						foreach($arPrice as $key=>$value)
						{
							fwrite($this->fp, "\t\t\t\t\t\t<".$key.">".htmlspecialchars($value)."</".$key.">\n");
						}
						fwrite($this->fp, "\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PRICE").">\n");
					}
					fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_PRICES").">\n");
				}
			}

			if($this->next_step["catalog"])
				fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_OFFER").">\n");
			else
				fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_POSITION").">\n");

			$this->next_step["LAST_ID"] = $arElement["ID"];
			$counter++;
			if($INTERVAL > 0 && (time()-$start_time) > $INTERVAL)
				break;
			if($counter_limit > 0 && ($counter >= $counter_limit))
				break;
		}
		return $counter;
	}

	function EndExportCatalog()
	{
		if($this->next_step["catalog"])
		{
			fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_OFFERS").">\n");
			fwrite($this->fp, "\t</".GetMessage("IBLOCK_XML2_OFFER_LIST").">\n");
		}
		else
		{
			fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_POSITIONS").">\n");
			fwrite($this->fp, "\t</".GetMessage("IBLOCK_XML2_CATALOG").">\n");
		}
	}

	function EndExport()
	{
		fwrite($this->fp, "</".GetMessage("IBLOCK_XML2_COMMERCE_INFO").">\n");
	}
}
/*
GetMessage("IBLOCK_XML2_COEFF")
GetMessage("IBLOCK_XML2_OWNER")
GetMessage("IBLOCK_XML2_TITLE")
GetMessage("IBLOCK_XML2_VALUES_TYPE")
GetMessage("IBLOCK_XML2_VIEW")
*/
?>