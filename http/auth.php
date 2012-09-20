<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
if($_GET['forgot_password']=='yes'):
$APPLICATION->SetTitle("Запрос пароля на восстановление");
elseif($_GET['change_password']=='yes'):
$APPLICATION->SetTitle("Востановление пароля");
elseif($_GET['register']=='yes'):
$APPLICATION->SetTitle("Регистрация");
else:
$APPLICATION->SetTitle("Авторизация");
endif;
global $USER;
if ($USER->IsAuthorized())
{
if (isset($_REQUEST["backurl"]) && strlen($_REQUEST["backurl"])>0) 
   LocalRedirect($backurl);
}
?> <?global $USER;
if ($USER->IsAuthorized())
{?> 
<p>Вы зарегистрированы и успешно авторизовались.</p>
 
<p><a href="<?=SITE_DIR?>" href="">Вернуться на главную страницу</a></p>
 <?}?>


 <?global $USER;
if (!$USER->IsAuthorized())
{?>


<?if($_GET['forgot_password']=='yes'):?>
 <?$APPLICATION->IncludeComponent(
   "bitrix:system.auth.forgotpasswd","",false
);?>

 <?elseif($_GET['change_password']=='yes'):?>
 <?$APPLICATION->IncludeComponent(
   "bitrix:system.auth.changepasswd","",false
);?>

 <?elseif($_GET['register']=='yes'):?>
 <?$APPLICATION->IncludeComponent(
   "bitrix:main.register",
   "",
   Array(
      "USER_PROPERTY_NAME" => "",
      "SEF_MODE" => "N",
      "SHOW_FIELDS" => Array("NAME", "SECOND_NAME", "LAST_NAME", "PERSONAL_MOBILE", "PERSONAL_CITY", "WORK_COMPANY", "WORK_POSITION", "WORK_WWW", "WORK_PHONE", "WORK_FAX", "WORK_PROFILE", "WORK_LOGO"),
      "REQUIRED_FIELDS" => Array("NAME", "SECOND_NAME", "LAST_NAME", "PERSONAL_MOBILE", "PERSONAL_CITY"),
      "AUTH" => "Y",
      "USE_BACKURL" => "Y",
      "SUCCESS_PAGE" => "",
      "SET_TITLE" => "Y",
      "USER_PROPERTY" => Array("UF_AGENT")
   )
);?>

 <?else:?>
 <?$APPLICATION->IncludeComponent(
   "bitrix:system.auth.form",
   "",
   Array(
      "REGISTER_URL" => SITE_DIR."auth.php",
      "PROFILE_URL" => SITE_DIR."club/",
      "SHOW_ERRORS" => "Y"
   )
);?>
<?endif?> <?}?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>