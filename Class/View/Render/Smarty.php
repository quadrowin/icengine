<?php

class View_Render_Smarty extends View_Render_Abstract
{
	
	/**
	 * Вшений шаблон по умолчанию
	 * @var string
	 */
	const DEFAULT_LAYOUT = 'main.tpl';
	
	/**
	 * Директория для скопилированных шаблонов Smarty
	 * @var string
	 */
	const DEFAULT_COMPILE_DIR = 'cache/templates';
	
	/**
	 * Объект шаблонизатора
	 * @var Smarty
	 */
	protected $_smarty;
	
	/**
	 * Директория Smarty
	 * @var string
	 */
	protected $_pathToSmarty = 'smarty/Smarty.class.php';
	
	protected function _afterConstruct()
	{
		$this->_layout = isset ($this->_fields ['layout']) ? 
			$this->_fields ['layout'] : 
			self::DEFAULT_LAYOUT;
			
		if (
			class_exists ('Smarty') ||
			Loader::requireOnce ($this->_pathToSmarty, 'includes')
		)
		{
			$this->_smarty = new Smarty();
			
			$compile_dir = isset($this->_fields ['compile_dir']) ? 
				$this->_fields ['compile_dir'] : 
				self::DEFAULT_COMPILE_DIR;
				
			$this->_smarty->compile_dir = rtrim ($compile_dir, '\\/') . '/';
			$this->_smarty->template_dir = $this->_templatesPathes;
		}
		
		Loader::load ('Helper_Smarty_Filter_Dblbracer');
		Helper_Smarty_Filter_Dblbracer::register ($this->_smarty);
	}
	
	/**
	 * Добавление пути до директории с плагинами Smarty
	 * @param string $path
	 * 		Директория с плагинами
	 */
	public function addPluginsPath ($path)
	{
		$this->_smarty->plugins_dir = array_merge (
			(array) $this->_smarty->plugins_dir,
			(array) $path
		);
	}
	
	/**
	 * Добавление пути до директории с шаблонами
	 * @param string $path
	 * 		Директория с шаблонами.
	 */
	public function addTemplatesPath ($path)
	{
		$dir = rtrim ($path, '/');
		$this->_templatesPathes [] = $dir . '/';
		if ($this->_smarty)
		{
			$this->_smarty->template_dir = $this->_templatesPathes;
		}
	}
	
	public function addHelper ($helper, $method)
	{
	}
	
	public function assign ($key, $value = null)
	{
		if (is_array ($key))
		{
			$this->_smarty->assign ($key);
		}
		else
		{
			$this->_smarty->assign ($key, $value);
		}
	}
	
	public function display ($tpl = null)
	{
		$tpl = $tpl ? $tpl : $this->_layout;
		return $this->_smarty->display ($tpl);
	}
	
	public function fetch ($tpl)
	{
		return $this->_smarty->fetch ($tpl);
	}
	
	public function popVars ()
	{
	    $this->_smarty->_tpl_vars = array_pop ($this->_varsStack);
	}
	
	public function pushVars ()
	{
	    $this->_varsStack [] = $this->_smarty->_tpl_vars;
	    $this->_smarty->_tpl_vars = array ();
	}
	
}