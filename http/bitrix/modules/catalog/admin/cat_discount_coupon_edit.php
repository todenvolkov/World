<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/*
$catalogPermissions = $APPLICATION->GetGroupRight("catalog");
if ($catalogPermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
*/

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_discount')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bReadOnly = !$USER->CanDoOperation('catalog_discount');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");

if ($ex = $APPLICATION->GetException())
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");
	
	$strError = $ex->GetString();
	ShowError($strError);
	
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

ClearVars();

$errorMessage = "";
$bVarsFromForm = false;

$ID = IntVal($ID);

if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && !$bReadOnly /*$catalogPermissions=="W"*/ && check_bitrix_sessid())
{
	if (strlen($COUPON) <= 0)
		$errorMessage .= GetMessage("DSC_CPN_ERR_CODE")."<br>";
	if (strlen($DISCOUNT_ID) <= 0)
		$errorMessage .= GetMessage("DSC_CPN_ERR_DISC")."<br>";

	if (strlen($errorMessage) <= 0)
	{
		$DB->StartTransaction();

		$arFields = Array(
			"DISCOUNT_ID" => $DISCOUNT_ID,
			"ACTIVE" => (($ACTIVE == "Y") ? "Y" : "N"),
			"COUPON" => $COUPON,
			"DATE_APPLY" => $DATE_APPLY,
			"ONE_TIME" => (($ONE_TIME == "Y") ? "Y" : "N")
		);
		
		if ($ID > 0)
		{
			$res = CCatalogDiscountCoupon::Update($ID, $arFields);
		}
		else
		{
			$ID = CCatalogDiscountCoupon::Add($arFields);
			$res = ($ID>0);
		}

		if (!$res)
		{
			$ex = $APPLICATION->GetException();
			$errorMessage .= $ex->GetString()."<br>";
			$bVarsFromForm = true;
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
			if (strlen($apply)<=0)
				LocalRedirect("/bitrix/admin/cat_discount_coupon.php?lang=".LANG.GetFilterParams("filter_", false));
			else
				LocalRedirect("/bitrix/admin/cat_discount_coupon_edit.php?lang=".LANG."&ID=".intval($ID).GetFilterParams("filter_", false));
		}
	}
	else
	{
		$bVarsFromForm = true;
	}
}

if ($ID > 0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("DSC_TITLE_UPDATE")));
else
	$APPLICATION->SetTitle(GetMessage("DSC_TITLE_ADD"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


$str_ACTIVE = "Y";

$dbDiscountCoupon = CCatalogDiscountCoupon::GetList(
		array(),
		array("ID" => $ID),
		false,
		false,
		array("ID", "DISCOUNT_ID", "ACTIVE", "COUPON", "ONE_TIME", "DATE_APPLY")
	);
if (!$dbDiscountCoupon->NavNext(true, "str_", true, false))
	$ID = 0;

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_catalog_discount_coupon", "", "str_");

?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("DSC_TO_LIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/cat_discount_coupon.php?lang=".LANG.GetFilterParams("filter_", false)
	)
);

if ($ID > 0 && !$bReadOnly /*$catalogPermissions == "W"*/)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("CDEN_NEW_DISCOUNT"),
		"ICON" => "btn_new",
		"LINK" => "/bitrix/admin/cat_discount_coupon_edit.php?lang=".LANG.GetFilterParams("filter_", false)
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("CDEN_DELETE_DISCOUNT"), 
		"ICON" => "btn_delete",
		"LINK" => "javascript:if(confirm('".GetMessage("CDEN_DELETE_DISCOUNT_CONFIRM")."')) window.location='/bitrix/admin/cat_discount_coupon.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"WARNING" => "Y"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($errorMessage);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fdiscount_edit">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("CDEN_TAB_DISCOUNT"), "ICON" => "catalog", "TITLE" => GetMessage("CDEN_TAB_DISCOUNT_DESCR")),
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
			<td width="60%"><?= $ID ?></td>
		</tr>
	<?endif;?>
	<tr>
		<td width="40%"><span class="required">*</span><?= GetMessage("DSC_CPN_DISC") ?>:</td>
		<td width="60%">
			<select name="DISCOUNT_ID">
				<?
				$dbDiscountList = CCatalogDiscount::GetList(
					array("NAME" => "ASC"),
					array(),
					false,
					false,
					array("ID", "SITE_ID", "NAME")
				);
				while ($arDiscountList = $dbDiscountList->Fetch())
				{
					?><option value="<?= $arDiscountList["ID"] ?>"<?if ($str_DISCOUNT_ID == $arDiscountList["ID"]) echo " selected";?>><?= htmlspecialchars("[".$arDiscountList["ID"]."] ".$arDiscountList["NAME"]." (".$arDiscountList["SITE_ID"].")") ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><label for="ACTIVE"><?= GetMessage("DSC_ACTIVE") ?>:</label></td>
		<td>
			<input type="checkbox" id="ACTIVE" name="ACTIVE" value="Y"<?if ($str_ACTIVE=="Y") echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td><label for="ONE_TIME"><?= GetMessage("DSC_TIME") ?>:</label></td>
		<td>
			<input type="checkbox" id="ONE_TIME" name="ONE_TIME" value="Y"<?if ($str_ONE_TIME=="Y") echo ' checked="checked"'?> />
		</td>
	</tr>
	<tr>
		<td><span class="required">*</span><?= GetMessage("DSC_CPN_CODE") ?>:</td>
		<td>
			<input type="text" name="COUPON" size="50" maxlength="32" value="<?= $str_COUPON ?>" />
			&nbsp;&nbsp;&nbsp;
			<input type="button" value="<?= GetMessage("DSC_CPN_GEN") ?>" OnClick="GenerateCheck()">
			<script language="JavaScript">
			<!--
			function GenerateCheck()
			{
				var oCoupon = document.fdiscount_edit.COUPON;

				var allchars = 'ABCDEFGHIJKLNMOPQRSTUVWXYZ0123456789';
				var string1 = '';
				var string2 = '';
				for (var i = 0; i < 5; i++)
					string1 = string1 + allchars.substr(Math.round((Math.random())*(allchars.length-1)), 1);

				for (var i = 0; i < 7; i++)
					string2 = string2 + allchars.substr(Math.round((Math.random())*(allchars.length-1)), 1);

				oCoupon.value = "CP-"+string1+"-"+string2;
			}
			//-->
			</script>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DDSC_CPN_DATE") ?>: (<?= CSite::GetDateFormat("SHORT", LANG); ?>)</td>
		<td>
			<?= CalendarDate("DATE_APPLY", $str_DATE_APPLY, "fdiscount_edit", "20", ""); ?>
		</td>
	</tr>

<?
$tabControl->EndTab();

$tabControl->Buttons(
	array(
		//"disabled" => ($catalogPermissions < "W"),
		"disabled" => $bReadOnly,
		"back_url" => "/bitrix/admin/cat_discount_coupon.php?lang=".LANG.GetFilterParams("filter_", false)
	)
);
$tabControl->End();
?>
</form>

<?echo BeginNote();?>
<span class="required">*</span> <?echo GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote(); ?>		

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
