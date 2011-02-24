<?php

class Mail_Render_Smarty extends Mail_Render_Abstract
{
	
	public function render ($template, array $data)
	{
	    $view = View_Render_Broker::pushViewByName ('Smarty');
	    
	    $view->assign ($data);
	    
        $result = $view->fetch ($template);
        
	    View_Render_Broker::popView ();
	    
	    return $result;
	    
	    /*
		loader::requireOnce (self::PATH_TO_SMARTY);
		$smarty = Registry::get ('smarty');
		if (!$smarty)
		{
			if (class_exists ('Smarty'))
			{
				$smarty = new Smarty ();
				$config = Registry::get ('config_smarty');
				$smarty->_templateDir = $config->templateDir;
				$smarty->_compileDir = $config->compileDir;
			}
		}
		if (!$smarty)
		{
			return;
		}
		$smarty->assign (self::DATA_VAR, $data);
		
		return $smarty->fetch ($this->_template);
		*/
	}
}