<?
if(!function_exists("__UnEscape"))
{
	function __UnEscape(&$item, $key)
	{
		if(is_array($item))
			array_walk($item, '__UnEscape');
		elseif (strpos($item, "%u") !== false)
			$item = $GLOBALS["APPLICATION"]->UnJSEscape($item);
		elseif (LANG_CHARSET != "UTF-8" && preg_match("/^.{1}/su", $item) == 1)
			$item = $GLOBALS["APPLICATION"]->ConvertCharset($item, "UTF-8", LANG_CHARSET);
	}
}
if(!function_exists("__Escape"))
{
	function __Escape(&$item, $key)
	{
		if ("UTF-8" != SITE_CHARSET)
		{
			if(is_array($item))
				array_walk($item, '__Escape');
			else
				$item = $GLOBALS["APPLICATION"]->ConvertCharset($item, SITE_CHARSET, "UTF-8");
		}
	}
}

if (!function_exists("__ResizeImage"))
{
	function __ResizeImage(&$File, &$arRealFile, $Sight, $iStrongResize=1, $arWaterMark=array())
	{
		$oImage = new ThumbnailTools($File, $arRealFile);
		if (is_set($arWaterMark, "USER") || is_set($arWaterMark, "ALL")):
			$oImage->CreateThumbnail($Sight, $iStrongResize, true);
			$oImage->CreateWaterMark($arWaterMark["USER"]);
			$oImage->CreateWaterMark($arWaterMark["ALL"]);
		elseif (!empty($arWaterMark["text"]) || !empty($arWaterMark["file"])):
			$oImage->CreateThumbnail($Sight, $iStrongResize, true);
			$oImage->CreateWaterMark($arWaterMark);
		else:
			$oImage->CreateThumbnail($Sight, $iStrongResize);
		endif;
		$oImage->Restore(false);
	}
}

class ThumbnailTools
{
	var $Source = false;
	var $SourceObj = false;
	var $Dest = false;
	var $DestObj = false;
	var $Sight = array();
	var $Watermark = array();
	var $arError = array();
	var $arNote = array();

    function ThumbnailTools(&$File, &$arRealFile)
    {
    	$this->Source = &$arRealFile;
    	$this->Dest = &$File;

		if (function_exists("gd_info")):
			$res = gd_info();
			$this->GD = ((strpos($res['GD Version'], "2.") !== false) ? 2 : 1);
		endif;

		if ($this->GD == false):
			$this->arError[] = array(
				"id" => "gd_lib",
				"text" => "GD library is not installed.");
			return false;
		elseif (!is_array($this->Source) || empty($this->Source["tmp_name"])):
			$this->arError[] = array(
				"id" => "source",
				"text" => "Source file is not defined.");
			return false;
		elseif (!is_array($this->Dest) || empty($this->Dest["tmp_name"])):
			$this->arError[] = array(
				"id" => "dest",
				"text" => "Desctination file is not defined.");
			return false;
		elseif (!CopyDirFiles($this->Source["tmp_name"], $this->Dest["tmp_name"])):
			$this->arError[] = array(
				"id" => "copy",
				"text" => "File is not copied.");
			return false;
		endif;

		if (empty($this->Source["pathinfo"])):
			$this->Source["pathinfo"] = array("extension" => substr($this->Source["name"], strrpos($arRealFile["name"], ".") + 1));
		endif;

		$this->SourceObj = PictureTools::ImageCreate($arRealFile["tmp_name"], $arRealFile["pathinfo"]["extension"]);

		$this->Source["width"] = intVal(@imagesx($this->SourceObj));
		$this->Source["height"] = intVal(@imagesy($this->SourceObj));
    }

