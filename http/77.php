<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("РљР»РёРµРЅС‚Р°Рј");
?> 



<style type="text/css" id="page-css">
.manager_panel {
	padding: 10px;
	width: 200px;
	height: 410px;
	background: #E4E4E4;
	border: #4C4C4C 2px solid;
	font-family: Calibri,Verdana;
}


/* root element for scrollable */
.vertical {  
	
	/* required settings */
	position:relative;
	overflow:hidden;	

	/* vertical scrollers have typically larger height than width */	
	height: 380px;	 
	width: 200px;
	border-top:1px solid #ddd;	
}

/* root element for scrollable items */
.items {	
	position:absolute;
	
	/* this time we have very large space for height */	
	height:20000em;	
	margin: 0px;
}

/* single scrollable item */
.item {
	border-bottom:1px solid #ddd;
	margin:5px 0;
	padding:10px;
	font-size:12px;
	height:100px;
}

/* elements inside single item */
.item img {
	float:left;
	margin-right:10px;
}


/* the action buttons above the scrollable */
#actions {
	width:200px;
	margin:10px 0 10px 0;	
}

#actions a {
	font-size:11px;		
	cursor:pointer;
	color:#666;
}

#actions a:hover {
	text-decoration:underline;
	color:#000;
}

.disabled {
	visibility:hidden;		
}

.next {
	float:right;
}	




/* position and dimensions of the navigator */
.navi {
	/*width:50px;
	height:20px;*/
	margin-left:30px;
}

.navi open{
	width: 300px;
}

/* items inside navigator */
.navi a {
	width:8px;
	height:8px;
	float:left;
	margin:3px;
	background:url(/bitrix/templates/mir/images/navigator.png) 0 0 no-repeat;
	display:block;
	font-size:1px;
}

/* mouseover state */
.navi a:hover {
	background-position:0 -8px;      
}

/* active state (current page state) */
.navi a.active {
	background-position:0 -16px;     
}


</style>

<script type="text/javascript" src="/bitrix/templates/mir/jquery/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="/bitrix/templates/mir/jquery/jquery.tabSlideOut.v1.3.js"></script>
<!--
<script type="text/javascript" src="/bitrix/templates/mir/jquery/jquery-ui-1.8.13.custom.min.js"></script>
-->
<script type="text/javascript" src="/bitrix/templates/mir/jquery/jquery.tools.min.js"></script>

<script type="text/javascript">
$(function(){
	$('.manager_panel').tabSlideOut({
		tabHandle: '.handle',
		pathToTabImage: '/bitrix/templates/mir/images/manager_panel.gif',
		imageHeight: '226px',
		imageWidth: '36px',
		tabLocation: 'right',
		speed: 300,
		action: 'click', // click/hover
		topPos: '200px',
		fixedPosition: true,
		onLoadSlideOut: true	
	});
	//$(".scrollable").scrollable({ vertical: true, mousewheel: true }).navigator();
	$("#browsable").scrollable({ vertical: true, mousewheel: true }).navigator();
});
</script>


<div class="manager_panel">
	<a class="handle" href="#">Р’РєР»СЋС‡РёС‚Рµ javascript!</a> <!-- РЎСЃС‹Р»РєР° РґР»СЏ РїРѕР»СЊР·РѕРІР°С‚РµР»РµР№ СЃ РѕС‚РєР»СЋС‡РµРЅРЅС‹Рј JavaScript -->
	<b>РЎРїРёСЃРѕРє РјРµРЅРµРґР¶РµСЂРѕРІ:</b><br><br>

<div id="actions">
<table width=100% border=0 cellpadding=0 cellspacing=0>
<tr>
	<td align="left" width=10%><a class="prev">&laquo;&nbsp;РќР°Р·Р°Рґ</a></td>
	<td><div class="navi"></div></td>
	<td align="right" width=10%><a class="next">Р”Р°Р»СЊС€Рµ&nbsp;&raquo;</a></td>
</tr>
</table>
</div>

<div class="scrollable vertical" id="browsable">
<div class="items">
	<div>

<?
$filter = 
$rsUsers = CUser::GetList(($by="name"), ($order="asc"), Array( "ACTIVE" => "Y","GROUPS_ID" => Array(7) ) );

$i = 0;
while($rsUsers->NavNext(true, "f_")) :
	//echo "[".$f_ID."] (".$f_LOGIN.") ".$f_NAME." ".$f_LAST_NAME."<br>";
	//echo "".$f_NAME." ".$f_LAST_NAME."<br><br><br><br><br><br>";
	$i++;
	if ($i%2==0){
		echo "</div><div>";
	}
	echo '
	<div class="item">
		<img src="/bitrix/templates/mir/images/prt'.(($i<3)?'1':'2').'.gif" width="100" height="100">
		'.$f_NAME." ".$f_LAST_NAME.'<br><br><br>
		<a href="/chat/client.php?locale=ru&amp;group=2" target="_blank" onclick="if(navigator.userAgent.toLowerCase().indexOf(\'opera\') != -1 &amp;&amp; window.event.preventDefault) window.event.preventDefault();this.newWindow = window.open(\'/chat/client.php?locale=ru&amp;group=2&amp;url=\'+escape(document.location.href)+\'&amp;referrer=\'+escape(document.referrer), \'webim\', \'toolbar=0,scrollbars=0,location=0,status=1,menubar=0,width=640,height=480,resizable=1\');this.newWindow.focus();this.newWindow.opener=window;return false;">CРѕРѕР±С‰РµРЅРёРµ</a>
	</div>
	';
endwhile;

?>
	</div>
</div>
</div>

</div>


<!--
<div class="scrollable vertical">
	<div class="items">
		<div>
			<div class="item">
				<img src="outdoor_img/preview/UT000000133.JPG" width="206" height="164">
				123
			</div>
			<div class="item">
				<img src="outdoor_img/preview/UT000000134.JPG" width="206" height="164">
				123
			</div>
		</div>
		<div>
			<div class="item">
				<img src="outdoor_img/preview/UT000000130.JPG" width="206" height="164">
				123
			</div>
			<div class="item">
				<img src="outdoor_img/preview/UT000000131.JPG" width="206" height="164">
				123
			</div>
 		</div>
	</div>
	
</div>
-->



<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
