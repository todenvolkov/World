<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

if(!defined("__COMP_CALENDAREX_SCRIPT"))
{
	define("__COMP_CALENDAREX_SCRIPT", true);
	$APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
	$APPLICATION->AddHeadScript('/bitrix/js/main/popup_menu.js');
	if (!$this->InitComponentTemplate())
		return;

	$template = &$this->GetTemplate();
	$resource_path = $template->GetFolder();

	$str =
"<script>
if (typeof(phpVars) != 'object') {window['phpVars'] = {}};
phpVars.FORMAT_DATE = '".FORMAT_DATE."';
phpVars.FORMAT_DATETIME = '".FORMAT_DATETIME."';

jsCalendarMess = {
	'resource_path': '".$resource_path."',
	'title': '".GetMessage('TOOLS_CALENDAR')."',
	'date': '".GetMessage('calend_date')."',
	'jan': '".GetMessage('MONTH_1')."',
	'feb': '".GetMessage('MONTH_2')."',
	'mar': '".GetMessage('MONTH_3')."',
	'apr': '".GetMessage('MONTH_4')."',
	'may': '".GetMessage('MONTH_5')."',
	'jun': '".GetMessage('MONTH_6')."',
	'jul': '".GetMessage('MONTH_7')."',
	'aug': '".GetMessage('MONTH_8')."',
	'sep': '".GetMessage('MONTH_9')."',
	'okt': '".GetMessage('MONTH_10')."',
	'nov': '".GetMessage('MONTH_11')."',
	'des': '".GetMessage('MONTH_12')."',
	'prev_mon': '".GetMessage('calend_prev_mon')."',
	'next_mon': '".GetMessage('calend_next_mon')."',
	'curr': '".GetMessage('calend_curr')."',
	'curr_day': '".GetMessage("calend_curr_date")."',
	'mo': '".GetMessage('calend_mo')."',
	'tu': '".GetMessage('calend_tu')."',
	'we': '".GetMessage('calend_we')."',
	'th': '".GetMessage('calend_th')."',
	'fr': '".GetMessage('calend_fr')."',
	'sa': '".GetMessage('calend_sa')."',
	'su': '".GetMessage('calend_su')."',
	'per_week': '".GetMessage('calend_per_week')."',
	'per_mon': '".GetMessage('calend_per_mon')."',
	'per_year': '".GetMessage('calend_per_year')."',
	'close': '".GetMessage("calend_close")."',
	'month': '".GetMessage("calend_select_mon")."',
	'year': '".GetMessage("calend_select_year")."',
	'time': '".GetMessage("calend_show_time")."',
	'time_hide': '".GetMessage("calend_hide_time")."',
	'hour': '".GetMessage("calend_hour")."',
	'minute': '".GetMessage("calend_minute")."',
	'second': '".GetMessage("calend_second")."',
	'hour_title': '".GetMessage("calend_hours")."',
	'minute_title': '".GetMessage("calend_minutes")."',
	'second_title': '".GetMessage("calend_seconds")."',
	'set_time': '".GetMessage("calend_set_time")."',
	'clear_time': '".GetMessage("calend_reset_time")."',
	'error_fld': '".GetMessage('calend_error_fld')."'
};
</script>".
"<script type=\"text/javascript\" src=\"".$resource_path.'/calendar.js'.'?'.filemtime($_SERVER["DOCUMENT_ROOT"].$resource_path.'/calendar.js')."\"></script>";
	echo $str;
}

$this->IncludeComponentTemplate();
?>