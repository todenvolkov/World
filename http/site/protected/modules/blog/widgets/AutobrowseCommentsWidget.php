<?php
class AutobrowseCommentsWidget extends YWidget
{
    public $redirectTo;
    public $show_form = false;
    public $post_id;

    public function init(){
        $cs = Yii::app()->clientScript;
        //$cs->registerScriptFile(Yii::app()->theme->baseUrl."/js/jquery.esn.autobrowse.js");
        //$cs->registerScriptFile(Yii::app()->theme->baseUrl."/js/jquery.json-2.2.min.js");
        //$cs->registerScriptFile(Yii::app()->theme->baseUrl."/js/jstorage.js");
        $cs->registerScript('autobrowse','activate_autobrowse();');


    }

    public function run()
    {
		$dependency = new CDbCacheDependency('SELECT MAX(creation_date) FROM '.PostComment::tableName().' WHERE post_id='.$this->post_id);
		$count = Yii::app()->db->cache(1000, $dependency)->createCommand("SELECT COUNT(id) FROM ".PostComment::tableName().' WHERE post_id='.$this->post_id)->queryScalar();
		
        $this->render('autobrowsecomments', array(
            'redirectTo' => $this->redirectTo ,
            'max' => $count,
            'show_form' => $this->show_form ,
            'post_id' => $this->post_id ,
        ));
    }
}