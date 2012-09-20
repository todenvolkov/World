<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
// Скопируйте этот файл в папку /bitrix/php_interface/include/sale_payment/
// и измените в случае необходимости

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (CModule::IncludeModule("sale"))
{
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$bCorrectPayment = True;

		if (!($arOrder = CSaleOrder::GetByID(IntVal($_POST["LMI_PAYMENT_NO"]))))
			$bCorrectPayment = False;

		if ($bCorrectPayment)
			CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);

		$CNST_SECRET_KEY = CSalePaySystemAction::GetParamValue("CNST_SECRET_KEY");
		$CNST_PAYEE_PURSE = CSalePaySystemAction::GetParamValue("SHOP_ACCT");

		$strCheck = md5($_POST["LMI_PAYEE_PURSE"].$_POST["LMI_PAYMENT_AMOUNT"].$_POST["LMI_PAYMENT_NO"].$_POST["LMI_MODE"].$_POST["LMI_SYS_INVS_NO"].$_POST["LMI_SYS_TRANS_NO"].$_POST["LMI_SYS_TRANS_DATE"].$CNST_SECRET_KEY.$_POST["LMI_PAYER_PURSE"].$_POST["LMI_PAYER_WM"]);
		if ($bCorrectPayment && strtoupper($_POST["LMI_HASH"]) != strtoupper($strCheck))
			$bCorrectPayment = False;

		if ($bCorrectPayment)
		{
			$strPS_STATUS_DESCRIPTION = "";
			if ($_POST["LMI_MODE"] != 0)
				$strPS_STATUS_DESCRIPTION .= "тестовый режим, реально деньги не переводились; ";
			$strPS_STATUS_DESCRIPTION .= "кошелек продавца - ".$_POST["LMI_PAYEE_PURSE"]."; ";
			$strPS_STATUS_DESCRIPTION .= "номер счета - ".$_POST["LMI_SYS_INVS_NO"]."; ";
			$strPS_STATUS_DESCRIPTION .= "номер платежа - ".$_POST["LMI_SYS_TRANS_NO"]."; ";
			$strPS_STATUS_DESCRIPTION .= "дата платежа - ".$_POST["LMI_SYS_TRANS_DATE"]."";

			$strPS_STATUS_MESSAGE = "";
			$strPS_STATUS_MESSAGE .= "кошелек покупателя - ".$_POST["LMI_PAYER_PURSE"]."; ";
			$strPS_STATUS_MESSAGE .= "WMId покупателя - ".$_POST["LMI_PAYER_WM"]."; ";
			if (isset($_POST["LMI_PAYMER_NUMBER"]) && strlen($_POST["LMI_PAYMER_NUMBER"])>0)
				$strPS_STATUS_MESSAGE .= "номер ВМ-карты - ".$_POST["LMI_PAYMER_NUMBER"]."; ";
			if (isset($_POST["LMI_PAYMER_EMAIL"]) && strlen($_POST["LMI_PAYMER_EMAIL"])>0)
				$strPS_STATUS_MESSAGE .= "paymer.com e-mail покупателя - ".$_POST["LMI_PAYMER_EMAIL"]."; ";
			if (isset($_POST["LMI_TELEPAT_PHONENUMBER"]) && strlen($_POST["LMI_TELEPAT_PHONENUMBER"])>0)
				$strPS_STATUS_MESSAGE .= "телефон покупателя - ".$_POST["LMI_TELEPAT_PHONENUMBER"]."; ";
			if (isset($_POST["LMI_TELEPAT_ORDERID"]) && strlen($_POST["LMI_TELEPAT_ORDERID"])>0)
				$strPS_STATUS_MESSAGE .= "платеж в Телепате - ".$_POST["LMI_TELEPAT_ORDERID"]."";

			$arFields = array(
					"PS_STATUS" => "Y",
					"PS_STATUS_CODE" => "-",
					"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
					"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
					"PS_SUM" => $_POST["LMI_PAYMENT_AMOUNT"],
					"PS_CURRENCY" => $arOrder["CURRENCY"],
					"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
					"USER_ID" => $arOrder["USER_ID"]
				);

			// You can comment this code if you want PAYED flag not to be set automatically
			if ($arOrder["PRICE"] == $_POST["LMI_PAYMENT_AMOUNT"] 
				&& $CNST_PAYEE_PURSE == $_POST["LMI_PAYMENT_AMOUNT"])
			{
				$arFields["PAYED"] = "Y";
				$arFields["DATE_PAYED"] = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)));
				$arFields["EMP_PAYED_ID"] = false;
			}

			CSaleOrder::Update($arOrder["ID"], $arFields);
		}
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>