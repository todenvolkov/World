<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

function utf8_urldecode($str,$quotes,$charset){
	$str =  preg_replace_callback('/%u([0-9a-f]{4})/i',create_function('$arr','return "&#".hexdec($arr[1]).";";'),$str);
	return html_entity_decode($str,$quotes,$charset);
}


if (CModule::IncludeModule("sale") && CModule::IncludeModule("catalog"))

{
 	
 	$tmp = CIBlockElement::GetByID( $_REQUEST['ID']);
	if ($ob = $tmp->GetNextElement()){
		$aa = $ob->GetFields();
	}
	        
	$pall_price = GetCatalogProductPrice( 4501, 1 ); 

	$pall_price['P_PRICE'] = floatval($_REQUEST['PRICE']);

	$props = array(	
		array("SORT"=>"100","NAME" => "ID экрана", "CODE" => "SCODE1", "VALUE" => $aa['CODE']),
		array("SORT"=>"101","NAME" => "Название экрана", "CODE" => "SNAME1", "VALUE" => $aa['NAME'] ),
		array("SORT"=>"102","NAME" => "Название ролика", "CODE" => "NAME1", "VALUE" => utf8_urldecode($_REQUEST['NAME'])),
		array("SORT"=>"103","NAME" => "Дата начала", "CODE" => "DATE1", "VALUE" => $_REQUEST['DATE1']),
		array("SORT"=>"104","NAME" => "Дата конца", "CODE" => "DATE2", "VALUE" => $_REQUEST['DATE2']),
		array("SORT"=>"105","NAME" => "Дней", "CODE" => "DAYS1", "VALUE" => $_REQUEST['DAYS']),
		array("SORT"=>"106","NAME" => "Количество выходов", "CODE" => "KOLVO1", "VALUE" => $_REQUEST['KOLVO']),	
		array("SORT"=>"107","NAME" => "Хронометраж", "CODE" => "HRON1", "VALUE" => $_REQUEST['HRON']),
		array("SORT"=>"108","NAME" => "Цена", "CODE" => "PRICE11", "VALUE" => $_REQUEST['PRICE']),
		array("SORT"=>"109","NAME" => "Комментарий", "CODE" => "COMM1", "VALUE" => utf8_urldecode($_REQUEST['COMM']))
	);
	
	Add2Basket(
		$pall_price['ID'],
		1,
		array('CALLBACK_FUNC' => 'add2basket_pscreen', 'ORDER_CALLBACK_FUNC' => 'order_pall',  'CAN_BUY'=>'Y'),
		$props
	);

}
?>
