<?php
 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//$APPLICATION->SetTitle("Outdoor секция");
//define('STOP_STATISTICS', true);
//require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$GLOBALS['APPLICATION']->RestartBuffer();
?>

<link href="/bitrix/js/main/core/css/core.css?1344336180" type="text/css" rel="stylesheet" />
<link href="/bitrix/templates/mir/components/bitrix/main.calendar/mir/style.css?1344336720" type="text/css" rel="stylesheet" />
<link href="/bitrix/js/main/core/css/core_window.css?1344336180" type="text/css" rel="stylesheet" />
<link href="/bitrix/components/bitrix/map.yandex.system/templates/.default/style.css?1344340620" type="text/css" rel="stylesheet" />
<link href="/bitrix/templates/mir/styles.css?1344336900" type="text/css" rel="stylesheet" />
<link href="/bitrix/templates/mir/template_styles.css?1344336900" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="/bitrix/js/main/core/core.js?1344336180"></script>
<script type="text/javascript">BX.message({'LANGUAGE_ID':'ru','FORMAT_DATE':'DD.MM.YYYY','FORMAT_DATETIME':'DD.MM.YYYY HH:MI:SS','COOKIE_PREFIX':'BITRIX_SM','bitrix_sessid':'ccdd8950d11b93ccfce2e989278ceeb9','JS_CORE_LOADING':'Загрузка...','JS_CORE_WINDOW_CLOSE':'Закрыть','JS_CORE_WINDOW_EXPAND':'Развернуть','JS_CORE_WINDOW_NARROW':'Свернуть в окно','JS_CORE_WINDOW_SAVE':'Сохранить','JS_CORE_WINDOW_CANCEL':'Отменить'})</script>
<script type="text/javascript" src="/bitrix/js/main/core/core_ajax.js?1344336180"></script>
<script type="text/javascript" src="/bitrix/js/main/core/core_fx.js?1344336180"></script>
<script type="text/javascript" src="/bitrix/js/main/session.js?1344336240"></script>
<script type="text/javascript">
bxSession.mess.messSessExpired = 'Ваш сеанс работы с сайтом завершен из-за отсутствия активности в течение 120 мин. Введенные на странице данные не будут сохранены. Скопируйте их перед тем, как закроете или обновите страницу.';
bxSession.Expand(7200, 'ccdd8950d11b93ccfce2e989278ceeb9', true, '657ffeddfbb1ff37f7efaf180d8f8fbe');
</script>
<script type="text/javascript" src="/bitrix/js/main/core/core_window.js?1344336180"></script>
<script src="http://api-maps.yandex.ru/1.1/?key=AEOVRVABAAAA_1_sKAMAYEtG0HDHgVALDwsbUUezHFI6CdoAAAAAAAAAAAA9xX-DPdagpbZ07wzJDbBWUpPQWQ==&wizard=bitrix" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="/bitrix/js/main/utils.js?1344336240"></script>
<script type="text/javascript" src="/bitrix/js/main/popup_menu.js?1344336240"></script>

<link rel="shortcut icon" type="image/x-icon" href="/bitrix/templates/mir/favicon.ico" />
<link rel="stylesheet" type="text/css" href="/bitrix/templates/mir/styles.css" />

<script type="text/javascript" src="/bitrix/templates/mir/AC_RunActiveContent.js"></script>
<style>
body { background-color: transparent; }
</style>
<!--  --><table width="930" cellspacing="0" cellpadding="0" border="0" align="left" style="clear:both"> 	 
  <tbody> 		 	 
    <tr> 		 		<td valign="top" width="432" height="50" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;"></td> 		<td valign="center" height="50" align="left" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;"> 			 
        <table width="90%" cellspacing="0" cellpadding="0" border="0"> 				 
          <tbody> 
            <tr> 					<td style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;" id="outdoor_header1"></td> 					<td style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;" id="outdoor_header2"></td> 				</tr>
           			</tbody>
         </table>
       			<form name="form1" action="" method="POST"> 				 

 
          <table cellspacing="0" cellpadding="3" border="0" style="padding-bottom: 5px;"> 					 
            <tbody> 
              <tr> 					<td valign="top" nowrap="nowrap" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;"><input type="checkbox" name="check_status" id="check_status" onclick="javascript:reshow_points();" value="1" />&nbsp;Показать свободные</td> 					<td nowrap="nowrap" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">c даты <?$APPLICATION->IncludeComponent(
	"bitrix:main.calendar",
	"mir",
	Array(
		"SHOW_INPUT" => "Y",
		"FORM_NAME" => "form1",
		"INPUT_NAME" => "datefield1",
		"INPUT_NAME_FINISH" => "",
		"INPUT_VALUE" => date("d.m.Y"),
		"INPUT_VALUE_FINISH" => "",
		"SHOW_TIME" => "N",
		"HIDE_TIMEBAR" => "Y"
	)
);?></td> 					<td nowrap="nowrap" style="-moz-border-top-colors: none; -moz-border-right-colors: none; -moz-border-bottom-colors: none; -moz-border-left-colors: none; -moz-border-image: none;">на <input type="text" name="datefield2" id="datefield2" value="30" size="2" onchange="javascript:reshow_points();" /> дней</td> 					 					</tr>
             				</tbody>
           </table>
         			</form> 		</td> 	</tr>
   </tbody>
 </table><br>
 <?php $APPLICATION->IncludeComponent(
	"bitrix:map.yandex.view",
	".mir-yandex1",
	array(
		"KEY" => 
"AEOVRVABAAAA_1_sKAMAYEtG0HDHgVALDwsbUUezHFI6CdoAAAAAAAAAAAA9xX-DPdagpbZ07wzJDbBWUpPQWQ==",
		"INIT_MAP_TYPE" => "MAP",
		"INIT_MAP_LAT"=>"57.134999476730655",
		"INIT_MAP_LON"=>"65.5630020222928",
		"INIT_MAP_SCALE"=>"13",
		//"MAP_DATA" => "a:4:{s:10:\"yandex_lat\";d:57,134999476730655;s:10:\"yandex_lon\";d:65,5630020222928;s:12:\"yandex_scale\";i:13;s:10:\"PLACEMARKS\";a:1:{i:0;a:3:{s:3:\"LON\";d:65,5988807347;s:3:\"LAT\";d:57,141900609;s:4:\"TEXT\";s:35:\"Компания МИР###RN###540-654###RN###\";}}}",
		"MAP_WIDTH" => $_REQUEST["w"]?$_REQUEST["w"]:"930",
		"MAP_HEIGHT" => $_REQUEST["h"]?$_REQUEST["h"]:"620",
		"CONTROLS" => array("ZOOM"),
		"OPTIONS" => array("ENABLE_SCROLL_ZOOM", "ENABLE_DRAGGING"),
		"MAP_ID" => "mir_map"
	)
);?> <?php //require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

<script type="text/javascript" src="/bitrix/templates/mir/js/easyXDM.min.js"></script>
<script type="text/javascript">
    /**
     * Request the use of the JSON object
     */
    easyXDM.DomHelper.requiresJSON("/bitrix/templates/mir/js/json2.js");
</script>
<script type="text/javascript">
	var remote = new easyXDM.Rpc(/** The channel configuration*/{
		local: "/bitrix/templates/mir/js/name.html",
		swf: "/bitrix/templates/mir/js/easyxdm.swf"
	}, /** The configuration */ {
		remote: {
			alertMessage: {}
		},
		local: {
			//
		}
	});
</script>