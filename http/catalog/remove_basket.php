<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))

{
if ($_REQUEST[ID]>0){
	$result = 'true';
	$dbBasketItems = CSaleBasket::GetList(false,array("FUSER_ID" => CSaleBasket::GetBasketUserID(),"ORDER_ID" => "NULL" ,"CAN_BUY" => "Y", "PRODUCT_ID" => $_REQUEST[ID] ),false,false,array("ID", "CALLBACK_FUNC"));
	while ($arItems = $dbBasketItems->Fetch()){
		if (strlen($arItems["CALLBACK_FUNC"]) > 0){
			if (!CSaleBasket::Delete( $arItems["ID"] )){
				$result = 'false';
			}
		}
	}
	echo $result;
}else if ($_REQUEST[SALE_ID]>0){
	$dbBasketItems = CSaleBasket::GetList(false,array("FUSER_ID" => CSaleBasket::GetBasketUserID(),"ORDER_ID" => "NULL" ,"CAN_BUY" => "Y", "ID" => $_REQUEST[SALE_ID] ),false,false,array("ID", "CALLBACK_FUNC"));
	while ($arItems = $dbBasketItems->Fetch()){
		if (strlen($arItems["CALLBACK_FUNC"]) > 0){
			if (CSaleBasket::Delete( $arItems["ID"] )){
				break;
			}
		}
	}
}
}
?>
