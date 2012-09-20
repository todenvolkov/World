<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$SALE_RIGHT = $APPLICATION->GetGroupRight("sale");
if ($SALE_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
$APPLICATION->SetTitle(GetMessage("SS_TITLE"));
if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminPage->ShowSectionIndex("menu_sale_settings", "sale");

if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>

<?echo BeginNote('width="100%"');?>

<a href="javascript:void(0);" onClick="this.innerHTML=(jsUtils.ToggleDiv('text_description')? '<?echo GetMessage("sale_settings_hide")?>' : '<?echo GetMessage("sale_settings_how")?>');" title="<?echo GetMessage("sale_settings_title")?>" class="control"><?echo GetMessage("sale_settings_how")?></a>

<div class="ruler" id="text_description" style="display:none;">
<p><a href="/bitrix/admin/sale_discount.php?lang=<?= LANG ?>"><b><?echo GetMessage("sale_settings_disc")?></b></a><br>
<?echo GetMessage("sale_settings_disc_text")?>
</p>
<p><a href="/bitrix/admin/sale_delivery.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_DELIVERY")?></b></a><br>
<?echo GetMessage("SS_DELIVERY_DESCR")?>
</p>
<p><a href="/bitrix/admin/sale_pay_system.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_PAY_SYS")?></b></a><br>
<?echo GetMessage("SS_PAY_SYS_DESCR")?>
</p>
<p><a href="/bitrix/admin/sale_person_type.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_PERSON_TYPE")?></b></a><br>
<?echo GetMessage("SS_PERSON_TYPE_DESCR")?>
</p>
<p><a href="/bitrix/admin/sale_status.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_STATUS")?></b></a><br>
<?echo GetMessage("SS_STATUS_DESCR")?>
</p>
<p><a href="/bitrix/admin/sale_order_props.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_ORDER_PROPS")?></b></a><br>
<?echo GetMessage("SS_ORDER_PROPS_DESCR")?>
</p>
<p><a href="/bitrix/admin/sale_order_props_group.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_ORDER_PROPS_GR")?></b></a><br>
<?echo GetMessage("SS_ORDER_PROPS_GR_DESCR")?>
</p>
<p><a href="/bitrix/admin/sale_location_admin.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_LOCATION")?></b></a><br>
<?echo GetMessage("SS_LOCATION_DESCR")?>
</p>
<p><a href="/bitrix/admin/sale_location_group_admin.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_LOCATION_GROUPS")?></b></a><br>
<?echo GetMessage("SS_LOCATION_GROUPS_DESCR")?>
</p>
<p><a href="/bitrix/admin/sale_tax.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_TAX")?></b></a><br>
<?echo GetMessage("SS_TAX_DESCR")?>
</p>
<p><a href="/bitrix/admin/sale_tax_rate.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_TAX_RATE")?></b></a><br>
<?echo GetMessage("SS_TAX_RATE_DESCR")?>
</p>
<p><a href="/bitrix/admin/sale_tax_exempt.php?lang=<?= LANG ?>"><b><?echo GetMessage("SS_TAX_EX")?></b></a><br>
<?echo GetMessage("SS_TAX_EX_DESCR")?>
</p>
</div>
<?echo EndNote();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>