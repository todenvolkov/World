<?php
class LastPostsWidget extends YWidget
{
    public $limit = 10;
    public $type = Post::TYPE_POST;

    public function run()
    {
        $dependency = new CDbCacheDependency('SELECT MAX(update_date) FROM '.Post::tableName());
        $posts = Post::model()->published()->public()->cache($this->cacheTime,$dependency)->findAll(array(
            'condition'=>'post_type=:type',
            'params'=>array('type'=>$this->type),
            'limit'=>$this->limit,
            'order'=>'create_date DESC, id DESC',
        ));

        $this->render('lastposts', array(
            'posts' =>$posts
        ));
    }
}