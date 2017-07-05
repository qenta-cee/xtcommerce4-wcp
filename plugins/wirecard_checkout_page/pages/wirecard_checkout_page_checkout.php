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
define('TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION', 'wirecard_checkout_page_transaction');

$show_index_boxes = false;
if(isset($_SESSION['financialInstitution'])){
    unset($_SESSION['financialInstitution']);
}

if ($page->page_action == 'confirm') {

    $confirmReturnMessage = wirecardCheckoutPageConfirmResponse('Invalid call.');
    if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
        $stripSlashes = true;
    } else {
        $stripSlashes = false;
    }
    $paymentState = $_POST['paymentState'];
    $brand = (isset($_POST['financialInstitution']) &&
        !empty($_POST['financialInstitution'])) ? $_POST['financialInstitution'] : "";
    $everythingOk = false;
    $message = "";
    if (strcmp($paymentState, 'CANCEL') == 0) {
        // use the default cancel message from the translations
        $message = "Transaction has been cancelled";
    } else {
        if (strcmp($paymentState, 'FAILURE') == 0) {
            // use the error message given from Wirecard Checkout Page system
            $message = $_POST['message'];
            $confirmState = 'OK';
        } else {
            if (strcmp($paymentState, 'SUCCESS') == 0 ||
                strcmp($paymentState, 'PENDING') == 0
            ) {
                $responseFingerprintOrder = $_POST['responseFingerprintOrder'];
                $responseFingerprint = $_POST['responseFingerprint'];

                $str4responseFingerprint = "";
                $mandatoryFingerprintFields = 0;
                $secretUsed = 0;

                $fieldsNeeded = 2;
                if (array_key_exists('orderNumber', $_POST)) {
                    $fieldsNeeded = 3;
                }

                $tempArray = [];
                $keyOrder = explode(',', $responseFingerprintOrder);
                for ($i = 0; $i < count($keyOrder); $i++) {
                    $key = $keyOrder[$i];
                    if ($key != 'secret') {
                        $tempArray[(string)$key] = (string)$_POST[$key];
                    }
                    if ($stripSlashes) {
                        $value = stripslashes($_POST[$key]);
                    } else {
                        $value = $_POST[$key];
                    }
                    // check if there are enough fields in the
                    // responsefingerprint
                    if ((strcmp($key, 'paymentState') == 0 && !empty($value)) ||
                        (strcmp($key, 'orderNumber') == 0 && !empty(
                            $value)) ||
                        (strcmp($key, 'paymentType') == 0 && !empty(
                            $value))
                    ) {
                        $mandatoryFingerprintFields++;
                    }

                    if (strcmp($key, 'secret') == 0) {
                        $str4responseFingerprint .= WIRECARD_CHECKOUT_PAGE_PROJECT_SECRET;
                        $tempArray[$key] = WIRECARD_CHECKOUT_PAGE_PROJECT_SECRET;
                        $secretUsed = 1;
                    } else {
                        $str4responseFingerprint .= $value;
                    }
                }

                $hash = hash_init('sha512', HASH_HMAC, WIRECARD_CHECKOUT_PAGE_PROJECT_SECRET);

                foreach ($tempArray AS $paramName => $paramValue) {
                    hash_update($hash, $paramValue);
                }

                $responseFingerprintCalc = hash_final($hash);


                if ((strcmp($responseFingerprintCalc, $responseFingerprint) != 0)) {
                    $message = "Fingerprint validation failed.";
                    $paymentState = "FAILURE";
                    $confirmReturnMessage = $message;
                } else {
                    if ($mandatoryFingerprintFields != $fieldsNeeded) {
                        $message = 'Mandatory fields not used.';
                        $paymentState = "FAILURE";
                        $confirmReturnMessage = $message;
                    } else {
                        if ($secretUsed == 0) {
                            $message = 'Secret not used.';
                            $paymentState = 'FAILURE';
                            $confirmReturnMessage = $message;
                        } else {
                            $everythingOk = true;
                        }
                    }
                }
            }
        }
    }

    $aArrayToBeJSONized = $_POST;
    unset($aArrayToBeJSONized['responseFingerprintOrder']);
    unset($aArrayToBeJSONized['responseFingerprint']);
    unset($aArrayToBeJSONized['trid']);
    unset($aArrayToBeJSONized['x']);
    unset($aArrayToBeJSONized['y']);

    $ok = $db->AutoExecute(
        TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION,
        Array(
            'ORDERNUMBER' => $_POST['orderNumber'],
            'ORDERDESCRIPTION' => $_POST['orderDesc'],
            'STATE' => $paymentState,
            'MESSAGE' => $message,
            'BRAND' => $brand,
            'RESPONSEDATA' => json_encode($aArrayToBeJSONized),
            'PAYSYS' => $_POST['paymentType']
        ),
        'UPDATE',
        'TRID="' . $_POST['trid'] . '"'
    );
    if (!$ok) {
        $confirmReturnMessage = wirecardCheckoutPageConfirmResponse(
            'Transactiontable update failed.'
        );
    }

    if ($paymentState == 'SUCCESS') {
        if (isset($_POST['last_order_id'])) {
            $order = new order($_POST['last_order_id'], -1);
            $strOrderStatus = (isset($_POST['paymentType']) &&
                !empty($_POST['paymentType'])) ? "QT" .
                $_POST['paymentType'] : "";
            updateOrderPayment($_POST['last_order_id'], $strOrderStatus);
            $txtOk = $db->AutoExecute(
                TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION,
                Array(
                    'ORDERID' => $_POST['last_order_id']
                ),
                'UPDATE',
                'TRID="' . $_POST['trid'] . '"'
            );
            if (!$txtOk) {
                $confirmReturnMessage = wirecardCheckoutPageConfirmResponse(
                    'Transactiontable update failed.'
                );
            } else {
                $confirmReturnMessage = wirecardCheckoutPageConfirmResponse();
            }
        }
        $strMsg = 'The amount has been authorized and captured by Wirecard CEE.';
        if (isset($_POST['avsResultMessage']) && isset($_POST['avsResultCode'])) {
            $strMsg .= '<br />AVS Response: ' . $_POST['avsResultMessage'] . '(' .
                $_POST['avsResultCode'] . ')';
        }

// changed api, doesent work anymore, setting _updateOrderStatus, send_mail Option to true instead
//        if (!$order->_sendStatusMail($order->order_data['orders_status'], $ok)) {
//            $confirmReturnMessage = wirecardCheckoutPageConfirmResponse(
//                'Can\'t send status mail.'
//            );
//        }

        if (!$order->_sendOrderMail()) {
            $confirmReturnMessage = wirecardCheckoutPageConfirmResponse(
                'Can\'t send confirmation mail.'
            );
        }

        $order->_updateOrderStatus(
            WIRECARD_CHECKOUT_PAGE_ORDER_STATUS_COMPLETED,
            $strMsg,
            'true'
        );
    }

    if ($paymentState == 'PENDING') {
        if (isset($_POST['last_order_id'])) {
            $order = new order($_POST['last_order_id'], -1);
            $strOrderStatus = (isset($_POST['paymentType']) &&
                !empty($_POST['paymentType'])) ? "QT" .
                $_POST['paymentType'] : "";
            updateOrderPayment($_POST['last_order_id'], $strOrderStatus);
            $txtOk = $db->AutoExecute(
                TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION,
                Array(
                    'ORDERID' => $_POST['last_order_id']
                ),
                'UPDATE',
                'TRID="' . $_POST['trid'] . '"'
            );
            if (!$txtOk) {
                $confirmReturnMessage = wirecardCheckoutPageConfirmResponse(
                    'Transactiontable update failed.'
                );
            } else {
                $confirmReturnMessage = wirecardCheckoutPageConfirmResponse();
            }
        }
        $strMsg = 'The payment is pending, waiting for bank approval.';
        if (isset($_POST['avsResultMessage']) && isset($_POST['avsResultCode'])) {
            $strMsg .= '<br />AVS Response: ' . $_POST['avsResultMessage'] . '(' .
                $_POST['avsResultCode'] . ')';
        }
        $order->_updateOrderStatus(
            WIRECARD_CHECKOUT_PAGE_ORDER_STATUS_PENDING,
            $strMsg,
            'false'
        );
    }

    if ($paymentState == 'CANCEL') {
        if (isset($_POST['last_order_id'])) {
            $order = new order($_POST['last_order_id'], -1);
            $strMsg = 'Customer canceled the payment process';
            if (!checkPaid($order)) {
                $order->_updateOrderStatus(
                    WIRECARD_CHECKOUT_PAGE_ORDER_STATUS_CANCEL,
                    $strMsg,
                    'false'
                );
            }
            $txtOk = $db->AutoExecute(
                TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION,
                Array(
                    'ORDERID' => $_POST['last_order_id']
                ),
                'UPDATE',
                'TRID="' . $_POST['trid'] . '"'
            );
            if (!$txtOk) {
                $confirmReturnMessage = wirecardCheckoutPageConfirmResponse(
                    'Transactiontable update failed.'
                );
            } else {
                $confirmReturnMessage = wirecardCheckoutPageConfirmResponse();
            }
        }
    }

    if ($paymentState == 'FAILURE') {
        $order = new order($_POST['last_order_id'], -1);
        $strMsg = htmlentities($message);
        $payment_error_message = 'An error occured during the payment process: <br>' .
            $strMsg;

        // Order-Status setzen und History speichern
        if (!checkPaid($order)) {
            $order->_updateOrderStatus(
                WIRECARD_CHECKOUT_PAGE_ORDER_STATUS_FAILED,
                $payment_error_message,
                'false'
            );
        }
        $txtOk = $db->AutoExecute(
            TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION,
            Array(
                'ORDERID' => $_POST['last_order_id']
            ),
            'UPDATE',
            'TRID="' . $_POST['trid'] . '"'
        );
        if (!$txtOk) {
            $confirmReturnMessage = wirecardCheckoutPageConfirmResponse(
                'Transactiontable update failed.'
            );
        } else {
            $confirmReturnMessage = wirecardCheckoutPageConfirmResponse();
        }
    }

    // send confirmation for status change
    die($confirmReturnMessage);
} else {
    $strState = "";
    if (isset($_POST['trid'])) {

        if (isset($_SESSION['redirect_url'])) {
            unset($_SESSION['redirect_url']);
        }

        if (WIRECARD_CHECKOUT_PAGE_USE_IFRAME == 'true' &&
            !isset($_POST['mainFrame'])
        ) {
            ?>
            <html>
            <head>
                <title><?php echo TEXT_WIRECARD_CHECKOUT_PAGE_CHECKOUT_IFRAME_REDIRECT; ?></title>
            </head>
            <body>
            <form id="wirecardCheckoutPageBreakout"
                  name="wirecardCheckoutPageBreakout"
                  action="<?php echo $xtLink->_link(
                      array('page' => 'wirecard_checkout_page_checkout', 'conn' => 'SSL')
                  ); ?>"
                  method="POST" target="_parent">
                <?php
                foreach ($_POST as $paramKey => $value) {
                    ?>
                    <input type="hidden"
                           name="<?php echo $paramKey; ?>"
                           value="<?php echo $value; ?>">
                    <?php
                }
                ?>
                <input type="hidden" name="mainFrame" value="true">
            </form>
            <h3><?php echo TEXT_WIRECARD_CHECKOUT_PAGE_CHECKOUT_IFRAME_REDIRECT; ?></h3>
            <script type="text/javascript">
                document.wirecardCheckoutPageBreakout.submit();
            </script>
            </body>
            </html>
            <?php
            die();
        }
        $rs = $db->Execute(
            'SELECT STATE,MESSAGE FROM ' .
            TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION .
            ' WHERE `TRID`="' . $_POST['trid'] . '" '
        );
        if ($rs->RecordCount() == 1) {
            $strState = $rs->fields['STATE'];
        }
    }

    if ($strState == 'SUCCESS') {
        unset($_SESSION['last_order_id']);
        $_SESSION['cart']->_resetCart();
        $checkout_data = array(
            'page_action' => 'success'
        );
    } elseif ($strState == 'CANCEL') {
        $checkout_data = array(
            'page_action' => 'cancel'
        );
    } elseif ($strState == 'PENDING') {
        unset($_SESSION['last_order_id']);
        $_SESSION['cart']->_resetCart();
        $checkout_data = array(
            'page_action' => 'pending'
        );
    } elseif ($strState == 'FAILURE') {
        $messages = array();
        $messages[0]['message'] = $_POST['consumerMessage'];
        $checkout_data = array(
            'page_action' => 'failure',
            'messages' => $messages
        );
    } else {
        $messages = array();
        if (isset($_GET['message'])) {
            $messages[0]['message'] = htmlentities($_GET['message']);
        } else {
            $messages[0]['message'] = 'Invalid call';
        }
        $checkout_data = array(
            'page_action' => 'failure',
            'messages' => $messages
        );
    }

    if (is_array($checkout_data)) {
        $tpl_data = $checkout_data;
        ($plugin_code = $xtPlugin->PluginCode(
            'module_checkout.php:checkout_data'
        )) ? eval($plugin_code) : false;
        $template = new Template();
        $tpl = 'wirecard_checkout_page_checkout.html';
        ($plugin_code = $xtPlugin->PluginCode(
            'module_checkout.php:checkout_bottom'
        )) ? eval($plugin_code) : false;

        $page_data = $template->getTemplate(
            'smarty',
            '/' . _SRV_WEB_CORE . 'pages/' . $tpl,
            $tpl_data
        );
    }
}

function checkPaid($order)
{
    return (bool)($order->order_data['orders_status_id'] == WIRECARD_CHECKOUT_PAGE_ORDER_STATUS_COMPLETED);
}

function updateOrderPayment($oid, $strOrderStatus)
{
    if (!empty($strOrderStatus) && $oid > 0) {
        global $db;
        $ok = $db->AutoExecute(
            TABLE_ORDERS,
            Array(
                'subpayment_code' => $strOrderStatus
            ),
            'UPDATE',
            'orders_id="' . $oid . '" AND subpayment_code!="' .
            $strOrderStatus . '"'
        );
        if (!$ok) {
            return wirecardCheckoutPageConfirmResponse(
                'Paymenttype update failed'
            );
        }
    }
    return true;
}

function wirecardCheckoutPageConfirmResponse($message = null)
{
    if ($message != null) {
        $value = 'result="NOK" message="' . $message . '" ';
    } else {
        $value = 'result="OK"';
    }
    return '<QPAY-CONFIRMATION-RESPONSE ' . $value . ' />';
}

?>
