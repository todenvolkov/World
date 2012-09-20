<ul>
    <?php foreach ($posts as $post): 
	$img = Post::getImages($post->content,0);
	?>
    <li style="background:url('/timthumb.php?src=<?=urlencode($img[0])?>&amp;w=55&amp;h=55&amp;q=95') no-repeat left top; min-height:55px;">
        <ul class="clearfix">
            <li class="first"><?=$post->create_date?></li>
            <li><a href="<?=Yii::app()->createUrl('blog/'.$post->blog->slug)?>"><?=$post->blog->cat->name?></a></li>
            <li><a href="" class="like">0</a></li>
            <li class="last"><a href="<?=Yii::app()->createUrl("/post/".$post->slug.".html#comments")?>" class="comments"><?=$post->commentsCount?></a></li>
        </ul>
        <h3><a href="<?=Yii::app()->createUrl("/post/".$post->slug.".html")?>"><?=$post->title?></a></h3>
        <?=strip_tags($post->quote)?>
    <?php //echo CHtml::link($post->title, array('/blog/post/show/', 'slug' => $post->slug));?></li>
    <?php endforeach;?>
</ul>