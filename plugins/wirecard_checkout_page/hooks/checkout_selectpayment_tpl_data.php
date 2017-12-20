<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern
 * Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard
 * CEE range of products and services.
 *
 * They have been tested and approved for full functionality in the standard
 * configuration
 * (status on delivery) of the corresponding shop system. They are under
 * General Public License Version 2 (GPLv2) and can be used, developed and
 * passed on to third parties under the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability
 * for any errors occurring when used in an enhanced, customized shop system
 * configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and
 * requires a comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee
 * their full functionality neither does Wirecard CEE assume liability for any
 * disadvantages related to the use of the plugins. Additionally, Wirecard CEE
 * does not guarantee the full functionality for customized shop systems or
 * installed plugins of other vendors of plugins within the same shop system.
 *
 * Customers are responsible for testing the plugin's functionality before
 * starting productive operation.
 *
 * By installing the plugin into the shop system the customer agrees to these
 * terms of use. Please do not use the plugin if you do not agree to these
 * terms of use!
 */

if ($tpl_data['payment_code'] == 'wirecard_checkout_page') {
    $tpl_data['plugin'] = new wirecard_checkout_page();

    $consent_message = preg_replace_callback("/_(.*)_/", function ($matches) {
        if (strlen(WIRECARD_CHECKOUT_PAGE_PAYOLUTION_MID)) {
            return "<a style='color:white;mix-blend-mode:difference;' href='https://payment.payolution.com/payolution-payment/infoport/dataprivacyconsent?mId=" . base64_encode(WIRECARD_CHECKOUT_PAGE_PAYOLUTION_MID) . "' target='_blank'>$matches[1]</a>";
        } else {
            return $matches[1];
        }
    }, TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_PAYOLUTON_TERMS);

    $wcp_payments = array(
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_SELECT',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_SELECT,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_SELECT,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_SELECT,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_SELECT,
            'img' => 'checkoutpage'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_CCARD',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_CCARD,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_CCARD,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_CCARD,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_CCARD,
            'img' => 'cc'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_MASTERPASS',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_MASTERPASS,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_MASTERPASS,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_MASTERPASS,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_MASTERPASS,
            'img' => 'masterpass'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_MAESTRO',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_MAESTRO,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_MAESTRO,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_MAESTRO,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_MAESTRO,
            'img' => 'cc'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_PAYBOX',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_PAYBOX,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_PAYBOX,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_PAYBOX,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_PAYBOX,
            'img' => 'paybox'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_PAYSAFECARD',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_PAYSAFECARD,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_PAYSAFECARD,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_PAYSAFECARD,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_PAYSAFECARD,
            'img' => 'paysafecard'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_EPS_ONLINETRANSACTION',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_EPS_ONLINETRANSACTION,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_EPS_ONLINETRANSACTION,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_EPS_ONLINETRANSACTION,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_EPS_ONLINETRANSACTION,
            'img' => 'eps',
            'financialInstitutions' => WirecardCEE_QPay_PaymentType::getFinancialInstitutions('EPS'),
            'extra_fields' => true
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_DIRECT_DEBIT',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_DIRECT_DEBIT,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_DIRECT_DEBIT,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_DIRECT_DEBIT,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_DIRECT_DEBIT,
            'img' => 'sepadd'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_IDEAL',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_IDEAL,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_IDEAL,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_IDEAL,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_IDEAL,
            'img' => 'ideal',
            'financialInstitutions' => WirecardCEE_QPay_PaymentType::getFinancialInstitutions('IDL'),
            'extra_fields' => true
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_GIROPAY',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_GIROPAY,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_GIROPAY,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_GIROPAY,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_GIROPAY,
            'img' => 'giropay'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_PAYPAL',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_PAYPAL,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_PAYPAL,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_PAYPAL,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_PAYPAL,
            'img' => 'paypal'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_SOFORTUEBERWEISUNG',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_SOFORTUEBERWEISUNG,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_SOFORTUEBERWEISUNG,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_SOFORTUEBERWEISUNG,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_SOFORTUEBERWEISUNG,
            'img' => 'sofortbanking'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_BMC',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_BMC,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_BMC,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_BMC,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_BMC,
            'img' => 'bmc'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_INVOICE',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_INVOICE,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_INVOICE,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_INVOICE,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_INVOICE,
            'img' => 'invoice',
            'extra_fields' => true,
            'payolution_terms' => WIRECARD_CHECKOUT_PAGE_PAYOLUTION_TERMS === "true" && WIRECARD_CHECKOUT_PAGE_INVOICE_PROVIDER == 'payolution',
            'consent_message' => $consent_message,
            'birthdate' => $_SESSION['customer']->customer_payment_address['customers_age'] < 18
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_INSTALLMENT',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_INSTALLMENT,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_INSTALLMENT,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_INSTALLMENT,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_INSTALLMENT,
            'img' => 'installment',
            'extra_fields' => true,
            'payolution_terms' => WIRECARD_CHECKOUT_PAGE_PAYOLUTION_TERMS === "true" && WIRECARD_CHECKOUT_PAGE_INSTALLMENT_PROVIDER == 'payolution',
            'consent_message' => $consent_message,
            'birthdate' => $_SESSION['customer']->customer_payment_address['customers_age'] < 18
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_P24',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_P24,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_P24,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_P24,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_P24,
            'img' => 'p24'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_MONETA',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_MONETA,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_MONETA,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_MONETA,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_MONETA,
            'img' => 'moneta'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_POLI',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_POLI,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_POLI,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_POLI,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_POLI,
            'img' => 'poli'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_EKONTO',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_EKONTO,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_EKONTO,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_EKONTO,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_EKONTO,
            'img' => 'ekonto'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_SKRILLWALLET',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_SKRILLWALLET,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_SKRILLWALLET,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_SKRILLWALLET,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_SKRILLWALLET,
            'img' => 'skrillwallet'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_TRUSTLY',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_TRUSTLY,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_TRUSTLY,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_TRUSTLY,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_TRUSTLY,
            'img' => 'trustly'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_EPAY_BG',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_EPAY_BG,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_EPAY_BG,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_EPAY_BG,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_EPAY_BG,
            'img' => 'epaybg'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_TATRAPAY',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_TATRAPAY,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_TATRAPAY,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_TATRAPAY,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_TATRAPAY,
            'img' => 'tatrapay'
        ),
        array(
            'name' => 'WIRECARD_CHECKOUT_PAGE_VOUCHER',
            'active' => WIRECARD_CHECKOUT_PAGE_ACTIVATE_VOUCHER,
            'order' => WIRECARD_CHECKOUT_PAGE_ORDER_VOUCHER,
            'group' => WIRECARD_CHECKOUT_PAGE_PERMISSION_VOUCHER,
            'text' => TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_VOUCHER,
            'img' => 'voucher'
        ),
    );

    $customer = $_SESSION['customer'];
    $customer_info = $customer->customer_info;
    //get customergroup of current customer
    $customer_status = $customer_info['customers_status'];

    //sort paymenttypes for view
    foreach ($wcp_payments as $key => $row) {
        if ($row['group'] == 0 || $row['group'] == $customer_status) {
            $order[$key] = $row['order'];
            $name[$key] = $row['text'];
        } else {
            //unset paymenttype for customer without permissions
            unset($wcp_payments[$key]);
        }
    }
    array_multisort($order, SORT_ASC, $name, SORT_ASC, $wcp_payments);
    $tpl_data['wirecard_payment_types'] = $wcp_payments;

    $config = $tpl_data['plugin']->getConfigArray();
    $customer_id = $config['CUSTOMER_ID'];

    if (isset($_SESSION['wcp-consumerDeviceId'])) {
        $consumerDeviceId = $_SESSION['wcp-consumerDeviceId'];
    } else {
        $timestamp = microtime();
        $consumerDeviceId = md5($customer_id . "_" . $timestamp);
        $_SESSION['wcp-consumerDeviceId'] = $consumerDeviceId;
    }
    $ratepay = '<script language="JavaScript">var di = {t:"' . $consumerDeviceId . '",v:"WDWL",l:"Checkout"};</script>';
    $ratepay .= '<script type="text/javascript" src="//d.ratepay.com/' . $consumerDeviceId . '/di.js"></script>';
    $ratepay .= '<noscript><link rel="stylesheet" type="text/css" href="//d.ratepay.com/di.css?t=' . $consumerDeviceId . '&v=WDWL&l=Checkout"></noscript>';
    $ratepay .= '<object type="application/x-shockwave-flash" data="//d.ratepay.com/WDWL/c.swf" width="0" height="0"><param name="movie" value="//d.ratepay.com/WDWL/c.swf" /><param name="flashvars" value="t=' . $consumerDeviceId . '&v=WDWL"/><param name="AllowScriptAccess" value="always"/></object>';

    $tpl_data['ratepay'] = $ratepay;
}
