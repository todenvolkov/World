<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$CATALOG_RIGHT = $APPLICATION->GetGroupRight("catalog");
if ($CATALOG_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

ClearVars("str_iblock_");
ClearVars("s_");

$sTableID = "tbl_sale_product_search";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$IBLOCK_ID = IntVal($IBLOCK_ID);

$dbIBlock = CIBlock::GetByID($IBLOCK_ID);
if (!($arIBlock = $dbIBlock->Fetch()))
{
	$arFilterTmp = array("MIN_PERMISSION"=>"R");

	$dbItem = CCatalog::GetList();
	while($arItems = $dbItem->Fetch())
		$arFilterTmp["ID"][] = $arItems["IBLOCK_ID"];
	
	$events = GetModuleEvents("sale", "OnProductSearchFormIBlock");
	if ($arEvent = $events->Fetch())
		$arFilterTmp = ExecuteModuleEvent($arEvent, $arFilterTmp);

	$dbIBlock = CIBlock::GetList(Array("NAME"=>"ASC"), $arFilterTmp);
	$arIBlock = $dbIBlock->Fetch();
	$IBLOCK_ID = IntVal($arIBlock["ID"]);
}

$func_name = preg_replace("/[^a-zA-Z0-9_-]/is", "", $func_name);

$BlockPerm = CIBlock::GetPermission($IBLOCK_ID);
$bBadBlock = ($BlockPerm < "R");

$BUYER_ID = IntVal($BUYER_ID);
$arBuyerGroups = CUser::GetUserGroup($BUYER_ID);

$QUANTITY = IntVal($QUANTITY);
if ($QUANTITY <= 0)
	$QUANTITY = 1;

if (!$bBadBlock)
{
	$arFilterFields = array(
		"filter_section",
		"filter_subsections", 
		"filter_id_start",
		"filter_id_end",	
		"filter_timestamp_from",
		"filter_timestamp_to",
		"filter_active",
		"filter_intext",
		"filter_name",
		"filter_xml_id"
	);

	$lAdmin->InitFilter($arFilterFields);

	$arFilter = array();

	$arFilter = array(
		"WF_PARENT_ELEMENT_ID" => false,
		"IBLOCK_ID" => $IBLOCK_ID,
		"SECTION_ID" => $filter_section,
		"ACTIVE" => $filter_active,
		"?NAME" => $filter_name,
		"?SEARCHABLE_CONTENT" => $filter_intext,
		"SHOW_NEW" => "Y"
	);

	
	if (IntVal($filter_section) < 0 || strlen($filter_section) <= 0)
		unset($arFilter["SECTION_ID"]);
	elseif ($filter_subsections=="Y")
	{
		if ($arFilter["SECTION_ID"]==0)
			unset($arFilter["SECTION_ID"]);
		else
			$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
	}

	if (!empty(${"filter_id_start"})) $arFilter[">=ID"] = ${"filter_id_start"};
	if (!empty(${"filter_id_end"})) $arFilter["<=ID"] = ${"filter_id_end"};
	if (!empty(${"filter_timestamp_from"})) $arFilter["DATE_MODIFY_FROM"] = ${"filter_timestamp_from"};
	if (!empty(${"filter_timestamp_to"})) $arFilter["DATE_MODIFY_TO"] = ${"filter_timestamp_to"};
	if (!empty(${"filter_xml_id"})) $arFilter["XML_ID"] = ${"filter_xml_id"};

	$dbResultList = CIBlockElement::GetList(
		array($by => $order),
		$arFilter,
		false,
		false,
		${"filter_count_for_show"}
	);

	$dbResultList = new CAdminResult($dbResultList, $sTableID);
	$dbResultList->NavStart();

	$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("sale_prod_search_nav")));

	$arHeaders = array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
		array("id"=>"ACTIVE", "content"=>GetMessage("SPS_ACT"), "sort"=>"active", "default"=>true),
		array("id"=>"NAME", "content"=>GetMessage("SPS_NAME"), "sort"=>"name", "default"=>true),
		array("id"=>"PRICE", "content"=>GetMessage("SOPS_PRICE"), "default"=>true),
		array("id"=>"ACT", "content"=>"&nbsp;", "default"=>true),
	);

	$lAdmin->AddHeaders($arHeaders);

	$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

	$arDiscountCoupons = array();
	if (isset($BUYER_COUPONS) && strlen($BUYER_COUPONS) > 0)
	{
		$arBuyerCoupons = explode(",", $BUYER_COUPONS);
		for ($i = 0; $i < count($arBuyerCoupons); $i++)
		{
			$arBuyerCoupons[$i] = Trim($arBuyerCoupons[$i]);
			if (strlen($arBuyerCoupons[$i]) > 0)
				$arDiscountCoupons[] = $arBuyerCoupons[$i];
		}
	}

	while ($arItems = $dbResultList->NavNext(true, "f_"))
	{
		$row =& $lAdmin->AddRow($f_ID, $arItems);

		$row->AddField("ID", $f_ID);
		$row->AddField("ACTIVE", $f_ACTIVE);
		$row->AddField("NAME", $f_NAME);

		$fieldValue = "";
		if (in_array("PRICE", $arVisibleColumns))
		{
			$nearestQuantity = $QUANTITY;
			$arPrice = CCatalogProduct::GetOptimalPrice($f_ID, $nearestQuantity, $arBuyerGroups, "N", array(), false, $arDiscountCoupons);
			if (!$arPrice || count($arPrice) <= 0)
			{
				if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($f_ID, $nearestQuantity, $arBuyerGroups))
					$arPrice = CCatalogProduct::GetOptimalPrice($f_ID, $nearestQuantity, $arBuyerGroups, "N", array(), false, $arDiscountCoupons);
			}
			
			if (!$arPrice || count($arPrice) <= 0)
			{
				$fieldValue = "&nbsp;";
			}
			else
			{
				$currentPrice = $arPrice["PRICE"]["PRICE"];
				
				if($arPrice["PRICE"]["VAT_INCLUDED"] == "N" && DoubleVal($arPrice["PRICE"]["VAT_RATE"]) > 0 )
						$currentPrice = (1+DoubleVal($arPrice["PRICE"]["VAT_RATE"])) * $currentPrice;
						
				$currentDiscount = 0.0;
				if (isset($arPrice["DISCOUNT"]) && count($arPrice["DISCOUNT"]) > 0)
				{
					if ($arPrice["DISCOUNT"]["VALUE_TYPE"]=="F")
					{
						if ($arPrice["DISCOUNT"]["CURRENCY"] == $arPrice["PRICE"]["CURRENCY"])
							$currentDiscount = $arPrice["DISCOUNT"]["VALUE"];
						else
							$currentDiscount = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT"]["VALUE"], $arPrice["DISCOUNT"]["CURRENCY"], $arPrice["PRICE"]["CURRENCY"]);
					}
					else
						$currentDiscount = $currentPrice * $arPrice["DISCOUNT"]["VALUE"] / 100.0;

					$currentDiscount = roundEx($currentDiscount, SALE_VALUE_PRECISION);
					$currentPrice = $currentPrice - $currentDiscount;
				}
				$fieldValue = FormatCurrency($currentPrice, $arPrice["PRICE"]["CURRENCY"]);
				if (DoubleVal($nearestQuantity) != DoubleVal($QUANTITY))
					$fieldValue .= str_replace("#CNT#", $nearestQuantity, GetMessage("SOPS_PRICE1"));
			}
		}
		$row->AddField("PRICE", $fieldValue);

		$arCatalogProduct = CCatalogProduct::GetByID($f_ID);
		$URL = CIBlock::ReplaceDetailUrl($arItems["DETAIL_PAGE_URL"], $arItems, true);

		$arParams = "{'id' : '".$arItems["ID"]."', 'name' : '".htmlspecialchars(str_replace("'", "\'", str_replace("\\", "\\\\", $arItems["NAME"])))."', 'url' : '".htmlspecialchars(str_replace("'", "\'", str_replace("\\", "\\\\", $URL)))."', 'price' : '".htmlspecialchars(str_replace("'", "\'", str_replace("\\", "\\\\", $currentPrice)))."', 'currency' : '".htmlspecialchars(str_replace("'", "\'", str_replace("\\", "\\\\", $arPrice["PRICE"]["CURRENCY"])))."', 'weight' : '".IntVal($arCatalogProduct["WEIGHT"])."', 'priceType' : '".htmlspecialchars(str_replace("'", "\'", str_replace("\\", "\\\\", $arPrice["PRICE"]["CATALOG_GROUP_NAME"])))."', 'catalogXmlID' : '".htmlspecialchars(str_replace("'", "\'", str_replace("\\", "\\\\", $arIBlock["XML_ID"])))."', 'productXmlID' : '".htmlspecialchars(str_replace("'", "\'", str_replace("\\", "\\\\", $f_XML_ID)))."', 'callback' : 'CatalogBasketCallback',	'orderCallback' : 'CatalogBasketOrderCallback', 'cancelCallback' : 'CatalogBasketCancelCallback', 'payCallback' : 'CatalogPayOrderCallback'}";

		$events = GetModuleEvents("sale", "OnProductSearchForm");
		if ($arEvent = $events->Fetch())
			$arParams = ExecuteModuleEvent($arEvent, $f_ID, $arParams);

		$row->AddField("ACT", "<a href=\"javascript:void(0)\" onClick=\"SelEl(".$arParams.")\">".GetMessage("SPS_SELECT")."</a>");
	}

	$lAdmin->AddFooter(
		array(
			array(
				"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
				"value" => $dbResultList->SelectedRowsCount()
			),
		)
	);
}
else
{
	echo ShowError(GetMessage("SPS_NO_PERMS").".");
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SPS_SEARCH_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

?>

<script language="JavaScript">
<!--
function SelEl(arParams)
{
	window.opener.<?= $func_name ?>(<?= IntVal($index) ?>, arParams, <?= IntVal($IBLOCK_ID) ?>);
	window.close();
}
//-->
</script>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
	<input type="hidden" name="field_name" value="<?echo htmlspecialchars($field_name)?>">
	<input type="hidden" name="field_name_name" value="<?echo htmlspecialchars($field_name_name)?>">
	<input type="hidden" name="field_name_url" value="<?echo htmlspecialchars($field_name_url)?>">
	<input type="hidden" name="alt_name" value="<?echo htmlspecialchars($alt_name)?>">
	<input type="hidden" name="form_name" value="<?echo htmlspecialchars($form_name)?>">
	<input type="hidden" name="func_name" value="<?echo htmlspecialchars($func_name)?>">
	<input type="hidden" name="index" value="<?echo htmlspecialchars($index)?>">
	<input type="hidden" name="BUYER_ID" value="<?echo htmlspecialchars($BUYER_ID)?>">
	<input type="hidden" name="QUANTITY" value="<?echo htmlspecialchars($QUANTITY)?>">
<?
$arIBTYPE = CIBlockType::GetByIDLang($arIBlock["IBLOCK_TYPE_ID"], LANG);

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID (".GetMessage("SPS_ID_FROM_TO").")",
		GetMessage("SPS_XML_ID"),
		GetMessage("SPS_TIMESTAMP"),
		($arIBTYPE["SECTIONS"]=="Y"? GetMessage("SPS_SECTION") : null),
		GetMessage("SPS_ACTIVE"),
		GetMessage("SPS_NAME"),
		GetMessage("SPS_DESCR"),
	)
);

