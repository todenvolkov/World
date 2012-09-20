<?
IncludeModuleLangFile(__FILE__);

if(!defined("START_EXEC_TIME"))
	define("START_EXEC_TIME", getmicrotime());

class CAllSearch extends CDBResult
{
	var $Query; //Query parset
	var $Statistic; //Search statistic
	var $strQueryText = false; //q
	var $strTagsText = false; //tags
	var $strSqlWhere = ""; //additional sql filter
	var $strTags = ""; //string of tags in double quotes separated by commas
	var $errorno = 0;
	var $error = false;
	var $arParams = array();
	var $url_add_params = array(); //additional url params (OnSearch event)
	var $tf_hwm = 0;
	var $tf_hwm_site_id = "";

	function __construct($strQuery=false, $SITE_ID=false, $MODULE_ID=false, $ITEM_ID=false, $PARAM1=false, $PARAM2=false, $aSort=array(), $aParamsEx=array(), $bTagsCloud = false)
	{
		return $this->CSearch($strQuery, $SITE_ID, $MODULE_ID, $ITEM_ID, $PARAM1, $PARAM2, $aSort, $aParamsEx, $bTagsCloud);
	}

	function CSearch($strQuery=false, $LID=false, $MODULE_ID=false, $ITEM_ID=false, $PARAM1=false, $PARAM2=false, $aSort=array(), $aParamsEx=array(), $bTagsCloud = false)
	{
		if($strQuery===false)
			return $this;

		$arParams["QUERY"] = $strQuery;
		$arParams["SITE_ID"] = $LID;
		$arParams["MODULE_ID"] = $MODULE_ID;
		$arParams["ITEM_ID"] = $ITEM_ID;
		$arParams["PARAM1"] = $PARAM1;
		$arParams["PARAM2"] = $PARAM2;

		$this->Search($arParams, $aSort, $aParamsEx, $bTagsCloud);
	}
	//combination ($MODULE_ID, $PARAM1, $PARAM2, $PARAM3) is used to narrow search
	//returns recordset with search results
	function Search($arParams, $aSort=array(), $aParamsEx=array(), $bTagsCloud = false)
	{
		$DB = CDatabase::GetModuleConnection('search');

		if(!is_array($arParams))
			$arParams = Array("QUERY"=>$arParams);

		if(!is_set($arParams, "SITE_ID") && is_set($arParams, "LID"))
		{
			$arParams["SITE_ID"] = $arParams["LID"];
			unset($arParams["LID"]);
		}

		if(array_key_exists("TAGS", $arParams))
		{
			$this->strTagsText = $arParams["TAGS"];
			$arTags = explode(",", $arParams["TAGS"]);
			foreach($arTags as $i => $strTag)
			{
				$strTag = trim($strTag);
				if(strlen($strTag))
					$arTags[$i] = str_replace("\"", "\\\"", $strTag);
				else
					unset($arTags[$i]);
			}

			if(count($arTags))
				$arParams["TAGS"] = '"'.implode('","', $arTags).'"';
			else
				unset($arParams["TAGS"]);
		}

		$this->strQueryText = $strQuery = trim($arParams["QUERY"]);
		$this->strTags = $strTags  = $arParams["TAGS"];
		if((strlen($strQuery) <= 0) && (strlen($strTags) > 0))
		{
			$strQuery = $strTags;
			$bTagsSearch = true;
		}
		else
		{
			if(strlen($strTags))
				$strQuery .= " ".$strTags;
			$strQuery = preg_replace ("'&#(\\d+);'e", "chr(\\1)", $strQuery);
			$bTagsSearch = false;
		}

		if(!array_key_exists("STEMMING", $aParamsEx))
			$aParamsEx["STEMMING"] = COption::GetOptionString("search", "use_stemming", "N")=="Y";

		$this->Query = new CSearchQuery("and", "yes", 0, $arParams["SITE_ID"]);
		$query = $this->Query->GetQueryString("sc.SEARCHABLE_CONTENT", $strQuery, $bTagsSearch, $aParamsEx["STEMMING"]);
		if(!$query || strlen(trim($query))<=0)
		{
			if($bTagsCloud)
			{
				$query = "1=1";
			}
			else
			{
				$this->error = $this->Query->error;
				$this->errorno = $this->Query->errorno;
				return;
			}
		}

		if(strlen($query)>2000)
		{
			$this->error = GetMessage("SEARCH_ERROR4");
			$this->errorno = 4;
			return;
		}

		$db_events = GetModuleEvents("search", "OnSearch");
		while($arEvent = $db_events->Fetch())
		{
			$r = "";
			if($bTagsSearch)
			{
				if(strlen($strTags))
					$r = ExecuteModuleEventEx($arEvent, array("tags:".$strTags));
			}
			else
			{
				$r = ExecuteModuleEventEx($arEvent, array($strQuery));
			}
			if($r <> "")
				$this->url_add_params[] = $r;
		}

		$bIncSites = false;
		$strSqlWhere = CSearch::__PrepareFilter($arParams, $bIncSites);
		if(strlen($strSqlWhere)>0)
			$strSqlWhere = " AND ".$strSqlWhere;

		if(is_array($aParamsEx) && count($aParamsEx)>0)
		{
			$arSqlWhere=array();
			foreach($aParamsEx as $aParamEx)
			{
				$s=CSearch::__PrepareFilter($aParamEx, $bIncSites);
				if(strlen($s)>0)
					$arSqlWhere[]=$s;
			}
			if(count($arSqlWhere)>0)
				$strSqlWhere.="\n\t\t\t\tAND (\n\t\t\t\t\t(".implode(")\n\t\t\t\t\tOR(",$arSqlWhere).")\n\t\t\t\t)";
		}

		//This will be used in suggest
		$this->strSqlWhere = $strSqlWhere;

		$strSqlOrder = CSearch::__PrepareSort($aSort, "sc.", $bTagsCloud);

		if(!array_key_exists("USE_TF_FILTER", $aParamsEx))
			$aParamsEx["USE_TF_FILTER"] = COption::GetOptionString("search", "use_tf_cache", "N") == "Y";

		$bStem = !$bTagsSearch && count($this->Query->m_stemmed_words)>0;
		//calculate freq of the word on the whole site_id
		if($bStem && count($this->Query->m_stemmed_words))
		{
			$arStat = $this->GetFreqStatistics($this->Query->m_lang, $this->Query->m_stemmed_words, $arParams["SITE_ID"]);
			$this->tf_hwm_site_id = (strlen($arParams["SITE_ID"]) > 0? $arParams["SITE_ID"]: "");

			//we'll make filter by it's contrast
			if(!$bTagsCloud && $aParamsEx["USE_TF_FILTER"])
			{
				$hwm = false;
				foreach($this->Query->m_stemmed_words as $i => $stem)
				{
					if(!array_key_exists($stem, $arStat))
					{
						$hwm = 0;
						break;
					}
					elseif($hwm === false)
					{
						$hwm = $arStat[$stem]["TF"];
					}
					elseif($hwm > $arStat[$stem]["TF"])
					{
						$hwm = $arStat[$stem]["TF"];
					}
				}

				if($hwm > 0)
				{
					$strSqlWhere .= " AND st.TF >= ".number_format($hwm, 2, ".", "");
					$this->tf_hwm = $hwm;
				}
			}
		}

		if($bTagsCloud)
			$strSql = $this->tagsMakeSQL($query, $strSqlWhere, $strSqlOrder, $bIncSites, $bStem, $aParamsEx["LIMIT"]);
		else
			$strSql = $this->MakeSQL($query, $strSqlWhere, $strSqlOrder, $bIncSites, $bStem);

		//$tStart = getmicrotime();
		$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		//echo "<pre>",$strSql,"</pre><br>",(getmicrotime()-$tStart);
		parent::CDBResult($r);
	}

	function GetFilterMD5()
	{
		$perm = CSearch::CheckPermissions("sc.ID");
		$sql = preg_replace("/(DATE_FROM|DATE_TO|DATE_CHANGE)(\\s+IS\\s+NOT\\s+NULL|\\s+IS\\s+NULL|\\s*[<>!=]+\\s*'.*?')/im", "", $this->strSqlWhere);
		return md5($perm.$sql.$this->strTags);
	}

	function GetFreqStatistics($lang_id, $arStem, $site_id="")
	{
		$DB = CDatabase::GetModuleConnection('search');

		$limit = COption::GetOptionInt("search", "max_result_size");
		if($limit < 1)
			$limit = 500;

		$arResult = array();
		foreach($arStem as $stem)
			$arResult[$stem] = array("STEM" => false, "FREQ" => 0, "TF" => 0, "STEM_COUNT" => 0, "TF_SUM" => 0);

		$strSql = "
			SELECT STEM, FREQ, TF
			FROM b_search_content_freq
			WHERE LANGUAGE_ID = '".$lang_id."'
			AND STEM in ('".implode("','", $arStem)."')
			AND ".(strlen($site_id) > 0? "SITE_ID = '".$DB->ForSQL($site_id)."'": "SITE_ID IS NULL")."
			ORDER BY STEM
		";
		$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($ar = $rs->Fetch())
		{
			if(strlen($ar["TF"]) > 0)
				$arResult[$ar["STEM"]] = $ar;
		}

		$arMissed = array();
		foreach($arResult as $stem => $ar)
			if(!$ar["STEM"])
				$arMissed[] = $DB->ForSQL($stem);

		if(count($arMissed) > 0)
		{
			$strSql = "
				SELECT st.STEM, floor(st.TF*100) BUCKET, sum(st.TF) TF_SUM, count(*) STEM_COUNT
				FROM
					b_search_content_stem st
					".(strlen($site_id) > 0? "INNER JOIN b_search_content_site scsite ON scsite.SEARCH_CONTENT_ID = st.SEARCH_CONTENT_ID AND scsite.SITE_ID = '".$DB->ForSQL($site_id)."'": "")."
				WHERE st.LANGUAGE_ID = '".$lang_id."'
				AND st.STEM in ('".implode("','", $arMissed)."')
				GROUP BY st.STEM, floor(st.TF*100)
				ORDER BY st.STEM, floor(st.TF*100) DESC
			";
			$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($ar = $rs->Fetch())
			{
				$stem = $ar["STEM"];
				if($arResult[$stem]["STEM_COUNT"] < $limit)
					$arResult[$stem]["TF"] = $ar["BUCKET"]/100.0;
				$arResult[$stem]["STEM_COUNT"] += $ar["STEM_COUNT"];
				$arResult[$stem]["TF_SUM"] += $ar["TF_SUM"];
				$arResult[$stem]["DO_INSERT"] = true;
			}
		}

		foreach($arResult as $stem => $ar)
		{
			if($ar["DO_INSERT"])
			{
				$FREQ = intval(defined("search_range_by_sum_tf")? $ar["TF_SUM"]: $ar["STEM_COUNT"]);
				$strSql = "
					UPDATE b_search_content_freq
					SET FREQ=".$FREQ.", TF=".number_format($ar["TF"], 2, ".", "")."
					WHERE LANGUAGE_ID='".$lang_id."'
					AND ".(strlen($site_id) > 0? "SITE_ID = '".$DB->ForSQL($site_id)."'": "SITE_ID IS NULL")."
					AND STEM='".$DB->ForSQL($stem)."'
				";
				$rsUpdate = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if($rsUpdate->AffectedRowsCount() <= 0)
				{
					$strSql = "
						INSERT INTO b_search_content_freq
						(STEM, LANGUAGE_ID, SITE_ID, FREQ, TF)
						VALUES
						('".$DB->ForSQL($stem)."', '".$lang_id."', ".(strlen($site_id) > 0? "'".$DB->ForSQL($site_id)."'": "NULL").", ".$FREQ.", ".number_format($ar["TF"], 2, ".", "").")
					";
					$rsInsert = $DB->Query($strSql, true);
				}
			}
		}

		return $arResult;
	}

