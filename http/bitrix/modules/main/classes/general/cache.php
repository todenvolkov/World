<?
/*********************************************************************
						Caching
*********************************************************************/
class CPHPCache
{
	var $_cache;
	var $folder;
	var $content;
	var $vars;
	var $TTL;
	var $uniq_str;
	var $initdir;
	var $bStarted = false;
	var $bInit = "NO";

	function __construct()
	{
		return $this->CPHPCache();
	}

	function CPHPCache()
	{
		static $cache_type = false;
		if($cache_type === false)
		{
			if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/cluster/memcache.php"))
				include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/cluster/memcache.php");

			if(defined("BX_MEMCACHE_CLUSTER"))
				$prefered_cache_type = "memcache_cluster";
			elseif(defined("BX_CACHE_TYPE"))
				$prefered_cache_type = BX_CACHE_TYPE;
			else
				$prefered_cache_type = "files";

			$cache_type = "files";
			switch($prefered_cache_type)
			{
			case "memcache":
				if(extension_loaded('memcache') && defined("BX_MEMCACHE_HOST"))
				{
					include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/cache_memcache.php");
					$obCache = new CPHPCacheMemcache;
					if($obCache->IsAvailable())
						$cache_type = "memcache";
				}
				break;
			case "eaccelerator":
				if(extension_loaded('eaccelerator'))
				{
					include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/cache_eaccelerator.php");
					$obCache = new CPHPCacheEAccelerator;
					if($obCache->IsAvailable())
						$cache_type = "eaccelerator";
				}
				break;
			case "apc":
				if(extension_loaded('apc'))
				{
					include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/cache_apc.php");
					$obCache = new CPHPCacheAPC;
					if($obCache->IsAvailable())
						$cache_type = "apc";
				}
				break;
			case "memcache_cluster":
				if(extension_loaded('memcache'))
				{
					include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/cluster/classes/general/memcache_cache.php");
					$obCache = new CPHPCacheMemcacheCluster;
					if($obCache->IsAvailable())
						$cache_type = "memcache_cluster";
				}
				break;
			}

			if($cache_type == "files")
				include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/cache_files.php");
		}

		switch($cache_type)
		{
		case "memcache":
			$this->_cache = new CPHPCacheMemcache;
			break;
		case "eaccelerator":
			$this->_cache = new CPHPCacheEAccelerator;
			break;
		case "apc":
			$this->_cache = new CPHPCacheAPC;
			break;
		case "memcache_cluster":
			$this->_cache = new CPHPCacheMemcacheCluster;
			break;
		default:
			$this->_cache = new CPHPCacheFiles;
			break;
		}
	}

	function GetPath($uniq_str)
	{
		$un = md5($uniq_str);
		return substr($un, 0, 2)."/".$un.".php";
	}

	function Clean($uniq_str, $initdir = false, $basedir = "cache")
	{
		if(is_object($this) && is_object($this->_cache))
		{
			$basedir = BX_PERSONAL_ROOT."/".$basedir."/";
			$filename = CPHPCache::GetPath($uniq_str);
			return $this->_cache->clean($basedir, $initdir, "/".$filename);
		}
		else
		{
			$obCache = new CPHPCache();
			$obCache->Clean($uniq_str, $initdir, $basedir);
		}
	}

	function CleanDir($initdir = false, $basedir = "cache")
	{
		$basedir = BX_PERSONAL_ROOT."/".$basedir."/";
		return $this->_cache->clean($basedir, $initdir);
	}