    function Restore($bKillSource = false)
    {
		if ($bKillSource == true && $this->SourceObj):
			@imagedestroy($this->SourceObj);
		endif;

    	if (!$this->DestObj)
    		return true;
		switch (strToLower($this->Source["pathinfo"]["extension"]))
		{
			case 'gif':
				@imagegif($this->DestObj, $this->Dest["tmp_name"]);
			break;
			case 'png':
				@imagepng($this->DestObj, $this->Dest["tmp_name"]);
			break;
			default:
				if ($this->Source["pathinfo"]["extension"] == "bmp")
					$this->Dest["name"] = preg_replace("/(\.)bmp$/is", "$1jpg", $this->Dest["name"]);
				imagejpeg($this->DestObj, $this->Dest["tmp_name"]);
			break;
		}
		@imagedestroy($this->DestObj);
		$this->DestObj = false;
		$this->Dest["size"] = filesize($this->Dest["tmp_name"]);
    }

    function CreateThumbnail($Sight, $iStrongResize = 1, $bCreate = false)
    {
		$Sight = (is_array($Sight) ? $Sight : array());
		$Sight["width"] = ($Sight["width"] > 0 ? $Sight["width"] : false);
		$Sight["height"] = ($Sight["height"] > 0 ? $Sight["height"] : false);

		$src = array("x" => 0, "y" => 0, "width" => $this->Source["width"], "height" => $this->Source["height"]);
		$this->Dest["width"] = $this->Source["width"];
		$this->Dest["height"] = $this->Source["height"];

		$bCreate = ($bCreate == true ? true : false);
		$res = PictureTools::ResizeThumbnail($this->Source, $Sight, $iStrongResize);
		if (is_array($res)):
			$src = $res["src"];
			$this->Dest["width"] = $res["dst"]["width"];
			$this->Dest["height"] = $res["dst"]["height"];
			$bCreate = true;
		endif;

		if ($bCreate)
		{
			$this->DestObj = PictureTools::CreateThumbnail($this->SourceObj, $src, $this->Dest);
			return $this->DestObj;
		}

		return true;
    }

    function CreateWaterMark($arWatermark)
    {
    	if (empty($arWatermark)):
    		return true;
    	elseif (empty($arWatermark["text"]) && empty($arWatermark["file"])):
			$this->arNote[] = array(
				"id" => "data",
				"text" => "[Watermak] params is empty.");
			return true;
		elseif (!empty($arWatermark["text"]) && !file_exists($arWatermark["path_to_font"])):
			$this->arNote[] = array(
				"id" => "font",
				"text" => "[Watermark] Font file is empty.");
		elseif (!empty($arWatermark["file"]) && !file_exists($arWatermark["file"])):
			$this->arNote[] = array(
				"id" => "file",
				"text" => "[Watermark] Font file is not found.");
		elseif ($this->Dest["width"] < intVal($arWatermark["min_size_picture"])):
			$this->arNote[] = array(
				"id" => "small size",
				"text" => "[Watermark] Picture is too small.");
			return true;
		elseif (!$this->DestObj):
			$this->arError[] = array(
				"id" => "source",
				"text" => "[Watermark] Destination file is bad.");
			return false;
    	endif;

		if (intVal($arWatermark["coeff"]) <= 0):
			if ($arWatermark["size"] == "big"):
				$arWatermark["coeff"] = 7;
			elseif ($arWatermark["size"] == "small"):
				$arWatermark["coeff"] = 2;
			else:
				$arWatermark["coeff"] = 4;
			endif;
		endif;

		if (!empty($arWatermark["text"]))
			return PictureTools::WatermarkText($this->DestObj, $arWatermark["text"], $arWatermark["path_to_font"], $arWatermark["color"],
				array("coefficient" => $arWatermark["coeff"], "position" => $arWatermark["position"]));
		return PictureTools::WatermarkFile($this->DestObj, $arWatermark["file"],
			array(
				"position" => $arWatermark["position"],
				"alpha_level" => $arWatermark["alpha_level"],
				"type" => $arWatermark["type"]));
    }
}

