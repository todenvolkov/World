<table width="950" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" align="left">
<thead>
<tr bgcolor="#FFFFFF">
	<td height="50" width="250">
		<p>
		<?if ($INDEX>0):?>
		<img src="/bitrix/templates/mir/images/Header_Order.png" height="25" width="150">
		<? else: ?>
		<img src="/bitrix/templates/mir/images/Header_PersonCabinet.png" width="189" height="25" />
		<? endif; ?>
		</p>
	</td>
	<td width="250" class="C10">
		<p class="Header"><strong>
		<?if ($INDEX==1):?>
		Кампания<br>наружной рекламы
		<? elseif ($INDEX==2): ?>
		Полноцветная широкоформатная печать
		<? elseif ($INDEX==3): ?>
		Дизайн-Проект
		<? elseif ($INDEX==4): ?>
		Производство рекламных конструкций
		<? else: ?>
			<?if (strlen( $USER->GetLogin() ) > 0): ?>
				<?=$USER->GetLogin();?><br><?=$USER->GetFullName();?>
			<?else:?>
				Гость<br>Компании МИР
			<? endif ?>
		<? endif; ?>
		</strong></p>	
	</td>
	<td width="430" align="left" valign="top" class="C10">
		<p>Заказать:</p>
		<?if ($INDEX!=1):?>
		<p><a href="/catalog/">Кампанию наружной рекламы</a></p>
		<? else: ?>
		<p>Кампанию наружной рекламы</p>	
		<? endif; ?>
		<?if ($INDEX!=2):?>
		<p><a href="/personal/pprint/">Полноцветную широкоформатную печать</a></p>
		<? else: ?>
		<p>Полноцветную широкоформатную печать</p>	
		<? endif; ?>
		<?if ($INDEX!=3):?>
		<p><a href="/personal/pdesign">Дизайн-Проект</a></p>
		<? else: ?>
		<p>Дизайн-Проект</p>	
		<? endif; ?>
		<?if ($INDEX!=4):?>
		<p><a href="/personal/pmake">Производство рекламных конструкций</a></p>
		<? else: ?>
		<p>Производство рекламных конструкций</p>	
		<? endif; ?>
	</td>
</tr>
</thead>
