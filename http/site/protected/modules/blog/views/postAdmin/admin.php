<?php $this->pageTitle = Yii::t('blog', 'Посты'); ?>
<?php
//@formatter:off
    $this->breadcrumbs = array(
        Yii::t('blog', 'Блоги')=>array('blogAdmin/admin'),
        Yii::t('blog', 'Записи')=>array('admin'),
        Yii::t('blog', 'Управление'),
    );

    $this->menu = array(
        array('label'=> Yii::t('blog', 'Блоги')),
        array('icon' => 'list','label'=>Yii::t('blog','Список блогов'), 'url'=>array('blogAdmin/index')),
        array('icon' => 'file','label'=>Yii::t('blog','Добавить блог'), 'url'=>array('blogAdmin/create')),

        array('label'=> Yii::t('blog', 'Записи')),
        array('icon' => 'list','label'=>Yii::t('blog', 'Список записей'), 'url'=>array('index')),
        array('icon' => 'file','label'=>Yii::t('blog', 'Добавить запись'), 'url'=>array('create')),
        array('icon' => 'list-alt','label'=>Yii::t('blog', 'Управление записями'), 'url'=>array('admin')),

        array('label'=> Yii::t('blog', 'События')),
        array('icon' => 'list','label'=>Yii::t('blog', 'Список событий'), 'url'=>array('index')),
        array('icon' => 'file','label'=>Yii::t('blog', 'Добавить событие'), 'url'=>array('create')),
        array('icon' => 'list-alt','label'=>Yii::t('blog', 'Управление событиями'), 'url'=>array('admin')),
    );
//@formatter:on
    Yii::app()->clientScript->registerScript('search', "
        $('.search-button').click(function() {
            $('.search-form').toggle();
            return false;
        });
        $('.search-form form').submit(function() {
            $.fn.yiiGridView.update('post-grid', {
                data: $(this).serialize()
            });
            return false;
        });
    ");
?>

<div class="page-header"><h1>Посты <small><?php echo Yii::t('blog', 'Управление');; ?></small></h1></div>

<?php echo CHtml::link(Yii::t('blog', 'Поиск'), '#', array('class' => 'search-button')); ?>
<div class="search-form" style="display:none">
    <?php $this->renderPartial('_search', array('model' => $model)); ?>
</div><!-- search-form -->

<?php
    $this->widget('YCustomGridView', array(
        'id'=>'post-grid',
        'dataProvider'=>$model->search(),
        'itemsCssClass' => ' table table-condensed',
        'columns'=>array(
            'id',
            array(
                'name'  => 'title',
                'type'  => 'raw',
                'value' => 'CHtml::link($data->title,array("/blog/postAdmin/update/","id" => $data->id))'
            ),
            array(
                'name'  => 'blog_id',
                'type'  => 'raw',
                'value' => 'CHtml::link($data->blog->name,array("/blog/blogAdmin/view/","id" => $data->blog->id))'
            ),
            'slug',
            array(
                'name'=>'status',
                'type'=>'raw',
                'value'=>'$this->grid->returnBootstrapStatusHtml($data)',
                'htmlOptions' => array('style'=>'width:40px; text-align:center;'),
            ),
            array(
                'name'=>'create_user_id',
                'type' => 'raw',
                'value' => 'CHtml::link($data->createUser->getFullName(),array("/user/default/view/","id" => $data->createUser->id))'
            ),
            array(
                'name'=>'update_user_id',
                'type' => 'raw',
                'value' => 'CHtml::link($data->updateUser->getFullName(),array("/user/default/view/","id" => $data->updateUser->id))'
            ),
            array(
                'name'=>'create_date',
                'value'=>'$data->create_date',
            ),
            array(
                'name'=>'update_date',
                'value'=>'$data->update_date',
            ),
            array('class' => 'bootstrap.widgets.BootButtonColumn'),
        ),
    ));
?>
