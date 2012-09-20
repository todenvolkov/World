<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}
$arDefaultUrlTemplates404 = array(
	"index" => "index.php",

	"user" => "user/#user_id#/",
	"user_friends" => "user/#user_id#/friends/",
	"user_friends_add" => "user/#user_id#/friends/add/",
	"user_friends_delete" => "user/#user_id#/friends/delete/",
	"user_groups" => "user/#user_id#/groups/",
	"user_groups_add" => "user/#user_id#/groups/add/",
	"group_create" => "user/#user_id#/groups/create/",
	"user_profile_edit" => "user/#user_id#/edit/",
	"user_settings_edit" => "user/#user_id#/settings/",
	"user_features" => "user/#user_id#/features/",
	"user_subscribe" => "user/#user_id#/subscribe/",

	"search" => "search.php",
	"reindex" => "reindex.php",
	"group_content_search" => "group/#group_id#/search/",
	"user_content_search" => "user/#user_id#/search/",	

	"group" => "group/#group_id#/",
	"group_edit" => "group/#group_id#/edit/",
	"group_requests" => "group/#group_id#/requests/",
	"group_requests_out" => "group/#group_id#/requests_out/",
	"group_mods" => "group/#group_id#/moderators/",
	"group_users" => "group/#group_id#/users/",
	"group_ban" => "group/#group_id#/ban/",
	"group_delete" => "group/#group_id#/delete/",
	"group_features" => "group/#group_id#/features/",
	"group_subscribe" => "group/#group_id#/subscribe/",
	"group_list" => "group/",
	"group_search" => "group/search/",
	"group_search_subject" => "group/search/#subject_id#/",
	"user_leave_group" => "group/#group_id#/user_leave/",
	"user_request_group" => "group/#group_id#/user_request/",
	"group_request_search" => "group/#group_id#/user_search/",

	"group_request_user" => "group/#group_id#/user/#user_id#/request/",
	"group_request_group_search" => "group/#user_id#/group_search/",

	"message_to_group" => "group/#group_id#/chat/",
	"message_form_mess" => "messages/chat/#user_id#/#message_id#/",
	"messages_chat" => "messages/chat/#user_id#/",
	"message_form" => "messages/form/#user_id#/",
	"messages_users" => "messages/",
	"messages_users_messages" => "messages/#user_id#/",
	"messages_input" => "messages/input/",
	"messages_output" => "messages/output/",
	"messages_input_user" => "messages/input/#user_id#/",
	"messages_output_user" => "messages/output/#user_id#/",
	"user_ban" => "messages/ban/",
	"log" => "log/",
	"activity" => "user/#user_id#/activity/",
	"subscribe" => "subscribe/",
	"bizproc" => "bizproc/",
	"bizproc_edit" => "bizproc/#task_id#/",
	"bizproc_task_list" => "user/#user_id#/bizproc/",
	"bizproc_task" => "user/#user_id#/bizproc/#task_id#/",
	"video_call" => "video/#user_id#/",

	"user_photo" => "user/#user_id#/photo/",
	"user_photo_gallery" => "user/#user_id#/photo/gallery/",
	"user_photo_gallery_edit" => "user/#user_id#/photo/gallery/action/#action#/",
	"user_photo_galleries" => "user/#user_id#/photo/galleries/",
	"user_photo_section" => "user/#user_id#/photo/album/#section_id#/",
	"user_photo_section_edit" => "user/#user_id#/photo/album/#section_id#/action/#action#/",
	"user_photo_section_edit_icon" => "user/#user_id#/photo/album/#section_id#/icon/action/#action#/",
	"user_photo_element_upload" => "user/#user_id#/photo/photo/#section_id#/action/upload/",
	"user_photo_element" => "user/#user_id#/photo/photo/#section_id#/#element_id#/",
	"user_photo_element_edit" => "user/#user_id#/photo/photo/#section_id#/#element_id#/action/#action#/",
	"user_photo_element_slide_show" => "user/#user_id#/photo/photo/#section_id#/#element_id#/slide_show/",
	"user_photofull_gallery" => "user/#user_id#/photo/gallery/#user_alias#/",
	"user_photofull_gallery_edit" => "user/#user_id#/photo/gallery/#user_alias#/action/#action#/",
	"user_photofull_section" => "user/#user_id#/photo/album/#user_alias#/#section_id#/",
	"user_photofull_section_edit" => "user/#user_id#/photo/album/#user_alias#/#section_id#/action/#action#/",
	"user_photofull_section_edit_icon" => "user/#user_id#/photo/album/#user_alias#/#section_id#/icon/action/#action#/",
	"user_photofull_element_upload" => "user/#user_id#/photo/photo/#user_alias#/#section_id#/action/upload/",
	"user_photofull_element" => "user/#user_id#/photo/photo/#user_alias#/#section_id#/#element_id#/",
	"user_photofull_element_edit" => "user/#user_id#/photo/photo/#user_alias#/#section_id#/#element_id#/action/#action#/",
	"user_photofull_element_slide_show" => "user/#user_id#/photo/photo/#user_alias#/#section_id#/#element_id#/slide_show/",
	

	"user_forum" => "user/#user_id#/forum/",
	"user_forum_topic" => "user/#user_id#/forum/#topic_id#/",
	"user_forum_topic_edit" => "user/#user_id#/forum/edit/#action#/#topic_id#/#message_id#/",
	"user_forum_message" => "user/#user_id#/forum/message/#topic_id#/#message_id#/",
	"user_forum_message_edit" => "user/#user_id#/forum/message/#action#/#topic_id#/#message_id#/",

	"user_calendar" => "user/#user_id#/calendar/",

	"user_tasks" => "user/#user_id#/tasks/",
	"user_tasks_task" => "user/#user_id#/tasks/task/#action#/#task_id#/",
	"user_tasks_view" => "user/#user_id#/tasks/view/#action#/#view_id#/",

	"user_files" => "user/#user_id#/files/lib/#path#",
	"user_files_short" => "folder/view/#section_id#/#element_id#/#element_name#",
	"user_files_section_edit" => "user/#user_id#/files/folder/edit/#section_id#/#action#/",
	"user_files_element" => "user/#user_id#/files/element/view/#element_id#/",
	"user_files_element_edit" => "user/#user_id#/files/element/edit/#element_id#/#action#/",
	"user_files_element_file" => "",
	"user_files_element_history" => "user/#user_id#/files/element/history/#element_id#/",
	"user_files_element_history_get" => "user/#user_id#/files/element/historyget/#element_id#/#element_name#",
	"user_files_element_version" => "user/#user_id#/files/element/version/#action#/#element_id#/",
	"user_files_element_versions" => "user/#user_id#/files/element/versions/#element_id#/",
	"user_files_element_upload" => "user/#user_id#/files/element/upload/#section_id#/",
	"user_files_help" => "user/#user_id#/files/help/",
	"user_files_webdav_bizproc_history" => "user/#user_id#/files/bizproc/history/#element_id#/", 
	"user_files_webdav_bizproc_history_get" => "user/#user_id#/files/bizproc/historyget/#element_id#/#id#/#element_name#", 
	"user_files_webdav_bizproc_log" => "user/#user_id#/files/bizproc/log/#element_id#/#id#/", 
	"user_files_webdav_bizproc_view" => "user/#user_id#/files/bizproc/bizproc/#element_id#/", 
	"user_files_webdav_bizproc_workflow_admin" => "user/#user_id#/files/bizproc/admin/", 
	"user_files_webdav_bizproc_workflow_edit" => "user/#user_id#/files/bizproc/edit/#id#/", 
	"user_files_webdav_start_bizproc" => "user/#user_id#/files/bizproc/start/#element_id#/", 

	"group_photo" => "group/#group_id#/photo/",
	"group_photo_gallery" => "group/#group_id#/photo/gallery/",
	"group_photo_gallery_edit" => "group/#group_id#/photo/gallery/action/#action#/",
	"group_photo_galleries" => "group/#group_id#/photo/galleries/",
	"group_photo_section" => "group/#group_id#/photo/album/#section_id#/",
	"group_photo_section_edit" => "group/#group_id#/photo/album/#section_id#/action/#action#/",
	"group_photo_section_edit_icon" => "group/#group_id#/photo/album/#section_id#/icon/action/#action#/",
	"group_photo_element_upload" => "group/#group_id#/photo/photo/#section_id#/action/upload/",
	"group_photo_element" => "group/#group_id#/photo/photo/#section_id#/#element_id#/",
	"group_photo_element_edit" => "group/#group_id#/photo/photo/#section_id#/#element_id#/action/#action#/",
	"group_photo_element_slide_show" => "group/#group_id#/photo/photo/#section_id#/#element_id#/slide_show/",
	"group_photofull_gallery" => "group/#group_id#/photo/gallery/#user_alias#/",
	"group_photofull_gallery_edit" => "group/#group_id#/photo/gallery/#user_alias#/action/#action#/",
	"group_photofull_section" => "group/#group_id#/photo/album/#user_alias#/#section_id#/",
	"group_photofull_section_edit" => "group/#group_id#/photo/album/#user_alias#/#section_id#/action/#action#/",
	"group_photofull_section_edit_icon" => "group/#group_id#/photo/album/#user_alias#/#section_id#/icon/action/#action#/",
	"group_photofull_element_upload" => "group/#group_id#/photo/photo/#user_alias#/#section_id#/action/upload/",
	"group_photofull_element" => "group/#group_id#/photo/photo/#user_alias#/#section_id#/#element_id#/",
	"group_photofull_element_edit" => "group/#group_id#/photo/photo/#user_alias#/#section_id#/#element_id#/action/#action#/",
	"group_photofull_element_slide_show" => "group/#group_id#/photo/photo/#user_alias#/#section_id#/#element_id#/slide_show/",
	

	"group_forum" => "group/#group_id#/forum/",
	"group_forum_topic" => "group/#group_id#/forum/#topic_id#/",
	"group_forum_topic_edit" => "group/#group_id#/forum/edit/#topic_id#/",
	"group_forum_message" => "group/#group_id#/forum/message/#topic_id#/#message_id#/",
	"group_forum_message_edit" => "group/#group_id#/forum/message/#action#/#topic_id#/#message_id#/",

	"group_calendar" => "group/#group_id#/calendar/",

	"group_tasks" => "group/#group_id#/tasks/",
	"group_tasks_task" => "group/#group_id#/tasks/task/#action#/#task_id#/",
	"group_tasks_view" => "group/#group_id#/tasks/view/#action#/#view_id#/",

	"group_files" => "group/#group_id#/files/lib/#path#/",
	"group_files_short" => "folder/view/#section_id#/#element_id#/#element_name#",
	"group_files_section_edit" => "group/#group_id#/files/folder/edit/#section_id#/#action#/",
	"group_files_element" => "group/#group_id#/files/element/view/#element_id#/",
	"group_files_element_edit" => "group/#group_id#/files/element/edit/#element_id#/#action#/",
	"group_files_element_file" => "",
	"group_files_element_history" => "group/#group_id#/files/element/history/#element_id#/",
	"group_files_element_history_get" => "group/#group_id#/files/element/historyget/#element_id#/#element_name#",
	"group_files_element_version" => "group/#group_id#/files/element/version/#action#/#element_id#/",
	"group_files_element_versions" => "group/#group_id#/files/element/versions/#element_id#/",
	"group_files_element_upload" => "group/#group_id#/files/element/upload/#section_id#/",
	"group_files_help" => "group/#group_id#/files/help/",
	"group_files_webdav_bizproc_history" => "group/#group_id#/files/bizproc/history/#element_id#/", 
	"group_files_webdav_bizproc_history_get" => "group/#group_id#/files/bizproc/historyget/#element_id#/#id#/#element_name#", 
	"group_files_webdav_bizproc_log" => "group/#group_id#/files/bizproc/log/#element_id#/#id#/", 
	"group_files_webdav_bizproc_view" => "group/#group_id#/files/bizproc/bizproc/#element_id#/", 
	"group_files_webdav_bizproc_workflow_admin" => "group/#group_id#/files/bizproc/admin/", 
	"group_files_webdav_bizproc_workflow_edit" => "group/#group_id#/files/bizproc/edit/#id#/", 
	"group_files_webdav_start_bizproc" => "group/#group_id#/files/bizproc/start/#element_id#/", 

	"user_blog" => "user/#user_id#/blog/",
	"user_blog_post_edit" => "user/#user_id#/blog/edit/#post_id#/",
	"user_blog_rss" => "user/#user_id#/blog/rss/#type#/",
	"user_blog_post_rss" => "user/#user_id#/blog/rss/#type#/#post_id#",
	"user_blog_draft" => "user/#user_id#/blog/draft/",
	"user_blog_moderation" => "user/#user_id#/blog/moderation/",
	"user_blog_post" => "user/#user_id#/blog/#post_id#/",

	"group_blog" => "group/#group_id#/blog/",
	"group_blog_post_edit" => "group/#group_id#/blog/edit/#post_id#/",
	"group_blog_rss" => "group/#group_id#/blog/rss/#type#/",
	"group_blog_post_rss" => "group/#group_id#/blog/rss/#type#/#post_id#",
	"group_blog_draft" => "group/#group_id#/blog/draft/",
	"group_blog_moderation" => "group/#group_id#/blog/moderation/",
	"group_blog_post" => "group/#group_id#/blog/#post_id#/",
	
	"group_log" => "group/#group_id#/log/",
	"group_log_rss" => "group/#group_id#/log/rss/?bx_hit_hash=#sign#&events=#events#",
	"group_log_rss_mask" => "group/#group_id#/log/rss/",
);

