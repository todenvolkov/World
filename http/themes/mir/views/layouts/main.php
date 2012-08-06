<!DOCTYPE html>
<!--[if lt IE 7 ]> <html class="ie ie6" lang="ru"> <![endif]-->
<!--[if IE 7 ]> <html class="ie ie7" lang="ru"> <![endif]-->
<!--[if IE 8 ]> <html class="ie ie8" lang="ru"> <![endif]-->
<!--[if IE 9 ]> <html class="ie ie9" lang="ru"> <![endif]-->
<!--[if gt IE 9]><!--><html lang="ru"><!--<![endif]-->
<head>
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
<meta charset="UTF-8">

<meta name="language" content="<?php echo Yii::app()->language; ?>" />
<meta name="keywords" content="<?php echo $this->keywords; ?>" />
<meta name="description" content="<?php echo $this->description; ?>" />

<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/prettify.js"></script>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/kickstart.js"></script>
<!--[if lt IE 9]> <script src="js/html5.js" ></script> <![endif]-->
<!--[if lt IE 10]> <script type="text/javascript" src="js/PIE.js"></script> <![endif]-->
<link rel="stylesheet" type="text/css" href="<?=Yii::app()->theme->baseUrl?>/css/style.css" media="all" />
</head>
<body>
<div class="servicebar">
  <nav>
    <?php $this->widget('application.modules.menu.widgets.MirMenuWidget', array('id'=>'menu left-menu','name'=>'top-left-menu')); ?>
    <?php $this->widget('application.modules.menu.widgets.MirMenuWidget', array('id'=>'menu right-menu','name'=>'top-right-menu')); ?>
  </nav>
</div>
<!--.SERVICEBAR ENDS-->

<div class="header-wrapper">
  <header>
    <div class="logo"><a href="/"><img src="<?=Yii::app()->theme->baseUrl?>/images/logo.png" width="320" height="109" alt="Logo" /></a></div>
    <div class="user"><span class="phone_no"><?php $this->widget("application.modules.contentblock.widgets.ContentBlockWidget", array("code" => "phone")); ?></span>
      <ul>
        <li><?=CHtml::link('Вход',array('/login'))?></li>
        <li><?=CHtml::link('Регистрация',array('/registration'))?></li>
      </ul>
    </div>
    <div class="counter-wrapper"><span>Когда вы с нами вас видят:</span>
      <div class="counter">&nbsp;</div>
    </div>
  </header>
</div>
<!--.HEAEDER-WRAPPER ENDS-->
<div class="banner-wrapper">
  <div class="banner">
    <?php echo $content; ?>
  </div>
</div>
<div id="wrap">
<!--.BANNER-WRAPPER ENDS-->
  
  <div class="post-block clearfix">
    <div class="blogs">
      <h2>посты из <strong>блога</strong></h2>
      <ul>
        <li>
          <ul class="clearfix">
            <li>21.06.2012</li>
            <li><a href="#">Рубрика</a></li>
            <li><a href="" class="like">145</a></li>
            <li><a href="#" class="comments">64</a></li>
          </ul>
          <h3><a href="#">Не пропустите летние скидки!</a></h3>
          Мы дарим вам 10 дней и 10 любых услун на ваш выбор! Мы дарим вам 10 любых услун на ваш выбор! </li>
        <li>
          <ul class="clearfix">
            <li>21.06.2012</li>
            <li><a href="#">Рубрика</a></li>
            <li><a href="" class="like">145</a></li>
            <li><a href="#" class="comments">64</a></li>
          </ul>
          <h3><a href="#">Не пропустите летние скидки и новые предложения!</a></h3>
          Мы дарим вам 10 дней и 10 любых услун на ваш выбор! Мы дарим вам 10 любых услун на ваш выбор!</li>
        <li>
          <ul class="clearfix">
            <li>21.06.2012</li>
            <li><a href="#">Рубрика</a></li>
            <li><a href="" class="like">145</a></li>
            <li><a href="#" class="comments">64</a></li>
          </ul>
          <h3><a href="#">Не пропустите сезон лучших продаж!</a></h3>
          Мы дарим вам 10 дней и 10 любых услун на ваш выбор! Мы дарим вам 10 любых услун на ваш выбор!</li>
      </ul>
      <a href="#" class="more-post">Больше постов</a> </div>
    <div class="events">
      <h2>события из <strong>блога</strong></h2>
      <ul>
        <li>
          <ul class="clearfix">
            <li>21.06.2012</li>
            <li><a href="#">Рубрика</a></li>
            <li><a href="" class="like">145</a></li>
            <li><a href="#" class="comments">64</a></li>
          </ul>
          <h3><a href="#">Не пропустите летние скидки!</a></h3>
          Мы дарим вам 10 дней и 10 любых услун на ваш выбор! Мы дарим вам 10 любых услун на ваш выбор!</li>
        <li>
          <ul class="clearfix">
            <li>21.06.2012</li>
            <li><a href="#">Рубрика</a></li>
            <li><a href="" class="like">145</a></li>
            <li><a href="#" class="comments">64</a></li>
          </ul>
          <h3><a href="#">Не пропустите летние скидки и новые предложения!</a></h3>
          Мы дарим вам 10 дней и 10 любых услун на ваш выбор! Мы дарим вам 10 любых услун на ваш выбор!</li>
        <li>
          <ul class="clearfix">
            <li>21.06.2012</li>
            <li><a href="#">Рубрика</a></li>
            <li><a href="" class="like">145</a></li>
            <li><a href="#" class="comments">64</a></li>
          </ul>
          <h3><a href="#">Не пропустите сезон лучших продаж!</a></h3>
          Мы дарим вам 10 дней и 10 любых услун на ваш выбор! Мы дарим вам 10 любых услун на ваш выбор!</li>
      </ul>
      <a href="#" class="more-post">Больше событий</a> </div>
    <a href="#" class="more subscribe">подписаться</a> </div>
  <!--.POST-BLOCK ENDS-->
  
  <div class="tape-block clearfix">
    <div class="tape-sostav">
    	<h2>лента <strong>состав.ру</strong></h2>
		<?php $this->widget('application.modules.news.widgets.RssNewsWidget',array('feed'=>'http://www.sostav.ru/webServices/RSS/','count'=>5)); ?>
    </div>
    <div class="tape-adme">
      <h2>лента <strong>adme.ru</strong></h2>
      <?php $this->widget('application.modules.news.widgets.RssNewsWidget',array('feed'=>'http://www.adme.ru/rss/','count'=>5)); ?>
    </div>
  </div>
  <!--.TAP-BLOCK ENDS--> 
