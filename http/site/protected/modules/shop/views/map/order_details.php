<?php $this->pageTitle = "Basket";?>
<?php $this->renderPartial("_basket_menu"); ?>
<div class="basket_content">
    <h2>ЗАКАЗ <strong>№<?=$id?></strong></h2>
    <div class="info clearfix staticpage">
        <div id="basket_content" class="content">
        <h3 style="font:11px Tahoma, Geneva, sans-serif; font-weight:bold; padding: 15px 0px;">ДЕТАЛИ ЗАКАЗА:</h3>
        <?php $order_status = array("N"=>"Принят","P"=>"В обработке","F"=>"Выполнен","С"=>"Отменен") ?>
        
        
        <table class="order-detail data" width="400">
            <tbody><tr>
                <td nowrap="nowrap" width="100"><strong>НОМЕР:</strong></td>
                <td><?=$order["ID"]?></td>
            </tr>
            <tr>
                <td nowrap="nowrap"><strong>СТАТУС:</strong></td>
                <td><?=$order_status[$order["STATUS_ID"]]?></td>
            </tr>
            <tr>
                <td nowrap="nowrap"><strong>РАЗМЕЩЕН:</strong>&nbsp;&nbsp;</td>
                <td><?=$order["DATE_INSERT"]?></td>
            </tr>
            <tr>
                <td nowrap="nowrap"><strong>ОПЛАТА:</strong></td>
                <td><?=$order["PAYED"]=='Y'?"Оплачен":"Не оплачен"?></td>
            </tr>
            </tbody>
        </table>
        <h3 style="font:11px Tahoma, Geneva, sans-serif; font-weight:bold; padding: 15px 0px;">ПОЗИЦИИ:</h3>
        <table class="my-orders data" width="100%">
            <thead>
                <tr>
                    <th>Наименование</th>
                    <th class="h-align-ctr">Цена</th>
                    <th>Кол-во дней</th>
                    <th>Сумма</th>
                </tr>
            </thead>
            <tbody>
            <?php 
			$total = 0;
			foreach($data as $item): ?>
                <tr>
                    <td><?=$item["NAME"]?></td>
                    <td align="center"><?=number_format($item["PRICE"],2,","," ")?></td>
                    <td align="center"><?=(int)$item["QUANTITY"]?></td>
                    <td align="center"><?=number_format($item["PRICE"]*$item["QUANTITY"],2,","," ")?></td>
                </tr>
            <?php 
			$total += $item["PRICE"]*$item["QUANTITY"];
			endforeach; 
			?>
            	<tr><td colspan="2"></td><td><strong>ИТОГОВАЯ СУММА:</strong></td><td align="center"><?=number_format($total,2,","," ")?>&nbsp;</td></tr>
            </tbody>
    	</table>
        <h3 style="font:11px Tahoma, Geneva, sans-serif; font-weight:bold; padding: 15px 0px;">ИНФОРМАЦИЯ О ДОСТАВКЕ:</h3>
        <br><br><br>
        </div>
    </div>
</div>
