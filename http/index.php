<?php
// подробнее про index.php http://www.yiiframework.ru/doc/guide/ru/basics.entry
date_default_timezone_set('Asia/Yekaterinburg');

// путь к фреймворку Yii, при необходиомсти можно изменить
$yii = dirname(__FILE__) . '/../framework/yii.php';
// путь к основному конфигурациооному файлу Yii, при необходиомсти можно изменить
$config = dirname(__FILE__) . '/protected/config/main.php';

// при работе сайта в "боевом" режиме следующие две строки рекомендуется закомментировать
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

require_once($yii);
Yii::createWebApplication($config)->run();