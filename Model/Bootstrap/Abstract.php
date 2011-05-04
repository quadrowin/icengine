<?php
/**
 * 
 * @desc Абстрактный класс загрузчика
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Bootstrap_Abstract
{
	
	/**
	 * @desc Путь до начала структуры Ice.
	 * @var string
	 */
	protected $_basePath;
	
	/**
	 * @desc Название бутстрапа
	 * @var string
	 */
	protected $_name;
	
	/**
	 * @desc Флаг выполненного бутстрапа.
	 * @var boolean
	 */
	protected $_runned = false;
	
	/**
	 * @desc Возвращает загрузчик.
	 * @param string $path путь до этого загрузчика
	 */
	public function __construct ($path)
	{
		$this->_basePath = substr (
			$path,
			0,
			- strlen ('Model_' . get_class ($this) . '.php')
		);
		$this->_name = substr (get_class ($this), strlen ('Bootstrap_'));
	}
	
	/**
	 * @desc Запускает загрузчик.
	 */
	protected function _run ()
	{
		$this->addLoaderPathes ();
		
		Loader::load ('Config_Manager');
		
		Loader::load ('Zend_Exception');
		
		$this->initFirePhp ();
		$this->initMessageQueue ();
		$this->initDds ();
			
		Loader::load ('Registry');
		Loader::load ('Request');
		Loader::load ('Cache_Manager');
		Loader::load ('Executor');
		Loader::load ('Helper_Action');
		Loader::load ('Helper_Date');
		Loader::load ('Helper_Link');
		Loader::load ('Model');
		Loader::load ('Model_Child');
		Loader::load ('Model_Content');
		Loader::load ('Model_Component');
		Loader::load ('Model_Collection');
		Loader::load ('Model_Factory');
		Loader::load ('Model_Factory_Delegate');
		Loader::load ('Component');
		Loader::load ('Controller_Abstract');
		Loader::load ('Controller_Front');
		Loader::load ('Controller_Manager');
		Loader::load ('Page_Title');
		Loader::load ('View_Render');
		Loader::load ('View_Render_Broker');
		Loader::load ('View_Helper_Abstract');
			
		$this->initAttributeManager ();
		$this->initModelScheme ($this->name ());
			
		DDS::getDataSource ()->getDataMapper ()->setModelScheme (
			IcEngine::$modelScheme
		);
			
		$this->initModelManager ();
		$this->initWidgetManager ();
		$this->initView ();
		$this->initUser ();
		$this->initAcl ();
	}
	
	/**
	 * @desc Добавление путей в лоадер
	 */
	public function addLoaderPathes ()
	{
		$path = $this->basePath ();
		
		Loader::addPath ('Class', $path . 'Class/');
		Loader::addPath ('Class', $path . 'Model/');
		Loader::addPath ('Class', $path);
		
		Loader::addPath ('Controller', IcEngine::path () . 'Controller/');
		Loader::addPath ('Controller', $path . 'Controller/');
		
		Loader::addPath ('includes', $path . 'includes/');
	}
	
	/**
	 * @desc Возвращает путь до начала структуры Ice.
	 * @return string.
	 */
	public function basePath ()
	{
		return $this->_basePath;
	}
	
	/**
	 * @desc Инициализация менеджера атрибутов.
	 */
	public function initAttributeManager ()
	{
		Loader::load ('Attribute_Manager');
		IcEngine::$attributeManager = new Attribute_Manager (
			DDS::getDataSource ()
		);
	}
	
	/**
	 * @desc Подключение контроля доступа
	 */
	public function initAcl ()
	{
		Loader::load ('Acl_Resource');
		Loader::load ('Acl_Role');
	}
	
	/**
	 * @desc Инициализация источника данных по умолчанию.
	 */
	public function initDds ($source_name = 'default')
	{
		Loader::load ('Data_Provider_Abstract');
		Loader::load ('Data_Provider_Manager');
		
		Loader::load ('Query');
		Loader::load ('Query_Options');
		Loader::load ('Query_Result');
		Loader::load ('Query_Translator');
		
		Loader::load ('DDS');
		Loader::load ('Data_Mapper_Abstract');
		Loader::load ('Data_Source_Abstract');
		Loader::load ('Data_Source_Manager');
		
		DDS::setDataSource (Data_Source_Manager::get ($source_name));
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
		IcEngine::$messageQueue = new Message_Queue ();
	}
	
	/**
	 * @desc Инициализация менеджера моделей и менеджера коллекций.
	 */
	public function initModelManager ()
	{
		Loader::load ('Model_Manager');
		Loader::load ('Model_Collection_Manager');
		IcEngine::$modelManager = new Model_Manager (
			IcEngine::$modelScheme
		);
	}
	
	/**
	 * @desc Инициализация схемы моделей.
	 * @param string $config
	 */
	public function initModelScheme ($config)
	{
		Loader::load ('Model_Scheme');
		IcEngine::$modelScheme = new Model_Scheme (
			Config_Manager::get ('Model_Scheme', $config));
	}
	
	/**
	 * @desc Инициализация пользователя и сессии.
	 */
	public function initUser ()
	{
		Loader::load ('User');
		Loader::load ('User_Session');
		User_Guest::init ();
		User::init ();
	}
	
	/**
	 * @desc Инициализация рендера.
	 * @return View_Render_Abstract Рендер по умолчанию.
	 */
	public function initView ()
	{
		$view = View_Render_Broker::getView ();
		return $view;
	}
	
	/**
	 * @desc Инициализация менеджера виджетов.
	 */
	public function initWidgetManager ()
	{
		Loader::load ('Widget_Abstract');
		Loader::load ('Widget_Manager');
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