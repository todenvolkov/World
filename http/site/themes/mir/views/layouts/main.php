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
<script src='<?=Yii::app()->theme->baseUrl?>/js/jquery.fancybox-1.3.4.pack.js' type="text/javascript"></script>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/jquery.infieldlabel.min.js"></script>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/jquery.hoverIntent.minified.js"></script>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/jquery.jqtransform.js"></script>

<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/jquery.esn.autobrowse.js"></script>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/jquery.json-2.2.min.js"></script>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/jstorage.js"></script>

<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/jquery.scrollTo.js"></script>
<script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/to_top.js"></script>

<!--[if lt IE 9]> <script src="<?=Yii::app()->theme->baseUrl?>/js/html5.js" ></script> <![endif]-->
<!--[if lt IE 10]> <script type="text/javascript" src="<?=Yii::app()->theme->baseUrl?>/js/PIE.js"></script> <![endif]-->
<link rel="stylesheet" type="text/css" href="<?=Yii::app()->theme->baseUrl?>/css/style.css" media="all" />
<link rel="stylesheet" href="<?=Yii::app()->theme->baseUrl?>/css/jquery.fancybox-1.3.4.css" type="text/css" media="screen">
</head>
<body>
<div class="servicebar">
  <nav>
    <?php $this->widget('application.modules.menu.widgets.MirMenuWidget', array('id'=>'menu left-menu','name'=>'top-left-menu')); ?>
    <?php $this->widget('application.modules.menu.widgets.MirMenuWidget', array('id'=>'menu right-menu','name'=>'top-right-menu')); ?>
  </nav>
</div>

<!--.SERVICEBAR ENDS-->
<div style="display:none">
  <div id="feedback-box">
    <div class="feedback"> <span>Обратная связь</span> </div>
    <div class="form-wrap">
    <p><span>*</span> Обязательные поля</p>
      <form action="#" method="post" class="clearfix jqtransform">
        <div class="form-left">
          <div class="form-row placeholder">
            <label for="Name"><span>*</span>Ваше имя</label>
            <input type="text" name="Name" id="Name" />
          </div>
          <div class="form-row placeholder">
            <label for="Company"><span>*</span>Компания</label>
            <input type="text" name="Company" id="Company" />
          </div>
          <div class="form-row placeholder">
            <label for="Email"><span>*</span> Email</label>
            <input type="text" name="Email" id="Email" />
          </div>
          <div class="form-row placeholder">
            <label for="PhoneNumber"><span>*</span>Номер телефона</label>
            <input type="text" name="PhoneNumber" id="PhoneNumber" />
          </div>
        </div>
        <div class="form-right">
          <div class="form-row placeholder">
            <label for="Comment">Текст сообщения...</label>
            <textarea name="Comment" id="Comment"></textarea>
            </div>
            <div class="form-row submit clearfix">
            <span><a href="#" class="clean">Очистить</a></span>
            <input type="submit" name="submit" value="Отправить" />
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- #FEEDBACK-BOX ENDS -->

<div class="header-wrapper">
    <header>
        <div class="logo"><a href="/"><img src="<?=Yii::app()->theme->baseUrl?>/images/logo.png" width="320" height="109" alt="Logo" /></a></div>
        <div class="user"><span class="phone_no"><?php $this->widget("application.modules.contentblock.widgets.ContentBlockWidget", array("code" => "phone")); ?></span>
            <ul>
                <li><a href="#login" class="fancybox">Вход</a></li>
                <li><a href="#">Регистрация</a></li>
            </ul>
        </div>
        <div class="counter-wrapper"><span>Когда вы с нами вас видят:</span>
            <div class="counter">&nbsp;</div>
        </div>
    </header>
</div>
<!--.HEAEDER-WRAPPER ENDS-->

<?php $this->renderPartial('//layouts/_loginform'); ?>

<!-- #LOGIN-BOX ENDS-->

