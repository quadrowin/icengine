<?php

namespace Ice;

/**
 *
 * @desc Абстрактный класс загрузчика
 * @author Юрий Шведов
 * @package Ice
 *
 */
abstract class Bootstrap_Abstract
{

	/**
	 * @desc Путь до начала структуры проекта.
	 * @var string
	 */
	protected $_appDir;

	/**
	 * @desc Название бутстрапа
	 * @var string
	 */
	protected $_name;

	/**
	 * @desc Пространство имен
	 * @var string
	 */
	protected $_namespace;

	/**
	 * @desc Флаг выполненного бутстрапа.
	 * @var boolean
	 */
	protected $_runned = false;

	/**
	 * @desc Возвращает загрузчик.
	 * @param string $path путь до этого загрузчика
	 */
	public function __construct ()
	{
		$this->_appDir = substr (
			$this->dir (),
			0,
			-strlen ('/App/Model/Bootstrap')
		);
		$class = get_class ($this);
		$p = strrpos ($class, '\\');
		if (false === $p)
		{
			$this->_name = substr ($class, strlen ('Bootstrap_'));
			$this->_namespace = '';
		}
		else
		{
			$this->_name = substr ($class, $p + strlen ('\\Bootstrap_'));
			$this->_namespace = substr ($class, 0, $p);
		}
	}

	/**
	 * @desc Запускает загрузчик.
	 */
	protected function _run ()
	{
		$this->addLoaderPathes ();

		Loader::multiLoad (
			'Manager_Abstract',
			'Config_Manager',
			'Zend_Exception'
		);

		Config_Manager::setPath (
			$this->getConfigDir () . '/',
			$this->getNamespace ()
		);
		$this->initFirePhp ();

		Loader::multiLoad (
			'Registry',
			'Request',
			'Executor',
			'Ice\\Exception',
			'Helper_Action',
			'Helper_Date',
			'Helper_Link',
			'Model',
			'Model_Child',
			'Model_Component',
			'Model_Collection',
			'Model_Factory',
			'Model_Factory_Delegate',
			'Model_Option',
			'Model_Content',
			'Model_Mapper_Scheme',
			'Component',
			'Controller_Abstract',
			'Controller_Exception',
			'Controller_Front',
			'Controller_Manager',
			'Page_Title',
			'View_Render',
			'View_Render_Manager',
			'View_Helper_Abstract',
			'Data_Transport_Manager'
		);

		$this->initMessageQueue ();

		$this->initDds ();

		$this->initAttributeManager ();

		$this->initModelScheme ($this->name ());

		$this->initModelManager ();

		$this->initView ();

		$this->initUser ();

		$this->initAcl ();
	}

	/**
	 * @desc Добавление путей в лоадер
	 */
	public function addLoaderPathes ()
	{
		$path = $this->getAppDir () . '/';

		$class = get_class ($this);
		$p = strrpos ($class, '\\');
		$namespace = substr ($class, 0, (int) $p);

		Loader::addPath ($namespace, $path . 'App/Class/');
		Loader::addPath ($namespace, $path . 'App/Controller/');
		Loader::addPath ($namespace, $path . 'App/Model/');
		Loader::addPath ($namespace, $path . 'App/');

		Loader::addPath ('includes', $path . 'Vendor/');
	}

	/**
	 * @desc Возвращает директорию бутстрапа
	 * В работающем загрузчике __DIR__ или dirname(__FILE__).
	 * @return string
	 */
	public function dir ()
	{
		$r = new \ReflectionClass ($this);
		return dirname ($r->getFileName ());
	}

	/**
	 * @desc Возвращает путь до начала структуры проекта.
	 * В проектах следует использовать Application::getDir().
	 * Класс Application должен быть реализован для каждого проекта.
	 * Application::getDir возвращает путь без заключительного слеша.
	 * @return string.
	 */
	public function getAppDir ()
	{
		return $this->_appDir;
	}

	/**
	 * @desc Возвращает директорию конфигов
	 * @return string
	 */
	public function getConfigDir ()
	{
		return $this->_appDir . '/Config';
	}

	/**
	 * @desc Вовзрващает рабочее пространство имен
	 * @return string
	 */
	public function getNamespace ()
	{
		return $this->_namespace;
	}

	/**
	 * @desc Директория для переменных
	 * @return string
	 */
	public function getVarDir ()
	{
		return $this->_appDir . '/Var';
	}

	/**
	 * @desc Инициализация менеджера атрибутов.
	 */
	public function initAttributeManager ()
	{
		Loader::load ('Attribute_Manager');
		Attribute_Manager::init ();
	}

	/**
	 * @desc Подключение контроля доступа
	 */
	public function initAcl ()
	{
		Loader::multiLoad (
			'Acl_Resource',
			'Acl_Role'
		);
	}

	/**
	 * @desc Инициализация источника данных по умолчанию.
	 */
	public function initDds ($source_name = 'default')
	{
		Loader::multiLoad (
			'Data_Provider_Abstract',
			'Data_Provider_Manager',

			'Query',
			'Query_Options',
			'Query_Result',
			'Query_Translator',

			'DDS',
			'Data_Mapper_Abstract',
			'Data_Source',
			'Data_Source_Manager'
		);

		DDS::setDataSource (
			Data_Source_Manager::getInstance ()->get ($source_name)
		);
	}

	/**
	 * @desc Подключение FirePHP
	 */
	public function initFirePhp ()
	{
		if (!function_exists ('fb'))
		{
			Loader::requireOnce ('FirePHPCore/fb.php', 'includes');
		}
	}

	/**
	 * @desc Инициализация очереди событий.
	 */
	public function initMessageQueue ()
	{
		Loader::load ('Message_Queue');
		Message_Queue::flush ();
	}

	/**
	 * @desc Инициализация менеджера моделей и менеджера коллекций.
	 */
	public function initModelManager ()
	{
		Loader::multiLoad (
			'Model_Manager',
			'Model_Collection_Manager'
		);
	}

	/**
	 * @desc Инициализация схемы моделей.
	 * @param string $name
	 */
	public function initModelScheme ($name)
	{
		Loader::load ('Model_Scheme');
		Model_Scheme::getInstance ()->init (
			Config_Manager::get ('Model_Scheme', $name)
		);
	}

	/**
	 * @desc Инициализация пользователя и сессии.
	 */
	public function initUser ()
	{
		Loader::multiLoad (
			'User_Abstract',
			'User',
			'User_Guest',
			'User_Session'
		);

		User_Guest::init ();
		User::init ();
	}

	/**
	 * @desc Инициализация рендера.
	 */
	public function initView ()
	{
		$manager = Core::di ()->getInstance ('Ice\\View_Render_Manager', $this);
		$view = $manager->getDefaultView ();

		$view->addTemplatesPath (array(
			Core::path () . 'Resource/Template/',
			$this->getAppDir () . '/Resource/Template/'
		));
	}

	/**
	 * @desc Возвращает название загрузчика.
	 * @return string
	 */
	public function name ()
	{
		return $this->_name;
	}

	/**
	 * @desc Запускает загрузчик, если этого не было сделано ранее.
	 */
	public function run ()
	{
		if (!$this->_runned)
		{
			$this->_runned = true;
			$this->_run ();
		}
	}

}