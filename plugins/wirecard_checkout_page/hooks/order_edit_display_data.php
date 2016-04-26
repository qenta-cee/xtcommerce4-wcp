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

if ($this->order_data['order_data']['payment_code'] == 'wirecard_checkout_page') {
    /** @global ADODB_mysql $db */
    global $db;

    $rs = $db->GetAssoc("SELECT * FROM wirecard_checkout_page_transaction WHERE orderid=?", array((int)$this->order_data['order_data']['orders_id']));
    if (count($rs))
    {
        $wcp_data = array_pop($rs);
        if (strlen($wcp_data['RESPONSEDATA']))
        {
            $blacklist = array('last_order_id');
            $info = json_decode($wcp_data['RESPONSEDATA']);
            foreach ($info as $k => $v)
            {
                if (in_array($k, $blacklist))
                    continue;
                $tpl_data['order_data']['order_info_options'][] = array('text' => $k, 'value' => $v);
            }

        }
    }

//    if($rs->RecordCount()>0) {
//        print "xxx";
//        foreach ($rs as $v)
//            print_r($v);
//    }

}

