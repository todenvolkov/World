<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CUtil::InitJSCore(array('window'));

if (!CModule::IncludeModule("catalog")) die();
if (!CModule::IncludeModule('sale')) die();

// Корзина
$dbBasketItems = CSaleBasket::GetList(false,array("FUSER_ID" => CSaleBasket::GetBasketUserID(),"ORDER_ID" => "NULL" ,"CAN_BUY"  => "Y"),false,false,array("ID", "CALLBACK_FUNC","PRODUCT_ID"));
while ($arItems = $dbBasketItems->Fetch()){
	if (strlen($arItems["CALLBACK_FUNC"]) > 0) $in_basket[ $arItems["PRODUCT_ID"] ] = 1;
}


// Статусы.csv
$datas0 = time();
$f = fopen("/home/temp/public_html/csv/status.csv", "r") or die("Ошибка!");

$contents = explode("\n",fread($f,filesize("/home/temp/public_html/csv/status.csv")));
for ($i=1; $i<sizeof($contents); $i++){  
	$data=explode(';', ereg_replace('"','',rtrim($contents[$i])));

	if (sizeof($data)>3){
		$datas1 = explode(".",$data[2]); $datas1 = mktime(0,0,0,$datas1[1],$datas1[0],$datas1[2]);
		$datas2 = explode(".",$data[3]); $datas2 = mktime(0,0,0,$datas2[1],$datas2[0],$datas2[2]);
		
		$tmp_reserved[$data[1]][$data[0]][] = "['".$data[2]."','".$data[3]."']";
		if ($datas1 < $datas0 && $datas0 < $datas2) $tmp_status[$data[0]] = $data[1];
	}
}
fclose($f);


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
			$split_name = explode("сторона ",$item["NAME"]);
			$rz_items[$split_name[0]][] = array(
				"ID"		=> $item["ID"],
				"NAME"		=> $item["NAME"],
				"SIDE"		=> $split_name[1],
				"CODE"		=> $item["CODE"],
				"LON"		=> $koords[1],
	 			"LAT"		=> $koords[0],
	 			//"FOTO"		=> ereg_replace("'","",$itemP["FOTO1"]["VALUE"]),
	 			"FOTO1"		=> CFile::GetPath($item["PREVIEW_PICTURE"]),
	 			"FOTO2"		=> CFile::GetPath($item["DETAIL_PICTURE"]),
	 			"ART"		=> $itemP["ART1"]["VALUE"],
	 			"CAT"		=> $rz_index[ $rz_index2_parent[$item["IBLOCK_SECTION_ID"]] ],
	 			"CAT2"		=> $rz_index2[$item["IBLOCK_SECTION_ID"]],
	 			"PRICE"		=> $tmp_price[$item["ID"]]
			);
		}
	} 
}


