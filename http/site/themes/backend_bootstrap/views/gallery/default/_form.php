    <?php $form = $this->beginWidget('CActiveForm', array(
                                                         'id' => 'gallery-form'
                                                    )); ?>
    <fieldset class="inline">
        <div class="alert alert-info"><?php echo Yii::t('page', 'Поля, отмеченные * обязательны для заполнения')?></div>

        <?php echo $form->errorSummary($model); ?>

        <div class="row-fluid control-group">
            <?php if(!$model->isNewRecord): ?>
            <div class="span2">
                <?php echo $form->labelEx($model, 'cover_id'); ?>
                <a class="thumbnail" data-toggle="modal" href="#selector_modal_window" rel="tooltip" data-original-title="Выбрать обложку"><img id="coverImg" src="<?=$model->cover_id?"/timthumb.php?src=".urlencode($model->cover->file)."&w=260":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQQAAACgBAMAAAAY3A91AAAAG1BMVEXMzMyWlpacnJzFxcWxsbG+vr6jo6Oqqqq3t7ejTsAZAAABPElEQVR4nO3XMVICQRCF4dYVIaRBkJC5AXsDqPIAbHkBTYjhBhJ4b7tnlipzg4dV/5dMuG9ne6d7zAAAAAAAAAAAAAAAwP/06O7bX6vAQzx6GWsf61wX4SXWgzbCItaii5A14EebuK4WzD7dz/bkvlYFMBvy9WMzVroI9emD8DNYfoNXu7jvdRG6KMT8IXa6CLkD37kTQlEHb9JqrPV4klaj2SyPJf9SRrAaQZog61Fbja1RL7URniPChzZCnI/KszEJh7abOJu0J9M9/BFdPReUXapWo7ge76BHXGun3CgjxPz6Lp1ec35fZIyjLsI0r1PXnORl+uxRvbRRHbJHTdvNUqTkxDRrN0uNSZuYim50K2N/uOhu1j52yUEbYWttZhBG2FvrVaIIZezTnS4CAAAAAAAAAAAAAAB/8QMd3R8Ly/oOewAAAABJRU5ErkJggg=="?>" alt="">
                    </a>
                <?php echo $form->hiddenField($model, 'cover_id');?>
            </div>
            <?php endif; ?>
        </div>

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
            <div class="span2">
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