	function Repl($strCond, $strType, $strWh)
	{
		$l=strlen($strCond);

		if($this->Query->bStemming)
		{
			$arStemInfo = stemming_init($this->Query->m_lang);
			$letters = $arStemInfo["letters"];
			$strWhUpp = stemming_upper($strWh, $this->Query->m_lang);
		}
		else
		{
			$strWhUpp=ToUpper($strWh);
		}

		$strCondUpp=ToUpper($strCond);

		$pos = 0;
		do
		{
			$pos = strpos($strWhUpp, $strCondUpp, $pos);

			//Check if we are in the middle of the numeric entity
			while(
				$pos !== false &&
				preg_match("/^[0-9]+;/", substr($strWh, $pos)) &&
				preg_match("/^[0-9]+#&/", strrev(substr($strWh, 0, $pos+strlen($strCond))))
			)
			{
				$pos = strpos($strWhUpp, $strCondUpp, $pos+1);
			}

			if($pos === false) break;

			if($strType=="STEM")
			{
				$lw=strlen($strWhUpp);
				for($s=$pos; $s>=0 && strpos($letters, substr($strWhUpp, $s, 1))!==false;$s--){}
				$s++;
				for($e=$pos; $e<$lw && strpos($letters, substr($strWhUpp, $e, 1))!==false;$e++){}
				$e--;
				$a = array_keys(stemming(substr($strWhUpp,$s,$e-$s+1), $this->Query->m_lang));
				foreach($a as $stem)
				{
					if($stem == $strCondUpp)
					{
						$strWh = substr($strWh, 0, $pos)."%^%".substr($strWh, $pos, $l)."%/^%".substr($strWh,$pos+$l);
						$strWhUpp = substr($strWhUpp, 0, $pos+$l)."%^%%/^%".substr($strWhUpp,$pos+$l);
						$pos += 7;
					}
				}
			}
			else
			{
				$strWh = substr($strWh, 0, $pos)."%^%".substr($strWh, $pos, $l)."%/^%".substr($strWh,$pos+$l);
				$strWhUpp = substr($strWhUpp, 0, $pos+$l)."%^%%/^%".substr($strWhUpp,$pos+$l);
				$pos += 7;
			}
			$pos += 1;
		} while ($pos < strlen($strWhUpp));

		return $strWh;
	}

	function PrepareSearchResult($str)
	{
		$w = array();
		foreach($this->Query->m_words as $k=>$v)
		{
			$v = ToUpper($v);
			$w[$v] = "KAV";
			if(strpos($v, "\"")!==false)
				$w[str_replace("\"", "&QUOT;", $v)] = "KAV";
		}

		foreach($this->Query->m_stemmed_words as $k=>$v)
			$w[ToUpper($v)]="STEM";

		if($this->Query->bStemming)
		{
			$arStemInfo = stemming_init($this->Query->m_lang);
			$letters = $arStemInfo["letters"];
			$strUpp = stemming_upper($str, $this->Query->m_lang);
		}
		else
		{
			$strUpp = ToUpper($str);
		}

		$arPos = Array();
		foreach($w as $search=>$type)
		{
			$p = strpos($strUpp, $search);

			while($p!==false)
			{
				//Check if we are in the middle of the numeric entity
				if(
					preg_match("/^[0-9]+;/", substr($str, $p)) &&
					preg_match("/^[0-9]+#&/", strrev(substr($str, 0, $p+strlen($search))))
				)
				{
					$p = strpos($strUpp, $search, $p+1);
				}
				elseif($type=="STEM")
				{
					$l = strlen($strUpp);
					for($s=$p; $s>=0 && strpos($letters, substr($strUpp, $s, 1))!==false;$s--){}
					$s++;
					for($e=$p; $e<$l && strpos($letters, substr($strUpp, $e, 1))!==false;$e++){}
					$e--;
					$a = array_keys(stemming(substr($strUpp,$s,$e-$s+1), $this->Query->m_lang));

					foreach($a as $stem)
					{
						if($stem == $search)
						{
							$arPos[] = $p;
							$p = false;
							break;
						}
					}
					if($p !== false)
						$p = strpos($strUpp, $search, $p+1);
				}
				else
				{
					$arPos[] = $p;
					$p=false;
				}
			}
		}

		if(count($arPos)<=0)
		{
			$str_len = strlen($str);
			$pos_end = 500;
			while(($pos_end < $str_len) && (strpos(" ,.\n\r", substr($str, $pos_end, 1)) === false))
				$pos_end++;
			return substr($str, 0, $pos_end).($pos_end < $str_len? "...": "");
		}

		sort($arPos);

		$str_result = "";
		$last_pos = -1;
		$delta = 250/count($arPos);
		$str_len = strlen($str);
		foreach($arPos as $pos_mid)
		{
			//Find where word begins
			$pos_beg = $pos_mid - $delta;
			if($pos_beg <= 0)
				$pos_beg = 0;
			while(($pos_beg > 0) && (strpos(" ,.\n\r", substr($str, $pos_beg, 1)) === false))
				$pos_beg--;

			//Find where word ends
			$pos_end = $pos_mid + $delta;
			if($pos_end > $str_len)
				$pos_end = $str_len;
			while(($pos_end < $str_len) && (strpos(" ,.\n\r", substr($str, $pos_end, 1)) === false))
				$pos_end++;

			if($pos_beg <= $last_pos)
				$arOtr[count($arOtr)-1][1] = $pos_end;
			else
				$arOtr[] = Array($pos_beg, $pos_end);

			$last_pos = $pos_end;
		}

		for($i=0; $i<count($arOtr); $i++)
			$str_result .= ($arOtr[$i][0]<=0?"":" ...").
					substr($str, $arOtr[$i][0], $arOtr[$i][1]-$arOtr[$i][0]).
					($arOtr[$i][1]>=strlen($str)?"":"... ");

		foreach($w as $search=>$type)
			$str_result=$this->repl($search, $type, $str_result);

		$str_result = str_replace("%/^%", "</b>", str_replace("%^%","<b>", $str_result));

		return $str_result;
	}

	function NavStart($nPageSize=0, $bShowAll=true, $iNumPage=false)
	{
		parent::NavStart($nPageSize, $bShowAll, $iNumPage);
		if(COption::GetOptionString("search", "stat_phrase") == "Y")
		{
			$this->Statistic = new CSearchStatistic($this->strQueryText, $this->strTagsText);
			$this->Statistic->PhraseStat($this->NavRecordCount, $this->NavPageNomer);
			if($this->Statistic->phrase_id)
				$this->url_add_params[] = "sphrase_id=".$this->Statistic->phrase_id;
		}
	}

	function Fetch()
	{
		global $DB;
		static $arSite = array();

		$r = parent::Fetch();
		if($r)
		{
			$site_id = $r["SITE_ID"];
			if(!isset($arSite[$site_id]))
			{
				$rsSite = CSite::GetList($b, $o, array("ID"=>$site_id));
				$arSite[$site_id] = $rsSite->Fetch();
			}
			$r["DIR"] = $arSite[$site_id]["DIR"];
			$r["SERVER_NAME"] = $arSite[$site_id]["SERVER_NAME"];

			if(strlen($r["SITE_URL"])>0)
				$r["URL"] = $r["SITE_URL"];

			if(substr($r["URL"], 0, 1)=="=")
			{
				$events = GetModuleEvents("search", "OnSearchGetURL");
				while ($arEvent = $events->Fetch())
					$r["URL"] = ExecuteModuleEventEx($arEvent, array($r));
			}

			$r["URL"] = str_replace(
				array("#LANG#", "#SITE_DIR#", "#SERVER_NAME#"),
				array($r["DIR"], $r["DIR"], $r["SERVER_NAME"]),
				$r["URL"]
			);
			$r["URL"] = preg_replace("'(?<!:)/+'s", "/", $r["URL"]);
			$r["URL_WO_PARAMS"] = $r["URL"];

			$w = $this->Query->m_words;
			if(count($this->url_add_params))
			{
				$p1 = strpos($r["URL"], "?");
				if($p1 === false)
					$ch = "?";
				else
					$ch = "&";

				$p2 = strpos($r["URL"], "#", $p1);
				if($p2===false)
				{
					$r["URL"] = $r["URL"].$ch.implode("&", $this->url_add_params);
				}
				else
				{
					$r["URL"] = substr($r["URL"], 0, $p2).$ch.implode("&", $this->url_add_params).substr($r["URL"], $p2);
				}
			}

			$r["TITLE_FORMATED"] = $this->PrepareSearchResult(htmlspecialcharsex($r["TITLE"]));
			$r["TITLE_FORMATED_TYPE"] = "html";
			$r["TAGS_FORMATED"] = tags_prepare($r["TAGS"], SITE_ID);
			$r["BODY_FORMATED"] = $this->PrepareSearchResult(htmlspecialcharsex($r["BODY"]));
			$r["BODY_FORMATED_TYPE"] = "html";
		}

		return $r;
	}