	function InitCache($TTL, $uniq_str, $initdir=false, $basedir = "cache")
	{
		global $APPLICATION, $USER;
		if($initdir === false)
			$initdir = $APPLICATION->GetCurDir();

		$this->basedir = BX_PERSONAL_ROOT."/".$basedir."/";
		$this->initdir = $initdir;
		$this->filename = "/".CPHPCache::GetPath($uniq_str);
		$this->TTL = $TTL;
		$this->uniq_str = $uniq_str;

		$this->vars = false;

		if($TTL<=0)
			return false;

		if(is_object($USER))
		{
			if(strtoupper($_GET["clear_cache"])=="Y" && $USER->CanDoOperation('cache_control'))
				return false;

			if(strtoupper($_GET["clear_cache_session"])=="Y" && $USER->CanDoOperation('cache_control'))
				$_SESSION["SESS_CLEAR_CACHE"] = "Y";
			elseif(strlen($_GET["clear_cache_session"])>0 && $USER->CanDoOperation('cache_control'))
				unset($_SESSION["SESS_CLEAR_CACHE"]);
		}

		if($_SESSION["SESS_CLEAR_CACHE"] == "Y")
			return false;

		$arAllVars = array("CONTENT" => "", "VARS" => "");
		if(!$this->_cache->read($arAllVars, $this->basedir, $this->initdir, $this->filename, $this->TTL))
			return false;

		$GLOBALS["CACHE_STAT_BYTES"] += $this->_cache->read;
		$this->content = $arAllVars["CONTENT"];
		$this->vars = $arAllVars["VARS"];
		return true;
	}

	function Output()
	{
		echo $this->content;
	}

	function GetVars()
	{
		return $this->vars;
	}

	function StartDataCache($TTL=false, $uniq_str=false, $initdir=false, $vars=Array(), $basedir = "cache")
	{
		$narg = func_num_args();
		if($narg<=0)
			$TTL = $this->TTL;
		if($narg<=1)
			$uniq_str = $this->uniq_str;
		if($narg<=2)
			$initdir = $this->initdir;
		if($narg<=3)
			$vars = $this->vars;

		if($this->InitCache($TTL, $uniq_str, $initdir, $basedir))
		{
			$this->Output();
			return false;
		}

		if($TTL<=0)
			return true;

		ob_start();
		$this->vars = $vars;
		$this->bStarted = true;

		return true;
	}

	function AbortDataCache()
	{
		if(!$this->bStarted)
			return;
		$this->bStarted = false;

		ob_end_flush();
	}

	function EndDataCache($vars=false)
	{
		if(!$this->bStarted)
			return;
		$this->bStarted = false;

		$arAllVars = array(
			"CONTENT" => ob_get_contents(),
			"VARS" => ($vars!==false? $vars: $this->vars),
		);

		$this->_cache->write($arAllVars, $this->basedir, $this->initdir, $this->filename, $this->TTL);
		$GLOBALS["CACHE_STAT_BYTES"] += $this->_cache->written;

 		if(strlen(ob_get_contents())>0)
			ob_end_flush();
		else
			ob_end_clean();
	}

	function IsCacheExpired($path)
	{
		if(is_object($this) && is_object($this->_cache))
		{
			return $this->_cache->IsCacheExpired($path);
		}
		else
		{
			$obCache = new CPHPCache();
			$obCache->IsCacheExpired($path);
		}
	}
}

class CPageCache
{
	var $filename;
	var $folder;
	var $content;
	var $TTL;
	var $bStarted = false;
	var $uniq_str = false;
	var $init_dir = false;

	function GetPath($uniq_str)
	{
		$un = md5($uniq_str);
		return substr($un, 0, 2)."/".$un.".html";
	}

	function Clean($uniq_str, $initdir = false, $basedir = "cache")
	{
		$cache_file = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/".$basedir."/".$initdir."/".CPageCache::GetPath($uniq_str);
		$res = false;
		if (file_exists($cache_file))
		{
			@chmod($cache_file, BX_FILE_PERMISSIONS);
			if(unlink($cache_file))
				$res = true;
		}
		return $res;
	}

