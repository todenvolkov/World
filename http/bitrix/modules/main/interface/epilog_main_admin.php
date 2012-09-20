<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/lang_files.php");
?>
<?
//End of Content
?>
</div>
</div>
</div>
					<td>					
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="footerpanel">
<?
//Footer
$vendor = COption::GetOptionString("main", "vendor", "1c_bitrix");

//wizard customization file
if(isset($bxProductConfig["admin"]["copyright"]))
	$sCopyright = $bxProductConfig["admin"]["copyright"];
else
	$sCopyright = GetMessage("EPILOG_ADMIN_POWER").' <a href="'.GetMessage("EPILOG_ADMIN_URL_PRODUCT_".$vendor).'">'.GetMessage("EPILOG_ADMIN_SM_".$vendor).'#VERSION#</a>. '.GetMessage("EPILOG_ADMIN_COPY_".$vendor);
$sVer = ($GLOBALS['USER']->CanDoOperation('view_other_settings')? " ".SM_VERSION : "");
$sCopyright = str_replace("#VERSION#", $sVer, $sCopyright);

if(isset($bxProductConfig["admin"]["links"]))
	$sLinks = $bxProductConfig["admin"]["links"];
else
	$sLinks = '<a href="'.GetMessage("EPILOG_ADMIN_URL_MAIN_".$vendor).'">'.GetMessage("EPILOG_ADMIN_URL_MAIN_TEXT_".$vendor).'</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.GetMessage("EPILOG_ADMIN_URL_SUPPORT_".$vendor).'">'.GetMessage("epilog_support_link").'</a>';
?>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td><?echo $sCopyright?></td>
					<td align="right"><?if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/this_site_support.php")):?><?include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/this_site_support.php");?><?else:?><?echo $sLinks?><?endif;?></td>
				</tr>
			</table>
<?
//End of Footer
?>
		</td>
	</tr>
</table>
<script type="text/javascript">
	jsUtils.addEvent(window, "unload", function(){jsUtils.removeAllEvents(false);});
</script>
</body>
</html>