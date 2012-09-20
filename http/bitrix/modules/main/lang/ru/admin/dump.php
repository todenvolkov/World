<?
$MESS["MAIN_DUMP_FILE_CNT"] = "Файлов сжато:";
$MESS["MAIN_DUMP_FILE_SIZE"] = "Размер файлов:";
$MESS["MAIN_DUMP_FILE_FINISH"] = "Создание резервной копии завершено";
$MESS["MAIN_DUMP_FILE_MAX_SIZE"] = "Исключить из архива файлы размером более (0 - без ограничения):";
$MESS["MAIN_DUMP_FILE_STEP"] = "Шаг:";
$MESS["MAIN_DUMP_FILE_STEP_SLEEP"] = "интервал:";
$MESS["MAIN_DUMP_FILE_STEP_sec"] = "сек.";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_b"] = "б ";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_kb"] = "кб ";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_mb"] = "Мб ";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_gb"] = "Гб ";
$MESS["MAIN_DUMP_FILE_DUMP_BUTTON"] = "Архивировать";
$MESS["MAIN_DUMP_FILE_STOP_BUTTON"] = "Остановить";
$MESS["MAIN_DUMP_FILE_KERNEL"] = "Архивировать ядро:";
$MESS["MAIN_DUMP_FILE_NAME"] = "Имя";
$MESS["FILE_SIZE"] = "Размер файла";
$MESS["MAIN_DUMP_FILE_TIMESTAMP"] = "Изменен";
$MESS["MAIN_DUMP_FILE_PUBLIC"] = "Архивировать публичную часть:";
$MESS["MAIN_DUMP_FILE_TITLE"] = "Файлы";
$MESS["MAIN_DUMP_BASE_STAT"] = "статистику";
$MESS["MAIN_DUMP_BASE_SINDEX"] = "поисковый индекс";
$MESS["MAIN_DUMP_BASE_IGNORE"] = "Исключить из архива:";
$MESS["MAIN_DUMP_BASE_TRUE"] = "Архивировать базу данных:";
$MESS["MAIN_DUMP_BASE_TITLE"] = "База данных";
$MESS["MAIN_DUMP_BASE_SIZE"] = "МБ";
$MESS["MAIN_DUMP_PAGE_TITLE"] = "Резервное копирование";
$MESS["MAIN_DUMP_TAB"] = "Копирование";
$MESS["MAIN_DUMP_SITE_PROC"] = "Сжатие...";
$MESS["MAIN_DUMP_ARC_SIZE"] = "Размер архива:";
$MESS["MAIN_DUMP_TABLE_FINISH"] = "Обработано таблиц:";
$MESS["MAIN_DUMP_ACTION_DOWNLOAD"] = "Скачать";
$MESS["MAIN_DUMP_DELETE"] = "Удалить";
$MESS["MAIN_DUMP_ALERT_DELETE"] = "Вы уверены, что хотите удалить файл?";
$MESS["MAIN_DUMP_FILE_PAGES"] = "Резервные копии";
$MESS["MAIN_RIGHT_CONFIRM_EXECUTE"] = "Внимание! Распаковка резервной копии на действующем сайте может привести к повреждению сайта! Продолжить?";
$MESS["MAIN_DUMP_RESTORE"] = "Распаковать";
$MESS["MAIN_DUMP_ENCODE"] = "Внимание! Вы используете закодированную версию продукта";
$MESS["MAIN_DUMP_MYSQL_ONLY"] = "Система резервного копирования работает только с базой данных MySQL.<br> Пожалуйста, используйте внешние инструменты для создания архива базы данных.";
$MESS["MAIN_DUMP_HEADER_MSG"] = "Для переноса архива сайта на другой хостинг поместите в корневой папке нового сайта скрипт для восстановления <a href='/bitrix/admin/restore_export.php'>restore.php</a> и сам архив, затем наберите в строке браузера &quot;&lt;имя сайта&gt;/restore.php&quot; и следуйте инструкциям по распаковке.<br>Подробная инструкция доступна в <a href='http://dev.1c-bitrix.ru/api_help/main/going_remote.php' target=_blank>разделе справки</a>.";
$MESS["MAIN_DUMP_SKIP_SYMLINKS"] = "Пропускать символические ссылки на директории:";
$MESS["MAIN_DUMP_MASK"] = "Исключить из архива файлы и директории по маске:";
$MESS["MAIN_DUMP_MORE"] = "Ещё...";
$MESS["MAIN_DUMP_FOOTER_MASK"] = "Для маски исключения действуют следующие правила:
	<p>
	<li>шаблон маски может содержать символы &quot;*&quot;, которые соответствуют любому количеству любых символов в имени файла или папки;</li>
	<li>если в начале стоит косая черта (&quot;/&quot; или &quot;\\&quot;), путь считается от корня сайта;</li>
	<li>в противном случае шаблон применяется к каждому файлу или папке;</li>
	<p>Примеры шаблонов:</p>
	<li>/content/photo - исключить целиком папку /content/photo;</li>
	<li>*.zip - исключить файлы с расширением &quot;zip&quot;;</li>
	<li>.access.php - исключить все файлы &quot;.access.php&quot;;</li>
	<li>/files/download/*.zip - исключить файлы с расширением &quot;zip&quot; в директории /files/download;</li>
	<li>/files/d*/*.ht* - исключить файлы из директорий, начинающихся на &quot;/files/d&quot;  с расширениями, начинающимися на &quot;ht&quot;.</li>
	";
