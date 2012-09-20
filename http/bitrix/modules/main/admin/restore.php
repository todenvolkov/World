<?
# define('VMBITRIX', 'defined');
error_reporting(E_ALL & ~E_NOTICE);

if (version_compare(phpversion(),'5.0.0','<'))
	die('PHP5 is required');

if(strpos($_SERVER['REQUEST_URI'], '/restore.php') !== 0 || !file_exists($_SERVER['DOCUMENT_ROOT'].'/restore.php'))
	die('This script must be started from Web Server\'s DOCUMENT ROOT');

if(isset($_SERVER["BX_PERSONAL_ROOT"]) && $_SERVER["BX_PERSONAL_ROOT"] <> "")
	define("BX_PERSONAL_ROOT", $_SERVER["BX_PERSONAL_ROOT"]);
else
	define("BX_PERSONAL_ROOT", "/bitrix");

if(!defined("START_EXEC_TIME"))
	define("START_EXEC_TIME", microtime(true));

define("STEP_TIME", defined('VMBITRIX') ? 30 : 15);
# define("DELAY", defined('VMBITRIX') ? 0 : 3); // reserved

if (function_exists('mb_internal_encoding'))
{
	switch (ini_get("mbstring.func_overload"))
	{
		case 0:
			$bUTF_serv = false;
		break;
		case 2:
			$bUTF_serv = mb_internal_encoding() == 'UTF-8';
		break;
		default:
			die('PHP parameter mbstring.func_overload='.ini_get("mbstring.func_overload").'. The only supported values are 0 or 2.');
		break;
	}
	mb_internal_encoding('ISO-8859-1');
}
else
	$bUTF_serv = false;


# http://bugs.php.net/bug.php?id=48886 - We have 2Gb file limit on Linux

#@set_time_limit(0);
ob_start();
 
if (@preg_match('#ru#i',$_SERVER['HTTP_ACCEPT_LANGUAGE']))
	$lang = 'ru';
elseif (@preg_match('#de#i',$_SERVER['HTTP_ACCEPT_LANGUAGE']))
	$lang = 'de';
if ($_REQUEST['lang'])
	$lang = $_REQUEST['lang'];
if (!in_array($lang,array('ru','en')))
	$lang = 'en';
define("LANG", $lang);
if (LANG=='ru' && !headers_sent())
	header("Content-type:text/html; charset=windows-1251");

$dbconn = $_SERVER['DOCUMENT_ROOT']."/bitrix/php_interface/dbconn.php";

$arc_name = $_REQUEST["arc_name"];
$mArr_ru =  array(
			"WINDOW_TITLE" => "Восстановление архива",
			"BACK" => "Назад",
			"BEGIN" => "
			<p>
			<ul>
			<li>Перейдите в административную панель своего сайта на страницу <b>Настройки &gt; Инструменты &gt; Резервное копирование</b>
			<li>Создайте полную резервную копию, которая будет включать <b>публичную часть</b>, <b>ядро</b> и <b>базу данных</b>
			</ul>
			<b>Документация:</b> <a href='http://dev.1c-bitrix.ru/api_help/main/going_remote.php' target='_blank'>http://dev.1c-bitrix.ru/api_help/main/going_remote.php</a>
			</p>
			",
			"ARC_DOWN" => "Скачать архив с дальнего сайта",
			"DB_SELECT" => "Выберите дамп БД:",
			"DB_SETTINGS" => "Данные для подключения к базе данных",
			"DB_DEF" => "по умолчанию для выделенного сервера или виртуальной машины",
			"DB_ENV" => "восстановление в &quot;Битрикс: Веб-окружение&quot;",
			"DB_OTHER" => "установить значения вручную",
			"ARC_DOWN_SITE" => "адрес сайта (www.site.ru):",
			"DELETE_FILES" => "Удалить архив и служебные скрипты",
			"ARC_DOWN_NAME" => "имя архива (2010-09-20.12-43-39.a269376c.tar.gz):",
			"OR" => "ИЛИ",
			"ARC_DOWN_URL" => "прямой URL архива (http://site.ru/2010-09-20.12-43-39.a269376c.tar.gz):",
			"NO_FILES" => "нет архивов",
			"TITLE0" => "Шаг 1: Подготовка архива",
			"TITLE1" => "Шаг 2: Распаковка архива",
			"TITLE_PROCESS1" => "Шаг 2: Выполняется распаковка архива",
			"TITLE_PROCESS2" => "Шаг 3: Выполняется восстановление базы данных",
			"TITLE2" => "Шаг 3: Восстановление базы данных",
			"SELECT_LANG" => "Выберите язык",
			"ARC_SKIP" => "Архив уже распакован",
			"ARC_SKIP_DESC" => "переход к восстановлению базы данных",
			"ARC_NAME" => "Архив загружен в корневую папку сервера",
			"ARC_DOWN_PROCESS" => "Загружается:",
			"ARC_LOCAL" => "Загрузить с локального диска",
			"MAX_TIME" => "Шаг выполнения (сек.)",
			"ERR_NO_ARC" => "Не выбран архив для распаковки!",
			"BUT_TEXT1" => "Далее",
			"BUT_TEXT_BACK" => "Назад",
			"DUMP_NAME" => "Файл резервной копии базы:",
			"USER_NAME" => "Имя пользователя базы данных",
			"USER_PASS" => "Пароль",
			"BASE_NAME" => "Имя базы данных",
			"BASE_HOST" => "Адрес сервера базы данных",
			"BASE_RESTORE" => "Восстановить",
			"ERR_NO_DUMP" => "Не выбран архив базы данных для восстановления!",
			"ERR_EXTRACT" => "Ошибка",
			"ERR_UPLOAD" => "Не удалось загрузить файл на сервер",
			"ERR_DUMP_RESTORE" => "Ошибка восстановления базы данных",
			"ERR_DB_CONNECT" => "Ошибка соединения с базой данных",
			"ERR_CREATE_DB" => "Ошибка создания базы",
			"FINISH" => "Операция выполнена успешно",
			"FINISH_MSG" => "Операция восстановления системы завершена.",
			"EXTRACT_FINISH_TITLE" => "Распаковка архива",
			"EXTRACT_FINISH_MSG" => "Распаковка архива завершена.",
			"BASE_CREATE_DB" => "Создать базу данных",
			"EXTRACT_FINISH_DELL" => "Обязательно удалите скрипт restore.php и файл резервной копии из корневой директории сайта.",
			"EXTRACT_FULL_FINISH_DELL" => "Обязательно удалите скрипт restore.php, файл резервной копии из корневой директории сайта, а также дамп базы.",
			"BUT_DELL" => "Удалить",
			"FINISH_ERR_DELL" => "Не удалось удалить все временные файлы! Обязательно удалите их вручную.",
			"FINISH_ERR_DELL_TITLE" => "Ошибка удаления файлов!",
			"NO_READ_PERMS" => "Нет прав на чтение корневой папки сайта",
			"UTF8_ERROR1" => "Внимание! Сайт работал в кодировке UTF-8. Конфигурация сервера не соответствует требованиям, установите mbstring.func_overload=2 и mbstring.internal_encoding=UTF-8.",
			"UTF8_ERROR2" => "Внимание! Сайт работал в однобайтовой кодировке, а конфигурация сервера рассчитана на кодировку UTF-8. Установите mbstring.func_overload=0 или mbstring.internal_encoding=ISO-8859-1.",
			"DOC_ROOT_WARN" => "Внимание! В настройках сайта указан путь к корневой папке, убедитесь, что путь установлен правильно, иначе возникнут проблемы с доступом к публичной части.",
			"HTACCESS_WARN" => "Внимание! Файл .htaccess из архива был сохранен в корне сайта под именем .htaccess.restore, т.к. он может содержать директивы, недопустимые на данном сервере. Пожалуйста, переименуйте его вручную через FTP.",

			"NOT_SAAS_ENV" => "Вы используете дистрибутив SaaS, он может быть развернут только на SaaS окружении",
			"NOT_SAAS_DISTR" => "Вы работаете на SaaS окружении, необходимо использовать редакцию SaaS",

'TAR_WRONG_BLOCK_SIZE' => 'Неверный размер блока: ',
'TAR_ERR_FORMAT' => 'Архив поврежден, ошибочный блок: ',
'TAR_EMPTY_FILE' => 'Пустое имя файла, ошибочный блок: ',
'TAR_ERR_CRC' => 'Ошибка контрольной суммы на файле: ',
'TAR_ERR_FOLDER_CREATE' => 'Не удалось создать папку: ',
'TAR_ERR_FILE_CREATE' => 'Не удалось создать файл: ',
'TAR_ERR_FILE_OPEN' => 'Не удалось открыть файл: ',
'TAR_ERR_FILE_SIZE' => 'Размер файла отличается: ',
'TAR_ERR_WRITE_HEADER' => 'Ошибка записи заголовка',
'TAR_PATH_TOO_LONG' => 'Слишком длинный путь: ',
'TAR_ERR_FILE_READ' => 'Ошибка чтения файла: ',
'TAR_ERR_FILE_WRITE' => 'Ошибка записи на файле: ',
'TAR_ERR_FILE_NO_ACCESS' => 'Нет доступа к файлу: ',
'TAR_NO_GZIP' => 'Не доступна функция gzopen',
			);

