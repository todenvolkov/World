<?
IncludeModuleLangFile(__FILE__);
/*
Here is testing code
$s=<<<EOT

Place tested html here

EOT;
cmodule::includemodule('security');
$Antivirus = new CSecurityAntiVirus("pre");
$Antivirus->replace=1;
echo htmlspecialchars($Antivirus->Analyze($s)),'<hr><pre>',htmlspecialchars(print_r($Antivirus,1));
*/
class CSecurityAntiVirus
{
	var $place = "";
	var $stylewithiframe = false;

	// this properties may be changed after object creation
	var $maxrating = 10; //рейтинг принятия решений..
	var $useglobalrules = 1; //использовать глобальные правила

	var $replace = 1;//
	var $replacement = "<!-- deleted by bitrix WAF -->"; //на что заменяем, если заменяем..

	//результаты
	var $resultrules; //массив сработавших правил

	//вспомогательные свойства
	var $data = ''; //полный код блока, включая ограничивающие теги
	var $type = ''; //тип блока
	var $body = ''; // тело блока.
	var $bodylines = false; // массив строк из body

	var $atributes = ''; // дополнительные атрибуты (вместе с src)

	var $cnt = 0; //счетчик обработанных блоков

	var $prev = '';
	var $next = '';

	function __construct($place = "body")
	{
		return $this->CSecurityAntiVirus($place);
	}

	function CSecurityAntiVirus($place = "body")
	{
		$this->place = $place;
		global $BX_SECURITY_AV_ACTION;
		if($BX_SECURITY_AV_ACTION === "notify_only")
			$this->replace = false;
	}

	function IsActive()
	{
		$bActive = false;
		$rsEvents = GetModuleEvents("main", "OnPageStart");
		while($arEvent = $rsEvents->Fetch())
		{
			if(
				$arEvent["TO_MODULE_ID"] == "security"
				&& $arEvent["TO_CLASS"] == "CSecurityAntiVirus"
			)
			{
				$bActive = true;
				break;
			}
		}
		return $bActive;
	}

	function SetActive($bActive = false)
	{
		if($bActive)
		{
			if(!CSecurityAntiVirus::IsActive())
			{
				//Just pre compression
				RegisterModuleDependences("main", "OnPageStart", "security", "CSecurityAntiVirus", "OnPageStart", -1);
				RegisterModuleDependences("main", "OnEndBufferContent", "security", "CSecurityAntiVirus", "OnEndBufferContent", 10000);
				//Right after compression
				RegisterModuleDependences("main", "OnAfterEpilog", "security", "CSecurityAntiVirus", "OnAfterEpilog", 10001);
			}
		}
		else
		{
			if(CSecurityAntiVirus::IsActive())
			{
				UnRegisterModuleDependences("main", "OnPageStart", "security", "CSecurityAntiVirus", "OnPageStart");
				UnRegisterModuleDependences("main", "OnEndBufferContent", "security", "CSecurityAntiVirus", "OnEndBufferContent");
				UnRegisterModuleDependences("main", "OnAfterEpilog", "security", "CSecurityAntiVirus", "OnAfterEpilog");
			}
		}
	}

	function GetAuditTypes()
	{
		return array(
			"SECURITY_VIRUS" => "[SECURITY_VIRUS] ".GetMessage("SECURITY_VIRUS"),
		);
	}

	function OnPageStart()
	{
		global $APPLICATION, $DB, $BX_SECURITY_AV_TIMEOUT, $BX_SECURITY_AV_ACTION;
		$BX_SECURITY_AV_TIMEOUT = COption::GetOptionInt("security", "antivirus_timeout");
		$BX_SECURITY_AV_ACTION = COption::GetOptionInt("security", "antivirus_action");

		//пользовательский белый список
		global $BX_SECURITY_AV_WHITE_LIST, $CACHE_MANAGER;
		if($CACHE_MANAGER->Read(36000, "b_sec_white_list"))
		{
			$BX_SECURITY_AV_WHITE_LIST = $CACHE_MANAGER->Get("b_sec_white_list");
		}
		else
		{
			$BX_SECURITY_AV_WHITE_LIST = array();
			$res = CSecurityAntiVirus::GetWhiteList();
			while($ar = $res->Fetch())
				$BX_SECURITY_AV_WHITE_LIST[] = $ar["WHITE_SUBSTR"];
			$CACHE_MANAGER->Set("b_sec_white_list", $BX_SECURITY_AV_WHITE_LIST);
		}

		//Init DB in order to be able to register the event in the shutdown function
		CSecurityDB::Init();

		//Check if we started output buffering in auto_prepend_file
		//so we'll have chances to detect virus before prolog
		if(defined("BX_SECURITY_AV_STARTED"))
		{
			$content = ob_get_contents();
			ob_end_clean();
			if(strlen($content))
			{
				$Antivirus = new CSecurityAntiVirus("pre");
				echo $Antivirus->Analyze($content);
			}
		}

		//инициируем наблюдение за выводом, который может быть подключен после отработки антивируса.
		register_shutdown_function(array('CSecurityAntiVirus', 'PHPShutdown'));

		//Check notification from previous hit
		$fname = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_cache/b_sec_virus";
		if(file_exists($fname))
		{
			$rsInfo = $DB->Query("select * from b_sec_virus where SENT='N'");
			if($arInfo = $rsInfo->Fetch())
			{
				if($table_lock = CSecurityDB::LockTable('b_sec_virus', $APPLICATION->GetServerUniqID()."_virus"))
				{
					$SITE_ID = false;
					do {
						$SITE_ID = $arInfo["SITE_ID"];
						if(strlen($arInfo["INFO"]))
						{
							$arEvent = unserialize(base64_decode($arInfo["INFO"]));
							if(is_array($arEvent))
								$DB->Add("b_event_log", $arEvent, array("DESCRIPTION"));
						}
						CSecurityDB::Query("update b_sec_virus set SENT='Y' where ID='".$arInfo["ID"]."'", '');
					} while ($arInfo = $rsInfo->Fetch());

					$arDate = localtime(time());
					$date = mktime($arDate[2], $arDate[1]-$BX_SECURITY_AV_TIMEOUT, 0, $arDate[4]+1, $arDate[3], 1900+$arDate[5]);
					CSecurityDB::Query("DELETE FROM b_sec_virus WHERE TIMESTAMP_X <= ".$DB->CharToDateFunction(ConvertTimeStamp($date, "FULL")), '');
					CEvent::Send("VIRUS_DETECTED", $SITE_ID? $SITE_ID: SITE_ID, array("EMAIL" => COption::GetOptionString("main", "email_from", "")));

					CSecurityDB::UnlockTable($table_lock);

					unlink($fname);
				}
			}
		}
	}

