<?php
class AjaxController extends YFrontController {
	
	public function actions()
    {
        return array(
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
				'foreColor' => 0xE85B1F,
				'width'=>70,
				'height'=>41,
				'padding' => 5,
				'minLength' => 3,
				'maxLength' => 3,
            )
        );
    }
	
    // actionIndex вызывается всегда, когда action не указан явно.
    function actionIndex(){
        $input = $_POST['input'];
        // для примера будем приводить строку к верхнему регистру
        $output = mb_strtoupper($input, 'utf-8');
 
        // если запрос асинхронный, то нам нужно отдать только данные
        if(Yii::app()->request->isAjaxRequest){
            echo CHtml::encode($output);
            // Завершаем приложение
            Yii::app()->end();
        }
        else {
            // если запрос не асинхронный, отдаём форму полностью
            /*$this->render('form', array(
                'input'=>$input,
                'output'=>$output,
            ));*/
        }
    }
	
	public function actionGetComments(){
        $limit = 10;
        $page = (int)$_REQUEST['page']>0?(int)$_REQUEST['page']:1;
        $post = (int)$_REQUEST['postId'];

        $dependency = new CDbCacheDependency('SELECT MAX(creation_date) FROM '.PostComment::tableName().' WHERE post_id='.$post);
        $sql = "SELECT pc.text, pc.creation_date, pc.name FROM ".PostComment::tableName()." pc LEFT JOIN ".User::tableName()." usr ON pc.user_id = usr.id";
        $where = " WHERE post_id=".$post;
        $limit = " LIMIT ".$limit." OFFSET ".$limit*($page-1);
        $comments = Yii::app()->db->cache(1000, $dependency)->createCommand($sql.$where.$limit)->queryAll();

        echo json_encode($comments);
    }
	
	public function actionAddComment(){
		if(Yii::app()->request->isAjaxRequest){
			
			$module = Yii::app()->getModule('comment');
			
			$pcomment = new PostComment;
			$pcomment->setAttributes($_POST['PostComment']);
			$pcomment->status = $module->defaultCommentStatus;
			
			if (Yii::app()->user->isAuthenticated())
            {
                $pcomment->setAttributes(array(
                    'user_id' => Yii::app()->user->getId(),
                    'name' => Yii::app()->user->getState('nick_name'),
                    'email' => Yii::app()->user->getState('email'),
                ));

                if($module->autoApprove)
                    $pcomment->status = PostComment::STATUS_APPROVED;
            }// if auth
			
			if ($pcomment->save()) {
				echo json_encode(array('text'=>$pcomment->text, 'creation_date'=>$pcomment->creation_date, 'name'=>$pcomment->name));
				Yii::app()->ajax->success();
			}else{
				$error = $pcomment->getErrors();
				$msg = "\n\n";
				foreach($error as $key=>$value)
					$msg .= $value[0]."\n";
				Yii::app()->ajax->failure(Yii::t('postcomment', 'Запись не добавлена!'.$msg));
			}
			
		}// if ajax
	}
	
}
?>