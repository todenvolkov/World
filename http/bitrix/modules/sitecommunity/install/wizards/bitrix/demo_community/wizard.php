<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/install/wizard_sol/wizard.php");

class SelectSiteStep extends CSelectSiteWizardStep
{
	function InitStep()
	{
		parent::InitStep();

		$wizard =& $this->GetWizard();
		$wizard->solutionName = "community";
	}
}

class SelectTemplateStep extends CSelectTemplateWizardStep { }

class SelectThemeStep extends CSelectThemeWizardStep { }

class SiteSettingsStep extends CSiteSettingsWizardStep
{
	function InitStep()
	{
		$wizard =& $this->GetWizard();
		$wizard->solutionName = "community";
		parent::InitStep();

		$this->SetTitle(GetMessage("wiz_settings"));
		$this->SetNextStep("data_install");
		$this->SetNextCaption(GetMessage("wiz_install"));

		$siteID = $wizard->GetVar("siteID");
		
		$siteLogo = $this->GetFileContentImgSrc(WIZARD_SITE_PATH."include/company_logo.php", "/bitrix/wizards/bitrix/demo_community/site/templates/taby/images/logo.jpg");
	
		$wizard->SetDefaultVars(
			Array(
				"siteName" => $this->GetFileContent(WIZARD_SITE_PATH."include/company_name.php", GetMessage("wiz_name")),
				"siteDescription" => $this->GetFileContent(WIZARD_SITE_PATH."include/company_description.php", GetMessage("wiz_slogan")), 
				"siteLogo" => $siteLogo,
				"siteMetaDescription" => GetMessage("wiz_slogan"),
				"siteMetaKeywords" => GetMessage("wiz_keywords")  
			)
		);
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();
		$res = $this->SaveFile("siteLogo", Array("extensions" => "gif,jpg,jpeg,png", "max_height" => 80, "max_width" => 90, "make_preview" => "Y"));
	}
	
	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		$siteLogo = $wizard->GetVar("siteLogo", true);
		
		$this->content .= '<div class="wizard-input-form">';
		$this->content .= '
		<div class="wizard-input-form-block">
			<h4><label for="siteName">'.GetMessage("wiz_company_name").'</label></h4>
			<div class="wizard-input-form-block-content">
				<div class="wizard-input-form-field wizard-input-form-field-text">'.
					$this->ShowInputField('text', 'siteName', array("id" => "siteName")).
				'</div>
			</div>
		</div>';
		$this->content .= '
		<div class="wizard-input-form-block">
			<h4><label for="siteDescription">'.GetMessage("wiz_company_description").'</label></h4>
			<div class="wizard-input-form-block-content">
				<div class="wizard-input-form-field wizard-input-form-field-text">'.
					$this->ShowInputField('text', 'siteDescription', array("id" => "siteDescription")).'</div>
			</div>
		</div>';

		if($wizard->GetVar("templateID")=="taby")
		{
		$this->content .= '
		<div class="wizard-input-form-block">
			<h4><label for="siteLogo">'.GetMessage("wiz_company_logo").'</label></h4>
			<div class="wizard-input-form-block-content">
				<div class="wizard-input-form-field wizard-input-form-field-text">'.
					$this->ShowFileField("siteLogo", 
						Array(
							"show_file_info"=> "N", 
							"id" => "siteLogo", 
							"style" => "width: 90%; border: solid 1px #CECECE; background-color: #F5F5F5; padding: 3px;")). '<br />'. 
					CFile::ShowImage($siteLogo, 100, 100, "border=0 vspace=5").'</div>
			</div>
		</div>';
		}

		$firstStep = COption::GetOptionString("main", "wizard_first" . substr($wizard->GetID(), 7)  . "_" . $wizard->GetVar("siteID"), false, $wizard->GetVar("siteID"));
		$styleMeta = 'style="display:block"';
		if($firstStep == "Y") $styleMeta = 'style="display:none"';
		$this->content .= '
			<div id="bx_metadata" '. $styleMeta .'><div class="wizard-input-form-block">
				<h4><label for="siteMetaDescription">'.GetMessage("wiz_meta_data").'</label></h4>
				<label for="siteMetaDescription">'.GetMessage("wiz_meta_description").'</label>
				<div class="wizard-input-form-block-content" style="margin-top:7px;">
					<div class="wizard-input-form-field wizard-input-form-field-textarea">'.
						$this->ShowInputField("textarea", "siteMetaDescription", Array("id" => "siteMetaDescription", "style" => "width:100%", "rows"=>"3")).'</div>
				</div>
			</div>';
			$this->content .= '
			<div class="wizard-input-form-block">
				<label for="siteMetaKeywords">'.GetMessage("wiz_meta_keywords").'</label><br>
				<div class="wizard-input-form-block-content" style="margin-top:7px;">
					<div class="wizard-input-form-field wizard-input-form-field-text">'.
						$this->ShowInputField('text', 'siteMetaKeywords', array("id" => "siteMetaKeywords")).'</div>
				</div>
			</div></div>';
		
 
		if($firstStep == "Y")
		{
			$this->content .= '
			<div class="wizard-input-form-block">
				<div class="wizard-input-form-block-content">'.
						$this->ShowCheckboxField(
							"installDemoData", 
							"Y", 
							(array("id" => "installDemoData", "onClick" => "if(this.checked == true){document.getElementById('bx_metadata').style.display='block';}else{document.getElementById('bx_metadata').style.display='none';}"))
						).
				'
				<label for="installDemoData">'.GetMessage("wiz_structure_data").'</label>
				</div>
			</div>';
			}
		else
		{
			$this->content .= $this->ShowHiddenField("installDemoData","Y");
		}
	}
}

class DataInstallStep extends CDataInstallWizardStep
{
	function CorrectServices(&$arServices)
	{
		$wizard =& $this->GetWizard();
		if($wizard->GetVar("installDemoData") != "Y")
		{
		}
	}
}

class FinishStep extends CFinishWizardStep
{
}

?>