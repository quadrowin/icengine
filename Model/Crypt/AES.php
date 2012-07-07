<?php

/**
 * @desc Шифрование по методу AES (SSL 128 bit)
 * @author horr1f1k
 * @package IcEngine
 */
class Crypt_AES extends Crypt_Abstract
{
	private static $_privateKey;
	private static $_publicKey;
	
	public function __construct () {
		self::$_privateKey
			= file_get_contents(IcEngine::root() . 'Ice/Static/private_key.rsa');
		self::$_publicKey
			= file_get_contents(IcEngine::root() . 'Ice/Static/public_key.rsa');
	}
	
	public static function getPrivateKey() {
		return self::$_privateKey;
	}
	
	public static function getPublicKey() {
		return self::$_publicKey;
	}
	
    /**
	 * @desc Генерация ключей
	 * @return array keys
	 */
    public function generate_keys() {
      	$res=openssl_pkey_new();
		openssl_pkey_export($res, $privatekey);
		$publickey=openssl_pkey_get_details($res);
		$publickey=$publickey["key"];
		$keys = array(
			'privateKey'	=> $privatekey,
			'publicKey'		=> $publickey
		);
		return $keys;
    }
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::decode()
	 */
	public function encode ($input, $key = null) {
		if ($key == null) {
			$key = self::getPublicKey();
		}
		openssl_public_encrypt($input, $crypttext, $key);
		
		$e = array();
		for($i = 0; $i < strlen($crypttext); $i++) {
			$e[] = ord($crypttext[$i]);
		}
		
		return implode('.', $e);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crypt_Abstract::encode()
	 */
	public function decode ($input, $key = null) {
		$e = explode('.', $input);
		$input = "";
		foreach ($e as $code) {
			$input .= chr($code);
		}
		
		if ($key == null) {
			$key = self::getPrivateKey();
		}
		$input = openssl_private_decrypt($input, $decrypted, $key);
		return $decrypted;
	}

}

