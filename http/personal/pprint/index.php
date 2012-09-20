<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Клиентам");


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


$materials = read_file_csv($_SERVER["DOCUMENT_ROOT"]."/csv/materials.csv");
for ($i=0;$i<sizeof($materials);$i++){
	for ( $j=1; $j<4; $j++ ){
		$materials[$i][$j] = preg_replace("/[^0-9]*/", "", $materials[$i][$j]);
	}
	$material_price[] = "[".$materials[$i][1].",".$materials[$i][2].",".$materials[$i][3]."]";
}

$resolutions = read_file_csv($_SERVER["DOCUMENT_ROOT"]."/csv/resolutions.csv");
for ($i=0;$i<sizeof($resolutions);$i++){
	$resolution_price[] = $resolutions[$i][1];
}


$services = read_file_csv($_SERVER["DOCUMENT_ROOT"]."/csv/services.csv");
for ($i=0;$i<sizeof($services);$i++){
	$services_price[] = "'".$services[$i][0]."':".$services[$i][1];
}


if ($_POST['2Basket']!=""){
	if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
	{
		$pall_price = GetCatalogProductPrice( 4486, 1 ); 

		$pall_price['P_PRICE'] = floatval($_POST['one_price']);

		$props = array(	
				array("SORT"=>"99","NAME" => "Код материала", "CODE" => "MAT1", "VALUE" => $materials[ $_POST['material_select'] ][4]),
				array("SORT"=>"100","NAME" => "Материал", "CODE" => "MAT2", "VALUE" => $materials[ $_POST['material_select'] ][0]),
				array("SORT"=>"101","NAME" => "Разрешение", "CODE" => "SIZE1",  "VALUE" => $resolutions[ $_POST['material_size_select'] ][0]." dpi"),
				array("SORT"=>"102","NAME" => "Высота x Ширина", "CODE" => "WH1",  "VALUE" => $_POST['material_width']."x".$_POST['material_height'] )
			);
		if ($_POST['luver_check']=='1')	$props[] = array("SORT"=>"103","NAME" => "Установка люверсов", "CODE" => "LUVER1",  "VALUE" => $_POST['luver_select'] );
		if ($_POST['prok_check']=='1')	$props[] = array("SORT"=>"104","NAME" => "Проклейка", "CODE" => "PROK1",  "VALUE" => $_POST['prok_select'] );
		if ($_POST['lam_check']=='1')	$props[] = array("SORT"=>"105","NAME" => "Ламинирование", "CODE" => "LAM1",  "VALUE" => "+" );
		if ($_POST['ypak_check']=='1')	$props[] = array("SORT"=>"106","NAME" => "Упаковка", "CODE" => "YPAK1",  "VALUE" => "+" );
		if ($_POST['rask_check']=='1')	$props[] = array("SORT"=>"107","NAME" => "Раскрой", "CODE" => "RASK1",  "VALUE" => "+" );
   		
		$props[] = array("SORT"=>"108","NAME" => "Количество", "CODE" => "COUNT1",  "VALUE" => $_POST['material_count'] );

		
		Add2Basket(
			$pall_price['ID'],
			$_POST['material_count'],
			array('CALLBACK_FUNC' => 'add2basket_pprint', 'ORDER_CALLBACK_FUNC' => 'order_pall',  'CAN_BUY'=>'Y'),
			$props
		);
		LocalRedirect('/personal/cart/');
	}
}


?> 
<style>
option.imagebacked {
padding: 2px 0 2px 20px;
background-repeat: no-repeat;
background-position: 1px 2px;
vertical-align: middle;
}
</style>
<script src="/bitrix/templates/mir/jquery/jquery-1.4.2.min.js"></script>
<script type="text/javascript">

function find_material_price_index(msize){ //-20, 20-100, 100+
	if (msize>=0 && msize<20){ return 0 }else if (msize>=20 && msize<100){ return 1 }else{ return 2 }
}

// предусмотреть разделитель - ,
var material_price = [ <?=implode(", ",$material_price);?> ];
var resolution_price = [ <?=implode(", ",$resolution_price);?> ];
var dop_price = { <?=implode(", ",$services_price);?> };

