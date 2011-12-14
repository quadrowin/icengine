<?php

namespace Ice;

/**
 *
 * @desc Мэнеджер конфигов.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Config_Manager
{

	/**
	 * @desc Путь до конфигов
	 * @var string
	 */
	protected static $_path;

	/**
	 * @desc Флаг означающий, что идет процесс загрузки конфига,
	 * необходим для предотвращения бесконечной рекурсии при
	 * загрузке конфигов для менеджера ресурсов.
	 * @var boolean
	 */
	protected static $_inLoading = false;

	/**
	 * @desc Загружает конфиг из файла и возвращает объект конфига.
	 * @param string $type Тип конфига.
	 * @param string|array $config Название конфига или конфиг по умолчанию.
	 * @return Config_Array|Objective Заруженный конфиг.
	 */
	protected static function _load ($type, $config = '')
	{
		$p = strrpos ($type, '\\');
		if (false !== $p) {
			$type = substr ($type, $p + 1);
		}

		$filename =
			self::getPath () .
			str_replace ('_', '/', $type) .
			(is_string ($config) && $config ? '/' . $config : '') .
			'.php';

		if (is_file ($filename))
		{
			$ext = ucfirst (strtolower (substr (strrchr ($filename, '.'), 1)));
			$class = __NAMESPACE__ . '\\Config_' . $ext;
			Loader::load ($class);

			$result = new $class ($filename);
		}
		else
		{
			$result = self::emptyConfig ();
		}

		return is_array ($config) ? $result->merge ($config) : $result;
	}

	/**
	 * @desc Пустой конфиг.
	 * @return Config_Array
	 */
	public static function emptyConfig ()
	{
		Loader::load ('Config_Array');
		return new Config_Array (array ());
	}

	/**
	 * @desc Загружает и возвращает конфиг.
	 * @param string $type Тип конфига.
	 * @param string|array $config [optional] Название или конфиг по умолчанию.
	 * 		Если параметром $config переданы настройки по умолчанию,
	 * 		результатом функции будет смержованный конфиг.
	 * @return Objective
	 */
	public static function get ($type, $config = '')
	{
		$rname = $type . (is_string ($config) && $config ? '/' . $config : '');

		if (self::$_inLoading)
		{
			return self::_load ($type, $config);
		}

		Loader::load ('Resource_Manager');

		self::$_inLoading = true;
		$cfg = Resource_Manager::get ('Config', $rname);
		self::$_inLoading = false;

		if (!$cfg)
		{
			$cfg = self::_load ($type, $config);
			Resource_Manager::set ('Config', $rname, $cfg);
		}

		return $cfg;
	}

	/**
	 * @desc Возвращает текущию директорию конфигов
	 * @return string
	 */
	public static function getPath() {
		if (!self::$_path) {
			self::$_path = Core::root () . 'Ice/Config/';
		}
		return self::$_path;
	}

	/**
	 * @desc Загрузка реального конфига, игнорируя менеджер ресурсов.
	 * @param string $type Тип конфига.
	 * @param string|array $config [optional] Название или конфиг по умолчанию.
	 */
	public static function getReal ($type, $config = null)
	{
		return self::_load ($type, $config);
	}

	/**
	 * @desc Устанавливает директорию конфигов
	 * @param string $path
	 */
	public static function setPath ($path)
	{
		self::$_path = $path;
	}

}