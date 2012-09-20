<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

function remake_date($date){
	$aa = explode('.',$date);
	return sprintf("%02d.%02d.%04d", $aa[0], $aa[1], $aa[2]);
}


if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))

{

//$res1 = Add2BasketByProductID(3818,3.5);
//echo $res1;
//exit;
	
	
	$azz = explode(',',$_REQUEST[PARAM]);

	$result = 'false';

	for ($i=0;$i<sizeof($azz);$i+=3){
		if (preg_match("/\d+\.\d+\.\d+/",$azz[$i+1]) && preg_match("/\d+\.\d+\.\d+/",$azz[$i+2])){
			$res1 = Add2BasketByProductID($_REQUEST[ID],$azz[$i],
				array(
					array("NAME" => "Дата начала", "CODE" => "DATE1", "SORT"=>"100", "VALUE" => remake_date($azz[$i+1])),
					array("NAME" => "Дата конца", "CODE" => "DATE2", "SORT"=>"101", "VALUE" => remake_date($azz[$i+2]))
				)
			);
 		}
		if ($res1){ $result='true'; }
        }
        echo $result;

}
?>
