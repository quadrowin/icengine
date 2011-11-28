<?php
/**
 *
 * @desc Класс необходимый для инициализации фреймворка.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class IcEngine
{
	/**
	 * @desc Загрузчик
	 * @var Bootstrap_Abstract
	 */
	protected static $_bootstrap;

	/**
	 * @desc Путь до движка.
	 * @var string
	 */
	protected static $_path;

	/**
	 * @desc Путь до корня сайта.
	 * @var string
	 */
	protected static $_root;

/**
	 * @desc Задача фронт контроллера.
	 * @var Controller_Task
	 */
	protected static $_task;

	/**
	 * @desc Экшин фронт контролера по умолчанию
	 * @var string
	 */
	public static $frontAction = 'index';

	/**
	 * @desc Фронт контролер по умолчанию
	 * @var string
	 */
	public static $frontController = 'Front';

	/**
	 * @desc Название транспорта по умолчанию
	 * @var string
	 */
	public static $frontInput = 'default_input';

	/**
	 * @desc Рендер по умолчанию
	 * @var string
	 */
	public static $frontRender = 'Front';

	/**
	 * @desc Лайаут
	 * @var string
	 */
	public static $frontTemplate;

	/**
	 * @desc Зарегистрированные менеджеры
	 * @var array
	 */
	protected static $_managers = array ();

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
		Controller_Manager::call (
			'Render', 'index',
			array (
				'task'		=> self::$_task
			)
		);
	}

	/**
	 * @desc Инициализация лоадера
	 * @param string $root Путь до корня сайта
	 * @param string $bootstrap_class Класс загрузчика
	 * @param string $bootstrap_file Путь до загрузчика
	 */
	public static function init ($root = null, $bootstap_class = null,
		$bootstrap_file = null
	)
	{
		// Запоминаем путь до движка
		self::$_path = __DIR__ . '/';
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

		if ($bootstap_class)
		{
			self::initBootstrap ($bootstap_class, $bootstrap_file);
		}

		register_shutdown_function (array (__CLASS__, 'shutdownHandler'));
	}

	/**
	 * @desc Подключает загрузчик и запускает его
	 * @param string $class Класс загрузчика
	 * @param string $path Путь до файла загрузчика
	 */
	public static function initBootstrap ($class, $path)
	{
		Loader::multiLoad (
			'Bootstrap_Abstract',
			'Bootstrap_Manager'
		);

		self::$_bootstrap = Bootstrap_Manager::get ($class, $path);
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
			require __DIR__ . '/App/Class/Debug.php';
		}

		call_user_func_array (array ('Debug', 'init'), func_get_args ());
	}

	/**
	 * @desc Инициализация лоадера.
	 */
	public static function initLoader ()
	{
		require __DIR__ . '/App/Class/Loader.php';

		Loader::addPathes (array (
			'' => array (
				self::$_path . 'App/Class/',
				self::$_path . 'App/Model/',
				self::$_path . 'App/'
			),
			'includes' => self::$_path . 'Vendor/'
		));
	}

	/**
	 * @desc Подключение класса Tracer
	 */
	public static function initTracer ()
	{
		static $loaded = false;
		if (!$loaded)
		{
			$loaded = true;
			require __DIR__ . '/App/Class/Tracer.php';
		}
	}

	/**
	 * @desc Получить менеджера по имени
	 * @param string $name
	 * @return Manager_Abstract
	 */
	public static function getManager ($name)
	{
		if (!isset (self::$_managers [$name]))
		{
			Loader::load ($name . '_Manager');
			self::$_managers [$name] = new $name . '_Manager';
		}
		return self::$_managers [$name];
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
	 * @desc Зарегистрировать нового менеджера по имени
	 * @param string $name
	 * @param Manager_Abstract $manager
	 */
	public static function registerManager ($name, Manager_Abstract $manager)
	{
		self::$_managers [$name] = $manager;
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
		self::$_bootstrap->run ();

		Loader::multiLoad (
			'Data_Transport_Manager',
			'Controller_Task',
			'Controller_Action'
		);

		self::$_task = new Controller_Task (
			new Controller_Action (array (
				'id'			=> null,
				'controller'	=> self::$frontController,
				'action'		=> self::$frontAction
			))
		);

		self::$_task->setViewRender (
			View_Render_Manager::byName (self::$frontRender)
		);

		if (self::$frontTemplate)
		{
			self::$_task->setTemplate (self::$frontTemplate);
		}

		Controller_Manager::call (
			self::$frontController,
			self::$frontAction,
			Data_Transport_Manager::get (self::$frontInput),
			self::$_task
		);
	}

	public static function shutdownHandler ()
	{
		if (!error_get_last ())
		{
			Resource_Manager::save ();
		}
	}

}

