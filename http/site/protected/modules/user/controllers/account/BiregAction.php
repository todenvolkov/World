<?php 
class BiregAction extends CAction
{
    public function run()
    {
        $this->controller->render('bitrixreg', array('model' => $form));
    }
}