<?
define('STOP_STATISTICS', true); 
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php'); 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");
//$GLOBALS['APPLICATION']->RestartBuffer(); 

if (!CModule::IncludeModule("catalog")) die();

$img_dir1 = $_SERVER["DOCUMENT_ROOT"].'/outdoor_img/';
$img_dir2 = $_SERVER["DOCUMENT_ROOT"].'/outdoor_img/preview/';



$tmp3 = CIBlockElement::GetList(array("NAME" => "ASC"),array("INCLUDE_SUBSECTIONS" => "Y", "ACTIVE"=>"Y","IBLOCK_ID"=>array(6,11)));
while($ob = $tmp3->GetNextElement()){
	$item = $ob->GetFields();
	
	//echo "<pre>"; print_r($item); echo "</pre>";

	if ($item['CODE']!=""){
		//print "<tr><td>".$item['CODE']."</td><td>".$item['NAME']."</td><td>".$f."</td></tr>";
		//$code[] = $item['CODE'];
		//$pic1[]	= $item["DETAIL_PICTURE"];
		//$pic2[]	= $item["PREVIEW_PICTURE"];

		$file_exist_db[ $item['CODE'] ] = 1;
	}
} 



$ff = fopen($_SERVER["DOCUMENT_ROOT"].'/outdoor_img/list.txt',"r");
$cc = explode("\n",fread($ff, filesize( $_SERVER["DOCUMENT_ROOT"].'/outdoor_img/list.txt' )));
fclose($ff);
for ($i=0;$i<sizeof($cc);$i++){
	$zz = explode(" ",$cc[$i]);
	$file_size_list[ $zz[0] ] = $zz[1];
}

$handle=opendir($img_dir1); 
while (false!==($file = readdir($handle))) { 
	if (preg_match ("/^(.*)\.jpg$/i", $file, $ss)) {
		$file_exist[ $ss[1] ] = 1;
	}
}
closedir($handle);


foreach ($file_exist as $key=>$value){
		clearstatcache();
		if ($file_exist_db[ $key ]==1){ // construction in database
			if ( $file_size_list[ $key ] != filesize( $img_dir1.$key.'.jpg' ) || file_exists( $img_dir2.$key.'.jpg' )===false){ // recreate preview
				CWizardUtil::CreateThumbnail( $img_dir1.$key.'.jpg', $img_dir2.$key.'.jpg' , 206, 155);
			}
		}else{ // delete files
			unlink( $img_dir1.$key.'.jpg' );
			if ( file_exists( $img_dir2.$key.'.jpg' ) ) unlink( $img_dir2.$key.'.jpg' );
		}
}


$handle=opendir($img_dir1); 
while (false!==($file = readdir($handle))) { 
	if (preg_match ("/^(.*)\.jpg$/i", $file, $ss)) {
		$file_write_size[] = $ss[1].' '.filesize( $img_dir1.$file );
	}
}
closedir($handle);
$ff = fopen($_SERVER["DOCUMENT_ROOT"].'/outdoor_img/list.txt',"w");
fwrite($ff, implode("\n",$file_write_size));
fclose($ff);


exit;





/*
for ($i=0;$i<sizeof($code);$i++){
//for ($i=0;$i<2;$i++){
	$f0 = $_SERVER["DOCUMENT_ROOT"].CFile::GetPath( $pic1[$i] );
	$f1 = $img_dir1.$code[$i].'.jpg';
	$f2 = $img_dir2.$code[$i].'.jpg';

	$f9[] = $code[$i].' '.filesize($f0);

	copy( $f0, $f1);
	CWizardUtil::CreateThumbnail( $f1, $f2 , 206, 155);

	//CFile::Delete( $pic1[$i] );
	//CFile::Delete( $pic2[$i] );
}

$ff = fopen($_SERVER["DOCUMENT_ROOT"].'/outdoor_img/list.txt',"w");
fwrite($ff, implode("\n",$f9));
fclose($ff);
*/
?>
