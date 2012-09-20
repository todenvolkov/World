<?php
$this->pageTitle = Yii::t('user', 'Авторизация');
$this->breadcrumbs = array('Авторизация');
?>


<h2>Авторизация</h2>

<?php $this->widget('application.modules.yupe.widgets.YFlashMessages'); ?>
<div class="ask-question clearfix">
    <div class="form-wrapper" style="width:280px">
    
        <?php  $form = $this->beginWidget('CActiveForm', array(
                                                             'id' => 'login-form',
    'htmlOptions'=>array("class"=>"clearfix jqtransform"),
    'enableClientValidation' => true
                                                        ));?>
    
        <?php echo $form->errorSummary($model); ?>
        <div class="form-row placeholder inFields">
            <label for="Login" ><span>*</span> Логин</label>
            <input name="LoginForm[email]" id="Login" type="text" style="width:252px" value="">
        </div>
        <div class="form-row placeholder inFields">
            <label for="Password" class="required"><span>*</span> Пароль</label>
            <input name="LoginForm[password]" id="Password" type="password" style="width:252px" value="">
        </div>

        <div class="form-row placeholder inFields">
            <p class="hint" style="text-align:center">
        <?php echo CHtml::link(Yii::t('user', "Регистрация"), array('/user/account/registration')); ?>
        <br><?php echo CHtml::link(Yii::t('user', "Восстановление пароля"), array('/user/account/recovery')) ?>
    		</p>
        </div>
            
       <!-- <div class="form-row placeholder inFields">
        
            <?php echo $form->labelEx($model, 'email'); ?>
            <?php echo $form->textField($model, 'email') ?>
            <?php echo $form->error($model, 'email'); ?>
        </div>
    
        <div class="form-row placeholder inFields">
            <?php echo $form->labelEx($model, 'password'); ?>
            <?php echo $form->passwordField($model, 'password') ?>
            <?php echo $form->error($model, 'password'); ?>
        </div>
    
        <div class="row">
            <p class="hint">
                <?php echo CHtml::link(Yii::t('user', "Регистрация"), array('/user/account/registration')); ?>
                | <?php echo CHtml::link(Yii::t('user', "Восстановление пароля"), array('/user/account/recovery')) ?>
            </p>
        </div> -->
    
        <div class="row submit">
            <?php echo CHtml::submitButton('Войти',array("class"=>"more")); ?>
        </div>
    
        <?php $this->endWidget(); ?>
    </div><!-- form -->
</div>
