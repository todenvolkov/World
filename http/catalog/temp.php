<?
define('STOP_STATISTICS', true); 
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php'); 

if (!CModule::IncludeModule("catalog")) die();

$cc = array('','','','ART1','COMM1','FULL_NAME1','SAPL1','OSV1','PIVO1','DIST1','STREET1','SIZE1','','SIDE1','FAME1','KOORD1','PRICE1','');
$name_index = 2;
$group2_index = 1;
$group1_index = 12;


function read_file_csv($filename){
	$result = array();
	$ff = fopen($filename,"r");
	fgets ($ff, 3000);
	while ($data = fgets ($ff, 3000)) {
		$result[] = explode('";"',preg_replace("/\;(\d*)\;/",";\"\$1\";",substr($data,1,strlen($data)-2)));
	}
	fclose($ff);
	return $result;
}


$tmp = CPrice::GetList();
while ($a=$tmp->GetNext()){
	$price[$a['PRODUCT_ID']] = $a['ID'];
}


// ID, NAME, IBLOCK_ID, IBLOCK_SECTION_ID, ACTIVE
$tmp = CIBlockSection::GetList(array(),array("INCLUDE_SUBSECTIONS" => "Y", "IBLOCK_ID"=>array(6,11)));
while ($a=$tmp->GetNext()){
	$sec[ $a['ID'] ] = array( 'N'=>$a['NAME'],'A'=>$a['ACTIVE'],'P'=>$a['IBLOCK_SECTION_ID'],'E'=>0 );
}
foreach ($sec as $key=>$value) {
	if ($value['P']!=''){
		$sec_name_id[ $sec[ $value['P'] ]['N'] ][ $value['N']] = $key;
	}
}


//ID, NAME, IBLOCK_ID, IBLOCK_SECTION_ID, ACTIVE
$tmp = CIBlockElement::GetList(array(),array("INCLUDE_SUBSECTIONS" => "Y", "IBLOCK_ID"=>array(6,11)));
while($a = $tmp->GetNextElement()){
	$i = $a->GetFields();
	$ip = $a->GetProperties();
	if ($i['CODE']!=""){
		$sec2 = $sec[ $i['IBLOCK_SECTION_ID'] ];
		$sec1 = $sec[ $sec2['P'] ];
		
		$i['GROUP2'] = $sec2['N'];
		$i['GROUP1'] = $sec1['N'];
		
		$reflect_db1[ $i['CODE'] ] = $i;
		$reflect_db2[ $i['CODE'] ] = $ip;
		
		//$reflect_db[ $i['CODE'] ] = implode('";"', array($i['CODE'],$sec2['N'],$i['NAME'],$ip['ART1']['VALUE'],$ip['COMM1']['VALUE'],$ip['FULL_NAME1']['VALUE'],$ip['SAPL1']['VALUE'],$ip['PIVO1']['VALUE'],$ip['DIST1']['VALUE'],$ip['STREET1']['VALUE'],$ip['SIZE1']['VALUE'],$sec1['N'],$ip['SIDE1']['VALUE'],$ip['FAME1']['VALUE'],$ip['KOORD1']['VALUE'],$ip['PRICE1']['VALUE'],"RUB") );
	}
} 
//exit;



/*
0 "ID";
1 "Группа";
2 "Наименование";
3 "Артикул";
4 "Комментарий";
5 "НаименованиеПолное";
6 "Саплаер";
7 "Освещение";
8 "Пиво";
9 "Район";
10 "Улица";
11 "Размер";
12 "Вид";
13 "Сторона";
14 "Популярность";
15 "ЯндексКоординаты";
16 "Цена";
17 "Валюта"
*/

$reflect = read_file_csv($_SERVER["DOCUMENT_ROOT"]."/csv/reflect.csv");
for ($i=1;$i<sizeof($reflect);$i++){
	$z = $reflect[$i];
	$update = 0;
	
	$rdb1 = $reflect_db1[ $z[0] ]; // основные свойства
	$rdb2 = $reflect_db2[ $z[0] ]; // параметры

	if ($rdb1 && $rdb2){
	
		if ( $rdb1['NAME'] != htmlspecialchars($z[$name_index]) || $rdb1['GROUP2'] != $z[$group2_index] || $rdb1['GROUP1'] != $z[$group1_index] ){
			$update = 1;
		}else{
			for ($j=0;$j<sizeof($cc);$j++){
				if ( $cc[$j]!=''){
					if ( htmlspecialchars($z[$j]) != $rdb2[ $cc[$j] ]['VALUE']){
						echo $rdb1['CODE']."|".$cc[$j]."|".$z[$j]."|".$rdb2[ $cc[$j] ]['VALUE']."|<br>\n";
						$update = 1;
						break;
					}
				}
			}
		}

		if ($update==1){
			print "1";
			$el = new CIBlockElement; 
			
			$qq = array();
			for ($j=0;$j<sizeof($cc);$j++){
				if ( $cc[$j]!=''){
					$qq[ $cc[$j] ] = $z[$j];
			  	}
			}
                        
			$el->Update($rdb1['ID'], array( "ACTIVE" => "Y", "NAME" => $z[$name_index], "IBLOCK_SECTION" => $sec_name_id[ $z[$group1_index] ][ $z[$group2_index] ] ) );

			CIBlockElement::SetPropertyValuesEx($rdb1['ID'],false,$qq);

			CPrice::Update($price[ $rdb1['ID'] ],array('PRICE' => round($qq['PRICE1']/30,2) ));
		}
	}

}


/*

*/




/*
$tmp = CCatalogProduct::GetList(false,array("ELEMENT_IBLOCK_ID"=>array(6,11)) );
while ($a=$tmp->GetNext()){
	echo $a['ID']."<br>";

}
*/

/*
foreach ($sec_name as $key=>$value) {
	foreach ($value as $key2=>$value2) {
		echo $sec_name[$key][$key2]['N']."<br>";
	}
}
*/
?>
