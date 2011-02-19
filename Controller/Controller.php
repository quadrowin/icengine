<?php

class Controller_Controller extends Controller_Abstract
{
    
    const DEFAULT_METHOD = 'index';
    
	public function ajax ()
	{
		$call = explode ('/', $this->_input->receive ('call'));
		
		$controller = $call [0];
		$method = isset ($call [1]) ? $call [1] : self::DEFAULT_METHOD;
		
		Loader::load ('Data_Provider_Buffer');
		$input_buffer = new Data_Provider_Buffer ();
		
		$params = (array) $this->_input->receive ('params');
	    foreach ($params as $key => $value)
        {
        	$input_buffer->set ($key, $value);
        }
		
		$input = new Data_Transport ();
		$input->appendProvider ($input_buffer);
		
		$ca = new Controller_Action (array (
            'controller'	=> $controller,
		    'action'		=> $method,
		    'input'			=> $input
		));
		
		IcEngine::$application->frontController->getDispatcher ()->push ($ca);
		
		$ca = new Controller_Action (array (
            'controller'	=> $this->name (),
		    'action'		=> 'ajaxFinish'
		));
		
		IcEngine::$application->frontController->getDispatcher ()->push ($ca);
	}
	
	public function ajaxFinish ()
	{
	    $iterations = Controller_Broker::iterations ();
	    $iteration = end ($iterations);
	    Controller_Broker::flushResults ();
	    	    
        /**
	     * 
	     * @var $transaction Data_Transport_Transaction
	     */
	    $transaction = $iteration->getTransaction ();
	    
		$tpl = $iteration->getTemplate ();
        
        $result ['data'] = (array) $transaction->receive ('data');
        
        if ($tpl)
        {
            $view = View_Render_Broker::pushViewByName ('Smarty');
            
            $view->pushVars ();
            try
            {
            	$vals = $transaction->buffer ();
            	$this->_output->getFilters()->apply ($vals);
                $view->assign ($vals);
                $result ['html'] = $view->fetch ($tpl);
            }
            catch (Exception $e)
            {
    		    $msg = 
    		    	'[' . $e->getFile () . '@' . 
    				$e->getLine () . ':' . 
    				$e->getCode () . '] ' .
    				$e->getMessage () . "\r\n";
    				
    		    error_log ($msg . PHP_EOL, E_USER_ERROR, 3);
		    
    		    $this->_output->send ('error', 'Произола ошибка.');
                $result ['html'] = '';
            }
            $view->popVars ();
            
            View_Render_Broker::popView ();
        }
        else
        {
            $result ['html'] = '';
        }
        
        $this->_output->send (array (
            'back'		=> $this->_input->receive ('back'),
        	'result'    => $result
        ));
	}
	
	/**
	 * Вызов экшена контроллера по названию из входных параметров
	 */
	public function auto ()
	{
		$controller = $this->_input->receive ('controller');
		$action = $this->_input->receive ('action');
		
		return $this->replaceAction ($controller, $action);
	}
    
	/**
	 * 
	 * @param boolean $with_actions
	 * @return array
	 */
	public static function getControllersList ($with_actions = true)
	{
		$controllers = array();
		$postfix = '.php';
		
		$action_prefix = 'action';
		$action_prefix_len = strlen($action_prefix);
		
		$ajax_prefix = 'ajax';
		$ajax_prefix_len = strlen($ajax_prefix);
		
		$post_prefix = 'post';
		$post_prefix_len = strlen($post_prefix);
		
		$founded_res_ids = array();
		
		$controller_name2index = array();
		
		foreach (Loader::$pathes['controller'] as $path)
		{
			$files = scandir($path);
			foreach ($files as $file)
			{
				if (
					substr($file, -strlen($postfix), strlen($postfix)) == $postfix
				)
				{
					$controller = substr(
						$file, 
						0, 
						- strlen($postfix)
					);
						
					require_once $path . $file;
					
					$class = 'Controller_' . $controller;
					$actions = get_class_methods($class);
					if (!empty($actions))
					{
						sort($actions);
					}
					else
					{
						if (class_exists($class))
						{
							trigger_error(
								"No methods found for $class.", 
								E_USER_NOTICE
							);
						}
						else
						{
							trigger_error(
								"Class not found $class.",
								E_USER_NOTICE
							);
						}
						$actions = array();
					}
					$resources = array();
					foreach ($actions as $action)
					{ 
						if (
							substr($action, 0, $action_prefix_len) == $action_prefix ||
							substr($action, 0, $ajax_prefix_len) == $ajax_prefix ||
							substr($action, 0, $post_prefix_len) == $post_prefix
						)
						{
							$res = Acl_Resource::byControllerAction($controller, $action);
							if (!$res)
							{
								$res = new Acl_Resource (false);
								$res->create(array(
									'name'			=> "$controller::$action",
									'controller'	=> $controller,
									'action'		=> $action,
									'type_id'		=> Acl_Resource::TYPE_CONTROLLER_ACTION
								));
							}
							$resources[] = $res;
							$founded_res_ids[] = $res->id;
						}
					}
					
					$n = count($controllers);
					$controller_name2index[$controller] = $n;
					$controllers[$n] = array(
						'name'		=> $controller,
						'resources'	=> $resources
					);
				}
			}
		}
		
		// Несуществующие контроллеры и действия	
		$reses = new Acl_Resource_Collection ();
		$reses->where ('id NOT IN (?)', $founded_res_ids);
		
		foreach ($reses->items () as $resource)
		{
			$controller = $resource->controller;
			if (!isset ($controller_name2index [$controller]))
			{
				$n = count ($controllers);
				$controller_name2index [$controller] = $n;
				$controllers[$n] = array (
					'name'		=> $controller,
					'resources'	=> array (),
					'unexists'	=> true
				);
			}
			else
			{
				$n = $controller_name2index [$controller];
			}
			
			$controllers [$n]['resources'][] = $resource; 
		}
		
		
		return $controllers;
	}
    
}