<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$errorMessage = "";
$bVarsFromForm = false;

ClearVars();

$ID = IntVal($ID);

if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && $saleModulePermissions>="W" && check_bitrix_sessid())
{
	$arFields = array(
		"LID" => $LID,
		"PRICE_FROM" => $PRICE_FROM,
		"PRICE_TO" => $PRICE_TO,
		"CURRENCY" => $CURRENCY,
		"DISCOUNT_VALUE" => $DISCOUNT_VALUE,
		"DISCOUNT_TYPE" => $DISCOUNT_TYPE,
		"ACTIVE_FROM" => $ACTIVE_FROM,
		"ACTIVE_TO" => $ACTIVE_TO,
		"ACTIVE" => $ACTIVE,
		"SORT" => $SORT
	);

	if ($ID > 0)
	{
		if (!CSaleDiscount::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().".<br>";
			else
				$errorMessage .= GetMessage("SDEN_ERROR_SAVING_DISCOUNT").".<br>";
		}
	}
	else
	{
		$ID = CSaleDiscount::Add($arFields);
		$ID = IntVal($ID);
		if ($ID <= 0)
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().".<br>";
			else
				$errorMessage .= GetMessage("SDEN_ERROR_SAVING_DISCOUNT").".<br>";
		}
	}

	if (strlen($errorMessage) <= 0)
	{
		if (strlen($apply) <= 0)
			LocalRedirect("/bitrix/admin/sale_discount.php?lang=".LANG.GetFilterParams("filter_", false));
		else
			LocalRedirect("/bitrix/admin/sale_discount_edit.php?lang=".LANG."&ID=".$ID);
	}
	else
	{
		$bVarsFromForm = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

if ($ID > 0)
	$APPLICATION->SetTitle(GetMessage("SDEN_UPDATING"));
else
	$APPLICATION->SetTitle(GetMessage("SDEN_ADDING"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$dbDiscount = CSaleDiscount::GetList($b="", $o="", array("ID" => $ID));
if (!$dbDiscount->ExtractFields("str_"))
{
	if ($saleModulePermissions < "W")
		$errorMessage .= GetMessage("SDEN_NO_PERMS2ADD").".<br>";
	$ID = 0;
}

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sale_discount", "", "str_");

?>

<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("SDEN_2FLIST"),
				"LINK" => "/bitrix/admin/sale_discount.php?lang=".LANG.GetFilterParams("filter_"),
				"ICON" => "btn_list"
			)
	);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("SDEN_NEW_DISCOUNT"),
			"LINK" => "/bitrix/admin/sale_discount_edit.php?lang=".LANG.GetFilterParams("filter_"),
			"ICON" => "btn_new"
		);

	if ($saleModulePermissions >= "W")
	{
		$aMenu[] = array(
				"TEXT" => GetMessage("SDEN_DELETE_DISCOUNT"), 
				"LINK" => "javascript:if(confirm('".GetMessage("SDEN_DELETE_DISCOUNT_CONFIRM")."')) window.location='/bitrix/admin/sale_discount.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
				"WARNING" => "Y",
				"ICON" => "btn_delete"
			);
	}
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?if(strlen($errorMessage)>0)
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SDEN_ERROR"), "HTML"=>true));?>


<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SDEN_TAB_DISCOUNT"), "ICON" => "sale", "TITLE" => GetMessage("SDEN_TAB_DISCOUNT_DESCR"))
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
	<?endif;?>
	<tr>
		<td width="40%"><span class="required">*</span><?echo GetMessage("SDEN_SITE")?>:</td>
		<td width="60%">
			<?echo CSite::SelectBox("LID", $str_LID, "", "", "");?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SDEN_PRICE")?>:</td>
		<td>
			<?echo GetMessage("SDEN_PRICE_FROM")?>
			<input type="text" name="PRICE_FROM" size="10" maxlength="20" value="<?= roundEx($str_PRICE_FROM, SALE_VALUE_PRECISION) ?>">
			<?echo GetMessage("SDEN_PRICE_TO")?>
			<input type="text" name="PRICE_TO" size="10" maxlength="20" value="<?= roundEx($str_PRICE_TO, SALE_VALUE_PRECISION) ?>">
			<?echo CCurrency::SelectBox("CURRENCY", $str_CURRENCY, "", false, "", "");?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SDEN_PERIOD") ?>:</td>
		<td>
			<?= CalendarPeriod("ACTIVE_FROM", $str_ACTIVE_FROM, "ACTIVE_TO", $str_ACTIVE_TO, "form1", "N", "", "", "19")?>
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?echo GetMessage("SDEN_DISCOUNT_VALUE")?>:</td>
		<td>
			<input type="text" name="DISCOUNT_VALUE" size="10" maxlength="20" value="<?= roundEx($str_DISCOUNT_VALUE, SALE_VALUE_PRECISION) ?>">
			<select name="DISCOUNT_TYPE">
				<option value="P"<?= (($str_DISCOUNT_TYPE=="P") ? " selected" : "")?>><?= GetMessage("SDEN_TYPE_PERCENT") ?></option>
				<option value="V"<?= (($str_DISCOUNT_TYPE=="V") ? " selected" : "")?>><?= GetMessage("SDEN_TYPE_FIX") ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SDEN_ACTIVE")?>:</td>
		<td>
			<input type="checkbox" name="ACTIVE" value="Y"<?if ($str_ACTIVE == "Y") echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SDEN_SORT")?>:</td>
		<td>
			<input type="text" name="SORT" value="<?= IntVal($str_SORT) ?>">
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($saleModulePermissions < "W"),
				"back_url" => "/bitrix/admin/sale_discount.php?lang=".LANG.GetFilterParams("filter_")
			)
	);
?>

<?
$tabControl->End();
?>

</form>

<?echo BeginNote();?>
<span class="required">*</span> <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote(); ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>