</div>
<!--#WRAP ENDS-->

<div class="footer-wrapper">
  <footer class="clearfix">
    <div class="footer-logo"> <a href="#"><img src="<?=Yii::app()->theme->baseUrl?>/images/008.png" width="181" height="109" alt="" /></a>
      <address>
      2012 &copy; &laquo;Компания МИР&raquo; Ул. Харьковская, 83А, крп. 4 <br />
      БЦ &laquo;Благман&raquo; <br />
      Тел: <?php $this->widget("application.modules.contentblock.widgets.ContentBlockWidget", array("code" => "phone")); ?>
      </address>
    </div>
    <div class="navigation-block">
      <div class="navigation clearfix">
        <div class="home">
          <h4>Главная</h4>
          <ul>
            <li><a href="">Оптимизировать затраты</a></li>
            <li><a href="#">Разместить и  согласовать</a></li>
            <li><a href="#">Спроектировать  и изготовить</a></li>
            <li><a href="#">Анализировать</a></li>
            <li><a href="#">Гторвые решения</a></li>
            <li><a href="#">Получить больше</a></li>
            <li><a href="#">Менеджеры</a></li>
            <li><a href="#">Контакты</a></li>
          </ul>
        </div>
        <div class="blog">
          <h4>блог</h4>
          <ul>
            <li><a href="#">Посты</a></li>
            <li><a href="#">События</a></li>
            <li><a href="#">Портфолио</a></li>
            <li><a href="#">Полезное</a></li>
            <li><a href="#">Блоггеры</a></li>
            <li><a href="#">FAQ</a></li>
          </ul>
        </div>
        <div class="directions">
          <h4>направления</h4>
          <ul>
            <li><a href="#">Outdoor</a></li>
            <li><a href="#">Печать</a></li>
            <li><a href="#">Производство</a></li>
            <li><a href="#">Дизайн</a></li>
            <li><a href="#">Маркетинг</a></li>
          </ul>
        </div>
        <div class="personal-data">
          <h4>личные данные</h4>
          <ul>
            <li><a href="#">Войти</a></li>
            <li><a href="#">Регистрация</a></li>
            <li>&nbsp;</li>
            <li><a href="#">Личный кабинет</a></li>
            <li><a href="#">Корзина (0)</a></li>
            <li>&nbsp;</li>
            <li><a href="#">Подписаться на новости</a></li>
          </ul>
        </div>
        <div class="follow-us">
          <h4>следуйте за нами</h4>
          <?php $this->widget('application.modules.menu.widgets.MirMenuWidget', array('id'=>'','name'=>'bottom-follow-us')); ?>
        </div>
      </div>
      <div class="site-development clearfix">
        <div class="Nsystems">Создание сайта -<a href="#"> N-Systems</a></div>
        <ul class="share-this">
          <li><a href="#">Bloger</a></li>
          <li><a href="#" class="facebook">Facebook</a> </li>
          <li><a href="#" class="google">Google Plus</a></li>
        </ul>
        <br>
        <br>
        <div class="clearfix"><?php $this->widget('YPerformanceStatistic');?></div>
      </div>
    </div>
  </footer>
  <!--FOOTER ENDS--> 
</div>
<!--.FOOTER-WRAPPER ENDS-->
</body>
</html>