$arDefaultUrlTemplatesN404 = array(
	"index" => "",

	"user" => "page=user&user_id=#user_id#",
	"user_friends" => "page=user_friends&user_id=#user_id#",
	"user_friends_add" => "page=user_friends_add&user_id=#user_id#",
	"user_friends_delete" => "page=user_friends_delete&user_id=#user_id#",
	"user_groups" => "page=user_groups&user_id=#user_id#",
	"user_groups_add" => "page=user_groups_add&user_id=#user_id#",
	"group_create" => "page=group_create&user_id=#user_id#",
	"user_profile_edit" => "page=user_profile_edit&user_id=#user_id#",
	"user_settings_edit" => "page=user_settings_edit&user_id=#user_id#",
	"user_features" => "page=user_features&user_id=#user_id#",
	"user_subscribe" => "page=user_subscribe&user_id=#user_id#",

	"search" => "page=search",
	"reindex" => "page=reindex",
	"group_content_search" => "page=group_content_search&group_id=#group_id#",
	"user_content_search" => "page=user_content_search&user_id=#user_id#",

	"group" => "page=group&group_id=#group_id#",
	"group_edit" => "page=group_edit&group_id=#group_id#",
	"group_requests" => "page=group_requests&group_id=#group_id#",
	"group_requests_out" => "page=group_requests_out&group_id=#group_id#",
	"group_mods" => "page=group_mods&group_id=#group_id#",
	"group_users" => "page=group_users&group_id=#group_id#",
	"group_ban" => "page=group_ban&group_id=#group_id#",
	"group_delete" => "page=group_delete&group_id=#group_id#",
	"group_features" => "page=group_features&group_id=#group_id#",
	"group_subscribe" => "page=group_subscribe&group_id=#group_id#",
	"group_list" => "page=group_list",
	"group_search" => "page=group_search",
	"group_search_subject" => "page=group_search_subject&subject_id=#subject_id#",
	"user_leave_group" => "page=user_leave_group&group_id=#group_id#",
	"user_request_group" => "page=user_request_group&group_id=#group_id#",
	"group_request_search" => "page=group_request_search&group_id=#group_id#",

	"group_request_user" => "page=group_request_user&group_id=#group_id#&user_id=#user_id#",
	"group_request_group_search" => "page=group_request_group_search&user_id=#user_id#",

	"message_to_group" => "page=message_to_group&group_id=#group_id#",
	"message_form_mess" => "page=message_form_mess&user_id=#user_id#&message_id=#message_id#",
	"messages_chat" => "page=messages_chat&user_id=#user_id#",
	"message_form" => "page=message_form&user_id=#user_id#",
	"messages_users" => "page=messages_users",
	"messages_users_messages" => "page=messages_users_messages&user_id=#user_id#",
	"messages_input" => "page=messages_input",
	"messages_output" => "page=messages_output",
	"messages_input_user" => "page=messages_input_user&user_id=#user_id#",
	"messages_output_user" => "page=messages_output_user&user_id=#user_id#",
	"user_ban" => "page=user_ban",
	"log" => "page=log",
	"activity" => "page=activity&user_id=#user_id#",
	"subscribe" => "page=subscribe",
	"bizproc" => "page=bizproc",
	"bizproc_edit" => "page=bizproc_edit&task_id=#task_id#",
	"bizproc_task_list" => "page=bizproc_task_list&user_id=#user_id#",
	"bizproc_task" => "page=bizproc_task&user_id=#user_id#&task_id=#task_id#",
	"video_call" => "page=video_call&user_id=#user_id#",

	"user_photo" => "page=user_photo&user_id=#user_id#",
	"user_photo_gallery" => "page=user_photo_gallery&user_id=#user_id#",
	"user_photo_gallery_edit" => "page=user_photo_gallery&user_id=#user_id#&action=#action#",
	"user_photo_galleries" => "page=user_photo_galleries&user_id=#user_id#",
	"user_photo_section" => "page=user_photo_section&user_id=#user_id#&section_id=#section_id#",
	"user_photo_section_edit" => "page=user_photo_section_edit&user_id=#user_id#&section_id=#section_id#&action=#action#",
	"user_photo_section_edit_icon" => "page=user_photo_section_edit_icon&user_id=#user_id#&section_id=#section_id#&action=#action#",
	"user_photo_element_upload" => "page=user_photo_element_upload&user_id=#user_id#&section_id=#section_id#",
	"user_photo_element" => "page=user_photo_element&user_id=#user_id#&section_id=#section_id#&element_id=#element_id#",
	"user_photo_element_edit" => "page=user_photo_element_edit&user_id=#user_id#&section_id=#section_id#&element_id=#element_id#&action=#action#",
	"user_photo_element_slide_show" => "page=user_photo_element_slide_show&user_id=#user_id#&section_id=#section_id#&element_id=#element_id#",
	"user_photofull_gallery" => "page=user_photofull_gallery&user_id=#user_id#&user_alias=#user_alias#",
	"user_photofull_gallery_edit" => "page=user_photofull_gallery_edit&user_id=#user_id#&user_alias=#user_alias#&action=#action#",
	"user_photofull_section" => "page=user_photofull_section&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#",
	"user_photofull_section_edit" => "page=user_photofull_section_edit&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#&action=#action#",
	"user_photofull_section_edit_icon" => "page=user_photofull_section_edit_icon&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#&action=#action#",
	"user_photofull_element_upload" => "page=user_photofull_element_upload&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#",
	"user_photofull_element" => "page=user_photofull_element&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#",
	"user_photofull_element_edit" => "page=user_photofull_element_edit&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#&action=#action#",	
	"user_photofull_element_slide_show" => "page=user_photofull_element_slide_show&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#",

	"user_forum" => "page=user_forum&user_id=#user_id#",
	"user_forum_topic" => "page=user_forum_topic&user_id=#user_id#&topic_id=#topic_id#",
	"user_forum_topic_edit" => "page=user_forum_topic_edit&user_id=#user_id#&topic_id=#topic_id#",
	"user_forum_message" => "page=user_forum_message&user_id=#user_id#&topic_id=#topic_id#&message_id=#message_id#",
	"user_forum_message_edit" => "page=user_forum_message_edit&user_id=#user_id#&topic_id=#topic_id#&message_id=#message_id#&action=#action#",

	"user_calendar" => "page=user_calendar&user_id=#user_id#",

	"user_tasks" => "page=user_tasks&user_id=#user_id#",
	"user_tasks_task" => "page=user_tasks_task&user_id=#user_id#&action=#action#&task_id=#task_id#",
	"user_tasks_view" => "page=user_tasks_view&user_id=#user_id#&action=#action#&view_id=#view_id#",

	"user_files" => "page=user_files&user_id=#user_id#&path=#path#",
	"user_files_short" => "page=user_files_short&user_id=#user_id#&section_id=#section_id#&element_id=#element_id#&element_name=#element_name#",
	"user_files_section_edit" => "page=user_files_section_edit&user_id=#user_id#&section_id=#section_id#&action=#action#",
	"user_files_element" => "page=user_files_element&user_id=#user_id#&element_id=#element_id#",
	"user_files_element_edit" => "page=user_files_element_edit&user_id=#user_id#&element_id=#element_id#&action=#action#",
	"user_files_element_file" => "",
	"user_files_element_history" => "page=user_files_element_history&user_id=#user_id#&element_id=#element_id#",
	"user_files_element_history_get" => "page=user_files_element_history_get&user_id=#user_id#&element_id=#element_id#&element_name=#element_name#",
	"user_files_element_version" => "page=user_files_element_version&user_id=#user_id#&element_id=#element_id#&action=#action#",
	"user_files_element_versions" => "page=user_files_element_versions&user_id=#user_id#&element_id=#element_id#",
	"user_files_element_upload" => "page=user_files_element_upload&user_id=#user_id#&section_id=#section_id#",
	"user_files_help" => "page=user_files_help&user_id=#user_id#",
	"user_files_webdav_bizproc_history" => "page=user_files_webdav_bizproc_history&user_id=#user_id#&element_id=#element_id#", 
	"user_files_webdav_bizproc_history_get" => "page=user_files_webdav_bizproc_history_get&user_id=#user_id#&element_id=#element_id#&element_name=#element_name#", 
	"user_files_webdav_bizproc_log" => "page=user_files_webdav_bizproc_log&user_id=#user_id#&element_id=#element_id#&id=#id#", 
	"user_files_webdav_bizproc_view" => "page=user_files_webdav_bizproc_view&user_id=#user_id#&element_id=#element_id#", 
	"user_files_webdav_bizproc_workflow_admin" => "page=user_files_webdav_bizproc_workflow_admin&user_id=#user_id#", 
	"user_files_webdav_bizproc_workflow_edit" => "page=user_files_webdav_bizproc_workflow_edit&user_id=#user_id#&id=#id#", 
	"user_files_webdav_start_bizproc" => "page=user_files_webdav_start_bizproc&user_id=#user_id#&element_id=#element_id#", 

	"group_photo" => "page=group_photo&group_id=#group_id#",
	"group_photo_gallery" => "page=group_photo_gallery&group_id=#group_id#",
	"group_photo_gallery_edit" => "page=group_photo_gallery&group_id=#group_id#&action=#action#",
	"group_photo_galleries" => "page=group_photo_galleries&group_id=#group_id#",
	"group_photo_section" => "page=group_photo_section&group_id=#group_id#&section_id=#section_id#",
	"group_photo_section_edit" => "page=group_photo_section_edit&group_id=#group_id#&section_id=#section_id#&action=#action#",
	"group_photo_section_edit_icon" => "page=group_photo_section_edit_icon&group_id=#group_id#&section_id=#section_id#&action=#action#",
	"group_photo_element_upload" => "page=group_photo_element_upload&group_id=#group_id#&section_id=#section_id#",
	"group_photo_element" => "page=group_photo_element&group_id=#group_id#&section_id=#section_id#&element_id=#element_id#",
	"group_photo_element_edit" => "page=group_photo_element_edit&group_id=#group_id#&section_id=#section_id#&element_id=#element_id#&action=#action#",
	"group_photo_element_slide_show" => "page=group_photo_element_slide_show&group_id=#group_id#&section_id=#section_id#&element_id=#element_id#",
	"group_photofull_gallery" => "page=group_photofull_gallery&group_id=#group_id#&user_alias=#user_alias#",
	"group_photofull_gallery_edit" => "page=group_photofull_gallery_edit&group_id=#group_id#&user_alias=#user_alias#&action=#action#",
	"group_photofull_section" => "page=group_photofull_section&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#",
	"group_photofull_section_edit" => "page=group_photofull_section_edit&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#&action=#action#",
	"group_photofull_section_edit_icon" => "page=group_photofull_section_edit_icon&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#&action=#action#",
	"group_photofull_element_upload" => "page=group_photofull_element_upload&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#",
	"group_photofull_element" => "page=group_photofull_element&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#",
	"group_photofull_element_edit" => "page=group_photofull_element_edit&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#&action=#action#",
	"group_photofull_element_slide_show" => "page=group_photofull_element_slide_show&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#",

	"group_forum" => "page=group_forum&group_id=#group_id#",
	"group_forum_topic" => "page=group_forum_topic&group_id=#group_id#&topic_id=#topic_id#",
	"group_forum_topic_edit" => "page=group_forum_topic_edit&group_id=#group_id#&topic_id=#topic_id#",
	"group_forum_message" => "page=group_forum_message&group_id=#group_id#&topic_id=#topic_id#&message_id=#message_id#",
	"group_forum_message_edit" => "page=group_forum_message_edit&group_id=#group_id#&topic_id=#topic_id#&message_id=#message_id#&action=#action#",

	"group_calendar" => "page=group_calendar&group_id=#group_id#",

	"group_tasks" => "page=group_tasks&group_id=#group_id#",
	"group_tasks_task" => "page=group_tasks_task&group_id=#group_id#&action=#action#&task_id=#task_id#",
	"group_tasks_view" => "page=group_tasks_view&group_id=#group_id#&action=#action#&view_id=#view_id#",

	"group_files" => "page=group_files&group_id=#group_id#&path=#path#",
	"group_files_short" => "page=group_files_short&group_id=#group_id#&section_id=#section_id#&element_id=#element_id#&element_name=#element_name#",
	"group_files_section_edit" => "page=group_files_section_edit&group_id=#group_id#&section_id=#section_id#&action=#action#",
	"group_files_element" => "page=group_files_element&group_id=#group_id#&element_id=#element_id#",
	"group_files_element_edit" => "page=group_files_element_edit&group_id=#group_id#&element_id=#element_id#&action=#action#",
	"group_files_element_file" => "",
	"group_files_element_history" => "page=group_files_element_history&element_id=#element_id#",
	"group_files_element_history_get" => "page=group_files_element_history_get&element_id=#element_id#&element_name=#element_name#",
	"group_files_element_version" => "page=group_files_element_version&group_id=#group_id#&element_id=#element_id#&action=#action#",
	"group_files_element_versions" => "page=group_files_element_versions&group_id=#group_id#&element_id=#element_id#",
	"group_files_element_upload" => "page=group_files_element_upload&group_id=#group_id#&section_id=#section_id#",
	"group_files_help" => "page=group_files_help&group_id=#group_id#",
	"group_files_webdav_bizproc_history" => "page=group_files_webdav_bizproc_history&group_id=#group_id#&element_id=#element_id#", 
	"group_files_webdav_bizproc_history_get" => "page=group_files_webdav_bizproc_history_get&group_id=#group_id#&element_id=#element_id#&element_name=#element_name#", 
	"group_files_webdav_bizproc_log" => "page=group_files_webdav_bizproc_log&group_id=#group_id#&element_id=#element_id#&id=#id#", 
	"group_files_webdav_bizproc_view" => "page=group_files_webdav_bizproc_view&group_id=#group_id#&element_id=#element_id#", 
	"group_files_webdav_bizproc_workflow_admin" => "page=group_files_webdav_bizproc_workflow_admin&group_id=#group_id#", 
	"group_files_webdav_bizproc_workflow_edit" => "page=group_files_webdav_bizproc_workflow_edit&group_id=#group_id#&id=#id#", 
	"group_files_webdav_start_bizproc" => "page=group_files_webdav_start_bizproc&group_id=#group_id#&element_id=#element_id#", 

	"user_blog" => "page=user_blog&user_id=#user_id#",
	"user_blog_post_edit" => "page=user_blog_post_edit&user_id=#user_id#&post_id=#post_id#",
	"user_blog_rss" => "page=user_blog_rss&user_id=#user_id#&type=#type#",
	"user_blog_post_rss" => "page=user_blog_post_rss&user_id=#user_id#&type=#type#&post_id=#post_id#",
	"user_blog_draft" => "page=user_blog_draft&user_id=#user_id#",
	"user_blog_moderation" => "page=user_blog_moderation&user_id=#user_id#",
	"user_blog_post" => "page=user_blog_post&user_id=#user_id#&post_id=#post_id#",

	"group_blog" => "page=group_blog&group_id=#group_id#",
	"group_blog_post_edit" => "page=group_blog_post_edit&group_id=#group_id#&post_id=#post_id#",
	"group_blog_rss" => "page=group_blog_rss&group_id=#group_id#&type=#type#",
	"group_blog_post_rss" => "page=group_blog_post_rss&group_id=#group_id#&type=#type#&post_id=#post_id#",
	"group_blog_draft" => "page=group_blog_draft&group_id=#group_id#",
	"group_blog_moderation" => "page=group_blog_moderation&group_id=#group_id#",
	"group_blog_post" => "page=group_blog_post&group_id=#group_id#&post_id=#post_id#",

	"group_log" => "page=group_log&group_id=#group_id#",
	"group_log_rss" => "page=group_log_rss&group_id=#group_id#&sign=#sign#&events=#events#",
//	"group_log_rss_mask" => "page=group_log_rss&group_id=#group_id#"
	);