	function CheckPath($path)
	{
		static $SEARCH_MASKS_CACHE = false;

		if(!is_array($SEARCH_MASKS_CACHE))
		{
			$arSearch = array("\\", ".",  "?", "*",   "'");
			$arReplace = array("/",  "\.", ".", ".*?", "\'");

			$arInc = array();
			$inc = str_replace(
				$arSearch,
				$arReplace,
				COption::GetOptionString("search", "include_mask")
			);
			$arIncTmp = explode(";", $inc);
			foreach($arIncTmp as $mask)
			{
				$mask = trim($mask);
				if(strlen($mask))
					$arInc[] = "'^".$mask."$'";
			}

			$arFullExc = array();
			$arExc = array();
			$exc = str_replace(
				$arSearch,
				$arReplace,
				COption::GetOptionString("search", "exclude_mask")
			);
			$arExcTmp = explode(";", $exc);
			foreach($arExcTmp as $mask)
			{
				$mask = trim($mask);
				if(strlen($mask))
				{
					if(preg_match("#^/[a-z0-9_]+/#i", $mask))
						$arFullExc[] = "'^".$mask."$'";
					else
						$arExc[] = "'^".$mask."$'";
				}
			}

			$SEARCH_MASKS_CACHE = Array(
				"full_exc" => $arFullExc,
				"exc"=>$arExc,
				"inc"=>$arInc
			);
		}

		$file = end(explode('/', $path)); //basename
		if(strncmp($file, ".", 1)==0)
			return 0;

		foreach($SEARCH_MASKS_CACHE["full_exc"] as $mask)
			if(preg_match($mask, $path))
				return false;

		foreach($SEARCH_MASKS_CACHE["exc"] as $mask)
			if(preg_match($mask, $path))
				return 0;

		foreach($SEARCH_MASKS_CACHE["inc"] as $mask)
			if(preg_match($mask, $path))
				return true;

		return 0;
	}

	function GetGroupCached()
	{
		static $SEARCH_CACHED_GROUPS = false;

		if(!is_array($SEARCH_CACHED_GROUPS))
		{
			$SEARCH_CACHED_GROUPS = Array();
			$db_groups = CGroup::GetList($order="ID", $by="ASC");
			while($g = $db_groups->Fetch())
			{
				$group_id = intval($g["ID"]);
				if($group_id > 1)
					$SEARCH_CACHED_GROUPS[$group_id]=$group_id;
			}
		}

		return $SEARCH_CACHED_GROUPS;
	}

	function QueryMnogoSearch(&$xml)
	{
		$SITE = COption::GetOptionString("search", "mnogosearch_url", "www.mnogosearch.org");
		$PATH = COption::GetOptionString("search", "mnogosearch_path", "");
		$PORT = COption::GetOptionString("search", "mnogosearch_port", "80");

		$QUERY_STR = 'document='.urlencode($xml);

		$strRequest = "POST ".$PATH." HTTP/1.0\r\n";
		$strRequest.= "User-Agent: BitrixSM\r\n";
		$strRequest.= "Accept: */*\r\n";
		$strRequest.= "Host: $SITE\r\n";
		$strRequest.= "Accept-Language: en\r\n";
		$strRequest.= "Content-type: application/x-www-form-urlencoded\r\n";
		$strRequest.= "Content-length: ".strlen($QUERY_STR)."\r\n";
		$strRequest.= "\r\n";
		$strRequest.= $QUERY_STR;
		$strRequest.= "\r\n";

		$arAll = "";

		$FP = fsockopen($SITE, $PORT, $errno, $errstr, 120);
		if ($FP)
		{
			fputs($FP, $strRequest);

			while (($line = fgets($FP, 4096)) && $line!="\r\n");
			while ($line = fread($FP, 4096))
				$arAll .= $line;
			fclose($FP);
		}

		return $arAll;
	}

