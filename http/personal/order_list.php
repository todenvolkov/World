<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


if (CModule::IncludeModule("sale")){
	echo "<table>\n";
	/*
	if ($_GET["IDS"]>0){
			$dbBasketItems = CSaleBasket::GetList( array("NAME" => "ASC"), array("ORDER_ID" => $_GET["IDS"]), false, false, array() );
			while ($arBasketItems = $dbBasketItems->Fetch()) {
				echo "<pre>";
				print_r($arBasketItems);
				echo "</pre>";
  			}
	}
	*/
	if ($_GET["ID"]>0){
		if ($_GET["STATUS"]!=""){
			CSaleOrder::Update($_GET["ID"], array("STATUS_ID"=>$_GET["STATUS"]) );
		}else if ($_GET["CANCELED"]!=""){
			CSaleOrder::Update($_GET["ID"], array("CANCELED"=>$_GET["CANCELED"]) );
		}else{
			$dbBasketItems = CSaleBasket::GetList( array("NAME" => "ASC"), array("ORDER_ID" => $_GET["ID"]), false, false, array() );
			while ($arBasketItems = $dbBasketItems->Fetch()) {
				$zzz = "";
				$db_res = CSaleBasket::GetPropsList( array( "SORT" => "ASC", "NAME" => "ASC" ), array("BASKET_ID" => $arBasketItems["ID"] ) );
				while ($ar_res = $db_res->Fetch()) {
					if (strpos($ar_res["NAME"],"XML")===false){
						$ar_res["VALUE"] = preg_replace("/\;|\<|\>|\:|\n|\r/","",$ar_res["VALUE"]);
						if (preg_match ("/^FILE\d+$/", $ar_res["CODE"])) {
							$ar_res["VALUE"] = "http://".SITE_SERVER_NAME.$ar_res["VALUE"];
						}
						$zzz.=$ar_res["CODE"].":".$ar_res["VALUE"].";";
					}
				}	
       	
				if ($arBasketItems['CALLBACK_FUNC']=='CatalogBasketCallback'){
					echo "<tr><td>1</td><td>".$arBasketItems["PRODUCT_XML_ID"]."</td><td>".$arBasketItems["NAME"]."</td><td>".$arBasketItems["PRICE"]."</td><td>".intval($arBasketItems["QUANTITY"])."</td><td>".$zzz."</td></tr>\n";
				}
				
				if ($arBasketItems['CALLBACK_FUNC']=='add2basket_pprint'){
					echo "<tr><td>2</td><td>".$arBasketItems["PRODUCT_XML_ID"]."</td><td>".$arBasketItems["NAME"]."</td><td>".$arBasketItems["PRICE"]."</td><td>".intval($arBasketItems["QUANTITY"])."</td><td>".$zzz."</td></tr>\n";
				}
				
				if ($arBasketItems['CALLBACK_FUNC']=='add2basket_pdesign'){
					echo "<tr><td>3</td><td>".$arBasketItems["PRODUCT_XML_ID"]."</td><td>".$arBasketItems["NAME"]."</td><td>".$arBasketItems["PRICE"]."</td><td>".intval($arBasketItems["QUANTITY"])."</td><td>".$zzz."</td></tr>\n";
				}

				if ($arBasketItems['CALLBACK_FUNC']=='add2basket_pscreen'){
					echo "<tr><td>4</td><td>".$arBasketItems["PRODUCT_XML_ID"]."</td><td>".$arBasketItems["NAME"]."</td><td>".$arBasketItems["PRICE"]."</td><td>".intval($arBasketItems["QUANTITY"])."</td><td>".$zzz."</td></tr>\n";
				}

				if ($arBasketItems['CALLBACK_FUNC']=='add2basket_pmake'){
					echo "<tr><td>4</td><td>".$arBasketItems["PRODUCT_XML_ID"]."</td><td>".$arBasketItems["NAME"]."</td><td>".$arBasketItems["PRICE"]."</td><td>".intval($arBasketItems["QUANTITY"])."</td><td>".$zzz."</td></tr>\n";
				}
       	
  			}
		}
	}else{
		$zak_limit = 0;
		$rsSales = CSaleOrder::GetList(array("DATE_INSERT" => "DESC"), Array() );
		while ($arSales = $rsSales->Fetch())
		{
			$personal_profile = "";
			$db_vals = CSaleOrderPropsValue::GetList(
				array("SORT" => "ASC"),
				array("ORDER_ID" => $arSales["ID"])
			);
			while ($arVals = $db_vals->Fetch()){
				$personal_profile .= "<td>".preg_replace("/\;|\<|\>|\:|\n|\r/","",$arVals["VALUE"])."</td>";
			}
			echo "<tr><td>".$arSales["ID"]."</td><td>".$arSales["STATUS_ID"]."</td><td>".$arSales["CANCELED"]."</td><td>".$arSales["DATE_INSERT"]."</td><td>".$arSales["PRICE"]."</td><td>".$arSales["USER_LOGIN"]."</td>".$personal_profile."</tr>\n";
			$zak_limit++;
			if ($zak_limit>500) break;
		}
	}
	echo "</table>";
}
?>
