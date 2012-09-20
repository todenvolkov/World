<?
function read_file_csv($filename){
	$result = array();
	$ff = fopen($filename,"r");
	$data = fgetcsv ($ff, 0, ";");
	while ($data = fgetcsv ($ff, 0, ";")) {
		$result[ $data[0] ] = $data;
	}
	fclose($ff);
	return $result;
}
$reflect = read_file_csv($_SERVER["DOCUMENT_ROOT"]."/csv/reflect.csv");

//for ($i=0;$i<sizeof($reflect);$i++){
//	$reflect[ $reflect_tmp[$i][0] ] = $reflect_tmp[$i];
//}
foreach ($reflect as $key => $value){
	echo $key." ".$value[1]."<br>";
}

?>