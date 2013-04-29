<?php

/**
 * Рендер с использованием шаблонизатора Smarty.
 * 
 * @author goorus
 */
class View_Render_Smarty extends View_Render_Abstract
{
	/**
	 * Объект шаблонизатора
	 * 
     * @var Smarty
	 */
	protected $smarty;

	/**
	 * @inheritdoc
	 */
	protected $config = array(
		/**
		 * Директория для скопилированных шаблонов Smarty
		 * 
         * @var string
		 */
		'compilePath'		=> 'cache/templates',
		/**
		 * Путь для лоадера до смарти
		 * 
         * @var string
		 */
		'smartyPath'		=> 'smarty3/Smarty.class.php',
		/**
		 * Пути до шаблонов
		 * 
         * @var array
		 */
		'templatesPath'     => array(),
		/**
		 * Пути до плагинов
		 * 
         * @var array
		 */
		'pluginsPath'		=> array(),
		/**
		 * Фильры
		 * 
         * @var array
		 */
		'filters'			=> array()
	);

    /**
     * @inheritdoc
     */
	public function __construct()
	{
		$config = $this->config();
        $loader = $this->getService('loader');
		$loader->requireOnce($config['smartyPath'], 'Vendor');
		$this->smarty = new Smarty();
		$this->smarty->compile_dir = $config['compilePath'];
		$this->smarty->template_dir = array_reverse(
			$config['templatesPath']->__toArray()
		);
		$this->smarty->plugins_dir = $config['pluginsPath']->__toArray();
		// Фильтры
		foreach ($config['filters'] as $filter) {
			$filterClass = 'Helper_Smarty_Filter_' . $filter;
            $filter = new $filterClass;
			$filter->register($this->smarty);
		}
	}

	/**
	 * Получает идентификатор компилятор для шаблона.
	 * Необходимо, т.к. шаблон зависит от путей шаблонизатора.
	 * 
     * @param string $tpl
	 * @return string
	 */
	protected function compileId($tpl)
	{
		return crc32(json_encode($this->smarty->template_dir));
	}

	/**
	 * Добавление пути до директории с плагинами Smarty
	 * 
     * @param string|array $path Директории с плагинами
	 */
	public function addPluginsPath ($path)
	{
		$this->smarty->plugins_dir = array_merge(
			(array) $this->smarty->plugins_dir, (array) $path
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addTemplatesPath()
	 */
	public function addTemplatesPath($path)
	{
		$this->smarty->template_dir = array_merge(
			array_reverse((array) $path),
			(array) $this->smarty->template_dir
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addHelper()
	 */
	public function addHelper($helper, $method)
	{
        
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::assign()
	 */
	public function assign($key, $value = null)
	{
		if (is_array($key)) {
			$this->smarty->assign($key);
		} else {
			$this->smarty->assign($key, $value);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display($tpl)
	{
		$tpl .= '.tpl';
		return $this->smarty->display($tpl, null, $this->compileId($tpl));
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function fetch($tpl)
	{
		$tpl .= '.tpl';
		return $this->smarty->fetch($tpl);
	}

	/**
	 * Возвращает массив путей до шаблонов.
	 * 
     * @return array
	 */
	public function getTemplatesPathes()
	{
		return $this->smarty->template_dir;
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::getVar()
	 */
	public function getVar($key)
	{
		return $this->smarty->getTemplateVars($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::popVars()
	 */
	public function popVars()
	{
		$this->smarty->clearAllAssign();
		$vars = array_pop($this->varsStack);
        $this->smarty->assign($vars);
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::pushVars()
	 */
	public function pushVars()
	{
		$this->varsStack[] = $this->smarty->getTemplateVars();
		$this->smarty->clearAllAssign();
	}

	/**
	 * Возвращает используемый экземпляр шаблонизатора.
	 * 
     * @return Smarty
	 */
	public function smarty()
	{
		return $this->smarty;
	}
}