	function InitCache($TTL, $uniq_str, $initdir = false, $basedir = "cache")
	{
		global $APPLICATION, $USER;
		if($initdir === false)
			$initdir = $APPLICATION->GetCurDir();

		$this->TTL = $TTL;
		$this->folder = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/".$basedir."/".$initdir."/";
		$this->filename = $this->folder.CPageCache::GetPath($uniq_str);
		$this->tmp_filename = $this->folder.md5(mt_rand()).".tmp";
		$this->init_dir = $initdir;

		if($TTL<=0)
			return false;

		if(is_object($USER))
		{
			if(strtoupper($_GET["clear_cache"])=="Y" && $USER->CanDoOperation('cache_control'))
				return false;
			if(strtoupper($_GET["clear_cache_session"])=="Y" && $USER->CanDoOperation('cache_control'))
				$_SESSION["SESS_CLEAR_CACHE"] = "Y";
			elseif(strlen($_GET["clear_cache_session"])>0 && $USER->CanDoOperation('cache_control'))
				unset($_SESSION["SESS_CLEAR_CACHE"]);
		}

		if($_SESSION["SESS_CLEAR_CACHE"] == "Y")
			return false;

		if(!file_exists($this->filename))
			return false;

		if(!($handle = fopen($this->filename, "rb")))
			return false;

		$fdatacreate = fread($handle, 2);
		if($fdatacreate=="BX")
		{
			$fdatacreate = fread($handle, 12);
			$fdateexpire = fread($handle, 12);
		}
		else
			$fdatacreate .= fread($handle, 10);

		if(IntVal($fdatacreate)<mktime()-$TTL)
		{
			fclose($handle);
			return false;
		}

		$this->content = fread($handle, filesize($this->filename)-10);
		fclose ($handle);
		return true;
	}

	function Output()
	{
		echo $this->content;
	}

	function StartDataCache($TTL, $uniq_str=false, $initdir=false, $basedir = "cache")
	{
		if($this->InitCache($TTL, $uniq_str, $initdir, $basedir))
		{
			$this->Output();
			return false;
		}

		if($TTL<=0)
			return true;

		ob_start();
		$this->bStarted = true;
		return true;
	}

	function EndDataCache()
	{
		if(!$this->bStarted) return;
		$this->bStarted = false;
		CheckDirPath($this->filename);
		if($handle = fopen($this->tmp_filename, "wb+"))
		{
			$contents = ob_get_contents();
			fwrite($handle, "BX".str_pad(mktime(), 12, "0", STR_PAD_LEFT).str_pad(mktime() + IntVal($this->TTL), 12, "0", STR_PAD_LEFT));
			fwrite($handle, $contents);
			fclose($handle);
			if(file_exists($this->filename))
				unlink($this->filename);
			rename($this->tmp_filename, $this->filename);
		}
		ob_end_flush();
	}

	function IsCacheExpired($path)
	{
		if(!file_exists($path))
			return true;

		if(!($handle = fopen($path, "rb")))
			return false;

		$fdatacreate = fread($handle, 2);
		if($fdatacreate=="BX")
		{
			$fdatacreate = fread($handle, 12);
			$fdateexpire = fread($handle, 12);
		}
		else
			$fdataexpire = 0;

		fclose($handle);

		if(IntVal($fdateexpire)<mktime())
			return true;

		return false;
	}
}

