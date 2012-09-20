<?php $this->pageTitle = "Basket";?>
<?php $this->renderPartial("_basket_menu"); ?>
<div class="basket_content">
    <h2><?=$title?> <strong>ЗАКАЗЫ</strong></h2>
    <div class="info clearfix staticpage">
        <div id="basket_content" class="content">
        <?php $order_status = array("N"=>"Принят","P"=>"В обработке")?>
        
	<table class="my-orders data" width="100%">
  		<thead>
			<tr>
				<th>ID</th>
				<th class="h-align-ctr">Стоимость</th>
				<th>Размещен</th>
				<th>Статус</th>
				<th class="h-align-ctr">&nbsp;</th>
			</tr>
		</thead>
        <tbody>
        <?php foreach($data as $order): ?>
            <tr>
                        <td align="center"><?=$order["ID"]?></td>
                        <td align="center"><?=number_format($order["PRICE"],2,","," ")?></td>
                        <td align="center"><?=$order["DATE_INSERT"]?></td>
                        <td align="center"><?=$order_status[$order["STATUS_ID"]]?></td>
                        <td align="center"><?=CHtml::link("<strong>Подробности</strong>",Yii::app()->createUrl('/shop/map/order/'.$order["ID"]))?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
        </div>
    </div>
</div>