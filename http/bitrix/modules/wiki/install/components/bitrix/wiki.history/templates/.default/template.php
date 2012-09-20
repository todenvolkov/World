<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>

<div id="wiki-post">
<?if(strlen($arResult['MESSAGE'])>0):
	?>
	<div class="wiki-notes">
		<div class="wiki-note-text">
			<?=$arResult['MESSAGE']?>
		</div>
	</div>
	<?
endif;?>
<?if(!empty($arResult['FATAL_MESSAGE'])):
	?>
	<div class="wiki-errors wiki-note-box wiki-note-error">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
else:
	?>
	<div id="wiki-post-content">    
	<? 
	if (empty($arResult['HISTORY'])):     
		ShowNote(GetMessage('WIKI_HISTORY_NOT_FIND'));        
	else:    		
		if ($arResult['SOCNET']) :
			$APPLICATION->IncludeComponent('bitrix:main.user.link',
				'',
				array(
					'AJAX_ONLY' => 'Y',	
					'PATH_TO_SONET_USER_PROFILE' => str_replace('#user_id#', '#ID#', $arResult['PATH_TO_USER']),
					'PATH_TO_SONET_MESSAGES_CHAT' => $arResult['PATH_TO_SONET_MESSAGES_CHAT'],                    
					'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],                    
					'SHOW_LOGIN' => $arResult['SHOW_LOGIN'],                    
					'PATH_TO_CONPANY_DEPARTMENT' => $arResult['PATH_TO_CONPANY_DEPARTMENT'],                    
					'PATH_TO_VIDEO_CALL' => $arResult['PATH_TO_VIDEO_CALL']				
				),
				$component,
				array('HIDE_ICONS' => 'Y')							
			);
		endif;	
		
		$arHeaders = array(
			array('id' => 'LOGIN', 'name' => GetMessage('WIKI_COLUMN_AUTHOR'), 'sort' => false, 'default' => true), 
			array('id' => 'DATE', 'name' => GetMessage('WIKI_COLUMN_DATE_CREATE'), 'sort' => false, 'default' => true), 
		); 
                
		foreach($arResult['HISTORY'] as $sKey =>  $arHistory)  
		{
			$arResult['HISTORY'][$sKey]['ANCHOR_ID'] = RandString(8);

			$_arData = array(
				'LOGIN' => !empty($arHistory['USER_LINK']) ? '<a href="'.$arHistory['USER_LINK'].'" id="anchor_'.$arResult['HISTORY'][$sKey]['ANCHOR_ID'].'">'.$arHistory['USER_LOGIN'].'</a>' : $arHistory['USER_LOGIN'], 
				'DATE' => $arHistory['MODIFIED']
			);                
		    
			$arActions = array();
			$arActions[] =  array(
				'TITLE' => GetMessage('WIKI_VERSION_TITLE'),
				'TEXT' => GetMessage('WIKI_VERSION'),
				'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arHistory['SHOW_LINK'])."');"
			);
			$arActions[] =  array(
				'TITLE' => GetMessage('WIKI_RECOVER_TITLE'),
				'TEXT' => GetMessage('WIKI_RECOVER'),
				'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arHistory['CANCEL_LINK'])."');"
			);  
			$arActions[] =  array('SEPARATOR' => 'true'); 
			if (!empty($arHistory['PREV_LINK']))
			{
				$arActions[] =  array(
					'TITLE' => GetMessage('WIKI_PREV_VERSION_TITLE'),
					'TEXT' => GetMessage('WIKI_PREV_VERSION'),
					'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arHistory['PREV_LINK'])."');"
				);  
			}
		    
			if (!empty($arHistory['CUR_LINK']))
			{
				$arActions[] =  array(
					'TITLE' => GetMessage('WIKI_CURR_VERSION_TITLE'),
					'TEXT' => GetMessage('WIKI_CURR_VERSION'),
					'ONCLICK' =>  "jsUtils.Redirect([], '".CUtil::JSEscape($arHistory['CUR_LINK'])."');"
				);  
			}                     
			$arResult["GRID_DATA"][] = array(
				'id' => $arHistory['ID'],
				'actions' => $arActions,
				'data' => $_arData,
				'editable' => 'N'
			);             
		}        
                
		$APPLICATION->IncludeComponent(
			'bitrix:main.interface.grid',
			'',
			array(
				'GRID_ID' => 'WIKI_HISTORY',
				'HEADERS' => $arHeaders, 
				'SORT' => array($by => $order),
				'ROWS' => $arResult['GRID_DATA'],
				'FOOTER' => array(array('title' => GetMessage('WIKI_ALL'), 'value' => $arResult['DB_LIST']->SelectedRowsCount())),
				'EDITABLE' => 'Y',
				'ACTIONS' => array(
				'custom_html' => "
					<input type=\"hidden\" name=\"".$arResult['PAGE_VAR']."\" value=\"".$arResult['ELEMENT']['NAME']."\">		
					<input type=\"hidden\" name=\"".$arResult['OPER_VAR']."\" value=\"history_diff\">
					<input type=\"submit\" value=\"".GetMessage('WIKI_DIFF_VERSION')."\"/>"
				),
				'ACTION_ALL_ROWS' => false,
				'NAV_OBJECT' => $arResult['DB_LIST'],
				'AJAX_MODE' => 'N',
			),
			$component
		);
        
		if ($arResult['SOCNET']): 
			foreach($arResult["HISTORY"] as $arHistory)  
			{
				?>
				<script type="text/javascript">
				 BX.tooltip(<?=$arHistory["USER_ID"]?>, "anchor_<?=$arHistory['ANCHOR_ID']?>", "<?=CUtil::JSEscape($arResult['AJAX_PAGE'])?>");
				</script>                        
				<?        
			}
		endif;  
		?>    
		<script type="text/javascript">

			BX('WIKI_HISTORY_check_all').style.visibility = 'hidden';
			document.forms['form_WIKI_HISTORY'].action = '<?=$arResult['PATH_TO_HISTORY_DIFF']?>';
			var inp = document.forms['form_WIKI_HISTORY'].elements;
			for(var i = 0; i < inp.length; i++)
			{
				if (inp[i].type == 'submit' && inp[i].name == 'apply')
					inp[i].style.visibility = 'hidden';
				
				if (inp[i].type == 'checkbox' && inp[i].id.indexOf('ID_') == 0)
				{
					inp[i].title = '<?=CUTIL::JSEscape(GetMessage('WIKI_SELECT_DIFF'))?>';
					BX.bind(inp[i], 'click', function() {
						var j = 0;
						var inp = document.forms['form_WIKI_HISTORY'].elements;
						for(var i = 0; i < inp.length; i++)
						{
							if (inp[i].type == 'checkbox' && inp[i].id.indexOf('ID_') == 0 && inp[i].checked)
								j++;							
						}	

						if ((j >= 2 && this.checked) || !this.checked)
						{
							for(var i = 0; i < inp.length; i++)
							{
								if (inp[i].type == 'checkbox' && inp[i].id.indexOf('ID_') == 0 && !inp[i].checked)
									inp[i].disabled = this.checked;				
							}
						}
					});
				}
			}              
		</script>                 
	<? endif;?>
	</div>
<? endif;?>  
</div>