<?php

/**
 * @package   isotope-payment-sepa-bundle
 * @author    Michael Gruschwitz <info@grusch-it.de>
 * @license   LGPL-3.0+
 * @copyright Michael Gruschwitz 2019
 */

namespace Gruschit\Contao\Isotope\Payment\Sepa;

use Contao\Input;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;

/**
 * SEPA Payment Module.
 *
 * Collects and persists bank account data.
 *
 * @package    isotope-payment-sepa-bundle
 * @author     Michael Gruschwitz <info@grusch-it.de>
 * @copyright  Michael Gruschwitz 2019
 * @see        http://stackoverflow.com/questions/20983339/validate-iban-php#20983340
 */
class SepaPayment extends Payment implements IsotopePayment
{

	/**
	 * Process payment on checkout confirmation page.
	 *
	 * @param   IsotopeProductCollection $objOrder The order being placed
	 * @param   \Module $objModule The checkout module instance
	 * @return  bool
	 */
	public function processPayment(IsotopeProductCollection $objOrder, \Contao\Module $objModule)
	{
		if ( ! $objOrder instanceof Order)
		{
			return true;
		}

		if ( ! $objOrder->getPaymentMethod() instanceof SepaPayment)
		{
			return true;
		}

		if ($objOrder->getPaymentMethod()->sepa_persist && isset($_SESSION['SEPA_PAYMENT']))
		{
			$objOrder->payment_data = $_SESSION['SEPA_PAYMENT']->all(false);
		}

		$objOrder->checkout();
		$objOrder->updateOrderStatus($this->new_order_status);
		$objOrder->save();

		return true;
	}

	/**
	 * Return a html form for checkout
	 *
	 * @param IsotopeProductCollection $objOrder The order being placed
	 * @param \Module $objModule The checkout module instance
	 * @return bool|string
	 */
	public function checkoutForm(IsotopeProductCollection $objOrder, \Contao\Module $objModule)
	{
		$objForm = new SepaCheckoutForm($objModule->tableless);

		// continue checkout process if checkout form is valid
		if (Input::post('FORM_SUBMIT') == $objForm->getId() && $objForm->validate())
		{
			return false;
		}

		return $objForm->generate();
	}

	/**
	 * Return information or advanced features in the backend.
	 *
	 * Shows bank account data.
	 *
	 * @param integer $orderId Order ID
	 * @return string
	 */
	public function backendInterface($orderId)
	{
		if (is_null($objOrder = Order::findByPk($orderId)) || ! $objOrder instanceof Order)
		{
			return parent::backendInterface($orderId);
		}

		$objPaymentBag = SepaPaymentBag::load($objOrder->payment_data);
		$objInterface = new SepaBackendInterface($objPaymentBag, $objOrder->getPaymentMethod());

		return $objInterface->generate();
	}

	/**
	 * @param string $strRaw
	 * @return string
	 */
	public static function normalizeIBAN($strRaw)
	{
		return strtoupper(preg_replace('/[^\da-z]/i', '', $strRaw));
	}

	/**
	 * Retrieve masked IBAN code
	 *
	 * The country code and the first 4 and last 4 digits will be preserved.
	 * The rest will be replaced by $strChar letter.
	 *
	 * @param string $strRaw
	 * @param string $strChar
	 * @return string
	 */
	public static function maskIBAN($strRaw, $strChar = 'X')
	{
		$normalized = self::normalizeIBAN($strRaw);
		$cut = preg_replace('/^([a-z]{2}[0-9]{4})([0-9]+)([0-9]{4})$/i', '\1\3', $normalized);

		$first = substr($cut, 0, 6);
		$middle = str_repeat($strChar, max(0, strlen($normalized) - 10));
		$last = substr($cut, 6);

		return $first . $middle . $last;
	}
}
