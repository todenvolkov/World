<!DOCTYPE html>
<!--[if lt IE 7 ]> <html class="ie ie6" lang="ru"> <![endif]-->
<!--[if IE 7 ]> <html class="ie ie7" lang="ru"> <![endif]-->
<!--[if IE 8 ]> <html class="ie ie8" lang="ru"> <![endif]-->
<!--[if IE 9 ]> <html class="ie ie9" lang="ru"> <![endif]-->
<!--[if gt IE 9]><!--><html lang="ru"><!--<![endif]-->

<head>
<title><?php echo CHtml::encode("Ошибка! Такая страница не найдена"); ?></title>
<meta charset="UTF-8">
<meta name="description" content="" />
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/prettify.js"></script>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/kickstart.js"></script>
<!--[if lt IE 9]> <script src="<?=Yii::app()->theme->baseUrl?>/js/html5.js" ></script> <![endif]-->
<!--[if lt IE 10]> <script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/PIE.js"></script> <![endif]-->
<link rel="stylesheet" type="text/css" href="<?=Yii::app()->theme->baseUrl?>/css/style.css" media="all" />
</head>
<body class="page404">
<div class="not-exist">
	<img src="<?=Yii::app()->theme->baseUrl?>/css/images/wrong-address.png" width="300" height="460" alt=""  />
  <p>К сожалению страницы<br /> с таким адресом не существует.</p>
  <span>Воспользуйтесь <a href="/sitemap">картой сайта</a>,<br /> либо перейдите на <a href="/">главную страницу</a>.</span>
	<span><strong>И вам непременно повезет!</strong></span>
</div>

</body>
</html>