<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
CBPDocument::AddShowParameterInit($arParams["DOCUMENT_TYPE"][0], "only_users", $arParams["DOCUMENT_TYPE"][2], $arParams["DOCUMENT_TYPE"][1]);
?>
<div class="bizproc-page-workflow-start">
<?
if (!empty($arResult["ERROR_MESSAGE"])):
	ShowError($arResult["ERROR_MESSAGE"]);
endif;

if ($arResult["SHOW_MODE"] == "StartWorkflowSuccess")
{
	ShowNote(str_replace("#TEMPLATE#", $arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["NAME"], GetMessage("BPABS_MESSAGE_SUCCESS")));
}
elseif ($arResult["SHOW_MODE"] == "StartWorkflowError")
{
	ShowNote(str_replace("#TEMPLATE#", $arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["NAME"], GetMessage("BPABS_MESSAGE_ERROR")));
}
elseif ($arResult["SHOW_MODE"] == "WorkflowParameters")
{
?>
<form method="post" name="start_workflow_form1" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data">
	<input type="hidden" name="workflow_template_id" value="<?=intval($arParams["TEMPLATE_ID"]) ?>" />
	<input type="hidden" name="document_type" value="<?= htmlspecialchars($arParams["DOCUMENT_TYPE"][2]) ?>" />
	<input type="hidden" name="document_id" value="<?= htmlspecialchars($arParams["DOCUMENT_ID"][2]) ?>" />
	<input type="hidden" name="back_url" value="<?= htmlspecialchars($arResult["back_url"]) ?>" />
	<?= bitrix_sessid_post() ?>
<fieldset class="bizproc-item bizproc-workflow-template">
	<legend class="bizproc-item-legend bizproc-workflow-template-title">
		<?=$arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["NAME"]?>
	</legend>
	<?if($arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["DESCRIPTION"]!=''):?>
	<div class="bizproc-item-description bizproc-workflow-template-description">
		<?= $arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["DESCRIPTION"] ?>
	</div>
	<?endif;

	if (!empty($arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["PARAMETERS"]))
	{
?>
	<div class="bizproc-item-text">
		<ul class="bizproc-list bizproc-workflow-template-params">
<?
	
	foreach ($arResult["TEMPLATES"][$arParams["TEMPLATE_ID"]]["PARAMETERS"] as $parameterKey => $arParameter)
	{
?>
		<li class="bizproc-list-item bizproc-workflow-template-param">
			<div class="bizproc-field bizproc-field-<?=$arParameter["Type"]?>">
				<label class="bizproc-field-name">
					<?=($arParameter["Required"] ? "<span class=\"required\">*</span> " : "")?>
						<span class="bizproc-field-title"><?=$arParameter["Name"]?></span><?
					if (strlen($arParameter["Description"]) > 0):
						?><span class="bizproc-field-description"> (<?=$arParameter["Description"]?>)</span><?
					endif;
					?>:
				</label>
				<span class="bizproc-field-value"><?
				switch ($arParameter["Type"])
				{
					case "int":
					case "double":
						?><input type="text" name="<?= $parameterKey ?>" value="<?= $arResult["PARAMETERS_VALUES"][$parameterKey] ?>" /><?
						break;
					case "string":
						?><input type="text" name="<?= $parameterKey ?>" value="<?= $arResult["PARAMETERS_VALUES"][$parameterKey] ?>" /><?
						break;
					case "text":
						?><textarea name="<?= $parameterKey ?>"><?= $arResult["PARAMETERS_VALUES"][$parameterKey] ?></textarea><?
						break;
					case "select":
						?><select name="<?= $parameterKey ?><?= $arParameter["Multiple"] ? "[]\" size='5' multiple" : "\"" ?>>
						<?
						if (is_array($arParameter["Options"]) && count($arParameter["Options"]) > 0)
						{
							foreach ($arParameter["Options"] as $key => $value)
							{
								?><option value="<?= $key ?>"<?=(!$arParameter["Multiple"] && $key == $arResult["PARAMETERS_VALUES"][$parameterKey] || $arParameter["Multiple"] && is_array($arResult["PARAMETERS_VALUES"][$parameterKey]) && in_array($key, $arResult["PARAMETERS_VALUES"][$parameterKey])) ? " selected=\"selected\"" : "" ?>><?= $value ?></option><?
							}
						}
						?>
						</select><?
						break;
					case "bool":
						?><select name="<?= $parameterKey ?>">
							<option value="Y"<?= ($arResult["PARAMETERS_VALUES"][$parameterKey] == "Y") ? " selected=\"selected\"" : "" ?>><?= GetMessage("BPABS_YES") ?></option>
							<option value="N"<?= ($arResult["PARAMETERS_VALUES"][$parameterKey] == "N") ? " selected=\"selected\"" : "" ?>><?= GetMessage("BPABS_NO") ?></option>
						</select><?
						break;
					case "date":
					case "datetime":
						require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/init_admin.php");
						echo CAdminCalendar::CalendarDate($parameterKey, $arResult["PARAMETERS_VALUES"][$parameterKey], 19, ($arParameter["Type"] == "date"));
						break;
					case "user":
?>
						<table cellpadding="0" border="0" cellspacing="0">
							<tr>
								<td valign="bottom">
									<textarea name="<?= $parameterKey ?>" id="id_<?= $parameterKey ?>"><?= $arResult["PARAMETERS_VALUES"][$parameterKey] ?></textarea>
								</td>
								<td valign="bottom">
									<input type="button" value="..." onclick="BPAShowSelector('id_<?= $parameterKey ?>', 'user');" />
								</td>
							</tr>
						</table>
<?
						break;
					default:
						echo GetMessage("BPABS_INVALID_TYPE");
				}
				?></span>
			</div>
		</li>
<?
	}
?>
		</ul>
	</div>
<?
	}
?>
	<div class="bizproc-item-buttons bizproc-workflow-start-buttons">
		<input type="submit" name="DoStartParamWorkflow" value="<?= GetMessage("BPABS_DO_START") ?>" />
		<input type="submit" name="CancelStartParamWorkflow" value="<?= GetMessage("BPABS_DO_CANCEL") ?>" />
	</div>
</fieldset>
</form>
<?
}
elseif ($arResult["SHOW_MODE"] == "SelectWorkflow" && count($arResult["TEMPLATES"]) > 0)
{
	foreach($_GET as $key => $val):
		if (in_array(strtolower($key), array("sessid", "workflow_template_id")))
			continue;
	endforeach;
	$bFirst = true;
?>
	<ul class="bizproc-list bizproc-workflow-templates">
		<?foreach ($arResult["TEMPLATES"] as $workflowTemplateId => $arWorkflowTemplate):?>
			<li class="bizproc-list-item bizproc-workflow-template">
				<div class="bizproc-item-title">
					<a href="<?=$arResult["TEMPLATES"][$arWorkflowTemplate["ID"]]["URL"]?>"><?=$arWorkflowTemplate["NAME"]?></a>
				</div>
				<?if (strlen($arWorkflowTemplate["DESCRIPTION"]) > 0):?>
				<div class="bizproc-item-description">
					<?= $arWorkflowTemplate["DESCRIPTION"] ?>
				</div>
				<?endif;?>
			</li>
		<?endforeach;?>
	</ul>
<?
}
elseif ($arResult["SHOW_MODE"] == "SelectWorkflow")
{
	ShowNote(GetMessage("BPABS_NO_TEMPLATES"));
}
?>
</div>