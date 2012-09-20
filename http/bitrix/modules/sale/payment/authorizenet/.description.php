<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$psTitle = "authorize.net";
$psDescription = "Advanced Integration Method (AIM). <a href=\"http://www.authorize.net\" target=\"_blank\">http://www.authorize.net</a>";

$arPSCorrespondence = array(
		"PS_LOGIN" => array(
				"NAME" => "Login ID",
				"DESCR" => "The Login ID used to access the Merchant Interface",
				"VALUE" => "login",
				"TYPE" => ""
			),
		"PS_TRANSACTION_KEY" => array(
				"NAME" => "Transaction key",
				"DESCR" => "The transaction key obtained from the merchant interface",
				"VALUE" => "key",
				"TYPE" => ""
			),
		"HASH_VALUE" => array(
				"NAME" => "Hash value",
				"DESCR" => "Hash value for verifying that the results of a transaction were actually sent from the Payment Gateway",
				"VALUE" => "",
				"TYPE" => ""
			),
		"TEST_TRANSACTION" => array(
				"NAME" => "Test transaction",
				"DESCR" => "Indicates whether the transaction should be processed as a test transaction (any value)",
				"VALUE" => "",
				"TYPE" => ""
			),
		"FIRST_NAME" => array(
				"NAME" => "Name",
				"DESCR" => "Customer name",
				"VALUE" => "FIRST_NAME",
				"TYPE" => "PROPERTY"
			),
		"LAST_NAME" => array(
				"NAME" => "Last name",
				"DESCR" => "Customer last name",
				"VALUE" => "LAST_NAME",
				"TYPE" => "PROPERTY"
			),
		"COMPANY" => array(
				"NAME" => "Company",
				"DESCR" => "Customer company",
				"VALUE" => "COMPANY",
				"TYPE" => "PROPERTY"
			),
		"ADDRESS" => array(
				"NAME" => "Address",
				"DESCR" => "Customer address",
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"CITY" => array(
				"NAME" => "City",
				"DESCR" => "Customer city",
				"VALUE" => "CITY",
				"TYPE" => "PROPERTY"
			),
		"STATE" => array(
				"NAME" => "State",
				"DESCR" => "Customer state",
				"VALUE" => "STATE",
				"TYPE" => "PROPERTY"
			),
		"ZIP" => array(
				"NAME" => "Zip",
				"DESCR" => "Customer zip",
				"VALUE" => "ZIP",
				"TYPE" => "PROPERTY"
			),
		"COUNTRY" => array(
				"NAME" => "Country",
				"DESCR" => "Customer country",
				"VALUE" => "COUNTRY",
				"TYPE" => "PROPERTY"
			),
		"PHONE" => array(
				"NAME" => "Phone",
				"DESCR" => "Customer phone",
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			),
		"FAX" => array(
				"NAME" => "Fax",
				"DESCR" => "Customer fax",
				"VALUE" => "FAX",
				"TYPE" => "PROPERTY"
			),
		"REMOTE_ADDR" => array(
				"NAME" => "Remote address",
				"DESCR" => "Remote address",
				"VALUE" => "=\$_SERVER[\"REMOTE_ADDR\"]",
				"TYPE" => ""
			),
		"EMAIL" => array(
				"NAME" => "EMail",
				"DESCR" => "Customer email",
				"VALUE" => "EMAIL",
				"TYPE" => "PROPERTY"
			),
		"SHIP_FIRST_NAME" => array(
				"NAME" => "Name (for shipping)",
				"DESCR" => "Customer name",
				"VALUE" => "FIRST_NAME",
				"TYPE" => "PROPERTY"
			),
		"SHIP_LAST_NAME" => array(
				"NAME" => "Last name (for shipping)",
				"DESCR" => "Customer last name",
				"VALUE" => "LAST_NAME",
				"TYPE" => "PROPERTY"
			),
		"SHIP_COMPANY" => array(
				"NAME" => "Company (for shipping)",
				"DESCR" => "Customer company",
				"VALUE" => "COMPANY",
				"TYPE" => "PROPERTY"
			),
		"SHIP_ADDRESS" => array(
				"NAME" => "Address (for shipping)",
				"DESCR" => "Customer address",
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			),
		"SHIP_CITY" => array(
				"NAME" => "City (for shipping)",
				"DESCR" => "Customer city",
				"VALUE" => "CITY",
				"TYPE" => "PROPERTY"
			),
		"SHIP_STATE" => array(
				"NAME" => "State (for shipping)",
				"DESCR" => "Customer state",
				"VALUE" => "STATE",
				"TYPE" => "PROPERTY"
			),
		"SHIP_ZIP" => array(
				"NAME" => "Zip (for shipping)",
				"DESCR" => "Customer zip",
				"VALUE" => "ZIP",
				"TYPE" => "PROPERTY"
			),
		"SHIP_COUNTRY" => array(
				"NAME" => "Country (for shipping)",
				"DESCR" => "Customer country",
				"VALUE" => "COUNTRY",
				"TYPE" => "PROPERTY"
			)
	);
?>