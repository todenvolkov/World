<?
class CIBlockPriceTools
{
	function GetCatalogPrices($IBLOCK_ID, $arPriceCode)
	{
		global $USER;
		$arCatalogPrices = array();
		if(CModule::IncludeModule("catalog"))
		{
			$bFromCatalog = true;
			$arCatalogGroupCodesFilter = array();
			foreach($arPriceCode as $key => $value)
			{
				$t_value = trim($value);
				if(strlen($t_value) > 0)
					$arCatalogGroupCodesFilter[$value] = true;
			}
			$arCatalogGroupsFilter = array();
			$arCatalogGroups = CCatalogGroup::GetListArray();
			foreach($arCatalogGroups as $key => $value)
			{
				if(array_key_exists($value["NAME"], $arCatalogGroupCodesFilter))
				{
					$arCatalogGroupsFilter[] = $key;
					$arCatalogPrices[$value["NAME"]] = array(
						"ID" => htmlspecialchars($value["ID"]),
						"TITLE" => htmlspecialchars($value["NAME_LANG"]),
						"SELECT" => "CATALOG_GROUP_".$value["ID"],
					);
				}
			}
			$arPriceGroups = CCatalogGroup::GetGroupsPerms($USER->GetUserGroupArray(), $arCatalogGroupsFilter);
			foreach($arCatalogPrices as $name=>$value)
			{
				$arCatalogPrices[$name]["CAN_VIEW"]=in_array($value["ID"], $arPriceGroups["view"]);
				$arCatalogPrices[$name]["CAN_BUY"]=in_array($value["ID"], $arPriceGroups["buy"]);
			}
		}
		else
		{
			$bFromCatalog = false;
			$arPriceGroups = array(
				"view" => array(),
			);
			$rsProperties = CIBlockProperty::GetList(array(), array(
				"IBLOCK_ID"=>$IBLOCK_ID,
				"CHECK_PERMISSIONS"=>"N",
				"PROPERTY_TYPE"=>"N",
			));
			while($arProperty = $rsProperties->Fetch())
			{
				if($arProperty["MULTIPLE"]=="N" && in_array($arProperty["CODE"], $arPriceCode))
				{
					$arPriceGroups["view"][]=htmlspecialchars("PROPERTY_".$arProperty["CODE"]);
					$arCatalogPrices[$arProperty["CODE"]] = array(
						"ID"=>htmlspecialchars($arProperty["ID"]),
						"TITLE"=>htmlspecialchars($arProperty["NAME"]),
						"SELECT" => "PROPERTY_".$arProperty["ID"],
						"CAN_VIEW"=>true,
						"CAN_BUY"=>false,
					);
				}
			}
		}
		return $arCatalogPrices;
	}

