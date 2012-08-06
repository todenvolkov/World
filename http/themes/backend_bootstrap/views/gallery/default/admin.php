<?php $this->pageTitle = Yii::t('gallery', 'Галереи изображений'); ?>

<?php
//@formatter:off
$this->breadcrumbs = array(
    $this->getModule('category')->getCategory() => array(''),
    Yii::t('gallery', 'Галереи изображений') => array('admin'),
    Yii::t('gallery', 'Управление галереями'),
);

$this->menu = array(
    array('label' => Yii::t('gallery', 'Список галерей'), 'url' => array('index')),
    array('label' => Yii::t('gallery', 'Добавить галерею'), 'url' => array('create')),
);
//@formatter:on
Yii::app()->clientScript->registerScript('search', "
    $('.search-button').click(function() {
        $('.search-form').toggle();
        return false;
    });
    $('.search-form form').submit(function() {
        $.fn.yiiGridView.update('feed-back-grid', {
            data: $(this).serialize()
        });
        return false;
    });
");
?>
<div class="page-header"><h1><?php echo $this->module->getName()?> <small><?php echo Yii::t('gallery', 'Управление');; ?></small></h1></div>

<?php echo CHtml::link(Yii::t('gallery', 'Поиск галерей'), '#', array('class' => 'search-button')); ?>
<div class="search-form" style="display:none">
    <?php $this->renderPartial('_search', array('model' => $model)); ?>
</div><!-- search-form -->

<?php
        $dp = $model->search();
        $dp->criteria->order = 'status ASC, id DESC';
        $this->widget('YCustomGridView', array(
        'statusField' => 'status',
        'id' => 'feed-back-grid',
        'dataProvider' => $dp,
        'itemsCssClass' => ' table table-condensed',
        'columns' => array(
            array(
                'name' => 'id',
                'header' => '№',
            ),
            array(
                'name' => 'status',
                'type' => 'raw',
                'value' => "'<span class=\"label label-'.(\$data->status?((\$data->status==1)?'warning':((\$data->status==3)?'success':'default')):'info').'\">'.\$data-> getStatus().'</span>'",
                'filter' => CHtml::activeDropDownList($model, 'status', $model->getStatusList()),
            ),
            array(
                'name'  => 'name',
				'type' => 'raw',
                'value' => 'CHtml::link($data->name, array("/gallery/default/update/", "id" => $data->id))'
            ),
            

            array(
                'name' => 'description',
                'type' => 'raw',
                'value' => 'CHtml::link($data->description, array("/gallery/default/update/", "id" => $data->id))',
            ),
            array('class' => 'bootstrap.widgets.BootButtonColumn'),
        ),
    ));
 ?>