$mArr_en = array(
			"WINDOW_TITLE" => "Restoring",
			"BACK" => "Back",
			"BEGIN" => "
			<p>
			<ul>
			<li>Step 1. Open Control Panel section of your old site and select <b>Settings &gt; Tools &gt; Backup</b>
			<li>Create full archive which contains <b>public site files</b>, <b>kernel files</b> and <b>database dump</b>
			</ul>
			<b>Documentation:</b> <a href='http://www.bitrixsoft.com/support/training/course/lesson.php?COURSE_ID=12&ID=441' target='_blank'>learning course</a>
			</p>
			",
			"ARC_DOWN" => "Download from remote server",
			"DB_SELECT" => "Select Database Dump:",
			"DB_SETTINGS" => "Database settings",
			"DB_DEF" => "default values for Dedicated Server or Virtual Machine",
			"DB_ENV" => "restoring in Bitrix Environment",
			"DB_OTHER" => "custom database settings",
			"ARC_DOWN_SITE" => "Server URL (www.site.com):",
			"DELETE_FILES" => "Delete archive and temporary scripts",
			"ARC_DOWN_NAME" => "Archive name (2010-09-20.12-43-39.a269376c.tar.gz):",
			"OR" => "OR",
			"ARC_DOWN_URL" => "Archive URL (http://www.site.com/2010-09-20.12-43-39.a269376c.tar.gz):",
			"NO_FILES" => "no archives found",
			"TITLE0" => "Step 1: Archive Creation",
			"TITLE1" => "Step 2: Archive Extracting",
			"TITLE_PROCESS1" => "Step 2: Extracting an archive...",
			"TITLE_PROCESS2" => "Step 3: Restoring database...",
			"TITLE2" => "Step 3: Database restore",
			"SELECT_LANG" => "Choose the language",
			"ARC_SKIP" => "Archive is already extracted",
			"ARC_SKIP_DESC" => "Starting database restore",
			"ARC_NAME" => "Archive is stored in document root folder",
			"ARC_DOWN_PROCESS" => "Downloading:",
			"ARC_LOCAL" => "Upload from local disk",
			"MAX_TIME" => "Step (sec.)",
			"ERR_NO_ARC" => "Archive for extracting is not specified!",
			"BUT_TEXT1" => "Continue",
			"BUT_TEXT_BACK" => "Back",
			"DUMP_NAME" => "Database dump file:",
			"USER_NAME" => "Database User Name",
			"USER_PASS" => "Password",
			"BASE_NAME" => "Database Name",
			"BASE_HOST" => "Database Host",
			"BASE_RESTORE" => "Restore",
			"ERR_NO_DUMP" => "Database dump file is not specified!",
			"ERR_EXTRACT" => "Error",
			"ERR_UPLOAD" => "Unable to upload file",
			"ERR_DUMP_RESTORE" => "Error restoring the database:",
			"ERR_DB_CONNECT" => "Error connecting the database:",
			"ERR_CREATE_DB" => "Error creating the database",
			"FINISH" => "Successfully completed",
			"FINISH_MSG" => "Restoring of the system was completed.",
			"EXTRACT_FINISH_TITLE" => "Archive extracting",
			"EXTRACT_FINISH_MSG" => "Archive extracting was completed.",
			"BASE_CREATE_DB" => "Create database",
			"EXTRACT_FINISH_DELL" => "Warning! You should delete restore.php script and backup copy file from the root folder of your site!",
			"EXTRACT_FULL_FINISH_DELL" => "Warning! You should delete restore.php script, backup copy file and database dump from the root folder of your site!",
			"BUT_DELL" => "Delete",
			"FINISH_ERR_DELL" => "Failed to delete temporary files! You should delete them manually",
			"FINISH_ERR_DELL_TITLE" => "Error deleting the files!",
			"NO_READ_PERMS" => "No permissions for reading Web Server root",
			"UTF8_ERROR1" => "Warning! Your server is not configured for UTF-8 encoding. Please set mbstring.func_overload=2 and mbstring.internal_encoding=UTF-8.",
			"UTF8_ERROR2" => "Warning! Your server is configured for UTF-8 encoding. Please set mbstring.func_overload=0 or mbstring.internal_encoding=ISO-8859-1.",
			"DOC_ROOT_WARN" => "Warning:  In the site settings, make sure that the path to the root folder is correct, otherwise access to the public part will be promblematic or impossible.",
			"HTACCESS_WARN" => "Warning! The file .htaccess was saved as .htaccess.restore, because it may contain directives which are not permitted on this server.  Please rename it manually using FTP.",

'TAR_WRONG_BLOCK_SIZE' => 'Wrong Block size: ',
'TAR_ERR_FORMAT' => 'Archive is currupted, error block: ',
'TAR_EMPTY_FILE' => 'Empty filename, block: ',
'TAR_ERR_CRC' => 'Checksum error on file: ',
'TAR_ERR_FOLDER_CREATE' => 'Can\'t create folder: ',
'TAR_ERR_FILE_CREATE' => 'Can\'t create file: ',
'TAR_ERR_FILE_OPEN' => 'Can\'t open file: ',
'TAR_ERR_FILE_SIZE' => 'Filesize differs: ',
'TAR_ERR_WRITE_HEADER' => 'Error writing header',
'TAR_PATH_TOO_LONG' => 'Path is too long: ',
'TAR_ERR_FILE_READ' => 'Error reading file: ',
'TAR_ERR_FILE_WRITE' => 'Error adding file: ',
'TAR_ERR_FILE_NO_ACCESS' => 'No access to file: ',
'TAR_NO_GZIP' => 'PHP extension GZIP is not available',
			);

	$MESS = array();
	if (LANG=="ru")
	{
		$MESS["LOADER_SUBTITLE1"] = "Загрузка архива";
		$MESS["LOADER_SUBTITLE1_ERR"] = "Ошибка загрузки";
		$MESS["STATUS"] = "% выполнено...";
		$MESS["LOADER_MENU_UNPACK"] = "Распаковка файла";
		$MESS["LOADER_TITLE_LIST"] = "Выбор файла";
		$MESS["LOADER_TITLE_LOAD"] = "Загрузка файла на сайт";
		$MESS["LOADER_TITLE_UNPACK"] = "Распаковка файла";
		$MESS["LOADER_TITLE_LOG"] = "Отчет по загрузке";
		$MESS["LOADER_NEW_LOAD"] = "Загрузить";
		$MESS["LOADER_BACK_2LIST"] = "Вернуться в список файлов";
		$MESS["LOADER_LOG_ERRORS"] = "Загрузка архива не удалась";
		$MESS["LOADER_NO_LOG"] = "Log-файл не найден";
		$MESS["LOADER_KB"] = "кб";
		$MESS["LOADER_LOAD_QUERY_SERVER"] = "Подключение к серверу...";
		$MESS["LOADER_LOAD_QUERY_DISTR"] = "Запрашиваю файл #DISTR#";
		$MESS["LOADER_LOAD_CONN2HOST"] = "Подключение к серверу #HOST#...";
		$MESS["LOADER_LOAD_NO_CONN2HOST"] = "Не могу соединиться с #HOST#:";
		$MESS["LOADER_LOAD_QUERY_FILE"] = "Запрашиваю файл...";
		$MESS["LOADER_LOAD_WAIT"] = "Ожидаю ответ...";
		$MESS["LOADER_LOAD_SERVER_ANSWER"] = "Ошибка загрузки. Сервер ответил: #ANS#";
		$MESS["LOADER_LOAD_SERVER_ANSWER1"] = "Ошибка загрузки. У вас нет прав на доступ к этому файлу. Сервер ответил: #ANS#";
		$MESS["LOADER_LOAD_NEED_RELOAD"] = "Ошибка загрузки. Докачка файла невозможна.";
		$MESS["LOADER_LOAD_NO_WRITE2FILE"] = "Не могу открыть файл #FILE# на запись";
		$MESS["LOADER_LOAD_LOAD_DISTR"] = "Загружаю файл #DISTR#";
		$MESS["LOADER_LOAD_ERR_SIZE"] = "Ошибка размера файла";
		$MESS["LOADER_LOAD_ERR_RENAME"] = "Не могу переименовать файл #FILE1# в файл #FILE2#";
		$MESS["LOADER_LOAD_CANT_OPEN_WRITE"] = "Не могу открыть файл #FILE# на запись";
		$MESS["LOADER_LOAD_CANT_OPEN_READ"] = "Не могу открыть файл #FILE# на чтение";
		$MESS["LOADER_LOAD_LOADING"] = "Загружаю файл... дождитесь окончания загрузки...";
		$MESS["LOADER_LOAD_FILE_SAVED"] = "Файл сохранен: #FILE# [#SIZE# байт]";
		$MESS["LOADER_UNPACK_ACTION"] = "Распаковываю файл... дождитесь окончания распаковки...";
		$MESS["LOADER_UNPACK_UNKNOWN"] = "Неизвестная ошибка. Повторите процесс еще раз или обратитесь в службу технической поддержки";
		$MESS["LOADER_UNPACK_SUCCESS"] = "Файл успешно распакован";
		$MESS["LOADER_UNPACK_ERRORS"] = "Файл распакован с ошибками";
		$MESS["LOADER_KEY_DEMO"] = "Демонстрационная версия";
		$MESS["LOADER_KEY_COMM"] = "Коммерческая версия";
		$MESS["UPDATE_SUCCESS"] = "Обновлено успешно. <a href='?'>Открыть</a>.";
		$MESS["LOADER_NEW_VERSION"] = "Доступна новая версия скрипта восстановления, но загрузить её не удалось";
	}
	else
	{
		$MESS["LOADER_SUBTITLE1"] = "Loading";
		$MESS["LOADER_SUBTITLE1_ERR"] = "Loading Error";
		$MESS["STATUS"] = "% done...";
		$MESS["LOADER_MENU_LIST"] = "Select package";
		$MESS["LOADER_MENU_UNPACK"] = "Unpack file";
		$MESS["LOADER_TITLE_LIST"] = "Select file";
		$MESS["LOADER_TITLE_LOAD"] = "Uploading file to the site";
		$MESS["LOADER_TITLE_UNPACK"] = "Unpack file";
		$MESS["LOADER_TITLE_LOG"] = "Upload report";
		$MESS["LOADER_NEW_ED"] = "package edition";
		$MESS["LOADER_NEW_AUTO"] = "automatically start unpacking after loading";
		$MESS["LOADER_NEW_STEPS"] = "load gradually with interval:";
		$MESS["LOADER_NEW_STEPS0"] = "unlimited";
		$MESS["LOADER_NEW_LOAD"] = "Download";
		$MESS["LOADER_BACK_2LIST"] = "Back to packages list";
		$MESS["LOADER_LOG_ERRORS"] = "Error occured";
		$MESS["LOADER_NO_LOG"] = "Log file not found";
		$MESS["LOADER_KB"] = "kb";
		$MESS["LOADER_LOAD_QUERY_SERVER"] = "Connecting server...";
		$MESS["LOADER_LOAD_QUERY_DISTR"] = "Requesting package #DISTR#";
		$MESS["LOADER_LOAD_CONN2HOST"] = "Connection to #HOST#...";
		$MESS["LOADER_LOAD_NO_CONN2HOST"] = "Cannot connect to #HOST#:";
		$MESS["LOADER_LOAD_QUERY_FILE"] = "Requesting file...";
		$MESS["LOADER_LOAD_WAIT"] = "Waiting for response...";
		$MESS["LOADER_LOAD_SERVER_ANSWER"] = "Error while downloading. Server reply was: #ANS#";
		$MESS["LOADER_LOAD_SERVER_ANSWER1"] = "Error while downloading. Your can not download this package. Server reply was: #ANS#";
		$MESS["LOADER_LOAD_NEED_RELOAD"] = "Error while downloading. Cannot resume download.";
		$MESS["LOADER_LOAD_NO_WRITE2FILE"] = "Cannot open file #FILE# for writing";
		$MESS["LOADER_LOAD_LOAD_DISTR"] = "Downloading package #DISTR#";
		$MESS["LOADER_LOAD_ERR_SIZE"] = "File size error";
		$MESS["LOADER_LOAD_ERR_RENAME"] = "Cannot rename file #FILE1# to #FILE2#";
		$MESS["LOADER_LOAD_CANT_OPEN_WRITE"] = "Cannot open file #FILE# for writing";
		$MESS["LOADER_LOAD_CANT_OPEN_READ"] = "Cannot open file #FILE# for reading";
		$MESS["LOADER_LOAD_LOADING"] = "Download in progress. Please wait...";
		$MESS["LOADER_LOAD_FILE_SAVED"] = "File saved: #FILE# [#SIZE# bytes]";
		$MESS["LOADER_UNPACK_ACTION"] = "Unpacking the package. Please wait...";
		$MESS["LOADER_UNPACK_UNKNOWN"] = "Unknown error occured. Please try again or consult the technical support service";
		$MESS["LOADER_UNPACK_SUCCESS"] = "The file successfully unpacked";
		$MESS["LOADER_UNPACK_ERRORS"] = "Errors occured while unpacking the file";
		$MESS["LOADER_KEY_DEMO"] = "Demo version";
		$MESS["LOADER_KEY_COMM"] = "Commercial version";
		$MESS["UPDATE_SUCCESS"] = "Successful update. <a href='?'>Open</a>.";
		$MESS["LOADER_NEW_VERSION"] = "Error occured while updating restore.php script!";
	}

