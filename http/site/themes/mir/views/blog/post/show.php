<?php $this->pageTitle = $post->title;?>
<?php $this->description = $post->description;?>
<?php $this->keywords = $post->keywords;?>

<?php
$this->breadcrumbs = array(
    'Блоги' => array('/blogs/'),
    CHtml::encode($post->blog->name) => array('/blog/blog/show/','slug' => $post->blog->slug),
    CHtml::encode($post->title)
);
$title = $post->title;
if(strpos($title," ")!==false){
	$title = wordwrap($title,(strlen($title)/2),"|",true);
	$title = explode("|",$title,2);
	$title = str_replace("|"," ",$title);
	$title = $title[0]." <strong>".$title[1]."</strong>";
}
?>
<h2><?=$title?></h2>
<?php $this->widget('application.modules.menu.widgets.MirMenuWidget', array('id'=>'parent clearfix','name'=>'post-menu')); ?>
<div class="info clearfix staticpage">
        <div class="content">
            <p><?php echo $post->content; ?></p>
        </div>
        <br/>
        <div class="author">
            Опубликовал <b><?php echo CHtml::link($post->createUser->nick_name,array('/user/people/userInfo','username' => $post->createUser->nick_name));?></b>
            в блоге "<?php echo CHtml::link($post->blog->name,array('/blog/blog/show/','slug' => $post->blog->slug))?>"
            дата: <?php echo $post->publish_date; ?>
        </div>
</div>

<div class="sort">
    <p>Теги:
        <?php foreach($post->getTags() as $tag): ?>
            <?php echo CHtml::link(CHtml::encode($tag),array('/posts/','tag' => CHtml::encode($tag)));?>
            <?php endforeach;?>
    </p>
</div>

    <br/><br/><br/>

<?php $this->widget('application.modules.blog.widgets.AutobrowseCommentsWidget', array('post_id' => $post->id, 'show_form'=>true)); ?>


