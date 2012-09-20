<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class Step1 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('STATWIZ_STEP1_TITLE'));
		$this->SetNextStep("step2");
		$this->SetStepID("step1");
		$this->SetCancelStep("cancel");
		if(!CModule::IncludeModule('statistic'))
			$this->SetError(GetMessage('STATWIZ_NO_MODULE_ERROR'));
	}

	function OnPostForm()
	{
		$wizard = &$this->GetWizard();
		$install_type = $wizard->GetVar("install_type");
		$wizard->SetCurrentStep($install_type);
	}

	function ShowStep()
	{
		$this->content = GetMessage('STATWIZ_STEP1_CONTENT');
		$this->content .= "<br><br>";

		$wizard =& $this->GetWizard();
		$import_type = $wizard->GetVar('import_type');
		if($import_type !== 'city')
			$import_type = 'country';

		$arOptions = array(
			'country' => array(
				"TITLE" => GetMessage('STATWIZ_STEP1_COUNTRY'),
				"DEFAULT" => ($import_type == 'country'? 'Y': 'N'),
				"ONCLICK" => 'document.getElementById("city_note").style.display="none";document.getElementById("country_note").style.display="block";',
			),
			'city' => array(
				"TITLE" => GetMessage('STATWIZ_STEP1_CITY'),
				"DEFAULT" => ($import_type == 'city'? 'Y': 'N'),
				"ONCLICK" => 'document.getElementById("country_note").style.display="none";document.getElementById("city_note").style.display="block";',
			),
		);

		foreach($arOptions as $option_id => $arOption)
		{
			$arInputAttr = array();
			if ($arOption["DEFAULT"] == "Y")
				$arInputAttr['checked'] = 'checked';
			$arInputAttr["id"] = $option_id;
			$arInputAttr["onclick"] = $arOption["ONCLICK"];

			$this->content .= $this->ShowRadioField("import_type", $option_id, $arInputAttr);
			$this->content .= '<label for="'.$option_id.'">'.$arOption["TITLE"].'</label>';
			$this->content .= '</br>';
		}
		$this->content .= '</br>';
		$this->content .= '<div id="country_note" style="display:'.($import_type=='country'? 'block': 'none').';">'.GetMessage("STATWIZ_STEP1_COUNTRY_NOTE", array(
			"#GEOIP_HREF#" => "http://www.maxmind.com/app/country",
			"#GEOIPLITE_HREF#" => "http://www.maxmind.com/app/geolitecountry",
			"#IPTOCOUNTRY_HREF#" => "http://ip-to-country.webhosting.info/",
		)).'</div>';
		$this->content .= '<div id="city_note" style="display:'.($import_type=='city'? 'block': 'none').';">'.GetMessage("STATWIZ_STEP1_CITY_NOTE", array(
			"#GEOIP_HREF#" => "http://www.maxmind.com/app/city",
			"#GEOIPLITE_HREF#" => "http://www.maxmind.com/app/geolitecity",
			"#IPGEOBASE_HREF#" => "http://ipgeobase.ru/cgi-bin/Archive.cgi",
		)).'</div>';
		$this->content .= '<p>'.GetMessage("STATWIZ_STEP1_COMMON_NOTE", array(
			"#PATH#" => '<span style="white-space:nowrap;">/bitrix/modules/statistic/ip2country</span>',
		)).'</p>';
	}
}

