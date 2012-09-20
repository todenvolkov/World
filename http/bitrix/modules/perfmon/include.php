<?
global $DB;
$db_type = strtolower($DB->type);

CModule::AddAutoloadClasses(
	"perfmon",
	array(
		"CAllPerfomanceKeeper" => "classes/general/keeper.php",
		"CPerfomanceKeeper" => "classes/".$db_type."/keeper.php",
		"CAllPerfomanceHit" => "classes/general/hit.php",
		"CPerfomanceHit" => "classes/".$db_type."/hit.php",
		"CPerfomanceComponent" => "classes/general/component.php",
		"CAllPerfomanceSQL" => "classes/general/sql.php",
		"CPerfomanceSQL" => "classes/".$db_type."/sql.php",
		"CAllPerfomanceTable" => "classes/general/table.php",
		"CPerfomanceTable" => "classes/".$db_type."/table.php",
		"CPerfomanceTableList" => "classes/".$db_type."/table.php",
		"CAllPerfomanceError" => "classes/general/error.php",
		"CPerfomanceError" => "classes/".$db_type."/error.php",
		"CPerfomanceMeasure" => "classes/general/measure.php",
		"CPerfAccel" => "classes/general/measure.php",
		"CPerfCluster" =>  "classes/general/cluster.php",
	)
);
?>