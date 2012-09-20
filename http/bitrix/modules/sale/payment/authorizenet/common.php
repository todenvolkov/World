<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$arAVSErr = array(
	"A" => "Address (Street) matches, ZIP does not",
	"E" => "AVS error",
	"N" => "No Match on Address (Street) or ZIP",
	"P" => "AVS not applicable for this transaction",
	"R" => "Retry. System unavailable or timed out",
	"S" => "Service not supported by issuer",
	"U" => "Address information is unavailable",
	"W" => "9 digit ZIP matches, Address (Street) does not",
	"X" => "Exact AVS Match",
	"Y" => "Address (Street) and 5 digit ZIP match",
	"Z" => "5 digit ZIP matches, Address (Street) does not"
);

$arCVVErr = array(
	"M" => "Match",
	"N" => "No Match",
	"P" => "Not Processed",
	"S" => "Should have been present",
	"U" => "Issuer unable to process request"
);

$arCAVVErr = array(
	"0" => "CAVV not validated because erroneous data was submitted",
	"1" => "CAVV failed validation",
	"2" => "CAVV passed validation",
	"3" => "CAVV validation could not be performed; issuer attempt incomplete",
	"4" => "CAVV validation could not be performed; issuer system error",
	"7" => "CAVV attempt - failed validation - issuer available (US issued card/non-US acquirer)",
	"8" => "CAVV attempt - passed validation - issuer available (US issued card/non-US acquirer)",
	"9" => "CAVV attempt - failed validation - issuer unavailable (US issued card/non-US acquirer)",
	"A" => "CAVV attempt - passed validation - issuer unavailable (US issued card/non-US acquirer)",
	"B" => "CAVV passed validation, information only, no liability shift"
);
?>