$oFilter->Begin();
?>
	<tr> 
		<td><?= GetMessage("SPS_CATALOG") ?>:</td>
		<td>
			<select name="IBLOCK_ID">
			<?
			$catalogID = Array();
			$dbItem = CCatalog::GetList();
			while($arItems = $dbItem->Fetch())
				$catalogID[] = $arItems["IBLOCK_ID"];
			$db_iblocks = CIBlock::GetList(Array("ID"=>"ASC"), Array("ID" => $catalogID));
			while ($db_iblocks->ExtractFields("str_iblock_"))
			{
				?><option value="<?=$str_iblock_ID?>"<?if($IBLOCK_ID==$str_iblock_ID)echo " selected"?>><?=$str_iblock_NAME?> [<?=$str_iblock_LID?>] (<?=$str_iblock_ID?>)</option><?
			}
			?>
			</select>
		</td>
	</tr>

	<tr>
		<td>ID (<?= GetMessage("SPS_ID_FROM_TO") ?>):</td>
		<td>
			<nobr>
			<input type="text" name="filter_id_start" size="10" value="<?echo htmlspecialcharsex($filter_id_start)?>">
			...
			<input type="text" name="filter_id_end" size="10" value="<?echo htmlspecialcharsex($filter_id_end)?>">
			</nobr>
		</td>
	</tr>

	<tr>
		<td nowrap><?= GetMessage("SPS_XML_ID") ?>:</td>
		<td nowrap>
			<input type="text" name="filter_xml_id" size="50" value="<?echo htmlspecialcharsex(${"filter_xml_id"})?>">
		</td>
	</tr>

	<tr>
		<td  nowrap><?= GetMessage("SPS_TIMESTAMP") ?> (<?= CLang::GetDateFormat("SHORT") ?>):</td>
		<td nowrap><? echo CalendarPeriod("filter_timestamp_from", htmlspecialcharsex($filter_timestamp_from), "filter_timestamp_to", htmlspecialcharsex($filter_timestamp_to), "form1")?></td>
	</tr>

