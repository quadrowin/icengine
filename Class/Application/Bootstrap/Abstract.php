<?php

/**
 * 
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
	
	public function __construct (Application_Behavior_Abstract $behavior)
	{
		$this->behavior = $behavior;
	}
	
	public function initAttributeManager ()
	{
		Loader::load ('Attribute_Manager');
		$this->behavior->attributeManager = new Attribute_Manager (
			DDS::getDataSource ()
		);
		IcEngine::$attributeManager = $this->behavior->attributeManager;
	}
	
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
	
	public function initMessageQueue ()
	{
		Loader::load ('Message_Queue');
		IcEngine::$application->messageQueue = new Message_Queue ();
	}
	
	public function initModelManager ()
	{
		Loader::load ('Model_Manager');
		$this->behavior->modelManager = new Model_Manager (
			IcEngine::$modelScheme,
			$this->behavior->resourceManager
		);
		IcEngine::$modelManager = $this->behavior->modelManager;
	}
	
	/**
	 * 
	 * @param string $config
	 */
	public function initModelScheme ($config)
	{
		Loader::load ('Model_Scheme');
		IcEngine::$modelScheme = new Model_Scheme (
			Config_Manager::get ('Model_Scheme', $config));
	}
	
	public function initResourceManager ()
	{
		Loader::load ('Resource_Manager');
		$this->behavior->resourceManager = new Resource_Manager ();
		IcEngine::$resourceManager = $this->behavior->resourceManager;
	}
	
	/**
	 * Инициализация пользователя и сессии.
	 */
	public function initUser ()
	{
		Loader::load ('User');
		Loader::load ('User_Session');
		User::init ();
	}
	
	public function initWidgetManager ()
	{
		Loader::load ('Widget_Abstract');
		Loader::load ('Widget_Manager');
		$this->behavior->widgetManager = new Widget_Manager ();
		IcEngine::$widgetManager = $this->behavior->widgetManager;
	}
	
	public function makeInclude ()
	{
		include ('Observer.php');
		include ('Query.php');
		include ('Query/Translator.php');
		include ('Query/Translator/Mysql.php');
		include ('Query/Translator/CachePattern.php');

		include ('Mysql.php');
		
		include ('Registry.php');
		
		include ('Cacher_template.php');
		include ('Cacher_file.php');
		include ('ItemScheme.php');
	}
	
	public function run ()
	{
		//$this->makeInclude();
		//$this->initDataModel();
	}
	
}