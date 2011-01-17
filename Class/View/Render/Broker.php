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
	private static $_defaultView = 'Smarty';
	
	/**
	 * 
	 * @var string
	 */
	private static $_templateExtension = '.tpl';
	
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
			self::pushViewByName (self::$_defaultView);
			//self::$_view = new View_Render (array('name' => self::$_defaultView));
		} 
		
		return end (self::$_views);
	}
	
	/**
	 * @return string
	 */
	public static function getDefaultView ()
	{
		return self::$_defautlView;
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
//	    echo 'pop' . count (self::$_views) . ' ' . end (self::$_views)->name;
	    return array_pop (self::$_views);
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
	    $view = IcEngine::$modelManager->get ('View_Render', $id);
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
	 * @param string $view
	 */
	public static function setDefaultView ($view)
	{
		self::$_defaultView = $view;
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
			
//			$view->pushVars ();
//            
//			try
//			{
//			    $result = $view->fetch ($template);
//			}
//			catch (Exception $e)
//			{
//			    $msg = 
//    		    	'[' . $e->getFile () . '@' . 
//    				$e->getLine () . ':' . 
//    				$e->getCode () . '] ' .
//    				$e->getMessage () . "\r\n";
//    				
//    			Debug::vardump ($msg);
//    				
//    		    error_log ($msg . PHP_EOL, E_USER_ERROR, 3);
//    		    
//    		    $result = '';
//			}
//			
//			$view->popVars ();

//			$result = self::fetchIteration ($item);
			
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
		
//		$view->pushVars ();
//            
//		try
//		{
//			$result = $view->fetch ($template);
//		}
//		catch (Exception $e)
//		{
//			$msg = 
//				'[' . $e->getFile () . '@' . 
//				$e->getLine () . ':' . 
//				$e->getCode () . '] ' .
//				$e->getMessage () . "\r\n";
//			
//			Debug::vardump ($msg);
//    				
//			error_log ($msg . PHP_EOL, E_USER_ERROR, 3);
//			
//			$result = '';
//		}
//		
//		$view->popVars ();

		return $result;
	}
	
}
