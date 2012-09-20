create table b_lang
(
	LID			char(2) 	not null,
	SORT			INT(18) 	not null default '100',
	DEF			char(1) 	not null default 'N',
	ACTIVE		char(1) 	not null default 'Y',
	NAME			varchar(50) not null,
	DIR			varchar(50) not null,
	FORMAT_DATE	varchar(50) not null,
	FORMAT_DATETIME varchar(50) not null,
	CHARSET varchar(255),
	LANGUAGE_ID char(2) NOT NULL,
	DOC_ROOT varchar(255) NULL,
	DOMAIN_LIMITED char(1) NOT NULL default 'N',
	SERVER_NAME varchar(255) NULL,
	SITE_NAME varchar(255) NULL,
	EMAIL varchar(255) NULL,
	primary key (LID)
);

CREATE TABLE b_language
(
	LID char(2) NOT NULL,
	SORT int NOT NULL default '100',
	DEF char(1) NOT NULL default 'N',
	ACTIVE char(1) NOT NULL default 'Y',
	NAME varchar(50) NOT NULL,
	FORMAT_DATE varchar(50) NOT NULL,
	FORMAT_DATETIME varchar(50) NOT NULL,
	CHARSET varchar(255) NULL,
	DIRECTION char(1) NOT NULL default 'Y',
	primary key (LID)
);

CREATE TABLE b_lang_domain
(
	LID char(2) NOT NULL,
	DOMAIN varchar(255) NOT NULL,
	primary key (LID, DOMAIN)
);

create table b_event_type
(
	ID 			INT(18) 		not null auto_increment,
	LID 		char(2) 	not null,
	EVENT_NAME 	varchar(50) 	not null,
	NAME 		varchar(100),
	DESCRIPTION text,
	SORT 		INT(18) 		not null default '150',
	primary key (ID),
	unique ux_1 (EVENT_NAME, LID)
);


create table b_event_message
(
	ID 			INT(18) 		not null auto_increment,
	TIMESTAMP_X timestamp,
	EVENT_NAME 	varchar(50) 	not null,
	LID 		char(2) 		null,
	ACTIVE 		char(1) 		not null default 'Y',
	EMAIL_FROM 	varchar(255) 	not null default '#EMAIL_FROM#',
	EMAIL_TO 	varchar(255) 	not null default '#EMAIL_TO#',
	SUBJECT 	varchar(255),
	MESSAGE 	text,
	BODY_TYPE 	varchar(4) 		not null default 'text',
	BCC 		text,
	REPLY_TO	varchar(255) NULL,
	CC		varchar(255) NULL,
	IN_REPLY_TO	varchar(255) NULL,
	PRIORITY	varchar(50) NULL,
	FIELD1_NAME	varchar(50) NULL,
	FIELD1_VALUE	varchar(255) NULL,
	FIELD2_NAME	varchar(50) NULL,
	FIELD2_VALUE	varchar(255) NULL,
	primary key (ID)
);

create table b_event
(
	ID 			INT(18) 	not null auto_increment,
	EVENT_NAME 	varchar(50) not null,
	MESSAGE_ID      int(18),
	LID 		varchar(255) 	not null,
	C_FIELDS 	longtext,
	DATE_INSERT datetime,
	DATE_EXEC 	datetime,
	SUCCESS_EXEC char(1) 	not null default 'N',
	DUPLICATE char(1) 	not null default 'Y',
	primary key (ID),
	index ix_success (SUCCESS_EXEC)
);

create table b_group (
   ID int(18) not null auto_increment,
   TIMESTAMP_X timestamp,
   ACTIVE char(1) not null default 'Y',
   C_SORT int(18) not null default '100',
   ANONYMOUS char(1) not null default 'N',
   NAME varchar(255) not null,
   DESCRIPTION varchar(255),
   SECURITY_POLICY text null,
   STRING_ID varchar(255),
   primary key (ID)
);

