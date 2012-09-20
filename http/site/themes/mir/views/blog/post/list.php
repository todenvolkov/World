<?php $this->pageTitle = 'Список записей'; ?>

<h2>Записи с меткой <strong><?php echo $tag;?></strong></h2>
<?php $this->widget('application.modules.menu.widgets.MirMenuWidget', array('id'=>'parent clearfix','name'=>'post-menu')); ?>
<div class="info clearfix staticpage">
        <div class="content">
            <p>
<?php foreach($posts as $post):?>
    <?php $this->renderPartial('_view',array('data' => $post));?>
<?php endforeach;?></p>
        </div>
</div>