$strErrMsg = '';
if (defined('VMBITRIX'))
{
	$this_script_name = basename(__FILE__);
	$bx_host = 'www.1c-bitrix.ru';
	$bx_url = '/download/files/scripts/'.$this_script_name;
	$form = '';

	// Check for updates
	$res = @fsockopen($bx_host, 80, $errno, $errstr, 3);

	if($res) 
	{
		$strRequest = "HEAD ".$bx_url." HTTP/1.1\r\n";
		$strRequest.= "Host: ".$bx_host."\r\n";
		$strRequest.= "\r\n";

		fputs($res, $strRequest);

		while ($line = fgets($res, 4096))
		{
			if (@preg_match("/Content-Length: *([0-9]+)/i", $line, $regs))
			{
				if (filesize(__FILE__) != trim($regs[1]))
				{
					$tmp_name = $this_script_name.'.tmp';
					if (LoadFile('http://'.$bx_host.$bx_url, $tmp_name, 0))
					{
						if (rename($_SERVER['DOCUMENT_ROOT'].'/'.$tmp_name,__FILE__))
						{
							bx_accelerator_reset();
							echo '<script>document.location="?lang='.LANG.'";</script>'.LoaderGetMessage('UPDATE_SUCCESS');
							die();
						}
						else
							$strErrMsg = str_replace("#FILE#", $this_script_name, LoaderGetMessage("LOADER_LOAD_CANT_OPEN_WRITE"));
					}
					else
						$strErrMsg = LoaderGetMessage('LOADER_NEW_VERSION');
				}
				break;
			}
		}
		fclose($res);
	}
}

$bSelectDumpStep = false;
if ($_REQUEST['source']=='dump')
	$bSelectDumpStep = true;

$Step = IntVal($_REQUEST["Step"]);

