<?
$user_login = $USER->GetLogin();
$user_name = $USER->GetFullName();


CModule::IncludeModule("sale");

$in_basket = CSaleBasket::GetList(false, array("FUSER_ID" => CSaleBasket::GetBasketUserID(),"LID" => SITE_ID,"ORDER_ID" => "NULL","DELAY"=>"N","CAN_BUY"=>"Y"),false,false,array("ID" ))->SelectedRowsCount();
$out_basket = CSaleBasket::GetList(false, array("FUSER_ID" => CSaleBasket::GetBasketUserID(),"LID" => SITE_ID,"ORDER_ID" => "NULL","DELAY"=>"Y","CAN_BUY"=>"Y"),false,false,array("ID" ))->SelectedRowsCount();

?>
<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH.'/order_header.php',Array(),Array("MODE"=>"php")); ?>
<?if ($NO_LEFT!=1):?>
<tbody>
<tr bgcolor="#FFFFFF">
	<td valign="top" class="C10_Left_Column">
		<table width="240" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="Ltop2">
					<p>&nbsp;</p>
					<p class="Header0">Личная информация</p>
					<p>&nbsp;</p>
					<ul>
					<?if (strlen($user_login) > 0): ?>
   						<li><a href="/personal/profile/">Изменить личные данные</a></li>
					<?else:?>
       						<li><a href="/login/?register=yes">Регистрация</a></li>
						<li><a href="/personal/profile/">Войти на сайт</a></li>
						<li><a href="/personal/profile/?forgot_password=yes">Забыли пароль?</a></li>
					<? endif ?>
					</ul>
					<p>&nbsp;</p>
					<p class="Header0">Корзина</p>
					<p>&nbsp;</p>
					<ul>
						<li><a href="/personal/cart/">Содержимое корзины</a> (<?=$in_basket?>)</li>
						<li><a href="/personal/cart/?delay=1">Отложенные товары</a> (<?=$out_basket?>)</li>
					</ul>
					<p>&nbsp;</p>
					<p class="Header0">Заказы</p>
					<p>&nbsp;</p>
					<ul>
						<!--
						<li><a href="/personal/order/">Ознакомиться с состоянием заказов</a></li>
						<li><a href="/personal/order/?filter_history=Y">Посмотреть историю заказов</a></li>
						-->
						<li><a href="/personal/order/?filter_history=N">Активные</a></li>
						<li><a href="/personal/order/?filter_status=F&filter_history=Y">Выполненные</a></li>
						<li><a href="/personal/order/?filter_canceled=Y&filter_history=Y">Отмененные</a></li>
					</ul>
					<p>&nbsp;</p>
					<p class="Header0">Подписка</p>
					<p>&nbsp;</p>
					<ul>
						<li><a href="/personal/subscribe/">Изменить подписку</a></li>
					</ul>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
				</td>
			</tr>
		</table>
	</td>
	<td valign="top" class="C10_Right_Column Lleft" colspan="2" width="750">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td class="Ltop2" width="750" valign="top" colspan="2">
<? endif; ?>