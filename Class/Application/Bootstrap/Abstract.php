<?php
/**
 * 
 * @desc Абстрактный загрузчик.
 * @author Goorus
 * @package IcEngine
 *
 */
class Application_Bootstrap_Abstract
{
	
	/**
	 * @desc Окружение
	 * @var Application_Behavior_Abstract
	 */
	public $behavior;
	
	/**
	 * @desc Возвращает загрузчик.
	 * @param Application_Behavior_Abstract $behavior Окружение загрузичка.
	 */
	public function __construct (Application_Behavior_Abstract $behavior)
	{
		$this->behavior = $behavior;
	}
	
	/**
	 * @desc Инициализация менеджера атрибутов.
	 */
	public function initAttributeManager ()
	{
		Loader::load ('Attribute_Manager');
		$this->behavior->attributeManager = new Attribute_Manager (
			DDS::getDataSource ()
		);
		IcEngine::$attributeManager = $this->behavior->attributeManager;
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
	public function initDds ()
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
		
		DDS::setDataSource (Data_Source_Manager::get ('default'));
	}
	
	/**
	 * @desc Инициализация очереди событий.
	 */
	public function initMessageQueue ()
	{
		Loader::load ('Message_Queue');
		IcEngine::$application->messageQueue = new Message_Queue ();
	}
	
	/**
	 * @desc Инициализация менеджера моделей и менеджера коллекций.
	 */
	public function initModelManager ()
	{
		Loader::load ('Model_Manager');
		Loader::load ('Model_Collection_Manager');
		$this->behavior->modelManager = new Model_Manager (
			IcEngine::$modelScheme
		);
		IcEngine::$modelManager = $this->behavior->modelManager;
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
		User::init ();
	}
	
	/**
	 * @desc Инициализация рендера.
	 * @return View_Render_Abstract Рендер по умолчанию.
	 */
	public function initView ()
	{
		$view = View_Render_Broker::getView ();
		return $this->behavior->view = $view;
	}
	
	/**
	 * @desc Инициализация менеджера виджета.
	 */
	public function initWidgetManager ()
	{
		Loader::load ('Widget_Abstract');
		Loader::load ('Widget_Manager');
		$this->behavior->widgetManager = new Widget_Manager ();
		IcEngine::$widgetManager = $this->behavior->widgetManager;
	}
	
	public function run ()
	{
		//$this->makeInclude();
		//$this->initDataModel();
	}
	
}