$MESS["MAIN_DUMP_ERROR"] = "Ошибка";
$MESS["DUMP_NO_PERMS"] = "Нет прав на сервере на создание архива";
$MESS["DUMP_NO_PERMS_READ"] = "Ошибка открытия архива на чтение";
$MESS["DUMP_DB_CREATE"] = "Создание дампа базы данных";
$MESS["DUMP_CUR_PATH"] = "Текущий путь:";
$MESS["INTEGRITY_CHECK"] = "Проверка целостности";
$MESS["CURRENT_POS"] = "Текущая позиция:";
$MESS["TAB_STANDARD"] = "Стандартные";
$MESS["TAB_STANDARD_DESC"] = "Стандартные режимы создания резервной копии";
$MESS["TAB_ADVANCED"] = "Расширенные";
$MESS["TAB_ADVANCED_DESC"] = "Специальные настройки создания резервной копии";
$MESS["MODE_DESC"] = "Будет создан полный архив публичной части <b>текущего сайта</b> (для многосайтовой конфигурации на разных доменах), <b>ядра продукта</b> и <b>базы данных</b> (только для MySQL), который подходит для полного восстановления системы и переноса на другой сервер. После выбора одного из режимов можно скорректировать настройки на вкладке &quot;<b>Расширенные</b>&quot;.";
$MESS["MODE_VPS"] = "Выделенный сервер или VPS (оптимально по времени)";
$MESS["MODE_SHARED"] = "Стандартный хостинг (подходит для большинства сайтов)";
$MESS["MODE_SLOW"] = "Безопасный режим (если другие режимы не работают: без сжатия, с перерывами между шагами)";
$MESS["PUBLIC_PART"] = "Публичная часть сайта:";
$MESS["SERVER_LIMIT"] = "Серверные ограничения";
$MESS["STEP_LIMIT"] = "Длительность шага:";
$MESS["DISABLE_GZIP"] = "Отключить компрессию архива (снижение нагрузки на процессор):";
$MESS["INTEGRITY_CHECK_OPTION"] = "Проверить целостность архива после завершения:";
$MESS["MAIN_DUMP_DB_PROC"] = "Сжатие дампа базы данных";
$MESS["CDIR_FOLDER_ERROR"] = "Ошибка обработки папки: ";
$MESS["CDIR_FOLDER_OPEN_ERROR"] = "Ошибка открытия папки: ";
$MESS["CDIR_FILE_ERROR"] = "Ошибка обработки файла: ";
$MESS["BACKUP_NO_PERMS"] = "Нет прав на запись в папку /bitrix/backup";
$MESS["TIME_SPENT"] = "Затрачено времени:";
$MESS["TIME_H"] = "час.";
$MESS["TIME_M"] = "мин.";
$MESS["TIME_S"] = "сек.";
?>
