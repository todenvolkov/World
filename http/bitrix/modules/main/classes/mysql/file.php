<?
class CFile extends CAllFile
{
	function Delete($ID)
	{
		if(IntVal($ID)<=0)
			return;

		global $DB;
		$res = CFile::GetByID($ID);
		if($res = $res->Fetch())
		{
			$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");
			$fname = $_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$res["SUBDIR"]."/".$res["FILE_NAME"];
			if(file_exists($fname))
			{
				if(unlink($fname))
				{
					/****************************** QUOTA ******************************/
					if (COption::GetOptionInt("main", "disk_space") > 0)
					{
						CDiskQuota::updateDiskQuota("file", $res["FILE_SIZE"], "delete");
					}
					/****************************** QUOTA ******************************/
				}
			}

			$cacheImageFilePath = $_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/resize_cache/".$res["SUBDIR"];
			if (file_exists($cacheImageFilePath))
			{
				if ($cacheImageHandle = @opendir($cacheImageFilePath))
				{
					while (($cacheImageFile = readdir($cacheImageHandle)) !== false)
					{
						if ($cacheImageFile == "." || $cacheImageFile == "..")
							continue;

						if (file_exists($cacheImageFilePath."/".$cacheImageFile."/".$res["FILE_NAME"]))
						{
							if (COption::GetOptionInt("main", "disk_space") > 0)
							{
								$fileSizeTmp = filesize($cacheImageFilePath."/".$cacheImageFile."/".$res["FILE_NAME"]);
								if (unlink($cacheImageFilePath."/".$cacheImageFile."/".$res["FILE_NAME"]))
									CDiskQuota::updateDiskQuota("file", $fileSizeTmp, "delete");
							}
							else
							{
								unlink($cacheImageFilePath."/".$cacheImageFile."/".$res["FILE_NAME"]);
							}
							@rmdir($cacheImageFilePath."/".$cacheImageFile);
						}
					}
					@closedir($handle);
				}
			}

			$DB->Query("DELETE FROM b_file WHERE ID=".IntVal($ID));
			@rmdir($_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$res["SUBDIR"]);
			CFile::CleanCache($ID);
		}
	}

	function DoDelete($ID)
	{
		CFile::Delete($ID);
	}
}
?>