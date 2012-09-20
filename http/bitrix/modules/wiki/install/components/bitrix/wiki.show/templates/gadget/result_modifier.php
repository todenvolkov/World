<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!array_key_exists("TEXT_LIMIT", $arParams) || $arParams["TEXT_LIMIT"] <= 0)
	$arParams["TEXT_LIMIT"] = 500;
	
function strip_words($string, $count)
{
	$result = "";
	$counter_plus  = true;
	$counter = 0;
	$string_len = strlen($string);
	for($i=0; $i<$string_len; ++$i)
	{
		$char = substr($string, $i, 1);
		if($char == '<') 
			$counter_plus = false;
		if($char == '>' && substr($string, $i+1, 1) != '<')
		{
			$counter_plus = true;
			$counter--;
		}
		$result .= $char;
		if ($counter_plus) 
			$counter++;
		if($counter >= $count) 
		{
			$pos_space = strpos($string, " ", $i);
			$pos_tag = strpos($string, "<", $i);
			if ($pos_space == false) 
			{
				$pos = strrpos($result, " ");
				$result = substr($result, 0, strlen($result)-($i-$pos+1));
            } 
			else 
			{
				$pos = min($pos_space, $pos_tag);
				if ($pos != $i) 
				{
					$dop_str = substr($string, $i+1, $pos-$i-1);
					$result .= $dop_str;
                } 
				else 
					$result = substr($result, 0, strlen($result)-1);
			}
            break;
		}
	}
	return $result;
}

function closetags($html)
{
	preg_match_all("#<([a-z0-9]+)([^>]*)(?<!/)>#i".BX_UTF_PCRE_MODIFIER, $html, $result);
	$openedtags = $result[1];

	preg_match_all("#</([a-z0-9]+)>#i".BX_UTF_PCRE_MODIFIER, $html, $result);
	$closedtags = $result[1];
	$len_opened = count($openedtags);
 
	if(count($closedtags) == $len_opened)
		return $html;

	$openedtags = array_reverse($openedtags);
 
	for($i=0;$i<$len_opened;$i++) 
	{
		if (!in_array($openedtags[$i], $closedtags))
			$html .= '</'.$openedtags[$i].'>';
		else 
			unset($closedtags[array_search($openedtags[$i], $closedtags)]);
	}
	
	return $html;
}
   
function html_cut($html, $size) 
{
	$symbols = strip_tags($html);
	$symbols_len = strlen($symbols);

	if($symbols_len < strlen($html)) 
	{
		$strip_text = strip_words($html, $size);

		if($symbols_len > $size) 
			$strip_text = $strip_text."...";

		$final_text = closetags($strip_text);
	}
	else
		$final_text = substr($html, 0, $size);

	return $final_text;
}

$arResult["ELEMENT"]["DETAIL_TEXT"] = html_cut($arResult["ELEMENT"]["DETAIL_TEXT"], $arParams["TEXT_LIMIT"]) ;
?>