if ($Step == 2 && !$bSelectDumpStep)
{
	if ($_REQUEST['source']=='download')
	{
		$url = $_REQUEST['arc_down_url'];
		if (!$url)
			$url = $_REQUEST['arc_down_site'].'/bitrix/backup/'.$_REQUEST['arc_down_name'];

		if (!preg_match('#http://#',$url))
			$url = 'http://'.$url;
		$arc_name = basename($url);
		if (!preg_match("#\.tar(\.gz)?(.[0-9]+)?$#",$arc_name))
			$arc_name = 'archive.tar.gz';
		$strLog = '';
		$status = '';
		
		if ($_REQUEST['continue'])
			$res = LoadFile($url, $_SERVER['DOCUMENT_ROOT'].'/'.$arc_name);
		else
		{
			$res = 2;
			SetCurrentProgress(0);
		}

		if (!$res)
		{
			$ar = array(
				'TITLE' => LoaderGetMessage('LOADER_SUBTITLE1_ERR'),
				'TEXT' => nl2br($strLog),
				'BOTTOM' => '<input type="button" value="'.getMsg('BUT_TEXT_BACK').'" onClick="document.location=\'/restore.php?Step=1&lang='.LANG.'\'"> '
			);
			html($ar);
			die();
		}
		elseif ($res==2) // частичная закачка
		{
			$text = getMsg('ARC_DOWN_PROCESS').' <b>'.htmlspecialchars($arc_name).'</b>' . $status .
			'<input type=hidden name=Step value=2>'.
			'<input type=hidden name=source value=download>'.
			'<input type=hidden name=continue value=Y>'.
			'<input type=hidden name=arc_down_url value="'.htmlspecialchars($url).'">';
		}
		else
		{
			$tar = new CTar;
			$next_arc_name = $tar->getNextName($arc_name);
			$next_url = str_replace($arc_name, $next_arc_name, $url);
			$res = LoadFile($next_url,$next_arc_name);
			if ($res != false)
			{
				$text = getMsg('ARC_DOWN_PROCESS').' <b>'.htmlspecialchars($next_arc_name).'</b>' . $status .
				'<input type=hidden name=Step value=2>'.
				'<input type=hidden name=source value=download>'.
				'<input type=hidden name=continue value=Y>'.
				'<input type=hidden name=arc_down_url value="'.htmlspecialchars($next_url).'">';
			}
			else
			{
				$text = $status .
				'<input type=hidden name=Step value=2>'.
				'<input type=hidden name=arc_name value="'.htmlspecialchars(preg_replace('#\.[0-9]+$#','',$arc_name)).'">';
			}
		}
		$bottom = '<input type="button" value="'.getMsg('BUT_TEXT_BACK').'" onClick="document.location=\'/restore.php?Step=1&lang='.LANG.'\'"> ';
		showMsg(LoaderGetMessage('LOADER_SUBTITLE1'),$text,$bottom);
		?><script>reloadPage(2, '<?= LANG?>', 1);</script><?
		die();
	}
	elseif($_REQUEST['source']=='upload')
	{
		$tmp = $_FILES['archive'];
		$arc_name = $_REQUEST['arc_name'] = 'uploaded_archive.tar.gz';
		if (move_uploaded_file($tmp['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/'.$arc_name))
		{
			$text = 
			'<input type=hidden name=Step value=2>'.
			'<input type=hidden name=arc_name value="'.($arc_name).'">';
			showMsg(LoaderGetMessage('LOADER_SUBTITLE1'),$text);
			?><script>reloadPage(2, '<?= LANG?>', 1);</script><?
			die();
		}
		else
		{
			$ar = array(
				'TITLE' => getMsg('ERR_EXTRACT'),
				'TEXT' => getMsg('ERR_UPLOAD'),
				'BOTTOM' => '<input type="button" value="'.getMsg('BUT_TEXT_BACK').'" onClick="document.location=\'/restore.php?Step=1&lang='.LANG.'\'"> '
			);
			html($ar);
			die();
		}
	}
}
elseif($Step == 3)
{
	$d_pos = (double) $_REQUEST["d_pos"];
	if ($d_pos < 0)
		$d_pos = 0;

	if ($_REQUEST['db_settings']=='default' || $_REQUEST['db_settings'] == 'env')
	{
		$_REQUEST['db_host'] = 'localhost'.($_REQUEST['db_settings'] == 'env' ? ':31006' : '');
		$_REQUEST['db_name'] = 'bitrix';
		$_REQUEST['db_user'] = 'root';
		$_REQUEST['db_pass'] = '';
		$_REQUEST['create_db'] = 'Y';
	}

	$oDB = new CDBRestore($_REQUEST["db_host"], $_REQUEST["db_name"], $_REQUEST["db_user"], $_REQUEST["db_pass"], $_REQUEST["dump_name"], $d_pos);

	if(!$oDB->Connect())
	{
		$strErrMsg = $oDB->getError();
		$Step = 2;
		$bSelectDumpStep = true;
	}
}





if(!$Step)
{
	$ar = array(
		'TITLE' => getMsg("TITLE0", LANG),
		'TEXT' => 
			($strErrMsg ? '<div style="color:red;padding:10px;border:1px solid red">'.$strErrMsg.'</div>' : '').
			getMsg('BEGIN') .
			'<br>' . 
			(file_exists($img = 'images/dump'.(LANG=='ru'?'_ru':'').'.png') ? '<img src="'.$img.'">' : ''),
		'BOTTOM' => 
		(defined('VMBITRIX') ? '<input type=button value="'.getMsg('BUT_TEXT_BACK').'" onClick="document.location=\'/\'"> ' : '').
		'<input type="button" value="'.getMsg("BUT_TEXT1", LANG).'" onClick="reloadPage(1,\''.LANG.'\')">'
	);
	html($ar);
}
elseif($Step == 1)
{
	$ar = array(
		'TITLE' => getMsg("TITLE1", LANG),
		'TEXT' =>' 
				<div class=t_div>
					<input type=radio name=source value=download id=val1 onclick="div_show(1)"><label for=val1>'.getMsg("ARC_DOWN", LANG).'</label>
					<div id=div1 class="div-tool" style="display:none" align="right">
				'.getMsg("ARC_DOWN_SITE").' <input name=arc_down_site size=20><br>
				'.getMsg("ARC_DOWN_NAME").' <input name=arc_down_name size=20><br>
										'.getMsg("OR").'<br>
				'.getMsg("ARC_DOWN_URL").' <input name=arc_down_url size=40><br>
					</div>
				</div>
				<div class=t_div>
					<input type=radio name=source value=upload id=val2 onclick="div_show(2)"><label for=val2>'. getMsg("ARC_LOCAL", LANG).'</label>
					<div id=div2 class="div-tool" style="display:none">
						<input type=file name=archive size=40>
					</div>
				</div>
				<div class=t_div>
					<input type=radio name=source value=local id=val3 onclick="div_show(3)"><label for=val3>'.getMsg("ARC_NAME", LANG).'</label>
					<div id=div3 class="div-tool" style="display:none">'
					.(
						strlen($option = getArcList()) 
						? 
						'<select class="selectitem" name="arc_name">'.$option.'</select>' 
						: 
						'<span style="color:#999999">'.getMsg('NO_FILES', LANG).'</span>'
					).
					'</div>'.
					($option === false ? '<div style="color:red">'.getMsg('NO_READ_PERMS', LANG).'</div>' : '').
				'</div>'.
				'<div class=t_div>'.
					'<input type=radio name=source value=dump id=val4 onclick="div_show(4)"><label for=val4>'.getMsg("ARC_SKIP", LANG).'</label>
					<div id=div4 class="div-tool" style="display:none;color:#999999">'.getMsg('ARC_SKIP_DESC').'</div>
				</div>'
				,
		'BOTTOM' => 
		'<input type="button" value="'.getMsg('BUT_TEXT_BACK').'" onClick="document.location=\'/restore.php?Step=1&lang='.LANG.'\'"> '.
		'<input type="button" class="selectitem" id="start_button" value="'.getMsg("BUT_TEXT1", LANG).'" onClick="reloadPage(2,\''.LANG.'\')">'
	);
	html($ar);
	?>
	<script>
		function div_show(i)
		{
			document.getElementById('div1').style.display='none';
			document.getElementById('div2').style.display='none';
			document.getElementById('div3').style.display='none';
			document.getElementById('div4').style.display='none';
			document.getElementById('div'+i).style.display='block';
		}
	</script>
	<style type="text/css">
		.div-tool
		{
			border:1px solid #CCCCCC;
			padding:10px;
		}
		.t_div
		{
			padding:5px;
		}
	</style>
	<?
}
elseif($Step == 2)
{
	if(!$bSelectDumpStep)
	{
		$tar = new CTarRestore;
		$tar->path = $_SERVER['DOCUMENT_ROOT'];
		$tar->ReadBlockCurrent = intval($_REQUEST['ReadBlockCurrent']);

		$bottom = '<input type="button" value="'.getMsg('BUT_TEXT_BACK').'" onClick="document.location=\'/restore.php?Step=1&lang='.LANG.'\'"> ';
		if ($tar->openRead($_SERVER['DOCUMENT_ROOT'].'/'.$arc_name))
		{
			$DataSize = intval($_REQUEST['DataSize']);
			if(!isset($_REQUEST['Block'])) // first step
			{
				$DataSize = $tar->getArchiveSize();
				while(file_exists($file = $tar->getNextName($file)))
					$DataSize += $tar->getArchiveSize($file);
				SetCurrentProgress(0);
				$r = true;
			} 
			else
			{
				$Block = intval($_REQUEST['Block']);
				$tar->Skip($Block);
				while(($r = $tar->extractFile()) && haveTime());
			}


			if($r === false) // Error
				showMsg(getMsg("ERR_EXTRACT", LANG), implode('<br>',$tar->err), $bottom);
			elseif ($r === 0) // Finish
				$bSelectDumpStep = true;
			else
			{
				SetCurrentProgress(($tar->BlockHeader + $tar->ReadBlockCurrent) * 512,$DataSize, $red=false);

				$text = $status .
				'<input type="hidden" name="Block" value="'.$tar->BlockHeader.'">'.
				'<input type="hidden" name="ReadBlockCurrent" value="'.$tar->ReadBlockCurrent.'">'.
				'<input type="hidden" name="DataSize" value="'.$DataSize.'">'.
				'<input type="hidden" name="arc_name" value="'.$arc_name.'">';
				showMsg(getMsg('TITLE_PROCESS1'),$text,$bottom);
				?><script>reloadPage(2, '<?= LANG?>', 1);</script><?
			}
			$tar->close();
		}
		else
			showMsg(getMsg("ERR_EXTRACT", LANG), implode('<br>',$tar->err),$bottom);
	}

	if ($bSelectDumpStep)
	{
		if(file_exists($dbconn))
		{
			include($dbconn);
			$bUTF_conf = (defined('BX_UTF') && BX_UTF === true);

			if ($bUTF_conf && !$bUTF_serv)
				$strErrMsg = getMsg('UTF8_ERROR1').'<br><br>'.$strErrMsg;
			elseif (!$bUTF_conf && $bUTF_serv)
				$strErrMsg = getMsg('UTF8_ERROR2').'<br><br>'.$strErrMsg;
		}

		$arDName = getDumpList();
		$strDName = '';
		foreach($arDName as $db)
			$strDName .= '<option value="'.htmlspecialchars($db).'">'.htmlspecialchars($db).'</option>';

		if(count($arDName))
		{
			$ar = array(
				'TITLE' => getMsg("TITLE2", LANG),
				'TEXT' => 
					($strErrMsg ? '<div style="color:red">'.$strErrMsg.'</div>' : '').
					'<input type="hidden" name="arc_name" value="'.$arc_name.'">'.
					(count($arDName)>1
					?
					getMsg("DB_SELECT").' <select class="selectitem" name="dump_name">'.$strDName.'</select>'
					:
					'<input type=hidden name=dump_name value="'.htmlspecialchars($arDName[0]).'">'
					) .
					'<p align=center><b>'.getMsg("DB_SETTINGS", LANG).'</b></p>'.
					'<p><input type=radio name=db_settings value=default id=default onClick="s_display(0)" '.((!$_REQUEST['db_settings'] && defined('VMBITRIX') || $_REQUEST['db_settings']=='default')?'checked':'').'><label for=default>'.getMsg("DB_DEF").'</label></p>
					<p><input type=radio name=db_settings value=env id=env onClick="s_display(0)" '.($_REQUEST['db_settings']=='env'?'checked':'').'><label for=env>'.getMsg("DB_ENV").'</label></p>
					<p><input type=radio name=db_settings value=custom id=custom onClick="s_display(1)" '.($_REQUEST['db_settings']=='custom'?'checked':'').'><label for=custom>'.getMsg("DB_OTHER").'</label></p>
					
					<div style="border:1px solid #aeb8d7;padding:5px;'.($_REQUEST['db_settings']=='custom'?'':'display:none').'" id=settings>
					<table width=100% cellspacing=0 cellpadding=2 border=0>
					<tr><td class="tablebody1" align=right>'. getMsg("BASE_HOST", LANG).'</td><td><input type="text" class="selectitem" name="db_host" id="db_host_id" value="'.(strlen($_REQUEST["db_host"])>0 ? htmlspecialchars($_REQUEST['db_host']) : "localhost").'"></td></tr>
					<tr><td class="tablebody1" align=right>'. getMsg("USER_NAME", LANG).'</td><td><input type="text" class="selectitem" name="db_user" id="db_user_id" value="'.(strlen($_REQUEST["db_user"])>0 ? htmlspecialchars($_REQUEST["db_user"]) : "").'"></td></tr>
					<tr><td class="tablebody1" align=right>'. getMsg("USER_PASS", LANG).'</td><td><input type="password" class="selectitem" name="db_pass" id="db_pass_id" value="'.(strlen($_REQUEST["db_pass"])>0 ? htmlspecialchars($_REQUEST["db_pass"]) : "").'"></td></tr>
					<tr><td class="tablebody1" align=right>'. getMsg("BASE_NAME", LANG).'</td><td><input type="text" class="selectitem" name="db_name" id="db_name_id" value="'.(strlen($_REQUEST["db_name"])>0 ? htmlspecialchars($_REQUEST["db_name"]) : "").'"></td></tr>
					<tr><td class="tablebody1" align=right>'. getMsg("BASE_CREATE_DB", LANG).'</td><td><input type="checkbox" name="create_db" id="create_db_id" value="Y" '.($_REQUEST["create_db"]=="Y" ? "checked" : "").'></td></tr>
					</table>
					</div>'
				,
				'BOTTOM' => 
				'<input type="button" value="'.getMsg('BUT_TEXT_BACK').'" onClick="document.location=\'/restore.php?Step=1&lang='.LANG.'\'"> '.
				'<input type="button" class="selectitem" id="start_button" value="'.getMsg("BASE_RESTORE", LANG).'" onClick="reloadPage(3, \''. LANG.'\')">'
			);
			html($ar);
			?>
			<script>
				function s_display(val)
				{
					document.getElementById('settings').style.display = (val ? 'block' : 'none');
				}
			</script>
			<?
		}
		else
		{
			$text = 
			($strErrMsg ? '<div style="color:red">'.$strErrMsg.'</div>' : '').
			(file_exists($_SERVER['DOCUMENT_ROOT'].'/.htaccess.restore') ? '<div style="color:red">'.getMsg('HTACCESS_WARN').'</div>' : '') .
			getMsg("EXTRACT_FINISH_MSG", LANG) . '
			<input type="hidden" name="arc_name" value="'.$arc_name.'">
			<input type="hidden" name="dump_name" id="dump_name_id" value="'. $_REQUEST["dump_name"].'">';
			$bottom = '<input type="button" value="'.getMsg('BUT_TEXT_BACK').'" onClick="document.location=\'/restore.php?Step=1&lang='.LANG.'\'"> '.
			'<input type=button value="'.getMsg('DELETE_FILES').'" onClick="reloadPage(4)">';

			showMsg(getMsg("EXTRACT_FINISH_TITLE", LANG), $text, $bottom);
		}
	}
}
elseif($Step == 3)
{
	$d_pos = (double) $_REQUEST["d_pos"];
	if ($d_pos < 0)
		$d_pos = 0;

	if (!isset($_REQUEST['d_pos'])) // start
	{
		if(file_exists($dbconn))
		{
			include($dbconn);
			$arReplace = array(
				'DBHost' => 'db_host',
				'DBLogin' => 'db_user',
				'DBPassword' => 'db_pass',
				'DBName' => 'db_name'
			);

			$arFile = file($dbconn);
			$bCron = false;
			foreach($arFile as $line)
			{
				if (preg_match("#^[ \t]*".'\$'."(DB[a-zA-Z]+)#",$line,$regs))
				{
					$key = $regs[1];
					$new_val = $_REQUEST[$arReplace[$key]];
					if (isset($new_val) && $$key != $new_val)
					{
						$strFile.='#'.$line.
						'$'.$key.' = "'.addslashes($new_val).'";'."\n\n";
					}
					else
						$strFile.=$line;
				}
				else
					$strFile.=$line;

				if (preg_match('#BX_CRONTAB_SUPPORT#',$line)) // почта уже на кроне
					$bCron = true;
			}

			if (defined('VMBITRIX') && !$bCron)
				$strFile = '<'.'?define("BX_CRONTAB_SUPPORT", true);?'.'>'.$strFile;

			$f = fopen($dbconn,"wb");
			fputs($f,$strFile);
			fclose($f);
		}
		SetCurrentProgress(0);
		$r = true;
	}
	else
		$r = $oDB->restore(); 

	$bottom = '<input type="button" value="'.getMsg('BUT_TEXT_BACK').'" onClick="document.location=\'/restore.php?Step=1&lang='.LANG.'\'"> ';
	if($r && !$oDB->is_end())
	{
		$d_pos = $oDB->getPos();
		$oDB->close();
		$arc_name = $_REQUEST["arc_name"];
		SetCurrentProgress($d_pos,filesize($_SERVER['DOCUMENT_ROOT'].'/bitrix/backup/'.$_REQUEST['dump_name']));
		$text = 
		$status . '
		<input type="hidden" name="arc_name" value="'.$arc_name.'">
		<input type="hidden" name="dump_name" id="dump_name_id" value="'. $_REQUEST["dump_name"].'">
		<input type="hidden" name="d_pos" id="d_pos_id" value="'.$d_pos.'">
		<input type="hidden" name="db_user" id="db_user_id" value="'.$_REQUEST["db_user"].'">
		<input type="hidden" name="db_pass" id="db_pass_id" value="'. (strlen($_REQUEST["db_pass"]) > 0 ? htmlspecialchars($_REQUEST["db_pass"]) : "").'">
		<input type="hidden" name="db_name" id="db_name_id" value="'. $_REQUEST["db_name"].'">
		<input type="hidden" name="db_host" id="db_host_id" value="'. $_REQUEST["db_host"].'">
		';
		showMsg(getMsg('TITLE_PROCESS2'),$text,$bottom);
		?><script>reloadPage(3, '<?= LANG?>', 1);</script><?
	}
	else
	{
		if($oDB->getError() != "")
			showMsg(getMsg("ERR_DUMP_RESTORE", LANG), '<div style="color:red">'.$oDB->getError().'</div>', $bottom);
		else
		{
			$rs = $oDB->Query('SELECT DOC_ROOT FROM b_lang WHERE DOC_ROOT IS NOT NULL');

			$text = getMsg("FINISH_MSG", LANG) . 
			(mysql_fetch_array($rs) ? '<div style="color:red">'.getMsg('DOC_ROOT_WARN').'</div>' : '') .
			(file_exists($_SERVER['DOCUMENT_ROOT'].'/.htaccess.restore') ? '<div style="color:red">'.getMsg('HTACCESS_WARN').'</div>' : '') .
			'<input type="hidden" name="arc_name" value="'.$arc_name.'">
			<input type="hidden" name="dump_name" id="dump_name_id" value="'. $_REQUEST["dump_name"].'">';
			$bottom = '<input type=button value="'.getMsg('DELETE_FILES').'" onClick="reloadPage(4)">';
			showMsg(getMsg("FINISH", LANG), $text, $bottom);
		}
	}
}
elseif($Step == 4)
{
	if ($_REQUEST['dump_name'])
	{
		@unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/backup/".$_REQUEST["dump_name"]);
		@unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/backup/".str_replace('.sql','_after_connect.sql',$_REQUEST["dump_name"]));
	}
	@unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix8setup.php');
	$ok = unlink($_SERVER["DOCUMENT_ROOT"]."/restore.php");

	if($_REQUEST['arc_name'])
	{
		$ok = unlink($_SERVER["DOCUMENT_ROOT"]."/".$_REQUEST["arc_name"]) && $ok;
		$i = 0;
		while(file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$_REQUEST['arc_name'].'.'.++$i))
			$ok = unlink($_SERVER['DOCUMENT_ROOT'].'/'.$_REQUEST['arc_name'].'.'.$i) && $ok;
	}


	if (!$ok)
		showMsg(getMsg("FINISH_ERR_DELL_TITLE", LANG), getMsg("FINISH_ERR_DELL", LANG));
	else
	{
		showMsg(getMsg("FINISH", LANG), getMsg("FINISH_MSG", LANG));
		?><script>window.setTimeout(function(){document.location="/";},3000);</script><?
	}
}

