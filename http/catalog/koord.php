<?
define('STOP_STATISTICS', true); 
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php'); 


if (!CModule::IncludeModule("catalog")) die();

echo "<table>";
$tmp3 = CIBlockElement::GetList(array("NAME" => "ASC"),array("INCLUDE_SUBSECTIONS" => "Y", "ACTIVE"=>"Y","IBLOCK_ID"=>array(6,11)));
while($ob = $tmp3->GetNextElement()){
	$item = $ob->GetFields();
	$itemP = $ob->GetProperties();
	if ($item['CODE']!=""){
	//if ($elem['"'.$item['CODE'].'"']!=1){
		$a = split(',',$itemP['KOORD1']['VALUE']);
		print "<tr><td>".$item['CODE']."</td><td>".$item['NAME']."</td><td>".$a[0]."</td><td>".$a[1]."</td></tr>";
	//}
	}
} 
echo "</table>";

?>
