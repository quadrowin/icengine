<?php
/**
 * 
 * @desc Ренден сообщений Smarty
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Render_Smarty extends Mail_Render_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Mail_Render_Abstract::render()
	 */
	public function render ($template, array $data)
	{
	    $view = View_Render_Broker::pushViewByName ('Smarty');
	    
	    $view->assign ($data);
	    
        $result = $view->fetch ($template);
        
	    View_Render_Broker::popView ();
	    
	    return $result;
	}
	
}