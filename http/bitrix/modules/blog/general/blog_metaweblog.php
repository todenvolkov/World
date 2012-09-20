<?
IncludeModuleLangFile(__FILE__);

class CBlogMetaWeblog
{
	function Authorize($user, $password)
	{
		global $USER, $APPLICATION;
		
		$arAuthResult = $USER->Login($user, $password, "Y");
		$APPLICATION->arAuthResult = $arAuthResult;
		if($USER->IsAuthorized() && strlen($arAuthResult["MESSAGE"])<=0)
			return true;
		else
			return false;
	}
	
	function DecodeParams($value)
	{
		foreach($value as $t => $v)
		{
			if($t == "base64")
				return base64_decode($v[0]["#"]);
			else
				return $v[0]["#"];
		}
	}

	function GetUsersBlogs($params, $arPath)
	{
		global $USER;
		$blog = CBlogMetaWeblog::DecodeParams($params[0]["#"]["value"][0]["#"]);
		$user = CBlogMetaWeblog::DecodeParams($params[1]["#"]["value"][0]["#"]);
		$password = CBlogMetaWeblog::DecodeParams($params[2]["#"]["value"][0]["#"]);

		if(CBlogMetaWeblog::Authorize($user, $password))
		{
			$result = '';
			$userId = $USER->GetID();

			$dbBlog = CBlog::GetList(Array(), Array("OWNER_ID" => $userId, "GROUP_SITE_ID" => SITE_ID, "ACTIVE" => "Y"), false, false, Array("ID", "URL", "NAME", "OWNER_ID"));
			while($arBlog = $dbBlog->GetNext())
			{
			
				if(strlen($arPath["PATH_TO_BLOG"]) > 0)
				{
					if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
						$serverName = SITE_SERVER_NAME;
					else
						$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
					$path2Blog = "http://".$serverName.CComponentEngine::MakePathFromTemplate($arPath["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"]));
				}
				else
					$path2Blog = $arBlog["URL"];
                $result .= '
						<value>
                            <struct>
                                <member>
                                    <name>url</name>
                                    <value>'.$path2Blog.'</value>
                                </member>
                                <member>
                                    <name>blogid</name>
                                    <value>'.$arBlog["ID"].'</value>
                                </member>
                                <member>
                                    <name>blogName</name>
                                    <value>'.$arBlog["NAME"].'</value>
                                </member>
                            </struct>
                        </value>
					';
			}
			
			if (CModule::IncludeModule("socialnetwork"))
			{
				$arGroupFilter = array(
					"USER_ID" => $userId,
					"<=ROLE" => SONET_ROLES_USER,
					"GROUP_SITE_ID" => SITE_ID,
					"GROUP_ACTIVE" => "Y"
				);

				$dbGroups = CSocNetUserToGroup::GetList(
					array("GROUP_NAME" => "ASC"),
					$arGroupFilter,
					false,
					false,
					array("ID", "GROUP_ID",  "GROUP_OWNER_ID", "GROUP_NAME")
				);
				while ($arGroups = $dbGroups->GetNext())
				{
					$perms = BLOG_PERMS_DENY;
					
					if (CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_GROUP, $arGroups["GROUP_ID"], "blog", "write_post", CSocNetUser::IsCurrentUserModuleAdmin()))
						$perms = BLOG_PERMS_WRITE;
					elseif (CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_GROUP, $arGroups["GROUP_ID"], "blog", "full_post"))
						$perms = BLOG_PERMS_FULL;
					
					if($perms >= BLOG_PERMS_WRITE)
					{
						$dbBlog = CBlog::GetList(Array(), Array("SOCNET_GROUP_ID" => $arGroups["GROUP_ID"], "GROUP_SITE_ID" => SITE_ID, "ACTIVE" => "Y"), false, false, Array("ID", "URL", "NAME"));
						if($arBlog = $dbBlog->GetNext())
						{
							$result .= '
									<value>
										<struct>
											<member>
												<name>url</name>
												<value>'.$arBlog["URL"].'</value>
											</member>
											<member>
												<name>blogid</name>
												<value>'.$arBlog["ID"].'</value>
											</member>
											<member>
												<name>blogName</name>
												<value>'.$arBlog["NAME"].'</value>
											</member>
										</struct>
									</value>
								';
						}
					}
				}
			}

			if(strlen($result) > 0)
			{
				return '<params>
							<param>
								<value>
									<array>
										<data>'
											.$result.
										'</data>
									</array>
								</value>
							</param>
						</params>';
			}
			else
			{
				return '<fault>
					  <value>
						 <struct>
							<member>
							   <name>faultCode</name>
							   <value><int>4</int></value>
							   </member>
							<member>
							   <name>faultString</name>
							   <value><string>User hasn\'t blog.</string></value>
							   </member>
							</struct>
						 </value>
					  </fault>';
			}
		}
		else
		{
			return '<fault>
				  <value>
					 <struct>
						<member>
						   <name>faultCode</name>
						   <value><int>3</int></value>
						   </member>
						<member>
						   <name>faultString</name>
						   <value><string>'.$arAuthResult["MESSAGE"].'</string></value>
						   </member>
						</struct>
					 </value>
				  </fault>';
		}
	}
	
	function GetCategories($params)
	{
		global $USER;
		$blog = CBlogMetaWeblog::DecodeParams($params[0]["#"]["value"][0]["#"]);
		$user = CBlogMetaWeblog::DecodeParams($params[1]["#"]["value"][0]["#"]);
		$password = CBlogMetaWeblog::DecodeParams($params[2]["#"]["value"][0]["#"]);

		if(CBlogMetaWeblog::Authorize($user, $password))
		{
			$result = '';
			$userId = $USER->GetID();

			$dbBlog = CBlog::GetList(Array(), Array("GROUP_SITE_ID" => SITE_ID, "ACTIVE" => "Y", "ID" => $blog), false, false, Array("ID", "URL", "NAME"));
			if($arBlog = $dbBlog->GetNext())
			{
				$dbCategory = CBlogCategory::GetList(Array("NAME" => "ASC"), Array("BLOG_ID" => $arBlog["ID"]));
				while($arCategory = $dbCategory->GetNext())
				{
				
					$result .= '
							<value>
								<struct>
									<member>
										<name>description</name>
										<value>'.$arCategory["NAME"].'</value>
									</member>
									<member>
										<name>title</name>
										<value>'.$arCategory["NAME"].'</value>
									</member>
								</struct>
							</value>
						';
				}
			}


			if(strlen($result) > 0)
			{
				return '<params>
							<param>
								<value>
									<array>
										<data>'
											.$result.
										'</data>
									</array>
								</value>
							</param>
						</params>';
			}
			else
			{
				return '<fault>
					  <value>
						 <struct>
							<member>
							   <name>faultCode</name>
							   <value><int>4</int></value>
							</member>
							<member>
							   <name>faultString</name>
							   <value><string>No categories in blog.</string></value>
							</member>
							</struct>
						 </value>
					  </fault>';
			}
		}
		else
		{
			return '<fault>
				  <value>
					 <struct>
						<member>
						   <name>faultCode</name>
						   <value><int>3</int></value>
						   </member>
						<member>
						   <name>faultString</name>
						   <value><string>'.$arAuthResult["MESSAGE"].'</string></value>
						   </member>
						</struct>
					 </value>
				  </fault>';
		}

	}
	function GetRecentPosts($params, $arPath)
	{
		global $USER;
		$blogId = IntVal(CBlogMetaWeblog::DecodeParams($params[0]["#"]["value"][0]["#"]));
		$user = CBlogMetaWeblog::DecodeParams($params[1]["#"]["value"][0]["#"]);
		$password = CBlogMetaWeblog::DecodeParams($params[2]["#"]["value"][0]["#"]);
		$numPosts = IntVal(CBlogMetaWeblog::DecodeParams($params[3]["#"]["value"][0]["#"]));
		if($numPosts <= 0)
			$numPosts = 1;
		elseif($numPosts > 20)
			$numPosts = 20;

		if(CBlogMetaWeblog::Authorize($user, $password))
		{
			$result = '';
			$userId = $USER->GetID();

			if(IntVal($blogId) > 0)
			{
				$dbBlog = CBlog::GetList(Array(), Array("GROUP_SITE_ID" => SITE_ID, "ACTIVE" => "Y", "ID" => $blogId), false, false, Array("ID", "URL", "NAME"));
				if($arBlog = $dbBlog->GetNext())
				{
					$parser = new blogTextParser();
					$arSelectedFields = array("ID", "BLOG_ID", "TITLE", "DATE_PUBLISH", "AUTHOR_ID", "DETAIL_TEXT", "DETAIL_TEXT_TYPE");
					$dbPost = CBlogPost::GetList(Array("DATE_PUBLISH" => "DESC", "ID" => "DESC"), Array("BLOG_ID" => $blogId), false, Array("nTopCount" => $numPosts), $arSelectedFields);
					while($arPost = $dbPost->GetNext())
					{
						$dateISO = date("Y-m-d\TH:i:s", MakeTimeStamp($arPost["DATE_PUBLISH"]));
						$title = htmlspecialcharsEx($arPost["TITLE"]);
						$arImages = Array();
						$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arPost["BLOG_ID"]));
						while ($arImage = $res->Fetch())
									$arImages[$arImage['ID']] = $arImage['FILE_ID'];
									
						if($arPost["DETAIL_TEXT_TYPE"] == "html")
						{
							$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "N", "QUOTE" => "N", "CODE" => "N");

							$text = $parser->convert_to_rss($arPost["DETAIL_TEXT"], $arImages, $arAllow, false);
						}
						else
						{
							$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "N", "CODE" => "N", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "N");
							$text = $parser->convert_to_rss(htmlspecialcharsEx($arPost["DETAIL_TEXT"]), $arImages, $arAllow, false);
						}
						$text = "<![CDATA[".$text."]]>";

						$category="";
						$dbCategory = CBlogPostCategory::GetList(Array(), Array("BLOG_ID" => $arPost["BLOG_ID"], "POST_ID" => $arPost["ID"]));
						while($arCategory = $dbCategory->Fetch())
						{
							$category .= '<value>'.htmlspecialcharsEx($arCategory["NAME"]).'</value>';
						}
						
						$path2Post = "";
						if(strlen($arPath["PATH_TO_POST"]) > 0)
						{
							if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
								$serverName = SITE_SERVER_NAME;
							else
								$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
							$path2Post = "http://".$serverName.CComponentEngine::MakePathFromTemplate($arPath["PATH_TO_POST"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "post_id" => $arPost["ID"]));
						}
						
						$result .= '
							<value>
								<struct>';
						if(strlen($category) > 0)
							$result .= '<member>
										<name>categories</name>
										<value>
										<array>
										<data>
										'.$category.'
										</data>
										</array>
										</value>
									</member>
								';
						$result .= '
									<member>
										<name>dateCreated</name>
										<value>
										<dateTime.iso8601>'.$dateISO.'</dateTime.iso8601>
										</value>
									</member>
									<member>
										<name>description</name>
										<value>'.$text.'</value>
									</member>
									<member>
										<name>link</name>
										<value>'.htmlspecialcharsEx($path2Post).'</value>
									</member>
									<member>
										<name>postid</name>
										<value>
										<i4>'.$arPost["ID"].'</i4>
										</value>
									</member>
									<member>
										<name>title</name>
										<value>'.$title.'</value>
									</member>
									<member>
										<name>publish</name>
										<value>
										<boolean>'.(($arPost["PUBLISH_STATUS"] == "D") ? "0" : "1").'</boolean>
										</value>
									</member>
								</struct>
							</value>
							';
					}
				}
			}
			
			return '<params>
						<param>
							<value>
								<array>
									<data>'
										.$result.
									'</data>
								</array>
							</value>
						</param>
					</params>';
		}
		else
		{
			return '<fault>
				  <value>
					 <struct>
						<member>
						   <name>faultCode</name>
						   <value><int>3</int></value>
						   </member>
						<member>
						   <name>faultString</name>
						   <value><string>'.$arAuthResult["MESSAGE"].'</string></value>
						   </member>
						</struct>
					 </value>
				  </fault>';
		}

	}
	
	function NewMediaObject($params)
	{
		global $USER, $DB;
		$blogId = IntVal(CBlogMetaWeblog::DecodeParams($params[0]["#"]["value"][0]["#"]));
		$user = CBlogMetaWeblog::DecodeParams($params[1]["#"]["value"][0]["#"]);
		$password = CBlogMetaWeblog::DecodeParams($params[2]["#"]["value"][0]["#"]);
		$arImage = $params[3]["#"]["value"][0]["#"]["struct"][0]["#"]["member"];
		

		foreach($arImage as $val)
		{
			$arImageInfo[$val["#"]["name"][0]["#"]] = CBlogMetaWeblog::DecodeParams($val["#"]["value"][0]["#"]);
		}
		
		if(CBlogMetaWeblog::Authorize($user, $password))
		{
			$result = '';
			$userId = $USER->GetID();

			if(IntVal($blogId) > 0)
			{
				$dbBlog = CBlog::GetList(Array(), Array("GROUP_SITE_ID" => SITE_ID, "ACTIVE" => "Y", "ID" => $blogId), false, false, Array("ID", "URL", "NAME"));
				if($arBlog = $dbBlog->GetNext())
				{
					$ABS_FILE_NAME = false;
					$filename = trim(str_replace("\\", "/", trim($arImageInfo["name"])), "/");
					$DIR_NAME = "/".COption::GetOptionString("main", "upload_dir")."/blog/tmp/".$arBlog["URL"];
					$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"].$DIR_NAME, "/".$filename);
					if((strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename))
					{
						$ABS_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$DIR_NAME."/".$FILE_NAME;
					}
					CheckDirPath($_SERVER["DOCUMENT_ROOT"].$DIR_NAME."/");
					
					if($fp = fopen($ABS_FILE_NAME, "ab"))
					{
						$result = fwrite($fp, $arImageInfo["bits"]);
						if($result !== (function_exists("mb_strlen")? mb_strlen($arImageInfo["bits"], 'latin1'): strlen($arImageInfo["bits"])))
						{
							return '<fault>
									  <value>
										 <struct>
											<member>
											   <name>faultCode</name>
											   <value><int>3</int></value>
											   </member>
											<member>
											   <name>faultString</name>
											   <value><string>Error on saving media object</string></value>
											   </member>
											</struct>
										 </value>
									  </fault>';
						}
						fclose($fp);
					}
					else
					{
						return '<fault>
								  <value>
									 <struct>
										<member>
										   <name>faultCode</name>
										   <value><int>3</int></value>
										   </member>
										<member>
										   <name>faultString</name>
										   <value><string>Error on saving media object</string></value>
										   </member>
										</struct>
									 </value>
								  </fault>';
					}
					
					
					$arFields = array(
						"BLOG_ID"	=> $arBlog["ID"],
						"USER_ID"	=> $userId,
						"=TIMESTAMP_X"	=> $DB->GetNowFunction(),
						"FILE_ID" => Array(
							"name" => $arImageInfo["name"],
							"tmp_name" => $_SERVER["DOCUMENT_ROOT"].$DIR_NAME.$FILE_NAME,
							//"content" => $arImageInfo["bits"],
							"MODULE_ID" => "blog",
							"type" => $arImageInfo["type"],
						)
					);
					$imageId = CBlogImage::Add($arFields);
					$arImg = CBlogImage::GetByID($imageId);
					$path = CFile::GetPath($arImg["FILE_ID"]);
					
					$dbSite = CSite::GetByID(SITE_ID);
					$arSite = $dbSite -> Fetch();
					$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
					if (strlen($serverName) <=0)
					{
						if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
							$serverName = SITE_SERVER_NAME;
						else
							$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
					}
					DeleteDirFilesEx($DIR_NAME);

					if(strlen($path) > 0)
					{
						return '<params>
							<param>
								<value>
									<struct>
									<member>
									<name>url</name>
									<value>
									<string>'.'http://'.$serverName.$path.'</string>
									</value>
									</member>
									</struct>
								</value>
							</param>
						</params>';
					}
				}
			}
			return '<fault>
				  <value>
					 <struct>
						<member>
						   <name>faultCode</name>
						   <value><int>3</int></value>
						   </member>
						<member>
						   <name>faultString</name>
						   <value><string>Error on saving media object</string></value>
						   </member>
						</struct>
					 </value>
				  </fault>';
		}
		else
		{
			return '<fault>
				  <value>
					 <struct>
						<member>
						   <name>faultCode</name>
						   <value><int>3</int></value>
						   </member>
						<member>
						   <name>faultString</name>
						   <value><string>'.$arAuthResult["MESSAGE"].'</string></value>
						   </member>
						</struct>
					 </value>
				  </fault>';
		}

	}
	
	function NewPost($params)
	{
		global $USER, $DB;
		$blogId = IntVal(CBlogMetaWeblog::DecodeParams($params[0]["#"]["value"][0]["#"]));
		$user = CBlogMetaWeblog::DecodeParams($params[1]["#"]["value"][0]["#"]);
		$password = CBlogMetaWeblog::DecodeParams($params[2]["#"]["value"][0]["#"]);
		$arPostInfo = $params[3]["#"]["value"][0]["#"]["struct"][0]["#"]["member"];
		$publish = $params[4]["#"]["value"][0]["#"]["boolean"][0]["#"];

		foreach($arPostInfo as $val)
		{
			${$val["#"]["name"][0]["#"]} = CBlogMetaWeblog::DecodeParams($val["#"]["value"][0]["#"]);
		}
		
		$arCategory = Array();
		if(is_array($categories["data"][0]["#"]["value"]))
		{
			foreach($categories["data"][0]["#"]["value"] as $val)
			{
				$catTmp = CBlogMetaWeblog::DecodeParams($val["#"]);
				if(strlen($catTmp) > 0)
					$arCategory[] = $catTmp;
			}
		}
		
		if(CBlogMetaWeblog::Authorize($user, $password))
		{
			$result = '';
			$userId = $USER->GetID();

			if(IntVal($blogId) > 0)
			{
				$dbBlog = CBlog::GetList(Array(), Array("GROUP_SITE_ID" => SITE_ID, "ACTIVE" => "Y", "ID" => $blogId), false, false, Array("ID", "URL", "NAME", "GROUP_ID"));
				if($arBlog = $dbBlog->GetNext())
				{
					$CATEGORYtmp = Array();
					$dbCategory = CBlogCategory::GetList(Array(), Array("BLOG_ID" => $blogId));
					while($arCat = $dbCategory->Fetch())
					{
						$arCatBlog[ToLower($arCat["NAME"])] = $arCat["ID"];
					}

					foreach($arCategory as $tg)
					{
						$tg = trim($tg);
						if(!in_array($arCatBlog[ToLower($tg)], $CATEGORYtmp))
						{
							if(IntVal($arCatBlog[ToLower($tg)]) > 0)
							{
							
								$CATEGORYtmp[] = $arCatBlog[ToLower($tg)];
							}
							else
							{
							
								$CATEGORYtmp[] = CBlogCategory::Add(array("BLOG_ID" => $blogId, "NAME" => $tg));
								BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/category/");
							}
						}
					}
					
					$dbSite = CSite::GetByID(SITE_ID);
					$arSite = $dbSite -> Fetch();
					$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
					if (strlen($serverName) <=0)
					{
						if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
							$serverName = SITE_SERVER_NAME;
						else
							$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
					}

					$arImgRepl = Array();
					$dbImage = CBlogImage::GetList(array(), Array("POST_ID" => false, "BLOG_ID" => $blogId));
					while($arImage = $dbImage->Fetch())
					{
						$path = "";
						$path = CFile::GetPath($arImage["FILE_ID"]);
						$path = "http://".$serverName.$path;

						if(strpos($description, $path) !== false)
						{
							$description = str_replace(('<img src="'.$path.'" alt=""/>'), "[IMG ID=".$arImage["ID"]."]", $description);
							$arImgRepl[] = $arImage["ID"];
						}
					}

					$arFields=array(
							"BLOG_ID"			=> $blogId,
							"AUTHOR_ID"			=> $userId,
							"TITLE"			=> $title,
							"DETAIL_TEXT"		=> $description,
							"DETAIL_TEXT_TYPE"	=> "html",
							"=DATE_PUBLISH"		=> $DB->GetNowFunction(),
							"=DATE_CREATE"		=> $DB->GetNowFunction(),
							"PUBLISH_STATUS"	=> (($publish == 1) ? "P" : "D"),
							"ENABLE_TRACKBACK"	=> "N",
							"ENABLE_COMMENTS"	=> "Y",
							"CATEGORY_ID"		=> implode(",", $CATEGORYtmp),
							"PERMS_POST" => array(),
							"PERMS_COMMENT" => array(),
						);
					$postId = CBlogPost::Add($arFields);
					if(IntVal($postId) > 0)
					{
						foreach($CATEGORYtmp as $v)
							CBlogPostCategory::Add(Array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $postId, "CATEGORY_ID"=>$v));
						foreach($arImgRepl as $v)
							CBlogImage::Update($v, Array("POST_ID" => $postId));
							
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/calendar/");
						BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
						BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
						BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
						BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
						BXClearCache(True, "/".SITE_ID."/blog/groups/".$arBlog["GROUP_ID"]."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_all/");
						BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
						BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/favorite/");
						
						return '<params>
									<param>
										<value>
											<i4>'.$postId.'</i4>
										</value>
									</param>
								</params>';
					}
					else
					{
						return '<fault>
							  <value>
								 <struct>
									<member>
									   <name>faultCode</name>
									   <value><int>3</int></value>
									   </member>
									<member>
									   <name>faultString</name>
									   <value><string>Error on adding post</string></value>
									   </member>
									</struct>
								 </value>
							  </fault>';
					}
				}
			}
			
		}
		else
		{
			return '<fault>
				  <value>
					 <struct>
						<member>
						   <name>faultCode</name>
						   <value><int>3</int></value>
						   </member>
						<member>
						   <name>faultString</name>
						   <value><string>'.$arAuthResult["MESSAGE"].'</string></value>
						   </member>
						</struct>
					 </value>
				  </fault>';
		}
	}
	
	function EditPost($params)
	{
		global $USER, $DB;
		$postId = IntVal(CBlogMetaWeblog::DecodeParams($params[0]["#"]["value"][0]["#"]));
		$user = CBlogMetaWeblog::DecodeParams($params[1]["#"]["value"][0]["#"]);
		$password = CBlogMetaWeblog::DecodeParams($params[2]["#"]["value"][0]["#"]);
		$arPostInfo = $params[3]["#"]["value"][0]["#"]["struct"][0]["#"]["member"];
		$publish = $params[4]["#"]["value"][0]["#"]["boolean"][0]["#"];

		foreach($arPostInfo as $val)
		{
			${$val["#"]["name"][0]["#"]} = CBlogMetaWeblog::DecodeParams($val["#"]["value"][0]["#"]);
		}

		$arCategory = Array();
		if(is_array($categories["data"][0]["#"]["value"]))
		{
			foreach($categories["data"][0]["#"]["value"] as $val)
			{
				$catTmp = CBlogMetaWeblog::DecodeParams($val["#"]);
				if(strlen($catTmp) > 0)
					$arCategory[] = $catTmp;
			}
		}
		
		if(CBlogMetaWeblog::Authorize($user, $password))
		{
			$result = '';
			$userId = $USER->GetID();

			if(IntVal($postId) > 0)
			{
				$arSelectedFields = array("ID", "BLOG_ID", "TITLE", "DATE_PUBLISH", "AUTHOR_ID", "DETAIL_TEXT", "DETAIL_TEXT_TYPE");
				$dbPost = CBlogPost::GetList(Array(), Array("AUTHOR_ID" => $userId, "ID" => $postId), false, Array("nTopCount" => 1), $arSelectedFields);
				if($arPost = $dbPost->Fetch())
				{
					$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
					$CATEGORYtmp = Array();
					$dbCategory = CBlogCategory::GetList(Array(), Array("BLOG_ID" => $arPost["BLOG_ID"]));
					while($arCat = $dbCategory->Fetch())
					{
						$arCatBlog[ToLower($arCat["NAME"])] = $arCat["ID"];
					}

					foreach($arCategory as $tg)
					{
						$tg = trim($tg);
						if(!in_array($arCatBlog[ToLower($tg)], $CATEGORYtmp))
						{
							if(IntVal($arCatBlog[ToLower($tg)]) > 0)
							{
							
								$CATEGORYtmp[] = $arCatBlog[ToLower($tg)];
							}
							else
							{
							
								$CATEGORYtmp[] = CBlogCategory::Add(array("BLOG_ID" => $arPost["BLOG_ID"], "NAME" => $tg));
								BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/category/");
							}
						}
					}
					
					$dbSite = CSite::GetByID(SITE_ID);
					$arSite = $dbSite -> Fetch();
					$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
					if (strlen($serverName) <=0)
					{
						if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
							$serverName = SITE_SERVER_NAME;
						else
							$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
					}

					$dbImage = CBlogImage::GetList(array(), Array("POST_ID" => false, "BLOG_ID" => $arBlog["ID"]));
					while($arImage = $dbImage->Fetch())
					{
						$path = "";
						$path = CFile::GetPath($arImage["FILE_ID"]);
						$path = "http://".$serverName.$path;

						if(strpos($description, $path) !== false)
						{
							$description = str_replace(('<img src="'.$path.'" alt=""/>'), "[IMG ID=".$arImage["ID"]."]", $description);
							CBlogImage::Update($arImage["ID"], Array("POST_ID" => $arPost["ID"]));
						}
					}


					$arFields=array(
							"TITLE"			=> $title,
							"DETAIL_TEXT"		=> $description,
							"DETAIL_TEXT_TYPE"	=> "html",
							"PUBLISH_STATUS"	=> (($publish == 1) ? "P" : "D"),
							"CATEGORY_ID"		=> implode(",", $CATEGORYtmp),
						);
					$postId = CBlogPost::Update($arPost["ID"], $arFields);
					
					CBlogPostCategory::DeleteByPostID($arPost["ID"]);
					foreach($CATEGORYtmp as $v)
						CBlogPostCategory::Add(Array("BLOG_ID" => $arPost["BLOG_ID"], "POST_ID" => $arPost["ID"], "CATEGORY_ID"=>$v));
					if(IntVal($postId) > 0)
					{
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/calendar/");
						BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
						BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
						BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
						BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
						BXClearCache(True, "/".SITE_ID."/blog/groups/".$arBlog["GROUP_ID"]."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/trackback/".$arPost["ID"]."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/comment/".$arPost["ID"]."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_all/");
						BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
						BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/favorite/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$arPost["ID"]."/");

						return '<params>
									<param>
										<value>
											<i4>'.$postId.'</i4>
										</value>
									</param>
								</params>';
					}
					else
					{
						return '<fault>
							  <value>
								 <struct>
									<member>
									   <name>faultCode</name>
									   <value><int>3</int></value>
									   </member>
									<member>
									   <name>faultString</name>
									   <value><string>Error on saving post</string></value>
									   </member>
									</struct>
								 </value>
							  </fault>';
					}
				}
			}
			
		}
		else
		{
			return '<fault>
				  <value>
					 <struct>
						<member>
						   <name>faultCode</name>
						   <value><int>3</int></value>
						   </member>
						<member>
						   <name>faultString</name>
						   <value><string>'.$arAuthResult["MESSAGE"].'</string></value>
						   </member>
						</struct>
					 </value>
				  </fault>';
		}
	}
	
	function GetPost($params, $arPath)
	{
		global $USER;
		$postId = IntVal(CBlogMetaWeblog::DecodeParams($params[0]["#"]["value"][0]["#"]));
		$user = CBlogMetaWeblog::DecodeParams($params[1]["#"]["value"][0]["#"]);
		$password = CBlogMetaWeblog::DecodeParams($params[2]["#"]["value"][0]["#"]);

		if(CBlogMetaWeblog::Authorize($user, $password))
		{
			$result = '';
			$userId = $USER->GetID();

			if(IntVal($postId) > 0)
			{
				$arSelectedFields = array("ID", "BLOG_ID", "TITLE", "DATE_PUBLISH", "AUTHOR_ID", "DETAIL_TEXT", "DETAIL_TEXT_TYPE", "BLOG_URL", "BLOG_OWNER_ID");
				$dbPost = CBlogPost::GetList(Array(), Array("AUTHOR_ID" => $userId, "ID" => $postId), false, Array("nTopCount" => 1), $arSelectedFields);
				if($arPost = $dbPost->Fetch())
				{
					$parser = new blogTextParser();
					$dateISO = date("Y-m-d\TH:i:s", MakeTimeStamp($arPost["DATE_PUBLISH"]));
					$title = htmlspecialcharsEx($arPost["TITLE"]);
					$arImages = Array();
					$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arPost["BLOG_ID"]));
					while ($arImage = $res->Fetch())
								$arImages[$arImage['ID']] = $arImage['FILE_ID'];
								
					if($arPost["DETAIL_TEXT_TYPE"] == "html")
					{
						$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "N", "QUOTE" => "N", "CODE" => "N");

						$text = $parser->convert_to_rss($arPost["DETAIL_TEXT"], $arImages, $arAllow, false);
					}
					else
					{
						$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "N", "CODE" => "N", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "N");
						$text = $parser->convert_to_rss(htmlspecialcharsEx($arPost["DETAIL_TEXT"]), $arImages, $arAllow, false);
					}
					$text = "<![CDATA[".($text)."]]>";

					$category="";
					$dbCategory = CBlogPostCategory::GetList(Array(), Array("BLOG_ID" => $arPost["BLOG_ID"], "POST_ID" => $arPost["ID"]));
					while($arCategory = $dbCategory->Fetch())
					{
						$category .= '<value>'.htmlspecialcharsEx($arCategory["NAME"]).'</value>';
					}
					
					$path2Post = "";
					if(strlen($arPath["PATH_TO_POST"]) > 0)
					{
						if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
							$serverName = SITE_SERVER_NAME;
						else
							$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
						$path2Post = "http://".$serverName.CComponentEngine::MakePathFromTemplate($arPath["PATH_TO_POST"], array("blog" => $arPost["BLOG_URL"], "user_id" => $arPost["BLOG_OWNER_ID"], "post_id" => $arPost["ID"]));
					}

					$result .= '
						<value>
							<struct>';
					if(strlen($category) > 0)
						$result .= '<member>
									<name>categories</name>
									<value>
									<array>
									<data>
									'.$category.'
									</data>
									</array>
									</value>
								</member>
							';
					$result .= '
								<member>
									<name>dateCreated</name>
									<value>
									<dateTime.iso8601>'.$dateISO.'</dateTime.iso8601>
									</value>
								</member>
								<member>
									<name>description</name>
									<value>'.$text.'</value>
								</member>
								<member>
									<name>link</name>
									<value>'.htmlspecialcharsEx($path2Post).'</value>
								</member>
								<member>
									<name>postid</name>
									<value>
									<i4>'.$arPost["ID"].'</i4>
									</value>
								</member>
								<member>
									<name>title</name>
									<value>'.$title.'</value>
								</member>
								<member>
									<name>publish</name>
									<value>
									<boolean>'.(($arPost["PUBLISH_STATUS"] == "D") ? "0" : "1").'</boolean>
									</value>
								</member>
							</struct>
						</value>
						';
				}
			}
			
			return '<params>
						<param>
							<value>
								<array>
									<data>'
										.$result.
									'</data>
								</array>
							</value>
						</param>
					</params>';
		}
		else
		{
			return '<fault>
				  <value>
					 <struct>
						<member>
						   <name>faultCode</name>
						   <value><int>3</int></value>
						   </member>
						<member>
						   <name>faultString</name>
						   <value><string>'.$arAuthResult["MESSAGE"].'</string></value>
						   </member>
						</struct>
					 </value>
				  </fault>';
		}
	}
	
	function DeletePost($params)
	{
		global $USER;
		$postId = IntVal(CBlogMetaWeblog::DecodeParams($params[1]["#"]["value"][0]["#"]));
		$user = CBlogMetaWeblog::DecodeParams($params[2]["#"]["value"][0]["#"]);
		$password = CBlogMetaWeblog::DecodeParams($params[3]["#"]["value"][0]["#"]);

		if(CBlogMetaWeblog::Authorize($user, $password))
		{
			$result = '';
			$userId = $USER->GetID();

			if(IntVal($postId) > 0)
			{
				$dbPost = CBlogPost::GetList(Array(), Array("AUTHOR_ID" => $userId, "ID" => $postId), false, Array("nTopCount" => 1), Array("ID", "BLOG_ID", "AUTHOR_ID"));
				if($arPost = $dbPost->Fetch())
				{
					CBlogPost::Delete($postId);
					
					$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/pages/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/calendar/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$postId."/");
					BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
					BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
					BXClearCache(True, "/".SITE_ID."/blog/groups/".$arResult["BLOG"]["GROUP_ID"]."/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/trackback/".$postId."/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_all/");
					BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
					BXClearCache(True, "/".SITE_ID."/blog/rss_all/");

				}
			}
			
			return '<params>
						<param>
							<value>
								<boolean>1</boolean> 
							</value>
						</param>
					</params>';
		}
		else
		{
			return '<fault>
				  <value>
					 <struct>
						<member>
						   <name>faultCode</name>
						   <value><int>3</int></value>
						   </member>
						<member>
						   <name>faultString</name>
						   <value><string>'.$arAuthResult["MESSAGE"].'</string></value>
						   </member>
						</struct>
					 </value>
				  </fault>';
		}
	}
	
	function GetUserInfo($params)
	{
		global $USER;
		$user = CBlogMetaWeblog::DecodeParams($params[1]["#"]["value"][0]["#"]);
		$password = CBlogMetaWeblog::DecodeParams($params[2]["#"]["value"][0]["#"]);

		if(CBlogMetaWeblog::Authorize($user, $password))
		{
			$result = '';
			$userId = $USER->GetID();

			$dbUser = CUser::GetByID($userId);
			if($arUser = $dbUser->Fetch())
			{
				$BlogUser = CBlogUser::GetByID($userId, BLOG_BY_USER_ID); 
				if(strlen($BlogUser["ALIAS"]) > 0)
					$nick = htmlspecialcharsEx($BlogUser["ALIAS"]);
				else
					$nick = htmlspecialcharsEx($arUser["LOGIN"]);
				$result .= '
						<value>
							<struct>
								<member>
									<name>nickname</name>
									<value>'.$nick.'</value>
								</member>
								<member>
									<name>email</name>
									<value>'.$arUser["EMAIL"].'</value>
								</member>
								<member>
									<name>lastname</name>
									<value>'.$arUser["LAST_NAME"].'</value>
								</member>
								<member>
									<name>firstname</name>
									<value>'.$arUser["NAME"].'</value>
								</member>
							</struct>
						</value>
					';
			}


			if(strlen($result) > 0)
			{
				return '<params>
							<param>
								<value>
									<array>
										<data>'
											.$result.
										'</data>
									</array>
								</value>
							</param>
						</params>';
			}
			else
			{
				return '<fault>
					  <value>
						 <struct>
							<member>
							   <name>faultCode</name>
							   <value><int>4</int></value>
							</member>
							<member>
							   <name>faultString</name>
							   <value><string>User not found.</string></value>
							</member>
							</struct>
						 </value>
					  </fault>';
			}
		}
		else
		{
			return '<fault>
				  <value>
					 <struct>
						<member>
						   <name>faultCode</name>
						   <value><int>3</int></value>
						   </member>
						<member>
						   <name>faultString</name>
						   <value><string>'.$arAuthResult["MESSAGE"].'</string></value>
						   </member>
						</struct>
					 </value>
				  </fault>';
		}

	}
	

}
?>