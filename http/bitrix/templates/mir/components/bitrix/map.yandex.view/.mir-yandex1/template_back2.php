<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CUtil::InitJSCore(array('window'));


// Статусы.csv
$datas0 = time();
$f = fopen("/home/temp/public_html/Статусы.csv", "r") or die("Ошибка!");
$contents = explode("\n",fread($f,filesize("/home/temp/public_html/Статусы.csv")));
for ($i=1; $i<sizeof($contents); $i++){  
	$data=explode(';', rtrim($contents[$i]));
	if (sizeof($data)>3){
		$datas1 = preg_split("/\s|\./",$data[4]); $datas1 = mktime(0,0,0,$datas1[1],$datas1[0],$datas1[2]);
		$datas2 = preg_split("/\s|\./",$data[3]); $datas2 = mktime(0,0,0,$datas2[1],$datas2[0],$datas2[2]);
		if ($data[2]=='Активна'){
			$tmp_purchased[$data[1]][]="['".$data[4]."','".$data[3]."']";
			if ($datas1 < $datas0 && $datas0 < $datas2) $tmp_status[$data[1]] = 1;
		}else{
			$tmp_reserved[$data[1]][]="['".$data[4]."','".$data[3]."']";
			if ($datas1 < $datas0 && $datas0 < $datas2) $tmp_status[$data[1]] = 2;
		}
	}
}
fclose($f);


if (!CModule::IncludeModule("catalog")) die();

$tmp1 = CCatalog::GetList(array(),array("SUBSCRIPTION" => "Y"));
while ($catalog = $tmp1->Fetch()){
	$tmp2 = GetIBlockSectionList($catalog["ID"]);
	while($section = $tmp2->GetNext()) {
		$rz_len = sizeof($rz_cat);
		$rz_cat[] = $section["NAME"];
		$rz_index[$section["ID"]] = $rz_len;
		$icon_files[]="'".CFile::GetPath($section["PICTURE"])."'";
		$rz_html[] = "<td><a href=\"#\" onClick=\"javascript:return show_hide_overlay(".$rz_len.");\"><img src=\"".CFile::GetPath($section["PICTURE"])."\" border=0 title=\"".$section["NAME"]."\" id=\"imgc_".$rz_len."\"></a></td>";
	}

	$tmp3 = CIBlockElement::GetList(array("NAME" => "ASC"),array("IBLOCK_ID" =>$catalog["ID"], "INCLUDE_SUBSECTIONS" => "Y", "ACTIVE"=>"Y"));
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
	 			"FOTO"		=> $itemP["FOTO1"]["VALUE"],
	 			"ART"		=> $itemP["ART1"]["VALUE"],
	 			"CAT"		=> $rz_index[$item["IBLOCK_SECTION_ID"]]
			);
		}
	} 
}


