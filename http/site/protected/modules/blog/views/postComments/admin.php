<?php
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

<h1><?php echo $this->module->getName(); ?></h1>

<?php echo CHtml::link(Yii::t('blog', 'Поиск'), '#', array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
    <?php $this->renderPartial('_search',array('model'=>$model)); ?>
</div><!-- search-form -->

<?php
    $this->widget('YCustomGridView', array(
        'id'=>'blog-grid',
        'dataProvider'=>$model->search(),
        'itemsCssClass' => ' table table-condensed',
        'columns'=>array(
            'id',
            array(
                'name'  => 'name',
                'type'  => 'raw',
                'value' => 'CHtml::link($data->name,array("/blog/postComments/update/","id" => $data->id))'
            ),
            array(
                'name' => 'status',
                'type' => 'raw',
                'value' => '$this->grid->returnBootstrapStatusHtml($data)',
                'htmlOptions' => array('style'=>'width:40px; text-align:center;'),
            ),
            array(
                'name'=>'user_id',
                'type' => 'raw',
                'value' => 'CHtml::link($data->author->getFullName(),array("/user/default/view/","id" => $data->author->id))'
            ),
			array(
                'name'=>'text',
                'value'=>'$data->text',
            ),
            array(
                'name'=>'creation_date',
                'value'=>'$data->creation_date',
            ),
            array('class' => 'bootstrap.widgets.BootButtonColumn'),
        ),
    ));
?>
