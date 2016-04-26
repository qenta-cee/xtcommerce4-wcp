<?php
/**
 * Shop System Plugins - Terms of use
 *
 * This terms of use regulates warranty and liability between Wirecard
 * Central Eastern Europe (subsequently referred to as WDCEE) and it's
 * contractual partners (subsequently referred to as customer or customers)
 * which are related to the use of plugins provided by WDCEE.
 *
 * The Plugin is provided by WDCEE free of charge for it's customers and
 * must be used for the purpose of WDCEE's payment platform integration
 * only. It explicitly is not part of the general contract between WDCEE
 * and it's customer. The plugin has successfully been tested under
 * specific circumstances which are defined as the shopsystem's standard
 * configuration (vendor's delivery state). The Customer is responsible for
 * testing the plugin's functionality before putting it into production
 * enviroment.
 * The customer uses the plugin at own risk. WDCEE does not guarantee it's
 * full functionality neither does WDCEE assume liability for any
 * disadvantage related to the use of this plugin. By installing the plugin
 * into the shopsystem the customer agrees to the terms of use. Please do
 * not use this plugin if you do not agree to the terms of use!
 */
defined('_VALID_CALL') or die('Direct Access is not allowed.');

/** @global ADODB_mysql $db */
global $db;

$db->Execute("
CREATE TABLE IF NOT EXISTS `wirecard_checkout_page_transaction` (
  `TRID` varchar(255) NOT NULL default '',
  `DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  `PAYSYS` varchar(50) NOT NULL default '',
  `BRAND` varchar(100) NOT NULL default '',
  `ORDERNUMBER` int(11) unsigned NOT NULL default '0',
  `ORDERDESCRIPTION` varchar(255) NOT NULL default '',
  `STATE` varchar(20) NOT NULL default '',
  `MESSAGE` varchar(255) NOT NULL default '',
  `ORDERID` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`TRID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1  AUTO_INCREMENT=1;
");

$db->IgnoreErrors();
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 24, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 25, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 26, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 27, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 28, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 29, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 30, '', 0, 10000.00, 0, 1);");
$db->Execute("INSERT INTO ".TABLE_PAYMENT_COST." (`payment_id`, `payment_geo_zone`, `payment_country_code`, `payment_type_value_from`, `payment_type_value_to`, `payment_price`,`payment_allowed`) VALUES(".$payment_id.", 31, '', 0, 10000.00, 0, 1);");
$db->Execute("ALTER TABLE `wirecard_checkout_page_transaction` ADD `RESPONSEDATA` TEXT NULL DEFAULT NULL");


