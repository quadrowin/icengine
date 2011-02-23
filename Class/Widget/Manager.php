<?php
/**
 * 
 * @desc Менеджер виджетов.
 * @author Юрий
 * @package IcEngine
 *
 */
class Widget_Manager 
{
	
	/**
	 * Шаблон, не передающийся в рендер.
	 * @var string
	 */
	const NULL_TEMPLATE = 'NULL';
	
	/**
	 * Конфиг
	 * @var array|Objective
	 */
	public static $config = array (
		
		'widgets'	=> array ()
		
	);
	
	/**
	 * @desc Настройки кэширования для виджета
	 * @param string $widget
	 * @param string $method
	 * @return Objective
	 */
	protected function _cacheConfig ($widget, $method)
	{
		if (is_array (self::$config))
		{
			self::$config = Config_Manager::get (__CLASS__, self::$config);
		}
		
		$config = self::$config->widgets [$widget . '::' . $method];
		return $config ? $config : self::$config->widgets [$widget];
	}
	
	/**
	 * Получение виджета по названию
	 * @param string $name
	 * 		Название виджета
	 * @return Widget_Abstract
	 * 		Виджет
	 */
	protected function _get ($name)
	{
		$widget = IcEngine::$resourceManager->get ('Widget', $name);
		
		if (!$widget)
		{
			$class = 'Widget_' . $name;
			Loader::load ($class);
			$widget = new $class ();
 			
			Loader::load ('Data_Provider_Buffer');
			
			$widget
				->getInput ()->appendProvider (new Data_Provider_Buffer ());
			$widget
				->getOutput ()->appendProvider (new Data_Provider_Buffer ());
			
			IcEngine::$resourceManager->set ('Widget', $name, $widget);
		}
		
		if (!$widget)
		{
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ("Widget not found: $name.");
			return;
		}
		
		return $widget;
	}
	
	/**
	 * 
	 * @param string $name
	 * 		Название виджета
	 * @param string $method
	 * 		Метод
	 * @param boolean $html_only
	 * 		Вернуть только html.
	 * 		Если false, будет возвращен массив со всеми результатами.
	 * @param array $args
	 * 		Параметры
	 * @return string|array
	 */
	public function call ($name, $method = 'index', array $args = array (), 
		$html_only = true)
	{
		$cache_config = $this->_cacheConfig ($name, $method);
		
		return Executor::execute (
			array ($this, 'callUncached'),
			array ($name, $method, $args, $html_only),
			$cache_config
		);
	}
	
	/**
	 * 
	 * @param string $name
	 * 		Название виджета
	 * @param string $method
	 * 		Метод
	 * @param array $args
	 * 		Параметры
	 * @param boolean $html_only
	 * 		Вернуть только html.
	 */
	public function callUncached ($name, $method = 'index', 
		array $args = array (), $html_only = true)
	{
		$widget = $this->_get ($name);
		
		$input = $widget->getInput ()->getProvider (0);
		$input->flush ();
		
		foreach ($args as $key => $value)
		{
			$input->set ($key, $value);
		}
		
		$result = array (
			'return'	=> $widget->{$method} ()
		);
	   
		$tpl = $widget->template ($method);
		
		$output = $widget->getOutput ()->getProvider (0);
		
		$result ['data'] = (array) $output->get ('data');
		
		if ($tpl && $tpl != self::NULL_TEMPLATE)
		{
			$view = View_Render_Broker::pushViewByName ('Smarty');
			
			$view->pushVars ();
			try
			{
				$view->assign ($output->getAll ());
				$result ['html'] = $view->fetch ($tpl);
			}
			catch (Exception $e)
			{
				$msg = 
					'[' . $e->getFile () . '@' . 
					$e->getLine () . ':' . 
					$e->getCode () . '] ' .
					$e->getMessage () . PHP_EOL;
					
				error_log (
					$msg . PHP_EOL .
					$e->getTraceAsString () . PHP_EOL, 
					E_USER_ERROR, 3
				);
			
				$result ['error'] = 'Widget_Manager: Error in template.';
				$result ['html'] = '';
			}
			$view->popVars ();
			
			View_Render_Broker::popView ();
		}
		else
		{
			$result ['html'] = '';
		}

		$output->flush ();
		
		return $html_only ? $result ['html'] : $result;
	}
	
}