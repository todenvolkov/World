<?
global $BX_MEMCACHE;
$BX_MEMCACHE = false;

class CPHPCacheMemcache
{
	var $sid = "";
	//cache stats
	var $written = false;
	var $read = false;
	// unfortunately is not available for memcache...

	function __construct()
	{
		$this->CPHPCacheMemcache();
	}

	function CPHPCacheMemcache()
	{
		global $BX_MEMCACHE;

		if(!is_object($BX_MEMCACHE))
			$BX_MEMCACHE = new Memcache;

		if(defined("BX_MEMCACHE_PORT"))
			$port = intval(BX_MEMCACHE_PORT);
		else
			$port = 11211;

		if(!defined("BX_MEMCACHE_CONNECTED"))
		{
			if($BX_MEMCACHE->connect(BX_MEMCACHE_HOST, $port))
				define("BX_MEMCACHE_CONNECTED", true);
		}

		if(defined("BX_CACHE_SID"))
			$this->sid = BX_CACHE_SID;
		else
			$this->sid = "BX";
	}

	function IsAvailable()
	{
		return defined("BX_MEMCACHE_CONNECTED");
	}

	function clean($basedir, $initdir = false, $filename = false)
	{
		global $BX_MEMCACHE;

		if(is_object($BX_MEMCACHE))
		{
			if(strlen($filename))
			{
				$basedir_version = $BX_MEMCACHE->get($this->sid.$basedir);
				if($basedir_version === false)
					return true;

				if($initdir !== false)
				{
					$initdir_version = $BX_MEMCACHE->get($basedir_version."|".$initdir);
					if($initdir_version === false)
						return true;
				}
				else
				{
					$initdir_version = "";
				}

				$BX_MEMCACHE->delete($basedir_version."|".$initdir_version."|".$filename);
			}
			else
			{
				if(strlen($initdir))
				{
					$basedir_version = $BX_MEMCACHE->get($this->sid.$basedir);
					if($basedir_version === false)
						return true;

					$BX_MEMCACHE->delete($basedir_version."|".$initdir);
				}
				else
				{
					$BX_MEMCACHE->delete($this->sid.$basedir);
				}
			}
			return true;
		}

		return false;
	}

	function read(&$arAllVars, $basedir, $initdir, $filename, $TTL)
	{
		global $BX_MEMCACHE;

		$basedir_version = $BX_MEMCACHE->get($this->sid.$basedir);
		if($basedir_version === false)
			return false;

		if($initdir !== false)
		{
			$initdir_version = $BX_MEMCACHE->get($basedir_version."|".$initdir);
			if($initdir_version === false)
				return false;
		}
		else
		{
			$initdir_version = "";
		}

		$arAllVars = $BX_MEMCACHE->get($basedir_version."|".$initdir_version."|".$filename);

		if($arAllVars === false)
			return false;

		return true;
	}

	function write($arAllVars, $basedir, $initdir, $filename, $TTL)
	{
		global $BX_MEMCACHE;

		$basedir_version = $BX_MEMCACHE->get($this->sid.$basedir);
		if($basedir_version === false)
		{
			$basedir_version = md5(mt_rand());
			$BX_MEMCACHE->set($this->sid.$basedir, $basedir_version);
		}

		if($initdir !== false)
		{
			$initdir_version = $BX_MEMCACHE->get($basedir_version."|".$initdir);
			if($initdir_version === false)
			{
				$initdir_version = md5(mt_rand());
				$BX_MEMCACHE->set($basedir_version."|".$initdir, $initdir_version);
			}
		}
		else
		{
			$initdir_version = "";
		}

		$BX_MEMCACHE->set($basedir_version."|".$initdir_version."|".$filename, $arAllVars, 0, time()+intval($TTL));
	}

	function IsCacheExpired($path)
	{
		return false;
	}
}
?>