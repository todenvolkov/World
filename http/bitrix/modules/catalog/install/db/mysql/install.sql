create table b_catalog_iblock
(
	IBLOCK_ID int not null,
	YANDEX_EXPORT char(1) not null default 'N',
	SUBSCRIPTION char(1) not null default 'N',
	VAT_ID int(11) NULL default '0',
	OFFERS_IBLOCK_ID int not null default '0',
	OFFERS char(1) not null default 'N',
	primary key (IBLOCK_ID)
);

create table b_catalog_price
(
	ID int not null auto_increment,
	PRODUCT_ID int not null,
	EXTRA_ID int null,
	CATALOG_GROUP_ID int not null,
	PRICE decimal(18,2) not null,
	CURRENCY char(3) not null,
	TIMESTAMP_X timestamp not null,
	QUANTITY_FROM int null,
	QUANTITY_TO int null,
	TMP_ID varchar(40) null,
	primary key (ID),
	index IXS_CAT_PRICE_PID(PRODUCT_ID, CATALOG_GROUP_ID),
	index IXS_CAT_PRICE_GID(CATALOG_GROUP_ID)
);

create table b_catalog_product
(
	ID int not null,
	QUANTITY double not null,
	QUANTITY_TRACE char(1) not null default 'N',
	WEIGHT double not null default '0',
	TIMESTAMP_X timestamp not null,
	PRICE_TYPE char(1) not null default 'S',
	RECUR_SCHEME_LENGTH int null,
	RECUR_SCHEME_TYPE char(1) not null default 'M',
	TRIAL_PRICE_ID int null,
	WITHOUT_ORDER char(1) not null default 'N',
	SELECT_BEST_PRICE char(1) not null default 'Y',
	VAT_ID int(11) NULL default '0',
	VAT_INCLUDED char(1) NULL default 'Y',
	primary key (ID)
);

create table b_catalog_product2group
(
  ID int not null auto_increment,
  PRODUCT_ID int not null,
  GROUP_ID int not null,
  ACCESS_LENGTH int not null,
  ACCESS_LENGTH_TYPE char(1) not null default 'D',
  primary key (ID),
  unique IX_C_P2G_PROD_GROUP(PRODUCT_ID, GROUP_ID)
);

create table b_catalog_extra
(
	ID int not null auto_increment,
	NAME varchar(50) not null,
	PERCENTAGE decimal(18,2) not null,
	primary key (ID)
);

create table b_catalog_group
(
	ID int not null auto_increment,
	NAME varchar(100) not null,
	BASE char(1) not null default 'N',
	SORT int not null default '100',
	primary key (ID)
);

create table b_catalog_group_lang
(
	ID int not null auto_increment,
	CATALOG_GROUP_ID int not null,
	LID char(3) not null,
	NAME varchar(100) null,
	primary key (ID),
	unique IX_CATALOG_GROUP_ID(CATALOG_GROUP_ID, LID)
);

create table b_catalog_group2group
(
	ID int not null auto_increment,
	CATALOG_GROUP_ID int not null,
	GROUP_ID int not null,
	BUY char(1) not null default 'Y',
	primary key (ID),
	unique IX_CATG2G_UNI(CATALOG_GROUP_ID, GROUP_ID, BUY)
);

create table b_catalog_load
(
	NAME varchar(250) not null, 
	VALUE text not null, 
	`TYPE` char(1) not null default 'I', 
	LAST_USED char(1) not null default 'N',
	primary key (NAME, `TYPE`)
);

create table b_catalog_export
(
	ID int not null auto_increment,
	FILE_NAME varchar(100) not null,
	NAME varchar(250) not null,
	DEFAULT_PROFILE char(1) not null default 'N',
	IN_MENU char(1) not null default 'N',
	IN_AGENT char(1) not null default 'N',
	IN_CRON char(1) not null default 'N',
	SETUP_VARS text null,
	LAST_USE datetime null,
	IS_EXPORT char(1) not null default 'Y',
	primary key (ID),
	index BCAT_EX_FILE_NAME(FILE_NAME),
	index IX_CAT_IS_EXPORT(IS_EXPORT)
);

create table b_catalog_discount
(
  ID int not null auto_increment,
  SITE_ID char(2) not null,
  ACTIVE char(1) not null default 'Y',
  ACTIVE_FROM datetime null,
  ACTIVE_TO datetime null,
  RENEWAL char(1) not null default 'N',
  NAME varchar(255) null,
  MAX_USES int not null default '0',
  COUNT_USES int not null default '0',
  COUPON varchar(20) null,
  SORT int not null default '100',
  MAX_DISCOUNT decimal(18,4) null,
  VALUE_TYPE char(1) not null default 'P',
  VALUE decimal(18,4) not null default '0.0',
  CURRENCY char(3) not null,
  MIN_ORDER_SUM decimal(18,4) null default '0.0',
  TIMESTAMP_X timestamp not null,
  NOTES varchar(255) null,
  primary key (ID),
  index IX_C_D_COUPON(COUPON),
  index IX_C_D_ACT(ACTIVE, ACTIVE_FROM, ACTIVE_TO),
  index IX_C_D_ACT_B(SITE_ID, RENEWAL, ACTIVE, ACTIVE_FROM, ACTIVE_TO)
);

create table b_catalog_discount2product
(
  ID int not null auto_increment,
  DISCOUNT_ID int not null,
  PRODUCT_ID int not null,
  primary key (ID),
  unique IX_C_D2P_PRODIS(PRODUCT_ID, DISCOUNT_ID),
  unique IX_C_D2P_PRODIS_B(DISCOUNT_ID, PRODUCT_ID)
);

create table b_catalog_discount2group
(
  ID int not null auto_increment,
  DISCOUNT_ID int not null,
  GROUP_ID int not null,
  primary key (ID),
  unique IX_C_D2G_GRDIS(GROUP_ID, DISCOUNT_ID),
  unique IX_C_D2G_GRDIS_B(DISCOUNT_ID, GROUP_ID)
);

create table b_catalog_discount2cat
(
  ID int not null auto_increment,
  DISCOUNT_ID int not null,
  CATALOG_GROUP_ID int not null,
  primary key (ID),
  unique IX_C_D2C_CATDIS(CATALOG_GROUP_ID, DISCOUNT_ID),
  unique IX_C_D2C_CATDIS_B(DISCOUNT_ID, CATALOG_GROUP_ID)
);

create table b_catalog_discount2section
(
  ID int not null auto_increment,
  DISCOUNT_ID int not null,
  SECTION_ID int not null,
  primary key (ID),
  unique IX_C_D2S_SECDIS(SECTION_ID, DISCOUNT_ID),
  unique IX_C_D2S_SECDIS_B(DISCOUNT_ID, SECTION_ID)
);

create table b_catalog_discount_coupon
(
	ID int not null auto_increment,
	DISCOUNT_ID int not null,
	ACTIVE char(1) not null default 'Y',
	COUPON varchar(32) not null,
	DATE_APPLY datetime null,
	ONE_TIME char(1) not null default 'Y',
	primary key (ID),
	unique ix_cat_dc_index1(DISCOUNT_ID, COUPON),
	index ix_cat_dc_index2(COUPON, ACTIVE)
);

CREATE TABLE b_catalog_vat (
  ID int(11) NOT NULL auto_increment,
  TIMESTAMP_X timestamp NOT NULL,
  ACTIVE char(1) NOT NULL default 'Y',
  C_SORT int(18) NOT NULL default 100,
  NAME varchar(50) NOT NULL default '',
  RATE decimal(18,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID),
  KEY IX_CAT_VAT_ACTIVE (ACTIVE)
);