<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BLOG_CATEGORY_TITLE"));

$USER_ID = intval($USER->GetID());
$arResult["CATEGORY"] = Array();

if ($arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]))
{
	if($arBlog["ACTIVE"] == "Y")
	{
		$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
		if($arGroup["SITE_ID"] == SITE_ID)
		{

			$arResult["BLOG"] = $arBlog;
			if($arParams["SET_TITLE"]=="Y")
				$APPLICATION->SetTitle(GetMessage("BLOG_CATEGORY_TITLE")."\"".$arBlog["NAME"]."\"");
				
			if (CBlog::CanUserManageBlog($arBlog["ID"], $USER_ID))
			{
				if ($_POST["save"] && check_bitrix_sessid())
				{
					$arFields=array(
						'NAME' => $_POST["NAME"],
					);

					if (IntVal($_POST['ID']) > 0)
					{
						$res = CBlogCategory::GetList(Array("NAME"=>"ASC"), Array("BLOG_ID" => $arBlog["ID"], "ID" => IntVal($_POST["ID"])));
						if ($res->Fetch())
						{
							if ($_POST["category_del"]=="Y")
							{
								CBlogCategory::Delete(IntVal($_POST['ID']));
							}
							else
							{
								$res = CBlogCategory::GetList(Array("NAME"=>"ASC"), Array("BLOG_ID" => $arBlog["ID"], "NAME" => $arFields["NAME"]));
								if(!$res->Fetch())
									$newID = CBlogCategory::Update(IntVal($_POST["ID"]), $arFields);
								else
									$arResult["ERROR_MESSAGE"] = GetMessage("BLOG_CATEGORY_EXIST_1")." \"".htmlspecialcharsEx($arFields["NAME"])."\" ".GetMessage("BLOG_CATEGORY_EXIST_2");
							}
						}
						else
							$arResult["ERROR_MESSAGE"] = GetMessage("BLOG_ERR_NO_RIGHTS");
					}
					else
					{
						$arFields["BLOG_ID"] = $arBlog["ID"];
						$res = CBlogCategory::GetList(Array("NAME"=>"ASC"), Array("BLOG_ID"=>$arFields["BLOG_ID"], "NAME" => $arFields["NAME"]));
						if (!$res->Fetch())
						{
							$newID = CBlogCategory::Add($arFields);
						}	
						else
							$arResult["ERROR_MESSAGE"] = GetMessage("BLOG_CATEGORY_EXIST_1")." \"".htmlspecialcharsEx($arFields["NAME"])."\" ".GetMessage("BLOG_CATEGORY_EXIST_2");
					}
				
					if(strlen($arResult["ERROR_MESSAGE"])<=0)
						LocalRedirect($_POST["BACK_URL"]);
				}

				if(strlen($_POST["BACK_URL"])>0)
					$arResult["BACK_URL"] = $_POST["BACK_URL"];
				else
					$arResult["BACK_URL"] = htmlspecialchars($APPLICATION->GetCurPageParam());

				$res=CBlogCategory::GetList(Array("NAME" => "ASC"), Array("BLOG_ID" => $arBlog["ID"]));
				while ($arCategory=$res->GetNext())
				{
					$arSumCat[$arCategory["ID"]] = Array(
							"ID" => $arCategory["ID"],
							"NAME" => $arCategory["NAME"],
						);
					$toCnt[] = $arCategory['ID'];
				}

				$resCnt =CBlogPostCategory::GetList(Array(), Array("BLOG_ID" => $arBlog["ID"], "CATEGORY_ID"=> $toCnt), Array("CATEGORY_ID"), false, array("ID", "BLOG_ID", "CATEGORY_ID", "NAME"));
				while($arCategoryCount = $resCnt->Fetch())
				{
					if(IntVal($arSumCat[$arCategoryCount["CATEGORY_ID"]]["ID"])>0)
						$arSumCat[$arCategoryCount["CATEGORY_ID"]]["CNT"] = $arCategoryCount['CNT'];
				}
				
				if(!empty($arSumCat))
					$arResult["CATEGORY"] = $arSumCat;
			}
			else
				$arResult["FATAL_ERROR_MESSAGE"] = GetMessage("BLOG_ERR_NO_RIGHTS");
		}
		else
			$arResult["FATAL_ERROR_MESSAGE"] = GetMessage("BLOG_ERR_NO_BLOG");
	}
	else
		$arResult["FATAL_ERROR_MESSAGE"] = GetMessage("BLOG_ERR_NO_BLOG");
}
else
	$arResult["FATAL_ERROR_MESSAGE"] = GetMessage("BLOG_ERR_NO_BLOG");

$this->IncludeComponentTemplate();
?>