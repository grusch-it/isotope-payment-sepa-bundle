<?php

/**
 * @package   isotope-payment-sepa-bundle
 * @author    Michael Gruschwitz <info@grusch-it.de>
 * @license   LGPL-3.0+
 * @copyright Michael Gruschwitz 2019
 */

/**
 * Payment methods
 */
\Isotope\Model\Payment::registerModelType('sepa', 'Gruschit\Contao\Isotope\Payment\Sepa\SepaPayment');

/**
 * Notification Center notification types
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'sepa_holder';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'sepa_iban';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'sepa_iban_masked';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'sepa_bic';

/**
 * Events / Hooks
 */
$GLOBALS['ISO_HOOKS']['getOrderNotificationTokens'][] = array('Gruschit\Contao\Isotope\Payment\Sepa\SepaPaymentEventHandler', 'onGetNotificationTokens');
$GLOBALS['ISO_HOOKS']['postCheckout'][] = array('Gruschit\Contao\Isotope\Payment\Sepa\SepaPaymentEventHandler', 'onPostCheckout');

/**
 * Checkout Form Validator
 */
$GLOBALS['TL_HOOKS']['addCustomRegexp'][] = array('Gruschit\Contao\Isotope\Payment\Sepa\SepaValidator', 'validate');
