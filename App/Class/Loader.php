<?php
/**
 *
 * @desc Загрузчик модулей и классов.
 * @author Юрий Шведов, Илья Колесников
 * @package Ice
 *
 */
class Loader
{

	/**
	 * @desc Пространства имен.
	 * Будет отличаться для потомков загрузчика в других пространствах имен.
	 * @var string
	 */
	protected static $_namespaces = array();

	/**
	 * @desc Пути поиска файлов
	 * @var array
	 */
	protected static $_pathes = array ();

	/**
	 * @desc Подключенные файлы
	 * @var array
	 */
	protected static $_required = array ();

	/**
	 * @desc Добавление пути
	 * @param string $namespace Пространство имен
	 * @param string $path Путь
	 */
	public static function addPath ($namespace, $path)
	{
		if (!isset (self::$_pathes [$namespace]))
		{
			self::$_pathes [$namespace] = array ($path);
		}
		else
		{
			self::$_pathes [$namespace][] = $path;
		}
	}

	/**
	 * @desc Добавление путей
	 * @param array $pathes
	 */
	public static function addPathes (array $pathes)
	{
		foreach ($pathes as $namespace => $path)
		{
			$path = (array) $path;

			if (isset (self::$_pathes [$namespace]))
			{
				self::$_pathes [$namespace] = array_merge (
					self::$_pathes [$namespace],
					$path
				);
			}
			else
			{
				self::$_pathes [$namespace] = $path;
			}
		}
	}

	/**
	 * @desc Возвращает полный путь до файла.
	 * Если файла не существует, возвращается false.
	 * @param string $file Искомый файл.
	 * @param string $namespace [optional] Пространство
	 * @return string Если файл найден, полный путь до файла. Иначе false.
	 */
	public static function findFile ($file, $namespace = null)
	{
		if (null === $namespace)
		{
			$namespace = static::getNamespace ();
		}

		foreach (self::$_pathes [$namespace] as $path)
		{
			$fn = $path . $file;
			if (file_exists ($fn))
			{
				return $fn;
			}
		}

		return false;
	}

	/**
	 * @desc Возвращает пространство имен, в котором работает загрузчик
	 * @return string Пространство имен
	 */
	public static function getNamespace ()
	{
		$class = get_called_class ();
		if (!isset (self::$_namespaces [$class]))
		{
			$p = strrpos ($class, '\\');
			self::$_namespaces [$class] = (false === $p)
				? ''
				: substr ($class, 0, (int) $p);
		}
		return self::$_namespaces [$class];
	}

	/**
	 * @desc Возвращает все пути для указанного типа.
	 * @param string $namespace Пространство
	 * @return array
	 */
	public static function getPathes ($namespace)
	{
		return isset (self::$_pathes [$namespace])
			? self::$_pathes [$namespace]
			: array ();
	}

	/**
	 * @desc Проверяет был ли уже подключен файл
	 * @param string $file Название
	 * @param string $namespace
	 * @return boolean
	 */
	public static function getRequired ($file, $namespace)
	{
		return isset (self::$_required [$namespace][$file]);
	}

	/**
	 * @desc Подключение класса.
	 * @param string $class_name Название класса.
	 * @param string $namespace [optional] Пространство
	 * @return boolean true, если удалось подключить, иначе false.
	 */
	public static function load ($class, $namespace = null)
	{
		if (class_exists ($class))
		{
			return true;
		}

		if (null === $namespace)
		{
			$p = strpos ($class, '\\');

			if (false === $p)
			{
				$namespace = static::getNamespace ();
			}
			else
			{
				$namespace = substr ($class, 0, $p);
				$class = substr ($class, $p + 1);
			}
		}

		return self::requireOnce (
			str_replace ('_', '/', $class) . '.php',
			$namespace
		);
	}

	/**
	 * @desc Загрузка всех классов, переданных в параметрах.
	 * @param string $class...
	 */
	public static function multiLoad ()
	{
		$namespace = static::getNamespace ();
		foreach (func_get_args () as $class)
		{
			if (!class_exists ($class))
			{
				self::requireOnce (
					str_replace ('_', '/', $class) . '.php',
					$namespace
				);
			}
		}
	}

	/**
	 * @desc Подключение файла.
	 * @param string $file Название файла
	 * @param string $namespace Пространство
	 * @return boolean
	 */
	public static function requireOnce ($file, $namespace)
	{
		if (isset (self::$_required [$namespace][$file]))
		{
			return true;
		}

		for ($i = count (self::$_pathes [$namespace]) - 1; $i >= 0; --$i)
		{
			$fn = self::$_pathes [$namespace][$i] . $file;

			if (file_exists ($fn))
			{
				self::$_required [$namespace][$file] = true;
				require_once $fn;
				return true;
			}
		}

		if (true)
		{
			include __DIR__ . '/Loader/Exception.php';
//			throw new Loader_Exception ("Not found: $file");
			echo '<pre>Not found: ' . $file . "\n";
//			echo 'Pathes: ';
//			var_dump (self::$_pathes);
//			var_dump (self::$_pathes [$namespace]);
			echo "\n\n";
			debug_print_backtrace ();
			echo '</pre>';
			die();
		}

		return false;
	}

	/**
	 * @desc Устанавливает пространство имен по умолчанию
	 * @param string $namespace Связанное пространство имен
	 */
	public static function setNamespace ($namespace)
	{
		self::$_namespaces [get_called_class ()] = $namespace;
	}

	/**
	 * @desc Заного устанавливает пути до файлов. Предыдущие пути будут
	 * удалены.
	 * @param string $namespace Пространство
	 * @param string|array $path Путь или массив патей.
	 */
	public static function setPath ($namespace, $path)
	{
		self::$_pathes [$namespace] = (array) $path;
	}

	/**
	 * @desc Делает отметку о подключении файла.
	 * @param string $file Файл.
	 * @param string $namespace Пространство.
	 * @param boolean $required [optional]
	 */
	public static function setRequired ($file, $namespace, $required = true)
	{
		self::$_required [$namespace][$file] = $required ? true : null;
	}

	/**
	 * @desc Попытка подключить указанный класс. В случае ошибки
	 * не возникает исключения.
	 * @param string $class Название класса.
	 * @param string $namespace [optional]
	 * @return boolean true в случае, если файл класса подключен или класс
	 * уже подключен, иначе false.
	 */
	public static function tryLoad ($class, $namespace = null)
	{
		if (class_exists ($class))
		{
			return true;
		}

		if (null === $namespace)
		{
			$p = strpos ($class, '\\');

			if (false === $p)
			{
				$namespace = self::getNamespace ();
			}
			else
			{
				$namespace = substr ($class, 0, $p);
				$class = substr ($class, $p + 1);
			}
		}

		if (!isset (self::$_pathes [$namespace]))
		{
			return false;
		}

		$file = str_replace ('_', '/', $class) . '.php';

		for ($i = count (self::$_pathes [$namespace]) - 1; $i >= 0; --$i)
		{
			$fn = self::$_pathes [$namespace][$i] . $file;

			if (file_exists ($fn))
			{
				self::$_required [$namespace][$file] = true;
				require_once $fn;
				return true;
			}
		}

		return false;
	}

}