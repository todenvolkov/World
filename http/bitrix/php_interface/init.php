<?

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("MyClass", "OnBeforeIBlockElementAddHandler"));
AddEventHandler("catalog", "OnBeforePriceAdd", Array("MyClass", "OnBeforePriceAddHandler")); 
AddEventHandler("catalog", "OnBeforePriceUpdate", Array("MyClass", "OnBeforePriceUpdateHandler")); 

//AddEventHandler("sale", "OnBeforeBasketAdd", "OnBeforeBasketAddHandler"); 
AddEventHandler("sale", "OnBeforeBasketDelete", Array("MyClass", "OnBeforeBasketDeleteHandler")); 

class MyClass
{
	function OnBeforeIBlockElementAddHandler(&$arFields){
		$arFields["CODE"] = $arFields["XML_ID"];
	}
	function OnBeforePriceAddHandler($ID,&$arFields){
		CIBlockElement::SetPropertyValueCode($arFields["PRODUCT_ID"], "PRICE1",  $arFields["PRICE"] );
		$arFields["PRICE"] = ceil($arFields["PRICE"]/30);
	}


	function OnBeforePriceUpdateHandler($ID,&$arFields){
		
		$res = CIBlockElement::GetByID($arFields["PRODUCT_ID"]);
		if($ar = $res->GetNextElement()){
			$itemP = $ar->GetProperties(false,array("CODE"=>"PRICE1"));


			//if ($item["CODE"]=="UT000000143"){
			/*
			if ($arFields["PRODUCT_ID"]==4148){
				echo round( $itemP["PRICE1"]["VALUE"]/30, 2)." ".$arFields["PRICE"]."<br>";
				exit;
			}
			*/


			if( round( $itemP["PRICE1"]["VALUE"]/30, 2) != $arFields["PRICE"] ){
				CIBlockElement::SetPropertyValueCode($arFields["PRODUCT_ID"], "PRICE1",  $arFields["PRICE"] );	
				$arFields["PRICE"] = round( $arFields["PRICE"]/30, 2);
			}
		}
	}

	function OnBeforeBasketDeleteHandler($ID){
		$db_res = CSaleBasket::GetPropsList( false, array("BASKET_ID" => $ID) );
		while ($ar_res = $db_res->Fetch()){
			if (preg_match("/FILE_ID\d+/",$ar_res["CODE"])){
				Cfile::Delete(intval($ar_res["VALUE"]));
			}
		}
	}

}


function add2basket_pprint($PRODUCT_ID, $QUANTITY = 0){
	global $pall_price;
	if ($pall_price){
		$arResult = array(
		"PRODUCT_PRICE_ID" => $pall_price['ID'],
		"PRICE" => $pall_price['P_PRICE'],
		"CURRENCY" => "RUB",
		"CAN_BUY" => "Y"
		);		
	}else{
		$arResult = array(
		"CAN_BUY" => "Y"
		);
	}
	return $arResult;
}

function add2basket_pscreen($PRODUCT_ID, $QUANTITY = 0){
	global $pall_price;
	if ($pall_price){
		$arResult = array(
		"PRODUCT_PRICE_ID" => $pall_price['ID'],
		"PRICE" => $pall_price['P_PRICE'],
		"CURRENCY" => "RUB",
		"CAN_BUY" => "Y"
		);		
	}else{
		$arResult = array(
		"CAN_BUY" => "Y"
		);
	}
	return $arResult;
}


function add2basket_pdesign($PRODUCT_ID, $QUANTITY = 0){
	global $pall_price;
	if ($pall_price){
		$arResult = array(
		"PRODUCT_PRICE_ID" => $pall_price['ID'],
		"PRICE" => $pall_price['P_PRICE'],
		"CURRENCY" => "RUB",
		"CAN_BUY" => "Y"
		);		
	}else{
		$arResult = array(
		"CAN_BUY" => "Y"
		);
	}
	return $arResult;
}

function add2basket_pmake($PRODUCT_ID, $QUANTITY = 0){
	global $pall_price;
	if ($pall_price){
		$arResult = array(
		"PRODUCT_PRICE_ID" => $pall_price['ID'],
		"PRICE" => $pall_price['P_PRICE'],
		"CURRENCY" => "RUB",
		"CAN_BUY" => "Y"
		);		
	}else{
		$arResult = array(
		"CAN_BUY" => "Y"
		);
	}
	return $arResult;
}


function order_pall($PRODUCT_ID, $QUANTITY = 0){

	$price = 0;

	$dbBasketItems = CSaleBasket::GetList( false,array( "FUSER_ID" => CSaleBasket::GetBasketUserID(), "LID" => SITE_ID, "ORDER_ID" => "NULL" ) );
	while ($arItems = $dbBasketItems->Fetch()){
		if (strlen($arItems["CALLBACK_FUNC"]) > 0){
			$price = $arItems['PRICE'];
		}
	}

	$arResult = array(
		"PRICE" => $price
	);

	return $arResult;
}

/*
function cancel_pdesign($PRODUCT_ID, $QUANTITY, $bCancel){
	echo $bCancel;
	exit;
}
*/
?>