create table b_user (
   ID int(18) not null auto_increment,
   TIMESTAMP_X timestamp,
   LOGIN varchar(50) not null,
   `PASSWORD` varchar(50) not null,
   CHECKWORD varchar(50),
   ACTIVE char(1) not null default 'Y',
   NAME varchar(50),
   LAST_NAME varchar(50),
   EMAIL varchar(255) not null,
   LAST_LOGIN datetime,
   DATE_REGISTER datetime not null,
   LID char(2),
   PERSONAL_PROFESSION varchar(255),
   PERSONAL_WWW varchar(255),
   PERSONAL_ICQ varchar(255),
   PERSONAL_GENDER char(1),
   PERSONAL_BIRTHDATE varchar(50),
   PERSONAL_PHOTO int(18),
   PERSONAL_PHONE varchar(255),
   PERSONAL_FAX varchar(255),
   PERSONAL_MOBILE varchar(255),
   PERSONAL_PAGER varchar(255),
   PERSONAL_STREET text,
   PERSONAL_MAILBOX varchar(255),
   PERSONAL_CITY varchar(255),
   PERSONAL_STATE varchar(255),
   PERSONAL_ZIP varchar(255),
   PERSONAL_COUNTRY varchar(255),
   PERSONAL_NOTES text,
   WORK_COMPANY varchar(255),
   WORK_DEPARTMENT varchar(255),
   WORK_POSITION varchar(255),
   WORK_WWW varchar(255),
   WORK_PHONE varchar(255),
   WORK_FAX varchar(255),
   WORK_PAGER varchar(255),
   WORK_STREET text,
   WORK_MAILBOX varchar(255),
   WORK_CITY varchar(255),
   WORK_STATE varchar(255),
   WORK_ZIP varchar(255),
   WORK_COUNTRY varchar(255),
   WORK_PROFILE text,
   WORK_LOGO int(18),
   WORK_NOTES text,
   ADMIN_NOTES text,
   STORED_HASH varchar(32),
   XML_ID varchar(255),
   PERSONAL_BIRTHDAY date,
   EXTERNAL_AUTH_ID varchar(255),
   CHECKWORD_TIME datetime,
   SECOND_NAME varchar(50),
   CONFIRM_CODE varchar(8),
   LOGIN_ATTEMPTS int(18),
   LAST_ACTIVITY_DATE datetime null,
   primary key (ID),
   unique ix_login (LOGIN, EXTERNAL_AUTH_ID),
   index ix_b_user_email (EMAIL),
   index ix_b_user_activity_date (LAST_ACTIVITY_DATE),
   index IX_B_USER_XML_ID (XML_ID)
);

create table b_user_group
(
	USER_ID 	INT(18) not null,
	GROUP_ID 	INT(18) not null,
	DATE_ACTIVE_FROM datetime null,
	DATE_ACTIVE_TO datetime null,
	unique ix_user_group (USER_ID, GROUP_ID),
	index ix_user_group_group (GROUP_ID)
);


CREATE TABLE b_module
(
	ID	 		VARCHAR(50) NOT NULL,
	DATE_ACTIVE timestamp not null,
	primary key (ID)
);


CREATE TABLE b_option
(
	MODULE_ID	VARCHAR(50),
	NAME	 	VARCHAR(50) 	NOT NULL,
	VALUE	 	TEXT,
	DESCRIPTION	VARCHAR(255),
	SITE_ID		CHAR(2) default null,
	UNIQUE ix_option(MODULE_ID, NAME, SITE_ID),
	INDEX ix_option_name(NAME)
);

CREATE TABLE b_module_to_module
(
	ID int not null auto_increment,
    TIMESTAMP_X    TIMESTAMP	not null,
    SORT         INT(18) 	not null default '100',
    FROM_MODULE_ID VARCHAR(50) NOT NULL,
    MESSAGE_ID     VARCHAR(50) NOT NULL,
    TO_MODULE_ID   VARCHAR(50) NOT NULL,
    TO_PATH        VARCHAR(255),
    TO_CLASS       VARCHAR(50),
    TO_METHOD      VARCHAR(50),
	TO_METHOD_ARG  varchar(255),
	primary key (ID),
	INDEX ix_module_to_module(FROM_MODULE_ID, MESSAGE_ID, TO_MODULE_ID, TO_CLASS, TO_METHOD)
);

