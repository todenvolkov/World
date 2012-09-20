<?
define('STOP_STATISTICS', true); 
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php'); 




if (!CModule::IncludeModule("catalog")) die();




//CIBlockElement::SetPropertyValueCode(4398, "PREVIEW_TEXT", ".");
//$res = CIBlockElement::Update(4398, array("DETAIL_PICTURE" => CFile::MakeFileArray($photo)));

$ff = fopen("/home/temp/public_html/0/photo.txt","r");
$c = fread($ff, filesize("/home/temp/public_html/0/photo.txt"));

$cc = explode("\n",$c);
for ($i=1;$i<sizeof($cc);$i++){
	$ccc = explode("\t",rtrim($cc[$i]));
	if (strlen($ccc[1])>3){
		$elem[ereg_replace("РЈРў","UT",$ccc[0])] = urldecode($ccc[1]);
	}
}


$tmp1 = CCatalog::GetList(array(),array());
while ($catalog = $tmp1->Fetch()){
	$tmp2 = GetIBlockSectionList($catalog["ID"]);
	while($section = $tmp2->GetNext()) {
		if ($section['DEPTH_LEVEL']==1){
			
		}else{
			$sec2_id[] = $section["ID"];
		}
	}

	//CFile::GetPath($section["PICTURE"])
	$tmp3 = CIBlockElement::GetList(array("NAME" => "ASC"),array("SECTION_ID" => $sec2_id, "INCLUDE_SUBSECTIONS" => "Y", "ACTIVE"=>"Y", "SECTION_ACTIVE"=>"Y"));
	while($ob = $tmp3->GetNextElement()){
		$item = $ob->GetFields();
		$itemP = $ob->GetProperties();
		//echo $item["NAME"]."<br>";
		//if ($item["CODE"]=="UT000000825"){
		$file = basename($elem[$item["CODE"]]);
		if (strlen($elem[$item["CODE"]])>3 && file_exists("img/".$file)==true){
			//echo $item["ID"]."<br>";
			//$el = new CIBlockElement;
			//$el->Update($item["ID"], array("DETAIL_PICTURE" => CFile::MakeFileArray("img/".$file) ),false,false,true);
		}
	} 
}


?>
