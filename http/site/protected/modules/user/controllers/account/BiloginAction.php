<?php 
class BiloginAction extends CAction
{
    public function run()
    {
        $this->controller->render('bitrixlogin', array('model' => $form));
    }
}