	function OnEndBufferContent(&$content)
	{
		//Обработка основного вывода
		$Antivirus = new CSecurityAntiVirus("body");
		$content = $Antivirus->Analyze($content);
	}

	function OnAfterEpilog()
	{
		//начинаем наблюдение за выводом, который может быть подключен после отработки антивируса.
		ob_start();
		define("BX_SECURITY_AV_AFTER_EPILOG", true);
	}

	function PHPShutdown()
	{
		if(defined("BX_SECURITY_AV_AFTER_EPILOG"))
		{
			$content = ob_get_contents();
			if(strlen($content))
			{
				ob_end_clean();

				if(substr($content, 0, 6) == "<html>" && preg_match("#</html>\\s*\$#is", $content))
					$Antivirus = new CSecurityAntiVirus("body");
				else
					$Antivirus = new CSecurityAntiVirus("post");

				echo $Antivirus->Analyze($content);
			}
		}
	}

	function GetWhiteList()
	{
		global $DB;
		$res = $DB->Query("SELECT * FROM b_sec_white_list ORDER BY ID", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		return $res;
	}

	function UpdateWhiteList($arWhiteList)
	{
		global $DB, $CACHE_MANAGER;
		$res = $DB->Query("DELETE FROM b_sec_white_list", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$i = 1;
		foreach($arWhiteList as $white_str)
		{
			$white_str = trim($white_str);
			if($white_str)
				$DB->Add("b_sec_white_list", array("ID" => $i++, "WHITE_SUBSTR" => $white_str));
		}
		$CACHE_MANAGER->Clean("b_sec_white_list");
	}

	// function returns 1, if current block is in white list and needs not processing.
	function isinwhitelist()
	{
		if(strpos($this->atributes, 'src="/bitrix/') !== false)
			return 1;

		if(strpos($this->body, 'BX_DEBUG_INFO') !== false)
			return 1;

		if(strpos($this->body, 'BX.CDebugDialog(') !== false)
			return 1;

		if(strpos($this->body, 'SWFObject(') !== false)
			return 1;

		if(strpos($this->body, 'name = eval("\'PROP[\'') !== false)
			return 1;

		if(strpos($this->body, 'google-analytics.com/ga.js') !== false)
			return 1;

		if(strpos($this->body, 'openstat.net/cnt.js') !== false)
			return 1;

		if(strpos($this->body, 'autocontext.begun.ru/autocontext.js') !== false)
			return 1;

		if(strpos($this->body, 'itm_name') !== false)
			return 1;

		if(strpos($this->body, 'form_tbl_dump') !== false)
			return 1;

		if(preg_match('/var\s+(cmt|jsMnu_toolbar_|hint)/', $this->body))
			return 1;

		if(preg_match('/(arFDDirs|arFDFiles|arPropFieldsList)\[/', $this->body))
			return 1;

		if(preg_match('/(MoveProgress|Import|DoNext|JCMenu|AttachFile|CloseDialog|_processData|showComment)\(/', $this->body))
			return 1;

		if(strpos($this->body, 'bx_template_params') !== false)
			return 1;

		if(strpos($this->body, '.ShowWarnings(') !== false)
			return 1;

		if(strpos($this->body, 'window.operation_success = true;') !== false)
			return 1;

		if(preg_match('/(jsAjaxUtil|jsUtils|jsPopup|elOnline|jsAdminChain|jsEvent|jsAjaxHistory|bxSession)\./', $this->body))
			return 1;

		if(preg_match('/new\s+(PopupMenu|JCAdminFilter|JCAdminMenu|BXHint|ViewTabControl|BXHTMLEditor|JCTitleSearch|JCWDTitleSearch|BxInterfaceForm)/', $this->body))
			return 1;

		if(strpos($this->body, 'document\.write(\'<link href="/bitrix/templates/') !== false)
			return 1;

		if(preg_match('/(BX|document\.getElementById)\(\'session_time_result\'\).innerHTML/', $this->body))
			return 1;

		if(preg_match('/(structRegisterDD|bx_adv_includeFlash|BXSnippetsTaskbar|BXPropertiesTaskbar)/', $this->body))
			return 1;

		if(preg_match('/(iblock_element_edit|iblock_element_search|posting_admin|fileman_file_view|sale_print|get_message|user_edit)\.php/', $this->body))
			return 1;

		if(preg_match('/BX\.(WindowManager|reload|message|browser|ready|tooltip|admin|hint_replace)/', $this->body))
			return 1;

		if(preg_match('/window\.parent\.(InitActionProps|Tree)/', $this->body))
			return 1;

		if(preg_match('/top\.(jsBXAC|bx_req_res)/', $this->body))
			return 1;

		if(preg_match('/var\s+dates\s+=\s+new\s+Array/', $this->body))
			return 1;

		if(preg_match('/var\s+(updateURL|bx_incl_area|basketTotalWeight)\s+=/', $this->body))
			return 1;

		if(preg_match('/^\s*__status\s+=\s+true;\s*$/', $this->body))
			return 1;

		if(preg_match('/^[ \n\r\t]*window\.location[ \n\r\t]*=[ \n\r\t]*([\'"]).*?\1[ \n\r\t;]/m', $this->body))
			return 1;


		if(preg_match('/window\.(bx_req_res|MLSearchResult|arUsedCSS|arComp2Templates|arComp2TemplateProps|arComp2TemplateLists|arComp1Elements|arSnippets|JCCalendarViewMonth|JCCalendarViewWeek|JCCalendarViewDay|_bx_result|_bx_new_event|_bx_plann_mr|_bx_ar_events|_bx_calendar|_bx_plann_events|_bx_existent_event|_ml_items_colls|MLCollections|fmsBtimeout|fmsResult|arSnGroups|BXFM_result|BXFM_NoCopyToDir|oPhotoEditAlbumDialogError|structOptions|__bxst_result)/', $this->body))
			return 1;

		if(preg_match('/^\s*alert\s*\(\s*\'[^\']*\'\s*\)\s*;*\s*$/', $this->body))
			return 1;

		if(preg_match('/\s*(self|window)\.close\s*\(\s*\)\s*;*\s*$/', $this->body))
			return 1;

		if($this->body === 'window.location.reload();')
			return 1;

		if($this->body === 'window.location = window.location.href;')
			return 1;

		if(preg_match('/^parent\.window\.(End\(\d+\)|EndTasks\(\));\s*$/', $this->body))
			return 1;

		if(preg_match('/parent\.window\.|Start\(\s*\d+,\s*\d+\s*\);\s*$/', $this->body))
			return 1;

		if(preg_match('/^top\.location\.href\s*=\s*([\'"])[^\'"]*\1;{0,1}$/', $this->body))
			return 1;

		if(preg_match('/\.setTimeout\(\'CheckNew\(\)\'/', $this->body))
			return 1;

		//user defined white list
		global $BX_SECURITY_AV_WHITE_LIST;
		if(is_array($BX_SECURITY_AV_WHITE_LIST))
			foreach($BX_SECURITY_AV_WHITE_LIST as $white_substr)
				if(strpos($this->data, $white_substr) !== false)
					return 1;

		return 0;
	}

	//заглушка. Возщвращает рейтинг опасности текущего блока из кеша, или FALSE
	// кешируются только составляющся рейтинга, вложденная внутренними правилами.
	function returnfromcache()
	{
		// тут можно вставить кеширование. Для кеширование вычислять и сохранять кеш от $this->data
		return false;
	}

	//заглушка. Добавляет рейтинг опасности для текущего блока в кеш.
	function addtocache()
	{
		// тут можно вставить кеширование. Для кеширование вычислять и сохранять кеш от $this->data
		return true;
	}

	//механизм для вывода сообщения об обнаруженном подозрительном текущем блоке
	function dolog()
	{
		if(defined("ANTIVIRUS_CREATE_TRACE"))
			$this->CreateTrace();

		$uniq_id = md5($this->data);
		$rsLog = CSecurityDB::Query("SELECT * FROM b_sec_virus WHERE ID = '".$uniq_id."'", "Module: security; Class: CSecurityAntiVirus; Function: AddEventLog; File: ".__FILE__."; Line: ".__LINE__);
		$arLog = CSecurityDB::Fetch($rsLog);
		if($arLog && ($arLog["SENT"] == "Y"))
		{
			CSecurityDB::Query("DELETE FROM b_sec_virus WHERE SENT = 'Y' AND TIMESTAMP_X < ".CSecurityDB::SecondsAgo($BX_SECURITY_AV_TIMEOUT*60)."", "Module: security; Class: CSecurityAntiVirus; Function: AddEventLog; File: ".__FILE__."; Line: ".__LINE__);
			$rsLog = CSecurityDB::Query("SELECT * FROM b_sec_virus WHERE ID = '".$uniq_id."'", "Module: security; Class: CSecurityAntiVirus; Function: AddEventLog; File: ".__FILE__."; Line: ".__LINE__);
			$arLog = CSecurityDB::Fetch($rsLog);
		}

		if(!$arLog)
		{
			$ss = $this->data;

			if(defined("ANTIVIRUS_CREATE_TRACE"))
				foreach($this->resultrules as $k=>$v)
					$ss .= "\n".$k."=".$v;

			if(defined("SITE_ID") && !defined("ADMIN_SECTION"))
			{
				$SITE_ID = SITE_ID;
			}
			else
			{
				$rsDefSite = CSecurityDB::Query("SELECT LID FROM b_lang WHERE ACTIVE='Y' ORDER BY DEF desc, SORT", "Module: security; Class: CSecurityAntiVirus; Function: AddEventLog; File: ".__FILE__."; Line: ".__LINE__);
				$arDefSite = CSecurityDB::Fetch($rsDefSite);
				if($arDefSite)
					$SITE_ID = $arDefSite["LID"];
				else
					$SITE_ID = false;
			}

			$s = serialize(array(
				"SEVERITY" => "SECURITY",
				"AUDIT_TYPE_ID" => "SECURITY_VIRUS",
				"MODULE_ID" => "security",
				"ITEM_ID" => "UNKNOWN",
				"REMOTE_ADDR" => $_SERVER["REMOTE_ADDR"],
				"USER_AGENT" => $_SERVER["HTTP_USER_AGENT"],
				"REQUEST_URI" => $_SERVER["REQUEST_URI"],
				"SITE_ID" => defined("SITE_ID")? SITE_ID: false,
				"USER_ID" => false,
				"GUEST_ID" => array_key_exists("SESS_GUEST_ID", $_SESSION) && ($_SESSION["SESS_GUEST_ID"] > 0)? $_SESSION["SESS_GUEST_ID"]: false,
				"DESCRIPTION" => "==".base64_encode($ss),
			));
			CSecurityDB::QueryBind(
				"insert into b_sec_virus (ID, TIMESTAMP_X, SITE_ID, INFO) values ('".$uniq_id."', ".CSecurityDB::CurrentTimeFunction().", ".($SITE_ID? "'".$SITE_ID."'": "null").", :INFO)",
				array("INFO" => base64_encode($s)),
				"Module: security; Class: CSecurityAntiVirus; Function: AddEventLog; File: ".__FILE__."; Line: ".__LINE__
			);
			@fclose(@fopen($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_cache/b_sec_virus","w"));
		}
	}


	// вызывается каждый раз, когда обработка блока закончена и блок признан нормальным.
	// функция должна возвратить содержимое блока.
	function end_okblock()
	{
		return $this->data;
	}

	function end_whiteblock()
	{
		return $this->data;
	}

	// вызывается каждый раз, когда обработка блока закончена и блок признан опасным.
	// функция должна возвратить содержимое блока.
	function end_blkblock()
	{
		if($this->replace)
			return $this->replacement;
		else
			return $this->data;
	}

	function CreateTrace()
	{
		$cache_id = md5($this->data);
		$fn = $_SERVER["DOCUMENT_ROOT"]."/bitrix/cache/virus.db/".$cache_id.".vir";
		if(!file_exists($fn))
		{
			CheckDirPath($fn);
			$f = fopen($fn, "wb");

			fwrite($f, $this->data);

			fwrite($f, "\n------------------------------\n\$_SERVER:\n");
			foreach($_SERVER as $k=>$v)
				fwrite($f, $k." = ".$v."\n");

			fwrite($f, "\n------------------------------\n\$this->resultrules:\n");
			foreach($this->resultrules as $k=>$v)
				fwrite($f, $k." = ".$v."\n");

			fclose($f);

			@chmod($fn, BX_FILE_PERMISSIONS);
		}
	}

	function Analyze($content)
	{
		static $arLocalCache = array();

		$content_len = defined("BX_UTF")? mb_strlen($content, 'latin1'): strlen($content);
		if(intval(ini_get("pcre.backtrack_limit")) <= $content_len)
			@ini_set("pcre.backtrack_limit", $content_len+1);

		$this->stylewithiframe = preg_match("/<style.*>\s*iframe/", $content);

		$this->next = $content;
		$this->prev = "";
		//                    1  <       2         3  >  4        5        6
		while(preg_match("/^(.*?)<(script|iframe)(.*?)>(.*?)(<\\/\\2.*?>)(.*)/is", $this->next, $ret))
		{
			$this->prev .= $ret[1];
			$this->data = "<".$ret[2].$ret[3].">".$ret[4].$ret[5]; //полный код блока, включая ограничивающие теги
			$this->next = $ret[6];

			// массив строк из body
			$this->scriptblockadd = false;
			$this->resultrules = array();

			$this->bodylines = false;
			$this->atributes = $ret[3];
			if(strtolower($ret[2]) == 'script')
			{
				$this->body = $this->returnscriptbody($this->data);
				$this->type = 'script';
			}
			else
			{
				$this->body = '';
				$this->type = 'iframe';
			}

			if($this->isinwhitelist())
			{
				$this->prev .= $this->end_whiteblock();
			}
			else
			{
				$cache_id = md5($this->data);
				if(isset($arLocalCache[$cache_id]))
					$rating = $arLocalCache[$cache_id];
				else
					$rating = $arLocalCache[$cache_id] = $this->returnblockrating();

				if($rating >= $this->maxrating)
				{
					$this->dolog();
					$this->prev .= $this->end_blkblock();
				}
				else
				{
					$this->prev .= $this->end_okblock();
				}
			}

			$this->cnt++;
		}

		return $this->prev.$this->next;
	}

	/*
	Возвращает рейтинг опасности блока (ифрейм или скрипт)
	входные параметры класса должны быть заполнеы.
	*/
	function returnblockrating()
	{
		if($this->type=='iframe')
		{
			if(!preg_match("/src=[\'\"]?http/", $this->atributes))
				return 0;
		}

		$r = $this->returnfromcache();
		if($r === false)
		{
			$r = 0;
			//вначале все кешируемые внутренние правила
			if($this->type=='iframe')
			{
				$r += $this->ruleframevisiblity();
			}
			elseif($this->type=='script')
			{
				$r += $this->rulescriptbasics();
				$r += $this->rulescriptvbscript();
				$r += $this->rulescriptlenghts();
				$r += $this->rulescriptfrequensy();
				$r += $this->rulescriptwhiterules();
				$r += $this->rulescriptnamerules();
			}
			$r += $this->ruleallsources();

			$this->addtocache($r);
		}

		// некешируемые наружные правила..
		$r += $this->rulescriptglobals();
		$r += $this->rulescriptblocks();

		return $r;
	}

	// ПРАВИЛА
	// надбавки и скидки действующие для каждого скрипта (возможно с некоторыми условиями)
	function rulescriptglobals()
	{
		$r = 0;
		if(!$this->useglobalrules)
		{
			return 0;
		}

		if($this->type=='script' && $this->stylewithiframe
		)
		{
			$val = 4;
			$r += $val;
			$this->resultrules['rulescriptglobals_styleiframe'] = $val;
		}

		if($this->place == "post")
		{
			$val = 12;
			$r += $val;
			$this->resultrules['rulescriptglobals_blockafterend'] = $val;
		}

		if($this->place == "pre")
		{
			$val = 12;
			$r += $val;
			$this->resultrules['rulescriptglobals_blockprestart'] = $val;
		}

		return $r;
	}

	//правила, учитывающие окружение скрипта
	function rulescriptblocks()
	{
		$r = 0;
		$strp = preg_replace('/<!\-\-.*?\-\->$/', '', $this->prev);
		$strn = preg_replace('/^<!\-\-.*?\-\->/', '', $this->next);
		//удалили окружающие комментарии, если имелись..

		if($this->cnt == 0) //обрабатывается первое попадание...
		{
			if(preg_match("/^\s*$/is", $strp))
			{
				$val = 10;
				$r += $val;
				$this->resultrules['rulescriptblocks_blockinstart'] = $val;
			}
		}

		if(preg_match("/^\s*$/is", $strn))
		{
			$val = 10;
			$r += $val;
			$this->resultrules['rulescriptblocks_endofhtml'] = $val;
		}

		if(preg_match("/<body[^>]*?>\s*$/is", $strp))
		{
			$val = 3;
			$r += $val;
			$this->resultrules['rulescriptblocks_postbody'] = $val;
		}

		if(preg_match("/^\s*<\\/body[^>]*?>/is", $strn))
		{
			$val = 3;
			$r += $val;
			$this->resultrules['rulescriptblocks_preendofbody'] = $val;
		}

		if(preg_match("/<\\/html[^>]*?>\s*$/is", $strp))
		{
			$val = 10;
			$r += $val;
			$this->resultrules['rulescriptblocks_postendofhtml'] = $val;
		}

		if($this->type == 'iframe')
		{
			if(preg_match("/<div[^>]+((visibility\s*:\s*hidden)|(display\s*:\s*none))[^>]*>\s*$/is", $strp))
			{
				$val = 11;
				$r += $val;
				$this->resultrules['rulescriptblocks_inhideddiv'] = $val;
			}
		}

		if(preg_match("/^\s*<noscript/is", $strn))
		{
			$val = -3;
			$r += $val;
			$this->resultrules['rulescriptblocks_prenoscript'] = $val;
		}

		return $r;
	}

	//правила, отлавливающие "невидимость" блока
	function ruleframevisiblity()
	{
		$r = 0;
		if(
			preg_match('/visibility\s*:\s*hidden/is', $this->atributes)
			|| preg_match('/display\s*:\s*none/is', $this->atributes)
		)
		{
			$val = 20;
			$r += $val;
			$this->resultrules['ruleframevisiblity_invisible'] = $val;
		}

		if(
			preg_match('/width=[\'\"]?[10][\'\"]?/is', $this->atributes)
			&& preg_match('/height=[\'\"]?[10][\'\"]?/is', $this->atributes)
		)
		{
			$val = 20;
			$r += $val;
			$this->resultrules['ruleframevisiblity_sizes'] = $val;
		}

		if(preg_match('/position\s*:\s*absolute/is', $this->atributes))
		{
			$val = 2;
			$r += $val;
			$this->resultrules['ruleframevisiblity_position'] = $val;
		}
		return $r;
	}

	//правила, отлавливающие потенциально опасныеключевые слова в скрипте
	function rulescriptbasics()
	{
		$r = 0;
		if(preg_match("/\<iframe/is", $this->body))
		{
			$val = 11;
			$r += $val;
			$this->resultrules['rulescriptbasics_iframe'] = $val;
		}

		if(preg_match("/eval\(/is", $this->body))
		{
			$val = 7;
			$r += $val;
			$this->resultrules['rulescriptbasics_eval'] = $val;
		}

		if(preg_match("/replace\(/is", $this->body))
		{
			$val = 4;
			$r += $val;
			$this->resultrules['rulescriptbasics_raplace'] = $val;
		}

		if(preg_match("/unescape\(/is", $this->body))
		{
			$val = 6;
			$r += $val;
			$this->resultrules['rulescriptbasics_unescape'] = $val;
		}

		if(preg_match("/fromCharCode\(/is", $this->body))
		{
			$val = 5;
			$r += $val;
			$this->resultrules['rulescriptbasics_fromcharcode'] = $val;
		}

		if(preg_match("/parseInt\(/is", $this->body))
		{
			$val = 2;
			$r += $val;
			$this->resultrules['rulescriptbasics_parseInt'] = $val;
		}

		if(preg_match("/substr\(/is", $this->body))
		{
			$val = 1;
			$r += $val;
			$this->resultrules['rulescriptbasics_substr'] = $val;
		}

		if(preg_match("/substring\(/is", $this->body))
		{
			$val = 1;
			$r += $val;
			$this->resultrules['rulescriptbasics_substring'] = $val;
		}

		if(preg_match("/document\.write\(/is", $this->body))
		{
			$val = 1;
			$r += $val;
			$this->resultrules['rulescriptbasics_documentwrite'] = $val;
		}

		if(preg_match("/window\.status/is", $this->body))
		{
			$val = 3;
			$r += $val;
			$this->resultrules['rulescriptbasics_windowstatus'] = $val;
		}

		if(
			preg_match('/visibility\s*:\s*hidden/is', $this->body)
			|| preg_match('/display\s*:\s*none/is', $this->body)
		)
		{
			$val = 8;
			$r += $val;
			$this->resultrules['rulescriptbasics_invisible'] = $val;
		}

		return $r;
	}

	//правила, отлавливающие vbscript
	function rulescriptvbscript()
	{
		$r = 0;
		if(preg_match('/vbscript/is', $this->atributes))
		{
			$val = 8;
			$r += $val;
			$this->resultrules['rulescript_vbscript'] = $val;
		}
		return $r;
	}

	//правила, отлавливающие опасные места загрузки блоков
	function ruleallsources()
	{
		$r = 0;
		static $bl = array(
			"/gumblar\.cn/is",
			"/martuz\.cn/is",
			"/beladen\.net/is",
			"/38zu\.cn/is",
			"/googleanalytlcs\.net/is",
			"/lousecn\.cn/is",
			"/fqwerz\.cn/is",
			"/d99q\.cn/is",
			"/orgsite\.info/is",
			"/94\.247\.2\.0/is",
			"/94\.247\.2\.195/is",
			"/mmsreader\.com/is",
			"/google-ana1yticz\.com/is",
			"/my2\.mobilesect\.info/is",
			"/thedeadpit\.com/is",
			"/internetcountercheck\.com/is",
			"/165\.194\.30\.123/is",
			"/ruoo\.info/is",
			"/gogo2me\.net/is",
			"/live-counter\.net/is",
			"/klinoneshoes\.info/is",
			"/protection-livescan\.com/is",
			"/webexperience13\.com/is",
			"/q5x\.ru/is",
		);

		foreach($bl as $url)
		{
			if(preg_match($url, $this->atributes))
			{
				$val = 12;
				$r += $val;
				$this->resultrules['ruleallsources_url'] = $val;
				return $r;//только одно правило из блока
			}
		}

		if(preg_match('/src=.*anal.*google/is', $this->atributes))
		{
			$val = 12;
			$r += $val;
			$this->resultrules['ruleallsources_url'] = $val;
			return $r;//только одно правило из блока
		}

		if(
			preg_match('/src=.*google.*anal/is', $this->atributes)
			&& !preg_match('/src=.*google\-analytics\.com/is', $this->atributes)
		)
		{
			$val = 12;
			$r += $val;
			$this->resultrules['ruleallsources_url'] = $val;
			return $r;//только одно правило из блока
		}

		if(preg_match('/src=.*\:\/\/\d+\.\d+\.\d+\.\d+/is', $this->atributes))
		{
			$val = 10;
			$r += $val;
			$this->resultrules['ruleallsources_ip'] = $val;
			return $r;//только одно правило из блока
		}

		if(preg_match('/src=.*\:\d+\//is', $this->atributes))
		{
			$val = 10;
			$r += $val;
			$this->resultrules['ruleallsources_port'] = $val;
			return $r;//только одно правило из блока
		}

		if(preg_match('/src=[\'\"]?http\:\/\//is', $this->atributes))
		{
			$val = 9;
			$r += $val;
			$this->resultrules['ruleallsources_extern'] = $val;
			return $r;//только одно правило из блока
		}

		return $r;
	}

	//правила, учитываюбщие подозрительные длины строк и объектов
	function rulescriptlenghts()
	{
		if(!$this->bodylines)
			$this->bodylines = explode("\n", $this->body);

		$r = 0;

		if(count($this->bodylines) == 1)
		{
			$ll = strlen(bin2hex($this->body))/2;
			if($ll > 500)
			{
				$val = 9;
				$r += $val;
				$this->resultrules['rulescriptlenghts_sl'] = $val;
				return $r;//только одно правило из блока
			}
			elseif($ll > 300)
			{
				$val = 7;
				$r += $val;
				$this->resultrules['rulescriptlenghts_sl'] = $val;
				return $r;//только одно правило из блока
			}
			elseif($ll > 100)
			{
				$val = 5;
				$r += $val;
				$this->resultrules['rulescriptlenghts_sl'] = $val;
				return $r;//только одно правило из блока
			}
		}
		else
		{
			$mxl = 0;
			foreach($this->bodylines as $str)
			{
				$ll = strlen(bin2hex($str))/2;
				if($mxl < $ll)
					$mxl = $ll;
			}

			if($ll > 500)
			{
				$val = 7;
				$r += $val;
				$this->resultrules['rulescriptlenghts_ml'] = $val;
				return $r;//только одно правило из блока
			}
			elseif($ll > 300)
			{
				$val = 5;
				$r += $val;
				$this->resultrules['rulescriptlenghts_ml'] = $val;
				return $r;//только одно правило из блока
			}
			elseif($ll > 100)
			{
				$val = 3;
				$r += $val;
				$this->resultrules['rulescriptlenghts_ml'] = $val;
				return $r;//только одно правило из блока
			}
		}

		return $r;
	}

	// Анализ частотных вхождений символов...
	function rulescriptfrequensy()
	{
		if(!$this->bodylines)
			$this->bodylines = explode("\n", $this->body);

		$all = array("MAXCHAR"=>0, "D"=>0,"H"=>0, "NW"=>0, "B"=>0, "LEN"=>0 );
		$maxes = array("MAXCHAR"=>0, "D"=>0,"H"=>0, "NW"=>0, "B"=>0, "LEN"=>0 );

		foreach($this->bodylines as $str)
		{
			$ret = $this->getstatchars($str);

			$all['MAXCHAR'] += $ret['MAXCHAR'];
			$all['D'] += $ret['D'];
			$all['H'] += $ret['H'];
			$all['NW'] += $ret['NW'];
			$all['B'] += $ret['B'];
			$all['LEN'] += $ret['LEN'];

			if($ret['LEN'] > 30)
			{
				$ret['MAXCHAR'] = $ret['MAXCHAR']*100/$ret['LEN'];
				$ret['D'] = $ret['D']*100/$ret['LEN'];
				$ret['H'] = $ret['H']*100/$ret['LEN'];
				$ret['NW'] = $ret['NW']*100/$ret['LEN'];
				$ret['B'] = $ret['B']*100/$ret['LEN'];

				if($ret['MAXCHAR'] > $maxes['MAXCHAR'])
					$maxes['MAXCHAR'] = $ret['MAXCHAR'];
				if($ret['D'] > $maxes['D'])
					$maxes['D'] = $ret['D'];
				if($ret['H'] > $maxes['H'])
					$maxes['H'] = $ret['H'];
				if($ret['NW'] > $maxes['NW'])
					$maxes['NW'] = $ret['NW'];
				if($ret['B'] > $maxes['B'])
					$maxes['B'] = $ret['B'];
			}
		}

		if($all['LEN'] > 0)
		{
			$all['MAXCHAR'] = $all['MAXCHAR']*100/$all['LEN'];
			$all['D'] = $all['D']*100/$all['LEN'];
			$all['H'] = $all['H']*100/$all['LEN'];
			$all['NW'] = $all['NW']*100/$all['LEN'];
			$all['B'] = $all['B']*100/$all['LEN'];
		}

		$g3=$g4=$g5=$g6=0; // груповые баллы
		$g3s=$g4s=$g5s=$g6s=0; // груповые баллы


		if($all['LEN'] > 30)
		{
			//G3 какой либо символ встречается более чем в ps1%  ps1=17   [3]
			//G3 какой либо символ встречается более чем в ps2%  ps2=19   [5]
			//G3 какой либо символ встречается более чем в ps3%  ps3=20   [7]

			if($all['MAXCHAR'] > 17)
			{
				$val = 2;
				if($g3 < $val)
				{
					$g3 = $val;
					$g3s = "rulescriptfrequensy_maxchar";
				}
			}

			if($all['MAXCHAR'] > 19)
			{
				$val = 4;
				if($g3 < $val)
				{
					$g3 = $val;
					$g3s = "rulescriptfrequensy_maxchar";
				}
			}

			if($all['MAXCHAR'] > 20)
			{
				$val = 6;
				if($g3 < $val)
				{
					$g3 = $val;
					$g3s = "rulescriptfrequensy_maxchar";
				}
			}

			//G4 процентное содержание цифр более чем pc1%   pc1= 20   [6]
			//G4 процентное содержание цифр более чем pc2%   pc2= 25   [8]
			//G4 процентное содержание цифр более чем pc3%   pc3= 30   [9]
			if($all['D'] > 20)
			{
				$val = 6;
				if($g3 < $val)
				{
					$g4 = $val;
					$g4s = "rulescriptfrequensy_D";
				}
			}

			if($all['D'] > 25)
			{
				$val = 8;
				if($g4 < $val)
				{
					$g4 = $val;
					$g4s = "rulescriptfrequensy_D";
				}
			}

			if($all['D'] > 40)
			{
				$val = 9;
				if($g4 < $val)
				{
					$g4 = $val;
					$g4s = "rulescriptfrequensy_D";
				}
			}

			//G4 процентное содержание HEX цифр более чем ph1%   ph1=35    [5]
			//G4 процентное содержание HEX цифр более чем ph2%   ph2=45    [7]
			//G4 процентное содержание HEX цифр более чем ph3%   ph3=55    [9]

			if($all['H'] > 35)
			{
				$val = 5;
				if($g4 < $val)
				{
					$g4 = $val;
					$g4s="rulescriptfrequensy_H";
				}
			}


			if($all['H'] > 40)
			{
				$val = 7;
				if($g3 < $val)
				{
					$g4 = $val;
					$g4s = "rulescriptfrequensy_H";
				}
			}

			if($all['H'] > 55)
			{
				$val = 9;
				if($g4 < $val)
				{
					$g4 = $val;
					$g4s = "rulescriptfrequensy_H";
				}
			}

			//G5 процентное содержание невордслооварных символов pnw1% = 23  [3]
			//G5 процентное содержание невордслооварных символов pnw2% = 26  [5]
			//G5 процентное содержание невордслооварных символов pnw3% = 30  [7]

			if($all['NW'] > 23)
			{
				$val = 2;
				if($g5 < $val)
				{
					$g5 = $val;
					$g5s = "rulescriptfrequensy_NW";
				}
			}

			if($all['NW'] > 26)
			{
				$val = 4;
				if($g5 < $val)
				{
					$g5 = $val;
					$g5s = "rulescriptfrequensy_NW";
				}
			}

			if($all['NW'] > 30)
			{
				$val = 6;
				if($g5 < $val)
				{
					$g5 = $val;
					$g5s = "rulescriptfrequensy_NW";
				}
			}

			//G6 процентное содержание символов с кодом меньше чем 20 (hex) больше чем pb1%  0.1   [7]
			//G6 процентное содержание символов с кодом меньше чем 20 (hex) больше чем pb2%  0.5   [8]
			//G6 процентное содержание символов с кодом меньше чем 20 (hex) больше чем pb3%  1.0   [9]
			if($all['B'] > 0.1)
			{
				$val = 7;
				if($g6 < $val)
				{
					$g6 = $val;
					$g6s = "rulescriptfrequensy_B";
				}
			}

			if($all['B'] > 0.5)
			{
				$val = 8;
				if($g6 < $val)
				{
					$g6 = $val;
					$g6s = "rulescriptfrequensy_B";
				}
			}

			if($all['B'] > 1)
			{
				$val = 9;
				if($g6 < $val)
				{
					$g6 = $val;
					$g6s = "rulescriptfrequensy_B";
				}
			}
		};// if($all['LEN']>30)

		//G3 какой либо символ встречается  в одной строке (длиной более psslss1 =30символов) более чем в pss1%  20  [3]
		//G3 какой либо символ встречается  в одной строке (длиной более psslss2 =30символов) более чем в pss2%  24  [5]
		//G3 какой либо символ встречается  в одной строке (длиной более psslss3 =30символов) более чем в pss3%  28  [6]
		if($maxes['MAXCHAR']>20)
		{

			$val = 3;
			if($g3 < $val)
			{
				$g3 = $val;
				$g3s = "rulescriptfrequensystr_MAXCHAR";
			}
		}

		if($maxes['MAXCHAR'] > 24)
		{
			$val = 5;
			if($g3 < $val)
			{
				$g3 = $val;
				$g3s = "rulescriptfrequensystr_MAXCHAR";
			}
		}

		if($maxes['MAXCHAR'] > 28)
		{
			$val = 6;
			if($g3 < $val)
			{
				$g3 = $val;
				$g3s = "rulescriptfrequensystr_MAXCHAR";
			}
		}

		//G4 процентное содержание цифр в одной строке (длиной более psclss1=30 символов) более чем psc1% 50 [4]
		//G4 процентное содержание цифр в одной строке (длиной более psclss2=30 символов) более чем psc2% 65 [5]
		//G4 процентное содержание цифр в одной строке (длиной более psclss3=30 символов) более чем psc3% 80 [6]

		if($maxes['D'] > 50)
		{
			$val = 4;
			if($g4 < $val)
			{
				$g4 = $val;
				$g4s = "rulescriptfrequensystr_D";
			}
		}

		if($maxes['D'] > 65)
		{
			$val = 5;
			if($g4 < $val)
			{
				$g4 = $val;
				$g4s = "rulescriptfrequensystr_D";
			}
		}

		if($maxes['D'] > 80)
		{
			$val = 6;
			if($g4 < $val)
			{
				$g4 = $val;
				$g4s = "rulescriptfrequensystr_D";
			}
		}

		//G4 процентное содержание HEX цифр в одной строке (длиной более pshlss1=30символов) более чем psh1% 30 [4]
		//G4 процентное содержание HEX цифр в одной строке (длиной более pshlss2=30символов) более чем psh2% 50 [6]
		//G4 процентное содержание HEX цифр в одной строке (длиной более pshlss3=30символов) более чем psh3% 70 [8]

		if($maxes['H'] > 40)
		{
			$val = 3;
			if($g4 < $val)
			{
				$g4 = $val;
				$g4s = "rulescriptfrequensystr_H";
			}
		}

		if($maxes['H'] > 55)
		{
			$val = 5;
			if($g4 < $val)
			{
				$g4 = $val;
				$g4s = "rulescriptfrequensystr_H";
			}
		}

		if($maxes['H'] > 70)
		{
			$val = 7;
			if($g4 < $val)
			{
				$g4 = $val;
				$g4s = "rulescriptfrequensystr_H";
			}
		}

		//G5 процентное содержание невордслооварных символов в одной строке (длиной более pshlss3 =30символов) более чем psw1% = 23  [3]
		//G5 процентное содержание невордслооварных символов в одной строке (длиной более pshlss3 =30символов) более чем psw2% = 26  [5]
		//G5 процентное содержание невордслооварных символов в одной строке (длиной более pshlss3 =30символов) более чем psw3% = 30  [7]

		if($maxes['NW'] > 23)
		{
			$val = 3;
			if($g5 < $val)
			{
				$g5 = $val;
				$g5s = "rulescriptfrequensystr_NW";
			}
		}

		if($maxes['NW'] > 26)
		{
			$val = 5;
			if($g5 < $val)
			{
				$g5 = $val;
				$g5s = "rulescriptfrequensystr_NW";
			}
		}

		if($maxes['NW'] > 30)
		{
			$val = 7;
			if($g5 < $val)
			{
				$g5 = $val;
				$g5s = "rulescriptfrequensystr_NW";
			}
		}

		//G6 процентное содержание символов с кодом меньше чем 20 (hex) в одной строке  (длиной более psblss1=30 символов) больше чем psb1% 0.1   [7]
		//G6 процентное содержание символов с кодом меньше чем 20 (hex) в одной строке  (длиной более psblss2=30 символов) больше чем psb2% 0.5 [8]
		//G6 процентное содержание символов с кодом меньше чем 20 (hex) в одной строке  (длиной более psblss3=30 символов) больше чем psb3% 1.0  [9]

		if($maxes['B'] > 0.1)
		{
			$val = 7;
			if($g6 < $val)
			{
				$g6 = $val;
				$g6s = "rulescriptfrequensystr_B";
			}
		}

		if($maxes['B'] > 0.5)
		{
			$val = 8;
			if($g6 < $val)
			{
				$g6 = $val;
				$g6s = "rulescriptfrequensystr_B";
			}
		}

		if($maxes['B'] > 1)
		{
			$val = 9;
			if($g6 < $val)
			{
				$g6 = $val;
				$g6s = "rulescriptfrequensystr_B";
			}
		}

		if(!empty($g3s))
			$this->resultrules[$g3s] = $g3;
		if(!empty($g4s))
			$this->resultrules[$g4s] = $g4;
		if(!empty($g5s))
			$this->resultrules[$g5s] = $g5;
		if(!empty($g6s))
			$this->resultrules[$g6s] = $g6;

		return ($g3+$g4+$g5+$g6);
	}

	// признаки, уменьшающие рейтинг опасности скрипта
	function rulescriptwhiterules()
	{
		if(!$this->bodylines)
			$this->bodylines = explode("\n", $this->body);

		$ll = strlen(bin2hex($this->body))/2;
		$r = 0;
		$lstr = count($this->bodylines);

		if(!preg_match("/src=/", $this->atributes))
		{
			if($ll < 100)
			{
				$val = -6;
				$this->resultrules["rulescriptwhiterules_len"] = $val;
				$r += $val;
			}
			elseif($ll < 200)
			{
				$val = -4;
				$this->resultrules["rulescriptwhiterules_len"] = $val;
				$r += $val;
			}
			elseif($ll < 400)
			{
				$val = -1;
				$this->resultrules["rulescriptwhiterules_len"] = $val;
				$r += $val;
			}

			$ok = 0;
			$ok2 = 0;
			$i = 0;
			$lstr=sizeof($this->bodylines);
			while((!$ok || !$ok2)  && $i<$lstr)
			{
				if(!$ok && preg_match("/^[\\s\\r\\n]*$/", $this->bodylines[$i]))
				{
					$val = -6;
					$this->resultrules["rulescriptwhiterules_nullines"] = $val;
					$r += $val;
					$ok = 1;
				}

				if(!$ok2 && preg_match("/^((  )|(\t))/", $this->bodylines[$i]))
				{
					$val = -6;
					$this->resultrules["rulescriptwhiterules_tabs"] = $val;
					$r += $val;
					$ok2 = 1;
				}

				$i++;
			}
		}

		if($lstr > 30)
		{
			$val = -20;
			$this->resultrules["rulescriptwhiterules_lines"] = $val;
			$r += $val;
		}
		elseif($lstr > 15)
		{
			$val = -14;
			$this->resultrules["rulescriptwhiterules_lines"] = $val;
			$r += $val;
		}
		elseif($lstr > 7)
		{
			$val = -6;
			$this->resultrules["rulescriptwhiterules_lines"] = $val;
			$r += $val;
		}

		return $r;
	}

	//анализ признаков в именах функций и переменных
	function rulescriptnamerules()
	{

		$rr = $this->getnames($this->body);

		$cc = 0;
		$cn = 0;
		$r = 0;

		foreach($rr['f'] as $k=>$v)
		{
			$cc++;
			if(!$this->isnormalname($v, $l))
				$cn++;
		}

		$mxl = 0;
		foreach($rr['n'] as $k=>$v)
		{
			$cc++;
			if(!$this->isnormalname($v, $l))
				$cn++;

			if($l > $mxl)
				$mxl = $l;
		}

		if($mxl > 35)
		{
			$val = 6;
			$this->resultrules["rulescriptnamerules_nlen"] = $val;
			$r += $val;
		}
		elseif($mxl > 25)
		{
			$val = 4;
			$this->resultrules["rulescriptnamerules_nlen"] = $val;
			$r += $val;
		}
		elseif($mxl > 15)
		{
			$val = 2;
			$this->resultrules["rulescriptnamerules_nlen"] = $val;
			$r += $val;
		}

		$mxs = 0;
		foreach($rr['s'] as $k=>$v)
		{
			$l = strlen(bin2hex($v))/2;
			if($l > $mxs)
				$mxs = $l;
		}

		if($mxs > 400)
		{
			$val = 7;
			$this->resultrules["rulescriptnamerules_str"] = $val;
			$r += $val;
		}
		elseif($mxs > 200)
		{
			$val = 4;
			$this->resultrules["rulescriptnamerules_str"] = $val;
			$r += $val;
		}
		elseif($mxs > 100)
		{
			$val = 2;
			$this->resultrules["rulescriptnamerules_str"] = $val;
			$r += $val;
		}

		if($cc > 3)
		{
			$nspp = 100*$cn/$cc;

			if($nspp > 40)
			{
				$val = 9;
				$this->resultrules["rulescriptnamerules_nnormnam"] = $val;
				$r += $val;
			}
			elseif($nspp > 25)
			{
				$val = 8;
				$this->resultrules["rulescriptnamerules_nnormnam"] = $val;
				$r += $val;
			}
			elseif($nspp > 10)
			{
				$val = 6;
				$this->resultrules["rulescriptnamerules_nnormnam"] = $val;
				$r += $val;
			}
		}

		return $r;
	}

	// вспомогательные функции..

	// возвращает частотные содержания символов в строке
	function getstatchars(&$str)
	{
		static $arCharClasses = false;
		if(!$arCharClasses)
		{
			$arCharClasses = array(
				'D' => array(),
				'H' => array(),
				'B' => array(),
				'NW' => array(),
			);

			for($i = ord('0'), $end = ord('9'); $i <= $end; $i++)
				$arCharClasses['D'][] = $i;

			for($i = ord('a'), $end = ord('f'); $i <= $end; $i++)
				$arCharClasses['H'][] = $i;

			for($i = ord('A'), $end = ord('F'); $i <= $end; $i++)
				$arCharClasses['H'][] = $i;

			for($i = 0;$i < 32; $i++)
				$arCharClasses['B'][] = $i;

			$strPunct = "`~!@#$%^&*[]{}();:'\",.\/?\|";
			$len = strlen($strPunct);
			for($i = 0; $i < $len; $i++)
				$arCharClasses['NW'][] = ord(substr($strPunct, $i ,1));
		}

		$chars = count_chars($str, 1);
		$len = array_sum($chars);

		unset($chars[9]);
		unset($chars[10]);
		unset($chars[13]);
		unset($chars[32]);
		unset($chars[208]);
		unset($chars[209]);

		$out = array(
			'MAXCHAR' => $len && count($chars)? max($chars): 0,
			"D" => 0,
			"H" => 0,
			"B" => 0,
			"NW" => 0,
			"LEN" => $len,
		);

		if(count($chars))
		{
			foreach($arCharClasses as $class => $arChars)
				foreach($arChars as $ch)
					if(isset($chars[$ch]))
						$out[$class] += $chars[$ch];
			$out["H"] += $out["D"];
		}

		return $out;
	}

	function getnames_cb($m)
	{
		$this->quotes[] = ($m[2]);
		return $m[1].$m[3];
	}

	function getnames($str)
	{
		$this->quotes = array();

		$str = preg_replace_callback("/([\'\"])(.*?[^\\\\])(\\1)/is", array($this, 'getnames_cb'), $str);

		$r = array('f'=>array(), 'n'=>array(), 's'=>array());

		if(preg_match_all("/(?<=[^\w\d\_\'\"]|^)([a-z][\w\d\_]*)([^\w\d\_\'\"])/is", $str, $ret))
		{
			$added = array();
			foreach($ret[1] as $k => $v)
			{
				if(!$added[$v])
				{
					if($ret[2][$k] == '(')
						$r['f'][] = $v;
					else
						$r['n'][]=$v;;

					$added[$v] = 1;
				}
			}
		}

		$r['s'] = $this->quotes;

		return $r;
	}

	function isnormalname($nm, &$l)
	{
		$lnm = strtolower($nm);
		if($lnm == 'ac_fl_runcontent')
			return 1;
		if($lnm == 'innerhtml')
			return 1;

		if(preg_match("/[a-z]\d+[a-z]+\d+[a-z]+/is", $nm))
			return 0;

		static $cache = array();
		if(!isset($cache[$nm]))
		{
			$chars = count_chars($nm, 1);
			$l = array_sum($chars);

			$cs = 0;
			$start = ord('a');
			$end = ord('z');
			for($i = $start; $i <= $end; $i++)
			{
				if(isset($chars[$i]))
					$cs += $chars[$i];
			}

			$cz = 0;
			$start = ord('A');
			$end = ord('Z');
			for($i = $start; $i <= $end; $i++)
			{
				if(isset($chars[$i]))
					$cz += $chars[$i];
			}

			$cc = 0;
			$start = ord('0');
			$end = ord('9');
			for($i = $start; $i <= $end; $i++)
			{
				if(isset($chars[$i]))
					$cc += $chars[$i];
			}

			if($cs<$cz && $cs>2 && $l>5)
				$cache[$nm] = 0;
			elseif($cs>$cz && $cz>3 && $l>6)
				$cache[$nm] = 0;
			elseif($l>0 && $cc*100/$l>50 && $l>5)
				$cache[$nm] = 0;
			else
				$cache[$nm] = 1;
		}
		return $cache[$nm];
	}

	function returnscriptbody($str)
	{
		if(preg_match("/<script.*?>((\s*<!\-\-)|(<!\[CDATA\[))?\s*(.*?)\s*((\/\/\s*\-\->\s*)|(\/\/\s*\]\s*\]\s*))?<\/script.*>/is", $str, $ret))
			return $ret[4];
		return $str;
	}
}
?>