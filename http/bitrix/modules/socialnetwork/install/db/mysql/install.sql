create table b_sonet_group_subject
(
  ID int not null auto_increment,
  SITE_ID char(2) not null,
  NAME varchar(255) not null,
  SORT int(10) not null default '100',
  primary key (ID)
);

create table b_sonet_group
(
  ID int not null auto_increment,
  SITE_ID char(2) not null,
  NAME varchar(255) not null,
  DESCRIPTION text null,
  DATE_CREATE datetime not null,
  DATE_UPDATE datetime not null,
  ACTIVE char(1) not null default 'Y',
  VISIBLE char(1) not null default 'Y',
  OPENED char(1) not null default 'N',
  SUBJECT_ID int not null,
  OWNER_ID int not null,
  KEYWORDS varchar(255) null,
  IMAGE_ID int null,
  NUMBER_OF_MEMBERS int not null default 0,
  INITIATE_PERMS char(1) not null default 'K',
  DATE_ACTIVITY datetime not null,
  CLOSED char(1) not null default 'N',
  SPAM_PERMS char(1) not null default 'K',
  primary key (ID),
  index IX_SONET_GROUP_1(OWNER_ID)
);

create table b_sonet_user2group
(
  ID int not null auto_increment,
  USER_ID int not null,
  GROUP_ID int not null,
  ROLE char(1) not null default 'U',
  DATE_CREATE datetime not null,
  DATE_UPDATE datetime not null,
  INITIATED_BY_TYPE char(1) not null default 'U',
  INITIATED_BY_USER_ID int not null,
  MESSAGE text null,
  primary key (ID),
  unique IX_SONET_USER2GROUP_1(USER_ID, GROUP_ID),
  index IX_SONET_USER2GROUP_2(USER_ID, GROUP_ID, ROLE),
  index IX_SONET_USER2GROUP_3(GROUP_ID, USER_ID, ROLE)
);

create table b_sonet_features
(
  ID int not null auto_increment,
  ENTITY_TYPE char(1) not null default 'G',
  ENTITY_ID int not null,
  FEATURE varchar(50) not null,
  FEATURE_NAME varchar(250) null,
  ACTIVE char(1) not null default 'Y',
  DATE_CREATE datetime not null,
  DATE_UPDATE datetime not null,
  primary key (ID),
  unique IX_SONET_GROUP_FEATURES_1(ENTITY_TYPE, ENTITY_ID, FEATURE)
);

create table b_sonet_features2perms
(
  ID int not null auto_increment,
  FEATURE_ID int not null,
  OPERATION_ID varchar(50) not null,
  ROLE char(1) not null,
  primary key (ID),
  unique IX_SONET_GROUP_FEATURES2PERMS_1(FEATURE_ID, OPERATION_ID),
  index IX_SONET_GROUP_FEATURES2PERMS_2(FEATURE_ID, ROLE, OPERATION_ID)
);

create table b_sonet_user_relations
(
  ID int not null auto_increment,
  FIRST_USER_ID int not null,
  SECOND_USER_ID int not null,
  RELATION char(1) not null default 'N',
  DATE_CREATE datetime not null,
  DATE_UPDATE datetime not null,
  MESSAGE text null,
  INITIATED_BY char(1) not null default 'F',
  primary key (ID),
  unique IX_SONET_RELATIONS_1(FIRST_USER_ID, SECOND_USER_ID),
  index IX_SONET_RELATIONS_2(FIRST_USER_ID, SECOND_USER_ID, RELATION)
);

create table b_sonet_messages
(
  ID int not null auto_increment,
  FROM_USER_ID int not null,
  TO_USER_ID int not null,
  TITLE varchar(250) null,
  MESSAGE text null,
  DATE_CREATE datetime not null,
  DATE_VIEW datetime null,
  MESSAGE_TYPE char(1) not null default 'P',
  FROM_DELETED char(1) not null default 'N',
  TO_DELETED char(1) not null default 'N',
  SEND_MAIL char(1) not null default 'N',
  EMAIL_TEMPLATE varchar(250) null,
  IS_LOG char(1) NULL,
  primary key (ID),
  index IX_SONET_MESSAGES_1(FROM_USER_ID),
  index IX_SONET_MESSAGES_2(TO_USER_ID)
);

