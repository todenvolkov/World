<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/classes/general/search.php");

class CSearch extends CAllSearch
{
	function MakeSQL($query, $strSqlWhere, $strSort, $bIncSites, $bStem)
	{
		global $USER;
		$DB = CDatabase::GetModuleConnection('search');
		if(strpos($strSort, "TITLE_RANK") !== false)
		{
			if($bStem)
			{
				$strSelect = "";
				foreach($this->Query->m_stemmed_words as $stem)
				{
					if(strlen($strSelect) > 0)
						$strSelect .= " + ";
					$strSelect .= "if(locate('".$stem."', upper(sc.TITLE)) > 0, 1, 0)";
				}
				$strSelect = ",".$strSelect." TITLE_RANK\n";
			}
			else
			{
				$strSelect = "";
				foreach($this->Query->m_words as $word)
				{
					if(strlen($strSelect) > 0)
						$strSelect .= " + ";
					$strSelect .= "if(locate('".$DB->ForSql(ToUpper($word))."', upper(sc.TITLE)) > 0, 1, 0)";
				}
				$strSelect = ",".$strSelect." TITLE_RANK\n";
			}
		}
		else
		{
			$strSelect = "";
		}

		if($bIncSites && $bStem)
		{
			if(strpos($query, "sc.")===false)
			{
				$strSqlWhere = preg_replace('# AND st.TF >= [0-9\.,]+#i', "", $strSqlWhere);

				$strSql = "
				SELECT
					sc.ID
					,sc.MODULE_ID
					,sc.ITEM_ID
					,sc.LID
					,sc.TITLE
					,sc.TAGS
					,sc.BODY
					,sc.SEARCHABLE_CONTENT
					,sc.PARAM1
					,sc.PARAM2
					,sc.UPD
					,sc.DATE_FROM
					,sc.DATE_TO
					,sc.URL as URL
					,stt.TF as RANK
					,scsite.URL as SITE_URL
					,scsite.SITE_ID
					,".$DB->DateToCharFunction("sc.DATE_CHANGE")." as FULL_DATE_CHANGE
					,".$DB->DateToCharFunction("sc.DATE_CHANGE", "SHORT")." as DATE_CHANGE
					".$strSelect."
				FROM b_search_content sc
					INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
					INNER JOIN ".(count($this->Query->m_stemmed_words) > 1?
						"(
							select search_content_id, max(st.TF) TF, sum(st.TF/sf.FREQ) as RANK
							from b_search_content_stem st, b_search_content_freq sf
							where st.language_id = '".$this->Query->m_lang."'
							and st.stem = sf.stem
							and sf.language_id = st.language_id
							and st.stem in ('".implode("' ,'", $this->Query->m_stemmed_words)."')
							".($this->tf_hwm > 0? "and st.TF >= ".number_format($this->tf_hwm, 2, ".", ""): "")."
							".(strlen($this->tf_hwm_site_id) > 0? "and sf.SITE_ID = '".$DB->ForSQL($this->tf_hwm_site_id, 2)."'": "and sf.SITE_ID IS NULL")."
							group by st.search_content_id
							having (".$query.")
						)"
					:
						"(
							select st0.search_content_id, st0.TF, st0.TF RANK
							from b_search_content_stem st0
							where st0.language_id = '".$this->Query->m_lang."'
							and st0.stem in ('".implode("' ,'", $this->Query->m_stemmed_words)."')
							".($this->tf_hwm > 0? "and st0.TF >= ".number_format($this->tf_hwm, 2, ".", ""): "")."
						)"
					)." stt ON sc.id = stt.search_content_id
				WHERE
				".CSearch::CheckPermissions("sc.ID")."
				".$strSqlWhere."
				".$strSort."
				";
			}
			else
			{
				$strSql = "
				SELECT
					sc.ID
					,sc.MODULE_ID
					,sc.ITEM_ID
					,sc.LID
					,sc.TITLE
					,sc.TAGS
					,sc.BODY
					,sc.SEARCHABLE_CONTENT
					,sc.PARAM1
					,sc.PARAM2
					,sc.UPD
					,sc.DATE_FROM
					,sc.DATE_TO
					,sc.URL as URL
					,".(count($this->Query->m_stemmed_words)>1?"sum(st.TF/sf.FREQ)":"st.TF")."  as RANK
					,scsite.URL as SITE_URL
					,scsite.SITE_ID
					,".$DB->DateToCharFunction("sc.DATE_CHANGE")." as FULL_DATE_CHANGE
					,".$DB->DateToCharFunction("sc.DATE_CHANGE", "SHORT")." as DATE_CHANGE
					".$strSelect."
				FROM b_search_content sc
					INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
					INNER JOIN b_search_content_stem st ON sc.id = st.search_content_id+0
					".(count($this->Query->m_stemmed_words)>1?
						"INNER JOIN b_search_content_freq sf ON
							st.language_id = sf.language_id
							and st.stem=sf.stem
							".(strlen($this->tf_hwm_site_id) > 0?
								"and sf.SITE_ID = '".$DB->ForSQL($this->tf_hwm_site_id, 2)."'":
								"and sf.SITE_ID IS NULL"
							):
						""
					)."
				WHERE
					".CSearch::CheckPermissions("sc.ID")."
					AND st.STEM in ('".implode("','", $this->Query->m_stemmed_words)."')
					".(count($this->Query->m_stemmed_words)>1? "AND sf.STEM in ('".implode("','", $this->Query->m_stemmed_words)."')": "")."
					AND st.language_id='".$this->Query->m_lang."'
					".$strSqlWhere."
				GROUP BY
					sc.ID
					,scsite.URL
					,scsite.SITE_ID
				HAVING
					(".$query.")
				".$strSort."
				";
			}
		}
		elseif($bIncSites && !$bStem)
			$strSql = "
			SELECT DISTINCT
				sc.ID
				,sc.MODULE_ID
				,sc.ITEM_ID
				,sc.LID
				,sc.TITLE
				,sc.TAGS
				,sc.BODY
				,sc.SEARCHABLE_CONTENT
				,sc.PARAM1
				,sc.PARAM2
				,sc.UPD
				,sc.DATE_FROM
				,sc.DATE_TO
				,sc.URL as URL
				,1 as RANK
				,scsite.URL as SITE_URL
				,scsite.SITE_ID
				,".$DB->DateToCharFunction("sc.DATE_CHANGE")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("sc.DATE_CHANGE", "SHORT")." as DATE_CHANGE
				".$strSelect."
			FROM b_search_content sc
				INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
				".($this->Query->bTagsSearch? "
				INNER JOIN b_search_tags stags ON (sc.ID = stags.SEARCH_CONTENT_ID)
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				".$strSqlWhere."
                		".(is_array($this->Query->m_tags_words) && count($this->Query->m_tags_words)>0? "AND stags.NAME in ('".implode("','", $this->Query->m_tags_words)."')": "")."
			GROUP BY
				sc.ID
				,scsite.URL
				,scsite.SITE_ID
			HAVING
				(".$query.")": "
			WHERE
				".CSearch::CheckPermissions("sc.ID")." AND (".$query.") ".$strSqlWhere."")."
			".$strSort."
			";
		elseif(!$bIncSites && $bStem)
			$strSql = "
			SELECT
				sc.ID
				,sc.MODULE_ID
				,sc.ITEM_ID
				,sc.LID
				,sc.TITLE
				,sc.TAGS
				,sc.BODY
				,sc.SEARCHABLE_CONTENT
				,sc.PARAM1
				,sc.PARAM2
				,sc.UPD
				,sc.DATE_FROM
				,sc.DATE_TO
				,sc.LID as SITE_ID
				,sc.URL as URL
				".(count($this->Query->m_stemmed_words)>1?",sum(st.TF/sf.FREQ)":",st.TF")."  as RANK
				,".$DB->DateToCharFunction("sc.DATE_CHANGE")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("sc.DATE_CHANGE", "SHORT")." as DATE_CHANGE
				".$strSelect."
			FROM b_search_content sc
				INNER JOIN b_search_content_stem st ON sc.id = st.search_content_id
				".(count($this->Query->m_stemmed_words)>1?
					"INNER JOIN b_search_content_freq sf ON
						st.language_id = sf.language_id
						and st.stem=sf.stem
						".(strlen($this->tf_hwm_site_id) > 0?
							"and sf.SITE_ID = '".$DB->ForSQL($this->tf_hwm_site_id, 2)."'":
							"and sf.SITE_ID IS NULL"
						):
					""
				)."
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND st.STEM in ('".implode("','", $this->Query->m_stemmed_words)."')
				".(count($this->Query->m_stemmed_words)>1? "AND sf.STEM in ('".implode("','", $this->Query->m_stemmed_words)."')": "")."
				AND st.language_id='".$this->Query->m_lang."'
				".$strSqlWhere."
			".(count($this->Query->m_stemmed_words)>1?"
			GROUP BY
				sc.ID
			HAVING
				(".$query.") ": "")."
			".$strSort."
			";
		else //if(!$bIncSites && !$bStem)
			$strSql = "
			SELECT DISTINCT
				sc.ID
				,sc.MODULE_ID
				,sc.ITEM_ID
				,sc.LID
				,sc.TITLE
				,sc.TAGS
				,sc.BODY
				,sc.SEARCHABLE_CONTENT
				,sc.PARAM1
				,sc.PARAM2
				,sc.UPD
				,sc.DATE_FROM
				,sc.DATE_TO
				,sc.LID as SITE_ID
				,sc.URL as URL
				,1 as RANK
				,".$DB->DateToCharFunction("sc.DATE_CHANGE")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("sc.DATE_CHANGE", "SHORT")." as DATE_CHANGE
				".$strSelect."
			FROM b_search_content sc
				".($this->Query->bTagsSearch? "INNER JOIN b_search_tags stags ON (sc.ID = stags.SEARCH_CONTENT_ID)
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				".$strSqlWhere."
                		".(is_array($this->Query->m_tags_words) && count($this->Query->m_tags_words)>0? "AND stags.NAME in ('".implode("','", $this->Query->m_tags_words)."')": "")."
			GROUP BY
				sc.ID
			HAVING
				(".$query.")" :
			" WHERE
				(".$query.")
				".$strSqlWhere."
			")."
			".$strSort."
			";

		$limit = COption::GetOptionInt("search", "max_result_size");
		if($limit<1)
			$limit=500;

		return $strSql."LIMIT ".$limit;
	}

	function tagsMakeSQL($query, $strSqlWhere, $strSort, $bIncSites, $bStem, $limit = 100)
	{
		global $USER;
		$DB = CDatabase::GetModuleConnection('search');
		$limit = intVal($limit);
		if($bStem && count($this->Query->m_stemmed_words)>1)
		{//We have to make some magic in case quotes was used in query
		//We have to move (sc.searchable_content LIKE '%".ToUpper($word)."%') from $query to $strSqlWhere
			while(preg_match("/(AND\s+\(sc.searchable_content LIKE \'\%.+?\%\'\))/", $query, $arMatches))
			{
				$strSqlWhere .= $arMatches[0];
				$query = str_replace($arMatches[0], "", $query);
			}
		}
		if($bIncSites && $bStem)
			$strSql = "
			SELECT
				stags.NAME
				,COUNT(DISTINCT stags.SEARCH_CONTENT_ID) as CNT
				,MAX(sc.DATE_CHANGE) DC_TMP
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)", "SHORT")." as DATE_CHANGE
				".(count($this->Query->m_stemmed_words)>1 && strpos($query, "searchable_content")!==false?"
					,sc.SEARCHABLE_CONTENT
				": "")."
			FROM b_search_tags stags
				INNER JOIN b_search_content sc ON (stags.SEARCH_CONTENT_ID=sc.ID)
				INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
				INNER JOIN b_search_content_stem st ON sc.id = st.search_content_id
				".(count($this->Query->m_stemmed_words)>1?
					"INNER JOIN b_search_content_freq sf ON
						st.language_id = sf.language_id
						and st.stem=sf.stem
						".(strlen($this->tf_hwm_site_id) > 0?
							"and sf.SITE_ID = '".$DB->ForSQL($this->tf_hwm_site_id, 2)."'":
							"and sf.SITE_ID IS NULL"
						):
					""
				)."
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND st.STEM in ('".implode("','", $this->Query->m_stemmed_words)."')
				".(count($this->Query->m_stemmed_words)>1? "AND sf.STEM in ('".implode("','", $this->Query->m_stemmed_words)."')": "")."
				AND st.language_id='".$this->Query->m_lang."'
				AND stags.SITE_ID = scsite.SITE_ID
				".$strSqlWhere."
			GROUP BY
				stags.NAME
			".((count($this->Query->m_stemmed_words)>1)?"
			HAVING
				(".$query.") ": "")."
			".$strSort."
			";
		elseif($bIncSites && !$bStem)
		{
			if($query == "1=1")
			{
			$strSql = "
			SELECT
				stags2.NAME
				,COUNT(DISTINCT stags2.SEARCH_CONTENT_ID) as CNT
				,MAX(sc.DATE_CHANGE) DC_TMP
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)", "SHORT")." as DATE_CHANGE
			FROM b_search_tags stags2
				INNER JOIN b_search_content sc ON (stags2.SEARCH_CONTENT_ID=sc.ID)
				INNER JOIN b_search_content_site scsite ON (sc.ID=scsite.SEARCH_CONTENT_ID AND stags2.SITE_ID=scsite.SITE_ID)
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".($this->Query->bTagsSearch? (
				//Index range scan optimization (make it for other queries ???)
				is_array($this->Query->m_tags_words) && count($this->Query->m_tags_words)?
				"stags.name in ('".implode("', '", $this->Query->m_tags_words)."')":
				"(1=1)"
				) : "(".$query.")")." ".$strSqlWhere."
			GROUP BY
				stags2.NAME
			".$strSort."
			";
			}
			else
			{
			$strSql = "
			SELECT
				stags2.NAME
				,COUNT(DISTINCT stags.SEARCH_CONTENT_ID) as CNT
				,MAX(sc.DATE_CHANGE) DC_TMP
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)", "SHORT")." as DATE_CHANGE
			FROM b_search_tags stags2
				INNER JOIN b_search_tags stags ON (stags.SEARCH_CONTENT_ID=stags2.SEARCH_CONTENT_ID and stags.SITE_ID=stags2.SITE_ID)
				INNER JOIN b_search_content sc ON (stags.SEARCH_CONTENT_ID=sc.ID)
				INNER JOIN b_search_content_site scsite ON (sc.ID=scsite.SEARCH_CONTENT_ID AND stags.SITE_ID=scsite.SITE_ID)
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".($this->Query->bTagsSearch? (
				//Index range scan optimization (make it for other queries ???)
				is_array($this->Query->m_tags_words) && count($this->Query->m_tags_words)?
				"stags.name in ('".implode("', '", $this->Query->m_tags_words)."')":
				"(1=1)"
				) : "(".$query.")")." ".$strSqlWhere."
			GROUP BY
				stags2.NAME
				".($this->Query->bTagsSearch? "
			HAVING
				(".$query.")": "")."
			".$strSort."
			";
			}
		}
		elseif(!$bIncSites && $bStem)
			$strSql = "
			SELECT
				stags.NAME
				,COUNT(DISTINCT stags.SEARCH_CONTENT_ID) as CNT
				,MAX(sc.DATE_CHANGE) DC_TMP
				, ".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)")." as FULL_DATE_CHANGE
				, ".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)", "SHORT")." as DATE_CHANGE
				".(count($this->Query->m_stemmed_words)>1 && strpos($query, "searchable_content")!==false?"
					,sc.SEARCHABLE_CONTENT
				": "")."
			FROM b_search_tags stags
				INNER JOIN b_search_content sc ON (stags.SEARCH_CONTENT_ID=sc.ID)
				INNER JOIN b_search_content_stem st ON sc.id = st.search_content_id
				".(count($this->Query->m_stemmed_words)>1?
					"INNER JOIN b_search_content_freq sf ON
						st.language_id = sf.language_id
						and st.stem=sf.stem
						".(strlen($this->tf_hwm_site_id) > 0?
							"and sf.SITE_ID = '".$DB->ForSQL($this->tf_hwm_site_id, 2)."'":
							"and sf.SITE_ID IS NULL"
						):
					""
				)."
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND st.STEM in ('".implode("','", $this->Query->m_stemmed_words)."')
				".(count($this->Query->m_stemmed_words)>1? "AND sf.STEM in ('".implode("','", $this->Query->m_stemmed_words)."')": "")."
				AND st.language_id='".$this->Query->m_lang."'
				".$strSqlWhere."
			GROUP BY
				stags.NAME
			".(count($this->Query->m_stemmed_words)>1?"
				,sc.ID
				,sc.SEARCHABLE_CONTENT
			HAVING
				(".$query.") ": "")."
			".$strSort."
			";
		else //if(!$bIncSites && !$bStem)
			$strSql = "
			SELECT
				stags2.NAME
				,COUNT(DISTINCT stags.SEARCH_CONTENT_ID) as CNT
				,MAX(sc.DATE_CHANGE) DC_TMP
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)", "SHORT")." as DATE_CHANGE
			FROM b_search_tags stags2
				INNER JOIN b_search_tags stags ON (stags.SEARCH_CONTENT_ID=stags2.SEARCH_CONTENT_ID and stags.SITE_ID=stags2.SITE_ID)
				INNER JOIN b_search_content sc ON (stags.SEARCH_CONTENT_ID=sc.ID)
			WHERE
				".CSearch::CheckPermissions("sc.ID")."
				AND ".($this->Query->bTagsSearch? (
				//Index range scan optimization (make it for other queries ???)
				is_array($this->Query->m_tags_words) && count($this->Query->m_tags_words)?
				"stags.name in ('".implode("', '", $this->Query->m_tags_words)."')":
				"(1=1)"
				) : "(".$query.")")." ".$strSqlWhere."
			GROUP BY
				stags2.NAME
				".($this->Query->bTagsSearch? "
			HAVING
				(".$query.")": "")."
			".$strSort."
			";

		if($limit < 1)
			$limit = 150;

		return $strSql."LIMIT ".$limit;
	}

	function ReindexLock()
	{
		//do not lock for mysql database
	}

	function DeleteOld($SESS_ID, $MODULE_ID="", $SITE_ID="")
	{
		$DB = CDatabase::GetModuleConnection('search');

		$strFilter = "";
		if($MODULE_ID!="")
			$strFilter.=" AND MODULE_ID = '".$DB->ForSql($MODULE_ID)."' ";

		$strJoin = "";
		if($SITE_ID!="")
		{
			$strFilter.=" AND scsite.SITE_ID = '".$DB->ForSql($SITE_ID)."' ";
			$strJoin.=" INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID ";
		}

		if(!is_array($SESS_ID))
			$SESS_ID = array($SESS_ID);

		$strSql = "
			SELECT ID
			FROM b_search_content sc
			".$strJoin."
			WHERE (UPD not in ('".implode("', '", $SESS_ID)."') OR UPD IS NULL)
			".$strFilter."
		";

		$arEvents = array();
		$db_events = GetModuleEvents("search", "OnBeforeIndexDelete");
		while($arEvent = $db_events->Fetch())
			$arEvents[] = $arEvent;

		$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($ar = $rs->Fetch())
		{
			foreach($arEvents as $arEvent)
				ExecuteModuleEventEx($arEvent, array("SEARCH_CONTENT_ID = ".$ar["ID"]));
			$DB->Query("DELETE FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_right WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content WHERE ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		CSearchTags::CleanCache();
	}

	function DeleteForReindex($MODULE_ID)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$MODULE_ID = $DB->ForSql($MODULE_ID);
		$strSql = "SELECT ID FROM b_search_content WHERE MODULE_ID = '".$MODULE_ID."'";

		$arEvents = array();
		$db_events = GetModuleEvents("search", "OnBeforeIndexDelete");
		while($arEvent = $db_events->Fetch())
			$arEvents[] = $arEvent;

		$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($ar = $rs->Fetch())
		{
			foreach($arEvents as $arEvent)
				ExecuteModuleEventEx($arEvent, array("SEARCH_CONTENT_ID = ".$ar["ID"]));
			$DB->Query("DELETE FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_right WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_title WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content WHERE ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		CSearchTags::CleanCache();
	}

	function OnLangDelete($lang)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$lang = $DB->ForSql($lang);

		$strSql = "
			SELECT SEARCH_CONTENT_ID ID
			FROM b_search_content_site
			WHERE SITE_ID='".$lang."'
			GROUP BY SEARCH_CONTENT_ID
			HAVING COUNT(*)=1
		";

		$arEvents = array();
		$db_events = GetModuleEvents("search", "OnBeforeIndexDelete");
		while($arEvent = $db_events->Fetch())
			$arEvents[] = $arEvent;

		$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($ar = $rs->Fetch())
		{
			foreach($arEvents as $arEvent)
				ExecuteModuleEventEx($arEvent, array("SEARCH_CONTENT_ID = ".$ar["ID"]));
			$DB->Query("DELETE FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_right WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_title WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content WHERE ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$DB->Query("DELETE FROM b_search_content_site WHERE SITE_ID='".$lang."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$r = $DB->Query("
			SELECT sc.ID, MIN(scsite.SITE_ID) as SITE_ID
			FROM b_search_content sc, b_search_content_site scsite
			WHERE sc.LID = '".$lang."'
			AND sc.ID = scsite.SEARCH_CONTENT_ID
			AND scsite.SITE_ID <> '".$lang."'
			GROUP BY sc.ID
		", false, "File: ".__FILE__."<br>Line: ".__LINE__);

		while($arR = $r->Fetch())
			$DB->Query("UPDATE b_search_content SET LID = '".$arR["SITE_ID"]."' WHERE ID=".$arR["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$DB->Query("DELETE FROM b_search_content WHERE LID='".$lang."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);

		CSearchTags::CleanCache();
	}

	function ChangePermission($MODULE_ID, $arGroups, $ITEM_ID=false, $PARAM1=false, $PARAM2=false, $SITE_ID=false, $PARAMS=false)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$arNewGroups = array();
		foreach($arGroups as $group_id)
		{
			if(is_numeric($group_id))
				$arNewGroups[$group_id] = "G".intval($group_id);
			else
				$arNewGroups[$group_id] = $group_id;
		}

		$strSqlWhere = CSearch::__PrepareFilter(array(
			"MODULE_ID"=>$MODULE_ID,
			"ITEM_ID"=>$ITEM_ID,
			"PARAM1"=>$PARAM1,
			"PARAM2"=>$PARAM2,
			"SITE_ID"=>$SITE_ID,
			"PARAMS"=>$PARAMS,
		), $bIncSites);

		$DB->Query("
			DELETE FROM b_search_content_right
			".($strSqlWhere?
				"WHERE SEARCH_CONTENT_ID IN (
					SELECT ID FROM b_search_content sc WHERE ".$strSqlWhere."
				)":
				""
			)."
		", false, "File: ".__FILE__."<br>Line: ".__LINE__);

		foreach($arNewGroups as $group_code)
		{
			$DB->Query("
				INSERT INTO b_search_content_right (SEARCH_CONTENT_ID, GROUP_CODE)
				SELECT ID, '".$DB->ForSQL($group_code, 100)."'
				FROM b_search_content sc
				".($strSqlWhere?
					"WHERE ".$strSqlWhere:
					""
				)."
			", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
	}

	function ChangeSite($MODULE_ID, $arSite, $ITEM_ID=false, $PARAM1=false, $PARAM2=false, $SITE_ID=false)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$strSqlWhere = CSearch::__PrepareFilter(Array("MODULE_ID"=>$MODULE_ID, "ITEM_ID"=>$ITEM_ID, "PARAM1"=>$PARAM1, "PARAM2"=>$PARAM2, "SITE_ID"=>$SITE_ID), $bIncSites);

		$strSql = "
			SELECT sc.ID
			FROM b_search_content sc
			".($bIncSites? "INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID": "")."
			WHERE
			".$strSqlWhere."
		";

		$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($arR = $r->Fetch())
		{
			$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$arR["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$bFirst = true;
			foreach($arSite as $site => $url)
			{
				if($bFirst)
				{
					$strSql = "
						UPDATE b_search_content
						SET LID = '".$DB->ForSql($site, 2)."',
						URL = '".$DB->ForSql($url, 2000)."'
						WHERE ID = ".$arR["ID"]."
					";
					$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
					$bFirst = false;
				}
				$strSql = "
					INSERT INTO b_search_content_site(SEARCH_CONTENT_ID, SITE_ID, URL)
					VALUES(".$arR["ID"].", '".$DB->ForSql($site, 2)."', '".$DB->ForSql($url, 2000)."')";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
	}

	function DeleteIndex($MODULE_ID, $ITEM_ID=false, $PARAM1=false, $PARAM2=false, $SITE_ID=false)
	{
		$DB = CDatabase::GetModuleConnection('search');

		if($PARAM1 !== false && $PARAM2 !== false)
			$strSqlWhere = CSearch::__PrepareFilter(Array("MODULE_ID"=>$MODULE_ID, "ITEM_ID"=>$ITEM_ID, array("=PARAM1"=>$PARAM1, "PARAM2"=>$PARAM2), "SITE_ID"=>$SITE_ID), $bIncSites);
		else
			$strSqlWhere = CSearch::__PrepareFilter(Array("MODULE_ID"=>$MODULE_ID, "ITEM_ID"=>$ITEM_ID, "PARAM1"=>$PARAM1, "PARAM2"=>$PARAM2, "SITE_ID"=>$SITE_ID), $bIncSites);

		$strSql = "
			SELECT sc.ID
			FROM b_search_content sc
				".($bIncSites? "INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID" :"")."
			WHERE
			".$strSqlWhere."
		";

		$arEvents = array();
		$db_events = GetModuleEvents("search", "OnBeforeIndexDelete");
		while($arEvent = $db_events->Fetch())
			$arEvents[] = $arEvent;

		$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($ar = $rs->Fetch())
		{
			foreach($arEvents as $arEvent)
				ExecuteModuleEventEx($arEvent, array("SEARCH_CONTENT_ID = ".$ar["ID"]));
			$DB->Query("DELETE FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_right WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_title WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$DB->Query("DELETE FROM b_search_content WHERE ID = ".$ar["ID"], false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		CSearchTags::CleanCache();
	}

	function FormatDateString($strField)
	{
		return "DATE_FORMAT(".$strField.", '%d.%m.%Y %H:%i:%s')";
	}

	function FormatLimit($strSql, $limit)
	{
		return str_replace("/*TOP*/", "", $strSql)."LIMIT ".intval($limit);
	}

	function Update($ID, $arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');

		if(is_set($arFields, "LAST_MODIFIED"))
			$arFields["DATE_CHANGE"] = $arFields["LAST_MODIFIED"];
		elseif(is_set($arFields, "DATE_CHANGE"))
			$arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["DATE_CHANGE"], "DD.MM.YYYY HH.MI.SS", CLang::GetDateFormat());

		$strUpdate = $DB->PrepareUpdate("b_search_content", $arFields);
		if(strlen($strUpdate) > 0)
		{
			$DB->Query("UPDATE b_search_content SET ".$strUpdate." WHERE ID=".intval($ID), false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
	}

	function CleanFreqCache($ID)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$rs = $DB->Query("SELECT LANGUAGE_ID,STEM FROM b_search_content_stem WHERE SEARCH_CONTENT_ID = ".intval($ID), false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($ar = $rs->Fetch())
		{
			$DB->Query("
				UPDATE b_search_content_freq SET TF = null
				WHERE TF is not null
				AND LANGUAGE_ID = '".$DB->ForSQL($ar["LANGUAGE_ID"])."'
				AND STEM = '".$DB->ForSQL($ar["STEM"])."'
			", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
	}

	function IndexTitle($arLID, $ID, $sTitle)
	{
		$DB = CDatabase::GetModuleConnection('search');
		static $CACHE_SITE_LANGS = array();

		$arLang=array();
		if(!is_array($arLID))
			$arLID = Array();
		foreach($arLID as $site=>$url)
		{
			if(!array_key_exists($site, $CACHE_SITE_LANGS))
			{
				$db_site_tmp = CSite::GetByID($site);
				if ($ar_site_tmp = $db_site_tmp->Fetch())
					$CACHE_SITE_LANGS[$site] = array(
						"LANGUAGE_ID" => $ar_site_tmp["LANGUAGE_ID"],
						"CHARSET" => $ar_site_tmp["CHARSET"],
						"SERVER_NAME" => $ar_site_tmp["SERVER_NAME"]
					);
				else
					$CACHE_SITE_LANGS[$site] = false;
			}

			if(is_array($CACHE_SITE_LANGS[$site]))
			{
				$lang = $CACHE_SITE_LANGS[$site]["LANGUAGE_ID"];

				$arTitle = stemming_split($sTitle, $lang);
				if(!empty($arTitle))
				{
					$maxValuesLen = 2048;
					$strSqlPrefix = "
							insert into b_search_content_title
							(SEARCH_CONTENT_ID, SITE_ID, WORD, POS)
							values
					";
					$strSqlValues = "";
					$strSqlSuffix = "";
					foreach($arTitle as $word => $pos)
					{
						$strSqlValues .= ",\n(".$ID.", '".$site."', '".$DB->ForSql($word)."', ".$pos.")";
						if(strlen($strSqlValues) > $maxValuesLen)
						{
							$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, "File: ".__FILE__."<br>Line: ".__LINE__);
							$strSqlValues = "";
						}
					}
					if(strlen($strSqlValues) > 0)
					{
						$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, "File: ".__FILE__."<br>Line: ".__LINE__);
						$strSqlValues = "";
					}
				}
			}
		}
	}

	function StemIndex($arLID, $ID, $sContent)
	{
		$DB = CDatabase::GetModuleConnection('search');
		static $CACHE_SITE_LANGS = array();

		$arLang=array();
		if(!is_array($arLID))
			$arLID = Array();
		foreach($arLID as $site=>$url)
		{
			if(!array_key_exists($site, $CACHE_SITE_LANGS))
			{
				$db_site_tmp = CSite::GetByID($site);
				if ($ar_site_tmp = $db_site_tmp->Fetch())
					$CACHE_SITE_LANGS[$site] = array(
						"LANGUAGE_ID" => $ar_site_tmp["LANGUAGE_ID"],
						"CHARSET" => $ar_site_tmp["CHARSET"],
						"SERVER_NAME" => $ar_site_tmp["SERVER_NAME"]
					);
				else
					$CACHE_SITE_LANGS[$site] = false;
			}
			if(is_array($CACHE_SITE_LANGS[$site]))
				$arLang[$CACHE_SITE_LANGS[$site]["LANGUAGE_ID"]]++;
		}
		foreach($arLang as $lang=>$value)
		{
			$arDoc = stemming($sContent, $lang);
			$docLength = array_sum($arDoc);
			if($docLength > 0)
			{
				$doc = "";
				$logDocLength = log($docLength<20?20:$docLength);
				$strSqlPrefix = "
						insert into b_search_content_stem
						(SEARCH_CONTENT_ID, LANGUAGE_ID, STEM, TF)
						values
				";
				$maxValuesLen = 2048;
				$strSqlValues = "";
				foreach($arDoc as $word => $count)
				{
					$strSqlValues .= ",\n(".$ID.", '".$lang."', '".$DB->ForSql($word)."', ".
						number_format(log($count+1)/$logDocLength, 4, ".", "").")";
					if(strlen($strSqlValues) > $maxValuesLen)
					{
						$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, "File: ".__FILE__."<br>Line: ".__LINE__);
						$strSqlValues = "";
					}
				}
				if(strlen($strSqlValues) > 0)
				{
					$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, "File: ".__FILE__."<br>Line: ".__LINE__);
					$strSqlValues = "";
				}
			}
		}
	}

	function TagsIndex($arLID, $ID, $sContent)
	{
		$DB = CDatabase::GetModuleConnection('search');
		if(!is_array($arLID))
			$arLID = Array();
		$sContent = str_replace("\x00", "", $sContent);

		foreach($arLID as $site_id => $url)
		{
			$arTags = tags_prepare($sContent, $site_id);
			if(!empty($arTags))
			{
				$strSqlPrefix = "
						insert into b_search_tags
						(SEARCH_CONTENT_ID, SITE_ID, NAME)
						values
				";
				$maxValuesLen = 2048;
				$strSqlValues = "";
				CSearchTags::CleanCache($arTags);
				foreach($arTags as $tag)
				{
					$strSqlValues .= ",\n(".$ID.", '".$site_id."', '".$DB->ForSql($tag, 255)."')";
					if(strlen($strSqlValues) > $maxValuesLen)
					{
						$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, "File: ".__FILE__."<br>Line: ".__LINE__);
						$strSqlValues = "";
					}
				}
				if(strlen($strSqlValues) > 0)
				{
					$DB->Query($strSqlPrefix.substr($strSqlValues, 2), false, "File: ".__FILE__."<br>Line: ".__LINE__);
					$strSqlValues = "";
				}
			}
		}
	}

	function ChangeIndex($MODULE_ID, $arFields, $ITEM_ID=false, $PARAM1=false, $PARAM2=false, $SITE_ID=false)
	{
		$DB = CDatabase::GetModuleConnection('search');

		if(array_key_exists("TITLE", $arFields))
			$arFields["TITLE"] = Trim($arFields["TITLE"]);

		if(array_key_exists("BODY", $arFields))
			$arFields["BODY"] = Trim($arFields["BODY"]);

		if(array_key_exists("PERMISSIONS", $arFields) && is_array($arFields["PERMISSIONS"]))
			CSearch::ChangePermission($MODULE_ID, $arFields["PERMISSIONS"], $ITEM_ID, $PARAM1, $PARAM2, $SITE_ID);

		if(array_key_exists("SITE_ID", $arFields) && is_array($arFields["SITE_ID"]))
			CSearch::ChangeSite($MODULE_ID, $arFields["SITE_ID"], $ITEM_ID, $PARAM1, $PARAM2, $SITE_ID);

		$strUpdate = $DB->PrepareUpdate("b_search_content", $arFields);
		if(strlen($strUpdate) > 0)
		{
			$strSqlWhere = CSearch::__PrepareFilter(Array("MODULE_ID"=>$MODULE_ID, "ITEM_ID"=>$ITEM_ID, "PARAM1"=>$PARAM1, "PARAM2"=>$PARAM2, "SITE_ID"=>$SITE_ID), $bIncSites);
			$strSql = "
				SELECT sc.ID
				FROM b_search_content sc
				".($bIncSites? "INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID": "")."
				".(strlen($strSqlWhere)>0? "WHERE ".$strSqlWhere: "")."
			";
			$rs = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($ar = $rs->Fetch())
			{
				$strSql = "UPDATE b_search_content SET ".$strUpdate." WHERE ID=".$ar["ID"];
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

				if(array_key_exists("PARAMS", $arFields))
					CSearch::SetContentItemParams($ar["ID"], $arFields["PARAMS"]);
			}
		}
	}
}

class CSearchQuery extends CAllSearchQuery
{
	var $cnt = 0;
	function BuildWhereClause($word)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$this->cnt++;
		if($this->cnt>10)
			return "1=1";

		if(isset($this->m_kav[$word]))
		{
			$word = $this->m_kav[$word];
			$bInQuotes = true;
		}
		else
		{
			$bInQuotes = false;
		}
		$this->m_words[] = $word;
		$word = $DB->ForSql($word, 100);

		if($this->bTagsSearch)
		{
			if(strpos($word, "%")===false)
			{
				//We can optimize query by doing range scan
				if(is_array($this->m_tags_words))
					$this->m_tags_words[] = $word;
				$op = "=";
			}
			else
			{
				//Optimization is not possible
				$this->m_tags_words = false;
				$op = "like";
			}
			return "(sum(stags.name ".$op." '".$word."')>0)";
		}
		elseif($this->bStemming && !$bInQuotes)
		{
			$word = ToUpper($word);
			$this->m_stemmed_words[] = $word;
			return "(sum(st.stem = '".$word."')>0)";
		}
		else
		{
			return "(sc.searchable_content LIKE '%".str_replace(array("%", "_"), array("\\%", "\\_") ,ToUpper($word))."%')";
		}
	}
}
?>
