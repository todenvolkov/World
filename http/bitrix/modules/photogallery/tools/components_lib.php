<?
IncludeModuleLangFile(__FILE__);
class CPGalleryInterface
{
	var $IBlockID = 0;

	var $Gallery = false;

	var $User = array("Permission" => "D"); // current user

	var $arCache = array();
	var $arError = array();

	function CPGalleryInterface($main_params = array(), $additional_params = array())
	{
		$additional_params["cache_time"] = intval(!empty($additional_params["cache_time"]) ?
			$additional_params["cache_time"] : $additional_params["CACHE_TIME"]);
		$additional_params["cache_path"] = trim(!empty($additional_params["cache_path"]) ?
			$additional_params["cache_path"] : $additional_params["CACHE_PATH"]);
		$this->arCache = array(
			"time" => $additional_params["cache_time"],
			"path" => str_replace("//", "/", $additional_params["cache_path"]."/"));

		$additional_params["show_error"] = (!empty($additional_params["show_error"]) ? $additional_params["show_error"] : $additional_params["SHOW_ERROR"]);
		$additional_params["set_404"] = (!empty($additional_params["set_404"]) ? $additional_params["set_404"] : $additional_params["SET_404"]);
		$this->arError = array(
			"show_error" => ($additional_params["show_error"] == "N" ? "N" : "Y"),
			"set_404" => ($arParams["set_404"] == "Y" ? "Y" : "N"));

		// check id iblock
		if (intval($main_params["IBlockID"]) <= 0)
		{
			ShowError(GetMessage("P_IBLOCK_ID_EMPTY"));
			return false;
		}

		$this->IBlockID = intval($main_params["IBlockID"]);

		if (!empty($main_params["GalleryID"]))
		{
			$this->Gallery = $this->GetGallery($main_params["GalleryID"]);
			if (!$this->Gallery)
				return false;
		}

		$this->User["Permission"] = (!empty($main_params["Permission"]) ? $main_params["Permission"] : $this->GetPermission());
		if (!$this->CheckPermission("view"))
			return false;
	}

	function GetGallery($gallery_id)
	{
		static $arResult = array();
		$arCache = array(
			"id" => serialize(array(
				"iblock_id" => $this->IBlockID,
				"user_alias" => $gallery_id)),
			"path" => $this->arCache["path"]."gallery".$gallery_id,
			"time" => $this->arCache["time"]);

		if (empty($arResult[$arCache["id"]]))
		{
			$cache = new CPHPCache;
			if ($arCache["time"] > 0 && $cache->InitCache($arCache["time"], $arCache["id"], $arCache["path"]))
			{
				$arResult[$arCache["id"]] = $cache->GetVars();
			}
			else
			{
				CModule::IncludeModule("iblock");
				$arFilter = array(
					"IBLOCK_ACTIVE" => "Y",
					"IBLOCK_ID" => $this->IBlockID,
					"SECTION_ID" => 0,
					"CODE" => $gallery_id);
					
				$db_res = CIBlockSection::GetList(
					array(),
					$arFilter,
					false
				);

				if ($db_res && $res = $db_res->GetNext())
				{
					$arResult[$arCache["id"]] = $res;
					if ($arCache["time"] > 0)
					{
						$cache->StartDataCache($arCache["time"], $arCache["id"], $arCache["path"]);
						$cache->EndDataCache($res);
					}
				}
			}
		}
		if (empty($arResult[$arCache["id"]]))
		{
			if ($this->arError["show_error"] == "Y")
				ShowError(GetMessage("P_GALLERY_NOT_FOUND"));
			if ($this->arError["set_404"] == "Y")
				CHTTP::SetStatus("404 Not Found");
			return false;
		}
		elseif ($arResult[$arCache["id"]]["ACTIVE"] != "Y")
		{
			if ($this->arError["show_error"] == "Y")
				ShowError(GetMessage("P_GALLERY_IS_BLOCKED"));
			return false;
		}

		return $arResult[$arCache["id"]];
	}