$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();
$componentPage = "";
$arComponentVariables = array("user_id", "group_id", "page", "message_id", "subject_id", "path", "section_id", "element_id", "action", "post_id", "category", "topic_id", "task_id", "view_id", "type");

if ($_REQUEST["auth"]=="Y" && $USER->IsAuthorized())
	LocalRedirect($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password", "backurl", "auth")));

if (!array_key_exists("SET_NAV_CHAIN", $arParams))
	$arParams["SET_NAV_CHAIN"] = $arParams["SET_NAVCHAIN"];
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (!array_key_exists("ALLOW_GROUP_CREATE_REDIRECT_REQUEST", $arParams))
	$arParams["ALLOW_GROUP_CREATE_REDIRECT_REQUEST"] = "Y";

if (strlen(trim($arParams["NAME_TEMPLATE"])) <= 0)
	$arParams["NAME_TEMPLATE"] = GetMessage('SONET_NAME_TEMPLATE_DEFAULT');
$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";	

if (IsModuleInstalled("intranet"))
	$arParams['CAN_OWNER_EDIT_DESKTOP'] = $arParams['CAN_OWNER_EDIT_DESKTOP'] != "Y" ? "N" : "Y";
else	
	$arParams['CAN_OWNER_EDIT_DESKTOP'] = $arParams['CAN_OWNER_EDIT_DESKTOP'] != "N" ? "Y" : "N";

// for bitrix:main.user.link
if (IsModuleInstalled('intranet'))
{
	$arTooltipFieldsDefault	= serialize(array(
		"EMAIL",
		"PERSONAL_MOBILE",
		"WORK_PHONE",
		"PERSONAL_ICQ",
		"PERSONAL_PHOTO",
		"PERSONAL_CITY",
		"WORK_COMPANY",
		"WORK_POSITION",
	));
	$arTooltipPropertiesDefault = serialize(array(
		"UF_DEPARTMENT",
		"UF_PHONE_INNER",
	));
}
else
{
	$arTooltipFieldsDefault = serialize(array(
		"PERSONAL_ICQ",
		"PERSONAL_BIRTHDAY",
		"PERSONAL_PHOTO",
		"PERSONAL_CITY",
		"WORK_COMPANY",
		"WORK_POSITION"
	));
	$arTooltipPropertiesDefault = serialize(array());
}

if (!array_key_exists("SHOW_FIELDS_TOOLTIP", $arParams))
	$arParams["SHOW_FIELDS_TOOLTIP"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_fields", $arTooltipFieldsDefault));
if (!array_key_exists("USER_PROPERTY_TOOLTIP", $arParams))
	$arParams["USER_PROPERTY_TOOLTIP"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_properties", $arTooltipPropertiesDefault));

if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";

$arParams["USE_MAIN_MENU"] = (isset($arParams["USE_MAIN_MENU"]) && $arParams["USE_MAIN_MENU"] == "Y" ? $arParams["USE_MAIN_MENU"] : false);

if ($arParams["USE_MAIN_MENU"] == "Y" && !array_key_exists("MAIN_MENU_TYPE", $arParams))
	$arParams["MAIN_MENU_TYPE"] = "left";
	
$arParams["LOG_SUBSCRIBE_ONLY"] = (isset($arParams["LOG_SUBSCRIBE_ONLY"]) && $arParams["LOG_SUBSCRIBE_ONLY"] == "Y" ? "Y" : "N");

$arParams["LOG_RSS_TTL"] = (isset($arParams["LOG_RSS_TTL"]) && intval($arParams["LOG_RSS_TTL"]) > 0 ? $arParams["LOG_RSS_TTL"] : "60");

$arParams["GROUP_USE_BAN"] = $arParams["GROUP_USE_BAN"] != "N" ? "Y" : "N";

$arParams["ALLOW_RATING_SORT"] = $arParams["ALLOW_RATING_SORT"] != "Y" ? "N" : "Y";
$arParams["SHOW_RATING"] = $arParams["SHOW_RATING"] != "Y" ? "N" : "Y";

if (
	!array_key_exists("RATING_ID", $arParams)
	|| 
	(
		!is_array($arParams["RATING_ID"])
		&& intval($arParams["RATING_ID"]) <= 0
	)
)
	$arParams["RATING_ID"] = 0;

if (IsModuleInstalled("search"))
{
	if (!array_key_exists("SEARCH_FILTER_NAME", $arParams))
		$arParams["SEARCH_FILTER_NAME"] = "sonet_search_filter";
	if (!array_key_exists("SEARCH_FILTER_DATE_NAME", $arParams))
		$arParams["SEARCH_FILTER_DATE_NAME"] = "sonet_search_filter_date";
}

$arCustomPagesPath = array();

if ($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$events = GetModuleEvents("socialnetwork", "OnParseSocNetComponentPath");
	while ($arEvent = $events->Fetch())
		ExecuteModuleEventEx($arEvent, array(&$arDefaultUrlTemplates404, &$arCustomPagesPath, $arParams));

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	
	/* This code is needed to use short paths in WebDAV */
	$arUrlTemplates["user_files_short"] = str_replace("#path#", $arDefaultUrlTemplates404["user_files_short"], $arUrlTemplates["user_files"]); 
	$arUrlTemplates["group_files_short"] = str_replace("#path#", $arDefaultUrlTemplates404["group_files_short"], $arUrlTemplates["group_files"]); 
	/* / This code is needed to use short paths in WebDAV */
	
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);

	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplates404))
		$componentPage = $arVariables["page"];

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
	{
		//if (strlen($componentPage) <= 0)
		$componentPage = "index";
	}

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	foreach ($arUrlTemplates as $url => $value)
		$arResult["PATH_TO_".strToUpper($url)] = $arParams["SEF_FOLDER"].$value;

	if ($_REQUEST["auth"] == "Y")
		$componentPage = "auth";
}
else
{
	if(is_array($arParams["VARIABLE_ALIASES"]))
		foreach ($arParams["VARIABLE_ALIASES"] as $key => $val)
			$arParams["VARIABLE_ALIASES"][$key] = (!empty($val) ? $val : $key);
			
	$events = GetModuleEvents("socialnetwork", "OnParseSocNetComponentPath");
	while ($arEvent = $events->Fetch())
		ExecuteModuleEventEx($arEvent, array(&$arDefaultUrlTemplatesN404, &$arCustomPagesPath, $arParams));	

	$arVariables = array();
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);

	$events = GetModuleEvents("socialnetwork", "OnInitSocNetComponentVariables");
	while ($arEvent = $events->Fetch())
		ExecuteModuleEventEx($arEvent, array(&$arVariableAliases, &$arCustomPagesPath));

	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	if (!empty($arDefaultUrlTemplatesN404) && !empty($arParams["VARIABLE_ALIASES"]))
	{
		foreach ($arDefaultUrlTemplatesN404 as $url => $value)
		{
			$pattern = array();
			$replace = array();
			foreach ($arParams["VARIABLE_ALIASES"] as $key => $res)
			{
				if ($key != $res && !empty($res))
				{
					$pattern[] = preg_quote("/(^|([&?]+))".$key."\=/is");
					$replace[] = "$1".$res."=";
				}
			}
			if (!empty($pattern))
			{
				$value = preg_replace($pattern, $replace, $value);
				$arDefaultUrlTemplatesN404[$url] = $value;
			}
		}
	}
	foreach ($arDefaultUrlTemplatesN404 as $url => $value)
	{
		$arParamsKill = array("page", "path",
				"section_id", "element_id", "action", "user_id", "group_id", "action", "use_light_view", "AJAX_CALL", "MUL_MODE",
				"edit_section", "sessid", "post_id", "category", "topic_id", "result", "MESSAGE_TYPE", "user_settings_id", "q", "how", "tags", "where");
		$arParamsKill = array_merge($arParamsKill, $arParams["VARIABLE_ALIASES"], array_values($arVariableAliases));
		$arResult["PATH_TO_".strToUpper($url)] = $GLOBALS["APPLICATION"]->GetCurPageParam($value, $arParamsKill);
	}
	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplatesN404))
		$componentPage = $arVariables["page"];

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplatesN404)))
	{
		//if (strlen($componentPage) <= 0)
		$componentPage = "index";
	}
	if ($_REQUEST["auth"] == "Y")
		$componentPage = "auth";
		
	$arResult["PATH_TO_GROUP_LOG_RSS_MASK"] = $arResult["~PATH_TO_GROUP_LOG_RSS_MASK"] = $APPLICATION->GetCurPage(true)."?page=group_log_rss&group_id=".$arVariables["group_id"];
}

