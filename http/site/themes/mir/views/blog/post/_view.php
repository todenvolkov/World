<div style="display:inline-block; margin-bottom:65px;">
    <div style="display:inline-block; margin-bottom:25px;">
    <?php
    $img = Post::getImages($data->content,0,"/site/themes/mir/css/images/photo.jpg");
	?>
        <div class="photo" style="background: white url('/timthumb.php?src=<?=urlencode($img[0])?>&amp;w=441&amp;h=283&amp;q=95') no-repeat center center;"> Photo </div>
        <div class="post">
          <h2><?=CHtml::encode($data->title)?></h2>
          <?php $read = CHtml::link("Читать дальше", array('/blog/post/show/', 'slug' => $data->slug)); ?>
          <?=$read?>
          <p><?php echo strip_tags($data->quote); ?></p>
          <?=$read?> 
        </div>
    </div>
    <!-- .BLOG-POST ENDS -->
          
    <div class="post-and-tags">
    <ul class="post-detail clearfix">
      <li><a href="#" class="active"><?=$data->createUser->last_name?> <?=$data->createUser->first_name?></a></li>
      <li><a href="#">Должность</a></li>
      <li><?=strftime("%d %B",strtotime($data->create_date))?></li>
      <li><?=strftime("%H:%M:%S",strtotime($data->create_date))?></li>
      <li><a href="#" class="like">0</a></li>
      <li><a href="/post/<?=$data->slug?>.html#comments" class="comments"><?=$data->commentsCount?></a></li>
    </ul>
    <div class="tags">
      <p>Теги: <?php foreach($data->getTags() as $tag): ?>
            <?php echo CHtml::link(CHtml::encode($tag),array('/posts/','tag' => CHtml::encode($tag)));?>
            <?php endforeach;?></p>
    </div>
    </div>
    <!-- .POST-AND-TAGS ENDS-->
</div>