function BXClearCache($full=false, $initdir="")
{
	if($full !== true && $full !== false && $initdir === "" && is_string($full))
	{
		$initdir = $full;
		$full = true;
	}

	$res = true;

	if($full === true)
	{
		$obCache = new CPHPCache;
		$obCache->CleanDir($initdir, "cache");
	}

	$path = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/cache".$initdir;
	if(is_dir($path) && ($handle = opendir($path)))
	{
		while(($file = readdir($handle)) !== false)
		{
			if($file == "." || $file == "..") continue;

			if(is_dir($path."/".$file))
			{
				if(!BXClearCache($full, $initdir."/".$file))
				{
					$res = false;
				}
				else
				{
					@chmod($path."/".$file, BX_DIR_PERMISSIONS);
					//We suppress error handle here because there may be valid cache files in this dir
					@rmdir($path."/".$file);
				}
			}
			elseif($full)
			{
				@chmod($path."/".$file, BX_FILE_PERMISSIONS);
				if(!unlink($path."/".$file))
					$res = false;
			}
			elseif(substr($file, -5)==".html")
			{
				if(CPageCache::IsCacheExpired($path."/".$file))
				{
					@chmod($path."/".$file, BX_FILE_PERMISSIONS);
					if(!unlink($path."/".$file))
						$res = false;
				}
			}
			elseif(substr($file, -4)==".php")
			{
				if(CPHPCache::IsCacheExpired($path."/".$file))
				{
					@chmod($path."/".$file, BX_FILE_PERMISSIONS);
					if(!unlink($path."/".$file))
						$res = false;
				}
			}
			else
			{
				//We should skip unknown file
				//it will be deleted with full cache cleanup
			}
		}
		closedir($handle);
	}

	return $res;
}
// The main purpose of the class is:
// one read - many uses - optional one write
// of the set of variables
class CCacheManager
{
	var $CACHE= array();
	var $CACHE_PATH = array();
	var $VARS = array();
	var $TTL = array();
	// Tries to read cached variable value from the file
	// Returns true on success
	// overwise returns false
	function Read($ttl, $uniqid, $table_id=false)
	{
		global $DB;
		if(array_key_exists($uniqid, $this->CACHE))
			return true;
		else
		{
			$this->CACHE[$uniqid] = new CPHPCache;
			$this->CACHE_PATH[$uniqid] = $DB->type.($table_id===false?"":"/".$table_id);
			$this->TTL[$uniqid] = $ttl;
			return $this->CACHE[$uniqid]->InitCache($ttl, $uniqid, $this->CACHE_PATH[$uniqid], "managed_cache");
		}
	}
	// This method is used to read the variable value
	// from the cache after successfull Read
	function Get($uniqid)
	{
		if(array_key_exists($uniqid, $this->VARS))
			return $this->VARS[$uniqid];
		elseif(array_key_exists($uniqid, $this->CACHE))
			return $this->CACHE[$uniqid]->GetVars();
		else
			return false;
	}
	// Sets new value to the variable
	function Set($uniqid, $val)
	{
		if(array_key_exists($uniqid, $this->CACHE))
			$this->VARS[$uniqid]=$val;
	}
	// Marks cache entry as invalid
	function Clean($uniqid, $table_id=false)
	{
		global $DB;
		$obCache = new CPHPCache;
		$obCache->Clean($uniqid, $DB->type.($table_id===false?"":"/".$table_id), "managed_cache");
		if(array_key_exists($uniqid, $this->CACHE))
		{
			unset($this->CACHE[$uniqid]);
			unset($this->CACHE_PATH[$uniqid]);
			unset($this->VARS[$uniqid]);
		}
	}
	// Marks cache entries associated with the table as invalid
	function CleanDir($table_id)
	{
		global $DB;
		$strPath = $DB->type."/".$table_id;
		foreach($this->CACHE_PATH as $uniqid=>$Path)
		{
			if($Path==$strPath)
			{
				unset($this->CACHE[$uniqid]);
				unset($this->CACHE_PATH[$uniqid]);
				unset($this->VARS[$uniqid]);
			}
		}
		$obCache = new CPHPCache;
		$obCache->CleanDir($DB->type."/".$table_id, "managed_cache");
	}
	// Clears all managed_cache
	function CleanAll()
	{
		global $DB;
		$this->CACHE= array();
		$this->CACHE_PATH = array();
		$this->VARS = array();
		$this->TTL = array();
		$obCache = new CPHPCache;
		$obCache->CleanDir(false, "managed_cache");

		if(defined("BX_COMP_MANAGED_CACHE"))
			$this->ClearByTag(true);
	}
	// Use it to flush cache to the files.
	// Causion: only at the end of all operations!
	function _Finalize()
	{
		global $DB, $CACHE_MANAGER;
		$obCache = new CPHPCache;
		foreach($CACHE_MANAGER->CACHE as $uniqid=>$val)
		{
			if(array_key_exists($uniqid, $CACHE_MANAGER->VARS))
			{
				$obCache->StartDataCache($CACHE_MANAGER->TTL[$uniqid], $uniqid, $CACHE_MANAGER->CACHE_PATH[$uniqid],  $CACHE_MANAGER->VARS[$uniqid], "managed_cache");
				$obCache->EndDataCache();
			}
		}
	}

