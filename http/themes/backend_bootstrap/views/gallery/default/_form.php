    <?php $form = $this->beginWidget('CActiveForm', array(
                                                         'id' => 'gallery-form'
                                                    )); ?>
    <fieldset class="inline">
        <div class="alert alert-info"><?php echo Yii::t('page', 'Поля, отмеченные * обязательны для заполнения')?></div>

        <?php echo $form->errorSummary($model); ?>
		<?php if(!$model->isNewRecord): ?>
        <div class="row-fluid control-group">
       		<div class="span2">
                <img src="http://placehold.it/260x180" alt="">
            </div>
            <div class="span4">
            	<a class="btn" data-toggle="modal" href="#selector_modal_window" >Выбрать обложку</a>
            </div>
        </div>
        <?php endif; ?>
        <div class="row-fluid control-group  <?php echo $model-> hasErrors('name')?'error':'' ?>">
            <div class="span7">
                <?php echo $form->labelEx($model, 'name'); ?>
                <?php echo $form->textField($model, 'name', array('size' => 60, 'maxlength' => 100)); ?>
            </div>
            <div class="span5">
                <?php echo $form->error($model, 'name'); ?>
            </div>
        </div>

        <div class="row-fluid control-group  <?php echo $model-> hasErrors('description')?'error':'' ?>">
            <div class="span7">
                <?php echo $form->labelEx($model, 'description'); ?>
                <?php echo $form->textArea($model, 'description', array('rows' => 5, 'cols' => 6)); ?>
            </div>
            <div class="span5">
                <?php echo $form->error($model, 'description'); ?>
            </div>
        </div>

        <div class="row-fluid control-group  <?php echo $model-> hasErrors('status')?'error':'' ?>">
            <div class="span7">
                <?php echo $form->labelEx($model, 'status'); ?>
                <?php echo $form->dropDownList($model, 'status', $model->getStatusList()); ?>
            </div>
            <div class="span5">
                <?php echo $form->error($model, 'status'); ?>
            </div>
        </div>

       
       <?php echo CHtml::submitButton($model->isNewRecord
                                               ? Yii::t('feedback', 'Добавить галлерею')
                                               : Yii::t('feedback', 'Сохранить изменения'),
                                               array('class' => 'btn btn-primary',)); ?>
    </fieldset>
    <?php $this->endWidget(); ?>