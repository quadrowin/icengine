<?php
/**
 * 
 * @desc Абстрактный класс окружения.
 * @author Ilya
 * @package IcEngine
 *
 */
class Application_Behavior_Abstract
{
	
	/**
	 * @desc Аттрибуты моделей
	 * @var Attribute_Manager
	 */
	public $attributeManager;

	/**
	 * @desc Загрузчик
	 * @var Application_Bootstrap_Abstract
	 */
	public $bootstrap;
	
	/**
	 * @desc Путь до контроллеров
	 * @var string
	 */
//	public $controllersPath;
	
	/**
	 * @desc Менеджер моделей
	 * @var Model_Manager
	 */
	public $modelManager;
	
	/**
	 * @desc Менеджер виджетов
	 * @var Widget_Manager
	 */
	public $widgetManager;
	
	/**
	 * @desc Рендер по умолчанию
	 * @var View_Render_Abstract
	 */
	public $view;
	
	public function __construct ()
	{
//		$this->controllersPath = Ice_Implementator::getControllersPath ();
		include dirname (__FILE__) . '/../Bootstrap/Abstract.php';
	}
	
	/**
	 * @desc Возвращает имя окружения
	 * @return string
	 */
	public function name ()
	{
		return substr (strrchr (get_class ($this), '_'), 1);
	}
	
	/**
	 * @desc Старт окружения
	 */
	public function run ()
	{	
		$fn = dirname (__FILE__) . '/../Bootstrap/' . $this->name () . '.php';
		include $fn;
		$bootstrap = 'Application_Bootstrap_' . $this->name ();
		$this->bootstrap = new $bootstrap ($this);
		$this->bootstrap->run ();
	}
	
	/**
	 * @desc Делает окружение активным. Актуально в случае динамической смены окружения.
	 */
	public function activate ()
	{
		IcEngine::$attributeManager = $this->attributeManager;
		IcEngine::$modelManager = $this->modelManager;
		IcEngine::$widgetManager = $this->widgetManager;
	}
	
}