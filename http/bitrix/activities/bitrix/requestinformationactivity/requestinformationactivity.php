<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPRequestInformationActivity
	extends CBPActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	private $taskId = 0;
	private $isInEventActivityMode = false;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Users" => null,
			"Name" => null,
			"Description" => null,
			"Parameters" => null,
			"OverdueDate" => null,
			"RequestedInformation" => null,
			"ResponcedInformation" => null,
		);
	}

	public function SetStatusTitle($title = '')
	{
		$rootActivity = $this->GetRootActivity();
		$stateService = $this->workflow->GetService("StateService");
		if ($rootActivity instanceof CBPStateMachineWorkflowActivity)
		{
			$arState = $stateService->GetWorkflowState($this->GetWorkflowInstanceId());

			$arActivities = $rootActivity->CollectNestedActivities();

			foreach ($arActivities as $activity)
				if ($activity->GetName() == $arState["STATE_NAME"])
					break;

			$stateService->SetStateTitle(
				$this->GetWorkflowInstanceId(),
				$activity->Title.($title != '' ? ": ".$title : '')
			);
		}
		else
		{
			if ($title != '')
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

	private function ExtractUsers()
	{
		$arUsers = array();

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$arUsersTmp = $this->Users;

		if (!is_array($arUsersTmp))
			$arUsersTmp = array($arUsersTmp);

		if (is_array($arUsersTmp))
		{
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
					$arDSUsers = $documentService->GetUsersFromUserGroup($user, $this->GetDocumentId());
					foreach ($arDSUsers as $v)
					{
						$user = intval($v);
						if ($user > 0)
							$arUsers[] = $user;
					}
				}
			}
		}

		return $arUsers;
	}

	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$this->isInEventActivityMode = true;

		$arUsers = array();
		$arUsersTmp = $this->Users;
		if (!is_array($arUsersTmp))
			$arUsersTmp = array($arUsersTmp);

		$this->WriteToTrackingService(str_replace("#VAL#", "{=user:".implode("}, {=user:", $arUsersTmp)."}", GetMessage("BPRIA_ACT_TRACK1")));

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$arUsers = $this->ExtractUsers();

		$arParameters = $this->Parameters;
		if (!is_array($arParameters))
			$arParameters = array($arParameters);
		$arParameters["DOCUMENT_ID"] = $documentId;
		$arParameters["DOCUMENT_URL"] = $documentService->GetDocumentAdminPage($documentId);
		$arParameters["DOCUMENT_TYPE"] = $this->GetDocumentType();
		$arParameters["FIELD_TYPES"] = $documentService->GetDocumentFieldTypes($arParameters["DOCUMENT_TYPE"]);
		$arParameters["REQUEST"] = array();

		$requestedInformation = $this->RequestedInformation;
		if ($requestedInformation && is_array($requestedInformation) && count($requestedInformation) > 0)
		{
			foreach ($requestedInformation as $v)
				$arParameters["REQUEST"][] = $v;
		}

		$taskService = $this->workflow->GetService("TaskService");
		$this->taskId = $taskService->CreateTask(
			array(
				"USERS" => $arUsers,
				"WORKFLOW_ID" => $this->GetWorkflowInstanceId(),
				"ACTIVITY" => "RequestInformationActivity",
				"ACTIVITY_NAME" => $this->name,
				"OVERDUE_DATE" => $this->OverdueDate,
				"NAME" => $this->Name,
				"DESCRIPTION" => $this->Description,
				"PARAMETERS" => $arParameters,
			)
		);

		$this->SetStatusTitle(GetMessage("BPRIA_ACT_INFO"));

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}

	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$taskService = $this->workflow->GetService("TaskService");
		$taskService->DeleteTask($this->taskId);

		$this->workflow->RemoveEventHandler($this->name, $eventHandler);

		$this->taskId = 0;
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

	public function OnExternalEvent($arEventParameters = array())
	{
		if ($this->executionStatus == CBPActivityExecutionStatus::Closed)
			return;

		if (!array_key_exists("USER_ID", $arEventParameters) || intval($arEventParameters["USER_ID"]) <= 0)
			return;

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$arUsers = $this->ExtractUsers();

		$arEventParameters["USER_ID"] = intval($arEventParameters["USER_ID"]);
		if (!in_array($arEventParameters["USER_ID"], $arUsers))
			return;

		$taskService = $this->workflow->GetService("TaskService");
		$taskService->MarkCompleted($this->taskId, $arEventParameters["USER_ID"]);

//		$dbUser = CUser::GetById($arEventParameters["USER_ID"]);
//		if ($arUser = $dbUser->Fetch())
//			$this->Comments = $this->Comments.
//				$arUser["NAME"]." ".$arUser["LAST_NAME"]." (".$arUser["LOGIN"]."): ".GetMessage("BPRIA_LOG_Y")."\n".
//				(strlen($arEventParameters["COMMENT"]) > 0 ? GetMessage("BPRIA_LOG_COMMENTS").": ".$arEventParameters["COMMENT"] : "")."\n";

		$this->WriteToTrackingService(
			str_replace(
				array("#PERSON#", "#COMMENT#"),
				array("{=user:".$arEventParameters["USER_ID"]."}", (strlen($arEventParameters["COMMENT"]) > 0 ? ": ".$arEventParameters["COMMENT"] : "")),
				GetMessage("BPRIA_ACT_APPROVE_TRACK")
			),
			$arEventParameters["USER_ID"]
		);

		$this->ResponcedInformation = $arEventParameters["RESPONCE"];
		$rootActivity->SetVariablesTypes($arEventParameters["RESPONCE_TYPES"]);
		$rootActivity->SetVariables($arEventParameters["RESPONCE"]);

		$this->Unsubscribe($this);
		$this->SetStatusTitle();

		$this->workflow->CloseActivity($this);
	}

	protected function OnEvent(CBPActivity $sender)
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->CloseActivity($this);
	}

	public static function ShowTaskForm($arTask, $userId, $userName = "", $arRequest = null)
	{
		$form = "";

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		if ($arTask["PARAMETERS"] && is_array($arTask["PARAMETERS"]) && count($arTask["PARAMETERS"]) > 0
			&& $arTask["PARAMETERS"]["REQUEST"] && is_array($arTask["PARAMETERS"]["REQUEST"]) && count($arTask["PARAMETERS"]["REQUEST"]) > 0)
		{
			//CBPDocument::AddShowParameterInit($arTask["PARAMETERS"]["DOCUMENT_ID"][0], "only_users", $arTask["PARAMETERS"]["DOCUMENT_ID"][2], $arTask["PARAMETERS"]["DOCUMENT_ID"][1]);

			foreach ($arTask["PARAMETERS"]["REQUEST"] as $parameter)
			{
				if (strlen($parameter["Name"]) <= 0)
					continue;

				$form .=
					'<tr><td valign="top" width="40%" align="right" class="bizproc-field-name">'.($parameter["Required"] ? '<span style="color:#FF0000;">*</span> ' : '').''.$parameter["Title"].':</td>'.
					'<td valign="top" width="60%" class="bizproc-field-value">';

				if ($arTask["PARAMETERS"]["FIELD_TYPES"][$parameter["Type"]]["BaseType"] == "select")
				{
					$op = array();
					foreach ($parameter["Options"] as $v)
						$op[$v[0]] = $v[1];
					$parameter["Options"] = $op;
				}

				if ($arRequest === null)
					$realValue = $parameter["Default"];
				else
					$realValue = $arRequest[$parameter["Name"]];

				$form .= $documentService->GetGUIFieldEdit(
					$arTask["PARAMETERS"]["DOCUMENT_TYPE"],
					"task_form1",
					$parameter["Name"],
					$realValue,
					$parameter,
					false
				);

				$form .= '</td></tr>';
			}
		}

		$form .=
			'<tr><td valign="top" width="40%" align="right" class="bizproc-field-name">'.GetMessage("BPRIA_ACT_COMMENT").':</td>'.
			'<td valign="top" width="60%" class="bizproc-field-value">'.
			'<textarea rows="3" cols="50" name="task_comment"></textarea>'.
			'</td></tr>';

		$buttons =
			'<input type="submit" name="approve" value="'.GetMessage("BPRIA_ACT_BUTTON1").'"/>';

		return array($form, $buttons);
	}

	public static function PostTaskForm($arTask, $userId, $arRequest, &$arErrors, $userName = "")
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		try
		{
			$userId = intval($userId);
			if ($userId <= 0)
				throw new CBPArgumentNullException("userId");

			$arEventParameters = array(
				"USER_ID" => $userId,
				"USER_NAME" => $userName,
				"COMMENT" => $arRequest["task_comment"],
				"RESPONCE" => array(),
				"RESPONCE_TYPES" => array(),
			);

			if ($arTask["PARAMETERS"] && is_array($arTask["PARAMETERS"]) && count($arTask["PARAMETERS"]) > 0
				&& $arTask["PARAMETERS"]["REQUEST"] && is_array($arTask["PARAMETERS"]["REQUEST"]) && count($arTask["PARAMETERS"]["REQUEST"]) > 0)
			{
				$arRequest = $_REQUEST;

				foreach ($_FILES as $k => $v)
				{
					if (array_key_exists("name", $v))
					{
						if (is_array($v["name"]))
						{
							$ks = array_keys($v["name"]);
							for ($i = 0, $cnt = count($ks); $i < $cnt; $i++)
							{
								$ar = array();
								foreach ($v as $k1 => $v1)
									$ar[$k1] = $v1[$ks[$i]];

								$arRequest[$k][] = $ar;
							}
						}
						else
						{
							$arRequest[$k] = $v;
						}
					}
				}

				foreach ($arTask["PARAMETERS"]["REQUEST"] as $parameter)
				{
					$arErrorsTmp = array();

					if ($arTask["PARAMETERS"]["FIELD_TYPES"][$parameter["Type"]]["BaseType"] == "select")
					{
						$op = array();
						foreach ($parameter["Options"] as $v)
							$op[$v[0]] = $v[1];
						$parameter["Options"] = $op;
					}

					$arEventParameters["RESPONCE"][$parameter["Name"]] = $documentService->SetGUIFieldEdit(
						$arTask["PARAMETERS"]["DOCUMENT_TYPE"],
						$parameter["Name"],
						$arRequest,
						$arErrorsTmp,
						$parameter
					);

					if (count($arErrorsTmp) > 0)
					{
						$bCanStartWorkflow = false;
						foreach ($arErrorsTmp as $e)
							$arResult["ErrorMessage"] .= $e["message"]."<br />";
					}

					if ($parameter["Required"] 
						&& (is_array($arEventParameters["RESPONCE"][$parameter["Name"]]) && count($arEventParameters["RESPONCE"][$parameter["Name"]]) <= 0 || !is_array($arEventParameters["RESPONCE"][$parameter["Name"]]) && $arEventParameters["RESPONCE"][$parameter["Name"]] === null)
						)
						throw new CBPArgumentNullException($parameter["Name"], str_replace("#PARAM#", htmlspecialchars($parameter["Title"]), GetMessage("BPRIA_ARGUMENT_NULL")));

					$arEventParameters["RESPONCE_TYPES"][$parameter["Name"]] = array("Type" => $parameter["Type"]);
				}
			}

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
			$arErrors[] = array("code" => "NotExist", "parameter" => "Users", "message" => GetMessage("BPRIA_ACT_PROP_EMPTY1"));

		if (!array_key_exists("Name", $arTestProperties) || strlen($arTestProperties["Name"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "Name", "message" => GetMessage("BPRIA_ACT_PROP_EMPTY4"));

		if (!array_key_exists("RequestedInformation", $arTestProperties) || !is_array($arTestProperties["RequestedInformation"]) || count($arTestProperties["RequestedInformation"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "RequestedInformation", "message" => GetMessage("BPRIA_ACT_PROP_EMPTY2"));

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null)
	{
		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"Users" => "requested_users",
			"OverdueDate" => "requested_overdue_date",
			"Name" => "requested_name",
			"Description" => "requested_description",
			"Parameters" => "requested_parameters",
			"RequestedInformation" => "requested_information",
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
							$arCurrentValues[$arMap[$k]] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"][$k], $arWorkflowTemplate, $documentType);
						else
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
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
		}

		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);
		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		$ar = array();
		$j = -1;
		if (array_key_exists("requested_information", $arCurrentValues) && is_array($arCurrentValues["requested_information"]))
		{
			for ($i = 0, $cnt = count($arCurrentValues["requested_information"]); $i < $cnt; $i++)
			{
				if (strlen($arCurrentValues["requested_information"][$i]["Name"]) <= 0)
					continue;

				$j++;
				$ar[$j] = $arCurrentValues["requested_information"][$i];
				$ar[$j]["Required"] = ($arCurrentValues["requested_information"][$i]["Required"] ? "Y" : "N");
				$ar[$j]["Multiple"] = ($arCurrentValues["requested_information"][$i]["Multiple"] ? "Y" : "N");

				$ar[$j]["Options"] = "";
				if (is_array($arCurrentValues["requested_information"][$i]["Options"]) && count($arCurrentValues["requested_information"][$i]["Options"]) > 0)
				{
					foreach ($arCurrentValues["requested_information"][$i]["Options"] as $v)
					{
						if (strlen($ar[$j]["Options"]) > 0)
							$ar[$j]["Options"] .= "\r\n";
						$ar[$j]["Options"] .= "[".$v[1]."] ".$v[0];
					}
				}
			}
		}

		$arCurrentValues["requested_information"] = $ar;

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFields", $arDocumentFields, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"arDocumentFields" => $arDocumentFields,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
				"formName" => $formName,
				"popupWindow" => &$popupWindow,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"requested_users" => "Users",
			"requested_overdue_date" => "OverdueDate",
			"requested_name" => "Name",
			"requested_description" => "Description",
			"requested_parameters" => "Parameters",
			"requested_information" => "RequestedInformation",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "requested_users")
				continue;
			$arProperties[$value] = $arCurrentValues[$key];
		}

		$arProperties["Users"] = CBPHelper::UsersStringToArray($arCurrentValues["requested_users"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$ar = array();
		$j = -1;

		if (array_key_exists("RequestedInformation", $arProperties) && is_array($arProperties["RequestedInformation"]))
		{
			foreach ($arProperties["RequestedInformation"] as $arRI)
			{
				if (strlen($arRI["Name"]) <= 0)
					continue;

				$j++;
				$ar[$j] = $arRI;
				$ar[$j]["Required"] = ($arRI["Required"] == "Y");
				$ar[$j]["Multiple"] = ($arRI["Multiple"] == "Y");

				$ar[$j]["Options"] = array();
				if (strlen($arRI["Options"]) > 0)
				{
					$a = explode("\n", $arRI["Options"]);
					foreach ($a as $v)
					{
						$v = trim(trim($v), "\r\n");
						$v1 = $v2 = $v;
						if (substr($v, 0, 1) == "[" && strpos($v, "]") !== false)
						{
							$v1 = substr($v, 1, strpos($v, "]") - 1);
							$v2 = trim(substr($v, strpos($v, "]") + 1));
						}
						$ar[$j]["Options"][] = array($v2, $v1);
					}
				}

				if ($ar[$j]["Type"] == "user" && strlen($ar[$j]["Default"]) > 0)
				{
					$ar[$j]["Default"] = CBPHelper::UsersStringToArray($ar[$j]["Default"], $documentType, $arErrors);
					if (!$ar[$j]["Multiple"])
						$ar[$j]["Default"] = $ar[$j]["Default"][0];
				}
			}
		}

		$arProperties["RequestedInformation"] = $ar;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		if (is_array($arProperties["RequestedInformation"]))
		{
			foreach ($arProperties["RequestedInformation"] as $v)
			{
				$arWorkflowVariables[$v["Name"]] = $v;
				$arWorkflowVariables[$v["Name"]]["Name"] = $v["Title"];
				if (is_array($v["Options"]) && count($v["Options"]) > 0)
				{
					$a = array();
					foreach ($v["Options"] as $v1)
						$a[$v1[1]] = $v1[0];
					$v["Options"] = $a;
					$arWorkflowVariables[$v["Name"]]["Options"] = $v["Options"];
				}
			}
		}

		return true;
	}
}
?>