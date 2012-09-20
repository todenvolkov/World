<?
IncludeModuleLangFile(__FILE__);
class CLightHTMLEditor // LHE
{
	var $ownerType;
	function Init(&$arParams)
	{
		global $USER;
		$basePath = '/bitrix/js/fileman/light_editor/';
		$this->Id = (isset($arParams['id']) && strlen($arParams['id']) > 0) ? $arParams['id'] : 'bxlhe'.substr(uniqid(mt_rand(), true), 0, 4);
		$this->cssPath = $this->GetActualPath($basePath.'light_editor.css');
		$this->arJSPath = array(
			$this->GetActualPath($basePath.'le_dialogs.js'),
			$this->GetActualPath($basePath.'le_controls.js'),
			$this->GetActualPath($basePath.'le_toolbarbuttons.js'),
			$this->GetActualPath($basePath.'le_core.js')
		);

		$this->bBBCode = $arParams['BBCode'] === true;
		//if ($this->bBBCode)
			$this->arJSPath[] = $this->GetActualPath($basePath.'le_bb.js');

		$arJS = Array();
		$arCSS = Array();
		$events = GetModuleEvents("fileman", "OnBeforeLightEditorScriptsGet");
		while($arEvent = $events->Fetch())
		{
			$tmp = ExecuteModuleEventEx($arEvent, array($this->Id, $arParams));
			if (!is_array($tmp))
				continue;

			if (is_array($tmp['JS']))
			{
				for($i = 0, $c = count($tmp['JS']); $i < $c; $i++)
				{
					if(file_exists($_SERVER['DOCUMENT_ROOT'].$tmp['JS'][$i]))
						$this->arJSPath[] = $this->GetActualPath($tmp['JS'][$i]);
				}
			}
		}

		$this->bAutorized = is_object($USER) && $USER->IsAuthorized();
		$this->bUseFileDialogs = $arParams['bUseFileDialogs'] !== false && $this->bAutorized;
		$this->bUseMedialib = $arParams['bUseMedialib'] !== false && COption::GetOptionString('fileman', "use_medialib", "Y") == "Y" && CMedialib::CanDoOperation('medialib_view_collection', 0);

		$this->bResizable = $arParams['bResizable'] === true;
		$this->bAutoResize = $arParams['bAutoResize'] !== false;
		$this->bInitByJS = $arParams['bInitByJS'] === true;
		$this->bSaveOnBlur = $arParams['bSaveOnBlur'] !== false;
		$this->content = $arParams['content'];
		$this->inputName = isset($arParams['inputName']) ? $arParams['inputName'] : 'lha_content';
		$this->inputId = isset($arParams['inputId']) ? $arParams['inputId'] : 'lha_content_id';
		$this->videoSettings = is_array($arParams['videoSettings']) ? $arParams['videoSettings'] : array(
				'maxWidth' => 640,
				'maxHeight' => 480,
				'WMode' => 'transparent',
				'windowless' => true,
				'bufferLength' => 20,
				'skin' => '/bitrix/components/bitrix/player/mediaplayer/skins/bitrix.swf',
				'logo' => ''
			);

		if (!is_array($arParams['arFonts']) || count($arParams['arFonts']) <= 0)
			$arParams['arFonts'] = array('Arial', 'Verdana', 'Times New Roman', 'Courier', 'Tahoma', 'Georgia', 'Optima', 'Impact', 'Geneva', 'Helvetica');

		if (!is_array($arParams['arFontSizes']) || count($arParams['arFontSizes']) <= 0)
			$arParams['arFontSizes'] = array('1' => 'xx-small', '2' => 'x-small', '3' => 'small', '4' => 'medium', '5' => 'large', '6' => 'x-large', '7' => 'xx-large');

		// Tables
		//$this->arJSPath[] = $this->GetActualPath($basePath.'le_table.js');
		$this->jsObjName = (isset($arParams['jsObjName']) && strlen($arParams['jsObjName']) > 0) ? $arParams['jsObjName'] : 'LightHTMLEditor'.$this->Id;

		if ($this->bResizable)
		{
			// Get height user settings
			$userOpt = CUserOptions::GetOption(
				'fileman',
				'LHESize_'.$this->Id,
				array('height' => $arParams['height'])
			);
			$arParams['height'] = $userOpt['height'];
		}

		$this->JSConfig = array(
			'id' => $this->Id,
			'content' => $this->content,
			'bBBCode' => $this->bBBCode,
			'bUseFileDialogs' => $this->bUseFileDialogs,
			'bUseMedialib' => $this->bUseMedialib,
			'arSmiles' => $arParams['arSmiles'],
			'arFonts' => $arParams['arFonts'],
			'arFontSizes' => $arParams['arFontSizes'],
			'inputName' => $this->inputName,
			'inputId' => $this->inputId,
			'videoSettings' => $this->videoSettings,
			'bSaveOnBlur' => $this->bSaveOnBlur,
			'bResizable' => $this->bResizable,
			'bAutoResize' => $this->bAutoResize,
			'bReplaceTabToNbsp' => true,
			'bSetDefaultCodeView' => isset($arParams['bSetDefaultCodeView']) && $arParams['bSetDefaultCodeView'],
			'bBBParseImageSize' => isset($arParams['bBBParseImageSize']) && $arParams['bBBParseImageSize'],
			'smileCountInToolbar' => intVal($arParams['smileCountInToolbar']),
			'bQuoteFromSelection' => $arParams['bQuoteFromSelection'],
			'bConvertContentFromBBCodes' => $arParams['bConvertContentFromBBCodes'],
			'oneGif' => '/bitrix/images/1.gif',
			'imagePath' => '/bitrix/images/fileman/light_htmledit/'
		);

		if (isset($arParams['width']) && intVal($arParams['width']) > 0)
			$this->JSConfig['width'] = $arParams['width'];
		if (isset($arParams['height']) && intVal($arParams['height']) > 0)
			$this->JSConfig['height'] = $arParams['height'];
		if (isset($arParams['toolbarConfig']))
			$this->JSConfig['toolbarConfig'] = $arParams['toolbarConfig'];
			
		if ($this->bBBCode)
		{
			$this->JSConfig['bParceBBImageSize'] = true;
		}

		if (isset($arParams['ctrlEnterHandler']))
			$this->JSConfig['ctrlEnterHandler'] = $arParams['ctrlEnterHandler'];
	}

