<?php

abstract class View_Render_Broker
{

	/**
	 * 
	 * @var array <View_Render_Abstract>
	 */
	private static $_views = array ();
	
	/**
	 * @var array
	 */
	private static $_templatesToRender = array ();
	
	/**
	 * 
	 * @var string
	 */
	private static $_templateExtension = '.tpl';
	
	/**
	 * Конфиг
	 * @var array
	 */
	public static $config = array (
		/**
		 * Рендер по умолчанию
		 * @var string
		 */
		'default_view'		=> 'Smarty'
	);
	
	/**
	 * Выводит результат работы шаблонизатора в браузер
	 */
	public static function display ()
	{
		return self::getView ()->display ();
	}
	
	/**
	 * 
	 * @return View_Render_Abstract
	 */
	public static function getView ()
	{
		if (!self::$_views)
		{
			Loader::load ('View_Render');
			self::pushViewByName (self::$config ['default_view']);
			//self::$_view = new View_Render (array('name' => self::$_defaultView));
		} 
		
		return end (self::$_views);
	}
	
	/**
	 * @return string
	 */
	public static function getTemplateExtension ()
	{
		return self::$_templateExtension;
	}
	
	/**
	 * @return View_Render_Abstract
	 */
	public static function popView ()
	{
//		echo 'pop' . count (self::$_views) . ' ' . end (self::$_views)->name;
		$view = array_pop (self::$_views);
		return $view;
	}
	
	/**
	 * 
	 * @param View_Render_Abstract $view
	 * @return View_Render_Abstract
	 */
	public static function pushView (View_Render_Abstract $view)
	{
		self::$_views [] = $view;
		return $view;
	}
	
	/**
	 * 
	 * @param integer $id
	 * @return View_Render_Abstract
	 */
	public static function pushViewById ($id)
	{
		$view = Model_Manager::byKey ('View_Render', $id);
		return self::pushView ($view);
	}
	
	/**
	 * 
	 * 
	 * @param string $name
	 * @return View_Render_Abstract
	 */
	public static function pushViewByName ($name)
	{
		$view = View_Render::byName ($name);	
		return self::pushView ($view);
	}
	
	/**
	 * 
	 * @param string $value
	 */
	public static function setTemplateExtension ($value)
	{
		self::$_templateExtension = $value;
	}
	
	/**
	 * @desc	Обработка шаблонов из стека.
	 * @param	array $data
	 */
	public static function render (array $outputs)
	{
		$view = self::getView ();
		
		Loader::load ('Message_Before_Render');
		Message_Before_Render::push ($view);
		
		// Рендерим в обратном порядке		
		$outputs = array_reverse ($outputs);
		
		/**
		 * @var item Controller_Dispatcher_Iteration
		 */
		foreach ($outputs as $item)
		{
			/**
			 * 
			 * @var $transaction Data_Transport_Transaction
			 */
			$transaction = $item->getTransaction ();
			
			/**
			 * @var $action Route_Action
			 */
			$action = $item->getRouteAction ();
			
			$transaction->commit ();
			
			$template = $item->getTemplate ();
			$result = $view->fetch ($template);
			
			$view->assign (
				isset ($action->assign) ? $action->assign : 'content',
				$result);
		}
		
		Loader::load ('Message_After_Render');
		Message_After_Render::push ($view);
	}
	
	/**
	 * @desc	Рендер одной итерации диспетчера.
	 * @param	Controller_Dispatcher_Iteration $iteration
	 * @return	string
	 */
	public static function fetchIteration (
		Controller_Dispatcher_Iteration $iteration)
	{
		/**
		 * 
		 * @var $transaction Data_Transport_Transaction
		 */
		$transaction = $iteration->getTransaction ();
		
		/**
		 * @var $action Route_Action
		 */
		$action = $iteration->getRouteAction ();

		$template = $iteration->getTemplate ();
		
		$view = self::getView ();
		$view->assign ($transaction->buffer ());
		
		$result = $view->fetch ($template);

		return $result;
	}
	
}
