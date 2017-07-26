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

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$missing_fields = array();
$selected_payment = $_POST['selected_payment'];
if (strpos($selected_payment, ":") > -1) {
    $selected_payment = explode(":", $selected_payment)[1];

    if($selected_payment == 'WIRECARD_CHECKOUT_PAGE_IDEAL') {
        $_SESSION['financialInstitution'] = $_POST["wcp_ideal_financialInstitution"];
    }
    if($selected_payment == 'WIRECARD_CHECKOUT_PAGE_EPS_ONLINETRANSACTION') {
        $_SESSION['financialInstitution'] = $_POST["wcp_eps_financialInstitution"];
    }

    foreach (get_required_fields($selected_payment) as $field) {
        if ($field == 'wcp_payolution_terms_' . $selected_payment && $_POST[$field] != 'on') {
            _failureRedirect(TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_PAYOLUTON_TERMS_ARE_REQUIRED);
            die();
        }
        if ($field == 'wcp_dob_day_' . $selected_payment) {
            $dob = new DateTime();
            $dob->setDate($_POST['wcp_dob_year_' . $selected_payment],
                $_POST['wcp_dob_month_' . $selected_payment],
                $_POST['wcp_dob_day_' . $selected_payment]);
            if ($dob->diff(new DateTime())->y < 18) {
                _failureRedirect(TEXT_PAYMENT_WIRECARD_CHECKOUT_PAGE_INVOICE_INSTALLMENT_TOO_YOUNG);
                die();
            }
        }
    }
}

function _failureRedirect($message)
{
    global $xtLink;
    $failureUrl = $xtLink->_link(
        array(
            'page' => 'wirecard_checkout_page_checkout',
            'params' => 'message=' . $message,
            'conn' => 'SSL'
        )
    );
    $xtLink->_redirect($failureUrl);
}

function get_required_fields($paymenttype)
{
    $array = array(
        'WIRECARD_CHECKOUT_PAGE_INSTALLMENT' => array(),
        'WIRECARD_CHECKOUT_PAGE_INVOICE' => array()
    );


    if ($_SESSION['customer']->customer_payment_address['customers_age'] < 18) {
        $array['WIRECARD_CHECKOUT_PAGE_INSTALLMENT'] = array_merge($array['WIRECARD_CHECKOUT_PAGE_INSTALLMENT'],
            array(
                'wcp_dob_day_' . $paymenttype,
                'wcp_dob_month_' . $paymenttype,
                'wcp_dob_year_' . $paymenttype
            ));
        $array['WIRECARD_CHECKOUT_PAGE_INVOICE'] = array_merge($array['WIRECARD_CHECKOUT_PAGE_INVOICE'],
            array(
                'wcp_dob_day_' . $paymenttype,
                'wcp_dob_month_' . $paymenttype,
                'wcp_dob_year_' . $paymenttype
            ));
    }

    if (WIRECARD_CHECKOUT_PAGE_PAYOLUTION_TERMS === "true" && WIRECARD_CHECKOUT_PAGE_INVOICE_PROVIDER == 'payolution') {
        $array['WIRECARD_CHECKOUT_PAGE_INVOICE'] = array_merge($array['WIRECARD_CHECKOUT_PAGE_INVOICE'],
            array('wcp_payolution_terms_' . $paymenttype));
    }

    if (WIRECARD_CHECKOUT_PAGE_PAYOLUTION_TERMS === "true" && WIRECARD_CHECKOUT_PAGE_INSTALLMENT_PROVIDER == 'payolution') {
        $array['WIRECARD_CHECKOUT_PAGE_INSTALLMENT'] = array_merge($array['WIRECARD_CHECKOUT_PAGE_INSTALLMENT'],
            array('wcp_payolution_terms_' . $paymenttype));
    }

    if (!isset($array[$paymenttype])) {
        $array[$paymenttype] = array();
    }
    return $array[$paymenttype];
}