class PictureTools
{
	function WatermarkText(&$obj, $text, $font, $color, $params = array())
	{
		if (!$obj):
			return false;
		elseif (empty($text)):
			return true;
		elseif (!file_exists($font)):
			return false;
		elseif (PictureTools::__gd() == false):
			return false;
		endif;
		$color = trim($color);
		$params["coefficient"] = intval($params["coefficient"]);
		$params["width"] = intVal(@imagesx($obj));
		$params["height"] = intVal(@imagesy($obj));
		$params["position"] = trim($params["position"]);

		$arColor = array("red" => 255, "green" => 255, "blue" => 255);
		$sColor = preg_replace("/[^a-z0-9]/is", "", $color);
		if (strLen($sColor) == 6):
			$arColor = array("red" => hexdec(substr($sColor, 0, 2)), "green" => hexdec(substr($sColor, 2, 2)), "blue" => hexdec(substr($sColor, 4, 2)));
		endif;

		$iSize = $params["width"] * $params["coefficient"] / 100;
		if ($iSize * strLen($text)*0.7 > $params["width"]):
			$iSize = intVal($params["width"] / (strLen($text)*0.7));
		endif;

		$watermark_position = array("x" => 5, "y" => $iSize + 5,
			"width" => (strLen($text)*0.7 + 1)*$iSize, "height" => $iSize);

		if (PictureTools::__gd() != "2"):
			$watermark_position["width"] = strLen($text)*imagefontwidth(5);
			$watermark_position["height"] = imagefontheight(5);
		endif;

		if (substr($params["position"], 0, 1) == "m"):
			$watermark_position["y"] = intVal(($params["height"] - $watermark_position["height"]) / 2);
		elseif (substr($params["position"], 0, 1) == "b"):
			$watermark_position["y"] = intVal(($params["height"] - $watermark_position["height"]));
		endif;
		if (substr($params["position"], 1, 1) == "c"):
			$watermark_position["x"] = intVal(($params["width"] - $watermark_position["width"]) / 2);
		elseif (substr($params["position"], 1, 1) == "r"):
			$watermark_position["x"] = intVal(($params["width"] - $watermark_position["width"]));
		endif;
		$watermark_position["y"] = ($watermark_position["y"] < 2 ? 2 : $watermark_position["y"]);
		$watermark_position["x"] = ($watermark_position["x"] < 2 ? 2 : $watermark_position["x"]);

		$text_color = imagecolorallocate($obj, $arColor["red"], $arColor["green"], $arColor["blue"]);
		if (PictureTools::__gd() == "2")
		{
			if (function_exists("utf8_encode"))
			{
				$text = $GLOBALS["APPLICATION"]->ConvertCharset($text, SITE_CHARSET, "UTF-8");
				if ($arWatermark["use_copyright"] != "N")
					$text = utf8_encode("&#169;").$text;
			}
			else
			{
				$text = $GLOBALS["APPLICATION"]->ConvertCharset($text, SITE_CHARSET, "UTF-8");
				if ($arWatermark["use_copyright"] != "N")
					$text = "©".$text;
			}
			@imagettftext($obj, $iSize, 0, $watermark_position["x"], $watermark_position["y"], $text_color, $font, $text);
		}
		else
		{
			@imagestring($obj, 3, $watermark_position["x"], $watermark_position["y"], $text, $text_color);
		}
		return true;
	}

