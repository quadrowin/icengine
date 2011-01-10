<?php

class Widget_Manager 
{
    
    const NULL_TEMPLATE = 'NULL';
    
	/**
	 * Имя файла с настройками кэширования
	 * @param string $widget
	 * @return string
	 */
	protected function _cacheName ($widget)
	{
		return 'config/Widget/' . $widget . '_Cache.php';
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
        $cache_config_file = $this->_cacheName ($name);
        
        return Executor::execute (
            array ($this, 'callUncached'),
            array ($name, $method, $args, $html_only),
            Cache_Manager::load ($cache_config_file)
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
            'return'    => $widget->{$method} ()
        );
       
        $tpl = $widget->template ($method);
        
        $output = $widget->getOutput ()->getProvider (0);
        
        $result ['data'] = (array) $output->get ('data');
        
        if ($tpl && $tpl != self::NULL_TEMPLATE)
        {
            $view = View_Render_Broker::pushViewByName ('Smarty');
            
            $view->pushVars ();
            
            $view->assign ($output->getAll ());
            $result ['html'] = $view->fetch ($tpl);
            
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