#################### END ############




class CDBRestore
{
	var $type = "";
	var $DBHost ="";
	var $DBName = "";
	var $DBLogin = "";
	var $DBPassword = "";
	var $DBdump = "";
	var $db_Conn = "";
	var $db_Error = "";
	var $f_end = false;
	var $start;
	var $d_pos;
	var $_dFile;


	function Query($sql)
	{
		return mysql_query($sql, $this->db_Conn);
	}

	function CDBRestore($DBHost, $DBName, $DBLogin, $DBPassword, $DBdump, $d_pos)
	{
		$this->DBHost = $DBHost;
		$this->DBLogin = $DBLogin;
		$this->DBPassword = $DBPassword;
		$this->DBName = $DBName;
		$this->DBdump = $_SERVER["DOCUMENT_ROOT"]."/bitrix/backup/".$DBdump;
		$this->d_pos = $d_pos;
	}

	//Соединяется с базой данных
	function Connect()
	{

		$this->type="MYSQL";
		if (!defined("DBPersistent")) define("DBPersistent",false);
		if (DBPersistent)
		{
			$this->db_Conn = @mysql_pconnect($this->DBHost, $this->DBLogin, $this->DBPassword);
		}
		else
		{
			$this->db_Conn = @mysql_connect($this->DBHost, $this->DBLogin, $this->DBPassword);
		}
		if(!($this->db_Conn))
		{
			if (DBPersistent) $s = "mysql_pconnect"; else $s = "mysql_connect";
			if(($str_err = mysql_error()) != "")
				$this->db_Error .= "<br><font color=#ff0000>Error! ".$s."('-', '-', '-')</font><br>".$str_err."<br>";
			return false;
		}

		$after_file = str_replace('.sql','_after_connect.sql',$this->DBdump);
		if (file_exists($after_file))
		{
			$rs = fopen($after_file,'rb');
			$str = fread($rs,filesize($after_file));
			fclose($rs);
			$arSql = explode(';',$str);
			foreach($arSql as $sql)
				mysql_query($sql, $this->db_Conn);
		}


		if (@$_REQUEST["create_db"]=="Y")
		{
			if(!@mysql_query("CREATE DATABASE ".@$_REQUEST["db_name"], $this->db_Conn))
			{
				$this->db_Error = getMsg("ERR_CREATE_DB", LANG).': '.mysql_error();
				return false;
			}
		}

		if(!mysql_select_db($this->DBName, $this->db_Conn))
		{
			if(($str_err = mysql_error($this->db_Conn)) != "")
				$this->db_Error = "<br><font color=#ff0000>Error! mysql_select_db($this->DBName)</font><br>".$str_err."<br>";
			return false;
		}

		return true;
	}

	function readSql()
	{
		$cache ="";

		while(!feof($this->_dFile) && (substr($cache, (strlen($cache)-2), 1) != ";"))
			$cache .= fgets($this->_dFile);

		if(!feof($this->_dFile))
			return $cache;
		else
		{
			$this->f_end = true;
			return false;
		}
	}

	function restore()
	{
		$this->_dFile = @fopen($this->DBdump, 'r');

		if($this->d_pos > 0)
			@fseek($this->_dFile, $this->d_pos);

		$sql = "";

		while(($sql = $this->readSql()) && haveTime())
		{
			if (defined('VMBITRIX')) // избавимся от MyISAM
			{
				if (preg_match('#^CREATE TABLE#i',$sql))
				{
					$sql = preg_replace('#ENGINE=MyISAM#i','',$sql);
					$sql = preg_replace('#TYPE=MyISAM#i','',$sql);
				}
			}

			$result = @mysql_query($sql, $this->db_Conn);

			if(!$result)
			{
				$this->db_Error .= mysql_error().'<br><br>'.htmlspecialchars($sql);
				return false;
			}
			$sql = "";
		}

		if($sql != "")
		{
			$result = @mysql_query($sql, $this->db_Conn);

			if(!$result)
			{
				$this->db_Error .= mysql_error().'<br><br>'.htmlspecialchars($sql);
				return false;
			}
			$sql = "";
		}

		return true;
	}

	function getError()
	{
		return $this->db_Error;
	}

	function getPos()
	{
		if (is_resource($this->_dFile))
		{
			return @ftell($this->_dFile);
		}
	}

	function close()
	{
		unset($this->_dFile);
		return true;
	}

	function is_end()
	{
		return $this->f_end;
	}
}

function getDumpList()
{
	$arDump = array();
	$handle = @opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/backup");
	while (false !== ($file = @readdir($handle)))
	{
		if($file == "." || $file == "..")
			continue;

		if(is_dir($_SERVER["DOCUMENT_ROOT"]."/".$file))
			continue;

		if (strpos($file,'_after_connect.sql'))
			continue;

		if(substr($file, strlen($file) - 3, 3) == "sql")
			$arDump[] = $file;
	}

	return $arDump;
}

function getMsg($str_index, $str_lang='')
{
	global $mArr_ru, $mArr_en;
	if(LANG == "ru")
		return $mArr_ru[$str_index];
	else
		return $mArr_en[$str_index];
}

function getArcList()
{
	$arc = "";

	$handle = @opendir($_SERVER["DOCUMENT_ROOT"]);
	if (!$handle)
		return false;

	while (false !== ($file = @readdir($handle)))
	{
		if($file == "." || $file == "..")
			continue;

		if(is_dir($_SERVER["DOCUMENT_ROOT"]."/".$file))
			continue;

		if(substr($file, strlen($file) - 6, 6) == "tar.gz" || substr($file, strlen($file) - 3, 3) == "tar")
			$arc .= "<option value=\"$file\"> ".$file;
	}

	return $arc;
}

function showMsg($title, $msg, $bottom='')
{
	$ar = array(
		'TITLE' => $title,
		'TEXT' => $msg,
		'BOTTOM' => $bottom

	);
	html($ar);
}

function html($ar)
{
?>
	<html>
	<head>
	<title><?=$ar['TITLE']?></title>
	</head>
	<body style="background:#4A507B">
	<style>
		td {font-family:Verdana;font-size:9pt}
	</style>
	<form name="restore" id="restore" action="restore.php" enctype="multipart/form-data" method="POST">
	<input type="hidden" name="Step" id="Step_id" value="">
	<input type="hidden" name="lang" value="<?=LANG?>">
	<script language="JavaScript">
		function reloadPage(val, lang, delay)
		{
			document.getElementById('Step_id').value = val;
			document.getElementById('restore').action='restore.php?lang=<?=LANG?>';
			if (null!=delay)
				window.setTimeout("document.getElementById('restore').submit()",1000);
			else
				document.getElementById('restore').submit();
		}
	</script>
	<table width=100% height=100%><tr><td align=center valign=middle>
	<table align="center" cellspacing=0 cellpadding=0 border=0 style="width:601px;height:387px">
		<tr>
			<td width=11><div style="background:#FFF url(<?=img('corner_top_left.gif')?>);width:11px;height:57px"></td>
			<td height=57 bgcolor="#FFFFFF" valign="middle">
				<table cellpadding=0 cellspacing=0 border=0 width=100%><tr>
					<td align=left style="font-size:14pt;color:#E11537;padding-left:25px"><?=$ar['TITLE']?></td>
					<td align=right>
						<?
						$arLang = array();
						foreach(array('en') as $l)
							$arLang[] = LANG == $l ? "<span style='color:grey'>$l</span>" : "<a href='?lang=$l' style='color:black'>$l</a>";
#						echo implode(' | ',$arLang);
						?>
					</td>
				</tr></table>

			</td>
			<td width=11><div style="background:#FFF url(<?=img('corner_top_right.gif')?>);width:11px;height:57px"></td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF">&nbsp;</td>
			<td height=1 bgcolor="#FFFFFF"><hr size="1px" color="#D6D6D6"></td>
			<td bgcolor="#FFFFFF">&nbsp;</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF">&nbsp;</td>
			<td bgcolor="#FFFFFF" style="padding:10px;font-size:10pt" valign="<?=$ar['TEXT_ALIGN']?$ar['TEXT_ALIGN']:'top'?>"><?=$ar['TEXT']?></td>
			<td bgcolor="#FFFFFF">&nbsp;</td>
		</tr>
		<tr>
			<td bgcolor="#FFFFFF">&nbsp;</td>
			<td bgcolor="#FFFFFF" style="padding:20x;font-size:10pt" valign="middle" align="right" height="40px"><?=$ar['BOTTOM']?></td>
			<td bgcolor="#FFFFFF">&nbsp;</td>
		</tr>
		<tr>
			<td><div style="background:#FFF url(<?=img('corner_bottom_left.gif')?>);width:11;height:23"></td>
			<td height=23 bgcolor="#FFFFFF" background="<?=img('bottom_fill.gif')?>"></td>
			<td><div style="background:#FFF url(<?=img('corner_bottom_right.gif')?>);width:11;height:23"></td>
		</tr>
	</table>
	<div style="background:url(<?=img('logo_'.(LANG=='ru'?'':'en_').'installer.gif')?>); width:95; height:34">
	</td></tr></table>
	</form>
<?
}

