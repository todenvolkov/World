<?php
$this->pageTitle = 'Блоги';
$this->breadcrumbs = array(Yii::t('blog', 'Блоги'));
?>

<h2>Направления</h2>
<?php $this->widget('application.modules.menu.widgets.MirMenuWidget', array('id'=>'parent clearfix','name'=>'post-menu')); ?>

<div class="info clearfix staticpage">
        <div class="content">
            <p>
<?php $this->widget('zii.widgets.CListView', array(
                                                  'dataProvider' => $dataProvider,
                                                  'itemView' => '_view',
                                             )); ?>
			</p>
        </div>
</div>