create table b_agent
(
	ID 				INT(18) 		not null auto_increment,
	MODULE_ID 		varchar(50),
	SORT			INT(18) 	not null default '100',
	NAME 			text	null,
	ACTIVE 			char(1) 		not null default 'Y',
	LAST_EXEC 		datetime,
	NEXT_EXEC 		datetime		not null,
	DATE_CHECK 		datetime,
	AGENT_INTERVAL 	INT(18) 					default '86400',
	IS_PERIOD 		char(1) 					default 'Y',
	USER_ID		INT(18) default null,
	primary key (ID),
	index ix_act_next_exec(ACTIVE, NEXT_EXEC),
	index ix_agent_user_id(USER_ID)
);

CREATE TABLE b_file
(
	ID				INT(18)		NOT NULL auto_increment,
	TIMESTAMP_X 	TIMESTAMP	not null,
	MODULE_ID 		varchar(50),
	HEIGHT			INT(18),
	WIDTH			INT(18),
	FILE_SIZE		INT(18)	NOT NULL,
	CONTENT_TYPE	VARCHAR(255)	DEFAULT 'IMAGE',
	SUBDIR			VARCHAR(255),
	FILE_NAME		VARCHAR(255)	NOT NULL,
	ORIGINAL_NAME VARCHAR(255) NULL,
	DESCRIPTION VARCHAR(255) NULL,
	PRIMARY KEY (ID)
);


create table b_module_group
(
  ID int(11) not null auto_increment,
  MODULE_ID varchar(50) not null,
  GROUP_ID int(11) not null,
  G_ACCESS varchar(255) not null,
  primary key (ID),
  unique UK_GROUP_MODULE(MODULE_ID, GROUP_ID)
);

create table b_favorite (
	ID int(18) not null auto_increment,
	TIMESTAMP_X datetime,
	DATE_CREATE datetime,
	C_SORT int(18) not null default '100',
	MODIFIED_BY int(18),
	CREATED_BY int(18),
	MODULE_ID varchar(50) null,
	NAME varchar(255),
	URL text,
	COMMENTS text,
	LANGUAGE_ID char(2) null,
	USER_ID int null,
	COMMON char(1) not null default 'Y',
	primary key (ID)
);

create table b_user_stored_auth
(
	ID int(18) not null auto_increment,
	USER_ID int(18) not null,
	DATE_REG datetime not null,
	LAST_AUTH datetime not null,
	STORED_HASH varchar(32) not null,
	TEMP_HASH char(1) not null default 'N',
	IP_ADDR int(10) unsigned not null,
	primary key (ID),
	index ux_user_hash (USER_ID)
);

CREATE TABLE b_site_template
(
	ID int NOT NULL auto_increment,
	SITE_ID char(2) NOT NULL,
	`CONDITION` varchar(255) NULL,
	SORT int NOT NULL default '500',
	TEMPLATE varchar(50) NOT NULL,
	primary key (ID)
);

ALTER TABLE b_site_template ADD UNIQUE INDEX UX_B_SITE_TEMPLATE(SITE_ID, `CONDITION`, TEMPLATE);


CREATE TABLE b_event_message_site
(
	EVENT_MESSAGE_ID int NOT NULL,
	SITE_ID char(2) NOT NULL,
	primary key (EVENT_MESSAGE_ID, SITE_ID)
);

create table b_user_option
(
	ID int not null auto_increment,
	USER_ID int null,
	CATEGORY varchar(50) not null,
	NAME varchar(255) not null,
	VALUE text null,
	COMMON char(1) not null default 'N',
	primary key (ID),
	index ix_user_option_param(CATEGORY, NAME),
	index ix_user_option_user(USER_ID)
);

CREATE TABLE b_captcha
(
	ID varchar(32) not null,
	CODE varchar(20) not null,
	IP varchar(15) not null,
	DATE_CREATE datetime not null,
	UNIQUE UX_B_CAPTCHA(ID)
);

