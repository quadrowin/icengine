<?php

class Loader
{
	
	/**
	 * Пути
	 * @var array
	 */
	public static $pathes = array();
	
	/**
	 * Подключенные
	 * @var array
	 */
	public static $required = array();
	
	/**
	 * Добавление пути 
	 * @param string $type
	 * @param string $path
	 */
	public static function addPath ($type, $path)
	{
		if (!isset(self::$pathes [$type]))
		{
			self::$pathes [$type] = array($path);
		}
		else
		{
			self::$pathes [$type][] = $path;
		}
	}
	
	/**
	 * Добавление путей
	 * @param array $pathes
	 */
	public static function addPathes (array $pathes)
	{
		foreach ($pathes as $type => $path)
		{
			$path = (array) $path;

			if (isset (self::$pathes [$type]))
			{
				self::$pathes [$type] = array_merge (self::$pathes[$type], $path);
			}
			else
			{
				self::$pathes [$type] = $path;
			}
		}
	}
	
	/**
	 * Возвращает полный путь до файла.
	 * Если файла не существует, возвращается false.
	 * @param string $file
	 * @param string $type
	 * @return string
	 * 		Если файл найден, полный путь до файла.
	 * 		Иначе false. 
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
	 * 
	 * @param string $type
	 * @return array
	 */
	public static function getPathes($type)
	{
		if (!isset (self::$pathes [$type]))
		{
			return array ();
		}
		
		return self::$pathes [$type]; 
	}
	
	/**
	 * Проверяет был ли уже подключен файл
	 * @param string $file
	 * @param string $type
	 * @return bool
	 */
	public static function getRequired($file, $type)
	{
		return 
			isset (self::$required [$type]) && 
			isset (self::$required [$type][$file]);
	}
	
	/**
	 * Подключение файла
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
		
		if (!isset (self::$pathes [$type]))
		{
		    throw new Exception ('Path not found: ' . $type, E_USER_NOTICE);
			return false;
		}
		
		for ($i = count (self::$pathes [$type]) - 1; $i >= 0; $i--)
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
    		echo '<pre>';
    		debug_print_backtrace ();
    		trigger_error('File not found: ' . $file, E_USER_NOTICE);
    		echo 'Pathes: ';
    		var_dump (self::$pathes [$type]);
    		echo '</pre>';
		}
		
		return false;
	}
	
	/**
	 * 
	 * @param string $type
	 * @param string|array $path
	 */
	public static function setPath($type, $path)
	{
		self::$pathes[$type] = (array) $path;
	}
	
	/**
	 * 
	 * @param string $file
	 * @param string $type
	 * @param boolean $required
	 */
	public static function setRequired($file, $type, $required = true)
	{
		$required = $required ? true : null;
		if (isset(self::$required[$type]))
		{
			self::$required[$type][$file] = $required;
		}
		else
		{
			self::$required[$type] = array(
				$file	=> $required
			);
		}
	}
	
	/**
	 * 
	 * @param string $class
	 * @param string $path
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
		elseif (substr($path, -1, 1) == '/')
		{
			$path = $path . $class . '.php';
		}
		
		return self::requireOnce($path, 'Class');
	}
	
	/**
	 * 
	 * @param string $class_name
	 * @param string $type
	 * @return string;
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
	 * 
	 * @param string $class
	 * @param string $type
	 * @return boolean
	 */
	public static function loadExtClass($class, $type = '')
	{
		$class_name = empty($type) ? $class : $type . '_' . $class;
		
		if (class_exists($class_name))
		{
			return true;
		}
		
		return self::requireOnce($class . '.php', $type);
	}
	
}