	//////////////////////////////////
	//reindex the whole server content
	//$bFull = true - no not check change_date. all index tables will be truncated
	//       = false - add new ones. update changed and delete deleted.
	function ReIndexAll($bFull = false, $max_execution_time = 0, $NS = Array(), $clear_suggest = false)
	{
		global $APPLICATION;
		$DB = CDatabase::GetModuleConnection('search');

		@set_time_limit(0);
		if(!is_array($NS))
			$NS = Array();
		if($max_execution_time<=0)
		{
			$NS_OLD=$NS;
			$NS=Array("CLEAR"=>"N", "MODULE"=>"", "ID"=>"", "SESS_ID"=>md5(uniqid("")));
			if($NS_OLD["SITE_ID"]!="") $NS["SITE_ID"]=$NS_OLD["SITE_ID"];
			if($NS_OLD["MODULE_ID"]!="") $NS["MODULE_ID"]=$NS_OLD["MODULE_ID"];
		}
		$NS["CNT"] = IntVal($NS["CNT"]);
		if(!$bFull && strlen($NS["SESS_ID"])!=32)
			$NS["SESS_ID"] = md5(uniqid(""));

		$p1 = getmicrotime();

		$DB->StartTransaction();
		CSearch::ReindexLock();

		if($NS["CLEAR"] != "Y")
		{
			if($bFull)
			{
				$db_events = GetModuleEvents("search", "OnBeforeFullReindexClear");
				while($arEvent = $db_events->Fetch())
					ExecuteModuleEventEx($arEvent);

				CSearchTags::CleanCache();
				$DB->Query("TRUNCATE TABLE b_search_content_param", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_content_site", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_content_right", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_content_stem", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_content_title", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_tags", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_content_freq", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_content", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_suggest", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_user_right", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			elseif($clear_suggest)
			{
				$DB->Query("TRUNCATE TABLE b_search_suggest", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_user_right", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("TRUNCATE TABLE b_search_content_freq", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}


		$NS["CLEAR"] = "Y";

		clearstatcache();

		if(
			($NS["MODULE"]=="" || $NS["MODULE"]=="main") &&
			($NS["MODULE_ID"]=="" || $NS["MODULE_ID"]=="main")
		)
		{
			$arLangDirs = Array();
			$arFilter = Array("ACTIVE"=>"Y");
			if($NS["SITE_ID"]!="")
				$arFilter["ID"]=$NS["SITE_ID"];
			$r = CSite::GetList($by="sort", $order="asc", $arFilter);
			while($arR = $r->Fetch())
			{
				$path = rtrim($arR["DIR"], "/");
				$arLangDirs[$arR["ABS_DOC_ROOT"]."/".$path."/"] = $arR;
			}

			//get rid of duplicates
			$dub = Array();
			foreach($arLangDirs as $path=>$arR)
			{
				foreach($arLangDirs as $path2=>$arR2)
				{
					if($path==$path2) continue;
					if(substr($path, 0, strlen($path2)) == $path2)
						$dub[] = $path;
				}
			}

			foreach($dub as $p)
				unset($arLangDirs[$p]);

			foreach($arLangDirs as $arR)
			{
				$site = $arR["ID"];
				$path = rtrim($arR["DIR"], "/");
				$site_path = $site."|".$path."/";

				if(
					$max_execution_time > 0
					&& $NS["MODULE"] == "main"
					&& substr($NS["ID"]."/", 0, strlen($site_path)) != $site_path
				)
					continue;

				//for every folder
				CSearch::RecurseIndex(Array($site, $path), $max_execution_time, $NS);
				if(
					$max_execution_time > 0
					&& strlen($NS["MODULE"]) > 0
				)
				{
					$DB->Commit();
					return $NS;
				}
			}
		}

		$p1 = getmicrotime();

		//for every who wants to reindex
		$oCallBack = new CSearchCallback;
		$oCallBack->max_execution_time = $max_execution_time;
		$db_events = GetModuleEvents("search", "OnReindex");
		while($arEvent = $db_events->Fetch())
		{
			if($NS["MODULE_ID"]!="" && $NS["MODULE_ID"]!=$arEvent["TO_MODULE_ID"]) continue;
			if($max_execution_time>0 && strlen($NS["MODULE"])>0 && $NS["MODULE"]!= "main" && $NS["MODULE"]!=$arEvent["TO_MODULE_ID"]) continue;
			//here we get recordset
			$oCallBack->MODULE = $arEvent["TO_MODULE_ID"];
			$oCallBack->CNT = &$NS["CNT"];
			$oCallBack->SESS_ID = $NS["SESS_ID"];
			$r = &$oCallBack;
			$arResult = ExecuteModuleEventEx($arEvent, array($NS, $r, "Index"));
			if(is_array($arResult)) //old way
			{
				if(count($arResult)>0)
				{
					for($i=0; $i<count($arResult); $i++)
					{
						$arFields = $arResult[$i];
						$ID = $arFields["ID"];
						if(strlen($ID)<=0) continue;
						unset($arFields["ID"]);
						$NS["CNT"]++;
						CSearch::Index($arEvent["TO_MODULE_ID"], $ID, $arFields, false, $NS["SESS_ID"]);
					}
				}
			}
			else  //new method
			{
				if($max_execution_time>0 && $arResult!==false && strlen(".".$arResult)>1)
				{
					$DB->Commit();
					return Array(
						"MODULE"=>$arEvent["TO_MODULE_ID"],
						"CNT"=>$oCallBack->CNT,
						"ID"=>$arResult,
						"CLEAR"=>$NS["CLEAR"],
						"SESS_ID"=>$NS["SESS_ID"],
						"SITE_ID"=>$NS["SITE_ID"],
						"MODULE_ID"=>$NS["MODULE_ID"],
					);
				}
			}
			$NS["MODULE"] = "";
		}

		if(!$bFull)
		{
			CSearch::DeleteOld($NS["SESS_ID"], $NS["MODULE_ID"], $NS["SITE_ID"]);
		}

		$DB->Commit();

		return $NS["CNT"];
	}

	function ReindexModule($MODULE_ID, $bFull=false)
	{
		global $APPLICATION;
		$DB = CDatabase::GetModuleConnection('search');

		if($bFull)
			CSearch::DeleteForReindex($MODULE_ID);

		$NS=Array("CLEAR"=>"N", "MODULE"=>"", "ID"=>"", "SESS_ID"=>md5(uniqid("")));
		//for every who wants to be reindexed
		$db_events = GetModuleEvents("search", "OnReindex");
		while($arEvent = $db_events->Fetch())
		{
			if($arEvent["TO_MODULE_ID"]!=$MODULE_ID) continue;

			$oCallBack = new CSearchCallback;
			$oCallBack->MODULE = $arEvent["TO_MODULE_ID"];
			$oCallBack->CNT = &$NS["CNT"];
			$oCallBack->SESS_ID = $NS["SESS_ID"];
			$r = &$oCallBack;

			$arResult = ExecuteModuleEventEx($arEvent, array($NS, $r, "Index"));
			if(is_array($arResult)) //old way
			{
				if(count($arResult)>0)
				{
					for($i=0; $i<count($arResult); $i++)
					{
						$arFields = $arResult[$i];
						$ID = $arFields["ID"];
						if(strlen($ID)<=0) continue;
						unset($arFields["ID"]);
						$NS["CNT"]++;
						CSearch::Index($arEvent["TO_MODULE_ID"], $ID, $arFields, false, $NS["SESS_ID"]);
					}
				}
			}
			else  //new way
			{
				return Array("MODULE"=>$arEvent["TO_MODULE_ID"], "CNT"=>$oCallBack->CNT, "ID"=>$arResult, "CLEAR"=>$NS["CLEAR"], "SESS_ID"=>$NS["SESS_ID"]);
			}
		}

		if(!$bFull)
			CSearch::DeleteOld($NS["SESS_ID"], $MODULE_ID, $NS["SITE_ID"]);
	}
	//index one item (forum message, news, etc.)
	//combination of ($MODULE_ID, $ITEM_ID) is used to determine the documents
	function Index($MODULE_ID, $ITEM_ID, $arFields, $bOverWrite=false, $SEARCH_SESS_ID="")
	{
		$DB = CDatabase::GetModuleConnection('search');

		$arFields["MODULE_ID"] = $MODULE_ID;
		$arFields["ITEM_ID"] = $ITEM_ID;
		$db_events = GetModuleEvents("search", "BeforeIndex");
		while($arEvent = $db_events->Fetch())
		{
			$arEventResult = ExecuteModuleEventEx($arEvent, array($arFields));
			if(is_array($arEventResult))
				$arFields = $arEventResult;
		}

		unset($arFields["MODULE_ID"]);
		unset($arFields["ITEM_ID"]);

		$bTitle = array_key_exists("TITLE", $arFields);
		if($bTitle)
			$arFields["TITLE"] = trim($arFields["TITLE"]);
		$bBody = array_key_exists("BODY", $arFields);
		if($bBody)
			$arFields["BODY"] = trim($arFields["BODY"]);
		$bTags = array_key_exists("TAGS", $arFields);
		if($bTags)
			$arFields["TAGS"] = trim($arFields["TAGS"]);

		if(!array_key_exists("SITE_ID", $arFields) && array_key_exists("LID", $arFields))
			$arFields["SITE_ID"] = $arFields["LID"];

		if(array_key_exists("SITE_ID", $arFields))
		{
			if(!is_array($arFields["SITE_ID"]))
			{
				$arFields["SITE_ID"] = Array($arFields["SITE_ID"]=>"");
			}
			else
			{
				$bNotAssoc = true;
				$i = 0;
				foreach($arFields["SITE_ID"] as $k=>$val)
				{
					if("".$k!="".$i)
					{
						$bNotAssoc=false;
						break;
					}
					$i++;
				}
				if($bNotAssoc)
				{
					$x = $arFields["SITE_ID"];
					$arFields["SITE_ID"] = Array();
					foreach($x as $val)
						$arFields["SITE_ID"][$val] = "";
				}
			}

			if(count($arFields["SITE_ID"])<=0)
				return 0;

			reset($arFields["SITE_ID"]);
			list($arFields["LID"], $url) = each($arFields["SITE_ID"]);

			$arSites = array();
			foreach($arFields["SITE_ID"] as $site => $url)
			{
				$arSites[] = $DB->ForSQL($site, 2);
			}

			$strSql = "
				SELECT CR.RANK
				FROM b_search_custom_rank CR
				WHERE CR.SITE_ID in ('".implode("', '", $arSites)."')
				AND CR.MODULE_ID='".$DB->ForSQL($MODULE_ID)."'
				".(is_set($arFields, "PARAM1")?"AND (CR.PARAM1 IS NULL OR CR.PARAM1='".$DB->ForSQL($arFields["PARAM1"])."')":"")."
				".(is_set($arFields, "PARAM2")?"AND (CR.PARAM2 IS NULL OR CR.PARAM2='".$DB->ForSQL($arFields["PARAM2"])."')":"")."
				".($ITEM_ID<>""?"AND (CR.ITEM_ID IS NULL OR CR.ITEM_ID='".$DB->ForSQL($ITEM_ID)."')":"")."
				ORDER BY
					PARAM1 DESC, PARAM2 DESC, ITEM_ID DESC
			";
			$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$arFields["CUSTOM_RANK_SQL"]=$strSql;
			if($arResult = $r->Fetch())
				$arFields["CUSTOM_RANK"]=$arResult["RANK"];
		}

		$arGroups = array();
		if(is_set($arFields, "PERMISSIONS"))
		{
			foreach($arFields["PERMISSIONS"] as $group_id)
			{
				if(is_numeric($group_id))
					$arGroups[$group_id] = "G".intval($group_id);
				else
					$arGroups[$group_id] = $group_id;
			}
		}

		$strSqlSelect = "";
		if($bBody) $strSqlSelect .= ",BODY";
		if($bTitle) $strSqlSelect .= ",TITLE";
		if($bTags) $strSqlSelect .= ",TAGS";

		$strSql =
			"SELECT ID, ".CSearch::FormatDateString("DATE_CHANGE")." as DATE_CHANGE
			".$strSqlSelect."
			FROM b_search_content
			WHERE MODULE_ID = '".$DB->ForSQL($MODULE_ID)."'
				AND ITEM_ID = '".$DB->ForSQL($ITEM_ID)."' ";

		$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($arResult = $r->Fetch())
		{
			$ID = $arResult["ID"];

			if($bTitle && $bBody && strlen($arFields["BODY"])<=0 && strlen($arFields["TITLE"])<=0)
			{
				$db_events = GetModuleEvents("search", "OnBeforeIndexDelete");
				while($arEvent = $db_events->Fetch())
					ExecuteModuleEventEx($arEvent, array("SEARCH_CONTENT_ID = ".$ID));

				CSearchTags::CleanCache("", $ID);
				CSearch::CleanFreqCache($ID);
				$DB->Query("DELETE FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("DELETE FROM b_search_content_right WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("DELETE FROM b_search_content_title WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$DB->Query("DELETE FROM b_search_content WHERE ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);

				return 0;
			}

			if(count($arGroups) > 0)
				CAllSearch::SetContentItemGroups($ID, $arGroups);

			if(is_set($arFields, "PARAMS"))
				CAllSearch::SetContentItemParams($ID, $arFields["PARAMS"]);

			if(is_set($arFields, "SITE_ID"))
			{
				$arSITE_ID = $arFields["SITE_ID"];
				$strSql = "
					SELECT SITE_ID, URL
					FROM b_search_content_site
					WHERE SEARCH_CONTENT_ID = ".$ID."
				";
				$rsSite = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				while($arSite = $rsSite->Fetch())
				{
					if(array_key_exists($arSite["SITE_ID"], $arSITE_ID))
					{
						if($arSite["URL"] !== $arSITE_ID[$arSite["SITE_ID"]])
							$strSql = "
								UPDATE b_search_content_site
								SET URL = '".$DB->ForSql($url, 2000)."'
								WHERE SEARCH_CONTENT_ID = ".$ID."
								AND SITE_ID = '".$DB->ForSql($arSite["SITE_ID"])."'
							";
						else
							$strSql = "";

						unset($arSITE_ID[$arSite["SITE_ID"]]);
					}
					else
					{
						$strSql = "
							DELETE FROM b_search_content_site
							WHERE SEARCH_CONTENT_ID = ".$ID."
							AND SITE_ID = '".$DB->ForSql($arSite["SITE_ID"])."'
						";
					}

					if(!empty($strSql))
						$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}

				foreach($arSITE_ID as $site => $url)
				{
					$strSql = "
						INSERT INTO b_search_content_site(SEARCH_CONTENT_ID, SITE_ID, URL)
						VALUES(".$ID.", '".$DB->ForSql($site, 2)."', '".$DB->ForSql($url, 2000)."')
					";
					$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				}
			}

			if(is_set($arFields, "LAST_MODIFIED"))
				$arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["LAST_MODIFIED"], CLang::GetDateFormat(), "DD.MM.YYYY HH:MI:SS");
			if(!$bOverWrite && is_set($arFields, "DATE_CHANGE") && $arFields["DATE_CHANGE"]==$arResult["DATE_CHANGE"])
			{
				if(strlen($SEARCH_SESS_ID)>0)
					$DB->Query("UPDATE b_search_content SET UPD='".$SEARCH_SESS_ID."' WHERE ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				//$DB->Commit();
				return $ID;
			}

			unset($arFields["MODULE_ID"]);
			unset($arFields["ITEM_ID"]);

			if($bBody || $bTitle || $bTags)
			{
				if($bTitle)
					$content = $arFields["TITLE"]."\r\n";
				else
					$content = $arResult["TITLE"]."\r\n";

				if($bBody)
					$content .= $arFields["BODY"]."\r\n";
				else
					$content .= $arResult["BODY"]."\r\n";

				if($bTags)
					$content .= $arFields["TAGS"];
				else
					$content .= $arResult["TAGS"];

				$content = preg_replace("'&#(\d+);'e", "chr(\\1)", $content);
				$arFields["SEARCHABLE_CONTENT"] = CSearch::KillEntities(ToUpper($content));
			}

			if(strlen($SEARCH_SESS_ID)>0)
				$arFields["UPD"] = $SEARCH_SESS_ID;


			$db_events = GetModuleEvents("search", "OnBeforeIndexUpdate");
			while($arEvent = $db_events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			CSearch::Update($ID, $arFields);

			if(is_set($arFields, "SEARCHABLE_CONTENT"))
			{
				CSearch::CleanFreqCache($ID);
				$DB->Query("DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				CSearch::StemIndex($arFields["SITE_ID"], $ID, $arFields["SEARCHABLE_CONTENT"]);
				CSearch::CleanFreqCache($ID);
			}

			if(array_key_exists("TITLE", $arFields))
			{
				$DB->Query("DELETE FROM b_search_content_title WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if(
					!array_key_exists("INDEX_TITLE", $arFields)
					|| $arFields["INDEX_TITLE"] !== false
				)
					CSearch::IndexTitle($arFields["SITE_ID"], $ID, $arFields["TITLE"]);
			}

			if($bTags && ($arResult["TAGS"] != $arFields["TAGS"]))
			{
				CSearchTags::CleanCache("", $ID);
				$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				CSearch::TagsIndex($arFields["SITE_ID"], $ID, $arFields["TAGS"]);
			}
		}
		else
		{
			if($bTitle && $bBody && strlen($arFields["BODY"])<=0 && strlen($arFields["TITLE"])<=0)
			{
				//$DB->Commit();
				return 0;
			}

			$arFields["MODULE_ID"] = $MODULE_ID;
			$arFields["ITEM_ID"] = $ITEM_ID;

			$content = $arFields["TITLE"]."\r\n".$arFields["BODY"]."\r\n".$arFields["TAGS"];
			$content = preg_replace ("'&#(\d+);'e", "chr(\\1)", $content);
			$arFields["SEARCHABLE_CONTENT"] = CSearch::KillEntities(ToUpper($content));

			if($SEARCH_SESS_ID!="")
				$arFields["UPD"] = $SEARCH_SESS_ID;

			$ID = CSearch::Add($arFields);

			$db_events = GetModuleEvents("search", "OnAfterIndexAdd");
			while($arEvent = $db_events->Fetch())
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			CAllSearch::SetContentItemGroups($ID, $arGroups);
			if(is_set($arFields, "PARAMS"))
				CAllSearch::SetContentItemParams($ID, $arFields["PARAMS"]);

			foreach($arFields["SITE_ID"] as $site=>$url)
			{
				$strSql = "
					INSERT INTO b_search_content_site(SEARCH_CONTENT_ID, SITE_ID, URL)
					VALUES(".$ID.", '".$DB->ForSql($site, 2)."', '".$DB->ForSql($url, 2000)."')";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

			CSearch::StemIndex($arFields["SITE_ID"], $ID, $arFields["SEARCHABLE_CONTENT"]);
			CSearch::TagsIndex($arFields["SITE_ID"], $ID, $arFields["TAGS"]);

			if(
				!array_key_exists("INDEX_TITLE", $arFields)
				|| $arFields["INDEX_TITLE"] !== false
			)
				CSearch::IndexTitle($arFields["SITE_ID"], $ID, $arFields["TITLE"]);

			CSearch::CleanFreqCache($ID);
		}
		//$DB->Commit();

		return $ID;
	}

	function KillEntities($str)
	{
		static $arAllEntities = array(
			'UMLYA' => ARRAY('&IQUEST;','&AGRAVE;','&AACUTE;','&ACIRC;','&ATILDE;','&AUML;','&ARING;','&AELIG;','&CCEDIL;','&EGRAVE;','&EACUTE;','&ECIRC;','&EUML;','&IGRAVE;','&IACUTE;','&ICIRC;','&IUML;','&ETH;','&NTILDE;','&OGRAVE;','&OACUTE;','&OCIRC;','&OTILDE;','&OUML;','&TIMES;','&OSLASH;','&UGRAVE;','&UACUTE;','&UCIRC;','&UUML;','&YACUTE;','&THORN;','&SZLIG;','&AGRAVE;','&AACUTE;','&ACIRC;','&ATILDE;','&AUML;','&ARING;','&AELIG;','&CCEDIL;','&EGRAVE;','&EACUTE;','&ECIRC;','&EUML;','&IGRAVE;','&IACUTE;','&ICIRC;','&IUML;','&ETH;','&NTILDE;','&OGRAVE;','&OACUTE;','&OCIRC;','&OTILDE;','&OUML;','&DIVIDE;','&OSLASH;','&UGRAVE;','&UACUTE;','&UCIRC;','&UUML;','&YACUTE;','&THORN;','&YUML;','&OELIG;','&OELIG;','&SCARON;','&SCARON;','&YUML;'),
			'GREEK' => ARRAY('&ALPHA;','&BETA;','&GAMMA;','&DELTA;','&EPSILON;','&ZETA;','&ETA;','&THETA;','&IOTA;','&KAPPA;','&LAMBDA;','&MU;','&NU;','&XI;','&OMICRON;','&PI;','&RHO;','&SIGMA;','&TAU;','&UPSILON;','&PHI;','&CHI;','&PSI;','&OMEGA;','&ALPHA;','&BETA;','&GAMMA;','&DELTA;','&EPSILON;','&ZETA;','&ETA;','&THETA;','&IOTA;','&KAPPA;','&LAMBDA;','&MU;','&NU;','&XI;','&OMICRON;','&PI;','&RHO;','&SIGMAF;','&SIGMA;','&TAU;','&UPSILON;','&PHI;','&CHI;','&PSI;','&OMEGA;','&THETASYM;','&UPSIH;','&PIV;'),
			'OTHER' => ARRAY('&IEXCL;','&CENT;','&POUND;','&CURREN;','&YEN;','&BRVBAR;','&SECT;','&UML;','&COPY;','&ORDF;','&LAQUO;','&NOT;','&REG;','&MACR;','&DEG;','&PLUSMN;','&SUP2;','&SUP3;','&ACUTE;','&MICRO;','&PARA;','&MIDDOT;','&CEDIL;','&SUP1;','&ORDM;','&RAQUO;','&FRAC14;','&FRAC12;','&FRAC34;','&CIRC;','&TILDE;','&ENSP;','&EMSP;','&THINSP;','&ZWNJ;','&ZWJ;','&LRM;','&RLM;','&NDASH;','&MDASH;','&LSQUO;','&RSQUO;','&SBQUO;','&LDQUO;','&RDQUO;','&BDQUO;','&DAGGER;','&DAGGER;','&PERMIL;','&LSAQUO;','&RSAQUO;','&EURO;','&BULL;','&HELLIP;','&PRIME;','&PRIME;','&OLINE;','&FRASL;','&WEIERP;','&IMAGE;','&REAL;','&TRADE;','&ALEFSYM;','&LARR;','&UARR;','&RARR;','&DARR;','&HARR;','&CRARR;','&LARR;','&UARR;','&RARR;','&DARR;','&HARR;','&FORALL;','&PART;','&EXIST;','&EMPTY;','&NABLA;','&ISIN;','&NOTIN;','&NI;','&PROD;','&SUM;','&MINUS;','&LOWAST;','&RADIC;','&PROP;','&INFIN;','&ANG;','&AND;','&OR;','&CAP;','&CUP;','&INT;','&THERE4;','&SIM;','&CONG;','&ASYMP;','&NE;','&EQUIV;','&LE;','&GE;','&SUB;','&SUP;','&NSUB;','&SUBE;','&SUPE;','&OPLUS;','&OTIMES;','&PERP;','&SDOT;','&LCEIL;','&RCEIL;','&LFLOOR;','&RFLOOR;','&LANG;','&RANG;','&LOZ;','&SPADES;','&CLUBS;','&HEARTS;','&DIAMS;'),
		);
		foreach($arAllEntities as $key => $entities)
			$str = str_replace($entities, "", $str);
		return $str;
	}

	function ReindexFile($path, $SEARCH_SESS_ID="")
	{
		global $APPLICATION;
		$DB = CDatabase::GetModuleConnection('search');

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		$site = CSite::GetSiteByFullPath($DOC_ROOT."/".$path);
		if(strlen($site) <= 0)
			return 0;

		$DOC_ROOT = CSite::GetSiteDocRoot($site);
		$abs_file_path = $DOC_ROOT."/".$path;

		if(!file_exists($abs_file_path) || !is_readable($abs_file_path))
			return 0;

		if(!CSearch::CheckPath($path))
			return 0;

		$max_file_size = COption::GetOptionInt("search", "max_file_size", 0);
		if($max_file_size > 0 && filesize($abs_file_path) > $max_file_size*1024)
			return 0;

		if(strlen($SEARCH_SESS_ID) > 0)
		{
			$strSql = "
				SELECT ID
				FROM b_search_content
				WHERE MODULE_ID = 'main'
					AND ITEM_ID = '".$DB->ForSQL($site."|".$path)."'
					AND ".CSearch::FormatDateString("DATE_CHANGE")." = '".date("d.m.Y H:i:s", filemtime($abs_file_path))."'
			";
			$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if($arR = $r->Fetch())
			{
				$strSql = "UPDATE b_search_content SET UPD='".$SEARCH_SESS_ID."' WHERE ID = ".$arR["ID"];
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				return $arR["ID"];
			}
		}

		$arrFile = false;
		$events = GetModuleEvents("search", "OnSearchGetFileContent");
		while($arEvent = $events->Fetch())
		{
			if($arrFile = ExecuteModuleEventEx($arEvent, array($abs_file_path, $SEARCH_SESS_ID)))
				break;
		}
		if(!is_array($arrFile))
		{
			$sFile = $APPLICATION->GetFileContent($abs_file_path);
			$sHeadEndPos = strpos($sFile, "</head>");
			if($sHeadEndPos===false)
				$sHeadEndPos = strpos($sFile, "</HEAD>");
			if($sHeadEndPos!==false)
			{
				//html header detected try to get document charset
				if(preg_match("/<(meta)\s+([^>]*)(content)\s*=\s*(['\"]).*?(charset)\s*=\s*(.*?)(\\4)/is", substr($sFile, 0, $sHeadEndPos), $arMetaMatch))
				{
					$doc_charset = $arMetaMatch[6];
					if(defined("BX_UTF"))
					{
						if(strtoupper($doc_charset) != "UTF-8")
							$sFile = $APPLICATION->ConvertCharset($sFile, $doc_charset, "UTF-8");
					}
				}
			}
			$arrFile = ParseFileContent($sFile);
		}

		$title = CSearch::KillTags(trim($arrFile["TITLE"]));

		if(strlen($title)<=0) return 0;

		//strip out all the tags
		$filesrc = CSearch::KillTags($arrFile["CONTENT"]);

		$arGroups = CSearch::GetGroupCached();
		$arGPerm = Array();
		foreach($arGroups as $group_id)
		{
			$p = $APPLICATION->GetFileAccessPermission(Array($site, $path), Array($group_id));
			if($p >= "R")
			{
				$arGPerm[] = $group_id;
				if($group_id==2) break;
			}
		}

		$tags = COption::GetOptionString("search", "page_tag_property");

		//save to database
		$ID = CSearch::Index("main", $site."|".$path,
			Array(
				"SITE_ID" => $site,
				"DATE_CHANGE" => date("d.m.Y H:i:s", filemtime($abs_file_path)+1),
				"PARAM1" => "",
				"PARAM2" => "",
				"URL" => $path,
				"PERMISSIONS" => $arGPerm,
				"TITLE" => $title,
				"BODY" => $filesrc,
				"TAGS" => array_key_exists($tags, $arrFile["PROPERTIES"])? $arrFile["PROPERTIES"][$tags]: "",
			), false, $SEARCH_SESS_ID
		);

		return $ID;
	}

	function RecurseIndex($path=Array(), $max_execution_time = 0, &$NS)
	{
		global $APPLICATION;
		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);
		$abs_path = $DOC_ROOT.$path;

		if(strlen($site)<=0)
			return 0;

		if(
			!file_exists($abs_path)
			|| !is_dir($abs_path)
			|| !is_readable($abs_path)
		)
			return 0;
		$handle  = @opendir($abs_path);
		while(false !== ($file = @readdir($handle)))
		{
			if($file == "." || $file == "..")
				continue;

			$path_file = $path."/".$file;

			if(is_dir($abs_path."/".$file))
			{
				if($path_file == "/bitrix")
					continue;

				//this is not first step and we had stopped here, so go on to reindex
				if(
					$max_execution_time <= 0
					|| strlen($NS["MODULE"]) <= 0
					|| (
						$NS["MODULE"]=="main"
						&& substr($NS["ID"]."/", 0, strlen($site."|".$path_file."/")) == $site."|".$path_file."/"
					)
				)
				{
					if(CSearch::CheckPath($path_file."/") !== false)
					{
						if(CSearch::RecurseIndex(Array($site, $path_file), $max_execution_time, $NS)===false)
							return false;
					}
				}
				else //all done
				{
					continue;
				}
			}
			else
			{
				//not the first step and we found last file from previos one
				if(
					$max_execution_time > 0
					&& strlen($NS["MODULE"]) > 0
					&& $NS["MODULE"]=="main"
					&& $NS["ID"] == $site."|".$path_file
					)
				{
					$NS["MODULE"] = "";
				}
				elseif(strlen($NS["MODULE"]) <= 0)
				{
					$ID = CSearch::ReindexFile(Array($site, $path_file), $NS["SESS_ID"]);
					if(IntVal($ID)>0)
					{
						$NS["CNT"] = IntVal($NS["CNT"]) + 1;
					}

					if(
						$max_execution_time > 0
						&& (getmicrotime() - START_EXEC_TIME > $max_execution_time)
					)
					{
						$NS["MODULE"] = "main";
						$NS["ID"] = $site."|".$path_file;
						return false;
					}
				}
			}
		}

		return true;
	}

	function KillTags($str)
	{
		while(($p1 = strpos($str, "<?"))!==false)
		{
			$p = $p1 + 2;

			$php_doubleq = false;
			$php_singleq = false;
			$php_comment = false;
			$php_star_comment = false;
			$php_line_comment = false;

			while($p < strlen($str))
			{
				if(substr($str, $p, 2)=="?>" && !$php_doubleq && !$php_singleq && !$php_star_comment)
				{
					$p+=2;
					break;
				}
				elseif(!$php_comment && substr($str, $p, 2)=="//" && !$php_doubleq && !$php_singleq)
				{
					$php_comment = $php_line_comment = true;
					$p++;
				}
				elseif($php_line_comment && (substr($str, $p, 1)=="\n" || substr($str, $p, 1)=="\r"))
				{
					$php_comment = $php_line_comment = false;
				}
				elseif(!$php_comment && substr($str, $p, 2)=="/*" && !$php_doubleq && !$php_singleq)
				{
					$php_comment = $php_star_comment = true;
					$p++;
				}
				elseif($php_star_comment && substr($str, $p, 2)=="*/")
				{
					$php_comment = $php_star_comment = false;
					$p++;
				}
				elseif(!$php_comment)
				{
					if(($php_doubleq || $php_singleq) && substr($str, $p, 2)=="\\\\")
					{
						$p++;
					}
					elseif(!$php_doubleq && substr($str, $p, 1)=='"')
					{
						$php_doubleq=true;
					}
					elseif($php_doubleq && substr($str, $p, 1)=='"' && substr($str, $p-1, 1)!='\\')
					{
						$php_doubleq=false;
					}
					elseif(!$php_doubleq)
					{
						if(!$php_singleq && substr($str, $p, 1)=="'")
						{
							$php_singleq=true;
						}
						elseif($php_singleq && substr($str, $p, 1)=="'" && substr($str, $p-1, 1)!='\\')
						{
							$php_singleq=false;
						}
					}
				}
				$p++;
			}
			$str = substr($str, 0, $p1).substr($str, $p);
		}

		$search = array (
			"'<script[^>]*?>.*?</script>'si",  // Strip out javascript
			"'<style[^>]*?>.*?</style>'si",  // Strip out styles
			"'<select[^>]*?>.*?</select>'si",  // Strip out <select></select>
			"'<head[^>]*?>.*?</head>'si",  // Strip out <head></head>
			"'<tr[^>]*?>'",
			"'<[^>]*?>'",
			"'([\\r\\n])[\\s]+'",  // Strip out white space
			"'&(quot|#34);'i",  // Replace html entities
			"'&(amp|#38);'i",
			"'&(lt|#60);'i",
			"'&(gt|#62);'i",
			"'&(nbsp|#160);'i",
			"'[ ]+ '",
		);

		$replace = array (
			"",
			"",
			"",
			"",
			"\r\n",
			"\r\n",
			"\\1",
			"\"",
			"&",
			"<",
			">",
			" ",
			" ",
		);

		$str = preg_replace ($search, $replace, $str);

		return $str;
	}

	function OnChangeFile($path, $site)
	{
		CSearch::ReindexFile(Array($site, $path));
	}

	function OnGroupDelete($ID)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$DB->Query("
			DELETE FROM b_search_content_right
			WHERE GROUP_CODE = 'G".IntVal($ID)."'
		", false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	function _CallbackPARAMS($field_name, $operation, $field_value)
	{
		global $DB;

		$arSql = array();
		if(is_array($field_value))
		{
			foreach($field_value as $key => $val)
			{
				if(is_array($val))
				{
					foreach($val as $i=>$val2)
						$val[$i] = $DB->ForSQL($val2);
					$where = " in ('".implode("', '", $val)."')";
				}
				else
				{
					$where = " = '".$DB->ForSQL($val)."'";
				}
				$arSql[] = "EXISTS (SELECT * FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$field_name." AND PARAM_NAME = '".$DB->ForSQL($key)."' AND PARAM_VALUE ".$where.")";
			}
		}

		switch($operation)
		{
			case "I":
			case "E":
			case "S":
			case "M":
				if(count($arSql))
					return implode(" AND ", $arSql);
		}
	}

	function __PrepareFilter($arFilter, &$bIncSites, $strSearchContentAlias="sc.")
	{
		$DB = CDatabase::GetModuleConnection('search');
		$arSql = array();
		$arNewFilter = array();
		static $arFilterEvents = false;

		if(!is_array($arFilter))
			$arFilter = array();

		foreach($arFilter as $field=>$val)
		{
			$field = strtoupper($field);
			if(
				is_array($val)
				&& count($val) == 1
				&& $field !== "URL"
				&& $field !== "PARAMS"
			)
				$val = $val[0];
			switch($field)
			{
			case "MODULE_ID":
			case "ITEM_ID":
			case "PARAM1":
			case "PARAM2":
				if($val !== false)
					$arNewFilter["=".$field] = $val;
				break;
			case "CHECK_DATES":
				if($val == "Y")
				{
					$arNewFilter[] = array(
						"LOGIC" => "AND",
						array(
							"LOGIC" => "OR",
							"=DATE_FROM" => false,
							"<=DATE_FROM" => ConvertTimeStamp(false, "FULL"),
						),
						array(
							"LOGIC" => "OR",
							"=DATE_TO" => false,
							">=DATE_TO" => ConvertTimeStamp(false, "FULL"),
						),
					);
				}
				break;
			case "DATE_CHANGE":
				if(strlen($val) > 0)
					$arNewFilter[">=".$field] = $val;
				break;
			case "SITE_ID":
				if($val !== false)
					$arNewFilter["=".$field] = $val;
				break;
			case "URL":
				if(is_array($val))
				{
					$strInc = "";
					foreach($val as $url_i)
					{
						if(strlen($strInc)>0)
							$strInc .= " OR ";
						$strInc .= "(".$strSearchContentAlias."URL LIKE '".$DB->ForSql($url_i)."' OR scsite.URL LIKE '".$DB->ForSql($url_i)."')";
					}
					if($strInc!="")
						$arSql[] = "(".$strInc.")";
					$bIncSites = true;
				}
				elseif($val !== false)
				{
					$arSql[] = "(".$strSearchContentAlias."URL LIKE '".$DB->ForSql($val)."' OR scsite.URL LIKE '".$DB->ForSql($val)."')";
					$bIncSites = true;
				}
				break;
			default:
				if(!is_array($arFilterEvents))
				{
					$arFilterEvents = array();
					$events = GetModuleEvents("search", "OnSearchPrepareFilter");
					while($arEvent = $events->Fetch())
						$arFilterEvents[] = $arEvent;
				}
				//Try to get someone to make the filter sql
				$sql = "";
				foreach($arFilterEvents as $arEvent)
				{
					$sql = ExecuteModuleEventEx($arEvent, array($strSearchContentAlias, $field, $val));
					if(strlen($sql))
					{
						$arSql[] = "(".$sql.")";
						break;
					}
				}

				if(!$sql)
					$arNewFilter[$field] = $val;
			}
		}

		$strSearchContentAlias = rtrim($strSearchContentAlias, ".");
		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields(array(
			"MODULE_ID" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".MODULE_ID",
				"FIELD_TYPE" => "string",
			),
			"ITEM_ID" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".ITEM_ID",
				"FIELD_TYPE" => "string",
			),
			"PARAM1" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".PARAM1",
				"FIELD_TYPE" => "string",
			),
			"PARAM2" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".PARAM2",
				"FIELD_TYPE" => "string",
			),
			"DATE_FROM" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".DATE_FROM",
				"FIELD_TYPE" => "datetime",
			),
			"DATE_TO" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".DATE_TO",
				"FIELD_TYPE" => "datetime",
			),
			"DATE_CHANGE" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".DATE_CHANGE",
				"FIELD_TYPE" => "datetime",
			),
			"SITE_ID" => array(
				"TABLE_ALIAS" => "scsite",
				"FIELD_NAME" => "scsite.SITE_ID",
				"FIELD_TYPE" => "string",
				"JOIN" => true,
			),
			"SITE_URL" => array(
				"TABLE_ALIAS" => "scsite",
				"FIELD_NAME" => "scsite.URL",
				"FIELD_TYPE" => "string",
				"JOIN" => true,
			),
			"URL" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".URL",
				"FIELD_TYPE" => "string",
				"JOIN" => true,
			),
			"PARAMS" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".ID",
				"FIELD_TYPE" => "callback",
				"CALLBACK" => array("CSearch", "_CallbackPARAMS"),
			),
		));

