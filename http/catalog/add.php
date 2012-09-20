<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

function remake_date($date){
	$aa = explode('.',$date);
	return sprintf("%02d.%02d.%04d", $aa[0], $aa[1], $aa[2]);
}


if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
{

//$res1 = Add2BasketByProductID(4371,3);

}
?>
