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

defined('_VALID_CALL') or die ('Direct Access is not allowed.');

class wirecard_checkout_page
{
    var $data = array();
    var $post_form = false;
    var $iframe = false;
    var $external = true;
    var $IFRAME_URL = '';
    var $_transaction_table = 'wirecard_checkout_page_transaction';
    var $_transaction_id = '';
    /**
     * WD Variablen
     */
    var $demoMode = false;
    var $initHost = 'checkout.wirecard.com';
    var $initPath = '/page/init-server.php';
    var $initPort = '443';
    var $initParams = array();

    var $version = '1.4.4';

    var $paymentTypes = array(
        'WIRECARD_CHECKOUT_PAGE_SELECT' => 'SELECT',
        'WIRECARD_CHECKOUT_PAGE_CCARD' => 'CCARD',
        'WIRECARD_CHECKOUT_PAGE_MAESTRO' => 'MAESTRO',
        'WIRECARD_CHECKOUT_PAGE_PAYBOX' => 'PBX',
        'WIRECARD_CHECKOUT_PAGE_PAYSAFECARD' => 'PSC',
        'WIRECARD_CHECKOUT_PAGE_EPS_ONLINETRANSACTION' => 'EPS',
        'WIRECARD_CHECKOUT_PAGE_DIRECT_DEBIT' => 'SEPA-DD',
        'WIRECARD_CHECKOUT_PAGE_QUICK' => 'QUICK',
        'WIRECARD_CHECKOUT_PAGE_IDEAL' => 'IDL',
        'WIRECARD_CHECKOUT_PAGE_GIROPAY' => 'GIROPAY',
        'WIRECARD_CHECKOUT_PAGE_PAYPAL' => 'PAYPAL',
        'WIRECARD_CHECKOUT_PAGE_SOFORTUEBERWEISUNG' => 'SOFORTUEBERWEISUNG',
        'WIRECARD_CHECKOUT_PAGE_BMC' => 'BANCONTACT_MISTERCASH',
        'WIRECARD_CHECKOUT_PAGE_INVOICE' => 'INVOICE',
        'WIRECARD_CHECKOUT_PAGE_INSTALLMENT' => 'INSTALLMENT',
        'WIRECARD_CHECKOUT_PAGE_P24' => 'PRZELEWY24',
        'WIRECARD_CHECKOUT_PAGE_MONETA' => 'MONETA',
        'WIRECARD_CHECKOUT_PAGE_POLI' => 'POLI',
        'WIRECARD_CHECKOUT_PAGE_EKONTO' => 'EKONTO',
        'WIRECARD_CHECKOUT_PAGE_TRUSTLY' => 'TRUSTLY',
        'WIRECARD_CHECKOUT_PAGE_MPASS' => 'MPASS',
        'WIRECARD_CHECKOUT_PAGE_SKRILLDIRECT' => 'SKRILLDIRECT',
        'WIRECARD_CHECKOUT_PAGE_SKRILLWALLET' => 'SKRILLWALLET',
        'WIRECARD_CHECKOUT_PAGE_TATRAPAY' => 'TATRAPAY',
        'WIRECARD_CHECKOUT_PAGE_VOUCHER' => 'VOUCHER',
        'WIRECARD_CHECKOUT_PAGE_EPAY_BG' => 'EPAY_BG',
    );

    const INVOICE_INSTALLMENT_MIN_AGE = 18;

    /**
     * php style constructor
     *
     * @access public
     */
    function wirecard_checkout_page()
    {
        global $xtLink;
        if (WIRECARD_CHECKOUT_PAGE_USE_IFRAME == 'true') {
            $this->external = false;
            $this->iframe = true;
            $this->IFRAME_URL = $xtLink->_link(array('page' => 'checkout', 'paction' => 'pay_frame', 'conn' => 'SSL'));
            $this->initParams = Array('windowName' => 'veyton_paymentframe');
        }
    }

