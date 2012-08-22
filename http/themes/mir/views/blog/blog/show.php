<?php $this->pageTitle = $blog->name;?>
<?php $this->description = $blog->description;?>

<?php
$this->breadcrumbs = array(
    'Блоги' => array('/blogs/'),
    $blog->name,
);
$title = $blog->name;
if(strpos($title," ")!==false){
	$title = wordwrap($title,(strlen($title)/2),"|",true);
	$title = explode("|",$title,2);
	$title = str_replace("|"," ",$title);
	$title = $title[0]." <strong>".$title[1]."</strong>";
}
?>
<h2><?=$title?></h2>
<?php $this->widget('application.modules.menu.widgets.MirMenuWidget', array('id'=>'parent clearfix','name'=>'post-menu')); ?>
<!-- <ul class="parent clearfix" style="width: 457px; ">
    <li class="first"><a href="#" class="active">посты</a></li>
    <li><a href="#">события</a></li>
    <li><a href="#">полезное</a></li>
    <li><a href="#">портфолио</a></li>
    <li><a href="#">faq</a></li>
    <li class="last"><a href="#">блоггеры</a></li>
</ul> -->
<div class="info clearfix staticpage">
        <div class="content">
            <p>
<?php if(count($posts)):?>
    <?php foreach ($posts as $post):?>
         <?php $this->renderPartial('//blog/post/_view',array('data' => $post));?>
    <?php endforeach;?>
<?php endif;?>
			</p>
        </div>
</div>