<?php
class RssNewsWidget extends YWidget
{
    public $count = 4;
	public $feed = "";
	public $cache_time = 3600;
	
	private $filepath = "";

    public function run()
    {
        $this->filepath = Yii::getPathOfAlias('webroot').DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'rss'.DIRECTORY_SEPARATOR.md5($this->feed).".rss";
		
		$xml = $this->parseRss();
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($xml);
		
		if(!$xml){
			
		}else{
			$counter = 0;
			$entries = array();
			$entries = array_merge($entries, $xml->xpath('/rss//item'));
			usort($entries, function ($x, $y) { return (strtotime($x->pubDate) < strtotime($y->pubDate))?1:-1; });
			foreach ($entries as $item) {
				$counter++;
				echo "<h3>".CHtml::link($item->title,$item->link,array('target'=>'_blank'))."</h3>";
				echo "<p>".$this->strip_tags_content($item->description,'<p>')."</p>";
				if($counter>=$this->count) break;
			}
			//var_dump($xml->channel);
		}

        //$this->render('news', array('news' => $news));
    }
	
	private function parseRss(){
		$ping = $this->ping($this->feed,80,10);
		if((file_exists($this->filepath) && ( time() - filemtime($this->filepath) < $this->cache_time)) || ($ping == "down")){
			if($ping == "down") { touch($this->filepath); }
			return file_get_contents($this->filepath);
		}else{
			return $this->getRss();
		}
	}
	
	private function getRss(){
		/**/$buffer = file_get_contents($this->feed);
		if(strlen($buffer)>0){
			$lfile = fopen($this->filepath,"w+");
			fwrite($lfile,$buffer);
			fclose($lfile);
		}
		return $buffer;
	}
	
	private function strip_tags_content($text, $tags = '', $invert = FALSE) { 
		  preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags); 
		  $tags = array_unique($tags[1]); 
		  
		  if(is_array($tags) AND count($tags) > 0) { 
			if($invert == FALSE) { 
			$text = preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
			  return  preg_replace("/<img[^>]+\>/i", "", $text); 
			} 
			else { 
			  return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text); 
			} 
		  } 
		  elseif($invert == FALSE) {
			  $text = preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
			return preg_replace("/<img[^>]+\>/i", "", $text); 
		  } 
		  return $text; 
	}
	
	private function ping($host, $port, $timeout) {
		  $host = parse_url($host); 
		  $tB = microtime(true); 
		  $fP = fsockopen($host['host'], $port, $errno, $errstr, $timeout); 
		  if (!$fP) { return "down"; } 
		  $tA = microtime(true); 
		  return round((($tA - $tB) * 1000), 0)." ms"; 
	}
}