$(function() {
	function make_num(num){
		return num.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
	}
	function update_cost(){
		var m_w	= parseFloat($('#material_width').val()); if (isNaN(m_w)) m_w = 0;
		var m_h	= parseFloat($('#material_height').val()); if (isNaN(m_h)) m_h = 0;
		var m_c = parseFloat($('#material_count').val());  if (isNaN(m_c)) m_c = 1;
		var m_ind = parseFloat($('#material_select').val()); // индекс материала
		var m_pl = m_w * m_h; // площадь
		var m_price_ind = find_material_price_index( m_pl * m_c ); // индекс цены материала
		
		// Установка люверсов
		var luver_kp = 1/(30/100);
		var luver_count = 0;
		var luver_cost = 0;
		
		//var zzz = parseInt($('#luver_select').find('option:selected').index());
		//zzz = 1;
		if ($('#luver_check').attr('checked')){
			switch ( $('#luver_select').find('option:selected').index() ){
				case 0:	luver_count = 2 * luver_kp * ( m_w + m_h ); break;
				case 1:	luver_count = 2 * luver_kp * m_w ; break;
				case 2:	luver_count = luver_kp * m_w ; break;
				case 3:	luver_count = ( 2 * luver_kp * m_h ) + ( luver_kp * m_w ); break;			
				case 4:	luver_count = 2 * luver_kp * m_h ; break;
				case 5:	luver_count = luver_kp * m_w ; break;
				case 6:	luver_count = 4; break;
				case 7:	luver_count = 2; break;
				default: break;
			}
			luver_count = Math.ceil(luver_count);
			luver_cost = luver_count * dop_price['Установка люверсов'];
		}
		$('#luver_count').html( luver_count );
		$('#luver_cost').html( make_num(luver_cost.toFixed(2)) );
		
		// Проклейка
		var prok_count = 0;
		var prok_cost = 0;
		if ($('#prok_check').attr('checked')){
			switch ($('#prok_select').find('option:selected').index()){
				case 0:	prok_count = 2 * ( m_w + m_h ); break;
				case 1:	prok_count = 2 * m_w ; break;
				case 2:	prok_count = m_w ; break;
				case 3:	prok_count = ( 2 * m_h ) + m_w ; break;			
				case 4:	prok_count = 2 * m_h ; break;
				case 5:	prok_count = m_w ; break;
				default: break;
			}
			prok_count = Math.ceil(prok_count);
			prok_cost = prok_count * dop_price['Проклейка'];
		}
		$('#prok_count').html( prok_count );
		$('#prok_cost').html( make_num(prok_cost.toFixed(2)) );

		// Ламинирование
		var lam_count = 0;
		var lam_cost = 0;
		if ($('#lam_check').attr('checked')){
			lam_count = Math.ceil(m_pl);
			lam_cost = dop_price['Ламинирование'] * lam_count;
		}
		$('#lam_count').html( make_num( lam_count ) );
		$('#lam_cost').html( make_num(lam_cost.toFixed(2)) );

		// Раскрой
		var rask_count = 0;
		var rask_cost = 0;
		if ($('#rask_check').attr('checked')){
			rask_count = Math.ceil(m_pl);
			rask_cost = 2 * ( m_w + m_h ) * dop_price['Раскрой'];
		}
		$('#rask_count').html( make_num( rask_count ) );
		$('#rask_cost').html( make_num(rask_cost.toFixed(2)) );

		
		// Упаковка
		var ypak_cost = 0;
		if ($('#ypak_check').attr('checked')){
			ypak_cost = dop_price['Упаковка'];
		}
		$('#ypak_cost').html( make_num(ypak_cost.toFixed(2)) );

		// Склейка
		var sk_count = 0;
		var sk_cost = 0;
		if ($('#sk_check').attr('checked')){
			sk_count = Math.ceil(m_pl);
			sk_cost = 0;
                }
                $('#sk_count').html( make_num( sk_count ) );


		var cost1 = (material_price[ m_ind ][m_price_ind] +  resolution_price[parseFloat($('#material_size_select').val())] ) * m_pl;
		var cost2 = luver_cost + prok_cost + lam_cost + rask_cost + ypak_cost + sk_cost;

		$('#cost1').html( make_num( cost1.toFixed(2) ) );
		$('#cost2').html( make_num( cost2.toFixed(2) ) );
		$('#cost3').html( make_num( ( (cost1 + cost2) * m_c).toFixed(2) ) );
		$('#one_price').val( ( cost1 + cost2) .toFixed(2)  );
	}

	
	$('#material_select').change(function() {	update_cost();	});
	$('#material_size_select').change(function() {	update_cost();	});
	$('#material_width').change(function() {	update_cost();	});
	$('#material_width').keyup(function() {	update_cost();	});
        $('#material_height').change(function() {	update_cost();	});
        $('#material_height').keyup(function() {	update_cost();	});
	$('#material_count').change(function() {	update_cost();	});
	$('#material_count').keyup(function() {	update_cost();	});
	$('#luver_select').change(function() {	update_cost();	});
	$('#prok_select').change(function() {	update_cost();	});


	$('#luver_check').change(function() {	update_cost();	});
	$('#prok_check').change(function() {	update_cost();	});
	$('#sk_check').change(function() {	update_cost();	});
	$('#lam_check').change(function() {	update_cost();	});
	$('#ypak_check').change(function() {	update_cost();	});
	$('#rask_check').change(function() {	update_cost();	});

	//$('.input').change(function() {	update_cost();	});

	update_cost();
});

