<?
$aMenuLinks = Array(
	Array(
		"Личная страница", 
		"#SEF_FOLDER#index.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Мои сообщения", 
		"#SEF_FOLDER#messages/", 
		Array(), 
		Array(), 
		"\$GLOBALS['USER']->IsAuthorized()" 
	),
	Array(
		"Обновления", 
		"#SEF_FOLDER#log/", 
		Array(), 
		Array(), 
		"\$GLOBALS['USER']->IsAuthorized()" 
	),
	Array(
		"Найти людей", 
		"#SEF_FOLDER#search/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Найти группу", 
		"#SEF_FOLDER#group/search/", 
		Array(), 
		Array(), 
		"" 
	)
);
?>