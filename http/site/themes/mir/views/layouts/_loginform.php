<div style="display:none">
    <div id="login">
        <div class="login-reg">
            <ul>
                <li>Вход</li>
                <li>регистрация</li>
            </ul>
        </div>
        <div class="form-wrap">
            <?php

            $model = new LoginForm;

            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'login-form',
                'enableClientValidation' => true,
                'action' => Yii::app()->createUrl('/login'),
            ));?>
                <div class="form-row">
                    <?php echo $form->textField($model, 'email', array('placeholder'=>'Введите ваш email')) ?>
                    <!-- <input type="text" name="email" placeholder="Введите ваш email" /> -->
                </div>
                <div class="form-row">
                    <?php echo $form->passwordField($model, 'password', array('placeholder'=>'Пароль')) ?>
                    <!-- <input type="password" name="password" placeholder="Пароль" /> -->
                </div>
                <div class="form-row login-btn">
                    <ul>
                        <li><?php echo CHtml::link(Yii::t('user', "Зарегистрироваться"), array('/user/account/registration')); ?></li>
                        <li><?php echo CHtml::link(Yii::t('user', "Забыли пароль?"), array('/user/account/recovery')) ?></li>
                    </ul>
                    <?php echo CHtml::submitButton('Войти'); ?>
                </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>