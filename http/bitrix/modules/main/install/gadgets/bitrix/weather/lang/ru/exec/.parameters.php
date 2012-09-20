<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


include(dirname(__FILE__).'/city.php');

asort($arCity);

$arParameters = Array(
		"PARAMETERS"=> Array(
			"CACHE_TIME" => array(
				"NAME" => "Время кеширования, сек (0-не кешировать)",
				"TYPE" => "STRING",
				"DEFAULT" => "3600"
				),
			"SHOW_URL" => Array(
					"NAME" => "Показывать ссылку на подробную информацию",
					"TYPE" => "CHECKBOX",
					"MULTIPLE" => "N",
					"DEFAULT" => "N",
				),
		),
		"USER_PARAMETERS"=> Array(
			"CITY"=>Array(
				"NAME" => "Город",
				"TYPE" => "LIST",
				"MULTIPLE" => "N",
				"DEFAULT" => "c213",
				"VALUES"=>$arCity,
			),
		),
	);

?>
