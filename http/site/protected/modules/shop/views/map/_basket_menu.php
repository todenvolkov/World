<div class="basket_menu">
	<ul>
        <li class="header">Личный кабинет</li>
     <?php if(Yii::app()->user->biuser): ?>
        <li><a href="<?=Yii::app()->createUrl("/user/account/bilogin")?>">Личные данные</a></li>
        <li><a href="<?=Yii::app()->createUrl("/user/account/bilogin")?>">Создать пост</a></li>
        <li><a href="<?=Yii::app()->createUrl("/user/account/bilogin")?>">Мои посты</a></li>
        <li><a href="<?=Yii::app()->createUrl("/user/account/bilogin")?>">Мое портфолио</a></li>
        <li><a href="<?=Yii::app()->createUrl("/user/account/bilogin")?>">Мои черновики</a></li>
     <?php else: ?>
        <li><a href="<?=Yii::app()->createUrl("/user/account/bilogin")?>">Войти</a></li>
        <li><a href="<?=Yii::app()->createUrl("/user/account/bireg")?>">Регистрация</a></li>
     <?php endif; ?>
        <li>&nbsp;</li>
        <li class="header">Корзина</li>
        <li><?=CHtml::link("Содержимое корзины: <strong id='cart_count'>".ShoppingCart::getItemsCount()."</strong>",Yii::app()->createUrl('/shop/map/basket'))?></li>
        <li><?=CHtml::link("Отложенные товары: <strong id='delay_count'>".ShoppingCart::getDelayItemsCount()."</strong>",Yii::app()->createUrl('/shop/map/delayedItems'))?></li>
        <?php if(Yii::app()->user->biuser): ?>
      <li>&nbsp;</li>
        <li class="header">Заказы</li>
        <li><?=CHtml::link("Активные: <strong>".ShoppingCart::getActiveOrdersCount()."</strong>",Yii::app()->createUrl('/shop/map/order/active'))?></li>
        <li><?=CHtml::link("Выполненные: <strong>".ShoppingCart::getFinishedOrdersCount()."</strong>",Yii::app()->createUrl('/shop/map/order/finished'))?></li>
        <li><?=CHtml::link("Отмененные: <strong>".ShoppingCart::getCanceledOrdersCount()."</strong>",Yii::app()->createUrl('/shop/map/order/canceled'))?></li>
        <?php endif; ?>
    </ul>
</div>