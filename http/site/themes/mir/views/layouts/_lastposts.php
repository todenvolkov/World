<div class="post-block clearfix">
    <div class="block-one">
        <h2>посты из <strong>блога</strong></h2>
        <?php $this->widget('application.modules.blog.widgets.LastPostsWidget', array('limit'=>3, 'type'=>Post::TYPE_POST) ); ?>
        <a href="<?=Yii::app()->createUrl("/blog/type/posts")?>" class="more-post">Больше постов</a> </div>
    <div class="block-two">
        <h2>события из <strong>блога</strong></h2>
        <?php $this->widget('application.modules.blog.widgets.LastPostsWidget', array('limit'=>3, 'type'=>Post::TYPE_EVENT) ); ?>
        <a href="<?=Yii::app()->createUrl("/blog/type/events")?>" class="more-post">Больше событий</a> </div>
    <a href="<?=Yii::app()->createUrl("feedback/subscribe")?>" class="more subscribe">подписаться</a>
</div>