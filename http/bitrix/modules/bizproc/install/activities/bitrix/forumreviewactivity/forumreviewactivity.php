<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPForumReviewActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"IBlockId" => "",
			"ForumId" => "",
			"ForumUser" => "",
			"ForumPostMessage" => "",
		);
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("forum"))
			return CBPActivityExecutionStatus::Closed;
		if (!CModule::IncludeModule("iblock"))
			return CBPActivityExecutionStatus::Closed;

		$forumId = intval($this->ForumId);

		if ($forumId <= 0)
			return CBPActivityExecutionStatus::Closed;

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$iblockId = $this->IBlockId;

		$dbResult = CIBlockElement::GetProperty($iblockId, $documentId[2], false, false, array("CODE" => "FORUM_TOPIC_ID"));
		$arResult = $dbResult->Fetch();
		if (!$arResult)
		{
			$obProperty = new CIBlockProperty();
			$obProperty->Add(
				array(
					"IBLOCK_ID" => $iblockId,
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "N",
					"MULTIPLE" => "N",
					"NAME" => "Forum topic",
					"CODE" => "FORUM_TOPIC_ID"
				)
			);
			$obProperty->Add(
				array(
					"IBLOCK_ID" => $iblockId,
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "N",
					"MULTIPLE" => "N",
					"NAME" => "Forum message count",
					"CODE" => "FORUM_MESSAGE_CNT"
				)
			);

			$dbResult = CIBlockElement::GetProperty($iblockId, $documentId[2], false, false, array("CODE" => "FORUM_TOPIC_ID"));
			$arResult = $dbResult->Fetch();
		}

		$forumTopicId = intval($arResult["VALUE"]);

		$arForumUser = array();
		$arForumUserTmp = $this->ForumUser;
		if (!is_array($arForumUserTmp) && intval($arForumUserTmp) > 0 && intval($arForumUserTmp)."|" == $arForumUserTmp."|")
			$arForumUserTmp = array($arForumUserTmp);

		if (is_array($arForumUserTmp))
		{
			$l = strlen("user_");
			foreach ($arForumUserTmp as $user)
			{
				if (substr($user, 0, $l) == "user_")
				{
					$user = intval(substr($user, $l));
					if ($user > 0)
						$arForumUser[] = $user;
				}
				else
				{
					$user = intval($user);
					if ($user > 0)
						$arForumUser[] = $user;
				}
			}
		}

		$forumUserId = 1;
		$forumUserName = "Admin";
		if (count($arForumUser) > 0)
		{
			$forumUserId = $arForumUser[0];
			$dbResult = CUser::GetByID($forumUserId);
			if ($arResult = $dbResult->Fetch())
			{
				$forumUserName = $arResult["NAME"].(strlen($arResult["NAME"]) > 0 && strlen($arResult["LAST_NAME"]) > 0 ? " " : "").$arResult["LAST_NAME"];
				if (strlen($forumUserName) <= 0)
					$forumUserName = $arResult["LOGIN"];
			}
		}

		$newTopic = "N";
		if ($forumTopicId <= 0)
		{
			$documentService = $this->workflow->GetService("DocumentService");
			$document = $documentService->GetDocument($documentId);
			$newTopic = "Y";

			$arFields = array(
				"TITLE" => $document["NAME"],
				"FORUM_ID" => $forumId,
				"USER_START_ID"	=> $forumUserId,
				"USER_START_NAME" => $forumUserName,
				"LAST_POSTER_NAME" => $forumUserName,
				"APPROVED" => "Y"
			);
			$forumTopicId = CForumTopic::Add($arFields);
			CIBlockElement::SetPropertyValues($documentId[2], $iblockId, $forumTopicId, "FORUM_TOPIC_ID");
		}

		$arFields = array(
			"POST_MESSAGE" => $this->ForumPostMessage,
			"AUTHOR_ID" => $forumUserId,
			"AUTHOR_NAME" => $forumUserName,
			"FORUM_ID" => $forumId,
			"TOPIC_ID" => $forumTopicId,
			"APPROVED" => "Y",
			"NEW_TOPIC" => $newTopic,
			//"PARAM1" => "IB",
			"PARAM2" => $documentId[2]
		);
		$forumMessageId = CForumMessage::Add($arFields, false, array("SKIP_INDEXING" => "Y", "SKIP_STATISTIC" => "N"));

		return CBPActivityExecutionStatus::Closed;
	}
}
?>