    /**
     * XTC-Funktion, um das Paymentrequest an einen externen PSP zu senden
     *
     * Die Funktion spiegelt in etwa die alte "payment_action" wieder. An dieser Stelle
     * wird die Anfrage gestellt und je nach der Ergebnis der Sprung auf die entsprechende
     * Seite vorbereitet (idR IFrame oder Fehlerseite)
     *
     * @param $order_data array mit den wichtigsten Infos zur Bestellung
     * @return URL, zu der als nächstes gesprungen werden soll
     * @access public
     */
    function pspRedirect($order_data = null)
    {
        global $xtLink, $filter, $order, $db;
        if (!$order_data) {
            $order_data = $order->order_data;
        }
        if (($res = $this->_checkOrderData($order_data)) !== true) {
            return $xtLink->_link($res);
        }

        $orders_id = ( int )$order_data ['orders_id'];
        if (!is_int($orders_id)) {
            return $xtLink->_link(
                array(
                    'page' => 'wirecard_checkout_page_checkout',
                    'paction' => 'failure',
                    'conn' => 'SSL',
                    'params' => 'code_1=210'
                )
            );
        }
        # Special, da xt der Meinung ist alle alten GET Parameter mit anzuh�ngen
        $_GET = array();

        # Anfrage durchführen
        $strPaymentType = $this->paymentTypes[$_SESSION ['selected_payment_sub']];
        $paymentType1 = (isset ($strPaymentType) && !empty ($strPaymentType)) ? $strPaymentType : "SELECT";

        # Daten setzen
        $this->_setSystemData($order_data);
        if (WIRECARD_CHECKOUT_PAGE_SEND_CUSTOMER_DATA == 'true' || $paymentType1 == 'INSTALLMENT' || $paymentType1 == 'INVOICE') {
            $this->_setCustomerData($order_data);
        }

        $requestFingerprintOrder = 'secret';
        $requestFingerprintSeed = WIRECARD_CHECKOUT_PAGE_PROJECT_SECRET;
        foreach ($this->initParams AS $paramName => $paramValue) {
            $requestFingerprintOrder .= ',' . $paramName;
            $requestFingerprintSeed .= $paramValue;
        }
        $requestFingerprintOrder .= ',requestFingerprintOrder';
        $requestFingerprintSeed .= $requestFingerprintOrder;

        $requestFingerprint = md5($requestFingerprintSeed);
        $this->initParams['requestFingerprintOrder'] = $requestFingerprintOrder;
        $this->initParams['requestFingerprint'] = $requestFingerprint;
        $result = @$db->Execute(
            "INSERT INTO " . $this->_transaction_table . " (TRID, PAYSYS,    STATE, DATE) VALUES ('" . $this->_transaction_id . "', '" . $paymentType1 . "','REDIRECTED', NOW())"
        );
        $paymentUrl = $this->_initiateWirecardCheckoutPageSession();
        return $paymentUrl;
    }

    /**
     * XTC-Funktion, um auf eine spezielle Success-Seite zu springen
     *
     * Da der Aufruf in der checkout-Klasse "payment_process" falsch ausgewertet wird (!= anstatt !==)
     * macht die Funktion zur Zeit keinen Sinn, da auch eine URL "true" wäre und nie aufgerufen werden
     * würde.
     *
     * @return URL oder true
     * @access public
     */
    function pspSuccess()
    {
        return true;
    }

    /**
     * Führt Prüfungen vor Absenden des Request durch
     *
     * @return true im Erfolgsfall, ansonsten Array mit Daten für Sprung zur Fehlerseite
     * @access private
     */
    function _checkOrderData($order_data)
    {
        # Prüfen, ob Paymenttype gsetezt
        if (!array_key_exists($_SESSION ['selected_payment_sub'], $this->paymentTypes)) {
            return array(
                'page' => 'wirecard_checkout_page_checkout',
                'paction' => 'failure',
                'conn' => 'SSL',
                'params' => 'code_1=209'
            );
        }
        return true;
    }

