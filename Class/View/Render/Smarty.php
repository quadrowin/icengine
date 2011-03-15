<?php
/**
 * 
 * Рендер с использованием шаблонизатора Smarty.
 * @author Гурус
 * @package IcEngine
 *
 */
class View_Render_Smarty extends View_Render_Abstract
{
	
	/**
	 * Объект шаблонизатора
	 * @var Smarty
	 */
	protected $_smarty;
	
	/**
	 * Конфиг
	 * @var array
	 */
	public $config = array (
		/**
		 * Внешний шаблон.
		 * Будет использоватья при выводе в браузер через метод display.
		 * @var string
		 */
		'layout'			=> 'main.tpl',
		/**
		 * Директория для скопилированных шаблонов Smarty
		 * @var string
		 */
		'compile_path'		=> 'cache/templates',
		/**
		 * Путь для лоадера до смарти
		 * @var string
		 */
		'smarty_path'		=> 'smarty/Smarty.class.php',
		/**
		 * Пути до шаблонов
		 * @var array
		 */
		'templates_path'	=> array (),
		/**
		 * Пути до плагинов
		 * @var array
		 */
		'plugins_path'		=> array ()
	);
	
	protected function _afterConstruct()
	{
		$this->config ();
		if (!class_exists ('Smarty'))
		{
			Loader::requireOnce ($this->config ['smarty_path'], 'includes');
		}
		
		$this->_smarty = new Smarty ();
		
		$this->_smarty->compile_dir = $this->config ['compile_path'];
		$this->_smarty->template_dir = $this->config ['templates_path']->__toArray ();
		$this->_smarty->plugins_dir = $this->config ['plugins_path']->__toArray ();
		
		Loader::load ('Helper_Smarty_Filter_Dblbracer');
		Helper_Smarty_Filter_Dblbracer::register ($this->_smarty);
		
		Loader::load ('Helper_Smarty_Filter_Acl');
		Helper_Smarty_Filter_Acl::register ($this->_smarty);
	}
	
	/**
	 * Добавление пути до директории с плагинами Smarty
	 * @param string|array $path
	 * 		Директории с плагинами
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
		$this->_smarty->template_dir = array_merge (
			(array) $this->_smarty->template_dir,
			(array) $path
		);
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
		$tpl = $tpl ? $tpl : $this->config ['layout'];
		return $this->_smarty->display ($tpl);
	}
	
	public function fetch ($tpl)
	{
		return $this->_smarty->fetch ($tpl);
	}
	
	public function getVar ($key)
	{
		return $this->_smarty->_tpl_vars [$key];
	}
	
	public function popVars ()
	{
		$this->_smarty->_tpl_vars = array_pop ($this->_varsStack);
	}
	
	public function pushVars ()
	{
		$this->_varsStack [] = $this->_smarty->_tpl_vars;
		$this->_smarty->_tpl_vars = null;
	}
	
	/**
	 * 
	 * @return Smarty
	 */
	public function smarty ()
	{
		return $this->_smarty;
	}
	
}