	function GetActualPath($path)
	{
		return $path.'?v='.@filemtime($_SERVER['DOCUMENT_ROOT'].$path);
	}

	function Show($arParams)
	{
		CUtil::InitJSCore(array('window', 'ajax'));
		$this->Init($arParams);
		$this->BuildSceleton();
		$this->InitLangMess();
		$this->InitScripts();

		if ($this->bUseFileDialogs)
			$this->InitFileDialogs();

		if ($this->bUseMedialib)
			$this->InitMedialibDialogs();
	}

	function BuildSceleton()
	{
		?>
		<img src="/bitrix/images/1.gif" width="300" id="bxlhe_ww_<?=$this->Id?>" />
<div class="bxlhe-frame" id="bxlhe_frame_<?=$this->Id?>"><table class="bxlhe-frame-table">
		<tr><td class="bxlhe-editor-buttons"><div class="lhe-stat-toolbar-cont"></div></td></tr>
		<tr><td class="bxlhe-editor-cell"></td></tr>
		<?if ($this->bResizable):?>
		<tr><td class="lhe-resize-row"><img id="bxlhe_resize_<?=$this->Id?>" src="/bitrix/images/1.gif"/></td></tr>
		<?endif;?>
</table></div>
		<?
	}

	function InitScripts()
	{
		ob_start();
		$db_events = GetModuleEvents("fileman", "OnIncludeLightEditorScript");
		while($arEvent = $db_events->Fetch())
			ExecuteModuleEventEx($arEvent, array($this->Id));

		$scripts = trim(ob_get_contents());
		ob_end_clean();

		$scripts = str_replace("<script>", "", $scripts);
		$scripts = str_replace("</script>", "", $scripts);

		?>
		<script>
		function LoadLHE_<?=$this->Id?>()
		{
			// Load css
			if (!window.BXLHEStyles)
			{
				BX.loadCSS('<?=$this->cssPath?>');
				window.BXLHEStyles = true;
			}

			var arScripts = [<?for ($i = 0, $l = count($this->arJSPath); $i < $l; $i++){echo '\''.$this->arJSPath[$i].'\''.($i == $l - 1 ? '' : ',');}?>];
			BX.loadScript(arScripts, function()
			{
				// Place to add user script
				try{
				<?= $scripts?>
				}catch(e){alert('Errors in customization scripts! ' + e);}

				setTimeout(function()
				{
					window.<?=$this->jsObjName?> = new window.JCLightHTMLEditor(<?=CUtil::PhpToJSObject($this->JSConfig)?>);
				}, 100);
			});
		}

		<?if(!$this->bInitByJS):?>
		BX.ready(function()
		{
			var ww = BX('bxlhe_ww_<?=$this->Id?>');
			if (ww)
				BX.showWait(ww, '<?= GetMessage('FILEMAN_LHE_WAIT')?>');
			setTimeout(LoadLHE_<?=$this->Id?>, 50)
		});
		<?endif;?>

		</script><?
	}