// собираем javascript array
foreach ($rz_items as $i => $value) {

	$item_len = sizeof($rz_item);
	$item_all_len = sizeof($rz_item_all);
	$sides = array();
	$status_html = array(
		''=>'<span style=\"color:#65781F\"><b>Свободен</b></span>',
		'1'=>'<span style=\"color:#C70000\">Занят</span>',
		'2'=>'<span style=\"color:#D5CD00\">Забронирован</span>'
	);


	//сначала узнаем стороны и корзину
	$first_basket = $value[0]["CAT"];
	for ($i=0;$i<sizeof($value);$i++){
		$sides[]='<a href=\"\" onClick=\"javascript:return swapb('.($item_len).','.($item_all_len+$i).');\"><span>'.$value[$i]["SIDE"].'</span></a>';
		if ($in_basket[ $value[$i]["ID"] ]==1) $first_basket = -1;
	}


	$rz_item[]='{"LON":"'.$value[0]["LON"].'", "LAT":"'.$value[0]["LAT"].'", "CAT":"'.$first_basket.'", "CAT_ORIG":"'.$value[0]["CAT"].'", "CAT2":"'.$value[0]["CAT2"].'", "FIRST":"'.$item_all_len.'"}';
	
	
	//создаем массив контента
	for ($i=0;$i<sizeof($value);$i++){
		$tmp_links = '';
		if (sizeof($sides)>1){
			$tmp_sides = $sides;
			$tmp_sides[$i]='<span class=\"Header\">'.$value[$i]["SIDE"].'</span>';
			$tmp_links='<tr><td nowrap>Стороны: '.implode("&nbsp;",$tmp_sides).'</td></tr>';
		}
		
		$rz_item_all[] = '{'.
		'"ID":"'.$value[$i]["ID"].'",'.
		'"PARENT":'.$item_len.','.
		'"PRICE":"'.$value[$i]["PRICE"].'",'.
		'"STATUS":"'.$tmp_status[$value[$i]["CODE"]].'",'.
		'"PURCHASED":['.implode(",",$tmp_reserved[1][$value[$i]["CODE"]]).'],'.
		'"RESERVED":['.implode(",",$tmp_reserved[2][$value[$i]["CODE"]]).'],'.
		'"BASKET":"'.$in_basket[ $value[$i]["ID"] ].'",'.
		'"TEXT":"'.
			'<table width=\"206\" border=\"0\" cellpadding=\"0\" cellspacing=\"2\" bgcolor=\"#FFFFFF\">'.
			'<tr><td nowrap><p class=\"Header\">'.$rz_cat2[$value[$i]["CAT2"]].'</p></td></tr>'.
			'<tr><td>'.$value[$i]["NAME"].'</td></tr>'.
			''.$tmp_links.
			'<tr><td nowrap><p><img src=\"'.$value[$i]["FOTO1"].'\" width=\"206\" height=\"155\" border=\"0\" onClick=\'javascript:show_window_photo(\"'.$value[$i]["FOTO2"].'\");\' style=\"cursor: pointer;}\"></p></td></tr>'.
			'<tr><td align=\"right\" nowrap><p class=\"Grd\">'.$value[$i]["ART"].' / '.$value[$i]["CODE"].'</p></td></tr>",'.
		'"TEXT_FOOT1":"<tr><td nowrap><a href=\"\" onClick=\'javascript:return show_window_calendar('.($item_all_len+$i).');\'>'.$status_html[$tmp_status[$value[$i]["CODE"]]].'</a> | <a href=\"\" onClick=\'javascript:return show_window_calendar('.($item_all_len+$i).');\'>Календарь </a></td></tr><tr><td align=\"right\" nowrap><p>&nbsp;</p><p><a href=\"\" onClick=\'javascript:return show_window_calendar('.($item_all_len+$i).');\'>Поместить в Корзину</a></p><p>&nbsp;</p></td></tr></table>",'.
		'"TEXT_FOOT2":"<tr><td nowrap>&nbsp;</td></tr><tr><td align=\"right\" nowrap><p>&nbsp;</p><p><a href=\"\" onClick=\'javascript:return remove2basket('.($item_all_len+$i).');\'>Удалить из корзины</a></p><p>&nbsp;</p></td></tr></table>"'.
		'}';

	}
}
?>
<table cellspacing="0" cellpadding="0" border="0" align="center" width="100%"> 
	<tbody> 
		<tr>
        		<td height="50" valign="top"><img src="<?=SITE_TEMPLATE_PATH?>/images/HeaderOutDoorMap.png" width="347" height="25" vspace="20"></td>
  			<td height="50" valign="center" align="left"><table width="90%" border="0" cellpadding="0" cellspacing="0"><tr><?=implode("",$rz_html);?><td width=70>Мой заказ<br><a href="#" onClick="javascript:return show_hide_overlay(-1);"><img src="/bitrix/templates/mir/images/OrderItemsShow.png" width="47" height="41" border="0" title="Мой заказ" id="imgc_-1"></a></td></tr></table></td>
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
var map_main;
var arObjects = {PLACEMARKS:[],POLYLINES:[]};
var arObjects_skip;

var map_point_data	= [<?=implode(",",$rz_item)?>];
var map_point_all	= [<?=implode(",",$rz_item_all)?>];

