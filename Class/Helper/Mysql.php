<?php

/**
 * Хелпер для работы с mysql
 *
 * @author morph, goorus
 * @Service("helperMysql")
 */
class Helper_Mysql
{
	const SQL_ESCAPE = '`';
	const SQL_QUOTE	 = '"';
	const SQL_WILDCARD = '*';

	/**
	 * Обособляет название mysql терма, если в этом есть необходимость.
	 *
	 *	Функция вернет исходную строку, если в ней присутствуют спец. символы
	 * (точки, скобки, кавычки, знаки мат. операций и т.п.)
	 * @param string $value Название терма.
	 * @return string Резултат обособления.
	 */
	public function escape ($value)
	{
		if (
			strpos($value, self::SQL_WILDCARD) === false &&
			strpos($value, '(') === false &&
			strpos($value, ' ') === false &&
			strpos($value, '.') === false &&
			strpos($value, '<') === false &&
			strpos($value, '>') === false &&
			strpos($value, '`') === false
		)
		{
			return self::SQL_ESCAPE .
				addslashes(iconv('UTF-8', 'UTF-8//IGNORE', $value)) .
				self::SQL_ESCAPE;
		}
		return $value;
	}

	/**
	 * Заключает выражение в кавычки
	 *
     * @param mixed $value
	 * @return string
	 */
	public function quote($value)
	{
		if (is_array($value)) {
			debug_print_backtrace();
			die ();
		}
		return self::SQL_QUOTE .
			addslashes(iconv('UTF-8', 'UTF-8//IGNORE', stripslashes ($value))) .
			self::SQL_QUOTE;
	}
}