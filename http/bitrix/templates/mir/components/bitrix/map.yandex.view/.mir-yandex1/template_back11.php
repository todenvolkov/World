<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


if (!CUtil::InitJSCore(array('window'))) die();
if (!CModule::IncludeModule("catalog")) die();
if (!CModule::IncludeModule('sale')) die();


if ($_POST['admin_submit']!='' && $USER->isAdmin()){
	$pdata = preg_split("/\;|\:/",$_POST['admin_data']);
	for ($i=0;$i<sizeof($pdata);$i+=3) {
		if ($pdata[$i]!=""){
			CIBlockElement::SetPropertyValues($pdata[$i], $pdata[$i+1], $pdata[$i+2], "KOORD1");
		}
	}

	$force_reload_map = 1;
	include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/map_include_js_create.php");
	LocalRedirect('/catalog/');
}

include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/map_include_js_create.php");


// Корзина
$dbBasketItems = CSaleBasket::GetList(false,array("FUSER_ID" => CSaleBasket::GetBasketUserID(),"ORDER_ID" => "NULL" ,"CAN_BUY"  => "Y"),false,false,array("ID", "CALLBACK_FUNC","PRODUCT_ID"));
while ($arItems = $dbBasketItems->Fetch()){
	if (strlen($arItems["CALLBACK_FUNC"]) > 0) $rz_basket[] = '"'.$arItems["PRODUCT_ID"].'":"1"';
}
//echo "<pre>";
//print_r($arParams['OPTIONS']);
//echo "</pre>";


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
		'YANDEX_VERSION' => '1.1'
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



<?
  	if ($_REQUEST[B]):
?>
	document.getElementById('imgc_-1').className = 'opacity2';
	map_showhide[-1] = 1;
	for (var i=0; i < map_showhide_len; i++){
	        document.getElementById('imgc_'+i).className = 'opacity1';
		map_showhide[i] = 0;
	}
<?
	else:
?>
	map_showhide[-1] = 0;
	for (var i=0; i < map_showhide_len; i++){
		map_showhide[i] = 1;
	}
<?
	endif;
?>