var map_imgs		= [<?=implode(",",$icon2_files)?>];
var map_styles		= new Array();
var map_showhide	= new Array();


for (i=0; i<map_imgs.length; i++){
	var s = new YMaps.Style();
	s.iconStyle = new YMaps.IconStyle();
	s.iconStyle.href = map_imgs[i];
	s.iconStyle.offset = new YMaps.Point(-5, -30);
	map_styles[i] = s;
	map_showhide[i] = 1;
}

function show_hide_overlay(type){
	map_showhide[type] = (map_showhide[type]==0)?1:0;
		if (map_showhide[type]==0){
		document.getElementById('imgc_'+type).style.opacity = 0.2;
		for(var i=0;i<map_point_data.length;i++){
			if (map_point_data[i]['CAT']==type) map_main.removeOverlay(arObjects.PLACEMARKS[arObjects_skip+i]);
		}
	}else if (map_showhide[type]==1){
		document.getElementById('imgc_'+type).style.opacity = 1;
		for(var i=0;i<map_point_data.length;i++){
			if (map_point_data[i]['CAT']==type) map_main.addOverlay(arObjects.PLACEMARKS[arObjects_skip+i]);
		}		
	}
	return false;
}

function BX_SetPlacemarks_<?echo $arParams['MAP_ID']?>(map)
{
	arObjects = {PLACEMARKS:[],POLYLINES:[]};
	map_main = map;
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
	
	arObjects_skip = arObjects.PLACEMARKS.length;
	for(var i=0;i<map_point_data.length;i++){
		var obPlacemark = new map.bx_context.YMaps.Placemark(new map.bx_context.YMaps.GeoPoint(map_point_data[i].LON, map_point_data[i].LAT), {'style': map_styles[map_point_data[i]['CAT2']]});
		var z = map_point_data[i]['FIRST'];
		obPlacemark.setBalloonContent(map_point_all[z]['TEXT']+((map_point_all[z]['BASKET']>0)?map_point_all[z]['TEXT_FOOT2']:map_point_all[z]['TEXT_FOOT1']));
		map.addOverlay(obPlacemark);
		arObjects.PLACEMARKS.push( obPlacemark );
	}

<?
  	if ($_REQUEST[ID]>0):
?>  		
	for(var i=0;i<map_point_all.length;i++){
		if (map_point_all[i]['ID']==<?=$_REQUEST[ID]?>){
			if (i!=map_point_data[ map_point_all[i]['PARENT'] ]['FIRST']) swapb(map_point_all[i]['PARENT'], i);
			arObjects.PLACEMARKS[ arObjects_skip+map_point_all[i]['PARENT'] ].openBalloon();
			break;
		}
	}
<?  	
	endif;
?>
	//show_hide_overlay(-1);
}


function swapb(num1,num2){
	arObjects.PLACEMARKS[arObjects_skip+num1].setBalloonContent(map_point_all[num2]['TEXT']+((map_point_all[num2]['BASKET']>0)?map_point_all[num2]['TEXT_FOOT2']:map_point_all[num2]['TEXT_FOOT1']));
	return false;
}

var Dialog_photo = new BX.CDialog({'title': 'Фотография','head': '','content':'','width': '820','height': '630','buttons': [ BX.CDialog.btnClose ]});
var Dialog_calendar = new BX.CDialog({'title': 'Календарь','head': '','content':'','width': '840','height': '480','buttons': [ '<input type="submit" value="В корзину" id="to_basket" disabled onClick="javascript:return add2basket();">', BX.CDialog.btnClose ]});

var ajax_id = 0;

function show_window_photo(img){
	Dialog_photo.SetContent('<img src="'+img+'" border="0">');
	Dialog_photo.Show();
	return false;
}
function show_window_calendar(i){
	ajax_id = i;
	Dialog_calendar.SetContent('<div id="aaa"></div>');
	document.getElementById("aaa").innerHTML = create_calendar(map_point_all[i]['ID'],  map_point_all[i]['PRICE'], map_point_all[i]['PURCHASED'],  map_point_all[i]['RESERVED']);
	Dialog_calendar.Show();
	return false;
}


