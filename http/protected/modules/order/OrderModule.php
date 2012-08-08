<?php
class OrderModule extends YWebModule
{


    public function getParamsLabels()
    {
        return array(
            'adminMenuOrder' => Yii::t('page','Порядок следования в меню'),

        );
    }

    public function  getVersion()
    {
        return '0.1';
    }

    public function getEditableParams()
    {
        return array(

        );
    }

    public function getCategory()
    {
        return Yii::t('page', 'Заказы');
    }

    public function getName()
    {
        return Yii::t('page', 'Заказы');
    }

    public function getDescription()
    {
        return Yii::t('page', 'Модуль для создания и редактирования заказов на сайте');
    }

    public function getAuthor()
    {
        return Yii::t('page', 'N-systems');
    }

    public function getAuthorEmail()
    {
        return Yii::t('page', 'info@neo-systems.ru');
    }

    public function getUrl()
    {
        return Yii::t('page', 'http://neo-systems.ru');
    }

    public function getIcon()
    {
        return "file";
    }

    public function init()
    {
        parent::init();

        $this->setImport(array(
                              'application.modules.order.models.*',
                              'application.modules.order.components.*',
                              'application.modules.order.components.widgets.*',
                         ));

    }
}
?>