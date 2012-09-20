<?php
$this->pageTitle = Yii::t('user', 'Авторизация');
$this->breadcrumbs = array('Авторизация');
?>

<h2>Регистрация</h2>

<?php $this->widget('application.modules.yupe.widgets.YFlashMessages'); ?>

<div class="ask-question clearfix">
    <div class="form-wrapper" style="width:280px">
		<form method="post" action="/auth.php?register=yes" name="bform">
            <input type="hidden" name="backurl" value="<?=$_REQUEST["backurl"]?>">
            <input type="hidden" name="AUTH_FORM" value="Y">
            <input type="hidden" name="TYPE" value="REGISTRATION">
                <div class="field">
                    <label class="field-title">Имя</label>
                    <div class="form-input"><input type="text" name="USER_NAME" maxlength="50" value=""></div>
                </div>
                <p>&nbsp;</p>
                <div class="field">
                    <label class="field-title">Фамилия</label>
                    <div class="form-input"><input type="text" name="USER_LAST_NAME" maxlength="50" value=""></div>
                </div>
                <p>&nbsp;</p>
                <div class="field">
                    <label class="field-title">Логин<span class="starrequired">*</span></label>
                    <div class="form-input"><input type="text" name="USER_LOGIN" maxlength="50" value=""></div>
                    <div class="description">— Логин должен быть не менее 3 символов.</div>
                </div>
                <p>&nbsp;</p>
                <div class="field">
                    <label class="field-title">Пароль<span class="starrequired">*</span></label>
                    <div class="form-input"><input type="password" name="USER_PASSWORD" maxlength="50" value=""></div>
                    <div class="description">— Пароль должен быть не менее 6 символов длиной.</div>
                </div>
                <div class="field">
                    <label class="field-title">Подтверждение пароля<span class="starrequired">*</span></label>
                    <div class="form-input"><input type="password" name="USER_CONFIRM_PASSWORD" maxlength="50" value=""></div>
                </div>
                <p>&nbsp;</p>
                <div class="field">
                    <label class="field-title">E-Mail<span class="starrequired">*</span></label>
                    <div class="form-input"><input type="text" name="USER_EMAIL" maxlength="255" value=""></div>
                </div>
                <p>&nbsp;</p>
                <div class="field">
                    <label class="field-title">Код на картинке<span class="starrequired">*</span></label>
                    <div class="form-input"><input type="text" name="captcha_word" maxlength="50" value=""></div>
                    <p style="clear: left;"><input type="hidden" name="captcha_sid" value="29c7e30cccbc36b7200d707f0f3c54f9">
                    <img src="/bitrix/tools/captcha.php?captcha_sid=29c7e30cccbc36b7200d707f0f3c54f9" width="180" height="40" alt="CAPTCHA"></p>
                </div>
                    <p>&nbsp;</p>
            <div class="field field-button"><input type="submit" class="input-submit" name="Register" value="Зарегистрироваться"></div>
        
        
        </form>
	</div>
	</div>
<script type="text/javascript">
try{document.form_auth.USER_PASSWORD.focus();}catch(e){}
</script>
