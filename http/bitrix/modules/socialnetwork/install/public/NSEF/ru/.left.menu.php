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
		"#SEF_FOLDER#index.php?page=messages_users", 
		Array(), 
		Array(), 
		"\$GLOBALS['USER']->IsAuthorized()" 
	),
	Array(
		"Обновления", 
		"#SEF_FOLDER#index.php?page=log", 
		Array(), 
		Array(), 
		"\$GLOBALS['USER']->IsAuthorized()" 
	),
	Array(
		"Найти людей", 
		"#SEF_FOLDER#index.php?page=search", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Найти группу", 
		"#SEF_FOLDER#index.php?page=group_search", 
		Array(), 
		Array(), 
		"" 
	)
);
?>