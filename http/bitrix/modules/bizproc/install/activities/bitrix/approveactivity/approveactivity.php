<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPApproveActivity
	extends CBPCompositeActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	private $taskId = 0;
	private $subscriptionId = 0;

	private $isInEventActivityMode = false;

	private $arApproveResults = array();

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Users" => null,
			"ApproveType" => "all",
			"Percent" => 100,
			"OverdueDate" => null,
			"Name" => null,
			"Description" => null,
			"Parameters" => null,
			"ApproveMinPercent" => 50,
			"ApproveWaitForAll" => "N",
			"Comments" => "",
			"VotedCount" => 0,
			"TotalCount" => 0,
			"VotedPercent" => 0,
			"ApprovedPercent" => 0,
			"NotApprovedPercent" => 0,
			"ApprovedCount" => 0,
			"NotApprovedCount" => 0,
			"StatusMessage" => "",
			"SetStatusMessage" => "Y",
			"LastApprover" => null,
			"TimeoutDuration" => 0,
			"IsTimeout" => 0,
		);
	}

	public function SetStatusTitle($title='')
	{
		$rootActivity = $this->GetRootActivity();
		$stateService = $this->workflow->GetService("StateService");
		if($rootActivity instanceof CBPStateMachineWorkflowActivity)
		{
			$arState = $stateService->GetWorkflowState($this->GetWorkflowInstanceId());

			$arActivities = $rootActivity->CollectNestedActivities();

			foreach($arActivities as $activity)
				if($activity->GetName() == $arState["STATE_NAME"])
					break;

			$stateService->SetStateTitle(
				$this->GetWorkflowInstanceId(),
				$activity->Title.($title!=''?": ".$title:'')
			);
		}
		else
		{
			if($title!='')
				$stateService->SetStateTitle(
					$this->GetWorkflowInstanceId(),
					$title
				);
		}
	}

	public function Execute()
	{
		if ($this->isInEventActivityMode)
			return CBPActivityExecutionStatus::Closed;

		$this->Subscribe($this);

		$this->isInEventActivityMode = false;
		return CBPActivityExecutionStatus::Executing;
	}

	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$this->isInEventActivityMode = true;

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$arUsers = array();
		$arUsersTmp = $this->Users;
		if (!is_array($arUsersTmp))
			$arUsersTmp = array($arUsersTmp);

		if ($this->ApproveType == "any")
			$this->WriteToTrackingService(str_replace("#VAL#", "{=user:".implode("}, {=user:", $arUsersTmp)."}", GetMessage("BPAA_ACT_TRACK1")));
		elseif ($this->ApproveType == "all")
			$this->WriteToTrackingService(str_replace("#VAL#", "{=user:".implode("}, {=user:", $arUsersTmp)."}", GetMessage("BPAA_ACT_TRACK2")));
		elseif ($this->ApproveType == "vote")
			$this->WriteToTrackingService(str_replace("#VAL#", "{=user:".implode("}, {=user:", $arUsersTmp)."}", GetMessage("BPAA_ACT_TRACK3")));

		$l = strlen("user_");
		foreach ($arUsersTmp as $user)
		{
			if (substr($user, 0, $l) == "user_")
			{
				$user = intval(substr($user, $l));
				if ($user > 0)
					$arUsers[] = $user;
			}
			else
			{
				$arDSUsers = $documentService->GetUsersFromUserGroup($user, $documentId);
				foreach ($arDSUsers as $v)
				{
					$user = intval($v);
					if ($user > 0)
						$arUsers[] = $user;
				}
			}
		}

		$arParameters = $this->Parameters;
		if (!is_array($arParameters))
			$arParameters = array($arParameters);
		$arParameters["DOCUMENT_ID"] = $documentId;
		$arParameters["DOCUMENT_URL"] = $documentService->GetDocumentAdminPage($documentId);

		$taskService = $this->workflow->GetService("TaskService");
		$this->taskId = $taskService->CreateTask(
			array(
				"USERS" => $arUsers,
				"WORKFLOW_ID" => $this->GetWorkflowInstanceId(),
				"ACTIVITY" => "ApproveActivity",
				"ACTIVITY_NAME" => $this->name,
				"OVERDUE_DATE" => $this->OverdueDate,
				"NAME" => $this->Name,
				"DESCRIPTION" => $this->Description,
				"PARAMETERS" => $arParameters,
			)
		);

		$this->TotalCount = count($arUsers);
		if (!$this->IsPropertyExists("SetStatusMessage") || $this->SetStatusMessage == "Y")
			$this->AddStatusTitle(str_replace(array("#PERC#", "#REV#", "#TOT#"), array($this->VotedPercent, $this->VotedCount, $this->TotalCount), ($this->IsPropertyExists("StatusMessage") && strlen($this->StatusMessage) > 0) ? $this->StatusMessage : GetMessage("BPAA_ACT_INFO")));

		if ($this->IsPropertyExists("TimeoutDuration") && ($this->TimeoutDuration > 0))
		{
			$schedulerService = $this->workflow->GetService("SchedulerService");
			$this->subscriptionId = $schedulerService->SubscribeOnTime($this->workflow->GetInstanceId(), $this->name, time() + $this->TimeoutDuration);
		}

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}

	private function ReplaceTemplate($str, $ar)
	{
		$str = str_replace("%", "%2", $str);
		foreach ($ar as $key => $val)
		{
			$val = str_replace("%", "%2", $val);
			$val = str_replace("#", "%1", $val);
			$str = str_replace("#".$key."#", $val, $str);
		}
		$str = str_replace("%1", "#", $str);
		$str = str_replace("%2", "%", $str);

		return $str;
	}

	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$taskService = $this->workflow->GetService("TaskService");
		$taskService->DeleteTask($this->taskId);

		if ($this->IsPropertyExists("TimeoutDuration") && ($this->TimeoutDuration > 0))
		{
			$schedulerService = $this->workflow->GetService("SchedulerService");
			$schedulerService->UnSubscribeOnTime($this->subscriptionId);
		}

		$this->workflow->RemoveEventHandler($this->name, $eventHandler);

		$this->taskId = 0;
		$this->subscriptionId = 0;
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

	public function Cancel()
	{
		if (!$this->isInEventActivityMode && $this->taskId > 0)
			$this->Unsubscribe($this);

		return CBPActivityExecutionStatus::Closed;
	}

	protected function ExecuteOnApprove()
	{
		if (count($this->arActivities) <= 0)
		{
			$this->workflow->CloseActivity($this);
			return;
		}

		$this->WriteToTrackingService(GetMessage("BPAA_ACT_APPROVE"));

		$activity = $this->arActivities[0];
		$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->ExecuteActivity($activity);
	}

	protected function ExecuteOnNonApprove()
	{
		if (count($this->arActivities) <= 1)
		{
			$this->workflow->CloseActivity($this);
			return;
		}

		$this->WriteToTrackingService(GetMessage("BPAA_ACT_NONAPPROVE"));

		$activity = $this->arActivities[1];
		$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->ExecuteActivity($activity);
	}

	public function OnExternalEvent($arEventParameters = array())
	{
		if ($this->executionStatus == CBPActivityExecutionStatus::Closed)
			return;

		if ($this->IsPropertyExists("TimeoutDuration") && ($this->TimeoutDuration > 0))
		{
			if (array_key_exists("SchedulerService", $arEventParameters) && $arEventParameters["SchedulerService"] == "OnAgent")
			{
				$this->IsTimeout = 1;
				$this->Unsubscribe($this);
				$this->ExecuteOnNonApprove();
				return;
			}
		}

		if (!array_key_exists("USER_ID", $arEventParameters) || intval($arEventParameters["USER_ID"]) <= 0)
			return;
		if (!array_key_exists("APPROVE", $arEventParameters))
			return;

		$approve = ($arEventParameters["APPROVE"] ? true : false);

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$l = strlen("user_");
		$arUsers = array();
		$arUsersTmp = $this->Users;
		if (!is_array($arUsersTmp))
			$arUsersTmp = array($arUsersTmp);

		foreach ($arUsersTmp as $user)
		{
			if (substr($user, 0, $l) == "user_")
			{
				$user = intval(substr($user, $l));
				if ($user > 0)
					$arUsers[] = $user;
			}
			else
			{
				$arDSUsers = $documentService->GetUsersFromUserGroup($user, $documentId);
				foreach ($arDSUsers as $v)
				{
					$user = intval($v);
					if ($user > 0)
						$arUsers[] = $user;
				}
			}
		}

		$arEventParameters["USER_ID"] = intval($arEventParameters["USER_ID"]);
		if (!in_array($arEventParameters["USER_ID"], $arUsers))
			return;

		if ($this->IsPropertyExists("LastApprover"))
			$this->LastApprover = "user_".$arEventParameters["USER_ID"];

		if (!$this->IsPropertyExists("SetStatusMessage") || $this->SetStatusMessage == "Y")
			$this->DeleteStatusTitle(str_replace(array("#PERC#", "#REV#", "#TOT#"), array($this->VotedPercent, $this->VotedCount, $this->TotalCount), ($this->IsPropertyExists("StatusMessage") && strlen($this->StatusMessage) > 0) ? $this->StatusMessage : GetMessage("BPAA_ACT_INFO")));

		$taskService = $this->workflow->GetService("TaskService");
		$taskService->MarkCompleted($this->taskId, $arEventParameters["USER_ID"]);

		$dbUser = CUser::GetById($arEventParameters["USER_ID"]);
		if($arUser = $dbUser->Fetch())
			$this->Comments = $this->Comments.
				$arUser["NAME"]." ".$arUser["LAST_NAME"]." (".$arUser["LOGIN"]."): ".($approve?GetMessage("BPAA_LOG_Y"):GetMessage("BPAA_LOG_N"))."\n".
				(strlen($arEventParameters["COMMENT"]) > 0 ? GetMessage("BPAA_LOG_COMMENTS").": ".$arEventParameters["COMMENT"] : "")."\n";

		if ($approve)
		{
			$this->WriteToTrackingService(
				str_replace(
					array("#PERSON#", "#COMMENT#"),
					array("{=user:user_".$arEventParameters["USER_ID"]."}", (strlen($arEventParameters["COMMENT"]) > 0 ? ": ".$arEventParameters["COMMENT"] : "")),
					GetMessage("BPAA_ACT_APPROVE_TRACK")
				),
				$arEventParameters["USER_ID"]
			);
		}
		else
		{
			$this->WriteToTrackingService(
				str_replace(
					array("#PERSON#", "#COMMENT#"),
					array("{=user:user_".$arEventParameters["USER_ID"]."}", (strlen($arEventParameters["COMMENT"]) > 0 ? ": ".$arEventParameters["COMMENT"] : "")),
					GetMessage("BPAA_ACT_NONAPPROVE_TRACK")
				),
				$arEventParameters["USER_ID"]
			);
		}

		$result = "Continue";

		$this->arApproveResults[$arEventParameters["USER_ID"]] = $approve;

		if($approve)
			$this->ApprovedCount = $this->ApprovedCount + 1;
		else
			$this->NotApprovedCount = $this->NotApprovedCount + 1;

		$this->VotedCount = count($this->arApproveResults);
		$this->VotedPercent = intval($this->VotedCount/$this->TotalCount*100);
		$this->ApprovedPercent = intval($this->ApprovedCount/$this->TotalCount*100);
		$this->NotApprovedPercent = intval($this->NotApprovedCount/$this->TotalCount*100);

		if ($this->ApproveType == "any")
		{
			$result = ($approve ? "Approve" : "NonApprove");
		}
		elseif ($this->ApproveType == "all" || ($this->ApproveType == "vote" && $this->ApproveMinPercent>=100))
		{
			if (!$approve)
			{
				$result = "NonApprove";
			}
			else
			{
				$allAproved = true;
				foreach ($arUsers as $userId)
				{
					if (!isset($this->arApproveResults[$userId]))	// && $this->arApproveResults[$userId]!==true
						$allAproved = false;
				}

				if ($allAproved)
					$result = "Approve";
			}
		}
		elseif ($this->ApproveType == "vote")
		{
			if($this->ApproveWaitForAll == "Y")
			{
				if($this->VotedPercent==100)
				{
					if ($this->ApprovedPercent > $this->ApproveMinPercent)
						$result = "Approve";
					else
						$result = "NonApprove";
				}
			}
			else
			{
				if ($this->ApprovedPercent > $this->ApproveMinPercent)
					$result = "Approve";
				elseif(($this->VotedCount-$this->ApprovedCount)/$this->TotalCount*100 >= 100 - $this->ApproveMinPercent)
					$result = "NonApprove";
			}
		}

		if ($result != "Continue")
		{
			$this->Unsubscribe($this);

			//$this->SetStatusTitle();

			if ($result == "Approve")
				$this->ExecuteOnApprove();
			else
				$this->ExecuteOnNonApprove();
		}
		else
		{
			if (!$this->IsPropertyExists("SetStatusMessage") || $this->SetStatusMessage == "Y")
				$this->AddStatusTitle(str_replace(array("#PERC#", "#REV#", "#TOT#"), array($this->VotedPercent, $this->VotedCount, $this->TotalCount), ($this->IsPropertyExists("StatusMessage") && strlen($this->StatusMessage) > 0) ? $this->StatusMessage : GetMessage("BPAA_ACT_INFO")));
		}
	}

	protected function OnEvent(CBPActivity $sender)
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->CloseActivity($this);
	}

	public static function ShowTaskForm($arTask, $userId, $userName = "")
	{
		$form =
			'<tr><td valign="top" width="40%" align="right" class="bizproc-field-name">'.GetMessage("BPAA_ACT_COMMENT").':</td>'.
			'<td valign="top" width="60%" class="bizproc-field-value">'.
			'<textarea rows="3" cols="50" name="task_comment"></textarea>'.
			'</td></tr>';

		$buttons =
			'<input type="submit" name="approve" value="'.GetMessage("BPAA_ACT_BUTTON1").'"/>'.
			'<input type="submit" name="nonapprove" value="'.GetMessage("BPAA_ACT_BUTTON2").'"/>';

		return array($form, $buttons);
	}

	public static function PostTaskForm($arTask, $userId, $arRequest, &$arErrors, $userName = "")
	{
		$arErrors = array();

		try
		{
			$userId = intval($userId);
			if ($userId <= 0)
				throw new CBPArgumentNullException("userId");

			$arEventParameters = array(
				"USER_ID" => $userId,
				"USER_NAME" => $userName,
				"COMMENT" => $arRequest["task_comment"],
			);

			if (strlen($arRequest["approve"]) > 0)
				$arEventParameters["APPROVE"] = true;
			elseif (strlen($arRequest["nonapprove"]) > 0)
				$arEventParameters["APPROVE"] = false;
			else
				throw new CBPNotSupportedException(GetMessage("BPAA_ACT_NO_ACTION"));

			CBPRuntime::SendExternalEvent($arTask["WORKFLOW_ID"], $arTask["ACTIVITY_NAME"], $arEventParameters);

			return true;
		}
		catch (Exception $e)
		{
			$arErrors[] = array(
				"code" => $e->getCode(),
				"message" => $e->getMessage(),
				"file" => $e->getFile()." [".$e->getLine()."]",
			);
		}

		return false;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!array_key_exists("Users", $arTestProperties))
		{
			$bUsersFieldEmpty = true;
		}
		else
		{
			if (!is_array($arTestProperties["Users"]))
				$arTestProperties["Users"] = array($arTestProperties["Users"]);

			$bUsersFieldEmpty = true;
			foreach ($arTestProperties["Users"] as $userId)
			{
				if (strlen(trim($userId)) > 0)
				{
					$bUsersFieldEmpty = false;
					break;
				}
			}
		}

		if ($bUsersFieldEmpty)
			$arErrors[] = array("code" => "NotExist", "parameter" => "Users", "message" => GetMessage("BPAA_ACT_PROP_EMPTY1"));

		if (!array_key_exists("ApproveType", $arTestProperties))
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "ApproveType", "message" => GetMessage("BPAA_ACT_PROP_EMPTY2"));
		}
		else
		{
			if (!in_array($arTestProperties["ApproveType"], array("any", "all", "vote")))
				$arErrors[] = array("code" => "NotInRange", "parameter" => "ApproveType", "message" => GetMessage("BPAA_ACT_PROP_EMPTY3"));
		}

		if (!array_key_exists("Name", $arTestProperties) || strlen($arTestProperties["Name"]) <= 0)
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "Name", "message" => GetMessage("BPAA_ACT_PROP_EMPTY4"));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"Users" => "approve_users",
			"ApproveType" => "approve_type",
			"ApproveMinPercent" => "approve_percent",
			"OverdueDate" => "approve_overdue_date",
			"Name" => "approve_name",
			"Description" => "approve_description",
			"Parameters" => "approve_parameters",
			"ApproveWaitForAll" => "approve_wait",
			"StatusMessage" => "status_message",
			"SetStatusMessage" => "set_status_message",
			"TimeoutDuration" => "timeout_duration",
		);

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = Array();
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				foreach ($arMap as $k => $v)
				{
					if (array_key_exists($k, $arCurrentActivity["Properties"]))
					{
						if ($k == "Users")
						{
							$arCurrentValues[$arMap[$k]] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"][$k], $arWorkflowTemplate, $documentType);
						}
						elseif ($k == "TimeoutDuration")
						{
							$arCurrentValues["timeout_duration"] = $arCurrentActivity["Properties"]["TimeoutDuration"];
							if (!preg_match('#^{=[A-Za-z0-9_]+:[A-Za-z0-9_]+}$#i', $arCurrentValues["timeout_duration"]))
							{
								$arCurrentValues["timeout_duration"] = intval($arCurrentValues["timeout_duration"]);
								$arCurrentValues["timeout_duration_type"] = "s";
								if ($arCurrentValues["timeout_duration"] % (3600 * 24) == 0)
								{
									$arCurrentValues["timeout_duration"] = $arCurrentValues["timeout_duration"] / (3600 * 24);
									$arCurrentValues["timeout_duration_type"] = "d";
								}
								elseif ($arCurrentValues["timeout_duration"] % 3600 == 0)
								{
									$arCurrentValues["timeout_duration"] = $arCurrentValues["timeout_duration"] / 3600;
									$arCurrentValues["timeout_duration_type"] = "h";
								}
								elseif ($arCurrentValues["timeout_duration"] % 60 == 0)
								{
									$arCurrentValues["timeout_duration"] = $arCurrentValues["timeout_duration"] / 60;
									$arCurrentValues["timeout_duration_type"] = "m";
								}
							}
						}
						else
						{
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
						}
					}
					else
					{
						$arCurrentValues[$arMap[$k]] = "";
					}
				}
			}
			else
			{
				foreach ($arMap as $k => $v)
					$arCurrentValues[$arMap[$k]] = "";
			}

			if(strlen($arCurrentValues["approve_wait"])<=0)
				$arCurrentValues["approve_wait"] = "N";

			if(strlen($arCurrentValues["approve_percent"])<=0)
				 $arCurrentValues["approve_percent"] = "50";
		}

		if (strlen($arCurrentValues['status_message']) <= 0)
			$arCurrentValues['status_message'] = GetMessage("BPAA_ACT_INFO");

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"arDocumentFields" => $arDocumentFields,
				"formName" => $formName,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"approve_users" => "Users",
			"approve_type" => "ApproveType",
			"approve_overdue_date" => "OverdueDate",
			"approve_percent" => "ApproveMinPercent",
			"approve_wait" => "ApproveWaitForAll",
			"approve_name" => "Name",
			"approve_description" => "Description",
			"approve_parameters" => "Parameters",
			"status_message" => "StatusMessage",
			"set_status_message" => "SetStatusMessage",
			"timeout_duration" => "TimeoutDuration",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "approve_users" || $key == "timeout_duration")
				continue;

			if(strlen($arCurrentValues[$key."_X"])>0)
				$arProperties[$value] = $arCurrentValues[$key."_X"];
			else
				$arProperties[$value] = $arCurrentValues[$key];
		}

		$arProperties["Users"] = CBPHelper::UsersStringToArray($arCurrentValues["approve_users"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		if (!preg_match('#^{=[A-Za-z0-9_]+:[A-Za-z0-9_]+}$#i', $arCurrentValues["timeout_duration"]))
		{
			$delayType = $arCurrentValues["timeout_duration_type"];
			$arProperties["TimeoutDuration"] = intval($arCurrentValues["timeout_duration"]) * ($delayType == "s" ? 1 : ($delayType == "m" ? 60 : ($delayType == "h" ? 3600 : 3600 * 24)));
		}
		else
		{
			$arProperties["TimeoutDuration"] = $arCurrentValues["timeout_duration"];
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