// собираем javascript array
foreach ($rz_items as $i => $value) {
	$conent_len = sizeof($rz_item_content);
	$sides = array();
	$status_html = array(
		''=>'<span style=\"color:#4CFF39\">Свободен для размещения</span>',
		'1'=>'<span style=\"color:#C80202\">Занят</span>',
		'2'=>'<span style=\"color:#D5CD00\">Забронирован</span>'
	);

	$rz_item[]='{"LON":"'.$value[0]["LON"].'", "LAT":"'.$value[0]["LAT"].'", "CAT":"'.$value[0]["CAT"].'", "FIRST":"'.$conent_len.'"}';
	$item_len = sizeof($rz_item);
	
	//сначала узнаем стороны
	for ($i=0;$i<sizeof($value);$i++){
		$sides[]='<a href=\"\" onClick=\"javascript:return swapb('.$item_len.','.($conent_len+$i).');\"><span>'.$value[$i]["SIDE"].'</span></a>';
	}
	
	//создаем массив контента
	for ($i=0;$i<sizeof($value);$i++){
		$tmp_links = '';
		if (sizeof($sides)>1){
			$tmp_sides = $sides;
			$tmp_sides[$i]='<span class=\"Header\">'.$value[$i]["SIDE"].'</span>';
			$tmp_links='<tr><td nowrap>Стороны: '.implode("&nbsp;",$tmp_sides).'</td></tr>';
		}
		

		$rz_item_content[] = '"'.
			'<table width=\"250\" border=\"0\" cellpadding=\"0\" cellspacing=\"2\" bgcolor=\"#FFFFFF\">'.
			'<tr><td nowrap><p class=\"Header\">'.$rz_cat[$value[$i]["CAT"]].'</p></td></tr>'.
			'<tr><td>'.$value[$i]["NAME"].'</td></tr>'.
			''.$tmp_links.
			'<tr><td nowrap><p><img src=\"'.$value[$i]["FOTO"].'\" width=\"250\" height=\"155\" border=\"0\" onClick=\'javascript:show_window_photo(\"'.$value[$i]["FOTO"].'\");\' style=\"cursor: pointer;}\"></p></td></tr>'.
			'<tr><td align=\"right\" nowrap><p class=\"Grd\">999А / '.$value[$i]["CODE"].'</p></td></tr>'.
			'<tr><td nowrap>'.$status_html[$tmp_status[$value[$i]["CODE"]]].' | <a href=\"\" onClick=\'javascript:return show_window_calendar('.($conent_len+$i).');\'>Календарь </a></td></tr><tr><td align=\"right\" nowrap><p>&nbsp;</p><p><a href=\"\" onClick=\'javascript:return show_window_calendar('.($conent_len+$i).');\'>Поместить в Корзину</a></p><p>&nbsp;</p></td></tr>'.
			'</table>'.
			'"';

		$rz_item_purchased[] = '['.implode(",",$tmp_purchased[$value[$i]["CODE"]]).']';
		$rz_item_reserved[] = '['.implode(",",$tmp_reserved[$value[$i]["CODE"]]).']';
		$rz_item_id[] = $value[$i]["ID"];
		$rz_item_status[] = $tmp_status[$value[$i]["CODE"]];

	}

	
	
	
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
var map_main;
var arObjects = {PLACEMARKS:[],POLYLINES:[]};
var arObjects_skip;

var map_point_data	= [<?=implode(",",$rz_item)?>];

var map_point_content	= [<?=implode(",",$rz_item_content)?>];
var map_point_purchased	= [<?=implode(",",$rz_item_purchased)?>]
var map_point_reserved	= [<?=implode(",",$rz_item_reserved)?>]
var map_point_status	= [<?=implode(",",$rz_item_status)?>]
var map_point_id	= [<?=implode(",",$rz_item_id)?>]

var map_imgs		= [<?=implode(",",$icon_files)?>];
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
		var obPlacemark = new map.bx_context.YMaps.Placemark(new map.bx_context.YMaps.GeoPoint(map_point_data[i].LON, map_point_data[i].LAT), {'style': map_styles[map_point_data[i]['CAT']]});
		obPlacemark.setBalloonContent(map_point_content[map_point_data[i]['FIRST']]);
		map.addOverlay(obPlacemark);
		arObjects.PLACEMARKS.push( obPlacemark );
	}
	
}


function swapb(num1,num2){
	arObjects.PLACEMARKS[num1].setBalloonContent(map_point_content[num2]);
	return false;
}

var Dialog_photo = new BX.CDialog({'title': 'Фотография','head': '','content':'','width': '820','height': '630','buttons': [ BX.CDialog.btnClose ]});
var Dialog_calendar = new BX.CDialog({'title': 'Календарь','head': '','content':'','width': '840','height': '480','buttons': [ '<input type="submit" value="В корзину" id="to_basket" disabled>', BX.CDialog.btnClose ]});

function show_window_photo(img){
	Dialog_photo.SetContent('<img src="'+img+'" border="0">');
	Dialog_photo.Show();
	return false;
}
function show_window_calendar(i){
	Dialog_calendar.SetContent('<div id="aaa"></div>');
	document.getElementById("aaa").innerHTML = create_calendar( map_point_purchased[i],  map_point_reserved[i]);
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
