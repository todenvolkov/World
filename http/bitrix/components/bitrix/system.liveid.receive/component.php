<?
if(COption::GetOptionString('main', 'auth_liveid', 'N') <> 'Y')
{
	ShowError(GetMessage("liveid_receive_comp_error"));
	return;
}

$arResult['ERROR'] = false;

$arResult['POST_URL'] = $APPLICATION->GetCurPageParam();

$arResult['LOGIN'] = '';
$arResult['EMAIL'] = '';

$arResult['REDIRECT_URL'] = array_key_exists('DEFAULT_REDIRECT_URL', $arParams) && strlen($arParams['DEFAULT_REDIRECT_URL']) > 0 ? 
	$arParams['DEFAULT_REDIRECT_URL'] : '/';
if (
	array_key_exists('USE_SESSION_URL', $arParams) && $arParams['USE_SESSION_URL'] == 'Y' &&
	array_key_exists('BX_LIVEID_LAST_PAGE', $_SESSION) && strlen($_SESSION['BX_LIVEID_LAST_PAGE']) > 0
	)
{
	$arResult['REDIRECT_URL'] = $_SESSION['BX_LIVEID_LAST_PAGE'];
}

$strCookieName = COption::GetOptionString('main', 'liveid_cookie_name', 'LIVEID');

$wll = new WindowsLiveLogin();
$wll->setAppId(COption::GetOptionString('main', 'liveid_appid'));
$wll->setSecret(COption::GetOptionString('main', 'liveid_secret'));

if (strlen($_REQUEST['action']) > 0)
{
	switch ($_REQUEST['action']) {
		case 'logout':
			$APPLICATION->set_cookie($strCookieName, '', false, '/', false, false, false);
			
			LocalRedirect($arResult['REDIRECT_URL']);
			break;
		case 'clearcookie':
			$APPLICATION->RestartBuffer();
			$APPLICATION->set_cookie($strCookieName, '', false, '/', false, false, false);
			
	
			list($type, $response) = $wll->getClearCookieResponse();
			header("Content-Type: $type");
			echo $response;
			exit();
	
			break;
		default:
			/*@var $wll_user WLL_User*/
			$wll_user = $wll->processLogin($_REQUEST);
	
			if ($wll_user) {
				$APPLICATION->set_cookie($strCookieName, $wll_user->getToken(), false, '/', false, false, false);
				
				$arResult['LIVEID_USERID'] = $wll_user->getId();
				
				$rs = CUser::GetList($b, $o, array('EXTERNAL_AUTH_ID' => 'LIVEID', 'XML_ID' => $arResult['LIVEID_USERID']));
				if ($arUser = $rs->Fetch())
				{
					$USER->Authorize($arUser['ID']);
					LocalRedirect($arResult['REDIRECT_URL']);
				}
				
			}
			else {
				$APPLICATION->set_cookie($strCookieName, '', false, '/', false, false, false);
				LocalRedirect($arResult['REDIRECT_URL']);
			}
	}
}
elseif (array_key_exists('savelogin', $_POST) && $_POST['savelogin'] == 'Y')
{
	$arResult['USER_ID'] = 0;
	$token = $APPLICATION->get_cookie($strCookieName);
	$wll_user = $wll->processToken($token);
	
	if ($wll_user)
	{
		$arResult['LIVEID_USERID'] = $wll_user->getId();
		
		$rs = CUser::GetList($b, $o, array('EXTERNAL_AUTH_ID' => 'LIVEID', 'XML_ID' => $arResult['LIVEID_USERID']));
		if ($arUser = $rs->Fetch())
		{
			$arResult['USER_ID'] = intval($arUser['ID']);
		}
		else 
		{
			if (check_email($_POST['EMAIL']))
			{
				$arFields = array(
					'LOGIN' => $_POST['LOGIN'],
					'EXTERNAL_AUTH_ID' => 'LIVEID',
					'XML_ID' => $arResult['LIVEID_USERID'],
					'EMAIL' => $_POST['EMAIL'],
					'PASSWORD' => randString(),
				);
				
				$def_group = COption::GetOptionString('main', 'new_user_registration_def_group', '');
				if($def_group != '')
				{
					$arFields['GROUP_ID'] = explode(',', $def_group);
				}				
				
				$arResult['USER_ID'] = intval($USER->Add($arFields));
				
				if ($arResult['USER_ID'] <= 0)
				{
					$arResult['ERROR'] = true;
					$arResult['ERROR_TEXT'] = $USER->LAST_ERROR;
				}
			}
			else 
			{
				$arResult['ERROR'] = true;
				$arResult['ERROR_TEXT'] = GetMessage('WRONG_EMAIL');
			}
		}
		
		if ($arResult['USER_ID'] > 0)
		{
			unset($_SESSION['BX_LIVEID_LAST_PAGE']);
			$USER->Authorize($arResult['USER_ID']);
			LocalRedirect($arResult['REDIRECT_URL']);
		}
		
		$arResult['LOGIN'] = htmlspecialchars($_POST['LOGIN']);
		$arResult['EMAIL'] = htmlspecialchars($_POST['EMAIL']);
	}
	else 
	{
		LocalRedirect($arResult['REDIRECT_URL']);
	}
}

$this->IncludeComponentTemplate();

?>