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
<!--[if lt IE 9]> <script src="js/html5.js" ></script> <![endif]-->
<!--[if lt IE 10]> <script type="text/javascript" src="js/PIE.js"></script> <![endif]-->
<link rel="stylesheet" type="text/css" href="<?=Yii::app()->theme->baseUrl?>/css/style.css" media="all" />
<link rel="stylesheet" href="<?=Yii::app()->theme->baseUrl?>/css/jquery.fancybox-1.3.4.css" type="text/css" media="screen">
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

<div class="banner-wrapper">
  <div class="banner">
    <div class="video">
      <div class="player">
        <object width="540" height="330">
          <param name="movie" value="<?php $this->widget("application.modules.contentblock.widgets.ContentBlockWidget", array("code" => "youtube")); ?>">
          </param>
          <param name="allowFullScreen" value="true">
          </param>
          <param name="allowscriptaccess" value="always">
          </param>
          <embed src="<?php $this->widget("application.modules.contentblock.widgets.ContentBlockWidget", array("code" => "youtube")); ?>" type="application/x-shockwave-flash" width="540" height="330" allowscriptaccess="always" allowfullscreen="true"></embed>
        </object>
      </div>
      <div class="share-this"><span>Поделиться:</span>
        <ul>
          <li><a href="#">Facebook</a></li>
          <li><a href="#" class="bloger">Bloger</a></li>
          <li><a href="#" class="link">Link</a></li>
          <li><a href="#" class="twitter">Twitter</a></li>
          <li><a href="#" class="youtube">YouTube</a></li>
          <li><a href="#" class="at">At</a></li>
        </ul>
      </div>
    </div>
    <div class="information"><?php $this->widget("application.modules.contentblock.widgets.ContentBlockWidget", array("code" => "intro")); ?></div>
  </div>
</div>
<!--.BANNER-WRAPPER ENDS-->

<div id="sidePanel" style="right: -262px; ">
    <div id="panelContent">
        <div id="response-box">
            <ul class="comment-box">
                <li class="first"> <a href="#respond" class="fancybox">
                    <div class="avatar"> <img src="avatars/avatar.png" width="58" height="69" alt="avatar"> </div>
                    <div class="content"> <strong class="name-surname">Имя Фамилие</strong> <span>Написать собщение<br>
            Личная страница</span> <span class="star">145</span> </div>
                </a> </li>
                <li> <a href="#respond" class="fancybox">
                    <div class="avatar"> <img src="avatars/avatar.png" width="58" height="69" alt="avatar"> </div>
                    <div class="content"> <strong class="name-surname">Имя Фамилие</strong> <span>Написать собщение<br>
            Личная страница</span> <span class="star">145</span> </div>
                </a> </li>
                <li> <a href="#respond" class="fancybox">
                    <div class="avatar"> <img src="avatars/avatar.png" width="58" height="69" alt="avatar"> </div>
                    <div class="content"> <strong class="name-surname">Имя Фамилие</strong> <span>Написать собщение<br>
            Личная страница</span> <span class="star">145</span> </div>
                </a> </li>
                <li> <a href="#respond" class="fancybox">
                    <div class="avatar"> <img src="avatars/avatar.png" width="58" height="69" alt="avatar"> </div>
                    <div class="content"> <strong class="name-surname">Имя Фамилие</strong> <span>Написать собщение<br>
            Личная страница</span> <span class="star">145</span> </div>
                </a> </li>
                <li class="last"> <a href="#respond" class="fancybox">
                    <div class="avatar"> <img src="avatars/avatar.png" width="58" height="69" alt="avatar"> </div>
                    <div class="content"> <strong class="name-surname">Имя Фамилие</strong> <span>Написать собщение<br>
            Личная страница</span><span class="star">145</span> </div>
                </a> </li>
            </ul>
        </div>
        <!--.RESPONSE BOX ENDS-->
    </div>
    <!--.PANEL CONTENT ENDS-->
    <div id="panelHandle" style="background-image: url(<?=Yii::app()->theme->baseUrl?>/css/images/respond-bg.png); background-position: 0px 108px; background-repeat: no-repeat no-repeat; "><a href="#" title="respond">Respond</a></div>