function SetCurrentProgress($cur,$total=0,$red=true)
{
	global $status;
	if (!$total)
	{
		$total=100;
		$cur=0;
	}
	$val = intval($cur/$total*100);
	if ($val > 100)
		$val = 99;

	$status = '
	<div align=center style="padding:10px;font-size:18px">'.$val.'%</div>
	<table width=100% cellspacing=0 cellpadding=0 border=0 style="border:1px solid #D8D8D8">
	<tr>
		<td style="width:'.$val.'%;height:13px" bgcolor="'.($red?'#FF5647':'#54B4FF').'" background="'.img(($red?'red':'blue').'_progress.gif').'"></td>
		<td style="width:'.(100-$val).'%"></td>
	</tr>
	</table>';
}

function LoadFile($strRequestedUrl, $strFilename)
{
	global $proxyaddr, $proxyport, $strUserAgent, $strRequestedSize;

	$strRealUrl = $strRequestedUrl;
	$iStartSize = 0;
	$iRealSize = 0;

	$bCanContinueDownload = False;

	// ИНИЦИАЛИЗИРУЕМ, ЕСЛИ ДОКАЧКА
	$strRealUrl_tmp = "";
	$iRealSize_tmp = 0;
	if (file_exists($strFilename.".tmp") && file_exists($strFilename.".log") && filesize($strFilename.".log")>0)
	{
		$fh = fopen($strFilename.".log", "rb");
		$file_contents_tmp = fread($fh, filesize($strFilename.".log"));
		fclose($fh);

		list($strRealUrl_tmp, $iRealSize_tmp) = explode("\n", $file_contents_tmp);
		$strRealUrl_tmp = Trim($strRealUrl_tmp);
		$iRealSize_tmp = doubleval(Trim($iRealSize_tmp));
	}
	if ($iRealSize_tmp<=0 || strlen($strRealUrl_tmp)<=0)
	{
		$strRealUrl_tmp = "";
		$iRealSize_tmp = 0;

		if (file_exists($strFilename.".tmp"))
			@unlink($strFilename.".tmp");

		if (file_exists($strFilename.".log"))
			@unlink($strFilename.".log");
	}
	else
	{
		$strRealUrl = $strRealUrl_tmp;
		$iRealSize = $iRealSize_tmp;
		$iStartSize = filesize($strFilename.".tmp");
	}
	// КОНЕЦ: ИНИЦИАЛИЗИРУЕМ, ЕСЛИ ДОКАЧКА

	//SetCurrentStatus(LoaderGetMessage("LOADER_LOAD_QUERY_SERVER"));
	SetCurrentStatus(str_replace("#HOST#", $host, LoaderGetMessage("LOADER_LOAD_CONN2HOST")));

	// ИЩЕМ ФАЙЛ И ЗАПРАШИВАЕМ ИНФО
	do
	{
		SetCurrentStatus(str_replace("#DISTR#", $strRealUrl, LoaderGetMessage("LOADER_LOAD_QUERY_DISTR")));

		$lasturl = $strRealUrl;
		$redirection = "";

		$parsedurl = @parse_url($strRealUrl);
		$useproxy = (($proxyaddr != "") && ($proxyport != ""));

		if (!$useproxy)
		{
			$host = $parsedurl["host"];
			$port = $parsedurl["port"];
			$hostname = $host;
		}
		else
		{
			$host = $proxyaddr;
			$port = $proxyport;
			$hostname = $parsedurl["host"];
		}

		$port = $port ? $port : "80";

		$sockethandle = @fsockopen($host, $port, $error_id, $error_msg, 10);
		if (!$sockethandle)
		{
			SetCurrentStatus(str_replace("#HOST#", $host, LoaderGetMessage("LOADER_LOAD_NO_CONN2HOST"))." [".$error_id."] ".$error_msg);
			return false;
		}
		else
		{
			if (!$parsedurl["path"])
				$parsedurl["path"] = "/";

//			SetCurrentStatus(LoaderGetMessage("LOADER_LOAD_QUERY_FILE"));
			$request = "";
			if (!$useproxy)
			{
				$request .= "HEAD ".$parsedurl["path"].($parsedurl["query"] ? '?'.$parsedurl["query"] : '')." HTTP/1.0\r\n";
				$request .= "Host: $hostname\r\n";
			}
			else
			{
				$request .= "HEAD ".$strRealUrl." HTTP/1.0\r\n";
				$request .= "Host: $hostname\r\n";
			}

			if ($strUserAgent != "")
				$request .= "User-Agent: $strUserAgent\r\n";

			$request .= "\r\n";

			fwrite($sockethandle, $request);

			$result = "";
//			SetCurrentStatus(LoaderGetMessage("LOADER_LOAD_WAIT"));

			$replyheader = "";
			while (($result = fgets($sockethandle, 4096)) && $result!="\r\n")
			{
				$replyheader .= $result;
			}
			fclose($sockethandle);

			$ar_replyheader = explode("\r\n", $replyheader);

			$replyproto = "";
			$replyversion = "";
			$replycode = 0;
			$replymsg = "";
			if (preg_match("#([A-Z]{4})/([0-9.]{3}) ([0-9]{3})#", $ar_replyheader[0], $regs))
			{
				$replyproto = $regs[1];
				$replyversion = $regs[2];
				$replycode = IntVal($regs[3]);
				$replymsg = substr($ar_replyheader[0], strpos($ar_replyheader[0], $replycode) + strlen($replycode) + 1, strlen($ar_replyheader[0]) - strpos($ar_replyheader[0], $replycode) + 1);
			}

			if ($replycode!=200 && $replycode!=302)
			{
				if ($replycode==403)
					SetCurrentStatus(str_replace("#ANS#", $replycode." - ".$replymsg, LoaderGetMessage("LOADER_LOAD_SERVER_ANSWER1")));
				else
					SetCurrentStatus(str_replace("#ANS#", $replycode." - ".$replymsg, LoaderGetMessage("LOADER_LOAD_SERVER_ANSWER")));
				return false;
			}

			$strLocationUrl = "";
			$iNewRealSize = 0;
			$strAcceptRanges = "";
			for ($i = 1; $i < count($ar_replyheader); $i++)
			{
				if (strpos($ar_replyheader[$i], "Location") !== false)
					$strLocationUrl = trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1));
				elseif (strpos($ar_replyheader[$i], "Content-Length") !== false)
					$iNewRealSize = IntVal(Trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1)));
				elseif (strpos($ar_replyheader[$i], "Accept-Ranges") !== false)
					$strAcceptRanges = Trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1));
			}

			if (strlen($strLocationUrl)>0)
			{
				$redirection = $strLocationUrl;
				$redirected = true;
				if ((strpos($redirection, "http://")===false))
					$strRealUrl = dirname($lasturl)."/".$redirection;
				else
					$strRealUrl = $redirection;
			}

			if (strlen($strLocationUrl)<=0)
				break;
		}
	}
	while (true);
	// КОНЕЦ: ИЩЕМ ФАЙЛ И ЗАПРАШИВАЕМ ИНФО

	$bCanContinueDownload = ($strAcceptRanges == "bytes");

	// ЕСЛИ НЕЛЬЗЯ ДОКАЧИВАТЬ
	if (!$bCanContinueDownload
		|| ($iRealSize>0 && $iNewRealSize != $iRealSize))
	{
	//	SetCurrentStatus(LoaderGetMessage("LOADER_LOAD_NEED_RELOAD"));
	//	$iStartSize = 0;
		die(LoaderGetMessage("LOADER_LOAD_NEED_RELOAD"));
	}
	// КОНЕЦ: ЕСЛИ НЕЛЬЗЯ ДОКАЧИВАТЬ

	// ЕСЛИ МОЖНО ДОКАЧИВАТЬ
	if ($bCanContinueDownload)
	{
		$fh = fopen($strFilename.".log", "wb");
		if (!$fh)
		{
			SetCurrentStatus(str_replace("#FILE#", $strFilename.".log", LoaderGetMessage("LOADER_LOAD_NO_WRITE2FILE")));
			return false;
		}
		fwrite($fh, $strRealUrl."\n");
		fwrite($fh, $iNewRealSize."\n");
		fclose($fh);
	}
	// КОНЕЦ: ЕСЛИ МОЖНО ДОКАЧИВАТЬ

	SetCurrentStatus(str_replace("#DISTR#", $strRealUrl, LoaderGetMessage("LOADER_LOAD_LOAD_DISTR")));
	$strRequestedSize = $iNewRealSize;

	// КАЧАЕМ ФАЙЛ
	$parsedurl = parse_url($strRealUrl);
	$useproxy = (($proxyaddr != "") && ($proxyport != ""));

	if (!$useproxy)
	{
		$host = $parsedurl["host"];
		$port = $parsedurl["port"];
		$hostname = $host;
	}
	else
	{
		$host = $proxyaddr;
		$port = $proxyport;
		$hostname = $parsedurl["host"];
	}

	$port = $port ? $port : "80";

	SetCurrentStatus(str_replace("#HOST#", $host, LoaderGetMessage("LOADER_LOAD_CONN2HOST")));
	$sockethandle = @fsockopen($host, $port, $error_id, $error_msg, 10);
	if (!$sockethandle)
	{
		SetCurrentStatus(str_replace("#HOST#", $host, LoaderGetMessage("LOADER_LOAD_NO_CONN2HOST"))." [".$error_id."] ".$error_msg);
		return false;
	}
	else
	{
		if (!$parsedurl["path"])
			$parsedurl["path"] = "/";

		SetCurrentStatus(LoaderGetMessage("LOADER_LOAD_QUERY_FILE"));

		$request = "";
		if (!$useproxy)
		{
			$request .= "GET ".$parsedurl["path"].($parsedurl["query"] ? '?'.$parsedurl["query"] : '')." HTTP/1.0\r\n";
			$request .= "Host: $hostname\r\n";
		}
		else
		{
			$request .= "GET ".$strRealUrl." HTTP/1.0\r\n";
			$request .= "Host: $hostname\r\n";
		}

		if ($strUserAgent != "")
			$request .= "User-Agent: $strUserAgent\r\n";

		if ($bCanContinueDownload && $iStartSize>0)
			$request .= "Range: bytes=".$iStartSize."-\r\n";

		$request .= "\r\n";

		fwrite($sockethandle, $request);

		$result = "";
		SetCurrentStatus(LoaderGetMessage("LOADER_LOAD_WAIT"));

		$replyheader = "";
		while (($result = fgets($sockethandle, 4096)) && $result!="\r\n")
			$replyheader .= $result;

		$ar_replyheader = explode("\r\n", $replyheader);

		$replyproto = "";
		$replyversion = "";
		$replycode = 0;
		$replymsg = "";
		if (preg_match("#([A-Z]{4})/([0-9.]{3}) ([0-9]{3})#", $ar_replyheader[0], $regs))
		{
			$replyproto = $regs[1];
			$replyversion = $regs[2];
			$replycode = IntVal($regs[3]);
			$replymsg = substr($ar_replyheader[0], strpos($ar_replyheader[0], $replycode) + strlen($replycode) + 1, strlen($ar_replyheader[0]) - strpos($ar_replyheader[0], $replycode) + 1);
		}

		if ($replycode!=200 && $replycode!=302 && $replycode!=206)
		{
			SetCurrentStatus(str_replace("#ANS#", $replycode." - ".$replymsg, LoaderGetMessage("LOADER_LOAD_SERVER_ANSWER")));
			return false;
		}

		$strContentRange = "";
		$iContentLength = 0;
		$strAcceptRanges = "";
		for ($i = 1; $i < count($ar_replyheader); $i++)
		{
			if (strpos($ar_replyheader[$i], "Content-Range") !== false)
				$strContentRange = trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1));
			elseif (strpos($ar_replyheader[$i], "Content-Length") !== false)
				$iContentLength = doubleval(Trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1)));
			elseif (strpos($ar_replyheader[$i], "Accept-Ranges") !== false)
				$strAcceptRanges = Trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1));
		}

		$bReloadFile = True;
		if (strlen($strContentRange)>0)
		{
			if (preg_match("# *bytes +([0-9]*) *- *([0-9]*) */ *([0-9]*)#i", $strContentRange, $regs))
			{
				$iStartBytes_tmp = doubleval($regs[1]);
				$iEndBytes_tmp = doubleval($regs[2]);
				$iSizeBytes_tmp = doubleval($regs[3]);

				if ($iStartBytes_tmp==$iStartSize
					&& $iEndBytes_tmp==($iNewRealSize-1)
					&& $iSizeBytes_tmp==$iNewRealSize)
				{
					$bReloadFile = False;
				}
			}
		}

		if ($bReloadFile)
		{
			@unlink($strFilename.".tmp");
			$iStartSize = 0;
		}

		if (($iContentLength+$iStartSize)!=$iNewRealSize)
		{
			SetCurrentStatus(LoaderGetMessage("LOADER_LOAD_ERR_SIZE"));
			return false;
		}

		$fh = fopen($strFilename.".tmp", "ab");
		if (!$fh)
		{
			SetCurrentStatus(str_replace("#FILE#", $strFilename.".tmp", LoaderGetMessage("LOADER_LOAD_CANT_OPEN_WRITE")));
			return false;
		}

		$bFinished = True;
		$downloadsize = (double) $iStartSize;
		SetCurrentStatus(LoaderGetMessage("LOADER_LOAD_LOADING"));
		while (!feof($sockethandle))
		{
			if (!haveTime())
			{
				$bFinished = False;
				break;
			}

			$result = fread($sockethandle, 40960);
			$downloadsize += strlen($result);
			if ($result=="")
				break;

			fwrite($fh, $result);
		}
		SetCurrentProgress($downloadsize,$iNewRealSize);

		fclose($fh);
		fclose($sockethandle);

		if ($bFinished)
		{
			@unlink($strFilename);
			if (!@rename($strFilename.".tmp", $strFilename))
			{
				SetCurrentStatus(str_replace("#FILE2#", $strFilename, str_replace("#FILE1#", $strFilename.".tmp", LoaderGetMessage("LOADER_LOAD_ERR_RENAME"))));
				return false;
			}
		}
		else
			return 2;

		SetCurrentStatus(str_replace("#SIZE#", $downloadsize, str_replace("#FILE#", $strFilename, LoaderGetMessage("LOADER_LOAD_FILE_SAVED"))));
		@unlink($strFilename.".log");
		return 1;
	}
	// КОНЕЦ: КАЧАЕМ ФАЙЛ
}