    function WatermarkFile(&$obj, $file, $params = array())
    {
		if (!$obj):
			return false;
		elseif (empty($file)):
			return true;
		elseif (!file_exists($file)):
			return false;
		endif;

		$arFile = array(
			"ext" => substr($file, strrpos($file, ".") + 1));
		$params["width"] = intVal(@imagesx($obj));
		$params["height"] = intVal(@imagesy($obj));
		$params["coefficient"] = intval($params["coefficient"]);
		$params["position"] = trim($params["position"]);
		$params["alpha_level"] = intVal($params["alpha_level"]);
		$params["alpha_level"] /= 100;


		$file_obj = false;
		if ($params["type"] == 'resize'):
			$file_obj_1 = PictureTools::ImageCreate($file, $arFile["ext"]);
			$arFile["width"] = intVal(@imagesx($file_obj_1));
			$arFile["height"] = intVal(@imagesy($file_obj_1));
			$res = PictureTools::ResizeThumbnail($arFile, array("width" => $params["width"], "height" => $params["height"]), 'inscribe');
			if (is_array($res)):
				$file_obj = PictureTools::CreateThumbnail($file_obj_1, $res["src"], $res["dst"]);
				@imagedestroy($file_obj_1);
			endif;
		else:
			if ($params["type"] == 'repeat'):
				$params["position"] = "tl";
			endif;
			$file_obj = PictureTools::ImageCreate($file, $arFile["ext"]);
		endif;

		if (!$file_obj):
			return false;
		endif;

		$arFile["width"] = intVal(@imagesx($file_obj));
		$arFile["height"] = intVal(@imagesy($file_obj));

		$watermark_position = array("x" => 0, "y" => 0,
			"width" => $arFile["width"], "height" => $arFile["height"]);

		if (substr($params["position"], 0, 1) == "m"):
			$watermark_position["y"] = intVal(($params["height"] - $watermark_position["height"]) / 2);
		elseif (substr($params["position"], 0, 1) == "b"):
			$watermark_position["y"] = ($params["height"] - $watermark_position["height"]);
		endif;

		if (substr($params["position"], 1, 1) == "c"):
			$watermark_position["x"] = intVal(($params["width"] - $watermark_position["width"]) / 2);
		elseif (substr($params["position"], 1, 1) == "r"):
			$watermark_position["x"] = intVal($params["width"] - $watermark_position["width"]);
		endif;

		$watermark_position["y"] = ($watermark_position["y"] < 0 ? 0 : $watermark_position["y"]);
		$watermark_position["x"] = ($watermark_position["x"] < 0 ? 0 : $watermark_position["x"]);

		for ($y = 0; $y < $arFile["height"]; $y++ )
		{
			for ($x = 0; $x < $arFile["width"]; $x++ )
			{
				$watermark_y = $watermark_position["y"] + $y;
				while (true)
				{
					$watermark_x = $watermark_position["x"] + $x;
					while (true)
					{

						$return_color = NULL;
						$watermark_alpha = $params["alpha_level"];

						$main_rgb = imagecolorsforindex($obj, imagecolorat($obj, $watermark_x, $watermark_y));
						$watermark_rbg = imagecolorsforindex($file_obj, imagecolorat($file_obj, $x, $y));

						if ($watermark_rbg['alpha']):
							$watermark_alpha = round((( 127 - $watermark_rbg['alpha']) / 127), 2);
							$watermark_alpha = $watermark_alpha * $params["alpha_level"];
						endif;

						$res = array(
							"red" => PictureTools::__mix_color($main_rgb['red'], $watermark_rbg['red'], $watermark_alpha),
							"green" => PictureTools::__mix_color($main_rgb['green'], $watermark_rbg['green'], $watermark_alpha),
							"blue" => PictureTools::__mix_color($main_rgb['blue'], $watermark_rbg['blue'], $watermark_alpha));

						$return_color = PictureTools::__get_image_color($obj, $res["red"], $res["green"], $res["blue"]);
						imagesetpixel($obj, $watermark_x, $watermark_y, $return_color);


						$watermark_x += $arFile["width"];
						if ($params["type"] == 'repeat'):
							if ($watermark_x > $params["width"]):
								break;
							endif;
						else:
							break;
						endif;
					}

					$watermark_y += $arFile["height"];
					if ($params["type"] == 'repeat'):
						if ($watermark_y > $params["height"]):
							break;
						endif;
					else:
						break;
					endif;
				}
			}
		}
		@imagedestroy($file_obj);
    }

