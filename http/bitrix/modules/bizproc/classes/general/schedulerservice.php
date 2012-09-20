<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/classes/general/runtimeservice.php");

class CBPSchedulerService
	extends CBPRuntimeService
{
	public function SubscribeOnTime($workflowId, $eventName, $expiresAt)
	{
		$result = CAgent::AddAgent(
			"CBPSchedulerService::OnAgent('".$workflowId."', '".$eventName."', array('SchedulerService' => 'OnAgent'));",
			"bizproc",
			"N",
			10,
			"",
			"Y",
			date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATETIME), $expiresAt)
		);
		return $result;
	}

	public function UnSubscribeOnTime($id)
	{
		CAgent::Delete($id);
	}

	public static function OnAgent($workflowId, $eventName, $arEventParameters = array())
	{
		try
		{
			CBPRuntime::SendExternalEvent($workflowId, $eventName, $arEventParameters);
		}
		catch (Exception $e)
		{
			
		}
	}
}
?>