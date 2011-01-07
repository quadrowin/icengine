<?php

class Application_Behavior_Abstract
{
    
    /**
     * Аттрибуты моделей
     * @var Attribute_Manager
     */
    public $attributeManager;

	/**
	 * Загрузчик
	 * @var Application_Bootstrap_Abstract
	 */
	public $bootstrap;
	
	/**
	 * Путь до контроллеров
	 * @var string
	 */
	public $controllersPath;
	
	/**
	 * Менеджер моделей
	 * @var Model_Manager
	 */
	public $modelManager;
	
	/**
	 * Менеджер ресурсов
	 * @var Resource_Manager
	 */
	public $resourceManager;
	
	/**
	 * Менеджер виджетов
	 * 
	 * @var Widget_Manager
	 */
	public $widgetManager;
	
	/**
	 * 
	 * @var View_Render_Abstract
	 */
	public $view;
	
	public function __construct ()
	{
		$this->controllersPath = Ice_Implementator::getControllersPath ();
		include dirname (__FILE__) . '/../Bootstrap/Abstract.php';
	}
	
	/**
	 * @return string
	 */
	public function name ()
	{
		return substr (strrchr (get_class ($this), '_'), 1);
	}
	
	public function run ()
	{	
		$fn = dirname (__FILE__) . '/../Bootstrap/' . $this->name () . '.php';
		include $fn;
		$bootstrap = 'Application_Bootstrap_' . $this->name ();
		$this->bootstrap = new $bootstrap ($this);
		$this->bootstrap->run ();
	}
	
	/**
	 * Делает окружение активным.
	 * Актуально в случае динамической смены окружения.
	 */
	public function activate ()
	{
	    IcEngine::$attributeManager = $this->attributeManager;
	    IcEngine::$modelManager = $this->modelManager;
	    IcEngine::$resourceManager = $this->resourceManager;
	    IcEngine::$widgetManager = $this->widgetManager;
	}
	
}