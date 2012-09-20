<?php $this->pageTitle = Yii::t('blog', 'Блоги'); ?>

<?php
//@formatter:off
$this->breadcrumbs = array(
    Yii::t('blog', 'Блоги')=>array('admin'),
    Yii::t('blog', 'Управление'),
);

$this->menu = array(
    array('label'=> Yii::t('blog', 'Блоги')),
    array('label'=>Yii::t('blog','Список блогов'), 'url'=>array('index')),
    array('label'=>Yii::t('blog','Добавить блог'), 'url'=>array('create')),

    array('label'=> Yii::t('blog', 'Записи')),
    array('label'=>Yii::t('blog', 'Список записей'), 'url'=>array('postAdmin/index')),
    array('label'=>Yii::t('blog', 'Добавить запись'), 'url'=>array('postAdmin/create')),
    array('label'=>Yii::t('blog', 'Управление записями'), 'url'=>array('postAdmin/admin')),
);
//@formatter:on
Yii::app()->clientScript->registerScript('search', "
    $('.search-button').click(function() {
        $('.search-form').toggle();
        return false;
    });
    $('.search-form form').submit(function() {
        $.fn.yiiGridView.update('blog-grid', {
            data: $(this).serialize()
        });
        return false;
    });
");
?>
<div class="page-header"><h1><?php echo $this->module->getName()?> <small><?php echo Yii::t('blog', 'Управление');; ?></small></h1></div>

<?php echo CHtml::link(Yii::t('blog', 'Поиск'), '#', array('class' => 'search-button')); ?>
<div class="search-form" style="display:none">
    <?php $this->renderPartial('_search', array('model' => $model)); ?>
</div><!-- search-form -->

<?php
        $dp = $model->search();
        $dp->criteria->order = 'name ASC';
        $this->widget('YCustomGridView', array(
        'id' => 'blog-grid',
        'dataProvider' => $dp,
        'itemsCssClass' => ' table table-condensed',
        'columns' => array(
            array(
                'name' => 'id',
                'header' => '№',
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