class Step2 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('STATWIZ_STEP2_TITLE'));
		$this->SetNextStep("step3");
		$this->SetPrevStep("step1");
		$this->SetStepID("step2");
		$this->SetCancelStep("cancel");
		if(!CModule::IncludeModule('statistic'))
			$this->SetError(GetMessage('STATWIZ_NO_MODULE_ERROR'));
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		$import_type = $wizard->GetVar('import_type');
		if($import_type !== 'city')
		{
			$import_type = 'country';
			$this->content = GetMessage('STATWIZ_STEP2_COUNTRY_CHOOSEN');
		}
		else
		{
			$this->content = GetMessage('STATWIZ_STEP2_CITY_CHOOSEN');
		}
		$this->content .= "<br><br>";
		$this->content .= GetMessage('STATWIZ_STEP2_CONTENT');
		$this->content .= "<br><br>";

		$sizes = array("b", "Kb", "Mb", "Gb");

		$file_name = $wizard->GetVar('file_name');
		$arFiles = CCity::FindFiles($import_type);
		if(count($arFiles) <= 0)
		{
			$this->content .= GetMessage('STATWIZ_FILES_NOT_FOUND');
		}
		else
		{
			$this->content .= "<style>
			table.statwiz_table  { border-collapse:collapse; }
			table.statwiz_table td { font-family:Verdana,Arial,sans-serif; border: 1px solid #BDC6E0; padding:3px; background-color: white; }
			table.statwiz_table td.head { background-color:#E6E9F4; }
			table.statwiz_table td.tail { background-color:#EAEDF7; }
			</style>
			";
			$this->content .= '<table class="statwiz_table">
			<tr>
				<td class="head">&nbsp;</td>
				<td class="head">'.GetMessage('STATWIZ_STEP2_FILE_NAME').'</td>
				<td class="head">'.GetMessage('STATWIZ_STEP2_FILE_SIZE').'</td>
				<td class="head">'.GetMessage('STATWIZ_STEP2_DESCRIPTION').'</td>
			</tr>';
			foreach($arFiles as $arFile)
			{
				$this->content .= '<tr>';
				$arInputAttr = array();
				if ($arFile["FILE"] === $file_name)
					$arInputAttr['checked'] = 'checked';
				$arInputAttr["id"] = htmlspecialchars($arFile["FILE"]);

				$this->content .= '<td>'.$this->ShowRadioField("file_name", $arFile["FILE"], $arInputAttr).'</td>';
				$this->content .= '<td nowrap><label for="'.$arInputAttr["id"].'">'.$arFile["FILE"].'</label></td>';
				$pos = 0;
				$size = $arFile["SIZE"];
				while($size >= 1024)
				{
					$size /= 1024;
					$pos++;
				}
				$this->content .= '<td nowrap>'.round($size, 2)." ".$sizes[$pos].'</td>';
				switch($arFile["SOURCE"])
				{
					case "MAXMIND-IP-COUNTRY":
						$this->content .= '<td>'.GetMessage('STATWIZ_STEP2_FILE_TYPE_MAXMIND_IP_COUNTRY').'</td>';
						break;
					case "IP-TO-COUNTRY":
						$this->content .= '<td>'.GetMessage('STATWIZ_STEP2_FILE_TYPE_IP_TO_COUNTRY').'</td>';
						break;
					case "MAXMIND-IP-LOCATION":
						$this->content .= '<td>'.GetMessage('STATWIZ_STEP2_FILE_TYPE_MAXMIND_IP_LOCATION').'</td>';
						break;
					case "MAXMIND-CITY-LOCATION":
						$this->content .= '<td>'.GetMessage('STATWIZ_STEP2_FILE_TYPE_MAXMIND_CITY_LOCATION').'</td>';
						break;
					case "IPGEOBASE":
						$this->content .= '<td>'.GetMessage('STATWIZ_STEP2_FILE_TYPE_IPGEOBASE').'</td>';
						break;
					default:
						$this->content .= '<td>'.GetMessage('STATWIZ_STEP2_FILE_TYPE_UNKNOWN').'</td>';
				}
				$this->content .= '</tr>';
			}
			$this->content .= '</table>';
		}
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();

		if ($wizard->IsNextButtonClick() || $wizard->IsFinishButtonClick())
		{
			$file_name = $wizard->GetVar('file_name');
			if(strlen($file_name) <= 0)
				$this->SetError(GetMessage('STATWIZ_STEP2_FILE_ERROR'), 'file_name');
		}
	}
}

class Step3 extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('STATWIZ_STEP3_TITLE'));
		$this->SetNextStep("final");
		$this->SetPrevStep("step2");
		$this->SetStepID("step3");
		$this->SetCancelStep("cancel");
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		$import_type = $wizard->GetVar('import_type');
		$file_name = $wizard->GetVar('file_name');

		$path = $wizard->package->path;

		$this->content = '';
		$this->content .= '<div style="padding: 20px;">';
		$this->content .= '<div id="progress" style="height: 20px; width: 500px;"></div>';
		$this->content .= '<div id="wait_message" style="display: none;"></div>';
		$this->content .= '<div id="output"><br /></div>';
		$this->content .= '</div>';
		$this->content .= '<script type="text/javascript" src="'.$path.'/js/import.js"></script>';
		$this->content .= '<script type="text/javascript">

var nextButtonID = "'.$wizard->GetNextButtonID().'";
var formID = "'.$wizard->GetFormName().'";
var ajaxMessages = {wait:\''.GetMessage('STATWIZ_STEP3_LOADING').'\'};
var LANG = \''.LANG.'\';
var import_type = "'.CUtil::JSEscape($import_type).'";
var file_name = "'.CUtil::JSEscape($file_name).'";
var path = "'.CUtil::JSEscape($path).'";

if (window.addEventListener)
{
	window.addEventListener("load", DisableButton, false);
	window.addEventListener("load", Import, false);
}
else if (window.attachEvent)
{
	window.attachEvent("onload", DisableButton);
	window.attachEvent("onload", Import);
}
</script>';
	}
}

class FinalStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('STATWIZ_FINALSTEP_TITLE'));
		$this->SetStepID("final");
		$this->SetCancelStep("final");
		$this->SetCancelCaption(GetMessage('STATWIZ_FINALSTEP_BUTTONTITLE'));
	}

	function ShowStep()
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$rs = $DB->Query("SELECT count(*) CNT FROM b_stat_country");
		$arCountry = $rs->Fetch();
		$rs = $DB->Query("SELECT count(*) CNT FROM b_stat_city");
		$arCity = $rs->Fetch();
		$rs = $DB->Query("SELECT count(*) CNT FROM b_stat_city_ip");
		$arCityIP = $rs->Fetch();
		$this->content = '<ul>';
		$this->content .= '<li>'.GetMessage('STATWIZ_FINALSTEP_COUNTRIES', array("#COUNT#" => $arCountry["CNT"])).'</li>';
		$this->content .= '<li>'.GetMessage('STATWIZ_FINALSTEP_CITIES', array("#COUNT#" => $arCity["CNT"])).'</li>';
		$this->content .= '<li>'.GetMessage('STATWIZ_FINALSTEP_CITY_IPS', array("#COUNT#" => $arCityIP["CNT"])).'</li>';
		$this->content .= '</ul>';
	}
}

class CancelStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetTitle(GetMessage('STATWIZ_CANCELSTEP_TITLE'));
		$this->SetStepID("cancel");
		$this->SetCancelStep("cancel");
		$this->SetCancelCaption(GetMessage('STATWIZ_CANCELSTEP_BUTTONTITLE'));
	}

	function ShowStep()
	{
		$this->content = GetMessage('STATWIZ_CANCELSTEP_CONTENT');
	}
}
?>