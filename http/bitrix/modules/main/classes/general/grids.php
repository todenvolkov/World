<?
IncludeModuleLangFile(__FILE__);

class CGridOptions
{
	protected $grid_id;
	protected $options;
	protected $filter;
	
	public function __construct($grid_id)
	{
		$this->grid_id = $grid_id;
		$this->options = array();
		$this->filter = array();

		$aOptions = CUserOptions::GetOption("main.interface.grid", $this->grid_id, array());
		if(is_array($aOptions["views"]) && array_key_exists($aOptions["current_view"], $aOptions["views"]))
			$this->options = $aOptions["views"][$aOptions["current_view"]];
		if($this->options["saved_filter"] <> '' && is_array($aOptions["filters"]) && array_key_exists($this->options["saved_filter"], $aOptions["filters"]))
			if(is_array($aOptions["filters"][$this->options["saved_filter"]]["fields"]))
				$this->filter = $aOptions["filters"][$this->options["saved_filter"]]["fields"];
	}
	
	public function GetSorting($arParams=array())
	{
		if(!is_array($arParams["vars"]))
			$arParams["vars"] = array("by" => "by", "order" => "order");
		if(!is_array($arParams["sort"]))
			$arParams["sort"] = array();
			
		$arResult = array(
			"sort" => $arParams["sort"],
			"vars" => $arParams["vars"],
		);

		$uniq = md5($this->grid_id.":".$GLOBALS["APPLICATION"]->GetCurPage());

		$key = '';
		if(isset($_REQUEST[$arParams["vars"]["by"]]))
		{
			$_SESSION["SESS_SORT_BY"][$uniq] = $_REQUEST[$arParams["vars"]["by"]];
		}
		elseif(!isset($_SESSION["SESS_SORT_BY"][$uniq]))
		{
			if($this->options["sort_by"] <> '')
				$key = $this->options["sort_by"];
		}
		if(isset($_SESSION["SESS_SORT_BY"][$uniq]))
			$key = $_SESSION["SESS_SORT_BY"][$uniq];
			
		if($key <> '')
		{
			if(isset($_REQUEST[$arParams["vars"]["order"]]))
			{
				$_SESSION["SESS_SORT_ORDER"][$uniq] = $_REQUEST[$arParams["vars"]["order"]];
			}
			elseif(!isset($_SESSION["SESS_SORT_ORDER"][$uniq]))
			{
				if($this->options["sort_order"] <> '')
					$arResult["sort"] = array($key => $this->options["sort_order"]);
			}
			if(isset($_SESSION["SESS_SORT_ORDER"][$uniq]))
				$arResult["sort"] = array($key => $_SESSION["SESS_SORT_ORDER"][$uniq]);
		}

		return $arResult;
	}
	
	public function GetNavParams($arParams=array())
	{
		$arResult = array(
			"nPageSize" => (isset($arParams["nPageSize"])? $arParams["nPageSize"] : 20),
		);
		
		if($this->options["page_size"] <> '')
			$arResult["nPageSize"] = $this->options["page_size"];

		return $arResult;
	}
	
	public function GetVisibleColumns()
	{
		if($this->options["columns"] <> '')
			return explode(",", $this->options["columns"]);
		return array();
	}
	
	public function GetFilter($arFilter)
	{
		$uniq = md5($this->grid_id.":".$GLOBALS["APPLICATION"]->GetCurPage());
		$aRes = array();
		foreach($arFilter as $field)
		{
			//date
			if(isset($_REQUEST[$field["id"]."_datesel"]))
			{
				if($_REQUEST[$field["id"]."_datesel"] <> '')
				{
					$aRes[$field["id"]."_datesel"] = $_REQUEST[$field["id"]."_datesel"];
					CGridOptions::CalcDates($field["id"], $_REQUEST, $aRes);
				}
				else
				{
					unset($_SESSION["GRID_FILTER"][$uniq][$field["id"]."_datesel"]);
					unset($_SESSION["GRID_FILTER"][$uniq][$field["id"]."_from"]);
					unset($_SESSION["GRID_FILTER"][$uniq][$field["id"]."_to"]);
					unset($_SESSION["GRID_FILTER"][$uniq][$field["id"]."_days"]);
				}
				continue;
			}
			
			//quick
			if($_REQUEST[$field["id"]."_list"] <> '' && $_REQUEST[$field["id"]] <> '')
				$aRes[$field["id"]."_list"] = $_REQUEST[$field["id"]."_list"];

			//number interval
			if(isset($_REQUEST[$field["id"]."_from"]))
			{
				if($_REQUEST[$field["id"]."_from"] <> '')
					$aRes[$field["id"]."_from"] = $_REQUEST[$field["id"]."_from"];
				else
					unset($_SESSION["GRID_FILTER"][$uniq][$field["id"]."_from"]);
			}
			if(isset($_REQUEST[$field["id"]."_to"]))
			{
				if($_REQUEST[$field["id"]."_to"] <> '')
					$aRes[$field["id"]."_to"] = $_REQUEST[$field["id"]."_to"];
				else
					unset($_SESSION["GRID_FILTER"][$uniq][$field["id"]."_to"]);
			}

			//filtered outside, we don't control the filter field value
			if($field["filtered"] == true)
			{
				$aRes[$field["id"]] = true;
				continue;
			}

			//list or string
			if(isset($_REQUEST[$field["id"]]))
			{ 
				if(is_array($_REQUEST[$field["id"]]) && !empty($_REQUEST[$field["id"]]) && $_REQUEST[$field["id"]][0] <> '' || !is_array($_REQUEST[$field["id"]]) && $_REQUEST[$field["id"]] <> '')
					$aRes[$field["id"]] = $_REQUEST[$field["id"]];
				else
					unset($_SESSION["GRID_FILTER"][$uniq][$field["id"]]);
			}
		}
		if(!empty($aRes))
			$_SESSION["GRID_FILTER"][$uniq] = $aRes;
		elseif($_REQUEST["clear_filter"] <> '')
			$_SESSION["GRID_FILTER"][$uniq] = array();
		elseif(is_array($_SESSION["GRID_FILTER"][$uniq]))
			return $_SESSION["GRID_FILTER"][$uniq];
		elseif(!empty($this->filter))
		{
			foreach($arFilter as $field)
			{
				if($this->filter[$field["id"]."_datesel"] <> '')
				{
					$aRes[$field["id"]."_datesel"] = $this->filter[$field["id"]."_datesel"];
					CGridOptions::CalcDates($field["id"], $this->filter, $aRes);
					continue;
				}
				if($this->filter[$field["id"]."_list"] <> '' && $this->filter[$field["id"]] <> '')
					$aRes[$field["id"]."_list"] = $this->filter[$field["id"]."_list"];
				if($this->filter[$field["id"]."_from"] <> '')
					$aRes[$field["id"]."_from"] = $this->filter[$field["id"]."_from"];
				if($this->filter[$field["id"]."_to"] <> '')
					$aRes[$field["id"]."_to"] = $this->filter[$field["id"]."_to"];
				if(is_array($this->filter[$field["id"]]) && !empty($this->filter[$field["id"]]) && $this->filter[$field["id"]][0] <> '' || !is_array($this->filter[$field["id"]]) && $this->filter[$field["id"]] <> '')
					$aRes[$field["id"]] = $this->filter[$field["id"]];
			}
			if(!empty($aRes))
				$_SESSION["GRID_FILTER"][$uniq] = $aRes;
		}

		return $aRes;
	}

