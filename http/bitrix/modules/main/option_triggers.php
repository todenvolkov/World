<?
function __OnAfterSetOption_disk_space($value)
{
	if(COption::GetOptionInt("main", "disk_space") > 0)
		RegisterModuleDependences("main", "OnEpilog", "main", "CDiskQuota", "setDBSize");
	else
		UnRegisterModuleDependences("main", "OnEpilog", "main", "CDiskQuota", "setDBSize");
}

AddEventHandler("main", 'OnAfterSetOption_disk_space', '__OnAfterSetOption_disk_space');


function __OnAfterSetOption_auth_openid($value)
{
	if ($value == 'Y')
	{
		RegisterModuleDependences('main', 'OnExternalAuthList', 'main', 'COpenIDClient', 'OnExternalAuthList');
	}
	else 
	{
		UnRegisterModuleDependences('main', 'OnExternalAuthList', 'main', 'COpenIDClient', 'OnExternalAuthList');
	}
}

AddEventHandler('main', 'OnAfterSetOption_auth_openid', '__OnAfterSetOption_auth_openid');

function __OnAfterSetOption_auth_liveid($value)
{
	if ($value == 'Y')
	{
		RegisterModuleDependences('main', 'OnExternalAuthList', 'main', 'WindowsLiveLogin', 'OnExternalAuthList');
	}
	else 
	{
		UnRegisterModuleDependences('main', 'OnExternalAuthList', 'main', 'WindowsLiveLogin', 'OnExternalAuthList');
	}
}

AddEventHandler('main', 'OnAfterSetOption_auth_liveid', '__OnAfterSetOption_auth_liveid');


?>