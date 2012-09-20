<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


if (!CUtil::InitJSCore(array('window'))) die();
if (!CModule::IncludeModule("catalog")) die();
if (!CModule::IncludeModule('sale')) die();


include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/map_include_js_create.php");

// Корзина
$dbBasketItems = CSaleBasket::GetList(false,array("FUSER_ID" => CSaleBasket::GetBasketUserID(),"ORDER_ID" => "NULL" ,"CAN_BUY"  => "Y"),false,false,array("ID", "CALLBACK_FUNC","PRODUCT_ID"));
while ($arItems = $dbBasketItems->Fetch()){
	if (strlen($arItems["CALLBACK_FUNC"]) > 0) $rz_basket[] = '"'.$arItems["PRODUCT_ID"].'":"1"';
}


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


<link rel="stylesheet" href="<?=$templateFolder?>/calendar1.css">
<style type="text/css">
.opacity1 { opacity: .2; -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=20)"; filter: alpha(opacity=20); }
.opacity2 { opacity: 1.0; /*-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=100)"; filter: alpha(opacity=100);*/ }

</style>
<script src="<?=$templateFolder?>/calendar1.js"></script>
<script src="<?=$templateFolder.'/map_include.js'?>"></script>

<script type="text/javascript">
var map_main;
var arObjects = {PLACEMARKS:[],POLYLINES:[]};
var arObjects_skip;

var map_point_basket = {<?=implode(",",$rz_basket);?>};
var map_showhide	= new Array(); 
var map_styles		= new Array(); 
var today11,today22;


document.getElementById("outdoor_header1").innerHTML = '<table width="100%" border="0" cellpadding="3" cellspacing="0"><tr>'+map_header+'</tr></table>';
document.getElementById("outdoor_header2").innerHTML = '<table width="100%" border="0" cellpadding="3" cellspacing="0"><tr><td width=70 nowrap>Мой заказ<br><a href="#" onClick="javascript:return show_hide_overlay(-1);"><img src="/bitrix/templates/mir/images/OrderItemsShow.png" width="47" height="41" border="0" title="Мой заказ" id="imgc_-1" class="opacity1"></a></td></tr></table>';

/*
document.getElementById("outdoor_header").innerHTML = ''+
'<table width="90%" border="0" cellpadding="3" cellspacing="0">'+
'<tr>'+map_header+'<td width=70>Мой заказ<br><a href="#" onClick="javascript:return show_hide_overlay(-1);"><img src="/bitrix/templates/mir/images/OrderItemsShow.png" width="47" height="41" border="0" title="Мой заказ" id="imgc_-1" class="opacity1"></a></td></tr>'+
'</table>'+
'<form name="form1" action="" method="POST">'+
'<table border="0" cellpadding="3" cellspacing="0" style="padding-bottom:5px;"><tr>'+
'<td><input type=checkbox name="check_status" id="check_status" onClick="javascript:reshow_points();" value="1">&nbsp;Показать свободные</td>'+
'<td nowrap=\"nowrap\">c даты <input name=\"datefield\" id=\"datefield\" value=\"28.06.2011\" size=\"10\" type=\"text\" readonly></td>'+
'<td nowrap=\"nowrap\">на <input name=\"textfield\" id=\"textfield\" value=\"30\" size=\"2\" type=\"text\"> дней</td>'+
'<td nowrap=\"nowrap\">Показать </td>'+
'</tr>'+
'</table>'+
'</form>';
*/

map_showhide[-1] = 0;
for (var i=0; i < map_showhide_len; i++){
	map_showhide[i] = 1;
}


for (var i=0; i<map_imgs.length; i++){
	var s = new YMaps.Style();
	s.iconStyle = new YMaps.IconStyle();
	s.iconStyle.href = map_imgs[i];
	s.iconStyle.offset = new YMaps.Point(-3, -20);
	map_styles[i] = s;
}


