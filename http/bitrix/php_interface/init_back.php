<?
/*
function imTranslite($str){
   static $tbl= array(
      'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ж'=>'g', 'з'=>'z',
      'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p',
      'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'ы'=>'y', 'э'=>'e', 'А'=>'A',
      'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Е'=>'E', 'Ж'=>'G', 'З'=>'Z', 'И'=>'I',
      'Й'=>'Y', 'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R',
      'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F', 'Ы'=>'Y', 'Э'=>'E', 'ё'=>"yo", 'х'=>"h",
      'ц'=>"ts", 'ч'=>"ch", 'ш'=>"sh", 'щ'=>"shch", 'ъ'=>"", 'ь'=>"", 'ю'=>"yu", 'я'=>"ya",
      'Ё'=>"YO", 'Х'=>"H", 'Ц'=>"TS", 'Ч'=>"CH", 'Ш'=>"SH", 'Щ'=>"SHCH", 'Ъ'=>"", 'Ь'=>"",
      'Ю'=>"YU", 'Я'=>"YA", ' '=>"_", '№'=>"", '«'=>"<", '»'=>">", '—'=>"-","'"=>'_','"'=>'_'
   );
    return strtr($str, $tbl);
} 
*/

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("MyClass", "OnBeforeIBlockElementAddHandler"));

/*
AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("MyClass", "OnBeforeIBlockElementAddHandler"));
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("MyClass", "OnBeforeIBlockElementUpdateHandler"));

AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("MyClass", "OnAfterIBlockElementAddHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("MyClass", "OnAfterIBlockElementUpdateHandler"));
*/
class MyClass
{
	function OnBeforeIBlockElementUpdateHandler(&$arFields){

		#global $APPLICATION;
		#$APPLICATION->throwException("Введите мнемонический код. (ID:".$arFields["CODE"].")");
		#return false;
		
		/*
		$s_Name = trim($arFields['NAME']);
		if(strlen($arFields['CODE']) <= 0){
			$arFields['CODE'] = translit($s_Name);
		}
		*/

		
		#$arFields['PROPERTY_VALUES']['24'][$arFields['ID'].":24"] - фотография
		
		
		/*
		$tmp = CIBlockElement::GetList(array(),array("ID" =>$arFields['ID']));
		while($ob = $tmp->GetNextElement()){
			$item = $ob->GetFields();
			$itemP = $ob->GetProperties();
			echo "<pre>"; print_r($item); echo "</pre>";
			echo "<pre>"; print_r($itemP); echo "</pre>";
			echo "old:".$itemP["FOTO1"]["VALUE"]."<br>";
		}
		exit;
		*/
	}

	function OnAfterIBlockElementUpdateHandler(&$arFields){
		/*
		$tmp = CIBlock::GetProperties($arFields['IBLOCK_ID'],array(),array("CODE"=>"FOTO1"));
		if($res_arr = $tmp->Fetch()){ 
			$FOTO1_ID = $res_arr["ID"];
		}
		echo $arFields['PROPERTY_VALUES'][$FOTO1_ID][$arFields['ID'].":".$FOTO1_ID]["VALUE"];
		*/
		/*
		$tmp = CIBlockElement::GetList(array(),array("ID" =>$arFields['ID']));
		while($ob = $tmp->GetNextElement()){
			$itemP = $ob->GetProperties();
			$filename = imTranslite(basename(rtrim($itemP['FOTO1']['VALUE'],'/')));

			
			if(file_exists('catalog_img/'.$filename)==false) {

				$ff = CFile::MakeFileArray($itemP['FOTO1']['VALUE']);
				$ff["name"] = $filename;
				//CFile::SaveFile($ff, 'catalog_img/');

				//CAllFile::ResizeImage(&$arFile,array("width" => 150,"height" => 150),BX_RESIZE_IMAGE_EXACT);

				

				//CFile::MakeFileArray($itemP['FOTO1']['VALUE']);

			}

		}*/
	}
	
	function OnBeforeIBlockElementAddHandler(&$arFields){
		
		$arFields["CODE"] = $arFields["XML_ID"];

		/*
		while (list($key, $value) = each ($arFields)) {
			$zz.=$key."=>".$value.";<br>";
		}
		global $APPLICATION;
		$APPLICATION->throwException("Введите мнемонический код.<br>$zz"."111".$arFields["DETAIL_PICTURE"]);
		return false;
		*/
	}


	function OnAfterIBlockElementAddHandler(&$arFields){
		
		/*
		$tmp = CIBlockElement::GetList(array(),array("ID" =>$arFields['ID']));
		while($ob = $tmp->GetNextElement()){
			$item = $ob->GetFields();
			$itemP = $ob->GetProperties();
			echo "<pre>"; print_r($item); echo "</pre>";
			echo "<pre>"; print_r($itemP); echo "</pre>";
			
			//echo $itemP['FOTO1']['VALUE'];
		}

		exit;
		*/
		//CIBlockElement::SetPropertyValues($arFields['ID'], $arFields["IBLOCK_ID"], "123123", "FOTO1");
	}
}
?>
