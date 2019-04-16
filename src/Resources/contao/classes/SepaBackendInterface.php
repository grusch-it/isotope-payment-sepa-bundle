<?php

/**
 * @package   isotope-payment-sepa-bundle
 * @author    Michael Gruschwitz <info@grusch-it.de>
 * @license   LGPL-3.0+
 * @copyright Michael Gruschwitz 2019
 */

namespace Gruschit\Contao\Isotope\Payment\Sepa;

use Contao\Environment;
use Contao\Frontend;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Template;

/**
 * SEPA Backend Interface.
 *
 * Shows the bank account data for an order.
 *
 * @package    isotope-payment-sepa-bundle
 * @author     Michael Gruschwitz <info@grusch-it.de>
 * @copyright  Michael Gruschwitz 2019
 * @see        http://stackoverflow.com/questions/20983339/validate-iban-php#20983340
 */
class SepaBackendInterface extends Frontend
{

	/**
	 * @var string
	 */
	protected $strTemplate = 'be_iso_payment_sepa';

	/**
	 * @var Template
	 */
	protected $Template;

	/**
	 * Create new backend interface.
	 *
	 * @param SepaPaymentBag $objPaymentBag
	 * @param IsotopePayment $objPayment
	 */
	public function __construct(SepaPaymentBag $objPaymentBag, IsotopePayment $objPayment)
	{
		parent::__construct();

		$this->Template = new Template($this->strTemplate);
		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['backBT'];
		$this->Template->backHref = ampersand(str_replace('&key=payment', '', Environment::get('request')));
		$this->Template->data = $objPaymentBag->all();
		$this->Template->name = $objPayment->name;
	}

	/**
	 * Parse the template
	 *
	 * @return string
	 */
	public function generate()
	{
		return $this->Template->parse();
	}

}