	/*Components managed(tagged) cache*/

	var $comp_cache_stack = array();
	var $SALT = false;
	var $DBCacheTags = false;
	var $bWasTagged = false;

	function InitDBCache()
	{
		if(!$this->DBCacheTags)
		{
			global $DB;

			$this->DBCacheTags = array();
			$rs = $DB->Query("
				SELECT *
				FROM b_cache_tag
				WHERE SITE_ID = '".$DB->ForSQL(SITE_ID, 2)."'
				AND CACHE_SALT = '".$DB->ForSQL($this->SALT, 4)."'
			");
			while($ar = $rs->Fetch())
			{
				$path = $ar["RELATIVE_PATH"];
				$this->DBCacheTags[$path][$ar["TAG"]] = true;
			}
		}

	}

	function InitCompSalt()
	{
		if($this->SALT === false)
		{
			if($_SERVER["SCRIPT_NAME"] == "/bitrix/urlrewrite.php" && isset($_SERVER["REAL_FILE_PATH"]))
				$SCRIPT_NAME = $_SERVER["REAL_FILE_PATH"];
			elseif($_SERVER["SCRIPT_NAME"] == "/404.php" && isset($_SERVER["REAL_FILE_PATH"]))
				$SCRIPT_NAME = $_SERVER["REAL_FILE_PATH"];
			else
				$SCRIPT_NAME = $_SERVER["SCRIPT_NAME"];

			$this->SALT = "/".substr(md5($SCRIPT_NAME), 0, 3);
		}
	}

	function GetCompCachePath($relativePath)
	{
		global $BX_STATE;
		$this->InitCompSalt();

		if($BX_STATE === "WA")
			$salt = $this->SALT;
		else
			$salt = "/".substr(md5($BX_STATE), 0, 3);

		$path = "/".SITE_ID.$relativePath.$salt;
		return $path;
	}

	function StartTagCache($relativePath)
	{
		array_unshift($this->comp_cache_stack, array($relativePath, array()));
	}

	function EndTagCache()
	{
		global $DB;
		$this->InitCompSalt();

		if($this->bWasTagged)
		{
			$this->InitDBCache();
			$sqlSITE_ID = $DB->ForSQL(SITE_ID, 2);
			$sqlCACHE_SALT = $this->SALT;

			foreach($this->comp_cache_stack as $arCompCache)
			{
				$path = $arCompCache[0];
				if(strlen($path))
				{
					$sqlRELATIVE_PATH = $DB->ForSQL($path, 255);

					$sql = "INSERT INTO b_cache_tag (SITE_ID, CACHE_SALT, RELATIVE_PATH, TAG)
						VALUES ('".$sqlSITE_ID."', '".$sqlCACHE_SALT."', '".$sqlRELATIVE_PATH."',";

					if(!isset($this->DBCacheTags[$path]))
						$this->DBCacheTags[$path] = array();

					foreach($arCompCache[1] as $tag => $t)
					{
						if(!isset($this->DBCacheTags[$path][$tag]))
						{
							$DB->Query($sql." '".$DB->ForSQL($tag, 50)."')");
							$this->DBCacheTags[$path][$tag] = true;
						}
					}
				}
			}
		}

		array_shift($this->comp_cache_stack);
	}

	function AbortTagCache()
	{
		array_shift($this->comp_cache_stack);
	}

	function RegisterTag($tag)
	{
		if(count($this->comp_cache_stack))
		{
			$this->comp_cache_stack[0][1][$tag] = true;
			$this->bWasTagged = true;
		}
	}

	function ClearByTag($tag)
	{
		global $DB;

		if($tag === true)
			$sqlWhere = "";
		else
			$sqlWhere = " WHERE TAG = '".$DB->ForSQL($tag)."'";

		$arDirs = array();
		$rs = $DB->Query("SELECT * FROM b_cache_tag".$sqlWhere);
		while($ar = $rs->Fetch())
			$arDirs[$ar["RELATIVE_PATH"]] = $ar;
		$DB->Query("DELETE FROM b_cache_tag".$sqlWhere);

		$obCache = new CPHPCache;
		foreach($arDirs as $path => $ar)
		{
			$DB->Query("
				DELETE FROM b_cache_tag
				WHERE SITE_ID = '".$DB->ForSQL($ar["SITE_ID"])."'
				AND CACHE_SALT = '".$DB->ForSQL($ar["CACHE_SALT"])."'
				AND RELATIVE_PATH = '".$DB->ForSQL($ar["RELATIVE_PATH"])."'
			");

			if(preg_match("/^managed:(.+)$/", $path, $match))
				$this->CleanDir($match[1]);
			else
				$obCache->CleanDir($path);

			unset($this->DBCacheTags[$path]);
		}
	}
}

global $CACHE_MANAGER;
$CACHE_MANAGER = new CCacheManager;

$GLOBALS["CACHE_STAT_BYTES"] = 0;

/*****************************************************************************************************/
/************************  CStackCacheManager  *******************************************************/
/*****************************************************************************************************/
class CStackCacheEntry
{
	var $entity = "";
	var $id = "";
	var $values = array();
	var $len = 10;
	var $ttl = 3600;
	var $cleanGet = true;
	var $cleanSet = true;

	function __construct($entity, $length = 0, $ttl = 0)
	{
		$this->entity = $entity;

		if($length > 0)
			$this->len = intval($length);

		if($ttl > 0)
			$this->ttl = intval($ttl);
	}

	function SetLength($length)
	{
		if($length > 0)
			$this->len = intval($length);

		while(count($this->values) > $this->len)
		{
			$this->cleanSet = false;
			array_shift($this->values);
		}
	}

	function SetTTL($ttl)
	{
		if($ttl > 0)
			$this->ttl = intval($ttl);
	}

	function Load()
	{
		global $DB;
		$objCache = new CPHPCache;
		if($objCache->InitCache($this->ttl, $this->entity, $DB->type."/".$this->entity, "stack_cache"))
		{
			$this->values = $objCache->GetVars();
			$this->cleanGet = true;
			$this->cleanSet = true;
		}
	}

	function DeleteEntry($id)
	{
		if(array_key_exists($id, $this->values))
		{
			unset($this->values[$id]);
			$this->cleanSet = false;
		}
	}

	function Clean()
	{
		global $DB;

		$objCache = new CPHPCache;
		$objCache->Clean($this->entity, $DB->type."/".$this->entity, "stack_cache");

		$this->values = array();
		$this->cleanGet = true;
		$this->cleanSet = true;
	}

	function Get($id)
	{
		if(array_key_exists($id, $this->values))
		{
			$result = $this->values[$id];
			//Move accessed value to the top of list only when it is not at the top
			end($this->values);
			if(key($this->values) !== $id)
			{
				$this->cleanGet = false;
				unset($this->values[$id]);
				$this->values = $this->values + array($id => $result);
			}

			return $result;
		}
		else
		{
			return false;
		}
	}

	function Set($id, $value)
	{
		if(array_key_exists($id, $this->values))
		{
			unset($this->values[$id]);
			$this->values = $this->values + array($id => $value);
		}
		else
		{
			//$this->values = $this->values + array($id => $value);
			while(count($this->values) > $this->len)
				array_shift($this->values);
		}

		$this->cleanSet = false;
	}

	function Save()
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		global $DB;

		if(
			!$this->cleanSet
			|| (
				!$this->cleanGet
				&& (count($this->values) >= $this->len)
			)
		)
		{
			$objCache = new CPHPCache;
			$objCache->Clean($this->entity, $DB->type."/".$this->entity, "stack_cache");

			if($objCache->StartDataCache($this->ttl, $this->entity, $DB->type."/".$this->entity,  $this->values, "stack_cache"))
				$objCache->EndDataCache();

			$this->cleanGet = true;
			$this->cleanSet = true;
		}
	}
}

class CStackCacheManager
{
	var $cache = array();
	var $cacheLen = array();
	var $cacheTTL = array();
	var $eventHandlerAdded = false;

	function SetLength($entity, $length)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if(is_object($this->cache[$entity]))
			$this->cache[$entity]->SetLength($length);
		else
			$this->cacheLen[$entity] = $length;
	}

	function SetTTL($entity, $ttl)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if(is_object($this->cache[$entity]))
			$this->cache[$entity]->SetTTL($ttl);
		else
			$this->cacheTTL[$entity] = $ttl;
	}