create table b_sonet_smile (
   ID smallint(3) not null auto_increment,
   SMILE_TYPE char(1) not null default 'S',
   TYPING varchar(100) null,
   IMAGE varchar(128) not null,
   DESCRIPTION varchar(50),
   CLICKABLE char(1) not null default 'Y',
   SORT int(10) not null default '150',
   IMAGE_WIDTH int(11) not null default '0',
   IMAGE_HEIGHT int(11) not null default '0',
   primary key (ID));

create table b_sonet_smile_lang (
   ID int(11) not null auto_increment,
   SMILE_ID int(11) not null default '0',
   LID char(2) not null,
   NAME varchar(255) not null,
   primary key (ID),
   unique IX_SONET_SMILE_K (SMILE_ID, LID));

create table b_sonet_user_perms
(
  ID int not null auto_increment,
  USER_ID int not null,
  OPERATION_ID varchar(50) not null,
  RELATION_TYPE char(1) not null,
  primary key (ID),
  index IX_SONET_USER_PERMS_1(USER_ID),
  unique IX_SONET_USER_PERMS_2(USER_ID, OPERATION_ID)
);

create table b_sonet_user_events
(
  ID int not null auto_increment,
  USER_ID int not null,
  EVENT_ID varchar(50) not null,
  ACTIVE char(1) not null default 'Y',
  SITE_ID char(2) not null,
  primary key (ID),
  index IX_SONET_USER_PERMS_1(USER_ID),
  unique IX_SONET_USER_PERMS_2(USER_ID, EVENT_ID)
);

create table b_sonet_log
(
  ID int not null auto_increment,
  ENTITY_TYPE char(1) not null default 'G',
  ENTITY_ID int not null,
  EVENT_ID varchar(50) not null,
  USER_ID int null,
  LOG_DATE datetime not null,
  SITE_ID char(2) null,
  TITLE_TEMPLATE varchar(250) null,
  TITLE varchar(250) not null,
  MESSAGE text null,
  TEXT_MESSAGE text null,
  URL varchar(250) null,
  MODULE_ID varchar(50) null,
  CALLBACK_FUNC varchar(250) null,
  EXTERNAL_ID varchar(250) null,
  PARAMS text,
  TMP_ID int(11) default NULL,
  primary key (ID),
  index IX_SONET_LOG_1(ENTITY_TYPE, ENTITY_ID, EVENT_ID),
  index IX_SONET_LOG_2(USER_ID, LOG_DATE, EVENT_ID)  
);

create table b_sonet_log_events
(
  ID int not null auto_increment,
  USER_ID int not null,
  ENTITY_TYPE char(1) not null default 'G',
  ENTITY_ID int not null,
  ENTITY_CB char(1) NOT NULL default 'N',
  ENTITY_MY char(1) NOT NULL default 'N',
  EVENT_ID varchar(50) not null,
  SITE_ID char(2) null,
  MAIL_EVENT char(1) not null default 'N',
  TRANSPORT char(1) NOT NULL default 'N',
  VISIBLE char(1) NOT NULL default 'Y',
  primary key (ID),
  index IX_SONET_LOG_EVENTS_1(USER_ID),
  index IX_SONET_LOG_EVENTS_2(ENTITY_TYPE, ENTITY_ID, EVENT_ID),
  unique IX_SONET_LOG_EVENTS_3(USER_ID, ENTITY_TYPE, ENTITY_ID, ENTITY_CB, ENTITY_MY, EVENT_ID)
);

CREATE TABLE b_sonet_event_user_view 
(
	ENTITY_TYPE char(1) NOT NULL default 'G',
	ENTITY_ID int(11) NOT NULL,
	EVENT_ID varchar(50) NOT NULL,
	USER_ID int(11) NOT NULL default 0,
	USER_IM_ID int(11) default NULL,
	USER_ANONYMOUS char(1) NOT NULL default 'N',
	PRIMARY KEY (ENTITY_TYPE,ENTITY_ID,EVENT_ID,USER_ID,USER_IM_ID)
);