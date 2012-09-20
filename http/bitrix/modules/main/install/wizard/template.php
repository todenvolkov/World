<?php
class WizardTemplate extends CWizardTemplate
{
	function GetLayout()
	{
		global $arWizardConfig;
		$wizard = &$this->wizard;

		$formName = htmlspecialchars($wizard->GetFormName());
		$wizardName = $wizard->GetWizardName();

		$nextButtonID = htmlspecialchars($wizard->GetNextButtonID());
		$prevButtonID = htmlspecialchars($wizard->GetPrevButtonID());
		$cancelButtonID = htmlspecialchars($wizard->GetCancelButtonID());
		$finishButtonID = htmlspecialchars($wizard->GetFinishButtonID());

		$wizardPath = $wizard->GetPath();

		$obStep =& $wizard->GetCurrentStep();
		$arErrors = $obStep->GetErrors();
		$strError = "";
		if (count($arErrors) > 0)
		{
			foreach ($arErrors as $arError)
				$strError .= $arError[0]."<br />";

			if (strlen($strError) > 0)
				$strError = '<div id="step-error">'.$strError."</div>";
		}

		$stepTitle = $obStep->GetTitle();
		$stepSubTitle = $obStep->GetSubTitle();

		$alertText = GetMessage("MAIN_WIZARD_WANT_TO_CANCEL");
		$loadingText = GetMessage("MAIN_WIZARD_WAIT_WINDOW_TEXT");

		$BX_ROOT = BX_ROOT;
		$productVersion = SM_VERSION;

		//wizard customization file
		$bxProductConfig = array();
		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php"))
		{
			include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php");
			if(defined("INSTALL_UTF_PAGE") && is_array($bxProductConfig["product_wizard"]))
			{
				foreach($bxProductConfig["product_wizard"] as $key=>$val)
					$bxProductConfig["product_wizard"][$key] = mb_convert_encoding($val, INSTALL_CHARSET, "utf-8");
			}
		}

		if(isset($bxProductConfig["product_wizard"]["product_name"]))
			$title = $bxProductConfig["product_wizard"]["product_name"];
		else
			$title = (isset($arWizardConfig["productName"]) ? $arWizardConfig["productName"] : InstallGetMessage("INS_TITLE1"));
		$title = str_replace("#VERS#", $productVersion , $title);
		$browserTitle = strip_tags(str_replace(Array("<br>", "<br />"), " ",$title));

		if(isset($bxProductConfig["product_wizard"]["copyright"]))
			$copyright = $bxProductConfig["product_wizard"]["copyright"];
		else
		{
			$copyright = InstallGetMessage("COPYRIGHT");
			if (isset($arWizardConfig["copyrightText"]))
				$copyright .= $arWizardConfig["copyrightText"];
		}
		$copyright = str_replace("#CURRENT_YEAR#", date("Y") , $copyright);

		if(isset($bxProductConfig["product_wizard"]["links"]))
			$support = $bxProductConfig["product_wizard"]["links"];
		else
			$support = (isset($arWizardConfig["supportText"]) ? $arWizardConfig["supportText"] : InstallGetMessage("SUPPORT"));

		//Images
		$logoImage = "";
		$boxImage = "";

		if(isset($bxProductConfig["product_wizard"]["logo"]))
		{
			$logoImage = $bxProductConfig["product_wizard"]["logo"];
		}
		else
		{
			if (isset($arWizardConfig["imageLogoSrc"]) && file_exists($_SERVER["DOCUMENT_ROOT"].$arWizardConfig["imageLogoSrc"]))
				$logoImage = '<img src="'.$arWizardConfig["imageLogoSrc"].'" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/install/".LANGUAGE_ID."/logo.gif"))
				$logoImage = '<img src="/bitrix/images/install/'.LANGUAGE_ID.'/logo.gif" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/install/en/logo.gif"))
				$logoImage = '<img src="/bitrix/images/install/en/logo.gif" alt="" />';
		}

		if(isset($bxProductConfig["product_wizard"]["product_image"]))
		{
			$boxImage = $bxProductConfig["product_wizard"]["product_image"];
		}
		else
		{
			if (isset($arWizardConfig["imageBoxSrc"]) && file_exists($_SERVER["DOCUMENT_ROOT"].$arWizardConfig["imageBoxSrc"]))
				$boxImage = '<img src="'.$arWizardConfig["imageBoxSrc"].'" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/install/".LANGUAGE_ID."/box.jpg"))
				$boxImage = '<img src="/bitrix/images/install/'.LANGUAGE_ID.'/box.jpg" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/install/en/box.jpg"))
				$boxImage = '<img src="/bitrix/images/install/en/box.jpg" alt="" />';
		}

		$strErrorMessage = "";
		$strWarningMessage = "";
		$strNavigation = "";

		$arSteps = $wizard->GetWizardSteps();
		$currentStep = $wizard->GetCurrentStepID();

		$currentSuccess = false;
		$stepNumber = 1;

		foreach ($arSteps as $stepID => $stepObject)
		{
			if ($stepID == $currentStep)
			{
				$class = 'class="selected"';
				$currentSuccess = true;
			}
			elseif ($currentSuccess)
				$class = '';
			else
				$class = 'class="done"';

			$strNavigation .= '
			<tr '.$class.'>
				<td class="menu-number">'.$stepNumber.'</td>
				<td class="menu-name">'.$stepObject->GetTitle().'</td>
				<td class="menu-end"></td>
			</tr>
			<tr class="menu-separator">
				<td colspan="3"></td>
			</tr>';

			$stepNumber++;
		}

		if (strlen($strNavigation) > 0)
			$strNavigation = '<table width="100%" cellpadding="0" cellspacing="0" id="menu">'.$strNavigation.'</table>';

		$currentStep = $wizard->GetCurrentStepID();
		$jsBeforeOnload = "";
		if ($currentStep == "create_modules")
		{
			$jsBeforeOnload .= "var warningBeforeOnload = '".InstallGetMessage("INS_BEFORE_USER_EXIT")."';\n";
			$jsBeforeOnload .= "window.onbeforeunload = OnBeforeUserExit;";
		}

		$jsCode = "";
		$jsCode = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard/script.js");

		$instructionText = InstallGetMessage("GOTO_README");
		$noscriptInfo = InstallGetMessage("INST_JAVASCRIPT_DISABLED");
		$charset = (defined("INSTALL_UTF_PAGE") ? "UTF-8" : INSTALL_CHARSET);


		return <<<HTML
<html>
	<head>
		<title>{$browserTitle}</title>
		<meta http-equiv="Content-Type" content="text/html; charset={$charset}">
		<noscript>
			<style type="text/css">
				div {display: none;}
				#noscript {padding: 3em; font-size: 130%; background:white;}
			</style>
			<p id="noscript">{$noscriptInfo}</p>
		</noscript>

		<style type="text/css">

			html {height:100%;}

			body 
			{
				background:#4a507b url(/bitrix/images/install/bg_fill.gif) repeat;
				margin:0;
				padding:0;
				padding-bottom:6px;
				font-family: Arial, Verdana, Helvetica, sans-serif;
				font-size:82%;
				height:100%;
				color:black;
				box-sizing:border-box;
				-moz-box-sizing:border-box;
				-webkit-box-sizing: border-box;
				-khtml-box-sizing: border-box;
			}

			table {font-size:100.01%;}

			a {color:#2676b9}

			h3 {font-size:120%;}

			#container
			{
				padding-top:6px;
				height:100%;
				background: transparent url(/bitrix/images/install/bg_top.gif) repeat-x;
				box-sizing:border-box;
				-moz-box-sizing:border-box;
				-webkit-box-sizing: border-box;
				-khtml-box-sizing: border-box;
			}

			#main-table
			{
				width:760px;
				height:100%;
				border-collapse:collapse;
			}

			#main-table td {padding:0;}

			td.wizard-title
			{
				background:#e3f0f9 url(/bitrix/images/install/top_gradient_fill.gif) repeat-x; 
				height:77px; 
				color:#19448a; 
				font-size:140%; 
			}
			#step-title
			{
				color:#cd4d3e; 
				margin: 20px; 
				padding-bottom:20px; 
				border-bottom:1px solid #d9d9d9; 
				font-weight:bold;
				font-size:120%;
			}
			#step-content {margin:20px 25px; zoom:1;}

			table.data-table
			{
				width:100%;
				border-collapse:collapse;
				border:1px solid #d0d0d0;
			}

			table.data-table td
			{
				padding:5px !important;
				border:1px solid #d0d0d0;
			}

			table.data-table td.header
			{
				text-align:center;
				background: #e3f0f9;
				font-weight: bold;
			}

			#menu td.menu-number, #menu td.menu-name
			{
				background:#eaeaea url(/bitrix/images/install/menu_fill.gif) repeat-x;
				height:40px;
				color:#c0c0c0;
			}

			#menu tr.menu-separator
			{
				height:2px;
				background: none;
			}

			#menu tr.selected td.menu-number, #menu tr.selected td.menu-name
			{
				background:#b41d07 url(/bitrix/images/install/menu_fill_selected.gif) repeat-x;
				color:white;
			}

			#menu tr.done
			{
				color:black;
			}

			#menu td.menu-end
			{
				background: url(/bitrix/images/install/menu_end.gif) repeat-x;
				width:11px;
			}

			#menu tr.selected td.menu-end
			{
				background: url(/bitrix/images/install/menu_end_selected.gif) repeat-x;
				width:11px;
			}

			#menu td.menu-number
			{
				width:30px;
				font-size: 170%;
				text-align:center;
			}

			#menu td.menu-name
			{
				font-size:110%;
				padding-bottom:1px;
			}

			#copyright {font-size:95%; color:#606060; margin:4px 7px 0 7px; zoom:1;}

			input.wizard-prev-button {background: #ffe681 url(/bitrix/images/install/prev.gif); border:none; width:116px; height:31px; font-weight:bold; padding-bottom:4px; cursor:pointer; cursor:hand;}
			input.wizard-next-button {background: #ffe681 url(/bitrix/images/install/next.gif); border:none; width:116px; height:31px; font-weight:bold; padding-bottom:4px; cursor:pointer; cursor:hand;}

			form {margin:0; padding:0;}
			#step-error {color:red; padding:4px 4px 4px 25px;margin-bottom:4px; background:url(/bitrix/images/install/error.gif) no-repeat;}
			small{font-size:85%;}

		</style>
		<script type="text/javascript">
		<!--
			document.onkeydown = EnterKeyPress;

			function EnterKeyPress(event)
			{
				if (!document.getElementById)
					return;

				if (window.event)
					event = window.event;

				var sourceElement = (event.target? event.target : (event.srcElement? event.srcElement : null));

				if (!sourceElement || sourceElement.tagName.toUpperCase() == "TEXTAREA")
					return;

				var key = (event.keyCode ? event.keyCode : (event.which ? event.which : null) );
				if (!key)
					return;

				if (key == 13)
				{
					CancelBubble(event);
				}
				else if (key == 39 && event.ctrlKey)
				{
					var nextButton = document.forms["{$formName}"].elements["{$nextButtonID}"];
					if (nextButton)
					{
						nextButton.click();
						CancelBubble(event);
					}
				}
				else if (key == 37 && event.ctrlKey)
				{
					var prevButton = document.forms["{$formName}"].elements["{$prevButtonID}"];
					if (prevButton)
					{
						prevButton.click();
						CancelBubble(event);
					}
				}
			}

			{$jsCode}
			{$jsBeforeOnload}
		//-->
		</script>


	</head>

<body id="bitrix_install_template">
<div id="container">

	<table id="main-table" align="center">
		<tr>
			<td width="10" height="10"><img src="/bitrix/images/install/corner_top_left.gif" width="10" height="10" alt="" /></td>
			<td width="100%">
				<table width="100%" height="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="215" height="10" style="background:white;"></td>
						<td width="525" height="10" style="background:#e3f0f9;"></td>
					</tr>
				</table>
			</td>
			<td width="10" height="10"><img src="/bitrix/images/install/corner_top_right.gif" width="10" height="10" alt="" /></td>
		</tr>
		<tr>
			<td colspan="3" height="100%" style="background:white">
				<table width="100%" height="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="225" valign="top">
							<!-- Left column -->
							<table width="100%" height="100%" cellpadding="0" cellspacing="0">
								<tr><td align="center" height="185">{$boxImage}</td></tr>
								<tr>
									<td height="100%" valign="top">
										<!-- Menu -->
										{$strNavigation}
									</td>
								</tr>
								<tr><td align="center" height="100">{$logoImage}</td></tr>
							</table>
						</td>
						<td width="535" valign="top">
							<!-- Right column -->
							<table width="100%" height="77" cellpadding="0" cellspacing="0">
								<tr>
									<td width="9" style="background:#e3f0f9;"><img src="/bitrix/images/install/top_gradient_begin.gif" width="9" height="77" alt="" /></td>
									<td class="wizard-title" width="14">&nbsp;</td>
									<td class="wizard-title">{$title}</td>
								</tr>
							</table>
							<div id="step-title">{$stepTitle}</div>
							{#FORM_START#}
							<div id="step-content">
								{$strError}
								{#CONTENT#}
								<br /><br /><br /><div align="right">{#BUTTONS#}</div><br />
							</div>
							
							{#FORM_END#}
						</td>
					</tr>
				</table>
			</td>
		</tr>

		<tr height="20" style="background:#e8e8e8;">
			<td colspan="3">
				<div id="copyright">
					<table width="100%" height="100%" cellpadding="0" cellspacing="5">
						<tr>
							<td>{$copyright}</td>
							<td align="right">{$support}</td>
						</tr>
					</table>
				</div>
		</tr>
		<tr>
			<td width="10" height="10" valign="bottom"><img src="/bitrix/images/install/corner_bottom_left.gif" width="10" height="10" alt="" /></td>
			<td width="100%" style="background:#e8e8e8;"></td>
			<td width="10" height="10" valign="bottom"><img src="/bitrix/images/install/corner_bottom_right.gif" width="10" height="10" alt="" /></td>
		</tr>
	</table>
	<script type="text/javascript">PreloadImages();</script>

</div>
</body>
</html>

HTML;
	}
}
?>