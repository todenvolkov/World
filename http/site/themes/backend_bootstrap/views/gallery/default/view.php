<?php $this->pageTitle = Yii::t('gallery', 'Галереи изображений'); ?>

<?php
$this->breadcrumbs = array(
    Yii::t('feedback', 'Сообщения с сайта') => array('admin'),
    $model->name,
);

$this->menu = array(
    array('label' => Yii::t('feedback', 'Управление галереями'), 'url' => array('admin')),
    array('label' => Yii::t('feedback', 'Добавить галерею'), 'url' => array('create')),
    array('label' => Yii::t('feedback', 'Редактировать данною галерею'), 'url' => array('update', 'id' => $model->id)),
    array('label' => Yii::t('feedback', 'Удалить данную галерею'), 'url' => '#', 'linkOptions' => array('submit' => array('delete', 'id' => $model->id), 'confirm' => 'Подтверждаете удаление галереи ?')),
);
?>

<h1><?php echo Yii::t('feedback', 'Просмотр сообщения с сайта');?>
    #<?php echo $model->id; ?></h1>

<?php $this->widget('bootstrap.widgets.BootDetailView', array(
                                                    'data' => $model,
                                                    'attributes' => array(
                                                        'id',
                                                        'name',
                                                        'description',
                                                        array(
                                                            'name' => 'status',
                                                            'value' => $model->getStatus()
                                                        ),
                                                    ),
                                               )); ?>