	function Init($entity, $length = 0, $ttl = 0)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if (!$this->eventHandlerAdded)
		{
			AddEventHandler("main", "OnEpilog", array("CStackCacheManager", "SaveAll"));
			$this->eventHandlerAdded = True;
		}

		if($length <= 0 && isset($this->cacheLen[$entity]))
			$length = $this->cacheLen[$entity];

		if($ttl <= 0 && isset($this->cacheTTL[$entity]))
			$ttl = $this->cacheTTL[$entity];

		if (!array_key_exists($entity, $this->cache))
			$this->cache[$entity] = new CStackCacheEntry($entity, $length, $ttl);
	}

	function Load($entity)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if (!array_key_exists($entity, $this->cache))
			$this->Init($entity);

		$this->cache[$entity]->Load();
	}

	//NO ONE SHOULD NEVER EVER USE INTEGER $id HERE
	function Clear($entity, $id = False)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if (!array_key_exists($entity, $this->cache))
			$this->Load($entity);

		if ($id !== False)
			$this->cache[$entity]->DeleteEntry($id);
		else
			$this->cache[$entity]->Clean();
	}

	// Clears all managed_cache
	function CleanAll()
	{
		$this->cache = array();

		$objCache = new CPHPCache;
		$objCache->CleanDir(false, "stack_cache");
	}

	//NO ONE SHOULD NEVER EVER USE INTEGER $id HERE
	function Exist($entity, $id)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return False;

		if (!array_key_exists($entity, $this->cache))
			$this->Load($entity);

		return array_key_exists($id, $this->cache[$entity]->values);
	}

	//NO ONE SHOULD NEVER EVER USE INTEGER $id HERE
	function Get($entity, $id)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return False;

		if (!array_key_exists($entity, $this->cache))
			$this->Load($entity);

		return $this->cache[$entity]->Get($id);
	}

	//NO ONE SHOULD NEVER EVER USE INTEGER $id HERE
	function Set($entity, $id, $value)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if (!array_key_exists($entity, $this->cache))
			$this->Load($entity);

		$this->cache[$entity]->Set($id, $value);
	}

	function Save($entity)
	{
		if(defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if(array_key_exists($entity, $this->cache))
			$this->cache[$entity]->Save();
	}

	function SaveAll()
	{
		if(defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		global $stackCacheManager;

		foreach($stackCacheManager->cache as $entity => $value)
			$value->Save();
	}

	function MakeIDFromArray($arVals)
	{
		$id = "id";

		sort($arVals);

		for ($i = 0, $c = count($arVals); $i < $c; $i++)
			$id .= "_".$arVals[$i];

		return $id;
	}
}

$GLOBALS["stackCacheManager"] = new CStackCacheManager();
?>