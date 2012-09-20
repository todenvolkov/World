<?php
$this->pageTitle = Yii::t('user', 'Авторизация');
$this->breadcrumbs = array('Авторизация');
?>

<?php Yii::app()->clientScript->registerScriptFile('http://connect.facebook.net/ru_RU/all.js'); ?>

<h2>Авторизация</h2>

<?php $this->widget('application.modules.yupe.widgets.YFlashMessages'); ?>

<div class="ask-question clearfix">
    <div class="form-wrapper" style="width:280px">
		<form name="form_auth" method="post" target="_top" action="/auth.php">
			<input type="hidden" name="AUTH_FORM" value="Y">
			<input type="hidden" name="TYPE" value="AUTH">
			<input type="hidden" name="backurl" value="<?=$_REQUEST["backurl"]?>">
									<p>&nbsp;</p>
			<div class="field">
				<label class="field-title" style="font:11px Tahoma, Geneva, sans-serif ">Логин</label>
				<div class="form-input"><input type="text" name="USER_LOGIN" maxlength="50" value="hades" class="input-field"></div>
			</div>	
			<p>&nbsp;</p>
			<div class="field">
				<label class="field-title">Пароль</label>
				<div class="form-input"><input type="password" name="USER_PASSWORD" maxlength="50" class="input-field"></div>
			</div>
                       							<p>&nbsp;</p>
			<div class="field field-option" style="visibility:hidden; display:none">
				<input type="checkbox" id="USER_REMEMBER" name="USER_REMEMBER" value="Y" checked="checked"><label for="USER_REMEMBER">&nbsp;Запомнить меня</label>
			</div>
						<p>&nbsp;</p>
			<div class="field field-button">
				<input type="submit" class="more" name="Login" value="Войти">
			</div>
		</form>
	</div>
	</div>
<script type="text/javascript">
try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
</script>