	function InitLangMess()
	{
		$langPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/fileman/lang/'.LANGUAGE_ID.'/classes/general/light_editor_js.php';
		if(file_exists($langPath))
			include($langPath);
		else
			$langPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/fileman/lang/en/classes/general/light_editor_js.php';

		?><script>LHE_MESS = window.LHE_MESS = <?=CUtil::PhpToJSObject($MESS)?>;</script><?
	}

	function InitFileDialogs()
	{
		// Link
		CAdminFileDialog::ShowScript(Array(
			"event" => "LHED_Link_FDOpen",
			"arResultDest" => Array("ELEMENT_ID" => "lhed_link_href"),
			"arPath" => Array("SITE" => SITE_ID),
			"select" => 'F',
			"operation" => 'O',
			"showUploadTab" => true,
			"showAddToMenuTab" => false,
			"fileFilter" => 'php, html',
			"allowAllFiles" => true,
			"SaveConfig" => true
		));

		// Image
		CAdminFileDialog::ShowScript(Array
		(
			"event" => "LHED_Img_FDOpen",
			"arResultDest" => Array("FUNCTION_NAME" => "LHED_Img_SetUrl"),
			"arPath" => Array("SITE" => SITE_ID),
			"select" => 'F',
			"operation" => 'O',
			"showUploadTab" => true,
			"showAddToMenuTab" => false,
			"fileFilter" => 'image',
			"allowAllFiles" => true,
			"SaveConfig" => true
		));

		// video path
		CAdminFileDialog::ShowScript(Array
		(
			"event" => "LHED_VideoPath_FDOpen",
			"arResultDest" => Array("FUNCTION_NAME" => "LHED_Video_SetPath"),
			"arPath" => Array("SITE" => SITE_ID),
			"select" => 'F',
			"operation" => 'O',
			"showUploadTab" => true,
			"showAddToMenuTab" => false,
			"fileFilter" => 'wmv,wma,flv,vp6,mp3,mp4,aac,jpg,jpeg,gif,png',
			"allowAllFiles" => true,
			"SaveConfig" => true
		));

		// video preview
		CAdminFileDialog::ShowScript(Array
		(
			"event" => "LHED_VideoPreview_FDOpen",
			"arResultDest" => Array("ELEMENT_ID" => "lhed_video_prev_path"),
			"arPath" => Array("SITE" => SITE_ID),
			"select" => 'F',
			"operation" => 'O',
			"showUploadTab" => true,
			"showAddToMenuTab" => false,
			"fileFilter" => 'image',
			"allowAllFiles" => true,
			"SaveConfig" => true
		));
	}

	function InitMedialibDialogs()
	{
		CMedialib::ShowDialogScript(array(
			"event" => "LHED_Img_MLOpen",
			"arResultDest" => Array("FUNCTION_NAME" => "LHED_Img_SetUrl")
		));
		CMedialib::ShowDialogScript(array(
			"event" => "LHED_Video_MLOpen",
			"arResultDest" => Array("FUNCTION_NAME" => "LHED_Video_SetPath")
		));
	}
}
?>