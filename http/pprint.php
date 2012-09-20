<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Клиентам");
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
var material_price = [ [320,260,220] ,	[250,200,180] ,	[160,120,94],	[190,150,130]];
var dop_price = {
'Ламинирование':250,
'Люверсовка':10,
'Проклейка':50,
'Раскрой':30,
'Склейка':80,
'Упаковка':100
};


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
		var m_price_ind = find_material_price_index( m_pl ); // индекс цены материала
		
		// Люверсовка
		var luver_kp = 1/(30/100);
		var luver_count = 0;
		var luver_cost = 0;
		switch ($('#luver_select').val()){
			case '1':	luver_count = 2 * luver_kp * ( m_w + m_h ); break;
			case '2':	luver_count = 2 * luver_kp * m_w ; break;
			case '3':	luver_count = luver_kp * m_w ; break;
			case '4':	luver_count = ( 2 * luver_kp * m_h ) + ( luver_kp * m_w ); break;			
			case '5':	luver_count = 2 * luver_kp * m_h ; break;
			case '6':	luver_count = luver_kp * m_w ; break;
			case '7':	luver_count = 4; break;
			case '8':	luver_count = 2; break;
		}
		if ($('#luver_check').attr('checked')){
			luver_count = Math.ceil(luver_count);
			luver_cost = luver_count * dop_price['Люверсовка'];
		}
		$('#luver_count').html( luver_count );
		$('#luver_cost').html( make_num(luver_cost.toFixed(2)) );
		
		// Проклейка
		var prok_count = 0;
		var prok_cost = 0;
		switch ($('#prok_select').val()){
			case '1':	prok_count = 2 * ( m_w + m_h ); break;
			case '2':	prok_count = 2 * m_w ; break;
			case '3':	prok_count = m_w ; break;
			case '4':	prok_count = ( 2 * m_h ) + m_w ; break;			
			case '5':	prok_count = 2 * m_h ; break;
			case '6':	prok_count = m_w ; break;
		}
		if ($('#prok_check').attr('checked')){
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


		var cost1 = (material_price[ m_ind ][m_price_ind] +  parseFloat($('#material_size_select').val()) ) * m_pl;
		var cost2 = luver_cost + prok_cost + lam_cost + rask_cost + ypak_cost + sk_cost;

		$('#cost1').html( make_num( cost1.toFixed(2) ) );
		$('#cost2').html( make_num( cost2.toFixed(2) ) );
		$('#cost3').html( make_num( ( (cost1 + cost2) * m_c).toFixed(2) ) );
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

<table align="left" bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="1000">
<thead>
<tr bgcolor="#FFFFFF">
	<td height="50" width="250">
		<p><img src="/bitrix/templates/mir/images/Header_Order.png" height="25" width="150"></p>	</td>
	<td class="C10" width="250">
		<p class="Header">Полноцветная</p>
		<p class="Header">широкоформатная</p>
		<p class="Header">печать<strong></strong></p></td>
	<td class="C10" align="left" valign="top" width="500">
		<p>Заказать:</p>
		<p><a href="">Кампанию наружной рекламы</a></p>
		<p><a href="">Дизайн-Проект</a></p>
		<p><a href="">Производство рекламных конструкций</a></p>	</td>
</tr>
</thead>
<tbody>
	<tr bgcolor="#FFFFFF">
	<td class="C10_Left_Column" valign="top">
		<table border="0" cellpadding="0" cellspacing="0" width="240">
			<tbody><tr>
				<td class="Ltop2">
					<p>&nbsp;</p>
					<p class="Header">Контактные данные</p>				</td>
			</tr>
		</tbody></table>	</td>
	<td class="C10_Right_Column Lleft" colspan="2" valign="top" width="750">
		<table border="0" cellpadding="5" cellspacing="0" width="100%">
                <tbody><tr><td class="Ltop2"><p>&nbsp;</p></td>
                <td class="Ltop2"><p>&nbsp;</p></td>
              

				<td class="Ltop2">&nbsp;</td>
                <td class="Ltop2">&nbsp;</td>
                </tr><tr>
					<td align="left" nowrap="nowrap" valign="top">
						ФАМИЛИЯ Имя:
						 <span class="active">*</span>					</td>
					<td>
	  <input maxlength="250" size="10" name="ORDER_PROP_1" id="ORDER_PROP_1" style="width: 200px;" type="text">
													<span id="ФАМИЛИЯ Имя"></span>					</td>
				    <td nowrap="nowrap">Компания: <span class="active">*</span> </td>
                    <td><input maxlength="250" size="0" name="ORDER_PROP_20" id="ORDER_PROP_20" style="width: 200px;" type="text"></td>
                </tr>
								<tr>
					<td align="left" nowrap="nowrap" valign="top">&nbsp;</td>
					<td nowrap="nowrap"><span id="Компания"></span></td>
				    <td>Должность: <span class="active">*</span> </td>
							<td><input maxlength="250" size="0" name="ORDER_PROP_21" id="ORDER_PROP_21" style="width: 200px;" type="text"></td>
							</tr>
								<tr>
					<td align="left" nowrap="nowrap" valign="top">&nbsp;</td>
					<td><span id="Должность"></span>					</td>
				    <td>&nbsp;</td>
							<td>&nbsp;</td>
							</tr>
								<tr>
					<td align="left" nowrap="nowrap" valign="top">
						Телефон:
						 <span class="active">*</span>					</td>
  <td>
			    <input name="ORDER_PROP_22" id="ORDER_PROP_22" style="width: 200px;" value="+7 " size="0" maxlength="250" type="text">
													<span id="Телефон"></span>					</td>
				    <td nowrap="nowrap">Email: <span class="active">*</span> </td>
							<td><input name="ORDER_PROP_23" id="ORDER_PROP_23" style="width: 200px;" value="@" size="0" maxlength="250" type="text"></td>
							</tr>
		</tbody></table>	</td>
</tr>
  <tr bgcolor="#FFFFFF">
    <td class="C10_Left_Column" valign="top"><p>&nbsp;</p>
      <p class="Header">Стоимость </p>
      <p class="Header">печати  изделий</p>
      <p>&nbsp;</p>
      <p>Разрешение - параметр печати. </p>      </td>
    <td class="C10_Right_Column Lleft" colspan="2" valign="top"><p>&nbsp;</p>
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
			<option value='0'>Виниловая ткань DLS Германия</option>
			<option value='1'>Виниловая ткань DLS Россия</option>
			<option value='2'>Виниловая ткань Frontlit Китай 300 г/м3</option>
			<option value='3'>Виниловая ткань Frontlit Китай 440 г/м3</option>
		</select>          
          
          </td>
        </tr>
        <tr>
          <td align="left" nowrap="nowrap" valign="top">Разрешение 
            	<select name='material_size_select' id='material_size_select'>
			<option value='0'>360</option>
			<option value='30'>720</option>
		</select> dpi
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
      <p>Люверсовка - протыкание и укрепление отверстия металлическими кольцами для крепления к чему-либо.</p>
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
          <td align="left" nowrap="nowrap" valign="top">Люверсовка 
            <input name="luver_check" id="luver_check" checked="checked" type="checkbox" value="1">
            <span class="active" title="Люверсовка - протыкание и укрепление отверстия металлическими кольцами для крепления к чему-либо." style="cursor:pointer;">?</span></td>
          <td nowrap="nowrap">
                <select name='luver_select' id='luver_select'>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/1.gif);" value="1">По всему периметру</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/2.gif);" value="2">Сверху и снизу</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/3.gif);" value="3">Только сверху</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/4.gif);" value="4">Сверху и по краям</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/5.gif);" value="5">Только по краям</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/6.gif);" value="6">Только снизу</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/7.gif);" value="7">Только по всем углам</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/8.gif);" value="8">Только по верхним углам</option>
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
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/1.gif);" value="1">По всему периметру</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/2.gif);" value="2">Сверху и снизу</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/3.gif);" value="3">Только сверху</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/4.gif);" value="4">Сверху и по краям</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/5.gif);" value="5">Только по краям</option>
			<option class="imagebacked" style="background-image: url(/bitrix/templates/mir/images/icon/luver/6.gif);" value="6">Только снизу</option>
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
            <span class="active" style="cursor:pointer;">?</span></td>
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
    </table></td>
  </tr>

<tr bgcolor="#FFFFFF">
<td colspan="3"><p>&nbsp;</p>
  <p class="sof-ok">Комментарий от FIGURA: </p>
  <p class="sof-ok">Дополнительно рекомендую показывать только разъяснения типов допработ и предложения выбора типов люверсовки и проклейки. </p>
  <p class="sof-ok">Всплывающими title (текстовые подсказки)и окнами (иконки с типами).</p>
  <p>&nbsp;</p></td>            
</tr>
</tbody>
</table>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
