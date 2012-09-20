<?/*Tab Control*/
class CForumTabControl
{
	var $name, $unique_name;
	var $tabs;
	var $selectedTab;
	var $tabIndex = 0;
	var $bButtons = false;
	var $bCanExpand;

	var $customTabber;

	function CForumTabControl($name, $tabs, $bCanExpand=true)
	{
		//array(array("DIV"=>"", "TAB"=>"", "ICON"=>, "TITLE"=>"", "ONSELECT"=>"javascript"), ...)
		$this->tabs = $tabs;
		$this->name = $name;
		$this->unique_name = $name."_".md5($GLOBALS["APPLICATION"]->GetCurPage());
		$this->bCanExpand = $bCanExpand;
		if(isset($_REQUEST[$this->name."_active_tab"]))
			$this->selectedTab = $_REQUEST[$this->name."_active_tab"];
		else
			$this->selectedTab = $tabs[0]["DIV"];
	}

	function Begin()
	{
		echo '
		<div class="f-tabs">
			<table cellspacing="0" class="tabs" width="100%">
				<tr>
					<td class="tab-indent-left"><div class="empty"></div></td>
';
		$nTabs = count($this->tabs);
		$i = 0;
		foreach($this->tabs as $tab)
		{
			$bSelected = ($tab["DIV"] == $this->selectedTab);
			echo '
					<td title="'.$tab["TITLE"].'" id="tab_cont_'.$tab["DIV"].'" class="tab-container'.($bSelected? "-selected":"").'" onClick="'.$this->name.'.SelectTab(\''.$tab["DIV"].'\');" onMouseOver="'.$this->name.'.HoverTab(\''.$tab["DIV"].'\', true);" onMouseOut="'.$this->name.'.HoverTab(\''.$tab["DIV"].'\', false);">
						<table cellspacing="0">
							<tr>
								<td class="tab'.($bSelected? "-selected":"").'" id="tab_'.$tab["DIV"].'">'.$tab["TAB"].'</td>
							</tr>
						</table>
					</td>
';
			$i++;
		}
		echo '	 <td width="100%" class="tab-indent-right"><div class="empty"></div></td>
				</tr>
			</table>

			<table cellspacing="0" class="tab">
				<tr>
					<td>
';
	}

	function BeginNextTab()
	{
		//end previous tab
		$this->EndTab();

		if($this->tabIndex >= count($this->tabs))
			return;

		echo '
<div id="'.$this->tabs[$this->tabIndex]["DIV"].'"'.($this->tabs[$this->tabIndex]["DIV"] <> $this->selectedTab? ' style="display:none;"':'').'>
<table cellpadding="0" cellspacing="0" border="0" class="tab-content" id="'.$this->tabs[$this->tabIndex]["DIV"].'_edit_table">
';
		if(array_key_exists("CUSTOM", $this->tabs[$this->tabIndex]) && $this->tabs[$this->tabIndex]["CUSTOM"] == "Y")
		{
			$this->customTabber->ShowTab($this->tabs[$this->tabIndex]["DIV"]);
			$this->tabIndex++;
			$this->BeginNextTab();
		}
		else
		{
			$this->tabIndex++;
		}
	}

	function EndTab()
	{
		if($this->tabIndex < 1 || $this->tabIndex > count($this->tabs) || $this->tabs[$this->tabIndex-1]["_closed"] === true)
			return;

		echo '
</table>
</div>
';
		$this->tabs[$this->tabIndex-1]["_closed"] = true;
	}

	function End()
	{
		while ($this->tabIndex < count($this->tabs))
			$this->BeginNextTab();

		//end previous tab
		$this->EndTab();

		echo '
				</td>
			</tr>
		</table>
	</div>

<input type="hidden" id="'.$this->name.'_active_tab" name="'.$this->name.'_active_tab" value="'.htmlspecialchars($this->selectedTab).'">

<script>';
		$s = "";
		foreach($this->tabs as $tab)
		{
			$s .= ($s <> ""? ", ":"").
			"{".
			"'DIV': '".$tab["DIV"]."' ".
			($tab["ONSELECT"] <> ""? ", 'ONSELECT': '".CUtil::JSEscape($tab["ONSELECT"])."'":"").
			"}";
		}
		echo '
var '.$this->name.' = new TabControl("'.$this->name.'", "'.$this->unique_name.'", ['.$s.']);';
		echo '
'.$this->name.'.InitEditTables();
jsUtils.addEvent(window, "unload", function(){'.$this->name.'.Destroy();});
</script>
';
	}
}
?>