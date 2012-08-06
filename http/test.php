<br>
<!-- #### Facebook #### -->
<div class="link_share">
<fb:like href="<?php echo rawurlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);?>" layout="button_count" action="like" show-faces="true" width="200" height="20px" scrolling="no" frameborder="0" locale="ru" allowTransparency="true" overflow="hidden" colorscheme="light" font="trebuchet ms"></fb:like>
</div>
<div id="fb-root"></div>
<script type="text/javascript">
		<!--
		window.fbAsyncInit = function() {
			// Don't use my app id, use your own or it won't work!
			 FB.init({status: true, cookie: true, xfbml: true});
			 FB.Event.subscribe('edge.create', function(href, widget) {
			 alert('FB Like pressed. U like it') ;
				var rel = jQuery("#idrecource").attr("rel");
				jQuery.post("/ajax.php",{"rel":rel});
			 });
			 FB.Event.subscribe('edge.remove', function(href, widget) {
			 alert('FB Like pressed. U hate this') ;
				var rel = jQuery("#idrecource").attr("rel");
				jQuery.post("/ajax.php",{"rel":rel});
			 });
		};
		(function() {
			 var e = document.createElement('script');
			 e.type = 'text/javascript';
			 e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
			 e.async = true;
			 document.getElementById('fb-root').appendChild(e);
		 }());
		//-->
		</script>
        
<br>
 <!-- #### VK #### -->
<script type="text/javascript" src="http://userapi.com/js/api/openapi.js"></script>
	<script type="text/javascript">
	   VK.init({apiId: 3041786, onlyWidgets: true});
	</script>
	
	<div id="vk_like" style="width:120px;"></div>


	<script type="text/javascript">VK.Widgets.Like("vk_like", {type: "button"});</script>
	<script type="text/javascript">
		VK.Observer.subscribe('widgets.like.liked',function(response){ 
			alert('VK Like pressed. U like it') ;
			var rel = jQuery("#idrecource").attr("rel");
				jQuery.post("/ajax.php",{"rel":rel});
			});
		VK.Observer.subscribe('widgets.like.unliked',function(response){ 
			alert('VK Like pressed. U hate this') ;
			var rel = jQuery("#idrecource").attr("rel");
				jQuery.post("/ajax.php",{"rel":rel});
			});
	</script>
    
<br>
<!-- #### TWITTER #### -->
<a rel="nofollow" href="http://twitter.com/share" class="twitter-share-button" data-url="<?php echo rawurlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);?>"  data-width="108" data-count="horizontal" data-via="jquerydoc" data-lang="ru">Twitter</a>

<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
<script type="text/javascript" charset="utf-8">
  window.twttr = (function (d,s,id) {
    var t, js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return; js=d.createElement(s); js.id=id;
    js.src="//platform.twitter.com/widgets.js"; fjs.parentNode.insertBefore(js, fjs);
    return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });
  }(document, "script", "twitter-wjs"));
</script>
<script type="text/javascript">      
	twttr.events.bind('tweet',function (twttr) {
	 alert('Tweeter button pressed.') ;
			var rel = jQuery("#idrecource").attr("rel");
			jQuery.post("/ajax.php",{"rel":rel});	

	});
</script>