</div>

<div id="wrap">
  <div class="our-contacts">
    <h2>как мы увеличиваем <strong>количество контактов</strong></h2>
    <ul class="clearfix">
      <li><a href="/pages/optimization">Оптимизируем затраты <img src="<?=Yii::app()->theme->baseUrl?>/images/001.png" width="107" height="135" alt="" /></a></li>
      <li><a href="/pages/location-and-agreement">размещаем и согласовываем <img src="<?=Yii::app()->theme->baseUrl?>/images/002.png" width="86" height="135" alt="" /></a></li>
      <li><a href="/pages/design-and-production">проектируем и изготавливаем <img src="<?=Yii::app()->theme->baseUrl?>/images/003.png" width="104" height="135" alt="" /></a></li>
      <li><a href="/pages/analysis-and-maintenance">Анализируем и сопровождаем <img src="<?=Yii::app()->theme->baseUrl?>/images/004.png" width="92" height="135" alt="" /></a></li>
    </ul>
  </div>
  <!--.OUR-CONTANTS ENDS-->
  
  <div class="solutions-block">
    <h2>возьмите готовые <strong>решения</strong></h2>
    <ul class="solutions clearfix">
      <li><a href="/pages/solution-for-small-business">Молодой бизнес</a> У вас молодой перспективный бизнес нуждающийся в активном привлечении клиентов, при условии скромного бюджета на рекламу. </li>
      <li><a href="/pages/solution-for-medium-business">Оптима</a> Максимальный охват аудитории, при оптимальных вложениях для удержания занятых  позиций бизнеса и активного продвижения вперед.</li>
      <li><a href="/pages/solution-for-big-business">Премиум</a> Хороший бизнесмен ни когда не забывает о подростающей конкуренции. Закрепляем позиции с мксимальным размахом.</li>
    </ul>
    <ul class="clearfix">
      <li class="read-more"><a href="/pages/solution-for-small-business">Подробнее</a></li>
      <li class="read-more"><a href="/pages/solution-for-medium-business">Подробнее</a></li>
      <li class="read-more"><a href="/pages/solution-for-big-business">Подробнее</a></li>
    </ul>
    <a href="#" class="more">Другие варианты</a> </div>
  <!--.SOLUTIONS-BLOCK ENDS-->
  
  <div class="get-more-block">
    <h2>как получить <strong>больше</strong></h2>
    <ul class="get-more clearfix">
      <li><span>Зарегистрироваться на сайте и получить  дисконтно-бонусную карту <strong>клиента</strong>. <img src="<?=Yii::app()->theme->baseUrl?>/images/005.png" width="107" height="124" alt="" /></span> </li>
      <li class="separater">&nbsp;</li>
      <li> <span>Подписаться на новости и<br />
        получить<br />
        <strong>КУПОНЫ</strong>. <img src="<?=Yii::app()->theme->baseUrl?>/images/006.png" width="141" height="124" alt="" /></span></li>
      <li class="separater">&nbsp;</li>
      <li><span>Комментировать, оценивать посты, работы, и получить <br/>
        <strong>ПОДАРОК</strong>. <img src="<?=Yii::app()->theme->baseUrl?>/images/007.png" width="104" height="124" alt="" /></span></li>
    </ul>
    <a href="#" class="more">задать вопрос</a> </div>
  <!--.GET-MORE-BLOCK ENDS-->

  <?php $this->renderPartial('//layouts/_lastposts'); ?>

  <!--.POST-BLOCK ENDS-->
  
  <div class="tape-block clearfix">
    <div class="tape-sostav">
    	<h2>лента <strong>состав.ру</strong></h2>
		<?php $this->widget('application.modules.news.widgets.RssNewsWidget',array('feed'=>'http://www.sostav.ru/webServices/RSS/','count'=>5)); ?>
    </div>
    <div class="tape-adme">
      <h2><strong>лента</strong> adme.ru</h2>
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
      Тел: +7 (3452) 54-06-54
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