function show_hide_point(i,show){
	if (arObjects.PLACEMARKS[i].metaDataProperty.show!=show){
		(show==0)? map_main.removeOverlay(arObjects.PLACEMARKS[i]) : map_main.addOverlay(arObjects.PLACEMARKS[i]);
		arObjects.PLACEMARKS[i].metaDataProperty.show = show;
	}
}

function parse_arrays_free(arr){
	for (var i=0;i<arr.length;i++){
		if ( ! ((today11>=arr[i][0] && today11>=arr[i][1] && today22>=arr[i][0] && today22>=arr[i][1]) || (today11<=arr[i][0] && today11<=arr[i][1] && today22<=arr[i][0] && today22<=arr[i][1])) ){
			return false;
		}
	}
	return true;
}


function reshow_points(){
	today11 = (Math.floor(new Date(document.getElementById('datefield1').value.replace(/(\d+)\.(\d+)\.(\d+).*/, '$2/$1/$3')))+21600000)/1000;
	today22 = today11 + Math.floor(document.getElementById('datefield2').value-1)*24*60*60;
	var zz = (document.getElementById('check_status').checked==true)?1:0;

	if (zz==1){
		for (var i=0; i<map_point_data.length; i++){
			map_point_data[i]['FREE'] = false;
		}
		for (var i=0; i<map_point_all.length; i++){
			if (map_point_data[ map_point_all[i]['PARENT'] ]['FREE']==false){
				var flag = parse_arrays_free( map_point_all[i]['PURCHASED'] );
				if (flag==true)
					flag = parse_arrays_free( map_point_all[i]['RESERVED'] );
				map_point_data[ map_point_all[i]['PARENT'] ]['FREE'] = flag;	
			}
		}
	}

	for (var i=arObjects_skip; i<arObjects.PLACEMARKS.length; i++){
		var icon	= map_showhide[ arObjects.PLACEMARKS[i].metaDataProperty.cat ];
		var basket	= (map_showhide[-1]==1 && map_point_data[i-arObjects_skip]['BASKET']>0);
		
		/*
		var flag	= (map_point_data[i-arObjects_skip]['STATUS']==1 && zz==1);
		if ( basket || ( icon==1 && !flag ) ){
			show_hide_point(i,1);
		}else if ( !basket && ( icon==0 || flag ) ){
			show_hide_point(i,0);
		}
		*/

		var flag	= (map_point_data[i-arObjects_skip]['FREE']==false && zz==1);

		if ( basket || ( icon==1 && !flag ) ){
			show_hide_point(i,1);
		}else if ( !basket && ( icon==0 || flag ) ){
			show_hide_point(i,0);
		}
	}
}


