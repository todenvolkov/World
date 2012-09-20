<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/assist.php"));
//return url
//price format
$SERVER_NAME_tmp = "";
if (defined("SITE_SERVER_NAME"))
	$SERVER_NAME_tmp = SITE_SERVER_NAME;
if (strlen($SERVER_NAME_tmp)<=0)
	$SERVER_NAME_tmp = COption::GetOptionString("main", "server_name", "");

$dateInsert = (strlen(CSalePaySystemAction::GetParamValue("DATE_INSERT")) > 0) ? CSalePaySystemAction::GetParamValue("DATE_INSERT") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"];
$orderID = (strlen(CSalePaySystemAction::GetParamValue("ORDER_ID")) > 0) ? CSalePaySystemAction::GetParamValue("ORDER_ID") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];
$shouldPay = (strlen(CSalePaySystemAction::GetParamValue("SHOULD_PAY")) > 0) ? CSalePaySystemAction::GetParamValue("SHOULD_PAY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
$currency = (strlen(CSalePaySystemAction::GetParamValue("CURRENCY")) > 0) ? CSalePaySystemAction::GetParamValue("CURRENCY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"];
$sucUrl = (strlen(CSalePaySystemAction::GetParamValue("SUCCESS_URL")) > 0) ? CSalePaySystemAction::GetParamValue("SUCCESS_URL") : "http://".$SERVER_NAME_tmp;
$failUrl = (strlen(CSalePaySystemAction::GetParamValue("FAIL_URL")) > 0) ? CSalePaySystemAction::GetParamValue("FAIL_URL") : "http://".$SERVER_NAME_tmp;

?>
<FORM ACTION="https://secure.assist.ru/shops/cardpayment.cfm" METHOD="POST" target="_blank">
<font class="tablebodytext">
<?echo GetMessage("SASP_PROMT")?><br>
<?echo GetMessage("SASP_ACCOUNT_NO")?> <?= $orderID.GetMessage("SASP_ORDER_FROM").$dateInsert ?><br>
<?echo GetMessage("SASP_ORDER_SUM")?> <b><?echo SaleFormatCurrency($shouldPay, $currency) ?></b><br>
<br>
<INPUT TYPE="HIDDEN" NAME="Shop_IDP" VALUE="<?= (CSalePaySystemAction::GetParamValue("SHOP_IDP")) ?>">
<INPUT TYPE="HIDDEN" NAME="Order_IDP" VALUE="<?= $orderID ?>">
<INPUT TYPE="HIDDEN" NAME="Subtotal_P" VALUE="<?= (str_replace(",", ".", $shouldPay)) ?>">
<INPUT TYPE="HIDDEN" NAME="Delay" VALUE="0">
<INPUT TYPE="HIDDEN" NAME="Language" VALUE="0">
<INPUT TYPE="HIDDEN" NAME="URL_RETURN_OK" VALUE="<?= (CSalePaySystemAction::GetParamValue("SUCCESS_URL")) ?>">
<INPUT TYPE="HIDDEN" NAME="URL_RETURN_NO" VALUE="<?= (CSalePaySystemAction::GetParamValue("FAIL_URL")) ?>">
<INPUT TYPE="HIDDEN" NAME="Currency" VALUE="<?=(($currency == "RUB") ? "RUR" :($currency)) ?>">
<INPUT TYPE="HIDDEN" NAME="Comment" VALUE="Invoice <?= $orderID." (".$dateInsert.")" ?>">
<INPUT TYPE="HIDDEN" NAME="LastName" VALUE="<?= (CSalePaySystemAction::GetParamValue("LAST_NAME")) ?>">
<INPUT TYPE="HIDDEN" NAME="FirstName" VALUE="<?= (CSalePaySystemAction::GetParamValue("FIRST_NAME")) ?>">
<INPUT TYPE="HIDDEN" NAME="MiddleName" VALUE="<?= (CSalePaySystemAction::GetParamValue("MIDDLE_NAME")) ?>">
<INPUT TYPE="HIDDEN" NAME="Email" VALUE="<?= (CSalePaySystemAction::GetParamValue("EMAIL")) ?>">
<INPUT TYPE="HIDDEN" NAME="Address" VALUE="<?= (CSalePaySystemAction::GetParamValue("ADDRESS")) ?>">
<INPUT TYPE="HIDDEN" NAME="Phone" VALUE="<?= (CSalePaySystemAction::GetParamValue("PHONE")) ?>">

<INPUT TYPE="HIDDEN" NAME="IsFrame" VALUE="0">

<?if ($valTmp = CSalePaySystemAction::GetParamValue("DEMO")):?>
<INPUT TYPE="HIDDEN" NAME="DemoResult" VALUE="<?= ($valTmp) ?>">
<?endif;?>

<INPUT TYPE="HIDDEN" NAME="CardPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_CardPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="WalletPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_WalletPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="WebMoneyPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_WebMoneyPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="EPortPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_EPortPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="KreditPilotPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_KreditPilotPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="PayCashPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_PayCashPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="AssistIDCCPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_AssistIDCCPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="RapidaPayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_RapidaPayment")) == 1) ? 1 : 0?>">
<INPUT TYPE="HIDDEN" NAME="EPBeelinePayment" VALUE="<?echo (IntVal(CSalePaySystemAction::GetParamValue("PAYMENT_EPBeelinePayment")) == 1) ? 1 : 0?>">

<INPUT TYPE="SUBMIT" NAME="Submit" VALUE="<?echo GetMessage("SASP_ACTION")?>">
</font>
</form>

<p align="justify"><font class="tablebodytext"><b><?echo GetMessage("SASP_NOTES_TITLE")?></b></font></p>
<p align="justify"><font class="tablebodytext"><?echo GetMessage("SASP_NOTES")?></font></p>
<p align="justify"><font class="tablebodytext"><b><?echo GetMessage("SASP_NOTES_TITLE1")?></b></font></p>
<p align="justify"><font class="tablebodytext"><?echo GetMessage("SASP_NOTES1")?></font></p>