function SetCurrentStatus($str)
{
	global $strLog;
	$strLog .= $str."\n";
}

function LoaderGetMessage($name)
{
	global $MESS;
	return $MESS[$name];
}

class CTar
{
	var $gzip;
	var $file;
	var $err = array();
	var $res;
	var $Block = 0;
	var $BlockHeader;
	var $path;
	var $FileCount = 0;
	var $DirCount = 0;
	var $ReadBlockMax = 2000;
	var $ReadBlockCurrent = 0;
	var $header = null;
	var $ArchiveSizeMax;
	var $BX_EXTRA = 'BX0000';

	##############
	# READ
	# {
	function openRead($file)
	{
		if (!isset($this->gzip) && (substr($file,-3)=='.gz' || substr($file,-4)=='.tgz'))
			$this->gzip = true;

		return $this->open($file, 'r');
	}

	function readBlock()
	{
		$str = $this->gzip ? gzread($this->res,512) : fread($this->res,512);
		if (!$str && $this->openNext())
			$str = $this->gzip ? gzread($this->res,512) : fread($this->res,512);

		if ($str)
			$this->Block++;

		return $str;
	}

	function SkipFile()
	{
		$this->Skip(ceil($this->header['size']/512));
		$this->header = null;
	}

	function Skip($Block = 0)
	{
		if (!$Block)
			return false;
		$pos = $this->gzip ? gztell($this->res) : ftell($this->res);
		if (file_exists($this->getNextName()))
		{
			while(($BlockLeft = ($this->getArchiveSize($this->file) - $pos)/512) < $Block)
			{
				if ($BlockLeft != floor($BlockLeft))
					return false; // invalid file size
				$this->Block += $BlockLeft;
				$Block -= $BlockLeft;
				if (!$this->openNext())
					return false;
				$pos = 0;
			}
		}

		$this->Block += $Block;
		return 0 === ($this->gzip ? gzseek($this->res,$pos + $Block*512) : fseek($this->res,$pos + $Block*512));
	}

	function readHeader($Long = false)
	{
		$str = '';
		while(trim($str) == '')
			if (!strlen($str = $this->readBlock()))
				return 0; // finish
		if (!$Long)
			$this->BlockHeader = $this->Block - 1;

		if (strlen($str)!=512)
			return $this->Error('TAR_WRONG_BLOCK_SIZE',$this->Block.' ('.strlen($str).')');


		$data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix", $str);
		$chk = $data['devmajor'].$data['devminor'];

		if (!is_numeric(trim($data['checksum'])) || $chk!='' && $chk!=0)
			return $this->Error('TAR_ERR_FORMAT',($this->Block-1).'<hr>Header: <br>'.htmlspecialchars($str)); // быстрая проверка

		$header['filename'] = trim($data['prefix'].'/'.$data['filename'],'/');
		$header['mode'] = OctDec($data['mode']);
		$header['uid'] = OctDec($data['uid']);
		$header['gid'] = OctDec($data['gid']);
		$header['size'] = OctDec($data['size']);
		$header['mtime'] = OctDec($data['mtime']);
		$header['type'] = $data['type'];
//		$header['link'] = $data['link'];

		if (strpos($header['filename'],'./')===0)
			$header['filename'] = substr($header['filename'],2);

		if ($header['type']=='L') // Long header
		{
			$n = ceil($header['size']/512);
			for ($i = 0; $i < $n; $i++)
				$filename .= $this->readBlock();

			$header = $this->readHeader($Long = true);
			$header['filename'] = substr($filename,0,strpos($filename,chr(0)));
		}
		
		if (substr($header['filename'],-1)=='/') // trailing slash
			$header['type'] = 5; // Directory

		if ($header['type']=='5')
			$header['size'] = '';

		if ($header['filename']=='')
			return $this->Error('TAR_EMPTY_FILE',($this->Block-1));

		if (!$this->checkCRC($str, $data))
			return $this->Error('TAR_ERR_CRC',htmlspecialchars($header['filename']));

		$this->header = $header;

		return $header;
	}

	function checkCRC($str, $data)
	{
		$checksum = $this->checksum($str);
		$res = octdec($data['checksum']) == $checksum || $data['checksum']===0 && $checksum==256;
#		if (!$res)
#			var_dump(octdec($data['checksum']) .'=='. $checksum);
		return $res;
	}

	function extractFile()
	{
		if ($this->header === null)
		{
			if(($header = $this->readHeader()) === false || $header === 0 || $header === true)
			{
				if ($header === true)
					$this->SkipFile();
				return $header;
			}

			$this->lastPath = $f = $this->path.'/'.$header['filename'];
		
			if ($this->ReadBlockCurrent == 0)
			{
				if ($header['type']==5) // dir
				{
					if(!file_exists($f) && !self::xmkdir($f))
						return $this->Error('TAR_ERR_FOLDER_CREATE',htmlspecialchars($f));
					//chmod($f, $header['mode']);
				}
				else // file
				{
					if (!self::xmkdir($dirname = dirname($f)))
						return $this->Error('TAR_ERR_FOLDER_CREATE'.htmlspecialchars($dirname));
					elseif (($rs = fopen($f, 'wb'))===false)
						return $this->Error('TAR_ERR_FILE_CREATE',htmlspecialchars($f));
				}
			}
			else
				$this->Skip($this->ReadBlockCurrent);
		}
		else // файл уже частично распакован, продолжаем на том же хите
		{
			$header = $this->header;
			$this->lastPath = $f = $this->path.'/'.$header['filename'];
		}

		if ($header['type'] != 5) // пишем контент в файл 
		{
			if (!$rs)
			{
				if (($rs = fopen($f, 'ab'))===false)
					return $this->Error('TAR_ERR_FILE_OPEN',htmlspecialchars($f));
			}

			$i = 0;
			$FileBlockCount = ceil($header['size'] / 512);
			while(++$this->ReadBlockCurrent <= $FileBlockCount && ($contents = $this->readBlock()))
			{
				if ($this->ReadBlockCurrent == $FileBlockCount && ($chunk = $header['size'] % 512))
					$contents = substr($contents, 0, $chunk);

				fwrite($rs,$contents);

				if ($this->ReadBlockMax && ++$i >= $this->ReadBlockMax)
				{
					fclose($rs);
					return true; // Break
				}
			}
			fclose($rs);

			//chmod($f, $header['mode']);
			if (($s=filesize($f)) != $header['size'])
				return $this->Error('TAR_ERR_FILE_SIZE',htmlspecialchars($header['filename']).' (real: '.$s.',  expected: '.$header['size'].')');
		}

		if ($this->header['type']==5)
			$this->DirCount++;
		else
			$this->FileCount++;

		$this->debug_header = $this->header;
		$this->BlockHeader = $this->Block;
		$this->ReadBlockCurrent = 0;
		$this->header = null;

		return true;
	}