function show_hide_overlay(type){
	map_showhide[type] = (map_showhide[type]==0)?1:0;
	if (type==-1){
		if (map_showhide[type]==0){
			document.getElementById('imgc_'+type).className = 'opacity1';
			for(var i=0;i<map_showhide_len;i++){
				document.getElementById('imgc_'+i).className = 'opacity2';
				map_showhide[i]	= 1;
			}
			reshow_points();
		}else if (map_showhide[type]==1){
			document.getElementById('imgc_'+type).className = 'opacity2';
			for(var i=0;i<map_showhide_len;i++){
				document.getElementById('imgc_'+i).className = 'opacity1';
				map_showhide[i]	= 0;
			}
			reshow_points();
		}
	}else{
		if (map_showhide[type]==0){
			document.getElementById('imgc_'+type).className = "opacity1";
			reshow_points();
		}else if (map_showhide[type]==1){
			document.getElementById('imgc_'+type).className = "opacity2";
			reshow_points();
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
	
	for(var i=0;i<map_point_all.length;i++){ // родителя в корзину
		if (map_point_basket[ map_point_all[i]['ID'] ]==1){
			map_point_all[i]['BASKET'] = 1;
			map_point_data[ map_point_all[i]['PARENT'] ]['BASKET'] += 1; 
		}
		if (map_point_all[i]['STATUS']==0){
			map_point_data[ map_point_all[i]['PARENT'] ]['STATUS'] = 0;
		}
	}
	

	for(var i=0;i<map_point_data.length;i++){
		var obPlacemark = new map.bx_context.YMaps.Placemark(new map.bx_context.YMaps.GeoPoint(map_point_data[i].LON, map_point_data[i].LAT), {'style': map_styles[map_point_data[i]['CAT2']]});
		obPlacemark.metaDataProperty.id = map_point_data[i]['ID'];
		obPlacemark.metaDataProperty.cat = map_point_data[i]['CAT'];
		obPlacemark.metaDataProperty.show = 1;
		
		map.addOverlay(obPlacemark);
		arObjects.PLACEMARKS.push( obPlacemark );
		map.bx_context.YMaps.Events.observe(obPlacemark, obPlacemark.Events.BalloonOpen,function (obj){	var z = obj.metaDataProperty.id; obj.setBalloonContent(map_point_all[z]['TEXT']+((map_point_all[z]['BASKET']>0)?map_point_all[z]['TEXT_FOOT2']:map_point_all[z]['TEXT_FOOT1'])); });
	}
<?
  	if ($_REQUEST[ID]>0):
?>  		
	for(var i=0;i<map_point_all.length;i++){
		if (map_point_all[i]['ID']==<?=$_REQUEST[ID]?>){
			if (i!=map_point_data[ map_point_all[i]['PARENT'] ]['ID']) swapb(map_point_all[i]['PARENT'], i);
			arObjects.PLACEMARKS[ arObjects_skip+map_point_all[i]['PARENT'] ].openBalloon();
			break;
		}
	}
<?  	
	endif;
?>
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
 			var z = map_point_all[ajax_id]['PARENT'];
 			map_point_data[z]['BASKET'] += 1;
 			arObjects.PLACEMARKS[arObjects_skip+z].setBalloonContent(map_point_all[ajax_id]['TEXT']+map_point_all[ajax_id]['TEXT_FOOT2']);

 			var bnum = parseInt(document.getElementById('basket_num').innerHTML.toString().replace(/\(|\)/,''));
 			if (!bnum) bnum = 0;
 			document.getElementById('basket_num').innerHTML = '('+(bnum+1)+')';

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
 			var z = map_point_all[ajax_id]['PARENT'];
 			arObjects.PLACEMARKS[arObjects_skip+z].setBalloonContent(map_point_all[ajax_id]['TEXT']+map_point_all[ajax_id]['TEXT_FOOT1']);
 			map_point_data[z]['BASKET'] -= 1;
 			if (map_point_data[z]['BASKET']<0){
 				map_point_data[z]['BASKET']=0; // на всякий случай
 				alert('Произошла ошибка! Обновите страницу.');
 			}

 			var bnum = parseInt(document.getElementById('basket_num').innerHTML.toString().replace(/\(|\)/,''));
 			document.getElementById('basket_num').innerHTML = (bnum>1) ? '('+(bnum-1)+')' : '(0)';

 			if (map_showhide[-1]==1 && map_point_data[z]['BASKET']==0) show_hide_point(z+arObjects_skip,0);
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
		//arObjects.PLACEMARKS[arObjects_skip+map_point_all[ajax_id]['PARENT']].setBalloonContent(map_point_all[ajax_id]['TEXT']+'<tr><td nowrap>&nbsp;</td></tr><tr><td align=\"right\" nowrap><p>&nbsp;</p><p>Удаление из корзины...</p><p>&nbsp;</p></td></tr></table>');
		arObjects.PLACEMARKS[arObjects_skip+map_point_all[ajax_id]['PARENT']].setBalloonContent(map_point_all[ajax_id]['TEXT']+'<td align="left" nowrap>&nbsp;</td><td align="right" nowrap>Удаление из корзины...</td></tr></table>');
		requestURI("/catalog/remove_basket.php?ID="+map_point_all[ajax_id]['ID'],2);
	}
	return false;
}


</script>



<?
endif;
?>