</script>
<form name="form_pprint" action="" method="POST">
<input type=hidden name="one_price" id="one_price" value="0">
<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/order_header.php',Array("INDEX"=>"2"),Array("MODE"=>"php")); ?>
<tbody>
  <tr bgcolor="#FFFFFF">
    <td class="C10_Left_Column" valign="top">
	<table border="0" cellpadding="0" cellspacing="0" width="240">
	<tbody>
	<tr>
	<td class="Ltop2">
		<p>&nbsp;</p>
		<p class="Header">Стоимость </p>
		<p class="Header">печати  изделий</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>      
	</td>
	</tr>
	</tbody>
	</table>
    </td>
    <td class="C10_Right_Column Lleft" colspan="2" valign="top" width="750">
      <table border="0" cellpadding="5" cellspacing="0" width="100%">
      <tbody>
        <tr>
          <td class="Ltop2"><p>&nbsp;</p></td>
          <td class="Ltop2"><p>&nbsp;</p></td>
          <td class="Ltop2">&nbsp;</td>
        </tr>
        <tr>
          <td align="left" nowrap="nowrap" valign="top">Материал</td>
          <td colspan="2">
		<select name='material_select' id='material_select'>
		<?
			for ($i=0;$i<sizeof($materials);$i++){
				?>
					<option value='<?=$i?>'><?=$materials[$i][0]?></option>
				<?
			}
		?>
		</select>          
          
          </td>
        </tr>
        <tr>
          <td align="left" nowrap="nowrap" valign="top">Разрешение 
            	<select name='material_size_select' id='material_size_select'>
            	<?
			for ($i=0;$i<sizeof($resolutions);$i++){
				?>
					<option value='<?=$i?>'><?=$resolutions[$i][0]?> dpi</option>
				<?
			}
		?>
		</select>
		&nbsp;<span class="active" title="Разрешение - качественный параметр печати" style="cursor:pointer;">?</span>
	  </td>
          <td nowrap="nowrap">Высота <input name='material_height' id='material_height' type="text" size="5" value='2'> метров</td>
          <td nowrap="nowrap">Ширина <input name='material_width' id='material_width' type="text" size="5" value='4'> метров</td>
          </tr>

        <tr>
          <td align="left" nowrap="nowrap" valign="top">&nbsp;</td>
          <td align="right">Стоимость печати одного изделия (Руб.)</td>
          <td align="right"><span id="cost1">0</span></td>
        </tr>
      </tbody>
    </table></td>
  </tr>
  <tr bgcolor="#FFFFFF">
    <td class="C10_Left_Column" valign="top"><p>&nbsp;</p>
      <p class="Header">Стоимость </p>
      <p class="Header">дополнительных работ</p>
      <p>&nbsp;</p>
      <p>Установка люверсов - протыкание и укрепление отверстия металлическими кольцами для крепления к чему-либо.</p>
      <p>&nbsp;</p>
      <p>Проклейка - укрепление края изделия</p>
      <p>&nbsp;</p>
      <p>Ламинирование - нанесение защитного покрытия. </p>
      </td>
    <td class="C10_Right_Column Lleft" colspan="2" valign="top">
    <table border="0" cellpadding="5" cellspacing="0" width="100%">
      <tbody>
        <tr>
          <td class="Ltop2" width="250"><p>&nbsp;</p></td>
          <td class="Ltop2" width="250"><p>&nbsp;</p></td>
          <td class="Ltop2" width="250">&nbsp;</td>
          <td class="Ltop2" align="right" width="250">&nbsp;</td>
        </tr>
        <tr>
          <td align="left" nowrap="nowrap" valign="top">Установка люверсов 
            <input name="luver_check" id="luver_check" checked="checked" type="checkbox" value="1">
            <span class="active" title="Установка люверсов - протыкание и укрепление отверстия металлическими кольцами для крепления к чему-либо." style="cursor:pointer;">?</span></td>
          <td nowrap="nowrap">
                <select name='luver_select' id='luver_select'>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/1.gif);">По всему периметру</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/2.gif);">Сверху и снизу</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/3.gif);">Только сверху</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/4.gif);">Сверху и по краям</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/5.gif);">Только по краям</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/6.gif);">Только снизу</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/7.gif);">Только по всем углам</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/8.gif);">Только по верхним углам</option>
                </select>
          </td>
          <td nowrap="nowrap">Количество люверсов - <span id="luver_count">0</span></td>
          <td align="right" nowrap="nowrap"><span id="luver_cost">0</span></td>
        </tr>

        <tr>
          <td align="left" nowrap="nowrap" valign="top">Проклейка
            <input name="prok_check" id="prok_check" checked="checked" type="checkbox" value="1">
            <span class="active" title="Проклейка - укрепление края изделия" style="cursor:pointer;">?</span></td>
          <td nowrap="nowrap">
                <select name='prok_select' id='prok_select'>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/1.gif);">По всему периметру</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/2.gif);">Сверху и снизу</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/3.gif);">Только сверху</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/4.gif);">Сверху и по краям</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/5.gif);">Только по краям</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/6.gif);">Только снизу</option>
                </select>
          </td>
          <td nowrap="nowrap">Количество метров пог. - <span id="prok_count">0</span></td>
          <td align="right" nowrap="nowrap"><span id="prok_cost">0</span></td>
        </tr>
        <tr>
          <td align="left" nowrap="nowrap" valign="top">Склейка
            <input name="sk_check" id="sk_check" type="checkbox" disabled value="1">
            <span class="active" style="cursor:pointer;">?</span></td>
          <td nowrap="nowrap">&nbsp;</td>
          <td nowrap="nowrap">Количество метров пог. - <span id="sk_count">0</span></td>
          <td align="right" nowrap="nowrap"><span id="sk_cost">0</span></td>
        </tr>
        <tr>
          <td align="left" nowrap="nowrap" valign="top">Ламинирование
            <input name="lam_check" id="lam_check" type="checkbox" value="1">
            <span class="active" title="Ламинирование - нанесение защитного покрытия." style="cursor:pointer;">?</span></td>
          <td nowrap="nowrap">&nbsp;</td>
          <td nowrap="nowrap">Количество метров кв. - <span id="lam_count">0</span></td>
          <td align="right" nowrap="nowrap"><span id="lam_cost">0</span></td>
        </tr>
        <tr>
          <td align="left" nowrap="nowrap" valign="top">Упаковка
            <input name="ypak_check" id="ypak_check" type="checkbox" value="1"></td>
          <td nowrap="nowrap">&nbsp;</td>
          <td nowrap="nowrap">цена за один баннер</td>
          <td align="right" nowrap="nowrap"><span id="ypak_cost">0</span></td>
        </tr>
        <tr>
          <td align="left" nowrap="nowrap" valign="top">Раскрой 
            <input name="rask_check" id="rask_check" type="checkbox" value="1"></td>
          <td nowrap="nowrap">&nbsp;</td>
          <td nowrap="nowrap">Количество метров пог. - <span id="rask_count">0</span></td>
          <td align="right" nowrap="nowrap"><span id="rask_cost">0</span></td>
        </tr>
        <tr>
          <td align="left" nowrap="nowrap" valign="top">&nbsp;</td>
          <td colspan="2" align="right" nowrap="nowrap">Стоимость дополнительной подготовки одного изделия (Руб.)</td>
          <td align="right" nowrap="nowrap"><span id="cost2">0</span></td>
        </tr>
        <tr>
          <td class="Ltop2" align="left" nowrap="nowrap" valign="top">&nbsp;</td>
          <td colspan="2" class="Ltop2" align="right" nowrap="nowrap">&nbsp;</td>
          <td class="Ltop2" align="right" nowrap="nowrap">&nbsp;</td>
        </tr>
        <tr>
          <td align="left" nowrap="nowrap" valign="top">Количество изделий <input name="material_count" id="material_count" size="3" type="text" value="1"></td>
          <td colspan="2" align="right" nowrap="nowrap"><strong>Стоимость печати и дополнительной подготовки всех изделий</strong> (Руб.)</td>
          <td class="Header" align="right" nowrap="nowrap"><span id="cost3">0</span></td>
        </tr>
      </tbody>
    </table>
        <p>&nbsp;</p>
	<div class="cart-buttons" align="right">
		<input value="Добавить в корзину" name="2Basket" id="2Basket" type="submit">
	</div>
	
    </td>
  </tr>
</tbody>

<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/order_footer.php',Array(),Array("MODE"=>"php")); ?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
