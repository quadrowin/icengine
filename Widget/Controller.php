<?php
/**
 * 
 * @desc Виджет для вызова методов контроллеров.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Widget_Controller extends Widget_Abstract
{
	
	/**
	 * @desc Метод контроллера по умолчанию
	 * @var string
	 */
	const DEFAULT_ACTION = 'index';
	
	/**
	 * @desc Реализация
	 */
	public function index ()
	{
		$action = explode ('/', $this->_input->receive ('action'));
		
		$controller = $action [0];
		$action = isset ($action [1]) ? $action [1] : self::DEFAULT_ACTION;
		
		Loader::load ('Controller_Manager');
		Loader::load ('Controller_Action');
		Loader::load ('Controller_Dispatcher_Iteration');
		Loader::load ('Route_Action');
		$iteration = Controller_Manager::run (new Controller_Action (array (
			'controller'	=> $controller,
			'action'		=> $action,
			'input'			=> $this->_input,
			'output'		=> $this->_output
		)));
		
		$this->_output->send (
			'widget_content_unique_var',
			View_Render_Broker::fetchIteration ($iteration)
		);
	}
	
}