CREATE TABLE b_user_field
(
	ID int(11) not null auto_increment,
	ENTITY_ID varchar(20),
	FIELD_NAME varchar(20),
	USER_TYPE_ID varchar(50),
	XML_ID varchar(255),
	SORT int,
	MULTIPLE char(1) not null default 'N',
	MANDATORY char(1) not null default 'N',
	SHOW_FILTER char(1) not null default 'N',
	SHOW_IN_LIST char(1) not null default 'Y',
	EDIT_IN_LIST char(1) not null default 'Y',
	IS_SEARCHABLE char(1) not null default 'N',
	SETTINGS text,
	PRIMARY KEY (ID),
	UNIQUE ux_user_type_entity(ENTITY_ID, FIELD_NAME)
);

create table b_user_field_lang (
	USER_FIELD_ID int(11),
	LANGUAGE_ID char(2),
	EDIT_FORM_LABEL varchar(255),
	LIST_COLUMN_LABEL varchar(255),
	LIST_FILTER_LABEL varchar(255),
	ERROR_MESSAGE varchar(255),
	HELP_MESSAGE varchar(255),
	PRIMARY KEY (USER_FIELD_ID, LANGUAGE_ID)
);

create table if not exists b_user_field_enum
(
	ID int(11) not null auto_increment,
	USER_FIELD_ID int(11),
	VALUE varchar(255) not null,
	DEF char(1) not null default 'N',
	SORT int(11) not null default 500,
	XML_ID varchar(255) not null,
	primary key (ID),
	unique ux_user_field_enum(USER_FIELD_ID, XML_ID)
);

CREATE TABLE b_task(
	ID int(18) not null auto_increment,
	NAME varchar(100) not null,
	LETTER char(1),
	MODULE_ID varchar(50) not null,
	SYS char(1) not null,
	DESCRIPTION varchar(255),
	BINDING varchar(50) default 'module',
	primary key (ID),
	index ix_task(MODULE_ID, BINDING, LETTER, SYS)
);

CREATE TABLE b_group_task(
	GROUP_ID int(18) not null,
	TASK_ID int(18) not null,
	EXTERNAL_ID varchar(50) DEFAULT '',
	primary key (GROUP_ID,TASK_ID)
);

CREATE TABLE b_operation(
	ID int(18) not null auto_increment,
	NAME varchar(50) not null,
	MODULE_ID varchar(50) not null,
	DESCRIPTION varchar(255),
	BINDING varchar(50) default 'module',
	primary key (ID)
);

CREATE TABLE b_task_operation(
	TASK_ID int(18) not null,
	OPERATION_ID int(18) not null,
	primary key (TASK_ID,OPERATION_ID)
);

CREATE TABLE b_group_subordinate(
	ID int(18) not null,
	AR_SUBGROUP_ID varchar(255) not null,
	primary key (ID)
);

CREATE TABLE b_rating (
  ID int(11) NOT NULL auto_increment,
  ACTIVE char(1) NOT NULL,
  NAME varchar(512) NOT NULL,
  ENTITY_ID varchar(50) NOT NULL,
  CALCULATION_METHOD varchar(3) NOT NULL default 'SUM',
  CREATED datetime default NULL,
  LAST_MODIFIED datetime default NULL,
  LAST_CALCULATED datetime default NULL,
  POSITION char(1) null default 'N',
  AUTHORITY char(1) null default 'N',
  CALCULATED char(1) NOT NULL default 'N',
  CONFIGS text,
  PRIMARY KEY  (ID)
);

