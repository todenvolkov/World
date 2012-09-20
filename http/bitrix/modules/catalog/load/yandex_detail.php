<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

__IncludeLang(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/lang/", "/export_yandex.php"));

$APPLICATION->SetTitle(GetMessage('YANDEX_DETAIL_TITLE'));

CModule::IncludeModule('iblock');
CModule::IncludeModule('currency');
CModule::IncludeModule('catalog');

$IBLOCK_ID = intval($_REQUEST['IBLOCK']);

$strError = '';
if (!$USER->CanDoOperation('catalog_export_edit'))
	$strError = GetMessage('YANDEX_ERR_NO_ACCESS_EXPORT');
elseif ($IBLOCK_ID <= 0)
	$strError = GetMessage('YANDEX_ERR_NO_IBLOCK_CHOSEN');
else
{
	$dbRes = CIBlock::GetByID($IBLOCK_ID);

	if (!($IBLOCK = $dbRes->Fetch()))
		$strError = GetMessage('YANDEX_ERR_NO_IBLOCK_FOUND');
	elseif (CIBlock::GetPermission($IBLOCK_ID) < 'R')
		$strError = GetMessage('YANDEX_ERR_NO_ACCESS_IBLOCK');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && strlen($_REQUEST['save'])>0)
{
	$arCurrency = array('RUB' => array('rate' => 1));
	if (is_array($_POST['CURRENCY']) && count($_POST['CURRENCY']) > 0)
	{
		$arCurrency = array();
		foreach ($_POST['CURRENCY'] as $CURRENCY)
		{
			$arCurrency[$CURRENCY] = array(
				'rate' => $_POST['CURRENCY_RATE'][$CURRENCY],
				'plus' => $_POST['CURRENCY_PLUS'][$CURRENCY]
			);
		}
	}

	$type = $_POST['type'];
	$XML_DATA = array_merge(/*$_POST['XML_DATA']['common'], */$_POST['XML_DATA'][$type], $_POST['XML_DATA']['params']);
	
	foreach ($XML_DATA as $key => $value)
	{
		if (!$value) unset($XML_DATA[$key]);
	}
	
	$arXMLData = array(
		'TYPE' => $type,
		'XML_DATA' => $XML_DATA,
		'CURRENCY' => $arCurrency,
		'PRICE' => intval($_POST['PRICE']),
	);
?>
<script type="text/javascript">
top.BX.closeWait();
top.BX.WindowManager.Get().Close();
top.setDetailData('<?=CUtil::JSEscape(serialize($arXMLData))?>');
</script>
<?
	die();
}

if ($strError)
{
?>
<script type="text/javascript">
var obDialog = BX.WindowManager.Get();
obDialog.Close();
obDialog.ShowError('<?=CUtil::JSEscape($strError);?>');
</script>
<?
	die();
}

$dbRes = CIBlockProperty::GetList(
	array('sort' => 'asc', 'id' => 'asc'),
	array('ACTIVE' => 'Y', 'IBLOCK_ID' => $IBLOCK_ID)
);
$IBLOCK['PROPERTY'] = array();
while ($arRes = $dbRes->Fetch())
{
	$IBLOCK['PROPERTY'][$arRes['CODE']] = $arRes;
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage('YANDEX_TAB1_TITLE'), "TITLE" => GetMessage('YANDEX_TAB1_DESC')),
	array("DIV" => "edit2", "TAB" => GetMessage('YANDEX_TAB2_TITLE'), "TITLE" => GetMessage('YANDEX_TAB2_DESC')),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

/*
$arCommonConfig = array(
	'country_of_origin',
);
*/

$arTypesConfig = array(
	'vendor.model' => array(
		'vendor', 'vendorCode', 'model', 'manufacturer_warranty',
	),
	'book' => array(
		'author', 'publisher', 'series', 'year', 'ISBN', 'volume', 'part', 'language', 'binding', 'page_extent', 'table_of_contents',
	),
	'audiobook' => array(
		'author', 'publisher', 'series', 'year', 'ISBN', 'performed_by', 'performance_type', 'language', 'volume', 'part', 'format', 'storage', 'recording_length', 'table_of_contents',
	),
	'artist.title' => array(
		'title', 'artist', 'director', 'starring', 'originalName', 'country', 'year', 'media',
	),
	
	// a bit later
/*	
	'tour' => array(
		'worldRegion', 'country', 'region', 'days', 'dataTour', 'hotel_stars', 'room', 'meal', 'included', 'transport',
	),
	'event-ticket' => array(
		'place', 'hall', 'date', 'is_premiere', 'is_kids',
	),
*/
);

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

function __yand_show_selector($group, $key, $IBLOCK)
{
?><select name="XML_DATA[<?=htmlspecialchars($group)?>][<?=htmlspecialchars($key)?>]">
		<option value=""><?=GetMessage('YANDEX_SKIP_PROP')?></option>
<?
	foreach ($IBLOCK['PROPERTY'] as $key => $arProp):
?>
		<option value="<?=$arProp['ID']?>">[<?=htmlspecialchars($key)?>] <?=htmlspecialchars($arProp['NAME'])?></option>
<?
	endforeach;
?>
	</select><?
}

/***************************************************************************
							   HTML form
****************************************************************************/
?>
<script type="text/javascript">
var currentSelectedType = 'none';

function switchType(type)
{
	BX('config_' + currentSelectedType).style.display = 'none';
	currentSelectedType = type;
	BX('config_' + currentSelectedType).style.display = 'block';
}
</script>
<form name="yandex_form" method="POST">
	<input type="hidden" name="Update" value="Y" />
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td><?=GetMessage('YANDEX_TYPE')?>: </td>
		<td><select name="type" onchange="switchType(this[this.selectedIndex].value)">
			<option value="none"><?=GetMessage('YANDEX_TYPE_SIMPLE');?></option>
<?
foreach ($arTypesConfig as $key => $arConfig):
?>
			<option value="<?=$key?>"><?=$key?></option>
<?
endforeach;
?>
		</select></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
<?echo BeginNote(), GetMessage('YANDEX_TYPE_NOTE'), EndNote();?>
		</td>
	</tr>
<?/*
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_PROPS_COMMON')?></td>
	</tr>
	<tr>
		<td colspan="2">
			<div id="config_common" style="padding: 10px;">
				<table width="75%" class="inner" align="center">
<?
foreach ($arCommonConfig as $prop):
?>
					<tr>
						<td align="right"><?=htmlspecialchars(GetMessage('YANDEX_PROP_'.$prop))?>: </td>
						<td><?__yand_show_selector('common', $prop, $IBLOCK)?> <small>(<?=htmlspecialchars($prop)?>)</small></td>
					</tr>
<?
endforeach;
?>
				</table>
			</div>
		</td>
	</tr>
*/?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_PROPS_TYPE')?></td>
	</tr>
	<tr>
		<td colspan="2">
			<div id="config_none" style="text-align: center; padding: 10px;">
				<?=GetMessage('YANDEX_PROPS_NO')?>
			</div>
<?
foreach ($arTypesConfig as $key => $arConfig):
?>
			<div id="config_<?=htmlspecialchars($key)?>" style="display: none; padding: 10px;">
				<table width="75%" class="inner" align="center">
<?
	foreach ($arConfig as $prop):
?>
	<tr>
		<td align="right"><?=htmlspecialchars(GetMessage('YANDEX_PROP_'.$prop))?>: </td>
		<td><?__yand_show_selector($key, $prop, $IBLOCK)?> <small>(<?=htmlspecialchars($prop)?>)</small></td>
	</tr>
<?
	endforeach;
?>
				</table>
			</div>
<?
endforeach;
?>
		
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2" valign="top"><?=GetMessage('YANDEX_PROPS_ADDITIONAL')?></td>
	</tr>
	<tr>
		<td colspan="2">
<script type="text/javascript">
function addYandexParam()
{
	var oCont = BX('yandex_add_params');
	var oNew = BX.clone(oCont);
	oCont.id = "";
	BX.findChild(oNew, {tag: 'SELECT'}, false).selectedIndex = BX.findChild(oCont, {tag: 'SELECT'}, false).selectedIndex;
	oCont.parentNode.appendChild(oNew);
}
</script>
			<div id="config_param" style="padding: 10px;">
				<table width="75%" class="inner" align="center">
					<tbody>
						<tr>
							<td valign="top" align="right"><?=GetMessage('YANDEX_PROPS_ADDITIONAL_TITLE')?>: </td>
							<td>
								<div id="yandex_add_params">
<?
__yand_show_selector('params', 'PARAMS][', $IBLOCK);
echo ' <small>(param)</small><br />';
?>
								</div>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td></td>
							<td><button onclick="addYandexParam(); return false;"><?=GetMessage('YANDEX_PROPS_ADDITIONAL_MORE')?></button></td>
						</tr>
					</tfoot>
				</table>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
<?
$tabControl->BeginNextTab();

$arGroups = '';
$dbRes = CCatalogGroup::GetGroupsList(array("GROUP_ID"=>2));
while ($arRes = $dbRes->Fetch()) 
{
	if ($arRes['BUY'] == 'Y')
		$arGroups[] = $arRes['CATALOG_GROUP_ID'];
}
?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_PRICES')?></td>
	</tr>

	<tr>
		<td><?=GetMessage('YANDEX_PRICE_TYPE');?>: </td>
		<td><br /><select name="PRICE">
			<option value=""><?=GetMessage('YANDEX_PRICE_TYPE_NONE');?></option>
<?
$dbRes = CCatalogGroup::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y', 'ID' => $arGroups), 0, 0, array('ID', 'NAME', 'BASE'));
while ($arRes = $dbRes->GetNext())
{
?>
			<option value="<?=$arRes['ID']?>"><?='['.$arRes['ID'].'] '.$arRes['NAME'];?></option>
<?
}
?>
		</select><br /><br /></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_CURRENCIES')?></td>
	</tr>

	<tr>
		<td colspan="2"><br />
<?
$arCurrencyList = array();
$arCurrencyAllowed = array('RUR', 'RUB', 'USD', 'EUR', 'UAH', 'BYR', 'KZT');
$dbRes = CCurrency::GetList($by = 'sort', $order = 'asc');
while ($arRes = $dbRes->GetNext())
{
	if (in_array($arRes['CURRENCY'], $arCurrencyAllowed))
		$arCurrencyList[$arRes['CURRENCY']] = $arRes['FULL_NAME'];
}

$arValues = array(
	'SITE' => GetMessage('YANDEX_CURRENCY_RATE_SITE'),
	'CBRF' => GetMessage('YANDEX_CURRENCY_RATE_CBRF'),
	'NBU' => GetMessage('YANDEX_CURRENCY_RATE_NBU'),
	'NBK' => GetMessage('YANDEX_CURRENCY_RATE_NBK'),
	'CB' => GetMessage('YANDEX_CURRENCY_RATE_CB')
);
?>
<table cellpadding="2" cellspacing="0" border="0" class="internal" align="center">
<thead>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('YANDEX_CURRENCY')?></td>
		<td><?=GetMessage('YANDEX_CURRENCY_RATE')?></td>
		<td><?=GetMessage('YANDEX_CURRENCY_PLUS')?></td>
	</tr>
</thead>
<tbody>
<?
foreach ($arCurrencyList as $CURRENCY => $CURRENCY_NAME)
{
?>
	<tr>
		<td><input type="checkbox" name="CURRENCY[]" id="CURRENCY_<?=$CURRENCY?>" value="<?=$CURRENCY?>" checked="checked" /></td>
		<td><label for="CURRENCY_<?=$CURRENCY?>" class="text">[<?=$CURRENCY?>] <?=$CURRENCY_NAME?></label></td>
		<td><select name="CURRENCY_RATE[<?=$CURRENCY?>]" onchange="BX('CURRENCY_PLUS_<?=$CURRENCY?>').disabled = this.selectedIndex == 0">
<?
	foreach ($arValues as $key => $title)
	{
?>
			<option value="<?=htmlspecialchars($key)?>">(<?=htmlspecialchars($key)?>) <?=htmlspecialchars($title)?></option>
<?
	}
?>
		</select></td>
		<td>+<input type="text" size="3" id="CURRENCY_PLUS_<?=$CURRENCY?>" name="CURRENCY_PLUS[<?=$CURRENCY?>]" disabled="disabled" />%</td>
	</tr>
<?
}
?>
</tbody>
</table>

		</td>
	</tr>
<?
$tabControl->EndTab();
$tabControl->Buttons(array());
$tabControl->End();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