$arResult = array_merge(
	array(
		"SEF_MODE" => $arParams["SEF_MODE"],
		"SEF_FOLDER" => $arParams["SEF_FOLDER"],
		"VARIABLES" => $arVariables,
		"ALIASES" => $arParams["SEF_MODE"] == "Y"? array(): $arVariableAliases,
		"SET_TITLE" => $arParams["SET_TITLE"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TIME_LONG" => $arParams["CACHE_TIME_LONG"],
		"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"],
		"SET_NAVCHAIN" => $arParams["SET_NAV_CHAIN"],
		"ITEM_DETAIL_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
		"ITEM_MAIN_COUNT" => $arParams["ITEM_MAIN_COUNT"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"USER_PROPERTY_MAIN" => $arParams["USER_PROPERTY_MAIN"],
		"USER_PROPERTY_CONTACT" => $arParams["USER_PROPERTY_CONTACT"],
		"USER_PROPERTY_PERSONAL" => $arParams["USER_PROPERTY_PERSONAL"],
		"USER_FIELDS_MAIN" => $arParams["USER_FIELDS_MAIN"],
		"USER_FIELDS_CONTACT" => $arParams["USER_FIELDS_CONTACT"],
		"USER_FIELDS_PERSONAL" => $arParams["USER_FIELDS_PERSONAL"],
		"USER_FIELDS_SEARCH_SIMPLE" => $arParams["USER_FIELDS_SEARCH_SIMPLE"],
		"USER_FIELDS_SEARCH_ADV" => $arParams["USER_FIELDS_SEARCH_ADV"],
		"USER_PROPERTIES_SEARCH_SIMPLE" => $arParams["USER_PROPERTIES_SEARCH_SIMPLE"],
		"USER_PROPERTIES_SEARCH_ADV" => $arParams["USER_PROPERTIES_SEARCH_ADV"],
		"USER_FIELDS_LIST" => $arParams["SONET_USER_FIELDS_LIST"],
		"USER_PROPERTY_LIST" => $arParams["SONET_USER_PROPERTY_LIST"],
		"USER_FIELDS_SEARCHABLE" => $arParams["SONET_USER_FIELDS_SEARCHABLE"],
		"USER_PROPERTY_SEARCHABLE" => $arParams["SONET_USER_PROPERTY_SEARCHABLE"],
		"GROUP_PROPERTY" => $arParams["GROUP_PROPERTY"],		
		"GROUP" => array()
	),
	$arResult
);

$arResult["PATH_TO_SEARCH_INNER"] = $arResult["PATH_TO_SEARCH"];
$arParams["PATH_TO_SEARCH_EXTERNAL"] = Trim($arParams["PATH_TO_SEARCH_EXTERNAL"]);
if (StrLen($arParams["PATH_TO_SEARCH_EXTERNAL"]) > 0)
	$arResult["PATH_TO_SEARCH"] = $arParams["PATH_TO_SEARCH_EXTERNAL"];

$arParams["ERROR_MESSAGE"] = "";
$arParams["NOTE_MESSAGE"] = "";
/********************************************************************
				Search Index
********************************************************************/
if(check_bitrix_sessid() || $_SERVER['REQUEST_METHOD'] == "PUT")
{
	global $bxSocNetSearch;
	if(!is_object($bxSocNetSearch))
	{
		$bxSocNetSearch = new CSocNetSearch($arResult["VARIABLES"]["user_id"], $arResult["VARIABLES"]["group_id"],
			array(
				"BLOG_GROUP_ID" => $arParams["BLOG_GROUP_ID"],
				"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
				"PATH_TO_GROUP_BLOG_POST" => $arResult["PATH_TO_GROUP_BLOG_POST"],
				"PATH_TO_GROUP_BLOG_COMMENT" => $arResult["PATH_TO_GROUP_BLOG_POST"]."?commentId=#comment_id###comment_id#",
				"PATH_TO_USER_BLOG" => $arResult["PATH_TO_USER_BLOG"],
				"PATH_TO_USER_BLOG_POST" => $arResult["PATH_TO_USER_BLOG_POST"],
				"PATH_TO_USER_BLOG_COMMENT" => $arResult["PATH_TO_USER_BLOG_POST"]."?commentId=#comment_id###comment_id#",

				"FORUM_ID" => $arParams["FORUM_ID"],
				"PATH_TO_GROUP_FORUM_MESSAGE" => $arResult["PATH_TO_GROUP_FORUM_MESSAGE"],
				"PATH_TO_USER_FORUM_MESSAGE" => $arResult["PATH_TO_USER_FORUM_MESSAGE"],

				"PHOTO_GROUP_IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
				"PATH_TO_GROUP_PHOTO_ELEMENT" => $arResult["PATH_TO_GROUP_PHOTO_ELEMENT"],
				"PHOTO_USER_IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],
				"PATH_TO_USER_PHOTO_ELEMENT" => $arResult["PATH_TO_USER_PHOTO_ELEMENT"],
				"PHOTO_FORUM_ID" => $arParams["PHOTO_FORUM_ID"],

				"CALENDAR_GROUP_IBLOCK_ID" => $arParams["CALENDAR_GROUP_IBLOCK_ID"],
				"PATH_TO_GROUP_CALENDAR_ELEMENT" => $arResult["PATH_TO_GROUP_CALENDAR"]."?EVENT_ID=#element_id#",

				"TASK_IBLOCK_ID" => $arParams["TASK_IBLOCK_ID"],
				"PATH_TO_GROUP_TASK_ELEMENT" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
				"PATH_TO_USER_TASK_ELEMENT" => $arResult["PATH_TO_USER_TASKS_TASK"],
				"TASK_FORUM_ID" => $arParams["TASK_FORUM_ID"],

				"FILES_PROPERTY_CODE" => $arParams["NAME_FILE_PROPERTY"],
				"FILES_FORUM_ID" => $arParams["FILES_FORUM_ID"],
				"FILES_GROUP_IBLOCK_ID" => $arParams["FILES_GROUP_IBLOCK_ID"],
				"PATH_TO_GROUP_FILES_ELEMENT" => $arResult["PATH_TO_GROUP_FILES_ELEMENT"],
				"FILES_USER_IBLOCK_ID" => $arParams["FILES_USER_IBLOCK_ID"],
				"PATH_TO_USER_FILES_ELEMENT" => $arResult["PATH_TO_USER_FILES_ELEMENT"],
		));
		AddEventHandler("search", "BeforeIndex", Array($bxSocNetSearch, "BeforeIndex"));
		AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array($bxSocNetSearch, "IBlockElementUpdate"));
		AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array($bxSocNetSearch, "IBlockElementUpdate"));
		AddEventHandler("iblock", "OnAfterIBlockElementDelete", Array($bxSocNetSearch, "IBlockElementDelete"));
	}
}
/********************************************************************
				WebDav
********************************************************************/
if (strPos($componentPage, "user_files") === false && strPos($componentPage, "group_files") === false)
{
	$sCurrUrl = strToLower(str_replace("//", "/", "/".$APPLICATION->GetCurPage()."/"));
	$arBaseUrl = array(
		"user" => $arParams["FILES_USER_BASE_URL"],
		"group" => $arParams["FILES_GROUP_BASE_URL"]);

	if ($arParams["SEF_MODE"] == "Y" )
	{
		$arBaseUrl = array(
			"user" => $arResult["PATH_TO_USER_FILES"],
			"group" => $arResult["PATH_TO_GROUP_FILES"]);
	}
	foreach ($arBaseUrl as $key => $res)
	{
		if (strPos($res, "#path#") !== false)
			$res = subStr($res, 0, strPos($res, "#path#"));
		$res = strToLower(str_replace("//", "/", "/".$res."/"));
		$pos = strPos($res, "#".$key."_id#");
		if ($pos !== false && subStr($res, 0, $pos) == subStr($sCurrUrl, 0, $pos))
		{
			$v1 = subStr($res, $pos + strLen("#".$key."_id#"));
			$v2 = subStr($sCurrUrl, $pos);
			$v3 = subStr($v2, strPos($v2, subStr($v1, 0, 1)), strLen($v1));
			if ($v1 == $v3)
			{
				$componentPage = $key."_files";
				$arResult["VARIABLES"]["#".$key."_id#"] = intVal(subStr($v2, 0, strPos($v2, subStr($v1, 0, 1))));
				$arResult["VARIABLES"][$key."_id"] = intVal(subStr($v2, 0, strPos($v2, subStr($v1, 0, 1))));
			}
		}
	}
}
if (strPos($componentPage, "user_files")!== false || strPos($componentPage, "group_files")!== false)
{
	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/webdav.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "WebDAV file is not exist.";
		$res = 0;
	}
	else
	{
		$res = include_once($path);
	}

	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");
}
/********************************************************************
				/WebDav
********************************************************************/
/********************************************************************
				Photogalley
********************************************************************/
elseif (strPos($componentPage, "user_photo")!== false || strPos($componentPage, "group_photo")!== false)
{
	if (strPos($componentPage, "user_photofull")!== false || strPos($componentPage, "group_photofull")!== false)
		$componentPage = str_replace("_photofull", "_photo", $componentPage); 

	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/photogallery.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "Photogallery file is not exist.";
		$res = 0;
	}
	else
	{
		$res = include_once($path);
	}
	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");
}
/********************************************************************
				/Photogalley
********************************************************************/
/********************************************************************
				Forum
********************************************************************/
elseif (strPos($componentPage, "user_forum")!== false || strPos($componentPage, "group_forum")!== false ||
	$componentPage == "user" || $componentPage == "group" || $componentPage == "index")
{
	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/forum.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "Forum file is not exist.";
		$res = 0;
	}
	else
	{
		$res = include_once($path);
	}
	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");
}
/********************************************************************
				/Forum
********************************************************************/
/********************************************************************
				Content Search
********************************************************************/
elseif (strPos($componentPage, "user_content_search")!== false || strPos($componentPage, "group_content_search")!== false)
{
	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/search.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "Content search file is not exist.";
		$res = 0;
	}
	else
	{
		$res = include_once($path);
	}
	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");
}
/********************************************************************
				/Content search
********************************************************************/


