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
	 * @desc Config
	 * @var array|Objective
	 */
	protected static $_config = array (
		'components' => array ()
	);

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
	 *
	 * @return Component_Manager
	 */
	protected static function _getComponentManager ()
	{
		return Core::di ()->getInstance('Ice\\Component_Manager', __CLASS__);
	}

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

		if (is_array ($config))
		{
			$result = $result->union ($config);
		}

		return self::compliteConfig ($type, $result);
	}

	/**
	 * @desc
	 * @param string $type
	 * @param Objective $config
	 * @return Objective
	 */
	public static function compliteConfig ($type, Config_Array $config)
	{
		if ($type == 'Config_Manager')
		{
			return $config;
		}

		$cfg = self::config ();

		if ($cfg ['components'] && $cfg ['components'][$type])
		{
			$components = $cfg ['components'][$type];

			foreach ($components as $component)
			{

				$dir = self::_getComponentManager ()->get ($component);
				$fn =
					$dir . '/Config/' .
					str_replace ('_', '/', $type) . '.php';

				if (file_exists ($fn))
				{
					$config->includeFile ($fn);
				}
			}
		}

		return $config;
	}

	/**
	 * @desc Возвращает конфиг
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (self::$_config))
		{
			self::$_config = self::get (__CLASS__, self::$_config);
		}
		return self::$_config;
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
	public static function getPath ()
	{
		if (!self::$_path)
		{
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