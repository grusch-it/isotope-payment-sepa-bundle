<?php

/**
 * @package   isotope-payment-sepa-bundle
 * @author    Michael Gruschwitz <info@grusch-it.de>
 * @license   LGPL-3.0+
 * @copyright Michael Gruschwitz 2019
 */

namespace Gruschit\Contao\Isotope\Payment\Sepa;

/**
 * Encrypts and decrypts data.
 *
 * @package    isotope-payment-sepa-bundle
 * @author     Michael Gruschwitz <info@grusch-it.de>
 * @copyright  Michael Gruschwitz 2019
 */
class Encryption
{
    /**
	 * Object instance (Singleton)
	 * @var Encryption
	 */
	protected static $objInstance;

	/**
	 * Mcrypt resource
	 * @var object
	 */
	protected static $resTd;


	/**
	 * Encrypt a value
	 *
	 * @param mixed  $varValue The value to encrypt
	 * @param string $strKey   An optional encryption key
	 *
	 * @return string The encrypted value
	 */
	public static function encrypt($varValue, $strKey=null)
	{
		// Recursively encrypt arrays
		if (is_array($varValue))
		{
			foreach ($varValue as $k=>$v)
			{
				$varValue[$k] = static::encrypt($v);
			}

			return $varValue;
		}
		elseif ($varValue == '')
		{
			return '';
		}

		// Initialize the module
		if (static::$resTd === null)
		{
			static::initialize();
		}

		if (!$strKey)
		{
			$strKey = \Contao\System::getContainer()->getParameter('kernel.secret');
		}

		$iv = phpseclib_mcrypt_create_iv(phpseclib_mcrypt_enc_get_iv_size(static::$resTd));
		phpseclib_mcrypt_generic_init(static::$resTd, md5($strKey), $iv);
		$strEncrypted = phpseclib_mcrypt_generic(static::$resTd, $varValue);
		$strEncrypted = base64_encode($iv.$strEncrypted);
		phpseclib_mcrypt_generic_deinit(static::$resTd);

		return $strEncrypted;
	}


	/**
	 * Decrypt a value
	 *
	 * @param mixed  $varValue The value to decrypt
	 * @param string $strKey   An optional encryption key
	 *
	 * @return string The decrypted value
	 */
	public static function decrypt($varValue, $strKey=null)
	{
		// Recursively decrypt arrays
		if (is_array($varValue))
		{
			foreach ($varValue as $k=>$v)
			{
				$varValue[$k] = static::decrypt($v);
			}

			return $varValue;
		}
		elseif ($varValue == '')
		{
			return '';
		}

		// Initialize the module
		if (static::$resTd === null)
		{
			static::initialize();
		}

		$varValue = base64_decode($varValue);
		$ivsize = phpseclib_mcrypt_enc_get_iv_size(static::$resTd);
		$iv = substr($varValue, 0, $ivsize);
		$varValue = substr($varValue, $ivsize);

		if ($varValue == '')
		{
			return '';
		}

		if (!$strKey)
		{
			$strKey = \Contao\System::getContainer()->getParameter('kernel.secret');
		}

		phpseclib_mcrypt_generic_init(static::$resTd, md5($strKey), $iv);
		$strDecrypted = mdecrypt_generic(static::$resTd, $varValue);
		phpseclib_mcrypt_generic_deinit(static::$resTd);

		return $strDecrypted;
	}


	/**
	 * Initialize the encryption module
	 *
	 * @throws \Exception If the encryption module cannot be initialized
	 */
	protected static function initialize()
	{
		if ((self::$resTd = phpseclib_mcrypt_module_open(\Contao\Config::get('encryptionCipher'), '', \Contao\Config::get('encryptionMode'), '')) == false)
		{
			throw new \Exception('Error initializing encryption module');
		}
	}

	/**
	 * Initialize the encryption module
	 */
	protected function __construct()
	{
		static::initialize();
	}


	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final public function __clone() {}


	/**
	 * Return the object instance (Singleton)
	 *
	 * @return Encryption
	 */
	public static function getInstance()
	{
		if (static::$objInstance === null)
		{
			static::$objInstance = new static();
		}

		return static::$objInstance;
	}
}
