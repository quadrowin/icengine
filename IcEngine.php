<?php
/**
 * 
 * @desc Класс движка. Необходим для инициализации фреймворка.
 * @author Юрий
 * @package IcEngine
 *
 */
class IcEngine
{
	
	/**
	 * @desc Путь до движка.
	 * @var string
	 */
	private static $_path;
	
	/**
	 * @desc Путь до корня сайта.
	 * @var string
	 */
	private static $_root;
	
	/**
	 * @desc Приложение
	 * @var Application
	 */
	public static $application;
	
	/**
	 * @desc Менеджер атрибутов
	 * @var Attribute_Manager
	 */
	public static $attributeManager;
	
	/**
	 * @desc Менеджер моделей
	 * @var Model_Manager
	 */
	public static $modelManager;
	
	/**
	 * @desc Схема моделей.
	 * @var Model_Scheme
	 */
	public static $modelScheme;
	
	/**
	 * @desc Менеджер виджетов
	 * @var Widget_Manager
	 */
	public static $widgetManager;
	
	/**
	 * @desc Возвращает путь до корня сайта.
	 * @return string
	 */
	protected static function _getRoot ()
	{
		return isset ($_SERVER ['DOCUMENT_ROOT']) ?
			rtrim ($_SERVER ['DOCUMENT_ROOT'], '/') . '/' :
			rtrim (realpath (self::$_path . '..'), '/') . '/';
	}
	
	/**
	 * @desc Проверка адреса страницы на существования роутера, который
	 * привязан к этой странице.
	 * 
	 * @param string $route_table Префикс адреса, который не будет учитываться 
	 * при поиске роутера
	 * @param function $select Особая функция для вызова SQL запроса из Mysql.
	 * Если указана, будет вызвана со строковым параметром - sql запросом.
	 * Если не указана, sql запрос будет выполнен через mysql_query().
	 */
	public static function checkImplementation ($route_table = 'route', $select = null)
	{
		if (!isset ($_SERVER ['REQUEST_URI']))
		{
			return false;
		}
		
		// Отрезаем GET
		$request = $_SERVER ['REQUEST_URI'];
		$p = strpos ($request, '?');
		if ($p !== false)
		{
			$request = substr ($request, 0, $p);
		}
		
		$request = trim ($request, '/');
		$request_parts = explode ('/', $request);				
		$request = '/' . $request . '/';
		
		// Заменяем все числовые части запроса на нули,
		// чтобы запрос одной и той же страницы с разными параметрами
		// приводил к одному запросу к БД
		
		// Замеянем /12345678/ на /?/
		$base_req = preg_replace ('#/[0-9]{1,}/#i', '/?/', $request);
		$base_req = preg_replace ('#/[0-9]{1,}/#i', '/?/', $base_req);
		
		// Находим подходящие роутеры
		$query =
			"SELECT router.id
			 FROM `" . mysql_real_escape_string ($route_table) . "` AS router
			 WHERE 
			 	'" . mysql_real_escape_string ($base_req) .
						"' RLIKE router.template AND
			 	active = 1";

		if ($select)
		{
			$routers = call_user_func ($select, $query, $route_table);
		}
		else
		{
			$routers = mysql_query ($query);
			$routers = mysql_fetch_row ($routers);
		}
		
		return !empty ($routers);
	}
	
	/**
	 * @desc Вывод результата работы.
	 */
	public static function flush ()
	{
		self::$application->shutdown ();
	}
	
	/**
	 * @desc Путь до корня движка
	 * @return string
	 */
	public static function path ()
	{
		return self::$_path;
	}
	
	/**
	 * @desc Путь до корня сайта.
	 * @return string
	 */
	public static function root ()
	{
		return self::$_root;
	}
	
	/**
	 * @desc Инициализация лоадера.
	 * @param string $root Путь до корня сайта
	 */
	public static function init ($root = null)
	{
		// Запоминаем путь до движка
		self::$_path = dirname (__FILE__) . '/';
		if (strlen (self::$_path) < 2)
		{
			self::$_path = '';
		}
		
		// путь до корня сайта
		self::$_root = $root ? $root : self::_getRoot ();
		
		require self::$_path . 'Class/Loader.php';
		
		Loader::addPathes (array (
			'Class'			=> array (
				self::$_path . 'Class/',
				self::$_path . 'Model/',
				self::$_path
			),
			'Controller'	=> array (
				self::$_path . 'Controller/'
			),
			'includes'		=> self::$_path . 'includes/'
		));
		
		Loader::load ('Config_Manager');
	}
	
	/**
	 * @desc Инициализация окружения.
	 * @param string $behavior Название окружения.
	 * @param string $path Путь до файла окружения, если он находится
	 * не в директории движка.
	 */
	public static function initApplication ($behavior, $path = '')
	{
		Loader::load ('Application');
		
		self::$application = new Application ();
		self::$application->init ($behavior, $path);
	}
	
	/**
	 * @desc Запуск рабочего цикла и вывод результата.
	 */
	public static function run ()
	{
		self::$application->run ();
	}
	
	/**
	 * @desc Подключение класса Debug
	 */
	public static function useDebug ()
	{
		static $loaded = false;
		if ($loaded)
		{
			return;
		}
		$loaded = true;
		
		require self::$_path . 'Class/Debug.php';
	}

}