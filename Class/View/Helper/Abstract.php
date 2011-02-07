<?php

abstract class View_Helper_Abstract
{
	
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
	    $this->_view = $view ? $view : View_Render_Broker::getView ();
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