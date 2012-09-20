<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/xml.php');

class gdRssFeeds
{
	var $title;
	var $link;
	var $description;
	var $pubDate;
	var $items = array();

}

function gdGetRss($rss_url, $cache_time = 0)
{
	$cache = new CPHPCache();
	if(!$cache->StartDataCache($cache_time, 'c'.$rss_url, "gdrss"))
	{
		$v = $cache->GetVars();
		return $v['oRss'];
	}

	$oRssFeeds = new gdRssFeeds();

	$arUrl = parse_url($rss_url);
	if(IntVal($arUrl["port"])<=0)
		$arUrl["port"] = 80;


	$ob = new CHTTP();
	$ob->http_timeout = 10;
	$ob->Query(
		"GET",
		$arUrl["host"],
		$arUrl["port"],
		$arUrl["path"].($arUrl["query"] <> ''? "?".$arUrl["query"] : ''),
		false,
		"",
		"N"
	);

	$errno = $ob->errno;
	$errstr = $ob->errstr;

	$res = $ob->result;

	if(!$res)
	{
		$cache->EndDataCache(array("oRss"=>false));
		return false;
	}


	if (preg_match("/<"."\?XML[^>]{1,}encoding=[\"']([^>\"']{1,})[\"'][^>]{0,}\?".">/i", $res, $matches))
		$charset = Trim($matches[1]);

	$res = $GLOBALS["APPLICATION"]->ConvertCharset($res, $charset, SITE_CHARSET);

	$xml = new CDataXML();
	$xml->LoadString($res);

	$oNode = $xml->SelectNodes("/rss/channel/title");
	if(!$oNode)
	{
		$cache->EndDataCache(array("oRss"=>false));
		return false;
	}

	$oRssFeeds->title = $oNode->content;
	if (strlen(trim($oRssFeeds->title)) <= 0)
	{
		if($oSubNode = $oNode->elementsByName("cdata-section"))
			$oRssFeeds->title = $oSubNode[0]->content;
	}
	
	if($oNode = $xml->SelectNodes("/rss/channel/link"))
		$oRssFeeds->link = $oNode->content;

	if($oNode = $xml->SelectNodes("/rss/channel/description"))
		$oRssFeeds->description = $oNode->content;
	if (strlen(trim($oRssFeeds->description)) <= 0)
	{
		if($oNode && $oSubNode = $oNode->elementsByName("cdata-section"))
			$oRssFeeds->description = $oSubNode[0]->content;
	}

	if($oNode = $xml->SelectNodes("/rss/channel/pubDate"))
		$oRssFeeds->pubDate = $oNode->content;
	elseif($oNode = $xml->SelectNodes("/rss/channel/lastBuildDate"))
		$oRssFeeds->pubDate = $oNode->content;

	if($oNode = $xml->SelectNodes("/rss/channel"))
	{
		$oNodes = $oNode->elementsByName("item");
		foreach($oNodes as $oNode)
		{
			$item = Array();


			if($oSubNode = $oNode->elementsByName("title"))
				$item["TITLE"] = $oSubNode[0]->content;
			if (strlen(trim($item["TITLE"])) <= 0 && !empty($oSubNode))
			{
				if($oSubNode = $oSubNode[0]->elementsByName("cdata-section"))
					$item["TITLE"] = $oSubNode[0]->content;
			}

			if($oSubNode = $oNode->elementsByName("link"))
				$item["LINK"] = $oSubNode[0]->content;

			if($oSubNode = $oNode->elementsByName("pubDate"))
				$item["PUBDATE"] = $oSubNode[0]->content;

			if($oSubNode = $oNode->elementsByName("description"))
				$item["DESCRIPTION"] = $oSubNode[0]->content;
			if (strlen(trim($item["DESCRIPTION"])) <= 0 && !empty($oSubNode))
			{
				if($oSubNode = $oSubNode[0]->elementsByName("cdata-section"))
					$item["DESCRIPTION"] = $oSubNode[0]->content;
			}

			if($oSubNode = $oNode->elementsByName("author"))
				$item["AUTHOR"] = $oSubNode[0]->content;
			if (strlen(trim($item["AUTHOR"])) <= 0 && !empty($oSubNode))
			{
				if($oSubNode = $oSubNode[0]->elementsByName("cdata-section"))
					$item["AUTHOR"] = $oSubNode[0]->content;
			}

			$oRssFeeds->items[] = $item;
		}
	}

	$cache->EndDataCache(array("oRss"=>$oRssFeeds));

	return $oRssFeeds;
}
?>
