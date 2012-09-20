<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if (!$USER->CanDoOperation('fileman_view_file_structure') && !$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

function _addslashes($str)
{
	return CUtil::JSEscape($str);
	$pos = strpos(strtolower($str), "script");
	if ($pos !== FALSE)
		$str = str_replace("</script","</ script",$str);

	$pos2 = strpos(strtolower($str), "\n");
	if ($pos2!==FALSE)
	{
		$str = str_replace("\r","",$str);
		$str = str_replace("\n","\\n",$str);
	}
	return CUtil::addslashes($str);
}

function GetProperties($componentName, $curTemplate = '')
{
	$stid = (isset($_GET['stid'])) ? $_GET['stid'] : '';
	$curTemplate = (!$curTemplate || $curTemplate == '.default') ? '' : _addslashes($curTemplate);
	$arTemplates = CComponentUtil::GetTemplatesList($componentName, $stid);
	$arCurVals = isset($_POST['curval']) ? CEditorUtils::UnJSEscapeArray($_POST['curval']) : Array();
	$loadHelp = (isset($_GET['loadhelp']) && $_GET['loadhelp']=="Y") ? true : false;
	foreach ($arTemplates as $k => $arTemplate)
	{
		push2arComp2Templates($arTemplate['NAME'], $arTemplate['TEMPLATE'], $arTemplate['TITLE'], $arTemplate['DESCRIPTION']);
		$tName = (!$arTemplate['NAME'] || $arTemplate['NAME'] == '.default') ? '' : $arTemplate['NAME'];
		if ($tName == $curTemplate)
		{
			$arTemplateProps = CComponentUtil::GetTemplateProps($componentName, $arTemplate['NAME'], $stid, $arCurVals);
			foreach ($arTemplateProps as $k => $arTemplateProp)
				push2arComp2TemplateProps($componentName,$k,$arTemplateProp);
		}
	}

	$arProps = CComponentUtil::GetComponentProps($componentName, $arCurVals);
	if ($loadHelp)
		fetchPropsHelp($componentName);

	$bGroup = (isset($arProps['GROUPS']) && count($arProps['GROUPS'])>0);

	if (is_array($arProps['PARAMETERS']))
		foreach ($arProps['PARAMETERS'] as $k => $arParam)
			push2arComp2Props($k, $arParam, (($bGroup) ? $arProps['GROUPS'] : false));
}


function fetchPropsHelp($componentName_)
{
	$componentName = str_replace("..", "", $componentName_);
	$componentName = str_replace(":", "/", $componentName);
	$lang = preg_replace("/[^a-zA-Z0-9_]/is", "", $_GET["lang"]);
	$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/".$componentName."/help/.tooltips.php";
	$lang_path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/".$componentName."/lang/".$lang."/help/.tooltips.php";
	global $MESS;

	if(file_exists($lang_path) && file_exists($path))
	{
		@include($lang_path);
		@include($path);
	}

	if (!is_array($arTooltips))
		return;

	?>var arTT = {};<?
	foreach($arTooltips as $propName => $tooltip)
	{
		?>arTT["<?=$propName?>"] = '<?=_addslashes($tooltip);?>';<?
	}
	?>window.arComp2Tooltips["<?=$componentName_?>"] = arTT;<?
}

function push2arComp2Props($name, $arParam, $arGroup)
{
	?>

var p = {};
p.param_name = '<?=_addslashes($name);?>';
<?
	if ($arGroup!==false && isset($arParam['PARENT']))
	{
		foreach ($arGroup as $k =>$group)
		{
			if ($k == $arParam['PARENT'])
			{
				?>p.group_title = '<?= (($group['NAME']) ? _addslashes($group['NAME']) : $k);?>';<?
				echo "\n";
				break;
			}
		}
	}
	foreach ($arParam  as $k => $prop)
	{
		if ($k == 'TYPE' && $prop == 'FILE')
			$GLOBALS['arFD'][] = Array(
				'NAME' => _addslashes($name),
				'TARGET' => isset($arParam['FD_TARGET']) ? $arParam['FD_TARGET'] : 'F',
				'EXT' => isset($arParam['FD_EXT']) ? $arParam['FD_EXT'] : '',
				'UPLOAD' => isset($arParam['FD_UPLOAD']) && $arParam['FD_UPLOAD'] && $arParam['FD_TARGET'] == 'F',
				'USE_ML' => isset($arParam['FD_USE_MEDIALIB']) && $arParam['FD_USE_MEDIALIB'],
				'ONLY_ML' => isset($arParam['FD_USE_ONLY_MEDIALIB']) && $arParam['FD_USE_ONLY_MEDIALIB'],
				'ML_TYPES' => isset($arParam['FD_MEDIALIB_TYPES']) ? $arParam['FD_MEDIALIB_TYPES'] : false
			);
		elseif (in_array($k, Array('FD_TARGET', 'FD_EXT','FD_UPLOAD', 'FD_MEDIALIB_TYPES', 'FD_USE_ONLY_MEDIALIB')))
			continue;

		if (is_array($prop))
		{
			?>p.<? echo$k;?> = {<?
					echo "\n";
					$i = true;
					foreach ($prop as $k2 => $prop_)
					{
						if (!$i)
							echo ",\n";
						else
							$i = false;

						if ($k2 === 'JS_DATA')
							echo "'JS_DATA' : '".CUtil::JSEscape($prop_)."'";
						else
							echo '\''._addslashes($k2).'\' : \''._addslashes($prop_).'\'';
					}
				echo "\n";
				?>}<?
		}
		else
		{
			?>p.<? echo$k;?> = '<? echo _addslashes($prop);?>';<?
		}
		echo "\n";
	}
?>window.arComp2Props.push(p);<?
}


function push2arComp2Templates($name,$template,$title,$description)
{
?>
window.arComp2Templates.push({
name : '<?=$name;?>',
template : '<?=$template;?>',
title	 : '<?=_addslashes($title);?>',
description : '<?=_addslashes($description);?>'
});
<?
}


function push2arComp2TemplateProps($componentName, $paramName, $arParam)
{
	?>var p2 = {param_name: '<?=_addslashes($paramName)?>'};
<?
	foreach ($arParam  as $k => $prop)
	{
		if ($k == 'TYPE' && $prop == 'FILE')
		{
			$GLOBALS['arFD'][] = Array(
				'NAME' => _addslashes($name),
				'TARGET' => isset($arParam['FD_TARGET']) ? $arParam['FD_TARGET'] : 'F',
				'EXT' => isset($arParam['FD_EXT']) ? $arParam['FD_TARGET'] : '',
				'UPLOAD' => isset($arParam['FD_UPLOAD']) ? $arParam['FD_UPLOAD'] : true,
				'USE_ML' => isset($arParam['FD_USE_MEDIALIB']) && $arParam['FD_USE_MEDIALIB'],
				'ONLY_ML' => isset($arParam['FD_USE_ONLY_MEDIALIB']) && $arParam['FD_USE_ONLY_MEDIALIB'],
				'ML_TYPES' => isset($arParam['FD_MEDIALIB_TYPES']) ? $arParam['FD_MEDIALIB_TYPES'] : false
			);
		}
		elseif (in_array($k, Array('FD_TARGET', 'FD_EXT','FD_UPLOAD')))
			continue;

		if (is_array($prop))
		{
?>p2.<? echo$k;?> = {<?
		echo "\n";
				$i=true;
				foreach ($prop as $k2 => $prop_)
				{
					if (!$i)
						echo",\n";
					else
						$i = false;

					echo '\''._addslashes($k2).'\' : \''._addslashes($prop_).'\'';
				}
			echo "\n";
?>}<?
		}
		else
		{
?>p2.<?= _addslashes($k)?> = '<?= _addslashes($prop)?>';<?
		}
		echo "\n";
	}
?>window.arComp2TemplateProps.push(p2);<?
}


function ShowFileDialogsScripts()
{
	global $arFD;
	$l = count($arFD);
	if ($l < 1)
		return;


	for($i = 0; $i < $l; $i++)
	{
		if ($arFD[$i]['USE_ML'])
		{
			$MLRes = CMedialib::ShowBrowseButton(
				array(
					'mode' => $arFD[$i]['ONLY_ML'] ? 'medialib' : 'select',
					'value' => '...',
					'event' => "BX_FD_".$arFD[$i]['NAME'],
					'id' => "bx_fd_input_".strtolower($arFD[$i]['NAME']),
					'MedialibConfig' => array(
						"event" => "bx_ml_event_".$arFD[$i]['NAME'],
						"arResultDest" => Array("FUNCTION_NAME" => "BX_FD_ONRESULT_".$arFD[$i]['NAME']),
						"types" => $arFD[$i]['ML_TYPES']
					),
					'bReturnResult' => true
				)
			);
			?>
			<script>window._bxMlBrowseButton_<?= strtolower($arFD[$i]['NAME'])?> = '<?= CUtil::JSEscape($MLRes)?>';</script>
			<?
		}
		CAdminFileDialog::ShowScript(Array
		(
			"event" => "BX_FD_".$arFD[$i]['NAME'],
			"arResultDest" => Array("FUNCTION_NAME" => "BX_FD_ONRESULT_".$arFD[$i]['NAME']),
			"arPath" => Array(),
			"select" => $arFD[$i]['TARGET'], // F - file only, D - folder only, DF - files & dirs
			"operation" => 'O',
			"showUploadTab" => $arFD[$i]['UPLOAD'],
			"showAddToMenuTab" => false,
			"fileFilter" => $arFD[$i]['EXT'],
			"allowAllFiles" => true,
			"SaveConfig" => true
		));
	}
}
?>
<script>
window.arComp2Templates = [];
window.arComp2Props = [];
window.arComp2TemplateProps = [];
<?
$arFD = Array();
if (isset($_GET['cname']))
	GetProperties($_GET['cname'], $_GET['tname']);
?>
</script>
<?
ShowFileDialogsScripts();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");?>