<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// *****************************************************************************************
//Входные параметры 
// $bVarsFromForm - 
// arUserField USER_TYPE - тип свойства
// arUserField VALUE- значение пользовательского свойства
// *****************************************************************************************
$arParams["bVarsFromForm"] = $arParams["bVarsFromForm"] ? true:false;
$arResult["VALUE"] = false;
// *****************************************************************************************
if($arParams["arUserField"]["USER_TYPE"])
{
	if($arParams["arUserField"]["ENTITY_VALUE_ID"]<1 && strlen($arParams["arUserField"]["SETTINGS"]["DEFAULT_VALUE"])>0)
		$arResult["VALUE"] = $arParams["~arUserField"]["SETTINGS"]["DEFAULT_VALUE"];
	else 
		$arResult["VALUE"] = $arParams["~arUserField"]["VALUE"];
		
	if (!is_array($arResult["VALUE"]))
		$arResult["VALUE"] = array($arResult["VALUE"]);
	if (empty($arResult["VALUE"]))
		$arResult["VALUE"] = array(null);
	$arResult["~VALUE"] = $arResult["VALUE"];
	if ($arParams["arUserField"]["USER_TYPE"]["BASE_TYPE"] == "enum")
	{
		$obEnum = new CUserFieldEnum;
		$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID"=>$arParams["arUserField"]["ID"]));
		$enum = array();
		while($arEnum = $rsEnum->GetNext())
		{
			$enum[$arEnum["ID"]] = $arEnum["VALUE"];
		}
		$arParams["arUserField"]["USER_TYPE"]["FIELDS"] = $enum;
	}

	foreach ($arResult["VALUE"] as $key => $res)
	{
		switch ($arParams["arUserField"]["USER_TYPE"]["BASE_TYPE"])
		{
			case "double":
				if (strlen($res)>0)
					$res = round(doubleval($res), $arParams["arUserField"]["SETTINGS"]["PRECISION"]);
				break;
			case "int":
				$res = intVal($res);
				break;
			case "enum":
				$res = strLen($enum[$res]) > 0 ? $enum[$res] : htmlspecialchars($res);
				break;
			default:
				$res = htmlspecialchars($res);
				break;
		}
		$arResult["VALUE"][$key] = $res;
	}
	$this->IncludeComponentTemplate();
}?>