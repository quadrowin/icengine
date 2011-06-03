<?php
/**
 * 
 * @desc Рендер с использованием шаблонизатора Smarty.
 * @author Гурус
 * @package IcEngine
 *
 */
class View_Render_Smarty extends View_Render_Abstract
{
	
	/**
	 * @desc Объект шаблонизатора
	 * @var Smarty
	 */
	protected $_smarty;
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Внешний шаблон.
		 * Будет использоватья при выводе в браузер через метод display.
		 * @var string
		 */
		'layout'			=> 'main.tpl',
		/**
		 * @desc Директория для скопилированных шаблонов Smarty
		 * @var string
		 */
		'compile_path'		=> 'cache/templates',
		/**
		 * @desc Путь для лоадера до смарти
		 * @var string
		 */
		'smarty_path'		=> 'smarty/Smarty.class.php',
		/**
		 * @desc Пути до шаблонов
		 * @var array
		 */
		'templates_path'	=> array (),
		/**
		 * @desc Пути до плагинов
		 * @var array
		 */
		'plugins_path'		=> array (),
		/**
		 * @desc Фильры
		 * @var array
		 */
		'filters'			=> array (
			'Dblbracer'
		)
	);
	
	protected function _afterConstruct ()
	{
		$config = $this->config ();
		if (!class_exists ('Smarty'))
		{
			Loader::requireOnce ($config ['smarty_path'], 'includes');
		}
		
		$this->_smarty = new Smarty ();
		
		$this->_smarty->compile_dir = $config ['compile_path'];
		$this->_smarty->template_dir = $config ['templates_path']->__toArray ();
		$this->_smarty->plugins_dir = $config ['plugins_path']->__toArray ();
		
		// Фильтры
		foreach ($config ['filters'] as $filter)
		{
			$filter = 'Helper_Smarty_Filter_' . $filter;
			Loader::load ($filter);
			$filter::register ($this->_smarty);
		}
	}
	
	/**
	 * @desc Добавление пути до директории с плагинами Smarty
	 * @param string|array $path Директории с плагинами
	 */
	public function addPluginsPath ($path)
	{
		$this->_smarty->plugins_dir = array_merge (
			(array) $this->_smarty->plugins_dir,
			(array) $path
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addTemplatesPath()
	 */
	public function addTemplatesPath ($path)
	{
		$this->_smarty->template_dir = array_merge (
			(array) $this->_smarty->template_dir,
			(array) $path
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addHelper()
	 */
	public function addHelper ($helper, $method)
	{
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::assign()
	 */
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
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display ($tpl = null)
	{
		$tpl = $tpl ? $tpl : self::$_config ['layout'];
		return $this->_smarty->display ($tpl);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function fetch ($tpl)
	{
		return $this->_smarty->fetch ($tpl);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::getVar()
	 */
	public function getVar ($key)
	{
		return $this->_smarty->_tpl_vars [$key];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::popVars()
	 */
	public function popVars ()
	{
		$this->_smarty->_tpl_vars = array_pop ($this->_varsStack);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::pushVars()
	 */
	public function pushVars ()
	{
		$this->_varsStack [] = $this->_smarty->_tpl_vars;
		$this->_smarty->_tpl_vars = null;
	}
	
	/**
	 * @desc Возвращает используемый экземпляр шаблонизатора.
	 * @return Smarty
	 */
	public function smarty ()
	{
		return $this->_smarty;
	}
	
}