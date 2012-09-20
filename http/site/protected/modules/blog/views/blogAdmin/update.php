<?php
    $this->breadcrumbs=array(
        Yii::t('blog', 'Блоги')=>array('admin'),
        $model->name=>array('view', 'id'=>$model->id),
        Yii::t('blog', 'Редактирование'),
    );

    $this->menu=array(
        array('label' => Yii::t('blog', 'Список блогов'), 'url'=>array('index')),
        array('label' => Yii::t('blog', 'Добавить блог'), 'url'=>array('create')),
        array('label' => Yii::t('blog', 'Просмотр блога'), 'url'=>array('view', 'id'=>$model->id)),
        array('label' => Yii::t('blog', 'Управление блогами'), 'url'=>array('admin')),
    );
?>
<div class="page-header">
    <h1><?php echo Yii::t('blog', 'Редактирование блога'); ?>
        <br /><small>&laquo;<?php echo $model->name; ?>&raquo;</small>
    </h1>
</div>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>