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
	 * @desc Задача фронт контроллера.
	 * @var Controller_Task
	 */
	private static $_task;
	
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
	 * @desc Загрузчик
	 * @var Bootstrap_Abstract
	 */
	protected static $_bootstrap;
	
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
	 * @desc Получить текущий бутстрап
	 * @desc Bootstrap_Abstract
	 */
	public static function bootstrap ()
	{
		return self::$_bootstrap;
	}
	
	/**
	 * @desc Вывод результата работы.
	 */
	public static function flush ()
	{
		//Resource_Manager::save ();
		
		Controller_Manager::call (
			'Render', 'index',
			array (
				'task'		=> self::$_task,
				'render'	=> View_Render_Manager::byName ('Front')
			)
		);
	}
	
	/**
	 * @desc Инициализация лоадера.
	 * @param string $root Путь до корня сайта.
	 * @param string $bootstap Путь до загрузчика.
	 */
	public static function init ($root = null, $bootstap = null)
	{
		// Запоминаем путь до движка
		self::$_path = dirname (__FILE__) . '/';
		if (strlen (self::$_path) < 2)
		{
			self::$_path = '';
		}
		
		// путь до корня сайта
		self::$_root = $root ? 
			rtrim ($root, '/\\') . '/' : 
			self::_getRoot ();
		
		self::initLoader ();
		
		Loader::load ('Config_Manager');
		
		if ($bootstap)
		{
			self::initBootstrap ($bootstap);
		}
		
		register_shutdown_function (array (__CLASS__, 'shutdownHandler'));
	}
	
	public static function shutdownHandler ()
	{
		if (!error_get_last ())
		{
			Resource_Manager::save ();
		}
	}	
	
	/**
	 * @desc Подключает загрузчик и запускает его.
	 * @param string $bootstrap Путь до загрузчика.
	 */
	public static function initBootstrap ($bootstrap)
	{
		Loader::multiLoad (
			'Bootstrap_Abstract',
			'Bootstrap_Manager'
		);
		
		require $bootstrap;
		
		$name = basename ($bootstrap, '.php');
		self::$_bootstrap = Bootstrap_Manager::get ($name);
	}
	
	/**
	 * @desc Подключение класса Debug
	 */
	public static function initDebug ($params)
	{
		static $loaded = false;
		if (!$loaded)
		{
			$loaded = true;
			require dirname (__FILE__) . '/Class/Debug.php';
		}
		
		call_user_func_array (array ('Debug', 'init'), func_get_args ());
	}
	
	/**
	 * @desc Инициализация лоадера.
	 */
	public static function initLoader ()
	{
		require dirname (__FILE__) . '/Class/Loader.php';
		
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
	 * @desc Запуск рабочего цикла и вывод результата.
	 */
	public static function run ()
	{
		Loader::load ('Data_Transport_Manager'); 
		
		self::$_bootstrap->run ();
		
		self::$_task = Controller_Manager::call (
			'Front', 'index',
			Data_Transport_Manager::get ('default_input')
		);
	}

}

