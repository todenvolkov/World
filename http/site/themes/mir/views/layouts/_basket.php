<?php
$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
$conn = Yii::app()->db2;
$comm = $conn->createCommand("SELECT COUNT(id) AS cnt, SUM(PRICE*QUANTITY) AS total  FROM b_sale_basket WHERE FUSER_ID=:uid AND ORDER_ID IS NULL AND DELAY='N'");
$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
$res = $comm->queryRow();

/*
$comm = $conn->createCommand("SELECT SUM(PRICE*QUANTITY) FROM b_sale_basket WHERE FUSER_ID=:uid AND ORDER_ID IS NULL");
$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
$tprice = $comm->queryScalar();*/
?>

<a href="<?=Yii::app()->createUrl('/shop/map/basket')?>">
    <div class="item"><?=$res['cnt']?></div>
    <ul class="clearfix">
        <li class="first"><span>В вашей корзине товаров:</span> <?=$res['cnt']?></li>
        <li class="last">на сумму: <?=number_format($res['total'], 0, ' ', ' ')?> руб.</li>
    </ul>
</a>