for (var i=0; i<map_imgs.length; i++){
	var s = new YMaps.Style();
	s.iconStyle = new YMaps.IconStyle();
	s.iconStyle.href = map_imgs[i];
	s.iconStyle.offset = new YMaps.Point(-3, -20);
	s.iconStyle.size = new YMaps.Point(25, 25);;
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

function reshow_points(show_cat_id){
	if (Dialog_screen.visible()==true) return screen_recount();

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

	var show_cat_count = 0;
	var show_cat_point;

	for (var i=arObjects_skip; i<arObjects.PLACEMARKS.length; i++){
		var cat		= arObjects.PLACEMARKS[i].metaDataProperty.cat;
		var icon	= map_showhide[ cat ];
		var basket	= (map_showhide[-1]==1 && map_point_data[i-arObjects_skip]['BASKET']>0);
		var flag	= (map_point_data[i-arObjects_skip]['FREE']==false && zz==1);

		if ( basket || ( icon==1 && !flag ) ){
			show_hide_point(i,1);
			if (cat==show_cat_id){
				show_cat_count++;
				show_cat_point = arObjects.PLACEMARKS[i];
			}
		}else if ( !basket && ( icon==0 || flag ) ){
			show_hide_point(i,0);
		}
	}

	if (show_cat_count<2 && show_cat_point){
		map_main.panTo(show_cat_point.getGeoPoint());
		show_cat_point.openBalloon();
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
			reshow_points(type); //...
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
	}
	


	for(var i=0;i<map_point_data.length;i++){
		var obPlacemark = new map.bx_context.YMaps.Placemark(new map.bx_context.YMaps.GeoPoint(map_point_data[i].LON, map_point_data[i].LAT), {'style': map_styles[map_point_data[i]['CAT2']], 'zIndex': (110+map_point_sides[i].length) }); //,'draggable': true
		obPlacemark.metaDataProperty.id = map_point_data[i]['ID'];
		obPlacemark.metaDataProperty.cat = map_point_data[i]['CAT'];
<?
  	if ($_REQUEST[B]):
?>              
		obPlacemark.metaDataProperty.show = 0;
		if (map_point_data[i]['BASKET']>0){
			map.addOverlay(obPlacemark);
		}
<?  	
	else:
?>
                obPlacemark.metaDataProperty.show = 1;
		map.addOverlay(obPlacemark);
<?  	
	endif;
?>
		arObjects.PLACEMARKS.push( obPlacemark );
		map.bx_context.YMaps.Events.observe(
			obPlacemark, 
			obPlacemark.Events.BalloonOpen,
			function (obj){
				obj.setBalloonContent( fill_balloon( map_point_all[ obj.metaDataProperty.id ]['PARENT'] , obj.metaDataProperty.id ) );
			}
		);
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

var status_html = { '':'<span style=\"color:#65781F\"><b>Свободен</b></span>', '1':'<span style=\"color:#C70000\">Занят</span>', '2':'<span style=\"color:#D5CD00\">Забронирован</span>' };
var selected_point = 0; // map_point_all selected
function fill_balloon(i,j){
	// i : map_point_data
	// j : map_point_all_selected
	selected_point = j;
	var k = map_point_data[i];
	var z = map_point_all[j];
	var c = '';
	var tmp_links = '';

	if (z['FOTO1']=='' || z['FOTO2']==''){
		z['FOTO1'] = "<?=SITE_TEMPLATE_PATH?>/images/7x7_Op.gif";
		z['FOTO2'] = z['FOTO1'];
	}

	if (k['COMBI'].length==0){
		if (map_point_sides[i].length > 1){
			tmp_links = 'Сторона:';
			for (var q=0; q < map_point_sides[i].length; q++){
				var side = map_point_sides[i][q];
				tmp_links+=(side==j)?'&nbsp;<span class="Header">'+map_point_all[ side ]['SIDE']+'</span>':'&nbsp;<a href="" onClick="javascript:return swapb('+i+','+side+');"><span>'+map_point_all[ side ]['SIDE']+'</span></a>';
			}
		}
		c = '<table width="250" border="0" cellspacing="4" cellpadding="4"><tr><td rowspan="7"><img src="\/bitrix\/templates\/mir\/images\/7x7_Op.gif" width="1" height="7"></td><td rowspan="5"><p class="Header"><img src="'+z['FOTO1']+'" width="250" height="187" border="0" onClick=\'javascript:show_window_photo("'+z['FOTO2']+'");\' style="cursor:pointer;"></p></td><td nowrap><span class="Header">'+map_point_cat2[k['CAT2']]+'</span></td><td rowspan="7"><img src="\/bitrix\/templates\/mir\/images\/7x7_Op.gif" width="1" height="7"></td></tr><tr><td>'+z['NAME']+'</td></tr><tr><td nowrap>&nbsp;'+tmp_links+'</td></tr><tr><td>&nbsp;</td></tr><tr><td><p><span class="Grd">'+z['AC']+'</span></p></td></tr><tr><td></td><td></td></tr>';
	}else{
		for (var combi=0; combi < k['COMBI'].length; combi++){
			var ii = k['COMBI'][combi]; // id комби конструкции из map_point_data
			var kk = map_point_data[ii];

			tmp_links += '<span class="Header">'+map_point_cat2[ kk['CAT2'] ]+'</span><br>Сторона:';
			for (var q=0; q < map_point_sides[ii].length; q++){
				var side = map_point_sides[ii][q];
				tmp_links+=(side==j)?'&nbsp;<span class="Header">'+map_point_all[ side ]['SIDE']+'</span>':'&nbsp;<a href="" onClick="javascript:return swapb('+i+','+side+');"><span>'+map_point_all[ side ]['SIDE']+'</span></a>';
			}
			tmp_links += '<br>';
		}
		c = '<table width="250" border="0" cellspacing="4" cellpadding="4"><tr><td rowspan="6"><img src="\/bitrix\/templates\/mir\/images\/7x7_Op.gif" width="1" height="7"></td><td rowspan="4"><p class="Header"><img src="'+z['FOTO1']+'" width="250" height="187" border="0" onClick=\'javascript:show_window_photo("'+z['FOTO2']+'");\' style="cursor:pointer;"></p></td><td>'+z['NAME']+'</td><td rowspan="6"><img src="\/bitrix\/templates\/mir\/images\/7x7_Op.gif" width="1" height="7"></td></tr><tr><td nowrap>'+tmp_links+'</td></tr><tr><td>&nbsp;</td></tr><tr><td><p><span class="Grd">'+z['AC']+'</span></p></td></tr><tr><td></td><td></td></tr>';
	}
	
	if (z['BASKET']>0){
		c+='<tr><td>&nbsp;</td><td align="right" nowrap><a href="" onClick=\'javascript:return remove2basket('+j+');\'>Удалить из корзины</a></td></tr></table>';
	}else if (k['MCAT']==11){ // экраны
		c+='<tr><td>&nbsp;</td><td align="right" nowrap><a href="" onClick=\'javascript:return show_window_screen('+j+');\'>Поместить в Корзину</a></td></tr></table>';
	}else{
		c+='<tr><td><a href="" onClick=\'javascript:return show_window_calendar('+j+');\'>'+status_html[z['STATUS']]+'</a> | <a href="" onClick=\'javascript:return show_window_calendar('+j+');\'>Календарь </a></td><td align="right" nowrap><a href="" onClick=\'javascript:return show_window_calendar('+j+');\'>Поместить в Корзину</a></td></tr></table>';
	}
	<?if ($USER->isAdmin()):?>
	if (document.getElementById('admin_mode').checked==true){
		c+='<center>Координаты: <input type=text name="point_koord_'+i+'" id="point_koord_'+i+'" value="'+''+arObjects.PLACEMARKS[ arObjects_skip+i ].getGeoPoint()+'" size="20"><input type=submit value="применить" onClick="javascript:admin_mode_move('+i+',document.getElementById(\'point_koord_'+i+'\').value);"></center>';
 	}
	<? endif; ?>
	return c;
}


function swapb(num1,num2){
	arObjects.PLACEMARKS[arObjects_skip+num1].setBalloonContent( fill_balloon( num1, num2 ) );
	return false;
}

var Dialog_photo = new BX.CDialog({'title': 'Фотография','head': '','content':'','width': '820','height': '630','buttons': [ BX.CDialog.btnClose ]});
var Dialog_calendar = new BX.CDialog({'title': 'Календарь','head': '','content':'','width': '840','height': '480','buttons': [ '<input type="submit" value="В корзину" id="to_basket" disabled onClick="javascript:return add2basket();">', BX.CDialog.btnClose ]});
var Dialog_screen = new BX.CDialog({'title': 'Заказ видеорекламы','head': '','content':'','width': '540','height': '320','buttons': [ '<input type="submit" value="В корзину" id="to_basket_screen" onClick="javascript:return add2basket_screen();">', '<input type="submit" value="Закрыть" onClick="javascript:Dialog_screen.Close();">' ]});


function make_num(num){
	return num.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
}



function find_screen_koof(hrono,days){
	var hrono_key = 0;
	var days_key = 0;
	for ( var key in screen_base ) {
		if (hrono >= key || hrono_key==0) hrono_key = key;
	}
	var last_key = 0;
	for ( var key in screen_base[hrono_key] ) {
		//if (days >= key || days_key==0) days_key = key;
		if (days <= key){ 
			days_key = key;
			break;
		}
		last_key = key;
	}
	if (days_key==0) days_key = last_key;

	return screen_base[hrono_key][days_key];
}

var screen_request = '';

function screen_recount(){
	screen_request = '';
	//document.getElementById('screenheader').innerHTML = map_point_all[selected_point]['NAME'];
	var name = escape(document.getElementById('screenname').value);
	var comm = escape(document.getElementById('screencomm').value);
	var hron = parseInt(document.getElementById('screenhron').value);
	if ( ! (hron>=5)){
		document.getElementById('screenhron').value = 5;
		hron = 5;
 	}
	
	var date1=document.getElementById('screendate1').value;
	var date2=document.getElementById('screendate2').value;
	var kolvo = document.getElementById('screenkol').value;
	var days = Math.floor( (new Date(date2.replace(/(\d+)\.(\d+)\.(\d+).*/, '$2/$1/$3')) - new Date(date1.replace(/(\d+)\.(\d+)\.(\d+).*/, '$2/$1/$3'))) / (1000*24*60*60) ) + 1;
	var koof = find_screen_koof(hron,days);
	var cost = 0;
	if (days <= 0 || !(koof>0)){
		document.getElementById('to_basket_screen').disabled = true;
		document.getElementById('screendays').innerHTML = 'ошибка';
		document.getElementById('screencost').innerHTML = 'ошибка';
	}else{
		document.getElementById('screendays').innerHTML = 'дней: '+days;
		var cost = screen_count * screen_price * (document.getElementById('screenkol').selectedIndex+1) * days * hron * koof;
		document.getElementById('screencost').innerHTML = make_num(cost.toFixed(2))+' руб.';//+' (коэффициент: '+koof+')';
		if (name!=''){
			document.getElementById('to_basket_screen').disabled = false;
			screen_request = 'ID='+map_point_all[selected_point]['ID']+'&NAME='+name+'&DAYS='+days+'&DATE1='+date1+'&DATE2='+date2+'&HRON='+hron+'&PRICE='+cost.toFixed(2)+'&KOLVO='+kolvo+'&COMM='+comm;
		}else{
			document.getElementById('to_basket_screen').disabled = true;
		}
	}
        return false;
}

function show_window_photo(img){
	Dialog_photo.SetContent('<img src="'+img+'" border="0" width="800" height="600">');
	Dialog_photo.Show();
	return false;
}
function show_window_calendar(i){
	Dialog_calendar.SetContent('<div id="aaa"></div>');
	document.getElementById("aaa").innerHTML = create_calendar(map_point_all[i]['ID'],  map_point_all[i]['PRICE'], map_point_all[i]['PURCHASED'],  map_point_all[i]['RESERVED']);
	Dialog_calendar.Show();
	return false;
}
function show_window_screen(i){
	Dialog_screen.SetContent('<div id="bbb"><center><br><br><img src="<?=SITE_TEMPLATE_PATH?>/images/ajax.gif"></div>');
	requestURI("/catalog/show_screen.php",3,'');
	Dialog_screen.Show();
	return false;
}


function requestURI(url,type,post_params) {
	c_request = false;

	if (window.XMLHttpRequest) {
		c_request = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		c_request = new ActiveXObject("Microsoft.XMLHTTP");
	}

	if (c_request) {
		if (type==1){
			c_request.onreadystatechange = processRequestChange1;
		}else if (type==2){
			c_request.onreadystatechange = processRequestChange2;
		}else if (type==3){
			c_request.onreadystatechange = processRequestChange3;
		}else if (type==4){
			c_request.onreadystatechange = processRequestChange4;
		}
		if (post_params==''){
			c_request.open("GET", url, true);
			c_request.send();
		}else{
			c_request.open("POST", url, true);
			c_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			c_request.send(post_params);
		}
	}
}

function update_basket_num(){
	var bnum = parseInt(document.getElementById('basket_num').innerHTML.toString().replace(/\(|\)/,''));
 	if (!bnum) bnum = 0;
 	document.getElementById('basket_num').innerHTML = '('+(bnum+1)+')';
}

function processRequestChange1() {
 	if (c_request.readyState == 4) {
 		Dialog_calendar.Close();
 		if (c_request.status == 200 && c_request.responseText!='false') {
 			map_point_all[selected_point]['BASKET'] = 1;
 			var z = map_point_all[selected_point]['PARENT'];
 			map_point_data[z]['BASKET'] += 1;
 			arObjects.PLACEMARKS[arObjects_skip+z].setBalloonContent( fill_balloon( z , selected_point ) );
			update_basket_num();
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
 			map_point_all[selected_point]['BASKET'] = 0;
 			var z = map_point_all[selected_point]['PARENT'];
 			arObjects.PLACEMARKS[arObjects_skip+z].setBalloonContent( fill_balloon( z , selected_point ) );

 			map_point_data[z]['BASKET'] -= 1;
 			if (map_point_data[z]['BASKET']<0){
 				map_point_data[z]['BASKET']=0; // на всякий случай
 				alert('Произошла ошибка! Обновите страницу.');
 			}
			update_basket_num();
 			if (map_showhide[-1]==1 && map_point_data[z]['BASKET']==0) show_hide_point(z+arObjects_skip,0);
			//alert("Успешно удалено из корзины...");
		} else {
			alert("Не удалось открыть корзину!");
 		}
 	}
}

function change_map_baloon(i){
	pan_point = arObjects.PLACEMARKS[ map_point_all[i]['PARENT']+arObjects_skip ];
	map_main.panTo(pan_point.getGeoPoint());
	pan_point.openBalloon();	
}

function processRequestChange3() {
 	if (c_request.readyState == 4) {
 		if (c_request.status == 200) {
 			document.getElementById('bbb').innerHTML = c_request.responseText;
 			screen_recount();
 			var select_s='';
 			for(var i=0;i<map_point_all.length;i++){
 				if (map_point_data[ map_point_all[i]['PARENT'] ]['MCAT']==11){
 					select_s+='<option value="'+i+'"'+((i==selected_point)?' selected':'')+'>'+map_point_all[i]['NAME']+'</option>';
 				}
 			}
 			document.getElementById('screenheader').innerHTML = '<select name="screen_id" id="screen_id" onChange="javascript:change_map_baloon(this.value);">'+select_s+'</select>';
		} else {
			alert("Ошибка в открытии модуля заказа видеорекламы!");
 		}
 	}
}

function processRequestChange4() {
 	if (c_request.readyState == 4) {
 		Dialog_screen.Close();
 		if (c_request.status == 200 && c_request.responseText!='false') {
			update_basket_num();
		} else {
			alert("Ошибка в открытии модуля заказа видеорекламы!");
 		}
 	}
}


function add2basket(){
	document.getElementById('to_basket').disabled = true;
	Dialog_calendar.SetContent('<br><br><br><br><br><center><b>Добавление в корзину...</b></center>');
	requestURI("/catalog/add_basket.php?ID="+c_request_id+"&PARAM="+c_request_array.join(','),1,'');
	return false;
}

function add2basket_screen(){
	document.getElementById('to_basket_screen').disabled = true;
	screen_recount();
	if (screen_request!=''){
		Dialog_screen.SetContent('<br><br><br><center><b>Добавление в корзину...</b></center>');
		requestURI("/catalog/add_basket_screen.php?"+screen_request,4,'');
 	}
	return false;
}


function remove2basket(id_product){
	selected_point = id_product;
	if (map_point_all[selected_point]['BASKET']==1){
		arObjects.PLACEMARKS[arObjects_skip+map_point_all[selected_point]['PARENT']].setBalloonContent( '<table width="250" border="0" cellspacing="4" cellpadding="4"><tr><td align="center" nowrap>Удаление из корзины...</td></tr></table>');
		requestURI("/catalog/remove_basket.php?ID="+map_point_all[selected_point]['ID'],2,'');
	}
	return false;
}

</script>

<?if ($USER->isAdmin()):?>
<script type="text/javascript">
function admin_mode_change(){
	if (document.getElementById('admin_mode').checked==true){
		for (var i=arObjects_skip; i<arObjects.PLACEMARKS.length; i++){
			arObjects.PLACEMARKS[i].setOptions({'draggable': true});	
		}
	}else{
		for (var i=arObjects_skip; i<arObjects.PLACEMARKS.length; i++){
			arObjects.PLACEMARKS[i].setOptions({'draggable': false});	
		}
	}
	map_main.closeBalloon();
}
function admin_mode_save(){
	var val = '';
	for (var i=0; i<map_point_all.length; i++){
		var koord = (''+arObjects.PLACEMARKS[ arObjects_skip+map_point_all[i]['PARENT'] ].getGeoPoint()).split(',');
		//var map_point_data[ map_point_all[i]['PARENT'] ]['MCAT'];
		val+=map_point_all[i]['ID']+';'+map_point_data[ map_point_all[i]['PARENT'] ]['MCAT']+';'+koord[1]+','+koord[0]+';';
	}
	document.getElementById('admin_data').value = val;
}
function admin_mode_export(){
	window.open('koord.php');
	return false;
}
function admin_mode_move(i,koord){
	var k = koord.split(',');
	if (k[0]>0 && k[1]>0){
		arObjects.PLACEMARKS[ arObjects_skip+i ].setGeoPoint( new YMaps.GeoPoint(k[0],k[1]) );
		map_main.panTo( arObjects.PLACEMARKS[ arObjects_skip+i ].getGeoPoint());
	}else{
		alert('Ошибка в задании координат!');
	}
}
</script>

<div align="right">
<br>
<form name="formz" action="" method="POST">
<input type="hidden" name="admin_data" id="admin_data" value="">
Редактирование: <input type="checkbox" name="admin_mode" id="admin_mode" onClick="javascript:admin_mode_change();">
&nbsp;&nbsp;<input type="submit" name="admin_submit" value="Сохранить координаты" onClick="javascript:admin_mode_save();">
&nbsp;&nbsp;<input type="submit" name="admin_submit2" value="Экпорт координат" onClick="javascript:return admin_mode_export();">
&nbsp;&nbsp;<input type="submit" name="admin_submit" value="Загрузить координаты">
</form>
</div>
<? endif; ?>
<?
endif;
?>
