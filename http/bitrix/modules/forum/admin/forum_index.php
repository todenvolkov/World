<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$forumModulePermissions = $APPLICATION->GetGroupRight("forum");
if ($forumModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("FFI_TITLE"));
if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
else
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminPage->ShowSectionIndex("menu_forum", "forum");

if($_REQUEST["mode"] == "list")
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
?>

<h2><?echo GetMessage("FFI_TAB_DESCR")?></h2>

<table cellpadding="0" cellspacing="0" border="0" class="list-table" style="width:auto">
	<tr>
		<td><?echo GetMessage("FFI_FORUM_CNT") ?>:</td>
		<td align="right">
			<?
			$totalCnt = 0;
			$activeCnt = 0;
			$topicsCnt = 0;
			$messagesCnt = 0;
			$arLangCnt = array();
			$dbResultList = CForumNew::GetList(array(), array());
			while ($arResult = $dbResultList->Fetch())
			{
				$totalCnt++;
				if ($arResult["ACTIVE"] == "Y")
					$activeCnt++;

				$arForumSite_tmp = CForumNew::GetSites($arResult["ID"]);
				foreach ($arForumSite_tmp as $key => $value)
				{
					if (!array_key_exists($key, $arLangCnt))
						$arLangCnt[$key] = 0;
					$arLangCnt[$key]++;
				}

				$topicsCnt += $arResult["TOPICS"];
				$messagesCnt += $arResult["POSTS"];
			}
			?>
			<a href="forum_admin.php?lang=<?= LANG ?>"><?= $totalCnt ?></a>
		</td>
	</tr>
	<tr>
		<td>
			&nbsp;&nbsp;&nbsp;<?echo GetMessage("FFI_FORUM_ACTIVE_CNT") ?>:
		</td>
		<td align="right">
			<a href="forum_admin.php?filter_active=Y&amp;set_filter=Y&amp;lang=<?= LANG ?>"><?= $activeCnt ?></a>
		</td>
	</tr>
<?
foreach ($arLangCnt as $key => $value):
?>
	<tr>
		<td>
			&nbsp;&nbsp;&nbsp;<?echo str_replace("#LANG#", $key, GetMessage("FFI_FORUM_LANG_CNT")) ?>:
		</td>
		<td align="right">
			<a href="forum_admin.php?filter_site_id=<?= $key ?>&amp;set_filter=Y&amp;lang=<?= LANG ?>"><?= $value ?></a>
		</td>
	</tr>
<?
endforeach;
?>
	<tr>
		<td>
			<?echo GetMessage("FFI_TOPICS_CNT") ?>:
		</td>
		<td align="right">
			<?= $topicsCnt ?>
		</td>
	</tr>
	<tr>
		<td>
			<?echo GetMessage("FFI_MESSAGES_CNT") ?>:
		</td>
		<td align="right">
			<?= $messagesCnt ?>
		</td>
	</tr>
	<tr>
		<td>
			<?echo GetMessage("FFI_USERS_CNT") ?>:
		</td>
		<td align="right">
			<?= CForumUser::CountUsers() ?>
		</td>
	</tr>
	<tr>
		<td>
			&nbsp;&nbsp;&nbsp;<?echo GetMessage("FFI_USERS_ACTIVE_CNT") ?>:
		</td>
		<td align="right">
			<?= CForumUser::CountUsers(True) ?>
		</td>
	</tr>
</table>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>