    function isInstallmentAllowed()
    {
        global $currency;

        if (!array_key_exists('customer', $_SESSION)) {
            return false;
        }

        if (!array_key_exists('cart', $_SESSION)) {
            return false;
        }

        if ($currency->code != 'EUR') {
            return false;
        }

        $customer = $_SESSION['customer'];
        $cart = $_SESSION['cart'];

        $paymentAddress = $customer->customer_payment_address;
        $shippingAddress = $customer->customer_shipping_address;

        $total = $cart->content_total['plain'];

        if ($paymentAddress['address_book_id'] != $shippingAddress['address_book_id']) {
            $fields = array(
                'customers_country',
                'customers_company',
                'customers_firstname',
                'customers_lastname',
                'customers_street_address',
                'customers_suburb',
                'customers_postcode',
                'customers_city',
                'customers_federal_state_code'
            );
            foreach ($fields as $f) {
                if ($paymentAddress[$f] != $shippingAddress[$f]) {
                    return false;
                }
            }

        }

        if ($paymentAddress['customers_age'] < self::INVOICE_INSTALLMENT_MIN_AGE) {
            return false;
        }

        if (WIRECARD_CHECKOUT_PAGE_INSTALLMENT_MIN_AMOUNT == 0 || WIRECARD_CHECKOUT_PAGE_INSTALLMENT_MAX_AMOUNT == 0) {
            return false;
        }

        if (WIRECARD_CHECKOUT_PAGE_INSTALLMENT_MIN_AMOUNT && WIRECARD_CHECKOUT_PAGE_INSTALLMENT_MIN_AMOUNT > $total) {
            return false;
        }

        if (WIRECARD_CHECKOUT_PAGE_INSTALLMENT_MAX_AMOUNT && WIRECARD_CHECKOUT_PAGE_INSTALLMENT_MAX_AMOUNT < $total) {
            return false;
        }

        return true;
    }

    function isInvoiceAllowed()
    {
        global $currency;

        if (!array_key_exists('customer', $_SESSION)) {
            return false;
        }

        if (!array_key_exists('cart', $_SESSION)) {
            return false;
        }

        if ($currency->code != 'EUR') {
            return false;
        }

        $customer = $_SESSION['customer'];
        $cart = $_SESSION['cart'];

        $paymentAddress = $customer->customer_payment_address;
        $shippingAddress = $customer->customer_shipping_address;

        $total = $cart->content_total['plain'];

        if ($paymentAddress['address_book_id'] != $shippingAddress['address_book_id']) {
            $fields = array(
                'customers_country',
                'customers_company',
                'customers_firstname',
                'customers_lastname',
                'customers_street_address',
                'customers_suburb',
                'customers_postcode',
                'customers_city',
                'customers_federal_state_code'
            );
            foreach ($fields as $f) {
                if ($paymentAddress[$f] != $shippingAddress[$f]) {
                    return false;
                }
            }

        }

        if ($paymentAddress['customers_age'] < self::INVOICE_INSTALLMENT_MIN_AGE) {
            return false;
        }

        if (WIRECARD_CHECKOUT_PAGE_INVOICE_MIN_AMOUNT == 0 || WIRECARD_CHECKOUT_PAGE_INVOICE_MAX_AMOUNT == 0) {
            return false;
        }

        if (WIRECARD_CHECKOUT_PAGE_INVOICE_MIN_AMOUNT && WIRECARD_CHECKOUT_PAGE_INVOICE_MIN_AMOUNT > $total) {
            return false;
        }

        if (WIRECARD_CHECKOUT_PAGE_INVOICE_MAX_AMOUNT && WIRECARD_CHECKOUT_PAGE_INVOICE_MAX_AMOUNT < $total) {
            return false;
        }

        return true;
    }

    /**
     * Setzt die Systemdaten für den Aufruf der Schnittstelle
     *
     * @param $order_data array mit den wichtigsten Infos zur Bestellung
     * @access private
     */

