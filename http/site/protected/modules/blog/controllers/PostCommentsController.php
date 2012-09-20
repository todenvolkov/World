<?php

class PostCommentsController extends YBackController
{
    public function actionIndex()
    {
        $this->redirect('admin');
    }
	
	/**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model = new PostComment('search');
        $model->unsetAttributes(); // clear any default values
        if(isset($_GET['PostComment']))
            $model->attributes = $_GET['PostComment'];

        $this->render('admin', array(
            'model' => $model,
        ));
    }
	
	/**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['PostComment']))
        {
            $model->attributes = $_POST['PostComment'];

            if($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }
	
	
	
	
	/**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     *
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $model = PostComment::model()->findByPk($id);
        if($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }
}