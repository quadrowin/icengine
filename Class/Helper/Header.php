<?php
/**
 * 
 * @desc Хелпер управления заголовоками
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Helper_Header
{
	/**
	 * 
	 * @desc Код ошибки "ресурс перемещен постоянно"
	 * @var unknown_type
	 */
	const E301 = 301;
	
	/**
	 * @desc Код ошибки "доступ закрыт"
	 * @var integer
	 */
	const E403 = 403;
	
	/**
	 * @desc Код ошибки "страница не найдена"
	 * @var integer
	 */
	const E404 = 404;
	
	/**
	 * @desc Флаг кодирования gzip
	 * @var string
	 */
	const CONTENT_ENCODING_GZIP = 'gzip';
	
	/**
	 * @desc Сообщения с ошибками
	 * @var array
	 */
	public static $statuses = array (
		self::E403 => array (
			"HTTP/1.0 403 Permission Denied",
			"Status: 403 Permission Denied"
		),
		self::E404 => array(
			"HTTP/1.0 404 Not Found",
			"Status: 404 Not Found"
		),
		self::E301 => array(
			"HTTP/1.1 301 Moved Remanently",
			"Status: 301 Moved Remanently"
		)
	);
	
	/**
	 * @desc Отправить статус в заголовке ответа
	 * @param integer $status Статус.
	 */
	public static function setStatus ($status)
	{
//		if (headers_sent ())
//		{
//			return;
//		}
//		if (isset (self::$statuses [$status]))
//		{
			foreach (self::$statuses [$status] as $text)
			{
				header ($text);
			}
//		}
	}
	
	/**
	 * @desc Редирект по указанному адресу.
	 * К переданному $uri при необходимости будет добавлено имя сервера
	 * и "http://".
	 * @param string $uri
	 */
	public static function redirect ($uri)
	{
		if (substr ($uri, 0, 7) != 'http://')
		{
			$uri = 'http://' . $_SERVER ['HTTP_HOST'] . $uri;
		}
		
		if (!headers_sent ())
		{
			header ("Location: $uri");
		}
		else
		{
			echo '<script>window.location.href="' . $uri . '"</script>';
		}
	}
	
}