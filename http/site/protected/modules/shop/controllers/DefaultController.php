<?php
class DefaultController extends YBackController {
	public function actionIndex() {
   		 $this->redirect(array('shop/index'));
	}

 }
