<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentParameters = array(
	'PARAMETERS' => array(
		'DEFAULT_REDIRECT_URL' => array(
			'NAME' => GetMessage('LIVEID_DEFAULT_REDIRECT_URL'), 
			'TYPE' => 'STRING',
			'DEFAULT' => '/',
		),
		'USE_SESSION_URL' => array(
			'NAME' => GetMessage('LIVEID_USE_SESSION_URL'), 
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
	),
);
?>