    function _setSystemData()
    {
        global $order, $language;

        $pluginVersion = base64_encode('Veyton; 4.x; ; xtCommerce4; ' . $this->version);
        $order_data = $order->order_data;
        $this->_transaction_id = $this->generate_trid();

        $shopId = trim(WIRECARD_CHECKOUT_PAGE_SHOP_ID);
        if ($shopId != '-')
            $request['shopid'] = WIRECARD_CHECKOUT_PAGE_SHOP_ID;
        $request['customerId'] = WIRECARD_CHECKOUT_PAGE_PROJECT_ID;

        if (intval(WIRECARD_CHECKOUT_PAGE_MAX_RETRIES) >= 0) {
            $request['maxRetries'] = intval(WIRECARD_CHECKOUT_PAGE_MAX_RETRIES);
        }
        $request['amount'] = $order->order_total ['total'] ['plain'];
        $request['currency'] = $order_data ['currency_code'];
        $request['language'] = $order_data ['language_code'];

        $strPaymentType = $this->paymentTypes[$_SESSION ['selected_payment_sub']];

        $request['trid'] = $this->_transaction_id;
        $request['successURL'] = $this->_link(array('page' => 'wirecard_checkout_page_checkout', 'conn' => 'SSL'));
        $request['failureURL'] = $this->_link(array('page' => 'wirecard_checkout_page_checkout', 'conn' => 'SSL'));
        $request['cancelURL'] = $this->_link(array('page' => 'wirecard_checkout_page_checkout', 'conn' => 'SSL'));
        $request['pendingURL'] = $this->_link(array('page' => 'wirecard_checkout_page_checkout', 'conn' => 'SSL'));
        $request['confirmURL'] = $this->_link(
            array(
                'lang_code' => $language->default_language,
                'page' => 'wirecard_checkout_page_checkout',
                'paction' => 'confirm',
                'conn' => 'SSL'
            )
        );
        $request['paymentType'] = (isset ($strPaymentType) && !empty ($strPaymentType)) ? $strPaymentType : "SELECT";
        $request['serviceURL'] = WIRECARD_CHECKOUT_PAGE_SERVICE_URL;
        $request['imageURL'] = WIRECARD_CHECKOUT_PAGE_IMAGE_URL;
        $request['displayText'] = WIRECARD_CHECKOUT_PAGE_DISPLAY_TEXT;
        $request['last_order_id'] = $_SESSION ['last_order_id'];
        $request['orderDescription'] = $this->_transaction_id . ' - ' . $order->order_data ['customers_email_address'];
        $request['orderDesc'] = $this->_transaction_id . ' - ' . $order->order_data['customers_email_address'];
        $request['pluginVersion'] = $pluginVersion;
        $request['consumerIpAddress'] = $_SERVER['REMOTE_ADDR'];
        $request['consumerUserAgent'] = $_SERVER['HTTP_USER_AGENT'];
        $this->initParams = array_merge($this->initParams, $request);
    }

    function _link($data)
    {
        global $xtLink;
        $ampedLink = $xtLink->_link($data);
        $link = str_replace('&amp;', '&', $ampedLink);
        return $link;
    }

    function _setCustomerData()
    {
        $genericData = $_SESSION['customer']->customer_default_address;
        $shippingData = $_SESSION['customer']->customer_shipping_address;
        $billingData = $_SESSION['customer']->customer_payment_address;

        $consumerBirthDateTimestamp = strtotime($genericData['customers_dob']);
        $consumerBirthDate = date('Y-m-d', $consumerBirthDateTimestamp);
        $request['consumerShippingFirstname'] = $shippingData['customers_firstname'];
        $request['consumerShippingLastname'] = $shippingData['customers_lastname'];
        $request['consumerShippingAddress1'] = $shippingData['customers_street_address'];
        $request['consumerShippingAddress2'] = $shippingData['customers_suborb'];
        $request['consumerShippingCity'] = $shippingData['customers_city'];
        $request['consumerShippingCountry'] = $shippingData['customers_country_code'];
        $request['consumerShippingZipCode'] = $shippingData['customers_postcode'];
        $request['consumerShippingPhone'] = $genericData['customers_phone'];
        $request['consumerShippingFax'] = $genericData['customers_fax'];
        $request['consumerBillingFirstname'] = $billingData['customers_firstname'];
        $request['consumerBillingLastname'] = $billingData['customers_lastname'];
        $request['consumerBillingAddress1'] = $billingData['customers_street_address'];
        $request['consumerBillingAddress2'] = $billingData['customers_suborb'];
        $request['consumerBillingCity'] = $billingData['customers_city'];
        $request['consumerBillingCountry'] = $billingData['customers_country_code'];
        $request['consumerBillingZipCode'] = $billingData['customers_postcode'];
        $request['consumerBillingPhone'] = $genericData['customers_phone'];
        $request['consumerBillingFax'] = $genericData['customers_fax'];
        $request['consumerBirthDate'] = $consumerBirthDate;
        $request['consumerEmail'] = $_SESSION['customer']->customer_info['customers_email_address'];
        $this->initParams = array_merge($this->initParams, $request);
    }

