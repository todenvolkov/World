<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPDelayActivity
	extends CBPActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	private $subscriptionId = 0;
	private $isInEventActivityMode = false;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "", "TimeoutDuration" => null);
	}

	public function Cancel()
	{
		if (!$this->isInEventActivityMode && $this->subscriptionId > 0)
			$this->Unsubscribe($this);

		return CBPActivityExecutionStatus::Closed;
	}

	public function Execute()
	{
		if ($this->isInEventActivityMode)
			return CBPActivityExecutionStatus::Closed;

		$this->Subscribe($this);

		$this->WriteToTrackingService(str_replace("#PERIOD#", CBPHelper::FormatTimePeriod($this->TimeoutDuration), GetMessage("BPDA_TRACK")));

		$this->isInEventActivityMode = false;
		return CBPActivityExecutionStatus::Executing;
	}

	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$this->isInEventActivityMode = true;
		$expiresAt = time() + $this->TimeoutDuration;

		$schedulerService = $this->workflow->GetService("SchedulerService");
		$this->subscriptionId = $schedulerService->SubscribeOnTime($this->workflow->GetInstanceId(), $this->name, $expiresAt);

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}

	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$schedulerService = $this->workflow->GetService("SchedulerService");
		$schedulerService->UnSubscribeOnTime($this->subscriptionId);

		$this->workflow->RemoveEventHandler($this->name, $eventHandler);

		$this->subscriptionId = 0;
	}

	public function OnExternalEvent($arEventParameters = array())
	{
		if ($this->executionStatus != CBPActivityExecutionStatus::Closed)
		{
			$this->Unsubscribe($this);
			$this->workflow->CloseActivity($this);
		}
	}

	public function HandleFault(Exception $exception)
	{
		if ($exception == null)
			throw new Exception("exception");

		$status = $this->Cancel();
		if ($status == CBPActivityExecutionStatus::Canceling)
			return CBPActivityExecutionStatus::Faulting;

		return $status;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!array_key_exists("TimeoutDuration", $arTestProperties)
			|| (intval($arTestProperties["TimeoutDuration"]) <= 0 
				&& !preg_match('#^{=[A-Za-z0-9_]+:[A-Za-z0-9_]+}$#i', $arTestProperties["TimeoutDuration"])))
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "TimeoutDuration", "message" => GetMessage("BPDA_EMPTY_PROP"));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]) && array_key_exists("TimeoutDuration", $arCurrentActivity["Properties"]))
				$arCurrentValues["delay_time"] = $arCurrentActivity["Properties"]["TimeoutDuration"];
			else
				$arCurrentValues["delay_time"] = 0;

			if (!preg_match('#^{=[A-Za-z0-9_]+:[A-Za-z0-9_]+}$#i', $arCurrentValues["delay_time"]))
			{
				$arCurrentValues["delay_time"] = intval($arCurrentValues["delay_time"]);
				if ($arCurrentValues["delay_time"] <= 0)
					$arCurrentValues["delay_time"] = 3600;

				$arCurrentValues["delay_type"] = "s";
				if ($arCurrentValues["delay_time"] % (3600 * 24) == 0)
				{
					$arCurrentValues["delay_time"] = $arCurrentValues["delay_time"] / (3600 * 24);
					$arCurrentValues["delay_type"] = "d";
				}
				elseif ($arCurrentValues["delay_time"] % 3600 == 0)
				{
					$arCurrentValues["delay_time"] = $arCurrentValues["delay_time"] / 3600;
					$arCurrentValues["delay_type"] = "h";
				}
				elseif ($arCurrentValues["delay_time"] % 60 == 0)
				{
					$arCurrentValues["delay_time"] = $arCurrentValues["delay_time"] / 60;
					$arCurrentValues["delay_type"] = "m";
				}
			}
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = Array();

		$runtime = CBPRuntime::GetRuntime();

		$arProperties = array();
		if (!preg_match('#^{=[A-Za-z0-9_]+:[A-Za-z0-9_]+}$#i', $arCurrentValues["delay_time"]))
		{
			$delayType = $arCurrentValues["delay_type"];
			$arProperties["TimeoutDuration"] = intval($arCurrentValues["delay_time"]) * ($delayType == "s" ? 1 : ($delayType == "m" ? 60 : ($delayType == "h" ? 3600 : 3600 * 24)));
		}
		else
		{
			$arProperties["TimeoutDuration"] = $arCurrentValues["delay_time"];
		}

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}
?>
