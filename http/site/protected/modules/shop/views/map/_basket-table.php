        <table width="100%" border="0" cellpadding="5" cellspacing="0">
          <thead>
            <th width="503">Информация</th>
            <th>Сумма</th>
            <th width="78">Действия</th>
          </thead>
        
            <?php foreach($data as $bi):?>
          <tr>
            <td>
                Наименование: <?=$bi['NAME']?></br>
                Дата начала: <?=$bi['dt_start']?></br>
                Дата конца: <?=$bi['dt_end']?></br>
                Дней: <?=number_format($bi['QUANTITY'],0)?>
            </td>
            <td align="center"><?=$bi['total']?></td>
            <td align="center">
            
            <?php
			
			if($bi['DELAY']=='N'){
				$title = "Отложить";
				$action = "/shop/map/delay/id/".$bi['ID'];
				$id = "delay".$bi['ID'];
				$refresh_count = "jQuery('#cart_count').html(parseInt(jQuery('#cart_count').html())-1).show('fast'); jQuery('#delay_count').html(parseInt(jQuery('#delay_count').html())+1).show('fast');";
				$refresh_del = "jQuery('#cart_count').html(parseInt(jQuery('#cart_count').html())-1).show('fast'); jQuery('#basket_total').fadeOut('fast').load('".Yii::app()->createUrl("shop/map/printTotalBasket")."',function(){ jQuery('#basket_total').fadeIn('fast') });";
			}else{
				$title = "В корзину";
				$action = "/shop/map/undelay/id/".$bi['ID'];
				$id = "undelay".$bi['ID'];
				$refresh_count = "jQuery('#cart_count').html(parseInt(jQuery('#cart_count').html())+1).show('fast'); jQuery('#delay_count').html(parseInt(jQuery('#delay_count').html())-1).show('fast');";
				$refresh_del = "jQuery('#delay_count').html(parseInt(jQuery('#delay_count').html())-1).show('fast');";
			}
			
            echo CHtml::ajaxLink(
                'Удалить',
                Yii::app()->createUrl('/shop/map/delItem/id/'.$bi['ID']),
                array(
                'type'=>'POST',
				'data'=>array("id"=>$bi['ID'],Yii::app()->request->csrfTokenName => Yii::app()->request->csrfToken),
                'success'=>"function(data){
					jQuery('#".$id."').parent().parent().hide(600);
					".$refresh_del."
                }"
                ),
                array("id"=>"del".$bi['ID'], "live"=>false)
                );
            ?> <div style="line-height: 8px;">&nbsp;</div>
            <?php
            echo CHtml::ajaxLink(
                $title,
                Yii::app()->createUrl($action),
                array(
                'type'=>'POST',
                'data'=>array("id"=>$bi['ID'],Yii::app()->request->csrfTokenName => Yii::app()->request->csrfToken),
                'success'=>"function(data){
					jQuery('#".$id."').parent().parent().hide(600);
					".$refresh_count."
					jQuery('#basket_total').fadeOut('fast').load('".Yii::app()->createUrl("shop/map/printTotalBasket")."',function(){ jQuery('#basket_total').fadeIn('fast') });
                }",
                ),
                array("id"=>$id, "live"=>true)
                );
            ?>
            </td>
          </tr>
            <?php endforeach; ?>
            <tr>
            	<td style="border:none; text-transform:uppercase;"><div class="basket_total">ИТОГОВАЯ СУММА: <strong id="basket_total"><?=number_format(ShoppingCart::getTotalPrice(), 0, ' ', ' ')." руб."?></strong></div></td><td colspan="2" style="border:none"><?php if(count($data)>0): ?><a href="<?=Yii::app()->createUrl('/shop/map/order/make')?>" class="more subscribe" style="margin: 17px 0px 17px auto;">оформить заказ</a><?php endif; ?></td>
            </tr>
        </table>
<!-- 
'success'=>"function(data){
					jQuery('#basket_content').hide('fast');
					jQuery.ajax({url:'".Yii::app()->createUrl('/shop/map/printBasket'.($bi['DELAY']=='Y'?"/delay/Y":""))."'}).success(function(data){
						jQuery('#basket_content').html(data).show('slow');
					});
                }", -->