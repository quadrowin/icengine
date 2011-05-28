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
	 * @desc Фронт контроллер.
	 * @var Controller_Front
	 */
	private static $_frontController;
	
	/**
	 * @desc Задания, обработанные Font Controller
	 * @var Controller_Task
	 */
	private statis $_mainTask;
	
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
	 * @desc Менеджер аттрибутов.
	 * @var Attribute_Manager
	 */
	public static $attributeManager;
	
	/**
	 * @desc Загрузчик
	 * @var Bootstrap_Abstract
	 */
	public static $bootstrap;
	
	/**
	 * @desc Очередь сообщений.
	 * @var Message_Queue
	 */
	public static $messageQueue;
	
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
	 * @desc Вывод результата работы.
	 */
	public static function flush ()
	{
		Resource_Manager::save ();
		
		Controller_Manager::call (
			'Render', 'index',
			array (
				'task'		=> self::$_mainTask,
				'render'	=> View_Render_Manager::byName ('Front')
			)
		);
		
		View_Render_Manager::display ();
	}
	
	/**
	 * @desc Создает и возвращает фронт контроллер.
	 * @return Controller_Front
	 */
	public static function frontController ()
	{
		if (!self::$_frontController)
		{
			Loader::Load ('Controller_Front');
			self::$_frontController = new Controller_Front ();
		}
		return self::$_frontController;
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
	}
	
	/**
	 * @desc Подключает загрузчик и запускает его.
	 * @param string $bootstrap Путь до загрузчика.
	 */
	public static function initBootstrap ($bootstrap)
	{
		Loader::load ('Bootstrap_Abstract');
		Loader::load ('Bootstrap_Manager');
		require $bootstrap;
		$name = basename ($bootstrap, '.php');
		self::$bootstrap = Bootstrap_Manager::get ($name);
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
	 * @desc Проверка адреса страницы на существования роутера, который
	 * привязан к этой странице.
	 * @return Route|null
	 */
	public static function route ()
	{
		Loader::load ('Route');
		return Route::byUrl (Request::uri ());
	}
	
	/**
	 * @desc Запуск рабочего цикла и вывод результата.
	 */
	public static function run ()
	{
		Loader::load ('Data_Transport_Manager');
		
		self::$bootstrap->run ();
		
		self::$_mainTask = Controller_Manager::call (
			'Front', 'index',
			Data_Transport_Manager::get ('default_input')
		);
	}

}

