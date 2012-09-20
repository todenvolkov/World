<?

IncludeModuleLangFile(__FILE__);

class CWikiDiff
{
	//http://en.wikipedia.org/wiki/Longest_common_subsequence_problem
	//function  LCS(X[1..m], Y[1..n])
	static function LongestCommonSubsequence($X, $Y)
	{
	    //	m_start := 1
		$m_start = 0;
	    //	m_end := m
		$m_end = count($X)-1;
	    //	n_start := 1
		$n_start = 0;
	    //	n_end := n
		$n_end = count($Y)-1;
	    //	C = array(m_start-1..m_end, n_start-1..n_end)
		$C = array();
	    //	for($i = $m_start-1; $i <= $m_end; $i++)
	    //	{
	    //		$C[$i] = array();
	    //		for($j = $n_start-1; $j <= $n_end; $j++)
	    //		{
	    //			$C[$i][$j] = 0;
	    //		}
	    //	}
	    //	for i := m_start..m_end
		for($i = $m_start; $i <= $m_end; $i++)
		{
	    //		for j := n_start..n_end
			for($j = $n_start; $j <= $n_end; $j++)
			{
	    //			if X[i] = Y[j]
				if($X[$i] == $Y[$j])
				{
	    //				C[i,j] := C[i-1,j-1] + 1
					$C[$i][$j] = $C[($i-1)][($j-1)] + 1;
				}
	    //			else:
				else
				{
					$k = max($C[$i][($j-1)], $C[($i-1)][$j]);
	    //				C[i,j] := max(C[i,j-1], C[i-1,j])
					if($k != 0)
					{
						$C[$i][$j] = $k;
						//Clean up to the left
						if($C[$i][$j-1] < $k)
							for($jj = $j-1;$jj >= $n_start;$jj--)
								if(is_array($C[$i]) && array_key_exists($jj, $C[$i]))
									unset($C[$i][$jj]);
								else
									break;
					}
				}
			}
			//Clean up to the up
			if($i > $m_start)
			{
				$ii = $i - 1;
				if(is_array($C[$ii]))
				{
					for($j = $n_end; $j > $n_start && array_key_exists($j, $C[$ii]); $j--)
					{
						if($C[$i][$j] > $C[$ii][$j])
							unset($C[$ii][$j]);
					}
				}
			}
		}
	    //	return C[m,n]
		return $C;
	}
    
	//function printDiff(C[0..m,0..n], X[1..m], Y[1..n], i, j)
	static function printDiff($C, $X, $Y, $Xt, $Yt, $i, $j)
	{
	    //	if i > 0 and j > 0 and X[i] = Y[j]
		if( ($i >= 0) && ($j >= 0) && ($Xt[$i] == $Yt[$j]) )
		{
	    //		self::printDiff(C, X, Y, i-1, j-1)
			self::printDiff($C, $X, $Y, $Xt, $Yt, $i-1, $j-1);
	    //		print "  " + X[i]
			echo $X[1][$i].$X[2][$i];
		}
	    //	else
		else
		{
	    //		if j > 0 and (i = 0 or C[i,j-1] >= C[i-1,j])
			if( ($j >= 0) && (($i < 0) || ($C[$i][($j-1)] >= $C[($i-1)][$j])) )
			{
	    //			self::printDiff(C, X, Y, i, j-1)
				self::printDiff($C, $X, $Y, $Xt, $Yt, $i, $j-1);
	    //			print "+ " + Y[j]
				echo $Y[1][$j].'<b style="color:green">',$Y[2][$j],"</b >";
			}
	    //		else if i > 0 and (j = 0 or C[i,j-1] < C[i-1,j])
			elseif( ($i >= 0) && (($j < 0) || ($C[$i][($j-1)] < $C[($i-1)][$j])) )
			{
	    //			self::printDiff(C, X, Y, i-1, j)
				self::printDiff($C, $X, $Y, $Xt, $Yt, $i-1, $j);
	    //			print "- " + X[i]
				echo $X[1][$i].'<s style="color:red">',$X[2][$i],"</s >";
			}
		}
	}
    
	static function getDiff($X, $Y)
	{    
		preg_match_all("/(<.*?>\s*|\s+)([^\s<]*)/", " ".$X, $Xmatch);
		preg_match_all("/(<.*?>\s*|\s+)([^\s<]*)/", " ".$Y, $Ymatch);
	    
		//Determine common beginning
		$sHTMLStart = "";
		while( count($Xmatch[0]) && count($Ymatch[0]) && (trim($Xmatch[2][0]) == trim($Ymatch[2][0])) )
		{
			$sHTMLStart .= $Xmatch[0][0];
			array_shift($Xmatch[0]);array_shift($Xmatch[1]);array_shift($Xmatch[2]);
			array_shift($Ymatch[0]);array_shift($Ymatch[1]);array_shift($Ymatch[2]);
		}
	    
		//Find common ending
		$X_end = count($Xmatch[0])-1;
		$Y_end = count($Ymatch[0])-1;
		$sHTMLEnd = "";
		while( ($X_end >= 0) && ($Y_end >= 0) && (trim($Xmatch[2][$X_end]) == trim($Ymatch[2][$Y_end])) )
		{
			$sHTMLEnd = $Xmatch[0][$X_end].$sHTMLEnd;
			unset($Xmatch[0][$X_end]);unset($Xmatch[1][$X_end]);unset($Xmatch[2][$X_end]);
			unset($Ymatch[0][$Y_end]);unset($Ymatch[1][$Y_end]);unset($Ymatch[2][$Y_end]);
			$X_end--;
			$Y_end--;
		}
	    
		//What will actually diff
		$Xmatch_trimmed = array();
		foreach($Xmatch[2] as $i => $match)
		{
			$Xmatch_trimmed[] = trim($match);
		}
	    
		$Ymatch_trimmed = array();
		foreach($Ymatch[2] as $i => $match)
		{
			$Ymatch_trimmed[] = trim($match);
		}
	    
		ob_start();
		self::printDiff(
			self::LongestCommonSubsequence($Xmatch_trimmed, $Ymatch_trimmed),
			$Xmatch,
			$Ymatch,
			$Xmatch_trimmed,
			$Ymatch_trimmed,
			count($Xmatch_trimmed)-1,
			count($Ymatch_trimmed)-1
		);
		$sHTML = ob_get_contents();
		ob_end_clean();
	    
		$sHTML = preg_replace('#</b >(\s*)<b style="color:green">#','\\1',$sHTML);
		$sHTML = preg_replace('#<b style="color:green">(\s*)</b >#','\\1',$sHTML);
		$sHTML = preg_replace('#</s >(\s*)<s style="color:red">#','\\1',$sHTML);
		$sHTML = preg_replace('#<s style="color:red">(\s*)</s >#','\\1',$sHTML);
	    
		return $sHTMLStart.$sHTML.$sHTMLEnd;
	}  
}
