<?php
/**
 * 
 * @desc Абстрактный класс хелпера представления.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class View_Helper_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $_config = array ();
	
	/**
	 * 
	 * @var View_Render_Abstract
	 */
	protected $_view;
	
	/**
	 * 
	 * @param View_Render_Abstract $render
	 * 		Рендерер, для которого вызывается хелпер
	 */
	public function __construct ($view = null)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $viewRenderManager = $serviceLocator->getService('viewRenderManager');
	    $this->_view = $view ? $view : $viewRenderManager->getView ();
	}
	
	/**
	 * @desc Загружает и возвращает конфиг для модели
	 * @param string $class Класс модели, если отличен от get_class ($this)
	 * @return Objective
	 */
	public function config ($class = null)
	{
		if (is_array ($this->_config))
		{
            $serviceLocator = IcEngine::serviceLocator();
            $configManager = $serviceLocator->getService('configManager');
			$this->_config = $configManager->get (
				$class ? $class : get_class ($this),
				$this->_config
			);
		}
		return $this->_config;
	}
	
	/**
	 * 
	 * @param array $params
	 * 		Параметры, переданные из шаблона
	 * @return string
	 * 		Результат работы хелпера
	 */
	abstract public function get (array $params);
	
}