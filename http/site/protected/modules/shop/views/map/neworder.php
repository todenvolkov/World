<?php $this->pageTitle = "Basket";?>

<div class="basket_content" style="width:100%">
    <h2>НОВЫЙ <strong>ЗАКАЗ</strong></h2>
    <div class="info clearfix staticpage">
        <div id="basket_content" class="content">
        
        <form action method="post" enctype="application/x-www-form-urlencoded" id="neworder" name="neworder">
        	<input type="hidden" name="<?=Yii::app()->request->csrfTokenName?>" value="<?=Yii::app()->request->csrfToken?>"/>
            <input name="Order[step]" type="hidden" value="2" />
            <table border="0">
            <?php foreach($fields as $field): ?>
              <tr>
                <td><label for="Order[ORDER_PROP_<?=$field["CODE"]?>_<?=$field["ID"]?>]"><?=$field["NAME"]?></label></td>
                <td>&nbsp;</td>
                <td><input id="Order[ORDER_PROP_<?=$field["CODE"]?>_<?=$field["ID"]?>]" name="Order[ORDER_PROP_<?=$field["CODE"]?>_<?=$field["ID"]?>]" type="<?=$field["TYPE"]?>" /></td>
              </tr>
            <?php endforeach; ?>
            </table>
            <br><br>
<table width="100%" border="0">
  <thead>
    <th width="503">Наименование</th>
    <th>Начало</th>
    <th>Конец</th>
    <th>Дней</th>
    <th>Руб./день</th>
    <th width="78">Руб.</th>
  </thead>
            <?php foreach($basket_items as $item): ?>
   <tr>
    <td><?=$item["NAME"]?></td>
    <td align="center"><?=$item["dt_start"]?></td>
    <td align="center"><?=$item["dt_end"]?></td>
    <td align="center"><?=(int)$item["QUANTITY"]?></td>
    <td align="center"><?=number_format($item["PRICE"],2,',',' ')?></td>
    <td align="center"><?=number_format($item["total"],2,',',' ')?></td>
  </tr>
            <?php endforeach; ?>
</table>
		<a id="submit" class="more subscribe" style="cursor:pointer">отправить заказ</a>
        </form>
      </div>
    </div>
</div>
<script language="javascript" type="application/javascript">
	jQuery("#submit").click(function(){
		jQuery("#neworder").submit();
	});
</script>