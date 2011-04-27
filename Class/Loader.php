<?php
/**
 * 
 * @desc Загрузчик модулей и классов.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Loader
{
	
	/**
	 * @desc Пути.
	 * @var array
	 */
	public static $pathes = array ();
	
	/**
	 * @desc Подключенные.
	 * @var array
	 */
	public static $required = array ();
	
	/**
	 * @desc Добавление пути.
	 * @param string $type Тип.
	 * @param string $path Путь.
	 */
	public static function addPath ($type, $path)
	{
		if (!isset (self::$pathes [$type]))
		{
			self::$pathes [$type] = array ($path);
		}
		else
		{
			self::$pathes [$type][] = $path;
		}
	}
	
	/**
	 * @desc Добавление путей.
	 * @param array $pathes
	 */
	public static function addPathes (array $pathes)
	{
		foreach ($pathes as $type => $path)
		{
			$path = (array) $path;

			if (isset (self::$pathes [$type]))
			{
				self::$pathes [$type] = array_merge (self::$pathes [$type], $path);
			}
			else
			{
				self::$pathes [$type] = $path;
			}
		}
	}
	
	/**
	 * @desc Возвращает полный путь до файла.
	 * Если файла не существует, возвращается false.
	 * @param string $file Искомый файл.
	 * @param string $type Тип.
	 * @return string Если файл найден, полный путь до файла. Иначе false. 
	 */
	public static function findFile ($file, $type = 'Class')
	{
		foreach (self::$pathes [$type] as $path)
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
	 * @desc Возвращает все пути для указанного типа.
	 * @param string $type
	 * @return array
	 */
	public static function getPathes ($type)
	{
		if (!isset (self::$pathes [$type]))
		{
			return array ();
		}
		
		return self::$pathes [$type]; 
	}
	
	/**
	 * @desc Проверяет был ли уже подключен файл
	 * @param string $file
	 * @param string $type
	 * @return bool
	 */
	public static function getRequired ($file, $type)
	{
		return 
			isset (self::$required [$type]) && 
			isset (self::$required [$type][$file]);
	}
	
	/**
	 * @desc Подключение файла.
	 * @param string $file
	 * @param string $type
	 * @return boolean
	 */
	public static function requireOnce ($file, $type)
	{
		if (self::getRequired ($file, $type))
		{
			return true;
		}
		
		if (class_exists ('Loader_Cache'))
		{
//			var_dump (array (
//				'file'	=> $file,
//				'type'	=> $type
//			));
		}
		
		if (!isset (self::$pathes [$type]))
		{
			throw new Exception ('Path not found: ' . $type, E_USER_NOTICE);
			return false;
		}
		
		for ($i = count (self::$pathes [$type]) - 1; $i >= 0; --$i)
		{
			$fn = self::$pathes [$type][$i] . $file;
			if (file_exists ($fn))
			{
				self::setRequired ($file, $type);
				require_once $fn;
				return true;
			}
		}
		
		if (false)
		{
			echo '<pre>Not found: ' . $file . "\n";
			echo 'Pathes: ';
			var_dump (self::$pathes);
			var_dump (self::$pathes [$type]);
			echo "\n\n";
			//debug_print_backtrace ();
			echo '</pre>';
			die();
		}
		
		return false;
	}
	
	/**
	 * @desc Заного устанавливает пути до файлов. Предыдущие пути будут 
	 * удалены.
	 * @param string $type Тип.
	 * @param string|array $path Путь или массив патей.
	 */
	public static function setPath ($type, $path)
	{
		self::$pathes [$type] = (array) $path;
	}
	
	/**
	 * @desc Делает отметку о подключении файла.
	 * @param string $file Файл.
	 * @param string $type Тип.
	 * @param boolean $required [optional] 
	 */
	public static function setRequired ($file, $type, $required = true)
	{
		$required = $required ? true : null;
		if (isset (self::$required [$type]))
		{
			self::$required [$type][$file] = $required;
		}
		else
		{
			self::$required [$type] = array (
				$file	=> $required
			);
		}
	}
	
	/**
	 * @desc Подключение класса.
	 * @param string $class Название класса.
	 * @param string $path Путь.
	 * 	Имя файла или путь до него.
	 * 	Путь должен заканчиваться символом "/"
	 * @return boolean
	 */
	public static function loadClass ($class, $path = '')
	{
		if (class_exists ($class))
		{
			return true;
		}
		
		if (empty ($path))
		{
			$path = $class . '.php';
		}
		elseif (substr ($path, -1, 1) == '/')
		{
			$path = $path . $class . '.php';
		}
		
		return self::requireOnce ($path, 'Class');
	}
	
	/**
	 * @desc Подключение класса.
	 * @param string $class_name Название класса.
	 * @param string $type [optional]
	 * @return boolean true, если удалось подключить, иначе false.
	 */
	public static function load ($class, $type = 'Class')
	{
		if (class_exists ($class))
		{
			return true;
		}
		
		$file = str_replace ('_', '/', $class) . '.php';
		return self::requireOnce ($file, $type);
	}
	
	
	/**
	 * @desc Подключение класса указанного типа.
	 * @param string $class
	 * @param string $type
	 * @return boolean
	 */
	public static function loadExtClass ($class, $type = '')
	{
		$class_name = empty ($type) ? $class : $type . '_' . $class;
		
		if (class_exists ($class_name))
		{
			return true;
		}
		
		return self::requireOnce ($class . '.php', $type);
	}
	
	/**
	 * @desc Загрузка всех классов, переданных в параметрах.
	 * @param string $class...
	 */
	public static function multiLoad ()
	{
		foreach (func_get_args () as $class)
		{
			self::load ($class);
		}
	}
	
}