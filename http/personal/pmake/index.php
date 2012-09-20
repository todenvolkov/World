<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказать производство рекламных конструкций");

if(!$USER->IsAuthorized()){
       LocalRedirect('/login/');
}


if ($_POST['2Basket']!=""){
	if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))
	{

		$pall_price = GetCatalogProductPrice( 4502, 1 ); 
                
                $pall_price['P_PRICE'] = 0;

                $props = array(	
				array("SORT"=>100,"NAME" => "Тип конструкции", "CODE" => "TYPE1", "VALUE" => $_POST['pmake_type']),
				array("SORT"=>101,"NAME" => "Место установки", "CODE" => "PLACE1", "VALUE" => $_POST['pmake_place']),
				array("SORT"=>102,"NAME" => "Классность проекта", "CODE" => "CLASS1", "VALUE" => $_POST['pmake_class'])
			);
   		
   		if ($_POST['pmake_height']!="" && $_POST['pmake_width']!=""){
			$props[]=array("SORT"=>103,"NAME" => "Высота оригинала", "CODE" => "HEIGHT1", "VALUE" => $_POST['pmake_height']." ".$_POST['pmake_height_']);
			$props[]=array("SORT"=>104,"NAME" => "Ширина оригинала", "CODE" => "WIDTH1", "VALUE" => $_POST['pmake_width']." ".$_POST['pmake_width_']);
		}
 		if ($_POST['pmake_text']!="") $props[]=array("SORT"=>110,"NAME" => "Комментарии", "CODE" => "COMMENT1", "VALUE" => $_POST['pmake_text']);


		
		$i = 1;
		foreach ($_FILES as $file){
			if ($file["size"]>0){
				$fid = CFile::SaveFile(array_merge($file,array("del"=>"N","MODULE_ID"=>"pmake")), "pmake");
				if ($fid){
					$props[] = array("SORT"=>120+$i*2,"NAME" => "ID Файла № ".$i, "CODE" => "FILE_ID".$i,  "VALUE" => $fid );
					$props[] = array("SORT"=>121+$i*2,"NAME" => "Файл № ".$i, "CODE" => "FILE".$i,  "VALUE" => Cfile::GetPath($fid) );
				}else{
					echo "Ошибка загрузки файла! Обратитесь к Администрации.";
					exit;
				}
				$i++;
			}
		}
		

		
		$aa = Add2Basket(
			$pall_price['ID'],
			1,
			array('CALLBACK_FUNC' => 'add2basket_pmake', 'ORDER_CALLBACK_FUNC' => 'order_pall',  'CAN_BUY'=>'Y'),
			$props
		);

		LocalRedirect('/personal/cart/');
	}
}


?>
<form name="form_pmake" action="" method="POST" enctype="multipart/form-data">
<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/order_header.php',Array("INDEX"=>"4"),Array("MODE"=>"php")); ?>
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
          <td valign="top" nowrap="nowrap" align="left">Тип конструкции</td>
          <td colspan="2"><textarea id="pmake_type" rows="3" cols="77" name="pmake_type"></textarea></td>
        </tr>
        <tr>
          <td valign="top" nowrap="nowrap" align="left">Место установки</td>
          <td colspan="2"><input type="pmake_place" size="57" id="pmake_place" name="pmake_place"></td>
        </tr>
        <tr>
        <tr>
          <td valign="top" nowrap="nowrap" align="left">Классность проекта</td>
          <td colspan="2">
          <select id="pmake_class" name="pmake_class">
            <option>Хозяйственная конструкция</option>
            <option>Конструкция общего пользования</option>
            <option>Конструкция повышенных свойств</option>
          </select>          
          </td>
        </tr>

        <tr>
          <td align="left" nowrap="nowrap" valign="top">&nbsp;</td>
          <td nowrap="nowrap">Высота оригинала           
            <input size="3" id="pmake_height" name="pmake_height" type="text"> метров
          </td>
          <td nowrap="nowrap">Ширина оригинала           
            <input size="3" id="pmake_width" name="pmake_width" type="text"> метров
          </td>
        </tr>  
        


        <tr>
          <td valign="top" nowrap="nowrap" align="left">Макет
          </td>
          <td align="left" colspan="2"> 
          	Приложите файл с проектом, чертежи, эскиз. (размер максимум 5 Mb)<br>
          	<input type="file" name="filename1"><br>
  	  </td>
          </tr>
        <tr>
          <td valign="top" nowrap="nowrap" align="left">&nbsp;</td>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td valign="top" nowrap="nowrap" align="left">Комментарии</td>
          <td colspan="2"><textarea id="pmake_text" rows="5" cols="77" name="pmake_text"></textarea></td>
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