	function extract()
	{
		while ($r = $this->extractFile());
		return $r === 0;
	}

	function openNext()
	{
		if (file_exists($file = $this->getNextName()))
		{
			$this->close();
			return $this->open($file,$this->mode);
		}
		else
			return false;
	}

	# }
	##############

	##############
	# WRITE 
	# {
	function openWrite($file)
	{
		if (!isset($this->gzip) && (substr($file,-3)=='.gz' || substr($file,-4)=='.tgz'))
			$this->gzip = true;

		if ($this->ArchiveSizeMax > 0)
		{
			while(file_exists($file1 = $this->getNextName($file)))
				$file = $file1;

			$size = 0;
			if (($size = $this->getArchiveSize($file)) >= $this->ArchiveSizeMax)
			{
				$file = $file1;
				$size = 0;
			}
			$this->ArchiveSizeCurrent = $size;
		}
		return $this->open($file, 'a');
	}

	// создадим пустой gzip с экстра полем
	function createEmptyGzipExtra($file)
	{
		if (file_exists($file))
			return false;

		if (!($f = gzopen($file,'wb')))
			return false;
		gzwrite($f,'');
		gzclose($f);

		$data = file_get_contents($file);

		if (!($f = fopen($file, 'w')))
			return false;

		$ar = unpack('A3bin0/A1FLG/A6bin1',substr($data,0,10));
		if ($ar['FLG'] != 0)
			return $this->Error('Error writing extra field: already exists');

		$EXTRA = chr(0).chr(0).chr(strlen($this->BX_EXTRA)).chr(0).$this->BX_EXTRA;
		fwrite($f,$ar['bin0'].chr(4).$ar['bin1'].chr(strlen($EXTRA)).chr(0).$EXTRA.substr($data,10));
		fclose($f);
		return true;
	}

	function writeBlock($str)
	{
		$l = strlen($str);
		if ($l!=512)
			return $this->Error('TAR_WRONG_BLOCK_SIZE'.$l);

		if ($this->ArchiveSizeMax && $this->ArchiveSizeCurrent >= $this->ArchiveSizeMax)
		{
			$file = $this->getNextName();
			$this->close();

			if (!$this->open($file,$this->mode))
				return false;

			$this->ArchiveSizeCurrent = 0;
		}

		if ($res = $this->gzip ? gzwrite($this->res, $str) : fwrite($this->res,$str))
		{
			$this->Block++;
			$this->ArchiveSizeCurrent+=512;
		}

		return $res;
	}

	function writeHeader($ar)
	{
		$header0 = pack("a100a8a8a8a12a12", $ar['filename'], decoct($ar['mode']), decoct($ar['uid']), decoct($ar['gid']), decoct($ar['size']), decoct($ar['mtime']));
		$header1 = pack("a1a100a6a2a32a32a8a8a155", $ar['type'],'','','','','','', '', $ar['prefix']);

		$checksum = pack("a8",decoct($this->checksum($header0.'        '.$header1)));
		$header = pack("a512", $header0.$checksum.$header1);
		return $this->writeBlock($header) || $this->Error('TAR_ERR_WRITE_HEADER');
	}

	function addFile($f)
	{
		$f = str_replace('\\', '/', $f);
		$path = substr($f,strlen($this->path) + 1);
		if ($path == '')
			return true;
		if (strlen($path)>512)
			return $this->Error('TAR_PATH_TOO_LONG',htmlspecialchars($path));

		$ar = array();

		if (is_dir($f))
		{
			$ar['type'] = 5;
			$path .= '/';
		}
		else
			$ar['type'] = 0;

		$info = stat($f);
		if ($info)
		{
			if ($this->ReadBlockCurrent == 0) // read from start
			{
				$ar['mode'] = 0777 & $info['mode'];
				$ar['uid'] = $info['uid'];
				$ar['gid'] = $info['gid'];
				$ar['size'] = $ar['type']==5 ? 0 : $info['size'];
				$ar['mtime'] = $info['mtime'];


				if (strlen($path)>100) // Long header
				{
					$ar0 = $ar;
					$ar0['type'] = 'L';
					$ar0['filename'] = '././@LongLink';
					$ar0['size'] = strlen($path);
					if (!$this->writeHeader($ar0))
						return false;
					$path .= str_repeat(chr(0),512 - strlen($path));

					if (!$this->writeBlock($path))
						return false;
					$ar['filename'] = substr($path,0,100);
				}
				else
					$ar['filename'] = $path;

				if (!$this->writeHeader($ar))
					return false;
			}

			if ($ar['type']==0 && $info['size']>0) // File
			{
				if (!($rs = fopen($f, 'rb')))
					return $this->Error('TAR_ERR_FILE_READ',htmlspecialchars($f));

				if ($this->ReadBlockCurrent)
					fseek($rs, $this->ReadBlockCurrent * 512);

				$i = 0;
				while(!feof($rs) && $str = fread($rs,512))
				{
					$this->ReadBlockCurrent++;
					if (feof($rs) && ($l = strlen($str)) && $l < 512)
						$str .= str_repeat(chr(0),512 - $l);

					if (!$this->writeBlock($str))
					{
						fclose($rs);
						return $this->Error('TAR_ERR_FILE_WRITE',htmlspecialchars($f));
					}

					if ($this->ReadBlockMax && ++$i >= $this->ReadBlockMax)
					{
						fclose($rs);
						return true;
					}
				}
				fclose($rs);
				$this->ReadBlockCurrent = 0;
			}
			return true;
		}
		else
			return $this->Error('TAR_ERR_FILE_NO_ACCESS',htmlspecialchars($f));
	}

	# }
	##############

	##############
	# BASE 
	# {
	function open($file, $mode='r')
	{
		$this->file = $file;
		$this->mode = $mode;

		if ($this->gzip) 
		{
			if(!function_exists('gzopen'))
				return $this->Error('TAR_NO_GZIP');
			else
			{
				if ($mode == 'a' && !file_exists($file) && !$this->createEmptyGzipExtra($file))
					return false;
				$this->res = gzopen($file,$mode."b");
			}
		}
		else
			$this->res = fopen($file,$mode."b");

		return $this->res;
	}

	function close()
	{
		if ($this->gzip)
		{
			gzclose($this->res);

			// добавим фактический размер всех несжатых данных в extra поле
			if ($this->mode == 'a')
			{
				$f = fopen($this->file, 'rb+');
#				fseek($f, -4, SEEK_END);
				fseek($f, 18);
				fwrite($f, pack("V", $this->ArchiveSizeCurrent));
				fclose($f);
			}
		}
		else
			fclose($this->res);
	}

	function getNextName($file = '')
	{
		if (!$file)
			$file = $this->file;
		static $CACHE;
		$c = &$CACHE[$file];

		if (!$c)
		{
			$l = strrpos($file, '.');
			$num = substr($file,$l+1);
			if (is_numeric($num))
				$file = substr($file,0,$l+1).++$num;
			else
				$file .= '.1';
			$c = $file;
		}
		return $c;
	}

	function checksum($str)
	{
		static $CACHE;
		$checksum = &$CACHE[md5($str)];
		if (!$checksum)
		{
//			$str = pack("a512",$str);
			for ($i = 0; $i < 512; $i++)
				if ($i>=148 && $i<156)
					$checksum += 32; // ord(' ')
				else
					$checksum += ord($str[$i]);
		}
		return $checksum;
	}

	function getArchiveSize($file = '')
	{
		if (!$file)
			$file = $this->file;
		static $CACHE;
		$size = &$CACHE[$file];

		if (!$size)
		{
			if (!file_exists($file))
				$size = 0;
			else
			{
				if ($this->gzip)
				{
					$f = fopen($file, "rb");
		#			fseek($f, -4, SEEK_END);
					fseek($f, 16);
					if (fread($f, 2) == 'BX')
					{
						$size = end(unpack("V", fread($f, 4)));
						fclose($f);
					}
					else
					{
						fclose($f);
						$size = filesize($file) * 3; // fake
					}
				}
				else
					$size = filesize($file);
			}
		}
		return $size;
	}

	function Error($err_code, $str = '')
	{
//		echo '<pre>';debug_print_backtrace();echo '</pre>';
//		echo '<pre>';print_r($this);echo '</pre>';

		if (is_array($this->debug_header))
			$str .= '<hr>Последний успешный файл: <br><pre>'.(htmlspecialchars(print_r($this->debug_header,1))).'</pre>';
		$this->err[] = self::getMsg($err_code).' '.$str;
		return false;
	}

	function xmkdir($dir)
	{
		if (!file_exists($dir))
		{
			$upper_dir = dirname($dir);
			if (!file_exists($upper_dir) && !self::xmkdir($upper_dir))
				return false;

			return mkdir($dir);
		}

		return is_dir($dir);
	}

	function getMsg($code)
	{
		if (function_exists('getMsg'))
			return getMsg($code);
		else
			return $code;
	}

	function __construct()
	{
//		$this->ArchiveSizeMax = 2000 * 1024 * 1024;
		$this->ArchiveSizeMax = 20 * 1024 * 1024;
	}

	# }
	##############
}

class CTarRestore extends CTar
{
	function readHeader($Long = false)
	{
		$header = parent::readHeader($Long);
		if (is_array($header))
		{
			$dr = str_replace(array('/','\\'),'',$_SERVER['DOCUMENT_ROOT']);
			$f = str_replace(array('/','\\'),'',$this->path.'/'.$header['filename']);

			if ($f == $dr.'restore.php')
				return true;
			elseif ($f == $dr.'.htaccess')
				$header['filename'] .= '.restore';
			elseif ($f == $dr.'bitrix.config.php')
				return file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/.config.php') ? true : $this->Error('NOT_SAAS_ENV');
			elseif ($this->Block == 1 && file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/.config.php')) 
				return $this->Error('NOT_SAAS_DISTR');
		}
		return $header;
	}
}

function haveTime()
{
	return microtime(true) - START_EXEC_TIME < STEP_TIME;
}

function img($name)
{
	if (file_exists($_SERVER['DOCUMENT_ROOT'].'/images/'.$name))
		return '/images/'.$name;
	return 'http://www.1c-bitrix.ru/images/bitrix_setup/'.$name;
}

function bx_accelerator_reset()
{
        if(function_exists("accelerator_reset"))
                accelerator_reset();
        elseif(function_exists("wincache_refresh_if_changed"))
                wincache_refresh_if_changed();
}
?>