<div id="wrap">
    <div class="page clearfix">
        <div class="shadow">
            <div id="top_basket" class="basket"><?php $this->renderPartial('//layouts/_basket'); ?></div>
            <!-- .BASKET ENDS-->

            <div class="blogger clearfix">

                    <?php echo $content; ?>

            </div>
            <!-- .BLOGGER ENDS -->

            <?php $this->renderPartial('//layouts/_sharethis'); ?>
            <!-- .SHARE THIS ENDS-->


        </div>
        <!-- .SAHDOW ENDS-->
    </div>
    <!-- .PAGE ENDS-->

<?php $this->renderPartial('//layouts/_lastposts'); ?>

    <!-- .POST-BLOCK ENDS-->
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
          <h4><a href="/">Главная</a></h4>
          <ul>
            <li><a href="<?=Yii::app()->createUrl("/pages/optimization")?>">Оптимизировать затраты</a></li>
            <li><a href="<?=Yii::app()->createUrl("/pages/location-and-agreement")?>">Разместить и согласовать</a></li>
            <li><a href="<?=Yii::app()->createUrl("/pages/design-and-production")?>">Спроектировать и изготовить</a></li>
            <li><a href="<?=Yii::app()->createUrl("/pages/analysis-and-maintenance")?>">Анализировать</a></li>
            <li><a href="<?=Yii::app()->createUrl("/pages/bundles")?>">Готовые решения</a></li>
            <li><a href="<?=Yii::app()->createUrl("/pages/get-more")?>">Получить больше</a></li>
            <li><a href="<?=Yii::app()->createUrl("/managers")?>">Менеджеры</a></li>
            <li><a href="<?=Yii::app()->createUrl("/pages/contacts")?>">Контакты</a></li>
          </ul>
        </div>
        <div class="blog">
          <h4>блог</h4>
          <ul>
            <li><a href="<?=Yii::app()->createUrl("/blog/posts")?>">Посты</a></li>
            <li><a href="<?=Yii::app()->createUrl("/blog/events")?>">События</a></li>
            <li><a href="<?=Yii::app()->createUrl("/blog/portfolio")?>">Портфолио</a></li>
            <li><a href="<?=Yii::app()->createUrl("/posts/tag/usefull")?>">Полезное</a></li>
            <li><a href="<?=Yii::app()->createUrl("/bloggers")?>">Блоггеры</a></li>
            <li><a href="<?=Yii::app()->createUrl("/faq")?>">FAQ</a></li>
          </ul>
        </div>
        <div class="directions">
          <h4>направления</h4>
          <ul>
            <li><a href="<?=Yii::app()->createUrl("/blog/outdoor")?>">Outdoor</a></li>
            <li><a href="<?=Yii::app()->createUrl("/blog/print")?>">Печать</a></li>
            <li><a href="<?=Yii::app()->createUrl("/blog/production")?>">Производство</a></li>
            <li><a href="<?=Yii::app()->createUrl("/blog/design")?>">Дизайн</a></li>
            <li><a href="<?=Yii::app()->createUrl("/blog/marketing")?>">Маркетинг</a></li>
          </ul>
        </div>
        <div class="personal-data">
          <h4>личные данные</h4>
          <ul>
            <li><a href="<?=Yii::app()->createUrl("/bilogin")?>">Войти</a></li>
            <li><a href="<?=Yii::app()->createUrl("/bireg")?>">Регистрация</a></li>
            <li>&nbsp;</li>
            <li><a href="<?=Yii::app()->createUrl("")?>">Личный кабинет</a></li>
            <li><a href="<?=Yii::app()->createUrl("")?>">Корзина (<?=ShoppingCart::getItemsCount()?>)</a></li>
            <li>&nbsp;</li>
            <li><a href="<?=Yii::app()->createUrl("")?>">Подписаться на новости</a></li>
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
<script type="text/javascript">
    <!--
     $(document).ready(function (){
     $('a.fancybox').fancybox({
         'scrolling'		: 'no',
         'titleShow'		: false,
         'padding' : '0',
         'centerOnScroll':true,
         'overlayColor' : '#000'
     });
     });
     -->
</script>
</body>
</html>