<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CUtil::InitJSCore(array('window'));

if (!CModule::IncludeModule("catalog")) die();

//получение списка каталогов
//$blocks = array();
//$tmp1 = CCatalog::GetList(array(),array("SUBSCRIPTION" => "Y"));
//while ($ar_res = $tmp1->Fetch()){
//	$blocks[] = $ar_res["ID"];
//}
// получение списка товаров
//$tmp1 = CCatalogProduct::GetList(array(),array("SUBSCRIPTION" => "Y","@ELEMENT_IBLOCK_ID" => $blocks),false,array() );
//while ($ar_res = $tmp1->Fetch()){ echo "<pre>"; print_r($ar_res); echo "</pre>"; }


//$res = CIBlock::GetList(Array(), Array('TYPE'=>'catalog','SITE_ID'=>SITE_ID,'ACTIVE'=>'Y',"CNT_ACTIVE"=>"Y","!CODE"=>'my_products'), true);
//while($ar_res = $res->Fetch()){ 	echo "<pre>"; print_r($ar_res); echo "</pre>";	}

//$iblocks = GetIBlockList("catalog");
//while($arIBlock = $iblocks->GetNext()){   echo $arIBlock["ID"]."<br>";  }

$tmp1 = CCatalog::GetList(array(),array("SUBSCRIPTION" => "Y"));
while ($catalog = $tmp1->Fetch()){
	$tmp2 = GetIBlockSectionList($catalog["ID"]);
	while($section = $tmp2->GetNext()) {
		$rz[] = $section;
		$rz_index[$section["ID"]] = sizeof($rz)-1;
		$icon_files[]="'".CFile::GetPath($section["PICTURE"])."'";
		$rz_html[] = "<td><a href=\"#\"><img src=\"".CFile::GetPath($section["PICTURE"])."\" border=0 title=\"".$section["NAME"]."\"></a></td>";
	}

//for ($i=0;$i<sizeof($rz);$i++){
//	echo "<img src=\"".CFile::GetPath($rz[$i]["PICTURE"])."\" border=0>".$rz[$i]["NAME"]." &nbsp;";
//}

	
	$tmp3 = CIBlockElement::GetList(array("NAME" => "ASC"),array("IBLOCK_ID" =>$catalog["ID"], "INCLUDE_SUBSECTIONS" => "Y", "ACTIVE"=>"Y"));
	while($ob = $tmp3->GetNextElement()){
		$itemP = $ob->GetProperties();
		if ($itemP["KOORD1"]["VALUE"]!=""){
			$item = $ob->GetFields();
			$koords = explode(",",$itemP["KOORD1"]["VALUE"]);
			
			//$rz_item[] = '{"LON":'.$koords[1].', "LAT":'.$koords[0].', "STYLE":'.$rz_index[$item["IBLOCK_SECTION_ID"]].', "TEXT":"<table width=\"250\" border=\"0\" cellpadding=\"0\" cellspacing=\"2\" bgcolor=\"#FFFFFF\"><tr><td nowrap><p class=\"Header\">'.$rz[$rz_index[$item["IBLOCK_SECTION_ID"]]]["NAME"].'</p></td></tr><tr><td>'.$item["NAME"].'</td></tr><tr><td nowrap>Стороны: <span class=\"Header\">А1</span> <span><a href=\"B1\">Б1</a></span></td></tr><tr><td nowrap><p><img src=\"'.$itemP["FOTO1"]["VALUE"].'\" width=\"250\" height=\"155\" border=\"0\" onClick=\'javascript:show_window_photo(\"'.$itemP["FOTO1"]["VALUE"].'\");\' style=\"cursor: pointer;}\"></p></td></tr><tr><td align=\"right\" nowrap><p class=\"Grd\">999А / '.$item["CODE"].'</p></td></tr><tr><td nowrap>Свободен для размещения | <a href=\"\" onClick=\'javascript:return show_window_calendar();\'>Календарь </a></td></tr><tr><td align=\"right\" nowrap><p>&nbsp;</p><p><a href=\"\" onClick=\'javascript:alert(\"Не помещу\");return false;\'>Поместить в Корзину</a></p><p>&nbsp;</p></td></tr></table>"}';
			$text = ''.
			'<table width=\"250\" border=\"0\" cellpadding=\"0\" cellspacing=\"2\" bgcolor=\"#FFFFFF\">'.
			'<tr>'.
				'<td nowrap><p class=\"Header\">'.$rz[$rz_index[$item["IBLOCK_SECTION_ID"]]]["NAME"].'</p></td>'.
			'</tr>'.
			'<tr>'.
				'<td>'.$item["NAME"].'</td>'.
			'</tr>'.
			'<tr>'.
				'<td nowrap>Стороны: <span class=\"Header\">А1</span> <span><a href=\"B1\">Б1</a></span></td>'.
			'</tr>'.
			'<tr>'.
				'<td nowrap><p><img src=\"'.$itemP["FOTO1"]["VALUE"].'\" width=\"250\" height=\"155\" border=\"0\" onClick=\'javascript:show_window_photo(\"'.$itemP["FOTO1"]["VALUE"].'\");\' style=\"cursor: pointer;}\"></p></td>'.
			'</tr>'.
			'<tr>'.
				'<td align=\"right\" nowrap><p class=\"Grd\">999А / '.$item["CODE"].'</p></td>'.
			'</tr>'.
			'<tr>'.
				'<td nowrap>Свободен для размещения | <a href=\"\" onClick=\'javascript:return show_window_calendar();\'>Календарь </a></td></tr><tr><td align=\"right\" nowrap><p>&nbsp;</p><p><a href=\"\" onClick=\'javascript:alert(\"Не помещу\");return false;\'>Поместить в Корзину</a></p><p>&nbsp;</p></td>'.
			'</tr>'.
			'</table>';
			//$rz_item[] = '{"LON":'.$koords[1].', "LAT":'.$koords[0].', "STYLE":'.$rz_index[$item["IBLOCK_SECTION_ID"]].', "TEXT":"'.$text.'"}';
			$rz_item[] = '{"LON":'.$koords[1].', "LAT":'.$koords[0].', "STYLE":'.$rz_index[$item["IBLOCK_SECTION_ID"]].', "TEXT":""}';



			//$rz_item_new[] = $item;
			//$rz_item_newP[] = $itemP;			
			$rz_item_content[] = '"'.$text.'"';
		}
	} 
}

