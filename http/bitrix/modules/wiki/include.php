<?

if(!CModule::IncludeModule('iblock'))
	return false;

IncludeModuleLangFile(__FILE__);

CModule::AddAutoloadClasses(
	'wiki',
	array(
		'CWiki' => 'classes/general/wiki.php',
		'CWikiUtils'  => 'classes/general/wiki_utils.php',
		'CWikiParser' => 'classes/general/wiki_parser.php',
		'CWikiDiff' => 'classes/general/wiki_diff.php',
		'CWikiSocnet' => 'classes/general/wiki_socnet.php',
		'CWikiDocument' => 'classes/general/wiki_document.php',
		'CWikiSecurity' => 'classes/general/wiki_security.php'
    )
);
	
	
?>