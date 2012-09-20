<?
define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

require_once(dirname(__FILE__)."/../include/prolog_before.php");
IncludeModuleLangFile(__FILE__);

class CClock
{
	function Init(&$arParams)
	{
		if (!isset($arParams['inputId']))
			$arParams['inputId'] = 'bxclock_'.rand();
		if (!isset($arParams['inputName']))
			$arParams['inputName'] = $arParams['inputId'];
		if (!isset($arParams['step']))
			$arParams['step'] = 5;
		if ($arParams['view'] == 'select' && $arParams['step'] < 30)
			$arParams['step'] = 30;
		$arParams['view'] = 'input';
	}
	
	function Show($arParams)
	{
		CClock::Init($arParams);
		// Show input
		switch ($arParams['view'])
		{
			case 'label':
				?>
				<input type="hidden" id="<?=$arParams['inputId']?>" name="<?=$arParams['inputName']?>"  value="<?=$arParams['initTime']?>">
				<div id=class="bx-clock-label" onmouseover="this.className='bx-clock-label-over';" onmouseout="this.className='bx-clock-label';" onclick=""><? echo $arParams['initTime'] ? $arParams['initTime'] : 'Time'; ?></div><?
				break;
			case 'select':
				?>
				<select id="<?=$arParams['inputId']?>" name="<?=$arParams['inputName']?>">
					<?
						for ($i = 0; $i < 24; $i++)
						{
							$h = ($i < 10) ? '0'.$i : $i;
							?><option value="<?=$h?>:00"><?=$h?>:00</option><?
							if ($arParams['step']) {?><option value="<?=$h?>:30"><?=$h?>:30</option><?}
						}
					?>
				</select>
				<?
				break;
			default: //input
				?><input id="<?=$arParams['inputId']?>" name="<?=$arParams['inputName']?>" type="text" value="<?=$arParams['initTime']?>" size="4" title="<?=$arParams['inputTitle']?>" /><?
				break;
		}
		// Show icon
		if ($arParams['showIcon'] !== false)
		{
			?><a href="javascript:void(0);" onclick="bxShowClock_<?=$arParams['inputId']?>()" title="<?=GetMessage('BX_CLOCK_TITLE')?>" onmouseover="this.className='bxc-icon-hover';" onmouseout="this.className='';"><img id="<?=$arParams['inputId']?>_icon" src="/bitrix/images/1.gif" class="bx-clock-icon bxc-iconkit-c"></a><?
		}

		//Init JS and append CSS
		?><script>
		function bxc_load_css()
		{
			if (!window.BXClockStyles)
				window.BXClockStyles = jsUtils.loadCSSFile(['/bitrix/themes/.default/clock.css']);
		}
		if (!window.phpVars)
			phpVars = {ADMIN_THEME_ID:'.default'};
		if (!window.jsUtils)
		{
			setTimeout(function(){
				var oSript = document.body.appendChild(document.createElement('script'));
				oSript.src = '/bitrix/js/main/utils.js';
				if (document.attachEvent && navigator.userAgent.toLowerCase().indexOf('opera') == -1)
					oSript.onreadystatechange = function(){if (oSript.readyState == 'loaded'){bxc_load_css();}};
				else
					oSript.onload = function(){setTimeout(bxc_load_css, 50);};
			}, 50);
		}
		else
		{
			bxc_load_css();
		}

		function bxShowClock_<?=$arParams['inputId']?>()
		{
			if (!window.JCClock)
				return jsUtils.loadJSFile(['/bitrix/js/main/clock.js'], bxShowClock_<?=$arParams['inputId']?>);

			var obId = 'bxClock_<?=$arParams['inputId']?>';
			if (!window[obId])
				window[obId] = new JCClock({
					step: <?=$arParams['step']?>,
					initTime: '<?=$arParams['initTime']?>',
					showIcon: <? echo $arParams['showIcon'] ? 'true' : 'false';?>,
					inputId: '<?=$arParams['inputId']?>',
					iconId: '<?=$arParams['inputId'].'_icon'?>',
					AmPmMode: <? echo $arParams['am_pm_mode'] ? 'true' : 'false';?>,
					MESS: {
						Insert: '<?=GetMessage('BX_CLOCK_INSERT')?>',
						Close: '<?=GetMessage('BX_CLOCK_CLOSE')?>',
						Hours: '<?=GetMessage('BX_CLOCK_HOURS')?>',
						Minutes: '<?=GetMessage('BX_CLOCK_MINUTES')?>',
						Up: '<?=GetMessage('BX_CLOCK_UP')?>',
						Down: '<?=GetMessage('BX_CLOCK_DOWN')?>'
					}
				});
			window[obId].Show();
		}
		</script><?
	}
}
?>