CREATE TABLE b_rating_component (
  ID int(11) NOT NULL auto_increment,
  RATING_ID int(11) NOT NULL,
  ACTIVE char(1) NOT NULL default 'N',
  ENTITY_ID varchar(50) NOT NULL,
  MODULE_ID varchar(50) NOT NULL,
  RATING_TYPE varchar(50) NOT NULL,
  NAME varchar(50) NOT NULL,
  COMPLEX_NAME varchar(200) NOT NULL,
  CLASS varchar(255) NOT NULL,
  CALC_METHOD varchar(255) NOT NULL,
  EXCEPTION_METHOD varchar(255) NULL,
  LAST_MODIFIED datetime default NULL,
  LAST_CALCULATED datetime default NULL,
  NEXT_CALCULATION datetime default NULL,
  REFRESH_INTERVAL int(11) NOT NULL,
  CONFIG text,
  PRIMARY KEY  (ID),
  KEY IX_RATING_ID_1 (RATING_ID, ACTIVE, NEXT_CALCULATION)
);

CREATE TABLE b_rating_component_results (
  ID int(11) NOT NULL auto_increment,
  RATING_ID int(11) NOT NULL,
  ENTITY_TYPE_ID varchar(50) NOT NULL,
  ENTITY_ID int(11) NOT NULL,
  MODULE_ID varchar(50) NOT NULL,
  RATING_TYPE varchar(50) NOT NULL,
  NAME varchar(50) NOT NULL,
  COMPLEX_NAME varchar(200) NOT NULL,
  CURRENT_VALUE decimal(18,4) NULL,
  PRIMARY KEY  (ID),
  KEY IX_ENTITY_TYPE_ID (ENTITY_TYPE_ID),
  KEY IX_COMPLEX_NAME (COMPLEX_NAME),
  KEY IX_RATING_ID_2 (RATING_ID, COMPLEX_NAME)
);

CREATE TABLE b_rating_results (
  ID int(11) NOT NULL auto_increment,
  RATING_ID int(11) NOT NULL,
  ENTITY_TYPE_ID varchar(50) NOT NULL,
  ENTITY_ID int(11) NOT NULL,
  CURRENT_VALUE decimal(18,4) NULL,
  PREVIOUS_VALUE decimal(18,4) NULL,
  CURRENT_POSITION int(11) null default '0',
  PREVIOUS_POSITION int(11) null default '0',
  PRIMARY KEY  (ID),
  KEY IX_RATING_3 (RATING_ID, ENTITY_TYPE_ID, ENTITY_ID),
  KEY IX_RATING_4 (RATING_ID, ENTITY_ID)
);

CREATE TABLE b_rating_vote (
  ID int(11) NOT NULL auto_increment,
  RATING_VOTING_ID int(11) NOT NULL,
  VALUE decimal(18,4) NOT NULL,
  ACTIVE char(1) NOT NULL,
  CREATED datetime NOT NULL,
  USER_ID int(11) NOT NULL,
  USER_IP varchar(64) NOT NULL,
  PRIMARY KEY  (ID),
  KEY IX_RAT_VOTING_ID (RATING_VOTING_ID, USER_ID)
);

CREATE TABLE b_rating_voting (
  ID int(11) NOT NULL auto_increment,
  ENTITY_TYPE_ID varchar(50) NOT NULL,
  ENTITY_ID int(11) NOT NULL,
  ACTIVE char(1) NOT NULL,
  CREATED datetime default NULL,
  LAST_CALCULATED datetime default NULL,
  TOTAL_VALUE decimal(18,4) NOT NULL,
  TOTAL_VOTES int(11) NOT NULL,
  TOTAL_POSITIVE_VOTES int(11) NOT NULL,
  TOTAL_NEGATIVE_VOTES int(11) NOT NULL,
  PRIMARY KEY  (ID),
  KEY IX_ENTITY_TYPE_ID_2 (ENTITY_TYPE_ID, ENTITY_ID, ACTIVE)
);

CREATE TABLE b_rating_rule (
  ID int(11) NOT NULL auto_increment,
  ACTIVE char(1) NOT NULL default 'N',
  NAME varchar(256) NOT NULL,
  ENTITY_TYPE_ID varchar(50) NOT NULL,
  CONDITION_NAME varchar(200) NOT NULL,
  CONDITION_CLASS varchar(255) NOT NULL,
  CONDITION_METHOD varchar(255) NOT NULL,
  CONDITION_CONFIG text NOT NULL,
  ACTION_NAME varchar(200) NOT NULL,
  ACTION_CONFIG text NOT NULL,
  ACTIVATE char(1) NOT NULL default 'N',
  ACTIVATE_CLASS varchar(255) NOT NULL,
  ACTIVATE_METHOD varchar(255) NOT NULL,
  DEACTIVATE char(1) NOT NULL default 'N',
  DEACTIVATE_CLASS varchar(255) NOT NULL,
  DEACTIVATE_METHOD varchar(255) NOT NULL,
  CREATED datetime default NULL,
  LAST_MODIFIED datetime default NULL,
  LAST_APPLIED datetime default NULL,
  primary key (ID)
);

