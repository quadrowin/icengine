<?php

class View
{
	
	/**
	 * 
	 * @var View_Render_Abstract
	 */
	protected $_delegee;
	
	protected $_layout = 'main.tpl';
	
	protected $_defaultRender = 'View_Render_Smarty';
	
	protected $_templatesPath = 'View/';
	
	public function __call ($method, $args)
	{
		return call_user_func_array (array ($this->_delegee, $method), $args);
	}
	
	/**
	 * 
	 * @param $render
	 */
	public function __construct ($render = null)
	{
		Loader::load('View_Render_Abstract');
		
		if (empty($render))
		{
			if (Loader::load ($this->_defaultRender))
			{
				$this->setRender (new $this->_defaultRender);
			}
		}
		else
		{
			$this->setRender ($render);
		}
		
		Loader::setPath('View', 'View/');
		
		if (method_exists ($this->_delegee, 'setTemplatesPath'))
		{
			$this->_delegee->setTemplatesPath ($this->getTemplatesPath());
		}
	}
	
	public function getLayout ()
	{
		return $this->_layout;
	}
	
	/**
	 * @return ViewRender
	 */
	public function getRender ()
	{
		return $this->_delegee;
	}
	
	public function getTemplatesPath ()
	{
		return $this->_templatesPath;
	}
	
	public function getDefaultRender ()
	{
		return $this->_defaultRender;
	}
	
	public function setLayout ($layout)
	{
		if (file_exists ($this->_templatesPath . $layout))
		{
			$this->_layout = $layout;
		}
		else
		{
			trigger_error("Layout not found: $layout", E_USER_WARNING);
		}
	}
	
	public function setTemplatesPath ($path)
	{
		$dir = rtrim ($path, '/');
		if (is_dir ($dir))
		{
			$this->_templatesPath = $dir . '/';
			
			if (
				$this->_delegee && 
				method_exists ($this->_delegee, 'setTemplatesPath')
			)
			{
				$this->_delegee->setTemplatesPath ($this->_templatesPath);
			}
		}
		else
		{
			trigger_error("Templates path not found: $path", E_USER_WARNING);
		}
	}
	
	public function setRender (View_Render_Abstract $render)
	{
		$this->_delegee = $render;
	}
	
}