    function _initiateWirecardCheckoutPageSession()
    {
        if (strlen($_SESSION['redirect_url'])) {
            return $_SESSION['redirect_url'];
        }
        $requestDataString = $this->_createWirecardCheckoutPagePostData();
        $fp = fsockopen('ssl://' . $this->initHost, $this->initPort, $errno, $errstr, 30);
        if (!$fp) {
            $message = 'No route to the payment service provider';
            $this->_failureRedirect($message);
        } else {
            $out = "POST " . $this->initPath . " HTTP/1.1\r\n";
            $out .= "Host: " . $this->initHost . "\r\n";
            $out .= "User-Agent: PHP Veyton Plugin \r\n";
            $out .= "Content-type: application/x-www-form-urlencoded\r\n";
            $out .= "Content-length: " . strlen($requestDataString) . "\r\n";
            $out .= "Connection: close\r\n\r\n";
            $out .= $requestDataString . "\n";
            fwrite($fp, $out);
            $response = Array();
            while (!feof($fp)) {
                $responseEntry = explode('=', fgets($fp, 512));
                if ($responseEntry[0] == 'redirectUrl') {
                    fclose($fp);
                    $_SESSION['redirect_url'] = urldecode($responseEntry[1]);
                    return urldecode($responseEntry[1]);
                } else {
                    if ($responseEntry[0] == 'message') {
                        $message = $responseEntry[1];
                        $this->_failureRedirect($message);
                    }
                }
            }
            fclose($fp);
            $message = 'Invalid response from the payment service provider';
            $this->_failureRedirect($message);
        }
    }

    function _createWirecardCheckoutPagePostData()
    {
        $requestArray = $this->initParams;
        $requestData = Array();
        foreach ($requestArray AS $key => $value) {
            $requestData[] = urlencode($key) . '=' . urlencode($value);
        }
        $requestDataString = implode('&', $requestData);
        return $requestDataString;
    }

    function _failureRedirect($message)
    {
        global $xtLink;
        $failureUrl = $xtLink->_link(
            array('page' => 'wirecard_checkout_page_checkout', 'params' => 'message=' . $message, 'conn' => 'SSL')
        );
        $xtLink->_redirect($failureUrl);
    }

    function generate_trid()
    {
        global $db;
        do {
            $trid = $this->create_random_value(16);
            //$oDB = oxDb::getDb();
            $sSelect = "SELECT TRID FROM " . $this->_transaction_table . " WHERE TRID = '" . $trid . "'";
            $rs = @$db->Execute($sSelect);
        } while ($rs->recordCount());

        return $trid;
    }

    function create_random_value($length, $type = 'mixed')
    {
        if (($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) {
            return false;
        }

        $rand_value = '';
        while (strlen($rand_value) < $length) {
            if ($type == 'digits') {
                $char = $this->randomvalue(0, 9);
            } else {
                $char = chr($this->randomvalue(0, 255));
            }
            if ($type == 'mixed') {
                if (eregi('^[a-z0-9]$', $char)) {
                    $rand_value .= $char;
                }
            } elseif ($type == 'chars') {
                if (eregi('^[a-z]$', $char)) {
                    $rand_value .= $char;
                }
            } elseif ($type == 'digits') {
                if (ereg('^[0-9]$', $char)) {
                    $rand_value .= $char;
                }
            }
        }

        return $rand_value;
    }

    function randomvalue($min = null, $max = null)
    {
        static $seeded;

        if (!$seeded) {
            mt_srand(( double )microtime() * 1000000);
            $seeded = true;
        }

        if (isset ($min) && isset ($max)) {
            if ($min >= $max) {
                return $min;
            } else {
                return mt_rand($min, $max);
            }
        } else {
            return mt_rand();
        }
    }

    function getMajorVersion()
    {
        $parts = explode('.', _SYSTEM_VERSION);
        return (int)$parts[0];
    }

    function getMinorVersion()
    {
        $parts = explode('.', _SYSTEM_VERSION);
        return (int)$parts[1];
    }

}