	function GetSection($id, &$arSection, $params = array())
	{
		static $arResult = array();
		$params = (is_array($params) ? $params : array($params));
		$id = intval($id);
		if ($id <= 0)
			return 200;

		$arCache = array(
			"id" => serialize(array(
					"iblock_id" => $this->IBlockID,
					"section_id" => $id,
//					"get_elements_count" => ($params["get_elements_count"] == "N" ? "N" : "Y"),
//					"get_sections_count" => ($params["get_sections_count"] == "N" ? "N" : "Y")
				)
			),
			"path" => $this->arCache["path"]."section".$id,
			"time" => $this->arCache["time"]);

		if (empty($arResult[$arCache["id"]]))
		{
			$cache = new CPHPCache;
			if ($arCache["time"] > 0 && $cache->InitCache($arCache["time"], $arCache["id"], $arCache["path"]))
			{
				$arResult[$arCache["id"]] = $cache->GetVars();
			}
			else
			{
				CModule::IncludeModule("iblock");
				$arFilter = array(
					"IBLOCK_ACTIVE" => "Y",
					"IBLOCK_ID" => $this->IBlockID,
					"ID" => $id);

				$db_res = CIBlockSection::GetList(
					array(),
					$arFilter,
					false
				);

				if (!($db_res && $arSection = $db_res->GetNext()))
				{
					if ($this->arError["show_error"] == "Y")
						ShowError(GetMessage("P_SECTION_NOT_FOUND"));
					if ($this->arError["set_404"] == "Y")
						CHTTP::SetStatus("404 Not Found");
					return 404;
				}
				elseif ($arSection["ACTIVE"] != "Y" && $this->User["Permission"] < "U")
				{
					if ($this->arError["show_error"] == "Y")
						ShowError(GetMessage("P_ALBUM_IS_BLOCKED"));
					return 405;
				}
				elseif ($this->Gallery && ($arSection["LEFT_MARGIN"] < $this->Gallery["LEFT_MARGIN"] ||
					$this->Gallery["RIGHT_MARGIN"] < $arSection["RIGHT_MARGIN"]))
				{
					return 301;
				}
				else
				{
					$arSection["SECTIONS_CNT"] = 0;
					if (($arSection["RIGHT_MARGIN"] - $arSection["LEFT_MARGIN"]) > 1)
						$arSection["SECTIONS_CNT"] = intVal(CIBlockSection::GetCount(array("SECTION_ID" => $arSection["ID"])));

					$arSection["SECTION_ELEMENTS_CNT"] = $arSection["SECTION_ELEMENTS_CNT_ALL"] = $arSection["ELEMENTS_CNT"] = 0;
					$arSection["ELEMENTS_CNT_ALL"] = intVal(CIBlockSection::GetSectionElementsCount(
							$arSection["ID"], array("CNT_ALL" => "Y")));

					// if section not empty
					if ($arSection["ELEMENTS_CNT_ALL"] > 0)
					{
						if ($arSection["SECTIONS_CNT"] > 0)
						{
							$arSection["SECTION_ELEMENTS_CNT_ALL"] = intval(CIBlockElement::GetList(
								array(),
								array("SECTION_ID" => $arSection["ID"]),
								array(),
								false,
								array("ID")));
						}
						else
						{
							$arSection["SECTION_ELEMENTS_CNT_ALL"] = $arSection["ELEMENTS_CNT_ALL"];
						}
						if ($this->User["Permission"] < "U")
						{
							$arSection["ELEMENTS_CNT"] = intVal(CIBlockSection::GetSectionElementsCount($arSection["ID"], array("CNT_ACTIVE" => "Y")));
						}
						else
						{
							$arSection["ELEMENTS_CNT"] = $arSection["ELEMENTS_CNT_ALL"];
						}
						// if not exists active elements
						if ($arSection["ELEMENTS_CNT"] <= 0)
							$arSection["SECTION_ELEMENTS_CNT"] = 0;
						// if not exists unactive elements
						elseif ($arSection["ELEMENTS_CNT_ALL"] == $arSection["ELEMENTS_CNT"])
							$arSection["SECTION_ELEMENTS_CNT"] = $arSection["SECTION_ELEMENTS_CNT_ALL"];
						elseif ($arSection["SECTIONS_CNT"] <= 0)
							$arSection["SECTION_ELEMENTS_CNT"] = $arSection["ELEMENTS_CNT"];
						else
						{
							$arSection["SECTION_ELEMENTS_CNT"] = intval(CIBlockElement::GetList(
								array(),
								array("SECTION_ID" => $arSection["ID"], "ACTIVE" => "Y"),
								array(),
								false,
								array("ID")));
						}
					}


					$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$this->IBlockID."_SECTION", $arSection["ID"], LANGUAGE_ID);
					$arSection["USER_FIELDS"] = $arUserFields;
					$arSection["DATE"] = $arSection["~DATE"] = $arUserFields["UF_DATE"];
					$arSection["~PASSWORD"] = $arUserFields["UF_PASSWORD"];
					if (is_array($arSection["~PASSWORD"]))
						$arSection["PASSWORD"] = $arSection["~PASSWORD"]["VALUE"];

					$arSection["PICTURE"] = CFile::GetFileArray($arSection["PICTURE"]);
					$arSection["DETAIL_PICTURE"] = CFile::GetFileArray($arSection["DETAIL_PICTURE"]);
					$arSection["PATH"] = array();
					$db_res = GetIBlockSectionPath($this->IBlockID, $arSection["ID"]);
					while ($res = $db_res->GetNext())
					{
						$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$this->IBlockID."_SECTION", $res["ID"], LANGUAGE_ID);
						$res["~PASSWORD"] = $arUserFields["UF_PASSWORD"];
						if (is_array($res["~PASSWORD"]))
							$res["PASSWORD"] = $res["~PASSWORD"]["VALUE"];
						$arSection["PATH"][$res["ID"]] = $res;
					}

					$arResult[$arCache["id"]] = $arSection;
					if ($arCache["time"] > 0)
					{
						$cache->StartDataCache($arCache["time"], $arCache["id"], $arCache["path"]);
						$cache->EndDataCache($arSection);
					}
				}
			}
		}
		$arSection = $arResult[$arCache["id"]];
		return 200;
	}

	function GetSectionGallery($arSection = array())
	{
		$arGallery = $this->GetGallery;
		if (!empty($arSection) && ($arSection["LEFT_MARGIN"] < $arGallery["LEFT_MARGIN"] || $arGallery["RIGHT_MARGIN"] < $arSection["RIGHT_MARGIN"]))
		{
			CModule::IncludeModule("iblock");
			$db_res = CIBlockSection::GetList(
				array(),
				array(
					"IBLOCK_ID" => $arSection["IBLOCK_ID"],
					"SECTION_ID" => 0,
					"!LEFT_MARGIN" => $arSection["LEFT_MARGIN"],
					"!RIGHT_MARGIN" => $arSection["RIGHT_MARGIN"],
					"!ID" => $arSection["ID"]),
				false
			);

			if (!($db_res && $res = $db_res->GetNext()))
			{
				if ($this->arError["show_error"] == "Y")
					ShowError(GetMessage("P_GALLERY_NOT_FOUND"));
				if ($this->arError["set_404"] == "Y")
					CHTTP::SetStatus("404 Not Found");
				return 0;
			}
			else
			{
				return $res;
			}
		}
		return $arGallery;
	}

	function GetPermission()
	{
		static $arResult = array();
		$user_id = intVal($GLOBALS["USER"]->GetID());
		$user_groups = $GLOBALS["USER"]->GetGroups();

		if (!$this->IBlockID)
			return false;

		$arCache = array(
			"id" => serialize(array(
				"iblock_id" => $this->IBlockID,
				"permission" => $user_groups)),
			"path" => $this->arCache["path"]."pemission",
			"time" => $this->arCache["time"]);

		if (empty($arResult[$arCache["id"]]))
		{
			$cache = new CPHPCache;
			if ($arCache["time"] > 0 && $cache->InitCache($arCache["time"], $arCache["id"], $arCache["path"])):
				$arResult[$arCache["id"]] = $cache->GetVars();
			else:
				CModule::IncludeModule("iblock");
				$arResult[$arCache["id"]] = CIBlock::GetPermission($this->IBlockID);
				if ($arCache["time"] > 0):
					$cache->StartDataCache($arCache["time"], $arCache["id"], $arCache["path"]);
					$cache->EndDataCache($arResult[$arCache["id"]]);
				endif;
			endif;
		}

		if (!empty($arResult[$arCache["id"]]))
		{
			if (!empty($this->Gallery) &&
				("R" <= $arResult[$arCache["id"]] && $arResult[$arCache["id"]] < "W") &&
				$this->Gallery["CREATED_BY"] == $user_id)
			{
				return "W";
			}
			return $arResult[$arCache["id"]];
		}
		return "D";
	}

	function CheckPermission($permission = "D", $arSection = array())
	{
		$arSection = (!is_array($arSection) ? array() : $arSection);
		if ($permission < "R"):
			ShowError(GetMessage("P_DENIED_ACCESS"));
			return false;
		elseif ($permission < "U" && !empty($arSection) && $arSection["ELEMENTS_CNT"] <= 0):
			ShowNote($arSection["ELEMENTS_CNT_ALL"] > 0 ?
				GetMessage("P_SECTION_IS_NOT_APPROVED") : GetMessage("P_SECTION_IS_EMPTY"));
			return false;
		elseif ($permission < "U" && !empty($arSection["PATH"])):
			$password_checked = true;

			foreach ($arSection["PATH"] as $key => $res)
			{
				if (empty($res["PASSWORD"]))
					continue;

				if (check_bitrix_sessid() && $arSection["PASSWORD"] == md5($_REQUEST["password_".$arSection["ID"]]))
					$_SESSION['PHOTOGALLERY']['SECTION'][$arSection["ID"]] = $arSection["PASSWORD"];

			}
			foreach ($arSection["PATH"] as $key => $res)
			{
				if (empty($res["PASSWORD"]))
					continue;
				if ($res["PASSWORD"] != $_SESSION['PHOTOGALLERY']['SECTION'][$res["ID"]])
				{

					$password_checked = false;
					?>
					<div class="photo-info-box photo-album-password">
						<div class="photo-info-box-inner">
							<?ShowError(GetMessage("P_DENIED_ACCESS"));?>
							<p><?
							if ($res["ID"] != $arSection["ID"]):
								?><?=GetMessage("P_PARENT_ALBUM_IS_PASSWORDED")?><?
							else:
								?><?=GetMessage("P_ALBUM_IS_PASSWORDED")?><?
							endif;
							?><?=str_replace("#NAME#", $res["NAME"], GetMessage("P_ALBUM_IS_PASSWORDED_TITLE"))
							?></p>
							<form method="post" action="<?=POST_FORM_ACTION_URI?>" class="photo-form">
								<?=bitrix_sessid_post()?>
								<label for="password_<?=$res["ID"]?>"><?=GetMessage("P_PASSWORD")?>: </label>
								<input type="password" class="password" name="password_<?=$res["ID"]?>" <?
									?>id="password_<?=$res["ID"]?>" value="" />
								<input type="submit" class="submit" name="supply_password" value="<?=GetMessage("P_ENTER")?>" />
							</form>
						</div>
					</div>
					<?
				break;
				}
			}

			return $password_checked;
		endif;

		return true;
	}
}

?>