<?
if ($arIBTYPE["SECTIONS"]=="Y"):
?>
		<tr>
			<td nowrap valign="top"><?= GetMessage("SPS_SECTION") ?>:</td>
			<td nowrap>
				<select name="filter_section">
					<option value="">(<?= GetMessage("SPS_ANY") ?>)</option>
					<option value="0"<?if($filter_section=="0")echo" selected"?>><?= GetMessage("SPS_TOP_LEVEL") ?></option>
					<?
					$bsections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID));
					while($bsections->ExtractFields("s_")):
						?><option value="<?echo $s_ID?>"<?if($s_ID==$filter_section)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $s_DEPTH_LEVEL)?><?echo $s_NAME?></option><?
					endwhile;
					?>
				</select><br>
				<input type="checkbox" name="filter_subsections" value="Y"<?if($filter_subsections=="Y")echo" checked"?>> <?= GetMessage("SPS_INCLUDING_SUBS") ?>
			</td>
		</tr>
<?
endif;
?>

	<tr>
		<td nowrap><?= GetMessage("SPS_ACTIVE") ?>:</td>
		<td nowrap>
			<select name="filter_active">
				<option value=""><?=htmlspecialcharsex("(".GetMessage("SPS_ANY").")")?></option>
				<option value="Y"<?if($filter_active=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("SPS_YES"))?></option>
				<option value="N"<?if($filter_active=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("SPS_NO"))?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td nowrap><?= GetMessage("SPS_NAME") ?>:</td>
		<td nowrap>
			<input type="text" name="filter_name" value="<?echo htmlspecialcharsex($filter_name)?>" size="30">
		</td>
	</tr>
	<tr>
		<td nowrap><?= GetMessage("SPS_DESCR") ?>:</td>
		<td nowrap>
			<input type="text" name="filter_intext" size="50" value="<?echo htmlspecialcharsex(${"filter_intext"})?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
<?
$oFilter->Buttons();
?>
<input type="submit" name="set_filter" value="<?echo GetMessage("prod_search_find")?>" title="<?echo GetMessage("prod_search_find_title")?>">
<input type="submit" name="del_filter" value="<?echo GetMessage("prod_search_cancel")?>" title="<?echo GetMessage("prod_search_cancel_title")?>">
<?
$oFilter->End();
?>

<br>
<table>
<tr>
	<td><?= GetMessage("SOPS_COUPON") ?>:</td>
	<td><input type="text" name="BUYER_COUPONS" size="30" value="<?= htmlspecialchars($BUYER_COUPONS) ?>"></td>
	<td><input type="submit" value="<?= GetMessage("SOPS_APPLY") ?>"></td>
</tr>
</table>

</form>

<?
$lAdmin->DisplayList();
?>
<br>
<input type="button" class="typebutton" value="<?= GetMessage("SPS_CLOSE") ?>" onClick="window.close();">
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");?>