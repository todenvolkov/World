<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$req = 'cmd=_notify-synch';
$tx_token = $_REQUEST['tx'];
$auth_token = CSalePaySystemAction::GetParamValue("IDENTITY_TOKEN");
$req .= "&tx=".$tx_token."&at=".$auth_token;
$domain = "";

if(CSalePaySystemAction::GetParamValue("TEST") == "Y")
	$domain = "sandbox.";

// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

if(CSalePaySystemAction::GetParamValue("SSL_ENABLE") == "Y")
	$fp = fsockopen ("ssl://www.".$domain."paypal.com", 443, $errno, $errstr, 30);
else
	$fp = fsockopen ("www.".$domain."paypal.com", 80, $errno, $errstr, 30);

if($fp)
{
	fputs ($fp, $header . $req);
	$res = "";
	$headerdone = false;
	while(!feof($fp)) 
	{
		$line = fgets ($fp, 1024);
		if(strcmp($line, "\r\n") == 0)
			$headerdone = true;
		elseif($headerdone)
			$res .= $line;
	}

	// parse the data
	$lines = explode("\n", $res);
	$keyarray = array();
	if(strcmp ($lines[0], "SUCCESS") == 0)
	{
		for ($i=1; $i<count($lines);$i++)
		{
			list($key,$val) = explode("=", $lines[$i]);
			$keyarray[urldecode($key)] = urldecode($val);
		}
		
		$strPS_STATUS_MESSAGE = "";
		$strPS_STATUS_MESSAGE .= "Name: ".$keyarray["first_name"]." ".$keyarray["last_name"]."; ";
		$strPS_STATUS_MESSAGE .= "Email: ".$keyarray["payer_email"]."; ";
		$strPS_STATUS_MESSAGE .= "Item: ".$keyarray["item_name"]."; ";
		$strPS_STATUS_MESSAGE .= "Amount: ".$keyarray["mc_gross"]."; ";
		
		$strPS_STATUS_DESCRIPTION = "";
		$strPS_STATUS_DESCRIPTION .= "Payment status - ".$keyarray["payment_status"]."; ";
		$strPS_STATUS_DESCRIPTION .= "Payment sate - ".$keyarray["payment_date"]."; ";
		$arOrder = CSaleOrder::GetByID($keyarray["custom"]);
		$arFields = array(
				"PS_STATUS" => "Y",
				"PS_STATUS_CODE" => "-",
				"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
				"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
				"PS_SUM" => $keyarray["mc_gross"],
				"PS_CURRENCY" => $keyarray["mc_currency"],
				"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
				"USER_ID" => $arOrder["USER_ID"],
			);

		if (IntVal($arOrder["PRICE"]) == IntVal($keyarray["mc_gross"])
		     && $keyarray["receiver_email"] == CSalePaySystemAction::GetParamValue("BUSINESS")
		     && $keyarray["payment_status"] == "Completed"
			)
			CSaleOrder::PayOrder($arOrder["ID"], "Y");

		CSaleOrder::Update($arOrder["ID"], $arFields);

		$firstname = $keyarray['first_name'];
		$lastname = $keyarray['last_name'];
		$itemname = $keyarray['item_name'];
		$amount = $keyarray['mc_gross'];
		
		echo "<p><h3>Thank you for your purchase!</h3></p>";
		
		echo "<b>Payment Details</b><br>\n";
		echo "<li>Name: $firstname $lastname</li>\n";
		echo "<li>Item: $itemname</li>\n";
		echo "<li>Amount: $amount</li>\n";
	}
	else
		echo "<p>Payment was Unsuccessful.</p>";
}
else
	echo "<p>Error during connecting to PayPal.";

fclose ($fp);
?>

Your transaction has been completed, and a receipt for your purchase has been emailed to you.<br>You may log into your account at <a href='https://www.paypal.com'>www.paypal.com</a> to view details of this transaction.<br>

You can view order status in personal section on <a href="/">our site</a>.
