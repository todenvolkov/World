<?php
class MirMenuWidget extends YWidget
{
    public $name;
    public $parent_id = 0;

    public $id;
    public $params = array();

    public function init()
    {
        parent::init();

        $this->parent_id = (int)$this->parent_id;
    }

    public function run()
    {
        echo CHtml::openTag('ul', array('class' => $this->id));

        //$this->widget('zii.widgets.CMenu', array_merge($this->params, array('items' => Menu::model()->getItems($this->name, $this->parent_id))));
		
		foreach(Menu::model()->getItems($this->name, $this->parent_id) as $item){
			//var_dump($item);
			echo CHtml::openTag('li');
			echo $item['url']=="/"?"<a href='/'>".$item['label']."</a>":CHtml::link($item['label'],$item['url']);
			echo CHtml::closeTag('li');
		}

        echo CHtml::closeTag('ul');
    }

}