	function GetItemPrices($IBLOCK_ID, $arCatalogPrices, $arItem, $bVATInclude = true)
	{
		global $USER;
		$arPrices = array();
		if(CModule::IncludeModule("catalog"))
		{
			foreach($arCatalogPrices as $key => $value)
			{
				if($value["CAN_VIEW"] && strlen($arItem["CATALOG_PRICE_".$value["ID"]]) > 0)
				{
					// get final price with VAT included.
					if ($arItem['CATALOG_VAT_INCLUDED'] != 'Y')
					{
						$arItem['CATALOG_PRICE_'.$value['ID']] *= (1 + $arItem['CATALOG_VAT'] * 0.01);
					}
					// so discounts will include VAT
					$arDiscounts = CCatalogDiscount::GetDiscount(
						$arItem["ID"],
						$arItem["IBLOCK_ID"],
						array($value["ID"]),
						$USER->GetUserGroupArray(),
						"N",
						SITE_ID,
						array()
					);
					$discountPrice = CCatalogProduct::CountPriceWithDiscount(
						$arItem["CATALOG_PRICE_".$value["ID"]],
						$arItem["CATALOG_CURRENCY_".$value["ID"]],
						$arDiscounts
					);
					// get clear prices WO VAT
					$arItem['CATALOG_PRICE_'.$value['ID']] /= (1 + $arItem['CATALOG_VAT'] * 0.01);
					$discountPrice /= (1 + $arItem['CATALOG_VAT'] * 0.01);

					$vat_value_discount = $discountPrice * $arItem['CATALOG_VAT'] * 0.01;
					$vat_discountPrice = $discountPrice + $vat_value_discount;

					$vat_value = $arItem['CATALOG_PRICE_'.$value['ID']] * $arItem['CATALOG_VAT'] * 0.01;
					$vat_price = $arItem["CATALOG_PRICE_".$value["ID"]] + $vat_value;

					$arPrices[$key] = array(
						"ID" => $arItem["CATALOG_PRICE_ID_".$value["ID"]],

						"VALUE_NOVAT" => $arItem["CATALOG_PRICE_".$value["ID"]],
						"PRINT_VALUE_NOVAT" => FormatCurrency($arItem["CATALOG_PRICE_".$value["ID"]],$arItem["CATALOG_CURRENCY_".$value["ID"]]),

						"VALUE_VAT" => $vat_price,
						"PRINT_VALUE_VAT" => FormatCurrency($vat_price, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

						"VATRATE_VALUE" => $vat_value,
						"PRINT_VATRATE_VALUE" => FormatCurrency($vat_value, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

						"DISCOUNT_VALUE_NOVAT" => $discountPrice,
						"PRINT_DISCOUNT_VALUE_NOVAT" => FormatCurrency($discountPrice, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

						"DISCOUNT_VALUE_VAT" => $vat_discountPrice,
						"PRINT_DISCOUNT_VALUE_VAT" => FormatCurrency($vat_discountPrice, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

						'DISCOUNT_VATRATE_VALUE' => $vat_value_discount,
						'PRINT_DISCOUNT_VATRATE_VALUE' => FormatCurrency($vat_value_discount, $arItem["CATALOG_CURRENCY_".$value["ID"]]),

						"CURRENCY" => $arItem["CATALOG_CURRENCY_".$value["ID"]],
						"CAN_ACCESS" => $arItem["CATALOG_CAN_ACCESS_".$value["ID"]],
						"CAN_BUY" => $arItem["CATALOG_CAN_BUY_".$value["ID"]],
					);

					if ($bVATInclude)
					{
						$arPrices[$key]['VALUE'] = $arPrices[$key]['VALUE_VAT'];
						$arPrices[$key]['PRINT_VALUE'] = $arPrices[$key]['PRINT_VALUE_VAT'];
						$arPrices[$key]['DISCOUNT_VALUE'] = $arPrices[$key]['DISCOUNT_VALUE_VAT'];
						$arPrices[$key]['PRINT_DISCOUNT_VALUE'] = $arPrices[$key]['PRINT_DISCOUNT_VALUE_VAT'];
					}
					else
					{
						$arPrices[$key]['VALUE'] = $arPrices[$key]['VALUE_NOVAT'];
						$arPrices[$key]['PRINT_VALUE'] = $arPrices[$key]['PRINT_VALUE_NOVAT'];
						$arPrices[$key]['DISCOUNT_VALUE'] = $arPrices[$key]['DISCOUNT_VALUE_NOVAT'];
						$arPrices[$key]['PRINT_DISCOUNT_VALUE'] = $arPrices[$key]['PRINT_DISCOUNT_VALUE_NOVAT'];
					}
				}
			}
		}
		else
		{
			foreach($arCatalogPrices as $key => $value)
			{
				if($value["CAN_VIEW"])
				{
					$arPrices[$key] = array(
						"ID" => $arItem["PROPERTY_".$value["ID"]."_VALUE_ID"],
						"VALUE" => round(doubleval($arItem["PROPERTY_".$value["ID"]."_VALUE"]),2),
						"PRINT_VALUE" => round(doubleval($arItem["PROPERTY_".$value["ID"]."_VALUE"]),2)." ".$arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
						"DISCOUNT_VALUE" => round(doubleval($arItem["PROPERTY_".$value["ID"]."_VALUE"]),2),
						"PRINT_DISCOUNT_VALUE" => round(doubleval($arItem["PROPERTY_".$value["ID"]."_VALUE"]),2)." ".$arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
						"CURRENCY" => $arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
						"CAN_ACCESS" => true,
						"CAN_BUY" => false,
					);
				}
			}
		}
		return $arPrices;
	}

	function CanBuy($IBLOCK_ID, $arCatalogPrices, $arItem)
	{
		if(is_array($arItem["PRICE_MATRIX"]))
		{
			return $arItem["PRICE_MATRIX"]["AVAILABLE"] == "Y";
		}
		else
		{
			foreach($arCatalogPrices as $code=>$arPrice)
			{
				if($arPrice["CAN_BUY"])
				{
					if(
						($arItem["CATALOG_QUANTITY_TRACE"] != "Y")
						|| (intval($arItem["CATALOG_QUANTITY"]) > 0)
					)
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	function GetProductProperties($IBLOCK_ID, $ELEMENT_ID, $arPropertiesList, $arPropertiesValues)
	{
		$arResult = array();
		foreach($arPropertiesList as $pid)
		{
			$prop = $arPropertiesValues[$pid];
			$arResult[$pid] = array("VALUES" => array(), "SELECTED" => false);
			$product_props = &$arResult[$pid];

			if($prop["MULTIPLE"] == "Y" && is_array($prop["VALUE"]))
			{
				switch($prop["PROPERTY_TYPE"])
				{
				case "S":
				case "N":
					foreach($prop["VALUE"] as $value)
					{
						if(strlen($value))
						{
							if($product_props["SELECTED"] === false)
								$product_props["SELECTED"] = $value;
							$product_props["VALUES"][$value] = $value;
						}
					}
					break;
				case "G":
					$ar = array();
					foreach($prop["VALUE"] as $value)
					{
						$value = intval($value);
						if($value > 0)
							$ar[] = $value;
					}
					$rsSections = CIBlockSection::GetList(array("LEFT_MARGIN"=>"ASC"), array("=ID"=>$ar));
					while($arSection = $rsSections->GetNext())
					{
						if($product_props["SELECTED"] === false)
							$product_props["SELECTED"] = $arSection["ID"];
						$product_props["VALUES"][$arSection["ID"]] = $arSection["NAME"];
					}
					break;
				case "E":
					$ar = array();
					foreach($prop["VALUE"] as $value)
					{
						$value = intval($value);
						if($value > 0)
							$ar[] = $value;
					}
					$rsElements = CIBlockElement::GetList(array("ID"=>"ASC"), array("=ID"=>$ar), false, false, array("ID", "NAME"));
					while($arElement = $rsElements->GetNext())
					{
						if($product_props["SELECTED"] === false)
							$product_props["SELECTED"] = $arElement["ID"];
						$product_props["VALUES"][$arElement["ID"]] = $arElement["NAME"];
					}
					break;
				case "L":
					foreach($prop["VALUE"] as $i => $value)
					{
						if($product_props["SELECTED"] === false)
							$product_props["SELECTED"] = $prop["VALUE_ENUM_ID"][$i];
						$product_props["VALUES"][$prop["VALUE_ENUM_ID"][$i]] = $value;
					}
					break;
				}
			}
			elseif($prop["MULTIPLE"] == "N")
			{
				switch($prop["PROPERTY_TYPE"])
				{
				case "L":
					$rsEnum = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC", "VALUE"=>"ASC"), array("IBLOCK_ID"=>$arParams["IBLOCK_ID"], "CODE"=>$pid));
					while($arEnum = $rsEnum->GetNext())
					{
						$product_props["VALUES"][$arEnum["ID"]] = $arEnum["VALUE"];
						if($arEnum["DEF"] == "Y")
							$product_props["SELECTED"] = $arEnum["ID"];
					}
					break;
				case "E":
					if($prop["LINK_IBLOCK_ID"] > 0)
					{
						$rsElements = CIBlockElement::GetList(
							array("NAME"=>"ASC", "SORT"=>"ASC"),
							array("IBLOCK_ID"=>$prop["LINK_IBLOCK_ID"], "ACTIVE"=>"Y"),
							false, false,
							array("ID", "NAME")
						);
						while($arElement = $rsElements->GetNext())
						{
							if($product_props["SELECTED"] === false)
								$product_props["SELECTED"] = $arElement["ID"];
							$product_props["VALUES"][$arElement["ID"]] = $arElement["NAME"];
						}
					}
					break;
				}
			}
		}
		return $arResult;
	}

	/*
	Checks arPropertiesValues against DB values
	returns array on success
	or number on fail (may be used for debug)
	*/
	function CheckProductProperties($IBLOCK_ID, $ELEMENT_ID, $arPropertiesList, $arPropertiesValues)
	{
		$SORT=1;
		$arResult = array();
		$param_props = array_flip($arPropertiesList);
		$rsProps = CIBlockElement::GetProperty($IBLOCK_ID, $ELEMENT_ID);
		while($arProp = $rsProps->Fetch())
		{
			if(in_array($arProp["CODE"], $arPropertiesList))
				$pid = $arProp["CODE"];
			elseif(in_array($arProp["ID"], $arPropertiesList))
				$pid = $arProp["CODE"];
			else
				continue; //Skip next. It's not an product property

			//Check if already handled
			if(!array_key_exists($pid, $param_props))
				continue;

			if(!strlen($arPropertiesValues[$pid])) //Property value MUST be there
			{
				return 1;
			}
			elseif($arProp["MULTIPLE"] == "Y")
			{
				switch($arProp["PROPERTY_TYPE"])
				{
				case "S":
				case "N":
					if($arProp["VALUE"] == $arPropertiesValues[$pid])
					{
						$arResult[] = array(
							"NAME" => $arProp["NAME"],
							"CODE" => $pid,
							"VALUE" => $arProp["VALUE"],
							"SORT" => $SORT++,
						);
						unset($param_props[$pid]);//mark as found
					}
					break;
				case "G":
					if($arProp["VALUE"] == $arPropertiesValues[$pid])
					{
						$rsSection = CIBlockSection::GetList(array(), array("=ID"=>$arProp["VALUE"]));
						if($arSection = $rsSection->Fetch())
						{
							$arResult[] = array(
								"NAME" => $arProp["NAME"],
								"CODE" => $pid,
								"VALUE" => $arSection["NAME"],
								"SORT" => $SORT++,
							);
							unset($param_props[$pid]);//mark as found
						}
					}
					break;
				case "E":
					if($arProp["VALUE"] == $arPropertiesValues[$pid])
					{
						$rsElement = CIBlockElement::GetList(array(), array("=ID"=>$arProp["VALUE"]), false, false, array("ID", "NAME"));
						if($arElement = $rsElement->Fetch())
						{
							$arResult[] = array(
								"NAME" => $arProp["NAME"],
								"CODE" => $pid,
								"VALUE" => $arElement["NAME"],
								"SORT" => $SORT++,
							);
							unset($param_props[$pid]);//mark as found
						}
					}
					break;
				case "L":
					if($arProp["VALUE"] == $arPropertiesValues[$pid])
					{
						$rsEnum = CIBlockPropertyEnum::GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_ID" => $pid, "ID" => $arPropertiesValues[$pid]));
						if($arEnum = $rsEnum->Fetch())
						{
							$arResult[] = array(
								"NAME" => $arProp["NAME"],
								"CODE" => $pid,
								"VALUE" => $arEnum["VALUE"],
								"SORT" => $SORT++,
							);
							unset($param_props[$pid]);//mark as found
						}
					}
					break;
				default:
					return 2;
				}
			}
			else
			{
				switch($arProp["PROPERTY_TYPE"])
				{
				case "L":
					$rsEnum = CIBlockPropertyEnum::GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_ID" => $pid, "ID" => $arPropertiesValues[$pid]));
					if($arEnum = $rsEnum->Fetch())
					{
						$arResult[] = array(
							"NAME" => $arProp["NAME"],
							"CODE" => $pid,
							"VALUE" => $arEnum["VALUE"],
							"SORT" => $SORT++,
						);
						unset($param_props[$pid]);//mark as found
					}
					break;
				case "E":
					if($arProp["LINK_IBLOCK_ID"] > 0)
					{
						$rsElement = CIBlockElement::GetList(array(), array("IBLOCK_ID"=>$arProp["LINK_IBLOCK_ID"], "ACTIVE" => "Y", "=ID" => $arPropertiesValues[$pid]), false, false, array("ID", "NAME"));
						if($arElement = $rsElement->Fetch())
						{
							$arResult[] = array(
								"NAME" => $arProp["NAME"],
								"CODE" => $pid,
								"VALUE" => $arElement["NAME"],
								"SORT" => $SORT++,
							);
							unset($param_props[$pid]);//mark as found
						}
					}
					break;
				default:
					return 3;
				}
			}
		}

		if(count($param_props))
			return 4;

		return $arResult;
	}
	
	function GetOffersIBlock($IBLOCK_ID)
	{
		$arResult = false;
		$IBLOCK_ID = intval($IBLOCK_ID);
		if (0 < $IBLOCK_ID)
		{
			if(CModule::IncludeModule("catalog"))
			{
				$arCatalog = CCatalog::GetByID($IBLOCK_ID);
				if (true == is_array($arCatalog))
				{
					if (0 < intval($arCatalog['OFFERS_IBLOCK_ID']))
					{
						$rsProps = CIBlockProperty::GetList(array(),array('IBLOCK_ID' => $arCatalog['OFFERS_IBLOCK_ID'],'PROPERTY_TYPE' => 'E','LINK_IBLOCK_ID' => $IBLOCK_ID,'ACTIVE' => 'Y','MULTIPLE' => 'N'));
						if ($arProp = $rsProps->Fetch())
						{
							$arResult = array(
								'OFFERS_IBLOCK_ID' => $arCatalog['OFFERS_IBLOCK_ID'],
								'OFFERS_PROPERTY_ID' => $arProp['ID'],
							);
						}
					}
				}
			}
		}
		return $arResult;
	}
}
?>