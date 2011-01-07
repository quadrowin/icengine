<?php 

class Request
{
    
    const NONE_IP = '0.0.0.0';
	
	public static $_params = array();
	
	public static $post_charset = 'utf-8';
	public static $work_charset = 'utf-8';

	/**
	 * Получение параметра GET 
	 * @param string $name Имя параметра
	 * @param mixed $default Значение по умолчанию
	 * @return mixed
	 */
	public static function get ($name, $default = false)
	{
		if (isset ($_GET[$name]))
		{
			return $_GET [$name];
		}
		else
		{
			return $default;
		}
	}
	
	/**
	 * IP источника запроса
	 * @return string
	 */
	public static function ip ()
	{
		return isset ($_SERVER ['REMOTE_ADDR']) ? 
		    $_SERVER ['REMOTE_ADDR'] : self::NONE_IP;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public static function isAjax ()
	{
		return (
			isset ($_SERVER ['HTTP_X_REQUESTED_WITH']) &&
			$_SERVER ['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
		);
	}
	
	public static function isGet ()
	{
		return !empty ($_GET);
	}
	
	public static function isFiles ()
	{
		return !empty ($_FILES);
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public static function isPost ()
	{
		return (
			isset ($_SERVER ['REQUEST_METHOD']) &&
			$_SERVER ['REQUEST_METHOD'] == 'POST'
		);
	}
	
	/**
	 * Получение или установка параметра.
	 * 
	 * @param string $key
	 * 		Название параметра
	 * @param string $value
	 * 		Значение (не обязательно).
	 * 		Если передано значение, до оно будет установлено.
	 * @return string|null
	 * 		Если указано только название параметра, то возращается его значение
	 */
	public static function param ($key)
	{
		if (func_num_args () > 1)
		{
			self::$_params [$key] = func_get_arg (1);
		}
		else
		{
			return isset (self::$_params [$key]) ? self::$_params [$key] : null;
		}
	}
	
	/**
	 * Возвращает все параметры адресной строки
	 * 
	 * @return array 
	 */
	public static function params ()
	{
		return self::$_params;
	}
	
	/**
	 * Получение параметра POST
	 * @param string $name Имя параметра
	 * @param mixed $default Значение по умолчанию
	 * @return mixed 
	 */
	public static function post ($name, $default = false)
	{
		if (isset($_POST[$name]))
		{
			if (self::$work_charset == self::$post_charset)
			{
				return $_POST[$name];
			}
			else
			{
				return @iconv(
					self::$post_charset,
					self::$work_charset, 
					$_POST[$name]
				);
			}
		}
		else
		{
			return $default;
		}
	}
	
	/**
	 * Возвращает переданные скрипту id из $_REQUEST['id'] и $_REQUEST['ids']
	 * @return array
	 */
	public static function postIds ()
	{
		if (isset($_REQUEST['id']))
		{
			$item_ids = array(intval($_REQUEST['id']));
		}
		elseif (isset($_REQUEST['ids']))
		{
			$item_ids = $_REQUEST['ids'];
			if (!is_array($item_ids))
			{
				$item_ids = explode(',', $item_ids);
			}
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * 
	 * @param string $name Имя поля
	 * @return PostedFile|false
	 */
	public static function file ($name)
	{
		if (isset($_FILES[$name]) && !empty($_FILES[$name]['name']))
		{
			Loader::loadClass('PostedFile');
			return new PostedFile($_FILES[$name]);
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Возвращает объект переданного файла.
	 * 
	 * @param integer $index
	 * 		Индекс
	 * @return PostedFile
	 * 		Переданный файл.
	 * 		Если файлов меньше, чем указанный индекс - null.
	 */
	public static function fileByIndex ($index)
	{
		Loader::loadClass('PostedFile');
		$files = array_values($_FILES);
		return isset ($files [$index]) ? new PostedFile ($files [$index]) : null;
	}
	
	/**
	 * Возвращает массив объектов переданных файлов.
	 * @return array PostedFile
	 */
	public static function files ()
	{
		Loader::loadClass('PostedFile');
		$result = array();
		foreach ($_FILES as $name => $file)
		{
			$result[$name] = new PostedFile($file);
		}
		return $result;
	}
	
	/**
	 * Возвращает количество переданных в запросе файлов
	 * @return int Количество переданных файлов
	 */
	public static function filesCount ()
	{
		return count ($_FILES);
	}
	
	/**
	 * @return string
	 */
	public static function uri ()
	{
		return $_SERVER ['REQUEST_URI'];
	}
	
	/**
	 * @return string
	 */
	public static function referer ()
	{
		return $_SERVER ['HTTP_REFFERER'];
	}
	
	/**
	 * @return string
	 */
	public static function requestMethod ()
	{
		return $_SERVER ['REQUEST_METHOD'];
	}
	
	/**
	 * @return string
	 */
	public static function server ()
	{
		return $_SERVER ['SERVER_NAME'];
	}
	
	/**
	 * @return string
	 */
	public static function sessionId ()
	{
    	if (isset ($_COOKIE ['PHPSESSID']))
    	{
    		session_id ($_COOKIE ['PHPSESSID']);
    	}
    	elseif (isset ($_GET ['PHPSESSID']))
    	{
    		session_id ($_GET ['PHPSESSID']);
    	}
    	
    	return session_id ();
	}
	
}