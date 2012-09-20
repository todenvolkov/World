<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPGetUserActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "", "UserType" => null, "UserParameter" => null, "ReserveUserParameter" => null, "MaxLevel" => null, "GetUser" => null);
	}

	private function GetUsersList($arUsersList, $bSkipAbsent = true)
	{
		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		if (!is_array($arUsersList))
			$arUsersList = array($arUsersList);

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		if (!CModule::IncludeModule("intranet"))
			$bSkipAbsent = false;

		$arUsers = array();

		$l = strlen("user_");
		foreach ($arUsersList as $user)
		{
			if (substr($user, 0, $l) == "user_")
			{
				$user = intval(substr($user, $l));
				if ($user > 0 && !in_array($user, $arUsers) && (!$bSkipAbsent || !CIntranetUtils::IsUserAbsent($user)))
					$arUsers[] = $user;
			}
			else
			{
				$arDSUsers = $documentService->GetUsersFromUserGroup($user, $documentId);
				foreach ($arDSUsers as $v)
				{
					$user = intval($v);
					if ($user > 0 && !in_array($user, $arUsers) && (!$bSkipAbsent || !CIntranetUtils::IsUserAbsent($user)))
						$arUsers[] = $user;
				}
			}
		}

		return $arUsers;
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("intranet"))
			$this->UserType = "random";

		$arUsers = array();
		if ($this->UserType == "boss")
		{
			$arUsers = $this->GetUsersList($this->UserParameter, false);
			if (count($arUsers) <= 0)
			{
				$this->GetUser = null;
				return CBPActivityExecutionStatus::Closed;
			}

			$userId = $arUsers[0];

			$arUserDepartmentId = null;
			$dbUser = CUser::GetByID($userId);
			if ($arUser = $dbUser->Fetch())
			{
				if (isset($arUser["UF_DEPARTMENT"]))
				{
					if (!is_array($arUser["UF_DEPARTMENT"]))
						$arUser["UF_DEPARTMENT"] = array($arUser["UF_DEPARTMENT"]);

					foreach ($arUser["UF_DEPARTMENT"] as $v)
						$arUserDepartmentId[] = $v;
				}
			}

			$arUserDepartments = array();
			$departmentIBlockId = COption::GetOptionInt('intranet', 'iblock_structure');
			foreach ($arUserDepartmentId as $departmentId)
			{
				$ar = array();
				$dbPath = CIBlockSection::GetNavChain($departmentIBlockId, $departmentId);
				while ($arPath = $dbPath->GetNext())
					$ar[] = $arPath["ID"];

				$ar = array_reverse($ar);

				$i = -1;
				foreach ($ar as $v)
				{
					$i++;
					if (!isset($arUserDepartments[$i]))
						$arUserDepartments[$i] = array();

					$arUserDepartments[$i][] = $v;
				}
			}

			$arBoss = array();
			foreach ($arUserDepartments as $level => $arV)
			{
				if ($this->MaxLevel > 0 && $level + 1 > $this->MaxLevel)
					break;

				$dbRes = CIBlockSection::GetList(
					array(),
					array(
						'IBLOCK_ID' => $departmentIBlockId,
						'ID' => $arV,
					),
					false,
					array('ID', 'UF_HEAD')
				);
				while ($arRes = $dbRes->Fetch())
				{
					if (!in_array($arRes["UF_HEAD"], $arBoss) && $arRes["UF_HEAD"] != $userId)
						$arBoss[] = $arRes["UF_HEAD"];
				}
			}

			$ar = array();
			foreach ($arBoss as $v)
				$ar[] = "user_".$v;

			if (count($ar) == 0)
				$ar = null;
			elseif (count($ar) == 1)
				$ar = $ar[0];

			$this->GetUser = $ar;

			return CBPActivityExecutionStatus::Closed;
		}
		else
		{
			$arUsers = $this->GetUsersList($this->UserParameter, true);
			if (count($arUsers) > 0)
			{
				mt_srand(time());
				$rnd = mt_rand(0, count($arUsers) - 1);
				$this->GetUser = "user_".$arUsers[$rnd];

				return CBPActivityExecutionStatus::Closed;
			}
		}

		$arReserveUsers = $this->GetUsersList($this->ReserveUserParameter, true);
		if (count($arReserveUsers) > 0)
			$this->GetUser = "user_".$arReserveUsers[0];

		return CBPActivityExecutionStatus::Closed;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array("user_type" => "", "user_parameter" => "", "reserve_user_parameter" => "", "max_level" => 1);

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				$arCurrentValues["user_type"] = $arCurrentActivity["Properties"]["UserType"];
				$arCurrentValues["max_level"] = $arCurrentActivity["Properties"]["MaxLevel"];
				$arCurrentValues["user_parameter"] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"]["UserParameter"], $arWorkflowTemplate, $documentType);
				$arCurrentValues["reserve_user_parameter"] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"]["ReserveUserParameter"], $arWorkflowTemplate, $documentType);
			}
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arProperties = array();

		if (!isset($arCurrentValues["user_type"]) || !in_array($arCurrentValues["user_type"], array("boss", "random")))
			$arCurrentValues["user_type"] = "random";
		$arProperties["UserType"] = $arCurrentValues["user_type"];

		if (!isset($arCurrentValues["max_level"]) || $arCurrentValues["max_level"] < 1 || $arCurrentValues["max_level"] > 10)
			$arCurrentValues["max_level"] = 1;
		$arProperties["MaxLevel"] = $arCurrentValues["max_level"];

		$arProperties["UserParameter"] = CBPHelper::UsersStringToArray($arCurrentValues["user_parameter"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arProperties["ReserveUserParameter"] = CBPHelper::UsersStringToArray($arCurrentValues["reserve_user_parameter"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}
?>