function requestURI(url,type) {
	if (window.XMLHttpRequest) {
		c_request = new XMLHttpRequest();
		if (type==1){
			c_request.onreadystatechange = processRequestChange1;
		}else{
			c_request.onreadystatechange = processRequestChange2;
		}
		c_request.open("GET", url, true);
		c_request.send(null);
	} else if (window.ActiveXObject) {
		c_request = new ActiveXObject("Microsoft.XMLHTTP");
		if (c_request) {
			if (type==1){
				c_request.onreadystatechange = processRequestChange1;
			}else{
				c_request.onreadystatechange = processRequestChange2;
			}
			c_request.open("GET", url, true);
			c_request.send();
		}
	}
}

function processRequestChange1() {
 	if (c_request.readyState == 4) {
 		Dialog_calendar.Close();
 		if (c_request.status == 200 && c_request.responseText!='false') {
 			map_point_all[ajax_id]['BASKET'] = 1;
 			arObjects.PLACEMARKS[arObjects_skip+map_point_all[ajax_id]['PARENT']].setBalloonContent(map_point_all[ajax_id]['TEXT']+map_point_all[ajax_id]['TEXT_FOOT2']);
 			
 			var z = map_point_all[ajax_id]['PARENT'];

 			map_point_data[z]['CAT'] = -1;
 			if (map_showhide[ map_point_data[z]['CAT'] ]==0) map_main.removeOverlay(arObjects.PLACEMARKS[arObjects_skip+z]);
 			//alert("Успешно добавлено в корзину...");
		} else {
			alert("Не удалось открыть корзину!");
 		}
 	}
}

function processRequestChange2() {
 	if (c_request.readyState == 4) {
 		Dialog_calendar.Close();
 		if (c_request.status == 200 && c_request.responseText!='false') {
 			map_point_all[ajax_id]['BASKET'] = 0;
 			arObjects.PLACEMARKS[arObjects_skip+map_point_all[ajax_id]['PARENT']].setBalloonContent(map_point_all[ajax_id]['TEXT']+map_point_all[ajax_id]['TEXT_FOOT1']);
 			
 			var z = map_point_all[ajax_id]['PARENT'];
 			map_point_data[z]['CAT'] = map_point_data[z]['CAT_ORIG'];
 			for(var i=0;i<map_point_all.length;i++){
 				if (map_point_all[i]['PARENT']==z && map_point_all[i]['BASKET']==1){
 					map_point_data[z]['CAT'] = -1;
 					break;
 				}
 			}
 			if (map_showhide[ map_point_data[z]['CAT'] ]==0) map_main.removeOverlay(arObjects.PLACEMARKS[arObjects_skip+z]);
			//alert("Успешно удалено из корзины...");
		} else {
			alert("Не удалось открыть корзину!");
 		}
 	}
}

function add2basket(){
	document.getElementById('to_basket').disabled = true;
	Dialog_calendar.SetContent('<br><br><br><br><br><center><b>Добавление в корзину...</b></center>');
	requestURI("/catalog/add_basket.php?ID="+c_request_id+"&PARAM="+c_request_array.join(','),1);
	return false;
}

function remove2basket(id_product){
	ajax_id = id_product;
	if (map_point_all[ajax_id]['BASKET']==1){
		arObjects.PLACEMARKS[arObjects_skip+map_point_all[ajax_id]['PARENT']].setBalloonContent(map_point_all[ajax_id]['TEXT']+'<tr><td nowrap>&nbsp;</td></tr><tr><td align=\"right\" nowrap><p>&nbsp;</p><p>Удаление из корзины...</p><p>&nbsp;</p></td></tr></table>');
		requestURI("/catalog/remove_basket.php?ID="+map_point_all[ajax_id]['ID'],2);
	}
	return false;
}


</script>



<?
endif;
?>