// собираем javascript array
for ($i=0;$i<sizeof($rz_item_new);$i++){
	//if ()
}

?>
<table cellspacing="0" cellpadding="0" border="0" align="center" width="100%"> 
	<tbody> 
		<tr>
        		<td height="50" valign="top"><img src="<?=SITE_TEMPLATE_PATH?>/images/HeaderOutDoorMap.png" width="347" height="25" vspace="20"></td>
  			<td height="50" valign="center" align="left"><table width="90%" border="0" cellpadding="0" cellspacing="0"><tr><?=implode("",$rz_html);?></tr></table></td>
		</tr>
	</tbody>
</table>
<?
if ($arParams['BX_EDITOR_RENDER_MODE'] == 'Y'):
?>
<img src="/bitrix/components/bitrix/map.yandex.view/templates/.mir-yandex/images/screenshot.png" border="0" />
<?
else:

	$arTransParams = array(
		'KEY' => $arParams['KEY'],
		'INIT_MAP_TYPE' => $arParams['INIT_MAP_TYPE'],
		'INIT_MAP_LON' => $arResult['POSITION']['yandex_lon'],
		'INIT_MAP_LAT' => $arResult['POSITION']['yandex_lat'],
		'INIT_MAP_SCALE' => $arResult['POSITION']['yandex_scale'],
		'MAP_WIDTH' => $arParams['MAP_WIDTH'],
		'MAP_HEIGHT' => $arParams['MAP_HEIGHT'],
		'CONTROLS' => $arParams['CONTROLS'],
		'OPTIONS' => $arParams['OPTIONS'],
		'MAP_ID' => $arParams['MAP_ID'],
		'ONMAPREADY' => 'BX_SetPlacemarks_'.$arParams['MAP_ID'],
	);

	if ($arParams['DEV_MODE'] == 'Y')
	{
		$arTransParams['DEV_MODE'] = 'Y';
		if ($arParams['WAIT_FOR_EVENT'])
			$arTransParams['WAIT_FOR_EVENT'] = $arParams['WAIT_FOR_EVENT'];
	}
