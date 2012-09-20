<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
?>
<?if($arParams["POPUP"]):?>
<div style="display:none">
<div id="bx_auth_float" class="bx-auth-float">
<?endif?>

<?if($arParams["~CURRENT_SERVICE"] <> ''):?>
<script type="text/javascript">
BX.ready(function(){BxShowAuthService('<?=CUtil::JSEscape($arParams["~CURRENT_SERVICE"])?>', '<?=$arParams["~SUFFIX"]?>')});
</script>
<?endif?>

<div class="bx-auth">
	<form method="post" name="bx_auth_services<?=$arParams["SUFFIX"]?>" target="_top" action="<?=$arParams["AUTH_URL"]?>">
		<div class="bx-auth-title"><?=GetMessage("socserv_as_user")?></div>
		<div class="bx-auth-note"><?=GetMessage("socserv_as_user_note")?></div>
		<div class="bx-auth-services">
<?foreach($arParams["~AUTH_SERVICES"] as $service):?>
			<div><a href="javascript:void(0)" onclick="BxShowAuthService('<?=$service["ID"]?>', '<?=$arParams["SUFFIX"]?>')" id="bx_auth_href_<?=$arParams["SUFFIX"]?><?=$service["ID"]?>"><i class="bx-ss-icon <?=htmlspecialchars($service["ICON"])?>"></i><b><?=htmlspecialchars($service["NAME"])?></b></a></div>
<?endforeach?>
		</div>
		<div class="bx-auth-line"></div>
		<div class="bx-auth-service-form" id="bx_auth_serv<?=$arParams["SUFFIX"]?>" style="display:none">
<?foreach($arParams["~AUTH_SERVICES"] as $service):?>
			<div id="bx_auth_serv_<?=$arParams["SUFFIX"]?><?=$service["ID"]?>" style="display:none"><?=$service["FORM_HTML"]?></div>
<?endforeach?>
		</div>
<?foreach($arParams["~POST"] as $key => $value):?>
		<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
<?endforeach?>
		<input type="hidden" name="auth_service_id" value="" />
	</form>
</div>

<?if($arParams["POPUP"]):?>
</div>
</div>
<?endif?>