<?php
/**
 * 
 * @desc Помощник активации.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Activation
{
	
	/**
	 * @desc Генерация циферного кода для активации.
	 * @param integer $from Минимальное количество цифр.
	 * @param integer $to Максимальное количество цифр.
	 */
	public static function generateNumeric ($from = 5, $to = 7)
	{
		return rand (
			1 . str_repeat ('0', $from - 1),	// от 10000
			str_repeat ('9', $to)				// до 9999999
		);
	}
	
	/**
	 * @desc Создание активации с коротким кодом.
	 * @param string $prefix Префикс для кода.
	 * @param integer $from Минимальное количество символов.
	 * @param integer $to Максимальное количество символов.
	 * @return string Свободный код
	 */
	public static function newShortCode ($prefix, $from = 5, $to = 7)
	{
		Loader::load ('Activation');
		
		do {
			$code = $prefix . self::generateNumeric ($from, $to);
			$activation = Activation::byCode ($code);
		} while ($activation);
		
		return $code;
	}
	
	/**
	 * @desc Поиск активации по префиксу и короткому коду.
	 * @param string $prefix
	 * @param string $code
	 * @return Activation
	 */
	public static function byShortCode ($prefix, $code)
	{
		return Activation::byCode ($prefix . $code);
	}
	
}