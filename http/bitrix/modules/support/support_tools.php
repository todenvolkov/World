<?
function __PrintRussian($num, $ext)//$ext - 3 окончания для цифер соответственно 1, 2, 5
{
	if(strlen($num)>1 && substr($num,strlen($num)-2,1)=="1")
		return $ext[2];

	$c=IntVal(substr($num,strlen($num)-1,1));
	if($c==0 || ($c>=5 && $c<=9))
		return $ext[2];

	if($c==1)
		return $ext[0];

	return $ext[1];
}
?>