		$strWhere = $obQueryWhere->GetQuery($arNewFilter);

		if(count($arSql) > 0)
		{
			if($strWhere)
				$strWhere .= "\nAND (".implode(" AND ", $arSql).")";
			else
				$strWhere = implode("\nAND ", $arSql);
		}

		$bIncSites = $bIncSites || strlen($obQueryWhere->GetJoins()) > 0;
		return $strWhere;
	}

	function __PrepareSort($aSort=array(), $strSearchContentAlias="sc.", $bTagsCloud = false)
	{
		$arOrder = array();
		if(!is_array($aSort))
			$aSort=array($aSort => "ASC");

		if($bTagsCloud)
		{
			foreach($aSort as $key => $ord)
			{
				$ord = strtoupper($ord) <> "ASC"? "DESC": "ASC";
				$key = strtoupper($key);
				switch($key)
				{
					case "DATE_CHANGE":
						$arOrder[] = "DC_TMP ".$ord;
						break;
					case "NAME":
					case "CNT":
						$arOrder[] = $key." ".$ord;
						break;
				}
			}
			if(count($arOrder) == 0)
			{
				$arOrder[]= "NAME ASC";
			}
		}
		else
		{
			foreach($aSort as $key => $ord)
			{
				$ord = strtoupper($ord) <> "ASC"? "DESC": "ASC";
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
					case "MODULE_ID":
					case "ITEM_ID":
					case "LID":
					case "TITLE":
					case "PARAM1":
					case "PARAM2":
					case "UPD":
					case "DATE_FROM":
					case "DATE_TO":
					case "URL":
					case "RANK":
					case "CUSTOM_RANK":
					case "TITLE_RANK":
						$arOrder[]=$key." ".$ord;
						break;
					case "DATE_CHANGE":
						$arOrder[]=$strSearchContentAlias.$key." ".$ord;
						break;
				}
			}
			if(count($arOrder) == 0)
			{
				$arOrder[]= "CUSTOM_RANK DESC";
				$arOrder[]= "RANK DESC";
				$arOrder[]= $strSearchContentAlias."DATE_CHANGE DESC";
			}
		}

		return " ORDER BY ".implode(", ",$arOrder);
	}

	function Add($arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');
		if(is_set($arFields, "LAST_MODIFIED"))
			$arFields["DATE_CHANGE"] = $arFields["LAST_MODIFIED"];
		elseif(is_set($arFields, "DATE_CHANGE"))
			$arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["DATE_CHANGE"], "DD.MM.YYYY HH.MI.SS", CLang::GetDateFormat());
		return $DB->Add("b_search_content", $arFields, array("BODY", "TAGS", "SEARCHABLE_CONTENT"));
	}

	function OnChangeFilePermissions($path, $permission = array(), $old_permission = array(), $arGroups = false)
	{

		global $APPLICATION;
		$DB = CDatabase::GetModuleConnection('search');

		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);
		$path=rtrim($path, "/");

		if(!is_array($arGroups))
		{
			$arGroups = CSearch::GetGroupCached();
			//Check if anonymous permission was changed
			if(!array_key_exists(2, $permission) && array_key_exists("*", $permission))
				$permission[2] = $permission["*"];
			if(!is_array($old_permission))
				$old_permission = array();
			if(!array_key_exists(2, $old_permission) && array_key_exists("*", $old_permission))
				$old_permission[2] = $old_permission["*"];
			//And if not when will do nothing
			if(
				(array_key_exists(2, $permission)
				&& $permission[2] >= "R")
				&& array_key_exists(2, $old_permission)
				&& $old_permission[2] >= "R"
			)
			{
				return;
			}
		}

		if(file_exists($DOC_ROOT.$path))
		{
			@set_time_limit(300);
			if(is_dir($DOC_ROOT.$path))
			{
				$handle = @opendir($DOC_ROOT.$path);
				while(false !== ($file = @readdir($handle)))
				{
					if($file == "." || $file == "..")
						continue;

					$full_file = $path."/".$file;
					if($full_file == "/bitrix")
						continue;

					if(is_dir($DOC_ROOT.$full_file) || CSearch::CheckPath($full_file))
						CSearch::OnChangeFilePermissions(array($site, $full_file), array(), array(), $arGroups);
				}
			}
			else//if(is_dir($DOC_ROOT.$path))
			{
				$rs = $DB->Query("
					SELECT SC.ID
					FROM b_search_content SC
					WHERE MODULE_ID='main'
					AND ITEM_ID='".$DB->ForSql($site."|".$path)."'
				", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if($ar = $rs->Fetch())
				{
					$arNewGroups = array();
					foreach($arGroups as $group_id)
					{
						$p = $APPLICATION->GetFileAccessPermission(array($site, $path), array($group_id));
						if($p >= "R")
						{
							$arNewGroups[$group_id] = 'G'.$group_id;
							if($group_id == 2)
								break;
						}
					}
					CAllSearch::SetContentItemGroups($ar["ID"], $arNewGroups);
				}
			} //if(is_dir($DOC_ROOT.$path))
		}//if(file_exists($DOC_ROOT.$path))
	}


	function SetContentItemGroups($index_id, $arGroups)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$index_id = intval($index_id);

		$arToInsert = array();
		foreach($arGroups as $group_code)
			if(strlen($group_code))
				$arToInsert[$group_code] = $group_code;

		//Read database
		$rs = $DB->Query("
			SELECT * FROM b_search_content_right
			WHERE SEARCH_CONTENT_ID = ".$index_id."
		", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($ar = $rs->Fetch())
		{
			$group_code = $ar["GROUP_CODE"];
			if(isset($arToInsert[$group_code]))
				unset($arToInsert[$group_code]); //This already in DB
			else
				$DB->Query("
					DELETE FROM b_search_content_right
					WHERE
					SEARCH_CONTENT_ID = ".$index_id."
					AND GROUP_CODE = '".$DB->ForSQL($group_code)."'
				", false, "File: ".__FILE__."<br>Line: ".__LINE__); //And this should be deleted
		}

		foreach($arToInsert as $group_code)
		{
			$DB->Query("
				INSERT INTO b_search_content_right
				(SEARCH_CONTENT_ID, GROUP_CODE)
				VALUES
				(".$index_id.", '".$DB->ForSQL($group_code, 100)."')
			", true, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
	}

	function CheckPermissions($FIELD = "sc.ID")
	{
		global $USER;

		$arResult = array();

		if($USER->IsAdmin())
		{
			$arResult[] = "1=1";
		}
		else
		{
			if($USER->GetID() > 0)
			{
				CSearchUser::CheckCurrentUserGroups();
				$arResult[] = "
					EXISTS (
						SELECT 1
						FROM b_search_content_right scg
						WHERE ".$FIELD." = scg.SEARCH_CONTENT_ID
						AND scg.GROUP_CODE IN (
							SELECT GROUP_CODE FROM b_search_user_right
							WHERE USER_ID = ".$USER->GetID()."
						)
					)";
			}
			else
			{
				$arResult[] = "
					EXISTS (
						SELECT 1
						FROM b_search_content_right scg
						WHERE ".$FIELD." = scg.SEARCH_CONTENT_ID
						AND scg.GROUP_CODE = 'G2'
					)";
			}
		}
		return "((".implode(") OR (", $arResult)."))";
	}

	function SetContentItemParams($index_id, $arParams)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$index_id = intval($index_id);

		$DB->Query("
			DELETE FROM b_search_content_param
			WHERE
			SEARCH_CONTENT_ID = ".$index_id."
		", false, "File: ".__FILE__."<br>Line: ".__LINE__); //And this should be deleted

		if(is_array($arParams))
		{
			foreach($arParams as $k1 => $v1)
			{
				$name = trim($k1);
				if(strlen($name))
				{
					if(!is_array($v1))
						$v1 = array($v1);

					foreach($v1 as $v2)
					{
						$value = trim($v2);
						if(strlen($value))
						{
							$DB->Query("
								INSERT INTO b_search_content_param
								(SEARCH_CONTENT_ID, PARAM_NAME, PARAM_VALUE)
								VALUES
								(".$index_id.", '".$DB->ForSQL($name, 100)."', '".$DB->ForSQL($value, 100)."')
							", true, "File: ".__FILE__."<br>Line: ".__LINE__);
						}
					}
				}
			}
		}
	}
}

class CAllSearchQuery
{
	var $m_query;
	var $m_words;
	var $m_stemmed_words;
	var $m_fields;
	var $m_kav;
	var $default_query_type;
	var $rus_bool_lang;
	var $m_casematch;
	var $error;
	var $bTagsSearch = false;
	var $m_tags_words;
	var $bStemming = false;

	function __construct($default_query_type = "and", $rus_bool_lang = "yes", $m_casematch = 0, $site_id = "")
	{
		return $this->CSearchQuery($default_query_type, $rus_bool_lang, $m_casematch, $site_id);
	}

	function CSearchQuery($default_query_type = "and", $rus_bool_lang = "yes", $m_casematch = 0, $site_id = "")
	{
		$this->m_query  = "";
		$this->m_stemmed_words = array();
		$this->m_tags_words = array();
		$this->m_fields = "";
		$this->default_query_type = $default_query_type;
		$this->rus_bool_lang = $rus_bool_lang;
		$this->m_casematch = $m_casematch;
		$this->m_kav = array();
		$this->error = "";

		$db_site_tmp = CSite::GetByID($site_id);
		if ($ar_site_tmp = $db_site_tmp->Fetch())
			$this->m_lang=$ar_site_tmp["LANGUAGE_ID"];
		else
			$this->m_lang="en";
	}

	function GetQueryString($fields, $query, $bTagsSearch = false, $bUseStemming = true)
	{
		$this->m_words = Array();
		$this->m_fields = explode(",", $fields);
		if(!is_array($this->m_fields))
			$this->m_fields=array($this->m_fields);

		$this->bTagsSearch = $bTagsSearch;
		//In case there is no masks used we'll keep list
		//of all tags in this memeber
		//to perform optimization
		$this->m_tags_words = array();

		$query = $this->CutKav($query);

		//Assume query does not have any word which can be stemmed
		$this->bStemming = false;
		if(!$this->bTagsSearch && $bUseStemming && COption::GetOptionString("search", "use_stemming", "N")=="Y")
		{
			//In case when at least one word found: $this->bStemming = true
			$stem_query = $this->StemQuery($query, $this->m_lang);
			if($this->bStemming === true)
				$query = $stem_query;
		}
		$query = $this->ParseQ($query);

		if($query == "( )" || strlen($query)<=0)
		{
			$this->error=GetMessage("SEARCH_ERROR3");
			$this->errorno=3;
			return false;
		}

		$query = $this->PrepareQuery($query);

		return $query;
	}

	function CutKav($query)
	{
		if(preg_match_all("/([\"'])(.*?)(?<!\\\\)(\\1)/s", $query, $arQuotes))
		{
			foreach($arQuotes[2] as $i => $quoted)
			{
				$quoted = trim($quoted);
				if(strlen($quoted))
				{
					$repl = $i."cut5";
					$this->m_kav[$repl] = str_replace("\\\"", "\"", $quoted);
					$query = str_replace($arQuotes[0][$i], " ".$repl." ", $query);
				}
				else
				{
					$query = str_replace($arQuotes[0][$i], " ", $query);
				}

				if($i > 100) break;
			}
		}
		return $query;
	}

	function ParseQ($q)
	{
		$q = trim($q);
		if(strlen($q) <= 0)
			return '';

		$q = $this->ParseStr($q);

		$q = str_replace(
			array("&"   , "|"   , "~"  , "("  , ")"),
			array(" && ", " || ", " ! ", " ( ", " ) "),
			$q
		);
		$q = "( $q )";
		$q = preg_replace("/\\s+/".BX_UTF_PCRE_MODIFIER, " ", $q);

		return $q;
	}

	function ParseStr($qwe)
	{
		//Take alphabet into account
		$arStemInfo = stemming_init($this->m_lang);
		$letters = $arStemInfo["pcre_letters"]."|+&~()";

		//This chars will be whiped out
		$white_space = '*%!@#$^_={};\':<>?,.[]"\\/';
		//Take off alphabet chars from delimiters
		$white_space = preg_replace("/[".$letters."]+/".BX_UTF_PCRE_MODIFIER, "", $white_space);
		//Escape special chars
		$white_space = str_replace(
			array("\\"  , "-"  , "^"  , "]"  , "/"  ),
			array("\\\\", "\\-", "\\^", "\\]", "\\/"),
			$white_space
		);

		//Erase delimiters from the query
		$qwe = trim(preg_replace("/[".$white_space."]+/".BX_UTF_PCRE_MODIFIER, " ", $qwe));

		// query language normalizer
		if ($this->rus_bool_lang == 'yes')
		{
			$qwe=preg_replace("/(\s*\|\s*|\s+or\s+|\s+".GetMessage("SEARCH_TERM_OR")."\s+)/is".BX_UTF_PCRE_MODIFIER, "|", $qwe);
			$qwe=preg_replace("/(\s*\+\s*|\s*\&\s*|\s+and\s+|\s+".GetMessage("SEARCH_TERM_AND")."\s+)/is".BX_UTF_PCRE_MODIFIER, "&", $qwe);
			$qwe=preg_replace("/(\s*\~\s*|\s+not\s+|\s+without\s+|\s+".GetMessage("SEARCH_TERM_NOT_1")."\s+|\s+".GetMessage("SEARCH_TERM_NOT_2")."\s+)/is".BX_UTF_PCRE_MODIFIER, "~", $qwe);
		}
		else
		{
			$qwe=preg_replace("/(\s*\|\s*|\s+or\s+)/is".BX_UTF_PCRE_MODIFIER, "|", $qwe);
			$qwe=preg_replace("/(\s*\+\s*|\s*\&\s*|\s+and\s+)/is".BX_UTF_PCRE_MODIFIER, "&", $qwe);
			$qwe=preg_replace("/(\s*\~\s*|\s+not\s+|\s+without\s+)/is".BX_UTF_PCRE_MODIFIER, "~", $qwe);
		}

		$qwe=preg_replace("/\s*([()])\s*/s".BX_UTF_PCRE_MODIFIER,"\\1",$qwe);

		// default query type is and
		if(strtolower($this->default_query_type) == 'or')
			$default_op = "|";
		else
			$default_op = "&";

		$qwe=preg_replace("/(\s+|\&\|+|\|\&+)/s".BX_UTF_PCRE_MODIFIER, $default_op, $qwe);

		// remove unnesessary boolean operators
		$qwe=preg_replace("/\|+/", "|", $qwe);
		$qwe=preg_replace("/&+/", "&", $qwe);
		$qwe=preg_replace("/~+/", "~", $qwe);
		$qwe=preg_replace("/\|\&\|/", "&", $qwe);
		$qwe=preg_replace("/[\|\&\~]+$/", "", $qwe);
		$qwe=preg_replace("/^[\|\&]+/", "", $qwe);

		// transform "w1 ~w2" -> "w1 default_op ~ w2"
		// ") ~w" -> ") default_op ~w"
		// "w ~ (" -> "w default_op ~("
		// ") w" -> ") default_op w"
		// "w (" -> "w default_op ("
		// ")(" -> ") default_op ("

		$qwe=preg_replace("/([^\&\~\|\(\)]+)~([^\&\~\|\(\)]+)/s".BX_UTF_PCRE_MODIFIER,"\\1".$default_op."~\\2", $qwe);
		$qwe=preg_replace("/\)~{1,}/s".BX_UTF_PCRE_MODIFIER,")".$default_op."~", $qwe);
		$qwe=preg_replace("/~{1,}\(/s".BX_UTF_PCRE_MODIFIER, ($default_op=="|"? "~|(": "&~("), $qwe);
		$qwe=preg_replace("/\)([^\&\~\|\(\)]+)/s".BX_UTF_PCRE_MODIFIER, ")".$default_op."\\1", $qwe);
		$qwe=preg_replace("/([^\&\~\|\(\)]+)\(/s".BX_UTF_PCRE_MODIFIER, "\\1".$default_op."(", $qwe);
		$qwe=preg_replace("/\) *\(/s".BX_UTF_PCRE_MODIFIER, ")".$default_op."(", $qwe);

		// remove unnesessary boolean operators
		$qwe=preg_replace("/\|+/", "|", $qwe);
		$qwe=preg_replace("/&+/", "&", $qwe);

		// remove errornous format of query - ie: '(&', '&)', '(|', '|)', '~&', '~|', '~)'
		$qwe=preg_replace("/\(\&{1,}/s", "(", $qwe);
		$qwe=preg_replace("/\&{1,}\)/s", ")", $qwe);
		$qwe=preg_replace("/\~{1,}\)/s", ")", $qwe);
		$qwe=preg_replace("/\(\|{1,}/s", "(", $qwe);
		$qwe=preg_replace("/\|{1,}\)/s", ")", $qwe);
		$qwe=preg_replace("/\~{1,}\&{1,}/s", "&", $qwe);
		$qwe=preg_replace("/\~{1,}\|{1,}/s", "|", $qwe);

		$qwe=preg_replace("/\(\)/s", "", $qwe);
		$qwe=preg_replace("/^[\|\&]{1,}/s", "", $qwe);
		$qwe=preg_replace("/[\|\&\~]{1,}$/s", "", $qwe);
		$qwe=preg_replace("/\|\&/s", "&", $qwe);
		$qwe=preg_replace("/\&\|/s", "|", $qwe);

		return $qwe;
	}

	function StemWord($w)
	{
		static $preg_ru = false;
		$wu = ToUpper($w);
		if(preg_match("/^(OR|AND|NOT|WITHOUT)$/", $wu))
		{
			return $w;
		}
		elseif($this->rus_bool_lang == 'yes')
		{
			if($preg_ru === false)
				$preg_ru = "/^(".ToUpper(GetMessage("SEARCH_TERM_OR")."|".GetMessage("SEARCH_TERM_AND")."|".GetMessage("SEARCH_TERM_NOT_1")."|".GetMessage("SEARCH_TERM_NOT_2")).")$/".BX_UTF_PCRE_MODIFIER;
			if(preg_match($preg_ru, $wu))
				return $w;
		}
		if(preg_match("/cut[56]/i", $w))
			return $w;
		$arrStem = array_keys(stemming($w, $this->m_lang));
		if(count($arrStem) < 1)
			return " ";
		else
		{
			$this->bStemming = true;
			return $arrStem[0];
		}
	}

	function StemQuery($q, $lang="en")
	{
		$arStemInfo = stemming_init($lang);
		return preg_replace("/([".$arStemInfo["pcre_letters"]."]+)/e".BX_UTF_PCRE_MODIFIER, "CAllSearchQuery::StemWord('\$1')", $q);
	}

	function PrepareQuery($q)
	{
		$state = 0;
		$qu = "";
		$n = 0;
		$this->error = "";

		$t=strtok($q," ");

		while (($t!="") && ($this->error==""))
		{
			switch ($state)
			{
			case 0:
				if (($t=="||") || ($t=="&&") || ($t==")"))
				{
					$this->error=GetMessage("SEARCH_ERROR2")." ".$t;
					$this->errorno=2;
				}
				elseif ($t=="!")
				{
					$state=0;
					$qu="$qu NOT ";
					break;
				}
				elseif ($t=="(")
				{
					$n++;
					$state=0;
					$qu="$qu(";
				}
				else
				{
					$state=1;
					$qu="$qu ".$this->BuildWhereClause($t)." ";
				}
				break;

			case 1:
				if (($t=="||") || ($t=="&&"))
				{
					$state=0;
					if($t=='||') $qu="$qu OR ";
					else $qu="$qu AND ";
				}
				elseif ($t==")")
				{
					$n--;
					$state=1;
					$qu="$qu)";
				}
				else
				{
					$this->error=GetMessage("SEARCH_ERROR2")." ".$t;
					$this->errorno=2;
				}
				break;
			}
			$t=strtok(" ");
		}

		if (($this->error=="") && ($n != 0))
		{
			$this->error=GetMessage("SEARCH_ERROR1");
			$this->errorno=1;
		}
		if ($this->error!="") return 0;

		return $qu;
	}
}

class CSearchCallback
{
	var $MODULE="";
	var $max_execution_time=0;
	var $CNT=0;
	var $SESS_ID = "";
	function Index($arFields)
	{
		$ID = $arFields["ID"];
		if($ID=="")
			return true;
		unset($arFields["ID"]);
		CSearch::Index($this->MODULE, $ID, $arFields, false, $this->SESS_ID);
		$this->CNT = $this->CNT+1;
		if($this->max_execution_time>0 && getmicrotime() - START_EXEC_TIME > $this->max_execution_time)
			return false;
		else
			return true;
	}
}
?>