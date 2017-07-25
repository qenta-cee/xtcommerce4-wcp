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

global $db;

defined('_VALID_CALL') or die('Direct Access is not allowed.');
define('TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION', 'wirecard_checkout_page_transaction');

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$show_index_boxes = false;
if(isset($_SESSION['financialInstitution'])){
    unset($_SESSION['financialInstitution']);
}

if ($page->page_action == 'confirm') {
    $response = file_get_contents('php://input');
    _log(':raw:'.$response);

    $post = array();
    parse_str($response, $post);

    try {
        $return = WirecardCEE_QPay_ReturnFactory::getInstance($response, WIRECARD_CHECKOUT_PAGE_PROJECT_SECRET);

        if (!$return->validate()) {
            throw new Exception('Validation error: invalid response');
        }

        _log(':returned:'.$return->getReturned());

        if (!strlen($return->trid)) {
            throw new Exception('wirecard transaction id is missing');
        }
    } catch(Exception $e){
        _log($e->getMessage());
        _log($e->getTraceAsString());
    }

    $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString('Invalid call.');
    if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
        $stripSlashes = true;
    } else {
        $stripSlashes = false;
    }
    $paymentState = $return->getPaymentState();
    $brand = strlen($return->financialInstitution)?$return->financialInstitution:"";
    $everythingOk = false;
    $message = "";

    $aArrayToBeJSONized = $return->getReturned();
    unset($aArrayToBeJSONized['responseFingerprintOrder']);
    unset($aArrayToBeJSONized['responseFingerprint']);
    unset($aArrayToBeJSONized['trid']);
    unset($aArrayToBeJSONized['x']);
    unset($aArrayToBeJSONized['y']);

    $ok = $db->AutoExecute(
        TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION,
        Array(
            'ORDERNUMBER' => $return->getOrderNumber(),
            'ORDERDESCRIPTION' => $return->order_desc,
            'STATE' => $return->getPaymentState(),
            'MESSAGE' => $message,
            'BRAND' => $brand,
            'RESPONSEDATA' => json_encode($aArrayToBeJSONized),
            'PAYSYS' => $return->paymentType
        ),
        'UPDATE',
        'TRID="' . $return->trid . '"'
    );
    if (!$ok) {
        $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString(
            'Transactiontable update failed.'
        );
    }

    if ($paymentState == WirecardCEE_QPay_ReturnFactory::STATE_SUCCESS) {
        if (strlen($return->last_order_id)) {
            $order = new order($return->last_order_id, -1);
            $strOrderStatus = (strlen($return->paymentType) &&
                !empty($return->paymentType)) ? "QT" .
                $return->paymentType : "";
            updateOrderPayment($return->last_order_id, $strOrderStatus);
            $txtOk = $db->AutoExecute(
                TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION,
                Array(
                    'ORDERID' => $return->last_order_id
                ),
                'UPDATE',
                'TRID="' . $return->trid . '"'
            );
            if (!$txtOk) {
                $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString(
                    'Transactiontable update failed.'
                );
            } else {
                $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString();
            }
        }
        $strMsg = 'The amount has been authorized and captured by Wirecard CEE.';
        if (strlen($return->avsResultMessage) && strlen($return->avsResultCode)) {
            $strMsg .= '<br />AVS Response: ' . $return->avsResultMessage . '(' .
                $return->avsResultCode . ')';
        }

        if (!$order->_sendOrderMail()) {
            $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString(
                'Can\'t send confirmation mail.'
            );
        }

        $order->_updateOrderStatus(
            WIRECARD_CHECKOUT_PAGE_ORDER_STATUS_COMPLETED,
            $strMsg,
            'true'
        );
    }

    if ($paymentState == WirecardCEE_QPay_ReturnFactory::STATE_PENDING) {
        if (strlen($return->last_order_id)) {
            $order = new order($return->last_order_id, -1);
            $strOrderStatus = (strlen($return->paymentType) &&
                !empty($return->paymentType)) ? "QT" .
                $return->paymentType : "";
            updateOrderPayment($return->last_order_id, $strOrderStatus);
            $txtOk = $db->AutoExecute(
                TABLE_WIRECARD_CHECKOUT_PAGE_TRANSACTION,
                Array(
                    'ORDERID' => $return->last_order_id
                ),
                'UPDATE',
                'TRID="' . $return->trid . '"'
            );
            if (!$txtOk) {
                $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString(
                    'Transactiontable update failed.'
                );
            } else {
                $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString();
            }
        }
        $strMsg = 'The payment is pending, waiting for bank approval.';
        if (strlen($return->avsResultMessage) && strlen($return->avsResultCode)) {
            $strMsg .= '<br />AVS Response: ' . $return->avsResultMessage . '(' .
                $return->avsResultCode . ')';
        }
        $order->_updateOrderStatus(
            WIRECARD_CHECKOUT_PAGE_ORDER_STATUS_PENDING,
            $strMsg,
            'false'
        );
    }

    if ($paymentState == 'CANCEL') {
        if (strlen($return->last_order_id)) {
            $order = new order($return->last_order_id, -1);
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
                    'ORDERID' => $return->last_order_id
                ),
                'UPDATE',
                'TRID="' . $return->trid . '"'
            );
            if (!$txtOk) {
                $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString(
                    'Transactiontable update failed.'
                );
            } else {
                $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString();
            }
        }
    }

    if ($paymentState == 'FAILURE') {
        $order = new order($return->last_order_id, -1);
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
                'ORDERID' => $return->last_order_id
            ),
            'UPDATE',
            'TRID="' . $return->trid . '"'
        );
        if (!$txtOk) {
            $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString(
                'Transactiontable update failed.'
            );
        } else {
            $confirmReturnMessage = WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString();
        }
    }

    // send confirmation for status change
    die($confirmReturnMessage);
} else {
    $response = file_get_contents('php://input');
    $return = WirecardCEE_QPay_ReturnFactory::getInstance($response, WIRECARD_CHECKOUT_PAGE_PROJECT_SECRET);

    $strState = "";
    if (strlen($return->trid)) {

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
            $messages[1]['message'] = print_r($rs,true);
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
            die(WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString(
                'Paymenttype update failed'
            ));
        }
    }
}

function _log($msg){
    global $logHandler;

    $logHandler->_addLog('class.wirecard_checkout_page','confirm',0,$msg);
}

?>