/********************************************************************
				Buziness-process
********************************************************************/
elseif ($componentPage == "bizproc_task")
{
	$componentPage = "bizproc_edit";
}
elseif ($componentPage == "bizproc_task_list")
{
	$componentPage = "bizproc";
}
/********************************************************************
				/Buziness-process
********************************************************************/

CUtil::InitJSCore(array("window", "ajax"));
$this->IncludeComponentTemplate($componentPage, array_key_exists($componentPage, $arCustomPagesPath) ? $arCustomPagesPath[$componentPage] : "");
/********************************************************************
				Activity after
********************************************************************/
if (strPos($componentPage, "group_photo")!== false || strPos($componentPage, "group_file")!== false ||
	strPos($componentPage, "user_photo")!== false || strPos($componentPage, "user_file")!== false)
{
	if (strPos($componentPage, "group_photo")!== false || strPos($componentPage, "user_photo")!== false)
		$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/photogallery_2.php");
	else
		$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/webdav_2.php");
	if (file_exists($path))
		include_once($path);
}

//top panel button to reindex
if($GLOBALS['USER']->IsAdmin())
{
	$GLOBALS['APPLICATION']->AddPanelButton(array(
		"HREF"=> $arResult["PATH_TO_REINDEX"],
		"ICON"=>"bx-panel-reindex-icon",
		"ALT"=>GetMessage('SONET_PANEL_REINDEX_TITLE'),
		"TEXT"=>GetMessage('SONET_PANEL_REINDEX'),
		"MAIN_SORT"=>"1000",
		"SORT"=>100,
		"MODE"=>array("configure"),
	));
}
?>