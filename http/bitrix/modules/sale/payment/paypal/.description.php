<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$psTitle = "PayPal";
$psDescription = "<a href=\"http://www.paypal.com\" target=\"_blank\">http://www.paypal.com</a>";

$arPSCorrespondence = array(
		"BUSINESS" => array(
				"NAME" => "Business",
				"DESCR" => "Email address on your PayPal account",
				"VALUE" => "you@youremail.com",
				"TYPE" => ""
			),
		"IDENTITY_TOKEN" => array(
				"NAME" => "Identity",
				"DESCR" => "Identity token from PayPal for PDT (Payment Data Transfer)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"ORDER_ID" => Array(
				"NAME" => "Order ID",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"DATE_INSERT" => Array(
				"NAME" => "Order date",
 				"VALUE" => "Order insert date",
 				"TYPE" => "ORDER",
			),
		"SHOULD_PAY" => Array(
				"NAME" => "Order amount",
				"DESCR" => "The price or amount of the purchase",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"CURRENCY" => Array(
				"NAME" => "Order currency",
 				"VALUE" => "",
 				"TYPE" => "ORDER",
			),
		"NOTIFY_URL" => Array(
				"NAME" => "Notification url",
				"DESCR" => "The URL to which PayPal posts information about the transaction via Instant Payment Notification.",
 				"VALUE" => "",
 				"TYPE" => "",
			),
		"RETURN" => Array(
				"NAME" => "Return url",
				"DESCR" => "The URL to which PayPal return client after payment on PayPal.",
 				"VALUE" => "",
 				"TYPE" => "",
			),
		"TEST" => array(
				"NAME" => "Test mode",
				"DESCR" => "Y - for test mode",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SSL_ENABLE" => array(
				"NAME" => "SSL request enable in PHP on your site",
				"DESCR" => "Y - if enable. This option enable ssl request to PayPal.",
				"VALUE" => "",
				"TYPE" => ""
			),
		"ON0"  => Array(
				"NAME" => "First option field name",
				"DESCR" => "If omitted, no variable is passed back to you.",
 				"VALUE" => "",
 				"TYPE" => "",
			),
		"ON1"  => Array(
				"NAME" => "Second option field name",
				"DESCR" => "If omitted, no variable is passed back to you.",
 				"VALUE" => "",
 				"TYPE" => "",
			),
		"OS0"  => Array(
				"NAME" => "First set of option values",
				"DESCR" => "If this option is selected through a text box or radio button, each value should be no more than 64 characters. If this value is entered by the customer in a text field, there is a 200 character limit. If omitted, no variable is passed back to you. Note: on0 must also be defined.",
 				"VALUE" => "",
 				"TYPE" => "",
			),
		"OS1"  => Array(
				"NAME" => "Second set of option values",
				"DESCR" => "If this option is selected through a text box or radio button, each value should be no more than 64 characters. If this value is entered by the customer in a text field, there is a 200 character limit. If omitted, no variable is passed back to you. Note: on1 must also be defined.",
 				"VALUE" => "",
 				"TYPE" => "",
			),
	);
?>