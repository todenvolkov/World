<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2005 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if (!($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files')) || !check_bitrix_sessid())
	die();

$HTTP_USER_AGENT = "";
$HTTP_ACCEPT_ENCODING = "";

function _replacer($str)
{
	$str = CFileMan::SecurePathVar($str);
	$str = preg_replace("/[^a-zA-Z0-9_\.-\+]/i", "_", $str);
	return $str;
}


function callback($buffer)
{
	global $list;
	$filename = $_SERVER['DOCUMENT_ROOT'].'/styles'._replacer($list).'.xml';
	$handle = fopen($filename, 'wb+');
	fwrite($handle, $buffer);
	fclose($handle);
	return $buffer;
}


ob_start("callback");

$site = CFileMan::SecurePathVar(str_replace("\\", "/", $site));
$template = CFileMan::SecurePathVar(str_replace("\\", "/", $template));

if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".$site."/styles.css"))
	$styles = $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".$site."/styles.css");
elseif(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/php_interface/styles.css"))
	$styles = $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/styles.css");
elseif($template && file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$template."/styles.css"))
	$styles = $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$template."/styles.css");
else
	$styles = $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/.default/styles.css");

if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".$site."/editor.css"))
	$styles .= $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/".$site."/editor.css");
elseif(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/editor.css"))
	$styles .= $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/editor.css");

if($list=="y"):

	$arStyles = Array();
	function bhtml_style_tmp($matches, $matches2)
	{
		global $arStyles;
		$matches2 = trim($matches2);
		if(strlen($matches2)>0)
			$arStyles[] = Array($matches, substr($matches2, 2, -2));
		return "\n";
	}
	$tmp_styles = "\n".$styles."\n";
	preg_replace("'\n\.([A-Z0-9_]+).*?{.*?}.*?((\r\n)|(/\*.*?[\r\n]))?'ies", "bhtml_style_tmp('\\1', '\\2')", $tmp_styles);

	header("Content-type: text/xml");
	echo '<'.'?xml version="1.0" encoding="'.LANG_CHARSET.'"?'.'>';
	echo "\r\n<Styles>";

	for($i=0; $i<count($arStyles); $i++):
		$arSt = $arStyles[$i];
	?>
		<Style name="<?=htmlspecialchars($arSt[1])?>" element="span">
			<Attribute name="class" value="<?=htmlspecialchars($arSt[0])?>" />
		</Style>
	<?endfor;?>
	</Styles>
	<?
else:
	header("Content-type: text/css");
	echo $styles;
endif;

ob_end_flush();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");?>