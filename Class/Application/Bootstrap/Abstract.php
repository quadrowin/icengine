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
    
	public function initDataModel ()
	{
		$mysql = new Data_Source_Mysql ();
		$fileM = new Data_Source_File ();
		
		$redis = new Data_Source_Redis ();
		$fileR = new Data_Source_File ();
		
		$queue = new Data_Source_QueryQueue ();
		
		$mysql->setCacheScheme (array (
			Query::SELECT	=> new Data_Source_Collection ($fileM),
			Query::INSERT	=> new Data_Source_Collection ($fileM),
			Query::UPDATE	=> new Data_Source_Collection ($fileM),
			Query::DELETE	=> new Data_Source_Collection ($fileM)
		));
		
		$redis->setCacheScheme (array (
			Query::INSERT	=> new Data_Source_Collection ($fileR, $queue),
			Query::UPDATE	=> new Data_Source_Collection ($fileR, $queue),
			Query::DELETE	=> new Data_Source_Collection ($fileR, $queue),
		));
		$redis->setIndexSources (new Data_Source_Collection ($redis));
		
		$ds = new Data_Source_Mysql ();
		
		$ds->setCacheScheme (array (
			Query::INSERT	=> new Data_Source_Collection ($redis),
			Query::SELECT	=> new Data_Source_Collection ($redis, $fileR, $queue),
			Query::UPDATE	=> new Data_Source_Collection ($redis),
			Query::DELETE	=> new Data_Source_Collection ($redis)
		));
		$ds->setIndexSources (new Data_Source_Collection ($redis));
		
		Registry::set ('mysql', $mysql);
		Registry::set ('redis', $redis);
		Registry::set ('fileR', $fileR);
		Registry::set ('fileM', $fileM);
		Registry::set ('queue', $queue);
		Registry::set ('ds', $ds);
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
			Config_Manager::load ('Model_Scheme', $config));
	}
	
	public function initResourceManager ()
	{
	    Loader::load ('Resource_Manager');
	    $this->behavior->resourceManager = new Resource_Manager ();
	    IcEngine::$resourceManager = $this->behavior->resourceManager;
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