CREATE TABLE b_rating_rule_vetting (
  ID int(11) NOT NULL auto_increment,
  RULE_ID int(11) NOT NULL,
  ENTITY_TYPE_ID varchar(50) NOT NULL,
  ENTITY_ID int(11) NOT NULL,
  ACTIVATE char(1) NOT NULL default 'N',
  APPLIED char(1) NOT NULL default 'N',
  primary key (ID),
  KEY RULE_ID (RULE_ID,ENTITY_TYPE_ID,ENTITY_ID)
);

CREATE TABLE b_rating_user (
  ID int(11) NOT NULL auto_increment,
  RATING_ID int(11) NOT NULL,
  ENTITY_ID int(11) NOT NULL,
  BONUS decimal(18,4) NULL default '0.0000',
  VOTE_WEIGHT decimal(18,4) NULL default '0.0000',
  VOTE_COUNT int(11) NULL default '0',
  PRIMARY KEY  (ID),
  KEY RATING_ID (RATING_ID,ENTITY_ID)
);

CREATE TABLE b_rating_vote_group (
  ID int(11) NOT NULL auto_increment,
  GROUP_ID int(11) NOT NULL,
  TYPE char(1) NOT NULL,
  PRIMARY KEY  (ID),
  KEY RATING_ID (GROUP_ID, TYPE)
);

CREATE TABLE b_rating_weight (
  ID int(11) NOT NULL auto_increment,
  RATING_FROM decimal(18,4) NOT NULL,
  RATING_TO decimal(18,4) NOT NULL,
  WEIGHT decimal(18,4) default '0',
  COUNT int(11) default '0',
  PRIMARY KEY  (ID)
);
insert into b_rating_weight (RATING_FROM, RATING_TO, WEIGHT, COUNT) VALUES (-1000000, 1000000, 1, 10);

insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(1,'view_own_profile','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(2,'view_subordinate_users','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(3,'view_all_users','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(4,'view_groups','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(5,'view_tasks','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(6,'view_other_settings','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(7,'edit_own_profile','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(8,'edit_all_users','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(9,'edit_subordinate_users','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(10,'edit_groups','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(11,'edit_tasks','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(12,'edit_other_settings','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(13,'cache_control','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(14,'edit_php','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(15,'fm_view_permission','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(16,'fm_edit_permission','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(17,'fm_edit_existent_folder','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(18,'fm_create_new_file','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(19,'fm_edit_existent_file','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(20,'fm_create_new_folder','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(21,'fm_delete_file','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(22,'fm_delete_folder','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(23,'fm_view_file','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(24,'fm_view_listing','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(25,'fm_edit_in_workflow','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(26,'fm_rename_file','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(27,'fm_rename_folder','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(28,'fm_upload_file','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(29,'fm_add_to_menu','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(30,'fm_download_file','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(31,'fm_lpa','main',null,'file');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(32,'lpa_template_edit','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(33,'view_event_log','main',null,'module');
insert into b_operation (ID,NAME,MODULE_ID,DESCRIPTION,BINDING) values(34,'edit_ratings','main',null,'module');

insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(1,'main_denied','D','main','Y',null,'module');
insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(2,'main_change_profile','P','main','Y',null,'module');
insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(3,'main_view_all_settings','R','main','Y',null,'module');
insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(4,'main_view_all_settings_change_profile','T','main','Y',null,'module');
insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(5,'main_edit_subordinate_users','V','main','Y',null,'module');
insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(6,'main_full_access','W','main','Y',null,'module');
insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(7,'fm_folder_access_denied','D','main','Y',null,'file');
insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(8,'fm_folder_access_read','R','main','Y',null,'file');
insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(9,'fm_folder_access_write','W','main','Y',null,'file');
insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(10,'fm_folder_access_full','X','main','Y',null,'file');
insert into b_task (ID,NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING) values(11,'fm_folder_access_workflow','U','main','Y',null,'file');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('2','1');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('2','7');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('3','1');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('3','3');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('3','4');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('3','5');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('3','6');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('4','1');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('4','3');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('4','4');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('4','5');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('4','6');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('4','7');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('5','1');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('5','2');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('5','4');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('5','5');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('5','6');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('5','7');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('5','9');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','1');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','3');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','4');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','5');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','6');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','7');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','8');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','10');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','11');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','12');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','13');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','32');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','33');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('6','34');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('8','15');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('8','23');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('8','24');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','15');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','17');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','18');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','19');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','20');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','21');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','22');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','23');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','24');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','25');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','26');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','27');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','28');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','29');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','30');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('9','31');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','15');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','16');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','17');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','18');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','19');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','20');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','21');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','22');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','23');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','24');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','25');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','26');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','27');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','28');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','29');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','30');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('10','31');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('11','15');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('11','19');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('11','23');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('11','24');
insert into b_task_operation (TASK_ID,OPERATION_ID) VALUES ('11','25');

