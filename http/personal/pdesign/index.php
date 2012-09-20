<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказать дизайн-проект");

if(!$USER->IsAuthorized()){
       LocalRedirect('/login/');
}

if ($_POST['2Basket']!=""){
	if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
	{
		
		$pall_price = GetCatalogProductPrice( 4490, 1 ); 
                $pall_price['P_PRICE'] = 0;
                $props = array(	
				array("SORT"=>"100","NAME" => "Тип проекта", "CODE" => "TYPE1", "VALUE" => $_POST['pdesign_type'])
			);
   		
   		if ($_POST['pdesign_height']!="" && $_POST['pdesign_width']!=""){
			$props[]=array("SORT"=>"101","NAME" => "Высота оригинала", "CODE" => "HEIGHT1", "VALUE" => $_POST['pdesign_height']." ".$_POST['pdesign_height_']);
			$props[]=array("SORT"=>"102","NAME" => "Ширина оригинала", "CODE" => "WIDTH1", "VALUE" => $_POST['pdesign_width']." ".$_POST['pdesign_width_']);
		}
 		if ($_POST['pdesign_text']!="") $props[]=array("SORT"=>"103","NAME" => "Комментарии", "CODE" => "COMMENT1", "VALUE" => $_POST['pdesign_text']);


		
		$i = 1;
		foreach ($_FILES as $file){
			if ($file["size"]>0){
				$fid = CFile::SaveFile(array_merge($file,array("del"=>"N","MODULE_ID"=>"pdesign")), "pdesign");
				if ($fid){
					$props[] = array("SORT"=>110+$i*2,"NAME" => "ID Файла № ".$i, "CODE" => "FILE_ID".$i,  "VALUE" => $fid );
					$props[] = array("SORT"=>110+$i*2+1,"NAME" => "Файл № ".$i, "CODE" => "FILE".$i,  "VALUE" => Cfile::GetPath($fid) );
				}else{
					echo "Ошибка загрузки файла! Обратитесь к Администрации.";
					exit;
				}
				$i++;
			}
		}
		
		Add2Basket(
			$pall_price['ID'],
			1,
			array('CALLBACK_FUNC' => 'add2basket_pdesign', 'ORDER_CALLBACK_FUNC' => 'order_pall', 'CANCEL_CALLBACK_FUNC' => 'cancel_pdesign' ,  'CAN_BUY'=>'Y'),
			$props
		);
		LocalRedirect('/personal/cart/');
	}
}


?>
<form name="form_pdesign" action="" method="POST" enctype="multipart/form-data">
<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/order_header.php',Array("INDEX"=>"3"),Array("MODE"=>"php")); ?>
<tbody>

<tr bgcolor="#FFFFFF">
    <td class="C10_Left_Column" valign="top">
	<table border="0" cellpadding="0" cellspacing="0" width="240">
	<tbody>
	<tr>
	<td class="Ltop2">
		<p>&nbsp;</p>
		<p class="Header">Детали</p>
		<p class="Header">заказа</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>      
	</td>
	</tr>
	</tbody>
	</table>
    </td>
   <td class="C10_Right_Column Lleft" colspan="2" valign="top">

<table width="100%" cellspacing="0" cellpadding="5" border="0">
      <tbody>
        <tr>
          <td width="22%" class="Ltop2"><p>&nbsp;</p></td>
          <td width="41%" class="Ltop2"><p>&nbsp;</p></td>
          <td width="37%" class="Ltop2">&nbsp;</td>
        </tr>
        <tr>
          <td valign="top" nowrap="nowrap" align="left">Тип проекта</td>
          <td colspan="2"><select id="pdesign_type" name="pdesign_type">
            <option>Широкоформатная цветная печать</option>
            <option>Полиграфическое воспроизведение</option>
            <option>Медиа. Интернет. Презентация.</option>
            <option>Видеоролик анимациционный</option>
          </select>          </td>
          </tr>
        <tr>
          <td valign="top" nowrap="nowrap" align="left">&nbsp;</td>
          <td nowrap="nowrap">Высота оригинала           
            <input type="text" size="3" id="pdesign_height" name="pdesign_height">
            <select id="pdesign_height_" name="pdesign_height_">
              <option>Метров</option>
              <option>Сантиметров</option>
              <option>Миллиметров</option>
              <option>Пикселов</option>
            </select></td>
          <td nowrap="nowrap">Ширина оригинала           
            <input type="text" size="3" id="pdesign_width" name="pdesign_width">
            <select id="pdesign_width_" name="pdesign_width_">
              <option>Метров</option>
              <option>Сантиметров</option>
              <option>Миллиметров</option>
              <option>Пикселов</option>
            </select></td>
          </tr>

        <tr>
          <td valign="top" nowrap="nowrap" align="left">Макет
          </td>
          <td align="left" colspan="2"> 
          	Приложите файл с макетом, заданием, текстовым контентом, логотипом. (размер максимум 5 Mb)<br>
          	<input type="file" name="filename1"><br>
          	<input type="file" name="filename2"><br>
          	<input type="file" name="filename3"><br>
          	<input type="file" name="filename4"><br>
  	  </td>
          </tr>
        <tr>
          <td valign="top" nowrap="nowrap" align="left">&nbsp;</td>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td valign="top" nowrap="nowrap" align="left">Комментарии</td>
          <td colspan="2"><textarea id="pdesign_text" rows="5" cols="77" name="pdesign_text"></textarea></td>
          </tr>
        <tr>
         <td colspan=3>
         	<div class="cart-buttons" align="right">
		<input value="Добавить в корзину" name="2Basket" id="2Basket" type="submit">
	</div>
         </td>
        </tr>
      </tbody>
    </table>

	
	</td>	
</tr>





<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/order_footer.php',Array(),Array("MODE"=>"php")); ?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

