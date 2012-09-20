<?
//$CATALOG_RIGHT = $APPLICATION->GetGroupRight("catalog");
//if ($CATALOG_RIGHT=="W"):
if ($USER->CanDoOperation('catalog_price')) :
//****************************************************************

include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/templates/product_edit_action.php"));

$arCatalogBasePrices = array();
$arCatalogPrices = array();

$CAT_ROW_COUNTER = IntVal($CAT_ROW_COUNTER);
if ($CAT_ROW_COUNTER < 0)
	$strWarning .= GetMessage("C2IT_INTERNAL_ERROR")."<br>";

$arCatalogBaseGroup = CCatalogGroup::GetBaseGroup();
if (!$arCatalogBaseGroup)
	$strWarning .= GetMessage("C2IT_NO_BASE_TYPE")."<br>";

$CAT_VAT_ID = intval($CAT_VAT_ID);
$CAT_VAT_INCLUDED = !isset($CAT_VAT_INCLUDED) || $CAT_VAT_INCLUDED == 'N' ? 'N' : 'Y';

$bOffersUsed = ((true == isset($_POST['offers_used'])) && ('Y' == trim($_POST['offers_used'])));

$bUseExtForm = ($_POST['price_useextform'] == 'Y');

for ($i = 0; $i <= $CAT_ROW_COUNTER; $i++)
{
	${"CAT_BASE_PRICE_".$i} = str_replace(",", ".", ${"CAT_BASE_PRICE_".$i});

	if (IntVal(${"CAT_BASE_QUANTITY_FROM_".$i}) > 0
		|| IntVal(${"CAT_BASE_QUANTITY_TO_".$i}) > 0
		|| strlen(${"CAT_BASE_PRICE_".$i}) > 0)
	{
		$arCatalogBasePrices[] = array(
				"ID" => IntVal($CAT_BASE_ID[$i]),
				"IND" => $i,
				"QUANTITY_FROM" => $bUseExtForm ? IntVal(${"CAT_BASE_QUANTITY_FROM_".$i}) : '',
				"QUANTITY_TO" => $bUseExtForm ? IntVal(${"CAT_BASE_QUANTITY_TO_".$i}) : '',
				"PRICE" => ($bUseExtForm || $i == 0) ? ${"CAT_BASE_PRICE_".$i} : '',
				"CURRENCY" => ${"CAT_BASE_CURRENCY_".$i}
			);
	}
}

if ($bUseExtForm && count($arCatalogBasePrices) > 0)
{
	for ($i = 0; $i < count($arCatalogBasePrices) - 1; $i++)
	{
		for ($j = $i + 1; $j < count($arCatalogBasePrices); $j++)
		{
			if ($arCatalogBasePrices[$i]["QUANTITY_FROM"] > $arCatalogBasePrices[$j]["QUANTITY_FROM"])
			{
				$tmp = $arCatalogBasePrices[$i];
				$arCatalogBasePrices[$i] = $arCatalogBasePrices[$j];
				$arCatalogBasePrices[$j] = $tmp;
			}
		}
	}

	for ($i = 0, $cnt = count($arCatalogBasePrices); $i < $cnt; $i++)
	{
		if ($i != 0 && $arCatalogBasePrices[$i]["QUANTITY_FROM"] <= 0
			|| $i == 0 && $arCatalogBasePrices[$i]["QUANTITY_FROM"] < 0)
			$strWarning .= str_replace("#BORDER#", $arCatalogBasePrices[$i]["QUANTITY_FROM"], GetMessage("C2IT_ERROR_BOUND_LEFT"))."<br>";

		if ($i != $cnt-1 && $arCatalogBasePrices[$i]["QUANTITY_TO"] <= 0
			|| $i == $cnt-1 && $arCatalogBasePrices[$i]["QUANTITY_TO"] < 0)
			$strWarning .= str_replace("#BORDER#", $arCatalogBasePrices[$i]["QUANTITY_TO"], GetMessage("C2IT_ERROR_BOUND_RIGHT"))."<br>";

		if ($arCatalogBasePrices[$i]["QUANTITY_FROM"] > $arCatalogBasePrices[$i]["QUANTITY_TO"]
			&& ($i != $cnt-1 || $arCatalogBasePrices[$i]["QUANTITY_TO"] > 0))
			$strWarning .= str_replace("#DIAP#", $arCatalogBasePrices[$i]["QUANTITY_FROM"]."-".$arCatalogBasePrices[$i]["QUANTITY_TO"], GetMessage("C2IT_ERROR_BOUND"))."<br>";

		if ($i < $cnt-1 && $arCatalogBasePrices[$i]["QUANTITY_TO"] >= $arCatalogBasePrices[$i+1]["QUANTITY_FROM"])
			$strWarning .= str_replace("#DIAP1#", $arCatalogBasePrices[$i]["QUANTITY_FROM"]."-".$arCatalogBasePrices[$i]["QUANTITY_TO"], str_replace("#DIAP2#", $arCatalogBasePrices[$i+1]["QUANTITY_FROM"]."-".$arCatalogBasePrices[$i+1]["QUANTITY_TO"], GetMessage("C2IT_ERROR_BOUND_CROSS")))."<br>";

		if ($i < $cnt-1
			&& $arCatalogBasePrices[$i+1]["QUANTITY_FROM"] - $arCatalogBasePrices[$i]["QUANTITY_TO"] > 1)
			$strWarning .= str_replace("#DIAP1#", ($arCatalogBasePrices[$i]["QUANTITY_TO"] + 1)."-".($arCatalogBasePrices[$i+1]["QUANTITY_FROM"] - 1), GetMessage("C2IT_ERROR_BOUND_MISS"))."<br>";
		
		if ($i >= $cnt-1
			&& $arCatalogBasePrices[$i]["QUANTITY_TO"] > 0)
			$strWarning .= str_replace("#BORDER#", $arCatalogBasePrices[$i]["QUANTITY_TO"], GetMessage("C2IT_ERROR_BOUND_MISS_TOP"))."<br>";
			
		if (doubleval($arCatalogBasePrices[$i]["PRICE"]) <= 0)
			$strWarning .= (
				$i == 0 && $cnt == 1 ?
				GetMessage('C2IT_ERROR_PRICE') :
				str_replace("#DIAP#", $arCatalogBasePrices[$i]["QUANTITY_FROM"]."-".$arCatalogBasePrices[$i]["QUANTITY_TO"], GetMessage("C2IT_ERROR_BOUND_PRICE"))
				)."<br>";
	}
}

if (count($arCatalogBasePrices) <= 0)
{
	if (false == $bOffersUsed)
	{
		$strWarning .= '!!!'.GetMessage("C2IT_ERROR_PRICE").'!!!<br>';
	}
}
//****************************************************************
endif;
?>