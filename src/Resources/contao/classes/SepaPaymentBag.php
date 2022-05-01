<?php

/**
 * @package   isotope-payment-sepa-bundle
 * @author    Michael Gruschwitz <info@grusch-it.de>
 * @license   LGPL-3.0+
 * @copyright Michael Gruschwitz 2019
 */

namespace Gruschit\Contao\Isotope\Payment\Sepa;

use Serializable;

/**
 * SEPA Payment Data Bag.
 *
 * Holds the bank account data for the SEPA payment module.
 *
 * @package    isotope-payment-sepa-bundle
 * @author     Michael Gruschwitz <info@grusch-it.de>
 * @copyright  Michael Gruschwitz 2019
 * @see        http://stackoverflow.com/questions/20983339/validate-iban-php#20983340
 */
class SepaPaymentBag implements Serializable
{

	/**
	 * @var array
	 */
	private $arrData = array();

	/**
	 * @param array $arrData
	 */
	public function __construct(array $arrData = array())
	{
		$this->arrData = $arrData;
	}

	/**
	 * Load payment bag from a serialized string.
	 *
	 * @param string $strSerialized
	 * @return static
	 */
	public static function load($strSerialized)
	{
		$static = new static;
		$static->unserialize($strSerialized);

		return $static;
	}

	/**
	 * Save a value of form field to the bag.
	 *
	 * Automatically encrypts the value before saving, if
	 * encryption is enabled for the form field.
	 *
	 * @param string $strKey The name of the form field
	 * @param string $strValue The value to be saved
	 */
	public function put($strKey, $strValue)
	{
		foreach (SepaCheckoutForm::getFieldConfigurations() as $strName => $arrField)
		{
			// unknown form field
			if ($strKey != $strName)
			{
				continue;
			}

			// do not save submit button values
			if (isset($arrField['inputType']) && $arrField['inputType'] == 'submit')
			{
				continue;
			}

			// encrypted value
			if (isset($arrField['eval']) && isset($arrField['eval']['encrypt_data']) && $arrField['eval']['encrypt_data'] == true)
			{
				$this->arrData[$strKey] = \Gruschit\Contao\Isotope\Payment\Sepa\Encryption::encrypt($strValue);
				continue;
			}

			$this->arrData[$strKey] = $strValue;
		}
	}

	/**
	 * Retrieve all values.
	 *
	 * @param bool $blnDecrypt Automatically decrypt values
	 * @return array
	 */
	public function all($blnDecrypt = true)
	{
		$arrData = array();
		foreach (SepaCheckoutForm::getFieldConfigurations() as $strName => $arrField)
		{
			// do not return submit button values
			if (isset($arrField['inputType']) && $arrField['inputType'] == 'submit')
			{
				continue;
			}

			$arrData[$strName] = $this->get($strName, $blnDecrypt);
		}

		return $arrData;
	}

	/**
	 * Retrieve a value.
	 *
	 * Automatically decrypts an encrypted value, if
	 * encryption is enabled for the form field.
	 *
	 * @param string $strKey The form fields name
	 * @param bool $blnDecrypt Automatically decrypt value
	 * @return mixed|null
	 */
	public function get($strKey, $blnDecrypt = true)
	{
		if ( ! isset($this->arrData[$strKey]))
		{
			return null;
		}

		foreach (SepaCheckoutForm::getFieldConfigurations() as $strName => $arrField)
		{
			// unknown form field
			if ($strKey != $strName)
			{
				continue;
			}

			// prevent decryption
			if ($blnDecrypt != true)
			{
				return $this->arrData[$strKey];
			}

			// decrypt value
			if (isset($arrField['eval']) && isset($arrField['eval']['encrypt_data']) && $arrField['eval']['encrypt_data'] == true)
			{
				return \Gruschit\Contao\Isotope\Payment\Sepa\Encryption::decrypt($this->arrData[$strKey]);
			}

			return $this->arrData[$strKey];
		}

		return null;
	}

	/**
	 * Remove a value from the session.
	 *
	 * @param string $strKey
	 */
	public function remove($strKey)
	{
		if (isset($this->arrData[$strKey]))
		{
			unset($this->arrData[$strKey]);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function serialize()
	{
		return serialize($this->arrData);
	}

	/**
	 * @inheritdoc
	 */
	public function unserialize($serialized)
	{
		$this->arrData = \Contao\StringUtil::deserialize($serialized, true);
	}
}