?>
<div class="bx-yandex-view-layout">
	<div class="bx-yandex-view-map">
<?
	$APPLICATION->IncludeComponent('bitrix:map.yandex.system', '.default', $arTransParams, false, array('HIDE_ICONS' => 'Y'));
?>
	</div>
</div>


<link rel="stylesheet" href="/calendar1.css">
<script src="/calendar1.js"></script>


<script type="text/javascript">
function BX_SetPlacemarks_<?echo $arParams['MAP_ID']?>(map)
{
	var arObjects = {PLACEMARKS:[],POLYLINES:[]};
<?
	if (is_array($arResult['POSITION']['PLACEMARKS']) && ($cnt = count($arResult['POSITION']['PLACEMARKS']))):
		for($i = 0; $i < $cnt; $i++):
?>
	arObjects.PLACEMARKS[arObjects.PLACEMARKS.length] = BX_YMapAddPlacemark(map, <?echo CUtil::PhpToJsObject($arResult['POSITION']['PLACEMARKS'][$i])?>);
<?
		endfor;
	endif;
	if (is_array($arResult['POSITION']['POLYLINES']) && ($cnt = count($arResult['POSITION']['POLYLINES']))):
		for($i = 0; $i < $cnt; $i++):
?>
	arObjects.POLYLINES[arObjects.POLYLINES.length] = BX_YMapAddPolyline(map, <?echo CUtil::PhpToJsObject($arResult['POSITION']['POLYLINES'][$i])?>);
<?
		endfor;
	endif;
	
	if ($arParams['ONMAPREADY']):
?>
	if (window.<?echo $arParams['ONMAPREADY']?>)
	{
		window.<?echo $arParams['ONMAPREADY']?>(map, arObjects);
	}
<?
	endif;
?>


        var map_imgs = [<?=implode(",",$icon_files)?>];
        var map_styles = new Array();
        for (i=0; i<map_imgs.length; i++){
	        var s = new YMaps.Style();
		s.iconStyle = new YMaps.IconStyle();
		s.iconStyle.href = map_imgs[i];
		s.iconStyle.offset = new YMaps.Point(-5, -30);
        	map_styles[i] = s;
    	
        }
        
  	var GeoArr = [<?=implode(",",$rz_item)?>];
  	
  	var map_point_content = [<?=implode(",",$rz_item_content)?>];


        //for(i=0;i<GeoArr.length;i++){
	//	GeoArr[i]['STYLE'] = {'style': map_styles[GeoArr[i]['STYLE']]};
        //	arObjects.PLACEMARKS.push( BX_YMapAddPlacemark_new(map, GeoArr[i]));	
        //}
	for(i=0;i<GeoArr.length;i++){
		GeoArr[i]['STYLE'] = {'style': map_styles[GeoArr[i]['STYLE']]};
		GeoArr[i]['TEXT']  = map_point_content[i];
		arObjects.PLACEMARKS.push( BX_YMapAddPlacemark_new(map, GeoArr[i]));	
	}

}


var Dialog_photo = new BX.CDialog({'title': 'Фотография','head': '','content':'','width': '820','height': '630','buttons': [ BX.CDialog.btnClose ]});
var Dialog_calendar = new BX.CDialog({'title': 'Календарь','head': '','content':'','width': '840','height': '480','buttons': [ '<input type="submit" value="В корзину" id="to_basket" disabled>', BX.CDialog.btnClose ]});

function show_window_photo(img){
	Dialog_photo.SetContent('<img src="'+img+'" border="0">');
	Dialog_photo.Show();
	return false;
}
function show_window_calendar(){
	Dialog_calendar.SetContent('<div id="aaa"></div>');
	document.getElementById("aaa").innerHTML = create_calendar();
	Dialog_calendar.Show();
	return false;
}



/*
BX.ready(function(){
(new BX.CDialog({
'title': 'Заголовок','head': 'Текст',
'content':'123',
   'resizable': true,   'draggable': true,   'height': '168',   'width': '400', 
'buttons': [ BX.CDialog.btnClose]})).Show();
});
*/

//BX.ready();


</script>



<?
endif;
?>