CREATE TABLE b_event_log
(
	/*SYSTEM GENERATED*/
	ID INT(18) NOT NULL auto_increment,
	TIMESTAMP_X TIMESTAMP not null,

	/*CALLER INFO*/
	SEVERITY VARCHAR(50) not null, /*SECURITY, WARNING, NOTICE*/
	AUDIT_TYPE_ID VARCHAR(50) not null, /*LOGIN_OK, LOGIN_WRONG_PASSWORD*/
	MODULE_ID VARCHAR(50) not null, /*main, iblock, main.register */
	ITEM_ID VARCHAR(255) not null, /*user login, element id*/

	/*FROM $_SERVER*/
	REMOTE_ADDR VARCHAR(40),
	USER_AGENT TEXT, /*2000 for oracle and mssql*/
	REQUEST_URI TEXT, /*2000 for oracle and mssql*/

	/*FROM CONSTANTS AND VARIABLES*/
	SITE_ID CHAR(2), /*if defined*/
	USER_ID INT(18), /*if logged in*/
	GUEST_ID INT(18), /* if statistics installed*/

	/*ADDITIONAL*/
	DESCRIPTION MEDIUMTEXT,
	PRIMARY KEY (ID),
	index ix_b_event_log_time(TIMESTAMP_X)
);

CREATE TABLE b_cache_tag
(
	SITE_ID char(2),
	CACHE_SALT char(4),
	RELATIVE_PATH varchar(255),
	TAG varchar(100),
	index ix_b_cache_tag_0 (SITE_ID, CACHE_SALT, RELATIVE_PATH(50)),
	index ix_b_cache_tag_1 (TAG)
);

CREATE TABLE b_user_hit_auth
(
	ID int(18) NOT NULL auto_increment,
	USER_ID int(18) NOT NULL,
	HASH varchar(32) NOT NULL,
	URL varchar(255) NOT NULL,
	SITE_ID char(2) default NULL,
	TIMESTAMP_X datetime NOT NULL,
	PRIMARY KEY (ID),
	INDEX IX_USER_HIT_AUTH_1(HASH),
	INDEX IX_USER_HIT_AUTH_2(USER_ID),
	INDEX IX_USER_HIT_AUTH_3(TIMESTAMP_X)
);

CREATE TABLE b_undo
(
	ID varchar(255) not null,
	MODULE_ID varchar(50) null,
	UNDO_TYPE varchar(50) null,
	UNDO_HANDLER varchar(255) null,
	CONTENT mediumtext null,
	USER_ID int null,
	TIMESTAMP_X int null,
	primary key (ID)
);
