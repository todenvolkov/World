<?
define('STOP_STATISTICS', true); 
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php'); 
//$GLOBALS['APPLICATION']->RestartBuffer(); 


srand ((double) microtime() * 1000000);

$cc = 0;
if (!CModule::IncludeModule("catalog")) die();

/*
$ff = fopen("/home/temp/public_html/0/koord.csv","r");
$c = fread($ff, filesize("/home/temp/public_html/0/koord.csv"));

$cc = explode("\n",$c);
for ($i=0;$i<sizeof($cc);$i++){
	$ccc = explode(";",rtrim($cc[$i]));
	if (strlen($ccc[1])>3){
		$cccc = explode(",",rtrim($ccc[1]));
		$elem[$ccc[0]] = $cccc[1].','.$cccc[0];
	}
}
*/

echo "<table>";
$tmp1 = CCatalog::GetList();
while ($catalog = $tmp1->Fetch()){

	$tmp2 = GetIBlockSectionList($catalog["ID"]);
	while($section = $tmp2->GetNext()) {
		$rz[] = $section;
		$rz_index[$section["ID"]] = sizeof($rz)-1;
	}

	$tmp3 = CIBlockElement::GetList(array("NAME" => "ASC"),array("INCLUDE_SUBSECTIONS" => "Y", "ACTIVE"=>"Y","IBLOCK_ID"=>array(6,11)));
	while($ob = $tmp3->GetNextElement()){
		//$cc+=1;
		//$a = 57.1290252668 + rand(-100,100)/4000;
		//$b = 65.5080700427 + rand(-100,100)/700;
		
		//CIBlockElement::SetPropertyValues($item["ID"], $catalog["ID"], "$a,$b", "KOORD1");
		
		//if ($elem[$item["CODE"]]!=""){
		//	CIBlockElement::SetPropertyValues($item["ID"], $catalog["ID"], $elem[$item["CODE"]], "KOORD1");
		//}
		
		
		
		//if ($cc>200){
		//	CIBlockElement::SetPropertyValues($item["ID"], $catalog["ID"], "", "KOORD1");
		//}else{
		//	CIBlockElement::SetPropertyValues($item["ID"], $catalog["ID"], "$a,$b", "KOORD1");
		//}

		
		$item = $ob->GetFields();
		$itemP = $ob->GetProperties();
		
		//echo "<pre>"; print_r($item); echo "</pre>";
		//echo "<pre>"; print_r($itemP); echo "</pre>";
		if ($item['CODE']!=""){
			$a = split(',',$itemP['KOORD1']['VALUE']);
			print "<tr><td>".$item['CODE']."</td><td>".$item['NAME']."</td><td>".$a[0]."</td><td>".$a[1]."</td></tr>";
		}
	} 
}
echo "</table>";
/*
$db_res = CPrice::GetList(
        array(),
        array(
                "PRODUCT_ID" => $PRODUCT_ID,
                "CATALOG_GROUP_ID" => $PRICE_TYPE_ID
            )
    );
if ($ar_res = $db_res->Fetch())
{
    echo CurrencyFormat($ar_res["PRICE"], $ar_res["CURRENCY"]);
}
else
{
    echo "Р¦РµРЅР° РЅРµ РЅР°Р№РґРµРЅР°!";
}
*/
?>
