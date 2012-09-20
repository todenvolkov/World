<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?= $javascriptFunctions ?>
<?
$runtime = CBPRuntime::GetRuntime();
$documentService = $runtime->GetService("DocumentService");
foreach ($arDocumentFields as $fieldKey => $fieldValue)
{
	?>
	<tr>
		<td align="right" width="40%"><?= ($fieldValue["Required"]) ? "<span class=\"required\">*</span> " : "" ?><?= htmlspecialchars($fieldValue["Name"]) ?>:</td>
		<td width="60%" id="td_<?= htmlspecialchars($fieldKey) ?>">
			<?
			//echo $documentService->GetGUIFieldEdit($documentType, $formName, $fieldKey, $arCurrentValues[$fieldKey], $fieldValue, true);
			?>
		</td>
	</tr>
	<?
}
?>
<script language="JavaScript">
<?
foreach ($arDocumentFields as $fieldKey => $fieldValue)
{
	?>
	var valueTd = document.getElementById('td_<?= CUtil::JSEscape($fieldKey) ?>');
	valueTd.innerHTML = objFieldsCD.GetGUIFieldEdit('<?= CUtil::JSEscape($fieldKey) ?>', '<?= CUtil::JSEscape($arCurrentValues[$fieldKey]) ?>', true);
	<?
}
?>
</script>