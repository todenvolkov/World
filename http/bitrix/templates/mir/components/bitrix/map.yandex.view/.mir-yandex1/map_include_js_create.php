<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$status_file	= $_SERVER["DOCUMENT_ROOT"]."/csv/status.csv";
$reflect_file	= $_SERVER["DOCUMENT_ROOT"]."/csv/reflect.csv";
$include_js_file= $_SERVER["DOCUMENT_ROOT"].$templateFolder."/map_include.js";

$status_f = stat($include_js_file);
$ctime = time();


if (($ctime-$status_f["mtime"]>3600) || $force_reload_map==1){

// reload fotos:
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");
	$img_dir1 = $_SERVER["DOCUMENT_ROOT"].'/outdoor_img/';
	$img_dir2 = $_SERVER["DOCUMENT_ROOT"].'/outdoor_img/preview/';

	$tmp3 = CIBlockElement::GetList(array("NAME" => "ASC"),array("INCLUDE_SUBSECTIONS" => "Y", "ACTIVE"=>"Y","IBLOCK_ID"=>array(6,11)));
	while($ob = $tmp3->GetNextElement()){
		$item = $ob->GetFields();
		if ($item['CODE']!=""){
			$file_exist_db[ $item['CODE'] ] = 1;
		}
	} 
	clearstatcache();
	$ff = fopen($_SERVER["DOCUMENT_ROOT"].'/outdoor_img/list.txt',"r");
	$cc = explode("\n",fread($ff, filesize( $_SERVER["DOCUMENT_ROOT"].'/outdoor_img/list.txt' )));
	fclose($ff);
	for ($i=0;$i<sizeof($cc);$i++){
		$zz = explode(" ",$cc[$i]);
		$file_size_list[ $zz[0] ] = $zz[1];
	}
	$handle=opendir($img_dir1); 
	while (false!==($file = readdir($handle))) { 
		if (preg_match ("/^(.*)\.JPG$/i", $file, $ss)) {
			$file_exist[ $ss[1] ] = 1;
		}
	}
	closedir($handle);
	foreach ($file_exist as $key=>$value){
			clearstatcache();
			if ($file_exist_db[ $key ]==1){ // construction in database
				if ( $file_size_list[ $key ] != filesize( $img_dir1.$key.'.JPG' ) || file_exists( $img_dir2.$key.'.JPG' )===false){ // recreate preview
					CWizardUtil::CreateThumbnail( $img_dir1.$key.'.JPG', $img_dir2.$key.'.JPG' , 206, 155);
				}
			}else{ // delete files
				unlink( $img_dir1.$key.'.JPG' );
				if ( file_exists( $img_dir2.$key.'.JPG' ) ) unlink( $img_dir2.$key.'.JPG' );
			}
	}
	$handle=opendir($img_dir1); 
	while (false!==($file = readdir($handle))) { 
		if (preg_match ("/^(.*)\.JPG$/i", $file, $ss)) {
			$file_write_size[] = $ss[1].' '.filesize( $img_dir1.$file );
		}
	}
	closedir($handle);
	$ff = fopen($_SERVER["DOCUMENT_ROOT"].'/outdoor_img/list.txt',"w");
	fwrite($ff, implode("\n",$file_write_size));
	fclose($ff);
///////////////////////////////////




$f = fopen($status_file, "r") or die("Ошибка!");

$contents = explode("\n",fread($f,filesize($status_file)));
for ($i=1; $i<sizeof($contents); $i++){  
	$data=explode(';', ereg_replace('"','',rtrim($contents[$i])));
	
	if (sizeof($data)>3){
		$datas1 = explode(".",$data[2]); $datas1 = mktime(0,0,0,$datas1[1],$datas1[0],$datas1[2]);
		$datas2 = explode(".",$data[3]); $datas2 = mktime(0,0,0,$datas2[1],$datas2[0],$datas2[2]);

		$tmp_reserved[$data[1]][$data[0]][] = "['".$datas1."','".$datas2."']";

		if ($datas1 < $ctime && $ctime < $datas2) $tmp_status[$data[0]] = $data[1];
	}
}
fclose($f);


// read price index 1 - Base
$db_res = CPrice::GetList(array(),array("CATALOG_GROUP_ID" => 1 ) );
while ($ar_res = $db_res->Fetch()){
    $tmp_price[$ar_res["PRODUCT_ID"]] = $ar_res["PRICE"];
}


//base catalog = рефлексы
$tmp1 = CCatalog::GetList(array(),array( "ID"=>array(6,11) ));
while ($catalog = $tmp1->Fetch()){
        
        $tmp2 = GetIBlockSectionList($catalog["ID"]);
	while($section = $tmp2->GetNext()) {
		//echo $section["NAME"]."<br>";
		if ($section['DEPTH_LEVEL']==1){
			$rz_len = sizeof($rz_html);
			$rz_index[$section["ID"]] = $rz_len;
			$rz_html[] = "<td width=70>".$section["NAME"]."<br><a href=\"#\" onClick=\"javascript:return show_hide_overlay(".$rz_len.");\"><img src=\"".CFile::GetPath($section["PICTURE"])."\" border=\"0\" title=\"".$section["NAME"]."\" id=\"imgc_".$rz_len."\"></a></td>";
			
		}else{
			$rz_cat2[] = '"'.$section["NAME"].'"';
			$icon2_files[]="'".CFile::GetPath($section["PICTURE"])."'";
			$sec2_id[] = $section["ID"];
			$rz_index2[$section["ID"]] = sizeof($sec2_id)-1;
			$rz_index2_parent[ $section["ID"] ] = $section["IBLOCK_SECTION_ID"];
		}
	}


	$tmp3 = CIBlockElement::GetList(array("NAME" => "ASC"),array("IBLOCK_ID" => $catalog["ID"], "SECTION_ID" => $sec2_id, "INCLUDE_SUBSECTIONS" => "Y", "ACTIVE"=>"Y", "SECTION_ACTIVE"=>"Y"));
	while($ob = $tmp3->GetNextElement()){
		$itemP = $ob->GetProperties();
		if ($itemP["KOORD1"]["VALUE"]!=""){
			$item = $ob->GetFields();
			$koords = explode(",",$itemP["KOORD1"]["VALUE"]);
			$split_name = explode("сторона",preg_replace('/\s*/','',$item["NAME"]));
			//$p1 = CFile::GetPath($item["PREVIEW_PICTURE"]);// if ($p1=="") $p1 = SITE_TEMPLATE_PATH."/images/7x7_Op.gif";
			//$p2 = CFile::GetPath($item["DETAIL_PICTURE"]);// if ($p2=="") $p2 = SITE_TEMPLATE_PATH."/images/7x7_Op.gif";
			$p2 = ( file_exists( $img_dir1.$item["CODE"].'.JPG' )===true ) ? '/outdoor_img/'.$item["CODE"].'.JPG' : '';
			$p1 = ( file_exists( $img_dir2.$item["CODE"].'.JPG' )===true ) ? '/outdoor_img/preview/'.$item["CODE"].'.JPG' : '';
			
			
			if ($split_name[1]=='') $split_name[1] = 'A';
			
			$rz_items[$split_name[0]][] = array(
				"ID"		=> $item["ID"],
				"NAME"		=> $item["NAME"],
				"SIDE"		=> $split_name[1],
				"CODE"		=> $item["CODE"],
				"LON"		=> $koords[1],
	 			"LAT"		=> $koords[0],
	 			"FOTO1"		=> $p1,
	 			"FOTO2"		=> $p2,
	 			"ART"		=> $itemP["ART1"]["VALUE"],
	 			"CAT"		=> $rz_index[ $rz_index2_parent[$item["IBLOCK_SECTION_ID"]] ], //категория
	 			"CAT2"		=> $rz_index2[$item["IBLOCK_SECTION_ID"]], //подкатегория
	 			"PRICE"		=> $tmp_price[$item["ID"]],
	 			"MCAT"		=> $catalog["ID"] 	// каталог - рефлексы, экраны...
			);
		}
	} 
}

$status_html = array(
	''=>'<span style=\"color:#65781F\"><b>Свободен</b></span>',
	'1'=>'<span style=\"color:#C70000\">Занят</span>',
	'2'=>'<span style=\"color:#D5CD00\">Забронирован</span>'
);

// если у одних сторон - разные координаты, берем координату первой стороны
foreach ($rz_items as $key => $value) {
	$rz_items_koord[$value[0]["LON"].$value[0]["LAT"]][$key] = $value;
}

foreach ($rz_items_koord as $combu) {
	
	
	$tmp_len = 0;
	$combi_sides = array();
	foreach ($combu as $key => $value) {
		$combi_sides[] = sizeof($rz_item) + $tmp_len; // массив индексов комби-конструкций
		$tmp_len++;
	}
	if (sizeof($combi_sides)==1) $combi_sides = array();



	foreach ($combu as $value) {
		$sides = array();
		
		$item_all_len = sizeof($rz_item_all); // первая сторона - главная

		for ($i=0;$i<sizeof($value);$i++){
			$sides[] = sizeof($rz_item_all);
       	
			$rz_item_all[] = '{'.
			'"PARENT":'.sizeof($rz_item).','.
			'"BASKET":0,'.
			'"STATUS":"'.$tmp_status[$value[$i]["CODE"]].'",'.
			'"PURCHASED":['.implode(",",$tmp_reserved[1][$value[$i]["CODE"]]).'],'.
			'"RESERVED":['.implode(",",$tmp_reserved[2][$value[$i]["CODE"]]).'],'.
			'"ID":"'.$value[$i]["ID"].'",'.
			'"NAME":"'.$value[$i]["NAME"].'",'.
			'"PRICE":"'.$value[$i]["PRICE"].'",'.
			'"FOTO1":"'.$value[$i]["FOTO1"].'",'.
			'"FOTO2":"'.$value[$i]["FOTO2"].'",'.
			'"SIDE":"'.$value[$i]["SIDE"].'",'.
			'"AC":"'.$value[$i]["ART"].' / '.$value[$i]["CODE"].'"'.
			'}';
		}
		$rz_items_sides[] = "[".implode(",",$sides)."]";
		$rz_item[] = '{"ID":"'.$item_all_len.'","LON":"'.$value[0]["LON"].'", "LAT":"'.$value[0]["LAT"].'", "MCAT":"'.$value[0]["MCAT"].'", "CAT":"'.$value[0]["CAT"].'", "CAT2":"'.$value[0]["CAT2"].'", "COMBI":['.implode(",",$combi_sides).'], "BASKET":0,"STATUS":1}';
	}
}



// ************************
// чтение видео-рекламы
// ************************

function read_file_csv($filename){
	$result = array();
	$ff = fopen($filename,"r");
	$data = fgetcsv ($ff, 1000, ";");
	while ($data = fgetcsv ($ff, 1000, ";")) {
		$result[] = $data;
	}
	fclose($ff);
	return $result;
}

$aa = read_file_csv($_SERVER["DOCUMENT_ROOT"]."/csv/discount.csv");
for ($i=0;$i<sizeof($aa);$i++){
	if ($aa[$i][1]==0){
		$base_count = intval( $aa[$i][0] );
		$base_price = doubleval( str_replace(",",".", $aa[$i][2] ));
	}else{
		$base_screen[ intval( $aa[$i][0] ) ][ intval( $aa[$i][1] ) ] = doubleval( str_replace(",",".", $aa[$i][2] ));
	}
	
}

arsort ($base_screen);
reset ($base_screen);
foreach($base_screen as $key => $value){
	arsort ($value);
	reset ($value);
	$tmp = array();
	foreach($value as $key1 => $value1){
		$tmp[] = $key1.":".$value1;
	}
	$base_j[] = $key.":{".implode(",",$tmp)."}";
}
// ************************
// ************************
// ************************



$ff = fopen($include_js_file,"w");
if ($ff){
	fwrite($ff,"var map_point_data = [".implode(",",$rz_item)."];\n");
	fwrite($ff,"var map_point_sides = [".implode(",",$rz_items_sides)."];\n");
	fwrite($ff,"var map_point_cat2 = [".implode(",",$rz_cat2)."];\n");
	fwrite($ff,"var map_point_all = [".implode(",",$rz_item_all)."];\n");
	fwrite($ff,"var map_imgs = [".implode(",",$icon2_files)."];\n");
	fwrite($ff,"var map_header = '".implode("",$rz_html)."';\n");
	fwrite($ff,"var map_showhide_len = ".sizeof($rz_index).";\n");
	
	fwrite($ff,"var screen_count = ".$base_count.";\n");
	fwrite($ff,"var screen_price = ".$base_price.";\n");
	fwrite($ff,"var screen_base = {".implode(", ",$base_j)."};\n");

	fclose($ff);
}


clearstatcache();
touch( $include_js_file, $ctime );

}

?>
