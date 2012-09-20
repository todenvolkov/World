<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	include(GetLangFileName(dirname(__FILE__)."/", "/result_rec.php"));

	$cs1 = IntVal($_POST["cs1"]);
	$bCorrectPayment = True;
	$techMessage = "";
	if(!($arOrder = CSaleOrder::GetByID($cs1)))
	{
		$bCorrectPayment = False;
		$techMessage = GetMessage("SALE_CHR_REC_ORDER");
	}

	if ($bCorrectPayment)
	{
		CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);
		$productIdB = CSalePaySystemAction::GetParamValue("PRODUCT_ID");
		$orderIdB = CSalePaySystemAction::GetParamValue("ORDER_ID");
		$product_priceB = number_format(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2, '.', '');
		$sharedsecB = CSalePaySystemAction::GetParamValue("SHARED");

		$product_id = trim($_POST["product_id"]);
		$customer_id = trim($_POST["customer_id"]);
		$transaction_id = trim($_POST["transaction_id"]);
		$transaction_type = trim($_POST["transaction_type"]);
		$total = trim($_POST["total"]);
		$currency = trim($_POST["currency"]);
		$date = trim($_POST["date"]);
		$sign = trim($_POST["sign"]);
		
		if($product_id == $productIdB)
		{
			$checkB = md5($sharedsecB.$customer_id.$transaction_id.$transaction_type.$total);
			if($checkB == $sign)
			{
				if($transaction_type == "onetime" || $transaction_type == "Purchase")
				{
					if($product_priceB == $total)
					{

						if($arOrder["PAYED"] != "Y")
							CSaleOrder::PayOrder($arOrder["ID"], "Y");
					}
					else
						$techMessage = GetMessage("SALE_CHR_REC_SUMM");
				}
				else
					$techMessage = GetMessage("SALE_CHR_REC_TRANS");
			}
			else
				$techMessage = GetMessage("SALE_CHR_REC_SIGN");
		}
		else
			$techMessage = GetMessage("SALE_CHR_REC_PRODUCT");
		
		$strPS_STATUS_DESCRIPTION = "customer_id - ".$customer_id."; ";
		$strPS_STATUS_DESCRIPTION .= "transaction_id - ".$transaction_id."; ";
		$strPS_STATUS_DESCRIPTION .= "date - ".$date.";";

		$arFields = array(
				"PS_STATUS" => ($transaction_type == "onetime" || $transaction_type == "Purchase") ? "Y" : "N",
				"PS_STATUS_CODE" => $transaction_type,
				"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
				"PS_STATUS_MESSAGE" => $techMessage,
				"PS_SUM" => $total,
				"PS_CURRENCY" => $currency,
				"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
			);

		CSaleOrder::Update($arOrder["ID"], $arFields);
	}
}
?>