	public static function CalcDates($field_id, $aInput, &$aRes)
	{
		switch($aInput[$field_id."_datesel"])
		{
			case "today":
				$aRes[$field_id."_from"] = $aRes[$field_id."_to"] = ConvertTimeStamp();
				break;
			case "yesterday":
				$aRes[$field_id."_from"] = $aRes[$field_id."_to"] = ConvertTimeStamp(time()-86400);
				break;
			case "week":
				$day = date("w");
				if($day == 0)
					$day = 7;
				$aRes[$field_id."_from"] = ConvertTimeStamp(time()-($day-1)*86400);
				$aRes[$field_id."_to"] = ConvertTimeStamp(time()+(7-$day)*86400);
				break;
			case "week_ago":
				$day = date("w");
				if($day == 0)
					$day = 7;
				$aRes[$field_id."_from"] = ConvertTimeStamp(time()-($day-1+7)*86400);
				$aRes[$field_id."_to"] = ConvertTimeStamp(time()-($day)*86400);
				break;
			case "month":
				$aRes[$field_id."_from"] = ConvertTimeStamp(mktime(0, 0, 0, date("n"), 1));
				$aRes[$field_id."_to"] = ConvertTimeStamp(mktime(0, 0, 0, date("n")+1, 0));
				break;
			case "month_ago":
				$aRes[$field_id."_from"] = ConvertTimeStamp(mktime(0, 0, 0, date("n")-1, 1));
				$aRes[$field_id."_to"] = ConvertTimeStamp(mktime(0, 0, 0, date("n"), 0));
				break;
			case "days":
				$aRes[$field_id."_days"] = $aInput[$field_id."_days"];
				$aRes[$field_id."_from"] = ConvertTimeStamp(time() - intval($aRes[$field_id."_days"])*86400);
				$aRes[$field_id."_to"] = "";
				break;
			case "exact":
				$aRes[$field_id."_from"] = $aRes[$field_id."_to"] = $aInput[$field_id."_from"];
				break;
			case "after":
				$aRes[$field_id."_from"] = $aInput[$field_id."_from"];
				$aRes[$field_id."_to"] = "";
				break;
			case "before":
				$aRes[$field_id."_from"] = "";
				$aRes[$field_id."_to"] = $aInput[$field_id."_to"];
				break;
			case "interval":
				$aRes[$field_id."_from"] = $aInput[$field_id."_from"];
				$aRes[$field_id."_to"] = $aInput[$field_id."_to"];
				break;
		}
	}
	
	public static function GetThemes($path)
	{
		//color schemes
		$aColorNames = array(
			"grey"=>GetMessage("interface_grid_theme_grey"),
			"blue"=>GetMessage("interface_grid_theme_blue"),
			"brown"=>GetMessage("interface_grid_theme_brown"),
			"green"=>GetMessage("interface_grid_theme_green"),
			"lightblue"=>GetMessage("interface_grid_theme_lightblue"),
			"red"=>GetMessage("interface_grid_theme_red"),
			"lightgrey"=>GetMessage("interface_grid_theme_lightgrey"),
		);
		$arThemes = array();
		$themesPath = $_SERVER["DOCUMENT_ROOT"].$path.'/themes';
		if(is_dir($themesPath))
		{
			if($dir = opendir($themesPath))
			{
				while(($file = readdir($dir)) !== false) 
				{
					if($file != '.' && $file != '..' && is_dir($themesPath."/".$file))
						$arThemes[$file] = array("theme"=>$file, "name"=>(isset($aColorNames[$file])? $aColorNames[$file]:$file));
				}
				closedir($dir);
			}
		}
		uasort($arThemes, create_function('$a, $b', 'return strcmp($a["name"], $b["name"]);'));
		return $arThemes;
	}
}
?>