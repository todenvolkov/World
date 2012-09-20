<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
?>

<div id="blog-posts-content">
<?
if(!empty($arResult["OK_MESSAGE"]))
{
	?>
	<div class="blog-notes">
		<div class="blog-note-text">
			<ul>
				<?
				foreach($arResult["OK_MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}
if(!empty($arResult["MESSAGE"]))
{
	?>
	<div class="blog-textinfo">
		<div class="blog-textinfo-text">
			<ul>
				<?
				foreach($arResult["MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}
if(!empty($arResult["ERROR_MESSAGE"]))
{
	?>
	<div class="blog-errors">
		<div class="blog-error-text">
			<ul>
				<?
				foreach($arResult["ERROR_MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}

if(count($arResult["POST"])>0)
{
	foreach($arResult["POST"] as $CurPost)
	{
		?>
			<div class="blog-post">
				<h2 class="blog-post-title"><a href="<?=$CurPost["urlToPost"]?>" title="<?=$CurPost["TITLE"]?>"><?=$CurPost["TITLE"]?></a></h2>
				<div class="blog-post-info-back">
				<div class="blog-post-info">
					<?if ($arParams["SHOW_RATING"] == "Y"):?>
					<div class="blog-post-rating">
					<?
					$APPLICATION->IncludeComponent(
						"bitrix:rating.vote", "",
						Array(
							"ENTITY_TYPE_ID" => "BLOG_POST",
							"ENTITY_ID" => $CurPost["ID"],
							"OWNER_ID" => $CurPost["arUser"]["ID"],
							"USER_HAS_VOTED" => $arResult["RATING"][$CurPost["ID"]]["USER_HAS_VOTED"],
							"TOTAL_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_VOTES"],
							"TOTAL_POSITIVE_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_POSITIVE_VOTES"],
							"TOTAL_NEGATIVE_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_NEGATIVE_VOTES"],
							"TOTAL_VALUE" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_VALUE"]
						),
						null,
						array("HIDE_ICONS" => "Y")
					);?>
					</div>
					<?endif;?>
					<div class="blog-author">
					<?if($arParams["SEO_USER"] == "Y"):?>
						<noindex>
							<a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>" rel="nofollow"></a>
						</noindex>
					<?else:?>
						<a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>"></a>
					<?endif;?>
					<?
					$APPLICATION->IncludeComponent("bitrix:main.user.link",
						'',
						array(
							"ID" => $CurPost["AUTHOR_ID"],
							"HTML_ID" => "blog_blog_".$CurPost["AUTHOR_ID"],
							"NAME" => $CurPost["arUser"]["~NAME"],
							"LAST_NAME" => $CurPost["arUser"]["~LAST_NAME"],
							"SECOND_NAME" => $CurPost["arUser"]["~SECOND_NAME"],
							"LOGIN" => $CurPost["arUser"]["~LOGIN"],
							"USE_THUMBNAIL_LIST" => "N",
							"PROFILE_URL" => $CurPost["urlToAuthor"],
							"PROFILE_URL_LIST" => $CurPost["urlToBlog"],							
							"PATH_TO_SONET_USER_PROFILE" => $CurPost["urlToAuthor"],
							"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
							"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
							"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
							"SHOW_YEAR" => $arParams["SHOW_YEAR"],
							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
							"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
							"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
							"INLINE" => "Y",
						),
						false,
						array("HIDE_ICONS" => "Y")
					);
					?>					
					</div>
					<div class="blog-post-date"><?=$CurPost["DATE_PUBLISH_FORMATED"]?></div>
				</div>
				</div>
				<div class="blog-post-content">
					<?
					if(array_key_exists("USE_SHARE", $arParams) && $arParams["USE_SHARE"] == "Y")
					{
						?>
						<div class="blog-post-share" style="float: right;">
							<noindex>
							<?
							$APPLICATION->IncludeComponent("bitrix:main.share", "", array(
									"HANDLERS" => $arParams["SHARE_HANDLERS"],
									"PAGE_URL" => htmlspecialcharsback($CurPost["urlToPost"]),
									"PAGE_TITLE" => htmlspecialcharsback($CurPost["TITLE"]),
									"SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
									"SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
									"ALIGN" => "right",
									"HIDE" => $arParams["SHARE_HIDE"],
								),
								$component,
								array("HIDE_ICONS" => "Y")
							);
							?>
							</noindex>
						</div>
						<?
					}
					?>
					<div class="blog-post-avatar"><?=$CurPost["BlogUser"]["AVATAR_img"]?></div>
					<?=$CurPost["TEXT_FORMATED"]?>
					<?
					if ($CurPost["CUT"] == "Y")
					{
						?><p><a class="blog-postmore-link" href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_MORE")?></a></p><?
					}
					?>
					<?if($CurPost["POST_PROPERTIES"]["SHOW"] == "Y"):?>
						<p>
						<?foreach ($CurPost["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
						<?if(strlen($arPostField["VALUE"])>0):?>
						<b><?=$arPostField["EDIT_FORM_LABEL"]?>:</b>&nbsp;<?$APPLICATION->IncludeComponent(
										"bitrix:system.field.view", 
										$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
										array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?>
						<?endif;?>
						<?endforeach;?>
						</p>
					<?endif;?>
				</div>
				
				<div class="blog-post-meta">
					<div class="blog-post-meta-util">
						<span class="blog-post-views-link"><a href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_VIEWS")?></a> <a href="<?=$CurPost["urlToPost"]?>"><?=IntVal($CurPost["VIEWS"]);?></a></span>
						<?if($CurPost["ENABLE_COMMENTS"] == "Y"):?>
							<span class="blog-post-comments-link"><a href="<?=$CurPost["urlToPost"]?>#comments"><?=GetMessage("BLOG_BLOG_BLOG_COMMENTS")?></a> <a href="<?=$CurPost["urlToPost"]?>#comments"><?=IntVal($CurPost["NUM_COMMENTS"]);?></a></span>
						<?endif;?>
						<?if(strLen($CurPost["urlToHide"])>0):?>
							<span class="blog-post-hide-link"><a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_HIDE_POST_CONFIRM")?>')) window.location='<?=$CurPost["urlToHide"]."&".bitrix_sessid_get()?>'"><?=GetMessage("BLOG_MES_HIDE")?></a></span>
						<?endif;?>
						<?if(strLen($CurPost["urlToEdit"])>0):?>
							<span class="blog-post-edit-link"><a href="<?=$CurPost["urlToEdit"]?>"><?=GetMessage("BLOG_MES_EDIT")?></a></span>
						<?endif;?>
						<?if(strLen($CurPost["urlToDelete"])>0):?>
							<span class="blog-post-delete-link"><a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$CurPost["urlToDelete"]."&".bitrix_sessid_get()?>'"><?=GetMessage("BLOG_MES_DELETE")?></a></span>
						<?endif;?>

					</div>

					<div class="blog-post-tag">
						<noindex>
						<?
						if(!empty($CurPost["CATEGORY"]))
						{
							echo GetMessage("BLOG_BLOG_BLOG_CATEGORY");
							$i=0;
							foreach($CurPost["CATEGORY"] as $v)
							{
								if($i!=0)
									echo ",";
								?> <a href="<?=$v["urlToCategory"]?>" rel="nofollow"><?=$v["NAME"]?></a><?
								$i++;
							}
						}
						?>
						</noindex>
					</div>
				</div>
			</div>
		<?
	}
	if(strlen($arResult["NAV_STRING"])>0)
		echo $arResult["NAV_STRING"];
}
else
	echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
?>	
</div>