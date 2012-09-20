CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_customer` int(10) unsigned NOT NULL,
  `id_cart` int(10) unsigned NOT NULL,

  `id_address_delivery` int(10) unsigned NOT NULL,


  `payment` varchar(255) NOT NULL,
  `conversion_rate` decimal(13,6) NOT NULL DEFAULT '1.000000',
  `module` varchar(255) DEFAULT NULL,

  `shipping_number` varchar(32) DEFAULT NULL,
  `total_discounts` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_paid` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_paid_real` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_products` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_products_wt` decimal(17,2) NOT NULL DEFAULT '0.00',
  `total_shipping` decimal(17,2) NOT NULL DEFAULT '0.00',
  `carrier_tax_rate` decimal(10,3) NOT NULL DEFAULT '0.000',

  `invoice_number` int(10) unsigned NOT NULL DEFAULT '0',
  `delivery_number` int(10) unsigned NOT NULL DEFAULT '0',
  `invoice_date` datetime NOT NULL,
  `delivery_date` datetime NOT NULL,
  `valid` int(1) unsigned NOT NULL DEFAULT '0',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_customer` (`id_customer`),
  KEY `id_cart` (`id_cart`),
  KEY `invoice_number` (`invoice_number`),


  KEY `id_address_delivery` (`id_address_delivery`)

);


CREATE TABLE `ps_order_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `product_attribute_id` int(10) unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `product_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `reduction_percent` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reduction_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `product_ean13` varchar(13) DEFAULT NULL,
  `product_reference` varchar(32) DEFAULT NULL,
  `product_weight` float NOT NULL,
  `product_periodical`
  `product_datestart` DATE DEFAULT NULL,
  `product_dateend` DATE DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `order_detail_order` (`id_order`),
  KEY `product_id` (`product_id`),
  KEY `product_attribute_id` (`product_attribute_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=488