	function __mix_color($color_a, $color_b, $alpha_level)
	{
		return round(($color_a * (1 - $alpha_level)) + ($color_b * $alpha_level));
	}

	function __get_image_color($im, $r, $g, $b)
	{
		$c = imagecolorexact($im, $r, $g, $b);
		if ($c!=-1):
			return $c;
		endif;
		$c = imagecolorallocate($im, $r, $g, $b);
		if ($c!=-1):
			return $c;
		endif;
		return imagecolorclosest($im, $r, $g, $b);
	}

	function __gd()
	{
		static $gd = false;
		if (!$gd):
			if (function_exists("gd_info")):
				$res = gd_info();
				$gd = ((strpos($res['GD Version'], "2.") !== false) ? 2 : 1);
			endif;
		endif;
		return $gd;
	}

	function ResizeThumbnail($param, $Sight, $iStrongResize = 1)
	{
		$res = false;
		if ($param["width"] <= 0 || $param["height"] <= 0):
		elseif (intVal($Sight["width"]) <= 0 ||  intVal($Sight["height"]) <= 0):
		else:
			$res = array(
				"src" => array("x" => 0, "y" => 0, "width" => $param["width"], "height" => $param["height"]),
				"dst" => array("width" => $Sight["width"], "height" => $Sight["height"]));
			switch ($iStrongResize)
			{
				case 'describe':
				case 'stretch':
				case 2:

					$iResizeCoeff = max($Sight["width"]/$param["width"], $Sight["height"]/$param["height"]);
					$res["src"]["x"] = ((($param["width"]*$iResizeCoeff - $Sight["width"])/2)/$iResizeCoeff);
					$res["src"]["y"] = ((($param["height"]*$iResizeCoeff - $Sight["height"])/2)/$iResizeCoeff);
					$res["src"]["width"] = $Sight["width"] / $iResizeCoeff;
					$res["src"]["height"] = $Sight["height"] / $iResizeCoeff;
					break;
				case 'inscribe':
				case 0:
					$iResizeCoeff = min($Sight["width"]/$param["width"], $Sight["height"]/$param["height"]);
					$res["dst"]["width"] = intVal($iResizeCoeff * $param["width"]);
					$res["dst"]["height"] = intVal($iResizeCoeff * $param["height"]);
					break;
				default:
					$iResizeCoeff = min($Sight["width"]/$param["width"], $Sight["height"]/$param["height"]);
					$iResizeCoeff = (0 < $iResizeCoeff && $iResizeCoeff < 1 ? $iResizeCoeff : 1);
					$res["dst"]["width"] = intVal($iResizeCoeff * $param["width"]);
					$res["dst"]["height"] = intVal($iResizeCoeff * $param["height"]);
					break;
			}
		endif;
		return $res;
	}

	function CreateThumbnail(&$obj, $src, $dst)
	{
		$dst_obj = false;
		if (PictureTools::__gd() == "2")
		{
			$dst_obj = ImageCreateTrueColor($dst["width"], $dst["height"]);
			imagecopyresampled($dst_obj, $obj, 0, 0, $src["x"], $src["y"],
				$dst["width"], $dst["height"], $src["width"], $src["height"]);
		}
		else
		{
			$dst_obj = ImageCreate($dst["width"], $dst["height"]);
			imagecopyresized($dst_obj, $obj, 0, 0, $src["x"], $src["y"],
				$dst["width"], $dst["height"], $src["width"], $src["height"]);
		}
		return $dst_obj;
	}

	function ImageCreate($path, $extension)
	{
	$imageInput = false;
		switch ($extension)
		{
			case 'gif':
				$imageInput = imagecreatefromgif($path);
			break;
			case 'png':
				$imageInput = imagecreatefrompng($path);
			break;
			case 'bmp':
				$imageInput = CFile::ImageCreateFromBMP($path);
			break;
			default:
				$imageInput = imagecreatefromjpeg($path);
			break;
		}
		return $imageInput;
	}
}
?>