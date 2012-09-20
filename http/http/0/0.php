<?
define('STOP_STATISTICS', true); 
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php'); 




if (!CModule::IncludeModule("catalog")) die();



// read price index 1 - Base
$db_res = CPrice::GetList(array(),array("CATALOG_GROUP_ID" => 1 ) );
while ($ar_res = $db_res->Fetch()){
    $tmp_price[$ar_res["PRODUCT_ID"]] = $ar_res["PRICE"];
}

//$tmp1 = CCatalog::GetList(array(),array("SUBSCRIPTION" => "Y"));
$tmp1 = CCatalog::GetList(array(),array());
while ($catalog = $tmp1->Fetch()){
	$tmp2 = GetIBlockSectionList($catalog["ID"]);
	while($section = $tmp2->GetNext()) {
		if ($section['DEPTH_LEVEL']==1){
			$rz_len = sizeof($rz_html);
			$rz_index[$section["ID"]] = $rz_len;
			$rz_html[] = "<td width=70>".$section["NAME"]."<br><a href=\"#\" onClick=\"javascript:return show_hide_overlay(".$rz_len.");\"><img src=\"".CFile::GetPath($section["PICTURE"])."\" border=\"0\" title=\"".$section["NAME"]."\" id=\"imgc_".$rz_len."\"></a></td>";
			
		}else{
			$rz_cat2[] = $section["NAME"];
			$icon2_files[]="'".CFile::GetPath($section["PICTURE"])."'";
			$sec2_id[] = $section["ID"];
			$rz_index2[$section["ID"]] = sizeof($sec2_id)-1;
			$rz_index2_parent[ $section["ID"] ] = $section["IBLOCK_SECTION_ID"];
		}
	}

	$tmp3 = CIBlockElement::GetList(array("NAME" => "ASC"),array("SECTION_ID" => $sec2_id, "INCLUDE_SUBSECTIONS" => "Y", "ACTIVE"=>"Y", "SECTION_ACTIVE"=>"Y"));
	while($ob = $tmp3->GetNextElement()){
		$itemP = $ob->GetProperties();
		if ($itemP["KOORD1"]["VALUE"]!=""){
			$item = $ob->GetFields();
			$koords = explode(",",$itemP["KOORD1"]["VALUE"]);
			$split_name = explode("СЃС‚РѕСЂРѕРЅР° ",preg_replace('/\s+/',' ',$item["NAME"]));
			
			//if ($item["NAME"]=="РџСЂРёР·РјР°С‚СЂРѕРЅ 3*12 СѓР». РњР°Р»С‹РіРёРЅР° - Рњ.РўРѕСЂРµР·Р°, СЃС‚РѕСЂРѕРЅР° Рђ"){
			
			//echo "<pre>";
			//print_r($item);
			//echo "</pre>";
			if ($item["IBLOCK_SECTION_ID"]==112){
				echo $item["NAME"]."<br>";
			}

			$rz_items[$split_name[0]][] = array(
				"ID"		=> $item["ID"],
				"NAME"		=> $item["NAME"],
				"SIDE"		=> $split_name[1],
				"CODE"		=> $item["CODE"],
				"LON"		=> $koords[1],
	 			"LAT"		=> $koords[0],
	 			//"FOTO"		=> ereg_replace("'","",$itemP["FOTO1"]["VALUE"]),
	 			"FOTO1"		=> $p1,
	 			"FOTO2"		=> $p2,
	 			"ART"		=> $itemP["ART1"]["VALUE"],
	 			"CAT"		=> $rz_index[ $rz_index2_parent[$item["IBLOCK_SECTION_ID"]] ],
	 			"CAT2"		=> $rz_index2[$item["IBLOCK_SECTION_ID"]],
	 			"PRICE"		=> $tmp_price[$item["ID"]]
			);
		}
	} 
}



?>